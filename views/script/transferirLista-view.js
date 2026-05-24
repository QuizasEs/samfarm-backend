const TransferManager = (function() {
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

    const container = document.getElementById('transferir-container') || document.body;
    const SU_ACTUAL = parseInt(container.dataset.suActual || 0);
    const ROL_USUARIO = parseInt(container.dataset.rolUsuario || 0);

    const URL_AJAX = getBaseURL() + 'ajax/transferirAjax.php';

    let items = [];
    let currentPage = 1;

    function init() {
        configurarEventos();
    }

    function configurarEventos() {
        const btnBuscar = document.getElementById('btn-buscar-lotes-transfer');
        if (btnBuscar) {
            btnBuscar.addEventListener('click', () => buscarLotes(1));
        }

        const inputBusqueda = document.getElementById('busqueda_transfer');
        if (inputBusqueda) {
            inputBusqueda.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarLotes(1);
                }
            });
        }

        const filtros = document.querySelectorAll('#form-buscar-lotes-transfer select');
        filtros.forEach(filtro => {
            filtro.addEventListener('change', () => buscarLotes(1));
        });

        const fechaVencInput = document.getElementById('fecha_venc_max_transfer');
        if (fechaVencInput) {
            fechaVencInput.addEventListener('change', () => buscarLotes(1));
        }

        const inputCajas = document.getElementById('modal-cantidad-cajas-transfer');
        if (inputCajas) {
            inputCajas.addEventListener('input', calcularUnidadesModal);
        }

        const btnGenerar = document.getElementById('btn-generar-transfer');
        if (btnGenerar) {
            btnGenerar.addEventListener('click', generarTransferencia);
        }

        // Delegación para la paginación reutilizada (botones .pb generados por paginador_tablas_main)
        const pagContainer = document.getElementById('paginacion-lotes-transfer');
        if (pagContainer) {
            pagContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('.pb');
                if (!btn || btn.classList.contains('dis') || btn.disabled) return;
                const page = parseInt(btn.dataset.page || btn.getAttribute('data-page'));
                if (page) {
                    e.preventDefault();
                    buscarLotes(page);
                }
            });
        }
    }

    async function buscarLotes(pagina = null) {
        if (pagina !== null) {
            currentPage = pagina;
        }

        const busqueda = document.getElementById('busqueda_transfer').value.trim();
        const fechaVenc = document.getElementById('fecha_venc_max_transfer').value;

        let suOrigen = SU_ACTUAL;
        if (ROL_USUARIO === 1) {
            const suOrigenSelect = document.getElementById('su_origen_filter_transfer');
            if (suOrigenSelect && suOrigenSelect.value) {
                suOrigen = suOrigenSelect.value;
            }
        }

        const formData = new FormData();
        formData.append('transferirAjax', 'buscar_lotes');
        formData.append('su_origen', suOrigen);
        formData.append('busqueda', busqueda);
        formData.append('fecha_venc_max', fechaVenc);
        formData.append('pagina', currentPage);
        formData.append('registros', 15);

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

            renderizarLotes(data.lotes || []);
            const pagDiv = document.getElementById('paginacion-lotes-transfer');
            if (pagDiv) {
                pagDiv.innerHTML = data.html_paginacion || '';
            }

        } catch (error) {
            console.error('Error:', error);
            mostrarError('Error al buscar lotes');
        }
    }

    function renderizarLotes(lotes) {
        const container = document.getElementById('resultado-busqueda-lotes-transfer');

        if (!lotes || lotes.length === 0) {
            container.innerHTML = `
                <p style="text-align:center; padding: 20px; color: #999;">
                    <ion-icon name="information-circle-outline" style="font-size: 48px;"></ion-icon><br>
                    No se encontraron lotes disponibles
                </p>
            `;
            return;
        }

        let html = '<div class="tw tw-transferencias"><table><thead><tr>';
        html += '<th>#</th>';
        html += '<th>Medicamento</th>';
        html += '<th>Lote</th>';
        html += '<th>Laboratorio</th>';
        html += '<th>Stock (cajas)</th>';
        html += '<th>Stock (unidades)</th>';
        html += '<th>Vencimiento</th>';
        html += '<th>Acción</th>';
        html += '</tr></thead><tbody>';

        lotes.forEach((lote, index) => {
            const diasVencer = parseInt(lote.dias_vencer);
            let colorVenc = '#666';
            let advertencia = '';

            if (diasVencer < 0) {
                colorVenc = '#f44336';
                advertencia = '<br><small style="color: #f44336; font-weight: bold;">VENCIDO</small>';
            } else if (diasVencer <= 30) {
                colorVenc = '#ff9800';
                advertencia = '<br><small style="color: #ff9800; font-weight: bold;">Vence pronto (' + diasVencer + ' días)</small>';
            } else if (diasVencer <= 90) {
                colorVenc = '#ffc107';
            }

            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td><strong>' + escapeHtml(lote.med_nombre_quimico) + '</strong><br><small>' + escapeHtml(lote.med_principio_activo) + '</small></td>';
            html += '<td>' + escapeHtml(lote.lm_numero_lote) + '</td>';
            html += '<td>' + escapeHtml(lote.laboratorio || 'Sin laboratorio') + '</td>';
            html += '<td style="text-align:center;"><strong>' + lote.lm_cant_actual_cajas + '</strong></td>';
            html += '<td style="text-align:center;"><strong style="color: #1976D2;">' + lote.lm_cant_actual_unidades + '</strong></td>';
            html += '<td style="color: ' + colorVenc + ';">' + formatearFecha(lote.lm_fecha_vencimiento) + advertencia + '</td>';
            html += '<td>';
            if (lote.transferible == 1) {
                html += '<button type="button" class="btn btn-def" onclick="TransferManager.abrirModalAgregar(' +
                    lote.lm_id + ', ' +
                    lote.med_id + ', \'' +
                    escapeHtml(lote.med_nombre_quimico) + '\', \'' +
                    escapeHtml(lote.lm_numero_lote) + '\', ' +
                    lote.lm_cant_actual_cajas + ', ' +
                    lote.lm_cant_actual_unidades + ', ' +
                    lote.lm_cant_blister + ', ' +
                    lote.lm_cant_unidad + ', ' +
                    lote.lm_precio_compra + ', ' +
                    lote.lm_precio_venta + ', \'' +
                    lote.lm_fecha_vencimiento + '\')">';
                html += '<ion-icon name="add-circle-outline"></ion-icon> Seleccionar';
                html += '</button>';
            } else {
                html += '<span style="color: #999; font-style: italic;">No transferible</span>';
            }
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    function abrirModalAgregar(lmId, medId, nombreMed, numeroLote, stockCajas, stockUnidades, cantBlister, cantUnidad, precioCompra, precioVenta, vencimiento) {
        document.getElementById('modal-lm-id-transfer').value = lmId;
        document.getElementById('modal-med-id-transfer').value = medId;
        document.getElementById('modal-precio-compra-transfer').value = precioCompra;
        document.getElementById('modal-precio-venta-transfer').value = precioVenta;

        document.getElementById('modal-stock-cajas-real-transfer').value = stockCajas;
        document.getElementById('modal-stock-unidades-real-transfer').value = stockUnidades;
        document.getElementById('modal-cant-blister-transfer').value = cantBlister;
        document.getElementById('modal-cant-unidad-transfer').value = cantUnidad;

        document.getElementById('modal-medicamento-nombre-transfer').textContent = nombreMed;
        document.getElementById('modal-lote-numero-transfer').textContent = numeroLote;
        document.getElementById('modal-stock-disponible-transfer').textContent = stockCajas + ' cajas / ' + stockUnidades + ' unidades';
        document.getElementById('modal-vencimiento-transfer').textContent = formatearFecha(vencimiento);

        document.getElementById('modal-cantidad-cajas-transfer').value = '';
        document.getElementById('modal-cantidad-cajas-transfer').max = stockCajas;
        document.getElementById('modal-cantidad-unidades-transfer').value = '';
        document.getElementById('modal-sucursal-destino-transfer').value = '';
        document.getElementById('modal-subtotal-transfer').textContent = 'Bs. 0.00';

        const modal = document.getElementById('modal-agregar-item-transfer');
        modal.style.display = 'flex';
        modal.classList.add('open');
    }

    function cerrarModalAgregar() {
        const modal = document.getElementById('modal-agregar-item-transfer');
        modal.classList.remove('open');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    function calcularUnidadesModal() {
        const cajas = parseInt(document.getElementById('modal-cantidad-cajas-transfer').value) || 0;
        const stockCajasTotal = parseInt(document.getElementById('modal-stock-cajas-real-transfer').value) || 0;
        const stockUnidadesTotal = parseInt(document.getElementById('modal-stock-unidades-real-transfer').value) || 0;
        const cantBlister = parseInt(document.getElementById('modal-cant-blister-transfer').value) || 1;
        const cantUnidad = parseInt(document.getElementById('modal-cant-unidad-transfer').value) || 1;
        const precioCompra = parseFloat(document.getElementById('modal-precio-compra-transfer').value) || 0;

        if (cajas === 0 || stockCajasTotal === 0) {
            document.getElementById('modal-cantidad-unidades-transfer').value = 0;
            document.getElementById('modal-subtotal-transfer').textContent = 'Bs. 0.00';
            return;
        }

        let unidades;

        if (cajas === stockCajasTotal) {
            unidades = stockUnidadesTotal;
        } else {
            const unidadesPorCaja = cantBlister * cantUnidad;
            unidades = cajas * unidadesPorCaja;
        }

        const subtotal = cajas * precioCompra;

        document.getElementById('modal-cantidad-unidades-transfer').value = unidades;
        document.getElementById('modal-subtotal-transfer').textContent = 'Bs. ' + subtotal.toFixed(2);
    }

    function agregarItem() {
        const lmId = document.getElementById('modal-lm-id-transfer').value;
        const medId = document.getElementById('modal-med-id-transfer').value;
        const nombreMed = document.getElementById('modal-medicamento-nombre-transfer').textContent;
        const numeroLote = document.getElementById('modal-lote-numero-transfer').textContent;
        const cajas = parseInt(document.getElementById('modal-cantidad-cajas-transfer').value);
        const unidades = parseInt(document.getElementById('modal-cantidad-unidades-transfer').value);
        const suDestino = document.getElementById('modal-sucursal-destino-transfer').value;
        const suDestinoNombre = document.getElementById('modal-sucursal-destino-transfer').selectedOptions[0].text;
        const precioCompra = parseFloat(document.getElementById('modal-precio-compra-transfer').value);
        const precioVenta = parseFloat(document.getElementById('modal-precio-venta-transfer').value);
        const stockCajas = parseInt(document.getElementById('modal-cantidad-cajas-transfer').max);

        if (!cajas || cajas <= 0) {
            Swal.fire('Error', 'Debe ingresar una cantidad válida', 'error');
            return;
        }

        if (cajas > stockCajas) {
            Swal.fire('Error', 'La cantidad excede el stock disponible', 'error');
            return;
        }

        if (!suDestino) {
            Swal.fire('Error', 'Debe seleccionar una sucursal destino', 'error');
            return;
        }

        if (parseInt(suDestino) === SU_ACTUAL) {
            Swal.fire('Error', 'No puede transferir a la misma sucursal', 'error');
            return;
        }

        const subtotal = cajas * precioCompra;

        items.push({
            lm_id: lmId,
            med_id: medId,
            nombre_med: nombreMed,
            numero_lote: numeroLote,
            cantidad_cajas: cajas,
            cantidad_unidades: unidades,
            su_destino: suDestino,
            su_destino_nombre: suDestinoNombre,
            precio_compra: precioCompra,
            precio_venta: precioVenta,
            subtotal: subtotal
        });

        renderizarListaItems();
        cerrarModalAgregar();
    }

    function renderizarListaItems() {
        const container = document.getElementById('lista-items-transfer-container');
        const resumenContainer = document.getElementById('resumen-transfer-container');

        if (items.length === 0) {
            container.innerHTML = `
                <p style="text-align:center; padding: 20px; color: #999;">
                    <ion-icon name="cube-outline" style="font-size: 48px;"></ion-icon><br>
                    No hay items agregados
                </p>
            `;
            resumenContainer.style.display = 'none';
            return;
        }

        let html = '<div class="tw"><table><thead><tr>';
        html += '<th>#</th>';
        html += '<th>Medicamento</th>';
        html += '<th>Lote</th>';
        html += '<th>Cajas</th>';
        html += '<th>Unidades</th>';
        html += '<th>Sucursal Destino</th>';
        html += '<th>Subtotal</th>';
        html += '<th>Acción</th>';
        html += '</tr></thead><tbody>';

        items.forEach((item, index) => {
            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + escapeHtml(item.nombre_med) + '</td>';
            html += '<td>' + escapeHtml(item.numero_lote) + '</td>';
            html += '<td style="text-align:center;">' + item.cantidad_cajas + '</td>';
            html += '<td style="text-align:center;"><strong style="color: #1976D2;">' + item.cantidad_unidades + '</strong></td>';
            html += '<td><span style="background:#E3F2FD;padding:4px 10px;border-radius:4px;color:#1565C0;font-weight:600;">' + escapeHtml(item.su_destino_nombre) + '</span></td>';
            html += '<td style="text-align:right;">Bs. ' + item.subtotal.toFixed(2) + '</td>';
            html += '<td><button type="button" class="btn btn-war" onclick="TransferManager.eliminarItem(' + index + ')"><ion-icon name="trash-outline"></ion-icon></button></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        actualizarResumen();
        resumenContainer.style.display = 'block';
    }

    function eliminarItem(index) {
        Swal.fire({
            title: '¿Eliminar item?',
            text: '¿Está seguro de eliminar este item de la lista?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                items.splice(index, 1);
                renderizarListaItems();
            }
        });
    }

    function actualizarResumen() {
        const totalItems = items.length;
        const totalCajas = items.reduce((sum, item) => sum + item.cantidad_cajas, 0);
        const totalUnidades = items.reduce((sum, item) => sum + item.cantidad_unidades, 0);
        const totalValorado = items.reduce((sum, item) => sum + item.subtotal, 0);

        document.getElementById('resumen-total-items-transfer').textContent = totalItems;
        document.getElementById('resumen-total-cajas-transfer').textContent = totalCajas;
        document.getElementById('resumen-total-unidades-transfer').textContent = totalUnidades;
        document.getElementById('resumen-total-valorado-transfer').textContent = 'Bs. ' + totalValorado.toFixed(2);
    }

    async function generarTransferencia() {
        if (items.length === 0) {
            Swal.fire('Error', 'Debe agregar al menos un item', 'error');
            return;
        }

        const result = await Swal.fire({
            title: '¿Generar transferencia?',
            html: '<p>Se transferirán <strong>' + items.length + '</strong> items</p>' +
                '<p>Total: <strong>Bs. ' + items.reduce((sum, item) => sum + item.subtotal, 0).toFixed(2) + '</strong></p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar'
        });

        if (!result.isConfirmed) return;

        const observaciones = document.getElementById('observaciones-transfer').value.trim();

        const formData = new FormData();
        formData.append('transferirAjax', 'generar');
        formData.append('items_json', JSON.stringify(items));
        formData.append('observaciones', observaciones);

        Swal.fire({
            title: 'Procesando...',
            text: 'Generando transferencia',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(URL_AJAX, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            Swal.close();

            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            if (data.Tipo === 'success' && data.pdf_base64) {
                const result = await Swal.fire({
                    icon: 'success',
                    title: data.Titulo,
                    html: data.texto + '<br><br><p style="color: #666; font-size: 13px;">El PDF se descargará automáticamente</p>',
                    confirmButtonText: 'Entendido',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                window.abrirPDFDesdeBase64(data.pdf_base64, 'Transferencia_' + data.tr_numero + '.pdf');

                items = [];
                renderizarListaItems();
                document.getElementById('observaciones-transfer').value = '';
                document.getElementById('resultado-busqueda-lotes-transfer').innerHTML = `
                    <p style="text-align:center; padding: 20px; color: #666;">
                        <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                        Use los filtros para buscar lotes disponibles
                    </p>
                `;
                document.getElementById('paginacion-lotes-transfer').innerHTML = '';
            } else if (data.Tipo === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: data.Titulo,
                    html: data.texto,
                    confirmButtonText: 'Entendido',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });

                items = [];
                renderizarListaItems();
                document.getElementById('observaciones-transfer').value = '';
                document.getElementById('resultado-busqueda-lotes-transfer').innerHTML = `
                    <p style="text-align:center; padding: 20px; color: #666;">
                        <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                        Use los filtros para buscar lotes disponibles
                    </p>
                `;
                document.getElementById('paginacion-lotes-transfer').innerHTML = '';
            }

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Error al generar transferencia', 'error');
        }
    }

    function formatearFecha(fecha) {
        if (!fecha) return 'N/A';
        const [y, m, d] = fecha.split('-');
        return d + '/' + m + '/' + y;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function mostrarError(mensaje) {
        Swal.fire('Error', mensaje, 'error');
    }

    document.addEventListener('DOMContentLoaded', init);

    return {
        abrirModalAgregar,
        cerrarModalAgregar,
        agregarItem,
        eliminarItem
    };
})();
