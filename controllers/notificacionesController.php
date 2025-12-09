<?php

if ($peticionAjax) {
    require_once '../models/notificacionesModel.php';
} else {
    require_once './models/notificacionesModel.php';
}
class notificacionesController
{
    public static function obtener_notificaciones_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $su_id = $_SESSION['sucursal_smp'] ?? null;

        $resultado = notificacionesModel::obtener_notificaciones_controller($rol_usuario, $su_id);
        return json_encode([
            'error' => false,
            'notificaciones' => $resultado
        ]);
    }

    public static function marcar_como_leida_controller($id)
    {
        $resultado = notificacionesModel::marcar_como_leida_controller($id);
        return json_encode([
            'error' => !$resultado,
            'mensaje' => $resultado ? 'Marcada como leída' : 'Error al marcar como leída'
        ]);
    }

    public static function descartar_notificacion_controller($id)
    {
        $resultado = notificacionesModel::descartar_notificacion_controller($id);
        return json_encode([
            'error' => !$resultado,
            'mensaje' => $resultado ? 'Notificación descartada' : 'Error al descartar notificación'
        ]);
    }
}
