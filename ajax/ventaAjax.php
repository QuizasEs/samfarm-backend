<?php
file_put_contents('debug_post.txt', print_r($_POST, true));
// Indicamos que esta petición viene vía AJAX
$peticionAjax = true;

// Importamos la configuración general
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

// Forzamos salida JSON
header('Content-Type: application/json; charset=utf-8');

// ✅ VALIDACIÓN DE SEGURIDAD (igual que userAjax.php)
if (isset($_POST['ventaAjax'])) {

    // Iniciamos sesión para validar permisos
    session_start(['name' => 'SMP']);

    // Verificar que el usuario tenga sesión activa y permisos
    if (!isset($_SESSION['id_smp']) || !in_array($_SESSION['rol_smp'], [1,2,3])) {
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
    $valor = $_POST['ventaAjax'];

    require_once "../controllers/ventaController.php";
    $ins_venta = new ventaController();

    if ($valor == "save") {
        // 🐛 DEBUG
        $debug = [
            'Alerta' => 'simple',
            'Titulo' => 'DEBUG - Datos recibidos',
            'texto' => '<pre>' . print_r($_POST, true) . '</pre>',
            'Tipo' => 'info'
        ];
        echo json_encode($debug);
        exit();

        // 🚀 Producción (descomentar después)

    }
    if($valor === 'buscar'){
        $termino = $_POST['termino'] ?? '';
        $filtros = [
            'linea' => $_POST['linea'] ?? null,
            'presentacion' => $_POST['presentacion'] ?? null,
            'funcion' => $_POST['funcion'] ?? null,
            'via' => $_POST['via'] ?? null
        ];
        echo $ins_venta->buscar_medicamento_controller($termino,$filtros);
        exit();
    }
    if($valor === 'mas_vendidos'){
        $limit = $_POST['limit'] ?? 5;
        echo $ins_venta->mas_vendidos_controller($limit);
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
