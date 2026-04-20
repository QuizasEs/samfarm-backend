<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

    $rol_usuario = $_SESSION['rol_smp'];
    $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/mermaRegistrarAjax.php"
        data-ajax-param="mermaRegistrarAjax"
        data-ajax-action="listar"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="alert-circle-outline"></ion-icon> Registrar Merma
                </div>
                <div class="psub">Lotes con riesgo de caducidad</div>
            </div>
        </div>

        <div class="card mb20">
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Tipo de Lote</label>
                            <select class="sel" name="select1">
                                <option value="">Todos los tipos</option>
                                <option value="caducado">Caducados (Hoy o Anterior)</option>
                                <option value="proximo">Próximos a Vencer (1 - 10 días)</option>
                            </select>
                        </div>

                        <?php if ($rol_usuario == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal</label>
                                <select class="sel" name="select2">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>

                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input type="text" class="inp" name="busqueda" placeholder="Buscar por medicamento o lote...">
                                <button type="button" class="btn btn-def">
                                    <ion-icon name="search-outline"></ion-icon> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="tabla-contenedor"></div>
        </div>

    </div>

    <div class="mov" id="modalMermaRegistro">
        <div class="modal msm">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="alert-circle-outline"></ion-icon> Registrar Merma de Lote
                    </div>
                </div>
                <button class="mcl" onclick="cerrarModalMermaRegistro()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <form id="formMermaRegistro" class="mb">
                <input type="hidden" id="lm_id" name="lm_id">
                
                <div class="fg">
                    <label class="fl">Medicamento</label>
                    <input type="text" class="inp" id="medicamentoNombre" readonly>
                </div>

                <div class="fg">
                    <label class="fl">Total de Unidades</label>
                    <input type="text" class="inp" id="cantidadDisponible" readonly>
                    <div class="fh">Se registrarán TODAS las unidades del lote como merma</div>
                </div>

                <div class="fg">
                    <label class="fl">Motivo de la Merma <span class="tdan">*</span></label>
                    <textarea class="ta" id="me_motivo" name="me_motivo" required placeholder="Ej: Producto caducado, Vencimiento próximo, Daño físico, etc."></textarea>
                </div>
            </form>

            <div class="mf">
                <button type="button" class="btn btn-sec" onclick="cerrarModalMermaRegistro()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-dan" onclick="guardarMermaRegistro()">
                    <ion-icon name="checkmark-circle-outline"></ion-icon> Registrar Merma
                </button>
            </div>
        </div>
    </div>

    <script>
    /* gestor del modulo de registro de merma */
    const MermaRegistroManager = (function() {
        'use strict';

        let estado = {
            lm_id: null,
            cantidad: 0,
            apiUrl: '<?php echo SERVER_URL; ?>ajax/mermaRegistrarAjax.php'
        };

        const elementos = {
            modal: null,
            form: null,
            inputLmId: null,
            inputMedicamento: null,
            inputCantidad: null,
            inputMotivo: null
        };

        /* inicializa el modulo */
        function init() {
            cargarElementos();
            registrarEventos();
        }

        /* carga referencias a elementos del dom */
        function cargarElementos() {
            elementos.modal = document.getElementById('modalMermaRegistro');
            elementos.form = document.getElementById('formMermaRegistro');
            elementos.inputLmId = document.getElementById('lm_id');
            elementos.inputMedicamento = document.getElementById('medicamentoNombre');
            elementos.inputCantidad = document.getElementById('cantidadDisponible');
            elementos.inputMotivo = document.getElementById('me_motivo');
        }

        /* registra todos los eventos del modulo */
        function registrarEventos() {
        }

        /* abre el modal de registro de merma */
        function abrirModal(lm_id, medicamento, cantidad_disponible) {
            estado.lm_id = lm_id;
            estado.cantidad = cantidad_disponible;

            elementos.inputLmId.value = lm_id;
            elementos.inputMedicamento.value = medicamento;
            elementos.inputCantidad.value = cantidad_disponible + ' unidades';
            elementos.inputMotivo.value = '';

            elementos.modal.classList.add('open');
        }

        /* cierra el modal y limpia el estado */
        function cerrarModal() {
            elementos.modal.classList.remove('open');
            
            estado.lm_id = null;
            estado.cantidad = 0;
        }

        /* valida los campos del formulario */
        function validarFormulario() {
            const motivo = elementos.inputMotivo.value.trim();
            
            if (!estado.lm_id || !motivo) {
                Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
                return false;
            }

            return true;
        }

        /* procesa el envio del formulario */
        async function guardar() {
            if (!validarFormulario()) return;

            const formData = new FormData();
            formData.append('lm_id', estado.lm_id);
            formData.append('me_cantidad', estado.cantidad);
            formData.append('me_motivo', elementos.inputMotivo.value.trim());
            formData.append('mermaRegistrarAjax', 'crear');

            try {
                const response = await fetch(estado.apiUrl, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.Alerta === 'redireccionar') {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Merma Registrada',
                        text: 'Se registraron ' + estado.cantidad + ' unidades como merma',
                        confirmButtonText: 'Aceptar'
                    });
                    
                    window.location.href = data.URL;
                } else if (data.Alerta === 'simple') {
                    Swal.fire(data.Titulo, data.Texto, data.Tipo);
                }

                cerrarModal();

            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Ocurrio un error al procesar la merma', 'error');
                cerrarModal();
            }
        }

        document.addEventListener('DOMContentLoaded', init);

        return {
            abrirModal,
            cerrarModal,
            guardar
        };

    })();

    // exponer funciones globales para compatibilidad con onclick desde la tabla
    function abrirModalMermaRegistro(lm_id, medicamento, cantidad_disponible) {
        MermaRegistroManager.abrirModal(lm_id, medicamento, cantidad_disponible);
    }

    function cerrarModalMermaRegistro() {
        MermaRegistroManager.cerrarModal();
    }

    function guardarMermaRegistro() {
        MermaRegistroManager.guardar();
    }
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>