<?php

require_once "mainModel.php";

class devolucionModel extends mainModel
{
    protected static function buscar_venta_model($criterio, $valor)
    {
        $db = mainModel::conectar();

        $campo_busqueda = match ($criterio) {
            'fa_id' => 'f.fa_id',
            've_id' => 'v.ve_id',
            'numero_documento' => 'v.ve_numero_documento',
            'numero_factura' => 'f.fa_numero',
            default => null
        };

        if (!$campo_busqueda) {
            return false;
        }

        $sql = "
                SELECT 
                    v.ve_id,
                    v.ve_numero_documento,
                    v.ve_fecha_emision,
                    v.ve_subtotal,
                    v.ve_total,
                    v.ve_estado,
                    v.ve_tipo_documento,
                    v.ve_estado_documento,
                    v.su_id,
                    f.fa_id,
                    f.fa_numero,
                    f.fa_monto_total,
                    c.cl_id,
                    c.cl_nombres,
                    c.cl_apellido_paterno,
                    c.cl_apellido_materno,
                    c.cl_carnet,
                    u.us_nombres,
                    u.us_apellido_paterno,
                    s.su_nombre
                FROM ventas v
                INNER JOIN factura f ON f.ve_id = v.ve_id
                LEFT JOIN clientes c ON c.cl_id = v.cl_id
                INNER JOIN usuarios u ON u.us_id = v.us_id
                INNER JOIN sucursales s ON s.su_id = v.su_id
                WHERE $campo_busqueda = :valor
                AND v.ve_estado = 1
                LIMIT 1
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':valor', $valor);
        $stmt->execute();

        return $stmt;
    }

    protected static function obtener_detalle_venta_model($ve_id)
    {
        $db = mainModel::conectar();

        $sql = "
                SELECT 
                    dv.dv_id,
                    dv.med_id,
                    dv.lm_id,
                    dv.dv_cantidad,
                    dv.dv_unidad,
                    dv.dv_precio_unitario,
                    dv.dv_descuento,
                    dv.dv_subtotal,
                    dv.dv_estado,
                    m.med_nombre_quimico,
                    m.med_principio_activo,
                    m.med_presentacion,
                    COALESCE(ff.ff_nombre, '') AS forma_farmaceutica,
                    COALESCE(la.la_nombre_comercial, '') AS laboratorio,
                    lm.lm_numero_lote,
                    lm.lm_cant_actual_unidades
                FROM detalle_venta dv
                INNER JOIN medicamento m ON m.med_id = dv.med_id
                LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
                LEFT JOIN laboratorios la ON la.la_id = m.la_id
                LEFT JOIN lote_medicamento lm ON lm.lm_id = dv.lm_id
                WHERE dv.ve_id = :ve_id
                ORDER BY dv.dv_id ASC
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':ve_id', $ve_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    protected static function insertar_devolucion_model($datos)
    {
        $db = mainModel::conectar();

        $sql = "
                INSERT INTO devoluciones 
                (ve_id, fa_id, su_id, us_id, dev_total, dev_cantidad, dev_motivo, dev_estado, dev_fecha)
                VALUES 
                (:ve_id, :fa_id, :su_id, :us_id, :dev_total, :dev_cantidad, :dev_motivo, 'aceptada', NOW())
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':ve_id', $datos['ve_id'], PDO::PARAM_INT);
        $stmt->bindParam(':fa_id', $datos['fa_id'], PDO::PARAM_INT);
        $stmt->bindParam(':su_id', $datos['su_id'], PDO::PARAM_INT);
        $stmt->bindParam(':us_id', $datos['us_id'], PDO::PARAM_INT);
        $stmt->bindParam(':dev_total', $datos['dev_total']);
        $stmt->bindParam(':dev_cantidad', $datos['dev_cantidad'], PDO::PARAM_INT);
        $stmt->bindParam(':dev_motivo', $datos['dev_motivo']);
        $stmt->execute();

        return (int) $db->lastInsertId();
    }

    protected static function registrar_movimiento_baja_model($datos)
    {
        $db = mainModel::conectar();

        $sql = "
                INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
                VALUES
                (:lm_id, :med_id, :su_id, :us_id, 'baja', :mi_cantidad, :mi_unidad, 'devolucion', :mi_referencia_id, :mi_motivo)
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':lm_id', $datos['lm_id'], PDO::PARAM_INT);
        $stmt->bindParam(':med_id', $datos['med_id'], PDO::PARAM_INT);
        $stmt->bindParam(':su_id', $datos['su_id'], PDO::PARAM_INT);
        $stmt->bindParam(':us_id', $datos['us_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mi_cantidad', $datos['mi_cantidad'], PDO::PARAM_INT);
        $stmt->bindParam(':mi_unidad', $datos['mi_unidad']);
        $stmt->bindParam(':mi_referencia_id', $datos['mi_referencia_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mi_motivo', $datos['mi_motivo']);
        $stmt->execute();

        return $stmt;
    }

    protected static function registrar_movimiento_cambio_model($datos)
    {
        $db = mainModel::conectar();

        $sql = "
                INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
                VALUES
                (:lm_id, :med_id, :su_id, :us_id, 'salida', :mi_cantidad, :mi_unidad, 'cambio', :mi_referencia_id, :mi_motivo)
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':lm_id', $datos['lm_id'], PDO::PARAM_INT);
        $stmt->bindParam(':med_id', $datos['med_id'], PDO::PARAM_INT);
        $stmt->bindParam(':su_id', $datos['su_id'], PDO::PARAM_INT);
        $stmt->bindParam(':us_id', $datos['us_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mi_cantidad', $datos['mi_cantidad'], PDO::PARAM_INT);
        $stmt->bindParam(':mi_unidad', $datos['mi_unidad']);
        $stmt->bindParam(':mi_referencia_id', $datos['mi_referencia_id'], PDO::PARAM_INT);
        $stmt->bindParam(':mi_motivo', $datos['mi_motivo']);
        $stmt->execute();

        return $stmt;
    }

    protected static function actualizar_estado_detalle_venta_model($dv_id)
    {
        $db = mainModel::conectar();

        $sql = "
                UPDATE detalle_venta 
                SET dv_estado = 0
                WHERE dv_id = :dv_id
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':dv_id', $dv_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    protected static function actualizar_estado_venta_model($ve_id)
    {
        $db = mainModel::conectar();

        $sql = "
                UPDATE ventas 
                SET ve_estado_documento = 'devuelto',
                    ve_actualizado_en = NOW()
                WHERE ve_id = :ve_id
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':ve_id', $ve_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    protected static function descontar_lote_cambio_model($lm_id, $cantidad)
    {
        if ($cantidad <= 0) return false;

        $db = mainModel::conectar();

        $stmt = $db->prepare("
                SELECT lm_cant_actual_unidades, lm_cant_actual_cajas, 
                    lm_cant_blister, lm_cant_unidad 
                FROM lote_medicamento 
                WHERE lm_id = :lm_id 
                FOR UPDATE
            ");
        $stmt->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
        $stmt->execute();
        $lm = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lm) return false;

        $unidades_antes = (int)$lm['lm_cant_actual_unidades'];
        $blister = max(1, (int)$lm['lm_cant_blister']);
        $por_blister = max(1, (int)$lm['lm_cant_unidad']);
        $unidades_por_caja = $blister * $por_blister;

        if ($unidades_antes < $cantidad) return false;

        $unidades_despues = $unidades_antes - $cantidad;
        $cajas_despues = (int)floor($unidades_despues / $unidades_por_caja);

        $upd = $db->prepare("
                UPDATE lote_medicamento
                SET lm_cant_actual_unidades = :unidades_despues,
                    lm_cant_actual_cajas = :cajas_despues,
                    lm_actualizado_en = NOW()
                WHERE lm_id = :lm_id 
                AND lm_cant_actual_unidades >= :cantidad
            ");
        $upd->bindParam(':unidades_despues', $unidades_despues, PDO::PARAM_INT);
        $upd->bindParam(':cajas_despues', $cajas_despues, PDO::PARAM_INT);
        $upd->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
        $upd->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $upd->execute();

        return $upd->rowCount() > 0;
    }

    protected static function obtener_lotes_disponibles_model($med_id, $sucursal_id)
    {
        $db = mainModel::conectar();

        $sql = "
                SELECT 
                    lm_id,
                    lm_numero_lote,
                    lm_cant_actual_unidades,
                    lm_precio_venta,
                    lm_fecha_vencimiento
                FROM lote_medicamento
                WHERE med_id = :med_id 
                AND su_id = :su_id 
                AND lm_estado = 'activo' 
                AND lm_cant_actual_unidades > 0
                ORDER BY lm_fecha_vencimiento ASC, lm_fecha_ingreso ASC
                LIMIT 10
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
        $stmt->bindParam(':su_id', $sucursal_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    protected static function verificar_venta_ya_devuelta_model($ve_id)
    {
        $db = mainModel::conectar();

        $sql = "
                SELECT dev_id 
                FROM devoluciones 
                WHERE ve_id = :ve_id 
                AND dev_estado = 'aceptada'
                LIMIT 1
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':ve_id', $ve_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    protected static function agregar_informe_devolucion_model($datos)
    {
        $db = mainModel::conectar();

        $sql = "
                INSERT INTO informes 
                (inf_nombre, inf_tipo, inf_usuario, inf_config, inf_creado_en)
                VALUES 
                (:inf_nombre, 'devolucion', :inf_usuario, :inf_config, NOW())
            ";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':inf_nombre', $datos['inf_nombre']);
        $stmt->bindParam(':inf_usuario', $datos['inf_usuario'], PDO::PARAM_INT);
        $stmt->bindParam(':inf_config', $datos['inf_config']);
        $stmt->execute();

        return $stmt;
    }

    protected static function descontar_inventario_consolidado_devolucion_model($med_id, $sucursal_id, $cantidad)
    {
        if ($cantidad <= 0) return false;

        $db = mainModel::conectar();

        try {
            $check_stmt = $db->prepare("
                    SELECT inv_id, inv_total_unidades 
                    FROM inventarios 
                    WHERE med_id = :med_id AND su_id = :su_id
                ");
            $check_stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $check_stmt->bindParam(':su_id', $sucursal_id, PDO::PARAM_INT);
            $check_stmt->execute();
            $inv = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$inv) {
                error_log("No existe inventario para med_id={$med_id}, su_id={$sucursal_id}");
                return false;
            }

            $lock_stmt = $db->prepare("
                    SELECT inv_id, inv_total_unidades, inv_total_cajas 
                    FROM inventarios 
                    WHERE inv_id = :inv_id 
                    FOR UPDATE
                ");
            $lock_stmt->bindParam(':inv_id', $inv['inv_id'], PDO::PARAM_INT);
            $lock_stmt->execute();
            $inv = $lock_stmt->fetch(PDO::FETCH_ASSOC);

            $unidades_antes = (int)$inv['inv_total_unidades'];

            if ($unidades_antes < $cantidad) {
                error_log("Stock insuficiente en inventario consolidado. Disponible: {$unidades_antes}, Requerido: {$cantidad}");
                return false;
            }

            $unidades_despues = $unidades_antes - $cantidad;

            $stmt2 = $db->prepare("
                    SELECT lm_cant_blister, lm_cant_unidad 
                    FROM lote_medicamento 
                    WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo' 
                    LIMIT 1
                ");
            $stmt2->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $stmt2->bindParam(':su_id', $sucursal_id, PDO::PARAM_INT);
            $stmt2->execute();
            $ref = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($ref) {
                $blister = max(1, (int)$ref['lm_cant_blister']);
                $por_unidad = max(1, (int)$ref['lm_cant_unidad']);
                $unidades_por_caja = $blister * $por_unidad;
            } else {
                $unidades_por_caja = 1;
            }

            $cajas_despues = (int)floor($unidades_despues / $unidades_por_caja);

            $upd = $db->prepare("
                    UPDATE inventarios 
                    SET inv_total_unidades = :unidades_despues, 
                        inv_total_cajas = :cajas_despues, 
                        inv_actualizado_en = NOW()
                    WHERE inv_id = :inv_id
                ");
            $upd->bindParam(':unidades_despues', $unidades_despues, PDO::PARAM_INT);
            $upd->bindParam(':cajas_despues', $cajas_despues, PDO::PARAM_INT);
            $upd->bindParam(':inv_id', $inv['inv_id'], PDO::PARAM_INT);
            $upd->execute();

            return $upd->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en descontar_inventario_consolidado_devolucion_model: " . $e->getMessage());
            return false;
        }
    }
}
