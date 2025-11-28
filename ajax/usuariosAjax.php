<?php
$peticionAjax = true;
require_once "../config/APP.php";

if (isset($_GET['usuariosAjax']) && $_GET['usuariosAjax'] == "exportar_excel") {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para exportar usuarios.";
        exit();
    }

    require_once "../controllers/userController.php";
    $ins_usuario = new userController();
    $ins_usuario->exportar_usuarios_excel_controller();
    exit();
}

if (isset($_POST['usuariosAjax'])) {
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
            "texto" => "No cuenta con los privilegios necesarios para ejecutar esta acción",
            "Tipo" => "error"
        ]);
        exit();
    }

    $valor = $_POST['usuariosAjax'];

    require_once "../controllers/userController.php";
    $ins_usuario = new userController();

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        $sucursal = isset($_POST['select1']) ? $_POST['select1'] : '';
        $rol = isset($_POST['select2']) ? $_POST['select2'] : '';
        $estado = isset($_POST['select3']) ? $_POST['select3'] : '';

        echo $ins_usuario->paginado_usuarios_controller(
            $pagina,
            $registros,
            'usuariosLista',
            $busqueda,
            $sucursal,
            $rol,
            $estado
        );
        exit();
    }
    if ($valor === "nuevo") {
        echo $ins_usuario->agregar_usuario_controller();
        exit();
    }

    if ($valor === "editar") {
        echo $ins_usuario->editar_usuario_controller();
        exit();
    }

    if ($valor === "toggle_estado") {
        echo $ins_usuario->toggle_estado_usuario_controller();
        exit();
    }

    if ($valor === "datos_usuario") {
        echo $ins_usuario->datos_usuario_controller();
        exit();
    }
    if ($valor === "detalle_completo") {
        echo $ins_usuario->detalle_completo_usuario_controller();
        exit();
    }
    if ($valor === "ultimas_ventas") {
        echo $ins_usuario->ultimas_ventas_usuario_controller();
        exit();
    }
    if ($valor === "ventas_mensuales") {
        echo $ins_usuario->ventas_mensuales_usuario_controller();
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
