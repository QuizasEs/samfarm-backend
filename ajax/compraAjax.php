<?php
$debug_log = dirname(__FILE__) . '/debug_compra.log';
file_put_contents($debug_log, "\n=== SOLICITUD " . date('Y-m-d H:i:s') . " ===\n", FILE_APPEND);
file_put_contents($debug_log, print_r($_POST, true) . "\n", FILE_APPEND);

// Indicamos que esta petición viene vía AJAX
$peticionAjax = true;

// Importamos la configuración general
require_once "../config/APP.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    global $debug_log;
    $mensaje_error = "[$errno] $errstr en $errfile:$errline";
    file_put_contents($debug_log, "ERROR: $mensaje_error\n", FILE_APPEND);
    
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Error interno",
        "texto" => $mensaje_error,
        "Tipo" => "error"
    ]);
    exit();
}, E_ALL);

// Forzamos salida JSON
header('Content-Type: application/json; charset=utf-8');

// ✅ VALIDACIÓN DE SEGURIDAD (igual que userAjax.php)
if (isset($_POST['compraAjax'])) {

    // Iniciamos sesión para validar permisos
    session_start(['name' => 'SMP']);

    // Verificar que el usuario tenga sesión activa y permisos
    if (!isset($_SESSION['id_smp']) || $_SESSION['rol_smp'] != 1) {
        // Sesión inválida o sin permisos
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

    // ✅ Sesión válida, procesar petición
    $valor = $_POST['compraAjax'];

    require_once "../controllers/compraController.php";
    
    try {
        $ins_compra = new compraController();
        
        if ($valor == "save") {
            echo $ins_compra->agregar_compra_controller();
        }
    } catch (Throwable $e) {
        file_put_contents($debug_log, "EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Excepción",
            "texto" => $e->getMessage(),
            "Tipo" => "error"
        ]);
        exit();
    }

    if ($valor == "buscar_medicamentos") {
        $filtros = [
            'termino' => $_POST['termino'] ?? '',
            'forma' => $_POST['forma'] ?? '',
            'via' => $_POST['via'] ?? '',
            'laboratorio' => $_POST['laboratorio'] ?? '',
            'uso' => $_POST['uso'] ?? ''
        ];
        $resultados = $ins_compra->buscar_medicamento_controller($filtros);
        echo json_encode($resultados->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }
} else {
    // ❌ Petición inválida - cerrar sesión
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
