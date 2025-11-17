<?php

require_once "mainModel.php";

class clienteModel extends mainModel
{
    /* registrar clientes */
    protected static function registrar_cliente_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO `clientes`
            ( `cl_nombres`, 
            `cl_apellido_paterno`, 
            `cl_apellido_materno`, 
            `cl_telefono`, 
            `cl_correo`, 
            `cl_direccion`, 
            `cl_carnet`) 
            VALUES
            (:cl_nombres, 
            :cl_apellido_paterno, 
            :cl_apellido_materno, 
            :cl_telefono, 
            :cl_correo, 
            :cl_direccion, 
            :cl_carnet)
        ");
        $sql->bindParam(":cl_nombres",$datos['cl_nombres']);
        $sql->bindParam(":cl_apellido_paterno",$datos['cl_apellido_paterno']);
        $sql->bindParam(":cl_apellido_materno",$datos['cl_apellido_materno']);
        $sql->bindParam(":cl_telefono",$datos['cl_telefono']);
        $sql->bindParam(":cl_correo",$datos['cl_correo']);
        $sql->bindParam(":cl_direccion",$datos['cl_direccion']);
        $sql->bindParam(":cl_carnet",$datos['cl_carnet']);

        $sql->execute();
        return $sql;
    }
}
