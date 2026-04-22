<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    /* en caso que el rol del usuario este en admin o genrente */
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/loteAjax.php"
        data-ajax-param="loteAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit">Lotes de Medicamentos</div>
                <div class="psub">Seguimiento y gestión de stock por lotes y fechas de vencimiento</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-out" id="btnExportarExcelLote" data-tip="Exportar a Excel">
                    <ion-icon name="download-outline"></ion-icon> Excel
                </button>
                <button type="button" class="btn btn-out" id="btnExportarPDFLote" data-tip="Exportar a PDF">
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
                            <label class="fl">Desde</label>
                            <input class="inp" type="date" name="fecha_desde">
                        </div>
                        <div class="fg">
                            <label class="fl">Hasta</label>
                            <input class="inp" type="date" name="fecha_hasta">
                        </div>
                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todos</option>
                                <option value="en_espera">En Espera</option>
                                <option value="activo">Activo</option>
                                <option value="terminado">Terminado</option>
                                <option value="caducado">Caducado</option>
                            </select>
                        </div>
                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal</label>
                                <select class="sel select-filtro" name="select3">
                                    <option value="">Todas</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre o principio activo...">
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
                <div class="tw">
                    <div class="tabla-contenedor"></div>
                </div>
            </div>
        </div>
    </div>


    <script>
        // OOP approach: LoteManager class to handle lote operations
        class LoteManager {
            constructor() {
                this.modalId = 'modalActivarLote';
            }

            // Method to open activation modal for a specific lote
            openActivationModal(loteId, nombre) {
                // Populate modal
                document.getElementById('detalleLote').innerHTML = `¿Desea activar el lote del medicamento: <strong>${nombre}</strong>?`;

                // Open modal
                const modal = document.getElementById(this.modalId);
                modal.style.display = 'flex';
                modal.classList.add('open');

                // Bind confirm button to activate method
                const confirmBtn = document.getElementById('btnConfirmarActivacion');
                confirmBtn.onclick = () => {
                    this.activateLote(loteId);
                };
            }

            // Method to activate lote via AJAX
            activateLote(loteId) {
                const url = '<?php echo SERVER_URL; ?>ajax/loteAjax.php';
                const body = `loteAjax=active&id=${loteId}`;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: body
                })
                .then(response => response.json())
                .then(data => {
                    // Close modal first
                    const modal = document.getElementById(this.modalId);
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);

                    Swal.fire({
                        icon: data.Tipo,
                        title: data.Titulo,
                        text: data.texto
                    });

                    if (data.Alerta === 'recargar') {
                        location.reload();
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al activar el lote'
                    });
                });
            }
        }

        // Create global instance
        const loteManager = new LoteManager();

        document.addEventListener('DOMContentLoaded', function() {
            const btnExcelLote = document.getElementById('btnExportarExcelLote');
            if (btnExcelLote) {
                btnExcelLote.addEventListener('click', function() {
                    exportarExcelLote();
                });
            }

            const btnPDFLote = document.getElementById('btnExportarPDFLote');
            if (btnPDFLote) {
                btnPDFLote.addEventListener('click', function() {
                    exportarPDFLote();
                });
            }
        });

        function exportarExcelLote() {
            const form = document.querySelector('.filtro-dinamico');
            const params = new URLSearchParams();
            params.append('loteAjax', 'exportar_excel_lote_controller');

            if (form) {
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
                const select1 = form.querySelector('select[name="select1"]');
                const select3 = form.querySelector('select[name="select3"]');
                const busqueda = form.querySelector('input[name="busqueda"]');

                if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
                if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
                if (select1 && select1.value) params.append('select1', select1.value);
                if (select3 && select3.value) params.append('select3', select3.value);
                if (busqueda && busqueda.value) params.append('busqueda', encodeURIComponent(busqueda.value));
            }

            const url = '<?php echo SERVER_URL; ?>ajax/loteAjax.php?' + params.toString();
            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Descargando',
                text: 'El archivo Excel se está descargando...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function exportarPDFLote() {
            const form = document.querySelector('.filtro-dinamico');
            const params = new URLSearchParams();
            params.append('loteAjax', 'exportar_pdf');

            if (form) {
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
                const select1 = form.querySelector('select[name="select1"]');
                const select3 = form.querySelector('select[name="select3"]');
                const busqueda = form.querySelector('input[name="busqueda"]');

                if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
                if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
                if (select1 && select1.value) params.append('select1', select1.value);
                if (select3 && select3.value) params.append('select3', select3.value);
                if (busqueda && busqueda.value) params.append('busqueda', encodeURIComponent(busqueda.value));
            }

            const url = '<?php echo SERVER_URL; ?>ajax/loteAjax.php?' + params.toString();
            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Generando PDF',
                text: 'El reporte se está generando...',
                timer: 2000,
                showConfirmButton: false
            });
        }
    </script>

    <!-- Modal Activar Lote -->
    <div class="mov" id="modalActivarLote">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Activar Lote</div>
                    <div class="ms">Confirme la activación del lote seleccionado</div>
                </div>
                <button class="mcl" onclick="closeM('modalActivarLote')">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <div class="mb">
                <div id="detalleLote"></div>

                <div class="card mt16" style="background: var(--btn-warning-pale); border-color: var(--btn-warning);">
                    <div class="cb">
                        <div class="litem" style="border:none">
                            <ion-icon name="alert-circle-outline" style="font-size:20px;color:var(--btn-warning)"></ion-icon>
                            <div class="f1">
                                <div class="th5" style="color:var(--btn-warning)">Atención</div>
                                <div class="tc" style="color:var(--btn-warning)">La activación del lote solo puede hacerse una vez. Luego la edición será limitada.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button type="button" class="btn btn-war" onclick="closeM('modalActivarLote')">Cancelar</button>
                <button type="button" id="btnConfirmarActivacion" class="btn btn-def">Activar Lote</button>
            </div>
        </div>
    </div>

    <!-- Modal Editar Lote -->
    <div class="mov" id="modalEditarLote">
        <div class="modal mxl">
            <div class="mh ">
                <div>
                    <div class="mt">Editar Lote</div>
                    <div class="ms" id="modalEditarLoteTitulo">...</div>
                </div>
                <button class="mcl" onclick="LoteModals.cerrarEdicion()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/loteAjax.php" method="POST" data-form="update" autocomplete="off" id="formEditarLote">
                <div class="mb" style="max-height: 400px; overflow-y: auto;">
                    <input type="hidden" name="loteAjax" value="update">
                    <input type="hidden" name="id" id="editarLoteId">

                    <div class="stit">Información del Lote</div>
                    <div class="fr">
                        <div class="card">
                            <div class="cb">
                                <div class="litem"><ion-icon name="cube-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Número de lote</div><div class="th5" id="detalleEditarNumero">-</div></div></div>
                                <div class="litem"><ion-icon name="medical-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Medicamento</div><div class="th5" id="detalleEditarMedicamento">-</div></div></div>
                                <div class="litem"><ion-icon name="business-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Proveedor</div><div class="th5" id="detalleEditarProveedor">-</div></div></div>
                                <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Fecha Ingreso</div><div class="th5" id="detalleEditarIngreso">-</div></div></div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="cb">
                                <div class="litem"><ion-icon name="pricetag-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Estado</div><div class="th5" id="detalleEditarEstado">-</div></div></div>
                                <div class="litem"><ion-icon name="cart-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Precio Compra Actual</div><div class="th5" id="detalleEditarPrecioCompra">-</div></div></div>
                                <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Precio Venta Actual</div><div class="th5" id="detalleEditarPrecioVenta">-</div></div></div>
                                <div class="litem" style="border:none"><ion-icon name="time-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Vencimiento Actual</div><div class="th5" id="detalleEditarVencimiento">-</div></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="stit">Datos Editables</div>
                    <div class="card">
                        <div class="cb">
                            <div class="fr3">
                                 <div class="fg">
                                     <label class="fl">Cajas del lote</label>
                                     <input class="inp" type="number" name="Cantidad_caja_up" id="editarCantidadCaja" min="0">
                                 </div>
                                 <div class="fg">
                                     <label class="fl">Unidades individuales por empaque</label>
                                     <input class="inp" type="number" name="Cantidad_unidades_up" id="editarCantidadUnidades" min="0">
                                 </div>
                            </div>
                             <div class="fr3">
                                 <div class="fg">
                                     <label class="fl">Precio de compra</label>
                                     <input class="inp" type="number" step="0.01" name="Precio_compra_up" id="editarPrecioCompra" min="0" required>
                                 </div>
                                  <div class="fg">
                                      <label class="fl">Precio venta por unidad</label>
                                      <input class="inp" type="number" step="0.01" name="Precio_venta_up" id="editarPrecioVenta" min="0" readonly>
                                  </div>
                                 <div class="fg">
                                     <label class="fl">Fecha de vencimiento</label>
                                     <input class="inp" type="date" name="Fecha_vencimiento_up" id="editarFechaVencimiento" required>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <div class="stit">Datos de Auditoría</div>
                     <div class="card">
                         <div class="cb">
                             <div class="fr">
                                 <div class="fg">
                                     <label class="fl" for="costo_lista">Costo Lista</label>
                                     <input class="inp" type="number" step="0.01" name="lm_costo_lista" id="editarCostoLista" min="0">
                                 </div>
                                 <div class="fg">
                                     <label class="fl" for="margen_unitario">Margen Unitario (%)</label>
                                     <input class="inp" type="number" step="0.01" name="lm_margen_u" id="editarMargenU" min="0">
                                 </div>
                             </div>
                             <div class="fr">
                                 <div class="fg">
                                     <label class="fl" for="margen_caja">Margen Caja (%)</label>
                                     <input class="inp" type="number" step="0.01" name="lm_margen_c" id="editarMargenC" min="0">
                                 </div>
                                 <div class="fg">
                                     <label class="fl" for="precio_min_unitario">Precio Min. Unitario</label>
                                     <input class="inp" type="number" step="0.01" name="lm_precio_min_u" id="editarPrecioMinU" min="0">
                                 </div>
                             </div>
                             <div class="fr">
                                 <div class="fg">
                                     <label class="fl" for="precio_min_caja">Precio Min. Caja</label>
                                     <input class="inp" type="number" step="0.01" name="lm_precio_min_c" id="editarPrecioMinC" min="0">
                                 </div>
                             </div>
                         </div>
                     </div>

                    <div class="card mt16" style="background: var(--btn-warning-pale); border-color: var(--btn-warning);">
                        <div class="cb">
                            <div class="litem" style="border:none">
                                <ion-icon name="alert-circle-outline" style="font-size:20px;color:var(--btn-warning)"></ion-icon>
                                <div class="f1">
                                    <div class="th5" style="color:var(--btn-warning)">Atención</div>
                                    <div class="tc" style="color:var(--btn-warning)">Verifique apropiadamente la información que desea modificar, cualquier cambio puede influir de manera negativa al inventario.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mf">
                    <button type="button" class="btn btn-war" onclick="LoteModals.cerrarEdicion()">Cancelar</button>
                    <button type="submit" class="btn btn-def"><ion-icon name="save-outline"></ion-icon> Actualizar Lote</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const LoteModals = (function() {
            'use strict';
            const API_URL = '<?php echo SERVER_URL; ?>ajax/loteAjax.php';

            async function abrirEdicion(loteId) {
                try {
                    const formData = new FormData();
                    formData.append('loteAjax', 'obtener_lote');
                    formData.append('lote_id', loteId);

                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();
                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    document.getElementById('editarLoteId').value = loteId;
                    document.getElementById('modalEditarLoteTitulo').textContent = data.lm_numero_lote + ' - ' + data.med_nombre;
                    document.getElementById('detalleEditarNumero').textContent = data.lm_numero_lote;
                    document.getElementById('detalleEditarMedicamento').textContent = data.med_nombre;
                    document.getElementById('detalleEditarProveedor').textContent = data.proveedor_nombres || 'Sin proveedor';
                    document.getElementById('detalleEditarIngreso').textContent = data.lm_fecha_ingreso;
                    document.getElementById('detalleEditarEstado').textContent = data.lm_estado;
                    document.getElementById('detalleEditarPrecioCompra').textContent = data.lm_precio_compra + ' Bs';
                    document.getElementById('detalleEditarPrecioVenta').textContent = data.lm_precio_venta + ' Bs';
                    document.getElementById('detalleEditarVencimiento').textContent = data.lm_fecha_vencimiento;

                    document.getElementById('editarCantidadCaja').value = data.lm_cant_caja;
                    document.getElementById('editarCantidadUnidades').value = data.lm_cant_unidad;
                    document.getElementById('editarPrecioCompra').value = data.lm_precio_compra;
                    document.getElementById('editarPrecioVenta').value = data.lm_precio_venta;
                    document.getElementById('editarFechaVencimiento').value = data.lm_fecha_vencimiento;
                    /* campos de auditoria */
                    document.getElementById('editarCostoLista').value = data.lm_costo_lista || '';
                    document.getElementById('editarMargenU').value = data.lm_margen_u || '';
                    document.getElementById('editarMargenC').value = data.lm_margen_c || '';
                    document.getElementById('editarPrecioMinU').value = data.lm_precio_min_u || '';
                    document.getElementById('editarPrecioMinC').value = data.lm_precio_min_c || '';

                    document.getElementById('modalEditarLote').style.display = 'flex';
                    document.getElementById('modalEditarLote').classList.add('open');

                    // Bind calculation events and trigger initial calculations
                    bindCalculationEvents();
                    calcularPrecioVenta();
                    calcularPrecioMinCaja();
                    calcularPrecioMinUnitario();

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Ocurrió un error al cargar los datos del lote', 'error');
                }
            }

            function cerrarEdicion() {
                const modal = document.getElementById('modalEditarLote');
                modal.classList.remove('open');
                setTimeout(() => modal.style.display = 'none', 300);
                document.getElementById('formEditarLote').reset();
            }



            function calcularPrecioVenta() {
                const costo = parseFloat(document.getElementById('editarCostoLista')?.value) || 0;
                const margen = parseFloat(document.getElementById('editarMargenU')?.value) || 0;
                const precioVenta = costo + (costo * margen / 100);
                const precioVentaInput = document.getElementById('editarPrecioVenta');
                if (precioVentaInput) precioVentaInput.value = precioVenta.toFixed(2);
            }

            function calcularPrecioMinCaja() {
                const costo = parseFloat(document.getElementById('editarCostoLista')?.value) || 0;
                const margen = parseFloat(document.getElementById('editarMargenC')?.value) || 0;
                // Usar valor fijo de 1 para unidades por caja (lm_cant_blister)
                const unidadesPorCaja = 1;
                const precioMinCaja = costo * unidadesPorCaja * (1 + margen / 100);
                const precioMinCajaInput = document.getElementById('editarPrecioMinC');
                if (precioMinCajaInput) precioMinCajaInput.value = precioMinCaja.toFixed(2);
            }

            function calcularPrecioMinUnitario() {
                const costo = parseFloat(document.getElementById('editarCostoLista')?.value) || 0;
                const margen = parseFloat(document.getElementById('editarMargenC')?.value) || 0;
                const precioMinUnitario = costo * (1 + margen / 100);
                const precioMinUnitarioInput = document.getElementById('editarPrecioMinU');
                if (precioMinUnitarioInput) precioMinUnitarioInput.value = precioMinUnitario.toFixed(2);
            }

            function clampMargen(input) {
                let valor = parseFloat(input.value);
                if (isNaN(valor) || valor < 0) {
                    input.value = '0';
                } else if (valor > 100) {
                    input.value = '100';
                } else if (input.value.includes('.')) {
                    let parts = input.value.split('.');
                    if (parts[1].length > 2) {
                        input.value = parts[0] + '.' + parts[1].substring(0, 2);
                    }
                }
            }

            function validarMargen(input) {
                clampMargen(input);
                let valor = parseFloat(input.value);
                input.value = valor.toFixed(2);
            }

            function bindCalculationEvents() {
                const costoLista = document.getElementById('editarCostoLista');
                const margenU = document.getElementById('editarMargenU');
                const margenC = document.getElementById('editarMargenC');

                if (costoLista) {
                    costoLista.addEventListener('input', () => {
                        calcularPrecioVenta();
                        calcularPrecioMinCaja();
                        calcularPrecioMinUnitario();
                    });
                }

                if (margenU) {
                    margenU.addEventListener('input', (e) => {
                        clampMargen(e.target);
                        calcularPrecioVenta();
                    });
                    margenU.addEventListener('blur', (e) => validarMargen(e.target));
                }

                if (margenC) {
                    margenC.addEventListener('input', (e) => {
                        clampMargen(e.target);
                        calcularPrecioMinCaja();
                        calcularPrecioMinUnitario();
                    });
                    margenC.addEventListener('blur', (e) => validarMargen(e.target));
                }
            }

            return {
                abrirEdicion,
                cerrarEdicion,
                bindCalculationEvents
            };
        })();
    </script>

<?php } else {
    echo "que miras bobo";
?>
    <!-- en caso que no tenga el rol determinado -->
    <!-- eliminar sesion -->

<?php } ?>
