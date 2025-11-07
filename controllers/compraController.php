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
        /* ========== VALIDAR Y LIMPIAR CAMPOS ========== */

        // Datos principales de compra
        $numero_compra = mainModel::limpiar_cadena($_POST['Numero_compra_reg']);
        $razon_social = mainModel::limpiar_cadena($_POST['razon_reg']);
        $proveedor_id = mainModel::limpiar_cadena($_POST['Proveedor_reg']);
        $laboratorio_id = mainModel::limpiar_cadena($_POST['Laboratorio_factura_reg']);

        // Datos de factura
        $fecha_factura = mainModel::limpiar_cadena($_POST['Fecha_factura_reg']);
        $numero_factura = mainModel::limpiar_cadena($_POST['Numero_factura_reg']);
        $porcentaje_impuesto = mainModel::limpiar_cadena($_POST['impuestos_reg']);

        // Datos del usuario y sucursal
        $usuario_id = mainModel::limpiar_cadena($_SESSION['id_smp']);
        $sucursal_id = mainModel::limpiar_cadena($_SESSION['sucursal_smp'] ?? 1);

        // Decodificar JSON de lotes y totales
        $lotes_json = $_POST['lotes_json'] ?? '[]';
        $totales_json = $_POST['totales_json'] ?? '{}';

        $lotes = json_decode($lotes_json, true);
        $totales = json_decode($totales_json, true);

        /* ========== VALIDACIONES ========== */

        // Validar campos obligatorios
        if (
            empty($numero_compra) || empty($razon_social) || empty($proveedor_id) ||
            empty($laboratorio_id) || empty($fecha_factura) || empty($numero_factura)
        ) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos faltantes',
                'texto' => 'Por favor completa todos los campos obligatorios (número de compra, razón social, proveedor, laboratorio, fecha y número de factura).',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        // Validar que haya lotes
        if (empty($lotes) || !is_array($lotes) || count($lotes) === 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Sin medicamentos',
                'texto' => 'Debes agregar al menos un medicamento con su lote a la compra.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        // Validar formato de totales
        if (empty($totales) || !isset($totales['subtotal']) || !isset($totales['total'])) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error en totales',
                'texto' => 'Los totales de la compra no son válidos.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        // Validar porcentaje de impuesto
        if (!is_numeric($porcentaje_impuesto) || $porcentaje_impuesto < 0 || $porcentaje_impuesto > 100) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Impuesto inválido',
                'texto' => 'El porcentaje de impuesto debe estar entre 0 y 100.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* ========== PREPARAR DATOS DE COMPRA ========== */

        $datos_compra = [
            "co_numero" => $numero_compra,
            "la_id" => $laboratorio_id,
            "us_id" => $usuario_id,
            "su_id" => $sucursal_id,
            "pr_id" => $proveedor_id,
            "co_subtotal" => $totales['subtotal'],
            "co_impuesto" => $totales['impuestos'],
            "co_total" => $totales['total'],
            "co_numero_factura" => $numero_factura,
            "co_fecha_factura" => $fecha_factura,
            "co_razon_social" => $razon_social
        ];

        /* ========== INSERTAR COMPRA ========== */

        $compra_id = compraModel::agregar_compra_model($datos_compra);

        if ($compra_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error al registrar',
                'texto' => 'No se pudo registrar la compra en la base de datos.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* ========== PROCESAR CADA LOTE ========== */

        foreach ($lotes as $lote) {

            // Limpiar datos del lote
            $medicamento_id = mainModel::limpiar_cadena($lote['id_medicamento']);
            $numero_lote = mainModel::limpiar_cadena($lote['numero']);
            $cantidad = mainModel::limpiar_cadena($lote['cantidad']);
            $fecha_vencimiento = mainModel::limpiar_cadena($lote['vencimiento']);
            $precio_compra = mainModel::limpiar_cadena($lote['precioCompra']);
            $precio_venta = mainModel::limpiar_cadena($lote['precioVenta']);

            // Validar datos del lote
            if (empty($medicamento_id) || empty($cantidad) || empty($precio_compra)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Datos incompletos',
                    'texto' => 'Uno de los lotes tiene datos incompletos (medicamento, cantidad o precio).',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            // Calcular subtotal del lote (sin descuento por ahora)
            $subtotal_lote = $cantidad * $precio_compra;

            /* ========== INSERTAR LOTE MEDICAMENTO ========== */

            $datos_lote = [
                "pr_id" => $proveedor_id,
                "med_id" => $medicamento_id,
                "su_id" => $sucursal_id,
                "lm_numero_lote" => $numero_lote,
                "lm_cantidad_inicial" => $cantidad,
                "lm_cantidad_actual" => $cantidad,
                "lm_precio_compra" => $precio_compra,
                "lm_precio_venta" => $precio_venta,
                "lm_fecha_vencimiento" => $fecha_vencimiento
            ];

            $lote_id = compraModel::agregar_lote_model($datos_lote);

            if ($lote_id <= 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error al registrar lote',
                    'texto' => 'No se pudo registrar el lote del medicamento en la base de datos.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            /* ========== INSERTAR DETALLE DE COMPRA ========== */

            $datos_detalle = [
                "co_id" => $compra_id,
                "med_id" => $medicamento_id,
                "lm_id" => $lote_id,
                "cantidad" => $cantidad,
                "precio_unitario" => $precio_compra,
                "descuento" => 0.00, // Sin descuento por ahora
                "subtotal" => $subtotal_lote
            ];

            $detalle_resultado = compraModel::agregar_detalle_compra_model($datos_detalle);

            if ($detalle_resultado->rowCount() <= 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error en detalle',
                    'texto' => 'No se pudo registrar el detalle de la compra.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            /* ========== ACTUALIZAR/CREAR INVENTARIO ========== */

            $datos_inventario = [
                "su_id" => $sucursal_id,
                "med_id" => $medicamento_id,
                "lm_id" => $lote_id,
                "inv_cantidad" => $cantidad,
                "inv_ultimo_precio" => $precio_compra
            ];

            compraModel::actualizar_inventario_model($datos_inventario);

            /* ========== REGISTRAR MOVIMIENTO DE INVENTARIO ========== */

            $datos_movimiento = [
                "lm_id" => $lote_id,
                "med_id" => $medicamento_id,
                "su_id" => $sucursal_id,
                "us_id" => $usuario_id,
                "mi_tipo" => "entrada",
                "mi_cantidad" => $cantidad,
                "mi_unidad" => "unidad",
                "mi_referencia_tipo" => "compra",
                "mi_referencia_id" => $compra_id,
                "mi_motivo" => "Compra #{$numero_compra} - Lote: {$numero_lote}"
            ];

            compraModel::agregar_movimiento_inventario_model($datos_movimiento);
        }
        /* ========== REGISTRAR INFORME DE COMPRA ========== */

        $config_informe = [
            "compra_id" => $compra_id,
            "numero_compra" => $numero_compra,
            "proveedor_id" => $proveedor_id,
            "laboratorio_id" => $laboratorio_id,
            "sucursal_id" => $sucursal_id,
            "fecha_factura" => $fecha_factura,
            "numero_factura" => $numero_factura,
            "razon_social" => $razon_social,
            "subtotal" => $totales['subtotal'],
            "impuestos" => $totales['impuestos'],
            "total" => $totales['total'],
            "cantidad_lotes" => count($lotes),
            "lotes" => array_map(function ($lote) {
                return [
                    "medicamento_id" => $lote['id_medicamento'],
                    "numero_lote" => $lote['numero'],
                    "cantidad" => $lote['cantidad'],
                    "precio_compra" => $lote['precioCompra'],
                    "precio_venta" => $lote['precioVenta'],
                    "vencimiento" => $lote['vencimiento']
                ];
            }, $lotes)
        ];

        $datos_informe = [
            "inf_nombre" => "Compra {$numero_compra} - {$razon_social}",
            "inf_tipo" => "compra",
            "inf_usuario" => $usuario_id,
            "inf_config" => json_encode($config_informe, JSON_UNESCAPED_UNICODE)
        ];

        compraModel::agregar_informe_compra_model($datos_informe);

        /* ========== RESPUESTA EXITOSA ========== */

        


        $alerta = [
            'Alerta' => 'recargar',
            'Titulo' => 'Compra registrada',
            'texto' => "La compra {$numero_compra} se registró correctamente con {$totales['cantidadLotes']} lote(s) de medicamentos.",
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }
}
