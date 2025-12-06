    <!---------------------------------------------Cabecera--------------------------------------------------->
    <header>
        <nav id="navbar">
            <div class="hamburguesa">
                <ion-icon name="menu"></ion-icon>
            </div>
            <div class="nav-container">
                <div class="nav-title">
                    <h3>BIENVENIDO <?php echo $_SESSION['nombre_smp']; ?></h3>
                </div>
                <div class="nav-content">

                    <!-----------------------Modo Oscuro--------------------------------->
                    <div>
                        <label class="switch">
                            <input type="checkbox" id="darkModeToggleInput">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <!-----------------------Mensage notificaciones--------------------------------->

                    <div class="notificacion">
                        <ion-icon name="notifications-outline"></ion-icon>
                    </div>
                    <div class="user">
                        <button class="btn-exit-system" type="submit" title="Salir">
                            <ion-icon name="walk"></ion-icon>
                        </button>
                    </div>

                </div>

            </div>
        </nav>
    </header>