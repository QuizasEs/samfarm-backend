<?php

if ($peticionAjax) {
    require_once "../models/compraModel.php";
} else {
    require_once "./models/compraModel.php";
}

class compraController extends compraModel
{


    /* funcion endpoint SPA para busqueda de medicamentos formularios */

    public function buscar_medicamento_controller(array $filtros)
    {

        /* prepareamos la consulta */
        $sql = "
            SELECT 
                m.med_id,
                m.med_nombre_quimico AS nombre,
                m.med_precio_unitario AS precio,
                la.la_nombre_comercial AS laboratorio,
                ff.ff_nombre AS forma,
                vd.vd_nombre AS via,
                uf.uf_nombre AS uso
            FROM medicamento AS m
            LEFT JOIN laboratorios AS la ON m.la_id = la.la_id
            LEFT JOIN forma_farmaceutica AS ff ON m.ff_id = ff.ff_id
            LEFT JOIN via_de_administracion AS vd ON m.vd_id = vd.vd_id
            LEFT JOIN uso_farmacologico AS uf ON m.uf_id = uf.uf_id
            WHERE 1 = 1 
        ";


        /* aplicamos filtros de manera dinamica solo si existe el filtro */
        /* filtramos por categorias */
        if (!empty($filtros['forma'])) {
            $sql .= " AND m.ff_id = " . intval($filtros['forma']);
        }
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = " . intval($filtros['via']);
        }
        if (!empty($filtros['laboratorio'])) {
            $sql .= " AND m.la_id = " . intval($filtros['laboratorio']);
        }
        if (!empty($filtros['uso'])) {
            $sql .= " AND m.uf_id = " . intval($filtros['uso']);
        }
        /* filtramos por termino de busqueda */
        /* filtramos por termino de busqueda */
        if (!empty($filtros['termino'])) {
            $busqueda = "%" . $filtros['termino'] . "%";
            $sql .= "
                AND (
                    m.med_nombre_quimico LIKE '$busqueda' OR
                    m.med_principio_activo LIKE '$busqueda' OR
                    m.med_accion_farmacologica LIKE '$busqueda' OR
                    m.med_presentacion LIKE '$busqueda' OR
                    m.med_descripcion LIKE '$busqueda'  /* ← QUITAR LA COMA EXTRA */
                )
            ";
        }

        $sql .= " ORDER BY m.med_nombre_quimico ASC limit 100";

        $respuesta = mainModel::ejecutar_consulta_simple($sql);
        return $respuesta;
    }
    /* agegar compra nueva controlador */
    public function agregar_compra_controller()
    {
        session_start(['name' => 'SMP']);
        $usuarioId = $_SESSION['id_smp'];

        /* limpiamos y validamos */
        $numero_compra   = mainModel::limpiar_cadena($_POST['Numero_compra_reg'] ?? '');
        $razon           = mainModel::limpiar_cadena($_POST['razon_reg'] ?? '');
        $proveedor       = (int) mainModel::limpiar_cadena($_POST['Proveedor_reg'] ?? 0);
        $laboratorio     = (int) mainModel::limpiar_cadena($_POST['Laboratorio_factura_reg'] ?? 0);
        $fecha_factura   = mainModel::limpiar_cadena($_POST['Fecha_factura_reg'] ?? '');
        $numero_factura  = mainModel::limpiar_cadena($_POST['Numero_factura_reg'] ?? '');
        $impuestos_pct   = floatval(mainModel::limpiar_cadena($_POST['impuestos_reg'] ?? 0));
        $lotes           = json_decode($_POST['lotes_json'] ?? '[]', true);
        $totales         = json_decode($_POST['totales_json'] ?? '{}', true);

        if ($numero_compra === '' || $razon === '' || $proveedor <= 0 || $laboratorio <= 0 || $fecha_factura === '' || $numero_factura === '') {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos faltantes',
                'texto' => 'Asegúrate de completar todos los campos obligatorios.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        if (!is_array($lotes) || count($lotes) === 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Ocurrio Un error',
                'texto' => 'Datos erroneos en los lotes de compra.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();

        /* Totales */
        $subtotal = floatval($totales['subtotal'] ?? 0);
        $impuestos_valor = floatval($totales['impuestos'] ?? 0);
        $total = floatval($totales['total'] ?? 0);

        /* Registro compra */
        $compra_datos = [
            'co_numero' => $numero_compra,
            'co_numero_factura' => $numero_factura,
            'co_fecha_factura' => $fecha_factura,
            'co_subtotal' => $subtotal,
            'co_impuesto' => $impuestos_valor,
            'co_total' => $total,
            'la_id' => $laboratorio,
            'us_id' => $usuarioId,
            'su_id' => 1,
            'pr_id' => $proveedor,
            'co_razon_social' => $razon
        ];

        $co_id = compraModel::agregar_compra_model($compra_datos);
        if ($co_id <= 0) return $this->alerta_error('Error', 'No se pudo registrar la compra.');

        /* Procesamos lotes */
        foreach ($lotes as $item) {
            $med_id = (int) ($item['id_medicamento'] ?? $item['med_id'] ?? 0);
            $cantidad = (int) ($item['cantidad'] ?? 0);
            $vencimiento = $item['vencimiento'] ?? null;
            $precioCompra = floatval($item['precioCompra'] ?? 0);
            $precioVenta = floatval($item['precioVenta'] ?? 0);
            $numero_lote = $item['numero'] ?? ($item['lm_numero_lote'] ?? null);

            if ($med_id <= 0 || $cantidad <= 0)
                return $this->alerta_error('Datos inválidos', 'El lote o medicamento no es válido.');

            /* Insertar lote */
            $lotes_datos = [
                'pr_id' => $proveedor,
                'med_id' => $med_id,
                'su_id' => 1,
                'lm_numero_lote' => $numero_lote ?: 'L' . time() . rand(100, 999),
                'lm_cantidad_inicial' => $cantidad,
                'lm_cantidad_actual' => $cantidad,
                'lm_precio_compra' => $precioCompra,
                'lm_precio_venta' => $precioVenta,
                'lm_fecha_vencimiento' => $vencimiento
            ];

            $lm_id = compraModel::agregar_lote_model($lotes_datos);
            if ($lm_id <= 0) return $this->alerta_error('Error', 'No se pudo registrar el lote.');

            /* Detalle compra */
            $detalle = [
                'co_id' => $co_id,
                'med_id' => $med_id,
                'lm_id' => $lm_id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precioCompra,
                'descuento' => 0.00,
                'subtotal' => round($precioCompra * $cantidad, 2)
            ];
            compraModel::agregar_detalle_compra_model($detalle);

            /* Inventario */
            $inv = [
                'su_id' => 1,
                'med_id' => $med_id,
                'lm_id' => $lm_id,
                'inv_cantidad' => $cantidad,
                'inv_ultimo_precio' => $precioCompra
            ];
            compraModel::actualizar_inventario_model($inv);

            /* Movimiento */
            $mov = [
                'lm_id' => $lm_id,
                'med_id' => $med_id,
                'su_id' => 1,
                'us_id' => $usuarioId,
                'mi_tipo' => 'entrada',
                'mi_cantidad' => $cantidad,
                'mi_unidad' => 'unidad',
                'mi_referencia_tipo' => 'compra',
                'mi_referencia_id' => $co_id,
                'mi_motivo' => "Entrada por compra #{$co_id}"
            ];
            compraModel::agregar_movimiento_inventario_model($mov);
        }

        /* Informe general (se hace una sola vez) */
        $inf_config = json_encode([
            'compra_id' => $co_id,
            'subtotal' => $subtotal,
            'impuesto' => $impuestos_valor,
            'total' => $total,
            'cantidad_lotes' => count($lotes)
        ], JSON_UNESCAPED_UNICODE);

        compraModel::agregar_informe_compra_model([
            'inf_nombre' => "Compra {$numero_compra} - {$co_id}",
            'inf_usuario' => $usuarioId,
            'inf_config' => $inf_config
        ]);

        return $this->alerta_exito('Compra registrada', 'La compra, lotes e inventario se registraron correctamente.');
    }
}
