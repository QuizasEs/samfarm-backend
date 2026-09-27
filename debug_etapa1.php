<?php
// DEBUG Etapa 1 - Obtención de CUIS y CUFD (Autorización SIN)
// Estructura basada en debug_etapa2.php / debug_etapa4.php

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

echo "=== DEBUG ETAPA 1 - Obtención CUIS y CUFD ===\n\n";

// 1. Conexión a BD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=samfarm_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión BD OK\n\n";
} catch (PDOException $e) {
    die("❌ Error conexión BD: " . $e->getMessage() . "\n");
}

// 2. Obtener configuración desde BD
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cufd, sc_sucursal_codigo, sc_punto_venta_codigo FROM siat_configuracion WHERE su_id = 1 LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_OBJ);

if (!$config) {
    die("❌ No existe configuración SIAT para su_id=1\n");
}

echo "2. Configuración SIAT desde BD:\n";
echo "   su_id: {$config->su_id}\n";
echo "   sc_cuis (actual): " . ($config->sc_cuis ?? 'NULL') . "\n";
echo "   sc_cufd (actual): " . ($config->sc_cufd ?? 'NULL') . "\n";
echo "   sc_sucursal_codigo: {$config->sc_sucursal_codigo}\n";
echo "   sc_punto_venta_codigo: {$config->sc_punto_venta_codigo}\n";
echo "   NIT: " . SIAT_NIT . "\n";
echo "   códigoSistema: " . SIAT_COD_SISTEMA . "\n\n";

// 3. Crear cliente SOAP (servicio CODIGOS para CUIS/CUFD)
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header' => "apikey: TokenApi " . SIAT_TOKEN . "\r\n",
        'timeout' => 30
    ]
]);

try {
    $client = new SoapClient(SIAT_URLS[SIAT_MODO]['codigos'], [
        'stream_context' => $context,
        'trace' => 1,
        'exceptions' => true,
        'connection_timeout' => 60,
        'soap_version' => SOAP_1_1,
    ]);
    echo "3. ✅ Cliente SOAP (codigos) creado\n\n";
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

// ===========================================
// ETAPA 1-A: OBTENER CUIS
// ===========================================
echo "4. ETAPA 1-A: OBTENER CUIS\n";

$cuisActual = $config->sc_cuis;
echo "   CUIS actual en BD: " . ($cuisActual ?? 'NULL') . "\n";

if ($cuisActual && $cuisActual !== 'CUIS_PRUEBA_001') {
    echo "   ⚠️  Ya existe CUIS real en BD. ¿Sobrescribir? (continúa de todos modos)\n";
}

try {
    $resp = $client->cuis([
        'SolicitudCuis' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'codigoSucursal'   => (int)$config->sc_sucursal_codigo,
            'codigoPuntoVenta' => (int)$config->sc_punto_venta_codigo
        ]
    ]);

    $r = $resp->RespuestaCuis ?? null;
    
    if ($r && isset($r->codigo) && trim($r->codigo) !== '') {
        $nuevoCuis = $r->codigo;
        $vigencia = $r->fechaVigencia ?? null;
        
        echo "   ✅ CUIS OBTENIDO: {$nuevoCuis}\n";
        if ($vigencia) echo "   Vigencia: {$vigencia}\n";
        
        // Guardar en BD
        $stmt = $pdo->prepare("
            UPDATE siat_configuracion 
            SET sc_cuis = :cuis, sc_cuis_vigente_hasta = :vigencia, sc_actualizado_en = NOW()
            WHERE su_id = :su_id
        ");
        $stmt->execute([
            ':cuis' => $nuevoCuis,
            ':vigencia' => $vigencia,
            ':su_id' => $config->su_id
        ]);
        echo "   ✅ Guardado en siat_configuracion (sc_cuis, sc_cuis_vigente_hasta)\n";
        
        $cuisActual = $nuevoCuis;
    } else {
        echo "   ❌ Respuesta sin CUIS válido\n";
        debugSoapResponse($client, 'cuis', $resp);
        if (!empty($r->mensajesList)) {
            echo "   MENSAJES SIN:\n";
            print_r($r->mensajesList);
        }
    }
} catch (SoapFault $e) {
    echo "   ❌ SoapFault: " . $e->getMessage() . "\n";
    if ($client) { echo "   REQ:\n".htmlspecialchars($client->__getLastRequest())."\n   RESP:\n".htmlspecialchars($client->__getLastResponse())."\n"; }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
}
echo "\n";

// ===========================================
// ETAPA 1-B: OBTENER CUFD
// ===========================================
echo "5. ETAPA 1-B: OBTENER CUFD (requiere CUIS válido)\n";

if (!$cuisActual || $cuisActual === 'CUIS_PRUEBA_001') {
    echo "   ⚠️  Saltando CUFD: no hay CUIS real (usa CUIS_PRUEBA_001 o NULL)\n";
} else {
    $cufdActual = $config->sc_cufd;
    echo "   CUFD actual en BD: " . ($cufdActual ?? 'NULL') . "\n";
    
    try {
        $resp = $client->cufd([
            'SolicitudCufd' => [
                'codigoAmbiente'   => SIAT_AMBIENTE,
                'codigoModalidad'  => SIAT_MODALIDAD,
                'codigoSistema'    => SIAT_COD_SISTEMA,
                'nit'              => SIAT_NIT,
                'cuis'             => $cuisActual,
                'codigoSucursal'   => (int)$config->sc_sucursal_codigo,
                'codigoPuntoVenta' => (int)$config->sc_punto_venta_codigo
            ]
        ]);

        $r = $resp->RespuestaCufd ?? null;
        
        if ($r && isset($r->codigo) && trim($r->codigo) !== '') {
            $nuevoCufd = $r->codigo;
            $control = $r->codigoControl ?? null;
            $vigencia = $r->fechaVigencia ?? null;
            
            echo "   ✅ CUFD OBTENIDO: {$nuevoCufd}\n";
            if ($control) echo "   Código Control: {$control}\n";
            if ($vigencia) echo "   Vigencia: {$vigencia}\n";
            
            // Guardar en BD
            $stmt = $pdo->prepare("
                UPDATE siat_configuracion 
                SET sc_cufd = :cufd, sc_cufd_control = :control, sc_cufd_vigente_hasta = :vigencia, sc_actualizado_en = NOW()
                WHERE su_id = :su_id
            ");
            $stmt->execute([
                ':cufd' => $nuevoCufd,
                ':control' => $control,
                ':vigencia' => $vigencia,
                ':su_id' => $config->su_id
            ]);
            echo "   ✅ Guardado en siat_configuracion (sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta)\n";
        } else {
            echo "   ❌ Respuesta sin CUFD válido\n";
            debugSoapResponse($client, 'cufd', $resp);
            if (!empty($r->mensajesList)) {
                echo "   MENSAJES SIN:\n";
                print_r($r->mensajesList);
            }
        }
    } catch (SoapFault $e) {
        echo "   ❌ SoapFault: " . $e->getMessage() . "\n";
        if ($client) { echo "   REQ:\n".htmlspecialchars($client->__getLastRequest())."\n   RESP:\n".htmlspecialchars($client->__getLastResponse())."\n"; }
    } catch (Exception $e) {
        echo "   ❌ Exception: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// ===========================================
// VERIFICACIÓN FINAL EN BD
// ===========================================
echo "6. VERIFICACIÓN FINAL EN BD:\n";
$stmt = $pdo->prepare("SELECT sc_cuis, sc_cuis_vigente_hasta, sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta FROM siat_configuracion WHERE su_id = 1");
$stmt->execute();
$final = $stmt->fetch(PDO::FETCH_OBJ);

if ($final) {
    echo "   sc_cuis: " . ($final->sc_cuis ?? 'NULL') . "\n";
    echo "   sc_cuis_vigente_hasta: " . ($final->sc_cuis_vigente_hasta ?? 'NULL') . "\n";
    echo "   sc_cufd: " . ($final->sc_cufd ?? 'NULL') . "\n";
    echo "   sc_cufd_control: " . ($final->sc_cufd_control ?? 'NULL') . "\n";
    echo "   sc_cufd_vigente_hasta: " . ($final->sc_cufd_vigente_hasta ?? 'NULL') . "\n";
    
    $listo = ($final->sc_cuis && $final->sc_cuis !== 'CUIS_PRUEBA_001') 
          && ($final->sc_cufd && $final->sc_cufd !== 'CUFD_PRUEBA_001');
    
    echo "\n   " . ($listo ? "✅ ETAPA 1 COMPLETA - Listo para facturar" : "⚠️  ETAPA 1 INCOMPLETA - Falta CUIS o CUFD real") . "\n";
}

echo "\n=== FIN DEBUG ETAPA 1 ===\n";