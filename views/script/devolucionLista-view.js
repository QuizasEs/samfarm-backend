const DevolucionManager = (function() {
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

    const API_URL = getBaseURL() + 'ajax/devolucionAjax.php';
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
