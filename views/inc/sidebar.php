<?php
/* administrador vista */
if ($_SESSION['rol_smp'] == 1) {
    $current_view = isset($_GET['views']) ? explode("/", $_GET['views'])[0] : '';

?>
    <aside class="sidebar" id="sidebar">
        <div class="slogo">
            <div class="logo-ic"><ion-icon name="medkit"></ion-icon></div>
            <div>
                <div class="ltxt">SamFarm</div>
                <div class="lsub">Standard</div>
            </div>
        </div>

        <div class="nscr">
            <div class="nsec">
                <div class="nl">categoria 1</div>
                <a class="ni" data-tip="Inicio"><ion-icon class="nic" name="storefront-outline"></ion-icon><span class="ntxt">Inicio</span></a>
                <a class="ni" data-tip="Ventas"><ion-icon class="nic" name="people-outline"></ion-icon><span class="ntxt">Ventas</span></a>
                <a class="ni" data-tip="Devoluciones"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Devoluciones</span></a>
                <a class="ni" data-tip="Mermas"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Mermas</span></a>
                <a class="ni" data-tip="Clientes"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Clientes</span></a>
                <a class="ni" data-tip="Balances"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Balances</span></a>
            </div>
            <div class="nsec">
                <div class="nl">categoria 2</div>
                <!-- catalogo -->
                <div class="ni" id="nii" data-tip="Catalogo" onclick="App.toggleSub('si',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Catalogo</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si">
                    <a class="smi">
                        <div class="smd"></div>Medicamentos
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Categorias
                    </a>
                </div>
                <!-- almacen -->
                 <div class="ni" id="nii" data-tip="Almacen" onclick="App.toggleSub('si',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Almacen</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si">
                    <a class="smi">
                        <div class="smd"></div>Lotes
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Inventario
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Ajuste de Inventario
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Proveedores
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Transferencias
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Recepcionar
                    </a>
                </div>
                <!-- reportes -->
               
                <div class="ni" id="nii" data-tip="Reportes" onclick="App.toggleSub('si',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Reportes</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si">
                    <a class="smi">
                        <div class="smd"></div>Ventas
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Caja
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Cajas cerradas
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Compras
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Movimientos
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Merma
                    </a>
                </div>
                <!-- configuracion -->
                <div class="ni" id="nii" data-tip="Configuracion" onclick="App.toggleSub('si',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Configuracion</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si">
                    <a class="smi">
                        <div class="smd"></div>Usuarios
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Sucursales
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Gestion de Cajas
                    </a>
                    <a class="smi">
                        <div class="smd"></div>Empresa
                    </a>
                </div>
                <a class="ni" data-tip="Perfil"><ion-icon class="nic" name="business-outline"></ion-icon><span class="ntxt">Perfil</span></a>
            </div>
            
        </div>

        <div class="susr">
            <div class="uav">MS</div>
            <div class="uinfo">
                <div class="un">Mayk S.</div>
                <div class="ur">Administrador</div>
            </div>
        </div>
    </aside>

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
                            <span>Registrar compra</span>
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
                    <li class="sub-link"><a href="<?php echo SERVER_URL ?>ajusteInventario/">Ajuste de Inventario</a></li>
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