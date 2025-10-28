            <div class="title">
                <h1>LISTA DE Proveedores</h1>
            </div>
            <!---------------------------------------------lista de usuarios--------------------------------------------------->
            <div class="container">
                <div class="lista-header">
                    <div class="filtro">
                        <input type="text" name="" id=""><button><ion-icon name="search"></ion-icon></button>
                    </div>
                    <div class="header-btn-usuario">
                        <a href="<?php echo SERVER_URL;?>proveedorRegistro/">NUEVO PROVEEDOR</a>
                    </div>
                </div>

                <?php 
                    require_once "./controllers/proveedorController.php";
                    $ins_pro = new proveedorController();
                    $pagina_actual = isset($pagina[1]) ? $pagina[1] : 1;
                    
                    echo $ins_pro->paginado_proveedor_controller($pagina_actual,15,$pagina[0],"")
                ?>
                
            </div>
