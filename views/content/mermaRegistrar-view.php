<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

    $rol_usuario = $_SESSION['rol_smp'];
    $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/mermaRegistrarAjax.php"
        data-ajax-param="mermaRegistrarAjax"
        data-ajax-action="listar"
        data-ajax-registros="10">

        <div class="title">
            <h2>
                <ion-icon name="alert-circle-outline"></ion-icon> Registrar Merma - Lotes con Riesgo de Caducidad
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">
                    <small>Tipo de Lote</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todos los tipos</option>
                        <option value="caducado">Caducados (Hoy o Anterior)</option>
                        <option value="proximo">Próximos a Vencer (1 - 10 días)</option>
                    </select>
                </div>

                <?php if ($rol_usuario == 1) { ?>
                    <div class="form-fechas">
                        <small>Sucursal</small>
                        <select class="select-filtro" name="select2">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por medicamento o lote...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>

            </div>
        </form>

        <div class="tabla-contenedor"></div>

    </div>

    <div class="modal" id="modalMermaRegistro" style="display: none;">
        <div class="modal-content" style="width: 500px;">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="alert-circle-outline"></ion-icon>
                    Registrar Merma de Lote Caducado
                </div>
                <a class="close" onclick="cerrarModalMermaRegistro()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <form id="formMermaRegistro" class="form">
                <div class="modal-group">
                    <label>Medicamento:</label>
                    <input type="text" id="medicamentoNombre" readonly style="background-color: #f5f5f5;">
                    <input type="hidden" id="lm_id" name="lm_id">

                    <label style="margin-top: 15px;">Total de Unidades Caducadas:</label>
                    <input type="text" id="cantidadDisponible" readonly style="background-color: #f5f5f5;">
                    <small style="color: #666;">Se registrarán TODAS las unidades del lote como merma</small>

                    <label style="margin-top: 15px;">Motivo de la Merma:</label>
                    <textarea id="me_motivo" name="me_motivo" required placeholder="Ej: Producto caducado, Vencimiento próximo, Daño físico, etc." style="width: 100%; height: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>
                </div>

                <div class="modal-btn-content">
                    <a href="javascript:void(0)" class="btn default" onclick="cerrarModalMermaRegistro()">
                        Cancelar
                    </a>
                    <button type="button" class="btn success" onclick="guardarMermaRegistro()">
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Registrar Todas las Unidades
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let mermaIdActual = null;
        let cantidadMaxActual = 0;

        function abrirModalMermaRegistro(lm_id, medicamento, cantidad_disponible) {
            mermaIdActual = lm_id;
            cantidadMaxActual = cantidad_disponible;

            document.getElementById('lm_id').value = lm_id;
            document.getElementById('medicamentoNombre').value = medicamento;
            document.getElementById('cantidadDisponible').value = cantidad_disponible + ' unidades';
            document.getElementById('me_motivo').value = '';

            document.getElementById('modalMermaRegistro').style.display = 'flex';
        }

        function cerrarModalMermaRegistro() {
            document.getElementById('modalMermaRegistro').style.display = 'none';
            mermaIdActual = null;
        }

        function guardarMermaRegistro() {
            const lm_id = document.getElementById('lm_id').value;
            const me_motivo = document.getElementById('me_motivo').value;

            if (!lm_id || !me_motivo) {
                Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('lm_id', lm_id);
            formData.append('me_cantidad', cantidadMaxActual);
            formData.append('me_motivo', me_motivo);
            formData.append('mermaRegistrarAjax', 'crear');

            fetch('<?php echo SERVER_URL; ?>ajax/mermaRegistrarAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.Alerta === 'redireccionar') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Merma Registrada',
                        text: 'Se registraron ' + cantidadMaxActual + ' unidades como merma',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = data.URL;
                    });
                } else if (data.Alerta === 'simple') {
                    Swal.fire(data.Titulo, data.Texto, data.Tipo);
                }
                cerrarModalMermaRegistro();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Ocurrió un error al procesar la merma', 'error');
                cerrarModalMermaRegistro();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalMermaRegistro');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        cerrarModalMermaRegistro();
                    }
                });
            }
        });
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
