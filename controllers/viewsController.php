<?php
    require_once __DIR__ . "/../models/viewsModel.php";

    class viewsController extends viewsModel{

        /* -------------------------------------------Controlador para obtener plantilla-------------------------------------------------- */
        public function get_plantilla_controller(){
            return require_once "./views/plantilla.php";
        }


        /* -------------------------------------Controlador para obtener dvistas------------------------------------------------- */
        public function get_views_controller(){
            if(isset($_GET['views'])){
                $ruta=explode("/", $_GET['views']);
                $respuesta = viewsModel::get_views_model($ruta[0]);
            }else{
                $respuesta = "login";
            }
            return $respuesta;
        }

        /* ------------------------------------- -------------------------------------------------- */
        /* ------------------------------------- -------------------------------------------------- */
        /* ------------------------------------- -------------------------------------------------- */
        /* ------------------------------------- -------------------------------------------------- */
        /* ------------------------------------- -------------------------------------------------- */


    }
?>