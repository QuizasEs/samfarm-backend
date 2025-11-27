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

    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
    /* -----------------------------------controlador para paginar usuarios------------------------------------------ */
}
