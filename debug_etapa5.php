<?php
// DEBUG Etapa 5 - Registro de Eventos Significativos (Validación de Sistema SIAT)
// Basado en el patrón de debug_etapa4.php (mismas conexiones, config y cliente SOAP)

error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("America/La_Paz");

// Configuración SIAT: se toma de config/siat.php, que es la fuente unica que
// usa models/siatModel.php al enviar el paquete. Antes este script traia sus
// propias constantes con SIAT_MODALIDAD = 1, mientras el paquete sale con 2:
// el SIN no reconoce como propio un CUFD emitido en otra modalidad y responde
// 914 "CUFD INVALIDO" en el siguiente envio.
require_once __DIR__ . '/config/siat.php';

if (!defined('SIAT_NIT')) {
    define('SIAT_NIT', '6006007013');
}


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
$stmt = $pdo->prepare("SELECT su_id, sc_cuis, sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta, sc_sucursal_codigo, sc_punto_venta_codigo FROM siat_configuracion WHERE sc_cuis IS NOT NULL AND sc_cuis != '' ORDER BY sc_id LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_OBJ);

if (!$config) {
    die("❌ No existe configuración SIAT con CUIS válido\n");
}

$suId = $config->su_id;
$cuis = $config->sc_cuis;
$cufd = $config->sc_cufd;
$cufdControl = $config->sc_cufd_control;
$cufdVigenciaHasta = $config->sc_cufd_vigente_hasta;
$codigoSucursal = (int)($config->sc_sucursal_codigo ?? 0);
$codigoPuntoVenta = (int)($config->sc_punto_venta_codigo ?? 0);

echo "Configuración SIAT:\n";
echo "   su_id: {$suId}\n";
echo "   CUIS: {$cuis}\n";
echo "   CUFD: {$cufd}\n";
echo "   Sucursal: {$codigoSucursal}\n";
echo "   PtoVenta: {$codigoPuntoVenta}\n\n";

// 3. Crear clientes SOAP
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header' => "apikey: TokenApi " . SIAT_TOKEN . "\r\n",
        'timeout' => 30
    ]
]);

$crearCliente = function ($servicio) use ($context) {
    return new SoapClient(SIAT_URLS[SIAT_MODO][$servicio], [
        'stream_context' => $context,
        'trace' => 1,
        'exceptions' => true,
        'connection_timeout' => 60,
        'soap_version' => SOAP_1_1,
    ]);
};

try {
    $client = $crearCliente('operaciones');
    echo "✅ Cliente SOAP (operaciones) creado\n\n";
} catch (Exception $e) {
    die("❌ Error cliente SOAP: " . $e->getMessage() . "\n");
}

// 4. Parámetros del evento significativo
echo "Evento a registrar:\n";
echo "   codigoMotivoEvento: {$codigoEvento} ({$descripcionEvento})\n\n";

// 4.1 Capturar el CUFD vigente ACTUAL antes de pedir uno nuevo.
// Este es el cufdEvento: el CUFD que estaba en vigor durante la contingencia.
$cufdAnterior    = $cufd;
$cufdAnteriorVigencia = $cufdVigenciaHasta;
$cufdControlAnterior  = $cufdControl;

// 4.2 Solicitar un CUFD NUEVO y vigente.
// El cufd guardado en siat_configuracion puede estar invalidado en el SIN
// (error 914 "CUFD INVALIDO"). Se pide uno fresco y se persiste.
try {
    $clientCodigos = $crearCliente('codigos');
    $respCufd = $clientCodigos->cufd([
        'SolicitudCufd' => [
            'codigoAmbiente'   => SIAT_AMBIENTE,
            'codigoModalidad'  => SIAT_MODALIDAD,
            'codigoSistema'    => SIAT_COD_SISTEMA,
            'nit'              => SIAT_NIT,
            'cuis'             => $cuis,
            'codigoSucursal'   => $codigoSucursal,
            'codigoPuntoVenta' => $codigoPuntoVenta,
        ]
    ]);

    $rc = $respCufd->RespuestaCufd;

    if (empty($rc->codigo)) {
        // El SIN responde con transaccion=false y una lista de motivos. Sin
        // imprimirlos no hay forma de saber si es la modalidad, el CUIS o un
        // limite de emision, asi que se vuelca la respuesta completa.
        echo "❌ El SIN no devolvio CUFD para el evento.\n";
        echo "   respuesta cruda: " . $clientCodigos->__getLastResponse() . "\n\n";
        $err = $clientCodigos->getLastError();
        if ($err) {
            echo "   fault: " . $err->faultstring . "\n";
            if (!empty($err->detail)) {
                echo "   detalle: " . (is_string($err->detail) ? $err->detail : print_r($err->detail, true)) . "\n";
            }
            echo "\n";
        }
        die("   transaccion=" . var_export($rc->transaccion ?? null, true) . "\n");
    }

    $cufdNuevo = $rc->codigo;
    echo "✅ CUFD vigente obtenido: {$cufdNuevo}\n";
    echo "   vigencia: " . ($rc->fechaVigencia ?? 'n/a') . "\n\n";

    // Persistir el CUFD vigente (si no, el SIN lo rechaza con 914)
    $upd = $pdo->prepare("
        UPDATE siat_configuracion
        SET sc_cufd = :cufd, sc_cufd_control = :control, sc_cufd_vigente_hasta = :vigencia
        WHERE su_id = :su_id
    ");
    $upd->execute([
        ':cufd'     => $cufdNuevo,
        ':control'  => $rc->codigoControl ?? null,
        ':vigencia' => $rc->fechaVigencia ?? null,
        ':su_id'    => $suId,
    ]);
    echo "✅ CUFD actualizado en siat_configuracion\n\n";
} catch (Exception $e) {
    die("❌ Error pidiendo CUFD de evento: " . $e->getMessage() . "\n");
}

// 4.2 Periodo del evento.
// El SIN valida tres cosas a la vez:
//   - formato yyyy-MM-dd'T'HH:mm:ss.SSS en hora oficial de Bolivia (-04:00), no UTC
//   - fechaHoraInicioEvento < fechaHoraFinEvento, y ambos en el pasado
//   - el periodo debe caer dentro de la vigencia del cufdEvento
//
// El SIN distingue dos CUFD y por eso ambos son obligatorios y distintos:
//   cufd       = CUFD vigente al registrar el evento (el recien obtenido)
//   cufdEvento = CUFD que estaba vigente CUANDO OCURRIO la contingencia
// Si se mandan iguales responde 984 "EL EVENTO SIGNIFICATIVO NO CORRESPONDE
// AL CUFD DEL EVENTO REGISTRADO".
$cufd       = $cufdNuevo;
$cufdEvento = $cufdAnterior;

// El SIN exige ademas que el periodo completo este dentro de la vigencia del
// cufdEvento, que empieza cuando se emitio ese CUFD (vigencia - 24 horas).
$cufdEventoEmitido = strtotime($cufdAnteriorVigencia . ' -24 hours');

$fechaFinEvento    = date('Y-m-d\TH:i:s', time() - 120) . '.000';
$inicioMinimo     = $cufdEventoEmitido + 30;
$inicioMaximo     = strtotime($fechaFinEvento) - 60;
$inicio           = max(strtotime($fechaFinEvento) - 600, $inicioMinimo);

if ($inicio > $inicioMaximo) {
    die("❌ El cufdEvento es demasiado reciente (emitido " . date('H:i:s', $cufdEventoEmitido)
        . "). Se necesita al menos 90s de vigencia para registrar el evento.\n");
}

$fechaInicioEvento = date('Y-m-d\TH:i:s', $inicio) . '.000';

echo "Periodo del evento:\n";
echo "   cufd (registro):     {$cufd}\n";
echo "   cufdEvento (evento): {$cufdEvento}\n";
echo "   cufdEvento emitido:  " . date('Y-m-d H:i:s', $cufdEventoEmitido) . "\n";
echo "   fechaInicio:         {$fechaInicioEvento}\n";
echo "   fechaFin:            {$fechaFinEvento}\n\n";

// 5. Registrar evento en BD local (siat_eventos) ANTES de enviar al SIN
$evCodigo = 'EVT-' . date('YmdHis') . '-' . $suId . '-' . $codigoEvento;
$stmt = $pdo->prepare("
    INSERT INTO siat_eventos (su_id, ev_codigo, ev_codigo_evento, ev_cufd, ev_cufd_evento,
                              ev_fecha_inicio, ev_fecha_fin, ev_descripcion, ev_estado, ev_creado_en)
    VALUES (:su_id, :ev_codigo, :ev_codigo_evento, :ev_cufd, :ev_cufd_evento,
            :ev_fecha_inicio, :ev_fecha_fin, :ev_descripcion, 'registrado', NOW())
");
$stmt->execute([
    ':su_id'            => $suId,
    ':ev_codigo'        => $evCodigo,
    ':ev_codigo_evento' => (string)$codigoEvento,
    ':ev_cufd'          => $cufd,
    ':ev_cufd_evento'   => $cufdEvento,
    ':ev_fecha_inicio'  => $fechaInicioEvento,
    ':ev_fecha_fin'     => $fechaFinEvento,
    ':ev_descripcion'   => $descripcionEvento,
]);
echo "✅ Evento guardado en siat_eventos con ev_codigo: {$evCodigo}\n\n";

$solicitud = [
    'SolicitudEventoSignificativo' => [
        'codigoAmbiente'       => SIAT_AMBIENTE,
        'codigoSistema'        => SIAT_COD_SISTEMA,
        'nit'                  => SIAT_NIT,
        'cuis'                 => $cuis,
        'cufd'                 => $cufd,
        'codigoSucursal'       => $codigoSucursal,
        'codigoPuntoVenta'     => $codigoPuntoVenta,
        'codigoMotivoEvento'   => $codigoEvento,
        'descripcion'          => $descripcionEvento,
        'fechaHoraInicioEvento'=> $fechaInicioEvento,
        'fechaHoraFinEvento'   => $fechaFinEvento,
        'cufdEvento'           => $cufdEvento,
    ]
];

echo "➡️  Enviando registroEventoSignificativo al SIAT...\n";

try {
    $response = $client->registroEventoSignificativo($solicitud);

    echo "✅ Respuesta SIAT:\n";
    echo $client->__getLastResponse() . "\n";

    // La respuesta del SIN se llama RespuestaListaEventos (no RespuestaEventoSignificativo)
    $resultVar = 'RespuestaListaEventos';
    if (isset($response->$resultVar)) {
        $r = $response->$resultVar;
        echo "\n--- Datos de respuesta ---\n";
        echo "transaccion:      " . var_export($r->transaccion ?? null, true) . "\n";
        echo "codigoRecepcion:  " . ($r->codigoRecepcionEventoSignificativo ?? 'N/A') . "\n";

        if (!empty($r->listaCodigos)) {
            echo "codigos registrados:\n";
            foreach ((array)$r->listaCodigos as $ev) {
                echo "   - codigoEvento: " . ($ev->codigoEvento ?? '?')
                   . " | inicio: " . ($ev->fechaInicio ?? '?')
                   . " | fin: " . ($ev->fechaFin ?? '?') . "\n";
            }
        }

        // Actualizar siat_eventos con respuesta del SIN
        $estado = 'enviado';
        if (isset($r->transaccion) && $r->transaccion) $estado = 'validado';
        if (isset($r->mensajesList) && !empty($r->mensajesList)) $estado = 'rechazado';

        $stmt = $pdo->prepare("
            UPDATE siat_eventos
            SET ev_estado = :estado, ev_respuesta_siat = :respuesta
            WHERE ev_codigo = :ev_codigo
        ");
        // Se guarda la respuesta con la clave "recepcion" ademas de la que devuelve
        // el SIN, porque es la que leen debug_etapa5_loop.php y debug_etapa6.php
        // para armar el codigoEvento del paquete de contingencia.
        $respuesta = json_decode(json_encode($r), true);
        if (!isset($respuesta['recepcion']) && isset($r->codigoRecepcionEventoSignificativo)) {
            $respuesta['recepcion'] = (string)$r->codigoRecepcionEventoSignificativo;
        }
        // El codigo de control del CUFD de la contingencia (cufdEvento) tiene
        // que guardarse aqui: es con el que la etapa 6 arma el CUF de las
        // facturas, y si se usa el del CUFD vigente el SIN las observa con
        // 1002 "EL CUF ENVIADO EN EL XML ES INVALIDO", porque el CUF lo
        // calcula el SIN con el control del CUFD que va dentro del XML.
        $respuesta['cufd_control'] = $cufdControlAnterior;
        $respuesta['cufd_evento']   = $cufdAnterior;

        $stmt->execute([
            ':estado'           => $estado,
            ':respuesta'        => json_encode($respuesta),
            ':ev_codigo'        => $evCodigo,
        ]);
        echo "✅ siat_eventos actualizado con respuesta del SIN\n";

        if (!empty($r->mensajesList)) {
            echo "mensajes:\n";
            $mensajes = is_array($r->mensajesList) ? $r->mensajesList : [$r->mensajesList];
            foreach ($mensajes as $m) {
                echo "   - " . trim(($m->codigo ?? '') . ': ' . ($m->descripcion ?? '')) . "\n";
            }
        }
    }
} catch (SoapFault $e) {
    echo "❌ Error SOAP: " . $e->getMessage() . "\n";
    echo "Request enviado:\n" . $client->__getLastRequest() . "\n";
    
    // Marcar como rechazado en BD
    $stmt = $pdo->prepare("UPDATE siat_eventos SET ev_estado = 'rechazado', ev_respuesta_siat = :resp WHERE ev_codigo = :ev_codigo");
    $stmt->execute([':resp' => json_encode(['error' => $e->getMessage()]), ':ev_codigo' => $evCodigo]);
}

echo "\n=== FIN DEBUG ===\n";
