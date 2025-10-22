
            <div class="title">
                <h1>dashboard</h1>
            </div>

            <?php if ($_SESSION['rol_smp'] == 1) {?>

            <!-- enlaces directos o acrotadores mas usados -->
            <div class="direct-link">
                <div class="container-direct-links">
                    <div class="direct-link-item red">
                        <a href="#">
                            <div class="direct-link-text">
                                <h3>14</h3>
                                <p>Cajas</p>
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
                        <a href="#">
                            <div class="direct-link-text">
                                <h3>1300</h3>
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
                        <a href="#">
                            <div class="direct-link-text">
                                <h3>14</h3>
                                <p>Reportes</p>
                            </div>
                            <div class="direct-link-image">
                                <ion-icon name="clipboard"></ion-icon>
                            </div>
                            <div class="direct-link-collapsed">
                                <h3>Abrir</h3>
                            </div>
                        </a>
                    </div>
                    <div class="direct-link-item blue">
                        <a href="#">
                            <div class="direct-link-text">
                                <h3>14</h3>
                                <p>Sucursales</p>
                            </div>
                            <div class="direct-link-image">
                                <ion-icon name="contacts"></ion-icon>
                            </div>
                            <div class="direct-link-collapsed">
                                <h3>Abrir</h3>
                            </div>
                        </a>
                    </div>
                    <div class="direct-link-item blue">
                        <a href="#">
                            <div class="direct-link-text">
                                <h3><!-- mostramos la cantidad de usuarios -->
                                    <?php 
                                        $total=$ins_usuario->data_user_controller("Conteo", 0); 
                                        echo $total->rowCount();
                                    ?>
                                </h3>
                                <p>Usuarios</p>
                            </div>
                            <div class="direct-link-image">
                                <ion-icon name="contacts"></ion-icon>
                            </div>
                            <div class="direct-link-collapsed">
                                <h3>Abrir</h3>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <?php }?>

            <!-- resumende las compras del año actual -->
            <div class="sub-title">
                <h2>resumen de compras diarias</h2>
            </div>
            <div class="res-compras">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <!-- resumende ventas del año actual -->
            <div class="sub-title">
                <h2>resumen de ventas diarias</h2>
            </div>
            <div class="res-ventas">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <!-- resumen de ingresos egresos -->
            <div class="sub-title">
                <h2>ingresos y egresos</h2>
            </div>
            <div class="res-ingresos-egresos">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>cofarm</td>
                                <td>10bs</td>
                                <td>500 unidades</td>
                                <td><span class="estate"> Disponible</span></td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <!-- ingreso egresso grafica de barras -->
            <div class="sub-title">
                <h2>grafica de barras ingresos y egresos</h2>
            </div>
            <div class="ingresos-egresos-barras">
                <div class="graphyc-container">
                    <div id="graphyc" style="width: 500px;height:400px; min-width: 200px;"></div>

                </div>
            </div>