<?php

require_once "mainModel.php";

class preciosModel extends mainModel
{
    /**
     * OBTENER SUCURSAL DE UN LOTE
     */
    public static function obtener_sucursal_del_lote_model($lm_id)
    {
        $conexion = self::conectar();
        $stmt = $conexion->prepare("SELECT su_id FROM lote_medicamento WHERE lm_id = :lm_id");
        $stmt->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['su_id'] ?? null;
    }

    /**
     * DIAGNÓSTICO: VERIFICAR TABLA INFORMES
     */
    public static function diagnostico_informes_model()
    {
        $conexion = self::conectar();
        
        $diagnostico = [];
        
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM informes");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $diagnostico['total_informes'] = $resultado['total'] ?? 0;
        
        $stmt = $conexion->query("SELECT COUNT(*) as total FROM informes WHERE JSON_EXTRACT(inf_config, '$.tipo_cambio') IN ('lote_individual', 'todos_lotes')");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $diagnostico['informes_cambios_precio'] = $resultado['total'] ?? 0;
        
        $stmt = $conexion->query("SELECT inf_id, inf_usuario, inf_config, inf_creado_en FROM informes ORDER BY inf_creado_en DESC LIMIT 5");
        $diagnostico['ultimos_5_registros'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("DEBUG DIAGNÓSTICO: " . json_encode($diagnostico, JSON_PRETTY_PRINT));
        
        return $diagnostico;
    }

    /**
     * OBTENER MEDICAMENTOS CON SUS LOTES
     */
    public static function obtener_medicamentos_con_lotes_model($su_id = null, $busqueda = "")
    {
        $sql = "
            SELECT 
                m.med_id,
                m.med_nombre_quimico,
                la.la_nombre_comercial,
                ROUND(AVG(lm.lm_precio_compra), 2) AS precio_compra_promedio,
                COUNT(DISTINCT lm.lm_id) AS total_lotes,
                SUM(CASE WHEN lm.lm_estado = 'activo' AND lm.lm_cant_actual_unidades > 0 THEN 1 ELSE 0 END) AS lotes_activos,
                SUM(CASE WHEN lm.lm_estado = 'activo' AND lm.lm_cant_actual_unidades > 0 THEN lm.lm_cant_actual_unidades ELSE 0 END) AS total_unidades_activas,
                SUM(CASE WHEN lm.lm_estado = 'activo' AND lm.lm_cant_actual_unidades > 0 THEN lm.lm_cant_actual_unidades * lm.lm_precio_venta ELSE 0 END) AS total_valorado
            FROM medicamento m
            LEFT JOIN laboratorios la ON la.la_id = m.la_id
            LEFT JOIN lote_medicamento lm ON lm.med_id = m.med_id
            WHERE 1=1
        ";

        if ($su_id !== null && $su_id > 0) {
            $sql .= " AND lm.su_id = :su_id";
        }

        if (!empty($busqueda)) {
            $sql .= " AND (m.med_nombre_quimico LIKE :busqueda OR m.med_principio_activo LIKE :busqueda)";
        }

        $sql .= "
            GROUP BY m.med_id, m.med_nombre_quimico, la.la_nombre_comercial
            HAVING SUM(CASE WHEN lm.lm_estado = 'activo' AND lm.lm_cant_actual_unidades > 0 THEN 1 ELSE 0 END) > 0
            ORDER BY m.med_nombre_quimico ASC
        ";

        $conexion = self::conectar();
        $stmt = $conexion->prepare($sql);

        if ($su_id !== null && $su_id > 0) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }

        if (!empty($busqueda)) {
            $busqueda_param = "%{$busqueda}%";
            $stmt->bindParam(':busqueda', $busqueda_param, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * OBTENER LOTES DE UN MEDICAMENTO
     */
    public static function obtener_lotes_medicamento_model($med_id, $su_id = null)
    {
        $sql = "
            SELECT
                lm.lm_id,
                lm.lm_numero_lote,
                COALESCE(la.la_nombre_comercial, 'N/A') AS med_nombre_comercial,
                su.su_nombre AS sucursal_nombre,
                lm.lm_precio_compra,
                lm.lm_precio_venta,
                lm.lm_cant_actual_unidades,
                (lm.lm_cant_actual_unidades * lm.lm_precio_venta) AS subtotal_valorado,
                ROUND(((lm.lm_precio_venta - lm.lm_precio_compra) / lm.lm_precio_compra) * 100, 2) AS margen_pct,
                lm.lm_fecha_vencimiento,
                lm.lm_estado
            FROM lote_medicamento lm
            LEFT JOIN medicamento m ON m.med_id = lm.med_id
            LEFT JOIN laboratorios la ON m.la_id = la.la_id
            LEFT JOIN sucursales su ON su.su_id = lm.su_id
            WHERE lm.med_id = :med_id
        ";

        if ($su_id !== null && $su_id > 0) {
            $sql .= " AND lm.su_id = :su_id";
        }

        $sql .= "
            AND lm.lm_estado = 'activo'
            AND lm.lm_cant_actual_unidades > 0
            ORDER BY lm.lm_fecha_vencimiento ASC
        ";

        $conexion = self::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);

        if ($su_id !== null && $su_id > 0) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ACTUALIZAR PRECIO DE UN LOTE INDIVIDUAL
     */
    public static function actualizar_precio_lote_individual_model($lm_id, $precio_nuevo, $usuario_id, $med_id)
    {
        $conexion = self::conectar();
        
        try {
            $conexion->beginTransaction();

            // 1) Obtener precio anterior, med_id y su_id del lote
            $sql_get = $conexion->prepare("SELECT lm_precio_venta, med_id, su_id FROM lote_medicamento WHERE lm_id = :lm_id");
            $sql_get->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
            $sql_get->execute();
            $resultado = $sql_get->fetch(PDO::FETCH_ASSOC);
            $precio_anterior = $resultado['lm_precio_venta'] ?? 0;
            $med_id = $resultado['med_id'] ?? $med_id;
            $su_id = $resultado['su_id'] ?? null;

            // 2) Actualizar precio del lote
            $sql_update = $conexion->prepare("
                UPDATE lote_medicamento 
                SET lm_precio_venta = :precio_nuevo,
                    lm_actualizado_en = NOW()
                WHERE lm_id = :lm_id
            ");
            $sql_update->bindParam(':precio_nuevo', $precio_nuevo, PDO::PARAM_STR);
            $sql_update->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
            $sql_update->execute();

            // 3) Recalcular valorado del inventario
            $sql_inv = $conexion->prepare("
                UPDATE inventarios i
                SET i.inv_total_valorado = COALESCE((
                    SELECT SUM(lm.lm_cant_actual_unidades * lm.lm_precio_venta)
                    FROM lote_medicamento lm
                    WHERE lm.med_id = i.med_id 
                    AND lm.su_id = i.su_id
                    AND lm.lm_estado = 'activo'
                ), 0)
                WHERE i.med_id = :med_id AND i.su_id = :su_id
            ");
            $sql_inv->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $sql_inv->bindParam(':su_id', $su_id, PDO::PARAM_INT);
            $sql_inv->execute();

            // 4) Registrar en balance_precios
            self::registrar_balance_precio_model(
                $lm_id,
                $usuario_id,
                $precio_anterior,
                $precio_nuevo
            );

            $conexion->commit();

            return [
                'success' => true,
                'mensaje' => 'Precio actualizado correctamente'
            ];

        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error actualizando precio de lote: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error al actualizar precio: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ACTUALIZAR PRECIO DE TODOS LOS LOTES DE UN MEDICAMENTO
     */
    public static function actualizar_precio_todos_lotes_model($med_id, $precio_nuevo, $usuario_id, $su_id = null)
    {
        $conexion = self::conectar();
        
        try {
            $conexion->beginTransaction();

            // 1) Si no se proporciona su_id, obtenerlo del primer lote del medicamento
            if ($su_id === null) {
                $sql_su = $conexion->prepare("
                    SELECT su_id 
                    FROM lote_medicamento 
                    WHERE med_id = :med_id 
                    AND lm_estado IN ('activo', 'en_espera')
                    LIMIT 1
                ");
                $sql_su->bindParam(':med_id', $med_id, PDO::PARAM_INT);
                $sql_su->execute();
                $resultado_su = $sql_su->fetch(PDO::FETCH_ASSOC);
                $su_id = $resultado_su['su_id'] ?? null;
                
                if ($su_id === null) {
                    throw new Exception("No se encontraron lotes activos para el medicamento");
                }
            }

            // 2) Obtener lotes actuales para el histórico
            $sql_get = $conexion->prepare("
                SELECT lm_id, lm_precio_venta 
                FROM lote_medicamento 
                WHERE med_id = :med_id 
                AND su_id = :su_id
                AND lm_estado IN ('activo', 'en_espera')
            ");
            $sql_get->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $sql_get->bindParam(':su_id', $su_id, PDO::PARAM_INT);
            $sql_get->execute();
            $lotes = $sql_get->fetchAll(PDO::FETCH_ASSOC);
            $cantidad_lotes = count($lotes);

            // 2) Actualizar todos los lotes del medicamento
            $sql_update = $conexion->prepare("
                UPDATE lote_medicamento 
                SET lm_precio_venta = :precio_nuevo,
                    lm_actualizado_en = NOW()
                WHERE med_id = :med_id 
                AND su_id = :su_id
                AND lm_estado IN ('activo', 'en_espera')
            ");
            $sql_update->bindParam(':precio_nuevo', $precio_nuevo, PDO::PARAM_STR);
            $sql_update->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $sql_update->bindParam(':su_id', $su_id, PDO::PARAM_INT);
            $sql_update->execute();

            // 3) Recalcular valorado del inventario (UNA SOLA VEZ)
            $sql_inv = $conexion->prepare("
                UPDATE inventarios i
                SET i.inv_total_valorado = COALESCE((
                    SELECT SUM(lm.lm_cant_actual_unidades * lm.lm_precio_venta)
                    FROM lote_medicamento lm
                    WHERE lm.med_id = i.med_id 
                    AND lm.su_id = i.su_id
                    AND lm.lm_estado = 'activo'
                ), 0)
                WHERE i.med_id = :med_id AND i.su_id = :su_id
            ");
            $sql_inv->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $sql_inv->bindParam(':su_id', $su_id, PDO::PARAM_INT);
            $sql_inv->execute();

            // 4) Registrar en balance_precios (un registro por cada lote)
            foreach ($lotes as $lote) {
                self::registrar_balance_precio_model(
                    $lote['lm_id'],
                    $usuario_id,
                    $lote['lm_precio_venta'],
                    $precio_nuevo
                );
            }

            $conexion->commit();

            return [
                'success' => true,
                'mensaje' => "Precio actualizado en $cantidad_lotes lotes correctamente"
            ];

        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error actualizando precios de todos los lotes: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error al actualizar precios: ' . $e->getMessage()
            ];
        }
    }

    private static function registrar_balance_precio_model($lm_id, $usuario_id, $precio_anterior, $precio_nuevo)
    {
        $sql = "
            INSERT INTO balance_precios 
            (lm_id, us_id, bp_precio_anterior, bp_precio_nuevo, bp_creado_en)
            VALUES 
            (:lm_id, :us_id, :bp_precio_anterior, :bp_precio_nuevo, NOW())
        ";

        $conexion = self::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
        $stmt->bindParam(':us_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':bp_precio_anterior', $precio_anterior, PDO::PARAM_STR);
        $stmt->bindParam(':bp_precio_nuevo', $precio_nuevo, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    public static function obtener_informes_cambios_precios_model($inicio = 0, $registros = 10, $filtros = [])
    {
        $sql = "
            SELECT 
                bp.bp_id,
                bp.lm_id,
                bp.bp_precio_anterior,
                bp.bp_precio_nuevo,
                bp.bp_creado_en,
                u.us_nombres,
                u.us_apellido_paterno,
                u.us_apellido_materno,
                m.med_nombre_quimico,
                s.su_nombre,
                lm.lm_numero_lote
            FROM balance_precios bp
            INNER JOIN lote_medicamento lm ON lm.lm_id = bp.lm_id
            INNER JOIN medicamento m ON m.med_id = lm.med_id
            INNER JOIN sucursales s ON s.su_id = lm.su_id
            LEFT JOIN usuarios u ON u.us_id = bp.us_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['su_id']) && $filtros['su_id'] > 0) {
            $sql .= " AND lm.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (m.med_nombre_quimico LIKE :busqueda OR u.us_nombres LIKE :busqueda OR s.su_nombre LIKE :busqueda OR lm.lm_numero_lote LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(bp.bp_creado_en) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(bp.bp_creado_en) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $sql .= "
            ORDER BY bp.bp_creado_en DESC
            LIMIT :inicio, :registros
        ";

        $conexion = self::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $inicio = (int)$inicio;
        $registros = (int)$registros;
        $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
        $stmt->bindValue(':registros', $registros, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function contar_informes_cambios_precios_model($filtros = [])
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM balance_precios bp
            INNER JOIN lote_medicamento lm ON lm.lm_id = bp.lm_id
            INNER JOIN medicamento m ON m.med_id = lm.med_id
            INNER JOIN sucursales s ON s.su_id = lm.su_id
            LEFT JOIN usuarios u ON u.us_id = bp.us_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['su_id']) && $filtros['su_id'] > 0) {
            $sql .= " AND lm.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (m.med_nombre_quimico LIKE :busqueda OR u.us_nombres LIKE :busqueda OR s.su_nombre LIKE :busqueda OR lm.lm_numero_lote LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(bp.bp_creado_en) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(bp.bp_creado_en) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $conexion = self::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] ?? 0;
    }
}
