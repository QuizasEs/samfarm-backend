<?php

if ($peticionAjax) {
    require_once '../models/preciosModel.php';
} else {
    require_once './models/preciosModel.php';
}

class preciosController extends preciosModel
{
    /**
     * OBTENER MEDICAMENTOS CON LOTES PARA LA VISTA
     */
    public function obtener_medicamentos_precios_controller($busqueda = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Acceso Denegado",
                "texto" => "Solo administradores pueden gestionar precios",
                "Tipo" => "error"
            ]);
        }

        $busqueda = mainModel::limpiar_cadena($busqueda);
        
        $medicamentos = preciosModel::obtener_medicamentos_con_lotes_model(null, $busqueda);
        
        return json_encode($medicamentos);
    }

    /**
     * OBTENER LOTES DE UN MEDICAMENTO ESPECÍFICO
     */
    public function obtener_lotes_precios_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Acceso Denegado",
                "texto" => "Solo administradores pueden gestionar precios",
                "Tipo" => "error"
            ]);
        }

        if (!isset($_POST['med_id']) || !is_numeric($_POST['med_id'])) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "texto" => "ID de medicamento inválido",
                "Tipo" => "error"
            ]);
        }

        $med_id = (int)$_POST['med_id'];

        $lotes = preciosModel::obtener_lotes_medicamento_model($med_id, null);
        
        return json_encode($lotes);
    }

    /**
     * ACTUALIZAR PRECIO DE UN LOTE INDIVIDUAL
     */
    public function actualizar_precio_lote_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $usuario_id = $_SESSION['id_smp'] ?? 0;

        if ($rol_usuario != 1) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Acceso Denegado",
                "texto" => "Solo administradores pueden actualizar precios",
                "Tipo" => "error"
            ]);
        }

        if (!isset($_POST['lm_id']) || !is_numeric($_POST['lm_id']) ||
            !isset($_POST['med_id']) || !is_numeric($_POST['med_id']) ||
            !isset($_POST['precio_nuevo']) || !is_numeric($_POST['precio_nuevo'])) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error de Validación",
                "texto" => "Datos incompletos o inválidos",
                "Tipo" => "error"
            ]);
        }

        $lm_id = (int)$_POST['lm_id'];
        $med_id = (int)$_POST['med_id'];
        $precio_nuevo = (float)$_POST['precio_nuevo'];

        if ($precio_nuevo <= 0) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Precio Inválido",
                "texto" => "El precio debe ser mayor a 0",
                "Tipo" => "error"
            ]);
        }

        $resultado = preciosModel::actualizar_precio_lote_individual_model($lm_id, $precio_nuevo, $usuario_id, $med_id);

        return json_encode($resultado);
    }

    /**
     * ACTUALIZAR PRECIO DE TODOS LOS LOTES DE UN MEDICAMENTO
     */
    public function actualizar_precio_todos_lotes_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $usuario_id = $_SESSION['id_smp'] ?? 0;

        if ($rol_usuario != 1) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Acceso Denegado",
                "texto" => "Solo administradores pueden actualizar precios",
                "Tipo" => "error"
            ]);
        }

        if (!isset($_POST['med_id']) || !is_numeric($_POST['med_id']) ||
            !isset($_POST['precio_nuevo']) || !is_numeric($_POST['precio_nuevo'])) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error de Validación",
                "texto" => "Datos incompletos o inválidos",
                "Tipo" => "error"
            ]);
        }

        $med_id = (int)$_POST['med_id'];
        $precio_nuevo = (float)$_POST['precio_nuevo'];
        $su_id = isset($_POST['su_id']) && !empty($_POST['su_id']) ? (int)$_POST['su_id'] : null;

        if ($precio_nuevo <= 0) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Precio Inválido",
                "texto" => "El precio debe ser mayor a 0",
                "Tipo" => "error"
            ]);
        }

        $resultado = preciosModel::actualizar_precio_todos_lotes_model($med_id, $precio_nuevo, $usuario_id, $su_id);

        return json_encode($resultado);
    }

    /**
     * LISTAR INFORMES CON PAGINACIÓN
     */
    public function listar_informes_html_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3>Acceso Denegado</h3>
                        <p>Solo administradores pueden ver esta sección</p>
                    </div>';
        }

        $busqueda = isset($_POST['busqueda']) ? mainModel::limpiar_cadena($_POST['busqueda']) : '';
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $inicio = ($pagina - 1) * $registros;

        $filtros = [];
        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        try {
            $total_registros = preciosModel::contar_informes_cambios_precios_model($filtros);
            $total_paginas = ceil($total_registros / $registros);

            $informes = preciosModel::obtener_informes_cambios_precios_model($inicio, $registros, $filtros);

            $html = '<div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width:40px;">N°</th>
                                    <th style="width:90px;">Lote</th>
                                    <th>Medicamento</th>
                                    <th style="width:130px;">Sucursal</th>
                                    <th style="width:160px;">Precio (Bs)</th>
                                    <th style="width:210px;">Detalle</th>
                                    <th style="width:160px;">Usuario / Fecha</th>
                                </tr>
                            </thead>
                            <tbody>';

            if (!empty($informes) && $pagina <= $total_paginas) {
                $contador = $inicio + 1;
                $reg_inicio = $inicio + 1;
                foreach ($informes as $informe) {
                    $usuario = htmlspecialchars(trim(($informe['us_nombres'] ?? '') . ' ' . ($informe['us_apellido_paterno'] ?? '')));
                    $medicamento = htmlspecialchars($informe['med_nombre_quimico'] ?? 'N/A');
                    $sucursal = htmlspecialchars($informe['su_nombre'] ?? 'N/A');
                    $lote = htmlspecialchars($informe['lm_numero_lote'] ?? 'N/A');
                    $fecha = date('d/m/Y H:i:s', strtotime($informe['bp_creado_en'] ?? date('Y-m-d H:i:s')));
                    
                    $precio_anterior = number_format($informe['bp_precio_anterior'] ?? 0, 2, ',', '.');
                    $precio_nuevo = number_format($informe['bp_precio_nuevo'] ?? 0, 2, ',', '.');

                    // Decodificar detalles
                    $detalle_json = json_decode($informe['bp_detalle'] ?? '', true) ?: [];
                    $tipo = $detalle_json['origen'] ?? $detalle_json['tipo'] ?? 'balance_manual';

                    $origenes = [
                        'compra' => 'Compra',
                        'balance_manual' => 'Balance manual total',
                        'cambio_individual' => 'Balance solo al lote',
                        'cambio_todos_lotes' => 'Balance a todos los lotes',
                        'cambio_individual_lote' => 'Edicion de lote'
                    ];
                    $etiqueta = $origenes[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
                    $detalles = htmlspecialchars($etiqueta);

                    if ($tipo === 'compra' && !empty($detalle_json['compra_numero'])) {
                        $detalles .= '<br><span style="color:#888;">Compra N° ' . htmlspecialchars($detalle_json['compra_numero']) . '</span>';
                    }

                    $html .= '<tr>';
                    $html .= '<td  style="font-size:14px;">' . $contador . '</td>';
                    $html .= '<td  style="font-size:12px;"><strong>' . $lote . '</strong></td>';
                    $html .= '<td><strong>' . $medicamento . '</strong></td>';
                    $html .= '<td>' . $sucursal . '</td>';
                    $html .= '<td style="text-align:right;white-space:nowrap;">'
                        . '<span style="color:#e74c3c;text-decoration:line-through;">' . $precio_anterior . '</span>'
                        . ' <span style="color:#999;">&rarr;</span> '
                        . '<span style="color:#27ae60;font-weight:600;">' . $precio_nuevo . '</span>'
                        . '</td>';
                    $html .= '<td style="font-size:13px;line-height:1.5;">' . $detalles . '</td>';
                    $html .= '<td style="font-size:13px;">' . $usuario
                        . '<br><span style="color:#888;">' . $fecha . '</span></td>';
                    $html .= '</tr>';
                    $contador++;
                }
                $reg_final = $contador - 1;
            } else {
                error_log("No hay informes o no es un array. Total: " . $total_registros);
                $html .= '<tr><td colspan="7" style="text-align:center;padding:20px;color:#999;"><ion-icon name="document-outline"></ion-icon> No hay registros de cambios de precios</td></tr>';
            }

            $html .= '</tbody></table></div>';

            if (!empty($informes) && $pagina <= $total_paginas) {
                $html .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total_registros . '</p>';
                $html .= mainModel::paginador_tablas_main($pagina, $total_paginas, SERVER_URL . 'preciosBalance/', 5);
            }

            return $html;

        } catch (Exception $e) {
            error_log("Error en listar_informes_html_controller: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;"><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    public function paginado_informes_precios_controller($pagina, $registros, $url)
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario != 1) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3>Acceso Denegado</h3>
                        <p>Solo administradores pueden ver esta sección</p>
                    </div>';
        }

        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $filtros = [];

        $total = preciosModel::contar_informes_cambios_precios_model($filtros);
        $informes = preciosModel::obtener_informes_cambios_precios_model($inicio, $registros, $filtros);

        $Npaginas = ceil($total / $registros);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>LOTE</th>
                            <th>MEDICAMENTO</th>
                            <th>SUCURSAL</th>
                            <th>PRECIO ANTERIOR</th>
                            <th>PRECIO NUEVO</th>
                            <th>USUARIO</th>
                            <th>FECHA/HORA</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;

            foreach ($informes as $informe) {
                $usuario = htmlspecialchars(trim(($informe['us_nombres'] ?? '') . ' ' . ($informe['us_apellido_paterno'] ?? '')));
                $medicamento = htmlspecialchars($informe['med_nombre_quimico'] ?? 'N/A');
                $sucursal = htmlspecialchars($informe['su_nombre'] ?? 'N/A');
                $lote = htmlspecialchars($informe['lm_numero_lote'] ?? 'N/A');
                $fecha = date('d/m/Y H:i:s', strtotime($informe['bp_creado_en'] ?? date('Y-m-d H:i:s')));
                
                $precio_anterior = number_format($informe['bp_precio_anterior'] ?? 0, 2, ',', '.');
                $precio_nuevo = number_format($informe['bp_precio_nuevo'] ?? 0, 2, ',', '.');

                $tabla .= "
                    <tr>
                        <td>" . $contador . "</td>
                        <td><strong>" . $lote . "</strong></td>
                        <td><strong>" . $medicamento . "</strong></td>
                        <td>" . $sucursal . "</td>
                        <td style='text-align:right;color:#e74c3c;font-weight:600;'>Bs " . $precio_anterior . "</td>
                        <td style='text-align:right;color:#27ae60;font-weight:600;'>Bs " . $precio_nuevo . "</td>
                        <td>" . $usuario . "</td>
                        <td>" . $fecha . "</td>
                    </tr>
                ";
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="8" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="document-outline"></ion-icon> No hay registros
                    </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $reg_inicio = $inicio + 1;
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }
}
