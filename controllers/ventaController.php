<?php

if ($peticionAjax) {
    require_once "../models/ventaModel.php";
    require_once "../models/siatModel.php";
} else {
    require_once "./models/ventaModel.php";
    require_once "./models/siatModel.php";
}

class ventaController extends ventaModel
{

    private function validar_usuario_activo()
    {
        $usuario_id = $_SESSION['id_smp'] ?? 0;

        if ($usuario_id <= 0) {
            return false;
        }

        $db = mainModel::conectar();
        $stmt = $db->prepare("SELECT us_estado FROM usuarios WHERE us_id = :us_id");
        $stmt->bindParam(":us_id", $usuario_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() <= 0) {
            return false;
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$user['us_estado'] === 1;
    }

    /* controlador que busca al cliente */

    public function buscar_cliente_controller($termino)
    {
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se ha asignado una sucursal"
            ], JSON_UNESCAPED_UNICODE);
        }

        $termino = mainModel::limpiar_cadena($termino);

        if (strlen($termino) < 1) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        $rows = self::buscar_cliente_model($termino);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }

    public function select_v2_controller($tabla, $campos, $termino) {
        $termino = mainModel::limpiar_cadena($termino);
        if (strlen($termino) < 2) return json_encode([], JSON_UNESCAPED_UNICODE);
        return json_encode(mainModel::select_v2_model($tabla, $campos, $termino), JSON_UNESCAPED_UNICODE);
    }


    public function buscar_medicamento_controller($termino, $filtros = [])
    {
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se ha asignado una sucursal"
            ], JSON_UNESCAPED_UNICODE);
        }

        $sucursal_id = (int)$_SESSION['sucursal_smp'];
        $termino = mainModel::limpiar_cadena($termino);

        if (strlen($termino) < 1) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        $filtros_limpios = [];

        if (!empty($filtros['presentacion'])) {
            $filtros_limpios['presentacion'] = (int)mainModel::limpiar_cadena($filtros['presentacion']);
        }
        if (!empty($filtros['funcion'])) {
            $filtros_limpios['funcion'] = (int)mainModel::limpiar_cadena($filtros['funcion']);
        }
        if (!empty($filtros['via'])) {
            $filtros_limpios['via'] = (int)mainModel::limpiar_cadena($filtros['via']);
        }
        if (!empty($filtros['proveedor'])) {
            $filtros_limpios['proveedor'] = (int)mainModel::limpiar_cadena($filtros['proveedor']);
        }

        $rows = self::buscar_medicamento_model($termino, $sucursal_id, $filtros_limpios);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }

    public function buscar_medicamento_agrupado_controller($termino, $sucursal_id, $filtros = [])
    {
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se ha asignado una sucursal"
            ], JSON_UNESCAPED_UNICODE);
        }

        $sucursal_id = (int)$_SESSION['sucursal_smp'];
        $termino = mainModel::limpiar_cadena($termino);

        if (strlen($termino) < 1) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        $filtros_limpios = [];

        if (!empty($filtros['presentacion'])) {
            $filtros_limpios['presentacion'] = (int)mainModel::limpiar_cadena($filtros['presentacion']);
        }
        if (!empty($filtros['funcion'])) {
            $filtros_limpios['funcion'] = (int)mainModel::limpiar_cadena($filtros['funcion']);
        }
        if (!empty($filtros['via'])) {
            $filtros_limpios['via'] = (int)mainModel::limpiar_cadena($filtros['via']);
        }
        if (!empty($filtros['proveedor'])) {
            $filtros_limpios['proveedor'] = (int)mainModel::limpiar_cadena($filtros['proveedor']);
        }

        $rows = self::buscar_medicamento_agrupado_model($termino, $sucursal_id, $filtros_limpios);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }


    public function mas_vendidos_controller($limit = 5)
    {
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        $sucursal_id = (int)$_SESSION['sucursal_smp'];
        $limit = (int)mainModel::limpiar_cadena($limit);

        if ($limit <= 0 || $limit > 20) {
            $limit = 5;
        }

        $rows = self::top_ventas_model($sucursal_id, $limit);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }

    public function consulta_caja_controller()
    {
        if (isset($_SESSION['id_smp']) && in_array($_SESSION['rol_smp'], [1, 2, 3])) {
            $datos = [
                "us_id" => $_SESSION['id_smp'],
                "su_id" => $_SESSION['sucursal_smp']
            ];
            $respuesta = self::consulta_caja_model($datos);

            if ($respuesta->rowCount() <= 0) {
                return false;
            } else {
                return true;
            }
        }
    }


    public function abrir_caja_controller()
    {

        if (!$this->validar_usuario_activo()) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Usuario inactivo',
                'texto' => 'Tu cuenta está desactivada. Contacta al administrador.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $saldo_inicial = mainModel::limpiar_cadena($_POST['saldo_inicial']);
        $usuario_id = $_SESSION['id_smp'];
        $sucursal_id = $_SESSION['sucursal_smp'];

        if ($usuario_id === 1) {
            $nombre = "Caja Administrador";
        } else {
            $nombre = "Caja " . $_SESSION['nombre_smp'];
        }

        if (empty($saldo_inicial) || !is_numeric($saldo_inicial) || $saldo_inicial < 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incorrectos',
                'texto' => 'Ingrese un monto inicial válido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $check_sql = mainModel::conectar()->prepare("
            SELECT caja_id 
            FROM caja 
            WHERE us_id = :us_id 
            AND su_id = :su_id 
            AND caja_activa = 1
        ");
        $check_sql->bindParam(":us_id", $usuario_id);
        $check_sql->bindParam(":su_id", $sucursal_id);
        $check_sql->execute();

        if ($check_sql->rowCount() > 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Caja ya abierta',
                'texto' => 'Ya tienes una caja activa',
                'Tipo' => 'warning'
            ];
            echo json_encode($alerta);
            exit();
        }

        $datos_caja = [
            "su_id" => $sucursal_id,
            "us_id" => $usuario_id,
            "caja_saldo_inicial" => $saldo_inicial,
            "caja_nombre" => $nombre
        ];

        $caja_respuesta = self::abrir_caja_model($datos_caja);

        if ($caja_respuesta->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo abrir la caja',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $alerta = [
            'Alerta' => 'recargar',
            'Titulo' => 'Caja abierta',
            'texto' => 'La caja se abrió correctamente',
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }


    public function registrar_venta_controller()
    {
        error_log("Iniciando registrar_venta_controller");

        if (!$this->validar_usuario_activo()) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Usuario inactivo',
                'texto' => 'Tu cuenta está desactivada. Contacta al administrador.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (!isset($_SESSION['id_smp']) || !isset($_SESSION['sucursal_smp'])) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sesión inválida', 'texto' => 'No hay sesión válida', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $usuario_id = (int)$_SESSION['id_smp'];
        $sucursal_id = (int)$_SESSION['sucursal_smp'];

        $sucursal_check = mainModel::conectar()->prepare("SELECT su_estado FROM sucursales WHERE su_id = :su_id LIMIT 1");
        $sucursal_check->bindParam(':su_id', $sucursal_id, PDO::PARAM_INT);
        $sucursal_check->execute();

        if ($sucursal_check->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sucursal inválida', 'texto' => 'La sucursal no existe', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $sucursal = $sucursal_check->fetch(PDO::FETCH_ASSOC);
        if ((int)$sucursal['su_estado'] !== 1) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sucursal desactivada', 'texto' => 'No puedes realizar ventas en una sucursal desactivada', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $caja_stmt = self::consulta_caja_model(['us_id' => $usuario_id, 'su_id' => $sucursal_id]);
        if ($caja_stmt->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Caja cerrada', 'texto' => 'No tienes una caja activa', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $caja = $caja_stmt->fetch(PDO::FETCH_ASSOC);
        $caja_id = (int)$caja['caja_id'];

        $venta_items_json = $_POST['venta_items_json'] ?? '[]';
        $venta_items = json_decode($venta_items_json, true);
        $subtotal = isset($_POST['subtotal_venta']) ? (float) $_POST['subtotal_venta'] : 0.0;
        $total = isset($_POST['total_venta']) ? (float) $_POST['total_venta'] : 0.0;
        $dinero_recibido = isset($_POST['dinero_recibido_venta']) ? (float) $_POST['dinero_recibido_venta'] : 0.0;
        $cliente_id = isset($_POST['cliente_id']) && is_numeric($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago_venta'] ?? '');
        $documento = mainModel::limpiar_cadena($_POST['documento_venta'] ?? '');

        if ($metodo_pago == "" || !in_array($metodo_pago, ["targeta", "QR", "efectivo"])) {
            $metodo_pago = "efectivo";
        }
        if ($documento == "" || !in_array($documento, ["factura", "nota de venta"])) {
            $documento = "nota de venta";
        }

        if (!is_array($venta_items) || count($venta_items) === 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sin productos', 'texto' => 'Agrega al menos un producto', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
        if ($total <= 0 || $subtotal <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Totales inválidos', 'texto' => 'Los totales son inválidos', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $db = mainModel::conectar();
        try {
            error_log("Iniciando bloque try");
            $db->beginTransaction();

            $ve_numero_documento = self::generar_numero_venta_model($sucursal_id);
            $datos_venta = [
                "ve_numero_documento" => $ve_numero_documento,
                "cl_id" => $cliente_id,
                "us_id" => $usuario_id,
                "su_id" => $sucursal_id,
                "ve_subtotal" => $subtotal,
                "ve_impuesto" => 0.00,
                "ve_total" => $total,
                "ve_tipo_documento" => $documento,
                "ve_metodo_pago" => $metodo_pago,
                "caja_id" => $caja_id
            ];
            $ve_id = self::guardar_venta_model($datos_venta);
            if ($ve_id <= 0) throw new Exception("No se pudo registrar la venta");

            error_log("Iniciando bucle foreach");
            foreach ($venta_items as $item) {
                error_log("Procesando item: " . print_r($item, true));
                $med_id = isset($item['med_id']) ? (int)$item['med_id'] : 0;
                $lm_id_solicitado = isset($item['lote_id']) ? (int)$item['lote_id'] : 0;
                $cajas = isset($item['cajas']) ? (int)$item['cajas'] : 0;
                $unidades = isset($item['unidades']) ? (int)$item['unidades'] : 0;
                $precio_unitario = isset($item['precio']) ? (float)$item['precio'] : 0.0;
                $descuento_item = isset($item['descuento']) ? (float)$item['descuento'] : 0.00;

                if ($med_id <= 0 || ($cajas <= 0 && $unidades <= 0) || $precio_unitario <= 0) {
                    throw new Exception("Ítem inválido");
                }

                // Obtener factor de conversión para este medicamento
                $factor = self::obtener_factor_unidades_por_tipo_model($med_id, $sucursal_id);
                if (!$factor) {
                    throw new Exception("No se pudo obtener factor de conversión para med_id {$med_id}");
                }
                
                $unidades_por_caja = $factor['unidades_por_caja'];
                $unidades_por_blister = $factor['unidades_por_blister'];

                // Calcular unidades totales requeridas
                $unidades_requeridas = ($cajas * $unidades_por_caja) + $unidades;
                $stock_total = self::sumar_stock_lotes_med_sucursal_model($med_id, $sucursal_id);

                if ($stock_total < $unidades_requeridas) {
                    throw new Exception("Stock insuficiente para med_id {$med_id}");
                }

                $valorado_total_descuento = 0;

                if ($lm_id_solicitado > 0) {
                    $db_check = mainModel::conectar();
                    $stmt_check = $db_check->prepare("
                        SELECT lm_id, lm_cant_actual_unidades, lm_precio_compra, lm_precio_venta, lm_precio_min_u
                        FROM lote_medicamento
                        WHERE lm_id = :lm_id AND med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo'
                    ");
                    $stmt_check->bindParam(":lm_id", $lm_id_solicitado, PDO::PARAM_INT);
                    $stmt_check->bindParam(":med_id", $med_id, PDO::PARAM_INT);
                    $stmt_check->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
                    $stmt_check->execute();

                    if ($stmt_check->rowCount() <= 0) {
                        throw new Exception("Lote solicitado no existe o está inactivo: lm_id {$lm_id_solicitado}");
                    }

                    $lm = $stmt_check->fetch(PDO::FETCH_ASSOC);
                    $lm_id = (int)$lm['lm_id'];
                    $lm_disp = (int)$lm['lm_cant_actual_unidades'];
                    $lm_precio_compra = (float)$lm['lm_precio_compra'];

                    /* validar precio minimo del lote */
                    if ($lm['lm_precio_min_u'] && $precio_unitario < $lm['lm_precio_min_u']) {
                        throw new Exception("El precio de venta ({$precio_unitario}) es menor al precio minimo permitido ({$lm['lm_precio_min_u']}) para el lote seleccionado");
                    }

                    if ($lm_disp < $unidades_requeridas) {
                        throw new Exception("Stock insuficiente en el lote seleccionado: {$lm_id}");
                    }

                    $detalle = [
                        "ve_id" => $ve_id,
                        "med_id" => $med_id,
                        "lm_id" => $lm_id,
                        "dv_cantidad" => $unidades_requeridas,
                        "dv_unidad" => 'unidad',
                        "dv_precio_unitario" => $precio_unitario,
                        "dv_descuento" => $descuento_item,
                        "dv_subtotal" => $unidades_requeridas * $precio_unitario - $descuento_item
                    ];
                    $det_res = self::agregar_detalle_venta_model($detalle);
                    if (!$det_res || $det_res->rowCount() <= 0) throw new Exception("No se pudo registrar detalle de venta");

                    $ok = self::descontar_unidades_lote_model($lm_id, $unidades_requeridas);
                    if (!$ok) throw new Exception("No se pudo actualizar lote {$lm_id}");

                    self::verificar_estado_lote_terminado_model($lm_id);

                    $temp_ref_venta = "temp_venta_" . $usuario_id . "_" . $ve_id . "_" . $lm_id . "_" . time() . "_" . mt_rand(1000, 9999);

                    $mov_inv = [
                        "lm_id" => $lm_id,
                        "med_id" => $med_id,
                        "su_id" => $sucursal_id,
                        "us_id" => $usuario_id,
                        "mi_tipo" => "salida",
                        "mi_cantidad" => $unidades_requeridas,
                        "mi_unidad" => "unidad",
                        "mi_referencia_tipo" => "venta",
                        "mi_referencia_id" => $temp_ref_venta,
                        "mi_motivo" => "Venta {$ve_numero_documento} (lm_id {$lm_id})"
                    ];
                    $mov_res = self::agregar_movimiento_inventario_model($mov_inv);
                    if (!$mov_res || $mov_res->rowCount() <= 0) throw new Exception("No se pudo registrar movimiento_inventario");

                    $valorado_total_descuento = $unidades_requeridas * $lm_precio_compra;

                } else {
                    $remaining = $unidades_requeridas;
                    $lotes = self::obtener_lotes_activos_por_med_sucursal_model($med_id, $sucursal_id);

                    foreach ($lotes as $lm) {
                        if ($remaining <= 0) break;
                        $lm_id = (int)$lm['lm_id'];
                        $lm_disp = (int)$lm['lm_cant_actual_unidades'];
                        if ($lm_disp <= 0) continue;
                        $take = min($lm_disp, $remaining);
                        $lm_precio_compra = (float)$lm['lm_precio_compra'];

                        /* validar precio minimo del lote */
                        if ($lm['lm_precio_min_u'] && $precio_unitario < $lm['lm_precio_min_u']) {
                            throw new Exception("El precio de venta ({$precio_unitario}) es menor al precio minimo permitido ({$lm['lm_precio_min_u']}) para el lote {$lm_id}");
                        }

                        $descuento_proporcional = ($take / $unidades_requeridas) * $descuento_item;

                        $detalle = [
                            "ve_id" => $ve_id,
                            "med_id" => $med_id,
                            "lm_id" => $lm_id,
                            "dv_cantidad" => $take,
                            "dv_unidad" => 'unidad',
                            "dv_precio_unitario" => $precio_unitario,
                            "dv_descuento" => $descuento_proporcional,
                            "dv_subtotal" => $take * $precio_unitario - $descuento_proporcional
                        ];
                        $det_res = self::agregar_detalle_venta_model($detalle);
                        if (!$det_res || $det_res->rowCount() <= 0) throw new Exception("No se pudo registrar detalle de venta");

                        $ok = self::descontar_unidades_lote_model($lm_id, $take);
                        if (!$ok) throw new Exception("No se pudo actualizar lote {$lm_id}");

                        self::verificar_estado_lote_terminado_model($lm_id);

                        $temp_ref_multiple = "temp_venta_" . $usuario_id . "_" . $ve_id . "_" . $lm_id . "_" . time() . "_" . mt_rand(1000, 9999);

                        $mov_inv = [
                            "lm_id" => $lm_id,
                            "med_id" => $med_id,
                            "su_id" => $sucursal_id,
                            "us_id" => $usuario_id,
                            "mi_tipo" => "salida",
                            "mi_cantidad" => $take,
                            "mi_unidad" => "unidad",
                            "mi_referencia_tipo" => "venta",
                            "mi_referencia_id" => $temp_ref_multiple,
                            "mi_motivo" => "Venta {$ve_numero_documento} (lm_id {$lm_id})"
                        ];
                        $mov_res = self::agregar_movimiento_inventario_model($mov_inv);
                        if (!$mov_res || $mov_res->rowCount() <= 0) throw new Exception("No se pudo registrar movimiento_inventario");

                        $valorado_total_descuento += $take * $lm_precio_compra;
                        $remaining -= $take;
                    }

                    if ($remaining > 0) throw new Exception("Stock inconsistente");
                }

                $inv_ok = self::descontar_inventario_consolidado_model($med_id, $sucursal_id, $unidades_requeridas, $valorado_total_descuento);

                if (!$inv_ok) {
                    $inv_ok = self::recalcular_inventario_por_med_sucursal_model($med_id, $sucursal_id);
                    if (!$inv_ok) {
                        throw new Exception("No se pudo actualizar inventario");
                    }
                }
            }

            $mc = [
                "caja_id" => $caja_id,
                "us_id" => $usuario_id,
                "mc_tipo" => "venta",
                "mc_monto" => $total,
                "mc_concepto" => "Venta {$ve_numero_documento}",
                "mc_referencia_tipo" => "venta",
                "mc_referencia_id" => $ve_id
            ];
            $mc_res = self::registrar_movimiento_caja_model($mc);
            if (!$mc_res || $mc_res->rowCount() <= 0) throw new Exception("No se pudo registrar movimiento_caja");

            if ($documento === 'factura') {
                $fa_numero_control = self::obtener_numero_factura_numerico_model($sucursal_id);
            } else {
                $fa_numero_control = self::generar_numero_factura_model($sucursal_id);
            }
            $datos_factura = [
                "ve_id" => $ve_id,
                "cl_id" => $cliente_id,
                "us_id" => $usuario_id,
                "su_id" => $sucursal_id,
                "fa_numero_control" => $fa_numero_control,
                "fa_monto_total" => $total
            ];
            $fa_id = self::insertar_factura_model($datos_factura);
            if ($fa_id <= 0) throw new Exception("No se pudo insertar factura");

            $config_informe = [
                "ve_id" => $ve_id,
                "fa_id" => $fa_id,
                "ve_numero_documento" => $ve_numero_documento,
                "fa_numero_control" => $fa_numero_control,
                "usuario_id" => $usuario_id,
                "sucursal_id" => $sucursal_id,
                "items" => $venta_items,
                "subtotal" => $subtotal,
                "total" => $total,
                "metodo_pago" => $metodo_pago
            ];
            $informe_data = [
                "inf_nombre" => "Nota Venta {$fa_numero_control}",
                "inf_usuario" => $usuario_id,
                "inf_config" => json_encode($config_informe, JSON_UNESCAPED_UNICODE)
            ];
            $informe_res = self::agregar_informe_venta_model($informe_data);

            // Actualizar referencias temporales específicas de esta venta (solo registros de esta transacción)
            $stmt_update_refs = $db->prepare("
                UPDATE movimiento_inventario
                SET mi_referencia_id = :ve_id, mi_referencia_tipo = 'venta'
                WHERE mi_referencia_id LIKE :temp_pattern
                AND us_id = :us_id
                AND mi_referencia_tipo = 'venta'
                AND mi_creado_en >= DATE_SUB(NOW(), INTERVAL 30 SECOND)
            ");
            $stmt_update_refs->execute([
                ':ve_id' => $ve_id,
                ':temp_pattern' => "temp_venta_{$usuario_id}_{$ve_id}%",
                ':us_id' => $usuario_id
            ]);

            $db->commit();

            // === FACTURACIÓN ELECTRÓNICA SIAT ===
            // La venta ya fue confirmada con $db->commit() arriba, así que un fallo
            // de este bloque no revierte la venta: la venta SIEMPRE sale.
            //
            // Solo aplica si está habilitado y el documento es 'factura'. Cuando es
            // 'nota de venta' no se genera CUF ni XML: es un comprobante interno.
            //
            // $respuestaSiat se declara aca (y no dentro del if) porque la
            // respuesta JSON del final la lee, y sin valor por defecto sale un
            // undefined notice cuando el documento es nota de venta.
            $respuestaSiat = ($documento === 'factura' && SIAT_HABILITADO) ? 'pendiente' : 'nota_venta';
            $textoSiat = '';

            if (SIAT_HABILITADO && $documento === 'factura') {
                try {
                    // 0) Caja que emite.
                    //    Se resuelve por SUCURSAL ACTIVA, no por usuario.
                    //    La politica es de turnos: el mismo empleado puede abrir
                    //    caja en una sucursal hoy y en otra manana, asi que
                    //    usuarios.pv_id es un dato obsoleto en el momento en que
                    //    se cobra. Pasando null, obtenerConfiguracionPuntoVenta
                    //    resuelve WHERE pv_codigo = 0, que es el unico punto de
                    //    venta que hoy esta en uso.
                    $pvId = siatModel::resolverPuntoVenta($sucursal_id, null);

                    if (!$pvId) {
                        error_log("SIAT: su_id={$sucursal_id} sin caja pv_codigo = 0");
                        throw new Exception(
                            "Esta sucursal no tiene caja habilitada. Avise al administrador."
                        );
                    }

                    // 0.b) Comprobar que la sucursal puede facturar ANTES de
                    //      gastar el correlativo. Todo esto lee de la base, no
                    //      de la red, asi que es barato, y convierte un error
                    //      de SOAP de 30-45 s en un mensaje que el cajero
                    //      entiende de inmediato ("falta la habilitacion de la
                    //      caja", "la caja no esta dada de alta").
                    //
                    //      Se guarda el 'diagnostico' en el log y se lanza el
                    //      'motivo', que es el que va sin siglas ni codigos: el
                    //      mensaje de arriba lo termina viendo el cajero, y un
                    //      CUIS de 8 caracteres no le resuelve nada.
                    $salud = siatModel::estadoCajaParaFacturacion($sucursal_id, $pvId);
                    if (!$salud['listo']) {
                        error_log("SIAT salud de caja su_id={$sucursal_id} pv_id={$pvId}: " . $salud['diagnostico']);
                        throw new Exception($salud['motivo']);
                    }

                    // 1) Contexto de la caja: CUIS, CUFD y codigos de sucursal y
                    //    punto de venta. obtenerContextoCaja ya hereda el CUFD de
                    //    siat_configuracion cuando la caja aun no lo tiene, asi
                    //    que no hace falta pedir uno nuevo por red.
                    $caja = siatModel::obtenerContextoCaja($sucursal_id, $pvId);

                    if (empty($caja['cufd']) || empty($caja['cufd_control'])) {
                        /* Este mensaje llega al cajero dentro de la alerta
                           "advertencia_siat", asi que va sin CUFD ni pv_id: el
                           detalle tecnico se deja en el error_log de abajo. */
                        error_log("SIAT sin CUFD: pv_id={$caja['pv_id']} pv_nombre={$caja['pv_nombre']}");
                        throw new Exception(
                            "La caja {$caja['pv_nombre']} no tiene permiso de emision vigente. Avise al administrador."
                        );
                    }

                    // 2) Numero de factura para el CUF.
                    //    OJO: la secuencia factura_secuencia ya se consumio en la
                    //    linea 538 de este mismo metodo (por eso existe
                    //    $fa_numero_control). Volver a pedirlo aqui quemaria un
                    //    segundo numero y el CUF no coincidiria con el numero
                    //    guardado en la factura.
                    $nroFacturaSiat = (int)$fa_numero_control;

                    // 3) Generar CUF (Paso 7) con el codigo de control de la caja
                    //    y guardarlo junto al CUFD usado, en la misma factura.
                    $cuf = siatModel::generarCUF(
                        siatModel::obtenerNitEmpresa(),
                        date('Y-m-d H:i:s'),
                        $nroFacturaSiat,
                        $caja['cufd_control'],
                        $caja['codigo_sucursal'],
                        $caja['codigo_punto_venta'],
                        1, // tipoEmision: 1 = online
                        1, // tipoFactura: 1 = con derecho a credito fiscal
                        1  // tipoDocumentoSector: 1 = compra-venta
                    );

                    if (!$cuf) {
                        throw new Exception("no se pudo generar el CUF para la factura {$fa_id}");
                    }

                    $dbSiat = mainModel::conectar();
                    $stmtCuf = $dbSiat->prepare(
                        "UPDATE factura
                         SET fa_cuf = :cuf, fa_numero_siat = :nro, fa_cufd = :cufd, fa_cufd_control = :control
                         WHERE fa_id = :fa_id"
                    );
                    $stmtCuf->execute([
                        ':cuf'     => $cuf,
                        ':nro'     => $nroFacturaSiat,
                        ':cufd'    => $caja['cufd'],
                        ':control' => $caja['cufd_control'],
                        ':fa_id'   => $fa_id,
                    ]);

                    // 4) Armar y firmar el XML (generarXML ya devuelve el XML
                    //    firmado). Los datos se releen de la base porque el CUF
                    //    recien guardado tiene que viajar dentro del XML.
                    $datosXml = siatModel::obtenerDatosFacturaXML($ve_id);
                    if (!$datosXml) {
                        throw new Exception("no se pudieron obtener los datos del XML");
                    }

                    $detalleXml = siatModel::obtenerDetalleFacturaXML($ve_id);

                    // La leyenda se pide por la MISMA actividad que va a viajar
                    // en el XML. Consultarla por ce_codigo_actividad (477000) no
                    // encontraba nada, porque 477000 no existe en el catalogo
                    // del SIN: el resolver devuelve la real (4772100/4772200/
                    // 477300) y con esa se recupera la leyenda obligatoria.
                    $actividadXml = siatModel::resolverActividadEconomica(
                        $datosXml['ce_codigo_actividad'] ?? null
                    );
                    $leyenda = siatModel::obtener_leyenda_siat_model($actividadXml);

                    $xml = siatModel::generarXML($datosXml, $detalleXml, $leyenda, $sucursal_id, $pvId);

                    if (!$xml) {
                        throw new Exception("no se pudo generar el XML firmado");
                    }

                    // 5) Validar contra el XSD antes de registrar. Un XML invalido
                    //    guardado en facturacion_electronica queda como
                    //    PENDIENTE y despues nadie sabe por que el SIN lo
                    //    rechazo. Aca se descarta de entrada.
                    $xsd = __DIR__ . '/../libs/xmlseclibs/xsd/CompraVenta/facturaElectronicaCompraVenta.xsd';
                    if (is_file($xsd)) {
                        $prevErrors = libxml_use_internal_errors(true);
                        libxml_clear_errors();
                        $dom = new DOMDocument();
                        $dom->preserveWhiteSpace = true;
                        $dom->loadXML($xml);
                        if (!$dom->schemaValidate($xsd)) {
                            $errs = array_map(function ($e) {
                                return "linea {$e->line}: " . trim($e->message);
                            }, libxml_get_errors());
                            libxml_clear_errors();
                            libxml_use_internal_errors($prevErrors);
                            throw new Exception(
                                "el XML firmado no valida contra el XSD: " . implode(' | ', $errs)
                            );
                        }
                        libxml_clear_errors();
                        libxml_use_internal_errors($prevErrors);
                    }

                    // 6) Registrar la factura en la base, sin enviarla.
                    //    El envio NO va aqui: una llamada al SIN tarda 30-45 s y
                    //    el cajero no puede esperar eso ni dos cajas pueden
                    //    pegarle al mismo punto de venta a la vez. La fila queda
                    //    en fe_estado_local = 'PENDIENTE' y la drena
                    //    siatModel::procesarColaEnvio() desde el cron.
                    //    El XML firmado queda guardado tal cual: los reintentos
                    //    reenvian fe_payload, nunca lo regeneran.
                    siatModel::registrarFacturaLocal(
                        $fa_id,
                        $xml,
                        'Firmada en caja. En cola; la envia el cron.'
                    );
                    $respuestaSiat = 'factura_electronica_ok';
                } catch (Throwable $e) {
                    // La venta ya esta confirmada, asi que aqui solo se avisa.
                    // Se captura Throwable y no Exception porque un Error de PHP
                    // (por ejemplo llamar a un metodo inexistente) tambien debe
                    // quedar registrado sin romper la respuesta al cajero.
                    error_log("SIAT: fallo al preparar factura fa_id={$fa_id}: " . $e->getMessage());

                    // El cajero tiene que enterarse: si la factura se quedo sin
                    // CUF, en el historial va a aparecer como nota de venta y
                    // nadie sabra que falto facturar. Se deja la venta intacta
                    // (el SIN nunca debe tumbar una venta ya cobrada) pero se
                    // marca la factura para que quede a la vista.
                    try {
                        $dbAviso = mainModel::conectar();
                        $dbAviso->prepare("
                            UPDATE factura
                            SET fa_numero_control = CONCAT(IFNULL(fa_numero_control,''), ' [SIAT pendiente]')
                            WHERE fa_id = :id AND fa_cuf IS NULL
                        ")->execute([':id' => $fa_id]);
                    } catch (Throwable $ignore) {
                        // Si ni el aviso se puede guardar, el error_log de
                        // arriba ya deja rastro.
                    }

                    $respuestaSiat = 'advertencia_siat';
                    $textoSiat = 'La venta se registró, pero la factura electrónica NO se generó: '
                                 . $e->getMessage();
                }
            }
            // === FIN bloque SIAT ===

            /* El PDF se genera DESPUES del bloque SIAT, no antes.

               Antes se armaba aqui mismo, unas 70 lineas mas arriba, y eso hacia
               imposible imprimir el CUF: la factura todavia no lo tenia porque el
               bloque que se lo asigna viene despues. El comprobante salia
               rotulado "NOTA DE VENTA" sin CUF ni QR aunque fuera una factura
               electronica, y el cliente se llevaba un papel que el SIN no
               reconoce.

               Ademas el metodo ya no decide el tipo de documento: lee
               ventas.ve_tipo_documento y la existencia de CUF. */
            $pdf_base64 = self::generar_pdf_factura_model($fa_id, $documento);

            if (!$pdf_base64) {
                error_log(" No se pudo generar PDF para factura #{$fa_id}");
            }

            $texto = 'La venta se registró correctamente';
            if ($respuestaSiat === 'advertencia_siat') {
                $texto = $texto . '. ' . $textoSiat;
            } elseif ($respuestaSiat === 'factura_electronica_ok') {
                $texto = $texto . '. Factura electrónica firmada y en cola de envío al SIN';
            }

            echo json_encode([
                'Alerta' => 'venta_exitosa',
                'Titulo' => 'Venta registrada',
                'texto' => $texto,
                'Tipo' => ($respuestaSiat === 'advertencia_siat') ? 'warning' : 'success',
                'pdf_data' => $pdf_base64,
                'pdf_nombre' => ($documento === 'factura' ? 'factura' : 'nota_venta')
                    . "_{$ve_numero_documento}.pdf"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Exception $e) {
            error_log("Error en el bloque catch: " . $e->getMessage());
            $db->rollBack();
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error proceso venta', 'texto' => $e->getMessage(), 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
    }

 
    public function cerrar_caja_controller()
    {

        if (!$this->validar_usuario_activo()) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Usuario inactivo',
                'texto' => 'Tu cuenta está desactivada',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (!isset($_SESSION['id_smp']) || !isset($_SESSION['sucursal_smp'])) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sesión inválida', 'texto' => 'No hay sesión válida', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $usuario_id = (int) $_SESSION['id_smp'];
        $sucursal_id = (int) $_SESSION['sucursal_smp'];

        $caja_stmt = self::consulta_caja_model(['us_id' => $usuario_id, 'su_id' => $sucursal_id]);

        if ($caja_stmt->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sin caja activa', 'texto' => 'No tienes una caja abierta', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $caja = $caja_stmt->fetch(PDO::FETCH_ASSOC);
        $caja_id = (int)$caja['caja_id'];
        $saldo_inicial = (float)$caja['caja_saldo_inicial'];


        $ventas_efectivo = self::sumar_ventas_por_caja_model($caja_id, 'efectivo');
        $teorico = $saldo_inicial + (float)$ventas_efectivo;


        $datos_cierre = [
            "caja_id" => $caja_id,
            "caja_saldo_final" => $teorico, 
            "caja_cerrado_en" => date('Y-m-d H:i:s'),
            "caja_observacion" => "Cierre automático con balance interno"
        ];

        $res = self::cerrar_caja_model($datos_cierre);

        if (!$res || $res->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error BD', 'texto' => 'No se pudo cerrar caja', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        $alerta = [
            'Alerta' => 'recargar',
            'Titulo' => 'Caja cerrada',
            'texto' => 'La caja se cerró correctamente',
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }

    public function obtener_factores_conversion_controller($med_id, $sucursal_id) {
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se ha asignado una sucursal"
            ], JSON_UNESCAPED_UNICODE);
        }

        $med_id = (int)mainModel::limpiar_cadena($med_id);
        $sucursal_id = (int)mainModel::limpiar_cadena($sucursal_id);

        if ($med_id <= 0 || $sucursal_id <= 0) {
            return json_encode([
                "error" => true,
                "mensaje" => "Parámetros inválidos"
            ], JSON_UNESCAPED_UNICODE);
        }

        $factor = self::obtener_factor_unidades_por_tipo_model($med_id, $sucursal_id);
        
        if (!$factor) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se encontró factor de conversión"
            ], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            "error" => false,
            "unidades_por_caja" => $factor['unidades_por_caja'],
            "unidades_por_blister" => $factor['unidades_por_blister']
        ], JSON_UNESCAPED_UNICODE);
    }
}
