<?php
/* administrador vista */
if ($_SESSION['rol_smp'] == 1) {

?>
    <div class="sidebar">
        <ul class="sidebar-content">

            <!-- DASHBOARD -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="rocket-outline"></ion-icon>
                        <span><a href="<?php echo SERVER_URL ?>dashboard/">Dashboard</a></span>
                    </div>
                </div>
            </li>


            <!-- caja -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cart-outline"></ion-icon>
                        <span><a href="<?php echo SERVER_URL ?>caja/">Caja</a></span>
                    </div>
                </div>
            </li>
            <!-- devoluciones -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="repeat-outline"></ion-icon>
                        <span><a href="<?php echo SERVER_URL ?>devolucionLista/">Devolucion</a></span>
                    </div>
                </div>
            </li>
            <!-- CLIENTES -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="people-outline"></ion-icon>
                        <span><a href="<?php echo SERVER_URL ?>clienteLista/">Clientes</a></span>
                    </div>
                </div>
            </li>

            <!-- productos -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="medical-outline"></ion-icon>
                        <span>Medicamentos</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>medicamentoLista/">Medicamento</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>categoriaLista/">Categoria</a></li>
                </ul>
            </li>



            <!-- INVENTARIO -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="clipboard-outline"></ion-icon>
                        <span>Inventario</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>loteLista/">Lote</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>inventarioLista/">Inventario</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>compraOrden">Registrar compra</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>proveedorLista/">Proveedor</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>">Pedidos</a></li>
                </ul>
            </li>

            <!-- Reportes / facturas -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="document-outline"></ion-icon>
                        <span>Reportes</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">

                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>ventasHistorialLista/">Historial de ventas</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>cajaHistorialLista/">Hsitorial de caja</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>comprasHistorialLista/">Historial de compras</a></li>
                </ul>
            </li>

            <!-- ALERTAS -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="alert-circle-outline"></ion-icon>
                        <span>Alertas</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Vencimientos</a></li>
                    <li class="sub-link"><a href="#">Stock mínimo</a></li>
                    <li class="sub-link"><a href="#">Pendientes</a></li>
                    <li class="sub-link"><a href="#">Errores del sistema</a></li>
                </ul>
            </li>
            <!-- configuracion -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="construct-outline"></ion-icon>
                        <span>Configuracion</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL; ?>usuarioLista/">Usuarios</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL; ?>sucursalLista">sucursales</a></li>
                </ul>
            </li>






            <!-- CONFIGURACIÓN -->
            <!--             <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="settings"></ion-icon>
                        <span>Configuración</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Datos de empresa</a></li>
                    <li class="sub-link"><a href="#">Impuestos y moneda</a></li>
                    <li class="sub-link"><a href="#">Opciones de impresión</a></li>
                    <li class="sub-link"><a href="#">Backup</a></li>
                    <li class="sub-link"><a href="#">Seguridad</a></li>
                </ul>
            </li> -->
            <!-- perfil -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="person-outline"></ion-icon>
                        <span><a href="<?php echo SERVER_URL . "usuarioActualizar/" . $lc->encryption($_SESSION['id_smp']); ?>">Perfil</a></span>
                    </div>
                </div>
            </li>

        </ul>
    </div>



    <!-- gerente vista -->
<?php } elseif ($_SESSION['rol_smp'] == 2) { ?>

    <div class="sidebar">
        <ul class="sidebar-content">
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="speedometer"></ion-icon>
                        <a href="#">Dashboard</a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cart"></ion-icon>
                        <span>Ventas</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Nueva venta</a></li>
                    <li class="sub-link"><a href="#">Historial de ventas</a></li>
                    <li class="sub-link"><a href="#">Ventas por cliente</a></li>
                    <li class="sub-link"><a href="#">Ventas por doctor</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cube"></ion-icon>
                        <span>Inventario</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>medicamentoLista/">Medicamentos</a></li>
                    <li class="sub-link"><a href="#">Lotes y vencimientos</a></li>
                    <li class="sub-link"><a href="#">Movimientos de stock</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="briefcase"></ion-icon>
                        <span>Compras</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Registrar compra</a></li>
                    <li class="sub-link"><a href="#">Proveedores</a></li>
                    <li class="sub-link"><a href="#">Historial de compras</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="stats"></ion-icon>
                        <span>Reportes</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Reporte de ventas</a></li>
                    <li class="sub-link"><a href="#">Reporte de stock</a></li>
                    <li class="sub-link"><a href="#">Reporte de finanzas</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="notifications"></ion-icon>
                        <a href="#">Alertas</a>
                    </div>
                </div>
            </li>
            <!-- perfil -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="contact"></ion-icon>
                        <span><a href="<?php echo SERVER_URL . "usuarioActualizar/" . $lc->encryption($_SESSION['id_smp']); ?>">Perfil</a></span>
                    </div>
                </div>
            </li>
        </ul>
    </div>


    <!-- usuario caja vista -->
<?php } elseif ($_SESSION['rol_smp'] == 3) { ?>

    <div class="sidebar">
        <ul class="sidebar-content">
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="home"></ion-icon>
                        <a href="#">Inicio</a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cash"></ion-icon>
                        <span>Caja</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Apertura de caja</a></li>
                    <li class="sub-link"><a href="#">Movimientos</a></li>
                    <li class="sub-link"><a href="#">Arqueo</a></li>
                    <li class="sub-link"><a href="#">Cierre diario</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cart"></ion-icon>
                        <span>Ventas</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Nueva venta</a></li>
                    <li class="sub-link"><a href="#">Buscar producto</a></li>
                    <li class="sub-link"><a href="#">Detalle de ticket</a></li>
                    <li class="sub-link"><a href="#">Historial de ventas</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="people"></ion-icon>
                        <span>Clientes</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Registrar cliente</a></li>
                    <li class="sub-link"><a href="#">Editar cliente</a></li>
                    <li class="sub-link"><a href="#">Historial de compras</a></li>
                </ul>
            </li>
            <!-- perfil -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="contact"></ion-icon>
                        <span><a href="<?php echo SERVER_URL . "usuarioActualizar/" . $lc->encryption($_SESSION['id_smp']); ?>">Perfil</a></span>
                    </div>
                </div>
            </li>
        </ul>
    </div>



    <!-- nada -->
<?php } else { ?>
    <div class="sidebar">
        <ul class="sidebar-content">
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="home"></ion-icon>
                        <a href="#">salir</a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
<?php } ?>