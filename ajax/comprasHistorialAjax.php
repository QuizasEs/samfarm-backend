<?php
$peticionAjax = true;

require_once "../config/APP.php";

if (isset($_GET['comprasHistorialAjax']) && $_GET['comprasHistorialAjax'] == "exportar_pdf") {
    
    session_start(['name' => 'SMP']);
    
    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }
    
    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para exportar.";
        exit();
    }
    
    require_once "../controllers/compraHistorialController.php";
    $ins_compra = new compraHistorialController();
    $ins_compra->exportar_compras_pdf_controller();
    exit();
}

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['comprasHistorialAjax'])) {

    session_start(['name' => 'SMP']);

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
            "Titulo" => "Acceso denegado",
            "texto" => "No cuenta con los privilegios necesarios para ejecutar esta acción",
            "Tipo" => "error"
        ]);
        exit();
    }

    $valor = $_POST['comprasHistorialAjax'];

    require_once "../controllers/compraHistorialController.php";
    $ins_compra = new compraHistorialController();

    if ($valor === "listar") {
        header('Content-Type: text/html; charset=utf-8');
        
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        $select1 = isset($_POST['select1']) ? $_POST['select1'] : '';
        $select2 = isset($_POST['select2']) ? $_POST['select2'] : '';
        $select3 = isset($_POST['select3']) ? $_POST['select3'] : '';
        $select4 = isset($_POST['select4']) ? $_POST['select4'] : '';
        $select5 = isset($_POST['select5']) ? $_POST['select5'] : '';

        echo $ins_compra->paginado_compras_historial_controller(
            $pagina,
            $registros,
            'compras-historial',
            $busqueda,
            $select1,
            $select2,
            $select3,
            $select4,
            $select5
        );
        exit();
    }

    if ($valor == "detalle") {
        echo $ins_compra->detalle_compra_controller();
        exit();
    }

    if ($valor == "grafico") {
        echo $ins_compra->datos_grafico_compras_controller();
        exit();
    }

    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acción no válida",
        "texto" => "La acción solicitada no existe",
        "Tipo" => "error"
    ]);
    exit();

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