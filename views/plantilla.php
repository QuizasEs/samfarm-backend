<!DOCTYPE html>
<html lang="en" data-server-url="<?php echo SERVER_URL; ?>ajax/notificacionesAjax.php">

<?php include "inc/head.php"; ?>

<body class="darkmode">
    <?php
    $peticionAjax = false;
    require_once __DIR__ . "/../controllers/viewsController.php";

    $IV = new viewsController();
    $vistas = $IV->get_views_controller();

    if ($vistas == "login" || $vistas == "404") {
        require_once "./views/content/" . $vistas . "-view.php";
    } else {
        /* inicializa sesion */
        session_start(['name' => 'SMP']);

        require_once "./controllers/loginController.php";
        $lc = new loginController();
        if (
            !isset($_SESSION['token_smp']) ||
            !isset($_SESSION['apellido_paterno_smp']) ||
            !isset($_SESSION['apellido_materno_smp']) ||
            !isset($_SESSION['nombre_smp']) ||
            !isset($_SESSION['usuario_smp'])
        ) {
            echo $lc->forzar_cierre_sesion_controller();
            exit();
        }
        include_once "inc/header.php";

        /* dividir cadenas por "/"" */
        $pagina = explode("/", $_GET['views']);

    ?>
        <main>
            <!---------------------------------------------sidebar--------------------------------------------------->
            <?php include_once "inc/sidebar.php"; ?>
            <!---------------------------------------------Cuerpo principal--------------------------------------------------->
            <?php
            if ($_SESSION['rol_smp'] == 1) {
                /* iniciamos controller usuario si se tenen el privilegio necesario */
                require_once "./controllers/userController.php";
                $ins_usuario = new userController();
            }
            ?>
            <div class="main-content">
                <!--------------------------------------------- contenido de platillas y vistas--------------------------------------------------->
                <?php include_once $vistas; ?>
            </div>
        </main>

        <!---------------- -----------------------------Script--------------------------------------------------->
        <?php
        include_once "inc/logOut.php";
        include_once "inc/script.php";
        include_once "inc/footer.php";
        ?>
    <?php
    } // CIERRE DEL BLOQUE ELSE
    ?>
</body>
</html>