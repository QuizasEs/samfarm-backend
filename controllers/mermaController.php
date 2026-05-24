<?php

if ($peticionAjax) {
    require_once '../models/mermaModel.php';
    require_once '../models/loteModel.php';
} else {
    require_once './models/mermaModel.php';
    require_once './models/loteModel.php';
}

class mermaController extends mermaModel
{
    /**
     * Obtener lotes con riesgo de caducidad para crear mermas
     */
    public function obtener_lotes_caducidad_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1 && $rol_usuario != 2) {
            return null;
        }

        return mermaModel::detectar_lotes_caducados_model();
    }

    /**
     * Crear una merma (registro definitivo)
     */
    public function crear_merma_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $us_id = $_SESSION['id_smp'] ?? 0;

        if ($rol_usuario != 1 && $rol_usuario != 2) {
            return [
                "Alerta" => "simple",
                "Titulo" => "Acceso Denegado",
                "Texto" => "No tiene permisos para crear mermas",
                "Tipo" => "error"
            ];
        }

        $lm_id = isset($_POST['lm_id']) ? mainModel::limpiar_cadena($_POST['lm_id']) : '';
        $me_cantidad = isset($_POST['me_cantidad']) ? mainModel::limpiar_cadena($_POST['me_cantidad']) : '';
        $me_motivo = isset($_POST['me_motivo']) ? mainModel::limpiar_cadena($_POST['me_motivo']) : '';

        if (empty($lm_id) || !is_numeric($lm_id)) {
            return [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "ID de lote inválido",
                "Tipo" => "error"
            ];
        }

        if (empty($me_cantidad) || !is_numeric($me_cantidad) || $me_cantidad <= 0) {
            return [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "Cantidad de merma debe ser un número positivo",
                "Tipo" => "error"
            ];
        }

        if (empty($me_motivo)) {
            return [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "El motivo de la merma es obligatorio",
                "Tipo" => "error"
            ];
        }

        $loteModel = new loteModel();
        $stmt_lote = $loteModel->datos_lote_model((int)$lm_id);
        $detalle_lote = $stmt_lote->fetch(PDO::FETCH_ASSOC);

        if (!$detalle_lote) {
            return [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "Lote no encontrado",
                "Tipo" => "error"
            ];
        }

        $resultado = mermaModel::crear_merma_model(
            $detalle_lote['med_id'],
            (int)$lm_id,
            $detalle_lote['su_id'],
            (int)$us_id,
            (int)$me_cantidad,
            $me_motivo
        );

        if ($resultado) {
            return [
                "Alerta" => "redireccionar",
                "URL" => SERVER_URL . "mermaLista/"
            ];
        } else {
            return [
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "No se pudo crear la merma",
                "Tipo" => "error"
            ];
        }
    }

    /**
     * Mostrar lista de mermas
     */
    public function lista_mermas_controller($pagina = 1, $registros = 15, $busqueda = "", $f1 = "", $f2 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1 && $rol_usuario != 2) {
            return null;
        }

        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1);
        $f2 = mainModel::limpiar_cadena($f2);

        $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $filtros = [];

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if (!empty($f1)) {
            $filtros['fecha_desde'] = $f1;
            $filtros['fecha_hasta'] = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : date('Y-m-d');
        }

        if (!empty($f2) && is_numeric($f2)) {
            $filtros['su_id'] = (int)$f2;
        }

        try {
            $datosStmt = mermaModel::obtener_todas_mermas_model($inicio, $registros, $filtros);
            $datos = $datosStmt ? $datosStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            $total = mermaModel::contar_mermas_model($filtros);

            $datos_retorno = [
                'pagina' => $pagina,
                'registros' => $registros,
                'total' => $total,
                'datos' => $datos,
                'filtros' => $filtros
            ];

            return $datos_retorno;
        } catch (PDOException $e) {
            error_log("Error in lista_mermas_controller: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener detalles de una merma específica
     */
    public function detalle_merma_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1 && $rol_usuario != 2) {
            return null;
        }

        $me_id = isset($_GET['me_id']) ? mainModel::limpiar_cadena($_GET['me_id']) : '';

        if (empty($me_id) || !is_numeric($me_id)) {
            return null;
        }

        return mermaModel::obtener_detalle_merma_model((int)$me_id);
    }

    /**
     * Mostrar historial de mermas con paginación
     */
    public function paginado_historial_mermas_controller($pagina, $registros, $url, $busqueda = "", $fecha_desde = "", $fecha_hasta = "", $select2 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario != 1 && $rol_usuario != 2) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h3>
                        <p>No tiene permisos para ver el historial de mermas</p>
                    </div>';
        }

        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $fecha_desde = mainModel::limpiar_cadena($fecha_desde);
        $fecha_hasta = mainModel::limpiar_cadena($fecha_hasta);
        $select2 = mainModel::limpiar_cadena($select2);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $filtros = [];

        if ($rol_usuario == 1) {
            if ($select2 !== '') {
                $filtros['su_id'] = (int)$select2;
            }
        } elseif ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        }

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

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
        }
        // Removido el filtro automático del día actual - ahora muestra todos los registros por defecto

        try {
            $datosStmt = mermaModel::obtener_todas_mermas_model($inicio, $registros, $filtros);
            $datos = $datosStmt ? $datosStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            $total = mermaModel::contar_mermas_model($filtros);
        } catch (PDOException $e) {
            error_log("ERROR SQL MERMA: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);
        $mostrar_columna_sucursal = ($rol_usuario == 1);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>MEDICAMENTO</th>
                            <th>LOTE</th>
                            <th>CANTIDAD</th>
                            <th>MOTIVO</th>
                            <th>FECHA</th>
                            <th>USUARIO</th>' .
            ($mostrar_columna_sucursal ? '<th>SUCURSAL</th>' : '') .
            '</tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $fecha_formato = date('d/m/Y H:i', strtotime($row['me_fecha']));
                $usuario = trim(($row['us_nombres'] ?? 'Sistema') . ' ' . ($row['us_apellido_paterno'] ?? ''));

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td><strong>' . htmlspecialchars($row['med_nombre_quimico'] ?? '') . '</strong><br><small>' . htmlspecialchars($row['med_presentacion'] ?? '') . '</small></td>
                        <td>' . htmlspecialchars($row['lm_numero_lote'] ?? 'N/A') . '</td>
                        <td>' . number_format($row['me_cantidad'], 0) . ' unidades</td>
                        <td>' . htmlspecialchars($row['me_motivo'] ?? '-') . '</td>
                        <td>' . $fecha_formato . '</td>
                        <td>' . htmlspecialchars($usuario) . '</td>' .
                    ($mostrar_columna_sucursal ? '<td><span style="background:#E3F2FD;padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">' . htmlspecialchars($row['su_nombre']) . '</span></td>' : '') .
                    '</tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $colspan = $mostrar_columna_sucursal ? 8 : 7;
            $tabla .= '<tr><td colspan="' . $colspan . '" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="file-tray-outline"></ion-icon> No hay mermas registradas
                        </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }

    // metodo para generar la tabla de lotes con riesgo de caducidad para registrar merma
    // respeta el patron del proyecto: toda la generacion de html de tabla va en el controlador
    public function paginado_lotes_caducidad_merma_controller($pagina = 1, $registros = 10, $busqueda = '', $select1 = '', $select2 = '')
    {
        // rol y sucursal desde sesion
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        // validar permisos basicos
        if ($rol_usuario != 1 && $rol_usuario != 2) {
            return '<div class="error" style="padding:30px;text-align:center;">acceso denegado</div>';
        }

        // normalizar parametros
        $pagina = (int)$pagina;
        $registros = (int)$registros;
        $busqueda = trim($busqueda);
        $select1 = trim($select1);
        $select2 = trim($select2);

        // obtener datos base usando la funcion actual del controlador (sin modificarla)
        $lotes_caducidad = $this->obtener_lotes_caducidad_controller();

        // si no hay lotes en absoluto devolver el estado vacio exacto
        if (!is_array($lotes_caducidad) || count($lotes_caducidad) === 0) {
            return '
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Lote</th>
                                <th>Sucursal</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Cantidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px;color:#999;">
                                    <ion-icon name="checkmark-circle-outline" style="font-size:48px;margin-bottom:10px;"></ion-icon>
                                    <h3>Sin lotes con riesgo de caducidad</h3>
                                    <p>No hay productos proximos a vencer o caducados en este momento.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ';
        }

        // copiar lista para aplicar filtros sin tocar el original
        $lotes_filtrados = $lotes_caducidad;

        // filtro de busqueda por nombre o lote
        if (!empty($busqueda)) {
            $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($busqueda) {
                $busqueda_lower = strtolower($busqueda);
                return stripos($lote['med_nombre_quimico'], $busqueda_lower) !== false ||
                       stripos($lote['lm_numero_lote'], $busqueda_lower) !== false;
            });
        }

        // filtro por tipo: caducado o proximo a vencer
        if (!empty($select1)) {
            $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($select1) {
                $fecha_venc = strtotime($lote['lm_fecha_vencimiento']);
                $hoy = time();
                $diferencia_dias = ceil(($fecha_venc - $hoy) / (60 * 60 * 24));

                if ($select1 === 'caducado') {
                    return $diferencia_dias <= 0;
                } elseif ($select1 === 'proximo') {
                    return $diferencia_dias > 0 && $diferencia_dias <= 10;
                }
                return true;
            });
        }

        // filtro de sucursal segun rol del usuario
        if (!empty($select2) && $rol_usuario == 1) {
            $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($select2) {
                return $lote['su_id'] == $select2;
            });
        } elseif ($rol_usuario == 2) {
            $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($sucursal_usuario) {
                return $lote['su_id'] == $sucursal_usuario;
            });
        }

        // resetear indices del array
        $lotes_filtrados = array_values($lotes_filtrados);

        // calcular totales y paginacion
        $total = count($lotes_filtrados);
        $total_paginas = ceil($total / $registros);
        $inicio = ($pagina - 1) * $registros;
        $lotes_pagina = array_slice($lotes_filtrados, $inicio, $registros);

        // armar estructura base de la tabla html
        $tabla = '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>MEDICAMENTO</th>
                            <th>LOTE</th>
                            <th>SUCURSAL</th>
                            <th>VENCIMIENTO</th>
                            <th>ESTADO</th>
                            <th>CANTIDAD</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        // generar filas si hay datos en la pagina actual
        if (count($lotes_pagina) > 0) {
            $contador = $inicio + 1;
            foreach ($lotes_pagina as $lote) {
                $fecha_venc = strtotime($lote['lm_fecha_vencimiento']);
                $hoy = time();
                $diferencia_dias = ceil(($fecha_venc - $hoy) / (60 * 60 * 24));

                // definir badge visual de estado
                if ($diferencia_dias <= 0) {
                    $estado_html = '<span class="badge bdan" style="font-weight:bold;">CADUCADO</span>';
                } elseif ($diferencia_dias <= 10) {
                    $estado_html = '<span class="badge bwar" style="font-weight:bold;">' . $diferencia_dias . ' días</span>';
                } else {
                    $estado_html = '<span class="badge bdef">' . $diferencia_dias . ' días</span>';
                }

                // fila con onclick para abrir modal (mantiene comportamiento exacto)
                $tabla .= '
                <tr class="tr-click" onclick="abrirModalMermaRegistro(' . (int)$lote['lm_id'] . ', \'' . htmlspecialchars($lote['med_nombre_quimico']) . '\', ' . (int)$lote['lm_cant_actual_unidades'] . ')">
                    <td>' . $contador . '</td>
                    <td>
                        <div class="td-main">' . htmlspecialchars($lote['med_nombre_quimico'] ?? '') . '</div>
                    </td>
                    <td>' . htmlspecialchars($lote['lm_numero_lote'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($lote['su_nombre'] ?? '') . '</td>
                    <td>' . htmlspecialchars($lote['lm_fecha_vencimiento']) . '</td>
                    <td>' . $estado_html . '</td>
                    <td>' . number_format($lote['lm_cant_actual_unidades'], 0) . ' unidades</td>
                </tr>
                ';
                $contador++;
            }
        } else {
            // sin resultados despues de filtros
            $tabla .= '<tr><td colspan="8" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="file-tray-outline"></ion-icon> No hay lotes con riesgo de caducidad
                        </td></tr>';
        }

        // cerrar la tabla
        $tabla .= '
                    </tbody>
                </table>
            </div>
        ';

        // agregar bloque de paginacion solo si hay registros
        if (count($lotes_pagina) > 0) {
            $reg_inicio = $inicio + 1;
            $reg_final = $inicio + count($lotes_pagina);

            $tabla .= '<div class="pag">';
            $tabla .= '<div class="pginf">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</div>';

            // boton anterior
            if ($pagina == 1) {
                $tabla .= '<button class="pb dis" disabled><ion-icon name="chevron-back-outline"></ion-icon></button>';
            } else {
                $prev = $pagina - 1;
                $tabla .= '<button class="pb" data-page="' . $prev . '"><ion-icon name="chevron-back-outline"></ion-icon></button>';
            }

            // calcular botones de paginas a mostrar
            $botones = 5;
            $mitad = floor($botones / 2);
            $inicio_pag = max(1, $pagina - $mitad);
            $fin_pag = min($total_paginas, $inicio_pag + $botones - 1);

            if ($fin_pag - $inicio_pag + 1 < $botones) {
                $inicio_pag = max(1, $fin_pag - $botones + 1);
            }

            for ($i = $inicio_pag; $i <= $fin_pag; $i++) {
                if ($pagina == $i) {
                    $tabla .= '<button class="pb ac" data-page="' . $i . '">' . $i . '</button>';
                } else {
                    $tabla .= '<button class="pb" data-page="' . $i . '">' . $i . '</button>';
                }
            }

            // boton siguiente
            if ($pagina == $total_paginas) {
                $tabla .= '<button class="pb dis" disabled><ion-icon name="chevron-forward-outline"></ion-icon></button>';
            } else {
                $next = $pagina + 1;
                $tabla .= '<button class="pb" data-page="' . $next . '"><ion-icon name="chevron-forward-outline"></ion-icon></button>';
            }

            $tabla .= '</div>';
        }

        // devolver el html completo de tabla + paginacion
        return $tabla;
    }
}
