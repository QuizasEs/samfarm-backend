<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/inventarioAjax.php"
        data-ajax-param="inventarioAjax"
        data-ajax-registros="10">
        <div class="title">
            <h2>
                <ion-icon name="bandage-outline"></ion-icon> Inventario
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <!-- Select 1: Laboratorio -->
                <div class="form-fechas">
                    <small>Laboratorios</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todos los laboratorios</option>
                        <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                            <option value="<?php echo $lab['la_id'] ?>"><?php echo $lab['la_nombre_comercial'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Select 2: Estado de Stock-->
                <div class="form-fechas">
                    <small>Estados</small>
                    <select class="select-filtro" name="select2">
                        <option value="">Todos los estados</option>
                        <option value="agotado">Agotado</option>
                        <option value="critico">Crítico</option>
                        <option value="bajo">Bajo</option>
                        <option value="normal">Normal</option>
                        <option value="exceso">Exceso</option>
                        <option value="sin_definir">Sin Definir</option>
                    </select>
                </div>

                <!-- Select 3: Sucursal (solo admin) -->
                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                    <div class="form-fechas">
                        <small>Sucursales</small>
                        <select class="select-filtro" name="select3">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <!-- Select 4: Forma Farmacéutica -->
                <div class="form-fechas">
                    <small>Forma Farmaceutica</small>
                    <select class="select-filtro" name="select4">
                        <option value="">Todas las formas</option>
                        <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                            <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="search">
                    <!-- Búsqueda -->
                    <input type="text" name="busqueda" placeholder="Buscar por nombre, principio activo o código...">

                    <button type="button" class="">
                        <ion-icon name="search"></ion-icon>
                    </button>

                </div>

            </div>
            <!-- Botón Exportar Excel -->
            <button type="button" class="btn success" id="btnExportarExcel" style="margin-left: 10px;">
                <ion-icon name="download-outline"></ion-icon> Excel
            </button>
        </form>

        <div class="tabla-contenedor"></div>
    </div>


    <div class="modal" id="modalDetalleInventario" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="eye-outline"></ion-icon>
                    Detalle de Inventario - <span id="modalDetalleMedicamento">...</span>
                </div>
                <a class="close" onclick="InventarioModals.cerrar('modalDetalleInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="modalDetalleInvId">
            <input type="hidden" id="modalDetalleMedId">
            <input type="hidden" id="modalDetalleSuId">

            <div class="modal-group">
                <div class="row">
                    <h3> Información General</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Laboratorio:</label>
                        <p id="detalleLaboral">-</p>
                    </div>
                    <div class="col">
                        <label>Sucursal:</label>
                        <p id="detalleSucursal">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Total Cajas:</label>
                        <p id="detalleCajas">-</p>
                    </div>
                    <div class="col">
                        <label>Total Unidades:</label>
                        <p id="detalleUnidades">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Valor Inventario:</label>
                        <p id="detalleValorado">-</p>
                    </div>
                    <div class="col">
                        <label>Estado:</label>
                        <p id="detalleEstado">-</p>
                    </div>
                </div>

                <div class="row">
                    <h3> Lotes Disponibles</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N° Lote</th>
                                    <th>Unidades</th>
                                    <th>Precio</th>
                                    <th>Vencimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaLotesDetalle">
                                <tr>
                                    <td colspan="5" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn default" onclick="InventarioModals.cerrar('modalDetalleInventario')">
                        Cerrar
                    </a>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="modalHistorialInventario" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="time-outline"></ion-icon>
                    Historial de Movimientos - <span id="modalHistorialMedicamento">...</span>
                </div>
                <a class="close" onclick="InventarioModals.cerrar('modalHistorialInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="modalHistorialMedId">
            <input type="hidden" id="modalHistorialSuId">

            <div class="modal-group">
                <div class="row">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Cantidad</th>
                                    <th>Lote</th>
                                    <th>Usuario</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="tablaHistorialMovimientos">
                                <tr>
                                    <td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn default" onclick="InventarioModals.cerrar('modalHistorialInventario')">
                        Cerrar
                    </a>
                </div>
            </div>
        </div>
    </div>


    <div class="modal" id="modalConfiguracionInventario" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="settings-outline"></ion-icon>
                    Configurar Inventario - <span id="modalConfiguracionMedicamento">...</span>
                </div>
                <a class="close" onclick="InventarioModals.cerrar('modalConfiguracionInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="modalConfiguracionInvId">
            <input type="hidden" id="modalConfiguracionMedId">
            <input type="hidden" id="modalConfiguracionSuId">

            <div class="modal-group">
                <div class="row">
                    <div class="col">
                        <div class="modal-bloque">
                            <label for="configuracionMinimo">Cantidad Mínima</label>
                            <input type="number" id="configuracionMinimo" min="0" value="0">
                            <small style="color: #666;">Unidades mínimas antes de alertar</small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="modal-bloque">
                            <label for="configuracionMaximo">Cantidad Máxima</label>
                            <input type="number" id="configuracionMaximo" min="0" placeholder="Opcional">
                            <small style="color: #666;">Unidades máximas permitidas (dejar vacío sin límite)</small>
                        </div>
                    </div>
                </div>

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn warning" onclick="InventarioModals.cerrar('modalConfiguracionInventario')">
                        Cancelar
                    </a>
                    <a href="javascript:void(0)" class="btn success" onclick="InventarioModals.guardarConfiguracion()">
                        <ion-icon name="checkmark-outline"></ion-icon> Guardar
                    </a>
                </div>
            </div>
        </div>
    </div>



<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2>⛔ Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>