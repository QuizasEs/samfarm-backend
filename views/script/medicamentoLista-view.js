const MedicamentosModals = (function() {
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

    const API_URL = getBaseURL() + 'ajax/medicamentoAjax.php';

    function abrirModalNuevo() {
        const modal = document.getElementById('modalNuevoMedicamento');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
            document.getElementById('formNuevoMedicamento').reset();
        }
    }

    function cerrarModalNuevo() {
        const modal = document.getElementById('modalNuevoMedicamento');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    }

    async function abrirModalEditar(med_id) {
        const modal = document.getElementById('modalEditarMedicamento');
        if (!modal) return;

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    MedicamentoAjax: 'datos_medicamento',
                    med_id: med_id
                })
            });

            const data = await response.json();

            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            // Fill form
            document.querySelector('#formEditarMedicamento input[name="med_id"]').value = data.med_id;
            document.querySelector('#formEditarMedicamento input[name="Nombre_up"]').value = data.med_nombre_quimico || '';
            document.querySelector('#formEditarMedicamento input[name="Principio_up"]').value = data.med_principio_activo || '';
            document.querySelector('#formEditarMedicamento input[name="Accion_up"]').value = data.med_accion_farmacologica || '';
            document.querySelector('#formEditarMedicamento input[name="Descripcion_up"]').value = data.med_descripcion || '';
            document.querySelector('#formEditarMedicamento input[name="Presentacion_up"]').value = data.med_presentacion || '';
            document.querySelector('#formEditarMedicamento input[name="CodigoBarras_up"]').value = data.med_codigo_barras || '';
            document.querySelector('#formEditarMedicamento select[name="Uso_up"]').value = data.uf_id || '';
            document.querySelector('#formEditarMedicamento select[name="Forma_up"]').value = data.ff_id || '';
            document.querySelector('#formEditarMedicamento select[name="Via_up"]').value = data.vd_id || '';
            document.querySelector('#formEditarMedicamento select[name="Proveedor_up"]').value = data.pr_id || '';

            modal.style.display = 'flex';
            modal.classList.add('open');
        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar los datos', 'error');
        }
    }

    function cerrarModalEditar() {
        const modal = document.getElementById('modalEditarMedicamento');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    }

    async function guardarNuevo() {
        const formData = new FormData(document.getElementById('formNuevoMedicamento'));
        formData.append('MedicamentoAjax', 'save');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.Alerta === 'recargar') {
                cerrarModalNuevo();
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                });
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo guardar', 'error');
        }
    }

    async function guardarEditar() {
        const formData = new FormData(document.getElementById('formEditarMedicamento'));
        formData.append('MedicamentoAjax', 'update');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.Alerta === 'recargar') {
                cerrarModalEditar();
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                });
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo actualizar', 'error');
        }
    }

    // Función global para cerrar modales (compatibilidad)
    window.closeM = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    };

    return {
        abrirModalNuevo,
        cerrarModalNuevo,
        abrirModalEditar,
        cerrarModalEditar,
        guardarNuevo,
        guardarEditar
    };
})();
