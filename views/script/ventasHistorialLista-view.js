const VentasHistorialModals = (function() {
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

    const API_URL = getBaseURL() + 'ajax/ventasHistorialAjax.php';

    // ==================== UTILIDADES ====================
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
            } else {
                console.error(`  Modal no encontrado: ${modalId}`);
            }
        },

        cerrar(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
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

    // ==================== MODAL DETALLE ====================
    const detalle = {
        async abrir(veId, numeroDocumento) {
            console.log(' Abriendo detalle de venta:', {
                veId,
                numeroDocumento
            });

            document.getElementById('modalDetalleVeId').value = veId;
            utils.abrir('modalDetalleVenta');

            // Mostrar loading
            document.getElementById('tablaItemsVenta').innerHTML =
                '<tr><td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

            try {
                const data = await utils.ajax({
                    ventasHistorialAjax: 'detalle',
                    ve_id: veId
                });

                if (data.Alerta) {
                    Swal.fire({
                        title: data.Titulo,
                        text: data.texto,
                        icon: data.Tipo
                    });
                    utils.cerrar('modalDetalleVenta');
                    return;
                }

                // Llenar información general
                document.getElementById('detalleNumeroDocumento').textContent = data.venta.ve_numero_documento;
                document.getElementById('detalleFecha').textContent = data.venta.ve_fecha_emision;
                document.getElementById('detalleCliente').textContent = data.venta.cliente_nombre;
                document.getElementById('detalleCarnet').textContent = data.venta.cliente_carnet;
                document.getElementById('detalleVendedor').textContent = data.venta.vendedor_nombre;
                document.getElementById('detalleSucursal').textContent = data.venta.sucursal_nombre;
                document.getElementById('detalleCaja').textContent = data.venta.caja_nombre;
                document.getElementById('detalleNumeroFactura').textContent = data.venta.fa_numero || '-';

                // Llenar totales
                document.getElementById('detalleSubtotal').textContent = utils.formatearMoneda(data.venta.ve_subtotal);
                document.getElementById('detalleImpuesto').textContent = utils.formatearMoneda(data.venta.ve_impuesto);
                document.getElementById('detalleTotal').textContent = utils.formatearMoneda(data.venta.ve_total);

                // Llenar tabla de items
                const tbody = document.getElementById('tablaItemsVenta');
                if (data.items && data.items.length > 0) {
                    tbody.innerHTML = data.items.map(item => `
                        <tr>
                            <td>
                                <strong>${item.nombre}</strong>
                                ${item.presentacion ? '<br><small style="color:#666;">' + item.presentacion + '</small>' : ''}
                            </td>
                            <td>${item.lote}</td>
                            <td style="text-align:center;">${utils.formatearNumero(item.cantidad)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(item.precio_unitario)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(item.descuento)}</td>
                            <td style="text-align:right;"><strong>${utils.formatearMoneda(item.subtotal)}</strong></td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin items</td></tr>';
                }

            } catch (error) {
                console.error('  Error:', error);
                Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                utils.cerrar('modalDetalleVenta');
            }
        }
    };

    // ==================== REIMPRIMIR NOTA ====================
    const reimprimir = {
        async nota(veId) {
            console.log(' Reimprimiendo nota de venta:', veId);

            Swal.fire({
                title: 'Generando PDF...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const data = await utils.ajax({
                    ventasHistorialAjax: 'generar_pdf',
                    ve_id: veId
                });

                Swal.close();

                if (data.Alerta) {
                    Swal.fire({
                        title: data.Titulo,
                        text: data.texto,
                        icon: data.Tipo
                    });
                    return;
                }

                if (data.success && data.pdf_base64) {
                    window.abrirPDFDesdeBase64(data.pdf_base64, `Nota_Venta_${veId}.pdf`);
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
                console.error(' Error:', error);
                Swal.fire('Error', 'No se pudo generar el PDF', 'error');
            }
        }
    };

    // ==================== VER FACTURA ====================
    const factura = {
        ver(faId) {
            console.log(' Viendo factura:', faId);

            Swal.fire({
                title: 'Funcionalidad en desarrollo',
                text: 'La visualización de facturas se implementará próximamente',
                icon: 'info'
            });
        }
    };

    // ==================== API PÚBLICA ====================
    return {
        cerrar: utils.cerrar,
        verDetalle: detalle.abrir,
        reimprimirNota: reimprimir.nota,
        verFactura: factura.ver
    };
})();

// ==================== EXPORTAR EXCEL ====================
document.addEventListener('DOMContentLoaded', function() {
    const btnExcel = document.getElementById('btnExportarExcel');

    if (btnExcel) {
        btnExcel.addEventListener('click', function() {
            const form = document.querySelector('.filtro-dinamico');

            let url = getBaseURL() + 'ajax/ventasHistorialAjax.php?ventasHistorialAjax=exportar_excel';

            const fechaDesde = form.querySelector('input[name="fecha_desde"]');
            if (fechaDesde && fechaDesde.value) {
                url += '&fecha_desde=' + encodeURIComponent(fechaDesde.value);
            }

            const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
            if (fechaHasta && fechaHasta.value) {
                url += '&fecha_hasta=' + encodeURIComponent(fechaHasta.value);
            }

            const select1 = form.querySelector('select[name="select1"]');
            if (select1 && select1.value) {
                url += '&su_id=' + encodeURIComponent(select1.value);
            }

            const select2 = form.querySelector('select[name="select2"]');
            if (select2 && select2.value) {
                url += '&cliente=' + encodeURIComponent(select2.value);
            }

            const select3 = form.querySelector('select[name="select3"]');
            if (select3 && select3.value) {
                url += '&vendedor=' + encodeURIComponent(select3.value);
            }

            const select4 = form.querySelector('select[name="select4"]');
            if (select4 && select4.value) {
                url += '&tipo_documento=' + encodeURIComponent(select4.value);
            }

            console.log(' Descargando archivo:', url);

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Descargando',
                text: 'El archivo se está descargando...',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
});

function getBaseURL() {
    const serverUrl = document.documentElement.dataset.serverUrl;
    if (serverUrl) {
        return serverUrl.replace('ajax/notificacionesAjax.php', '');
    }
    const path = window.location.pathname;
    const match = path.match(/^\/([^\/]+)\//);
    return match ? "/" + match[1] + "/" : "/";
}
