<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
    $rol_usuario = $_SESSION['rol_smp'];
?>

<div class="pg tabla-dinamica"
    data-ajax-table="true"
    data-ajax-url="ajax/cajaAjax.php"
    data-ajax-param="cajaAjax"
    data-ajax-action="cajas_cerradas"
    data-ajax-registros="10">

    <div class="ph">
        <div>
            <div class="ptit">
                <ion-icon name="cash-outline"></ion-icon> Historial de Cajas Cerradas
            </div>
            <div class="psub">Consulta el historial completo de cajas cerradas y sus totales</div>
        </div>
    </div>

    <div class="card mb16">
        <div class="ch">
            <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
        </div>
        <div class="cb">
            <form class="filtro-dinamico">
                <div class="fr1">
                    <?php if ($rol_usuario == 1) { ?>
                        <div class="fg">
                            <label class="fl">Sucursal</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="">Todas las sucursales</option>
                                <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                    <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    <?php } ?>

                    <div class="fg">
                        <label class="fl">Búsqueda</label>
                        <div class="inpg">
                            <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre o usuario...">
                            <button type="button" class="btn btn-def btn-search">
                                <ion-icon name="search-outline"></ion-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="contenedorResumen" style="display:none;">
        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="bar-chart-outline"></ion-icon> Resumen General</div>
            </div>
            <div class="cb">
                <div class="grid4">
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="archive-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="resumenTotalCajas">0</div>
                            <div class="sl">Total Cajas Cerradas</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="resumenSaldosIniciales">Bs. 0.00</div>
                            <div class="sl">Saldos Iniciales</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="wallet-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="resumenSaldosFinales">Bs. 0.00</div>
                            <div class="sl">Saldos Finales</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="trending-up-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="resumenDiferencia">Bs. 0.00</div>
                            <div class="sl">Diferencia Total</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="cart-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="resumenVentas">Bs. 0.00</div>
                            <div class="sl">Total Ventas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="ch">
            <div class="ct"><ion-icon name="list-outline"></ion-icon> Cajas Cerradas</div>
        </div>
        <div class="cb">
            <div class="tabla-contenedor"></div>
        </div>
    </div>
</div>

<div class="mov" id="modalDetalleCajaCerrada">
    <div class="modal">
        <div class="mh">
            <div>
                <div class="mt">
                    <ion-icon name="cash-outline"></ion-icon>
                    Detalle de Caja Cerrada
                </div>
                <div class="ms">Información completa de la caja cerrada</div>
            </div>
            <button class="mcl" onclick="CajaHistorialTotales.cerrarModal()">
                <ion-icon name="close-outline"></ion-icon>
            </button>
        </div>

        <div class="mb">
            <div class="stit">
                <ion-icon name="information-circle-outline"></ion-icon> Información General
            </div>

            <div class="fr mb16">
                <div class="card">
                    <div class="cb">
                        <div class="litem"><ion-icon name="cash-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Nombre de Caja</div><div class="th5" id="detalleCajaNombre">-</div></div></div>
                        <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Usuario</div><div class="th5" id="detalleCajaUsuario">-</div></div></div>
                        <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Sucursal</div><div class="th5" id="detalleCajaSucursal">-</div></div></div>
                        <div class="litem" style="border:none"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Fecha Cierre</div><div class="th5" id="detalleCajaFechaCierre">-</div></div></div>
                    </div>
                </div>
            </div>

            <div class="stit">
                <ion-icon name="trending-up-outline"></ion-icon> Movimientos Financieros
            </div>

            <div class="grid4">
                <div class="statc">
                    <div class="siw gr"><ion-icon name="cash-outline"></ion-icon></div>
                    <div>
                        <div class="sv" id="detalleCajaSaldoInicial">-</div>
                        <div class="sl">Saldo Inicial</div>
                    </div>
                </div>
                <div class="statc">
                    <div class="siw ww"><ion-icon name="wallet-outline"></ion-icon></div>
                    <div>
                        <div class="sv" id="detalleCajaSaldoFinal">-</div>
                        <div class="sl">Saldo Final</div>
                    </div>
                </div>
                <div class="statc">
                    <div class="siw rd"><ion-icon name="trending-up-outline"></ion-icon></div>
                    <div>
                        <div class="sv" id="detalleCajaDiferencia">-</div>
                        <div class="sl">Diferencia</div>
                    </div>
                </div>
                <div class="statc">
                    <div class="siw bl"><ion-icon name="cart-outline"></ion-icon></div>
                    <div>
                        <div class="sv" id="detalleCajaVentas">-</div>
                        <div class="sl">Total Ventas</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mf">
            <button class="btn btn-war" onclick="CajaHistorialTotales.cerrarModal()">Cerrar</button>
        </div>
    </div>
</div>


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
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('open');
            }
        }

        function cerrarModal() {
            const modal = document.getElementById('modalDetalleCajaCerrada');
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 300);
            }
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