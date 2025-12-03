<?php

require_once "mainModel.php";

class medicamentoModel extends mainModel
{


    /* -------------------------------registrar medicamentos----------------------------------- */
    protected static function agregar_medicamento_model($datos)
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
                la_id,
                su_id,
                med_descripcion
            ) VALUES (
                :Nombre,
                :Principio,
                :Accion,
                :Presentacion,
                :Uso,
                :Forma,
                :Via,
                :Laboratorio,
                :Sucursal,
                :Descripcion
            )
        ");
        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Principio", $datos['Principio']);
        $sql->bindParam(":Accion", $datos['Accion']);
        $sql->bindParam(":Presentacion", $datos['Presentacion']);
        $sql->bindParam(":Uso", $datos['Uso']);
        $sql->bindParam(":Forma", $datos['Forma']);
        $sql->bindParam(":Via", $datos['Via']);
        $sql->bindParam(":Laboratorio", $datos['Laboratorio']);
        $sql->bindParam(":Sucursal", $datos['Sucursal']);
        $sql->bindParam(":Descripcion", $datos['Descripcion']);

        $sql->execute();
        return $sql;
    }

    protected static function actualizar_medicamento_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
            UPDATE medicamento SET 
                med_nombre_quimico = :Nombre,
                med_principio_activo = :Principio,
                med_accion_farmacologica = :Accion,
                med_descripcion = :Descripcion,
                med_presentacion = :Presentacion,
                uf_id = :Uso,
                ff_id = :Forma,
                vd_id = :Via,
                la_id = :Laboratorio,
                su_id = :Sucursal
            WHERE med_id = :Id
        ");

        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Principio", $datos['Principio']);
        $sql->bindParam(":Accion", $datos['Accion']);
        $sql->bindParam(":Descripcion", $datos['Descripcion']);
        $sql->bindParam(":Presentacion", $datos['Presentacion']);
        $sql->bindParam(":Uso", $datos['Uso']);
        $sql->bindParam(":Forma", $datos['Forma']);
        $sql->bindParam(":Via", $datos['Via']);
        $sql->bindParam(":Laboratorio", $datos['Laboratorio']);
        $sql->bindParam(":Sucursal", $datos['Sucursal']);
        $sql->bindParam(":Id", $datos['Id']);

        return $sql->execute();
    }

    protected static function datos_medicamento_model($inicio, $registros, $filtros = [])
    {
        $sql = "
                SELECT 
                    m.med_id,
                    m.med_nombre_quimico,
                    m.med_principio_activo,
                    m.med_accion_farmacologica,
                    m.med_presentacion,
                    m.med_codigo_barras,
                    m.med_creado_en,
                    m.med_actualizado_en,
                    COALESCE(la.la_nombre_comercial, 'Sin laboratorio') AS laboratorio,
                    COALESCE(ff.ff_nombre, 'Sin forma') AS forma_farmaceutica,
                    COALESCE(vd.vd_nombre, 'Sin vía') AS via_administracion,
                    COALESCE(uf.uf_nombre, 'Sin uso') AS uso_farmacologico
                FROM medicamento m
                LEFT JOIN laboratorios la ON la.la_id = m.la_id
                LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
                LEFT JOIN via_de_administracion vd ON vd.vd_id = m.vd_id
                LEFT JOIN uso_farmacologico uf ON uf.uf_id = m.uf_id
                WHERE 1=1
            ";

        $params = [];

        // Búsqueda por texto
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                    m.med_nombre_quimico LIKE :busqueda OR
                    m.med_principio_activo LIKE :busqueda OR
                    m.med_accion_farmacologica LIKE :busqueda OR
                    m.med_presentacion LIKE :busqueda OR
                    m.med_codigo_barras LIKE :busqueda
                )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        // Filtro por laboratorio
        if (!empty($filtros['laboratorio'])) {
            $sql .= " AND m.la_id = :laboratorio";
            $params[':laboratorio'] = (int)$filtros['laboratorio'];
        }

        // Filtro por vía
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = :via";
            $params[':via'] = (int)$filtros['via'];
        }

        // Filtro por forma
        if (!empty($filtros['forma'])) {
            $sql .= " AND m.ff_id = :forma";
            $params[':forma'] = (int)$filtros['forma'];
        }

        // Filtro por uso
        if (!empty($filtros['uso'])) {
            $sql .= " AND m.uf_id = :uso";
            $params[':uso'] = (int)$filtros['uso'];
        }

        $sql .= " ORDER BY m.med_nombre_quimico ASC";
        $sql .= " LIMIT :inicio, :registros";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
        $stmt->bindParam(':registros', $registros, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    protected static function contar_medicamentos_model($filtros = [])
    {
        $sql = "
                SELECT COUNT(*) as total
                FROM medicamento m
                LEFT JOIN laboratorios la ON la.la_id = m.la_id
                LEFT JOIN forma_farmaceutica ff ON ff.ff_id = m.ff_id
                LEFT JOIN via_de_administracion vd ON vd.vd_id = m.vd_id
                LEFT JOIN uso_farmacologico uf ON uf.uf_id = m.uf_id
                WHERE 1=1
            ";

        $params = [];

        // Búsqueda por texto
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (
                    m.med_nombre_quimico LIKE :busqueda OR
                    m.med_principio_activo LIKE :busqueda OR
                    m.med_accion_farmacologica LIKE :busqueda OR
                    m.med_presentacion LIKE :busqueda OR
                    m.med_codigo_barras LIKE :busqueda
                )";
            $params[':busqueda'] = '%' . $filtros['busqueda'] . '%';
        }

        // Filtro por laboratorio
        if (!empty($filtros['laboratorio'])) {
            $sql .= " AND m.la_id = :laboratorio";
            $params[':laboratorio'] = (int)$filtros['laboratorio'];
        }

        // Filtro por vía
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = :via";
            $params[':via'] = (int)$filtros['via'];
        }

        // Filtro por forma
        if (!empty($filtros['forma'])) {
            $sql .= " AND m.ff_id = :forma";
            $params[':forma'] = (int)$filtros['forma'];
        }

        // Filtro por uso
        if (!empty($filtros['uso'])) {
            $sql .= " AND m.uf_id = :uso";
            $params[':uso'] = (int)$filtros['uso'];
        }

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'];
    }

    /* ------------------------------- datos extras de medicamentos----------------------------------- */




    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
    /* ------------------------------- medicamentos----------------------------------- */
}
