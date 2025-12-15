<?php
// Indicamos que esta petición viene vía AJAX
$peticionAjax = true;

// Importamos la configuración general
require_once "../config/APP.php";

if (isset($_GET['inventarioAjax']) && $_GET['inventarioAjax'] == "exportar_excel") {

    session_start(['name' => 'SMP']);

    // Verificar sesión y permisos
    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para exportar inventario.";
        exit();
    }

    // Procesar exportación
    require_once "../controllers/inventarioController.php";
    $ins_inventario = new inventarioController();
    $ins_inventario->exportar_inventario_excel_controller();
    exit();
}

if (isset($_GET['inventarioAjax']) && $_GET['inventarioAjax'] == "exportar_pdf") {

    session_start(['name' => 'SMP']);

    // Verificar sesión y permisos
    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para exportar inventario.";
        exit();
    }

    // Procesar exportación PDF
    require_once "../controllers/inventarioController.php";
    $ins_inventario = new inventarioController();
    $ins_inventario->exportar_pdf_inventario_controller();
    exit();
}

if (isset($_POST['inventarioAjax'])) {

    // Iniciamos sesión para validar permisos
    session_start(['name' => 'SMP']);

    // Verificar que el usuario tenga sesión activa y permisos
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

    // ✅ Sesión válida, procesar petición
    $valor = $_POST['inventarioAjax'];

    require_once "../controllers/inventarioController.php";
    $ins_inventario = new inventarioController();

    /* ===== LISTAR INVENTARIOS ===== */
    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        $laboratorio = isset($_POST['select1']) ? $_POST['select1'] : '';
        $estado = isset($_POST['select2']) ? $_POST['select2'] : '';
        $sucursal = isset($_POST['select3']) ? $_POST['select3'] : '';
        $forma = isset($_POST['select4']) ? $_POST['select4'] : '';

        echo $ins_inventario->paginado_inventario_controller(
            $pagina,
            $registros,
            'inventario-lista',
            $busqueda,
            $laboratorio,
            $estado,
            $sucursal,
            $forma
        );
        exit();
    }

    /* ===== DETALLE DE INVENTARIO ===== */
    if ($valor == "detalle") {
        echo $ins_inventario->detalle_inventario_controller();
        exit();
    }

    /* ===== LOTES TRANSFERIBLES ===== */
    if ($valor == "lotes_transferibles") {
        echo $ins_inventario->lotes_transferibles_controller();
        exit();
    }

    /* ===== HISTORIAL DE MOVIMIENTOS ===== */
    if ($valor == "historial") {
        echo $ins_inventario->historial_movimientos_controller();
        exit();
    }

    /* ===== GUARDAR CONFIGURACIÓN DE INVENTARIO ===== */
    if ($valor == "configurar") {
        echo $ins_inventario->guardar_configuracion_inventario_controller();
        exit();
    }

    require_once "../models/inventarioModel.php";

    /* ===== OBTENER MARGEN BRUTO POR MEDICAMENTO ===== */
    if ($valor == "margen_medicamentos") {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
        $su_id = ($rol_usuario == 2) ? $sucursal_usuario : null;
        
        $datos = inventarioModel::margen_bruto_por_medicamento_model($su_id);
        echo json_encode($datos);
        exit();
    }

    /* ===== OBTENER MARGEN BRUTO DIARIO ===== */
    if ($valor == "margen_diario") {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
        $su_id = ($rol_usuario == 2) ? $sucursal_usuario : null;
        
        $datos = inventarioModel::margen_bruto_diario_model($su_id);
        echo json_encode($datos);
        exit();
    }

    /* ===== OBTENER MARGEN BRUTO POR SUCURSAL ===== */
    if ($valor == "margen_sucursal") {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
        $su_id = ($rol_usuario == 2) ? $sucursal_usuario : null;
        
        $datos = inventarioModel::margen_bruto_por_sucursal_model($su_id);
        echo json_encode($datos);
        exit();
    }

    // Si llegamos aquí, la acción no existe
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acción no válida",
        "texto" => "La acción solicitada no existe",
        "Tipo" => "error"
    ]);
    exit();

} else {
    // Petición inválida - cerrar sesión
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
