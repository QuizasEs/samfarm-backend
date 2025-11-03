<?php

if ($peticionAjax) {
    require_once "../models/compraModel.php";
} else {
    require_once "./models/compraModel.php";
}

class compraController extends compraModel
{


    /* funcion endpoint SPA para busqueda de medicamentos formularios */

    public function buscar_medicamento_controller(array $filtros)
    {

        /* prepareamos la consulta */
        $sql = "
            SELECT 
                m.med_id,
                m.med_nombre_quimico AS nombre,
                m.med_precio_unitario AS precio,
                la.la_nombre_comercial AS laboratorio,
                ff.ff_nombre AS forma,
                vd.vd_nombre AS via,
                uf.uf_nombre AS uso
            FROM medicamento AS m
            LEFT JOIN laboratorios AS la ON m.la_id = la.la_id
            LEFT JOIN forma_farmaceutica AS ff ON m.ff_id = ff.ff_id
            LEFT JOIN via_de_administracion AS vd ON m.vd_id = vd.vd_id
            LEFT JOIN uso_farmacologico AS uf ON m.uf_id = uf.uf_id
            WHERE 1 = 1 
        ";


        /* aplicamos filtros de manera dinamica solo si existe el filtro */
        /* filtramos por categorias */
        if (!empty($filtros['forma'])) {
            $sql .= " AND m.ff_id = " . intval($filtros['forma']);
        }
        if (!empty($filtros['via'])) {
            $sql .= " AND m.vd_id = " . intval($filtros['via']);
        }
        if (!empty($filtros['laboratorio'])) {
            $sql .= " AND m.la_id = " . intval($filtros['laboratorio']);
        }
        if (!empty($filtros['uso'])) {
            $sql .= " AND m.uf_id = " . intval($filtros['uso']);
        }
        /* filtramos por termino de busqueda */
        /* filtramos por termino de busqueda */
        if (!empty($filtros['termino'])) {
            $busqueda = "%" . $filtros['termino'] . "%";
            $sql .= "
                AND (
                    m.med_nombre_quimico LIKE '$busqueda' OR
                    m.med_principio_activo LIKE '$busqueda' OR
                    m.med_accion_farmacologica LIKE '$busqueda' OR
                    m.med_presentacion LIKE '$busqueda' OR
                    m.med_descripcion LIKE '$busqueda'  /* ← QUITAR LA COMA EXTRA */
                )
            ";
        }

        $sql .= " ORDER BY m.med_nombre_quimico ASC limit 100";

        $respuesta = mainModel::ejecutar_consulta_simple($sql);
        return $respuesta;
    }
}
