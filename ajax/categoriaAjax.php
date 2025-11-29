<?php
$peticionAjax = true;

require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['categoriaAjax'])) {

    session_start(['name' => 'SMP']);

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

    $valor = $_POST['categoriaAjax'];

    require_once "../controllers/categoriaController.php";
    $ins_categoria = new categoriaController();

    if ($valor === "listar_uso") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        echo $ins_categoria->paginado_uso_farmacologico_controller(
            $pagina,
            $registros,
            'categoria-lista',
            $busqueda
        );
        exit();
    }

    if ($valor == "agregar_uso") {
        echo $ins_categoria->agregar_uso_farmacologico_controller();
        exit();
    }

    if ($valor == "obtener_uso") {
        echo $ins_categoria->obtener_uso_farmacologico_controller();
        exit();
    }

    if ($valor == "actualizar_uso") {
        echo $ins_categoria->actualizar_uso_farmacologico_controller();
        exit();
    }

    if ($valor == "cambiar_estado_uso") {
        echo $ins_categoria->cambiar_estado_uso_farmacologico_controller();
        exit();
    }

    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acción no válida",
        "texto" => "La acción solicitada no existe",
        "Tipo" => "error"
    ]);
    exit();

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