<?php
$peticionAjax = true;
require_once "../config/APP.php";

if (isset($_GET['clientesAjax']) && $_GET['clientesAjax'] == "exportar_excel") {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para exportar clientes.";
        exit();
    }

    require_once "../controllers/clienteController.php";
    $ins_cliente = new clienteController();
    $ins_cliente->exportar_clientes_excel_controller();
    exit();
}

/* generadores de documentos */
if (isset($_GET['clientesAjax']) && $_GET['clientesAjax'] == "exportar_pdf_cliente") {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para exportar PDF.";
        exit();
    }

    require_once "../controllers/clienteController.php";
    $ins_cliente = new clienteController();
    $ins_cliente->exportar_pdf_cliente_controller();
    exit();
}

if (isset($_GET['clientesAjax']) && $_GET['clientesAjax'] == "exportar_pdf_detalle") {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para exportar PDF.";
        exit();
    }

    require_once "../controllers/clienteController.php";
    $ins_cliente = new clienteController();
    $ins_cliente->exportar_pdf_detalle_controller();
    exit();
}

if (isset($_POST['clientesAjax'])) {
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

    $valor = $_POST['clientesAjax'];

    require_once "../controllers/clienteController.php";
    $ins_cliente = new clienteController();

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        $estado = isset($_POST['select1']) ? $_POST['select1'] : '';
        $con_compras = isset($_POST['select2']) ? $_POST['select2'] : '';
        $ultima_compra = isset($_POST['select3']) ? $_POST['select3'] : '';

        echo $ins_cliente->paginado_clientes_controller(
            $pagina,
            $registros,
            'clientesLista',
            $busqueda,
            $estado,
            $con_compras,
            $ultima_compra
        );
        exit();
    }
    if ($valor === "nuevo") {
        echo $ins_cliente->agregar_cliente_controller();
        exit();
    }

    if ($valor === "editar") {
        echo $ins_cliente->editar_cliente_controller();
        exit();
    }

    if ($valor === "toggle_estado") {
        echo $ins_cliente->toggle_estado_cliente_controller();
        exit();
    }

    if ($valor === "datos_cliente") {
        echo $ins_cliente->datos_cliente_controller();
        exit();
    }
    if ($valor === "detalle_completo") {
        echo $ins_cliente->detalle_completo_cliente_controller();
        exit();
    }

    if ($valor === "ultimas_compras") {
        echo $ins_cliente->ultimas_compras_cliente_controller();
        exit();
    }

    if ($valor === "medicamentos_mas_comprados") {
        echo $ins_cliente->medicamentos_mas_comprados_controller();
        exit();
    }

    if ($valor === "grafico_compras_mensuales") {
        echo $ins_cliente->grafico_compras_mensuales_controller();
        exit();
    }
    if ($valor === "historial_completo") {
        echo $ins_cliente->historial_completo_controller();
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
