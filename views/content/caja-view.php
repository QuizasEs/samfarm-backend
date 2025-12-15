<?php

if (!isset($_SESSION['id_smp']) || !in_array($_SESSION['rol_smp'], [1, 2, 3])) {
    /* en caso que no se cuente con un rol correspodiente */
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
} else {
    /* preguntamos si el cliente de la session tiene cajas activas a su id */
    require_once "./controllers/ventaController.php";
    $ins_venta = new ventaController();
    $caja_activa = $ins_venta->consulta_caja_controller();

    if ($caja_activa == true) {

        require_once "./controllers/medicamentoController.php";
        $ins_med = new medicamentoController();
        $datos_select = $ins_med->datos_extras_controller();
?>
        <div class="container">
            <div class="title">
                <h2><ion-icon name="wallet-outline"></ion-icon> caja</h2>
            </div>
            <form id="form-venta-caja" class="form FormularioAjax" action="<?php echo SERVER_URL ?>ajax/ventaAjax.php" autocomplet="off" method="POST" autocomplete="off" data-form="save">
                <input type="hidden" name="ventaAjax" value="save">

                <input type="hidden" id="venta_items_json" name="venta_items_json">
                <input type="hidden" id="subtotal_venta" name="subtotal_venta">
                <input type="hidden" id="total_venta" name="total_venta">
                <input type="hidden" id="cambio_venta" name="cambio_venta">
                <input type="hidden" id="dinero_recibido_venta" name="dinero_recibido_venta">

                <div class="caja-content">

                    <div class="saldo-content">
                        <div class="ventas-resumen">

                            <div class="ventas-resumen-bloque">
                                <span>Dinero recibido</span>
                                <input type="number" id="input_dinero_recibido" placeholder="Dinero cancelado" required>
                            </div>

                            <div class="resumen-totales">
                                <span>cambio</span>
                                <span id="cambio_texto">0</span>
                            </div>

                            <div class="resumen-totales">
                                <span>subtotal</span>
                                <span id="subtotal_texto">0</span>
                            </div>

                            <div class="resumen-totales">
                                <span>total</span>
                                <span id="total_texto">0</span>
                            </div>

                        </div>

                        <div class="ventas-finalizar">
                            <div class="ventas-cliente">
                                <input type="text" id="buscar_cliente_venta" placeholder="Buscar Cliente">

                                <a href="javascript:void(0)" title="Nuevo" style="color: green;" onclick="ModalCliente.abrirModal()">
                                    <ion-icon name="person-add-outline"></ion-icon>
                                </a>

                            </div>
                            <div id="resultado_clientes" class="resultado-busqueda"></div>


                            <div id="cliente_seleccionado_container" class="cliente-seleccionado-container" style="display: none;">
                                <div class="cliente-seleccionado">
                                    <ion-icon name="person-outline"></ion-icon>
                                    <span id="cliente_nombre_texto"></span>
                                    <button type="button" id="quitar_cliente_btn" class="btn-remove">
                                        <ion-icon name="close-outline"></ion-icon>
                                    </button>
                                </div>
                                <input type="hidden" id="cliente_id_seleccionado" name="cliente_id">
                            </div>
                            <div class="ventas-metodo">
                                <select class="select-filtro" name="metodo_pago_venta" id="metodo_pago_venta">
                                    <option value="">Metodo</option>
                                    <option value="efectivo">efectivo</option>
                                    <option value="QR">QR</option>
                                    <option value="targeta">targeta</option>
                                </select>

                                <select class="select-filtro" name="documento_venta" id="documento_venta">
                                    <option value="">Documento</option>
                                    <option value="nota de venta">nota de venta</option>
                                    <option value="factura">factura</option>
                                </select>
                            </div>



                        </div>
                    </div>

                    <div class="table-container caja-lista">
                        <table class="table caja-lista">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Medicamento</th>
                                    <th>presentacion</th>
                                    <th>cantidad</th>
                                    <th>precio</th>
                                    <th>subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="tabla_items_venta"></tbody>
                        </table>
                    </div>

                    <div class="ventas-finalizar-buttons">
                        <button type="button" id="btn_cerrar_caja" class="btn danger">
                            <ion-icon name="lock-closed-outline"></ion-icon> cerrar caja
                        </button>
                        <button type="submit" class="btn success" id="btn_realizar_venta">vender</button>
                    </div>

                    <div class="filtro-caja">

                        <div class="caja-texto">
                            <h3>filtros de busqueda</h3>
                        </div>

                        <div class="caja-filtro-search" style="position: relative;">
                            <input type="text" id="med_search" placeholder="Que medicamento busca?">
                            <!-- El div se crea dinámicamente aquí -->
                        </div>

                        <div class="caja-texto">
                            <h3>Filtros de busqueda</h3>
                        </div>

                        <div class="caja-filtro-content">
                            <select class="select-filtro" id="filtro_linea">
                                <option value="">Linea</option>
                                <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                                    <option value="<?php echo $lab['la_id'] ?>"><?php echo $lab['la_nombre_comercial'] ?></option>
                                <?php } ?>
                            </select>

                            <select class="select-filtro" id="filtro_presentacion">
                                <option value="">presentacion</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                                <?php } ?>
                            </select>

                            <select class="select-filtro" id="filtro_funcion">
                                <option value="">Funcion</option>
                                <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                    <option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option>
                                <?php } ?>
                            </select>
                            <select class="select-filtro" id="filtro_via">
                                <option value="">Via</option>
                                <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                                    <option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option>
                                <?php } ?>
                            </select>

                        </div>

                        <div class="caja-texto">
                            <h3>Mas Vendidos</h3>
                        </div>

                        <div class="table-container">
                            <table class="table caja">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Medicamento</th>
                                        <th>Precio</th>
                                        <th>Accion</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla_mas_vendidos"></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </form>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/clientesAjax.php" method="POST" data-form="save" autocomplete="off">
                <input type="hidden" name="clientesAjax" value="nuevo">
                <div class="modal" id="modalCliente" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="modal-title"><ion-icon name="person-add-outline"></ion-icon> Nuevo cliente</div>
                            <a class="close" onclick="ModalCliente.cerrarModalCliente()">×</a>
                        </div>

                        <div class="modal-group">
                            <div class="row">
                                <div class="col">
                                    <div class="modal-bloque">
                                        <label class="required">Nombres</label>
                                        <input type="text" name="Nombres_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="modal-bloque">
                                        <label class="required">Apellido Paterno</label>
                                        <input type="text" name="Paterno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="modal-bloque">
                                        <label>Apellido Materno</label>
                                        <input type="text" name="Materno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="modal-bloque">
                                        <label class="required">Carnet</label>
                                        <input type="number" name="Carnet_cl" pattern="[0-9]{6,20}" maxlength="20">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="modal-bloque">
                                        <label>Teléfono</label>
                                        <input type="number" name="Telefono_cl" pattern="[0-9]{6,20}" maxlength="20">
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col">
                                    <div class="modal-bloque">
                                        <label>Dirección</label>
                                        <input type="text" name="Direccion_cl">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="modal-bloque">
                                        <label>Correo</label>
                                        <input type="email" name="Correo_cl">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-btn-content">
                                <a href="javascript:void(0)" class="btn warning" onclick="ModalCliente.cerrarModalCliente()">Cancelar</a>
                                <button type="submit" class="btn success">Registrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <?php

    } else {
    ?>

        <div class="open-caja-container">
            <div class="open-content">
                <form action="<?php echo SERVER_URL ?>ajax/ventaAjax.php" autocomplete="off" method="POST" data-form="save" class="form FormularioAjax">

                    <input type="hidden" name="ventaAjax" value="new-caja">

                    <div class="title">
                        <h2>¿Abrir una nueva caja para realzar las ventas diarias?</h2>
                    </div>
                    <div class="open-bloque">

                        <label for="">Monto inicial</label>
                        <input type="number" name="saldo_inicial" pattern="[0-9.]{1-10}" required>
                    </div>
                    <div class="open-buttons">

                        <button type="submit" class="btn default">
                            Nueva caja
                        </button>
                    </div>

                </form>
            </div>
        </div>
        <!-- script busqueda de cliente para caja -->

<?php
    }
}
