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
    data-ajax-url="ajax/cajaHistorialAjax.php"
    data-ajax-param="cajaHistorialAjax"
    data-ajax-registros="15">
    
    <div class="title">
        <h3>
            <ion-icon name="cash-outline"></ion-icon> Historial de Movimientos de Caja
        </h3>
    </div>

    <form class="filtro-dinamico">
        <div class="filtro-dinamico-search">

            <div class="form-fechas">
                <small>Desde</small>
                <input type="date" name="fecha_desde" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-fechas">
                <small>Hasta</small>
                <input type="date" name="fecha_hasta" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-fechas">
                <small>Tipo de Movimiento</small>
                <select class="select-filtro" name="select1">
                    <option value="">Todos</option>
                    <option value="ingreso">Ingreso</option>
                    <option value="egreso">Egreso</option>
                    <option value="venta">Venta</option>
                    <option value="compra">Compra</option>
                    <option value="ajuste">Ajuste</option>
                </select>
            </div>

            <div class="form-fechas">
                <small>Usuario</small>
                <select class="select-filtro" name="select2">
                    <option value="">Todos los usuarios</option>
                    <?php
                    foreach ($datos_select['caja'] as $usuario) {
                        $nombre_completo = trim(($usuario['us_nombres'] ?? '') . ' ' . ($usuario['us_apellido_paterno'] ?? ''));
                        echo '<option value="' . $usuario['us_id'] . '">' . $nombre_completo . '</option>';
                    }
                    ?>
                </select>
            </div>

            <?php if ($rol_usuario == 1) { ?>
            <div class="form-fechas">
                <small>Sucursal</small>
                <select class="select-filtro" name="select3">
                    <option value="">Todas las sucursales</option>
                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php } ?>

            <div class="search">
                <input type="text" name="busqueda" placeholder="Buscar por concepto o referencia...">
                <button type="button" class="btn-search">
                    <ion-icon name="search-outline"></ion-icon>
                </button>
            </div>

        </div>

        <button type="button" class="btn success" id="btnExportarExcelCajaHistorial">
            <ion-icon name="download-outline"></ion-icon> Excel
        </button>
        <button type="button" class="btn danger" id="btnExportarPDFCajaHistorial">
            <ion-icon name="document-text-outline"></ion-icon> PDF
        </button>
    </form>

    <div id="resumen-periodo"></div>

    <div class="tabla-contenedor"></div>

    <div id="grafico-movimientos" style="width: 100%; height: 400px; margin-top: 20px;"></div>
</div>

<?php } else { ?>
<div style="text-align: center; padding: 60px;">
    <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
    <p>No tiene permisos para acceder a esta sección.</p>
</div>
<?php } ?>