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
    public function encryption($string)
    {
        $output = FALSE;
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        $output = openssl_encrypt($string, METHOD, $key, 0, $iv);
        $output = base64_encode($output);
        return $output;
    }
    /* -------------------------------------- funcion de desncritar------------------------------------------------ */
    protected static function decryption($string)
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
    public function limpiar_cadena($cadena)
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
            if ($i >= $botones) {
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

    protected static function generar_pdf_reporte_fpdf($datos_pdf)
    {
        require_once dirname(__DIR__) . './libs/fpdf/fpdf.php';

        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        $config_empresa = self::obtener_config_empresa_model();

        // Encabezado más compacto
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 6, utf8_decode($config_empresa['ce_nombre']), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 3, utf8_decode('NIT: ' . $config_empresa['ce_nit'] . ' | Telf: ' . $config_empresa['ce_telefono']), 0, 1, 'C');
        $pdf->Cell(0, 3, utf8_decode($config_empresa['ce_direccion']), 0, 1, 'C');

        $pdf->SetDrawColor(52, 152, 219);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(10, $pdf->GetY() + 1, 200, $pdf->GetY() + 1);
        $pdf->Ln(2);

        // Título
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 5, utf8_decode($datos_pdf['titulo']), 0, 1, 'C');
        $pdf->Ln(1);

        // Información superior compacta
        if (isset($datos_pdf['info_superior'])) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Rect(10, $pdf->GetY(), 190, 12, 'F');

            $pdf->SetFont('Arial', '', 7);
            $pdf->SetTextColor(52, 73, 94);

            $y_start = $pdf->GetY() + 2;
            $x_pos = 15;
            $count = 0;

            foreach ($datos_pdf['info_superior'] as $key => $value) {
                $pdf->SetXY($x_pos, $y_start);
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(25, 3, utf8_decode($key . ':'), 0, 0, 'L');
                $pdf->SetFont('Arial', '', 7);
                $pdf->Cell(40, 3, utf8_decode($value), 0, 0, 'L');

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
            $ancho_disponible = 190;

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
                $pdf->Cell($header['width'], 4, utf8_decode($header['text']), 1, 0, 'C', true);
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
                        $pdf->Cell($header['width'], 4, utf8_decode($header['text']), 1, 0, 'C', true);
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
                    $text = utf8_decode($cell['text']);
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
            $pdf->Rect(10, $pdf->GetY(), 190, 15, 'F');

            $y_start = $pdf->GetY() + 2;
            $pdf->SetXY(15, $y_start);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(0, 4, utf8_decode('RESUMEN DEL PERIODO'), 0, 1, 'L');

            foreach ($datos_pdf['resumen'] as $key => $value) {
                $pdf->SetX(15);
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->Cell(50, 3, utf8_decode($key . ':'), 0, 0, 'L');
                $pdf->SetFont('Arial', '', 7);

                if (isset($value['color'])) {
                    $pdf->SetTextColor($value['color'][0], $value['color'][1], $value['color'][2]);
                }

                $pdf->Cell(0, 3, utf8_decode($value['text']), 0, 1, 'L');

                if (isset($value['color'])) {
                    $pdf->SetTextColor(44, 62, 80);
                }
            }
        }

        // Pie de página - SOLO SI ESTAMOS EN LA PRIMERA PÁGINA O HAY SUFICIENTE ESPACIO
        $pdf->SetY(-40); // Posición fija desde el fondo
        $pdf->SetFont('Arial', 'I', 6);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 2, utf8_decode('Generado: ' . date('d/m/Y H:i:s') . ' | Usuario: ' . ($_SESSION['nombre_smp'] ?? 'Sistema')), 0, 1, 'C');
        $pdf->Cell(0, 2, utf8_decode('Página ') . $pdf->PageNo(), 0, 0, 'C');

        $pdf->Output('I', $datos_pdf['nombre_archivo']);
        exit();
    }

    protected static function obtener_config_empresa_model()
    {
        $sql = self::conectar()->prepare("SELECT * FROM configuracion_empresa WHERE ce_id = 1 LIMIT 1");
        $sql->execute();
        $resultado = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$resultado) {
            return [
                'ce_nombre' => 'SAMFARM PHARMA',
                'ce_nit' => '123456789',
                'ce_direccion' => 'Dirección no configurada',
                'ce_telefono' => 'Sin teléfono',
                'ce_correo' => 'Sin correo'
            ];
        }

        return $resultado;
    }
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
}
