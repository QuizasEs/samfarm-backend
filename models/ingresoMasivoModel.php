<?php

require_once "mainModel.php";

class ingresoMasivoModel extends mainModel
{
    /**
     * Obtener ID de medicamento por nombre
     */
    public static function obtener_medicamento_por_nombre_model($nombre)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT med_id FROM medicamento 
            WHERE med_nombre_quimico = :nombre 
            LIMIT 1
        ");
        $sql->bindParam(":nombre", $nombre);
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['med_id'] : null;
    }

    /**
     * Crear nuevo medicamento
     */
    public static function crear_medicamento_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO medicamento (
                med_nombre_quimico,
                med_principio_activo,
                med_accion_farmacologica,
                med_presentacion,
                uf_id,
                ff_id,
                vd_id,
                pr_id,
                med_descripcion,
                med_codigo_barras
            ) VALUES (
                :Nombre,
                :Principio,
                :Accion,
                :Presentacion,
                :Uso,
                :Forma,
                :Via,
                :Proveedor,
                :Descripcion,
                :CodigoBarras
            )
        ");

        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Principio", $datos['Principio']);
        $sql->bindParam(":Accion", $datos['Accion']);
        $sql->bindParam(":Presentacion", $datos['Presentacion']);
        $sql->bindParam(":Uso", $datos['Uso']);
        $sql->bindParam(":Forma", $datos['Forma']);
        $sql->bindParam(":Via", $datos['Via']);
        $sql->bindParam(":Proveedor", $datos['Proveedor']);
        $sql->bindParam(":Descripcion", $datos['Descripcion']);
        $sql->bindParam(":CodigoBarras", $datos['CodigoBarras']);

        $sql->execute();
        return mainModel::conectar()->lastInsertId();
    }

    /**
     * Crear lote de medicamento
     */
    public static function crear_lote_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO lote_medicamento (
                med_id,
                su_id,
                pr_id,
                lm_numero_lote,
                lm_cant_caja,
                lm_cant_blister,
                lm_cant_unidad,
                lm_cant_actual_cajas,
                lm_cant_actual_unidades,
                lm_precio_compra,
                lm_precio_venta,
                lm_fecha_vencimiento,
                lm_estado,
                lm_fecha_ingreso
            ) VALUES (
                :med_id,
                :su_id,
                :pr_id,
                :lm_numero_lote,
                :lm_cant_caja,
                :lm_cant_blister,
                :lm_cant_unidad,
                :lm_cant_actual_cajas,
                :lm_cant_actual_unidades,
                :lm_precio_compra,
                :lm_precio_venta,
                :lm_fecha_vencimiento,
                :lm_estado,
                NOW()
            )
        ");

        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":pr_id", $datos['pr_id']);
        $sql->bindParam(":lm_numero_lote", $datos['lm_numero_lote']);
        $sql->bindParam(":lm_cant_caja", $datos['lm_cant_caja']);
        $sql->bindParam(":lm_cant_blister", $datos['lm_cant_blister']);
        $sql->bindParam(":lm_cant_unidad", $datos['lm_cant_unidad']);
        $sql->bindParam(":lm_cant_actual_cajas", $datos['lm_cant_actual_cajas']);
        $sql->bindParam(":lm_cant_actual_unidades", $datos['lm_cant_actual_unidades']);
        $sql->bindParam(":lm_precio_compra", $datos['lm_precio_compra']);
        $sql->bindParam(":lm_precio_venta", $datos['lm_precio_venta']);
        $sql->bindParam(":lm_fecha_vencimiento", $datos['lm_fecha_vencimiento']);
        $sql->bindParam(":lm_estado", $datos['lm_estado']);

        $sql->execute();
        return mainModel::conectar()->lastInsertId();
    }

    /**
     * Actualizar inventario (insertar o sumar stock)
     */
    public static function actualizar_inventario_model($datos)
    {
        // Primero verificar si existe el inventario
        $checkSql = mainModel::conectar()->prepare("
            SELECT inv_id FROM inventarios
            WHERE med_id = :med_id AND su_id = :su_id
            LIMIT 1
        ");
        $checkSql->bindParam(":med_id", $datos['med_id']);
        $checkSql->bindParam(":su_id", $datos['su_id']);
        $checkSql->execute();
        $existe = $checkSql->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // Actualizar inventario existente
            $sql = mainModel::conectar()->prepare("
                UPDATE inventarios SET
                    inv_total_cajas = inv_total_cajas + :inv_total_cajas,
                    inv_total_unidades = inv_total_unidades + :inv_total_unidades,
                    inv_total_valorado = inv_total_valorado + :inv_total_valorado,
                    inv_actualizado_en = NOW()
                WHERE med_id = :med_id AND su_id = :su_id
            ");
        } else {
            // Crear nuevo inventario
            $sql = mainModel::conectar()->prepare("
                INSERT INTO inventarios (
                    med_id,
                    su_id,
                    inv_total_cajas,
                    inv_total_unidades,
                    inv_total_valorado,
                    inv_creado_en,
                    inv_actualizado_en
                ) VALUES (
                    :med_id,
                    :su_id,
                    :inv_total_cajas,
                    :inv_total_unidades,
                    :inv_total_valorado,
                    NOW(),
                    NOW()
                )
            ");
        }

        $sql->bindParam(":med_id", $datos['med_id']);
        $sql->bindParam(":su_id", $datos['su_id']);
        $sql->bindParam(":inv_total_cajas", $datos['inv_total_cajas']);
        $sql->bindParam(":inv_total_unidades", $datos['inv_total_unidades']);
        $sql->bindParam(":inv_total_valorado", $datos['inv_total_valorado']);

        return $sql->execute();
    }

    /**
     * Registrar movimiento de inventario
     */
    public static function registrar_movimiento_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO movimiento_inventario (
                lm_id,
                med_id,
                su_id,
                us_id,
                mi_tipo,
                mi_cantidad,
                mi_unidad,
                mi_referencia_tipo,
                mi_referencia_id,
                mi_motivo
            ) VALUES (
                :lm_id,
                :med_id,
                :su_id,
                :us_id,
                :mi_tipo,
                :mi_cantidad,
                :mi_unidad,
                :mi_referencia_tipo,
                :mi_referencia_id,
                :mi_motivo
            )
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

        return $sql->execute();
    }

    /**
     * Obtener lista de sucursales
     */
    public static function obtener_sucursales_model()
    {
        $sql = mainModel::conectar()->prepare("
            SELECT su_id, su_nombre FROM sucursales 
            WHERE su_estado = 1 
            ORDER BY su_nombre ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener lista de proveedores
     */
    public static function obtener_proveedores_model()
    {
        $sql = mainModel::conectar()->prepare("
            SELECT pr_id, pr_razon_social FROM proveedores 
            WHERE pr_estado = 1 
            ORDER BY pr_razon_social ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si existe proveedor por ID
     */
    public static function verificar_proveedor_model($pr_id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT pr_id FROM proveedores WHERE pr_id = :pr_id LIMIT 1
        ");
        $sql->bindParam(":pr_id", $pr_id);
        $sql->execute();
        return $sql->rowCount() > 0;
    }

    /**
     * Limpiar cadena de texto para evitar caracteres especiales y problemas de codificación
     */
    public static function limpiar_cadena_especial($cadena)
    {
        // Eliminar espacios al inicio y final
        $cadena = trim($cadena);

        // Eliminar barras invertidas
        $cadena = stripslashes($cadena);

        // Reemplazar caracteres de control problemáticos
        $cadena = str_replace(["\r\n", "\r", "\n", "\t"], " ", $cadena);

        // Eliminar caracteres NULL y otros caracteres de control
        $cadena = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cadena);

        // Normalizar múltiples espacios a uno solo
        $cadena = preg_replace('/\s+/', ' ', $cadena);

        // Convertir a UTF-8 si no lo está
        if (!mb_check_encoding($cadena, 'UTF-8')) {
            $cadena = mb_convert_encoding($cadena, 'UTF-8', 'ISO-8859-1');
        }

        // Eliminar acentos problemáticos (opcional - mantener para español)
        // $cadena = str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'], '', $cadena);

        // Eliminar espacios al inicio y final después de todas las limpiezas
        $cadena = trim($cadena);

        return $cadena;
    }

    /**
     * Buscar lotes por criterios específicos
     */
    public static function buscar_lotes_por_criterios_model($condiciones)
    {
        // Construir la consulta dinámicamente basada en las condiciones
        $whereClauses = [];
        $params = [];

        foreach ($condiciones as $key => $value) {
            if ($value !== null) {
                $whereClauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($whereClauses)) {
            return [];
        }

        $whereClause = implode(" AND ", $whereClauses);

        $sql = mainModel::conectar()->prepare("
            SELECT lm_id FROM lote_medicamento 
            WHERE $whereClause
            LIMIT 100
        ");

        foreach ($params as $param => $value) {
            $sql->bindParam($param, $value);
        }

        $sql->execute();
        $resultados = $sql->fetchAll(PDO::FETCH_COLUMN, 0);

        return $resultados;
    }

    /**
     * Actualizar lote con los datos proporcionados
     * Wrapper para reutilizar el método de loteModel
     */
    public static function actualizar_lote_model($lm_id, $datos)
    {
        // Asegurarnos de que el ID esté en los datos
        $datos['ID'] = $lm_id;
        return loteModel::actualizar_lote_model($datos);
    }
}
