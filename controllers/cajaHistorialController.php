<?php

if ($peticionAjax) {
    require_once '../models/cajaHistorialModel.php';
} else {
    require_once './models/cajaHistorialModel.php';
}

class cajaHistorialController extends cajaHistorialModel
{
    public function paginado_historial_caja_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h3>
                        <p>No tiene permisos para ver el historial de caja</p>
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
        $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
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

        if ($f1 !== '') {
            $tipos_validos = ['ingreso', 'egreso', 'venta', 'compra', 'ajuste'];
            if (in_array($f1, $tipos_validos)) {
                $filtros['tipo'] = $f1;
            }
        }

        if ($f2 !== '' && is_numeric($f2)) {
            $filtros['usuario'] = (int)$f2;
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
            $filtros['fecha_hasta'] = date('Y-m-d');
        } elseif ($fecha_hasta_valida) {
            $filtros['fecha_desde'] = $fecha_hasta;
            $filtros['fecha_hasta'] = $fecha_hasta;
        } else {
            $filtros['fecha_desde'] = date('Y-m-d');
            $filtros['fecha_hasta'] = date('Y-m-d');
        }

        error_log("DEBUG FILTROS CAJA: " . print_r($filtros, true));

        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_historial_caja_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_historial_caja_model($filtros);

            error_log("DEBUG RESULTADOS CAJA: Total=$total, Registros=" . count($datos));
        } catch (PDOException $e) {
            error_log("ERROR SQL CAJA: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $mostrar_columna_sucursal = ($rol_usuario == 1);
        $colspan_total = $mostrar_columna_sucursal ? 10 : 9;

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>USUARIO</th>
                            <th>CAJA</th>
                            <th>FECHA</th>
                            <th>TIPO</th>
                            <th>CONCEPTO</th>
                            <th>REFERENCIA</th>
                            <th>MONTO</th>' .
            ($mostrar_columna_sucursal ? '<th>SUCURSAL</th>' : '') .
            '<th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $tipo_badge = self::generar_badge_tipo($row['mc_tipo']);
                $monto_formato = self::formatear_monto($row['mc_monto'], $row['mc_tipo']);
                $referencia = self::formatear_referencia($row['mc_referencia_tipo'], $row['mc_referencia_id']);
                $fecha_formato = date('d/m/Y H:i', strtotime($row['mc_fecha']));
                $usuario = trim(($row['us_nombres'] ?? 'Sistema') . ' ' . ($row['us_apellido_paterno'] ?? ''));

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>' . htmlspecialchars($usuario) . '</td>
                        <td>' . htmlspecialchars($row['caja_nombre']) . '</td>
                        <td>' . $fecha_formato . '</td>
                        <td>' . $tipo_badge . '</td>
                        <td>' . htmlspecialchars($row['mc_concepto'] ?? '-') . '</td>
                        <td>' . $referencia . '</td>
                        <td>' . $monto_formato . '</td>' .
                    ($mostrar_columna_sucursal ? '<td><span style="background:#E3F2FD;padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">' . htmlspecialchars($row['su_nombre']) . '</span></td>' : '') .
                    '<td class="accion-buttons">
                            <a href="javascript:void(0)" 
                            class="btn default" 
                            title="Ver referencia"
                            onclick="CajaHistorial.verReferencia(\'' . $row['mc_referencia_tipo'] . '\', ' . $row['mc_referencia_id'] . ')">
                                <ion-icon name="open-outline"></ion-icon> Detalles
                            </a>
                            <a href="javascript:void(0)" 
                            class="btn success" 
                            title="Exportar PDF"
                            onclick="CajaHistorial.exportarMovimiento(' . $row['mc_id'] . ')">
                                <ion-icon name="document-text-outline"></ion-icon> PDF
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="' . $colspan_total . '" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="file-tray-outline"></ion-icon> No hay movimientos registrados
                        </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }

    private static function generar_badge_tipo($tipo)
    {
        $badges = [
            'ingreso' => '<span class="estado-badge activo"><ion-icon name="arrow-down-circle-outline"></ion-icon> INGRESO</span>',
            'egreso' => '<span class="estado-badge caducado"><ion-icon name="arrow-up-circle-outline"></ion-icon> EGRESO</span>',
            'venta' => '<span class="estado-badge activo"><ion-icon name="cart-outline"></ion-icon> VENTA</span>',
            'compra' => '<span class="estado-badge espera"><ion-icon name="bag-outline"></ion-icon> COMPRA</span>',
            'ajuste' => '<span class="estado-badge desconocido"><ion-icon name="create-outline"></ion-icon> AJUSTE</span>'
        ];

        return $badges[$tipo] ?? '<span class="estado-badge desconocido">' . strtoupper($tipo) . '</span>';
    }

    private static function formatear_monto($monto, $tipo)
    {
        $color = in_array($tipo, ['ingreso', 'venta']) ? '#4caf50' : '#f44336';
        $signo = in_array($tipo, ['ingreso', 'venta']) ? '+' : '-';
        return '<strong style="color:' . $color . ';font-size:15px;">' . $signo . 'Bs. ' . number_format($monto, 2) . '</strong>';
    }

    private static function formatear_referencia($tipo, $id)
    {
        if (!$tipo || !$id) return '<span style="color:#999;">N/A</span>';

        $tipos = [
            'venta' => 'Venta',
            'compra' => 'Compra',
            'apertura' => 'Apertura',
            'cierre' => 'Cierre',
            'ajuste' => 'Ajuste'
        ];

        $nombre = $tipos[$tipo] ?? ucfirst($tipo);
        return '<span style="background:#f5f5f5;padding:3px 8px;border-radius:3px;font-size:13px;">' . $nombre . ' #' . $id . '</span>';
    }

    public function obtener_resumen_periodo_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif ($rol_usuario == 1 && isset($_POST['select4']) && $_POST['select4'] !== '') {
            $filtros['su_id'] = (int)$_POST['select4'];
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : date('Y-m-d');
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : date('Y-m-d');

        $filtros['fecha_desde'] = $fecha_desde;
        $filtros['fecha_hasta'] = $fecha_hasta;

        if (isset($_POST['select1']) && $_POST['select1'] !== '') {
            $filtros['caja_id'] = (int)$_POST['select1'];
        }

        if (isset($_POST['select2']) && $_POST['select2'] !== '') {
            $filtros['tipo'] = mainModel::limpiar_cadena($_POST['select2']);
        }

        if (isset($_POST['select3']) && $_POST['select3'] !== '') {
            $filtros['usuario'] = (int)$_POST['select3'];
        }

        try {
            $resumen = self::obtener_resumen_periodo_model($filtros);
            return json_encode($resumen, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_resumen_periodo_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al obtener resumen']);
        }
    }

    public function obtener_datos_grafico_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif ($rol_usuario == 1 && isset($_POST['select4']) && $_POST['select4'] !== '') {
            $filtros['su_id'] = (int)$_POST['select4'];
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : date('Y-m-d');
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : date('Y-m-d');

        $filtros['fecha_desde'] = $fecha_desde;
        $filtros['fecha_hasta'] = $fecha_hasta;

        try {
            $datos = self::obtener_datos_grafico_model($filtros);
            return json_encode($datos, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_datos_grafico_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al obtener datos del gráfico']);
        }
    }
    /* optener usuario caja */
    public function usuario_caja_controller($id, $rol)
    {
        $sql_cajas = mainModel::conectar()->prepare("
                        SELECT c.caja_id, c.caja_nombre, s.su_nombre 
                        FROM caja c
                        INNER JOIN sucursales s ON c.su_id = s.su_id
                        WHERE c.caja_activa = 1
                        " . ($rol == 2 ? "AND c.su_id = " : "") . "
                        ORDER BY s.su_nombre, c.caja_nombre
                    ");
    }


    public function exportar_historial_caja_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif ($rol_usuario == 1 && isset($_GET['su_id']) && $_GET['su_id'] !== '') {
            $filtros['su_id'] = (int)$_GET['su_id'];
        }

        $fecha_desde = isset($_GET['fecha_desde']) ? mainModel::limpiar_cadena($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? mainModel::limpiar_cadena($_GET['fecha_hasta']) : '';

        if (!empty($fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }
        if (!empty($fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        if (isset($_GET['select1']) && $_GET['select1'] !== '') {
            $filtros['caja_id'] = (int)$_GET['select1'];
        }

        if (isset($_GET['select2']) && $_GET['select2'] !== '') {
            $filtros['tipo'] = mainModel::limpiar_cadena($_GET['select2']);
        }

        if (isset($_GET['select3']) && $_GET['select3'] !== '') {
            $filtros['usuario'] = (int)$_GET['select3'];
        }

        try {
            $stmt = self::exportar_historial_caja_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $sucursal_nombre = isset($filtros['su_id']) ? 'Sucursal_' . $filtros['su_id'] : 'Todas_Sucursales';
            $filename = "Historial_Caja_{$sucursal_nombre}_{$fecha}.xls";

            $headers = array_keys($datos[0]);

            $info_superior = [
                'Fecha de Generación' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema',
                'Total de Registros' => count($datos)
            ];

            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                $info_superior['Periodo'] = date('d/m/Y', strtotime($fecha_desde)) . ' - ' . date('d/m/Y', strtotime($fecha_hasta));
            }

            mainModel::generar_excel_reporte([
                'titulo' => '💰 HISTORIAL DE MOVIMIENTOS DE CAJA - SAMFARM PHARMA',
                'datos' => $datos,
                'headers' => $headers,
                'nombre_archivo' => $filename,
                'formato_columnas' => [
                    'Fecha' => 'fecha-hora',
                    'Monto' => 'moneda'
                ],
                'columnas_totales' => ['Monto'],
                'info_superior' => $info_superior
            ]);

        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . $e->getMessage();
        }
    }

    public function exportar_historial_caja_pdf_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif ($rol_usuario == 1 && isset($_GET['su_id']) && $_GET['su_id'] !== '') {
            $filtros['su_id'] = (int)$_GET['su_id'];
        }

        $fecha_desde = isset($_GET['fecha_desde']) ? mainModel::limpiar_cadena($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? mainModel::limpiar_cadena($_GET['fecha_hasta']) : '';

        if (!empty($fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }
        if (!empty($fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        if (isset($_GET['select1']) && $_GET['select1'] !== '') {
            $filtros['caja_id'] = (int)$_GET['select1'];
        }

        if (isset($_GET['select2']) && $_GET['select2'] !== '') {
            $filtros['tipo'] = mainModel::limpiar_cadena($_GET['select2']);
        }

        if (isset($_GET['select3']) && $_GET['select3'] !== '') {
            $filtros['usuario'] = (int)$_GET['select3'];
        }

        try {
            $stmt = self::datos_historial_caja_model(0, 1000, $filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar en el periodo seleccionado.";
                return;
            }

            $resumen = self::obtener_resumen_periodo_model($filtros);

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
                ['text' => 'CAJA', 'width' => 25],
                ['text' => 'FECHA', 'width' => 22],
                ['text' => 'TIPO', 'width' => 18],
                ['text' => 'CONCEPTO', 'width' => 30],
                ['text' => 'REFERENCIA', 'width' => 30],
                ['text' => 'USUARIO', 'width' => 20],
                ['text' => 'MONTO', 'width' => 25]
            ];

            $rows = [];
            $total_ingresos = $resumen['total_ingresos'] ?? 0;
            $total_egresos = $resumen['total_egresos'] ?? 0;

            foreach ($datos as $row) {
                $tipo_text = strtoupper($row['mc_tipo']);
                $signo = in_array($tipo_text, ['INGRESO', 'VENTA']) ? '+' : '-';
                $color_monto = in_array($tipo_text, ['INGRESO', 'VENTA']) ? [39, 174, 96] : [231, 76, 60];

                $referencia = $row['mc_referencia_tipo'] ? strtoupper($row['mc_referencia_tipo']) . ' #' . $row['mc_referencia_id'] : 'N/A';
                $usuario = trim(($row['us_nombres'] ?? 'Sistema') . ' ' . ($row['us_apellido_paterno'] ?? ''));

                $cells = [
                    ['text' => substr($row['caja_nombre'], 0, 20), 'align' => 'L'],
                    ['text' => date('d/m/Y H:i', strtotime($row['mc_fecha'])), 'align' => 'C'],
                    ['text' => $tipo_text, 'align' => 'C'],
                    ['text' => substr($row['mc_concepto'] ?? '-', 0, 25), 'align' => 'L'],
                    ['text' => substr($referencia, 0, 25), 'align' => 'C'],
                    ['text' => substr($usuario, 0, 15), 'align' => 'L'],
                    ['text' => $signo . 'Bs ' . number_format($row['mc_monto'], 2), 'align' => 'R', 'color' => $color_monto]
                ];

                $rows[] = ['cells' => $cells];
            }

            $balance = $resumen['balance'] ?? 0;
            $color_balance = $balance >= 0 ? [39, 174, 96] : [231, 76, 60];

            $cells_total = array_fill(0, count($headers) - 1, ['text' => '', 'align' => 'C']);
            $cells_total[0] = ['text' => 'TOTAL GENERAL', 'align' => 'R'];
            $cells_total[count($headers) - 1] = [
                'text' => 'Bs ' . number_format($balance, 2),
                'align' => 'R',
                'color' => [255, 255, 255]
            ];

            $rows[] = [
                'cells' => $cells_total,
                'es_total' => true
            ];

            $resumen_pdf = [
                'Total Ingresos' => ['text' => '+Bs ' . number_format($total_ingresos, 2), 'color' => [39, 174, 96]],
                'Total Egresos' => ['text' => '-Bs ' . number_format($total_egresos, 2), 'color' => [231, 76, 60]],
                'Balance Final' => ['text' => 'Bs ' . number_format($balance, 2), 'color' => $color_balance]
            ];

            $datos_pdf = [
                'titulo' => 'HISTORIAL DE MOVIMIENTOS DE CAJA',
                'nombre_archivo' => 'Historial_Caja_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => $info_superior,
                'tabla' => [
                    'headers' => $headers,
                    'rows' => $rows
                ],
                'resumen' => $resumen_pdf
            ];

            self::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error exportando PDF historial: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    public function exportar_movimiento_individual_pdf_controller()
    {
        $mc_id = isset($_GET['mc_id']) ? (int)$_GET['mc_id'] : (isset($_POST['mc_id']) ? (int)$_POST['mc_id'] : 0);

        if ($mc_id <= 0) {
            echo "ID de movimiento inválido";
            return;
        }

        try {
            $stmt = self::obtener_movimiento_individual_model($mc_id);
            $movimiento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$movimiento) {
                echo "Movimiento no encontrado";
                return;
            }

            $tipo_text = strtoupper($movimiento['mc_tipo']);
            $signo = in_array($tipo_text, ['INGRESO', 'VENTA']) ? '+' : '-';
            $color_monto = in_array($tipo_text, ['INGRESO', 'VENTA']) ? [39, 174, 96] : [231, 76, 60];

            $referencia = $movimiento['mc_referencia_tipo'] ? strtoupper($movimiento['mc_referencia_tipo']) . ' #' . $movimiento['mc_referencia_id'] : 'N/A';

            $datos_pdf = [
                'titulo' => 'COMPROBANTE DE MOVIMIENTO DE CAJA',
                'nombre_archivo' => 'Movimiento_' . $mc_id . '.pdf',
                'info_superior' => [
                    'N° Movimiento' => $movimiento['mc_id'],
                    'Fecha' => date('d/m/Y H:i:s', strtotime($movimiento['mc_fecha'])),
                    'Caja' => $movimiento['caja_nombre'],
                    'Sucursal' => $movimiento['su_nombre']
                ],
                'tabla' => [
                    'headers' => [
                        ['text' => 'DETALLE', 'width' => 60, 'align' => 'L'],
                        ['text' => 'INFORMACION', 'width' => 115, 'align' => 'L']
                    ],
                    'rows' => [
                        ['cells' => [
                            ['text' => 'Tipo de Movimiento'],
                            ['text' => $tipo_text]
                        ]],
                        ['cells' => [
                            ['text' => 'Concepto'],
                            ['text' => $movimiento['mc_concepto'] ?? '-']
                        ]],
                        ['cells' => [
                            ['text' => 'Referencia'],
                            ['text' => $referencia]
                        ]],
                        ['cells' => [
                            ['text' => 'Usuario Responsable'],
                            ['text' => $movimiento['usuario_completo']]
                        ]],
                        [
                            'es_total' => true,
                            'cells' => [
                                ['text' => 'MONTO TOTAL'],
                                ['text' => $signo . 'Bs ' . number_format($movimiento['mc_monto'], 2), 'color' => [255, 255, 255]]
                            ]
                        ]
                    ]
                ]
            ];

            mainModel::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error exportando movimiento individual: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    public function obtener_referencia_movimiento_controller()
    {
        $tipo = isset($_POST['tipo']) ? mainModel::limpiar_cadena($_POST['tipo']) : '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        if (empty($tipo) || $id <= 0) {
            echo json_encode(['error' => 'Datos inválidos']);
            return;
        }

        try {
            $html = '';
            
            switch (strtolower($tipo)) {
                case 'venta':
                    $stmt = self::obtener_detalle_venta_model($id);
                    $venta = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($venta) {
                        $html = $this->generar_html_referencia_venta($venta);
                    } else {
                        $html = '<p style="color: #f44336; text-align: center;">No se encontró la venta</p>';
                    }
                    break;

                case 'compra':
                    $html = '<p style="color: #f44336; text-align: center;">Referencia de compra no disponible</p>';
                    break;

                case 'apertura':
                    $html = '<p style="color: #2196F3; text-align: center;"><strong>Apertura de Caja</strong></p>';
                    break;

                case 'cierre':
                    $html = '<p style="color: #FF9800; text-align: center;"><strong>Cierre de Caja</strong></p>';
                    break;

                case 'ajuste':
                    $html = '<p style="color: #9C27B0; text-align: center;"><strong>Ajuste Manual</strong></p>';
                    break;

                default:
                    $html = '<p style="color: #999; text-align: center;">Tipo de referencia desconocido</p>';
            }

            echo json_encode(['html' => $html]);
        } catch (Exception $e) {
            error_log("Error obteniendo referencia: " . $e->getMessage());
            echo json_encode(['error' => 'Error al obtener la referencia']);
        }
    }

    private function generar_html_referencia_venta($venta)
    {
        $html = '<div class="modal-group">';
        $html .= '<div class="row"><h3><ion-icon name="receipt-outline"></ion-icon> Información de Venta</h3></div>';
        
        $html .= '<div class="row">';
        $html .= '<div class="col"><label>Número de Venta:</label><p>' . htmlspecialchars($venta['v_numero'] ?? 'N/A') . '</p></div>';
        $html .= '<div class="col"><label>Fecha:</label><p>' . (isset($venta['v_fecha']) ? date('d/m/Y H:i', strtotime($venta['v_fecha'])) : 'N/A') . '</p></div>';
        $html .= '</div>';

        $html .= '<div class="row">';
        $html .= '<div class="col"><label>Cliente:</label><p>' . htmlspecialchars($venta['cliente_nombre'] ?? 'Mostrador') . '</p></div>';
        $html .= '<div class="col"><label>Total:</label><p><strong style="color: #4CAF50; font-size: 16px;">Bs. ' . number_format($venta['v_total'] ?? 0, 2) . '</strong></p></div>';
        $html .= '</div>';

        if (isset($venta['v_observacion']) && !empty($venta['v_observacion'])) {
            $html .= '<div class="row"><label>Observación:</label><p>' . htmlspecialchars($venta['v_observacion']) . '</p></div>';
        }

        $html .= '</div>';
        
        return $html;
    }

    protected static function obtener_detalle_venta_model($v_id)
    {
        try {
            $conexion = mainModel::conectar();
            $sql = "SELECT v.*, c.cliente_nombre FROM ventas v 
                    LEFT JOIN clientes c ON v.cliente_id = c.cliente_id 
                    WHERE v.v_id = :v_id LIMIT 1";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':v_id', $v_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en obtener_detalle_venta_model: " . $e->getMessage());
            return null;
        }
    }
}
