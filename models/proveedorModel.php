<?php

require_once "mainModel.php";

class proveedorModel extends mainModel
{
    protected static function datos_proveedores_model($inicio, $registros, $filtros = [])
    {
        $sql = "
                    SELECT 
                        p.pr_id,
                        p.pr_nombres,
                        p.pr_apellido_paterno,
                        p.pr_apellido_materno,
                        p.pr_nit,
                        p.pr_telefono,
                        p.pr_direccion,
                        p.pr_creado_en,
                        p.pr_estado,
                        
                        COALESCE(COUNT(DISTINCT c.co_id), 0) AS total_compras,
                        COALESCE(SUM(c.co_total), 0) AS monto_total_compras,
                        MAX(c.co_fecha) AS ultima_compra,
                        COALESCE(COUNT(DISTINCT lm.lm_id), 0) AS total_lotes,
                        
                        DATEDIFF(CURDATE(), MAX(c.co_fecha)) AS dias_ultima_compra
                        
                    FROM proveedores p
                    LEFT JOIN compras c ON c.pr_id = p.pr_id AND c.co_estado = 1
                    LEFT JOIN lote_medicamento lm ON lm.pr_id = p.pr_id
                    WHERE 1=1
                ";

        $params = [];

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                        p.pr_nombres LIKE :busqueda OR
                        p.pr_apellido_paterno LIKE :busqueda OR
                        p.pr_apellido_materno LIKE :busqueda OR
                        p.pr_nit LIKE :busqueda OR
                        p.pr_telefono LIKE :busqueda
                    )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['estado'])) {
            if ($filtros['estado'] === 'activo') {
                $sql .= " AND p.pr_estado = 1";
            } elseif ($filtros['estado'] === 'inactivo') {
                $sql .= " AND p.pr_estado = 0";
            }
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(p.pr_creado_en) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(p.pr_creado_en) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " GROUP BY p.pr_id";

        if (!empty($filtros['con_compras'])) {
            if ($filtros['con_compras'] === 'con_compras') {
                $sql .= " HAVING total_compras > 0";
            } elseif ($filtros['con_compras'] === 'sin_compras') {
                $sql .= " HAVING total_compras = 0";
            }
        }

        if (!empty($filtros['ultima_compra'])) {
            if ($filtros['ultima_compra'] === '7') {
                $sql .= " HAVING dias_ultima_compra <= 7";
            } elseif ($filtros['ultima_compra'] === '30') {
                $sql .= " HAVING dias_ultima_compra <= 30";
            } elseif ($filtros['ultima_compra'] === '90') {
                $sql .= " HAVING dias_ultima_compra <= 90";
            } elseif ($filtros['ultima_compra'] === 'mas_90') {
                $sql .= " HAVING dias_ultima_compra > 90";
            } elseif ($filtros['ultima_compra'] === 'nunca') {
                $sql .= " HAVING ultima_compra IS NULL";
            }
        }

        $sql .= " ORDER BY p.pr_nombres ASC";
        $sql .= " LIMIT :inicio, :registros";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
        $stmt->bindParam(':registros', $registros, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    protected static function contar_proveedores_model($filtros = [])
    {
        $sql = "
                    SELECT COUNT(DISTINCT p.pr_id) as total
                    FROM proveedores p
                    LEFT JOIN compras c ON c.pr_id = p.pr_id AND c.co_estado = 1
                    WHERE 1=1
                ";

        $params = [];

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                        p.pr_nombres LIKE :busqueda OR
                        p.pr_apellido_paterno LIKE :busqueda OR
                        p.pr_apellido_materno LIKE :busqueda OR
                        p.pr_nit LIKE :busqueda OR
                        p.pr_telefono LIKE :busqueda
                    )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['estado'])) {
            if ($filtros['estado'] === 'activo') {
                $sql .= " AND p.pr_estado = 1";
            } elseif ($filtros['estado'] === 'inactivo') {
                $sql .= " AND p.pr_estado = 0";
            }
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(p.pr_creado_en) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(p.pr_creado_en) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }
}
