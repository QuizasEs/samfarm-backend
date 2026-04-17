<header class="topbar" id="topbar">

    <button class="sb-toggle" id="sbBtn" onclick="App.toggleSidebar()" data-tip="Colapsar menú">
        <ion-icon name="menu-outline" id="sbIcon"></ion-icon>
    </button>

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