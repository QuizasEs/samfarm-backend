<?php

require_once "mainModel.php";

class compraHistorialModel extends mainModel
{

    protected static function datos_compras_historial_model($inicio, $registros, $filtros)
    {
        $whereParts = [];

        if (isset($filtros['su_id'])) {
            $whereParts[] = "c.su_id = '" . $filtros['su_id'] . "'";
        }

        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $whereParts[] = "(
                c.co_numero LIKE '%$busqueda%' OR
                c.co_numero_factura LIKE '%$busqueda%' OR
                c.co_nit_proveedor LIKE '%$busqueda%' OR
                p.pr_nombres LIKE '%$busqueda%'
            )";
        }

        if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.co_fecha) >= '" . $filtros['fecha_desde'] . "'";
        }

        if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.co_fecha) <= '" . $filtros['fecha_hasta'] . "'";
        }

        if (isset($filtros['proveedor']) && $filtros['proveedor'] != '') {
            $whereParts[] = "c.pr_id = '" . $filtros['proveedor'] . "'";
        }

        if (isset($filtros['laboratorio']) && $filtros['laboratorio'] != '') {
            $whereParts[] = "c.la_id = '" . $filtros['laboratorio'] . "'";
        }

        if (isset($filtros['usuario']) && $filtros['usuario'] != '') {
            $whereParts[] = "c.us_id = '" . $filtros['usuario'] . "'";
        }

        if (isset($filtros['estado_lotes'])) {
            $estado = $filtros['estado_lotes'];
            if ($estado === 'pendientes') {
                $whereParts[] = "EXISTS (
                    SELECT 1 FROM lote_medicamento lm 
                    WHERE lm.pr_id_compra = c.co_id 
                    AND lm.lm_estado = 'en_espera'
                )";
            } elseif ($estado === 'activos') {
                $whereParts[] = "EXISTS (
                    SELECT 1 FROM lote_medicamento lm 
                    WHERE lm.pr_id_compra = c.co_id 
                    AND lm.lm_estado = 'activo'
                )";
            } elseif ($estado === 'completado') {
                $whereParts[] = "NOT EXISTS (
                    SELECT 1 FROM lote_medicamento lm 
                    WHERE lm.pr_id_compra = c.co_id 
                    AND lm.lm_estado = 'en_espera'
                )";
            }
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";

        $sql = "SELECT SQL_CALC_FOUND_ROWS
                c.co_id,
                c.co_numero,
                c.co_fecha,
                c.co_numero_factura,
                c.co_fecha_factura,
                c.co_total,
                c.co_nit_proveedor,
                CONCAT(COALESCE(p.pr_nombres, ''), ' ', COALESCE(p.pr_apellido_paterno, '')) AS proveedor_nombre,
                p.pr_nit AS proveedor_nit,
                l.la_nombre_comercial AS laboratorio,
                s.su_nombre AS sucursal,
                CONCAT(u.us_nombres, ' ', u.us_apellido_paterno) AS usuario_nombre,
                (SELECT COUNT(*) FROM detalle_compra dc WHERE dc.co_id = c.co_id) AS total_items,
                (SELECT COUNT(*) FROM lote_medicamento lm WHERE lm.pr_id_compra = c.co_id) AS total_lotes,
                (SELECT COUNT(*) FROM lote_medicamento lm WHERE lm.pr_id_compra = c.co_id AND lm.lm_estado = 'en_espera') AS lotes_pendientes
            FROM compras c
            LEFT JOIN proveedores p ON c.pr_id = p.pr_id
            LEFT JOIN laboratorios l ON c.la_id = l.la_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            INNER JOIN usuarios u ON c.us_id = u.us_id
            $whereSQL
            ORDER BY c.co_fecha DESC
            LIMIT $inicio, $registros";

        $conexion = mainModel::conectar();
        return $conexion->query($sql);
    }

    protected static function contar_compras_historial_model($filtros)
    {
        $whereParts = [];

        if (isset($filtros['su_id'])) {
            $whereParts[] = "c.su_id = '" . $filtros['su_id'] . "'";
        }

        if (isset($filtros['busqueda']) && !empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $whereParts[] = "(
                c.co_numero LIKE '%$busqueda%' OR
                c.co_numero_factura LIKE '%$busqueda%' OR
                c.co_nit_proveedor LIKE '%$busqueda%' OR
                p.pr_nombres LIKE '%$busqueda%'
            )";
        }

        if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.co_fecha) >= '" . $filtros['fecha_desde'] . "'";
        }

        if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.co_fecha) <= '" . $filtros['fecha_hasta'] . "'";
        }

        if (isset($filtros['proveedor']) && $filtros['proveedor'] != '') {
            $whereParts[] = "c.pr_id = '" . $filtros['proveedor'] . "'";
        }

        if (isset($filtros['laboratorio']) && $filtros['laboratorio'] != '') {
            $whereParts[] = "c.la_id = '" . $filtros['laboratorio'] . "'";
        }

        if (isset($filtros['usuario']) && $filtros['usuario'] != '') {
            $whereParts[] = "c.us_id = '" . $filtros['usuario'] . "'";
        }

        if (isset($filtros['estado_lotes'])) {
            $estado = $filtros['estado_lotes'];
            if ($estado === 'pendientes') {
                $whereParts[] = "EXISTS (
                    SELECT 1 FROM lote_medicamento lm 
                    WHERE lm.pr_id_compra = c.co_id 
                    AND lm.lm_estado = 'en_espera'
                )";
            } elseif ($estado === 'activos') {
                $whereParts[] = "EXISTS (
                    SELECT 1 FROM lote_medicamento lm 
                    WHERE lm.pr_id_compra = c.co_id 
                    AND lm.lm_estado = 'activo'
                )";
            } elseif ($estado === 'completado') {
                $whereParts[] = "NOT EXISTS (
                    SELECT 1 FROM lote_medicamento lm 
                    WHERE lm.pr_id_compra = c.co_id 
                    AND lm.lm_estado = 'en_espera'
                )";
            }
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";

        $sql = "SELECT COUNT(*) as total
            FROM compras c
            LEFT JOIN proveedores p ON c.pr_id = p.pr_id
            $whereSQL";

        $conexion = mainModel::conectar();
        $result = $conexion->query($sql);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    protected static function detalle_compra_completo_model($co_id)
    {
        $sql = "SELECT 
                c.*,
                CONCAT(COALESCE(p.pr_nombres, ''), ' ', COALESCE(p.pr_apellido_paterno, '')) AS proveedor_nombre,
                p.pr_nit AS proveedor_nit,
                p.pr_direccion AS proveedor_direccion,
                p.pr_telefono AS proveedor_telefono,
                l.la_nombre_comercial AS laboratorio,
                s.su_nombre AS sucursal,
                CONCAT(u.us_nombres, ' ', u.us_apellido_paterno) AS usuario_nombre
            FROM compras c
            LEFT JOIN proveedores p ON c.pr_id = p.pr_id
            LEFT JOIN laboratorios l ON c.la_id = l.la_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            INNER JOIN usuarios u ON c.us_id = u.us_id
            WHERE c.co_id = :co_id";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':co_id', $co_id);
        $stmt->execute();
        return $stmt;
    }

    protected static function detalle_medicamentos_compra_model($co_id)
    {
        $sql = "SELECT 
                dc.*,
                m.med_nombre_quimico,
                m.med_principio_activo,
                lm.lm_numero_lote,
                lm.lm_estado,
                lm.lm_fecha_vencimiento
            FROM detalle_compra dc
            INNER JOIN medicamento m ON dc.med_id = m.med_id
            LEFT JOIN lote_medicamento lm ON dc.lm_id = lm.lm_id
            WHERE dc.co_id = :co_id
            ORDER BY dc.dc_id";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':co_id', $co_id);
        $stmt->execute();
        return $stmt;
    }

    protected static function resumen_lotes_compra_model($co_id)
    {
        $sql = "SELECT 
                COUNT(*) as total_lotes,
                SUM(CASE WHEN lm_estado = 'activo' THEN 1 ELSE 0 END) as lotes_activos,
                SUM(CASE WHEN lm_estado = 'en_espera' THEN 1 ELSE 0 END) as lotes_espera
            FROM lote_medicamento
            WHERE pr_id_compra = :co_id";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':co_id', $co_id);
        $stmt->execute();
        return $stmt;
    }

    protected static function datos_grafico_compras_model($filtros)
    {
        $whereParts = [];

        if (isset($filtros['su_id'])) {
            $whereParts[] = "c.su_id = '" . $filtros['su_id'] . "'";
        }

        if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.co_fecha) >= '" . $filtros['fecha_desde'] . "'";
        }

        if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.co_fecha) <= '" . $filtros['fecha_hasta'] . "'";
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";

        $sql = "SELECT 
                CONCAT(COALESCE(p.pr_nombres, ''), ' ', COALESCE(p.pr_apellido_paterno, '')) AS proveedor,
                COUNT(*) as cantidad_compras,
                ROUND(AVG(c.co_total), 2) as ticket_promedio,
                SUM(c.co_total) as total_monto
            FROM compras c
            LEFT JOIN proveedores p ON c.pr_id = p.pr_id
            $whereSQL
            GROUP BY c.pr_id
            ORDER BY cantidad_compras DESC
            LIMIT 10";

        $conexion = mainModel::conectar();
        return $conexion->query($sql);
    }
    protected static function exportar_compras_pdf_model($filtros)
    {
        $whereParts = [];

        if (isset($filtros['su_id'])) {
            $whereParts[] = "c.su_id = '" . $filtros['su_id'] . "'";
        }

        if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.co_fecha) >= '" . $filtros['fecha_desde'] . "'";
        }

        if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.co_fecha) <= '" . $filtros['fecha_hasta'] . "'";
        }

        if (isset($filtros['proveedor']) && $filtros['proveedor'] != '') {
            $whereParts[] = "c.pr_id = '" . $filtros['proveedor'] . "'";
        }

        if (isset($filtros['laboratorio']) && $filtros['laboratorio'] != '') {
            $whereParts[] = "c.la_id = '" . $filtros['laboratorio'] . "'";
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";

        $sql = "SELECT 
                c.co_numero,
                DATE_FORMAT(c.co_fecha, '%d/%m/%Y') as fecha_compra,
                CONCAT(COALESCE(p.pr_nombres, ''), ' ', COALESCE(p.pr_apellido_paterno, '')) AS proveedor,
                l.la_nombre_comercial AS laboratorio,
                s.su_nombre AS sucursal,
                (SELECT COUNT(*) FROM detalle_compra dc WHERE dc.co_id = c.co_id) AS items,
                (SELECT COUNT(*) FROM lote_medicamento lm WHERE lm.pr_id_compra = c.co_id) AS lotes,
                c.co_numero_factura,
                c.co_total
            FROM compras c
            LEFT JOIN proveedores p ON c.pr_id = p.pr_id
            LEFT JOIN laboratorios l ON c.la_id = l.la_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            $whereSQL
            ORDER BY c.co_fecha DESC";

        $conexion = mainModel::conectar();
        return $conexion->query($sql);
    }

    protected static function exportar_compras_excel_model($filtros)
    {
        $whereParts = [];

        if (isset($filtros['su_id'])) {
            $whereParts[] = "c.su_id = '" . $filtros['su_id'] . "'";
        }

        if (isset($filtros['fecha_desde']) && !empty($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.co_fecha) >= '" . $filtros['fecha_desde'] . "'";
        }

        if (isset($filtros['fecha_hasta']) && !empty($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.co_fecha) <= '" . $filtros['fecha_hasta'] . "'";
        }

        if (isset($filtros['proveedor']) && $filtros['proveedor'] != '') {
            $whereParts[] = "c.pr_id = '" . $filtros['proveedor'] . "'";
        }

        if (isset($filtros['laboratorio']) && $filtros['laboratorio'] != '') {
            $whereParts[] = "c.la_id = '" . $filtros['laboratorio'] . "'";
        }

        if (isset($filtros['usuario']) && $filtros['usuario'] != '') {
            $whereParts[] = "c.us_id = '" . $filtros['usuario'] . "'";
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";

        $sql = "SELECT 
                c.co_numero AS 'N° Compra',
                DATE_FORMAT(c.co_fecha, '%d/%m/%Y') AS 'Fecha Compra',
                CONCAT(COALESCE(p.pr_nombres, ''), ' ', COALESCE(p.pr_apellido_paterno, '')) AS 'Proveedor',
                p.pr_nit AS 'NIT Proveedor',
                l.la_nombre_comercial AS 'Laboratorio',
                s.su_nombre AS 'Sucursal',
                CONCAT(u.us_nombres, ' ', u.us_apellido_paterno) AS 'Usuario',
                (SELECT COUNT(*) FROM detalle_compra dc WHERE dc.co_id = c.co_id) AS 'Items',
                (SELECT COUNT(*) FROM lote_medicamento lm WHERE lm.pr_id_compra = c.co_id) AS 'Total Lotes',
                (SELECT COUNT(*) FROM lote_medicamento lm WHERE lm.pr_id_compra = c.co_id AND lm.lm_estado = 'activo') AS 'Lotes Activos',
                (SELECT COUNT(*) FROM lote_medicamento lm WHERE lm.pr_id_compra = c.co_id AND lm.lm_estado = 'en_espera') AS 'Lotes Pendientes',
                c.co_numero_factura AS 'N° Factura',
                DATE_FORMAT(c.co_fecha_factura, '%d/%m/%Y') AS 'Fecha Factura',
                c.co_subtotal AS 'Subtotal (Bs)',
                c.co_impuesto AS 'Impuestos (Bs)',
                c.co_total AS 'Total (Bs)',
                DATE_FORMAT(c.co_creado_en, '%d/%m/%Y %H:%i') AS 'Registrado el'
            FROM compras c
            LEFT JOIN proveedores p ON c.pr_id = p.pr_id
            LEFT JOIN laboratorios l ON c.la_id = l.la_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            INNER JOIN usuarios u ON c.us_id = u.us_id
            $whereSQL
            ORDER BY c.co_fecha DESC";

        $conexion = mainModel::conectar();
        return $conexion->query($sql);
    }
    protected static function exportar_compra_detalle_pdf_model($co_id)
    {
        $conexion = mainModel::conectar();

        $sql = "SELECT 
                c.*,
                CONCAT(COALESCE(p.pr_nombres, ''), ' ', COALESCE(p.pr_apellido_paterno, '')) AS proveedor_nombre,
                p.pr_nit AS proveedor_nit,
                l.la_nombre_comercial AS laboratorio,
                s.su_nombre AS sucursal,
                CONCAT(u.us_nombres, ' ', u.us_apellido_paterno) AS usuario_nombre
            FROM compras c
            LEFT JOIN proveedores p ON c.pr_id = p.pr_id
            LEFT JOIN laboratorios l ON c.la_id = l.la_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            INNER JOIN usuarios u ON c.us_id = u.us_id
            WHERE c.co_id = :co_id";

        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':co_id', $co_id);
        $stmt->execute();
        $compra = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$compra) {
            return null;
        }

        $sql_detalles = "SELECT 
                dc.*,
                m.med_nombre_quimico,
                m.med_principio_activo,
                lm.lm_numero_lote,
                lm.lm_estado,
                lm.lm_fecha_vencimiento
            FROM detalle_compra dc
            INNER JOIN medicamento m ON dc.med_id = m.med_id
            LEFT JOIN lote_medicamento lm ON dc.lm_id = lm.lm_id
            WHERE dc.co_id = :co_id
            ORDER BY dc.dc_id";

        $stmt_detalles = $conexion->prepare($sql_detalles);
        $stmt_detalles->bindParam(':co_id', $co_id);
        $stmt_detalles->execute();
        $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

        return [
            'compra' => $compra,
            'detalles' => $detalles
        ];
    }

    protected static function datos_orden_compra_modelo($co_id)
    {
        $conexion = mainModel::conectar();

        // Datos principales de la compra
        $sql_compra = "SELECT 
            c.*,
            CONCAT(COALESCE(p.pr_nombres, ''), ' ', COALESCE(p.pr_apellido_paterno, '')) AS proveedor_nombre,
            p.pr_nit AS proveedor_nit,
            p.pr_direccion AS proveedor_direccion,
            p.pr_telefono AS proveedor_telefono,
            l.la_nombre_comercial AS laboratorio,
            s.su_nombre AS sucursal,
            CONCAT(u.us_nombres, ' ', u.us_apellido_paterno) AS usuario_nombre
        FROM compras c
        LEFT JOIN proveedores p ON c.pr_id = p.pr_id
        LEFT JOIN laboratorios l ON c.la_id = l.la_id
        INNER JOIN sucursales s ON c.su_id = s.su_id
        INNER JOIN usuarios u ON c.us_id = u.us_id
        WHERE c.co_id = :co_id";

        $stmt_compra = $conexion->prepare($sql_compra);
        $stmt_compra->bindParam(':co_id', $co_id);
        $stmt_compra->execute();
        $compra = $stmt_compra->fetch(PDO::FETCH_ASSOC);

        if (!$compra) {
            return [];
        }

        // Detalles de medicamentos de la compra
        $sql_detalles = "SELECT 
            dc.*,
            m.med_nombre_quimico,
            m.med_principio_activo,
            lm.lm_numero_lote,
            lm.lm_estado,
            lm.lm_fecha_vencimiento
        FROM detalle_compra dc
        INNER JOIN medicamento m ON dc.med_id = m.med_id
        LEFT JOIN lote_medicamento lm ON dc.lm_id = lm.lm_id
        WHERE dc.co_id = :co_id
        ORDER BY dc.dc_id";

        $stmt_detalles = $conexion->prepare($sql_detalles);
        $stmt_detalles->bindParam(':co_id', $co_id);
        $stmt_detalles->execute();
        $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

        return [
            'compra' => $compra,
            'detalles' => $detalles
        ];
    }
}
