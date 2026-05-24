<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/proveedoresAjax.php"
        data-ajax-param="proveedoresAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">Gestión de Proveedores</div>
                <div class="psub">Administre y consulte la información detallada de sus proveedores</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="ProveedoresModals.abrirRegistro()">
                    <ion-icon name="person-add-outline"></ion-icon> Nuevo Proveedor
                </button>
                <button type="button" class="btn btn-out" id="btnExportarExcelProveedor" data-tip="Exportar a Excel">
                    <ion-icon name="download-outline"></ion-icon> Excel
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDFProveedor" data-tip="Exportar a PDF">
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
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todos</option>
                                <option value="activo">Activos</option>
                                <option value="inactivo">Inactivos</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Con Compras</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todos</option>
                                <option value="con_compras">Con compras</option>
                                <option value="sin_compras">Sin compras</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Última Compra</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todos</option>
                                <option value="7">Últimos 7 días</option>
                                <option value="30">Últimos 30 días</option>
                                <option value="90">Últimos 90 días</option>
                                <option value="mas_90">Más de 90 días</option>
                                <option value="nunca">Nunca</option>
                            </select>
                        </div>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Desde</label>
                            <input class="inp" type="date" name="fecha_desde" title="Fecha desde">
                        </div>

                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta" title="Fecha hasta">
                        </div>
                    </div>

                    <div class="fg">
                        <label class="fl">Búsqueda rápida</label>
                        <div class="inpg">
                            <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre, NIT o teléfono...">
                            <button type="button" class="btn btn-def btn-search">
                                <ion-icon name="search-outline"></ion-icon>
                            </button>
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
    <!-- modal para detalles -->
    <div class="mov" id="modalDetalleProveedor" style="display: none;">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Detalle del Proveedor</div>
                    <div class="ms" id="modalDetalleProveedorNombre">...</div>
                </div>
                <button class="mcl" onclick="ProveedoresModals.cerrar('modalDetalleProveedor')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <input type="hidden" id="modalDetallePrId">

            <div class="mb">
                <div class="stit">Información del Proveedor</div>
                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Nombre Completo</div><div class="th5" id="detalleNombreCompleto">-</div></div></div>
                            <div class="litem"><ion-icon name="card-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">NIT</div><div class="th5" id="detalleNit">-</div></div></div>
                            <div class="litem"><ion-icon name="call-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Teléfono</div><div class="th5" id="detalleTelefono">-</div></div></div>
                            <div class="litem"><ion-icon name="mail-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Correo</div><div class="th5" id="detalleCorreo">-</div></div></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Nombre Comercial</div><div class="th5" id="detalleNombreComercial">-</div></div></div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Fecha de Registro</div><div class="th5" id="detalleFechaRegistro">-</div></div></div>
                            <div class="litem"><ion-icon name="time-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Antigüedad</div><div class="th5"><span id="detalleAntiguedad">-</span> días</div></div></div>
                            <div class="litem" style="border:none"><ion-icon name="shield-checkmark-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Estado</div><div id="detalleEstado">-</div></div></div>
                        </div>
                    </div>
                </div>

                <div class="stit">Estadísticas de Compra</div>
                <div class="grid4">
                    <div class="statc"><div class="siw gr"><ion-icon name="cart-outline"></ion-icon></div><div><div class="sv" id="detalleTotalCompras">0</div><div class="sl">Total Compras</div></div></div>
                    <div class="statc"><div class="siw bl"><ion-icon name="cash-outline"></ion-icon></div><div><div class="sv" id="detalleMontoTotal">Bs. 0.00</div><div class="sl">Monto Total</div></div></div>
                    <div class="statc"><div class="siw ww"><ion-icon name="document-text-outline"></ion-icon></div><div><div class="sv" id="detalleTotalLotes">0</div><div class="sl">Lotes Generados</div></div></div>
                    <div class="statc"><div class="siw bl"><ion-icon name="stats-chart-outline"></ion-icon></div><div><div class="sv" id="detallePromedio">Bs. 0.00</div><div class="sl">Promedio por Compra</div></div></div>
                </div>

                <div class="stit">Última Compra</div>
                <div class="card">
                    <div class="cb">
                        <div class="th5" id="detalleUltimaCompra">-</div>
                    </div>
                </div>

                <div class="stit">Últimas 5 Compras</div>
                <div class="card">
                    <div class="tw">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N° Compra</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Total</th>
                                    <th>Items</th>
                                    <th>N° Factura</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUltimasCompras">
                                <tr>
                                    <td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="stit">Top 5 Medicamentos Suministrados</div>
                <div class="card">
                    <div class="tw">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Medicamento</th>
                                    <th>Veces Comprado</th>
                                    <th>Proveedor</th>
                                    <th>Última Compra</th>
                                </tr>
                            </thead>
                            <tbody id="tablaTopMedicamentos">
                                <tr>
                                    <td colspan="4" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="ProveedoresModals.cerrar('modalDetalleProveedor')">Cerrar</button>
            </div>
        </div>
    </div>
    <!-- Modal Nuevo Proveedor -->
    <div class="mov" id="modalRegistroProveedor" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Nuevo Proveedor</div>
                    <div class="ms">Complete los datos para registrar un nuevo proveedor</div>
                </div>
                <button class="mcl" onclick="ProveedoresModals.cerrarRegistro()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL ?>ajax/proveedoresAjax.php" method="POST" autocomplete="off" data-form="save" id="formRegistroProveedor">
                <div class="mb">
                    <input type="hidden" name="proveedoresAjax" value="registrar">

                    <div class="fg">
                        <label class="fl required">Nombres / Razón Social</label>
                        <input class="inp" type="text" name="Nombres_pr" id="registroNombres"  maxlength="100" requireduired>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl required">NIT</label>
                            <input class="inp" type="text" name="Nit_pr" id="registroNit" pattern="[0-9]{6,30}" maxlength="30" requireduired>
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_pr" id="registroTelefono" pattern="[0-9]{6,30}" maxlength="30">
                        </div>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Correo</label>
                            <input class="inp" type="email" name="Correo_pr" id="registroCorreo" maxlength="200">
                        </div>
                        <div class="fg">
                            <label class="fl">Nombre Comercial</label>
                            <input class="inp" type="text" name="Direccion_pr" id="registroDireccion" maxlength="250">
                        </div>
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn btn-war" onclick="ProveedoresModals.cerrarRegistro()">Cancelar</button>
                    <button type="submit" class="btn btn-def">
                        <ion-icon name="checkmark-outline"></ion-icon> Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Proveedor -->
    <div class="mov" id="modalEdicionProveedor" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Editar Proveedor</div>
                    <div class="ms">Actualice la información del proveedor seleccionado</div>
                </div>
                <button class="mcl" onclick="ProveedoresModals.cerrarEdicion()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL ?>ajax/proveedoresAjax.php" method="POST" autocomplete="off" data-form="update" id="formEdicionProveedor">
                <div class="mb">
                    <input type="hidden" name="proveedoresAjax" value="actualizar">
                    <input type="hidden" name="PrId_up" id="edicionPrId">

                    <div class="fg">
                        <label class="fl required">Nombres / Razón Social</label>
                        <input class="inp" type="text" name="Nombres_pr_up" id="edicionNombres"  maxlength="120" requireduired>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl required">NIT</label>
                            <input class="inp" type="text" name="Nit_pr_up" id="edicionNit" pattern="[0-9]{6,30}" maxlength="30" requireduired>
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_pr_up" id="edicionTelefono" pattern="[0-9]{6,30}" maxlength="30">
                        </div>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Correo</label>
                            <input class="inp" type="email" name="Correo_pr_up" id="edicionCorreo" maxlength="200">
                        </div>
                        <div class="fg">
                            <label class="fl">Nombre Comercial</label>
                            <input class="inp" type="text" name="Direccion_pr_up" id="edicionDireccion" maxlength="250">
                        </div>
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn btn-war" onclick="ProveedoresModals.cerrarEdicion()">Cancelar</button>
                    <button type="submit" class="btn btn-def">
                        <ion-icon name="checkmark-outline"></ion-icon> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- generar excel para el modulo proveedor -->

    <!-- escript para modal y funcionamientos del mismo del modulo de proveedor -->
    <script src="<?php echo SERVER_URL; ?>views/script/proveedorLista-view.js"></script>




<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
