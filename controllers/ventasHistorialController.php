<?php

if ($peticionAjax) {
    require_once '../models/ventasHistorialModel.php';
    require_once '../models/siatModel.php';
} else {
    require_once './models/ventasHistorialModel.php';
    require_once './models/siatModel.php';
}
class ventasHistorialController extends ventasHistorialModel
{
    /* Paginador para el historial de ventas */
    public function paginado_ventas_historial_controller($pagina, $registros, $url, $busqueda = "", $f1 = "", $f2 = "", $f3 = "", $f4 = "", $f5 = "")
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        if ($rol_usuario == 3) {
            return '<div class="error" style="padding:30px;text-align:center;">
                        <h3> Acceso Denegado</h3>
                        <p>No tiene permisos para ver el historial de ventas</p>
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

        if ($rol_usuario == 1) {
            // Admin: puede ver todas o filtrar
            if ($f1 !== '') {
                $filtros['su_id'] = (int)$f1;
            }
        } elseif ($rol_usuario == 2) {
            // Gerente: solo su sucursal
            $filtros['su_id'] = $sucursal_usuario;
        }

        $fecha_desde = isset($_POST['fecha_desde']) ? mainModel::limpiar_cadena($_POST['fecha_desde']) : '';
        $fecha_hasta = isset($_POST['fecha_hasta']) ? mainModel::limpiar_cadena($_POST['fecha_hasta']) : '';

        if (!empty($fecha_desde) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_desde)) {
            $filtros['fecha_desde'] = $fecha_desde;
        }

        if (!empty($fecha_hasta) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_hasta)) {
            $filtros['fecha_hasta'] = $fecha_hasta;
        }

        if ($f2 !== '' && is_numeric($f2)) {
            $filtros['cliente'] = (int)$f2;
        }

        if ($f3 !== '' && is_numeric($f3)) {
            $filtros['vendedor'] = (int)$f3;
        }

        if ($f4 !== '') {
            $filtros['tipo_documento'] = $f4;
        }

        /* Estado de la factura electronica (select5). Se acepta solo un grupo
           conocido: el valor viene del formulario y se termina usando en el
           WHERE, asi que la lista blanca es lo que impide que alguien
           inyecte SQL por aqui. Los grupos se definen en el modelo. */
        if ($f5 !== '' && in_array($f5, ['revision', 'pendiente', 'aceptada', 'sin_factura'], true)) {
            $filtros['estado_siat'] = $f5;
        }

        if (!empty($busqueda)) {
            $filtros['busqueda'] = $busqueda;
        }

        try {
            $conexion = mainModel::conectar();

            $datosStmt = self::datos_ventas_historial_model($inicio, $registros, $filtros);
            $datos = $datosStmt->fetchAll(PDO::FETCH_ASSOC);

            $total = self::contar_ventas_historial_model($filtros);
        } catch (PDOException $e) {
            error_log(" ERROR SQL: " . $e->getMessage());
            return '<div class="error" style="padding:20px;color:red;">
                        <strong>Error en la consulta:</strong><br>' .
                htmlspecialchars($e->getMessage()) .
                '</div>';
        }

        $Npaginas = ceil($total / $registros);

        $mostrar_columna_sucursal = ($rol_usuario == 1 && empty($f1));
        $colspan_total = $mostrar_columna_sucursal ? 11 : 10;

        $tabla .= '
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>N° DOCUMENTO</th>
                            <th>FECHA</th>
                            <th>CLIENTE</th>
                            <th>VENDEDOR</th>' .
                            ($mostrar_columna_sucursal ? '<th>SUCURSAL</th>' : '') .
                            '
                            <th>TOTAL</th>
                            <th>N° FACTURA</th>
                            <th>TIPO DOC.</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
        ';

        if ($pagina <= $Npaginas && $total >= 1) {
            $contador = $inicio + 1;
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $cliente_display = !empty($row['cliente_nombre'])
                    ? htmlspecialchars($row['cliente_nombre'])
                    : '<span style="color:#999;">Sin cliente</span>';

                $tipo_doc = !empty($row['ve_tipo_documento'])
                    ? strtoupper($row['ve_tipo_documento'])
                    : 'VENTA';

                // Se distingue la factura electronica de la nota de venta.
                //
                // Una factura electronica se reconoce por su CUF: es el codigo
                // unico que se genera al emitirla y que el SIN exige. Si hay CUF
                // la venta SI paso por facturacion electronica, aunque todavia
                // no se haya enviado. Se muestra ademas el estado del envío
                // para que quede claro si el SIN ya la acepto.
                $es_factura_electronica = !empty($row['fa_cuf']);

                if ($es_factura_electronica) {
                    $tipo_doc_html = '<div class="badge bdef bsm">'
                        . '<span class="bdt"></span>FACTURA ELECTRONICA</div>';

                    // Segundo badge con el estado ante el SIN, solo si la fila
                    // llego a registrarse en facturacion_electronica.
                    $estado = $row['fe_estado_siat'] ?? '';
                    if ($estado === '' || $estado === null) {
                        $estado_label = 'PENDIENTE';
                        $estado_clase = 'bwar';
                    } elseif (in_array(strtoupper($estado), ['908', 'VALIDADO'], true)) {
                        $estado_label = 'ACEPTADA';
                        $estado_clase = 'bgr';
                    } elseif (in_array(strtoupper($estado), ['CONTINGENCIA', 'PENDIENTE_VALIDACION'], true)) {
                        $estado_label = 'EN CONTINGENCIA';
                        $estado_clase = 'bwar';
                    } else {
                        $estado_label = 'RECHAZADA';
                        $estado_clase = 'bdan';
                    }

                    $tipo_doc_html .= '<div class="badge ' . $estado_clase . ' bsm" style="margin-top:4px;">'
                        . '<span class="bdt"></span>' . $estado_label . '</div>';
                } else {
                    $tipo_doc_html = '<span class="badge bgry bsm">'
                        . '<span class="bdt"></span>' . $tipo_doc . '</span>';
                }

                $tiene_factura = !empty($row['fa_id']);

                // N° de factura. En una factura electronica se muestra el
                // correlativo numerico que el SIN usa (fa_numero_siat), porque
                // es ese el que va embebido en el CUF. En una nota de venta se
                // muestra su codigo interno.
                if ($es_factura_electronica && !empty($row['fa_numero_siat'])) {
                    $celda_factura = '<strong>' . (int)$row['fa_numero_siat'] . '</strong>'
                        . '<br><small style="color:#999;">CUF ' . htmlspecialchars(substr($row['fa_cuf'], 0, 8)) . '...</small>';
                } elseif (!empty($row['fa_numero_control'])) {
                    $celda_factura = '<span style="color:#999;">' . htmlspecialchars($row['fa_numero_control']) . '</span>';
                } else {
                    $celda_factura = '<span style="color:#999;">-</span>';
                }

                $tabla .= '
                    <tr class="tr-click" onclick="VentasHistorialModals.verDetalle(' . $row['ve_id'] . ', \'' . addslashes($row['ve_numero_documento']) . '\')">
                        <td>' . $contador . '</td>
                        <td><strong>' . htmlspecialchars($row['ve_numero_documento']) . '</strong></td>
                        <td>' . date('d/m/Y H:i', strtotime($row['ve_fecha_emision'])) . '</td>
                        <td>' . $cliente_display . '</td>
                        <td>' . htmlspecialchars($row['vendedor_nombre']) . '</td>' .
                    ($mostrar_columna_sucursal ? '<td><span style=";padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">' . htmlspecialchars($row['sucursal_nombre']) . '</span></td>' : '') .'
                        <td style="text-align:right;font-size:14px;"><strong style="color:#2e7d32;">Bs. ' . number_format($row['ve_total'], 2) . '</strong></td>
                        <td style="text-align:center;">' . $celda_factura . '</td>
                        <td style="text-align:center;">' . $tipo_doc_html . '</td>
                        <td >
                            <a href="javascript:void(0)" 
                               class="btn btn-out btn-sm" 
title="Reimprimir comprobante"
                                onclick="event.stopPropagation(); VentasHistorialModals.reimprimirComprobante(' . $row['ve_id'] . ')">
                                <ion-icon name="print-outline"></ion-icon>Imprimir
                            </a>
                    </td>
                    </tr>
                ';
                $contador++;
            }
            $reg_final = $contador - 1;
        } else {
            $tabla .= '<tr><td colspan="' . $colspan_total . '" style="text-align:center;padding:20px;color:#999;">
                            <ion-icon name="receipt-outline"></ion-icon> No hay registros
                        </td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($pagina <= $Npaginas && $total >= 1) {
            $tabla .= '<p class="table-page-footer">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas_main($pagina, $Npaginas, $url, 5);
        }

        return $tabla;
    }


    /* Obtener el detalle completo de una venta */
    public function detalle_venta_controller()
    {
        $ve_id = isset($_POST['ve_id']) ? (int)$_POST['ve_id'] : 0;

        if ($ve_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Parámetros inválidos',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        try {
            $stmt = self::detalle_venta_completo_model($ve_id);
            $venta = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$venta) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'Venta no encontrada',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            $itemsStmt = self::detalle_items_venta_model($ve_id);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $itemsFormateados = array_map(function ($item) {
                $nombre_medicamento = $item['med_nombre_quimico'];
                if (!empty($item['med_version_comercial'])) {
                    $nombre_medicamento .= ' - ' . $item['med_version_comercial'];
                }

                return [
                    'nombre' => $nombre_medicamento,
                    'presentacion' => $item['med_presentacion'] ?? '',
                    'lote' => $item['lm_numero_lote'] ?? 'N/A',
                    'cantidad' => $item['dv_cantidad'],
                    'precio_unitario' => $item['dv_precio_unitario'],
                    'descuento' => $item['dv_descuento'],
                    'subtotal' => $item['dv_subtotal']
                ];
            }, $items);

            $cliente_nombre = trim(
                ($venta['cl_nombres'] ?? '') . ' ' .
                    ($venta['cl_apellido_paterno'] ?? '') . ' ' .
                    ($venta['cl_apellido_materno'] ?? '')
            );
            if (empty($cliente_nombre)) {
                $cliente_nombre = 'Sin cliente';
            }

            $vendedor_nombre = trim(
                ($venta['us_nombres'] ?? '') . ' ' .
                    ($venta['us_apellido_paterno'] ?? '') . ' ' .
                    ($venta['us_apellido_materno'] ?? '')
            );

            // Datos de facturacion electronica. Se agregan solo si la venta
            // llego a tener CUF, que es la senal de que paso por el proceso de
            // facturacion electronica.
            $esFacturaElectronica = !empty($venta['fa_cuf']);

            $siat = null;
            if ($esFacturaElectronica) {
                /* La etiqueta del estado sale de siatModel, no de un if/elseif
                   aqui. El estado mezcla codigos del SIN (902, 908) con
                   etiquetas internas (ANULADA, OBSERVADO) y este archivo no
                   tiene por que conocer ese catalogo: si se agrega un estado
                   nuevo, se actualiza un solo lugar. */
                list($etiqueta, $clase) = siatModel::etiquetaEstadoFactura($venta['fe_estado_siat'] ?? '');

                /* Lo que se manda al navegador es lo que el modal dibuja, ni una
                   clave mas. cufd, codigo_recepcion y fecha_envio se sacaron: el
                   modal ya no los muestra y mandarlos solo hacia que alguien los
                   lea en la pestaña de la consola creyendo que significan algo
                   para el usuario. El CUF tampoco se manda: va impreso en el
                   comprobante y dentro del QR, que es donde tiene que estar. */
                $siat = [
                    'es_factura_electronica' => true,
                    'fa_id' => !empty($venta['fa_id']) ? (int)$venta['fa_id'] : null,
                    'numero_factura' => !empty($venta['fa_numero_siat'])
                        ? (string)(int)$venta['fa_numero_siat']
                        : null,
                    'numero_control' => $venta['fa_numero_control'] ?? null,
                    'estado_etiqueta' => $etiqueta,
                    'estado_clase' => $clase,
                ];

                /* Anulacion. El modelo decide si la factura se puede anular y
                   por que no; aca solo se traduce su respuesta a algo que el
                   modal pueda mostrar.

                   Solo el administrador (rol 1) ve el boton. Que el backend
                   tambien lo exija es lo que hace que esto sea una restriccion
                   y no solo un cartel: un rol 2 puede llamar al ajax a mano. */
                $anulacion = siatModel::estadoAnulacionFactura($venta['fa_id'] ?? 0);
                $esAdmin = (($_SESSION['rol_smp'] ?? 0) == 1);

                $motivosAnulacion = [];
                foreach (siatModel::motivosAnulacion() as $codigo => $descripcion) {
                    $motivosAnulacion[] = ['codigo' => (int)$codigo, 'descripcion' => $descripcion];
                }

                $siat['anulacion'] = [
                    'es_admin' => $esAdmin,
                    'puede_anular' => $anulacion['anulable'],
                    'motivo_bloqueo' => $anulacion['anulable'] ? null : $anulacion['motivo'],
                    'motivos' => $motivosAnulacion,
                ];

                /* QR de consulta del portal del SIN.

                   Se construye aqui y no se lee de fe_qr porque la URL es
                   determinista a partir de (nit, cuf, numero): da igual si la
                   factura se valido hoy o hace seis meses, el QR sale igual. Eso
                   es justamente lo que permite reimprimir un comprobante viejo
                   sin depender de que se haya guardado la imagen al validar.

                   Se genera solo si hay numero fiscal: sin el, el QR apuntaria a
                   una factura que el SIN no encuentra. */
                if (!empty($venta['fa_numero_siat']) && !empty($venta['fa_cuf'])) {
                    $siat['qr_url'] = siatModel::urlQR(
                        siatModel::obtenerNitEmpresa(),
                        $venta['fa_cuf'],
                        $venta['fa_numero_siat']
                    );

                    // render() devuelve el PNG en base64 pelado, sin el prefijo
                    // data: URI. El prefijo lo pone el JS.
                    $siat['qr_img'] = siatModel::generarQR(
                        siatModel::obtenerNitEmpresa(),
                        $venta['fa_cuf'],
                        $venta['fa_numero_siat']
                    );
                } else {
                    $siat['qr_url'] = null;
                    $siat['qr_img'] = null;
                }
            }

            $response = [
                'venta' => [
                    've_numero_documento' => $venta['ve_numero_documento'],
                    've_fecha_emision' => date('d/m/Y H:i', strtotime($venta['ve_fecha_emision'])),
                    've_subtotal' => $venta['ve_subtotal'],
                    've_impuesto' => $venta['ve_impuesto'],
                    've_total' => $venta['ve_total'],
                    've_tipo_documento' => $venta['ve_tipo_documento'] ?? 'venta',
                    'cliente_nombre' => $cliente_nombre,
                    'cliente_carnet' => $venta['cl_carnet'] ?? 'S/N',
                    'vendedor_nombre' => $vendedor_nombre,
                    'sucursal_nombre' => $venta['su_nombre'],
                    'caja_nombre' => $venta['caja_nombre'] ?? 'N/A',
                    'fa_numero_control' => $venta['fa_numero_control'] ?? null,
                    'siat' => $siat
                ],
                'items' => $itemsFormateados
            ];

            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
        } catch (Exception $e) {
            error_log("Error en detalle_venta_controller: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Error al cargar detalle de venta',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }

    /* Anula una factura electronica ante el SIN.

       Solo el administrador (rol 1) puede llegar aca. El chequeo se repite en el
       ajax: el boton puede estar oculto en pantalla, pero la llamada se puede
       escribir a mano desde la consola del navegador, y anular una factura es
       de las pocas cosas de este sistema que no tiene vuelta atras. */
    public function anular_factura_controller()
    {
        $fa_id = isset($_POST['fa_id']) ? (int)$_POST['fa_id'] : 0;
        $motivo = isset($_POST['codigo_motivo']) ? (int)$_POST['codigo_motivo'] : 0;

        if ((($_SESSION['rol_smp'] ?? 0) != 1)) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Acceso denegado',
                'texto' => 'Solo un administrador puede anular facturas.',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        if ($fa_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Factura no valida',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        /* El modelo decide si se puede y valida el motivo contra el catalogo.
           No se reimplementa ninguna de las dos reglas aca. */
        $resultado = siatModel::anularFacturaConControl($fa_id, $motivo);

        if (!$resultado['ok']) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'No se pudo anular',
                'texto' => $resultado['mensaje'],
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        $alerta = [
            'Alerta' => 'simple',
            'Titulo' => 'Factura anulada',
            'texto' => $resultado['mensaje'],
            'Tipo' => 'success'
        ];
        echo json_encode($alerta);
        exit();
    }

    /* Reimpresion del comprobante desde el historial.

       Es la UNICA reimpresion del sistema y vive aca, en el boton de cada fila
       de la tabla, no en el modal de detalle. Dos razones:

         - el modal es informativo: que se imprima desde ahi duplicaba la accion
           y obligaba a mantener dos generadores de PDF, que se desincronizan
           entre si sin que nada avise;
         - este metodo ya existe desde antes de la facturacion electronica, asi
           que la reimpresion nunca estuvo rota: lo que estaba roto era el
           generador que este metodo usaba.

       Antes traia su propia consulta y su propio metodo privado
       generar_pdf_nota_venta_rep(), copia del PDF de venta anterior al modulo
       SIAT: historicaba desde ventas sin tocar la tabla factura, asi que una
       factura electronica salia reimpresa rotulada "NOTA DE VENTA", sin CUF, sin
       leyenda y sin QR. El cliente se llevaba un papel que el SIN no reconoce.

       ventaModel::generar_pdf_factura_historial_model() ya hacia esto bien: hace
       LEFT JOIN con factura, recupera los datos fiscales y decide el titulo
       segun exista o no CUF. Este metodo solo lo delega y ya no reimprime nada
       por su cuenta. */
    public function generar_pdf_nota_controller()
    {
        $ve_id = isset($_POST['ve_id']) ? (int)$_POST['ve_id'] : 0;

        if ($ve_id <= 0) {
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'ParÃ¡metros invÃ¡lidos',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }

        try {
            require_once '../models/ventaModel.php';

            $ins_venta = new ventaModel();
            $pdf_base64 = $ins_venta->generar_pdf_factura_historial_model($ve_id);

            if (!$pdf_base64) {
                $alerta = [
                    'Alerta' => 'simple',
                    'Titulo' => 'Error',
                    'texto' => 'No se pudo generar el comprobante',
                    'Tipo' => 'error'
                ];
                echo json_encode($alerta);
                exit();
            }

            echo json_encode(['success' => true, 'pdf_base64' => $pdf_base64]);
            exit();
        } catch (Exception $e) {
            error_log("  Error en generar_pdf_nota_controller: " . $e->getMessage());
            $alerta = [
                'Alerta' => 'simple',
                'Titulo' => 'Error',
                'texto' => 'Error al generar el comprobante',
                'Tipo' => 'error'
            ];
            echo json_encode($alerta);
            exit();
        }
    }


    /* Exportar el historial de ventas a un archivo Excel */
    public function exportar_excel_controller()
    {
        $rol_usuario = $_SESSION['rol_smp'] ?? 0;
        $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;

        $filtros = [];

        if ($rol_usuario == 2) {
            $filtros['su_id'] = $sucursal_usuario;
        } elseif (isset($_GET['su_id']) && $_GET['su_id'] !== '') {
            $filtros['su_id'] = (int)$_GET['su_id'];
        }

        if (isset($_GET['fecha_desde']) && !empty($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = mainModel::limpiar_cadena($_GET['fecha_desde']);
        }

        if (isset($_GET['fecha_hasta']) && !empty($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = mainModel::limpiar_cadena($_GET['fecha_hasta']);
        }

        if (isset($_GET['cliente']) && is_numeric($_GET['cliente'])) {
            $filtros['cliente'] = (int)$_GET['cliente'];
        }

        if (isset($_GET['vendedor']) && is_numeric($_GET['vendedor'])) {
            $filtros['vendedor'] = (int)$_GET['vendedor'];
        }

        if (isset($_GET['tipo_documento']) && !empty($_GET['tipo_documento'])) {
            $filtros['tipo_documento'] = mainModel::limpiar_cadena($_GET['tipo_documento']);
        }

        $estados_validos = ['revision', 'pendiente', 'aceptada', 'sin_factura'];
        if (isset($_GET['estado_siat']) && in_array($_GET['estado_siat'], $estados_validos, true)) {
            $filtros['estado_siat'] = $_GET['estado_siat'];
        }

        try {
            $stmt = self::exportar_historial_excel_model($filtros);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($datos)) {
                echo "No hay datos para exportar";
                return;
            }

            $fecha = date('Y-m-d_His');
            $sucursal_nombre = !empty($filtros['su_id']) ? 'Sucursal_' . $filtros['su_id'] : 'Todas_Sucursales';
            $filename = "Historial_Ventas_{$sucursal_nombre}_{$fecha}.xls";

            $headers = array_keys($datos[0]);

            $info_superior = [
                'Fecha de Generación' => date('d/m/Y H:i:s'),
                'Usuario' => $_SESSION['nombre_smp'] ?? 'Sistema',
                'Total de Registros' => count($datos)
            ];

            if (!empty($filtros['su_id'])) {
                $info_superior['Sucursal'] = 'Sucursal ID ' . $filtros['su_id'];
            } else {
                $info_superior['Sucursal'] = 'Todas las Sucursales';
            }

            if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
                $info_superior['Rango de Fechas'] = date('d/m/Y', strtotime($filtros['fecha_desde'])) . ' - ' . date('d/m/Y', strtotime($filtros['fecha_hasta']));
            }

            mainModel::generar_excel_reporte([
                'titulo' => 'HISTORIAL DE VENTAS',
                'datos' => $datos,
                'headers' => $headers,
                'nombre_archivo' => $filename,
                'formato_columnas' => [
                    'Subtotal (Bs)' => 'moneda',
                    'Impuestos (Bs)' => 'moneda',
                    'Total (Bs)' => 'moneda',
                    'Items' => 'numero'
                ],
                'columnas_totales' => ['Total (Bs)'],
                'info_superior' => $info_superior
            ]);

        } catch (Exception $e) {
            error_log("Error exportando Excel: " . $e->getMessage());
            echo "Error al generar archivo: " . htmlspecialchars($e->getMessage());
        }
    }
}
