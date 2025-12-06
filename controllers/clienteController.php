<?php

if ($peticionAjax) {
    require_once "../models/clienteModel.php";
} else {
    require_once "./models/clienteModel.php";
}

class clienteController extends clienteModel
{


    public function paginado_clientes_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                            <h3><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h3>
                            <p>No tiene permisos para ver clientes</p>
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

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f1 !== '') {
            $estados_validos = ['activo', 'inactivo'];
            if (in_array($f1, $estados_validos)) {
                $filtros['estado'] = $f1;
            }
        }

        if ($f2 !== '') {
            $compras_validas = ['con_compras', 'sin_compras'];
            if (in_array($f2, $compras_validas)) {
                $filtros['con_compras'] = $f2;
            }
        }

        if ($f3 !== '') {
            $ultima_compra_valida = ['7', '30', '90', 'mas_90', 'nunca'];
            if (in_array($f3, $ultima_compra_valida)) {
                $filtros['ultima_compra'] = $f3;
            }
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        $fecha_desde_valida = !empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde);
        $fecha_hasta_valida = !empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta);

        if ($fecha_desde_valida && $fecha_hasta_valida) {
            $timestamp_desde = strtotime($fecha_desde);
            $timestamp_hasta = strtotime($fecha_hasta);

            if ($timestamp_desde <= $timestamp_hasta) {
                $filtros['fecha_desde'] = $fecha_desde;
                $filtros['fecha_hasta'] = $fecha_hasta;
            } else {
                $filtros['fecha_desde'] = $fecha_hasta;
                $filtros['fecha_hasta'] = $fecha_desde;
            }
        } elseif ($fecha_desde_valida) {
            $filtros['fecha_desde'] = $fecha_desde;
        } elseif ($fecha_hasta_valida) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_clientes_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_clientes_model($filtros);
        } catch (PDOException $e) {
            error_log("ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $tabla .= '
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>CLIENTE</th>
                                <th>CI/CARNET</th>
                                <th>TELÉFONO</th>
                                <th>CORREO</th>
                                <th>FECHA REGISTRO</th>
                                <th>ÚLTIMA COMPRA</th>
                                <th>TOTAL COMPRAS</th>
                                <th>ESTADO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
            ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $nombre_completo = trim(($row['cl_nombres'] ?? '') . ' ' . ($row['cl_apellido_paterno'] ?? '') . ' ' . ($row['cl_apellido_materno'] ?? ''));
                $carnet = $row['cl_carnet'] ?: 'Sin CI';
                $telefono = $row['cl_telefono'] ?: '-';
                $correo = $row['cl_correo'] ?: '-';
                $fecha_registro = date('d/m/Y', strtotime($row['cl_creado_en']));

                $ultima_compra = 'Nunca';
                if ($row['ultima_compra']) {
                    $ultima_compra = date('d/m/Y', strtotime($row['ultima_compra']));
                }

                $total_compras = (int)($row['total_compras'] ?? 0);

                $estado_html = $row['cl_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';

                $tabla .= '
                        <tr>
                            <td>' . $contador . '</td>
                            <td><strong>' . htmlspecialchars($nombre_completo) . '</strong></td>
                            <td>' . htmlspecialchars($carnet) . '</td>
                            <td>' . htmlspecialchars($telefono) . '</td>
                            <td>' . htmlspecialchars($correo) . '</td>
                            <td>' . $fecha_registro . '</td>
                            <td>' . $ultima_compra . '</td>
                            <td style="text-align:center;"><strong style="color:#1976D2;">' . $total_compras . '</strong></td>
                            <td>' . $estado_html . '</td>
                            <td class="accion-buttons">
                                <a href="javascript:void(0)" 
                                class="btn default" 
                                title="Ver detalle"
                                onclick="ClientesModals.verDetalle(' . $row['cl_id'] . ')">
                                    <ion-icon name="eye-outline"></ion-icon> Detalle
                                </a>
                                <a href="javascript:void(0)" 
                                class="btn primary" 
                                title="Editar"
                                onclick="ClientesModals.abrirModalEditar(' . $row['cl_id'] . ')">
                                    <ion-icon name="create-outline"></ion-icon> Editar
                                </a>
                                <a href="javascript:void(0)" 
                                class="btn ' . ($row['cl_estado'] == 1 ? 'danger' : 'success') . '" 
                                title="' . ($row['cl_estado'] == 1 ? 'Desactivar' : 'Activar') . '"
                                onclick="ClientesModals.toggleEstado(' . $row['cl_id'] . ', ' . $row['cl_estado'] . ')">
                                    <ion-icon name="' . ($row['cl_estado'] == 1 ? 'close-circle-outline' : 'checkmark-circle-outline') . '"></ion-icon> 
                                    ' . ($row['cl_estado'] == 1 ? 'Desactivar' : 'Activar') . '
                                </a>
                            </td>
                        </tr>
                    ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="10" style="text-align:center;padding:20px;color:#999;">
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

    public function exportar_clientes_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            echo "Acceso denegado";
            return;
        }

        try {
            $stmt = self::exportar_clientes_excel_model();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $filename = "Clientes_{$fecha}.xls";

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
                        <h1>REPORTE DE CLIENTES - SAMFARM PHARMA</h1>
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

    /* funcionalidades de registro edicion */
    public function agregar_cliente_controller()
    {
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_cl'] ?? '');
        $paterno = mainModel::limpiar_cadena($_POST['Paterno_cl'] ?? '');
        $materno = mainModel::limpiar_cadena($_POST['Materno_cl'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_cl'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['Correo_cl'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_cl'] ?? '');
        $carnet = mainModel::limpiar_cadena($_POST['Carnet_cl'] ?? '');

        /* verificamos que os campos obligaptorios no vengan vacios */
        if (empty($nombres) || empty($paterno) || empty($carnet)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos obligatorios',
                'texto' => 'Debe completar los campos obligatorios',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* validar integridad de datos */
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
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $paterno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El APELLIDO PATERNO no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (!empty($materno)) {
            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $materno)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El APELLIDO MATERNO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }
        if (!empty($telefono)) {
            if (mainModel::verificar_datos("[0-9]{6,20}", $telefono)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El TELEFONO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }
        if (mainModel::verificar_datos("[0-9]{6,20}", $carnet)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El CARNET no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };

        if (!empty($carnet)) {
            $check_carnet = mainModel::ejecutar_consulta_simple("SELECT cl_id FROM clientes WHERE cl_carnet = '$carnet'");
            if ($check_carnet->rowCount() > 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'CI duplicado',
                    'texto' => 'Ya existe otro cliente con este número de carnet',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos_cliente = [
            'cl_nombres' => $nombres,
            'cl_apellido_paterno' => $paterno,
            'cl_apellido_materno' => $materno,
            'cl_telefono' => $telefono,
            'cl_correo' => $correo,
            'cl_direccion' => $direccion,
            'cl_carnet' => $carnet
        ];

        $agregar = clienteModel::agregar_cliente_model($datos_cliente);

        if ($agregar->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Cliente registrado',
                'texto' => 'El cliente fue registrado correctamente',
                'Tipo' => 'success'
            ];
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo registrar el cliente',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function editar_cliente_controller()
    {
        $cl_id = mainModel::limpiar_cadena($_POST['cl_id_editar'] ?? '');
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_cl'] ?? '');
        $paterno = mainModel::limpiar_cadena($_POST['Paterno_cl'] ?? '');
        $materno = mainModel::limpiar_cadena($_POST['Materno_cl'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_cl'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['Correo_cl'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_cl'] ?? '');
        $carnet = mainModel::limpiar_cadena($_POST['Carnet_cl'] ?? '');

        /* verificamos que los campos obligatorios no esten vacios */
        if (empty($cl_id) || empty($nombres) || empty($paterno) || empty($carnet)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos obligatorios',
                'texto' => 'Debe completar los campos obligatorios',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* verificamos la integridad de los datos */
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
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $paterno)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El APELLIDO PATERNO no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (!empty($materno)) {
            if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $materno)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El APELLIDO MATERNO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }
        if (!empty($carnet)) {
            if (mainModel::verificar_datos("[0-9]{6,20}", $carnet)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El CARNET no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }
        if (!empty($telefono)) {
            if (mainModel::verificar_datos("[0-9]{6,20}", $telefono)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El TELEFONO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }

        $check_cliente = mainModel::ejecutar_consulta_simple("SELECT cl_id FROM clientes WHERE cl_id = '$cl_id'");
        if ($check_cliente->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Cliente no existe',
                'texto' => 'El cliente no fue encontrado en el sistema',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (!empty($carnet)) {
            $check_carnet = mainModel::ejecutar_consulta_simple("SELECT cl_id FROM clientes WHERE cl_carnet = '$carnet' AND cl_id != '$cl_id'");
            if ($check_carnet->rowCount() > 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'CI duplicado',
                    'texto' => 'Ya existe otro cliente con este número de carnet',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        $datos_cliente = [
            'cl_id' => $cl_id,
            'cl_nombres' => $nombres,
            'cl_apellido_paterno' => $paterno,
            'cl_apellido_materno' => $materno,
            'cl_telefono' => $telefono,
            'cl_correo' => $correo,
            'cl_direccion' => $direccion,
            'cl_carnet' => $carnet
        ];

        $actualizar = clienteModel::editar_cliente_model($datos_cliente);

        if ($actualizar->rowCount() >= 0) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Cliente actualizado',
                'texto' => 'Los datos del cliente fueron actualizados correctamente',
                'Tipo' => 'success'
            ];
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo actualizar el cliente',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function toggle_estado_cliente_controller()
    {
        $cl_id = isset($_POST['cl_id']) ? (int)$_POST['cl_id'] : 0;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 0;

        if ($cl_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID de cliente inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $check_cliente = mainModel::ejecutar_consulta_simple("SELECT cl_id FROM clientes WHERE cl_id = '$cl_id'");
        if ($check_cliente->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Cliente no existe',
                'texto' => 'El cliente no fue encontrado en el sistema',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $nuevo_estado = $estado == 1 ? 0 : 1;
        $texto_estado = $nuevo_estado == 1 ? 'activado' : 'desactivado';

        $actualizar = clienteModel::toggle_estado_cliente_model($cl_id, $nuevo_estado);

        if ($actualizar->rowCount() == 1) {
            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Estado actualizado',
                'texto' => 'El cliente fue ' . $texto_estado . ' correctamente',
                'Tipo' => 'success'
            ];
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo cambiar el estado del cliente',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function datos_cliente_controller()
    {
        $cl_id = isset($_POST['cl_id']) ? (int)$_POST['cl_id'] : 0;

        if ($cl_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = self::datos_cliente_model($cl_id);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cliente) {
                return json_encode(['error' => 'Cliente no encontrado']);
            }

            return json_encode($cliente, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en datos_cliente_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar datos']);
        }
    }

    public function detalle_completo_cliente_controller()
    {
        $cl_id = isset($_POST['cl_id']) ? (int)$_POST['cl_id'] : 0;

        if ($cl_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = clienteModel::detalle_completo_cliente_model($cl_id);
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$detalle) {
                return json_encode(['error' => 'Cliente no encontrado']);
            }

            $detalle['antiguedad_dias'] = $this->calcularAntiguedad($detalle['cl_creado_en']);
            $detalle['promedio_compra'] = $detalle['total_compras'] > 0
                ? round($detalle['monto_total'] / $detalle['total_compras'], 2)
                : 0;

            return json_encode($detalle, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_completo_cliente_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar detalle']);
        }
    }

    public function ultimas_compras_cliente_controller()
    {
        $cl_id = isset($_POST['cl_id']) ? (int)$_POST['cl_id'] : 0;

        if ($cl_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = clienteModel::ultimas_compras_cliente_model($cl_id, 5);
            $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['compras' => $compras], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en ultimas_compras_cliente_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar compras']);
        }
    }

    public function medicamentos_mas_comprados_controller()
    {
        $cl_id = isset($_POST['cl_id']) ? (int)$_POST['cl_id'] : 0;

        if ($cl_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = clienteModel::medicamentos_mas_comprados_model($cl_id, 5);
            $medicamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['medicamentos' => $medicamentos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en medicamentos_mas_comprados_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar medicamentos']);
        }
    }

    public function grafico_compras_mensuales_controller()
    {
        $cl_id = isset($_POST['cl_id']) ? (int)$_POST['cl_id'] : 0;

        if ($cl_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = clienteModel::grafico_compras_mensuales_model($cl_id);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['datos' => $datos], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en grafico_compras_mensuales_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar gráfico']);
        }
    }

    private function calcularAntiguedad($fecha_registro)
    {
        $fecha_inicio = new DateTime($fecha_registro);
        $fecha_actual = new DateTime();
        $diferencia = $fecha_inicio->diff($fecha_actual);

        if ($diferencia->y > 0) {
            return $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '') . ' y ' . $diferencia->m . ' mes' . ($diferencia->m != 1 ? 'es' : '');
        } elseif ($diferencia->m > 0) {
            return $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '') . ' y ' . $diferencia->d . ' día' . ($diferencia->d != 1 ? 's' : '');
        } else {
            return $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
        }
    }

    public function exportar_pdf_cliente_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            echo "Acceso denegado";
            return;
        }

        try {
            $stmt = self::exportar_clientes_pdf_model();
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $datos_pdf = [
                'titulo' => 'REPORTE GENERAL DE CLIENTES',
                'nombre_archivo' => 'Clientes_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => [
                    'Fecha Generación' => date('d/m/Y H:i:s'),
                    'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema',
                    'Total Clientes' => count($datos),
                    'Clientes Activos' => count(array_filter($datos, function ($d) {
                        return $d['Estado'] == 'ACTIVO';
                    }))
                ],
                'tabla' => [
                    'headers' => [
                        ['text' => 'N°', 'width' => 10],
                        ['text' => 'CLIENTE', 'width' => 45],
                        ['text' => 'CI', 'width' => 20],
                        ['text' => 'TELÉFONO', 'width' => 20],
                        ['text' => 'COMPRAS', 'width' => 18],
                        ['text' => 'MONTO TOTAL', 'width' => 25],
                        ['text' => 'ÚLTIMA COMPRA', 'width' => 25],
                        ['text' => 'ESTADO', 'width' => 17]
                    ],
                    'rows' => []
                ],
                'resumen' => [
                    'Total de Clientes' => ['text' => count($datos)],
                    'Clientes Activos' => ['text' => count(array_filter($datos, function ($d) {
                        return $d['Estado'] == 'ACTIVO';
                    })), 'color' => [46, 125, 50]],
                    'Clientes Inactivos' => ['text' => count(array_filter($datos, function ($d) {
                        return $d['Estado'] == 'INACTIVO';
                    })), 'color' => [198, 40, 40]],
                    'Total Compras Registradas' => ['text' => array_sum(array_column($datos, 'Total Compras'))],
                    'Monto Total Acumulado' => ['text' => 'Bs. ' . number_format(array_sum(array_map(function ($d) {
                        return floatval(str_replace(['Bs. ', ','], '', $d['Monto Total']));
                    }, $datos)), 2), 'color' => [13, 71, 161]]
                ]
            ];

            $contador = 1;
            foreach ($datos as $row) {
                $nombre_completo = trim($row['Nombres'] . ' ' . $row['Apellido Paterno'] . ' ' . ($row['Apellido Materno'] ?: ''));

                $color_estado = $row['Estado'] == 'ACTIVO' ? [46, 125, 50] : [198, 40, 40];

                $datos_pdf['tabla']['rows'][] = [
                    'cells' => [
                        ['text' => $contador, 'align' => 'C'],
                        ['text' => $nombre_completo, 'align' => 'L'],
                        ['text' => $row['CI'] ?: 'Sin CI', 'align' => 'C'],
                        ['text' => $row['Teléfono'] ?: '-', 'align' => 'C'],
                        ['text' => $row['Total Compras'], 'align' => 'C'],
                        ['text' => $row['Monto Total'], 'align' => 'R'],
                        ['text' => $row['Última Compra'], 'align' => 'C'],
                        ['text' => $row['Estado'], 'align' => 'C', 'color' => $color_estado]
                    ]
                ];
                $contador++;
            }

            self::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error exportando PDF: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    public function exportar_pdf_detalle_controller()
    {
        $cl_id = isset($_GET['cl_id']) ? (int)$_GET['cl_id'] : 0;

        if ($cl_id <= 0) {
            echo "ID de cliente inválido";
            return;
        }

        try {
            $detalleStmt = self::detalle_completo_cliente_model($cl_id);
            $detalle = $detalleStmt->fetch(PDO::FETCH_ASSOC);

            if (!$detalle) {
                echo "Cliente no encontrado";
                return;
            }

            $nombre_completo = trim($detalle['cl_nombres'] . ' ' . $detalle['cl_apellido_paterno'] . ' ' . ($detalle['cl_apellido_materno'] ?: ''));

            $comprasStmt = self::historial_completo_model($cl_id);
            $compras = $comprasStmt->fetchAll(PDO::FETCH_ASSOC);

            $promedio = $detalle['total_compras'] > 0
                ? round($detalle['monto_total'] / $detalle['total_compras'], 2)
                : 0;

            $datos_pdf = [
                'titulo' => 'DETALLE DEL CLIENTE',
                'nombre_archivo' => 'Cliente_' . $cl_id . '_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => [
                    'Cliente' => $nombre_completo,
                    'CI/Carnet' => $detalle['cl_carnet'] ?: 'Sin CI',
                    'Teléfono' => $detalle['cl_telefono'] ?: '-',
                    'Correo' => $detalle['cl_correo'] ?: '-',
                    'Dirección' => $detalle['cl_direccion'] ?: '-',
                    'Fecha Registro' => date('d/m/Y', strtotime($detalle['cl_creado_en'])),
                    'Estado' => $detalle['cl_estado'] == 1 ? 'ACTIVO' : 'INACTIVO'
                ],
                'tabla' => [
                    'headers' => [
                        ['text' => 'N°', 'width' => 8],
                        ['text' => 'DOCUMENTO', 'width' => 30],
                        ['text' => 'FECHA', 'width' => 20],
                        ['text' => 'MEDICAMENTOS', 'width' => 50],
                        ['text' => 'UND', 'width' => 12],
                        ['text' => 'TOTAL', 'width' => 20],
                        ['text' => 'VENDEDOR', 'width' => 25],
                        ['text' => 'SUCURSAL', 'width' => 25]
                    ],
                    'rows' => []
                ],
                'resumen' => [
                    'Total de Compras Realizadas' => ['text' => $detalle['total_compras']],
                    'Monto Total Gastado' => ['text' => 'Bs. ' . number_format($detalle['monto_total'], 2), 'color' => [13, 71, 161]],
                    'Facturas Emitidas' => ['text' => $detalle['facturas_emitidas']],
                    'Promedio por Compra' => ['text' => 'Bs. ' . number_format($promedio, 2), 'color' => [123, 31, 162]],
                    'Última Compra' => ['text' => $detalle['ultima_compra'] ? date('d/m/Y', strtotime($detalle['ultima_compra'])) : 'Nunca']
                ]
            ];

            if (!empty($compras)) {
                $contador = 1;
                foreach ($compras as $compra) {
                    $vendedor = trim(($compra['vendedor_nombre'] ?: '') . ' ' . ($compra['vendedor_apellido'] ?: '')) ?: 'N/A';
                    $medicamentos = $compra['medicamentos_detalle'] ?: '-';
                    if (strlen($medicamentos) > 60) {
                        $medicamentos = substr($medicamentos, 0, 57) . '...';
                    }

                    $datos_pdf['tabla']['rows'][] = [
                        'cells' => [
                            ['text' => $contador, 'align' => 'C'],
                            ['text' => $compra['ve_numero_documento'], 'align' => 'L'],
                            ['text' => date('d/m/Y', strtotime($compra['ve_fecha_emision'])), 'align' => 'C'],
                            ['text' => $medicamentos, 'align' => 'L'],
                            ['text' => $compra['total_unidades'] ?: '0', 'align' => 'C'],
                            ['text' => 'Bs. ' . number_format($compra['ve_total'], 2), 'align' => 'R'],
                            ['text' => $vendedor, 'align' => 'L'],
                            ['text' => $compra['sucursal_nombre'] ?: '-', 'align' => 'L']
                        ]
                    ];
                    $contador++;
                }

                $total_unidades = array_sum(array_column($compras, 'total_unidades'));

                $datos_pdf['tabla']['rows'][] = [
                    'es_total' => true,
                    'cells' => [
                        ['text' => '', 'align' => 'C'],
                        ['text' => '', 'align' => 'L'],
                        ['text' => '', 'align' => 'C'],
                        ['text' => 'TOTALES:', 'align' => 'R'],
                        ['text' => $total_unidades, 'align' => 'C'],
                        ['text' => 'Bs. ' . number_format(array_sum(array_column($compras, 've_total')), 2), 'align' => 'R'],
                        ['text' => '', 'align' => 'L'],
                        ['text' => count($compras) . ' compras', 'align' => 'L']
                    ]
                ];
            }

            self::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error exportando PDF detalle: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    public function historial_completo_controller()
    {
        $cl_id = isset($_POST['cl_id']) ? (int)$_POST['cl_id'] : 0;

        if ($cl_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = clienteModel::historial_completo_model($cl_id);
            $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode(['compras' => $compras], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en historial_completo_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar historial']);
        }
    }
}
