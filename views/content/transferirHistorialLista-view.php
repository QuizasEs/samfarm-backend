<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {

    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/transferirHistorialAjax.php"
        data-ajax-param="transferirHistorialAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="swap-horizontal-outline"></ion-icon> Historial de Transferencias
                </div>
                <div class="psub">Consulta el historial completo de transferencias realizadas</div>
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
                                <label class="fl">Sucursal Origen</label>
                                <select class="sel select-filtro" name="select1">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="fg">
                                <label class="fl">Sucursal Destino</label>
                                <select class="sel select-filtro" name="select2">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <div></div>
                            <div></div>
                        <?php } ?>

                        <div class="fg">
                            <label class="fl">Usuario</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todos los usuarios</option>
                                 <?php if (!empty($datos_select['usuarios'])) { ?>
                                     <?php foreach ($datos_select['usuarios'] as $usuario) { ?>
                                         <option value="<?php echo $usuario['us_id'] ?>"><?php echo $usuario['us_nombres'] ?></option>
                                     <?php } ?>
                                 <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aceptada">Aceptada</option>
                                <option value="rechazada">Rechazada</option>
                            </select>
                        </div>


                    </div>
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por N° transferencia...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
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

    <div id="modalDetalleTransferencia" class="mov">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="swap-horizontal-outline"></ion-icon>
                        Detalle de Transferencia
                    </div>
                    <div class="ms">Información completa de la transferencia seleccionada</div>
                </div>
                <button class="mcl" onclick="TransferirHistorialModals.cerrar('modalDetalleTransferencia')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="stit">Información de la Transferencia</div>

                <input type="hidden" id="modalDetalleTrId">

                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Transferencia</div>
                                    <div class="th5" id="detalleNumero"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha Envío</div>
                                    <div class="th5" id="detalleFechaEnvio"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Origen</div>
                                    <div class="th5" id="detalleOrigen"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Destino</div>
                                    <div class="th5" id="detalleDestino"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Usuario Emisor</div>
                                    <div class="th5" id="detalleUsuarioEmisor"></div>
                                </div>
                            </div>
                            <div class="litem" style="border:none"><ion-icon name="radio-button-on-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Estado</div>
                                    <div id="detalleEstado"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stit">Medicamentos Transferidos</div>
                <div class="card mb16">
                    <div class="cb">
                        <div class="tw" style="max-height: 400px; overflow-y: auto;">
                              <table class="table-detail">
                                <thead>
                                    <tr>
                                        <th width="40%">Medicamento</th>
                                        <th width="8%">Cajas</th>
                                        <th width="10%">Unidades</th>
                                        <th width="12%">Precio Unit.</th>
                                        <th width="12%">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaItemsTransferencia">
                                     <tr>
                                        <td colspan="5" class="txctr">
                                            <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="stit">Totales</div>
                <div class="grid4">
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="archive-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotalCajas">0</div>
                            <div class="sl">Total Cajas</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="medical-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotalUnidades">0</div>
                            <div class="sl">Total Unidades</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotal">Bs. 0.00</div>
                            <div class="sl">Total Valorado</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="TransferirHistorialModals.cerrar('modalDetalleTransferencia')">Cerrar</button>
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

<script src="<?php echo SERVER_URL; ?>views/script/transferirHistorialLista-view.js"></script>
