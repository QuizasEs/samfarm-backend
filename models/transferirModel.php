<?php
require_once "mainModel.php";

class transferirModel extends mainModel
{

    protected static function buscar_lotes_disponibles_model($su_id, $busqueda, $laboratorio, $fecha_venc_max)
    {
        $sql = "
                    SELECT 
                        lm.lm_id,
                        lm.lm_numero_lote,
                        lm.lm_cant_actual_cajas,
                        lm.lm_cant_actual_unidades,
                        lm.lm_cant_blister,
                        lm.lm_cant_unidad,
                        lm.lm_precio_compra,
                        lm.lm_precio_venta,
                        lm.lm_fecha_vencimiento,
                        DATEDIFF(lm.lm_fecha_vencimiento, CURDATE()) AS dias_vencer,
                        m.med_id,
                        m.med_nombre_quimico,
                        m.med_principio_activo,
                        m.med_presentacion,
                        la.la_nombre_comercial AS laboratorio
                    FROM lote_medicamento lm
                    INNER JOIN medicamento m ON m.med_id = lm.med_id
                    LEFT JOIN laboratorios la ON la.la_id = m.la_id
                    WHERE lm.su_id = :su_id
                    AND lm.lm_estado = 'activo'
                    AND lm.lm_cant_actual_unidades > 0
                ";

        $params = [':su_id' => $su_id];

        if ($busqueda) {
            $sql .= " AND (m.med_nombre_quimico LIKE :busqueda OR lm.lm_numero_lote LIKE :busqueda)";
            $params[':busqueda'] = "%{$busqueda}%";
        }

        if ($laboratorio) {
            $sql .= " AND m.la_id = :laboratorio";
            $params[':laboratorio'] = $laboratorio;
        }

        if ($fecha_venc_max) {
            $sql .= " AND lm.lm_fecha_vencimiento <= :fecha_max";
            $params[':fecha_max'] = $fecha_venc_max;
        }

        $sql .= " ORDER BY lm.lm_fecha_vencimiento ASC";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected static function crear_transferencia_model($datos)
    {
        $items = json_decode($_POST['items_json'] ?? '[]', true);

        if (empty($items)) {
            throw new Exception("No hay items para procesar");
        }

        $destinos_unicos = array_unique(array_column($items, 'su_destino'));

        if (count($destinos_unicos) > 1) {
            throw new Exception("No se pueden transferir items a múltiples sucursales en una sola transferencia");
        }

        $su_destino_id = $destinos_unicos[0];

        $sql = "INSERT INTO transferencias 
                (tr_numero, su_origen_id, su_destino_id, us_emisor_id, tr_total_items, tr_observaciones)
                VALUES (:tr_numero, :su_origen_id, :su_destino_id, :us_emisor_id, :tr_total_items, :tr_observaciones)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':tr_numero' => $datos['tr_numero'],
            ':su_origen_id' => $datos['su_origen_id'],
            ':su_destino_id' => $su_destino_id,
            ':us_emisor_id' => $datos['us_emisor_id'],
            ':tr_total_items' => $datos['tr_total_items'],
            ':tr_observaciones' => $datos['tr_observaciones']
        ]);

        return mainModel::conectar()->lastInsertId();
    }

    protected static function insertar_detalle_transferencia_model($datos)
    {
        $sql = "INSERT INTO detalle_transferencia 
                        (tr_id, lm_origen_id, med_id, dt_numero_lote_origen, dt_cantidad_cajas, dt_cantidad_unidades, 
                        dt_precio_compra, dt_precio_venta, dt_subtotal_valorado)
                        VALUES (:tr_id, :lm_origen_id, :med_id, :dt_numero_lote_origen, :dt_cantidad_cajas, :dt_cantidad_unidades,
                        :dt_precio_compra, :dt_precio_venta, :dt_subtotal_valorado)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function datos_lote_transfer_model($lm_id)
    {
        $sql = "SELECT * FROM lote_medicamento WHERE lm_id = :lm_id LIMIT 1";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':lm_id' => $lm_id]);
        return $stmt;
    }

    protected static function descontar_stock_lote_model($lm_id, $cajas, $unidades)
    {
        $sql = "UPDATE lote_medicamento 
                        SET lm_cant_actual_cajas = lm_cant_actual_cajas - :cajas,
                            lm_cant_actual_unidades = lm_cant_actual_unidades - :unidades,
                            lm_actualizado_en = NOW()
                        WHERE lm_id = :lm_id 
                        AND lm_cant_actual_cajas >= :cajas
                        AND lm_cant_actual_unidades >= :unidades";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':cajas' => $cajas,
            ':unidades' => $unidades,
            ':lm_id' => $lm_id
        ]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("No se pudo descontar stock del lote");
        }

        return $stmt;
    }

    protected static function descontar_inventario_model($med_id, $su_id, $cajas, $unidades, $valorado)
    {
        $sql = "UPDATE inventarios
                        SET inv_total_cajas = inv_total_cajas - :cajas,
                            inv_total_unidades = inv_total_unidades - :unidades,
                            inv_total_valorado = inv_total_valorado - :valorado,
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

    protected static function registrar_movimiento_inventario_model($datos)
    {
        $sql = "INSERT INTO movimiento_inventario
                        (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
                        VALUES (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function registrar_historial_lote_model($datos)
    {
        $sql = "INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
                        VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function actualizar_totales_transferencia_model($tr_id, $cajas, $unidades, $valorado)
    {
        $sql = "UPDATE transferencias
                        SET tr_total_cajas = :cajas,
                            tr_total_unidades = :unidades,
                            tr_total_valorado = :valorado
                        WHERE tr_id = :tr_id";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':cajas' => $cajas,
            ':unidades' => $unidades,
            ':valorado' => $valorado,
            ':tr_id' => $tr_id
        ]);

        return $stmt;
    }

    protected static function registrar_informe_model($datos)
    {
        $sql = "INSERT INTO informes (inf_nombre, inf_tipo, inf_usuario, inf_config)
                        VALUES (:inf_nombre, :inf_tipo, :inf_usuario, :inf_config)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function obtener_ultimo_numero_transferencia_model()
    {
        $sql = "SELECT tr_numero FROM transferencias ORDER BY tr_id DESC LIMIT 1";
        $stmt = mainModel::conectar()->query($sql);
        $resultado = $stmt->fetch();
        return $resultado ? $resultado['tr_numero'] : null;
    }

    protected static function datos_transferencia_completa_model($tr_id)
    {
        $sql = "SELECT 
                            t.*,
                            so.su_nombre AS sucursal_origen,
                            sd.su_nombre AS sucursal_destino,
                            CONCAT(ue.us_nombres, ' ', ue.us_apellido_paterno) AS usuario_emisor
                        FROM transferencias t
                        INNER JOIN sucursales so ON so.su_id = t.su_origen_id
                        INNER JOIN sucursales sd ON sd.su_id = t.su_destino_id
                        INNER JOIN usuarios ue ON ue.us_id = t.us_emisor_id
                        WHERE t.tr_id = :tr_id
                        LIMIT 1";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':tr_id' => $tr_id]);
        return $stmt;
    }

    protected static function detalle_transferencia_model($tr_id)
    {
        $sql = "SELECT 
                            dt.*,
                            m.med_nombre_quimico
                        FROM detalle_transferencia dt
                        INNER JOIN medicamento m ON m.med_id = dt.med_id
                        WHERE dt.tr_id = :tr_id
                        ORDER BY dt.dt_id ASC";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':tr_id' => $tr_id]);
        return $stmt;
    }
}
