
<div class="title">
    <h1>Registrar Compra</h1>
</div>
<!-- formulario de registro provedor -->
<div class="container">
    <form class="form FormularioAjax" action="" method="POST"
        data-form="save" autocomplete="off">

        <input type="hidden" name="compraAjax" value="save">
        <!-- DATOS escenciales -->
        <div class="form-title">
            <h3>datos compra</h3>
        </div>

        <div class="form-group">
            <div class="form-bloque">
                <label for="">Proveedor*</label>
                <select class="select-style" name="Proveedor_reg">
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['proveedores'] as $pro) { ?>
                        <option value="<?php echo $pro['pr_id'] ?>"><?php echo $pro['pr_nombres'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">numero de factura*</label>
                <input type="text" name="Numero_factura_reg" placeholder="Nombres"
                    pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+&#39;]{3,100}" maxlength="100"
                    required="">
            </div>
            <div class="form-bloque">
                <label for="">Fecha de factura*</label>
                <input type="date" name="Fecha_factura_reg"
                    maxlength="100"
                    required="">
            </div>

        </div>
        <!-- seccion para busqueda de medicamentos -->
        <div class="form-title">
            <h3>filtrar por medicamento</h3>
        </div>
        <div class="form-search">

            <div class="form-bloque-search">
                <label for="">forma farmaceutica</label>
                <select name="Form_reg" id="">
                    <option value="">Seleccionar</option>

                    <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                        <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                    <?php } ?>

                </select>
            </div>
            <div class="form-bloque-search">
                <label for="">via de administracion</label>
                <select name="Via_reg" id="">
                    <option value="">Seleccionar</option>

                    <?php foreach ($datos_select['via_administracion'] as $via) { ?>

                        <option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option>
                    <?php } ?>

                </select>
            </div>
            <div class="form-bloque-search">
                <label for="">laboratorio</label>
                <select name="Laboratorio_reg" id="">
                    <option value="">Seleccionar</option>
                    <?php foreach ($datos_select['laboratorios'] as $lab) { ?>

                        <option value="<?php echo $lab['la_id'] ?>"><?php echo $lab['la_nombre_comercial'] ?></option>
                    <?php } ?>

                </select>
            </div>
            <div class="form-bloque-search">
                <label for="">uso farmacologico</label>
                <select name="Uso_reg" id="">
                    <option value="">Seleccionar</option>

                    <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>

                        <option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option>
                    <?php } ?>

                </select>
            </div>
            <div class="form-bloque-search">
                <label for="">Buscar</label>
                <input type="text" name="termino" id="buscarMedicamento" placeholder="Buscar medicamento..." onkeyup="SearchManager.buscarMedicamentos()">
            </div>
            <div class="form-bloque-search">
                <a href="javascript:void(0)" role="button" class="btn primary btn-search">Buscar</a>

            </div>

        </div>



        <!-- resultado de busqueda -->
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
                        <!-- aqui se muestran los resultados de la busqueda -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-title">
            <h3>🧾 Lista de medicamentos agregados</h3>
        </div>
        <div id="items-compra" class="content"></div>

        <div class="form-group">
            <!-- calculo de totales -->
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
                    <button class="btn success">Agregar</button>
                    <a class="btn warning">cancelar</a>
                </div>
            </div>
        </div>



        <div class="modal" id="modalLote" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">📦 Agregar Lote - <span
                            id="modalMedicamentoNombre">Paracetamol</span>
                    </div>
                    <a class="close" onclick="cerrarModal()"><ion-icon name="close-outline"></ion-icon></a>
                </div>


                <input type="hidden" id="modalMedicamentoId" value="1">

                <div class="modal-group">
                    <div class="row">

                        <label for="numero_lote" class="required">Número de Lote</label>
                        <input type="text" id="numero_lote" required="">
                    </div>


                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="cantidad" class="required">Cantidad</label>
                                <input type="number" name="Cantidad_reg" id="cantidad" min="1" required="">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="fecha_vencimiento" class="required">
                                    Vencimiento</label>
                                <input type="date" name="Vencimiento_reg" id="fecha_vencimiento" required="">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="precio_compra" class="required">Precio Compra</label>
                                <input type="number" name="Precio_compra_reg" id="precio_compra" step="0.01" min="0.01" required="">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label for="precio_venta" class="required">Precio Venta</label>
                                <input type="number" id="precio_venta_reg" step="0.01" min="0.01" required="">
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