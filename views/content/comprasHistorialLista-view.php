<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/comprasHistorialAjax.php"
        data-ajax-param="comprasHistorialAjax"
        data-ajax-registros="10">
        <div class="title">
            <h3>
                <ion-icon name="receipt-outline"></ion-icon> Historial de Compras
            </h3>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">
                    <small>Fecha Compra Desde</small>
                    <input type="date" name="fecha_desde" placeholder="Desde">
                </div>

                <div class="form-fechas">
                    <small>Fecha Compra Hasta</small>
                    <input type="date" name="fecha_hasta" placeholder="Hasta">
                </div>


                <div class="form-fechas">
                    <small>Proveedor</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todos los proveedores</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Laboratorio</small>
                    <select class="select-filtro" name="select2">
                        <option value="">Todos los laboratorios</option>
                    </select>
                </div>

                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                    <div class="form-fechas">
                        <small>Sucursal</small>
                        <select class="select-filtro" name="select3">
                            <option value="">Todas las sucursales</option>
                        </select>
                    </div>
                <?php } ?>

                <div class="form-fechas">
                    <small>Usuario</small>
                    <select class="select-filtro" name="select4">
                        <option value="">Todos los usuarios</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Estado de Lotes</small>
                    <select class="select-filtro" name="select5">
                        <option value="">Todos</option>
                        <option value="pendientes">Con lotes pendientes</option>
                        <option value="activos">Con lotes activos</option>
                        <option value="completado">Completamente procesado</option>
                    </select>
                </div>

                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por N° compra, factura, NIT o proveedor...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>
            </div>

            <button type="button" class="btn success" id="btnExportarExcelComprasHistorial">
                <ion-icon name="download-outline"></ion-icon> Excel
            </button>
            <button type="button" class="btn danger" id="btnExportarPDFComprasHistorial">
                <ion-icon name="document-text-outline"></ion-icon> PDF
            </button>
        </form>

        <div class="tabla-contenedor"></div>
    </div>

    <div class="container" id="grafico-compras-container">
        <div class="title">
            <h3>
                <ion-icon name="stats-chart-outline"></ion-icon> Análisis de Compras
            </h3>
        </div>
        <div id="grafico-compras-periodo" style="width: 100%; height: 400px;"></div>
    </div>

    <div class="modal" id="modalDetalleCompra" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="document-text-outline"></ion-icon>
                    Detalle de Compra - <span id="modalCompraNumero">...</span>
                </div>
                <a class="close" onclick="ComprasHistorialModals.cerrar('modalDetalleCompra')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="modalCompraId">

            <div class="modal-group">
                <div class="row">
                    <h3><ion-icon name="information-circle-outline"></ion-icon> Información General</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Número de Compra:</label>
                        <p id="detalleNumeroCompra">-</p>
                    </div>
                    <div class="col">
                        <label>Fecha de Compra:</label>
                        <p id="detalleFechaCompra">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Número de Factura:</label>
                        <p id="detalleNumeroFactura">-</p>
                    </div>
                    <div class="col">
                        <label>Fecha de Factura:</label>
                        <p id="detalleFechaFactura">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Proveedor:</label>
                        <p id="detalleProveedor">-</p>
                    </div>
                    <div class="col">
                        <label>Laboratorio:</label>
                        <p id="detalleLaboratorio">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Sucursal:</label>
                        <p id="detalleSucursal">-</p>
                    </div>
                    <div class="col">
                        <label>Usuario:</label>
                        <p id="detalleUsuario">-</p>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="list-outline"></ion-icon> Detalle de Medicamentos</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>MEDICAMENTO</th>
                                    <th>CANTIDAD</th>
                                    <th>PRECIO</th>
                                    <th>DESCUENTO</th>
                                    <th>SUBTOTAL</th>
                                    <th>LOTE</th>
                                    <th>ESTADO LOTE</th>
                                    <th>VENCIMIENTO</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetalleMedicamentos">
                                <tr>
                                    <td colspan="9" style="text-align:center;">
                                        <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="calculator-outline"></ion-icon> Totales</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Subtotal:</label>
                        <p id="detalleSubtotal">Bs. 0.00</p>
                    </div>
                    <div class="col">
                        <label>Impuestos:</label>
                        <p id="detalleImpuestos">Bs. 0.00</p>
                    </div>
                    <div class="col">
                        <label>Total:</label>
                        <p id="detalleTotal" style="font-weight: bold; color: #2c3e50;">Bs. 0.00</p>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="cube-outline"></ion-icon> Estado de Lotes</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Total de Lotes:</label>
                        <p id="detalleTotalLotes">-</p>
                    </div>
                    <div class="col">
                        <label>Activos:</label>
                        <p id="detalleLotesActivos">-</p>
                    </div>
                    <div class="col">
                        <label>En Espera:</label>
                        <p id="detalleLotesEspera">-</p>
                    </div>
                </div>

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn default" onclick="ComprasHistorialModals.cerrar('modalDetalleCompra')">
                        Cerrar
                    </a>
                    <a href="javascript:void(0)" class="btn primary" onclick="ComprasHistorialModals.imprimirPDF()">
                        <ion-icon name="print-outline"></ion-icon> Imprimir PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ComprasHistorialModals = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/comprasHistorialAjax.php';

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

                        const data = await response.json();
                        return data;

                    } catch (error) {
                        console.error('Error AJAX:', error);
                        throw error;
                    }
                },

                abrir(modalId) {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.style.display = 'flex';
                    }
                },

                cerrar(modalId) {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.style.display = 'none';
                    }
                },

                formatearFecha(fecha) {
                    if (!fecha) return 'N/A';
                    const d = new Date(fecha);
                    const dia = String(d.getDate()).padStart(2, '0');
                    const mes = String(d.getMonth() + 1).padStart(2, '0');
                    const anio = d.getFullYear();
                    return `${dia}/${mes}/${anio}`;
                },

                formatearNumero(num) {
                    return parseInt(num || 0).toLocaleString('es-BO');
                },

                formatearMoneda(num) {
                    return 'Bs ' + parseFloat(num || 0).toFixed(2);
                }
            };

            const detalle = {
                async abrir(coId, coNumero) {
                    document.getElementById('modalCompraNumero').textContent = coNumero;
                    document.getElementById('modalCompraId').value = coId;

                    utils.abrir('modalDetalleCompra');

                    document.getElementById('tablaDetalleMedicamentos').innerHTML =
                        '<tr><td colspan="9" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                    try {
                        const data = await utils.ajax({
                            comprasHistorialAjax: 'detalle',
                            co_id: coId
                        });

                        if (data.error) {
                            throw new Error(data.error);
                        }

                        document.getElementById('detalleNumeroCompra').textContent = data.numero_compra || 'N/A';
                        document.getElementById('detalleFechaCompra').textContent = data.fecha_compra || 'N/A';
                        document.getElementById('detalleNumeroFactura').textContent = data.numero_factura || 'N/A';
                        document.getElementById('detalleFechaFactura').textContent = data.fecha_factura || 'N/A';
                        document.getElementById('detalleProveedor').textContent = data.proveedor || 'N/A';
                        document.getElementById('detalleLaboratorio').textContent = data.laboratorio || 'N/A';
                        document.getElementById('detalleSucursal').textContent = data.sucursal || 'N/A';
                        document.getElementById('detalleUsuario').textContent = data.usuario || 'N/A';

                        document.getElementById('detalleSubtotal').textContent = utils.formatearMoneda(data.subtotal);
                        document.getElementById('detalleImpuestos').textContent = utils.formatearMoneda(data.impuestos);
                        document.getElementById('detalleTotal').textContent = utils.formatearMoneda(data.total);

                        document.getElementById('detalleTotalLotes').textContent = data.total_lotes || '0';
                        document.getElementById('detalleLotesActivos').textContent = data.lotes_activos || '0';
                        document.getElementById('detalleLotesEspera').textContent = data.lotes_espera || '0';

                        const tbody = document.getElementById('tablaDetalleMedicamentos');
                        if (data.medicamentos && data.medicamentos.length > 0) {
                            tbody.innerHTML = data.medicamentos.map((med, idx) => `
                        <tr>
                            <td>${idx + 1}</td>
                            <td>
                                <strong>${med.nombre}</strong><br>
                                <small style="color:#666;">${med.principio_activo}</small>
                            </td>
                            <td style="text-align:center;">${utils.formatearNumero(med.cantidad)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(med.precio_unitario)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(med.descuento)}</td>
                            <td style="text-align:right;font-weight:bold;">${utils.formatearMoneda(med.subtotal)}</td>
                            <td>${med.numero_lote}</td>
                            <td>${med.estado_lote}</td>
                            <td>${med.fecha_vencimiento ? utils.formatearFecha(med.fecha_vencimiento) : 'N/A'}</td>
                        </tr>
                    `).join('');
                        } else {
                            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin medicamentos</td></tr>';
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    }
                }
            };

            function imprimirPDF() {
                const coId = document.getElementById('modalCompraId').value;
                if (!coId) {
                    Swal.fire('Error', 'No se pudo obtener el ID de la compra', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Generando PDF',
                    text: 'El documento se está generando...',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            async function cargarGrafico() {
                const graficoContainer = document.getElementById('grafico-compras-periodo');
                if (!graficoContainer) {
                    return;
                }

                try {
                    const params = {
                        comprasHistorialAjax: 'grafico'
                    };

                    const form = document.querySelector('.filtro-dinamico');
                    if (form) {
                        const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                        const fechaHasta = form.querySelector('input[name="fecha_hasta"]');

                        if (fechaDesde && fechaDesde.value) {
                            params.fecha_desde = fechaDesde.value;
                        }
                        if (fechaHasta && fechaHasta.value) {
                            params.fecha_hasta = fechaHasta.value;
                        }
                    }

                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(params)
                    });

                    const datos = await response.json();

                    if (datos.error) {
                        console.error('Error en gráfico:', datos.error);
                        return;
                    }

                    if (!datos || datos.length === 0) {
                        graficoContainer.innerHTML = '<div style="text-align:center;padding:40px;color:#999;">No hay datos para mostrar en el gráfico</div>';
                        return;
                    }

                    const fechas = datos.map(d => {
                        const [y, m, dia] = d.fecha.split('-');
                        return `${dia}/${m}`;
                    });
                    const cantidades = datos.map(d => parseInt(d.cantidad_compras));
                    const montos = datos.map(d => parseFloat(d.total_monto));

                    const myChart = echarts.init(graficoContainer);

                    const option = {
                        title: {
                            text: 'Compras por Fecha',
                            left: 'center'
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'shadow'
                            },
                            formatter: function(params) {
                                let result = params[0].name + '<br>';
                                params.forEach(param => {
                                    const value = param.seriesName === 'Cantidad' ?
                                        param.value :
                                        'Bs ' + parseFloat(param.value).toFixed(2);
                                    result += param.marker + ' ' + param.seriesName + ': ' + value + '<br>';
                                });
                                return result;
                            }
                        },
                        legend: {
                            data: ['Cantidad', 'Monto Total'],
                            bottom: 0
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '15%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: fechas
                        },
                        yAxis: [{
                            type: 'value',
                            name: 'Cantidad',
                            position: 'left'
                        }, {
                            type: 'value',
                            name: 'Monto (Bs)',
                            position: 'right'
                        }],
                        series: [{
                            name: 'Cantidad',
                            type: 'bar',
                            data: cantidades,
                            itemStyle: {
                                color: '#3498db'
                            }
                        }, {
                            name: 'Monto Total',
                            type: 'line',
                            yAxisIndex: 1,
                            data: montos,
                            itemStyle: {
                                color: '#2ecc71'
                            }
                        }]
                    };

                    myChart.setOption(option);

                    window.addEventListener('resize', function() {
                        myChart.resize();
                    });

                } catch (error) {
                    console.error('Error cargando gráfico:', error);
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                const pageCheck = document.getElementById('grafico-compras-periodo');
                if (!pageCheck) {
                    return;
                }

                setTimeout(() => cargarGrafico(), 500);

                const filtros = document.querySelectorAll('.filtro-dinamico select, .filtro-dinamico input[type="date"]');
                filtros.forEach(filtro => {
                    filtro.addEventListener('change', function() {
                        setTimeout(() => cargarGrafico(), 500);
                    });
                });

                const btnExcel = document.getElementById('btnExportarExcelComprasHistorial');
                if (btnExcel) {
                    btnExcel.addEventListener('click', function() {
                        exportarExcelComprasHistorial();
                    });
                }

                const btnPDF = document.getElementById('btnExportarPDFComprasHistorial');
                if (btnPDF) {
                    btnPDF.addEventListener('click', function() {
                        exportarPDFComprasHistorial();
                    });
                }
            });

            function exportarExcelComprasHistorial() {
                const form = document.querySelector('.filtro-dinamico');
                const params = new URLSearchParams();
                params.append('comprasHistorialAjax', 'exportar_excel');

                if (form) {
                    const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                    const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
                    const select1 = form.querySelector('select[name="select1"]');
                    const select2 = form.querySelector('select[name="select2"]');
                    const select3 = form.querySelector('select[name="select3"]');
                    const select4 = form.querySelector('select[name="select4"]');

                    if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
                    if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
                    if (select1 && select1.value) params.append('select1', select1.value);
                    if (select2 && select2.value) params.append('select2', select2.value);
                    if (select3 && select3.value) params.append('su_id', select3.value);
                    if (select4 && select4.value) params.append('select4', select4.value);
                }

                const url = '<?php echo SERVER_URL; ?>ajax/comprasHistorialAjax.php?' + params.toString();

                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Descargando',
                    text: 'El archivo Excel se está descargando...',
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            function exportarPDFComprasHistorial() {
                const form = document.querySelector('.filtro-dinamico');
                const params = new URLSearchParams();
                params.append('comprasHistorialAjax', 'exportar_pdf');

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

                const url = '<?php echo SERVER_URL; ?>ajax/comprasHistorialAjax.php?' + params.toString();

                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Generando PDF',
                    text: 'El reporte se está generando...',
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            document.addEventListener('click', function(e) {
                const modal = document.getElementById('modalDetalleCompra');
                if (modal && modal.style && modal.style.display === 'flex' && e.target === modal) {
                    modal.style.display = 'none';
                }
            });

            return {
                cerrar: utils.cerrar,
                verDetalle: detalle.abrir,
                imprimirPDF: imprimirPDF
            };
        })();
    </script>

    <style>
        #grafico-compras-periodo {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        #grafico-compras-container {
            margin-top: 20px;
        }
    </style>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>