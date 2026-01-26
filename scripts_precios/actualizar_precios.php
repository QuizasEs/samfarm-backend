<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_excel'])) {
    
    require_once __DIR__ . "/preciosActualizarModel.php";

    $archivo_tmp = $_FILES['archivo_excel']['tmp_name'];
    $nombre_original = $_FILES['archivo_excel']['name'];
    $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);

    if ($extension != 'csv') {
        echo '<div class="notification is-danger is-light">
                <strong>¡Ocurrió un error!</strong><br>
                El archivo debe tener extensión .csv
              </div>';
        return;
    }

    $actualizador = new preciosActualizarModel();
    $resultados = $actualizador->actualizarPreciosDesdeCSV($archivo_tmp);

    if (isset($resultados['error'])) {
        echo '<div class="notification is-danger is-light">
                <strong>¡Ocurrió un error!</strong><br>
                ' . $resultados['error'] . '
              </div>';
        return;
    }

    $logMultiplesResultadosFile = __DIR__ . '/multiples_resultados.txt';
    $logNoEncontradosFile = __DIR__ . '/no_encontrados.txt';

    // Limpiar logs anteriores
    if (file_exists($logMultiplesResultadosFile)) unlink($logMultiplesResultadosFile);
    if (file_exists($logNoEncontradosFile)) unlink($logNoEncontradosFile);

    if (!empty($resultados['multiples'])) {
        file_put_contents($logMultiplesResultadosFile, implode(PHP_EOL, $resultados['multiples']));
    }

    if (!empty($resultados['no_encontrados'])) {
        file_put_contents($logNoEncontradosFile, implode(PHP_EOL, $resultados['no_encontrados']));
    }

    // --- Mensaje de resultados ---
    echo '<div class="notification is-success is-light">
            <strong>¡Proceso completado!</strong><br>
            - Se actualizaron <strong>' . $resultados['actualizados'] . '</strong> medicamentos.<br>';
    
    if (file_exists($logMultiplesResultadosFile) && filesize($logMultiplesResultadosFile) > 0) {
        $urlMultiples = defined('SERVER_URL') ? SERVER_URL . 'scripts_precios/multiples_resultados.txt' : 'scripts_precios/multiples_resultados.txt';
        echo '- Se encontraron múltiples resultados para algunos medicamentos. <a href="' . $urlMultiples . '" download>Descargar log de múltiples resultados</a><br>';
    }
    if (file_exists($logNoEncontradosFile) && filesize($logNoEncontradosFile) > 0) {
        $urlNoEncontrados = defined('SERVER_URL') ? SERVER_URL . 'scripts_precios/no_encontrados.txt' : 'scripts_precios/no_encontrados.txt';
        echo '- No se encontraron algunos medicamentos. <a href="' . $urlNoEncontrados . '" download>Descargar log de no encontrados</a><br>';
    }
    
    echo '</div>';
}
?>
