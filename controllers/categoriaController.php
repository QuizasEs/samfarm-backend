<?php

if ($peticionAjax) {
    require_once "../models/categoriaModel.php";
} else {
    require_once "./models/categoriaModel.php";
}

class categoriaController extends categoriaModel
{
    private function guardar_imagen_desde_archivo($tipo = 'uso_farmacologico')
    {
        if (!isset($_FILES['imgLoad_uso']) && !isset($_FILES['imgLoad_uso_edit'])) {
            return '';
        }

        $archivo = isset($_FILES['imgLoad_uso']) ? $_FILES['imgLoad_uso'] : $_FILES['imgLoad_uso_edit'];

        if ($archivo['error'] !== UPLOAD_ERR_OK || empty($archivo['name'])) {
            return '';
        }

        $img_dir = dirname(__FILE__) . '/../views/assets/img/';

        if (!is_dir($img_dir)) {
            mkdir($img_dir, 0755, true);
        }

        $mime_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $archivo['tmp_name']);
        if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return '';
        }

        if (($archivo['size'] / 1024) > 5120) {
            return '';
        }

        $extension = '';
        if ($mime_type === 'image/jpeg') {
            $extension = '.jpg';
        } elseif ($mime_type === 'image/png') {
            $extension = '.png';
        } elseif ($mime_type === 'image/gif') {
            $extension = '.gif';
        } elseif ($mime_type === 'image/webp') {
            $extension = '.webp';
        }

        $random_id = bin2hex(random_bytes(8));
        $nombre_archivo = $tipo . "_" . $random_id . "_" . time() . $extension;

        if (!move_uploaded_file($archivo['tmp_name'], $img_dir . $nombre_archivo)) {
            error_log("ERROR: No se pudo mover archivo a: " . $img_dir . $nombre_archivo);
            return '';
        }

        chmod($img_dir . $nombre_archivo, 0644);

        $ruta_final = SERVER_URL . 'views/assets/img/' . $nombre_archivo;
        error_log("DEBUG GUARDAR IMAGEN: archivo=$nombre_archivo | ruta=$ruta_final");
        return $ruta_final;
    }

    private function eliminar_imagen($ruta_imagen)
    {
        if (empty($ruta_imagen)) {
            return;
        }

        $ruta_relativa = str_replace(SERVER_URL, '', $ruta_imagen);
        $ruta_absoluta = dirname(__FILE__) . '/../' . $ruta_relativa;

        if (file_exists($ruta_absoluta)) {
            chmod($ruta_absoluta, 0777);
            unlink($ruta_absoluta);
        }
    }

    public function paginado_uso_farmacologico_controller($pagina, $registros, $url, $busqueda = "")
    {
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        try {
            $datosStmt = self::listar_uso_farmacologico_model($inicio, $registros, $busqueda);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);
            $total = self::contar_uso_farmacologico_model($busqueda);
        } catch (PDOException $e) {
            return '<div class="error" style="padding:20px;color:red;">Error en la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>IMAGEN</th>
                            <th>NOMBRE</th>
                            <th>FECHA CREACIÓN</th>
                            <th>ÚLTIMA ACTUALIZACIÓN</th>
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
                $estado_html = $rows['uf_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';

                $tiene_imagen = !empty($rows['uf_imagen']) && strlen(trim($rows['uf_imagen'])) > 10;
                $imagen_src = $tiene_imagen ? $rows['uf_imagen'] : SERVER_URL . 'views/assets/img/predeterminado.png';


                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>
                            <img src="' . ($tiene_imagen ? htmlspecialchars($imagen_src) : $imagen_src) . '" 
                                alt="' . htmlspecialchars($rows['uf_nombre']) . '" 
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                onerror="this.src=\'' . SERVER_URL . 'views/assets/img/predeterminado.png\'">
                        </td>
                        <td><strong>' . htmlspecialchars($rows['uf_nombre']) . '</strong></td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['uf_creado_en'])) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['uf_actualizado_en'])) . '</td>
                        <td>' . $estado_html . '</td>
                        <td class="buttons">
                            <a href="javascript:void(0)" 
                               class="btn default" 
                               onclick="abrirModalEditarUsoFarmacologico(' . $rows['uf_id'] . ')">
                                <ion-icon name="create-outline"></ion-icon> EDITAR
                            </a>
                            <a href="javascript:void(0)" 
                               class="btn ' . ($rows['uf_estado'] == 1 ? 'danger' : 'success') . '" 
                               onclick="cambiarEstadoUsoFarmacologico(' . $rows['uf_id'] . ', ' . ($rows['uf_estado'] == 1 ? 0 : 1) . ')">
                                <ion-icon name="' . ($rows['uf_estado'] == 1 ? 'ban-outline' : 'checkmark-outline') . '"></ion-icon> 
                                ' . ($rows['uf_estado'] == 1 ? 'DESACTIVAR' : 'ACTIVAR') . '
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="7" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="folder-open-outline"></ion-icon> No hay registros disponibles
                    </td></tr>';
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

    public function agregar_uso_farmacologico_controller()
    {
        $nombre = mainModel::limpiar_cadena($_POST['nombre_uso']);

        if (!empty($nombre)) {
            if (self::verificar_nombre_uso_existe_model($nombre)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Nombre duplicado',
                    'texto' => 'El nombre de uso farmacológico ya existe',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = '';
        if (isset($_FILES['imgLoad_uso']) && $_FILES['imgLoad_uso']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('uso_farmacologico');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => 1
        ];

        error_log("DEBUG AGREGAR - imagen: " . var_export($ruta_imagen, true) . " | SERVER_URL: " . SERVER_URL);

        $resultado = self::agregar_uso_farmacologico_model($datos);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Registro exitoso',
                'texto' => 'El uso farmacológico se registró correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (!empty($ruta_imagen)) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo registrar el uso farmacológico',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function obtener_uso_farmacologico_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['error' => 'ID inválido']);
            exit();
        }

        $stmt = self::obtener_uso_farmacologico_model($id);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dato) {
            echo json_encode($dato);
        } else {
            echo json_encode(['error' => 'No encontrado']);
        }
        exit();
    }

    public function actualizar_uso_farmacologico_controller()
    {
        $id = intval($_POST['id_uso_edit']);
        $nombre = mainModel::limpiar_cadena($_POST['nombre_uso_edit']);

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (empty($nombre)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (self::verificar_nombre_uso_existe_model($nombre, $id)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Nombre duplicado',
                'texto' => 'El nombre ya está en uso por otro registro',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $stmt = self::obtener_uso_farmacologico_model($id);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$actual) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Registro no encontrado',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = $actual['uf_imagen'];
        $imagen_antigua = $actual['uf_imagen'];

        if (isset($_FILES['imgLoad_uso_edit']) && $_FILES['imgLoad_uso_edit']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('uso_farmacologico');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => $actual['uf_estado']
        ];

        $resultado = self::actualizar_uso_farmacologico_model($datos);

        if ($resultado->rowCount() >= 0) {
            if (!empty($imagen_antigua) && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($imagen_antigua);
            }
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Actualización exitosa',
                'texto' => 'El uso farmacológico se actualizó correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (isset($_FILES['imgLoad_uso_edit']) && $_FILES['imgLoad_uso_edit']['error'] === UPLOAD_ERR_OK && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo actualizar el uso farmacológico',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function cambiar_estado_uso_farmacologico_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $resultado = self::cambiar_estado_uso_farmacologico_model($id, $estado);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Estado actualizado',
                'texto' => 'El estado se cambió correctamente',
                'Tipo' => 'success'
            ];
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo cambiar el estado',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    /* via de administracion */
    public function paginado_via_administracion_controller($pagina, $registros, $url, $busqueda = "")
    {
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        try {
            $datosStmt = self::listar_via_administracion_model($inicio, $registros, $busqueda);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);
            $total = self::contar_via_administracion_model($busqueda);
        } catch (PDOException $e) {
            return '<div class="error" style="padding:20px;color:red;">Error en la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>IMAGEN</th>
                            <th>NOMBRE</th>
                            <th>FECHA CREACIÓN</th>
                            <th>ÚLTIMA ACTUALIZACIÓN</th>
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
                $estado_html = $rows['vd_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';

                $tiene_imagen = !empty($rows['vd_imagen']) && strlen(trim($rows['vd_imagen'])) > 10;
                $imagen_src = $tiene_imagen ? $rows['vd_imagen'] : SERVER_URL . 'views/assets/img/predeterminado.png';

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>
                            <img src="' . ($tiene_imagen ? htmlspecialchars($imagen_src) : $imagen_src) . '" 
                                 alt="' . htmlspecialchars($rows['vd_nombre']) . '" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                 onerror="this.src=\'' . SERVER_URL . 'views/assets/img/predeterminado.png\'">
                        </td>
                        <td><strong>' . htmlspecialchars($rows['vd_nombre']) . '</strong></td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['vd_creado_en'])) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['vd_actualizado_en'])) . '</td>
                        <td>' . $estado_html . '</td>
                        <td class="buttons">
                            <a href="javascript:void(0)" 
                               class="btn default" 
                               onclick="abrirModalEditarViaAdministracion(' . $rows['vd_id'] . ')">
                                <ion-icon name="create-outline"></ion-icon> EDITAR
                            </a>
                            <a href="javascript:void(0)" 
                               class="btn ' . ($rows['vd_estado'] == 1 ? 'danger' : 'success') . '" 
                               onclick="cambiarEstadoViaAdministracion(' . $rows['vd_id'] . ', ' . ($rows['vd_estado'] == 1 ? 0 : 1) . ')">
                                <ion-icon name="' . ($rows['vd_estado'] == 1 ? 'ban-outline' : 'checkmark-outline') . '"></ion-icon> 
                                ' . ($rows['vd_estado'] == 1 ? 'DESACTIVAR' : 'ACTIVAR') . '
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="7" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="folder-open-outline"></ion-icon> No hay registros disponibles
                    </td></tr>';
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

    public function agregar_via_administracion_controller()
    {
        $nombre = mainModel::limpiar_cadena($_POST['nombre_via']);

        if (!empty($nombre)) {
            if (self::verificar_nombre_via_existe_model($nombre)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Nombre duplicado',
                    'texto' => 'El nombre de vía de administración ya existe',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = '';
        if (isset($_FILES['imgLoad_via']) && $_FILES['imgLoad_via']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('via_administracion');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => 1
        ];

        $resultado = self::agregar_via_administracion_model($datos);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Registro exitoso',
                'texto' => 'La vía de administración se registró correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (!empty($ruta_imagen)) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo registrar la vía de administración',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function obtener_via_administracion_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['error' => 'ID inválido']);
            exit();
        }

        $stmt = self::obtener_via_administracion_model($id);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dato) {
            echo json_encode($dato);
        } else {
            echo json_encode(['error' => 'No encontrado']);
        }
        exit();
    }

    public function actualizar_via_administracion_controller()
    {
        $id = intval($_POST['id_via_edit']);
        $nombre = mainModel::limpiar_cadena($_POST['nombre_via_edit']);

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (empty($nombre)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (self::verificar_nombre_via_existe_model($nombre, $id)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Nombre duplicado',
                'texto' => 'El nombre ya está en uso por otro registro',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $stmt = self::obtener_via_administracion_model($id);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$actual) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Registro no encontrado',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = $actual['vd_imagen'];
        $imagen_antigua = $actual['vd_imagen'];

        if (isset($_FILES['imgLoad_via_edit']) && $_FILES['imgLoad_via_edit']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('via_administracion');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => $actual['vd_estado']
        ];

        $resultado = self::actualizar_via_administracion_model($datos);

        if ($resultado->rowCount() >= 0) {
            if (!empty($imagen_antigua) && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($imagen_antigua);
            }
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Actualización exitosa',
                'texto' => 'La vía de administración se actualizó correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (isset($_FILES['imgLoad_via_edit']) && $_FILES['imgLoad_via_edit']['error'] === UPLOAD_ERR_OK && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo actualizar la vía de administración',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function cambiar_estado_via_administracion_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $resultado = self::cambiar_estado_via_administracion_model($id, $estado);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Estado actualizado',
                'texto' => 'El estado se cambió correctamente',
                'Tipo' => 'success'
            ];
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo cambiar el estado',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function paginado_forma_farmaceutica_controller($pagina, $registros, $url, $busqueda = "")
    {
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        try {
            $datosStmt = self::listar_forma_farmaceutica_model($inicio, $registros, $busqueda);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);
            $total = self::contar_forma_farmaceutica_model($busqueda);
        } catch (PDOException $e) {
            return '<div class="error" style="padding:20px;color:red;">Error en la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>IMAGEN</th>
                            <th>NOMBRE</th>
                            <th>FECHA CREACIÓN</th>
                            <th>ÚLTIMA ACTUALIZACIÓN</th>
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
                $estado_html = $rows['ff_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';

                $tiene_imagen = !empty($rows['ff_imagen']) && strlen(trim($rows['ff_imagen'])) > 10;
                $imagen_src = $tiene_imagen ? $rows['ff_imagen'] : SERVER_URL . 'views/assets/img/predeterminado.png';

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>
                            <img src="' . ($tiene_imagen ? htmlspecialchars($imagen_src) : $imagen_src) . '" 
                                alt="' . htmlspecialchars($rows['ff_nombre']) . '" 
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                onerror="this.src=\'' . SERVER_URL . 'views/assets/img/predeterminado.png\'">
                        </td>
                        <td><strong>' . htmlspecialchars($rows['ff_nombre']) . '</strong></td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['ff_creado_en'])) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['ff_actualizado_en'])) . '</td>
                        <td>' . $estado_html . '</td>
                        <td class="buttons">
                            <a href="javascript:void(0)" 
                               class="btn default" 
                               onclick="abrirModalEditarFormaFarmaceutica(' . $rows['ff_id'] . ')">
                                <ion-icon name="create-outline"></ion-icon> EDITAR
                            </a>
                            <a href="javascript:void(0)" 
                               class="btn ' . ($rows['ff_estado'] == 1 ? 'danger' : 'success') . '" 
                               onclick="cambiarEstadoFormaFarmaceutica(' . $rows['ff_id'] . ', ' . ($rows['ff_estado'] == 1 ? 0 : 1) . ')">
                                <ion-icon name="' . ($rows['ff_estado'] == 1 ? 'ban-outline' : 'checkmark-outline') . '"></ion-icon> 
                                ' . ($rows['ff_estado'] == 1 ? 'DESACTIVAR' : 'ACTIVAR') . '
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="7" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="folder-open-outline"></ion-icon> No hay registros disponibles
                    </td></tr>';
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

    public function agregar_forma_farmaceutica_controller()
    {
        $nombre = mainModel::limpiar_cadena($_POST['nombre_forma']);

        if (!empty($nombre)) {
            if (self::verificar_nombre_forma_farmaceutica_existe_model($nombre)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Nombre duplicado',
                    'texto' => 'El nombre de forma farmacéutica ya existe',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = '';
        if (isset($_FILES['imgLoad_forma']) && $_FILES['imgLoad_forma']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('forma_farmaceutica');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => 1
        ];

        $resultado = self::agregar_forma_farmaceutica_model($datos);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Registro exitoso',
                'texto' => 'La forma farmacéutica se registró correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (!empty($ruta_imagen)) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo registrar la forma farmacéutica',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function obtener_forma_farmaceutica_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['error' => 'ID inválido']);
            exit();
        }

        $stmt = self::obtener_forma_farmaceutica_model($id);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dato) {
            echo json_encode($dato);
        } else {
            echo json_encode(['error' => 'No encontrado']);
        }
        exit();
    }

    public function actualizar_forma_farmaceutica_controller()
    {
        $id = intval($_POST['id_forma_edit']);
        $nombre = mainModel::limpiar_cadena($_POST['nombre_forma_edit']);

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (empty($nombre)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (self::verificar_nombre_forma_farmaceutica_existe_model($nombre, $id)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Nombre duplicado',
                'texto' => 'El nombre ya está en uso por otro registro',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $stmt = self::obtener_forma_farmaceutica_model($id);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$actual) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Registro no encontrado',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = $actual['ff_imagen'];
        $imagen_antigua = $actual['ff_imagen'];

        if (isset($_FILES['imgLoad_forma_edit']) && $_FILES['imgLoad_forma_edit']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('forma_farmaceutica');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => $actual['ff_estado']
        ];

        $resultado = self::actualizar_forma_farmaceutica_model($datos);

        if ($resultado->rowCount() >= 0) {
            if (!empty($imagen_antigua) && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($imagen_antigua);
            }
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Actualización exitosa',
                'texto' => 'La forma farmacéutica se actualizó correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (isset($_FILES['imgLoad_forma_edit']) && $_FILES['imgLoad_forma_edit']['error'] === UPLOAD_ERR_OK && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo actualizar la forma farmacéutica',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function cambiar_estado_forma_farmaceutica_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $resultado = self::cambiar_estado_forma_farmaceutica_model($id, $estado);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Estado actualizado',
                'texto' => 'El estado se cambió correctamente',
                'Tipo' => 'success'
            ];
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo cambiar el estado',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function paginado_laboratorios_controller($pagina, $registros, $url, $busqueda = "")
    {
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        try {
            $datosStmt = self::listar_laboratorios_model($inicio, $registros, $busqueda);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);
            $total = self::contar_laboratorios_model($busqueda);
        } catch (PDOException $e) {
            return '<div class="error" style="padding:20px;color:red;">Error en la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>LOGO</th>
                            <th>NOMBRE</th>
                            <th>FECHA CREACIÓN</th>
                            <th>ÚLTIMA ACTUALIZACIÓN</th>
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
                $estado_html = $rows['la_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';

                $tiene_imagen = !empty($rows['la_logo']) && strlen(trim($rows['la_logo'])) > 10;
                $imagen_src = $tiene_imagen ? $rows['la_logo'] : SERVER_URL . 'views/assets/img/predeterminado.png';

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>
                            <img src="' . ($tiene_imagen ? htmlspecialchars($imagen_src) : $imagen_src) . '" 
                                alt="' . htmlspecialchars($rows['la_nombre_comercial']) . '" 
                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                onerror="this.src=\'' . SERVER_URL . 'views/assets/img/predeterminado.png\'">
                        </td>
                        <td><strong>' . htmlspecialchars($rows['la_nombre_comercial']) . '</strong></td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['la_creado_en'])) . '</td>
                        <td>' . date('d/m/Y H:i', strtotime($rows['la_actualizado_en'])) . '</td>
                        <td>' . $estado_html . '</td>
                        <td class="buttons">
                            <a href="javascript:void(0)" 
                               class="btn default" 
                               onclick="abrirModalEditarLaboratorio(' . $rows['la_id'] . ')">
                                <ion-icon name="create-outline"></ion-icon> EDITAR
                            </a>
                            <a href="javascript:void(0)" 
                               class="btn ' . ($rows['la_estado'] == 1 ? 'danger' : 'success') . '" 
                               onclick="cambiarEstadoLaboratorio(' . $rows['la_id'] . ', ' . ($rows['la_estado'] == 1 ? 0 : 1) . ')">
                                <ion-icon name="' . ($rows['la_estado'] == 1 ? 'ban-outline' : 'checkmark-outline') . '"></ion-icon> 
                                ' . ($rows['la_estado'] == 1 ? 'DESACTIVAR' : 'ACTIVAR') . '
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="7" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="folder-open-outline"></ion-icon> No hay registros disponibles
                    </td></tr>';
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

    public function agregar_laboratorios_controller()
    {
        $nombre = mainModel::limpiar_cadena($_POST['nombre_laboratorio']);

        if (!empty($nombre)) {
            if (self::verificar_nombre_laboratorios_existe_model($nombre)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Nombre duplicado',
                    'texto' => 'El nombre del laboratorio ya existe',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = '';
        if (isset($_FILES['imgLoad_laboratorio']) && $_FILES['imgLoad_laboratorio']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('laboratorio');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => 1
        ];

        $resultado = self::agregar_laboratorios_model($datos);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Registro exitoso',
                'texto' => 'El laboratorio se registró correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (!empty($ruta_imagen)) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo registrar el laboratorio',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function obtener_laboratorios_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['error' => 'ID inválido']);
            exit();
        }

        $stmt = self::obtener_laboratorios_model($id);
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dato) {
            echo json_encode($dato);
        } else {
            echo json_encode(['error' => 'No encontrado']);
        }
        exit();
    }

    public function actualizar_laboratorios_controller()
    {
        $id = intval($_POST['id_laboratorio_edit']);
        $nombre = mainModel::limpiar_cadena($_POST['nombre_laboratorio_edit']);

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (empty($nombre)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campo requerido',
                'texto' => 'El nombre es obligatorio',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (self::verificar_nombre_laboratorios_existe_model($nombre, $id)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Nombre duplicado',
                'texto' => 'El nombre ya está en uso por otro registro',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $stmt = self::obtener_laboratorios_model($id);
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$actual) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Registro no encontrado',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $ruta_imagen = $actual['la_logo'];
        $imagen_antigua = $actual['la_logo'];

        if (isset($_FILES['imgLoad_laboratorio_edit']) && $_FILES['imgLoad_laboratorio_edit']['error'] === UPLOAD_ERR_OK) {
            $ruta_imagen = $this->guardar_imagen_desde_archivo('laboratorio');
            if (empty($ruta_imagen)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error de imagen',
                    'texto' => 'No se pudo guardar la imagen',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'imagen' => $ruta_imagen,
            'estado' => $actual['la_estado']
        ];

        $resultado = self::actualizar_laboratorios_model($datos);

        if ($resultado->rowCount() >= 0) {
            if (!empty($imagen_antigua) && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($imagen_antigua);
            }
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Actualización exitosa',
                'texto' => 'El laboratorio se actualizó correctamente',
                'Tipo' => 'success'
            ];
        } else {
            if (isset($_FILES['imgLoad_laboratorio_edit']) && $_FILES['imgLoad_laboratorio_edit']['error'] === UPLOAD_ERR_OK && $ruta_imagen !== $imagen_antigua) {
                $this->eliminar_imagen($ruta_imagen);
            }
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo actualizar el laboratorio',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function cambiar_estado_laboratorios_controller()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $resultado = self::cambiar_estado_laboratorios_model($id, $estado);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Estado actualizado',
                'texto' => 'El estado se cambió correctamente',
                'Tipo' => 'success'
            ];
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo cambiar el estado',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }
}
