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

    /* -------------------------------desabilitar usuario usuario----------------------------------- */
    protected static function disable_user_model($id)
    {
        /* preparamos el update */
        $sql = mainModel::Conectar()->prepare("UPDATE usuarios SET us_estado = '0' WHERE us_id = :ID");

        /* asignamos el valor a reemplazar */
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);

        /* ejecutamos */
        $sql->execute();

        /* retornamos */
        return $sql;
    }

    /* ------------------------------ obtener datos de usuario----------------------------------- */

    protected static function data_user_model($tipo, $id)
    {
        /* para cuando el usuario quiere ver su propio perfil */
        if ($tipo == "Unico") {
            $sql = mainModel::Conectar()->prepare("SELECT * FROM usuarios WHERE us_id = :ID");
            $sql->bindParam(":ID", $id);
        } else if ($tipo == "Conteo") {
            /* contamos todos los registros de la base de datos exeptuendo el usuario principal */
            $sql = mainModel::conectar()->prepare("SELECT us_id FROM usuarios WHERE us_id != '1'");
        }
        $sql->execute();
        return $sql;
    }





    /* ------------------------------ modelo para actualizar los datos de usuario----------------------------------- */
    protected static function data_update_user_model($datos)
    {
        $sql = mainModel::conectar()->prepare("UPDATE usuarios 
        SET 
        us_nombres = :nombres, 
        us_apellido_paterno = :apellido_paterno,
        us_apellido_materno = :apellido_materno,
        us_numero_carnet = :carnet,
        us_telefono = :telefono,
        us_correo  = :correo,
        us_direccion = :direccion,
        us_username = :username,
        us_password_hash = :password,
        us_estado = :estado,
        su_id = :sucursal,
        ro_id = :rol
        WHERE us_id = :id
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
        $sql->bindParam(":estado", $datos['Estado']);
        $sql->bindParam(":sucursal", $datos['Sucursal']);
        $sql->bindParam(":rol", $datos['Rol']);
        $sql->bindParam(":id", $datos['Id']);
        $sql->execute();
        return $sql;
    }


    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
    /* ------------------------------ usuario usuario----------------------------------- */
}
