<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();

    $rol_usuario = $_SESSION['rol_smp'];
    $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/cajaHistorialAjax.php"
        data-ajax-param="cajaHistorialAjax"
        data-ajax-registros="15">

        <div class="title">
            <h2>
                <ion-icon name="cash-outline"></ion-icon> Historial de Movimientos de Caja
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <div class="form-fechas">
                    <small>Desde</small>
                    <input type="date" name="fecha_desde" placeholder="Selecciona fecha desde">
                </div>

                <div class="form-fechas">
                    <small>Hasta</small>
                    <input type="date" name="fecha_hasta" placeholder="Selecciona fecha hasta">
                </div>

                <div class="form-fechas">
                    <small>Tipo de Movimiento</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todos</option>
                        <option value="ingreso">Ingreso</option>
                        <option value="egreso">Egreso</option>
                        <option value="venta">Venta</option>
                        <option value="compra">Compra</option>
                        <option value="ajuste">Ajuste</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Usuario</small>
                    <select class="select-filtro" name="select2">
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
                    <div class="form-fechas">
                        <small>Sucursal</small>
                        <select class="select-filtro" name="select3">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por concepto o referencia...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>

            </div>

            <div class="filtro-dinamico-buttons">

                <button type="button" class="btn success" id="btnExportarExcelCajaHistorial">
                    <ion-icon name="download-outline"></ion-icon> Excel
                </button>
                <button type="button" class="btn primary" id="btnExportarPDFCajaHistorial">
                    <ion-icon name="document-text-outline"></ion-icon> PDF
                </button>
            </div>
        </form>

        <div id="resumen-periodo"></div>

        <div class="tabla-contenedor"></div>

        <div id="grafico-movimientos" style="width: 100%; height: 400px; margin-top: 20px;"></div>
    </div>

    <div class="modal" id="modalReferenciaMovimiento" style="display: none;">
        <div class="modal-content detalle">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="document-outline"></ion-icon>
                    Detalle de Referencia
                </div>
                <a class="close" onclick="CajaHistorial.cerrarModalReferencia()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <div class="modal-group" id="contenidoReferenciaMovimiento">
                <div style="text-align: center; padding: 40px;">
                    <ion-icon name="hourglass-outline" style="font-size: 48px; color: #999;"></ion-icon>
                    <p>Cargando información...</p>
                </div>
            </div>

            <div class="modal-btn-content">
                <a href="javascript:void(0)" class="btn default" onclick="CajaHistorial.cerrarModalReferencia()">
                    Cerrar
                </a>
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

                    const modal = document.getElementById('modalReferenciaMovimiento');
                    const contenido = document.getElementById('contenidoReferenciaMovimiento');
                    
                    modal.style.display = 'flex';

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
                    const modal = document.getElementById('modalReferenciaMovimiento');
                    modal.style.display = 'none';
                },

                exportarMovimiento: function(mc_id) {
                    if (!mc_id) {
                        Swal.fire('Error', 'ID de movimiento inválido', 'error');
                        return;
                    }

                    const url = '<?php echo SERVER_URL; ?>ajax/cajaHistorialAjax.php?cajaHistorialAjax=exportar_movimiento_pdf&mc_id=' + mc_id;
                    window.open(url, '_blank');

                    Swal.fire({
                        icon: 'success',
                        title: 'Generando PDF',
                        text: 'El comprobante se está generando...',
                        timer: 2000,
                        showConfirmButton: false
                    });
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

            const modal = document.getElementById('modalReferenciaMovimiento');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        CajaHistorial.cerrarModalReferencia();
                    }
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
                title: 'Descargando',
                text: 'El archivo Excel se está descargando...',
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
                if (select3 && select3.value) params.append('su_id', select3.value);
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
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
