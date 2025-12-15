<?php

if ($peticionAjax) {
    require_once '../models/transferirModel.php';
} else {
    require_once './models/transferirModel.php';
}

class transferirHistorialController extends transferirModel
{
    public function paginado_historial_transferencias_controller(
        $pagina,
        $registros,
        $su_origen = '',
        $su_destino = '',
        $us_emisor = '',
        $estado = '',
        $fecha_desde = '',
        $fecha_hasta = '',
        $busqueda = ''
    ) {
        $rol = $_SESSION['rol_smp'] ?? 1;
        $su_usuario = $_SESSION['sucursal_smp'] ?? 0;

        try {
            $stmt = transferirModel::listar_historial_transferencias_model(
                $pagina,
                $registros,
                $su_origen,
                $su_destino,
                $us_emisor,
                $estado,
                $fecha_desde,
                $fecha_hasta,
                $busqueda,
                $rol,
                $su_usuario
            );

            $transferencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $total = transferirModel::contar_historial_transferencias_model(
                $su_origen,
                $su_destino,
                $us_emisor,
                $estado,
                $fecha_desde,
                $fecha_hasta,
                $busqueda,
                $rol,
                $su_usuario
            );

            $Npaginas = ceil($total / $registros);

            $html = '<div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:15%">N° Transferencia</th>
                                <th style="width:20%">Origen → Destino</th>
                                <th style="width:15%">Usuario</th>
                                <th style="width:12%">Fecha</th>
                                <th style="width:10%">Estado</th>
                                <th style="width:15%">Total (Bs.)</th>
                                <th style="width:13%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>';

            if ($pagina <= $Npaginas && $total >= 1) {
                $reg_inicio = ($pagina - 1) * $registros + 1;
                $contador = $reg_inicio;

                foreach ($transferencias as $tr) {
                    $estado_badge = $this->obtener_badge_estado($tr['tr_estado']);
                    $fecha = date('d/m/Y H:i', strtotime($tr['tr_fecha_envio']));
                    $monto = number_format($tr['tr_total_valorado'], 2, '.', ',');

                    $url_pdf = SERVER_URL . 'ajax/transferirHistorialAjax.php?transferirHistorialAjax=generar_pdf&tr_id=' . $tr['tr_id'];

                    $html .= '<tr>
                                <td><strong>' . htmlspecialchars($tr['tr_numero']) . '</strong></td>
                                <td>' . htmlspecialchars($tr['sucursal_origen']) . ' → ' . htmlspecialchars($tr['sucursal_destino']) . '</td>
                                <td>' . htmlspecialchars($tr['usuario_emisor']) . '</td>
                                <td>' . $fecha . '</td>
                                <td>' . $estado_badge . '</td>
                                <td style="text-align:right;"><strong>Bs. ' . $monto . '</strong></td>
                                <td class="buttons">
                                    <a href="#" class="btn primary" onclick="event.preventDefault(); TransferirHistorialModals.verDetalle(' . (int)$tr['tr_id'] . ', \'' . htmlspecialchars($tr['tr_numero']) . '\')" title="Ver detalles">
                                        <ion-icon name="eye-outline"></ion-icon> Detalles
                                    </a>
                                    <a href="javascript:void(0)" class="btn default" title="Descargar PDF" onclick="window.open(\'' . $url_pdf . '\', \'_blank\')">
                                        <ion-icon name="download-outline"></ion-icon> PDF
                                    </a>
                                </td>
                            </tr>';
                    $contador++;
                }
                $reg_final = $contador - 1;
            } else {
                $html .= '<tr>
                            <td colspan="7" style="text-align:center;padding:20px;">
                                <ion-icon name="information-circle-outline"></ion-icon> Sin transferencias
                            </td>
                        </tr>';
            }

            $html .= '</tbody>
                    </table>
                </div>';

            if ($pagina <= $Npaginas && $total >= 1) {
                $html .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
                $url = 'transferirHistorial/';
                $html .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
            }

            return $html;
        } catch (Exception $e) {
            error_log("Error en paginado_historial_transferencias: " . $e->getMessage());
            return '<div class="error">Error al cargar el historial</div>';
        }
    }

    public function obtener_detalles_transferencia_controller()
    {
        $tr_id = isset($_POST['tr_id']) ? (int)$_POST['tr_id'] : 0;
        $rol = $_SESSION['rol_smp'] ?? 1;
        $su_usuario = $_SESSION['sucursal_smp'] ?? 0;

        if (!$tr_id) {
            return json_encode(['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'ID inválido', 'Tipo' => 'error']);
        }

        try {
            $stmt_tr = transferirModel::datos_transferencia_completa_model($tr_id);
            $transferencia = $stmt_tr->fetch(PDO::FETCH_ASSOC);

            if (!$transferencia) {
                return json_encode(['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'Transferencia no encontrada', 'Tipo' => 'error']);
            }

            if ($rol != 1 && $transferencia['su_origen_id'] != $su_usuario && $transferencia['su_destino_id'] != $su_usuario) {
                return json_encode(['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'No tiene permiso', 'Tipo' => 'error']);
            }

            $stmt_dt = transferirModel::detalle_transferencia_model($tr_id);
            $detalles = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'transferencia' => $transferencia,
                'detalles' => $detalles
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_detalles_transferencia: " . $e->getMessage());
            return json_encode(['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'Error al obtener detalles', 'Tipo' => 'error']);
        }
    }

    public function generar_pdf_transferencia_controller()
    {
        $tr_id = isset($_GET['tr_id']) ? (int)$_GET['tr_id'] : 0;
        $rol = $_SESSION['rol_smp'] ?? 1;
        $su_usuario = $_SESSION['sucursal_smp'] ?? 0;

        if (!$tr_id) {
            echo "ID inválido";
            return;
        }

        try {
            $stmt_tr = transferirModel::datos_transferencia_completa_model($tr_id);
            $transferencia = $stmt_tr->fetch(PDO::FETCH_ASSOC);

            if (!$transferencia) {
                echo "Transferencia no encontrada";
                return;
            }

            if ($rol != 1 && $transferencia['su_origen_id'] != $su_usuario && $transferencia['su_destino_id'] != $su_usuario) {
                echo "No tiene permiso";
                return;
            }

            $stmt_dt = transferirModel::detalle_transferencia_model($tr_id);
            $detalles = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($detalles)) {
                echo "La transferencia no tiene detalles";
                return;
            }

            // Información superior
            $info_superior = [
                'Número' => $transferencia['tr_numero'],
                'Fecha Envío' => date('d/m/Y H:i', strtotime($transferencia['tr_fecha_envio'])),
                'Origen' => $transferencia['sucursal_origen'],
                'Destino' => $transferencia['sucursal_destino'],
                'Emisor' => $transferencia['usuario_emisor'],
                'Estado' => ucfirst(str_replace(['pendiente', 'aceptada', 'rechazada'], ['Pendiente', 'Aceptada', 'Rechazada'], $transferencia['tr_estado']))
            ];

            if ($transferencia['tr_fecha_respuesta']) {
                $info_superior['Fecha Recepción'] = date('d/m/Y H:i', strtotime($transferencia['tr_fecha_respuesta']));
            }

            if (!empty($transferencia['usuario_receptor'])) {
                $info_superior['Receptor'] = $transferencia['usuario_receptor'];
            }

            // Headers
            $headers = [
                ['text' => '#', 'width' => 8],
                ['text' => 'Medicamento', 'width' => 55],
                ['text' => 'Lote', 'width' => 25],
                ['text' => 'Cajas', 'width' => 18],
                ['text' => 'Unidades', 'width' => 25],
                ['text' => 'Subtotal', 'width' => 30]
            ];

            // Rows
            $rows = [];
            $index = 1;
            foreach ($detalles as $det) {
                $cells = [
                    ['text' => $index, 'align' => 'C'],
                    ['text' => substr($det['med_nombre_quimico'], 0, 35), 'align' => 'L'],
                    ['text' => $det['dt_numero_lote_origen'], 'align' => 'C'],
                    ['text' => $det['dt_cantidad_cajas'], 'align' => 'C'],
                    ['text' => number_format($det['dt_cantidad_unidades']), 'align' => 'C'],
                    ['text' => 'Bs. ' . number_format($det['dt_subtotal_valorado'], 2), 'align' => 'R']
                ];
                $rows[] = ['cells' => $cells];
                $index++;
            }

            // Fila de totales
            $cells_total = array_fill(0, count($headers) - 1, ['text' => '', 'align' => 'C']);
            $cells_total[0] = ['text' => 'TOTALES', 'align' => 'R'];
            $cells_total[count($headers) - 5] = ['text' => $transferencia['tr_total_cajas'], 'align' => 'C'];
            $cells_total[count($headers) - 2] = ['text' => number_format($transferencia['tr_total_unidades']), 'align' => 'C'];
            $cells_total[count($headers) - 1] = ['text' => 'Bs. ' . number_format($transferencia['tr_total_valorado'], 2), 'align' => 'R'];

            $rows[] = [
                'cells' => $cells_total,
                'es_total' => true
            ];

            // Resumen
            $resumen = [
                'Total Medicamentos' => ['text' => count($detalles)],
                'Total Cajas' => ['text' => $transferencia['tr_total_cajas']],
                'Total Unidades' => ['text' => number_format($transferencia['tr_total_unidades'])],
                'Valor Total Transferido' => [
                    'text' => 'Bs. ' . number_format($transferencia['tr_total_valorado'], 2),
                    'color' => [46, 125, 50]
                ]
            ];

            // Configuración PDF
            $datos_pdf = [
                'titulo' => 'COMPROBANTE DE TRANSFERENCIA',
                'nombre_archivo' => 'Transferencia_' . $transferencia['tr_numero'] . '_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => $info_superior,
                'tabla' => [
                    'headers' => $headers,
                    'rows' => $rows
                ],
                'resumen' => $resumen
            ];

            // Crear PDF usando FPDF directamente - igual style que comprasHistorial
            require_once '../libs/fpdf/fpdf.php';

            // --- Integración de dimensiones y orientación personalizadas ---
            $orientacion = 'P';
            $unidad = 'mm';
            $tamano_papel = 'Letter'; // Tamaño por defecto

            $pdf = new FPDF($orientacion, $unidad, $tamano_papel);
            $pdf->AddPage();

            $config_empresa = mainModel::obtener_config_empresa_model();

            // Encabezado más compacto
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0, 6, ($config_empresa['ce_nombre']), 0, 1, 'C');

            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 3, ('NIT: ' . $config_empresa['ce_nit'] . ' | Telf: ' . $config_empresa['ce_telefono']), 0, 1, 'C');
            $pdf->Cell(0, 3, ($config_empresa['ce_direccion']), 0, 1, 'C');

            $pdf->SetDrawColor(52, 152, 219);
            $pdf->SetLineWidth(0.2);

            // Ancho de la línea dinámico
            $ancho_pagina = $pdf->GetPageWidth();
            $pdf->Line(10, $pdf->GetY() + 1, $ancho_pagina - 10, $pdf->GetY() + 1);
            $pdf->Ln(2);

            // Título
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0, 5, $datos_pdf['titulo'], 0, 1, 'C');
            $pdf->Ln(1);

            // Información superior compacta - igual que mainModel
            if (isset($datos_pdf['info_superior'])) {
                $pdf->SetFillColor(245, 245, 245);
                $pdf->Rect(10, $pdf->GetY(), $ancho_pagina - 20, 12, 'F');

                $pdf->SetFont('Arial', '', 7);
                $pdf->SetTextColor(52, 73, 94);

                $y_start = $pdf->GetY() + 2;
                $x_pos = 15;
                $count = 0;

                foreach ($datos_pdf['info_superior'] as $key => $value) {
                    $pdf->SetXY($x_pos, $y_start);
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->Cell(25, 3, ($key . ':'), 0, 0, 'L');
                    $pdf->SetFont('Arial', '', 7);
                    $pdf->Cell(40, 3, ($value), 0, 0, 'L');

                    $count++;
                    $x_pos += 80;
                    if ($count % 2 == 0) {
                        $y_start += 4;
                        $x_pos = 15;
                    }
                }
                $pdf->Ln(8);
            }

            // DEFINIR ALTURA MÁXIMA ANTES DEL PIE DE PÁGINA
            $altura_maxima = 250;

            // Tabla optimizada - igual que mainModel
            if (isset($datos_pdf['tabla'])) {
                $tabla = $datos_pdf['tabla'];

                // Ajustar anchos de columnas
                $ancho_total_tabla = array_sum(array_column($tabla['headers'], 'width'));
                $ancho_disponible = $ancho_pagina - 20;

                if ($ancho_total_tabla > $ancho_disponible) {
                    $factor_ajuste = $ancho_disponible / $ancho_total_tabla;
                    foreach ($tabla['headers'] as &$header) {
                        $header['width'] = round($header['width'] * $factor_ajuste);
                    }
                }

                // Encabezados compactos
                $pdf->SetFont('Arial', 'B', 6);
                $pdf->SetFillColor(52, 73, 94);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetDrawColor(52, 73, 94);

                foreach ($tabla['headers'] as $header) {
                    $pdf->Cell($header['width'], 4, ($header['text']), 1, 0, 'C', true);
                }
                $pdf->Ln();

                // Filas compactas
                $pdf->SetFont('Arial', '', 6);
                $pdf->SetTextColor(44, 62, 80);
                $pdf->SetFillColor(248, 249, 250);

                $fill = false;
                foreach ($tabla['rows'] as $row) {
                    // VERIFICAR ESPACIO CONSIDERANDO EL PIE DE PÁGINA
                    if ($pdf->GetY() > $altura_maxima) {
                        $pdf->AddPage();
                        // Redibujar encabezados
                        $pdf->SetFont('Arial', 'B', 6);
                        $pdf->SetFillColor(52, 73, 94);
                        $pdf->SetTextColor(255, 255, 255);
                        foreach ($tabla['headers'] as $header) {
                            $pdf->Cell($header['width'], 4, ($header['text']), 1, 0, 'C', true);
                        }
                        $pdf->Ln();
                        $pdf->SetFont('Arial', '', 6);
                        $pdf->SetTextColor(44, 62, 80);
                    }

                    if (isset($row['es_total']) && $row['es_total']) {
                        $pdf->SetFont('Arial', 'B', 7);
                        $pdf->SetFillColor(41, 128, 185);
                        $pdf->SetTextColor(255, 255, 255);
                        $fill_total = true;
                    } else {
                        $pdf->SetFont('Arial', '', 6);
                        $pdf->SetTextColor(44, 62, 80);
                        $fill_total = false;
                    }

                    // Filtrar celdas duplicadas también
                    $cells_filtrados = [];
                    $cell_count = 0;
                    foreach ($row['cells'] as $i => $cell) {
                        if ($cell_count < count($tabla['headers'])) {
                            $cells_filtrados[] = $cell;
                            $cell_count++;
                        }
                    }

                    foreach ($cells_filtrados as $i => $cell) {
                        $text = isset($cell['text']) ? $cell['text'] : '';
                        $width = $tabla['headers'][$i]['width'];
                        $align = isset($cell['align']) ? $cell['align'] : 'C';

                        if (isset($cell['color'])) {
                            $pdf->SetTextColor($cell['color'][0], $cell['color'][1], $cell['color'][2]);
                        }

                        $pdf->Cell($width, 4, $text, 1, 0, $align, $fill_total ? true : $fill);

                        if (isset($cell['color'])) {
                            $pdf->SetTextColor(44, 62, 80);
                        }
                    }
                    $pdf->Ln();
                    $fill = !$fill;
                }
            }

            // Resumen compacto - VERIFICAR ESPACIO PARA EL RESUMEN TAMBIÉN
            if (isset($datos_pdf['resumen'])) {
                // Altura aproximada del resumen
                $altura_resumen = 20;

                // Verificar si hay espacio para el resumen + pie de página
                if ($pdf->GetY() + $altura_resumen > $altura_maxima) {
                    $pdf->AddPage();
                }

                $pdf->Ln(3);
                $pdf->SetFillColor(236, 240, 241);
                $pdf->Rect(10, $pdf->GetY(), $ancho_pagina - 20, 15, 'F');

                $y_start = $pdf->GetY() + 2;
                $pdf->SetXY(15, $y_start);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(0, 4, ('RESUMEN DEL PERIODO'), 0, 1, 'L');

                foreach ($datos_pdf['resumen'] as $key => $value) {
                    $pdf->SetX(15);
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->Cell(50, 3, ($key . ':'), 0, 0, 'L');
                    $pdf->SetFont('Arial', '', 7);

                    if (isset($value['color'])) {
                        $pdf->SetTextColor($value['color'][0], $value['color'][1], $value['color'][2]);
                    }

                    $pdf->Cell(0, 3, ($value['text']), 0, 1, 'L');

                    if (isset($value['color'])) {
                        $pdf->SetTextColor(44, 62, 80);
                    }
                }
            }

            // Pie de página - SOLO SI ESTAMOS EN LA PRIMERA PÁGINA O HAY SUFICIENTE ESPACIO
            $pdf->SetY(-40); // Posición fija desde el fondo
            $pdf->SetFont('Arial', 'I', 6);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell(0, 2, ('Generado: ' . date('d/m/Y H:i:s') . ' | Usuario: ' . ($_SESSION['nombre_smp'] ?? 'Sistema')), 0, 1, 'C');
            $pdf->Cell(0, 2, ('Página ') . $pdf->PageNo(), 0, 0, 'C');

            // Generar y descargar PDF directamente como comprasHistorial
            $content = $pdf->Output('S');

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $datos_pdf['nombre_archivo'] . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $content;
            exit();

        } catch (Exception $e) {
            error_log("Error en generar_pdf_transferencia: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    private function obtener_badge_estado($estado)
    {
        $estados = [
            'pendiente' => '<span class="badge badge-warning">Pendiente</span>',
            'aceptada' => '<span class="badge badge-success">Aceptada</span>',
            'rechazada' => '<span class="badge badge-danger">Rechazada</span>'
        ];

        return $estados[$estado] ?? '<span class="badge badge-secondary">Desconocido</span>';
    }
}
