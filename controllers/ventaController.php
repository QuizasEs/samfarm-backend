<?php

if ($peticionAjax) {
    require_once "../models/ventaModel.php";
} else {
    require_once "./models/ventaModel.php";
}

class ventaController extends ventaModel
{

    public function buscar_medicamento_controller($termino, $filtros = [])
    {

        // Validar sesión y sucursal
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([
                "error" => true,
                "mensaje" => "No se ha asignado una sucursal"
            ], JSON_UNESCAPED_UNICODE);
        }

        $sucursal_id = (int)$_SESSION['sucursal_smp'];

        // Limpiar término de búsqueda usando mainModel
        $termino = mainModel::limpiar_cadena($termino);

        // Validar longitud mínima
        if (strlen($termino) < 1) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        // Limpiar y validar filtros
        $filtros_limpios = [];

        if (!empty($filtros['linea'])) {
            $filtros_limpios['linea'] = (int)mainModel::limpiar_cadena($filtros['linea']);
        }
        if (!empty($filtros['presentacion'])) {
            $filtros_limpios['presentacion'] = (int)mainModel::limpiar_cadena($filtros['presentacion']);
        }
        if (!empty($filtros['funcion'])) {
            $filtros_limpios['funcion'] = (int)mainModel::limpiar_cadena($filtros['funcion']);
        }
        if (!empty($filtros['via'])) {
            $filtros_limpios['via'] = (int)mainModel::limpiar_cadena($filtros['via']);
        }

        // Ejecutar búsqueda
        $rows = self::buscar_medicamento_model($termino, $sucursal_id, $filtros_limpios);
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Obtener productos más vendidos
     * @param int $limit - Cantidad de productos a retornar
     * @return string JSON con resultados
     */
    public function mas_vendidos_controller($limit = 5)
    {
        // Validar sesión y sucursal
        if (!isset($_SESSION['sucursal_smp'])) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }

        $sucursal_id = (int)$_SESSION['sucursal_smp'];

        // Limpiar y validar límite
        $limit = (int)mainModel::limpiar_cadena($limit);
        if ($limit <= 0 || $limit > 20) {
            $limit = 5;
        }

        // Ejecutar consulta
        $rows = self::top_ventas_model($sucursal_id, $limit);

        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE);
    }
}
