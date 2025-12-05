<?php

require_once "mainModel.php";

class cajaModel extends mainModel
{
    protected static function listar_cajas_model($estado = '', $busqueda = '', $pagina = 1, $registros = 10)
    {
        $offset = ($pagina - 1) * $registros;

        $sql = "
            SELECT 
                c.caja_id,
                c.caja_nombre,
                c.caja_saldo_inicial,
                c.caja_saldo_final,
                c.caja_creado_en,
                c.caja_cerrado_en,
                c.caja_activa,
                c.caja_observacion,
                u.us_id,
                u.us_nombres,
                u.us_apellido_paterno,
                u.us_apellido_materno,
                s.su_id,
                s.su_nombre,
                COALESCE(SUM(CASE WHEN mc.mc_tipo = 'venta' THEN mc.mc_monto ELSE 0 END), 0) AS total_ventas,
                COALESCE(SUM(CASE WHEN mc.mc_tipo IN ('ingreso', 'venta') THEN mc.mc_monto ELSE 0 END), 0) AS total_ingresos,
                COALESCE(SUM(CASE WHEN mc.mc_tipo IN ('egreso', 'compra', 'ajuste') THEN mc.mc_monto ELSE 0 END), 0) AS total_egresos
            FROM caja c
            INNER JOIN usuarios u ON c.us_id = u.us_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            LEFT JOIN movimiento_caja mc ON c.caja_id = mc.caja_id
            WHERE 1=1
        ";

        $params = [];

        if ($estado !== '') {
            if ($estado === 'abierta') {
                $sql .= " AND c.caja_activa = 1 AND c.caja_cerrado_en IS NULL";
            } elseif ($estado === 'cerrada') {
                $sql .= " AND (c.caja_activa = 0 OR c.caja_cerrado_en IS NOT NULL)";
            }
        }

        if (!empty($busqueda)) {
            $sql .= " AND (c.caja_nombre LIKE :busqueda OR u.us_nombres LIKE :busqueda OR u.us_apellido_paterno LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= " GROUP BY c.caja_id
                 ORDER BY c.caja_creado_en DESC
                 LIMIT :offset, :registros";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    protected static function contar_cajas_model($estado = '', $busqueda = '')
    {
        $sql = "
            SELECT COUNT(DISTINCT c.caja_id) AS total
            FROM caja c
            INNER JOIN usuarios u ON c.us_id = u.us_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            WHERE 1=1
        ";

        $params = [];

        if ($estado !== '') {
            if ($estado === 'abierta') {
                $sql .= " AND c.caja_activa = 1 AND c.caja_cerrado_en IS NULL";
            } elseif ($estado === 'cerrada') {
                $sql .= " AND (c.caja_activa = 0 OR c.caja_cerrado_en IS NOT NULL)";
            }
        }

        if (!empty($busqueda)) {
            $sql .= " AND (c.caja_nombre LIKE :busqueda OR u.us_nombres LIKE :busqueda OR u.us_apellido_paterno LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_caja_model($caja_id)
    {
        $sql = "
            SELECT 
                c.*,
                u.us_nombres,
                u.us_apellido_paterno,
                u.us_apellido_materno,
                s.su_nombre
            FROM caja c
            INNER JOIN usuarios u ON c.us_id = u.us_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            WHERE c.caja_id = :caja_id
            LIMIT 1
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':caja_id', $caja_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function cerrar_caja_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            UPDATE caja
            SET caja_saldo_final = :caja_saldo_final,
                caja_cerrado_en = :caja_cerrado_en,
                caja_activa = 0,
                caja_observacion = :caja_observacion
            WHERE caja_id = :caja_id
        ");
        $stmt->bindParam(":caja_saldo_final", $datos['caja_saldo_final']);
        $stmt->bindParam(":caja_cerrado_en", $datos['caja_cerrado_en']);
        $stmt->bindParam(":caja_id", $datos['caja_id']);
        $stmt->bindParam(":caja_observacion", $datos['caja_observacion']);
        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_movimientos_caja_model($caja_id)
    {
        $sql = "
            SELECT 
                COALESCE(SUM(CASE WHEN mc_tipo = 'venta' THEN mc_monto ELSE 0 END), 0) AS total_ventas,
                COALESCE(SUM(CASE WHEN mc_tipo IN ('ingreso', 'venta') THEN mc_monto ELSE 0 END), 0) AS total_ingresos,
                COALESCE(SUM(CASE WHEN mc_tipo IN ('egreso', 'compra', 'ajuste') THEN mc_monto ELSE 0 END), 0) AS total_egresos,
                COUNT(*) AS total_movimientos
            FROM movimiento_caja
            WHERE caja_id = :caja_id
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':caja_id', $caja_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_cajas_cerradas_model($su_id = null, $pagina = 1, $registros = 10, $busqueda = '')
    {
        $offset = ($pagina - 1) * $registros;

        $sql = "
            SELECT 
                c.caja_id,
                c.caja_nombre,
                c.caja_saldo_inicial,
                c.caja_saldo_final,
                c.caja_creado_en,
                c.caja_cerrado_en,
                u.us_id,
                u.us_nombres,
                u.us_apellido_paterno,
                u.us_apellido_materno,
                s.su_id,
                s.su_nombre,
                (c.caja_saldo_final - c.caja_saldo_inicial) AS diferencia_arqueo,
                COALESCE(SUM(CASE WHEN mc.mc_tipo = 'venta' THEN mc.mc_monto ELSE 0 END), 0) AS total_ventas,
                COALESCE(SUM(CASE WHEN mc.mc_tipo IN ('ingreso', 'venta') THEN mc.mc_monto ELSE 0 END), 0) AS total_ingresos
            FROM caja c
            INNER JOIN usuarios u ON c.us_id = u.us_id
            INNER JOIN sucursales s ON c.su_id = s.su_id
            LEFT JOIN movimiento_caja mc ON c.caja_id = mc.caja_id
            WHERE c.caja_cerrado_en IS NOT NULL
        ";

        $params = [];

        if ($su_id !== null && $su_id > 0) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$su_id;
        }

        if (!empty($busqueda)) {
            $sql .= " AND (c.caja_nombre LIKE :busqueda OR u.us_nombres LIKE :busqueda OR u.us_apellido_paterno LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= " GROUP BY c.caja_id
                 ORDER BY c.caja_cerrado_en DESC
                 LIMIT :offset, :registros";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    protected static function contar_cajas_cerradas_model($su_id = null, $busqueda = '')
    {
        $sql = "
            SELECT COUNT(DISTINCT c.caja_id) AS total
            FROM caja c
            INNER JOIN usuarios u ON c.us_id = u.us_id
            WHERE c.caja_cerrado_en IS NOT NULL
        ";

        $params = [];

        if ($su_id !== null && $su_id > 0) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$su_id;
        }

        if (!empty($busqueda)) {
            $sql .= " AND (c.caja_nombre LIKE :busqueda OR u.us_nombres LIKE :busqueda OR u.us_apellido_paterno LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_resumen_cajas_cerradas_model($su_id = null)
    {
        $sql = "
            SELECT 
                COUNT(c.caja_id) AS total_cajas_cerradas,
                COALESCE(SUM(c.caja_saldo_inicial), 0) AS total_saldos_iniciales,
                COALESCE(SUM(c.caja_saldo_final), 0) AS total_saldos_finales,
                COALESCE(SUM(c.caja_saldo_final - c.caja_saldo_inicial), 0) AS total_diferencia,
                COALESCE(SUM(CASE WHEN mc.mc_tipo = 'venta' THEN mc.mc_monto ELSE 0 END), 0) AS total_ventas_period
            FROM caja c
            LEFT JOIN movimiento_caja mc ON c.caja_id = mc.caja_id
            WHERE c.caja_cerrado_en IS NOT NULL
        ";

        $params = [];

        if ($su_id !== null && $su_id > 0) {
            $sql .= " AND c.su_id = :su_id";
            $params[':su_id'] = (int)$su_id;
        }

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_ventas_por_usuario_model($caja_id = null)
    {
        $sql = "
            SELECT 
                u.us_id,
                u.us_nombres,
                u.us_apellido_paterno,
                COUNT(DISTINCT CASE WHEN mc.mc_tipo = 'venta' THEN mc.mc_referencia_id END) AS total_ventas,
                COALESCE(SUM(CASE WHEN mc.mc_tipo = 'venta' THEN mc.mc_monto ELSE 0 END), 0) AS monto_ventas
            FROM usuarios u
            LEFT JOIN movimiento_caja mc ON u.us_id = mc.us_id AND mc.mc_tipo = 'venta'
        ";

        $params = [];

        if ($caja_id !== null && $caja_id > 0) {
            $sql .= " LEFT JOIN caja c ON mc.caja_id = c.caja_id
                     WHERE c.caja_id = :caja_id";
            $params[':caja_id'] = (int)$caja_id;
        } else {
            $sql .= " WHERE u.us_estado = 1";
        }

        $sql .= " GROUP BY u.us_id
                 ORDER BY monto_ventas DESC";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }
}
