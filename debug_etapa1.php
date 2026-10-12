<?php
// DEBUG Etapa 1 - Obtención de CUIS y CUFD (Autorización SIN)
// Procesa TODAS las sucursales que necesiten renovación.

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/La_Paz");

// Configuración SIAT
define('SIAT_MODO', 'PILOTO');
define('SIAT_AMBIENTE', 2);
define('SIAT_MODALIDAD', 1);
define('SIAT_COD_SISTEMA', '373641B66F08A38C69CE');
define('SIAT_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJtYXJ0aW55YW5hMTc0QGdtYWlsLmNvbSIsImNvZGlnb1Npc3RlbWEiOiIzNzM2NDFCNjZGMDhBMzhDNjlDRSIsIm5pdCI6Ikg0c0lBQUFBQUFBQUFETXpNREF6TURBM01EUUdBTWxsYVhNS0FBQUEiLCJpZCI6NTI2MTYyNCwiZXhwIjoxNzI4Nzg0OTI0LCJpYXQiOjE3ODYzMTkyOTQsIm5pdERlbGVnYWRvIjo2MDA2MDA3MDEzLCJzdWJjaXN0ZW1hIjoiU0ZFIn0.v8f1k7jyOF7lc7CaRdlAB799OGSqBI8gk33zwSUNN5Fb_sJvzXJr8oCV8bFBpQCaQS8aSIKcFSNoiHhGFygTAg');
define('SIAT_NIT', '6006007013');

define('SIAT_URLS', [
    'PILOTO' => [
        'sincronizacion_datos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl',
        'operaciones' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones?wsdl',
        'codigos' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos?wsdl',
        'compra_venta' => 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta?wsdl',
    ],
]);

function isPrueba($valor) {
    return $valor && strpos($valor, 'PRUEBA') !== false;
}

echo "=== DEBUG ETAPA 1 - Obtención CUIS y CUFD (todas las sucursales) ===\n\n";

// 1. Conexión a BD
try {
    $pdo = new PDO("mysql:host=localhost;dbname=samfarm_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión BD OK\n\n";
} catch (PDOException $e) {
    die("❌ Error conexión BD: " . $e->getMessage() . "\n");
}

// 2. Obtener configuración desde BD — todas las sucursales
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta,
                              sc_cuis_vigente_hasta, sc_sucursal_codigo, sc_punto_venta_codigo
                       FROM siat_configuracion
                       ORDER BY su_id");
$stmt->execute();
$configs = $stmt->fetchAll(PDO::FETCH_OBJ);

if (!$configs) {
    die("❌ No hay configuración SIAT en la base de datos\n");
}

echo "2. Sucursales detectadas: " . count($configs) . "\n";
foreach ($configs as $c) {
    $needsCuis = isPrueba($c->sc_cuis) || !$c->sc_cuis;
    $needsCufd = isPrueba($c->sc_cufd) || !$c->sc_cufd || (strtotime($c->sc_cufd_vigente_hasta ?? '') < time());
    $flag = ($needsCuis || $needsCufd) ? '→ NECESITA ACTUALIZACIÓN' : '→ OK';
    echo "   su_id={$c->su_id} sc_sucursal_codigo={$c->sc_sucursal_codigo} sc_cuis=" . ($c->sc_cuis ?? 'NULL') . " sc_cufd_vigente_hasta=" . ($c->sc_cufd_vigente_hasta ?? 'NULL') . " {$flag}\n";
}
echo "\n";

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
// PROCESAR CADA SUCURSAL
// ===========================================
foreach ($configs as $config) {
    $suId = $config->su_id;
    echo "========================================\n";
    echo "=== Procesando su_id={$suId} ===\n";
    echo "========================================\n";

    echo "   Configuración SIAT desde BD:\n";
    echo "   su_id: {$config->su_id}\n";
    echo "   sc_cuis (actual): " . ($config->sc_cuis ?? 'NULL') . "\n";
    echo "   sc_cufd (actual): " . ($config->sc_cufd ? substr($config->sc_cufd, 0, 30) . '...' : 'NULL') . "\n";
    echo "   sc_sucursal_codigo: {$config->sc_sucursal_codigo}\n";
    echo "   sc_punto_venta_codigo: {$config->sc_punto_venta_codigo}\n";
    echo "   NIT: " . SIAT_NIT . "\n";
    echo "   códigoSistema: " . SIAT_COD_SISTEMA . "\n\n";

    // ===========================================
    // ETAPA 1-A: OBTENER CUIS
    // ===========================================
    echo "4. ETAPA 1-A: OBTENER CUIS\n";

    $cuisActual = $config->sc_cuis;
    $needsCuis = isPrueba($cuisActual) || !$cuisActual;

    if ($cuisActual && !isPrueba($cuisActual) && !$needsCuis) {
        echo "   ✅ CUIS real en BD: {$cuisActual}\n";
        $cuisVigente = $config->sc_cuis_vigente_hasta;
        if ($cuisVigente && strtotime($cuisVigente) > time()) {
            echo "   ✅ Vigente hasta {$cuisVigente}\n";
        } elseif ($cuisVigente) {
            echo "   ⚠️  Vencido el {$cuisVigente} — renovando\n";
            $needsCuis = true;
        }
        if ($needsCuis) {
            echo "   Renovando CUIS...\n";
        } else {
            echo "   No se necesita renovación de CUIS\n";
        }
    } else {
        echo "   ❌ CUIS es PRUEBA o NULL — solicitando uno nuevo\n";
    }

    if ($needsCuis) {
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
                echo "   ✅ Guardado en siat_configificacion (sc_cuis, sc_cuis_vigente_hasta)\n";

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
            if ($client) { echo "   REQ:\n" . htmlspecialchars($client->__getLastRequest()) . "\n   RESP:\n" . htmlspecialchars($client->__getLastResponse()) . "\n"; }
        } catch (Exception $e) {
            echo "   ❌ Exception: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // ===========================================
    // ETAPA 1-B: OBTENER CUFD
    // ===========================================
    echo "5. ETAPA 1-B: OBTENER CUFD (requiere CUIS válido)\n";

    $cufdVigente = $config->sc_cufd_vigente_hasta;
    $cufdVence = $cufdVigente ? strtotime($cufdVigente) : 0;
    $needsCufd = isPrueba($config->sc_cufd) || !$config->sc_cufd || ($cufdVence < time());

    if (!$cuisActual || isPrueba($cuisActual)) {
        echo "   ⚠️  Saltando CUFD: no hay CUIS real\n";
    } elseif (!$needsCufd && $cufdVencido_ok = true) {
        $remaining = $cufdVence - time();
        echo "   ✅ CUFD vigente en BD ({$remaining} segundos restantes)\n";
        echo "   No se necesita renovación de CUFD\n";
    } else {
        $cufdActual = $config->sc_cufd;
        echo "   CUFD actual en BD: " . ($cufdActual ? substr($cufdActual, 0, 30) . '...' : 'NULL') . "\n";
        echo "   Solicitando CUFD...\n";

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
            if ($client) { echo "   REQ:\n" . htmlspecialchars($client->__getLastRequest()) . "\n   RESP:\n" . htmlspecialchars($client->__getLastResponse()) . "\n"; }
        } catch (Exception $e) {
            echo "   ❌ Exception: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// ===========================================
// VERIFICACIÓN FINAL EN BD
// ===========================================
echo "========================================\n";
echo "=== VERIFICACIÓN FINAL EN BD ===\n";
echo "========================================\n";
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cuis_vigente_hasta, sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta FROM siat_configuracion ORDER BY su_id");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_OBJ);

$todoListo = true;
foreach ($rows as $row) {
    echo "--- su_id={$row->su_id} ---\n";
    echo "   sc_cuis: " . ($row->sc_cuis ?? 'NULL') . "\n";
    echo "   sc_cuis_vigente_hasta: " . ($row->sc_cuis_vigente_hasta ?? 'NULL') . "\n";
    echo "   sc_cufd: " . ($row->sc_cufd ? substr($row->sc_cufd, 0, 30) . '...' : 'NULL') . "\n";
    echo "   sc_cufd_control: " . ($row->sc_cufd_control ?? 'NULL') . "\n";
    echo "   sc_cufd_vigente_hasta: " . ($row->sc_cufd_vigente_hasta ?? 'NULL') . "\n";

    $listo = ($row->sc_cuis && !isPrueba($row->sc_cuis))
        && ($row->sc_cufd && !isPrueba($row->sc_cufd));

    if ($listo) {
        echo "   ✅ ETAPA 1 COMPLETA - Listo para facturar\n";
    } else {
        echo "   ⚠️  ETAPA 1 INCOMPLETA - Falta CUIS o CUFD real\n";
        $todoListo = false;
    }
}

echo "\n" . ($todoListo ? "✅ TODAS LAS SUCURSALES LISTAS PARA FACTURAR" : "⚠️  ALGUNAS SUCURSALES FALTAN POR ACTUALIZAR") . "\n";

echo "\n=== FIN DEBUG ETAPA 1 ===\n";
