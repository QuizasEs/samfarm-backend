<?php

require_once "mainModel.php";

class compraModel extends mainModel
{
    public static function registrar_compra_completa_model($datos)
    {
        $db = mainModel::conectar();
        $db->beginTransaction();

        $numero_compra = $datos['numero_compra'];
        $usuario_id = $datos['usuario_id'];
        $sucursal_id = $datos['sucursal_id'];
        $lotes = $datos['lotes'];
        $totales = $datos['totales'];

        try {
            $compra_id = self::agregar_compra_model($db, [
                "co_numero" => $numero_compra,
                "us_id" => $usuario_id,
                "su_id" => $sucursal_id,
                "co_subtotal" => $totales['subtotal'],
                "co_total" => $totales['total']
            ]);

            if ($compra_id <= 0) {
                $db->rollBack();
                return [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error al registrar',
                    'texto' => 'No se pudo registrar la compra en la base de datos.',
                    'Tipo' => 'error'
                ];
            }

            $mensajePropagacion = '';

            foreach ($lotes as $lote) {
                $medicamento_id = mainModel::limpiar_cadena($lote['id_medicamento'] ?? '');
                $numero_lote = mainModel::limpiar_cadena($lote['numero'] ?? '');
                $cantidad_cajas = isset($lote['cantidad']) ? (int)$lote['cantidad'] : 0;
                $cantidad_unidades = isset($lote['cantidad_unidades']) && (int)$lote['cantidad_unidades'] > 0 ? (int)$lote['cantidad_unidades'] : 1;
                $fecha_vencimiento = mainModel::limpiar_cadena($lote['vencimiento'] ?? null);
                $precio_compra_caja = $lote['costo_lista'] ?? 0;
                $precio_venta = is_numeric($lote['precioVenta']) ? (float)$lote['precioVenta'] : 0;
                $activar_lote = isset($lote['activar_lote']) && ($lote['activar_lote'] == 1 || $lote['activar_lote'] === true);

                $precio_compra = ($cantidad_unidades > 0) ? $precio_compra_caja / $cantidad_unidades : $precio_compra_caja;

                $stmt = $db->prepare("SELECT pr_id FROM medicamento WHERE med_id = :med_id");
                $stmt->bindParam(':med_id', $medicamento_id);
                $stmt->execute();
                $medicamento = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$medicamento) {
                    $db->rollBack();
                    return [
                        'Alerta' => 'simple',
                        'Titulo' => 'Medicamento no encontrado',
                        'texto' => 'El medicamento seleccionado no existe.',
                        'Tipo' => 'error'
                    ];
                }
                $pr_id_lote = $medicamento['pr_id'];

                if ($pr_id_lote) {
                    $stmt2 = $db->prepare("SELECT pr_id FROM proveedores WHERE pr_id = :pr_id");
                    $stmt2->bindParam(':pr_id', $pr_id_lote);
                    $stmt2->execute();
                    if (!$stmt2->fetch(PDO::FETCH_ASSOC)) {
                        $pr_id_lote = NULL;
                    }
                }

                if (empty($medicamento_id) || $cantidad_cajas <= 0 || $precio_compra_caja <= 0) {
                    $db->rollBack();
                    return [
                        'Alerta' => 'simple',
                        'Titulo' => 'Datos incompletos',
                        'texto' => 'Uno de los lotes tiene datos incompletos.',
                        'Tipo' => 'error'
                    ];
                }

                $lm_cant_actual_unidades = $cantidad_cajas * $cantidad_unidades;
                $lm_estado = $activar_lote ? 'activo' : 'en_espera';
                $subtotal_lote = ($lote['costo_lista'] ?? 0) * $cantidad_cajas;

                $lote_id = self::agregar_lote_model($db, [
                    "pr_id" => $pr_id_lote,
                    "pr_id_compra" => $pr_id_lote,
                    "med_id" => $medicamento_id,
                    "su_id" => $sucursal_id,
                    "lm_numero_lote" => $numero_lote,
                    "lm_cant_caja" => $cantidad_cajas,
                    "lm_cant_blister" => 1,
                    "lm_cant_unidad" => $cantidad_unidades,
                    "lm_total_unidades" => $lm_cant_actual_unidades,
                    "lm_cant_actual_cajas" => $cantidad_cajas,
                    "lm_cant_actual_unidades" => $lm_cant_actual_unidades,
                    "lm_precio_compra" => $precio_compra,
                    "lm_precio_costo" => $precio_compra_caja,
                    "lm_precio_venta" => $precio_venta,
                    "lm_fecha_vencimiento" => $fecha_vencimiento,
                    "lm_estado" => $lm_estado,
                    "lm_costo_lista" => $precio_compra_caja,
                    "lm_margen_u" => $lote['margen_unitario'] ?? null,
                    "lm_margen_c" => $lote['margen_caja'] ?? null,
                    "lm_precio_min_u" => $lote['precio_min_unitario'] ?? null,
                    "lm_precio_min_c" => $lote['precio_min_caja'] ?? null
                ]);

                if ($lote_id <= 0) {
                    $db->rollBack();
                    return [
                        'Alerta' => 'simple',
                        'Titulo' => 'Error al registrar lote',
                        'texto' => 'No se pudo registrar el lote del medicamento.',
                        'Tipo' => 'error'
                    ];
                }

                $propagarPrecio = !empty($precio_venta) && $precio_venta > 0 && !empty($precio_compra) && $precio_compra > 0 && $precio_venta >= $precio_compra;

                if (!$propagarPrecio) {
                    $mensajePropagacion .= " Lote {$numero_lote}: precios inválidos para propagar.";
                }

                $lotes_existentes = [];
                if ($propagarPrecio) {
                    $sql_lotes = $db->prepare("
                        SELECT lm_id, lm_numero_lote, lm_precio_venta, su_id
                        FROM lote_medicamento
                        WHERE med_id = :med_id
                          AND lm_estado = 'activo'
                          AND lm_cant_actual_unidades > 0
                          AND lm_id != :lote_id_excluir
                    ");
                    $sql_lotes->bindParam(":med_id", $medicamento_id);
                    $sql_lotes->bindParam(":lote_id_excluir", $lote_id);
                    $sql_lotes->execute();
                    $lotes_existentes = $sql_lotes->fetchAll(PDO::FETCH_ASSOC);
                }

                foreach ($lotes_existentes as $lote_existente) {
                    $datos_up = [
                        'ID' => $lote_existente['lm_id'],
                        'lm_costo_lista' => $precio_compra_caja,
                        'lm_precio_costo' => $precio_compra_caja,
                        'lm_precio_compra' => $precio_compra,
                        'lm_cant_unidad' => $cantidad_unidades,
                        'lm_margen_u' => $lote['margen_unitario'] ?? null,
                        'lm_margen_c' => $lote['margen_caja'] ?? null,
                        'lm_precio_venta' => $precio_venta,
                        'lm_precio_min_u' => $lote['precio_min_unitario'] ?? null,
                        'lm_precio_min_c' => $lote['precio_min_caja'] ?? null
                    ];

                    $resultado_update = loteModel::actualizar_lote_model($db, $datos_up);

                    if ($resultado_update->rowCount() == 1) {
                        loteModel::registrar_historial_lote_model($db, [
                            'lm_id' => $lote_existente['lm_id'],
                            'us_id' => $usuario_id,
                            'hl_accion' => 'balance',
                            'hl_descripcion' => "Balance de precios por compra #{$numero_compra} - Lote {$lote_existente['lm_numero_lote']}"
                        ]);

                        preciosModel::registrar_balance_precio_model(
                            $db,
                            $lote_existente['lm_id'],
                            $usuario_id,
                            $lote_existente['lm_precio_venta'],
                            $precio_venta,
                            json_encode([
                                'costo_lista' => $precio_compra_caja,
                                'precio_costo' => $precio_compra_caja,
                                'precio_compra' => $precio_compra,
                                'unidades_caja' => $cantidad_unidades,
                                'margen_u' => $lote['margen_unitario'] ?? null,
                                'margen_c' => $lote['margen_caja'] ?? null,
                                'precio_venta' => $precio_venta,
                                'precio_min_u' => $lote['precio_min_unitario'] ?? null,
                                'precio_min_c' => $lote['precio_min_caja'] ?? null,
                                'origen' => 'compra',
                                'compra_numero' => $numero_compra
                            ])
                        );
                    }
                }

                $historial_result = self::registrar_historial_Lote_model($db, [
                    "lm_id" => $lote_id,
                    "us_id" => $usuario_id,
                    "hl_accion" => "creacion",
                    "hl_descripcion" => "Lote creado por compra #{$numero_compra} en estado '{$lm_estado}'."
                ]);

                if ($historial_result->rowCount() <= 0) {
                    $db->rollBack();
                    return [
                        'Alerta' => 'simple',
                        'Titulo' => 'Error al registrar historial',
                        'texto' => 'No se pudo registrar el historial del lote.',
                        'Tipo' => 'error'
                    ];
                }

                $detalle_result = self::agregar_detalle_compra_model($db, [
                    "co_id" => $compra_id,
                    "med_id" => $medicamento_id,
                    "lm_id" => $lote_id,
                    "cantidad" => $lm_cant_actual_unidades,
                    "precio_unitario" => $precio_compra,
                    "descuento" => 0.00,
                    "subtotal" => $subtotal_lote
                ]);

                if ($detalle_result->rowCount() <= 0) {
                    $db->rollBack();
                    return [
                        'Alerta' => 'simple',
                        'Titulo' => 'Error en detalle',
                        'texto' => 'No se pudo registrar el detalle de la compra.',
                        'Tipo' => 'error'
                    ];
                }

                if ($lm_estado === 'activo') {
                    $inv_result = self::actualizar_inventario_model($db, [
                        "su_id" => $sucursal_id,
                        "med_id" => $medicamento_id,
                        "inv_total_cajas" => $cantidad_cajas,
                        "inv_total_unidades" => $lm_cant_actual_unidades,
                        "inv_total_valorado" => $subtotal_lote
                    ]);

                    if (!$inv_result) {
                        $db->rollBack();
                        return [
                            'Alerta' => 'simple',
                            'Titulo' => 'Error inventario',
                            'texto' => 'No se pudo actualizar el inventario.',
                            'Tipo' => 'error'
                        ];
                    }

                    $mov_result = self::agregar_movimiento_inventario_model($db, [
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
                    ]);

                    if ($mov_result->rowCount() <= 0) {
                        $db->rollBack();
                        return [
                            'Alerta' => 'simple',
                            'Titulo' => 'Error movimiento',
                            'texto' => 'No se pudo registrar el movimiento de inventario.',
                            'Tipo' => 'error'
                        ];
                    }

                    $historial_activacion_result = self::registrar_historial_Lote_model($db, [
                        "lm_id" => $lote_id,
                        "us_id" => $usuario_id,
                        "hl_accion" => "activacion",
                        "hl_descripcion" => "Lote activado automáticamente al registrar compra #{$numero_compra}."
                    ]);

                    if ($historial_activacion_result->rowCount() <= 0) {
                        $db->rollBack();
                        return [
                            'Alerta' => 'simple',
                            'Titulo' => 'Error historial',
                            'texto' => 'No se pudo registrar el historial de activación.',
                            'Tipo' => 'error'
                        ];
                    }
                }
            }

            $db->commit();

            $textoRespuesta = "La compra {$numero_compra} se registró correctamente con " . count($lotes) . " lote(s).";
            if (!empty($mensajePropagacion)) {
                $textoRespuesta .= "<br><br><strong>Advertencia:</strong> $mensajePropagacion";
            }

            return [
                'Alerta' => 'recargar',
                'Titulo' => 'Compra registrada',
                'texto' => $textoRespuesta,
                'Tipo' => 'success'
            ];

        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en registrar_compra_completa_model: " . $e->getMessage());
            return [
                'Alerta' => 'simple',
                'Titulo' => 'Excepción',
                'texto' => $e->getMessage(),
                'Tipo' => 'error'
            ];
        }
    }

    public static function agregar_compra_model($db = null, $datos): int
    {
        $db = $db ?? mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO compras
                (co_numero, us_id, su_id, co_subtotal, co_total)
            VALUES
                (:co_numero, :us_id, :su_id, :co_subtotal, :co_total)
        ");
        $sql->bindParam(":co_numero", $datos['co_numero']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":co_subtotal", $datos['co_subtotal']);
        $sql->bindParam(":co_total", $datos['co_total']);

        $sql->execute();
        return (int) $db->lastInsertId();
    }

    public static function agregar_detalle_compra_model($db = null, $item)
    {
        $db = $db ?? mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO detalle_compra
                (co_id, med_id, lm_id, dc_cantidad, dc_precio_unitario, dc_descuento, dc_subtotal)
            VALUES
                (:co_id, :med_id, :lm_id, :cantidad, :precio_unitario, :descuento, :subtotal)
        ");
        $sql->bindParam(":co_id", $item['co_id']);
        $sql->bindParam(":med_id", $item['med_id']);
        $sql->bindParam(":lm_id", $item['lm_id']);
        $sql->bindParam(":cantidad", $item['cantidad']);
        $sql->bindParam(":precio_unitario", $item['precio_unitario']);
        $sql->bindParam(":descuento", $item['descuento']);
        $sql->bindParam(":subtotal", $item['subtotal']);
        $sql->execute();
        return $sql;
    }

    public static function agregar_lote_model($db = null, $datos)
    {
        $db = $db ?? mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO lote_medicamento
            (pr_id, pr_id_compra, med_id, su_id, lm_numero_lote, 
            lm_cant_caja, lm_cant_blister, lm_cant_unidad,
            lm_cant_actual_cajas, lm_cant_actual_unidades,
            lm_costo_lista, lm_precio_compra, lm_precio_venta,
            lm_margen_u, lm_margen_c, lm_precio_min_u, lm_precio_min_c,
            lm_fecha_ingreso, lm_fecha_vencimiento, lm_estado)
            VALUES
            (:pr_id, :pr_id_compra, :med_id, :su_id, :lm_numero_lote, 
            :lm_cant_caja, :lm_cant_blister, :lm_cant_unidad,
            :lm_cant_actual_cajas, :lm_cant_actual_unidades,
            :lm_costo_lista, :lm_precio_compra, :lm_precio_venta,
            :lm_margen_u, :lm_margen_c, :lm_precio_min_u, :lm_precio_min_c,
            NOW(), :lm_fecha_vencimiento, :lm_estado)
        ");

        $sql->bindParam(":pr_id", $datos['pr_id']);
        $sql->bindParam(":pr_id_compra", $datos['pr_id_compra']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":lm_numero_lote", $datos['lm_numero_lote']);
        $sql->bindParam(":lm_cant_caja", $datos['lm_cant_caja']);
        $sql->bindParam(":lm_cant_blister", $datos['lm_cant_blister']);
        $sql->bindParam(":lm_cant_unidad", $datos['lm_cant_unidad']);
        $sql->bindParam(":lm_cant_actual_cajas", $datos['lm_cant_actual_cajas']);
        $sql->bindParam(":lm_cant_actual_unidades", $datos['lm_cant_actual_unidades']);
        $sql->bindParam(":lm_costo_lista", $datos['lm_costo_lista']);
        $sql->bindParam(":lm_precio_compra", $datos['lm_precio_compra']);
        $sql->bindParam(":lm_precio_venta", $datos['lm_precio_venta']);
        $sql->bindParam(":lm_margen_u", $datos['lm_margen_u']);
        $sql->bindParam(":lm_margen_c", $datos['lm_margen_c']);
        $sql->bindParam(":lm_precio_min_u", $datos['lm_precio_min_u']);
        $sql->bindParam(":lm_precio_min_c", $datos['lm_precio_min_c']);
        $sql->bindParam(":lm_fecha_vencimiento", $datos['lm_fecha_vencimiento']);
        $sql->bindParam(":lm_estado", $datos['lm_estado']);

        $sql->execute();
        return (int) $db->lastInsertId();
    }

    public static function registrar_historial_Lote_model($db = null, $datos)
    {
        $db = $db ?? mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
            VALUES (:lm_id, :us_id, :hl_accion, :hl_descripcion)
        ");
        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":hl_accion", $datos['hl_accion']);
        $sql->bindParam(":hl_descripcion", $datos['hl_descripcion']);
        $sql->execute();
        return $sql;
    }

    /**************************************************************************
     * ACTUALIZAR INVENTARIO - CORREGIDO
     * - Usa ON DUPLICATE KEY UPDATE (requiere UNIQUE KEY en su_id,med_id)
     * - Si existe: SUMA las cantidades
     * - Si no existe: CREA el registro
     **************************************************************************/
    public static function actualizar_inventario_model($db, $datos)
    {
        $db = $db ?? mainModel::conectar();

        try {
            $sql = $db->prepare("
                INSERT INTO inventarios 
                (su_id, med_id, inv_total_cajas, inv_total_unidades, inv_total_valorado, inv_creado_en, inv_actualizado_en)
                VALUES 
                (:su_id, :med_id, :inv_total_cajas, :inv_total_unidades, :inv_total_valorado, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    inv_total_cajas = inv_total_cajas + VALUES(inv_total_cajas),
                    inv_total_unidades = inv_total_unidades + VALUES(inv_total_unidades),
                    inv_total_valorado = inv_total_valorado + VALUES(inv_total_valorado),
                    inv_actualizado_en = NOW()
            ");

            $sql->bindParam(":su_id", $datos['su_id'], PDO::PARAM_INT);
            $sql->bindParam(":med_id", $datos['med_id'], PDO::PARAM_INT);
            $sql->bindParam(":inv_total_cajas", $datos['inv_total_cajas'], PDO::PARAM_INT);
            $sql->bindParam(":inv_total_unidades", $datos['inv_total_unidades'], PDO::PARAM_INT);
            $sql->bindParam(":inv_total_valorado", $datos['inv_total_valorado']);

            $sql->execute();

            // Log para debugging
            error_log("INVENTARIO ACTUALIZADO: med_id={$datos['med_id']}, su_id={$datos['su_id']}, +{$datos['inv_total_unidades']} unidades, +{$datos['inv_total_cajas']} cajas");

            return $sql;
        } catch (PDOException $e) {
            error_log("ERROR en actualizar_inventario_model: " . $e->getMessage());
            return false;
        }
    }

    public static function agregar_movimiento_inventario_model($db = null, $datos)
    {
        $db = $db ?? mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo)
            VALUES
                (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo)
        ");

        $sql->bindParam(":lm_id", $datos['lm_id']);
        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":mi_tipo", $datos['mi_tipo']);
        $sql->bindParam(":mi_cantidad", $datos['mi_cantidad']);
        $sql->bindParam(":mi_unidad", $datos['mi_unidad']);
        $sql->bindParam(":mi_referencia_tipo", $datos['mi_referencia_tipo']);
        $sql->bindParam(":mi_referencia_id", $datos['mi_referencia_id']);
        $sql->bindParam(":mi_motivo", $datos['mi_motivo']);

        $sql->execute();
        return $sql;
    }

    /* registrar un informe en informes con el tipo "compra" */
    public static function agregar_informe_compra_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO informes (inf_nombre, inf_tipo, inf_usuario, inf_config)
            VALUES (:inf_nombre, 'compra', :inf_usuario, :inf_config)
        ");
        $sql->bindParam(":inf_nombre", $datos['inf_nombre']);
        $sql->bindParam(":inf_usuario", $datos['inf_usuario']);
        $sql->bindParam(":inf_config", $datos['inf_config']);

        $sql->execute();
        return $sql;
    }

    /* registrar informe de compra en la tabla informes_compra (nueva tabla estructurada) */
    public static function agregar_informe_compra_estructurado_model($datos)
    {
        $db = mainModel::conectar();
        $sql = $db->prepare("
            INSERT INTO informes_compra
                (co_id, pr_id, us_id, su_id, ic_numero_compra, ic_numero_factura, ic_fecha_compra, 
                 ic_subtotal, ic_impuestos, ic_total, ic_cantidad_lotes, ic_config_json)
            VALUES
                (:co_id, :pr_id, :us_id, :su_id, :ic_numero_compra, :ic_numero_factura, :ic_fecha_compra,
                 :ic_subtotal, :ic_impuestos, :ic_total, :ic_cantidad_lotes, :ic_config_json)
        ");
        
        $sql->bindParam(":co_id", $datos['co_id']);
        $sql->bindParam(":pr_id", $datos['pr_id']);
        $sql->bindParam(":us_id", $datos['us_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":ic_numero_compra", $datos['ic_numero_compra']);
        $sql->bindParam(":ic_numero_factura", $datos['ic_numero_factura']);
        $sql->bindParam(":ic_fecha_compra", $datos['ic_fecha_compra']);
        $sql->bindParam(":ic_subtotal", $datos['ic_subtotal']);
        $sql->bindParam(":ic_impuestos", $datos['ic_impuestos']);
        $sql->bindParam(":ic_total", $datos['ic_total']);
        $sql->bindParam(":ic_cantidad_lotes", $datos['ic_cantidad_lotes']);
        $sql->bindParam(":ic_config_json", $datos['ic_config_json']);

        $sql->execute();
        return $sql;
    }
}
