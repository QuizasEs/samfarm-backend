<?php
/**
 * DEBUG Etapa 9 - Reversion de una anulacion de factura ante el SIN.
 *
 * Etapa 1: CUIS de la sucursal / punto de venta
 * Etapa 2: catalogos (actividades, productos, leyendas, parametricas)
 * Etapa 4: firma del XML
 * Etapa 5: evento significativo
 * Etapa 6: emision en contingencia -> TAR.GZ -> recepcion -> validacion
 * Etapa 7: anulacion de una factura               <- debug_etapa7.php
 * Etapa 8: verificacion de la firma digital
 * Etapa 9: reversion de la anulacion             <- este archivo
 *
 * Que se prueba aca
 * ------------------
 * La operacion reversionAnulacionFactura, que es el camino de vuelta de la
 * etapa 7. Si la etapa 7 deja una factura en estado ANULADA, esta la devuelve a
 * su estado anterior.
 *
 * NO es un borrado. La factura sigue existiendo en el SIN, conserva su CUF y su
 * numero dentro de la correlativa; solo se le revierte el estado. El caso real es
 * un cajero que anula una venta por error, o el sistema que anula la factura
 * equivocada: sin reversion el contribuyente queda con una factura anulada que
 * deberia seguir vigente.
 *
 * Contrato tomado del WSDL del piloto (no de memoria):
 *
 *   <xs:complexType name="reversionAnulacionFactura">
 *     <SolicitudServicioReversionAnulacionFactura type="tns:solicitudReversionAnulacion"/>
 *
 *   solicitudReversionAnulacion extiende solicitudRecepcion y agrega:
 *     cuf (string)
 *
 *   solicitudRecepcion trae:
 *     codigoAmbiente, codigoDocumentoSector, codigoEmision, codigoModalidad,
 *     codigoPuntoVenta (opcional), codigoSistema, codigoSucursal, cufd, cuis,
 *     nit, tipoFacturaDocumento
 *
 * Comparado con la anulacion de la etapa 7:
 *
 *   anulacion    wrapper SolicitudServicioAnulacionFactura   + codigoMotivo + cuf
 *   reversion    wrapper SolicitudServicioReversionAnulacionFactura + cuf
 *
 * Dos detalles que ya costaron tiempo:
 *
 *   1. El nombre del wrapper es SolicitudServicioReversionAnulacionFactura, con
 *      "Factura" al final. No es el nombre del tipo (solicitudReversionAnulacion).
 *      Escribir el nombre corto hace que SoapClient responda
 *      "Encoding: object has no ... property". Le paso lo mismo con
 *      verificacionEstadoFactura en la etapa 7.
 *
 *   2. La reversion NO lleva codigoMotivo. No se esta justificando una anulacion
 *      nueva, se esta deshaciendo una anterior.
 *
 *   3. La respuesta vuelve en RespuestaServicioFacturacion (tipo respuestaRecepcion):
 *      codigoEstado, codigoDescripcion, codigoRecepcion, transaccion, mensajesList.
 *
 * De donde salen las facturas
 * --------------------------
 * Las etapas 1 a 6 no dejaron facturas en la base local (facturacion_electronica
 * tiene FK a factura, asi que escribir ahi obligaria a crear venta y factura
 * reales). Los CUFs quedaron en siat_paquetes.pa_facturas.
 *
 * Las facturas de prueba de la etapa 7 son las que la etapa 9 revierte. La
 * etapa 7 no escribe en la base, asi que no hay tabla donde figure quais
 * quedaron anuladas: van hardcodeadas abajo, con el paquete del que salieron.
 *
 * Material de prueba (las 6 anuladas en la etapa 7 el 2026-09-30):
 *
 *   0  19AF29AE4D2AD426C6109428A33F28814DA3B09A7D10B86E7B9653BF74   pa 17  pv 1
 *   1  19AF29AE4D2AD426C6109428A33F28814DA3B1148F00B86E7B9653BF74   pa 18  pv 1
 *   2  19AF29AE4D2AD426C4BBB09AA762BBA83407B282C4F17D0CE99653BF74   pa 24  pv 1
 *   3  19AF29AE4D2AD426C71517987B1432E47C380886B286216B3C9653BF74   pa 27  pv 0
 *   4  19AF29AE4D2AD426C7F87C6E658324253A7F7F8B721390E5DD9653BF74   pa 31  pv 0
 *   5  19AF29AE4D2AD426C7096110DF50DF139F6CDA265CB08FE4BC9653BF74   pa 34  pv 1
 *
 * Como este script no guarda nada, al terminar las pruebas las facturas quedan
 * como se dejen. --ciclo intenta devolverlas a ANULADA, pero el piloto puede
 * impedirlo (ver REGLA DEL PILOTO mas abajo).
 *
 * REGLA DEL PILOTO: no se puede volver a anular una factura ya revertida
 * -------------------------------------------------------------------------
 * Observado el 2026-09-30 contra el piloto, sobre dos facturas distintas:
 *
 *   anular     -> 905 ANULACION CONFIRMADA        estado 691 ANULADA
 *   revertir   -> 907 REVERSION CONFIRMADA        estado 690 VALIDA
 *   anular     -> 906 ANULACION RECHAZADA
 *                 981 REVERSION DE ANULACION NO DISPONIBLE PARA LA FACTURA O
 *                    NOTA DE CREDITO - DEBITO       estado sigue 690 VALIDA
 *
 * No es un tope global del NIT: sobre una factura que nunca se anularo la
 * anulacion responde 905 sin problema. Es una regla por factura, y el mensaje
 * 981 habla de la reversion aunque la llamada sea la de anulacion, asi que no
 * conviene deducir el significado del texto del mensaje.
 *
 * CODIGOS OBSERVADOS (el catalogo de internet no los acertaba todos)
 * ------------------------------------------------------------------------
 *   690 - VALIDA                       (verificacionEstadoFactura)
 *   691 - ANULADA                      (verificacionEstadoFactura)
 *   905 - ANULACION CONFIRMADA         (anulacionFactura)
 *   906 - ANULACION RECHAZADA          (anulacionFactura, lleva 936 o 981)
 *   907 - REVERSION DE ANULACION CONFIRMADA   (reversionAnulacionFactura)
 *   909 - REVERSION DE ANULACION RECHAZADA    (reversionAnulacionFactura, lleva 981)
 *   936 - La factura ya se encuentra anulada
 *   981 - Reversion de anulacion no disponible para la factura
 *
 * Uso:
 *   php debug_etapa9.php --listar              candidatos (paquetes + anuladas)
 *   php debug_etapa9.php                       revierte la primera anulada
 *   php debug_etapa9.php --candidatos          revierte las 6, una por una
 *   php debug_etapa9.php --cuf=... --pv=1      factura puntual
 *   php debug_etapa9.php --paquete=24 --indice=0
 *   php debug_etapa9.php --anular-primero      si no esta anulada, la anula antes
 *   php debug_etapa9.php --ciclo               anular -> revertir -> anular
 *   php debug_etapa9.php --negativo            revertir una factura NO anulada
 *   php debug_etapa9.php --motivo=2 --emision=1 --sin-verificar
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
date_default_timezone_set('America/La_Paz');

// Configuracion SIAT: misma fuente que usa models/siatModel.php. Si este
// script trajera sus propias constantes mandaria otro ambiente, modalidad o
// sistema del que se emitieron los CUFDs y el SIN responderia 914 o 959.
require_once __DIR__ . '/config/siat.php';

if (!defined('SIAT_NIT')) {
    define('SIAT_NIT', '6006007013');
}

$op = getopt('', [
    'listar', 'candidatos', 'cuf::', 'pv::', 'paquete::', 'indice::',
    'anular-primero', 'ciclo', 'negativo',
    'motivo::', 'emision::', 'sin-verificar', 'ayuda',
]);

/* Facturas que la etapa 7 dejo en estado ANULADA. */
$CANDIDATOS = [
    ['cuf' => '19AF29AE4D2AD426C6109428A33F28814DA3B09A7D10B86E7B9653BF74', 'pv' => 1, 'paquete' => 17, 'motivo' => 1],
    ['cuf' => '19AF29AE4D2AD426C6109428A33F28814DA3B1148F00B86E7B9653BF74', 'pv' => 1, 'paquete' => 18, 'motivo' => 2],
    ['cuf' => '19AF29AE4D2AD426C4BBB09AA762BBA83407B282C4F17D0CE99653BF74', 'pv' => 1, 'paquete' => 24, 'motivo' => 1],
    ['cuf' => '19AF29AE4D2AD426C71517987B1432E47C380886B286216B3C9653BF74', 'pv' => 0, 'paquete' => 27, 'motivo' => 1],
    ['cuf' => '19AF29AE4D2AD426C7F87C6E658324253A7F7F8B721390E5DD9653BF74', 'pv' => 0, 'paquete' => 31, 'motivo' => 2],
    ['cuf' => '19AF29AE4D2AD426C7096110DF50DF139F6CDA265CB08FE4BC9653BF74', 'pv' => 1, 'paquete' => 34, 'motivo' => 3],
];

$MOTIVOS = [
    1 => 'Error en la factura',
    2 => 'Devolucion de producto',
    3 => 'Otros',
];

echo "=== DEBUG ETAPA 9 - REVERSION DE ANULACION ===\n\n";

if (isset($op['ayuda'])) {
    echo "Uso: php debug_etapa9.php --listar | --candidatos | --cuf=... --pv=N | --paquete=N --indice=N\n";
    echo "     --anular-primero --ciclo --negativo --motivo=N --emision=N --sin-verificar\n";
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

/* --- 2. Helpers ----------------------------------------------------------- */

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
        'cuis'          => $r['sc_cuis'] ?? $r['pv_cuis'],
        'cufd'          => $r['sc_cufd'] ?? $r['pv_cufd'],
        'vigente_hasta' => $r['sc_cufd_vigente_hasta'] ?? $r['pv_cufd_vigente_hasta'],
    ];
}

/* El SIN devuelve mensajesList como un objeto suelto cuando hay uno solo y como
   arreglo cuando hay varios. (array) sobre un stdClass devuelve
   ['mensajesList' => ...], no la lista, asi que hay que aplanar a mano. */
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

/* --- 3. Cliente SOAP ------------------------------------------------------ */
$context = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => [
        'header'   => 'apikey: TokenApi ' . SIAT_TOKEN . "\r\n",
        'timeout'  => 60,
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
    echo "OK  Cliente SOAP (compra_venta)\n";
} catch (Exception $e) {
    die("ERROR cliente SOAP: " . $e->getMessage() . "\n");
}

/* --- 4. Llamadas al SIN --------------------------------------------------- */

/* verificacionEstadoFactura: cual es el estado real de la factura. Es la fuente
   de verdad. Wrapper SolicitudServicioVerificacionEstadoFactura, con "Factura"
   al final. */
function consultarEstado($client, $cred, $pvCodigo, $cuf, $codigoEmision)
{
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
            'cuf'                   => $cuf,
        ]]);

        $r = $v->RespuestaServicioFacturacion ?? $v;
        $msgs = [];
        foreach (aplanarMensajes($r->mensajesList ?? null) as $m) {
            $m = (array)$m;
            $msgs[] = ($m['codigo'] ?? '?') . ': ' . ($m['descripcion'] ?? '?');
        }

        return [
            'ok'          => true,
            'codigoEstado' => (int)($r->codigoEstado ?? 0),
            'descripcion' => (string)($r->codigoDescripcion ?? ''),
            'transaccion' => $r->transaccion ?? null,
            'mensajes'    => $msgs,
        ];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/* anulacionFactura: la etapa 7. Se usa para crear el estado previo cuando la
   factura todavia no esta anulada (--anular-primero y --ciclo). */
function anular($client, $cred, $pvCodigo, $cuf, $codigoMotivo, $codigoEmision)
{
    try {
        $resp = $client->anulacionFactura(['SolicitudServicioAnulacionFactura' => [
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
            'cuf'                   => $cuf,
        ]]);

        $r = $resp->RespuestaServicioFacturacion ?? $resp;
        $msgs = [];
        foreach (aplanarMensajes($r->mensajesList ?? null) as $m) {
            $m = (array)$m;
            $msgs[] = ($m['codigo'] ?? '?') . ': ' . ($m['descripcion'] ?? '?');
        }

        return [
            'ok'          => true,
            'codigoEstado' => (int)($r->codigoEstado ?? 0),
            'descripcion' => (string)($r->codigoDescripcion ?? ''),
            'transaccion' => $r->transaccion ?? null,
            'mensajes'    => $msgs,
            'crudo'       => $client->__getLastResponse(),
        ];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage(),
                'crudo' => $client->__getLastResponse()];
    }
}

/* reversionAnulacionFactura: la operacion de esta etapa. El wrapper termina en
   "AnulacionFactura", no es el nombre del tipo. */
function revertir($client, $cred, $pvCodigo, $cuf, $codigoEmision)
{
    try {
        $resp = $client->reversionAnulacionFactura([
            'SolicitudServicioReversionAnulacionFactura' => [
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
                'cuf'                   => $cuf,
            ],
        ]);

        $r = $resp->RespuestaServicioFacturacion ?? $resp;
        $msgs = [];
        foreach (aplanarMensajes($r->mensajesList ?? null) as $m) {
            $m = (array)$m;
            $msgs[] = ($m['codigo'] ?? '?') . ': ' . ($m['descripcion'] ?? '?');
        }

        return [
            'ok'          => true,
            'codigoEstado' => (int)($r->codigoEstado ?? 0),
            'descripcion' => (string)($r->codigoDescripcion ?? ''),
            'transaccion' => $r->transaccion ?? null,
            'mensajes'    => $msgs,
            'crudo'       => $client->__getLastResponse(),
        ];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage(),
                'crudo' => $client->__getLastResponse()];
    }
}

function mostrar($etiqueta, $r)
{
    if (!$r['ok']) {
        echo "  {$etiqueta}: ERROR " . ($r['error'] ?? '?') . "\n";
        return;
    }
    echo "  {$etiqueta}: codigoEstado=" . $r['codigoEstado']
       . "  \"" . $r['descripcion'] . "\""
       . "  transaccion=" . var_export($r['transaccion'], true) . "\n";
    foreach ($r['mensajes'] as $m) {
        echo "      {$m}\n";
    }
}

/* --- 5. Elegir las facturas a trabajar ------------------------------------ */

if (isset($op['listar'])) {
    echo "\nFacturas anuladas por la etapa 7 (material de esta etapa):\n";
    foreach ($CANDIDATOS as $i => $c) {
        printf("  %d  pa %-3s pv %d  %s\n", $i, $c['paquete'], $c['pv'], $c['cuf']);
    }

    $stmt = $pdo->query("
        SELECT pa.pa_id, pa.pa_codigo_recepcion, pa.pa_ev_codigo, pa.pa_cantidad_facturas
        FROM siat_paquetes pa
        WHERE pa.pa_estado = 'validado' AND pa.pa_codigo_recepcion <> ''
        ORDER BY pa.pa_id
    ");
        echo "\nPaquetes validados de donde --anular-primero puede sacar una factura para --anular-primero: "
 .
         $stmt->rowCount() . "\n";
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $pk) {
        printf("  pa %-3s pv %d  n=%-4s %s\n",
            $pk['pa_id'], (int)(explode('-', $pk['pa_ev_codigo'])[1] ?? 0),
            $pk['pa_cantidad_facturas'], $pk['pa_codigo_recepcion']);
    }
    echo "\nPara revertir la primera:  php debug_etapa9.php\n";
    exit(0);
}

$codigoMotivo   = isset($op['motivo']) ? (int)$op['motivo'] : 1;
$codigoEmision  = isset($op['emision']) ? (int)$op['emision'] : 1;
$sinVerificar   = isset($op['sin-verificar']);

$objetivos = [];

if (isset($op['candidatos'])) {
    $objetivos = $CANDIDATOS;
} elseif (isset($op['cuf'])) {
    if (!isset($op['pv'])) {
        die("\n--cuf necesita --pv=0 o --pv=1: el CUF solo no dice de que caja salio.\n");
    }
    $objetivos = [[
        'cuf' => $op['cuf'],
        'pv'  => (int)$op['pv'],
        'paquete' => null,
        'motivo' => $codigoMotivo,
    ]];
} elseif (isset($op['paquete'])) {
    $pk = $pdo->query("
        SELECT pa.pa_id, pa.pa_facturas, pa.pa_ev_codigo
        FROM siat_paquetes pa
        WHERE pa.pa_id = " . (int)$op['paquete'] . " LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);

    if (!$pk) {
        die("\nNo existe el paquete " . (int)$op['paquete'] . ".\n");
    }

    $facturas = json_decode($pk['pa_facturas'] ?? '[]', true) ?: [];
    $indice = isset($op['indice']) ? (int)$op['indice'] : 0;
    if (!$facturas || $indice >= count($facturas)) {
        die("\nEl paquete {$pk['pa_id']} tiene " . count($facturas)
            . " facturas; --indice={$indice} no existe.\n");
    }

    $objetivos = [[
        'cuf'     => $facturas[$indice]['cuf'],
        'pv'      => (int)(explode('-', $pk['pa_ev_codigo'])[1] ?? 0),
        'paquete' => (int)$pk['pa_id'],
        'motivo'  => $codigoMotivo,
    ]];
} else {
    $objetivos = [$CANDIDATOS[0]];
}

$modoNegativo = isset($op['negativo']);
$modoCiclo    = isset($op['ciclo']);
$anularPrimero = isset($op['anular-primero']);

// --- 6. Ejecucion ----------------------------------------------------------

echo "\n" . str_repeat('=', 74) . "\n";
echo count($objetivos) . " factura(s) | "
   . ($modoCiclo ? "CICIO (anular - revertir - anular)"
       : ($modoNegativo ? "NEGATIVO (revertir sin estar anulada, debe rechazar)"
       : ($anularPrimero ? "anular antes si hace falta" : "revertir"))) . "\n";
echo "codigoEmision={$codigoEmision}"
   . ($modoCiclo || $anularPrimero ? "  codigoMotivo={$codigoMotivo} ({$MOTIVOS[$codigoMotivo]})" : '') . "\n";
echo str_repeat('=', 74) . "\n";

$resumen = [];

foreach ($objetivos as $n => $obj) {
    $cuf = $obj['cuf'];
    $pv  = (int)$obj['pv'];

    echo "\n" . str_repeat('-', 74) . "\n";
    echo "[{$n}] CUF {$cuf}\n";
    echo "      punto de venta {$pv}"
       . ($obj['paquete'] ? "  (factura del paquete {$obj['paquete']})" : '') . "\n";

    try {
        $cred = credenciales($pdo, $pv);
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        $resumen[] = ['cuf' => $cuf, 'ok' => false, 'detalle' => $e->getMessage()];
        continue;
    }

    echo "      CUIS {$cred['cuis']}  CUFD " . substr($cred['cufd'], 0, 40) . "...\n";

    // --- Estado inicial
    $antes = consultarEstado($client, $cred, $pv, $cuf, $codigoEmision);
    if (!$antes['ok']) {
        echo "  ERROR al consultar el estado: {$antes['error']}\n";
        $resumen[] = ['cuf' => $cuf, 'ok' => false, 'detalle' => $antes['error']];
        continue;
    }
    mostrar('estado inicial  ', $antes);
    $estabaAnulada = stripos($antes['descripcion'], 'ANULADA') !== false;

    // --- Control negativo: revertir algo que no esta anulado
    if ($modoNegativo) {
        if ($estabaAnulada) {
            echo "\n  La factura YA esta anulada; este test no aplica.\n";
            echo "  Usa una factura de un paquete sin annular, por ejemplo:\n";
            echo "    php debug_etapa9.php --negativo --paquete=32 --indice=7\n";
            $resumen[] = ['cuf' => $cuf, 'ok' => false, 'detalle' => 'ya estaba anulada'];
            continue;
        }

        echo "\n  --- Intento de revertir una factura que NO esta anulada ---\n";
        echo "  Si el SIN lo acepta, el control de estado no esta sirviendo:\n";
        $r = revertir($client, $cred, $pv, $cuf, $codigoEmision);
        echo "\n  --- Respuesta cruda ---\n" . ($r['crudo'] ?: '(vacia)') . "\n";
        mostrar('reversion       ', $r);

        $despues = consultarEstado($client, $cred, $pv, $cuf, $codigoEmision);
        mostrar('estado posterior', $despues);

        $rechazada = !$r['ok'] || empty($r['transaccion']);
        echo "\n  VEREDICTO: " . ($rechazada
            ? "el SIN RECHAZO la reversion (control negativo correcto)"
            : "el SIN ACEPTO la reversion: el estado no se estaba validando") . "\n";

        $resumen[] = ['cuf' => $cuf, 'ok' => $rechazada, 'estado' => $r['codigoEstado'] ?? 'error'];
        continue;
    }

    // --- Ciclo completo
    if ($modoCiclo) {
        echo "\n  --- 1/5 anular ---\n";
        $a = anular($client, $cred, $pv, $cuf, $obj['motivo'] ?? $codigoMotivo, $codigoEmision);
        mostrar('anulacion       ', $a);

        echo "\n  --- 2/5 estado tras anular ---\n";
        $s1 = consultarEstado($client, $cred, $pv, $cuf, $codigoEmision);
        mostrar('estado          ', $s1);

        echo "\n  --- 3/5 revertir ---\n";
        $v = revertir($client, $cred, $pv, $cuf, $codigoEmision);
        mostrar('reversion       ', $v);

        echo "\n  --- 4/5 estado tras revertir ---\n";
        $s2 = consultarEstado($client, $cred, $pv, $cuf, $codigoEmision);
        mostrar('estado          ', $s2);

        echo "\n  --- 5/5 anular de nuevo (deja la factura como estaba) ---\n";
        $a2 = anular($client, $cred, $pv, $cuf, $obj['motivo'] ?? $codigoMotivo, $codigoEmision);
        mostrar('anulacion       ', $a2);

        $s3 = consultarEstado($client, $cred, $pv, $cuf, $codigoEmision);
        mostrar('estado final    ', $s3);

        /* El piloto no permite volver a anular una factura a la que ya se le
           revirtio la anulacion: la segunda anulacion vuelve con 906 y el
           mensaje 981 "REVERSION DE ANULACION NO DISPONIBLE PARA LA FACTURA O
           NOTA DE CREDITO - DEBITO". Comprobado sobre dos facturas distintas,
           tanto una recien revertida como una que venia de la etapa 7. En una
           factura nunca anulada la anulacion si funciona (905), asi que no es
           un tope global del NIT sino una regla por factura.

           Por eso el ciclo tiene dos finales validos: el de ida y vuelta
           completo, o el que cierra en VALIDA porque el piloto ya no admite
           la segunda anulacion. */
        /* La precondicion de $estabaAnulada no aplica en modo ciclo: aca la
           anulacion es el paso 1, asi que la factura arranca en VALIDA y el
           estado que importa es el de $s1 (despues de anular), no el inicial. */
        $huboAnulacion = stripos($s1['descripcion'], 'ANULADA') !== false;
        $huboReversion = stripos($s2['descripcion'], 'ANULADA') === false;
        $volvioAnulada = stripos($s3['descripcion'], 'ANULADA') !== false;
        $pilotoCerrado = stripos(implode(' ', $a2['mensajes'] ?? []), '981') !== false;

        if ($huboAnulacion && $huboReversion && $volvioAnulada) {
            $cicloOk = true;
            $nota = 'CICLO COMPLETO (anulada -> revertida -> anulada)';
        } elseif ($huboAnulacion && $huboReversion && !$volvioAnulada && $pilotoCerrado) {
            $cicloOk = true;
            $nota = 'CICLO CERRADO POR EL PILOTO (anulada -> revertida; el SIN ya no admite '
                  . 'la segunda anulacion con 981)';
        } else {
            $cicloOk = false;
            $nota = 'CICLO INCOMPLETO: revisar los estados de arriba';
        }

        echo "\n  VEREDICTO: {$nota}\n";
        if ($huboAnulacion && $huboReversion) {
            echo "  La anulacion y la reversion quedaron confirmadas por el SIN.\n";
        }

        $resumen[] = ['cuf' => $cuf, 'ok' => $cicloOk,
                      'estado' => $cicloOk ? 'ciclo ok' : 'revisar'];
        continue;
    }

    // --- Camino normal: revertir
    if (!$estabaAnulada) {
        if (!$anularPrimero) {
            echo "\n  La factura NO esta anulada, asi que no hay nada que revertir.\n";
            echo "  Con --anular-primero el script la anula antes de revertirla.\n";
            $resumen[] = ['cuf' => $cuf, 'ok' => false, 'detalle' => 'no estaba anulada'];
            continue;
        }

        echo "\n  No estaba anulada: se anula primero para crear el estado previo.\n";
        $a = anular($client, $cred, $pv, $cuf, $obj['motivo'] ?? $codigoMotivo, $codigoEmision);
        mostrar('anulacion previa ', $a);

        $antes = consultarEstado($client, $cred, $pv, $cuf, $codigoEmision);
        mostrar('estado ahora     ', $antes);

        if (stripos($antes['descripcion'], 'ANULADA') === false) {
            echo "\n  La anulacion no quedo firme; no se sigue con la reversion.\n";
            $resumen[] = ['cuf' => $cuf, 'ok' => false, 'detalle' => 'la anulacion previa no se confirmo'];
            continue;
        }
    }

    echo "\n  --- Reversion ---\n";
    $r = revertir($client, $cred, $pv, $cuf, $codigoEmision);

    echo "\n  --- Respuesta cruda ---\n" . ($r['crudo'] ?: '(vacia)') . "\n";
    mostrar('reversion       ', $r);

    $confirmada = $r['ok'] && !empty($r['transaccion']);

    if (!$sinVerificar) {
        echo "\n" . str_repeat('-', 60) . "\n";
        $despues = consultarEstado($client, $cred, $pv, $cuf, $codigoEmision);
        mostrar('estado posterior ', $despues);

        if ($despues['ok']) {
            $cambio = stripos($despues['descripcion'], 'ANULADA') === false;
            echo "\n  El estado ya no dice ANULADA: " . ($cambio ? "SI" : "NO") . "\n";
        }
    }

    echo "\n  VEREDICTO: " . ($confirmada
        ? "REVERSION ACEPTADA por el SIN"
        : "REVERSION NO ACEPTADA") . "\n";

    $resumen[] = ['cuf' => $cuf, 'ok' => $confirmada, 'estado' => $r['codigoEstado'] ?? ($r['error'] ?? 'error')];
}

// --- 7. Resumen -----------------------------------------------------------
echo "\n" . str_repeat('=', 74) . "\n";
echo "RESUMEN\n";
echo str_repeat('=', 74) . "\n";
foreach ($resumen as $r) {
    printf("  %-7s %s  %s\n",
        $r['ok'] ? 'OK' : 'FALLA',
        substr($r['cuf'], -12),
        $r['estado'] ?? ($r['detalle'] ?? ''));
}
echo "\n=== FIN ETAPA 9 ===\n";
