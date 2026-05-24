<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    /* Admin o Gerente */

    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/ventasHistorialAjax.php"
        data-ajax-param="ventasHistorialAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="receipt-outline"></ion-icon> Historial de Ventas
                </div>
                <div class="psub">Consulte y administre el historial completo de ventas realizadas</div>
            </div>
            <div class="tbr">
                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                        <button type="button" id="btnExportarExcel" class="btn btn-out">
                            <ion-icon name="download-outline"></ion-icon> Exportar Excel
                        </button>
                <?php } ?>
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
                            <input class="inp" type="date" name="fecha_desde" title="Fecha desde">
                        </div>
                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta" title="Fecha hasta">
                        </div>

                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursales</label>
                                <select class="sel select-filtro" name="select1">
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
                            <label class="fl">Cajero</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todos los cajeros</option>
                                 <?php foreach ($datos_select['usuarios'] as $caja) { ?>
                                     <option value="<?php echo $caja['us_id'] ?>"><?php echo $caja['us_nombres'] ?></option>
                                 <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Tipo de documento</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todos los tipos</option>
                                <option value="nota de venta">Nota de Venta</option>
                                <option value="factura">Factura</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por N° documento o cliente...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle de Venta -->
    <div id="modalDetalleVenta" class="mov">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="receipt-outline"></ion-icon>
                        Detalle de Venta
                    </div>
                    <div class="ms">Información completa de la venta seleccionada</div>
                </div>
                <button class="mcl" onclick="VentasHistorialModals.cerrar('modalDetalleVenta')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="stit">Información de la Venta</div>

                <input type="hidden" id="modalDetalleVeId">

                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Documento</div>
                                    <div class="th5" id="detalleNumeroDocumento"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha</div>
                                    <div class="th5" id="detalleFecha"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Cliente</div>
                                    <div class="th5" id="detalleCliente"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="card-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">CI/NIT</div>
                                    <div class="th5" id="detalleCarnet"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="person-circle-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Vendedor</div>
                                    <div class="th5" id="detalleVendedor"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Sucursal</div>
                                    <div class="th5" id="detalleSucursal"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Caja</div>
                                    <div class="th5" id="detalleCaja"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="receipt-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Factura</div>
                                    <div class="th5" id="detalleNumeroFactura">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stit">Medicamentos Vendidos</div>
                <div class="card mb16">
                    <div class="cb">
                        <div class="tw" style="max-height: 400px; overflow-y: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Lote</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Descuento</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaItemsVenta">
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

                <div class="stit">Totales</div>
                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem">
                                <div class="tc">Subtotal</div>
                                <div class="th5" id="detalleSubtotal">Bs. 0.00</div>
                            </div>
                            <div class="litem">
                                <div class="tc">Impuestos</div>
                                <div class="th5" id="detalleImpuesto">Bs. 0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb txctr">
                            <div class="tsuc" style="font-size:18px;font-weight:700">TOTAL: <span id="detalleTotal">Bs. 0.00</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="VentasHistorialModals.cerrar('modalDetalleVenta')">Cerrar</button>
            </div>
        </div>
    </div>

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


<script src="<?php echo SERVER_URL; ?>views/script/ventasHistorialLista-view.js"></script>
