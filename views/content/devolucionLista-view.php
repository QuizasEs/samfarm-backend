<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2 || $_SESSION['rol_smp'] == 3)) {
?>

    <div class="">
        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="return-down-back-outline"></ion-icon> Iniciar Devolución o Cambio
                </div>
                <div class="psub">Busque una venta para procesar devolución o cambio de productos</div>
            </div>
        </div>

        <div class="card mb20">
            <div class="cb">
                <form id="form_buscar_venta">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Criterio de Búsqueda</label>
                            <select class="sel" name="criterio_busqueda" id="criterio_busqueda">
                                <option value="">Seleccione...</option>
                                <option value="numero_factura">Número de Factura</option>
                                <option value="numero_documento">Número de Documento</option>
                                <option value="fa_id">ID Factura</option>
                                <option value="ve_id">ID Venta</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Valor a buscar</label>
                            <div class="inpg">
                                <input type="text" class="inp" name="valor_busqueda" id="valor_busqueda" placeholder="Ingrese el valor a buscar...">
                                <button type="button" class="btn btn-def" id="btn_buscar_venta">
                                    <ion-icon name="search"></ion-icon> Buscar
                                </button>
                            </div>
                        </div>
                        <div></div>
                    </div>
                </form>
            </div>
        </div>

        <div id="resultado_venta_container" style="display: none;">
            <div class="ph mb8">
                <div>
                    <div class="ptit">
                        <ion-icon name="document-text-outline"></ion-icon> Información de la Venta
                    </div>
                </div>
            </div>

            <div class="card mb20">
                <div class="cb">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Número de Documento</label>
                            <input type="text" class="inp" id="info_numero_documento" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Número de Factura</label>
                            <input type="text" class="inp" id="info_numero_factura" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Cliente</label>
                            <input type="text" class="inp" id="info_cliente" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Fecha de Venta</label>
                            <input type="text" class="inp" id="info_fecha" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Sucursal</label>
                            <input type="text" class="inp" id="info_sucursal" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Total</label>
                            <input type="text" class="inp" id="info_total" readonly>
                        </div>
                    </div>

                    <input type="hidden" id="venta_ve_id">
                    <input type="hidden" id="venta_fa_id">
                    <input type="hidden" id="venta_su_id">
                </div>
            </div>

            <div class="ph mb8">
                <div>
                    <div class="ptit">
                        <ion-icon name="list-outline"></ion-icon> Productos de la Venta
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="tw">
                    <table>
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Medicamento</th>
                                <th>Lote</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_items_venta">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mov" id="modalDevolucion">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="return-down-back-outline"></ion-icon> Procesar Devolución
                    </div>
                </div>
                <button class="mcl" onclick="DevolucionManager.cerrarModal()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <input type="hidden" id="modal_dv_id">
                <input type="hidden" id="modal_med_id">
                <input type="hidden" id="modal_lm_id">
                <input type="hidden" id="modal_precio_unitario">

                <div class="th4 mb12">Información del Producto</div>

                <div class="fr">
                    <div class="fg">
                        <label class="fl">Medicamento</label>
                        <input type="text" class="inp" id="modal_nombre_medicamento" readonly>
                    </div>
                    <div class="fg">
                        <label class="fl">Lote</label>
                        <input type="text" class="inp" id="modal_lote" readonly>
                    </div>
                </div>

                <div class="fr">
                    <div class="fg">
                        <label class="fl">Cantidad Original</label>
                        <input type="text" class="inp" id="modal_cantidad_original" readonly>
                    </div>
                    <div class="fg">
                        <label class="fl">Cantidad a Devolver <span class="tdan">*</span></label>
                        <input type="number" class="inp" id="modal_cantidad_devolver" min="1" required>
                    </div>
                </div>

                <div class="fg">
                    <label class="fl">Motivo de Devolución <span class="tdan">*</span></label>
                    <textarea class="ta" id="modal_motivo" rows="3" placeholder="Describa el motivo de la devolución..." required></textarea>
                </div>

                <div class="fg">
                    <label class="fl">Tipo de Devolución <span class="tdan">*</span></label>
                    <div class="flxc g20">
                        <label class="chkg">
                            <input type="radio" name="tipo_devolucion" value="devolucion" checked>
                            <span class="radb"></span>
                            <span class="chl">Solo Devolución</span>
                        </label>
                        <label class="chkg">
                            <input type="radio" name="tipo_devolucion" value="cambio">
                            <span class="radb"></span>
                            <span class="chl">Devolución con Cambio</span>
                        </label>
                    </div>
                </div>

                <div id="info_cambio_container" style="display: none;">
                    <div class="alert alinf mb0">
                        <ion-icon name="information-circle-outline"></ion-icon>
                        <div>
                            <div class="altt">Cambio</div>
                            <div class="altx">Se entregará el mismo medicamento de un lote disponible. No se genera nueva venta ni movimiento de caja.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-sec" onclick="DevolucionManager.cerrarModal()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-def" onclick="DevolucionManager.confirmarDevolucion()">
                    <ion-icon name="checkmark-outline"></ion-icon> Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
        const DevolucionManager = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/devolucionAjax.php';
            let ventaActual = null;
            let itemsVenta = [];

            function init() {
                bindEvents();
            }

            function bindEvents() {
                const btnBuscar = document.getElementById('btn_buscar_venta');
                if (btnBuscar) {
                    btnBuscar.addEventListener('click', buscarVenta);
                }

                const valorInput = document.getElementById('valor_busqueda');
                if (valorInput) {
                    valorInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            buscarVenta();
                        }
                    });
                }

                const tipoRadios = document.querySelectorAll('input[name="tipo_devolucion"]');
                tipoRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        const infoCambio = document.getElementById('info_cambio_container');
                        if (this.value === 'cambio') {
                            infoCambio.style.display = 'block';
                        } else {
                            infoCambio.style.display = 'none';
                        }
                    });
                });

                const modal = document.getElementById('modalDevolucion');
                if (modal) {
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) {
                            cerrarModal();
                        }
                    });
                }
            }

            async function buscarVenta() {
                const criterio = document.getElementById('criterio_busqueda').value;
                const valor = document.getElementById('valor_busqueda').value.trim();

                if (!criterio) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo requerido',
                        text: 'Seleccione un criterio de búsqueda'
                    });
                    return;
                }

                if (!valor) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Campo requerido',
                        text: 'Ingrese un valor para buscar'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Buscando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const formData = new FormData();
                    formData.append('devolucionAjax', 'buscar_venta');
                    formData.append('criterio', criterio);
                    formData.append('valor', valor);

                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    Swal.close();

                    if (data.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No encontrado',
                            text: data.mensaje
                        });
                        return;
                    }

                    mostrarResultadoVenta(data.venta, data.items);

                } catch (error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo procesar la búsqueda'
                    });
                }
            }

            function mostrarResultadoVenta(venta, items) {
                ventaActual = venta;
                itemsVenta = items;

                document.getElementById('info_numero_documento').value = venta.numero_documento;
                document.getElementById('info_numero_factura').value = venta.numero_factura;
                document.getElementById('info_cliente').value = venta.cliente;
                document.getElementById('info_fecha').value = venta.fecha;
                document.getElementById('info_sucursal').value = venta.sucursal;
                document.getElementById('info_total').value = 'Bs. ' + parseFloat(venta.total).toFixed(2);

                document.getElementById('venta_ve_id').value = venta.ve_id;
                document.getElementById('venta_fa_id').value = venta.fa_id;
                document.getElementById('venta_su_id').value = venta.su_id;

                const tbody = document.getElementById('tabla_items_venta');
                tbody.innerHTML = '';

                items.forEach((item, index) => {
                    const tr = document.createElement('tr');

                    const estadoClass = item.estado == 0 ? 'style="opacity: 0.5; text-decoration: line-through;"' : '';
                    const cursorStyle = item.estado == 0 ? '' : 'cursor: pointer;';

                    tr.style.cssText = cursorStyle;
                    if (item.estado != 0) {
                        tr.onclick = () => DevolucionManager.abrirModalDevolucion(index);
                    }

                    tr.innerHTML = `
                <td ${estadoClass}>${index + 1}</td>
                <td ${estadoClass}>
                    <div class="td-main">${escapeHtml(item.nombre)}</div>
                    <div class="td-sub">${escapeHtml(item.principio_activo)}</div>
                </td>
                <td ${estadoClass}>${escapeHtml(item.lote)}</td>
                <td ${estadoClass}>${item.cantidad}</td>
                <td ${estadoClass}>Bs. ${parseFloat(item.precio_unitario).toFixed(2)}</td>
                <td ${estadoClass}>Bs. ${parseFloat(item.subtotal).toFixed(2)}</td>
            `;

                    tbody.appendChild(tr);
                });

                document.getElementById('resultado_venta_container').style.display = 'block';
            }

            function abrirModalDevolucion(index) {
                const item = itemsVenta[index];

                if (item.estado == 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Producto no disponible',
                        text: 'Este producto ya fue devuelto'
                    });
                    return;
                }

                document.getElementById('modal_dv_id').value = item.dv_id;
                document.getElementById('modal_med_id').value = item.med_id;
                document.getElementById('modal_lm_id').value = item.lm_id;
                document.getElementById('modal_precio_unitario').value = item.precio_unitario;
                document.getElementById('modal_nombre_medicamento').value = item.nombre;
                document.getElementById('modal_lote').value = item.lote;
                document.getElementById('modal_cantidad_original').value = item.cantidad;
                document.getElementById('modal_cantidad_devolver').value = item.cantidad;
                document.getElementById('modal_cantidad_devolver').max = item.cantidad;
                document.getElementById('modal_motivo').value = '';

                document.querySelector('input[name="tipo_devolucion"][value="devolucion"]').checked = true;
                document.getElementById('info_cambio_container').style.display = 'none';

                document.getElementById('modalDevolucion').classList.add('open');
            }

            function cerrarModal() {
                document.getElementById('modalDevolucion').classList.remove('open');
            }

            async function confirmarDevolucion() {
                const dv_id = document.getElementById('modal_dv_id').value;
                const med_id = document.getElementById('modal_med_id').value;
                const lm_id = document.getElementById('modal_lm_id').value;
                const precio_unitario = document.getElementById('modal_precio_unitario').value;
                const cantidad = parseInt(document.getElementById('modal_cantidad_devolver').value);
                const motivo = document.getElementById('modal_motivo').value.trim();
                const tipo = document.querySelector('input[name="tipo_devolucion"]:checked').value;

                if (!cantidad || cantidad <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cantidad inválida',
                        text: 'Ingrese una cantidad válida'
                    });
                    return;
                }

                const cantidadMax = parseInt(document.getElementById('modal_cantidad_devolver').max);
                if (cantidad > cantidadMax) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cantidad excedida',
                        text: `La cantidad máxima es ${cantidadMax}`
                    });
                    return;
                }

                if (!motivo) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Motivo requerido',
                        text: 'Debe especificar el motivo de la devolución'
                    });
                    return;
                }

                const confirmResult = await Swal.fire({
                    title: '¿Confirmar devolución?',
                    html: `
                <p><strong>Producto:</strong> ${document.getElementById('modal_nombre_medicamento').value}</p>
                <p><strong>Cantidad:</strong> ${cantidad}</p>
                <p><strong>Tipo:</strong> ${tipo === 'cambio' ? 'Devolución con cambio' : 'Solo devolución'}</p>
                <p><strong>Motivo:</strong> ${motivo}</p>
            `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, confirmar',
                    cancelButtonText: 'Cancelar'
                });

                if (!confirmResult.isConfirmed) return;

                Swal.fire({
                    title: 'Procesando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const items = [{
                        dv_id: dv_id,
                        med_id: med_id,
                        lm_id: lm_id,
                        cantidad: cantidad,
                        precio_unitario: precio_unitario,
                        motivo: motivo,
                        tipo: tipo
                    }];

                    const formData = new FormData();
                    formData.append('devolucionAjax', 'procesar');
                    formData.append('ve_id', ventaActual.ve_id);
                    formData.append('fa_id', ventaActual.fa_id);
                    formData.append('su_id', ventaActual.su_id);
                    formData.append('items_devolucion', JSON.stringify(items));

                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    Swal.close();

                    if (data.Alerta === 'recargar') {
                        await Swal.fire({
                            icon: data.Tipo,
                            title: data.Titulo,
                            text: data.texto
                        });
                        window.location.reload();
                    } else {
                        Swal.fire({
                            icon: data.Tipo,
                            title: data.Titulo,
                            text: data.texto
                        });
                    }

                } catch (error) {
                    console.error('Error:', error);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo procesar la devolución'
                    });
                }
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
                return String(text).replace(/[&<>"']/g, m => map[m]);
            }

            document.addEventListener('DOMContentLoaded', init);

            return {
                buscarVenta,
                abrirModalDevolucion,
                cerrarModal,
                confirmarDevolucion
            };
        })();
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
