<?php

if ($peticionAjax) {
    require_once "../models/mainModel.php";
} else {
    require_once "./models/mainModel.php";
}

class ajusteInventarioCompletoModel extends mainModel
{
    /**
     * Modelo para obtener todas las sucursales activas.
     */
    protected static function obtener_sucursales_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT su_id, su_nombre FROM sucursales WHERE su_estado = 1 ORDER BY su_nombre ASC");
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para buscar medicamentos para el ajuste de inventario.
     */
    protected static function buscar_medicamentos_modelo($termino, $sucursal_id)
    {
        $termino_like = "%" . $termino . "%";

        $consulta = "
            SELECT 
                m.med_id,
                m.med_nombre_quimico,
                m.med_principio_activo,
                m.med_codigo_barras,
                p.pr_razon_social AS proveedor,
                i.inv_total_unidades,
                s.su_nombre,
                s.su_id
            FROM medicamento m
            LEFT JOIN lote_medicamento lm ON lm.med_id = m.med_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            JOIN inventarios i ON m.med_id = i.med_id
            JOIN sucursales s ON i.su_id = s.su_id
            WHERE (m.med_nombre_quimico LIKE :termino OR m.med_principio_activo LIKE :termino OR m.med_codigo_barras LIKE :termino)
        ";

        if (!empty($sucursal_id)) {
            $consulta .= " AND i.su_id = :sucursal_id";
        }

        $consulta .= " ORDER BY m.med_nombre_quimico, s.su_nombre LIMIT 50";

        $sql = mainModel::conectar()->prepare($consulta);
        $sql->bindParam(":termino", $termino_like, PDO::PARAM_STR);
        if (!empty($sucursal_id)) {
            $sql->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        }

        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener los datos de un medicamento específico.
     */
    protected static function obtener_medicamento_modelo($medicamento_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT m.*, p.pr_razon_social AS proveedor 
            FROM medicamento m
            LEFT JOIN lote_medicamento lm ON lm.med_id = m.med_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            WHERE m.med_id = :medicamento_id
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener los datos de un medicamento específico incluyendo cantidades y presentaciones.
     */
    protected static function obtener_medicamento_completo_modelo($medicamento_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT 
                m.*,
                p.pr_razon_social AS proveedor,
                ff.ff_nombre as forma_farmaceutica_nombre,
                uf.uf_nombre as uso_farmacologico_nombre,
                vd.vd_nombre as via_administracion_nombre
            FROM medicamento m
            LEFT JOIN lote_medicamento lm ON lm.med_id = m.med_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            LEFT JOIN forma_farmaceutica ff ON m.ff_id = ff.ff_id
            LEFT JOIN uso_farmacologico uf ON m.uf_id = uf.uf_id
            LEFT JOIN via_de_administracion vd ON m.vd_id = vd.vd_id
            WHERE m.med_id = :medicamento_id
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener los lotes activos de un medicamento en una sucursal.
     */
    protected static function obtener_lotes_medicamento_modelo($medicamento_id, $sucursal_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * 
            FROM lote_medicamento 
            WHERE med_id = :medicamento_id 
              AND su_id = :sucursal_id 
              AND lm_estado = 'activo'
            ORDER BY lm_fecha_vencimiento ASC
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener un lote específico.
     */
    protected static function obtener_lote_modelo($lote_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * 
            FROM lote_medicamento 
            WHERE lm_id = :lote_id
        ");
        $sql->bindParam(":lote_id", $lote_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para actualizar los datos del medicamento.
     */
    protected static function actualizar_medicamento_modelo($medicamento_id, $nombre, $principio, $codigo, $proveedor_id, $ff_id, $uf_id, $cant_caja, $cant_blister, $cant_unidad, $via_administracion)
    {
        $sql = mainModel::conectar()->prepare("
            UPDATE medicamento 
            SET med_nombre_quimico = :nombre, 
                med_principio_activo = :principio, 
                med_codigo_barras = :codigo, 
                pr_id = :proveedor_id, 
                ff_id = :ff_id, 
                uf_id = :uf_id,
                vd_id = :via_administracion
            WHERE med_id = :medicamento_id
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $sql->bindParam(":principio", $principio, PDO::PARAM_STR);
        $sql->bindParam(":codigo", $codigo, PDO::PARAM_STR);
        $sql->bindParam(":proveedor_id", $proveedor_id, PDO::PARAM_INT);
        $sql->bindParam(":ff_id", $ff_id, PDO::PARAM_INT);
        $sql->bindParam(":uf_id", $uf_id, PDO::PARAM_INT);
        $sql->bindParam(":via_administracion", $via_administracion, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para actualizar los datos de un lote.
     */
    protected static function actualizar_lote_modelo($lote_id, $numero_lote, $cant_caja, $cant_blister, $cant_unidad, $cant_actual_cajas, $cant_actual_unidades, $precio_compra, $precio_venta, $fecha_vencimiento)
    {
        $sql = mainModel::conectar()->prepare("
            UPDATE lote_medicamento 
            SET lm_numero_lote = :numero_lote,
                lm_cant_caja = :cant_caja,
                lm_cant_blister = :cant_blister,
                lm_cant_unidad = :cant_unidad,
                lm_cant_actual_cajas = :cant_actual_cajas,
                lm_cant_actual_unidades = :cant_actual_unidades,
                lm_precio_compra = :precio_compra, 
                lm_precio_venta = :precio_venta, 
                lm_fecha_vencimiento = :fecha_vencimiento
            WHERE lm_id = :lote_id
        ");
        
        $sql->bindParam(":lote_id", $lote_id, PDO::PARAM_INT);
        $sql->bindParam(":numero_lote", $numero_lote, PDO::PARAM_STR);
        $sql->bindParam(":cant_caja", $cant_caja, PDO::PARAM_INT);
        $sql->bindParam(":cant_blister", $cant_blister, PDO::PARAM_INT);
        $sql->bindParam(":cant_unidad", $cant_unidad, PDO::PARAM_INT);
        $sql->bindParam(":cant_actual_cajas", $cant_actual_cajas, PDO::PARAM_INT);
        $sql->bindParam(":cant_actual_unidades", $cant_actual_unidades, PDO::PARAM_INT);
        $sql->bindParam(":precio_compra", $precio_compra, PDO::PARAM_STR);
        $sql->bindParam(":precio_venta", $precio_venta, PDO::PARAM_STR);
        $sql->bindParam(":fecha_vencimiento", $fecha_vencimiento, PDO::PARAM_STR);
        
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para eliminar un lote.
     */
    protected static function eliminar_lote_modelo($lote_id)
    {
        $sql = mainModel::conectar()->prepare("
            DELETE FROM lote_medicamento 
            WHERE lm_id = :lote_id
        ");
        $sql->bindParam(":lote_id", $lote_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Recalcula el inventario total para un medicamento en una sucursal específica.
     */
    public static function recalcular_inventario($medicamento_id, $sucursal_id)
    {
        // Sumar cantidades de todos los lotes activos
        $sql = mainModel::conectar()->prepare("
            SELECT 
                SUM(lm_cant_actual_cajas) as total_cajas,
                SUM(lm_cant_actual_unidades) as total_unidades,
                SUM(lm_cant_actual_unidades * lm_precio_compra) as total_valorado
            FROM lote_medicamento
            WHERE med_id = :medicamento_id AND su_id = :sucursal_id AND lm_estado = 'activo'
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        $sql->execute();
        $totales = $sql->fetch(PDO::FETCH_ASSOC);

        $total_cajas = $totales['total_cajas'] ?? 0;
        $total_unidades = $totales['total_unidades'] ?? 0;
        $total_valorado = $totales['total_valorado'] ?? 0.00;

        // Actualizar la tabla inventarios
        $sql_update = mainModel::conectar()->prepare("
            UPDATE inventarios
            SET inv_total_cajas = :total_cajas,
                inv_total_unidades = :total_unidades,
                inv_total_valorado = :total_valorado,
                inv_actualizado_en = current_timestamp()
            WHERE med_id = :medicamento_id AND su_id = :sucursal_id
        ");
        $sql_update->bindParam(":total_cajas", $total_cajas, PDO::PARAM_INT);
        $sql_update->bindParam(":total_unidades", $total_unidades, PDO::PARAM_INT);
        $sql_update->bindParam(":total_valorado", $total_valorado, PDO::PARAM_STR);
        $sql_update->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql_update->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        
        return $sql_update->execute();
    }

    /**
     * Modelo para obtener proveedores.
     */
    protected static function obtener_proveedores_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT pr_id, pr_razon_social FROM proveedores WHERE pr_estado = 1 ORDER BY pr_razon_social ASC");
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener formas farmacéuticas (presentaciones).
     */
    protected static function obtener_formas_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT ff_id, ff_nombre FROM forma_farmaceutica WHERE ff_estado = 1 ORDER BY ff_nombre ASC");
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener usos farmacológicos.
     */
    protected static function obtener_usos_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT uf_id, uf_nombre FROM uso_farmacologico WHERE uf_estado = 1 ORDER BY uf_nombre ASC");
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener vías de administración.
     */
    protected static function obtener_vias_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT vd_id, vd_nombre FROM via_de_administracion WHERE vd_estado = 1 ORDER BY vd_nombre ASC");
        $sql->execute();
        return $sql;
    }
}
