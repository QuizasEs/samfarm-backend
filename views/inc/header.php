<header class="topbar" id="topbar">

    <button class="sb-toggle" id="sbBtn" onclick="App.toggleSidebar()" data-tip="Colapsar menú">
        <ion-icon name="menu-outline" id="sbIcon"></ion-icon>
    </button>

    <?php
    $current_view = isset($_GET['views']) ? explode("/", $_GET['views'])[0] : '';

    $page_titles = [
        // Operaciones Principales
        'dashboard' => 'Inicio',
        'caja' => 'Ventas',
        'compraOrden' => 'Registrar Compra',
        'devolucionLista' => 'Devoluciones',
        'mermaRegistrar' => 'Mermas',
        'clienteLista' => 'Clientes',

        // Catálogo
        'medicamentoLista' => 'Medicamentos',
        'categoriaLista' => 'Categorías',

        // Almacén
        'loteLista' => 'Lotes',
        'inventarioLista' => 'Inventario',
        'ajusteInventarioCompleto' => 'Ajuste de Inventario',
        'proveedorLista' => 'Proveedores',
        'transferirLista' => 'Transferencias',
        'recepcionarLista' => 'Recepcionar',

        // Reportes
        'ventasHistorialLista' => 'Historial de Ventas',
        'cajaHistorialLista' => 'Historial de Caja',
        'cajaHistorialTotales' => 'Cajas Cerradas',
        'comprasHistorialLista' => 'Historial de Compras',
        'transferirHistorialLista' => 'Movimientos de Inventario',
        'mermaLista' => 'Mermas',
        'preciosBalance' => 'Balances de Precios',

        // Configuración
        'usuarioLista' => 'Usuarios',
        'sucursalLista' => 'Sucursales',
        'cajaLista' => 'Gestión de Cajas',
        'empresaEditar' => 'Configuración de Empresa',

        // Perfil
        'perfilEditar' => 'Editar Perfil',
    ];

    $page_title = isset($page_titles[$current_view]) ? $page_titles[$current_view] : 'SamFarm';
    ?>

    <div class="bc">
        <a href="<?php echo SERVER_URL ?>dashboard/" class="link">Inicio</a>
        <span class="sep">/</span>
        <span class="cur"><?php echo $page_title; ?></span>
    </div>

    <div class="tbr">
        <div class="ibtn" id="thico" onclick="App.toggleTheme()">
            <ion-icon name="moon-outline"></ion-icon>
        </div>

        <?php if ($_SESSION['rol_smp'] != 3) { ?>
        <div class="notificacion-container">
            <button class="notificacion" id="notificacionBtn">
                <ion-icon name="notifications-outline"></ion-icon>
                <span class="ndot" id="notificacionBadge" style="display: none;"></span>
            </button>
            <div class="notificacion-modal" id="notificacionModal">
                <div class="notificacion-header">
                    <h3>Notificaciones</h3>
                    <button class="modal-close" id="notificacionModalClose">
                        <ion-icon name="close-outline"></ion-icon>
                    </button>
                </div>
                <div class="notificacion-list" id="notificacionList">
                    <div style="text-align: center; padding: 20px; color: var(--text-faint);">
                        Cargando notificaciones...
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="user">
            <button class="btn-exit-system ibtn" type="submit" title="Salir">
                <ion-icon name="log-out-outline"></ion-icon>
            </button>
        </div>
    </div>

</header>