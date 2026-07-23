<?php

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/models/siatModel.php';
require_once dirname(__DIR__) . '/models/ventaModel.php';

try {
    $db = mainModel::conectar();

    // 1) Enviar paquete de facturas en contingencia pendiente (Paso 12)
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
                $ticket = siatModel::enviarPaqueteContingencia((int)$suId);
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
            echo date('Y-m-d H:i:s') . " - SIAT contingencia: {$contingenciaOk} paquetes enviados, {$contingenciaFail} fallidos\n";
        }
    } catch (Exception $e) {
        error_log("SIAT contingencia ERROR: " . $e->getMessage());
    }

    // 2) Validar facturas pendientes de recepción (Paso 10)
    $stmt = $db->query("
        SELECT fe.fa_id, fe.fe_ticket, f.fa_cuf, f.fa_numero, v.su_id
        FROM facturacion_electronica fe
        JOIN factura f ON f.fa_id = fe.fa_id
        JOIN ventas v ON v.ve_id = f.ve_id
        WHERE fe.fe_qr IS NULL
          AND fe.fe_ticket IS NOT NULL
          AND fe.fe_estado_siat NOT IN ('ANULADA', 'CONTINGENCIA')
        LIMIT 50
    ");
    $pendientes = $stmt->fetchAll(PDO::FETCH_OBJ);

    if (!$pendientes) {
        echo date('Y-m-d H:i:s') . " - SIAT: sin facturas pendientes de validar\n";
        exit(0);
    }

    $ok = 0;
    $fail = 0;

    foreach ($pendientes as $row) {
        try {
            $nroFactura = ventaModel::obtener_numero_factura_numerico_model($row->su_id);
            $estado = siatModel::procesarValidacionPendiente(
                $row->fa_id,
                $row->fe_ticket,
                $row->su_id,
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

    echo date('Y-m-d H:i:s') . " - SIAT validacion: {$ok} validadas, {$fail} con error\n";
    exit(0);

} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - SIAT ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
