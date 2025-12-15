<?php

if ($peticionAjax) {
    require_once '../models/ventasHistorialModel.php';
} else {
    require_once './models/ventasHistorialModel.php';
}
class ventasHistorialController extends ventasHistorialModel
{
    /* Paginador para el historial de ventas */
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
            // Admin: puede ver todas o filtrar
            if ($f1 !== '') {
                $filtros['su_id'] = (int)$f1;
            }
        } elseif ($rol_usuario == 2) {
            // Gerente: solo su sucursal
            $filtros['su_id'] = $sucursal_usuario;
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        if ($f2 !== '' && is_numeric($f2)) {
            $filtros['cliente'] = (int)$f2;
        }

        if ($f3 !== '' && is_numeric($f3)) {
            $filtros['vendedor'] = (int)$f3;
        }

        if ($f4 !== '') {
            $filtros['tipo_documento'] = $f4;
        }

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

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

        $mostrar_columna_sucursal = ($rol_usuario == 1 && empty($f1));
        $colspan_total = $mostrar_columna_sucursal ? 10 : 9;

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
                            '
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
                $cliente_display = !empty($row['cliente_nombre'])
                    ? htmlspecialchars($row['cliente_nombre'])
                    : '<span style="color:#999;">Sin cliente</span>';

                $tipo_doc = !empty($row['ve_tipo_documento'])
                    ? strtoupper($row['ve_tipo_documento'])
                    : 'VENTA';

                $tipo_doc_html = '<span class="estado-badge activo" style="font-size:10px;">' . $tipo_doc . '</span>';

                $tiene_factura = !empty($row['fa_id']);

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td><strong>' . htmlspecialchars($row['ve_numero_documento']) . '</strong></td>
                        <td>' . date('d/m/Y H:i', strtotime($row['ve_fecha_emision'])) . '</td>
                        <td>' . $cliente_display . '</td>
                        <td>' . htmlspecialchars($row['vendedor_nombre']) . '</td>' .
                    ($mostrar_columna_sucursal ? '<td><span style=";padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">' . htmlspecialchars($row['sucursal_nombre']) . '</span></td>' : '') .'
                        <td style="text-align:right;font-size:14px;"><strong style="color:#2e7d32;">Bs. ' . number_format($row['ve_total'], 2) . '</strong></td>
                        <td style="text-align:center;">' . $tipo_doc_html . '</td>
                        <td class="buttons">
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


    /* Obtener el detalle completo de una venta */
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

            $itemsStmt = self::detalle_items_venta_model($ve_id);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

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

            $cliente_nombre = trim(
                ($venta['cl_nombres'] ?? '') . ' ' .
                    ($venta['cl_apellido_paterno'] ?? '') . ' ' .
                    ($venta['cl_apellido_materno'] ?? '')
            );
            if (empty($cliente_nombre)) {
                $cliente_nombre = 'Sin cliente';
            }

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

    /* Generar el PDF de reimpresión de nota de venta desde historial */
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

        try {
            require_once '../libs/fpdf/fpdf.php';

            // Consulta directa desde ventas (sin depender de factura)
            $db = mainModel::conectar();
            $sql = "
                SELECT
                    v.ve_id, v.ve_numero_documento, v.ve_total, v.ve_subtotal, v.ve_impuesto, v.ve_fecha_emision,
                    c.cl_nombres, c.cl_apellido_paterno, c.cl_apellido_materno, c.cl_carnet,
                    u.us_nombres, u.us_apellido_paterno,
                    s.su_nombre
                FROM ventas v
                LEFT JOIN clientes c ON c.cl_id = v.cl_id
                INNER JOIN usuarios u ON u.us_id = v.us_id
                INNER JOIN sucursales s ON s.su_id = v.su_id
                WHERE v.ve_id = :ve_id
            ";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":ve_id", $ve_id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() <= 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'Venta no encontrada',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Consulta detalles de venta
            $sql2 = "
                SELECT dv.*,
                    m.med_nombre_quimico AS med_nombre,
                    COALESCE(m.med_version_comercial, '') AS version_comercial,
                    COALESCE(ff.ff_nombre, '') AS presentacion
                FROM detalle_venta dv
                INNER JOIN medicamento m ON m.med_id = dv.med_id
                LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
                WHERE dv.ve_id = :ve_id
            ";
            $stmt2 = $db->prepare($sql2);
            $stmt2->bindParam(":ve_id", $ve_id, PDO::PARAM_INT);
            $stmt2->execute();
            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Config empresa
            $cfg_sql = "SELECT * FROM configuracion_empresa ORDER BY ce_id DESC LIMIT 1";
            $cfg_stmt = $db->prepare($cfg_sql);
            $cfg_stmt->execute();
            $empresa = $cfg_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$empresa) {
                $empresa = ['ce_nombre' => 'SAMFARM', 'ce_nit' => 'S/N', 'ce_direccion' => '', 'ce_telefono' => '', 'ce_logo' => null];
            }

            // Generar PDF usando la misma lógica que venta (sin utf8_decode deprecated)
            $pdf_base64 = $this->generar_pdf_nota_venta_rep($data, $detalles, $empresa);

            if (!$pdf_base64) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo generar el PDF',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

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

    /* Método para generar PDF de nota de venta para reimpresión (igual que venta pero sin utf8_decode) */
    private function generar_pdf_nota_venta_rep($data, $detalles, $empresa)
    {
        // Calcular altura dinámica
        $altura_base = 95;
        $altura_items = 0;
        $line_height = 4;
        $char_per_line = 45;

        foreach ($detalles as $d) {
            $nombre_producto = $d['med_nombre'] ?? 'Producto';
            $lineas_nombre = ceil(mb_strlen($nombre_producto, 'UTF-8') / $char_per_line);
            $altura_items += $lineas_nombre * $line_height;
        }

        $altura_total = $altura_base + $altura_items;

        $pdf = new FPDF('P', 'mm', [80, $altura_total]);
        $pdf->AddPage();
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);

        // Encabezado empresa
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 5, ($empresa['ce_nombre']), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, 'Sucursal: ' . ($data['su_nombre']), 0, 1, 'C');
        $pdf->MultiCell(0, 4, ($empresa['ce_direccion']), 0, 'C');
        $pdf->Cell(0, 4, 'Telf: ' . $empresa['ce_telefono'], 0, 1, 'C');
        $pdf->Cell(0, 4, 'NIT: ' . $empresa['ce_nit'], 0, 1, 'C');
        $pdf->Ln(3);

        // Título
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(0, 5, 'NOTA DE VENTA', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, '-------------------------------------------------------------------', 0, 1, 'C');

        // Datos venta
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'N Venta:', 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, $data['ve_numero_documento'], 0, 1);

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'Fecha:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, date('d/m/Y H:i', strtotime($data['ve_fecha_emision'])), 0, 1);

        $nombre_cliente = trim(
            ($data['cl_nombres'] ?? '') . ' ' .
                ($data['cl_apellido_paterno'] ?? '') . ' ' .
                ($data['cl_apellido_materno'] ?? '')
        );
        if (empty($nombre_cliente)) $nombre_cliente = 'Sin Cliente';

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'Cliente:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->MultiCell(0, 4, ($nombre_cliente));

        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'CI/NIT:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $data['cl_carnet'] ?? 'S/N', 0, 1);

        $vendedor_nombre = trim(($data['us_nombres'] ?? '') . ' ' . ($data['us_apellido_paterno'] ?? ''));
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(15, 4, 'Vendedor:', 0, 0);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, ($vendedor_nombre), 0, 1);

        $pdf->Ln(2);

        // Tabla detalles
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(35, 5, 'Producto', 'B', 0, 'L');
        $pdf->Cell(10, 5, 'Cant.', 'B', 0, 'C');
        $pdf->Cell(12, 5, 'P.U.', 'B', 0, 'R');
        $pdf->Cell(13, 5, 'Subtotal', 'B', 1, 'R');

        $pdf->SetFont('Arial', '', 7);
        foreach ($detalles as $d) {
            $y_actual = $pdf->GetY();
            $x_actual = $pdf->GetX();

            $pdf->MultiCell(35, 4, ($d['med_nombre']), 0, 'L');
            $y_despues = $pdf->GetY();

            $pdf->SetXY($x_actual + 35, $y_actual);
            $pdf->Cell(10, 4, $d['dv_cantidad'], 0, 0, 'C');
            $pdf->Cell(12, 4, number_format($d['dv_precio_unitario'], 2), 0, 0, 'R');
            $pdf->Cell(13, 4, number_format($d['dv_subtotal'], 2), 0, 1, 'R');
            $pdf->SetY($y_despues);
        }
        $pdf->Cell(0, 4, '-------------------------------------------------------------------', 0, 1, 'C');

        // Totales
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(45, 5, 'Subtotal:', 0, 0, 'R');
        $pdf->Cell(25, 5, 'Bs. ' . number_format($data['ve_subtotal'], 2), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(45, 6, 'TOTAL A PAGAR:', 0, 0, 'R');
        $pdf->Cell(25, 6, 'Bs. ' . number_format($data['ve_total'], 2), 0, 1, 'R');

        $pdf->Ln(5);

        // Pie
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->Cell(0, 4, '¡Gracias por su compra!', 0, 1, 'C');
        $pdf->Cell(0, 4, 'Este documento es una nota de venta.', 0, 1, 'C');

        $contenido_pdf = $pdf->Output('S');
        $pdf_base64 = base64_encode($contenido_pdf);

        error_log("PDF reimpresión generado exitosamente para venta #{$data['ve_id']}");
        return $pdf_base64;
    }


    /* Exportar el historial de ventas a un archivo Excel */
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

            $headers = array_keys($datos[0]);

            $info_superior = [
                'Fecha de Generación' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema',
                'Total de Registros' => count($datos)
            ];

            if (!empty($filtros['su_id'])) {
                $info_superior['Sucursal'] = 'Sucursal ID ' . $filtros['su_id'];
            } else {
                $info_superior['Sucursal'] = 'Todas las Sucursales';
            }

            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $info_superior['Rango de Fechas'] = date('d/m/Y', strtotime($filtros['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
            }

            mainModel::generar_excel_reporte([
                'titulo' => 'HISTORIAL DE VENTAS',
                'datos' => $datos,
                'headers' => $headers,
                'nombre_archivo' => $filename,
                'formato_columnas' => [
                    'Subtotal (Bs)' => 'moneda',
                    'Impuestos (Bs)' => 'moneda',
                    'Total (Bs)' => 'moneda',
                    'Items' => 'numero'
                ],
                'columnas_totales' => ['Total (Bs)'],
                'info_superior' => $info_superior
            ]);

        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . htmlspecialchars($e->getMessage());
        }
    }
}
