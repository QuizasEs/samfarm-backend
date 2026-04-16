<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="cloud-download-outline"></ion-icon> Recepcionar Transferencias
                </div>
                <div class="psub">Administre las transferencias pendientes de recepción en su sucursal</div>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico" id="form-filtro-recepcionar">
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" id="busqueda_recepcionar" placeholder="Buscar por número de transferencia...">
                                <button type="button" class="btn btn-def btn-search" id="btn-buscar-recepcionar">
                                    <ion-icon name="search"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Transferencias Pendientes</div>
            </div>
            <div class="cb">
                <div id="resultado-busqueda-recepcionar">
                    <p class="txctr tmut" style="padding: 20px;">
                        <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                        Use el buscador para encontrar transferencias pendientes
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="mov" id="modal-detalles-transfer-recepcionar">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="document-text-outline"></ion-icon>
                        Detalles de Transferencia
                    </div>
                    <div class="ms">Revise los detalles antes de aceptar o rechazar</div>
                </div>
                <button class="mcl" onclick="RecepcionManager.cerrarModalDetalles()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Número Transferencia</div><div class="th5" id="modal-numero-transfer-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="radio-button-on-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Estado</div><div class="th5" id="modal-estado-transfer-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Sucursal Origen</div><div class="th5" id="modal-sucursal-origen-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Sucursal Destino</div><div class="th5" id="modal-sucursal-destino-recepcionar"></div></div></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Usuario Emisor</div><div class="th5" id="modal-usuario-emisor-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Fecha Envío</div><div class="th5" id="modal-fecha-envio-recepcionar"></div></div></div>
                            <div class="litem" style="border:none"><ion-icon name="chatbox-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Observaciones</div><div class="th5" id="modal-observaciones-recepcionar" style="font-style: italic;"></div></div></div>
                        </div>
                    </div>
                </div>

                <div class="stit">Items en Transferencia</div>
                <div class="card mb16">
                    <div class="cb">
                        <div id="modal-detalles-items-recepcionar" style="max-height: 400px; overflow-y: auto;"></div>
                    </div>
                </div>

                <div class="stit">Resumen</div>
                <div class="grid4 mb16">
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="cube-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-items-recepcionar">0</div>
                            <div class="sl">Total Items</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="archive-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-cajas-recepcionar">0</div>
                            <div class="sl">Total Cajas</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="medical-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-unidades-recepcionar">0</div>
                            <div class="sl">Total Unidades</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-valorado-recepcionar">Bs. 0.00</div>
                            <div class="sl">Valor Total</div>
                        </div>
                    </div>
                </div>

                <div id="modal-rechazo-container" style="display: none;">
                    <div class="alert alwar mb16">
                        <ion-icon name="warning-outline"></ion-icon>
                        <div>
                            <div class="altt">Modo Rechazo</div>
                            <div class="altx">Ingrese el motivo del rechazo.</div>
                        </div>
                    </div>
                    <div class="fg mb16">
                        <label class="fl req">Motivo de Rechazo</label>
                        <textarea class="ta" id="modal-motivo-rechazo" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="RecepcionManager.cerrarModalDetalles()">Cancelar</button>
                <button class="btn btn-dan" id="btn-rechazar-transfer-modal" onclick="RecepcionManager.mostrarRechazo()">
                    <ion-icon name="close-circle-outline"></ion-icon> Rechazar
                </button>
                <button class="btn btn-def" id="btn-aceptar-transfer-modal" onclick="RecepcionManager.aceptarTransferencia()">
                    <ion-icon name="checkmark-circle-outline"></ion-icon> Aceptar
                </button>
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

                let html = '<div class="tw"><table><thead><tr>';
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
                    html += '<button type="button" class="btn btn-def" onclick="RecepcionManager.verDetalles(' + transfer.tr_id + ')">';
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
    </script>

<?php
} else {
?>
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
<?php
}
