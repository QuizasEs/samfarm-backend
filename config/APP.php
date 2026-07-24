<?php
    /* direccion del proyecto en local "cambiar una ves subido al servidor" */
    if (isset($_SERVER['HTTP_HOST'])) {
        $host_url = "http://" . $_SERVER['HTTP_HOST'] . "/";
    } else {
        // Fallback en caso de que se ejecute en CLI o localhost sea necesario
        $host_url = "http://localhost/";
    }

    // Usamos define() porque el resto de tu código usa SERVER_URL como constante
    define("SERVER_URL", $host_url);
    /*const SERVER_URL="http://localhost/samfarm-backend/";
    /* NOMPRE DE LA EMPRESA QUE SERA VISIBLE EN EL SERVIDOR */
    const COMPANY = "SAMFARM";
    /* MONEDA QUE SE TRABAJARA DENTRO DEL SISTEMA */
    const MONEDA = "Bs";

    /* sona horaria de bolivia  */
    date_default_timezone_set("America/La_Paz");

?>
