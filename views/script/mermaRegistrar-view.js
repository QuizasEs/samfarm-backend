/* gestor del modulo de registro de merma
   el envio del formulario lo maneja el protocolo FormularioAjax (alertas.js) */
const MermaRegistroManager = (function() {
    'use strict';

    const elementos = {
        modal: null,
        inputLmId: null,
        inputCantidad: null,
        inputCantidadTotal: null,
        inputMedicamento: null,
        inputMotivo: null
    };

    /* inicializa el modulo */
    function init() {
        cargarElementos();
    }

    /* carga referencias a elementos del dom */
    function cargarElementos() {
        elementos.modal = document.getElementById('modalMermaRegistro');
        elementos.inputLmId = document.getElementById('lm_id');
        elementos.inputCantidad = document.getElementById('cantidadDisponible');
        elementos.inputCantidadTotal = document.getElementById('me_cantidad');
        elementos.inputMedicamento = document.getElementById('medicamentoNombre');
        elementos.inputMotivo = document.getElementById('me_motivo');
    }

    /* abre el modal de registro de merma */
    function abrirModal(lm_id, medicamento, cantidad_disponible) {
        elementos.inputLmId.value = lm_id;
        elementos.inputMedicamento.value = medicamento;
        elementos.inputCantidad.value = cantidad_disponible + ' unidades';
        elementos.inputCantidadTotal.value = cantidad_disponible;
        elementos.inputMotivo.value = '';

        elementos.modal.classList.add('open');
    }

    /* cierra el modal */
    function cerrarModal() {
        elementos.modal.classList.remove('open');
    }

    document.addEventListener('DOMContentLoaded', init);

    return {
        abrirModal,
        cerrarModal
    };

})();

// exponer funciones globales para compatibilidad con onclick desde la tabla
function abrirModalMermaRegistro(lm_id, medicamento, cantidad_disponible) {
    MermaRegistroManager.abrirModal(lm_id, medicamento, cantidad_disponible);
}

function cerrarModalMermaRegistro() {
    MermaRegistroManager.cerrarModal();
}
