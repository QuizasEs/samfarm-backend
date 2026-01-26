<?php
require_once __DIR__ . "/preciosActualizarModel.php";

// Crear una instancia del modelo
$actualizador = new preciosActualizarModel();

// Ruta al archivo CSV de prueba
$archivo_csv = __DIR__ . '/Libro1.csv';

// Probar la actualización
$resultados = $actualizador->actualizarPreciosDesdeCSV($archivo_csv);

// Mostrar resultados
echo "<h2>Resultados de la actualización de precios:</h2>";
echo "<p>Medicamentos actualizados: " . $resultados['actualizados'] . "</p>";

if (!empty($resultados['multiples'])) {
    echo "<h3>Medicamentos con múltiples resultados:</h3>";
    foreach ($resultados['multiples'] as $med) {
        echo "<p>" . htmlspecialchars($med) . "</p>";
    }
}

if (!empty($resultados['no_encontrados'])) {
    echo "<h3>Medicamentos no encontrados:</h3>";
    foreach ($resultados['no_encontrados'] as $med) {
        echo "<p>" . htmlspecialchars($med) . "</p>";
    }
}

if (isset($resultados['error'])) {
    echo "<p style='color: red;'>Error: " . $resultados['error'] . "</p>";
}

// Mostrar algunas tablas para verificar
echo "<h3>Tablas en la base de datos:</h3>";
$db = $actualizador->conectar();
$stmt = $db->query("SHOW TABLES");
echo "<ul>";
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Mostrar algunos registros de la tabla lote_medicamento para verificar
echo "<h3>Registros en la tabla lote_medicamento:</h3>";
$stmt = $db->query("SELECT lm_id, med_id, lm_numero_lote, lm_precio_venta, lm_estado FROM lote_medicamento LIMIT 10");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Med ID</th><th>Número Lote</th><th>Precio</th><th>Estado</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $row['lm_id'] . "</td>";
    echo "<td>" . $row['med_id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['lm_numero_lote']) . "</td>";
    echo "<td>" . $row['lm_precio_venta'] . "</td>";
    echo "<td>" . $row['lm_estado'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Mostrar algunos registros de la tabla medicamento para verificar
echo "<h3>Registros en la tabla medicamento:</h3>";
$stmt = $db->query("SELECT med_id, med_nombre_quimico FROM medicamento LIMIT 10");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Nombre Químico</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>" . $row['med_id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['med_nombre_quimico']) . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
