<?php
// Script de debug definitivo para Etapa 2 - Sincronización de Catálogos
// Objetivo: Diagnosticar errores ocultos en sincronizarActividades/Productos/Leyendas
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/La_Paz");

// Configuración SIAT
define('SIAT_MODO', 'PILOTO');
define('SIAT_AMBIENTE', 2);
define('SIAT_MODALIDAD', 1);
define('SIAT_COD_SISTEMA', '373641B66F08A38C69CE');
define('SIAT_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJtYXJ0aW55YW5hMTc0QGdtYWlsLmNvbSIsImNvZGlnb1Npc3RlbWEiOiIzNzM2NDFCNjZGMDhBMzhDNjlDRSIsIm5pdCI6Ikg0c0lBQUFBQUFBQUFETXpNREF6TURBM01EUUdBTWxsYVhNS0FBQUEiLCJpZCI6NTA2MTYyNCwiZXhwIjoxNzkyNzg0OTI0LCJpYXQiOjE3ODYzMTkyOTQsIm5pdERlbGVnYWRvIjo2MDA2MDA3MDEzLCJzdWJzaXN0ZW1hIjoiU0ZFIn0.v8f1k7jyOF7lc7CaRdlAB799OGSqBI8gk33zwSUNN5Fb_sJvzXJr8oCV8bFBpQCaQS8aSIKcFSNoiHhGFygTAg');
define('SIAT_NIT', '6006007013');

define('SIAT_URLS', [
    'PILOTO' => [
        'sincronizacion_datos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'codigos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
    ],
]);

echo "=== DEBUG ETIQUETA 2 - Versión Definitiva ===\n\n";

// 1. Conexión a BD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=samfarm_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión BD OK\n\n";
} catch (PDOException $e) {
    die("❌ Error conexión BD: " . $e->getMessage() . "\n");
}

// 2. Obtener configuración desde BD
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_sucursal_codigo, sc_punto_venta_codigo FROM siat_configuracion WHERE sc_cuis IS NOT NULL AND sc_cuis != '' ORDER BY su_id LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_OBJ);

if (!$config) {
    die("❌ No existe configuración SIAT con CUIS válido\n");
}

echo "2. Configuración SIAT desde BD:\n";
echo "   su_id: {$config->su_id}\n";
echo "   CUIS: {$config->sc_cuis}\n";
echo "   sc_sucursal_codigo: {$config->sc_sucursal_codigo}\n";
echo "   sc_punto_venta_codigo: {$config->sc_punto_venta_codigo}\n";
echo "   NIT: " . SIAT_NIT . "\n";
echo "   códigoSistema: " . SIAT_COD_SISTEMA . "\n\n";

// 3. Crear cliente SOAP con autenticación
echo "3. Cliente SOAP inicializado\n";
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header' => "apikey: TokenApi " . SIAT_TOKEN . "\r\n",
        'timeout' => 30
    ]
]);

try {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['sincronizacion_datos'], [
        'stream_context' => $context,
        'trace' => 1,
        'exceptions' => true,
        'connection_timeout' => 60,
        'soap_version' => SOAP_1_1, // Forzado a SOAP 1.1 para compatibilidad con SIAT
    ]);
    echo "   ✅ Cliente creado (SOAP 1.1)\n\n";
} catch (Exception $e) {
    die("❌ Error creando cliente SOAP: " . $e->getMessage() . "\n");
}

// Función auxiliar para debug de respuestas SOAP
function debugSoapResponse($client, $methodName, $resp) {
    echo "   REQUEST SOAP:\n";
    echo "   " . str_repeat("-", 80) . "\n";
    $req = $client->__getLastRequest();
    foreach (explode("\n", $req) as $line) {
        echo "   " . htmlspecialchars($line) . "\n";
    }
    echo "\n   RESPONSE SOAP (RAW):\n";
    echo "   " . str_repeat("-", 80) . "\n";
    $resp_xml = $client->__getLastResponse();
    foreach (explode("\n", $resp_xml) as $line) {
        echo "   " . htmlspecialchars($line) . "\n";
    }
    echo "\n   RESPUESTA PROCESADA (objeto):\n";
    echo "   " . str_repeat("-", 80) . "\n";
    echo "   " . print_r($resp, true) . "\n";
    echo "   NODOS RAIZ DEL RESPONSE: " . json_encode(array_keys((array)$resp)) . "\n";
}

// 4. Test sincronizarFechaHora
echo "4. TEST sincronizarFechaHora:\n";
try {
    $resp = $client->sincronizarFechaHora([
        'SolicitudSincronizacion' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'cuis'             => $config->sc_cuis,
            'codigoSucursal'   => (int)$config->sc_sucursal_codigo,
            'codigoPuntoVenta' => (int)$config->sc_punto_venta_codigo
        ]
    ]);
    
    $root = $resp->RespuestaFechaHora ?? null;
    if ($root && !empty($root->fechaHora)) {
        $fechaSin = $root->fechaHora;
        echo "   ✅ Fecha obtenida: {$fechaSin}\n";
        $stmt = $pdo->prepare("UPDATE siat_configuracion SET sc_fecha_siat = ? WHERE su_id = ?");
        $stmt->execute([$fechaSin, $config->su_id]);
        echo "   ✅ Guardada sc_fecha_siat en BD\n";
    } else {
        echo "   ⚠️  Respuesta inesperada en fechaHora\n";
        debugSoapResponse($client, 'sincronizarFechaHora', $resp);
        if (isset($root->mensajesList)) { echo "   MENSAJES SIN:\n"; print_r($root->mensajesList); }
    }
} catch (SoapFault $e) {
    echo "   ❌ SoapFault: " . $e->getMessage() . "\n";
    if ($client) { echo "   REQ:\n".htmlspecialchars($client->__getLastRequest())."\n   RESP:\n".htmlspecialchars($client->__getLastResponse())."\n"; }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}
echo "\n";

// 5. Test sincronizarActividades
echo "5. TEST sincronizarActividades:\n";
try {
    $resp = $client->sincronizarListaActividadesDocumentoSector([
        'SolicitudSincronizacion' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'cuis'             => $config->sc_cuis,
            'codigoSucursal'   => (int)$config->sc_sucursal_codigo,
            'codigoPuntoVenta' => (int)$config->sc_punto_venta_codigo
        ]
    ]);
    
    $root = $resp->RespuestaListaActividadesDocumentoSector ?? null;
    if ($root && isset($root->listaActividadesDocumentoSector)) {
        $actividades = is_array($root->listaActividadesDocumentoSector) ? $root->listaActividadesDocumentoSector : [$root->listaActividadesDocumentoSector];
        echo "   ✅ NODO CORRECTO: RespuestaListaActividadesDocumentoSector\n";
        echo "   ✅ Actividades obtenidas: " . count($actividades) . "\n";
        $count = 0;
        foreach ($actividades as $act) {
            if (++$count > 3) break;
            echo "      - " . ($act->codigoActividad ?? 'N/A') . " / docSector=" . ($act->codigoDocumentoSector ?? 'N/A') . "\n";
        }
    } else {
        echo "   ⚠️  Respuesta del SIN sin listaActividadesDocumentoSector\n";
        debugSoapResponse($client, 'sincronizarListaActividadesDocumentoSector', $resp);
        if (isset($root->mensajesList)) {
            echo "   MENSAJES SIN:\n";
            print_r($root->mensajesList);
        }
    }
} catch (SoapFault $e) {
    echo "   ❌ SoapFault: " . $e->getMessage() . "\n";
    if ($client) { echo "   REQ:\n".htmlspecialchars($client->__getLastRequest())."\n   RESP:\n".htmlspecialchars($client->__getLastResponse())."\n"; }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}
echo "\n";

// 6. Test sincronizarProductos
echo "6. TEST sincronizarProductos:\n";
try {
    $resp = $client->sincronizarListaProductosServicios([
        'SolicitudSincronizacion' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'cuis'             => $config->sc_cuis,
            'codigoSucursal'   => (int)$config->sc_sucursal_codigo,
            'codigoPuntoVenta' => (int)$config->sc_punto_venta_codigo,
            'codigoActividad'  => '477000'
        ]
    ]);
    
    $root = $resp->RespuestaListaProductos ?? null;
    if ($root && isset($root->listaProductos)) {
        $productos = is_array($root->listaProductos) ? $root->listaProductos : [$root->listaProductos];
        echo "   ✅ Productos obtenidos: " . count($productos) . "\n";
    } else {
        echo "   ⚠️  Respuesta SIN (nodo RespuestaListaProductos). En PILOTO suele venir VACIO.\n";
        debugSoapResponse($client, 'sincronizarListaProductosServicios', $resp);
        if (isset($root->mensajesList)) { echo "   MENSAJES SIN:\n"; print_r($root->mensajesList); }
    }
} catch (SoapFault $e) {
    echo "   ❌ SoapFault: " . $e->getMessage() . "\n";
    if ($client) { echo "   REQ:\n".htmlspecialchars($client->__getLastRequest())."\n   RESP:\n".htmlspecialchars($client->__getLastResponse())."\n"; }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}
echo "\n";

// 7. Test sincronizarLeyendas
echo "7. TEST sincronizarLeyendas:\n";
try {
    $resp = $client->sincronizarListaLeyendasFactura([
        'SolicitudSincronizacion' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'cuis'             => $config->sc_cuis,
            'codigoSucursal'   => (int)$config->sc_sucursal_codigo,
            'codigoPuntoVenta' => (int)$config->sc_punto_venta_codigo
        ]
    ]);
    
    $root = $resp->RespuestaListaParametricasLeyendas ?? null;
    if ($root && isset($root->listaLeyendas)) {
        $leyendas = is_array($root->listaLeyendas) ? $root->listaLeyendas : [$root->listaLeyendas];
        echo "   ✅ Leyendas obtenidas: " . count($leyendas) . "\n";
    } else {
        echo "   ⚠️  Respuesta SIN (nodo RespuestaListaParametricasLeyendas). En PILOTO suele venir VACIO.\n";
        debugSoapResponse($client, 'sincronizarListaLeyendasFactura', $resp);
        if (isset($root->mensajesList)) { echo "   MENSAJES SIN:\n"; print_r($root->mensajesList); }
    }
} catch (SoapFault $e) {
    echo "   ❌ SoapFault: " . $e->getMessage() . "\n";
    if ($client) { echo "   REQ:\n".htmlspecialchars($client->__getLastRequest())."\n   RESP:\n".htmlspecialchars($client->__getLastResponse())."\n"; }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== FIN DEBUG ===\n";
