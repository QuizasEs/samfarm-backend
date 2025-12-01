
            <div class="title">
                <h1>dashboard</h1>
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
                        <a href="<?php echo SERVER_URL; ?>sucursal/">
                            <div class="direct-link-text">
                                <h3><?php echo $total_sucursales; ?></h3>
                                <p>sucursales</p>
                            </div>
                            <div class="direct-link-image">
                                <ion-icon name="medkit"></ion-icon>
                            </div>
                            <div class="direct-link-collapsed">
                                <h3>Abrir</h3>
                            </div>
                        </a>
                    </div>
                    <div class="direct-link-item orange">
                        <a href="<?php echo SERVER_URL; ?>inventario/">
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
                        <a href="<?php echo SERVER_URL; ?>clientes/">
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
                        <a href="<?php echo SERVER_URL; ?>usuario/">
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
                        <a href="<?php echo SERVER_URL; ?>categoria/">
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

            <?php } else { ?>
                <div class="sub-title" style="text-align: center; padding: 40px;">
                    <h2>Acceso no autorizado</h2>
                    <p>Su rol no tiene permisos para ver el dashboard</p>
                </div>
            <?php } ?>