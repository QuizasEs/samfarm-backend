<?php

$peticionAjax = true;

require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

session_start(['name' => 'SMP']);

if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
    session_unset();
    session_destroy();

    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Sesión Expirada',
        'Texto' => 'Por favor vuelva a iniciar sesión',
        'Tipo' => 'error'
    ]);
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

session_write_close();

require_once "../controllers/mermaController.php";
$ins_merma = new mermaController();

if (!isset($_POST['mermaRegistrarAjax'])) {
    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Error',
        'Texto' => 'Parámetro inválido',
        'Tipo' => 'error'
    ]);
    exit();
}

$valor = $_POST['mermaRegistrarAjax'];

if ($valor === "crear") {
    $resultado = $ins_merma->crear_merma_controller();
    echo json_encode($resultado);

} elseif ($valor === "listar") {
    // extraer parametros de la peticion
    $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
    $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
    $busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';
    $select1 = isset($_POST['select1']) ? trim($_POST['select1']) : '';
    $select2 = isset($_POST['select2']) ? trim($_POST['select2']) : '';

    // llamar al nuevo metodo del controlador que genera toda la tabla
    // esto respeta la arquitectura del proyecto (tabla en controlador)
    $html = $ins_merma->paginado_lotes_caducidad_merma_controller(
        $pagina,
        $registros,
        $busqueda,
        $select1,
        $select2
    );

    // el controlador ya devuelve el html correcto
    header('Content-Type: text/html; charset=utf-8');
    echo $html;

} else {
    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Error',
        'Texto' => 'Acción no válida',
        'Tipo' => 'error'
    ]);
}
