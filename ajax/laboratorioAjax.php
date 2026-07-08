<?php 
/* 
modulo deshabilitado 
la tabla laboratorios fue eliminada de la base de datos
*/

$peticionAjax = true;

header('content-Type: application/json; charset=utf-8');

echo json_encode([
    'alerta' => 'error',
    'titulo' => 'Modulo deshabilitado',
    'mensaje' => 'La funcionalidad de laboratorios ya no esta disponible'
]);

?>
