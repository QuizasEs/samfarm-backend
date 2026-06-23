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
                <button type="button" class="btn btn-def" onclick="MedicamentosModals.abrirModalNuevo()">
                    <ion-icon name="add-outline"></ion-icon> Nuevo Medicamento
                </button>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr3">
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
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Nombre, principio activo o código...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>

                        <!-- <div class="fg">
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
                        </div> -->

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

    <!-- Modal Nuevo Medicamento -->
    <div class="mov" id="modalNuevoMedicamento" style="display: none;">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt">Nuevo Medicamento</div>
                </div>
                <button class="mcl" onclick="MedicamentosModals.cerrarModalNuevo()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <div class="mb">
                <form id="formNuevoMedicamento">
                    <input type="hidden" name="MedicamentoAjax" value="save">
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Nombre Químico <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Nombre_reg" placeholder="Nombre comercial" maxlength="100" required>
                        </div>
                    </div>
                    <div class="fr">

                        <div class="fg">
                            <label class="fl">Principio Activo <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Principio_reg" maxlength="100" placeholder="Ingrediente principal" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Acción Farmacológica <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Accion_reg" maxlength="100" placeholder="Accion esperada" required>
                        </div>
                    </div>
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Descripción</label>
                            <input type="text" class="inp" name="Descripcion_reg" placeholder="Si se requiere" maxlength="200">
                        </div>
                        <div class="fg">
                            <label class="fl">Presentación <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Presentacion_reg" maxlength="100" placeholder="Metrica" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Código de Barras</label>
                            <input type="text" class="inp" name="CodigoBarras_reg" maxlength="100" placeholder="Código de barras opcional">
                        </div>
                    </div>
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Uso Farmacológico <span class="tdan">*</span></label>
                            <select class="sel" name="Uso_reg" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                    <option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Forma Farmacéutica <span class="tdan">*</span></label>
                            <select class="sel" name="Forma_reg" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="fr">

                        <div class="fg">
                            <label class="fl">Vía de Administración <span class="tdan">*</span></label>
                            <select class="sel" name="Via_reg" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                                    <option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Proveedor (Laboratorio) <span class="tdan">*</span></label>
                            <select class="sel" name="Proveedor_reg" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['proveedores'] as $proveedor) { ?>
                                    <option value="<?php echo $proveedor['pr_id'] ?>"><?php echo $proveedor['pr_razon_social'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-sec" onclick="MedicamentosModals.cerrarModalNuevo()">Cancelar</button>
                <button type="button" class="btn btn-def" onclick="MedicamentosModals.guardarNuevo()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Medicamento -->
    <div class="mov" id="modalEditarMedicamento" style="display: none;">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt">Editar Medicamento</div>
                </div>
                <button class="mcl" onclick="MedicamentosModals.cerrarModalEditar()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <div class="mb">
                <form id="formEditarMedicamento">
                    <input type="hidden" name="MedicamentoAjax" value="update">
                    <input type="hidden" name="med_id">
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Nombre Químico <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Nombre_up" placeholder="Nombre comercial" maxlength="100" required>
                        </div>


                    </div>
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Principio Activo <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Principio_up" maxlength="100" placeholder="Ingrediente principal" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Acción Farmacológica <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Accion_up" maxlength="100" placeholder="Accion esperada" required>
                        </div>
                    </div>
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Descripción</label>
                            <input type="text" class="inp" name="Descripcion_up" placeholder="Si se requiere" maxlength="200">
                        </div>
                        <div class="fg">
                            <label class="fl">Presentación <span class="tdan">*</span></label>
                            <input type="text" class="inp" name="Presentacion_up" maxlength="100" placeholder="Metrica" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Código de Barras</label>
                            <input type="text" class="inp" name="CodigoBarras_up" maxlength="100" placeholder="Código de barras opcional">
                        </div>
                    </div>
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Uso Farmacológico <span class="tdan">*</span></label>
                            <select class="sel" name="Uso_up" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                    <option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Forma Farmacéutica <span class="tdan">*</span></label>
                            <select class="sel" name="Forma_up" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Vía de Administración <span class="tdan">*</span></label>
                            <select class="sel" name="Via_up" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                                    <option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Proveedor (Laboratorio) <span class="tdan">*</span></label>
                            <select class="sel" name="Proveedor_up" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['proveedores'] as $proveedor) { ?>
                                    <option value="<?php echo $proveedor['pr_id'] ?>"><?php echo $proveedor['pr_razon_social'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-war" onclick="MedicamentosModals.eliminarMedicamento(document.querySelector('#formEditarMedicamento input[name=med_id]').value)">
                    <ion-icon name="trash-outline"></ion-icon> Eliminar
                </button>
                <div class="mf-right">
                    <button type="button" class="btn btn-sec" onclick="MedicamentosModals.cerrarModalEditar()">Cancelar</button>
                    <button type="button" class="btn btn-def" onclick="MedicamentosModals.guardarEditar()">Actualizar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo SERVER_URL; ?>views/script/medicamentoLista-view.js"></script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>