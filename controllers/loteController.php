<?php

if ($peticionAjax) {
    require_once '../models/loteModel.php';
} else {
    require_once './models/loteModel.php';
}

class loteController extends loteModel
{

    /* entrega datos de lote de una id especifica */
    public function datos_lote_controller($id)
    {
        if (is_numeric($id)) {
            $id = mainModel::limpiar_cadena($id);
            return loteModel::datos_lote_model($id);
        }
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);
        return loteModel::datos_lote_model($id);
    }

    public function paginado_lote_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "")
    {
        /* Validar permisos de acceso */
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
        $id_usuario = $_SESSION['id_smp'] ?? 0;

        // Vendedores (rol 3) NO pueden acceder
        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3> Acceso Denegado</h3>
                        <p>No tiene permisos para ver esta sección</p>
                    </div>';
        }

        /* limpiamos cadenas para evitar inyección */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1);
        $f2 = mainModel::limpiar_cadena($f2);
        $f3 = mainModel::limpiar_cadena($f3);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        // Construir WHERE dinámico
        $whereParts = [];

        //  DEBUG TEMPORAL (puedes comentarlo después)
        error_log("=== FILTROS DEBUG ===");
        error_log("Rol: $rol_usuario | Sucursal: $sucursal_usuario");
        error_log("F1='$f1' | F2='$f2' | F3='$f3' | Búsqueda='$busqueda'");

        //  FILTRO POR SUCURSAL según rol
        if ($rol_usuario == 1) {
            // ADMIN: puede ver todas o filtrar por sucursal específica
            if ($f3 !== '') {
                $whereParts[] = "lm.su_id = '" . $f3 . "'";
                error_log(" Admin filtrando por sucursal: $f3");
            } else {
                error_log(" Admin viendo TODAS las sucursales");
            }
        } elseif ($rol_usuario == 2) {
            // GERENTE: SIEMPRE filtra por su sucursal
            $whereParts[] = "lm.su_id = '" . $sucursal_usuario . "'";
            error_log(" Gerente viendo solo sucursal: $sucursal_usuario");
        }

        // 🔍 Búsqueda por nombre químico, principio activo, proveedor y número de lote
        if (!empty($busqueda)) {
            $whereParts[] = "(
                m.med_nombre_quimico LIKE '%$busqueda%' OR
                m.med_principio_activo LIKE '%$busqueda%' OR
                p.pr_razon_social LIKE '%$busqueda%' OR
                lm.lm_numero_lote LIKE '%$busqueda%'
            )";
        }

        //  Select 1: Estado del lote
        if ($f1 !== '') {
            $estados_validos = ['en_espera', 'activo', 'terminado', 'caducado', 'devuelto', 'bloqueado'];
            if (in_array($f1, $estados_validos)) {
                $whereParts[] = "lm.lm_estado = '$f1'";
            }
        }

        //  Select 2: Mes
        if ($f2 !== '' && is_numeric($f2)) {
            $mes = (int)$f2;
            if ($mes >= 1 && $mes <= 12) {
                $whereParts[] = "MONTH(lm.lm_fecha_ingreso) = $mes";
            }
        }

        //  Filtros de fecha con validación mejorada
        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        $fecha_desde_valida = !empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde);
        $fecha_hasta_valida = !empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta);

        if ($fecha_desde_valida && $fecha_hasta_valida) {
            $timestamp_desde = strtotime($fecha_desde);
            $timestamp_hasta = strtotime($fecha_hasta);

            if ($timestamp_desde <= $timestamp_hasta) {
                $whereParts[] = "DATE(lm.lm_fecha_ingreso) BETWEEN '$fecha_desde' AND '$fecha_hasta'";
            } else {
                $whereParts[] = "DATE(lm.lm_fecha_ingreso) BETWEEN '$fecha_hasta' AND '$fecha_desde'";
            }
        } elseif ($fecha_desde_valida) {
            $whereParts[] = "DATE(lm.lm_fecha_ingreso) >= '$fecha_desde'";
        } elseif ($fecha_hasta_valida) {
            $whereParts[] = "DATE(lm.lm_fecha_ingreso) <= '$fecha_hasta'";
        }

        //  Construir cláusula WHERE
        $whereSQL = count($whereParts) > 0 ? "WHERE " . implode(' AND ', $whereParts) : "";

        $consulta = "
            SELECT DISTINCT
                lm.lm_id,
                lm.lm_numero_lote,
                m.med_nombre_quimico,
                m.med_principio_activo,
                p.pr_razon_social,
                s.su_nombre,
                lm.lm_cant_caja AS lm_cajas_inicial,
                lm.lm_total_unidades AS lm_unidades_inicial,
                lm.lm_cant_actual_cajas AS lm_cajas_actual,
                lm.lm_cant_actual_unidades AS lm_unidades_actual,
                lm.lm_precio_compra,
                lm.lm_precio_venta,
                lm.lm_fecha_ingreso,
                lm.lm_fecha_vencimiento,
                lm.lm_estado,
                lm.lm_creado_en,
                lm.lm_actualizado_en,
                lm.lm_costo_lista,
                lm.lm_margen_u,
                lm.lm_margen_c,
                lm.lm_precio_min_u,
                lm.lm_precio_min_c
            FROM lote_medicamento lm
            INNER JOIN medicamento m ON lm.med_id = m.med_id
            INNER JOIN sucursales s ON lm.su_id = s.su_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            $whereSQL
            ORDER BY lm.lm_fecha_ingreso DESC 
            LIMIT $inicio, $registros
        ";
        
        $consulta_total = "
            SELECT COUNT(DISTINCT lm.lm_id) as total
            FROM lote_medicamento lm
            INNER JOIN medicamento m ON lm.med_id = m.med_id
            INNER JOIN sucursales s ON lm.su_id = s.su_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            $whereSQL
        ";

        error_log("=== SQL GENERADO ===");
        error_log($consulta);

        try {
            $conexion = mainModel::conectar();
            $datosStmt = $conexion->query($consulta);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $totalStmt = $conexion->query($consulta_total);
            $totalRow = $totalStmt->fetch(PDO::FETCH_ASSOC);
            $total = (int) $totalRow['total'];

            error_log("Resultados: " . count($datos) . " de $total total");
        } catch (PDOException $e) {
            error_log(" ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;border:2px solid red;margin:10px;">
                    <strong>Error en la consulta SQL:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        // Determinar si mostrar columna SUCURSAL (solo para admin)
        $mostrar_columna_sucursal = ($rol_usuario == 1);
        $colspan_total = 5;

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>LOTE / MEDICAMENTO</th>
                            <th>STOCK</th>
                            <th>FECHAS</th>
                            <th>AUDITORIA</th>
                            <th>ESTADO</th>

                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $rows) {
                $fecha_actual = date('Y-m-d');
                $fecha_vencimiento = $rows['lm_fecha_vencimiento'];

                // Generar badge de estado
                switch ($rows['lm_estado']) {
                    case 'en_espera':
                        $estado_html = '<span class="badge bwar"><ion-icon name="time-outline"></ion-icon> En Espera</span>';
                        break;
                    case 'activo':
                        $estado_html = '<span class="badge bgr"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>';
                        break;
                    case 'terminado':
                        $estado_html = '<span class="badge bgry"><ion-icon name="archive-outline"></ion-icon> Terminado</span>';
                        break;
                    case 'caducado':
                        $estado_html = '<span class="badge bdan"><ion-icon name="warning-outline"></ion-icon> Caducado</span>';
                        break;
                    case 'devuelto':
                        $estado_html = '<span class="badge binf"><ion-icon name="return-down-back-outline"></ion-icon> Devuelto</span>';
                        break;
                    case 'bloqueado':
                        $estado_html = '<span class="badge bgry"><ion-icon name="lock-closed-outline"></ion-icon> Bloqueado</span>';
                        break;
                    default:
                        $estado_html = '<span class="badge bdef">' . ucfirst($rows['lm_estado']) . '</span>';
                }

                $dias_restantes = '';
                if ($fecha_vencimiento && $rows['lm_estado'] == 'activo') {
                    $diff = strtotime($fecha_vencimiento) - strtotime($fecha_actual);
                    $dias = floor($diff / (60 * 60 * 24));
                    if ($dias <= 30 && $dias > 0) {
                        $dias_restantes = '<br><small style="color: orange;"><ion-icon name="alarm-outline"></ion-icon> Vence en ' . $dias . ' días</small>';
                    } elseif ($dias <= 0) {
                        $dias_restantes = '<br><small style="color: red;font-weight:bold;"><ion-icon name="trash-bin-outline"></ion-icon> ¡VENCIDO!</small>';
                    }
                }

                $tabla .= '
                        <tr class="tr-click" onclick="' . ($rows['lm_estado'] == 'activo' && $rows['lm_unidades_actual'] > 0 && $rol_usuario == 1 ? 'LoteModals.abrirEdicion(\'' . mainModel::encryption($rows['lm_id']) . '\');' : '') . '">
                        <td>
                            <div class="td-main"><strong>' . ($rows['lm_numero_lote'] ?? 'N/A') . '</strong> - ' . htmlspecialchars($rows['med_nombre_quimico']) . '</div>
                            <div class="td-sub">' . htmlspecialchars($rows['med_principio_activo']) . ' · ' . htmlspecialchars($rows['pr_razon_social'] ?? 'N/A') .
                    ($mostrar_columna_sucursal ? ' · ' . htmlspecialchars($rows['su_nombre']) : '') . '</div>
                        </td>
                        <td>
                            <div class="td-main"><strong style="color:#1976D2;">' . $rows['lm_unidades_actual'] . '</strong> unidades</div>
                            <div class="td-sub">' . $rows['lm_cajas_actual'] . ' cajas actuales / ' . $rows['lm_cajas_inicial'] . ' inicial</div>
                        </td>
                        <td>
                            <div class="td-main">Vence: ' . ($fecha_vencimiento ? date('d/m/Y', strtotime($fecha_vencimiento)) : 'N/A') . '</div>
                            <div class="td-sub">Ingreso: ' . date('d/m/Y', strtotime($rows['lm_fecha_ingreso'])) . $dias_restantes . '</div>
                        </td>
                        <td>
                            <div class="td-main">Marg. U: ' . ($rows['lm_margen_u'] ? $rows['lm_margen_u'] . '%' : 'N/A') . '</div>
                            <div class="td-sub">Marg. C: ' . ($rows['lm_margen_c'] ? $rows['lm_margen_c'] . '%' : 'N/A') . '</div>
                            <div class="td-sub">Min. U: ' . ($rows['lm_precio_min_u'] ? 'Bs. ' . number_format($rows['lm_precio_min_u'], 2) : 'N/A') . '</div>
                            <div class="td-sub">Min. C: ' . ($rows['lm_precio_min_c'] ? 'Bs. ' . number_format($rows['lm_precio_min_c'], 2) : 'N/A') . '</div>
                        </td>
                        <td>
                            <div class="td-main">' .
                    ($rows['lm_estado'] == "en_espera"
                        ? '<a href="#" onclick="loteManager.openActivationModal(\'' . mainModel::encryption($rows['lm_id']) . '\', \'' . htmlspecialchars(addslashes($rows['med_nombre_quimico'])) . '\'); return false;" title="Activar lote">Activar</a>'
                        : $estado_html)
                    . '</div>
                        </td>
                        <td class="buttons">
                            ' . ''
                            . '
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="5" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="bug-outline"></ion-icon> No hay registros que coincidan con los filtros aplicados
                    </td></tr>';
        }

        $tabla .= '
                    </tbody>
                </table>
            </div>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }


    public function actualizar_lote_controller()
    {
        // Desencriptar el ID una sola vez (la encriptación es importante por seguridad)
        $id = mainModel::decryption($_POST['id']);
        $id = mainModel::limpiar_cadena($id);

        /* Obtenemos la información del lote */
        $datos = loteModel::datos_lote_model($id);
        if ($datos->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'El lote no existe en el sistema',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $lote = $datos->fetch(PDO::FETCH_ASSOC);

        /* Verificar rol del usuario */
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        if ($rol_usuario != 1 && $rol_usuario != 2) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Permiso denegado',
                'texto' => 'No cuenta con los privilegios necesarios para editar este lote',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Solo se puede editar si el lote está activo */
        if ($lote['lm_estado'] != 'activo') {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Acción no permitida',
                'texto' => 'Solo los lotes activos pueden ser editados',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Obtenemos los valores del formulario */
        $cant_caja    = (int)mainModel::limpiar_cadena($_POST['Cantidad_caja_up'] ?? $lote['lm_cant_caja']);
        $cant_blister = 1; // Mantener valor fijo de 1 para lm_cant_blister (ya no se usa)
        $cant_unidad  = (int)mainModel::limpiar_cadena($_POST['Cantidad_unidades_up'] ?? $lote['lm_cant_unidad']);
        $precio_compra = (float)mainModel::limpiar_cadena($_POST['Precio_compra_up'] ?? $lote['lm_precio_compra']);
        $precio_venta = (float)mainModel::limpiar_cadena($_POST['Precio_venta_up'] ?? $lote['lm_precio_venta']);
        $fecha_vencimiento = mainModel::limpiar_cadena($_POST['Fecha_vencimiento_up'] ?? $lote['lm_fecha_vencimiento']);
        /* campos de auditoria */
        $costo_lista = isset($_POST['lm_costo_lista']) ? (float)mainModel::limpiar_cadena($_POST['lm_costo_lista']) : $lote['lm_costo_lista'];
        $margen_u = isset($_POST['lm_margen_u']) ? (float)mainModel::limpiar_cadena($_POST['lm_margen_u']) : $lote['lm_margen_u'];
        $margen_c = isset($_POST['lm_margen_c']) ? (float)mainModel::limpiar_cadena($_POST['lm_margen_c']) : $lote['lm_margen_c'];
        $precio_min_u = isset($_POST['lm_precio_min_u']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_min_u']) : $lote['lm_precio_min_u'];
        $precio_min_c = isset($_POST['lm_precio_min_c']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_min_c']) : $lote['lm_precio_min_c'];

        /* Validación de datos requeridos */
        if (empty($fecha_vencimiento)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'Debe ingresar la fecha de vencimiento',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Validaciones de formato para campos numéricos cuando se proporcionan */
        if (!empty($_POST['Cantidad_caja_up'])) {
            $cant_caja_input = mainModel::limpiar_cadena($_POST['Cantidad_caja_up']);
            if (mainModel::verificar_datos("[0-9]{1,10}", $cant_caja_input)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Formato inválido',
                    'texto' => 'La CANTIDAD DE CAJAS debe ser un número válido.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if (!empty($_POST['Cantidad_blister_up'])) {
            $cant_blister_input = mainModel::limpiar_cadena($_POST['Cantidad_blister_up']);
            if (mainModel::verificar_datos("[0-9]{1,10}", $cant_blister_input)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Formato inválido',
                    'texto' => 'La CANTIDAD DE BLISTER debe ser un número válido.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if (!empty($_POST['Cantidad_unidades_up'])) {
            $cant_unidad_input = mainModel::limpiar_cadena($_POST['Cantidad_unidades_up']);
            if (mainModel::verificar_datos("[0-9]{1,10}", $cant_unidad_input)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Formato inválido',
                    'texto' => 'La CANTIDAD DE UNIDADES debe ser un número válido.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if (!empty($_POST['Precio_compra_up'])) {
            $precio_compra_input = mainModel::limpiar_cadena($_POST['Precio_compra_up']);
            if (mainModel::verificar_datos("[0-9.]{1,10}", $precio_compra_input)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Formato inválido',
                    'texto' => 'El PRECIO DE COMPRA no cumple con el formato requerido.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        if (!empty($_POST['Precio_venta_up'])) {
            $precio_venta_input = mainModel::limpiar_cadena($_POST['Precio_venta_up']);
            if (mainModel::verificar_datos("[0-9.]{1,10}", $precio_venta_input)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Formato inválido',
                    'texto' => 'El PRECIO DE VENTA no cumple con el formato requerido.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        /* Calcular cantidades actuales */
        $lm_cant_actual_cajas = $cant_caja;
        $lm_cant_actual_unidades = $cant_caja * $cant_blister * $cant_unidad;

        /* Estructura para actualizar */
        $datos_up = [
            'lm_cant_caja' => $cant_caja,
            'lm_cant_blister' => $cant_blister,
            'lm_cant_unidad' => $cant_unidad,
            'lm_cant_actual_cajas' => $lm_cant_actual_cajas,
            'lm_cant_actual_unidades' => $lm_cant_actual_unidades,
            'lm_precio_compra' => $precio_compra,
            'lm_precio_venta' => $precio_venta,
            'lm_fecha_vencimiento' => $fecha_vencimiento,
            'lm_costo_lista' => $costo_lista,
            'lm_margen_u' => $margen_u,
            'lm_margen_c' => $margen_c,
            'lm_precio_min_u' => $precio_min_u,
            'lm_precio_min_c' => $precio_min_c,
            'lm_origen_id' => $lote['lm_origen_id'] ?? null,
            'ID' => $id
        ];

        $actualizado = loteModel::actualizar_lote_model($datos_up);

        if ($actualizado->rowCount() == 1) {
            // Calcular diferencias para actualizar inventario
            $unidades_anteriores = (int)$lote['lm_cant_actual_unidades'];
            $unidades_nuevas = $cant_caja * $cant_blister * $cant_unidad;
            $diferencia_unidades = $unidades_nuevas - $unidades_anteriores;

            $cajas_anteriores = (int)$lote['lm_cant_actual_cajas'];
            $cajas_nuevas = $cant_caja;
            $diferencia_cajas = $cajas_nuevas - $cajas_anteriores;

            // Actualizar inventario si hay cambios en cantidades
            if ($diferencia_unidades != 0 || $diferencia_cajas != 0) {
                $datos_inventario = [
                    'su_id' => $lote['su_id'],
                    'med_id' => $lote['med_id'],
                    'inv_total_cajas' => $diferencia_cajas,
                    'inv_total_unidades' => $diferencia_unidades,
                    'inv_total_valorado' => 0 // No cambiar valorado en ajustes de cantidad
                ];

                $inventario_resultado = loteModel::actualizar_inventario_model($datos_inventario);

                if ($inventario_resultado->rowCount() <= 0) {
                    error_log("Error actualizando inventario para lote ID: $id");
                } else {
                    // Registrar movimiento de inventario
                    $datos_movimiento = [
                        'lm_id' => $id,
                        'med_id' => $lote['med_id'],
                        'su_id' => $lote['su_id'],
                        'us_id' => $_SESSION['id_smp'],
                        'mi_tipo' => ($diferencia_unidades > 0) ? 'entrada' : 'salida',
                        'mi_cantidad' => abs($diferencia_unidades),
                        'mi_unidad' => 'unidad',
                        'mi_referencia_tipo' => 'ajuste_lote',
                        'mi_referencia_id' => $id,
                        'mi_motivo' => 'Ajuste de cantidades en lote ' . ($lote['lm_numero_lote'] ?? 'N/A')
                    ];

                    $movimiento_resultado = loteModel::registro_movimiento_inventario_model($datos_movimiento);

                    if ($movimiento_resultado->rowCount() <= 0) {
                        error_log("Error registrando movimiento de inventario para lote ID: $id");
                    }
                }
            }

            // Registrar historial
            $historial = [
                'lm_id' => $id,
                'us_id' => $_SESSION['id_smp'],
                'hl_accion' => 'ajuste',
                'hl_descripcion' => 'Actualización de datos del lote (cantidades/precios/fecha de vencimiento)'
            ];
            loteModel::registrar_historial_lote_model($historial);

            $alerta = [
                'Alerta' => 'recargar',
                'Titulo' => 'Actualizado',
                'texto' => 'Se actualizo el lote correctamente',
                'Tipo' => 'success'
            ];
            echo json_encode($alerta);
            exit();
        } else {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error al actualizar',
                'texto' => 'No se pudo actualizar el lote. Verifique los datos e intente nuevamente.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }
    public function activar_lote_controller()
    {
        /* Desencriptar el ID por seguridad */
        $id = mainModel::decryption($_POST['id']);
        $id = mainModel::limpiar_cadena($id);

        /* validamos la id */
        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'El lote no existe en el sistema',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        /* revisamos que quien intenta activar el lote tenga permisos para la accion */
        $rol = $_SESSION['rol_smp'] ?? 0;


        if (!in_array($rol, [1, 2])) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No cuenta con los privilegios necesarios para realizar esta accion',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        /* recuperamos la informacion del lote */
        $datos = self::datos_lote_model($id);
        /* verificamos el retorno de informacion */
        if ($datos->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado!',
                'texto' => 'El lote no existe',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        $lote = $datos->fetch();

        /* verificamos que el es estado del lote */
        if ($lote['lm_estado'] !== 'en_espera') {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado!',
                'texto' => 'Este lote ya se encuentra activado o no es posible su activacion',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        /* preparamos lote activacion */
        $datos_lote = [
            'lm_estado' => 'activo',
            'lm_id' => $id,
            'parametro' => 'en_espera',
            'us_id' => $_SESSION['id_smp']
        ];
        /* insertamos y actualizamos */
        $lote_up = loteModel::activar_lote_model($datos_lote);

        if ($lote_up->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado!',
                'texto' => 'No pudimos activar el lote',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        /* datos del lote para su historial, movimiento e inventario */
        $usuario_id = $_SESSION['id_smp'];
        $usuario_name = $_SESSION['nombre_smp'];
        $med_id = (int)$lote['med_id'];
        $su_id = (int)$lote['su_id'];
        $precio_compra = $lote['lm_precio_compra'];
        $precio_venta = $lote['lm_precio_venta'];
        $cantidad_cajas = (int)$lote['lm_cant_actual_cajas'];
        $cantidad_blister = (int)$lote['lm_cant_blister'];
        $cantidad_unidades = (int)$lote['lm_cant_actual_unidades'];
        $cantidad_actual_cajas = $cantidad_cajas;
        $cantidad_actual_unidades = $cantidad_cajas * $cantidad_blister * $cantidad_unidades;
        $total_unidades = $cantidad_actual_unidades;
        $subtotal_lote = $cantidad_unidades * $precio_compra;

        /* preparamos el historial */
        $datos_historial = [
            "lm_id" => $id,
            "us_id" => $usuario_id,
            "hl_accion" => "activacion",
            "hl_descripcion" => "Lote activado por " . $usuario_name
        ];
        $historial_resultado = loteModel::registrar_historial_lote_model($datos_historial);

        if ($historial_resultado->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error al registrar historial',
                'texto' => 'No se pudo registrar el historial del lote.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* preparamos datos para ctualizar inventario */
        $datos_inventario = [
            "su_id" => $su_id,
            "med_id" => $med_id,
            "inv_total_cajas" => $cantidad_actual_cajas,
            "inv_total_unidades" => $cantidad_actual_unidades,
            // valoramos según subtotal de compra (precio_compra * cajas)
            "inv_total_valorado" => $subtotal_lote
        ];

        /* insertamos el inventario */
        $inventario_resultado = loteModel::actualizar_inventario_model($datos_inventario);

        if ($inventario_resultado->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error inventario',
                'texto' => 'No se pudo actualizar el inventario.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* preparamos los datos de mivimeinto inventario */
        $datos_movimiento = [
            "lm_id" => $id,
            "med_id" => $med_id,
            "su_id" => $su_id,
            "us_id" => $usuario_id,
            "mi_tipo" => "entrada",
            "mi_cantidad" => $cantidad_actual_unidades,
            "mi_unidad" => "unidad",
            "mi_referencia_tipo" => "activacion",
            "mi_referencia_id" => $id,
            "mi_motivo" => "Ingreso por Activacion de lote {$lote['lm_numero_lote']}"
        ];

        /* insertamos movimiento */

        $movimiento_resultado = loteModel::registro_movimiento_inventario_model($datos_movimiento);

        /* verificamos */
        if ($movimiento_resultado->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error movimiento',
                'texto' => 'No se pudo registrar el movimiento de inventario.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* informe */
        $config_informe = [
            "tipo_informe"       => "activacion_lote",
            "lote_id"            => $id,
            "numero_lote"        => $lote['lm_numero_lote'],
            "medicamento_id"     => $med_id,
            "medicamento_nombre" => $lote['med_nombre'] ?? 'No especificado',
            "sucursal_id"        => $su_id,
            "usuario_id"         => $usuario_id,
            "usuario_nombre"     => $usuario_name,
            "fecha_activacion"   => date("Y-m-d H:i:s"),
            "precio_compra"      => (float) $precio_compra,
            "precio_venta"       => (float) $precio_venta,
            "cantidad_cajas"     => $cantidad_cajas,
            "cantidad_unidades"  => $cantidad_actual_unidades,
            "subtotal_lote"      => $subtotal_lote,
            "observaciones"      => "Activación inicial del lote e ingreso a inventario."
        ];

        $datos_informe = [
            "inf_nombre"  => "Activación de Lote #" . $lote['lm_numero_lote'] . " (" . ($lote['med_nombre'] ?? 'Nombre no disponible') . ")",
            "inf_usuario" => $usuario_id,
            "inf_config"  => json_encode($config_informe, JSON_UNESCAPED_UNICODE)
        ];


        /* insertamos informe */

        $informe_result = loteModel::agregar_informe_compra_model($datos_informe);

        if ($informe_result->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrió un error inesperado',
                'texto' => 'No se pudo registrar el informe.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $alerta = [
            'Alerta' => 'recargar',
            'Titulo' => 'Lote activado',
            'texto' => 'El lote se activó correctamente y se generó el informe de activación.',
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }

    public function exportar_pdf_lotes_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            echo "Acceso denegado";
            return;
        }

        $filtros = [];

        // Filtrar por sucursal según rol
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 1) {
            if (isset($_GET['select3']) && $_GET['select3'] !== '') {
                $filtros['su_id'] = (int)$_GET['select3'];
            }
        } elseif ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        }

        // Filtros de búsqueda y estado
        if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $filtros['busqueda'] = mainModel::limpiar_cadena($_GET['busqueda']);
        }

        if (isset($_GET['select1']) && $_GET['select1'] !== '') {
            $estados_validos = ['en_espera', 'activo', 'terminado', 'caducado', 'devuelto', 'bloqueado'];
            if (in_array($_GET['select1'], $estados_validos)) {
                $filtros['estado'] = mainModel::limpiar_cadena($_GET['select1']);
            }
        }

        if (isset($_GET['select2']) && is_numeric($_GET['select2'])) {
            $mes = (int)$_GET['select2'];
            if ($mes >= 1 && $mes <= 12) {
                $filtros['mes'] = $mes;
            }
        }

        // Filtros de fecha
        if (isset($_GET['fecha_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = mainModel::limpiar_cadena($_GET['fecha_desde']);
        }
        if (isset($_GET['fecha_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = mainModel::limpiar_cadena($_GET['fecha_hasta']);
        }

        try {
            // Usar la consulta existente pero con límite mayor para PDF
            $stmt = self::paginado_lote_controller(1, 1000, '#', $filtros['busqueda'] ?? '', $filtros['estado'] ?? '', $filtros['mes'] ?? '', $filtros['su_id'] ?? '');

            // Obtener datos directamente del modelo para PDF
            $consulta = "
                SELECT DISTINCT
                    lm.lm_id,
                    lm.lm_numero_lote,
                    m.med_nombre_quimico,
                    m.med_principio_activo,
                    p.pr_razon_social,
                    s.su_nombre,
                    lm.lm_cant_caja AS lm_cajas_inicial,
                    lm.lm_total_unidades AS lm_unidades_inicial,
                    lm.lm_cant_actual_cajas AS lm_cajas_actual,
                    lm.lm_cant_actual_unidades AS lm_unidades_actual,
                    lm.lm_precio_compra,
                    lm.lm_precio_venta,
                    lm.lm_fecha_ingreso,
                    lm.lm_fecha_vencimiento,
                    lm.lm_estado,
                    lm.lm_creado_en,
                    lm.lm_costo_lista,
                    lm.lm_margen_u,
                    lm.lm_margen_c,
                    lm.lm_precio_min_u,
                    lm.lm_precio_min_c
                FROM lote_medicamento lm
                INNER JOIN medicamento m ON lm.med_id = m.med_id
                INNER JOIN sucursales s ON lm.su_id = s.su_id
                LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            ";

            $whereParts = [];
            if (!empty($filtros['busqueda'])) {
                $whereParts[] = "(
                    m.med_nombre_quimico LIKE '%{$filtros['busqueda']}%' OR
                    m.med_principio_activo LIKE '%{$filtros['busqueda']}%' OR
                    p.pr_razon_social LIKE '%{$filtros['busqueda']}%' OR
                    lm.lm_numero_lote LIKE '%{$filtros['busqueda']}%'
                )";
            }
            if (!empty($filtros['estado'])) {
                $whereParts[] = "lm.lm_estado = '{$filtros['estado']}'";
            }
            if (!empty($filtros['mes'])) {
                $whereParts[] = "MONTH(lm.lm_fecha_ingreso) = {$filtros['mes']}";
            }
            if (!empty($filtros['su_id'])) {
                $whereParts[] = "lm.su_id = '{$filtros['su_id']}'";
            }

            $fecha_desde = $filtros['fecha_desde'] ?? '';
            $fecha_hasta = $filtros['fecha_hasta'] ?? '';

            if ($fecha_desde && $fecha_hasta) {
                $timestamp_desde = strtotime($fecha_desde);
                $timestamp_hasta = strtotime($fecha_hasta);
                if ($timestamp_desde <= $timestamp_hasta) {
                    $whereParts[] = "DATE(lm.lm_fecha_ingreso) BETWEEN '{$fecha_desde}' AND '{$fecha_hasta}'";
                } else {
                    $whereParts[] = "DATE(lm.lm_fecha_ingreso) BETWEEN '{$fecha_hasta}' AND '{$fecha_desde}'";
                }
            } elseif ($fecha_desde) {
                $whereParts[] = "DATE(lm.lm_fecha_ingreso) >= '{$fecha_desde}'";
            } elseif ($fecha_hasta) {
                $whereParts[] = "DATE(lm.lm_fecha_ingreso) <= '{$fecha_hasta}'";
            }

            if (count($whereParts) > 0) {
                $consulta .= " WHERE " . implode(' AND ', $whereParts);
            }

            $consulta .= " ORDER BY lm.lm_fecha_ingreso DESC LIMIT 1000";

            $conexion = mainModel::conectar();
            $stmt = $conexion->query($consulta);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar con los filtros aplicados.";
                return;
            }

            $periodo = '';
            if (!empty($fecha_desde) && !empty($fecha_hasta)) {
                $periodo = date('d/m/Y', strtotime($fecha_desde)) . ' al ' . date('d/m/Y', strtotime($fecha_hasta));
            } else {
                $periodo = 'Todo el período';
            }

            $info_superior = [
                'Periodo' => $periodo,
                'Total de Lotes' => count($datos),
                'Generado' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema'
            ];

            $headers = [];
            if ($rol_usuario == 1) {
                $headers = [
                    ['text' => 'N° LOTE', 'width' => 20],
                    ['text' => 'MEDICAMENTO', 'width' => 25],
                    ['text' => 'PROVEEDOR', 'width' => 20],
                    ['text' => 'SUCURSAL', 'width' => 15],
                    ['text' => 'CAJAS ACT.', 'width' => 12],
                    ['text' => 'UND ACT.', 'width' => 12],
                    ['text' => 'PRECIO COMPRA', 'width' => 15],
                    ['text' => 'PRECIO VENTA', 'width' => 15],
                    ['text' => 'COSTO LISTA', 'width' => 15],
                    ['text' => 'MARG. U', 'width' => 10],
                    ['text' => 'MARG. C', 'width' => 10],
                    ['text' => 'MIN. U', 'width' => 12],
                    ['text' => 'MIN. C', 'width' => 12],
                    ['text' => 'VENCIMIENTO', 'width' => 15],
                    ['text' => 'ESTADO', 'width' => 12]
                ];
            } else {
                $headers = [
                    ['text' => 'N° LOTE', 'width' => 22],
                    ['text' => 'MEDICAMENTO', 'width' => 30],
                    ['text' => 'PROVEEDOR', 'width' => 25],
                    ['text' => 'CAJAS ACT.', 'width' => 12],
                    ['text' => 'UND ACT.', 'width' => 12],
                    ['text' => 'PRECIO COMPRA', 'width' => 15],
                    ['text' => 'PRECIO VENTA', 'width' => 15],
                    ['text' => 'COSTO LISTA', 'width' => 15],
                    ['text' => 'MARG. U', 'width' => 10],
                    ['text' => 'MARG. C', 'width' => 10],
                    ['text' => 'MIN. U', 'width' => 12],
                    ['text' => 'MIN. C', 'width' => 12],
                    ['text' => 'VENCIMIENTO', 'width' => 18],
                    ['text' => 'ESTADO', 'width' => 12]
                ];
            }

            $rows = [];
            foreach ($datos as $row) {
                $estado_texto = '';
                switch ($row['lm_estado']) {
                    case 'en_espera': $estado_texto = 'En Espera'; break;
                    case 'activo': $estado_texto = 'Activo'; break;
                    case 'terminado': $estado_texto = 'Terminado'; break;
                    case 'caducado': $estado_texto = 'Caducado'; break;
                    case 'devuelto': $estado_texto = 'Devuelto'; break;
                    case 'bloqueado': $estado_texto = 'Bloqueado'; break;
                    default: $estado_texto = ucfirst($row['lm_estado']);
                }

                if ($rol_usuario == 1) {
                    $cells = [
                        ['text' => $row['lm_numero_lote'] ?? 'N/A', 'align' => 'L'],
                        ['text' => substr($row['med_nombre_quimico'] ?? 'N/A', 0, 20), 'align' => 'L'],
                        ['text' => substr($row['pr_razon_social'] ?? 'N/A', 0, 15), 'align' => 'L'],
                        ['text' => substr($row['su_nombre'] ?? 'N/A', 0, 12), 'align' => 'L'],
                        ['text' => $row['lm_cajas_actual'], 'align' => 'C'],
                        ['text' => number_format($row['lm_unidades_actual']), 'align' => 'C'],
                        ['text' => 'Bs. ' . number_format($row['lm_precio_compra'], 2), 'align' => 'R'],
                        ['text' => 'Bs. ' . number_format($row['lm_precio_venta'], 2), 'align' => 'R'],
                        ['text' => $row['lm_costo_lista'] ? 'Bs. ' . number_format($row['lm_costo_lista'], 2) : 'N/A', 'align' => 'R'],
                        ['text' => $row['lm_margen_u'] ? $row['lm_margen_u'] . '%' : 'N/A', 'align' => 'C'],
                        ['text' => $row['lm_margen_c'] ? $row['lm_margen_c'] . '%' : 'N/A', 'align' => 'C'],
                        ['text' => $row['lm_precio_min_u'] ? 'Bs. ' . number_format($row['lm_precio_min_u'], 2) : 'N/A', 'align' => 'R'],
                        ['text' => $row['lm_precio_min_c'] ? 'Bs. ' . number_format($row['lm_precio_min_c'], 2) : 'N/A', 'align' => 'R'],
                        ['text' => $row['lm_fecha_vencimiento'] ? date('d/m/Y', strtotime($row['lm_fecha_vencimiento'])) : 'N/A', 'align' => 'C'],
                        ['text' => $estado_texto, 'align' => 'C']
                    ];
                } else {
                    $cells = [
                        ['text' => $row['lm_numero_lote'] ?? 'N/A', 'align' => 'L'],
                        ['text' => substr($row['med_nombre_quimico'] ?? 'N/A', 0, 25), 'align' => 'L'],
                        ['text' => substr($row['pr_razon_social'] ?? 'N/A', 0, 20), 'align' => 'L'],
                        ['text' => $row['lm_cajas_actual'], 'align' => 'C'],
                        ['text' => number_format($row['lm_unidades_actual']), 'align' => 'C'],
                        ['text' => 'Bs. ' . number_format($row['lm_precio_compra'], 2), 'align' => 'R'],
                        ['text' => 'Bs. ' . number_format($row['lm_precio_venta'], 2), 'align' => 'R'],
                        ['text' => $row['lm_costo_lista'] ? 'Bs. ' . number_format($row['lm_costo_lista'], 2) : 'N/A', 'align' => 'R'],
                        ['text' => $row['lm_margen_u'] ? $row['lm_margen_u'] . '%' : 'N/A', 'align' => 'C'],
                        ['text' => $row['lm_margen_c'] ? $row['lm_margen_c'] . '%' : 'N/A', 'align' => 'C'],
                        ['text' => $row['lm_precio_min_u'] ? 'Bs. ' . number_format($row['lm_precio_min_u'], 2) : 'N/A', 'align' => 'R'],
                        ['text' => $row['lm_precio_min_c'] ? 'Bs. ' . number_format($row['lm_precio_min_c'], 2) : 'N/A', 'align' => 'R'],
                        ['text' => $row['lm_fecha_vencimiento'] ? date('d/m/Y', strtotime($row['lm_fecha_vencimiento'])) : 'N/A', 'align' => 'C'],
                        ['text' => $estado_texto, 'align' => 'C']
                    ];
                }

                $rows[] = ['cells' => $cells];
            }

            $resumen = [
                'Total de Lotes' => ['text' => count($datos)]
            ];

            $datos_pdf = [
                'titulo' => 'REPORTE DE LOTES DE MEDICAMENTOS',
                'nombre_archivo' => 'Lotes_Medicamentos_' . date('Y-m-d_His') . '.pdf',
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
            error_log("Error exportando PDF lotes: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }
}
