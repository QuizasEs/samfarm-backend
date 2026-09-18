<?php

$peticionAjax = true; // hace que mainModel use la rama "../config/..." (correcta desde cronjob/)

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/models/siatModel.php';

// Ejecutable por terminal (php cronjob/siat_sincronizar.php 1) o por navegador
// (http://localhost/samfarm-backend/cronjob/siat_sincronizar.php?su_id=1  |  ?all=1)
$suId = null;
if (isset($argv[1])) {
    $suId = (int) $argv[1];
} elseif (isset($_GET['su_id'])) {
    $suId = (int) $_GET['su_id'];
}

if (isset($_GET['all'])) {
    $procesarTodos = true;            // navegador: procesa todas las sucursales
} elseif ($suId !== null) {
    $procesarTodos = false;           // sucursal concreta (CLI o ?su_id=)
} else {
    $procesarTodos = false;           // terminal sin argumento: sucursal 1 (comportamiento original)
    $suId = 1;
}

try {
    $filas = $procesarTodos
        ? siatModel::listarSucursales()
        : [(int) $suId];

    foreach ($filas as $suId) {
        $suId = (int) $suId;

        // Etapa I: obtener/refrescar CUIS (usa el token delegado de clienteSOAP)
        try {
            siatModel::obtenerCUIS($suId);
            
        } catch (Exception $e) {
            error_log("SIAT obtenerCUIS su_id={$suId}: " . $e->getMessage());
        }

        $res = siatModel::sincronizarCatalogos($suId);
        if ($res === false) {
            echo date('Y-m-d H:i:s') . " - SIAT su_id={$suId}: fallo la sincronizacion de catalogos. Verificar CUIS y credenciales.\n";
            continue;
        }

        $cufd = siatModel::obtenerCUFD($suId);
        echo date('Y-m-d H:i:s') . " - SIAT su_id={$suId}: catalogos=" . json_encode($res)
            . " | CUFD: " . ($cufd ? "OK" : "FALLO") . "\n";
    }

    exit(0);
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - SIAT ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
