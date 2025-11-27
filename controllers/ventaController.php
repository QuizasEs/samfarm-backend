<?php

if ($peticionAjax) {
    require_once "../models/ventaModel.php";
} else {
    require_once "./models/ventaModel.php";
}

class ventaController extends ventaModel
{
    /* controlador que busca al cliente */
    public function buscar_cliente_controller($termino)
    {
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se ha asignado una sucursal"
            ], JSON_UNESCAPED_UNICODE);
        }

        // Limpiar cadena
        $termino = mainModel::limpiar_cadena($termino);

        if (strlen($termino) < 1) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        // Buscar en el modelo
        $rows = self::buscar_cliente_model($termino);

        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }



    public function buscar_medicamento_controller($termino, $filtros = [])
    {

        // Validar sesión y sucursal
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se ha asignado una sucursal"
            ], JSON_UNESCAPED_UNICODE);
        }

        $sucursal_id = (int)$_SESSION['sucursal_smp'];

        // Limpiar término de búsqueda usando mainModel
        $termino = mainModel::limpiar_cadena($termino);

        // Validar longitud mínima
        if (strlen($termino) < 1) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        // Limpiar y validar filtros
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

        // Ejecutar búsqueda
        $rows = self::buscar_medicamento_model($termino, $sucursal_id, $filtros_limpios);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }

    public function mas_vendidos_controller($limit = 5)
    {
        // Validar sesión y sucursal
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        $sucursal_id = (int)$_SESSION['sucursal_smp'];

        // Limpiar y validar límite
        $limit = (int)mainModel::limpiar_cadena($limit);
        if ($limit <= 0 || $limit > 20) {
            $limit = 5;
        }

        // Ejecutar consulta
        $rows = self::top_ventas_model($sucursal_id, $limit);

        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }
    /* preguntar por cajas activas de usuario */
    public function consulta_caja_controller()
    {
        /* nos aseguramos que el que intenta abrir la caja tenga rol */
        if (isset($_SESSION['id_smp']) && in_array($_SESSION['rol_smp'], [1, 2, 3])) {
            $datos = [
                "us_id" => $_SESSION['id_smp'],
                "su_id" => $_SESSION['sucursal_smp']
            ];
            $respuesta = self::consulta_caja_model($datos);

            if ($respuesta->rowCount() <= 0) {
                return false;
            } else {
                // RETORNAR LA RESPUESTA CUANDO HAY REGISTROS
                return true;
            }
        }
    }

    public function abrir_caja_controller()
    {
        // Limpiar y validar saldo inicial
        $saldo_inicial = mainModel::limpiar_cadena($_POST['saldo_inicial']);

        /* Datos de usuario */
        $usuario_id = $_SESSION['id_smp'];
        $sucursal_id = $_SESSION['sucursal_smp'];

        if ($usuario_id === 1) {
            $nombre = "Caja Administrador";
        } else {
            $nombre = "Caja " . $_SESSION['nombre_smp'];
        }

        /* Validar saldo inicial */
        if (empty($saldo_inicial) || !is_numeric($saldo_inicial) || $saldo_inicial < 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos faltantes o incorrectos',
                'texto' => 'Por favor ingrese un monto inicial válido (debe ser un número positivo)',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Verificar si ya existe una caja abierta para este usuario/sucursal */
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
                'texto' => 'Ya tienes una caja abierta. Debes cerrarla antes de abrir una nueva.',
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

        /* Enviar los datos */
        $caja_respuesta = self::abrir_caja_model($datos_caja);

        if ($caja_respuesta->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrió un error inesperado',
                'texto' => 'No pudimos abrir la caja, intenta nuevamente más tarde',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $alerta = [
            'Alerta' => 'recargar',
            'Titulo' => 'Caja abierta',
            'texto' => 'La caja se abrió correctamente, la vista se recargará',
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }

    /* registrar venta */

    public function registrar_venta_controller()
    {
        if (!isset($_SESSION['id_smp']) || !isset($_SESSION['sucursal_smp'])) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sesion invalida', 'texto' => 'No hay sesion valida', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
        $usuario_id = (int)$_SESSION['id_smp'];
        $sucursal_id = (int)$_SESSION['sucursal_smp'];

        // Obtener caja activa
        $caja_stmt = self::consulta_caja_model(['us_id' => $usuario_id, 'su_id' => $sucursal_id]);
        if ($caja_stmt->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Caja cerrada', 'texto' => 'No tienes una caja activa', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
        $caja = $caja_stmt->fetch(PDO::FETCH_ASSOC);
        $caja_id = (int)$caja['caja_id'];

        // Leer POST
        $venta_items_json = $_POST['venta_items_json'] ?? '[]';
        $venta_items = json_decode($venta_items_json, true);
        $subtotal = isset($_POST['subtotal_venta']) ? (float) $_POST['subtotal_venta'] : 0.0;
        $total = isset($_POST['total_venta']) ? (float) $_POST['total_venta'] : 0.0;
        $dinero_recibido = isset($_POST['dinero_recibido_venta']) ? (float) $_POST['dinero_recibido_venta'] : 0.0;
        $cliente_id = isset($_POST['cliente_id']) && is_numeric($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago_venta']);
        $documento = mainModel::limpiar_cadena($_POST['documento_venta']);


        /* verificamos que metodo de pago y documento no esten vacios */
        if ($metodo_pago == "" || !in_array($metodo_pago, ["targeta", "QR", "efectivo"])) {
            $metodo_pago = "efectivo";
        }
        if ($documento == "" || !in_array($metodo_pago, ["factura", "nota de venta"])) {
            $documento = "nota de venta";
        }

        // Validaciones
        if (!is_array($venta_items) || count($venta_items) === 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sin productos', 'texto' => 'Debes agregar al menos un producto', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
        if ($total <= 0 || $subtotal <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Totales inválidos', 'texto' => 'Los totales calculados son inválidos', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        // Iniciar transacción
        $db = mainModel::conectar();
        try {
            $db->beginTransaction();

            // Generar número de documento y registrar venta
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
                $tipo = isset($item['tipo']) ? $item['tipo'] : 'unidad'; // default unidad
                $precio_unitario = isset($item['precio']) ? (float)$item['precio'] : 0.0;
                $descuento_item = isset($item['descuento']) ? (float)$item['descuento'] : 0.00;

                if ($med_id <= 0 || $cantidad <= 0 || $precio_unitario <= 0) {
                    throw new Exception("Ítem inválido");
                }

                $unidades_requeridas = $cantidad;
                if ($tipo === 'caja' || $tipo === 'blister') {
                    // Obtener factor (unidades por tipo) preferentemente desde un lote activo.
                    $ref = self::obtener_factor_unidades_por_tipo_model($med_id, $sucursal_id);
                    if (!$ref) throw new Exception("No hay lotes para determinar factor de unidad");
                    $unidades_por_caja = $ref['unidades_por_caja'];
                    if ($tipo === 'caja') {
                        $unidades_requeridas = $cantidad * $unidades_por_caja;
                    } else { // blister
                        $unidades_por_blister = $ref['unidades_por_blister'];
                        $unidades_requeridas = $cantidad * $unidades_por_blister;
                    }
                }
                // Verificar stock total en lotes (suma)
                $stock_total = self::sumar_stock_lotes_med_sucursal_model($med_id, $sucursal_id);
                if ($stock_total < $unidades_requeridas) {
                    throw new Exception("Stock insuficiente para med_id {$med_id}. Disponible: {$stock_total}, Requerido: {$unidades_requeridas}");
                }

                // Consumir unidades por lotes (PEPS FIFO)
                $remaining = $unidades_requeridas;
                $lotes = self::obtener_lotes_activos_por_med_sucursal_model($med_id, $sucursal_id);
                foreach ($lotes as $lm) {
                    if ($remaining <= 0) break;
                    $lm_id = (int)$lm['lm_id'];
                    $lm_disp = (int)$lm['lm_cant_actual_unidades'];
                    if ($lm_disp <= 0) continue;
                    $take = min($lm_disp, $remaining);

                    // Insert detalle_venta para este lote
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

                    // Actualizar lote (unidades y cajas según factor)
                    $ok = self::descontar_unidades_lote_model($lm_id, $take);
                    if (!$ok) throw new Exception("No se pudo actualizar lote {$lm_id}");

                    /* verifica que el estado de stock de los lotes */
                    self::verificar_estado_lote_terminado_model($lm_id);
                    // Registrar en historial_lote
                    $historial_stmt = $db->prepare("
                        INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
                        VALUES (:lm_id, :us_id, 'terminacion', :descripcion)
                    ");

                    // Obtener usuario desde sesión (si está disponible)
                    $us_id = isset($_SESSION['id_smp']) ? (int)$_SESSION['id_smp'] : null;

                    $historial_stmt->bindParam(":lm_id", $lm_id, PDO::PARAM_INT);
                    $historial_stmt->bindParam(":us_id", $us_id, PDO::PARAM_INT);
                    $historial_stmt->bindValue(":descripcion", "Lote agotado por ventas, cambiado a estado 'terminado' automáticamente");
                    $historial_stmt->execute();

                    // Registrar movimiento_inventario
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

                    $remaining -= $take;
                } // end lotes


                if ($remaining > 0) throw new Exception("Stock inconsistente después de consumir lotes");

                // ✅ DESCUENTO DEL INVENTARIO CONSOLIDADO CON MEJOR MANEJO DE ERRORES
                $inv_ok = self::descontar_inventario_consolidado_model($med_id, $sucursal_id, $unidades_requeridas);

                if (!$inv_ok) {
                    // Log detallado del error
                    error_log("ERROR: Falló descuento inventario consolidado. med_id={$med_id}, su_id={$sucursal_id}, unidades={$unidades_requeridas}");

                    // Intentar recalcular como fallback
                    error_log("Intentando recalcular inventario como fallback...");
                    $inv_ok = self::recalcular_inventario_por_med_sucursal_model($med_id, $sucursal_id);

                    if (!$inv_ok) {
                        // Obtener nombre del medicamento para mensaje más claro
                        $med_stmt = mainModel::conectar()->prepare("SELECT med_nombre_quimico FROM medicamento WHERE med_id = :med_id");
                        $med_stmt->execute([':med_id' => $med_id]);
                        $med_data = $med_stmt->fetch(PDO::FETCH_ASSOC);
                        $med_nombre = $med_data ? $med_data['med_nombre_quimico'] : "ID: {$med_id}";

                        throw new Exception("No se pudo actualizar inventario para: {$med_nombre}");
                    }
                }
            } // end foreach items

            // Registrar movimiento de caja (venta)
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

            // Registrar factura
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

            // Registrar informe (nota_venta)
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
            if (!$informe_res || $informe_res->rowCount() <= 0) {
                // No crítico: informamos en logs pero continuamos
            }

            // Generar PDF y commit
            // Generar PDF en memoria (sin guardar)
            $pdf_base64 = self::generar_pdf_factura_model($fa_id, 'nota_venta');

            if (!$pdf_base64) {
                error_log("⚠️ No se pudo generar PDF para factura #{$fa_id}, pero la venta se registró");
            }

            $db->commit();

            // ✅ Responder con PDF en base64 para abrir en frontend
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

    /* cerrar caja controller */

    public function cerrar_caja_controller()
    {
        if (!isset($_SESSION['id_smp']) || !isset($_SESSION['sucursal_smp'])) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Sesion invalida', 'texto' => 'No hay sesion valida', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
        $usuario_id = (int) $_SESSION['id_smp'];
        $sucursal_id = (int) $_SESSION['sucursal_smp'];

        // Recuperar caja activa
        $caja_stmt = self::consulta_caja_model(['us_id' => $usuario_id, 'su_id' => $sucursal_id]);
        if ($caja_stmt->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Caja inválida', 'texto' => 'No hay caja activa', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }
        $caja = $caja_stmt->fetch(PDO::FETCH_ASSOC);
        $caja_id = (int)$caja['caja_id'];

        // Recibir conteo desde POST; ejemplo: counts[200]=1, counts[100]=0 ...
        $counts = $_POST['counts'] ?? [];
        // Calcular total contado
        $denoms = [200, 100, 50, 20, 10, 5, 2, 1, 0.5, 0.2];
        $total_contado = 0.0;
        foreach ($denoms as $d) {
            $k = (string)$d;
            $qty = isset($counts[$k]) ? (int)$counts[$k] : 0;
            $total_contado += $qty * $d;
        }

        // Obtener suma de ventas en efectivo realizadas en esta caja (mc_tipo = 'venta')
        $ventas_efectivo = self::sumar_ventas_por_caja_model($caja_id, 'efectivo');
        // ventas_efectivo devuelve float
        $saldo_inicial = (float)$caja['caja_saldo_inicial'];

        // Teórico: saldo_inicial + ventas_efectivo
        $teorico = $saldo_inicial + (float)$ventas_efectivo;

        // Guardar cierre en tabla caja
        $datos_cierre = [
            "caja_id" => $caja_id,
            "caja_saldo_final" => $total_contado,
            "caja_cerrado_en" => date('Y-m-d H:i:s')
        ];

        $res = self::cerrar_caja_model($datos_cierre);
        if (!$res || $res->rowCount() <= 0) {
            $alerta = ['Alerta' => 'simple', 'Titulo' => 'Error BD', 'texto' => 'No se pudo cerrar caja', 'Tipo' => 'error'];
            echo json_encode($alerta);
            exit();
        }

        // Opcional: generar informe/resumen de cierre en informes o PDF

        $alerta = [
            'Alerta' => 'recargar',
            'Titulo' => 'Caja cerrada',
            'texto' => "Caja cerrada. Teórico: {$teorico}, Contado: {$total_contado}",
            '
        Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }
}
