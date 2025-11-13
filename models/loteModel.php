<?php

require_once "mainModel.php";

class loteModel extends mainModel
{
    /* modelo que optiene datos del lote  */
    protected static function datos_lote_model($id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT 
                lm.lm_id,
                lm.med_id,
                lm.su_id,
                lm.pr_id,
                lm.lm_numero_lote,
                lm.lm_cant_caja,
                lm.lm_cant_blister,
                lm.lm_cant_unidad,
                lm.lm_total_unidades,
                lm.lm_cant_actual_cajas,
                lm.lm_cant_actual_unidades,
                lm.lm_precio_compra,
                lm.lm_precio_venta,
                lm.lm_fecha_ingreso,
                lm.lm_fecha_vencimiento,
                lm.lm_estado,
                lm.lm_creado_en,
                lm.lm_actualizado_en,
                lm.lm_origen_id,
                m.med_nombre_quimico AS med_nombre,
                m.med_principio_activo,
                m.med_presentacion,
                m.med_accion_farmacologica,
                m.med_precio_unitario,
                m.med_precio_caja,
                m.med_codigo_barras,
                m.med_version_comercial,
                ff.ff_nombre AS forma_farmaceutica,
                uf.uf_nombre AS uso_farmacologico,
                vd.vd_nombre AS via_administracion,
                la.la_nombre_comercial AS laboratorio_nombre,
                p.pr_nombres AS proveedor_nombres,
                p.pr_apellido_paterno AS proveedor_apellido,
                s.su_nombre AS sucursal_nombre
            FROM lote_medicamento lm
            LEFT JOIN medicamento m ON lm.med_id = m.med_id
            LEFT JOIN forma_farmaceutica ff ON m.ff_id = ff.ff_id
            LEFT JOIN uso_farmacologico uf ON m.uf_id = uf.uf_id
            LEFT JOIN via_de_administracion vd ON m.vd_id = vd.vd_id
            LEFT JOIN laboratorios la ON m.la_id = la.la_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            LEFT JOIN sucursales s ON lm.su_id = s.su_id
            WHERE lm.lm_id = :ID
            LIMIT 1;

        ");
        $sql->bindParam(":ID", $id);
        $sql->execute();
        return $sql;
    }
    
    protected static function actualizar_lote_model($datos)
    {
        $sql = self::conectar()->prepare("
        UPDATE lote_medicamento
        SET
            lm_cant_blister = :lm_cant_blister,
            lm_cant_unidad = :lm_cant_unidad,
            lm_precio_compra = :lm_precio_compra,
            lm_precio_venta = :lm_precio_venta,
            lm_fecha_vencimiento = :lm_fecha_vencimiento,
            lm_actualizado_en = NOW(),
            lm_origen_id = :lm_origen_id
        WHERE lm_id = :ID
    ");

        $sql->bindParam(":lm_cant_blister", $datos['lm_cant_blister']);
        $sql->bindParam(":lm_cant_unidad", $datos['lm_cant_unidad']);
        $sql->bindParam(":lm_precio_compra", $datos['lm_precio_compra']);
        $sql->bindParam(":lm_precio_venta", $datos['lm_precio_venta']);
        $sql->bindParam(":lm_fecha_vencimiento", $datos['lm_fecha_vencimiento']);
        $sql->bindParam(":lm_origen_id", $datos['lm_origen_id']);
        $sql->bindParam(":ID", $datos['ID']);

        $sql->execute();
        return $sql;
    }

    /* Registro en historial_lote */
    protected static function registrar_historial_lote_model($datos)
    {
        $sql = self::conectar()->prepare("
        INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion, hl_fecha)
        VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion, NOW())
    ");

        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":hl_accion", $datos['hl_accion']);
        $sql->bindParam(":hl_descripcion", $datos['hl_descripcion']);

        $sql->execute();
        return $sql;
    }
}
