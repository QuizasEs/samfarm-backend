<?php
$peticionAjax = true;
require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['transferirAjax'])) {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();
        echo json_encode([
            "error" => "Sesion expirada"
        ]);
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario != 1 && $rol_usuario != 2) {
        echo json_encode([
            "error" => "No cuenta con los privilegios necesarios. Solo Admin y Gerente pueden transferir. Rol actual: " . $rol_usuario
        ]);
        exit();
    }
    
    if (!isset($_SESSION['sucursal_smp']) || empty($_SESSION['sucursal_smp'])) {
        echo json_encode([
            "error" => "Sucursal no asignada en la sesión"
        ]);
        exit();
    }

    $valor = $_POST['transferirAjax'];

    require_once "../controllers/transferirController.php";
    $ins_transfer = new transferirController();

    if ($valor === "buscar_lotes") {
        echo $ins_transfer->buscar_lotes_disponibles_controller();
        exit();
    }

    if ($valor === "generar") {
        try {
            echo $ins_transfer->generar_transferencia_controller();
        } catch (Exception $e) {
            error_log("ERROR AJAX generar: " . $e->getMessage());
            echo json_encode([
                'error' => 'Error en AJAX: ' . $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => basename($e->getFile())
            ]);
        }
        exit();
    }

    if ($valor === "aceptar") {
        try {
            echo $ins_transfer->aceptar_transferencia_controller();
        } catch (Exception $e) {
            error_log("ERROR AJAX aceptar: " . $e->getMessage());
            echo json_encode([
                'error' => 'Error en AJAX: ' . $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => basename($e->getFile())
            ]);
        }
        exit();
    }

} else {
    session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();
    echo json_encode([
        "error" => "Peticion no autorizada"
    ]);
    exit();
}