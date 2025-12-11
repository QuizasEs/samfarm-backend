<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/proveedoresAjax.php"
        data-ajax-param="proveedoresAjax"
        data-ajax-registros="10">
        <div class="title">
            <h2>
                <ion-icon name="people-outline"></ion-icon> Gestión de Proveedores
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">
                    <small>Estado</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Con Compras</small>
                    <select class="select-filtro" name="select2">
                        <option value="">Todos</option>
                        <option value="con_compras">Con compras</option>
                        <option value="sin_compras">Sin compras</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Última Compra</small>
                    <select class="select-filtro" name="select3">
                        <option value="">Todos</option>
                        <option value="7">Últimos 7 días</option>
                        <option value="30">Últimos 30 días</option>
                        <option value="90">Últimos 90 días</option>
                        <option value="mas_90">Más de 90 días</option>
                        <option value="nunca">Nunca</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Desde</small>
                    <input type="date" name="fecha_desde" placeholder="Desde" title="Fecha desde">
                </div>

                <div class="form-fechas">
                    <small>Hasta</small>
                    <input type="date" name="fecha_hasta" placeholder="Hasta" title="Fecha hasta">
                </div>

                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por nombre, NIT o teléfono...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>

            </div>
            <div class="filtro-dinamico-buttons">

                <button type="button" class="btn success" onclick="ProveedoresModals.abrirRegistro()">
                    <ion-icon name="person-add-outline"></ion-icon> Nuevo
                </button>

                <button type="button" class="btn success" id="btnExportarExcelProveedor">
                    <ion-icon name="download-outline"></ion-icon> Excel
                </button>
            </div>
        </form>

        <div class="tabla-contenedor"></div>
    </div>
    <!-- modal para detalles -->
    <div class="modal" id="modalDetalleProveedor" style="display: none;">
        <div class="modal-content" style="max-width: 1000px;">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="person-circle-outline"></ion-icon>
                    Detalle de Proveedor - <span id="modalDetalleProveedorNombre">...</span>
                </div>
                <a class="close" onclick="ProveedoresModals.cerrar('modalDetalleProveedor')">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="modalDetallePrId">

            <div class="modal-group">
                <div class="row">
                    <h3><ion-icon name="information-circle-outline"></ion-icon> Información del Proveedor</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Nombre/Razón Social:</label>
                        <p id="detalleNombreCompleto">-</p>
                    </div>
                    <div class="col">
                        <label>NIT:</label>
                        <p id="detalleNit">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Teléfono:</label>
                        <p id="detalleTelefono">-</p>
                    </div>
                    <div class="col">
                        <label>Estado:</label>
                        <p id="detalleEstado">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Dirección:</label>
                        <p id="detalleDireccion">-</p>
                    </div>
                    <div class="col">
                        <label>Fecha de Registro:</label>
                        <p id="detalleFechaRegistro">-</p>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="stats-chart-outline"></ion-icon> Estadísticas de Compra</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Total de Compras:</label>
                        <p id="detalleTotalCompras">0</p>
                    </div>
                    <div class="col">
                        <label>Monto Total Comprado:</label>
                        <p id="detalleMontoTotal">Bs. 0.00</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Lotes Generados:</label>
                        <p id="detalleTotalLotes">0</p>
                    </div>
                    <div class="col">
                        <label>Promedio por Compra:</label>
                        <p id="detallePromedio">Bs. 0.00</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Última Compra:</label>
                        <p id="detalleUltimaCompra">-</p>
                    </div>
                    <div class="col">
                        <label>Antigüedad:</label>
                        <p id="detalleAntiguedad">-</p>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="receipt-outline"></ion-icon> Últimas 5 Compras</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N° Compra</th>
                                    <th>Fecha</th>
                                    <th>Laboratorio</th>
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

                <div class="row">
                    <h3><ion-icon name="medkit-outline"></ion-icon> Top 5 Medicamentos Suministrados</h3>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Medicamento</th>
                                    <th>Veces Comprado</th>
                                    <th>Laboratorio</th>
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

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn warning" onclick="ProveedoresModals.cerrar('modalDetalleProveedor')">
                        Cerrar
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- modal para registro y editar -->
    <form class="FormularioAjax" action="<?php echo SERVER_URL ?>ajax/proveedoresAjax.php" method="POST" autocomplete="off" data-form="save" id="formRegistroProveedor">
        <input type="hidden" name="proveedoresAjax" value="registrar">

        <div class="modal" id="modalRegistroProveedor" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <ion-icon name="person-add-outline"></ion-icon> Nuevo Proveedor
                    </div>
                    <a class="close" onclick="ProveedoresModals.cerrarRegistro()">
                        <ion-icon name="close-outline"></ion-icon>
                    </a>
                </div>

                <div class="modal-group">
                    <div class="row">
                        <div class="col">

                            <div class="modal-bloque">
                                <label class="required">Nombres / Razón Social</label>
                                <input type="text" name="Nombres_pr_up" id="edicionNombres" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,120}" maxlength="120" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Apellido Paterno</label>
                                <input type="text" name="Paterno_pr_up" id="edicionPaterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,80}" maxlength="80">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Apellido Materno</label>
                                <input type="text" name="Materno_pr_up" id="edicionMaterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,80}" maxlength="80">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">NIT</label>
                                <input type="text" name="Nit_pr_up" id="edicionNit" pattern="[0-9]{6,50}" maxlength="50" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Teléfono</label>
                                <input type="text" name="Telefono_pr_up" id="edicionTelefono" pattern="[0-9]{6,30}" maxlength="30">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Dirección</label>
                                <input type="text" name="Direccion_pr_up" id="edicionDireccion" maxlength="250" rows="3"></input>
                            </div>
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="ProveedoresModals.cerrarEdicion()">Cancelar</a>
                        <button type="submit" class="btn success">
                            <ion-icon name="checkmark-outline"></ion-icon> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form class="FormularioAjax" action="<?php echo SERVER_URL ?>ajax/proveedoresAjax.php" method="POST" autocomplete="off" data-form="update" id="formEdicionProveedor">
        <input type="hidden" name="proveedoresAjax" value="actualizar">
        <input type="hidden" name="PrId_up" id="edicionPrId">

        <div class="modal" id="modalEdicionProveedor" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title">
                        <ion-icon name="create-outline"></ion-icon> Editar Proveedor
                    </div>
                    <a class="close" onclick="ProveedoresModals.cerrarEdicion()">
                        <ion-icon name="close-outline"></ion-icon>
                    </a>
                </div>

                <div class="modal-group">
                    <div class="row">
                        <div class="col">

                            <div class="modal-bloque">
                                <label class="required">Nombres / Razón Social</label>
                                <input type="text" name="Nombres_pr_up" id="edicionNombres" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,120}" maxlength="120" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Apellido Paterno</label>
                                <input type="text" name="Paterno_pr_up" id="edicionPaterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,80}" maxlength="80">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Apellido Materno</label>
                                <input type="text" name="Materno_pr_up" id="edicionMaterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,80}" maxlength="80">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">NIT</label>
                                <input type="text" name="Nit_pr_up" id="edicionNit" pattern="[0-9]{6,50}" maxlength="50" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Teléfono</label>
                                <input type="text" name="Telefono_pr_up" id="edicionTelefono" pattern="[0-9]{6,30}" maxlength="30">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Dirección</label>
                                <input type="text" name="Direccion_pr_up" id="edicionDireccion" maxlength="250" rows="3"></input>
                            </div>
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="ProveedoresModals.cerrarEdicion()">Cancelar</a>
                        <button type="submit" class="btn success">
                            <ion-icon name="checkmark-outline"></ion-icon> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- generar excel para el modulo proveedor -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnExcelProveedor = document.getElementById('btnExportarExcelProveedor');

            if (btnExcelProveedor) {
                btnExcelProveedor.addEventListener('click', function(e) {
                    e.preventDefault();

                    const form = this.closest('.container').querySelector('.filtro-dinamico');
                    if (!form) {
                        console.warn('No se encontró el formulario de filtros');
                        return;
                    }

                    const busqueda = form.querySelector('input[name="busqueda"]');
                    const select1 = form.querySelector('select[name="select1"]');
                    const select2 = form.querySelector('select[name="select2"]');
                    const select3 = form.querySelector('select[name="select3"]');
                    const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                    const fechaHasta = form.querySelector('input[name="fecha_hasta"]');

                    let url = '<?php echo SERVER_URL; ?>ajax/proveedoresAjax.php?exportar=excel';

                    if (busqueda && busqueda.value.trim()) {
                        url += '&busqueda=' + encodeURIComponent(busqueda.value.trim());
                    }

                    if (select1 && select1.value) {
                        url += '&select1=' + encodeURIComponent(select1.value);
                    }

                    if (select2 && select2.value) {
                        url += '&select2=' + encodeURIComponent(select2.value);
                    }

                    if (select3 && select3.value) {
                        url += '&select3=' + encodeURIComponent(select3.value);
                    }

                    if (fechaDesde && fechaDesde.value) {
                        url += '&fecha_desde=' + encodeURIComponent(fechaDesde.value);
                    }

                    if (fechaHasta && fechaHasta.value) {
                        url += '&fecha_hasta=' + encodeURIComponent(fechaHasta.value);
                    }

                    console.log('📥 Descargando Excel de proveedores:', url);

                    window.open(url, '_blank');

                    Swal.fire({
                        icon: 'success',
                        title: 'Descargando',
                        text: 'El archivo Excel se está descargando...',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });
    </script>
    <!-- escript para modal y funcionamientos del mismo del modulo de proveedor -->
    <script>
        const ProveedoresModals = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/proveedoresAjax.php';

            const utils = {
                async ajax(params) {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(params)
                    });
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return await response.json();
                },

                abrir(modalId) {
                    const modal = document.getElementById(modalId);
                    if (modal) modal.style.display = 'flex';
                },

                cerrar(modalId) {
                    const modal = document.getElementById(modalId);
                    if (modal) modal.style.display = 'none';
                },

                formatearFecha(fecha) {
                    const d = new Date(fecha);
                    return `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()}`;
                },

                formatearNumero(num) {
                    return parseInt(num || 0).toLocaleString('es-BO');
                },

                formatearMoneda(num) {
                    return 'Bs ' + parseFloat(num || 0).toFixed(2);
                }
            };

            const detalle = {
                async abrir(prId, nombre) {
                    document.getElementById('modalDetalleProveedorNombre').textContent = nombre;
                    document.getElementById('modalDetallePrId').value = prId;
                    utils.abrir('modalDetalleProveedor');

                    document.getElementById('tablaUltimasCompras').innerHTML =
                        '<tr><td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                    document.getElementById('tablaTopMedicamentos').innerHTML =
                        '<tr><td colspan="4" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                    const data = await utils.ajax({
                        proveedoresAjax: 'detalle',
                        pr_id: prId
                    });

                    document.getElementById('detalleNombreCompleto').textContent = data.nombre_completo || '-';
                    document.getElementById('detalleNit').textContent = data.nit || '-';
                    document.getElementById('detalleTelefono').textContent = data.telefono || '-';
                    document.getElementById('detalleDireccion').textContent = data.direccion || '-';
                    document.getElementById('detalleFechaRegistro').textContent = data.fecha_registro;
                    document.getElementById('detalleEstado').textContent = data.estado;

                    document.getElementById('detalleTotalCompras').textContent = utils.formatearNumero(data.total_compras);
                    document.getElementById('detalleMontoTotal').textContent = utils.formatearMoneda(data.monto_total);
                    document.getElementById('detalleTotalLotes').textContent = utils.formatearNumero(data.total_lotes);
                    document.getElementById('detallePromedio').textContent = utils.formatearMoneda(data.promedio);
                    document.getElementById('detalleUltimaCompra').textContent = data.ultima_compra;
                    document.getElementById('detalleAntiguedad').textContent = data.antiguedad + ' días';

                    const compras = data.ultimas_compras ?? [];
                    const top = data.top_medicamentos ?? [];

                    document.getElementById('tablaUltimasCompras').innerHTML =
                        compras.length ?
                        compras.map(c => `
                            <tr>
                                <td>${c.co_numero}</td>
                                <td>${utils.formatearFecha(c.co_fecha)}</td>
                                <td>${c.laboratorio || '-'}</td>
                                <td>${utils.formatearMoneda(c.co_total)}</td>
                                <td>${utils.formatearNumero(c.total_items)}</td>
                                <td>${c.co_numero_factura || '-'}</td>
                            </tr>
                        `).join('') :
                        '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin compras</td></tr>';

                    document.getElementById('tablaTopMedicamentos').innerHTML =
                        top.length ?
                        top.map(m => `
                            <tr>
                                <td>${m.med_nombre_quimico}</td>
                                <td>${utils.formatearNumero(m.veces_comprado)}</td>
                                <td>${m.laboratorio || '-'}</td>
                                <td>${utils.formatearFecha(m.ultima_compra)}</td>
                            </tr>
                        `).join('') :
                        '<tr><td colspan="4" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin medicamentos</td></tr>';
                }
            };

            const registro = {
                abrir() {
                    utils.abrir('modalRegistroProveedor');
                    const form = document.getElementById('formRegistroProveedor');
                    if (form) form.reset();
                },
                cerrar() {
                    utils.cerrar('modalRegistroProveedor');
                }
            };

            const edicion = {
                async abrir(prId) {
                    utils.abrir('modalEdicionProveedor');

                    const data = await utils.ajax({
                        proveedoresAjax: 'obtener',
                        pr_id: prId
                    });

                    document.getElementById('edicionPrId').value = data.pr_id;
                    document.getElementById('edicionNombres').value = data.pr_nombres || '';
                    document.getElementById('edicionPaterno').value = data.pr_apellido_paterno || '';
                    document.getElementById('edicionMaterno').value = data.pr_apellido_materno || '';
                    document.getElementById('edicionNit').value = data.pr_nit || '';
                    document.getElementById('edicionTelefono').value = data.pr_telefono || '';
                    document.getElementById('edicionDireccion').value = data.pr_direccion || '';
                },
                cerrar() {
                    utils.cerrar('modalEdicionProveedor');
                }
            };

            document.addEventListener('click', function(e) {
                const m1 = document.getElementById('modalDetalleProveedor');
                const m2 = document.getElementById('modalRegistroProveedor');
                const m3 = document.getElementById('modalEdicionProveedor');

                if (m1 && m1.style.display === 'flex' && e.target === m1) utils.cerrar('modalDetalleProveedor');
                if (m2 && m2.style.display === 'flex' && e.target === m2) utils.cerrar('modalRegistroProveedor');
                if (m3 && m3.style.display === 'flex' && e.target === m3) utils.cerrar('modalEdicionProveedor');
            });

            return {
                cerrar: utils.cerrar,
                verDetalle: detalle.abrir,
                abrirRegistro: registro.abrir,
                cerrarRegistro: registro.cerrar,
                abrirEdicion: edicion.abrir,
                cerrarEdicion: edicion.cerrar
            };
        })();
    </script>




<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>