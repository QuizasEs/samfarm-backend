<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2 || $_SESSION['rol_smp'] == 3)) {
?>

    <div class="pg tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/clientesAjax.php"
        data-ajax-param="clientesAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">Gestión de Clientes</div>
                <div class="psub">Administre y consulte la información detallada de sus clientes</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="ClientesModals.abrirModalNuevo()">
                    <ion-icon name="person-add-outline"></ion-icon> Nuevo Cliente
                </button>
                <?php if ($_SESSION['rol_smp'] != 3) { ?>
                    <button type="button" class="btn btn-sec" id="btnExportarExcelClientes" data-tip="Exportar a Excel">
                        <ion-icon name="download-outline"></ion-icon> Excel
                    </button>
                    <button type="button" class="btn btn-sec" id="btnExportarPDFClientes" data-tip="Exportar a PDF">
                        <ion-icon name="document-text-outline"></ion-icon> PDF
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
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todos</option>
                                <option value="activo">Activos</option>
                                <option value="inactivo">Inactivos</option>
                            </select>
                        </div>

                        <!-- <div class="fg">
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
                        </div> -->
                        <div class="fg">
                            <label class="fl">Desde</label>
                            <input class="inp" type="date" name="fecha_desde">
                        </div>

                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta">
                        </div>
                    </div>

                    <div class="fr1">
                        

                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Nombre, CI, teléfono...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
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
    <!-- Modal Editar Cliente -->
    <div class="mov" id="modalEditarCliente" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Editar Cliente</div>
                    <div class="ms">Actualice la información del cliente seleccionado</div>
                </div>
                <button class="mcl" onclick="ClientesModals.cerrarModalEditar()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/clientesAjax.php" method="POST" data-form="update" autocomplete="off">
                <div class="mb">
                    <input type="hidden" name="clientesAjax" value="editar">
                    <input type="hidden" name="cl_id_editar" id="cl_id_editar">

                    <div class="fg">
                        <label class="fl req">Nombres</label>
                        <input class="inp" type="text" name="Nombres_cl" id="Nombres_cl_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl req">Apellido Paterno</label>
                            <input class="inp" type="text" name="Paterno_cl" id="Paterno_cl_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Apellido Materno</label>
                            <input class="inp" type="text" name="Materno_cl" id="Materno_cl_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100">
                        </div>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl req">Carnet / CI</label>
                            <input class="inp" type="number" name="Carnet_cl" id="Carnet_cl_edit" pattern="[0-9]{6,20}" maxlength="20">
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="number" name="Telefono_cl" id="Telefono_cl_edit" pattern="[0-9]{6,20}" maxlength="20">
                        </div>
                    </div>

                    <div class="fg">
                        <label class="fl">Dirección</label>
                        <input class="inp" type="text" name="Direccion_cl" id="Direccion_cl_edit">
                    </div>

                    <div class="fg" style="margin-bottom:0">
                        <label class="fl">Correo Electrónico</label>
                        <input class="inp" type="email" name="Correo_cl" id="Correo_cl_edit">
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn btn-war" onclick="ClientesModals.cerrarModalEditar()">Cancelar</button>
                    <button type="submit" class="btn btn-def">Actualizar Cliente</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal Nuevo Cliente -->
    <div class="mov" id="modalNuevoCliente" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Nuevo Cliente</div>
                    <div class="ms">Complete los datos para registrar un nuevo cliente</div>
                </div>
                <button class="mcl" onclick="ClientesModals.cerrarModalNuevo()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/clientesAjax.php" method="POST" data-form="save" autocomplete="off">
                <div class="mb">
                    <input type="hidden" name="clientesAjax" value="nuevo">

                    <div class="fg">
                        <label class="fl req">Nombres</label>
                        <input class="inp" type="text" name="Nombres_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl req">Apellido Paterno</label>
                            <input class="inp" type="text" name="Paterno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Apellido Materno</label>
                            <input class="inp" type="text" name="Materno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100">
                        </div>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl req">Carnet / CI</label>
                            <input class="inp" type="number" name="Carnet_cl" pattern="[0-9]{6,20}" maxlength="20">
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="number" name="Telefono_cl" pattern="[0-9]{6,20}" maxlength="20">
                        </div>
                    </div>

                    <div class="fg">
                        <label class="fl">Dirección</label>
                        <input class="inp" type="text" name="Direccion_cl">
                    </div>

                    <div class="fg" style="margin-bottom:0">
                        <label class="fl">Correo Electrónico</label>
                        <input class="inp" type="email" name="Correo_cl">
                    </div>
                </div>

                <div class="mf">
                    <button type="button" class="btn btn-war" onclick="ClientesModals.cerrarModalNuevo()">Cancelar</button>
                    <button type="submit" class="btn btn-def">Registrar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detalle Cliente -->
    <div class="mov" id="modalDetalleCliente" style="display: none;">
        <div class="modal mxl ">
            <div class="mh">
                <div>
                    <div class="mt">Detalle del Cliente</div>
                    <div class="ms" id="detalleClienteNombre">...</div>
                </div>
                <button class="mcl" onclick="ClientesModals.cerrarModalDetalle()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <input type="hidden" id="detalleClienteId">

                <div class="stit">Información Personal</div>
                <div class="fr">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Nombre Completo</div><div class="th5" id="detalleNombreCompleto">-</div></div></div>
                            <div class="litem"><ion-icon name="card-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">CI / Carnet</div><div class="th5" id="detalleCarnet">-</div></div></div>
                            <div class="litem"><ion-icon name="call-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Teléfono</div><div class="th5" id="detalleTelefono">-</div></div></div>
                            <div class="litem"><ion-icon name="mail-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Correo Electrónico</div><div class="th5" id="detalleCorreo">-</div></div></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Dirección</div><div class="th5" id="detalleDireccion">-</div></div></div>
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
                    <div class="statc"><div class="siw ww"><ion-icon name="document-text-outline"></ion-icon></div><div><div class="sv" id="detalleFacturasEmitidas">0</div><div class="sl">Facturas</div></div></div>
                    <div class="statc"><div class="siw bl"><ion-icon name="stats-chart-outline"></ion-icon></div><div><div class="sv" id="detallePromedio">Bs. 0.00</div><div class="sl">Promedio</div></div></div>
                </div>

                <div class="stit">Resumen de Actividad</div>
                <div class="fr">
                    <div class="card">
                        <div class="ch"><span class="ct">Medicamentos más Comprados</span></div>
                        <div class="tw">
                             <table class="table-detail" style="font-size: 12px;">
                                 <thead>
                                     <tr>
                                         <th>N°</th>
                                         <th>Medicamento</th>
                                         <th>Estadísticas</th>
                                     </tr>
                                 </thead>
                                 <tbody id="tablaMedicamentosMasComprados">
                                     <tr><td colspan="3" class="txctr tmut">Cargando...</td></tr>
                                 </tbody>
                             </table>
                        </div>
                    </div>
                    <div class="card">
                        <div class="ch"><span class="ct">Gráfico Mensual</span></div>
                        <div class="cb">
                            <div id="graficoComprasMensuales" style="width: 100%; height: 250px;"></div>
                        </div>
                    </div>
                </div>

                <div class="stit">Últimas Compras</div>
                <div class="card">
                    <div class="tw">
                         <table class="table-detail" style="font-size: 12px;">
                             <thead>
                                 <tr>
                                     <th>Documento</th>
                                     <th>Medicamentos</th>
                                     <th>Detalles</th>
                                     <th>Vendedor</th>
                                 </tr>
                             </thead>
                             <tbody id="tablaUltimasCompras">
                                 <tr><td colspan="4" class="txctr tmut">Cargando...</td></tr>
                             </tbody>
                         </table>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="ClientesModals.cerrarModalDetalle()">Cerrar</button>
                <?php if ($_SESSION['rol_smp'] != 3) { ?>
                    <button type="button" class="btn btn-dan" id="btnToggleEstadoDetalle">
                        <ion-icon name="power-outline"></ion-icon> Estado
                    </button>
                <?php } ?>
                <button type="button" class="btn btn-def" onclick="ClientesModals.editarDesdeDetalle()">
                    <ion-icon name="create-outline"></ion-icon> Editar
                </button>
                <?php if ($_SESSION['rol_smp'] != 3) { ?>
                    <button type="button" class="btn btn-sec" onclick="ClientesModals.exportarPDFDetalle(document.getElementById('detalleClienteId').value)">
                        <ion-icon name="document-text-outline"></ion-icon> PDF
                    </button>
                    <button type="button" class="btn btn-sec" onclick="ClientesModals.verHistorialCompleto()">
                        <ion-icon name="time-outline"></ion-icon> Historial
                    </button>
                <?php } ?>
            </div>
        </div>
    </div>

        <!-- modal clientes edicion detalles -->
        <script src="<?php echo SERVER_URL; ?>views/script/clientes.js"></script>



<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>