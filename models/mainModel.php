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


    public static function generar_pdf_reporte($datos)
    {
        require_once "./libs/fpdf/fpdf.php";

        $empresa = self::obtener_config_empresa_model();

        $contenido_html = $datos["contenido"] ?? "";
        $nombre_archivo = $datos["nombre_archivo"] ?? ("Reporte_" . date("Y-m-d") . ".pdf");

        // Iniciar documento tamaño carta
        $pdf = new FPDF('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);

        /* ----------------------------
            ENCABEZADO DEL DOCUMENTO
            ---------------------------- */
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 7, utf8_decode($empresa['ce_nombre']), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, utf8_decode("NIT: " . $empresa['ce_nit']), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode($empresa['ce_direccion']), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode("Tel: " . $empresa['ce_telefono']), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->Line(10, $pdf->GetY(), 205, $pdf->GetY());
        $pdf->Ln(5);

        /* ----------------------------
            PARSEADOR DE HTML → PDF
            (Tablas, textos, títulos, etc.)
            ---------------------------- */

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetDrawColor(0, 0, 0);

        // Dividir por líneas
        $lineas = explode("\n", $contenido_html);

        foreach ($lineas as $linea) {

            $linea = trim($linea);
            if ($linea == "") continue;

            /* ---------- TITULOS ---------- */
            if (preg_match('/<h3>(.*?)<\/h3>/', $linea, $m)) {
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->Cell(0, 8, utf8_decode(strip_tags($m[1])), 0, 1, 'L');
                $pdf->Ln(2);
                continue;
            }

            /* ---------- INFO BOX ---------- */
            if (preg_match('/<div(.*?)>(.*?)<\/div>/s', $linea, $m)) {
                $texto = strip_tags($m[2]);
                $pdf->SetFont('Arial', '', 10);
                $pdf->MultiCell(0, 5, utf8_decode($texto));
                $pdf->Ln(2);
                continue;
            }

            /* ---------- TABLAS ---------- */
            if (strpos($linea, "<table") !== false) {
                $en_tabla = true;
                continue;
            }

            if (strpos($linea, "</table>") !== false) {
                $en_tabla = false;
                $pdf->Ln(5);
                continue;
            }

            if (!empty($en_tabla)) {

                // Header
                if (preg_match('/<th>(.*?)<\/th>/', $linea)) {
                    preg_match_all('/<th>(.*?)<\/th>/', $linea, $ths);

                    $pdf->SetFont('Arial', 'B', 9);
                    foreach ($ths[1] as $enc) {
                        $pdf->Cell(25, 7, utf8_decode(strip_tags($enc)), 1, 0, 'C');
                    }
                    $pdf->Ln();
                    continue;
                }

                // Filas
                if (preg_match('/<td(.*?)>(.*?)<\/td>/', $linea)) {
                    preg_match_all('/<td(.*?)>(.*?)<\/td>/', $linea, $tds);

                    $pdf->SetFont('Arial', '', 9);
                    foreach ($tds[2] as $val) {
                        $pdf->Cell(25, 6, utf8_decode(strip_tags($val)), 1, 0, 'C');
                    }
                    $pdf->Ln();
                    continue;
                }
            }

            /* ---------- TEXTO GENERAL ---------- */
            $pdf->SetFont('Arial', '', 10);
            $pdf->MultiCell(0, 5, utf8_decode(strip_tags($linea)));
        }

        /* ----------------------------
            SALIDA
            ---------------------------- */
        $pdf->Output("I", $nombre_archivo);
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
