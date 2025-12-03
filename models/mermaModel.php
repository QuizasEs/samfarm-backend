<?php

require_once "mainModel.php";

class mermaModel extends mainModel
{
    /**
     * Crear una merma (registro definitivo)
     * Al crear merma, el lote se marca como 'caducado' automáticamente
     * También se actualiza la tabla inventarios restando las unidades del lote caducado
     */
    protected static function crear_merma_model($med_id, $lm_id, $su_id, $us_id, $me_cantidad, $me_motivo)
    {
        try {
            $conexion = mainModel::conectar();
            $conexion->beginTransaction();

            $sql_insert = "
                INSERT INTO merma (med_id, lm_id, su_id, us_id, me_cantidad, me_motivo, me_fecha)
                VALUES (:med_id, :lm_id, :su_id, :us_id, :me_cantidad, :me_motivo, NOW())
            ";

            $stmt = $conexion->prepare($sql_insert);
            $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $stmt->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
            $stmt->bindParam(':us_id', $us_id, PDO::PARAM_INT);
            $stmt->bindParam(':me_cantidad', $me_cantidad, PDO::PARAM_INT);
            $stmt->bindParam(':me_motivo', $me_motivo, PDO::PARAM_STR);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Insert failed: " . json_encode($stmt->errorInfo()));
            }

            $sql_update_lote = "
                UPDATE lote_medicamento 
                SET lm_estado = 'caducado'
                WHERE lm_id = :lm_id
            ";

            $stmt = $conexion->prepare($sql_update_lote);
            $stmt->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Update lote failed: " . json_encode($stmt->errorInfo()));
            }

            $sql_actualizar_inventario = "
                UPDATE inventarios 
                SET inv_total_unidades = GREATEST(0, inv_total_unidades - :me_cantidad),
                    inv_total_cajas = GREATEST(0, inv_total_cajas - CEIL(:me_cantidad / COALESCE((
                        SELECT (lm_cant_blister * lm_cant_unidad) 
                        FROM lote_medicamento 
                        WHERE lm_id = :lm_id
                    ), 1))),
                    inv_actualizado_en = NOW()
                WHERE med_id = :med_id AND su_id = :su_id
            ";

            $stmt = $conexion->prepare($sql_actualizar_inventario);
            $stmt->bindParam(':me_cantidad', $me_cantidad, PDO::PARAM_INT);
            $stmt->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
            $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Update inventarios failed: " . json_encode($stmt->errorInfo()));
            }

            $sql_movimiento = "
                INSERT INTO movimiento_inventario 
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo, mi_creado_en, mi_estado)
                VALUES (:lm_id, :med_id, :su_id, :us_id, 'salida', :me_cantidad, 'unidad', 'merma', 
                    (SELECT me_id FROM merma WHERE med_id = :med_id AND lm_id = :lm_id ORDER BY me_fecha DESC LIMIT 1),
                    CONCAT('Merma: ', :me_motivo), NOW(), 1)
            ";

            $stmt = $conexion->prepare($sql_movimiento);
            $stmt->bindParam(':lm_id', $lm_id, PDO::PARAM_INT);
            $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
            $stmt->bindParam(':us_id', $us_id, PDO::PARAM_INT);
            $stmt->bindParam(':me_cantidad', $me_cantidad, PDO::PARAM_INT);
            $stmt->bindParam(':me_motivo', $me_motivo, PDO::PARAM_STR);
            $result = $stmt->execute();

            if (!$result) {
                throw new Exception("Insert movimiento failed: " . json_encode($stmt->errorInfo()));
            }

            $conexion->commit();
            error_log("Merma created successfully: med_id=$med_id, lm_id=$lm_id, quantity=$me_cantidad");
            return true;
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("ERROR creating merma: " . $e->getMessage() . " | med_id=$med_id, lm_id=$lm_id, su_id=$su_id, us_id=$us_id, me_cantidad=$me_cantidad");
            return false;
        }
    }

    /**
     * Obtener todas las mermas registradas
     */
    protected static function obtener_todas_mermas_model($inicio = 0, $registros = 20, $filtros = [])
    {
        $sql = "
            SELECT 
                m.me_id,
                m.med_id,
                m.lm_id,
                m.su_id,
                m.us_id,
                m.me_cantidad,
                m.me_motivo,
                m.me_fecha,
                med.med_nombre_quimico,
                med.med_presentacion,
                lm.lm_numero_lote,
                lm.lm_fecha_vencimiento,
                lm.lm_cant_actual_unidades,
                s.su_nombre,
                u.us_nombres,
                u.us_apellido_paterno,
                la.la_nombre_comercial AS laboratorio
            FROM merma m
            INNER JOIN medicamento med ON med.med_id = m.med_id
            INNER JOIN lote_medicamento lm ON lm.lm_id = m.lm_id
            INNER JOIN sucursales s ON s.su_id = m.su_id
            INNER JOIN usuarios u ON u.us_id = m.us_id
            LEFT JOIN laboratorios la ON la.la_id = med.la_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND m.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (med.med_nombre_quimico LIKE :busqueda OR lm.lm_numero_lote LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(m.me_fecha) BETWEEN :fecha_desde AND :fecha_hasta";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " ORDER BY m.me_fecha DESC LIMIT :inicio, :registros";

        try {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
            $stmt->bindParam(':registros', $registros, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error obtaining mermas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Contar total de mermas
     */
    protected static function contar_mermas_model($filtros = [])
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM merma m
            INNER JOIN medicamento med ON med.med_id = m.med_id
            INNER JOIN lote_medicamento lm ON lm.lm_id = m.lm_id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND m.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (med.med_nombre_quimico LIKE :busqueda OR lm.lm_numero_lote LIKE :busqueda)";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
            $sql .= " AND DATE(m.me_fecha) BETWEEN :fecha_desde AND :fecha_hasta";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        try {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$resultado['total'];
        } catch (PDOException $e) {
            error_log("Error counting mermas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtener detalle de una merma
     */
    protected static function obtener_detalle_merma_model($me_id)
    {
        $sql = "
            SELECT 
                m.*,
                med.med_nombre_quimico,
                med.med_presentacion,
                med.med_principio_activo,
                lm.lm_numero_lote,
                lm.lm_fecha_vencimiento,
                lm.lm_cant_actual_unidades,
                s.su_nombre,
                u.us_nombres,
                u.us_apellido_paterno,
                la.la_nombre_comercial AS laboratorio
            FROM merma m
            INNER JOIN medicamento med ON med.med_id = m.med_id
            INNER JOIN lote_medicamento lm ON lm.lm_id = m.lm_id
            INNER JOIN sucursales s ON s.su_id = m.su_id
            INNER JOIN usuarios u ON u.us_id = m.us_id
            LEFT JOIN laboratorios la ON la.la_id = med.la_id
            WHERE m.me_id = :me_id
            LIMIT 1
        ";

        try {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':me_id', $me_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtaining merma details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener mermas recientes (últimas N horas)
     */
    protected static function obtener_mermas_recientes_model($horas = 24)
    {
        $sql = "
            SELECT 
                m.me_id,
                med.med_nombre_quimico,
                lm.lm_numero_lote,
                s.su_nombre,
                m.me_cantidad,
                m.me_fecha
            FROM merma m
            INNER JOIN medicamento med ON med.med_id = m.med_id
            INNER JOIN lote_medicamento lm ON lm.lm_id = m.lm_id
            INNER JOIN sucursales s ON s.su_id = m.su_id
            WHERE m.me_fecha >= DATE_SUB(NOW(), INTERVAL :horas HOUR)
            ORDER BY m.me_fecha DESC
        ";

        try {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':horas', $horas, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obtaining recent mermas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Detectar automáticamente lotes caducados o próximos a vencer
     * NO crea mermas, solo detecta. El gerente/admin decide si crear la merma
     * Excluye lotes que ya han sido registrados en merma
     */
    protected static function detectar_lotes_caducados_model()
    {
        $sql = "
            SELECT 
                lm.lm_id,
                med.med_id,
                med.med_nombre_quimico,
                lm.lm_numero_lote,
                lm.lm_fecha_vencimiento,
                lm.lm_cant_actual_unidades,
                s.su_id,
                s.su_nombre
            FROM lote_medicamento lm
            INNER JOIN medicamento med ON med.med_id = lm.med_id
            INNER JOIN sucursales s ON s.su_id = lm.su_id
            WHERE lm.lm_estado = 'activo'
            AND lm.lm_cant_actual_unidades > 0
            AND (
                lm.lm_fecha_vencimiento <= CURDATE()
                OR lm.lm_fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 10 DAY)
            )
            AND lm.lm_id NOT IN (
                SELECT DISTINCT lm_id FROM merma WHERE lm_id IS NOT NULL
            )
            ORDER BY lm.lm_fecha_vencimiento ASC
        ";

        try {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error detecting expired lots: " . $e->getMessage());
            return [];
        }
    }
}
