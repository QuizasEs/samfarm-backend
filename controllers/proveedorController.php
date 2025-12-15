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
                            <th>N°</th>
                            <th>PROVEEDOR/RAZON SOCIAL</th>
                            <th>NIT</th>
                            <th>TELÉFONO</th>
                            <th>DIRECCIÓN</th>
                            <th>FECHA REGISTRO</th>
                            <th>TOTAL COMPRAS</th>
                            <th>ÚLTIMA COMPRA</th>
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
                $nombre_completo = $row['pr_nombres'] ?? '';

                $estado_html = $row['pr_estado'] == 1
                    ? '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>'
                    : '<span class="estado-badge bloqueado"><ion-icon name="ban-outline"></ion-icon> Inactivo</span>';

                $ultima_compra = $row['ultima_compra']
                    ? date('d/m/Y', strtotime($row['ultima_compra']))
                    : '<span style="color:#999;">Nunca</span>';

                $dias_ultima = $row['dias_ultima_compra'];
                if ($dias_ultima !== null && $dias_ultima > 90) {
                    $ultima_compra .= '<br><small style="color:orange;"><ion-icon name="alert-outline"></ion-icon> Hace ' . $dias_ultima . ' días</small>';
                }

                $direccion = $row['pr_direccion'] ?? '-';
                if (strlen($direccion) > 30) {
                    $direccion = substr($direccion, 0, 30) . '...';
                }

                $tabla .= '
                    <tr>
                        <td>' . $contador . '</td>
                        <td><strong>' . htmlspecialchars($nombre_completo) . '</strong></td>
                        <td>' . htmlspecialchars($row['pr_nit'] ?? '-') . '</td>
                        <td>' . htmlspecialchars($row['pr_telefono'] ?? '-') . '</td>
                        <td>' . htmlspecialchars($direccion) . '</td>
                        <td>' . date('d/m/Y', strtotime($row['pr_creado_en'])) . '</td>
                        <td style="text-align:center;"><strong style="color:#1976D2;">' . number_format($row['total_compras']) . '</strong></td>
                        <td>' . $ultima_compra . '</td>
                        <td>' . $estado_html . '</td>
                        <td class="buttons">
                            <a href="javascript:void(0)"
                            class="btn default"
                            title="Ver detalle"
                            onclick="ProveedoresModals.verDetalle(' . $row['pr_id'] . ', \'' . addslashes($nombre_completo) . '\')">
                                <ion-icon name="eye-outline"></ion-icon> Detalle
                            </a>
                            <a href="javascript:void(0)"
                            class="btn primary"
                            title="Editar proveedor"
                            onclick="ProveedoresModals.abrirEdicion(' . $row['pr_id'] . ')">
                                <ion-icon name="create-outline"></ion-icon> Editar
                            </a>
                        </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="10" style="text-align:center;padding:20px;color:#999;">
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

            $nombre_completo = $proveedor['pr_nombres'] ?? '';

            $promedio = $proveedor['total_compras'] > 0
                ? $proveedor['monto_total_compras'] / $proveedor['total_compras']
                : 0;

            $response = [
                'nombre_completo' => $nombre_completo,
                'nit' => $proveedor['pr_nit'] ?? '-',
                'telefono' => $proveedor['pr_telefono'] ?? '-',
                'direccion' => $proveedor['pr_direccion'] ?? '-',
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
            return json_encode(['error' => 'Error al cargar detalle']);
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

        if (isset($_GET['select2']) && !empty($_GET['select2'])) {
            $filtros['con_compras'] = mainModel::limpiar_cadena($_GET['select2']);
        }

        if (isset($_GET['select3']) && !empty($_GET['select3'])) {
            $filtros['ultima_compra'] = mainModel::limpiar_cadena($_GET['select3']);
        }

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

        if (isset($_GET['select2']) && !empty($_GET['select2'])) {
            $filtros['con_compras'] = mainModel::limpiar_cadena($_GET['select2']);
        }

        if (isset($_GET['select3']) && !empty($_GET['select3'])) {
            $filtros['ultima_compra'] = mainModel::limpiar_cadena($_GET['select3']);
        }

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
                'Proveedores Activos' => count(array_filter($datos, function($p) { return $p['estado'] == 'Activo'; })),
                'Proveedores Inactivos' => count(array_filter($datos, function($p) { return $p['estado'] == 'Inactivo'; })),
                'Generado' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema'
            ];

            $headers = [
                ['text' => 'N°', 'width' => 10],
                ['text' => 'PROVEEDOR', 'width' => 40],
                ['text' => 'NIT', 'width' => 25],
                ['text' => 'TELÉFONO', 'width' => 20],
                ['text' => 'DIRECCIÓN', 'width' => 50],
                ['text' => 'FECHA REGISTRO', 'width' => 25],
                ['text' => 'TOTAL COMPRAS', 'width' => 25],
                ['text' => 'ÚLTIMA COMPRA', 'width' => 25],
                ['text' => 'ESTADO', 'width' => 15]
            ];

            $rows = [];
            $total_compras = 0;

            foreach ($datos as $index => $row) {
                $cells = [
                    ['text' => ($index + 1), 'align' => 'C'],
                    ['text' => substr($row['Nombres'] ?? 'N/A', 0, 30), 'align' => 'L'],
                    ['text' => $row['NIT'] ?? 'N/A', 'align' => 'C'],
                    ['text' => $row['Teléfono'] ?? 'N/A', 'align' => 'C'],
                    ['text' => substr($row['Dirección'] ?? 'N/A', 0, 35), 'align' => 'L'],
                    ['text' => date('d/m/Y', strtotime($row['Fecha Registro'])), 'align' => 'C'],
                    ['text' => 'Bs. ' . number_format($row['Total Compras'], 2), 'align' => 'R'],
                    ['text' => $row['Última Compra'] ? date('d/m/Y', strtotime($row['Última Compra'])) : 'Nunca', 'align' => 'C'],
                    ['text' => $row['Estado'], 'align' => 'C']
                ];

                $rows[] = ['cells' => $cells];
                $total_compras += $row['Total Compras'];
            }

            $resumen = [
                'Total de Proveedores' => ['text' => count($datos)],
                'Monto Total de Compras' => ['text' => 'Bs ' . number_format($total_compras, 2), 'color' => [46, 125, 50]]
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
