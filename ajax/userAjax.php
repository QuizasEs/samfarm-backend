<?php
    $peticionAjax = true;
    require_once "../config/APP.php";
    header('Content-Type: application/json');

    $respuesta = [
        "Alerta" => "simple",
        "Titulo" => "Usuario registrado",
        "Texto" => "El usuario fue agregado correctamente",
        "icono" => "success"
    ];

echo json_encode($respuesta);

    if (isset($_POST['Nombres_reg'])){
        /* -------------------------------------instancia de controlador---------------------------------------------- */
        require_once "../controllers/userController.php";
        $ins_user = new userController();

        /* -----------------------------------agregar usuario-------------------------------------------- */
        if (isset($_POST['usuario_reg'])&& isset($_POST['Carnet_reg'])){
            echo $ins_user->get_user_controller();
        }


    } else {
        session_start(['name' => 'SMP']);
        session_unset();
        session_destroy();
        header("location: ".SERVER_URL."login/");
        exit();
    }
?>