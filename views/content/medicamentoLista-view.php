<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>
    <div class="pg tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/medicamentoAjax.php"
        data-ajax-param="MedicamentoAjax"
        data-ajax-action="listar"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">Lista de Medicamentos</div>
                <div class="psub">Consulte y administre la información de los medicamentos registrados</div>
            </div>
            <div class="tbr">
                <a class="btn btn-def" href="<?php echo SERVER_URL; ?>medicamentoRegistro/">
                    <ion-icon name="add-outline"></ion-icon> Nuevo Medicamento
                </a>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr4">
                        <div class="fg">
                            <label class="fl">Laboratorios</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todos los proveedores</option>
                                <?php foreach ($datos_select['proveedores'] as $prov) { ?>
                                    <option value="<?php echo $prov['pr_id'] ?>"><?php echo $prov['pr_razon_social'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Vía de Administración</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todas las vías</option>
                                <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                                    <option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Forma Farmacéutica</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todas las formas</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Uso Farmacológico</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todos los usos</option>
                                <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                    <option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Nombre, principio activo o código...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div class="tw">
                    <div class="tabla-contenedor"></div>
                </div>
            </div>
        </div>
    </div>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>