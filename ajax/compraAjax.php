<?php 
/* indicamos que se envia esta petito via ajax */

$peticionAjax = true;

/* importar la configuracion general de la aplicacion (rutas, constantes, conexxiones, etc) */


header('content-Type: aplication/json; charset=uft-8');


if(isset($_POST['compraAjax'])){

    $valor=$_POST['compraAjax'];
    
    require_once "../controllers/compraController.php";
    $ins_med = new compraController();
    if ($valor == "save"){
        
    }
    if ($valor == "update"){
        
    }
    if ($valor == "buscar_medicamentos") {
    $filtros = [
        'termino' => $_POST['termino'] ?? '',
        'forma' => $_POST['forma'] ?? '',
        'via' => $_POST['via'] ?? '',
        'laboratorio' => $_POST['laboratorio'] ?? '',
        'uso' => $_POST['uso'] ?? ''
    ];
    $resultados = $ins_med->buscar_medicamento_controller($filtros);
    echo json_encode($resultados->fetchAll(PDO::FETCH_ASSOC));
    exit();
}
}

?>