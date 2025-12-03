<?php
$peticionAjax = true;

require_once "../config/APP.php";

if (isset($_GET['cajaHistorialAjax']) && $_GET['cajaHistorialAjax'] == "exportar_pdf") {
    
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
    
    require_once "../controllers/cajaHistorialController.php";
    $ins_historial = new cajaHistorialController();
    $ins_historial->exportar_historial_caja_pdf_controller();
    exit();
}

if (isset($_GET['cajaHistorialAjax']) && $_GET['cajaHistorialAjax'] == "exportar_excel") {
    
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
    
    require_once "../controllers/cajaHistorialController.php";
    $ins_historial = new cajaHistorialController();
    $ins_historial->exportar_historial_caja_excel_controller();
    exit();
}

if (isset($_GET['cajaHistorialAjax']) && $_GET['cajaHistorialAjax'] == "exportar_movimiento_pdf") {
    
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
    
    require_once "../controllers/cajaHistorialController.php";
    $ins_historial = new cajaHistorialController();
    $ins_historial->exportar_movimiento_individual_pdf_controller();
    exit();
}

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['cajaHistorialAjax'])) {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();

        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => 'Sesión expirada',
            'texto' => 'Por favor vuelva a iniciar sesión',
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;

    if ($rol_usuario == 3) {
        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => 'Acceso denegado',
            'texto' => 'No tiene permisos para ver esta sección',
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    $valor = $_POST['cajaHistorialAjax'];

    require_once "../controllers/cajaHistorialController.php";
    $ins_historial = new cajaHistorialController();

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 15;
        $busqueda = isset($_POST['busqueda']) ? $ins_historial->limpiar_cadena($_POST['busqueda']) : '';
        $select1 = isset($_POST['select1']) ? $ins_historial->limpiar_cadena($_POST['select1']) : '';
        $select2 = isset($_POST['select2']) ? $ins_historial->limpiar_cadena($_POST['select2']) : '';
        $select3 = isset($_POST['select3']) ? $ins_historial->limpiar_cadena($_POST['select3']) : '';
        $select4 = isset($_POST['select4']) ? $ins_historial->limpiar_cadena($_POST['select4']) : '';

        $html = $ins_historial->paginado_historial_caja_controller(
            $pagina,
            $registros,
            "cajaHistorialLista",
            $busqueda,
            $select1,
            $select2,
            $select3,
            $select4
        );

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit();
    }

    if ($valor === "resumen") {
        echo $ins_historial->obtener_resumen_periodo_controller();
        exit();
    }

    if ($valor === "grafico") {
        echo $ins_historial->obtener_datos_grafico_controller();
        exit();
    }

    if ($valor === "obtener_referencia") {
        echo $ins_historial->obtener_referencia_movimiento_controller();
        exit();
    }
} else {
    session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();

    $alerta = [
        'Alerta' => 'simple',
        'Titulo' => 'Acceso denegado',
        'texto' => 'Petición no autorizada',
        'Tipo' => 'error'
    ];
    echo json_encode($alerta);
    exit();
}