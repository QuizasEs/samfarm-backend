<?php
require_once "mainModel.php";

class userModel extends mainModel
{

    protected static function datos_usuarios_model($inicio, $registros, $filtros = [])
    {
        $whereParts = ["u.us_id != :excluir_id", "u.us_id != 1"];
        $params = [':excluir_id' => $filtros['excluir_id']];

        if (isset($filtros['sucursal'])) {
            $whereParts[] = "u.su_id = :sucursal";
            $params[':sucursal'] = $filtros['sucursal'];
        }

        if (!empty($filtros['busqueda'])) {
            $whereParts[] = "(
                        u.us_nombres LIKE :busqueda OR 
                        u.us_apellido_paterno LIKE :busqueda OR 
                        u.us_apellido_materno LIKE :busqueda OR 
                        u.us_numero_carnet LIKE :busqueda OR 
                        u.us_username LIKE :busqueda OR 
                        u.us_telefono LIKE :busqueda OR 
                        u.us_correo LIKE :busqueda
                    )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (isset($filtros['rol'])) {
            $whereParts[] = "u.ro_id = :rol";
            $params[':rol'] = $filtros['rol'];
        }

        if (isset($filtros['estado'])) {
            if ($filtros['estado'] == 'activo') {
                $whereParts[] = "u.us_estado = 1";
            } elseif ($filtros['estado'] == 'inactivo') {
                $whereParts[] = "u.us_estado = 0";
            }
        }

        $whereSQL = "WHERE " . implode(' AND ', $whereParts);

        $sql = "
                    SELECT 
                        u.us_id,
                        u.us_nombres,
                        u.us_apellido_paterno,
                        u.us_apellido_materno,
                        u.us_numero_carnet,
                        u.us_telefono,
                        u.us_correo,
                        u.us_direccion,
                        u.us_username,
                        u.us_creado_en,
                        u.us_estado,
                        u.ro_id,
                        r.ro_nombre as rol_nombre,
                        s.su_nombre as sucursal_nombre
                    FROM usuarios u
                    LEFT JOIN roles r ON u.ro_id = r.ro_id
                    LEFT JOIN sucursales s ON u.su_id = s.su_id
                    $whereSQL
                    ORDER BY u.us_creado_en DESC
                    LIMIT $inicio, $registros
                ";

        $stmt = self::conectar()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    protected static function contar_usuarios_model($filtros = [])
    {
        $whereParts = ["u.us_id != :excluir_id", "u.us_id != 1"];
        $params = [':excluir_id' => $filtros['excluir_id']];

        if (isset($filtros['sucursal'])) {
            $whereParts[] = "u.su_id = :sucursal";
            $params[':sucursal'] = $filtros['sucursal'];
        }

        if (!empty($filtros['busqueda'])) {
            $whereParts[] = "(
                        u.us_nombres LIKE :busqueda OR 
                        u.us_apellido_paterno LIKE :busqueda OR 
                        u.us_apellido_materno LIKE :busqueda OR 
                        u.us_numero_carnet LIKE :busqueda OR 
                        u.us_username LIKE :busqueda OR 
                        u.us_telefono LIKE :busqueda OR 
                        u.us_correo LIKE :busqueda
                    )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (isset($filtros['rol'])) {
            $whereParts[] = "u.ro_id = :rol";
            $params[':rol'] = $filtros['rol'];
        }

        if (isset($filtros['estado'])) {
            if ($filtros['estado'] == 'activo') {
                $whereParts[] = "u.us_estado = 1";
            } elseif ($filtros['estado'] == 'inactivo') {
                $whereParts[] = "u.us_estado = 0";
            }
        }

        $whereSQL = "WHERE " . implode(' AND ', $whereParts);

        $sql = "SELECT COUNT(*) as total FROM usuarios u $whereSQL";

        $stmt = self::conectar()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    protected static function exportar_usuarios_excel_model($rol_usuario, $sucursal_usuario)
    {
        $whereClause = "WHERE u.us_id != 1";

        if ($rol_usuario == 2) {
            $whereClause .= " AND u.su_id = $sucursal_usuario";
        }

        $sql = "
                    SELECT 
                        u.us_nombres AS 'Nombres',
                        u.us_apellido_paterno AS 'Apellido Paterno',
                        u.us_apellido_materno AS 'Apellido Materno',
                        u.us_numero_carnet AS 'Carnet',
                        u.us_telefono AS 'Teléfono',
                        u.us_correo AS 'Correo',
                        u.us_direccion AS 'Dirección',
                        u.us_username AS 'Usuario',
                        DATE_FORMAT(u.us_creado_en, '%d/%m/%Y') AS 'Fecha Creación',
                        r.ro_nombre AS 'Rol',
                        s.su_nombre AS 'Sucursal',
                        CASE WHEN u.us_estado = 1 THEN 'ACTIVO' ELSE 'INACTIVO' END AS 'Estado'
                    FROM usuarios u
                    LEFT JOIN roles r ON u.ro_id = r.ro_id
                    LEFT JOIN sucursales s ON u.su_id = s.su_id
                    $whereClause
                    ORDER BY u.us_creado_en DESC
                ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
    protected static function editar_usuario_model($datos)
    {
        $sql = "UPDATE usuarios SET 
                    us_nombres = :nombres,
                    us_apellido_paterno = :paterno,
                    us_apellido_materno = :materno,
                    us_numero_carnet = :carnet,
                    us_telefono = :telefono,
                    us_correo = :correo,
                    us_direccion = :direccion,
                    us_username = :username,
                    us_password_hash = :password,
                    su_id = :sucursal,
                    ro_id = :rol
                WHERE us_id = :us_id";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':us_id', $datos['us_id']);
        $stmt->bindParam(':nombres', $datos['Nombres']);
        $stmt->bindParam(':paterno', $datos['ApellidoPaterno']);
        $stmt->bindParam(':materno', $datos['ApellidoMaterno']);
        $stmt->bindParam(':carnet', $datos['Carnet']);
        $stmt->bindParam(':telefono', $datos['Telefono']);
        $stmt->bindParam(':correo', $datos['Correo']);
        $stmt->bindParam(':direccion', $datos['Direccion']);
        $stmt->bindParam(':username', $datos['UsuarioName']);
        $stmt->bindParam(':password', $datos['Password']);
        $stmt->bindParam(':sucursal', $datos['Sucursal']);
        $stmt->bindParam(':rol', $datos['Rol']);
        $stmt->execute();

        return $stmt;
    }

    protected static function toggle_estado_usuario_model($us_id, $estado)
    {
        $sql = "UPDATE usuarios SET us_estado = :estado WHERE us_id = :us_id";
        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':us_id', $us_id);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();

        return $stmt;
    }

    protected static function datos_usuario_model($us_id)
    {
        $sql = "SELECT * FROM usuarios WHERE us_id = :us_id";
        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':us_id', $us_id);
        $stmt->execute();

        return $stmt;
    }

    protected static function detalle_completo_usuario_model($us_id)
    {
        $sql = "
                    SELECT 
                        u.*,
                        r.ro_nombre as rol_nombre,
                        s.su_nombre as sucursal_nombre,
                        COUNT(v.ve_id) as total_ventas,
                        IFNULL(SUM(v.ve_total), 0) as monto_total
                    FROM usuarios u
                    LEFT JOIN roles r ON u.ro_id = r.ro_id
                    LEFT JOIN sucursales s ON u.su_id = s.su_id
                    LEFT JOIN ventas v ON u.us_id = v.us_id
                    WHERE u.us_id = :us_id
                    GROUP BY u.us_id
                ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':us_id', $us_id);
        $stmt->execute();

        return $stmt;
    }

    protected static function ultimas_ventas_usuario_model($us_id, $limite = 10)
    {
        $sql = "
                    SELECT 
                        v.ve_numero_documento,
                        v.ve_fecha_emision,
                        v.ve_total,
                        v.ve_tipo_documento,
                        v.ve_id,
                        COUNT(dv.dv_id) as total_items,
                        CONCAT_WS(' ', c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno) as cliente_nombre
                    FROM ventas v
                    LEFT JOIN detalle_venta dv ON v.ve_id = dv.ve_id
                    LEFT JOIN clientes c ON v.cl_id = c.cl_id
                    WHERE v.us_id = :us_id
                    GROUP BY v.ve_id
                    ORDER BY v.ve_fecha_emision DESC
                    LIMIT :limite
                ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':us_id', $us_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }
    protected static function agregar_usuario_modelo($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO usuarios(
                us_nombres, 
                us_apellido_paterno, 
                us_apellido_materno, 
                us_numero_carnet, 
                us_telefono, 
                us_correo, 
                us_direccion, 
                us_username, 
                us_password_hash, 
                su_id, 
                ro_id
            ) VALUES(
                :nombres, 
                :apellido_paterno, 
                :apellido_materno, 
                :carnet, 
                :telefono, 
                :correo, 
                :direccion, 
                :username, 
                :password, 
                :sucursal, 
                :rol
            )
        ");

        $sql->bindParam(":nombres", $datos['Nombres']);
        $sql->bindParam(":apellido_paterno", $datos['ApellidoPaterno']);
        $sql->bindParam(":apellido_materno", $datos['ApellidoMaterno']);
        $sql->bindParam(":carnet", $datos['Carnet']);
        $sql->bindParam(":telefono", $datos['Telefono']);
        $sql->bindParam(":correo", $datos['Correo']);
        $sql->bindParam(":direccion", $datos['Direccion']);
        $sql->bindParam(":username", $datos['UsuarioName']);
        $sql->bindParam(":password", $datos['Password']);
        $sql->bindParam(":sucursal", $datos['Sucursal']);
        $sql->bindParam(":rol", $datos['Rol']);

        $sql->execute();
        return $sql;
    }
    protected static function ventas_mensuales_usuario_model($us_id)
    {
        $sql = "
            SELECT 
                DATE_FORMAT(v.ve_fecha_emision, '%b %Y') as mes,
                DATE_FORMAT(v.ve_fecha_emision, '%Y-%m') as orden,
                COUNT(v.ve_id) as cantidad,
                IFNULL(SUM(v.ve_total), 0) as monto
            FROM ventas v
            WHERE v.us_id = :us_id
                AND v.ve_fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(v.ve_fecha_emision, '%Y-%m')
            ORDER BY orden ASC
        ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':us_id', $us_id);
        $stmt->execute();

        return $stmt;
    }

    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
}
