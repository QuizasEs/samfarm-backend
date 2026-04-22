<?php

if ($peticionAjax) {
    require_once '../models/inventarioModel.php';
    require_once '../models/loteModel.php';
} else {
    require_once './models/inventarioModel.php';
    require_once './models/loteModel.php';
}
class inventarioController extends inventarioModel
{

    public function paginado_inventario_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "")
    {
        /* ===== VALIDAR PERMISOS ===== */
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        // Vendedores NO pueden acceder al inventario
        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                            <h3> Acceso Denegado</h3>
                            <p>No tiene permisos para ver el inventario</p>
                        </div>';
        }

        /* ===== LIMPIAR PARÁMETROS ===== */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1); // Proveedor
        $f2 = mainModel::limpiar_cadena($f2); // Estado
        $f3 = mainModel::limpiar_cadena($f3); // Sucursal
        $f4 = mainModel::limpiar_cadena($f4); // Forma

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        /* ===== CONSTRUIR FILTROS ===== */
        $filtros = [];

        // 🔒 Filtro por sucursal según rol
        if ($rol_usuario == 1) {
            // Admin: puede ver todas o filtrar
            if ($f3 !== '') {
                $filtros['su_id'] = (int)$f3;
            }
        } elseif ($rol_usuario == 2) {
            // Gerente: solo su sucursal
            $filtros['su_id'] = $sucursal_usuario;
        }

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f2 !== '') {
            $estados_validos = ['agotado', 'bajo', 'normal', 'exceso', 'sin_definir'];
            if (in_array($f2, $estados_validos)) {
                $filtros['estado'] = $f2;
            }
        }

        if ($f4 !== '' && is_numeric($f4)) {
            $filtros['forma'] = (int)$f4;
        }

        /* ===== RECALCULAR VALORADO ANTES DE MOSTRAR ===== */
        try {
            inventarioModel::recalcular_valorado_inventario_model();
            error_log(" Valorado de inventario recalculado");
        } catch (Exception $e) {
            error_log("⚠️ Error en recalcular_valorado_inventario_model: " . $e->getMessage());
        }

        /* ===== CONSULTAR DATOS ===== */
        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_inventario_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_inventarios_model($filtros);
        } catch (PDOException $e) {
            error_log("  ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        // Determinar si mostrar columna sucursal
        $mostrar_columna_sucursal = ($rol_usuario == 1 && empty($f3));
        $colspan_total = 5;

        /* ===== CONSTRUIR TABLA ===== */
        $tabla .= '
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>MEDICAMENTO</th>' .
            ($mostrar_columna_sucursal ? '' : '') .
            '<th>STOCK</th>
                                <th>ESTADO</th>
                                <th>DETALLES</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
            ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                // Badge de estado
                $estado_html = self::generar_badge_estado($row['estado_stock'], $row['inv_total_unidades'], $row['inv_minimo']);

                // Calcular días para vencer
                $dias_vencer = '';
                if ($row['fecha_vencimiento_proximo']) {
                    $fecha_venc = new DateTime($row['fecha_vencimiento_proximo']);
                    $hoy = new DateTime();
                    $diff = $hoy->diff($fecha_venc);
                    $dias = (int)$diff->format('%R%a');

                    if ($dias < 0) {
                        $dias_vencer = '<span style="color:red;font-weight:bold;">VENCIDO</span>';
                    } elseif ($dias <= 30) {
                        $dias_vencer = '<span style="color:orange;font-weight:bold;">' . $dias . ' días</span>';
                    } elseif ($dias <= 90) {
                        $dias_vencer = '<span style="color:#ff9800;">' . $dias . ' días</span>';
                    } else {
                        $dias_vencer = $dias . ' días';
                    }
                } else {
                    $dias_vencer = '<span style="color:#999;">N/A</span>';
                }

                $tabla .= '
                        <tr class="tr-click" onclick="InventarioModals.verDetalle(' . $row['inv_id'] . ', ' . $row['med_id'] . ', ' . $row['su_id'] . ', \'' . addslashes($row['med_nombre_quimico']) . '\')">
                            <td>
                                <div class="td-main"><strong>' . htmlspecialchars($row['med_nombre_quimico']) . '</strong></div>
                                <div class="td-sub">' . htmlspecialchars($row['med_principio_activo']) .
                    ($mostrar_columna_sucursal ? ' · ' . htmlspecialchars($row['sucursal_nombre']) : '') . '</div>
                            </td>
                            <td>
                                <div class="td-main"><strong style="color:#1976D2;">' . number_format($row['inv_total_unidades']) . '</strong> unidades</div>
                                <div class="td-sub">' . number_format($row['inv_total_cajas']) . ' cajas · Bs. ' . number_format($row['inv_total_valorado'], 2) . '</div>
                            </td>
                            <td>
                                <div class="td-main">' . $estado_html . '</div>
                                <div class="td-sub">Lotes: ' . $row['lotes_activos'] . '</div>
                            </td>
                            <td>
                                <div class="td-main">Vence: ' . $dias_vencer . '</div>
                                <div class="td-sub">Min: ' . number_format($row['inv_minimo'] ?? 0) . ' / Max: ' . ($row['inv_maximo'] !== null ? number_format($row['inv_maximo']) : 'Sin límite') . '</div>
                            </td>
                            <td class="buttons">
                                ' . ($rol_usuario == 1 ? '
                                <a href="javascript:void(0)"
                                class="btn btn-douc"
                                title="Configurar minimo y maximo"
                                onclick="event.stopPropagation(); InventarioModals.abrirConfiguracion(' . $row['inv_id'] . ', ' . $row['med_id'] . ', ' . $row['su_id'] . ', \'' . addslashes($row['med_nombre_quimico']) . '\', ' . ($row['inv_minimo'] ?? 0) . ', ' . ($row['inv_maximo'] ?? 'null') . ')">
                                    <ion-icon name="settings-outline"></ion-icon>
                                </a>
                                ' : '') . '
                                <a href="javascript:void(0)"
                                class="btn btn-out"
                                title="Ver historial"
                                onclick="event.stopPropagation(); InventarioModals.verHistorial(' . $row['med_id'] . ', ' . $row['su_id'] . ', \'' . addslashes($row['med_nombre_quimico']) . '\')">
                                    <ion-icon name="time-outline"></ion-icon>
                                </a>
                            </td>
                        </tr>
                    ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="' . $colspan_total . '" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="cube-outline"></ion-icon> No hay registros
                        </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }


    private static function generar_badge_estado($estado, $unidades, $minimo)
    {
        if ($unidades == 0) {
            return '<span class="badge bdan"><ion-icon name="close-circle-outline"></ion-icon> AGOTADO</span>';
        }

        if ($minimo == 0 || $minimo === null) {
            return '<span class="badge bgry"><ion-icon name="help-circle-outline"></ion-icon> SIN DEFINIR</span>';
        }

        if ($unidades < $minimo) {
            return '<span class="badge bdan"><ion-icon name="alert-circle-outline"></ion-icon> CRÍTICO</span>';
        }

        if ($unidades < ($minimo * 1.5)) {
            return '<span class="badge bwar"><ion-icon name="warning-outline"></ion-icon> BAJO</span>';
        }

        return '<span class="badge bgr"><ion-icon name="checkmark-circle-outline"></ion-icon> NORMAL</span>';
    }

    public function detalle_inventario_controller()
    {
        $inv_id = isset($_POST['inv_id']) ? (int)$_POST['inv_id'] : 0;
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($inv_id <= 0 || $med_id <= 0 || $su_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            // Obtener datos del inventario
            $stmt = self::detalle_inventario_con_lotes_model($inv_id);
            $inv = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$inv) {
                return json_encode(['error' => 'Inventario no encontrado']);
            }

            // Obtener lotes activos
            $lotesStmt = self::lotes_por_inventario_model($med_id, $su_id);
            $lotes = $lotesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear lotes
            $lotesFormateados = array_map(function ($lote) {
                return [
                    'numero_lote' => $lote['lm_numero_lote'],
                    'unidades' => $lote['lm_cant_actual_unidades'],
                    'precio' => $lote['lm_precio_venta'],
                    'vencimiento' => $lote['lm_fecha_vencimiento'],
                    'estado' => ucfirst($lote['lm_estado']),
                    'dias_vencer' => $lote['dias_para_vencer'],
                    'proveedor' => $lote['proveedor'] ?? 'Sin proveedor'
                ];
            }, $lotes);

            // Generar badge de estado
            $estado_html = self::generar_badge_estado(
                $inv['inv_total_unidades'] == 0 ? 'agotado' : 'normal',
                $inv['inv_total_unidades'],
                $inv['inv_minimo']
            );

            $response = [
                'proveedor' => $inv['proveedor'] ?? 'Sin proveedor',
                'sucursal' => $inv['sucursal_nombre'],
                'cajas' => $inv['inv_total_cajas'],
                'unidades' => $inv['inv_total_unidades'],
                'valorado' => $inv['inv_total_valorado'],
                'estado_html' => $estado_html,
                'lotes' => $lotesFormateados
            ];

            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_inventario_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar detalle']);
        }
    }


    public function lotes_transferibles_controller()
    {
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($med_id <= 0 || $su_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            $stmt = self::lotes_por_inventario_model($med_id, $su_id);
            $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Filtrar solo lotes activos con stock
            $lotesDisponibles = array_filter($lotes, function ($lote) {
                return $lote['lm_estado'] === 'activo' && $lote['lm_cant_actual_unidades'] > 0;
            });

            $lotesFormateados = array_map(function ($lote) {
                return [
                    'lm_id' => $lote['lm_id'],
                    'numero_lote' => $lote['lm_numero_lote'],
                    'stock' => $lote['lm_cant_actual_unidades']
                ];
            }, array_values($lotesDisponibles));

            return json_encode(['lotes' => $lotesFormateados], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en lotes_transferibles_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar lotes']);
        }
    }


    public function historial_movimientos_controller()
    {
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($med_id <= 0 || $su_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            $stmt = self::historial_movimientos_inventario_model($med_id, $su_id, 30);
            $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $movimientosFormateados = array_map(function ($mov) {
                return [
                    'fecha' => date('d/m/Y H:i', strtotime($mov['mi_creado_en'])),
                    'tipo' => $mov['mi_tipo'],
                    'cantidad' => $mov['mi_cantidad'],
                    'unidad' => $mov['mi_unidad'],
                    'lote' => $mov['lm_numero_lote'],
                    'usuario' => trim(($mov['us_nombres'] ?? '') . ' ' . ($mov['us_apellido_paterno'] ?? '')),
                    'motivo' => $mov['mi_motivo']
                ];
            }, $movimientos);

            return json_encode(['movimientos' => $movimientosFormateados], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en historial_movimientos_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar historial']);
        }
    }


    public function exportar_inventario_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        // 🔒 Filtro por sucursal según rol
        if ($rol_usuario == 1) {
            // Admin: puede ver todas o filtrar
            $f3 = isset($_GET['select3']) ? $_GET['select3'] : '';
            if ($f3 !== '') {
                $filtros['su_id'] = (int)$f3;
            }
        } elseif ($rol_usuario == 2) {
            // Gerente: solo su sucursal
            $filtros['su_id'] = $sucursal_usuario;
        }

        // Demás filtros
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
        $f1 = isset($_GET['select1']) ? $_GET['select1'] : '';
        $f2 = isset($_GET['select2']) ? $_GET['select2'] : '';
        $f4 = isset($_GET['select4']) ? $_GET['select4'] : '';

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f1 !== '' && is_numeric($f1)) {
            $filtros['proveedor'] = (int)$f1;
        }

        if ($f2 !== '') {
            $estados_validos = ['agotado', 'bajo', 'normal', 'exceso', 'sin_definir', 'critico'];
            if (in_array($f2, $estados_validos)) {
                $filtros['estado'] = $f2;
            }
        }

        if ($f4 !== '' && is_numeric($f4)) {
            $filtros['forma'] = (int)$f4;
        }

        // Extraer su_id de filtros para usar en el reporte
        $su_id = $filtros['su_id'] ?? null;

        try {
            // Obtener datos con filtros
            $stmt = self::exportar_inventario_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            // Nombre del archivo
            $fecha = date('Y-m-d_His');
            $sucursal_nombre = $su_id ? 'Sucursal_' . $su_id : 'Todas_Sucursales';
            $filename = "Inventario_{$sucursal_nombre}_{$fecha}.xls";

            $headers = array_keys($datos[0]);

            $info_superior = [
                'Fecha de Generación' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema',
                'Sucursal' => ($su_id ? 'Sucursal ID ' . $su_id : 'Todas las Sucursales'),
                'Total de Registros' => count($datos)
            ];

            mainModel::generar_excel_reporte([
                'titulo' => 'REPORTE DE INVENTARIO',
                'datos' => $datos,
                'headers' => $headers,
                'nombre_archivo' => $filename,
                'formato_columnas' => [
                    'Valorado (Bs)' => 'moneda',
                    'Cajas' => 'numero',
                    'Unidades' => 'numero'
                ],
                'columnas_totales' => ['Valorado (Bs)'],
                'info_superior' => $info_superior
            ]);

        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . htmlspecialchars($e->getMessage());
        }
    }

    public function guardar_configuracion_inventario_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        // Solo administradores pueden configurar mínimos y máximos
        if ($rol_usuario != 1) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Acceso denegado',
                'texto' => 'Solo los administradores pueden configurar mínimos y máximos de inventario',
                'Tipo' => 'error'
            ]);
        }

        $inv_id = isset($_POST['inv_id']) ? (int)$_POST['inv_id'] : 0;
        $inv_minimo = isset($_POST['inv_minimo']) ? (int)$_POST['inv_minimo'] : 0;
        $inv_maximo = isset($_POST['inv_maximo']) && $_POST['inv_maximo'] !== '' ? (int)$_POST['inv_maximo'] : null;

        if ($inv_id <= 0) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Inventario no válido',
                'Tipo' => 'error'
            ]);
        }

        if ($inv_minimo < 0) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Validación',
                'texto' => 'La cantidad mínima no puede ser negativa',
                'Tipo' => 'error'
            ]);
        }

        if ($inv_maximo !== null && $inv_maximo < $inv_minimo) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Validación',
                'texto' => 'La cantidad máxima debe ser mayor o igual a la mínima',
                'Tipo' => 'error'
            ]);
        }

        try {
            $resultado = self::actualizar_configuracion_inventario_model($inv_id, $inv_minimo, $inv_maximo);

            if ($resultado) {
                return json_encode([
                    'Alerta' => 'recargar',
                    'Titulo' => 'Configuración actualizada',
                    'texto' => 'Los valores de mínimo y máximo se guardaron correctamente',
                    'Tipo' => 'success'
                ]);
            } else {
                return json_encode([
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo guardar la configuración',
                    'Tipo' => 'error'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en guardar_configuracion_inventario_controller: " . $e->getMessage());
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Error al guardar configuración',
                'Tipo' => 'error'
            ]);
        }
    }

    public function exportar_pdf_inventario_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 1) {
            $f3 = isset($_GET['select3']) ? $_GET['select3'] : '';
            if ($f3 !== '') {
                $filtros['su_id'] = (int)$f3;
            }
        } elseif ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        }

        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
        $f1 = isset($_GET['select1']) ? $_GET['select1'] : '';
        $f2 = isset($_GET['select2']) ? $_GET['select2'] : '';
        $f4 = isset($_GET['select4']) ? $_GET['select4'] : '';

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f1 !== '' && is_numeric($f1)) {
            $filtros['proveedor'] = (int)$f1;
        }

        if ($f2 !== '') {
            $estados_validos = ['agotado', 'bajo', 'normal', 'exceso', 'sin_definir', 'critico'];
            if (in_array($f2, $estados_validos)) {
                $filtros['estado'] = $f2;
            }
        }

        if ($f4 !== '' && is_numeric($f4)) {
            $filtros['forma'] = (int)$f4;
        }

        $su_id = $filtros['su_id'] ?? null;

        try {
            $stmt = self::exportar_inventario_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar con los filtros aplicados.";
                return;
            }

            $periodo = '';
            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $periodo = date('d/m/Y', strtotime($filtros['fecha_desde'])) . ' al ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
            } else {
                $periodo = 'Todo el período';
            }

            $info_superior = [
                'Sucursal' => ($su_id ? 'Sucursal ID ' . $su_id : 'Todas las Sucursales'),
                'Total de Medicamentos' => count($datos),
                'Generado' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema'
            ];

            $headers = [];
            if ($rol_usuario == 1) {
                $headers = [
                    ['text' => 'N°', 'width' => 10],
                    ['text' => 'MEDICAMENTO', 'width' => 50],
                    ['text' => 'LABORATORIO', 'width' => 25],
                    ['text' => 'SUCURSAL', 'width' => 25],
                    ['text' => 'CAJAS', 'width' => 15],
                    ['text' => 'UNIDADES', 'width' => 20],
                    ['text' => 'VALORADO', 'width' => 30],
                    ['text' => 'ESTADO', 'width' => 20]
                ];
            } else {
                $headers = [
                    ['text' => 'N°', 'width' => 10],
                    ['text' => 'MEDICAMENTO', 'width' => 55],
                    ['text' => 'LABORATORIO', 'width' => 30],
                    ['text' => 'CAJAS', 'width' => 15],
                    ['text' => 'UNIDADES', 'width' => 20],
                    ['text' => 'VALORADO', 'width' => 30],
                    ['text' => 'ESTADO', 'width' => 20]
                ];
            }

            $rows = [];
            foreach ($datos as $index => $row) {
                $estado_texto = $row['Estado'] ?? 'N/A';

                if ($rol_usuario == 1) {
                    $cells = [
                        ['text' => ($index + 1), 'align' => 'C'],
                        ['text' => substr($row['Medicamento'] ?? 'N/A', 0, 35), 'align' => 'L'],
                        ['text' => substr($row['Proveedor'] ?? 'N/A', 0, 20), 'align' => 'L'],
                        ['text' => substr($row['Sucursal'] ?? 'N/A', 0, 20), 'align' => 'L'],
                        ['text' => $row['Cajas'], 'align' => 'C'],
                        ['text' => number_format($row['Unidades']), 'align' => 'C'],
                        ['text' => 'Bs. ' . number_format($row['Valorado (Bs)'], 2), 'align' => 'R'],
                        ['text' => $estado_texto, 'align' => 'C']
                    ];
                } else {
                    $cells = [
                        ['text' => ($index + 1), 'align' => 'C'],
                        ['text' => substr($row['Medicamento'] ?? 'N/A', 0, 40), 'align' => 'L'],
                        ['text' => substr($row['Proveedor'] ?? 'N/A', 0, 20), 'align' => 'L'],
                        ['text' => $row['Cajas'], 'align' => 'C'],
                        ['text' => number_format($row['Unidades']), 'align' => 'C'],
                        ['text' => 'Bs. ' . number_format($row['Valorado (Bs)'], 2), 'align' => 'R'],
                        ['text' => $estado_texto, 'align' => 'C']
                    ];
                }

                $rows[] = ['cells' => $cells];
            }

            $total_valorado = array_sum(array_column($datos, 'Valorado (Bs)'));

            $resumen = [
                'Total de Medicamentos' => ['text' => count($datos)],
                'Valor Total del Inventario' => ['text' => 'Bs ' . number_format($total_valorado, 2), 'color' => [46, 125, 50]]
            ];

            $datos_pdf = [
                'titulo' => 'REPORTE DE INVENTARIO',
                'nombre_archivo' => 'Inventario_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => $info_superior,
                'tabla' => [
                    'headers' => $headers,
                    'rows' => $rows
                ],
                'resumen' => $resumen
            ];

            // Generar y descargar PDF directamente
            $content = self::generar_pdf_reporte_fpdf($datos_pdf);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $datos_pdf['nombre_archivo'] . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $content;
            exit();

        } catch (Exception $e) {
            error_log("Error exportando PDF inventario: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }

    /* ===== OBTENER DATOS PARA BALANCE ===== */
    public function obtener_datos_balance_controller($med_id, $su_id)
    {
        try {
            // Obtener datos actuales de precios desde un lote activo representativo
            $sql = mainModel::conectar()->prepare("
                SELECT
                    lm.lm_costo_lista,
                    lm.lm_margen_u,
                    lm.lm_margen_c,
                    lm.lm_precio_venta,
                    lm.lm_precio_min_u,
                    lm.lm_precio_min_c,
                    COALESCE(lp.pr_razon_social, mp.pr_razon_social, 'Sin proveedor') AS proveedor
                FROM lote_medicamento lm
                LEFT JOIN proveedores lp ON lm.pr_id = lp.pr_id
                LEFT JOIN medicamento m ON lm.med_id = m.med_id
                LEFT JOIN proveedores mp ON m.pr_id = mp.pr_id
                WHERE lm.med_id = :med_id
                  AND lm.su_id = :su_id
                  AND lm.lm_estado = 'activo'
                  AND lm.lm_cant_actual_unidades > 0
                ORDER BY lm.lm_fecha_ingreso DESC
                LIMIT 1
            ");

            $sql->bindParam(":med_id", $med_id);
            $sql->bindParam(":su_id", $su_id);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $datos = $sql->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => true,
                    'costo_lista' => $datos['lm_costo_lista'],
                    'margen_u' => $datos['lm_margen_u'],
                    'margen_c' => $datos['lm_margen_c'],
                    'precio_venta' => $datos['lm_precio_venta'],
                    'precio_min_u' => $datos['lm_precio_min_u'],
                    'precio_min_c' => $datos['lm_precio_min_c'],
                    'proveedor' => $datos['proveedor']
                ];
            } else {
                // Si no hay lotes activos, obtener datos del medicamento
                $sql_med = mainModel::conectar()->prepare("
                    SELECT
                        m.med_id,
                        COALESCE(p.pr_razon_social, 'Sin proveedor') AS proveedor
                    FROM medicamento m
                    LEFT JOIN proveedores p ON m.pr_id = p.pr_id
                    WHERE m.med_id = :med_id
                ");

                $sql_med->bindParam(":med_id", $med_id);
                $sql_med->execute();

                if ($sql_med->rowCount() > 0) {
                    $datos_med = $sql_med->fetch(PDO::FETCH_ASSOC);
                    return [
                        'success' => true,
                        'costo_lista' => null,
                        'margen_u' => null,
                        'margen_c' => null,
                        'precio_venta' => null,
                        'precio_min_u' => null,
                        'precio_min_c' => null,
                        'proveedor' => $datos_med['proveedor']
                    ];
                } else {
                    return ['success' => false, 'error' => 'Medicamento no encontrado'];
                }
            }
        } catch (Exception $e) {
            error_log("Error obteniendo datos balance: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()];
        }
    }

    /* ===== APLICAR BALANCE DE PRECIOS ===== */
    public function balance_precios_controller($med_id, $su_id, $costo_lista, $margen_u, $margen_c, $precio_venta, $precio_min_u, $precio_min_c)
    {
        try {
            // Validar permisos
            $rol_usuario = $_SESSION['rol_smp'] ?? 0;
            if ($rol_usuario != 1 && $rol_usuario != 2) {
                return [
                    'Alerta' => 'simple',
                    'Titulo' => 'Permiso denegado',
                    'texto' => 'No cuenta con los privilegios necesarios para realizar balance de precios',
                    'Tipo' => 'error'
                ];
            }

            // Obtener todos los lotes activos de este medicamento en esta sucursal
            $sql_lotes = mainModel::conectar()->prepare("
                SELECT lm_id, lm_numero_lote
                FROM lote_medicamento
                WHERE med_id = :med_id
                  AND su_id = :su_id
                  AND lm_estado = 'activo'
                  AND lm_cant_actual_unidades > 0
            ");

            $sql_lotes->bindParam(":med_id", $med_id);
            $sql_lotes->bindParam(":su_id", $su_id);
            $sql_lotes->execute();

            $lotes = $sql_lotes->fetchAll(PDO::FETCH_ASSOC);
            $cantidad_lotes = count($lotes);

            if ($cantidad_lotes == 0) {
                return [
                    'Alerta' => 'simple',
                    'Titulo' => 'Sin lotes activos',
                    'texto' => 'No hay lotes activos para este medicamento en esta sucursal',
                    'Tipo' => 'warning'
                ];
            }

            // Preparar datos de actualización
            $datos_up = [
                'lm_costo_lista' => $costo_lista,
                'lm_margen_u' => $margen_u,
                'lm_margen_c' => $margen_c,
                'lm_precio_venta' => $precio_venta,
                'lm_precio_min_u' => $precio_min_u,
                'lm_precio_min_c' => $precio_min_c
            ];

            // Actualizar cada lote
            $lotes_actualizados = 0;
            $errores = [];

            foreach ($lotes as $lote) {
                $datos_up['ID'] = $lote['lm_id'];

                try {
                    $resultado = loteModel::actualizar_lote_model($datos_up);
                    if ($resultado->rowCount() == 1) {
                        $lotes_actualizados++;

                        // Registrar historial del lote
                        $historial = [
                            'lm_id' => $lote['lm_id'],
                            'us_id' => $_SESSION['id_smp'],
                            'hl_accion' => 'balance',
                            'hl_descripcion' => 'Balance de precios aplicado a lote ' . $lote['lm_numero_lote']
                        ];
                        loteModel::registrar_historial_lote_model($historial);
                    } else {
                        $errores[] = 'Lote ' . $lote['lm_numero_lote'];
                    }
                } catch (Exception $e) {
                    $errores[] = 'Lote ' . $lote['lm_numero_lote'] . ' (Error: ' . $e->getMessage() . ')';
                }
            }

            if ($lotes_actualizados > 0) {
                $mensaje = "Balance aplicado correctamente a $lotes_actualizados de $cantidad_lotes lotes";

                if (!empty($errores)) {
                    $mensaje .= ". Errores en: " . implode(', ', $errores);
                }

                return [
                    'Alerta' => 'recargar',
                    'Titulo' => 'Balance aplicado',
                    'texto' => $mensaje,
                    'Tipo' => $lotes_actualizados == $cantidad_lotes ? 'success' : 'warning'
                ];
            } else {
                return [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error en balance',
                    'texto' => 'No se pudo aplicar el balance a ningún lote. Errores: ' . implode(', ', $errores),
                    'Tipo' => 'error'
                ];
            }

        } catch (Exception $e) {
            error_log("Error en balance_precios_controller: " . $e->getMessage());
            return [
                'Alerta' => 'simple',
                'Titulo' => 'Error interno',
                'texto' => 'Ocurrió un error interno al aplicar el balance',
                'Tipo' => 'error'
            ];
        }
    }

    /* ===== OBTENER DETALLE GENERAL DE INVENTARIO ===== */
    public function detalle_general_controller($med_id, $su_id)
    {
        try {
            $sql = mainModel::conectar()->prepare("
                SELECT
                    i.inv_total_cajas,
                    i.inv_total_unidades,
                    i.inv_total_valorado,
                    CASE
                        WHEN i.inv_total_unidades <= 0 THEN 'agotado'
                        WHEN i.inv_total_unidades <= (i.inv_minimo * 1.5) THEN 'critico'
                        WHEN i.inv_total_unidades <= (i.inv_minimo * 3) THEN 'bajo'
                        WHEN i.inv_total_unidades >= (i.inv_maximo * 0.9) THEN 'exceso'
                        ELSE 'normal'
                    END as estado,
                    CASE
                        WHEN i.inv_total_unidades <= 0 THEN '<span class=\"badge badge-error\">Agotado</span>'
                        WHEN i.inv_total_unidades <= (i.inv_minimo * 1.5) THEN '<span class=\"badge badge-warning\">Crítico</span>'
                        WHEN i.inv_total_unidades <= (i.inv_minimo * 3) THEN '<span class=\"badge badge-info\">Bajo</span>'
                        WHEN i.inv_total_unidades >= (i.inv_maximo * 0.9) THEN '<span class=\"badge badge-purple\">Exceso</span>'
                        ELSE '<span class=\"badge badge-success\">Normal</span>'
                    END as estado_html,
                    m.med_nombre_quimico as medicamento,
                    m.med_presentacion,
                    ff.ff_nombre as forma_farmaceutica,
                    uf.uf_nombre as uso_farmacologico,
                    p.pr_razon_social as laboral,
                    s.su_nombre as sucursal
                FROM inventarios i
                LEFT JOIN medicamento m ON i.med_id = m.med_id
                LEFT JOIN forma_farmaceutica ff ON m.ff_id = ff.ff_id
                LEFT JOIN uso_farmacologico uf ON m.uf_id = uf.uf_id
                LEFT JOIN proveedores p ON m.pr_id = p.pr_id
                LEFT JOIN sucursales s ON i.su_id = s.su_id
                WHERE i.med_id = :med_id AND i.su_id = :su_id
                LIMIT 1
            ");

            $sql->bindParam(":med_id", $med_id);
            $sql->bindParam(":su_id", $su_id);
            $sql->execute();

            if ($sql->rowCount() > 0) {
                $datos = $sql->fetch(PDO::FETCH_ASSOC);
                return $datos;
            } else {
                return null;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo detalle general: " . $e->getMessage());
            return null;
        }
    }

    /* ===== OBTENER LOTES DISPONIBLES ===== */
    public function lotes_disponibles_controller($med_id, $su_id)
    {
        try {
            $sql = mainModel::conectar()->prepare("
                SELECT
                    lm.lm_numero_lote,
                    lm.lm_cant_actual_unidades,
                    lm.lm_precio_venta,
                    lm.lm_fecha_vencimiento,
                    CASE
                        WHEN lm.lm_estado = 'activo' THEN 'Activo'
                        WHEN lm.lm_estado = 'en_espera' THEN 'En Espera'
                        WHEN lm.lm_estado = 'terminado' THEN 'Terminado'
                        WHEN lm.lm_estado = 'caducado' THEN 'Caducado'
                        ELSE lm.lm_estado
                    END as estado
                FROM lote_medicamento lm
                WHERE lm.med_id = :med_id
                  AND lm.su_id = :su_id
                  AND lm.lm_estado = 'activo'
                  AND lm.lm_cant_actual_unidades > 0
                ORDER BY lm.lm_fecha_vencimiento ASC
            ");

            $sql->bindParam(":med_id", $med_id);
            $sql->bindParam(":su_id", $su_id);
            $sql->execute();

            return $sql->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error obteniendo lotes disponibles: " . $e->getMessage());
            return [];
        }
    }
}
