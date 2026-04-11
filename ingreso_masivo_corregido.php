<?php
/**
 * INGRESO MASIVO CORREGIDO
 * Versión corregida del ingreso masivo que crea lotes con valores CORRECTOS
 */

if (!isset($peticionAjax)) {
    $peticionAjax = false;
}

require_once './models/ingresoMasivoModel.php';
require_once './models/mainModel.php';
require_once './libs/excel/ExcelReader.php';

header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel']) && isset($_POST['sucursal_id'])) {
    $sucursal_id = (int)$_POST['sucursal_id'];
    $us_id = isset($_SESSION['id_smp']) ? (int)$_SESSION['id_smp'] : 1;

    $fileExtension = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExtension, ['xlsx', 'xls'])) {
        echo '<div class="alert alert-error">El archivo debe ser un Excel (.xlsx o .xls)</div>';
        exit;
    }

    try {
        $datosExcel = leerExcelCorregido($_FILES['archivo_excel']['tmp_name'], $fileExtension);

        if (empty($datosExcel)) {
            echo '<div class="alert alert-error">El archivo Excel está vacío o no tiene datos.</div>';
            exit;
        }

        // Procesar cada fila y crear lotes CORRECTOS
        $resultados = procesarFilasIngresoCorregido($datosExcel, $sucursal_id, $us_id);

        echo '<div class="alert alert-success">';
        echo "Ingreso masivo corregido completado: {$resultados['exitosos']} registros creados exitosamente, {$resultados['errores']} con errores.";
        echo '</div>';

        if (!empty($resultados['detalles'])) {
            echo '<h3>Detalles:</h3><ul>';
            foreach ($resultados['detalles'] as $detalle) {
                echo '<li>[' . strtoupper($detalle['tipo']) . '] ' . $detalle['mensaje'] . '</li>';
            }
            echo '</ul>';
        }

    } catch (Exception $e) {
        echo '<div class="alert alert-error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
} else {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Ingreso Masivo CORREGIDO</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .container { max-width: 600px; margin: 0 auto; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="number"], input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            input[type="submit"] { background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
            input[type="submit"]:hover { background-color: #218838; }
            .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
            .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
            h3 { color: #333; }
            ul { margin-top: 10px; }
            li { margin-bottom: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🛠️ INGRESO MASIVO CORREGIDO</h1>
            <p><strong>IMPORTANTE:</strong> Este módulo crea los lotes con los valores CORRECTOS desde el inicio.</p>

            <div class="alert alert-warning">
                <strong>¿Cuándo usar este módulo?</strong>
                <ul>
                    <li>Si el ingreso masivo original falló o no creó lotes</li>
                    <li>Si necesitas crear lotes con los valores correctos directamente</li>
                    <li>Si los lotes existentes tienen valores incorrectos y quieres recrearlos</li>
                </ul>
            </div>

            <p><strong>Valores que se crearán CORRECTAMENTE:</strong></p>
            <ul>
                <li><strong>lm_cant_unidad</strong> = unidades por caja (del Excel columna 3)</li>
                <li><strong>lm_cant_caja</strong> = total unidades ÷ unidades por caja (redondeado hacia arriba)</li>
                <li><strong>lm_cant_blister</strong> = 1 (valor correcto)</li>
                <li><strong>Estado inicial</strong> = 'activo' (lotes listos para usar)</li>
            </ul>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="sucursal_id">Sucursal ID:</label>
                    <input type="number" id="sucursal_id" name="sucursal_id" required min="1" placeholder="Ej: 1">
                </div>

                <div class="form-group">
                    <label for="archivo_excel">Archivo Excel:</label>
                    <input type="file" id="archivo_excel" name="archivo_excel" accept=".xlsx,.xls" required>
                </div>

                <div class="form-group">
                    <input type="submit" value="🚀 Procesar Ingreso Masivo Corregido">
                </div>
            </form>

            <p><em>Nota: Este módulo crea lotes completamente nuevos con valores correctos.</em></p>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Leer Excel para ingreso corregido
 */
function leerExcelCorregido($filePath, $extension) {
    global $peticionAjax;
    if ($peticionAjax) {
        require_once __DIR__ . '/../libs/excel/ExcelReader.php';
    } else {
        require_once __DIR__ . '/libs/excel/ExcelReader.php';
    }

    if (!in_array($extension, ['xlsx'])) {
        throw new Exception('Solo se admiten archivos .xlsx. Para archivos .xls, por favor guarde como .xlsx');
    }

    try {
        $excel = new ExcelReader($filePath);
        $excel->read();
        $datos = $excel->toAssociativeArray();

        return $datos;
    } catch (Exception $e) {
        throw new Exception('Error al leer el archivo Excel: ' . $e->getMessage());
    }
}

/**
 * Procesar filas para crear lotes CORRECTOS (como debería funcionar el ingreso masivo)
 */
function procesarFilasIngresoCorregido($datosExcel, $sucursal_id, $us_id) {
    $resultados = [
        'exitosos' => 0,
        'errores' => 0,
        'detalles' => []
    ];

    $categoria_default = 10; // ID de categoría por defecto

    foreach ($datosExcel as $index => $fila) {
        try {
            $filaLimpia = limpiarFilaExcelParaCorreccion($fila);

            if (empty($filaLimpia['descripcion'])) {
                $resultados['errores']++;
                $resultados['detalles'][] = [
                    'tipo' => 'error',
                    'mensaje' => "Fila " . ($index + 2) . ": Descripción vacía - omitida"
                ];
                continue;
            }

            // 1. OBTENER/CREAR MEDICAMENTO
            $med_id = ingresoMasivoModel::obtener_medicamento_por_nombre_model($filaLimpia['descripcion']);

            if (!$med_id) {
                // Crear medicamento si no existe
                $datosMedicamento = [
                    'Nombre' => $filaLimpia['descripcion'],
                    'Principio' => 'N/A',
                    'Accion' => null,
                    'Presentacion' => null,
                    'Uso' => $categoria_default,
                    'Forma' => $categoria_default,
                    'Via' => $categoria_default,
                    'Proveedor' => !empty($filaLimpia['proveedor_id']) ? $filaLimpia['proveedor_id'] : null,
                    'Descripcion' => 'Creado desde ingreso masivo corregido',
                    'CodigoBarras' => null
                ];

                $med_id = ingresoMasivoModel::crear_medicamento_model($datosMedicamento);

                if (!$med_id) {
                    $resultados['errores']++;
                    $resultados['detalles'][] = [
                        'tipo' => 'error',
                        'mensaje' => "Fila " . ($index + 2) . ": Error al crear medicamento '{$filaLimpia['descripcion']}'"
                    ];
                    continue;
                }
            }

            // 2. VALIDAR PROVEEDOR
            $pr_id = !empty($filaLimpia['proveedor_id']) ? (int)$filaLimpia['proveedor_id'] : null;
            if ($pr_id !== null && !ingresoMasivoModel::verificar_proveedor_model($pr_id)) {
                $pr_id = null; // Usar null si el proveedor no existe
            }

            // 3. CALCULAR VALORES CORRECTOS
            $total_unidades = (int)$filaLimpia['cantidad'];
            $cant_por_caja = (int)$filaLimpia['cantidad_caja'];

            if ($total_unidades <= 0) {
                $resultados['exitosos']++; // Contar como exitoso - medicamento registrado sin stock
                $resultados['detalles'][] = [
                    'tipo' => 'success',
                    'mensaje' => "Fila " . ($index + 2) . ": OK (sin stock) - '{$filaLimpia['descripcion']}' registrado"
                ];
                continue;
            }

            // VALORES CORRECTOS (esto es lo que el ingreso masivo original debería haber hecho)
            $lm_cant_unidad = $cant_por_caja; // Unidades por caja del Excel
            $lm_cant_blister = 1; // Siempre 1 según el usuario
            $lm_cant_caja = ($cant_por_caja > 0) ? ceil($total_unidades / $cant_por_caja) : $total_unidades;
            $lm_total_unidades = $lm_cant_caja * $lm_cant_blister * $lm_cant_unidad;

            $precio_compra = !empty($filaLimpia['precio_compra']) ? (float)$filaLimpia['precio_compra'] : 0;
            $precio_venta = !empty($filaLimpia['precio_venta']) ? (float)$filaLimpia['precio_venta'] : 0;

            // Fecha de vencimiento
            $fecha_vencimiento = null;
            if (!empty($filaLimpia['fecha_vencimiento'])) {
                $fecha_vencimiento = $filaLimpia['fecha_vencimiento'];
            }

            // Número de lote único
            $numero_lote = 'LOTE-CORREGIDO-' . date('YmdHis') . '-' . ($index + 1);

            // 4. CREAR LOTE CON VALORES CORRECTOS
            $datosLote = [
                'med_id' => $med_id,
                'su_id' => $sucursal_id,
                'pr_id' => $pr_id,
                'lm_numero_lote' => $numero_lote,
                'lm_cant_caja' => $lm_cant_caja,
                'lm_cant_blister' => $lm_cant_blister,
                'lm_cant_unidad' => $lm_cant_unidad,
                'lm_cant_actual_cajas' => $lm_cant_caja, // Todas las cajas disponibles
                'lm_cant_actual_unidades' => $lm_total_unidades, // Todas las unidades disponibles
                'lm_precio_compra' => $precio_compra,
                'lm_precio_venta' => $precio_venta,
                'lm_fecha_vencimiento' => $fecha_vencimiento,
                'lm_estado' => 'activo' // Lotés activos listos para usar
            ];

            $lm_id = ingresoMasivoModel::crear_lote_model($datosLote);

            if (!$lm_id) {
                $resultados['errores']++;
                $resultados['detalles'][] = [
                    'tipo' => 'error',
                    'mensaje' => "Fila " . ($index + 2) . ": Error al crear lote para '{$filaLimpia['descripcion']}'"
                ];
                continue;
            }

            // 5. ACTUALIZAR INVENTARIO
            $total_valorado = $lm_total_unidades * $precio_venta;
            $datosInventario = [
                'med_id' => $med_id,
                'su_id' => $sucursal_id,
                'inv_total_cajas' => $lm_cant_caja,
                'inv_total_unidades' => $lm_total_unidades,
                'inv_total_valorado' => $total_valorado
            ];

            ingresoMasivoModel::actualizar_inventario_model($datosInventario);

            // 6. REGISTRAR MOVIMIENTO DE INVENTARIO
            $datosMovimiento = [
                'lm_id' => $lm_id,
                'med_id' => $med_id,
                'su_id' => $sucursal_id,
                'us_id' => $us_id,
                'mi_tipo' => 'entrada',
                'mi_cantidad' => $lm_total_unidades,
                'mi_unidad' => 'unidad',
                'mi_referencia_tipo' => 'ingreso_masivo_corregido',
                'mi_referencia_id' => $lm_id,
                'mi_motivo' => 'Ingreso masivo corregido desde Excel'
            ];

            ingresoMasivoModel::registrar_movimiento_model($datosMovimiento);

            $resultados['exitosos']++;
            $resultados['detalles'][] = [
                'tipo' => 'success',
                'mensaje' => "Fila " . ($index + 2) . ": ✅ Lote creado correctamente - '{$filaLimpia['descripcion']}' (Unidades/caja: $lm_cant_unidad, Cajas: $lm_cant_caja, Total unidades: $lm_total_unidades)"
            ];

        } catch (Exception $e) {
            $resultados['errores']++;
            $resultados['detalles'][] = [
                'tipo' => 'error',
                'mensaje' => "Fila " . ($index + 2) . ": Error inesperado - " . $e->getMessage()
            ];
        }
    }

    return $resultados;
}
?>