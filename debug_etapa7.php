<?php
/**
 * DEBUG Etapa 7 - Anulacion de facturas ante el SIN.
 *
 * Etapa 1: CUIS de la sucursal / punto de venta
 * Etapa 2: catalogos (actividades, productos, leyendas, parametricas)
 * Etapa 4: firma del XML
 * Etapa 5: evento significativo
 * Etapa 6: emision en contingencia -> TAR.GZ -> recepcion -> validacion
 * Etapa 7: anulacion de una factura ya aceptada por el SIN   <- este archivo
 *
 * Que se prueba aca
 * ------------------
 * La operacion anulacionFactura del ServicioFacturacionCompraVenta. A diferencia
 * de la etapa 6, aqui no se manda un archivo: se manda el CUF de UNA factura que
 * el SIN ya acepto y se le pide que la reversa.
 *
 * Contrato tomado del WSDL del piloto (no de memoria):
 *
 *   <xs:complexType name="anulacionFactura">
 *     <SolicitudServicioAnulacionFactura type="tns:solicitudAnulacion"/>
 *
 *   solicitudAnulacion extiende solicitudRecepcion y agrega:
 *     codigoMotivo (int)   - motivo de la anulacion
 *     cuf (string)         - CUF de la factura a anular
 *
 *   solicitudRecepcion trae:
 *     codigoAmbiente, codigoDocumentoSector, codigoEmision, codigoModalidad,
 *     codigoPuntoVenta (opcional), codigoSistema, codigoSucursal, cufd, cuis,
 *     nit, tipoFacturaDocumento
 *
 * OJO con dos cosas del WSDL del piloto:
 *   1. No existen codigoRecepcion ni numeroFactura en esta solicitud. La
 *      identificacion de la factura va solo por el CUF.
 *   2. La respuesta se llama igual RespuestaServicioFacturacion, y trae
 *      codigoEstado / codigoDescripcion / codigoRecepcion / transaccion /
 *      mensajesList. Sin transaccion en true la anulacion no se confirmo.
 *
 * Respuestas observadas contra el piloto, con este mismo script:
 *   905 - Anulacion confirmada, transaccion en true
 *   906 - Anulacion rechazada, con un mensaje de detalle
 *   691 - La factura quedo ANULADA (esto lo devuelve verificacionEstadoFactura)
 *   924 - La factura o nota no existe en la base de datos del SIN
 *   936 - La factura ya se encuentra anulada
 *
 * El catalogo de errores oficial menciona tambien 934 (anulacion fuera de plazo)
 * y 946 (el CUF no existe). El piloto responde 924 en vez de 946 cuando el CUF
 * no corresponde a ninguna factura, asi que el codigo de detalle hay que
 * leerlo del mensaje, no suponerlo.
 *
 * codigoEmision
 * -------------
 * Las facturas de las etapas 1 a 6 se emitieron en contingencia, con
 * codigoEmision=2. La anulacion se mando con codigoEmision=1 (electronica en
 * linea) y el SIN la acepto sin pestanear. Por si en otro escenario el SIN
 * exigiera el mismo valor que la emision, esta expuesto con --emision=N.
 *
 * De donde salen las facturas para anular
 * --------------------------------------
 * Las etapas 1 a 6 no dejaron facturas en la base local: la tabla
 * facturacion_electronica tiene FK a factura (fk_fe_fa), asi que escribir ahi
 * obligaria a crear venta y factura reales. Por eso las facturas de
 * certificacion solo existen como XML firmado dentro de un TAR.GZ.
 *
 * Los CUFs si quedaron guardados: siat_paquetes.pa_facturas es un JSON con
 * [{numero, cuf}] por paquete. Este script lee ahi para elegir a quien anular,
 * asi que annula facturas que el SIN acepto de verdad (paquetes con 908
 * VALIDADA) sin tocar el modulo de ventas.
 *
 * Uso:
 *   php debug_etapa7.php --listar              facturas candidatas
 *   php debug_etapa7.php --paquete=44          anula la primera de ese paquete
 *   php debug_etapa7.php --cuf=ABC...          anula un CUF puntual
 *   php debug_etapa7.php --pv=1 --indice=10    anula la 11a factura del pv 1
 *   php debug_etapa7.php --motivo=2            motivo de anulacion (default 1)
 *   php debug_etapa7.php --emision=2           codigoEmision a enviar (default 1)
 *   php debug_etapa7.php --sin-verificar       no llama verificacionEstadoFactura
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
date_default_timezone_set('America/La_Paz');

// Configuracion SIAT: se toma de config/siat.php, que es la fuente unica que
// usa models/siatModel.php. Si este script trajera sus propias constantes
// mandaria otro ambiente, modalidad o sistema del que se emitieron los CUFDs y
// el SIN responderia 914 o 959.
require_once __DIR__ . '/config/siat.php';

if (!defined('SIAT_NIT')) {
    define('SIAT_NIT', '6006007013');
}

$op = getopt('', [
    'listar', 'paquete::', 'cuf::', 'pv::', 'indice::', 'motivo::', 'emision::',
    'sin-verificar', 'ayuda',
]);

/* Motivo de anulacion. El catalogo oficial va mas alla de estos tres, asi que
   --motivo acepta cualquier entero; el script solo lo_documenta_. */
$MOTIVOS = [
    1 => 'Error en la factura',
    2 => 'Devolucion de producto',
    3 => 'Otros',
];

echo "=== DEBUG ETAPA 7 - ANULACION DE FACTURAS ===\n\n";

if (isset($op['ayuda'])) {
    echo "Uso: php debug_etapa7.php --listar | --paquete=N | --cuf=... | --pv=N --indice=N\n";
    echo "     --motivo=N --emision=N --sin-verificar\n";
    exit(0);
}

// --- 1. Conexion a BD ------------------------------------------------------
try {
    $pdo = new PDO('mysql:host=localhost;dbname=samfarm_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "OK  Conexion BD\n";
} catch (PDOException $e) {
    die("ERROR conexion BD: " . $e->getMessage() . "\n");
}

/* --- 2. Credenciales por punto de venta -----------------------------------
   Las facturas de certificacion salieron de dos cajas: la 0 (pv_id 1, que toma
   el CUIS y CUFD de siat_configuracion) y la 1 (pv_id 2, que ya esta
   registrada en el SIN y tiene CUIS y CUFD propios en puntos_venta). La caja se
   deduce del prefijo del evento: ev_codigo tiene la forma CERT-<pv>-M<motivo>-... */
function credenciales($pdo, $codigoPuntoVenta)
{
    if ($codigoPuntoVenta == 0) {
        $r = $pdo->query("SELECT sc_cuis, sc_cufd, sc_cufd_vigente_hasta
                          FROM siat_configuracion WHERE su_id = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    } else {
        $r = $pdo->query("SELECT pv_cuis, pv_cufd, pv_cufd_vigente_hasta
                          FROM puntos_venta
                          WHERE su_id = 1 AND (pv_codigo_sin = $codigoPuntoVenta
                                            OR pv_codigo = $codigoPuntoVenta)
                          LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }

    if (!$r || empty($r['sc_cuis'] ?? $r['pv_cuis']) || empty($r['sc_cufd'] ?? $r['pv_cufd'])) {
        throw new Exception("La caja con codigoPuntoVenta={$codigoPuntoVenta} no tiene CUIS ni CUFD.");
    }

    return [
        'cuis'            => $r['sc_cuis'] ?? $r['pv_cuis'],
        'cufd'            => $r['sc_cufd'] ?? $r['pv_cufd'],
        'vigente_hasta'   => $r['sc_cufd_vigente_hasta'] ?? $r['pv_cufd_vigente_hasta'],
    ];
}

/* Paquetes aceptados por el SIN: son los unicos con facturas que existen de
   verdad alla y por lo tanto los unicos que se pueden anular. */
function paquetesValidados($pdo, $pv = null)
{
    $filtro = '';
    if ($pv !== null) {
        $filtro = " AND ev.ev_codigo LIKE 'CERT-{$pv}-%'";
    }

    return $pdo->query("
        SELECT pa.pa_id, pa.pa_codigo_recepcion, pa.pa_cantidad_facturas, pa.pa_ev_codigo,
               ev.ev_codigo_evento, ev.ev_fecha_inicio, ev.ev_cufd_evento,
               pa.pa_facturas
        FROM siat_paquetes pa
        JOIN siat_eventos ev ON ev.ev_codigo = pa.pa_ev_codigo
        WHERE pa.pa_estado = 'validado'
          AND pa.pa_codigo_recepcion IS NOT NULL AND pa.pa_codigo_recepcion <> ''
          $filtro
        ORDER BY pa.pa_id
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/* El SIN devuelve mensajesList como un objeto suelto cuando hay uno solo y
   como arreglo cuando hay varios. (array) sobre un stdClass devuelve
   ['mensajesList' => ...], no la lista de mensajes, asi que hay que aplanar a
   mano en los dos casos. */
function aplanarMensajes($nodo)
{
    if ($nodo === null) {
        return [];
    }
    if (is_array($nodo)) {
        return $nodo;
    }
    if (isset($nodo->codigo) || isset($nodo->descripcion)) {
        return [$nodo];
    }
    $interno = (array_values((array)$nodo)[0] ?? null);
    return $interno === null ? [] : aplanarMensajes($interno);
}

// --- 3. Modo --listar ------------------------------------------------------
if (isset($op['listar'])) {
    $pv = isset($op['pv']) ? (int)$op['pv'] : null;
    $paquetes = paquetesValidados($pdo, $pv);

    echo "\nPaquetes validados por el SIN (" . count($paquetes) . "):\n";
    echo "  " . str_pad('pa_id', 7) . str_pad('pv', 4) . str_pad('n', 7)
       . str_pad('motivo', 8) . "recepcion / rango de CUFs\n";

    foreach ($paquetes as $pk) {
        $partes = explode('-', $pk['pa_ev_codigo']);
        $pvCod = (int)($partes[1] ?? 0);
        $facturas = json_decode($pk['pa_facturas'] ?? '[]', true) ?: [];
        $primero = $facturas[0]['cuf'] ?? '-';
        $ultimo  = $facturas ? end($facturas)['cuf'] : '-';

        printf("  %-7s%-4s%-7s%-8s%s\n       %s ... %s\n",
            $pk['pa_id'], $pvCod, count($facturas), $pk['ev_codigo_evento'],
            $pk['pa_codigo_recepcion'], substr((string)$primero, 0, 40), substr((string)$ultimo, 0, 40));
    }
    echo "\nPara anular:  php debug_etapa7.php --paquete=" . ($paquetes[0]['pa_id'] ?? 'N') . "\n";
    exit(0);
}

// --- 4. Elegir la factura a anular -----------------------------------------
$cufObjetivo   = $op['cuf'] ?? null;
$indice       = isset($op['indice']) ? (int)$op['indice'] : 0;
$pvFiltro     = isset($op['pv']) ? (int)$op['pv'] : null;
$paqueteFiltro = isset($op['paquete']) ? (int)$op['paquete'] : null;
$codigoMotivo = isset($op['motivo']) ? (int)$op['motivo'] : 1;
$codigoEmision = isset($op['emision']) ? (int)$op['emision'] : 1;

$origen = '';
$pvCodigo = $pvFiltro;

if ($cufObjetivo) {
    $origen = 'CUF indicado por linea de comandos';
    echo "\nFactora a anular (por --cuf):\n  {$cufObjetivo}\n";

    // El punto de venta hay que deducirlo del CUF: los de las cajas 0 y 1
    // difieren en el bloque inicial, pero lo mas seguro es buscarlo en los
    // paquetes guardados.
    foreach (paquetesValidados($pdo, $pvFiltro) as $pk) {
        $partes = explode('-', $pk['pa_ev_codigo']);
        foreach ((json_decode($pk['pa_facturas'] ?? '[]', true) ?: []) as $f) {
            if (($f['cuf'] ?? '') === $cufObjetivo) {
                $pvCodigo = (int)($partes[1] ?? 0);
                $origen = "paquete {$pk['pa_id']}, recepcion {$pk['pa_codigo_recepcion']}";
                break 2;
            }
        }
    }
} else {
    $sqlPaquete = '';
    $params = [];
    if ($paqueteFiltro) {
        $sqlPaquete = ' AND pa.pa_id = :pa_id';
        $params[':pa_id'] = $paqueteFiltro;
    }
    if ($pvFiltro !== null) {
        $sqlPaquete .= " AND ev.ev_codigo LIKE 'CERT-{$pvFiltro}-%'";
    }

    $stmt = $pdo->prepare("
        SELECT pa.pa_id, pa.pa_codigo_recepcion, pa.pa_facturas, pa.pa_ev_codigo,
               ev.ev_codigo_evento
        FROM siat_paquetes pa
        JOIN siat_eventos ev ON ev.ev_codigo = pa.pa_ev_codigo
        WHERE pa.pa_estado = 'validado'
          AND pa.pa_codigo_recepcion IS NOT NULL AND pa.pa_codigo_recepcion <> ''
          $sqlPaquete
        ORDER BY pa.pa_id
        LIMIT 1
    ");
    $stmt->execute($params);
    $pk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pk) {
        die("\nNo hay ningun paquete validado que corresponda. Corre primero:\n"
            . "  php siat_certificacion.php --listar\n"
            . "  php debug_etapa6.php\n");
    }

    $facturas = json_decode($pk['pa_facturas'] ?? '[]', true) ?: [];
    if (!$facturas) {
        die("\nEl paquete {$pk['pa_id']} no tiene lista de facturas (pa_facturas vacio).\n");
    }
    if ($indice >= count($facturas)) {
        die("\nEl paquete {$pk['pa_id']} tiene " . count($facturas)
            . " facturas; --indice={$indice} no existe.\n");
    }

    $elegida = $facturas[$indice];
    $cufObjetivo = $elegida['cuf'];
    $pvCodigo = (int)(explode('-', $pk['pa_ev_codigo'])[1] ?? 0);
    $origen = "paquete {$pk['pa_id']} (motivo {$pk['ev_codigo_evento']}), indice {$indice} de "
        . count($facturas) . ", recepcion {$pk['pa_codigo_recepcion']}";
}

if ($pvCodigo === null) {
    die("\nNo se pudo determinar el punto de venta de la factura. Indica --pv=0 o --pv=1.\n");
}

echo "\nFactora elegida:\n";
echo "  CUF           : {$cufObjetivo}\n";
echo "  Origen        : {$origen}\n";
echo "  Punto de venta: {$pvCodigo}\n";
echo "  codigoMotivo  : {$codigoMotivo} (" . ($MOTIVOS[$codigoMotivo] ?? 'fuera del catalogo corto') . ")\n";
echo "  codigoEmision : {$codigoEmision}\n";

// --- 5. Credenciales -------------------------------------------------------
try {
    $cred = credenciales($pdo, $pvCodigo);
} catch (Exception $e) {
    die("\nERROR: " . $e->getMessage() . "\n");
}

echo "\nCredenciales de la caja {$pvCodigo}:\n";
echo "  CUIS          : {$cred['cuis']}\n";
echo "  CUFD vigente  : " . substr($cred['cufd'], 0, 60) . "...\n";
echo "  vigente hasta : {$cred['vigente_hasta']}\n";

// El anulacion es una operacion en linea: se manda con el CUFD vigente, no con
// el cufdEvento de la contingencia en que se emitio la factura.

// --- 6. Cliente SOAP -------------------------------------------------------
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header' => 'apikey: TokenApi ' . SIAT_TOKEN . "\r\n",
        'timeout' => 60,
    ],
]);

try {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['compra_venta'], [
        'stream_context'     => $context,
        'trace'              => 1,
        'exceptions'         => true,
        'connection_timeout' => 60,
        'soap_version'       => SOAP_1_1,
    ]);
    echo "\nOK  Cliente SOAP (compra_venta)\n";
} catch (Exception $e) {
    die("\nERROR cliente SOAP: " . $e->getMessage() . "\n");
}

// --- 7. Solicitud de anulacion --------------------------------------------
$solicitud = ['SolicitudServicioAnulacionFactura' => [
    'codigoAmbiente'        => SIAT_AMBIENTE,
    'codigoModalidad'       => SIAT_MODALIDAD,
    'codigoSistema'         => SIAT_COD_SISTEMA,
    'codigoSucursal'        => 0,
    'codigoPuntoVenta'      => $pvCodigo,
    'codigoDocumentoSector' => 1,
    'tipoFacturaDocumento'  => 1,
    'codigoEmision'         => $codigoEmision,
    'cuis'                  => $cred['cuis'],
    'cufd'                  => $cred['cufd'],
    'nit'                   => SIAT_NIT,
    'codigoMotivo'          => $codigoMotivo,
    'cuf'                   => $cufObjetivo,
]];

echo "\nSolicitud enviada:\n";
foreach ($solicitud['SolicitudServicioAnulacionFactura'] as $k => $v) {
    $v = is_string($v) && strlen($v) > 60 ? substr($v, 0, 60) . '...' : $v;
    echo "  " . str_pad($k, 24) . $v . "\n";
}

echo "\nEnviando anulacionFactura al SIN...\n";

$codigoEstado = null;
$transaccion  = null;

try {
    $respuesta = $client->anulacionFactura($solicitud);

    echo "\n--- Respuesta cruda ---\n";
    echo $client->__getLastResponse() . "\n";

    // La respuesta viene envuelta en RespuestaServicioFacturacion. Leyendo el
    // nivel superior, transaccion y codigoEstado salian vacios y el script
    //eria una anulacion correcta.
    $r = $respuesta->RespuestaServicioFacturacion ?? $respuesta;

    $transaccion  = $r->transaccion ?? null;
    $codigoEstado = $r->codigoEstado ?? null;
    $descripcion  = $r->codigoDescripcion ?? '';

    echo "\n--- Resultado ---\n";
    echo "  transaccion    : " . var_export($transaccion, true) . "\n";
    echo "  codigoEstado   : " . var_export($codigoEstado, true) . "\n";
    echo "  codigoDescric. : {$descripcion}\n";
    if (!empty($r->codigoRecepcion)) {
        echo "  codigoRecepcion: {$r->codigoRecepcion}\n";
    }

    $mensajes = aplanarMensajes($r->mensajesList ?? null);

    if ($mensajes) {
        echo "  mensajes       :\n";
        foreach ($mensajes as $m) {
            $m = (array)$m;
            echo "     " . ($m['codigo'] ?? '?') . ': ' . ($m['descripcion'] ?? '?') . "\n";
        }
    }

    $confirmada = !empty($transaccion) && (int)$codigoEstado === 905;
    echo "\n  VEREDICTO: " . ($confirmada
        ? "ANULACION CONFIRMADA (905)"
        : "ANULACION NO CONFIRMADA") . "\n";

    if (!$confirmada) {
        echo "\n  Si el codigo es 946 el CUF no existe en el SIN; 934 significa fuera de\n";
        echo "  plazo de anulacion; 936 que ya estaba anulada. Para una factura de\n";
        echo "  contingencia, si el SIN la rechaza con codigoEmision=1, reintenta con\n";
        echo "  --emision=2 (la factura se emitio en contingencia, codigoEmision=2).\n";
    }
} catch (SoapFault $e) {
    echo "\nERROR SOAP: " . $e->getMessage() . "\n";
    echo "\n--- Request enviado ---\n";
    echo $client->__getLastRequest() . "\n";
    echo "\n--- Response recibida ---\n";
    echo ($client->__getLastResponse() ?: '(vacia)') . "\n";
}

// --- 8. Verificacion del estado de la factura ------------------------------
//
// anulacionFactura responde si la operacion se registro, pero no siempre
// confirma el estado final. verificacionEstadoFactura consulta el estado por CUF
// y es la que dice si la factura quedo realmente anulada.
if (!isset($op['sin-verificar'])) {
    echo "\n" . str_repeat('-', 60) . "\n";
    echo "Verificacion del estado de la factura (verificacionEstadoFactura)\n";
    echo str_repeat('-', 60) . "\n";

    try {
        $v = $client->verificacionEstadoFactura(['SolicitudServicioVerificacionEstadoFactura' => [
            'codigoAmbiente'        => SIAT_AMBIENTE,
            'codigoModalidad'       => SIAT_MODALIDAD,
            'codigoSistema'         => SIAT_COD_SISTEMA,
            'codigoSucursal'        => 0,
            'codigoPuntoVenta'      => $pvCodigo,
            'codigoDocumentoSector' => 1,
            'tipoFacturaDocumento'  => 1,
            'codigoEmision'         => $codigoEmision,
            'cuis'                  => $cred['cuis'],
            'cufd'                  => $cred['cufd'],
            'nit'                   => SIAT_NIT,
            'cuf'                   => $cufObjetivo,
        ]]);

        $rv = $v->RespuestaServicioFacturacion ?? $v;
        echo "  codigoEstado   : " . var_export($rv->codigoEstado ?? null, true) . "\n";
        echo "  descripcion    : " . ($rv->codigoDescripcion ?? '') . "\n";
        echo "  transaccion    : " . var_export($rv->transaccion ?? null, true) . "\n";

        $vmsgs = aplanarMensajes($rv->mensajesList ?? null);
        if ($vmsgs) {
            echo "  mensajes       :\n";
            foreach ($vmsgs as $m) {
                $m = (array)$m;
                echo "     " . ($m['codigo'] ?? '?') . ': ' . ($m['descripcion'] ?? '?') . "\n";
            }
        }
    } catch (SoapFault $e) {
        echo "  ERROR SOAP: " . $e->getMessage() . "\n";
    }
}

echo "\n=== FIN ETAPA 7 ===\n";
