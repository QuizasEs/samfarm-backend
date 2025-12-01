<?php
require_once "mainModel.php";

class transferirModel extends mainModel
{

    protected static function buscar_lotes_disponibles_model($su_id, $busqueda, $laboratorio, $fecha_venc_max)
    {
        $sql = "
                    SELECT 
                        lm.lm_id,
                        lm.lm_numero_lote,
                        lm.lm_cant_actual_cajas,
                        lm.lm_cant_actual_unidades,
                        lm.lm_cant_blister,
                        lm.lm_cant_unidad,
                        lm.lm_precio_compra,
                        lm.lm_precio_venta,
                        lm.lm_fecha_vencimiento,
                        lm.pr_id,
                        lm.pr_id_compra,
                        DATEDIFF(lm.lm_fecha_vencimiento, CURDATE()) AS dias_vencer,
                        m.med_id,
                        m.med_nombre_quimico,
                        m.med_principio_activo,
                        m.med_presentacion,
                        la.la_nombre_comercial AS laboratorio,
                        pr.pr_nombres AS proveedor
                    FROM lote_medicamento lm
                    INNER JOIN medicamento m ON m.med_id = lm.med_id
                    LEFT JOIN laboratorios la ON la.la_id = m.la_id
                    LEFT JOIN proveedores pr ON pr.pr_id = lm.pr_id
                    WHERE lm.su_id = :su_id
                    AND lm.lm_estado = 'activo'
                    AND lm.lm_cant_actual_unidades > 0
                ";

        $params = [':su_id' => $su_id];

        if ($busqueda) {
            $sql .= " AND (m.med_nombre_quimico LIKE :busqueda OR lm.lm_numero_lote LIKE :busqueda)";
            $params[':busqueda'] = "%{$busqueda}%";
        }

        if ($laboratorio) {
            $sql .= " AND m.la_id = :laboratorio";
            $params[':laboratorio'] = $laboratorio;
        }

        if ($fecha_venc_max) {
            $sql .= " AND lm.lm_fecha_vencimiento <= :fecha_max";
            $params[':fecha_max'] = $fecha_venc_max;
        }

        $sql .= " ORDER BY lm.lm_fecha_vencimiento ASC, lm.lm_numero_lote ASC";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    protected static function crear_transferencia_model($datos)
    {
        $items = json_decode($_POST['items_json'] ?? '[]', true);

        if (empty($items)) {
            throw new Exception("No hay items para procesar");
        }

        $destinos_unicos = array_unique(array_column($items, 'su_destino'));

        if (count($destinos_unicos) > 1) {
            throw new Exception("No se pueden transferir items a múltiples sucursales en una sola transferencia");
        }

        $su_destino_id = $destinos_unicos[0];

        $sql = "INSERT INTO transferencias 
                (tr_numero, su_origen_id, su_destino_id, us_emisor_id, tr_total_items, tr_observaciones)
                VALUES (:tr_numero, :su_origen_id, :su_destino_id, :us_emisor_id, :tr_total_items, :tr_observaciones)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':tr_numero' => $datos['tr_numero'],
            ':su_origen_id' => $datos['su_origen_id'],
            ':su_destino_id' => $su_destino_id,
            ':us_emisor_id' => $datos['us_emisor_id'],
            ':tr_total_items' => $datos['tr_total_items'],
            ':tr_observaciones' => $datos['tr_observaciones']
        ]);

        return mainModel::conectar()->lastInsertId();
    }

    protected static function insertar_detalle_transferencia_model($datos)
    {
        $sql = "INSERT INTO detalle_transferencia 
                        (tr_id, lm_origen_id, med_id, dt_numero_lote_origen, dt_cantidad_cajas, dt_cantidad_unidades, 
                        dt_precio_compra, dt_precio_venta, dt_subtotal_valorado)
                        VALUES (:tr_id, :lm_origen_id, :med_id, :dt_numero_lote_origen, :dt_cantidad_cajas, :dt_cantidad_unidades,
                        :dt_precio_compra, :dt_precio_venta, :dt_subtotal_valorado)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function datos_lote_transfer_model($lm_id)
    {
        $sql = "SELECT * FROM lote_medicamento WHERE lm_id = :lm_id LIMIT 1";
        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':lm_id' => $lm_id]);
        return $stmt;
    }

    protected static function descontar_stock_lote_model($lm_id, $cajas, $unidades)
    {
        $sql = "UPDATE lote_medicamento 
                        SET lm_cant_actual_cajas = lm_cant_actual_cajas - :cajas,
                            lm_cant_actual_unidades = lm_cant_actual_unidades - :unidades,
                            lm_actualizado_en = NOW()
                        WHERE lm_id = :lm_id 
                        AND lm_cant_actual_cajas >= :cajas
                        AND lm_cant_actual_unidades >= :unidades";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':cajas' => $cajas,
            ':unidades' => $unidades,
            ':lm_id' => $lm_id
        ]);

        if ($stmt->rowCount() == 0) {
            throw new Exception("No se pudo descontar stock del lote");
        }

        return $stmt;
    }

    protected static function descontar_inventario_model($med_id, $su_id, $cajas, $unidades, $valorado)
    {
        $sql = "UPDATE inventarios
                        SET inv_total_cajas = inv_total_cajas - :cajas,
                            inv_total_unidades = inv_total_unidades - :unidades,
                            inv_total_valorado = inv_total_valorado - :valorado,
                            inv_actualizado_en = NOW()
                        WHERE med_id = :med_id AND su_id = :su_id";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':cajas' => $cajas,
            ':unidades' => $unidades,
            ':valorado' => $valorado,
            ':med_id' => $med_id,
            ':su_id' => $su_id
        ]);

        return $stmt;
    }

    protected static function registrar_movimiento_inventario_model($datos)
    {
        $sql = "INSERT INTO movimiento_inventario
                        (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
                        VALUES (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function registrar_historial_lote_model($datos)
    {
        $sql = "INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
                        VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function actualizar_totales_transferencia_model($tr_id, $cajas, $unidades, $valorado)
    {
        $sql = "UPDATE transferencias
                        SET tr_total_cajas = :cajas,
                            tr_total_unidades = :unidades,
                            tr_total_valorado = :valorado
                        WHERE tr_id = :tr_id";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([
            ':cajas' => $cajas,
            ':unidades' => $unidades,
            ':valorado' => $valorado,
            ':tr_id' => $tr_id
        ]);

        return $stmt;
    }

    protected static function registrar_informe_model($datos)
    {
        $sql = "INSERT INTO informes (inf_nombre, inf_tipo, inf_usuario, inf_config)
                        VALUES (:inf_nombre, :inf_tipo, :inf_usuario, :inf_config)";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($datos);
        return $stmt;
    }

    protected static function obtener_ultimo_numero_transferencia_model()
    {
        $sql = "SELECT tr_numero FROM transferencias ORDER BY tr_id DESC LIMIT 1";
        $stmt = mainModel::conectar()->query($sql);
        $resultado = $stmt->fetch();
        return $resultado ? $resultado['tr_numero'] : null;
    }

    protected static function datos_transferencia_completa_model($tr_id)
    {
        $sql = "SELECT 
                            t.*,
                            so.su_nombre AS sucursal_origen,
                            sd.su_nombre AS sucursal_destino,
                            CONCAT(ue.us_nombres, ' ', ue.us_apellido_paterno) AS usuario_emisor,
                            CONCAT(ur.us_nombres, ' ', ur.us_apellido_paterno) AS usuario_receptor
                        FROM transferencias t
                        INNER JOIN sucursales so ON so.su_id = t.su_origen_id
                        INNER JOIN sucursales sd ON sd.su_id = t.su_destino_id
                        INNER JOIN usuarios ue ON ue.us_id = t.us_emisor_id
                        LEFT JOIN usuarios ur ON ur.us_id = t.us_receptor_id
                        WHERE t.tr_id = :tr_id
                        LIMIT 1";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':tr_id' => $tr_id]);
        return $stmt;
    }

    protected static function detalle_transferencia_model($tr_id)
    {
        $sql = "SELECT 
                            dt.*,
                            m.med_nombre_quimico
                        FROM detalle_transferencia dt
                        INNER JOIN medicamento m ON m.med_id = dt.med_id
                        WHERE dt.tr_id = :tr_id
                        ORDER BY dt.dt_id ASC";

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute([':tr_id' => $tr_id]);
        return $stmt;
    }

    protected static function listar_historial_transferencias_model(
        $pagina,
        $registros,
        $su_origen = '',
        $su_destino = '',
        $us_emisor = '',
        $estado = '',
        $fecha_desde = '',
        $fecha_hasta = '',
        $busqueda = '',
        $rol = 1,
        $su_usuario = ''
    ) {
        $inicio = ($pagina - 1) * $registros;
        $params = [];

        $sql = "SELECT 
                    t.tr_id,
                    t.tr_numero,
                    t.su_origen_id,
                    t.su_destino_id,
                    t.us_emisor_id,
                    t.us_receptor_id,
                    t.tr_total_items,
                    t.tr_total_cajas,
                    t.tr_total_unidades,
                    t.tr_total_valorado,
                    t.tr_estado,
                    t.tr_observaciones,
                    t.tr_motivo_rechazo,
                    t.tr_fecha_envio,
                    t.tr_fecha_respuesta,
                    so.su_nombre AS sucursal_origen,
                    sd.su_nombre AS sucursal_destino,
                    CONCAT(ue.us_nombres, ' ', ue.us_apellido_paterno) AS usuario_emisor,
                    CONCAT(ur.us_nombres, ' ', ur.us_apellido_paterno) AS usuario_receptor
                FROM transferencias t
                INNER JOIN sucursales so ON so.su_id = t.su_origen_id
                INNER JOIN sucursales sd ON sd.su_id = t.su_destino_id
                INNER JOIN usuarios ue ON ue.us_id = t.us_emisor_id
                LEFT JOIN usuarios ur ON ur.us_id = t.us_receptor_id
                WHERE 1=1";

        if ($rol != 1) {
            $sql .= " AND (t.su_origen_id = :su_usuario OR t.su_destino_id = :su_usuario)";
            $params[':su_usuario'] = $su_usuario;
        }

        if (!empty($su_origen)) {
            $sql .= " AND t.su_origen_id = :su_origen";
            $params[':su_origen'] = $su_origen;
        }

        if (!empty($su_destino)) {
            $sql .= " AND t.su_destino_id = :su_destino";
            $params[':su_destino'] = $su_destino;
        }

        if (!empty($us_emisor)) {
            $sql .= " AND t.us_emisor_id = :us_emisor";
            $params[':us_emisor'] = $us_emisor;
        }

        if (!empty($estado)) {
            $sql .= " AND t.tr_estado = :estado";
            $params[':estado'] = $estado;
        }

        if (!empty($fecha_desde)) {
            $sql .= " AND DATE(t.tr_fecha_envio) >= :fecha_desde";
            $params[':fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta)) {
            $sql .= " AND DATE(t.tr_fecha_envio) <= :fecha_hasta";
            $params[':fecha_hasta'] = $fecha_hasta;
        }

        if (!empty($busqueda)) {
            $sql .= " AND t.tr_numero LIKE :busqueda";
            $params[':busqueda'] = "%{$busqueda}%";
        }

        $sql .= " ORDER BY t.tr_fecha_envio DESC LIMIT :inicio, :registros";
        $params[':inicio'] = $inicio;
        $params[':registros'] = $registros;

        $stmt = mainModel::conectar()->prepare($sql);
        foreach ($params as $key => $value) {
            if (strpos($key, 'inicio') !== false || strpos($key, 'registros') !== false) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt;
    }

    protected static function contar_historial_transferencias_model(
        $su_origen = '',
        $su_destino = '',
        $us_emisor = '',
        $estado = '',
        $fecha_desde = '',
        $fecha_hasta = '',
        $busqueda = '',
        $rol = 1,
        $su_usuario = ''
    ) {
        $params = [];

        $sql = "SELECT COUNT(*) as total
                FROM transferencias t
                INNER JOIN sucursales so ON so.su_id = t.su_origen_id
                INNER JOIN sucursales sd ON sd.su_id = t.su_destino_id
                INNER JOIN usuarios ue ON ue.us_id = t.us_emisor_id
                LEFT JOIN usuarios ur ON ur.us_id = t.us_receptor_id
                WHERE 1=1";

        if ($rol != 1) {
            $sql .= " AND (t.su_origen_id = :su_usuario OR t.su_destino_id = :su_usuario)";
            $params[':su_usuario'] = $su_usuario;
        }

        if (!empty($su_origen)) {
            $sql .= " AND t.su_origen_id = :su_origen";
            $params[':su_origen'] = $su_origen;
        }

        if (!empty($su_destino)) {
            $sql .= " AND t.su_destino_id = :su_destino";
            $params[':su_destino'] = $su_destino;
        }

        if (!empty($us_emisor)) {
            $sql .= " AND t.us_emisor_id = :us_emisor";
            $params[':us_emisor'] = $us_emisor;
        }

        if (!empty($estado)) {
            $sql .= " AND t.tr_estado = :estado";
            $params[':estado'] = $estado;
        }

        if (!empty($fecha_desde)) {
            $sql .= " AND DATE(t.tr_fecha_envio) >= :fecha_desde";
            $params[':fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta)) {
            $sql .= " AND DATE(t.tr_fecha_envio) <= :fecha_hasta";
            $params[':fecha_hasta'] = $fecha_hasta;
        }

        if (!empty($busqueda)) {
            $sql .= " AND t.tr_numero LIKE :busqueda";
            $params[':busqueda'] = "%{$busqueda}%";
        }

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = isset($resultado['total']) ? (int)$resultado['total'] : 0;
        return $total;
    }
}
