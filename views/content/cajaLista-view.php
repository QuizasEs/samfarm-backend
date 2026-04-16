<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="pg tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/cajaAjax.php"
        data-ajax-param="cajaAjax"
        data-ajax-action="listar"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="cash-outline"></ion-icon> Gestión de Cajas</div>
                <div class="psub">Administre y supervise las cajas del sistema</div>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr2">
                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="abierta">Abiertas</option>
                                <option value="cerrada">Cerradas</option>
                                <option value="">Todas</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
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

        <div class="card mb16">
            <div class="cb">
                <div class="tw">
                    <div class="tabla-contenedor"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="bar-chart-outline"></ion-icon> Total de Ventas por Usuario</div>
            </div>
            <div class="cb">
                <div id="graficoVentasUsuario"></div>
            </div>
        </div>
    </div>

    <div class="mov" id="modalCerrarCaja" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="checkmark-circle-outline"></ion-icon> Cerrar Caja</div>
                    <div class="ms">Confirme el cierre de la caja con los datos finales</div>
                </div>
                <button class="mcl" onclick="CajaGestion.cerrarModalCerrar()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Nombre de Caja:</label>
                        <p class="tbs" id="modalNombreCaja">-</p>
                    </div>
                </div>

                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Usuario Responsable:</label>
                        <p class="tbs" id="modalUsuarioCaja">-</p>
                    </div>
                </div>

                <div class="fr2">
                    <div class="fg">
                        <label class="fl">Saldo Inicial (Bs):</label>
                        <p class="tbs" id="modalSaldoInicial">-</p>
                    </div>
                    <div class="fg">
                        <label class="fl">Total Ingresos (Bs):</label>
                        <p class="tbs" id="modalTotalIngresos">-</p>
                    </div>
                </div>

                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Saldo Final Teórico (Bs):</label>
                        <p class="tbs" id="modalSaldoFinal">-</p>
                    </div>
                </div>

                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Observaciones:</label>
                        <textarea id="modalObservacion" class="inp" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="CajaGestion.cerrarModalCerrar()">Cancelar</button>
                <button class="btn btn-danger" onclick="CajaGestion.confirmarCierre()"><ion-icon name="checkmark-circle-outline"></ion-icon> Confirmar Cierre</button>
            </div>
        </div>
    </div>

    <style>
        #graficoVentasUsuario {
            width: 100%;
            height: 400px;
            min-width: 300px;
            max-width: 100%;
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

                // Detecta si el contenedor es pequeño
                const isMobile = container.offsetWidth < 450;

                const datos = ventas.map(v => ({
                    name: `${v.us_nombres || ''} ${v.us_apellido_paterno || ''}`.trim(),
                    value: parseFloat(v.monto_ventas)
                }));

                const colores = ['#1976D2', '#4CAF50', '#FF9800', '#F44336', '#9C27B0', '#00BCD4', '#FFEB3B', '#795548', '#009688', '#E91E63', '#3F51B5', '#FFC107', '#009688', '#E91E63', '#3F51B5', '#FFC107', '#009688', '#E91E63'];

                const option = {
                    tooltip: {
                        trigger: 'item',
                        formatter: function(params) {
                            return `<strong>${params.name}</strong><br/>Ventas: ${params.data.ventas}<br/>Monto: Bs. ${parseFloat(params.value).toFixed(2)}`;
                        }
                    },

                    legend: {
                        orient: isMobile ? 'horizontal' : 'vertical',
                        right: isMobile ? null : 30,
                        top: isMobile ? null : 'center',
                        bottom: isMobile ? 0 : null,
                        textStyle: {
                            fontSize: 13,
                            color: colores
                        }
                    },

                    series: [{
                        name: 'Ventas por Usuario',
                        type: 'pie',
                        radius: isMobile ? ['35%', '55%'] : ['40%', '70%'], 
                        center: isMobile ? ['50%', '50%'] : ['35%', '50%'], 
                        avoidLabelOverlap: true,

                        itemStyle: {
                            borderRadius: 8,
                            borderColor: '#F0FFFE',
                            borderWidth: 2
                        },

                        label: {
                            show: true,
                            position: 'outside',
                            formatter: '{b}: Bs. {c}',
                            fontSize: 13,
                            Color: '#0037C4', 

                        },

                        emphasis: {
                            label: {
                                show: true,
                                fontSize: 16,
                                fontWeight: 'bold'
                            }
                        },

                        labelLine: {
                            show: true,
                            length: 20,
                            length2: 10,
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
                        animationDelay: idx => idx * 50
                    }]
                };

                graficoVentas.setOption(option);
                graficoVentas.resize();

                if (!window.__graficoVentasObserverAdded) {
                    const ro = new ResizeObserver(() => {
                        graficoVentas.resize();
                    });
                    ro.observe(container);
                    window.__graficoVentasObserverAdded = true;
                }

                window.addEventListener('resize', () => graficoVentas.resize());
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
                        if (modal) {
                            modal.style.display = 'flex';
                            modal.classList.add('open');
                        }
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
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
                    cajaActual = null;
                }
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
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</div>
                <div class="psub">Solo administradores pueden acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb">
                <div style="text-align: center; padding: 60px;">
                    <ion-icon name="lock-closed-outline" style="font-size: 64px; color: #ccc;"></ion-icon>
                </div>
            </div>
        </div>
    </div>
<?php } ?>