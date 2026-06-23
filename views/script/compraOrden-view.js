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
     * costo_lista = precio por CAJA (catálogo del proveedor)
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
     * Precio mínimo por caja = costo_lista por caja * (1 + margen_caja)
     */
    calcularPrecioMinCaja() {
        const costoCaja = parseFloat(this.costoLista?.value) || 0;
        const margen = parseFloat(this.margenCaja?.value) || 0;
        const precioMinCaja = costoCaja * (1 + margen / 100);
        if (this.precioMinCaja) this.precioMinCaja.value = precioMinCaja.toFixed(2);
    }

    /**
     * Precio mínimo unitario = costo unitario derivado * (1 + margen_unitario)
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