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

                    $html .= '<tr>
                                <td><strong>' . htmlspecialchars($tr['tr_numero']) . '</strong></td>
                                <td>' . htmlspecialchars($tr['sucursal_origen']) . ' → ' . htmlspecialchars($tr['sucursal_destino']) . '</td>
                                <td>' . htmlspecialchars($tr['usuario_emisor']) . '</td>
                                <td>' . $fecha . '</td>
                                <td>' . $estado_badge . '</td>
                                <td style="text-align:right;"><strong>Bs. ' . $monto . '</strong></td>
                                <td class="buttons">
                                    <a href="#" class="btn default" onclick="event.preventDefault(); TransferirHistorialModals.verDetalle(' . (int)$tr['tr_id'] . ', \'' . htmlspecialchars($tr['tr_numero']) . '\')" title="Ver detalles">
                                        <ion-icon name="eye-outline"></ion-icon>
                                    </a>
                                    <a href="#" class="btn default" onclick="event.preventDefault(); TransferirHistorialModals.descargarPDF(' . (int)$tr['tr_id'] . ')" title="Descargar PDF">
                                        <ion-icon name="download-outline"></ion-icon>
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
        $tr_id = isset($_POST['tr_id']) ? (int)$_POST['tr_id'] : 0;
        $rol = $_SESSION['rol_smp'] ?? 1;
        $su_usuario = $_SESSION['sucursal_smp'] ?? 0;

        if (!$tr_id) {
            return json_encode(['success' => false, 'error' => 'ID inválido']);
        }

        try {
            $stmt_tr = transferirModel::datos_transferencia_completa_model($tr_id);
            $transferencia = $stmt_tr->fetch(PDO::FETCH_ASSOC);

            if (!$transferencia) {
                return json_encode(['success' => false, 'error' => 'Transferencia no encontrada']);
            }

            if ($rol != 1 && $transferencia['su_origen_id'] != $su_usuario && $transferencia['su_destino_id'] != $su_usuario) {
                return json_encode(['success' => false, 'error' => 'No tiene permiso']);
            }

            $stmt_dt = transferirModel::detalle_transferencia_model($tr_id);
            $detalles = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);

            require_once '../libs/fpdf/fpdf.php';

            $pdf = new FPDF('P', 'mm', 'Letter');
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();

            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0, 8, utf8_decode('COMPROBANTE DE TRANSFERENCIA'), 0, 1, 'C');
            $pdf->Ln(3);

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(40, 5, utf8_decode('Número:'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, utf8_decode($transferencia['tr_numero']), 0, 1);

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(40, 5, utf8_decode('Fecha Envío:'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, utf8_decode(date('d/m/Y H:i', strtotime($transferencia['tr_fecha_envio']))), 0, 1);

            if ($transferencia['tr_fecha_respuesta']) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(40, 5, utf8_decode('Fecha Recepción:'), 0, 0);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(0, 5, utf8_decode(date('d/m/Y H:i', strtotime($transferencia['tr_fecha_respuesta']))), 0, 1);
            }

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(40, 5, utf8_decode('Origen:'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, utf8_decode($transferencia['sucursal_origen']), 0, 1);

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(40, 5, utf8_decode('Destino:'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, utf8_decode($transferencia['sucursal_destino']), 0, 1);

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(40, 5, utf8_decode('Emisor:'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, utf8_decode($transferencia['usuario_emisor']), 0, 1);

            if (!empty($transferencia['usuario_receptor'])) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(40, 5, utf8_decode('Receptor:'), 0, 0);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(0, 5, utf8_decode($transferencia['usuario_receptor']), 0, 1);
            }

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(40, 5, utf8_decode('Estado:'), 0, 0);
            $pdf->SetFont('Arial', '', 9);
            $estado_texto = ucfirst(str_replace(['pendiente', 'aceptada', 'rechazada'], ['Pendiente', 'Aceptada', 'Rechazada'], $transferencia['tr_estado']));
            $pdf->Cell(0, 5, utf8_decode($estado_texto), 0, 1);

            $pdf->Ln(3);

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(52, 73, 94);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(8, 6, '#', 1, 0, 'C', true);
            $pdf->Cell(55, 6, 'Medicamento', 1, 0, 'L', true);
            $pdf->Cell(25, 6, 'Lote', 1, 0, 'C', true);
            $pdf->Cell(18, 6, 'Cajas', 1, 0, 'C', true);
            $pdf->Cell(25, 6, 'Unidades', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Subtotal', 1, 1, 'R', true);

            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(44, 62, 80);

            $index = 1;
            foreach ($detalles as $det) {
                $pdf->Cell(8, 5, $index, 1, 0, 'C');
                $pdf->Cell(55, 5, utf8_decode(substr($det['med_nombre_quimico'], 0, 35)), 1, 0, 'L');
                $pdf->Cell(25, 5, utf8_decode($det['dt_numero_lote_origen']), 1, 0, 'C');
                $pdf->Cell(18, 5, $det['dt_cantidad_cajas'], 1, 0, 'C');
                $pdf->Cell(25, 5, number_format($det['dt_cantidad_unidades']), 1, 0, 'C');
                $pdf->Cell(30, 5, 'Bs. ' . number_format($det['dt_subtotal_valorado'], 2), 1, 1, 'R');
                $index++;
            }

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(41, 128, 185);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(8, 5, '', 1, 0);
            $pdf->Cell(55, 5, 'TOTALES', 1, 0, 'R');
            $pdf->Cell(25, 5, '', 1, 0);
            $pdf->Cell(18, 5, $transferencia['tr_total_cajas'], 1, 0, 'C');
            $pdf->Cell(25, 5, number_format($transferencia['tr_total_unidades']), 1, 0, 'C');
            $pdf->Cell(30, 5, 'Bs. ' . number_format($transferencia['tr_total_valorado'], 2), 1, 1, 'R');

            $pdf_output = $pdf->Output('S');
            $pdf_base64 = base64_encode($pdf_output);

            return json_encode([
                'success' => true,
                'pdf_base64' => $pdf_base64
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en generar_pdf_transferencia: " . $e->getMessage());
            return json_encode(['success' => false, 'error' => 'Error al generar PDF']);
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
