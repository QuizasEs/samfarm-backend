<?php
$pdo = new PDO("mysql:host=localhost;dbname=samfarm_db;charset=utf8", "root", "");
$r = $pdo->query("SELECT med_id, med_nombre_quimico, med_codigo_sin FROM medicamento WHERE med_id = 77213")->fetch(PDO::FETCH_ASSOC);
echo "med_id=77213:\n";
print_r($r);

echo "\n=== Medicamentos with med_codigo_sin NOT NULL and not empty ===\n";
$r2 = $pdo->query("SELECT med_id, med_nombre_quimico, med_codigo_sin FROM medicamento WHERE med_codigo_sin IS NOT NULL AND med_codigo_sin != '' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
print_r($r2);

echo "\n=== Check column name ===\n";
$r3 = $pdo->query("SHOW COLUMNS FROM medicamento LIKE '%sin%'")->fetchAll(PDO::FETCH_ASSOC);
print_r($r3);
