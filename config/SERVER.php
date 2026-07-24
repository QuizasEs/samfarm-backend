<?php
    /* Configuración inicial para la base de datos (local) */
    const SERVER = "localhost";
    const DB = "samfarm_db";
    /* const DB = "projecto"; */
    const USER = "web_app_user";
    const PASS = "2025sqlpass";

    /* Inicializa la conexión con la base de datos */
    define("SGBD", "mysql:host=" . SERVER . ";dbname=" . DB);

    /* Configuración de encriptación */
    const METHOD = "AES-256-CBC"; /* método de cifrado */
    const SECRET_KEY = '$farm@2025'; /* clave personalizada */
    const SECRET_IV = "037970"; /* vector de inicialización */
?>
