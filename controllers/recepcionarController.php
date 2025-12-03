<?php

if ($peticionAjax) {
    require_once '../models/recepcionarModel.php';
} else {
    require_once './models/recepcionarModel.php';
}

class recepcionarController extends recepcionarModel
{
    public function listar_transferencias_pendientes_controller()
    {
        $su_destino = $_SESSION['sucursal_smp'];
        $rol = $_SESSION['rol_smp'];

        try {
            $stmt = recepcionarModel::listar_transferencias_pendientes_model($su_destino, $rol, 'pendiente');
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($datos, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en listar_transferencias_pendientes: " . $e->getMessage());
            return json_encode(['error' => 'Error al listar transferencias']);
        }
    }

    public function obtener_detalles_transferencia_controller()
    {
        $tr_id = (int)$_POST['tr_id'];

        if (!$tr_id) {
            return json_encode(['error' => 'ID de transferencia inválido']);
        }

        try {
            $transferencia = recepcionarModel::obtener_transferencia_completa_model($tr_id)->fetch();

            if (!$transferencia) {
                return json_encode(['error' => 'Transferencia no encontrada']);
            }

            $su_destino = $_SESSION['sucursal_smp'];
            $rol = $_SESSION['rol_smp'];

            if ($rol != 1 && $transferencia['su_destino_id'] != $su_destino) {
                return json_encode(['error' => 'No tiene permiso para ver esta transferencia']);
            }

            $detalles = recepcionarModel::obtener_detalle_transferencia_model($tr_id)->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'transferencia' => $transferencia,
                'detalles' => $detalles
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_detalles: " . $e->getMessage());
            return json_encode(['error' => 'Error al obtener detalles']);
        }
    }

    public function aceptar_transferencia_controller()
    {
        $tr_id = (int)$_POST['tr_id'];
        $us_receptor = $_SESSION['id_smp'];
        $su_receptor = $_SESSION['sucursal_smp'];

        if (!$tr_id) {
            return json_encode(['error' => 'ID de transferencia inválido']);
        }

        try {
            $conexion = mainModel::conectar();
            $conexion->beginTransaction();

            $transferencia = recepcionarModel::obtener_transferencia_completa_model($tr_id)->fetch();

            if (!$transferencia) {
                throw new Exception("Transferencia no encontrada");
            }

            if ($transferencia['tr_estado'] !== 'pendiente') {
                throw new Exception("La transferencia no está en estado pendiente");
            }

            if ($transferencia['su_destino_id'] != $su_receptor && $_SESSION['rol_smp'] != 1) {
                throw new Exception("No tiene permiso para aceptar esta transferencia");
            }

            $detalles = recepcionarModel::obtener_detalle_transferencia_model($tr_id)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($detalles as $detalle) {
                $lm_origen_id = $detalle['lm_origen_id'];
                $med_id = $detalle['med_id'];
                $cajas = $detalle['dt_cantidad_cajas'];
                $unidades = $detalle['dt_cantidad_unidades'];
                $precio_compra = $detalle['dt_precio_compra'];
                $precio_venta = $detalle['dt_precio_venta'];
                $subtotal = $detalle['dt_subtotal_valorado'];
                $fecha_venc = $detalle['lm_fecha_vencimiento'];
                $numero_lote_original = $detalle['dt_numero_lote_origen'];

                $lote_origen = recepcionarModel::obtener_lote_por_id_model($lm_origen_id)->fetch();
                $pr_id = $lote_origen['pr_id'] ?? null;
                $pr_id_compra = $lote_origen['pr_id_compra'] ?? null;

                $datos_lote_nuevo = [
                    ':med_id' => $med_id,
                    ':su_id' => $su_receptor,
                    ':pr_id' => $pr_id,
                    ':pr_id_compra' => $pr_id_compra,
                    ':lm_numero_lote' => $numero_lote_original,
                    ':lm_cant_caja' => $cajas,
                    ':lm_cant_blister' => $detalle['lm_cant_blister'],
                    ':lm_cant_unidad' => $detalle['lm_cant_unidad'],
                    ':lm_cant_actual_cajas' => $cajas,
                    ':lm_cant_actual_unidades' => $unidades,
                    ':lm_precio_compra' => $precio_compra,
                    ':lm_precio_venta' => $precio_venta,
                    ':lm_fecha_vencimiento' => $fecha_venc,
                    ':lm_estado' => 'activo',
                    ':lm_origen_id' => $lm_origen_id
                ];

                $lm_destino_id = recepcionarModel::crear_lote_destino_model($datos_lote_nuevo);

                if (!$lm_destino_id) {
                    throw new Exception("No se pudo crear lote destino para medicamento " . $med_id);
                }

                recepcionarModel::actualizar_detalle_transferencia_lote_destino_model($detalle['dt_id'], $lm_destino_id);

                recepcionarModel::incrementar_inventario_model($med_id, $su_receptor, $cajas, $unidades, $subtotal);

                $datos_movimiento = [
                    ':lm_id' => $lm_destino_id,
                    ':med_id' => $med_id,
                    ':su_id' => $su_receptor,
                    ':us_id' => $us_receptor,
                    ':mi_tipo' => 'entrada',
                    ':mi_cantidad' => $unidades,
                    ':mi_unidad' => 'unidad',
                    ':mi_referencia_tipo' => 'transferencia_entrada',
                    ':mi_referencia_id' => $tr_id,
                    ':mi_motivo' => "Recepción de transferencia #{$transferencia['tr_numero']} desde {$transferencia['sucursal_origen']}"
                ];

                recepcionarModel::registrar_movimiento_entrada_model($datos_movimiento);

                $datos_historial = [
                    ':lm_id' => $lm_destino_id,
                    ':us_id' => $us_receptor,
                    ':hl_accion' => 'recepcion_transferencia',
                    ':hl_descripcion' => "Recepción de {$cajas} cajas por transferencia #{$transferencia['tr_numero']}"
                ];

                recepcionarModel::registrar_historial_lote_recepcion_model($datos_historial);
            }

            recepcionarModel::actualizar_estado_transferencia_model($tr_id, 'aceptada', $us_receptor);

            $config_informe_recepcion = [
                'tipo_informe' => 'transferencia_entrada',
                'tr_id' => $tr_id,
                'tr_numero' => $transferencia['tr_numero'],
                'su_destino' => $su_receptor,
                'us_receptor' => $us_receptor,
                'total_items' => count($detalles),
                'total_cajas' => $transferencia['tr_total_cajas'],
                'total_unidades' => $transferencia['tr_total_unidades'],
                'total_valorado' => $transferencia['tr_total_valorado'],
                'tr_estado' => 'aceptada'
            ];

            recepcionarModel::registrar_informe_recepcion_model([
                ':inf_nombre' => "Recepción de Transferencia {$transferencia['tr_numero']}",
                ':inf_tipo' => 'transferencia_recepcion',
                ':inf_usuario' => $us_receptor,
                ':inf_config' => json_encode($config_informe_recepcion, JSON_UNESCAPED_UNICODE)
            ]);

            $conexion->commit();

            return json_encode([
                'Tipo' => 'success',
                'Titulo' => 'Transferencia aceptada',
                'texto' => "Número: {$transferencia['tr_numero']}<br>Total: Bs. " . number_format($transferencia['tr_total_valorado'], 2)
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error en aceptar_transferencia: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function rechazar_transferencia_controller()
    {
        $tr_id = (int)$_POST['tr_id'];
        $motivo = mainModel::limpiar_cadena($_POST['motivo'] ?? '');
        $us_receptor = $_SESSION['id_smp'];
        $su_receptor = $_SESSION['sucursal_smp'];

        if (!$tr_id) {
            return json_encode(['error' => 'ID de transferencia inválido']);
        }

        if (!$motivo) {
            return json_encode(['error' => 'Debe especificar un motivo de rechazo']);
        }

        try {
            $conexion = mainModel::conectar();
            $conexion->beginTransaction();

            $transferencia = recepcionarModel::obtener_transferencia_completa_model($tr_id)->fetch();

            if (!$transferencia) {
                throw new Exception("Transferencia no encontrada");
            }

            if ($transferencia['tr_estado'] !== 'pendiente') {
                throw new Exception("La transferencia no está en estado pendiente");
            }

            if ($transferencia['su_destino_id'] != $su_receptor && $_SESSION['rol_smp'] != 1) {
                throw new Exception("No tiene permiso para rechazar esta transferencia");
            }

            $detalles = recepcionarModel::obtener_detalle_transferencia_model($tr_id)->fetchAll(PDO::FETCH_ASSOC);

            foreach ($detalles as $detalle) {
                $lm_origen_id = $detalle['lm_origen_id'];
                $med_id = $detalle['med_id'];
                $cajas = $detalle['dt_cantidad_cajas'];
                $unidades = $detalle['dt_cantidad_unidades'];
                $subtotal = $detalle['dt_subtotal_valorado'];
                $su_origen = $transferencia['su_origen_id'];

                recepcionarModel::descontar_stock_lote_origen_model($lm_origen_id, $cajas, $unidades);

                recepcionarModel::incrementar_inventario_origen_model($med_id, $su_origen, $cajas, $unidades, $subtotal);

                $datos_movimiento_reversa = [
                    ':lm_id' => $lm_origen_id,
                    ':med_id' => $med_id,
                    ':su_id' => $su_origen,
                    ':us_id' => $us_receptor,
                    ':mi_tipo' => 'entrada',
                    ':mi_cantidad' => $unidades,
                    ':mi_unidad' => 'unidad',
                    ':mi_referencia_tipo' => 'transferencia_reversa',
                    ':mi_referencia_id' => $tr_id,
                    ':mi_motivo' => "Reversa de transferencia #{$transferencia['tr_numero']} - Motivo: {$motivo}"
                ];

                recepcionarModel::registrar_movimiento_reversa_model($datos_movimiento_reversa);

                $datos_historial = [
                    ':lm_id' => $lm_origen_id,
                    ':us_id' => $us_receptor,
                    ':hl_accion' => 'rechazo_transferencia',
                    ':hl_descripcion' => "Rechazo de {$cajas} cajas de transferencia #{$transferencia['tr_numero']} - Motivo: {$motivo}"
                ];

                recepcionarModel::registrar_historial_lote_recepcion_model($datos_historial);
            }

            recepcionarModel::actualizar_estado_transferencia_model($tr_id, 'rechazada', $us_receptor, $motivo);

            $config_informe_rechazo = [
                'tipo_informe' => 'transferencia_rechazo',
                'tr_id' => $tr_id,
                'tr_numero' => $transferencia['tr_numero'],
                'su_destino' => $su_receptor,
                'us_receptor' => $us_receptor,
                'motivo_rechazo' => $motivo,
                'tr_estado' => 'rechazada'
            ];

            recepcionarModel::registrar_informe_recepcion_model([
                ':inf_nombre' => "Rechazo de Transferencia {$transferencia['tr_numero']}",
                ':inf_tipo' => 'transferencia_rechazo',
                ':inf_usuario' => $us_receptor,
                ':inf_config' => json_encode($config_informe_rechazo, JSON_UNESCAPED_UNICODE)
            ]);

            $conexion->commit();

            return json_encode([
                'Tipo' => 'success',
                'Titulo' => 'Transferencia rechazada',
                'texto' => "Número: {$transferencia['tr_numero']}<br>La transferencia ha sido rechazada"
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error en rechazar_transferencia: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
