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
                                <ion-icon name="open-outline"></ion-icon>
                            </a>
                            <a href="javascript:void(0)" 
                            class="btn success" 
                            title="Exportar PDF"
                            onclick="CajaHistorial.exportarMovimiento(' . $row['mc_id'] . ')">
                                <ion-icon name="document-text-outline"></ion-icon>
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
        } elseif ($rol_usuario == 1 && isset($_GET['select4']) && $_GET['select4'] !== '') {
            $filtros['su_id'] = (int)$_GET['select4'];
        }

        $fecha_desde = isset($_GET['fecha_desde']) ? mainModel::limpiar_cadena($_GET['fecha_desde']) : date('Y-m-d');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? mainModel::limpiar_cadena($_GET['fecha_hasta']) : date('Y-m-d');

        $filtros['fecha_desde'] = $fecha_desde;
        $filtros['fecha_hasta'] = $fecha_hasta;

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

            $resumen = self::obtener_resumen_periodo_model($filtros);

            $fecha = date('Y-m-d_His');
            $sucursal_nombre = isset($filtros['su_id']) ? 'Sucursal_' . $filtros['su_id'] : 'Todas_Sucursales';
            $filename = "Historial_Caja_{$sucursal_nombre}_{$fecha}.xls";

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
            
            .moneda-ingreso {
                text-align: right;
                font-weight: 700;
                font-family: "Courier New", monospace;
                color: #27ae60;
                background: linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%);
                border-left: 3px solid #27ae60;
            }
            
            .moneda-egreso {
                text-align: right;
                font-weight: 700;
                font-family: "Courier New", monospace;
                color: #c62828;
                background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
                border-left: 3px solid #c62828;
            }
            
            .tipo-ingreso {
                background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%) !important;
                color: #2e7d32;
                font-weight: 600;
                text-align: center;
                border-radius: 20px;
                padding: 6px 12px;
                border: 1px solid #4caf50;
            }
            
            .tipo-egreso {
                background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%) !important;
                color: #c62828;
                font-weight: 600;
                text-align: center;
                border-radius: 20px;
                padding: 6px 12px;
                border: 1px solid #ef5350;
            }
            
            .tipo-venta {
                background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
                color: #1565c0;
                font-weight: 600;
                text-align: center;
                border-radius: 20px;
                padding: 6px 12px;
                border: 1px solid #2196f3;
            }
            
            .tipo-compra {
                background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%) !important;
                color: #ef6c00;
                font-weight: 600;
                text-align: center;
                border-radius: 20px;
                padding: 6px 12px;
                border: 1px solid #ff9800;
            }
            
            .tipo-ajuste {
                background: linear-gradient(135deg, #f5f5f5 0%, #eeeeee 100%) !important;
                color: #757575;
                font-weight: 600;
                text-align: center;
                border-radius: 20px;
                padding: 6px 12px;
                border: 1px solid #bdbdbd;
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
            
            .resumen-box {
                background: linear-gradient(135deg, #ecf0f1 0%, #bdc3c7 100%);
                padding: 20px;
                margin: 20px;
                border-radius: 8px;
                border-left: 5px solid #3498db;
            }
            
            .resumen-item {
                padding: 10px;
                font-size: 12pt;
                font-weight: bold;
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

            echo '<div class="container">
                    <div class="header">
                        💰 HISTORIAL DE MOVIMIENTOS DE CAJA - SAMFARM PHARMA
                    </div>';

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
                            <strong>📆 Periodo</strong>
                            ' . date('d/m/Y', strtotime($fecha_desde)) . ' - ' . date('d/m/Y', strtotime($fecha_hasta)) . '
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

            $total_ingresos = 0;
            $total_egresos = 0;

            foreach ($datos as $row) {
                echo '<tr>';

                foreach ($headers as $key) {
                    $valor = $row[$key];

                    if ($key === 'Monto') {
                        $tipo = $row['Tipo'];
                        $clase = in_array($tipo, ['INGRESO', 'VENTA']) ? 'moneda-ingreso' : 'moneda-egreso';
                        $signo = in_array($tipo, ['INGRESO', 'VENTA']) ? '+' : '-';
                        echo '<td class="' . $clase . '">' . $signo . 'Bs ' . number_format($valor, 2, ',', '.') . '</td>';

                        if (in_array($tipo, ['INGRESO', 'VENTA'])) {
                            $total_ingresos += $valor;
                        } else {
                            $total_egresos += $valor;
                        }
                    } elseif ($key === 'Tipo') {
                        $clase_tipo = 'tipo-' . strtolower($valor);
                        $iconos = [
                            'INGRESO' => '⬇️',
                            'EGRESO' => '⬆️',
                            'VENTA' => '🛒',
                            'COMPRA' => '📦',
                            'AJUSTE' => '📝'
                        ];
                        $icono = $iconos[$valor] ?? '📌';
                        echo '<td class="' . $clase_tipo . '">' . $icono . ' ' . $valor . '</td>';
                    } elseif ($key === 'Fecha') {
                        echo '<td>' . date('d/m/Y H:i', strtotime($valor)) . '</td>';
                    } else {
                        echo '<td>' . htmlspecialchars($valor ?? '-') . '</td>';
                    }
                }

                echo '</tr>';
            }

            $balance = $total_ingresos - $total_egresos;
            $color_balance = $balance >= 0 ? '#27ae60' : '#c62828';

            echo '<tr class="total-row">
                    <td colspan="' . (count($headers) - 1) . '" style="text-align: right; padding-right: 20px;">📊 BALANCE TOTAL:</td>
                    <td class="numero" style="color: ' . $color_balance . ';">Bs ' . number_format($balance, 2, ',', '.') . '</td>
                </tr>';

            echo '</tbody></table>';

            echo '<div class="resumen-box">
                    <h3 style="margin-top: 0; color: #2c3e50;">📈 RESUMEN DEL PERIODO</h3>
                    <div class="resumen-item" style="color: #27ae60;">
                        ⬇️ Total Ingresos: <span style="float: right;">+Bs ' . number_format($total_ingresos, 2, ',', '.') . '</span>
                    </div>
                    <div class="resumen-item" style="color: #c62828;">
                        ⬆️ Total Egresos: <span style="float: right;">-Bs ' . number_format($total_egresos, 2, ',', '.') . '</span>
                    </div>
                    <div class="resumen-item" style="color: ' . $color_balance . '; font-size: 14pt; border-top: 2px solid #34495e; margin-top: 10px; padding-top: 10px;">
                        💰 Balance Final: <span style="float: right;">Bs ' . number_format($balance, 2, ',', '.') . '</span>
                    </div>
                </div>';

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

    public function exportar_historial_caja_pdf_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif ($rol_usuario == 1 && isset($_GET['select4']) && $_GET['select4'] !== '') {
            $filtros['su_id'] = (int)$_GET['select4'];
        }

        $fecha_desde = isset($_GET['fecha_desde']) ? mainModel::limpiar_cadena($_GET['fecha_desde']) : date('Y-m-d');
        $fecha_hasta = isset($_GET['fecha_hasta']) ? mainModel::limpiar_cadena($_GET['fecha_hasta']) : date('Y-m-d');

        $filtros['fecha_desde'] = $fecha_desde;
        $filtros['fecha_hasta'] = $fecha_hasta;

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
                echo "No hay datos para exportar";
                return;
            }

            $resumen = self::obtener_resumen_periodo_model($filtros);

            $rows = [];
            foreach ($datos as $row) {
                $tipo_text = strtoupper($row['mc_tipo']);
                $signo = in_array($tipo_text, ['INGRESO', 'VENTA']) ? '+' : '-';
                $color_monto = in_array($tipo_text, ['INGRESO', 'VENTA']) ? [39, 174, 96] : [231, 76, 60];

                $referencia = $row['mc_referencia_tipo'] ? strtoupper($row['mc_referencia_tipo']) . ' #' . $row['mc_referencia_id'] : 'N/A';
                $usuario = trim(($row['us_nombres'] ?? 'Sistema') . ' ' . ($row['us_apellido_paterno'] ?? ''));

                $rows[] = [
                    'cells' => [
                        ['text' => $row['caja_nombre']],
                        ['text' => date('d/m/Y H:i', strtotime($row['mc_fecha']))],
                        ['text' => $tipo_text],
                        ['text' => substr($row['mc_concepto'] ?? '-', 0, 30)],
                        ['text' => $referencia],
                        ['text' => substr($usuario, 0, 20)],
                        ['text' => $signo . 'Bs ' . number_format($row['mc_monto'], 2), 'color' => $color_monto]
                    ]
                ];
            }

            $balance = $resumen['balance'];
            $color_balance = $balance >= 0 ? [39, 174, 96] : [231, 76, 60];

            $rows[] = [
                'es_total' => true,
                'cells' => [
                    ['text' => 'TOTALES'],
                    ['text' => ''],
                    ['text' => ''],
                    ['text' => ''],
                    ['text' => ''],
                    ['text' => ''],
                    ['text' => 'Bs ' . number_format($balance, 2), 'color' => [255, 255, 255]]
                ]
            ];

            $datos_pdf = [
                'titulo' => 'HISTORIAL DE MOVIMIENTOS DE CAJA',
                'nombre_archivo' => 'Historial_Caja_' . date('Y-m-d') . '.pdf',
                'info_superior' => [
                    'Periodo' => date('d/m/Y', strtotime($fecha_desde)) . ' - ' . date('d/m/Y', strtotime($fecha_hasta)),
                    'Registros' => count($datos),
                    'Generado por' => $_SESSION['nombre_smp'] ?? 'Sistema'
                ],
                'tabla' => [
                    'headers' => [
                        ['text' => 'CAJA', 'width' => 30, 'align' => 'L'],
                        ['text' => 'FECHA', 'width' => 30, 'align' => 'C'],
                        ['text' => 'TIPO', 'width' => 20, 'align' => 'C'],
                        ['text' => 'CONCEPTO', 'width' => 40, 'align' => 'L'],
                        ['text' => 'REFERENCIA', 'width' => 25, 'align' => 'C'],
                        ['text' => 'USUARIO', 'width' => 25, 'align' => 'L'],
                        ['text' => 'MONTO', 'width' => 25, 'align' => 'R']
                    ],
                    'rows' => $rows
                ],
                'resumen' => [
                    'Total Ingresos' => [
                        'text' => '+Bs ' . number_format($resumen['total_ingresos'], 2),
                        'color' => [39, 174, 96]
                    ],
                    'Total Egresos' => [
                        'text' => '-Bs ' . number_format($resumen['total_egresos'], 2),
                        'color' => [231, 76, 60]
                    ],
                    'Balance Final' => [
                        'text' => 'Bs ' . number_format($balance, 2),
                        'color' => $color_balance
                    ]
                ]
            ];

            mainModel::generar_pdf_reporte_fpdf($datos_pdf);
        } catch (Exception $e) {
            error_log("Error exportando PDF historial: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    public function exportar_movimiento_individual_pdf_controller()
    {
        $mc_id = isset($_GET['mc_id']) ? (int)$_GET['mc_id'] : 0;

        if ($mc_id <= 0) {
            echo "ID de movimiento invalido";
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
}
