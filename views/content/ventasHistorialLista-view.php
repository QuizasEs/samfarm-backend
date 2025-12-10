<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

    /* Admin o Gerente */


?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/ventasHistorialAjax.php"
        data-ajax-param="ventasHistorialAjax"
        data-ajax-registros="10">

        <div class="title">
            <h2>
                <ion-icon name="receipt-outline"></ion-icon> Historial de Ventas
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <!-- Filtro por Rango de Fechas -->
                <div class="form-fechas">
                    <small>Desde</small>
                    <input type="date" name="fecha_desde" placeholder="Desde" title="Fecha desde">
                </div>
                <div class="form-fechas">
                    <small>Hasta</small>
                    <input type="date" name="fecha_hasta" placeholder="Hasta" title="Fecha hasta">
                </div>

                <!-- Select Sucursal (solo para admin) -->
                <div class="form-fechas">
                    <small>Sucursales</small>
                    <?php if ($_SESSION['rol_smp'] == 1) { ?>
                        <select class="select-filtro" name="select1">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>


                <!-- Select cajero -->
                <div class="form-fechas">
                    <small>Cajero</small>
                    <select class="select-filtro" name="select3">
                        <option value="">Todos los cajeros</option>
                        <?php foreach ($datos_select['caja'] as $caja) { ?>
                            <option value="<?php echo $caja['us_id'] ?>"><?php echo $caja['us_nombres'] ?></option>
                        <?php } ?>

                    </select>
                </div>

                <!-- Select Tipo Documento -->
                <div class="form-fechas">
                    <small>Tipo de documento</small>
                    <select class="select-filtro" name="select4">
                        <option value="">Todos los tipos</option>
                        <option value="nota de venta">Nota de Venta</option>
                        <option value="factura">Factura</option>
                    </select>
                </div>

                <!-- Búsqueda -->
                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por N° documento o cliente...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
            <?php if ($_SESSION['rol_smp'] == 1) { ?>
                <div class="filtro-acciones" style="display:flex; gap:10px; margin-top:10px;">
                    <button type="button" id="btnExportarExcel" class="btn success">
                        <ion-icon name="download-outline"></ion-icon> Excel
                    </button>
                </div>
            <?php } ?>
        </form>

        <div class="tabla-contenedor"></div>
    </div>

    <script>document.documentElement.setAttribute('data-server-url', '<?php echo SERVER_URL; ?>');</script>

    <!-- Modal Detalle de Venta -->
    <div id="modalDetalleVenta" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="receipt-outline"></ion-icon>
                    <span>Detalle de Venta</span>
                </div>
                <a class="close" onclick="VentasHistorialModals.cerrar('modalDetalleVenta')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <div class="modal-group">
                <h3>Información de la Venta</h3>

                <input type="hidden" id="modalDetalleVeId">

                <div class="row">
                    <div class="col-50">
                        <p><strong>N° Documento:</strong> <span id="detalleNumeroDocumento"></span></p>
                        <p><strong>Fecha:</strong> <span id="detalleFecha"></span></p>
                        <p><strong>Cliente:</strong> <span id="detalleCliente"></span></p>
                        <p><strong>CI/NIT:</strong> <span id="detalleCarnet"></span></p>
                    </div>
                    <div class="col-50">
                        <p><strong>Vendedor:</strong> <span id="detalleVendedor"></span></p>
                        <p><strong>Sucursal:</strong> <span id="detalleSucursal"></span></p>
                        <p><strong>Caja:</strong> <span id="detalleCaja"></span></p>
                        <p><strong>N° Factura:</strong> <span id="detalleNumeroFactura">-</span></p>
                    </div>
                </div>

                <h3 style="margin-top: 20px;">Medicamentos Vendidos</h3>

                <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Lote</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tablaItemsVenta">
                            <tr>
                                <td colspan="6" style="text-align:center;">
                                    <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-70"></div>
                    <div class="col-30">
                        <p><strong>Subtotal:</strong> <span id="detalleSubtotal">Bs. 0.00</span></p>
                        <p><strong>Impuestos:</strong> <span id="detalleImpuesto">Bs. 0.00</span></p>
                        <p style="font-size: 18px; color: #2e7d32;"><strong>TOTAL:</strong> <span id="detalleTotal">Bs. 0.00</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>


<!-- Script para Historial de Ventas -->
<script>
    const VentasHistorialModals = (function() {
        'use strict';

        const API_URL = '<?php echo SERVER_URL; ?>ajax/ventasHistorialAjax.php';

        // ==================== UTILIDADES ====================
        const utils = {
            async ajax(params) {
                try {
                    console.log('📡 Enviando petición:', params);

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
                    console.log('✅ Respuesta recibida:', data);
                    return data;

                } catch (error) {
                    console.error('❌ Error AJAX:', error);
                    throw error;
                }
            },

            abrir(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    console.log(`✅ Modal abierto: ${modalId}`);
                } else {
                    console.error(`❌ Modal no encontrado: ${modalId}`);
                }
            },

            cerrar(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                    console.log(`✅ Modal cerrado: ${modalId}`);
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
                console.log('📋 Abriendo detalle de venta:', {
                    veId,
                    numeroDocumento
                });

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
                    document.getElementById('detalleNumeroFactura').textContent = data.venta.fa_numero || '-';

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
                    console.error('❌ Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    utils.cerrar('modalDetalleVenta');
                }
            }
        };

        // ==================== REIMPRIMIR NOTA ====================
        const reimprimir = {
            async nota(veId) {
                console.log('🖨️ Reimprimiendo nota de venta:', veId);

                Swal.fire({
                    title: 'Generando PDF...',
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
                        window.abrirPDFDesdeBase64(data.pdf_base64, `Nota_Venta_${veId}.pdf`);
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
                    console.error(' Error:', error);
                    Swal.fire('Error', 'aquiNo se pudo generar el PDF', 'error');
                }
            }
        };
        // ==================== VER FACTURA ====================
        const factura = {
            ver(faId) {
                console.log('📄 Viendo factura:', faId);

                Swal.fire({
                    title: 'Funcionalidad en desarrollo',
                    text: 'La visualización de facturas se implementará próximamente',
                    icon: 'info'
                });
            }
        };

        // ==================== CERRAR AL HACER CLIC FUERA ====================
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('modalDetalleVenta');
            if (modal && modal.style.display === 'flex' && e.target === modal) {
                utils.cerrar('modalDetalleVenta');
            }
        });

        // ==================== API PÚBLICA ====================
        return {
            cerrar: utils.cerrar,
            verDetalle: detalle.abrir,
            reimprimirNota: reimprimir.nota,
            verFactura: factura.ver
        };
    })();
</script>
<script>
    // ==================== EXPORTAR EXCEL ====================
    document.addEventListener('DOMContentLoaded', function() {
        const btnExcel = document.getElementById('btnExportarExcel');

        if (btnExcel) {
            btnExcel.addEventListener('click', function() {
                const form = document.querySelector('.filtro-dinamico');

                let url = '<?php echo SERVER_URL; ?>ajax/ventasHistorialAjax.php?ventasHistorialAjax=exportar_excel';

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

                console.log('📥 Descargando archivo:', url);

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
</script>
