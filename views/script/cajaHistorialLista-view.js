const CajaHistorial = (() => {
    'use strict';

    function getBaseURL() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        // fallback
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    const API_URL = getBaseURL() + 'ajax/cajaHistorialAjax.php';

    return {
        verReferencia: function(tipo, id) {
            if (!tipo || !id) {
                Swal.fire('Error', 'Referencia inválida', 'error');
                return;
            }

            console.log('verReferencia llamado - tipo:', tipo, 'id:', id);

            const modal = document.getElementById('modalReferenciaCajaHistorial');
            const contenido = document.getElementById('contenidoReferenciaCajaHistorial');

            modal.style.display = 'flex';
            modal.classList.add('open');

            fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'cajaHistorialAjax': 'obtener_referencia',
                    'tipo': tipo,
                    'id': id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    contenido.innerHTML = data.html;
                } else if (data.error) {
                    contenido.innerHTML = '<p style="color: red; text-align: center;">' + data.error + '</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                contenido.innerHTML = '<p style="color: red; text-align: center;">Error al cargar la referencia</p>';
            });
        },

        cerrarModalReferencia: function() {
            const modal = document.getElementById('modalReferenciaCajaHistorial');
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        },

        cerrarModalReferenciaCaja: function() {
            const modal = document.getElementById('modalReferenciaCajaHistorial');
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        },

        exportarMovimiento: async function(mc_id) {
            if (!mc_id) {
                Swal.fire('Error', 'ID de movimiento inválido', 'error');
                return;
            }

            Swal.fire({
                title: 'Generando PDF...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const data = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        cajaHistorialAjax: 'exportar_movimiento_pdf',
                        mc_id: mc_id
                    })
                }).then(response => response.json());

                Swal.close();

                if (data.success && data.pdf_base64) {
                    window.abrirPDFDesdeBase64(data.pdf_base64, `Movimiento_${mc_id}.pdf`);
                    Swal.fire({
                        icon: 'success',
                        title: 'PDF generado',
                        text: 'El PDF se ha abierto en una nueva ventana',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo generar el PDF', 'error');
            }
        }
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    const btnExcel = document.getElementById('btnExportarExcelCajaHistorial');
    if (btnExcel) {
        btnExcel.addEventListener('click', function() {
            exportarExcelCajaHistorial();
        });
    }

    const btnPDF = document.getElementById('btnExportarPDFCajaHistorial');
    if (btnPDF) {
        btnPDF.addEventListener('click', function() {
            exportarPDFCajaHistorial();
        });
    }
});

function exportarExcelCajaHistorial() {
    const form = document.querySelector('.filtro-dinamico');
    const params = new URLSearchParams();
    params.append('cajaHistorialAjax', 'exportar_excel');

    if (form) {
        const fechaDesde = form.querySelector('input[name="fecha_desde"]');
        const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
        const select1 = form.querySelector('select[name="select1"]');
        const select2 = form.querySelector('select[name="select2"]');
        const select3 = form.querySelector('select[name="select3"]');

        if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
        if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
        if (select1 && select1.value) params.append('select1', select1.value);
        if (select2 && select2.value) params.append('select2', select2.value);
        if (select3 && select3.value) params.append('su_id', select3.value);
    }

    const base = (function() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    })();

    const url = base + 'ajax/cajaHistorialAjax.php?' + params.toString();

    window.open(url, '_blank');

    Swal.fire({
        icon: 'success',
        title: 'Descargando Excel',
        text: 'El archivo se está descargando...',
        timer: 2000,
        showConfirmButton: false
    });
}

function exportarPDFCajaHistorial() {
    const form = document.querySelector('.filtro-dinamico');
    const params = new URLSearchParams();
    params.append('cajaHistorialAjax', 'exportar_pdf');

    if (form) {
        const fechaDesde = form.querySelector('input[name="fecha_desde"]');
        const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
        const select1 = form.querySelector('select[name="select1"]');
        const select2 = form.querySelector('select[name="select2"]');
        const select3 = form.querySelector('select[name="select3"]');

        if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
        if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
        if (select1 && select1.value) params.append('select1', select1.value);
        if (select2 && select2.value) params.append('select2', select2.value);
        if (select3 && select3.value) params.append('select3', select3.value);
    }

    const base = (function() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    })();

    const url = base + 'ajax/cajaHistorialAjax.php?' + params.toString();
    window.open(url, '_blank');

    Swal.fire({
        icon: 'success',
        title: 'Generando PDF',
        text: 'El reporte se está generando...',
        timer: 2000,
        showConfirmButton: false
    });
}
