/**
 * Clase para gestionar la lógica de la Vista de Orden de Compra
 */
class CompraOrdenManager {
    constructor() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        this.cacheElements();
        this.bindEvents();
        this.initContadores();
        this.initPrecargaLote();
    }

    cacheElements() {
        this.form = document.getElementById('formCompra');
        this.costoLista = document.getElementById('costo_lista');
        this.margenUnitario = document.getElementById('margen_unitario');
        this.margenCaja = document.getElementById('margen_caja');
        this.cantidad = document.getElementById('cantidad');
        this.cantidadUnidades = document.getElementById('cantidad_unidades');
        this.precioVentaReg = document.getElementById('precio_venta_reg');
        this.precioMinCaja = document.getElementById('precio_min_caja');
        this.precioMinUnitario = document.getElementById('precio_min_unitario');
        this.numeroLote = document.getElementById('numero_lote');
        this.fechaVencimiento = document.getElementById('fecha_vencimiento');
        this.precioCompra = document.getElementById('precio_compra');
    }

    bindEvents() {
        // Cálculos automáticos (Evita el error de null al validar existencia)
        if (this.costoLista) {
            this.costoLista.addEventListener('input', () => {
                this.calcularPrecioVenta();
                this.calcularPrecioMinCaja();
                this.calcularPrecioMinUnitario();
            });
        }

        if (this.margenUnitario) {
            this.margenUnitario.addEventListener('input', (e) => {
                this.clampMargen(e.target);
                this.calcularPrecioVenta();
                this.calcularPrecioMinUnitario();
            });
            this.margenUnitario.addEventListener('blur', (e) => this.validarMargen(e.target));
        }

        if (this.margenCaja) {
            this.margenCaja.addEventListener('input', (e) => {
                this.clampMargen(e.target);
                this.calcularPrecioMinCaja();
            });
            this.margenCaja.addEventListener('blur', (e) => this.validarMargen(e.target));
        }

        if (this.cantidad) {
            this.cantidad.addEventListener('input', () => this.calcularPrecioMinCaja());
        }

        if (this.cantidadUnidades) {
            this.cantidadUnidades.addEventListener('input', () => {
                this.calcularPrecioVenta();
                this.calcularPrecioMinCaja();
                this.calcularPrecioMinUnitario();
            });
        }

        // Set JSON data on form submit
        this.form.addEventListener('submit', (e) => {
            if (typeof ModalManager !== 'undefined') {
                const lotes = ModalManager.obtenerLotes();
                const totales = ModalManager.obtenerTotales();
                document.getElementById('lotes_json').value = JSON.stringify(lotes);
                document.getElementById('totales_json').value = JSON.stringify(totales);
            }
        });
    }

    initContadores() {
        // Sincronizar con el ModalManager si existe
        const originalAbrirModal = window.abrirModal || function() {};
        window.abrirModal = (id, nombre) => {
            originalAbrirModal(id, nombre);
            // Re-cacheamos elementos porque el modal puede haber inyectado nuevos o haberlos hecho visibles
            this.cacheElements();
        };
    }

    initPrecargaLote() {
        if (typeof ModalManager === 'undefined' || typeof ModalManager.abrirModal !== 'function') {
            return;
        }

        const originalAbrirModal = ModalManager.abrirModal.bind(ModalManager);
        const form = document.getElementById('formCompra');
        const ajaxUrl = form ? form.getAttribute('action') : 'ajax/compraAjax.php';

        ModalManager.abrirModal = (medId, nombre) => {
            originalAbrirModal(medId, nombre);
            this.precargarUltimoLote(medId, ajaxUrl);
        };
    }

    precargarUltimoLote(medId, ajaxUrl) {
        if (!medId) return;

        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                compraAjax: 'ultimo_lote_producto',
                med_id: medId
            })
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!data || data.error || !this.esLoteUtil(data)) return;
            this.pintarDatosLote(data);
        })
        .catch(() => {});
    }

    esLoteUtil(data) {
        if (!data || typeof data !== 'object') return false;
        return !!data.lm_numero_lote ||
               data.lm_costo_lista > 0 ||
               data.lm_precio_costo > 0 ||
               data.lm_cant_caja > 0 ||
               data.lm_cant_unidad > 0 ||
               data.lm_precio_venta > 0;
    }

    pintarDatosLote(data) {
        if (this.numeroLote) this.numeroLote.value = data.lm_numero_lote || '';

        const cantCajas = data.lm_cant_caja || '';
        const cantUnidades = data.lm_cant_unidad || '';
        if (this.cantidad) this.cantidad.value = cantCajas;
        if (this.cantidadUnidades) this.cantidadUnidades.value = cantUnidades;
        if (this.fechaVencimiento) this.fechaVencimiento.value = data.lm_fecha_vencimiento || '';

        const costoLista = data.lm_costo_lista || 0;

        if (this.precioCompra) this.precioCompra.value = costoLista > 0 ? costoLista : '';
        if (this.costoLista) this.costoLista.value = costoLista || '';

        const tieneMargenU = data.lm_margen_u !== null && data.lm_margen_u !== undefined;
        const tieneMargenC = data.lm_margen_c !== null && data.lm_margen_c !== undefined;

        if (this.margenUnitario) this.margenUnitario.value = tieneMargenU ? data.lm_margen_u : '';
        if (this.margenCaja) this.margenCaja.value = tieneMargenC ? data.lm_margen_c : '';
        if (this.precioVentaReg) this.precioVentaReg.value = data.lm_precio_venta || '';

        if (tieneMargenU) {
            if (this.precioMinUnitario) this.precioMinUnitario.value = data.lm_precio_min_u || '';
            this.calcularPrecioMinUnitario();
        } else {
            if (this.precioMinUnitario) this.precioMinUnitario.value = '';
            if (this.margenUnitario) this.margenUnitario.value = '';
        }

        if (tieneMargenC) {
            if (this.precioMinCaja) this.precioMinCaja.value = data.lm_precio_min_c || '';
            this.calcularPrecioMinCaja();
        } else {
            if (this.precioMinCaja) this.precioMinCaja.value = '';
            if (this.margenCaja) this.margenCaja.value = '';
        }

        const tieneCostoBase = costoLista > 0 && (cantCajas || 0) > 0 && (cantUnidades || 0) > 0;

        if (tieneCostoBase) {
            this.calcularPrecioVenta();
        }
    }

    setHiddenInput(id, value) {
        let input = document.getElementById(id);
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = id;
            input.id = id;
            this.form.appendChild(input);
        }
        input.value = value;
    }

    /**
     * costo_lista = precio por caja
     * Se deriva el costo unitario dividiendo entre unidades por caja
     */
    calcularPrecioVenta() {
        const costoCaja = parseFloat(this.costoLista?.value) || 0;
        const margen = parseFloat(this.margenUnitario?.value) || 0;
        const unidadesPorCaja = parseInt(this.cantidadUnidades?.value) || 1;
        const costoUnitario = costoCaja / unidadesPorCaja;
        const precioVenta = costoUnitario + (costoUnitario * margen / 100);
        if (this.precioVentaReg) this.precioVentaReg.value = precioVenta.toFixed(2);
    }

    /**
     * Precio mínimo por caja = costo_lista * (1 + margen_caja)
     */
    calcularPrecioMinCaja() {
        const costoCaja = parseFloat(this.costoLista?.value) || 0;
        const margen = parseFloat(this.margenCaja?.value) || 0;
        const precioMinCaja = costoCaja * (1 + margen / 100);
        if (this.precioMinCaja) this.precioMinCaja.value = precioMinCaja.toFixed(2);
    }

    /**
     * Precio mínimo unitario = (costo_lista / unidades) * (1 + margen_unitario)
     */
    calcularPrecioMinUnitario() {
        const costoCaja = parseFloat(this.costoLista?.value) || 0;
        const margen = parseFloat(this.margenUnitario?.value) || 0;
        const unidadesPorCaja = parseInt(this.cantidadUnidades?.value) || 1;
        const costoUnitario = costoCaja / unidadesPorCaja;
        const precioMinUnitario = costoUnitario * (1 + margen / 100);
        if (this.precioMinUnitario) this.precioMinUnitario.value = precioMinUnitario.toFixed(2);
    }

    clampMargen(input) {
        let value = input.value;
        
        // Permitir vacío para que el usuario pueda borrar todo
        if (value === "") return;

        // Eliminar cualquier caracter que no sea número o punto
        value = value.replace(/[^0-9.]/g, "");

        // Asegurar solo un punto decimal
        const parts = value.split(".");
        if (parts.length > 2) {
            value = parts[0] + "." + parts.slice(1).join("").replace(/\./g, "");
        }

        // Limitar a 2 decimales
        if (parts.length > 1 && parts[1].length > 2) {
            value = parts[0] + "." + parts[1].substring(0, 2);
        }

        // Evitar que el 0 inicial interfiera con otros números, a menos que sea 0.
        if (value.length > 1 && value.startsWith("0") && value[1] !== ".") {
            value = value.substring(1);
        }

        // No superar el límite de 100
        if (parseFloat(value) > 100) {
            value = "100";
        }

        if (input.value !== value) {
            input.value = value;
        }
    }

    validarMargen(input) {
        let valor = parseFloat(input.value);
        if (isNaN(valor) || valor < 0) {
            input.value = "0.00";
        } else {
            if (valor > 100) valor = 100;
            input.value = valor.toFixed(2);
        }
    }
}

// Instanciar el manager de la vista
const compraManager = new CompraOrdenManager();

// Funciones globales para compatibilidad con atributos onchange/onclick del HTML existente
function actualizarProveedor() {
    // El filtrado real por proveedor se maneja en el SearchManager global (script.php)
    // Esta función existe solo para evitar errores de "is not a function"
    if (typeof buscar === 'function') {
        buscar();
    }
}
function calcularPrecioVenta() { compraManager.calcularPrecioVenta(); }
function calcularPrecioMinCaja() { compraManager.calcularPrecioMinCaja(); }
function calcularPrecioMinUnitario() { compraManager.calcularPrecioMinUnitario(); }
function forzarLimiteMargen(input) { compraManager.validarMargen(input); }
function validarMargen(input) { compraManager.validarMargen(input); }

// ===================== Dropdown con buscador (estilo módulo caja / medicamentos) =====================
function CompraDropdown(inputId, resultsId, tabla, campos, selectId) {
    this.input = document.getElementById(inputId);
    this.resultsContainer = document.getElementById(resultsId);
    this.select = document.getElementById(selectId);
    this.tabla = tabla;
    this.campos = campos;
    this.debounce = null;
    const form = document.getElementById('formCompra');
    this.apiUrl = form ? form.getAttribute('action') : 'ajax/compraAjax.php';
    if (this.input && this.resultsContainer) this.init();
}

CompraDropdown.prototype.escapeHtml = function(text) {
    return String(text).replace(/[&<>"'`]/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
};

CompraDropdown.prototype.search = async function(term) {
    if (term.length < 2) { this.hide(); return; }
    try {
        const body = new URLSearchParams();
        body.append('compraAjax', 'select_v2');
        body.append('tabla', this.tabla);
        body.append('campos', JSON.stringify(this.campos));
        body.append('termino', term);
        const response = await fetch(this.apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        });
        const items = await response.json();
        this.renderResults(items);
    } catch (err) {
        this.renderResults([]);
    }
};

CompraDropdown.prototype.renderResults = function(items) {
    if (!this.resultsContainer) return;
    if (!items.length) {
        this.resultsContainer.innerHTML = '<div class="search-results-item no-results">No se encontraron resultados</div>';
    } else {
        this.resultsContainer.innerHTML = items.map(item => {
            const id = item[this.campos[0]];
            const name = item[this.campos[1]] || '';
            const extra = item[this.campos[2]] || '';
            return `
                <div class="search-results-item" data-id="${id}" data-name="${this.escapeHtml(name)}" style="cursor: pointer; padding: 8px; border-bottom: 1px solid #eee;">
                    <div><strong>${this.escapeHtml(name)}</strong></div>
                    ${extra ? `<small style="color: #666;">${this.escapeHtml(extra)}</small>` : ''}
                </div>`;
        }).join('');
        this.attachListeners();
    }
    this.applyStyles();
    this.show();
};

CompraDropdown.prototype.applyStyles = function() {
    if (!this.resultsContainer) return;
    this.resultsContainer.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        background: var(--bg-primary, #fff);
        border: 1px solid var(--border-light, #ddd);
        border-top: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        border-radius: 0 0 4px 4px;
        margin-top: 0;
    `;
};

CompraDropdown.prototype.attachListeners = function() {
    this.resultsContainer.querySelectorAll('.search-results-item').forEach(item => {
        item.addEventListener('click', () => this.selectProvider(item));
    });
};

CompraDropdown.prototype.selectProvider = function(item) {
    this.input.value = item.dataset.name;
    this.input.dataset.selectedId = item.dataset.id;
    if (this.select) {
        this.select.value = item.dataset.id;
        this.select.dispatchEvent(new Event('change'));
    }
    this.hide();
};

CompraDropdown.prototype.show = function() { this.resultsContainer.style.display = 'block'; };
CompraDropdown.prototype.hide = function() { this.resultsContainer.style.display = 'none'; };

CompraDropdown.prototype.init = function() {
    this.input.addEventListener('input', (e) => {
        this.input.dataset.selectedId = '';
        if (this.select) this.select.value = '';
        clearTimeout(this.debounce);
        this.debounce = setTimeout(() => this.search(e.target.value.trim()), 300);
    });
    this.input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') this.hide();
    });
    document.addEventListener('click', (e) => {
        if (!this.resultsContainer.contains(e.target) && e.target !== this.input) this.hide();
    });
};

// Inicializar dropdowns de filtro (módulo de Compra)
new CompraDropdown('dd_Proveedor_filtro', 'dd_Proveedor_filtro_res', 'proveedores', ['pr_id', 'pr_razon_social', 'pr_nit'], 'Proveedor_filtro');
new CompraDropdown('dd_Form_reg', 'dd_Form_reg_res', 'forma_farmaceutica', ['ff_id', 'ff_nombre'], 'Form_reg');
new CompraDropdown('dd_Via_reg', 'dd_Via_reg_res', 'via_de_administracion', ['vd_id', 'vd_nombre'], 'Via_reg');
new CompraDropdown('dd_Uso_reg', 'dd_Uso_reg_res', 'uso_farmacologico', ['uf_id', 'uf_nombre'], 'Uso_reg');