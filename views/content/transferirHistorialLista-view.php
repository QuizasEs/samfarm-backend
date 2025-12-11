<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {

    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/transferirHistorialAjax.php"
        data-ajax-param="transferirHistorialAjax"
        data-ajax-registros="10">

        <div class="title">
            <h2>
                <ion-icon name="swap-horizontal-outline"></ion-icon> Historial de Transferencias
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">
                    <small>Desde</small>
                    <input type="date" name="fecha_desde" placeholder="Desde" title="Fecha desde">
                </div>
                <div class="form-fechas">
                    <small>Hasta</small>
                    <input type="date" name="fecha_hasta" placeholder="Hasta" title="Fecha hasta">
                </div>

                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                    <div class="form-fechas">
                        <small>Sucursal Origen</small>
                        <select class="select-filtro" name="select1">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-fechas">
                        <small>Sucursal Destino</small>
                        <select class="select-filtro" name="select2">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="form-fechas">
                    <small>Usuario</small>
                    <select class="select-filtro" name="select3">
                        <option value="">Todos los usuarios</option>
                        <?php if (!empty($datos_select['usuarios'])) { ?>
                            <?php foreach ($datos_select['usuarios'] as $usuario) { ?>
                                <option value="<?php echo $usuario['us_id'] ?>"><?php echo $usuario['us_nombres'] ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Estado</small>
                    <select class="select-filtro" name="select4">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aceptada">Aceptada</option>
                        <option value="rechazada">Rechazada</option>
                    </select>
                </div>

                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por N° transferencia...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
        </form>

        <div class="tabla-contenedor"></div>
    </div>

    <div id="modalDetalleTransferencia" class="modal" style="display:none;">
        <div class="modal-content detalle" >
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="swap-horizontal-outline"></ion-icon>
                    <span>Detalle de Transferencia</span>
                </div>
                <a class="close" onclick="TransferirHistorialModals.cerrar('modalDetalleTransferencia')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <div class="modal-group">
                <h3>Información de la Transferencia</h3>

                <input type="hidden" id="modalDetalleTrId">

                <div class="row">
                    <div class="col-50">
                        <p><strong>N° Transferencia:</strong> <span id="detalleNumero"></span></p>
                        <p><strong>Fecha Envío:</strong> <span id="detalleFechaEnvio"></span></p>
                        <p><strong>Origen:</strong> <span id="detalleOrigen"></span></p>
                    </div>
                    <div class="col-50">
                        <p><strong>Destino:</strong> <span id="detalleDestino"></span></p>
                        <p><strong>Usuario Emisor:</strong> <span id="detalleUsuarioEmisor"></span></p>
                        <p><strong>Estado:</strong> <span id="detalleEstado"></span></p>
                    </div>
                </div>

                <h3 style="margin-top: 20px;">Medicamentos Transferidos</h3>

                <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Lote</th>
                                <th>Cajas</th>
                                <th>Unidades</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tablaItemsTransferencia">
                            <tr>
                                <td colspan="6" style="text-align:center;">
                                    <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-70"></div>
                    <div class="col-30">
                        <p><strong>Total Cajas:</strong> <span id="detalleTotalCajas">0</span></p>
                        <p><strong>Total Unidades:</strong> <span id="detalleTotalUnidades">0</span></p>
                        <p style="font-size: 18px; color: #2e7d32;"><strong>TOTAL:</strong> <span id="detalleTotal">Bs. 0.00</span></p>
                    </div>
                </div>


            </div>
        </div>
    </div>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>

<script>
    const TransferirHistorialModals = (function() {
        'use strict';
        
        console.log('📋 Inicializando módulo TransferirHistorialModals');

        const API_URL = '<?php echo SERVER_URL; ?>ajax/transferirHistorialAjax.php';
        const API_URL_TRANSFER = '<?php echo SERVER_URL; ?>ajax/transferirAjax.php';

        const utils = {
            async ajax(params) {
                try {
                    console.log('📡 Enviando petición:', params);

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
                    console.log('✅ Respuesta recibida:', data);
                    return data;

                } catch (error) {
                    console.error('❌ Error AJAX:', error);
                    throw error;
                }
            },

            abrir(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    console.log(` Modal abierto: ${modalId}`);
                }
            },

            cerrar(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
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
                                    <strong>${item.med_nombre_quimico}</strong>
                                </td>
                                <td>${item.dt_numero_lote_origen}</td>
                                <td style="text-align:center;">${item.dt_cantidad_cajas}</td>
                                <td style="text-align:center;">${utils.formatearNumero(item.dt_cantidad_unidades)}</td>
                                <td style="text-align:right;">${utils.formatearMoneda(item.dt_precio_compra)}</td>
                                <td style="text-align:right;"><strong>${utils.formatearMoneda(item.dt_subtotal_valorado)}</strong></td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin items</td></tr>';
                    }

                } catch (error) {
                    console.error('❌ Error:', error);
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
                console.log('🖨️ Descargando PDF:', trId);

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
                        window.abrirPDFDesdeBase64(data.pdf_base64, `Transferencia_${trId}.pdf`);
                        Swal.fire({
                            icon: 'success',
                            title: 'PDF generado',
                            text: 'El PDF se ha abierto en una nueva ventana',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                    }

                } catch (error) {
                    console.error('❌ Error:', error);
                    Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                }
            }
        };

        document.addEventListener('click', function(e) {
            const modal = document.getElementById('modalDetalleTransferencia');
            if (modal && modal.style.display === 'flex' && e.target === modal) {
                utils.cerrar('modalDetalleTransferencia');
            }
        });

        const acciones = {
            async aceptar(trId) {
                console.log('✅ Aceptando transferencia:', trId);

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
                            console.error('❌ Error:', error);
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
        
        console.log('✅ Módulo TransferirHistorialModals creado correctamente', publicAPI);
        return publicAPI;
    })();
</script>
