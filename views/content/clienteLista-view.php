<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/clientesAjax.php"
        data-ajax-param="clientesAjax"
        data-ajax-registros="10">
        <div class="title">
            <h3>
                <ion-icon name="people-outline"></ion-icon> Gestión de Clientes
            </h3>
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
                    <input type="text" name="busqueda" placeholder="Buscar por nombre, CI, teléfono...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>

            </div>

            <button type="button" class="btn success" onclick="ClientesModals.abrirModalNuevo()">
                <ion-icon name="person-add-outline"></ion-icon> Nuevo Cliente
            </button>

            <button type="button" class="btn primary" id="btnExportarExcelClientes">
                <ion-icon name="download-outline"></ion-icon> Exportar Excel
            </button>
        </form>

        <div class="tabla-contenedor"></div>
    </div>
    <!-- modal -->
    <div class="modal" id="modalEditarCliente" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title"><ion-icon name="create-outline"></ion-icon> Editar Cliente</div>
                <a class="close" onclick="ClientesModals.cerrarModalEditar()"><ion-icon name="close-outline"></ion-icon></a>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/clientesAjax.php" method="POST" data-form="update" autocomplete="off">
                <input type="hidden" name="clientesAjax" value="editar">
                <input type="hidden" name="cl_id_editar" id="cl_id_editar">

                <div class="modal-group">
                    <div class="row">
                        <label class="required">Nombres</label>
                        <input type="text" name="Nombres_cl_edit" id="Nombres_cl_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Apellido Paterno</label>
                                <input type="text" name="Paterno_cl_edit" id="Paterno_cl_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Apellido Materno</label>
                                <input type="text" name="Materno_cl_edit" id="Materno_cl_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Teléfono</label>
                                <input type="number" name="Telefono_cl_edit" id="Telefono_cl_edit" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Correo</label>
                                <input type="email" name="Correo_cl_edit" id="Correo_cl_edit">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Dirección</label>
                                <input type="text" name="Direccion_cl_edit" id="Direccion_cl_edit">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Carnet</label>
                                <input type="text" name="Carnet_cl_edit" id="Carnet_cl_edit" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>
                    </div>

                    <div class="btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="ClientesModals.cerrarModalEditar()">Cancelar</a>
                        <button type="submit" class="btn success">Guardar Cambios</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="modalNuevoCliente" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title"><ion-icon name="person-add-outline"></ion-icon> Nuevo Cliente</div>
                <a class="close" onclick="ClientesModals.cerrarModalNuevo()"><ion-icon name="close-outline"></ion-icon></a>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/clientesAjax.php" method="POST" data-form="save" autocomplete="off">
                <input type="hidden" name="clientesAjax" value="nuevo">

                <div class="modal-group">
                    <div class="row">
                        <label class="required">Nombres</label>
                        <input type="text" name="Nombres_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
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
                                <label>Teléfono</label>
                                <input type="number" name="Telefono_cl" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Correo</label>
                                <input type="email" name="Correo_cl">
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
                                <label>Carnet</label>
                                <input type="text" name="Carnet_cl" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>
                    </div>

                    <div class="btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="ClientesModals.cerrarModalNuevo()">Cancelar</a>
                        <button type="submit" class="btn success">Registrar Cliente</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="modal" id="modalDetalleCliente" style="display: none;">
        <div class="modal-content" style="max-width: 1200px;">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="person-circle-outline"></ion-icon>
                    Detalle del Cliente - <span id="detalleClienteNombre">...</span>
                </div>
                <a class="close" onclick="ClientesModals.cerrarModalDetalle()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="detalleClienteId">

            <div class="modal-group">
                <div class="row">
                    <h3><ion-icon name="information-circle-outline"></ion-icon> Información Personal</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Nombre Completo:</label>
                        <p id="detalleNombreCompleto">-</p>
                    </div>
                    <div class="col">
                        <label>CI/Carnet:</label>
                        <p id="detalleCarnet">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Teléfono:</label>
                        <p id="detalleTelefono">-</p>
                    </div>
                    <div class="col">
                        <label>Correo Electrónico:</label>
                        <p id="detalleCorreo">-</p>
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
                    <div class="col">
                        <label>Estado:</label>
                        <p id="detalleEstado">-</p>
                    </div>
                    <div class="col">
                        <label>Antigüedad como Cliente:</label>
                        <p id="detalleAntiguedad">-</p>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="stats-chart-outline"></ion-icon> Estadísticas de Compra</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <div style="background:#e8f5e9;padding:15px;border-radius:8px;text-align:center;">
                            <label style="color:#2e7d32;">Total Compras:</label>
                            <p style="font-size:24px;font-weight:bold;color:#1b5e20;margin:5px 0;" id="detalleTotalCompras">0</p>
                        </div>
                    </div>
                    <div class="col">
                        <div style="background:#e3f2fd;padding:15px;border-radius:8px;text-align:center;">
                            <label style="color:#1565c0;">Monto Total Gastado:</label>
                            <p style="font-size:24px;font-weight:bold;color:#0d47a1;margin:5px 0;" id="detalleMontoTotal">Bs. 0.00</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div style="background:#fff3e0;padding:15px;border-radius:8px;text-align:center;">
                            <label style="color:#e65100;">Facturas Emitidas:</label>
                            <p style="font-size:24px;font-weight:bold;color:#bf360c;margin:5px 0;" id="detalleFacturasEmitidas">0</p>
                        </div>
                    </div>
                    <div class="col">
                        <div style="background:#f3e5f5;padding:15px;border-radius:8px;text-align:center;">
                            <label style="color:#7b1fa2;">Promedio por Compra:</label>
                            <p style="font-size:24px;font-weight:bold;color:#4a148c;margin:5px 0;" id="detallePromedio">Bs. 0.00</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div style="background:#fce4ec;padding:15px;border-radius:8px;text-align:center;">
                            <label style="color:#c2185b;">Última Compra:</label>
                            <p style="font-size:18px;font-weight:bold;color:#880e4f;margin:5px 0;" id="detalleUltimaCompra">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="bar-chart-outline"></ion-icon> Gráfico de Compras Mensuales</h3>
                </div>

                <div class="row">
                    <div id="graficoComprasMensuales" style="width:100%;height:350px;"></div>
                </div>

                <div class="row">
                    <h3><ion-icon name="receipt-outline"></ion-icon> Últimas 5 Compras</h3>
                </div>

                <div class="row">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Items</th>
                                    <th>Tipo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUltimasCompras">
                                <tr>
                                    <td colspan="6" style="text-align:center;">
                                        <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <h3><ion-icon name="medical-outline"></ion-icon> Medicamentos Más Comprados (Top 5)</h3>
                </div>

                <div class="row">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Medicamento</th>
                                    <th>Veces Comprado</th>
                                    <th>Última Compra</th>
                                </tr>
                            </thead>
                            <tbody id="tablaMedicamentosMasComprados">
                                <tr>
                                    <td colspan="4" style="text-align:center;">
                                        <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="btn-content">
                    <a href="javascript:void(0)" class="btn primary" onclick="ClientesModals.editarDesdeDetalle()">
                        <ion-icon name="create-outline"></ion-icon> Editar Cliente
                    </a>
                    <a href="javascript:void(0)" class="btn success" id="btnVerHistorialCompleto">
                        <ion-icon name="time-outline"></ion-icon> Ver Historial Completo
                    </a>
                    <a href="javascript:void(0)" class="btn warning" id="btnToggleEstadoDetalle">
                        <ion-icon name="power-outline"></ion-icon> Cambiar Estado
                    </a>
                    <a href="javascript:void(0)" class="btn default" onclick="ClientesModals.cerrarModalDetalle()">
                        Cerrar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- modal clientes edicion detalles -->
    <script>
        const ClientesModals = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/clientesAjax.php';

            function abrirModalNuevo() {
                const modal = document.getElementById('modalNuevoCliente');
                if (modal) modal.style.display = 'flex';
            }

            function cerrarModalNuevo() {
                const modal = document.getElementById('modalNuevoCliente');
                if (modal) {
                    modal.style.display = 'none';
                    const form = modal.querySelector('form');
                    if (form) form.reset();
                }
            }

            async function abrirModalEditar(cl_id) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            clientesAjax: 'datos_cliente',
                            cl_id: cl_id
                        })
                    });

                    const data = await response.json();
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    document.getElementById('cl_id_editar').value = data.cl_id;
                    document.getElementById('Nombres_cl_edit').value = data.cl_nombres || '';
                    document.getElementById('Paterno_cl_edit').value = data.cl_apellido_paterno || '';
                    document.getElementById('Materno_cl_edit').value = data.cl_apellido_materno || '';
                    document.getElementById('Telefono_cl_edit').value = data.cl_telefono || '';
                    document.getElementById('Correo_cl_edit').value = data.cl_correo || '';
                    document.getElementById('Direccion_cl_edit').value = data.cl_direccion || '';
                    document.getElementById('Carnet_cl_edit').value = data.cl_carnet || '';

                    const modal = document.getElementById('modalEditarCliente');
                    if (modal) modal.style.display = 'flex';

                } catch (error) {
                    Swal.fire('Error', 'No se pudo cargar los datos del cliente', 'error');
                }
            }

            function cerrarModalEditar() {
                const modal = document.getElementById('modalEditarCliente');
                if (modal) modal.style.display = 'none';
            }

            async function toggleEstado(cl_id, estado) {
                const texto = estado == 1 ? 'desactivar' : 'activar';

                const result = await Swal.fire({
                    title: '¿Está seguro?',
                    text: '¿Desea ' + texto + ' este cliente?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'Cancelar'
                });

                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            clientesAjax: 'toggle_estado',
                            cl_id: cl_id,
                            estado: estado
                        })
                    });

                    const data = await response.json();
                    Swal.close();

                    await Swal.fire({
                        title: data.Titulo || 'Resultado',
                        html: data.texto || '',
                        icon: data.Tipo || 'info'
                    });

                    if (data.Alerta === 'recargar' || data.Tipo === 'success') {
                        document.querySelector('.filtro-dinamico .btn-search')?.click();
                    }

                } catch (error) {
                    Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
                }
            }

            async function verDetalle(cl_id) {
                document.getElementById('detalleClienteId').value = cl_id;
                const modal = document.getElementById('modalDetalleCliente');
                if (modal) modal.style.display = 'flex';

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            clientesAjax: 'detalle_completo',
                            cl_id: cl_id
                        })
                    });

                    const data = await response.json();
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    const nombreCompleto = `${data.cl_nombres || ''} ${data.cl_apellido_paterno || ''} ${data.cl_apellido_materno || ''}`.trim();
                    document.getElementById('detalleClienteNombre').textContent = nombreCompleto;
                    document.getElementById('detalleNombreCompleto').textContent = nombreCompleto;
                    document.getElementById('detalleCarnet').textContent = data.cl_carnet || 'Sin CI';
                    document.getElementById('detalleTelefono').textContent = data.cl_telefono || '-';
                    document.getElementById('detalleCorreo').textContent = data.cl_correo || '-';
                    document.getElementById('detalleDireccion').textContent = data.cl_direccion || '-';
                    document.getElementById('detalleFechaRegistro').textContent = formatearFecha(data.cl_creado_en);
                    document.getElementById('detalleAntiguedad').textContent = data.antiguedad_dias;

                    const estadoHtml = data.cl_estado == 1 ?
                        '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>' :
                        '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';

                    document.getElementById('detalleEstado').innerHTML = estadoHtml;

                    document.getElementById('detalleTotalCompras').textContent = data.total_compras || 0;
                    document.getElementById('detalleMontoTotal').textContent = 'Bs. ' + formatearNumero(data.monto_total || 0);
                    document.getElementById('detalleFacturasEmitidas').textContent = data.facturas_emitidas || 0;
                    document.getElementById('detallePromedio').textContent = 'Bs. ' + formatearNumero(data.promedio_compra || 0);
                    document.getElementById('detalleUltimaCompra').textContent = data.ultima_compra ? formatearFecha(data.ultima_compra) : 'Nunca';

                    const btnToggle = document.getElementById('btnToggleEstadoDetalle');
                    if (btnToggle) btnToggle.onclick = function() {
                        toggleEstado(cl_id, data.cl_estado);
                    };

                    cargarUltimasCompras(cl_id);
                    cargarMedicamentosMasComprados(cl_id);
                    cargarGraficoComprasMensuales(cl_id);

                } catch (error) {
                    Swal.fire('Error', 'No se pudo cargar el detalle del cliente', 'error');
                }
            }

            async function cargarUltimasCompras(cl_id) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            clientesAjax: 'ultimas_compras',
                            cl_id: cl_id
                        })
                    });

                    const data = await response.json();
                    const tbody = document.getElementById('tablaUltimasCompras');

                    if (data.compras && data.compras.length > 0) {
                        tbody.innerHTML = data.compras.map(compra => `
                        <tr>
                            <td>${compra.ve_numero_documento}</td>
                            <td>${formatearFecha(compra.ve_fecha_emision)}</td>
                            <td>Bs. ${formatearNumero(compra.ve_total)}</td>
                            <td>${compra.total_items}</td>
                            <td>${compra.ve_tipo_documento || 'nota de venta'}</td>
                            <td>
                                <a href="<?php echo SERVER_URL; ?>ventaDetalle/${compra.ve_id}/" class="btn default">
                                    <ion-icon name="eye-outline"></ion-icon> Ver
                                </a>
                            </td>
                        </tr>
                    `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="cart-outline"></ion-icon> Sin compras registradas</td></tr>';
                    }

                } catch (error) {}
            }

            async function cargarMedicamentosMasComprados(cl_id) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            clientesAjax: 'medicamentos_mas_comprados',
                            cl_id: cl_id
                        })
                    });

                    const data = await response.json();
                    const tbody = document.getElementById('tablaMedicamentosMasComprados');

                    if (data.medicamentos && data.medicamentos.length > 0) {
                        tbody.innerHTML = data.medicamentos.map((med, index) => `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${med.med_nombre_quimico}</td>
                            <td>${med.veces_comprado}</td>
                            <td>${formatearFecha(med.ultima_compra)}</td>
                        </tr>
                    `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;"><ion-icon name="medical-outline"></ion-icon> Sin medicamentos comprados</td></tr>';
                    }

                } catch (error) {}
            }

            async function cargarGraficoComprasMensuales(cl_id) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            clientesAjax: 'grafico_compras_mensuales',
                            cl_id: cl_id
                        })
                    });

                    const data = await response.json();

                    if (data.datos && data.datos.length > 0) {

                        const meses = data.datos.map(d => {
                            const [year, month] = d.mes.split('-');
                            const fecha = new Date(year, month - 1);
                            return fecha.toLocaleDateString('es-ES', {
                                month: 'short',
                                year: 'numeric'
                            });
                        });

                        const compras = data.datos.map(d => parseInt(d.total_compras));
                        const montos = data.datos.map(d => parseFloat(d.monto_total));

                        const myChart = echarts.init(document.getElementById('graficoComprasMensuales'));

                        myChart.setOption({
                            title: {
                                text: 'Compras de los Últimos 12 Meses',
                                left: 'center'
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: ['Cantidad de Compras', 'Monto Total (Bs.)'],
                                bottom: 0
                            },
                            grid: {
                                left: '3%',
                                right: '4%',
                                bottom: '15%',
                                containLabel: true
                            },
                            xAxis: {
                                type: 'category',
                                data: meses,
                                axisLabel: {
                                    rotate: 45
                                }
                            },
                            yAxis: [{
                                    type: 'value',
                                    name: 'Compras'
                                },
                                {
                                    type: 'value',
                                    name: 'Monto (Bs.)',
                                    position: 'right'
                                }
                            ],
                            series: [{
                                    name: 'Cantidad de Compras',
                                    type: 'bar',
                                    data: compras,
                                    itemStyle: {
                                        color: '#4caf50'
                                    }
                                },
                                {
                                    name: 'Monto Total (Bs.)',
                                    type: 'line',
                                    yAxisIndex: 1,
                                    data: montos,
                                    itemStyle: {
                                        color: '#2196f3'
                                    }
                                }
                            ]
                        });

                    } else {
                        document.getElementById('graficoComprasMensuales').innerHTML =
                            '<div style="text-align:center;padding:50px;color:#999;">Sin datos para mostrar</div>';
                    }

                } catch (error) {}
            }

            function cerrarModalDetalle() {
                const modal = document.getElementById('modalDetalleCliente');
                if (modal) modal.style.display = 'none';
            }

            function editarDesdeDetalle() {
                const cl_id = document.getElementById('detalleClienteId').value;
                cerrarModalDetalle();
                abrirModalEditar(cl_id);
            }

            function formatearFecha(fecha) {
                if (!fecha) return '-';
                const d = new Date(fecha);
                const dia = String(d.getDate()).padStart(2, '0');
                const mes = String(d.getMonth() + 1).padStart(2, '0');
                const anio = d.getFullYear();
                return `${dia}/${mes}/${anio}`;
            }

            function formatearNumero(num) {
                return parseFloat(num || 0).toFixed(2);
            }

            document.addEventListener('click', function(e) {
                const modals = ['modalNuevoCliente', 'modalEditarCliente', 'modalDetalleCliente'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && modal.style.display === 'flex' && e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            return {
                abrirModalNuevo,
                cerrarModalNuevo,
                abrirModalEditar,
                cerrarModalEditar,
                toggleEstado,
                verDetalle,
                cerrarModalDetalle,
                editarDesdeDetalle
            };

        })();
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>