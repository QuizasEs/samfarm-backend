<?php
if (isset($_SESSION['id_smp']) && $_SESSION['rol_smp'] == 1) {
?>

    <div class="">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="business-outline"></ion-icon> Gestión de Sucursales</div>
                <div class="psub">Administre y consulte la información detallada de sus sucursales</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="SucursalesModals.abrirModalNuevo()">
                    <ion-icon name="add-circle-outline"></ion-icon> Nuevo Sucursal
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDF">
                    <ion-icon name="document-text-outline"></ion-icon> PDF
                </button>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico" id="filtroSucursales">
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="estado_filtro" id="estado_filtro">
                                <option value="">Todas</option>
                                <option value="1">Activas</option>
                                <option value="0">Inactivas</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" id="busqueda_sucursal" placeholder="Buscar por nombre o dirección...">
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
                <div id="contenedorGrillasSucursales" class="grillas-container">
                    <div class="loader-grillas">
                        <ion-icon name="hourglass-outline"></ion-icon>
                        <p>Cargando sucursales...</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- grafico de sucursales -->
        <div class="card mb16">
            <div class="ch">
                <div class="ct">
                    <ion-icon name="bar-chart-outline"></ion-icon> Análisis Costo-Beneficio por Sucursal
                    <div class="grafico-periodo">
                        <select id="periodoGrafico" class="sel">
                            <option value="mes">Último Mes</option>
                            <option value="trimestre">Último Trimestre</option>
                            <option value="semestre" selected>Último Semestre</option>
                            <option value="anio">Último Año</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="cb">
                <div id="graficoCostoBeneficio"></div>
            </div>
        </div>
    </div>
    <!-- cajas abiertas -->
    <div class="mov" id="modalCajasAbiertas" style="display: none;">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="cash-outline"></ion-icon> Cajas Abiertas - <span id="modalCajasNombreSucursal">...</span></div>
                    <div class="ms">Lista de cajas abiertas en esta sucursal</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalCajas()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <input type="hidden" id="modalCajasSuId">

                <div class="fr2">
                    <div class="fg">
                        <label class="fl">Usuario</label>
                        <select class="sel" id="filtroUsuarioCaja">
                            <option value="">Todos</option>
                        </select>
                    </div>

                    <div class="fg">
                        <label class="fl">Buscar caja</label>
                        <div class="inpg">
                            <input class="inp" type="text" id="busquedaCaja" placeholder="Buscar por nombre de caja...">
                            <button type="button" class="btn btn-def" onclick="SucursalesModals.buscarCajas()">
                                <ion-icon name="search-outline"></ion-icon>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tw table-detail">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="55%">Caja</th>
                                <th width="25%">Detalles</th>
                                <th width="20%">Tiempo</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCajasAbiertas">
                            <tr>
                                <td colspan="4" style="text-align:center;">
                                    <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalCajas()">Cerrar</button>
            </div>
        </div>
    </div>
    <!-- MODAL NUEVA SUCURSAL -->
    <div class="mov" id="modalNuevaSucursal" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="add-circle-outline"></ion-icon> Nueva Sucursal</div>
                    <div class="ms">Registre una nueva sucursal en el sistema</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalNuevo()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <form id="formNuevaSucursal" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/sucursalAjax.php" method="POST" data-form="save" autocomplete="off">
                    <input type="hidden" name="sucursalAjax" value="nuevo">

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="information-circle-outline"></ion-icon> Información de la Sucursal</h3>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl required">Nombre de la Sucursal</label>
                            <input class="inp" type="text" name="Nombre_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]{3,120}" maxlength="120" required>
                        </div>
                    </div>

                    <div class="fr2">
                        <div class="fg">
                            <label class="fl required">Dirección</label>
                            <input class="inp" type="text" name="Direccion_reg" maxlength="250" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_reg" pattern="[0-9+\-\s()]{6,30}" maxlength="30">
                        </div>
                    </div>
                </form>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalNuevo()">Cancelar</button>
                <button type="submit" form="formNuevaSucursal" class="btn btn-def">Registrar</button>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR SUCURSAL -->
    <div class="mov" id="modalEditarSucursal" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="create-outline"></ion-icon> Editar Sucursal</div>
                    <div class="ms">Modifique la información de la sucursal</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalEditar()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <form id="formEditarSucursal" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/sucursalAjax.php" method="POST" data-form="update" autocomplete="off">
                    <input type="hidden" name="sucursalAjax" value="editar">
                    <input type="hidden" name="su_id_editar" id="su_id_editar">

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="information-circle-outline"></ion-icon> Información de la Sucursal</h3>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl required">Nombre de la Sucursal</label>
                            <input class="inp" type="text" name="Nombre_edit" id="Nombre_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]{3,120}" maxlength="120" required>
                        </div>
                    </div>

                    <div class="fr2">
                        <div class="fg">
                            <label class="fl required">Dirección</label>
                            <input class="inp" type="text" name="Direccion_edit" id="Direccion_edit" maxlength="250" required>
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_edit" id="Telefono_edit" pattern="[0-9+\-\s()]{6,30}" maxlength="30">
                        </div>
                    </div>


                </form>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalEditar()">Cancelar</button>
                <button type="submit" form="formEditarSucursal" class="btn btn-def">Guardar Cambios</button>
            </div>
        </div>
    </div>

    <!-- MODAL DETALLE SUCURSAL -->
    <div class="mov" id="modalDetalleSucursal" style="display: none;">
        <div class="modal" style="max-width: 1200px;">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="business-outline"></ion-icon> Detalle de Sucursal - <span id="detalleNombreSucursal">...</span></div>
                    <div class="ms">Información completa y estadísticas de la sucursal</div>
                </div>
                <button class="mcl" onclick="SucursalesModals.cerrarModalDetalle()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <input type="hidden" id="detalleSuId">

                <div class="tb">
                    <h3 class="th3"><ion-icon name="information-circle-outline"></ion-icon> Información General</h3>
                </div>

                <div class="fr">
                    <div class="card">
                        <div class="cb">


                            <div class="litem">
                                <ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="tc">Nombre:</label>
                                    <p class="th5" id="detalleNombreSucursalInfo">-</p>
                                </div>
                            </div>
                            <div class="litem">
                                <ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="f1">Dirección:</label>
                                    <p class="tbs" id="detalleDireccionSucursal">-</p>
                                </div>
                            </div>
                            <div class="litem">
                                <ion-icon name="call-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="f1">Teléfono:</label>
                                    <p class="tbs" id="detalleTelefonoSucursal">-</p>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem">
                                <ion-icon name="power-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="f1">Estado:</label>
                                    <p class="tbs" id="detalleEstadoSucursal">-</p>
                                </div>
                            </div>
                            <div class="litem">
                                <ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="fr1">
                                    <label class="fl">Fecha de Creación:</label>
                                    <p class="tbs" id="detalleFechaCreacionSucursal">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tb">
                    <h3 class="th3"><ion-icon name="stats-chart-outline"></ion-icon> Estadísticas de Ventas</h3>
                </div>

                <div class="fr2">
                    <div class="fg">
                        <div id="graficoVentasSucursal" style="width:100%;height:300px;background:var(--bg-primary);border-radius:8px;"></div>
                    </div>
                    <div class="fg">
                        <div class="statc">
                            <div class="stat">
                                <label>Total Ventas:</label>
                                <p id="detalleTotalVentasSucursal">0</p>
                            </div>
                            <div class="stat">
                                <label>Monto Total:</label>
                                <p id="detalleMontoTotalSucursal">Bs. 0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tb">
                    <h3 class="th3"><ion-icon name="people-outline"></ion-icon> Personal Asignado</h3>
                </div>
                <div class="tw">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPersonalSucursal">
                            <tr>
                                <td colspan="4" style="text-align:center;">
                                    <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="SucursalesModals.cerrarModalDetalle()">Cerrar</button>
                <button class="btn btn-def" onclick="SucursalesModals.editarDesdeDetalle()"><ion-icon name="create-outline"></ion-icon> Editar</button>
                <button class="btn btn-out" id="btnToggleEstadoDetalleSucursal"><ion-icon name="power-outline"></ion-icon> Cambiar Estado</button>
            </div>
        </div>
    </div>

    <style>
        #graficoCostoBeneficio {
            height: 320px !important;
            min-height: 320px !important;
            width: 100% !important;
            display: block !important;
        }
    </style>

    <!-- modal sucursales editar y registrar -->
    <script src="<?php echo SERVER_URL; ?>views/script/sucursalLista-view.js"></script>

<?php } else { ?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb">
                <div style="text-align: center; padding: 60px;">
                    <ion-icon name="lock-closed-outline" style="font-size: 64px; color: #ccc;"></ion-icon>
                </div>
            </div>
        </div>
    </div>
<?php } ?>