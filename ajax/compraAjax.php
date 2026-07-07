<?php


// Indicamos que esta petición viene vía AJAX
$peticionAjax = true;
    
// Importamos la configuración general
require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['compraAjax'])) {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || !in_array($_SESSION['rol_smp'], [1, 2])) {
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

    $valor = $_POST['compraAjax'];

    require_once "../controllers/compraController.php";
    
    try {
        $ins_compra = new compraController();
        
        if ($valor == "save") {
            echo json_encode($ins_compra->agregar_compra_controller());
        }
    } catch (Throwable $e) {
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
            'uso' => $_POST['uso'] ?? '',
            'proveedor' => $_POST['proveedor'] ?? ''
        ];
        $resultados = $ins_compra->buscar_medicamento_controller($filtros);
        echo json_encode($resultados->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }

    if ($valor == "ultimo_lote_producto") {
        $med_id = isset($_POST['med_id']) ? (int)$_POST['med_id'] : 0;
        echo json_encode($ins_compra->ultimo_lote_por_medicamento_controller($med_id));
        exit();
    }
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
