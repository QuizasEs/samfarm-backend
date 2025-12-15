<?php
/* preguntamos si se realiza una peticion ajax o no */
if ($peticionAjax) {
    require_once "../config/SERVER.php";
} else {
    require_once "./config/SERVER.php";
}

/* -------------------------------------------------clase principal main model------------------------------------- */
class mainModel
{

    /* ------------------funcion de conexion a la base de datos usandos variables de SERVER.php ----------------*/
    // Variable estática para guardar la conexión
    private static $conexion = null;

    protected static function Conectar()
    {
        // Si ya existe la conexión, la retorna
        if (self::$conexion !== null) {
            return self::$conexion;
        }

        // Si no existe, la crea una sola vez
        try {
            self::$conexion = new PDO(SGBD . ";charset=utf8", USER, PASS);
            self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return self::$conexion;
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }


    /* ----------------------------------------funcion que ejecuta consultas simples---------------------------------------------- */
    protected static function ejecutar_consulta_simple($consulta)
    {
        $sql = self::conectar()->prepare($consulta);
        $sql->execute();
        return $sql;
    }

    /* ---------------------------------------funcion de encriptado---------------------------------------------- */
    public static function encryption($string)
    {
        $output = FALSE;
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        $output = openssl_encrypt($string, METHOD, $key, 0, $iv);
        $output = base64_encode($output);
        return $output;
    }
    /* -------------------------------------- funcion de desencriptar ------------------------------------------------ */
    public static function decryption($string)
    {
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        $output = openssl_decrypt(base64_decode($string), METHOD, $key, 0, $iv);
        return $output;
    }
    /* -------------------------------------genera codigos aleatorios------------------------------------------------- */
    protected static function generar_codigo_aleatorio($letra, $longitud, $numero)
    {
        for ($i = 0; $i < $longitud; $i++) {
            $aleatorio = rand(0, 9);
            $letra .= $aleatorio;
        }
        return $letra . "-" . $numero;
    }

    /* ----------------------------------------funcion para limpiar cadenas---------------------------------------------- */
    public static function limpiar_cadena($cadena)
    {


        // Eliminar espacios al inicio y final
        $cadena = trim($cadena);

        // Eliminar barras invertidas
        $cadena = stripslashes($cadena);

        // Eliminar etiquetas HTML y PHP (protección contra XSS)
        $cadena = strip_tags($cadena);

        // Eliminar caracteres de control peligrosos pero mantener espacios normales
        $cadena = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cadena);

        // Normalizar espacios múltiples a uno solo
        $cadena = preg_replace('/\s+/', ' ', $cadena);

        // Eliminar caracteres NULL
        $cadena = str_replace("\0", '', $cadena);

        // Eliminar espacios al inicio y final después de todas las limpiezas
        $cadena = trim($cadena);

        return $cadena;
    }
    /* --------------------------------------funcion que verifica los datos------------------------------------------------ */
    protected static function verificar_datos($filtro, $cadena)
    {
        if (preg_match("/^" . $filtro . "$/", $cadena)) {
            return false;
        } else {
            return true;
        }
    }

    /* -----------------------------------funcion para verificar las fechas --------------------------------------------------- */
    protected static function verificar_fecha($fecha)
    {
        $valor = explode('-', $fecha);
        if ((count($valor)) == 3 && checkdate($valor[1], $valor[2], $valor[0])) {
            return false;
        } else {
            return true;
        }
    }
    /* -----------------------------------------funcion paginador de tablas--------------------------------------------- */
    protected static function paginador_tablas($pagina, $Npaginas, $url, $botones)
    {
        $tabla = '<nav aria-label="Page navigation example">
                        <ul class="custom-pagination">';
        if ($pagina == 1) {
            $tabla .= '<li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>';
        } else {
            $tabla .= '<li class="page-item">
                            <a class="page-link" href="' . $url . ($pagina - 1) . '/" tabindex="-1">Previous</a>
                        </li>
                        
                        ';
        }


        $ci = 0;
        for ($i = $pagina; $i <= $Npaginas; $i++) {
            if ($i >= $botones) {
                break;
            }

            if ($pagina == $i) {
                $tabla .= '<li class="page-item active"><a class="page-link" href="' . $url . $i . '/">' . $i . '</a></li>';
            } else {
                $tabla .= '<li class="page-item"><a class="page-link" href="' . $url . $i . '/">' . $i . '</a></li>';
            }
            $ci++;
        }

        if ($pagina == $Npaginas) {
            $tabla .= '<li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Next</a>
                        </li>';
        } else {
            $tabla .= '<li class="page-item">
                            <a class="page-link" href="' . $url . ($pagina + 1) . '/" tabindex="-1">Next</a>
                        </li>
                        
                        ';
        }
        $tabla .= ' </ul>
                    </nav>';
        return $tabla;
    }
    protected static function paginador_tablas_main($pagina, $Npaginas, $url, $botones)
    {
        $tabla = '<nav aria-label="Page navigation example">
                    <ul class="custom-pagination">';
        if ($pagina == 1) {
            $tabla .= '<li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Previous</a>
                    </li>';
        } else {
            $prev = $pagina - 1;
            $tabla .= '<li class="page-item">
                        <a class="page-link" href="' . $url . $prev . '/" data-page="' . $prev . '" tabindex="-1">Previous</a>
                    </li>';
        }

        $ci = 0;
        for ($i = $pagina; $i <= $Npaginas; $i++) {
            if ($ci >= $botones) {
                break;
            }

            if ($pagina == $i) {
                $tabla .= '<li class="page-item active"><a class="page-link" href="' . $url . $i . '/" data-page="' . $i . '">' . $i . '</a></li>';
            } else {
                $tabla .= '<li class="page-item"><a class="page-link" href="' . $url . $i . '/" data-page="' . $i . '">' . $i . '</a></li>';
            }
            $ci++;
        }

        if ($pagina == $Npaginas) {
            $tabla .= '<li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Next</a>
                    </li>';
        } else {
            $next = $pagina + 1;
            $tabla .= '<li class="page-item">
                        <a class="page-link" href="' . $url . $next . '/" data-page="' . $next . '" tabindex="-1">Next</a>
                    </li>';
        }
        $tabla .= ' </ul>
                </nav>';
        return $tabla;
    }




    /* ------------------------------ obtener informacion de sucursales y roles para usuario----------------------------------- */
    protected static function data_rol_list_model($tipo, $id)
    {
        if ($tipo == "Unico") {
            $sql = mainModel::conectar()->prepare("
                SELECT u.us_nombres, r.ro_nombre, r.ro_id   
                FROM roles AS r
                JOIN usuarios AS u ON u.ro_id = r.ro_id 
                WHERE u.us_id = $id;");
        } else if ($tipo == "Multiple") {
            $sql = mainModel::conectar()->prepare("SELECT ro_nombre, ro_id FROM roles WHERE ro_estado = 1");
        }
        $sql->execute();
        return $sql;
    }
    /* -----------------------------------------modelo para obtener sucursales--------------------------------------------- */

    protected static function data_sucursal_list_model($tipo, $id)
    {
        if ($tipo == "Unico") {
            $sql = mainModel::conectar()->prepare("
            SELECT u.us_nombres, s.su_nombre, s.su_id   
            FROM sucursales AS s
            JOIN usuarios AS u ON u.su_id = s.su_id 
            WHERE u.us_id = $id;");
        } else if ($tipo == "Multiple") {
            $sql = mainModel::conectar()->prepare("SELECT su_id, su_nombre FROM sucursales WHERE su_estado = 1");
        }
        $sql->execute();
        return $sql;
    }

    /* ----------------------------------------- recupera informacion de tablas foraneas de medicamentos--------------------------------------------- */

    protected static function datos_extras_model()
    {

        $sql_uf = self::conectar()->prepare("
                SELECT * FROM uso_farmacologico WHERE uf_estado = 1
            ");
        $sql_ff = self::conectar()->prepare("
                SELECT * FROM forma_farmaceutica WHERE ff_estado = 1
            ");
        $sql_vd = self::conectar()->prepare("
                SELECT * FROM via_de_administracion WHERE vd_estado = 1
            ");
        $sql_la = self::conectar()->prepare("
                SELECT * FROM laboratorios WHERE la_estado =1    
            ");
        $sql_su = self::conectar()->prepare("
                SELECT * FROM sucursales WHERE su_estado = 1
            ");
        $sql_pr = self::conectar()->prepare("SELECT * FROM proveedores WHERE pr_estado = 1");
        $sql_caja = self::conectar()->prepare("SELECT * FROM `usuarios` WHERE ro_id != 1");

        /* ejecutamos todas las consultas */
        $sql_uf->execute();
        $sql_ff->execute();
        $sql_vd->execute();
        $sql_la->execute();
        $sql_su->execute();
        $sql_pr->execute();
        $sql_caja->execute();
        /* retornamos el resultado de consultas */
        return [
            'uso_farmacologico' => $sql_uf->fetchAll(),
            'forma_farmaceutica' => $sql_ff->fetchAll(),
            'via_administracion' => $sql_vd->fetchAll(),
            'laboratorios' => $sql_la->fetchAll(),
            'sucursales' => $sql_su->fetchAll(),
            'proveedores' => $sql_pr->fetchAll(),
            'caja' => $sql_caja->fetchAll()
        ];
    }
    /* ----------------------------------------- funcion para guardar imagenes--------------------------------------------- */

    public function procesar_imagen($archivo, $tipo, $img_dir)
    {
        // Validar formato de imagen
        $mime_type = mime_content_type($archivo['tmp_name']);
        if (!in_array($mime_type, ['image/jpeg', 'image/png'])) {
            return [
                'error' => true,
                'alerta' => [
                    "tipo" => "simple",
                    "titulo" => "Error de formato",
                    "texto" => "La imagen debe ser JPG o PNG",
                    "icono" => "error"
                ]
            ];
        }

        // Validar tamaño (5MB máximo)
        if (($archivo['size'] / 1024) > 5120) {
            return [
                'error' => true,
                'alerta' => [
                    "tipo" => "simple",
                    "titulo" => "Archivo muy grande",
                    "texto" => "La imagen supera el tamaño permitido (5MB)",
                    "icono" => "error"
                ]
            ];
        }

        // Generar nombre único aleatorio
        $extension = ($mime_type == 'image/jpeg') ? '.jpg' : '.png';
        $random_id = bin2hex(random_bytes(8)); // genera una cadena única segura
        $nombre_archivo = $tipo . "_" . $random_id . "_" . time() . $extension;

        // Evitar colisiones (muy raro, pero por si acaso)
        while (file_exists($img_dir . $nombre_archivo)) {
            $random_id = bin2hex(random_bytes(8));
            $nombre_archivo = $tipo . "_" . $random_id . "_" . time() . $extension;
        }

        // Mover archivo al directorio destino
        if (!move_uploaded_file($archivo['tmp_name'], $img_dir . $nombre_archivo)) {
            return [
                'error' => true,
                'alerta' => [
                    "tipo" => "simple",
                    "titulo" => "Error al subir",
                    "texto" => "No se pudo guardar la imagen",
                    "icono" => "error"
                ]
            ];
        }

        // Cambiar permisos
        chmod($img_dir . $nombre_archivo, 0644);

        return [
            'error' => false,
            'nombre' => $nombre_archivo
        ];
    }

    /* ----------------------------------------modelo para eliminar imagen---------------------------------------------- */
    public function eliminar_imagenes($imagenes, $img_dir)
    {
        foreach ($imagenes as $imagen) {
            $ruta = $img_dir . $imagen;
            if ($imagen != "" && is_file($ruta)) {
                chmod($ruta, 0777);
                unlink($ruta);
            }
        }
    }


    /* --------------------------------------generar reportes pdpf------------------------------------------------ */

    protected static function obtener_config_empresa_model()
    {
        try {
            $sql = self::conectar()->prepare("SELECT * FROM configuracion_empresa WHERE ce_id = 1 LIMIT 1");
            $sql->execute();
            $resultado = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$resultado) {
                return [
                    'ce_id' => 1,
                    'ce_nombre' => 'SAMFARM PHARMA - SIN CONFIGURACIÓN',
                    'ce_nit' => '000000000',
                    'ce_direccion' => 'Dirección no configurada',
                    'ce_telefono' => 'Sin teléfono',
                    'ce_correo' => 'Sin correo',
                    'ce_logo' => '',
                    'ce_creado_en' => date('Y-m-d H:i:s'),
                    'ce_actualizado_en' => date('Y-m-d H:i:s')
                ];
            }

            return $resultado;
        } catch (Exception $e) {
            return [
                'ce_id' => 1,
                'ce_nombre' => 'SAMFARM PHARMA - ERROR',
                'ce_nit' => '000000000',
                'ce_direccion' => 'Error al cargar dirección',
                'ce_telefono' => 'Error al cargar teléfono',
                'ce_correo' => 'Error al cargar correo',
                'ce_logo' => '',
                'ce_creado_en' => date('Y-m-d H:i:s'),
                'ce_actualizado_en' => date('Y-m-d H:i:s')
            ];
        }
    }

    protected static function generar_pdf_reporte_fpdf($datos_pdf, $modo_salida = 'I', $output_mode = 'download')
    {
        require_once dirname(__DIR__) . '/libs/fpdf/fpdf.php';

        // --- Integración de dimensiones y orientación personalizadas ---
        $orientacion = $datos_pdf['orientacion'] ?? 'P';
        $unidad = 'mm';
        $tamano_papel = 'Letter'; // Tamaño por defecto

        if (isset($datos_pdf['dimensiones']) && is_array($datos_pdf['dimensiones'])) {
            $ancho = $datos_pdf['dimensiones']['ancho'] ?? 0;
            $alto = $datos_pdf['dimensiones']['alto'] ?? 0;
            if ($ancho > 0 && $alto > 0) {
                $tamano_papel = [$ancho, $alto];
            }
        }

        $pdf = new FPDF($orientacion, $unidad, $tamano_papel);
        $pdf->AddPage();

        $config_empresa = self::obtener_config_empresa_model();

        // Encabezado más compacto
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 6, ($config_empresa['ce_nombre']), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 3, ('NIT: ' . $config_empresa['ce_nit'] . ' | Telf: ' . $config_empresa['ce_telefono']), 0, 1, 'C');
        $pdf->Cell(0, 3, ($config_empresa['ce_direccion']), 0, 1, 'C');

        $pdf->SetDrawColor(52, 152, 219);
        $pdf->SetLineWidth(0.2);

        // Ancho de la línea dinámico
        $ancho_pagina = $pdf->GetPageWidth();
        $pdf->Line(10, $pdf->GetY() + 1, $ancho_pagina - 10, $pdf->GetY() + 1);
        $pdf->Ln(2);

        // Título
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 5, ($datos_pdf['titulo']), 0, 1, 'C');
        $pdf->Ln(1);

        // Información superior compacta
        if (isset($datos_pdf['info_superior'])) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Rect(10, $pdf->GetY(), $ancho_pagina - 20, 12, 'F');

            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(52, 73, 94);

            $y_start = $pdf->GetY() + 2;
            $x_pos = 15;
            $count = 0;

            foreach ($datos_pdf['info_superior'] as $key => $value) {
                $pdf->SetXY($x_pos, $y_start);
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(25, 3, ($key . ':'), 0, 0, 'L');
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell(40, 3, ($value), 0, 0, 'L');

                $count++;
                $x_pos += 80;
                if ($count % 2 == 0) {
                    $y_start += 4;
                    $x_pos = 15;
                }
            }
            $pdf->Ln(8);
        }

        // DEFINIR ALTURA MÁXIMA ANTES DEL PIE DE PÁGINA
        $altura_maxima = 250; // 279mm (Letter) - 10mm margen sup - 15mm pie de página - 4mm margen

        // Tabla optimizada
        if (isset($datos_pdf['tabla'])) {
            $tabla = $datos_pdf['tabla'];

            // Eliminar columnas duplicadas
            $headers_filtrados = [];
            $seen_headers = [];

            foreach ($tabla['headers'] as $header) {
                $header_text = $header['text'];
                if (!in_array($header_text, $seen_headers)) {
                    $headers_filtrados[] = $header;
                    $seen_headers[] = $header_text;
                }
            }
            $tabla['headers'] = $headers_filtrados;

            // Ajustar anchos de columnas
            $ancho_total_tabla = array_sum(array_column($tabla['headers'], 'width'));
            $ancho_disponible = $ancho_pagina - 20;

            if ($ancho_total_tabla > $ancho_disponible) {
                $factor_ajuste = $ancho_disponible / $ancho_total_tabla;
                foreach ($tabla['headers'] as &$header) {
                    $header['width'] = round($header['width'] * $factor_ajuste);
                }
            }

            // Encabezados compactos
            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetFillColor(52, 73, 94);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetDrawColor(52, 73, 94);

            foreach ($tabla['headers'] as $header) {
                $pdf->Cell($header['width'], 4, ($header['text']), 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Filas compactas
            $pdf->SetFont('Arial', '', 6);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->SetFillColor(248, 249, 250);

            $fill = false;
            foreach ($tabla['rows'] as $row) {
                // VERIFICAR ESPACIO CONSIDERANDO EL PIE DE PÁGINA
                if ($pdf->GetY() > $altura_maxima) {
                    $pdf->AddPage();
                    // Redibujar encabezados
                    $pdf->SetFont('Arial', 'B', 6);
                    $pdf->SetFillColor(52, 73, 94);
                    $pdf->SetTextColor(255, 255, 255);
                    foreach ($tabla['headers'] as $header) {
                        $pdf->Cell($header['width'], 4, ($header['text']), 1, 0, 'C', true);
                    }
                    $pdf->Ln();
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetTextColor(44, 62, 80);
                }

                if (isset($row['es_total']) && $row['es_total']) {
                    $pdf->SetFont('Arial', 'B', 7);
                    $pdf->SetFillColor(41, 128, 185);
                    $pdf->SetTextColor(255, 255, 255);
                    $fill_total = true;
                } else {
                    $pdf->SetFont('Arial', '', 6);
                    $pdf->SetTextColor(44, 62, 80);
                    $fill_total = false;
                }

                // Filtrar celdas duplicadas también
                $cells_filtrados = [];
                $cell_count = 0;
                foreach ($row['cells'] as $i => $cell) {
                    if ($cell_count < count($tabla['headers'])) {
                        $cells_filtrados[] = $cell;
                        $cell_count++;
                    }
                }

                foreach ($cells_filtrados as $i => $cell) {
                    $text = $cell['text'];
                    $width = $tabla['headers'][$i]['width'];
                    $align = isset($cell['align']) ? $cell['align'] : 'C';

                    if (isset($cell['color'])) {
                        $pdf->SetTextColor($cell['color'][0], $cell['color'][1], $cell['color'][2]);
                    }

                    $pdf->Cell($width, 4, $text, 1, 0, $align, $fill_total ? true : $fill);

                    if (isset($cell['color'])) {
                        $pdf->SetTextColor(44, 62, 80);
                    }
                }
                $pdf->Ln();
                $fill = !$fill;
            }
        }

        // Resumen compacto - VERIFICAR ESPACIO PARA EL RESUMEN TAMBIÉN
        if (isset($datos_pdf['resumen'])) {
            // Altura aproximada del resumen
            $altura_resumen = 20;

            // Verificar si hay espacio para el resumen + pie de página
            if ($pdf->GetY() + $altura_resumen > $altura_maxima) {
                $pdf->AddPage();
            }

            $pdf->Ln(3);
            $pdf->SetFillColor(236, 240, 241);
            $pdf->Rect(10, $pdf->GetY(), $ancho_pagina - 20, 15, 'F');

            $y_start = $pdf->GetY() + 2;
            $pdf->SetXY(15, $y_start);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(0, 4, ('RESUMEN DEL PERIODO'), 0, 1, 'L');

            foreach ($datos_pdf['resumen'] as $key => $value) {
                $pdf->SetX(15);
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(50, 3, ($key . ':'), 0, 0, 'L');
                $pdf->SetFont('Arial', '', 7);

                if (isset($value['color'])) {
                    $pdf->SetTextColor($value['color'][0], $value['color'][1], $value['color'][2]);
                }

                $pdf->Cell(0, 3, ($value['text']), 0, 1, 'L');

                if (isset($value['color'])) {
                    $pdf->SetTextColor(44, 62, 80);
                }
            }
        }

        // Pie de página - SOLO SI ESTAMOS EN LA PRIMERA PÁGINA O HAY SUFICIENTE ESPACIO
        $pdf->SetY(-40); // Posición fija desde el fondo
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 2, ('Generado: ' . date('d/m/Y H:i:s') . ' | Usuario: ' . ($_SESSION['nombre_smp'] ?? 'Sistema')), 0, 1, 'C');
        $pdf->Cell(0, 2, ('Página ') . $pdf->PageNo(), 0, 0, 'C');

        // Retornar contenido como string - igual que ventaModel
        return $pdf->Output('S');
    }

    public static function generar_excel_reporte($config)
    {
        if (empty($config['nombre_archivo']) || empty($config['datos']) || empty($config['headers'])) {
            echo "Error: Configuración incompleta para generar Excel";
            return;
        }

        // Obtener información de la empresa desde la base de datos
        $config_empresa = self::obtener_config_empresa_model();

        $titulo = $config['titulo'] ?? 'REPORTE';
        $datos = $config['datos'];
        $headers = $config['headers'];
        $nombre_archivo = $config['nombre_archivo'];
        $formato_columnas = $config['formato_columnas'] ?? [];
        $columnas_totales = $config['columnas_totales'] ?? [];
        $info_superior = $config['info_superior'] ?? [];

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF" . '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <style>
                        body {
                            font-family: "Segoe UI", Arial, sans-serif;
                            font-size: 11pt;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            margin: 0;
                            padding: 20px;
                        }

                        .container {
                            background: white;
                            border-radius: 12px;
                            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                            overflow: hidden;
                            margin: 0 auto;
                            max-width: 1400px;
                        }

                        .header {
                            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                            color: black;
                            font-size: 20pt;
                            font-weight: 300;
                            text-align: center;
                            padding: 25px;
                            margin-bottom: 0;
                            letter-spacing: 1px;
                            position: relative;
                        }

                        .header::after {
                            content: "";
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            right: 0;
                            height: 4px;
                            background: linear-gradient(90deg, #e74c3c, #f39c12, #2ecc71, #3498db);
                        }

                        .info {
                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            padding: 20px;
                            border-bottom: 1px solid #dee2e6;
                            display: grid;
                            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                            gap: 15px;
                            font-size: 10pt;
                        }

                        .info-item {
                            background: white;
                            padding: 12px;
                            border-radius: 8px;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                            border-left: 4px solid #3498db;
                        }

                        .info-item strong {
                            color: #2c3e50;
                            display: block;
                            margin-bottom: 5px;
                            font-size: 9pt;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        }

                        table {
                            border-collapse: separate;
                            border-spacing: 0;
                            width: 100%;
                            font-size: 10pt;
                            background: white;
                        }

                        th {
                            background: #D1EBFF;
                            color: #2B2B2B;
                            font-weight: 600;
                            text-align: center;
                            padding: 14px 10px;
                            border: none;
                            position: relative;
                            font-size: 11pt;
                            text-transform: uppercase;
                        }

                        th::after {
                            content: "";
                            position: absolute;
                            right: 0;
                            top: 25%;
                            height: 50%;
                            width: 1px;
                            background: rgba(255,255,255,0.3);
                        }

                        th:last-child::after {
                            display: none;
                        }

                        td {
                            padding: 12px 10px;
                            border-bottom: 1px solid #f8f9fa;
                            text-align: left;
                            transition: all 0.2s ease;
                        }

                        tr:hover td {
                            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
                            transform: translateY(-1px);
                            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                        }

                        .numero {
                            text-align: right;
                            font-weight: 600;
                            font-family: "Courier New", monospace;
                            color: #2c3e50;
                        }

                        .moneda {
                            text-align: right;
                            font-weight: 700;
                            font-family: "Courier New", monospace;
                            color: #27ae60;
                            background: linear-gradient(135deg, #f8fff9 0%, #f0fff4 100%);
                            border-left: 3px solid #27ae60;
                        }

                        .moneda-egreso {
                            text-align: right;
                            font-weight: 700;
                            font-family: "Courier New", monospace;
                            color: #c62828;
                            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
                            border-left: 3px solid #c62828;
                        }

                        .total-row {
                            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
                            color: white;
                            font-weight: 600;
                            font-size: 11pt;
                        }

                        .total-row td {
                            border: none;
                            padding: 16px 10px;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                        }

                        .total-row .numero, .total-row .moneda, .total-row .moneda-egreso {
                            color: white;
                            background: none;
                            border-left: none;
                        }

                        .footer {
                            margin-top: 0;
                            padding: 25px;
                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                            border-top: 1px solid #dee2e6;
                            font-size: 9pt;
                            color: #6c757d;
                            text-align: center;
                        }

                        .footer strong {
                            color: #2c3e50;
                            display: block;
                            margin-bottom: 8px;
                            font-size: 10pt;
                        }
                    </style>
                </head>
                <body>';

        echo '<div class="container">
                                <div class="header">' . htmlspecialchars($titulo) . ' - ' . htmlspecialchars($config_empresa['ce_nombre']) . '</div>';

        if (!empty($info_superior)) {
            echo '<div class="info">';
            foreach ($info_superior as $key => $value) {
                echo '<div class="info-item">
                                        <strong>' . htmlspecialchars($key) . '</strong>
                                        ' . htmlspecialchars($value) . '
                                    </div>';
            }
            echo '</div>';
        }

        echo '<table>';
        echo '<thead><tr>';
        foreach ($headers as $header) {
            echo '<th>' . htmlspecialchars(strtoupper($header)) . '</th>';
        }
        echo '</tr></thead>';

        echo '<tbody>';

        $totales = [];
        foreach ($columnas_totales as $col) {
            $totales[$col] = 0;
        }

        foreach ($datos as $row) {
            echo '<tr>';
            foreach ($headers as $header) {
                $valor = $row[$header] ?? '-';
                $clase = '';

                if (in_array($header, $columnas_totales)) {
                    if (isset($formato_columnas[$header]) && $formato_columnas[$header] === 'moneda') {
                        $clase = 'moneda';
                        $totales[$header] += (float)$valor;
                    } elseif (isset($formato_columnas[$header]) && $formato_columnas[$header] === 'numero') {
                        $clase = 'numero';
                        $totales[$header] += (float)$valor;
                    }
                }

                if (isset($formato_columnas[$header])) {
                    switch ($formato_columnas[$header]) {
                        case 'moneda':
                            $valor = 'Bs ' . number_format($valor, 2, ',', '.');
                            if (empty($clase)) {
                                $clase = 'moneda';
                            }
                            break;
                        case 'numero':
                            $valor = number_format($valor, 2, ',', '.');
                            if (empty($clase)) {
                                $clase = 'numero';
                            }
                            break;
                        case 'fecha':
                            $valor = date('d/m/Y', strtotime($valor));
                            break;
                        case 'fecha-hora':
                            $valor = date('d/m/Y H:i', strtotime($valor));
                            break;
                    }
                }

                echo '<td class="' . $clase . '">' . htmlspecialchars($valor) . '</td>';
            }
            echo '</tr>';
        }

        if (!empty($totales)) {
            echo '<tr class="total-row">';
            $cols_before_total = count($headers) - count($columnas_totales);
            if ($cols_before_total > 0) {
                echo '<td colspan="' . $cols_before_total . '" style="text-align: right; padding-right: 20px; color: black; font-weight: 600;">TOTAL:</td>';
            }
            foreach ($headers as $header) {
                if (in_array($header, $columnas_totales)) {
                    $valor_total = $totales[$header];
                    if (isset($formato_columnas[$header]) && $formato_columnas[$header] === 'moneda') {
                        $valor_total = 'Bs ' . number_format($valor_total, 2, ',', '.');
                    } elseif (isset($formato_columnas[$header]) && $formato_columnas[$header] === 'numero') {
                        $valor_total = number_format($valor_total, 2, ',', '.');
                    }
                    echo '<td class="numero" style="color:black; text-align:left;">' . htmlspecialchars($valor_total) . '</td>';
                }
            }
            echo '</tr>';
        }

        echo '</tbody></table>';

        echo '<div class="footer">
                                <strong>' . htmlspecialchars($config_empresa['ce_nombre']) . '</strong>
                                Este reporte fue generado automáticamente el ' . date('d/m/Y \a \l\a\s H:i:s') . '. Para consultas contacte al administrador del sistema.
                            </div>';

        echo '</div></body></html>';

        exit();
    }

    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
}
