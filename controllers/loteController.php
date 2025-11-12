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

    public function agregar_lote_controller()
    {
        /* recibimos los datos del formulario */
        $id = $_POST['id'];

        $cantidad = mainModel::limpiar_cadena($_POST['Cantidad_real_reg']);
        $precio_venta = mainModel::limpiar_cadena($_POST['Precio_venta_reg']);
        $observacion = mainModel::limpiar_cadena($_POST['Observacion_reg']);
        /* recibimos el array de codigos */
        $codigos = $_POST['codigos'] ?? [];

        if ($id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado',
                'texto' => 'No se pudo identificar el lote a activar',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $consulta = self::datos_lote_controller($id);

        if ($consulta->rowCount() <= 0) {
            /* no se encontro el lote  */
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado',
                'texto' => 'No se pudo identificar el lote que decea activar',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        $id = mainModel::decryption($_POST['id'] ?? 0);
        $id = mainModel::limpiar_cadena($id);

        /* iniciamos sesion */


        $usuario_id = $_SESSION['id_smp'];
        $sucursal_id = $_SESSION['sucursal_smp'];

        if ($usuario_id != 1) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado',
                'texto' => 'no cuenta con los permisos necesarios para activar lotes',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $datos = $consulta->fetch(PDO::FETCH_ASSOC);

        /* validamos que los campos obligatorios no esten vacios */
        if ($cantidad == "" || $precio_venta == "") {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado',
                'texto' => 'Debe llenar todos los campos obligatorios',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* multiplicamos la cantidad de entrada por la cantidad por unidad */
        /* ejemplo  */
        /* caso 1: lote de 10 blister cada blister contiene 6 capsulas (unidades) 10*6 = 60 unidades totales */
        /* caso 2: lote de 10 jaraves cada jarabe tiene 1 frasco  10*1 10 unidades totales*/
        $cantidad_real = (int)$cantidad * (int)$datos['lm_cantidad_inicial'];
        /* verificamos que el lote este en estado en_espera para poder realizar el registro */
        if ($datos['lm_estado'] != "en_espera") {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio un error inesperado',
                'texto' => 'Solo se pueden activar lotes que esten en estado en espera',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $conexion = mainModel::conectar();

        try {
            $conexion->beginTransaction();

            /* actualizamos el estado del lote */
            $datos_lote = [
                'ID' => $id,
                /* registramos el nuevo precio de venta pero si este esta vacio se registra el anterior */
                'PrecioVenta' => $precio_venta === '' ? $datos['lm_precio_venta'] : $precio_venta,
                'CantidadActual' => $cantidad_real
            ];

            $lote_respuesta = loteModel::activar_lote_model($datos_lote);

            if ($lote_respuesta->rowCount() <= 0) {
                throw new Exception('No se pudo activar el lote, intente nuevamente mas tarde');
            }

            /* registramos los codigos */
            /* validamos que el array no este vacio */
            if (!empty($codigos)) {
                foreach ($codigos as $codigo) {
                    if (!empty($codigo)) {
                        $datos_codigo = [
                            'cb_codigo' => $codigo,
                            'lm_id' => $id
                        ];
                        $codigo_resultado = loteModel::registrar_codigo_model($datos_codigo);

                        if ($codigo_resultado->rowCount() <= 0) {
                            throw new Exception('No se pudo registrar el código: ' . $codigo);
                        }
                    }
                }
            }

            /* datos del historial */
            $datos_historial = [
                'LoteID' => $id,
                'UsuarioID' => $usuario_id,
                'Accion' => "activacion",
                'Descripcion' => "Activacion de lote: " . $datos['lm_numero_lote']
            ];

            /* registramos historial */
            $historial_respuesta = loteModel::registrar_hitorial_lote_model($datos_historial);

            /* preguntamos que se registro correctamente */
            if ($historial_respuesta->rowCount() <= 0) {
                throw new Exception('No se pudo registrar el historial del lote, intente nuevamente mas tarde');
            }

            /* actualizar inventario */
            $datos_inventario = [
                'LoteID' => $id,
                'MedID' => $datos['med_id'],
                'SuID' => $sucursal_id,
                'Cantidad' => (int)$cantidad_real,
                'CantidadMinima' => (int)($cantidad_real / 10),
                'CantidadMaxima' => (int)($cantidad_real * 2),
                'UltimoPrecio' => $datos['lm_precio_compra']
            ];

            $inventario_resultado = loteModel::actualizar_inventario_model($datos_inventario);

            if ($inventario_resultado->rowCount() <= 0) {
                throw new Exception('No se pudo actualizar el inventario, intente nuevamente mas tarde');
            }

            /* actualizamos movimiento de inventario */
            $datos_movimiento = [
                'LoteID' => $id,
                'MedID' => $datos['med_id'],
                'SucursalID' => $datos['su_id'],
                'UsuarioID' => $usuario_id,
                'Tipo' => 'entrada',
                'Cantidad' => $cantidad_real,
                'Unidad' => 'unidad',
                'RefTipo' => 'ingreso y activacion de lote',
                'RefID' => $id,
                'Motivo' => "Activación del lote {$datos['lm_numero_lote']}"
            ];

            $movimiento_resultado = loteModel::registrar_movimiento_inventario_model($datos_movimiento);

            if ($movimiento_resultado->rowCount() <= 0) {
                throw new Exception('No se pudo registrar el movimiento de inventario, intente nuevamente mas tarde');
            }

            $conexion->commit();

            /* si todo lo anterior es correcto entonces se muestra mensaje de completado */
            echo json_encode([
                'Alerta' => 'recargar',
                'Titulo' => 'Lote Activado',
                'texto' => 'El lote ha sido activado correctamente con ' . $cantidad_real . ' unidades y ' . count($codigos) . ' códigos registrados.',
                'Tipo' => 'success'
            ]);
            exit();
        } catch (Exception $e) {
            $conexion->rollBack();

            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error en la transacción',
                'texto' => $e->getMessage(),
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }
}
