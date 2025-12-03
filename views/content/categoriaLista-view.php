<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div id="tabla-uso-farmacologico" class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/categoriaAjax.php"
        data-ajax-param="categoriaAjax"
        data-ajax-registros="6"
        data-ajax-action="listar_uso">
        <div class="title">
            <h2>
                <ion-icon name="fitness-outline"></ion-icon> Uso Farmacológico
            </h2>

        </div>
        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por nombre...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
                <div class="filtro-dinamico-buttons">
                    <button type="button" class="btn success" onclick="abrirModalAgregarUsoFarmacologico()">
                        <ion-icon name="add-circle-outline"></ion-icon> Nuevo Uso
                    </button>
                </div>
            </div>
        </form>
        <div class="tabla-contenedor"></div>
    </div>

    <div id="modalAgregarUsoFarmacologico" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Agregar Uso Farmacológico</span>
                </div>
                <a class="close" onclick="cerrarModalAgregarUsoFarmacologico()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="agregar_uso">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_uso" class="required">Nombre</label>
                                <input type="text" name="nombre_uso" id="nombre_uso" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_uso">Imagen</label>
                                <input type="file" name="imgLoad_uso" id="imgLoad_uso" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-uso" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalAgregarUsoFarmacologico()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditarUsoFarmacologico" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="create-outline"></ion-icon>
                    <span>Editar Uso Farmacológico</span>
                </div>
                <a class="close" onclick="cerrarModalEditarUsoFarmacologico()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="actualizar_uso">
                    <input type="hidden" name="id_uso_edit" id="id_uso_edit">
                    <input type="hidden" name="imagen_antigua_edit" id="imagen_antigua_edit">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_uso_edit" class="required">Nombre</label>
                                <input type="text" name="nombre_uso_edit" id="nombre_uso_edit" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_uso_edit">Imagen</label>
                                <input type="file" name="imgLoad_uso_edit" id="imgLoad_uso_edit" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-uso-edit" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalEditarUsoFarmacologico()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- via de administracion -->
    <div id="tabla-via-administracion" class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/categoriaAjax.php"
        data-ajax-param="categoriaAjax"
        data-ajax-registros="6"
        data-ajax-action="listar_via">
        <div class="title">
            <h2>
                <ion-icon name="medkit-outline"></ion-icon> Vía de Administración
            </h2>

        </div>
        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por nombre...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
            <div class="filtro-dinamico-buttons">
                <button type="button" class="btn success" onclick="abrirModalAgregarViaAdministracion()">
                    <ion-icon name="add-circle-outline"></ion-icon> Nueva Vía
                </button>
            </div>
        </form>
        <div class="tabla-contenedor"></div>
    </div>

    <div id="modalAgregarViaAdministracion" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Agregar Vía de Administración</span>
                </div>
                <a class="close" onclick="cerrarModalAgregarViaAdministracion()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="agregar_via">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_via" class="required">Nombre</label>
                                <input type="text" name="nombre_via" id="nombre_via" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_via">Imagen</label>
                                <input type="file" name="imgLoad_via" id="imgLoad_via" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-via" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalAgregarViaAdministracion()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditarViaAdministracion" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="create-outline"></ion-icon>
                    <span>Editar Vía de Administración</span>
                </div>
                <a class="close" onclick="cerrarModalEditarViaAdministracion()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="actualizar_via">
                    <input type="hidden" name="id_via_edit" id="id_via_edit">
                    <input type="hidden" name="imagen_antigua_edit_via" id="imagen_antigua_edit_via">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_via_edit" class="required">Nombre</label>
                                <input type="text" name="nombre_via_edit" id="nombre_via_edit" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_via_edit">Imagen</label>
                                <input type="file" name="imgLoad_via_edit" id="imgLoad_via_edit" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-via-edit" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalEditarViaAdministracion()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- uso farmacologico  -->
    <script>
        function abrirModalAgregarUsoFarmacologico() {
            const modal = document.getElementById('modalAgregarUsoFarmacologico');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('nombre_uso').value = '';
                document.getElementById('imgLoad_uso').value = '';
                document.getElementById('img-pic-uso').src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
            }
        }

        function cerrarModalAgregarUsoFarmacologico() {
            const modal = document.getElementById('modalAgregarUsoFarmacologico');
            if (modal) modal.style.display = 'none';
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

                const modal = document.getElementById('modalEditarUsoFarmacologico');
                if (modal) modal.style.display = 'flex';

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos', 'error');
            }
        }

        function cerrarModalEditarUsoFarmacologico() {
            const modal = document.getElementById('modalEditarUsoFarmacologico');
            if (modal) modal.style.display = 'none';
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
                            document.getElementById('tabla-uso-farmacologico').querySelector('.btn-search')?.click();
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

            document.addEventListener('click', function(e) {
                const modalAgregar = document.getElementById('modalAgregarUsoFarmacologico');
                const modalEditar = document.getElementById('modalEditarUsoFarmacologico');

                if (modalAgregar && e.target === modalAgregar) {
                    cerrarModalAgregarUsoFarmacologico();
                }
                if (modalEditar && e.target === modalEditar) {
                    cerrarModalEditarUsoFarmacologico();
                }
            });
        });
    </script>
    <!-- via de administracion script -->
    <script>
        function abrirModalAgregarViaAdministracion() {
            const modal = document.getElementById('modalAgregarViaAdministracion');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('nombre_via').value = '';
                document.getElementById('imgLoad_via').value = '';
                document.getElementById('img-pic-via').src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
            }
        }

        function cerrarModalAgregarViaAdministracion() {
            const modal = document.getElementById('modalAgregarViaAdministracion');
            if (modal) modal.style.display = 'none';
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

                const modal = document.getElementById('modalEditarViaAdministracion');
                if (modal) modal.style.display = 'flex';

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos', 'error');
            }
        }

        function cerrarModalEditarViaAdministracion() {
            const modal = document.getElementById('modalEditarViaAdministracion');
            if (modal) modal.style.display = 'none';
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
                            document.getElementById('tabla-via-administracion').querySelector('.btn-search')?.click();
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

            document.addEventListener('click', function(e) {
                const modalAgregar = document.getElementById('modalAgregarViaAdministracion');
                const modalEditar = document.getElementById('modalEditarViaAdministracion');

                if (modalAgregar && e.target === modalAgregar) {
                    cerrarModalAgregarViaAdministracion();
                }
                if (modalEditar && e.target === modalEditar) {
                    cerrarModalEditarViaAdministracion();
                }
            });
        });
    </script>

    <div id="tabla-forma-farmaceutica" class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/categoriaAjax.php"
        data-ajax-param="categoriaAjax"
        data-ajax-registros="6"
        data-ajax-action="listar_forma">
        <div class="title">
            <h2>
                <ion-icon name="flask-outline"></ion-icon> Forma Farmacéutica
            </h2>

        </div>
        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por nombre...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
            <button type="button" class="btn success" onclick="abrirModalAgregarFormaFarmaceutica()">
                <ion-icon name="add-circle-outline"></ion-icon> Nueva Forma
            </button>
        </form>
        <div class="tabla-contenedor"></div>
    </div>

    <div id="modalAgregarFormaFarmaceutica" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Agregar Forma Farmacéutica</span>
                </div>
                <a class="close" onclick="cerrarModalAgregarFormaFarmaceutica()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="agregar_forma">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_forma" class="required">Nombre</label>
                                <input type="text" name="nombre_forma" id="nombre_forma" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_forma">Imagen</label>
                                <input type="file" name="imgLoad_forma" id="imgLoad_forma" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-forma" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalAgregarFormaFarmaceutica()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditarFormaFarmaceutica" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="create-outline"></ion-icon>
                    <span>Editar Forma Farmacéutica</span>
                </div>
                <a class="close" onclick="cerrarModalEditarFormaFarmaceutica()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="actualizar_forma">
                    <input type="hidden" name="id_forma_edit" id="id_forma_edit">
                    <input type="hidden" name="imagen_antigua_forma_edit" id="imagen_antigua_forma_edit">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_forma_edit" class="required">Nombre</label>
                                <input type="text" name="nombre_forma_edit" id="nombre_forma_edit" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_forma_edit">Imagen</label>
                                <input type="file" name="imgLoad_forma_edit" id="imgLoad_forma_edit" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-forma-edit" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalEditarFormaFarmaceutica()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- forma farmaceutica script -->
    <script>
        function abrirModalAgregarFormaFarmaceutica() {
            const modal = document.getElementById('modalAgregarFormaFarmaceutica');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('nombre_forma').value = '';
                document.getElementById('imgLoad_forma').value = '';
                document.getElementById('img-pic-forma').src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
            }
        }

        function cerrarModalAgregarFormaFarmaceutica() {
            const modal = document.getElementById('modalAgregarFormaFarmaceutica');
            if (modal) modal.style.display = 'none';
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

                const modal = document.getElementById('modalEditarFormaFarmaceutica');
                if (modal) modal.style.display = 'flex';

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos', 'error');
            }
        }

        function cerrarModalEditarFormaFarmaceutica() {
            const modal = document.getElementById('modalEditarFormaFarmaceutica');
            if (modal) modal.style.display = 'none';
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
                            document.getElementById('tabla-forma-farmaceutica').querySelector('.btn-search')?.click();
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

            document.addEventListener('click', function(e) {
                const modalAgregar = document.getElementById('modalAgregarFormaFarmaceutica');
                const modalEditar = document.getElementById('modalEditarFormaFarmaceutica');

                if (modalAgregar && e.target === modalAgregar) {
                    cerrarModalAgregarFormaFarmaceutica();
                }
                if (modalEditar && e.target === modalEditar) {
                    cerrarModalEditarFormaFarmaceutica();
                }
            });
        });
    </script>

    <div id="tabla-laboratorio" class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/categoriaAjax.php"
        data-ajax-param="categoriaAjax"
        data-ajax-registros="6"
        data-ajax-action="listar_laboratorio">
        <div class="title">
            <h2>
                <ion-icon name="flask-beaker-outline"></ion-icon> Laboratorio
            </h2>

        </div>
        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por nombre...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
            <button type="button" class="btn success" onclick="abrirModalAgregarLaboratorio()">
                <ion-icon name="add-circle-outline"></ion-icon> Nuevo Laboratorio
            </button>
        </form>
        <div class="tabla-contenedor"></div>
    </div>

    <div id="modalAgregarLaboratorio" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="add-circle-outline"></ion-icon>
                    <span>Agregar Laboratorio</span>
                </div>
                <a class="close" onclick="cerrarModalAgregarLaboratorio()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="agregar_laboratorio">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_laboratorio" class="required">Nombre</label>
                                <input type="text" name="nombre_laboratorio" id="nombre_laboratorio" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_laboratorio">Logo</label>
                                <input type="file" name="imgLoad_laboratorio" id="imgLoad_laboratorio" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-laboratorio" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalAgregarLaboratorio()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalEditarLaboratorio" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="create-outline"></ion-icon>
                    <span>Editar Laboratorio</span>
                </div>
                <a class="close" onclick="cerrarModalEditarLaboratorio()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>
            <div class="modal-group">
                <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="categoriaAjax" value="actualizar_laboratorio">
                    <input type="hidden" name="id_laboratorio_edit" id="id_laboratorio_edit">
                    <input type="hidden" name="imagen_antigua_laboratorio_edit" id="imagen_antigua_laboratorio_edit">

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="nombre_laboratorio_edit" class="required">Nombre</label>
                                <input type="text" name="nombre_laboratorio_edit" id="nombre_laboratorio_edit" required maxlength="250">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="imgLoad_laboratorio_edit">Logo</label>
                                <input type="file" name="imgLoad_laboratorio_edit" id="imgLoad_laboratorio_edit" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col" style="text-align: center;">
                            <img id="img-pic-laboratorio-edit" src="<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png"
                                style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                                onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'">
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <button type="button" class="btn warning" onclick="cerrarModalEditarLaboratorio()">
                            Cancelar
                        </button>
                        <button type="submit" class="btn success">
                            <ion-icon name="save-outline"></ion-icon> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- script para laboratorios  -->
    <script>
        function abrirModalAgregarLaboratorio() {
            const modal = document.getElementById('modalAgregarLaboratorio');
            if (modal) {
                modal.style.display = 'flex';
                document.getElementById('nombre_laboratorio').value = '';
                document.getElementById('imgLoad_laboratorio').value = '';
                document.getElementById('img-pic-laboratorio').src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
            }
        }

        function cerrarModalAgregarLaboratorio() {
            const modal = document.getElementById('modalAgregarLaboratorio');
            if (modal) modal.style.display = 'none';
        }

        async function abrirModalEditarLaboratorio(id) {
            try {
                const formData = new FormData();
                formData.append('categoriaAjax', 'obtener_laboratorio');
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

                document.getElementById('id_laboratorio_edit').value = data.la_id;
                document.getElementById('nombre_laboratorio_edit').value = data.la_nombre_comercial;
                document.getElementById('imagen_antigua_laboratorio_edit').value = data.la_logo || '';

                const tieneImagen = data.la_logo && data.la_logo.trim().length > 10;
                const imgElement = document.getElementById('img-pic-laboratorio-edit');

                if (tieneImagen) {
                    imgElement.src = data.la_logo;
                    imgElement.onerror = function() {
                        this.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                        this.onerror = null;
                    };
                } else {
                    imgElement.src = '<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png';
                }

                document.getElementById('imgLoad_laboratorio_edit').value = '';

                const modal = document.getElementById('modalEditarLaboratorio');
                if (modal) modal.style.display = 'flex';

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo cargar los datos', 'error');
            }
        }

        function cerrarModalEditarLaboratorio() {
            const modal = document.getElementById('modalEditarLaboratorio');
            if (modal) modal.style.display = 'none';
        }

        function cambiarEstadoLaboratorio(id, nuevoEstado) {
            Swal.fire({
                title: nuevoEstado == 1 ? '¿Activar laboratorio?' : '¿Desactivar laboratorio?',
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
                        formData.append('categoriaAjax', 'cambiar_estado_laboratorio');
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
                            document.getElementById('tabla-laboratorio').querySelector('.btn-search')?.click();
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo cambiar el estado', 'error');
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const inputFile = document.getElementById('imgLoad_laboratorio');
            const imgPic = document.getElementById('img-pic-laboratorio');

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

            const inputFileEdit = document.getElementById('imgLoad_laboratorio_edit');
            const imgPicEdit = document.getElementById('img-pic-laboratorio-edit');

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

            document.addEventListener('click', function(e) {
                const modalAgregar = document.getElementById('modalAgregarLaboratorio');
                const modalEditar = document.getElementById('modalEditarLaboratorio');

                if (modalAgregar && e.target === modalAgregar) {
                    cerrarModalAgregarLaboratorio();
                }
                if (modalEditar && e.target === modalEditar) {
                    cerrarModalEditarLaboratorio();
                }
            });
        });
    </script>
<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="ban-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>