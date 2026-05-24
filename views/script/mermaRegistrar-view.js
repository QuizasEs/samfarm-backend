/* gestor del modulo de registro de merma */
const MermaRegistroManager = (function() {
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

    let estado = {
        lm_id: null,
        cantidad: 0,
        apiUrl: getBaseURL() + 'ajax/mermaRegistrarAjax.php'
    };

    const elementos = {
        modal: null,
        form: null,
        inputLmId: null,
        inputMedicamento: null,
        inputCantidad: null,
        inputMotivo: null
    };

    /* inicializa el modulo */
    function init() {
        cargarElementos();
        registrarEventos();
    }

    /* carga referencias a elementos del dom */
    function cargarElementos() {
        elementos.modal = document.getElementById('modalMermaRegistro');
        elementos.form = document.getElementById('formMermaRegistro');
        elementos.inputLmId = document.getElementById('lm_id');
        elementos.inputMedicamento = document.getElementById('medicamentoNombre');
        elementos.inputCantidad = document.getElementById('cantidadDisponible');
        elementos.inputMotivo = document.getElementById('me_motivo');
    }

    /* registra todos los eventos del modulo */
    function registrarEventos() {
    }

    /* abre el modal de registro de merma */
    function abrirModal(lm_id, medicamento, cantidad_disponible) {
        estado.lm_id = lm_id;
        estado.cantidad = cantidad_disponible;

        elementos.inputLmId.value = lm_id;
        elementos.inputMedicamento.value = medicamento;
        elementos.inputCantidad.value = cantidad_disponible + ' unidades';
        elementos.inputMotivo.value = '';

        elementos.modal.classList.add('open');
    }

    /* cierra el modal y limpia el estado */
    function cerrarModal() {
        elementos.modal.classList.remove('open');
        
        estado.lm_id = null;
        estado.cantidad = 0;
    }

    /* valida los campos del formulario */
    function validarFormulario() {
        const motivo = elementos.inputMotivo.value.trim();
        
        if (!estado.lm_id || !motivo) {
            Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
            return false;
        }

        return true;
    }

    /* procesa el envio del formulario */
    async function guardar() {
        if (!validarFormulario()) return;

        const formData = new FormData();
        formData.append('lm_id', estado.lm_id);
        formData.append('me_cantidad', estado.cantidad);
        formData.append('me_motivo', elementos.inputMotivo.value.trim());
        formData.append('mermaRegistrarAjax', 'crear');

        try {
            const response = await fetch(estado.apiUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.Alerta === 'redireccionar') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Merma Registrada',
                    text: 'Se registraron ' + estado.cantidad + ' unidades como merma',
                    confirmButtonText: 'Aceptar'
                });
                
                window.location.href = data.URL;
            } else if (data.Alerta === 'simple') {
                Swal.fire(data.Titulo, data.Texto, data.Tipo);
            }

            cerrarModal();

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'Ocurrio un error al procesar la merma', 'error');
            cerrarModal();
        }
    }

    document.addEventListener('DOMContentLoaded', init);

    return {
        abrirModal,
        cerrarModal,
        guardar
    };

})();

// exponer funciones globales para compatibilidad con onclick desde la tabla
function abrirModalMermaRegistro(lm_id, medicamento, cantidad_disponible) {
    MermaRegistroManager.abrirModal(lm_id, medicamento, cantidad_disponible);
}

function cerrarModalMermaRegistro() {
    MermaRegistroManager.cerrarModal();
}

function guardarMermaRegistro() {
    MermaRegistroManager.guardar();
}
