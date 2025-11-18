<?php

require_once "mainModel.php";

class ventaModel extends mainModel
{
    /* modelo que se encarga de ejecutar la consulta de busqueda de cliente */
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


    /* buscar medicamentos con stock disponible en sucursal */
    protected static function buscar_medicamento_model($termino, $sucursal_id, $filtros = [])
    {
        if (!$sucursal_id) {
            return [];
        }

        $termino = "%$termino%";
        $conexion = mainModel::conectar();

        $sql = "
        SELECT DISTINCT
            m.med_id,
            m.med_nombre_quimico AS nombre,
            COALESCE(m.med_version_comercial, '') AS version_comercial,
            COALESCE(ff.ff_nombre, '') AS presentacion,
            COALESCE(la.la_nombre_comercial, '') AS linea,
            MIN(lm.lm_precio_venta) AS precio_venta,
            SUM(lm.lm_cant_actual_unidades) AS stock
        FROM medicamento m
        INNER JOIN lote_medicamento lm ON lm.med_id = m.med_id
        LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
        LEFT JOIN laboratorios la ON la.la_id = m.la_id
        WHERE lm.su_id = :sucursal_id
          AND lm.lm_estado = 'activo'
          AND lm.lm_cant_actual_unidades > 0
          AND (
              m.med_nombre_quimico LIKE :termino
              OR m.med_codigo_barras LIKE :termino
              OR m.med_version_comercial LIKE :termino
          )
    ";

        $params = [
            ":termino" => $termino,
            ":sucursal_id" => $sucursal_id
        ];

        // Aplicar filtros opcionales
        if (!empty($filtros['linea'])) {
            $sql .= " AND m.la_id = :la_id";
            $params[":la_id"] = $filtros['linea'];
        }
        if (!empty($filtros['presentacion'])) {
            $sql .= " AND m.ff_id = :ff_id";
            $params[":ff_id"] = $filtros['presentacion'];
        }
        if (!empty($filtros['funcion'])) {
            $sql .= " AND m.uf_id = :uf_id";
            $params[":uf_id"] = $filtros['funcion'];
        }
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = :vd_id";
            $params[":vd_id"] = $filtros['via'];
        }

        $sql .= " GROUP BY m.med_id, m.med_nombre_quimico, m.med_version_comercial, ff.ff_nombre, la.la_nombre_comercial
              HAVING SUM(lm.lm_cant_actual_unidades) > 0
              ORDER BY m.med_nombre_quimico ASC 
              LIMIT 50";

        $stmt = $conexion->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* buscar los productos mas vendidos de la sucursal */
    protected static function top_ventas_model($sucursal_id, $limit = 5)
    {
        if (!$sucursal_id) {
            return [];
        }

        $sql = mainModel::conectar()->prepare("
            SELECT
                m.med_id,
                m.med_nombre_quimico AS nombre,
                COALESCE(lm_ag.min_precio, 0) AS precio_venta,
                COALESCE(SUM(dv.dv_cantidad), 0) AS vendidos
            FROM medicamento m
            INNER JOIN detalle_venta dv ON dv.med_id = m.med_id
            INNER JOIN ventas v ON v.ve_id = dv.ve_id AND v.su_id = :sucursal_id
            LEFT JOIN (
                SELECT med_id, MIN(lm_precio_venta) AS min_precio
                FROM lote_medicamento
                WHERE lm_estado = 'activo' AND su_id = :sucursal_id2
                GROUP BY med_id
            ) lm_ag ON lm_ag.med_id = m.med_id
            WHERE lm_ag.min_precio IS NOT NULL AND lm_ag.min_precio > 0
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

    public static function guardar_venta_model($datos)
    {
        $db = mainModel::conectar();
        $stmt = $db->prepare("
            INSERT INTO ventas
                (ve_numero_documento, ve_fecha_emision, cl_id, us_id, su_id, ve_subtotal, ve_impuesto, ve_total, ve_tipo_documento, ve_estado, caja_id)
            VALUES
                (:ve_numero_documento, NOW(), :cl_id, :us_id, :su_id, :ve_subtotal, :ve_impuesto, :ve_total, :ve_tipo_documento, 1, :caja_id)
        ");
        $stmt->bindParam(":ve_numero_documento", $datos['ve_numero_documento']);
        $stmt->bindParam(":cl_id", $datos['cl_id']);
        $stmt->bindParam(":us_id", $datos['us_id']);
        $stmt->bindParam(":su_id", $datos['su_id']);
        $stmt->bindParam(":ve_subtotal", $datos['ve_subtotal']);
        $stmt->bindParam(":ve_impuesto", $datos['ve_impuesto']);
        $stmt->bindParam(":ve_total", $datos['ve_total']);
        $stmt->bindParam(":ve_tipo_documento", $datos['ve_tipo_documento']);
        $stmt->bindParam(":caja_id", $datos['caja_id']);
        $stmt->execute();
        return (int) $db->lastInsertId();
    }

    // Insertar detalle_venta
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

    // Actualizar lote: restar unidades (lm_cant_actual_unidades)
    public static function actualizar_lote_stock_model($lm_id, $cantidad)
    {
        if (!$lm_id || $cantidad <= 0) return false;
        $db = mainModel::conectar();

        // Restar unidades y ajustar cajas si es necesario (solo unidades para simplicidad)
        $stmt = $db->prepare("
            UPDATE lote_medicamento
            SET lm_cant_actual_unidades = lm_cant_actual_unidades - :cantidad,
                lm_cant_actual_cajas = FLOOR((lm_cant_actual_unidades - :cantidad) / COALESCE(NULLIF(lm_cant_unidad,0),1))
            WHERE lm_id = :lm_id AND lm_cant_actual_unidades >= :cantidad
        ");
        $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":lm_id", $lm_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Insertar movimiento_inventario
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
        $stmt->bindParam(":mi_referencia_tipo", $datos['mi_referencia_type']); // nota: clave en controller
        $stmt->bindParam(":mi_referencia_id", $datos['mi_referencia_id']);
        $stmt->bindParam(":mi_motivo", $datos['mi_motivo']);
        $stmt->execute();
        return $stmt;
    }

    // Registrar movimiento de caja (mc_tipo = 'venta' o 'salida')
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

    // Agregar informe (nota_venta)
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

    // Generar número de venta simple (puedes mejorar con prefijos, correlativos, etc.)
    public static function generar_numero_venta_model($sucursal_id)
    {
        // ejemplo: SU{su_id}-TS{timestamp}
        return "SU{$sucursal_id}-" . time();
    }

    // Sumar ventas por caja y por metodo de pago (ejemplo: 'efectivo')
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

    // Cerrar caja: actualizar registro caja
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
}
