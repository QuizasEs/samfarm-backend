<?php
$peticionAjax = true;

require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['cajaAjax']) || isset($_GET['cajaAjax'])) {

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

    $valor = $_POST['cajaAjax'] ?? $_GET['cajaAjax'];

    require_once "../controllers/cajaController.php";
    $ins_caja = new cajaController();

    if ($valor === "listar") {
        // Si viene desde tabla-dinamica, devolver HTML
        if (isset($_POST['pagina']) && isset($_POST['registros'])) {
            header('Content-Type: text/html; charset=utf-8');
            echo $ins_caja->listar_cajas_html_controller();
        } else {
            header('Content-Type: application/json; charset=utf-8');
            echo $ins_caja->listar_cajas_controller();
        }
        exit();
    }

    if ($valor === "obtener") {
        echo $ins_caja->obtener_caja_controller();
        exit();
    }

    if ($valor === "cerrar") {
        echo $ins_caja->cerrar_caja_controller();
        exit();
    }

    if ($valor === "cajas_cerradas") {
        // Si viene desde tabla-dinamica, devolver HTML
        if (isset($_POST['pagina']) && isset($_POST['registros'])) {
            header('Content-Type: text/html; charset=utf-8');
            echo $ins_caja->listar_cajas_cerradas_html_controller();
        } else {
            header('Content-Type: application/json; charset=utf-8');
            echo $ins_caja->obtener_cajas_cerradas_controller();
        }
        exit();
    }

    if ($valor === "ventas_por_usuario") {
        echo $ins_caja->obtener_ventas_por_usuario_controller();
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
