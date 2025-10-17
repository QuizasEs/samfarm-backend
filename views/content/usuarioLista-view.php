            <div class="title">
                <h1>LISTA DE USUARIOS</h1>
            </div>
            <!---------------------------------------------lista de usuarios--------------------------------------------------->
            <div class="container">
                <div class="lista-header">
                    <div class="filtro">
                        <input type="text" name="" id=""><button><ion-icon name="search"></ion-icon></button>
                    </div>
                    <div class="header-btn-usuario">
                        <a href="<?php echo SERVER_URL;?>registroUsuario/">NUEVO USUARIO</a>
                    </div>
                </div>

                <?php 
                    require_once "./controllers/userController.php";
                    $ins_usuario = new userController();
                    $pagina_actual = isset($pagina[1]) ? $pagina[1] : 1;
                    /* solisitamos lista tablas de la funcion paginador_usuario_controller */
                    echo $ins_usuario->paginado_user_controller($pagina_actual,15,$_SESSION['rol_smp'],$_SESSION['id_smp'],$pagina[0],"")
                ?>
                
            </div>
