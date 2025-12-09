<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$peticionAjax = true;

require_once '../config/APP.php';
require_once '../controllers/dashboardController.php';

$response = ['success' => false, 'data' => null, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $su_id = null;
        if (isset($_SESSION['sucursal_smp'])) {
            $su_id = $_SESSION['sucursal_smp'];
        }

        if (isset($_GET['dashboardAjax'])) {
            $action = $_GET['dashboardAjax'];

            if ($action === 'obtener_vencimientos_ajax') {
                $data = dashboardController::contar_vencimientos_por_estado_controller($su_id);
                $formatted = [];
                foreach ($data as $item) {
                    $formatted[$item['estado']] = (int)$item['cantidad'];
                }
                $response = [
                    'success' => true,
                    'data' => [
                        'expirados' => $formatted['expirado'] ?? 0,
                        'proximos' => $formatted['proximo'] ?? 0,
                        'disponibles' => $formatted['disponible'] ?? 0
                    ]
                ];
            } elseif ($action === 'obtener_stock_minimo_ajax') {
                $data = dashboardController::obtener_stock_minimo_controller($su_id);
                $response = ['success' => true, 'data' => $data ?? []];
            } elseif ($action === 'obtener_productos_vendidos_ajax') {
                $data = dashboardController::obtener_productos_mas_vendidos_controller($su_id);
                $response = ['success' => true, 'data' => $data ?? []];
            } elseif ($action === 'obtener_ventas_mensuales_ajax') {
                $data = dashboardController::obtener_ventas_mensuales_controller($su_id);
                $response = ['success' => true, 'data' => $data ?? []];
            }
        }
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
}

echo json_encode($response);
?>
