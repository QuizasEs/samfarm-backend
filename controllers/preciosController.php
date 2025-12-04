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

        $resultado = preciosModel::actualizar_precio_lote_individual_model($lm_id, $precio_nuevo, $usuario_id, 1, $med_id);

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

        $resultado = preciosModel::actualizar_precio_todos_lotes_model($med_id, $precio_nuevo, $usuario_id, 1);

        return json_encode($resultado);
    }

    /**
     * LISTAR INFORMES CON PAGINACIÓN
     */
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

        if (!$informes) {
            $tabla = '<tr><td colspan="10">
                        <div class="alert alert-info" style="padding: 20px; text-align: center;">
                            📋 No hay registros de cambios de precios
                        </div>
                    </td></tr>';
        } else {
            foreach ($informes as $informe) {
                $contenido = json_decode($informe['inf_config'], true);
                
                $tipo_cambio = $contenido['tipo_cambio'] == 'lote_individual' 
                    ? '<span class="badge" style="background: #3498db;">Individual</span>'
                    : '<span class="badge" style="background: #27ae60;">Todos los lotes</span>';
                
                $usuario = $informe['us_nombres'] . ' ' . ($informe['us_apellido_paterno'] ?? '');
                $medicamento = $informe['med_nombre_quimico'] ?? 'N/A';
                $sucursal = $informe['su_nombre'] ?? 'N/A';
                $fecha = date('d/m/Y H:i:s', strtotime($informe['inf_creado_en']));
                
                $precio_anterior = number_format($contenido['precio_anterior'], 2, ',', '.');
                $precio_nuevo = number_format($contenido['precio_nuevo'], 2, ',', '.');
                $cantidad = $contenido['cantidad_lotes_afectados'];

                $tabla .= "
                    <tr>
                        <td>$tipo_cambio</td>
                        <td><strong>$medicamento</strong></td>
                        <td>$sucursal</td>
                        <td><span style=\"color: #e74c3c;\">Bs $precio_anterior</span></td>
                        <td><span style=\"color: #27ae60;\">Bs $precio_nuevo</span></td>
                        <td>$cantidad</td>
                        <td>$usuario</td>
                        <td>$fecha</td>
                    </tr>
                ";
            }
        }

        // Calcular páginas
        $paginas = ceil($total / $registros);
        
        $html = "
            <div style='overflow-x: auto;'>
                <table class='tabla-dinamica-lista'>
                    <thead>
                        <tr>
                            <th>Tipo de Cambio</th>
                            <th>Medicamento</th>
                            <th>Sucursal</th>
                            <th>Precio Anterior</th>
                            <th>Precio Nuevo</th>
                            <th>Lotes Afectados</th>
                            <th>Usuario</th>
                            <th>Fecha/Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        $tabla
                    </tbody>
                </table>
            </div>
            <div class='paginador'>
                <button type='button' class='btn-paginador' onclick=\"goToPage(1)\">Primera</button>
        ";

        if ($pagina > 1) {
            $anterior = $pagina - 1;
            $html .= "<button type='button' class='btn-paginador' onclick=\"goToPage($anterior)\">← Anterior</button>";
        }

        for ($i = 1; $i <= $paginas; $i++) {
            $activa = ($i == $pagina) ? 'class="btn-paginador active"' : 'class="btn-paginador"';
            $html .= "<button type='button' $activa onclick=\"goToPage($i)\">$i</button>";
        }

        if ($pagina < $paginas) {
            $siguiente = $pagina + 1;
            $html .= "<button type='button' class='btn-paginador' onclick=\"goToPage($siguiente)\">Siguiente →</button>";
        }

        $html .= "
                <button type='button' class='btn-paginador' onclick=\"goToPage($paginas)\">Última</button>
                <span style='margin-left: 20px;'><strong>Mostrando:</strong> $inicio a " . ($inicio + count($informes)) . " de $total</span>
            </div>
        ";

        return $html;
    }
}
