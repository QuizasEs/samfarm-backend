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

        $su_id = preciosModel::obtener_sucursal_del_lote_model($lm_id);
        if (!$su_id) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "texto" => "No se pudo obtener la sucursal del lote",
                "Tipo" => "error"
            ]);
        }

        $resultado = preciosModel::actualizar_precio_lote_individual_model($lm_id, $precio_nuevo, $usuario_id, $su_id, $med_id);

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

        if ($precio_nuevo <= 0) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Precio Inválido",
                "texto" => "El precio debe ser mayor a 0",
                "Tipo" => "error"
            ]);
        }

        $su_id = isset($_POST['su_id']) && !empty($_POST['su_id']) ? (int)$_POST['su_id'] : 1;

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

            $html = '<div class="table-container"><table class="table"><thead><tr>';
            $html .= '<th>N°</th><th>Tipo Cambio</th><th>Medicamento</th><th>Sucursal</th>';
            $html .= '<th>Precio Anterior (Bs)</th><th>Precio Nuevo (Bs)</th>';
            $html .= '<th>Lotes Afectados</th><th>Usuario</th><th>Fecha/Hora</th></tr></thead><tbody>';

            if (!empty($informes) && $pagina <= $total_paginas) {
                $contador = $inicio + 1;
                $reg_inicio = $inicio + 1;
                
                foreach ($informes as $informe) {
                    $contenido = json_decode($informe['inf_config'], true) ?? [];
                    
                    $tipo_cambio = ($contenido['tipo_cambio'] ?? '') == 'lote_individual' 
                        ? '<span style="background:#E3F2FD;padding:4px 10px;border-radius:12px;font-weight:600;color:#1565C0;">Individual</span>'
                        : '<span style="background:#C8E6C9;padding:4px 10px;border-radius:12px;font-weight:600;color:#2e7d32;">Todos</span>';
                    
                    $usuario = htmlspecialchars(trim(($informe['us_nombres'] ?? '') . ' ' . ($informe['us_apellido_paterno'] ?? '')));
                    $medicamento = htmlspecialchars($informe['med_nombre_quimico'] ?? 'N/A');
                    $sucursal = htmlspecialchars($informe['su_nombre'] ?? 'N/A');
                    $fecha = date('d/m/Y H:i:s', strtotime($informe['inf_creado_en'] ?? date('Y-m-d H:i:s')));
                    
                    $precio_anterior = number_format($contenido['precio_anterior'] ?? 0, 2, ',', '.');
                    $precio_nuevo = number_format($contenido['precio_nuevo'] ?? 0, 2, ',', '.');
                    $cantidad = $contenido['cantidad_lotes_afectados'] ?? 0;

                    $html .= '<tr>';
                    $html .= '<td>' . $contador . '</td>';
                    $html .= '<td>' . $tipo_cambio . '</td>';
                    $html .= '<td><strong>' . $medicamento . '</strong></td>';
                    $html .= '<td>' . $sucursal . '</td>';
                    $html .= '<td style="text-align:right;color:#e74c3c;font-weight:600;">Bs ' . $precio_anterior . '</td>';
                    $html .= '<td style="text-align:right;color:#27ae60;font-weight:600;">Bs ' . $precio_nuevo . '</td>';
                    $html .= '<td style="text-align:center;">' . $cantidad . '</td>';
                    $html .= '<td>' . $usuario . '</td>';
                    $html .= '<td>' . $fecha . '</td>';
                    $html .= '</tr>';
                    $contador++;
                }
                $reg_final = $contador - 1;
            } else {
                $html .= '<tr><td colspan="9" style="text-align:center;padding:20px;color:#999;"><ion-icon name="document-outline"></ion-icon> No hay registros</td></tr>';
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
                            <th>TIPO CAMBIO</th>
                            <th>MEDICAMENTO</th>
                            <th>SUCURSAL</th>
                            <th>PRECIO ANTERIOR</th>
                            <th>PRECIO NUEVO</th>
                            <th>LOTES AFECTADOS</th>
                            <th>USUARIO</th>
                            <th>FECHA/HORA</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;

            foreach ($informes as $informe) {
                $contenido = json_decode($informe['inf_config'], true);
                
                $tipo_cambio = $contenido['tipo_cambio'] == 'lote_individual' 
                    ? '<span style="background:#E3F2FD;padding:4px 10px;border-radius:12px;font-weight:600;color:#1565C0;">Individual</span>'
                    : '<span style="background:#C8E6C9;padding:4px 10px;border-radius:12px;font-weight:600;color:#2e7d32;">Todos</span>';
                
                $usuario = htmlspecialchars($informe['us_nombres'] . ' ' . ($informe['us_apellido_paterno'] ?? ''));
                $medicamento = htmlspecialchars($informe['med_nombre_quimico'] ?? 'N/A');
                $sucursal = htmlspecialchars($informe['su_nombre'] ?? 'N/A');
                $fecha = date('d/m/Y H:i:s', strtotime($informe['inf_creado_en']));
                
                $precio_anterior = number_format($contenido['precio_anterior'], 2, ',', '.');
                $precio_nuevo = number_format($contenido['precio_nuevo'], 2, ',', '.');
                $cantidad = $contenido['cantidad_lotes_afectados'];

                $tabla .= "
                    <tr>
                        <td>" . $contador . "</td>
                        <td>" . $tipo_cambio . "</td>
                        <td><strong>" . $medicamento . "</strong></td>
                        <td>" . $sucursal . "</td>
                        <td style='text-align:right;color:#e74c3c;font-weight:600;'>Bs " . $precio_anterior . "</td>
                        <td style='text-align:right;color:#27ae60;font-weight:600;'>Bs " . $precio_nuevo . "</td>
                        <td style='text-align:center;'>" . $cantidad . "</td>
                        <td>" . $usuario . "</td>
                        <td>" . $fecha . "</td>
                    </tr>
                ";
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="9" style="text-align:center;padding:20px;color:#999;">
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
