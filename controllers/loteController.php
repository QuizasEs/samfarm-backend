<?php

if ($peticionAjax) {
    require_once '../models/loteModel.php';
} else {
    require_once './models/loteModel.php';
}

class loteController extends loteModel
{

    public function paginado_lote_controller($pagina, $registros, $url, $busqueda)
    {
        /* limpiamos cadenas para evitar injeccion */
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = '';

        /* validamos que el valor ingresado por url sea un numero */
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        if (isset($busqueda) && $busqueda != '') {
            /* busqueda */
            $consulta = "
            SELECT 
                SQL_CALC_FOUND_ROWS 
                lm.lm_id,
                lm.lm_numero_lote,
                m.med_nombre_quimico,
                m.med_principio_activo,
                p.pr_nombres,
                s.su_nombre,
                lm.lm_cantidad_inicial,
                lm.lm_cantidad_actual,
                lm.lm_precio_compra,
                lm.lm_precio_venta,
                lm.lm_fecha_ingreso,
                lm.lm_fecha_vencimiento,
                lm.lm_estado,
                lm.lm_creado_en,
                lm.lm_actualizado_en
            FROM lote_medicamento lm
            INNER JOIN medicamento m ON lm.med_id = m.med_id
            INNER JOIN sucursales s ON lm.su_id = s.su_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            WHERE (
                lm.lm_numero_lote LIKE '%$busqueda%' OR
                m.med_nombre_quimico LIKE '%$busqueda%' OR
                m.med_principio_activo LIKE '%$busqueda%' OR
                p.pr_nombres LIKE '%$busqueda%'
            )
            ORDER BY lm.lm_fecha_ingreso DESC 
            LIMIT $inicio, $registros
        ";
        } else {
            $consulta = "
            SELECT 
                SQL_CALC_FOUND_ROWS 
                lm.lm_id,
                lm.lm_numero_lote,
                m.med_nombre_quimico,
                m.med_principio_activo,
                p.pr_nombres,
                s.su_nombre,
                lm.lm_cantidad_inicial,
                lm.lm_cantidad_actual,
                lm.lm_precio_compra,
                lm.lm_precio_venta,
                lm.lm_fecha_ingreso,
                lm.lm_fecha_vencimiento,
                lm.lm_estado,
                lm.lm_creado_en,
                lm.lm_actualizado_en
            FROM lote_medicamento lm
            INNER JOIN medicamento m ON lm.med_id = m.med_id
            INNER JOIN sucursales s ON lm.su_id = s.su_id
            LEFT JOIN proveedores p ON lm.pr_id = p.pr_id
            WHERE lm.lm_estado IN ('en_espera', 'activo')
            ORDER BY lm.lm_fecha_ingreso DESC 
            LIMIT $inicio, $registros
        ";
        }

        /* realizamos la peticion a la base de datos */
        $conexion = mainModel::conectar();
        $datos = $conexion->query($consulta);
        $datos = $datos->fetchAll();

        /* obtenemos la cantidad total de registro */
        $total = $conexion->query('SELECT FOUND_ROWS()');
        $total = (int) $total->fetchColumn();

        /* numero de paginas por registros */
        $Npaginas = ceil($total / $registros);

        /* inicio de tabla */
        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>ESTADO</th>
                            <th>NÚMERO LOTE</th>
                            <th>MEDICAMENTO</th>
                            <th>PROVEEDOR</th>
                            <th>SUCURSAL</th>
                            <th>CANT. INICIAL</th>
                            <th>CANT. ACTUAL</th>
                            <th>PRECIO COMPRA</th>
                            <th>PRECIO VENTA</th>
                            <th>FECHA INGRESO</th>
                            <th>FECHA VENCIMIENTO</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;
            foreach ($datos as $rows) {
                // Determinar clase de estado según fecha de vencimiento
                $fecha_actual = date('Y-m-d');
                $fecha_vencimiento = $rows['lm_fecha_vencimiento'];
                $estado_class = '';
                $estado_texto = '';

                switch ($rows['lm_estado']) {
                    case 'en_espera':
                        $estado_class = 'estado-espera';
                        $estado_texto = 'En Espera';
                        break;
                    case 'activo':
                        $estado_class = 'estado-activo';
                        $estado_texto = 'Activo';
                        break;
                    case 'terminado':
                        $estado_class = 'estado-terminado';
                        $estado_texto = 'Terminado';
                        break;
                    case 'caducado':
                        $estado_class = 'estado-caducado';
                        $estado_texto = 'Caducado';
                        break;
                    default:
                        $estado_class = 'estado-desconocido';
                        $estado_texto = ucfirst($rows['lm_estado']);
                }

                // Alerta de vencimiento próximo (30 días)
                $dias_restantes = '';
                if ($fecha_vencimiento && $rows['lm_estado'] == 'activo') {
                    $diff = strtotime($fecha_vencimiento) - strtotime($fecha_actual);
                    $dias = floor($diff / (60 * 60 * 24));
                    if ($dias <= 30 && $dias > 0) {
                        $dias_restantes = '<br><small style="color: orange;">Vence en ' . $dias . ' días</small>';
                    } elseif ($dias <= 0) {
                        $dias_restantes = '<br><small style="color: red;">¡VENCIDO!</small>';
                    }
                }

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td>
                            ' . ($rows['lm_estado'] == "en_espera"
                    ? '<a href="' . SERVER_URL . 'loteActivar/' . mainModel::encryption($rows['lm_id']) . '/" class="btn-editar">Activar</a>'
                    : '<span class="badge-' . $rows['lm_estado'] . '">' . $estado_texto . '</span>') . '
                        </td>
                        <td>' . ($rows['lm_numero_lote'] ?? 'N/A') . '</td>
                        <td>' . $rows['med_nombre_quimico'] . '<br><small>' . $rows['med_principio_activo'] . '</small></td>
                        <td>' . ($rows['pr_nombres'] ?? 'N/A') . '</td>
                        <td>' . $rows['su_nombre'] . '</td>
                        <td>' . $rows['lm_cantidad_inicial'] . '</td>
                        <td><strong>' . $rows['lm_cantidad_actual'] . '</strong></td>
                        <td>Bs. ' . number_format($rows['lm_precio_compra'], 2) . '</td>
                        <td>Bs. ' . number_format($rows['lm_precio_venta'], 2) . '</td>
                        <td>' . date('d/m/Y', strtotime($rows['lm_fecha_ingreso'])) . '</td>
                        <td>' . ($fecha_vencimiento ? date('d/m/Y', strtotime($fecha_vencimiento)) : 'N/A') . $dias_restantes . '</td>
                        
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            if ($total >= 1) {
                /* en caso que la url no sea valida de una pagina con registros mostrara */
                $tabla .= ' <tr><td colspan="13"><a class="btn-primary" href="' . $url . '">Recargar</a></td></tr> ';
            } else {
                /* en caso que no tenga registrados ni un registro en la base de datos mostrara */
                $tabla .= ' <tr><td colspan="13">No hay registros</td></tr> ';
            }
        }

        /* final de tabla */
        $tabla .= '
                </tbody>
            </table>
        </div>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p>Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 5);
        }

        /* devolvemos tabla */
        return $tabla;
    }
}
