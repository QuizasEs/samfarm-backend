<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

    $rol_usuario = $_SESSION['rol_smp'];
    $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
?>

    <div class="pg tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/cajaHistorialAjax.php"
        data-ajax-param="cajaHistorialAjax"
        data-ajax-registros="15">

        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="cash-outline"></ion-icon> Historial de Movimientos de Caja
                </div>
                <div class="psub">Consulte el historial completo de movimientos de caja</div>
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
                            <label class="fl">Tipo de Movimiento</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todos</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="egreso">Egreso</option>
                                <option value="venta">Venta</option>
                                <option value="compra">Compra</option>
                                <option value="ajuste">Ajuste</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl">Usuario</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todos los usuarios</option>
                                <?php
                                foreach ($datos_select['caja'] as $usuario) {
                                    $nombre_completo = trim(($usuario['us_nombres'] ?? '') . ' ' . ($usuario['us_apellido_paterno'] ?? ''));
                                    echo '<option value="' . $usuario['us_id'] . '">' . $nombre_completo . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <?php if ($rol_usuario == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursal</label>
                                <select class="sel select-filtro" name="select3">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } else { ?>
                            <div></div>
                        <?php } ?>
                        <div class="fg">
                            <label class="fl">Búsqueda</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por concepto o referencia...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flxe mt12">
                        <button type="button" class="btn btn-def" id="btnExportarExcelCajaHistorial">
                            <ion-icon name="download-outline"></ion-icon> Exportar Excel
                        </button>
                        <button type="button" class="btn btn-def" id="btnExportarPDFCajaHistorial">
                            <ion-icon name="document-text-outline"></ion-icon> Exportar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="resumen-periodo" class="card mb16">
            <div class="cb">
                <!-- Resumen will be loaded here -->
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Movimientos</div>
            </div>
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="bar-chart-outline"></ion-icon> Gráfico de Movimientos</div>
            </div>
            <div class="cb">
                <div id="grafico-movimientos" style="width: 100%; height: 400px;"></div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle de Caja Historial -->
    <div id="modalReferenciaCajaHistorial" class="mov">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="cash-outline"></ion-icon>
                        Detalle del Movimiento
                    </div>
                    <div class="ms">Información detallada del movimiento seleccionado</div>
                </div>
                <button class="mcl" onclick="CajaHistorial.cerrarModalReferenciaCaja()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>
            <div class="mb">
                <div id="contenidoReferenciaCajaHistorial" class="cb tb"></div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="CajaHistorial.cerrarModalReferenciaCaja()">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        const CajaHistorial = (() => {
            return {
                verReferencia: function(tipo, id) {
                    if (!tipo || !id) {
                        Swal.fire('Error', 'Referencia inválida', 'error');
                        return;
                    }

                    console.log('verReferencia llamado - tipo:', tipo, 'id:', id);

                    const modal = document.getElementById('modalReferenciaCajaHistorial');
                    const contenido = document.getElementById('contenidoReferenciaCajaHistorial');

                    modal.style.display = 'flex';
                    modal.classList.add('open');

                    fetch('<?php echo SERVER_URL; ?>ajax/cajaHistorialAjax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            'cajaHistorialAjax': 'obtener_referencia',
                            'tipo': tipo,
                            'id': id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.html) {
                            contenido.innerHTML = data.html;
                        } else if (data.error) {
                            contenido.innerHTML = '<p style="color: red; text-align: center;">' + data.error + '</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        contenido.innerHTML = '<p style="color: red; text-align: center;">Error al cargar la referencia</p>';
                    });
                },

                cerrarModalReferencia: function() {
                    const modal = document.getElementById('modalReferenciaCajaHistorial');
                    if (modal) {
                        modal.classList.remove('open');
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 300);
                    }
                },

                cerrarModalReferenciaCaja: function() {
                    const modal = document.getElementById('modalReferenciaCajaHistorial');
                    if (modal) {
                        modal.classList.remove('open');
                        setTimeout(() => {
                            modal.style.display = 'none';
                        }, 300);
                    }
                },

                exportarMovimiento: async function(mc_id) {
                    if (!mc_id) {
                        Swal.fire('Error', 'ID de movimiento inválido', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Generando PDF...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    try {
                        const data = await fetch('<?php echo SERVER_URL; ?>ajax/cajaHistorialAjax.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                cajaHistorialAjax: 'exportar_movimiento_pdf',
                                mc_id: mc_id
                            })
                        }).then(response => response.json());

                        Swal.close();

                        if (data.success && data.pdf_base64) {
                            window.abrirPDFDesdeBase64(data.pdf_base64, `Movimiento_${mc_id}.pdf`);
                            Swal.fire({
                                icon: 'success',
                                title: 'PDF generado',
                                text: 'El PDF se ha abierto en una nueva ventana',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'No se pudo generar el PDF', 'error');
                    }
                }
            };
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const btnExcel = document.getElementById('btnExportarExcelCajaHistorial');
            if (btnExcel) {
                btnExcel.addEventListener('click', function() {
                    exportarExcelCajaHistorial();
                });
            }

            const btnPDF = document.getElementById('btnExportarPDFCajaHistorial');
            if (btnPDF) {
                btnPDF.addEventListener('click', function() {
                    exportarPDFCajaHistorial();
                });
            }
        });

        function exportarExcelCajaHistorial() {
            const form = document.querySelector('.filtro-dinamico');
            const params = new URLSearchParams();
            params.append('cajaHistorialAjax', 'exportar_excel');

            if (form) {
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
                const select1 = form.querySelector('select[name="select1"]');
                const select2 = form.querySelector('select[name="select2"]');
                const select3 = form.querySelector('select[name="select3"]');

                if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
                if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
                if (select1 && select1.value) params.append('select1', select1.value);
                if (select2 && select2.value) params.append('select2', select2.value);
                if (select3 && select3.value) params.append('su_id', select3.value);
            }

            const url = '<?php echo SERVER_URL; ?>ajax/cajaHistorialAjax.php?' + params.toString();

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Descargando Excel',
                text: 'El archivo se está descargando...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function exportarPDFCajaHistorial() {
            const form = document.querySelector('.filtro-dinamico');
            const params = new URLSearchParams();
            params.append('cajaHistorialAjax', 'exportar_pdf');

            if (form) {
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
                const select1 = form.querySelector('select[name="select1"]');
                const select2 = form.querySelector('select[name="select2"]');
                const select3 = form.querySelector('select[name="select3"]');

                if (fechaDesde && fechaDesde.value) params.append('fecha_desde', fechaDesde.value);
                if (fechaHasta && fechaHasta.value) params.append('fecha_hasta', fechaHasta.value);
                if (select1 && select1.value) params.append('select1', select1.value);
                if (select2 && select2.value) params.append('select2', select2.value);
                if (select3 && select3.value) params.append('select3', select3.value);
            }

            const url = '<?php echo SERVER_URL; ?>ajax/cajaHistorialAjax.php?' + params.toString();
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

<?php } else { ?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit">Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb txctr" style="padding:60px">
                <ion-icon name="lock-closed-outline" style="font-size:48px;color:var(--text-faint);margin-bottom:16px"></ion-icon>
                <div class="th3 mb8">Acceso Denegado</div>
                <div class="tbs tmut">No tiene permisos para acceder a esta sección.</div>
            </div>
        </div>
    </div>
<?php } ?>
