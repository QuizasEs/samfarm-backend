<?php

if ($peticionAjax) {
    require_once '../models/ventasHistorialModel.php';
} else {
    require_once './models/ventasHistorialModel.php';
}
class ventasHistorialController extends ventasHistorialModel
{

    public function paginado_ventas_historial_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "", $f5 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3> Acceso Denegado</h3>
                        <p>No tiene permisos para ver el historial de ventas</p>
                    </div>';
        }

        // ===== LIMPIAR PARÁMETROS =====
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1); 
        $f2 = mainModel::limpiar_cadena($f2); 
        $f3 = mainModel::limpiar_cadena($f3); 
        $f4 = mainModel::limpiar_cadena($f4); 
        $f5 = mainModel::limpiar_cadena($f5); 

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        // ===== CONSTRUIR FILTROS =====
        $filtros = [];

        
        if ($rol_usuario == 1) {
            // Admin: puede ver todas o filtrar
            if ($f1 !== '') {
                $filtros['su_id'] = (int)$f1;
            }
        } elseif ($rol_usuario == 2) {
            // Gerente: solo su sucursal
            $filtros['su_id'] = $sucursal_usuario;
        }

        //  Filtros de fecha
        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        //  Filtro por cliente
        if ($f2 !== '' && is_numeric($f2)) {
            $filtros['cliente'] = (int)$f2;
        }

        //  Filtro por vendedor
        if ($f3 !== '' && is_numeric($f3)) {
            $filtros['vendedor'] = (int)$f3;
        }

        //  Filtro por tipo de documento
        if ($f4 !== '') {
            $filtros['tipo_documento'] = $f4;
        }

        // Búsqueda
        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        // ===== CONSULTAR DATOS =====
        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_ventas_historial_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_ventas_historial_model($filtros);
        } catch (PDOException $e) {
            error_log(" ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        // Determinar si mostrar columna sucursal
        $mostrar_columna_sucursal = ($rol_usuario == 1 && empty($f1));
        $colspan_total = $mostrar_columna_sucursal ? 10 : 9;

        // ===== CONSTRUIR TABLA =====
        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>N° DOCUMENTO</th>
                            <th>FECHA</th>
                            <th>CLIENTE</th>
                            <th>VENDEDOR</th>' .
            ($mostrar_columna_sucursal ? '<th>SUCURSAL</th>' : '') .
            '<th>ITEMS</th>
                            <th>TOTAL</th>
                            <th>TIPO DOC.</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                // Cliente
                $cliente_display = !empty($row['cliente_nombre'])
                    ? htmlspecialchars($row['cliente_nombre'])
                    : '<span style="color:#999;">Sin cliente</span>';

                // Tipo documento con badge
                $tipo_doc = !empty($row['ve_tipo_documento'])
                    ? strtoupper($row['ve_tipo_documento'])
                    : 'VENTA';

                $tipo_doc_html = '<span class="estado-badge activo" style="font-size:10px;">' . $tipo_doc . '</span>';

                // Tiene factura
                $tiene_factura = !empty($row['fa_id']);

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td><strong>' . htmlspecialchars($row['ve_numero_documento']) . '</strong></td>
                        <td>' . date('d/m/Y H:i', strtotime($row['ve_fecha_emision'])) . '</td>
                        <td>' . $cliente_display . '</td>
                        <td>' . htmlspecialchars($row['vendedor_nombre']) . '</td>' .
                    ($mostrar_columna_sucursal ? '<td><span style="background:#E3F2FD;padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">' . htmlspecialchars($row['sucursal_nombre']) . '</span></td>' : '') .
                    '<td style="text-align:center;"><span style="background:#FFF3E0;padding:4px 10px;border-radius:12px;font-weight:600;color:#E65100;">' . $row['cantidad_items'] . '</span></td>
                        <td style="text-align:right;font-size:14px;"><strong style="color:#2e7d32;">Bs. ' . number_format($row['ve_total'], 2) . '</strong></td>
                        <td style="text-align:center;">' . $tipo_doc_html . '</td>
                        <td class="accion-buttons">
                            <a href="javascript:void(0)" 
                               class="btn default" 
                               title="Ver detalle"
                               onclick="VentasHistorialModals.verDetalle(' . $row['ve_id'] . ', \'' . addslashes($row['ve_numero_documento']) . '\')">
                                <ion-icon name="eye-outline"></ion-icon> Detalle
                            </a>
                            <a href="javascript:void(0)" 
                               class="btn primary" 
                               title="Reimprimir nota de venta"
                               onclick="VentasHistorialModals.reimprimirNota(' . $row['ve_id'] . ')">
                                <ion-icon name="print-outline"></ion-icon> Reimprimir
                            </a>
                    </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="' . $colspan_total . '" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="receipt-outline"></ion-icon> No hay registros
                        </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }


    public function detalle_venta_controller()
    {
        $ve_id = isset($_POST['ve_id']) ? (int)$_POST['ve_id'] : 0;

        if ($ve_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Parámetros inválidos',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        try {
            // Obtener datos de la venta
            $stmt = self::detalle_venta_completo_model($ve_id);
            $venta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'Venta no encontrada',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            // Obtener items
            $itemsStmt = self::detalle_items_venta_model($ve_id);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear items
            $itemsFormateados = array_map(function ($item) {
                $nombre_medicamento = $item['med_nombre_quimico'];
                if (!empty($item['med_version_comercial'])) {
                    $nombre_medicamento .= ' - ' . $item['med_version_comercial'];
                }

                return [
                    'nombre' => $nombre_medicamento,
                    'presentacion' => $item['med_presentacion'] ?? '',
                    'lote' => $item['lm_numero_lote'] ?? 'N/A',
                    'cantidad' => $item['dv_cantidad'],
                    'precio_unitario' => $item['dv_precio_unitario'],
                    'descuento' => $item['dv_descuento'],
                    'subtotal' => $item['dv_subtotal']
                ];
            }, $items);

            // Cliente
            $cliente_nombre = trim(
                ($venta['cl_nombres'] ?? '') . ' ' .
                    ($venta['cl_apellido_paterno'] ?? '') . ' ' .
                    ($venta['cl_apellido_materno'] ?? '')
            );
            if (empty($cliente_nombre)) {
                $cliente_nombre = 'Sin cliente';
            }

            // Vendedor
            $vendedor_nombre = trim(
                ($venta['us_nombres'] ?? '') . ' ' .
                    ($venta['us_apellido_paterno'] ?? '') . ' ' .
                    ($venta['us_apellido_materno'] ?? '')
            );

            $response = [
                'venta' => [
                    've_numero_documento' => $venta['ve_numero_documento'],
                    've_fecha_emision' => date('d/m/Y H:i', strtotime($venta['ve_fecha_emision'])),
                    've_subtotal' => $venta['ve_subtotal'],
                    've_impuesto' => $venta['ve_impuesto'],
                    've_total' => $venta['ve_total'],
                    've_tipo_documento' => $venta['ve_tipo_documento'] ?? 'venta',
                    'cliente_nombre' => $cliente_nombre,
                    'cliente_carnet' => $venta['cl_carnet'] ?? 'S/N',
                    'vendedor_nombre' => $vendedor_nombre,
                    'sucursal_nombre' => $venta['su_nombre'],
                    'caja_nombre' => $venta['caja_nombre'] ?? 'N/A',
                    'fa_numero' => $venta['fa_numero'] ?? null
                ],
                'items' => $itemsFormateados
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Exception $e) {
            error_log("Error en detalle_venta_controller: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Error al cargar detalle de venta',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    public function generar_pdf_nota_controller()
    {
        
        $ve_id = isset($_POST['ve_id']) ? (int)$_POST['ve_id'] : 0;

        if ($ve_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Parámetros inválidos',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        $consulta = self::ejecutar_consulta_simple("
                SELECT fa_id 
                FROM factura 
                WHERE ve_id = $ve_id
                LIMIT 1
            ");
        if ($consulta->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'La venta no tiene factura registrada',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        // Extraer fa_id
        $data = $consulta->fetch(PDO::FETCH_ASSOC);
        $fa_id = (int)$data['fa_id'];



        try {
            require_once dirname(__DIR__).'./models/ventaModel.php';
            $ins_venta = new ventaModel();
            $pdf_base64 = $ins_venta->generar_pdf_factura_model($fa_id, 'nota_venta');

            if (!$pdf_base64) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'Nonononono se pudo generar el PDF',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            // Respuesta exitosa
            $response = [
                'success' => true,
                'pdf_base64' => $pdf_base64
            ];

            echo json_encode($response);
            exit();
        } catch (Exception $e) {
            error_log("❌ Error en generar_pdf_nota_controller: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Error al generar PDF: ' . $e->getMessage(),
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }


    public function exportar_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif (isset($_GET['su_id']) && $_GET['su_id'] !== '') {
            $filtros['su_id'] = (int)$_GET['su_id'];
        }

        if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = mainModel::limpiar_cadena($_GET['fecha_desde']);
        }

        if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = mainModel::limpiar_cadena($_GET['fecha_hasta']);
        }

        if (isset($_GET['cliente']) && is_numeric($_GET['cliente'])) {
            $filtros['cliente'] = (int)$_GET['cliente'];
        }

        if (isset($_GET['vendedor']) && is_numeric($_GET['vendedor'])) {
            $filtros['vendedor'] = (int)$_GET['vendedor'];
        }

        if (isset($_GET['tipo_documento']) && !empty($_GET['tipo_documento'])) {
            $filtros['tipo_documento'] = mainModel::limpiar_cadena($_GET['tipo_documento']);
        }

        try {
            $stmt = self::exportar_historial_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $sucursal_nombre = !empty($filtros['su_id']) ? 'Sucursal_' . $filtros['su_id'] : 'Todas_Sucursales';
            $filename = "Historial_Ventas_{$sucursal_nombre}_{$fecha}.xls";

            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { 
                    font-family: "Segoe UI", Arial, sans-serif; 
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
                    max-width: 1400px;
                }
                
                .header {
                    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                    color: white;
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
                }
                
                tr:hover td {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
                }
                
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
            </style>
        </head>
        <body>';

            echo '<div class="container">';
            echo '<div class="header"> HISTORIAL DE VENTAS - SAMFARM PHARMA</div>';

            echo '<div class="info">';
            echo '<div class="info-item">';
            echo '<strong> Fecha de Generación</strong>';
            echo date('d/m/Y H:i:s');
            echo '</div>';

            echo '<div class="info-item">';
            echo '<strong>👤 Usuario :</strong>';
            echo htmlspecialchars($_SESSION['nombre_smp'] ?? 'Sistema');
            echo '</div>';

            if (!empty($filtros['su_id'])) {
                echo '<div class="info-item">';
                echo '<strong> Sucursal</strong>';
                echo 'Sucursal ID ' . $filtros['su_id'];
                echo '</div>';
            } else {
                echo '<div class="info-item">';
                echo '<strong> Sucursal: </strong>';
                echo 'Todas las Sucursales';
                echo '</div>';
            }

            if (!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta'])) {
                echo '<div class="info-item">';
                echo '<strong> Rango de Fechas</strong>';
                if (!empty($filtros['fecha_desde'])) {
                    echo date('d/m/Y', strtotime($filtros['fecha_desde']));
                }
                if (!empty($filtros['fecha_hasta'])) {
                    echo ' - ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
                }
                echo '</div>';
            }

            echo '<div class="info-item">';
            echo '<strong>Total de Registros: </strong>';
            echo count($datos);
            echo '</div>';
            echo '</div>';

            echo '<table>';
            echo '<thead><tr>';
            foreach (array_keys($datos[0]) as $header) {
                echo '<th>' . strtoupper(str_replace('_', ' ', $header)) . '</th>';
            }
            echo '</tr></thead>';

            echo '<tbody>';

            $total_general = 0;

            foreach ($datos as $row) {
                echo '<tr>';
                foreach ($row as $key => $valor) {
                    if ($key === 'Total (Bs)') {
                        echo '<td class="moneda">Bs ' . number_format($valor, 2, ',', '.') . '</td>';
                        $total_general += $valor;
                    } elseif ($key === 'Subtotal (Bs)' || $key === 'Impuestos (Bs)') {
                        echo '<td class="moneda">Bs ' . number_format($valor, 2, ',', '.') . '</td>';
                    } elseif ($key === 'Items') {
                        echo '<td class="numero">' . number_format($valor, 0, ',', '.') . '</td>';
                    } else {
                        echo '<td>' . htmlspecialchars($valor ?? '-') . '</td>';
                    }
                }
                echo '</tr>';
            }

            echo '<tr class="total-row">';
            echo '<td colspan="8" style="text-align: right; padding-right: 20px;"> TOTAL GENERAL:</td>';
            echo '<td class="moneda">Bs ' . number_format($total_general, 2, ',', '.') . '</td>';
            echo '<td colspan="2"></td>';
            echo '</tr>';

            echo '</tbody></table>';

            echo '<div class="footer">';
            echo '<strong>SAMFARM PHARMA - Sistema de Gestión Farmacéutica Premium</strong>';
            echo 'Este reporte fue generado automáticamente el ' . date('d/m/Y \a \l\a\s H:i:s') . '. Para consultas contacte al administrador del sistema.';
            echo '</div>';
            echo '</div>';

            echo '</body></html>';

            exit();
        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . htmlspecialchars($e->getMessage());
        }
    }
}
