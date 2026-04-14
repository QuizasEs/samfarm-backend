
<?php if (in_array($_SESSION['rol_smp'], [1, 2, 3 ])) {
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
        <h2 class="th2">Enlaces directos</h2>
        <div class="">
            <div class="card mb16">
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

    <?php if (in_array($_SESSION['rol_smp'], [1, 2])) { ?>
    <!-- tabla de fechas de vencimiento -->
    <h2 class="th2">Próximas fechas de vencimiento</h2>
    <div class="card mb16">
        <div class="tw table-detail">
            <table>
                <thead>
                    <tr>
                        <th style="width:45%">Producto</th>
                        <th style="width:25%">Sucursal</th>
                        <th style="width:15%">Unidades</th>
                        <th style="width:15%">Vencimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $proximos_vencimientos = dashboardController::obtener_proximos_vencimientos_controller($su_id);
                    if (empty($proximos_vencimientos)) {
                        echo "<tr><td colspan='4' style='text-align: center;'>No hay productos próximos a vencer</td></tr>";
                    }
                    foreach ($proximos_vencimientos as $index => $vencimiento):
                        $fecha_vencimiento = new DateTime($vencimiento['lm_fecha_vencimiento']);
                        $hoy = new DateTime();
                        $dias_restantes = $fecha_vencimiento->diff($hoy)->days;
                    ?>
                        <tr>
                            <td>
                                <div class="td-main"><?php echo $vencimiento['med_nombre_quimico']; ?></div>
                                <div class="td-sub">Lote: <?php echo $vencimiento['lm_numero_lote']; ?> · <?php echo $dias_restantes; ?> días restantes</div>
                                <div class="td-meta"><span class="badge bwar bsm">Vencimiento próximo</span></div>
                            </td>
                            <td><div class="td-main"><?php echo $vencimiento['su_nombre']; ?></div></td>
                            <td><div class="td-main"><?php echo $vencimiento['lm_cant_actual_unidades']; ?> u.</div></td>
                            <td><div class="td-main"><?php echo date('d/m/Y', strtotime($vencimiento['lm_fecha_vencimiento'])); ?></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de pastel de vencimientos -->
    <h2 class="th2">Resumen de vencimientos</h2>
    <div class="card mb16">
        <div class="cb">
            <div id="chartVencimientos" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <!-- tabla de stock minimo -->
    <h2 class="th2">Productos con stock mínimo</h2>
    <div class="card mb16">
        <div class="tw table-detail">
            <table>
                <thead>
                    <tr>
                        <th style="width:40%">Producto</th>
                        <th style="width:25%">Sucursal</th>
                        <th style="width:20%">Stock</th>
                        <th style="width:15%">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stock_minimo = dashboardController::obtener_stock_minimo_controller($su_id);
                    if (empty($stock_minimo)) {
                        echo "<tr><td colspan='4' style='text-align: center;'>No hay productos con stock mínimo</td></tr>";
                    }
                    foreach ($stock_minimo as $index => $producto):
                        $estado_color = '';
                        $estado_texto = '';
                        $badge_class = '';
                        $diferencia = $producto['inv_minimo'] - $producto['inv_total_unidades'];
                        $porcentaje = $producto['inv_minimo'] > 0 ? round(($producto['inv_total_unidades'] / $producto['inv_minimo']) * 100, 1) : 0;
                        if ($producto['estado_stock'] === 'sin_stock') {
                            $estado_color = 'var(--btn-danger)';
                            $estado_texto = 'Sin Stock';
                            $badge_class = 'bdan';
                        } else {
                            $estado_color = 'var(--btn-warning)';
                            $estado_texto = 'Bajo Stock';
                            $badge_class = 'bwar';
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="td-main"><?php echo $producto['med_nombre_quimico']; ?></div>
                                <div class="td-sub">Mínimo: <?php echo $producto['inv_minimo']; ?> u. · Disponible: <?php echo $porcentaje; ?>%</div>
                            </td>
                            <td><div class="td-main"><?php echo $producto['su_nombre']; ?></div></td>
                            <td><div class="td-main"><?php echo $producto['inv_total_unidades']; ?> u.</div><div class="td-sub" style="color:<?php echo $estado_color; ?>">Deficiencia: <?php echo $diferencia; ?> u.</div></td>
                            <td><span class="badge <?php echo $badge_class; ?> bsm"><?php echo $estado_texto; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de barras de stock minimo -->
    <h2 class="th2">Gráfico de stock mínimo</h2>
    <div class="card mb16">
        <div class="cb">
            <div id="chartStockMinimo" style="width: 100%; height: 400px;"></div>
        </div>
    </div>
    <?php } ?>

    <!-- tabla de productos mas vendidos -->
    <h2 class="th2">Productos más vendidos</h2>
    <div class="card mb16">
        <div class="tw table-detail">
            <table>
                <thead>
                    <tr>
                        <th style="width:40%">Producto</th>
                        <th style="width:25%">Sucursal</th>
                        <th style="width:20%">Ventas</th>
                        <th style="width:15%">Posición</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $productos_vendidos = dashboardController::obtener_productos_mas_vendidos_controller($su_id);
                    if (empty($productos_vendidos)) {
                        echo "<tr><td colspan='4' style='text-align: center;'>No hay datos de ventas</td></tr>";
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
                            <td>
                                <div class="td-main"><?php echo $producto['med_nombre_quimico']; ?></div>
                                <div class="td-sub">Total vendido: <?php echo number_format($producto['total_vendido'], 2); ?> Bs · Precio promedio: <?php echo number_format($promedio, 2); ?> Bs</div>
                            </td>
                            <td><div class="td-main"><?php echo $producto['su_nombre']; ?></div></td>
                            <td><div class="td-main"><?php echo $producto['cantidad_vendida']; ?> u.</div></td>
                            <td><div class="td-main"><?php echo $posicion; ?></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de barras de productos mas vendidos -->
    <h2 class="th2">Gráfico de productos más vendidos</h2>
    <div class="card mb16">
        <div class="cb">
            <div id="chartProductosVendidos" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <!-- tabla de ventas mensuales -->
    <h2 class="th2">Ventas mensuales</h2>
    <div class="card mb16">
        <div class="tw table-detail">
            <table>
                <thead>
                    <tr>
                        <th style="width:30%">Período</th>
                        <th style="width:25%">Sucursal</th>
                        <th style="width:20%">Transacciones</th>
                        <th style="width:25%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $ventas_mensuales = dashboardController::obtener_ventas_mensuales_controller($su_id);
                    if (empty($ventas_mensuales)) {
                        echo "<tr><td colspan='4' style='text-align: center;'>No hay datos de ventas</td></tr>";
                    }
                    foreach ($ventas_mensuales as $index => $venta):
                        $promedio = $venta['cantidad_ventas'] > 0 ? $venta['total_mes'] / $venta['cantidad_ventas'] : 0;
                        $venta_diaria = $venta['total_mes'] / 30;
                    ?>
                        <tr>
                            <td>
                                <div class="td-main"><?php echo date('M/Y', strtotime($venta['mes'] . '-01')); ?></div>
                                <div class="td-sub">Promedio x venta: <?php echo number_format($promedio, 2); ?> Bs · Venta diaria: <?php echo number_format($venta_diaria, 2); ?> Bs</div>
                            </td>
                            <td><div class="td-main"><?php echo $venta['su_nombre']; ?></div></td>
                            <td><div class="td-main"><?php echo $venta['cantidad_ventas']; ?></div></td>
                            <td><div class="td-main"><?php echo number_format($venta['total_mes'], 2); ?> Bs</div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- grafico de barras de ventas mensuales -->
    <h2 class="th2">Gráfico de ventas mensuales</h2>
    <div class="card mb16">
        <div class="cb">
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
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>