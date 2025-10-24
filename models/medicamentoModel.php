<?php

require_once "mainModel.php";

class medicamentoModel extends mainModel
{


    /* -------------------------------registrar medicamentos----------------------------------- */
    protected static function agregar_medicamento_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
        INSERT INTO medicamento (
            med_nombre_quimico,
            med_principio_activo,
            med_accion_farmacologica,
            med_presentacion,
            uf_id,
            ff_id,
            vd_id,
            la_id,
            su_id,
            med_descripcion,
            med_precio_unitario,
            med_precio_caja
        ) VALUES (
            :Nombre,
            :Principio,
            :Accion,
            :Presentacion,
            :Uso,
            :Forma,
            :Via,
            :Laboratorio,
            :Sucursal,
            :Descripcion,
            :PrecioUnitario,
            :PrecioCaja
        )
    ");
        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Principio", $datos['Principio']);
        $sql->bindParam(":Accion", $datos['Accion']);
        $sql->bindParam(":Presentacion", $datos['Presentacion']);
        $sql->bindParam(":Uso", $datos['Uso']);
        $sql->bindParam(":Forma", $datos['Forma']);
        $sql->bindParam(":Via", $datos['Via']);
        $sql->bindParam(":Laboratorio", $datos['Laboratorio']);
        $sql->bindParam(":Sucursal", $datos['Sucursal']);
        $sql->bindParam(":Descripcion", $datos['Descripcion']);
        $sql->bindParam(":PrecioUnitario", $datos['PrecioUnitario']);
        $sql->bindParam(":PrecioCaja", $datos['PrecioCaja']);

        $sql->execute();
        return $sql;
    }

    protected static function actualizar_medicamento_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
        UPDATE medicamento SET 
            med_nombre_quimico = :Nombre,
            med_principio_activo = :Principio,
            med_accion_farmacologica = :Accion,
            med_descripcion = :Descripcion,
            med_presentacion = :Presentacion,
            med_precio_unitario = :PrecioUnitario,
            med_precio_caja = :PrecioCaja,
            uf_id = :Uso,
            ff_id = :Forma,
            vd_id = :Via,
            la_id = :Laboratorio,
            su_id = :Sucursal
        WHERE med_id = :Id
    ");

        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Principio", $datos['Principio']);
        $sql->bindParam(":Accion", $datos['Accion']);
        $sql->bindParam(":Descripcion", $datos['Descripcion']);
        $sql->bindParam(":Presentacion", $datos['Presentacion']);
        $sql->bindParam(":PrecioUnitario", $datos['PrecioUnitario']);
        $sql->bindParam(":PrecioCaja", $datos['PrecioCaja']);
        $sql->bindParam(":Uso", $datos['Uso']);
        $sql->bindParam(":Forma", $datos['Forma']);
        $sql->bindParam(":Via", $datos['Via']);
        $sql->bindParam(":Laboratorio", $datos['Laboratorio']);
        $sql->bindParam(":Sucursal", $datos['Sucursal']);
        $sql->bindParam(":Id", $datos['Id']);

        return $sql->execute();
    }



    /* ------------------------------- datos extras de medicamentos----------------------------------- */




    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
}
