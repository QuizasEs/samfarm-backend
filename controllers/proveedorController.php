<?php

if ($peticionAjax) {
    require_once "../models/proveedorModel.php";
} else {
    require_once "./models/proveedorModel.php";
}

class proveedorController extends proveedorModel
{
    /* funciona para registra proveedor */
    public function agregar_proveedor_controller()
    {
        /* limpiamos cadena de caracteres ingresados en POST */
        $nombre = mainModel::limpiar_cadena($_POST['Nombre_reg']);
        $apellido_paterno = mainModel::limpiar_cadena($_POST['Apellido_paterno_reg']);
        $apellido_materno = mainModel::limpiar_cadena($_POST['Apellido_materno_reg']);
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_reg']);

        /* verificamos que los campos requeridos no esten vacios */
        if ($nombre == "" || $apellido_paterno == "" || $apellido_materno == "" || $telefono == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "No se han llenado todos los campos obligatorios!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* verificamos la integridad de los datos */
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $nombre)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El NOMBRE no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $apellido_paterno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El APELLIDO PATERNO no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $apellido_materno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El APELLIDO MATERNO no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (mainModel::verificar_datos("[0-9.]{1,100}", $telefono)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El TELÉFONO no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* preparamos los datos para el modelo */
        $datos_proveedor = [
            "Nombre" => $nombre,
            "ApellidoPaterno" => $apellido_paterno,
            "ApellidoMaterno" => $apellido_materno,
            "Telefono" => $telefono
        ];

        $agregar_datos = proveedorModel::agregar_proveedor_model($datos_proveedor);

        /* verificamos que el modelo inserto la informacion en la base de datos */
        if ($agregar_datos->rowCount() == 1) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Proveedor Registrado correctamente",
                "texto" => "El proveedor se ha registrado con éxito",
                "Tipo" => "success"
            ];
            echo json_encode($alerta);
            exit();
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "No se ha podido registrar el proveedor, por favor intente nuevamente!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
    }
    public function paginado_proveedor_controller($pagina, $registros, $url, $busqueda)
    {
        /* limpiamos cadenas para evitar injeccion */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
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
                pr_id,
                pr_nombres,
                pr_apellido_paterno,
                pr_apellido_materno,
                pr_telefono,
                pr_creado_en,
                pr_actualizado_en,
                pr_estado
            FROM proveedores
            WHERE (
                pr_nombres LIKE '%$busqueda%' OR
                pr_apellido_paterno LIKE '%$busqueda%' OR
                pr_apellido_materno LIKE '%$busqueda%' OR
                pr_telefono LIKE '%$busqueda%'
            )
            ORDER BY pr_nombres ASC 
            LIMIT $inicio, $registros
        ";
        } else {
            $consulta = "
            SELECT 
                SQL_CALC_FOUND_ROWS 
                pr_id,
                pr_nombres,
                pr_apellido_paterno,
                pr_apellido_materno,
                pr_telefono,
                pr_creado_en,
                pr_actualizado_en,
                pr_estado
            FROM proveedores
            WHERE pr_estado = 1
            ORDER BY pr_nombres ASC 
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
                            <th>APELLIDO MATERNO</th>
                            <th>TELÉFONO</th>
                            <th>CREADO EN</th>
                            <th>ACTUALIZADO EN</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
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
                    <td>' . $rows["pr_nombres"] . '</td>
                    <td>' . $rows["pr_apellido_paterno"] . '</td>
                    <td>' . $rows["pr_apellido_materno"] . '</td>
                    <td>' . $rows["pr_telefono"] . '</td>
                    <td>' . $rows["pr_creado_en"] . '</td>
                    <td>' . $rows["pr_actualizado_en"] . '</td>
                    <td>' . ($rows["pr_estado"] == 1 ? '<span class="active">Activo</span>' : '<span class="in-active">Inactivo</span>') . '</td>
                    <td>
                        <a href="' . SERVER_URL . 'proveedorActualizar/' . mainModel::encryption($rows['pr_id']) . '/" class="btn-editar">Editar</a>
                        ' .
                    ($rows["pr_estado"] == 1
                        ? '<form action="' . SERVER_URL . 'ajax/proveedorAjax.php" class="FormularioAjax" method="POST" data-form="disable" autocomplete="off">
                                <input type="hidden" value="' . mainModel::encryption($rows['pr_id']) . '" name="proveedor_des">
                                <button type="submit" class="btn-disable">Deshabilitar</button>
                        </form>'
                        : '') . '
                    </td>
                </tr>
            ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            if ($total >= 1) {
                /* en caso que la url no sea valida de una pagina con registros mostrara */
                $tabla .= ' <tr><td colspan="9"><a class="btn-primary" href="' . $url . '">Recargar</a></td></tr> ';
            } else {
                /* en caso que no tenga registrados ni un registro en la base de datos mostrara */
                $tabla .= ' <tr><td colspan="9">No hay registros</td></tr> ';
            }
        }

        /* final de tabla */
        $tabla .= '
            </tbody>
        </table>
    </div>
    ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p>Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 5);
        }

        /* devolvemos tabla */
        return $tabla;
    }
    /* funcion pedir informacion de provedor */
    public function datos_proveedor_controller($id)
    {
        /* limpiamos los datos de emntrada */

        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);

        /* enviamos la id al modelo proveedor */
        return proveedorModel::data_proveedor_model($id);
    }
    public function actualizar_proveedor_controller()
    {
        // Recibir y limpiar datos del formulario
        $id = mainModel::decryption($_POST['id']);
        $id = mainModel::limpiar_cadena($id);

        $nombres = mainModel::limpiar_cadena($_POST['Nombre_up'] ?? '');
        $apellido_paterno = mainModel::limpiar_cadena($_POST['Apellido_paterno_up'] ?? '');
        $apellido_materno = mainModel::limpiar_cadena($_POST['Apellido_materno_up'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_up'] ?? '');
        $estado = mainModel::limpiar_cadena($_POST['Estado_up'] ?? '');

        // Verificar campos obligatorios
        if ($nombres == "" || $apellido_paterno == "" || $apellido_materno == "" || $telefono == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto" => "No has llenado todos los campos que son obligatorios",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        // Verificar que el proveedor existe
        $check_proveedor = mainModel::ejecutar_consulta_simple("SELECT pr_id FROM proveedores WHERE pr_id = '$id'");
        if ($check_proveedor->rowCount() == 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto" => "El proveedor que intentas actualizar no existe en el sistema",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* verificamos la integridad de los datos */
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $nombres)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El NOMBRE no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $apellido_paterno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El APELLIDO PATERNO no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $apellido_materno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El APELLIDO MATERNO no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (mainModel::verificar_datos("[0-9.]{1,100}", $telefono)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El TELÉFONO no cumple con el formato requerido",
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

        // Preparar datos para el modelo
        $datos_proveedor = [
            "ID" => $id,
            "Nombre" => $nombres,
            "ApellidoPaterno" => $apellido_paterno,
            "ApellidoMaterno" => $apellido_materno,
            "Telefono" => $telefono,
            "Fecha" => date("Y-m-d H:i:s"),
            "Estado" => $estado
        ];

        // Actualizar proveedor
        $actualizar_proveedor = self::actualizar_proveedor_model($datos_proveedor);

        if ($actualizar_proveedor) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Proveedor Actualizado",
                "Texto" => "Los datos del proveedor fueron actualizados correctamente",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto" => "No hemos podido actualizar los datos del proveedor",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
    }



    public function agregar_laboratorio_controller()
    {
        /* Limpiar las cadenas recibidas */
        $nombre = mainModel::limpiar_cadena($_POST['Nombre_reg'] ?? '');
        $proveedor = mainModel::limpiar_cadena($_POST['proveedor_reg'] ?? '');
        $logo = $_FILES['logo_reg'] ?? null;

        /* Verificar campos obligatorios */
        if ($nombre == "" || empty($logo) || $logo['error'] !== UPLOAD_ERR_OK) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Campos incompletos",
                "texto" => "Debe llenar todos los campos y subir un logo válido.",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Validar formato del nombre */
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $nombre)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Formato inválido",
                "texto" => "El nombre comercial no cumple con el formato permitido.",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Directorio de imágenes */
        $img_dir = __DIR__ . "/../views/assets/img/"; 

        if (!is_dir($img_dir)) {
            mkdir($img_dir, 0777, true);
        }

        /* Usar función del mainModel para procesar imagen */
        $procesar_imagen = mainModel::procesar_imagen($logo, 'lab', $img_dir);

        if ($procesar_imagen['error']) {
            echo json_encode($procesar_imagen['alerta']);
            exit();
        }

        $nombre_logo = $procesar_imagen['nombre'];

        /* Preparar los datos para el modelo */
        $datos_laboratorio = [
            "Nombre" => $nombre,
            "Logo" => $nombre_logo,
            "Proveedor" => $proveedor
        ];

        /* Ejecutar inserción */
        $agregar_datos = proveedorModel::agregar_laboratorio_model($datos_laboratorio);

        /* Verificar resultado */
        if ($agregar_datos->rowCount() == 1) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Laboratorio registrado",
                "texto" => "El laboratorio se ha registrado correctamente.",
                "Tipo" => "success"
            ];
        } else {
            // Eliminar imagen si falla la inserción
            if (file_exists($img_dir . $nombre_logo)) {
                unlink($img_dir . $nombre_logo);
            }

            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error inesperado",
                "texto" => "No se pudo registrar el laboratorio, intente nuevamente.",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
        exit();
    }
}
