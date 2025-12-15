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
                    ? '<span class="estado-badge activo">Activo</span>'
                    : '<span class="estado-badge caducado">Inactivo</span>';

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
                        <td class="buttons">
                                ' . ($rol_usuario != 3 ? '<a href="javascript:void(0)"
                                class="btn default"
                                title="Ver detalle"
                                onclick="ClientesModals.verDetalle(' . $row['cl_id'] . ')">
                                    Detalle
                                </a>' : '') . '
                                <a href="javascript:void(0)"
                                class="btn primary"
                                title="Editar"
                                onclick="ClientesModals.abrirModalEditar(' . $row['cl_id'] . ')">
                                    Editar
                                </a>
                                ' . ($rol_usuario != 3 ? '<a href="javascript:void(0)"
                                class="btn ' . ($row['cl_estado'] == 1 ? 'danger' : 'success') . '"
                                title="' . ($row['cl_estado'] == 1 ? 'Desactivar' : 'Activar') . '"
                                onclick="ClientesModals.toggleEstado(' . $row['cl_id'] . ', ' . $row['cl_estado'] . ')">
                                    ' . ($row['cl_estado'] == 1 ? 'Desactivar' : 'Activar') . '
                                </a>' : '') . '
                            </td>
                        </tr>
                    ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="10" style="text-align:center;padding:20px;color:#999;">
                            No hay registros
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

        // Recibir filtros de la URL
        $filtros = [];

        $busqueda = isset($_GET['busqueda']) ? mainModel::limpiar_cadena($_GET['busqueda']) : '';
        $select1 = isset($_GET['select1']) ? mainModel::limpiar_cadena($_GET['select1']) : '';
        $select2 = isset($_GET['select2']) ? mainModel::limpiar_cadena($_GET['select2']) : '';
        $select3 = isset($_GET['select3']) ? mainModel::limpiar_cadena($_GET['select3']) : '';
        $fecha_desde = isset($_GET['fecha_desde']) ? mainModel::limpiar_cadena($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? mainModel::limpiar_cadena($_GET['fecha_hasta']) : '';

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if (!empty($select1)) {
            $estados_validos = ['activo', 'inactivo'];
            if (in_array($select1, $estados_validos)) {
                $filtros['estado'] = $select1;
            }
        }

        if (!empty($select2)) {
            $compras_validas = ['con_compras', 'sin_compras'];
            if (in_array($select2, $compras_validas)) {
                $filtros['con_compras'] = $select2;
            }
        }

        if (!empty($select3)) {
            $ultima_compra_valida = ['7', '30', '90', 'mas_90', 'nunca'];
            if (in_array($select3, $ultima_compra_valida)) {
                $filtros['ultima_compra'] = $select3;
            }
        }

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        try {
            $stmt = self::exportar_clientes_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar con los filtros aplicados";
                return;
            }

            $fecha = date('Y-m-d_His');
            $filename = "Clientes_{$fecha}.xls";

            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            // Crear tabla HTML con estilos elegantes
            echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                /* Estilos generales */
                body {
                    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
                    font-size: 11pt;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0;
                    padding: 20px;
                }

                .container {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                    overflow: hidden;
                    margin: 0 auto;
                    max-width: 1200px;
                }

                /* Encabezado elegante */
                .header {
                    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                    color: black;
                    font-size: 20pt;
                    font-weight: 300;
                    text-align: center;
                    padding: 25px;
                    margin-bottom: 0;
                    letter-spacing: 1px;
                    position: relative;
                }

                .header::after {
                    content: "";
                    position: absolute;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    height: 4px;
                    background: linear-gradient(90deg, #e74c3c, #f39c12, #2ecc71, #3498db);
                }

                /* Panel de información */
                .info {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    padding: 20px;
                    border-bottom: 1px solid #dee2e6;
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 15px;
                    font-size: 10pt;
                }

                .info-item {
                    background: white;
                    padding: 12px;
                    border-radius: 8px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                    border-left: 4px solid #3498db;
                }

                .info-item strong {
                    color: #2c3e50;
                    display: block;
                    margin-bottom: 5px;
                    font-size: 9pt;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                /* Tabla moderna */
                table {
                    border-collapse: separate;
                    border-spacing: 0;
                    width: 100%;
                    font-size: 10pt;
                    background: white;
                }

                th {
                    background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
                    color: black;
                    font-weight: 500;
                    text-align: center;
                    padding: 14px 10px;
                    border: none;
                    position: relative;
                    font-size: 9pt;
                    letter-spacing: 0.5px;
                    text-transform: uppercase;
                }

                th::after {
                    content: "";
                    position: absolute;
                    right: 0;
                    top: 25%;
                    height: 50%;
                    width: 1px;
                    background: rgba(255,255,255,0.3);
                }

                th:last-child::after {
                    display: none;
                }

                td {
                    padding: 12px 10px;
                    border-bottom: 1px solid #f8f9fa;
                    text-align: left;
                    transition: all 0.2s ease;
                }

                tr:hover td {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }

                /* Estilos numéricos mejorados */
                .numero {
                    text-align: right;
                    font-weight: 600;
                    font-family: "Courier New", monospace;
                    color: #2c3e50;
                }

                .moneda {
                    text-align: right;
                    font-weight: 700;
                    font-family: "Courier New", monospace;
                    color: #27ae60;
                    background: linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%);
                    border-left: 3px solid #27ae60;
                }

                /* Estados con diseño moderno */
                .estado-activo {
                    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%) !important;
                    color: #2e7d32;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #4caf50;
                }

                .estado-inactivo {
                    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%) !important;
                    color: #c62828;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #ef5350;
                }

                /* Fila de totales premium */
                .total-row {
                    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
                    color: #001670;
                    font-weight: 600;
                    font-size: 11pt;
                }

                .total-row td {
                    border: none;
                    padding: 16px 10px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .total-row .numero, .total-row .moneda {
                    color: #001670;
                    background: none;
                    border-left: none;
                    font-size: 11pt;
                }

                /* Pie de página elegante */
                .footer {
                    margin-top: 0;
                    padding: 25px;
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border-top: 1px solid #dee2e6;
                    font-size: 9pt;
                    color: #6c757d;
                    text-align: center;
                }

                .footer strong {
                    color: #2c3e50;
                    display: block;
                    margin-bottom: 8px;
                    font-size: 10pt;
                }

                /* Efectos de separación */
                tbody tr:not(.total-row) {
                    border-left: 3px solid transparent;
                    transition: border-left 0.3s ease;
                }

                tbody tr:not(.total-row):hover {
                    border-left: 3px solid #3498db;
                }

                /* Responsive para Excel */
                @media print {
                    body { background: white; }
                    .container { box-shadow: none; }
                }
            </style>
        </head>
        <body>';

            // Encabezado elegante
            echo '<div class="container">
                    <div class="header">
                        👥 REPORTE DE CLIENTES - SAMFARM PHARMA
                    </div>';

            // Información del reporte en formato grid
            $filtros_aplicados = [];
            if (!empty($filtros['busqueda'])) $filtros_aplicados[] = "Búsqueda: '{$filtros['busqueda']}'";
            if (!empty($filtros['estado'])) $filtros_aplicados[] = "Estado: " . ucfirst($filtros['estado']);
            if (!empty($filtros['con_compras'])) $filtros_aplicados[] = "Compras: " . str_replace('_', ' ', $filtros['con_compras']);
            if (!empty($filtros['ultima_compra'])) $filtros_aplicados[] = "Última compra: " . str_replace('_', ' ', $filtros['ultima_compra']);
            if (!empty($filtros['fecha_desde'])) $filtros_aplicados[] = "Desde: " . date('d/m/Y', strtotime($filtros['fecha_desde']));
            if (!empty($filtros['fecha_hasta'])) $filtros_aplicados[] = "Hasta: " . date('d/m/Y', strtotime($filtros['fecha_hasta']));

            echo '<div class="info">
                        <div class="info-item">
                            <strong>📅 Fecha de Generación</strong>
                            ' . date('d/m/Y H:i:s') . '
                        </div>
                        <div class="info-item">
                            <strong>👤 Usuario</strong>
                            ' . ($_SESSION['nombre_smp'] ?? 'Sistema') . '
                        </div>
                        <div class="info-item">
                            <strong>📋 Total de Registros</strong>
                            ' . count($datos) . '
                        </div>
                        <div class="info-item">
                            <strong>🔍 Filtros Aplicados</strong>
                            ' . (count($filtros_aplicados) > 0 ? implode('<br>', $filtros_aplicados) : 'Sin filtros') . '
                        </div>
                    </div>';

            // Tabla de datos
            echo '<table>';

            // Encabezados
            echo '<thead><tr>';
            $headers = array_keys($datos[0]);
            foreach ($headers as $header) {
                echo '<th>' . strtoupper(str_replace('_', ' ', $header)) . '</th>';
            }
            echo '</tr></thead>';

            // Cuerpo de la tabla
            echo '<tbody>';

            $total_compras = 0;
            $total_monto = 0;

            foreach ($datos as $row) {
                echo '<tr>';

                foreach ($headers as $key) {
                    $valor = $row[$key];

                    // Aplicar formato según el campo
                    if ($key === 'Total Compras') {
                        echo '<td class="numero">' . number_format($valor, 0, ',', '.') . '</td>';
                        $total_compras += $valor;
                    } elseif ($key === 'Monto Total') {
                        echo '<td class="moneda">Bs ' . number_format(floatval(str_replace(',', '', $valor)), 2, ',', '.') . '</td>';
                        $total_monto += floatval(str_replace(',', '', $valor));
                    } elseif ($key === 'Estado') {
                        $clase = 'estado-' . strtolower($valor);
                        echo '<td class="' . $clase . '">' . $valor . '</td>';
                    } else {
                        echo '<td>' . htmlspecialchars($valor ?? '-') . '</td>';
                    }
                }

                echo '</tr>';
            }

            // Fila de totales elegante
            echo '<tr class="total-row">
                    <td colspan="8" style="text-align: right; padding-right: 20px;">📊 TOTALES GENERALES:</td>
                    <td class="numero">' . number_format($total_compras, 0, ',', '.') . '</td>
                    <td class="moneda">Bs ' . number_format($total_monto, 2, ',', '.') . '</td>
                    <td colspan="2"></td>
                </tr>';

            echo '</tbody></table>';

            // Pie de página elegante
            echo '<div class="footer">
                        <strong>SAMFARM PHARMA - Sistema de Gestión Farmacéutica Premium</strong>
                        Este reporte fue generado automáticamente el ' . date('d/m/Y \a \l\a\s H:i:s') . '. Para consultas contacte al administrador del sistema.
                    </div>
                </div>';

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

            $content = self::generar_pdf_reporte_fpdf($datos_pdf);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $datos_pdf['nombre_archivo'] . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $content;
            exit();
        } catch (Exception $e) {
            error_log("Error exportando PDF: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    public function exportar_pdf_detalle_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            echo "Acceso denegado";
            return;
        }

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

            $content = self::generar_pdf_reporte_fpdf($datos_pdf);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $datos_pdf['nombre_archivo'] . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $content;
            exit();
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
