<?php

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/models/siatModel.php';

$suId = isset($argv[1]) ? (int) $argv[1] : 1;

try {
    $res = siatModel::sincronizarCatalogos($suId);

    if ($res === false) {
        $mensaje = date('Y-m-d H:i:s') . " - SIAT: fallo la sincronizacion de catalogos para su_id={$suId}. Verificar CUIS y credenciales.\n";
        echo $mensaje;
        exit(1);
    }

    $cufd = siatModel::obtenerCUFD($suId);

    $mensaje = date('Y-m-d H:i:s') . " - SIAT: catalogos sincronizados (su_id={$suId}): " . json_encode($res);
    $mensaje .= " | CUFD: " . ($cufd ? "OK" : "FALLO") . "\n";
    echo $mensaje;
    exit(0);

} catch (Exception $e) {
    $mensaje = date('Y-m-d H:i:s') . " - SIAT ERROR: " . $e->getMessage() . "\n";
    echo $mensaje;
    exit(1);
}
