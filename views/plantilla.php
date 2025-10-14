
<!DOCTYPE html>
<html lang="en">

    <?php include"inc/head.php"; ?>


<body class="darkmode">
    <?php 
        $peticionAjax = false;
        require_once __DIR__ . "/../controllers/viewsController.php";

        $IV = new viewsController();
        $vistas = $IV->get_views_controller();

        if($vistas=="login" || $vistas == "404"){
            require_once "./views/content/".$vistas."-view.php";
            } else{

            include_once "inc/header.php";
            ?> 
    <main>
        <!---------------------------------------------sidebar--------------------------------------------------->
        <?php include_once "inc/sidebar.php";?>
        <!---------------------------------------------Cuerpo principal--------------------------------------------------->
            <div class="main-content">
                <!--------------------------------------------- contenido de platillas y vistas--------------------------------------------------->
            <?php include_once $vistas;?>
            <?php
                // Configuración de conexión
                $host = "localhost";      // o la IP del servidor
                $usuario = "root";        // tu usuario MySQL
                $clave = "";              // tu contraseña
                $bd = "samfarm_db";       // el nombre de tu base de datos

                try {
                    // Intentamos conectar
                    $conexion = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $usuario, $clave);
                    // Configuramos el modo de errores
                    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    echo "✅ Conexión exitosa a la base de datos '$bd'";
                } catch (PDOException $e) {
                    // Si falla la conexión, mostramos el error
                    echo "❌ Error de conexión: " . $e->getMessage();
                }?>


        </div>

    </main>

    <!---------------------------------------------Pie de pagina--------------------------------------------------->

    <?php include_once "inc/footer.php"; ?>
    <!---------------- -----------------------------Script--------------------------------------------------->

    <?php 
        
        }
        
        include_once "inc/script.php";


        include_once "inc/footer.php";
        ?>

    


</body>

</html>

