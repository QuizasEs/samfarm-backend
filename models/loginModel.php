<?php
    require_once "mainModel.php";

    class loginModel extends mainModel{
        
        /* modelo para iniciar session */
        protected static function iniciar_sesion_model($datos){
            $sql = mainModel::conectar()->prepare("SELECT * FROM usuarios WHERE us_username = :Usuario
            AND us_password_hash = :Password AND us_estado = 1");
            $sql->bindParam(":Usuario",$datos['usuario']);
            $sql->bindParam(":Password",$datos['password']);
            

            $sql->execute();
            return $sql;
        }
    }
?>
