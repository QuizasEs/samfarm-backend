<?php
if ($peticionAjax) {
    require_once "../models/loginModel.php";
} else {
    require_once "./models/loginModel.php";
}

class loginController extends loginModel
{
    /* controlador para iniciar sesion */
    public function iniciar_sesion_controller()
    {
        $usuario = mainModel::limpiar_cadena($_POST['Usuario_log']);
        $contraseña = mainModel::limpiar_cadena($_POST['Password_log']);

        /* == comprobar que los campos no se encuentren vacios == */
        if ($usuario == "" || $contraseña == "") {
            echo '
                    <script>
                        Swal.fire({
                            title: "Ocurrio un error inesperado",
                            text: "No se han llenado todos los campos obligatorios!",
                            icon: "error",
                            confirmButtonText: "Aceptar"
                        });
                    </script>
                ';
                exit();
        }

        /* =verificar la integridad de los datos== */
        if (mainModel::verificar_datos("^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}", $usuario)) {
            echo '
                    <script>
                        Swal.fire({
                            title: "Ocurrio un error inesperado",
                            text: "El NOMBRE DE USUARIO no coincide con el formato solicitado!",
                            icon: "error",
                            confirmButtonText: "Aceptar"
                        });
                    </script>
                ';
            exit();
        };
        if (mainModel::verificar_datos("[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}", $contraseña)) {
            echo '
                    <script>
                        Swal.fire({
                            title: "Ocurrio un error inesperado",
                            text: "La CONTRASEÑA no coincide con el formato solicitado!",
                            icon: "error",
                            confirmButtonText: "Aceptar"
                        });
                    </script>
                ';
            exit();
        };
        $contraseña = mainModel::encryption($contraseña);

        $datos_login = [
            "usuario" => $usuario,
            "password" => $contraseña
        ];
        $datos_cuenta = loginModel::iniciar_sesion_model($datos_login);

        if ($datos_cuenta->rowCount() == 1) {
            /* =============iniciamos las variables de session=============== */
            $row = $datos_cuenta->fetch();

            session_start(['name' => 'SMP']);

            $_SESSION['id_smp'] = $row['us_id'];
            $_SESSION['nombre_smp'] = $row['us_nombres'];
            $_SESSION['apellido_paterno_smp'] = $row['us_apellido_paterno'];
            $_SESSION['apellido_materno_smp'] = $row['us_apellido_materno'];
            $_SESSION['sucursal_smp'] = $row['su_id'];
            /* $_SESSION[''] = $row[''];
            $_SESSION[''] = $row[''];
            $_SESSION[''] = $row['']; */
            $_SESSION['token_smp'] = md5(uniqid(mt_rand(), true));
            return header("Location: " . SERVER_URL . "dashboard/");
        } else {
            echo '
                    <script>
                        Swal.fire({
                            title: "Ocurrio un error inesperado",
                            text: "El USUARIO o CONTRASEÑA son incorrectos o su cuenta no esta activada!",
                            icon: "error",
                            confirmButtonText: "Aceptar"
                        });
                    </script>
                    ';
            exit();
        }
    }

    /* controlador para forzar el cierre de sesion */
    public function forzar_cierre_sesion_controller(){
        session_unset();
        session_destroy();
        if(headers_sent()){  
            return "<script>window.location.href='".SERVER_URL."login/';</script>";
        } else{
            return header("Location: ".SERVER_URL."login/");
        }
    }
}
