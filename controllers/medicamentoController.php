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

        $uso = mainModel::limpiar_cadena($_POST['Uso_reg']);
        $forma = mainModel::limpiar_cadena($_POST['Forma_reg']);
        $via = mainModel::limpiar_cadena($_POST['Via_reg']);
        $laboratorio = mainModel::limpiar_cadena($_POST['Laboratorio_reg']);

        $uso = (int)$uso;
        $forma = (int)$forma;
        $via = (int)$via;
        $laboratorio = (int)$laboratorio;

        /* verificamos que los campos requeridos no esten vacios */
        if ($nombre == "" || $principio == "" || $accion == "" || $presentacion == "" || $uso == "" || $forma == "" || $via == "" || $laboratorio == "") {
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

        /* verificar valor de selects no negativo */
        if ($uso <= 0 || $forma <= 0 || $via <= 0 || $laboratorio <= 0) {
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
            "Descripcion" => $descripcion
        ];
        $agregar_datos = medicamentoModel::agregar_medicamento_model($datos_med);

        /* verificamos que el modelo inserto la informacion en la base de datos */

        if ($agregar_datos->rowCount() == 1) {
            $alerta = [
                "Alerta" => "recargar",
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

    public function paginado_medicamento_controller($pagina, $registros, $privilegio, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "")
    {
        /* Limpiar parámetros */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $privilegio = mainModel::limpiar_cadena($privilegio); 
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1); 
        $f2 = mainModel::limpiar_cadena($f2); 
        $f3 = mainModel::limpiar_cadena($f3); 
        $f4 = mainModel::limpiar_cadena($f4); 

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        /* ===== CONSTRUIR WHERE DINÁMICO ===== */
        $whereParts = [];

        // Validar estados activos
        $whereParts[] = "(la.la_estado = 1 OR la.la_estado IS NULL)";
        $whereParts[] = "(ff.ff_estado = 1 OR ff.ff_estado IS NULL)";
        $whereParts[] = "(vd.vd_estado = 1 OR vd.vd_estado IS NULL)";
        $whereParts[] = "(uf.uf_estado = 1 OR uf.uf_estado IS NULL)";

        // Búsqueda por texto
        if (!empty($busqueda)) {
            $whereParts[] = "(
                    m.med_nombre_quimico LIKE '%$busqueda%' OR
                    m.med_principio_activo LIKE '%$busqueda%' OR
                    m.med_presentacion LIKE '%$busqueda%' OR
                    m.med_accion_farmacologica LIKE '%$busqueda%' OR
                    la.la_nombre_comercial LIKE '%$busqueda%'
                )";
        }

        // Filtros por selects
        if ($f1 !== '' && is_numeric($f1)) {
            $whereParts[] = "m.la_id = " . (int)$f1;
        }

        if ($f2 !== '' && is_numeric($f2)) {
            $whereParts[] = "m.vd_id = " . (int)$f2;
        }

        if ($f3 !== '' && is_numeric($f3)) {
            $whereParts[] = "m.ff_id = " . (int)$f3;
        }

        if ($f4 !== '' && is_numeric($f4)) {
            $whereParts[] = "m.uf_id = " . (int)$f4;
        }

        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";

        /* ===== CONSULTA SQL ===== */
        $consulta = "
                SELECT 
                    SQL_CALC_FOUND_ROWS 
                    m.med_id,
                    m.med_nombre_quimico,
                    m.med_principio_activo,
                    m.med_accion_farmacologica,
                    m.med_presentacion,
                    m.med_creado_en,
                    m.med_actualizado_en,
                    la.la_nombre_comercial AS laboratorio_nombre,
                    ff.ff_nombre AS forma_farmaceutica,
                    vd.vd_nombre AS via_administracion,
                    uf.uf_nombre AS uso_farmacologico
                FROM medicamento AS m
                LEFT JOIN laboratorios AS la ON m.la_id = la.la_id
                LEFT JOIN forma_farmaceutica AS ff ON m.ff_id = ff.ff_id
                LEFT JOIN via_de_administracion AS vd ON m.vd_id = vd.vd_id
                LEFT JOIN uso_farmacologico AS uf ON m.uf_id = uf.uf_id
                $whereSQL
                ORDER BY m.med_nombre_quimico ASC 
                LIMIT $inicio, $registros
            ";

        /* ===== EJECUTAR CONSULTA ===== */
        try {
            $conexion = mainModel::conectar();

            // 🛠️ DEBUG: Mostrar la consulta generada
            error_log("=== SQL GENERADO ===");
            error_log($consulta);

            $datos = $conexion->query($consulta);
            $datos = $datos->fetchAll();

            $total = $conexion->query("SELECT FOUND_ROWS()");
            $total = (int)$total->fetchColumn();

            error_log("Total registros encontrados: $total");
        } catch (PDOException $e) {
            error_log("❌ ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                            <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        /* ===== CONSTRUIR TABLA ===== */
        $tabla .= '
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>NOMBRE QUÍMICO</th>
                                <th>PRINCIPIO ACTIVO</th>
                                <th>LABORATORIO</th>
                                <th>FORMA</th>
                                <th>VÍA</th>
                                <th>USO</th>
                                <th>PRESENTACIÓN</th>
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
                            <td><strong>' . htmlspecialchars($rows['med_nombre_quimico']) . '</strong></td>
                            <td>' . htmlspecialchars($rows['med_principio_activo']) . '</td>
                            <td>' . htmlspecialchars($rows['laboratorio_nombre'] ?? 'Sin laboratorio') . '</td>
                            <td>' . htmlspecialchars($rows['forma_farmaceutica'] ?? 'Sin forma') . '</td>
                            <td>' . htmlspecialchars($rows['via_administracion'] ?? 'Sin vía') . '</td>
                            <td>' . htmlspecialchars($rows['uso_farmacologico'] ?? 'Sin uso') . '</td>
                            <td>' . htmlspecialchars($rows['med_presentacion']) . '</td>
                            <td class="accion-buttons">
                                <a href="' . SERVER_URL . 'medicamentoActualizar/' . mainModel::encryption($rows['med_id']) . '/" class="btn danger">
                                    <ion-icon name="create-outline"></ion-icon> Editar
                                </a>
                            </td>
                        </tr>
                    ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            if ($total >= 1) {
                $tabla .= '<tr><td colspan="9"><a class="btn-primary" href="' . $url . '">Recargar</a></td></tr>';
            } else {
                $tabla .= '<tr><td colspan="9" style="text-align:center;padding:20px;color:#999;">
                                <ion-icon name="bug-outline"></ion-icon> No hay registros que coincidan con los filtros aplicados
                            </td></tr>';
            }
        }

        $tabla .= '
                        </tbody>
                    </table>
                </div>
            ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
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
        $uso           = mainModel::limpiar_cadena($_POST['Uso_up']);
        $forma         = mainModel::limpiar_cadena($_POST['Forma_up']);
        $via           = mainModel::limpiar_cadena($_POST['Via_up']);
        $laboratorio   = mainModel::limpiar_cadena($_POST['Laboratorio_up']);
        /* verificamos que los campos obligatorios sean llenados correctamente */
        if (
            $nombre == "" || $principio == "" || $accion == "" || $presentacion == "" ||
            $uso == "" || $forma == "" || $via == "" || $laboratorio == ""
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

        $validaciones = [
            ["campo" => $nombre, "patron" => $pattern_texto, "msg" => "El nombre comercial no cumple con el formato requerido."],
            ["campo" => $principio, "patron" => $pattern_texto, "msg" => "El principio activo no cumple con el formato requerido."],
            ["campo" => $accion, "patron" => $pattern_texto, "msg" => "La acción farmacológica no cumple con el formato requerido."],
            ["campo" => $presentacion, "patron" => $pattern_texto, "msg" => "La presentación no cumple con el formato requerido."]
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

        /* preguntamos si estan vacios */
        if ($uso <= 0 || $forma <= 0 || $via <= 0 || $laboratorio <= 0) {
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
            "Uso"            => $uso,
            "Forma"          => $forma,
            "Via"            => $via,
            "Laboratorio"    => $laboratorio,
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
    /* -----------------------------------controlador saber el ultimo id de lote------------------------------------------ */
    public function ultimo_lote_controller()
    {
        $sql = mainModel::ejecutar_consulta_simple("
            SELECT lm_numero_lote FROM `lote_medicamento` WHERE lm_numero_lote REGEXP '^MED-[0-9]+$' ORDER BY CAST(SUBSTRING_INDEX(lm_numero_lote, '-', -1) AS UNSIGNED) DESC LIMIT 1
        ");

        $data = $sql->fetch();
        return $data['lm_numero_lote'] ?? 0;
    }
    /* -----------------------------------controlador para optener ultimos datos de  compra----------------------------------------- */

    public function ultima_compra_controller()
    {
        $sql = mainModel::conectar()->prepare("
            SELECT co_numero FROM `compras` WHERE co_numero REGEXP '^COMP-[0-9]{4}-[0-9]+$' ORDER BY CAST(SUBSTRING_INDEX(co_numero, '-', -1) AS UNSIGNED) DESC LIMIT 1
        ");
        $sql->execute();

        $resultado = $sql->fetch();
        return $resultado['co_numero'] ?? 0;
    }
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
    /* -----------------------------------controlador para agregar usuarios------------------------------------------ */
}
