<?php

if ($peticionAjax) {
    require_once '../models/proveedorModel.php';
} else {
    require_once './models/proveedorModel.php';
}

class proveedorController extends proveedorModel
{
    public function paginado_proveedor_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "", $f5 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3> Acceso Denegado</h3>
                        <p>No tiene permisos para ver esta sección</p>
                    </div>';
        }

        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = mainModel::limpiar_cadena($url);
        $url = SERVER_URL . $url . '/';
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $f1 = mainModel::limpiar_cadena($f1);
        $f2 = mainModel::limpiar_cadena($f2);
        $f3 = mainModel::limpiar_cadena($f3);
        $f4 = mainModel::limpiar_cadena($f4);
        $f5 = mainModel::limpiar_cadena($f5);

        $tabla = '';
        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $filtros = [];

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        if ($f1 !== '') {
            $filtros['estado'] = $f1;
        }

        if ($f2 !== '') {
            $filtros['con_compras'] = $f2;
        }

        if ($f3 !== '') {
            $filtros['ultima_compra'] = $f3;
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        try {
            $conexion = mainModel::conectar();
            $datosStmt = self::datos_proveedores_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);
            $total = self::contar_proveedores_model($filtros);
        } catch (PDOException $e) {
            error_log(" ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>PROVEEDOR</th>
                            <th>CONTACTO</th>
                            <th>ESTADÍSTICAS</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $nombre_completo = $row['pr_razon_social'] ?? '';

                $estado_html = $row['pr_estado'] == 1
                    ? '<span class="badge bgr"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="badge bgry"><ion-icon name="ban-outline"></ion-icon> Inactivo</span>';

                $ultima_compra = $row['ultima_compra']
                    ? date('d/m/Y', strtotime($row['ultima_compra']))
                    : '<span style="color:#999;">Nunca</span>';

                $dias_ultima = $row['dias_ultima_compra'];
                if ($dias_ultima !== null && $dias_ultima > 90) {
                    $ultima_compra .= '<br><small style="color:orange;"><ion-icon name="alert-outline"></ion-icon> Hace ' . $dias_ultima . ' días</small>';
                }

                $nombre_comercial = $row['pr_nombre_comercial'] ?? '-';
                if (strlen($nombre_comercial) > 30) {
                    $nombre_comercial = substr($nombre_comercial, 0, 30) . '...';
                }

                $tabla .= '
                    <tr class="tr-click">
                        <td onclick="ProveedoresModals.verDetalle(' . $row['pr_id'] . ', \'' . addslashes($nombre_completo) . '\')">
                            <div class="td-main"><strong>' . htmlspecialchars($nombre_completo) . '</strong></div>
                            <div class="td-sub">' . htmlspecialchars($row['pr_nit'] ?? '-') . ' · ' . htmlspecialchars($nombre_comercial) . '</div>
                        </td>
                        <td onclick="ProveedoresModals.verDetalle(' . $row['pr_id'] . ', \'' . addslashes($nombre_completo) . '\')">
                            <div class="td-main">' . htmlspecialchars($row['pr_telefono'] ?? '-') . '</div>
                            <div class="td-sub">' . htmlspecialchars($row['pr_correo'] ?? '-') . '</div>
                        </td>
                        <td onclick="ProveedoresModals.verDetalle(' . $row['pr_id'] . ', \'' . addslashes($nombre_completo) . '\')">
                            <div class="td-main"><strong style="color:#1976D2;">' . number_format($row['total_compras']) . ' compras</strong></div>
                            <div class="td-sub">Última: ' . $ultima_compra . '</div>
                        </td>
                        <td onclick="ProveedoresModals.verDetalle(' . $row['pr_id'] . ', \'' . addslashes($nombre_completo) . '\')">
                            ' . $estado_html . '
                            <div class="td-sub">Registrado: ' . date('d/m/Y', strtotime($row['pr_creado_en'])) . '</div>
                        </td>
                        <td class="buttons">
                            <a href="javascript:void(0)"
                            class="btn btn-def"
                            title="Editar proveedor"
                            onclick="event.stopPropagation(); ProveedoresModals.abrirEdicion(' . $row['pr_id'] . ')">
                                <ion-icon name="create-outline"></ion-icon>editar
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="5" style="text-align:center;padding:20px;color:#999;">
                        <ion-icon name="people-outline"></ion-icon> No hay registros
                    </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }

    public function detalle_proveedor_controller()
    {
        $pr_id = isset($_POST['pr_id']) ? (int)$_POST['pr_id'] : 0;

        if ($pr_id <= 0) {
            return json_encode(['error' => 'Parámetros inválidos']);
        }

        try {
            $stmt = self::detalle_proveedor_model($pr_id);
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$proveedor) {
                return json_encode(['error' => 'Proveedor no encontrado']);
            }

            $ultimasComprasStmt = self::ultimas_compras_proveedor_model($pr_id, 5);
            $ultimasCompras = $ultimasComprasStmt->fetchAll(PDO::FETCH_ASSOC);

            $topMedicamentosStmt = self::top_medicamentos_proveedor_model($pr_id, 5);
            $topMedicamentos = $topMedicamentosStmt->fetchAll(PDO::FETCH_ASSOC);

            $nombre_completo = $proveedor['pr_razon_social'] ?? '';

            $promedio = $proveedor['total_compras'] > 0
                ? $proveedor['monto_total_compras'] / $proveedor['total_compras']
                : 0;

            $response = [
                'nombre_completo' => $nombre_completo,
                'nit' => $proveedor['pr_nit'] ?? '-',
                'telefono' => $proveedor['pr_telefono'] ?? '-',
                'correo' => $proveedor['pr_correo'] ?? '-',
                'direccion' => $proveedor['pr_nombre_comercial'] ?? '-',
                'fecha_registro' => date('d/m/Y', strtotime($proveedor['pr_creado_en'])),
                'estado' => $proveedor['pr_estado'] == 1 ? 'Activo' : 'Inactivo',
                'total_compras' => (int)$proveedor['total_compras'],
                'monto_total' => (float)$proveedor['monto_total_compras'],
                'total_lotes' => (int)$proveedor['total_lotes'],
                'ultima_compra' => $proveedor['ultima_compra'] ? date('d/m/Y', strtotime($proveedor['ultima_compra'])) : 'Nunca',
                'promedio' => $promedio,
                'antiguedad' => (int)$proveedor['dias_antiguedad'],
                'ultimas_compras' => $ultimasCompras,
                'top_medicamentos' => $topMedicamentos
            ];

            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error en detalle_proveedor_controller: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar detalle: ' . $e->getMessage()]);
        }
    }

    public function exportar_proveedores_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;

        if ($rol_usuario == 3) {
            echo "Acceso denegado";
            return;
        }

        $filtros = [];

        if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $filtros['busqueda'] = mainModel::limpiar_cadena($_GET['busqueda']);
        }

        if (isset($_GET['select1']) && !empty($_GET['select1'])) {
            $filtros['estado'] = mainModel::limpiar_cadena($_GET['select1']);
        }

        // Los filtros de compras están temporalmente deshabilitados hasta implementar la relación compras-proveedores
        // if (isset($_GET['select2']) && !empty($_GET['select2'])) {
        //     $filtros['con_compras'] = mainModel::limpiar_cadena($_GET['select2']);
        // }

        // if (isset($_GET['select3']) && !empty($_GET['select3'])) {
        //     $filtros['ultima_compra'] = mainModel::limpiar_cadena($_GET['select3']);
        // }

        if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = mainModel::limpiar_cadena($_GET['fecha_desde']);
        }

        if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = mainModel::limpiar_cadena($_GET['fecha_hasta']);
        }

        try {
            $stmt = self::exportar_proveedores_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $filename = "Proveedores_{$fecha}.xls";

            $headers = array_keys($datos[0]);

            $info_superior = [
                'Fecha de Generación' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema',
                'Total de Registros' => count($datos)
            ];

            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $info_superior['Rango de Fechas'] = date('d/m/Y', strtotime($filtros['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
            }

            mainModel::generar_excel_reporte([
                'titulo' => 'REPORTE DE PROVEEDORES',
                'datos' => $datos,
                'headers' => $headers,
                'nombre_archivo' => $filename,
                'formato_columnas' => [
                    'Total Compras' => 'numero',
                    'Total Lotes' => 'numero'
                ],
                'columnas_totales' => [],
                'info_superior' => $info_superior
            ]);

        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . $e->getMessage();
        }
    }
    /* controlador para registrar o editar  */
    public function registrar_proveedor_controller()
    {
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_pr'] ?? '');
        $nit = mainModel::limpiar_cadena($_POST['Nit_pr'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_pr'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['Correo_pr'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_pr'] ?? '');
        /* verificar que los campos ablgatorios no esten vacios */
        if (empty($nombres) || empty($nit)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'Debe ingresar al menos el nombre y el NIT',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
        /* verificar la integridad de los datos  */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $nombres)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NOMBRE no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (mainModel::verificar_datos("[0-9]{6,30}", $nit)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NIT no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (!empty($telefono)) {
            if (mainModel::verificar_datos("[0-9]{6,30}", $telefono)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El TELEFONO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }
        /*  el nit no deve repetirse */
        $verificar_nit = self::verificar_nit_duplicado_model($nit);
        if ($verificar_nit->rowCount() > 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'NIT duplicado',
                'texto' => 'Ya existe un proveedor registrado con este NIT',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $datos = [
            'nombres' => $nombres,
            'nit' => $nit,
            'telefono' => $telefono,
            'correo' => $correo,
            'direccion' => $direccion
        ];

        try {
            $registrar = self::registrar_proveedor_model($datos);

            if ($registrar->rowCount() == 1) {
                $alerta = [
                    'Alerta' => 'recargar',
                    'Titulo' => 'Proveedor registrado',
                    'texto' => 'El proveedor se registró correctamente',
                    'Tipo' => 'success'
                ];
            } else {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo registrar el proveedor',
                    'Tipo' => 'error'
                ];
            }
        } catch (Exception $e) {
            error_log("Error registrando proveedor: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Ocurrió un error al registrar el proveedor',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function obtener_proveedor_controller()
    {
        $pr_id = isset($_POST['pr_id']) ? (int)$_POST['pr_id'] : 0;

        if ($pr_id <= 0) {
            return json_encode(['error' => 'ID inválido']);
        }

        try {
            $stmt = self::obtener_proveedor_por_id_model($pr_id);
            $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$proveedor) {
                return json_encode(['error' => 'Proveedor no encontrado']);
            }

            return json_encode($proveedor, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            error_log("Error obteniendo proveedor: " . $e->getMessage());
            return json_encode(['error' => 'Error al cargar datos']);
        }
    }

    public function actualizar_proveedor_controller()
    {
        $pr_id = mainModel::limpiar_cadena($_POST['PrId_up'] ?? '');
        $nombres = mainModel::limpiar_cadena($_POST['Nombres_pr_up'] ?? '');
        $nit = mainModel::limpiar_cadena($_POST['Nit_pr_up'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['Telefono_pr_up'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['Correo_pr_up'] ?? '');
        $direccion = mainModel::limpiar_cadena($_POST['Direccion_pr_up'] ?? '');

        if (empty($pr_id) || empty($nombres) || empty($nit)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Campos incompletos',
                'texto' => 'Debe completar los campos obligatorios',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* verificar la integridad de los datos  */
        if (mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}", $nombres)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NOMBRE no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (mainModel::verificar_datos("[0-9]{6,30}", $nit)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "ocurrio un error inesperado",
                "texto" => "El NIT no coincide con el formato solicitado!",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        };
        if (!empty($telefono)) {
            if (mainModel::verificar_datos("[0-9]{6,30}", $telefono)) {
                $alerta = [
                    "Alerta" => "simple",
                    "Titulo" => "ocurrio un error inesperado",
                    "texto" => "El TELEFONO no coincide con el formato solicitado!",
                    "Tipo" => "error"
                ];
                echo json_encode($alerta);
                exit();
            };
        }

        $verificar = self::obtener_proveedor_por_id_model($pr_id);
        if ($verificar->rowCount() == 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'El proveedor no existe',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $verificar_nit = self::verificar_nit_duplicado_model($nit, $pr_id);
        if ($verificar_nit->rowCount() > 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'NIT duplicado',
                'texto' => 'Ya existe otro proveedor con este NIT',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $datos = [
            'pr_id' => $pr_id,
            'nombres' => $nombres,
            'nit' => $nit,
            'telefono' => $telefono,
            'correo' => $correo,
            'direccion' => $direccion
        ];

        try {
            $actualizar = self::actualizar_proveedor_model($datos);

            if ($actualizar->rowCount() >= 0) {
                $alerta = [
                    'Alerta' => 'recargar',
                    'Titulo' => 'Proveedor actualizado',
                    'texto' => 'Los datos se actualizaron correctamente',
                    'Tipo' => 'success'
                ];
            } else {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo actualizar el proveedor',
                    'Tipo' => 'error'
                ];
            }
        } catch (Exception $e) {
            error_log("Error actualizando proveedor: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Ocurrió un error al actualizar',
                'Tipo' => 'error'
            ];
        }

        echo json_encode($alerta);
        exit();
    }

    public function exportar_pdf_proveedores_controller()
    {
        $filtros = [];

        if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $filtros['busqueda'] = mainModel::limpiar_cadena($_GET['busqueda']);
        }

        if (isset($_GET['select1']) && !empty($_GET['select1'])) {
            $filtros['estado'] = mainModel::limpiar_cadena($_GET['select1']);
        }

        // Los filtros de compras están temporalmente deshabilitados hasta implementar la relación compras-proveedores
        // if (isset($_GET['select2']) && !empty($_GET['select2'])) {
        //     $filtros['con_compras'] = mainModel::limpiar_cadena($_GET['select2']);
        // }

        // if (isset($_GET['select3']) && !empty($_GET['select3'])) {
        //     $filtros['ultima_compra'] = mainModel::limpiar_cadena($_GET['select3']);
        // }

        if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = mainModel::limpiar_cadena($_GET['fecha_desde']);
        }

        if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = mainModel::limpiar_cadena($_GET['fecha_hasta']);
        }

        try {
            $stmt = self::exportar_proveedores_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar con los filtros aplicados.";
                return;
            }

            $periodo = '';
            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $periodo = date('d/m/Y', strtotime($filtros['fecha_desde'])) . ' al ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
            } else {
                $periodo = 'Todo el período';
            }

            $info_superior = [
                'Periodo' => $periodo,
                'Total de Proveedores' => count($datos),
                'Proveedores Activos' => count(array_filter($datos, function($p) { return $p['Estado'] == 'ACTIVO'; })),
                'Proveedores Inactivos' => count(array_filter($datos, function($p) { return $p['Estado'] == 'INACTIVO'; })),
                'Generado' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema'
            ];

            $headers = [
                ['text' => 'N°', 'width' => 10],
                ['text' => 'PROVEEDOR', 'width' => 40],
                ['text' => 'NIT', 'width' => 25],
                ['text' => 'TELÉFONO', 'width' => 20],
                ['text' => 'CORREO', 'width' => 30],
                ['text' => 'NOMBRE COMERCIAL', 'width' => 35],
                ['text' => 'FECHA REGISTRO', 'width' => 25],
                ['text' => 'LOTES GENERADOS', 'width' => 25],
                ['text' => 'ESTADO', 'width' => 15]
            ];

            $rows = [];

            foreach ($datos as $index => $row) {
                $cells = [
                    ['text' => ($index + 1), 'align' => 'C'],
                    ['text' => substr($row['Proveedor'] ?? 'N/A', 0, 30), 'align' => 'L'],
                    ['text' => $row['NIT'] ?? 'N/A', 'align' => 'C'],
                    ['text' => $row['Teléfono'] ?? 'N/A', 'align' => 'C'],
                    ['text' => $row['Correo'] ?? '-', 'align' => 'L'],
                    ['text' => $row['Nombre Comercial'] ?? '-', 'align' => 'L'],
                    ['text' => $row['Fecha Registro'], 'align' => 'C'],
                    ['text' => $row['Lotes Generados'], 'align' => 'C'],
                    ['text' => $row['Estado'], 'align' => 'C']
                ];

                $rows[] = ['cells' => $cells];
            }

            $resumen = [
                'Total de Proveedores' => ['text' => count($datos)],
                'Lotes Totales Generados' => ['text' => array_sum(array_column($datos, 'Lotes Generados'))]
            ];

            $datos_pdf = [
                'titulo' => 'REPORTE DE PROVEEDORES',
                'nombre_archivo' => 'Proveedores_' . date('Y-m-d_His') . '.pdf',
                'info_superior' => $info_superior,
                'tabla' => [
                    'headers' => $headers,
                    'rows' => $rows
                ],
                'resumen' => $resumen
            ];

            // Generar y descargar PDF directamente
            $content = self::generar_pdf_reporte_fpdf($datos_pdf);

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $datos_pdf['nombre_archivo'] . '"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $content;
            exit();

        } catch (Exception $e) {
            error_log("Error exportando PDF proveedores: " . $e->getMessage());
            echo "Error al generar PDF: " . $e->getMessage();
        }
    }
}
