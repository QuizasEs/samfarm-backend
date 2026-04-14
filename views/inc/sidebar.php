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
                <div class="nl">Operaciones Principales</div>
                <a href="<?php echo SERVER_URL ?>dashboard/" class="ni <?php echo $current_view == 'dashboard' ? 'ac' : ''; ?>" data-tip="Inicio"><ion-icon class="nic" name="storefront-outline"></ion-icon><span class="ntxt">Inicio</span></a>
                <a href="<?php echo SERVER_URL ?>caja/" class="ni <?php echo $current_view == 'caja' ? 'ac' : ''; ?>" data-tip="Ventas"><ion-icon class="nic" name="people-outline"></ion-icon><span class="ntxt">Ventas</span></a>
                <a href="<?php echo SERVER_URL ?>devolucionLista/" class="ni <?php echo $current_view == 'devolucionLista' ? 'ac' : ''; ?>" data-tip="Devoluciones"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Devoluciones</span></a>
                <a href="<?php echo SERVER_URL ?>mermaRegistrar/" class="ni <?php echo $current_view == 'mermaRegistrar' ? 'ac' : ''; ?>" data-tip="Mermas"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Mermas</span></a>
                <a href="<?php echo SERVER_URL ?>clienteLista/" class="ni <?php echo $current_view == 'clienteLista' ? 'ac' : ''; ?>" data-tip="Clientes"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Clientes</span></a>
                <a href="<?php echo SERVER_URL ?>preciosBalance/" class="ni <?php echo $current_view == 'preciosBalance' ? 'ac' : ''; ?>" data-tip="Balances"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Balances</span></a>
            </div>
            <div class="nsec">
                <div class="nl">Catálogo e Inventario</div>
                <!-- catalogo -->
                <?php $is_cat = in_array($current_view, ['medicamentoLista', 'categoriaLista']); ?>
                <div class="ni <?php echo $is_cat ? 'open pac' : ''; ?>" id="nii" data-tip="Catalogo" onclick="App.toggleSub('si_cat',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Catalogo</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_cat ? 'open' : ''; ?>" id="si_cat">
                    <a href="<?php echo SERVER_URL ?>medicamentoLista/" class="smi <?php echo $current_view == 'medicamentoLista' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Medicamentos
                    </a>
                    <a href="<?php echo SERVER_URL ?>categoriaLista/" class="smi <?php echo $current_view == 'categoriaLista' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Categorias
                    </a>
                </div>
                <!-- almacen -->
                <?php $is_alm = in_array($current_view, ['loteLista', 'inventarioLista', 'ajusteInventarioCompleto', 'proveedorLista', 'transferirLista', 'recepcionarLista']); ?>
                 <div class="ni <?php echo $is_alm ? 'open pac' : ''; ?>" id="nii" data-tip="Almacen" onclick="App.toggleSub('si_alm',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Almacen</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_alm ? 'open' : ''; ?>" id="si_alm">
                    <a href="<?php echo SERVER_URL ?>loteLista/" class="smi <?php echo $current_view == 'loteLista' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Lotes
                    </a>
                    <a href="<?php echo SERVER_URL ?>inventarioLista/" class="smi <?php echo $current_view == 'inventarioLista' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>ajusteInventarioCompleto/" class="smi <?php echo $current_view == 'ajusteInventarioCompleto' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Ajuste de Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>proveedorLista/" class="smi <?php echo $current_view == 'proveedorLista' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Proveedores
                    </a>
                    <a href="<?php echo SERVER_URL ?>transferirLista/" class="smi <?php echo $current_view == 'transferirLista' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Transferencias
                    </a>
                    <a href="<?php echo SERVER_URL ?>recepcionarLista/" class="smi <?php echo $current_view == 'recepcionarLista' ? 'ac' : ''; ?>">
                        <div class="smd"></div>Recepcionar
                    </a>
                </div>
            </div>
            <div class="nsec">
                <div class="nl">Reportes</div>
                <div class="ni" id="nii" data-tip="Reportes" onclick="App.toggleSub('si_rep',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Reportes</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si_rep">
                    <a href="<?php echo SERVER_URL ?>ventasHistorialLista/" class="smi">
                        <div class="smd"></div>Ventas
                    </a>
                    <a href="<?php echo SERVER_URL ?>cajaHistorialLista/" class="smi">
                        <div class="smd"></div>Caja
                    </a>
                    <a href="<?php echo SERVER_URL ?>cajaHistorialTotales/" class="smi">
                        <div class="smd"></div>Cajas cerradas
                    </a>
                    <a href="<?php echo SERVER_URL ?>comprasHistorialLista/" class="smi">
                        <div class="smd"></div>Compras
                    </a>
                    <a href="<?php echo SERVER_URL ?>transferirHistorialLista/" class="smi">
                        <div class="smd"></div>Movimientos
                    </a>
                    <a href="<?php echo SERVER_URL ?>mermaLista/" class="smi">
                        <div class="smd"></div>Merma
                    </a>
                </div>
            </div>
            <div class="nsec">
                <div class="nl">Configuración</div>
                <div class="ni" id="nii" data-tip="Configuracion" onclick="App.toggleSub('si_conf',this)">
                    <ion-icon class="nic" name="layers-outline"></ion-icon>
                    <span class="ntxt">Configuracion</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si_conf">
                    <a href="<?php echo SERVER_URL ?>usuarioLista/" class="smi">
                        <div class="smd"></div>Usuarios
                    </a>
                    <a href="<?php echo SERVER_URL ?>sucursalLista/" class="smi">
                        <div class="smd"></div>Sucursales
                    </a>
                    <a href="<?php echo SERVER_URL ?>cajaLista/" class="smi">
                        <div class="smd"></div>Gestion de Cajas
                    </a>
                    <a href="<?php echo SERVER_URL ?>empresaEditar/" class="smi">
                        <div class="smd"></div>Empresa
                    </a>
                </div>
                <a href="<?php echo SERVER_URL ?>perfilEditar/" class="ni" data-tip="Perfil"><ion-icon class="nic" name="business-outline"></ion-icon><span class="ntxt">Perfil</span></a>
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

<?php } elseif ($_SESSION['rol_smp'] == 2) { 
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
                <div class="nl">Operaciones Diarias</div>
                <a href="<?php echo SERVER_URL ?>dashboard/" class="ni" data-tip="Inicio"><ion-icon class="nic" name="rocket-outline"></ion-icon><span class="ntxt">Dashboard</span></a>
                <a href="<?php echo SERVER_URL ?>caja/" class="ni" data-tip="Caja"><ion-icon class="nic" name="storefront-outline"></ion-icon><span class="ntxt">Caja</span></a>
                <a href="<?php echo SERVER_URL ?>compraOrden/" class="ni" data-tip="Registrar compra"><ion-icon class="nic" name="storefront-outline"></ion-icon><span class="ntxt">Registrar compra</span></a>
                <a href="<?php echo SERVER_URL ?>clienteLista/" class="ni" data-tip="Clientes"><ion-icon class="nic" name="people-outline"></ion-icon><span class="ntxt">Clientes</span></a>
                <a href="<?php echo SERVER_URL ?>devolucionLista/" class="ni" data-tip="Devolucion"><ion-icon class="nic" name="repeat-outline"></ion-icon><span class="ntxt">Devolucion</span></a>
                <a href="<?php echo SERVER_URL ?>mermaRegistrar/" class="ni" data-tip="Registrar Merma"><ion-icon class="nic" name="alert-circle-outline"></ion-icon><span class="ntxt">Registrar Merma</span></a>
            </div>
            <div class="nsec">
                <div class="nl">Catálogo e Inventario</div>
                <!-- catalogo -->
                <div class="ni" id="nii" data-tip="Catalogo" onclick="App.toggleSub('si_cat_v',this)">
                    <ion-icon class="nic" name="medical-outline"></ion-icon>
                    <span class="ntxt">Catalogo</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si_cat_v">
                    <a href="<?php echo SERVER_URL ?>medicamentoLista/" class="smi">
                        <div class="smd"></div>Medicamento
                    </a>
                    <a href="<?php echo SERVER_URL ?>categoriaLista/" class="smi">
                        <div class="smd"></div>Categoria
                    </a>
                </div>
                <!-- almacen -->
                 <div class="ni" id="nii" data-tip="Almacen" onclick="App.toggleSub('si_alm_v',this)">
                    <ion-icon class="nic" name="server-outline"></ion-icon>
                    <span class="ntxt">Almacen</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub" id="si_alm_v">
                    <a href="<?php echo SERVER_URL ?>loteLista/" class="smi">
                        <div class="smd"></div>Lote
                    </a>
                    <a href="<?php echo SERVER_URL ?>inventarioLista/" class="smi">
                        <div class="smd"></div>Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>ajusteInventarioCompleto/" class="smi">
                        <div class="smd"></div>Ajuste de Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>proveedorLista/" class="smi">
                        <div class="smd"></div>Proveedor
                    </a>
                    <a href="<?php echo SERVER_URL ?>transferirLista/" class="smi">
                        <div class="smd"></div>Tranferencias
                    </a>
                    <a href="<?php echo SERVER_URL ?>recepcionarLista/" class="smi">
                        <div class="smd"></div>Recepcionar
                    </a>
                </div>
            </div>
            
        </div>

        <div class="susr">
            <div class="uav"><?php echo substr($_SESSION['nombre_smp'], 0, 1) . substr($_SESSION['apellido_smp'], 0, 1); ?></div>
            <div class="uinfo">
                <div class="un"><?php echo $_SESSION['nombre_smp']; ?></div>
                <div class="ur">Vendedor</div>
            </div>
        </div>
    </aside>

<?php } elseif ($_SESSION['rol_smp'] == 3) { 
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
                <div class="nl">Gestión de Ventas</div>
                <a href="<?php echo SERVER_URL ?>caja/" class="ni" data-tip="Caja"><ion-icon class="nic" name="storefront-outline"></ion-icon><span class="ntxt">Caja</span></a>
                <a href="<?php echo SERVER_URL ?>clienteLista/" class="ni" data-tip="Clientes"><ion-icon class="nic" name="people-outline"></ion-icon><span class="ntxt">Clientes</span></a>
                <a href="<?php echo SERVER_URL ?>devolucionLista/" class="ni" data-tip="Devolucion"><ion-icon class="nic" name="repeat-outline"></ion-icon><span class="ntxt">Devolucion</span></a>
            </div>
        </div>

        <div class="susr">
            <div class="uav"><?php echo substr($_SESSION['nombre_smp'], 0, 1) . substr($_SESSION['apellido_smp'], 0, 1); ?></div>
            <div class="uinfo">
                <div class="un"><?php echo $_SESSION['nombre_smp']; ?></div>
                <div class="ur">Cajero</div>
            </div>
        </div>
    </aside>

<?php } else { ?>
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
                <div class="nl">Menu</div>
                <a href="#" class="ni" data-tip="salir"><ion-icon class="nic" name="home"></ion-icon><span class="ntxt">salir</span></a>
            </div>
        </div>

        <div class="susr">
            <div class="uav">IN</div>
            <div class="uinfo">
                <div class="un">Invitado</div>
                <div class="ur">Desconocido</div>
            </div>
        </div>
    </aside>
<?php } ?>
