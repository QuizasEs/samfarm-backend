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
                        <ion-icon name="speedometer"></ion-icon>
                        <span>Dashboard</span>
                    </div>
                </div>
            </li>

            <!-- CAJA -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cash"></ion-icon>
                        <span>Caja</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Apertura</a></li>
                    <li class="sub-link"><a href="#">Movimientos</a></li>
                    <li class="sub-link"><a href="#">Arqueo</a></li>
                    <li class="sub-link"><a href="#">Cierre diario</a></li>
                </ul>
            </li>

            <!-- VENTAS -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cart"></ion-icon>
                        <span>Ventas</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Nueva venta</a></li>
                    <li class="sub-link"><a href="#">Buscar producto</a></li>
                    <li class="sub-link"><a href="#">Detalle de ticket</a></li>
                    <li class="sub-link"><a href="#">Historial de ventas</a></li>
                    <li class="sub-link"><a href="#">Ventas por cliente</a></li>
                    <li class="sub-link"><a href="#">Ventas por doctor</a></li>
                </ul>
            </li>

            <!-- CLIENTES -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="people"></ion-icon>
                        <span>Clientes</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Registrar cliente</a></li>
                    <li class="sub-link"><a href="#">Editar cliente</a></li>
                    <li class="sub-link"><a href="#">Historial de compras</a></li>
                </ul>
            </li>

            <!-- INVENTARIO -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="cube"></ion-icon>
                        <span>Inventario</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Medicamentos</a></li>
                    <li class="sub-link"><a href="#">Lotes y vencimientos</a></li>
                    <li class="sub-link"><a href="#">Movimientos de stock</a></li>
                    <li class="sub-link"><a href="#">Categorías</a></li>
                    <li class="sub-link"><a href="#">Proveedores</a></li>
                </ul>
            </li>

            <!-- COMPRAS -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="briefcase"></ion-icon>
                        <span>Compras</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Registrar compra</a></li>
                    <li class="sub-link"><a href="#">Historial de compras</a></li>
                    <li class="sub-link"><a href="#">Recepción de productos</a></li>
                    <li class="sub-link"><a href="#">Gestión de proveedores</a></li>
                </ul>
            </li>

            <!-- USUARIOS -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="contacts"></ion-icon>
                        <span>Usuarios</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL; ?>usuarioRegistro/">Registrar usuario</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL;?>usuarioLista/">Lista de usuarios</a></li>
                    <li class="sub-link"><a href="#">Actividad del usuario</a></li>
                </ul>
            </li>

            <!-- REPORTES -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="stats"></ion-icon>
                        <span>Reportes</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Ventas</a></li>
                    <li class="sub-link"><a href="#">Stock</a></li>
                    <li class="sub-link"><a href="#">Finanzas</a></li>
                    <li class="sub-link"><a href="#">Empleados</a></li>
                    <li class="sub-link"><a href="#">Auditorías</a></li>
                </ul>
            </li>

            <!-- ALERTAS -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="notifications"></ion-icon>
                        <span>Alertas</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Vencimientos</a></li>
                    <li class="sub-link"><a href="#">Stock mínimo</a></li>
                    <li class="sub-link"><a href="#">Pendientes</a></li>
                    <li class="sub-link"><a href="#">Errores del sistema</a></li>
                </ul>
            </li>

            <!-- AUDITORIA -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="document"></ion-icon>
                        <span>Auditoría</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Acciones de usuario</a></li>
                    <li class="sub-link"><a href="#">Modificaciones BD</a></li>
                    <li class="sub-link"><a href="#">Inicios de sesión</a></li>
                    <li class="sub-link"><a href="#">Eliminaciones</a></li>
                </ul>
            </li>

            <!-- CONFIGURACIÓN -->
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="settings"></ion-icon>
                        <span>Configuración</span>
                    </div>
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Datos de empresa</a></li>
                    <li class="sub-link"><a href="#">Impuestos y moneda</a></li>
                    <li class="sub-link"><a href="#">Opciones de impresión</a></li>
                    <li class="sub-link"><a href="#">Backup</a></li>
                    <li class="sub-link"><a href="#">Seguridad</a></li>
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
                    <ion-icon name="arrow-dropdown"></ion-icon>
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
                    <ion-icon name="arrow-dropdown"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="#">Medicamentos</a></li>
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
                    <ion-icon name="arrow-dropdown"></ion-icon>
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
                    <ion-icon name="arrow-dropdown"></ion-icon>
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
                    <ion-icon name="arrow-dropdown"></ion-icon>
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
                    <ion-icon name="arrow-dropdown"></ion-icon>
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
                    <ion-icon name="arrow-dropdown"></ion-icon>
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