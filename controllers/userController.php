<?php
if ($peticionAjax) {
    require_once "../models/userModel.php";
} else {
    require_once "./models/userModel.php";
}

class userController extends userModel
{
    public function datos_extras_usuarios_controller()
    {
        $sql_su = mainModel::conectar()->prepare("SELECT * FROM sucursales WHERE su_estado = 1");
        $sql_su->execute();

        return [
            'sucursales' => $sql_su->fetchAll()
        ];
    }

    public function paginado_usuarios_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
        $id_usuario = $_SESSION['id_smp'] ?? 0;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                            <h3><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h3>
                            <p>No tiene permisos para ver usuarios</p>
                        </div>';
        }

        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1);
        $f2 = mainModel::limpiar_cadena($f2);
        $f3 = mainModel::limpiar_cadena($f3);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $filtros = [];

        if ($rol_usuario == 1) {
            if ($f1 !== '') {
                $filtros['sucursal'] = (int)$f1;
            }
        } elseif ($rol_usuario == 2) {
            $filtros['sucursal'] = $sucursal_usuario;
        }

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f2 !== '') {
            $roles_validos = ['2', '3'];
            if (in_array($f2, $roles_validos)) {
                $filtros['rol'] = (int)$f2;
            }
        }

        if ($f3 !== '') {
            $estados_validos = ['activo', 'inactivo'];
            if (in_array($f3, $estados_validos)) {
                $filtros['estado'] = $f3;
            }
        }

        $filtros['excluir_id'] = $id_usuario;

        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_usuarios_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_usuarios_model($filtros);
        } catch (PDOException $e) {
            error_log("ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $mostrar_columna_sucursal = ($rol_usuario == 1 && empty($f1));
        $colspan_total = $mostrar_columna_sucursal ? 11 : 10;

        $tabla .= '
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>NOMBRE COMPLETO</th>
                                <th>USUARIO</th>
                                <th>CARNET</th>
                                <th>TELÉFONO</th>
                                <th>CORREO</th>
                                <th>DIRECCIÓN</th>
                                <th>FECHA CREACIÓN</th>
                                <th>ROL</th>' .
            ($mostrar_columna_sucursal ? '<th>SUCURSAL</th>' : '') .
            '<th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
            ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $nombre_completo = trim(($row['us_nombres'] ?? '') . ' ' . ($row['us_apellido_paterno'] ?? '') . ' ' . ($row['us_apellido_materno'] ?? ''));

                $rol_nombre = $row['rol_nombre'] ?? 'N/A';
                $rol_color = $row['ro_id'] == 2 ? '#1976D2' : '#FF9800';

                $estado_html = $row['us_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';

                $tabla .= '
                        <tr>
                            <td>' . $contador . '</td>
                            <td><strong>' . htmlspecialchars($nombre_completo) . '</strong></td>
                            <td>' . htmlspecialchars($row['us_username']) . '</td>
                            <td>' . htmlspecialchars($row['us_numero_carnet'] ?: '-') . '</td>
                            <td>' . htmlspecialchars($row['us_telefono'] ?: '-') . '</td>
                            <td>' . htmlspecialchars($row['us_correo'] ?: '-') . '</td>
                            <td style="font-size:11px;">' . htmlspecialchars($row['us_direccion'] ?: '-') . '</td>
                            <td>' . date('d/m/Y', strtotime($row['us_creado_en'])) . '</td>
                            <td><span style="background:#E3F2FD;padding:4px 8px;border-radius:4px;font-weight:600;color:' . $rol_color . ';">' . $rol_nombre . '</span></td>' .
                    ($mostrar_columna_sucursal ? '<td><span style="background:#FFF3E0;padding:4px 8px;border-radius:4px;font-weight:600;color:#E65100;">' . htmlspecialchars($row['sucursal_nombre']) . '</span></td>' : '') .
                    '<td>' . $estado_html . '</td>
                            <td class="accion-buttons">
                                <a href="javascript:void(0)" 
                                class="btn default" 
                                title="Ver detalle"
                                onclick="UsuariosModals.verDetalle(' . $row['us_id'] . ')">
                                    <ion-icon name="eye-outline"></ion-icon> Detalle
                                </a>
                                <a href="javascript:void(0)" 
                                class="btn primary" 
                                title="Editar"
                                onclick="UsuariosModals.abrirModalEditar(' . $row['us_id'] . ')">
                                    <ion-icon name="create-outline"></ion-icon> Editar
                                </a>
                                <a href="javascript:void(0)" 
                                class="btn ' . ($row['us_estado'] == 1 ? 'danger' : 'success') . '" 
                                title="' . ($row['us_estado'] == 1 ? 'Desactivar' : 'Activar') . '"
                                onclick="UsuariosModals.toggleEstado(' . $row['us_id'] . ', ' . $row['us_estado'] . ')">
                                    <ion-icon name="' . ($row['us_estado'] == 1 ? 'close-circle-outline' : 'checkmark-circle-outline') . '"></ion-icon> 
                                    ' . ($row['us_estado'] == 1 ? 'Desactivar' : 'Activar') . '
                                </a>
                            </td>
                        </tr>
                    ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="' . $colspan_total . '" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="people-outline"></ion-icon> No hay registros
                        </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }

    public function exportar_usuarios_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 3) {
            echo "Acceso denegado";
            return;
        }

        try {
            $stmt = self::exportar_usuarios_excel_model($rol_usuario, $sucursal_usuario);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $filename = "Usuarios_{$fecha}.xls";

            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                </head>
                <body>';

            echo '<div style="text-align:center;padding:20px;background:#2c3e50;color:white;">
                        <h1>REPORTE DE USUARIOS - SAMFARM PHARMA</h1>
                        <p>Fecha de generación: ' . date('d/m/Y H:i:s') . '</p>
                        <p>Usuario: ' . ($_SESSION['nombre_smp'] ?? 'Sistema') . '</p>
                        <p>Total de registros: ' . count($datos) . '</p>
                    </div>';

            echo '<table border="1" style="width:100%;border-collapse:collapse;">';

            echo '<thead style="background:#34495e;color:white;"><tr>';
            $headers = array_keys($datos[0]);
            foreach ($headers as $header) {
                echo '<th style="padding:10px;">' . strtoupper(str_replace('_', ' ', $header)) . '</th>';
            }
            echo '</tr></thead>';

            echo '<tbody>';
            foreach ($datos as $row) {
                echo '<tr>';
                foreach ($headers as $key) {
                    $valor = $row[$key];
                    echo '<td style="padding:8px;">' . htmlspecialchars($valor ?? '-') . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';

            echo '</body></html>';

            exit();
        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . $e->getMessage();
        }
    }
    public function agregar_usuario_controller()
    {
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_reg'] ?? '');
        $apellido_paterno = mainModel::limpiar_cadena($_POST['ApellidoPaterno_reg'] ?? '');
        $apellido_materno = mainModel::limpiar_cadena($_POST['ApellidoMaterno_reg'] ?? '');
        $carnet = mainModel::limpiar_cadena($_POST['Carnet_reg'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_reg'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['Correo_reg'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_reg'] ?? '');
        $usuarioName = mainModel::limpiar_cadena($_POST['UsuarioName_reg'] ?? '');
        $password = mainModel::limpiar_cadena($_POST['Password_reg'] ?? '');
        $password_confirm = mainModel::limpiar_cadena($_POST['PasswordConfirm_reg'] ?? '');
        $sucursal = mainModel::limpiar_cadena($_POST['Sucursal_reg'] ?? '');
        $rol = mainModel::limpiar_cadena($_POST['Rol_reg'] ?? '');

        if (empty($nombres) || empty($apellido_paterno) || empty($apellido_materno) || empty($carnet) || empty($usuarioName) || empty($password) || empty($password_confirm) || empty($sucursal) || empty($rol)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Campos obligatorios",
                "texto" => "Debe completar todos los campos obligatorios",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if ($password != $password_confirm) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Contraseñas no coinciden",
                "texto" => "Las contraseñas ingresadas no coinciden",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if ($rol != 2 && $rol != 3) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Rol inválido",
                "texto" => "Solo puede crear usuarios con rol Gerente o Vendedor",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $check_carnet = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_numero_carnet = '$carnet'");
        if ($check_carnet->rowCount() > 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Carnet duplicado",
                "texto" => "Ya existe otro usuario con este número de carnet",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $check_usuario = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_username = '$usuarioName'");
        if ($check_usuario->rowCount() > 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Usuario duplicado",
                "texto" => "Ya existe otro usuario con este nombre de usuario",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (!empty($correo)) {
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Correo inválido",
                    "texto" => "El formato del correo electrónico no es válido",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }

            $check_correo = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_correo = '$correo'");
            if ($check_correo->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Correo duplicado",
                    "texto" => "Ya existe otro usuario con este correo electrónico",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $password_hash = mainModel::encryption($password);

        $datos_usuario = [
            'Nombres' => $nombres,
            'ApellidoPaterno' => $apellido_paterno,
            'ApellidoMaterno' => $apellido_materno,
            'Carnet' => $carnet,
            'Telefono' => $telefono,
            'Correo' => $correo,
            'Direccion' => $direccion,
            'UsuarioName' => $usuarioName,
            'Password' => $password_hash,
            'Sucursal' => $sucursal,
            'Rol' => $rol
        ];

        $agregar = userModel::agregar_usuario_modelo($datos_usuario);

        if ($agregar->rowCount() == 1) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Usuario registrado",
                "texto" => "El usuario fue registrado correctamente",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "texto" => "No se pudo registrar el usuario",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function editar_usuario_controller()
    {
        $us_id = mainModel::limpiar_cadena($_POST['us_id_editar'] ?? '');
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_edit'] ?? '');
        $apellido_paterno = mainModel::limpiar_cadena($_POST['ApellidoPaterno_edit'] ?? '');
        $apellido_materno = mainModel::limpiar_cadena($_POST['ApellidoMaterno_edit'] ?? '');
        $carnet = mainModel::limpiar_cadena($_POST['Carnet_edit'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_edit'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['Correo_edit'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_edit'] ?? '');
        $usuarioName = mainModel::limpiar_cadena($_POST['UsuarioName_edit'] ?? '');
        $password = mainModel::limpiar_cadena($_POST['Password_edit'] ?? '');
        $password_confirm = mainModel::limpiar_cadena($_POST['PasswordConfirm_edit'] ?? '');
        $sucursal = mainModel::limpiar_cadena($_POST['Sucursal_edit'] ?? '');
        $rol = mainModel::limpiar_cadena($_POST['Rol_edit'] ?? '');

        if (empty($us_id) || empty($nombres) || empty($apellido_paterno) || empty($apellido_materno) || empty($carnet) || empty($usuarioName) || empty($sucursal) || empty($rol)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Campos obligatorios",
                "texto" => "Debe completar todos los campos obligatorios",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $check_usuario = mainModel::ejecutar_consulta_simple("SELECT * FROM usuarios WHERE us_id = '$us_id'");
        if ($check_usuario->rowCount() <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Usuario no existe",
                "texto" => "El usuario no fue encontrado en el sistema",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $usuario_actual = $check_usuario->fetch();

        if ($carnet != $usuario_actual['us_numero_carnet']) {
            $check_carnet = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_numero_carnet = '$carnet'");
            if ($check_carnet->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Carnet duplicado",
                    "texto" => "Ya existe otro usuario con este número de carnet",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if ($usuarioName != $usuario_actual['us_username']) {
            $check_username = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_username = '$usuarioName'");
            if ($check_username->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Usuario duplicado",
                    "texto" => "Ya existe otro usuario con este nombre de usuario",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if (!empty($correo) && $correo != $usuario_actual['us_correo']) {
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Correo inválido",
                    "texto" => "El formato del correo electrónico no es válido",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }

            $check_correo = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_correo = '$correo'");
            if ($check_correo->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Correo duplicado",
                    "texto" => "Ya existe otro usuario con este correo electrónico",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $password_hash = $usuario_actual['us_password_hash'];

        if (!empty($password) && !empty($password_confirm)) {
            if ($password != $password_confirm) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Contraseñas no coinciden",
                    "texto" => "Las nuevas contraseñas no coinciden",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
            $password_hash = mainModel::encryption($password);
        }

        $datos_usuario = [
            'us_id' => $us_id,
            'Nombres' => $nombres,
            'ApellidoPaterno' => $apellido_paterno,
            'ApellidoMaterno' => $apellido_materno,
            'Carnet' => $carnet,
            'Telefono' => $telefono,
            'Correo' => $correo,
            'Direccion' => $direccion,
            'UsuarioName' => $usuarioName,
            'Password' => $password_hash,
            'Sucursal' => $sucursal,
            'Rol' => $rol
        ];

        $actualizar = userModel::editar_usuario_model($datos_usuario);

        if ($actualizar->rowCount() >= 0) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Usuario actualizado",
                "texto" => "Los datos del usuario fueron actualizados correctamente",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "texto" => "No se pudo actualizar el usuario",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function toggle_estado_usuario_controller()
    {
        $us_id = isset($_POST['us_id']) ? (int)$_POST['us_id'] : 0;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;

        if ($us_id <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "texto" => "ID de usuario inválido",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if ($us_id == 1) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Acción no permitida",
                "texto" => "No se puede cambiar el estado del usuario principal",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $check_usuario = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_id = '$us_id'");
        if ($check_usuario->rowCount() <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Usuario no existe",
                "texto" => "El usuario no fue encontrado en el sistema",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $nuevo_estado = $estado == 1 ? 0 : 1;
        $texto_estado = $nuevo_estado == 1 ? 'activado' : 'desactivado';

        $actualizar = userModel::toggle_estado_usuario_model($us_id, $nuevo_estado);

        if ($actualizar->rowCount() == 1) {
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Estado actualizado",
                "texto" => "El usuario fue " . $texto_estado . " correctamente",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "texto" => "No se pudo cambiar el estado del usuario",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function datos_usuario_controller($id = null)
    {
        if ($id === null) {
            $us_id = isset($_POST['us_id']) ? (int)$_POST['us_id'] : 0;
        } else {
            $id = mainModel::decryption($id);
            $us_id = (int)mainModel::limpiar_cadena($id);
        }

        if ($us_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = userModel::datos_usuario_model($us_id);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return json_encode(['error' => 'Usuario no encontrado']);
            }

            return json_encode($usuario, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en datos_usuario_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar datos']);
        }
    }

    public function detalle_completo_usuario_controller()
    {
        $us_id = isset($_POST['us_id']) ? (int)$_POST['us_id'] : 0;

        if ($us_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = userModel::detalle_completo_usuario_model($us_id);
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$detalle) {
                return json_encode(['error' => 'Usuario no encontrado']);
            }

            return json_encode($detalle, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_completo_usuario_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar detalle']);
        }
    }

    public function ultimas_ventas_usuario_controller()
    {
        $us_id = isset($_POST['us_id']) ? (int)$_POST['us_id'] : 0;

        if ($us_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = userModel::ultimas_ventas_usuario_model($us_id, 10);
            $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['ventas' => $ventas], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en ultimas_ventas_usuario_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar ventas']);
        }
    }
    public function ventas_mensuales_usuario_controller()
    {
        $us_id = isset($_POST['us_id']) ? (int)$_POST['us_id'] : 0;

        if ($us_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = userModel::ventas_mensuales_usuario_model($us_id);
            $ventas_mensuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['ventas_mensuales' => $ventas_mensuales], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en ventas_mensuales_usuario_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar estadísticas']);
        }
    }

    public function editar_perfil_controller()
    {
        $rol_actual = $_SESSION['rol_smp'] ?? 0;
        $usuario_sesion = $_SESSION['id_smp'] ?? 0;

        if ($rol_actual != 1) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Acceso denegado",
                "texto" => "Solo los administradores pueden editar el perfil",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $us_id_perfil = mainModel::decryption($_POST['us_id_perfil'] ?? '');
        $us_id_perfil = (int)mainModel::limpiar_cadena($us_id_perfil);

        if ($us_id_perfil != $usuario_sesion) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Acción no permitida",
                "texto" => "Solo puedes editar tu propio perfil",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $nombres = mainModel::limpiar_cadena($_POST['Nombres_perfil'] ?? '');
        $apellido_paterno = mainModel::limpiar_cadena($_POST['ApellidoPaterno_perfil'] ?? '');
        $apellido_materno = mainModel::limpiar_cadena($_POST['ApellidoMaterno_perfil'] ?? '');
        $carnet = mainModel::limpiar_cadena($_POST['Carnet_perfil'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_perfil'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['Correo_perfil'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_perfil'] ?? '');
        $usuarioName = mainModel::limpiar_cadena($_POST['UsuarioName_perfil'] ?? '');
        $password = mainModel::limpiar_cadena($_POST['Password_perfil'] ?? '');
        $password_confirm = mainModel::limpiar_cadena($_POST['PasswordConfirm_perfil'] ?? '');

        if (empty($nombres) || empty($apellido_paterno) || empty($apellido_materno) || empty($carnet) || empty($usuarioName)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Campos obligatorios",
                "texto" => "Debe completar todos los campos obligatorios",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $check_usuario = mainModel::ejecutar_consulta_simple("SELECT * FROM usuarios WHERE us_id = '$us_id_perfil'");
        if ($check_usuario->rowCount() <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Usuario no existe",
                "texto" => "El usuario no fue encontrado en el sistema",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $usuario_actual = $check_usuario->fetch();

        if ($carnet != $usuario_actual['us_numero_carnet']) {
            $check_carnet = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_numero_carnet = '$carnet'");
            if ($check_carnet->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Carnet duplicado",
                    "texto" => "Ya existe otro usuario con este número de carnet",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if ($usuarioName != $usuario_actual['us_username']) {
            $check_username = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_username = '$usuarioName'");
            if ($check_username->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Usuario duplicado",
                    "texto" => "Ya existe otro usuario con este nombre de usuario",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if (!empty($correo) && $correo != $usuario_actual['us_correo']) {
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Correo inválido",
                    "texto" => "El formato del correo electrónico no es válido",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }

            $check_correo = mainModel::ejecutar_consulta_simple("SELECT us_id FROM usuarios WHERE us_correo = '$correo'");
            if ($check_correo->rowCount() > 0) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Correo duplicado",
                    "texto" => "Ya existe otro usuario con este correo electrónico",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $password_hash = $usuario_actual['us_password_hash'];

        if (!empty($password) && !empty($password_confirm)) {
            if ($password != $password_confirm) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "Contraseñas no coinciden",
                    "texto" => "Las nuevas contraseñas no coinciden",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            }
            $password_hash = mainModel::encryption($password);
        }

        $datos_usuario = [
            'us_id' => $us_id_perfil,
            'Nombres' => $nombres,
            'ApellidoPaterno' => $apellido_paterno,
            'ApellidoMaterno' => $apellido_materno,
            'Carnet' => $carnet,
            'Telefono' => $telefono,
            'Correo' => $correo,
            'Direccion' => $direccion,
            'UsuarioName' => $usuarioName,
            'Password' => $password_hash,
            'Sucursal' => $usuario_actual['su_id'],
            'Rol' => $usuario_actual['ro_id']
        ];

        $actualizar = userModel::editar_usuario_model($datos_usuario);

        if ($actualizar->rowCount() >= 0) {
            $_SESSION['nombre_smp'] = $nombres . ' ' . $apellido_paterno;

            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Perfil actualizado",
                "texto" => "Los datos de tu perfil fueron actualizados correctamente",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "texto" => "No se pudo actualizar el perfil",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
        exit();
    }



    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
}
