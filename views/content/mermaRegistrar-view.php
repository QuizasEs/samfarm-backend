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

    <script src="<?php echo SERVER_URL; ?>views/script/mermaRegistrar-view.js"></script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>