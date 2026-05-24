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
        data-ajax-url="ajax/cajaHistorialAjax.php"
        data-ajax-param="cajaHistorialAjax"
        data-ajax-registros="15">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="cash-outline"></ion-icon> Historial de Movimientos de Caja
                </div>
                <div class="psub">Consulte el historial completo de movimientos de caja</div>
            </div>
            <div class="tbr">
                        <button type="button" class="btn btn-out" id="btnExportarExcelCajaHistorial">
                            <ion-icon name="download-outline"></ion-icon> Exportar Excel
                        </button>
                        <button type="button" class="btn btn-out" id="btnExportarPDFCajaHistorial">
                            <ion-icon name="document-text-outline"></ion-icon> Exportar PDF
                        </button>
                    </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Desde</label>
                            <input class="inp" type="date" name="fecha_desde">
                        </div>
                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta">
                        </div>
                        <div class="fg">
                            <label class="fl">Tipo de Movimiento</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todos</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                                <option value="venta">Venta</option>
                                <option value="compra">Compra</option>
                                <option value="ajuste">Ajuste</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Usuario</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todos los usuarios</option>
                                <?php
                                 foreach ($datos_select['usuarios'] as $usuario) {
                                    $nombre_completo = trim(($usuario['us_nombres'] ?? '') . ' ' . ($usuario['us_apellido_paterno'] ?? ''));
                                    echo '<option value="' . $usuario['us_id'] . '">' . $nombre_completo . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <?php if ($rol_usuario == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal</label>
                                <select class="sel select-filtro" name="select3">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <div></div>
                        <?php } ?>
                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por concepto o referencia...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>

        <div id="resumen-periodo" class="card mb16">
            <div class="cb">
                <!-- Resumen will be loaded here -->
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Movimientos</div>
            </div>
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="bar-chart-outline"></ion-icon> Gráfico de Movimientos</div>
            </div>
            <div class="cb">
                <div id="grafico-movimientos" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle de Caja Historial -->
    <div id="modalReferenciaCajaHistorial" class="mov">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="cash-outline"></ion-icon>
                        Detalle del Movimiento
                    </div>
                    <div class="ms">Información detallada del movimiento seleccionado</div>
                </div>
                <button class="mcl" onclick="CajaHistorial.cerrarModalReferenciaCaja()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <div class="mb">
                <div class="stit">
                    <ion-icon name="information-circle-outline"></ion-icon> Información General
                </div>

                <div class="fr1">
                    <div class="card">
                        <div class="cb" id="contenidoReferenciaCajaHistorial">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="CajaHistorial.cerrarModalReferenciaCaja()">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="<?php echo SERVER_URL; ?>views/script/cajaHistorialLista-view.js"></script>

<?php } else { ?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit">Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb txctr" style="padding:60px">
                <ion-icon name="lock-closed-outline" style="font-size:48px;color:var(--text-faint);margin-bottom:16px"></ion-icon>
                <div class="th3 mb8">Acceso Denegado</div>
                <div class="tbs tmut">No tiene permisos para acceder a esta sección.</div>
            </div>
        </div>
    </div>
<?php } ?>
