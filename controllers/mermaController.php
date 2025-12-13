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
}
