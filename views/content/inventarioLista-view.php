<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/inventarioAjax.php"
        data-ajax-param="inventarioAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">Inventario de Medicamentos</div>
                <div class="psub">Consulte el stock, estados y análisis de margen de sus productos</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-out" id="btnExportarExcel" data-tip="Exportar a Excel">
                    <ion-icon name="download-outline"></ion-icon> Excel
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDFInventario" data-tip="Exportar a PDF">
                    <ion-icon name="document-text-outline"></ion-icon> PDF
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
                            <label class="fl">Proveedor</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todos</option>
                                <?php foreach ($datos_select['proveedores'] as $prov) { ?>
                                    <option value="<?php echo $prov['pr_id'] ?>"><?php echo $prov['pr_razon_social'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Estado de Stock</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todos</option>
                                <option value="agotado">Agotado</option>
                                <option value="critico">Crítico</option>
                                <option value="bajo">Bajo</option>
                                <option value="normal">Normal</option>
                                <option value="exceso">Exceso</option>
                                <option value="sin_definir">Sin Definir</option>
                            </select>
                        </div>

                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal</label>
                                <select class="sel select-filtro" name="select3">
                                    <option value="">Todas</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>

                        <!-- <div class="fg">
                            <label class="fl">Forma Farmacéutica</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todas</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div> -->
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Nombre, principio activo o código...">
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
            <div class="cb">
                <div class="tw">
                    <div class="tabla-contenedor"></div>
                </div>
            </div>
        </div>

        <div class="stit">Análisis de Margen Bruto (Últimas Ventas)</div>
        
        <div class="grid4 mb16">
            <div class="statc">
                <div class="siw bl"><ion-icon name="cash-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="totalIngresos">Bs. 0.00</div>
                    <div class="sl">Ingresos Totales (3 meses)</div>
                </div>
            </div>
            <div class="statc">
                <div class="siw ww"><ion-icon name="calculator-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="totalCostos">Bs. 0.00</div>
                    <div class="sl">Costo Total</div>
                </div>
            </div>
            <div class="statc">
                <div class="siw gr"><ion-icon name="trending-up-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="margenBrutoBs">Bs. 0.00</div>
                    <div class="sl">Margen Bruto (Bs)</div>
                </div>
            </div>
            <div class="statc">
                <div class="siw bl"><ion-icon name="stats-chart-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="margenBrutoPct">0%</div>
                    <div class="sl">Margen Bruto (%)</div>
                </div>
            </div>
        </div>

        <div class="fr">
            <div class="card">
                <div class="ch"><div class="ct">Top 10 Medicamentos por Margen Bruto</div></div>
                <div class="cb">
                    <canvas id="chartMedicamentos" style="width: 100%; min-height: 300px;"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="ch"><div class="ct">Margen Bruto Diario (Últimos 30 días)</div></div>
                <div class="cb">
                    <canvas id="chartDiario" style="width: 100%; min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="card mt16">
            <div class="ch"><div class="ct">Margen Bruto por Sucursal (Últimos 6 meses)</div></div>
            <div class="cb">
                <canvas id="chartSucursales" style="width: 100%; height: 300px;"></canvas>
            </div>
        </div>

    </div>

    <!-- Modal Detalle Inventario -->
    <div class="mov" id="modalDetalleInventario">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Detalle de Inventario</div>
                    <div class="ms">Información detallada del inventario - <span id="modalDetalleMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalDetalleInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalDetalleInvId">
            <input type="hidden" id="modalDetalleMedId">
            <input type="hidden" id="modalDetalleSuId">

            <div class="mb">
                <div class="stit">Información General</div>

                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Línea</div><div class="th5" id="detalleLaboral">-</div></div></div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Sucursal</div><div class="th5" id="detalleSucursal">-</div></div></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="cube-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Total Cajas</div><div class="th5" id="detalleCajas">-</div></div></div>
                            <div class="litem"><ion-icon name="file-tray-full-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Total Unidades</div><div class="th5" id="detalleUnidades">-</div></div></div>
                        </div>
                    </div>
                </div>

                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Valor Inventario</div><div class="th5" id="detalleValorado">-</div></div></div>
                            <div class="litem"><ion-icon name="shield-checkmark-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Estado</div><div id="detalleEstado">-</div></div></div>
                        </div>
                    </div>
                </div>

                <div class="stit">Lotes Disponibles</div>
                <div class="card">
                    <div class="tw">
                        <table class="table-detail" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th>N° Lote</th>
                                    <th>Unidades</th>
                                    <th>Precio</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaLotesDetalle">
                                <tr><td colspan="5" class="txctr tmut">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalDetalleInventario')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Historial Inventario -->
    <div class="mov" id="modalHistorialInventario">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Historial de Movimientos</div>
                    <div class="ms">Historial de movimientos - <span id="modalHistorialMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalHistorialInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalHistorialMedId">
            <input type="hidden" id="modalHistorialSuId">

            <div class="mb">
                <div class="stit">Movimientos</div>
                <div class="card">
                    <div class="tw">
                        <table class="table-detail" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Lote</th>
                                    <th>Usuario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHistorialMovimientos">
                                <tr><td colspan="6" class="txctr tmut">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalHistorialInventario')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Configuracion Inventario -->
    <div class="mov" id="modalConfiguracionInventario">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Configurar Inventario</div>
                    <div class="ms">Configurar parámetros - <span id="modalConfiguracionMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalConfiguracionInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalConfiguracionInvId">
            <input type="hidden" id="modalConfiguracionMedId">
            <input type="hidden" id="modalConfiguracionSuId">

            <div class="mb">
                <div class="fr">
                    <div class="fg">
                        <label class="fl req">Cantidad Mínima</label>
                        <input class="inp" type="number" id="configuracionMinimo" min="0" value="0">
                        <small style="color: #666;">Unidades mínimas antes de alertar</small>
                    </div>
                    <div class="fg">
                        <label class="fl">Cantidad Máxima</label>
                        <input class="inp" type="number" id="configuracionMaximo" min="0" placeholder="Opcional">
                        <small style="color: #666;">Unidades máximas permitidas (dejar vacío sin límite)</small>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalConfiguracionInventario')">Cancelar</button>
                <button type="button" class="btn btn-def" onclick="InventarioModals.guardarConfiguracion()">
                    <ion-icon name="checkmark-outline"></ion-icon> Guardar
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script>
        let chartMedicamentos, chartDiario, chartSucursales;

        document.addEventListener('DOMContentLoaded', function() {
            // Función para el botón PDF
            const btnPDFInventario = document.getElementById('btnExportarPDFInventario');
            if (btnPDFInventario) {
                btnPDFInventario.addEventListener('click', function(e) {
                    e.preventDefault();
                    exportarPDFInventario();
                });
            }

            // Función para el botón Excel
            const btnExcelInventario = document.getElementById('btnExportarExcel');
            if (btnExcelInventario) {
                btnExcelInventario.addEventListener('click', function(e) {
                    e.preventDefault();
                    exportarExcelInventario();
                });
            }
        });

        function exportarExcelInventario() {
            const form = document.querySelector('.filtro-dinamico');
            if (!form) {
                console.warn('No se encontró el formulario de filtros');
                return;
            }

            const busqueda = form.querySelector('input[name="busqueda"]');
            const select1 = form.querySelector('select[name="select1"]');
            const select2 = form.querySelector('select[name="select2"]');
            const select3 = form.querySelector('select[name="select3"]');
            const select4 = form.querySelector('select[name="select4"]');

            let url = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php?inventarioAjax=exportar_excel';

            if (busqueda && busqueda.value.trim()) {
                url += '&busqueda=' + encodeURIComponent(busqueda.value.trim());
            }

            if (select1 && select1.value) {
                url += '&select1=' + encodeURIComponent(select1.value);
            }

            if (select2 && select2.value) {
                url += '&select2=' + encodeURIComponent(select2.value);
            }

            if (select3 && select3.value) {
                url += '&select3=' + encodeURIComponent(select3.value);
            }

            if (select4 && select4.value) {
                url += '&select4=' + encodeURIComponent(select4.value);
            }

            console.log(' Generando Excel de inventario:', url);

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Generando Excel',
                text: 'El archivo se está generado...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function exportarPDFInventario() {
            const form = document.querySelector('.filtro-dinamico');
            if (!form) {
                console.warn('No se encontró el formulario de filtros');
                return;
            }

            const busqueda = form.querySelector('input[name="busqueda"]');
            const select1 = form.querySelector('select[name="select1"]');
            const select2 = form.querySelector('select[name="select2"]');
            const select3 = form.querySelector('select[name="select3"]');
            const select4 = form.querySelector('select[name="select4"]');

            let url = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php?inventarioAjax=exportar_pdf';

            if (busqueda && busqueda.value.trim()) {
                url += '&busqueda=' + encodeURIComponent(busqueda.value.trim());
            }

            if (select1 && select1.value) {
                url += '&select1=' + encodeURIComponent(select1.value);
            }

            if (select2 && select2.value) {
                url += '&select2=' + encodeURIComponent(select2.value);
            }

            if (select3 && select3.value) {
                url += '&select3=' + encodeURIComponent(select3.value);
            }

            if (select4 && select4.value) {
                url += '&select4=' + encodeURIComponent(select4.value);
            }

            console.log(' Generando PDF de inventario:', url);

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Generando PDF',
                text: 'El reporte se está generando...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function cargarGraficosMargen() {
            const formData1 = new FormData();
            formData1.append('inventarioAjax', 'margen_medicamentos');

            fetch('<?php echo SERVER_URL; ?>ajax/inventarioAjax.php', {
                    method: 'POST',
                    body: formData1
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const labels = data.map(d => d.med_nombre_quimico.substring(0, 25));
                        const margenes = data.map(d => parseFloat(d.margen_bruto_pct) || 0);
                        const ingresos = data.map(d => parseFloat(d.ingresos_totales) || 0);
                        const costos = data.map(d => parseFloat(d.costo_ventas) || 0);

                        const totalIngresos = ingresos.reduce((a, b) => a + b, 0);
                        const totalCostos = costos.reduce((a, b) => a + b, 0);
                        const totalMargen = totalIngresos - totalCostos;
                        const pctMargen = totalIngresos > 0 ? ((totalMargen / totalIngresos) * 100).toFixed(2) : 0;

                        document.getElementById('totalIngresos').textContent = totalIngresos.toFixed(2) + ' Bs';
                        document.getElementById('totalCostos').textContent = totalCostos.toFixed(2) + ' Bs';
                        document.getElementById('margenBrutoBs').textContent = totalMargen.toFixed(2) + ' Bs';
                        document.getElementById('margenBrutoPct').textContent = pctMargen + '%';

                        const ctx1 = document.getElementById('chartMedicamentos');
                        if (chartMedicamentos) chartMedicamentos.destroy();
                        chartMedicamentos = new Chart(ctx1, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Margen (%)',
                                    data: margenes,
                                    backgroundColor: '#667eea',
                                    borderRadius: 4
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        max: 100,
                                        ticks: {
                                            callback: v => v + '%'
                                        }
                                    }
                                }
                            }
                        });
                    }
                });

            const formData2 = new FormData();
            formData2.append('inventarioAjax', 'margen_diario');

            fetch('<?php echo SERVER_URL; ?>ajax/inventarioAjax.php', {
                    method: 'POST',
                    body: formData2
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const labels = data.map(d => d.fecha);
                        const margenes = data.map(d => parseFloat(d.margen_pct) || 0);

                        const ctx2 = document.getElementById('chartDiario');
                        if (chartDiario) chartDiario.destroy();
                        chartDiario = new Chart(ctx2, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Margen % Diario',
                                    data: margenes,
                                    borderColor: '#43e97b',
                                    backgroundColor: 'rgba(67, 233, 123, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 3,
                                    pointBackgroundColor: '#43e97b'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        ticks: {
                                            callback: v => v + '%'
                                        }
                                    }
                                }
                            }
                        });
                    }
                });

            const formData3 = new FormData();
            formData3.append('inventarioAjax', 'margen_sucursal');

            fetch('<?php echo SERVER_URL; ?>ajax/inventarioAjax.php', {
                    method: 'POST',
                    body: formData3
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const labels = data.map(d => d.su_nombre + ' (' + d.mes + ')');
                        const margenes = data.map(d => parseFloat(d.margen_bruto_pct) || 0);

                        const ctx3 = document.getElementById('chartSucursales');
                        if (chartSucursales) chartSucursales.destroy();
                        chartSucursales = new Chart(ctx3, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Margen Bruto %',
                                    data: margenes,
                                    backgroundColor: '#f093fb',
                                    borderRadius: 4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        ticks: {
                                            callback: v => v + '%'
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(cargarGraficosMargen, 500);
        });
    </script>



<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>