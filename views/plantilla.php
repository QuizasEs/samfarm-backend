<!DOCTYPE html>
<html lang="es" data-theme="light" data-sidebar="expanded" data-server-url="<?php echo SERVER_URL; ?>ajax/notificacionesAjax.php">

<?php include "inc/head.php"; ?>

<body>
    <?php
    $peticionAjax = false;
    require_once __DIR__ . "/../controllers/viewsController.php";

    $IV = new viewsController();
    $vistas = $IV->get_views_controller();

    $view_name = basename($vistas, '-view.php'); // extraer 'dashboard' de './views/content/dashboard-view.php'

    $page_title = 'Dashboard'; // default
    switch ($view_name) {
        case 'dashboard':
            $page_title = 'Dashboard';
            break;
        case 'inventarioLista':
            $page_title = 'Inventario';
            break;
        case 'loteLista':
            $page_title = 'Lotes';
            break;
        case 'recepcionarLista':
            $page_title = 'Recepcionar';
            break;
        case 'proveedorLista':
            $page_title = 'Proveedores';
            break;
        case 'sucursalLista':
            $page_title = 'Sucursales';
            break;
        case 'usuarioLista':
            $page_title = 'Usuarios';
            break;
        case 'clienteLista':
            $page_title = 'Clientes';
            break;
        case 'ventaLista':
            $page_title = 'Ventas';
            break;
        case 'compraLista':
            $page_title = 'Compras';
            break;
        case 'devolucion':
            $page_title = 'Devoluciones';
            break;
        // Agregar más vistas según sea necesario
        default:
            $clean = str_replace(['-view', 'Lista', 'Form'], ['', '', ''], $view_name);
            $page_title = ucfirst($clean);
            break;
    }

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



        /* dividir cadenas por "/"" */
        $pagina = explode("/", $_GET['views']);

    ?>
        <!---------------------------------------------sidebar--------------------------------------------------->
        <div class="sb-ov" onclick="App.closeMobile()"></div>
        <?php include_once "inc/sidebar.php"; ?>

        <div class="main">
            <!-- include de navbar o topbar -->
            <?php include_once "inc/header.php"; ?>
            <!---------------------------------------------Cuerpo principal--------------------------------------------------->
            <?php
            if ($_SESSION['rol_smp'] == 1) {
                /* iniciamos controller usuario si se tenen el privilegio necesario */
                require_once "./controllers/userController.php";
                $ins_usuario = new userController();
            }
            ?>
            <div class="pg">
                <!--------------------------------------------- contenido de platillas y vistas--------------------------------------------------->
                <?php include_once $vistas; ?>
            </div>
        </div>

        <!---------------- -----------------------------Script--------------------------------------------------->
        <?php
        include_once "inc/logOut.php";
        include_once "inc/script.php";
        ?>
    <?php
    } // CIERRE DEL BLOQUE ELSE
    ?>
</body>

</html>