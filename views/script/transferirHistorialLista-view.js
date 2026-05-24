const TransferirHistorialModals = (function() {
    'use strict';

    function getBaseURL() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    console.log(' Inicializando módulo TransferirHistorialModals');

    const API_URL = getBaseURL() + 'ajax/transferirHistorialAjax.php';
    const API_URL_TRANSFER = getBaseURL() + 'ajax/transferirAjax.php';

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
