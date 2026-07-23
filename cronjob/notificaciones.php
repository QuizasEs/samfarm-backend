<?php

require_once dirname(__DIR__) . '/config/SERVER.php';
require_once dirname(__DIR__) . '/models/notificacionesModel.php';

try {
    notificacionesModel::generar_notificaciones_controller();
    echo date('Y-m-d H:i:s') . " - Notificaciones generadas correctamente\n";
    exit(0);
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Notificaciones ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
