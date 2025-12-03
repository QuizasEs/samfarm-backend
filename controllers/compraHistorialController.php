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
            '
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
                    '
                        <td style="text-align:center;">' . $lotes_badge . '</td>
                        <td>' . htmlspecialchars($row['co_numero_factura'] ?? 'N/A') . '</td>
                        <td style="text-align:right;font-weight:bold;color:#2e7d32;">Bs. ' . number_format($row['co_total'], 2) . '</td>
                        <td class="">
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
    public function exportar_compra_detalle_pdf_controller()
    {
        $co_id = isset($_GET['id']) ? (int)mainModel::limpiar_cadena($_GET['id']) : 0;

        if ($co_id <= 0) {
            echo "ID de compra no válido.";
            return;
        }

        try {
            $datos_compra = self::exportar_compra_detalle_pdf_model($co_id);

            if (empty($datos_compra)) {
                echo "No se encontraron datos para la compra seleccionada.";
                return;
            }

            $compra = $datos_compra['compra'];
            $detalles = $datos_compra['detalles'];

            $info_superior = [
                'N° Compra' => $compra['co_numero'],
                'Fecha' => date('d/m/Y', strtotime($compra['co_fecha'])),
                'Proveedor' => $compra['proveedor_nombre'],
                'Sucursal' => $compra['sucursal'],
                'Generado' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema'
            ];

            $headers = [
                ['text' => 'N°', 'width' => 8],
                ['text' => 'MEDICAMENTO', 'width' => 50],
                ['text' => 'CANTIDAD', 'width' => 15],
                ['text' => 'P. UNITARIO', 'width' => 20],
                ['text' => 'DESCUENTO', 'width' => 20],
                ['text' => 'SUBTOTAL', 'width' => 20],
                ['text' => 'LOTE', 'width' => 20],
                ['text' => 'VENCIMIENTO', 'width' => 20]
            ];

            $rows = [];
            $contador = 1;
            foreach ($detalles as $detalle) {
                $cells = [
                    ['text' => $contador, 'align' => 'C'],
                    ['text' => substr($detalle['med_nombre_quimico'], 0, 40), 'align' => 'L'],
                    ['text' => $detalle['dc_cantidad'], 'align' => 'C'],
                    ['text' => number_format($detalle['dc_precio_unitario'], 2), 'align' => 'R'],
                    ['text' => number_format($detalle['dc_descuento'], 2), 'align' => 'R'],
                    ['text' => number_format($detalle['dc_subtotal'], 2), 'align' => 'R'],
                    ['text' => $detalle['lm_numero_lote'] ?? 'N/A', 'align' => 'C'],
                    ['text' => $detalle['lm_fecha_vencimiento'] ? date('d/m/Y', strtotime($detalle['lm_fecha_vencimiento'])) : 'N/A', 'align' => 'C']
                ];
                $rows[] = ['cells' => $cells];
                $contador++;
            }

            $cells_total = array_fill(0, count($headers) - 1, ['text' => '', 'align' => 'C']);
            $cells_total[0] = ['text' => 'TOTAL', 'align' => 'R'];
            $cells_total[count($headers) - 1] = [
                'text' => number_format($compra['co_total'], 2),
                'align' => 'R',
                'color' => [255, 255, 255]
            ];

            $rows[] = [
                'cells' => $cells_total,
                'es_total' => true
            ];

            $resumen = [
                'Subtotal' => ['text' => 'Bs ' . number_format($compra['co_subtotal'], 2)],
                'Impuestos' => ['text' => 'Bs ' . number_format($compra['co_impuesto'], 2)],
                'Total General' => [
                    'text' => 'Bs ' . number_format($compra['co_total'], 2),
                    'color' => [46, 125, 50]
                ]
            ];

            $datos_pdf = [
                'titulo' => 'DETALLE DE COMPRA',
                'nombre_archivo' => 'Compra_' . $compra['co_numero'] . '_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => $info_superior,
                'tabla' => [
                    'headers' => $headers,
                    'rows' => $rows
                ],
                'resumen' => $resumen
            ];

            self::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error exportando PDF detalle compra: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
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
    public function generar_pdf_orden_compra_controlador()
    {
        $co_id = isset($_GET['id']) ? mainModel::limpiar_cadena($_GET['id']) : 0;

        if ($co_id <= 0) {
            echo "ID de compra no válido.";
            return;
        }

        try {
            $datos_compra = self::datos_orden_compra_modelo($co_id);

            if (empty($datos_compra)) {
                echo "No se encontraron datos para la orden de compra seleccionada.";
                return;
            }

            $compra = $datos_compra['compra'];
            $detalles = $datos_compra['detalles'];

            $periodo = date('d/m/Y', strtotime($compra['co_fecha']));

            $info_superior = [
                'N° Compra' => $compra['co_numero'],
                'Fecha' => $periodo,
                'Proveedor' => $compra['proveedor_nombre'],
                'NIT' => $compra['proveedor_nit'],
                'Generado' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema'
            ];

            $headers = [
                ['text' => 'Cant.', 'width' => 15],
                ['text' => 'Producto', 'width' => 85],
                ['text' => 'P. Unitario', 'width' => 30],
                ['text' => 'Descuento', 'width' => 30],
                ['text' => 'Subtotal', 'width' => 30]
            ];

            $rows = [];
            foreach ($detalles as $detalle) {
                $cells = [
                    ['text' => $detalle['dc_cantidad'], 'align' => 'C'],
                    ['text' => $detalle['med_nombre_quimico'], 'align' => 'L'],
                    ['text' => 'Bs. ' . number_format($detalle['dc_precio_unitario'], 2), 'align' => 'R'],
                    ['text' => 'Bs. ' . number_format($detalle['dc_descuento'], 2), 'align' => 'R'],
                    ['text' => 'Bs. ' . number_format($detalle['dc_subtotal'], 2), 'align' => 'R']
                ];
                $rows[] = ['cells' => $cells];
            }

            $resumen = [
                'Subtotal' => ['text' => 'Bs ' . number_format($compra['co_subtotal'], 2)],
                'Impuestos' => ['text' => 'Bs ' . number_format($compra['co_impuesto'], 2)],
                'Total' => [
                    'text' => 'Bs ' . number_format($compra['co_total'], 2),
                    'color' => [46, 125, 50]
                ]
            ];

            $datos_pdf = [
                'titulo' => 'ORDEN DE COMPRA',
                'nombre_archivo' => 'Orden_Compra_' . $compra['co_numero'] . '.pdf',
                'info_superior' => $info_superior,
                'tabla' => [
                    'headers' => $headers,
                    'rows' => $rows
                ],
                'resumen' => $resumen
            ];

            self::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error al generar PDF de orden de compra: " . $e->getMessage());
            echo "Error al generar el PDF: " . $e->getMessage();
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

            $headers = array_keys($datos[0]);

            $periodo = '';
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                $periodo = date('d/m/Y', strtotime($fecha_desde)) . ' al ' . date('d/m/Y', strtotime($fecha_hasta));
            } else {
                $periodo = 'Todo el historial';
            }

            $info_superior = [
                'Periodo' => $periodo,
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema',
                'Sucursal' => isset($filtros['su_id']) ? 'Sucursal ID ' . $filtros['su_id'] : 'Todas las Sucursales',
                'Total de Registros' => count($datos)
            ];

            mainModel::generar_excel_reporte([
                'titulo' => '📦 HISTORIAL DE COMPRAS - SAMFARM PHARMA',
                'datos' => $datos,
                'headers' => $headers,
                'nombre_archivo' => $filename,
                'formato_columnas' => [
                    'Items' => 'numero',
                    'Total Lotes' => 'numero',
                    'Lotes Activos' => 'numero',
                    'Lotes Pendientes' => 'numero',
                    'Subtotal (Bs)' => 'moneda',
                    'Impuestos (Bs)' => 'moneda',
                    'Total (Bs)' => 'moneda'
                ],
                'columnas_totales' => ['Total (Bs)'],
                'info_superior' => $info_superior
            ]);
        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . $e->getMessage();
        }
    }
}
