let mermaIdActual = null;
let cantidadMaxActual = 0;

function getBaseURL() {
    const serverUrl = document.documentElement.dataset.serverUrl;
    if (serverUrl) {
        return serverUrl.replace('ajax/notificacionesAjax.php', '');
    }
    const path = window.location.pathname;
    const match = path.match(/^\/([^\/]+)\//);
    return match ? "/" + match[1] + "/" : "/";
}

function abrirFormularioMerma(lm_id, med_id, medicamento, cantidad_max) {
    mermaIdActual = lm_id;
    cantidadMaxActual = cantidad_max;
    
    document.getElementById('lm_id').value = lm_id;
    document.getElementById('med_id').value = med_id;
    document.getElementById('medicamentoNombre').value = medicamento;
    document.getElementById('me_cantidad').value = '';
    document.getElementById('me_motivo').value = '';
    document.getElementById('cantidadMax').textContent = 'Máximo: ' + cantidad_max + ' unidades';
    
    document.getElementById('modalMerma').style.display = 'flex';
}

function cerrarModalMerma() {
    document.getElementById('modalMerma').style.display = 'none';
    mermaIdActual = null;
}

function guardarMerma() {
    const lm_id = document.getElementById('lm_id').value;
    const me_cantidad = document.getElementById('me_cantidad').value;
    const me_motivo = document.getElementById('me_motivo').value;

    if (!lm_id || !me_cantidad || !me_motivo) {
        Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
        return;
    }

    if (parseInt(me_cantidad) > cantidadMaxActual) {
        Swal.fire('Error', 'La cantidad no puede exceder ' + cantidadMaxActual + ' unidades', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('lm_id', lm_id);
    formData.append('me_cantidad', me_cantidad);
    formData.append('me_motivo', me_motivo);
    formData.append('mermaControllerAjax', 'crear');

    fetch(getBaseURL() + 'ajax/mermaAjax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.Alerta === 'redireccionar') {
            Swal.fire({
                icon: 'success',
                title: 'Merma Registrada',
                text: 'La merma ha sido registrada correctamente',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = data.URL;
            });
        } else if (data.Alerta === 'simple') {
            Swal.fire(data.Titulo, data.Texto, data.Tipo);
        }
        cerrarModalMerma();
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Ocurrió un error al procesar la merma', 'error');
        cerrarModalMerma();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalMerma');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                cerrarModalMerma();
            }
        });
    }
});
