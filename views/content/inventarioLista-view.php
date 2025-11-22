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

        <form class="filtro-dinamico">
            <div class="search">

                <!-- Select 1: Laboratorio -->
                <select class="select-filtro" name="select1">
                    <option value="">Todos los laboratorios</option>
                    <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                        <option value="<?php echo $lab['la_id'] ?>">🏭 <?php echo $lab['la_nombre_comercial'] ?></option>
                    <?php } ?>
                </select>

                <!-- Select 2: Estado de Stock (CORREGIDO) -->
                <select class="select-filtro" name="select2">
                    <option value="">Todos los estados</option>
                    <option value="agotado">❌ Agotado</option>
                    <option value="critico">🔴 Crítico</option>
                    <option value="bajo">⚠️ Bajo</option>
                    <option value="normal">✅ Normal</option>
                    <option value="exceso">📦 Exceso</option>
                    <option value="sin_definir">❓ Sin Definir</option>
                </select>

                <!-- Select 3: Sucursal (solo admin) -->
                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                    <select class="select-filtro" name="select3">
                        <option value="">Todas las sucursales</option>
                        <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>

                <!-- Select 4: Forma Farmacéutica -->
                <select class="select-filtro" name="select4">
                    <option value="">Todas las formas</option>
                    <?php foreach ($datos_select['formas_farmaceuticas'] as $forma) { ?>
                        <option value="<?php echo $forma['ff_id'] ?>">💊 <?php echo $forma['ff_nombre'] ?></option>
                    <?php } ?>
                </select>

                <!-- Búsqueda -->
                <input type="text" name="busqueda" placeholder="Buscar por nombre, principio activo o código...">

                <button type="button" class="btn-search">
                    <ion-icon name="search-outline"></ion-icon>
                </button>

                <!-- Botón Exportar Excel -->
                <button type="button" class="btn success" id="btnExportarExcel" style="margin-left: 10px;">
                    <ion-icon name="download-outline"></ion-icon> Excel
                </button>
            </div>
        </form>

        <div class="tabla-contenedor"></div>
    </div>

    <!-- ========================================
     MODAL 1: VER DETALLE DE INVENTARIO
     ======================================== -->
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
                    <h3>📊 Información General</h3>
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
                    <h3>📦 Lotes Disponibles</h3>
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

    <!-- ========================================
     MODAL 2: TRANSFERIR INVENTARIO
     ======================================== -->
    <div class="modal" id="modalTransferirInventario" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="swap-horizontal-outline"></ion-icon>
                    Transferir Inventario - <span id="modalTransferirMedicamento">...</span>
                </div>
                <a class="close" onclick="InventarioModals.cerrar('modalTransferirInventario')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="modalTransferirInvId">
            <input type="hidden" id="modalTransferirMedId">
            <input type="hidden" id="modalTransferirSuOrigenId">

            <div class="modal-group">
                <div class="row">
                    <label for="transferirSucursalDestino" class="required">Sucursal Destino</label>
                    <select id="transferirSucursalDestino" required>
                        <option value="">Seleccione sucursal...</option>
                        <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="row">
                    <label for="transferirLote" class="required">Lote a Transferir</label>
                    <select id="transferirLote" required>
                        <option value="">Seleccione lote...</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="modal-bloque">
                            <label for="transferirCantidad" class="required">Cantidad (Unidades)</label>
                            <input type="number" id="transferirCantidad" min="1" required>
                            <small id="transferirStockDisponible" style="color: #666;"></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <label for="transferirMotivo">Motivo de Transferencia</label>
                    <textarea id="transferirMotivo" rows="3" placeholder="Opcional..."></textarea>
                </div>

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn warning" onclick="InventarioModals.cerrar('modalTransferirInventario')">
                        Cancelar
                    </a>
                    <a href="javascript:void(0)" class="btn success" onclick="InventarioModals.procesarTransferencia()">
                        <ion-icon name="checkmark-outline"></ion-icon> Transferir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================
     MODAL 3: HISTORIAL DE MOVIMIENTOS
     ======================================== -->
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

    <!-- ========================================
     🔹 FUNCIONES GENÉRICAS PARA MODALES DE INVENTARIO
     ======================================== -->
    <script>
        /**
         * ============================================================
         * INVENTARIO MODALS - Sistema Corregido
         * ============================================================
         */
        const InventarioModals = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php';

            // ==================== UTILIDADES ====================
            const utils = {
                async ajax(params) {
                    try {
                        console.log('📡 Enviando petición:', params);

                        const response = await fetch(API_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams(params)
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }

                        const data = await response.json();
                        console.log('✅ Respuesta recibida:', data);
                        return data;

                    } catch (error) {
                        console.error('❌ Error AJAX:', error);
                        throw error;
                    }
                },

                abrir(modalId) {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.style.display = 'flex';
                        console.log(`✅ Modal abierto: ${modalId}`);
                    } else {
                        console.error(`❌ Modal no encontrado: ${modalId}`);
                    }
                },

                cerrar(modalId) {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.style.display = 'none';
                        console.log(`✅ Modal cerrado: ${modalId}`);
                    }
                },

                formatearFecha(fecha) {
                    if (!fecha) return 'N/A';
                    const d = new Date(fecha);
                    const dia = String(d.getDate()).padStart(2, '0');
                    const mes = String(d.getMonth() + 1).padStart(2, '0');
                    const anio = d.getFullYear();
                    return `${dia}/${mes}/${anio}`;
                },

                formatearNumero(num) {
                    return parseInt(num || 0).toLocaleString('es-BO');
                },

                formatearMoneda(num) {
                    return 'Bs ' + parseFloat(num || 0).toFixed(2);
                }
            };

            // ==================== MODAL DETALLE ====================
            const detalle = {
                async abrir(invId, medId, suId, medicamento) {
                    console.log('📋 Abriendo detalle:', {
                        invId,
                        medId,
                        suId,
                        medicamento
                    });

                    document.getElementById('modalDetalleMedicamento').textContent = medicamento;
                    document.getElementById('modalDetalleInvId').value = invId;
                    document.getElementById('modalDetalleMedId').value = medId;
                    document.getElementById('modalDetalleSuId').value = suId;

                    utils.abrir('modalDetalleInventario');

                    // Mostrar loading
                    document.getElementById('tablaLotesDetalle').innerHTML =
                        '<tr><td colspan="5" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                    try {
                        const data = await utils.ajax({
                            inventarioAjax: 'detalle',
                            inv_id: invId,
                            med_id: medId,
                            su_id: suId
                        });

                        if (data.error) {
                            throw new Error(data.error);
                        }

                        document.getElementById('detalleLaboral').textContent = data.laboratorio || 'N/A';
                        document.getElementById('detalleSucursal').textContent = data.sucursal || 'N/A';
                        document.getElementById('detalleCajas').textContent = utils.formatearNumero(data.cajas);
                        document.getElementById('detalleUnidades').textContent = utils.formatearNumero(data.unidades);
                        document.getElementById('detalleValorado').textContent = utils.formatearMoneda(data.valorado);
                        document.getElementById('detalleEstado').innerHTML = data.estado_html || 'N/A';

                        const tbody = document.getElementById('tablaLotesDetalle');
                        if (data.lotes && data.lotes.length > 0) {
                            tbody.innerHTML = data.lotes.map(lote => `
                        <tr>
                            <td>${lote.numero_lote}</td>
                            <td>${utils.formatearNumero(lote.unidades)}</td>
                            <td>${utils.formatearMoneda(lote.precio)}</td>
                            <td>${utils.formatearFecha(lote.vencimiento)}</td>
                            <td>${lote.estado}</td>
                        </tr>
                    `).join('');
                        } else {
                            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin lotes</td></tr>';
                        }

                    } catch (error) {
                        console.error('❌ Error:', error);
                        Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                    }
                }
            };

            // ==================== MODAL TRANSFERIR ====================
            const transferir = {
                async abrir(invId, medId, suId, medicamento) {
                    console.log('🔄 Abriendo transferencia:', {
                        invId,
                        medId,
                        suId,
                        medicamento
                    });

                    document.getElementById('modalTransferirMedicamento').textContent = medicamento;
                    document.getElementById('modalTransferirInvId').value = invId;
                    document.getElementById('modalTransferirMedId').value = medId;
                    document.getElementById('modalTransferirSuOrigenId').value = suId;

                    document.getElementById('transferirSucursalDestino').value = '';
                    document.getElementById('transferirCantidad').value = '';
                    document.getElementById('transferirMotivo').value = '';
                    document.getElementById('transferirStockDisponible').textContent = '';

                    utils.abrir('modalTransferirInventario');

                    try {
                        const data = await utils.ajax({
                            inventarioAjax: 'lotes_transferibles',
                            med_id: medId,
                            su_id: suId
                        });

                        const selectLote = document.getElementById('transferirLote');
                        selectLote.innerHTML = '<option value="">Seleccione lote...</option>';

                        if (data.lotes && data.lotes.length > 0) {
                            data.lotes.forEach(lote => {
                                selectLote.innerHTML += `<option value="${lote.lm_id}" data-stock="${lote.stock}">${lote.numero_lote} (${utils.formatearNumero(lote.stock)} unid.)</option>`;
                            });
                        } else {
                            selectLote.innerHTML = '<option value="">Sin lotes disponibles</option>';
                        }

                    } catch (error) {
                        console.error('❌ Error:', error);
                        Swal.fire('Error', 'No se pudieron cargar los lotes', 'error');
                    }
                },

                procesar() {
                    Swal.fire({
                        title: 'Funcionalidad en desarrollo',
                        text: 'La transferencia se implementará en la siguiente fase',
                        icon: 'info'
                    });
                }
            };

            // ==================== MODAL HISTORIAL ====================
            const historial = {
                async abrir(medId, suId, medicamento) {
                    console.log('📜 Abriendo historial:', {
                        medId,
                        suId,
                        medicamento
                    });

                    document.getElementById('modalHistorialMedicamento').textContent = medicamento;
                    document.getElementById('modalHistorialMedId').value = medId;
                    document.getElementById('modalHistorialSuId').value = suId;

                    utils.abrir('modalHistorialInventario');

                    document.getElementById('tablaHistorialMovimientos').innerHTML =
                        '<tr><td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                    try {
                        const data = await utils.ajax({
                            inventarioAjax: 'historial',
                            med_id: medId,
                            su_id: suId
                        });

                        const tbody = document.getElementById('tablaHistorialMovimientos');

                        if (data.movimientos && data.movimientos.length > 0) {
                            tbody.innerHTML = data.movimientos.map(mov => {
                                const colorTipo = mov.tipo === 'entrada' ? '#e8f5e9' : '#ffebee';
                                const iconTipo = mov.tipo === 'entrada' ? 'arrow-down-circle-outline' : 'arrow-up-circle-outline';

                                return `
                            <tr>
                                <td>${mov.fecha}</td>
                                <td>
                                    <span style="background:${colorTipo}; padding:4px 8px; border-radius:4px; display:inline-flex; align-items:center; gap:4px;">
                                        <ion-icon name="${iconTipo}"></ion-icon>
                                        ${mov.tipo.toUpperCase()}
                                    </span>
                                </td>
                                <td>${utils.formatearNumero(mov.cantidad)} ${mov.unidad}</td>
                                <td>${mov.lote || 'N/A'}</td>
                                <td>${mov.usuario || 'Sistema'}</td>
                                <td>${mov.motivo || '-'}</td>
                            </tr>
                        `;
                            }).join('');
                        } else {
                            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin movimientos</td></tr>';
                        }

                    } catch (error) {
                        console.error('❌ Error:', error);
                        Swal.fire('Error', 'No se pudo cargar el historial', 'error');
                    }
                }
            };

            // ==================== LISTENER PARA ACTUALIZAR STOCK ==================== 
            document.addEventListener('DOMContentLoaded', function() {
                const selectLote = document.getElementById('transferirLote');
                if (selectLote) {
                    selectLote.addEventListener('change', function() {
                        const option = this.options[this.selectedIndex];
                        const stock = option.getAttribute('data-stock');
                        const infoElement = document.getElementById('transferirStockDisponible');

                        if (stock && stock > 0) {
                            infoElement.textContent = `Stock disponible: ${utils.formatearNumero(stock)} unidades`;
                            infoElement.style.color = '#4caf50';
                        } else {
                            infoElement.textContent = '';
                        }
                    });
                }
            });

            // ==================== API PÚBLICA ====================
            return {
                cerrar: utils.cerrar,
                verDetalle: detalle.abrir,
                abrirTransferencia: transferir.abrir,
                procesarTransferencia: transferir.procesar,
                verHistorial: historial.abrir
            };
        })();

        // ==================== EXPORTAR EXCEL (CSV) ====================
        document.addEventListener('DOMContentLoaded', function() {
            const btnExcel = document.getElementById('btnExportarExcel');

            if (btnExcel) {
                btnExcel.addEventListener('click', function() {
                    const sucursalSelect = document.querySelector('select[name="select3"]');
                    const sucursalId = sucursalSelect ? sucursalSelect.value : '';

                    const url = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php?inventarioAjax=exportar_excel' +
                        (sucursalId ? '&su_id=' + sucursalId : '');

                    console.log('📥 Descargando archivo:', url);

                    // Abrir en nueva ventana para forzar descarga
                    window.open(url, '_blank');

                    Swal.fire({
                        icon: 'success',
                        title: 'Descargando',
                        text: 'El archivo se está descargando...',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });

        // ==================== CERRAR AL HACER CLIC FUERA ====================
        document.addEventListener('click', function(e) {
            const modales = ['modalDetalleInventario', 'modalTransferirInventario', 'modalHistorialInventario'];

            modales.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && modal.style.display === 'flex' && e.target === modal) {
                    InventarioModals.cerrar(modalId);
                }
            });
        });
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2>⛔ Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>