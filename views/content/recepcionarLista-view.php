<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="container">
        <div class="title">
            <h2>
                <ion-icon name="cloud-download-outline"></ion-icon> Recepcionar Transferencias
            </h2>
        </div>

        <div class="filtro-dinamico" id="form-filtro-recepcionar">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" id="busqueda_recepcionar" placeholder="Buscar por número de transferencia...">
                    <button type="button" class="btn-search" id="btn-buscar-recepcionar">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
        </div>

        <div id="resultado-busqueda-recepcionar" style="margin-top: 20px;">
            <p style="text-align:center; padding: 20px; color: #666;">
                <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                Use el buscador para encontrar transferencias pendientes
            </p>
        </div>
    </div>

    <div class="modal" id="modal-detalles-transfer-recepcionar" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="document-text-outline"></ion-icon>
                    <span>Detalles de Transferencia</span>
                </div>
                <a class="close" onclick="RecepcionManager.cerrarModalDetalles()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <div class="modal-group">
                <div class="row">
                    <div class="col">
                        <label><strong>Número Transferencia:</strong></label>
                        <p id="modal-numero-transfer-recepcionar"></p>
                    </div>
                    <div class="col">
                        <label><strong>Estado:</strong></label>
                        <p id="modal-estado-transfer-recepcionar"></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label><strong>Sucursal Origen:</strong></label>
                        <p id="modal-sucursal-origen-recepcionar"></p>
                    </div>
                    <div class="col">
                        <label><strong>Sucursal Destino:</strong></label>
                        <p id="modal-sucursal-destino-recepcionar"></p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label><strong>Usuario Emisor:</strong></label>
                        <p id="modal-usuario-emisor-recepcionar"></p>
                    </div>
                    <div class="col">
                        <label><strong>Fecha Envío:</strong></label>
                        <p id="modal-fecha-envio-recepcionar"></p>
                    </div>
                </div>

                <div class="row">
                    <label><strong>Observaciones:</strong></label>
                    <p id="modal-observaciones-recepcionar" style="color: #666; font-style: italic;"></p>
                </div>

                <div style="margin-top: 20px;">
                    <h4>Items en Transferencia</h4>
                    <div id="modal-detalles-items-recepcionar" style="max-height: 400px; overflow-y: auto;"></div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #f0f7ff; border-radius: 8px;">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                        <div>
                            <strong>Total Items:</strong>
                            <p style="font-size: 18px; color: #1976D2;" id="modal-total-items-recepcionar">0</p>
                        </div>
                        <div>
                            <strong>Total Cajas:</strong>
                            <p style="font-size: 18px; color: #1976D2;" id="modal-total-cajas-recepcionar">0</p>
                        </div>
                        <div>
                            <strong>Total Unidades:</strong>
                            <p style="font-size: 18px; color: #1976D2;" id="modal-total-unidades-recepcionar">0</p>
                        </div>
                        <div>
                            <strong>Total Valorado:</strong>
                            <p style="font-size: 18px; color: #27ae60;" id="modal-total-valorado-recepcionar">Bs. 0.00</p>
                        </div>
                    </div>
                </div>

                <div id="modal-rechazo-container" style="display: none; margin-top: 20px;">
                    <label for="modal-motivo-rechazo"><strong>Motivo de Rechazo:</strong></label>
                    <textarea id="modal-motivo-rechazo" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>

                <div class="modal-btn-content">
                    <a href="javascript:void(0)" class="btn warning" onclick="RecepcionManager.cerrarModalDetalles()">
                        Cancelar
                    </a>
                    <a href="javascript:void(0)" class="btn danger" id="btn-rechazar-transfer-modal" onclick="RecepcionManager.mostrarRechazo()">
                        <ion-icon name="close-circle-outline"></ion-icon> Rechazar
                    </a>
                    <a href="javascript:void(0)" class="btn success" id="btn-aceptar-transfer-modal" onclick="RecepcionManager.aceptarTransferencia()">
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Aceptar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const RecepcionManager = (function() {
            'use strict';

            const URL_AJAX = '<?php echo SERVER_URL; ?>ajax/recepcionarAjax.php';
            const SU_ACTUAL = <?php echo $_SESSION['sucursal_smp']; ?>;
            const ROL_USUARIO = <?php echo $_SESSION['rol_smp']; ?>;

            let transferenciasActuales = [];
            let transferenciasSeleccionada = null;
            let modoRechazo = false;

            function init() {
                configurarEventos();
                cargarTransferencias();
            }

            function configurarEventos() {
                const btnBuscar = document.getElementById('btn-buscar-recepcionar');
                if (btnBuscar) {
                    btnBuscar.addEventListener('click', cargarTransferencias);
                }

                const inputBusqueda = document.getElementById('busqueda_recepcionar');
                if (inputBusqueda) {
                    inputBusqueda.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            cargarTransferencias();
                        }
                    });
                }
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

                let html = '<div class="table-container"><table class="table"><thead><tr>';
                html += '<th>#</th>';
                html += '<th>Número</th>';
                html += '<th>Origen</th>';
                html += '<th>Items</th>';
                html += '<th>Total Cajas</th>';
                html += '<th>Total Unidades</th>';
                html += '<th>Valorado</th>';
                html += '<th>Fecha</th>';
                html += '<th>Acción</th>';
                html += '</tr></thead><tbody>';

                transferencias.forEach((transfer, index) => {
                    html += '<tr>';
                    html += '<td>' + (index + 1) + '</td>';
                    html += '<td><strong>' + escapeHtml(transfer.tr_numero) + '</strong></td>';
                    html += '<td>' + escapeHtml(transfer.sucursal_origen) + '</td>';
                    html += '<td style="text-align:center;"><strong>' + transfer.tr_total_items + '</strong></td>';
                    html += '<td style="text-align:center;"><strong>' + transfer.tr_total_cajas + '</strong></td>';
                    html += '<td style="text-align:center;"><strong style="color: #1976D2;">' + transfer.tr_total_unidades + '</strong></td>';
                    html += '<td style="text-align:right;">Bs. ' + formatearNumero(transfer.tr_total_valorado) + '</td>';
                    html += '<td>' + formatearFecha(transfer.tr_fecha_envio) + '</td>';
                    html += '<td>';
                    html += '<button type="button" class="btn primary" onclick="RecepcionManager.verDetalles(' + transfer.tr_id + ')">';
                    html += '<ion-icon name="eye-outline"></ion-icon> Ver';
                    html += '</button>';
                    html += '</td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                container.innerHTML = html;
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
                    document.getElementById('modal-detalles-transfer-recepcionar').style.display = 'flex';
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
                let html = '<div class="table-container"><table class="table" style="font-size: 13px;"><thead><tr>';
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
                document.getElementById('modal-detalles-transfer-recepcionar').style.display = 'none';
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
    </script>

<?php
} else {
    echo "<div class='alert warning'><p>No tiene permisos para acceder a esta sección</p></div>";
}
