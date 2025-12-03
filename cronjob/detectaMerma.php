<?php

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/models/mermaModel.php';

try {
    $lotes_detectados = mermaModel::detectar_lotes_caducados_model();
    
    $cantidad = count($lotes_detectados);
    $mensaje = date('Y-m-d H:i:s') . " - Detección de lotes caducados ejecutada. Lotes detectados: " . $cantidad . "\n";
    
    if ($cantidad > 0) {
        $mensaje .= "  Lotes detectados:\n";
        foreach ($lotes_detectados as $lote) {
            $mensaje .= "    - {$lote['med_nombre_quimico']} (Lote: {$lote['lm_numero_lote']}) - Vencimiento: {$lote['lm_fecha_vencimiento']} - Sucursal: {$lote['su_nombre']}\n";
        }
    }
    
    $log_file = dirname(__DIR__) . '/logs/detectaMerma.log';
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    file_put_contents($log_file, $mensaje, FILE_APPEND);
    
    echo "OK - Lotes caducados detectados: " . $cantidad;
    exit(0);
    
} catch (Exception $e) {
    $error_mensaje = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    
    $log_file = dirname(__DIR__) . '/logs/detectaMerma.log';
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    
    file_put_contents($log_file, $error_mensaje, FILE_APPEND);
    
    echo "ERROR - " . $e->getMessage();
    exit(1);
}
