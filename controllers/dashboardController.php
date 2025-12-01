<?php

require_once './models/mainModel.php';

class dashboardController extends mainModel
{
    public static function contar_sucursales_controller()
    {
        $sql = "SELECT COUNT(*) as total FROM sucursales WHERE su_estado = 1";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }

    public static function contar_medicamentos_inventario_controller()
    {
        $sql = "SELECT SUM(i.inv_total_unidades) as total 
                FROM inventarios i";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($resultado['total'] ?? 0);
    }

    public static function contar_clientes_controller()
    {
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE cl_estado = 1";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }

    public static function contar_usuarios_controller()
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE us_estado = 1 AND us_id != 1";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }

    public static function contar_categorias_controller()
    {
        $sql = "SELECT 
                    (SELECT COUNT(*) FROM via_de_administracion WHERE vd_estado = 1) +
                    (SELECT COUNT(*) FROM forma_farmaceutica WHERE ff_estado = 1) +
                    (SELECT COUNT(*) FROM laboratorios WHERE la_estado = 1)
                    as total";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }

    public static function obtener_proximos_vencimientos_controller($su_id = null)
    {
        $sql = "SELECT 
                    m.med_id,
                    m.med_nombre_quimico,
                    lm.lm_numero_lote,
                    lm.lm_fecha_vencimiento,
                    lm.lm_cant_actual_unidades,
                    s.su_nombre,
                    CASE 
                        WHEN lm.lm_fecha_vencimiento < CURDATE() THEN 'expirado'
                        WHEN lm.lm_fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'proximo'
                        ELSE 'disponible'
                    END as estado_vencimiento
                FROM lote_medicamento lm
                JOIN medicamento m ON lm.med_id = m.med_id
                JOIN sucursales s ON lm.su_id = s.su_id
                WHERE lm.lm_estado IN ('activo', 'terminado')
                AND lm.lm_fecha_vencimiento IS NOT NULL
                AND lm.lm_fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                AND lm.lm_fecha_vencimiento >= CURDATE()";
        
        if ($su_id) {
            $sql .= " AND lm.su_id = :su_id";
        }
        
        $sql .= " ORDER BY lm.lm_fecha_vencimiento ASC LIMIT 6";
        
        $stmt = mainModel::conectar()->prepare($sql);
        
        if ($su_id) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtener_stock_minimo_controller($su_id = null)
    {
        $sql = "SELECT 
                    i.inv_id,
                    m.med_nombre_quimico,
                    i.inv_total_unidades,
                    i.inv_minimo,
                    s.su_nombre,
                    CASE 
                        WHEN i.inv_total_unidades <= 0 THEN 'sin_stock'
                        WHEN i.inv_total_unidades < i.inv_minimo THEN 'bajo_stock'
                        ELSE 'adecuado'
                    END as estado_stock
                FROM inventarios i
                JOIN medicamento m ON i.med_id = m.med_id
                JOIN sucursales s ON i.su_id = s.su_id
                WHERE i.inv_minimo > 0
                AND i.inv_total_unidades <= i.inv_minimo";
        
        if ($su_id) {
            $sql .= " AND i.su_id = :su_id";
        }
        
        $sql .= " ORDER BY i.inv_total_unidades ASC LIMIT 6";
        
        $stmt = mainModel::conectar()->prepare($sql);
        
        if ($su_id) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtener_productos_mas_vendidos_controller($su_id = null)
    {
        $sql = "SELECT 
                    m.med_id,
                    m.med_nombre_quimico,
                    SUM(dv.dv_cantidad) as cantidad_vendida,
                    SUM(dv.dv_subtotal) as total_vendido,
                    s.su_nombre
                FROM detalle_venta dv
                JOIN medicamento m ON dv.med_id = m.med_id
                JOIN ventas v ON dv.ve_id = v.ve_id
                JOIN sucursales s ON v.su_id = s.su_id
                WHERE dv.dv_estado = 1";
        
        if ($su_id) {
            $sql .= " AND v.su_id = :su_id";
        }
        
        $sql .= " GROUP BY m.med_id, m.med_nombre_quimico, s.su_nombre, s.su_id
                ORDER BY cantidad_vendida DESC LIMIT 6";
        
        $stmt = mainModel::conectar()->prepare($sql);
        
        if ($su_id) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtener_ventas_mensuales_controller($su_id = null)
    {
        $sql = "SELECT 
                    DATE_FORMAT(v.ve_fecha_emision, '%Y-%m') as mes,
                    MONTH(v.ve_fecha_emision) as num_mes,
                    YEAR(v.ve_fecha_emision) as año,
                    SUM(v.ve_total) as total_mes,
                    COUNT(v.ve_id) as cantidad_ventas,
                    s.su_nombre
                FROM ventas v
                JOIN sucursales s ON v.su_id = s.su_id
                WHERE v.ve_estado = 1";
        
        if ($su_id) {
            $sql .= " AND v.su_id = :su_id";
        }
        
        $sql .= " GROUP BY DATE_FORMAT(v.ve_fecha_emision, '%Y-%m'), MONTH(v.ve_fecha_emision), YEAR(v.ve_fecha_emision), s.su_nombre, s.su_id
                ORDER BY v.ve_fecha_emision DESC LIMIT 12";
        
        $stmt = mainModel::conectar()->prepare($sql);
        
        if ($su_id) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function contar_vencimientos_por_estado_controller($su_id = null)
    {
        $sql = "SELECT 
                    CASE 
                        WHEN lm.lm_fecha_vencimiento < CURDATE() THEN 'expirado'
                        WHEN lm.lm_fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'proximo'
                        ELSE 'disponible'
                    END as estado,
                    COUNT(*) as cantidad
                FROM lote_medicamento lm
                WHERE lm.lm_estado IN ('activo', 'terminado')
                AND lm.lm_fecha_vencimiento IS NOT NULL";
        
        if ($su_id) {
            $sql .= " AND lm.su_id = :su_id";
        }
        
        $sql .= " GROUP BY estado";
        
        $stmt = mainModel::conectar()->prepare($sql);
        
        if ($su_id) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
