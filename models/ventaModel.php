<?php

require_once "mainModel.php";



class ventaModel extends mainModel
{

    /* Obtener lotes activos por medicamento y sucursal */
    public static function obtener_lotes_activos_por_med_sucursal_model($med_id, $sucursal_id)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
                SELECT lm_id, lm_cant_actual_unidades, lm_cant_actual_cajas, lm_cant_blister, lm_cant_unidad, lm_precio_venta, lm_precio_compra
                FROM lote_medicamento
                WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo' AND lm_cant_actual_unidades > 0
                ORDER BY lm_fecha_ingreso ASC, lm_fecha_vencimiento ASC
            ");
        $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* Buscar cliente por término de búsqueda */
    protected static function buscar_cliente_model($termino)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT *
            FROM clientes
            WHERE 
                cl_estado = 1 AND 
                (
                    cl_nombres LIKE :term OR
                    cl_apellido_paterno LIKE :term OR
                    cl_apellido_materno LIKE :term OR
                    cl_telefono LIKE :term OR
                    cl_carnet  LIKE :term
                )
            ORDER BY cl_nombres ASC
            LIMIT 10
        ");

        $sql->bindValue(":term", "%$termino%");
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }


    /* Buscar medicamento con stock disponible en sucursal */
    protected static function buscar_medicamento_model($termino, $sucursal_id, $filtros = [])
    {
        if (!$sucursal_id) {
            return [];
        }

        $termino = "%$termino%";
        $conexion = mainModel::conectar();

        $sql = "
        SELECT 
            m.med_id,
            lm.lm_id,
            lm.lm_numero_lote,
            m.med_nombre_quimico AS nombre,
            COALESCE(m.med_version_comercial, '') AS version_comercial,
            COALESCE(ff.ff_nombre, '') AS presentacion,
            COALESCE(la.la_nombre_comercial, '') AS linea,
            lm.lm_precio_venta AS precio_venta,
            lm.lm_cant_actual_unidades AS stock,
            DATE_FORMAT(lm.lm_fecha_vencimiento, '%Y-%m-%d') AS fecha_vencimiento
        FROM lote_medicamento lm
        INNER JOIN medicamento m ON m.med_id = lm.med_id
        LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
        LEFT JOIN laboratorios la ON la.la_id = m.la_id
        WHERE lm.su_id = :sucursal_id
          AND lm.lm_estado = 'activo'
          AND lm.lm_cant_actual_unidades > 0
          AND (
              m.med_nombre_quimico LIKE :termino
              OR m.med_codigo_barras LIKE :termino
              OR m.med_version_comercial LIKE :termino
              OR lm.lm_numero_lote LIKE :termino
          )
    ";

        $params = [
            ":termino" => $termino,
            ":sucursal_id" => $sucursal_id
        ];

        // Aplicar filtros opcionales
        if (!empty($filtros['linea'])) {
            $sql .= " AND m.la_id = :la_id";
            $params[":la_id"] = (int)$filtros['linea'];
        }
        if (!empty($filtros['presentacion'])) {
            $sql .= " AND m.ff_id = :ff_id";
            $params[":ff_id"] = (int)$filtros['presentacion'];
        }
        if (!empty($filtros['funcion'])) {
            $sql .= " AND m.uf_id = :uf_id";
            $params[":uf_id"] = (int)$filtros['funcion'];
        }
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = :vd_id";
            $params[":vd_id"] = (int)$filtros['via'];
        }

        // Ordenar: nombre, laboratorio, precio (más barato primero), vencimiento
        $sql .= " ORDER BY 
                m.med_nombre_quimico ASC,
                la.la_nombre_comercial ASC,
                lm.lm_precio_venta ASC,
                lm.lm_fecha_vencimiento ASC
              LIMIT 50";

        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* Obtener los productos más vendidos de la sucursal */
    protected static function top_ventas_model($sucursal_id, $limit = 5)
    {
        if (!$sucursal_id) {
            return [];
        }

        $sql = mainModel::conectar()->prepare("
            SELECT
                m.med_id,
                m.med_nombre_quimico AS nombre,
                lm.lm_id AS lote_id,
                lm.lm_numero_lote AS lote,
                COALESCE(ff.ff_nombre, '') AS presentacion,
                COALESCE(la.la_nombre_comercial, '') AS linea,
                lm.lm_precio_venta AS precio_venta,
                COALESCE(SUM(dv.dv_cantidad), 0) AS vendidos,
                lm.lm_cant_actual_unidades AS stock
            FROM medicamento m
            INNER JOIN detalle_venta dv ON dv.med_id = m.med_id
            INNER JOIN ventas v ON v.ve_id = dv.ve_id AND v.su_id = :sucursal_id
            INNER JOIN lote_medicamento lm ON lm.med_id = m.med_id AND lm.su_id = :sucursal_id2 AND lm.lm_estado = 'activo' AND lm.lm_precio_venta = (
                SELECT MIN(lm2.lm_precio_venta)
                FROM lote_medicamento lm2
                WHERE lm2.med_id = m.med_id AND lm2.su_id = :sucursal_id2 AND lm2.lm_estado = 'activo'
            )
            LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
            LEFT JOIN laboratorios la ON la.la_id = m.la_id
            WHERE lm.lm_precio_venta IS NOT NULL AND lm.lm_precio_venta > 0
            GROUP BY m.med_id
            ORDER BY vendidos DESC
            LIMIT :limit
        ");

        $sql->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $sql->bindValue(":sucursal_id", (int)$sucursal_id, PDO::PARAM_INT);
        $sql->bindValue(":sucursal_id2", (int)$sucursal_id, PDO::PARAM_INT);

        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /* Abrir una nueva caja */
    protected static function abrir_caja_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO `caja`
            (
                `su_id`, 
                `us_id`, 
                `caja_nombre`, 
                `caja_saldo_inicial`
            ) 
            VALUES 
            (
                :su_id,
                :us_id,
                :caja_nombre,
                :caja_saldo_inicial
            )
        ");

        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":caja_nombre", $datos['caja_nombre']);
        $sql->bindParam(":caja_saldo_inicial", $datos['caja_saldo_inicial']);

        $sql->execute();
        return $sql;
    }

    /* Consultar si un usuario tiene una caja activa en una sucursal */
    protected static function consulta_caja_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
                SELECT * 
                    FROM caja 
                    WHERE us_id = :us_id 
                    AND su_id = :su_id 
                    AND caja_activa = 1 
                    AND caja_cerrado_en IS NULL
        ");
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":su_id", $datos['su_id']);


        $sql->execute();
        return $sql;
    }

    /* Guardar una nueva venta */
    public static function guardar_venta_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            INSERT INTO ventas
                (ve_numero_documento, ve_fecha_emision, cl_id, us_id, su_id, ve_subtotal, ve_impuesto, ve_total, ve_tipo_documento, ve_estado,ve_metodo_pago, caja_id)
            VALUES
                (:ve_numero_documento, NOW(), :cl_id, :us_id, :su_id, :ve_subtotal, :ve_impuesto, :ve_total, :ve_tipo_documento, 1,:ve_metodo_pago, :caja_id)
        ");
        $stmt->bindParam(":ve_numero_documento", $datos['ve_numero_documento']);
        $stmt->bindParam(":cl_id", $datos['cl_id']);
        $stmt->bindParam(":us_id", $datos['us_id']);
        $stmt->bindParam(":su_id", $datos['su_id']);
        $stmt->bindParam(":ve_subtotal", $datos['ve_subtotal']);
        $stmt->bindParam(":ve_impuesto", $datos['ve_impuesto']);
        $stmt->bindParam(":ve_total", $datos['ve_total']);
        $stmt->bindParam(":ve_tipo_documento", $datos['ve_tipo_documento']);
        $stmt->bindParam(":ve_metodo_pago", $datos['ve_metodo_pago']);
        $stmt->bindParam(":caja_id", $datos['caja_id']);
        $stmt->execute();
        return (int) $db->lastInsertId();
    }

    /* Agregar un ítem al detalle de una venta */
    public static function agregar_detalle_venta_model($item)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            INSERT INTO detalle_venta
                (ve_id, med_id, lm_id, dv_cantidad, dv_unidad, dv_precio_unitario, dv_descuento, dv_subtotal)
            VALUES
                (:ve_id, :med_id, :lm_id, :dv_cantidad, :dv_unidad, :dv_precio_unitario, :dv_descuento, :dv_subtotal)
        ");
        $stmt->bindParam(":ve_id", $item['ve_id']);
        $stmt->bindParam(":med_id", $item['med_id']);
        $stmt->bindParam(":lm_id", $item['lm_id']);
        $stmt->bindParam(":dv_cantidad", $item['dv_cantidad']);
        $stmt->bindParam(":dv_unidad", $item['dv_unidad']);
        $stmt->bindParam(":dv_precio_unitario", $item['dv_precio_unitario']);
        $stmt->bindParam(":dv_descuento", $item['dv_descuento']);
        $stmt->bindParam(":dv_subtotal", $item['dv_subtotal']);
        $stmt->execute();
        return $stmt;
    }

    /* Agregar un informe de venta */
    public static function agregar_informe_venta_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            INSERT INTO informes
            (inf_nombre, inf_tipo, inf_usuario, inf_config)
            VALUES
            (:inf_nombre, 'nota_venta', :inf_usuario, :inf_config)
        ");
        $stmt->bindParam(":inf_nombre", $datos['inf_nombre']);
        $stmt->bindParam(":inf_usuario", $datos['inf_usuario']);
        $stmt->bindParam(":inf_config", $datos['inf_config']);
        $stmt->execute();
        return $stmt;
    }

    /* Generar un número de documento para la venta */
    public static function generar_numero_venta_model($sucursal_id)
    {
        // ejemplo: SU{su_id}-TS{timestamp}
        return "SU{$sucursal_id}-" . time();
    }

    /* Sumar el total de ventas en efectivo para una caja */
    public static function sumar_ventas_por_caja_model($caja_id, $metodo = 'efectivo')
    {
        $db = mainModel::conectar();
        // Asumiendo que almacenas metodo_pago en movimiento_caja o en informes. Si no, adaptar.
        // Aquí sumamos movimientos mc_tipo='venta' y filtramos por caja.
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(mc_monto),0) as total
            FROM movimiento_caja
            WHERE caja_id = :caja_id AND mc_tipo = 'venta'
        ");
        $stmt->bindParam(":caja_id", $caja_id);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)$r['total'];
    }

    /* Cerrar una caja actualizando su estado y saldo final */
    public static function cerrar_caja_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            UPDATE caja
            SET caja_saldo_final = :caja_saldo_final,
                caja_cerrado_en = :caja_cerrado_en,
                caja_activa = 0
            WHERE caja_id = :caja_id
        ");
        $stmt->bindParam(":caja_saldo_final", $datos['caja_saldo_final']);
        $stmt->bindParam(":caja_cerrado_en", $datos['caja_cerrado_en']);
        $stmt->bindParam(":caja_id", $datos['caja_id']);
        $stmt->execute();
        return $stmt;
    }

    /* Sumar el stock total de unidades para un medicamento en una sucursal */
    public static function sumar_stock_lotes_med_sucursal_model($med_id, $sucursal_id)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(lm_cant_actual_unidades),0) AS total_unidades
            FROM lote_medicamento
            WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo'
        ");
        $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$r['total_unidades'];
    }

    /* Obtener el factor de conversión de unidades por caja y blister */
    public static function obtener_factor_unidades_por_tipo_model($med_id, $sucursal_id)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            SELECT lm_cant_blister, lm_cant_unidad
            FROM lote_medicamento
            WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo' AND lm_cant_actual_unidades > 0
            LIMIT 1
        ");
        $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) return false;
        $blister = max(1, (int)$r['lm_cant_blister']);
        $por_unidad = max(1, (int)$r['lm_cant_unidad']);
        return [
            'unidades_por_blister' => $por_unidad,
            'unidades_por_caja' => $blister * $por_unidad
        ];
    }

    /* Descontar unidades de un lote específico y ajustar cajas */
    public static function descontar_unidades_lote_model($lm_id, $cantidad_unidades)
    {
        if ($cantidad_unidades <= 0) return false;
        $db = mainModel::conectar();

        // Bloqueamos la fila para evitar race conditions
        $stmt = $db->prepare("
            SELECT lm_cant_actual_unidades, lm_cant_actual_cajas, 
                lm_cant_blister, lm_cant_unidad 
            FROM lote_medicamento 
            WHERE lm_id = :lm_id 
            FOR UPDATE
        ");
        $stmt->bindParam(":lm_id", $lm_id, PDO::PARAM_INT);
        $stmt->execute();
        $lm = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lm) return false;

        $unidades_antes = (int)$lm['lm_cant_actual_unidades'];
        $blister = max(1, (int)$lm['lm_cant_blister']);
        $por_blister = max(1, (int)$lm['lm_cant_unidad']);
        $unidades_por_caja = $blister * $por_blister;

        // Validar stock suficiente
        if ($unidades_antes < $cantidad_unidades) return false;

        // Calcular nuevos valores
        $unidades_despues = $unidades_antes - $cantidad_unidades;
        $cajas_despues = (int)floor($unidades_despues / $unidades_por_caja);

        // Actualizar lote
        $upd = $db->prepare("
            UPDATE lote_medicamento
            SET lm_cant_actual_unidades = :unidades_despues,
                lm_cant_actual_cajas = :cajas_despues,
                lm_actualizado_en = NOW()
            WHERE lm_id = :lm_id
        ");
        $upd->bindParam(":unidades_despues", $unidades_despues, PDO::PARAM_INT);
        $upd->bindParam(":cajas_despues", $cajas_despues, PDO::PARAM_INT);
        $upd->bindParam(":lm_id", $lm_id, PDO::PARAM_INT);
        $upd->execute();

        return $upd->rowCount() > 0;
    }

    /* Verificar si un lote se ha agotado para cambiar su estado a 'terminado' */
    public static function verificar_estado_lote_terminado_model($lm_id)
    {
        if ($lm_id <= 0) return false;

        $db = mainModel::conectar();

        try {
            // Consultar estado actual del lote
            $check_stmt = $db->prepare("
                    SELECT lm_cant_actual_unidades, lm_estado 
                    FROM lote_medicamento 
                    WHERE lm_id = :lm_id
                ");
            $check_stmt->bindParam(":lm_id", $lm_id, PDO::PARAM_INT);
            $check_stmt->execute();

            $lote = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$lote) return false;

            $unidades = (int)$lote['lm_cant_actual_unidades'];
            $estado_actual = $lote['lm_estado'];

            // Si llegó a 0 unidades y está activo, cambiar a terminado
            if ($unidades === 0 && $estado_actual === 'activo') {
                $update_stmt = $db->prepare("
                        UPDATE lote_medicamento 
                        SET lm_estado = 'terminado',
                            lm_actualizado_en = NOW()
                        WHERE lm_id = :lm_id
                    ");
                $update_stmt->bindParam(":lm_id", $lm_id, PDO::PARAM_INT);
                $update_stmt->execute();

                // Log para debugging
                error_log("✅ Lote #{$lm_id} actualizado a estado 'terminado' (stock agotado)");

                return $update_stmt->rowCount() > 0;
            }

            return false;
        } catch (PDOException $e) {
            error_log("❌ Error verificando estado lote: " . $e->getMessage());
            return false;
        }
    }

    /* Agregar un registro al historial de movimientos de inventario */
    public static function agregar_movimiento_inventario_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            INSERT INTO movimiento_inventario
            (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
            VALUES
            (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)
        ");
        $stmt->bindParam(":lm_id", $datos['lm_id']);
        $stmt->bindParam(":med_id", $datos['med_id']);
        $stmt->bindParam(":su_id", $datos['su_id']);
        $stmt->bindParam(":us_id", $datos['us_id']);
        $stmt->bindParam(":mi_tipo", $datos['mi_tipo']);
        $stmt->bindParam(":mi_cantidad", $datos['mi_cantidad']);
        $stmt->bindParam(":mi_unidad", $datos['mi_unidad']);
        $stmt->bindParam(":mi_referencia_tipo", $datos['mi_referencia_tipo']);
        $stmt->bindParam(":mi_referencia_id", $datos['mi_referencia_id']);
        $stmt->bindParam(":mi_motivo", $datos['mi_motivo']);
        $stmt->execute();
        return $stmt;
    }

    /* Registrar un movimiento de dinero en una caja */
    public static function registrar_movimiento_caja_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            INSERT INTO movimiento_caja
            (caja_id, us_id, mc_tipo, mc_monto, mc_concepto, mc_referencia_tipo, mc_referencia_id)
            VALUES
            (:caja_id, :us_id, :mc_tipo, :mc_monto, :mc_concepto, :mc_referencia_tipo, :mc_referencia_id)
        ");
        $stmt->bindParam(":caja_id", $datos['caja_id']);
        $stmt->bindParam(":us_id", $datos['us_id']);
        $stmt->bindParam(":mc_tipo", $datos['mc_tipo']);
        $stmt->bindParam(":mc_monto", $datos['mc_monto']);
        $stmt->bindParam(":mc_concepto", $datos['mc_concepto']);
        $stmt->bindParam(":mc_referencia_tipo", $datos['mc_referencia_tipo']);
        $stmt->bindParam(":mc_referencia_id", $datos['mc_referencia_id']);
        $stmt->execute();
        return $stmt;
    }

    /* Recalcular el inventario consolidado a partir de los lotes activos */
    public static function recalcular_inventario_por_med_sucursal_model($med_id, $sucursal_id)
    {
        $db = mainModel::conectar();

        // 1) Sumar unidades y calcular valorado
        $stmt = $db->prepare("SELECT COALESCE(SUM(lm_cant_actual_unidades),0) AS tot_unidades, COALESCE(SUM(lm_cant_actual_unidades * lm_precio_compra),0) AS tot_valorado FROM lote_medicamento WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo'");
        $stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        $tot_unidades = (int)$r['tot_unidades'];
        $tot_valorado = (float)$r['tot_valorado'];

        // 2) Determinar factor de caja por primer lote activo (fallback 1)
        $stmt2 = $db->prepare("SELECT lm_cant_blister, lm_cant_unidad FROM lote_medicamento WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo' AND lm_cant_actual_unidades > 0 LIMIT 1");
        $stmt2->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt2->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
        $stmt2->execute();
        $ref = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($ref) {
            $blister = max(1, (int)$ref['lm_cant_blister']);
            $por_unidad = max(1, (int)$ref['lm_cant_unidad']);
            $unidades_por_caja = $blister * $por_unidad;
        } else {
            $unidades_por_caja = 1;
        }

        $cajas = (int)floor($tot_unidades / $unidades_por_caja);

        // 3) Upsert en inventarios (asume unique su_id,med_id)
        // Verificar si existe
        $stmt3 = $db->prepare("SELECT inv_id FROM inventarios WHERE med_id = :med_id AND su_id = :su_id");
        $stmt3->bindParam(":med_id", $med_id, PDO::PARAM_INT);
        $stmt3->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
        $stmt3->execute();
        $row = $stmt3->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $upd = $db->prepare("UPDATE inventarios SET inv_total_unidades = :tot_unidades, inv_total_cajas = :cajas, inv_total_valorado = :tot_valorado, inv_actualizado_en = NOW() WHERE inv_id = :inv_id");
            $upd->bindParam(":tot_unidades", $tot_unidades, PDO::PARAM_INT);
            $upd->bindParam(":cajas", $cajas, PDO::PARAM_INT);
            $upd->bindParam(":tot_valorado", $tot_valorado);
            $upd->bindParam(":inv_id", $row['inv_id'], PDO::PARAM_INT);
            $upd->execute();
            return $upd->rowCount() > 0;
        } else {
            $ins = $db->prepare("INSERT INTO inventarios (med_id, su_id, inv_total_cajas, inv_total_unidades, inv_total_valorado, inv_creado_en) VALUES (:med_id, :su_id, :cajas, :tot_unidades, :tot_valorado, NOW())");
            $ins->bindParam(":med_id", $med_id, PDO::PARAM_INT);
            $ins->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
            $ins->bindParam(":cajas", $cajas, PDO::PARAM_INT);
            $ins->bindParam(":tot_unidades", $tot_unidades, PDO::PARAM_INT);
            $ins->bindParam(":tot_valorado", $tot_valorado);
            $ins->execute();
            return $ins->rowCount() > 0;
        }
    }

    /* Insertar una nueva factura asociada a una venta */
    public static function insertar_factura_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            INSERT INTO factura (ve_id, cl_id, us_id, su_id, fa_numero, fa_monto_total, fa_creado_en)
            VALUES (:ve_id, :cl_id, :us_id, :su_id, :fa_numero, :fa_monto_total, NOW())
        ");
        $stmt->bindParam(":ve_id", $datos['ve_id']);
        $stmt->bindParam(":cl_id", $datos['cl_id']);
        $stmt->bindParam(":us_id", $datos['us_id']);
        $stmt->bindParam(":su_id", $datos['su_id']);
        $stmt->bindParam(":fa_numero", $datos['fa_numero']);
        $stmt->bindParam(":fa_monto_total", $datos['fa_monto_total']);
        $stmt->execute();
        return (int)$db->lastInsertId();
    }

    /* Generar un número único para una factura */
    public static function generar_numero_factura_model($sucursal_id)
    {
        return "F-" . $sucursal_id . "-" . date('YmdHis') . "-" . rand(100, 999);
    }

    /* Descontar el inventario consolidado tras una venta */
    public static function descontar_inventario_consolidado_model($med_id, $sucursal_id, $cantidad_unidades, $valorado_descuento = 0)
    {
        if ($cantidad_unidades <= 0) return false;
        $db = mainModel::conectar();

        try {
            // 1) Verificar si existe inventario
            $check_stmt = $db->prepare("
                SELECT inv_id, inv_total_unidades, inv_total_cajas, inv_total_valorado
                FROM inventarios 
                WHERE med_id = :med_id AND su_id = :su_id
            ");
            $check_stmt->bindParam(":med_id", $med_id, PDO::PARAM_INT);
            $check_stmt->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
            $check_stmt->execute();
            $inv = $check_stmt->fetch(PDO::FETCH_ASSOC);

            // 2) Si NO existe, crearlo primero recalculando desde lotes
            if (!$inv) {
                $recalc_ok = self::recalcular_inventario_por_med_sucursal_model($med_id, $sucursal_id);
                if (!$recalc_ok) {
                    error_log("ERROR: No se pudo crear inventario inicial para med_id={$med_id}, su_id={$sucursal_id}");
                    return false;
                }

                // Volver a consultar después de crear
                $check_stmt->execute();
                $inv = $check_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$inv) {
                    error_log("ERROR: Inventario no existe después de recalcular para med_id={$med_id}");
                    return false;
                }
            }

            // 3) Bloquear fila para actualización
            $lock_stmt = $db->prepare("
                SELECT inv_id, inv_total_unidades, inv_total_cajas, inv_total_valorado
                FROM inventarios 
                WHERE inv_id = :inv_id 
                FOR UPDATE
            ");
            $lock_stmt->bindParam(":inv_id", $inv['inv_id'], PDO::PARAM_INT);
            $lock_stmt->execute();
            $inv = $lock_stmt->fetch(PDO::FETCH_ASSOC);

            $inv_id = (int)$inv['inv_id'];
            $unidades_antes = (int)$inv['inv_total_unidades'];
            $valorado_antes = (float)$inv['inv_total_valorado'];

            // 4) Validar stock suficiente
            if ($unidades_antes < $cantidad_unidades) {
                error_log("ERROR: Stock insuficiente en inventario. med_id={$med_id}, disponible={$unidades_antes}, requerido={$cantidad_unidades}");
                return false;
            }

            // 5) Calcular nuevas cantidades
            $unidades_despues = $unidades_antes - $cantidad_unidades;
            $valorado_despues = max(0, $valorado_antes - $valorado_descuento);

            // 6) Obtener factor de conversión para cajas
            $stmt2 = $db->prepare("
                SELECT lm_cant_blister, lm_cant_unidad 
                FROM lote_medicamento 
                WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo' AND lm_cant_actual_unidades > 0 
                LIMIT 1
            ");
            $stmt2->bindParam(":med_id", $med_id, PDO::PARAM_INT);
            $stmt2->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
            $stmt2->execute();
            $ref = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($ref) {
                $blister = max(1, (int)$ref['lm_cant_blister']);
                $por_unidad = max(1, (int)$ref['lm_cant_unidad']);
                $unidades_por_caja = $blister * $por_unidad;
            } else {
                $unidades_por_caja = 1;
            }

            $cajas_despues = (int)floor($unidades_despues / $unidades_por_caja);

            // 7) Actualizar inventario
            $upd = $db->prepare("
                UPDATE inventarios 
                SET inv_total_unidades = :unidades_despues, 
                    inv_total_cajas = :cajas_despues, 
                    inv_total_valorado = :valorado_despues,
                    inv_actualizado_en = NOW()
                WHERE inv_id = :inv_id
            ");
            $upd->bindParam(":unidades_despues", $unidades_despues, PDO::PARAM_INT);
            $upd->bindParam(":cajas_despues", $cajas_despues, PDO::PARAM_INT);
            $upd->bindParam(":valorado_despues", $valorado_despues);
            $upd->bindParam(":inv_id", $inv_id, PDO::PARAM_INT);
            $upd->execute();

            $rows_affected = $upd->rowCount();

            if ($rows_affected > 0) {
                error_log("SUCCESS: Inventario actualizado. med_id={$med_id}, unidades: {$unidades_antes} -> {$unidades_despues}, valorado: {$valorado_antes} -> {$valorado_despues}");
                return true;
            } else {
                error_log("WARNING: UPDATE no afectó filas. med_id={$med_id}, inv_id={$inv_id}");
                return false;
            }
        } catch (PDOException $e) {
            error_log("ERROR PDO en descontar_inventario_consolidado_model: " . $e->getMessage());
            return false;
        }
    }

    /* Generar el PDF de una nota de venta o factura */
    public function generar_pdf_factura_model($fa_id, $tipo = 'nota_venta')
    {
        $fa_id = (int)$fa_id;
        if ($fa_id <= 0) return false;

        try {
            $root = dirname(__DIR__);

            require_once $root . "/libs/fpdf/fpdf.php";

            // Conectar a BD
            $db = mainModel::conectar();

            $sql = "
            SELECT f.*,
                v.ve_numero_documento, v.ve_total, v.ve_subtotal, v.ve_fecha_emision,
                c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno, c.cl_carnet,
                u.us_nombres, u.us_apellido_paterno,
                s.su_nombre
            FROM factura f
            INNER JOIN ventas v ON v.ve_id = f.ve_id
            LEFT JOIN clientes c ON c.cl_id = f.cl_id
            INNER JOIN usuarios u ON u.us_id = f.us_id
            INNER JOIN sucursales s ON s.su_id = f.su_id
            WHERE f.fa_id = :fa_id
        ";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":fa_id", $fa_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() <= 0) {
                error_log("❌ No se encontró factura con ID: {$fa_id}");
                return false;
            }

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            $ve_id = (int)$data['ve_id'];

            $sql2 = "
            SELECT dv.*,
                m.med_nombre_quimico AS med_nombre,
                COALESCE(m.med_version_comercial, '') AS version_comercial,
                COALESCE(ff.ff_nombre, '') AS presentacion
            FROM detalle_venta dv
            INNER JOIN medicamento m ON m.med_id = dv.med_id
            LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
            WHERE dv.ve_id = :ve_id
        ";
            $stmt2 = $db->prepare($sql2);
            $stmt2->bindParam(":ve_id", $ve_id, PDO::PARAM_INT);
            $stmt2->execute();
            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $cfg_sql = "SELECT * FROM configuracion_empresa ORDER BY ce_id DESC LIMIT 1";
            $cfg_stmt = $db->prepare($cfg_sql);
            $cfg_stmt->execute();
            $empresa = $cfg_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$empresa) {
                $empresa = ['ce_nombre' => 'SAMFARM', 'ce_nit' => 'S/N', 'ce_direccion' => '', 'ce_telefono' => '', 'ce_logo' => null];
            }

            return $this->generar_pdf_nota_venta($data, $detalles, $empresa);

        } catch (Exception $e) {
            error_log("❌ Error generando PDF: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /* Generar el PDF de reimpresión de nota de venta desde historial (sin fa_id) */
    public function generar_pdf_factura_historial_model($ve_id, $tipo = 'nota_venta')
    {
        $ve_id = (int)$ve_id;
        if ($ve_id <= 0) return false;

        try {
            $root = dirname(__DIR__);

            require_once $root . "/libs/fpdf/fpdf.php";

            // Conectar a BD
            $db = mainModel::conectar();

            // Consulta similar pero sin dependencia de factura
            $sql = "
            SELECT
                v.ve_id, v.ve_numero_documento, v.ve_total, v.ve_subtotal, v.ve_impuesto, v.ve_fecha_emision, v.ve_tipo_documento,
                c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno, c.cl_carnet,
                u.us_nombres, u.us_apellido_paterno,
                s.su_nombre
            FROM ventas v
            LEFT JOIN clientes c ON c.cl_id = v.cl_id
            INNER JOIN usuarios u ON u.us_id = v.us_id
            INNER JOIN sucursales s ON s.su_id = v.su_id
            WHERE v.ve_id = :ve_id
        ";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":ve_id", $ve_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() <= 0) {
                error_log("❌ No se encontró venta con ID: {$ve_id}");
                return false;
            }

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql2 = "
            SELECT dv.*,
                m.med_nombre_quimico AS med_nombre,
                COALESCE(m.med_version_comercial, '') AS version_comercial,
                COALESCE(ff.ff_nombre, '') AS presentacion
            FROM detalle_venta dv
            INNER JOIN medicamento m ON m.med_id = dv.med_id
            LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
            WHERE dv.ve_id = :ve_id
        ";
            $stmt2 = $db->prepare($sql2);
            $stmt2->bindParam(":ve_id", $ve_id, PDO::PARAM_INT);
            $stmt2->execute();
            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $cfg_sql = "SELECT * FROM configuracion_empresa ORDER BY ce_id DESC LIMIT 1";
            $cfg_stmt = $db->prepare($cfg_sql);
            $cfg_stmt->execute();
            $empresa = $cfg_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$empresa) {
                $empresa = ['ce_nombre' => 'SAMFARM', 'ce_nit' => 'S/N', 'ce_direccion' => '', 'ce_telefono' => '', 'ce_logo' => null];
            }

            return $this->generar_pdf_nota_venta($data, $detalles, $empresa);

        } catch (Exception $e) {
            error_log("❌ Error generando PDF reimpresión: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /* Método común para generar el PDF de nota de venta */
    private function generar_pdf_nota_venta($data, $detalles, $empresa)
    {
        // Calcular altura dinámica
        $altura_base = 95;
        $altura_items = 0;
        $line_height = 4;
        $char_per_line = 45;

        foreach ($detalles as $d) {
            $nombre_producto = $d['med_nombre'] ?? 'Producto';
            $lineas_nombre = ceil(mb_strlen($nombre_producto, 'UTF-8') / $char_per_line);
            $altura_items += $lineas_nombre * $line_height;
        }

        $altura_total = $altura_base + $altura_items;

        $pdf = new FPDF('P', 'mm', [80, $altura_total]);
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);

        // Encabezado empresa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, ($empresa['ce_nombre']), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, 'Sucursal: ' . ($data['su_nombre']), 0, 1, 'C');
        $pdf->MultiCell(0, 4, ($empresa['ce_direccion']), 0, 'C');
        $pdf->Cell(0, 4, 'Telf: ' . $empresa['ce_telefono'], 0, 1, 'C');
        $pdf->Cell(0, 4, 'NIT: ' . $empresa['ce_nit'], 0, 1, 'C');
        $pdf->Ln(3);

        // Título
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 5, 'NOTA DE VENTA', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, '-------------------------------------------------------------------', 0, 1, 'C');

        // Datos venta
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'N Venta:', 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, $data['ve_numero_documento'], 0, 1);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'Fecha:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, date('d/m/Y H:i', strtotime($data['ve_fecha_emision'])), 0, 1);

        $nombre_cliente = trim(
            ($data['cl_nombres'] ?? '') . ' ' .
                ($data['cl_apellido_paterno'] ?? '') . ' ' .
                ($data['cl_apellido_materno'] ?? '')
        );
        if (empty($nombre_cliente)) $nombre_cliente = 'Sin Cliente';

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'Cliente:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->MultiCell(0, 4, ($nombre_cliente));

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'CI/NIT:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $data['cl_carnet'] ?? 'S/N', 0, 1);

        $vendedor_nombre = trim(($data['us_nombres'] ?? '') . ' ' . ($data['us_apellido_paterno'] ?? ''));
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'Vendedor:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, ($vendedor_nombre), 0, 1);

        $pdf->Ln(2);

        // Tabla detalles
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(35, 5, 'Producto', 'B', 0, 'L');
        $pdf->Cell(10, 5, 'Cant.', 'B', 0, 'C');
        $pdf->Cell(12, 5, 'P.U.', 'B', 0, 'R');
        $pdf->Cell(13, 5, 'Subtotal', 'B', 1, 'R');

        $pdf->SetFont('Arial', '', 7);
        foreach ($detalles as $d) {
            $y_actual = $pdf->GetY();
            $x_actual = $pdf->GetX();

            $pdf->MultiCell(35, 4, ($d['med_nombre']), 0, 'L');
            $y_despues = $pdf->GetY();

            $pdf->SetXY($x_actual + 35, $y_actual);
            $pdf->Cell(10, 4, $d['dv_cantidad'], 0, 0, 'C');
            $pdf->Cell(12, 4, number_format($d['dv_precio_unitario'], 2), 0, 0, 'R');
            $pdf->Cell(13, 4, number_format($d['dv_subtotal'], 2), 0, 1, 'R');
            $pdf->SetY($y_despues);
        }
        $pdf->Cell(0, 4, '-------------------------------------------------------------------', 0, 1, 'C');

        // Totales
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(45, 5, 'Subtotal:', 0, 0, 'R');
        $pdf->Cell(25, 5, 'Bs. ' . number_format($data['ve_subtotal'], 2), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(45, 6, 'TOTAL A PAGAR:', 0, 0, 'R');
        $pdf->Cell(25, 6, 'Bs. ' . number_format($data['ve_total'], 2), 0, 1, 'R');

        $pdf->Ln(5);

        // Pie
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->Cell(0, 4, '¡Gracias por su compra!', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Este documento es una nota de venta.', 0, 1, 'C');

        $contenido_pdf = $pdf->Output('S');
        $pdf_base64 = base64_encode($contenido_pdf);

        error_log("PDF generado exitosamente para venta #{$data['ve_id']}");
        return $pdf_base64;
    }
}
