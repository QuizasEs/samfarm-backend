<?php
require_once "mainModel.php";

class recepcionarModel extends mainModel
{
    protected static function obtener_lote_por_id_model($lm_id)
    {
        $sql = "SELECT * FROM lote_medicamento WHERE lm_id = :lm_id LIMIT 1";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':lm_id' => $lm_id]);
        return $stmt;
    }

    protected static function listar_transferencias_pendientes_model($su_destino, $rol, $estado = 'pendiente')
    {
        $sql = "SELECT 
                    t.tr_id,
                    t.tr_numero,
                    t.su_origen_id,
                    t.su_destino_id,
                    t.us_emisor_id,
                    t.tr_total_items,
                    t.tr_total_cajas,
                    t.tr_total_unidades,
                    t.tr_total_valorado,
                    t.tr_estado,
                    t.tr_observaciones,
                    t.tr_fecha_envio,
                    so.su_nombre AS sucursal_origen,
                    sd.su_nombre AS sucursal_destino,
                    CONCAT(ue.us_nombres, ' ', ue.us_apellido_paterno) AS usuario_emisor
                FROM transferencias t
                INNER JOIN sucursales so ON so.su_id = t.su_origen_id
                INNER JOIN sucursales sd ON sd.su_id = t.su_destino_id
                INNER JOIN usuarios ue ON ue.us_id = t.us_emisor_id
                WHERE t.su_destino_id = :su_destino
                AND t.tr_estado = :estado";

        if ($rol != 1) {
            $sql .= " AND t.su_destino_id = :su_destino";
        }

        $sql .= " ORDER BY t.tr_fecha_envio DESC";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':su_destino' => $su_destino,
            ':estado' => $estado
        ]);
        return $stmt;
    }

    protected static function obtener_transferencia_completa_model($tr_id)
    {
        $sql = "SELECT 
                    t.*,
                    so.su_nombre AS sucursal_origen,
                    sd.su_nombre AS sucursal_destino,
                    CONCAT(ue.us_nombres, ' ', ue.us_apellido_paterno) AS usuario_emisor,
                    CONCAT(ur.us_nombres, ' ', ur.us_apellido_paterno) AS usuario_receptor
                FROM transferencias t
                INNER JOIN sucursales so ON so.su_id = t.su_origen_id
                INNER JOIN sucursales sd ON sd.su_id = t.su_destino_id
                INNER JOIN usuarios ue ON ue.us_id = t.us_emisor_id
                LEFT JOIN usuarios ur ON ur.us_id = t.us_receptor_id
                WHERE t.tr_id = :tr_id
                LIMIT 1";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':tr_id' => $tr_id]);
        return $stmt;
    }

    protected static function obtener_detalle_transferencia_model($tr_id)
    {
        $sql = "SELECT 
                    dt.*,
                    m.med_nombre_quimico,
                    m.med_principio_activo,
                    lm.lm_cant_blister,
                    lm.lm_cant_unidad,
                    lm.lm_fecha_vencimiento
                FROM detalle_transferencia dt
                INNER JOIN medicamento m ON m.med_id = dt.med_id
                INNER JOIN lote_medicamento lm ON lm.lm_id = dt.lm_origen_id
                WHERE dt.tr_id = :tr_id
                ORDER BY dt.dt_id ASC";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':tr_id' => $tr_id]);
        return $stmt;
    }

    protected static function actualizar_estado_transferencia_model($tr_id, $estado, $us_receptor, $motivo_rechazo = null)
    {
        $sql = "UPDATE transferencias 
                SET tr_estado = :estado,
                    us_receptor_id = :us_receptor,
                    tr_fecha_respuesta = NOW(),
                    tr_motivo_rechazo = :motivo_rechazo,
                    tr_actualizado_en = NOW()
                WHERE tr_id = :tr_id";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':estado' => $estado,
            ':us_receptor' => $us_receptor,
            ':motivo_rechazo' => $motivo_rechazo,
            ':tr_id' => $tr_id
        ]);

        return $stmt;
    }

    protected static function crear_lote_destino_model($datos)
    {
        $sql = "INSERT INTO lote_medicamento 
                (med_id, su_id, pr_id, pr_id_compra, lm_numero_lote, lm_cant_blister, lm_cant_unidad, 
                 lm_cant_actual_cajas, lm_cant_actual_unidades, lm_precio_compra, 
                 lm_precio_venta, lm_fecha_vencimiento, lm_estado, lm_origen_id)
                VALUES (:med_id, :su_id, :pr_id, :pr_id_compra, :lm_numero_lote, :lm_cant_blister, :lm_cant_unidad,
                        :lm_cant_actual_cajas, :lm_cant_actual_unidades, :lm_precio_compra,
                        :lm_precio_venta, :lm_fecha_vencimiento, :lm_estado, :lm_origen_id)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return mainModel::conectar()->lastInsertId();
    }

    protected static function actualizar_detalle_transferencia_lote_destino_model($dt_id, $lm_destino_id)
    {
        $sql = "UPDATE detalle_transferencia 
                SET lm_destino_id = :lm_destino_id,
                    dt_estado = 1
                WHERE dt_id = :dt_id";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':lm_destino_id' => $lm_destino_id,
            ':dt_id' => $dt_id
        ]);

        return $stmt;
    }

    protected static function incrementar_inventario_model($med_id, $su_id, $cajas, $unidades, $valorado)
    {
        $sql = "SELECT * FROM inventarios WHERE med_id = :med_id AND su_id = :su_id";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':med_id' => $med_id,
            ':su_id' => $su_id
        ]);

        if ($stmt->rowCount() > 0) {
            $sql_update = "UPDATE inventarios
                          SET inv_total_cajas = inv_total_cajas + :cajas,
                              inv_total_unidades = inv_total_unidades + :unidades,
                              inv_total_valorado = inv_total_valorado + :valorado,
                              inv_actualizado_en = NOW()
                          WHERE med_id = :med_id AND su_id = :su_id";

            $stmt_update = mainModel::conectar()->prepare($sql_update);
            $stmt_update->execute([
                ':cajas' => $cajas,
                ':unidades' => $unidades,
                ':valorado' => $valorado,
                ':med_id' => $med_id,
                ':su_id' => $su_id
            ]);
        } else {
            $sql_insert = "INSERT INTO inventarios (med_id, su_id, inv_total_cajas, inv_total_unidades, inv_total_valorado)
                          VALUES (:med_id, :su_id, :cajas, :unidades, :valorado)";

            $stmt_insert = mainModel::conectar()->prepare($sql_insert);
            $stmt_insert->execute([
                ':med_id' => $med_id,
                ':su_id' => $su_id,
                ':cajas' => $cajas,
                ':unidades' => $unidades,
                ':valorado' => $valorado
            ]);
        }

        return $stmt;
    }

    protected static function registrar_movimiento_entrada_model($datos)
    {
        $sql = "INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
                VALUES (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function registrar_historial_lote_recepcion_model($datos)
    {
        $sql = "INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
                VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function descontar_stock_lote_origen_model($lm_id, $cajas, $unidades)
    {
        $sql = "UPDATE lote_medicamento 
                SET lm_cant_actual_cajas = lm_cant_actual_cajas + :cajas,
                    lm_cant_actual_unidades = lm_cant_actual_unidades + :unidades,
                    lm_actualizado_en = NOW()
                WHERE lm_id = :lm_id";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':cajas' => $cajas,
            ':unidades' => $unidades,
            ':lm_id' => $lm_id
        ]);

        return $stmt;
    }

    protected static function incrementar_inventario_origen_model($med_id, $su_id, $cajas, $unidades, $valorado)
    {
        $sql = "UPDATE inventarios
                SET inv_total_cajas = inv_total_cajas + :cajas,
                    inv_total_unidades = inv_total_unidades + :unidades,
                    inv_total_valorado = inv_total_valorado + :valorado,
                    inv_actualizado_en = NOW()
                WHERE med_id = :med_id AND su_id = :su_id";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':cajas' => $cajas,
            ':unidades' => $unidades,
            ':valorado' => $valorado,
            ':med_id' => $med_id,
            ':su_id' => $su_id
        ]);

        return $stmt;
    }

    protected static function registrar_movimiento_reversa_model($datos)
    {
        $sql = "INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
                VALUES (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function registrar_informe_recepcion_model($datos)
    {
        $sql = "INSERT INTO informes (inf_nombre, inf_tipo, inf_usuario, inf_config)
                VALUES (:inf_nombre, :inf_tipo, :inf_usuario, :inf_config)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }
}
