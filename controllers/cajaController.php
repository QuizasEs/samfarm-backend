<?php

if ($peticionAjax) {
    require_once '../models/cajaModel.php';
} else {
    require_once './models/cajaModel.php';
}

class cajaController extends cajaModel
{
    public function listar_cajas_controller()
    {
        $estado = isset($_POST['estado']) ? mainModel::limpiar_cadena($_POST['estado']) : '';
        $busqueda = isset($_POST['busqueda']) ? mainModel::limpiar_cadena($_POST['busqueda']) : '';
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;

        try {
            $stmt = self::listar_cajas_model($estado, $busqueda, $pagina, $registros);
            $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt_total = self::contar_cajas_model($estado, $busqueda);
            $total_row = $stmt_total->fetch(PDO::FETCH_ASSOC);
            $total_registros = $total_row['total'] ?? 0;
            $total_paginas = ceil($total_registros / $registros);

            return json_encode([
                'error' => false,
                'cajas' => $cajas,
                'total' => $total_registros,
                'total_paginas' => $total_paginas
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en listar_cajas_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar cajas'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_caja_controller()
    {
        $caja_id = isset($_POST['caja_id']) ? (int)$_POST['caja_id'] : 0;

        if ($caja_id <= 0) {
            return json_encode([
                'error' => true,
                'mensaje' => 'ID de caja inválido'
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            $stmt = self::obtener_caja_model($caja_id);
            $caja = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$caja) {
                return json_encode([
                    'error' => true,
                    'mensaje' => 'Caja no encontrada'
                ], JSON_UNESCAPED_UNICODE);
            }

            $movimientos_stmt = self::obtener_movimientos_caja_model($caja_id);
            $movimientos = $movimientos_stmt->fetch(PDO::FETCH_ASSOC);

            return json_encode([
                'error' => false,
                'caja' => $caja,
                'movimientos' => $movimientos
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_caja_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al obtener caja'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cerrar_caja_controller()
    {
        if (!isset($_SESSION['id_smp'])) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sesión inválida', 'texto' => 'No hay sesión válida', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $caja_id = isset($_POST['caja_id']) ? (int)$_POST['caja_id'] : 0;

        if ($caja_id <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'ID de caja inválido', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $stmt_caja = self::obtener_caja_model($caja_id);

        if ($stmt_caja->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'Caja no encontrada', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $caja = $stmt_caja->fetch(PDO::FETCH_ASSOC);

        if ($caja['caja_activa'] == 0 || $caja['caja_cerrado_en'] !== null) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'Esta caja ya está cerrada', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $saldo_inicial = (float)$caja['caja_saldo_inicial'];
        $movimientos_stmt = self::obtener_movimientos_caja_model($caja_id);
        $movimientos = $movimientos_stmt->fetch(PDO::FETCH_ASSOC);

        $total_ingresos = (float)$movimientos['total_ingresos'];
        $saldo_teorico = $saldo_inicial + $total_ingresos;

        $datos_cierre = [
            "caja_id" => $caja_id,
            "caja_saldo_final" => $saldo_teorico,
            "caja_cerrado_en" => date('Y-m-d H:i:s'),
            "caja_observacion" => isset($_POST['observacion']) ? mainModel::limpiar_cadena($_POST['observacion']) : "Cierre manual"
        ];

        $res = self::cerrar_caja_model($datos_cierre);

        if (!$res || $res->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error BD', 'texto' => 'No se pudo cerrar caja', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => 'Caja cerrada',
            'texto' => 'La caja se cerró correctamente. Saldo final: Bs. ' . number_format($saldo_teorico, 2),
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }

    public function obtener_cajas_cerradas_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $su_id = null;
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;

        if ($rol_usuario == 2) {
            $su_id = $_SESSION['sucursal_smp'] ?? null;
        } elseif ($rol_usuario == 1) {
            $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : null;
        }

        try {
            $stmt = self::obtener_cajas_cerradas_model($su_id, $pagina, $registros);
            $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt_total = self::contar_cajas_cerradas_model($su_id);
            $total_row = $stmt_total->fetch(PDO::FETCH_ASSOC);
            $total_registros = $total_row['total'] ?? 0;
            $total_paginas = ceil($total_registros / $registros);

            $resumen_stmt = self::obtener_resumen_cajas_cerradas_model($su_id);
            $resumen = $resumen_stmt->fetch(PDO::FETCH_ASSOC);

            return json_encode([
                'error' => false,
                'cajas' => $cajas,
                'resumen' => $resumen,
                'total' => $total_registros,
                'total_paginas' => $total_paginas
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_cajas_cerradas_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar cajas cerradas'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_ventas_por_usuario_controller()
    {
        $caja_id = isset($_POST['caja_id']) ? (int)$_POST['caja_id'] : null;

        try {
            $stmt = self::obtener_ventas_por_usuario_model($caja_id);
            $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'error' => false,
                'ventas' => $ventas
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_ventas_por_usuario_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar ventas por usuario'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function listar_cajas_html_controller()
    {
        $estado = isset($_POST['select1']) ? mainModel::limpiar_cadena($_POST['select1']) : '';
        $busqueda = isset($_POST['busqueda']) ? mainModel::limpiar_cadena($_POST['busqueda']) : '';
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $inicio = ($pagina - 1) * $registros;

        try {
            $stmt = self::listar_cajas_model($estado, $busqueda, $pagina, $registros);
            $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt_total = self::contar_cajas_model($estado, $busqueda);
            $total_row = $stmt_total->fetch(PDO::FETCH_ASSOC);
            $total_registros = $total_row['total'] ?? 0;
            $total_paginas = ceil($total_registros / $registros);

            $html = '<div class="table-container"><table class="table"><thead><tr>';
            $html .= '<th>N°</th><th>Nombre de Caja</th><th>Usuario</th><th>Sucursal</th>';
            $html .= '<th>Saldo Inicial (Bs)</th><th>Total Ingresos (Bs)</th><th>Total Egresos (Bs)</th>';
            $html .= '<th>Estado</th><th>Fecha Apertura</th><th>Acciones</th></tr></thead><tbody>';

            if (!empty($cajas) && $pagina <= $total_paginas) {
                $contador = $inicio + 1;
                $reg_inicio = $inicio + 1;
                
                foreach ($cajas as $caja) {
                    $nombreUsuario = trim(($caja['us_nombres'] ?? '') . ' ' . ($caja['us_apellido_paterno'] ?? ''));
                    $estado_caja = ($caja['caja_activa'] == 1 && $caja['caja_cerrado_en'] === null) ? 'Abierta' : 'Cerrada';
                    $estadoClass = ($estado_caja === 'Abierta') ? 'text-success' : 'text-muted';
                    
                    $fecha = '';
                    if ($caja['caja_creado_en']) {
                        $d = new DateTime($caja['caja_creado_en']);
                        $fecha = $d->format('d/m/Y H:i');
                    }

                    $btnCerrar = '';
                    if ($caja['caja_activa'] == 1 && $caja['caja_cerrado_en'] === null) {
                        $btnCerrar = '<button class="btn danger btn-sm" onclick="CajaGestion.abrirModalCerrar(' . $caja['caja_id'] . ')"><ion-icon name="lock-closed-outline"></ion-icon> Cerrar</button>';
                    }

                    $html .= '<tr>';
                    $html .= '<td>' . $contador . '</td>';
                    $html .= '<td>' . htmlspecialchars($caja['caja_nombre'] ?? '-') . '</td>';
                    $html .= '<td>' . htmlspecialchars($nombreUsuario ?: '-') . '</td>';
                    $html .= '<td>' . htmlspecialchars($caja['su_nombre'] ?? '-') . '</td>';
                    $html .= '<td>Bs. ' . number_format($caja['caja_saldo_inicial'] ?? 0, 2) . '</td>';
                    $html .= '<td>Bs. ' . number_format($caja['total_ingresos'] ?? 0, 2) . '</td>';
                    $html .= '<td>Bs. ' . number_format($caja['total_egresos'] ?? 0, 2) . '</td>';
                    $html .= '<td><span class="' . $estadoClass . ' text-bold">' . $estado_caja . '</span></td>';
                    $html .= '<td>' . $fecha . '</td>';
                    $html .= '<td>' . $btnCerrar . '</td>';
                    $html .= '</tr>';
                    $contador++;
                }
                $reg_final = $contador - 1;
            } else {
                $html .= '<tr><td colspan="10" style="text-align:center;">No hay cajas registradas</td></tr>';
            }

            $html .= '</tbody></table></div>';

            if (!empty($cajas) && $pagina <= $total_paginas) {
                $html .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total_registros . '</p>';
                $html .= mainModel::paginador_tablas_main($pagina, $total_paginas, SERVER_URL . 'cajaLista/', 5);
            }

            return $html;

        } catch (Exception $e) {
            error_log("Error en listar_cajas_html_controller: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    public function listar_cajas_cerradas_html_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $su_id = null;
        $busqueda = isset($_POST['busqueda']) ? mainModel::limpiar_cadena($_POST['busqueda']) : '';
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $inicio = ($pagina - 1) * $registros;

        if ($rol_usuario == 2) {
            $su_id = $_SESSION['sucursal_smp'] ?? null;
        } elseif ($rol_usuario == 1) {
            $su_id = isset($_POST['select1']) && !empty($_POST['select1']) ? (int)$_POST['select1'] : null;
        }

        try {
            $stmt = self::obtener_cajas_cerradas_model($su_id, $pagina, $registros, $busqueda);
            $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt_total = self::contar_cajas_cerradas_model($su_id, $busqueda);
            $total_row = $stmt_total->fetch(PDO::FETCH_ASSOC);
            $total_registros = $total_row['total'] ?? 0;
            $total_paginas = ceil($total_registros / $registros);

            $resumen_stmt = self::obtener_resumen_cajas_cerradas_model($su_id);
            $resumen = $resumen_stmt->fetch(PDO::FETCH_ASSOC);

            $resumen_html = '';
            if ($resumen && $resumen['total_cajas_cerradas'] > 0) {
                $resumen_html = '<script>';
                $resumen_html .= 'document.getElementById("contenedorResumen").style.display="block";';
                $resumen_html .= 'document.getElementById("resumenTotalCajas").textContent=' . json_encode($resumen['total_cajas_cerradas']) . ';';
                $resumen_html .= 'document.getElementById("resumenSaldosIniciales").textContent="Bs. ' . number_format($resumen['total_saldos_iniciales'] ?? 0, 2) . '";';
                $resumen_html .= 'document.getElementById("resumenSaldosFinales").textContent="Bs. ' . number_format($resumen['total_saldos_finales'] ?? 0, 2) . '";';
                $resumen_html .= 'document.getElementById("resumenDiferencia").textContent="Bs. ' . number_format($resumen['total_diferencia'] ?? 0, 2) . '";';
                $resumen_html .= 'document.getElementById("resumenVentas").textContent="Bs. ' . number_format($resumen['total_ventas_period'] ?? 0, 2) . '";';
                $resumen_html .= '</script>';
            }

            $html = '<div class="table-container"><table class="table"><thead><tr>';
            $html .= '<th>N°</th><th>Nombre de Caja</th><th>Usuario</th><th>Sucursal</th>';
            $html .= '<th>Saldo Inicial (Bs)</th><th>Saldo Final (Bs)</th><th>Arqueo (Bs)</th>';
            $html .= '<th>Total Ventas (Bs)</th><th>Fecha Cierre</th><th>Acciones</th></tr></thead><tbody>';

            if (!empty($cajas) && $pagina <= $total_paginas) {
                $contador = $inicio + 1;
                $reg_inicio = $inicio + 1;
                
                foreach ($cajas as $caja) {
                    $nombreUsuario = trim(($caja['us_nombres'] ?? '') . ' ' . ($caja['us_apellido_paterno'] ?? ''));
                    $arqueo = floatval($caja['caja_saldo_final'] ?? 0) - floatval($caja['caja_saldo_inicial'] ?? 0);
                    $arqueoClass = $arqueo >= 0 ? 'text-success' : 'text-error';
                    $arqueoFormato = $arqueo >= 0 ? '+' : '';
                    
                    $fecha = '';
                    if ($caja['caja_cerrado_en']) {
                        $d = new DateTime($caja['caja_cerrado_en']);
                        $fecha = $d->format('d/m/Y H:i');
                    }

                    $cajaJson = json_encode($caja);
                    $btnVer = '<button class="btn default btn-sm" onclick="CajaHistorialTotales.abrirModal(' . htmlspecialchars($cajaJson) . ')"><ion-icon name="eye-outline"></ion-icon> Ver</button>';

                    $html .= '<tr>';
                    $html .= '<td>' . $contador . '</td>';
                    $html .= '<td>' . htmlspecialchars($caja['caja_nombre'] ?? '-') . '</td>';
                    $html .= '<td>' . htmlspecialchars($nombreUsuario ?: '-') . '</td>';
                    $html .= '<td>' . htmlspecialchars($caja['su_nombre'] ?? '-') . '</td>';
                    $html .= '<td>Bs. ' . number_format($caja['caja_saldo_inicial'] ?? 0, 2) . '</td>';
                    $html .= '<td>Bs. ' . number_format($caja['caja_saldo_final'] ?? 0, 2) . '</td>';
                    $html .= '<td><span class="' . $arqueoClass . ' text-bold">' . $arqueoFormato . 'Bs. ' . number_format($arqueo, 2) . '</span></td>';
                    $html .= '<td>Bs. ' . number_format($caja['total_ventas'] ?? 0, 2) . '</td>';
                    $html .= '<td>' . $fecha . '</td>';
                    $html .= '<td>' . $btnVer . '</td>';
                    $html .= '</tr>';
                    $contador++;
                }
                $reg_final = $contador - 1;
            } else {
                $html .= '<tr><td colspan="10" style="text-align:center;">No hay cajas cerradas registradas</td></tr>';
            }

            $html .= '</tbody></table></div>';

            if (!empty($cajas) && $pagina <= $total_paginas) {
                $html .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total_registros . '</p>';
                $html .= mainModel::paginador_tablas_main($pagina, $total_paginas, SERVER_URL . 'cajaHistorialTotales/', 5);
            }

            return $resumen_html . $html;

        } catch (Exception $e) {
            error_log("Error en listar_cajas_cerradas_html_controller: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

