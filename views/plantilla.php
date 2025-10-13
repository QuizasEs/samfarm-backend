
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

