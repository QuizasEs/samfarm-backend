<?php

if ($peticionAjax) {
    require_once '../models/sucursalModel.php';
} else {
    require_once './models/sucursalModel.php';
}

class sucursalController extends sucursalModel
{
    public function listar_sucursales_controller()
    {
        $busqueda = isset($_POST['busqueda']) ? mainModel::limpiar_cadena($_POST['busqueda']) : '';
        $estado = isset($_POST['estado']) ? mainModel::limpiar_cadena($_POST['estado']) : '';

        try {
            $stmt = self::listar_sucursales_model($busqueda, $estado);
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'error' => false,
                'sucursales' => $sucursales
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en listar_sucursales_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar sucursales'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cajas_abiertas_controller()
    {
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($su_id <= 0) {
            return json_encode([
                'error' => true,
                'mensaje' => 'ID de sucursal inválido'
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            $stmt = self::cajas_abiertas_model($su_id);
            $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $usuarios_stmt = self::usuarios_con_cajas_abiertas_model($su_id);
            $usuarios = $usuarios_stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'error' => false,
                'cajas' => $cajas,
                'usuarios' => $usuarios
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en cajas_abiertas_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar cajas'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function toggle_estado_controller()
    {
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;
        $nuevo_estado = isset($_POST['nuevo_estado']) ? (int)$_POST['nuevo_estado'] : 0;

        if ($su_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ID de sucursal inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if (!in_array($nuevo_estado, [0, 1])) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Estado inválido',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if ($nuevo_estado === 0) {
            $usuarios_stmt = self::personal_por_sucursal_model($su_id);
            $usuarios = $usuarios_stmt->fetchAll(PDO::FETCH_ASSOC);
            $usuarios_activos = array_filter($usuarios, function($u) { return (int)$u['us_estado'] === 1; });

            if (count($usuarios_activos) > 0) {
                $nombres_usuarios = implode(', ', array_map(function($u) { 
                    return $u['us_nombres'] . ' ' . ($u['us_apellido_paterno'] ?? ''); 
                }, $usuarios_activos));
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'No se puede desactivar',
                    'texto' => "Existen usuarios activos en esta sucursal: {$nombres_usuarios}",
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            $cajas_stmt = self::cajas_abiertas_model($su_id);
            $cajas_abiertas = $cajas_stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($cajas_abiertas) > 0) {
                $nombres_cajas = implode(', ', array_map(function($c) { return $c['caja_nombre']; }, $cajas_abiertas));
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'No se puede desactivar',
                    'texto' => "Existen cajas abiertas en esta sucursal: {$nombres_cajas}",
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }
        }

        try {
            $resultado = self::toggle_estado_model($su_id, $nuevo_estado);

            if ($resultado->rowCount() > 0) {
                $accion = $nuevo_estado === 1 ? 'activada' : 'desactivada';
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Estado actualizado',
                    'texto' => "La sucursal fue {$accion} correctamente",
                    'Tipo' => 'success'
                ];
            } else {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Sin cambios',
                    'texto' => 'No se pudo actualizar el estado',
                    'Tipo' => 'warning'
                ];
            }

            echo json_encode($alerta);
            exit();
        } catch (Exception $e) {
            error_log("Error en toggle_estado_controller: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'No se pudo cambiar el estado de la sucursal',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    public function exportar_pdf_controller()
    {
        try {
            $stmt = self::listar_sucursales_model('', '');
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($sucursales)) {
                echo "No hay datos para exportar";
                return;
            }

            $filename = "Sucursales_" . date('Y-m-d_His') . ".pdf";

            $root = dirname(__DIR__);
            require_once $root . "/libs/fpdf/fpdf.php";

            $pdf = new FPDF('P', 'mm', 'Letter');
            $pdf->AddPage();
            $pdf->SetMargins(15, 15, 15);

            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'REPORTE DE SUCURSALES'), 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, 'Fecha: ' . date('d/m/Y H:i:s'), 0, 1);
            $pdf->Cell(0, 5, 'Usuario: ' . ($_SESSION['nombre_smp'] ?? 'Sistema'), 0, 1);
            $pdf->Ln(5);

            $pdf->SetFillColor(52, 73, 94);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Arial', 'B', 9);

            $pdf->Cell(10, 8, 'N', 1, 0, 'C', true);
            $pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Nombre'), 1, 0, 'C', true);
            $pdf->Cell(60, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Dirección'), 1, 0, 'C', true);
            $pdf->Cell(25, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'Teléfono'), 1, 0, 'C', true);
            $pdf->Cell(15, 8, 'Cajas', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'Estado', 1, 1, 'C', true);

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 8);

            $contador = 1;
            foreach ($sucursales as $suc) {
                $estado = (int)$suc['su_estado'] === 1 ? 'ACTIVA' : 'INACTIVA';
                $fillColor = (int)$suc['su_estado'] === 1 ? [232, 245, 233] : [255, 235, 238];

                $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);

                $pdf->Cell(10, 7, $contador, 1, 0, 'C', true);
                $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($suc['su_nombre'], 0, 30)), 1, 0, 'L', true);
                $pdf->Cell(60, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', substr($suc['su_direccion'] ?? 'N/A', 0, 30)), 1, 0, 'L', true);
                $pdf->Cell(25, 7, $suc['su_telefono'] ?? 'N/A', 1, 0, 'C', true);
                $pdf->Cell(15, 7, $suc['cajas_abiertas'], 1, 0, 'C', true);
                $pdf->Cell(20, 7, $estado, 1, 1, 'C', true);

                $contador++;
            }

            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', 'SAMFARM PHARMA - Sistema de Gestión Farmacéutica'), 0, 1, 'C');

            $pdf->Output('I', $filename);
        } catch (Exception $e) {
            error_log("Error exportando PDF: " . $e->getMessage());
            echo "Error al generar archivo: " . $e->getMessage();
        }
    }

    public function nueva_sucursal_controller()
    {
        $nombre = isset($_POST['Nombre_reg']) ? mainModel::limpiar_cadena($_POST['Nombre_reg']) : '';
        $direccion = isset($_POST['Direccion_reg']) ? mainModel::limpiar_cadena($_POST['Direccion_reg']) : '';
        $telefono = isset($_POST['Telefono_reg']) ? mainModel::limpiar_cadena($_POST['Telefono_reg']) : '';

        if (empty($nombre) || empty($direccion)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'El nombre y la dirección son obligatorios',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        try {
            $existe = self::verificar_nombre_sucursal_model($nombre);
            if ($existe->rowCount() > 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Nombre duplicado',
                    'texto' => 'Ya existe una sucursal con este nombre',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            $datos = [
                'su_nombre' => $nombre,
                'su_direccion' => $direccion,
                'su_telefono' => $telefono
            ];

            $resultado = self::agregar_sucursal_model($datos);

            if ($resultado->rowCount() > 0) {
                $alerta = [
                    'Alerta' => 'recargar',
                    'Titulo' => 'Sucursal registrada',
                    'texto' => 'La sucursal fue registrada correctamente',
                    'Tipo' => 'success'
                ];
            } else {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo registrar la sucursal',
                    'Tipo' => 'error'
                ];
            }

            echo json_encode($alerta);
            exit();
        } catch (Exception $e) {
            error_log("Error en nueva_sucursal_controller: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Ocurrió un error al registrar la sucursal',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    public function editar_sucursal_controller()
    {
        $su_id = isset($_POST['su_id_editar']) ? (int)$_POST['su_id_editar'] : 0;
        $nombre = isset($_POST['Nombre_edit']) ? mainModel::limpiar_cadena($_POST['Nombre_edit']) : '';
        $direccion = isset($_POST['Direccion_edit']) ? mainModel::limpiar_cadena($_POST['Direccion_edit']) : '';
        $telefono = isset($_POST['Telefono_edit']) ? mainModel::limpiar_cadena($_POST['Telefono_edit']) : '';

        if ($su_id <= 0 || empty($nombre) || empty($direccion)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'Todos los campos obligatorios deben estar llenos',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        try {
            $existe = self::verificar_nombre_sucursal_editar_model($nombre, $su_id);
            if ($existe->rowCount() > 0) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Nombre duplicado',
                    'texto' => 'Ya existe otra sucursal con este nombre',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            $datos = [
                'su_id' => $su_id,
                'su_nombre' => $nombre,
                'su_direccion' => $direccion,
                'su_telefono' => $telefono
            ];

            $resultado = self::actualizar_sucursal_model($datos);

            if ($resultado->rowCount() > 0) {
                $alerta = [
                    'Alerta' => 'recargar',
                    'Titulo' => 'Sucursal actualizada',
                    'texto' => 'Los datos fueron actualizados correctamente',
                    'Tipo' => 'success'
                ];
            } else {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Sin cambios',
                    'texto' => 'No se realizaron cambios en los datos',
                    'Tipo' => 'warning'
                ];
            }

            echo json_encode($alerta);
            exit();
        } catch (Exception $e) {
            error_log("Error en editar_sucursal_controller: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Ocurrió un error al actualizar la sucursal',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    public function obtener_sucursal_controller()
    {
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($su_id <= 0) {
            return json_encode([
                'error' => true,
                'mensaje' => 'ID inválido'
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            $stmt = self::obtener_sucursal_model($su_id);
            $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sucursal) {
                return json_encode([
                    'error' => true,
                    'mensaje' => 'Sucursal no encontrada'
                ], JSON_UNESCAPED_UNICODE);
            }

            return json_encode([
                'error' => false,
                'sucursal' => $sucursal
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en obtener_sucursal_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al obtener datos'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function detalle_sucursal_controller()
    {
        $su_id = isset($_POST['su_id']) ? (int)$_POST['su_id'] : 0;

        if ($su_id <= 0) {
            return json_encode([
                'error' => true,
                'mensaje' => 'ID inválido'
            ], JSON_UNESCAPED_UNICODE);
        }

        try {
            $sucursal_stmt = self::obtener_sucursal_model($su_id);
            $sucursal = $sucursal_stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sucursal) {
                throw new Exception('Sucursal no encontrada');
            }

            $personal_stmt = self::personal_por_sucursal_model($su_id);
            $personal = $personal_stmt->fetchAll(PDO::FETCH_ASSOC);

            $estadisticas_stmt = self::estadisticas_ventas_sucursal_model($su_id);
            $estadisticas = $estadisticas_stmt->fetch(PDO::FETCH_ASSOC);

            $ventas_usuarios_stmt = self::ventas_por_usuario_sucursal_model($su_id);
            $ventas_usuarios = $ventas_usuarios_stmt->fetchAll(PDO::FETCH_ASSOC);

            return json_encode([
                'error' => false,
                'sucursal' => $sucursal,
                'personal' => $personal,
                'estadisticas' => $estadisticas,
                'ventas_usuarios' => $ventas_usuarios
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_sucursal_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar detalle'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    public function costo_beneficio_controller()
    {
        $periodo = isset($_POST['periodo']) ? mainModel::limpiar_cadena($_POST['periodo']) : 'semestre';

        $periodos_validos = ['mes', 'trimestre', 'semestre', 'anio'];
        if (!in_array($periodo, $periodos_validos)) {
            $periodo = 'semestre';
        }

        error_log("Cargando costo-beneficio para periodo: " . $periodo);

        try {
            $stmt = self::costo_beneficio_sucursales_model($periodo);
            $sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Sucursales encontradas: " . count($sucursales));

            return json_encode([
                'error' => false,
                'sucursales' => $sucursales,
                'periodo' => $periodo
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en costo_beneficio_controller: " . $e->getMessage());
            return json_encode([
                'error' => true,
                'mensaje' => 'Error al cargar datos'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
