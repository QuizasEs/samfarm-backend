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
            <div class="mh">
                <div>
                    <div class="mt">Editar Lote</div>
                    <div class="ms" id="modalEditarLoteTitulo">...</div>
                </div>
                <button class="mcl" onclick="LoteModals.cerrarEdicion()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/loteAjax.php" method="POST" data-form="update" autocomplete="off" id="formEditarLote">
                <div class="mb">
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
                                    <label class="fl">Unidades de empaque por caja</label>
                                    <input class="inp" type="number" name="Cantidad_blister_up" id="editarCantidadBlister" min="0">
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
                                    <input class="inp" type="number" step="0.01" name="Precio_venta_up" id="editarPrecioVenta" min="0" required>
                                </div>
                                <div class="fg">
                                    <label class="fl">Fecha de vencimiento</label>
                                    <input class="inp" type="date" name="Fecha_vencimiento_up" id="editarFechaVencimiento" required>
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
                    document.getElementById('editarCantidadBlister').value = data.lm_cant_blister;
                    document.getElementById('editarCantidadUnidades').value = data.lm_cant_unidad;
                    document.getElementById('editarPrecioCompra').value = data.lm_precio_compra;
                    document.getElementById('editarPrecioVenta').value = data.lm_precio_venta;
                    document.getElementById('editarFechaVencimiento').value = data.lm_fecha_vencimiento;

                    document.getElementById('modalEditarLote').style.display = 'flex';
                    document.getElementById('modalEditarLote').classList.add('open');

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



            return {
                abrirEdicion,
                cerrarEdicion
            };
        })();
    </script>

<?php } else {
    echo "que miras bobo";
?>
    <!-- en caso que no tenga el rol determinado -->
    <!-- eliminar sesion -->

<?php } ?>
