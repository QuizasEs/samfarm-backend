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

    <form class="FormularioAjax formCompra" action="<?php echo SERVER_URL; ?>ajax/compraAjax.php" method="POST"
        data-form="save" autocomplete="off">

        <input type="hidden" name="compraAjax" value="save">
        <input type="hidden" id="ultimo_lote_valor" value="<?php echo $ultimo_lote ?? 0; ?>">
        <input type="hidden" id="ultima_campra_valor" value="<?php echo $ultima_compra ?? 0; ?>">

        <script>
            function actualizarProveedor() {
                const proveedorFiltro = document.getElementById('Proveedor_filtro');
                const proveedorHidden = document.getElementById('Proveedor_reg');

                // Actualizar el campo oculto
                proveedorHidden.value = proveedorFiltro.value;
            }

            // Función de compatibilidad
            function actualizarRazonSocial() {
                actualizarProveedor();
            }

            document.querySelector('.FormularioAjax').addEventListener('submit', function(e) {
                e.preventDefault();

                // Obtener valor del proveedor directamente del filtro
                const proveedorFiltro = document.getElementById('Proveedor_filtro');
                const proveedorHidden = document.getElementById('Proveedor_reg');
                
                if (proveedorFiltro && proveedorHidden) {
                    proveedorHidden.value = proveedorFiltro.value;
                }

                // Validar que se haya seleccionado un proveedor
                if (!proveedorFiltro || !proveedorFiltro.value) {
                    Swal.fire('Error', 'Debe seleccionar un proveedor', 'error');
                    return;
                }

                const lotes = ModalManager.obtenerLotes();
                const totales = ModalManager.obtenerTotales();

                let inputLotes = document.getElementById('lotes_json');
                if (!inputLotes) {
                    inputLotes = document.createElement('input');
                    inputLotes.type = 'hidden';
                    inputLotes.name = 'lotes_json';
                    inputLotes.id = 'lotes_json';
                    this.appendChild(inputLotes);
                }
                inputLotes.value = JSON.stringify(lotes);

                let inputTotales = document.getElementById('totales_json');
                if (!inputTotales) {
                    inputTotales = document.createElement('input');
                    inputTotales.type = 'hidden';
                    inputTotales.name = 'totales_json';
                    inputTotales.id = 'totales_json';
                    this.appendChild(inputTotales);
                }
                inputTotales.value = JSON.stringify(totales);
            });
        </script>

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
                <input type="hidden" name="Proveedor_reg" id="Proveedor_reg" value="">
            </div>
        </div>


        <!-- FILTRAR MEDICAMENTO -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Filtrar por Medicamento</span>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl" for="Proveedor_filtro">Proveedor</label>
                            <select class="sel select-filtro" name="Proveedor_filtro" id="Proveedor_filtro" onchange="actualizarProveedor()">
                                <option value="">Todos</option>
                                <?php foreach ($datos_select['proveedores'] as $pro) { ?>
                                    <option value="<?php echo $pro['pr_id']; ?>" data-nit="<?php echo $pro['pr_nit']; ?>"><?php echo $pro['pr_razon_social']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="Form_reg">Forma Farmacéutica</label>
                            <select class="sel select-filtro" name="Form_reg" id="Form_reg">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id']; ?>"><?php echo $forma['ff_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="Via_reg">Vía de Administración</label>
                            <select class="sel select-filtro" name="Via_reg" id="Via_reg">
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
                            <select class="sel select-filtro" name="Uso_reg" id="Uso_reg">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                    <option value="<?php echo $uso['uf_id']; ?>"><?php echo $uso['uf_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="buscarMedicamento">Buscar Medicamento</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="termino" id="buscarMedicamento" placeholder="Buscar medicamento..." onkeyup="SearchManager.buscarMedicamentos()">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- RESULTADO DE BÚSQUEDA -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Resultado Filtrado</span>
            </div>
            <div class="cb">
                <div class="tw">
                    <table>
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Presentación</th>
                                <th>Descripción</th>
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

        <!-- LISTA DE MEDICAMENTOS AGREGADOS -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Lista de Medicamentos Agregados</span>
            </div>
            <div class="cb">
                <div id="items-compra" class="content"></div>
            </div>
        </div>

        <!-- TOTALES -->
        <div class="card">
            <div class="ch">
                <span class="ct">Totales</span>
            </div>
            <div class="cb">
                <div class="calc">
                    <div class="calc-group">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="calc-group">
                        <span>Impuestos:</span>
                        <span id="impuestos">$0.00</span>
                    </div>
                    <div class="calc-group total">
                        <span>TOTAL:</span>
                        <span id="total">$0.00</span>
                    </div>
                </div>
                <div class="calc-buttons">
                    <button type="submit" class="btn btn-suc">Agregar</button>
                    <a href="#" class="btn btn-war">Cancelar</a>
                </div>
            </div>
        </div>

        <!-- MODAL LOTE -->
        <div class="mov" id="modalLote">
            <div class="modal">
                <div class="mh">
                    <div>
                        <div class="mt">Agregar Lote - <span id="modalMedicamentoNombre">Paracetamol</span></div>
                        <div class="ms">Complete los datos del lote</div>
                    </div>
                    <button class="mcl" onclick="cerrarModal()"><ion-icon name="close-outline"></ion-icon></button>
                </div>

                <input type="hidden" id="modalMedicamentoId" value="1">

                <div class="mb">
                    <div class="fr">
                        <div class="fg">
                            <label class="fl" for="numero_lote">Número de Lote</label>
                            <input class="inp" type="text" id="numero_lote" readonly>
                        </div>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl" for="cantidad">Cantidad por Caja</label>
                            <input class="inp" type="number" name="Cantidad_reg" id="cantidad" min="1">
                        </div>
                        <div class="fg">
                            <label class="fl" for="cantidad_unidades">Cantidad Unitaria para Venta</label>
                            <input class="inp" type="number" name="Cantidad_unidades_reg" id="cantidad_unidades" min="1" value="1">
                        </div>
                    </div>
                    <div class="fr">
                        <div class="fg">
                            <label class="fl" for="fecha_vencimiento">Vencimiento</label>
                            <input class="inp" type="date" name="Vencimiento_reg" id="fecha_vencimiento">
                        </div>
                    </div>
                    <div class="fr">
                        <div class="fg">
                            <label class="fl" for="precio_compra">Precio Costo</label>
                            <input class="inp" type="number" name="Precio_compra_reg" id="precio_compra" step="0.01" min="0.01">
                        </div>
                        <div class="fg">
                            <label class="fl" for="precio_venta_reg">Precio Venta</label>
                            <input class="inp" type="number" name="precio_venta_reg" id="precio_venta_reg" step="0.01" min="0.01">
                        </div>
                    </div>
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Activar este Lote?</label>
                            <div class="swg">
                                <div></div>
                                <label class="sw">
                                    <input type="checkbox" id="cb5">
                                    <span class="swt"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mf">
                    <button class="btn btn-war" onclick="cerrarModal()">Cancelar</button>
                    <button class="btn btn-suc" onclick="agregarLote()">Agregar</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- MODAL LOTE -->
<div class="mov" id="modalLote">
    <div class="modal">
        <div class="mh">
            <div>
                <div class="mt">Agregar Lote - <span id="modalMedicamentoNombre">Paracetamol</span></div>
                <div class="ms">Complete los datos del lote</div>
            </div>
            <button class="mcl" onclick="cerrarModal()"><ion-icon name="close-outline"></ion-icon></button>
        </div>
        <div class="mb">
            <input type="hidden" id="modalMedicamentoId" value="1">

            <div class="fr">
                <div class="fg">
                    <label class="fl" for="numero_lote">Número de Lote</label>
                    <input class="inp" type="text" id="numero_lote" readonly>
                </div>
            </div>

            <div class="fr">
                <div class="fg">
                    <label class="fl" for="cantidad">Cantidad por Caja</label>
                    <input class="inp" type="number" name="Cantidad_reg" id="cantidad" min="1">
                </div>
                <div class="fg">
                    <label class="fl" for="cantidad_unidades">Cantidad Unitaria para Venta</label>
                    <input class="inp" type="number" name="Cantidad_unidades_reg" id="cantidad_unidades" min="1" value="1">
                </div>
            </div>
            <div class="fr">
                <div class="fg">
                    <label class="fl" for="fecha_vencimiento">Vencimiento</label>
                    <input class="inp" type="date" name="Vencimiento_reg" id="fecha_vencimiento">
                </div>
            </div>
            <div class="fr">
                <div class="fg">
                    <label class="fl" for="precio_compra">Precio Costo</label>
                    <input class="inp" type="number" name="Precio_compra_reg" id="precio_compra" step="0.01" min="0.01">
                </div>
                <div class="fg">
                    <label class="fl" for="precio_venta_reg">Precio Venta</label>
                    <input class="inp" type="number" name="precio_venta_reg" id="precio_venta_reg" step="0.01" min="0.01">
                </div>
            </div>
            <div class="fr">
                <div class="fg">
                    <label class="fl">Activar este Lote?</label>
                    <div class="swg">
                        <div></div>
                        <label class="sw">
                            <input type="checkbox" id="cb5">
                            <span class="swt"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mf">
            <button class="btn btn-war" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-suc" onclick="agregarLote()">Agregar</button>
        </div>
    </div>
</div>