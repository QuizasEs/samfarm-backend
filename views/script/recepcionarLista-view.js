const RecepcionManager = (function() {
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

    const URL_AJAX = getBaseURL() + 'ajax/recepcionarAjax.php';
    const SU_ACTUAL = window.SU_ACTUAL_RECEPCION || 0;
    const ROL_USUARIO = window.ROL_USUARIO_RECEPCION || 0;

    let transferenciasActuales = [];
    let transferenciasSeleccionada = null;
    let modoRechazo = false;

    function init() {
        configurarEventos();
        cargarTransferencias();
    }

            function configurarEventos() {
                // Sin filtros de búsqueda por el momento
            }

    async function cargarTransferencias() {
        const formData = new FormData();
        formData.append('recepcionarAjax', 'listar');

        try {
            const response = await fetch(URL_AJAX, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.error) {
                mostrarError(data.error);
                return;
            }

            transferenciasActuales = data;
            renderizarTransferencias(data);

        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error al cargar transferencias');
        }
    }

    function renderizarTransferencias(transferencias) {
        const container = document.getElementById('resultado-busqueda-recepcionar');

        if (!transferencias || transferencias.length === 0) {
            container.innerHTML = `
                <p style="text-align:center; padding: 20px; color: #999;">
                    <ion-icon name="information-circle-outline" style="font-size: 48px;"></ion-icon><br>
                    No hay transferencias pendientes
                </p>
            `;
            return;
        }

        let html = '<div class="tw"><table><thead><tr>';
        html += '<th>#</th>';
        html += '<th>Número</th>';
        html += '<th>Origen</th>';
        html += '<th>Items</th>';
        html += '<th>Total Cajas</th>';
        html += '<th>Total Unidades</th>';
        html += '<th>Valorado</th>';
        html += '<th>Fecha</th>';
        html += '</tr></thead><tbody>';

        transferencias.forEach((transfer, index) => {
            html += '<tr class="recepcion-row" data-tr-id="' + transfer.tr_id + '" style="cursor: pointer;">';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td><strong>' + escapeHtml(transfer.tr_numero) + '</strong></td>';
            html += '<td>' + escapeHtml(transfer.sucursal_origen) + '</td>';
            html += '<td style="text-align:center;"><strong>' + transfer.tr_total_items + '</strong></td>';
            html += '<td style="text-align:center;"><strong>' + transfer.tr_total_cajas + '</strong></td>';
            html += '<td style="text-align:center;"><strong style="color: #1976D2;">' + transfer.tr_total_unidades + '</strong></td>';
            html += '<td style="text-align:right;">Bs. ' + formatearNumero(transfer.tr_total_valorado) + '</td>';
            html += '<td>' + formatearFecha(transfer.tr_fecha_envio) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        // Agregar event listeners a las filas clickeables
        const filas = container.querySelectorAll('.recepcion-row');
        filas.forEach(fila => {
            fila.addEventListener('click', function() {
                const trId = this.getAttribute('data-tr-id');
                if (trId) {
                    RecepcionManager.verDetalles(parseInt(trId));
                }
            });
        });
    }

    async function verDetalles(trId) {
        const formData = new FormData();
        formData.append('recepcionarAjax', 'obtener_detalles');
        formData.append('tr_id', trId);

        try {
            const response = await fetch(URL_AJAX, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.error) {
                mostrarError(data.error);
                return;
            }

            transferenciasSeleccionada = data.transferencia;
            mostrarDetallesTransferencia(data.transferencia, data.detalles);
            const modal = document.getElementById('modal-detalles-transfer-recepcionar');
            modal.style.display = 'flex';
            modal.classList.add('open');
            modoRechazo = false;
            document.getElementById('modal-rechazo-container').style.display = 'none';
            document.getElementById('btn-aceptar-transfer-modal').style.display = 'inline-block';

        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error al obtener detalles');
        }
    }

    function mostrarDetallesTransferencia(transferencia, detalles) {
        document.getElementById('modal-numero-transfer-recepcionar').textContent = transferencia.tr_numero;
        document.getElementById('modal-estado-transfer-recepcionar').textContent = transferencia.tr_estado;
        document.getElementById('modal-sucursal-origen-recepcionar').textContent = transferencia.sucursal_origen;
        document.getElementById('modal-sucursal-destino-recepcionar').textContent = transferencia.sucursal_destino;
        document.getElementById('modal-usuario-emisor-recepcionar').textContent = transferencia.usuario_emisor;
        document.getElementById('modal-fecha-envio-recepcionar').textContent = formatearFecha(transferencia.tr_fecha_envio);
        document.getElementById('modal-observaciones-recepcionar').textContent = transferencia.tr_observaciones || 'Sin observaciones';

        document.getElementById('modal-total-items-recepcionar').textContent = transferencia.tr_total_items;
        document.getElementById('modal-total-cajas-recepcionar').textContent = transferencia.tr_total_cajas;
        document.getElementById('modal-total-unidades-recepcionar').textContent = transferencia.tr_total_unidades;
        document.getElementById('modal-total-valorado-recepcionar').textContent = 'Bs. ' + formatearNumero(transferencia.tr_total_valorado);

        renderizarDetallesItems(detalles);
    }

    function renderizarDetallesItems(detalles) {
        let html = '<div class="tw"><table style="font-size: 13px;"><thead><tr>';
        html += '<th>#</th>';
        html += '<th>Medicamento</th>';
        html += '<th>Lote Origen</th>';
        html += '<th>Cajas</th>';
        html += '<th>Unidades</th>';
        html += '<th>Precio Compra</th>';
        html += '<th>Subtotal</th>';
        html += '</tr></thead><tbody>';

        detalles.forEach((det, index) => {
            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td><strong>' + escapeHtml(det.med_nombre_quimico) + '</strong><br><small>' + escapeHtml(det.med_principio_activo) + '</small></td>';
            html += '<td>' + escapeHtml(det.dt_numero_lote_origen) + '</td>';
            html += '<td style="text-align:center;"><strong>' + det.dt_cantidad_cajas + '</strong></td>';
            html += '<td style="text-align:center;"><strong style="color: #1976D2;">' + det.dt_cantidad_unidades + '</strong></td>';
            html += '<td style="text-align:right;">Bs. ' + formatearNumero(det.dt_precio_compra) + '</td>';
            html += '<td style="text-align:right;"><strong>Bs. ' + formatearNumero(det.dt_subtotal_valorado) + '</strong></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        document.getElementById('modal-detalles-items-recepcionar').innerHTML = html;
    }

    function cerrarModalDetalles() {
        const modal = document.getElementById('modal-detalles-transfer-recepcionar');
        modal.classList.remove('open');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
        modoRechazo = false;
        document.getElementById('modal-rechazo-container').style.display = 'none';
        document.getElementById('modal-motivo-rechazo').value = '';
    }

    function mostrarRechazo() {
        modoRechazo = true;
        document.getElementById('modal-rechazo-container').style.display = 'block';
        document.getElementById('btn-aceptar-transfer-modal').style.display = 'none';
        document.getElementById('btn-rechazar-transfer-modal').innerHTML = '<ion-icon name="checkmark-outline"></ion-icon> Confirmar Rechazo';
        document.getElementById('btn-rechazar-transfer-modal').onclick = function() {
            rechazarTransferencia();
        };
        mostrarAdvertencia('Modo Rechazo Activado', 'Ingrese el motivo del rechazo en el campo de texto');
    }

    function aceptarTransferencia() {
        if (!transferenciasSeleccionada) {
            mostrarError('Seleccione una transferencia');
            return;
        }

        mostrarConfirmacion(
            '¿Aceptar Transferencia?',
            '¿Está seguro que desea aceptar esta transferencia? Se crearán nuevos lotes e incrementará el inventario.',
            async function() {
                const formData = new FormData();
                formData.append('recepcionarAjax', 'aceptar');
                formData.append('tr_id', transferenciasSeleccionada.tr_id);

                try {
                    const response = await fetch(URL_AJAX, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.error) {
                        mostrarError(data.error);
                        return;
                    }

                    mostrarExito(data.Titulo, data.texto);
                    cerrarModalDetalles();
                    cargarTransferencias();

                } catch (error) {
                    console.error('Error:', error);
                    mostrarError('Error al aceptar transferencia');
                }
            }
        );
    }

    function rechazarTransferencia() {
        if (!transferenciasSeleccionada) {
            mostrarError('Seleccione una transferencia');
            return;
        }

        const motivo = document.getElementById('modal-motivo-rechazo').value.trim();

        if (!motivo) {
            mostrarAdvertencia('Campo requerido', 'Ingrese el motivo del rechazo');
            return;
        }

        mostrarConfirmacion(
            '¿Rechazar Transferencia?',
            '¿Está seguro que desea rechazar esta transferencia? Se revertirán los cambios de stock.',
            async function() {
                const formData = new FormData();
                formData.append('recepcionarAjax', 'rechazar');
                formData.append('tr_id', transferenciasSeleccionada.tr_id);
                formData.append('motivo', motivo);

                try {
                    const response = await fetch(URL_AJAX, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.error) {
                        mostrarError(data.error);
                        return;
                    }

                    mostrarExito(data.Titulo, data.texto);
                    cerrarModalDetalles();
                    cargarTransferencias();

                } catch (error) {
                    console.error('Error:', error);
                    mostrarError('Error al rechazar transferencia');
                }
            }
        );
    }

    function mostrarError(mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: mensaje,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Aceptar'
        });
    }

    function mostrarExito(titulo, texto) {
        Swal.fire({
            icon: 'success',
            title: titulo,
            html: texto,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Aceptar'
        });
    }

    function mostrarConfirmacion(titulo, mensaje, callback) {
        Swal.fire({
            icon: 'question',
            title: titulo,
            text: mensaje,
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    }

    function mostrarAdvertencia(titulo, mensaje) {
        Swal.fire({
            icon: 'warning',
            title: titulo,
            text: mensaje,
            confirmButtonColor: '#ff9800',
            confirmButtonText: 'Aceptar'
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    function formatearFecha(fecha) {
        if (!fecha) return '';
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function formatearNumero(numero) {
        return parseFloat(numero).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    return {
        init: init,
        verDetalles: verDetalles,
        cerrarModalDetalles: cerrarModalDetalles,
        mostrarRechazo: mostrarRechazo,
        aceptarTransferencia: aceptarTransferencia
    };
})();

document.addEventListener('DOMContentLoaded', RecepcionManager.init);
