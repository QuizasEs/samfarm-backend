<?php
// Indicamos que esta petición viene vía AJAX
$peticionAjax = true;

// Importamos la configuración general de la aplicación (rutas, constantes, conexión, etc.)
require_once "../config/APP.php";

// Forzamos la salida del contenido como JSON (esto evita errores de parseo en el frontend)
header('Content-Type: application/json; charset=utf-8');



if (isset($_POST['Nombres_reg']) || isset($_POST['usuario_des']) || isset($_POST['usuario_id_up'])) {

    require_once "../controllers/userController.php";
    $ins_user = new userController();

    /* ----------------------------- conectar ajax y controladores----------------------------- */
    // Verificamos que también existan los datos necesarios para el registro
    if (isset($_POST['UsuarioName_reg']) && isset($_POST['Carnet_reg'])) {
        echo $ins_user->get_user_controller();
    }/* desabilitar usuarios */
    if (isset($_POST['usuario_des'])) {
        echo $ins_user->disable_user_controller();
    }
    /* ajax para actualizar usuario */
    if (isset($_POST['usuario_id_up'])){
        echo $ins_user->data_update_user_controller();
    }
} else {
    /* ----------------------------- SESIÓN INVÁLIDA O DATOS INCOMPLETOS ----------------------------- */
    // Si los datos requeridos no se enviaron correctamente, se asume que la sesión ha expirado
    // o que el acceso no fue autorizado.

    // Iniciamos (o retomamos) la sesión actual
    session_start(['name' => 'SMP']);

    // Limpiamos cualquier variable de sesión existente
    session_unset();

    // Destruimos la sesión por seguridad
    session_destroy();

    // Redirigimos al login principal
    header("location: " . SERVER_URL . "login/");

    // Enviamos una respuesta JSON para notificar el error al frontend
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Sesión expirada",
        "texto" => "Por favor vuelva a iniciar sesión",
        "Tipo" => "error"
    ]);

    // Finalizamos la ejecución del script
    exit();
}
