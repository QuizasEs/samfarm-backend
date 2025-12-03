<?php

$peticionAjax = true;

require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['mermaAjax'])) {

    session_start(['name' => 'SMP']);

    if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
        session_unset();
        session_destroy();

        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => 'Sesión Expirada',
            'Texto' => 'Por favor vuelva a iniciar sesión',
            'Tipo' => 'error'
        ];

        echo json_encode($alerta);
        exit();
    }

    $rol_usuario = $_SESSION['rol_smp'] ?? 0;
    if ($rol_usuario != 1 && $rol_usuario != 2) {
        echo json_encode([
            'Alerta' => 'simple',
            'Titulo' => 'Acceso Denegado',
            'Texto' => 'No tiene permisos para acceder a esta funcionalidad',
            'Tipo' => 'error'
        ]);
        exit();
    }

    $valor = $_POST['mermaAjax'];

    require_once "../controllers/mermaController.php";
    $ins_merma = new mermaController();

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 15;
        $busqueda = isset($_POST['busqueda']) ? $ins_merma->limpiar_cadena($_POST['busqueda']) : '';
        $fecha_desde = isset($_POST['fecha_desde']) ? $ins_merma->limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? $ins_merma->limpiar_cadena($_POST['fecha_hasta']) : '';
        $select2 = isset($_POST['select2']) ? $ins_merma->limpiar_cadena($_POST['select2']) : '';

        $html = $ins_merma->paginado_historial_mermas_controller(
            $pagina,
            $registros,
            "mermaLista",
            $busqueda,
            $fecha_desde,
            $fecha_hasta,
            $select2
        );

        header('Content-Type: text/html; charset=utf-8');
        echo $html;

    } elseif ($valor === "crear") {
        $resultado = $ins_merma->crear_merma_controller();
        echo json_encode($resultado);
    }

} else {
    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Error',
        'Texto' => 'Parámetro inválido',
        'Tipo' => 'error'
    ]);
}
