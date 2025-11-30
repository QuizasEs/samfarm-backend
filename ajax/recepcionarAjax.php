<?php
$peticionAjax = true;
require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['recepcionarAjax'])) {
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
            "error" => "No cuenta con los privilegios necesarios. Rol actual: " . $rol_usuario
        ]);
        exit();
    }

    $valor = $_POST['recepcionarAjax'];

    require_once "../controllers/recepcionarController.php";
    $ins_recepcionar = new recepcionarController();

    if ($valor === "listar") {
        echo $ins_recepcionar->listar_transferencias_pendientes_controller();
        exit();
    }

    if ($valor === "obtener_detalles") {
        echo $ins_recepcionar->obtener_detalles_transferencia_controller();
        exit();
    }

    if ($valor === "aceptar") {
        try {
            echo $ins_recepcionar->aceptar_transferencia_controller();
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

    if ($valor === "rechazar") {
        try {
            echo $ins_recepcionar->rechazar_transferencia_controller();
        } catch (Exception $e) {
            error_log("ERROR AJAX rechazar: " . $e->getMessage());
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
