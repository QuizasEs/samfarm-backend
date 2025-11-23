<?php
// Indicamos que esta petición viene vía AJAX
$peticionAjax = true;

// Importamos la configuración general
require_once "../config/APP.php";

// Forzamos salida JSON por defecto
header('Content-Type: application/json; charset=utf-8');

// ✅ CAMBIO 1: medicamentoAjax en minúscula
if (isset($_POST['MedicamentoAjax'])) {
    session_start(['name' => 'SMP']);

    // Verificar que el usuario tenga sesión activa y permisos
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
    if ($rol_usuario == 3) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error",
            "texto" => "No cuenta con los privilegios necesarios para ejecutar esta acción",
            "Tipo" => "error"
        ]);
        exit();
    }

    // ✅ CAMBIO 2: medicamentoAjax en minúscula
    $valor = $_POST['MedicamentoAjax'];

    require_once "../controllers/medicamentoController.php";
    $ins_med = new medicamentoController();

    if ($valor == "save") {
        echo $ins_med->agregar_medicamento_controller();
    }

    if ($valor == "update") {
        echo $ins_med->actualizar_medicamento_controller();
    }

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $ins_med->limpiar_cadena($_POST['busqueda']) : '';

        $laboratorio = isset($_POST['select1']) ? $ins_med->limpiar_cadena($_POST['select1']) : '';
        $via = isset($_POST['select2']) ? $ins_med->limpiar_cadena($_POST['select2']) : '';
        $forma = isset($_POST['select3']) ? $ins_med->limpiar_cadena($_POST['select3']) : '';
        $uso = isset($_POST['select4']) ? $ins_med->limpiar_cadena($_POST['select4']) : '';


        $html = $ins_med->paginado_medicamento_controller(
            $pagina,           
            $registros,         
            $_SESSION['rol_smp'],
            'medicamentoLista',   
            $busqueda,           
            $laboratorio,       
            $via,               
            $forma,           
            $uso                
        );

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit();
    }
} else {
    // Petición inválida - cerrar sesión
/*     session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();
    header("Location: " . SERVER_URL . "login/");
    exit(); */
}
