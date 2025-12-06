<div class="title">
    <h2><ion-icon name="desktop-outline"></ion-icon> dashboard</h2>
</div>

<?php if ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2) {
    require_once './controllers/dashboardController.php';

    $su_id = null;
    if ($_SESSION['rol_smp'] == 2) {
        $su_id = $_SESSION['su_smp'] ?? null;
    }

    if ($_SESSION['rol_smp'] == 1) {
        $total_sucursales = dashboardController::contar_sucursales_controller();
        $total_medicamentos = dashboardController::contar_medicamentos_inventario_controller();
        $total_clientes = dashboardController::contar_clientes_controller();
        $total_usuarios = dashboardController::contar_usuarios_controller();
        $total_categorias = dashboardController::contar_categorias_controller();
    }
?>

    <!-- enlaces directos o acrotadores mas usados -->
    <?php if ($_SESSION['rol_smp'] == 1) { ?>
        <div class="direct-link">
            <div class="container-direct-links">
                <div class="direct-link-item red">
                    <a href="<?php echo SERVER_URL; ?>sucursalLista/">
                        <div class="direct-link-text">
                            <h3><?php echo $total_sucursales; ?></h3>
                            <p>sucursales</p>
                        </div>
                        <div class="direct-link-image">
                            <ion-icon name="git-branch-outline"></ion-icon>
                        </div>
                        <div class="direct-link-collapsed">
                            <h3>Abrir</h3>
                        </div>
                    </a>
                </div>
                <div class="direct-link-item orange">
                    <a href="<?php echo SERVER_URL; ?>inventarioLista/">
                        <div class="direct-link-text">
                            <h3><?php echo $total_medicamentos; ?></h3>
                            <p>Inventario</p>
                        </div>
                        <div class="direct-link-image">
                            <ion-icon name="cube"></ion-icon>
                        </div>
                        <div class="direct-link-collapsed">
                            <h3>Abrir</h3>
                        </div>
                    </a>
                </div>
                <div class="direct-link-item green">
                    <a href="<?php echo SERVER_URL; ?>clienteLista/">
                        <div class="direct-link-text">
                            <h3><?php echo $total_clientes; ?></h3>
                            <p>clientes</p>
                        </div>
                        <div class="direct-link-image">
                            <ion-icon name="people-outline"></ion-icon>
                        </div>
                        <div class="direct-link-collapsed">
                            <h3>Abrir</h3>
                        </div>
                    </a>
                </div>
                <div class="direct-link-item blue">
                    <a href="<?php echo SERVER_URL; ?>usuarioLista/">
                        <div class="direct-link-text">
                            <h3><?php echo $total_usuarios; ?></h3>
                            <p>usuario</p>
                        </div>
                        <div class="direct-link-image">
                            <ion-icon name="person-outline"></ion-icon>
                        </div>
                        <div class="direct-link-collapsed">
                            <h3>Abrir</h3>
                        </div>
                    </a>
                </div>
                <div class="direct-link-item blue">
                    <a href="<?php echo SERVER_URL; ?>categoriaLista/">
                        <div class="direct-link-text">
                            <h3>
                                <?php echo $total_categorias; ?>
                            </h3>
                            <p>categorias</p>
                        </div>
                        <div class="direct-link-image">
                            <ion-icon name="apps-outline"></ion-icon>
                        </div>
                        <div class="direct-link-collapsed">
                            <h3>Abrir</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    <?php } ?>

    <!-- tabla de fechas de vencimiento -->
    <div class="sub-title">
        <h2>proximas fechas de vencimiento</h2>
    </div>
    <div class="res-compras">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Producto</th>
                        <th>Lote</th>
                        <th>Unidades</th>
                        <th>Sucursal</th>
                        <th>Fecha Vencimiento</th>
                        <th>Días Restantes</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $proximos_vencimientos = dashboardController::obtener_proximos_vencimientos_controller($su_id);
                    if (empty($proximos_vencimientos)) {
                        echo "<tr><td colspan='8' style='text-align: center;'>No hay productos próximos a vencer</td></tr>";
                    }
                    foreach ($proximos_vencimientos as $index => $vencimiento):
                        $fecha_vencimiento = new DateTime($vencimiento['lm_fecha_vencimiento']);
                        $hoy = new DateTime();
                        $dias_restantes = $fecha_vencimiento->diff($hoy)->days;
                    ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $vencimiento['med_nombre_quimico']; ?></td>
                            <td><?php echo $vencimiento['lm_numero_lote']; ?></td>
                            <td><?php echo $vencimiento['lm_cant_actual_unidades']; ?></td>
                            <td><?php echo $vencimiento['su_nombre']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($vencimiento['lm_fecha_vencimiento'])); ?></td>
                            <td><?php echo $dias_restantes; ?> días</td>
                            <td><span class="estate" style="color: #ffa500; font-weight: bold;">PRÓXIMO</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de pastel de vencimientos -->
    <div class="sub-title">
        <h2>resumen de vencimientos</h2>
    </div>
    <div class="ingresos-egresos-barras">
        <div class="graphyc-container">
            <div id="chartVencimientos" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <!-- tabla de stock minimo -->
    <div class="sub-title">
        <h2>productos con stock minimo</h2>
    </div>
    <div class="res-compras">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Deficiencia</th>
                        <th>% Disponible</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stock_minimo = dashboardController::obtener_stock_minimo_controller($su_id);
                    if (empty($stock_minimo)) {
                        echo "<tr><td colspan='8' style='text-align: center;'>No hay productos con stock mínimo</td></tr>";
                    }
                    foreach ($stock_minimo as $index => $producto):
                        $estado_color = '';
                        $estado_texto = '';
                        $diferencia = $producto['inv_minimo'] - $producto['inv_total_unidades'];
                        $porcentaje = $producto['inv_minimo'] > 0 ? round(($producto['inv_total_unidades'] / $producto['inv_minimo']) * 100, 1) : 0;
                        if ($producto['estado_stock'] === 'sin_stock') {
                            $estado_color = 'color: #d32f2f;';
                            $estado_texto = 'SIN STOCK';
                        } else {
                            $estado_color = 'color: #ffa500;';
                            $estado_texto = 'BAJO STOCK';
                        }
                    ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $producto['med_nombre_quimico']; ?></td>
                            <td><?php echo $producto['su_nombre']; ?></td>
                            <td><?php echo $producto['inv_total_unidades']; ?></td>
                            <td><?php echo $producto['inv_minimo']; ?></td>
                            <td><?php echo $diferencia; ?></td>
                            <td><?php echo $porcentaje; ?>%</td>
                            <td><span class="estate" style="<?php echo $estado_color; ?> font-weight: bold;"><?php echo $estado_texto; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de barras de stock minimo -->
    <div class="sub-title">
        <h2>grafico de stock minimo</h2>
    </div>
    <div class="ingresos-egresos-barras">
        <div class="graphyc-container">
            <div id="chartStockMinimo" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <!-- tabla de productos mas vendidos -->
    <div class="sub-title">
        <h2>productos mas vendidos</h2>
    </div>
    <div class="res-compras">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th>Cantidad Vendida</th>
                        <th>Ingreso Total</th>
                        <th>Precio Promedio</th>
                        <th>Posición</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $productos_vendidos = dashboardController::obtener_productos_mas_vendidos_controller($su_id);
                    if (empty($productos_vendidos)) {
                        echo "<tr><td colspan='7' style='text-align: center;'>No hay datos de ventas</td></tr>";
                    }
                    foreach ($productos_vendidos as $index => $producto):
                        $promedio = $producto['cantidad_vendida'] > 0 ? $producto['total_vendido'] / $producto['cantidad_vendida'] : 0;
                        $posicion = '';
                        if ($index == 0) {
                            $posicion = '🥇 1°';
                        } elseif ($index == 1) {
                            $posicion = '🥈 2°';
                        } elseif ($index == 2) {
                            $posicion = '🥉 3°';
                        } else {
                            $posicion = ($index + 1) . '°';
                        }
                    ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $producto['med_nombre_quimico']; ?></td>
                            <td><?php echo $producto['su_nombre']; ?></td>
                            <td><?php echo $producto['cantidad_vendida']; ?></td>
                            <td><?php echo number_format($producto['total_vendido'], 2); ?> Bs</td>
                            <td><?php echo number_format($promedio, 2); ?> Bs</td>
                            <td><?php echo $posicion; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de barras de productos mas vendidos -->
    <div class="sub-title">
        <h2>grafico de productos mas vendidos</h2>
    </div>
    <div class="ingresos-egresos-barras">
        <div class="graphyc-container">
            <div id="chartProductosVendidos" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <!-- tabla de ventas mensuales -->
    <div class="sub-title">
        <h2>ventas mensuales</h2>
    </div>
    <div class="res-compras">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Período</th>
                        <th>Sucursal</th>
                        <th>Transacciones</th>
                        <th>Total Mensual</th>
                        <th>Promedio x Venta</th>
                        <th>Venta Diaria Aprox</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ventas_mensuales = dashboardController::obtener_ventas_mensuales_controller($su_id);
                    if (empty($ventas_mensuales)) {
                        echo "<tr><td colspan='7' style='text-align: center;'>No hay datos de ventas</td></tr>";
                    }
                    foreach ($ventas_mensuales as $index => $venta):
                        $promedio = $venta['cantidad_ventas'] > 0 ? $venta['total_mes'] / $venta['cantidad_ventas'] : 0;
                        $venta_diaria = $venta['total_mes'] / 30;
                    ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('M/Y', strtotime($venta['mes'] . '-01')); ?></td>
                            <td><?php echo $venta['su_nombre']; ?></td>
                            <td><?php echo $venta['cantidad_ventas']; ?></td>
                            <td><?php echo number_format($venta['total_mes'], 2); ?> Bs</td>
                            <td><?php echo number_format($promedio, 2); ?> Bs</td>
                            <td><?php echo number_format($venta_diaria, 2); ?> Bs</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de barras de ventas mensuales -->
    <div class="sub-title">
        <h2>grafico de ventas mensuales</h2>
    </div>
    <div class="ingresos-egresos-barras">
        <div class="graphyc-container">
            <div id="chartVentasMensuales" style="width: 100%; height: 400px;"></div>
        </div>
    </div>


    <!-- el script debe estar aqui y no en otro archivo  -->
    <script>
        async function loadChartVencimientos() {
            try {
                const url = '<?php echo SERVER_URL; ?>ajax/dashboardAjax.php?dashboardAjax=obtener_vencimientos_ajax';
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
                    const chart = echarts.init(document.getElementById('chartVencimientos'));
                    const option = {
                        title: {
                            text: 'Estado de Vencimientos',
                            left: 'center'
                        },
                        tooltip: {
                            trigger: 'item',
                            formatter: '{b}: {c} ({d}%)'
                        },
                        legend: {
                            orient: 'vertical',
                            left: 'left'
                        },
                        series: [{
                            name: 'Vencimientos',
                            type: 'pie',
                            radius: '50%',
                            data: [{
                                    value: data.expirados,
                                    name: 'Expirados',
                                    itemStyle: {
                                        color: '#d32f2f'
                                    }
                                },
                                {
                                    value: data.proximos,
                                    name: 'Próximos',
                                    itemStyle: {
                                        color: '#ffa500'
                                    }
                                },
                                {
                                    value: data.disponibles,
                                    name: 'Disponibles',
                                    itemStyle: {
                                        color: '#4caf50'
                                    }
                                }
                            ],
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            }
                        }]
                    };
                    chart.setOption(option);
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
                const url = '<?php echo SERVER_URL; ?>ajax/dashboardAjax.php?dashboardAjax=obtener_stock_minimo_ajax';
                const response = await fetch(url);

                if (!response.ok) return console.error('HTTP error:', response.status);

                const result = await response.json();

                if (!result.success) {
                    console.error('Failed to load stock minimo data:', result.message);
                    return;
                }

                const data = result.data;
                const chartContainer = document.getElementById('chartStockMinimo');
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
                            fontWeight: 'bold'
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
                            color: '#EB3434'
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
                                color: '#ffa500'
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
                const url = '<?php echo SERVER_URL; ?>ajax/dashboardAjax.php?dashboardAjax=obtener_productos_vendidos_ajax';
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
                const url = '<?php echo SERVER_URL; ?>ajax/dashboardAjax.php?dashboardAjax=obtener_ventas_mensuales_ajax';
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

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCharts);
        } else {
            initializeCharts();
        }
    </script>

<?php } else { ?>
    <div class="sub-title" style="text-align: center; padding: 40px;">
        <h2>Acceso no autorizado</h2>
        <p>Su rol no tiene permisos para ver el dashboard</p>
    </div>
<?php } ?>