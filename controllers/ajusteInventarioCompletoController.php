<?php

if ($peticionAjax) {
    require_once "../models/ajusteInventarioCompletoModel.php";
} else {
    require_once "./models/ajusteInventarioCompletoModel.php";
}

class ajusteInventarioCompletoController extends ajusteInventarioCompletoModel
{
    /**
     * Sucursales en las que este usuario puede ajustar, ya filtradas por rol.
     */
    public function sucursales_permitidas_controlador()
    {
        return ajusteInventarioCompletoModel::sucursales_permitidas_model(
            (int)($_SESSION['rol_smp'] ?? 0),
            (int)($_SESSION['sucursal_smp'] ?? 0)
        );
    }

    /**
     * Controlador para buscar medicamentos.
     *
     * La sucursal NO se toma del POST: se deduce de la sesion con la misma regla
     * que usa el modulo de Transferencias, para que el usuario no pueda buscar ni
     * ajustar el inventario de una sucursal ajena cambiando un campo del formulario.
     */
    public function buscar_medicamentos_controlador()
    {
        $termino = mainModel::limpiar_cadena($_POST['termino'] ?? '');

        if ($termino === '') {
            return json_encode(["error" => "Escriba el nombre, el principio activo o el código del medicamento."]);
        }

        $sucursal_id = $this->sucursal_obligatoria();
        if ($sucursal_id <= 0) {
            return json_encode(["error" => "No tiene una sucursal asignada para ajustar el inventario."]);
        }

        $resultados = ajusteInventarioCompletoModel::buscar_medicamentos_modelo($termino, $sucursal_id);
        return json_encode($resultados->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * La sucursal sobre la que el usuario tiene permiso.
     *
     * El administrador puede en todas; el resto solo en la suya. Devuelve 0 si no
     * tiene ninguna valida.
     */
    private function sucursal_obligatoria()
    {
        $rol = (int)($_SESSION['rol_smp'] ?? 0);
        $sucursal_id = (int)($_SESSION['sucursal_smp'] ?? 0);

        if ($rol === 1) {
            return $sucursal_id > 0 ? $sucursal_id : 0;
        }

        $permitidas = ajusteInventarioCompletoModel::sucursales_permitidas_model($rol, $sucursal_id);

        return $permitidas ? (int)$permitidas[0]['su_id'] : 0;
    }

    /**
     * Controlador para obtener los detalles de un medicamento y sus lotes.
     */
    public function obtener_detalles_controlador() {
        $medicamento_id = mainModel::limpiar_cadena($_POST['medicamento_id'] ?? 0);

        if ($medicamento_id === '' || (int)$medicamento_id <= 0) {
            return json_encode(["error" => "Medicamento no válido."]);
        }

        $sucursal_id = $this->sucursal_obligatoria();

        $medicamento = ajusteInventarioCompletoModel::obtener_medicamento_completo_modelo($medicamento_id)->fetch(PDO::FETCH_ASSOC);

        if (!$medicamento) {
            return json_encode(["error" => "No se encontró el medicamento."]);
        }

        // Un medicamento que todavia no entro a ninguna sucursal no tiene stock que
        // ajustar, pero si sus datos generales que corregir: se devuelven sin lotes
        // en vez de cortar con un error.
        $lotes = $sucursal_id > 0
            ? ajusteInventarioCompletoModel::obtener_lotes_medicamento_modelo($medicamento_id, $sucursal_id)->fetchAll(PDO::FETCH_ASSOC)
            : [];

        return json_encode([
            "medicamento" => $medicamento,
            "lotes" => $lotes,
            "sucursal_id" => $sucursal_id > 0 ? $sucursal_id : null
        ]);
    }

    /**
     * Controlador para actualizar los datos del medicamento.
     *
     * Se actualiza el medicamento en TODAS las sucursales porque aqui no se elige
     * una: el nombre, el principio activo, el proveedor, la forma, el uso y la via
     * son datos del medicamento, no del stock de una sucursal. Editarlo afecta a
     * las listas de venta, compra, inventario y lotes de ese medicamento, asi que
     * conviene hacerlo con criterio.
     */
    public function actualizar_medicamento_controlador() {
        $medicamento_id = mainModel::limpiar_cadena($_POST['medicamento_id'] ?? 0);
        $nombre = mainModel::limpiar_cadena($_POST['nombre'] ?? '');
        $principio = mainModel::limpiar_cadena($_POST['principio'] ?? '');
        $codigo = mainModel::limpiar_cadena($_POST['codigo'] ?? '');
        $proveedor_id = (int)mainModel::limpiar_cadena($_POST['proveedor_id'] ?? 0);
        $ff_id = (int)mainModel::limpiar_cadena($_POST['ff_id'] ?? 0);
        $uf_id = (int)mainModel::limpiar_cadena($_POST['uf_id'] ?? 0);
        $via_administracion = (int)mainModel::limpiar_cadena($_POST['via_administracion'] ?? 0);

        if ($medicamento_id === '' || (int)$medicamento_id <= 0 || $nombre === '' || $principio === '') {
            return json_encode(["error" => "Datos del medicamento incompletos."]);
        }

        $actual = ajusteInventarioCompletoModel::obtener_medicamento_modelo($medicamento_id)->fetch(PDO::FETCH_ASSOC);
        if (!$actual) {
            return json_encode(["error" => "No se encontró el medicamento."]);
        }

        $db = mainModel::conectarTransaccional();

        try {
            $db->beginTransaction();

            $actualizacion = ajusteInventarioCompletoModel::actualizar_medicamento_modelo(
                $db, $medicamento_id, $nombre, $principio, $codigo, $proveedor_id, $ff_id, $uf_id, $via_administracion
            );

            if ($actualizacion->rowCount() <= 0) {
                throw new Exception("no habia cambios que aplicar");
            }

            $cambios = [];
            if ($actual['med_nombre_quimico'] !== $nombre) {
                $cambios[] = "nombre: {$actual['med_nombre_quimico']} -> {$nombre}";
            }
            if ($actual['med_principio_activo'] !== $principio) {
                $cambios[] = "principio activo: {$actual['med_principio_activo']} -> {$principio}";
            }
            if ($actual['med_codigo_barras'] !== $codigo) {
                $cambios[] = "codigo de barras: " . ($actual['med_codigo_barras'] ?: '(vacio)') . " -> " . ($codigo ?: '(vacio)');
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("AJUSTE INVENTARIO: fallo al actualizar el medicamento {$medicamento_id}: " . $e->getMessage());
            return json_encode(["error" => "No se pudo guardar el medicamento. No se modifico nada."]);
        }

        if ($cambios) {
            error_log("AJUSTE INVENTARIO: medicamento {$medicamento_id} (us_id "
                . ($_SESSION['id_smp'] ?? 0) . ") - " . implode('; ', $cambios));
        }

        return json_encode(["success" => "Medicamento actualizado exitosamente."]);
    }

    /**
     * Controlador para actualizar los datos de un lote.
     *
     * Todo el guardado va en una transaccion: antes se actualizaba el lote, se
     * recalculaba el inventario y se dejaba el movimiento, tres pasos sueltos. Si
     * el segundo fallaba, el lote ya estaba cambiado y el inventario seguia con la
     * cantidad anterior, sin forma de volver atras.
     */
    public function actualizar_lote_controlador() {
        $lote_id = mainModel::limpiar_cadena($_POST['lote_id'] ?? 0);
        $numero_lote = mainModel::limpiar_cadena($_POST['numero_lote'] ?? '');
        $cant_caja = (int)mainModel::limpiar_cadena($_POST['cant_caja'] ?? 0);
        $cant_unidad = (int)mainModel::limpiar_cadena($_POST['cant_unidad'] ?? 0);
        $cant_actual_cajas = (int)mainModel::limpiar_cadena($_POST['cant_actual_cajas'] ?? 0);
        $cant_actual_unidades = (int)mainModel::limpiar_cadena($_POST['cant_actual_unidades'] ?? 0);
        $fecha_vencimiento = mainModel::limpiar_cadena($_POST['fecha_vencimiento'] ?? '');

        // empty() da por vacio el "0", asi que un lote llamado "0" o una cantidad
        // en cero se rechazaban como dato incompleto. Se compara contra cadena vacia.
        if ($lote_id === '' || (int)$lote_id <= 0 || $numero_lote === '' || $fecha_vencimiento === '') {
            return json_encode(["error" => "Datos del lote incompletos."]);
        }

        if ($cant_actual_cajas < 0 || $cant_actual_unidades < 0) {
            return json_encode(["error" => "Las cantidades actuales no pueden ser negativas."]);
        }

        // Estado del lote antes de tocarlo: de aqui salen el medicamento, la
        // sucursal y la diferencia de unidades para el movimiento de inventario.
        $lote_previo = ajusteInventarioCompletoModel::obtener_lote_modelo($lote_id)->fetch(PDO::FETCH_ASSOC);
        if (!$lote_previo) {
            return json_encode(["error" => "Lote no encontrado."]);
        }

        if ($lote_previo['lm_estado'] !== 'activo') {
            return json_encode([
                "error" => "Este lote no está activo (estado actual: {$lote_previo['lm_estado']}). Solo se pueden ajustar los lotes activos."
            ]);
        }

        $diferencia_unidades = $cant_actual_unidades - (int)$lote_previo['lm_cant_actual_unidades'];

        $db = mainModel::conectarTransaccional();

        try {
            $db->beginTransaction();

            ajusteInventarioCompletoModel::actualizar_lote_modelo(
                $db, $lote_id, $numero_lote, $cant_caja, $cant_unidad,
                $cant_actual_cajas, $cant_actual_unidades, $fecha_vencimiento
            );

            // Inventario primero: si esta consulta falla, se revierte tambien el
            // UPDATE del lote.
            ajusteInventarioCompletoModel::recalcular_inventario(
                $db,
                $lote_previo['med_id'],
                $lote_previo['su_id']
            );

            $usuario_id = (int)($_SESSION['id_smp'] ?? 0);

            ajusteInventarioCompletoModel::registrar_movimiento_inventario(
                $db,
                $lote_id,
                $lote_previo['med_id'],
                $lote_previo['su_id'],
                $usuario_id,
                $diferencia_unidades,
                "Ajuste de inventario en el lote {$numero_lote}: "
                . "{$lote_previo['lm_cant_actual_unidades']} -> {$cant_actual_unidades} unidades"
            );

            ajusteInventarioCompletoModel::registrar_historial_lote(
                $db,
                $lote_id,
                $usuario_id,
                "Ajuste de inventario: unidades {$lote_previo['lm_cant_actual_unidades']} -> {$cant_actual_unidades}, "
                . "cajas {$lote_previo['lm_cant_actual_cajas']} -> {$cant_actual_cajas}, "
                . "vencimiento {$lote_previo['lm_fecha_vencimiento']} -> {$fecha_vencimiento}"
            );

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("AJUSTE INVENTARIO: fallo al actualizar el lote {$lote_id}: " . $e->getMessage());
            return json_encode(["error" => "No se pudo guardar el lote. No se modifico nada."]);
        }

        return json_encode(["success" => "Lote actualizado e inventario recalculado exitosamente."]);
    }

    /**
     * Da por terminado un lote (borrado logico).
     *
     * El lote pasa a lm_estado = 'terminado', que es un valor del enum. No se
     * borra la fila: un DELETE fisico se llevaba en cascada historial_lote,
     * balance_precios y detalle_transferencia del lote, y dejaba en NULL el lote
     * de las lineas de venta que salieron de el.
     *
     * El estado 'terminado' no es 'deshabilitado': deshabilitado es "no lo
     * usamos mas", terminado es "este lote se agoto". Para una merma por
     * caducidad o rotura esta el modulo de merma, que ademas pide el motivo.
     */
    public function terminar_lote_controlador() {
        $lote_id = mainModel::limpiar_cadena($_POST['lote_id'] ?? 0);

        if ($lote_id === '' || (int)$lote_id <= 0) {
            return json_encode(["error" => "ID de lote no válido."]);
        }

        $lote = ajusteInventarioCompletoModel::obtener_lote_modelo($lote_id)->fetch(PDO::FETCH_ASSOC);
        if (!$lote) {
            return json_encode(["error" => "El lote no existe."]);
        }

        if ($lote['lm_estado'] === 'terminado') {
            return json_encode(["error" => "El lote ya se encuentra terminado."]);
        }

        if ($lote['lm_estado'] !== 'activo') {
            return json_encode([
                "error" => "Solo se pueden terminar los lotes activos. Este lote está en estado: {$lote['lm_estado']}."
            ]);
        }

        $db = mainModel::conectarTransaccional();

        try {
            $db->beginTransaction();

            $cambio = ajusteInventarioCompletoModel::terminar_lote_modelo($db, $lote_id);

            if ($cambio->rowCount() <= 0) {
                throw new Exception("el lote no cambio de estado");
            }

            // Al quedar terminado sale del SUM de lotes activos, asi que el
            // inventario hay que recalcularlo igual que en el ajuste.
            ajusteInventarioCompletoModel::recalcular_inventario($db, $lote['med_id'], $lote['su_id']);

            $usuario_id = (int)($_SESSION['id_smp'] ?? 0);

            ajusteInventarioCompletoModel::registrar_movimiento_inventario(
                $db,
                $lote_id,
                $lote['med_id'],
                $lote['su_id'],
                $usuario_id,
                -(int)$lote['lm_cant_actual_unidades'],
                "Lote {$lote['lm_numero_lote']} terminado: se retiran del inventario "
                . "sus {$lote['lm_cant_actual_unidades']} unidades"
            );

            ajusteInventarioCompletoModel::registrar_historial_lote(
                $db,
                $lote_id,
                $usuario_id,
                "Lote terminado desde Ajuste de Inventario. Quedan {$lote['lm_cant_actual_cajas']} cajas "
                . "y {$lote['lm_cant_actual_unidades']} unidades sin vender.",
                'terminacion'
            );

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log("AJUSTE INVENTARIO: fallo al terminar el lote {$lote_id}: " . $e->getMessage());
            return json_encode(["error" => "No se pudo terminar el lote. No se modifico nada."]);
        }

        return json_encode(["success" => "Lote terminado e inventario actualizado exitosamente."]);
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
