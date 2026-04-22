<?php

require_once "mainModel.php";

class proveedorModel extends mainModel
{
    protected static function datos_proveedores_model($inicio, $registros, $filtros = [])
    {
        $sql = "
                    SELECT
                        p.pr_id,
                        p.pr_razon_social,
                        p.pr_nit,
                        p.pr_telefono,
                        p.pr_correo,
                        p.pr_nombre_comercial,
                        p.pr_creado_en,
                        p.pr_estado,

                        0 AS total_compras,  -- Temporalmente 0 hasta que se implemente la relación compras-proveedores
                        0 AS monto_total_compras,  -- Temporalmente 0 hasta que se implemente la relación compras-proveedores
                        NULL AS ultima_compra,  -- Temporalmente NULL hasta que se implemente la relación compras-proveedores
                        COALESCE(COUNT(DISTINCT lm.lm_id), 0) AS total_lotes,

                        NULL AS dias_ultima_compra  -- Temporalmente NULL hasta que se implemente la relación compras-proveedores

                    FROM proveedores p
                    LEFT JOIN lote_medicamento lm ON lm.pr_id = p.pr_id
                    WHERE 1=1
                ";

        $params = [];

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                p.pr_razon_social LIKE :busqueda OR
                p.pr_nit LIKE :busqueda OR
                p.pr_telefono LIKE :busqueda OR
                p.pr_correo LIKE :busqueda OR
                p.pr_nombre_comercial LIKE :busqueda
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

        // Los filtros de compras están temporalmente deshabilitados hasta implementar la relación compras-proveedores
        // if (!empty($filtros['con_compras'])) {
        //     if ($filtros['con_compras'] === 'con_compras') {
        //         $sql .= " HAVING total_compras > 0";
        //     } elseif ($filtros['con_compras'] === 'sin_compras') {
        //         $sql .= " HAVING total_compras = 0";
        //     }
        // }

        // if (!empty($filtros['ultima_compra'])) {
        //     if ($filtros['ultima_compra'] === '7') {
        //         $sql .= " HAVING dias_ultima_compra <= 7";
        //     } elseif ($filtros['ultima_compra'] === '30') {
        //         $sql .= " HAVING dias_ultima_compra <= 30";
        //     } elseif ($filtros['ultima_compra'] === '90') {
        //         $sql .= " HAVING dias_ultima_compra <= 90";
        //     } elseif ($filtros['ultima_compra'] === 'mas_90') {
        //         $sql .= " HAVING dias_ultima_compra > 90";
        //     } elseif ($filtros['ultima_compra'] === 'nunca') {
        //         $sql .= " HAVING ultima_compra IS NULL";
        //     }
        // }

        $sql .= " ORDER BY p.pr_razon_social ASC";
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
                    WHERE 1=1
                ";

        $params = [];

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                        p.pr_razon_social LIKE :busqueda OR
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
                p.pr_id,
                p.pr_razon_social,
                p.pr_nombre_comercial,
                p.pr_nit,
                p.pr_telefono,
                p.pr_correo,
                p.pr_estado,
                p.pr_creado_en,
                p.pr_actualizado_en,
                0 AS total_compras,  -- Temporalmente 0 hasta implementar relación compras-proveedores
                0 AS monto_total_compras,  -- Temporalmente 0 hasta implementar relación compras-proveedores
                COALESCE(COUNT(DISTINCT lm.lm_id), 0) AS total_lotes,
                NULL AS ultima_compra,  -- Temporalmente NULL hasta implementar relación compras-proveedores
                DATEDIFF(CURDATE(), p.pr_creado_en) AS dias_antiguedad
            FROM proveedores p
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
        // Temporalmente retorna una consulta vacía hasta implementar la relación compras-proveedores
        // La tabla compras actual no tiene relación con proveedores
        $sql = "SELECT NULL as co_id, NULL as co_numero, NULL as co_fecha, NULL as co_total, NULL as co_numero_factura, NULL as proveedor, NULL as total_items WHERE 1=0";
        return mainModel::conectar()->prepare($sql);
    }

    protected static function top_medicamentos_proveedor_model($pr_id, $limit = 5)
    {
        // Temporalmente retorna una consulta vacía hasta implementar la relación compras-proveedores
        // La tabla compras actual no tiene relación con proveedores
        $sql = "SELECT NULL as med_id, NULL as med_nombre_quimico, NULL as veces_comprado, NULL as ultima_compra, NULL as proveedor WHERE 1=0";
        return mainModel::conectar()->prepare($sql);
    }

    protected static function exportar_proveedores_excel_model($filtros = [])
    {
        $sql = "
            SELECT
                p.pr_razon_social AS 'Proveedor',
                p.pr_nit AS 'NIT',
                p.pr_telefono AS 'Teléfono',
                p.pr_correo AS 'Correo',
                p.pr_nombre_comercial AS 'Nombre Comercial',
                DATE_FORMAT(p.pr_creado_en, '%d/%m/%Y') AS 'Fecha Registro',
                0 AS 'Total Compras',  -- Temporalmente 0 hasta implementar relación compras-proveedores
                0 AS 'Monto Total (Bs)',  -- Temporalmente 0 hasta implementar relación compras-proveedores
                COALESCE(COUNT(DISTINCT lm.lm_id), 0) AS 'Lotes Generados',
                'Nunca' AS 'Última Compra',  -- Temporalmente 'Nunca' hasta implementar relación compras-proveedores
                CASE
                    WHEN p.pr_estado = 1 THEN 'ACTIVO'
                    ELSE 'INACTIVO'
                END AS 'Estado'
            FROM proveedores p
            LEFT JOIN lote_medicamento lm ON lm.pr_id = p.pr_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                        p.pr_razon_social LIKE :busqueda OR
                        p.pr_nit LIKE :busqueda OR
                        p.pr_telefono LIKE :busqueda OR
                        p.pr_correo LIKE :busqueda OR
                        p.pr_nombre_comercial LIKE :busqueda
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

        $sql .= " GROUP BY p.pr_id, p.pr_razon_social, p.pr_nit, p.pr_telefono, p.pr_correo, p.pr_nombre_comercial, p.pr_creado_en, p.pr_estado ORDER BY p.pr_razon_social ASC";

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
                pr_razon_social,
                pr_nit,
                pr_telefono,
                pr_correo,
                pr_nombre_comercial,
                pr_estado
            ) VALUES (
                :nombres,
                :nit,
                :telefono,
                :correo,
                :direccion,
                1
            )
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombres', $datos['nombres']);
        $stmt->bindParam(':nit', $datos['nit']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
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
                pr_razon_social = :nombres,
                pr_nit = :nit,
                pr_telefono = :telefono,
                pr_correo = :correo,
                pr_nombre_comercial = :direccion,
                pr_actualizado_en = NOW()
            WHERE pr_id = :pr_id
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombres', $datos['nombres']);
        $stmt->bindParam(':nit', $datos['nit']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':correo', $datos['correo']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':pr_id', $datos['pr_id'], PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
