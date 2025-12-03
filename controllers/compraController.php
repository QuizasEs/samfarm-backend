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
                    m.med_descripcion LIKE '$busqueda'
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
        /* validamos y limpiamos cadena entrante */
        $numero_compra = mainModel::limpiar_cadena($_POST['Numero_compra_reg']);
        $proveedor_id = mainModel::limpiar_cadena($_POST['Proveedor_reg']);
        $laboratorio_id = mainModel::limpiar_cadena($_POST['Laboratorio_factura_reg']);
        $fecha_factura = mainModel::limpiar_cadena($_POST['Fecha_factura_reg']);
        $numero_factura = mainModel::limpiar_cadena($_POST['Numero_factura_reg']);
        $impuesto = mainModel::limpiar_cadena($_POST['impuestos_reg'] ?? 0);
        $usuario_id = mainModel::limpiar_cadena($_SESSION['id_smp']);
        $sucursal_id = mainModel::limpiar_cadena($_SESSION['sucursal_smp']);

        $lotes_json = $_POST['lotes_json'] ?? '[]';
        $totales_json = $_POST['totales_json'] ?? '{}';

        $lotes = json_decode($lotes_json, true);
        $totales = json_decode($totales_json, true);

        /* obtener datos del proveedor para construir razón social */
        $conexion = mainModel::conectar();
        $stmt_proveedor = $conexion->prepare("SELECT pr_nombres, pr_nit FROM proveedores WHERE pr_id = :pr_id");
        $stmt_proveedor->bindParam(':pr_id', $proveedor_id);
        $stmt_proveedor->execute();
        $proveedor = $stmt_proveedor->fetch(PDO::FETCH_ASSOC);

        if (!$proveedor) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Proveedor no válido',
                'texto' => 'El proveedor seleccionado no existe.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $razon_social = $proveedor['pr_nombres'];
        if (!empty($proveedor['pr_nit'])) {
            $razon_social .= ' - NIT: ' . $proveedor['pr_nit'];
        }

        /* validamos los campos obligatorios */
        if (
            empty($numero_compra) || empty($proveedor_id) ||
            empty($laboratorio_id) || empty($fecha_factura) || empty($numero_factura)
        ) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos faltantes',
                'texto' => 'Por favor completa todos los campos obligatorios.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* verificamos que los lotes existan en la lista */
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

        /* validamos el formato de totales */
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

        /* preparamos datos para el registro de compra */
        $datos_compra = [
            "co_numero" => $numero_compra,
            "la_id" => $laboratorio_id,
            "us_id" => $usuario_id,
            "su_id" => $sucursal_id,
            "pr_id" => $proveedor_id,
            "co_subtotal" => $totales['subtotal'],
            "co_impuesto" => $totales['impuestos'] ?? 0,
            "co_total" => $totales['total'],
            "co_numero_factura" => $numero_factura,
            "co_fecha_factura" => $fecha_factura,
            "co_razon_social" => $razon_social
        ];

        /* insertamos compra */
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

        /* procesamos cada lote */
        foreach ($lotes as $lote) {
            /* limpiamos datos del lote */
            $medicamento_id = mainModel::limpiar_cadena($lote['id_medicamento'] ?? '');
            $numero_lote = mainModel::limpiar_cadena($lote['numero'] ?? '');
            $cantidad_cajas = isset($lote['cantidad']) ? (int)$lote['cantidad'] : 0;
            $cantidad_blister = isset($lote['cantidad_blister']) && (int)$lote['cantidad_blister'] > 0 ? (int)$lote['cantidad_blister'] : 1;
            $cantidad_unidades = isset($lote['cantidad_unidades']) && (int)$lote['cantidad_unidades'] > 0 ? (int)$lote['cantidad_unidades'] : 1;
            $fecha_vencimiento = mainModel::limpiar_cadena($lote['vencimiento'] ?? null);
            $precio_compra = is_numeric($lote['precioCompra']) ? (float)$lote['precioCompra'] : 0;
            $precio_venta = is_numeric($lote['precioVenta']) ? (float)$lote['precioVenta'] : 0;
            $activar_lote = isset($lote['activar_lote']) && ($lote['activar_lote'] == 1 || $lote['activar_lote'] === true);

            /* Validar datos del lote */
            if (empty($medicamento_id) || $cantidad_cajas <= 0 || $precio_compra <= 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Datos incompletos',
                    'texto' => 'Uno de los lotes tiene datos incompletos.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            /* cálculos de cantidades */
            $lm_cant_caja = $cantidad_cajas;
            $lm_cant_blister = $cantidad_blister;
            $lm_cant_unidad = $cantidad_unidades;
            $lm_total_unidades = $lm_cant_caja * $lm_cant_blister * $lm_cant_unidad;
            $lm_cant_actual_cajas = $lm_cant_caja;
            $lm_cant_actual_unidades = $lm_total_unidades;
            $subtotal_lote = $cantidad_cajas * $precio_compra;
            $lm_estado = $activar_lote ? 'activo' : 'en_espera';

            /* datos de lote medicamento */
            $datos_lote = [
                "pr_id" => $proveedor_id,
                "pr_id_compra" => $compra_id,
                "med_id" => $medicamento_id,
                "su_id" => $sucursal_id,
                "lm_numero_lote" => $numero_lote,
                "lm_cant_caja" => $lm_cant_caja,
                "lm_cant_blister" => $lm_cant_blister,
                "lm_cant_unidad" => $lm_cant_unidad,
                "lm_total_unidades" => $lm_total_unidades,
                "lm_cant_actual_cajas" => $lm_cant_actual_cajas,
                "lm_cant_actual_unidades" => $lm_cant_actual_unidades,
                "lm_precio_compra" => $precio_compra,
                "lm_precio_venta" => $precio_venta,
                "lm_fecha_vencimiento" => $fecha_vencimiento,
                "lm_estado" => $lm_estado
            ];

            $lote_id = compraModel::agregar_lote_model($datos_lote);

            if ($lote_id <= 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error al registrar lote',
                    'texto' => 'No se pudo registrar el lote del medicamento.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            /* historial de creación del lote */
            $historial = [
                "lm_id" => $lote_id,
                "us_id" => $usuario_id,
                "hl_accion" => "creacion",
                "hl_descripcion" => "Lote creado por compra #{$numero_compra} en estado '{$lm_estado}'."
            ];

            $historial_result = compraModel::registrar_historial_Lote_model($historial);

            if ($historial_result->rowCount() <= 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error al registrar historial',
                    'texto' => 'No se pudo registrar el historial del lote.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            /* detalle de compra */
            $datos_detalle = [
                "co_id" => $compra_id,
                "med_id" => $medicamento_id,
                "lm_id" => $lote_id,
                "cantidad" => $lm_cant_actual_unidades,
                "precio_unitario" => $precio_compra,
                "descuento" => 0.00,
                "subtotal" => $subtotal_lote
            ];

            $detalle_result = compraModel::agregar_detalle_compra_model($datos_detalle);

            if ($detalle_result->rowCount() <= 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error en detalle',
                    'texto' => 'No se pudo registrar el detalle de la compra.',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            /* SI EL LOTE SE ACTIVA EN LA COMPRA */
            if ($lm_estado === 'activo') {
                /* actualizar inventario consolidado */
                $datos_inventario = [
                    "su_id" => $sucursal_id,
                    "med_id" => $medicamento_id,
                    "inv_total_cajas" => $lm_cant_actual_cajas,
                    "inv_total_unidades" => $lm_cant_actual_unidades,
                    "inv_total_valorado" => $subtotal_lote
                ];

                $inv_result = compraModel::actualizar_inventario_model($datos_inventario);

                // CORRECCIÓN: Validación simplificada
                if (!$inv_result) {
                    $alerta = [
                        'Alerta' => 'simple',
                        'Titulo' => 'Error inventario',
                        'texto' => 'No se pudo actualizar el inventario.',
                        'Tipo' => 'error'
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                /* registrar movimiento de inventario */
                $datos_movimiento = [
                    "lm_id" => $lote_id,
                    "med_id" => $medicamento_id,
                    "su_id" => $sucursal_id,
                    "us_id" => $usuario_id,
                    "mi_tipo" => "entrada",
                    "mi_cantidad" => $lm_cant_actual_unidades,
                    "mi_unidad" => "unidad",
                    "mi_referencia_tipo" => "compra",
                    "mi_referencia_id" => $compra_id,
                    "mi_motivo" => "Ingreso por compra {$numero_compra}"
                ];

                $mov_result = compraModel::agregar_movimiento_inventario_model($datos_movimiento);

                if ($mov_result->rowCount() <= 0) {
                    $alerta = [
                        'Alerta' => 'simple',
                        'Titulo' => 'Error movimiento',
                        'texto' => 'No se pudo registrar el movimiento de inventario.',
                        'Tipo' => 'error'
                    ];
                    echo json_encode($alerta);
                    exit();
                }

                /* historial de activación */
                $datos_historial_activacion = [
                    "lm_id" => $lote_id,
                    "us_id" => $usuario_id,
                    "hl_accion" => "activacion",
                    "hl_descripcion" => "Lote activado automáticamente al registrar compra #{$numero_compra}."
                ];

                $historial_activacion_result = compraModel::registrar_historial_Lote_model($datos_historial_activacion);

                if ($historial_activacion_result->rowCount() <= 0) {
                    $alerta = [
                        'Alerta' => 'simple',
                        'Titulo' => 'Error historial',
                        'texto' => 'No se pudo registrar el historial de activación.',
                        'Tipo' => 'error'
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }
        } /* fin foreach lotes */

        /* preparar informe de compra */
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
                    "vencimiento" => $lote['vencimiento'],
                    "activar_lote" => $lote['activar_lote'] ?? false
                ];
            }, $lotes)
        ];

        $datos_informe = [
            "inf_nombre" => "Compra {$numero_compra} - {$razon_social}",
            "inf_usuario" => $usuario_id,
            "inf_config" => json_encode($config_informe, JSON_UNESCAPED_UNICODE)
        ];

        $informe_result = compraModel::agregar_informe_compra_model($datos_informe);

        if ($informe_result->rowCount() <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Advertencia',
                'texto' => 'Compra registrada pero no se pudo crear el informe.',
                'Tipo' => 'warning'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* respuesta exitosa */
        $alerta = [
            'Alerta' => 'recargar',
            'Titulo' => 'Compra registrada',
            'texto' => "La compra {$numero_compra} se registró correctamente con " . count($lotes) . " lote(s).",
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }
}
