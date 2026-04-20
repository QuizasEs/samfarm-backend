<?php

if ($peticionAjax) {
    require_once '../models/inventarioModel.php';
} else {
    require_once './models/inventarioModel.php';
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
}
