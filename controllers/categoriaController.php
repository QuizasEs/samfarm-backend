<?php

if ($peticionAjax) {
    require_once "../models/categoriaModel.php";
} else {
    require_once "./models/categoriaModel.php";
}

class categoriaController extends categoriaModel
{
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

                $imagen_src = !empty($rows['uf_imagen']) ? $rows['uf_imagen'] : SERVER_URL . 'views/asset/img/predeterminado.png';

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>
                            <img src="' . $imagen_src . '" 
                                 alt="' . htmlspecialchars($rows['uf_nombre']) . '" 
                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                 onerror="this.src=\'' . SERVER_URL . 'views/asset/img/predeterminado.png\'">
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
        $imagen = '';

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

        if (!empty($_POST['imagen_uso_base64'])) {
            $imagen = $_POST['imagen_uso_base64'];
        }

        $datos = [
            'nombre' => $nombre,
            'imagen' => $imagen,
            'estado' => 1
        ];

        $resultado = self::agregar_uso_farmacologico_model($datos);

        if ($resultado->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Registro exitoso',
                'texto' => 'El uso farmacológico se registró correctamente',
                'Tipo' => 'success'
            ];
        } else {
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
        $id = isset($_POST['id_uso_edit']) ? (int)$_POST['id_uso_edit'] : 0;
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

        $imagen = $actual['uf_imagen'];

        if (!empty($_POST['imagen_uso_base64_edit'])) {
            $imagen = $_POST['imagen_uso_base64_edit'];
        }

        $datos = [
            'id' => $id,
            'nombre' => $nombre,
            'imagen' => $imagen,
            'estado' => $actual['uf_estado']
        ];

        $resultado = self::actualizar_uso_farmacologico_model($datos);

        if ($resultado->rowCount() >= 0) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Actualización exitosa',
                'texto' => 'El uso farmacológico se actualizó correctamente',
                'Tipo' => 'success'
            ];
        } else {
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
}
