<?php
if (isset($_SESSION['id_smp']) && $_SESSION['rol_smp'] == 1) {
?>

<div class="container tabla-dinamica"
    data-ajax-table="true"
    data-ajax-url="ajax/cajaAjax.php"
    data-ajax-param="cajaAjax"
    data-ajax-action="listar"
    data-ajax-registros="10">
    
    <div class="title">
        <h2>
            <ion-icon name="cash-outline"></ion-icon> Gestión de Cajas
        </h2>
    </div>

    <form class="filtro-dinamico">
        <div class="filtro-dinamico-search">
            <div class="form-fechas">
                <small>Estado</small>
                <select class="select-filtro" name="select1">
                    <option value="">Todas</option>
                    <option value="abierta">Abiertas</option>
                    <option value="cerrada">Cerradas</option>
                </select>
            </div>

            <div class="search">
                <input type="text" name="busqueda" placeholder="Buscar por nombre o usuario...">
                <button type="button" class="btn-search">
                    <ion-icon name="search-outline"></ion-icon>
                </button>
            </div>
        </div>
    </form>

    <div class="tabla-contenedor"></div>

    <div class="caja-grafico-container">
        <h3 class="caja-grafico-title">Total de Ventas por Usuario</h3>
        <div id="graficoVentasUsuario" class="caja-grafico-chart"></div>
    </div>
</div>

<div class="modal" id="modalCerrarCaja" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">
                <ion-icon name="checkmark-circle-outline"></ion-icon> Cerrar Caja
            </div>
            <a class="close" onclick="CajaGestion.cerrarModalCerrar()">
                <ion-icon name="close-outline"></ion-icon>
            </a>
        </div>

        <div class="modal-group">
            <div class="row">
                <div class="col">
                    <div class="modal-bloque">
                        <label>Nombre de Caja:</label>
                        <p id="modalNombreCaja" class="text-bold">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="modal-bloque">
                        <label>Usuario Responsable:</label>
                        <p id="modalUsuarioCaja" class="text-bold">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="modal-bloque">
                        <label>Saldo Inicial (Bs):</label>
                        <p id="modalSaldoInicial" class="text-bold">-</p>
                    </div>
                </div>
                <div class="col">
                    <div class="modal-bloque">
                        <label>Total Ingresos (Bs):</label>
                        <p id="modalTotalIngresos" class="text-bold">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="modal-bloque">
                        <label>Saldo Final Teórico (Bs):</label>
                        <p id="modalSaldoFinal" class="text-bold">-</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="modal-bloque">
                        <label>Observaciones:</label>
                        <textarea id="modalObservacion" class="modal-textarea"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-btn-content">
                <button type="button" class="btn danger btn-full" onclick="CajaGestion.confirmarCierre()">
                    <ion-icon name="checkmark-circle-outline"></ion-icon> Confirmar Cierre
                </button>
                <button type="button" class="btn warning btn-full" onclick="CajaGestion.cerrarModalCerrar()">
                    <ion-icon name="close-outline"></ion-icon> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .caja-grafico-container {
        margin-top: 40px;
        padding: 20px;
        background: var(--bg-secondary);
        border-radius: 8px;
    }

    .caja-grafico-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
        color: var(--text-primary);
    }

    .caja-grafico-chart {
        width: 100%;
        height: 400px;
        border-radius: 8px;
        background: var(--bg-primary);
    }

    .modal-textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-height: 80px;
        font-family: inherit;
        font-size: inherit;
    }

    .dark .modal-textarea {
        background: var(--dark-bg-secondary);
        color: var(--dark-text-primary);
        border-color: var(--dark-border-dark);
    }
</style>

<script>
    const CajaGestion = (() => {
        const API_URL = '<?php echo SERVER_URL; ?>ajax/cajaAjax.php';
        let cajaActual = null;
        let graficoVentas = null;

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

        function cargarVentasPorUsuario() {
            const formData = new FormData();
            formData.append('cajaAjax', 'ventas_por_usuario');

            fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.error || !data.ventas || data.ventas.length === 0) {
                    renderizarGraficoVacio();
                    return;
                }
                renderizarGraficoVentas(data.ventas);
            })
            .catch(error => {
                console.error('Error:', error);
                renderizarGraficoVacio();
            });
        }

        function renderizarGraficoVentas(ventas) {
            if (typeof echarts === 'undefined') {
                setTimeout(() => renderizarGraficoVentas(ventas), 100);
                return;
            }

            const container = document.getElementById('graficoVentasUsuario');
            if (!graficoVentas) {
                graficoVentas = echarts.init(container);
            }

            const datos = ventas.map(v => ({
                name: `${v.us_nombres || ''} ${v.us_apellido_paterno || ''}`.trim(),
                value: parseFloat(v.monto_ventas)
            }));

            const colores = ['#1976D2', '#4CAF50', '#FF9800', '#F44336', '#9C27B0', '#00BCD4', '#FFEB3B', '#795548'];

            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: function(params) {
                        return `<strong>${params.name}</strong><br/>Ventas: ${params.data.ventas}<br/>Monto: Bs. ${parseFloat(params.value).toFixed(2)}`;
                    }
                },
                legend: {
                    orient: 'vertical',
                    right: 10,
                    top: 'center',
                    textStyle: {
                        fontSize: 11
                    }
                },
                series: [{
                    name: 'Ventas por Usuario',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    center: ['35%', '50%'],
                    avoidLabelOverlap: true,
                    itemStyle: {
                        borderRadius: 8,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: true,
                        position: 'outside',
                        formatter: '{b}: Bs. {c}',
                        fontSize: 10
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 12,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: true,
                        length: 15,
                        length2: 10
                    },
                    data: datos.map((item, index) => ({
                        ...item,
                        itemStyle: {
                            color: colores[index % colores.length]
                        },
                        ventas: ventas[index].total_ventas
                    })),
                    animationType: 'scale',
                    animationEasing: 'elasticOut',
                    animationDelay: function(idx) {
                        return idx * 50;
                    }
                }]
            };

            graficoVentas.setOption(option);

            window.addEventListener('resize', function() {
                if (graficoVentas) graficoVentas.resize();
            });
        }

        function renderizarGraficoVacio() {
            if (typeof echarts === 'undefined') {
                setTimeout(renderizarGraficoVacio, 100);
                return;
            }

            const container = document.getElementById('graficoVentasUsuario');
            if (!graficoVentas) {
                graficoVentas = echarts.init(container);
            }

            graficoVentas.setOption({
                title: {
                    text: 'Sin datos de ventas',
                    left: 'center',
                    top: 'middle',
                    textStyle: {
                        color: '#999',
                        fontSize: 14
                    }
                }
            });
        }

        function abrirModalCerrar(cajaId) {
            const formData = new FormData();
            formData.append('cajaAjax', 'obtener');
            formData.append('caja_id', cajaId);

            fetch(API_URL, {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.error) {
                    Swal.fire('Error', 'No se pudo cargar la información de la caja', 'error');
                    return;
                }

                cajaActual = data.caja;
                const movimientos = data.movimientos;

                document.getElementById('modalNombreCaja').textContent = data.caja.caja_nombre || '-';
                document.getElementById('modalUsuarioCaja').textContent = `${data.caja.us_nombres || ''} ${data.caja.us_apellido_paterno || ''}`;
                document.getElementById('modalSaldoInicial').textContent = `Bs. ${formatearNumero(data.caja.caja_saldo_inicial)}`;
                document.getElementById('modalTotalIngresos').textContent = `Bs. ${formatearNumero(movimientos.total_ingresos)}`;
                
                const saldoFinal = parseFloat(data.caja.caja_saldo_inicial) + parseFloat(movimientos.total_ingresos);
                document.getElementById('modalSaldoFinal').textContent = `Bs. ${formatearNumero(saldoFinal)}`;
                document.getElementById('modalObservacion').value = '';

                const modal = document.getElementById('modalCerrarCaja');
                if (modal) modal.style.display = 'flex';
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al cargar los datos', 'error');
            });
        }

        function confirmarCierre() {
            if (!cajaActual) {
                Swal.fire('Error', 'No hay caja seleccionada', 'error');
                return;
            }

            const observacion = document.getElementById('modalObservacion').value;

            Swal.fire({
                title: 'Confirmar cierre de caja',
                text: `Se cerrará la caja ${cajaActual.caja_nombre}. Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39e00',
                cancelButtonColor: '#999',
                confirmButtonText: 'Sí, cerrar caja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('cajaAjax', 'cerrar');
                    formData.append('caja_id', cajaActual.caja_id);
                    formData.append('observacion', observacion);

                    fetch(API_URL, {
                        method: 'POST',
                        body: formData
                    })
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire(data.Titulo, data.texto, data.Tipo);
                        if (data.Tipo === 'success') {
                            cerrarModalCerrar();
                            document.querySelector('.tabla-dinamica .btn-search')?.click();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error al cerrar la caja', 'error');
                    });
                }
            });
        }

        function cerrarModalCerrar() {
            const modal = document.getElementById('modalCerrarCaja');
            if (modal) modal.style.display = 'none';
            cajaActual = null;
        }

        function init() {
            const modal = document.getElementById('modalCerrarCaja');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        cerrarModalCerrar();
                    }
                });
            }

            // Cargar gráfico cuando el DOM esté listo
            if (typeof echarts === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js';
                script.onload = cargarVentasPorUsuario;
                document.head.appendChild(script);
            } else {
                cargarVentasPorUsuario();
            }
        }

        document.addEventListener('DOMContentLoaded', init);

        return {
            abrirModalCerrar,
            confirmarCierre,
            cerrarModalCerrar
        };
    })();
</script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>Solo administradores pueden acceder a esta sección.</p>
    </div>
<?php } ?>
