<?php
$peticionAjax = true;
require_once "../config/APP.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['ventasHistorialAjax'])) {

    // ✅ Manejo especial para exportar Excel (viene por GET)
    if (isset($_GET['ventasHistorialAjax']) && $_GET['ventasHistorialAjax'] === 'exportar_excel') {
        session_start(['name' => 'SMP']);

        if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
            echo "Sesión expirada. Por favor inicie sesión nuevamente.";
            exit();
        }

        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            echo "Acceso denegado. No tiene permisos para exportar.";
            exit();
        }

        require_once "../controllers/ventasHistorialController.php";
        $ins_ventas = new ventasHistorialController();
        $ins_ventas->exportar_excel_controller();
        exit();
    }
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

    $valor = $_POST['ventasHistorialAjax'];



    // 🍬 DEBUG: Verificar archivos
    $controller_existe = file_exists("../controllers/ventasHistorialController.php");
    $model_existe = file_exists("../models/ventasHistorialModel.php");

    if (!$controller_existe || !$model_existe) {
        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => '🔍 DEBUG: Archivos',
            'texto' => 'Controller: ' . ($controller_existe ? '✅' : '❌') .
                ' | Model: ' . ($model_existe ? '✅' : '❌'),
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    require_once "../controllers/ventasHistorialController.php";

    // 🍬 DEBUG: Verificar clase
    if (!class_exists('ventasHistorialController')) {
        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => '🔍 DEBUG: Clase no existe',
            'texto' => 'La clase ventaHistorialController no se pudo cargar',
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    try {
        $ins_ventas = new ventasHistorialController();
    } catch (Exception $e) {
        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => '🔍 DEBUG: Error instanciando',
            'texto' => $e->getMessage(),
            'Tipo' => 'error'
        ];
        echo json_encode($alerta);
        exit();
    }

    if ($valor === "listar") {
        $pagina = isset($_POST['pagina']) ? (int) $_POST['pagina'] : 1;
        $registros = isset($_POST['registros']) ? (int) $_POST['registros'] : 10;
        $busqueda = isset($_POST['busqueda']) ? $ins_ventas->limpiar_cadena($_POST['busqueda']) : '';
        $select1 = isset($_POST['select1']) ? $ins_ventas->limpiar_cadena($_POST['select1']) : '';
        $select2 = isset($_POST['select2']) ? $ins_ventas->limpiar_cadena($_POST['select2']) : '';
        $select3 = isset($_POST['select3']) ? $ins_ventas->limpiar_cadena($_POST['select3']) : '';
        $select4 = isset($_POST['select4']) ? $ins_ventas->limpiar_cadena($_POST['select4']) : '';
        $select5 = isset($_POST['select5']) ? $ins_ventas->limpiar_cadena($_POST['select5']) : '';

        try {
            // 🍬 DEBUG: Antes de llamar al método
            if (!method_exists($ins_ventas, 'paginado_ventas_historial_controller')) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => '🔍 DEBUG: Método no existe',
                    'texto' => 'El método paginado_ventas_historial_controller no existe en la clase',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            $html = $ins_ventas->paginado_ventas_historial_controller(
                $pagina,
                $registros,
                "ventasHistorialLista",
                $busqueda,
                $select1,
                $select2,
                $select3,
                $select4,
                $select5
            );

            // 🍬 DEBUG: Verificar HTML generado
            if (empty($html)) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => '🔍 DEBUG: HTML vacío',
                    'texto' => 'El controlador retornó un HTML vacío',
                    'Tipo' => 'warning'
                ];
                echo json_encode($alerta);
                exit();
            }

            if (strpos($html, '<') === false) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => '🔍 DEBUG: No es HTML',
                    'texto' => 'Primeros 200 chars: ' . substr($html, 0, 200),
                    'Tipo' => 'warning'
                ];
                echo json_encode($alerta);
                exit();
            }

            // ✅ Todo OK, devolver HTML
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit();
        } catch (PDOException $e) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => '🔍 DEBUG: Error SQL',
                'texto' => 'Error: ' . $e->getMessage() . ' | Línea: ' . $e->getLine(),
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        } catch (Exception $e) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => '🔍 DEBUG: Exception',
                'texto' => 'Mensaje: ' . $e->getMessage() . ' | Archivo: ' . basename($e->getFile()) . ' | Línea: ' . $e->getLine(),
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    if ($valor === "detalle") {
        echo $ins_ventas->detalle_venta_controller();
        exit();
    }
    if ($valor === "generar_pdf") {
        echo $ins_ventas->generar_pdf_nota_controller();
        exit();
    }

    if ($valor === "exportar_excel") {
        $ins_ventas->exportar_excel_controller();
        exit();
    }
} else {
    session_start(['name' => 'SMP']);
    session_unset();
    session_destroy();

    $alerta = [
        'Alerta' => 'simple',
        'Titulo' => 'Acceso denegado',
        'texto' => 'Petición no autorizada',
        'Tipo' => 'error'
    ];
    echo json_encode($alerta);
    exit();
}
