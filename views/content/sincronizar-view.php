<?php
require_once "./models/mainModel.php";
$ins_med = new mainModel();
$db = $ins_med->conectar();

echo "=== SINCRONIZACIÓN DE INVENTARIOS ===\n\n";

// 1. Obtener todos los medicamentos con lotes activos pero sin inventario
$sql = "
    SELECT DISTINCT m.med_id, m.med_nombre_quimico, lm.su_id
    FROM medicamento m
    INNER JOIN lote_medicamento lm ON lm.med_id = m.med_id
    LEFT JOIN inventarios i ON i.med_id = m.med_id AND i.su_id = lm.su_id
    WHERE lm.lm_estado = 'activo'
    AND i.inv_id IS NULL
";

$stmt = $db->prepare($sql);
$stmt->execute();
$faltantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Medicamentos sin inventario: " . count($faltantes) . "\n\n";

foreach ($faltantes as $item) {
    $med_id = $item['med_id'];
    $su_id = $item['su_id'];
    $nombre = $item['med_nombre_quimico'];
    
    echo "Procesando: {$nombre} (med_id={$med_id}, su_id={$su_id})...\n";
    
    // Sumar unidades de lotes
    $sum_stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(lm_cant_actual_unidades), 0) as total_unidades,
            lm_cant_blister,
            lm_cant_unidad
        FROM lote_medicamento
        WHERE med_id = :med_id AND su_id = :su_id AND lm_estado = 'activo'
        GROUP BY lm_cant_blister, lm_cant_unidad
        LIMIT 1
    ");
    $sum_stmt->execute([':med_id' => $med_id, ':su_id' => $su_id]);
    $totales = $sum_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($totales) {
        $total_unidades = (int)$totales['total_unidades'];
        $blister = max(1, (int)$totales['lm_cant_blister']);
        $unidad = max(1, (int)$totales['lm_cant_unidad']);
        $unidades_por_caja = $blister * $unidad;
        $total_cajas = (int)floor($total_unidades / $unidades_por_caja);
        
        // Insertar en inventarios
        $ins_stmt = $db->prepare("
            INSERT INTO inventarios 
            (med_id, su_id, inv_total_cajas, inv_total_unidades, inv_total_valorado, inv_creado_en, inv_actualizado_en)
            VALUES 
            (:med_id, :su_id, :cajas, :unidades, 0, NOW(), NOW())
        ");
        
        $ins_stmt->execute([
            ':med_id' => $med_id,
            ':su_id' => $su_id,
            ':cajas' => $total_cajas,
            ':unidades' => $total_unidades
        ]);
        
        echo "  ✓ Creado: {$total_unidades} unidades ({$total_cajas} cajas)\n\n";
    } else {
        echo "  ✗ No se encontraron lotes activos\n\n";
    }
}

echo "\n=== SINCRONIZACIÓN COMPLETADA ===\n";