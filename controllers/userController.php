<?php
if ($peticionAjax) {
    require_once "../models/userModel.php";
} else {
    require_once "./model/userModel.php";
}

class userController extends userModel
{

    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    public function get_user_controller()
    {
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
        if ($nombres == "" || $apellido_paterno == "" || $apellido_materno == "" || $carnet == "" || $usuarioName == "" || $password == "" || $password_confirm == "" || $sucursal == "" || $rol == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "No se han llenado todos los campos obligatorios!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }


        /* verificar la integridad de los datos (patern) */
        /* nombres */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $nombres)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "El NOMBRE no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* apellido paterno */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $apellido_paterno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "El APELLIDO PATERNO no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* apellido materno */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $apellido_materno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "El APELLIDO MATERNO no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* numero de carnet */
        if (mainModel::verificar_datos("[0-9]{6,20}", $carnet)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "El Carnet no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* telefono vacio */
        if ($telefono != "") {
            if (mainModel::verificar_datos("[0-9]{6,20}", $telefono)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "Testo" => "El Telefono no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        };
        /* direccion */
        if ($direccion != "") {
            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ().,#\/- ] {3,200}", $direccion)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "Testo" => "La DIRECCION no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        };
        /* nombre de usaurio */
        if (mainModel::verificar_datos("^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_-]{3,100}$", $usuarioName)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "El USERNAME no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* contraseñas */
        if (mainModel::verificar_datos("[a-zA-Z0-9$@.-]{3,100}", $password) || mainModel::verificar_datos("[a-zA-Z0-9$@.-]{3,100}", $password_confirm)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "Las Contraseñas no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };


        /* comprobar que no hayan datos repetidos  */
        /* carnet */
        $check_carnet = mainModel::ejecutar_consulta_simple("SELECT us_numero_carnet FROM usuarios WHERE us_numero_carnet = '$carnet'");
        if ($check_carnet->rowCount() > 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "El NUMERO DE CARNET ya se encuentra registrado, por favor ingrese otro!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* nombre de usuario */
        $check_usuario = mainModel::ejecutar_consulta_simple("SELECT us_username FROM usuarios WHERE us_username = '$usuarioName'");
        if ($check_usuario->rowCount() > 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "Testo" => "El USUARIO ya se encuentra registrado, por favor ingrese otro!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if ($correo != "") {
            if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $check_correo = mainModel::ejecutar_consulta_simple("SELECT us_correo FROM usuarios WHERE us_correo = '$correo'");
                if ($check_correo->rowCount() > 0) {
                    $alerta = [
                        "Alerta" => "simple",
                        "Titulo" => "ocurrio un error inesperado",
                        "Testo" => "El correo ya se encuentra registrado, por favor ingrese otro!",
                        "Tipo" => "error"
                    ];
                    echo json_encode($alerta);
                    exit();
                };
            } else {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "Testo" => "Has ingresado un correo no valido!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }
    }
}
