<?php
$peticionAjax = true;
require_once "../config/APP.php";

if (isset($_GET['ventasHistorialAjax']) && $_GET['ventasHistorialAjax'] === 'exportar_excel') {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        
        
        
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;

    if ($rol_usuario == 3) {
        echo "Acceso denegado. No tiene permisos para exportar.";
        exit();
    }

    require_once "../controllers/ventasHistorialController.php";
    $ins_ventas = new ventasHistorialController();
    $ins_ventas->exportar_excel_controller();
    exit();
}

if (isset($_POST['ventasHistorialAjax'])) {
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
            'texto' => 'No cuenta con los privilegios necesarios',
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    $valor = $_POST['ventasHistorialAjax'];

    require_once "../controllers/ventasHistorialController.php";
    $ins_ventas = new ventasHistorialController();

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $ins_ventas->limpiar_cadena($_POST['busqueda']) : '';
        $select1 = isset($_POST['select1']) ? $ins_ventas->limpiar_cadena($_POST['select1']) : '';
        $select2 = isset($_POST['select2']) ? $ins_ventas->limpiar_cadena($_POST['select2']) : '';
        $select3 = isset($_POST['select3']) ? $ins_ventas->limpiar_cadena($_POST['select3']) : '';
        $select4 = isset($_POST['select4']) ? $ins_ventas->limpiar_cadena($_POST['select4']) : '';
        $select5 = isset($_POST['select5']) ? $ins_ventas->limpiar_cadena($_POST['select5']) : '';

        $html = $ins_ventas->paginado_ventas_historial_controller(
            $pagina,
            $registros,
            "ventasHistorialLista",
            $busqueda,
            $select1,
            $select2,
            $select3,
            $select4,
            $select5
        );

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit();
    }

    if ($valor === "detalle") {
        echo $ins_ventas->detalle_venta_controller();
        exit();
    }

    if ($valor === "generar_pdf") {
        echo $ins_ventas->generar_pdf_nota_controller();
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