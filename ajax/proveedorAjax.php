<?php 
/* indicamos que se envia esta petito via ajax */

$peticionAjax = true;

/* importar la configuracion general de la aplicacion (rutas, constantes, conexxiones, etc) */


header('content-Type: aplication/json; charset=uft-8');


if(isset($_POST['ProveedorAjax'])){

    $valor=$_POST['ProveedorAjax'];
    
    require_once "../controllers/proveedorController.php";
    $ins_pro = new proveedorController();
    if ($valor == "save"){
        echo $ins_pro->agregar_proveedor_controller();
    }
    if ($valor == "update"){
        echo $ins_pro->actualizar_proveedor_controller();
        
    }
}

?>