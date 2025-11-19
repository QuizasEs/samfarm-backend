<?php

require_once "mainModel.php";

class compraModel extends mainModel
{
    /* registrar compra nueva */
    public static function agregar_compra_model($datos): int
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO compras
                (co_numero, co_fecha, la_id, us_id, su_id, pr_id, co_subtotal, co_impuesto, co_total, co_numero_factura, co_fecha_factura, co_razon_social)
            VALUES
                (:co_numero, NOW(), :la_id, :us_id, :su_id, :pr_id, :co_subtotal, :co_impuesto, :co_total, :co_numero_factura, :co_fecha_factura, :co_razon_social)
        ");
        $sql->bindParam(":co_numero", $datos['co_numero']);
        $sql->bindParam(":la_id", $datos['la_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":pr_id", $datos['pr_id']);
        $sql->bindParam(":co_subtotal", $datos['co_subtotal']);
        $sql->bindParam(":co_impuesto", $datos['co_impuesto']);
        $sql->bindParam(":co_total", $datos['co_total']);
        $sql->bindParam(":co_numero_factura", $datos['co_numero_factura']);
        $sql->bindParam(":co_fecha_factura", $datos['co_fecha_factura']);
        $sql->bindParam(":co_razon_social", $datos['co_razon_social']);

        $sql->execute();
        return (int) mainModel::conectar()->lastInsertId();
    }

    /* registrar detalle de compra */
    public static function agregar_detalle_compra_model($item)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO detalle_compra
                (co_id, med_id, lm_id, dc_cantidad, dc_precio_unitario, dc_descuento, dc_subtotal)
            VALUES
                (:co_id, :med_id, :lm_id, :cantidad, :precio_unitario, :descuento, :subtotal)
        ");
        $sql->bindParam(":co_id", $item['co_id']);
        $sql->bindParam(":med_id", $item['med_id']);
        $sql->bindParam(":lm_id", $item['lm_id']);
        $sql->bindParam(":cantidad", $item['cantidad']);
        $sql->bindParam(":precio_unitario", $item['precio_unitario']);
        $sql->bindParam(":descuento", $item['descuento']);
        $sql->bindParam(":subtotal", $item['subtotal']);
        $sql->execute();
        return $sql;
    }

    /* insertar lote medicamento */
    public static function agregar_lote_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO lote_medicamento
            (pr_id, pr_id_compra, med_id, su_id, lm_numero_lote,
            lm_cant_caja, lm_cant_blister, lm_cant_unidad, lm_total_unidades,
            lm_cant_actual_cajas, lm_cant_actual_unidades,
            lm_precio_compra, lm_precio_venta, lm_fecha_ingreso, lm_fecha_vencimiento, lm_estado)
            VALUES
            (:pr_id, :pr_id_compra, :med_id, :su_id, :lm_numero_lote,
            :lm_cant_caja, :lm_cant_blister, :lm_cant_unidad, :lm_total_unidades,
            :lm_cant_actual_cajas, :lm_cant_actual_unidades,
            :lm_precio_compra, :lm_precio_venta, NOW(), :lm_fecha_vencimiento, :lm_estado)
        ");

        $sql->bindParam(":pr_id", $datos['pr_id']);
        $sql->bindParam(":pr_id_compra", $datos['pr_id_compra']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":lm_numero_lote", $datos['lm_numero_lote']);
        $sql->bindParam(":lm_cant_caja", $datos['lm_cant_caja']);
        $sql->bindParam(":lm_cant_blister", $datos['lm_cant_blister']);
        $sql->bindParam(":lm_cant_unidad", $datos['lm_cant_unidad']);
        $sql->bindParam(":lm_total_unidades", $datos['lm_total_unidades']);
        $sql->bindParam(":lm_cant_actual_cajas", $datos['lm_cant_actual_cajas']);
        $sql->bindParam(":lm_cant_actual_unidades", $datos['lm_cant_actual_unidades']);
        $sql->bindParam(":lm_precio_compra", $datos['lm_precio_compra']);
        $sql->bindParam(":lm_precio_venta", $datos['lm_precio_venta']);
        $sql->bindParam(":lm_fecha_vencimiento", $datos['lm_fecha_vencimiento']);
        $sql->bindParam(":lm_estado", $datos['lm_estado']);

        $sql->execute();
        return (int) mainModel::conectar()->lastInsertId();
    }

    /* registrar historial de lote */
    public static function registrar_historial_Lote_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
            VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion)
        ");
        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":hl_accion", $datos['hl_accion']);
        $sql->bindParam(":hl_descripcion", $datos['hl_descripcion']);
        $sql->execute();
        return $sql;
    }

    /**************************************************************************
     * ACTUALIZAR INVENTARIO - CORREGIDO
     * - Usa ON DUPLICATE KEY UPDATE (requiere UNIQUE KEY en su_id,med_id)
     * - Si existe: SUMA las cantidades
     * - Si no existe: CREA el registro
     **************************************************************************/
    public static function actualizar_inventario_model($datos)
    {
        $db = mainModel::conectar();

        try {
            $sql = $db->prepare("
                INSERT INTO inventarios 
                (su_id, med_id, inv_total_cajas, inv_total_unidades, inv_total_valorado, inv_creado_en, inv_actualizado_en)
                VALUES 
                (:su_id, :med_id, :inv_total_cajas, :inv_total_unidades, :inv_total_valorado, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    inv_total_cajas = inv_total_cajas + VALUES(inv_total_cajas),
                    inv_total_unidades = inv_total_unidades + VALUES(inv_total_unidades),
                    inv_total_valorado = inv_total_valorado + VALUES(inv_total_valorado),
                    inv_actualizado_en = NOW()
            ");

            $sql->bindParam(":su_id", $datos['su_id'], PDO::PARAM_INT);
            $sql->bindParam(":med_id", $datos['med_id'], PDO::PARAM_INT);
            $sql->bindParam(":inv_total_cajas", $datos['inv_total_cajas'], PDO::PARAM_INT);
            $sql->bindParam(":inv_total_unidades", $datos['inv_total_unidades'], PDO::PARAM_INT);
            $sql->bindParam(":inv_total_valorado", $datos['inv_total_valorado']);

            $sql->execute();

            // Log para debugging
            error_log("INVENTARIO ACTUALIZADO: med_id={$datos['med_id']}, su_id={$datos['su_id']}, +{$datos['inv_total_unidades']} unidades, +{$datos['inv_total_cajas']} cajas");

            return $sql;
        } catch (PDOException $e) {
            error_log("ERROR en actualizar_inventario_model: " . $e->getMessage());
            return false;
        }
    }

    /* insertar movimientos para inventario */
    public static function agregar_movimiento_inventario_model($datos)
    {
        $db = mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
            VALUES
                (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)
        ");

        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":mi_tipo", $datos['mi_tipo']);
        $sql->bindParam(":mi_cantidad", $datos['mi_cantidad']);
        $sql->bindParam(":mi_unidad", $datos['mi_unidad']);
        $sql->bindParam(":mi_referencia_tipo", $datos['mi_referencia_tipo']);
        $sql->bindParam(":mi_referencia_id", $datos['mi_referencia_id']);
        $sql->bindParam(":mi_motivo", $datos['mi_motivo']);

        $sql->execute();
        return $sql;
    }

    /* registrar un informe en informes con el tipo "compra" */
    public static function agregar_informe_compra_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO informes (inf_nombre, inf_tipo, inf_usuario, inf_config)
            VALUES (:inf_nombre, 'compra', :inf_usuario, :inf_config)
        ");
        $sql->bindParam(":inf_nombre", $datos['inf_nombre']);
        $sql->bindParam(":inf_usuario", $datos['inf_usuario']);
        $sql->bindParam(":inf_config", $datos['inf_config']);

        $sql->execute();
        return $sql;
    }
}
