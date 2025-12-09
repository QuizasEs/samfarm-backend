<?php

require_once "mainModel.php";

class notificacionesModel extends mainModel
{
    public static function generar_notificaciones_controller()
    {
        self::generar_notificaciones_automaticas_model();
        return true;
    }

    public static function obtener_notificaciones_controller($rol, $su_id = null)
    {
        if ($rol == 1) {
            return self::obtener_todas_notificaciones_model();
        } elseif ($rol == 2) {
            return self::obtener_notificaciones_sucursal_model($su_id);
        } else {
            return [];
        }
    }

    public static function marcar_como_leida_controller($id)
    {
        return self::marcar_como_leida_model($id);
    }

    public static function descartar_notificacion_controller($id)
    {
        return self::descartar_notificacion_model($id);
    }

    protected static function generar_notificaciones_automaticas_model()
    {
        $conexion = mainModel::conectar();

        self::generar_notificaciones_stock_bajo_model($conexion);
        self::generar_notificaciones_proximos_caducar_model($conexion);
        self::generar_notificaciones_ya_caducados_model($conexion);
        self::generar_notificaciones_sin_stock_model($conexion);
        self::generar_notificaciones_bajo_minimo_model($conexion);
        self::generar_notificaciones_transferencias_pendientes_model($conexion);

        self::limpiar_notificaciones_antiguas_model($conexion);
    }

    protected static function generar_notificaciones_stock_bajo_model($conexion)
    {
        $sql = "INSERT IGNORE INTO notificaciones (not_tipo, not_referencia_id, not_su_id, not_titulo, not_mensaje, not_icono, not_color, not_aplicable_rol_1, not_aplicable_rol_2)
                SELECT 'stock_bajo', CONCAT(m.med_id, '_', s.su_id), s.su_id, 'Stock Bajo', 
                CONCAT(m.med_nombre_quimico, ' - ', s.su_nombre, ': ', i.inv_total_unidades, ' unidades'),
                'warning-outline', '#ff9800', 1, 1
                FROM inventarios i
                JOIN medicamento m ON i.med_id = m.med_id
                JOIN sucursales s ON i.su_id = s.su_id
                WHERE i.inv_total_unidades > 0 AND i.inv_total_unidades <= (i.inv_minimo * 1.5)
                AND NOT EXISTS (SELECT 1 FROM notificaciones WHERE not_tipo = 'stock_bajo' 
                  AND not_referencia_id = CONCAT(m.med_id, '_', s.su_id) AND not_leida = 0 AND not_descartada = 0)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    protected static function generar_notificaciones_proximos_caducar_model($conexion)
    {
        $sql = "INSERT IGNORE INTO notificaciones (not_tipo, not_referencia_id, not_su_id, not_titulo, not_mensaje, not_icono, not_color, not_aplicable_rol_1, not_aplicable_rol_2)
                SELECT 'proximo_caducar', l.lm_id, l.su_id, 'Próximo a Caducar',
                CONCAT(m.med_nombre_quimico, ' Lote: ', l.lm_numero_lote, ' caduca en ', DATEDIFF(l.lm_fecha_vencimiento, CURDATE()), ' días'),
                'alert-circle-outline', '#ff5722', 1, 1
                FROM lote_medicamento l
                JOIN medicamento m ON l.med_id = m.med_id
                JOIN sucursales s ON l.su_id = s.su_id
                WHERE DATEDIFF(l.lm_fecha_vencimiento, CURDATE()) > 0 AND DATEDIFF(l.lm_fecha_vencimiento, CURDATE()) <= 30
                AND l.lm_cant_actual_unidades > 0
                AND NOT EXISTS (SELECT 1 FROM notificaciones WHERE not_tipo = 'proximo_caducar' 
                  AND not_referencia_id = l.lm_id AND not_leida = 0 AND not_descartada = 0)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    protected static function generar_notificaciones_ya_caducados_model($conexion)
    {
        $sql = "INSERT IGNORE INTO notificaciones (not_tipo, not_referencia_id, not_su_id, not_titulo, not_mensaje, not_icono, not_color, not_aplicable_rol_1, not_aplicable_rol_2)
                SELECT 'ya_caducado', l.lm_id, l.su_id, 'Producto Caducado',
                CONCAT(m.med_nombre_quimico, ' Lote: ', l.lm_numero_lote, ' caducó el ', DATE_FORMAT(l.lm_fecha_vencimiento, '%d/%m/%Y')),
                'close-circle-outline', '#f44336', 1, 1
                FROM lote_medicamento l
                JOIN medicamento m ON l.med_id = m.med_id
                JOIN sucursales s ON l.su_id = s.su_id
                WHERE l.lm_fecha_vencimiento < CURDATE() AND l.lm_cant_actual_unidades > 0
                AND NOT EXISTS (SELECT 1 FROM notificaciones WHERE not_tipo = 'ya_caducado' 
                  AND not_referencia_id = l.lm_id AND not_leida = 0 AND not_descartada = 0)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    protected static function generar_notificaciones_sin_stock_model($conexion)
    {
        $sql = "INSERT IGNORE INTO notificaciones (not_tipo, not_referencia_id, not_su_id, not_titulo, not_mensaje, not_icono, not_color, not_aplicable_rol_1, not_aplicable_rol_2)
                SELECT 'sin_stock', CONCAT(m.med_id, '_', s.su_id), s.su_id, 'Sin Stock',
                CONCAT(m.med_nombre_quimico, ' en ', s.su_nombre, ' no tiene existencias'),
                'close-outline', '#f44336', 1, 1
                FROM inventarios i
                JOIN medicamento m ON i.med_id = m.med_id
                JOIN sucursales s ON i.su_id = s.su_id
                WHERE i.inv_total_unidades = 0
                AND NOT EXISTS (SELECT 1 FROM notificaciones WHERE not_tipo = 'sin_stock' 
                  AND not_referencia_id = CONCAT(m.med_id, '_', s.su_id) AND not_leida = 0 AND not_descartada = 0)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    protected static function generar_notificaciones_bajo_minimo_model($conexion)
    {
        $sql = "INSERT IGNORE INTO notificaciones (not_tipo, not_referencia_id, not_su_id, not_titulo, not_mensaje, not_icono, not_color, not_aplicable_rol_1, not_aplicable_rol_2)
                SELECT 'bajo_minimo', i.inv_id, s.su_id, 'Bajo Mínimo',
                CONCAT(m.med_nombre_quimico, ' en ', s.su_nombre, ' está por debajo del mínimo permitido'),
                'trending-down-outline', '#ff9800', 1, 1
                FROM inventarios i
                JOIN medicamento m ON i.med_id = m.med_id
                JOIN sucursales s ON i.su_id = s.su_id
                WHERE i.inv_total_unidades < i.inv_minimo AND i.inv_total_unidades > 0
                AND NOT EXISTS (SELECT 1 FROM notificaciones WHERE not_tipo = 'bajo_minimo' 
                  AND not_referencia_id = i.inv_id AND not_leida = 0 AND not_descartada = 0)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    protected static function generar_notificaciones_transferencias_pendientes_model($conexion)
    {
        $sql = "INSERT IGNORE INTO notificaciones (not_tipo, not_referencia_id, not_su_id, not_titulo, not_mensaje, not_icono, not_color, not_aplicable_rol_1, not_aplicable_rol_2)
                SELECT 'transferencia_pendiente', t.tr_id, t.su_destino_id, 'Transferencia Pendiente',
                CONCAT('Transferencia #', t.tr_numero, ' de ', s1.su_nombre, ' pendiente de recepcionar'),
                'swap-horizontal-outline', '#2196f3', 1, 1
                FROM transferencias t
                JOIN sucursales s1 ON t.su_origen_id = s1.su_id
                WHERE t.tr_estado = 'pendiente'
                AND NOT EXISTS (SELECT 1 FROM notificaciones WHERE not_tipo = 'transferencia_pendiente' 
                  AND not_referencia_id = t.tr_id AND not_leida = 0 AND not_descartada = 0)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    protected static function limpiar_notificaciones_antiguas_model($conexion)
    {
        $sql = "DELETE FROM notificaciones WHERE not_descartada = 1 AND not_fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    protected static function obtener_todas_notificaciones_model()
    {
        self::generar_notificaciones_automaticas_model();

        $sql = "SELECT not_id as id, not_tipo as tipo, not_icono as icono, not_color as color,
                not_titulo as titulo, not_mensaje as mensaje, not_fecha_creacion as fecha, not_leida as leida
                FROM notificaciones
                WHERE not_descartada = 0 AND (not_aplicable_rol_1 = 1)
                ORDER BY not_leida ASC, not_fecha_creacion DESC
                LIMIT 100";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected static function obtener_notificaciones_sucursal_model($su_id)
    {
        self::generar_notificaciones_automaticas_model();

        $sql = "SELECT not_id as id, not_tipo as tipo, not_icono as icono, not_color as color,
                not_titulo as titulo, not_mensaje as mensaje, not_fecha_creacion as fecha, not_leida as leida
                FROM notificaciones
                WHERE not_descartada = 0 AND not_su_id = :su_id AND (not_aplicable_rol_2 = 1)
                ORDER BY not_leida ASC, not_fecha_creacion DESC
                LIMIT 100";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected static function marcar_como_leida_model($id)
    {
        $sql = "UPDATE notificaciones SET not_leida = 1, not_fecha_lectura = NOW() WHERE not_id = :id";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    protected static function descartar_notificacion_model($id)
    {
        $sql = "UPDATE notificaciones SET not_descartada = 1 WHERE not_id = :id";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
