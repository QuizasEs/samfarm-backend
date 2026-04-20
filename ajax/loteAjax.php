
<?php
$peticionAjax = true;

require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['loteAjax'])) {

    // Iniciamos sesión para validar permisos
    session_start(['name' => 'SMP']);

    // Verificar que el usuario tenga sesión activa y permisos
    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        // Sesión inválida o sin permisos
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
            "Titulo" => "Ocurrio un error",
            "texto" => "No cuenta con lo privilegios necesarios para ejecutar esta accion",
            "Tipo" => "error"
        ]);
        exit();
    }

    $valor = $_POST['loteAjax'];

    require_once "../controllers/loteController.php";
    $ins_lote = new loteController();

    if ($valor === "obtener_lote") {
        $lote_id = $_POST['lote_id'];
        $datos = $ins_lote->datos_lote_controller($lote_id);
        if ($datos->rowCount() == 1) {
            echo json_encode($datos->fetch(PDO::FETCH_ASSOC));
        } else {
            echo json_encode(['error' => 'Lote no encontrado']);
        }
        exit();
    }

    if ($valor === "listar") {
        // obtener parámetros
        $pagina   = isset($_POST['pagina']) ? (int) $_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int) $_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $ins_lote->limpiar_cadena($_POST['busqueda']) : '';
        $select1  = isset($_POST['select1']) ? $ins_lote->limpiar_cadena($_POST['select1']) : '';
        $select2  = isset($_POST['select2']) ? $ins_lote->limpiar_cadena($_POST['select2']) : '';
        $select3  = isset($_POST['select3']) ? $ins_lote->limpiar_cadena($_POST['select3']) : '';

        // Llamada al controlador. Asegúrate que el método acepte los nuevos parámetros.
        $html = $ins_lote->paginado_lote_controller($pagina, $registros, "loteLista", $busqueda, $select1, $select2, $select3);

        // devolver HTML directamente
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit();
    }

    if ($valor == "active") {

        // 🐛 DEBUG
        /* $debug = [
            'Alerta' => 'simple',
            'Titulo' => 'DEBUG - Datos recibidos',
            'texto' => '<pre>' . print_r($_POST, true) . '</pre>',
            'Tipo' => 'info'
        ];
        echo json_encode($debug);
        exit(); */

        echo $ins_lote->activar_lote_controller();
    }
    if ($valor == "update") {
        // 🐛 DEBUG
        /* $debug = [
            'Alerta' => 'simple',
            'Titulo' => 'DEBUG - Datos recibidos',
            'texto' => '<pre>' . print_r($_POST, true) . '</pre>',
            'Tipo' => 'info'
        ];
        echo json_encode($debug);
        exit(); */

        echo $ins_lote->actualizar_lote_controller();
    }
}

// Manejar exportaciones GET (PDF/excel)
if (isset($_GET['loteAjax'])) {
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
            "Titulo" => "Ocurrio un error",
            "texto" => "No cuenta con los privilegios necesarios para ejecutar esta accion",
            "Tipo" => "error"
        ]);
        exit();
    }

    $valor = $_GET['loteAjax'];
    require_once "../controllers/loteController.php";
    $ins_lote = new loteController();

    if ($valor == "exportar_pdf") {
        $ins_lote->exportar_pdf_lotes_controller();
    }
} else if (!isset($_POST['loteAjax'])) {
    //  Petición inválida - cerrar sesión
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
