<?php

require_once "mainModel.php";

class ventasHistorialModel extends mainModel
{
    protected static function datos_ventas_historial_model($inicio, $registros, $filtros = [])
    {
        $sql = "
                SELECT 
                    v.ve_id,
                    v.ve_numero_documento,
                    v.ve_fecha_emision,
                    v.ve_total,
                    v.ve_subtotal,
                    v.ve_impuesto,
                    v.ve_tipo_documento,
                    v.ve_estado_documento,
                    COALESCE(CONCAT_WS(' ', c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno), '') AS cliente_nombre,
                    COALESCE(c.cl_carnet, '') AS cl_carnet,
                    CONCAT_WS(' ', u.us_nombres, u.us_apellido_paterno, u.us_apellido_materno) AS vendedor_nombre,
                    s.su_nombre AS sucursal_nombre,
                    COALESCE(cj.caja_nombre, '') AS caja_nombre,
                    COALESCE(f.fa_id, 0) AS fa_id,
                    COALESCE(f.fa_numero, '') AS fa_numero,
                    (SELECT COUNT(*) FROM detalle_venta dv WHERE dv.ve_id = v.ve_id AND dv.dv_estado = 1) AS cantidad_items
                FROM ventas v
                INNER JOIN usuarios u ON u.us_id = v.us_id
                INNER JOIN sucursales s ON s.su_id = v.su_id
                LEFT JOIN clientes c ON c.cl_id = v.cl_id
                LEFT JOIN caja cj ON cj.caja_id = v.caja_id
                LEFT JOIN factura f ON f.ve_id = v.ve_id
                WHERE v.ve_estado = 1
            ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND v.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(v.ve_fecha_emision) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(v.ve_fecha_emision) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['cliente'])) {
            $sql .= " AND v.cl_id = :cliente";
            $params[':cliente'] = (int)$filtros['cliente'];
        }

        if (!empty($filtros['vendedor'])) {
            $sql .= " AND v.us_id = :vendedor";
            $params[':vendedor'] = (int)$filtros['vendedor'];
        }

        if (!empty($filtros['tipo_documento'])) {
            $sql .= " AND v.ve_tipo_documento = :tipo_documento";
            $params[':tipo_documento'] = $filtros['tipo_documento'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                    v.ve_numero_documento LIKE :busqueda OR
                    CONCAT_WS(' ', c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno) LIKE :busqueda OR
                    CONCAT_WS(' ', u.us_nombres, u.us_apellido_paterno, u.us_apellido_materno) LIKE :busqueda
                )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        $sql .= " ORDER BY v.ve_fecha_emision DESC LIMIT :inicio, :registros";

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

    /**
     * Contar total de ventas con filtros
     */
    protected static function contar_ventas_historial_model($filtros = [])
    {
        $sql = "
                SELECT COUNT(*) as total
                FROM ventas v
                INNER JOIN usuarios u ON u.us_id = v.us_id
                INNER JOIN sucursales s ON s.su_id = v.su_id
                LEFT JOIN clientes c ON c.cl_id = v.cl_id
                WHERE v.ve_estado = 1
            ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND v.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(v.ve_fecha_emision) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(v.ve_fecha_emision) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['cliente'])) {
            $sql .= " AND v.cl_id = :cliente";
            $params[':cliente'] = (int)$filtros['cliente'];
        }

        if (!empty($filtros['vendedor'])) {
            $sql .= " AND v.us_id = :vendedor";
            $params[':vendedor'] = (int)$filtros['vendedor'];
        }

        if (!empty($filtros['tipo_documento'])) {
            $sql .= " AND v.ve_tipo_documento = :tipo_documento";
            $params[':tipo_documento'] = $filtros['tipo_documento'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                    v.ve_numero_documento LIKE :busqueda OR
                    CONCAT_WS(' ', c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno) LIKE :busqueda OR
                    CONCAT_WS(' ', u.us_nombres, u.us_apellido_paterno, u.us_apellido_materno) LIKE :busqueda
                )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
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

    /**
     * Obtener detalle completo de una venta
     */
    protected static function detalle_venta_completo_model($ve_id)
    {
        $sql = "
            SELECT 
                v.*,
                
                -- Cliente
                c.cl_nombres,
                c.cl_apellido_paterno,
                c.cl_apellido_materno,
                c.cl_carnet,
                c.cl_telefono,
                c.cl_direccion,
                
                -- Usuario/Vendedor
                u.us_nombres,
                u.us_apellido_paterno,
                u.us_apellido_materno,
                
                -- Sucursal
                s.su_nombre,
                s.su_direccion,
                s.su_telefono,
                
                -- Caja
                cj.caja_nombre,
                
                -- Factura
                f.fa_id,
                f.fa_numero,
                f.fa_codigo_control,
                f.fa_cuf
                
            FROM ventas v
            INNER JOIN usuarios u ON u.us_id = v.us_id
            INNER JOIN sucursales s ON s.su_id = v.su_id
            LEFT JOIN clientes c ON c.cl_id = v.cl_id
            LEFT JOIN caja cj ON cj.caja_id = v.caja_id
            LEFT JOIN factura f ON f.ve_id = v.ve_id
            WHERE v.ve_id = :ve_id
            LIMIT 1
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':ve_id', $ve_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener items del detalle de venta
     */
    protected static function detalle_items_venta_model($ve_id)
    {
        $sql = "
            SELECT 
                dv.*,
                m.med_nombre_quimico,
                m.med_principio_activo,
                m.med_presentacion,
                m.med_version_comercial,
                ff.ff_nombre AS forma_farmaceutica,
                lm.lm_numero_lote
                
            FROM detalle_venta dv
            INNER JOIN medicamento m ON m.med_id = dv.med_id
            LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
            LEFT JOIN lote_medicamento lm ON lm.lm_id = dv.lm_id
            WHERE dv.ve_id = :ve_id
            ORDER BY dv.dv_id ASC
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':ve_id', $ve_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Listar clientes activos para filtro
     */
    protected static function listar_clientes_activos_model()
    {
        $sql = "
            SELECT 
                cl_id,
                CONCAT_WS(' ', cl_nombres, cl_apellido_paterno, cl_apellido_materno) AS nombre_completo
            FROM clientes
            WHERE cl_estado = 1
            ORDER BY cl_nombres ASC
        ";

        return mainModel::conectar()->query($sql);
    }

    /**
     * Listar usuarios/vendedores activos para filtro
     */
    protected static function listar_usuarios_activos_model()
    {
        $sql = "
            SELECT 
                us_id,
                CONCAT_WS(' ', us_nombres, us_apellido_paterno, us_apellido_materno) AS nombre_completo
            FROM usuarios
            WHERE us_estado = 1
            ORDER BY us_nombres ASC
        ";

        return mainModel::conectar()->query($sql);
    }

    /**
     * Obtener tipos de documento únicos
     */
    protected static function listar_tipos_documento_model()
    {
        $sql = "
            SELECT DISTINCT ve_tipo_documento
            FROM ventas
            WHERE ve_tipo_documento IS NOT NULL 
            AND ve_tipo_documento != ''
            ORDER BY ve_tipo_documento ASC
        ";

        return mainModel::conectar()->query($sql);
    }

    /**
     * Exportar historial a Excel
     */
    protected static function exportar_historial_excel_model($filtros = [])
    {
        $sql = "
            SELECT 
                v.ve_numero_documento AS 'N° Documento',
                DATE_FORMAT(v.ve_fecha_emision, '%d/%m/%Y %H:%i') AS 'Fecha',
                COALESCE(CONCAT_WS(' ', c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno), 'Sin cliente') AS 'Cliente',
                CONCAT_WS(' ', u.us_nombres, u.us_apellido_paterno) AS 'Vendedor',
                s.su_nombre AS 'Sucursal',
                (SELECT COUNT(*) FROM detalle_venta dv WHERE dv.ve_id = v.ve_id) AS 'Items',
                v.ve_subtotal AS 'Subtotal (Bs)',
                v.ve_impuesto AS 'Impuestos (Bs)',
                v.ve_total AS 'Total (Bs)',
                COALESCE(v.ve_tipo_documento, 'venta') AS 'Tipo Documento',
                f.fa_numero AS 'N° Factura'
            FROM ventas v
            INNER JOIN usuarios u ON u.us_id = v.us_id
            INNER JOIN sucursales s ON s.su_id = v.su_id
            LEFT JOIN clientes c ON c.cl_id = v.cl_id
            LEFT JOIN factura f ON f.ve_id = v.ve_id
            WHERE v.ve_estado = 1
        ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND v.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(v.ve_fecha_emision) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(v.ve_fecha_emision) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        if (!empty($filtros['cliente'])) {
            $sql .= " AND v.cl_id = :cliente";
            $params[':cliente'] = (int)$filtros['cliente'];
        }

        if (!empty($filtros['vendedor'])) {
            $sql .= " AND v.us_id = :vendedor";
            $params[':vendedor'] = (int)$filtros['vendedor'];
        }

        if (!empty($filtros['tipo_documento'])) {
            $sql .= " AND v.ve_tipo_documento = :tipo_documento";
            $params[':tipo_documento'] = $filtros['tipo_documento'];
        }

        $sql .= " ORDER BY v.ve_fecha_emision DESC";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }
}
