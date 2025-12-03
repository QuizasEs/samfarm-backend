

<?php

if ($peticionAjax) {
    require_once '../models/transferirModel.php';
} else {
    require_once './models/transferirModel.php';
}

class transferirController extends transferirModel
{
    public function buscar_lotes_disponibles_controller()
    {
        $su_origen = $_SESSION['sucursal_smp'];
        $rol = $_SESSION['rol_smp'];

        if ($rol == 1 && isset($_POST['su_origen']) && !empty($_POST['su_origen'])) {
            $su_origen = mainModel::limpiar_cadena($_POST['su_origen']);
        } elseif ($rol != 1) {
            $su_origen = $_SESSION['sucursal_smp'];
        }

        $busqueda = mainModel::limpiar_cadena($_POST['busqueda'] ?? '');
        $laboratorio = mainModel::limpiar_cadena($_POST['laboratorio'] ?? '');
        $fecha_venc_max = mainModel::limpiar_cadena($_POST['fecha_venc_max'] ?? '');

        try {
            $stmt = transferirModel::buscar_lotes_disponibles_model($su_origen, $busqueda, $laboratorio, $fecha_venc_max);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return json_encode($datos, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en buscar_lotes: " . $e->getMessage());
            return json_encode(['error' => 'Error al buscar lotes']);
        }
    }

    public function generar_transferencia_controller()
    {
        $items = json_decode($_POST['items_json'], true);

        if (empty($items)) {
            return json_encode(['error' => 'No hay items para transferir']);
        }

        $su_origen = $_SESSION['sucursal_smp'];
        $us_emisor = $_SESSION['id_smp'];
        $rol = $_SESSION['rol_smp'];
        $observaciones = mainModel::limpiar_cadena($_POST['observaciones'] ?? '');

        $sucursales_destino = array_unique(array_column($items, 'su_destino'));

        if (in_array($su_origen, $sucursales_destino)) {
            return json_encode(['error' => 'No puede transferir a su propia sucursal']);
        }

        if ($rol != 1 && count($sucursales_destino) > 1) {
            return json_encode(['error' => 'Gerente solo puede transferir a una sucursal destino a la vez']);
        }

        $tr_numero = $this->generar_numero_transferencia_controller();

        try {
            $conexion = mainModel::conectar();
            $conexion->beginTransaction();

            $datos_transfer = [
                'tr_numero' => $tr_numero,
                'su_origen_id' => $su_origen,
                'us_emisor_id' => $us_emisor,
                'tr_total_items' => count($items),
                'tr_observaciones' => $observaciones
            ];

            $tr_id = transferirModel::crear_transferencia_model($datos_transfer);

            if (!$tr_id) {
                throw new Exception("No se pudo crear la transferencia");
            }

            $total_cajas = 0;
            $total_unidades = 0;
            $total_valorado = 0;

            foreach ($items as $item) {
                $lm_id = (int)$item['lm_id'];
                $cantidad_cajas = (int)$item['cantidad_cajas'];
                $cantidad_unidades = (int)$item['cantidad_unidades'];
                $su_destino = (int)$item['su_destino'];

                $lote = transferirModel::datos_lote_transfer_model($lm_id)->fetch();

                if (!$lote || $lote['lm_cant_actual_cajas'] < $cantidad_cajas) {
                    throw new Exception("Stock insuficiente en lote " . ($lote['lm_numero_lote'] ?? 'desconocido'));
                }

                $subtotal = $cantidad_cajas * $lote['lm_precio_compra'];

                $datos_detalle = [
                    'tr_id' => $tr_id,
                    'lm_origen_id' => $lm_id,
                    'med_id' => $lote['med_id'],
                    'dt_numero_lote_origen' => $lote['lm_numero_lote'],
                    'dt_cantidad_cajas' => $cantidad_cajas,
                    'dt_cantidad_unidades' => $cantidad_unidades,
                    'dt_precio_compra' => $lote['lm_precio_compra'],
                    'dt_precio_venta' => $lote['lm_precio_venta'],
                    'dt_subtotal_valorado' => $subtotal
                ];

                transferirModel::insertar_detalle_transferencia_model($datos_detalle);

                transferirModel::descontar_stock_lote_model($lm_id, $cantidad_cajas, $cantidad_unidades);

                transferirModel::descontar_inventario_model(
                    $lote['med_id'],
                    $su_origen,
                    $cantidad_cajas,
                    $cantidad_unidades,
                    $subtotal
                );

                $datos_movimiento = [
                    'lm_id' => $lm_id,
                    'med_id' => $lote['med_id'],
                    'su_id' => $su_origen,
                    'us_id' => $us_emisor,
                    'mi_tipo' => 'salida',
                    'mi_cantidad' => $cantidad_unidades,
                    'mi_unidad' => 'unidad',
                    'mi_referencia_tipo' => 'transferencia_salida',
                    'mi_referencia_id' => $tr_id,
                    'mi_motivo' => "Transferencia #{$tr_numero} hacia sucursal destino"
                ];

                transferirModel::registrar_movimiento_inventario_model($datos_movimiento);

                $datos_historial = [
                    'lm_id' => $lm_id,
                    'us_id' => $us_emisor,
                    'hl_accion' => 'transferencia_salida',
                    'hl_descripcion' => "Salida de {$cantidad_cajas} cajas por transferencia #{$tr_numero}"
                ];

                transferirModel::registrar_historial_lote_model($datos_historial);

                $total_cajas += $cantidad_cajas;
                $total_unidades += $cantidad_unidades;
                $total_valorado += $subtotal;
            }

            transferirModel::actualizar_totales_transferencia_model($tr_id, $total_cajas, $total_unidades, $total_valorado);

            $config_informe = [
                'tipo_informe' => 'transferencia_salida',
                'tr_id' => $tr_id,
                'tr_numero' => $tr_numero,
                'su_origen' => $su_origen,
                'us_emisor' => $us_emisor,
                'total_items' => count($items),
                'total_cajas' => $total_cajas,
                'total_unidades' => $total_unidades,
                'total_valorado' => $total_valorado,
                'tr_estado' => 'pendiente'
            ];

            transferirModel::registrar_informe_model([
                'inf_nombre' => "Transferencia {$tr_numero}",
                'inf_tipo' => 'transferencia',
                'inf_usuario' => $us_emisor,
                'inf_config' => json_encode($config_informe, JSON_UNESCAPED_UNICODE)
            ]);

            $conexion->commit();

            $pdf_base64 = $this->generar_pdf_transferencia_controller($tr_id);

            return json_encode([
                'Tipo' => 'success',
                'Titulo' => 'Transferencia generada exitosamente',
                'texto' => "Número: <strong>{$tr_numero}</strong><br>Total: <strong>Bs. " . number_format($total_valorado, 2) . "</strong>",
                'tr_numero' => $tr_numero,
                'pdf_base64' => $pdf_base64
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error en generar_transferencia: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    private function generar_numero_transferencia_controller()
    {
        $ultimo = transferirModel::obtener_ultimo_numero_transferencia_model();
        $anio_actual = date('Y');
        $nuevo_numero = 1;

        if ($ultimo) {
            $patron = '/^TRANS-(\d{4})-(\d+)$/';
            if (preg_match($patron, $ultimo, $match)) {
                $anio_anterior = $match[1];
                $numero_anterior = (int)$match[2];

                if ($anio_anterior === $anio_actual) {
                    $nuevo_numero = $numero_anterior + 1;
                }
            }
        }

        return 'TRANS-' . $anio_actual . '-' . str_pad($nuevo_numero, 4, '0', STR_PAD_LEFT);
    }

    private function generar_pdf_transferencia_controller($tr_id)
    {
        require_once '../libs/fpdf/fpdf.php';

        $transferencia = transferirModel::datos_transferencia_completa_model($tr_id)->fetch();
        $detalles = transferirModel::detalle_transferencia_model($tr_id)->fetchAll();

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 6, utf8_decode('COMPROBANTE DE TRANSFERENCIA'), 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, 5, utf8_decode('Número:'), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, utf8_decode($transferencia['tr_numero']), 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, 5, utf8_decode('Fecha:'), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, utf8_decode(date('d/m/Y H:i', strtotime($transferencia['tr_fecha_envio']))), 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, 5, utf8_decode('Origen:'), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, utf8_decode($transferencia['sucursal_origen']), 0, 1);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(40, 5, utf8_decode('Destino:'), 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 5, utf8_decode($transferencia['sucursal_destino']), 0, 1);

        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(52, 73, 94);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
        $pdf->Cell(70, 6, 'Medicamento', 1, 0, 'L', true);
        $pdf->Cell(30, 6, 'Lote', 1, 0, 'C', true);
        $pdf->Cell(20, 6, 'Cajas', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'Unidades', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'Subtotal', 1, 1, 'R', true);

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(44, 62, 80);

        $index = 1;
        foreach ($detalles as $det) {
            $pdf->Cell(10, 5, $index, 1, 0, 'C');
            $pdf->Cell(70, 5, utf8_decode(substr($det['med_nombre_quimico'], 0, 40)), 1, 0, 'L');
            $pdf->Cell(30, 5, utf8_decode($det['dt_numero_lote_origen']), 1, 0, 'C');
            $pdf->Cell(20, 5, $det['dt_cantidad_cajas'], 1, 0, 'C');
            $pdf->Cell(30, 5, number_format($det['dt_cantidad_unidades']), 1, 0, 'C');
            $pdf->Cell(30, 5, 'Bs. ' . number_format($det['dt_subtotal_valorado'], 2), 1, 1, 'R');
            $index++;
        }

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(41, 128, 185);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(10, 5, '', 1, 0);
        $pdf->Cell(70, 5, 'TOTALES', 1, 0, 'R');
        $pdf->Cell(30, 5, '', 1, 0);
        $pdf->Cell(20, 5, $transferencia['tr_total_cajas'], 1, 0, 'C');
        $pdf->Cell(30, 5, number_format($transferencia['tr_total_unidades']), 1, 0, 'C');
        $pdf->Cell(30, 5, 'Bs. ' . number_format($transferencia['tr_total_valorado'], 2), 1, 1, 'R');

        $pdf_output = $pdf->Output('S');
        return base64_encode($pdf_output);
    }



    public function aceptar_transferencia_controller()
    {
        $tr_id = isset($_POST['tr_id']) ? (int)$_POST['tr_id'] : 0;
        $us_receptor = $_SESSION['id_smp'] ?? 0;
        $rol = $_SESSION['rol_smp'] ?? 0;
        $su_usuario = $_SESSION['sucursal_smp'] ?? 0;

        if (!$tr_id || !$us_receptor) {
            return json_encode(['error' => 'Datos inválidos']);
        }

        try {
            $conexion = mainModel::conectar();
            $conexion->beginTransaction();

            $stmt_tr = transferirModel::datos_transferencia_completa_model($tr_id);
            $transferencia = $stmt_tr->fetch(PDO::FETCH_ASSOC);

            if (!$transferencia) {
                throw new Exception("Transferencia no encontrada");
            }

            if ($rol != 1 && $transferencia['su_destino_id'] != $su_usuario) {
                throw new Exception("No tiene permisos para aceptar esta transferencia");
            }

            if ($transferencia['tr_estado'] != 'pendiente') {
                throw new Exception("La transferencia no está en estado pendiente");
            }

            $stmt_dt = transferirModel::detalle_transferencia_model($tr_id);
            $detalles = $stmt_dt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($detalles as $det) {
                $lm_origen_id = $det['dt_id'];
                $med_id = $det['med_id'];
                $dt_cantidad_cajas = $det['dt_cantidad_cajas'];
                $dt_cantidad_unidades = $det['dt_cantidad_unidades'];
                $dt_numero_lote = $det['dt_numero_lote_origen'];
                $dt_precio_compra = $det['dt_precio_compra'];
                $dt_precio_venta = $det['dt_precio_venta'];
                $subtotal = $det['dt_subtotal_valorado'];

                $stmt_lm_origen = transferirModel::datos_lote_transfer_model($det['lm_origen_id']);
                $lm_origen = $stmt_lm_origen->fetch(PDO::FETCH_ASSOC);

                if (!$lm_origen) {
                    throw new Exception("Lote origen no encontrado");
                }

                $datos_lote = [
                    'med_id' => $med_id,
                    'su_id' => $transferencia['su_destino_id'],
                    'lm_numero_lote' => $dt_numero_lote,
                    'lm_cant_caja' => $dt_cantidad_cajas,
                    'lm_cant_blister' => $lm_origen['lm_cant_blister'],
                    'lm_cant_unidad' => $lm_origen['lm_cant_unidad'],
                    'lm_cant_actual_cajas' => $dt_cantidad_cajas,
                    'lm_cant_actual_unidades' => $dt_cantidad_unidades,
                    'lm_precio_compra' => $dt_precio_compra,
                    'lm_precio_venta' => $dt_precio_venta,
                    'lm_fecha_vencimiento' => $lm_origen['lm_fecha_vencimiento'],
                    'lm_origen_id' => $det['lm_origen_id']
                ];

                $lm_destino_id = transferirModel::crear_lote_en_destino_model($datos_lote);

                transferirModel::actualizar_inventario_destino_model(
                    $med_id,
                    $transferencia['su_destino_id'],
                    $dt_cantidad_cajas,
                    $dt_cantidad_unidades,
                    $subtotal
                );

                $datos_movimiento = [
                    'lm_id' => $lm_destino_id,
                    'med_id' => $med_id,
                    'su_id' => $transferencia['su_destino_id'],
                    'us_id' => $us_receptor,
                    'mi_tipo' => 'entrada',
                    'mi_cantidad' => $dt_cantidad_unidades,
                    'mi_unidad' => 'unidad',
                    'mi_referencia_tipo' => 'transferencia_entrada',
                    'mi_referencia_id' => $tr_id,
                    'mi_motivo' => "Entrada de {$dt_cantidad_cajas} cajas por transferencia #{$transferencia['tr_numero']}"
                ];

                transferirModel::registrar_movimiento_entrada_model($datos_movimiento);

                $sql_update_detalle = "UPDATE detalle_transferencia SET lm_destino_id = :lm_destino_id WHERE dt_id = :dt_id";
                $stmt_update = mainModel::conectar()->prepare($sql_update_detalle);
                $stmt_update->execute([':lm_destino_id' => $lm_destino_id, ':dt_id' => $det['dt_id']]);
            }

            transferirModel::actualizar_estado_transferencia_model($tr_id, 'aceptada', $us_receptor);

            $conexion->commit();

            return json_encode([
                'Tipo' => 'success',
                'Titulo' => 'Transferencia aceptada',
                'texto' => "Transferencia #{$transferencia['tr_numero']} aceptada correctamente"
            ], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error en aceptar_transferencia: " . $e->getMessage());
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    private function truncar_texto($texto, $longitud)
    {
        if (strlen($texto) > $longitud) {
            return substr($texto, 0, $longitud - 3) . '...';
        }
        return $texto;
    }
}


