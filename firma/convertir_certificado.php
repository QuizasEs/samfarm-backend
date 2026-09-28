<?php
/**
 * Convierte el certificado digital .p12 al formato moderno (AES-256-CBC + SHA-256)
 * que exige OpenSSL 3.x.
 *
 * Los .p12 entregados por las entidades certificadoras Bolivianas usan PKCS#12 PBE
 * con SHA-1 y RC2-128 / 3DES. OpenSSL 3 movio esos algoritmos al "proveedor legacy",
 * que no se carga por defecto, por lo que openssl_pkcs12_read() devuelve false y
 * cargarCertificadoP12() de models/siatModel.php nunca logra firmar.
 *
 * Este script Lee el .p12 viejo con el proveedor legacy y lo reescribe en formato
 * moderno. El archivo resultante se lee sin ningun ajuste en OpenSSL.
 *
 * Uso (solo linea de comandos):
 *   php firma\convertir_certificado.php [origen] [destino]
 *
 * Si el origen no se puede leer, el propio script imprime el comando exacto
 * (con las variables de entorno del proveedor legacy) que hay que ejecutar.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("Este script solo se ejecuta desde la linea de comandos.\n");
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$raiz = str_replace('/', '\\', dirname(__DIR__));

$origen  = $argv[1] ?? $raiz . '\\firma\\MARTIN_YANA.p12';
$destino = $argv[2] ?? $raiz . '\\firma\\MARTIN_YANA_MODERNO.p12';

if (!defined('SIAT_CERT_PASSWORD')) {
    require_once $raiz . '/config/siat.php';
}

$password = SIAT_CERT_PASSWORD;

/* NID_aes_256_cbc. Las constantes OPENSSL_CRYPTODREAM_* no existen en este build. */
const NID_AES_256_CBC = 419;

function leerP12($ruta, $password)
{
    $certs = array();
    $ok = @openssl_pkcs12_read(@file_get_contents($ruta), $certs, $password);
    while ($e = openssl_error_string()) { /* limpia la cola de errores */ }
    return $ok ? $certs : false;
}

function comandoConLegacy($origen, $destino)
{
    $raiz    = str_replace('/', '\\', dirname(__DIR__));
    $modulos = dirname(str_replace('/', '\\', ini_get('extension_dir'))) . '\\extras\\ssl';
    $cnf     = str_replace('/', '\\', __DIR__) . '\\ossl_legacy.cnf';
    return implode("\n", array(
        'cd ' . $raiz,
        '$env:OPENSSL_MODULES = "' . $modulos . '"',
        '$env:OPENSSL_CONF    = "' . $cnf . '"',
        'php firma\\convertir_certificado.php "' . $origen . '" "' . $destino . '"',
    ));
}

echo "=== Conversion de certificado digital .p12 ===\n\n";

if (!file_exists($origen)) {
    echo "[X] No existe el archivo origen: {$origen}\n";
    exit(1);
}
echo "Origen : {$origen}\n";
echo "Destino: {$destino}\n\n";

$certs = leerP12($origen, $password);

if ($certs === false) {
    echo "[X] No se pudo leer el origen.\n\n";
    echo "Casi siempre es porque el .p12 usa cifrado obsoleto (RC2 / 3DES + SHA-1)\n";
    echo "y OpenSSL 3 necesita el proveedor legacy para descifrarlo.\n\n";
    echo "Ejecuta esto en PowerShell y vuelve a correr el script:\n\n";
    echo comandoConLegacy($origen, $destino) . "\n";
    exit(1);
}

echo "[OK] Origen leido. Claves: " . implode(', ', array_keys($certs)) . "\n";

if (empty($certs['cert']) || empty($certs['pkey'])) {
    echo "[X] El origen no tiene certificado o clave privada.\n";
    exit(1);
}

$info = openssl_x509_parse($certs['cert']);
echo "     CN     : " . ($info['subject']['CN'] ?? '?') . "\n";
echo "     NIT    : " . ($info['subject']['serialNumber'] ?? '?') . "\n";
echo "     Emisor : " . ($info['issuer']['CN'] ?? '?') . "\n";
echo "     Vigente: " . date('Y-m-d', $info['validFrom_time_t']) . " -> " . date('Y-m-d', $info['validTo_time_t']) . "\n";
echo "     Cadena : " . (empty($certs['extracerts']) ? 'no includeda' : 'incluida (se perdera)') . "\n\n";

$opciones = array(
    'private_key_enc' => NID_AES_256_CBC,
    'certificate_enc' => NID_AES_256_CBC,
    'digest_alg'      => 'sha256',
);

$salida = null;
if (openssl_pkcs12_export($certs['cert'], $salida, $certs['pkey'], $password, $opciones) === false) {
    echo "[X] Fallo la exportacion.\n";
    while ($e = openssl_error_string()) { echo "    $e\n"; }
    exit(1);
}

if (!is_string($salida) || $salida === '') {
    echo "[X] La exportacion no devolvio contenido.\n";
    exit(1);
}

if (file_put_contents($destino, $salida) === false) {
    echo "[X] No se pudo escribir el destino: {$destino}\n";
    exit(1);
}

echo "[OK] Conversión escrita: {$destino} (" . strlen($salida) . " bytes)\n\n";

$verif = leerP12($destino, $password);
if ($verif === false) {
    echo "[X] El archivo nuevo no se puede leer. Revisa los permisos.\n";
    exit(1);
}

$k = openssl_pkey_get_private($verif['pkey']);
$info2 = openssl_x509_parse($verif['cert']);

echo "=== Verificacion del archivo generado ===\n";
echo "  Lectura            : OK\n";
echo "  Clave privada      : " . ($k ? 'OK' : 'FALLA') . "\n";
echo "  CN / NIT           : " . ($info2['subject']['CN'] ?? '?') . " / " . ($info2['subject']['serialNumber'] ?? '?') . "\n";
echo "  Vigente hasta      : " . date('Y-m-d', $info2['validTo_time_t']) . "\n";
echo "\n";

echo "Listo. Para que el sistema lo use, apunta SIAT_CERT_P12_PATH en config/siat.php\n";
echo "a este archivo. El .p12 original se conserva como respaldo.\n";
