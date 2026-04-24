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
                <div class="ch">
                    <div class="ct">Top 10 Medicamentos por Margen Bruto</div>
                </div>
                <div class="cb">
                    <canvas id="chartMedicamentos" style="width: 100%; min-height: 300px;"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="ch">
                    <div class="ct">Margen Bruto Diario (Últimos 30 días)</div>
                </div>
                <div class="cb">
                    <canvas id="chartDiario" style="width: 100%; min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="card mt16">
            <div class="ch">
                <div class="ct">Margen Bruto por Sucursal (Últimos 6 meses)</div>
            </div>
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
                            <div class="litem"><ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Línea</div>
                                    <div class="th5" id="detalleLaboral">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Sucursal</div>
                                    <div class="th5" id="detalleSucursal">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="cube-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Total Cajas</div>
                                    <div class="th5" id="detalleCajas">-</div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="file-tray-full-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Total Unidades</div>
                                    <div class="th5" id="detalleUnidades">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Valor Inventario</div>
                                    <div class="th5" id="detalleValorado">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="shield-checkmark-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Estado</div>
                                    <div id="detalleEstado">-</div>
                                </div>
                            </div>
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
                                <tr>
                                    <td colspan="5" class="txctr tmut">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalDetalleInventario')">Cerrar</button>
                <?php if (isset($_SESSION['rol_smp']) && $_SESSION['rol_smp'] == 1) { ?>
                <button type="button" class="btn btn-def" onclick="if(typeof window.InventarioModals.abrirBalance === 'function') { window.InventarioModals.abrirBalance(); } else { console.error('window.InventarioModals.abrirBalance is not a function'); }">
                    <ion-icon name="scale-outline"></ion-icon> Balance de Precios
                </button>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Modal Balance de Precios -->
    <div class="mov" id="modalBalanceInventario">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Balance de Precios</div>
                    <div class="ms">Ajustar precios y márgenes - <span id="modalBalanceMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalBalanceInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/inventarioAjax.php" method="POST" data-form="balance" autocomplete="off" id="formBalanceInventario">
                <input type="hidden" name="inventarioAjax" value="balance_precios">
                <input type="hidden" name="med_id" id="balanceMedId">
                <input type="hidden" name="su_id" id="balanceSuId">

                <div class="mb" style="max-height: 70vh; overflow-y: auto;">
                    <div class="stit">Información del Producto</div>

                    <div class="fr1">
                        <div class="card">
                            <div class="cb">
                                <div class="litem"><ion-icon name="medical-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                    <div class="f1">
                                        <div class="tc">Medicamento</div>
                                        <div class="th5" id="balanceNombreMedicamento">-</div>
                                    </div>
                                </div>
                                <div class="litem"><ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                    <div class="f1">
                                        <div class="tc">Laboratorio</div>
                                        <div class="th5" id="balanceLaboratorio">-</div>
                                    </div>
                                </div>
                                <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                    <div class="f1">
                                        <div class="tc">Sucursal</div>
                                        <div class="th5" id="balanceSucursal">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stit">Datos de Auditoría</div>
                    <div class="card">
                        <div class="cb">
                            <div class="fr1">
                                <div class="fg">
                                    <label class="fl" for="balancePrecioCompra">Precio de Compra</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_compra" id="balancePrecioCompra" min="0" readonly>
                                </div>

                            </div>
                            <div class="fr">
                                <div class="fg">
                                    <label class="fl" for="balanceCostoLista">Costo Lista</label>
                                    <input class="inp" type="number" step="0.01" name="lm_costo_lista" id="balanceCostoLista" min="0">
                                </div>
                                <div class="fg">
                                    <label class="fl" for="balanceMargenUnitario">Margen Unitario (%)</label>
                                    <input class="inp" type="number" step="0.01" name="lm_margen_u" id="balanceMargenUnitario" min="0">
                                </div>

                            </div>
                            <div class="fr">
                                <div class="fg">
                                    <label class="fl" for="balanceMargenCaja">Margen Caja (%)</label>
                                    <input class="inp" type="number" step="0.01" name="lm_margen_c" id="balanceMargenCaja" min="0">
                                </div>
                                <div class="fg">
                                    <label class="fl" for="balancePrecioVenta">Precio Venta Unitario</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_venta" id="balancePrecioVenta" min="0" readonly>
                                </div>
                            </div>
                            <div class="fr">
                                <div class="fg">
                                    <label class="fl" for="balancePrecioMinUnitario">Precio Min. Unitario</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_min_u" id="balancePrecioMinUnitario" min="0">
                                </div>
                                <div class="fg">
                                    <label class="fl" for="balancePrecioMinCaja">Precio Min. Caja</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_min_c" id="balancePrecioMinCaja" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="card mt16" style="background: var(--btn-warning-pale); border-color: var(--btn-warning);">
                            <div class="cb">
                                <div class="litem" style="border:none">
                                    <ion-icon name="alert-circle-outline" style="font-size:20px;color:var(--btn-warning)"></ion-icon>
                                    <div class="f1">
                                        <div class="th5" style="color:var(--btn-warning); font-weight:bold; font-size:19px">Atención</div>
                                        <div class="tc" style="color:var(--btn-warning); font-weight:bold; font-size:16px">Este cambio afectará a TODOS los lotes activos de este medicamento en TODAS las sucursales. Verifique los valores antes de guardar.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="mf">
                    <button type="button" class="btn btn-war" onclick="App.closeM('modalBalanceInventario')">Cancelar</button>
                    <button type="submit" class="btn btn-def">
                        <ion-icon name="scale-outline"></ion-icon> Aplicar Balance
                    </button>
                </div>
            </form>

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
                                    <th>Sucursal</th>
                                    <th>Usuario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHistorialMovimientos">
                                <tr>
                                    <td colspan="7" class="txctr tmut">Cargando...</td>
                                </tr>
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
                <button type="button" class="btn btn-def" onclick="window.InventarioModals.guardarConfiguracion()">
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
            const select2 = form.querySelector('select[name="select2"]');
            const select3 = form.querySelector('select[name="select3"]');
            const select4 = form.querySelector('select[name="select4"]');

            let url = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php?inventarioAjax=exportar_excel';

            if (busqueda && busqueda.value.trim()) {
                url += '&busqueda=' + encodeURIComponent(busqueda.value.trim());
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
            const select2 = form.querySelector('select[name="select2"]');
            const select3 = form.querySelector('select[name="select3"]');
            const select4 = form.querySelector('select[name="select4"]');

            let url = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php?inventarioAjax=exportar_pdf';

            if (busqueda && busqueda.value.trim()) {
                url += '&busqueda=' + encodeURIComponent(busqueda.value.trim());
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

        // Funciones para modales de inventario
        // Extender el objeto InventarioModals existente o crear uno nuevo
        window.InventarioModals = window.InventarioModals || {};

        // Agregar función verDetalle si no existe
        if (!window.InventarioModals.verDetalle) {
            window.InventarioModals.verDetalle = function(invId, medId, suId) {
                // Llenar campos ocultos
                document.getElementById('modalDetalleInvId').value = invId;
                document.getElementById('modalDetalleMedId').value = medId;
                document.getElementById('modalDetalleSuId').value = suId;

                // Cargar datos del inventario
                cargarDetalleInventario(invId, medId, suId);

                // Abrir modal
                document.getElementById('modalDetalleInventario').style.display = 'flex';
                document.getElementById('modalDetalleInventario').classList.add('open');
            };
        }

        // Función para cargar datos del detalle de inventario
        async function cargarDetalleInventario(invId, medId, suId) {
            try {
                // Convertir 'null' string a null
                const suIdValue = suId === 'null' ? null : suId;

                // Cargar información general
                const formData1 = new FormData();
                formData1.append('inventarioAjax', 'detalle_general');
                formData1.append('med_id', medId);
                formData1.append('su_id', suIdValue);

                const response1 = await fetch('<?php echo SERVER_URL; ?>ajax/inventarioAjax.php', {
                    method: 'POST',
                    body: formData1
                });

                const data1 = await response1.json();
                if (data1) {
                    document.getElementById('detalleLaboral').textContent = data1.laboral || '-';
                    document.getElementById('detalleSucursal').textContent = data1.sucursal || '-';
                    document.getElementById('detalleCajas').textContent = data1.total_cajas || '-';
                    document.getElementById('detalleUnidades').textContent = data1.total_unidades || '-';
                    document.getElementById('detalleValorado').textContent = data1.total_valorado ? 'Bs. ' + data1.total_valorado : '-';
                    document.getElementById('detalleEstado').innerHTML = data1.estado_html || '-';
                    document.getElementById('modalDetalleMedicamento').textContent = data1.medicamento || '-';
                }

                // Cargar lotes disponibles
                const formData2 = new FormData();
                formData2.append('inventarioAjax', 'lotes_disponibles');
                formData2.append('med_id', medId);
                formData2.append('su_id', suIdValue);

                const response2 = await fetch('<?php echo SERVER_URL; ?>ajax/inventarioAjax.php', {
                    method: 'POST',
                    body: formData2
                });

                const data2 = await response2.json();
                const tablaLotes = document.getElementById('tablaLotesDetalle');
                if (data2 && data2.length > 0) {
                    const esAgregado = suIdValue === null;
                    const filas = data2.map(lote => `
                        <tr>
                            <td>${lote.numero_lote || 'N/A'}</td>
                            ${esAgregado ? `<td>${lote.sucursal || 'N/A'}</td>` : ''}
                            <td>${lote.cant_actual_unidades || 0}</td>
                            <td>Bs. ${lote.precio_venta || 0}</td>
                            <td>${lote.fecha_vencimiento || 'N/A'}</td>
                            <td>${lote.estado || 'N/A'}</td>
                        </tr>
                    `).join('');
                    tablaLotes.innerHTML = filas;

                    // Actualizar encabezado de la tabla si es necesario
                    const thead = tablaLotes.closest('table').querySelector('thead tr');
                    if (esAgregado && thead.children.length === 5) {
                        const sucursalHeader = document.createElement('th');
                        sucursalHeader.textContent = 'Sucursal';
                        thead.insertBefore(sucursalHeader, thead.children[1]);
                    } else if (!esAgregado && thead.children.length === 6) {
                        thead.removeChild(thead.children[1]);
                    }
                } else {
                    const colspan = suIdValue === null ? 6 : 5;
                    tablaLotes.innerHTML = `<tr><td colspan="${colspan}" class="txctr tmut">No hay lotes disponibles</td></tr>`;
                }

            } catch (error) {
                console.error('Error cargando detalle de inventario:', error);
            }
        }

        Object.assign(window.InventarioModals, (function() {
            'use strict';

            function abrirBalance() {
                const medId = document.getElementById('modalDetalleMedId').value;
                const suId = document.getElementById('modalDetalleSuId').value;
                const medicamento = document.getElementById('modalDetalleMedicamento').textContent;
                const sucursal = document.getElementById('detalleSucursal').textContent;

                if (!medId) {
                    console.error('medId missing');
                    Swal.fire('Error', 'No se pudo identificar el producto', 'error');
                    return;
                }

                // Llenar información del modal
                document.getElementById('balanceMedId').value = medId;
                document.getElementById('balanceSuId').value = suId;
                document.getElementById('modalBalanceMedicamento').textContent = medicamento;
                document.getElementById('balanceNombreMedicamento').textContent = medicamento;
                document.getElementById('balanceSucursal').textContent = sucursal;

                // Obtener datos actuales de precios
                obtenerDatosActualesBalance(medId, suId);

                // Abrir modal
                const modal = document.getElementById('modalBalanceInventario');
                if (!modal) {
                    console.error('Modal modalBalanceInventario not found');
                    return;
                }
                modal.style.display = 'flex';
                modal.classList.add('open');

                // Bind calculation events
                bindCalculationEventsBalance();
            }

            async function obtenerDatosActualesBalance(medId, suId) {
                try {
                    const formData = new FormData();
                    formData.append('inventarioAjax', 'obtener_datos_balance');
                    formData.append('med_id', medId);

                    const response = await fetch('<?php echo SERVER_URL; ?>ajax/inventarioAjax.php', {
                        method: 'POST',
                        body: formData
                    });

                    const text = await response.text();

                    if (!response.ok) {
                        console.error('Response not ok, text:', text);
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const data = JSON.parse(text);

                    if (data.success) {
                        // Llenar campos con datos actuales
                        document.getElementById('balancePrecioCompra').value = data.precio_compra || '';
                        document.getElementById('balanceCostoLista').value = data.costo_lista || '';
                        document.getElementById('balanceMargenUnitario').value = data.margen_u || '';
                        document.getElementById('balanceMargenCaja').value = data.margen_c || '';
                        document.getElementById('balancePrecioVenta').value = data.precio_venta || '';
                        document.getElementById('balancePrecioMinUnitario').value = data.precio_min_u || '';
                        document.getElementById('balancePrecioMinCaja').value = data.precio_min_c || '';
                        document.getElementById('balanceLaboratorio').textContent = data.proveedor || 'Sin proveedor';

                        // Trigger initial calculations
                        calcularPrecioVentaBalance();
                        calcularPrecioMinCajaBalance();
                        calcularPrecioMinUnitarioBalance();
                    } else {
                        console.error('Error obteniendo datos:', data.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }

            function calcularPrecioVentaBalance() {
                const costo = parseFloat(document.getElementById('balanceCostoLista')?.value) || 0;
                const margen = parseFloat(document.getElementById('balanceMargenUnitario')?.value) || 0;
                const precioVenta = costo + (costo * margen / 100);
                const precioVentaInput = document.getElementById('balancePrecioVenta');
                if (precioVentaInput) precioVentaInput.value = precioVenta.toFixed(2);
            }

            function calcularPrecioMinCajaBalance() {
                const costo = parseFloat(document.getElementById('balanceCostoLista')?.value) || 0;
                const margen = parseFloat(document.getElementById('balanceMargenCaja')?.value) || 0;
                // Usar valor fijo de 1 para unidades por caja
                const unidadesPorCaja = 1;
                const precioMinCaja = costo * unidadesPorCaja * (1 + margen / 100);
                const precioMinCajaInput = document.getElementById('balancePrecioMinCaja');
                if (precioMinCajaInput) precioMinCajaInput.value = precioMinCaja.toFixed(2);
            }

            function calcularPrecioMinUnitarioBalance() {
                const costo = parseFloat(document.getElementById('balanceCostoLista')?.value) || 0;
                const margen = parseFloat(document.getElementById('balanceMargenCaja')?.value) || 0;
                const precioMinUnitario = costo * (1 + margen / 100);
                const precioMinUnitarioInput = document.getElementById('balancePrecioMinUnitario');
                if (precioMinUnitarioInput) precioMinUnitarioInput.value = precioMinUnitario.toFixed(2);
            }

            function clampMargen(input) {
                let valor = parseFloat(input.value);
                if (isNaN(valor) || valor < 0) {
                    input.value = '0';
                } else if (valor > 100) {
                    input.value = '100';
                } else if (input.value.includes('.')) {
                    let parts = input.value.split('.');
                    if (parts[1].length > 2) {
                        input.value = parts[0] + '.' + parts[1].substring(0, 2);
                    }
                }
            }

            function validarMargen(input) {
                clampMargen(input);
                let valor = parseFloat(input.value);
                input.value = valor.toFixed(2);
            }

            function bindCalculationEventsBalance() {
                const costoLista = document.getElementById('balanceCostoLista');
                const margenU = document.getElementById('balanceMargenUnitario');
                const margenC = document.getElementById('balanceMargenCaja');

                if (costoLista) {
                    costoLista.addEventListener('input', () => {
                        calcularPrecioVentaBalance();
                        calcularPrecioMinCajaBalance();
                        calcularPrecioMinUnitarioBalance();
                    });
                }

                if (margenU) {
                    margenU.addEventListener('input', (e) => {
                        clampMargen(e.target);
                        calcularPrecioVentaBalance();
                    });
                    margenU.addEventListener('blur', (e) => validarMargen(e.target));
                }

                if (margenC) {
                    margenC.addEventListener('input', (e) => {
                        clampMargen(e.target);
                        calcularPrecioMinCajaBalance();
                        calcularPrecioMinUnitarioBalance();
                    });
                    margenC.addEventListener('blur', (e) => validarMargen(e.target));
                }
            }

            function guardarConfiguracion() {
                // Función existente para configuración de inventario
                const invId = document.getElementById('modalConfiguracionInvId').value;
                const medId = document.getElementById('modalConfiguracionMedId').value;
                const suId = document.getElementById('modalConfiguracionSuId').value;
                const minimo = document.getElementById('configuracionMinimo').value;
                const maximo = document.getElementById('configuracionMaximo').value;

                if (!invId || !medId || !suId) {
                    Swal.fire('Error', 'Datos incompletos', 'error');
                    return;
                }

                const formData = new FormData();
                formData.append('inventarioAjax', 'configurar');
                formData.append('inv_id', invId);
                formData.append('med_id', medId);
                formData.append('su_id', suId);
                formData.append('inv_minimo', minimo);
                formData.append('inv_maximo', maximo);

                fetch('<?php echo SERVER_URL; ?>ajax/inventarioAjax.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.Alerta === 'recargar') {
                            App.closeM('modalConfiguracionInventario');
                            Swal.fire('Éxito', data.texto, 'success');
                            // Recargar tabla si es necesario
                            if (window.tableInstance) {
                                window.tableInstance.reload();
                            }
                        } else {
                            Swal.fire('Error', data.texto || 'Error al guardar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error de conexión', 'error');
                    });
            }

            return {
                abrirBalance,
                guardarConfiguracion
            };
        })());

        // Procesar parámetros de URL para filtros
        function procesarParametrosURL() {
            const urlParams = new URLSearchParams(window.location.search);

            // Si hay parámetro 'sucursal', seleccionar esa sucursal en el filtro
            const sucursalParam = urlParams.get('sucursal');
            if (sucursalParam) {
                const selectSucursal = document.querySelector('select[name="select3"]');
                if (selectSucursal) {
                    selectSucursal.value = sucursalParam;
                    // Disparar evento change para que se actualice la tabla
                    selectSucursal.dispatchEvent(new Event('change'));
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(cargarGraficosMargen, 500);
            // Procesar parámetros de URL después de que se cargue la página
            setTimeout(procesarParametrosURL, 100);
        });
    </script>



<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>