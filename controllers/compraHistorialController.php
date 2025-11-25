<?php

if ($peticionAjax) {
    require_once "../models/compraHistorialModel.php";
} else {
    require_once "./models/compraHistorialModel.php";
}

class compraHistorialController extends compraHistorialModel
{

    public function paginado_compras_historial_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "", $f5 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h3>
                        <p>No tiene permisos para ver esta sección</p>
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
        $f4 = mainModel::limpiar_cadena($f4);
        $f5 = mainModel::limpiar_cadena($f5);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $filtros = [];

        if ($rol_usuario == 1) {
            if ($f3 !== '') {
                $filtros['su_id'] = (int)$f3;
            }
        } elseif ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        }

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }
        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        if ($f1 !== '' && is_numeric($f1)) {
            $filtros['proveedor'] = (int)$f1;
        }

        if ($f2 !== '' && is_numeric($f2)) {
            $filtros['laboratorio'] = (int)$f2;
        }

        if ($f4 !== '' && is_numeric($f4)) {
            $filtros['usuario'] = (int)$f4;
        }

        if ($f5 !== '') {
            $estados_validos = ['pendientes', 'activos', 'completado'];
            if (in_array($f5, $estados_validos)) {
                $filtros['estado_lotes'] = $f5;
            }
        }

        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_compras_historial_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_compras_historial_model($filtros);
        } catch (PDOException $e) {
            error_log("ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $mostrar_columna_sucursal = ($rol_usuario == 1 && empty($f3));
        $colspan_total = $mostrar_columna_sucursal ? 11 : 10;

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>N° COMPRA</th>
                            <th>FECHA COMPRA</th>
                            <th>PROVEEDOR</th>
                            <th>LABORATORIO</th>' .
            ($mostrar_columna_sucursal ? '<th>SUCURSAL</th>' : '') .
            '<th>ITEMS</th>
                            <th>LOTES</th>
                            <th>FACTURA</th>
                            <th>TOTAL</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $proveedor_info = '<strong>' . htmlspecialchars($row['proveedor_nombre']) . '</strong>';
                if (!empty($row['proveedor_nit'])) {
                    $proveedor_info .= '<br><small style="color:#666;">NIT: ' . htmlspecialchars($row['proveedor_nit']) . '</small>';
                }

                $lotes_badge = '<span style="background:#E3F2FD;padding:4px 10px;border-radius:12px;font-weight:600;color:#1565C0;">' .
                    $row['total_lotes'] . '</span>';

                if ($row['lotes_pendientes'] > 0) {
                    $lotes_badge .= ' <span style="background:#FFF3E0;padding:4px 8px;border-radius:12px;font-weight:600;color:#E65100;">' .
                        '<ion-icon name="warning-outline"></ion-icon> ' . $row['lotes_pendientes'] . ' pendiente(s)</span>';
                }

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td><strong style="color:#1976D2;">' . htmlspecialchars($row['co_numero']) . '</strong></td>
                        <td>' . date('d/m/Y', strtotime($row['co_fecha'])) . '</td>
                        <td>' . $proveedor_info . '</td>
                        <td>' . htmlspecialchars($row['laboratorio'] ?? 'N/A') . '</td>' .
                    ($mostrar_columna_sucursal ? '<td><span style="background:#E3F2FD;padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">' . htmlspecialchars($row['sucursal']) . '</span></td>' : '') .
                    '<td style="text-align:center;"><strong>' . $row['total_items'] . '</strong></td>
                        <td style="text-align:center;">' . $lotes_badge . '</td>
                        <td>' . htmlspecialchars($row['co_numero_factura'] ?? 'N/A') . '</td>
                        <td style="text-align:right;font-weight:bold;color:#2e7d32;">Bs. ' . number_format($row['co_total'], 2) . '</td>
                        <td class="accion-buttons">
                            <a href="javascript:void(0)" 
                            class="btn default" 
                            title="Ver detalle"
                            onclick="ComprasHistorialModals.verDetalle(' . $row['co_id'] . ', \'' . addslashes($row['co_numero']) . '\')">
                                <ion-icon name="eye-outline"></ion-icon> Ver
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="' . $colspan_total . '" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="document-outline"></ion-icon> No hay registros
                    </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }

    public function detalle_compra_controller()
    {
        $co_id = isset($_POST['co_id']) ? (int)$_POST['co_id'] : 0;

        if ($co_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            $stmt = self::detalle_compra_completo_model($co_id);
            $compra = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$compra) {
                return json_encode(['error' => 'Compra no encontrada']);
            }

            $medicamentosStmt = self::detalle_medicamentos_compra_model($co_id);
            $medicamentos = $medicamentosStmt->fetchAll(PDO::FETCH_ASSOC);

            $medicamentosFormateados = array_map(function ($med) {
                $estado_html = '';
                switch ($med['lm_estado']) {
                    case 'en_espera':
                        $estado_html = '<span class="estado-badge espera"><ion-icon name="time-outline"></ion-icon> En Espera</span>';
                        break;
                    case 'activo':
                        $estado_html = '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>';
                        break;
                    case 'terminado':
                        $estado_html = '<span class="estado-badge terminado"><ion-icon name="archive-outline"></ion-icon> Terminado</span>';
                        break;
                    case 'caducado':
                        $estado_html = '<span class="estado-badge caducado"><ion-icon name="warning-outline"></ion-icon> Caducado</span>';
                        break;
                    default:
                        $estado_html = '<span class="estado-badge desconocido">N/A</span>';
                }

                return [
                    'nombre' => $med['med_nombre_quimico'],
                    'principio_activo' => $med['med_principio_activo'],
                    'cantidad' => $med['dc_cantidad'],
                    'precio_unitario' => $med['dc_precio_unitario'],
                    'descuento' => $med['dc_descuento'],
                    'subtotal' => $med['dc_subtotal'],
                    'numero_lote' => $med['lm_numero_lote'] ?? 'N/A',
                    'estado_lote' => $estado_html,
                    'fecha_vencimiento' => $med['lm_fecha_vencimiento'] ?? null
                ];
            }, $medicamentos);

            $lotesStmt = self::resumen_lotes_compra_model($co_id);
            $lotes = $lotesStmt->fetch(PDO::FETCH_ASSOC);

            $proveedor_completo = $compra['proveedor_nombre'];
            if (!empty($compra['proveedor_nit'])) {
                $proveedor_completo .= ' (NIT: ' . $compra['proveedor_nit'] . ')';
            }
            if (!empty($compra['proveedor_direccion'])) {
                $proveedor_completo .= ' - ' . $compra['proveedor_direccion'];
            }
            if (!empty($compra['proveedor_telefono'])) {
                $proveedor_completo .= ' - Tel: ' . $compra['proveedor_telefono'];
            }

            $response = [
                'numero_compra' => $compra['co_numero'],
                'fecha_compra' => date('d/m/Y', strtotime($compra['co_fecha'])),
                'numero_factura' => $compra['co_numero_factura'] ?? 'N/A',
                'fecha_factura' => $compra['co_fecha_factura'] ? date('d/m/Y', strtotime($compra['co_fecha_factura'])) : 'N/A',
                'proveedor' => $proveedor_completo,
                'laboratorio' => $compra['laboratorio'] ?? 'N/A',
                'sucursal' => $compra['sucursal'],
                'usuario' => $compra['usuario_nombre'],
                'subtotal' => $compra['co_subtotal'],
                'impuestos' => $compra['co_impuesto'],
                'total' => $compra['co_total'],
                'medicamentos' => $medicamentosFormateados,
                'total_lotes' => $lotes['total_lotes'] ?? 0,
                'lotes_activos' => $lotes['lotes_activos'] ?? 0,
                'lotes_espera' => $lotes['lotes_espera'] ?? 0
            ];

            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_compra_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar detalle']);
        }
    }

    public function datos_grafico_compras_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        } else {
            $filtros['fecha_desde'] = date('Y-m-01');
        }

        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        } else {
            $filtros['fecha_hasta'] = date('Y-m-d');
        }

        try {
            $stmt = self::datos_grafico_compras_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode($datos, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en datos_grafico_compras_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar datos del gráfico']);
        }
    }
    public function exportar_compras_pdf_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 3) {
            echo "No tiene permisos para exportar.";
            return;
        }

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif (isset($_GET['su_id']) && $_GET['su_id'] != '') {
            $filtros['su_id'] = (int)$_GET['su_id'];
        }

        $fecha_desde = isset($_GET['fecha_desde']) ? mainModel::limpiar_cadena($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? mainModel::limpiar_cadena($_GET['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }
        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        if (isset($_GET['select1']) && $_GET['select1'] != '') {
            $filtros['proveedor'] = (int)$_GET['select1'];
        }
        if (isset($_GET['select2']) && $_GET['select2'] != '') {
            $filtros['laboratorio'] = (int)$_GET['select2'];
        }

        try {
            $stmt = self::exportar_compras_pdf_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar en el periodo seleccionado.";
                return;
            }

            $periodo = '';
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                $periodo = date('d/m/Y', strtotime($fecha_desde)) . ' al ' . date('d/m/Y', strtotime($fecha_hasta));
            } else {
                $periodo = 'Todo el historial';
            }

            $info_superior = [
                'Periodo' => $periodo,
                'Total Registros' => count($datos),
                'Generado' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema'
            ];

            $headers = [
                ['text' => 'N°', 'width' => 10],
                ['text' => 'N° COMPRA', 'width' => 25],
                ['text' => 'FECHA', 'width' => 20],
                ['text' => 'PROVEEDOR', 'width' => 35],
                ['text' => 'LABORATORIO', 'width' => 30],
                ['text' => 'ITEMS', 'width' => 15],
                ['text' => 'LOTES', 'width' => 15],
                ['text' => 'N° FACTURA', 'width' => 25],
                ['text' => 'TOTAL (Bs)', 'width' => 25]
            ];

            if ($rol_usuario == 1 && !isset($filtros['su_id'])) {
                array_splice($headers, 5, 0, [['text' => 'SUCURSAL', 'width' => 25]]);
            }

            $rows = [];
            $total_general = 0;
            $contador = 1;

            foreach ($datos as $row) {
                $cells = [
                    ['text' => $contador, 'align' => 'C'],
                    ['text' => $row['co_numero'], 'align' => 'L'],
                    ['text' => $row['fecha_compra'], 'align' => 'C'],
                    ['text' => substr($row['proveedor'], 0, 25), 'align' => 'L'],
                    ['text' => substr($row['laboratorio'] ?? 'N/A', 0, 20), 'align' => 'L'],
                    ['text' => $row['items'], 'align' => 'C'],
                    ['text' => $row['lotes'], 'align' => 'C'],
                    ['text' => $row['co_numero_factura'] ?? 'N/A', 'align' => 'C'],
                    ['text' => number_format($row['co_total'], 2), 'align' => 'R']
                ];

                if ($rol_usuario == 1 && !isset($filtros['su_id'])) {
                    array_splice($cells, 5, 0, [['text' => substr($row['sucursal'], 0, 20), 'align' => 'C']]);
                }

                $rows[] = ['cells' => $cells];
                $total_general += $row['co_total'];
                $contador++;
            }

            $cells_total = array_fill(0, count($headers) - 1, ['text' => '', 'align' => 'C']);
            $cells_total[0] = ['text' => 'TOTAL GENERAL', 'align' => 'R'];
            $cells_total[count($headers) - 1] = [
                'text' => 'Bs ' . number_format($total_general, 2),
                'align' => 'R',
                'color' => [255, 255, 255]
            ];

            $rows[] = [
                'cells' => $cells_total,
                'es_total' => true
            ];

            $resumen = [
                'Total de Compras' => ['text' => count($datos)],
                'Monto Total' => [
                    'text' => 'Bs ' . number_format($total_general, 2),
                    'color' => [46, 125, 50]
                ]
            ];

            $datos_pdf = [
                'titulo' => 'HISTORIAL DE COMPRAS',
                'nombre_archivo' => 'Historial_Compras_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => $info_superior,
                'tabla' => [
                    'headers' => $headers,
                    'rows' => $rows
                ],
                'resumen' => $resumen
            ];

            self::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error exportando PDF compras: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    public function exportar_compras_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 3) {
            echo "No tiene permisos para exportar inventario.";
            return;
        }

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif (isset($_GET['su_id']) && $_GET['su_id'] != '') {
            $filtros['su_id'] = (int)$_GET['su_id'];
        }

        $fecha_desde = isset($_GET['fecha_desde']) ? mainModel::limpiar_cadena($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? mainModel::limpiar_cadena($_GET['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }
        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        if (isset($_GET['select1']) && $_GET['select1'] != '') {
            $filtros['proveedor'] = (int)$_GET['select1'];
        }
        if (isset($_GET['select2']) && $_GET['select2'] != '') {
            $filtros['laboratorio'] = (int)$_GET['select2'];
        }
        if (isset($_GET['select4']) && $_GET['select4'] != '') {
            $filtros['usuario'] = (int)$_GET['select4'];
        }

        try {
            $stmt = self::exportar_compras_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $sucursal_texto = isset($filtros['su_id']) ? 'Sucursal_' . $filtros['su_id'] : 'Todas_Sucursales';
            $filename = "Historial_Compras_{$sucursal_texto}_{$fecha}.xls";

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
                    max-width: 1400px;
                }
                
                .header {
                    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                    color: white;
                    font-size: 22pt;
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
                    color: white;
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
                
                .estado-pendiente {
                    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%) !important;
                    color: #ef6c00;
                    font-weight: 600;
                    text-align: center;
                    border-radius: 20px;
                    padding: 6px 12px;
                    margin: 2px;
                    border: 1px solid #ff9800;
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
                
                tbody tr:not(.total-row) {
                    border-left: 3px solid transparent;
                    transition: border-left 0.3s ease;
                }
                
                tbody tr:not(.total-row):hover {
                    border-left: 3px solid #3498db;
                }
                
                @media print {
                    body { background: white; }
                    .container { box-shadow: none; }
                }
            </style>
        </head>
        <body>';

            echo '<div class="container">
                    <div class="header">
                        📦 HISTORIAL DE COMPRAS - SAMFARM PHARMA
                    </div>';

            $periodo = '';
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                $periodo = date('d/m/Y', strtotime($fecha_desde)) . ' al ' . date('d/m/Y', strtotime($fecha_hasta));
            } else {
                $periodo = 'Todo el historial';
            }

            echo '<div class="info">
                        <div class="info-item">
                            <strong>📅 Periodo</strong>
                            ' . $periodo . '
                        </div>
                        <div class="info-item">
                            <strong>👤 Usuario</strong>
                            ' . ($_SESSION['nombre_smp'] ?? 'Sistema') . '
                        </div>
                        <div class="info-item">
                            <strong>🏢 Sucursal</strong>
                            ' . (isset($filtros['su_id']) ? 'Sucursal ID ' . $filtros['su_id'] : 'Todas las Sucursales') . '
                        </div>
                        <div class="info-item">
                            <strong>📋 Total de Registros</strong>
                            ' . count($datos) . '
                        </div>
                    </div>';

            echo '<table>';

            echo '<thead><tr>';
            $headers = array_keys($datos[0]);
            foreach ($headers as $header) {
                echo '<th>' . strtoupper(str_replace('_', ' ', $header)) . '</th>';
            }
            echo '</tr></thead>';

            echo '<tbody>';

            $total_general = 0;
            $total_items = 0;
            $total_lotes = 0;

            foreach ($datos as $row) {
                echo '<tr>';

                foreach ($headers as $key) {
                    $valor = $row[$key];

                    if ($key === 'Items' || $key === 'Total Lotes' || $key === 'Lotes Activos' || $key === 'Lotes Pendientes') {
                        echo '<td class="numero">' . number_format($valor, 0, ',', '.') . '</td>';
                        
                        if ($key === 'Items') $total_items += $valor;
                        if ($key === 'Total Lotes') $total_lotes += $valor;
                        
                    } elseif ($key === 'Subtotal (Bs)' || $key === 'Impuestos (Bs)' || $key === 'Total (Bs)') {
                        echo '<td class="moneda">Bs ' . number_format($valor, 2, ',', '.') . '</td>';
                        
                        if ($key === 'Total (Bs)') $total_general += $valor;
                        
                    } elseif ($key === 'Lotes Activos' && $valor > 0) {
                        echo '<td class="estado-activo">' . number_format($valor, 0) . ' Activo(s)</td>';
                    } elseif ($key === 'Lotes Pendientes' && $valor > 0) {
                        echo '<td class="estado-pendiente">' . number_format($valor, 0) . ' Pendiente(s)</td>';
                    } else {
                        echo '<td>' . htmlspecialchars($valor ?? '-') . '</td>';
                    }
                }

                echo '</tr>';
            }

            $num_cols = count($headers);
            echo '<tr class="total-row">
                    <td colspan="' . ($num_cols - 3) . '" style="text-align: right; padding-right: 20px;">📊 TOTALES GENERALES:</td>
                    <td class="numero">' . number_format($total_items, 0, ',', '.') . '</td>
                    <td class="numero">' . number_format($total_lotes, 0, ',', '.') . '</td>
                    <td class="moneda">Bs ' . number_format($total_general, 2, ',', '.') . '</td>
                </tr>';

            echo '</tbody></table>';

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
}
