<?php
$peticionAjax = true;

require_once "../config/APP.php";

if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "Acceso denegado. No cuenta con los privilegios necesarios.";
        exit();
    }

    require_once "../controllers/proveedorController.php";
    $ins_proveedor = new proveedorController();
    $ins_proveedor->exportar_proveedores_excel_controller();
    exit();
}

if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "Acceso denegado. No cuenta con los privilegios necesarios.";
        exit();
    }

    require_once "../controllers/proveedorController.php";
    $ins_proveedor = new proveedorController();
    $ins_proveedor->exportar_pdf_proveedores_controller();
    exit();
}

if (isset($_POST['proveedoresAjax'])) {

    session_start(['name' => 'SMP']);

    header('Content-Type: application/json; charset=utf-8');

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
            "texto" => "No cuenta con los privilegios necesarios",
            "Tipo" => "error"
        ]);
        exit();
    }

    $valor = $_POST['proveedoresAjax'];

    require_once "../controllers/proveedorController.php";
    $ins_proveedor = new proveedorController();

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int) $_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int) $_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $ins_proveedor->limpiar_cadena($_POST['busqueda']) : '';
        $select1 = isset($_POST['select1']) ? $ins_proveedor->limpiar_cadena($_POST['select1']) : '';
        $select2 = isset($_POST['select2']) ? $ins_proveedor->limpiar_cadena($_POST['select2']) : '';
        $select3 = isset($_POST['select3']) ? $ins_proveedor->limpiar_cadena($_POST['select3']) : '';

        $html = $ins_proveedor->paginado_proveedor_controller($pagina, $registros, "proveedoresLista", $busqueda, $select1, $select2, $select3);

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit();
    }

    if ($valor === "detalle") {
        echo $ins_proveedor->detalle_proveedor_controller();
        exit();
    }
    if ($valor === "registrar") {
        echo $ins_proveedor->registrar_proveedor_controller();
        exit();
    }

    if ($valor === "obtener") {
        echo $ins_proveedor->obtener_proveedor_controller();
        exit();
    }

    if ($valor === "actualizar") {
        echo $ins_proveedor->actualizar_proveedor_controller();
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
