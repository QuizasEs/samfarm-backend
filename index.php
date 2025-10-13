<?php
    require_once "./config/APP.php";
    require_once "./controllers/viewsController.php";


    $plantilla = new viewsController();
    $plantilla ->get_plantilla_controller();
?>