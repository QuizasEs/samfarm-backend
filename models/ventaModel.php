<?php

require_once "mainModel.php";

class ventaModel extends mainModel
{
    /* buscar medicamentos */
    /* buscar medicamentos con stock disponible en sucursal */
    protected static function buscar_medicamento_model($termino, $sucursal_id, $filtros = [])
    {
        if (!$sucursal_id) {
            return [];
        }

        $termino = "%$termino%";
        $conexion = mainModel::conectar();

        $sql = "
        SELECT DISTINCT
            m.med_id,
            m.med_nombre_quimico AS nombre,
            COALESCE(m.med_version_comercial, '') AS version_comercial,
            COALESCE(ff.ff_nombre, '') AS presentacion,
            COALESCE(la.la_nombre_comercial, '') AS linea,
            MIN(lm.lm_precio_venta) AS precio_venta,
            SUM(lm.lm_cant_actual_unidades) AS stock
        FROM medicamento m
        INNER JOIN lote_medicamento lm ON lm.med_id = m.med_id
        LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
        LEFT JOIN laboratorios la ON la.la_id = m.la_id
        WHERE lm.su_id = :sucursal_id
          AND lm.lm_estado = 'activo'
          AND lm.lm_cant_actual_unidades > 0
          AND (
              m.med_nombre_quimico LIKE :termino
              OR m.med_codigo_barras LIKE :termino
              OR m.med_version_comercial LIKE :termino
          )
    ";

        $params = [
            ":termino" => $termino,
            ":sucursal_id" => $sucursal_id
        ];

        // Aplicar filtros opcionales
        if (!empty($filtros['linea'])) {
            $sql .= " AND m.la_id = :la_id";
            $params[":la_id"] = $filtros['linea'];
        }
        if (!empty($filtros['presentacion'])) {
            $sql .= " AND m.ff_id = :ff_id";
            $params[":ff_id"] = $filtros['presentacion'];
        }
        if (!empty($filtros['funcion'])) {
            $sql .= " AND m.uf_id = :uf_id";
            $params[":uf_id"] = $filtros['funcion'];
        }
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = :vd_id";
            $params[":vd_id"] = $filtros['via'];
        }

        $sql .= " GROUP BY m.med_id, m.med_nombre_quimico, m.med_version_comercial, ff.ff_nombre, la.la_nombre_comercial
              HAVING SUM(lm.lm_cant_actual_unidades) > 0
              ORDER BY m.med_nombre_quimico ASC 
              LIMIT 50";

        $stmt = $conexion->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* buscar los productos mas vendidos de la sucursal */
    protected static function top_ventas_model($sucursal_id, $limit = 5)
    {
        if (!$sucursal_id) {
            return [];
        }

        $sql = mainModel::conectar()->prepare("
            SELECT
                m.med_id,
                m.med_nombre_quimico AS nombre,
                COALESCE(lm_ag.min_precio, 0) AS precio_venta,
                COALESCE(SUM(dv.dv_cantidad), 0) AS vendidos
            FROM medicamento m
            INNER JOIN detalle_venta dv ON dv.med_id = m.med_id
            INNER JOIN ventas v ON v.ve_id = dv.ve_id AND v.su_id = :sucursal_id
            LEFT JOIN (
                SELECT med_id, MIN(lm_precio_venta) AS min_precio
                FROM lote_medicamento
                WHERE lm_estado = 'activo' AND su_id = :sucursal_id2
                GROUP BY med_id
            ) lm_ag ON lm_ag.med_id = m.med_id
            WHERE lm_ag.min_precio IS NOT NULL AND lm_ag.min_precio > 0
            GROUP BY m.med_id
            ORDER BY vendidos DESC
            LIMIT :limit
        ");

        $sql->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $sql->bindValue(":sucursal_id", (int)$sucursal_id, PDO::PARAM_INT);
        $sql->bindValue(":sucursal_id2", (int)$sucursal_id, PDO::PARAM_INT);

        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}
