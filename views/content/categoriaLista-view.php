<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="pg tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/categoriaAjax.php"
        data-ajax-param="categoriaAjax"
        data-ajax-registros="6"
        data-ajax-action="listar_uso">

        <div class="ph">
            <div>
                <div class="ptit">Uso Farmacológico</div>
                <div class="psub">Administre los usos farmacológicos de los medicamentos</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="abrirModalAgregarUsoFarmacologico()">
                    <ion-icon name="add-outline"></ion-icon> Nuevo Uso Farmacológico
                </button>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre...">
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

    <!-- Modal Agregar Uso Farmacológico -->
    <div class="mov" id="modalAgregarUsoFarmacologico">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Agregar Uso Farmacológico</div>
                    <div class="ms">Complete los datos para agregar un nuevo uso farmacológico</div>
                </div>
                <button class="mcl" onclick="App.closeM('modalAgregarUsoFarmacologico')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <form id="formAgregarUso" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="agregar_uso">

                    <div class="fg">
                        <label class="fl req">Nombre</label>
                        <input class="inp" type="text" name="nombre_uso" id="nombre_uso" required maxlength="250">
                    </div>

                    <div class="fg">
                        <label class="fl">Imagen</label>
                        <input class="inp" type="file" name="imgLoad_uso" id="imgLoad_uso" accept="image/*">
                    </div>

                    <div class="fg" style="text-align: center;">
                        <img id="img-pic-uso" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                            onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                    </div>
                </form>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalAgregarUsoFarmacologico')">Cancelar</button>
                <button type="submit" form="formAgregarUso" class="btn btn-def">
                    <ion-icon name="save-outline"></ion-icon> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Uso Farmacológico -->
    <div class="mov" id="modalEditarUsoFarmacologico">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Editar Uso Farmacológico</div>
                    <div class="ms">Actualice los datos del uso farmacológico seleccionado</div>
                </div>
                <button class="mcl" onclick="App.closeM('modalEditarUsoFarmacologico')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <form id="formEditarUso" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="actualizar_uso">
                    <input type="hidden" name="id_uso_edit" id="id_uso_edit">
                    <input type="hidden" name="imagen_antigua_edit" id="imagen_antigua_edit">

                    <div class="fg">
                        <label class="fl req">Nombre</label>
                        <input class="inp" type="text" name="nombre_uso_edit" id="nombre_uso_edit" required maxlength="250">
                    </div>

                    <div class="fg">
                        <label class="fl">Imagen</label>
                        <input class="inp" type="file" name="imgLoad_uso_edit" id="imgLoad_uso_edit" accept="image/*">
                    </div>

                    <div class="fg" style="text-align: center;">
                        <img id="img-pic-uso-edit" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                            onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                    </div>
                </form>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalEditarUsoFarmacologico')">Cancelar</button>
                <button type="submit" form="formEditarUso" class="btn btn-def">
                    <ion-icon name="save-outline"></ion-icon> Actualizar
                </button>
            </div>
        </div>
    </div>

    <!-- via de administracion -->
    <div class="pg tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/categoriaAjax.php"
        data-ajax-param="categoriaAjax"
        data-ajax-registros="6"
        data-ajax-action="listar_via">

        <div class="ph">
            <div>
                <div class="ptit">Vía de Administración</div>
                <div class="psub">Administre las vías de administración de los medicamentos</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="abrirModalAgregarViaAdministracion()">
                    <ion-icon name="add-outline"></ion-icon> Nueva Vía de Administración
                </button>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre...">
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

    <!-- Modal Agregar Vía de Administración -->
    <div class="mov" id="modalAgregarViaAdministracion">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Agregar Vía de Administración</div>
                    <div class="ms">Complete los datos para agregar una nueva vía de administración</div>
                </div>
                <button class="mcl" onclick="App.closeM('modalAgregarViaAdministracion')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <form id="formAgregarVia" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="agregar_via">

                    <div class="fg">
                        <label class="fl req">Nombre</label>
                        <input class="inp" type="text" name="nombre_via" id="nombre_via" required maxlength="250">
                    </div>

                    <div class="fg">
                        <label class="fl">Imagen</label>
                        <input class="inp" type="file" name="imgLoad_via" id="imgLoad_via" accept="image/*">
                    </div>

                    <div class="fg" style="text-align: center;">
                        <img id="img-pic-via" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                            onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                    </div>
                </form>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalAgregarViaAdministracion')">Cancelar</button>
                <button type="submit" form="formAgregarVia" class="btn btn-def">
                    <ion-icon name="save-outline"></ion-icon> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Vía de Administración -->
    <div class="mov" id="modalEditarViaAdministracion">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Editar Vía de Administración</div>
                    <div class="ms">Actualice los datos de la vía de administración seleccionada</div>
                </div>
                <button class="mcl" onclick="App.closeM('modalEditarViaAdministracion')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <form id="formEditarVia" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="actualizar_via">
                    <input type="hidden" name="id_via_edit" id="id_via_edit">
                    <input type="hidden" name="imagen_antigua_edit_via" id="imagen_antigua_edit_via">

                    <div class="fg">
                        <label class="fl req">Nombre</label>
                        <input class="inp" type="text" name="nombre_via_edit" id="nombre_via_edit" required maxlength="250">
                    </div>

                    <div class="fg">
                        <label class="fl">Imagen</label>
                        <input class="inp" type="file" name="imgLoad_via_edit" id="imgLoad_via_edit" accept="image/*">
                    </div>

                    <div class="fg" style="text-align: center;">
                        <img id="img-pic-via-edit" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                            onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                    </div>
                </form>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalEditarViaAdministracion')">Cancelar</button>
                <button type="submit" form="formEditarVia" class="btn btn-def">
                    <ion-icon name="save-outline"></ion-icon> Actualizar
                </button>
            </div>
        </div>
    </div>



    <div class="pg tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/categoriaAjax.php"
        data-ajax-param="categoriaAjax"
        data-ajax-registros="6"
        data-ajax-action="listar_forma">

        <div class="ph">
            <div>
                <div class="ptit">Forma Farmacéutica</div>
                <div class="psub">Administre las formas farmacéuticas de los medicamentos</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="abrirModalAgregarFormaFarmaceutica()">
                    <ion-icon name="add-outline"></ion-icon> Nueva Forma Farmacéutica
                </button>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre...">
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

    <!-- Modal Agregar Forma Farmacéutica -->
    <div class="mov" id="modalAgregarFormaFarmaceutica">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Agregar Forma Farmacéutica</div>
                    <div class="ms">Complete los datos para agregar una nueva forma farmacéutica</div>
                </div>
                <button class="mcl" onclick="App.closeM('modalAgregarFormaFarmaceutica')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <form id="formAgregarForma" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="agregar_forma">

                    <div class="fg">
                        <label class="fl req">Nombre</label>
                        <input class="inp" type="text" name="nombre_forma" id="nombre_forma" required maxlength="250">
                    </div>

                    <div class="fg">
                        <label class="fl">Imagen</label>
                        <input class="inp" type="file" name="imgLoad_forma" id="imgLoad_forma" accept="image/*">
                    </div>

                    <div class="fg" style="text-align: center;">
                        <img id="img-pic-forma" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                            onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                    </div>
                </form>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalAgregarFormaFarmaceutica')">Cancelar</button>
                <button type="submit" form="formAgregarForma" class="btn btn-def">
                    <ion-icon name="save-outline"></ion-icon> Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Forma Farmacéutica -->
    <div class="mov" id="modalEditarFormaFarmaceutica">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Editar Forma Farmacéutica</div>
                    <div class="ms">Actualice los datos de la forma farmacéutica seleccionada</div>
                </div>
                <button class="mcl" onclick="App.closeM('modalEditarFormaFarmaceutica')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <form id="formEditarForma" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="actualizar_forma">
                    <input type="hidden" name="id_forma_edit" id="id_forma_edit">
                    <input type="hidden" name="imagen_antigua_forma_edit" id="imagen_antigua_forma_edit">

                    <div class="fg">
                        <label class="fl req">Nombre</label>
                        <input class="inp" type="text" name="nombre_forma_edit" id="nombre_forma_edit" required maxlength="250">
                    </div>

                    <div class="fg">
                        <label class="fl">Imagen</label>
                        <input class="inp" type="file" name="imgLoad_forma_edit" id="imgLoad_forma_edit" accept="image/*">
                    </div>

                    <div class="fg" style="text-align: center;">
                        <img id="img-pic-forma-edit" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                            style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                            onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                    </div>
                </form>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalEditarFormaFarmaceutica')">Cancelar</button>
                <button type="submit" form="formEditarForma" class="btn btn-def">
                    <ion-icon name="save-outline"></ion-icon> Actualizar
                </button>
            </div>
        </div>
    </div>
    <script src="<?php echo SERVER_URL; ?>views/script/categoriaLista-view.js"></script>


<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
