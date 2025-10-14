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
    protected static function Conectar()
    {
        try {
            $conexion = new PDO(SGBD . ";charset=utf8", USER, PASS);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
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
        $cadena = str_ireplace(">", "", $cadena);
        $cadena = str_ireplace("<", "", $cadena);
        $cadena = str_ireplace("[", "", $cadena);
        $cadena = str_ireplace("]", "", $cadena);
        $cadena = str_ireplace("^", "", $cadena);
        $cadena = str_ireplace("==", "", $cadena);
        $cadena = str_ireplace(";", "", $cadena);
        $cadena = str_ireplace("::", "", $cadena);
        $cadena = stripslashes($cadena);
        $cadena = trim($cadena);
        return $cadena;
    }
    /* --------------------------------------funcion que verifica los datos------------------------------------------------ */
		protected static function verificar_datos($filtro,$cadena){
			if(preg_match("/^".$filtro."$/", $cadena)){
				return false;
			}else{
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
    }


    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
    /* -------------------------------------------------------------------------------------- */
}
