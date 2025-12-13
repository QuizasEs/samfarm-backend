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
        data-ajax-url="ajax/mermaAjax.php"
        data-ajax-param="mermaAjax"
        data-ajax-action="listar"
        data-ajax-registros="15">

        <div class="title">
            <h2>
                <ion-icon name="archive-outline"></ion-icon> Historial de Mermas Registradas
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">
                    <small>Desde</small>
                    <input type="date" name="fecha_desde" placeholder="Selecciona fecha desde">
                </div>

                <div class="form-fechas">
                    <small>Hasta</small>
                    <input type="date" name="fecha_hasta" placeholder="Selecciona fecha hasta">
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

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
