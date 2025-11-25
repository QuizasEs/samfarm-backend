<?php

require_once "mainModel.php";

class cajaHistorialModel extends mainModel
{
    protected static function datos_historial_caja_model($inicio, $registros, $filtros = [])
    {
        $sql = "
            SELECT 
                mc.mc_id,
                mc.mc_fecha,
                mc.mc_tipo,
                mc.mc_monto,
                mc.mc_concepto,
                mc.mc_referencia_tipo,
                mc.mc_referencia_id,
                c.caja_nombre,
                s.su_nombre,
                u.us_nombres,
                u.us_apellido_paterno
            FROM movimiento_caja mc
            INNER JOIN caja c ON c.caja_id = mc.caja_id
            INNER JOIN sucursales s ON s.su_id = c.su_id
            LEFT JOIN usuarios u ON u.us_id = mc.us_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                mc.mc_concepto LIKE :busqueda OR
                CONCAT(mc.mc_referencia_tipo, ' #', mc.mc_referencia_id) LIKE :busqueda
            )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['caja_id'])) {
            $sql .= " AND mc.caja_id = :caja_id";
            $params[':caja_id'] = (int)$filtros['caja_id'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND mc.mc_tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['usuario'])) {
            $sql .= " AND mc.us_id = :usuario";
            $params[':usuario'] = (int)$filtros['usuario'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(mc.mc_fecha) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(mc.mc_fecha) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " ORDER BY mc.mc_fecha DESC LIMIT :inicio, :registros";

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

    protected static function contar_historial_caja_model($filtros = [])
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM movimiento_caja mc
            INNER JOIN caja c ON c.caja_id = mc.caja_id
            INNER JOIN sucursales s ON s.su_id = c.su_id
            LEFT JOIN usuarios u ON u.us_id = mc.us_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                mc.mc_concepto LIKE :busqueda OR
                CONCAT(mc.mc_referencia_tipo, ' #', mc.mc_referencia_id) LIKE :busqueda
            )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['caja_id'])) {
            $sql .= " AND mc.caja_id = :caja_id";
            $params[':caja_id'] = (int)$filtros['caja_id'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND mc.mc_tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['usuario'])) {
            $sql .= " AND mc.us_id = :usuario";
            $params[':usuario'] = (int)$filtros['usuario'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(mc.mc_fecha) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(mc.mc_fecha) <= :fecha_hasta";
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

    protected static function obtener_resumen_periodo_model($filtros = [])
    {
        $sql = "
            SELECT 
                SUM(CASE WHEN mc.mc_tipo IN ('ingreso', 'venta') THEN mc.mc_monto ELSE 0 END) as total_ingresos,
                SUM(CASE WHEN mc.mc_tipo IN ('egreso', 'compra') THEN mc.mc_monto ELSE 0 END) as total_egresos
                FROM movimiento_caja mc
                INNER JOIN caja c ON c.caja_id = mc.caja_id
                WHERE 1=1
                ";
        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['caja_id'])) {
            $sql .= " AND mc.caja_id = :caja_id";
            $params[':caja_id'] = (int)$filtros['caja_id'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND mc.mc_tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['usuario'])) {
            $sql .= " AND mc.us_id = :usuario";
            $params[':usuario'] = (int)$filtros['usuario'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(mc.mc_fecha) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(mc.mc_fecha) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        $total_ingresos = (float)($resultado['total_ingresos'] ?? 0);
        $total_egresos = (float)($resultado['total_egresos'] ?? 0);
        $balance = $total_ingresos - $total_egresos;

        return [
            'total_ingresos' => $total_ingresos,
            'total_egresos' => $total_egresos,
            'balance' => $balance
        ];
    }

    protected static function obtener_datos_grafico_model($filtros = [])
    {
        $sql = "
            SELECT 
                DATE(mc.mc_fecha) as fecha,
                mc.mc_tipo,
                SUM(mc.mc_monto) as total
            FROM movimiento_caja mc
            INNER JOIN caja c ON c.caja_id = mc.caja_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(mc.mc_fecha) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(mc.mc_fecha) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " GROUP BY DATE(mc.mc_fecha), mc.mc_tipo ORDER BY fecha ASC";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    protected static function exportar_historial_caja_excel_model($filtros = [])
    {
        $sql = "
                SELECT 
                    s.su_nombre AS 'Sucursal',
                    c.caja_nombre AS 'Caja',
                    mc.mc_fecha AS 'Fecha',
                    UPPER(mc.mc_tipo) AS 'Tipo',
                    mc.mc_concepto AS 'Concepto',
                    CONCAT(UPPER(mc.mc_referencia_tipo), ' #', mc.mc_referencia_id) AS 'Referencia',
                    CONCAT(u.us_nombres, ' ', COALESCE(u.us_apellido_paterno, '')) AS 'Usuario',
                    mc.mc_monto AS 'Monto'
                FROM movimiento_caja mc
                INNER JOIN caja c ON c.caja_id = mc.caja_id
                INNER JOIN sucursales s ON s.su_id = c.su_id
                LEFT JOIN usuarios u ON u.us_id = mc.us_id
                WHERE 1=1
            ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['caja_id'])) {
            $sql .= " AND mc.caja_id = :caja_id";
            $params[':caja_id'] = (int)$filtros['caja_id'];
        }

        if (!empty($filtros['tipo'])) {
            $sql .= " AND mc.mc_tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }

        if (!empty($filtros['usuario'])) {
            $sql .= " AND mc.us_id = :usuario";
            $params[':usuario'] = (int)$filtros['usuario'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND DATE(mc.mc_fecha) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(mc.mc_fecha) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " ORDER BY mc.mc_fecha DESC";

        $stmt = mainModel::conectar()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }
    
    protected static function obtener_movimiento_individual_model($mc_id)
    {
        $sql = "
            SELECT 
                mc.mc_id,
                mc.mc_fecha,
                mc.mc_tipo,
                mc.mc_concepto,
                mc.mc_monto,
                mc.mc_referencia_tipo,
                mc.mc_referencia_id,
                
                c.caja_nombre,
                s.su_nombre,
                
                CONCAT(u.us_nombre, ' ', u.us_apellido) AS usuario_completo
                
            FROM movimiento_caja AS mc
            INNER JOIN caja AS c ON mc.caja_id = c.caja_id
            INNER JOIN sucursal AS s ON c.su_id = s.su_id
            INNER JOIN usuario AS u ON mc.us_id = u.us_id
            WHERE mc.mc_id = :id
            LIMIT 1
        ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(":id", $mc_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }
}
