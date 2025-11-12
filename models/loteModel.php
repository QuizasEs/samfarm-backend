<?php

require_once "mainModel.php";

class loteModel extends mainModel
{
    /* modelo que optiene datos del lote  */
    protected static function datos_lote_model($id)
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
                m.med_precio_unitario,
                m.med_precio_caja,
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
    protected static function activar_lote_model($datos)
    {
        /* en casoq que se requirea */
        /* lm_cantidad_actual = :Cantidad, */
        $sql = mainModel::conectar()->prepare("
            UPDATE lote_medicamento
                SET
                    
                    lm_precio_venta = :PrecioVenta,
                    lm_estado = 'activo',
                    lm_actualizado_en = NOW()
                WHERE lm_id = :ID
            ");
        /* $sql->bindParam(":Cantidad", $datos['Cantidad'], PDO::PARAM_INT); */
        $sql->bindParam(":PrecioVenta", $datos['PrecioVenta']);
        $sql->bindParam(":ID", $datos['ID'], PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }
    /* registrar historial de lote */
    protected static function registrar_hitorial_lote_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
            VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion)
        ");
        $sql->bindParam(":lm_id",   $datos['LoteID']);
        $sql->bindParam(":us_id", $datos['UsuarioID']);
        $sql->bindParam(":hl_accion", $datos['Accion']);
        $sql->bindParam(":hl_descripcion", $datos['Descripcion']);
        $sql->execute();
        return $sql;
    }
    /* insertar y/o actualizar inventario  */

    protected static function actualizar_inventario_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
        INSERT INTO `inventarios`
        (`med_id`, `su_id`, `lm_id`, `inv_cantidad`, `inv_minimo`, 
        `inv_maximo`, `inv_ultimo_precio`, `inv_actualizado_en`, `inv_creado_en`) 
        VALUES 
        (:med_id,:su_id,:lm_id,:inv_cantidad,:inv_minimo,:inv_maximo,
        :inv_ultimo_precio,NOW(),NOW())
        "
        );

        $sql->bindParam(":med_id", $datos['MedID']);
        $sql->bindParam(":su_id", $datos['SuID']);
        $sql->bindParam(":lm_id", $datos['LoteID']);
        $sql->bindParam(":inv_cantidad", $datos['Cantidad']);
        $sql->bindParam(':inv_minimo', $datos['CantidadMinima']);
        $sql->bindParam(':inv_maximo', $datos['CantidadMaxima']);
        $sql->bindParam(":inv_ultimo_precio", $datos['UltimoPrecio']);

        $sql->execute();
        return $sql;
    }
    /* registrar movimiento de inventario */
    protected static function registrar_movimiento_inventario_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
        INSERT INTO movimiento_inventario
            (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
            VALUES
            (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)
        ");
        $sql->bindParam(":lm_id", $datos['LoteID']);
        $sql->bindParam(":med_id", $datos['MedID']);
        $sql->bindParam(":su_id", $datos['SucursalID']);
        $sql->bindParam(":us_id", $datos['UsuarioID']);
        $sql->bindParam(":mi_tipo", $datos['Tipo']);
        $sql->bindParam(":mi_cantidad", $datos['Cantidad']);
        $sql->bindParam(":mi_unidad", $datos['Unidad']);
        $sql->bindParam(":mi_referencia_tipo", $datos['RefTipo']);
        $sql->bindParam(":mi_referencia_id", $datos['RefID']);
        $sql->bindParam(":mi_motivo", $datos['Motivo']);

        $sql->execute();
        return $sql;
    }
    protected static function registrar_codigo_model($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO `codigo_barras`
            ( `cb_codigo`, `lm_id`, `cb_creado_en`) 
            VALUES
            (:cb_codigo,:lm_id, NOW())
        ");
        $sql->bindParam(":cb_codigo",$datos['cb_codigo']);
        $sql->bindParam(":lm_id",$datos['lm_id']);
        $sql->execute();
        return $sql;

    }
}
