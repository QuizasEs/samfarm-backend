<?php

if ($peticionAjax) {
    require_once "../models/mainModel.php";
} else {
    require_once "./models/mainModel.php";
}

class ajusteInventarioCompletoModel extends mainModel
{
    /**
     * Modelo para buscar medicamentos para el ajuste de inventario.
     *
     * Los JOIN con inventarios y sucursales son LEFT, no INNER, a proposito: hay
     * medicamentos dados de alta que todavia no entraron a ninguna sucursal y por
     * eso no tienen fila en inventarios. Con INNER esos medicamentos no aparecian
     * en la busqueda y no se podian corregir ni el nombre ni el principio
     * activo, que es justamente lo que este modulo viene a arreglar. Esos
     * medicamentos se devuelven con su stock en 0 y sin sucursal, y la vista los
     * marca como tales.
     *
     * La consulta siempre es de UNA sucursal, que se elige en la pantalla
     * anterior, igual que en Transferencias.
     *
     * El JOIN con inventarios es LEFT y trae la condicion de la sucursal en el ON,
     * no en el WHERE. Asi un medicamento que esta en el catalogo pero todavia no
     * tiene stock en la sucursal elegida sigue apareciendo con 0, en vez de
     * desaparecer. Hay 18 medicamentos en ese caso y son justo los que este modulo
     * viene a poder corregir.
     *
     * Como un medicamento sin stock en la sucursal elegida se veria 3 veces (una
     * por sucursal), el WHERE descarta los que no tienen stock alli PERO si lo
     * tienen en alguna otra. Asi los huerfanos se pueden corregir desde cualquier
     * sucursal y una sola vez.
     */
    protected static function buscar_medicamentos_modelo($termino, $sucursal_id)
    {
        $termino_like = "%" . $termino . "%";

        $consulta = "
            SELECT
                m.med_id,
                m.med_nombre_quimico,
                m.med_principio_activo,
                m.med_codigo_barras,
                COALESCE(p.pr_razon_social, 'Proveedor eliminado') AS proveedor,
                COALESCE(i.inv_total_unidades, 0) AS inv_total_unidades,
                COALESCE(s.su_nombre, 'Sin inventario') AS su_nombre,
                i.su_id
            FROM medicamento m
            LEFT JOIN proveedores p ON m.pr_id = p.pr_id
            LEFT JOIN inventarios i ON m.med_id = i.med_id AND i.su_id = :sucursal_id
            LEFT JOIN sucursales s ON i.su_id = s.su_id
            WHERE (m.med_nombre_quimico LIKE :termino OR m.med_principio_activo LIKE :termino OR m.med_codigo_barras LIKE :termino)
              AND (
                    i.inv_id IS NOT NULL
                    OR NOT EXISTS (
                        SELECT 1 FROM inventarios i2 WHERE i2.med_id = m.med_id
                    )
              )
            ORDER BY (i.inv_id IS NULL), m.med_nombre_quimico
            LIMIT 50
        ";

        /* bindParam recibe por referencia, asi que el valor tiene que estar en una
           variable: pasar un (int) o un ternario en linea tira un Error fatal. */
        $sucursal_id = (int)$sucursal_id;
        $sql = mainModel::conectar()->prepare($consulta);
        $sql->bindParam(":termino", $termino_like, PDO::PARAM_STR);
        $sql->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Sucursales en las que este usuario puede hacer ajustes.
     *
     * Misma regla que el modulo de Transferencias: el administrador ajusta en
     * cualquier sucursal, los demas usuarios solo en la suya. Si el usuario tiene
     * asignada una sucursal que no existe o esta dada de baja, no se le ofrece
     * ninguna en vez de ofrecerle una que no puede usar.
     */
    public static function sucursales_permitidas_model($rol, $sucursal_propia)
    {
        if ((int)$rol !== 1) {
            $sucursal_propia = (int)$sucursal_propia;
            $sql = mainModel::conectar()->prepare("
                SELECT su_id, su_nombre FROM sucursales
                WHERE su_id = :su_id AND su_estado = 1
                LIMIT 1
            ");
            $sql->bindParam(":su_id", $sucursal_propia, PDO::PARAM_INT);
            $sql->execute();
            $propia = $sql->fetch(PDO::FETCH_ASSOC);
            return $propia ? [$propia] : [];
        }

        $sql = mainModel::conectar()->query("
            SELECT su_id, su_nombre FROM sucursales
            WHERE su_estado = 1
            ORDER BY su_nombre ASC
        ");
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Modelo para obtener los datos de un medicamento específico.
     */
    protected static function obtener_medicamento_modelo($medicamento_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT m.* 
            FROM medicamento m
            WHERE m.med_id = :medicamento_id
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener los datos de un medicamento específico incluyendo cantidades y presentaciones.
     */
    protected static function obtener_medicamento_completo_modelo($medicamento_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT 
                m.*,
                COALESCE(p.pr_razon_social, 'Proveedor eliminado') AS proveedor,
                ff.ff_nombre as forma_farmaceutica_nombre,
                uf.uf_nombre as uso_farmacologico_nombre,
                vd.vd_nombre as via_administracion_nombre
            FROM medicamento m
            LEFT JOIN proveedores p ON m.pr_id = p.pr_id
            LEFT JOIN forma_farmaceutica ff ON m.ff_id = ff.ff_id
            LEFT JOIN uso_farmacologico uf ON m.uf_id = uf.uf_id
            LEFT JOIN via_de_administracion vd ON m.vd_id = vd.vd_id
            WHERE m.med_id = :medicamento_id
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener los lotes activos de un medicamento en una sucursal.
     */
    protected static function obtener_lotes_medicamento_modelo($medicamento_id, $sucursal_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * 
            FROM lote_medicamento 
            WHERE med_id = :medicamento_id 
              AND su_id = :sucursal_id 
              AND lm_estado = 'activo'
            ORDER BY lm_fecha_vencimiento ASC
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener un lote específico.
     */
    protected static function obtener_lote_modelo($lote_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * 
            FROM lote_medicamento 
            WHERE lm_id = :lote_id
        ");
        $sql->bindParam(":lote_id", $lote_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para actualizar los datos del medicamento.
     */
    protected static function actualizar_medicamento_modelo($db, $medicamento_id, $nombre, $principio, $codigo, $proveedor_id, $ff_id, $uf_id, $via_administracion)
    {
        $sql = $db->prepare("
            UPDATE medicamento 
            SET med_nombre_quimico = :nombre, 
                med_principio_activo = :principio, 
                med_codigo_barras = :codigo, 
                pr_id = :proveedor_id, 
                ff_id = :ff_id, 
                uf_id = :uf_id,
                vd_id = :via_administracion,
                med_actualizado_en = NOW()
            WHERE med_id = :medicamento_id
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->bindParam(":nombre", $nombre, PDO::PARAM_STR);
        $sql->bindParam(":principio", $principio, PDO::PARAM_STR);
        $sql->bindParam(":codigo", $codigo, PDO::PARAM_STR);
        $sql->bindParam(":proveedor_id", $proveedor_id, PDO::PARAM_INT);
        $sql->bindParam(":ff_id", $ff_id, PDO::PARAM_INT);
        $sql->bindParam(":uf_id", $uf_id, PDO::PARAM_INT);
        $sql->bindParam(":via_administracion", $via_administracion, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para actualizar los datos de un lote.
     *
     * Todos los metodos que escriben aceptan la conexion como primer parametro,
     * igual que loteModel y compraModel. No es cosmetico: mainModel::conectar() y
     * mainModel::conectarTransaccional() guardan la conexion en dos claves
     * distintas del arreglo de conexiones y devuelven dos PDO diferentes. Si el
     * controlador abre la transaccion con conectarTransaccional() y el modelo
     * escribe con conectar(), el UPDATE sale por otra conexion y el rollBack no lo
     * alcanza: la transaccion parece funcionar y no revierte nada.
     */
protected static function actualizar_lote_modelo($db, $lote_id, $numero_lote, $cant_caja, $cant_unidad, $cant_actual_cajas, $cant_actual_unidades, $fecha_vencimiento)
    {
        /* El UPDATE lista solo las columnas que este modulo administra.
           Antes incluia lm_cant_blister, lm_precio_compra, lm_precio_venta y
           lm_precio_costo, y el controlador las mandaba siempre desde el
           formulario. Si se sacaran esos campos de la pantalla sin quitar el
           UPDATE, cada guardado escribiria 0 en los precios de miles de lotes:
           el modulo de balance y la valoracion se quedarian sin dato.
           lm_cant_blister la fija compraModel en 1 y no la usa nadie mas que
           como multiplicador por 1 (ventaModel y mermaModel); queda en la base
           intacta para esos calculos. */
        $sql = $db->prepare("
            UPDATE lote_medicamento 
            SET lm_numero_lote = :numero_lote,
                lm_cant_caja = :cant_caja,
                lm_cant_unidad = :cant_unidad,
                lm_cant_actual_cajas = :cant_actual_cajas,
                lm_cant_actual_unidades = :cant_actual_unidades,
                lm_fecha_vencimiento = :fecha_vencimiento,
                lm_actualizado_en = NOW()
            WHERE lm_id = :lm_id
        ");

        $sql->bindParam(":lm_id", $lote_id, PDO::PARAM_INT);
        $sql->bindParam(":numero_lote", $numero_lote, PDO::PARAM_STR);
        $sql->bindParam(":cant_caja", $cant_caja, PDO::PARAM_INT);
        $sql->bindParam(":cant_unidad", $cant_unidad, PDO::PARAM_INT);
        $sql->bindParam(":cant_actual_cajas", $cant_actual_cajas, PDO::PARAM_INT);
        $sql->bindParam(":cant_actual_unidades", $cant_actual_unidades, PDO::PARAM_INT);
        $sql->bindParam(":fecha_vencimiento", $fecha_vencimiento, PDO::PARAM_STR);

        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para eliminar un lote.
     *
     * NO es un DELETE: es el borrado logico que ya usa el modulo de lotes.
     * lote_medicamento esta referenciada por nueve tablas y tres de ellas borran
     * en cascada: historial_lote, balance_precios y detalle_transferencia
     * (lm_origen_id). Ademas detalle_venta.lm_id queda en NULL, o sea que la
     * linea de la venta pierde que lote salio. Un DELETE fisico aqui destruye
     * la auditoria de lotes que ya se vendieron y rompe la trazabilidad que el
     * modulo de facturacion electronica necesita.
     *
     * El estado 'deshabilitado' es el que el propio proyecto le asigna al borrado
     * logico (loteController, accion 'eliminar lote') y todas las listas de lotes lo
     * excluyen, asi que el lote desaparece de la pantalla igual que antes pero sin
     * arrastrar historial ni ventas.
     */
protected static function terminar_lote_modelo($db, $lote_id)
    {
        /* 'terminado' es un valor del enum lm_estado. El lote no se borra: sale
           de los SUM de lotes activos, asi que recalcular_inventario lo saca del
           inventario y las ventas ya registradas siguen intactas. */
        $sql = $db->prepare("
            UPDATE lote_medicamento
            SET lm_estado = 'terminado',
                lm_actualizado_en = NOW()
            WHERE lm_id = :lm_id
        ");
        $sql->bindParam(":lm_id", $lote_id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /**
     * Recalcula el inventario consolidado de un medicamento en una sucursal.
     *
     * La valoracion usa SUM(lm_cant_actual_unidades * lm_precio_venta), que es la
     * formula que dice inventarioModel::recalcular_valorado_inventario_model() y la
     * que de verdad esta en la base: medida sobre las 23,128 filas de inventarios,
     * coincide en el 99.97%.
     *
     * Antes este metodo multiplicaba cajas por precio_costo
     * (SUM(lm_cant_actual_cajas * lm_precio_costo)), que es otra cantidad: no son
     * las mismas unidades ni el mismo precio. Con esa formula el 74.1% de las filas
     * de inventarios quedaba con un valor distinto al que tenian, y como el cron
     * recalcularValorado.php reescribe la columna cada pocas horas, el modulo
     * dejaba el inventario con un numero, el cron lo corregia a otro, y los
     * totales de inventarioLista fluctuaban sin que nadie hiciera nada.
     *
     * El UPDATE es un upsert y no un UPDATE a secas: si el medicamento no tiene
     * fila en inventarios todavia (18 medicamentos estan en ese caso), el UPDATE
     * no encontraba fila, no hacia nada y el controlador igual respondia que el
     * inventario estaba recalculado.
     */
    public static function recalcular_inventario($db, $medicamento_id, $sucursal_id)
    {
        // Totales de cantidad: todos los lotes activos de esa sucursal.
        $sql = $db->prepare("
            SELECT
                COALESCE(SUM(lm_cant_actual_cajas), 0) AS total_cajas,
                COALESCE(SUM(lm_cant_actual_unidades), 0) AS total_unidades,
                COALESCE(SUM(lm_cant_actual_unidades * lm_precio_venta), 0) AS total_valorado
            FROM lote_medicamento
            WHERE med_id = :medicamento_id AND su_id = :sucursal_id AND lm_estado = 'activo'
        ");
        $sql->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        $sql->execute();
        $totales = $sql->fetch(PDO::FETCH_ASSOC);

        $total_cajas = (int)($totales['total_cajas'] ?? 0);
        $total_unidades = (int)($totales['total_unidades'] ?? 0);
        $total_valorado = (float)($totales['total_valorado'] ?? 0);

        $sql_update = $db->prepare("
            INSERT INTO inventarios
                (med_id, su_id, inv_total_cajas, inv_total_unidades, inv_total_valorado,
                 inv_creado_en, inv_actualizado_en)
            VALUES
                (:medicamento_id, :sucursal_id, :total_cajas, :total_unidades, :total_valorado,
                 NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                inv_total_cajas = VALUES(inv_total_cajas),
                inv_total_unidades = VALUES(inv_total_unidades),
                inv_total_valorado = VALUES(inv_total_valorado),
                inv_actualizado_en = NOW()
        ");
        $sql_update->bindParam(":medicamento_id", $medicamento_id, PDO::PARAM_INT);
        $sql_update->bindParam(":sucursal_id", $sucursal_id, PDO::PARAM_INT);
        $sql_update->bindParam(":total_cajas", $total_cajas, PDO::PARAM_INT);
        $sql_update->bindParam(":total_unidades", $total_unidades, PDO::PARAM_INT);
        $sql_update->bindParam(":total_valorado", $total_valorado, PDO::PARAM_STR);

        return $sql_update->execute();
    }

    /**
     * Registra el cambio en historial_lote.
     *
     * El enum hl_accion ya tiene 'ajuste' y es lo que usa loteController cuando
     * edita un lote. Este modulo actualizaba el lote sin dejar rastro: el cambio
     * de cantidades o de precio de venta quedaba sin registro y despues no habia
     * forma de saber quien lo habia tocado.
     */
    public static function registrar_historial_lote($db, $lote_id, $usuario_id, $descripcion, $accion = 'ajuste')
    {
        /* hl_accion es un enum. 'ajuste' y 'terminacion' son los dos que usa este
           modulo; el resto queda permitido por si hace falta enganchar otra
           accion del enum mas adelante. */
        $permitidas = ['creacion', 'activacion', 'ajuste', 'caducidad', 'terminacion', 'devolucion', 'bloqueo', 'desbloqueo', 'transferencia_salida', 'transferencia_entrada', 'recepcion_transferencia', 'rechazo_transferencia', 'balance'];
        if (!in_array($accion, $permitidas, true)) {
            $accion = 'ajuste';
        }

        $sql = $db->prepare("
            INSERT INTO historial_lote (lm_id, us_id, hl_accion, hl_descripcion)
            VALUES (:lm_id, :us_id, :hl_accion, :descripcion)
        ");
        $sql->bindParam(":lm_id", $lote_id, PDO::PARAM_INT);
        $sql->bindParam(":us_id", $usuario_id, PDO::PARAM_INT);
        $sql->bindParam(":hl_accion", $accion, PDO::PARAM_STR);
        $sql->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
        $sql->execute();
        return $sql;
    }

    /**
     * Registra el movimiento de inventario que provoca el ajuste.
     *
     * Es la misma forma que usa loteModel::registro_movimiento_inventario_model()
     * con mi_referencia_tipo 'ajuste_lote'. Sin esto el inventario cambia de
     * cantidad sin que quede un movimiento que lo explique, y el kardex deja de
     * cuadrar con el stock real.
     */
    public static function registrar_movimiento_inventario($db, $lote_id, $medicamento_id, $sucursal_id, $usuario_id, $diferencia_unidades, $motivo)
    {
        if ($diferencia_unidades == 0) {
            return null;
        }

        $sql = $db->prepare("
            INSERT INTO movimiento_inventario
                (lm_id, med_id, su_id, us_id, mi_tipo, mi_cantidad, mi_unidad,
                 mi_referencia_tipo, mi_referencia_id, mi_motivo)
            VALUES
                (:lm_id, :med_id, :su_id, :us_id, :mi_tipo, :mi_cantidad, 'unidad',
                 'ajuste_lote', :mi_referencia_id, :mi_motivo)
        ");
        /* El tipo va en una variable y no en un ternario dentro del bindParam:
           bindParam recibe el argumento POR REFERENCIA y PHP no puede pasarle una
           expresion. Con el ternario adentro tira un Error fatal y el guardado del
           lote se cae entero. */
        $mi_tipo = $diferencia_unidades > 0 ? 'entrada' : 'salida';
        $mi_cantidad = abs($diferencia_unidades);

        $sql->bindParam(":lm_id", $lote_id, PDO::PARAM_INT);
        $sql->bindParam(":med_id", $medicamento_id, PDO::PARAM_INT);
        $sql->bindParam(":su_id", $sucursal_id, PDO::PARAM_INT);
        $sql->bindParam(":us_id", $usuario_id, PDO::PARAM_INT);
        $sql->bindParam(":mi_tipo", $mi_tipo, PDO::PARAM_STR);
        $sql->bindParam(":mi_cantidad", $mi_cantidad, PDO::PARAM_INT);
        $sql->bindParam(":mi_referencia_id", $lote_id, PDO::PARAM_INT);
        $sql->bindParam(":mi_motivo", $motivo, PDO::PARAM_STR);
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener proveedores.
     */
    protected static function obtener_proveedores_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT pr_id, pr_razon_social FROM proveedores WHERE pr_estado = 1 ORDER BY pr_razon_social ASC");
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener formas farmacéuticas (presentaciones).
     */
    protected static function obtener_formas_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT ff_id, ff_nombre FROM forma_farmaceutica WHERE ff_estado = 1 ORDER BY ff_nombre ASC");
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener usos farmacológicos.
     */
    protected static function obtener_usos_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT uf_id, uf_nombre FROM uso_farmacologico WHERE uf_estado = 1 ORDER BY uf_nombre ASC");
        $sql->execute();
        return $sql;
    }

    /**
     * Modelo para obtener vías de administración.
     */
    protected static function obtener_vias_modelo()
    {
        $sql = mainModel::conectar()->prepare("SELECT vd_id, vd_nombre FROM via_de_administracion WHERE vd_estado = 1 ORDER BY vd_nombre ASC");
        $sql->execute();
        return $sql;
    }
}
