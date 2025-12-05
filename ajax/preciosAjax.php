<?php

$peticionAjax = true;

require_once "../config/APP.php";

if (isset($_POST['preciosAjax'])) {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();

        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Sesión expirada",
            "texto" => "Por favor vuelva a iniciar sesión",
            "Tipo" => "error"
        ]);
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario != 1) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Acceso denegado",
            "texto" => "Solo administradores pueden ejecutar esta acción",
            "Tipo" => "error"
        ]);
        exit();
    }

    $valor = $_POST['preciosAjax'];

    require_once "../controllers/preciosController.php";
    $ins_precios = new preciosController();

    /**
     * OBTENER MEDICAMENTOS CON LOTES
     */
    if ($valor === "obtener_medicamentos") {
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';
        echo $ins_precios->obtener_medicamentos_precios_controller($busqueda);
        exit();
    }

    /**
     * LISTAR MEDICAMENTOS CON PAGINACIÓN (JSON)
     */
    if ($valor === "obtener_medicamentos_paginado") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        
        $busqueda = isset($_POST['busqueda']) ? mainModel::limpiar_cadena($_POST['busqueda']) : '';
        $su_id = isset($_POST['su_id']) && !empty($_POST['su_id']) ? (int)$_POST['su_id'] : null;

        $medicamentos = preciosModel::obtener_medicamentos_con_lotes_model($su_id, $busqueda);
        
        $total = count($medicamentos);
        $total_paginas = ceil($total / $registros);
        
        $inicio = ($pagina - 1) * $registros;
        $medicamentos_paginados = array_slice($medicamentos, $inicio, $registros);

        echo json_encode([
            'medicamentos' => $medicamentos_paginados,
            'total' => $total,
            'total_paginas' => $total_paginas,
            'pagina_actual' => $pagina
        ]);
        exit();
    }

    /**
     * OBTENER LOTES DE UN MEDICAMENTO
     */
    if ($valor === "obtener_lotes") {
        echo $ins_precios->obtener_lotes_precios_controller();
        exit();
    }

    /**
     * ACTUALIZAR PRECIO DE UN LOTE
     */
    if ($valor === "actualizar_lote") {
        echo $ins_precios->actualizar_precio_lote_controller();
        exit();
    }

    /**
     * ACTUALIZAR PRECIO DE TODOS LOS LOTES
     */
    if ($valor === "actualizar_todos") {
        echo $ins_precios->actualizar_precio_todos_lotes_controller();
        exit();
    }

    /**
     * LISTAR INFORMES
     */
    if ($valor === "listar_informes") {
        // Si viene desde tabla-dinamica, devolver HTML
        if (isset($_POST['pagina']) && isset($_POST['registros'])) {
            header('Content-Type: text/html; charset=utf-8');
            echo $ins_precios->listar_informes_html_controller();
        } else {
            $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
            $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;

            echo $ins_precios->paginado_informes_precios_controller(
                $pagina,
                $registros,
                'precio-informes-lista'
            );
        }
        exit();
    }

    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acción no válida",
        "texto" => "La acción solicitada no existe",
        "Tipo" => "error"
    ]);
    exit();

} else {
    session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();

    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acceso denegado",
        "texto" => "Petición no autorizada",
        "Tipo" => "error"
    ]);
    exit();
}
