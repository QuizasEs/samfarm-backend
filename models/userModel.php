<?php
require_once "mainModel.php";

class userModel extends mainModel
{



    /* -------------------------------registrar usuario----------------------------------- */
    protected static function agregar_usuario_modelo($datos)
    {
        $sql = mainModel::conectar()->prepare("
        INSERT INTO usuarios(
            us_nombres, 
            us_apellido_paterno, 
            us_apellido_materno, 
            us_numero_carnet, 
            us_telefono, 
            us_correo, 
            us_direccion, 
            us_username, 
            us_password_hash, 
            su_id, 
            ro_id
        ) VALUES(
            :nombres, 
            :apellido_paterno, 
            :apellido_materno, 
            :carnet, 
            :telefono, 
            :correo, 
            :direccion, 
            :username, 
            :password, 
            :sucursal, 
            :rol
        )
    ");

        $sql->bindParam(":nombres", $datos['Nombres']);
        $sql->bindParam(":apellido_paterno", $datos['ApellidoPaterno']);
        $sql->bindParam(":apellido_materno", $datos['ApellidoMaterno']);
        $sql->bindParam(":carnet", $datos['Carnet']);
        $sql->bindParam(":telefono", $datos['Telefono']);
        $sql->bindParam(":correo", $datos['Correo']);
        $sql->bindParam(":direccion", $datos['Direccion']);
        $sql->bindParam(":username", $datos['UsuarioName']);
        $sql->bindParam(":password", $datos['Password']);
        $sql->bindParam(":sucursal", $datos['Sucursal']);
        $sql->bindParam(":rol", $datos['Rol']);

        $sql->execute();
        return $sql;
    }
}
