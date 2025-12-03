<?php

if ($peticionAjax) {
    require_once '../models/inventarioModel.php';
} else {
    require_once './models/inventarioModel.php';
}
class inventarioController extends inventarioModel
{

    public function paginado_inventario_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "")
    {
        /* ===== VALIDAR PERMISOS ===== */
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        // Vendedores NO pueden acceder
        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                            <h3>⛔ Acceso Denegado</h3>
                            <p>No tiene permisos para ver el inventario</p>
                        </div>';
        }

        /* ===== LIMPIAR PARÁMETROS ===== */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1); // Laboratorio
        $f2 = mainModel::limpiar_cadena($f2); // Estado
        $f3 = mainModel::limpiar_cadena($f3); // Sucursal
        $f4 = mainModel::limpiar_cadena($f4); // Forma

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        /* ===== CONSTRUIR FILTROS ===== */
        $filtros = [];

        // 🔒 Filtro por sucursal según rol
        if ($rol_usuario == 1) {
            // Admin: puede ver todas o filtrar
            if ($f3 !== '') {
                $filtros['su_id'] = (int)$f3;
            }
        } elseif ($rol_usuario == 2) {
            // Gerente: solo su sucursal
            $filtros['su_id'] = $sucursal_usuario;
        }

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f1 !== '' && is_numeric($f1)) {
            $filtros['laboratorio'] = (int)$f1;
        }

        if ($f2 !== '') {
            $estados_validos = ['agotado', 'bajo', 'normal', 'exceso', 'sin_definir'];
            if (in_array($f2, $estados_validos)) {
                $filtros['estado'] = $f2;
            }
        }

        if ($f4 !== '' && is_numeric($f4)) {
            $filtros['forma'] = (int)$f4;
        }

        /* ===== CONSULTAR DATOS ===== */
        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_inventario_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_inventarios_model($filtros);
        } catch (PDOException $e) {
            error_log("❌ ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        // Determinar si mostrar columna sucursal
        $mostrar_columna_sucursal = ($rol_usuario == 1 && empty($f3));
        $colspan_total = $mostrar_columna_sucursal ? 13 : 12;

        /* ===== CONSTRUIR TABLA ===== */
        $tabla .= '
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>MEDICAMENTO</th>
                                <th>LABORATORIO</th>' .
            ($mostrar_columna_sucursal ? '<th>SUCURSAL</th>' : '') .
            '<th>CAJAS</th>
                                <th>UNIDADES</th>
                                <th>MÍNIMO</th>
                                <th>MÁXIMO</th>
                                <th>VALORADO</th>
                                <th>ESTADO</th>
                                <th>LOTES</th>
                                <th>VENCE</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
            ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                // Badge de estado
                $estado_html = self::generar_badge_estado($row['estado_stock'], $row['inv_total_unidades'], $row['inv_minimo']);

                // Calcular días para vencer
                $dias_vencer = '';
                if ($row['fecha_vencimiento_proximo']) {
                    $fecha_venc = new DateTime($row['fecha_vencimiento_proximo']);
                    $hoy = new DateTime();
                    $diff = $hoy->diff($fecha_venc);
                    $dias = (int)$diff->format('%R%a');

                    if ($dias < 0) {
                        $dias_vencer = '<span style="color:red;font-weight:bold;">VENCIDO</span>';
                    } elseif ($dias <= 30) {
                        $dias_vencer = '<span style="color:orange;font-weight:bold;">' . $dias . ' días</span>';
                    } elseif ($dias <= 90) {
                        $dias_vencer = '<span style="color:#ff9800;">' . $dias . ' días</span>';
                    } else {
                        $dias_vencer = $dias . ' días';
                    }
                } else {
                    $dias_vencer = '<span style="color:#999;">N/A</span>';
                }

                $tabla .= '
                        <tr>
                            <td>' . $contador . '</td>
                            <td>
                                <strong>' . htmlspecialchars($row['med_nombre_quimico']) . '</strong><br>
                                <small style="color:#666;">' . htmlspecialchars($row['med_principio_activo']) . '</small>
                            </td>
                            <td>' . htmlspecialchars($row['laboratorio']) . '</td>' .
                    ($mostrar_columna_sucursal ? '<td><span style="background:#E3F2FD;padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">' . htmlspecialchars($row['sucursal_nombre']) . '</span></td>' : '') .
                    '<td style="text-align:center;"><strong>' . number_format($row['inv_total_cajas']) . '</strong></td>
                            <td style="text-align:center;font-size:16px;"><strong style="color:#1976D2;">' . number_format($row['inv_total_unidades']) . '</strong></td>
                            <td style="text-align:center;">' . number_format($row['inv_minimo'] ?? 0) . '</td>
                            <td style="text-align:center;">' . ($row['inv_maximo'] !== null ? number_format($row['inv_maximo']) : '<span style="color:#999;">Sin límite</span>') . '</td>
                            <td style="text-align:right;">Bs. ' . number_format($row['inv_total_valorado'], 2) . '</td>
                            <td>' . $estado_html . '</td>
                            <td style="text-align:center;">
                                <span style="background:#FFF3E0;padding:4px 10px;border-radius:12px;font-weight:600;color:#E65100;">' .
                    $row['lotes_activos'] . '</span>
                            </td>
                            <td style="text-align:center;">' . $dias_vencer . '</td>
                            <td class="accion-buttons">
                                <a href="javascript:void(0)" 
                                class="btn default" 
                                title="Ver detalle"
                                onclick="InventarioModals.verDetalle(' . $row['inv_id'] . ', ' . $row['med_id'] . ', ' . $row['su_id'] . ', \'' . addslashes($row['med_nombre_quimico']) . '\')">
                                    <ion-icon name="eye-outline"></ion-icon> Detalles
                                </a>
                                
                                <a href="javascript:void(0)" 
                                class="btn danger" 
                                title="Configurar minimo y maximo"
                                onclick="InventarioModals.abrirConfiguracion(' . $row['inv_id'] . ', ' . $row['med_id'] . ', ' . $row['su_id'] . ', \'' . addslashes($row['med_nombre_quimico']) . '\', ' . ($row['inv_minimo'] ?? 0) . ', ' . ($row['inv_maximo'] ?? 'null') . ')">
                                    <ion-icon name="settings-outline"></ion-icon> Configurar
                                </a>
                                <a href="javascript:void(0)" 
                                class="btn info" 
                                title="Ver historial"
                                onclick="InventarioModals.verHistorial(' . $row['med_id'] . ', ' . $row['su_id'] . ', \'' . addslashes($row['med_nombre_quimico']) . '\')">
                                    <ion-icon name="time-outline"></ion-icon> Historial
                                </a>
                            </td>
                        </tr>
                    ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="' . $colspan_total . '" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="cube-outline"></ion-icon> No hay registros
                        </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }


    private static function generar_badge_estado($estado, $unidades, $minimo)
    {
        if ($unidades == 0) {
            return '<span class="estado-badge agotado"><ion-icon name="close-circle-outline"></ion-icon> AGOTADO</span>';
        }

        if ($minimo == 0 || $minimo === null) {
            return '<span class="estado-badge desconocido"><ion-icon name="help-circle-outline"></ion-icon> SIN DEFINIR</span>';
        }

        if ($unidades < $minimo) {
            return '<span class="estado-badge caducado"><ion-icon name="alert-circle-outline"></ion-icon> CRÍTICO</span>';
        }

        if ($unidades < ($minimo * 1.5)) {
            return '<span class="estado-badge espera"><ion-icon name="warning-outline"></ion-icon> BAJO</span>';
        }

        return '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> NORMAL</span>';
    }

    public function detalle_inventario_controller()
    {
        $inv_id = isset($_POST['inv_id']) ? (int)$_POST['inv_id'] : 0;
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($inv_id <= 0 || $med_id <= 0 || $su_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            // Obtener datos del inventario
            $stmt = self::detalle_inventario_con_lotes_model($inv_id);
            $inv = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$inv) {
                return json_encode(['error' => 'Inventario no encontrado']);
            }

            // Obtener lotes activos
            $lotesStmt = self::lotes_por_inventario_model($med_id, $su_id);
            $lotes = $lotesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear lotes
            $lotesFormateados = array_map(function ($lote) {
                return [
                    'numero_lote' => $lote['lm_numero_lote'],
                    'unidades' => $lote['lm_cant_actual_unidades'],
                    'precio' => $lote['lm_precio_venta'],
                    'vencimiento' => $lote['lm_fecha_vencimiento'],
                    'estado' => ucfirst($lote['lm_estado']),
                    'dias_vencer' => $lote['dias_para_vencer']
                ];
            }, $lotes);

            // Generar badge de estado
            $estado_html = self::generar_badge_estado(
                $inv['inv_total_unidades'] == 0 ? 'agotado' : 'normal',
                $inv['inv_total_unidades'],
                $inv['inv_minimo']
            );

            $response = [
                'laboratorio' => $inv['laboratorio'],
                'sucursal' => $inv['sucursal_nombre'],
                'cajas' => $inv['inv_total_cajas'],
                'unidades' => $inv['inv_total_unidades'],
                'valorado' => $inv['inv_total_valorado'],
                'estado_html' => $estado_html,
                'lotes' => $lotesFormateados
            ];

            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_inventario_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar detalle']);
        }
    }


    public function lotes_transferibles_controller()
    {
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($med_id <= 0 || $su_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            $stmt = self::lotes_por_inventario_model($med_id, $su_id);
            $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Filtrar solo lotes activos con stock
            $lotesDisponibles = array_filter($lotes, function ($lote) {
                return $lote['lm_estado'] === 'activo' && $lote['lm_cant_actual_unidades'] > 0;
            });

            $lotesFormateados = array_map(function ($lote) {
                return [
                    'lm_id' => $lote['lm_id'],
                    'numero_lote' => $lote['lm_numero_lote'],
                    'stock' => $lote['lm_cant_actual_unidades']
                ];
            }, array_values($lotesDisponibles));

            return json_encode(['lotes' => $lotesFormateados], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en lotes_transferibles_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar lotes']);
        }
    }


    public function historial_movimientos_controller()
    {
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($med_id <= 0 || $su_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            $stmt = self::historial_movimientos_inventario_model($med_id, $su_id, 30);
            $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $movimientosFormateados = array_map(function ($mov) {
                return [
                    'fecha' => date('d/m/Y H:i', strtotime($mov['mi_creado_en'])),
                    'tipo' => $mov['mi_tipo'],
                    'cantidad' => $mov['mi_cantidad'],
                    'unidad' => $mov['mi_unidad'],
                    'lote' => $mov['lm_numero_lote'],
                    'usuario' => trim(($mov['us_nombres'] ?? '') . ' ' . ($mov['us_apellido_paterno'] ?? '')),
                    'motivo' => $mov['mi_motivo']
                ];
            }, $movimientos);

            return json_encode(['movimientos' => $movimientosFormateados], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en historial_movimientos_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar historial']);
        }
    }


    public function exportar_inventario_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        // 🔒 Filtro por sucursal según rol
        if ($rol_usuario == 1) {
            // Admin: puede ver todas o filtrar
            $f3 = isset($_GET['select3']) ? $_GET['select3'] : '';
            if ($f3 !== '') {
                $filtros['su_id'] = (int)$f3;
            }
        } elseif ($rol_usuario == 2) {
            // Gerente: solo su sucursal
            $filtros['su_id'] = $sucursal_usuario;
        }

        // Demás filtros
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
        $f1 = isset($_GET['select1']) ? $_GET['select1'] : '';
        $f2 = isset($_GET['select2']) ? $_GET['select2'] : '';
        $f4 = isset($_GET['select4']) ? $_GET['select4'] : '';

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f1 !== '' && is_numeric($f1)) {
            $filtros['laboratorio'] = (int)$f1;
        }

        if ($f2 !== '') {
            $estados_validos = ['agotado', 'bajo', 'normal', 'exceso', 'sin_definir', 'critico'];
            if (in_array($f2, $estados_validos)) {
                $filtros['estado'] = $f2;
            }
        }

        if ($f4 !== '' && is_numeric($f4)) {
            $filtros['forma'] = (int)$f4;
        }

        try {
            // Obtener datos con filtros
            $stmt = self::exportar_inventario_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            // Nombre del archivo
            $fecha = date('Y-m-d_His');
            $sucursal_nombre = $su_id ? 'Sucursal_' . $su_id : 'Todas_Sucursales';
            $filename = "Inventario_{$sucursal_nombre}_{$fecha}.xls";

            // Headers para forzar descarga
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
                .estado-agotado {
                    background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%) !important;
                    color: #c62828;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #ef5350;
                }
                
                .estado-critico {
                    background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%) !important;
                    color: #ad1457;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #ec407a;
                }
                
                .estado-bajo {
                    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%) !important;
                    color: #ef6c00;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #ff9800;
                }
                
                .estado-normal {
                    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%) !important;
                    color: #2e7d32;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #4caf50;
                }
                
                .estado-exceso {
                    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
                    color: #1565c0;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #2196f3;
                }
                
                .estado-sin-definir {
                    background: linear-gradient(135deg, #f5f5f5 0%, #eeeeee 100%) !important;
                    color: #757575;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #bdbdbd;
                }
                
                /* Fila de totales premium */
                .total-row {
                    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
                    color: white;
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
                    color: white;
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
                        💊 REPORTE DE INVENTARIO - SAMFARM PHARMA
                    </div>';

            // Información del reporte en formato grid
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
                            <strong>🏪 Sucursal</strong>
                            ' . ($su_id ? 'Sucursal ID ' . $su_id : 'Todas las Sucursales') . '
                        </div>
                        <div class="info-item">
                            <strong>📋 Total de Registros</strong>
                            ' . count($datos) . '
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

            $total_cajas = 0;
            $total_unidades = 0;
            $total_valorado = 0;

            foreach ($datos as $row) {
                echo '<tr>';

                foreach ($headers as $key) {
                    $valor = $row[$key];

                    // Aplicar formato según el campo
                    if ($key === 'Cajas' || $key === 'Unidades') {
                        echo '<td class="numero">' . number_format($valor, 0, ',', '.') . '</td>';

                        if ($key === 'Cajas') $total_cajas += $valor;
                        if ($key === 'Unidades') $total_unidades += $valor;
                    } elseif ($key === 'Valorado (Bs)') {
                        echo '<td class="moneda">Bs ' . number_format($valor, 2, ',', '.') . '</td>';
                        $total_valorado += $valor;
                    } elseif ($key === 'Estado') {
                        $clase = 'estado-' . strtolower(str_replace(' ', '-', $valor));
                        $iconos_map = [
                            'AGOTADO' => '❌',
                            'CRÍTICO' => '🔴',
                            'BAJO' => '🟡',
                            'NORMAL' => '✅',
                            'EXCESO' => '📦',
                            'SIN DEFINIR' => '❓'
                        ];
                        $icono = isset($iconos_map[$valor]) ? $iconos_map[$valor] : '';
                        echo '<td class="' . $clase . '">' . $icono . ' ' . $valor . '</td>';
                    } elseif ($key === 'Última Actualización') {
                        echo '<td>' . date('d/m/Y H:i', strtotime($valor)) . '</td>';
                    } else {
                        echo '<td>' . htmlspecialchars($valor ?? '-') . '</td>';
                    }
                }

                echo '</tr>';
            }

            // Fila de totales elegante
            echo '<tr class="total-row">
                    <td colspan="5" style="text-align: right; padding-right: 20px;">📊 TOTALES GENERALES:</td>
                    <td class="numero">' . number_format($total_cajas, 0, ',', '.') . '</td>
                    <td class="numero">' . number_format($total_unidades, 0, ',', '.') . '</td>
                    <td class="moneda">Bs ' . number_format($total_valorado, 2, ',', '.') . '</td>
                    <td colspan="4"></td>
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

    public function guardar_configuracion_inventario_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1 && $rol_usuario != 2) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Acceso denegado',
                'texto' => 'No tiene permisos para configurar inventario',
                'Tipo' => 'error'
            ]);
        }

        $inv_id = isset($_POST['inv_id']) ? (int)$_POST['inv_id'] : 0;
        $inv_minimo = isset($_POST['inv_minimo']) ? (int)$_POST['inv_minimo'] : 0;
        $inv_maximo = isset($_POST['inv_maximo']) && $_POST['inv_maximo'] !== '' ? (int)$_POST['inv_maximo'] : null;

        if ($inv_id <= 0) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Inventario no válido',
                'Tipo' => 'error'
            ]);
        }

        if ($inv_minimo < 0) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Validación',
                'texto' => 'La cantidad mínima no puede ser negativa',
                'Tipo' => 'error'
            ]);
        }

        if ($inv_maximo !== null && $inv_maximo < $inv_minimo) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Validación',
                'texto' => 'La cantidad máxima debe ser mayor o igual a la mínima',
                'Tipo' => 'error'
            ]);
        }

        try {
            $resultado = self::actualizar_configuracion_inventario_model($inv_id, $inv_minimo, $inv_maximo);

            if ($resultado) {
                return json_encode([
                    'Alerta' => 'recargar',
                    'Titulo' => 'Configuración actualizada',
                    'texto' => 'Los valores de mínimo y máximo se guardaron correctamente',
                    'Tipo' => 'success'
                ]);
            } else {
                return json_encode([
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo guardar la configuración',
                    'Tipo' => 'error'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en guardar_configuracion_inventario_controller: " . $e->getMessage());
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Error al guardar configuración',
                'Tipo' => 'error'
            ]);
        }
    }
}
