<?php
require_once "mainModel.php";

class sucursalModel extends mainModel
{
    protected static function listar_sucursales_model($busqueda = '', $estado = '')
    {
        $sql = "
                    SELECT 
                        s.su_id,
                        s.su_nombre,
                        s.su_direccion,
                        s.su_telefono,
                        s.su_creado_en,
                        s.su_actualizado_en,
                        s.su_estado,
                        COALESCE(
                            (SELECT COUNT(*) 
                            FROM caja c 
                            WHERE c.su_id = s.su_id 
                            AND c.caja_activa = 1 
                            AND c.caja_cerrado_en IS NULL), 
                            0
                        ) AS cajas_abiertas
                    FROM sucursales s
                    WHERE 1=1
                ";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND (s.su_nombre LIKE :busqueda OR s.su_direccion LIKE :busqueda)";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        if ($estado !== '') {
            $sql .= " AND s.su_estado = :estado";
            $params[':estado'] = (int)$estado;
        }

        $sql .= " ORDER BY s.su_estado DESC, s.su_nombre ASC";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    protected static function cajas_abiertas_model($su_id)
    {
        $sql = "
                    SELECT 
                        c.caja_id,
                        c.caja_nombre,
                        c.caja_saldo_inicial,
                        c.caja_creado_en,
                        c.caja_observacion,
                        u.us_id,
                        u.us_nombres,
                        u.us_apellido_paterno,
                        u.us_apellido_materno
                    FROM caja c
                    LEFT JOIN usuarios u ON c.us_id = u.us_id
                    WHERE c.su_id = :su_id
                    AND c.caja_activa = 1
                    AND c.caja_cerrado_en IS NULL
                    ORDER BY c.caja_creado_en DESC
                ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function usuarios_con_cajas_abiertas_model($su_id)
    {
        $sql = "
                    SELECT DISTINCT
                        u.us_id,
                        u.us_nombres,
                        u.us_apellido_paterno
                    FROM caja c
                    INNER JOIN usuarios u ON c.us_id = u.us_id
                    WHERE c.su_id = :su_id
                    AND c.caja_activa = 1
                    AND c.caja_cerrado_en IS NULL
                    ORDER BY u.us_nombres ASC
                ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function toggle_estado_model($su_id, $nuevo_estado)
    {
        $sql = "
                    UPDATE sucursales
                    SET su_estado = :nuevo_estado,
                        su_actualizado_en = NOW()
                    WHERE su_id = :su_id
                ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->bindParam(':nuevo_estado', $nuevo_estado, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function verificar_nombre_sucursal_model($nombre)
    {
        $sql = "SELECT su_id FROM sucursales WHERE su_nombre = :nombre LIMIT 1";
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        return $stmt;
    }

    protected static function verificar_nombre_sucursal_editar_model($nombre, $su_id)
    {
        $sql = "SELECT su_id FROM sucursales WHERE su_nombre = :nombre AND su_id != :su_id LIMIT 1";
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function agregar_sucursal_model($datos)
    {
        $sql = "
            INSERT INTO sucursales (su_nombre, su_direccion, su_telefono, su_creado_en, su_actualizado_en)
            VALUES (:su_nombre, :su_direccion, :su_telefono, NOW(), NOW())
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_nombre', $datos['su_nombre']);
        $stmt->bindParam(':su_direccion', $datos['su_direccion']);
        $stmt->bindParam(':su_telefono', $datos['su_telefono']);
        $stmt->execute();
        return $stmt;
    }

    protected static function actualizar_sucursal_model($datos)
    {
        $sql = "
            UPDATE sucursales
            SET su_nombre = :su_nombre,
                su_direccion = :su_direccion,
                su_telefono = :su_telefono,
                su_actualizado_en = NOW()
            WHERE su_id = :su_id
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $datos['su_id'], PDO::PARAM_INT);
        $stmt->bindParam(':su_nombre', $datos['su_nombre']);
        $stmt->bindParam(':su_direccion', $datos['su_direccion']);
        $stmt->bindParam(':su_telefono', $datos['su_telefono']);
        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_sucursal_model($su_id)
    {
        $sql = "SELECT * FROM sucursales WHERE su_id = :su_id LIMIT 1";
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function personal_por_sucursal_model($su_id)
    {
        $sql = "
            SELECT 
                u.us_id,
                u.us_nombres,
                u.us_apellido_paterno,
                u.us_apellido_materno,
                u.us_username,
                u.us_estado,
                r.ro_nombre AS rol_nombre
            FROM usuarios u
            LEFT JOIN roles r ON u.ro_id = r.ro_id
            WHERE u.su_id = :su_id
            ORDER BY u.us_nombres ASC
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function estadisticas_ventas_sucursal_model($su_id)
    {
        $sql = "
            SELECT 
                COUNT(ve_id) AS total_ventas,
                COALESCE(SUM(ve_total), 0) AS monto_total
            FROM ventas
            WHERE su_id = :su_id
              AND ve_estado = 1
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function ventas_por_mes_sucursal_model($su_id)
    {
        $sql = "
            SELECT 
                DATE_FORMAT(ve_fecha_emision, '%b') AS mes,
                SUM(ve_total) AS total
            FROM ventas
            WHERE su_id = :su_id
              AND ve_estado = 1
              AND ve_fecha_emision >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(ve_fecha_emision, '%Y-%m')
            ORDER BY ve_fecha_emision ASC
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_config_empresa_model()
    {
        $sql = "SELECT * FROM configuracion_empresa ORDER BY ce_id DESC LIMIT 1";
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            return [
                'ce_nombre' => 'SAMFARM PHARMA',
                'ce_nit' => 'S/N',
                'ce_direccion' => '',
                'ce_telefono' => ''
            ];
        }

        return $config;
    }
    protected static function ventas_por_usuario_sucursal_model($su_id)
    {
        $sql = "
            SELECT 
                CONCAT(u.us_nombres, ' ', u.us_apellido_paterno) AS usuario,
                COUNT(v.ve_id) AS total_ventas,
                COALESCE(SUM(v.ve_total), 0) AS monto_total
            FROM ventas v
            INNER JOIN usuarios u ON v.us_id = u.us_id
            WHERE v.su_id = :su_id
              AND v.ve_estado = 1
            GROUP BY v.us_id, u.us_nombres, u.us_apellido_paterno
            ORDER BY monto_total DESC
            LIMIT 8
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    protected static function costo_beneficio_sucursales_model($periodo)
    {
        $intervalo_map = [
            'mes' => 1,
            'trimestre' => 3,
            'semestre' => 6,
            'anio' => 12
        ];

        $meses = isset($intervalo_map[$periodo]) ? $intervalo_map[$periodo] : 6;

        $sql = "
            SELECT 
                s.su_nombre AS sucursal,
                COALESCE(SUM(c.co_total), 0) AS total_costos,
                COALESCE(SUM(v.ve_total), 0) AS total_ingresos,
                (COALESCE(SUM(v.ve_total), 0) - COALESCE(SUM(c.co_total), 0)) AS beneficio_neto
            FROM sucursales s
            LEFT JOIN compras c ON c.su_id = s.su_id 
                AND c.co_fecha >= DATE_SUB(NOW(), INTERVAL :meses MONTH)
                AND c.co_estado = 1
            LEFT JOIN ventas v ON v.su_id = s.su_id 
                AND v.ve_fecha_emision >= DATE_SUB(NOW(), INTERVAL :meses MONTH)
                AND v.ve_estado = 1
            WHERE s.su_estado = 1
            GROUP BY s.su_id, s.su_nombre
            HAVING (total_costos > 0 OR total_ingresos > 0)
            ORDER BY beneficio_neto DESC
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':meses', $meses, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }


    /* ------------------------------ sucursal sucursal----------------------------------- */
    /* ------------------------------ sucursal sucursal----------------------------------- */
    /* ------------------------------ sucursal sucursal----------------------------------- */
    /* ------------------------------ sucursal sucursal----------------------------------- */
    /* ------------------------------ sucursal sucursal----------------------------------- */
    /* ------------------------------ sucursal sucursal----------------------------------- */
    /* ------------------------------ sucursal sucursal----------------------------------- */
}
