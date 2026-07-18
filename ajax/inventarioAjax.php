<?php
// Indicamos que esta peticiรณn viene vรญa AJAX
$peticionAjax = true;

// Importamos la configuraciรณn general
require_once "../config/APP.php";

if (isset($_GET['inventarioAjax']) && $_GET['inventarioAjax'] == "exportar_excel") {

    session_start(['name' => 'SMP']);

    // Verificar sesiรณn y permisos
    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesiรณn expirada. Por favor inicie sesiรณn nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if (!in_array($rol_usuario, [1, 2])) {
        echo "No tiene permisos para exportar inventario.";
        exit();
    }

    // Procesar exportaciรณn
    require_once "../controllers/inventarioController.php";
    $ins_inventario = new inventarioController();
    $ins_inventario->exportar_inventario_excel_controller();
    exit();
}

if (isset($_GET['inventarioAjax']) && $_GET['inventarioAjax'] == "exportar_pdf") {

    session_start(['name' => 'SMP']);

    // Verificar sesiรณn y permisos
    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesiรณn expirada. Por favor inicie sesiรณn nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if (!in_array($rol_usuario, [1, 2])) {
        echo "No tiene permisos para exportar inventario.";
        exit();
    }

    // Procesar exportaciรณn PDF
    require_once "../controllers/inventarioController.php";
    $ins_inventario = new inventarioController();
    $ins_inventario->exportar_pdf_inventario_controller();
    exit();
}

if (isset($_POST['inventarioAjax'])) {

    // Iniciamos sesiรณn para validar permisos
    session_start(['name' => 'SMP']);

    // Verificar que el usuario tenga sesiรณn activa y permisos
    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();

        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Sesiรณn expirada",
            "texto" => "Por favor vuelva a iniciar sesiรณn",
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

    session_write_close();

    session_write_close();

    //  Sesión válida, procesar petición
    $valor = $_POST['inventarioAjax'];

    require_once "../controllers/inventarioController.php";
    $ins_inventario = new inventarioController();

    /* ===== LISTAR INVENTARIOS ===== */
    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';

        $proveedor = isset($_POST['select1']) ? $_POST['select1'] : '';
        $estado = isset($_POST['select2']) ? $_POST['select2'] : '';
        $sucursal = isset($_POST['select3']) ? $_POST['select3'] : '';
        $forma = isset($_POST['select4']) ? $_POST['select4'] : '';

        echo $ins_inventario->paginado_inventario_controller(
            $pagina,
            $registros,
            'inventario-lista',
            $busqueda,
            $proveedor,
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

    /* ===== GUARDAR CONFIGURACIร�N DE INVENTARIO ===== */
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

    /* ===== OBTENER DATOS PARA BALANCE ===== */
    if ($valor === "obtener_datos_balance") {
        $med_id = mainModel::limpiar_cadena($_POST['med_id'] ?? '');

        if (empty($med_id)) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        // Obtener datos actuales de precios del medicamento en la sucursal
        $resultado = $ins_inventario->obtener_datos_balance_controller($med_id);

        // Verificar que el resultado es un array vรกlido
        if (!is_array($resultado)) {
            echo json_encode(['success' => false, 'error' => 'Respuesta invรกlida del servidor']);
        } else {
            echo json_encode($resultado);
        }
        exit();
    }

    /* ===== APLICAR BALANCE DE PRECIOS ===== */
    if ($valor === "balance_precios") {
        $med_id = mainModel::limpiar_cadena($_POST['med_id']);
        $costo_lista = isset($_POST['lm_costo_lista']) ? (float)mainModel::limpiar_cadena($_POST['lm_costo_lista']) : null;
        $precio_costo = isset($_POST['lm_precio_costo']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_costo']) : null;
        $unidades_caja = isset($_POST['lm_unidades_caja']) ? (int)mainModel::limpiar_cadena($_POST['lm_unidades_caja']) : 1;
        $margen_u = isset($_POST['lm_margen_u']) ? (float)mainModel::limpiar_cadena($_POST['lm_margen_u']) : null;
        $margen_c = isset($_POST['lm_margen_c']) ? (float)mainModel::limpiar_cadena($_POST['lm_margen_c']) : null;
        $precio_venta = isset($_POST['lm_precio_venta']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_venta']) : null;
        $precio_min_u = isset($_POST['lm_precio_min_u']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_min_u']) : null;
        $precio_min_c = isset($_POST['lm_precio_min_c']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_min_c']) : null;

        if (empty($med_id)) {
            echo json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Datos incompletos',
                'texto' => 'Informaciรณn insuficiente para aplicar balance',
                'Tipo' => 'error'
            ]);
            exit();
        }

        $resultado = $ins_inventario->balance_precios_controller($med_id, $costo_lista, $precio_costo, $unidades_caja, $margen_u, $margen_c, $precio_venta, $precio_min_u, $precio_min_c);
        echo json_encode($resultado);
        exit();
    }

    /* ===== GUARDAR BALANCE DE PRECIOS ===== */
    if ($valor === "guardar_balance") {
        $med_id = mainModel::limpiar_cadena($_POST['med_id']);
        $su_id = mainModel::limpiar_cadena($_POST['su_id']);
        $costo_lista = isset($_POST['lm_costo_lista']) ? (float)mainModel::limpiar_cadena($_POST['lm_costo_lista']) : null;
        $precio_costo = isset($_POST['lm_precio_costo']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_costo']) : null;
        $unidades_caja = isset($_POST['lm_unidades_caja']) ? (int)mainModel::limpiar_cadena($_POST['lm_unidades_caja']) : 1;
        $margen_u = isset($_POST['lm_margen_u']) ? (float)mainModel::limpiar_cadena($_POST['lm_margen_u']) : null;
        $margen_c = isset($_POST['lm_margen_c']) ? (float)mainModel::limpiar_cadena($_POST['lm_margen_c']) : null;
        $precio_venta = isset($_POST['lm_precio_venta']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_venta']) : null;
        $precio_min_u = isset($_POST['lm_precio_min_u']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_min_u']) : null;
        $precio_min_c = isset($_POST['lm_precio_min_c']) ? (float)mainModel::limpiar_cadena($_POST['lm_precio_min_c']) : null;

        if (empty($med_id) || empty($su_id)) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        $resultado = $ins_inventario->guardar_balance_controller($med_id, $su_id, $costo_lista, $precio_costo, $unidades_caja, $margen_u, $margen_c, $precio_venta, $precio_min_u, $precio_min_c);
        echo json_encode($resultado);
        exit();
    }

    /* ===== OBTENER DETALLE GENERAL DE INVENTARIO ===== */
    if ($valor === "detalle_general") {
        $med_id = mainModel::limpiar_cadena($_POST['med_id']);
        $su_id = mainModel::limpiar_cadena($_POST['su_id']);

        if (empty($med_id) || empty($su_id)) {
            echo json_encode(['error' => 'Datos incompletos']);
            exit();
        }

        $resultado = $ins_inventario->detalle_general_controller($med_id, $su_id);
        echo json_encode($resultado);
        exit();
    }

    /* ===== OBTENER LOTES DISPONIBLES ===== */
    if ($valor === "lotes_disponibles") {
        $med_id = mainModel::limpiar_cadena($_POST['med_id']);
        $su_id = mainModel::limpiar_cadena($_POST['su_id']);

        if (empty($med_id) || empty($su_id)) {
            echo json_encode([]);
            exit();
        }

        $resultado = $ins_inventario->lotes_disponibles_controller($med_id, $su_id);
        echo json_encode($resultado);
        exit();
    }

    // Si llegamos aquรญ, la acciรณn no existe
    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acciรณn no vรกlida",
        "texto" => "La acciรณn solicitada no existe",
        "Tipo" => "error"
    ]);
    exit();

} else {
    // Peticiรณn invรกlida - cerrar sesiรณn
    session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();

    echo json_encode([
        "Alerta" => "simple",
        "Titulo" => "Acceso denegado",
        "texto" => "Peticiรณn no autorizada",
        "Tipo" => "error"
    ]);
    exit();
}