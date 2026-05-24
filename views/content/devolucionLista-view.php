<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2 || $_SESSION['rol_smp'] == 3)) {
?>

    <div class="">
        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="return-down-back-outline"></ion-icon> Iniciar Devolución o Cambio
                </div>
                <div class="psub">Busque una venta para procesar devolución o cambio de productos</div>
            </div>
        </div>

        <div class="card mb20">
            <div class="cb">
                <form id="form_buscar_venta">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Criterio de Búsqueda</label>
                            <select class="sel" name="criterio_busqueda" id="criterio_busqueda">
                                <option value="">Seleccione...</option>
                                <option value="numero_factura">Número de Factura</option>
                                <option value="numero_documento">Número de Documento</option>
                                <option value="fa_id">ID Factura</option>
                                <option value="ve_id">ID Venta</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Valor a buscar</label>
                            <div class="inpg">
                                <input type="text" class="inp" name="valor_busqueda" id="valor_busqueda" placeholder="Ingrese el valor a buscar...">
                                <button type="button" class="btn btn-def" id="btn_buscar_venta">
                                    <ion-icon name="search"></ion-icon> Buscar
                                </button>
                            </div>
                        </div>
                        <div></div>
                    </div>
                </form>
            </div>
        </div>

        <div id="resultado_venta_container" style="display: none;">
            <div class="ph mb8">
                <div>
                    <div class="ptit">
                        <ion-icon name="document-text-outline"></ion-icon> Información de la Venta
                    </div>
                </div>
            </div>

            <div class="card mb20">
                <div class="cb">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Número de Documento</label>
                            <input type="text" class="inp" id="info_numero_documento" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Número de Factura</label>
                            <input type="text" class="inp" id="info_numero_factura" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Cliente</label>
                            <input type="text" class="inp" id="info_cliente" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Fecha de Venta</label>
                            <input type="text" class="inp" id="info_fecha" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Sucursal</label>
                            <input type="text" class="inp" id="info_sucursal" readonly>
                        </div>
                        <div class="fg">
                            <label class="fl">Total</label>
                            <input type="text" class="inp" id="info_total" readonly>
                        </div>
                    </div>

                    <input type="hidden" id="venta_ve_id">
                    <input type="hidden" id="venta_fa_id">
                    <input type="hidden" id="venta_su_id">
                </div>
            </div>

            <div class="ph mb8">
                <div>
                    <div class="ptit">
                        <ion-icon name="list-outline"></ion-icon> Productos de la Venta
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="tw">
                    <table>
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Medicamento</th>
                                <th>Lote</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_items_venta">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mov" id="modalDevolucion">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="return-down-back-outline"></ion-icon> Procesar Devolución
                    </div>
                </div>
                <button class="mcl" onclick="DevolucionManager.cerrarModal()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <input type="hidden" id="modal_dv_id">
                <input type="hidden" id="modal_med_id">
                <input type="hidden" id="modal_lm_id">
                <input type="hidden" id="modal_precio_unitario">

                <div class="th4 mb12">Información del Producto</div>

                <div class="fr">
                    <div class="fg">
                        <label class="fl">Medicamento</label>
                        <input type="text" class="inp" id="modal_nombre_medicamento" readonly>
                    </div>
                    <div class="fg">
                        <label class="fl">Lote</label>
                        <input type="text" class="inp" id="modal_lote" readonly>
                    </div>
                </div>

                <div class="fr">
                    <div class="fg">
                        <label class="fl">Cantidad Original</label>
                        <input type="text" class="inp" id="modal_cantidad_original" readonly>
                    </div>
                    <div class="fg">
                        <label class="fl">Cantidad a Devolver <span class="tdan">*</span></label>
                        <input type="number" class="inp" id="modal_cantidad_devolver" min="1" required>
                    </div>
                </div>

                <div class="fg">
                    <label class="fl">Motivo de Devolución <span class="tdan">*</span></label>
                    <textarea class="ta" id="modal_motivo" rows="3" placeholder="Describa el motivo de la devolución..." required></textarea>
                </div>

                <div class="fg">
                    <label class="fl">Tipo de Devolución <span class="tdan">*</span></label>
                    <div class="flxc g20">
                        <label class="chkg">
                            <input type="radio" name="tipo_devolucion" value="devolucion" checked>
                            <span class="radb"></span>
                            <span class="chl">Solo Devolución</span>
                        </label>
                        <label class="chkg">
                            <input type="radio" name="tipo_devolucion" value="cambio">
                            <span class="radb"></span>
                            <span class="chl">Devolución con Cambio</span>
                        </label>
                    </div>
                </div>

                <div id="info_cambio_container" style="display: none;">
                    <div class="alert alinf mb0">
                        <ion-icon name="information-circle-outline"></ion-icon>
                        <div>
                            <div class="altt">Cambio</div>
                            <div class="altx">Se entregará el mismo medicamento de un lote disponible. No se genera nueva venta ni movimiento de caja.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-sec" onclick="DevolucionManager.cerrarModal()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-def" onclick="DevolucionManager.confirmarDevolucion()">
                    <ion-icon name="checkmark-outline"></ion-icon> Confirmar
                </button>
            </div>
        </div>
    </div>

    <script src="<?php echo SERVER_URL; ?>views/script/devolucionLista-view.js"></script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
