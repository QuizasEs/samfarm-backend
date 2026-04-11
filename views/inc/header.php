<header class="topbar" id="topbar">

    <button class="sb-toggle" id="sbBtn" onclick="App.toggleSidebar()" data-tip="Colapsar menú">
        <ion-icon name="menu-outline" id="sbIcon"></ion-icon>
    </button>

    <div class="bc">
        <a href="<?php echo SERVER_URL ?>dashboard/" class="link">Inicio</a>
        <span class="sep">/</span>
        <span class="cur">Dashboard</span>
    </div>

    <div class="tbr">
        <div class="ibtn" id="thico">
            <ion-icon name="moon-outline"></ion-icon>
        </div>

        <div class="notificacion-container">
            <button class="notificacion ibtn" id="notificacionBtn">
                <ion-icon name="notifications-outline"></ion-icon>
                <span class="ndot" id="notificacionBadge"></span>
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

        <div class="user">
            <button class="btn-exit-system ibtn" type="submit" title="Salir">
                <ion-icon name="log-out-outline"></ion-icon>
            </button>
        </div>
    </div>

</header>