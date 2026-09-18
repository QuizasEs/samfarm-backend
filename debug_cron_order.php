<?php
$peticionAjax = true;
require 'C:\xampp\htdocs\samfarm-backend\config/SERVER.php';
require 'C:\xampp\htdocs\samfarm-backend\config/siat.php';
require 'C:\xampp\htdocs\samfarm-backend\models/mainModel.php';
require 'C:\xampp\htdocs\samfarm-backend\models/siatModel.php';

$suId = 1;
echo "=== REPRODUCCION ORDEN CRON: CUIS -> sincronizarCatalogos -> CUFD ===\n";
try {
    siatModel::obtenerCUIS($suId);
    echo "obtenerCUIS: OK\n";
} catch (Exception $e) {
    echo "obtenerCUIS EXC: ".$e->getMessage()."\n";
}

echo "\n--- sincronizarCatalogos ---\n";
$res = siatModel::sincronizarCatalogos($suId);
var_dump($res);

echo "\n--- obtenerCUFD ---\n";
try {
    $c = siatModel::obtenerCUFD($suId);
    echo "obtenerCUFD: ".($c?'OK':'FALSE')."\n";
} catch (Exception $e) {
    echo "obtenerCUFD EXC: ".$e->getMessage()."\n";
}
echo "\n=== FIN ===\n";
