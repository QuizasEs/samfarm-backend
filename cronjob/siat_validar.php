<?php

/* Drenado de la cola de facturacion electronica.

   Orden a proposito. Primero lo que ya esta firmado y solo falta transmitir,
   porque un CUFD caduca a las 24 h: lo que no sale hoy puede no salir nunca.
   Despues la contingencia, despues las validaciones, y al final un informe de
   salud de cada sucursal. */

/* mainModel.php resuelve la configuracion con rutas relativas ("../config/")
   y decide con $peticionAjax. Este cron se lanzaba sin definirla, asi que caia
   siempre en la rama "./config/", que solo funciona si el directorio de
   trabajo es la raiz del proyecto. Y Windows lanza las tareas programadas
   desde C:\Windows\System32: con cualquiera de los dos directorios, el
   include reventaba con "Failed opening required" y el cron no hacia nada.

   Se fijan las dos cosas aqui, dentro del propio cron, para que mainModel.php
   no haya que tocarlo: se declara la variable y se trabaja en la carpeta del
   script, de modo que "../config/" resuelve bien desde donde se lance. */
$peticionAjax = true;
chdir(__DIR__);

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/config/siat.php';
require_once dirname(__DIR__) . '/models/siatModel.php';
require_once dirname(__DIR__) . '/models/ventaModel.php';

$marca = date('Y-m-d H:i:s');

try {
    /* conectarTransaccional() y no mainModel::conectar(): esta ultima es
       protected, y llamarla desde el ambito global de un script lanzaba
       "Call to protected method" y mataba el cron en la linea siguiente.
       La transaccional es publica, no persistente (bien para un cron: no
       deja una conexion de MySQL abierta entre pasadas) y con 60 s de
       timeout. */
    $db = mainModel::conectarTransaccional();

    /* ---------------------------------------------------------------- */
    /* 1) Cola de envio de facturas online                             */
    /* ---------------------------------------------------------------- */
    /* Cada factura se reenvia tal cual salio de fe_payload. Nunca se regenera:
       el XML lleva dentro el CUF y el CUFD con los que se firmo, y cambiarlo
       rompe la trazabilidad de la factura. procesarColaEnvio() ademas agrupa
       por sucursal, de modo que dos cajas nunca llaman al SIN a la vez, y
       aplica backoff creciente a los reintentos. */
    try {
        $cola = siatModel::procesarColaEnvio();

        if ($cola['candidatas'] > 0) {
            echo $marca . sprintf(
                " - SIAT cola: %d candidatas, %d enviadas, %d para reintento, "
                . "%d a revision manual, %d diferidas\n",
                $cola['candidatas'],
                $cola['enviadas'],
                $cola['reintentadas'],
                $cola['permanentes'],
                $cola['descartadas']
            );

            foreach ($cola['detalle'] as $d) {
                $extra = $d['error'] ?? ($d['respuesta'] ?? '');
                if ($d['agotada'] ?? false) {
                    $extra .= ' (intentos agotados)';
                } elseif ($d['permanente'] ?? false) {
                    $extra .= ' (permanente: no se reintenta)';
                } elseif (isset($d['reintento_min'])) {
                    $extra .= ' (reintenta en ' . $d['reintento_min'] . ' min)';
                }
                if ($extra !== '') {
                    error_log("SIAT cola fe_id={$d['fe_id']} fa_id={$d['fa_id']}: {$extra}");
                }
            }
        } else {
            echo $marca . " - SIAT cola: sin facturas pendientes de envio\n";
        }
    } catch (Exception $e) {
        error_log("SIAT cola ERROR: " . $e->getMessage());
    }

    /* ---------------------------------------------------------------- */
    /* 2) Paquetes de facturas en contingencia                          */
    /* ---------------------------------------------------------------- */
    try {
        $stmtCont = $db->query("
            SELECT DISTINCT v.su_id
            FROM facturacion_electronica fe
            JOIN factura f ON f.fa_id = fe.fa_id
            JOIN ventas v ON v.ve_id = f.ve_id
            WHERE fe.fe_tipo_emision = 2
              AND fe.fe_estado_siat = 'CONTINGENCIA'
        ");
        $sucursalesCont = $stmtCont->fetchAll(PDO::FETCH_COLUMN);

        $contingenciaOk = 0;
        $contingenciaFail = 0;

        foreach ($sucursalesCont as $suId) {
            try {
                /* El codigo del evento significativo es obligatorio: sin el el
                   SIN no puede asociar el paquete a ninguna contingencia y lo
                   rechaza. Por eso ya no se puede llamar a
                   enviarPaqueteContingencia solo con el su_id.

                   Ese codigo no tiene columna propia: se guardo dentro de
                   ev_respuesta_siat como {"recepcion":"9938749",...}. */
                $jsonEv = $db->query("SELECT ev_respuesta_siat FROM siat_eventos
                                      WHERE ev_estado = 'validado'
                                      ORDER BY ev_id DESC LIMIT 1")->fetchColumn();
                $evRecepcion = json_decode($jsonEv ?: '{}', true)['recepcion'] ?? null;

                if (!$evRecepcion) {
                    error_log("SIAT contingencia su_id={$suId}: no hay evento significativo validado, se omite");
                    $contingenciaFail++;
                    continue;
                }

                $ticket = siatModel::enviarPaqueteContingencia((int)$suId, null, $evRecepcion);
                if ($ticket) {
                    $contingenciaOk++;
                } else {
                    $contingenciaFail++;
                }
            } catch (Exception $e) {
                error_log("SIAT contingencia su_id={$suId}: " . $e->getMessage());
                $contingenciaFail++;
            }
        }

        if ($sucursalesCont) {
            echo $marca . " - SIAT contingencia: {$contingenciaOk} paquetes enviados, {$contingenciaFail} fallidos\n";
        }
    } catch (Exception $e) {
        error_log("SIAT contingencia ERROR: " . $e->getMessage());
    }

    /* ---------------------------------------------------------------- */
    /* 3) Validar facturas pendientes de recepcion                      */
    /* ---------------------------------------------------------------- */
    /* fa_numero_siat es el numero fiscal real: el mismo que va dentro del CUF.
       NO se vuelve a pedir correlativo con
       ventaModel::obtener_numero_factura_numerico_model(): ese metodo INCREMENTA
       factura_secuencia, y llamarlo desde aqui quemaba un numero de factura
       cada vez que se validaba otra. El QR saldia con un numero que no era el de
       la factura, y la secuencia quedaba con huecos. */
    $stmt = $db->query("
        SELECT fe.fa_id, fe.fe_ticket, f.fa_cuf, f.fa_numero_siat, f.su_id
        FROM facturacion_electronica fe
        JOIN factura f ON f.fa_id = fe.fa_id
        WHERE fe.fe_qr IS NULL
          AND fe.fe_ticket IS NOT NULL
          AND fe.fa_id IS NOT NULL
          AND fe.fe_estado_siat NOT IN ('ANULADA', 'CONTINGENCIA')
        LIMIT 50
    ");
    $pendientes = $stmt->fetchAll(PDO::FETCH_OBJ);

    $ok = 0;
    $fail = 0;

    foreach ($pendientes as $row) {
        try {
            $nroFactura = (int)$row->fa_numero_siat;

            if ($nroFactura <= 0) {
                error_log(
                    "SIAT validar fa_id={$row->fa_id}: la factura no tiene fa_numero_siat, "
                    . "no se puede validar ni armar el QR"
                );
                $fail++;
                continue;
            }

            $estado = siatModel::procesarValidacionPendiente(
                $row->fa_id,
                $row->fe_ticket,
                (int)$row->su_id,
                $nroFactura
            );

            if ($estado == 908) {
                $ok++;
            } else {
                $fail++;
            }
        } catch (Exception $e) {
            error_log("SIAT validar fa_id={$row->fa_id}: " . $e->getMessage());
            $fail++;
        }
    }

    if ($pendientes) {
        echo $marca . " - SIAT validacion: {$ok} validadas, {$fail} con error\n";
    } else {
        echo $marca . " - SIAT validacion: sin facturas pendientes\n";
    }

    /* ---------------------------------------------------------------- */
    /* 4) Reintentar facturas rechazadas                               */
    /* ---------------------------------------------------------------- */
    $stmtRech = $db->query("
        SELECT fe.fa_id
        FROM facturacion_electronica fe
        WHERE fe.fe_estado_siat = 'RECHAZADA'
        LIMIT 20
    ");
    $rechazadas = $stmtRech->fetchAll(PDO::FETCH_OBJ);

    foreach ($rechazadas as $row) {
        try {
            $db->prepare("
                UPDATE facturacion_electronica
                SET fe_estado_siat = 'CONTINGENCIA',
                    fe_ticket = NULL,
                    fe_fecha_envio = NULL
                WHERE fa_id = :fa_id
            ")->execute([':fa_id' => $row->fa_id]);
        } catch (Exception $e) {
            error_log("SIAT reintento rechazada fa_id={$row->fa_id}: " . $e->getMessage());
        }
    }

    /* ---------------------------------------------------------------- */
    /* 5) Salud de cada sucursal                                        */
    /* ---------------------------------------------------------------- */
    /* Solo informa. No pide CUIS ni CUFD por su cuenta: eso requiere decidir si
       la sucursal esta dada de alta en el SIN, y equivocarse aqui significa
       credenciales pedidas con el codigo equivocado. Lo que hace es dejar
       escrito que sucursal esta lista y cual no, para que se vea en el log
       todos los dias y no se descubra cuando una venta falla. */
    try {
        foreach ($db->query("SELECT su_id FROM sucursales WHERE su_estado = 1 ORDER BY su_id") as $s) {
            $estado = siatModel::estadoCajaParaFacturacion((int)$s['su_id'], null);
            if (!$estado['listo']) {
                /* Aca va el 'diagnostico' y no el 'motivo': este log lo lee quien
                   administra la caja, y sin el CUIS y el CUFD no hay forma de
                   saber que credencial hay que pedir. El 'motivo' es el texto
                   corto, sin codigos, que ve el cajero en un cartel. */
                echo $marca . " - SIAT sucursal {$s['su_id']} NO puede facturar: {$estado['diagnostico']}\n";
            }
        }
    } catch (Exception $e) {
        error_log("SIAT salud de sucursales ERROR: " . $e->getMessage());
    }

    exit(0);

} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - SIAT ERROR: " . $e->getMessage() . "\n";
    exit(1);
}