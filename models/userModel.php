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

    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
}
