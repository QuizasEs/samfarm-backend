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
        data-ajax-url="ajax/comprasHistorialAjax.php"
        data-ajax-param="comprasHistorialAjax"
        data-ajax-registros="10">
        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="receipt-outline"></ion-icon> Historial de Compras
                </div>
                <div class="psub">Consulta el historial completo de compras realizadas</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-out" id="btnExportarExcelComprasHistorial">
                    <ion-icon name="download-outline"></ion-icon> Exportar Excel
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDFComprasHistorial">
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


                    </div>
                    <div class="fr">
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
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por N° compra...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Compras Realizadas</div>
            </div>
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="ch">
            <div class="ct"><ion-icon name="stats-chart-outline"></ion-icon> Análisis de Compras por Sucursal</div>
        </div>
        <div class="cb">
            <div id="grafico-compras-periodo" style="width: 100%; height: 400px;"></div>
        </div>
    </div>

    <div class="mov" id="modalDetalleCompra">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="document-text-outline"></ion-icon>
                        Detalle de Compra - <span id="modalCompraNumero">...</span>
                    </div>
                    <div class="ms">Información completa de la compra seleccionada</div>
                </div>
                <button class="mcl" onclick="ComprasHistorialModals.cerrar('modalDetalleCompra')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalCompraId">

            <div class="mb">
                <div class="stit">
                    <ion-icon name="information-circle-outline"></ion-icon> Información General
                </div>

                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Número de Compra</div>
                                    <div class="th5" id="detalleNumeroCompra">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha de Compra</div>
                                    <div class="th5" id="detalleFechaCompra">-</div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Sucursal</div>
                                    <div class="th5" id="detalleSucursal">-</div>
                                </div>
                            </div>
                            <div class="litem" style="border:none"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Usuario</div>
                                    <div class="th5" id="detalleUsuario">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stit">
                    <ion-icon name="list-outline"></ion-icon> Detalle de Medicamentos
                </div>
                <div class="card mb16">
                    <div class="cb">
                        <div class="tw">
                             <table class="table-detail">
                                <thead>
                                    <tr>
                                        <th width="35%">Medicamento</th>
                                        <th width="8%">Cant</th>
                                        <th width="12%">Precio</th>
                                        <th width="10%">Desc</th>
                                        <th width="12%">Subtotal</th>
                                        <th width="12%">Vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaDetalleMedicamentos">
                                    <tr>
                                        <td colspan="6" class="txctr">
                                            <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="stit">
                    <ion-icon name="calculator-outline"></ion-icon> Totales
                </div>
                <div class="grid4 mb16">
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleSubtotal">Bs. 0.00</div>
                            <div class="sl">Subtotal</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="receipt-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleImpuestos">Bs. 0.00</div>
                            <div class="sl">Impuestos</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="calculator-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotal">Bs. 0.00</div>
                            <div class="sl">Total</div>
                        </div>
                    </div>
                </div>

                <div class="stit">
                    <ion-icon name="cube-outline"></ion-icon> Estado de Lotes
                </div>
                <div class="grid4">
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="archive-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotalLotes">-</div>
                            <div class="sl">Total Lotes</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="checkmark-circle-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleLotesActivos">-</div>
                            <div class="sl">Activos</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="time-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleLotesEspera">-</div>
                            <div class="sl">En Espera</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="ComprasHistorialModals.cerrar('modalDetalleCompra')">Cerrar</button>

            </div>
        </div>
    </div>

    <script src="<?php echo SERVER_URL; ?>views/script/comprasHistorialLista-view.js"></script>



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
