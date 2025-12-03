<?php

if ($peticionAjax) {
    require_once "../models/ventaModel.php";
} else {
    require_once "./models/ventaModel.php";
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

    /**
     * Buscar medicamentos
     */
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

        if (!empty($filtros['linea'])) {
            $filtros_limpios['linea'] = (int)mainModel::limpiar_cadena($filtros['linea']);
        }
        if (!empty($filtros['presentacion'])) {
            $filtros_limpios['presentacion'] = (int)mainModel::limpiar_cadena($filtros['presentacion']);
        }
        if (!empty($filtros['funcion'])) {
            $filtros_limpios['funcion'] = (int)mainModel::limpiar_cadena($filtros['funcion']);
        }
        if (!empty($filtros['via'])) {
            $filtros_limpios['via'] = (int)mainModel::limpiar_cadena($filtros['via']);
        }

        $rows = self::buscar_medicamento_model($termino, $sucursal_id, $filtros_limpios);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Más vendidos
     */
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

    /**
     * Consultar caja activa
     */
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

    /**
     * 🆕 Abrir caja con validación de usuario activo
     */
    public function abrir_caja_controller()
    {
        // ✅ Validar usuario activo
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

    /**
     * 🆕 Registrar venta con validación de usuario activo
     */
    public function registrar_venta_controller()
    {
        // ✅ Validar usuario activo
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

            foreach ($venta_items as $item) {
                $med_id = isset($item['med_id']) ? (int)$item['med_id'] : 0;
                $cantidad = isset($item['cantidad']) ? (int)$item['cantidad'] : 0;
                $precio_unitario = isset($item['precio']) ? (float)$item['precio'] : 0.0;
                $descuento_item = isset($item['descuento']) ? (float)$item['descuento'] : 0.00;

                if ($med_id <= 0 || $cantidad <= 0 || $precio_unitario <= 0) {
                    throw new Exception("Ítem inválido");
                }

                $unidades_requeridas = $cantidad;
                $stock_total = self::sumar_stock_lotes_med_sucursal_model($med_id, $sucursal_id);

                if ($stock_total < $unidades_requeridas) {
                    throw new Exception("Stock insuficiente para med_id {$med_id}");
                }

                $remaining = $unidades_requeridas;
                $lotes = self::obtener_lotes_activos_por_med_sucursal_model($med_id, $sucursal_id);
                $valorado_total_descuento = 0;

                foreach ($lotes as $lm) {
                    if ($remaining <= 0) break;
                    $lm_id = (int)$lm['lm_id'];
                    $lm_disp = (int)$lm['lm_cant_actual_unidades'];
                    if ($lm_disp <= 0) continue;
                    $take = min($lm_disp, $remaining);
                    $lm_precio_compra = (float)$lm['lm_precio_compra'];

                    $detalle = [
                        "ve_id" => $ve_id,
                        "med_id" => $med_id,
                        "lm_id" => $lm_id,
                        "dv_cantidad" => $take,
                        "dv_unidad" => 'unidad',
                        "dv_precio_unitario" => $precio_unitario,
                        "dv_descuento" => $descuento_item,
                        "dv_subtotal" => $take * $precio_unitario - $descuento_item
                    ];
                    $det_res = self::agregar_detalle_venta_model($detalle);
                    if (!$det_res || $det_res->rowCount() <= 0) throw new Exception("No se pudo registrar detalle de venta");

                    $ok = self::descontar_unidades_lote_model($lm_id, $take);
                    if (!$ok) throw new Exception("No se pudo actualizar lote {$lm_id}");

                    self::verificar_estado_lote_terminado_model($lm_id);

                    $mov_inv = [
                        "lm_id" => $lm_id,
                        "med_id" => $med_id,
                        "su_id" => $sucursal_id,
                        "us_id" => $usuario_id,
                        "mi_tipo" => "salida",
                        "mi_cantidad" => $take,
                        "mi_unidad" => "unidad",
                        "mi_referencia_tipo" => "venta",
                        "mi_referencia_id" => $ve_id,
                        "mi_motivo" => "Venta {$ve_numero_documento} (lm_id {$lm_id})"
                    ];
                    $mov_res = self::agregar_movimiento_inventario_model($mov_inv);
                    if (!$mov_res || $mov_res->rowCount() <= 0) throw new Exception("No se pudo registrar movimiento_inventario");

                    $valorado_total_descuento += $take * $lm_precio_compra;
                    $remaining -= $take;
                }

                if ($remaining > 0) throw new Exception("Stock inconsistente");

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

            $fa_numero = self::generar_numero_factura_model($sucursal_id);
            $datos_factura = [
                "ve_id" => $ve_id,
                "cl_id" => $cliente_id,
                "us_id" => $usuario_id,
                "su_id" => $sucursal_id,
                "fa_numero" => $fa_numero,
                "fa_monto_total" => $total
            ];
            $fa_id = self::insertar_factura_model($datos_factura);
            if ($fa_id <= 0) throw new Exception("No se pudo insertar factura");

            $config_informe = [
                "ve_id" => $ve_id,
                "fa_id" => $fa_id,
                "ve_numero_documento" => $ve_numero_documento,
                "fa_numero" => $fa_numero,
                "usuario_id" => $usuario_id,
                "sucursal_id" => $sucursal_id,
                "items" => $venta_items,
                "subtotal" => $subtotal,
                "total" => $total,
                "metodo_pago" => $metodo_pago
            ];
            $informe_data = [
                "inf_nombre" => "Nota Venta {$fa_numero}",
                "inf_usuario" => $usuario_id,
                "inf_config" => json_encode($config_informe, JSON_UNESCAPED_UNICODE)
            ];
            $informe_res = self::agregar_informe_venta_model($informe_data);

            $pdf_base64 = self::generar_pdf_factura_model($fa_id, 'nota_venta');

            if (!$pdf_base64) {
                error_log("⚠️ No se pudo generar PDF para factura #{$fa_id}");
            }

            $db->commit();

            echo json_encode([
                'Alerta' => 'venta_exitosa',
                'Titulo' => 'Venta registrada',
                'texto' => 'La venta se registró correctamente',
                'Tipo' => 'success',
                'pdf_data' => $pdf_base64,
                'pdf_nombre' => "nota_venta_{$ve_numero_documento}.pdf"
            ], JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error proceso venta', 'texto' => $e->getMessage(), 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
    }

    /**
     * 🆕 Cerrar caja con balance interno
     */
    public function cerrar_caja_controller()
    {
        // ✅ Validar usuario activo
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

        // Obtener total de ventas en efectivo
        $ventas_efectivo = self::sumar_ventas_por_caja_model($caja_id, 'efectivo');
        $teorico = $saldo_inicial + (float)$ventas_efectivo;

        // ✅ Balance interno: El cajero NO ve cuánto vendió
        // Se registra automáticamente el saldo teórico
        $datos_cierre = [
            "caja_id" => $caja_id,
            "caja_saldo_final" => $teorico, // Balance automático
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
}
