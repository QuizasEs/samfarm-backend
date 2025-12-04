<?php
$peticionAjax = true;

require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['sucursalAjax']) || isset($_GET['sucursalAjax'])) {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || $_SESSION['rol_smp'] != 1) {
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

    $valor = $_POST['sucursalAjax'] ?? $_GET['sucursalAjax'];

    require_once "../controllers/sucursalController.php";
    $ins_sucursal = new sucursalController();

    if ($valor === "listar") {
        echo $ins_sucursal->listar_sucursales_controller();
        exit();
    }

    if ($valor === "cajas_abiertas") {
        echo $ins_sucursal->cajas_abiertas_controller();
        exit();
    }

    if ($valor === "toggle_estado") {
        echo $ins_sucursal->toggle_estado_controller();
        exit();
    }

    if ($valor === "exportar_pdf") {
        $ins_sucursal->exportar_pdf_controller();
        exit();
    }
    if ($valor === "nuevo") {
        echo $ins_sucursal->nueva_sucursal_controller();
        exit();
    }

    if ($valor === "editar") {
        echo $ins_sucursal->editar_sucursal_controller();
        exit();
    }

    if ($valor === "obtener") {
        echo $ins_sucursal->obtener_sucursal_controller();
        exit();
    }

    if ($valor === "detalle") {
        echo $ins_sucursal->detalle_sucursal_controller();
        exit();
    }
    if ($valor === "costo_beneficio") {
        echo $ins_sucursal->costo_beneficio_controller();
        exit();
    }

    if ($valor === "actualizar_config") {
        echo $ins_sucursal->actualizar_config_empresa_controller();
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
