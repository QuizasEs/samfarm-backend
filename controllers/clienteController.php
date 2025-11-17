<?php

if ($peticionAjax) {
    require_once "../models/clienteModel.php";
} else {
    require_once "./models/clienteModel.php";
}

class clienteController extends clienteModel
{
    /* registrar clientes */
    public function registrar_cliente_controller()
    {

        $nombre = mainModel::limpiar_cadena($_POST['Nombres_cl']);
        $paterno = mainModel::limpiar_cadena($_POST['Paterno_cl']);
        $materno = mainModel::limpiar_cadena($_POST['Materno_cl']);
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_cl']);
        $correo = mainModel::limpiar_cadena($_POST['Correo_cl']);
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_cl']);
        $carnet = mainModel::limpiar_cadena($_POST['Carnet_cl']);
        /* validamos que los campos obligatorios no esten vacios */

        if ($nombre == "" || $paterno == "") {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrió un error inesperado',
                'texto' => 'No se han llenado todos los campos obligatorios!',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        /* verificamos la integridad de los datos  */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $nombre)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrió un error inesperado',
                'texto' => 'El Nombre No cumple con los parametros establecidos!',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $paterno)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrió un error inesperado',
                'texto' => 'El Apellido Paterno No cumple con los parametros establecidos!',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        if ($materno != "") {
            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $materno)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Ocurrió un error inesperado',
                    'texto' => 'El Apellido Materno No cumple con los parametros establecidos!',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }
        if ($telefono != "") {
            if (mainModel::verificar_datos("[0-9]{6,20}", $telefono)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Ocurrió un error inesperado',
                    'texto' => 'El Telefono No cumple con los parametros establecidos!',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }
        if ($carnet != "") {
            if (mainModel::verificar_datos("[0-9]{6,20}", $carnet)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Ocurrió un error inesperado',
                    'texto' => 'El Carnet No cumple con los parametros establecidos!',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }
        if ($correo != "") {
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "Has ingresado un correo no valido!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }
        /* preparamos los datos de usuario */
        $datos_cliente = [
            "cl_nombres" => $nombre,
            "cl_apellido_paterno" => $paterno,
            "cl_apellido_materno" => $materno,
            "cl_telefono" => $telefono,
            "cl_correo" => $correo,
            "cl_direccion" => $direccion,
            "cl_carnet" => $carnet
        ];
        /* enviamos  datos */
        $cliente_respuesta = clienteModel::registrar_cliente_model($datos_cliente);

        /* verificamos que se regostro correctamente */
        if ($cliente_respuesta->rowCount() <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No pudimos registrar al Cliente, por favor intentelo mas tarde!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Cliente registrado",
            "texto" => "El cliente se ha registrado correctamente!",
            "Tipo" => "success"
        ];
        echo json_encode($alerta);
        exit();
    }
}
