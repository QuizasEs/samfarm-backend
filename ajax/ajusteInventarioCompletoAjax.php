<?php

$peticionAjax = true;
require_once "../config/APP.php";
header('Content-Type: application/json; charset=utf-8');

// Validar sesión y permisos (Admin y Gerente)
session_start(['name' => 'SMP']);
if (!isset($_SESSION['id_smp']) || !in_array($_SESSION['rol_smp'], [1, 2])) {
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Sesión Expirada o Permiso Denegado",
        "texto" => "Por favor, inicie sesión nuevamente.",
        "Tipo" => "error"
    ]);
    exit();
}

require_once "../controllers/ajusteInventarioCompletoController.php";
$ins_ajuste = new ajusteInventarioCompletoController();

if (isset($_POST['accion'])) {
    
    switch ($_POST['accion']) {
        case 'buscar_medicamentos':
            echo $ins_ajuste->buscar_medicamentos_controlador();
            break;
        
        case 'obtener_detalles':
            echo $ins_ajuste->obtener_detalles_controlador();
            break;
        
        case 'actualizar_medicamento':
            echo $ins_ajuste->actualizar_medicamento_controlador();
            break;
        
        case 'actualizar_lote':
            echo $ins_ajuste->actualizar_lote_controlador();
            break;
        
        case 'eliminar_lote':
            echo $ins_ajuste->eliminar_lote_controlador();
            break;
        
        case 'obtener_listas':
            echo $ins_ajuste->obtener_listas_controlador();
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