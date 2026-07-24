<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] != 1 && $_SESSION['rol_smp'] != 2)) {
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}
require_once "./controllers/medicamentoController.php";
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();
$ultimo_lote = $ins_med->ultimo_lote_controller();
$ultima_compra = $ins_med->ultima_compra_controller();
?>
<div class="">
    <div class="ph">
        <div>
            <div class="ptit">Registrar Compra</div>
            <div class="psub">Complete los datos para registrar una nueva compra</div>
        </div>
    </div>

    <form class="FormularioAjax formCompra" id="formCompra" action="<?php echo SERVER_URL; ?>ajax/compraAjax.php" method="POST"
        data-form="save" autocomplete="off">

        <input type="hidden" name="compraAjax" value="save">
        <input type="hidden" name="lotes_json" id="lotes_json" value="[]">
        <input type="hidden" name="totales_json" id="totales_json" value="{}">
        <input type="hidden" name="Proveedor_reg" id="Proveedor_reg" value="">
        <input type="hidden" id="ultimo_lote_valor" value="<?php echo $ultimo_lote ?? 0; ?>">

        <style>
            /* Estilos para la lista de lotes en compra */
            .lote-card {
                border: 1px solid var(--bg-secondary);
                border-radius: 8px;
                margin-bottom: 15px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                background-color: #fff;
                overflow: hidden;
            }

            .lote-card-header {
                background-color: var(--bg-primary);
                padding: 15px;
                border-bottom: 1px solid var(--bg-secondary);
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            .lote-titulo {
                color: #007bff;
                /* Color primario */
                font-size: 1.1em;
                margin-bottom: 5px;
                display: block;
            }

            .badge {
                display: inline-block;
                padding: 4px 8px;
                font-size: 0.75em;
                font-weight: 700;
                line-height: 1;
                text-align: center;
                white-space: nowrap;
                vertical-align: baseline;
                border-radius: 4px;
                margin-left: 8px;
            }

            .badge-success {
                background-color: #28a745;
                /* Color éxito */
                color: #fff;
            }

            .badge-secondary {
                background-color: #6c757d;
                /* Color secundario */
                color: #fff;
            }

            .lote-detalles {
                padding: 10px 15px;
                font-size: 0.9em;
            }

            .lote-detalles span {
                display: inline-block;
                margin-right: 15px;
                margin-bottom: 5px;
            }

            .text-muted {
                color: #6c757d;
            }

            .text-success {
                color: #28a745;
            }

            .text-info {
                color: #17a2b8;
            }

            .text-warning {
                color: #ffc107;
            }

            .btn-danger {
                background-color: #dc3545;
                border-color: #dc3545;
                color: #fff;
                padding: 6px 12px;
                border-radius: 4px;
                text-decoration: none;
                display: inline-block;
                font-size: 0.875em;
            }

            .btn-danger:hover {
                background-color: #c82333;
                border-color: #bd2130;
            }

            .btn-sm {
                padding: 4px 8px;
                font-size: 0.75em;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .lote-card-header {
                    flex-direction: column;
                    align-items: stretch;
                }

                .lote-card-header>div:last-child {
                    margin-top: 10px;
                    text-align: center;
                }

                .lote-detalles span {
                    display: block;
                    margin-right: 0;
                    margin-bottom: 8px;
                }

                .espacio {
                    margin-left: 0;
                }
            }

            .tabla-resultado-container {
                max-height: 400px;
                overflow-y: auto;
            }

            .tabla-resultado {
                width: 100%;
                border-collapse: collapse;
            }

            .tabla-resultado-container::-webkit-scrollbar {
                width: 6px;
            }

            .tabla-resultado-container::-webkit-scrollbar-thumb {
                background: var(--border-strong);
                border-radius: 3px;
            }
        </style>

        <!-- DATOS ESENCIALES -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Datos de Compra</span>
            </div>
            <div class="cb">
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="numero_compra">Número de Compra*</label>
                        <input class="inp" type="text" name="Numero_compra_reg" id="numero_compra" readonly>
                    </div>
                </div>
            </div>
        </div>



        <!-- FILTRAR MEDICAMENTO -->
        <div class="card mb16" style="overflow: visible !important;">
            <div class="ch">
                <span class="ct">Filtrar por Medicamento</span>
            </div>
            <div class="cb" style="overflow: visible !important;">
                <div class="filtro-dinamico">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl" for="Proveedor_filtro">Proveedor</label>
                            <div class="search-wrapper" style="position: relative;">
                                <input type="text" class="inp" id="dd_Proveedor_filtro" placeholder="Buscar proveedor..." autocomplete="off">
                                <div id="dd_Proveedor_filtro_res" class="search-results-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; display: none;"></div>
                            </div>
                            <select class="sel select-filtro" name="Proveedor_filtro" id="Proveedor_filtro" onchange="actualizarProveedor()" style="display: none;">
                                <option value="">Todos</option>
                                <?php foreach ($datos_select['proveedores'] as $pro) { ?>
                                    <option value="<?php echo $pro['pr_id']; ?>" data-nit="<?php echo $pro['pr_nit']; ?>"><?php echo $pro['pr_razon_social']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="Form_reg">Forma Farmacéutica</label>
                            <div class="search-wrapper" style="position: relative;">
                                <input type="text" class="inp" id="dd_Form_reg" placeholder="Buscar forma..." autocomplete="off">
                                <div id="dd_Form_reg_res" class="search-results-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; display: none;"></div>
                            </div>
                            <select class="sel select-filtro" name="Form_reg" id="Form_reg" style="display: none;">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id']; ?>"><?php echo $forma['ff_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="Via_reg">Vía de Administración</label>
                            <div class="search-wrapper" style="position: relative;">
                                <input type="text" class="inp" id="dd_Via_reg" placeholder="Buscar vía..." autocomplete="off">
                                <div id="dd_Via_reg_res" class="search-results-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; display: none;"></div>
                            </div>
                            <select class="sel select-filtro" name="Via_reg" id="Via_reg" style="display: none;">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                                    <option value="<?php echo $via['vd_id']; ?>"><?php echo $via['vd_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl" for="Uso_reg">Uso Farmacológico</label>
                            <div class="search-wrapper" style="position: relative;">
                                <input type="text" class="inp" id="dd_Uso_reg" placeholder="Buscar uso..." autocomplete="off">
                                <div id="dd_Uso_reg_res" class="search-results-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; display: none;"></div>
                            </div>
                            <select class="sel select-filtro" name="Uso_reg" id="Uso_reg" style="display: none;">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                    <option value="<?php echo $uso['uf_id']; ?>"><?php echo $uso['uf_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="buscarMedicamento">Buscar Medicamento</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="termino" id="buscarMedicamento" placeholder="Buscar medicamento...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESULTADO DE BÚSQUEDA -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Resultado Filtrado</span>
            </div>
            <div class="cb">
                <div class="tw">
                    <div class="tabla-resultado-container">
                        <table class="tabla-resultado">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Producto</th>
                                    <th>Presentación</th>
                                    <th>Proveedor</th>
                                    <th>Código de Barras</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMedicamentos">
                                <!-- Resultados de búsqueda -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- LISTA DE MEDICAMENTOS AGREGADOS -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Lista de Medicamentos Agregados</span>
            </div>
            <div class="cb">
                <div id="items-compra" class="content"></div>
            </div>
        </div>

        <!-- Sucursal -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">¿En que sucursal se registrara la compra? </span>
            </div>
            <div class="cb">
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="sucursal_reg">Sucursal*</label>
                        <select class="sel" name="sucursal_reg" id="sucursal_reg">
                            <?php foreach ($datos_select['sucursales'] as $suc) { ?>
                                <option value="<?php echo $suc['su_id']; ?>" <?php echo ($suc['su_id'] == $_SESSION['id_smp']) ? 'selected' : ''; ?>>
                                    <?php echo $suc['su_nombre']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <!-- TOTALES -->
        <div class="card">
            <div class="ch">
                <span class="ct">Totales</span>
            </div>
            <div class="cb">
                <div class="card mb16">
                    <div class="ch">
                        <span class="ct">Resumen de Totales</span>
                    </div>
                    <div class="cb">
                        <div class="tw">
                            <table class="totales-table">
                                <tbody>
                                    <tr class="total-row total-row-final">
                                        <td class="total-label total-label-final">TOTAL:</td>
                                        <td class="total-value total-value-final text-suc" id="total">Bs0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="calc-buttons">
                    <button type="submit" class="btn btn-suc">Agregar</button>
                    <a href="#" class="btn btn-war">Cancelar</a>
                </div>
            </div>
        </div>

    </form>

    <!-- MODAL LOTE -->
    <div class="mov" id="modalLote">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Agregar Lote - <span id="modalMedicamentoNombre">Paracetamol</span>
                        <span class="badge badge-secondary" id="modalLoteNumeroInfo" style="margin-left:8px;"></span>
                    </div>
                    <div class="ms" id="modalLoteSubtitle">Complete los datos del lote</div>
                </div>
                <button class="mcl" onclick="cerrarModal()"><ion-icon name="close-outline"></ion-icon></button>
            </div>

            <input type="hidden" id="modalMedicamentoId" value="1">

            <div class="mb">
                <div class="stit">Información Básica</div>
                <input type="hidden" id="numero_lote">

                <div class="stit">Cantidades</div>
                <div class="fr3">
                    <div class="fg">
                        <label class="fl" for="cantidad">Número de Cajas que Entran</label>
                        <input class="inp" type="number" name="Cantidad_reg" id="cantidad" min="1">
                    </div>
                    <div class="fg">
                        <label class="fl" for="cantidad_unidades">Unidades por Caja</label>
                        <input class="inp" type="number" name="Cantidad_unidades_reg" id="cantidad_unidades" min="1" value="1" oninput="calcularPrecioMinCaja();">
                    </div>

                    <div class="fg">
                        <label class="fl" for="fecha_vencimiento">Vencimiento</label>
                        <input class="inp" type="date" name="Vencimiento_reg" id="fecha_vencimiento">
                    </div>
                </div>

                <div class="stit">Precios Principales</div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="precio_compra">Precio Costo</label>
                        <input class="inp" type="number" name="Precio_compra_reg" id="precio_compra" step="0.01" min="0.01">
                    </div>
                    <div class="fg">
                        <label class="fl" for="costo_lista">Costo Lista</label>
                        <input class="inp" type="number" id="costo_lista" step="0.01" min="0">
                    </div>
                </div>

                <div class="stit">Auditoría y Márgenes</div>
                <div class="fr3">

                    <div class="fg">
                        <label class="fl" for="margen_unitario">Margen Unitario (%)</label>
                        <input class="inp" type="number" id="margen_unitario" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label class="fl" for="precio_venta_reg">Precio Venta</label>
                        <input class="inp" type="number" name="precio_venta_reg" id="precio_venta_reg" step="0.01" min="0.01" readonly>
                    </div>
                    <div class="fg">
                        <label class="fl" for="precio_min_unitario">Precio Min. Unitario</label>
                        <input class="inp" type="number" id="precio_min_unitario" step="0.01" min="0">
                    </div>
                </div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="margen_caja">Margen Caja (%)</label>
                        <input class="inp" type="number" id="margen_caja" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label class="fl" for="precio_min_caja">Precio Min. Caja</label>
                        <input class="inp" type="number" id="precio_min_caja" step="0.01" min="0">
                    </div>

                </div>

            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-suc" id="btnGuardarLote" onclick="agregarLote()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo SERVER_URL; ?>views/script/compraOrden-view.js"></script>
<script>
    function handleSelectItem(id, nombre) {
        if (typeof ModalManager !== 'undefined' && ModalManager.abrirModal) {
            ModalManager.abrirModal(id, nombre);
        } else if (typeof abrirModal === 'function') {
            abrirModal(id, nombre);
        }
    }
</script>