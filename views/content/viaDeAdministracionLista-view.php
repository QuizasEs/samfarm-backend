            <div class="title">
                <h1>LISTA VIA DE ADMINISTRACION</h1>
            </div>
            <!---------------------------------------------LISTA VIA DE ADMINISTRACION--------------------------------------------------->
            <div class="container">
                <div class="lista-header">
                    <div class="filtro">
                        <input type="text" name="" id=""><button><ion-icon name="search"></ion-icon></button>
                    </div>
                    <div class="header-btn-usuario">
                        <a href="<?php echo SERVER_URL; ?>/">NUEVA VIA</a>
                    </div>
                </div>

                <?php
                require_once "./controllers/medicamentoController.php";
                $ins_med = new medicamentoController();
                $pagina_actual = isset($pagina[1]) ? $pagina[1] : 1;
                /* solisitamos lista tablas de la funcion paginador_usuario_controller */
                echo $ins_med->paginado_medicamento_via_controller($pagina_actual, 15, $_SESSION['rol_smp'], $pagina[0], "")
                ?>

            </div>