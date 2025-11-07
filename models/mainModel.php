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
    protected static function limpiar_cadena($cadena)
    {
        /* ANTIGUO    
        $cadena = trim($cadena);
        $cadena = stripslashes($cadena);
        $cadena = str_ireplace("<script>", "", $cadena);
        $cadena = str_ireplace("</script>", "", $cadena);
        $cadena = str_ireplace("<script src", "", $cadena);
        $cadena = str_ireplace("<script type=", "", $cadena);
        $cadena = str_ireplace("SELECT * FROM", "", $cadena);
        $cadena = str_ireplace("DELETE FROM", "", $cadena);
        $cadena = str_ireplace("INSERT INTO", "", $cadena);
        $cadena = str_ireplace("DROP TABLE", "", $cadena);
        $cadena = str_ireplace("DROP DATABASE", "", $cadena);
        $cadena = str_ireplace("TRUNCATE TABLE", "", $cadena);
        $cadena = str_ireplace("SHOW TABLES", "", $cadena);
        $cadena = str_ireplace("SHOW DATABASES", "", $cadena);
        $cadena = str_ireplace("<?php", "", $cadena);
        $cadena = str_ireplace("?>", "", $cadena);
        $cadena = str_ireplace("--", "", $cadena);
        $cadena = str_ireplace("[", "", $cadena);
        $cadena = str_ireplace("]", "", $cadena);
        $cadena = str_ireplace("^", "", $cadena);
        $cadena = str_ireplace("==", "", $cadena);
        $cadena = str_ireplace(";", "", $cadena);
        $cadena = str_ireplace("::", "", $cadena);
        $cadena = stripslashes($cadena);
        $cadena = trim($cadena);
        return $cadena; */

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

        /* ejecutamos todas las consultas */
        $sql_uf->execute();
        $sql_ff->execute();
        $sql_vd->execute();
        $sql_la->execute();
        $sql_su->execute();
        $sql_pr->execute();
        /* retornamos el resultado de consultas */
        return [
            'uso_farmacologico' => $sql_uf->fetchAll(),
            'forma_farmaceutica' => $sql_ff->fetchAll(),
            'via_administracion' => $sql_vd->fetchAll(),
            'laboratorios' => $sql_la->fetchAll(),
            'sucursales' => $sql_su->fetchAll(),
            'proveedores' => $sql_pr->fetchAll()
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


    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
}
