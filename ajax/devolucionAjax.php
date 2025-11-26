<?php

$peticionAjax = true;

require_once "../config/APP.php";

ini_set('display_errors', 0);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Error interno",
        "texto" => "$errstr en $errfile:$errline",
        "Tipo" => "error"
    ]);
    exit();
});

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['devolucionAjax'])) {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || !in_array($_SESSION['rol_smp'], [1, 2, 3])) {
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

    $valor = $_POST['devolucionAjax'];

    require_once "../controllers/devolucionController.php";
    $ins_devolucion = new devolucionController();

    if ($valor === 'buscar_venta') {
        echo $ins_devolucion->buscar_venta_controller();
        exit();
    }

    if ($valor === 'procesar') {
        echo $ins_devolucion->procesar_devolucion_controller();
        exit();
    }

    if ($valor === 'lotes_cambio') {
        echo $ins_devolucion->obtener_lotes_cambio_controller();
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