<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {

    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/transferirHistorialAjax.php"
        data-ajax-param="transferirHistorialAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="swap-horizontal-outline"></ion-icon> Historial de Transferencias
                </div>
                <div class="psub">Consulta el historial completo de transferencias realizadas</div>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Desde</label>
                            <input class="inp" type="date" name="fecha_desde" title="Fecha desde">
                        </div>
                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta" title="Fecha hasta">
                        </div>

                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal Origen</label>
                                <select class="sel select-filtro" name="select1">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="fg">
                                <label class="fl">Sucursal Destino</label>
                                <select class="sel select-filtro" name="select2">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <div></div>
                            <div></div>
                        <?php } ?>

                        <div class="fg">
                            <label class="fl">Usuario</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todos los usuarios</option>
                                <?php if (!empty($datos_select['usuarios'])) { ?>
                                    <?php foreach ($datos_select['usuarios'] as $usuario) { ?>
                                        <option value="<?php echo $usuario['us_id'] ?>"><?php echo $usuario['us_nombres'] ?></option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aceptada">Aceptada</option>
                                <option value="rechazada">Rechazada</option>
                            </select>
                        </div>


                    </div>
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por N° transferencia...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>
    </div>

    <div id="modalDetalleTransferencia" class="mov">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="swap-horizontal-outline"></ion-icon>
                        Detalle de Transferencia
                    </div>
                    <div class="ms">Información completa de la transferencia seleccionada</div>
                </div>
                <button class="mcl" onclick="TransferirHistorialModals.cerrar('modalDetalleTransferencia')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="stit">Información de la Transferencia</div>

                <input type="hidden" id="modalDetalleTrId">

                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Transferencia</div>
                                    <div class="th5" id="detalleNumero"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha Envío</div>
                                    <div class="th5" id="detalleFechaEnvio"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Origen</div>
                                    <div class="th5" id="detalleOrigen"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Destino</div>
                                    <div class="th5" id="detalleDestino"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Usuario Emisor</div>
                                    <div class="th5" id="detalleUsuarioEmisor"></div>
                                </div>
                            </div>
                            <div class="litem" style="border:none"><ion-icon name="radio-button-on-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Estado</div>
                                    <div id="detalleEstado"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stit">Medicamentos Transferidos</div>
                <div class="card mb16">
                    <div class="cb">
                        <div class="tw" style="max-height: 400px; overflow-y: auto;">
                              <table class="table-detail">
                                <thead>
                                    <tr>
                                        <th width="40%">Medicamento</th>
                                        <th width="8%">Cajas</th>
                                        <th width="10%">Unidades</th>
                                        <th width="12%">Precio Unit.</th>
                                        <th width="12%">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaItemsTransferencia">
                                     <tr>
                                        <td colspan="5" class="txctr">
                                            <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="stit">Totales</div>
                <div class="grid4">
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="archive-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotalCajas">0</div>
                            <div class="sl">Total Cajas</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="medical-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotalUnidades">0</div>
                            <div class="sl">Total Unidades</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotal">Bs. 0.00</div>
                            <div class="sl">Total Valorado</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="TransferirHistorialModals.cerrar('modalDetalleTransferencia')">Cerrar</button>
            </div>
        </div>
    </div>

<?php } else { ?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit">Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb txctr" style="padding:60px">
                <ion-icon name="lock-closed-outline" style="font-size:48px;color:var(--text-faint);margin-bottom:16px"></ion-icon>
                <div class="th3 mb8">Acceso Denegado</div>
                <div class="tbs tmut">No tiene permisos para acceder a esta sección.</div>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    const TransferirHistorialModals = (function() {
        'use strict';

        console.log(' Inicializando módulo TransferirHistorialModals');

        const API_URL = '<?php echo SERVER_URL; ?>ajax/transferirHistorialAjax.php';
        const API_URL_TRANSFER = '<?php echo SERVER_URL; ?>ajax/transferirAjax.php';

        const utils = {
            async ajax(params) {
                try {
                    console.log(' Enviando petición:', params);

                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(params)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const data = await response.json();
                    console.log(' Respuesta recibida:', data);
                    return data;

                } catch (error) {
                    console.error('  Error AJAX:', error);
                    throw error;
                }
            },

            abrir(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.add('open');
                    console.log(` Modal abierto: ${modalId}`);
                }
            },

            cerrar(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
                    console.log(` Modal cerrado: ${modalId}`);
                }
            },

            formatearMoneda(num) {
                return 'Bs. ' + parseFloat(num || 0).toFixed(2);
            },

            formatearNumero(num) {
                return parseInt(num || 0).toLocaleString('es-BO');
            }
        };

        const detalle = {
            async abrir(trId, numeroTransferencia) {
                console.log(' Abriendo detalle de transferencia:', {
                    trId,
                    numeroTransferencia
                });

                document.getElementById('modalDetalleTrId').value = trId;
                utils.abrir('modalDetalleTransferencia');

                document.getElementById('tablaItemsTransferencia').innerHTML =
                    '<tr><td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                try {
                    const data = await utils.ajax({
                        transferirHistorialAjax: 'detalle',
                        tr_id: trId
                    });

                    if (data.Alerta) {
                        Swal.fire({
                            title: data.Titulo,
                            text: data.texto,
                            icon: data.Tipo
                        });
                        utils.cerrar('modalDetalleTransferencia');
                        return;
                    }

                    const tr = data.transferencia;
                    document.getElementById('detalleNumero').textContent = tr.tr_numero;
                    document.getElementById('detalleFechaEnvio').textContent = tr.tr_fecha_envio;
                    document.getElementById('detalleOrigen').textContent = tr.sucursal_origen;
                    document.getElementById('detalleDestino').textContent = tr.sucursal_destino;
                    document.getElementById('detalleUsuarioEmisor').textContent = tr.usuario_emisor;
                    document.getElementById('detalleEstado').innerHTML = detalle.obtenerBadgeEstado(tr.tr_estado);
                    document.getElementById('detalleTotalCajas').textContent = tr.tr_total_cajas;
                    document.getElementById('detalleTotalUnidades').textContent = utils.formatearNumero(tr.tr_total_unidades);
                    document.getElementById('detalleTotal').textContent = utils.formatearMoneda(tr.tr_total_valorado);

                    const tbody = document.getElementById('tablaItemsTransferencia');
                    if (data.detalles && data.detalles.length > 0) {
                     tbody.innerHTML = data.detalles.map(item => `
                        <tr>
                            <td>
                                <div class="td-main"><strong>${item.med_nombre_quimico}</strong></div>
                                <div class="td-sub"><ion-icon name="pricetag-outline"></ion-icon> Lote: ${item.dt_numero_lote_origen}</div>
                            </td>
                            <td style="text-align:center;">${item.dt_cantidad_cajas}</td>
                            <td style="text-align:center;">${utils.formatearNumero(item.dt_cantidad_unidades)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(item.dt_precio_compra)}</td>
                            <td style="text-align:right;"><strong>${utils.formatearMoneda(item.dt_subtotal_valorado)}</strong></td>
                        </tr>
                    `).join('');
                     } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="cart-outline"></ion-icon> Sin items</td></tr>';
                    }

                } catch (error) {
                    console.error('  Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    utils.cerrar('modalDetalleTransferencia');
                }
            },

            obtenerBadgeEstado(estado) {
                const estados = {
                    'pendiente': '<span class="badge badge-warning">Pendiente</span>',
                    'aceptada': '<span class="badge badge-success">Aceptada</span>',
                    'rechazada': '<span class="badge badge-danger">Rechazada</span>'
                };
                return estados[estado] || '<span class="badge badge-secondary">Desconocido</span>';
            }
        };

        const descarga = {
            async pdf(trId) {
                console.log(' Descargando PDF:', trId);

                Swal.fire({
                    title: 'Generando PDF...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const data = await utils.ajax({
                        transferirHistorialAjax: 'generar_pdf',
                        tr_id: trId
                    });

                    Swal.close();

                    if (data.success && data.pdf_base64) {
                        mostrarPDFEnModal(data.pdf_base64, `Transferencia_${trId}.pdf`);
                        // No mostrar alert de success, el modal ya indica que se generó
                    } else {
                        Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                    }

                } catch (error) {
                    console.error('  Error:', error);
                    Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                }
            }
        };



        const acciones = {
            async aceptar(trId) {
                console.log(' Aceptando transferencia:', trId);

                Swal.fire({
                    title: 'Confirmar',
                    text: '¿Está seguro de aceptar esta transferencia?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar'
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Procesando...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        try {
                            const response = await fetch(API_URL_TRANSFER, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams({
                                    transferirAjax: 'aceptar',
                                    tr_id: trId
                                })
                            });

                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}`);
                            }

                            const data = await response.json();

                            Swal.close();

                            if (data.error) {
                                Swal.fire('Error', data.error, 'error');
                            } else if (data.Tipo === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.Titulo,
                                    text: data.texto,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    detalle.abrir(trId, '');
                                });
                            } else {
                                Swal.fire('Error', 'Respuesta inesperada del servidor', 'error');
                            }
                        } catch (error) {
                            console.error('  Error:', error);
                            Swal.fire('Error', 'No se pudo aceptar la transferencia', 'error');
                        }
                    }
                });
            }
        };

        const publicAPI = {
            cerrar: utils.cerrar,
            verDetalle: detalle.abrir,
            descargarPDF: descarga.pdf,
            aceptarTransferencia: acciones.aceptar
        };

        console.log(' Módulo TransferirHistorialModals creado correctamente', publicAPI);
        return publicAPI;
    })();
</script>
