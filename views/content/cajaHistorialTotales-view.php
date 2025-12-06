<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
    $rol_usuario = $_SESSION['rol_smp'];
?>

<div class="container tabla-dinamica"
    data-ajax-table="true"
    data-ajax-url="ajax/cajaAjax.php"
    data-ajax-param="cajaAjax"
    data-ajax-action="cajas_cerradas"
    data-ajax-registros="10">
    
    <div class="title">
        <h2>
            <ion-icon name="cash-outline"></ion-icon> Historial de Cajas Cerradas
        </h2>
    </div>

    <form class="filtro-dinamico">
        <div class="filtro-dinamico-search">
            <?php if ($rol_usuario == 1) { ?>
                <div class="form-fechas">
                    <small>Sucursal</small>
                    <select class="select-filtro" name="select1">
                        <option value="">Todas las sucursales</option>
                        <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="search">
                <input type="text" name="busqueda" placeholder="Buscar por nombre o usuario...">
                <button type="button" class="btn-search">
                    <ion-icon name="search-outline"></ion-icon>
                </button>
            </div>
        </div>
    </form>

    <div id="contenedorResumen" class="caja-resumen-container" style="display:none;">
        <div class="caja-resumen-grid">
            <div class="caja-resumen-item">
                <label>Total Cajas Cerradas</label>
                <p id="resumenTotalCajas">0</p>
            </div>
            <div class="caja-resumen-item">
                <label>Total Saldos Iniciales</label>
                <p id="resumenSaldosIniciales">Bs. 0.00</p>
            </div>
            <div class="caja-resumen-item">
                <label>Total Saldos Finales</label>
                <p id="resumenSaldosFinales">Bs. 0.00</p>
            </div>
            <div class="caja-resumen-item">
                <label>Diferencia Total</label>
                <p id="resumenDiferencia">Bs. 0.00</p>
            </div>
            <div class="caja-resumen-item">
                <label>Total Ventas</label>
                <p id="resumenVentas">Bs. 0.00</p>
            </div>
        </div>
    </div>

    <div class="tabla-contenedor"></div>
</div>

<div class="modal" id="modalDetalleCajaCerrada" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <ion-icon name="cash-outline"></ion-icon> Detalle de Caja Cerrada
            </div>
            <a class="close" onclick="CajaHistorialTotales.cerrarModal()">
                <ion-icon name="close-outline"></ion-icon>
            </a>
        </div>

        <div class="modal-group">
            <div class="row">
                <h3><ion-icon name="information-circle-outline"></ion-icon> Información General</h3>
            </div>

            <div class="row">
                <div class="col">
                    <label>Nombre de Caja:</label>
                    <p id="detalleCajaNombre">-</p>
                </div>
                <div class="col">
                    <label>Usuario:</label>
                    <p id="detalleCajaUsuario">-</p>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>Sucursal:</label>
                    <p id="detalleCajaSucursal">-</p>
                </div>
                <div class="col">
                    <label>Fecha Cierre:</label>
                    <p id="detalleCajaFechaCierre">-</p>
                </div>
            </div>

            <div class="row">
                <h3><ion-icon name="trending-up-outline"></ion-icon> Movimientos Financieros</h3>
            </div>

            <div class="row">
                <div class="col">
                    <label>Saldo Inicial (Bs):</label>
                    <p id="detalleCajaSaldoInicial" class="text-bold">-</p>
                </div>
                <div class="col">
                    <label>Saldo Final (Bs):</label>
                    <p id="detalleCajaSaldoFinal" class="text-bold">-</p>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>Diferencia Arqueo (Bs):</label>
                    <p id="detalleCajaDiferencia" class="text-bold">-</p>
                </div>
                <div class="col">
                    <label>Total Ventas (Bs):</label>
                    <p id="detalleCajaVentas" class="text-bold">-</p>
                </div>
            </div>

            <div class="modal-btn-content">
                <a href="javascript:void(0)" class="btn warning" onclick="CajaHistorialTotales.cerrarModal()">Cerrar</a>
            </div>
        </div>
    </div>
</div>

<style>
    .caja-resumen-container {
        margin-bottom: 30px;
    }

    .caja-resumen-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .caja-resumen-item {
        padding: 15px;
        background: var(--bg-secondary);
        border-radius: 8px;
        text-align: center;
        border-left: 4px solid #13386c;
    }

    .caja-resumen-item label {
        display: block;
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }

    .caja-resumen-item p {
        font-size: 18px;
        font-weight: bold;
        color: var(--text-primary);
    }

    .dark .caja-resumen-item {
        background: var(--dark-bg-secondary);
        border-left-color: #ff7b36;
    }
</style>

<script>
    const CajaHistorialTotales = (() => {
        const API_URL = '<?php echo SERVER_URL; ?>ajax/cajaAjax.php';

        function formatearNumero(num) {
            return parseFloat(num || 0).toFixed(2);
        }

        function formatearFecha(fecha) {
            if (!fecha) return '-';
            const d = new Date(fecha);
            const dia = String(d.getDate()).padStart(2, '0');
            const mes = String(d.getMonth() + 1).padStart(2, '0');
            const anio = d.getFullYear();
            const horas = String(d.getHours()).padStart(2, '0');
            const minutos = String(d.getMinutes()).padStart(2, '0');
            return `${dia}/${mes}/${anio} ${horas}:${minutos}`;
        }

        function abrirModal(cajaData) {
            const arqueo = parseFloat(cajaData.caja_saldo_final || 0) - parseFloat(cajaData.caja_saldo_inicial || 0);
            const nombreUsuario = `${cajaData.us_nombres || ''} ${cajaData.us_apellido_paterno || ''}`.trim();

            document.getElementById('detalleCajaNombre').textContent = cajaData.caja_nombre || '-';
            document.getElementById('detalleCajaUsuario').textContent = nombreUsuario || '-';
            document.getElementById('detalleCajaSucursal').textContent = cajaData.su_nombre || '-';
            document.getElementById('detalleCajaFechaCierre').textContent = formatearFecha(cajaData.caja_cerrado_en);
            document.getElementById('detalleCajaSaldoInicial').textContent = `Bs. ${formatearNumero(cajaData.caja_saldo_inicial)}`;
            document.getElementById('detalleCajaSaldoFinal').textContent = `Bs. ${formatearNumero(cajaData.caja_saldo_final)}`;
            document.getElementById('detalleCajaDiferencia').textContent = `${arqueo >= 0 ? '+' : ''}Bs. ${formatearNumero(arqueo)}`;
            document.getElementById('detalleCajaVentas').textContent = `Bs. ${formatearNumero(cajaData.total_ventas)}`;

            const modal = document.getElementById('modalDetalleCajaCerrada');
            if (modal) modal.style.display = 'flex';
        }

        function cerrarModal() {
            const modal = document.getElementById('modalDetalleCajaCerrada');
            if (modal) modal.style.display = 'none';
        }

        function init() {
            const modal = document.getElementById('modalDetalleCajaCerrada');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        cerrarModal();
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', init);

        return {
            abrirModal,
            cerrarModal
        };
    })();
</script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>