<?php

$peticionAjax = true;

require_once "../config/APP.php";

header('Content-Type: application/json; charset=utf-8');

session_start(['name' => 'SMP']);

if (!isset($_SESSION['id_smp']) || empty($_SESSION['id_smp'])) {
    session_unset();
    session_destroy();

    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Sesión Expirada',
        'Texto' => 'Por favor vuelva a iniciar sesión',
        'Tipo' => 'error'
    ]);
    exit();
}

$rol_usuario = $_SESSION['rol_smp'] ?? 0;
if ($rol_usuario != 1 && $rol_usuario != 2) {
    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Acceso Denegado',
        'Texto' => 'No tiene permisos para acceder a esta funcionalidad',
        'Tipo' => 'error'
    ]);
    exit();
}

require_once "../controllers/mermaController.php";
$ins_merma = new mermaController();

if (!isset($_POST['mermaRegistrarAjax'])) {
    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Error',
        'Texto' => 'Parámetro inválido',
        'Tipo' => 'error'
    ]);
    exit();
}

$valor = $_POST['mermaRegistrarAjax'];

if ($valor === "crear") {
    $resultado = $ins_merma->crear_merma_controller();
    echo json_encode($resultado);

} elseif ($valor === "listar") {
    $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
    $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
    $registros = isset($_POST['registros']) ? (int)$_POST['registros'] : 10;
    $busqueda = isset($_POST['busqueda']) ? trim($_POST['busqueda']) : '';
    $select1 = isset($_POST['select1']) ? trim($_POST['select1']) : '';
    $select2 = isset($_POST['select2']) ? trim($_POST['select2']) : '';

    $lotes_caducidad = $ins_merma->obtener_lotes_caducidad_controller();

    if (!is_array($lotes_caducidad) || count($lotes_caducidad) === 0) {
        header('Content-Type: text/html; charset=utf-8');
        echo '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Sucursal</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#999;">
                                <ion-icon name="checkmark-circle-outline" style="font-size:48px;margin-bottom:10px;"></ion-icon>
                                <h3>Sin lotes con riesgo de caducidad</h3>
                                <p>No hay productos próximos a vencer o caducados en este momento.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        ';
        exit();
    }

    $lotes_filtrados = $lotes_caducidad;

    if (!empty($busqueda)) {
        $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($busqueda) {
            $busqueda_lower = strtolower($busqueda);
            return stripos($lote['med_nombre_quimico'], $busqueda_lower) !== false ||
                   stripos($lote['lm_numero_lote'], $busqueda_lower) !== false;
        });
    }

    if (!empty($select1)) {
        $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($select1) {
            $fecha_venc = strtotime($lote['lm_fecha_vencimiento']);
            $hoy = time();
            $diferencia_dias = ceil(($fecha_venc - $hoy) / (60 * 60 * 24));

            if ($select1 === 'caducado') {
                return $diferencia_dias <= 0;
            } elseif ($select1 === 'proximo') {
                return $diferencia_dias > 0 && $diferencia_dias <= 10;
            }
            return true;
        });
    }

    if (!empty($select2) && $rol_usuario == 1) {
        $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($select2) {
            return $lote['su_id'] == $select2;
        });
    } elseif ($rol_usuario == 2) {
        $lotes_filtrados = array_filter($lotes_filtrados, function($lote) use ($sucursal_usuario) {
            return $lote['su_id'] == $sucursal_usuario;
        });
    }

    $lotes_filtrados = array_values($lotes_filtrados);

    $total = count($lotes_filtrados);
    $total_paginas = ceil($total / $registros);
    $inicio = ($pagina - 1) * $registros;
    $lotes_pagina = array_slice($lotes_filtrados, $inicio, $registros);

    header('Content-Type: text/html; charset=utf-8');

    $tabla = '
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>MEDICAMENTO</th>
                        <th>LOTE</th>
                        <th>SUCURSAL</th>
                        <th>VENCIMIENTO</th>
                        <th>ESTADO</th>
                        <th>CANTIDAD</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
    ';

    if (count($lotes_pagina) > 0) {
        $contador = $inicio + 1;
        foreach ($lotes_pagina as $lote) {
            $fecha_venc = strtotime($lote['lm_fecha_vencimiento']);
            $hoy = time();
            $diferencia_dias = ceil(($fecha_venc - $hoy) / (60 * 60 * 24));

            if ($diferencia_dias <= 0) {
                $estado_html = '<span class="estado-badge caducado" style="font-weight:bold;">CADUCADO</span>';
            } elseif ($diferencia_dias <= 10) {
                $estado_html = '<span class="estado-badge espera" style="font-weight:bold;">' . $diferencia_dias . ' días</span>';
            } else {
                $estado_html = '<span>' . $diferencia_dias . ' días</span>';
            }

            $tabla .= '
                <tr>
                    <td>' . $contador . '</td>
                    <td>
                        <strong>' . htmlspecialchars($lote['med_nombre_quimico'] ?? '') . '</strong>
                    </td>
                    <td>' . htmlspecialchars($lote['lm_numero_lote'] ?? 'N/A') . '</td>
                    <td>' . htmlspecialchars($lote['su_nombre'] ?? '') . '</td>
                    <td>' . htmlspecialchars($lote['lm_fecha_vencimiento']) . '</td>
                    <td>' . $estado_html . '</td>
                    <td>' . number_format($lote['lm_cant_actual_unidades'], 0) . ' unidades</td>
                    <td class="buttons">
                        <button type="button" class="btn success btn-sm" onclick="abrirModalMermaRegistro(' . (int)$lote['lm_id'] . ', \'' . htmlspecialchars($lote['med_nombre_quimico']) . '\', ' . (int)$lote['lm_cant_actual_unidades'] . ')">
                            <ion-icon name="create-outline"></ion-icon> Registrar
                        </button>
                    </td>
                </tr>
            ';
            $contador++;
        }
    } else {
        $tabla .= '<tr><td colspan="8" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="file-tray-outline"></ion-icon> No hay lotes con riesgo de caducidad
                    </td></tr>';
    }

    $tabla .= '
                </tbody>
            </table>
        </div>
    ';

    if (count($lotes_pagina) > 0) {
        $reg_inicio = $inicio + 1;
        $reg_final = $inicio + count($lotes_pagina);
        $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
        
        if ($total_paginas > 1) {
            $tabla .= '<div class="pagination-custom" style="text-align: center; padding: 20px;">';
            for ($i = 1; $i <= $total_paginas; $i++) {
                $clase_activa = ($i == $pagina) ? 'active' : '';
                $tabla .= '<a href="#" class="page-link ' . $clase_activa . '" data-page="' . $i . '">' . $i . '</a> ';
            }
            $tabla .= '</div>';
        }
    }

    echo $tabla;

} else {
    echo json_encode([
        'Alerta' => 'simple',
        'Titulo' => 'Error',
        'Texto' => 'Acción no válida',
        'Tipo' => 'error'
    ]);
}
