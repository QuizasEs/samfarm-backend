<?php

if ($peticionAjax) {
    require_once "../models/ingresoMasivoModel.php";
} else {
    require_once "./models/ingresoMasivoModel.php";
}

class ingresoMasivoController extends ingresoMasivoModel
{
    /**
     * Obtener datos iniciales para la vista
     */
    public function obtener_datos_iniciales_controlador()
    {
        $sucursales = ingresoMasivoModel::obtener_sucursales_model();
        $proveedores = ingresoMasivoModel::obtener_proveedores_model();

        return [
            'sucursales' => $sucursales,
            'proveedores' => $proveedores
        ];
    }

    /**
     * Procesar ingreso masivo desde Excel
     */
    public function procesar_ingreso_masivo_controlador()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Validar que se haya enviado el archivo y la sucursal
        if (!isset($_FILES['archivo_excel']) || empty($_FILES['archivo_excel']['tmp_name'])) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se ha enviado ningún archivo Excel.',
                'Tipo' => 'error'
            ]);
        }

        if (!isset($_POST['sucursal_id']) || empty($_POST['sucursal_id'])) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Debe seleccionar una sucursal.',
                'Tipo' => 'error'
            ]);
        }

        $sucursal_id = (int)$_POST['sucursal_id'];
        $us_id = isset($_SESSION['id_smp']) ? (int)$_SESSION['id_smp'] : 1;

        // ID de categorías por defecto (N/A)
        $categoria_default = 10;

        // Validar que el archivo sea un Excel válido
        $allowedExtensions = ['xlsx', 'xls'];
        $fileName = $_FILES['archivo_excel']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'El archivo debe ser un Excel (.xlsx o .xls).',
                'Tipo' => 'error'
            ]);
        }

        try {
            // Leer el archivo Excel
            $datosExcel = $this->leerExcel($_FILES['archivo_excel']['tmp_name'], $fileExtension);

            if (empty($datosExcel)) {
                return json_encode([
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'El archivo Excel está vacío o no tiene datos.',
                    'Tipo' => 'error'
                ]);
            }

            // Procesar cada fila del Excel
            $resultados = [
                'exitosos' => 0,
                'errores' => 0,
                'detalles' => []
            ];

            foreach ($datosExcel as $index => $fila) {
                try {
                    // Limpiar datos de la fila usando índice
                    $filaLimpia = $this->limpiarFilaExcel($fila);

                    // Si la descripción está vacía, omitir la fila
                    if (empty($filaLimpia['descripcion'])) {
                        $resultados['errores']++;
                        $resultados['detalles'][] = [
                            'fila' => $index + 2,
                            'mensaje' => 'Fila omitida: Todos los campos vacíos'
                        ];
                        continue;
                    }

                    // Obtener o crear medicamento
                    $med_id = $this->obtenerOCrearMedicamento($filaLimpia, $categoria_default);

                    if (!$med_id) {
                        $resultados['errores']++;
                        $resultados['detalles'][] = [
                            'fila' => $index + 2,
                            'mensaje' => 'Error al crear/obtener medicamento: ' . $filaLimpia['descripcion']
                        ];
                        continue;
                    }

                    // Validar proveedor
                    $pr_id = !empty($filaLimpia['proveedor_id']) ? (int)$filaLimpia['proveedor_id'] : null;
                    
                    // Si hay proveedor, verificar que exista
                    if ($pr_id !== null && !ingresoMasivoModel::verificar_proveedor_model($pr_id)) {
                        // Si el proveedor no existe, usar null
                        $pr_id = null;
                    }

                    // Total Unidades viene del Excel (es la cantidad total de unidades)
                    $total_unidades = !empty($filaLimpia['cantidad']) ? (int)$filaLimpia['cantidad'] : 0;
                    
                    // Si la cantidad es 0 o nula, usar valor por defecto 999
                    if ($total_unidades <= 0) {
                        $total_unidades = 999;
                    }
                    
        // Obtener cantidad por caja (unidades contenidas en cada caja)
        $cant_por_caja = !empty($filaLimpia['cantidad_caja']) ? (int)$filaLimpia['cantidad_caja'] : 1;
        // LM_CANTIDAD_BLISTER debe ser 1 (esta columna no tiene fin y será removida en el futuro)
        $cant_blister = 1;
        // LM_CANTIDAD_UNIDAD debe ser las unidades por caja (del Excel)
        $cant_unidad = $cant_por_caja;
        
        // Calcular Total Cajas = Total Unidades / Unidades por caja
        $cantidad = ($cant_por_caja > 0) ? ceil($total_unidades / $cant_por_caja) : $total_unidades;
        
        // Calcular cantidad actual en cajas y unidades
        $cant_caja = $cantidad;
        $cant_actual_cajas = $cantidad;
        $cant_actual_unidades = $total_unidades;

                    // Precio de compra y venta
                    $precio_compra = !empty($filaLimpia['precio_compra']) ? (float)$filaLimpia['precio_compra'] : 0;
                    $precio_venta = !empty($filaLimpia['precio_venta']) ? (float)$filaLimpia['precio_venta'] : 0;

                    // Fecha de vencimiento - valor por defecto 2040-12-31
                    $fecha_vencimiento = '2040-12-31';
                    if (!empty($filaLimpia['fecha_vencimiento'])) {
                        $fecha_vencimiento = $this->formatearFecha($filaLimpia['fecha_vencimiento']);
                    }

                    // Número de lote - se genera automáticamente con formato MED-XXXX
                    $numero_lote = $this->generarNumeroLote();
                    $numero_lote = mainModel::limpiar_cadena($numero_lote);

                    // Crear lote
                    $costo_lista = !empty($filaLimpia['costo_lista']) ? (float)$filaLimpia['costo_lista'] : 0;

                    // Márgenes fijos en 0
                    $margen_u = 0;
                    $margen_c = 0;

                    // Precios mínimos calculados (márgenes = 0 → precio_min = precio_compra)
                    $precio_min_u = $precio_compra;
                    $precio_min_c = $precio_venta * $cant_unidad;

                    $datosLote = [
                        'med_id' => $med_id,
                        'su_id' => $sucursal_id,
                        'pr_id' => $pr_id,
                        'pr_id_compra' => null,
                        'lm_numero_lote' => $numero_lote,
                        'lm_cant_caja' => $cant_caja,
                        'lm_cant_blister' => $cant_blister,
                        'lm_cant_unidad' => $cant_unidad,
                        'lm_cant_actual_cajas' => $cant_actual_cajas,
                        'lm_cant_actual_unidades' => $cant_actual_unidades,
                        'lm_costo_lista' => $costo_lista,
                        'lm_precio_compra' => $precio_compra,
                        'lm_precio_venta' => $precio_venta,
                        'lm_margen_u' => $margen_u,
                        'lm_margen_c' => $margen_c,
                        'lm_precio_min_u' => $precio_min_u,
                        'lm_precio_min_c' => $precio_min_c,
                        'lm_fecha_vencimiento' => $fecha_vencimiento,
                        'lm_estado' => 'activo'
                    ];

                    $lm_id = ingresoMasivoModel::crear_lote_model($datosLote);

                    if (!$lm_id) {
                        $resultados['errores']++;
                        $resultados['detalles'][] = [
                            'fila' => $index + 2,
                            'mensaje' => 'Error al crear lote para: ' . $filaLimpia['descripcion']
                        ];
                        continue;
                    }

                    // Actualizar inventario
                    $total_valorado = $cant_actual_unidades * $precio_venta;
                    $datosInventario = [
                        'med_id' => $med_id,
                        'su_id' => $sucursal_id,
                        'inv_total_cajas' => $cant_actual_cajas,
                        'inv_total_unidades' => $cant_actual_unidades,
                        'inv_total_valorado' => $total_valorado
                    ];

                    ingresoMasivoModel::actualizar_inventario_model($datosInventario);

                    // Registrar movimiento de inventario
                    $datosMovimiento = [
                        'lm_id' => $lm_id,
                        'med_id' => $med_id,
                        'su_id' => $sucursal_id,
                        'us_id' => $us_id,
                        'mi_tipo' => 'entrada',
                        'mi_cantidad' => $cant_actual_unidades,
                        'mi_unidad' => 'unidad',
                        'mi_referencia_tipo' => 'ingreso_masivo',
                        'mi_referencia_id' => $lm_id,
                        'mi_motivo' => 'Ingreso masivo desde Excel'
                    ];

                    ingresoMasivoModel::registrar_movimiento_model($datosMovimiento);

                    $resultados['exitosos']++;
                    $resultados['detalles'][] = [
                        'fila' => $index + 2,
                        'mensaje' => 'OK: ' . $filaLimpia['descripcion'] . ' (' . $cant_actual_unidades . ' unidades)'
                    ];

                } catch (Exception $e) {
                    $resultados['errores']++;
                    $resultados['detalles'][] = [
                        'fila' => $index + 2,
                        'mensaje' => 'Error: ' . $e->getMessage()
                    ];
                }
            }

            return json_encode([
                'Alerta' => 'recargar',
                'Titulo' => 'Ingreso Masivo Completado',
                'texto' => "Se procesaron {$resultados['exitosos']} registros exitosamente y {$resultados['errores']} con errores.",
                'Tipo' => $resultados['errores'] > 0 ? 'warning' : 'success',
                'resultados' => $resultados
            ]);

        } catch (Exception $e) {
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'Tipo' => 'error'
            ]);
        }
    }

    /**
     * Obtener o crear medicamento
     */
    private function obtenerOCrearMedicamento($fila, $categoria_default)
    {
        // Buscar medicamento por nombre
        $med_id = ingresoMasivoModel::obtener_medicamento_por_nombre_model($fila['descripcion']);

        if (!$med_id) {
            // Crear nuevo medicamento
            $datosMedicamento = [
                'Nombre' => $fila['descripcion'],
                'Principio' => 'N/A',
                'Accion' => null,
                'Presentacion' => null,
                'Uso' => $categoria_default,
                'Forma' => $categoria_default,
                'Via' => $categoria_default,
                'Proveedor' => !empty($fila['proveedor_id']) ? $fila['proveedor_id'] : null,
                'Descripcion' => 'Creado desde ingreso masivo',
                'CodigoBarras' => null
            ];

            $med_id = ingresoMasivoModel::crear_medicamento_model($datosMedicamento);
        }

        return $med_id;
    }

    /**
     * Leer archivo Excel
     */
    private function leerExcel($filePath, $extension)
    {
        global $peticionAjax;
        if ($peticionAjax) {
            require_once '../libs/excel/ExcelReader.php';
        } else {
            require_once './libs/excel/ExcelReader.php';
        }

        if (!in_array($extension, ['xlsx'])) {
            throw new Exception('Solo se admiten archivos .xlsx. Para archivos .xls, por favor guarde como .xlsx');
        }

        try {
            $excel = new ExcelReader($filePath);
            $excel->read(); // Cargar los datos primero
            $datos = $excel->toArray();

            return $datos;

        } catch (Exception $e) {
            throw new Exception('Error al leer el archivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Mapear columnas del Excel a nombres conocidos
     * Estructura del Excel (sin código de barras):
     * Índice 0: Descripción (obligatorio)
     * Índice 1: Proveedor (opcional)
     * Índice 2: Unidades por caja (opcional)
     * Índice 3: Costo lista (opcional)
     * Índice 4: Precio compra (obligatorio)
     * Índice 5: Precio venta (obligatorio)
     * Índice 6: Total Unidades (obligatorio)
     * Índice 7: Vencimiento (opcional)
     */
    private function mapearColumnas($headers)
    {
        $mapeo = [
            'descripcion' => null,
            'nombre' => null,
            'medicamento' => null,
            'producto' => null,
            'proveedor' => null,
            'proveedor_id' => null,
            'cantidad' => null,
            'cantidad_cajas' => null,
            'cajas' => null,
            'stock' => null,
            'cantidad_caja' => null,
            'cantidad_blister' => null,
            'blister' => null,
            'unidades' => null,
            'cantidad_unidad' => null,
            'precio_compra' => null,
            'precio_compra_bs' => null,
            'pc' => null,
            'precio_venta' => null,
            'precio_venta_bs' => null,
            'pv' => null,
            'precio' => null,
            'fecha_vencimiento' => null,
            'vencimiento' => null,
            'fecha' => null,
            'numero_lote' => null,
            'lote' => null,
            'n_lote' => null,
            'nro_lote' => null
        ];

        foreach ($headers as $index => $header) {
            $headerLower = strtolower(trim($header));
            
            // Detectar columna de descripción/nombre
            $descripciones = ['descripcion', 'descripción', 'nombre', 'medicamento', 'producto', 'articulo', 'item'];
            if (in_array($headerLower, $descripciones, true)) {
                $mapeo['descripcion'] = $header;
            }
            // Detectar columna de proveedor
            elseif (in_array($headerLower, ['proveedor', 'proveedor_id', 'id_proveedor', 'pr_id'])) {
                $mapeo['proveedor_id'] = $header;
            }
            // Detectar columna de cantidad (total unidades)
            elseif (in_array($headerLower, ['cantidad', 'cantidad_cajas', 'cajas', 'stock', 'total unidades', 'total_unidades'])) {
                $mapeo['cantidad'] = $header;
            }
            // Detectar cantidad por caja (unidades por caja)
            elseif (in_array($headerLower, ['cantidad_caja', 'caja', 'cajas', 'unidades por caja', 'unidades_por_caja'])) {
                $mapeo['cantidad_caja'] = $header;
            }
            // Detectar cantidad por blister
            elseif (in_array($headerLower, ['cantidad_blister', 'blister', 'blisters'])) {
                $mapeo['cantidad_blister'] = $header;
            }
            // Detectar cantidad por unidad (no incluir total unidades aquí para evitar conflicto)
            elseif (in_array($headerLower, ['cantidad_unidad', 'unidad', 'unidades'])) {
                $mapeo['cantidad_unidad'] = $header;
            }
            // Detectar columna de precio compra
            elseif (in_array($headerLower, ['precio_compra', 'precio_compra_bs', 'pc', 'costo', 'p_compra', 'costo lista', 'costo_lista', 'costo caja', 'costo_caja', 'costo unitario', 'costo_unitario'])) {
                $mapeo['precio_compra'] = $header;
            }
            // Detectar columna de precio venta
            elseif (in_array($headerLower, ['precio_venta', 'precio_venta_bs', 'pv', 'precio', 'p_venta', 'precio caja', 'precio_caja', 'precio unitario', 'precio_unitario'])) {
                $mapeo['precio_venta'] = $header;
            }
            // Detectar columna de fecha vencimiento
            elseif (in_array($headerLower, ['fecha_vencimiento', 'vencimiento', 'fecha', 'f_vencimiento', 'fec_venc'])) {
                $mapeo['fecha_vencimiento'] = $header;
            }
            // Detectar columna de número de lote
            elseif (in_array($headerLower, ['numero_lote', 'lote', 'n_lote', 'nro_lote', 'num_lote'])) {
                $mapeo['numero_lote'] = $header;
            }
        }

        return $mapeo;
    }

/**
     * Limpiar fila de Excel - versión por índice (más literal)
     * Estructura del Excel (sin código de barras):
     * Índice 0: Descripción (obligatorio)
     * Índice 1: Proveedor (opcional)
     * Índice 2: Unidades por caja (opcional)
     * Índice 3: Costo lista (opcional)
     * Índice 4: Precio compra (obligatorio)
     * Índice 5: Precio venta (obligatorio)
     * Índice 6: Total Unidades (obligatorio)
     * Índice 7: Vencimiento (opcional)
     */
    private function limpiarFilaExcel($fila)
    {
        // Obtener valores por índice numérico
        $values = array_values($fila);

        $resultado = [
            'descripcion' => '',
            'proveedor_id' => null,
            'cantidad' => 0,
            'cantidad_caja' => 1,
            'cantidad_blister' => 1,
            'cantidad_unidad' => 1,
            'precio_compra' => 0,
            'precio_venta' => 0,
            'fecha_vencimiento' => null,
            'costo_lista' => 0,
            'codigo_barras' => '',
            'numero_lote' => ''
        ];

        // Índice 0: Descripción (obligatorio)
        if (isset($values[0]) && !empty(trim(strval($values[0])))) {
            $resultado['descripcion'] = ingresoMasivoModel::limpiar_cadena_especial(trim(strval($values[0])));
        }

        // Índice 1: Proveedor (opcional)
        if (isset($values[1]) && is_numeric($values[1])) {
            $resultado['proveedor_id'] = (int)$values[1];
        }

        // Índice 2: Unidades por caja (opcional)
        if (isset($values[2]) && is_numeric($values[2]) && (int)$values[2] > 0) {
            $resultado['cantidad_caja'] = (int)$values[2];
        }

        // Índice 3: Costo lista (opcional)
        if (isset($values[3]) && is_numeric($values[3])) {
            $resultado['costo_lista'] = (float)$values[3];
        }

        // Índice 4: Precio compra (obligatorio)
        if (isset($values[4]) && is_numeric($values[4])) {
            $resultado['precio_compra'] = (float)$values[4];
        }

        // Índice 5: Precio venta (obligatorio)
        if (isset($values[5]) && is_numeric($values[5])) {
            $resultado['precio_venta'] = (float)$values[5];
        }

        // Índice 6: Total Unidades - Cantidad (obligatorio)
        if (isset($values[6]) && is_numeric($values[6])) {
            $resultado['cantidad'] = (int)$values[6];
        }

        // Índice 7: Vencimiento (opcional)
        if (isset($values[7]) && !empty(trim(strval($values[7])))) {
            $resultado['fecha_vencimiento'] = trim(strval($values[7]));
        }

return $resultado;
    }

    /**
     * Formatear fecha de Excel a formato MySQL
     */
    private function formatearFecha($valor)
    {
        // Fecha predeterminada si no hay fecha
        $fechaDefault = '2040-12-31';
        
        if (empty($valor)) {
            return $fechaDefault;
        }

        // Si es un número de Excel (fecha serial)
        if (is_numeric($valor)) {
            try {
                global $peticionAjax;
                if ($peticionAjax) {
                    require_once '../libs/excel/ExcelReader.php';
                } else {
                    require_once './libs/excel/ExcelReader.php';
                }
                return ExcelReader::excelDateToPHP($valor);
            } catch (Exception $e) {
                return $fechaDefault;
            }
        }

        // Si es una cadena de texto
        $fecha = trim($valor);
        
        // Intentar diferentes formatos
        $formatos = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formatos as $formato) {
            $date = DateTime::createFromFormat($formato, $fecha);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return $fechaDefault;
    }

    /**
     * Generar número de lote con formato MED-XXXX
     */
    private function generarNumeroLote()
    {
        $sql = mainModel::ejecutar_consulta_simple("
            SELECT lm_numero_lote FROM `lote_medicamento` 
            WHERE lm_numero_lote REGEXP '^MED-[0-9]+$' 
            ORDER BY CAST(SUBSTRING_INDEX(lm_numero_lote, '-', -1) AS UNSIGNED) DESC 
            LIMIT 1
        ");
        
        $data = $sql->fetch();
        $ultimoNumero = $data['lm_numero_lote'] ?? 0;
        
        if ($ultimoNumero && preg_match('/^MED-(\d+)$/', $ultimoNumero, $matches)) {
            $nuevoNumero = (int)$matches[1] + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return 'MED-' . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }
}
