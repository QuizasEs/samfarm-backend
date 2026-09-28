const TransferirHistorialModals = (function() {
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

    console.log(' Inicializando módulo TransferirHistorialModals');

    const API_URL = getBaseURL() + 'ajax/transferirHistorialAjax.php';

    const utils = {
        async ajax(params) {
            try {
                console.log(' Enviando petición:', params);

                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(params)
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();
                console.log(' Respuesta recibida:', data);
                return data;

            } catch (error) {
                console.error('  Error AJAX:', error);
                throw error;
            }
        },

        abrir(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('open');
                console.log(` Modal abierto: ${modalId}`);
            }
        },

        cerrar(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => modal.style.display = 'none', 300);
                console.log(` Modal cerrado: ${modalId}`);
            }
        },

        formatearMoneda(num) {
            return 'Bs. ' + parseFloat(num || 0).toFixed(2);
        },

        formatearNumero(num) {
            return parseInt(num || 0).toLocaleString('es-BO');
        }
    };

    const detalle = {
        async abrir(trId, numeroTransferencia) {
            console.log(' Abriendo detalle de transferencia:', {
                trId,
                numeroTransferencia
            });

            document.getElementById('modalDetalleTrId').value = trId;
            utils.abrir('modalDetalleTransferencia');

            document.getElementById('tablaItemsTransferencia').innerHTML =
                '<tr><td colspan="5" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

            try {
                const data = await utils.ajax({
                    transferirHistorialAjax: 'detalle',
                    tr_id: trId
                });

                if (data.Alerta) {
                    Swal.fire({
                        title: data.Titulo,
                        text: data.texto,
                        icon: data.Tipo
                    });
                    utils.cerrar('modalDetalleTransferencia');
                    return;
                }

                const tr = data.transferencia;
                document.getElementById('detalleNumero').textContent = tr.tr_numero;
                document.getElementById('detalleFechaEnvio').textContent = tr.tr_fecha_envio;
                document.getElementById('detalleOrigen').textContent = tr.sucursal_origen;
                document.getElementById('detalleDestino').textContent = tr.sucursal_destino;
                document.getElementById('detalleUsuarioEmisor').textContent = tr.usuario_emisor;
                document.getElementById('detalleTotalCajas').textContent = tr.tr_total_cajas;
                document.getElementById('detalleTotalUnidades').textContent = utils.formatearNumero(tr.tr_total_unidades);
                document.getElementById('detalleTotal').textContent = utils.formatearMoneda(tr.tr_total_valorado);

                const tbody = document.getElementById('tablaItemsTransferencia');
                if (data.detalles && data.detalles.length > 0) {
                 tbody.innerHTML = data.detalles.map(item => `
                    <tr>
                        <td>
                            <div class="td-main"><strong>${item.med_nombre_quimico}</strong></div>
                            <div class="td-sub"><ion-icon name="pricetag-outline"></ion-icon> Lote: ${item.dt_numero_lote_origen}</div>
                        </td>
                        <td style="text-align:center;">${item.dt_cantidad_cajas}</td>
                        <td style="text-align:center;">${utils.formatearNumero(item.dt_cantidad_unidades)}</td>
                        <td style="text-align:right;">${utils.formatearMoneda(item.dt_precio_compra)}</td>
                        <td style="text-align:right;"><strong>${utils.formatearMoneda(item.dt_subtotal_valorado)}</strong></td>
                    </tr>
                `).join('');
                 } else {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="cart-outline"></ion-icon> Sin items</td></tr>';
                }

            } catch (error) {
                console.error('  Error:', error);
                Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                utils.cerrar('modalDetalleTransferencia');
            }
        }
    };

    const publicAPI = {
        cerrar: utils.cerrar,
        verDetalle: detalle.abrir
    };

    return publicAPI;
})();
