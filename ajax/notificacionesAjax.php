<?php

session_start(['name' => 'SMP']);

$peticionAjax = true;

if (!isset($_SESSION['id_smp']) || !isset($_SESSION['rol_smp'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autenticado']);
    exit;
}

require_once "../controllers/notificacionesController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? null;

    switch ($accion) {
        case 'obtener':
            echo notificacionesController::obtener_notificaciones_controller();
            break;

        case 'marcar_leida':
            $id = $_POST['id'] ?? null;
            echo notificacionesController::marcar_como_leida_controller($id);
            break;

        case 'descartar':
            $id = $_POST['id'] ?? null;
            echo notificacionesController::descartar_notificacion_controller($id);
            break;

        default:
            echo json_encode(['error' => true, 'mensaje' => 'Acción no válida']);
            break;
    }
} else {
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido']);
}
