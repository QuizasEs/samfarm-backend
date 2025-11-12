<?php
// debug_compra.php - Archivo temporal para depuración

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_compra.log');

echo "=== DEBUG COMPRA ===\n\n";

echo "POST recibido:\n";
print_r($_POST);

echo "\n\n=== LOTES JSON ===\n";
$lotes_json = $_POST['lotes_json'] ?? '[]';
echo "Raw: " . $lotes_json . "\n\n";

$lotes = json_decode($lotes_json, true);
echo "Decodificado:\n";
print_r($lotes);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "\nERROR JSON: " . json_last_error_msg() . "\n";
}

echo "\n\n=== TOTALES JSON ===\n";
$totales_json = $_POST['totales_json'] ?? '{}';
echo "Raw: " . $totales_json . "\n\n";

$totales = json_decode($totales_json, true);
echo "Decodificado:\n";
print_r($totales);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "\nERROR JSON: " . json_last_error_msg() . "\n";
}

echo "\n\n=== ANÁLISIS DE LOTES ===\n";
if (is_array($lotes)) {
    foreach ($lotes as $index => $lote) {
        echo "Lote #{$index}:\n";
        echo "  - activar_lote: " . var_export($lote['activar_lote'] ?? 'NO EXISTE', true) . "\n";
        echo "  - Tipo: " . gettype($lote['activar_lote'] ?? null) . "\n";
        
        $activar = isset($lote['activar_lote']) && ($lote['activar_lote'] == 1 || $lote['activar_lote'] === true);
        echo "  - Resultado conversión: " . var_export($activar, true) . "\n";
        echo "  - Estado que se asignará: " . ($activar ? 'activo' : 'en_espera') . "\n\n";
    }
}