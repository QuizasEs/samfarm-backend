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
        <div class="">
            

            <form id="form-venta-caja" class="FormularioAjax" action="<?php echo SERVER_URL ?>ajax/ventaAjax.php" autocomplete="off" method="POST" data-form="save">
                <input type="hidden" name="ventaAjax" value="save">
                <input type="hidden" id="venta_items_json" name="venta_items_json">
                <input type="hidden" id="subtotal_venta" name="subtotal_venta">
                <input type="hidden" id="total_venta" name="total_venta">
                <input type="hidden" id="cambio_venta" name="cambio_venta">
                <input type="hidden" id="dinero_recibido_venta" name="dinero_recibido_venta">

                <div class="ph th1">Búsqueda de Productos</div>
                <div class="grid2 mb16">
                    <div class="card">
                        <div class="ch"><span class="ct">Buscar para Venta</span></div>
                        <div class="cb">
                            <div class="fg" style="margin-bottom:0"><label class="fl">Buscar Medicamento</label><div class="inpg"><input class="inp med_search" placeholder="¿Qué medicamento busca?"><button type="button" class="btn btn_buscar_med"><ion-icon name="search"></ion-icon></button></div></div>
                            <div class="grid4">
                                <div class="fg"><label class="fl">Proveedor</label><select class="sel" id="filtro_proveedor"><option value="">Proveedor</option><?php foreach ($datos_select['proveedores'] as $prov) { ?><option value="<?php echo $prov['pr_id'] ?>"><?php echo $prov['pr_razon_social'] ?></option><?php } ?></select></div>
                                <div class="fg"><label class="fl">Presentación</label><select class="sel" id="filtro_presentacion"><option value="">Presentación</option><?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?><option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option><?php } ?></select></div>
                                <div class="fg"><label class="fl">Función</label><select class="sel" id="filtro_funcion"><option value="">Función</option><?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?><option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option><?php } ?></select></div>
                                <div class="fg"><label class="fl">Vía</label><select class="sel" id="filtro_via"><option value="">Vía</option><?php foreach ($datos_select['via_administracion'] as $via) { ?><option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option><?php } ?></select></div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="ch"><span class="ct">Cotizar</span></div>
                        <div class="cb">
                            <div class="fg" style="margin-bottom:0"><label class="fl">Buscar Medicamento</label><div class="inpg"><input class="inp med_search_quote" placeholder="¿Qué medicamento cotizar?"><button type="button" class="btn btn_buscar_med_quote"><ion-icon name="search"></ion-icon></button></div></div>
                            <div class="fg"><label class="fl">Proveedor</label><select class="sel" id="filtro_proveedor_quote"><option value="">Proveedor</option><?php foreach ($datos_select['proveedores'] as $prov) { ?><option value="<?php echo $prov['pr_id'] ?>"><?php echo $prov['pr_razon_social'] ?></option><?php } ?></select></div>
                        </div>
                    </div>
                </div>

                 <div class="th1">Carrito de Compras</div>
                <div class="card mb16">
                    <div class="ch"><span class="ct">Productos en Venta</span></div>
                    <div class="cb">
                                <div class="tw">
                             <table>
                                  <thead><tr><th></th><th>Medicamento</th><th>Presentación</th><th>Unidades</th><th>Cajas</th><th>Precio Caja</th><th>Precio</th><th>Descuento</th><th>Subtotal</th></tr></thead>
                                  <tbody id="tabla_items_venta"><tr><td colspan="9" class="txctr tc">No hay medicamentos en la lista</td></tr></tbody>
                             </table>
                         </div>
                    </div>
                    <div class="cf"><button type="button" id="btn_cerrar_caja" class="btn btn-war btn-lg"><ion-icon name="lock-closed-outline"></ion-icon> Cerrar Caja</button><button type="submit" class="btn btn-suc btn-lg" id="btn_realizar_venta"><ion-icon name="checkmark-outline"></ion-icon> Vender</button></div>
                </div>

                <div class="grid2 mb16">
                    <div class="card">
                        <div class="ch"><span class="ct">Resumen de Venta</span></div>
                        <div class="cb">
                                <div class="fg"><label class="fl">Dinero Recibido</label><input class="inp" id="input_dinero_recibido" type="number" placeholder="Dinero cancelado"></div>
                            <div class="flx g8">
                                <div class="statc"><div class="sv" id="cambio_texto">0</div><div class="sl">Cambio</div></div>
                                <div class="statc"><div class="sv" id="subtotal_texto">0</div><div class="sl">Subtotal</div></div>
                                <div class="statc"><div class="sv" id="total_texto">0</div><div class="sl">Total</div></div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="ch"><span class="ct">Cliente y Pago</span></div>
                        <div class="cb">
                            <div class="fg" style="position: relative;"><label class="fl">Buscar Cliente</label><div class="inpw"><ion-icon class="iil" name="search-outline"></ion-icon><input class="inp" id="buscar_cliente_venta" placeholder="Buscar Cliente"><ion-icon class="iir" name="person-add-outline" style="cursor:pointer" onclick="ModalCliente.abrirModal()"></ion-icon></div></div>
                            <div id="resultado_clientes" class="resultado-busqueda"></div>
                            <div id="cliente_seleccionado_container" class="cliente-seleccionado-container" style="display: none;">
                                <div class="flx g8 flxc">
                                    <div class="av av-sm av-def"><ion-icon name="person-outline"></ion-icon></div>
                                    <span id="cliente_nombre_texto" class="td"></span>
                                    <button type="button" id="quitar_cliente_btn" class="btn btn-war btn-ico"><ion-icon name="close-outline"></ion-icon></button>
                                </div>
                                <input type="hidden" id="cliente_id_seleccionado" name="cliente_id">
                            </div>
                            <div class="fr">
                                <div class="fg"><label class="fl">Método de Pago</label><select class="sel" name="metodo_pago_venta" id="metodo_pago_venta"><option value="">Método</option><option value="efectivo">Efectivo</option><option value="QR">QR</option><option value="targeta">Tarjeta</option></select></div>
                                <div class="fg"><label class="fl">Documento</label><select class="sel" name="documento_venta" id="documento_venta"><option value="">Documento</option><option value="nota de venta">Nota de Venta</option><option value="factura">Factura</option></select></div>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="th1">Más Vendidos</div>
                 <div class="card">
                     <div class="cb">
                         <div class="tw">
                             <table>
                                 <thead><tr><th>N°</th><th>Medicamento</th><th>Precio</th><th>Acción</th></tr></thead>
                                 <tbody id="tabla_mas_vendidos"></tbody>
                             </table>
                         </div>
                     </div>
                 </div>
             </form>

            <div class="mov" id="modalCliente" style="display: none;">
                <div class="modal">
                    <div class="mh">
                        <div><div class="mt"><ion-icon name="person-add-outline"></ion-icon> Nuevo Cliente</div><div class="ms">Complete los datos del cliente</div></div>
                        <button class="mcl" onclick="ModalCliente.cerrarModalCliente()"><ion-icon name="close-outline"></ion-icon></button>
                    </div>
                    <div class="mb">
                        <form id="form-nuevo-cliente" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/clientesAjax.php" method="POST" data-form="save" autocomplete="off">
                            <input type="hidden" name="clientesAjax" value="nuevo">
                            <div class="fg"><label class="fl">Nombres <span class="req">*</span></label><input class="inp" type="text" name="Nombres_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required></div>
                            <div class="fr">
                                <div class="fg"><label class="fl">Apellido Paterno <span class="req">*</span></label><input class="inp" type="text" name="Paterno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required></div>
                                <div class="fg"><label class="fl">Apellido Materno</label><input class="inp" type="text" name="Materno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100"></div>
                            </div>
                            <div class="fr">
                                <div class="fg"><label class="fl">Carnet <span class="req">*</span></label><input class="inp" type="number" name="Carnet_cl" pattern="[0-9]{6,20}" maxlength="20"></div>
                                <div class="fg"><label class="fl">Teléfono</label><input class="inp" type="number" name="Telefono_cl" pattern="[0-9]{6,20}" maxlength="20"></div>
                            </div>
                            <div class="fr">
                                <div class="fg"><label class="fl">Dirección</label><input class="inp" type="text" name="Direccion_cl"></div>
                                <div class="fg"><label class="fl">Correo</label><input class="inp" type="email" name="Correo_cl"></div>
                            </div>
                        </form>
                    </div>
                    <div class="mf"><button class="btn btn-gho btn-lg" onclick="ModalCliente.cerrarModalCliente()">Cancelar</button><button type="submit" form="form-nuevo-cliente" class="btn btn-def btn-lg">Registrar</button></div>
                </div>
            </div>
        </div>
    <?php

    } else {
    ?>

        <div class="pg">
            <div class="ph">
                <div><div class="ptit"><ion-icon name="wallet-outline"></ion-icon> Abrir Caja</div><div class="psub">Inicia una nueva sesión de ventas</div></div>
            </div>
            <div class="card">
                    <div class="ch"><span class="ct">¿Abrir una nueva caja para realizar las ventas diarias?</span></div>
                        <div class="cb">
                            <form action="<?php echo SERVER_URL ?>ajax/ventaAjax.php" autocomplete="off" method="POST" data-form="save" class="FormularioAjax">

                        <input type="hidden" name="ventaAjax" value="new-caja">

                        <div class="fg"><label class="fl">Monto Inicial</label><input class="inp" type="number" name="saldo_inicial" pattern="[0-9.]{1-10}" required></div>
                        <div class="flxe"><button type="submit" class="btn btn-def btn-lg">Nueva Caja</button></div>
                    </form>
                </div>
            </div>
        </div>
        

<?php
    }
}
