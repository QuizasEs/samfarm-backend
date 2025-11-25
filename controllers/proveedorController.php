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
                        <h3>⛔ Acceso Denegado</h3>
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
            error_log("⌚ ERROR SQL: " . $e->getMessage());
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
                            <th>PROVEEDOR</th>
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
                $nombre_completo = trim(($row['pr_nombres'] ?? '') . ' ' . ($row['pr_apellido_paterno'] ?? '') . ' ' . ($row['pr_apellido_materno'] ?? ''));

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
}
