<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    /* Admin o Gerente */

    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/ventasHistorialAjax.php"
        data-ajax-param="ventasHistorialAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="receipt-outline"></ion-icon> Historial de Ventas
                </div>
                <div class="psub">Consulte y administre el historial completo de ventas realizadas</div>
            </div>
            <div class="tbr">
                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                        <button type="button" id="btnExportarExcel" class="btn btn-out">
                            <ion-icon name="download-outline"></ion-icon> Exportar Excel
                        </button>
                <?php } ?>
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
                            <label class="fl">Desde</label>
                            <input class="inp" type="date" name="fecha_desde" title="Fecha desde">
                        </div>
                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta" title="Fecha hasta">
                        </div>

                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursales</label>
                                <select class="sel select-filtro" name="select1">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <div></div>
                        <?php } ?>

                        <div class="fg">
                            <label class="fl">Cajero</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todos los cajeros</option>
                                 <?php foreach ($datos_select['usuarios'] as $caja) { ?>
                                     <option value="<?php echo $caja['us_id'] ?>"><?php echo $caja['us_nombres'] ?></option>
                                 <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Tipo de documento</label>
                            <select class="sel select-filtro" name="select4">
                                <option value="">Todos los tipos</option>
                                <option value="nota de venta">Nota de Venta</option>
                                <option value="factura">Factura</option>
                            </select>
                        </div>

                        <!-- Estado de la factura electronica.

                             Sin esto, las facturas que el SIN no acepto se
                             mezclan con el resto y no hay forma de saber
                             cuales hay que reemitir a mano. Los cuatro grupos
                             son excluyentes y cubren todas las ventas: 9
                             requieren revision, 8 pendientes de enviar, 2
                             aceptadas y 31 sin factura electronica (las notas
                             de venta y las que no llegaron a firmarse).

                             Los nombres no son los estados internos del cron
                             porque quien mira esta pantalla no debe tener que
                             saber que significa REQUIERE_REVISION. -->
                        <div class="fg">
                            <label class="fl">Estado de facturación</label>
                            <select class="sel select-filtro" name="select5">
                                <option value="">Todos los estados</option>
                                <option value="revision">Requieren revisión</option>
                                <option value="pendiente">Pendientes de enviar</option>
                                <option value="aceptada">Aceptadas por el SIN</option>
                                <option value="sin_factura">Sin factura electrónica</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por N° documento o cliente...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle de Venta -->
    <div id="modalDetalleVenta" class="mov">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="receipt-outline"></ion-icon>
                        Detalle de Venta
                    </div>
                    <div class="ms">Información completa de la venta seleccionada</div>
                </div>
                <button class="mcl" onclick="VentasHistorialModals.cerrar('modalDetalleVenta')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="stit">Información de la Venta</div>

                <input type="hidden" id="modalDetalleVeId">

                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Documento</div>
                                    <div class="th5" id="detalleNumeroDocumento"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha</div>
                                    <div class="th5" id="detalleFecha"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Cliente</div>
                                    <div class="th5" id="detalleCliente"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="card-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">CI/NIT</div>
                                    <div class="th5" id="detalleCarnet"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="receipt-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Tipo de Documento</div>
                                    <div class="th5" id="detalleTipoDocumento"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="person-circle-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Vendedor</div>
                                    <div class="th5" id="detalleVendedor"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Sucursal</div>
                                    <div class="th5" id="detalleSucursal"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Caja</div>
                                    <div class="th5" id="detalleCaja"></div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="pricetag-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">N° Factura</div>
                                    <div class="th5" id="detalleNumeroFactura">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datos de facturacion electronica. Solo se muestra cuando la
                     venta tiene CUF, es decir cuando SI paso por facturacion
                     electronica. Se oculta por defecto desde el JS.

                     Este bloque no lleva CUF, CUFD ni codigo de recepcion a
                     proposito. Son identificadores que el SIN usa entre si: el
                     cajero no los usa para nada y la pantalla se vuelve ilegible
                     con un CUFD de 16 caracteres partido en dos lineas.

                     Donde hacen falta siguen a mano: el CUF viaja dentro del QR
                     del comprobante, y la columna factura.fa_cuf tiene el resto.
                     Aca se ven el estado y el numero de factura, que es lo unico
                     con lo que el usuario compara contra el papel. -->
                <div class="stit" id="detalleSiatTitulo" style="display:none;">Facturación Electrónica</div>
                <div class="card mb16" id="detalleSiatCard" style="display:none;">
                    <div class="cb">
                        <div class="litem"><ion-icon name="shield-checkmark-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                            <div class="f1">
                                <div class="tc">Estado</div>
                                <div class="th5"><span class="badge bsm" id="detalleSiatEstado"></span></div>
                            </div>
                        </div>
                        <div class="litem"><ion-icon name="document-text-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                            <div class="f1">
                                <div class="tc">Número de Factura</div>
                                <div class="th5" id="detalleSiatNumeroFactura">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- QR de consulta en el portal del SIN.
                         La imagen se genera en el momento a partir de (nit, cuf,
                         numero) con siatModel::urlQR(), que es determinista: no
                         depende de que el QR se haya guardado al validar, asi que
                         una factura de hace meses se reimprime igual.

                         Aca no hay boton de reimprimir: la reimpresion esta en el
                         boton de cada fila de la tabla. Es el unico lugar desde
                         donde tiene sentido, y duplicarla obligaria a mantener
                         dos generadores de comprobante. -->
                    <div class="cb" id="detalleSiatQrBox" style="display:none;align-items:center;gap:16px;flex-wrap:wrap;">
                        <div style="text-align:center;">
                            <img id="detalleSiatQrImg" alt="Código QR de consulta"
                                 style="width:150px;height:150px;image-rendering:pixelated;border:1px solid var(--border-color);border-radius:4px;background:#fff;">
                            <div class="tc" style="margin-top:6px;font-size:11px;">Escanee para consultar<br>la factura en el portal del SIN</div>
                        </div>
                        <div style="flex:1;min-width:220px;">
                            <div class="tc">Verificación</div>
                            <div class="th5" style="font-size:12px;margin-bottom:10px;">
                                El código permite confirmar la factura en el portal del SIN.
                                Para reimprimir el comprobante, use el botón
                                <strong>Imprimir</strong> de esta misma tabla.
                            </div>
                            <a id="detalleSiatQrLink" href="#" target="_blank" rel="noopener"
                               class="btn btn-def" style="text-decoration:none;">
                                <ion-icon name="open-outline"></ion-icon> Abrir en el portal
                            </a>
                        </div>
                    </div>

                    <!-- Anulacion ante el SIN.
                         El boton y su aviso se controlan desde el JS con lo que
                         devuelve siatModel::estadoAnulacionFactura(): no se ofrece
                         anular una factura que el SIN todavia no acepto, ni una
                         que ya esta anulada, ni una que salio por contingencia.
                         El texto del bloqueo viene del modelo, no de un if/elseif
                         aca: asi el motivo que ve el usuario es el mismo que
                         recibe el controller cuando decide no llamar al SIN. -->
                    <div class="cb" id="detalleSiatAnularBox" style="display:none;border-top:1px solid var(--border-color);margin-top:14px;padding-top:14px;">
                        <div class="th5" id="detalleSiatAnularAviso" style="display:none;margin-bottom:10px;"></div>
                        <button type="button" class="btn btn-dan" id="btnAnularFactura">
                            <ion-icon name="close-circle-outline"></ion-icon> Anular factura ante el SIN
                        </button>
                    </div>
                </div>

                <div class="stit">Medicamentos Vendidos</div>
                <div class="card mb16">
                    <div class="cb">
                        <div class="tw" style="max-height: 400px; overflow-y: auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Lote</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Descuento</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaItemsVenta">
                                    <tr>
                                        <td colspan="6" class="txctr">
                                            <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="stit">Totales</div>
                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem">
                                <div class="tc">Subtotal</div>
                                <div class="th5" id="detalleSubtotal">Bs. 0.00</div>
                            </div>
                            <div class="litem">
                                <div class="tc">Impuestos</div>
                                <div class="th5" id="detalleImpuesto">Bs. 0.00</div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb txctr">
                            <div class="tsuc" style="font-size:18px;font-weight:700">TOTAL: <span id="detalleTotal">Bs. 0.00</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="VentasHistorialModals.cerrar('modalDetalleVenta')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Confirmacion de anulacion.
         Va aparte del detalle y no se abre al vuelo: la anulacion ante el SIN
         no tiene vuelta atras, asi que el usuario tiene que ver que factura es
         la que va a anular y elegir el motivo antes de confirmar. Se muestra el
         numero de factura y no el CUF porque el numero es lo que el usuario
         puede comparar contra el papel; el CUF va dentro del QR. La lista de
         motivos la arma el backend desde siat_parametricas, no esta escrita aca.

         El envoltorio .mov no es opcional: en base.css .mov es el overlay
         (position:fixed, opacity 0) y .modal es el panel de dentro. Sin el
         .mov, el panel queda en el flujo normal de la pagina y se ve como un
         formulario suelto al final de la tabla; ademas VentasHistorialModals
         hace abrir()/cerrar() alternando la clase "open" sobre el .mov, que
         es lo que muestra y oculta el overlay. style.css, que si defines esa
         diferencia, esta comentado en head.php. -->
    <div id="modalAnularFactura" class="mov">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="warning-outline"></ion-icon>
                        Anular factura ante el SIN
                    </div>
                    <div class="ms">Esta acción no se puede deshacer</div>
                </div>
                <button class="mcl" onclick="VentasHistorialModals.cerrar('modalAnularFactura')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="card mb16">
                    <div class="cb">
                        <div class="th3 mb8">Se enviará la anulación de la factura al SIN</div>
                        <div class="tbs tmut mb16">
                            El comprobante queda invalidado en el portal del SIN y deja de ser un documento
                            fiscal válido. No tiene vuelta atrás.
                        </div>
                        <div class="litem"><ion-icon name="document-text-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                            <div class="f1">
                                <div class="tc">Factura a anular</div>
                                <div class="th5" id="anularSiatNumeroFactura" style="font-size:12px;"></div>
                            </div>
                        </div>
                        <div class="fg mt16">
                            <label class="fl">Motivo de la anulación</label>
                            <select class="sel" id="anularSiatMotivo"></select>
                        </div>
                        <div class="fg mt16">
                            <label class="fl" for="anularSiatConfirmacion">Escriba ANULAR para confirmar</label>
                            <input class="inp" type="text" id="anularSiatConfirmacion"
                                   placeholder="ANULAR" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="VentasHistorialModals.cerrar('modalAnularFactura')">Cancelar</button>
                <button class="btn btn-dan" id="btnConfirmarAnularFactura">
                    <ion-icon name="close-circle-outline"></ion-icon> Anular ante el SIN
                </button>
            </div>
        </div>
    </div>

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


<script src="<?php echo SERVER_URL; ?>views/script/ventasHistorialLista-view.js"></script>
