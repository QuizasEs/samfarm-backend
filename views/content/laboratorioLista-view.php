            <div class="title">
                <h1>LISTA DE LABORATORIOS</h1>
            </div>
            <!---------------------------------------------lista de usuarios--------------------------------------------------->
            <div class="container">
                <div class="lista-header">
                    <div class="filtro">
                        <input type="text" name="" id=""><button><ion-icon name="search"></ion-icon></button>
                    </div>
                    <div class="header-btn-usuario">
                        <a href="<?php echo SERVER_URL;?>laboratorioRegistro/">NUEVO LABORATORIO</a>
                    </div>
                </div>

                <?php 
                    require_once "./controllers/proveedorController.php";
                    $ins_lab = new proveedorController();
                    $pagina_actual = isset($pagina[1]) ? $pagina[1] : 1;
                    
                    echo $ins_lab->paginado_laboratorio_controller($pagina_actual,15,$pagina[0],"")
                ?>
                
            </div>
