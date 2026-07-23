<?php

if ($peticionAjax) {
    require_once "../models/compraModel.php";
    require_once "../models/loteModel.php";
    require_once "../models/preciosModel.php";
} else {
    require_once "./models/compraModel.php";
    require_once "./models/loteModel.php";
    require_once "./models/preciosModel.php";
}

class compraController extends compraModel
{

    public function buscar_medicamento_controller(array $filtros)
    {
        $sql = "
            SELECT
                m.med_id,
                m.med_nombre_quimico AS nombre,
                m.med_presentacion,
                pr.pr_razon_social AS proveedor,
                m.med_codigo_barras,
                ff.ff_nombre AS forma,
                vd.vd_nombre AS via,
                uf.uf_nombre AS uso
            FROM medicamento AS m
            LEFT JOIN proveedores AS pr ON m.pr_id = pr.pr_id
            LEFT JOIN forma_farmaceutica AS ff ON m.ff_id = ff.ff_id
            LEFT JOIN via_de_administracion AS vd ON m.vd_id = vd.vd_id
            LEFT JOIN uso_farmacologico AS uf ON m.uf_id = uf.uf_id
            WHERE 1 = 1
        ";

        if (!empty($filtros['proveedor'])) {
            $sql .= " AND m.pr_id = " . intval($filtros['proveedor']);
        }
        if (!empty($filtros['forma'])) {
            $sql .= " AND m.ff_id = " . intval($filtros['forma']);
        }
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = " . intval($filtros['via']);
        }
        if (!empty($filtros['uso'])) {
            $sql .= " AND m.uf_id = " . intval($filtros['uso']);
        }
        if (!empty($filtros['termino'])) {
            $busqueda = "%" . $filtros['termino'] . "%";
            $sql .= "
                AND (
                    m.med_nombre_quimico LIKE '$busqueda' OR
                    m.med_presentacion LIKE '$busqueda' OR
                    m.med_descripcion LIKE '$busqueda' OR
                    m.med_codigo_barras LIKE '$busqueda' OR
                    pr.pr_razon_social LIKE '$busqueda'
                )
            ";
        }

        $sql .= " ORDER BY m.med_nombre_quimico ASC limit 100";

        $respuesta = mainModel::ejecutar_consulta_simple($sql);
        return $respuesta;
    }

    public function ultimo_lote_por_medicamento_controller($med_id)
    {
        if ($med_id <= 0) {
            return ['error' => 'ID inválido'];
        }

        try {
            $sql = mainModel::conectar()->prepare("
                SELECT
                    lm.lm_id, lm.med_id, lm.su_id, lm.pr_id, lm.pr_id_compra,
                    lm.lm_numero_lote, lm.lm_cant_caja, lm.lm_cant_blister, lm.lm_cant_unidad,
                    lm.lm_total_unidades, lm.lm_cant_actual_cajas, lm.lm_cant_actual_unidades,
                    lm.lm_costo_lista, lm.lm_precio_costo, lm.lm_precio_compra, lm.lm_precio_venta,
                    lm.lm_fecha_ingreso, lm.lm_fecha_vencimiento, lm.lm_estado,
                    lm.lm_creado_en, lm.lm_actualizado_en, lm.lm_origen_id, lm.lm_tr_bloqueado,
                    lm.lm_margen_u, lm.lm_margen_c, lm.lm_precio_min_u, lm.lm_precio_min_c,
                    m.med_nombre_quimico AS med_nombre, m.med_principio_activo,
                    m.med_presentacion, m.med_accion_farmacologica, m.med_codigo_barras,
                    m.med_version_comercial, m.ff_id, m.uf_id, m.vd_id, m.pr_id,
                    ff.ff_nombre AS forma_farmaceutica, uf.uf_nombre AS uso_farmacologico,
                    vd.vd_nombre AS via_administracion,
                    COALESCE(lp.pr_razon_social, mp.pr_razon_social, 'Sin proveedor') AS proveedor_nombres,
                    s.su_nombre AS sucursal_nombre
                FROM lote_medicamento lm
                LEFT JOIN medicamento m ON m.med_id = lm.med_id
                LEFT JOIN forma_farmaceutica ff ON m.ff_id = ff.ff_id
                LEFT JOIN uso_farmacologico uf ON m.uf_id = uf.uf_id
                LEFT JOIN via_de_administracion vd ON m.vd_id = vd.vd_id
                LEFT JOIN proveedores lp ON lp.pr_id = lm.pr_id
                LEFT JOIN proveedores mp ON mp.pr_id = m.pr_id
                LEFT JOIN sucursales s ON s.su_id = lm.su_id
                WHERE lm.med_id = :med_id
                ORDER BY lm.lm_id DESC
                LIMIT 1
            ");
            $sql->bindParam(':med_id', $med_id);
            $sql->execute();

            $data = $sql->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                return [];
            }

            $data['lm_cant_caja'] = (int)($data['lm_cant_caja'] ?? 0);
            $data['lm_cant_blister'] = max(1, (int)($data['lm_cant_blister'] ?? 1));
            $data['lm_cant_unidad'] = max(1, (int)($data['lm_cant_unidad'] ?? 1));
            $data['lm_total_unidades'] = (int)($data['lm_total_unidades'] ?? 0);
            $data['lm_cant_actual_cajas'] = (int)($data['lm_cant_actual_cajas'] ?? 0);
            $data['lm_cant_actual_unidades'] = (int)($data['lm_cant_actual_unidades'] ?? 0);

            $data['lm_costo_lista'] = (float)($data['lm_costo_lista'] ?? 0);
            $data['lm_precio_costo'] = (float)($data['lm_precio_costo'] ?? 0);
            $data['lm_precio_venta'] = (float)($data['lm_precio_venta'] ?? 0);
            $data['lm_margen_u'] = isset($data['lm_margen_u']) ? (float)$data['lm_margen_u'] : null;
            $data['lm_margen_c'] = isset($data['lm_margen_c']) ? (float)$data['lm_margen_c'] : null;
            $data['lm_precio_min_u'] = (float)($data['lm_precio_min_u'] ?? 0);
            $data['lm_precio_min_c'] = (float)($data['lm_precio_min_c'] ?? 0);

            if ($data['lm_total_unidades'] <= 0 && $data['lm_cant_caja'] > 0 && $data['lm_cant_unidad'] > 0) {
                $data['lm_total_unidades'] = $data['lm_cant_caja'] * $data['lm_cant_unidad'];
            }
            if ($data['lm_cant_actual_unidades'] <= 0 && $data['lm_total_unidades'] > 0) {
                $data['lm_cant_actual_unidades'] = $data['lm_total_unidades'];
            }
            if ($data['lm_cant_actual_cajas'] <= 0 && $data['lm_cant_caja'] > 0) {
                $data['lm_cant_actual_cajas'] = $data['lm_cant_caja'];
            }

            return $data;
        } catch (Exception $e) {
            error_log("Error en ultimo_lote_por_medicamento_controller: " . $e->getMessage());
            return ['error' => 'Error al cargar datos del último lote'];
        }
    }

    public function agregar_compra_controller()
    {
        $numero_compra = mainModel::limpiar_cadena($_POST['Numero_compra_reg'] ?? '');
        $usuario_id = mainModel::limpiar_cadena($_SESSION['id_smp']);
        $sucursal_id = mainModel::limpiar_cadena($_POST['sucursal_reg'] ?? $_SESSION['sucursal_smp']);

        $lotes_json = trim($_POST['lotes_json'] ?? '[]');
        $totales_json = trim($_POST['totales_json'] ?? '{}');

        $lotes = json_decode($lotes_json, true);
        $totales = json_decode($totales_json, true);

        if ($lotes === null && $lotes_json !== '[]') {
            echo json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error en datos',
                'texto' => 'Los datos de lotes son inválidos.',
                'Tipo' => 'error'
            ]);
            exit();
        }

        if (empty($lotes) || !is_array($lotes) || count($lotes) === 0) {
            echo json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Sin medicamentos',
                'texto' => 'No has agregado medicamentos a la compra.',
                'Tipo' => 'error'
            ]);
            exit();
        }

        if (empty($totales) || !isset($totales['subtotal']) || !isset($totales['total'])) {
            echo json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error en totales',
                'texto' => 'Los totales de la compra no son válidos.',
                'Tipo' => 'error'
            ]);
            exit();
        }

        $resultado = self::registrar_compra_completa_model([
            'numero_compra' => $numero_compra,
            'usuario_id' => $usuario_id,
            'sucursal_id' => $sucursal_id,
            'lotes' => $lotes,
            'totales' => $totales
        ]);

        echo json_encode($resultado);
        exit();
    }
}
