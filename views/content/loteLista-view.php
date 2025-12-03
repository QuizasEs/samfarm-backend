<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    /* en caso que el rol del usuario este en admin o genrente */
    require_once "./controllers/MedicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/loteAjax.php"
        data-ajax-param="loteAjax"
        data-ajax-registros="10">
        <div class="title">
            <h2>
                <ion-icon name="bandage-outline"></ion-icon> Lotes de medicamentos
            </h2>
        </div>
        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">

                    <small>Estados</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todos los estados</option>
                        <option value="en_espera">En Espera</option>
                        <option value="activo">Activo</option>
                        <option value="terminado">Terminado</option>
                        <option value="caducado">Caducado</option>
                        <option value="devuelto">Devuelto</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>
                <div class="form-fechas">
                    <small>Meses</small>
                    <select class="select-filtro" name="select2" id="">
                        <option value="">Mes</option>
                        <option value="1">Enero</option>
                        <option value="2">Febrero</option>
                        <option value="3">Marzo</option>
                        <option value="4">Abril</option>
                        <option value="5">Mayo</option>
                        <option value="6">Junio</option>
                        <option value="7">Julio</option>
                        <option value="8">Agosto</option>
                        <option value="9">Septiempre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <!-- select de sucursales solo para administrador -->
                <div class="form-fechas">

                    <small>Sucursales</small>
                    <?php if ($_SESSION['rol_smp'] == 1) { ?>
                        <select class="select-filtro" name="select3" id="">
                            <option value="">Sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>
                <!-- Fechas -->
                <div class="form-fechas">
                    <small>
                        Desde
                    </small>
                    <input type="date" name="fecha_desde" placeholder="Desde" title="Fecha desde">
                </div>
                <div class="form-fechas">
                    <small>
                        Hasta
                    </small>
                    <input type="date" name="fecha_hasta" placeholder="Hasta" title="Fecha hasta">
                </div>

                <div class="search">
                    <!-- Búsqueda -->
                    <input type="text" name="busqueda" placeholder="Buscar por nombre o principio activo...">

                    <button type="button" class="btn-search">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
            </div>
        </form>

        <div class="tabla-contenedor"></div>
    </div>


    <!-- modal -->
    <!-- Modal Activar Lote -->
    <div id="modalActivarLote" class="modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">📦<span> Activar lote</span></div>
                <a class="close" onclick="cerrarModal()"><ion-icon name="close-outline"></ion-icon></a>
            </div>
            <div class="modal-group">
                <h3>Activar lote</h3>

                <div class="row">
                    <div id="detalleLote"></div>

                </div>
                <div class="row">
                    <div class="modal-info">

                        <p class="info">
                            <strong>Atención:</strong> La activación del lote solo puede hacerse una vez.
                            Luego la edición será limitada.
                        </p>
                    </div>
                </div>
                <div class="row">
                    <button id="btnConfirmarActivacion" class="btn success">Activar</button>
                    <button class="modal-close btn warning">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

<?php } else {
    echo "que miras bobo";
?>
    <!-- en caso que no tenga el rol determinado -->
    <!-- eliminar sesion -->

<?php } ?>