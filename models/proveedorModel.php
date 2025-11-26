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


    protected static function detalle_proveedor_model($pr_id)
    {
        $sql = "
            SELECT 
                p.*,
                COALESCE(COUNT(DISTINCT c.co_id), 0) AS total_compras,
                COALESCE(SUM(c.co_total), 0) AS monto_total_compras,
                COALESCE(COUNT(DISTINCT lm.lm_id), 0) AS total_lotes,
                MAX(c.co_fecha) AS ultima_compra,
                DATEDIFF(CURDATE(), p.pr_creado_en) AS dias_antiguedad
            FROM proveedores p
            LEFT JOIN compras c ON c.pr_id = p.pr_id AND c.co_estado = 1
            LEFT JOIN lote_medicamento lm ON lm.pr_id = p.pr_id
            WHERE p.pr_id = :pr_id
            GROUP BY p.pr_id
            LIMIT 1
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':pr_id', $pr_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function ultimas_compras_proveedor_model($pr_id, $limit = 5)
    {
        $sql = "
            SELECT 
                c.co_id,
                c.co_numero,
                c.co_fecha,
                c.co_total,
                c.co_numero_factura,
                la.la_nombre_comercial AS laboratorio,
                COUNT(dc.dc_id) AS total_items
            FROM compras c
            LEFT JOIN laboratorios la ON la.la_id = c.la_id
            LEFT JOIN detalle_compra dc ON dc.co_id = c.co_id
            WHERE c.pr_id = :pr_id AND c.co_estado = 1
            GROUP BY c.co_id
            ORDER BY c.co_fecha DESC
            LIMIT :limit
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':pr_id', $pr_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function top_medicamentos_proveedor_model($pr_id, $limit = 5)
    {
        $sql = "
            SELECT 
                m.med_id,
                m.med_nombre_quimico,
                la.la_nombre_comercial AS laboratorio,
                COUNT(dc.dc_id) AS veces_comprado,
                MAX(c.co_fecha) AS ultima_compra
            FROM detalle_compra dc
            INNER JOIN compras c ON c.co_id = dc.co_id
            INNER JOIN medicamento m ON m.med_id = dc.med_id
            LEFT JOIN laboratorios la ON la.la_id = m.la_id
            WHERE c.pr_id = :pr_id AND c.co_estado = 1
            GROUP BY m.med_id
            ORDER BY veces_comprado DESC
            LIMIT :limit
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':pr_id', $pr_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function exportar_proveedores_excel_model($filtros = [])
    {
        $sql = "
            SELECT 
                CONCAT_WS(' ', p.pr_nombres, p.pr_apellido_paterno, p.pr_apellido_materno) AS 'Proveedor',
                p.pr_nit AS 'NIT',
                p.pr_telefono AS 'Teléfono',
                p.pr_direccion AS 'Dirección',
                DATE_FORMAT(p.pr_creado_en, '%d/%m/%Y') AS 'Fecha Registro',
                COALESCE(COUNT(DISTINCT c.co_id), 0) AS 'Total Compras',
                COALESCE(SUM(c.co_total), 0) AS 'Monto Total (Bs)',
                COALESCE(COUNT(DISTINCT lm.lm_id), 0) AS 'Lotes Generados',
                CASE 
                    WHEN MAX(c.co_fecha) IS NULL THEN 'Nunca'
                    ELSE DATE_FORMAT(MAX(c.co_fecha), '%d/%m/%Y')
                END AS 'Última Compra',
                CASE 
                    WHEN p.pr_estado = 1 THEN 'ACTIVO'
                    ELSE 'INACTIVO'
                END AS 'Estado'
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

        $sql .= " GROUP BY p.pr_id ORDER BY p.pr_nombres ASC";

        $stmt = mainModel::conectar()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }


    /* modelo para registrar y editar */
    protected static function registrar_proveedor_model($datos)
    {
        $sql = "
            INSERT INTO proveedores (
                pr_nombres,
                pr_apellido_paterno,
                pr_apellido_materno,
                pr_nit,
                pr_telefono,
                pr_direccion,
                pr_estado
            ) VALUES (
                :nombres,
                :paterno,
                :materno,
                :nit,
                :telefono,
                :direccion,
                1
            )
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombres', $datos['nombres']);
        $stmt->bindParam(':paterno', $datos['paterno']);
        $stmt->bindParam(':materno', $datos['materno']);
        $stmt->bindParam(':nit', $datos['nit']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_proveedor_por_id_model($pr_id)
    {
        $sql = "SELECT * FROM proveedores WHERE pr_id = :pr_id LIMIT 1";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':pr_id', $pr_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function verificar_nit_duplicado_model($nit, $pr_id = null)
    {
        if ($pr_id) {
            $sql = "SELECT pr_id FROM proveedores WHERE pr_nit = :nit AND pr_id != :pr_id LIMIT 1";
            $stmt = mainModel::conectar()->prepare($sql);
            $stmt->bindParam(':pr_id', $pr_id, PDO::PARAM_INT);
        } else {
            $sql = "SELECT pr_id FROM proveedores WHERE pr_nit = :nit LIMIT 1";
            $stmt = mainModel::conectar()->prepare($sql);
        }
        $stmt->bindParam(':nit', $nit);
        $stmt->execute();
        return $stmt;
    }

    protected static function actualizar_proveedor_model($datos)
    {
        $sql = "
            UPDATE proveedores SET
                pr_nombres = :nombres,
                pr_apellido_paterno = :paterno,
                pr_apellido_materno = :materno,
                pr_nit = :nit,
                pr_telefono = :telefono,
                pr_direccion = :direccion,
                pr_actualizado_en = NOW()
            WHERE pr_id = :pr_id
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombres', $datos['nombres']);
        $stmt->bindParam(':paterno', $datos['paterno']);
        $stmt->bindParam(':materno', $datos['materno']);
        $stmt->bindParam(':nit', $datos['nit']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':pr_id', $datos['pr_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
