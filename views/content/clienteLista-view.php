<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2 || $_SESSION['rol_smp'] == 3)) {
?>

    <div class="tabla-dinamica"
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
    <div class="mov" id="modalEditarCliente">
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
    <div class="mov" id="modalNuevoCliente">
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
    <div class="mov" id="modalDetalleCliente">
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
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Nombre Completo</div>
                                    <div class="th5" id="detalleNombreCompleto">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="card-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">CI / Carnet</div>
                                    <div class="th5" id="detalleCarnet">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="call-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Teléfono</div>
                                    <div class="th5" id="detalleTelefono">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="mail-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Correo Electrónico</div>
                                    <div class="th5" id="detalleCorreo">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Dirección</div>
                                    <div class="th5" id="detalleDireccion">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Fecha de Registro</div>
                                    <div class="th5" id="detalleFechaRegistro">-</div>
                                </div>
                            </div>
                            <div class="litem"><ion-icon name="time-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Antigüedad</div>
                                    <div class="th5"><span id="detalleAntiguedad">-</span> días</div>
                                </div>
                            </div>
                            <div class="litem" style="border:none"><ion-icon name="shield-checkmark-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon>
                                <div class="f1">
                                    <div class="tc">Estado</div>
                                    <div id="detalleEstado">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stit">Estadísticas de Compra</div>
                <div class="grid5">
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="cart-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleTotalCompras">0</div>
                            <div class="sl">Total Compras</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleMontoTotal">Bs. 0.00</div>
                            <div class="sl">Monto Total</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="document-text-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleFacturasEmitidas">0</div>
                            <div class="sl">Facturas</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="stats-chart-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detallePromedio">Bs. 0.00</div>
                            <div class="sl">Promedio</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="time-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="detalleUltimaCompra">-</div>
                            <div class="sl">Última Compra</div>
                        </div>
                    </div>
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
                                        <th>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaMedicamentosMasComprados">
                                    <tr>
                                        <td colspan="2" class="txctr tmut">Cargando...</td>
                                    </tr>
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
                                        <th>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaUltimasCompras">
                                    <tr>
                                        <td colspan="2" class="txctr tmut">Cargando...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="ClientesModals.cerrarModalDetalle()">Cerrar</button>
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
    <script>
        const ClientesModals = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/clientesAjax.php';

            function abrirModalNuevo() {
                const modal = document.getElementById('modalNuevoCliente');
                if (modal) {
                    modal.classList.add('open');
                }
            }

            function cerrarModalNuevo() {
                const modal = document.getElementById('modalNuevoCliente');
                if (modal) {
                    modal.classList.remove('open');
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
                    if (modal) modal.classList.add('open');

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cargar los datos del cliente', 'error');
                }
            }

            function cerrarModalEditar() {
                const modal = document.getElementById('modalEditarCliente');
                if (modal) {
                    modal.classList.remove('open');
                }
            }

            async function toggleEstado(cl_id, estado) {
                const texto = estado == 1 ? 'desactivar' : 'activar';

                const result = await Swal.fire({
                    title: '¿Está seguro?',
                    text: '¿Desea ' + texto + ' este cliente?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ' + texto,
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
                    console.error(error);
                    Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
                }
            }

            async function verDetalle(cl_id) {
                document.getElementById('detalleClienteId').value = cl_id;

                const modal = document.getElementById('modalDetalleCliente');
                if (modal) modal.classList.add('open');

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
                        '<span class="badge bgr"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>' :
                        '<span class="badge bgry"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';
                    document.getElementById('detalleEstado').innerHTML = estadoHtml;

                    document.getElementById('detalleTotalCompras').textContent = data.total_compras || 0;
                    document.getElementById('detalleMontoTotal').textContent = 'Bs. ' + formatearNumero(data.monto_total || 0);
                    document.getElementById('detalleFacturasEmitidas').textContent = data.facturas_emitidas || 0;
                    document.getElementById('detallePromedio').textContent = 'Bs. ' + formatearNumero(data.promedio_compra || 0);
                    document.getElementById('detalleUltimaCompra').textContent = data.ultima_compra ? formatearFecha(data.ultima_compra) : 'Nunca';

                    const btnToggle = document.getElementById('btnToggleEstadoDetalle');
                    if (btnToggle) {
                        btnToggle.onclick = function() {
                            toggleEstado(cl_id, data.cl_estado);
                        };
                    }

                    cargarUltimasCompras(cl_id);
                    cargarMedicamentosMasComprados(cl_id);
                    cargarGraficoComprasMensuales(cl_id);

                } catch (error) {
                    console.error('Error:', error);
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
                        tbody.innerHTML = data.compras.map(compra => {
                            const vendedor = compra.vendedor_nombre || 'N/A';
                            const medicamentos = compra.medicamentos_detalle ?
                                (compra.medicamentos_detalle.length > 80 ?
                                    compra.medicamentos_detalle.substring(0, 80) + '...' :
                                    compra.medicamentos_detalle) :
                                '-';

                            return `
                        <tr>
                            <td><strong>${compra.ve_numero_documento}</strong></td>
                            <td>
                                <div class="td-main">Fecha: ${formatearFecha(compra.ve_fecha_emision)} | Total: <strong style="color:#1976D2;">Bs. ${formatearNumero(compra.ve_total)}</strong></div>
                                <div class="td-sub" title="${compra.medicamentos_detalle || ''}">Medicamentos: ${medicamentos} | Unidades: ${compra.total_unidades || 0} | Vendedor: ${vendedor}</div>
                            </td>
                        </tr>
                    `
                        }).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;"><ion-icon name="cart-outline"></ion-icon> Sin compras registradas</td></tr>';
                    }

                } catch (error) {
                    console.error('Error:', error);
                }
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
                            <td>
                                <div class="td-main"><strong>${med.med_nombre_quimico}</strong></div>
                                <div class="td-sub">Proveedor: ${med.proveedor || '-'} | Forma: ${med.forma_farmaceutica || '-'} | Veces: ${med.veces_comprado} | Unidades: ${med.total_unidades} | Última: ${formatearFecha(med.ultima_compra)}</div>
                            </td>
                        </tr>
                    `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="2" style="text-align:center;"><ion-icon name="medical-outline"></ion-icon> Sin medicamentos comprados</td></tr>';
                    }

                } catch (error) {
                    console.error('Error:', error);
                }
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

                        const option = {
                            title: {
                                text: 'Compras de los Últimos 12 Meses',
                                left: 'center',
                                top: 0,
                                textStyle: {
                                    fontSize: 16
                                }
                            },
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                }
                            },
                            legend: {
                                data: ['Cantidad de Compras', 'Monto Total (Bs.)'],
                                top: 20,
                                textStyle: {
                                    fontSize: 12
                                }
                            },
                            grid: {
                                left: '12%',
                                right: '12%',
                                top: '20%',
                                bottom: '15%',
                                containLabel: true
                            },
                            xAxis: {
                                type: 'category',
                                data: meses,
                                axisLabel: {
                                    rotate: 45,
                                    fontSize: 11,
                                    margin: 15,
                                    interval: 0
                                }
                            },
                            yAxis: [{
                                    type: 'value',
                                    name: 'Compras',
                                    position: 'left',
                                    axisLabel: {
                                        formatter: '{value}',
                                        margin: 10
                                    }
                                },
                                {
                                    type: 'value',
                                    name: 'Monto (Bs.)',
                                    position: 'right',
                                    axisLabel: {
                                        formatter: 'Bs. {value}',
                                        margin: 10
                                    }
                                }
                            ],
                            series: [{
                                    name: 'Cantidad de Compras',
                                    type: 'bar',
                                    data: compras,
                                    itemStyle: {
                                        color: '#4caf50'
                                    },
                                    label: {
                                        show: false // Eliminado para evitar superposiciones
                                    },
                                    barWidth: '60%'
                                },
                                {
                                    name: 'Monto Total (Bs.)',
                                    type: 'line',
                                    yAxisIndex: 1,
                                    data: montos,
                                    itemStyle: {
                                        color: '#2196f3'
                                    },
                                    label: {
                                        show: false // Eliminado para evitar superposiciones
                                    },
                                    lineStyle: {
                                        width: 2
                                    },
                                    symbolSize: 6
                                }
                            ]
                        };

                        myChart.setOption(option);

                        // Ajuste automático después de renderizar
                        setTimeout(() => {
                            myChart.resize();
                        }, 100);

                        // Redimensionar con la ventana
                        window.addEventListener('resize', function() {
                            myChart.resize();
                        });

                    } else {
                        document.getElementById('graficoComprasMensuales').innerHTML = '<div style="text-align:center;padding:50px;color:#999;">Sin datos para mostrar</div>';
                    }

                } catch (error) {
                    console.error('Error:', error);
                }
            }

            function cerrarModalDetalle() {
                const modal = document.getElementById('modalDetalleCliente');
                if (modal) modal.classList.remove('open');
            }

            function editarDesdeDetalle() {
                const cl_id = document.getElementById('detalleClienteId').value;
                cerrarModalDetalle();
                abrirModalEditar(cl_id);
            }

            function exportarPDFDetalle(cl_id) {
                const url = '<?php echo SERVER_URL; ?>ajax/clientesAjax.php?clientesAjax=exportar_pdf_detalle&cl_id=' + cl_id;

                console.log('Exportando PDF detalle:', url);

                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Generando PDF',
                    text: 'El comprobante PDF se está generando...',
                    timer: 2000,
                    showConfirmButton: false
                });
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


            function verHistorialCompleto() {
                const cl_id = document.getElementById('detalleClienteId').value;
                const nombreCliente = document.getElementById('detalleClienteNombre').textContent;

                cerrarModalDetalle();

                Swal.fire({
                    title: 'Historial Completo',
                    html: `
                    <p style="margin-bottom:15px;">Seleccione cómo desea ver el historial de <strong>${nombreCliente}</strong>:</p>
                    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                        <button onclick="ClientesModals.verHistorialEnPantalla(${cl_id})" class="swal2-confirm swal2-styled" style="background:#2196f3;">
                            <ion-icon name="eye-outline"></ion-icon> Ver en Pantalla
                        </button>
                        <button onclick="ClientesModals.descargarHistorialPDF(${cl_id})" class="swal2-confirm swal2-styled" style="background:#f44336;">
                            <ion-icon name="document-text-outline"></ion-icon> Descargar PDF
                        </button>                    
                    </div>
                `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '600px'
                });
            }

            async function verHistorialEnPantalla(cl_id) {
                Swal.fire({
                    title: 'Cargando historial...',
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
                            clientesAjax: 'historial_completo',
                            cl_id: cl_id
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    const compras = data.compras || [];

                    if (compras.length === 0) {
                        Swal.fire('Sin datos', 'Este cliente no tiene compras registradas', 'info');
                        return;
                    }

                    const tablaHTML = `
                    <div style="max-height:500px;overflow-y:auto;">
                        <table class="table" style="width:100%;font-size:11px;">
                            <thead>
                                <tr>
                                    <th>N°</th>
                                    <th>Documento</th>
                                    <th>Fecha</th>
                                    <th>Medicamentos</th>
                                    <th>Unidades</th>
                                    <th>Total</th>
                                    <th>Vendedor</th>
                                    <th>Sucursal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${compras.map((c, i) => {
                                    const vendedor = (c.vendedor_nombre || '') + ' ' + (c.vendedor_apellido || '');
                                    const medicamentos = c.medicamentos_detalle 
                                        ? (c.medicamentos_detalle.length > 60 
                                            ? c.medicamentos_detalle.substring(0, 60) + '...' 
                                            : c.medicamentos_detalle)
                                        : '-';
                                    return `
                                    <tr>
                                        <td>${i + 1}</td>
                                        <td>${c.ve_numero_documento}</td>
                                        <td>${formatearFecha(c.ve_fecha_emision)}</td>
                                        <td style="font-size:10px;" title="${c.medicamentos_detalle || ''}">${medicamentos}</td>
                                        <td style="text-align:center;">${c.total_unidades || 0}</td>
                                        <td style="text-align:right;">Bs. ${formatearNumero(c.ve_total)}</td>
                                        <td style="font-size:10px;">${vendedor.trim() || 'N/A'}</td>
                                        <td style="font-size:10px;">${c.sucursal_nombre || '-'}</td>
                                    </tr>
                                `}).join('')}
                            </tbody>
                            <tfoot>
                                <tr style="background:#f5f5f5;font-weight:bold;">
                                    <td colspan="4">TOTALES:</td>
                                    <td style="text-align:center;">${compras.reduce((sum, c) => sum + parseInt(c.total_unidades || 0), 0)}</td>
                                    <td style="text-align:right;">Bs. ${formatearNumero(compras.reduce((sum, c) => sum + parseFloat(c.ve_total), 0))}</td>
                                    <td colspan="2">${compras.length} compras</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                `;

                    Swal.fire({
                        title: 'Historial Completo de Compras',
                        html: tablaHTML,
                        width: '1100px',
                        showCloseButton: true,
                        showConfirmButton: false
                    });

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el historial', 'error');
                }
            }

            function descargarHistorialPDF(cl_id) {
                Swal.close();

                const url = '<?php echo SERVER_URL; ?>ajax/clientesAjax.php?clientesAjax=exportar_pdf_detalle&cl_id=' + cl_id;

                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Generando PDF',
                    text: 'El historial completo se está generando en PDF...',
                    timer: 2000,
                    showConfirmButton: false
                });
            }




            return {
                abrirModalNuevo,
                cerrarModalNuevo,
                abrirModalEditar,
                cerrarModalEditar,
                toggleEstado,
                verDetalle,
                cerrarModalDetalle,
                editarDesdeDetalle,
                exportarPDFDetalle,
                verHistorialCompleto,
                verHistorialEnPantalla,
                descargarHistorialPDF
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const btnExcelClientes = document.getElementById('btnExportarExcelClientes');

            if (btnExcelClientes) {
                btnExcelClientes.addEventListener('click', function() {
                    const estadoSelect = document.querySelector('select[name="select1"]');
                    const comprasSelect = document.querySelector('select[name="select2"]');
                    const ultimaCompraSelect = document.querySelector('select[name="select3"]');
                    const busquedaInput = document.querySelector('input[name="busqueda"]');
                    const fechaDesdeInput = document.querySelector('input[name="fecha_desde"]');
                    const fechaHastaInput = document.querySelector('input[name="fecha_hasta"]');

                    const estado = estadoSelect ? estadoSelect.value : '';
                    const compras = comprasSelect ? comprasSelect.value : '';
                    const ultimaCompra = ultimaCompraSelect ? ultimaCompraSelect.value : '';
                    const busqueda = busquedaInput ? busquedaInput.value : '';
                    const fechaDesde = fechaDesdeInput ? fechaDesdeInput.value : '';
                    const fechaHasta = fechaHastaInput ? fechaHastaInput.value : '';

                    let url = '<?php echo SERVER_URL; ?>ajax/clientesAjax.php?clientesAjax=exportar_excel';

                    if (estado) url += '&select1=' + encodeURIComponent(estado);
                    if (compras) url += '&select2=' + encodeURIComponent(compras);
                    if (ultimaCompra) url += '&select3=' + encodeURIComponent(ultimaCompra);
                    if (busqueda) url += '&busqueda=' + encodeURIComponent(busqueda);
                    if (fechaDesde) url += '&fecha_desde=' + encodeURIComponent(fechaDesde);
                    if (fechaHasta) url += '&fecha_hasta=' + encodeURIComponent(fechaHasta);

                    console.log('Descargando Excel:', url);

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

            const btnPDFClientes = document.getElementById('btnExportarPDFClientes');

            if (btnPDFClientes) {
                btnPDFClientes.addEventListener('click', function() {
                    const url = '<?php echo SERVER_URL; ?>ajax/clientesAjax.php?clientesAjax=exportar_pdf_cliente';

                    console.log('Generando PDF:', url);

                    window.open(url, '_blank');

                    Swal.fire({
                        icon: 'success',
                        title: 'Generando PDF',
                        text: 'El archivo PDF se está generando...',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });
    </script>



<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
