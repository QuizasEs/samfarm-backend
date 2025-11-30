<?php
$peticionAjax = true;

require_once "../config/APP.php";

if (isset($_POST['categoriaAjax'])) {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();

        header('Content-Type: application/json; charset=utf-8');
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
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Acceso denegado",
            "texto" => "No cuenta con los privilegios necesarios",
            "Tipo" => "error"
        ]);
        exit();
    }

    $valor = $_POST['categoriaAjax'];
    
    error_log("DEBUG categoriaAjax: Valor recibido = '" . $valor . "' (tipo: " . gettype($valor) . ")");
    error_log("DEBUG categoriaAjax: \$_POST = " . json_encode($_POST));

    require_once "../controllers/categoriaController.php";
    $ins_categoria = new categoriaController();

    if ($valor === "listar_uso") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 6;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        header('Content-Type: text/html; charset=utf-8');
        echo $ins_categoria->paginado_uso_farmacologico_controller(
            $pagina,
            $registros,
            'categoria-lista',
            $busqueda
        );
        exit();
    }

    if ($valor == "agregar_uso") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->agregar_uso_farmacologico_controller();
        exit();
    }

    if ($valor == "obtener_uso") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->obtener_uso_farmacologico_controller();
        exit();
    }

    if ($valor == "actualizar_uso") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->actualizar_uso_farmacologico_controller();
        exit();
    }

    if ($valor == "cambiar_estado_uso") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->cambiar_estado_uso_farmacologico_controller();
        exit();
    }
    if ($valor === "listar_via") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 6;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        header('Content-Type: text/html; charset=utf-8');
        echo $ins_categoria->paginado_via_administracion_controller(
            $pagina,
            $registros,
            'categoria-lista',
            $busqueda
        );
        exit();
    }

    if ($valor == "agregar_via") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->agregar_via_administracion_controller();
        exit();
    }

    if ($valor == "obtener_via") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->obtener_via_administracion_controller();
        exit();
    }

    if ($valor == "actualizar_via") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->actualizar_via_administracion_controller();
        exit();
    }

    if ($valor == "cambiar_estado_via") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->cambiar_estado_via_administracion_controller();
        exit();
    }

    if ($valor === "listar_forma") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 6;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        header('Content-Type: text/html; charset=utf-8');
        echo $ins_categoria->paginado_forma_farmaceutica_controller(
            $pagina,
            $registros,
            'categoria-lista',
            $busqueda
        );
        exit();
    }

    if ($valor == "agregar_forma") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->agregar_forma_farmaceutica_controller();
        exit();
    }

    if ($valor == "obtener_forma") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->obtener_forma_farmaceutica_controller();
        exit();
    }

    if ($valor == "actualizar_forma") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->actualizar_forma_farmaceutica_controller();
        exit();
    }

    if ($valor == "cambiar_estado_forma") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->cambiar_estado_forma_farmaceutica_controller();
        exit();
    }

    if ($valor === "listar_laboratorio") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 6;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        header('Content-Type: text/html; charset=utf-8');
        echo $ins_categoria->paginado_laboratorios_controller(
            $pagina,
            $registros,
            'categoria-lista',
            $busqueda
        );
        exit();
    }

    if ($valor == "agregar_laboratorio") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->agregar_laboratorios_controller();
        exit();
    }

    if ($valor == "obtener_laboratorio") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->obtener_laboratorios_controller();
        exit();
    }

    if ($valor == "actualizar_laboratorio") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->actualizar_laboratorios_controller();
        exit();
    }

    if ($valor == "cambiar_estado_laboratorio") {
        header('Content-Type: application/json; charset=utf-8');
        echo $ins_categoria->cambiar_estado_laboratorios_controller();
        exit();
    }

    header('Content-Type: application/json; charset=utf-8');
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

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acceso denegado",
        "texto" => "Petición no autorizada",
        "Tipo" => "error"
    ]);
    exit();
}
