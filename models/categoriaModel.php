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
        $sql = mainModel::conectar()->prepare("
            INSERT INTO uso_farmacologico (uf_nombre, uf_imagen, uf_estado)
            VALUES (:nombre, :imagen, :estado)
        ");

        $sql->bindParam(':nombre', $datos['nombre']);
        $sql->bindParam(':imagen', $datos['imagen']);
        $sql->bindParam(':estado', $datos['estado']);

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
        $sql = mainModel::conectar()->prepare("
            UPDATE uso_farmacologico 
            SET uf_nombre = :nombre,
                uf_imagen = :imagen,
                uf_estado = :estado,
                uf_actualizado_en = NOW()
            WHERE uf_id = :id
        ");

        $sql->bindParam(':nombre', $datos['nombre']);
        $sql->bindParam(':imagen', $datos['imagen']);
        $sql->bindParam(':estado', $datos['estado']);
        $sql->bindParam(':id', $datos['id']);

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
}
