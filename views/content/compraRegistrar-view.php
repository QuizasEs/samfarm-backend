<div class="title">
    <h1>Registrar Compra</h1>
</div>
<!-- formulario de registro provedor -->
<div class="registro-usaurios-container">
    <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/proveedorAjax.php" method="POST" data-form="save" autocomplete="off">

        <input type="hidden" name="ProveedorAjax" value="save">
        <!-- DATOS escenciales -->
        <div class="form-title">
            <h3>datos compra</h3>
        </div>

        <div class="form-group">
            <div class="form-bloque">
                <label for="">Proveedor*</label>
                <select class="select-style" name="Uso_reg">
                    <option value="">SELECCIONAR</option>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">numero de factura*</label>
                <input type="text" name="Nombre_reg" placeholder="Nombres" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">Fecha de factura*</label>
                <input type="text" name="Apellido_paterno_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Apellido paterno" required>
            </div>
            <div class="form-bloque">
                <label for="">Numero de compra*</label>
                <input type="text" name="Apellido_materno_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Apellido materno" required>
            </div>

        </div>
        <!-- seccion para busqueda de medicamentos -->
        <div class="form-group">
            <div class="section-search">
                <div class="form-title">
                    <h3>filtrar por medicamento</h3>
                </div>
                <div class="filters">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="">forma farmaceutica</label>
                            <select name="" id="">
                                <option value="">seleccionar</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="">via de administracion</label>
                            <select name="" id="">
                                <option value="">seleccionar</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="">laboratorio</label>
                            <select name="" id="">
                                <option value="">seleccionar</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="">uso farmacologico</label>
                            <select name="" id="">
                                <option value="">seleccionar</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="">forma farmaceutica</label>
                            <input type="text" placeholder="termino de busqueda">
                        </div>
                        <div class="filter-group">
                            <button class="btn search-btn" onclick="BuscarMedicamentos()">buscar</button>
                        </div>
                    </div>

                </div>
                <!-- resultado de busqueda -->
                <div class="form-group">
                    <label for="">resultado de busqueda</label>
                    <div class="table-container">
                        <table id="tabla-medicamentos">
                            <thead>
                                <tr>
                                    <th>Nombre Químico</th>
                                    <th>Principio Activo</th>
                                    <th>Presentación</th>
                                    <th>Laboratorio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Paracetamol</td>
                                    <td>Paracetamol</td>
                                    <td>Tabletas 500mg</td>
                                    <td>das</td>
                                    <td class="actions">
                                        <button class="btn btn-success btn-sm" onclick="agregarMedicamento(1, 'Paracetamol')">
                                            ➕ Agregar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Ibuprofeno</td>
                                    <td>Ibuprofeno</td>
                                    <td>Cápsulas 400mg</td>
                                    <td>sdfdsf</td>
                                    <td class="actions">
                                        <button class="btn btn-success btn-sm" onclick="agregarMedicamento(2, 'Ibuprofeno')">
                                            ➕ Agregar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
                <!-- calculo de totales -->
                <div class="section-calc">
                    <div class="title">totales</div>
                    <div class="calc-content">
                        <div class="calc-group">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="calc-group">
                            <span>Impuestos:</span>
                            <span id="impuestos">$0.00</span>
                        </div>
                        <div class="calc-group">
                            <span>TOTAL:</span>
                            <span id="total">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="form-buttons">
            <button class="btn-primary">Agregar</button>
        </div>
        <div class="form-button">

            <button class="btn-danger">cancelar</button>
        </div>
        <div class="modal" id="modalLote">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">📦 Agregar Lote - <span id="modalMedicamentoNombre"></span></div>
                    <button class="close" onclick="cerrarModal()">&times;</button>
                </div>

                <form id="formLote">
                    <input type="hidden" id="modalMedicamentoId">

                    <div class="form-group">
                        <label for="numero_lote" class="required">Número de Lote</label>
                        <input type="text" id="numero_lote" required>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="cantidad" class="required">Cantidad</label>
                                <input type="number" id="cantidad" min="1" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="fecha_vencimiento" class="required">Fecha Vencimiento</label>
                                <input type="date" id="fecha_vencimiento" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="precio_compra" class="required">Precio Compra</label>
                                <input type="number" id="precio_compra" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="precio_venta" class="required">Precio Venta</label>
                                <input type="number" id="precio_venta" step="0.01" min="0.01" required>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                        <button type="button" class="btn btn-danger" onclick="cerrarModal()">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="agregarLote()">Agregar Lote</button>
                    </div>
                </form>
            </div>

        </div>






    </form>
</div>