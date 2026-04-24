<?php
require_once __DIR__ . '/../../models/mainModel.php';

/* administrador vista */
if ($_SESSION['rol_smp'] == 1) {
    $current_view = isset($_GET['views']) ? explode("/", $_GET['views'])[0] : '';

    // Fetch empresa config
    require_once __DIR__ . '/../../controllers/sucursalController.php';
    $ins_sucursal = new sucursalController();
    $config_json = $ins_sucursal->datos_config_empresa_controller();
    $config = json_decode($config_json, true);
    $logo = $config['ce_logo'] ?? null;
    $program_name = $config['ce_nombre'] ?? 'SamFarm';
    $sucursal = isset($_SESSION['sucursal_smp']) ? $_SESSION['sucursal_smp'] : 'Standard';

?>
    <aside class="sidebar" id="sidebar">
        <div class="slogo">
            <div class="logo-ic">
                <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; display: <?php echo $logo ? 'block' : 'none'; ?>;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <ion-icon name="medkit" style="display: <?php echo $logo ? 'none' : 'block'; ?>;"></ion-icon>
            </div>
            <div>
                <div class="ltxt"><?php echo htmlspecialchars($program_name); ?></div>
                <div class="lsub">Sistema</div>
            </div>
        </div>


        <div class="nscr">
            <div class="nsec">
                <div class="nl">Operaciones Principales</div>
                <a href="<?php echo SERVER_URL ?>dashboard/" class="ni <?php echo $current_view == 'dashboard' ? 'ac' : ''; ?>" data-tip="Inicio"><ion-icon class="nic" name="home-outline"></ion-icon><span class="ntxt">Inicio</span></a>
                <a href="<?php echo SERVER_URL ?>caja/" class="ni <?php echo $current_view == 'caja' ? 'ac' : ''; ?>" data-tip="Ventas"><ion-icon class="nic" name="cash-outline"></ion-icon><span class="ntxt">Ventas</span></a>
                <a href="<?php echo SERVER_URL ?>compraOrden/" class="ni <?php echo $current_view == 'compraOrden' ? 'ac' : ''; ?>" data-tip="Registrar Compra"><ion-icon class="nic" name="cart-outline"></ion-icon><span class="ntxt">Registrar Compra</span></a>
                <a href="<?php echo SERVER_URL ?>devolucionLista/" class="ni <?php echo $current_view == 'devolucionLista' ? 'ac' : ''; ?>" data-tip="Devoluciones"><ion-icon class="nic" name="repeat-outline"></ion-icon><span class="ntxt">Devoluciones</span></a>
                <a href="<?php echo SERVER_URL ?>mermaRegistrar/" class="ni <?php echo $current_view == 'mermaRegistrar' ? 'ac' : ''; ?>" data-tip="Mermas"><ion-icon class="nic" name="warning-outline"></ion-icon><span class="ntxt">Mermas</span></a>
                <a href="<?php echo SERVER_URL ?>clienteLista/" class="ni <?php echo $current_view == 'clienteLista' ? 'ac' : ''; ?>" data-tip="Clientes"><ion-icon class="nic" name="person-outline"></ion-icon><span class="ntxt">Clientes</span></a>
            </div>
            <div class="nsec">
                <div class="nl">Catálogo e Inventario</div>
                <!-- catalogo -->
                <?php $is_cat = in_array($current_view, ['medicamentoLista', 'categoriaLista']); ?>
                <div class="ni <?php echo $is_cat ? 'open pac' : ''; ?>" id="nii" data-tip="Catalogo" onclick="App.toggleSub('si_cat',this)">
                    <ion-icon class="nic" name="list-outline"></ion-icon>
                    <span class="ntxt">Catalogo</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_cat ? 'open' : ''; ?>" id="si_cat">
                    <a href="<?php echo SERVER_URL ?>medicamentoLista/" class="smi <?php echo $current_view == 'medicamentoLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="medical-outline"></ion-icon>Medicamentos
                    </a>
                    <a href="<?php echo SERVER_URL ?>categoriaLista/" class="smi <?php echo $current_view == 'categoriaLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="folder-outline"></ion-icon>Categorias
                    </a>
                </div>
                <!-- almacen -->
                <?php $is_alm = in_array($current_view, ['loteLista', 'inventarioLista', 'ajusteInventarioCompleto', 'proveedorLista', 'transferirLista', 'recepcionarLista']); ?>
                <div class="ni <?php echo $is_alm ? 'open pac' : ''; ?>" id="nii" data-tip="Almacen" onclick="App.toggleSub('si_alm',this)">
                    <ion-icon class="nic" name="cube-outline"></ion-icon>
                    <span class="ntxt">Almacen</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_alm ? 'open' : ''; ?>" id="si_alm">
                    <a href="<?php echo SERVER_URL ?>loteLista/" class="smi <?php echo $current_view == 'loteLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="cube-outline"></ion-icon>Lotes
                    </a>
                    <a href="<?php echo SERVER_URL ?>inventarioLista/" class="smi <?php echo $current_view == 'inventarioLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="list-outline"></ion-icon>Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>ajusteInventarioCompleto/" class="smi <?php echo $current_view == 'ajusteInventarioCompleto' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="build-outline"></ion-icon>Ajuste de Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>proveedorLista/" class="smi <?php echo $current_view == 'proveedorLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="business-outline"></ion-icon>Proveedores
                    </a>
                    <a href="<?php echo SERVER_URL ?>transferirLista/" class="smi <?php echo $current_view == 'transferirLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="swap-horizontal-outline"></ion-icon>Transferencias
                    </a>
                    <a href="<?php echo SERVER_URL ?>recepcionarLista/" class="smi <?php echo $current_view == 'recepcionarLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="download-outline"></ion-icon>Recepcionar
                    </a>
                </div>
            </div>
            <?php $is_rep = in_array($current_view, ['ventasHistorialLista', 'cajaHistorialLista', 'cajaHistorialTotales', 'comprasHistorialLista', 'transferirHistorialLista', 'mermaLista']); ?>
            <div class="nsec">
                <div class="nl">Reportes</div>
                <div class="ni <?php echo $is_rep ? 'open pac' : ''; ?>" id="nii" data-tip="Reportes" onclick="App.toggleSub('si_rep',this)">
                    <ion-icon class="nic" name="bar-chart-outline"></ion-icon>
                    <span class="ntxt">Reportes</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_rep ? 'open' : ''; ?>" id="si_rep">
                    <a href="<?php echo SERVER_URL ?>ventasHistorialLista/" class="smi <?php echo $current_view == 'ventasHistorialLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="bar-chart-outline"></ion-icon>Ventas
                    </a>
                    <a href="<?php echo SERVER_URL ?>cajaHistorialLista/" class="smi <?php echo $current_view == 'cajaHistorialLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="cash-outline"></ion-icon>Caja
                    </a>
                    <a href="<?php echo SERVER_URL ?>cajaHistorialTotales/" class="smi <?php echo $current_view == 'cajaHistorialTotales' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="calculator-outline"></ion-icon>Cajas cerradas
                    </a>
                    <a href="<?php echo SERVER_URL ?>comprasHistorialLista/" class="smi <?php echo $current_view == 'comprasHistorialLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="cart-outline"></ion-icon>Compras
                    </a>
                    <a href="<?php echo SERVER_URL ?>transferirHistorialLista/" class="smi <?php echo $current_view == 'transferirHistorialLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="swap-horizontal-outline"></ion-icon>Movimientos
                    </a>
                    <a href="<?php echo SERVER_URL ?>mermaLista/" class="smi <?php echo $current_view == 'mermaLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="warning-outline"></ion-icon>Mermas
                    </a>
                    <a href="<?php echo SERVER_URL ?>preciosBalance/" class="smi <?php echo $current_view == 'mermaLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="warning-outline"></ion-icon>Balances
                    </a>
                </div>
            </div>
            <?php $is_conf = in_array($current_view, ['usuarioLista', 'sucursalLista', 'cajaLista', 'empresaEditar']); ?>
            <div class="nsec">
                <div class="nl">Configuración</div>
                <div class="ni <?php echo $is_conf ? 'open pac' : ''; ?>" id="nii" data-tip="Configuracion" onclick="App.toggleSub('si_conf',this)">
                    <ion-icon class="nic" name="settings-outline"></ion-icon>
                    <span class="ntxt">Configuracion</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_conf ? 'open' : ''; ?>" id="si_conf">
                    <a href="<?php echo SERVER_URL ?>usuarioLista/" class="smi <?php echo $current_view == 'usuarioLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="people-outline"></ion-icon>Usuarios
                    </a>
                    <a href="<?php echo SERVER_URL ?>sucursalLista/" class="smi <?php echo $current_view == 'sucursalLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="business-outline"></ion-icon>Sucursales
                    </a>
                    <a href="<?php echo SERVER_URL ?>cajaLista/" class="smi <?php echo $current_view == 'cajaLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="cash-outline"></ion-icon>Gestion de Cajas
                    </a>
                    <a href="<?php echo SERVER_URL ?>empresaEditar/" class="smi <?php echo $current_view == 'empresaEditar' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="briefcase-outline"></ion-icon>Empresa
                    </a>
                </div>
            </div>

        </div>

        <a href="<?php echo SERVER_URL ?>perfilEditar/<?php echo mainModel::encryption($_SESSION['id_smp']); ?>/" class="susr" data-tip="Perfil">
            <div class="uav">PF</div>
            <div class="uinfo">
                <div class="un">Perfil</div>
                <div class="ur">Administrador</div>
            </div>
        </a>
    </aside>

<?php } elseif ($_SESSION['rol_smp'] == 2) {
    $current_view = isset($_GET['views']) ? explode("/", $_GET['views'])[0] : '';

    // Fetch empresa config
    require_once __DIR__ . '/../../controllers/sucursalController.php';
    $ins_sucursal = new sucursalController();
    $config_json = $ins_sucursal->datos_config_empresa_controller();
    $config = json_decode($config_json, true);
    $logo = $config['ce_logo'] ?? null;
    $program_name = $config['ce_nombre'] ?? 'SamFarm';
    $sucursal = isset($_SESSION['sucursal_smp']) ? $_SESSION['sucursal_smp'] : 'Standard';

    $is_cat = in_array($current_view, ['medicamentoLista', 'categoriaLista']);
    $is_alm = in_array($current_view, ['loteLista', 'inventarioLista', 'ajusteInventarioCompleto', 'proveedorLista', 'transferirLista', 'recepcionarLista']);

?>
    <aside class="sidebar" id="sidebar">
        <div class="slogo">
            <div class="logo-ic">
                <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; display: <?php echo $logo ? 'block' : 'none'; ?>;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <ion-icon name="medkit" style="display: <?php echo $logo ? 'none' : 'block'; ?>;"></ion-icon>
            </div>
            <div>
                <div class="ltxt"><?php echo htmlspecialchars($program_name); ?></div>
                <div class="lsub">Sistema</div>
            </div>
        </div>

        <div class="nscr">
            <div class="nsec">
                <div class="nl">Operaciones Diarias</div>
                <a href="<?php echo SERVER_URL ?>dashboard/" class="ni" data-tip="Inicio"><ion-icon class="nic" name="home-outline"></ion-icon><span class="ntxt">Inicio</span></a>
                <a href="<?php echo SERVER_URL ?>caja/" class="ni" data-tip="Ventas"><ion-icon class="nic" name="cash-outline"></ion-icon><span class="ntxt">Ventas</span></a>
                <a href="<?php echo SERVER_URL ?>compraOrden/" class="ni" data-tip="Registrar Compra"><ion-icon class="nic" name="cart-outline"></ion-icon><span class="ntxt">Registrar Compra</span></a>
                <a href="<?php echo SERVER_URL ?>clienteLista/" class="ni" data-tip="Clientes"><ion-icon class="nic" name="people-outline"></ion-icon><span class="ntxt">Clientes</span></a>
                <a href="<?php echo SERVER_URL ?>devolucionLista/" class="ni" data-tip="Devoluciones"><ion-icon class="nic" name="repeat-outline"></ion-icon><span class="ntxt">Devoluciones</span></a>
                <a href="<?php echo SERVER_URL ?>mermaRegistrar/" class="ni" data-tip="Mermas"><ion-icon class="nic" name="warning-outline"></ion-icon><span class="ntxt">Mermas</span></a>
            </div>
            <div class="nsec">
                <div class="nl">Catálogo e Inventario</div>
                <!-- catalogo -->
                <div class="ni <?php echo $is_cat ? 'open pac' : ''; ?>" id="nii" data-tip="Catalogo" onclick="App.toggleSub('si_cat_v',this)">
                    <ion-icon class="nic" name="list-outline"></ion-icon>
                    <span class="ntxt">Catalogo</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_cat ? 'open' : ''; ?>" id="si_cat_v">
                    <a href="<?php echo SERVER_URL ?>medicamentoLista/" class="smi <?php echo $current_view == 'medicamentoLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="medical-outline"></ion-icon>Medicamento
                    </a>
                    <a href="<?php echo SERVER_URL ?>categoriaLista/" class="smi <?php echo $current_view == 'categoriaLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="folder-outline"></ion-icon>Categoria
                    </a>
                </div>
                <!-- almacen -->
                <div class="ni <?php echo $is_alm ? 'open pac' : ''; ?>" id="nii" data-tip="Almacen" onclick="App.toggleSub('si_alm_v',this)">
                    <ion-icon class="nic" name="cube-outline"></ion-icon>
                    <span class="ntxt">Almacen</span>
                    <ion-icon class="narr" name="chevron-forward"></ion-icon>
                </div>
                <div class="sub <?php echo $is_alm ? 'open' : ''; ?>" id="si_alm_v">
                    <a href="<?php echo SERVER_URL ?>loteLista/" class="smi <?php echo $current_view == 'loteLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="cube-outline"></ion-icon>Lote
                    </a>
                    <a href="<?php echo SERVER_URL ?>inventarioLista/" class="smi <?php echo $current_view == 'inventarioLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="list-outline"></ion-icon>Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>ajusteInventarioCompleto/" class="smi <?php echo $current_view == 'ajusteInventarioCompleto' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="build-outline"></ion-icon>Ajuste de Inventario
                    </a>
                    <a href="<?php echo SERVER_URL ?>proveedorLista/" class="smi <?php echo $current_view == 'proveedorLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="business-outline"></ion-icon>Proveedor
                    </a>
                    <a href="<?php echo SERVER_URL ?>transferirLista/" class="smi <?php echo $current_view == 'transferirLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="swap-horizontal-outline"></ion-icon>Tranferencias
                    </a>
                    <a href="<?php echo SERVER_URL ?>recepcionarLista/" class="smi <?php echo $current_view == 'recepcionarLista' ? 'ac' : ''; ?>">
                        <ion-icon class="smd" name="download-outline"></ion-icon>Recepcionar
                    </a>
                </div>
            </div>

        </div>

        <div class="susr">
            <div class="uav"><?php echo (isset($_SESSION['nombre_smp']) && isset($_SESSION['apellido_smp'])) ? substr($_SESSION['nombre_smp'], 0, 1) . substr($_SESSION['apellido_smp'], 0, 1) : 'U'; ?></div>
            <div class="uinfo">
                <div class="un"><?php echo $_SESSION['nombre_smp'] ?? 'Usuario'; ?></div>
                <div class="ur">Vendedor</div>
            </div>
        </div>
    </aside>

<?php } elseif ($_SESSION['rol_smp'] == 3) {
    $current_view = isset($_GET['views']) ? explode("/", $_GET['views'])[0] : '';

    // Fetch empresa config
    require_once __DIR__ . '/../../controllers/sucursalController.php';
    $ins_sucursal = new sucursalController();
    $config_json = $ins_sucursal->datos_config_empresa_controller();
    $config = json_decode($config_json, true);
    $logo = $config['ce_logo'] ?? null;
    $program_name = $config['ce_nombre'] ?? 'SamFarm';
    $sucursal = isset($_SESSION['sucursal_smp']) ? $_SESSION['sucursal_smp'] : 'Standard';

?>
    <aside class="sidebar" id="sidebar">
        <div class="slogo">
            <div class="logo-ic">
                <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; display: <?php echo $logo ? 'block' : 'none'; ?>;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <ion-icon name="medkit" style="display: <?php echo $logo ? 'none' : 'block'; ?>;"></ion-icon>
            </div>
            <div>
                <div class="ltxt"><?php echo htmlspecialchars($program_name); ?></div>
                <div class="lsub">Sistema</div>
            </div>
        </div>

        <div class="nscr">
            <div class="nsec">
                <div class="nl">Gestión de Ventas</div>
                <a href="<?php echo SERVER_URL ?>caja/" class="ni" data-tip="Ventas"><ion-icon class="nic" name="cash-outline"></ion-icon><span class="ntxt">Ventas</span></a>
                <a href="<?php echo SERVER_URL ?>clienteLista/" class="ni" data-tip="Clientes"><ion-icon class="nic" name="people-outline"></ion-icon><span class="ntxt">Clientes</span></a>
                <a href="<?php echo SERVER_URL ?>devolucionLista/" class="ni" data-tip="Devoluciones"><ion-icon class="nic" name="repeat-outline"></ion-icon><span class="ntxt">Devoluciones</span></a>
            </div>
        </div>

        <div class="susr">
            <div class="uav"><?php echo (isset($_SESSION['nombre_smp']) && isset($_SESSION['apellido_smp'])) ? substr($_SESSION['nombre_smp'], 0, 1) . substr($_SESSION['apellido_smp'], 0, 1) : 'U'; ?></div>
            <div class="uinfo">
                <div class="un"><?php echo $_SESSION['nombre_smp'] ?? 'Usuario'; ?></div>
                <div class="ur">Cajero</div>
            </div>
        </div>
    </aside>

<?php } else {
    // Fetch empresa config
    require_once __DIR__ . '/../../controllers/sucursalController.php';
    $ins_sucursal = new sucursalController();
    $config_json = $ins_sucursal->datos_config_empresa_controller();
    $config = json_decode($config_json, true);
    $logo = $config['ce_logo'] ?? null;
    $program_name = $config['ce_nombre'] ?? 'SamFarm';
    $sucursal = isset($_SESSION['sucursal_smp']) ? $_SESSION['sucursal_smp'] : 'Standard';

?>
    <aside class="sidebar" id="sidebar">
        <div class="slogo">
            <div class="logo-ic">
                <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; display: <?php echo $logo ? 'block' : 'none'; ?>;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <ion-icon name="medkit" style="display: <?php echo $logo ? 'none' : 'block'; ?>;"></ion-icon>
            </div>
            <div>
                <div class="ltxt"><?php echo htmlspecialchars($program_name); ?></div>
                <div class="lsub">Sistema</div>
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