<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/proveedoresAjax.php"
        data-ajax-param="proveedoresAjax"
        data-ajax-registros="10">
        <div class="title">
            <h3>
                <ion-icon name="people-outline"></ion-icon> Gestión de Proveedores
            </h3>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">
                    <small>Estado</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Con Compras</small>
                    <select class="select-filtro" name="select2">
                        <option value="">Todos</option>
                        <option value="con_compras">Con compras</option>
                        <option value="sin_compras">Sin compras</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Última Compra</small>
                    <select class="select-filtro" name="select3">
                        <option value="">Todos</option>
                        <option value="7">Últimos 7 días</option>
                        <option value="30">Últimos 30 días</option>
                        <option value="90">Últimos 90 días</option>
                        <option value="mas_90">Más de 90 días</option>
                        <option value="nunca">Nunca</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Desde</small>
                    <input type="date" name="fecha_desde" placeholder="Desde" title="Fecha desde">
                </div>

                <div class="form-fechas">
                    <small>Hasta</small>
                    <input type="date" name="fecha_hasta" placeholder="Hasta" title="Fecha hasta">
                </div>

                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por nombre, NIT o teléfono...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>

            </div>

            <button type="button" class="btn success" id="btnExportarExcel">
                <ion-icon name="download-outline"></ion-icon> Excel
            </button>
        </form>

        <div class="tabla-contenedor"></div>
    </div>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2>⛔ Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>