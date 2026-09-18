<?php
// DEBUG Etapa 5 - Registro de Eventos Significativos (Validación de Sistema SIAT)
// Basado en el patrón de debug_etapa4.php (mismas conexiones, config y cliente SOAP)

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/La_Paz");

// Configuración SIAT
define('SIAT_MODO', 'PILOTO');
define('SIAT_AMBIENTE', 2);
define('SIAT_MODALIDAD', 1);
define('SIAT_COD_SISTEMA', '373641B66F08A38C69CE');
define('SIAT_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJtYXJ0aW55YW5hMTc0QGdtYWlsLmNvbSIsImNvZGlnb1Npc3RlbWEiOiIzNzM2NDFCNjZGMDhBMzhDNjlDRSIsIm5pdCI6Ikg0c0lBQUFBQUFBQUFETXpNREF6TURBM01EUUdBTWxsYVhNS0FBQUEiLCJpZCI6NTA2MTYyNCwiZXhwIjoxNzkyNzg0OTI0LCJpYXQiOjE3ODYzMTkyOTQsIm5pdERlbGVnYWRvIjo2MDA2MDA3MDEzLCJzdWJzaXN0ZW1hIjoiU0ZFIn0.v8f1k7jyOF7lc7CaRdlAB799OGSqBI8gk33zwSUNN5Fb_sJvzXJr8oCV8bFBpQCaQS8aSIKcFSNoiHhGFygTAgeyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJtYXJ0aW55YW5hMTc0QGdtYWlsLmNvbSIsImNvZGlnb1Npc3RlbWEiOiIzNzM2NDFCNjZGMDhBMzhDNjlDRSIsIm5pdCI6Ikg0c0lBQUFBQUFBQUFETXpNREF6TURBM01EUUdBTWxsYVhNS0FBQUEiLCJpZCI6NTA2MTYyNCwiZXhwIjoxNzkyNzg0OTI0LCJpYXQiOjE3ODYzMTkyOTQsIm5pdERlbGVnYWRvIjo2MDA2MDA3MDEzLCJzdWJzaXN0ZW1hIjoiU0ZFIn0.v8f1k7jyOF7lc7CaRdlAB799OGSqBI8gk33zwSUNN5Fb_sJvzXJr8oCV8bFBpQCaQS8aSIKcFSNoiHhGFygTAg');
define('SIAT_NIT', '6006007013');

define('SIAT_URLS', [
    'PILOTO' => [
        'sincronizacion_datos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'codigos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
    ],
]);

// Catálogo de Eventos Significativos (Anexo Técnico SIAT)
// 1=Corte de Internet, 2=Inaccesibilidad Web SIN, 3=Zonas sin Internet (vehículos),
// 4=Venta en lugares sin Internet, 5=Corte de energía, 6=Virus/Falla software,
// 7=Cambio de infraestructura/Falla hardware
$codigoEvento = 1; // TODO: cambiar según el caso de prueba del Excel
$descripcionEvento = 'Corte del Servicio de Internet';

echo "=== DEBUG ETAPA 5 - Registro de Eventos Significativos ===\n\n";

// 1. Conexión a BD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=samfarm_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión BD OK\n\n";
} catch (PDOException $e) {
    die("❌ Error conexión BD: " . $e->getMessage() . "\n");
}

// 2. Obtener configuración SIAT
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cufd, sc_sucursal_codigo, sc_punto_venta_codigo FROM siat_configuracion WHERE sc_cuis IS NOT NULL AND sc_cuis != '' LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_OBJ);

if (!$config) {
    die("❌ No existe configuración SIAT con CUIS válido\n");
}

$suId = $config->su_id;
$cuis = $config->sc_cuis;
$cufd = $config->sc_cufd;
$codigoSucursal = (int)($config->sc_sucursal_codigo ?? 0);
$codigoPuntoVenta = (int)($config->sc_punto_venta_codigo ?? 0);

echo "Configuración SIAT:\n";
echo "   su_id: {$suId}\n";
echo "   CUIS: {$cuis}\n";
echo "   CUFD: {$cufd}\n";
echo "   Sucursal: {$codigoSucursal}\n";
echo "   PtoVenta: {$codigoPuntoVenta}\n\n";

// 3. Crear cliente SOAP (servicio de OPERACIONES, no compra_venta)
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header' => "apikey: TokenApi " . SIAT_TOKEN . "\r\n",
        'timeout' => 30
    ]
]);

try {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['operaciones'], [
        'stream_context' => $context,
        'trace' => 1,
        'exceptions' => true,
        'connection_timeout' => 60,
        'soap_version' => SOAP_1_1,
    ]);
    echo "✅ Cliente SOAP (operaciones) creado\n\n";
} catch (Exception $e) {
    die("❌ Error cliente SOAP: " . $e->getMessage() . "\n");
}

// 4. Parámetros del evento significativo
// Fechas en formato UTC extendido SIN zona horaria: yyyy-MM-dd'T'HH:mm:ss.SSS
$fechaInicioEvento = '2026-08-18T08:00:00.000';
$fechaFinEvento    = '2026-08-18T09:00:00.000';

// cufdEvento = CUFD vigente al momento de la contingencia (usamos el actual de config)
$cufdEvento = $cufd;

echo "Evento a registrar:\n";
echo "   códigoEvento: {$codigoEvento} ({$descripcionEvento})\n";
echo "   fechaInicio:  {$fechaInicioEvento}\n";
echo "   fechaFin:     {$fechaFinEvento}\n";
echo "   cufdEvento:   {$cufdEvento}\n\n";

$solicitud = [
    'SolicitudEventoSignificativo' => [
        'codigoAmbiente'     => SIAT_AMBIENTE,
        'codigoSistema'      => SIAT_COD_SISTEMA,
        'nit'                => SIAT_NIT,
        'cuis'               => $cuis,
        'cufd'               => $cufd,
        'codigoSucursal'     => $codigoSucursal,
        'codigoPuntoVenta'   => $codigoPuntoVenta,
        'codigoEvento'       => $codigoEvento,
        'codigoMotivoEvento' => 1,
        'descripcion'        => $descripcionEvento,
        'fechaInicioEvento'  => $fechaInicioEvento,
        'fechaFinEvento'     => $fechaFinEvento,
        'cufdEvento'         => $cufdEvento,
    ]
];

echo "➡️  Enviando registroEventoSignificativo al SIAT...\n";

try {
    $response = $client->registroEventoSignificativo($solicitud);

    echo "✅ Respuesta SIAT:\n";
    echo $client->__getLastResponse() . "\n";

    // Intentar mostrar campos útiles de la respuesta
    $resultVar = 'RespuestaEventoSignificativo';
    if (isset($response->$resultVar)) {
        $r = $response->$resultVar;
        echo "\n--- Datos de respuesta ---\n";
        echo "transaccion:      " . var_export($r->transaccion ?? null, true) . "\n";
        echo "codigoRecepcion:  " . ($r->codigoRecepcion ?? 'N/A') . "\n";
        if (!empty($r->mensajes)) {
            echo "mensajes:\n";
            foreach ((array)$r->mensajes as $m) {
                echo "   - " . print_r($m, true) . "\n";
            }
        }
    }
} catch (SoapFault $e) {
    echo "❌ Error SOAP: " . $e->getMessage() . "\n";
    echo "Request enviado:\n" . $client->__getLastRequest() . "\n";
}

echo "\n=== FIN DEBUG ===\n";
