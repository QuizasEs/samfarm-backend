<?php

require_once "mainModel.php";

class inventarioModel extends mainModel
{
    protected static function datos_inventario_model($inicio, $registros, $filtros = [])
    {
        $sql = "
                    SELECT 
                        i.inv_id,
                        i.med_id,
                        i.su_id,
                        i.inv_total_cajas,
                        i.inv_total_unidades,
                        i.inv_total_valorado,
                        i.inv_minimo,
                        i.inv_maximo,
                        i.inv_actualizado_en,
                        m.med_nombre_quimico,
                        m.med_principio_activo,
                        m.med_presentacion,
                        COALESCE(la.la_nombre_comercial, 'Sin laboratorio') AS laboratorio,
                        COALESCE(ff.ff_nombre, '') AS forma_farmaceutica,
                        s.su_nombre AS sucursal_nombre,
                        
                        -- Estado del stock
                        CASE 
                            WHEN i.inv_total_unidades = 0 THEN 'agotado'
                            WHEN i.inv_minimo > 0 AND i.inv_total_unidades < i.inv_minimo THEN 'bajo'
                            WHEN i.inv_maximo > 0 AND i.inv_total_unidades > i.inv_maximo THEN 'exceso'
                            ELSE 'normal'
                        END AS estado_stock,
                        
                        -- Contar lotes activos
                        (SELECT COUNT(*) 
                        FROM lote_medicamento lm 
                        WHERE lm.med_id = i.med_id 
                        AND lm.su_id = i.su_id 
                        AND lm.lm_estado = 'activo'
                        ) AS lotes_activos,
                        
                        -- Lote más próximo a vencer
                        (SELECT MIN(lm.lm_fecha_vencimiento)
                        FROM lote_medicamento lm
                        WHERE lm.med_id = i.med_id
                        AND lm.su_id = i.su_id
                        AND lm.lm_estado = 'activo'
                        AND lm.lm_fecha_vencimiento IS NOT NULL
                        ) AS fecha_vencimiento_proximo
                        
                    FROM inventarios i
                    INNER JOIN medicamento m ON m.med_id = i.med_id
                    INNER JOIN sucursales s ON s.su_id = i.su_id
                    LEFT JOIN laboratorios la ON la.la_id = m.la_id
                    LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
                    WHERE 1=1
                ";

        $params = [];

        // Filtro por sucursal (CRÍTICO para permisos)
        if (!empty($filtros['su_id'])) {
            $sql .= " AND i.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        // Filtro por búsqueda
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                        m.med_nombre_quimico LIKE :busqueda OR
                        m.med_principio_activo LIKE :busqueda OR
                        m.med_codigo_barras LIKE :busqueda
                    )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        // Filtro por laboratorio
        if (!empty($filtros['laboratorio'])) {
            $sql .= " AND m.la_id = :laboratorio";
            $params[':laboratorio'] = (int)$filtros['laboratorio'];
        }

        // Filtro por estado de stock
        if (!empty($filtros['estado'])) {
            switch ($filtros['estado']) {
                case 'agotado':
                    $sql .= " AND i.inv_total_unidades = 0";
                    break;

                case 'critico':
                    $sql .= " AND i.inv_total_unidades > 0";
                    $sql .= " AND i.inv_minimo > 0";
                    $sql .= " AND i.inv_total_unidades < i.inv_minimo";
                    break;

                case 'bajo':
                    $sql .= " AND i.inv_total_unidades > 0";
                    $sql .= " AND i.inv_minimo > 0";
                    $sql .= " AND i.inv_total_unidades >= i.inv_minimo";
                    $sql .= " AND i.inv_total_unidades < (i.inv_minimo * 1.5)";
                    break;

                case 'normal':
                    $sql .= " AND i.inv_total_unidades > 0";
                    $sql .= " AND (
                        (i.inv_minimo > 0 AND i.inv_total_unidades >= (i.inv_minimo * 1.5))
                        OR
                        (i.inv_minimo IS NULL OR i.inv_minimo = 0)
                    )";
                    $sql .= " AND (i.inv_maximo IS NULL OR i.inv_maximo = 0 OR i.inv_total_unidades <= i.inv_maximo)";
                    break;

                case 'exceso':
                    $sql .= " AND i.inv_maximo > 0";
                    $sql .= " AND i.inv_total_unidades > i.inv_maximo";
                    break;

                case 'sin_definir':
                    $sql .= " AND (i.inv_minimo IS NULL OR i.inv_minimo = 0)";
                    $sql .= " AND i.inv_total_unidades > 0";
                    break;
            }
        }

        // Filtro por forma farmacéutica
        if (!empty($filtros['forma'])) {
            $sql .= " AND m.ff_id = :forma";
            $params[':forma'] = (int)$filtros['forma'];
        }

        // Ordenamiento
        $sql .= " ORDER BY 
                    CASE 
                        WHEN i.inv_total_unidades = 0 THEN 1
                        WHEN i.inv_minimo > 0 AND i.inv_total_unidades < i.inv_minimo THEN 2
                        ELSE 3
                    END,
                    m.med_nombre_quimico ASC
                ";

        $sql .= " LIMIT :inicio, :registros";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
        $stmt->bindParam(':registros', $registros, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }


    protected static function contar_inventarios_model($filtros = [])
    {
        $sql = "
                        SELECT COUNT(*) as total
                        FROM inventarios i
                        INNER JOIN medicamento m ON m.med_id = i.med_id
                        INNER JOIN sucursales s ON s.su_id = i.su_id
                        LEFT JOIN laboratorios la ON la.la_id = m.la_id
                        LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
                        WHERE 1=1
                    ";

        $params = [];

        if (!empty($filtros['su_id'])) {
            $sql .= " AND i.su_id = :su_id";
            $params[':su_id'] = (int)$filtros['su_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                            m.med_nombre_quimico LIKE :busqueda OR
                            m.med_principio_activo LIKE :busqueda OR
                            m.med_codigo_barras LIKE :busqueda
                        )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        if (!empty($filtros['laboratorio'])) {
            $sql .= " AND m.la_id = :laboratorio";
            $params[':laboratorio'] = (int)$filtros['laboratorio'];
        }

        if (!empty($filtros['estado'])) {
            switch ($filtros['estado']) {
                case 'agotado':
                    $sql .= " AND i.inv_total_unidades = 0";
                    break;
                case 'bajo':
                    $sql .= " AND i.inv_minimo > 0 AND i.inv_total_unidades < i.inv_minimo";
                    break;
                case 'exceso':
                    $sql .= " AND i.inv_maximo > 0 AND i.inv_total_unidades > i.inv_maximo";
                    break;
                case 'normal':
                    $sql .= " AND i.inv_total_unidades > 0";
                    $sql .= " AND (i.inv_minimo = 0 OR i.inv_total_unidades >= i.inv_minimo)";
                    $sql .= " AND (i.inv_maximo = 0 OR i.inv_total_unidades <= i.inv_maximo)";
                    break;
            }
        }

        if (!empty($filtros['forma'])) {
            $sql .= " AND m.ff_id = :forma";
            $params[':forma'] = (int)$filtros['forma'];
        }

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }

    protected static function detalle_inventario_con_lotes_model($inv_id)
    {
        $sql = "
            SELECT 
                i.*,
                m.med_nombre_quimico,
                m.med_principio_activo,
                m.med_presentacion,
                m.med_codigo_barras,
                m.med_accion_farmacologica,
                la.la_nombre_comercial AS laboratorio,
                ff.ff_nombre AS forma_farmaceutica,
                uf.uf_nombre AS uso_farmacologico,
                vd.vd_nombre AS via_administracion,
                s.su_nombre AS sucursal_nombre
            FROM inventarios i
            INNER JOIN medicamento m ON m.med_id = i.med_id
            INNER JOIN sucursales s ON s.su_id = i.su_id
            LEFT JOIN laboratorios la ON la.la_id = m.la_id
            LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
            LEFT JOIN uso_farmacologico uf ON uf.uf_id = m.uf_id
            LEFT JOIN via_de_administracion vd ON vd.vd_id = m.vd_id
            WHERE i.inv_id = :inv_id
            LIMIT 1
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':inv_id', $inv_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener lotes activos de un inventario
     */
    protected static function lotes_por_inventario_model($med_id, $su_id)
    {
        $sql = "
            SELECT 
                lm_id,
                lm_numero_lote,
                lm_cant_actual_cajas,
                lm_cant_actual_unidades,
                lm_precio_compra,
                lm_precio_venta,
                lm_fecha_ingreso,
                lm_fecha_vencimiento,
                lm_estado,
                DATEDIFF(lm_fecha_vencimiento, CURDATE()) AS dias_para_vencer
            FROM lote_medicamento
            WHERE med_id = :med_id 
            AND su_id = :su_id 
            AND lm_estado IN ('activo', 'terminado')
            ORDER BY lm_fecha_vencimiento ASC
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener historial de movimientos de inventario
     */
    protected static function historial_movimientos_inventario_model($med_id, $su_id, $limit = 20)
    {
        $sql = "
            SELECT 
                mi.mi_id,
                mi.mi_tipo,
                mi.mi_cantidad,
                mi.mi_unidad,
                mi.mi_referencia_tipo,
                mi.mi_referencia_id,
                mi.mi_motivo,
                mi.mi_creado_en,
                lm.lm_numero_lote,
                u.us_nombres,
                u.us_apellido_paterno
            FROM movimiento_inventario mi
            LEFT JOIN lote_medicamento lm ON lm.lm_id = mi.lm_id
            LEFT JOIN usuarios u ON u.us_id = mi.us_id
            WHERE mi.med_id = :med_id 
            AND mi.su_id = :su_id
            ORDER BY mi.mi_creado_en DESC
            LIMIT :limit
        ";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':med_id', $med_id, PDO::PARAM_INT);
        $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener todas las sucursales para transferencias
     */
    protected static function listar_sucursales_activas_model()
    {
        $sql = "SELECT su_id, su_nombre FROM sucursales WHERE su_estado = 1 ORDER BY su_nombre ASC";
        return mainModel::conectar()->query($sql);
    }

    /**
     * Exportar inventario a Excel
     */
    protected static function exportar_inventario_excel_model($su_id = null)
    {
        $sql = "
            SELECT 
                s.su_nombre AS 'Sucursal',
                m.med_nombre_quimico AS 'Medicamento',
                m.med_principio_activo AS 'Principio Activo',
                la.la_nombre_comercial AS 'Laboratorio',
                ff.ff_nombre AS 'Forma',
                i.inv_total_cajas AS 'Cajas',
                i.inv_total_unidades AS 'Unidades',
                i.inv_total_valorado AS 'Valorado (Bs)',
                i.inv_minimo AS 'Stock Mínimo',
                i.inv_maximo AS 'Stock Máximo',
                CASE 
                    WHEN i.inv_total_unidades = 0 THEN 'AGOTADO'
                    WHEN i.inv_minimo > 0 AND i.inv_total_unidades < i.inv_minimo THEN 'CRÍTICO'
                    WHEN i.inv_minimo > 0 AND i.inv_total_unidades < (i.inv_minimo * 1.5) THEN 'BAJO'
                    WHEN i.inv_maximo > 0 AND i.inv_total_unidades > i.inv_maximo THEN 'EXCESO'
                    ELSE 'NORMAL'
                END AS 'Estado',
                i.inv_actualizado_en AS 'Última Actualización'
            FROM inventarios i
            INNER JOIN medicamento m ON m.med_id = i.med_id
            INNER JOIN sucursales s ON s.su_id = i.su_id
            LEFT JOIN laboratorios la ON la.la_id = m.la_id
            LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
        ";

        if ($su_id !== null) {
            $sql .= " WHERE i.su_id = :su_id";
        }

        $sql .= " ORDER BY s.su_nombre, m.med_nombre_quimico";

        $stmt = mainModel::conectar()->prepare($sql);

        if ($su_id !== null) {
            $stmt->bindParam(':su_id', $su_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt;
    }

    protected static function actualizar_configuracion_inventario_model($inv_id, $inv_minimo, $inv_maximo)
    {
        try {
            $db = mainModel::conectar();

            $sql = "UPDATE inventarios 
                    SET inv_minimo = :inv_minimo,
                        inv_maximo = :inv_maximo,
                        inv_actualizado_en = NOW()
                    WHERE inv_id = :inv_id";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':inv_id', $inv_id, PDO::PARAM_INT);
            $stmt->bindParam(':inv_minimo', $inv_minimo, PDO::PARAM_INT);
            $stmt->bindParam(':inv_maximo', $inv_maximo, is_null($inv_maximo) ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->execute();

            error_log("Configuración actualizada: inv_id={$inv_id}, minimo={$inv_minimo}, maximo={$inv_maximo}");
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error en actualizar_configuracion_inventario_model: " . $e->getMessage());
            return false;
        }
    }
}
