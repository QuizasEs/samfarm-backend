<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    /* Admin o Gerente */

    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/ventasHistorialAjax.php"
        data-ajax-param="ventasHistorialAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="receipt-outline"></ion-icon> Historial de Ventas
                </div>
                <div class="psub">Consulte y administre el historial completo de ventas realizadas</div>
            </div>
            <div class="tbr">
                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                        <button type="button" id="btnExportarExcel" class="btn btn-out">
                            <ion-icon name="download-outline"></ion-icon> Exportar Excel
                        </button>
                <?php } ?>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Desde</label>
                            <input class="inp" type="date" name="fecha_desde" title="Fecha desde">
                        </div>
                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta" title="Fecha hasta">
                        </div>

                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursales</label>
                                <select class="sel select-filtro" name="select1">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <div></div>
                        <?php } ?>

                        <div class="fg">
                            <label class="fl">Cajero</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todos los cajeros</option>
                                <?php foreach ($datos_select['caja'] as $caja) { ?>
                                    <option value="<?php echo $caja['us_id'] ?>"><?php echo $caja['us_nombres'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Tipo de documento</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todos los tipos</option>
                                <option value="nota de venta">Nota de Venta</option>
                                <option value="factura">Factura</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por N° documento o cliente...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle de Venta -->
    <div id="modalDetalleVenta" class="mov">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="receipt-outline"></ion-icon>
                        Detalle de Venta
                    </div>
                    <div class="ms">Información completa de la venta seleccionada</div>
                </div>
                <button class="mcl" onclick="VentasHistorialModals.cerrar('modalDetalleVenta')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="stit">Información de la Venta</div>

                <input type="hidden" id="modalDetalleVeId">

                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Documento</div>
                                    <div class="th5" id="detalleNumeroDocumento"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha</div>
                                    <div class="th5" id="detalleFecha"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Cliente</div>
                                    <div class="th5" id="detalleCliente"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="card-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">CI/NIT</div>
                                    <div class="th5" id="detalleCarnet"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="person-circle-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Vendedor</div>
                                    <div class="th5" id="detalleVendedor"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Sucursal</div>
                                    <div class="th5" id="detalleSucursal"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Caja</div>
                                    <div class="th5" id="detalleCaja"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="receipt-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Factura</div>
                                    <div class="th5" id="detalleNumeroFactura">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stit">Medicamentos Vendidos</div>
                <div class="card mb16">
                    <div class="cb">
                        <div class="tw" style="max-height: 400px; overflow-y: auto;">
                            <table>
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
                                        <td colspan="6" class="txctr">
                                            <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="stit">Totales</div>
                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem">
                                <div class="tc">Subtotal</div>
                                <div class="th5" id="detalleSubtotal">Bs. 0.00</div>
                            </div>
                            <div class="litem">
                                <div class="tc">Impuestos</div>
                                <div class="th5" id="detalleImpuesto">Bs. 0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb txctr">
                            <div class="tsuc" style="font-size:18px;font-weight:700">TOTAL: <span id="detalleTotal">Bs. 0.00</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="VentasHistorialModals.cerrar('modalDetalleVenta')">Cerrar</button>
            </div>
        </div>
    </div>

<?php } else { ?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit">Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb txctr" style="padding:60px">
                <ion-icon name="lock-closed-outline" style="font-size:48px;color:var(--text-faint);margin-bottom:16px"></ion-icon>
                <div class="th3 mb8">Acceso Denegado</div>
                <div class="tbs tmut">No tiene permisos para acceder a esta sección.</div>
            </div>
        </div>
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
                } else {
                    console.error(`  Modal no encontrado: ${modalId}`);
                }
            },

            cerrar(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 300);
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

        // ==================== MODAL DETALLE ====================
        const detalle = {
            async abrir(veId, numeroDocumento) {
                console.log(' Abriendo detalle de venta:', {
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
                    console.error('  Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    utils.cerrar('modalDetalleVenta');
                }
            }
        };

        // ==================== REIMPRIMIR NOTA ====================
        const reimprimir = {
            async nota(veId) {
                console.log(' Reimprimiendo nota de venta:', veId);

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
                    Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                }
            }
        };
        // ==================== VER FACTURA ====================
        const factura = {
            ver(faId) {
                console.log(' Viendo factura:', faId);

                Swal.fire({
                    title: 'Funcionalidad en desarrollo',
                    text: 'La visualización de facturas se implementará próximamente',
                    icon: 'info'
                });
            }
        };



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

                console.log(' Descargando archivo:', url);

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
