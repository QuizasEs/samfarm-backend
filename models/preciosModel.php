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
    public static function actualizar_precio_lote_individual_model($lm_id, $precio_nuevo, $usuario_id, $su_id, $med_id)
    {
        $conexion = self::conectar();
        
        try {
            $conexion->beginTransaction();

            // 1) Obtener precio anterior
            $sql_get = $conexion->prepare("SELECT lm_precio_venta FROM lote_medicamento WHERE lm_id = :lm_id");
            $sql_get->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
            $sql_get->execute();
            $resultado = $sql_get->fetch(PDO::FETCH_ASSOC);
            $precio_anterior = $resultado['lm_precio_venta'] ?? 0;

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

            // 4) Registrar informe
            self::registrar_informe_cambio_precio_model(
                $usuario_id,
                $med_id,
                $su_id,
                'lote_individual',
                $lm_id,
                $precio_anterior,
                $precio_nuevo,
                1
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
    public static function actualizar_precio_todos_lotes_model($med_id, $precio_nuevo, $usuario_id, $su_id)
    {
        $conexion = self::conectar();
        
        try {
            $conexion->beginTransaction();

            // 1) Obtener lotes actuales para el histórico
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

            // 4) Registrar informe (consolidado)
            $precio_anterior_promedio = 0;
            foreach ($lotes as $lote) {
                $precio_anterior_promedio += $lote['lm_precio_venta'];
            }
            $precio_anterior_promedio = $cantidad_lotes > 0 ? $precio_anterior_promedio / $cantidad_lotes : 0;

            self::registrar_informe_cambio_precio_model(
                $usuario_id,
                $med_id,
                $su_id,
                'todos_lotes',
                null,
                $precio_anterior_promedio,
                $precio_nuevo,
                $cantidad_lotes
            );

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

    /**
     * REGISTRAR INFORME DE CAMBIO DE PRECIO
     */
    private static function registrar_informe_cambio_precio_model($usuario_id, $med_id, $su_id, $tipo, $lm_id, $precio_anterior, $precio_nuevo, $cantidad_lotes)
    {
        $sql = "
            INSERT INTO informes 
            (inf_usuario, inf_config, inf_creado_en)
            VALUES 
            (:inf_usuario, :inf_config, NOW())
        ";

        $contenido = [
            'tipo_cambio' => $tipo,
            'med_id' => $med_id,
            'su_id' => $su_id,
            'lm_id' => $lm_id,
            'precio_anterior' => (float)$precio_anterior,
            'precio_nuevo' => (float)$precio_nuevo,
            'cantidad_lotes_afectados' => (int)$cantidad_lotes,
            'usuario_id' => (int)$usuario_id,
            'fecha_cambio' => date('Y-m-d H:i:s')
        ];

        $conexion = self::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':inf_usuario', $usuario_id, PDO::PARAM_INT);
        $contenido_json = json_encode($contenido);
        $stmt->bindParam(':inf_config', $contenido_json, PDO::PARAM_STR);
        
        $resultado = $stmt->execute();
        
        if ($resultado) {
            error_log("✓ Informe registrado: tipo=$tipo, med_id=$med_id, su_id=$su_id, usuario=$usuario_id, config=" . substr($contenido_json, 0, 100));
        } else {
            error_log("✗ Error registrando informe: " . json_encode($stmt->errorInfo()));
        }
        
        return $resultado;
    }

    /**
     * OBTENER INFORMES DE CAMBIOS DE PRECIOS
     */
    public static function obtener_informes_cambios_precios_model($inicio = 0, $registros = 10, $filtros = [])
    {
        $sql = "
            SELECT 
                inf.inf_id,
                inf.inf_config,
                inf.inf_creado_en,
                u.us_nombres,
                u.us_apellido_paterno,
                u.us_apellido_materno,
                m.med_nombre_quimico,
                s.su_nombre
            FROM informes inf
            LEFT JOIN usuarios u ON u.us_id = inf.inf_usuario
            LEFT JOIN medicamento m ON JSON_EXTRACT(inf.inf_config, '$.med_id') = m.med_id
            LEFT JOIN sucursales s ON s.su_id = JSON_EXTRACT(inf.inf_config, '$.su_id')
            WHERE JSON_EXTRACT(inf.inf_config, '$.tipo_cambio') IN ('lote_individual', 'todos_lotes')
        ";

        $params = [];

        if (!empty($filtros['su_id']) && $filtros['su_id'] > 0) {
            $sql .= " AND JSON_EXTRACT(inf.inf_config, '$.su_id') = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (m.med_nombre_quimico LIKE :busqueda OR u.us_nombres LIKE :busqueda OR s.su_nombre LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(inf.inf_creado_en) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(inf.inf_creado_en) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $sql .= "
            ORDER BY inf.inf_creado_en DESC
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

    /**
     * CONTAR TOTAL DE INFORMES
     */
    public static function contar_informes_cambios_precios_model($filtros = [])
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM informes inf
            LEFT JOIN usuarios u ON u.us_id = inf.inf_usuario
            LEFT JOIN medicamento m ON JSON_EXTRACT(inf.inf_config, '$.med_id') = m.med_id
            LEFT JOIN sucursales s ON s.su_id = JSON_EXTRACT(inf.inf_config, '$.su_id')
            WHERE JSON_EXTRACT(inf.inf_config, '$.tipo_cambio') IN ('lote_individual', 'todos_lotes')
        ";

        $params = [];

        if (!empty($filtros['su_id']) && $filtros['su_id'] > 0) {
            $sql .= " AND JSON_EXTRACT(inf.inf_config, '$.su_id') = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (m.med_nombre_quimico LIKE :busqueda OR u.us_nombres LIKE :busqueda OR s.su_nombre LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(inf.inf_creado_en) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(inf.inf_creado_en) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        $conexion = self::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $resultado['total'] ?? 0;
        
        error_log("DEBUG: contar_informes_cambios_precios_model - total=$total, filtros=" . json_encode($filtros));
        
        return $total;
    }
}
