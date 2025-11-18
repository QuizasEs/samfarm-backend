<?php
if ($peticionAjax) {
    require_once "../models/userModel.php";
} else {
    require_once "./models/userModel.php";
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
        $carnet =   mainModel::limpiar_cadena($_POST['Carnet_reg']);
        $telefono =  mainModel::limpiar_cadena($_POST['Telefono_reg']);
        $correo = mainModel::limpiar_cadena($_POST['Correo_reg']);
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_reg']);


        /* datos de usuario */
        $usuarioName = mainModel::limpiar_cadena($_POST['UsuarioName_reg']);
        $password = mainModel::limpiar_cadena($_POST['Password_reg']);
        $password_confirm = mainModel::limpiar_cadena($_POST['PasswordConfirm_reg']);
        $sucursal =  mainModel::limpiar_cadena($_POST['Sucursal_reg']);
        $rol = mainModel::limpiar_cadena($_POST['Rol_reg']);
        $sucursal = (int)$sucursal;
        $rol = (int)$rol;


        /* combertimos enteros los campos */


        /* comprobar que los campos obligatorios no esten vacios */
        if ($nombres == "" || $apellido_paterno == "" || $apellido_materno == "" || $carnet == "" || $usuarioName == "" || $password == "" || $password_confirm == "" || $sucursal == "" || $rol == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No se han llenado todos los campos obligatorios!",
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
                "texto" => "El NOMBRE no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* apellido paterno */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $apellido_paterno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El APELLIDO PATERNO no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* apellido materno */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $apellido_materno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El APELLIDO MATERNO no coincide con el formato solicitado!",
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
                "texto" => "El Carnet no coincide con el formato solicitado!",
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
                    "texto" => "El Telefono no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        };
        /* nombre de usaurio */
        if (mainModel::verificar_datos("^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$", $usuarioName)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El USERNAME no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* contraseñas */
        if (
            mainModel::verificar_datos("[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}", $password) ||
            mainModel::verificar_datos("[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}", $password_confirm)
        ) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "Las Contraseñas no coincide con el formato solicitado!",
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
                "texto" => "El NUMERO DE CARNET ya se encuentra registrado, por favor ingrese otro!",
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
                "texto" => "El USUARIO ya se encuentra registrado, por favor ingrese otro!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* comprobar correo */
        if ($correo != "") {
            if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $check_correo = mainModel::ejecutar_consulta_simple("SELECT us_correo FROM usuarios WHERE us_correo = '$correo'");
                if ($check_correo->rowCount() > 0) {
                    $alerta = [
                        "Alerta" => "simple",
                        "Titulo" => "ocurrio un error inesperado",
                        "texto" => "El correo ya se encuentra registrado, por favor ingrese otro!",
                        "Tipo" => "error"
                    ];
                    echo json_encode($alerta);
                    exit();
                };
            } else {
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

        /* comprobar contraseñas */

        if ($password != $password_confirm) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "Las CONTRASEÑAS no coinciden, intente nuevamente!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        } else {
            $password_hash = mainModel::encryption($password);
        };

        if ($rol < 1 || $rol > 3) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El ROL seleccionado no es valido!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        $datos_usuario_reg = [
            "Nombres" => $nombres,
            "ApellidoPaterno" => $apellido_paterno,
            "ApellidoMaterno" => $apellido_materno,
            "Carnet" => $carnet,
            "Telefono" => $telefono,
            "Correo" => $correo,
            "Direccion" => $direccion,
            "UsuarioName" => $usuarioName,
            "Password" => $password_hash,
            "Sucursal" => $sucursal,
            "Rol" => $rol
        ];
        $agregar_usuario = userModel::agregar_usuario_modelo($datos_usuario_reg);

        if ($agregar_usuario->rowCount() == 1) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Usuario registrado correctamente",
                "texto" => "El USUARIO se ha registrado con exito",
                "Tipo" => "success"
            ];
            echo json_encode($alerta);
            exit();
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No se ha podido registrar el usuario, por favor intente nuevamente!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
    }
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    public function paginado_user_controller($pagina, $registros, $privilegio, $id, $url, $busqueda)
    {
        /* limpiamos cadenas para evitar injeccion */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $privilegio = mainModel::limpiar_cadena($privilegio);
        $id = mainModel::limpiar_cadena($id);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . "/";
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = "";

        /* validamos que el valor ingresado por url sea un numero */
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        if (isset($busqueda) && $busqueda != "") {
            /* busqueda */
            $consulta = "
                SELECT 
                    SQL_CALC_FOUND_ROWS 
                    u.*, 
                    r.ro_nombre AS rol_nombre, 
                    s.su_nombre AS sucursal_nombre
                FROM usuarios AS u
                LEFT JOIN roles AS r ON u.ro_id = r.ro_id
                LEFT JOIN sucursales AS s ON u.su_id = s.su_id
                WHERE (u.us_id != '$id' AND u.us_id != '5')
                AND (
                    u.us_numero_carnet LIKE '%$busqueda%' OR
                    u.us_nombres LIKE '%$busqueda%' OR
                    u.us_apellido_paterno LIKE '%$busqueda%' OR
                    u.us_apellido_materno LIKE '%$busqueda%' OR
                    u.us_telefono LIKE '%$busqueda%' OR
                    u.us_correo LIKE '%$busqueda%' OR
                    u.us_direccion LIKE '%$busqueda%' OR
                    u.us_username LIKE '%$busqueda%' OR
                    r.ro_nombre LIKE '%$busqueda%' OR
                    s.su_nombre LIKE '%$busqueda%'
                )
                ORDER BY u.us_nombres ASC 
                LIMIT $inicio, $registros
            ";
        } else {
            /* evitamos que el usuario actual y el usuario principal sean visibles y accesibles */
            $consulta = "
                SELECT 
                    SQL_CALC_FOUND_ROWS 
                    u.*, 
                    r.ro_nombre AS rol_nombre, 
                    s.su_nombre AS sucursal_nombre
                FROM usuarios AS u
                LEFT JOIN roles AS r ON u.ro_id = r.ro_id
                LEFT JOIN sucursales AS s ON u.su_id = s.su_id
                WHERE u.us_id != '$id' 
                AND u.us_id != '5'
                ORDER BY u.us_nombres ASC 
                LIMIT $inicio, $registros
            ";
        }

        /* realizamos la peticion a la base de datos */
        $conexion = mainModel::conectar();
        $datos = $conexion->query($consulta);
        $datos = $datos->fetchAll();

        /* obtenemos la cantidad total de registro */
        $total = $conexion->query("SELECT FOUND_ROWS()");
        $total = (int) $total->fetchColumn();

        /* numero de paginas por registros */
        $Npaginas = ceil($total / $registros);

        /* inicio de tabla */
        $tabla .= '
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>NOMBRES</th>
                                <th>APELLIDO PATERNO</th>
                                <th>APELLIDO MATERMNO</th>
                                <th>N° CARNET</th>
                                <th>N° TELEFONO</th>
                                <th>CORREO</th>
                                <th>DIRECCION</th>
                                <th>NOMBRE DE USUARIO</th>
                                <th>CREADO EN</th>
                                <th>ACTUALIZADO EN</th>
                                <th>ROL</th>
                                <th>SUCURSAL</th>
                                <th>ESTADO</th>
                                <th>
                                    ACCIONES
                                </th>
                            </tr>
                        </thead>
                        <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;
            foreach ($datos as $rows) {
                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>' . $rows["us_nombres"] . '</td>
                        <td>' . $rows["us_apellido_paterno"] . '</td>
                        <td>' . $rows["us_apellido_materno"] . '</td>
                        <td>' . $rows["us_numero_carnet"] . '</td>
                        <td>' . $rows["us_telefono"] . '</td>
                        <td>' . $rows["us_correo"] . '</td>
                        <td>' . $rows["us_direccion"] . '</td>
                        <td>' . $rows["us_username"] . '</td>
                        <td>' . $rows["us_creado_en"] . '</td>
                        <td>' . $rows["us_actualizado_en"] . '</td>
                        <td>' . $rows["rol_nombre"] . '</td>
                        <td>' . $rows["sucursal_nombre"] . '</td>
                        <td>' . ($rows["us_estado"] == 1 ? '<span class="active">Activo</span>' : '<span class="in-active">Inactivo</span>') . '</td>
                        <td><a href="' . SERVER_URL . 'usuarioActualizar/' . mainModel::encryption($rows['us_id']) . '/" class="btn-editar">Editar</a>
                            ' .
                    ($rows["us_estado"] == 1
                        /* POR VERDAD */
                        ? '<form action="' . SERVER_URL . 'ajax/userAjax.php" class="FormularioAjax" method="POST" data-form="disable" autocomplete="off">
                                        <input type="hidden" value="' . mainModel::encryption($rows['us_id']) . '" name="usuario_des">
                                        <button type="submit" class="btn-disable">Deshabilitar</button>
                                </form>'
                        /* POR FALSEO NO MUESTRA NADA */
                        : '') . '
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final =  $contador - 1;
        } else {


            if ($total >= 1) {
                /* en caso que la url no sea valida de una pagina con registros mostrara  */
                $tabla .= ' <tr><td colspan="15">  <a class="btn-primary" href="' . $url . '"> Recargar </a></td></tr> ';
            } else {
                /* en caso que no tenga registrados ni un registro en la base de datos mostrara  */
                $tabla .= ' <tr><td colspan="15"> No hay registros</td></tr> ';
            }
        }

        /* final de talbla */
        $tabla .= '
                </tbody>
            </table>
        </div>
        
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p> Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . ' </p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 5);
        }

        /* devolvemos tabla */
        return $tabla;
    }


    /* -----------------------------------controlador para desabilitar usuarios------------------------------------------ */
    public function disable_user_controller()
    {
        /* resibimos el id del usuario que qeremos desabilitar */
        $id = mainModel::decryption($_POST['usuario_des']);
        $id = mainModel::limpiar_cadena($id);
        /*  el id no debe ser ugual al id del usuario principal */
        if ($id == 1) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "Este usuario no puede ser desabilitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* el id de ussuario debe existir dentro de la base de datos */
        $check_id = mainModel::ejecutar_consulta_simple("SELECT * FROM usuarios WHERE us_id = '$id'");
        if ($check_id->rowCount() <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "La ID de usuario no existe dentro de la base de datos!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        /* para verificar que el usuario no tenga pendientes podemos reutilizar este codigo cambiando algunos parametros */

        /* $check_id = mainModel::ejecutar_consulta_simple("SELECT * FROM usuarios WHERE us_id = '$id'");
            if ($check_id->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "La ID de usuario no existe dentro de la base de datos!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        } */
        /* preguntamos quien realiza las consultas tiene los privilegios necesarios para desabilitar usuario */
        session_start(['name' => 'SMP']);
        if ($_SESSION['rol_smp'] != 1) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No cuenta con los permisos necesarios para ejecutar esta accion!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* ejecutar modelo para desabilitar */
        $desabilitar_usuario = userModel::disable_user_model($id);
        /* comprobamos si se ejecuto correctamente este metodo */
        if ($desabilitar_usuario->rowCount() == 1) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Completado",
                "texto" => "Usuario deshabilitado exitosamente!",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "No se pudo deshabilitar el usuario, por favor intente nuevamente más tarde!",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
    }


    /* -----------------------------------controlador para recabard datos de usuarios------------------------------------------ */

    public function data_user_controller($tipo, $id)
    {
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);

        return userModel::data_user_model($tipo, $id);
    }
    /* -----------------------------------controlador para recabard datos de usuarios roles------------------------------------------ */

    public function data_rol_list_controller($tipo, $id)
    {
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);

        return mainModel::data_rol_list_model($tipo, $id);
    }
    /* -----------------------------------controlador para recabar datos de usuarios sucursales------------------------------------------ */
    public function data_sucursal_list_controller($tipo, $id)
    {
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);

        return mainModel::data_sucursal_list_model($tipo, $id);
    }

    /* -----------------------------------controlador para actualizar datos de usuarios------------------------------------------ */
    public function data_update_user_controller()
    {
        
        $id = mainModel::decryption($_POST['usuario_id_up']);
        $id = mainModel::limpiar_cadena($id);
        /* comprobamos laexistencia de id usuario dentro de la base de datos */
        $check_id = mainModel::ejecutar_consulta_simple("SELECT * FROM usuarios WHERE us_id = '$id'");
        if ($check_id->rowCount() <= 0) {
            /* no se encontro ningun registro */
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No se han encontrado el usuario a actualizar!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        } else {
            /* si hay usuario  */
            $campos = $check_id->fetch();
        }

        /* asignamos los campos  del formulario*/

        $nombres = mainModel::limpiar_cadena($_POST['Nombres_up']);
        $apellido_paterno = mainModel::limpiar_cadena($_POST['ApellidoPaterno_up']);
        $apellido_materno = mainModel::limpiar_cadena($_POST['ApellidoMaterno_up']);
        $carnet = mainModel::limpiar_cadena($_POST['Carnet_up']);
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_up']);
        $correo = mainModel::limpiar_cadena($_POST['Correo_up']);
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_up']);

        /* campos de confirmacion de cambios */
        $username = mainModel::limpiar_cadena($_POST['UsuarioName_up']);
        $password = mainModel::limpiar_cadena($_POST['Password_up']);

        /* apra estado */
        if (isset($_POST['Estado_up'])) {
            /* nuevo registro */
            $estado = mainModel::limpiar_cadena($_POST['Estado_up']);
        } else {
            /* sin cambios (usando la informacion de la base de datos pre existente) */
            $estado = $campos['us_estado'];
        }
        /* oara sucursal */
        if (isset($_POST['Sucursal_up'])) {
            /* nuevo registro */
            $sucursal = mainModel::limpiar_cadena($_POST['Sucursal_up']);
        } else {
            /* usar de la base de datos */
            $sucursal = $campos['su_id'];
        }

        /* para rol */
        if (isset($_POST['Rol_up'])) {
            /* nuevo */
            $rol = mainModel::limpiar_cadena($_POST['Rol_up']);
        } else {
            /* viejo */
            $rol = $campos['ro_id'];
        }
        $admin_usuario = mainModel::limpiar_cadena($_POST['Usuario_confirm']);
        $admin_password = mainModel::limpiar_cadena($_POST['Password_confirm']);;
        $tipo_cuenta = mainModel::limpiar_cadena($_POST['Tipo_up']);
        /* verificamos que los campos obligatorios no esten vacios */

        if ($nombres == "" || $apellido_paterno == "" || $apellido_materno == "" || $carnet == "" || $username == "" || $admin_usuario == "" || $admin_password == "") {
                /* si algun campo esta basio */;
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No se han llenado todos los campos obligatorios!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }


        /* verificar la integridad de los datos (patern) */
        /* nombres */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $nombres)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NOMBRE no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* apellido paterno */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $apellido_paterno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El APELLIDO PATERNO no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* apellido materno */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}", $apellido_materno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El APELLIDO MATERNO no coincide con el formato solicitado!",
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
                "texto" => "El Carnet no coincide con el formato solicitado!",
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
                    "texto" => "El Telefono no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        };
        /* nombre de usaurio */
        if (mainModel::verificar_datos("^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$", $username)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El USERNAME no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* usuario de confirmacion */
        if (mainModel::verificar_datos("^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$", $admin_usuario)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "Tu nombre de USUARIO no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };/* contraseña de confirmacion */
        if (mainModel::verificar_datos("^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$", $admin_password)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "Tu contraseña no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        /* encriptamos la contrseña de confirmacion */
        $admin_password = mainModel::encryption($admin_password);


        /* revisamos el rango de privilegios */
        if ($rol < 1 || $rol > 3) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "Permiso equivocado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        /* revisamos que elvalor de estado sea 1 o 0 no otros (evitamos manipuilacion por html) */
        if ($estado != 1 && $estado != 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No podemos procesar este estado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        /* campos unicos */

        if ($carnet != $campos['us_numero_carnet']) {
            $check_carnet = mainModel::ejecutar_consulta_simple("SELECT * FROM usuarios WHERE us_numero_carnet = '$carnet'");
            if ($check_carnet->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El numero de carnet ya se encuentra registrado en sistema!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }
        /* verificar si el usuario se esta repitiendo */
        if ($username != $campos['us_username']) {
            $check_usuario = mainModel::ejecutar_consulta_simple("SELECT * FROM usuarios WHERE us_username = '$username'");
            if ($check_usuario->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El nombre de usuario ya se encuentra registrado en sistema!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        /* verificar correo */
        if ($correo != $campos['us_correo'] && $correo != "") {
            if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $check_correo = mainModel::ejecutar_consulta_simple("SELECT us_correo FROM usuarios WHERE us_correo = '$correo'");
                if ($check_correo->rowCount() > 0) {
                    $alerta = [
                        "Alerta" => "simple",
                        "Titulo" => "ocurrio un error inesperado",
                        "texto" => "El correo ya se encuentra registrado, intente nuevamente!",
                        "Tipo" => "error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            } else {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "Ingrese un correo valido!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        /* validamos contraseñas para actualizar (cambair contraseñas)  */
        if ($_POST['Password_up'] != "" && $_POST['PasswordConfirm_up'] != "") {
            if ($_POST['Password_up'] != $_POST['PasswordConfirm_up']) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "Las nuevas contraseñas no coinciden!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            } else {
                if (mainModel::verificar_datos("[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}", $_POST['Password_up'])  || mainModel::verificar_datos("[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}", $_POST['PasswordConfirm_up'])) {
                    $alerta = [
                        "Alerta" => "simple",
                        "Titulo" => "ocurrio un error inesperado",
                        "texto" => "Las nuevas contraseñas no coinciden con el formato solicitado!",
                        "Tipo" => "error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
                $password = mainModel::encryption($_POST['Password_up']);
            }
        } else {
            $password = $campos['us_password_hash'];
        }

        /* comprovar credenciales para actualizar datos de usuarios */
        if ($tipo_cuenta == "Propia") {
            $check_cuenta = mainModel::ejecutar_consulta_simple(
                "SELECT us_id FROM usuarios 
            WHERE
            us_username = '$admin_usuario' AND
            us_password_hash = '$admin_password' AND
            us_id = '$id'"
            );
        } else {
            session_start(['name' => 'SMP']);
            if ($_SESSION['rol_smp'] != 1) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "Nocuentas con los permisos necesarios!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
            $check_cuenta = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_username = '$admin_usuario' AND us_password_hash = '$admin_password'");
        }
        /* contar si hay registros */

        if ($check_cuenta->rowCount() <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "Las credenciales de administrador no existen dentro del sistema!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* preparamos los datos para su registro en la base de datos  */


        $datos_usuario_up = [
            "Nombres" => $nombres,
            "ApellidoPaterno" => $apellido_paterno,
            "ApellidoMaterno" => $apellido_materno,
            "Carnet" => $carnet,
            "Telefono" => $telefono,
            "Correo" => $correo,
            "Direccion" => $direccion,
            "UsuarioName" => $username,
            "Password" => $password,
            "Estado" => $estado,
            "Sucursal" => $sucursal,
            "Rol" => $rol,
            "Id" => $id
        ];

        if (userModel::data_update_user_model($datos_usuario_up)) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Actualizado correctamente",
                "texto" => "Se alctualizo la informacion del usuario correctamente",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "No se pudo actualizar el usuario, intente nuevamente mas tarde!",
                "Tipo" => "error"
            ];
        }
        echo json_encode($alerta);
    }
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
}
