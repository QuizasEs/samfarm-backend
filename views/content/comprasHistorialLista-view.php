<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

    $rol_usuario = $_SESSION['rol_smp'];
    $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/comprasHistorialAjax.php"
        data-ajax-param="comprasHistorialAjax"
        data-ajax-registros="10">
        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="receipt-outline"></ion-icon> Historial de Compras
                </div>
                <div class="psub">Consulta el historial completo de compras realizadas</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-out" id="btnExportarExcelComprasHistorial">
                    <ion-icon name="download-outline"></ion-icon> Exportar Excel
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDFComprasHistorial">
                    <ion-icon name="document-text-outline"></ion-icon> Exportar PDF
                </button>
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
                            <input class="inp" type="date" name="fecha_desde">
                        </div>
                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta">
                        </div>
                        <div class="fg">
                            <label class="fl">Usuario</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todos los usuarios</option>
                                <?php
                                foreach ($datos_select['caja'] as $usuario) {
                                    $nombre_completo = trim(($usuario['us_nombres'] ?? '') . ' ' . ($usuario['us_apellido_paterno'] ?? ''));
                                    echo '<option value="' . $usuario['us_id'] . '">' . $nombre_completo . '</option>';
                                }
                                ?>
                            </select>
                        </div>


                    </div>
                    <div class="fr">
                        <?php if ($rol_usuario == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal</label>
                                <select class="sel select-filtro" name="select3">
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
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por N° compra...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Compras Realizadas</div>
            </div>
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="ch">
            <div class="ct"><ion-icon name="stats-chart-outline"></ion-icon> Análisis de Compras por Sucursal</div>
        </div>
        <div class="cb">
            <div id="grafico-compras-periodo" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <div class="mov" id="modalDetalleCompra">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="document-text-outline"></ion-icon>
                        Detalle de Compra - <span id="modalCompraNumero">...</span>
                    </div>
                    <div class="ms">Información completa de la compra seleccionada</div>
                </div>
                <button class="mcl" onclick="ComprasHistorialModals.cerrar('modalDetalleCompra')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalCompraId">

            <div class="mb">
                <div class="stit">
                    <ion-icon name="information-circle-outline"></ion-icon> Información General
                </div>

                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Número de Compra</div>
                                    <div class="th5" id="detalleNumeroCompra">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha de Compra</div>
                                    <div class="th5" id="detalleFechaCompra">-</div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Sucursal</div>
                                    <div class="th5" id="detalleSucursal">-</div>
                                </div>
                            </div>
                            <div class="litem" style="border:none"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Usuario</div>
                                    <div class="th5" id="detalleUsuario">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stit">
                    <ion-icon name="list-outline"></ion-icon> Detalle de Medicamentos
                </div>
                <div class="card mb16">
                    <div class="cb">
                        <div class="tw">
                             <table class="table-detail">
                                <thead>
                                    <tr>
                                        <th width="35%">Medicamento</th>
                                        <th width="8%">Cant</th>
                                        <th width="12%">Precio</th>
                                        <th width="10%">Desc</th>
                                        <th width="12%">Subtotal</th>
                                        <th width="12%">Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDetalleMedicamentos">
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

                <div class="stit">
                    <ion-icon name="calculator-outline"></ion-icon> Totales
                </div>
                <div class="grid4 mb16">
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleSubtotal">Bs. 0.00</div>
                            <div class="sl">Subtotal</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="receipt-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleImpuestos">Bs. 0.00</div>
                            <div class="sl">Impuestos</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="calculator-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotal">Bs. 0.00</div>
                            <div class="sl">Total</div>
                        </div>
                    </div>
                </div>

                <div class="stit">
                    <ion-icon name="cube-outline"></ion-icon> Estado de Lotes
                </div>
                <div class="grid4">
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="archive-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotalLotes">-</div>
                            <div class="sl">Total Lotes</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="checkmark-circle-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleLotesActivos">-</div>
                            <div class="sl">Activos</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="time-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleLotesEspera">-</div>
                            <div class="sl">En Espera</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="ComprasHistorialModals.cerrar('modalDetalleCompra')">Cerrar</button>
                <button class="btn btn-def" onclick="ComprasHistorialModals.imprimirPDF()" id="btnImprimirPDF">
                    <ion-icon name="print-outline"></ion-icon> Imprimir PDF
                </button>
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
                            <td>
                                <div class="td-main"><strong>${med.nombre}</strong></div>
                                <div class="td-sub">${med.principio_activo}</div>
                                <div class="td-meta">
                                    <ion-icon name="pricetag-outline"></ion-icon> Lote: ${med.numero_lote}
                                    |  ${med.estado_lote}
                                </div>
                            </td>
                            <td style="text-align:center;">${utils.formatearNumero(med.cantidad)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(med.precio_unitario)}</td>
                            <td style="text-align:right;">${utils.formatearMoneda(med.descuento)}</td>
                            <td style="text-align:right;font-weight:bold;">${utils.formatearMoneda(med.subtotal)}</td>
                            <td>${med.fecha_vencimiento ? utils.formatearFecha(med.fecha_vencimiento) : 'N/A'}</td>
                        </tr>
                    `).join('');
                        } else {
                            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin medicamentos</td></tr>';
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    }
                }
            };

            async function imprimirPDF() {
                const coId = document.getElementById('modalCompraId').value;
                if (!coId) {
                    Swal.fire('Error', 'No se ha podido obtener el ID de la compra.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Generando PDF...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const data = await utils.ajax({
                        comprasHistorialAjax: 'exportar_pdf_detalle',
                        co_id: coId
                    });

                    Swal.close();

                    if (data.success && data.pdf_base64) {
                        window.abrirPDFDesdeBase64(data.pdf_base64, `Compra_${coId}.pdf`);
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

                    const sucursales = datos.map(d => d.sucursal.length > 25 ? d.sucursal.substring(0, 25) + '...' : d.sucursal);
                    const cantidades = datos.map(d => parseInt(d.cantidad_compras));
                    const ticketsPromedios = datos.map(d => parseFloat(d.ticket_promedio));

                    const myChart = echarts.init(graficoContainer);

                    const option = {
                        title: {
                            text: 'Distribución de Compras por Sucursal - Ticket Promedio',
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
                                    const value = param.seriesName === 'Cantidad de Compras' ?
                                        param.value :
                                        'Bs ' + parseFloat(param.value).toFixed(2);
                                    result += param.marker + ' ' + param.seriesName + ': ' + value + '<br>';
                                });
                                return result;
                            }
                        },
                        legend: {
                            data: ['Cantidad de Compras', 'Ticket Promedio'],
                            bottom: 0,
                            textStyle: {
                                fontSize: 13,
                                color: '#009DC4'
                            }
                        },


                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '15%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: sucursales,
                            axisLabel: {
                                rotate: 45,
                                interval: 0
                            }
                        },
                        yAxis: [{
                            type: 'value',
                            name: 'Cantidad',
                            position: 'left'
                        }, {
                            type: 'value',
                            name: 'Ticket Promedio (Bs)',
                            position: 'right'
                        }],
                        series: [{
                            name: 'Cantidad de Compras',
                            type: 'bar',
                            data: cantidades,
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                        offset: 0,
                                        color: '#3498db'
                                    },
                                    {
                                        offset: 1,
                                        color: '#2980b9'
                                    }
                                ])
                            }
                        }, {
                            name: 'Ticket Promedio',
                            type: 'line',
                            yAxisIndex: 1,
                            data: ticketsPromedios,
                            itemStyle: {
                                color: '#e74c3c'
                            },
                            smooth: true,
                            symbolSize: 8
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
                    const select2 = form.querySelector('select[name="select2"]');
                    const select3 = form.querySelector('select[name="select3"]');
                    const select4 = form.querySelector('select[name="select4"]');

                    if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
                    if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
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
                    const select2 = form.querySelector('select[name="select2"]');
                    const select3 = form.querySelector('select[name="select3"]');

                    if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
                    if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
                    if (select2 && select2.value) params.append('select2', select2.value);
                    if (select3 && select3.value) params.append('select3', select3.value);
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



            return {
                cerrar: utils.cerrar,
                verDetalle: detalle.abrir,
                imprimirPDF: imprimirPDF
            };
        })();
    </script>



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
