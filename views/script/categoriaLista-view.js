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
const DEFAULT_IMG = BASE_URL + 'views/assets/img/predeterminado.png';

// ==================== USO FARMACOLÓGICO ====================

function abrirModalAgregarUsoFarmacologico() {
    document.getElementById('nombre_uso').value = '';
    document.getElementById('imgLoad_uso').value = '';
    document.getElementById('img-pic-uso').src = DEFAULT_IMG;
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
        document.getElementById('imagen_antigua_edit').value = data.uf_imagen || '';

        const tieneImagen = data.uf_imagen && data.uf_imagen.trim().length > 10;
        const imgElement = document.getElementById('img-pic-uso-edit');

        if (tieneImagen) {
            imgElement.src = data.uf_imagen;
            imgElement.onerror = function() {
                this.src = DEFAULT_IMG;
                this.onerror = null;
            };
        } else {
            imgElement.src = DEFAULT_IMG;
        }

        document.getElementById('imgLoad_uso_edit').value = '';

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
    document.getElementById('imgLoad_via').value = '';
    document.getElementById('img-pic-via').src = DEFAULT_IMG;
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
        document.getElementById('imagen_antigua_edit_via').value = data.vd_imagen || '';

        const tieneImagen = data.vd_imagen && data.vd_imagen.trim().length > 10;
        const imgElement = document.getElementById('img-pic-via-edit');

        if (tieneImagen) {
            imgElement.src = data.vd_imagen;
            imgElement.onerror = function() {
                this.src = DEFAULT_IMG;
                this.onerror = null;
            };
        } else {
            imgElement.src = DEFAULT_IMG;
        }

        document.getElementById('imgLoad_via_edit').value = '';

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
    document.getElementById('imgLoad_forma').value = '';
    document.getElementById('img-pic-forma').src = DEFAULT_IMG;
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
        document.getElementById('imagen_antigua_forma_edit').value = data.ff_imagen || '';

        const tieneImagen = data.ff_imagen && data.ff_imagen.trim().length > 10;
        const imgElement = document.getElementById('img-pic-forma-edit');

        if (tieneImagen) {
            imgElement.src = data.ff_imagen;
            imgElement.onerror = function() {
                this.src = DEFAULT_IMG;
                this.onerror = null;
            };
        } else {
            imgElement.src = DEFAULT_IMG;
        }

        document.getElementById('imgLoad_forma_edit').value = '';

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

// ==================== DOMContentLoaded handlers (image previews) ====================

document.addEventListener('DOMContentLoaded', function() {
    // Uso Farmacológico
    const inputFile = document.getElementById('imgLoad_uso');
    const imgPic = document.getElementById('img-pic-uso');

    if (inputFile && imgPic) {
        inputFile.onchange = function() {
            if (inputFile.files && inputFile.files[0]) {
                if (inputFile.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFile.value = '';
                    imgPic.src = DEFAULT_IMG;
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPic.src = e.target.result;
                };
                reader.readAsDataURL(inputFile.files[0]);
            }
        };
    }

    const inputFileEdit = document.getElementById('imgLoad_uso_edit');
    const imgPicEdit = document.getElementById('img-pic-uso-edit');

    if (inputFileEdit && imgPicEdit) {
        inputFileEdit.onchange = function() {
            if (inputFileEdit.files && inputFileEdit.files[0]) {
                if (inputFileEdit.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFileEdit.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPicEdit.src = e.target.result;
                };
                reader.readAsDataURL(inputFileEdit.files[0]);
            }
        };
    }

    // Vía de Administración
    const inputFileVia = document.getElementById('imgLoad_via');
    const imgPicVia = document.getElementById('img-pic-via');

    if (inputFileVia && imgPicVia) {
        inputFileVia.onchange = function() {
            if (inputFileVia.files && inputFileVia.files[0]) {
                if (inputFileVia.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFileVia.value = '';
                    imgPicVia.src = DEFAULT_IMG;
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPicVia.src = e.target.result;
                };
                reader.readAsDataURL(inputFileVia.files[0]);
            }
        };
    }

    const inputFileViaEdit = document.getElementById('imgLoad_via_edit');
    const imgPicViaEdit = document.getElementById('img-pic-via-edit');

    if (inputFileViaEdit && imgPicViaEdit) {
        inputFileViaEdit.onchange = function() {
            if (inputFileViaEdit.files && inputFileViaEdit.files[0]) {
                if (inputFileViaEdit.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFileViaEdit.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPicViaEdit.src = e.target.result;
                };
                reader.readAsDataURL(inputFileViaEdit.files[0]);
            }
        };
    }

    // Forma Farmacéutica
    const inputFileForma = document.getElementById('imgLoad_forma');
    const imgPicForma = document.getElementById('img-pic-forma');

    if (inputFileForma && imgPicForma) {
        inputFileForma.onchange = function() {
            if (inputFileForma.files && inputFileForma.files[0]) {
                if (inputFileForma.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFileForma.value = '';
                    imgPicForma.src = DEFAULT_IMG;
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPicForma.src = e.target.result;
                };
                reader.readAsDataURL(inputFileForma.files[0]);
            }
        };
    }

    const inputFileFormaEdit = document.getElementById('imgLoad_forma_edit');
    const imgPicFormaEdit = document.getElementById('img-pic-forma-edit');

    if (inputFileFormaEdit && imgPicFormaEdit) {
        inputFileFormaEdit.onchange = function() {
            if (inputFileFormaEdit.files && inputFileFormaEdit.files[0]) {
                if (inputFileFormaEdit.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFileFormaEdit.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPicFormaEdit.src = e.target.result;
                };
                reader.readAsDataURL(inputFileFormaEdit.files[0]);
            }
        };
    }
});
