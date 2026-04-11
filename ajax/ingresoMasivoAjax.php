<?php

$peticionAjax = true;
require_once "../config/APP.php";
header('Content-Type: application/json; charset=utf-8');

// Validar sesión
session_start(['name' => 'SMP']);
if (!isset($_SESSION['id_smp']) || !in_array($_SESSION['rol_smp'], [1, 2])) {
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Sesión Expirada",
        "texto" => "Por favor, inicie sesión nuevamente.",
        "Tipo" => "error"
    ]);
    exit();
}

require_once "../controllers/ingresoMasivoController.php";
$ins_ingreso = new ingresoMasivoController();

if (isset($_POST['accion'])) {
    
    switch ($_POST['accion']) {
        case 'procesar_ingreso':
            echo $ins_ingreso->procesar_ingreso_masivo_controlador();
            break;
        
        case 'obtener_datos':
            $datos = $ins_ingreso->obtener_datos_iniciales_controlador();
            echo json_encode($datos);
            break;
        
        default:
            echo json_encode([
                "Alerta" => "simple",
                "Titulo" => "Acción no reconocida",
                "texto" => "La acción solicitada es inválida.",
                "Tipo" => "error"
            ]);
    }

} else {
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Petición Inválida",
        "texto" => "No se ha especificado una acción a realizar.",
        "Tipo" => "error"
    ]);
}
