<?php
$peticionAjax = true;
require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if () {

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
    