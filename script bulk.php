<?php
/**
 * Script para registro masivo de inventario y lotes con precios fijos
 * VERSIÓN CORREGIDA - Sin error 22001
 */

// Configuración de conexión
$host = 'localhost';
$dbname = 'samfarm_db';
$username = 'web_app_user';
$password = '2025sqlpass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexión exitosa a la base de datos\n";
    
    // Configuración del script
    $stock_por_medicamento = 2000;
    $sucursal_id = 4;
    $usuario_id = 1;
    $precio_compra_base = 998.00;
    $precio_venta_base = 999.00;
    $unidades_por_caja_base = 10;
    $meses_vencimiento_base = 24;
    
    // Obtener todos los medicamentos del catálogo
    $stmt_medicamentos = $pdo->query("
        SELECT med_id, med_nombre_quimico, med_principio_activo, la_id
        FROM medicamento 
        WHERE med_creado_en IS NOT NULL
        ORDER BY med_id
    ");
    
    $medicamentos = $stmt_medicamentos->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($medicamentos)) {
        echo "No se encontraron medicamentos en el catálogo.\n";
        exit;
    }
    
    echo "Se encontraron " . count($medicamentos) . " medicamentos para procesar.\n";
    echo "Se registrarán " . $stock_por_medicamento . " unidades para cada uno.\n\n";
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    $total_registros = 0;
    $errores = [];
    
    foreach ($medicamentos as $medicamento) {
        try {
            $med_id = $medicamento['med_id'];
            $med_nombre = $medicamento['med_nombre_quimico'];
            $laboratorio_id = $medicamento['la_id'] ?? null;
            
            echo "Procesando medicamento: $med_nombre (ID: $med_id)\n";
            
            // Generar número de lote único
            $numero_lote = "MED-" . str_pad($med_id, 7, '0', STR_PAD_LEFT);
            
            // Calcular cantidades
            $cajas = ceil($stock_por_medicamento / $unidades_por_caja_base);
            $total_unidades = $cajas * $unidades_por_caja_base;
            
            // Fechas
            $fecha_ingreso = date('Y-m-d H:i:s');
            $fecha_vencimiento = date('Y-m-d', strtotime("+$meses_vencimiento_base months"));
            
            // 1. Crear registro en lote_medicamento
            $sql_lote = "
                INSERT INTO lote_medicamento (
                    med_id, su_id, pr_id, pr_id_compra, lm_numero_lote,
                    lm_cant_caja, lm_cant_blister, lm_cant_unidad,
                    lm_cant_actual_cajas, lm_cant_actual_unidades,
                    lm_precio_compra, lm_precio_venta, lm_fecha_ingreso,
                    lm_fecha_vencimiento, lm_estado, lm_creado_en, lm_actualizado_en
                ) VALUES (
                    :med_id, :su_id, :pr_id, :pr_id_compra, :lm_numero_lote,
                    :lm_cant_caja, :lm_cant_blister, :lm_cant_unidad,
                    :lm_cant_actual_cajas, :lm_cant_actual_unidades,
                    :lm_precio_compra, :lm_precio_venta, :lm_fecha_ingreso,
                    :lm_fecha_vencimiento, :lm_estado, :lm_creado_en, :lm_actualizado_en
                )
            ";
            
            $stmt_lote = $pdo->prepare($sql_lote);
            $stmt_lote->execute([
                ':med_id' => $med_id,
                ':su_id' => $sucursal_id,
                ':pr_id' => $laboratorio_id,
                ':pr_id_compra' => null,
                ':lm_numero_lote' => $numero_lote,
                ':lm_cant_caja' => $cajas,
                ':lm_cant_blister' => 1,
                ':lm_cant_unidad' => $unidades_por_caja_base,
                ':lm_cant_actual_cajas' => $cajas,
                ':lm_cant_actual_unidades' => $total_unidades,
                ':lm_precio_compra' => $precio_compra_base,
                ':lm_precio_venta' => $precio_venta_base,
                ':lm_fecha_ingreso' => $fecha_ingreso,
                ':lm_fecha_vencimiento' => $fecha_vencimiento,
                ':lm_estado' => 'activo',
                ':lm_creado_en' => $fecha_ingreso,
                ':lm_actualizado_en' => $fecha_ingreso
            ]);
            
            $lote_id = $pdo->lastInsertId();
            
            // 2. Actualizar o crear registro en inventarios
            $sql_inventario = "
                INSERT INTO inventarios (
                    med_id, su_id, inv_total_cajas, inv_total_unidades, 
                    inv_total_valorado, inv_creado_en, inv_actualizado_en
                ) VALUES (
                    :med_id, :su_id, :inv_total_cajas, :inv_total_unidades,
                    :inv_total_valorado, :inv_creado_en, :inv_actualizado_en
                )
                ON DUPLICATE KEY UPDATE
                    inv_total_cajas = inv_total_cajas + VALUES(inv_total_cajas),
                    inv_total_unidades = inv_total_unidades + VALUES(inv_total_unidades),
                    inv_total_valorado = inv_total_valorado + VALUES(inv_total_valorado),
                    inv_actualizado_en = VALUES(inv_actualizado_en)
            ";
            
            $valorado = $total_unidades * $precio_compra_base;
            
            $stmt_inventario = $pdo->prepare($sql_inventario);
            $stmt_inventario->execute([
                ':med_id' => $med_id,
                ':su_id' => $sucursal_id,
                ':inv_total_cajas' => $cajas,
                ':inv_total_unidades' => $total_unidades,
                ':inv_total_valorado' => $valorado,
                ':inv_creado_en' => $fecha_ingreso,
                ':inv_actualizado_en' => $fecha_ingreso
            ]);
            
            // 3. Registrar movimiento de inventario (CORREGIDO)
            $sql_movimiento = "
                INSERT INTO movimiento_inventario (
                    med_id, lm_id, su_id, us_id, mi_tipo, mi_cantidad,
                    mi_unidad, mi_referencia_tipo, mi_referencia_id, mi_motivo
                ) VALUES (
                    :med_id, :lm_id, :su_id, :us_id, :mi_tipo, :mi_cantidad,
                    :mi_unidad, :mi_referencia_tipo, :mi_referencia_id, :mi_motivo
                )
            ";
            
            $stmt_movimiento = $pdo->prepare($sql_movimiento);
            $stmt_movimiento->execute([
                ':med_id' => $med_id,
                ':lm_id' => $lote_id,
                ':su_id' => $sucursal_id,
                ':us_id' => $usuario_id,
                ':mi_tipo' => 'entrada',
                ':mi_cantidad' => $total_unidades,
                ':mi_unidad' => 'unidad',
                ':mi_referencia_tipo' => 'import_masivo', // ACORTADO
                ':mi_referencia_id' => $lote_id,
                ':mi_motivo' => "Stock inicial: $total_unidades uds" // ACORTADO
            ]);
            
            // 4. Registrar historial del lote (CORREGIDO)
            $sql_historial = "
                INSERT INTO historial_lote (
                    lm_id, us_id, hl_accion, hl_descripcion, hl_fecha
                ) VALUES (
                    :lm_id, :us_id, :hl_accion, :hl_descripcion, :hl_fecha
                )
            ";
            
            $stmt_historial = $pdo->prepare($sql_historial);
            $stmt_historial->execute([
                ':lm_id' => $lote_id,
                ':us_id' => $usuario_id,
                ':hl_accion' => 'creacion',
                ':hl_descripcion' => "Import: $total_unidades uds", // ACORTADO
                ':hl_fecha' => $fecha_ingreso
            ]);
            
            $total_registros++;
            echo "  ✓ Lote creado: $numero_lote con $total_unidades unidades\n";
            echo "  ✓ Precio Compra: Bs. $precio_compra_base | Precio Venta: Bs. $precio_venta_base\n";
            echo "  ✓ Inventario actualizado\n\n";
            
        } catch (Exception $e) {
            $errores[] = "Error medicamento ID {$medicamento['med_id']}: " . $e->getMessage();
            echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        }
    }
    
    // Si no hubo errores, confirmar transacción
    if (empty($errores)) {
        $pdo->commit();
        echo "========================================\n";
        echo "PROCESO COMPLETADO EXITOSAMENTE\n";
        echo "========================================\n";
        echo "Medicamentos procesados: " . count($medicamentos) . "\n";
        echo "Registros creados: $total_registros\n";
        echo "Stock total: " . (count($medicamentos) * $stock_por_medicamento) . " unidades\n";
        echo "========================================\n";
    } else {
        $pdo->rollBack();
        echo "========================================\n";
        echo "PROCESO FINALIZADO CON ERRORES\n";
        echo "========================================\n";
        echo "Exitosos: $total_registros\n";
        echo "Errores: " . count($errores) . "\n\n";
        foreach ($errores as $error) {
            echo "- $error\n";
        }
        echo "========================================\n";
    }
    
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    exit;
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
    exit;
}
?>