<?php

if ($peticionAjax) {
    require_once "../models/devolucionModel.php";
} else {
    require_once "./models/devolucionModel.php";
}

class devolucionController extends devolucionModel
{
    public function buscar_venta_controller()
    {
        $criterio = mainModel::limpiar_cadena($_POST['criterio'] ?? '');
        $valor = mainModel::limpiar_cadena($_POST['valor'] ?? '');

        if (empty($criterio) || empty($valor)) {
            return json_encode([
                'error' => true,
                'mensaje' => 'Debe especificar criterio y valor de búsqueda'
            ], JSON_UNESCAPED_UNICODE);
        }

        $criterios_validos = ['fa_id', 've_id', 'numero_documento', 'numero_factura'];
        if (!in_array($criterio, $criterios_validos)) {
            return json_encode([
                'error' => true,
                'mensaje' => 'Criterio de búsqueda inválido'
            ], JSON_UNESCAPED_UNICODE);
        }

        $stmt = self::buscar_venta_model($criterio, $valor);

        if (!$stmt || $stmt->rowCount() <= 0) {
            return json_encode([
                'error' => true,
                'mensaje' => 'No se encontró ninguna venta con ese criterio'
            ], JSON_UNESCAPED_UNICODE);
        }

        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($venta['ve_estado_documento'] === 'devuelto') {
            return json_encode([
                'error' => true,
                'mensaje' => 'Esta venta ya fue devuelta anteriormente'
            ], JSON_UNESCAPED_UNICODE);
        }

        $stmt_detalle = self::obtener_detalle_venta_model($venta['ve_id']);
        $items = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

        $items_formateados = array_map(function ($item) {
            return [
                'dv_id' => $item['dv_id'],
                'med_id' => $item['med_id'],
                'lm_id' => $item['lm_id'],
                'nombre' => $item['med_nombre_quimico'],
                'principio_activo' => $item['med_principio_activo'],
                'presentacion' => $item['med_presentacion'],
                'forma' => $item['forma_farmaceutica'],
                'laboratorio' => $item['laboratorio'],
                'lote' => $item['lm_numero_lote'],
                'cantidad' => $item['dv_cantidad'],
                'unidad' => $item['dv_unidad'],
                'precio_unitario' => $item['dv_precio_unitario'],
                'descuento' => $item['dv_descuento'],
                'subtotal' => $item['dv_subtotal'],
                'estado' => $item['dv_estado'],
                'stock_lote' => $item['lm_cant_actual_unidades']
            ];
        }, $items);

        $nombre_cliente = trim(
            ($venta['cl_nombres'] ?? '') . ' ' .
                ($venta['cl_apellido_paterno'] ?? '') . ' ' .
                ($venta['cl_apellido_materno'] ?? '')
        );
        if (empty($nombre_cliente)) {
            $nombre_cliente = 'Cliente General';
        }

        $response = [
            'error' => false,
            'venta' => [
                've_id' => $venta['ve_id'],
                'fa_id' => $venta['fa_id'],
                'su_id' => $venta['su_id'],
                'numero_documento' => $venta['ve_numero_documento'],
                'numero_factura' => $venta['fa_numero'],
                'fecha' => date('d/m/Y H:i', strtotime($venta['ve_fecha_emision'])),
                'cliente' => $nombre_cliente,
                'carnet' => $venta['cl_carnet'] ?? 'S/N',
                'subtotal' => $venta['ve_subtotal'],
                'total' => $venta['ve_total'],
                'sucursal' => $venta['su_nombre'],
                'vendedor' => trim($venta['us_nombres'] . ' ' . $venta['us_apellido_paterno'])
            ],
            'items' => $items_formateados
        ];

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function procesar_devolucion_controller()
    {
        if (!isset($_SESSION['id_smp']) || !isset($_SESSION['sucursal_smp'])) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Sesión inválida",
                "texto" => "No hay sesión válida",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $usuario_id = (int)$_SESSION['id_smp'];
        $sucursal_usuario = (int)$_SESSION['sucursal_smp'];

        $ve_id = isset($_POST['ve_id']) ? (int)$_POST['ve_id'] : 0;
        $fa_id = isset($_POST['fa_id']) ? (int)$_POST['fa_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;
        $items_json = $_POST['items_devolucion'] ?? '[]';
        $items = json_decode($items_json, true);

        if ($ve_id <= 0 || $fa_id <= 0 || $su_id <= 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Datos inválidos",
                "texto" => "Faltan datos de la venta",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (!is_array($items) || count($items) === 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Sin productos",
                "texto" => "Debe seleccionar al menos un producto para devolver",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        if ($rol_usuario == 2 && $su_id != $sucursal_usuario) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Acceso denegado",
                "texto" => "No puede procesar devoluciones de otras sucursales",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        if (self::verificar_venta_ya_devuelta_model($ve_id)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Venta ya devuelta",
                "texto" => "Esta venta ya fue procesada como devolución",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $db = mainModel::conectar();

        try {
            $db->beginTransaction();

            $total_devolucion = 0;
            $cantidad_total = 0;
            $motivo_general = '';

            foreach ($items as $item) {
                $dv_id = isset($item['dv_id']) ? (int)$item['dv_id'] : 0;
                $med_id = isset($item['med_id']) ? (int)$item['med_id'] : 0;
                $lm_id = isset($item['lm_id']) ? (int)$item['lm_id'] : 0;
                $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;
                $motivo = mainModel::limpiar_cadena($item['motivo'] ?? '');
                $tipo = mainModel::limpiar_cadena($item['tipo'] ?? 'devolucion');
                $precio_unitario = isset($item['precio_unitario']) ? (float)$item['precio_unitario'] : 0;

                if ($dv_id <= 0 || $med_id <= 0 || $cantidad <= 0) {
                    throw new Exception("Datos de item inválidos");
                }

                if (empty($motivo)) {
                    throw new Exception("Debe especificar el motivo de la devolución");
                }

                $tipos_validos = ['devolucion', 'cambio'];
                if (!in_array($tipo, $tipos_validos)) {
                    throw new Exception("Tipo de devolución inválido");
                }

                if (empty($motivo_general)) {
                    $motivo_general = $motivo;
                }

                $total_devolucion += ($cantidad * $precio_unitario);
                $cantidad_total += $cantidad;

                $datos_baja = [
                    'lm_id' => $lm_id,
                    'med_id' => $med_id,
                    'su_id' => $su_id,
                    'us_id' => $usuario_id,
                    'mi_cantidad' => $cantidad,
                    'mi_unidad' => 'unidad',
                    'mi_referencia_id' => 0,
                    'mi_motivo' => "Devolución: {$motivo}"
                ];

                $baja_result = self::registrar_movimiento_baja_model($datos_baja);
                if (!$baja_result || $baja_result->rowCount() <= 0) {
                    throw new Exception("No se pudo registrar movimiento de baja");
                }

                $descuento_lote_ok = self::descontar_lote_devolucion_model($lm_id, $cantidad);
                if (!$descuento_lote_ok) {
                    throw new Exception("No se pudo descontar del lote medicamento");
                }

                $inv_ok = self::descontar_inventario_consolidado_devolucion_model($med_id, $su_id, $cantidad, $precio_unitario);
                if (!$inv_ok) {
                    throw new Exception("No se pudo actualizar inventario consolidado en devolución");
                }

                if ($tipo === 'cambio') {
                    $stmt_lotes = self::obtener_lotes_disponibles_model($med_id, $su_id);
                    $lotes_disponibles = $stmt_lotes->fetchAll(PDO::FETCH_ASSOC);

                    if (count($lotes_disponibles) === 0) {
                        throw new Exception("No hay lotes disponibles para el cambio del medicamento ID: {$med_id}");
                    }

                    $cantidad_pendiente = $cantidad;
                    foreach ($lotes_disponibles as $lote) {
                        if ($cantidad_pendiente <= 0) break;

                        $lm_cambio_id = (int)$lote['lm_id'];
                        $stock_disponible = (int)$lote['lm_cant_actual_unidades'];

                        $cantidad_usar = min($stock_disponible, $cantidad_pendiente);

                        $descuento_ok = self::descontar_lote_cambio_model($lm_cambio_id, $cantidad_usar);
                        if (!$descuento_ok) {
                            throw new Exception("No se pudo descontar del lote {$lm_cambio_id}");
                        }

                        $datos_cambio = [
                            'lm_id' => $lm_cambio_id,
                            'med_id' => $med_id,
                            'su_id' => $su_id,
                            'us_id' => $usuario_id,
                            'mi_cantidad' => $cantidad_usar,
                            'mi_unidad' => 'unidad',
                            'mi_referencia_id' => 0,
                            'mi_motivo' => "Cambio por devolución: {$motivo}"
                        ];

                        $cambio_result = self::registrar_movimiento_cambio_model($datos_cambio);
                        if (!$cambio_result || $cambio_result->rowCount() <= 0) {
                            throw new Exception("No se pudo registrar movimiento de cambio");
                        }

                        $cantidad_pendiente -= $cantidad_usar;
                    }

                    if ($cantidad_pendiente > 0) {
                        throw new Exception("Stock insuficiente para completar el cambio");
                    }
                }

                $det_result = self::actualizar_estado_detalle_venta_model($dv_id);
                if (!$det_result || $det_result->rowCount() <= 0) {
                    throw new Exception("No se pudo actualizar estado del detalle de venta");
                }
            }

            $datos_devolucion = [
                've_id' => $ve_id,
                'fa_id' => $fa_id,
                'su_id' => $su_id,
                'us_id' => $usuario_id,
                'dev_total' => $total_devolucion,
                'dev_cantidad' => $cantidad_total,
                'dev_motivo' => $motivo_general
            ];

            $dev_id = self::insertar_devolucion_model($datos_devolucion);
            if ($dev_id <= 0) {
                throw new Exception("No se pudo registrar la devolución");
            }

            $db->exec("UPDATE movimiento_inventario SET mi_referencia_id = {$dev_id} WHERE mi_referencia_id = 0 AND mi_referencia_tipo IN ('devolucion', 'cambio') AND us_id = {$usuario_id}");

            $venta_result = self::actualizar_estado_venta_model($ve_id);
            if (!$venta_result || $venta_result->rowCount() <= 0) {
                throw new Exception("No se pudo actualizar estado de la venta");
            }

            $config_informe = [
                'dev_id' => $dev_id,
                've_id' => $ve_id,
                'fa_id' => $fa_id,
                'usuario_id' => $usuario_id,
                'sucursal_id' => $su_id,
                'items' => $items,
                'total_devolucion' => $total_devolucion,
                'cantidad_items' => $cantidad_total,
                'motivo' => $motivo_general,
                'fecha' => date('Y-m-d H:i:s')
            ];

            $datos_informe = [
                'inf_nombre' => "Devolución #{$dev_id} - Venta #{$ve_id}",
                'inf_usuario' => $usuario_id,
                'inf_config' => json_encode($config_informe, JSON_UNESCAPED_UNICODE)
            ];

            $informe_result = self::agregar_informe_devolucion_model($datos_informe);
            if (!$informe_result || $informe_result->rowCount() <= 0) {
                error_log("WARNING: No se pudo registrar informe de devolución");
            }

            $db->commit();

            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Devolución procesada",
                "texto" => "La devolución se registró correctamente",
                "Tipo" => "success"
            ];
            echo json_encode($alerta);
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error en procesar_devolucion_controller: " . $e->getMessage());

            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error al procesar",
                "texto" => $e->getMessage(),
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    public function obtener_lotes_cambio_controller()
    {
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($med_id <= 0 || $su_id <= 0) {
            return json_encode([
                'error' => true,
                'mensaje' => 'Parámetros inválidos'
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            $stmt = self::obtener_lotes_disponibles_model($med_id, $su_id);
            $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($lotes) === 0) {
                return json_encode([
                    'error' => true,
                    'mensaje' => 'No hay lotes disponibles para cambio'
                ], JSON_UNESCAPED_UNICODE);
            }

            $lotes_formateados = array_map(function ($lote) {
                return [
                    'lm_id' => $lote['lm_id'],
                    'numero_lote' => $lote['lm_numero_lote'],
                    'stock' => $lote['lm_cant_actual_unidades'],
                    'precio' => $lote['lm_precio_venta'],
                    'vencimiento' => $lote['lm_fecha_vencimiento']
                ];
            }, $lotes);

            return json_encode([
                'error' => false,
                'lotes' => $lotes_formateados
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_lotes_cambio_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar lotes disponibles'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
