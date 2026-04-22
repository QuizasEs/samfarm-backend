<?php
if (isset($_SESSION['id_smp']) && $_SESSION['rol_smp'] == 1) {
?>

    <div class="">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="business-outline"></ion-icon> Gestión de Sucursales</div>
                <div class="psub">Administre y consulte la información detallada de sus sucursales</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="SucursalesModals.abrirModalNuevo()">
                    <ion-icon name="add-circle-outline"></ion-icon> Nuevo Sucursal
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDF">
                    <ion-icon name="document-text-outline"></ion-icon> PDF
                </button>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico" id="filtroSucursales">
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="estado_filtro" id="estado_filtro">
                                <option value="">Todas</option>
                                <option value="1">Activas</option>
                                <option value="0">Inactivas</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" id="busqueda_sucursal" placeholder="Buscar por nombre o dirección...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div id="contenedorGrillasSucursales" class="grillas-container">
                    <div class="loader-grillas">
                        <ion-icon name="hourglass-outline"></ion-icon>
                        <p>Cargando sucursales...</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- grafico de sucursales -->
        <div class="card mb16">
            <div class="ch">
                <div class="ct">
                    <ion-icon name="bar-chart-outline"></ion-icon> Análisis Costo-Beneficio por Sucursal
                    <div class="grafico-periodo">
                        <select id="periodoGrafico" class="sel">
                            <option value="mes">Último Mes</option>
                            <option value="trimestre">Último Trimestre</option>
                            <option value="semestre" selected>Último Semestre</option>
                            <option value="anio">Último Año</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="cb">
                <div id="graficoCostoBeneficio"></div>
            </div>
        </div>
    </div>
    <!-- cajas abiertas -->
    <div class="mov" id="modalCajasAbiertas" style="display: none;">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="cash-outline"></ion-icon> Cajas Abiertas - <span id="modalCajasNombreSucursal">...</span></div>
                    <div class="ms">Lista de cajas abiertas en esta sucursal</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalCajas()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <input type="hidden" id="modalCajasSuId">

                <div class="fr2">
                    <div class="fg">
                        <label class="fl">Usuario</label>
                        <select class="sel" id="filtroUsuarioCaja">
                            <option value="">Todos</option>
                        </select>
                    </div>

                    <div class="fg">
                        <label class="fl">Buscar caja</label>
                        <div class="inpg">
                            <input class="inp" type="text" id="busquedaCaja" placeholder="Buscar por nombre de caja...">
                            <button type="button" class="btn btn-def" onclick="SucursalesModals.buscarCajas()">
                                <ion-icon name="search-outline"></ion-icon>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tw table-detail">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="55%">Caja</th>
                                <th width="25%">Detalles</th>
                                <th width="20%">Tiempo</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCajasAbiertas">
                            <tr>
                                <td colspan="4" style="text-align:center;">
                                    <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalCajas()">Cerrar</button>
            </div>
        </div>
    </div>
    <!-- MODAL NUEVA SUCURSAL -->
    <div class="mov" id="modalNuevaSucursal" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="add-circle-outline"></ion-icon> Nueva Sucursal</div>
                    <div class="ms">Registre una nueva sucursal en el sistema</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalNuevo()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <form id="formNuevaSucursal" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/sucursalAjax.php" method="POST" data-form="save" autocomplete="off">
                    <input type="hidden" name="sucursalAjax" value="nuevo">

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="information-circle-outline"></ion-icon> Información de la Sucursal</h3>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl required">Nombre de la Sucursal</label>
                            <input class="inp" type="text" name="Nombre_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]{3,120}" maxlength="120" required>
                        </div>
                    </div>

                    <div class="fr2">
                        <div class="fg">
                            <label class="fl required">Dirección</label>
                            <input class="inp" type="text" name="Direccion_reg" maxlength="250" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_reg" pattern="[0-9+\-\s()]{6,30}" maxlength="30">
                        </div>
                    </div>
                </form>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalNuevo()">Cancelar</button>
                <button type="submit" form="formNuevaSucursal" class="btn btn-def">Registrar</button>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR SUCURSAL -->
    <div class="mov" id="modalEditarSucursal" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="create-outline"></ion-icon> Editar Sucursal</div>
                    <div class="ms">Modifique la información de la sucursal</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalEditar()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <form id="formEditarSucursal" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/sucursalAjax.php" method="POST" data-form="update" autocomplete="off">
                    <input type="hidden" name="sucursalAjax" value="editar">
                    <input type="hidden" name="su_id_editar" id="su_id_editar">

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="information-circle-outline"></ion-icon> Información de la Sucursal</h3>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl required">Nombre de la Sucursal</label>
                            <input class="inp" type="text" name="Nombre_edit" id="Nombre_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]{3,120}" maxlength="120" required>
                        </div>
                    </div>

                    <div class="fr2">
                        <div class="fg">
                            <label class="fl required">Dirección</label>
                            <input class="inp" type="text" name="Direccion_edit" id="Direccion_edit" maxlength="250" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_edit" id="Telefono_edit" pattern="[0-9+\-\s()]{6,30}" maxlength="30">
                        </div>
                    </div>


                </form>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalEditar()">Cancelar</button>
                <button type="submit" form="formEditarSucursal" class="btn btn-def">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <!-- MODAL DETALLE SUCURSAL -->
    <div class="mov" id="modalDetalleSucursal" style="display: none;">
        <div class="modal" style="max-width: 1200px;">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="business-outline"></ion-icon> Detalle de Sucursal - <span id="detalleNombreSucursal">...</span></div>
                    <div class="ms">Información completa y estadísticas de la sucursal</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalDetalle()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <input type="hidden" id="detalleSuId">

                <div class="tb">
                    <h3 class="th3"><ion-icon name="information-circle-outline"></ion-icon> Información General</h3>
                </div>

                <div class="fr">
                    <div class="card">
                        <div class="cb">


                            <div class="litem">
                                <ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="tc">Nombre:</label>
                                    <p class="th5" id="detalleNombreSucursalInfo">-</p>
                                </div>
                            </div>
                            <div class="litem">
                                <ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="f1">Dirección:</label>
                                    <p class="tbs" id="detalleDireccionSucursal">-</p>
                                </div>
                            </div>
                            <div class="litem">
                                <ion-icon name="call-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="f1">Teléfono:</label>
                                    <p class="tbs" id="detalleTelefonoSucursal">-</p>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem">
                                <ion-icon name="power-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="f1">Estado:</label>
                                    <p class="tbs" id="detalleEstadoSucursal">-</p>
                                </div>
                            </div>
                            <div class="litem">
                                <ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="fl">Fecha de Creación:</label>
                                    <p class="tbs" id="detalleFechaCreacionSucursal">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tb">
                    <h3 class="th3"><ion-icon name="stats-chart-outline"></ion-icon> Estadísticas de Ventas</h3>
                </div>

                <div class="fr2">
                    <div class="fg">
                        <div id="graficoVentasSucursal" style="width:100%;height:300px;background:#f9f9f9;border-radius:8px;"></div>
                    </div>
                    <div class="fg">
                        <div class="statc">
                            <div class="stat">
                                <label>Total Ventas:</label>
                                <p id="detalleTotalVentasSucursal">0</p>
                            </div>
                            <div class="stat">
                                <label>Monto Total:</label>
                                <p id="detalleMontoTotalSucursal">Bs. 0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tb">
                    <h3 class="th3"><ion-icon name="people-outline"></ion-icon> Personal Asignado</h3>
                </div>
                <div class="tw">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPersonalSucursal">
                            <tr>
                                <td colspan="4" style="text-align:center;">
                                    <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalDetalle()">Cerrar</button>
                <button class="btn btn-def" onclick="SucursalesModals.editarDesdeDetalle()"><ion-icon name="create-outline"></ion-icon> Editar</button>
                <button class="btn btn-out" id="btnToggleEstadoDetalleSucursal"><ion-icon name="power-outline"></ion-icon> Cambiar Estado</button>
            </div>
        </div>
    </div>

    <style>
        #graficoCostoBeneficio {
            height: 320px !important;
            min-height: 320px !important;
            width: 100% !important;
            display: block !important;
        }
    </style>

    <!-- modal sucursales editar y registrar -->
    <script>
        const SucursalesModals = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/sucursalAjax.php';
            let currentFilters = {
                busqueda: '',
                estado: ''
            };
            let sucursalActualDetalle = null;

            function init() {
                cargarSucursales();
                cargarGraficoCostoBeneficio();
                bindEvents();
                cargarEcharts();
            }
            async function cargarGraficoCostoBeneficio(periodo = 'semestre') {
                if (typeof echarts === 'undefined') {
                    console.warn('ECharts no está cargado');
                    return;
                }

                const container = document.getElementById('graficoCostoBeneficio');
                if (!container) return;

                const chart = echarts.init(container);

                chart.showLoading({
                    text: 'Cargando datos...',
                    color: '#1976D2',
                    textColor: '#0044DE',
                    maskColor: 'rgba(255, 255, 255, 0.8)'
                });

                try {
                    console.log('Solicitando datos para período:', periodo);

                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            sucursalAjax: 'costo_beneficio',
                            periodo: periodo
                        })
                    });

                    const data = await response.json();
                    console.log('Datos recibidos:', data);

                    chart.hideLoading();

                    if (data.error) {
                        throw new Error(data.mensaje);
                    }

                    renderizarGraficoCostoBeneficio(data.sucursales || []);

                } catch (error) {
                    console.error('Error:', error);
                    chart.hideLoading();

                    chart.setOption({
                        title: {
                            text: 'Error al cargar datos',
                            left: 'center',
                            top: 'middle',
                            textStyle: {
                                color: '#f44336',
                                fontSize: 16
                            }
                        }
                    });
                }
            }


            function renderizarGraficoCostoBeneficio(sucursales) {
                const container = document.getElementById('graficoCostoBeneficio');
                if (!container || typeof echarts === 'undefined') return;

                const chart = echarts.init(container);
                chart.clear();

                if (!sucursales || sucursales.length === 0) {
                    chart.setOption({
                        title: {
                            text: 'No hay datos de costo-beneficio',
                            subtext: 'No hay registros de ventas o compras para este período',
                            left: 'center',
                            top: 'middle',
                            textStyle: {
                                color: '#999',
                                fontSize: 16
                            },
                            subtextStyle: {
                                color: '#aaa',
                                fontSize: 12
                            }
                        }
                    });
                    return;
                }

                const nombres = sucursales.map(s => s.sucursal);
                const costos = sucursales.map(s => parseFloat(s.total_costos));
                const ingresos = sucursales.map(s => parseFloat(s.total_ingresos));
                const beneficios = sucursales.map(s => parseFloat(s.beneficio_neto));

                const option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: function(params) {
                            let html = `<strong>${params[0].axisValue}</strong><br/>`;
                            params.forEach(param => {
                                const valor = param.value;
                                const signo = valor >= 0 ? '+' : '';
                                html += `${param.marker} ${param.seriesName}: ${signo}Bs. ${valor.toFixed(2)}<br/>`;
                            });
                            return html;
                        }
                    },
                    legend: {
                        data: ['Costos', 'Ingresos', 'Beneficio Neto'],
                        top: 10,
                        textStyle: {
                            fontSize: 12,
                            color: '0044DE'
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        top: '15%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: nombres,
                        axisLabel: {
                            fontSize: 11,
                            rotate: 30,
                            interval: 0
                        }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: {
                            fontSize: 11,
                            formatter: 'Bs. {value}'
                        },
                        splitLine: {
                            lineStyle: {
                                type: 'dashed',
                                color: '#e0e0e0'
                            }
                        }
                    },
                    series: [{
                            name: 'Costos',
                            type: 'bar',
                            data: costos,
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                        offset: 0,
                                        color: '#FF6B6B'
                                    },
                                    {
                                        offset: 1,
                                        color: '#EE5A6F'
                                    }
                                ]),
                                borderRadius: [6, 6, 0, 0]
                            },
                            label: {
                                show: true,
                                position: 'top',
                                formatter: function(params) {
                                    return params.value > 0 ? 'Bs. ' + params.value.toFixed(0) : '';
                                },
                                fontSize: 10,
                                color: '#009DC4'
                            },
                            barMaxWidth: 60
                        },
                        {
                            name: 'Ingresos',
                            type: 'bar',
                            data: ingresos,
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                        offset: 0,
                                        color: '#4CAF50'
                                    },
                                    {
                                        offset: 1,
                                        color: '#45A049'
                                    }
                                ]),
                                borderRadius: [6, 6, 0, 0]
                            },
                            label: {
                                show: true,
                                position: 'top',
                                formatter: function(params) {
                                    return params.value > 0 ? 'Bs. ' + params.value.toFixed(0) : '';
                                },
                                fontSize: 10,
                                color: '#009DC4'
                            },
                            barMaxWidth: 60
                        },
                        {
                            name: 'Beneficio Neto',
                            type: 'line',
                            data: beneficios,
                            lineStyle: {
                                width: 3,
                                color: '#2196F3'
                            },
                            itemStyle: {
                                color: '#2196F3',
                                borderWidth: 3,
                                borderColor: '#fff'
                            },
                            symbol: 'circle',
                            symbolSize: 10,
                            label: {
                                show: true,
                                position: 'top',
                                formatter: function(params) {
                                    const signo = params.value >= 0 ? '+' : '';
                                    return signo + 'Bs. ' + params.value.toFixed(0);
                                },
                                fontSize: 11,
                                fontWeight: 'bold',
                                color: function(params) {
                                    return params.value >= 0 ? '#4CAF50' : '#f44336';
                                }
                            }
                        }
                    ]
                };

                chart.setOption(option);

                window.addEventListener('resize', function() {
                    chart.resize();
                });
            }

            function bindEvents() {
                const busquedaInput = document.getElementById('busqueda_sucursal');
                const estadoSelect = document.getElementById('estado_filtro');


                if (busquedaInput) {
                    busquedaInput.addEventListener('input', function() {
                        currentFilters.busqueda = this.value.trim();
                        cargarSucursales();
                    });
                }

                if (estadoSelect) {
                    estadoSelect.addEventListener('change', function() {
                        currentFilters.estado = this.value;
                        cargarSucursales();
                    });
                }

                const btnExportarPDF = document.getElementById('btnExportarPDF');
                if (btnExportarPDF) {
                    btnExportarPDF.addEventListener('click', exportarPDF);
                }

                const btnToggleEstadoDetalle = document.getElementById('btnToggleEstadoDetalleSucursal');
                if (btnToggleEstadoDetalle) {
                    btnToggleEstadoDetalle.addEventListener('click', function() {
                        if (sucursalActualDetalle) {
                            toggleEstado(sucursalActualDetalle.su_id, sucursalActualDetalle.su_estado);
                        }
                    });
                }
                const periodoSelect = document.getElementById('periodoGrafico');
                if (periodoSelect) {
                    periodoSelect.addEventListener('change', function() {
                        console.log('Cambiando período a:', this.value);
                        cargarGraficoCostoBeneficio(this.value);
                    });
                }
            }

            function cargarEcharts() {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js';
                script.onload = function() {
                    console.log('ECharts cargado');
                };
                document.head.appendChild(script);
            }

            async function cargarSucursales() {
                const container = document.getElementById('contenedorGrillasSucursales');

                container.innerHTML = `
            <div class="loader-grillas">
                <ion-icon name="hourglass-outline"></ion-icon>
                <p>Cargando sucursales...</p>
            </div>
        `;

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            sucursalAjax: 'listar',
                            busqueda: currentFilters.busqueda,
                            estado: currentFilters.estado
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.mensaje);
                    }

                    renderizarGrillas(data.sucursales || []);

                } catch (error) {
                    console.error('Error:', error);
                    container.innerHTML = `
                <div class="no-results">
                    <ion-icon name="alert-circle-outline"></ion-icon>
                    <p>Error al cargar sucursales</p>
                </div>
            `;
                }
            }

            function renderizarGrillas(sucursales) {
                const container = document.getElementById('contenedorGrillasSucursales');

                if (sucursales.length === 0) {
                    container.innerHTML = `
                <div class="no-results">
                    <ion-icon name="business-outline"></ion-icon>
                    <p>No se encontraron sucursales</p>
                </div>
            `;
                    return;
                }

                container.innerHTML = sucursales.map(suc => {
                    const estadoClass = parseInt(suc.su_estado) === 1 ? 'activo' : 'inactivo';
                    const cardClass = parseInt(suc.su_estado) === 1 ? '' : 'inactiva';
                    const estadoText = parseInt(suc.su_estado) === 1 ? 'Activa' : 'Inactiva';

                    const cajasAbiertas = parseInt(suc.cajas_abiertas) || 0;
                    const cajasBadgeClass = cajasAbiertas > 0 ? '' : 'sin-cajas';

                    const fechaCreacion = formatearFecha(suc.su_creado_en);

                    return `
                        <div class="sucursal-card ${cardClass}">
                            <div class="sucursal-header" onclick="SucursalesModals.verDetalle(${suc.su_id})">
                                <div class="sucursal-nombre">
                                    <ion-icon name="business-outline"></ion-icon>
                                    ${escapeHtml(suc.su_nombre)}
                                </div>
                                <span class="sucursal-estado ${estadoClass}">${estadoText}</span>
                            </div>

                            <div class="sucursal-info" onclick="SucursalesModals.verDetalle(${suc.su_id})">
                                <div class="sucursal-info-item">
                                    <ion-icon name="location-outline"></ion-icon>
                                    <span>${escapeHtml(suc.su_direccion || 'Sin dirección')}</span>
                                </div>

                                <div class="sucursal-info-item">
                                    <ion-icon name="call-outline"></ion-icon>
                                    <span>${escapeHtml(suc.su_telefono || 'Sin teléfono')}</span>
                                </div>

                                <div class="sucursal-info-item">
                                    <ion-icon name="calendar-outline"></ion-icon>
                                    <span>Creada: ${fechaCreacion}</span>
                                </div>

                                <div class="sucursal-info-item">
                                    <ion-icon name="cash-outline"></ion-icon>
                                    <span class="sucursal-cajas-badge ${cajasBadgeClass}" 
                                        onclick="event.stopPropagation(); ${cajasAbiertas > 0 ? `SucursalesModals.verCajasAbiertas(${suc.su_id}, '${escapeHtml(suc.su_nombre)}')` : 'void(0)'}">
                                        ${cajasAbiertas} ${cajasAbiertas === 1 ? 'Caja Abierta' : 'Cajas Abiertas'}
                                    </span>
                                </div>
                            </div>

                            <div class="sucursal-actions">
                                <a href="javascript:void(0)" class="btn btn-out" 
                                onclick="event.stopPropagation(); SucursalesModals.editarSucursal(${suc.su_id})">
                                    <ion-icon name="create-outline"></ion-icon> Editar
                                </a>
                                <a href="javascript:void(0)" class="btn btn-suc" 
                                onclick="event.stopPropagation(); window.location.href='<?php echo SERVER_URL; ?>inventarioLista/?sucursal=${suc.su_id}'">
                                    <ion-icon name="cube-outline"></ion-icon> Inventario
                                </a>
                            </div>
                        </div>
                    `;
                }).join('');
            }

            async function verDetalle(suId) {
                abrirModal('modalDetalleSucursal');
                document.getElementById('detalleSuId').value = suId;

                document.getElementById('tablaPersonalSucursal').innerHTML = `
            <tr><td colspan="4" style="text-align:center;">
                <ion-icon name="hourglass-outline"></ion-icon> Cargando...
            </td></tr>
        `;

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            sucursalAjax: 'detalle',
                            su_id: suId
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.mensaje);
                    }

                    sucursalActualDetalle = data.sucursal;
                    renderizarDetalle(data);

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    cerrarModalDetalle();
                }
            }

            function renderizarDetalle(data) {
                const suc = data.sucursal;

                document.getElementById('detalleNombreSucursal').textContent = suc.su_nombre;
                document.getElementById('detalleNombreSucursalInfo').textContent = suc.su_nombre;
                document.getElementById('detalleDireccionSucursal').textContent = suc.su_direccion || 'Sin dirección';
                document.getElementById('detalleTelefonoSucursal').textContent = suc.su_telefono || 'Sin teléfono';
                document.getElementById('detalleFechaCreacionSucursal').textContent = formatearFechaHora(suc.su_creado_en);

                const estadoHtml = parseInt(suc.su_estado) === 1 ?
                    '<span style="color:#4CAF50;font-weight:600;">Activa</span>' :
                    '<span style="color:#f44336;font-weight:600;">Inactiva</span>';
                document.getElementById('detalleEstadoSucursal').innerHTML = estadoHtml;

                document.getElementById('detalleTotalVentasSucursal').textContent = data.estadisticas.total_ventas;
                document.getElementById('detalleMontoTotalSucursal').textContent = 'Bs. ' + formatearNumero(data.estadisticas.monto_total);

                renderizarPersonal(data.personal || []);
                renderizarGrafico(data.ventas_usuarios || []);

                const btnToggle = document.getElementById('btnToggleEstadoDetalleSucursal');
                if (btnToggle) {
                    btnToggle.innerHTML = parseInt(suc.su_estado) === 1 ?
                        '<ion-icon name="power-outline"></ion-icon> Desactivar' :
                        '<ion-icon name="power-outline"></ion-icon> Activar';
                    btnToggle.className = parseInt(suc.su_estado) === 1 ? 'btn btn-war' : 'btn btn-suc';
                }
            }

            function renderizarPersonal(personal) {
                const tbody = document.getElementById('tablaPersonalSucursal');

                if (personal.length === 0) {
                    tbody.innerHTML = `
                <tr><td colspan="4" style="text-align:center;color:#999;">
                    <ion-icon name="information-circle-outline"></ion-icon> 
                    No hay personal asignado a esta sucursal
                </td></tr>
            `;
                    return;
                }

                tbody.innerHTML = personal.map(p => {
                    const nombreCompleto = `${p.us_nombres} ${p.us_apellido_paterno} ${p.us_apellido_materno}`.trim();
                    const rolNombre = p.rol_nombre || 'Sin rol';
                    const estadoHtml = parseInt(p.us_estado) === 1 ?
                        '<span style="color:#4CAF50;font-weight:600;">Activo</span>' :
                        '<span style="color:#f44336;font-weight:600;">Inactivo</span>';

                    return `
                <tr>
                    <td>${escapeHtml(nombreCompleto)}</td>
                    <td>${escapeHtml(p.us_username)}</td>
                    <td>${escapeHtml(rolNombre)}</td>
                    <td>${estadoHtml}</td>
                </tr>
            `;
                }).join('');
            }

            function renderizarGrafico(ventasUsuarios) {
                if (typeof echarts === 'undefined') {
                    console.warn('ECharts no está cargado');
                    return;
                }

                const container = document.getElementById('graficoVentasSucursal');
                if (!container) return;

                const chart = echarts.init(container);

                if (!ventasUsuarios || ventasUsuarios.length === 0) {
                    chart.setOption({
                        title: {
                            text: 'Sin datos de ventas',
                            left: 'center',
                            top: 'middle',
                            textStyle: {
                                color: '#999',
                                fontSize: 14
                            }
                        }
                    });
                    return;
                }

                const datos = ventasUsuarios.map(v => ({
                    name: v.usuario,
                    value: parseFloat(v.total_ventas)
                }));

                const colores = ['#1976D2', '#4CAF50', '#FF9800', '#F44336', '#9C27B0', '#00BCD4', '#FFEB3B', '#795548'];

                const option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: function(params) {
                            return `<strong>${params.name}</strong><br/>
                            Ventas: ${params.value}<br/>
                            Monto: Bs. ${params.data.monto}<br/>
                            Porcentaje: ${params.percent.toFixed(1)}%`;
                        }
                    },
                    legend: {
                        orient: 'vertical',
                        right: 10,
                        top: 'center',
                        textStyle: {
                            fontSize: 11
                        },
                        formatter: function(name) {
                            const item = ventasUsuarios.find(v => v.usuario === name);
                            return `${name} (${item.total_ventas})`;
                        }
                    },
                    series: [{
                        name: 'Ventas por Usuario',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        center: ['35%', '50%'],
                        avoidLabelOverlap: true,
                        itemStyle: {
                            borderRadius: 8,
                            borderColor: '#fff',
                            borderWidth: 2
                        },
                        label: {
                            show: true,
                            position: 'outside',
                            formatter: '{b}: {c}',
                            fontSize: 10
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: 12,
                                fontWeight: 'bold'
                            },
                            itemStyle: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowColor: 'rgba(0, 0, 0, 0.5)'
                            }
                        },
                        labelLine: {
                            show: true,
                            length: 15,
                            length2: 10
                        },
                        data: datos.map((item, index) => {
                            const usuario = ventasUsuarios.find(v => v.usuario === item.name);
                            return {
                                ...item,
                                monto: formatearNumero(usuario.monto_total),
                                itemStyle: {
                                    color: colores[index % colores.length]
                                }
                            };
                        }),
                        animationType: 'scale',
                        animationEasing: 'elasticOut',
                        animationDelay: function(idx) {
                            return idx * 50;
                        }
                    }]
                };

                chart.setOption(option);

                window.addEventListener('resize', function() {
                    chart.resize();
                });
            }


            async function verCajasAbiertas(suId, nombreSucursal) {
                document.getElementById('modalCajasNombreSucursal').textContent = nombreSucursal;
                document.getElementById('modalCajasSuId').value = suId;

                abrirModal('modalCajasAbiertas');

                document.getElementById('tablaCajasAbiertas').innerHTML = `
            <tr><td colspan="8" style="text-align:center;">
                <ion-icon name="hourglass-outline"></ion-icon> Cargando cajas...
            </td></tr>
        `;

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            sucursalAjax: 'cajas_abiertas',
                            su_id: suId
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.mensaje);
                    }

                    renderizarCajas(data.cajas || []);
                    cargarUsuariosCajas(data.usuarios || []);

                } catch (error) {
                    console.error('Error:', error);
                    document.getElementById('tablaCajasAbiertas').innerHTML = `
                <tr><td colspan="8" style="text-align:center;color:red;">
                    Error al cargar cajas
                </td></tr>
            `;
                }
            }

            function renderizarCajas(cajas) {
                const tbody = document.getElementById('tablaCajasAbiertas');

                if (cajas.length === 0) {
                    tbody.innerHTML = `
                <tr><td colspan="8" style="text-align:center;color:#999;">
                    <ion-icon name="information-circle-outline"></ion-icon> 
                    No hay cajas abiertas en esta sucursal
                </td></tr>
            `;
                    return;
                }

                tbody.innerHTML = cajas.map((caja, index) => {
                    const nombreUsuario = `${caja.us_nombres || ''} ${caja.us_apellido_paterno || ''} ${caja.us_apellido_materno || ''}`.trim();
                    const tiempoAbierta = calcularTiempoAbierta(caja.caja_creado_en);
                    const fechaApertura = formatearFechaHora(caja.caja_creado_en);

                    return `
                <tr class="tr-click" onclick="window.location.href='<?php echo SERVER_URL; ?>cajaHistorialLista/?select2=${caja.us_id}'">
                    <td>
                        <div class="td-main">${escapeHtml(caja.caja_nombre)}</div>
                        <div class="td-sub">
                            <ion-icon name="person-outline"></ion-icon> ${escapeHtml(nombreUsuario)}
                        </div>
                        <div class="td-meta">
                            <ion-icon name="calendar-outline"></ion-icon> ${fechaApertura}
                            ${caja.caja_observacion ? ` | <ion-icon name="alert-circle-outline"></ion-icon> ${escapeHtml(caja.caja_observacion)}` : ''}
                        </div>
                    </td>
                    <td>
                        <div class="td-main">Bs. ${formatearNumero(caja.caja_saldo_inicial)}</div>
                        <div class="td-sub">Saldo Inicial</div>
                    </td>
                    <td>
                        <span style="color:#ff9800;font-weight:600;">${tiempoAbierta}</span>
                    </td>
                </tr>
            `;
                }).join('');
            }

            function cargarUsuariosCajas(usuarios) {
                const select = document.getElementById('filtroUsuarioCaja');
                if (!select) return;

                select.innerHTML = '<option value="">Todos</option>';

                usuarios.forEach(usuario => {
                    const nombre = `${usuario.us_nombres} ${usuario.us_apellido_paterno}`;
                    select.innerHTML += `<option value="${usuario.us_id}">${escapeHtml(nombre)}</option>`;
                });
            }

            async function editarSucursal(suId) {
                abrirModal('modalEditarSucursal');
                document.getElementById('su_id_editar').value = suId;

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            sucursalAjax: 'obtener',
                            su_id: suId
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.mensaje);
                    }

                    document.getElementById('Nombre_edit').value = data.sucursal.su_nombre;
                    document.getElementById('Direccion_edit').value = data.sucursal.su_direccion || '';
                    document.getElementById('Telefono_edit').value = data.sucursal.su_telefono || '';

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cargar los datos', 'error');
                    cerrarModalEditar();
                }
            }

            async function toggleEstado(suId, estadoActual) {
                const nuevoEstado = parseInt(estadoActual) === 1 ? 0 : 1;
                const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';

                const result = await Swal.fire({
                    title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} sucursal?`,
                    text: `Esta acción cambiará el estado de la sucursal`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: nuevoEstado === 1 ? '#4CAF50' : '#f44336',
                    cancelButtonColor: '#999',
                    confirmButtonText: `Sí, ${accion}`,
                    cancelButtonText: 'Cancelar'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            sucursalAjax: 'toggle_estado',
                            su_id: suId,
                            nuevo_estado: nuevoEstado
                        })
                    });

                    const data = await response.json();

                    await Swal.fire({
                        title: data.Titulo,
                        text: data.texto,
                        icon: data.Tipo,
                        confirmButtonText: 'Entendido'
                    });

                    if (data.Tipo === 'success') {
                        cargarSucursales();
                        if (sucursalActualDetalle && sucursalActualDetalle.su_id === suId) {
                            cerrarModalDetalle();
                        }
                    }

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
                }
            }

            function calcularTiempoAbierta(fechaCreacion) {
                const ahora = new Date();
                const apertura = new Date(fechaCreacion);
                const diff = ahora - apertura;

                const horas = Math.floor(diff / (1000 * 60 * 60));
                const minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

                if (horas > 0) {
                    return `${horas}h ${minutos}m`;
                }
                return `${minutos}m`;
            }

            function formatearFecha(fecha) {
                if (!fecha) return '-';
                const d = new Date(fecha);
                const dia = String(d.getDate()).padStart(2, '0');
                const mes = String(d.getMonth() + 1).padStart(2, '0');
                const anio = d.getFullYear();
                return `${dia}/${mes}/${anio}`;
            }

            function formatearFechaHora(fecha) {
                if (!fecha) return '-';
                const d = new Date(fecha);
                const dia = String(d.getDate()).padStart(2, '0');
                const mes = String(d.getMonth() + 1).padStart(2, '0');
                const anio = d.getFullYear();
                const horas = String(d.getHours()).padStart(2, '0');
                const minutos = String(d.getMinutes()).padStart(2, '0');
                return `${dia}/${mes}/${anio} ${horas}:${minutos}`;
            }

            function formatearNumero(num) {
                return parseFloat(num || 0).toFixed(2);
            }

            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, m => map[m]);
            }

            function abrirModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.add('open');
                }
            }

            function cerrarModalNuevo() {
                const modal = document.getElementById('modalNuevaSucursal');
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
                }
            }

            function cerrarModalEditar() {
                const modal = document.getElementById('modalEditarSucursal');
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
                }
            }

            function cerrarModalCajas() {
                const modal = document.getElementById('modalCajasAbiertas');
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
                }
            }

            function cerrarModalDetalle() {
                const modal = document.getElementById('modalDetalleSucursal');
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
                    sucursalActualDetalle = null;
                }
            }

            function abrirModalNuevo() {
                abrirModal('modalNuevaSucursal');
            }

            function editarDesdeDetalle() {
                if (sucursalActualDetalle) {
                    const suId = sucursalActualDetalle.su_id;
                    cerrarModalDetalle();
                    setTimeout(() => editarSucursal(suId), 100);
                }
            }

            function exportarPDF() {
                const url = '<?php echo SERVER_URL; ?>ajax/sucursalAjax.php?sucursalAjax=exportar_pdf';
                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Generando PDF',
                    text: 'El reporte se está generando...',
                    timer: 2000,
                    showConfirmButton: false
                });
            }

            function buscarCajas() {
                const suId = document.getElementById('modalCajasSuId').value;
                const busqueda = document.getElementById('busquedaCaja').value;
                const usuario = document.getElementById('filtroUsuarioCaja').value;

                console.log('Buscar cajas:', {
                    suId,
                    busqueda,
                    usuario
                });
            }


            document.addEventListener('DOMContentLoaded', init);

            return {
                verDetalle,
                verCajasAbiertas,
                cerrarModalCajas,
                cerrarModalDetalle,
                cerrarModalNuevo,
                cerrarModalEditar,
                toggleEstado,
                abrirModalNuevo,
                editarSucursal,
                editarDesdeDetalle,
                buscarCajas
            };
        })();
    </script>

<?php } else { ?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb">
                <div style="text-align: center; padding: 60px;">
                    <ion-icon name="lock-closed-outline" style="font-size: 64px; color: #ccc;"></ion-icon>
                </div>
            </div>
        </div>
    </div>
<?php } ?>