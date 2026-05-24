// proveedorLista-view.js - Consolidated script for Proveedores module (exports + modals)

function getBaseURL() {
    const serverUrl = document.documentElement.dataset.serverUrl;
    if (serverUrl) {
        return serverUrl.replace('ajax/notificacionesAjax.php', '');
    }
    const path = window.location.pathname;
    const match = path.match(/^\/([^\/]+)\//);
    return match ? "/" + match[1] + "/" : "/";
}

// ==================== Export Buttons ====================
document.addEventListener('DOMContentLoaded', function() {
    const btnExcelProveedor = document.getElementById('btnExportarExcelProveedor');

    if (btnExcelProveedor) {
        btnExcelProveedor.addEventListener('click', function(e) {
            e.preventDefault();

            const form = document.querySelector('.filtro-dinamico');
            if (!form) {
                console.warn('No se encontró el formulario de filtros');
                return;
            }

            const busqueda = form.querySelector('input[name="busqueda"]');
            const select1 = form.querySelector('select[name="select1"]');
            const select2 = form.querySelector('select[name="select2"]');
            const select3 = form.querySelector('select[name="select3"]');
            const fechaDesde = form.querySelector('input[name="fecha_desde"]');
            const fechaHasta = form.querySelector('input[name="fecha_hasta"]');

            let url = getBaseURL() + 'ajax/proveedoresAjax.php?exportar=excel';

            if (busqueda && busqueda.value.trim()) {
                url += '&busqueda=' + encodeURIComponent(busqueda.value.trim());
            }

            if (select1 && select1.value) {
                url += '&select1=' + encodeURIComponent(select1.value);
            }

            if (select2 && select2.value) {
                url += '&select2=' + encodeURIComponent(select2.value);
            }

            if (select3 && select3.value) {
                url += '&select3=' + encodeURIComponent(select3.value);
            }

            if (fechaDesde && fechaDesde.value) {
                url += '&fecha_desde=' + encodeURIComponent(fechaDesde.value);
            }

            if (fechaHasta && fechaHasta.value) {
                url += '&fecha_hasta=' + encodeURIComponent(fechaHasta.value);
            }

            console.log('Descargando Excel de proveedores:', url);

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Descargando',
                text: 'El archivo Excel se está descargando...',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }

    // Función para el botón PDF de proveedores
    const btnPDF = document.getElementById('btnExportarPDFProveedor');
    if (btnPDF) {
        btnPDF.addEventListener('click', function(e) {
            e.preventDefault();

            const form = document.querySelector('.filtro-dinamico');
            if (!form) {
                console.warn('No se encontró el formulario de filtros');
                return;
            }

            const busqueda = form.querySelector('input[name="busqueda"]');
            const select1 = form.querySelector('select[name="select1"]');
            const select2 = form.querySelector('select[name="select2"]');
            const select3 = form.querySelector('select[name="select3"]');
            const fechaDesde = form.querySelector('input[name="fecha_desde"]');
            const fechaHasta = form.querySelector('input[name="fecha_hasta"]');

            let url = getBaseURL() + 'ajax/proveedoresAjax.php?exportar=pdf';

            if (busqueda && busqueda.value.trim()) {
                url += '&busqueda=' + encodeURIComponent(busqueda.value.trim());
            }

            if (select1 && select1.value) {
                url += '&select1=' + encodeURIComponent(select1.value);
            }

            if (select2 && select2.value) {
                url += '&select2=' + encodeURIComponent(select2.value);
            }

            if (select3 && select3.value) {
                url += '&select3=' + encodeURIComponent(select3.value);
            }

            if (fechaDesde && fechaDesde.value) {
                url += '&fecha_desde=' + encodeURIComponent(fechaDesde.value);
            }

            if (fechaHasta && fechaHasta.value) {
                url += '&fecha_hasta=' + encodeURIComponent(fechaHasta.value);
            }

            console.log('Generando PDF de proveedores:', url);

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Generando PDF',
                text: 'El reporte se está generando...',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
});

// ==================== ProveedoresModals ====================
const ProveedoresModals = (function() {
    'use strict';

    const API_URL = getBaseURL() + 'ajax/proveedoresAjax.php';

    const utils = {
        async ajax(params) {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(params)
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        },

        abrir(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('open');
            }
        },

        cerrar(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        },

        formatearFecha(fecha) {
            const d = new Date(fecha);
            return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
        },

        formatearNumero(num) {
            return parseInt(num || 0).toLocaleString('es-BO');
        },

        formatearMoneda(num) {
            return 'Bs ' + parseFloat(num || 0).toFixed(2);
        }
    };

    const detalle = {
        async abrir(prId, nombre) {
            const modalNombre = document.getElementById('modalDetalleProveedorNombre');
            const modalPrId = document.getElementById('modalDetallePrId');
            const tablaCompras = document.getElementById('tablaUltimasCompras');
            const tablaMedicamentos = document.getElementById('tablaTopMedicamentos');

            if (!modalNombre || !tablaCompras || !tablaMedicamentos) {
                console.error('Elementos del modal no encontrados');
                return;
            }

            modalNombre.textContent = nombre;
            if (modalPrId) modalPrId.value = prId;

            tablaCompras.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';
            tablaMedicamentos.innerHTML = '<tr><td colspan="4" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

            utils.abrir('modalDetalleProveedor');

            try {
                const data = await utils.ajax({
                    proveedoresAjax: 'detalle',
                    pr_id: prId
                });

                if (data.error) {
                    console.error('Error del servidor:', data.error);
                    return;
                }

                const setText = (id, value) => {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value || '-';
                };

                setText('detalleNombreCompleto', data.nombre_completo);
                setText('detalleNit', data.nit);
                setText('detalleTelefono', data.telefono);
                setText('detalleCorreo', data.correo);
                setText('detalleNombreComercial', data.direccion);
                setText('detalleFechaRegistro', data.fecha_registro);
                setText('detalleEstado', data.estado);
                setText('detalleTotalCompras', utils.formatearNumero(data.total_compras || 0));
                setText('detalleMontoTotal', utils.formatearMoneda(data.monto_total || 0));
                setText('detalleTotalLotes', utils.formatearNumero(data.total_lotes || 0));
                setText('detallePromedio', utils.formatearMoneda(data.promedio || 0));
                setText('detalleUltimaCompra', data.ultima_compra);
                setText('detalleAntiguedad', (data.antiguedad || 0) + ' días');

                const compras = data.ultimas_compras ?? [];
                const top = data.top_medicamentos ?? [];

                if (tablaCompras) {
                    tablaCompras.innerHTML =
                        compras.length ?
                        compras.map(c => `
                            <tr>
                                <td>${c.co_numero || '-'}</td>
                                <td>${utils.formatearFecha(c.co_fecha)}</td>
                                <td>${c.proveedor || '-'}</td>
                                <td>${utils.formatearMoneda(c.co_total)}</td>
                                <td>${utils.formatearNumero(c.total_items)}</td>
                                <td>${c.co_numero_factura || '-'}</td>
                            </tr>
                        `).join('') :
                        '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin compras</td></tr>';
                }

                if (tablaMedicamentos) {
                    tablaMedicamentos.innerHTML =
                        top.length ?
                        top.map(m => `
                            <tr>
                                <td>${m.med_nombre_quimico || '-'}</td>
                                <td>${utils.formatearNumero(m.veces_comprado)}</td>
                                <td>${m.proveedor || '-'}</td>
                                <td>${utils.formatearFecha(m.ultima_compra)}</td>
                            </tr>
                        `).join('') :
                        '<tr><td colspan="4" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin medicamentos</td></tr>';
                }
            } catch (error) {
                console.error('Error en detalle.abrir:', error);
                if (tablaCompras) {
                    tablaCompras.innerHTML = '<tr><td colspan="6" style="text-align:center;color:red;"><ion-icon name="alert-circle-outline"></ion-icon> Error de conexión</td></tr>';
                }
                if (tablaMedicamentos) {
                    tablaMedicamentos.innerHTML = '<tr><td colspan="4" style="text-align:center;color:red;"><ion-icon name="alert-circle-outline"></ion-icon> Error de conexión</td></tr>';
                }
            }
        }
    };

    const registro = {
        abrir() {
            utils.abrir('modalRegistroProveedor');
            const form = document.getElementById('formRegistroProveedor');
            if (form) form.reset();
        },
        cerrar() {
            utils.cerrar('modalRegistroProveedor');
        }
    };

    const edicion = {
        async abrir(prId) {
            utils.abrir('modalEdicionProveedor');

            const data = await utils.ajax({
                proveedoresAjax: 'obtener',
                pr_id: prId
            });

            document.getElementById('edicionPrId').value = data.pr_id;
            document.getElementById('edicionNombres').value = data.pr_razon_social || '';
            document.getElementById('edicionNit').value = data.pr_nit || '';
            document.getElementById('edicionTelefono').value = data.pr_telefono || '';
            document.getElementById('edicionCorreo').value = data.pr_correo || '';
            document.getElementById('edicionDireccion').value = data.pr_nombre_comercial || '';
        },
        cerrar() {
            utils.cerrar('modalEdicionProveedor');
        }
    };

    return {
        cerrar: utils.cerrar,
        verDetalle: detalle.abrir,
        abrirRegistro: registro.abrir,
        cerrarRegistro: registro.cerrar,
        abrirEdicion: edicion.abrir,
        cerrarEdicion: edicion.cerrar
    };
})();
