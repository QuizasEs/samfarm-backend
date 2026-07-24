<?php

session_start(['name' => 'SMP']);

$peticionAjax = true;

if (!isset($_SESSION['id_smp']) || !isset($_SESSION['rol_smp'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'mensaje' => 'No autenticado']);
    exit;
}

session_write_close();

require_once "../controllers/notificacionesController.php";

header('Content-Type: application/json');

$MAX_RETRIES = 3;
$RETRY_DELAY_MICROSECONDS = 500000;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? null;
    $retryCount = 0;
    $shouldExit = false;

    do {
        try {
            switch ($accion) {
                case 'obtener':
                    echo notificacionesController::obtener_notificaciones_controller();
                    $shouldExit = true;
                    break;

                case 'marcar_leida':
                    $id = $_POST['id'] ?? null;
                    echo notificacionesController::marcar_como_leida_controller($id);
                    $shouldExit = true;
                    break;

                case 'descartar':
                    $id = $_POST['id'] ?? null;
                    echo notificacionesController::descartar_notificacion_controller($id);
                    $shouldExit = true;
                    break;

                default:
                    echo json_encode(['error' => true, 'mensaje' => 'Acción no válida']);
                    $shouldExit = true;
                    break;
            }
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            $isLockTimeout = stripos($errorMessage, 'Lock wait timeout exceeded') !== false || 
                            stripos($errorMessage, 'HY000') !== false;
            
            if ($isLockTimeout && $retryCount < $MAX_RETRIES) {
                $retryCount++;
                usleep($RETRY_DELAY_MICROSECONDS * $retryCount);
                continue;
            }
            
            http_response_code(500);
            echo json_encode(['error' => true, 'mensaje' => 'Error interno: ' . $errorMessage]);
            $shouldExit = true;
        }
        
        if ($shouldExit) break;
    } while ($retryCount < $MAX_RETRIES);
} else {
    http_response_code(405);
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido']);
}