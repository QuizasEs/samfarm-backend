<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/inventarioAjax.php"
        data-ajax-param="inventarioAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">Inventario de Medicamentos</div>
                <div class="psub">Consulte el stock, estados y análisis de margen de sus productos</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-out" id="btnExportarExcel" data-tip="Exportar a Excel">
                    <ion-icon name="download-outline"></ion-icon> Excel
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDFInventario" data-tip="Exportar a PDF">
                    <ion-icon name="document-text-outline"></ion-icon> PDF
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
                            <label class="fl">Estado de Stock</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todos</option>
                                <option value="agotado">Agotado</option>
                                <option value="critico">Crítico</option>
                                <option value="bajo">Bajo</option>
                                <option value="normal">Normal</option>
                                <option value="exceso">Exceso</option>
                                <option value="sin_definir">Sin Definir</option>
                            </select>
                        </div>

                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal</label>
                                <select class="sel select-filtro" name="select3">
                                    <option value="">Todas</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>

                        <!-- <div class="fg">
                            <label class="fl">Forma Farmacéutica</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todas</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div> -->
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Nombre, principio activo o código...">
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
            <div class="cb">
                <div class="tw">
                    <div class="tabla-contenedor"></div>
                </div>
            </div>
        </div>

        <div class="stit">Análisis de Margen Bruto (Últimas Ventas)</div>

        <div class="grid4 mb16">
            <div class="statc">
                <div class="siw bl"><ion-icon name="cash-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="totalIngresos">Bs. 0.00</div>
                    <div class="sl">Ingresos Totales (3 meses)</div>
                </div>
            </div>
            <div class="statc">
                <div class="siw ww"><ion-icon name="calculator-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="totalCostos">Bs. 0.00</div>
                    <div class="sl">Costo Total</div>
                </div>
            </div>
            <div class="statc">
                <div class="siw gr"><ion-icon name="trending-up-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="margenBrutoBs">Bs. 0.00</div>
                    <div class="sl">Margen Bruto (Bs)</div>
                </div>
            </div>
            <div class="statc">
                <div class="siw bl"><ion-icon name="stats-chart-outline"></ion-icon></div>
                <div>
                    <div class="sv" id="margenBrutoPct">0%</div>
                    <div class="sl">Margen Bruto (%)</div>
                </div>
            </div>
        </div>

        <div class="fr">
            <div class="card">
                <div class="ch">
                    <div class="ct">Top 10 Medicamentos por Margen Bruto</div>
                </div>
                <div class="cb">
                    <canvas id="chartMedicamentos" style="width: 100%; min-height: 300px;"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="ch">
                    <div class="ct">Margen Bruto Diario (Últimos 30 días)</div>
                </div>
                <div class="cb">
                    <canvas id="chartDiario" style="width: 100%; min-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="card mt16">
            <div class="ch">
                <div class="ct">Margen Bruto por Sucursal (Últimos 6 meses)</div>
            </div>
            <div class="cb">
                <canvas id="chartSucursales" style="width: 100%; height: 300px;"></canvas>
            </div>
        </div>

    </div>

    <!-- Modal Detalle Inventario -->
    <div class="mov" id="modalDetalleInventario">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Detalle de Inventario</div>
                    <div class="ms">Información detallada del inventario - <span id="modalDetalleMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalDetalleInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalDetalleInvId">
            <input type="hidden" id="modalDetalleMedId">
            <input type="hidden" id="modalDetalleSuId">

            <div class="mb">
                <div class="stit">Información General</div>

                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Línea</div>
                                    <div class="th5" id="detalleLaboral">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Sucursal</div>
                                    <div class="th5" id="detalleSucursal">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="cube-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Total Cajas</div>
                                    <div class="th5" id="detalleCajas">-</div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="file-tray-full-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Total Unidades</div>
                                    <div class="th5" id="detalleUnidades">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Valor Inventario</div>
                                    <div class="th5" id="detalleValorado">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="shield-checkmark-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Estado</div>
                                    <div id="detalleEstado">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="stit">Lotes Disponibles</div>
                <div class="card">
                    <div class="tw">
                        <table class="table-detail" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th>N° Lote</th>
                                    <th>Unidades</th>
                                    <th>Precio</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaLotesDetalle">
                                <tr>
                                    <td colspan="5" class="txctr tmut">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalDetalleInventario')">Cerrar</button>
                <?php if (isset($_SESSION['rol_smp']) && $_SESSION['rol_smp'] == 1) { ?>
                <button type="button" class="btn btn-def" onclick="if(typeof window.InventarioModals.abrirBalance === 'function') { window.InventarioModals.abrirBalance(); } else { console.error('window.InventarioModals.abrirBalance is not a function'); }">
                    <ion-icon name="scale-outline"></ion-icon> Balance de Precios
                </button>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Modal Balance de Precios -->
    <div class="mov" id="modalBalanceInventario">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Balance de Precios</div>
                    <div class="ms">Ajustar precios y márgenes - <span id="modalBalanceMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalBalanceInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/inventarioAjax.php" method="POST" data-form="balance" autocomplete="off" id="formBalanceInventario">
                <input type="hidden" name="inventarioAjax" value="balance_precios">
                <input type="hidden" name="med_id" id="balanceMedId">
                <input type="hidden" name="su_id" id="balanceSuId">

                <div class="mb" style="max-height: 70vh; overflow-y: auto;">
                    <div class="stit">Información del Producto</div>

                    <div class="fr1">
                        <div class="card">
                            <div class="cb">
                                <div class="litem"><ion-icon name="medical-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                    <div class="f1">
                                        <div class="tc">Medicamento</div>
                                        <div class="th5" id="balanceNombreMedicamento">-</div>
                                    </div>
                                </div>
                                <div class="litem"><ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                    <div class="f1">
                                        <div class="tc">Laboratorio</div>
                                        <div class="th5" id="balanceLaboratorio">-</div>
                                    </div>
                                </div>
                                <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                    <div class="f1">
                                        <div class="tc">Sucursal</div>
                                        <div class="th5" id="balanceSucursal">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="stit">Datos de Auditoría</div>
                    <div class="card">
                        <div class="cb">
                            <div class="fr1">
                                <div class="fg">
                                    <label class="fl" for="balancePrecioCompra">Precio de Compra</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_compra" id="balancePrecioCompra" min="0" readonly>
                                </div>

                            </div>
                            <div class="fr">
                                <div class="fg">
                                    <label class="fl" for="balanceCostoLista">Costo Lista</label>
                                    <input class="inp" type="number" step="0.01" name="lm_costo_lista" id="balanceCostoLista" min="0">
                                </div>
                                <div class="fg">
                                    <label class="fl" for="balanceMargenUnitario">Margen Unitario (%)</label>
                                    <input class="inp" type="number" step="0.01" name="lm_margen_u" id="balanceMargenUnitario" min="0">
                                </div>

                            </div>
                            <div class="fr">
                                <div class="fg">
                                    <label class="fl" for="balanceMargenCaja">Margen Caja (%)</label>
                                    <input class="inp" type="number" step="0.01" name="lm_margen_c" id="balanceMargenCaja" min="0">
                                </div>
                                <div class="fg">
                                    <label class="fl" for="balancePrecioVenta">Precio Venta Unitario</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_venta" id="balancePrecioVenta" min="0" readonly>
                                </div>
                            </div>
                            <div class="fr">
                                <div class="fg">
                                    <label class="fl" for="balancePrecioMinUnitario">Precio Min. Unitario</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_min_u" id="balancePrecioMinUnitario" min="0">
                                </div>
                                <div class="fg">
                                    <label class="fl" for="balancePrecioMinCaja">Precio Min. Caja</label>
                                    <input class="inp" type="number" step="0.01" name="lm_precio_min_c" id="balancePrecioMinCaja" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="card mt16" style="background: var(--btn-warning-pale); border-color: var(--btn-warning);">
                            <div class="cb">
                                <div class="litem" style="border:none">
                                    <ion-icon name="alert-circle-outline" style="font-size:20px;color:var(--btn-warning)"></ion-icon>
                                    <div class="f1">
                                        <div class="th5" style="color:var(--btn-warning); font-weight:bold; font-size:19px">Atención</div>
                                        <div class="tc" style="color:var(--btn-warning); font-weight:bold; font-size:16px">Este cambio afectará a TODOS los lotes activos de este medicamento en TODAS las sucursales. Verifique los valores antes de guardar.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="mf">
                    <button type="button" class="btn btn-war" onclick="App.closeM('modalBalanceInventario')">Cancelar</button>
                    <button type="submit" class="btn btn-def">
                        <ion-icon name="scale-outline"></ion-icon> Aplicar Balance
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Modal Historial Inventario -->
    <div class="mov" id="modalHistorialInventario">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Historial de Movimientos</div>
                    <div class="ms">Historial de movimientos - <span id="modalHistorialMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalHistorialInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalHistorialMedId">
            <input type="hidden" id="modalHistorialSuId">

            <div class="mb">
                <div class="stit">Movimientos</div>
                <div class="card">
                    <div class="tw">
                        <table class="table-detail" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Lote</th>
                                    <th>Sucursal</th>
                                    <th>Usuario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHistorialMovimientos">
                                <tr>
                                    <td colspan="7" class="txctr tmut">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalHistorialInventario')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Modal Configuracion Inventario -->
    <div class="mov" id="modalConfiguracionInventario">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Configurar Inventario</div>
                    <div class="ms">Configurar parámetros - <span id="modalConfiguracionMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="App.closeM('modalConfiguracionInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalConfiguracionInvId">
            <input type="hidden" id="modalConfiguracionMedId">
            <input type="hidden" id="modalConfiguracionSuId">

            <div class="mb">
                <div class="fr">
                    <div class="fg">
                        <label class="fl req">Cantidad Mínima</label>
                        <input class="inp" type="number" id="configuracionMinimo" min="0" value="0">
                        <small style="color: #666;">Unidades mínimas antes de alertar</small>
                    </div>
                    <div class="fg">
                        <label class="fl">Cantidad Máxima</label>
                        <input class="inp" type="number" id="configuracionMaximo" min="0" placeholder="Opcional">
                        <small style="color: #666;">Unidades máximas permitidas (dejar vacío sin límite)</small>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="App.closeM('modalConfiguracionInventario')">Cancelar</button>
                <button type="button" class="btn btn-def" onclick="window.InventarioModals.guardarConfiguracion()">
                    <ion-icon name="checkmark-outline"></ion-icon> Guardar
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <script src="<?php echo SERVER_URL; ?>views/script/inventarioLista-view.js"></script>



<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>