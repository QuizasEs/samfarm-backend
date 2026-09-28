// categoriaLista-view.js - Script consolidado para la vista de categorías (Uso Farmacológico, Vía de Administración, Forma Farmacéutica)

function getBaseURL() {
    const serverUrl = document.documentElement.dataset.serverUrl;
    if (serverUrl) {
        return serverUrl.replace('ajax/notificacionesAjax.php', '');
    }
    const path = window.location.pathname;
    const match = path.match(/^\/([^\/]+)\//);
    return match ? "/" + match[1] + "/" : "/";
}

const BASE_URL = getBaseURL();
const AJAX_URL = BASE_URL + 'ajax/categoriaAjax.php';

// ==================== USO FARMACOLÓGICO ====================

function abrirModalAgregarUsoFarmacologico() {
    document.getElementById('nombre_uso').value = '';
    App.showM('modalAgregarUsoFarmacologico');
}

function cerrarModalAgregarUsoFarmacologico() {
    App.closeM('modalAgregarUsoFarmacologico');
}

async function abrirModalEditarUsoFarmacologico(id) {
    try {
        const formData = new FormData();
        formData.append('categoriaAjax', 'obtener_uso');
        formData.append('id', id);

        const response = await fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.error) {
            Swal.fire('Error', data.error, 'error');
            return;
        }

        document.getElementById('id_uso_edit').value = data.uf_id;
        document.getElementById('nombre_uso_edit').value = data.uf_nombre;

        App.showM('modalEditarUsoFarmacologico');

    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cargar los datos', 'error');
    }
}

function cerrarModalEditarUsoFarmacologico() {
    App.closeM('modalEditarUsoFarmacologico');
}

function cambiarEstadoUsoFarmacologico(id, nuevoEstado) {
    Swal.fire({
        title: nuevoEstado == 1 ? '¿Activar uso farmacológico?' : '¿Desactivar uso farmacológico?',
        text: 'Esta acción cambiará el estado del registro',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado == 1 ? '#28a745' : '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('categoriaAjax', 'cambiar_estado_uso');
                formData.append('id', id);
                formData.append('estado', nuevoEstado);

                const response = await fetch(AJAX_URL, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                await Swal.fire({
                    title: data.Titulo,
                    text: data.texto,
                    icon: data.Tipo
                });

                if (data.Alerta === 'recargar') {
                    document.querySelector('[data-ajax-action="listar_uso"]').querySelector('.btn-search')?.click();
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
            }
        }
    });
}

// ==================== VÍA DE ADMINISTRACIÓN ====================

function abrirModalAgregarViaAdministracion() {
    document.getElementById('nombre_via').value = '';
    App.showM('modalAgregarViaAdministracion');
}

function cerrarModalAgregarViaAdministracion() {
    App.closeM('modalAgregarViaAdministracion');
}

async function abrirModalEditarViaAdministracion(id) {
    try {
        const formData = new FormData();
        formData.append('categoriaAjax', 'obtener_via');
        formData.append('id', id);

        const response = await fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.error) {
            Swal.fire('Error', data.error, 'error');
            return;
        }

        document.getElementById('id_via_edit').value = data.vd_id;
        document.getElementById('nombre_via_edit').value = data.vd_nombre;

        App.showM('modalEditarViaAdministracion');

    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cargar los datos', 'error');
    }
}

function cerrarModalEditarViaAdministracion() {
    App.closeM('modalEditarViaAdministracion');
}

function cambiarEstadoViaAdministracion(id, nuevoEstado) {
    Swal.fire({
        title: nuevoEstado == 1 ? '¿Activar vía de administración?' : '¿Desactivar vía de administración?',
        text: 'Esta acción cambiará el estado del registro',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado == 1 ? '#28a745' : '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('categoriaAjax', 'cambiar_estado_via');
                formData.append('id', id);
                formData.append('estado', nuevoEstado);

                const response = await fetch(AJAX_URL, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                await Swal.fire({
                    title: data.Titulo,
                    text: data.texto,
                    icon: data.Tipo
                });

                if (data.Alerta === 'recargar') {
                    document.querySelector('[data-ajax-action="listar_via"]').querySelector('.btn-search')?.click();
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
            }
        }
    });
}

// ==================== FORMA FARMACÉUTICA ====================

function abrirModalAgregarFormaFarmaceutica() {
    document.getElementById('nombre_forma').value = '';
    App.showM('modalAgregarFormaFarmaceutica');
}

function cerrarModalAgregarFormaFarmaceutica() {
    App.closeM('modalAgregarFormaFarmaceutica');
}

async function abrirModalEditarFormaFarmaceutica(id) {
    try {
        const formData = new FormData();
        formData.append('categoriaAjax', 'obtener_forma');
        formData.append('id', id);

        const response = await fetch(AJAX_URL, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.error) {
            Swal.fire('Error', data.error, 'error');
            return;
        }

        document.getElementById('id_forma_edit').value = data.ff_id;
        document.getElementById('nombre_forma_edit').value = data.ff_nombre;

        App.showM('modalEditarFormaFarmaceutica');

    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudo cargar los datos', 'error');
    }
}

function cerrarModalEditarFormaFarmaceutica() {
    App.closeM('modalEditarFormaFarmaceutica');
}

function cambiarEstadoFormaFarmaceutica(id, nuevoEstado) {
    Swal.fire({
        title: nuevoEstado == 1 ? '¿Activar forma farmacéutica?' : '¿Desactivar forma farmacéutica?',
        text: 'Esta acción cambiará el estado del registro',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: nuevoEstado == 1 ? '#28a745' : '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('categoriaAjax', 'cambiar_estado_forma');
                formData.append('id', id);
                formData.append('estado', nuevoEstado);

                const response = await fetch(AJAX_URL, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                await Swal.fire({
                    title: data.Titulo,
                    text: data.texto,
                    icon: data.Tipo
                });

                if (data.Alerta === 'recargar') {
                    document.querySelector('[data-ajax-action="listar_forma"]').querySelector('.btn-search')?.click();
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
            }
        }
    });
}

