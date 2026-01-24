<?php
/**
 * Script para registro masivo de inventario y lotes con precios fijos
 * 
 * Este script permite registrar stock para cada medicamento en el catálogo
 * con precios de compra y venta fijos predefinidos.
 * 
 * IMPORTANTE: Este script debe ejecutarse con precaución y preferiblemente en un entorno de pruebas primero.
 */

// Configuración de conexión
$host = '127.0.0.1';
$dbname = 'samfarm_db';
$username = 'root'; // Cambia según tu configuración
$password = '';     // Cambia según tu configuración

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conexión exitosa a la base de datos\n";
    
    // Configuración del script
    $stock_por_medicamento = 2000;
    $sucursal_id = 1; // Cambia según la sucursal donde deseas registrar el stock
    $usuario_id = 1;  // ID del usuario que realizará la operación (admin)
    $precio_compra_base = 10.00;  // Precio de compra base
    $precio_venta_base = 15.00;   // Precio de venta base
    $unidades_por_caja_base = 10; // Unidades por caja base
    $meses_vencimiento_base = 24; // Meses de vencimiento base
    
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
            $numero_lote = "BULK-" . date('Ymd') . "-" . $med_id . "-" . rand(1000, 9999);
            
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
                ':pr_id' => $laboratorio_id, // Usamos la ID del laboratorio como proveedor
                ':pr_id_compra' => null, // No hay compra real
                ':lm_numero_lote' => $numero_lote,
                ':lm_cant_caja' => $cajas,
                ':lm_cant_blister' => 1, // Asumimos 1 blíster por caja
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
            
            // 3. Registrar movimiento de inventario
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
                ':mi_referencia_tipo' => 'bulk_import',
                ':mi_referencia_id' => $lote_id,
                ':mi_motivo' => "Ingreso masivo de stock inicial: $stock_por_medicamento unidades - Precio Compra: Bs. $precio_compra_base - Precio Venta: Bs. $precio_venta_base"
            ]);
            
            // 4. Registrar historial del lote
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
                ':hl_descripcion' => "Lote creado por importación masiva con $total_unidades unidades - Precio Compra: Bs. $precio_compra_base - Precio Venta: Bs. $precio_venta_base",
                ':hl_fecha' => $fecha_ingreso
            ]);
            
            $total_registros++;
            echo "  ✓ Lote creado: $numero_lote con $total_unidades unidades\n";
            echo "  ✓ Precio Compra: Bs. $precio_compra_base | Precio Venta: Bs. $precio_venta_base\n";
            echo "  ✓ Unidades por caja: $unidades_por_caja_base | Cajas: $cajas\n";
            echo "  ✓ Vencimiento: $fecha_vencimiento\n";
            echo "  ✓ Inventario actualizado\n";
            echo "  ✓ Movimiento registrado\n";
            echo "  ✓ Historial creado\n\n";
            
        } catch (Exception $e) {
            $errores[] = "Error con medicamento {$medicamento['med_nombre_quimico']} (ID: {$medicamento['med_id']}): " . $e->getMessage();
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
        echo "Stock total registrado: " . (count($medicamentos) * $stock_por_medicamento) . " unidades\n";
        echo "========================================\n";
    } else {
        $pdo->rollBack();
        echo "========================================\n";
        echo "PROCESO FINALIZADO CON ERRORES\n";
        echo "========================================\n";
        echo "Medicamentos procesados exitosamente: " . $total_registros . "\n";
        echo "Errores encontrados: " . count($errores) . "\n";
        echo "\nDetalles de errores:\n";
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

<!-- borrar el script de arriva una vez usado -->

<div class="404-main" style="background: linear-gradient(135deg, #13386c 0%, #1b4681 50%, #293241 100%);">
        <div class="error-404-page error-404-design active">
            <div class="error-404-heart-monitor">
                <div class="error-404-ecg-line">
                    <svg viewBox="0 0 400 100">
                        <path class="error-404-ecg-path" d="M 0 50 L 50 50 L 60 30 L 70 70 L 80 20 L 90 50 L 400 50" />
                    </svg>
                </div>
                <div class="error-404-code">404</div>
            </div>
            <h2 class="error-404-title">Sin señales vitales</h2>
            <p class="error-404-text">Esta página no responde a nuestro diagnóstico</p>
            <a href="<?php echo SERVER_URL;?>dashboard/" class="error-404-btn-home">Volver al inicio</a>
        </div>
    </div>