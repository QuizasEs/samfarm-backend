<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="" id="transferir-container" data-su-actual="<?php echo (int)($_SESSION['sucursal_smp'] ?? 0); ?>" data-rol-usuario="<?php echo (int)($_SESSION['rol_smp'] ?? 0); ?>">
        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="swap-horizontal-outline"></ion-icon> Transferir Medicamentos
                </div>
                <div class="psub">Busque y seleccione medicamentos disponibles para transferir entre sucursales</div>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico" id="form-buscar-lotes-transfer">
                    <div class="fr3">
                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal Origen</label>
                                <select class="sel select-filtro" name="su_origen_filter" id="su_origen_filter_transfer">
                                    <option value="">Mi sucursal</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>

                        <div class="fg">
                            <label class="fl">Vence hasta</label>
                            <input class="inp" type="date" name="fecha_venc_max_transfer" id="fecha_venc_max_transfer">
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda_transfer" id="busqueda_transfer" placeholder="Buscar medicamento o lote...">
                                <button type="button" class="btn btn-def btn-search" id="btn-buscar-lotes-transfer">
                                    <ion-icon name="search"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Lotes Disponibles</div>
            </div>
            <div class="cb">
                <div id="resultado-busqueda-lotes-transfer">
                    <p class="txctr tmut" style="padding: 20px;">
                        <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                        Use los filtros para buscar lotes disponibles
                    </p>
                </div>
                <div id="paginacion-lotes-transfer"></div>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Items a Transferir</div>
            </div>
            <div class="cb">
                <div id="lista-items-transfer-container">
                    <p class="txctr tmut" style="padding: 20px;">
                        <ion-icon name="cube-outline" style="font-size: 48px;"></ion-icon><br>
                        No hay items agregados
                    </p>
                </div>
            </div>
        </div>

        <div id="resumen-transfer-container" style="display: none;">
            <div class="card">
                <div class="ch">
                    <div class="ct"><ion-icon name="stats-chart-outline"></ion-icon> Resumen de Transferencia</div>
                </div>
                <div class="cb">
                    <div class="grid4 mb16">
                        <div class="statc">
                            <div class="siw bl"><ion-icon name="cube-outline"></ion-icon></div>
                            <div>
                                <div class="sv" id="resumen-total-items-transfer">0</div>
                                <div class="sl">Total Items</div>
                            </div>
                        </div>
                        <div class="statc">
                            <div class="siw gr"><ion-icon name="archive-outline"></ion-icon></div>
                            <div>
                                <div class="sv" id="resumen-total-cajas-transfer">0</div>
                                <div class="sl">Total Cajas</div>
                            </div>
                        </div>
                        <div class="statc">
                            <div class="siw ww"><ion-icon name="medical-outline"></ion-icon></div>
                            <div>
                                <div class="sv" id="resumen-total-unidades-transfer">0</div>
                                <div class="sl">Total Unidades</div>
                            </div>
                        </div>
                        <div class="statc">
                            <div class="siw rd"><ion-icon name="cash-outline"></ion-icon></div>
                            <div>
                                <div class="sv" id="resumen-total-valorado-transfer">Bs. 0.00</div>
                                <div class="sl">Valor Total</div>
                            </div>
                        </div>
                    </div>

                    <div class="fg mb16">
                        <label class="fl">Observaciones (opcional)</label>
                        <textarea class="ta" id="observaciones-transfer" rows="3"></textarea>
                    </div>

                    <div class="flxe">
                        <button type="button" class="btn btn-def" id="btn-generar-transfer">
                            <ion-icon name="send-outline"></ion-icon> Generar Transferencia
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mov" id="modal-agregar-item-transfer">
        <div class="modal mxl">
            <input type="hidden" id="modal-stock-cajas-real-transfer">
            <input type="hidden" id="modal-stock-unidades-real-transfer">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="add-circle-outline"></ion-icon>
                        Agregar a Transferencia
                    </div>
                    <div class="ms">Seleccione la cantidad y sucursal destino</div>
                </div>
                <button class="mcl" onclick="TransferManager.cerrarModalAgregar()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <input type="hidden" id="modal-lm-id-transfer">
                <input type="hidden" id="modal-med-id-transfer">
                <input type="hidden" id="modal-precio-compra-transfer">
                <input type="hidden" id="modal-precio-venta-transfer">
                <input type="hidden" id="modal-cant-blister-transfer">
                <input type="hidden" id="modal-cant-unidad-transfer">

                <div class="fr1 mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="th4 mb8" id="modal-medicamento-nombre-transfer"></div>
                            <div class="litem"><ion-icon name="pricetag-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Lote</div><div class="th5" id="modal-lote-numero-transfer"></div></div></div>
                            <div class="litem"><ion-icon name="cube-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Stock Disponible</div><div class="th5" id="modal-stock-disponible-transfer"></div></div></div>
                            <div class="litem" style="border:none"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Vencimiento</div><div class="th5" id="modal-vencimiento-transfer"></div></div></div>
                        </div>
                    </div>
                </div>

                <div class="fr mb16">
                    <div class="fg">
                        <label class="fl req">Cantidad (cajas)</label>
                        <input class="inp" type="number" id="modal-cantidad-cajas-transfer" min="1" required>
                    </div>
                    <div class="fg">
                        <label class="fl">Equivale a (unidades)</label>
                        <input class="inp" type="number" id="modal-cantidad-unidades-transfer" readonly>
                    </div>
                </div>

                <div class="fg mb16">
                    <label class="fl req">Sucursal Destino</label>
                    <select class="sel" id="modal-sucursal-destino-transfer" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="alert alsuc mb16">
                    <ion-icon name="cash-outline"></ion-icon>
                    <div>
                        <div class="altt">Subtotal valorado</div>
                        <div class="altx"><span id="modal-subtotal-transfer" style="font-weight:700">Bs. 0.00</span></div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="TransferManager.cerrarModalAgregar()">Cancelar</button>
                <button class="btn btn-def" onclick="TransferManager.agregarItem()">
                    <ion-icon name="add-outline"></ion-icon> Agregar
                </button>
            </div>
        </div>
    </div>


    

    <script src="<?php echo SERVER_URL; ?>views/script/transferirLista-view.js"></script>

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
