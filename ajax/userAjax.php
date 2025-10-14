<?php
$peticionAjax = true;
require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['Nombres_reg']) && !empty($_POST['UsuarioName_reg'])) {
    /* -------------------------------------instancia de controlador---------------------------------------------- */
    require_once "../controllers/userController.php";
    $ins_user = new userController();

    /* -----------------------------------agregar usuario-------------------------------------------- */
    if (isset($_POST['UsuarioName_reg']) && isset($_POST['Carnet_reg'])) {
        echo $ins_user->get_user_controller();
    }
} else {
    session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();
    header("location: " . SERVER_URL . "login/");
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Sesión expirada",
        "texto" => "Por favor vuelva a iniciar sesión",
        "Tipo" => "error"
    ]);
    exit();
}
