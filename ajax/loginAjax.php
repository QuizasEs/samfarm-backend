<?php
$peticionAjax = true;
require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['token']) && isset($_POST['usuario'])) {
    /* instanciamos al controlador login */
    require_once "../controllers/loginController.php";
    $ins_login = new loginController();

    echo $ins_login->cerrar_sesion_controller();
} else {
    session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();
    header("Location: " . SERVER_URL . "login/");
    exit();
}
    