let notificacionesTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    const notificacionBtn = document.getElementById('notificacionBtn');
    const notificacionModal = document.getElementById('notificacionModal');
    const notificacionModalClose = document.getElementById('notificacionModalClose');

    if (notificacionBtn) {
        notificacionBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificacionModal.classList.toggle('active');
            if (notificacionModal.classList.contains('active')) {
                cargarNotificaciones();
            }
        });
    }

    if (notificacionModalClose) {
        notificacionModalClose.addEventListener('click', function() {
            notificacionModal.classList.remove('active');
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.notificacion-container')) {
            notificacionModal.classList.remove('active');
        }
    });

    cargarNotificaciones();
    setInterval(cargarNotificaciones, 30000);
});

function cargarNotificaciones() {
    const formData = new FormData();
    formData.append('accion', 'obtener');

    const url = document.documentElement.getAttribute('data-server-url');

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.error) {
            mostrarNotificaciones(data.notificaciones);
            actualizarBadge(data.notificaciones);
        }
    })
    .catch(error => {
        console.error('Error cargando notificaciones:', error);
    });
}

function mostrarNotificaciones(notificaciones) {
    const lista = document.getElementById('notificacionList');

    if (!notificaciones || notificaciones.length === 0) {
        lista.innerHTML = `
            <div class="notificacion-empty">
                <ion-icon name="checkmark-done-circle-outline"></ion-icon>
                <p>No hay notificaciones</p>
            </div>
        `;
        return;
    }

    let html = '';

    notificaciones.forEach(notif => {
        const claseNoLeida = notif.leida ? '' : 'no-leida';
        const colorStyle = notif.color ? `color: ${notif.color};` : '';

        html += `
            <div class="notificacion-item ${claseNoLeida}" data-id="${notif.id}" data-tipo="${notif.tipo}">
                <div class="notificacion-icon" style="${colorStyle}">
                    <ion-icon name="${notif.icono}"></ion-icon>
                </div>
                <div class="notificacion-content">
                    <div class="notificacion-titulo">${notif.titulo}</div>
                    <div class="notificacion-mensaje">${notif.mensaje}</div>
                    <div class="notificacion-fecha">${formatearFecha(notif.fecha)}</div>
                </div>
                <button class="notificacion-discard" data-id="${notif.id}">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
        `;
    });

    lista.innerHTML = html;

    document.querySelectorAll('.notificacion-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // If clicked on discard button, don't navigate
            if (e.target.closest('.notificacion-discard')) {
                return;
            }
            const id = this.dataset.id;
            const tipo = this.dataset.tipo;
            if (this.classList.contains('no-leida')) {
                marcarComoLeida(id);
            }
            // Navigate based on type
            const ajaxUrl = document.documentElement.getAttribute('data-server-url');
            const baseUrl = ajaxUrl.replace('ajax/notificacionesAjax.php', '');
            let view = '';
            if (tipo === 'stock_bajo' || tipo === 'sin_stock' || tipo === 'bajo_minimo') {
                view = 'inventarioLista/';
            } else if (tipo === 'proximo_caducar' || tipo === 'ya_caducado') {
                view = 'loteLista/';
            } else if (tipo === 'transferencia_pendiente') {
                view = 'recepcionarLista/';
            }
            if (view) {
                window.location.href = baseUrl + view;
            }
        });
    });

    document.querySelectorAll('.notificacion-discard').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            descartarNotificacion(id);
        });
    });
}

function actualizarBadge(notificaciones) {
    const badge = document.getElementById('notificacionBadge');

    if (notificaciones.length > 0) {
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

function marcarComoLeida(id) {
    const formData = new FormData();
    formData.append('accion', 'marcar_leida');
    formData.append('id', id);

    const url = document.documentElement.getAttribute('data-server-url');

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.error) {
            const item = document.querySelector(`[data-id="${id}"]`);
            if (item) {
                item.classList.remove('no-leida');
            }
            cargarNotificaciones();
        }
    })
    .catch(error => {
        console.error('Error marcando notificación como leída:', error);
    });
}

function descartarNotificacion(id) {
    const formData = new FormData();
    formData.append('accion', 'descartar');
    formData.append('id', id);

    const url = document.documentElement.getAttribute('data-server-url');

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.error) {
            // Remove the item from the list
            const item = document.querySelector(`.notificacion-item[data-id="${id}"]`);
            if (item) {
                item.remove();
            }
            // Update badge
            actualizarBadge({ length: document.querySelectorAll('.notificacion-item').length });
        } else {
            console.error('Error descartando notificación:', data.mensaje);
        }
    })
    .catch(error => {
        console.error('Error descartando notificación:', error);
    });
}

function formatearFecha(fecha) {
    const fecha_obj = new Date(fecha);
    const ahora = new Date();
    const diff = Math.floor((ahora - fecha_obj) / 1000);

    if (diff < 60) {
        return 'Hace unos segundos';
    } else if (diff < 3600) {
        const minutos = Math.floor(diff / 60);
        return 'Hace ' + minutos + ' minuto' + (minutos > 1 ? 's' : '');
    } else if (diff < 86400) {
        const horas = Math.floor(diff / 3600);
        return 'Hace ' + horas + ' hora' + (horas > 1 ? 's' : '');
    } else {
        const dias = Math.floor(diff / 86400);
        return 'Hace ' + dias + ' día' + (dias > 1 ? 's' : '');
    }
}
