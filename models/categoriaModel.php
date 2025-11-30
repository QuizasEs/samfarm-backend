<?php

require_once "mainModel.php";

class categoriaModel extends mainModel
{
    protected static function listar_uso_farmacologico_model($inicio, $registros, $busqueda = '')
    {
        $sql = "SELECT 
                    uf_id,
                    uf_nombre,
                    uf_imagen,
                    uf_creado_en,
                    uf_actualizado_en,
                    uf_estado
                FROM uso_farmacologico
                WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND uf_nombre LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY uf_nombre ASC LIMIT :inicio, :registros";

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

    protected static function contar_uso_farmacologico_model($busqueda = '')
    {
        $sql = "SELECT COUNT(*) as total FROM uso_farmacologico WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND uf_nombre LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
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

    protected static function agregar_uso_farmacologico_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
                INSERT INTO uso_farmacologico (uf_nombre, uf_imagen, uf_estado)
                VALUES (:nombre, :imagen, :estado)
            ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function obtener_uso_farmacologico_model($id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * FROM uso_farmacologico WHERE uf_id = :id LIMIT 1
        ");

        $sql->bindParam(':id', $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    protected static function actualizar_uso_farmacologico_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
                UPDATE uso_farmacologico 
                SET uf_nombre = :nombre,
                    uf_imagen = :imagen,
                    uf_estado = :estado,
                    uf_actualizado_en = NOW()
                WHERE uf_id = :id
            ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);
        $sql->bindParam(':id', $datos['id'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function cambiar_estado_uso_farmacologico_model($id, $estado)
    {
        $sql = mainModel::conectar()->prepare("
            UPDATE uso_farmacologico 
            SET uf_estado = :estado,
                uf_actualizado_en = NOW()
            WHERE uf_id = :id
        ");

        $sql->bindParam(':estado', $estado, PDO::PARAM_INT);
        $sql->bindParam(':id', $id, PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function verificar_nombre_uso_existe_model($nombre, $id = null)
    {
        $sql = "SELECT COUNT(*) as total FROM uso_farmacologico WHERE uf_nombre = :nombre";

        if ($id) {
            $sql .= " AND uf_id != :id";
        }

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);

        if ($id) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'] > 0;
    }

    /* via de administracion */
    protected static function listar_via_administracion_model($inicio, $registros, $busqueda = '')
    {
        $sql = "SELECT 
                    vd_id,
                    vd_nombre,
                    vd_imagen,
                    vd_creado_en,
                    vd_actualizado_en,
                    vd_estado
                FROM via_de_administracion
                WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND vd_nombre LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY vd_nombre ASC LIMIT :inicio, :registros";

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

    protected static function contar_via_administracion_model($busqueda = '')
    {
        $sql = "SELECT COUNT(*) as total FROM via_de_administracion WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND vd_nombre LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
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

    protected static function agregar_via_administracion_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
            INSERT INTO via_de_administracion (vd_nombre, vd_imagen, vd_estado)
            VALUES (:nombre, :imagen, :estado)
        ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function obtener_via_administracion_model($id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * FROM via_de_administracion WHERE vd_id = :id LIMIT 1
        ");

        $sql->bindParam(':id', $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    protected static function actualizar_via_administracion_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
            UPDATE via_de_administracion 
            SET vd_nombre = :nombre,
                vd_imagen = :imagen,
                vd_estado = :estado,
                vd_actualizado_en = NOW()
            WHERE vd_id = :id
        ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);
        $sql->bindParam(':id', $datos['id'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function cambiar_estado_via_administracion_model($id, $estado)
    {
        $sql = mainModel::conectar()->prepare("
            UPDATE via_de_administracion 
            SET vd_estado = :estado,
                vd_actualizado_en = NOW()
            WHERE vd_id = :id
        ");

        $sql->bindParam(':estado', $estado, PDO::PARAM_INT);
        $sql->bindParam(':id', $id, PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function verificar_nombre_via_existe_model($nombre, $id = null)
    {
        $sql = "SELECT COUNT(*) as total FROM via_de_administracion WHERE vd_nombre = :nombre";

        if ($id) {
            $sql .= " AND vd_id != :id";
        }

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);

        if ($id) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'] > 0;
    }

    protected static function listar_forma_farmaceutica_model($inicio, $registros, $busqueda = '')
    {
        $sql = "SELECT 
                    ff_id,
                    ff_nombre,
                    ff_imagen,
                    ff_creado_en,
                    ff_actualizado_en,
                    ff_estado
                FROM forma_farmaceutica
                WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND ff_nombre LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY ff_nombre ASC LIMIT :inicio, :registros";

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

    protected static function contar_forma_farmaceutica_model($busqueda = '')
    {
        $sql = "SELECT COUNT(*) as total FROM forma_farmaceutica WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND ff_nombre LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
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

    protected static function agregar_forma_farmaceutica_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
                INSERT INTO forma_farmaceutica (ff_nombre, ff_imagen, ff_estado)
                VALUES (:nombre, :imagen, :estado)
            ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function obtener_forma_farmaceutica_model($id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * FROM forma_farmaceutica WHERE ff_id = :id LIMIT 1
        ");

        $sql->bindParam(':id', $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    protected static function actualizar_forma_farmaceutica_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
                UPDATE forma_farmaceutica 
                SET ff_nombre = :nombre,
                    ff_imagen = :imagen,
                    ff_estado = :estado,
                    ff_actualizado_en = NOW()
                WHERE ff_id = :id
            ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);
        $sql->bindParam(':id', $datos['id'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function cambiar_estado_forma_farmaceutica_model($id, $estado)
    {
        $sql = mainModel::conectar()->prepare("
            UPDATE forma_farmaceutica 
            SET ff_estado = :estado,
                ff_actualizado_en = NOW()
            WHERE ff_id = :id
        ");

        $sql->bindParam(':estado', $estado, PDO::PARAM_INT);
        $sql->bindParam(':id', $id, PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function verificar_nombre_forma_farmaceutica_existe_model($nombre, $id = null)
    {
        $sql = "SELECT COUNT(*) as total FROM forma_farmaceutica WHERE ff_nombre = :nombre";

        if ($id) {
            $sql .= " AND ff_id != :id";
        }

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);

        if ($id) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'] > 0;
    }

    protected static function listar_laboratorios_model($inicio, $registros, $busqueda = '')
    {
        $sql = "SELECT 
                    la_id,
                    la_nombre_comercial,
                    la_logo,
                    la_creado_en,
                    la_actualizado_en,
                    la_estado
                FROM laboratorios
                WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND la_nombre_comercial LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
        }

        $sql .= " ORDER BY la_nombre_comercial ASC LIMIT :inicio, :registros";

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

    protected static function contar_laboratorios_model($busqueda = '')
    {
        $sql = "SELECT COUNT(*) as total FROM laboratorios WHERE 1=1";

        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND la_nombre_comercial LIKE :busqueda";
            $params[':busqueda'] = '%' . $busqueda . '%';
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

    protected static function agregar_laboratorios_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
                INSERT INTO laboratorios (la_nombre_comercial, la_logo, la_estado)
                VALUES (:nombre, :imagen, :estado)
            ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function obtener_laboratorios_model($id)
    {
        $sql = mainModel::conectar()->prepare("
            SELECT * FROM laboratorios WHERE la_id = :id LIMIT 1
        ");

        $sql->bindParam(':id', $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    protected static function actualizar_laboratorios_model($datos)
    {
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
                UPDATE laboratorios 
                SET la_nombre_comercial = :nombre,
                    la_logo = :imagen,
                    la_estado = :estado,
                    la_actualizado_en = NOW()
                WHERE la_id = :id
            ");

        $sql->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
        $sql->bindParam(':imagen', $datos['imagen'], PDO::PARAM_STR);
        $sql->bindParam(':estado', $datos['estado'], PDO::PARAM_INT);
        $sql->bindParam(':id', $datos['id'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function cambiar_estado_laboratorios_model($id, $estado)
    {
        $sql = mainModel::conectar()->prepare("
            UPDATE laboratorios 
            SET la_estado = :estado,
                la_actualizado_en = NOW()
            WHERE la_id = :id
        ");

        $sql->bindParam(':estado', $estado, PDO::PARAM_INT);
        $sql->bindParam(':id', $id, PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }

    protected static function verificar_nombre_laboratorios_existe_model($nombre, $id = null)
    {
        $sql = "SELECT COUNT(*) as total FROM laboratorios WHERE la_nombre_comercial = :nombre";

        if ($id) {
            $sql .= " AND la_id != :id";
        }

        $stmt = mainModel::conectar()->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);

        if ($id) {
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        }

        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$resultado['total'] > 0;
    }
}
