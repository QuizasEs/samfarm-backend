<?php
    if($peticionAjax){
        require_once "../models/userModel.php";
    } else {
        require_once "./model/userModel.php";
    }

    class userController extends userModel{

        /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
        public function get_user_controller(){
            /* datos personales */
            $nombres = mainModel::limpiar_cadena($_POST['Nombres_reg']);
            $apellido_paterno = mainModel::limpiar_cadena($_POST['ApellidoPaterno_reg']);
            $apellido_materno = mainModel::limpiar_cadena($_POST['ApellidoMaterno_reg']);
            $carnet = mainModel::limpiar_cadena($_POST['Carnet_reg']);
            $telefono = mainModel::limpiar_cadena($_POST['Telefono_reg']);
            $correo = mainModel::limpiar_cadena($_POST['Correo_reg']);
            $direccion = mainModel::limpiar_cadena($_POST['Direccion_reg']);


            /* datos de usuario */
            $usuarioName = mainModel::limpiar_cadena($_POST['UsuarioName_reg']);
            $password = mainModel::limpiar_cadena($_POST['Password_reg']);
            $password_confirm = mainModel::limpiar_cadena($_POST['PasswordConfirm_reg']);
            $sucursal = mainModel::limpiar_cadena($_POST['Sucursal_reg']);
            $rol = mainModel::limpiar_cadena($_POST['Rol_reg']);


            /* comprobar que los campos obligatorios no esten vacios */
            if ($nombres == "" || $apellido_paterno =="" || $apellido_materno == "" || $carnet == "" || $usuarioName == "" || $password == "" || $password_confirm == "" || $sucursal == "" || $rol ==""){
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "Testo"=> "No se han llenado todos los campos obligatorios!",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }
    }
?>