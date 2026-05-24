async function loadChartVencimientos() {
    try {
        const url = getBaseURL() + 'ajax/dashboardAjax.php?dashboardAjax=obtener_vencimientos_ajax';
        console.log('Fetching:', url);
        const response = await fetch(url);

        if (!response.ok) {
            console.error('HTTP error:', response.status, response.statusText);
            return;
        }

        const result = await response.json();
        console.log('Vencimientos response:', result);

        if (result.success) {
            const data = result.data;
            const chartDom = document.getElementById('chartVencimientos');

            // Asegurarse de destruir cualquier instancia previa
            if (echarts.getInstanceByDom(chartDom)) {
                echarts.dispose(chartDom);
            }

            const chart = echarts.init(chartDom);

            const option = {
                title: {
                    text: 'Próximas Fechas de Vencimiento',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: ['Expirados', 'Próximos', 'Disponibles'],
                    axisLabel: {
                        interval: 0
                    }
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    name: 'Cantidad',
                    type: 'bar',
                    data: [{
                            value: data.expirados,
                            itemStyle: {
                                color: '#d32f2f'
                            }
                        },
                        {
                            value: data.proximos,
                            itemStyle: {
                                color: '#ffa500'
                            }
                        },
                        {
                            value: data.disponibles,
                            itemStyle: {
                                color: '#4caf50'
                            }
                        }
                    ]
                }]
            };
            chart.setOption(option, true);
            window.addEventListener('resize', () => chart.resize());
        } else {
            console.error('Failed to load vencimientos data:', result.message);
        }
    } catch (error) {
        console.error('Error loading vencimientos chart:', error);
    }
}

async function loadChartStockMinimo() {
    try {
        const url = getBaseURL() + 'ajax/dashboardAjax.php?dashboardAjax=obtener_stock_minimo_ajax';
        const response = await fetch(url);

        if (!response.ok) return console.error('HTTP error:', response.status);

        const result = await response.json();

        if (!result.success) {
            console.error('Failed to load stock minimo data:', result.message);
            return;
        }

        const data = result.data;
        const chartContainer = document.getElementById('chartStockMinimo');

        // Asegurarse de destruir cualquier instancia previa
        if (echarts.getInstanceByDom(chartContainer)) {
            echarts.dispose(chartContainer);
        }

        const chart = echarts.init(chartContainer);

        const productos = data.map(item => item.med_nombre_quimico);
        const stocks = data.map(item => parseInt(item.inv_total_unidades));
        const minimos = data.map(item => parseInt(item.inv_minimo));

        // 🔥 Detectar contenedor pequeño
        const isSmall = chartContainer.offsetWidth < 450;

        const option = {
            title: {
                text: 'Stock Actual vs Stock Mínimo',
                left: 'center',
                textStyle: {
                    fontSize: isSmall ? 14 : 18,
                    fontWeight: 'bold',
                    
                }
            },

            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },

            legend: {
                top: 25,
                textStyle: {
                    fontSize: isSmall ? 11 : 14,
                    fontWeight:'bold',
                    color: '#919191'

                }
            },

            grid: {
                left: isSmall ? '6%' : '3%',
                right: '4%',
                bottom: isSmall ? '12%' : '5%',
                containLabel: true
            },

            xAxis: {
                type: 'category',
                data: productos,
                axisLabel: {
                    rotate: isSmall ? 65 : 45,
                    fontSize: isSmall ? 10 : 12,
                    interval: 0,
                    color: '#333'
                }
            },

            yAxis: {
                type: 'value',
                axisLabel: {
                    fontSize: isSmall ? 10 : 12
                },
                splitLine: {
                    lineStyle: {
                        color: '#e0e0e0',
                        type: 'dashed'
                    }
                }
            },

            series: [{
                    name: 'Stock Actual',
                    type: 'bar',
                    data: stocks,
                    itemStyle: {
                        color: '#3CBF24'
                    },
                    barMaxWidth: 40
                },
                {
                    name: 'Stock Mínimo',
                    type: 'bar',
                    data: minimos,
                    itemStyle: {
                        color: '#d32f2f'
                    },
                    barMaxWidth: 40
                }
            ]
        };

        chart.setOption(option);

        // 🔥 Responsividad real con ResizeObserver
        if (!window.__chartStockMinimoObserverAdded) {
            const ro = new ResizeObserver(() => chart.resize());
            ro.observe(chartContainer);
            window.__chartStockMinimoObserverAdded = true;
        }

        window.addEventListener('resize', () => chart.resize());

    } catch (error) {
        console.error('Error loading stock minimo chart:', error);
    }
}


async function loadChartProductosVendidos() {
    try {
        const url = getBaseURL() + 'ajax/dashboardAjax.php?dashboardAjax=obtener_productos_vendidos_ajax';
        console.log('Fetching:', url);
        const response = await fetch(url);

        if (!response.ok) {
            console.error('HTTP error:', response.status, response.statusText);
            return;
        }

        const result = await response.json();
        console.log('Productos vendidos response:', result);

        if (result.success) {
            const data = result.data;
            const chart = echarts.init(document.getElementById('chartProductosVendidos'));

            const productos = data.map(item => item.med_nombre_quimico);
            const cantidades = data.map(item => parseInt(item.cantidad_vendida));

            const option = {
                title: {
                    text: 'Productos Más Vendidos',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'shadow'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: productos,
                    axisLabel: {
                        rotate: 45,
                        interval: 0
                    }
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    name: 'Cantidad Vendida',
                    type: 'bar',
                    data: cantidades,
                    itemStyle: {
                        color: '#2196f3'
                    }
                }]
            };
            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        } else {
            console.error('Failed to load productos vendidos data:', result.message);
        }
    } catch (error) {
        console.error('Error loading productos vendidos chart:', error);
    }
}

async function loadChartVentasMensuales() {
    try {
        const url = getBaseURL() + 'ajax/dashboardAjax.php?dashboardAjax=obtener_ventas_mensuales_ajax';
        console.log('Fetching:', url);
        const response = await fetch(url);

        if (!response.ok) {
            console.error('HTTP error:', response.status, response.statusText);
            return;
        }

        const result = await response.json();
        console.log('Ventas mensuales response:', result);

        if (result.success) {
            const data = result.data;
            const chart = echarts.init(document.getElementById('chartVentasMensuales'));

            const meses = data.map(item => {
                const date = new Date(item.mes + '-01');
                return date.toLocaleDateString('es-ES', {
                    month: 'short',
                    year: 'numeric'
                });
            });
            const totales = data.map(item => parseFloat(item.total_mes));

            const option = {
                title: {
                    text: 'Ventas Mensuales',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                grid: {
                    left: '3%',
                    right: '4%',
                    bottom: '3%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: meses
                },
                yAxis: {
                    type: 'value'
                },
                series: [{
                    name: 'Total Ventas (Bs)',
                    type: 'line',
                    data: totales,
                    smooth: true,
                    itemStyle: {
                        color: '#4caf50'
                    },
                    areaStyle: {
                        color: 'rgba(76, 175, 80, 0.3)'
                    }
                }]
            };
            chart.setOption(option);
            window.addEventListener('resize', () => chart.resize());
        } else {
            console.error('Failed to load ventas mensuales data:', result.message);
        }
    } catch (error) {
        console.error('Error loading ventas mensuales chart:', error);
    }
}

function initializeCharts() {
    if (typeof echarts === 'undefined') {
        console.warn('ECharts not loaded, retrying in 500ms...');
        setTimeout(initializeCharts, 500);
        return;
    }
    loadChartVencimientos();
    loadChartStockMinimo();
    loadChartProductosVendidos();
    loadChartVentasMensuales();
}

function getBaseURL() {
    const serverUrl = document.documentElement.dataset.serverUrl;
    if (serverUrl) {
        return serverUrl.replace('ajax/notificacionesAjax.php', '');
    }
    const path = window.location.pathname;
    const match = path.match(/^\/([^\/]+)\//);
    return match ? "/" + match[1] + "/" : "/";
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCharts);
} else {
    initializeCharts();
}
