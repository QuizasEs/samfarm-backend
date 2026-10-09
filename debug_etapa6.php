<?php
/**
 * ETAPA 6 - Emision de facturas en contingencia y envio del paquete al SIN.
 *
 * El ciclo completo es:
 *
 *   1. Elegir un evento significativo YA VALIDADO en la Etapa 5. De el se
 *      toman dos cosas:
 *        - codigoRecepcionEventoSignificativo -> es el <codigoEvento> que
 *          exige recepcionPaqueteFactura. Sin el, el SIN no puede asociar el
 *          paquete a ninguna contingencia y lo rechaza.
 *        - ev_cufd_evento -> el CUFD que estaba vigente CUANDO OCURRIO la
 *          contingencia. Es el que debe ir dentro del XML de la factura.
 *
 *   2. Emitir la factura con tipo de emision 2 (fuera de linea). El CUF se
 *      genera con tipoEmision=2, que es donde queda registrado que la
 *      factura salio durante la contingencia. El tipo de emision NO se
 *      escribe en el XML: el XSD de la factura no tiene ese campo.
 *
 *   3. Firmar el XML con el certificado y validarlo contra el XSD, igual que
 *      en la Etapa 4. Un XML que no valida se rechaza con 939, asi que es
 *      mas barato comprobarlo aqui que gastarse una recepcion.
 *
 *   4. Guardar la factura como CONTINGENCIA en facturacion_electronica.
 *      EnviarPaqueteContingencia la recoge sola de ahi.
 *
 *   5. Armar el TAR.GZ, enviarlo y consultar el resultado con
 *      validacionRecepcionPaquete.
 *
 * OJO con los dos CUFD, que es el error clasico:
 *   - el del XML de la factura = el que estaba vigente en la contingencia
 *   - el del sobre SOAP        = el vigente al ENVIAR el paquete
 * Si se manda el mismo, el SIN responde 984.
 *
 * Uso:
 *   php debug_etapa6.php                  1 factura en contingencia
 *   php debug_etapa6.php --facturas=3     varias, en un solo paquete
 *   php debug_etapa6.php --factura=631     forzar una factura concreta
 *   php debug_etapa6.php --evento=EVT-...  forzar un evento concreto
 *   php debug_etapa6.php --sin-enviar    solo arma y valida, no llama al SIN
 *   php debug_etapa6.php --factura=625 --solo-empaquetar
 *                                        no emite nada: manda las facturas que
 *                                        ya estan en CONTINGENCIA
 */

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);
date_default_timezone_set('America/La_Paz');

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

define('SIAT_NIT', '6006007013');

// mainModel decide como resolver sus includes segun $peticionAjax: en true usa
// "../config/...", o sea que el CWD tiene que ser un subdirectorio de la app.
// Por eso el chdir a models/. Mismo criterio que debug_etapa4.php.
$peticionAjax = true;
chdir(__DIR__ . '/models');
require_once __DIR__ . '/models/siatModel.php';

$op = getopt('', ['facturas::', 'factura::', 'evento::', 'sin-enviar', 'reintentar::', 'solo-empaquetar']);
$TOTAL_FACTURAS = isset($op['facturas']) ? max(1, (int)$op['facturas']) : 1;
// Apunta a una factura puntual y saltea el filtro por sucursal. Necesario
// mientras las demas sucursales no tengan CUFD propio en el SIN: la factura se
// emite con los codigos y credenciales de la sucursal del evento, que es la
// unica registrada.
$FA_FIJA       = isset($op['factura']) ? (int)$op['factura'] : null;
$EVENTO_FIJO    = $op['evento'] ?? null;
$SOLO_LOCAL     = isset($op['sin-enviar']);
$SOLO_EMPAQUETAR = isset($op['solo-empaquetar']);
$REINTENTAR_PA  = isset($op['reintentar']) ? (int)$op['reintentar'] : null;

echo "=== ETAPA 6 - PAQUETE DE FACTURAS EN CONTINGENCIA (SIN PILOTO) ===\n\n";
echo "Facturas a emitir : " . ($FA_FIJA ? "1 (forzada fa_id={$FA_FIJA})" : $TOTAL_FACTURAS) . "\n";
echo "Evento forzado     : " . ($EVENTO_FIJO ?: '(el validado mas reciente)') . "\n";
echo "Envio al SIN      : " . ($SOLO_LOCAL ? 'NO (--sin-enviar)' : 'si') . "\n";
if ($SOLO_EMPAQUETAR) {
    echo "Emision           : NO (--solo-empaquetar, se reenvia lo ya emitido)\n";
}
if ($REINTENTAR_PA) {
    echo "Reintento         : paquete pa_id={$REINTENTAR_PA} (se reusa el archivo guardado)\n";
}
echo "\n";

/* ---------------------------------------------------------------------------
   MODO REINTENTO: no se emiten facturas ni se arma nada. Se reenvia un paquete
   que ya quedo guardado, reutilizando pa_archivo y pa_hash_archivo. Es lo que
   hay que hacer cuando la llamada anterior cayo por red: el TAR.GZ tiene que
   ser el mismo, asi que no se puede regenerar.
   --------------------------------------------------------------------------- */
function reintentarPaquete($pdo, $paId)
{
    echo str_repeat('=', 72) . "\n";
    echo "REINTENTO DEL PAQUETE pa_id={$paId}\n";
    echo str_repeat('=', 72) . "\n\n";

    $recepcion = null;
    // El piloto se cae seguido; se reintenta la llamada antes de rendirse.
    for ($i = 1; $i <= 4; $i++) {
        try {
            $recepcion = siatModel::reenviarPaquete($paId);
            break;
        } catch (SoapFault $e) {
            echo "  intento {$i}: SoapFault - " . $e->getMessage() . "\n";
            if ($i < 4) { sleep(20); }
        } catch (Throwable $e) {
            echo "  ERROR: " . $e->getMessage() . "\n";
            break;
        }
    }

    if (!$recepcion) {
        $fila = $pdo->query("SELECT pa_estado, pa_cantidad_facturas, pa_mensajes
                             FROM siat_paquetes WHERE pa_id = {$paId}")->fetch(PDO::FETCH_ASSOC);
        echo "\n  estado={$fila['pa_estado']} facturas={$fila['pa_cantidad_facturas']}\n";
        if (!empty($fila['pa_mensajes'])) {
            echo "  " . $fila['pa_mensajes'] . "\n";
        }
        return 1;
    }

    echo "  RECEPCION OBTENIDA: {$recepcion}\n\n";

    try {
        $resp = siatModel::validarPaqueteContingencia(1, null, $recepcion);
        echo "  transaccion : " . var_export($resp->transaccion ?? null, true) . "\n";
        echo "  codigoEstado: " . ($resp->codigoEstado ?? '-') . " "
           . ($resp->codigoDescripcion ?? '') . "\n";
        foreach ((array)($resp->mensajesList ?? []) as $m) {
            $c = $m->codigo ?? '?'; $t = trim($m->descripcion ?? '');
            if ($c === '?' && $t === '') { continue; }
            echo "  msg         : [$c] $t\n";
        }
    } catch (Throwable $e) {
        echo "  validacion fallo: " . $e->getMessage() . "\n";
    }
    return 0;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=samfarm_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Error de conexion: " . $e->getMessage() . "\n");
}

if ($REINTENTAR_PA) {
    $rc = reintentarPaquete($pdo, $REINTENTAR_PA);
    echo "\n=== FIN ===\n";
    exit($rc);
}

echo "Certificado: " . SIAT_CERT_P12_PATH . "\n\n";

// ===========================================================================
// 1. Evento significativo validado -> codigoEvento + CUFD de la contingencia
// ===========================================================================
echo str_repeat('=', 72) . "\n";
echo "PASO 1  Evento significativo de referencia\n";
echo str_repeat('=', 72) . "\n";

if ($EVENTO_FIJO) {
    $ev = $pdo->prepare("SELECT * FROM siat_eventos
                         WHERE ev_codigo = :c AND ev_estado = 'validado'");
    $ev->execute([':c' => $EVENTO_FIJO]);
    $evento = $ev->fetch(PDO::FETCH_ASSOC);
    if (!$evento) {
        die("No hay ningun evento VALIDADO con codigo {$EVENTO_FIJO}\n");
    }
} else {
    $evento = $pdo->query("SELECT * FROM siat_eventos
                           WHERE ev_estado = 'validado' AND ev_cufd_evento IS NOT NULL
                           ORDER BY ev_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if (!$evento) {
        die("No hay eventos validados. Ejecuta primero debug_etapa5_loop.php\n");
    }
}

// El codigo de recepcion del evento quedo guardado dentro de ev_respuesta_siat
// como {"recepcion":"9938749",...}. Si no esta ahi, no hay con que armar el
// codigoEvento del paquete. debug_etapa5.php guarda la clave que devuelve el SIN
// (codigoRecepcionEventoSignificativo), asi que se aceptan las dos.
$respEv = json_decode($evento['ev_respuesta_siat'] ?? '{}', true);
$codigoEvento = $respEv['recepcion']
    ?? $respEv['codigoRecepcionEventoSignificativo']
    ?? null;

echo "  ev_codigo    : {$evento['ev_codigo']}\n";
echo "  motivo       : {$evento['ev_codigo_evento']} - {$evento['ev_descripcion']}\n";
echo "  periodo      : {$evento['ev_fecha_inicio']} -> {$evento['ev_fecha_fin']}\n";
echo "  cufdEvento   : {$evento['ev_cufd_evento']}\n";
echo "  recepcion    : " . ($codigoEvento ?: 'NO REGISTRADA') . "\n";

// El CUF lo calcula el SIN con el codigo de control del CUFD que va DENTRO del
// XML, o sea el de la contingencia (cufdEvento), no el del CUFD vigente. Si se
// usa el equivocado el SIN observa la factura con 1002 "EL CUF ENVIADO EN EL
// XML ES INVALIDO CUF esperado ... enviado ...".
$cufdControlEvento = $respEv['cufd_control'] ?? null;

if (!$codigoEvento) {
    die("\nERROR: el evento no tiene codigo de recepcion guardado.\n"
        . "       Sin el no se puede armar el codigoEvento del paquete.\n");
}

// El CUFD de la contingencia tiene que haber caducado ya: es el que estaba
// vigente durante el evento y las facturas se transcriben despues. Se avisa
// si aun esta vivo porque el SIN puede rechazar el paquete.
$cufdEvento = $evento['ev_cufd_evento'];
$cufdVig   = $pdo->query("SELECT sc_cufd, sc_cufd_control, sc_cufd_vigente_hasta
                          FROM siat_configuracion
                          WHERE su_id = " . (int)$evento['su_id'])->fetch(PDO::FETCH_ASSOC);

if ($cufdEvento === $cufdVig['sc_cufd']) {
    die("\nERROR: cufdEvento y el CUFD vigente son el mismo (" . $cufdEvento . ").\n"
        . "       El SIN responderia 984. Pide un CUFD nuevo antes de seguir.\n");
}
echo "  CUFD vigente : {$cufdVig['sc_cufd']}\n";
echo "  (distinto del cufdEvento: correcto, el SIN exige que difieran)\n\n";

// La factura en contingencia tiene que estar fechada dentro del periodo del
// evento, que es cuando el cufdEvento estaba vigente. Se toma un momento
// dentro de ese periodo, a un minuto del inicio para no chocar con los limites.
$iniEvento = strtotime($evento['ev_fecha_inicio']);
$finEvento = strtotime($evento['ev_fecha_fin']);
$fechaEmisionEvento = min($iniEvento + 60, $finEvento - 30);

if ($fechaEmisionEvento <= $iniEvento || $fechaEmisionEvento >= $finEvento) {
    die("\nERROR: el periodo del evento es demasiado corto para fechar una factura dentro.\n");
}

$suId = (int)$evento['su_id'];

echo "  fecha de emision de la factura: " . date('Y-m-d H:i:s', $fechaEmisionEvento)
   . "  (dentro del periodo del evento)\n\n";

// ===========================================================================
// 2-4. Emitir las facturas en contingencia
// ===========================================================================
echo str_repeat('=', 72) . "\n";
echo "PASO 2-4  Emision de las facturas en contingencia\n";
echo str_repeat('=', 72) . "\n\n";

$cfg = $pdo->query("SELECT * FROM siat_configuracion WHERE su_id = {$suId}")->fetch(PDO::FETCH_ASSOC);
$cuis             = $cfg['sc_cuis'];
$codigoSucursal   = (int)$cfg['sc_sucursal_codigo'];
$codigoPuntoVenta = (int)$cfg['sc_punto_venta_codigo'];
// El control del CUFD de la contingencia manda; el del CUFD vigente solo se usa
// si el evento no lo guardó (eventos registrados antes de este arreglo).
$cufdControl      = $cufdControlEvento ?: $cfg['sc_cufd_control'];

echo "Sucursal {$suId} | CUIS {$cuis} | sucursal {$codigoSucursal} | ptoVenta {$codigoPuntoVenta}\n";
echo "control del CUF : {$cufdControl}  ("
    . ($cufdControlEvento ? 'del CUFD de la contingencia' : 'del CUFD vigente: el evento no lo guardo') . ")\n\n";

$firmar = new ReflectionMethod('siatModel', 'firmarXML');
$firmar->setAccessible(true);
$xsdPath = __DIR__ . '/libs/xmlseclibs/xsd/CompraVenta/facturaElectronicaCompraVenta.xsd';

$emitidas = [];
$descartadas = [];
$paso    = 0;

// Tope de vueltas. El while de abajo solo avanza cuando una factura se emite
// con exito; si todas fallaran, sin este tope no terminaria nunca.
$MAX_INTENTOS = 50;

while (!$SOLO_EMPAQUETAR && count($emitidas) < $TOTAL_FACTURAS) {
    if (++$paso > $MAX_INTENTOS) {
        echo "\n  Se alcanzó el tope de {$MAX_INTENTOS} intentos. Cortando para no "
           . "quemar mas numeros de secuencia.\n";
        break;
    }

    // Se excluyen tanto las ya emitidas como las que ya fallaron. Sin esto, un
    // fallo hacia 'continue' sin tocar $emitidas y el while volvia a elegir la
    // misma factura una y otra vez, consumiendo un numero de secuencia por
    // vuelta.
    $excluidas = array_merge(
        array_column($emitidas, 'fa_id'),
        $descartadas
    );

    if ($FA_FIJA) {
        // Factura forzada: se saltea el filtro de v.su_id porque la venta puede
        // ser de una sucursal que aun no tiene CUFD propio. Lo que no se saltea
        // es la coherencia minima: tiene que tener detalle, no tener CUF y no
        // haberse registrado antes en facturacion_electronica.
        $sql = "
            SELECT v.ve_id, v.ve_total, v.ve_fecha_emision AS ve_fecha,
                   v.ve_codigo_moneda, v.ve_tipo_cambio, v.su_id,
                   c.cl_carnet, c.cl_nombres, c.cl_apellido_paterno, c.cl_id,
                   c.cl_tipo_documento,
                   ce.ce_nit, ce.ce_nombre, ce.ce_direccion, ce.ce_telefono,
                   ce.ce_municipio, ce.ce_codigo_actividad,
                   f.fa_id, f.fa_numero_control, f.fa_cuf, f.fa_numero_siat,
                   f.fa_leyenda, f.fa_monto_sujeto_iva, f.fa_descuento_adicional,
                   f.fa_codigo_metodo_pago, f.fa_numero_tarjeta,
                   f.fa_codigo_moneda, f.fa_tipo_cambio, f.fa_doc_sector,
                   f.fa_tipo_factura, f.fa_modalidad,
                   u.us_nombres AS us_nombre
            FROM ventas v
            JOIN factura f ON f.ve_id = v.ve_id
            LEFT JOIN clientes c ON c.cl_id = v.cl_id
            JOIN configuracion_empresa ce ON ce.ce_id = 1
            JOIN usuarios u ON u.us_id = v.us_id
            WHERE f.fa_id = :fa_id
              AND f.fa_cuf IS NULL
              AND EXISTS (SELECT 1 FROM detalle_venta dv
                          WHERE dv.ve_id = v.ve_id AND dv.dv_estado = 1)
              AND NOT EXISTS (SELECT 1 FROM facturacion_electronica fe
                              WHERE fe.fa_id = f.fa_id)
            LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([':fa_id' => $FA_FIJA]);
    } else {
        // Solo facturas que ya tengan detalle y esten listas para emitir.
        $sql = "
            SELECT v.ve_id, v.ve_total, v.ve_fecha_emision AS ve_fecha,
                   v.ve_codigo_moneda, v.ve_tipo_cambio, v.su_id,
                   c.cl_carnet, c.cl_nombres, c.cl_apellido_paterno, c.cl_id,
                   c.cl_tipo_documento,
                   ce.ce_nit, ce.ce_nombre, ce.ce_direccion, ce.ce_telefono,
                   ce.ce_municipio, ce.ce_codigo_actividad,
                   f.fa_id, f.fa_numero_control, f.fa_cuf, f.fa_numero_siat,
                   f.fa_leyenda, f.fa_monto_sujeto_iva, f.fa_descuento_adicional,
                   f.fa_codigo_metodo_pago, f.fa_numero_tarjeta,
                   f.fa_codigo_moneda, f.fa_tipo_cambio, f.fa_doc_sector,
                   f.fa_tipo_factura, f.fa_modalidad,
                   u.us_nombres AS us_nombre
            FROM ventas v
            JOIN factura f ON f.ve_id = v.ve_id
            LEFT JOIN clientes c ON c.cl_id = v.cl_id
            JOIN configuracion_empresa ce ON ce.ce_id = 1
            JOIN usuarios u ON u.us_id = v.us_id
            WHERE v.su_id = :su_id
              AND f.fa_cuf IS NULL
              AND EXISTS (SELECT 1 FROM detalle_venta dv
                          WHERE dv.ve_id = v.ve_id AND dv.dv_estado = 1)
              AND NOT EXISTS (SELECT 1 FROM facturacion_electronica fe
                              WHERE fe.fa_id = f.fa_id)
              AND f.fa_id NOT IN (" . (count($excluidas) ? implode(',', $excluidas) : '0') . ")
            ORDER BY f.fa_id DESC
            LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute([':su_id' => $suId]);
    }
    $datos = $st->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        if ($FA_FIJA) {
            echo "  La factura forzada fa_id={$FA_FIJA} no es emitible.\n";
            $r = $pdo->query("SELECT f.fa_id, f.fa_cuf, f.fa_numero_siat,
                                     (SELECT COUNT(*) FROM detalle_venta dv WHERE dv.ve_id=f.ve_id AND dv.dv_estado=1) items,
                                     (SELECT COUNT(*) FROM facturacion_electronica fe WHERE fe.fa_id=f.fa_id) regs
                              FROM factura f WHERE f.fa_id = " . (int)$FA_FIJA)->fetch(PDO::FETCH_ASSOC);
            if (!$r) {
                echo "    No existe esa factura.\n";
            } else {
                echo "    items con detalle={$r['items']}  registros en facturacion_electronica={$r['regs']}"
                   . "  cuf=" . ($r['fa_cuf'] ?: 'NULL') . "\n";
                if (!empty($r['fa_cuf'])) {
                    echo "    Ya tiene CUF, no se puede reemitir por la etapa 6.\n";
                }
                if ($r['regs'] > 0) {
                    echo "    facturacion_electronica tiene UNIQUE ux_fe_fa_id: ya esta registrada.\n";
                }
            }
            break;
        }

        $hechas = count($emitidas);
        echo "  No quedan facturas con detalle, sin CUF y sin registro previo "
           . "en facturacion_electronica para emitir. Se emitieron {$hechas}.\n";

        // Diagnosticar por que no hay candidatas ayuda a no pensar que el
        // script esta roto cuando en realidad ya no queda nada por hacer.
        $q = $pdo->query("
            SELECT COUNT(*) FROM ventas v
            JOIN factura f ON f.ve_id = v.ve_id
            WHERE v.su_id = {$suId} AND f.fa_cuf IS NULL
              AND EXISTS (SELECT 1 FROM detalle_venta dv
                          WHERE dv.ve_id = v.ve_id AND dv.dv_estado = 1)");
        $sinCuf = (int)$q->fetchColumn();
        if ($sinCuf > 0) {
            echo "  Hay {$sinCuf} factura(s) sin CUF, pero ya tienen fila en "
               . "facturacion_electronica: no se pueden reemitir porque el "
               . "UNIQUE ux_fe_fa_id lo impide.\n";
            echo "  Revisa si alguna quedo a medias con una corrida anterior "
               . "interrumpida.\n";
        } else {
            echo "  No hay ninguna venta con detalle pendiente de emitir. "
               . "Hay que crear una venta nueva.\n";
        }
        break;
    }

    $faId = (int)$datos['fa_id'];
    echo "  Factura fa_id={$faId}  numero={$datos['fa_numero_control']}"
       . "  (venta ve_id={$datos['ve_id']}, su_id={$datos['su_id']})\n";
    if ((int)$datos['su_id'] !== $suId) {
        echo "    OJO: la venta es de la sucursal {$datos['su_id']} pero el evento y "
           . "las credenciales son de la {$suId}. Se emite con los codigos de la {$suId}.\n";
    }

    // A partir de aca cualquier fallo marca la factura como descartada para
    // que el while no la vuelva a elegir.
    $descartadas[] = $faId;

    // El SIN exige un documento de identidad real del cliente: con
    // numeroDocumento = 0 (venta sin cliente registrado) la factura se observa
    // con 1048 "NUMERO DE DOCUMENTO NO VALIDO". Se descarta antes de tocar la
    // secuencia para no gastar un numero ni un CUF en una factura que el SIN
    // va a rechazar.
    if (trim((string)($datos['cl_carnet'] ?? '')) === '') {
        echo "    sin cliente registrado: numeroDocumento quedaria en 0 y el SIN\n"
           . "    la observa con 1048. Se omite sin gastar numero ni CUF.\n\n";
        continue;
    }

    $det = $pdo->prepare("
        SELECT dv.dv_cantidad, dv.dv_precio_unitario, dv.dv_descuento, dv.dv_subtotal,
               dv.dv_unidad_medida_sin, m.med_id,
               m.med_nombre_quimico AS med_nombre, m.med_codigo_sin
        FROM detalle_venta dv
        JOIN medicamento m ON m.med_id = dv.med_id
        WHERE dv.ve_id = :ve AND dv.dv_estado = 1");
    $det->execute([':ve' => $datos['ve_id']]);
    $detalles = $det->fetchAll(PDO::FETCH_ASSOC);

    if (!$detalles) {
        echo "    (sin detalle activo, se omite)\n\n";
        continue;
    }

    // --- Actividad economica y codigos de producto del SIN ------------------
    //
    // Se resuelven ANTES de la numeracion: si algun producto no tiene codigo
    // del SIN, la factura no es emitible y hay que salir sin haber consumido un
    // numero de la secuencia ni un CUF.
    $actividad = siatModel::resolverActividadEconomica($datos['ce_codigo_actividad'] ?? null);
    echo "    actividad  : {$actividad}  (del catalogo SIN)\n";

    $codigosSin = [];
    $sinCodigoSin = [];
    foreach ($detalles as $d) {
        // Primero el codigo cargado en el medicamento; si no hay, se busca en
        // siat_productos (etapa 2).
        //
        // No se usa 99900 ("OTROS") como ultimo recurso: ese codigo no esta en
        // el catalogo del SIN para ninguna actividad y el paquete vuelve
        // observado con 1017 "EL CODIGO DE PRODUCTO NO ESTA RELACIONADO A
        // NINGUN ACTIVIDAD ECONOMICA DEL CONTRIBUYENTE". Un producto sin codigo
        // es un dato pendiente de carga (medicamento.med_codigo_sin), no algo
        // que se pueda inventar en la factura.
        $codigoSin = trim((string)($d['med_codigo_sin'] ?? ''));
        $origenSin = 'medicamento.med_codigo_sin';
        if ($codigoSin === '') {
            $codigoSin = (string)(siatModel::resolverCodigoProductoSIN($d['med_nombre'] ?? '', $actividad) ?? '');
            $origenSin = 'siat_productos';
        }
        if ($codigoSin === '') {
            $sinCodigoSin[] = $d['med_nombre'];
            continue;
        }
        $codigosSin[(int)$d['med_id']] = ['codigo' => $codigoSin, 'origen' => $origenSin];
    }

    if ($sinCodigoSin) {
        echo "    ERROR: " . count($sinCodigoSin) . " producto(s) sin codigo del SIN:\n";
        foreach ($sinCodigoSin as $nombre) {
            echo "      - {$nombre}\n";
        }
        echo "    Cargar el codigo en medicamento.med_codigo_sin (o ajustar el\n";
        echo "    nombre para que el resolver lo encuentre en siat_productos).\n";
        echo "    Se omite la factura sin gastar numero ni CUF.\n\n";
        continue;
    }

    foreach ($detalles as $d) {
        $info = $codigosSin[(int)$d['med_id']];
        echo "    producto   : {$d['med_nombre']} -> codigoProductoSin={$info['codigo']}"
           . "  [{$info['origen']}]\n";
    }

    // --- Numeracion: se consume un numero real de la secuencia --------------
    // --- Fecha de emision -------------------------------------------------
    //
    // NO se usa la fecha de la venta. El CUF lleva la fecha de emision
    // dentro de si mismo, asi que tiene que ser coherente con el CUFD que se
    // pone en el XML: el cufdEvento solo estuvo vigente el dia en que
    // ocurrio la contingencia, durante el periodo del evento.
    //
    // Con la fecha de la venta (que puede ser de semanas antes) el CUF queda
    // fechado fuera de la vigencia del CUFD, y el SIN rechaza la factura con
    // 902 y sin detalle. Es la causa clasica de este rechazo.
    $fechaEmision = $fechaEmisionEvento;
    $fechaSql     = date('Y-m-d H:i:s', $fechaEmision);

    $pdo->prepare("UPDATE factura_secuencia
                   SET fs_ultimo_nro = fs_ultimo_nro + 1
                   WHERE su_id = :su AND fs_anio = YEAR(:f) AND fs_punto_venta = :pv")
        ->execute([':su' => $suId, ':f' => $fechaSql, ':pv' => $codigoPuntoVenta]);
    $seq = $pdo->prepare("SELECT fs_ultimo_nro FROM factura_secuencia
                          WHERE su_id = :su AND fs_anio = YEAR(:f) AND fs_punto_venta = :pv");
    $seq->execute([':su' => $suId, ':f' => $fechaSql, ':pv' => $codigoPuntoVenta]);
    $numeroFactura = (int)($seq->fetchColumn() ?: 1);

    // --- CUF con tipoEmision = 2 (fuera de linea) --------------------------
    $cuf = siatModel::generarCUF(
        SIAT_NIT,
        $fechaSql,
        $numeroFactura,
        $cufdControl,
        $codigoSucursal,
        $codigoPuntoVenta,
        2,                                  // tipoEmision: CONTINGENCIA
        1,                                  // factura con derecho a credito
        (int)($datos['fa_doc_sector'] ?? 1)
    );

    if (!$cuf) {
        echo "    ERROR: no se pudo generar el CUF\n\n";
        continue;
    }

    // El CUF y el numero se persisten SOLO despues de que el XML haya validado
    // contra el XSD. Si se guardaran antes y el XML saliera mal, la factura
    // quedaria con un CUF consumido que no corresponde a ninguna emision, y
    // como el listado busca fa_cuf IS NULL, no se podria volver a intentar.
    echo "    numero    : {$numeroFactura}\n";
    echo "    CUF       : {$cuf}\n";
    echo "    cufd (XML): {$cufdEvento}  <- CUFD de la contingencia\n";

    $detalleXml = '';
    // --- XML ----------------------------------------------------------------
    $clienteNombre = trim(($datos['cl_nombres'] ?? '') . ' ' . ($datos['cl_apellido_paterno'] ?? ''));
    if ($clienteNombre === '') { $clienteNombre = 'Consumidor Final'; }
    $tipoDoc = ['CI' => 1, 'NIT' => 5, 'CE' => 4, 'Pasaporte' => 3][$datos['cl_tipo_documento'] ?? 'CI'] ?? 1;

    // La actividad economica y los codigos de producto se resolvieron mas
    // arriba, antes de gastar numero y CUF.

    $leyenda = $datos['fa_leyenda'];
    if ($leyenda === null || trim((string)$leyenda) === '') {
        $leyenda = siatModel::obtener_leyenda_siat_model($actividad);
        echo "    leyenda    : " . substr((string)$leyenda, 0, 60) . "...  (del catalogo SIAT)\n";
    }

    // --- codigoMetodoPago: la BD guarda texto, el XSD exige un entero -------
    //
    // fa_codigo_metodo_pago es un varchar(20) y historicamente se guardo como
    // texto ('efectivo'). El XSD lo define como xs:integer entre 1 y 308, asi
    // que mandar el texto crudo hacia que el documento NO valide y se descarte
    // antes de gastar una recepcion. Se traduce aqui en vez de tocar la BD.
    //
    // Catalogo SIAT: 1 Efectivo, 2 Tarjeta, 3 Codigo QR, 4 Transferencia
    // bancaria, 5 Cheque. Cualquier valor desconocido cae a 1 (efectivo),
    // que es el metodo por defecto del sistema.
    $metodoPagoCrudo = strtolower(trim((string)($datos['fa_codigo_metodo_pago'] ?? '')));
    $metodoPago = [
        ''            => 1,  // NULL o vacio: efectivo
        'efectivo'    => 1,
        'contado'     => 1,
        'tarjeta'     => 2,
        'tarjeta debito' => 2,
        'tarjeta credito' => 2,
        'debito'      => 2,
        'credito'     => 2,
        'codigo qr'   => 3,
        'codigoqr'    => 3,
        'qr'          => 3,
        'transferencia' => 4,
        'transferencia bancaria' => 4,
        'cheque'      => 5,
    ][$metodoPagoCrudo] ?? 1;

    // numeroTarjeta es un entero en el XSD, asi que una cadena vacia no valida.
    // Cuando no hay tarjeta hay que mandar el elemento nulo.
    $tarjeta = trim((string)($datos['fa_numero_tarjeta'] ?? ''));
    $tarjeta = ($metodoPago === 2 && $tarjeta !== '' && $tarjeta !== '0') ? $tarjeta : '';
    $tarjetaXml = $tarjeta !== ''
        ? "<numeroTarjeta>{$tarjeta}</numeroTarjeta>"
        : '<numeroTarjeta xsi:nil="true"/>';

    $detalleXml = '';
    foreach ($detalles as $d) {
        $codigoSin = $codigosSin[(int)$d['med_id']]['codigo'];

        $detalleXml .= '
    <detalle>
      <actividadEconomica>' . $actividad . '</actividadEconomica>
      <codigoProductoSin>' . $codigoSin . '</codigoProductoSin>
      <codigoProducto>' . $d['med_id'] . '</codigoProducto>
      <descripcion>' . $d['med_nombre'] . '</descripcion>
      <cantidad>' . $d['dv_cantidad'] . '</cantidad>
      <unidadMedida>' . ($d['dv_unidad_medida_sin'] ?? 1) . '</unidadMedida>
      <precioUnitario>' . $d['dv_precio_unitario'] . '</precioUnitario>
      <montoDescuento>' . ($d['dv_descuento'] ?? 0) . '</montoDescuento>
      <subTotal>' . $d['dv_subtotal'] . '</subTotal>
      <numeroSerie></numeroSerie>
      <numeroImei></numeroImei>
    </detalle>';
    }

    // El orden de los elementos importa: el XSD define una xs:sequence, asi
    // que un campo fuera de sitio invalida el documento. codigoDocumentoSector
    // es el ultimo y va fixed="1".
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<facturaElectronicaCompraVenta xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="facturaElectronicaCompraVenta.xsd">
  <cabecera>
    <nitEmisor>' . SIAT_NIT . '</nitEmisor>
    <razonSocialEmisor>' . $datos['ce_nombre'] . '</razonSocialEmisor>
    <municipio>' . ($datos['ce_municipio'] ?? 'La Paz') . '</municipio>
    <telefono>' . ($datos['ce_telefono'] ?? '0000000') . '</telefono>
    <numeroFactura>' . $numeroFactura . '</numeroFactura>
    <cuf>' . $cuf . '</cuf>
    <cufd>' . $cufdEvento . '</cufd>
    <codigoSucursal>' . $codigoSucursal . '</codigoSucursal>
    <direccion>' . ($datos['ce_direccion'] ?? 'DIRECCION SUCURSAL') . '</direccion>
    <codigoPuntoVenta>' . $codigoPuntoVenta . '</codigoPuntoVenta>
    <fechaEmision>' . date('c', $fechaEmision) . '</fechaEmision>
    <nombreRazonSocial>' . $clienteNombre . '</nombreRazonSocial>
    <codigoTipoDocumentoIdentidad>' . $tipoDoc . '</codigoTipoDocumentoIdentidad>
    <numeroDocumento>' . ($datos['cl_carnet'] ?: '0') . '</numeroDocumento>
    <complemento xsi:nil="true"/>
    <codigoCliente>' . ($datos['cl_id'] ?? 0) . '</codigoCliente>
    <codigoMetodoPago>' . $metodoPago . '</codigoMetodoPago>
    ' . $tarjetaXml . '
    <montoTotal>' . $datos['ve_total'] . '</montoTotal>
    <montoTotalSujetoIva>' . ($datos['fa_monto_sujeto_iva'] ?? $datos['ve_total']) . '</montoTotalSujetoIva>
    <codigoMoneda>' . ($datos['fa_codigo_moneda'] ?? $datos['ve_codigo_moneda'] ?? 1) . '</codigoMoneda>
    <tipoCambio>' . ($datos['fa_tipo_cambio'] ?? $datos['ve_tipo_cambio'] ?? '1.0000') . '</tipoCambio>
    <montoTotalMoneda>' . $datos['ve_total'] . '</montoTotalMoneda>
    <montoGiftCard xsi:nil="true"/>
    <descuentoAdicional>' . ($datos['fa_descuento_adicional'] ?? '0.00') . '</descuentoAdicional>
    <codigoExcepcion xsi:nil="true"/>
    <cafc xsi:nil="true"/>
    <leyenda>' . $leyenda . '</leyenda>
    <usuario>' . ($datos['us_nombre'] ?? 'admin') . '</usuario>
    <codigoDocumentoSector>1</codigoDocumentoSector>
  </cabecera>' . $detalleXml . '
</facturaElectronicaCompraVenta>';

    // --- Firma --------------------------------------------------------------
    try {
        $xmlFirmado = $firmar->invoke(null, $xml);
    } catch (Throwable $e) {
        echo "    ERROR firmando: " . $e->getMessage() . "\n\n";
        continue;
    }

    // --- Validacion contra el XSD antes de gastar una recepcion -------------
    libxml_use_internal_errors(true);
    libxml_clear_errors();
    $domValida = new DOMDocument();
    $domValida->preserveWhiteSpace = true;
    $domValida->loadXML($xmlFirmado);

    if (!$domValida->schemaValidate($xsdPath)) {
        echo "    ERROR: el XML firmado no valida contra el XSD:\n";
        foreach (libxml_get_errors() as $err) {
            echo "      linea {$err->line}: " . trim($err->message) . "\n";
        }
        libxml_clear_errors();
        echo "    No se envia nada al SIN.\n\n";
        continue;
    }
    libxml_clear_errors();
    echo "    XML firmado y validado contra el XSD (" . strlen($xmlFirmado) . " bytes)\n";

    // --- Registro en facturacion_electronica ------------------------------
    //
    // Va ANTES de tocar la tabla factura a proposito. facturacion_electronica
    // tiene UNIQUE ux_fe_fa_id (una sola fila por factura), asi que si la
    // factura ya estaba registrada el INSERT revienta. Si el CUF se guardara
    // primero, la factura quedaria con un CUF consumido que no corresponde a
    // ningun XML, y como el listado busca fa_cuf IS NULL ya no se podria
    // reintentar: queda bloqueada para siempre.
    try {
        siatModel::emitirEnContingencia($faId, $cuf, $xmlFirmado, $suId);
    } catch (Throwable $e) {
        echo "    ERROR al registrar en facturacion_electronica: "
           . $e->getMessage() . "\n";
        echo "    El CUF {$numeroFactura} NO se guardo, la factura sigue "
           . "disponible para reintentar.\n\n";
        continue;
    }

    // --- Persistencia ------------------------------------------------------
    // El CUF se guarda ahora que el INSERT funciono. fa_cufd queda con el
    // CUFD de la contingencia, que es el que viajaba en el XML firmado.
    $pdo->prepare("UPDATE factura
                   SET fa_cuf = :cuf, fa_numero_siat = :nro, fa_cufd = :cufd
                   WHERE fa_id = :id")
        ->execute([':cuf' => $cuf, ':nro' => $numeroFactura,
                   ':cufd' => $cufdEvento, ':id' => $faId]);

    // emitirEnContingencia() usa su propia conexion, asi que lastInsertId()
    // sobre $pdo devuelve 0. El fe_id se recupera por fa_id.
    $feId = (int)$pdo->query("SELECT fe_id FROM facturacion_electronica
                              WHERE fa_id = {$faId} ORDER BY fe_id DESC LIMIT 1")->fetchColumn();
    echo "    Guardada en facturacion_electronica fe_id={$feId} (CONTINGENCIA)\n\n";

    $emitidas[] = ['fa_id' => $faId, 'fe_id' => $feId, 'cuf' => $cuf];
}

if (!$emitidas && !$SOLO_EMPAQUETAR) {
    die("\nNo se emitio ninguna factura en contingencia. Abortando.\n");
}

if ($SOLO_EMPAQUETAR) {
    echo "  --solo-empaquetar: no se emite nada nuevo, se manda lo que ya esta\n"
       . "  en facturacion_electronica como CONTINGENCIA.\n\n";
}

if ($emitidas) {
    echo "  Total emitidas: " . count($emitidas) . "\n\n";
}

// ===========================================================================
// 5. Paquete
// ===========================================================================
echo str_repeat('=', 72) . "\n";
echo "PASO 5  Armado y envio del paquete\n";
echo str_repeat('=', 72) . "\n\n";

// Con --factura la factura puede ser de una sucursal distinta a la del evento,
// asi que el paquete no se filtra por f.su_id: si se filtrara, la factura recien
// emitida quedaria fuera del TAR.GZ y se guardaria como CONTINGENCIA para
// siempre sin llegar jamas al SIN.
if ($FA_FIJA) {
    $sqlPaq = "
        SELECT fe.fe_id, f.fa_cuf, fe.fe_payload
        FROM facturacion_electronica fe
        JOIN factura f ON f.fa_id = fe.fa_id
        WHERE fe.fe_tipo_emision = 2
          AND fe.fe_estado_siat = 'CONTINGENCIA'
          AND fe.fa_id = " . (int)$FA_FIJA . "
        ORDER BY fe.fe_id";
    $facturas = $pdo->query($sqlPaq)->fetchAll(PDO::FETCH_OBJ);
} else {
    $facturas = $pdo->query("
        SELECT fe.fe_id, f.fa_cuf, fe.fe_payload
        FROM facturacion_electronica fe
        JOIN factura f ON f.fa_id = fe.fa_id
        WHERE fe.fe_tipo_emision = 2
          AND fe.fe_estado_siat = 'CONTINGENCIA'
          AND f.su_id = " . $suId . "
        ORDER BY fe.fe_id")->fetchAll(PDO::FETCH_OBJ);
}

// Inspección del tar antes de enviarlo: si el TAR.GZ no se puede abrir, el SIN
// lo rechaza entero, y es más barato verlo acá.
$paquete = siatModel::armarPaqueteTargz($facturas);
echo "  Facturas en el paquete : " . count($facturas) . " (max " . SIAT_MAX_FACTURAS_CONTINGENCIA . ")\n";
echo "  Tamaño comprimido      : {$paquete['bytes']} bytes\n";
echo "  hashArchivo (SHA256)   : {$paquete['hash']}\n";
echo "  codigoEvento           : {$codigoEvento}\n\n";

$tmpGz = tempnam(sys_get_temp_dir(), 'paq') . '.tar.gz';
file_put_contents($tmpGz, base64_decode($paquete['base64']));
$phar = new PharData($tmpGz);
echo "  Contenido del TAR.GZ:\n";
foreach (new RecursiveIteratorIterator($phar) as $f) {
    echo "     " . $f->getFilename() . "  (" . $f->getSize() . " bytes)\n";
}
unset($phar);
unlink($tmpGz);
echo "\n";

if ($SOLO_LOCAL) {
    echo "  --sin-enviar: no se llama al SIN. Paquete armado y verificado.\n";
    echo "=== FIN (sin envio) ===\n";
    exit(0);
}

echo "  Enviando recepcionPaqueteFactura...\n\n";
$recepcion = null;
$paId = null;
// El piloto corta la conexion de forma intermitente. Se reintenta la llamada
// antes de darla por perdida; como el paquete ya quedo guardado con su
// archivo, un reintento posterior es seguro.
for ($i = 1; $i <= 4; $i++) {
    try {
        $recepcion = siatModel::enviarPaqueteContingencia(
            $suId, null, $codigoEvento, array_column($facturas, 'fe_id')
        );
        break;
    } catch (SoapFault $e) {
        echo "  intento {$i}: SoapFault - " . $e->getMessage() . "\n";
        if ($i < 4) { sleep(20); }
    } catch (Throwable $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
        break;
    }
}

if (!$recepcion) {
    $fila = $pdo->query("SELECT pa_id, pa_estado, pa_cantidad_facturas, pa_hash_archivo, pa_mensajes
                         FROM siat_paquetes ORDER BY pa_id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    echo "  El SIN no devolvio codigo de recepcion.\n";
    if ($fila) {
        $paId = $fila['pa_id'];
        echo "  pa_id={$paId} estado={$fila['pa_estado']} facturas={$fila['pa_cantidad_facturas']}\n";
        echo "  hash enviado: {$fila['pa_hash_archivo']}\n";
        if (!empty($fila['pa_mensajes'])) {
            echo "  respuesta   : {$fila['pa_mensajes']}\n";
        }
        echo "\n  Para reenviar el mismo archivo (sin emitir nada nuevo):\n";
        echo "    php debug_etapa6.php --reintentar={$paId}\n";
    }
    echo "=== FIN ===\n";
    exit(1);
}

echo "  RECEPCION OBTENIDA: {$recepcion}\n";
echo "  La recepcion de paquetes es asincrona: el SIN todavia puede observar.\n\n";

// ===========================================================================
// 6. Validacion
// ===========================================================================
echo str_repeat('=', 72) . "\n";
echo "PASO 6  validacionRecepcionPaquete\n";
echo str_repeat('=', 72) . "\n\n";

try {
    $resp = siatModel::validarPaqueteContingencia($suId, null, $recepcion);
} catch (Throwable $e) {
    echo "  ERROR consultando la validacion: " . $e->getMessage() . "\n";
    echo "  El paquete quedo enviado. Reintentar la validacion con:\n";
    echo "    php debug_etapa6_validar.php {$recepcion}\n";
    echo "=== FIN ===\n";
    exit(1);
}

// La respuesta del SIN viene envuelta en RespuestaServicioFacturacion; sin
// desenvolverla, transaccion y codigoEstado salian vacios.
$resp = $resp->RespuestaServicioFacturacion ?? $resp;

echo "  transaccion       : " . var_export($resp->transaccion ?? null, true) . "\n";
echo "  codigoEstado      : " . ($resp->codigoEstado ?? '(no vino)') . "\n";
echo "  codigoDescripcion : " . ($resp->codigoDescripcion ?? '(no vino)') . "\n";

$msgs = $resp->mensajesList ?? null;
if ($msgs) {
    echo "\n  Mensajes del SIN:\n";
    foreach ((array)$msgs as $m) {
        echo "     [" . ($m->codigo ?? '?') . '] ' . trim($m->descripcion ?? '') . "\n";
    }
}

$estadoPaq = $pdo->query("SELECT pa_estado FROM siat_paquetes
                          WHERE pa_codigo_recepcion = " . $pdo->quote($recepcion))
             ->fetchColumn();
$estadoFact = $pdo->query("SELECT fe_estado_local, COUNT(*) n
                           FROM facturacion_electronica
                           WHERE fe_codigo_recepcion = " . $pdo->quote($recepcion) . "
                           GROUP BY fe_estado_local")->fetchAll(PDO::FETCH_ASSOC);

echo "\n  siat_paquetes      : {$estadoPaq}\n";
foreach ($estadoFact as $f) {
    echo "  facturas          : {$f['n']} en {$f['fe_estado_local']}\n";
}

echo "\n=== FIN ETAPA 6 ===\n";
