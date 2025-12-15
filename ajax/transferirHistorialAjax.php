<?php
$peticionAjax = true;
require_once "../config/APP.php";

// GET para descarga de PDF (como compras)
if (isset($_GET['transferirHistorialAjax']) && $_GET['transferirHistorialAjax'] == "generar_pdf") {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        echo "Sesión expirada. Por favor inicie sesión nuevamente.";
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario == 3) {
        echo "No tiene permisos para descargar PDF.";
        exit();
    }

    require_once "../controllers/transferirHistorialController.php";
    $ins_historial = new transferirHistorialController();
    $_GET['tr_id'] = isset($_GET['tr_id']) ? (int)$_GET['tr_id'] : 0;
    $ins_historial->generar_pdf_transferencia_controller();
    exit();
}

if (isset($_POST['transferirHistorialAjax'])) {
    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();
        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => 'Sesión expirada',
            'texto' => 'Por favor vuelva a iniciar sesión',
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;

    if ($rol_usuario == 3) {
        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => 'Acceso denegado',
            'texto' => 'No cuenta con los privilegios necesarios',
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    $valor = $_POST['transferirHistorialAjax'];

    require_once "../controllers/transferirHistorialController.php";
    $ins_historial = new transferirHistorialController();

    if ($valor === "listar") {
        header('Content-Type: text/html; charset=utf-8');
        
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $ins_historial->limpiar_cadena($_POST['busqueda']) : '';
        $su_origen = isset($_POST['select1']) ? $ins_historial->limpiar_cadena($_POST['select1']) : '';
        $su_destino = isset($_POST['select2']) ? $ins_historial->limpiar_cadena($_POST['select2']) : '';
        $us_emisor = isset($_POST['select3']) ? $ins_historial->limpiar_cadena($_POST['select3']) : '';
        $estado = isset($_POST['select4']) ? $ins_historial->limpiar_cadena($_POST['select4']) : '';
        $fecha_desde = isset($_POST['fecha_desde']) ? $ins_historial->limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? $ins_historial->limpiar_cadena($_POST['fecha_hasta']) : '';

        error_log("AJAX LISTAR DEBUG: pagina=$pagina, registros=$registros, su_origen='$su_origen', su_destino='$su_destino', us_emisor='$us_emisor', estado='$estado'");

        $html = $ins_historial->paginado_historial_transferencias_controller(
            $pagina,
            $registros,
            $su_origen,
            $su_destino,
            $us_emisor,
            $estado,
            $fecha_desde,
            $fecha_hasta,
            $busqueda
        );

        echo $html;
        exit();
    } elseif ($valor === "detalle") {
        header('Content-Type: application/json; charset=utf-8');
        $_POST['tr_id'] = isset($_POST['tr_id']) ? (int)$_POST['tr_id'] : 0;
        echo $ins_historial->obtener_detalles_transferencia_controller();
        exit();
    } elseif ($valor === "generar_pdf") {
        header('Content-Type: application/json; charset=utf-8');
        $_POST['tr_id'] = isset($_POST['tr_id']) ? (int)$_POST['tr_id'] : 0;
        echo $ins_historial->generar_pdf_transferencia_controller();
        exit();
    }
} else {
    echo json_encode(['Alerta' => 'simple', 'Titulo' => 'Error', 'texto' => 'Solicitud inválida', 'Tipo' => 'error']);
}
