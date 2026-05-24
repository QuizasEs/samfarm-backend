const UsuariosModals = (function() {
    'use strict';

    function getBaseURL() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    const API_URL = getBaseURL() + 'ajax/usuariosAjax.php';

    function abrirModalNuevo() {
        const modal = document.getElementById('modalNuevoUsuario');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
        }
    }

    function cerrarModalNuevo() {
        const modal = document.getElementById('modalNuevoUsuario');
        if (modal) {
            modal.classList.remove('open');
            setTimeout(() => modal.style.display = 'none', 300);
            const form = modal.querySelector('form');
            if (form) form.reset();
        }
    }

    async function abrirModalEditar(us_id) {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    usuariosAjax: 'datos_usuario',
                    us_id: us_id
                })
            });

            const data = await response.json();

            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            document.getElementById('us_id_editar').value = data.us_id;
            document.getElementById('Nombres_edit').value = data.us_nombres || '';
            document.getElementById('ApellidoPaterno_edit').value = data.us_apellido_paterno || '';
            document.getElementById('ApellidoMaterno_edit').value = data.us_apellido_materno || '';
            document.getElementById('Carnet_edit').value = data.us_numero_carnet || '';
            document.getElementById('Telefono_edit').value = data.us_telefono || '';
            document.getElementById('Correo_edit').value = data.us_correo || '';
            document.getElementById('Direccion_edit').value = data.us_direccion || '';
            document.getElementById('UsuarioName_edit').value = data.us_username || '';
            document.getElementById('Rol_edit').value = data.ro_id || '';
            document.getElementById('Sucursal_edit').value = data.su_id || '';

            document.getElementById('Password_edit').value = '';
            document.getElementById('PasswordConfirm_edit').value = '';

            const modal = document.getElementById('modalEditarUsuario');
            if (modal) {
                modal.style.display = 'flex';
                modal.classList.add('open');
            }

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar los datos del usuario', 'error');
        }
    }

    function cerrarModalEditar() {
        const modal = document.getElementById('modalEditarUsuario');
        if (modal) {
            modal.classList.remove('open');
            setTimeout(() => modal.style.display = 'none', 300);
        }
    }

    async function toggleEstado(us_id, estado) {
        const texto = estado == 1 ? 'desactivar' : 'activar';

        const result = await Swal.fire({
            title: '¿Está seguro?',
            text: '¿Desea ' + texto + ' este usuario?',
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
                    usuariosAjax: 'toggle_estado',
                    us_id: us_id,
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

    async function verDetalle(us_id) {
        document.getElementById('detalleUsuarioId').value = us_id;

        const modal = document.getElementById('modalDetalleUsuario');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
        }

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    usuariosAjax: 'detalle_completo',
                    us_id: us_id
                })
            });

            const data = await response.json();

            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            const nombreCompleto = `${data.us_nombres || ''} ${data.us_apellido_paterno || ''} ${data.us_apellido_materno || ''}`.trim();
            document.getElementById('detalleUsuarioNombre').textContent = nombreCompleto;
            document.getElementById('detalleNombreCompletoUsuario').textContent = nombreCompleto;
            document.getElementById('detalleUsername').textContent = data.us_username || '-';
            document.getElementById('detalleCarnetUsuario').textContent = data.us_numero_carnet || '-';
            document.getElementById('detalleTelefonoUsuario').textContent = data.us_telefono || '-';
            document.getElementById('detalleCorreoUsuario').textContent = data.us_correo || '-';
            document.getElementById('detalleDireccionUsuario').textContent = data.us_direccion || '-';
            document.getElementById('detalleRolUsuario').textContent = data.rol_nombre || '-';
            document.getElementById('detalleSucursalUsuario').textContent = data.sucursal_nombre || '-';
            document.getElementById('detalleFechaRegistroUsuario').textContent = formatearFecha(data.us_creado_en);

            const estadoHtml = data.us_estado == 1 ?
                '<span class="badge bgr"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>' :
                '<span class="badge bgry"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';
            document.getElementById('detalleEstadoUsuario').innerHTML = estadoHtml;

            document.getElementById('detalleTotalVentas').textContent = data.total_ventas || 0;
            document.getElementById('detalleMontoTotalUsuario').textContent = 'Bs. ' + formatearNumero(data.monto_total || 0);

            const btnToggle = document.getElementById('btnToggleEstadoDetalleUsuario');
            if (btnToggle) {
                btnToggle.onclick = function() {
                    toggleEstado(us_id, data.us_estado);
                };
            }

            // Cargar tabla de ventas
            cargarUltimasVentasUsuario(us_id);

            // NUEVO: Cargar gráfico de ventas mensuales
            cargarGraficoVentasMensuales(us_id);

        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar el detalle del usuario', 'error');
        }
    }

    async function cargarGraficoVentasMensuales(us_id) {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    usuariosAjax: 'ventas_mensuales',
                    us_id: us_id
                })
            });

            const data = await response.json();

            if (data.error) {
                console.error('Error cargando ventas mensuales:', data.error);
                return;
            }

            // Inicializar gráfico ECharts
            const chartDom = document.getElementById('graficoVentasUsuario');
            const myChart = echarts.init(chartDom);

            const meses = data.ventas_mensuales.map(item => item.mes);
            const cantidades = data.ventas_mensuales.map(item => parseInt(item.cantidad));
            const montos = data.ventas_mensuales.map(item => parseFloat(item.monto));

            const option = {
                title: {
                    text: 'Rendimiento de Ventas',
                    left: 'center',
                    textStyle: {
                        fontSize: 16,
                        fontWeight: 'bold',
                        color: '#333',
                    },
                    top: 10
                },
                tooltip: {
                    trigger: 'axis',
                    axisPointer: {
                        type: 'cross'
                    }
                },
                legend: {
                    data: ['Cantidad', 'Monto Bs.'],
                    top: 40,
                    textStyle: {
                        fontSize: 12
                    }
                },
                grid: {
                    left: '15%',
                    right: '15%',
                    top: '25%',
                    bottom: '25%',
                    containLabel: true
                },
                xAxis: {
                    type: 'category',
                    data: meses,
                    axisLabel: {
                        rotate: 0,
                        fontSize: 11,
                        interval: 0
                    }
                },
                yAxis: [{
                        type: 'value',
                        name: 'Cantidad',
                        position: 'left',
                        axisLabel: {
                            formatter: '{value}'
                        }
                    },
                    {
                        type: 'value',
                        name: 'Bs.',
                        position: 'right',
                        axisLabel: {
                            formatter: '{value}'
                        }
                    }
                ],
                series: [{
                        name: 'Cantidad',
                        type: 'bar',
                        data: cantidades,
                        itemStyle: {
                            color: '#1976D2'
                        },
                        label: {
                            show: false
                        }
                    },
                    {
                        name: 'Monto Bs.',
                        type: 'line',
                        yAxisIndex: 1,
                        data: montos,
                        itemStyle: {
                            color: '#4CAF50'
                        },
                        lineStyle: {
                            width: 2
                        },
                        label: {
                            show: false
                        }
                    }
                ]
            };

            myChart.setOption(option);

            // Responsive simple
            window.addEventListener('resize', function() {
                myChart.resize();
            });

        } catch (error) {
            console.error('Error en gráfico:', error);
        }
    }

    async function cargarUltimasVentasUsuario(us_id) {
        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    usuariosAjax: 'ultimas_ventas',
                    us_id: us_id
                })
            });

            const data = await response.json();
            const tbody = document.getElementById('tablaUltimasVentasUsuario');

            if (data.ventas && data.ventas.length > 0) {
                tbody.innerHTML = data.ventas.map(venta => {
                    const cliente = venta.cliente_nombre || 'Sin cliente';
                    return `
                        <tr>
                            <td><strong>${venta.ve_numero_documento}</strong></td>
                            <td>${formatearFecha(venta.ve_fecha_emision)}</td>
                            <td style="font-size:11px;">${cliente}</td>
                            <td style="text-align:center;">${venta.total_items || 0}</td>
                            <td style="text-align:right;"><strong style="color:#1976D2;">Bs. ${formatearNumero(venta.ve_total)}</strong></td>
                            <td style="font-size:11px;">${venta.ve_tipo_documento || 'nota de venta'}</td>
                        </tr>
                    `
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="cart-outline"></ion-icon> Sin ventas registradas</td></tr>';
            }

        } catch (error) {
            console.error('Error:', error);
        }
    }

    function cerrarModalDetalle() {
        const modal = document.getElementById('modalDetalleUsuario');
        if (modal) {
            modal.classList.remove('open');
            setTimeout(() => modal.style.display = 'none', 300);
        }
    }

    function editarDesdeDetalle() {
        const us_id = document.getElementById('detalleUsuarioId').value;
        cerrarModalDetalle();
        abrirModalEditar(us_id);
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

    return {
        abrirModalNuevo,
        cerrarModalNuevo,
        abrirModalEditar,
        cerrarModalEditar,
        toggleEstado,
        verDetalle,
        cerrarModalDetalle,
        editarDesdeDetalle
    };
})();
