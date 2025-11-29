<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

<div class="container tabla-dinamica"
    data-ajax-table="true"
    data-ajax-url="ajax/categoriaAjax.php"
    data-ajax-param="categoriaAjax"
    data-ajax-registros="10"
    data-ajax-action="listar_uso">
    <div class="title">
        <h3>
            <ion-icon name="fitness-outline"></ion-icon> Uso Farmacológico
        </h3>
        <button type="button" class="btn success" onclick="abrirModalAgregarUsoFarmacologico()">
            <ion-icon name="add-circle-outline"></ion-icon> Nuevo Uso
        </button>
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
            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="save" autocomplete="off">
                <input type="hidden" name="categoriaAjax" value="agregar_uso">
                <input type="hidden" name="imagen_uso_base64" id="imagen_uso_base64">
                
                <div class="row">
                    <div class="col">
                        <label for="nombre_uso" class="required">Nombre</label>
                        <input type="text" name="nombre_uso" id="nombre_uso" required maxlength="250">
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label for="imgLoad_uso">Imagen</label>
                        <input type="file" name="imgLoad_uso" id="imgLoad_uso" accept="image/*">
                    </div>
                </div>

                <div class="row">
                    <div class="col" style="text-align: center;">
                        <img id="img-pic-uso" src="<?php echo SERVER_URL; ?>views/asset/img/predeterminado.png" 
                             style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                             onerror="this.src='<?php echo SERVER_URL; ?>views/asset/img/predeterminado.png'">
                    </div>
                </div>

                <div class="btn-content">
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
            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/categoriaAjax.php" method="POST" data-form="update" autocomplete="off">
                <input type="hidden" name="categoriaAjax" value="actualizar_uso">
                <input type="hidden" name="id_uso_edit" id="id_uso_edit">
                <input type="hidden" name="imagen_uso_base64_edit" id="imagen_uso_base64_edit">
                
                <div class="row">
                    <div class="col">
                        <label for="nombre_uso_edit" class="required">Nombre</label>
                        <input type="text" name="nombre_uso_edit" id="nombre_uso_edit" required maxlength="250">
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label for="imgLoad_uso_edit">Imagen</label>
                        <input type="file" name="imgLoad_uso_edit" id="imgLoad_uso_edit" accept="image/*">
                    </div>
                </div>

                <div class="row">
                    <div class="col" style="text-align: center;">
                        <img id="img-pic-uso-edit" src="<?php echo SERVER_URL; ?>views/asset/img/predeterminado.png" 
                             style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #ddd;"
                             onerror="this.src='<?php echo SERVER_URL; ?>views/asset/img/predeterminado.png'">
                    </div>
                </div>

                <div class="btn-content">
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

<script>
function abrirModalAgregarUsoFarmacologico() {
    const modal = document.getElementById('modalAgregarUsoFarmacologico');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('nombre_uso').value = '';
        document.getElementById('imgLoad_uso').value = '';
        document.getElementById('img-pic-uso').src = '<?php echo SERVER_URL; ?>views/asset/img/predeterminado.png';
        document.getElementById('imagen_uso_base64').value = '';
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
        
        const imgSrc = data.uf_imagen || '<?php echo SERVER_URL; ?>views/asset/img/predeterminado.png';
        document.getElementById('img-pic-uso-edit').src = imgSrc;
        document.getElementById('imagen_uso_base64_edit').value = data.uf_imagen || '';

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
                    document.querySelector('.filtro-dinamico .btn-search')?.click();
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
    const hiddenBase64 = document.getElementById('imagen_uso_base64');

    if (inputFile && imgPic) {
        inputFile.onchange = function() {
            if (inputFile.files && inputFile.files[0]) {
                if (inputFile.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFile.value = '';
                    imgPic.src = '<?php echo SERVER_URL; ?>views/asset/img/predeterminado.png';
                    hiddenBase64.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPic.src = e.target.result;
                    hiddenBase64.value = e.target.result;
                };
                reader.readAsDataURL(inputFile.files[0]);
            }
        };
    }

    const inputFileEdit = document.getElementById('imgLoad_uso_edit');
    const imgPicEdit = document.getElementById('img-pic-uso-edit');
    const hiddenBase64Edit = document.getElementById('imagen_uso_base64_edit');

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
                    hiddenBase64Edit.value = e.target.result;
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

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="ban-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>