<?php
/* administrador vista */
if ($_SESSION['rol_smp'] == 1) {
    $current_view = isset($_GET['views']) ? explode("/", $_GET['views'])[0] : '';

?>
    <div class="sidebar">
        <ul class="sidebar-content">

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>dashboard/">
                            <ion-icon name="rocket-outline"></ion-icon>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>caja/">
                            <ion-icon name="storefront-outline"></ion-icon>
                            <span>Caja</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>devolucionLista/">
                            <ion-icon name="repeat-outline"></ion-icon>
                            <span>Devolucion</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>mermaRegistrar/">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            <span>Registrar Merma</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>clienteLista/">
                            <ion-icon name="people-outline"></ion-icon>
                            <span>Clientes</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>compraOrden/">
                            <ion-icon name="cart-outline"></ion-icon>
                            <span>Registrar Compra</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>preciosLista/">
                            <ion-icon name="stats-chart-outline"></ion-icon>
                            <span>Balance general</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="medical-outline"></ion-icon>
                        <span>Catalogo</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>medicamentoLista/">Medicamento</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>categoriaLista/">Categoria</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="server-outline"></ion-icon>
                        <span>Almacen</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>loteLista/">Lote</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>inventarioLista/">Inventario</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>proveedorLista/">Proveedor</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>transferirLista/">Tranferencias</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>recepcionarLista/">Recepcionar</a></li>
                </ul>
            </li>

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
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>cajaHistorialTotales/">Historial de cajas cerradas</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>comprasHistorialLista/">Historial de compras</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>transferirHistorialLista/">Movimientos</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>mermaLista/">Historial de Mermas</a></li>
                </ul>
            </li>

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
                    <li class="sub-link"><a href="<?php echo SERVER_URL; ?>sucursalLista/">sucursales</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL; ?>cajaLista/">Gestión de Cajas</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL; ?>empresaEditar/">Empresa</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL . "perfilEditar/" . $lc->encryption($_SESSION['id_smp']); ?>">
                            <ion-icon name="person-outline"></ion-icon>
                            <span>Perfil</span>
                        </a>
                    </div>
                </div>
            </li>

        </ul>
    </div>

<?php } elseif ($_SESSION['rol_smp'] == 2) { ?>

    <div class="sidebar">
        <ul class="sidebar-content">

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>dashboard/">
                            <ion-icon name="rocket-outline"></ion-icon>
                            <span>Dashboard</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>caja/">
                            <ion-icon name="storefront-outline"></ion-icon>
                            <span>Caja</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>devolucionLista/">
                            <ion-icon name="repeat-outline"></ion-icon>
                            <span>Devolucion</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>mermaRegistrar/">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            <span>Registrar Merma</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>clienteLista/">
                            <ion-icon name="people-outline"></ion-icon>
                            <span>Clientes</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>compraOrden/">
                            <ion-icon name="storefront-outline"></ion-icon>
                            <span>Compra</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="medical-outline"></ion-icon>
                        <span>Catalogo</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>medicamentoLista/">Medicamento</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>categoriaLista/">Categoria</a></li>
                </ul>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <ion-icon name="server-outline"></ion-icon>
                        <span>Almacen</span>
                    </div>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
                <ul class="sub-links">
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>loteLista/">Lote</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>inventarioLista/">Inventario</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>proveedorLista/">Proveedor</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>transferirLista/">Tranferencias</a></li>
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>recepcionarLista/">Recepcionar</a></li>
                </ul>
            </li>

        </ul>
    </div>

<?php } elseif ($_SESSION['rol_smp'] == 3) { ?>

    <div class="sidebar">
        <ul class="sidebar-content">

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>caja/">
                            <ion-icon name="storefront-outline"></ion-icon>
                            <span>Caja</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>devolucionLista/">
                            <ion-icon name="repeat-outline"></ion-icon>
                            <span>Devolucion</span>
                        </a>
                    </div>
                </div>
            </li>

            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="<?php echo SERVER_URL ?>clienteLista/">
                            <ion-icon name="people-outline"></ion-icon>
                            <span>Clientes</span>
                        </a>
                    </div>
                </div>
            </li>

        </ul>
    </div>

<?php } else { ?>
    <div class="sidebar">
        <ul class="sidebar-content">
            <li class="link">
                <div class="menu-item">
                    <div class="item-link">
                        <a href="#">
                            <ion-icon name="home"></ion-icon>
                            <span>salir</span>
                        </a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
<?php } ?>
