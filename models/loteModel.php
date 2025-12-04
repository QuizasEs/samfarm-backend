<?php

require_once "mainModel.php";

class loteModel extends mainModel
{
    /* modelo que optiene datos del lote  */
    public static function datos_lote_model($id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT 
                lm.lm_id,
                lm.med_id,
                lm.su_id,
                lm.pr_id,
                lm.lm_numero_lote,
                lm.lm_cant_caja,
                lm.lm_cant_blister,
                lm.lm_cant_unidad,
                lm.lm_total_unidades,
                lm.lm_cant_actual_cajas,
                lm.lm_cant_actual_unidades,
                lm.lm_precio_compra,
                lm.lm_precio_venta,
                lm.lm_fecha_ingreso,
                lm.lm_fecha_vencimiento,
                lm.lm_estado,
                lm.lm_creado_en,
                lm.lm_actualizado_en,
                lm.lm_origen_id,
                m.med_nombre_quimico AS med_nombre,
                m.med_principio_activo,
                m.med_presentacion,
                m.med_accion_farmacologica,
                m.med_codigo_barras,
                m.med_version_comercial,
                ff.ff_nombre AS forma_farmaceutica,
                uf.uf_nombre AS uso_farmacologico,
                vd.vd_nombre AS via_administracion,
                la.la_nombre_comercial AS laboratorio_nombre,
                p.pr_nombres AS proveedor_nombres,
                p.pr_apellido_paterno AS proveedor_apellido,
                s.su_nombre AS sucursal_nombre
            FROM lote_medicamento lm
            LEFT JOIN medicamento m ON lm.med_id = m.med_id
            LEFT JOIN forma_farmaceutica ff ON m.ff_id = ff.ff_id
            LEFT JOIN uso_farmacologico uf ON m.uf_id = uf.uf_id
            LEFT JOIN via_de_administracion vd ON m.vd_id = vd.vd_id
            LEFT JOIN laboratorios la ON m.la_id = la.la_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            LEFT JOIN sucursales s ON lm.su_id = s.su_id
            WHERE lm.lm_id = :ID
            LIMIT 1;

        ");
        $sql->bindParam(":ID", $id);
        $sql->execute();
        return $sql;
    }

    protected static function actualizar_lote_model($datos)
    {
        $sql = self::conectar()->prepare("
            UPDATE lote_medicamento
            SET
                lm_cant_blister = :lm_cant_blister,
                lm_cant_unidad = :lm_cant_unidad,
                lm_precio_compra = :lm_precio_compra,
                lm_precio_venta = :lm_precio_venta,
                lm_fecha_vencimiento = :lm_fecha_vencimiento,
                lm_actualizado_en = NOW(),
                lm_origen_id = :lm_origen_id
            WHERE lm_id = :ID
        ");

        $sql->bindParam(":lm_cant_blister", $datos['lm_cant_blister']);
        $sql->bindParam(":lm_cant_unidad", $datos['lm_cant_unidad']);
        $sql->bindParam(":lm_precio_compra", $datos['lm_precio_compra']);
        $sql->bindParam(":lm_precio_venta", $datos['lm_precio_venta']);
        $sql->bindParam(":lm_fecha_vencimiento", $datos['lm_fecha_vencimiento']);
        $sql->bindParam(":lm_origen_id", $datos['lm_origen_id']);
        $sql->bindParam(":ID", $datos['ID']);

        $sql->execute();
        return $sql;
    }
    protected static function activar_lote_model($datos)
    {
        $db = self::conectar();

        try {
            $db->beginTransaction();

            // 1) Obtener información completa del lote
            $consulta_lote = $db->prepare("
            SELECT lm.*, m.med_nombre_quimico
            FROM lote_medicamento lm
            INNER JOIN medicamento m ON m.med_id = lm.med_id
            WHERE lm.lm_id = :lm_id AND lm.lm_estado = :parametro
        ");
            $consulta_lote->execute([
                ':lm_id' => $datos['lm_id'],
                ':parametro' => $datos['parametro']
            ]);

            if ($consulta_lote->rowCount() <= 0) {
                throw new Exception("Lote no encontrado o no está en estado '{$datos['parametro']}'");
            }

            $lote = $consulta_lote->fetch(PDO::FETCH_ASSOC);
            $med_id = (int)$lote['med_id'];
            $su_id = (int)$lote['su_id'];
            $lm_cant_actual_cajas = (int)$lote['lm_cant_actual_cajas'];
            $lm_cant_actual_unidades = (int)$lote['lm_cant_actual_unidades'];
            $lm_precio_venta = (float)$lote['lm_precio_venta'];
            $subtotal_lote = $lm_cant_actual_unidades * $lm_precio_venta;
            $numero_lote = $lote['lm_numero_lote'];

            // 2) Actualizar estado del lote a 'activo'
            $sql = $db->prepare("
            UPDATE `lote_medicamento` 
            SET `lm_estado` = :lm_estado,
                `lm_actualizado_en` = NOW()
            WHERE lm_id = :ID AND lm_estado = :parametro
        ");
            $sql->execute([
                ':lm_estado' => $datos['lm_estado'],
                ':ID' => $datos['lm_id'],
                ':parametro' => $datos['parametro']
            ]);

            if ($sql->rowCount() <= 0) {
                throw new Exception("No se pudo actualizar el estado del lote");
            }

            // 3) ✅ ACTUALIZAR INVENTARIO CONSOLIDADO (ESTO FALTABA)
            $datos_inventario = [
                "su_id" => $su_id,
                "med_id" => $med_id,
                "inv_total_cajas" => $lm_cant_actual_cajas,
                "inv_total_unidades" => $lm_cant_actual_unidades,
                "inv_total_valorado" => $subtotal_lote
            ];

            $inv_result = self::actualizar_inventario_model($datos_inventario);

            if (!$inv_result) {
                throw new Exception("No se pudo actualizar el inventario consolidado");
            }

            // 4) ✅ REGISTRAR MOVIMIENTO DE INVENTARIO (ESTO FALTABA)
            $datos_movimiento = [
                "lm_id" => $datos['lm_id'],
                "med_id" => $med_id,
                "su_id" => $su_id,
                "us_id" => $datos['us_id'],
                "mi_tipo" => "entrada",
                "mi_cantidad" => $lm_cant_actual_unidades,
                "mi_unidad" => "unidad",
                "mi_referencia_tipo" => "activacion_lote",
                "mi_referencia_id" => $datos['lm_id'],
                "mi_motivo" => "Activación manual de lote {$numero_lote}"
            ];

            $mov_result = self::registro_movimiento_inventario_model($datos_movimiento);

            if ($mov_result->rowCount() <= 0) {
                throw new Exception("No se pudo registrar el movimiento de inventario");
            }

            // 5) Commit de la transacción
            $db->commit();

            error_log("LOTE ACTIVADO CORRECTAMENTE: lm_id={$datos['lm_id']}, med_id={$med_id}, unidades={$lm_cant_actual_unidades}");

            return $sql;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("ERROR en activar_lote_model: " . $e->getMessage());
            return false;
        }
    }

    /* Registro en historial_lote */
    public static function registrar_historial_lote_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
            VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion)
        ");
        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":hl_accion", $datos['hl_accion']);
        $sql->bindParam(":hl_descripcion", $datos['hl_descripcion']);
        $sql->execute();
        return $sql;
    }
    /* insertar y/o actualizar inventario  */

    public static function actualizar_inventario_model($datos)
    {
        $sql = mainModel::conectar()->prepare(
            "INSERT INTO inventarios (su_id, med_id, inv_total_cajas, inv_total_unidades, inv_total_valorado, inv_creado_en, inv_actualizado_en)
            VALUES (:su_id, :med_id, :inv_total_cajas, :inv_total_unidades, :inv_total_valorado, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                inv_total_cajas = inv_total_cajas + VALUES(inv_total_cajas),
                inv_total_unidades = inv_total_unidades + VALUES(inv_total_unidades),
                inv_total_valorado = inv_total_valorado + VALUES(inv_total_valorado),
                inv_actualizado_en = NOW()
            "
        );

        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":inv_total_cajas", $datos['inv_total_cajas']);
        $sql->bindParam(":inv_total_unidades", $datos['inv_total_unidades']);
        $sql->bindParam(":inv_total_valorado", $datos['inv_total_valorado']);

        $sql->execute();
        return $sql;
    }
    /* insertar movimientos para inventario modelo */
    public static function registro_movimiento_inventario_model($datos)
    {
        $db = mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad,  mi_referencia_tipo, mi_referencia_id, mi_motivo)
            VALUES
                (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)
        ");

        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":mi_tipo", $datos['mi_tipo']);
        $sql->bindParam(":mi_cantidad", $datos['mi_cantidad']);
        $sql->bindParam(":mi_unidad", $datos['mi_unidad']);
        $sql->bindParam(":mi_referencia_tipo", $datos['mi_referencia_tipo']);
        $sql->bindParam(":mi_referencia_id", $datos['mi_referencia_id']);
        $sql->bindParam(":mi_motivo", $datos['mi_motivo']);

        $sql->execute();
        return $sql;
    }


    /* registrar un informe en informes ocn el tipo "compra" */
    public static function agregar_informe_compra_model($datos)
    {
        $sql = mainModel::conectar()->prepare(
            "INSERT INTO informes (inf_nombre, inf_tipo, inf_usuario, inf_config)
            VALUES (:inf_nombre, 'Activacion', :inf_usuario, :inf_config)
            "
        );
        $sql->bindParam(":inf_nombre", $datos['inf_nombre']);
        $sql->bindParam(":inf_usuario", $datos['inf_usuario']);
        $sql->bindParam(":inf_config", $datos['inf_config']);

        $sql->execute();
        return $sql;
    }
}
