const CajaHistorialTotales = (() => {
    function getBaseURL() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        // fallback
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    const API_URL = getBaseURL() + 'ajax/cajaAjax.php';

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
    }

    document.addEventListener('DOMContentLoaded', init);

    return {
        abrirModal,
        cerrarModal
    };
})();
