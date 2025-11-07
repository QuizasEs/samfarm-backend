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
            (pr_id, med_id, su_id, lm_numero_lote, lm_cantidad_inicial, lm_cantidad_actual, lm_precio_compra, lm_precio_venta, lm_fecha_ingreso, lm_fecha_vencimiento)
            VALUES
            (:pr_id, :med_id, :su_id, :lm_numero_lote, :lm_cantidad_inicial, :lm_cantidad_actual, :lm_precio_compra, :lm_precio_venta, NOW(), :lm_fecha_vencimiento)
        ");
        $sql->bindParam(":pr_id", $datos['pr_id']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":lm_numero_lote", $datos['lm_numero_lote']);
        $sql->bindParam(":lm_cantidad_inicial", $datos['lm_cantidad_inicial']);
        $sql->bindParam(":lm_cantidad_actual", $datos['lm_cantidad_actual']);
        $sql->bindParam(":lm_precio_compra", $datos['lm_precio_compra']);
        $sql->bindParam(":lm_precio_venta", $datos['lm_precio_venta']);
        $sql->bindParam(":lm_fecha_vencimiento", $datos['lm_fecha_vencimiento']);

        $sql->execute();
        return (int) mainModel::conectar()->lastInsertId();
    }
    /* insertar y/o actualizar inventario  */

    public static function actualizar_inventario_model($datos)
    {
        $sql = mainModel::conectar()->prepare(
            "INSERT INTO inventarios (su_id, med_id, lm_id, inv_cantidad, inv_reservado, inv_minimo, inv_maximo, inv_ultimo_precio)
                VALUES (:su_id, :med_id, :lm_id, :inv_cantidad, 0, 0, NULL, :inv_ultimo_precio)
                ON DUPLICATE KEY UPDATE
                    inv_cantidad = inv_cantidad + VALUES(inv_cantidad),
                    inv_ultimo_precio = VALUES(inv_ultimo_precio),
                    inv_actualizado_en = NOW()"
        );

        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":inv_cantidad", $datos['inv_cantidad']);
        $sql->bindParam(":inv_ultimo_precio", $datos['inv_ultimo_precio']);

        $sql->execute();
        return $sql;
    }
    /* insertar movimientos para inventario modelo */
    public static function agregar_movimiento_inventario_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
        INSERT INTO movimiento_inventario
            (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_fecha, mi_referencia_tipo, mi_referencia_id, mi_motivo)
            VALUES
            (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, NOW(), :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)
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
    /* registrar un informe en informes ocn el tipo "compra" */
    public static function agregar_informe_compra_model($datos)
    {
        $sql = mainModel::conectar()->prepare(
            "INSERT INTO informes (inf_nombre, inf_tipo, inf_usuario, inf_config)
            VALUES (:inf_nombre, 'compra', :inf_usuario, :inf_config)
        "
        );
        $sql->bindParam(":inf_nombre", $datos['inf_nombre']);
        $sql->bindParam(":inf_usuario", $datos['inf_usuario']);
        $sql->bindParam(":inf_config", $datos['inf_config']);

        $sql->execute(); // ← FALTABA ESTO
        return $sql;
    }
}
