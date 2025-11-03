<?php

require_once "mainModel.php";

class proveedorModel extends mainModel
{
    protected static function agregar_proveedor_model($datos)
    {

        $sql = mainModel::conectar()->prepare("
            INSERT INTO proveedores 
            (pr_nombres, pr_apellido_paterno, pr_apellido_materno, pr_telefono)
            VALUES
            (:Nombre,
            :ApellidoPaterno,
            :ApellidoMaterno,
            :Telefono
            )
        ");

        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":ApellidoPaterno", $datos['ApellidoPaterno']);
        $sql->bindParam(":ApellidoMaterno", $datos['ApellidoMaterno']);
        $sql->bindParam(":Telefono", $datos['Telefono']);

        /* ejecutamos el sql */
        $sql->execute();
        return $sql;
    }
    protected static function data_proveedor_model($id)
    {

        $sql = mainModel::conectar()->prepare("SELECT * FROM proveedores WHERE pr_id = :ID");
        $sql->bindParam(":ID", $id);
        $sql->execute();
        return $sql;
    }
    protected static function actualizar_proveedor_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
        UPDATE proveedores
        SET
            pr_nombres = :Nombre,
            pr_apellido_paterno = :ApellidoPaterno,
            pr_apellido_materno = :ApellidoMaterno,
            pr_telefono = :Telefono,
            pr_actualizado_en = :Fecha,
            pr_estado = :Estado
        WHERE pr_id = :ID
        ");

        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":ApellidoPaterno", $datos['ApellidoPaterno']);
        $sql->bindParam(":ApellidoMaterno", $datos['ApellidoMaterno']);
        $sql->bindParam(":Telefono", $datos['Telefono']);
        $sql->bindParam(":Fecha", $datos['Fecha']);
        $sql->bindParam(":Estado", $datos['Estado']);
        $sql->bindParam(":ID", $datos['ID']);

        return $sql->execute(); // devuelve true o false
    }

    protected static function agregar_laboratorio_model($datos)
    {
        try {
            $sql = mainModel::conectar()->prepare("
            INSERT INTO laboratorios 
            (
                la_nombre_comercial,
                la_logo,
                pr_id 
            )
            VALUES 
            (
                :Nombre,
                :Logo,
                :Proveedor
            )
        ");

            // Usar bindValue en lugar de bindParam para mayor seguridad
            $sql->bindValue(":Nombre", $datos["Nombre"], PDO::PARAM_STR);
            $sql->bindValue(":Logo", $datos["Logo"], PDO::PARAM_STR);
            $sql->bindValue(":Proveedor", $datos["Proveedor"] ?: null, PDO::PARAM_INT);

            $sql->execute();
            return $sql;
        } catch (PDOException $e) {
            // Log del error
            error_log("Error al insertar laboratorio: " . $e->getMessage());
            return false;
        }
    }
    /*  modelo para recuperar datos de laboratorios */
    protected static function data_laboratorio_model($id)
    {
        $sql = mainModel::conectar()->prepare("SELECT * FROM laboratorios WHERE la_id = :ID");

        $sql->bindParam(":ID", $id);
        $sql->execute();
        return $sql;
    }
    protected static function actualizar_laboratorio_model($datos)
    {
        $sql = mainModel::conectar()->prepare("
        UPDATE laboratorios
        SET
            la_nombre_comercial = :Nombre,
            la_logo = :Logo,
            la_actualizado_en = :Fecha,
            la_estado = :Estado
        WHERE
            la_id = :ID");
        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Logo", $datos['Logo']);
        $sql->bindParam(":Fecha", $datos['Fecha']);
        $sql->bindParam(":Estado", $datos['Estado']);
        $sql->bindParam(":ID", $datos['ID']);

        return $sql->execute();
    }
}
