<?php

if ($peticionAjax) {
    require_once "../models/medicamentoModel.php";
} else {
    require_once "./models/medicamentoModel.php";
}



class medicamentoController extends medicamentoModel
{

    /* -----------------------------------controlador para recabar datos de los selects------------------------------------------ */
    public function datos_extras_controller()
    {
        /* $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id); */

        return mainModel::datos_extras_model();
    }



    /* -----------------------------------controlador para agregar medicamentos------------------------------------------ */
    public function agregar_medicamento_controller()
    {

        /* limpiamos cadena de caracteres ingresados en POST */
        $nombre = mainModel::limpiar_cadena($_POST['Nombre_reg']);
        $principio = mainModel::limpiar_cadena($_POST['Principio_reg']);
        $accion = mainModel::limpiar_cadena($_POST['Accion_reg']);
        $descripcion = mainModel::limpiar_cadena($_POST['Descripcion_reg']);
        $presentacion = mainModel::limpiar_cadena($_POST['Presentacion_reg']);
        $unitario = mainModel::limpiar_cadena($_POST['Precio_unitario_reg']);
        $caja = mainModel::limpiar_cadena($_POST['Precio_caja_reg']);

        $uso = mainModel::limpiar_cadena($_POST['Uso_reg']);
        $forma = mainModel::limpiar_cadena($_POST['Forma_reg']);
        $via = mainModel::limpiar_cadena($_POST['Via_reg']);
        $laboratorio = mainModel::limpiar_cadena($_POST['Laboratorio_reg']);
        $sucursal = mainModel::limpiar_cadena($_POST['Sucursal_reg']);

        $uso = (int)$uso;
        $forma = (int)$forma;
        $via = (int)$via;
        $laboratorio = (int)$laboratorio;
        $sucursal = (int)$sucursal;

        /* verificamos que los campos requeridos no esten vacios */
        if ($nombre == "" || $principio == "" || $accion == "" || $presentacion == "" || $uso == "" || $forma == "" || $via == "" || $laboratorio == "" || $unitario == "" || $caja == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "No se han llenado todos los campos obligatorios!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        /* verificamos la integridad de los parents */
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $nombre)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El NOMBRE COMERCIAL no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $principio)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El PRINCIPIO ACTIVO no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $accion)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "La ACCION FARMACOLOGICA no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        if (mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}", $presentacion)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "La PRESENTACION no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        if (mainModel::verificar_datos("[0-9.]{1,10}", $unitario)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El PRECIO POR UNIDAD no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        if (mainModel::verificar_datos("[0-9.]{1,10}", $caja)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "El PRECIO POR CAJA no cumple con el formato requerido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* verificar valor de selects no negativo */
        if ($uso <= 0 || $forma <= 0 || $via <= 0 || $laboratorio <= 0 || $sucursal == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "No se seleccionó ningún campo disponible",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
        $datos_med = [
            "Nombre" =>  $nombre,
            "Principio" => $principio,
            "Accion" => $accion,
            "Presentacion" => $presentacion,
            "Uso" => $uso,
            "Forma" => $forma,
            "Via" => $via,
            "Laboratorio" => $laboratorio,
            "Sucursal" => $sucursal,
            "Descripcion" => $descripcion,
            "PrecioUnitario" => $unitario,
            "PrecioCaja" => $caja
        ];
        $agregar_datos = medicamentoModel::agregar_medicamento_model($datos_med);

        /* verificamos que el modelo inserto la informacion en la base de datos */

        if ($agregar_datos->rowCount() == 1) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Medicamento Registrado correctamente",
                "texto" => "Medicamento se ha registrado con exito",
                "Tipo" => "success"
            ];
            echo json_encode($alerta);
            exit();
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "texto" => "No se ha podido registrar el medicamento, por favor intente nuevamente!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */

    public function paginado_medicamento_controller($pagina, $registros, $privilegio, $url, $busqueda)
    {
        /* limpiamos cadenas para evitar inyección SQL */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $privilegio = mainModel::limpiar_cadena($privilegio);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . "/";
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = "";

        /* validamos que el valor ingresado por url sea un número */
        $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        if (isset($busqueda) && $busqueda != "") {
            $consulta = "
            SELECT 
                SQL_CALC_FOUND_ROWS 
                m.*, 
                la.la_nombre_comercial AS laboratorio_nombre,
                ff.ff_nombre AS forma_farmaceutica,
                vd.vd_nombre AS via_administracion,
                uf.uf_nombre AS uso_farmacologico,
                s.su_nombre AS sucursal_nombre
            FROM medicamento AS m
            LEFT JOIN laboratorios AS la ON m.la_id = la.la_id
            LEFT JOIN forma_farmaceutica AS ff ON m.ff_id = ff.ff_id
            LEFT JOIN via_de_administracion AS vd ON m.vd_id = vd.vd_id
            LEFT JOIN uso_farmacologico AS uf ON m.uf_id = uf.uf_id
            LEFT JOIN sucursales AS s ON m.su_id = s.su_id
            WHERE (
                m.med_nombre_quimico LIKE '%$busqueda%' OR
                m.med_principio_activo LIKE '%$busqueda%' OR
                m.med_presentacion LIKE '%$busqueda%' OR
                m.med_accion_farmacologica LIKE '%$busqueda%' OR
                la.la_nombre_comercial LIKE '%$busqueda%' OR
                ff.ff_nombre LIKE '%$busqueda%' OR
                vd.vd_nombre LIKE '%$busqueda%' OR
                uf.uf_nombre LIKE '%$busqueda%' OR
                s.su_nombre LIKE '%$busqueda%'
            )
            AND (la.la_estado = 1 OR la.la_estado IS NULL)
            AND (ff.ff_estado = 1 OR ff.ff_estado IS NULL)
            AND (vd.vd_estado = 1 OR vd.vd_estado IS NULL)
            AND (uf.uf_estado = 1 OR uf.uf_estado IS NULL)
            AND (s.su_estado = 1 OR s.su_estado IS NULL)
            ORDER BY m.med_nombre_quimico ASC 
            LIMIT $inicio, $registros
        ";
        } else {
            $consulta = "
            SELECT 
                SQL_CALC_FOUND_ROWS 
                m.*, 
                la.la_nombre_comercial AS laboratorio_nombre,
                ff.ff_nombre AS forma_farmaceutica,
                vd.vd_nombre AS via_administracion,
                uf.uf_nombre AS uso_farmacologico,
                s.su_nombre AS sucursal_nombre
            FROM medicamento AS m
            LEFT JOIN laboratorios AS la ON m.la_id = la.la_id
            LEFT JOIN forma_farmaceutica AS ff ON m.ff_id = ff.ff_id
            LEFT JOIN via_de_administracion AS vd ON m.vd_id = vd.vd_id
            LEFT JOIN uso_farmacologico AS uf ON m.uf_id = uf.uf_id
            LEFT JOIN sucursales AS s ON m.su_id = s.su_id
            WHERE (la.la_estado = 1 OR la.la_estado IS NULL)
                AND (ff.ff_estado = 1 OR ff.ff_estado IS NULL)
                AND (vd.vd_estado = 1 OR vd.vd_estado IS NULL)
                AND (uf.uf_estado = 1 OR uf.uf_estado IS NULL)
                AND (s.su_estado = 1 OR s.su_estado IS NULL)
            ORDER BY m.med_nombre_quimico ASC 
            LIMIT $inicio, $registros
        ";
        }

        /* realizamos la petición a la base de datos */
        $conexion = mainModel::conectar();
        $datos = $conexion->query($consulta);
        $datos = $datos->fetchAll();

        /* obtenemos la cantidad total de registros */
        $total = $conexion->query("SELECT FOUND_ROWS()");
        $total = (int)$total->fetchColumn();

        /* número de páginas por registros */
        $Npaginas = ceil($total / $registros);

        /* inicio de tabla */
        $tabla .= '
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>NOMBRE QUÍMICO</th>
                    <th>PRINCIPIO ACTIVO</th>
                    <th>ACCIÓN FARMACOLÓGICA</th>
                    <th>PRESENTACIÓN</th>
                    <th>USO FARMACOLÓGICO</th>
                    <th>FORMA FARMACÉUTICA</th>
                    <th>VÍA ADMINISTRACIÓN</th>
                    <th>LABORATORIO</th>
                    <th>SUCURSAL</th>
                    <th>PRECIO UNITARIO</th>
                    <th>PRECIO CAJA</th>
                    <th>CREADO EN</th>
                    <th>ACTUALIZADO EN</th>
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
                <td>' . htmlspecialchars($rows["med_nombre_quimico"]) . '</td>
                <td>' . htmlspecialchars($rows["med_principio_activo"]) . '</td>
                <td>' . htmlspecialchars($rows["med_accion_farmacologica"]) . '</td>
                <td>' . htmlspecialchars($rows["med_presentacion"]) . '</td>
                <td>' . htmlspecialchars($rows["uso_farmacologico"]) . '</td>
                <td>' . htmlspecialchars($rows["forma_farmaceutica"]) . '</td>
                <td>' . htmlspecialchars($rows["via_administracion"]) . '</td>
                <td>' . htmlspecialchars($rows["laboratorio_nombre"]) . '</td>
                <td>' . htmlspecialchars($rows["sucursal_nombre"]) . '</td>
                <td>Bs. ' . number_format($rows["med_precio_unitario"], 2) . '</td>
                <td>Bs. ' . number_format($rows["med_precio_caja"], 2) . '</td>
                <td>' . date('d/m/Y H:i', strtotime($rows["med_creado_en"])) . '</td>
                <td>' . date('d/m/Y H:i', strtotime($rows["med_actualizado_en"])) . '</td>
                <td>
                    <a href="' . SERVER_URL . 'medicamentoActualizar/' . mainModel::encryption($rows['med_id']) . '/" class="btn-editar">Editar</a>
                    <form action="' . SERVER_URL . 'ajax/medicamentoAjax.php" class="FormularioAjax" method="POST" data-form="delete" autocomplete="off">
                        <input type="hidden" name="medicamento_del" value="' . mainModel::encryption($rows['med_id']) . '">
                        <button type="submit" class="btn-disable">Eliminar</button>
                    </form>
                </td>
            </tr>
        ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            if ($total >= 1) {
                $tabla .= '<tr><td colspan="15"><a class="btn-primary" href="' . $url . '">Recargar</a></td></tr>';
            } else {
                $tabla .= '<tr><td colspan="15">No hay registros</td></tr>';
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

        return $tabla;
    }



    /* -----------------------------------controlador para recuperar datos de un medicamento en especifico------------------------------------------ */
    public function datos_medicamento_controller($id)
    {
        $id = mainModel::decryption($id);
        $id =  mainModel::limpiar_cadena($id);

        $sql = mainModel::conectar()->prepare("SELECT * FROM medicamento WHERE med_id = '$id'");
        $sql->execute();
        return $sql;
    }


    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    public function actualizar_medicamento_controller()
    {
        /* desencritamos la id de medicamento */
        $id = mainModel::decryption($_POST['id']);
        $id = mainModel::limpiar_cadena($id);
        /* verificamos que el medicamento exista en la base de datos */
        $check_med = mainModel::ejecutar_consulta_simple("SELECT * FROM medicamento WHERE med_id = '$id'");
        if ($check_med->rowCount() <= 0) {
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error inesperado",
                "texto"  => "No se encontró el medicamento solicitado para actualizar.",
                "Tipo"   => "error"
            ]);
            exit();
        }
        /* en caso que el medicamento exista reserbamos la informacion actual  */
        $campos = $check_med->fetch();
        /*  con el metodo post tomamos todos los datos del formulario */
        $nombre        = mainModel::limpiar_cadena($_POST['Nombre_up']);
        $principio     = mainModel::limpiar_cadena($_POST['Principio_up']);
        $accion        = mainModel::limpiar_cadena($_POST['Accion_up']);
        $descripcion   = mainModel::limpiar_cadena($_POST['Descripcion_up']);
        $presentacion  = mainModel::limpiar_cadena($_POST['Presentacion_up']);
        $unitario      = mainModel::limpiar_cadena($_POST['Precio_unitario_up']);
        $caja          = mainModel::limpiar_cadena($_POST['Precio_caja_up']);
        $uso           = mainModel::limpiar_cadena($_POST['Uso_up']);
        $forma         = mainModel::limpiar_cadena($_POST['Forma_up']);
        $via           = mainModel::limpiar_cadena($_POST['Via_up']);
        $laboratorio   = mainModel::limpiar_cadena($_POST['Laboratorio_up']);
        $sucursal      = mainModel::limpiar_cadena($_POST['Sucursal_up']);
        /* verificamos que los campos obligatorios sean llenados correctamente */
        if (
            $nombre == "" || $principio == "" || $accion == "" || $presentacion == "" ||
            $unitario == "" || $caja == "" || $uso == "" || $forma == "" ||
            $via == "" || $laboratorio == "" || $sucursal == ""
        ) {
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error de validación",
                "texto"  => "Por favor, completa todos los campos obligatorios.",
                "Tipo"   => "error"
            ]);
            exit();
        }
        /* verificamos que el formulario tenga el formato solicitado */
        $pattern_texto = "[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}";
        $pattern_precio = "[0-9.]{1,10}";

        $validaciones = [
            ["campo" => $nombre, "patron" => $pattern_texto, "msg" => "El nombre comercial no cumple con el formato requerido."],
            ["campo" => $principio, "patron" => $pattern_texto, "msg" => "El principio activo no cumple con el formato requerido."],
            ["campo" => $accion, "patron" => $pattern_texto, "msg" => "La acción farmacológica no cumple con el formato requerido."],
            ["campo" => $presentacion, "patron" => $pattern_texto, "msg" => "La presentación no cumple con el formato requerido."],
            ["campo" => $unitario, "patron" => $pattern_precio, "msg" => "El precio unitario no cumple con el formato requerido."],
            ["campo" => $caja, "patron" => $pattern_precio, "msg" => "El precio por caja no cumple con el formato requerido."]
        ];

        foreach ($validaciones as $v) {
            if (mainModel::verificar_datos($v["patron"], $v["campo"])) {
                echo json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error de formato",
                    "texto"  => $v["msg"],
                    "Tipo"   => "error"
                ]);
                exit();
            }
        }
        /* validamos en enteros los campos con id foraneas */
        $uso = (int)$uso;
        $forma = (int)$forma;
        $via = (int)$via;
        $laboratorio = (int)$laboratorio;
        $sucursal = (int)$sucursal;

        /* preguntamos si estan vacios */
        if ($uso <= 0 || $forma <= 0 || $via <= 0 || $laboratorio <= 0 || $sucursal <= 0) {
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error de validación",
                "texto"  => "Por favor selecciona valores válidos en todos los campos desplegables.",
                "Tipo"   => "error"
            ]);
            exit();
        }
        /* en caso que se quiera restringir el duplicado de informacion */
        /*         if ($nombre != $campos['med_nombre_quimico']) {
            $check_nombre = mainModel::ejecutar_consulta_simple("SELECT med_id FROM medicamento WHERE med_nombre_quimico = '$nombre'");
            if ($check_nombre->rowCount() > 0) {
                echo json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Duplicado",
                    "texto"  => "Ya existe un medicamento con ese nombre comercial.",
                    "Tipo"   => "error"
                ]);
                exit();
            }
        } */
        /* alacenamos la informacion ya procesada y filtrada */
        $datos_med_up = [
            "Nombre"         => $nombre,
            "Principio"      => $principio,
            "Accion"         => $accion,
            "Descripcion"    => $descripcion,
            "Presentacion"   => $presentacion,
            "PrecioUnitario" => $unitario,
            "PrecioCaja"     => $caja,
            "Uso"            => $uso,
            "Forma"          => $forma,
            "Via"            => $via,
            "Laboratorio"    => $laboratorio,
            "Sucursal"       => $sucursal,
            "Id"             => $id
        ];
        /* preguntamos si se actualizo correctamente los datos */
        if (medicamentoModel::actualizar_medicamento_model($datos_med_up)) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Actualizado correctamente",
                "texto"  => "El medicamento se actualizó exitosamente.",
                "Tipo"   => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error inesperado",
                "texto"  => "No se pudo actualizar el medicamento, intenta nuevamente.",
                "Tipo"   => "error"
            ];
        }

        echo json_encode($alerta);
    }


    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    public function paginado_medicamento_via_controller($pagina, $registros, $privilegio, $url, $busqueda)
    {
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $privilegio = mainModel::limpiar_cadena($privilegio);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . "/";
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = "";

        $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        if (isset($busqueda) && $busqueda != "") {
            $consulta = "
            SELECT SQL_CALC_FOUND_ROWS *
            FROM via_de_administracion
            WHERE vd_nombre LIKE '%$busqueda%'
            ORDER BY vd_nombre ASC
            LIMIT $inicio, $registros
        ";
        } else {
            $consulta = "
            SELECT SQL_CALC_FOUND_ROWS *
            FROM via_de_administracion
            ORDER BY vd_nombre ASC
            LIMIT $inicio, $registros
        ";
        }

        $conexion = mainModel::conectar();
        $datos = $conexion->query($consulta);
        $datos = $datos->fetchAll();

        $total = $conexion->query("SELECT FOUND_ROWS()");
        $total = (int)$total->fetchColumn();
        $Npaginas = ceil($total / $registros);

        $tabla .= '
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>NOMBRE</th>
                    <th>IMAGEN</th>
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
                // Estado visual
                $estado = $rows['vd_estado'] == 1 ? '<span class="estado-activo">Activo</span>' : '<span class="estado-inactivo">Inactivo</span>';

                // Imagen (si existe)
                $img_tag = $rows['vd_imagen']
                    ? '<img src="' . SERVER_URL . 'uploads/vias/' . htmlspecialchars($rows['vd_imagen']) . '" alt="imagen" class="thumb-table">'
                    : '<span class="sin-imagen">—</span>';

                $tabla .= '
                <tr>
                    <td>' . $contador . '</td>
                    <td>' . htmlspecialchars($rows["vd_nombre"]) . '</td>
                    <td>' . $img_tag . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($rows["vd_creado_en"])) . '</td>
                    <td>' . date('d/m/Y H:i', strtotime($rows["vd_actualizado_en"])) . '</td>
                    <td>' . $estado . '</td>
                    <td>
                        <a href="' . SERVER_URL . 'viaActualizar/' . mainModel::encryption($rows['vd_id']) . '/" class="btn-editar">Editar</a>
                        <form action="' . SERVER_URL . 'ajax/viaAjax.php" class="FormularioAjax" method="POST" data-form="delete" autocomplete="off">
                            <input type="hidden" name="via_del" value="' . mainModel::encryption($rows['vd_id']) . '">
                            <button type="submit" class="btn-disable">Eliminar</button>
                        </form>
                    </td>
                </tr>
            ';
                $contador++;
            }

            $reg_final = $contador - 1;
        } else {
            if ($total >= 1) {
                $tabla .= '<tr><td colspan="7"><a class="btn-primary" href="' . $url . '">Recargar</a></td></tr>';
            } else {
                $tabla .= '<tr><td colspan="7">No hay registros en el sistema.</td></tr>';
            }
        }

        $tabla .= '
            </tbody>
        </table>
    </div>
    ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p>Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
}
