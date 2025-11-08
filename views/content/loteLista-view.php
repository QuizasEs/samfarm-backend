            <div class="title">
                <h1>LISTA DE inventarios</h1>
            </div>
            <!---------------------------------------------lista de usuarios--------------------------------------------------->
            <div class="container">
                <div class="lista-header">
                    <div class="filtro">
                        <select class="select" name="" id="">
                            <option value="">en espera</option>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                        <input type="text" name="" id="">
                        <button><ion-icon name="search"></ion-icon></button>

                        
                    </div>
                    
                    
                </div>

                <?php 
                    require_once "./controllers/loteController.php";
                    $ins_lote = new loteController();
                    $pagina_actual = isset($pagina[1]) ? $pagina[1] : 1;
                    
                    echo $ins_lote->paginado_lote_controller($pagina_actual,15,$pagina[0],"")
                ?>
                
            </div>
