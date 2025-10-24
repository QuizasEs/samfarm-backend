<?php 
/* indicamos que se envia esta petito via ajax */

$peticionAjax = true;

/* importar la configuracion general de la aplicacion (rutas, constantes, conexxiones, etc) */


header('content-Type: aplication/json; charset=uft-8');


if(isset($_POST['MedicamentoAjax'])){

    $valor=$_POST['MedicamentoAjax'];
    
    require_once "../controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    if ($valor == "save"){
        echo $ins_med->agregar_medicamento_controller();
    }
    if ($valor == "update"){
        echo $ins_med->actualizar_medicamento_controller();
    }
}

?>