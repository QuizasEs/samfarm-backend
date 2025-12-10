<?php

if ($peticionAjax) {
    require_once '../models/proveedorModel.php';
} else {
    require_once './models/proveedorModel.php';
}

class proveedorController extends proveedorModel
{
    public function paginado_proveedor_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "", $f5 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3> Acceso Denegado</h3>
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

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f1 !== '') {
            $filtros['estado'] = $f1;
        }

        if ($f2 !== '') {
            $filtros['con_compras'] = $f2;
        }

        if ($f3 !== '') {
            $filtros['ultima_compra'] = $f3;
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        try {
            $conexion = mainModel::conectar();
            $datosStmt = self::datos_proveedores_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);
            $total = self::contar_proveedores_model($filtros);
        } catch (PDOException $e) {
            error_log(" ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>PROVEEDOR/RAZON SOCIAL</th>
                            <th>NIT</th>
                            <th>TELÉFONO</th>
                            <th>DIRECCIÓN</th>
                            <th>FECHA REGISTRO</th>
                            <th>TOTAL COMPRAS</th>
                            <th>ÚLTIMA COMPRA</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $nombre_completo = $row['pr_nombres'] ?? '';

                $estado_html = $row['pr_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge bloqueado"><ion-icon name="ban-outline"></ion-icon> Inactivo</span>';

                $ultima_compra = $row['ultima_compra']
                    ? date('d/m/Y', strtotime($row['ultima_compra']))
                    : '<span style="color:#999;">Nunca</span>';

                $dias_ultima = $row['dias_ultima_compra'];
                if ($dias_ultima !== null && $dias_ultima > 90) {
                    $ultima_compra .= '<br><small style="color:orange;"><ion-icon name="alert-outline"></ion-icon> Hace ' . $dias_ultima . ' días</small>';
                }

                $direccion = $row['pr_direccion'] ?? '-';
                if (strlen($direccion) > 30) {
                    $direccion = substr($direccion, 0, 30) . '...';
                }

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td><strong>' . htmlspecialchars($nombre_completo) . '</strong></td>
                        <td>' . htmlspecialchars($row['pr_nit'] ?? '-') . '</td>
                        <td>' . htmlspecialchars($row['pr_telefono'] ?? '-') . '</td>
                        <td>' . htmlspecialchars($direccion) . '</td>
                        <td>' . date('d/m/Y', strtotime($row['pr_creado_en'])) . '</td>
                        <td style="text-align:center;"><strong style="color:#1976D2;">' . number_format($row['total_compras']) . '</strong></td>
                        <td>' . $ultima_compra . '</td>
                        <td>' . $estado_html . '</td>
                        <td class="accion-buttons">
                            <a href="javascript:void(0)"
                            class="btn default"
                            title="Ver detalle"
                            onclick="ProveedoresModals.verDetalle(' . $row['pr_id'] . ', \'' . addslashes($nombre_completo) . '\')">
                                <ion-icon name="eye-outline"></ion-icon> Detalle
                            </a>
                            <a href="javascript:void(0)"
                            class="btn primary"
                            title="Editar proveedor"
                            onclick="ProveedoresModals.abrirEdicion(' . $row['pr_id'] . ')">
                                <ion-icon name="create-outline"></ion-icon> Editar
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="10" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="people-outline"></ion-icon> No hay registros
                    </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }

    public function detalle_proveedor_controller()
    {
        $pr_id = isset($_POST['pr_id']) ? (int)$_POST['pr_id'] : 0;

        if ($pr_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            $stmt = self::detalle_proveedor_model($pr_id);
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$proveedor) {
                return json_encode(['error' => 'Proveedor no encontrado']);
            }

            $ultimasComprasStmt = self::ultimas_compras_proveedor_model($pr_id, 5);
            $ultimasCompras = $ultimasComprasStmt->fetchAll(PDO::FETCH_ASSOC);

            $topMedicamentosStmt = self::top_medicamentos_proveedor_model($pr_id, 5);
            $topMedicamentos = $topMedicamentosStmt->fetchAll(PDO::FETCH_ASSOC);

            $nombre_completo = $proveedor['pr_nombres'] ?? '';

            $promedio = $proveedor['total_compras'] > 0
                ? $proveedor['monto_total_compras'] / $proveedor['total_compras']
                : 0;

            $response = [
                'nombre_completo' => $nombre_completo,
                'nit' => $proveedor['pr_nit'] ?? '-',
                'telefono' => $proveedor['pr_telefono'] ?? '-',
                'direccion' => $proveedor['pr_direccion'] ?? '-',
                'fecha_registro' => date('d/m/Y', strtotime($proveedor['pr_creado_en'])),
                'estado' => $proveedor['pr_estado'] == 1 ? 'Activo' : 'Inactivo',
                'total_compras' => (int)$proveedor['total_compras'],
                'monto_total' => (float)$proveedor['monto_total_compras'],
                'total_lotes' => (int)$proveedor['total_lotes'],
                'ultima_compra' => $proveedor['ultima_compra'] ? date('d/m/Y', strtotime($proveedor['ultima_compra'])) : 'Nunca',
                'promedio' => $promedio,
                'antiguedad' => (int)$proveedor['dias_antiguedad'],
                'ultimas_compras' => $ultimasCompras,
                'top_medicamentos' => $topMedicamentos
            ];

            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_proveedor_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar detalle']);
        }
    }

    public function exportar_proveedores_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            echo "Acceso denegado";
            return;
        }

        $filtros = [];

        if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $filtros['busqueda'] = mainModel::limpiar_cadena($_GET['busqueda']);
        }

        if (isset($_GET['select1']) && !empty($_GET['select1'])) {
            $filtros['estado'] = mainModel::limpiar_cadena($_GET['select1']);
        }

        if (isset($_GET['select2']) && !empty($_GET['select2'])) {
            $filtros['con_compras'] = mainModel::limpiar_cadena($_GET['select2']);
        }

        if (isset($_GET['select3']) && !empty($_GET['select3'])) {
            $filtros['ultima_compra'] = mainModel::limpiar_cadena($_GET['select3']);
        }

        if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = mainModel::limpiar_cadena($_GET['fecha_desde']);
        }

        if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = mainModel::limpiar_cadena($_GET['fecha_hasta']);
        }

        try {
            $stmt = self::exportar_proveedores_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $filename = "Proveedores_{$fecha}.xls";

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
                        transition: all 0.2s ease;
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
                    
                    .estado-inactivo {
                        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%) !important;
                        color: #c62828;
                        font-weight: 600;
                        text-align: center;
                        border-radius: 20px;
                        padding: 6px 12px;
                        margin: 2px;
                        border: 1px solid #ef5350;
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
                    
                    tbody tr:not(.total-row) {
                        border-left: 3px solid transparent;
                        transition: border-left 0.3s ease;
                    }
                    
                    tbody tr:not(.total-row):hover {
                        border-left: 3px solid #3498db;
                    }
                </style>
            </head>
            <body>';

            echo '<div class="container">
                    <div class="header">
                        👥 REPORTE DE PROVEEDORES - SAMFARM PHARMA
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

            $total_compras = 0;
            $monto_total = 0;
            $total_lotes = 0;

            foreach ($datos as $row) {
                echo '<tr>';

                foreach ($headers as $key) {
                    $valor = $row[$key];

                    if ($key === 'Total Compras' || $key === 'Lotes Generados') {
                        echo '<td class="numero">' . number_format($valor, 0, ',', '.') . '</td>';

                        if ($key === 'Total Compras') $total_compras += $valor;
                        if ($key === 'Lotes Generados') $total_lotes += $valor;
                    } elseif ($key === 'Monto Total (Bs)') {
                        echo '<td class="moneda">Bs ' . number_format($valor, 2, ',', '.') . '</td>';
                        $monto_total += $valor;
                    } elseif ($key === 'Estado') {
                        $clase = 'estado-' . strtolower($valor);
                        $icono = $valor === 'ACTIVO' ? '✅' : '❌';
                        echo '<td class="' . $clase . '">' . $icono . ' ' . $valor . '</td>';
                    } else {
                        echo '<td>' . htmlspecialchars($valor ?? '-') . '</td>';
                    }
                }

                echo '</tr>';
            }

            echo '<tr class="total-row">
                    <td colspan="5" style="text-align: right; padding-right: 20px;">📊 TOTALES GENERALES:</td>
                    <td class="numero">' . number_format($total_compras, 0, ',', '.') . '</td>
                    <td class="moneda">Bs ' . number_format($monto_total, 2, ',', '.') . '</td>
                    <td class="numero">' . number_format($total_lotes, 0, ',', '.') . '</td>
                    <td colspan="2"></td>
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
    /* controlador para registrar o editar  */
    public function registrar_proveedor_controller()
    {
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_pr'] ?? '');
        $nit = mainModel::limpiar_cadena($_POST['Nit_pr'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_pr'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_pr'] ?? '');
        /* verificar que los campos ablgatorios no esten vacios */
        if (empty($nombres) || empty($nit)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'Debe ingresar al menos el nombre y el NIT',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        /* verificar la integridad de los datos  */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $nombres)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NOMBRE no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (mainModel::verificar_datos("[0-9]{6,30}", $nit)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NIT no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (!empty($telefono)) {
            if (mainModel::verificar_datos("[0-9]{6,30}", $telefono)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El TELEFONO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }
        /*  el nit no deve repetirse */
        $verificar_nit = self::verificar_nit_duplicado_model($nit);
        if ($verificar_nit->rowCount() > 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'NIT duplicado',
                'texto' => 'Ya existe un proveedor registrado con este NIT',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $datos = [
            'nombres' => $nombres,
            'nit' => $nit,
            'telefono' => $telefono,
            'direccion' => $direccion
        ];

        try {
            $registrar = self::registrar_proveedor_model($datos);

            if ($registrar->rowCount() == 1) {
                $alerta = [
                    'Alerta' => 'recargar',
                    'Titulo' => 'Proveedor registrado',
                    'texto' => 'El proveedor se registró correctamente',
                    'Tipo' => 'success'
                ];
            } else {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo registrar el proveedor',
                    'Tipo' => 'error'
                ];
            }
        } catch (Exception $e) {
            error_log("Error registrando proveedor: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Ocurrió un error al registrar el proveedor',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function obtener_proveedor_controller()
    {
        $pr_id = isset($_POST['pr_id']) ? (int)$_POST['pr_id'] : 0;

        if ($pr_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = self::obtener_proveedor_por_id_model($pr_id);
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$proveedor) {
                return json_encode(['error' => 'Proveedor no encontrado']);
            }

            return json_encode($proveedor, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error obteniendo proveedor: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar datos']);
        }
    }

    public function actualizar_proveedor_controller()
    {
        $pr_id = mainModel::limpiar_cadena($_POST['PrId_up'] ?? '');
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_pr_up'] ?? '');
        $nit = mainModel::limpiar_cadena($_POST['Nit_pr_up'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_pr_up'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_pr_up'] ?? '');

        if (empty($pr_id) || empty($nombres) || empty($nit)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'Debe completar los campos obligatorios',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* verificar la integridad de los datos  */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $nombres)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NOMBRE no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (mainModel::verificar_datos("[0-9]{6,30}", $nit)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NIT no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (!empty($telefono)) {
            if (mainModel::verificar_datos("[0-9]{6,30}", $telefono)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El TELEFONO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }

        $verificar = self::obtener_proveedor_por_id_model($pr_id);
        if ($verificar->rowCount() == 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'El proveedor no existe',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $verificar_nit = self::verificar_nit_duplicado_model($nit, $pr_id);
        if ($verificar_nit->rowCount() > 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'NIT duplicado',
                'texto' => 'Ya existe otro proveedor con este NIT',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $datos = [
            'pr_id' => $pr_id,
            'nombres' => $nombres,
            'nit' => $nit,
            'telefono' => $telefono,
            'direccion' => $direccion
        ];

        try {
            $actualizar = self::actualizar_proveedor_model($datos);

            if ($actualizar->rowCount() >= 0) {
                $alerta = [
                    'Alerta' => 'recargar',
                    'Titulo' => 'Proveedor actualizado',
                    'texto' => 'Los datos se actualizaron correctamente',
                    'Tipo' => 'success'
                ];
            } else {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo actualizar el proveedor',
                    'Tipo' => 'error'
                ];
            }
        } catch (Exception $e) {
            error_log("Error actualizando proveedor: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Ocurrió un error al actualizar',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }
}
