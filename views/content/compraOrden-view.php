<?php
if ($_SESSION['rol_smp'] != 1) {
    echo "aqui no";
    exit();
}
require_once "./controllers/MedicamentoController.php";
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();
$ultimo_lote = $ins_med->ultimo_lote_controller();
$ultima_compra = $ins_med->ultima_compra_controller();


?>
<div class="title">
    <h1>Registrar Compra</h1>
</div>

<div class="container">
    <form class="form FormularioAjax formCompra" action="<?php echo SERVER_URL; ?>ajax/compraAjax.php" method="POST"
        data-form="save" autocomplete="off">

        <input type="hidden" name="compraAjax" value="save">
        <input type="hidden" id="ultimo_lote_valor" value="<?php echo $ultimo_lote ?? 0; ?>">
        <input type="hidden" id="ultima_campra_valor" value="<?php echo $ultima_compra ?? 0; ?>">

        <script>
            document.querySelector('.FormularioAjax').addEventListener('submit', function(e) {
                e.preventDefault();

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
        <div class="form-title">
            <h3>datos compra</h3>
        </div>

        <div class="form-group">
            <div class="form-bloque">
                <label for="numero_compra">numero de compra*</label>
                <input type="text" name="Numero_compra_reg" id="numero_compra" readonly>
            </div>
            <div class="form-bloque">
                <label for="razon_reg">razon social*</label>
                <input type="text" name="razon_reg" id="razon_reg" placeholder="Razon social"
                    pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+&#39;]{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="Proveedor_reg">Proveedor*</label>
                <select class="select-style" name="Proveedor_reg" id="Proveedor_reg" required>
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['proveedores'] as $pro) { ?>
                        <option value="<?php echo $pro['pr_id']; ?>"><?php echo $pro['pr_nombres']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="Laboratorio_factura_reg">Laboratorio*</label>
                <select class="select-style" name="Laboratorio_factura_reg" id="Laboratorio_factura_reg" required>
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                        <option value="<?php echo $lab['la_id']; ?>"><?php echo $lab['la_nombre_comercial']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>


        <!-- DATOS DE FACTURA -->
        <div class="form-title">
            <h3>datos de factura</h3>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="Fecha_factura_reg">Fecha de factura*</label>
                <input type="date" name="Fecha_factura_reg" id="Fecha_factura_reg" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="Numero_factura_reg">numero de factura*</label>
                <input type="number" name="Numero_factura_reg" id="Numero_factura_reg" placeholder="Numero de factura" required>
            </div>
            <div class="form-bloque">
                <label for="impuestos_reg">Impuestos %*</label>
                <small>de 0% a 100%</small>
                <input type="number" name="impuestos_reg" id="impuestos_reg" min="0" max="100" step="0.01"
                    placeholder="0" oninput="validarPorcentaje(this)">
            </div>
        </div>

        <!-- FILTRAR MEDICAMENTO -->
        <div class="form-title">
            <h3>filtrar por medicamento</h3>
        </div>
        <div class="form-search">
            <div class="form-bloque-search">
                <label for="Form_reg">forma farmaceutica</label>
                <select name="Form_reg" id="Form_reg">
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                        <option value="<?php echo $forma['ff_id']; ?>"><?php echo $forma['ff_nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-bloque-search">
                <label for="Via_reg">via de administracion</label>
                <select name="Via_reg" id="Via_reg">
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                        <option value="<?php echo $via['vd_id']; ?>"><?php echo $via['vd_nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-bloque-search">
                <label for="Laboratorio_reg">laboratorio</label>
                <select name="Laboratorio_reg" id="Laboratorio_reg">
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                        <option value="<?php echo $lab['la_id']; ?>"><?php echo $lab['la_nombre_comercial']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-bloque-search">
                <label for="Uso_reg">uso farmacologico</label>
                <select name="Uso_reg" id="Uso_reg">
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                        <option value="<?php echo $uso['uf_id']; ?>"><?php echo $uso['uf_nombre']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-bloque-search">
                <label for="buscarMedicamento">Buscar</label>
                <input type="text" name="termino" id="buscarMedicamento" placeholder="Buscar medicamento..."
                    onkeyup="SearchManager.buscarMedicamentos()">
            </div>
        </div>

        <!-- RESULTADO DE BÚSQUEDA -->
        <div class="form-title">
            <h3>resultado filtrado</h3>
        </div>
        <div class="content">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMedicamentos">
                        <!-- Resultados de búsqueda -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- LISTA DE MEDICAMENTOS AGREGADOS -->
        <div class="form-title">
            <h3>Lista de medicamentos agregados</h3>
        </div>
        <div id="items-compra" class="content"></div>

        <!-- TOTALES -->
        <div class="form-group">
            <div class="form-title">
                <h3>Total</h3>
            </div>
            <div class="content-calc">
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
                    <button type="submit" class="btn success">Agregar</button>
                    <a href="#" class="btn warning">cancelar</a>
                </div>
            </div>
        </div>

        <!-- MODAL LOTE -->
        <div class="modal" id="modalLote" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">📦 Agregar Lote - <span id="modalMedicamentoNombre">Paracetamol</span></div>
                    <a class="close" onclick="cerrarModal()">×</a>
                </div>

                <input type="hidden" id="modalMedicamentoId" value="1">

                <div class="modal-group">
                    <div class="row">
                        <label for="numero_lote" class="required">Número de Lote</label>
                        <input type="text" id="numero_lote" required readonly>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="cantidad" class="required">Paquetes o Cajas</label>
                                <input type="number" name="Cantidad_reg" id="cantidad" min="1" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="fecha_vencimiento" class="required">Vencimiento</label>
                                <input type="date" name="Vencimiento_reg" id="fecha_vencimiento" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="cantidad" >Blisters por caja </label>
                                <input type="number" name="Cantidad_blister_reg" id="cantidad_blister" min="1" placeholder="si aplica">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="fecha_vencimiento" >Unidades por blister </label>
                                <input type="number" name="Cantidad_unidades_reg" id="cantidad_unidades" min="1" placeholder="si aplica">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="precio_compra" class="required">Precio Compra por caja</label>
                                <input type="number" name="Precio_compra_reg" id="precio_compra" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="precio_venta_reg" class="required">Precio Venta por unidad</label>
                                <input type="number" name="precio_venta_reg" id="precio_venta_reg" step="0.01" min="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <label for="">Activar este Lote?</label>
                            <div class="checkbox-wraper">
                                <input class="tgl tgl-flip" id="cb5" type="checkbox" />
                                <label class="tgl-btn" data-tg-off="Nop" data-tg-on="SI!" for="cb5"></label>
                            </div>

                        </div>
                    </div>

                    <div class="btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="cerrarModal()">Cancelar</a>
                        <a href="javascript:void(0)" class="btn success" onclick="agregarLote()">Agregar</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>