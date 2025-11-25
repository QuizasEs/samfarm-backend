<?php
if (isset($_SESSION['id_smp'])) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>
    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/medicamentoAjax.php"
        data-ajax-param="MedicamentoAjax"
        data-ajax-registros="10">

        <div class="title">
            <h2><ion-icon name="medkit-outline"></ion-icon> LISTA DE MEDICAMENTOS</h2>
        </div>

        <div class="container">
            <form class="filtro-dinamico">
                <div class="filtro-dinamico-search">

                    <div class="form-fechas">
                        <small>
                            Laboratorios
                        </small>
                        <select class="select-filtro" name="select1">
                            <option value="">Todos los laboratorios</option>
                            <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                                <option value="<?php echo $lab['la_id'] ?>"><?php echo $lab['la_nombre_comercial'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-fechas">
                        <small>Via de administracion</small>
                        <select class="select-filtro" name="select2">
                            <option value="">Todas las vías</option>
                            <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                                <option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-fechas">
                        <small>Forma farmaceutica</small>
                        <select class="select-filtro" name="select3">
                            <option value="">Todas las formas</option>
                            <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-fechas">
                        <small>Uso farmacologico</small>
                        <select class="select-filtro" name="select4">
                            <option value="">Todos los usos</option>
                            <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                <option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="search">
                        <input type="text" name="busqueda" placeholder="Buscar medicamento...">
                        <button type="button" class="btn-search"><ion-icon name="search"></ion-icon></button>
                    </div>

                </div>

                <div class="">
                    <a class="btn default" href="<?php echo SERVER_URL; ?>medicamentoRegistro/"><ion-icon name="add-outline"></ion-icon> NUEVO</a>
                </div>
            </form>

            <div class="tabla-contenedor"></div>
        </div>
    </div>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2>Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>