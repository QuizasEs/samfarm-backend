<?php

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/models/inventarioModel.php';

try {
    $inicio = microtime(true);
    
    $resultado = inventarioModel::recalcular_valorado_inventario_model();
    
    $tiempo_ejecucion = round((microtime(true) - $inicio) * 1000, 2);
    
    $mensaje = date('Y-m-d H:i:s') . " - Recálculo de valorado completado en {$tiempo_ejecucion}ms\n";
    $mensaje .= "  Registros actualizados: " . $resultado['actualizados'] . "\n";
    $mensaje .= "  Total medicamentos verificados: " . $resultado['total'] . "\n";
    
    $log_file = dirname(__DIR__) . '/logs/recalcularValorizado.log';
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    file_put_contents($log_file, $mensaje, FILE_APPEND);
    
    echo "OK - Valorado recalculado: " . $resultado['actualizados'] . " registros actualizados";
    exit(0);
    
} catch (Exception $e) {
    $error_mensaje = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    
    $log_file = dirname(__DIR__) . '/logs/recalcularValorizado.log';
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    file_put_contents($log_file, $error_mensaje, FILE_APPEND);
    
    echo "ERROR - " . $e->getMessage();
    exit(1);
}
