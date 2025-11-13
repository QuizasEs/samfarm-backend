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
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);
        return loteModel::datos_lote_model($id);
    }
    public function paginado_lote_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "")
    {
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

        // Búsqueda por término
        if (isset($busqueda) && $busqueda != '') {
            $b = $busqueda;
            $whereParts[] = "(
            lm.lm_numero_lote LIKE '%$b%' OR
            m.med_nombre_quimico LIKE '%$b%' OR
            m.med_principio_activo LIKE '%$b%' OR
            p.pr_nombres LIKE '%$b%'
        )";
        }

        // filtro select1 (ej: estado)
        if ($f1 !== '') {
            // si es lista de valores separados por comas se puede adaptar; aquí simple igualdad o IN
            // suponemos que select1 corresponde a lm.lm_estado
            $whereParts[] = "lm.lm_estado = '$f1'";
        }

        // filtro select2 (ej: si data-type="fecha", se envió el número de mes)
        if ($f2 !== '') {
            // Si el select2 viene marcado como fecha (ej: mes), intenta filtrar por MONTH(lm_fecha_ingreso)
            // Permitimos que el valor pueda ser "YYYY" o "MM" o "YYYY-MM"
            if (is_numeric($f2)) {
                // mes (1..12)
                $mes = (int)$f2;
                if ($mes >= 1 && $mes <= 12) {
                    $whereParts[] = "MONTH(lm.lm_fecha_ingreso) = $mes";
                } else {
                    // si viene un año
                    $whereParts[] = "YEAR(lm.lm_fecha_ingreso) = " . intval($f2);
                }
            } else {
                // fallback: intentar YEAR-MONTH
                if (preg_match('/^\d{4}-\d{2}$/', $f2)) {
                    $parts = explode('-', $f2);
                    $y = intval($parts[0]);
                    $m = intval($parts[1]);
                    $whereParts[] = "(YEAR(lm.lm_fecha_ingreso) = $y AND MONTH(lm.lm_fecha_ingreso) = $m)";
                }
            }
        }

        // filtro select3 (ej: sucursal su_id)
        if ($f3 !== '') {
            // supondremos que corresponde a su_id
            $whereParts[] = "lm.su_id = " . intval($f3);
        }

        // Si no hay búsqueda ni filtros, aplicar filtro por defecto (en_espera y activo)
        if (count($whereParts) === 0) {
            $whereSQL = "WHERE lm.lm_estado IN ('en_espera','activo')";
        } else {
            $whereSQL = "WHERE " . implode(' AND ', $whereParts);
        }

        // Consulta principal con SQL_CALC_FOUND_ROWS
        $consulta = "
        SELECT 
            SQL_CALC_FOUND_ROWS 
            lm.lm_id,
            lm.lm_numero_lote,
            m.med_nombre_quimico,
            m.med_principio_activo,
            p.pr_nombres,
            s.su_nombre,
            lm.lm_cant_caja AS lm_cantidad_inicial,
            lm.lm_cant_actual_unidades AS lm_cantidad_actual,
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
        $whereSQL
        ORDER BY lm.lm_fecha_ingreso DESC 
        LIMIT $inicio, $registros
    ";

        $conexion = mainModel::conectar();
        $datosStmt = $conexion->query($consulta);
        $datos = $datosStmt->fetchAll();

        $totalStmt = $conexion->query('SELECT FOUND_ROWS()');
        $total = (int) $totalStmt->fetchColumn();

        $Npaginas = ceil($total / $registros);

        // inicio de tabla (igual que tu versión)
        $tabla .= '
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>N° LOTE</th>
                        <th>MEDICAMENTO</th>
                        <th>PROVEEDOR</th>
                        <th>SUCURSAL</th>
                        <th>CANT. INICIAL</th>
                        <th>CANT. ACTUAL</th>
                        <th>PRECIO COMPRA</th>
                        <th>PRECIO VENTA</th>
                        <th>FECHA INGRESO</th>
                        <th>FECHA VENCIMIENTO</th>
                        <th>ESTADO</th>
                        <th>ACCIONES</th>
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
                    case 'devuelto':
                        $estado_class = 'estado-devuelto';
                        $estado_texto = 'Devuelto';
                        break;
                    case 'bloqueado':
                        $estado_class = 'estado-bloqueado';
                        $estado_texto = 'Bloqueado';
                        break;
                    default:
                        $estado_class = 'estado-desconocido';
                        $estado_texto = ucfirst($rows['lm_estado']);
                }

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
                    <td>
                        ' .
                    ($rows['lm_estado'] == "en_espera"
                        ? '<a href="' . SERVER_URL . 'loteActivar/' . mainModel::encryption($rows['lm_id']) . '/" class="btn-editar">Activar</a>'
                        : '<span class="badge-' . $rows['lm_estado'] . '">' . $estado_texto . '</span>')
                    . '
                    </td>
                    <td class="buttons">
                        <a href="' . SERVER_URL . 'loteActualizar/' . mainModel::encryption($rows['lm_id']) . '/" class="btn default">EDITAR</a>
                    </td>
                </tr>
            ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            if ($total >= 1) {
                $tabla .= ' <tr><td colspan="13"><a class="btn-primary" href="' . $url . '">Recargar</a></td></tr> ';
            } else {
                $tabla .= ' <tr><td colspan="13">No hay registros</td></tr> ';
            }
        }

        $tabla .= '
            </tbody>
            </table>
        </div>
    ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p>Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            // mainModel::paginador_tablas_main debe usarse (ya adaptado)
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }



    public function actualizar_lote_controller()
    {
        $id = $_POST['id'];


        /* Obtenemos la información del lote */
        $datos = self::datos_lote_controller($id);
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
        $id = mainModel::decryption($_POST['id']);
        $id = mainModel::limpiar_cadena($id);

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
        $cant_blister = (int)mainModel::limpiar_cadena($_POST['Cantidad_blister_up'] ?? $lote['lm_cant_blister']);
        $cant_unidad  = (int)mainModel::limpiar_cadena($_POST['Cantidad_unidades_up'] ?? $lote['lm_cant_unidad']);
        $precio_compra = (float)mainModel::limpiar_cadena($_POST['Precio_compra_up'] ?? $lote['lm_precio_compra']);
        $precio_venta = (float)mainModel::limpiar_cadena($_POST['Precio_venta_up'] ?? $lote['lm_precio_venta']);
        $fecha_vencimiento = mainModel::limpiar_cadena($_POST['Fecha_vencimiento_up'] ?? $lote['lm_fecha_vencimiento']);

        /* Validación de datos requeridos */
        if ($precio_venta <= 0 || empty($fecha_vencimiento)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'Debe ingresar al menos el precio de venta y la fecha de vencimiento',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Estructura para actualizar */
        $datos_up = [
            'lm_cant_blister' => $cant_blister,
            'lm_cant_unidad' => $cant_unidad,
            'lm_precio_compra' => $precio_compra,
            'lm_precio_venta' => $precio_venta,
            'lm_fecha_vencimiento' => $fecha_vencimiento,
            'lm_origen_id' => $lote['lm_origen_id'] ?? null,
            'ID' => $id
        ];

        $actualizado = loteModel::actualizar_lote_model($datos_up);

        if ($actualizado->rowCount() == 1) {
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
                'Titulo' => 'Ocurrio un error inesperado',
                'texto' => 'Solo se pueden activar lotes que esten en estado en espera',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }
}
