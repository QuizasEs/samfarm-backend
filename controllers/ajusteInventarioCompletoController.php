<?php

if ($peticionAjax) {
    require_once "../models/ajusteInventarioCompletoModel.php";
} else {
    require_once "./models/ajusteInventarioCompletoModel.php";
}

class ajusteInventarioCompletoController extends ajusteInventarioCompletoModel
{
    /**
     * Controlador para obtener los datos iniciales de la vista de ajuste.
     */
    public function obtener_datos_iniciales_controlador()
    {
        $sucursales = ajusteInventarioCompletoModel::obtener_sucursales_modelo();
        return [
            "sucursales" => $sucursales->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    /**
     * Controlador para buscar medicamentos.
     */
    public function buscar_medicamentos_controlador()
    {
        $termino = mainModel::limpiar_cadena($_POST['termino'] ?? '');
        $sucursal_id = mainModel::limpiar_cadena($_POST['sucursal_id'] ?? '');

        if (empty($termino) && empty($sucursal_id)) {
            return json_encode(["error" => "Debe proporcionar un término de búsqueda o una sucursal."]);
        }

        $resultados = ajusteInventarioCompletoModel::buscar_medicamentos_modelo($termino, $sucursal_id);
        return json_encode($resultados->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Controlador para obtener los detalles de un medicamento y sus lotes.
     */
    public function obtener_detalles_controlador() {
        $medicamento_id = mainModel::limpiar_cadena($_POST['medicamento_id'] ?? 0);
        $sucursal_id = mainModel::limpiar_cadena($_POST['sucursal_id'] ?? 0);

        if (empty($medicamento_id) || empty($sucursal_id)) {
            return json_encode(["error" => "ID de medicamento o sucursal no válido."]);
        }

        $medicamento = ajusteInventarioCompletoModel::obtener_medicamento_completo_modelo($medicamento_id)->fetch(PDO::FETCH_ASSOC);
        $lotes = ajusteInventarioCompletoModel::obtener_lotes_medicamento_modelo($medicamento_id, $sucursal_id)->fetchAll(PDO::FETCH_ASSOC);

        if (!$medicamento) {
            return json_encode(["error" => "No se encontró el medicamento."]);
        }

        return json_encode([
            "medicamento" => $medicamento,
            "lotes" => $lotes
        ]);
    }

    /**
     * Controlador para actualizar los datos del medicamento.
     */
    public function actualizar_medicamento_controlador() {
        $medicamento_id = mainModel::limpiar_cadena($_POST['medicamento_id'] ?? 0);
        $nombre = mainModel::limpiar_cadena($_POST['nombre'] ?? '');
        $principio = mainModel::limpiar_cadena($_POST['principio'] ?? '');
        $codigo = mainModel::limpiar_cadena($_POST['codigo'] ?? '');
        $proveedor_id = mainModel::limpiar_cadena($_POST['proveedor_id'] ?? 0);
        $ff_id = mainModel::limpiar_cadena($_POST['ff_id'] ?? 0);
        $uf_id = mainModel::limpiar_cadena($_POST['uf_id'] ?? 0);
        $via_administracion = mainModel::limpiar_cadena($_POST['via_administracion'] ?? '');
        $cant_caja = mainModel::limpiar_cadena($_POST['cant_caja'] ?? 0);
        $cant_blister = mainModel::limpiar_cadena($_POST['cant_blister'] ?? 0);

        if (empty($medicamento_id) || empty($nombre) || empty($principio)) {
            return json_encode(["error" => "Datos del medicamento incompletos."]);
        }

        $actualizacion = ajusteInventarioCompletoModel::actualizar_medicamento_modelo(
            $medicamento_id, $nombre, $principio, $codigo, $proveedor_id, $ff_id, $uf_id,
            0, 0, 0, $via_administracion
        );

        if ($actualizacion && $actualizacion->rowCount() > 0) {
            return json_encode(["success" => "Medicamento actualizado exitosamente."]);
        } else {
            // Puede que no haya cambios, pero igual retornamos éxito si la consulta no falló
            return json_encode(["success" => "Datos del medicamento procesados."]);
        }
    }

    /**
     * Controlador para actualizar los datos de un lote.
     */
    public function actualizar_lote_controlador() {
        $lote_id = mainModel::limpiar_cadena($_POST['lote_id'] ?? 0);
        $numero_lote = mainModel::limpiar_cadena($_POST['numero_lote'] ?? '');
        $cant_caja = mainModel::limpiar_cadena($_POST['cant_caja'] ?? 0);
        $cant_blister = mainModel::limpiar_cadena($_POST['cant_blister'] ?? 0);
        $cant_unidad = mainModel::limpiar_cadena($_POST['cant_unidad'] ?? 0);
        $cant_actual_cajas = mainModel::limpiar_cadena($_POST['cant_actual_cajas'] ?? 0);
        $cant_actual_unidades = mainModel::limpiar_cadena($_POST['cant_actual_unidades'] ?? 0);
        $precio_compra = mainModel::limpiar_cadena($_POST['precio_compra'] ?? 0);
        $precio_venta = mainModel::limpiar_cadena($_POST['precio_venta'] ?? 0);
        $fecha_vencimiento = mainModel::limpiar_cadena($_POST['fecha_vencimiento'] ?? '');

        if (empty($lote_id) || empty($numero_lote) || empty($fecha_vencimiento)) {
            return json_encode(["error" => "Datos del lote incompletos."]);
        }

        // Obtener datos del lote antes de actualizar para saber qué medicamento y sucursal recalcular
        $lote_previo = ajusteInventarioCompletoModel::obtener_lote_modelo($lote_id)->fetch(PDO::FETCH_ASSOC);
        if (!$lote_previo) {
            return json_encode(["error" => "Lote no encontrado."]);
        }

        $actualizacion = ajusteInventarioCompletoModel::actualizar_lote_modelo(
            $lote_id, $numero_lote, $cant_caja, $cant_blister, $cant_unidad, $cant_actual_cajas, $cant_actual_unidades, $precio_compra, $precio_venta, $fecha_vencimiento
        );

        // Recalcular inventario siempre para asegurar sincronización
        ajusteInventarioCompletoModel::recalcular_inventario($lote_previo['med_id'], $lote_previo['su_id']);

        return json_encode(["success" => "Lote actualizado e inventario recalculado exitosamente."]);
    }

    /**
     * Controlador para eliminar un lote.
     */
    public function eliminar_lote_controlador() {
        $lote_id = mainModel::limpiar_cadena($_POST['lote_id'] ?? 0);

        if (empty($lote_id)) {
            return json_encode(["error" => "ID de lote no válido."]);
        }

        $lote = ajusteInventarioCompletoModel::obtener_lote_modelo($lote_id)->fetch(PDO::FETCH_ASSOC);
        if (!$lote) {
            return json_encode(["error" => "El lote no existe."]);
        }

        $eliminacion = ajusteInventarioCompletoModel::eliminar_lote_modelo($lote_id);

        if ($eliminacion->rowCount() > 0) {
            // Recalcular inventario tras eliminar
            ajusteInventarioCompletoModel::recalcular_inventario($lote['med_id'], $lote['su_id']);
            return json_encode(["success" => "Lote eliminado e inventario actualizado exitosamente."]);
        } else {
            return json_encode(["error" => "No se pudo eliminar el lote."]);
        }
    }

    /**
     * Controlador para obtener listas de datos para los selects.
     */
    public function obtener_listas_controlador() {
        $proveedores = ajusteInventarioCompletoModel::obtener_proveedores_modelo()->fetchAll(PDO::FETCH_ASSOC);
        $formas = ajusteInventarioCompletoModel::obtener_formas_modelo()->fetchAll(PDO::FETCH_ASSOC);
        $usos = ajusteInventarioCompletoModel::obtener_usos_modelo()->fetchAll(PDO::FETCH_ASSOC);
        $vias = ajusteInventarioCompletoModel::obtener_vias_modelo()->fetchAll(PDO::FETCH_ASSOC);

        return json_encode([
            "proveedores" => $proveedores,
            "formas" => $formas,
            "usos" => $usos,
            "vias" => $vias
        ]);
    }
}
