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
    <!-- uso farmacologico  -->
    <script>
        function abrirModalAgregarUsoFarmacologico() {
            document.getElementById('nombre_uso').value = '';
            document.getElementById('imgLoad_uso').value = '';
            document.getElementById('img-pic-uso').src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
            App.showM('modalAgregarUsoFarmacologico');
        }

        function cerrarModalAgregarUsoFarmacologico() {
            App.closeM('modalAgregarUsoFarmacologico');
        }

        async function abrirModalEditarUsoFarmacologico(id) {
            try {
                const formData = new FormData();
                formData.append('categoriaAjax', 'obtener_uso');
                formData.append('id', id);

                const response = await fetch('<?php echo SERVER_URL; ?>ajax/categoriaAjax.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.error) {
                    Swal.fire('Error', data.error, 'error');
                    return;
                }

                document.getElementById('id_uso_edit').value = data.uf_id;
                document.getElementById('nombre_uso_edit').value = data.uf_nombre;
                document.getElementById('imagen_antigua_edit').value = data.uf_imagen || '';

                const tieneImagen = data.uf_imagen && data.uf_imagen.trim().length > 10;
                const imgElement = document.getElementById('img-pic-uso-edit');

                if (tieneImagen) {
                    imgElement.src = data.uf_imagen;
                    imgElement.onerror = function() {
                        this.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                        this.onerror = null;
                    };
                } else {
                    imgElement.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                }

                document.getElementById('imgLoad_uso_edit').value = '';

                App.showM('modalEditarUsoFarmacologico');

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos', 'error');
            }
        }

        function cerrarModalEditarUsoFarmacologico() {
            App.closeM('modalEditarUsoFarmacologico');
        }

        function cambiarEstadoUsoFarmacologico(id, nuevoEstado) {
            Swal.fire({
                title: nuevoEstado == 1 ? '¿Activar uso farmacológico?' : '¿Desactivar uso farmacológico?',
                text: 'Esta acción cambiará el estado del registro',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado == 1 ? '#28a745' : '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append('categoriaAjax', 'cambiar_estado_uso');
                        formData.append('id', id);
                        formData.append('estado', nuevoEstado);

                        const response = await fetch('<?php echo SERVER_URL; ?>ajax/categoriaAjax.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        await Swal.fire({
                            title: data.Titulo,
                            text: data.texto,
                            icon: data.Tipo
                        });

                        if (data.Alerta === 'recargar') {
                            document.querySelector('[data-ajax-action="listar_uso"]').querySelector('.btn-search')?.click();
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const inputFile = document.getElementById('imgLoad_uso');
            const imgPic = document.getElementById('img-pic-uso');

            if (inputFile && imgPic) {
                inputFile.onchange = function() {
                    if (inputFile.files && inputFile.files[0]) {
                        if (inputFile.files[0].size > 5 * 1024 * 1024) {
                            alert('El archivo es muy grande. Máximo 5MB.');
                            inputFile.value = '';
                            imgPic.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imgPic.src = e.target.result;
                        };
                        reader.readAsDataURL(inputFile.files[0]);
                    }
                };
            }

            const inputFileEdit = document.getElementById('imgLoad_uso_edit');
            const imgPicEdit = document.getElementById('img-pic-uso-edit');

            if (inputFileEdit && imgPicEdit) {
                inputFileEdit.onchange = function() {
                    if (inputFileEdit.files && inputFileEdit.files[0]) {
                        if (inputFileEdit.files[0].size > 5 * 1024 * 1024) {
                            alert('El archivo es muy grande. Máximo 5MB.');
                            inputFileEdit.value = '';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imgPicEdit.src = e.target.result;
                        };
                        reader.readAsDataURL(inputFileEdit.files[0]);
                    }
                };
            }


        });
    </script>
    <!-- via de administracion script -->
    <script>
        function abrirModalAgregarViaAdministracion() {
            document.getElementById('nombre_via').value = '';
            document.getElementById('imgLoad_via').value = '';
            document.getElementById('img-pic-via').src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
            App.showM('modalAgregarViaAdministracion');
        }

        function cerrarModalAgregarViaAdministracion() {
            App.closeM('modalAgregarViaAdministracion');
        }

        async function abrirModalEditarViaAdministracion(id) {
            try {
                const formData = new FormData();
                formData.append('categoriaAjax', 'obtener_via');
                formData.append('id', id);

                const response = await fetch('<?php echo SERVER_URL; ?>ajax/categoriaAjax.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.error) {
                    Swal.fire('Error', data.error, 'error');
                    return;
                }

                document.getElementById('id_via_edit').value = data.vd_id;
                document.getElementById('nombre_via_edit').value = data.vd_nombre;
                document.getElementById('imagen_antigua_edit_via').value = data.vd_imagen || '';

                const tieneImagen = data.vd_imagen && data.vd_imagen.trim().length > 10;
                const imgElement = document.getElementById('img-pic-via-edit');

                if (tieneImagen) {
                    imgElement.src = data.vd_imagen;
                    imgElement.onerror = function() {
                        this.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                        this.onerror = null;
                    };
                } else {
                    imgElement.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                }

                document.getElementById('imgLoad_via_edit').value = '';

                App.showM('modalEditarViaAdministracion');

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos', 'error');
            }
        }

        function cerrarModalEditarViaAdministracion() {
            App.closeM('modalEditarViaAdministracion');
        }

        function cambiarEstadoViaAdministracion(id, nuevoEstado) {
            Swal.fire({
                title: nuevoEstado == 1 ? '¿Activar vía de administración?' : '¿Desactivar vía de administración?',
                text: 'Esta acción cambiará el estado del registro',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado == 1 ? '#28a745' : '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append('categoriaAjax', 'cambiar_estado_via');
                        formData.append('id', id);
                        formData.append('estado', nuevoEstado);

                        const response = await fetch('<?php echo SERVER_URL; ?>ajax/categoriaAjax.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        await Swal.fire({
                            title: data.Titulo,
                            text: data.texto,
                            icon: data.Tipo
                        });

                        if (data.Alerta === 'recargar') {
                            document.querySelector('[data-ajax-action="listar_via"]').querySelector('.btn-search')?.click();
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const inputFile = document.getElementById('imgLoad_via');
            const imgPic = document.getElementById('img-pic-via');

            if (inputFile && imgPic) {
                inputFile.onchange = function() {
                    if (inputFile.files && inputFile.files[0]) {
                        if (inputFile.files[0].size > 5 * 1024 * 1024) {
                            alert('El archivo es muy grande. Máximo 5MB.');
                            inputFile.value = '';
                            imgPic.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imgPic.src = e.target.result;
                        };
                        reader.readAsDataURL(inputFile.files[0]);
                    }
                };
            }

            const inputFileEdit = document.getElementById('imgLoad_via_edit');
            const imgPicEdit = document.getElementById('img-pic-via-edit');

            if (inputFileEdit && imgPicEdit) {
                inputFileEdit.onchange = function() {
                    if (inputFileEdit.files && inputFileEdit.files[0]) {
                        if (inputFileEdit.files[0].size > 5 * 1024 * 1024) {
                            alert('El archivo es muy grande. Máximo 5MB.');
                            inputFileEdit.value = '';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imgPicEdit.src = e.target.result;
                        };
                        reader.readAsDataURL(inputFileEdit.files[0]);
                    }
                };
            }


        });
    </script>

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
    <!-- forma farmaceutica script -->
    <script>
        function abrirModalAgregarFormaFarmaceutica() {
            document.getElementById('nombre_forma').value = '';
            document.getElementById('imgLoad_forma').value = '';
            document.getElementById('img-pic-forma').src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
            App.showM('modalAgregarFormaFarmaceutica');
        }

        function cerrarModalAgregarFormaFarmaceutica() {
            App.closeM('modalAgregarFormaFarmaceutica');
        }

        async function abrirModalEditarFormaFarmaceutica(id) {
            try {
                const formData = new FormData();
                formData.append('categoriaAjax', 'obtener_forma');
                formData.append('id', id);

                const response = await fetch('<?php echo SERVER_URL; ?>ajax/categoriaAjax.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.error) {
                    Swal.fire('Error', data.error, 'error');
                    return;
                }

                document.getElementById('id_forma_edit').value = data.ff_id;
                document.getElementById('nombre_forma_edit').value = data.ff_nombre;
                document.getElementById('imagen_antigua_forma_edit').value = data.ff_imagen || '';

                const tieneImagen = data.ff_imagen && data.ff_imagen.trim().length > 10;
                const imgElement = document.getElementById('img-pic-forma-edit');

                if (tieneImagen) {
                    imgElement.src = data.ff_imagen;
                    imgElement.onerror = function() {
                        this.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                        this.onerror = null;
                    };
                } else {
                    imgElement.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                }

                document.getElementById('imgLoad_forma_edit').value = '';

                App.showM('modalEditarFormaFarmaceutica');

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos', 'error');
            }
        }

        function cerrarModalEditarFormaFarmaceutica() {
            App.closeM('modalEditarFormaFarmaceutica');
        }

        function cambiarEstadoFormaFarmaceutica(id, nuevoEstado) {
            Swal.fire({
                title: nuevoEstado == 1 ? '¿Activar forma farmacéutica?' : '¿Desactivar forma farmacéutica?',
                text: 'Esta acción cambiará el estado del registro',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: nuevoEstado == 1 ? '#28a745' : '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const formData = new FormData();
                        formData.append('categoriaAjax', 'cambiar_estado_forma');
                        formData.append('id', id);
                        formData.append('estado', nuevoEstado);

                        const response = await fetch('<?php echo SERVER_URL; ?>ajax/categoriaAjax.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        await Swal.fire({
                            title: data.Titulo,
                            text: data.texto,
                            icon: data.Tipo
                        });

                        if (data.Alerta === 'recargar') {
                            document.querySelector('[data-ajax-action="listar_forma"]').querySelector('.btn-search')?.click();
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const inputFile = document.getElementById('imgLoad_forma');
            const imgPic = document.getElementById('img-pic-forma');

            if (inputFile && imgPic) {
                inputFile.onchange = function() {
                    if (inputFile.files && inputFile.files[0]) {
                        if (inputFile.files[0].size > 5 * 1024 * 1024) {
                            alert('El archivo es muy grande. Máximo 5MB.');
                            inputFile.value = '';
                            imgPic.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imgPic.src = e.target.result;
                        };
                        reader.readAsDataURL(inputFile.files[0]);
                    }
                };
            }

            const inputFileEdit = document.getElementById('imgLoad_forma_edit');
            const imgPicEdit = document.getElementById('img-pic-forma-edit');

            if (inputFileEdit && imgPicEdit) {
                inputFileEdit.onchange = function() {
                    if (inputFileEdit.files && inputFileEdit.files[0]) {
                        if (inputFileEdit.files[0].size > 5 * 1024 * 1024) {
                            alert('El archivo es muy grande. Máximo 5MB.');
                            inputFileEdit.value = '';
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imgPicEdit.src = e.target.result;
                        };
                        reader.readAsDataURL(inputFileEdit.files[0]);
                    }
                };
            }


        });
    </script>


<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
