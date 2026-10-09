const VentasHistorialModals = (function() {
    'use strict';

    /* Swal viene de un CDN (views/inc/head.php). En una red sin salida a
       internet el script no carga, `Swal` no existe y la PRIMERA llamada a
       Swal.fire() revienta la accion con "Swal is not defined": la
       reimpresion del comprobante llegaba hasta ahi y se caia, sin abrir nada.

       Este envoltorio devuelve el Swal real cuando esta, y si no, algo que
       cumple lo mismo de forma basica. Se declara aqui, con este nombre, a
       proposito: todas las llamadas de este archivo pasan a usar esta
       variable y el resto del archivo no cambia.

       Lo que NO hace el sustituto es fingir que es SweetAlert: sin el, un error
       se muestra como notice propio y el flujo sigue. */
    const Swal = window.Swal || (function() {
        let overlay = null;

        const quitar = () => {
            if (overlay) {
                overlay.remove();
                overlay = null;
            }
        };

        const poner = (html, conBoton) => {
            quitar();
            overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;display:flex;'
                + 'align-items:center;justify-content:center;background:rgba(0,0,0,.45)';
            overlay.innerHTML = '<div style="background:#fff;border-radius:8px;padding:22px 26px;'
                + 'max-width:420px;font-family:inherit;box-shadow:0 10px 40px rgba(0,0,0,.3)">' + html + '</div>';
            overlay.addEventListener('click', (ev) => {
                if (ev.target === overlay && conBoton) {
                    quitar();
                }
            });
            document.body.appendChild(overlay);
        };

        return {
            /* Acepta las dos formas que usa este archivo:
               fire(titulo, texto, icono) y fire({title, text, icon, didOpen}). */
            fire(a, b) {
                const esObjeto = a !== null && typeof a === 'object';

                /* Con didOpen es la llamada de "esperando": no se muestra nada
                   todavia, porque el mensaje de exito o de error viene despues
                   y si se mostrara este ahora se quedaria pegado en pantalla. */
                if (esObjeto && typeof a.didOpen === 'function') {
                    return Promise.resolve();
                }

                const titulo = esObjeto ? (a.title || '') : (a || '');
                const texto = esObjeto ? (a.text || '') : (b || '');
                const icono = esObjeto ? (a.icon || '') : '';

                const color = icono === 'success' ? '#1a7f37'
                    : icono === 'warning' ? '#b26a00'
                        : icono === 'error' ? '#c62828' : '#333';

                poner(
                    '<div style="font-size:15px;font-weight:600;color:' + color + '">'
                    + String(titulo).replace(/</g, '&lt;') + '</div>'
                    + (texto ? '<div style="font-size:13px;color:#555;margin-top:8px">'
                        + String(texto).replace(/</g, '&lt;') + '</div>' : '')
                    + '<div style="font-size:11px;color:#999;margin-top:14px;text-align:center">'
                    + 'Clic en cualquier lado para cerrar</div>',
                    true
                );

                return Promise.resolve({ isConfirmed: true });
            },
            close: quitar,
            showLoading() { }
        };
    })();

    function getBaseURL() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    const API_URL = getBaseURL() + 'ajax/ventasHistorialAjax.php';

    // ==================== UTILIDADES ====================
    const utils = {
        async ajax(params) {
            try {
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

                return await response.json();
            } catch (error) {
                throw error;
            }
        },

        abrir(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('open');
            }
        },

        cerrar(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
        },

        formatearMoneda(num) {
            return 'Bs. ' + parseFloat(num || 0).toFixed(2);
        },

        formatearNumero(num) {
            return parseInt(num || 0).toLocaleString('es-BO');
        }
    };

    // ==================== MODAL DETALLE ====================
    const detalle = {
        async abrir(veId, numeroDocumento) {
            document.getElementById('modalDetalleVeId').value = veId;
            utils.abrir('modalDetalleVenta');

            // Mostrar loading
            document.getElementById('tablaItemsVenta').innerHTML =
                '<tr><td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

            try {
                const data = await utils.ajax({
                    ventasHistorialAjax: 'detalle',
                    ve_id: veId
                });

                if (data.Alerta) {
                    Swal.fire({
                        title: data.Titulo,
                        text: data.texto,
                        icon: data.Tipo
                    });
                    utils.cerrar('modalDetalleVenta');
                    return;
                }

                // Llenar información general
                document.getElementById('detalleNumeroDocumento').textContent = data.venta.ve_numero_documento;
                document.getElementById('detalleFecha').textContent = data.venta.ve_fecha_emision;
                document.getElementById('detalleCliente').textContent = data.venta.cliente_nombre;
                document.getElementById('detalleCarnet').textContent = data.venta.cliente_carnet;
                document.getElementById('detalleVendedor').textContent = data.venta.vendedor_nombre;
                document.getElementById('detalleSucursal').textContent = data.venta.sucursal_nombre;
                document.getElementById('detalleCaja').textContent = data.venta.caja_nombre;

                // Distinguir factura electronica de nota de venta.
                //
                // El backend manda venta.siat SOLO cuando la venta tiene CUF, que
                // es la senal de que SI paso por facturacion electronica. Si no
                // viene, es una nota de venta comun.
                const siat = data.venta.siat;
                const elTipoDoc = document.getElementById('detalleTipoDocumento');
                const elNumFactura = document.getElementById('detalleNumeroFactura');

                if (siat && siat.es_factura_electronica) {
                    elTipoDoc.innerHTML = '<span class="badge bdef bsm">' +
                        '<span class="bdt"></span>FACTURA ELECTRONICA</span>';

                    elNumFactura.textContent = siat.numero_factura || data.venta.fa_numero_control || '-';

                    // Se muestra el bloque de facturacion electronica.
                    document.getElementById('detalleSiatTitulo').style.display = '';
                    document.getElementById('detalleSiatCard').style.display = '';

                    const elEstado = document.getElementById('detalleSiatEstado');
                    elEstado.className = 'badge bsm ' + (siat.estado_clase || 'bwar');
                    elEstado.innerHTML = '<span class="bdt"></span>' + (siat.estado_etiqueta || 'PENDIENTE');

                    document.getElementById('detalleSiatNumeroFactura').textContent =
                        siat.numero_factura || 'Pendiente de emisión';

                    /* QR. El servidor lo arma con urlQR() porque es determinista
                       (nit + cuf + numero), asi que una factura vieja se reimprime
                       igual que una de ayer. render() devuelve el PNG en base64
                       pelado, aqui se le pone el prefijo data: URI.

                       Aca solo se previsualiza. La reimpresion esta en el boton
                       Imprimir de la tabla. */
                    if (siat.qr_img) {
                        document.getElementById('detalleSiatQrImg').src =
                            'data:image/png;base64,' + siat.qr_img;
                        document.getElementById('detalleSiatQrLink').href = siat.qr_url || '#';
                        document.getElementById('detalleSiatQrBox').style.display = 'flex';
                    } else {
                        document.getElementById('detalleSiatQrBox').style.display = 'none';
                    }

                    /* El bloque de anulacion se decide aca y no con un if/elseif
                       propio: siat.anulacion viene ya resuelto desde
                       estadoAnulacionFactura(), con el motivo de bloqueo en
                       texto. Reimplementar esas reglas en el JS las dejaria
                       desincronizadas con las del servidor en el primer estado
                       nuevo que se agregue. */
                    anulacion.pintarBloqueo(siat);

                } else {
                    elTipoDoc.innerHTML = '<span class="badge bgry bsm">' +
                        '<span class="bdt"></span>' +
                        String(data.venta.ve_tipo_documento || 'nota de venta').toUpperCase() + '</span>';

                    elNumFactura.textContent = data.venta.fa_numero_control || '-';

                    // Se oculta el bloque de facturacion electronica.
                    document.getElementById('detalleSiatTitulo').style.display = 'none';
                    document.getElementById('detalleSiatCard').style.display = 'none';

                    // Una nota de venta no tiene CUF y no se anula en el SIN.
                    anulacion.pintarBloqueo(null);
                }

                // Llenar totales
                document.getElementById('detalleSubtotal').textContent = utils.formatearMoneda(data.venta.ve_subtotal);
                document.getElementById('detalleImpuesto').textContent = utils.formatearMoneda(data.venta.ve_impuesto);
                document.getElementById('detalleTotal').textContent = utils.formatearMoneda(data.venta.ve_total);

                // Llenar tabla de items
                const tbody = document.getElementById('tablaItemsVenta');
                if (data.items && data.items.length > 0) {
                    tbody.innerHTML = data.items.map(item => `
                        <tr>
                            <td>
                                <strong>${item.nombre}</strong>
                                ${item.presentacion ? '<br><small style="color:#666;">' + item.presentacion + '</small>' : ''}
                            </td>
                            <td>${item.lote}</td>
                            <td style="text-align:center;">${utils.formatearNumero(item.cantidad)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(item.precio_unitario)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(item.descuento)}</td>
                            <td style="text-align:right;"><strong>${utils.formatearMoneda(item.subtotal)}</strong></td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin items</td></tr>';
                }

            } catch (error) {
                Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                utils.cerrar('modalDetalleVenta');
            }
        }
    };

    // ==================== REIMPRIMIR COMPROBANTE ====================
    /* La unica reimpresion del sistema. La dispara el boton "Imprimir" de cada
       fila de la tabla.

       El backend decide que comprobante sale: si la venta tiene CUF reimprime
       la factura electronica con su QR y su leyenda, y si no, reimprime la nota
       de venta de siempre. Por eso el nombre del archivo no lo decide el
       navegador: "Nota_Venta_..." en una factura electronica seria mentiroso. */
    const reimprimir = {
        async comprobante(veId) {
            Swal.fire({
                title: 'Generando comprobante...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const data = await utils.ajax({
                    ventasHistorialAjax: 'generar_pdf',
                    ve_id: veId
                });

                Swal.close();

                if (data.Alerta) {
                    Swal.fire({
                        title: data.Titulo,
                        text: data.texto,
                        icon: data.Tipo
                    });
                    return;
                }

                if (data.success && data.pdf_base64) {
                    window.abrirPDFDesdeBase64(data.pdf_base64, `Comprobante_${veId}.pdf`);
                    Swal.fire({
                        icon: 'success',
                        title: 'Comprobante generado',
                        text: 'El comprobante se ha abierto en una nueva ventana',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('Error', 'No se pudo generar el comprobante', 'error');
                }

            } catch (error) {
                Swal.fire('Error', 'No se pudo generar el comprobante', 'error');
            }
        }
    };

    // ==================== VER FACTURA ====================
    const factura = {
        ver(faId) {
            Swal.fire({
                title: 'Funcionalidad en desarrollo',
                text: 'La visualización de facturas se implementará próximamente',
                icon: 'info'
            });
        }
    };

    // ==================== ANULAR FACTURA ANTE EL SIN ====================
    /* La anulacion es la unica accion de este historial que escribe en el SIN y
       no tiene vuelta atras, asi que va con tres barreras:

         1. el boton solo se muestra si el backend dice que se puede,
         2. hay que escribir ANULAR a mano en el modal,
         3. el backend vuelve a validar rol, estado y motivo antes de llamar.

       La 3 no es redundante con las otras dos: el boton y el modal se pueden
       tocar desde la consola del navegador. Lo que no se puede saltarse es el
       chequeo del servidor. */
    const anulacion = {
        /* Estado que devuelve el detalle de la venta. Se guarda para reusarlo al
           confirmar: el modal se abre desde el detalle, no necesita otra ida al
           servidor solo para pintar un select. */
        contexto: null,

        pintarBloqueo(siat) {
            const box = document.getElementById('detalleSiatAnularBox');
            const aviso = document.getElementById('detalleSiatAnularAviso');
            const btn = document.getElementById('btnAnularFactura');

            if (!siat || !siat.anulacion) {
                box.style.display = 'none';
                return;
            }

            // El select de motivos solo tiene sentido si hay algo que anular.
            // Se cubre el caso en que falte motivos para que este bloque no
            // reviente el modal de detalle completo: aca no hay try/catch
            // propio y una excepcion cerraria la pantalla con "no se pudo
            // cargar el detalle", que seria mentira.
            if (!Array.isArray(siat.anulacion.motivos) || siat.anulacion.motivos.length === 0) {
                box.style.display = 'none';
                return;
            }

            box.style.display = '';

            if (siat.anulacion.puede_anular) {
                aviso.style.display = 'none';
                btn.style.display = '';
                btn.onclick = () => anulacion.abrir(siat);
                return;
            }

            // Bloqueada. El motivo lo dice el modelo, no esta pantalla.
            btn.style.display = 'none';
            aviso.style.display = '';
            aviso.className = 'th5 mb12';
            aviso.innerHTML = '<span class="badge bwar bsm"><span class="bdt"></span>' +
                'NO SE PUEDE ANULAR</span> ' +
                anulacion.escape(siat.anulacion.motivo_bloqueo || '');
        },

        abrir(siat) {
            anulacion.contexto = siat;

            const selMotivo = document.getElementById('anularSiatMotivo');
            selMotivo.innerHTML = siat.anulacion.motivos
                .map(m => '<option value="' + m.codigo + '">' +
                    anulacion.escape(m.codigo + '. ' + m.descripcion) + '</option>')
                .join('');

            document.getElementById('anularSiatNumeroFactura').textContent =
                'Factura N° ' + (siat.numero_factura || '(sin numero)');
            document.getElementById('anularSiatConfirmacion').value = '';

            utils.abrir('modalAnularFactura');
        },

        async confirmar() {
            const siat = anulacion.contexto;
            if (!siat || !siat.fa_id) {
                Swal.fire('Error', 'No hay una factura seleccionada', 'error');
                return;
            }

            const escrito = document.getElementById('anularSiatConfirmacion')
                .value.trim().toUpperCase();
            if (escrito !== 'ANULAR') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Falta la confirmación',
                    text: 'Escriba ANULAR en el casillero para confirmar.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            const motivo = document.getElementById('anularSiatMotivo').value;

            Swal.fire({
                title: 'Anulando ante el SIN...',
                text: 'No cierre esta ventana',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const data = await utils.ajax({
                    ventasHistorialAjax: 'anular_factura',
                    fa_id: siat.fa_id,
                    codigo_motivo: motivo
                });

                Swal.close();

                if (data.Alerta && data.Tipo !== 'success') {
                    Swal.fire({
                        icon: 'error',
                        title: data.Titulo,
                        text: data.texto
                    });
                    return;
                }

                utils.cerrar('modalAnularFactura');
                utils.cerrar('modalDetalleVenta');

                Swal.fire({
                    icon: 'success',
                    title: 'Factura anulada',
                    text: data.texto || 'El SIN confirmo la anulacion.'
                }).then(() => {
                    // El detalle se vuelve a pedir: el estado, el badge y el boton
                    // de anular ya no son los mismos que antes de la llamada.
                    detalle.abrir(
                        document.getElementById('modalDetalleVeId').value,
                        null
                    );
                });

            } catch (error) {
                Swal.fire('Error', 'No se pudo comunicar con el servidor', 'error');
            }
        },

        /* Los motivos vienen de siat_parametricas, o sea de texto escrito por
           personas. Se escapan antes de innerHTML porque este archivo no usa
           textContent para armar el select. */
        escape(texto) {
            return String(texto === null || texto === undefined ? '' : texto)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    };

    // ==================== API PÚBLICA ====================
    return {
        cerrar: utils.cerrar,
        verDetalle: detalle.abrir,
        reimprimirComprobante: reimprimir.comprobante,
        verFactura: factura.ver,
        anularFactura: anulacion.confirmar
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    const btnConfirmar = document.getElementById('btnConfirmarAnularFactura');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            anulacion.confirmar();
        });
    }
});

// ==================== EXPORTAR EXCEL ====================
document.addEventListener('DOMContentLoaded', function() {
    const btnExcel = document.getElementById('btnExportarExcel');

    if (btnExcel) {
        btnExcel.addEventListener('click', function() {
            const form = document.querySelector('.filtro-dinamico');

            let url = getBaseURL() + 'ajax/ventasHistorialAjax.php?ventasHistorialAjax=exportar_excel';

            const fechaDesde = form.querySelector('input[name="fecha_desde"]');
            if (fechaDesde && fechaDesde.value) {
                url += '&fecha_desde=' + encodeURIComponent(fechaDesde.value);
            }

            const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
            if (fechaHasta && fechaHasta.value) {
                url += '&fecha_hasta=' + encodeURIComponent(fechaHasta.value);
            }

            const select1 = form.querySelector('select[name="select1"]');
            if (select1 && select1.value) {
                url += '&su_id=' + encodeURIComponent(select1.value);
            }

            const select2 = form.querySelector('select[name="select2"]');
            if (select2 && select2.value) {
                url += '&cliente=' + encodeURIComponent(select2.value);
            }

            const select3 = form.querySelector('select[name="select3"]');
            if (select3 && select3.value) {
                url += '&vendedor=' + encodeURIComponent(select3.value);
            }

            const select4 = form.querySelector('select[name="select4"]');
            if (select4 && select4.value) {
                url += '&tipo_documento=' + encodeURIComponent(select4.value);
            }

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Descargando',
                text: 'El archivo se está descargando...',
                timer: 2000,
                showConfirmButton: false
            });
        });
    }
});

function getBaseURL() {
    const serverUrl = document.documentElement.dataset.serverUrl;
    if (serverUrl) {
        return serverUrl.replace('ajax/notificacionesAjax.php', '');
    }
    const path = window.location.pathname;
    const match = path.match(/^\/([^\/]+)\//);
    return match ? "/" + match[1] + "/" : "/";
}
