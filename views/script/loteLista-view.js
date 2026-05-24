// loteLista-view.js - Consolidated script for Lote management (activation + editing + exports)

function getBaseURL() {
    const serverUrl = document.documentElement.dataset.serverUrl;
    if (serverUrl) {
        return serverUrl.replace('ajax/notificacionesAjax.php', '');
    }
    const path = window.location.pathname;
    const match = path.match(/^\/([^\/]+)\//);
    return match ? "/" + match[1] + "/" : "/";
}

// ==================== LoteManager (Activation) ====================
class LoteManager {
    constructor() {
        this.modalId = 'modalActivarLote';
    }

    openActivationModal(loteId, nombre) {
        document.getElementById('detalleLote').innerHTML = `¿Desea activar el lote del medicamento: <strong>${nombre}</strong>?`;

        const modal = document.getElementById(this.modalId);
        modal.style.display = 'flex';
        modal.classList.add('open');

        const confirmBtn = document.getElementById('btnConfirmarActivacion');
        confirmBtn.onclick = () => {
            this.activateLote(loteId);
        };
    }

    activateLote(loteId) {
        const url = getBaseURL() + 'ajax/loteAjax.php';
        const body = `loteAjax=active&id=${loteId}`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            const modal = document.getElementById(this.modalId);
            modal.classList.remove('open');
            setTimeout(() => modal.style.display = 'none', 300);

            Swal.fire({
                icon: data.Tipo,
                title: data.Titulo,
                text: data.texto
            });

            if (data.Alerta === 'recargar') {
                location.reload();
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al activar el lote'
            });
        });
    }
}

// Global instance
const loteManager = new LoteManager();

// ==================== Export Functions ====================
function exportarExcelLote() {
    const form = document.querySelector('.filtro-dinamico');
    const params = new URLSearchParams();
    params.append('loteAjax', 'exportar_excel_lote_controller');

    if (form) {
        const fechaDesde = form.querySelector('input[name="fecha_desde"]');
        const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
        const select1 = form.querySelector('select[name="select1"]');
        const select3 = form.querySelector('select[name="select3"]');
        const busqueda = form.querySelector('input[name="busqueda"]');

        if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
        if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
        if (select1 && select1.value) params.append('select1', select1.value);
        if (select3 && select3.value) params.append('select3', select3.value);
        if (busqueda && busqueda.value) params.append('busqueda', encodeURIComponent(busqueda.value));
    }

    const url = getBaseURL() + 'ajax/loteAjax.php?' + params.toString();
    window.open(url, '_blank');

    Swal.fire({
        icon: 'success',
        title: 'Descargando',
        text: 'El archivo Excel se está descargando...',
        timer: 2000,
        showConfirmButton: false
    });
}

function exportarPDFLote() {
    const form = document.querySelector('.filtro-dinamico');
    const params = new URLSearchParams();
    params.append('loteAjax', 'exportar_pdf');

    if (form) {
        const fechaDesde = form.querySelector('input[name="fecha_desde"]');
        const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
        const select1 = form.querySelector('select[name="select1"]');
        const select3 = form.querySelector('select[name="select3"]');
        const busqueda = form.querySelector('input[name="busqueda"]');

        if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
        if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
        if (select1 && select1.value) params.append('select1', select1.value);
        if (select3 && select3.value) params.append('select3', select3.value);
        if (busqueda && busqueda.value) params.append('busqueda', encodeURIComponent(busqueda.value));
    }

    const url = getBaseURL() + 'ajax/loteAjax.php?' + params.toString();
    window.open(url, '_blank');

    Swal.fire({
        icon: 'success',
        title: 'Generando PDF',
        text: 'El reporte se está generando...',
        timer: 2000,
        showConfirmButton: false
    });
}

// ==================== LoteModals (Editing) ====================
const LoteModals = (function() {
    'use strict';
    const API_URL = getBaseURL() + 'ajax/loteAjax.php';

    async function abrirEdicion(loteId) {
        try {
            const formData = new FormData();
            formData.append('loteAjax', 'obtener_lote');
            formData.append('lote_id', loteId);

            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            document.getElementById('editarLoteId').value = loteId;
            document.getElementById('modalEditarLoteTitulo').textContent = data.lm_numero_lote + ' - ' + data.med_nombre;
            document.getElementById('detalleEditarNumero').textContent = data.lm_numero_lote;
            document.getElementById('detalleEditarMedicamento').textContent = data.med_nombre;
            document.getElementById('detalleEditarProveedor').textContent = data.proveedor_nombres || 'Sin proveedor';
            document.getElementById('detalleEditarIngreso').textContent = data.lm_fecha_ingreso;
            document.getElementById('detalleEditarEstado').textContent = data.lm_estado;
            document.getElementById('detalleEditarPrecioCompra').textContent = data.lm_precio_compra + ' Bs';
            document.getElementById('detalleEditarPrecioVenta').textContent = data.lm_precio_venta + ' Bs';
            document.getElementById('detalleEditarVencimiento').textContent = data.lm_fecha_vencimiento;

            document.getElementById('editarCantidadCaja').value = data.lm_cant_caja;
            document.getElementById('editarCantidadUnidades').value = data.lm_cant_unidad;
            document.getElementById('editarPrecioCompra').value = data.lm_precio_compra;
            document.getElementById('editarPrecioVenta').value = data.lm_precio_venta;
            document.getElementById('editarFechaVencimiento').value = data.lm_fecha_vencimiento;
            /* campos de auditoria */
            document.getElementById('editarCostoLista').value = data.lm_costo_lista || '';
            document.getElementById('editarMargenU').value = data.lm_margen_u || '';
            document.getElementById('editarMargenC').value = data.lm_margen_c || '';
            document.getElementById('editarPrecioMinU').value = data.lm_precio_min_u || '';
            document.getElementById('editarPrecioMinC').value = data.lm_precio_min_c || '';

            document.getElementById('modalEditarLote').style.display = 'flex';
            document.getElementById('modalEditarLote').classList.add('open');

            // Bind calculation events and trigger initial calculations
            bindCalculationEvents();
            calcularPrecioVenta();
            calcularPrecioMinCaja();
            calcularPrecioMinUnitario();

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Ocurrió un error al cargar los datos del lote', 'error');
        }
    }

    function cerrarEdicion() {
        const modal = document.getElementById('modalEditarLote');
        modal.classList.remove('open');
        setTimeout(() => modal.style.display = 'none', 300);
        document.getElementById('formEditarLote').reset();
    }

    function calcularPrecioVenta() {
        const costo = parseFloat(document.getElementById('editarCostoLista')?.value) || 0;
        const margen = parseFloat(document.getElementById('editarMargenU')?.value) || 0;
        const precioVenta = costo + (costo * margen / 100);
        const precioVentaInput = document.getElementById('editarPrecioVenta');
        if (precioVentaInput) precioVentaInput.value = precioVenta.toFixed(2);
    }

    function calcularPrecioMinCaja() {
        const costo = parseFloat(document.getElementById('editarCostoLista')?.value) || 0;
        const margen = parseFloat(document.getElementById('editarMargenC')?.value) || 0;
        const unidadesPorCaja = 1;
        const precioMinCaja = costo * unidadesPorCaja * (1 + margen / 100);
        const precioMinCajaInput = document.getElementById('editarPrecioMinC');
        if (precioMinCajaInput) precioMinCajaInput.value = precioMinCaja.toFixed(2);
    }

    function calcularPrecioMinUnitario() {
        const costo = parseFloat(document.getElementById('editarCostoLista')?.value) || 0;
        const margen = parseFloat(document.getElementById('editarMargenC')?.value) || 0;
        const precioMinUnitario = costo * (1 + margen / 100);
        const precioMinUnitarioInput = document.getElementById('editarPrecioMinU');
        if (precioMinUnitarioInput) precioMinUnitarioInput.value = precioMinUnitario.toFixed(2);
    }

    function clampMargen(input) {
        let value = input.value;
        
        if (value === "") return;

        value = value.replace(/[^0-9.]/g, "");

        const parts = value.split(".");
        if (parts.length > 2) {
            value = parts[0] + "." + parts.slice(1).join("").replace(/\./g, "");
        }

        if (parts.length > 1 && parts[1].length > 2) {
            value = parts[0] + "." + parts[1].substring(0, 2);
        }

        if (value.length > 1 && value.startsWith("0") && value[1] !== ".") {
            value = value.substring(1);
        }

        if (parseFloat(value) > 100) {
            value = "100";
        }

        if (input.value !== value) {
            input.value = value;
        }
    }

    function validarMargen(input) {
        let valor = parseFloat(input.value);
        if (isNaN(valor) || valor < 0) {
            input.value = "0.00";
        } else {
            if (valor > 100) valor = 100;
            input.value = valor.toFixed(2);
        }
    }

    function bindCalculationEvents() {
        const costoLista = document.getElementById('editarCostoLista');
        const margenU = document.getElementById('editarMargenU');
        const margenC = document.getElementById('editarMargenC');

        if (costoLista) {
            costoLista.addEventListener('input', () => {
                calcularPrecioVenta();
                calcularPrecioMinCaja();
                calcularPrecioMinUnitario();
            });
        }

        if (margenU) {
            margenU.addEventListener('input', (e) => {
                clampMargen(e.target);
                calcularPrecioVenta();
            });
            margenU.addEventListener('blur', (e) => validarMargen(e.target));
        }

        if (margenC) {
            margenC.addEventListener('input', (e) => {
                clampMargen(e.target);
                calcularPrecioMinCaja();
                calcularPrecioMinUnitario();
            });
            margenC.addEventListener('blur', (e) => validarMargen(e.target));
        }
    }

    return {
        abrirEdicion,
        cerrarEdicion,
        bindCalculationEvents
    };
})();

// ==================== DOMContentLoaded for Export Buttons ====================
document.addEventListener('DOMContentLoaded', function() {
    const btnExcelLote = document.getElementById('btnExportarExcelLote');
    if (btnExcelLote) {
        btnExcelLote.addEventListener('click', function() {
            exportarExcelLote();
        });
    }

    const btnPDFLote = document.getElementById('btnExportarPDFLote');
    if (btnPDFLote) {
        btnPDFLote.addEventListener('click', function() {
            exportarPDFLote();
        });
    }
});
