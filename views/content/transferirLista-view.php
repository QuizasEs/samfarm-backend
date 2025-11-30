<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="container">
        <div class="title">
            <h3>
                <ion-icon name="swap-horizontal-outline"></ion-icon> Transferir Medicamentos
            </h3>
        </div>

        <form class="filtro-dinamico" id="form-buscar-lotes-transfer">
            <div class="filtro-dinamico-search">

                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                    <div class="form-fechas">
                        <small>Sucursal Origen</small>
                        <select class="select-filtro" name="su_origen_filter" id="su_origen_filter_transfer">
                            <option value="">Mi sucursal</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="form-fechas">
                    <small>Laboratorio</small>
                    <select class="select-filtro" name="laboratorio_filter_transfer" id="laboratorio_filter_transfer">
                        <option value="">Todos</option>
                        <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                            <option value="<?php echo $lab['la_id'] ?>"><?php echo $lab['la_nombre_comercial'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Vence hasta</small>
                    <input type="date" name="fecha_venc_max_transfer" id="fecha_venc_max_transfer">
                </div>

                <div class="search">
                    <input type="text" name="busqueda_transfer" id="busqueda_transfer" placeholder="Buscar medicamento o lote...">
                    <button type="button" class="btn-search" id="btn-buscar-lotes-transfer">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
        </form>

        <div id="resultado-busqueda-lotes-transfer" style="margin-top: 20px;">
            <p style="text-align:center; padding: 20px; color: #666;">
                <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                Use los filtros para buscar lotes disponibles
            </p>
        </div>

        <div class="title" style="margin-top: 40px;">
            <h3>
                <ion-icon name="list-outline"></ion-icon> Items a Transferir
            </h3>
        </div>

        <div id="lista-items-transfer-container">
            <p style="text-align:center; padding: 20px; color: #999;">
                <ion-icon name="cube-outline" style="font-size: 48px;"></ion-icon><br>
                No hay items agregados
            </p>
        </div>

        <div id="resumen-transfer-container" style="display:none; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div>
                    <strong>Total Items:</strong>
                    <p id="resumen-total-items-transfer" style="font-size: 20px; color: #1976D2;">0</p>
                </div>
                <div>
                    <strong>Total Cajas:</strong>
                    <p id="resumen-total-cajas-transfer" style="font-size: 20px; color: #1976D2;">0</p>
                </div>
                <div>
                    <strong>Total Unidades:</strong>
                    <p id="resumen-total-unidades-transfer" style="font-size: 20px; color: #1976D2;">0</p>
                </div>
                <div>
                    <strong>Total Valorado:</strong>
                    <p id="resumen-total-valorado-transfer" style="font-size: 20px; color: #27ae60;">Bs. 0.00</p>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <label for="observaciones-transfer">Observaciones (opcional):</label>
                <textarea id="observaciones-transfer" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <button type="button" class="btn success" id="btn-generar-transfer" style="font-size: 16px; padding: 12px 30px;">
                    <ion-icon name="send-outline"></ion-icon> Generar Transferencia
                </button>
            </div>
        </div>
    </div>

    <div class="modal" id="modal-agregar-item-transfer" style="display: none;">
        <input type="hidden" id="modal-stock-cajas-real-transfer">
        <input type="hidden" id="modal-stock-unidades-real-transfer">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Agregar a Transferencia</span>
                </div>
                <a class="close" onclick="TransferManager.cerrarModalAgregar()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <div class="modal-group">
                <input type="hidden" id="modal-lm-id-transfer">
                <input type="hidden" id="modal-med-id-transfer">
                <input type="hidden" id="modal-precio-compra-transfer">
                <input type="hidden" id="modal-precio-venta-transfer">
                <input type="hidden" id="modal-cant-blister-transfer">
                <input type="hidden" id="modal-cant-unidad-transfer">

                <div class="row">
                    <h4 id="modal-medicamento-nombre-transfer"></h4>
                    <p><strong>Lote:</strong> <span id="modal-lote-numero-transfer"></span></p>
                    <p><strong>Stock disponible:</strong> <span id="modal-stock-disponible-transfer"></span></p>
                    <p><strong>Vencimiento:</strong> <span id="modal-vencimiento-transfer"></span></p>
                </div>

                <div class="row">
                    <div class="col">
                        <label class="required">Cantidad (cajas)</label>
                        <input type="number" id="modal-cantidad-cajas-transfer" min="1" required>
                    </div>
                    <div class="col">
                        <label>Equivale a (unidades)</label>
                        <input type="number" id="modal-cantidad-unidades-transfer" readonly style="background: #f5f5f5;">
                    </div>
                </div>

                <div class="row">
                    <label class="required">Sucursal Destino</label>
                    <select id="modal-sucursal-destino-transfer" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="row">
                    <p style="background: #e8f5e9; padding: 10px; border-radius: 4px;">
                        <strong>Subtotal valorado:</strong>
                        <span id="modal-subtotal-transfer" style="color: #27ae60; font-size: 18px;">Bs. 0.00</span>
                    </p>
                </div>

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn warning" onclick="TransferManager.cerrarModalAgregar()">
                        Cancelar
                    </a>
                    <a href="javascript:void(0)" class="btn success" onclick="TransferManager.agregarItem()">
                        <ion-icon name="add-outline"></ion-icon> Agregar a Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const TransferManager = (function() {
            'use strict';

            const URL_AJAX = '<?php echo SERVER_URL; ?>ajax/transferirAjax.php';
            const SU_ACTUAL = <?php echo $_SESSION['sucursal_smp']; ?>;
            const ROL_USUARIO = <?php echo $_SESSION['rol_smp']; ?>;

            let items = [];

            function init() {
                configurarEventos();
            }

            function configurarEventos() {
                const btnBuscar = document.getElementById('btn-buscar-lotes-transfer');
                if (btnBuscar) {
                    btnBuscar.addEventListener('click', buscarLotes);
                }

                const inputBusqueda = document.getElementById('busqueda_transfer');
                if (inputBusqueda) {
                    inputBusqueda.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            buscarLotes();
                        }
                    });
                }

                const filtros = document.querySelectorAll('#form-buscar-lotes-transfer select');
                filtros.forEach(filtro => {
                    filtro.addEventListener('change', buscarLotes);
                });

                const inputCajas = document.getElementById('modal-cantidad-cajas-transfer');
                if (inputCajas) {
                    inputCajas.addEventListener('input', calcularUnidadesModal);
                }

                const btnGenerar = document.getElementById('btn-generar-transfer');
                if (btnGenerar) {
                    btnGenerar.addEventListener('click', generarTransferencia);
                }
            }

            async function buscarLotes() {
                const busqueda = document.getElementById('busqueda_transfer').value.trim();
                const laboratorio = document.getElementById('laboratorio_filter_transfer').value;
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
                formData.append('laboratorio', laboratorio);
                formData.append('fecha_venc_max', fechaVenc);

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

                    renderizarLotes(data);

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

                let html = '<div class="table-container"><table class="table"><thead><tr>';
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
                    html += '<td>' + escapeHtml(lote.laboratorio || 'N/A') + '</td>';
                    html += '<td style="text-align:center;"><strong>' + lote.lm_cant_actual_cajas + '</strong></td>';
                    html += '<td style="text-align:center;"><strong style="color: #1976D2;">' + lote.lm_cant_actual_unidades + '</strong></td>';
                    html += '<td style="color: ' + colorVenc + ';">' + formatearFecha(lote.lm_fecha_vencimiento) + advertencia + '</td>';
                    html += '<td>';
                    html += '<button type="button" class="btn primary" onclick="TransferManager.abrirModalAgregar(' +
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

                document.getElementById('modal-agregar-item-transfer').style.display = 'flex';
            }

            function cerrarModalAgregar() {
                document.getElementById('modal-agregar-item-transfer').style.display = 'none';
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

                let html = '<div class="table-container"><table class="table"><thead><tr>';
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
                    html += '<td><button type="button" class="btn danger" onclick="TransferManager.eliminarItem(' + index + ')"><ion-icon name="trash-outline"></ion-icon></button></td>';
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
                        await Swal.fire({
                            icon: 'success',
                            title: data.Titulo,
                            html: data.texto,
                            confirmButtonText: 'Entendido'
                        });

                        window.abrirPDFDesdeBase64(data.pdf_base64, 'Transferencia.pdf');

                        items = [];
                        renderizarListaItems();
                        document.getElementById('observaciones-transfer').value = '';
                        document.getElementById('resultado-busqueda-lotes-transfer').innerHTML = `
                    <p style="text-align:center; padding: 20px; color: #666;">
                        <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                        Use los filtros para buscar lotes disponibles
                    </p>
                `;
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

            document.addEventListener('click', (e) => {
                const modal = document.getElementById('modal-agregar-item-transfer');
                if (e.target === modal) {
                    cerrarModalAgregar();
                }
            });

            return {
                abrirModalAgregar,
                cerrarModalAgregar,
                agregarItem,
                eliminarItem
            };
        })();
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>