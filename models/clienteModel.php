<?php

require_once "mainModel.php";

class clienteModel extends mainModel
{

    protected static function datos_clientes_model($inicio, $registros, $filtros = [])
    {
        $whereParts = [];
        $havingParts = [];
        $params = [];

        if (!empty($filtros['busqueda'])) {
            $whereParts[] = "(
                    c.cl_nombres LIKE :busqueda OR 
                    c.cl_apellido_paterno LIKE :busqueda OR 
                    c.cl_apellido_materno LIKE :busqueda OR 
                    c.cl_carnet LIKE :busqueda OR 
                    c.cl_telefono LIKE :busqueda
                )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (isset($filtros['estado'])) {
            if ($filtros['estado'] == 'activo') {
                $whereParts[] = "c.cl_estado = 1";
            } elseif ($filtros['estado'] == 'inactivo') {
                $whereParts[] = "c.cl_estado = 0";
            }
        }

        if (isset($filtros['con_compras'])) {
            if ($filtros['con_compras'] == 'con_compras') {
                $havingParts[] = "COUNT(v.ve_id) > 0";
            } elseif ($filtros['con_compras'] == 'sin_compras') {
                $havingParts[] = "COUNT(v.ve_id) = 0";
            }
        }

        if (isset($filtros['ultima_compra'])) {
            $dias = $filtros['ultima_compra'];
            if ($dias == 'nunca') {
                $havingParts[] = "MAX(v.ve_fecha_emision) IS NULL";
            } elseif ($dias == 'mas_90') {
                $havingParts[] = "MAX(v.ve_fecha_emision) < DATE_SUB(NOW(), INTERVAL 90 DAY)";
            } else {
                $havingParts[] = "MAX(v.ve_fecha_emision) >= DATE_SUB(NOW(), INTERVAL " . (int)$dias . " DAY)";
            }
        }

        if (isset($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.cl_creado_en) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (isset($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.cl_creado_en) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";
        $havingSQL = count($havingParts) > 0 ? "HAVING " . implode(' AND ', $havingParts) : "";

        $sql = "
                SELECT 
                    c.cl_id,
                    c.cl_nombres,
                    c.cl_apellido_paterno,
                    c.cl_apellido_materno,
                    c.cl_carnet,
                    c.cl_telefono,
                    c.cl_correo,
                    c.cl_creado_en,
                    c.cl_estado,
                    COUNT(v.ve_id) as total_compras,
                    MAX(v.ve_fecha_emision) as ultima_compra
                FROM clientes c
                LEFT JOIN ventas v ON c.cl_id = v.cl_id
                $whereSQL
                GROUP BY c.cl_id
                $havingSQL
                ORDER BY ultima_compra DESC
                LIMIT $inicio, $registros
            ";

        $stmt = self::conectar()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    protected static function contar_clientes_model($filtros = [])
    {
        $whereParts = [];
        $havingParts = [];
        $params = [];

        if (!empty($filtros['busqueda'])) {
            $whereParts[] = "(
                    c.cl_nombres LIKE :busqueda OR 
                    c.cl_apellido_paterno LIKE :busqueda OR 
                    c.cl_apellido_materno LIKE :busqueda OR 
                    c.cl_carnet LIKE :busqueda OR 
                    c.cl_telefono LIKE :busqueda
                )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (isset($filtros['estado'])) {
            if ($filtros['estado'] == 'activo') {
                $whereParts[] = "c.cl_estado = 1";
            } elseif ($filtros['estado'] == 'inactivo') {
                $whereParts[] = "c.cl_estado = 0";
            }
        }

        if (isset($filtros['con_compras'])) {
            if ($filtros['con_compras'] == 'con_compras') {
                $havingParts[] = "COUNT(v.ve_id) > 0";
            } elseif ($filtros['con_compras'] == 'sin_compras') {
                $havingParts[] = "COUNT(v.ve_id) = 0";
            }
        }

        if (isset($filtros['ultima_compra'])) {
            $dias = $filtros['ultima_compra'];
            if ($dias == 'nunca') {
                $havingParts[] = "MAX(v.ve_fecha_emision) IS NULL";
            } elseif ($dias == 'mas_90') {
                $havingParts[] = "MAX(v.ve_fecha_emision) < DATE_SUB(NOW(), INTERVAL 90 DAY)";
            } else {
                $havingParts[] = "MAX(v.ve_fecha_emision) >= DATE_SUB(NOW(), INTERVAL " . (int)$dias . " DAY)";
            }
        }

        if (isset($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.cl_creado_en) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (isset($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.cl_creado_en) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";
        $havingSQL = count($havingParts) > 0 ? "HAVING " . implode(' AND ', $havingParts) : "";

        $sql = "
                SELECT COUNT(*) as total FROM (
                    SELECT c.cl_id
                    FROM clientes c
                    LEFT JOIN ventas v ON c.cl_id = v.cl_id
                    $whereSQL
                    GROUP BY c.cl_id
                    $havingSQL
                ) as subquery
            ";

        $stmt = self::conectar()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    protected static function exportar_clientes_excel_model($filtros = [])
    {
        $whereParts = [];
        $havingParts = [];
        $params = [];

        if (!empty($filtros['busqueda'])) {
            $whereParts[] = "(
                    c.cl_nombres LIKE :busqueda OR
                    c.cl_apellido_paterno LIKE :busqueda OR
                    c.cl_apellido_materno LIKE :busqueda OR
                    c.cl_carnet LIKE :busqueda OR
                    c.cl_telefono LIKE :busqueda
                )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (isset($filtros['estado'])) {
            if ($filtros['estado'] == 'activo') {
                $whereParts[] = "c.cl_estado = 1";
            } elseif ($filtros['estado'] == 'inactivo') {
                $whereParts[] = "c.cl_estado = 0";
            }
        }

        if (isset($filtros['con_compras'])) {
            if ($filtros['con_compras'] == 'con_compras') {
                $havingParts[] = "COUNT(v.ve_id) > 0";
            } elseif ($filtros['con_compras'] == 'sin_compras') {
                $havingParts[] = "COUNT(v.ve_id) = 0";
            }
        }

        if (isset($filtros['ultima_compra'])) {
            $dias = $filtros['ultima_compra'];
            if ($dias == 'nunca') {
                $havingParts[] = "MAX(v.ve_fecha_emision) IS NULL";
            } elseif ($dias == 'mas_90') {
                $havingParts[] = "MAX(v.ve_fecha_emision) < DATE_SUB(NOW(), INTERVAL 90 DAY)";
            } else {
                $havingParts[] = "MAX(v.ve_fecha_emision) >= DATE_SUB(NOW(), INTERVAL " . (int)$dias . " DAY)";
            }
        }

        if (isset($filtros['fecha_desde'])) {
            $whereParts[] = "DATE(c.cl_creado_en) >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (isset($filtros['fecha_hasta'])) {
            $whereParts[] = "DATE(c.cl_creado_en) <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";
        $havingSQL = count($havingParts) > 0 ? "HAVING " . implode(' AND ', $havingParts) : "";

        $sql = "
                SELECT
                    c.cl_nombres AS 'Nombres',
                    c.cl_apellido_paterno AS 'Apellido Paterno',
                    c.cl_apellido_materno AS 'Apellido Materno',
                    c.cl_carnet AS 'CI',
                    c.cl_telefono AS 'Teléfono',
                    c.cl_correo AS 'Correo',
                    c.cl_direccion AS 'Dirección',
                    DATE_FORMAT(c.cl_creado_en, '%d/%m/%Y') AS 'Fecha Registro',
                    COUNT(v.ve_id) AS 'Total Compras',
                    FORMAT(IFNULL(SUM(v.ve_total), 0), 2) AS 'Monto Total',
                    IFNULL(DATE_FORMAT(MAX(v.ve_fecha_emision), '%d/%m/%Y'), 'Nunca') AS 'Última Compra',
                    CASE WHEN c.cl_estado = 1 THEN 'ACTIVO' ELSE 'INACTIVO' END AS 'Estado'
                FROM clientes c
                LEFT JOIN ventas v ON c.cl_id = v.cl_id
                $whereSQL
                GROUP BY c.cl_id
                $havingSQL
                ORDER BY c.cl_creado_en DESC
            ";

        $stmt = self::conectar()->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt;
    }

    /* modelo de registro y edicion */
    protected static function agregar_cliente_model($datos)
    {
        $sql = "INSERT INTO clientes (
            cl_nombres, 
            cl_apellido_paterno, 
            cl_apellido_materno, 
            cl_telefono, 
            cl_correo, 
            cl_direccion, 
            cl_carnet
        ) VALUES (
            :nombres, 
            :paterno, 
            :materno, 
            :telefono, 
            :correo, 
            :direccion, 
            :carnet
        )";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':nombres', $datos['cl_nombres']);
        $stmt->bindParam(':paterno', $datos['cl_apellido_paterno']);
        $stmt->bindParam(':materno', $datos['cl_apellido_materno']);
        $stmt->bindParam(':telefono', $datos['cl_telefono']);
        $stmt->bindParam(':correo', $datos['cl_correo']);
        $stmt->bindParam(':direccion', $datos['cl_direccion']);
        $stmt->bindParam(':carnet', $datos['cl_carnet']);
        $stmt->execute();

        return $stmt;
    }

    protected static function editar_cliente_model($datos)
    {
        $sql = "UPDATE clientes SET 
            cl_nombres = :nombres,
            cl_apellido_paterno = :paterno,
            cl_apellido_materno = :materno,
            cl_telefono = :telefono,
            cl_correo = :correo,
            cl_direccion = :direccion,
            cl_carnet = :carnet
        WHERE cl_id = :cl_id";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $datos['cl_id']);
        $stmt->bindParam(':nombres', $datos['cl_nombres']);
        $stmt->bindParam(':paterno', $datos['cl_apellido_paterno']);
        $stmt->bindParam(':materno', $datos['cl_apellido_materno']);
        $stmt->bindParam(':telefono', $datos['cl_telefono']);
        $stmt->bindParam(':correo', $datos['cl_correo']);
        $stmt->bindParam(':direccion', $datos['cl_direccion']);
        $stmt->bindParam(':carnet', $datos['cl_carnet']);
        $stmt->execute();

        return $stmt;
    }

    protected static function toggle_estado_cliente_model($cl_id, $estado)
    {
        $sql = "UPDATE clientes SET cl_estado = :estado WHERE cl_id = :cl_id";
        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $cl_id);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();

        return $stmt;
    }

    protected static function datos_cliente_model($cl_id)
    {
        $sql = "SELECT * FROM clientes WHERE cl_id = :cl_id";
        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $cl_id);
        $stmt->execute();

        return $stmt;
    }


    protected static function detalle_completo_cliente_model($cl_id)
    {
        $sql = "
            SELECT 
                c.*,
                COUNT(v.ve_id) as total_compras,
                IFNULL(SUM(v.ve_total), 0) as monto_total,
                COUNT(DISTINCT f.fa_id) as facturas_emitidas,
                MAX(v.ve_fecha_emision) as ultima_compra
            FROM clientes c
            LEFT JOIN ventas v ON c.cl_id = v.cl_id
            LEFT JOIN factura f ON v.ve_id = f.ve_id
            WHERE c.cl_id = :cl_id
            GROUP BY c.cl_id
        ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $cl_id);
        $stmt->execute();

        return $stmt;
    }

    protected static function ultimas_compras_cliente_model($cl_id, $limite = 5)
    {
        $sql = "
                    SELECT 
                        v.ve_numero_documento,
                        v.ve_fecha_emision,
                        v.ve_total,
                        v.ve_tipo_documento,
                        v.ve_id,
                        v.ve_subtotal,
                        v.ve_impuesto,
                        COUNT(dv.dv_id) as total_items,
                        SUM(dv.dv_cantidad) as total_unidades,
                        GROUP_CONCAT(
                            CONCAT(m.med_nombre_quimico, ' (', dv.dv_cantidad, ')')
                            ORDER BY dv.dv_id 
                            SEPARATOR ' | '
                        ) as medicamentos_detalle,
                        u.us_nombres as vendedor_nombre,
                        s.su_nombre as sucursal_nombre,
                        c.caja_nombre as caja_nombre
                    FROM ventas v
                    LEFT JOIN detalle_venta dv ON v.ve_id = dv.ve_id
                    LEFT JOIN medicamento m ON dv.med_id = m.med_id
                    LEFT JOIN usuarios u ON v.us_id = u.us_id
                    LEFT JOIN sucursales s ON v.su_id = s.su_id
                    LEFT JOIN caja c ON v.caja_id = c.caja_id
                    WHERE v.cl_id = :cl_id
                    GROUP BY v.ve_id
                    ORDER BY v.ve_fecha_emision DESC
                    LIMIT :limite
                ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $cl_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    protected static function medicamentos_mas_comprados_model($cl_id, $limite = 5)
    {
        $sql = "
                    SELECT 
                        m.med_nombre_quimico,
                        m.med_version_comercial,
                        l.la_nombre_comercial as laboratorio,
                        ff.ff_nombre as forma_farmaceutica,
                        COUNT(dv.dv_id) as veces_comprado,
                        SUM(dv.dv_cantidad) as total_unidades,
                        MAX(v.ve_fecha_emision) as ultima_compra,
                        ROUND(AVG(dv.dv_precio_unitario), 2) as precio_promedio
                    FROM detalle_venta dv
                    INNER JOIN ventas v ON dv.ve_id = v.ve_id
                    INNER JOIN medicamento m ON dv.med_id = m.med_id
                    LEFT JOIN laboratorios l ON m.la_id = l.la_id
                    LEFT JOIN forma_farmaceutica ff ON m.ff_id = ff.ff_id
                    WHERE v.cl_id = :cl_id
                    GROUP BY dv.med_id
                    ORDER BY veces_comprado DESC, total_unidades DESC
                    LIMIT :limite
                ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $cl_id);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    protected static function grafico_compras_mensuales_model($cl_id)
    {
        $sql = "
            SELECT 
                DATE_FORMAT(v.ve_fecha_emision, '%Y-%m') as mes,
                COUNT(v.ve_id) as total_compras,
                SUM(v.ve_total) as monto_total
            FROM ventas v
            WHERE v.cl_id = :cl_id
            AND v.ve_fecha_emision >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $cl_id);
        $stmt->execute();

        return $stmt;
    }


    protected static function exportar_clientes_pdf_model()
    {
        $sql = "
            SELECT 
                c.cl_id,
                c.cl_nombres AS 'Nombres',
                c.cl_apellido_paterno AS 'Apellido Paterno',
                c.cl_apellido_materno AS 'Apellido Materno',
                c.cl_carnet AS 'CI',
                c.cl_telefono AS 'Teléfono',
                c.cl_correo AS 'Correo',
                DATE_FORMAT(c.cl_creado_en, '%d/%m/%Y') AS 'Fecha Registro',
                COUNT(v.ve_id) AS 'Total Compras',
                CONCAT('Bs. ', FORMAT(IFNULL(SUM(v.ve_total), 0), 2)) AS 'Monto Total',
                IFNULL(DATE_FORMAT(MAX(v.ve_fecha_emision), '%d/%m/%Y'), 'Nunca') AS 'Última Compra',
                CASE WHEN c.cl_estado = 1 THEN 'ACTIVO' ELSE 'INACTIVO' END AS 'Estado'
            FROM clientes c
            LEFT JOIN ventas v ON c.cl_id = v.cl_id
            GROUP BY c.cl_id
            ORDER BY c.cl_creado_en DESC
        ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    protected static function obtener_config_empresa_model()
    {
        $sql = "SELECT * FROM configuracion_empresa LIMIT 1";
        $stmt = self::conectar()->prepare($sql);
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        return $config ?: [
            'ce_nombre' => 'SAMFARM PHARMA',
            'ce_nit' => '123456789',
            'ce_telefono' => '591-2-1234567',
            'ce_direccion' => 'Av. Principal #123, La Paz, Bolivia'
        ];
    }

    protected static function historial_completo_model($cl_id)
    {
        $sql = "
                SELECT 
                    v.ve_numero_documento,
                    v.ve_fecha_emision,
                    v.ve_total,
                    v.ve_subtotal,
                    v.ve_impuesto,
                    v.ve_tipo_documento,
                    v.ve_id,
                    COUNT(dv.dv_id) as total_items,
                    SUM(dv.dv_cantidad) as total_unidades,
                    GROUP_CONCAT(
                        CONCAT(m.med_nombre_quimico, ' (', dv.dv_cantidad, ')')
                        ORDER BY dv.dv_id 
                        SEPARATOR ' | '
                    ) as medicamentos_detalle,
                    u.us_nombres as vendedor_nombre,
                    u.us_apellido_paterno as vendedor_apellido,
                    s.su_nombre as sucursal_nombre,
                    c.caja_nombre as caja_nombre
                FROM ventas v
                LEFT JOIN detalle_venta dv ON v.ve_id = dv.ve_id
                LEFT JOIN medicamento m ON dv.med_id = m.med_id
                LEFT JOIN usuarios u ON v.us_id = u.us_id
                LEFT JOIN sucursales s ON v.su_id = s.su_id
                LEFT JOIN caja c ON v.caja_id = c.caja_id
                WHERE v.cl_id = :cl_id
                GROUP BY v.ve_id
                ORDER BY v.ve_fecha_emision DESC
            ";

        $stmt = self::conectar()->prepare($sql);
        $stmt->bindParam(':cl_id', $cl_id);
        $stmt->execute();

        return $stmt;
    }
}
