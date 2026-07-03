<script>
    // funcionamiento del modo oscuro
    const toggle = document.querySelector('#darkModeToggleInput'); // el input real
    const body = document.querySelector('body');

    if (toggle) {
        load();

        // Detectar cambio de estado
        toggle.addEventListener('change', () => {
            body.classList.toggle('dark');
            store(body.classList.contains('dark'));
        });

        // Cargar estado guardado
        function load() {
            const darkmode = localStorage.getItem('dark') === 'true';
            body.classList.toggle('dark', darkmode);
            toggle.checked = darkmode; // refleja el estado en el toggle
        }

        // Guardar estado
        function store(value) {
            localStorage.setItem('dark', value);
        }
    }
</script>

<!-- PREVENIR CIERRE DE MODALES AL HACER CLICK FUERA -->
<script>
    document.addEventListener('click', (e) => {
        const modal = e.target;
        if (modal && modal.classList && modal.classList.contains('modal')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    }, true);
</script>

<script type="text/javascript">
    //crafica de ingresos y egresos
    // Initialize the echarts instance based on the prepared dom
    var graphycEl = document.getElementById('graphyc');
    if (graphycEl) {
        var myChart = echarts.init(graphycEl);
    }

    if (graphycEl) {
        // Specify the configuration items and data for the chart
        var option = {
            title: {
                text: 'INGRESOS EGRESOS'
            },
            tooltip: {},
            legend: {
                data: ['sales']
            },
            xAxis: {
                data: ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO']
            },
            yAxis: {},
            series: [{
                name: 'egresos',
                type: 'bar',
                data: [5, 25, 36, 10, 10, 34, 1]
            }, {
                name: 'ingresos',
                type: 'bar',
                data: [1, 20, 56, 10, 13, 20, 1]
            }]
        };

        // Display the chart using the configuration items and data just specified.
        myChart.setOption(option);
    }
</script>
<script src="<?php echo SERVER_URL; ?>views/script/alertas.js"></script>
<script>
    // Para visualizar imágenes en inputs

    const imgPic = document.getElementById('img-pic');
    const inputFile = document.getElementById('imgLoad');

    // Validar que los elementos existan antes de agregar eventos
    if (imgPic && inputFile) {
        inputFile.onchange = function() {
            if (inputFile.files && inputFile.files[0]) {
                // Validar tamaño del archivo (5MB)
                if (inputFile.files[0].size > 5 * 1024 * 1024) {
                    alert('El archivo es muy grande. Máximo 5MB.');
                    inputFile.value = '';
                    imgPic.style.display = 'none';
                    return;
                }

                // Mostrar imagen
                imgPic.src = URL.createObjectURL(inputFile.files[0]);
                imgPic.style.display = 'block';
            }
        }
    } else {}
</script>

<!-- para busqueda en el formulario de registro de compras  -->
<script>
    const SearchManager = (() => {
        let currentTimeout;
        let hasSearched = false;

        function init() {
            bindEvents();
            const resultados = document.getElementById('tablaMedicamentos');
            if (resultados && !hasSearched) {
                resultados.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Use los filtros o busque por nombre</td></tr>';
            }
        }

        function bindEvents() {

            const searchBtn = document.querySelector('.btn-search');
            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    buscar();
                });
            }


            const searchInput = document.getElementById('buscarMedicamento');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    if (currentTimeout) clearTimeout(currentTimeout);

                    const termino = this.value.trim();

                    if (termino.length > 0 || hasSearched) {
                        currentTimeout = setTimeout(() => buscar(), 300);
                    }
                });
            }


            const filterSelects = document.querySelectorAll('select[name="Form_reg"], select[name="Via_reg"], select[name="Laboratorio_reg"], select[name="Uso_reg"], select[name="Proveedor_filtro"]');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    if (currentTimeout) clearTimeout(currentTimeout);
                    currentTimeout = setTimeout(() => buscar(), 300);
                });
            });
        }

        async function buscar() {
            const resultados = document.getElementById('tablaMedicamentos');
            if (!resultados) return;


            const termino = document.getElementById('buscarMedicamento')?.value.trim() || '';
            const forma = document.querySelector('select[name="Form_reg"]')?.value || '';
            const via = document.querySelector('select[name="Via_reg"]')?.value || '';
            const laboratorio = document.querySelector('select[name="Laboratorio_reg"]')?.value || '';
            const uso = document.querySelector('select[name="Uso_reg"]')?.value || '';
            const proveedor = document.querySelector('select[name="Proveedor_filtro"]')?.value || '';


            if (!termino && !forma && !via && !laboratorio && !uso && !proveedor) {
                if (hasSearched) {
                    resultados.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Ingrese algún criterio de búsqueda</td></tr>';
                }
                return;
            }

            hasSearched = true;
            resultados.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Buscando...</td></tr>';

            try {
                const filtros = {
                    compraAjax: 'buscar_medicamentos',
                    termino: termino,
                    forma: forma,
                    via: via,
                    laboratorio: laboratorio,
                    uso: uso,
                    proveedor: proveedor
                };


                const response = await fetch('<?php echo SERVER_URL; ?>ajax/compraAjax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(filtros)
                });

                if (!response.ok) throw new Error('Error en la respuesta');

                const data = await response.json();
                renderResultados(data);

            } catch (error) {
                resultados.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Error en la búsqueda</td></tr>';
            }
        }

        function renderResultados(data) {
            const resultados = document.getElementById('tablaMedicamentos');
            if (!resultados) return;

            if (!data || data.length === 0) {
                resultados.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> No se encontraron resultados</td></tr>';
                return;
            }

            resultados.innerHTML = data.map((item, index) => `
        <tr class="fila-seleccionable" onclick="handleSelectItem(${item.med_id}, '${escapeHtml(item.nombre || '')}')" style="cursor:pointer;">
            <td>${index + 1}</td>
            <td>${escapeHtml(item.nombre || 'N/A')}</td>
            <td>${escapeHtml(item.med_presentacion || 'N/A')}</td>
            <td>${escapeHtml(item.proveedor || 'N/A')}</td>
            <td>${escapeHtml(item.med_codigo_barras || 'N/A')}</td>
            <td>
                <span style="color: #27ae60; font-size: 12px;">Click para agregar</span>
            </td>
        </tr>
    `).join('');
        }

        function escapeHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function formatCurrency(amount) {
            const num = parseFloat(amount) || 0;
            return `${num.toFixed(2)}`;
        }

        return {
            init
        };
    })();

    document.addEventListener('DOMContentLoaded', () => {
        SearchManager.init();
    });
</script>

<?php if (isset($_SESSION['id_smp']) && !empty($_SESSION['id_smp'])) { ?>
    <!-- Session Timer Component -->
    <style>
        .session-timer-floating {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #fff;
            color: #333;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            font-family: Arial, sans-serif;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        body.dark .session-timer-floating {
            background: #242526;
            color: #e4e6eb;
            border-color: #3e4042;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .session-timer-floating .timer-close {
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            line-height: 1;
            padding: 2px 5px;
            border-radius: 4px;
            color: #888;
        }

        .session-timer-floating .timer-close:hover {
            background: rgba(0, 0, 0, 0.1);
            color: #333;
        }

        body.dark .session-timer-floating .timer-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .session-timer-floating .timer-icon {
            font-size: 18px;
            color: #1976D2;
        }

        body.dark .session-timer-floating .timer-icon {
            color: #2196F3;
        }

        .session-timer-expired {
            color: #d32f2f;
            font-weight: 600;
        }

        body.dark .session-timer-expired {
            color: #ff5252;
        }
    </style>

    <div id="sessionTimer" class="session-timer-floating">
        <span class="timer-icon"><ion-icon name="time-outline"></ion-icon></span>
        <div id="timerContent">
            Sesión: <span id="timerCountdown">--:--</span>
        </div>
        <span class="timer-close" onclick="document.getElementById('sessionTimer').style.display='none'">&times;</span>
    </div>

    <script>
        (function() {
            // Obtener tiempo de vida de sesión de PHP (en segundos)
            // session_cache_expire() retorna minutos, multiplicamos por 60
            // Si no, usamos un valor por defecto seguro como 24 minutos (1440s)
            let sessionTime = <?php echo session_cache_expire() * 60; ?>;

            // Si session_cache_expire retorna 0 o algo raro, default a 1440
            if (sessionTime <= 0) sessionTime = 1440;

            const countdownEl = document.getElementById('timerCountdown');
            const timerContent = document.getElementById('timerContent');

            function updateTimer() {
                if (sessionTime <= 0) {
                    timerContent.innerHTML = '<span class="session-timer-expired">Sesión expirada. Recargue la página.</span>';
                    clearInterval(timerInterval);
                    return;
                }

                const minutes = Math.floor(sessionTime / 60);
                const seconds = sessionTime % 60;

                countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                sessionTime--;
            }

            updateTimer();
            const timerInterval = setInterval(updateTimer, 1000);
        })();
    </script>
<?php } ?>

<!-- script para modales -->
<script>
    const ModalManager = (() => {
        const modal = document.getElementById("modalLote");
        const modalNombre = document.getElementById("modalMedicamentoNombre");
        const modalId = document.getElementById("modalMedicamentoId");
        const contenedorLista = document.getElementById("items-compra");
        const listaLotes = [];

        let contadorLote = 0;

        /** 🧮 Inicializa el contador de lote */
        function inicializarContador() {
            const ultimoLoteInput = document.getElementById("ultimo_lote_valor");
            const valor = ultimoLoteInput ? ultimoLoteInput.value.trim() : "";

            if (!valor || valor === "0") {
                contadorLote = 0;
                return;
            }

            const patron = /^MED-(\d+)$/;
            const match = valor.match(patron);

            if (!match) {
                Swal.fire({
                    icon: "error",
                    title: "Número de lote inválido",
                    text: "El número de lote anterior no tiene un formato válido. Se iniciará desde MED-0000.",
                    timer: 4000,
                    showConfirmButton: false
                });
                contadorLote = 0;
                return;
            }

            contadorLote = parseInt(match[1]) || 0;
        }

        /** 🔢 Genera un número de lote único y formateado */
        function generarNumeroLote() {
            const nuevoNumero = contadorLote + listaLotes.length + 1;
            return `MED-${String(nuevoNumero).padStart(4, "0")}`;
        }

        /** 🧹 Limpia campos del modal */
        function limpiarCampos() {
            [
                "cantidad",
                "cantidad_unidades",
                "fecha_vencimiento",
                "precio_compra",
                "precio_venta_reg",
                "costo_lista",
                "margen_unitario",
                "margen_caja",
                "precio_min_unitario",
                "precio_min_caja"
            ].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = "";
            });
            // Reset cantidad_unidades to 1
            const cantUni = document.getElementById("cantidad_unidades");
            if (cantUni) cantUni.value = 1;

            const check = document.getElementById("cb5");
            if (check) check.checked = false;
        }

        /** 🔓 Abre el modal de lote */
        function abrirModal(id, nombre) {
            if (!modal) return;
            modal.style.display = "flex";
            setTimeout(() => modal.classList.add('open'), 10);
            modalId.value = id;
            modalNombre.textContent = nombre;
            limpiarCampos();

            const numeroLoteInput = document.getElementById("numero_lote");
            if (numeroLoteInput) {
                numeroLoteInput.value = generarNumeroLote();
            }
        }

        /** 🔒 Cierra modal */
        function cerrarModal() {
            if (modal) {
                modal.classList.remove('open');
                setTimeout(() => modal.style.display = "none", 300);
            }
            limpiarCampos();
        }

        /**  Valida datos antes de agregar lote */
        function validarCampos() {
            const numero = document.getElementById("numero_lote").value.trim();
            const cantidad = parseInt(document.getElementById("cantidad").value);
            const cantidadUnidades = parseInt(document.getElementById("cantidad_unidades").value) || 1;
            const vencimiento = document.getElementById("fecha_vencimiento").value;
            const precioCompra = parseFloat(document.getElementById("precio_compra").value);
            const precioVenta = parseFloat(document.getElementById("precio_venta_reg").value);
            const activar = 1;

            // Campos de auditoría (costo lista y márgenes por caja)
            const costoLista = parseFloat(document.getElementById("costo_lista").value) || null;
            const margenUnitario = parseFloat(document.getElementById("margen_unitario").value) || null;
            const margenCaja = parseFloat(document.getElementById("margen_caja").value) || null;
            const precioMinUnitario = parseFloat(document.getElementById("precio_min_unitario").value) || null;
            const precioMinCaja = parseFloat(document.getElementById("precio_min_caja").value) || null;

            if (!numero) {
                Swal.fire('Error', 'No se pudo generar el número de lote.', 'error');
                return false;
            }
            if (!cantidad || cantidad <= 0) {
                Swal.fire('Error', 'La cantidad debe ser mayor a 0.', 'error');
                return false;
            }
            if (!vencimiento) {
                Swal.fire('Error', 'Debe ingresar una fecha de vencimiento.', 'error');
                return false;
            }

            const fechaVenc = new Date(vencimiento);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            if (fechaVenc < hoy) {
                Swal.fire('Error', 'La fecha de vencimiento no puede ser anterior a hoy.', 'error');
                return false;
            }

            if (!precioVenta || precioVenta <= 0) {
                Swal.fire('Error', 'Precio de venta inválido.', 'error');
                return false;
            }

            // Validar que costo_lista sea el valor principal
            if (!costoLista || costoLista <= 0) {
                Swal.fire('Error', 'Costo lista debe ser mayor a 0.', 'error');
                return false;
            }

            return {
                numero,
                cantidad,
                cantidad_blister: 1,
                cantidad_unidades: cantidadUnidades,
                vencimiento,
                precioCompra,
                precioVenta,
                activar_lote: activar,
                costo_lista: costoLista,
                margen_unitario: margenUnitario,
                margen_caja: margenCaja,
                precio_min_unitario: precioMinUnitario,
                precio_min_caja: precioMinCaja
            };
        }

        /** ➕ Agrega un nuevo lote */
        function agregarLote() {
            const datos = validarCampos();
            if (!datos) {
                alert('Datos inválidos en el modal. Verifica que todos los campos estén llenos correctamente.');
                return;
            }

            const id = modalId.value;
            const nombre = modalNombre.textContent;

            listaLotes.push({
                id_medicamento: id,
                nombre,
                ...datos
            });
            recalcularNumerosLote();
            renderizarLista();
            actualizarTotales();
            cerrarModal();
            Swal.fire('Éxito', 'Lote agregado correctamente', 'success');
        }

        /** 🧾 Renderiza todos los lotes */
        function renderizarLista() {
            if (listaLotes.length === 0) {
                contenedorLista.innerHTML = "<p style='text-align:center; padding: 20px; color: #666;'>No hay lotes agregados aún.</p>";
                return;
            }

            contenedorLista.innerHTML = listaLotes.map((lote, i) => `
                <div class="lote-card card mb-3 shadow-sm">
                    <div class="lote-card-header card-header d-flex justify-content-between align-items-center bg-light">
                        <div class="lote-card-info">
                            <strong class="lote-titulo text-primary">${i + 1}. ${lote.nombre}</strong>
                            <span class="badge ${lote.activar_lote ? 'badge-success' : 'badge-secondary'} ml-2">
                                ${lote.activar_lote ? 'Activo' : 'Inactivo'}
                            </span>

                            <div class="lote-detalles mt-2 fila-1">
                                <span class="text-muted"><ion-icon name="clipboard-outline"></ion-icon> <strong>Lote:</strong> ${lote.numero}</span>
                                <span class="espacio text-muted"><ion-icon name="cube-outline"></ion-icon> <strong>Cant:</strong> ${lote.cantidad}</span>
                                <span class="espacio text-muted"><ion-icon name="calendar-outline"></ion-icon> <strong>Vence:</strong> ${formatearFecha(lote.vencimiento)}</span>
                            </div>

                            <div class="lote-detalles fila-2">
                                <span class="text-success"><ion-icon name="cash-outline"></ion-icon> <strong>Compra:</strong> Bs. ${lote.precioCompra.toFixed(2)}</span>
                                <span class="espacio text-info"><ion-icon name="pricetag-outline"></ion-icon> <strong>Venta:</strong> Bs. ${lote.precioVenta.toFixed(2)}</span>
                                <span class="espacio text-warning"><ion-icon name="card-outline"></ion-icon> <strong>Subtotal:</strong> Bs. ${(lote.cantidad * lote.precioCompra).toFixed(2)}</span>
                            </div>
                        </div>

                        <div>
                            <button type="button" class="btn btn-danger btn-sm lote-btn-eliminar" onclick="ModalManager.eliminarLote(${i})">
                                <ion-icon name="trash-outline"></ion-icon> Eliminar
                            </button>
                        </div>
                    </div>
                </div>

        `).join("");
        }

        function formatearFecha(fecha) {
            const [y, m, d] = fecha.split("-");
            return `${d}/${m}/${y}`;
        }

        /** 💰 Actualiza totales */
        function actualizarTotales() {
            const subtotal = listaLotes.reduce((t, l) => t + (l.cantidad * l.precioCompra), 0);
            const total = subtotal; // Impuestos en 0%

            document.getElementById("total").textContent = `Bs. ${total.toFixed(2)}`;
        }

        /**   Eliminar lote */
        function eliminarLote(i) {
            Swal.fire({
                title: "¿Estás seguro?",
                text: `¿Deseas eliminar el lote ${listaLotes[i].numero}?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if (result.isConfirmed) {
                    listaLotes.splice(i, 1);
                    recalcularNumerosLote();
                    renderizarLista();
                    actualizarTotales();
                    Swal.fire("¡Eliminado!", "El lote ha sido eliminado.", "success");
                }
            });
        }

        /** ♻️ Recalcula los números después de eliminar o agregar */
        function recalcularNumerosLote() {
            listaLotes.forEach((lote, index) => {
                lote.numero = `MED-${String(contadorLote + index + 1).padStart(4, "0")}`;
            });
        }

        // 🚀 Inicialización
        inicializarContador();

        return {
            abrirModal,
            cerrarModal,
            agregarLote,
            eliminarLote,
            obtenerLotes: () => listaLotes,
            obtenerTotales: () => {
                const subtotal = listaLotes.reduce((t, l) => t + (l.cantidad * l.precioCompra), 0);
                return {
                    subtotal: subtotal.toFixed(2),
                    total: subtotal.toFixed(2),
                    cantidadLotes: listaLotes.length
                };
            }
        };
    })();

    /** 🌐 Vinculación externa - maneja tanto botón como parámetros directos */
    function handleSelectItem(param1, param2) {
        // Si param1 es un número, es la nueva forma (id, nombre)
        // Si param1 es un elemento, es la forma antigua (button)
        if (typeof param1 === 'number' || (!isNaN(parseInt(param1)))) {
            // Nueva forma: handleSelectItem(id, nombre)
            const id = param1;
            const nombre = param2;
            if (typeof ModalManager !== 'undefined' && ModalManager.abrirModal) {
                ModalManager.abrirModal(id, nombre);
            } else if (typeof abrirModal === 'function') {
                abrirModal(id, nombre);
            }
        } else {
            // Forma antigua: handleSelectItem(button)
            const button = param1;
            ModalManager.abrirModal(
                button.getAttribute("data-id"),
                button.getAttribute("data-nombre")
            );
        }
    }

    function cerrarModal() {
        ModalManager.cerrarModal();
    }

    function agregarLote() {
        ModalManager.agregarLote();
    }

    document.addEventListener("DOMContentLoaded", () => {
        ModalManager.cerrarModal();
    });
</script>



<!-- calcular impuestos y totales -->
<script>
    // REEMPLAZAR todo el TotalManager con esta versión corregida:
    const TotalManager = (() => {
        const subtotalEl = document.getElementById("subtotal");
        const impuestosEl = document.getElementById("impuestos");
        const totalEl = document.getElementById("total");
        const impuestosInput = document.getElementById("impuestos_reg");

        if (!subtotalEl || !impuestosEl || !totalEl || !impuestosInput) {
            return {
                actualizarTotales: () => {}
            };
        }

        function calcularSubtotal() {
            const lotes = ModalManager.obtenerLotes();
            return lotes.reduce((acc, lote) => acc + (lote.cantidad * lote.precioCompra), 0);
        }

        function calcularImpuestos(subtotal) {
            const valor = impuestosInput ? parseFloat(impuestosInput.value) || 0 : 0;
            return subtotal * (valor / 100);
        }

        function actualizarTotales() {
            const subtotal = calcularSubtotal();
            const impuestos = calcularImpuestos(subtotal);
            const total = subtotal + impuestos;

            // Actualizar DOM
            if (subtotalEl) subtotalEl.textContent = `Bs. ${subtotal.toFixed(2)}`;
            if (impuestosEl) impuestosEl.textContent = `Bs. ${impuestos.toFixed(2)}`;
            if (totalEl) totalEl.textContent = `Bs. ${total.toFixed(2)}`;

            // Actualizar campos ocultos para envío
            const subtotalField = document.getElementById('subtotal_total');
            const impuestosField = document.getElementById('impuestos_total');
            const totalField = document.getElementById('total_general');

            if (subtotalField) subtotalField.value = subtotal.toFixed(2);
            if (impuestosField) impuestosField.value = impuestos.toFixed(2);
            if (totalField) totalField.value = total.toFixed(2);

            // Actualizar campos JSON para alertas.js
            const lotes = typeof ModalManager !== 'undefined' ? ModalManager.obtenerLotes() : [];
            const totales = typeof ModalManager !== 'undefined' ? ModalManager.obtenerTotales() : {};
            
            const lotesField = document.getElementById('lotes_json');
            const totalesField = document.getElementById('totales_json');
            
            if (lotesField) lotesField.value = JSON.stringify(lotes);
            if (totalesField) totalesField.value = JSON.stringify(totales);
        }

        // Escuchar cambios en impuestos
        if (impuestosInput) {
            impuestosInput.addEventListener("input", actualizarTotales);
        }

        // Conectar con ModalManager
        function conectarConModal() {
            if (!ModalManager) return;

            // Guardar referencia a los métodos originales
            const originalAgregar = ModalManager.agregarLote;
            const originalEliminar = ModalManager.eliminarLote;

            // Sobrescribir para incluir cálculo de totales
            ModalManager.agregarLote = function() {
                const result = originalAgregar.call(ModalManager);
                actualizarTotales();
                return result;
            };

            ModalManager.eliminarLote = function(index) {
                const result = originalEliminar.call(ModalManager, index);
                actualizarTotales();
                return result;
            };
        }

        // Escuchar cambios en proveedor
        const proveedorFiltro = document.getElementById('Proveedor_filtro');
        const proveedorHidden = document.getElementById('Proveedor_reg');
        if (proveedorFiltro && proveedorHidden) {
            proveedorFiltro.addEventListener('change', function() {
                proveedorHidden.value = this.value;
            });
            // Valor inicial
            proveedorHidden.value = proveedorFiltro.value;
        }

        document.addEventListener("DOMContentLoaded", () => {
            conectarConModal();
            actualizarTotales(); // Calcular inicial
        });

        return {
            actualizarTotales
        };
    })();

    // Bloque duplicado de TotalManager eliminado para evitar cierres extra y errores de sintaxis.
</script>
<!-- envia por POST al ajax -->
<script>
    // 📤 ENVÍO DE FORMULARIO CON LOTES
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.formCompra');

        if (form) {
            form.addEventListener('submit', function(e) {
                // Validar que haya lotes agregados
                const lotes = ModalManager.obtenerLotes();

                if (lotes.length === 0) {
                    e.preventDefault();
                    Swal.fire('Advertencia', 'Debe agregar al menos un lote antes de guardar la compra', 'warning');
                    return false;
                }

                // Asegurar que los campos JSON estén actualizados una última vez
                if (typeof TotalManager !== 'undefined' && TotalManager.actualizarTotales) {
                    TotalManager.actualizarTotales();
                }

                console.log("Formulario de compra listo para enviar", {
                    lotes: lotes,
                    totales: ModalManager.obtenerTotales()
                });
            });
        }
    });
</script>
<!--  para el numero de compra de manera automatica -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputUltimaCompra = document.getElementById('ultima_campra_valor');
        const inputNumeroCompra = document.getElementById('numero_compra');

        // 🧩 Validar existencia de los elementos
        if (!inputNumeroCompra) {
            return;
        }

        const ultimaCompraValor = (inputUltimaCompra && inputUltimaCompra.value.trim()) || '';
        const añoActual = new Date().getFullYear().toString();
        let nuevoNumero = 1; // Valor inicial por defecto

        // 🧮 Intentar extraer número si existe un formato válido
        const patron = /^COMP-(\d{4})-(\d+)$/;
        const match = ultimaCompraValor.match(patron);

        if (match) {
            const añoAnterior = match[1];
            const numeroAnterior = parseInt(match[2]);

            if (añoAnterior === añoActual) {
                nuevoNumero = numeroAnterior + 1;
            } else {
                // Si cambió el año, reinicia la secuencia
                nuevoNumero = 1;
            }
        } else {
            // Si el valor está vacío, es 0 o no cumple el patrón
            if (!ultimaCompraValor || ultimaCompraValor === '0') {} else {
                // Si el valor es inválido y no está vacío, avisamos al usuario
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'El número de compra anterior no tiene un formato válido. Se iniciará desde COMP-' + añoActual + '-0001.',
                    timer: 4000,
                    showConfirmButton: false
                });
            }
        }

        // 🧾 Formatear número final
        const numeroFormateado = String(nuevoNumero).padStart(4, '0');
        const nuevoCodigo = `COMP-${añoActual}-${numeroFormateado}`;

        // Asignar solo si el campo está vacío
        if (inputNumeroCompra.value.trim() === '') {
            inputNumeroCompra.value = nuevoCodigo;
        }
    });
</script>

<script src="<?php echo SERVER_URL; ?>views/script/ajax-tabla.js"></script>
<script>
    // perminte manejar los inputs con porcentaje obligandolo a estar dentro del parametro 100%
    /* validar porcentaje de 0 a 100% */
    // AGREGAR ESTE SCRIPT EN TU FORMULARIO
    function validarPorcentaje(input) {
        let valor = parseFloat(input.value);

        // Si está vacío, dejar que required se encargue
        if (isNaN(valor)) return;

        // Forzar entre 0 y 100
        if (valor < 0) {
            input.value = 0;
        } else if (valor > 100) {
            input.value = 100;
        }

        // Actualizar totales si existe TotalManager
        if (typeof TotalManager !== 'undefined' && TotalManager.actualizarTotales) {
            TotalManager.actualizarTotales();
        }
    }

    // También validar al perder el foco
    document.addEventListener('DOMContentLoaded', function() {
        const inputImpuestos = document.getElementById('impuestos_reg');
        if (inputImpuestos) {
            inputImpuestos.addEventListener('blur', function() {
                validarPorcentaje(this);
            });
        }
    });
</script>
<!-- script para modal de activacion de lotes -->
<script>
    // ABRIR MODAL
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-activar-lote');
        if (btn) {
            const id = btn.dataset.id;
            const nombre = btn.dataset.nombre;

            const modalId = 'modalActivarLote';
            showM(modalId);

            document.getElementById('detalleLote').innerHTML = `
            <div class="litem"><ion-icon name="barcode-outline"></ion-icon><div class="f1"><div class="tc">ID Lote</div><div class="th5">#${id}</div></div></div>
            <div class="litem"><ion-icon name="medical-outline"></ion-icon><div class="f1"><div class="tc">Medicamento</div><div class="th5">${nombre}</div></div></div>
            <div class="psub mt16">Confirme que desea activar este lote. Esta acción no se puede deshacer y habilitará el medicamento para la venta.</div>
        `;

            document.getElementById('btnConfirmarActivacion').dataset.id = id;
        }

        // CERRAR MODAL CON BOTÓN (Cancelar o X)
        if (e.target.classList.contains('modal-close') || e.target.classList.contains('mcl')) {
            closeM('modalActivarLote');
        }
    });

    // CONFIRMAR EN EL MODAL
    document.addEventListener('click', (e) => {
        if (e.target.id === 'btnConfirmarActivacion') {
            const id = e.target.dataset.id;
            if (!id) return;

            Swal.fire({
                title: "¿Activar este lote?",
                text: "Al activar el lote, el stock estará disponible para la venta.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "var(--btn-default)",
                cancelButtonColor: "var(--btn-warning)",
                confirmButtonText: "Sí, activar",
                cancelButtonText: "Cancelar"
            }).then((r) => {
                if (r.isConfirmed) activarLote(id);
            });
        }
    });

    // FUNCIÓN PARA ACTIVAR LOTE (AJAX)
    async function activarLote(id) {
        Swal.fire({
            title: 'Procesando...',
            text: 'Activando lote en el sistema',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch('<?php echo SERVER_URL; ?>ajax/loteAjax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `loteAjax=active&id=${id}`
            });

            const data = await res.json();
            Swal.close();

            await Swal.fire({
                title: data.Titulo || 'Resultado',
                html: data.texto || '',
                icon: data.Tipo || 'info',
                confirmButtonColor: "var(--btn-default)"
            });

            if (data.Alerta === 'recargar' || data.Tipo === 'success') {
                closeM('modalActivarLote');
                document.querySelector('.filtro-dinamico .btn-search')?.click();
            }

        } catch (err) {
            Swal.fire("Error", "No se pudo procesar la solicitud: " + err.message, "error");
        }
    }
</script>


<!-- script que maneja el modal de agregar nuevo cliente -->
<script>
    (function() {
        // Estado interno
        let modal = null;
        let initialized = false;

        function initIfNeeded() {
            if (initialized && modal) return;
            modal = document.getElementById('modalCliente');
            if (!modal) {
                // No marcar initialized si todavía no existe, para que futuros clicks re-intenten
                return;
            }
            setupModal();
        }

        function setupModal() {
            if (!modal) return; // nada que hacer si no existe

            // Conectar botones de cierre estándar (.mcl es el usado en este modal, .close y data-close para compatibilidad)
            const closeBtns = modal.querySelectorAll('.close, .mcl, [data-close="modalCliente"]');
            closeBtns.forEach(btn => {
                // Evitar duplicar listeners si ya tiene onclick inline
                if (!btn.hasAttribute('onclick')) {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        api.cerrarModal();
                    });
                }
            });

            initialized = true;
        }

        // Funciones públicas que aseguran init
        function abrirModal() {
            initIfNeeded();
            if (!modal) {
                return;
            }
            modal.style.display = 'flex';
            // Usar el sistema estándar .mov + .open para la transición
            setTimeout(() => {
                modal.classList.add('open');
            }, 10);
        }

        function cerrarModal() {
            initIfNeeded();
            if (!modal) {
                // intento fallback: cerrar cualquier modal visible
                const visible = Array.from(document.querySelectorAll('.mov')).find(m => m.classList.contains('open'));
                if (visible) {
                    visible.classList.remove('open');
                    setTimeout(() => { visible.style.display = 'none'; }, 300);
                }
                return;
            }
            modal.classList.remove('open');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // API que exponemos
        const api = {
            abrirModal,
            cerrarModal,
            // nombres alternativos para compatibilidad
            abrirModalCliente: abrirModal,
            cerrarModalCliente: cerrarModal
        };

        // Exponer en window
        window.ModalCliente = api;

        // También crear funciones globales para onclick antiguos
        if (typeof window.abrirModal !== 'function') {
            window.abrirModal = () => {
                try {
                    window.ModalCliente.abrirModal();
                } catch (e) {}
            };
        }
        if (typeof window.cerrarModal !== 'function') {
            window.cerrarModal = () => {
                try {
                    window.ModalCliente.cerrarModal();
                } catch (e) {}
            };
        }

        // Intentar inicializar ahora si el elemento ya está en DOM
        if (document.readyState === 'loading') {
            // DOM aún no listo; initIfNeeded will attach DOMContentLoaded listener
            initIfNeeded();
        } else {
            // DOM ready
            initIfNeeded();
        }

    })();
</script>

<!-- script que maneja la busqueda de medicamentos lista de compras y envio por post -->
<script>
    // Funciones de tooltip para medicamentos
    function mostrarTooltip(e, item, row) {
        let tooltip = document.getElementById('medicamento-tooltip');
        if (!tooltip) {
            tooltip = document.createElement('div');
            tooltip.id = 'medicamento-tooltip';
            tooltip.style.cssText = `
                position: fixed;
                top: 10px;
                right: 10px;
                max-width: 300px;
                padding: 12px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.15);
                z-index: 10000;
                font-family: Arial, sans-serif;
                font-size: 12px;
            `;
            document.body.appendChild(tooltip);
        }
        
        const nombre = item.nombre || 'N/A';
        const lote = item.lote || 'N/A';
        const precio = item.precio_venta || 0;
        const stock = item.stock || 0;
        
        tooltip.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 8px;">${nombre}</div>
            <div style="margin-bottom: 4px;">Lote: ${lote}</div>
            <div style="margin-bottom: 4px;">Precio: Bs. ${precio.toFixed(2)}</div>
            <div>Stock: ${stock}</div>
        `;
        
        const rect = row.getBoundingClientRect();
        tooltip.style.top = (rect.bottom + window.scrollY + 5) + 'px';
        tooltip.style.left = (rect.left + window.scrollX) + 'px';
    }

    function ocultarTooltip() {
        const tooltip = document.getElementById('medicamento-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }

    // clase para manejar el carrito de caja
    class CajaManager {
        constructor() {
            // inicializa propiedades
            this.formVenta = document.getElementById('form-venta-caja');
            if (!this.formVenta) return;

            this.URL_MED = "<?php echo SERVER_URL ?>ajax/ventaAjax.php";
            this.cart = [];
            this.debounce = null;
            this.medicamentosCache = {};
            this.tooltipTimeout = null;

            // elementos del dom
            this.itemsHidden = this.ensureHidden('venta_items_json', 'venta_items_json');
            this.subtotalHidden = this.ensureHidden('subtotal_venta', 'subtotal_venta');
            this.totalHidden = this.ensureHidden('total_venta', 'total_venta');
            this.cambioHidden = this.ensureHidden('cambio_venta', 'cambio_venta');
            this.dineroHidden = this.ensureHidden('dinero_recibido_venta', 'dinero_recibido_venta');

            this.medSearch = this.formVenta.querySelector('.med_search');
            this.filtro_presentacion = this.$( '#filtro_presentacion');
            this.filtro_funcion = this.$( '#filtro_funcion');
            this.filtro_via = this.$( '#filtro_via');
            this.filtro_proveedor = this.$( '#filtro_proveedor');

            this.resultsContainer = this.$( '#med_search_results');
            if (!this.resultsContainer && this.medSearch) {
                this.resultsContainer = document.createElement('div');
                this.resultsContainer.id = 'med_search_results';
                this.resultsContainer.className = 'search-results-dropdown';
                this.resultsContainer.style.cssText = `
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                z-index: 1000;
                max-height: 300px;
                overflow-y: auto;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            `;
                this.medSearch.parentElement.style.position = 'relative';
                this.medSearch.parentElement.appendChild(this.resultsContainer);
            }

            this.tablaBody = this.$( '#tabla_items_venta');
            this.tablaMasVendidos = this.$( '#tabla_mas_vendidos');
            this.inputDinero = this.$( '#input_dinero_recibido');
            this.subtotalText = this.$( '#subtotal_texto');
            this.totalText = this.$( '#total_texto');
            this.cambioText = this.$( '#cambio_texto');

            // inicializa eventos
            this.init();
        }

        // metodo para seleccionar elementos dentro del formulario
        $(s) {
            return this.formVenta.querySelector(s);
        }

        // metodo para seleccionar multiples elementos
        $all(s) {
            return Array.from(this.formVenta.querySelectorAll(s));
        }

        // metodo para asegurar campos ocultos
        ensureHidden(name, id) {
            let el = this.formVenta.querySelector('input[name="' + name + '"]');
            if (!el) {
                el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                if (id) el.id = id;
                this.formVenta.appendChild(el);
            }
            return el;
        }

        // metodo para formatear dinero
        formatMoney(n) {
            return Number(n || 0).toFixed(2);
        }

        // metodo para escapar html
        escapeHtml(s) {
            if (s == null) return '';
            return String(s).replace(/[&<>"'`]/g, function(m) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": "&#39;",
                    '`': '&#96;'
                })[m];
            });
        }

        // metodo para encontrar indice de item en carrito
        findItemIndex(med_id, lote_id) {
            return this.cart.findIndex(item =>
                String(item.med_id) === String(med_id) &&
                String(item.lote_id || null) === String(lote_id || null)
            );
        }

        // metodo para renderizar el carrito
        renderCart() {
            if (!this.tablaBody) return;

            this.tablaBody.innerHTML = '';

            if (this.cart.length === 0) {
                this.tablaBody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#666;padding:12px">no hay medicamentos en la lista</td></tr>';
            } else {
                this.cart.forEach((it, i) => {
                    const tr = document.createElement('tr');
                    tr.dataset.med = it.med_id;

                    let nombreDisplay = this.escapeHtml(it.nombre);
                    if (it.lote) {
                        nombreDisplay += '<br><small style="color: #666;">' +
                            '<ion-icon name="barcode-outline"></ion-icon> ' +
                            this.escapeHtml(it.lote) +
                            (it.proveedor ? ' | ' + this.escapeHtml(it.proveedor) : '')
                        '</small>';
                    }

                    // calcular cajas y unidades restantes
                    const unidadesPorCaja = it.unidades_por_caja || 1;
                    const cajas = Math.floor(it.cantidad / unidadesPorCaja);
                    const unidadesRestantes = it.cantidad % unidadesPorCaja;

                    tr.innerHTML =
                        '<td>' +
                        '<button type="button" class="btn delete-item" data-index="' + i + '" title="eliminar" style="padding: 0; min-width: 20px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd; border-radius: 4px;">' +
                        '<ion-icon name="trash-outline" style="font-size: 20px; color: red;"></ion-icon>' +
                        '</button>' +
                        '</td>' +
                        '<td><span style="color:#1565C0;font-weight:700;">' + nombreDisplay + '</span></td>' +
                        '<td><span style="color:#00897B;font-weight:600;">' + this.escapeHtml(it.presentacion || '') + '</span></td>' +
                        '<td><input type="text" class="qty-input-unidades inp" data-index="' + i + '" value="' + unidadesRestantes + '" style="width: 60px; text-align: center; color:#6A1B9A;font-weight:700;"></td>' +
                        '<td><input type="text" class="qty-input-cajas inp" data-index="' + i + '" value="' + cajas + '" style="width: 60px; text-align: center; color:#00695C;font-weight:700;"></td>' +
                        '<td><span style="color:#00BFA5;font-weight:700;">' + this.formatMoney(it.precio * (it.unidades_por_caja || 1)) + '</span></td>' +
                        '<td><span style="color:#00BFA5;font-weight:700;">' + this.formatMoney(it.precio) + '</span></td>' +
                        '<td><input type="number" class="descuento-input inp" data-index="' + i + '" value="' + (it.descuento || 0) + '" min="0" max="100" step="0.01" style="width: 80px; text-align: center; color:#7B1FA2;font-weight:700;"></td>' +
                        '<td class="' + ((it.descuento || 0) > 0 ? 'discounted-subtotal' : '') + '"><span style="color:#2E7D32;font-weight:700;">' + this.formatMoney((it.precio * it.cantidad) - (it.descuento || 0)) + '</span></td>';

                    this.tablaBody.appendChild(tr);
                });
            }

            this.itemsHidden.value = JSON.stringify(this.cart.map(i => {
                const upc = i.unidades_por_caja || 1;
                return {
                    med_id: i.med_id,
                    lote_id: i.lote_id || null,
                    cantidad: i.cantidad,
                    unidades: i.cantidad % upc,
                    cajas: Math.floor(i.cantidad / upc),
                    unidades_por_caja: upc,
                    precio: Number(i.precio),
                    descuento: Number(i.descuento || 0),
                    subtotal: Number(((i.precio * i.cantidad) - (i.descuento || 0)).toFixed(2))
                };
            }));

            this.updateTotals();
            this.attachQtyEvents();
        }

        // metodo para eliminar item del carrito
        eliminarItem(index) {
            if (index < 0 || index >= this.cart.length) return;

            Swal.fire({
                title: 'eliminar este medicamento?',
                text: 'esta accion no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'si, eliminar',
                cancelButtonText: 'cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.cart.splice(index, 1);
                    this.renderCart();
                }
            });
        }

        // metodo para adjuntar eventos a inputs de cantidad
        attachQtyEvents() {
            // eventos para unidades
            this.$all('.qty-input-unidades').forEach(i => {
                i.oninput = function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                };
                i.onchange = function() {
                    // Usamos la versión inteligente: permite ingresar cantidad total en el campo Unidades
                    this.manager.setSmartQtyUnidadesByIndex(parseInt(this.dataset.index), parseInt(this.value) || 0);
                };
                i.manager = this; // referencia a la instancia
            });

            // eventos para cajas
            this.$all('.qty-input-cajas').forEach(i => {
                i.oninput = function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                };
                i.onchange = function() {
                    this.manager.setQtyCajasByIndex(parseInt(this.dataset.index), parseInt(this.value) || 0);
                };
                i.manager = this;
            });

            // eventos para descuento
            this.$all('.descuento-input').forEach(i => {
                i.oninput = function() {
                    this.manager.clampDescuento(this);
                };
                i.onblur = function() {
                    this.manager.validarDescuento(this);
                    const index = parseInt(this.dataset.index);
                    const descuento = parseFloat(this.value) || 0;
                    this.manager.setDescuentoByIndex(index, descuento);
                };
                i.manager = this;
            });

            // eventos para botones de eliminar
            this.$all('.delete-item').forEach(btn => {
                btn.onclick = function() {
                    const index = parseInt(this.dataset.index);
                    this.manager.eliminarItem(index);
                };
                btn.manager = this;
            });
        }

        // metodo para cambiar cantidad de unidades por indice
        changeQtyUnidadesByIndex(idx, delta) {
            if (idx < 0 || idx >= this.cart.length) return;

            const item = this.cart[idx];
            const unidadesPorCaja = item.unidades_por_caja || 1;
            const unidadesActuales = item.cantidad % unidadesPorCaja;
            const cajasActuales = Math.floor(item.cantidad / unidadesPorCaja);

            let nuevasUnidades = unidadesActuales + delta;

            // validar limites de unidades
            if (nuevasUnidades < 0) {
                nuevasUnidades = 0;
            } else if (nuevasUnidades >= unidadesPorCaja) {
                nuevasUnidades = unidadesPorCaja - 1;
            }

            const nuevaCantidadTotal = (cajasActuales * unidadesPorCaja) + nuevasUnidades;

            // validar stock total
            if (item.stock != null && nuevaCantidadTotal > item.stock) {
                Swal.fire({
                    title: 'sin stock suficiente',
                    html: `<p><strong>${this.escapeHtml(item.nombre)}</strong></p>
                       ${item.lote ? '<p>lote: <strong>' + this.escapeHtml(item.lote) + '</strong></p>' : ''}
                       <p>stock disponible: <strong>${item.stock}</strong> unidades</p>
                       <p>intentando agregar: <strong>${nuevaCantidadTotal}</strong> unidades</p>`,
                    icon: 'warning',
                    confirmButtonText: 'entendido'
                });
                return;
            }

            item.cantidad = nuevaCantidadTotal;
            this.renderCart();
        }

        // metodo para cambiar cantidad de cajas por indice
        changeQtyCajasByIndex(idx, delta) {
            if (idx < 0 || idx >= this.cart.length) return;

            const item = this.cart[idx];
            const unidadesPorCaja = item.unidades_por_caja || 1;
            const unidadesActuales = item.cantidad % unidadesPorCaja;
            const cajasActuales = Math.floor(item.cantidad / unidadesPorCaja);

            let nuevasCajas = cajasActuales + delta;

            // validar que no sea negativo
            if (nuevasCajas < 0) {
                nuevasCajas = 0;
            }

            const nuevaCantidadTotal = (nuevasCajas * unidadesPorCaja) + unidadesActuales;

            // validar stock total
            if (item.stock != null && nuevaCantidadTotal > item.stock) {
                // calcular cuantas cajas completas y unidades restantes se pueden agregar
                const maxCajasCompletas = Math.floor(item.stock / unidadesPorCaja);
                const unidadesRestantes = item.stock % unidadesPorCaja;

                // mostrar alerta con informacion detallada
                Swal.fire({
                    title: 'sin stock suficiente',
                    html: `<p><strong>${this.escapeHtml(item.nombre)}</strong></p>
                       ${item.lote ? '<p>lote: <strong>' + this.escapeHtml(item.lote) + '</strong></p>' : ''}
                       <p>stock disponible: <strong>${item.stock}</strong> unidades</p>
                       <p>equivalente a: <strong>${maxCajasCompletas}</strong> cajas y <strong>${unidadesRestantes}</strong> unidades</p>
                       <p>intentando agregar: <strong>${nuevasCajas}</strong> cajas y <strong>${unidadesActuales}</strong> unidades</p>`,
                    icon: 'warning',
                    confirmButtonText: 'entendido'
                });
                return;
            }

            item.cantidad = nuevaCantidadTotal;
            this.renderCart();
        }

        // metodo para establecer cantidad de unidades por indice
        // NOTA: Este método ahora delega a la versión inteligente para evitar que se "borren"
        // cantidades grandes que el usuario ingresa en el campo Unidades.
        setQtyUnidadesByIndex(idx, val) {
            // Redirigimos al nuevo comportamiento inteligente
            this.setSmartQtyUnidadesByIndex(idx, val);
        }

        /**
         * Versión inteligente para el campo Unidades.
         * - Si el usuario ingresa un número >= unidades_por_caja, lo interpreta como "cantidad total deseada".
         * - Si ingresa un número pequeño (< upc), se comporta como antes (reemplaza solo la parte de unidades).
         * - El input de Cajas permanece 100% sin cambios.
         */
        setSmartQtyUnidadesByIndex(idx, enteredVal) {
            if (idx < 0 || idx >= this.cart.length) return;

            const item = this.cart[idx];
            const unidadesPorCaja = item.unidades_por_caja || 1;

            let finalCantidad;

            if (enteredVal >= unidadesPorCaja) {
                // Usuario ingresó cantidad total directamente (ej: 25 cuando upc=10)
                finalCantidad = enteredVal;
            } else {
                // Comportamiento tradicional: mantener las cajas actuales + nuevas unidades
                const cajasActuales = Math.floor(item.cantidad / unidadesPorCaja);
                finalCantidad = (cajasActuales * unidadesPorCaja) + enteredVal;
            }

            // Validación de stock (igual que en el resto del sistema)
            if (item.stock != null && finalCantidad > item.stock) {
                Swal.fire({
                    title: 'sin stock suficiente',
                    html: `<p><strong>${this.escapeHtml(item.nombre)}</strong></p>
                       ${item.lote ? '<p>lote: <strong>' + this.escapeHtml(item.lote) + '</strong></p>' : ''}
                       <p>stock disponible: <strong>${item.stock}</strong> unidades</p>
                       <p>intentando agregar: <strong>${finalCantidad}</strong> unidades</p>`,
                    icon: 'warning',
                    confirmButtonText: 'entendido'
                });
                return;
            }

            // Validación de negativo (seguridad adicional)
            if (finalCantidad < 0) finalCantidad = 0;

            item.cantidad = finalCantidad;
            this.renderCart();
        }

        // metodo para establecer cantidad de cajas por indice
        setQtyCajasByIndex(idx, val) {
            if (idx < 0 || idx >= this.cart.length) return;

            const item = this.cart[idx];
            const unidadesPorCaja = item.unidades_por_caja || 1;
            const unidadesActuales = item.cantidad % unidadesPorCaja;

            // validar que no sea negativo
            if (val < 0) val = 0;

            const nuevaCantidadTotal = (val * unidadesPorCaja) + unidadesActuales;

            // validar stock total
            if (item.stock != null && nuevaCantidadTotal > item.stock) {
                // calcular cuantas cajas completas y unidades restantes se pueden agregar
                const maxCajasCompletas = Math.floor(item.stock / unidadesPorCaja);
                const unidadesRestantes = item.stock % unidadesPorCaja;

                // mostrar alerta con informacion detallada
                Swal.fire({
                    title: 'sin stock suficiente',
                    html: `<p><strong>${this.escapeHtml(item.nombre)}</strong></p>
                       ${item.lote ? '<p>lote: <strong>' + this.escapeHtml(item.lote) + '</strong></p>' : ''}
                       <p>stock disponible: <strong>${item.stock}</strong> unidades</p>
                       <p>equivalente a: <strong>${maxCajasCompletas}</strong> cajas y <strong>${unidadesRestantes}</strong> unidades</p>
                       <p>intentando agregar: <strong>${val}</strong> cajas y <strong>${unidadesActuales}</strong> unidades</p>`,
                    icon: 'warning',
                    confirmButtonText: 'entendido'
                });
                return;
            }

            item.cantidad = nuevaCantidadTotal;
            this.renderCart();
        }

        // metodo para establecer cantidad por indice
        setQtyByIndex(idx, val) {
            if (idx < 0 || idx >= this.cart.length) return;

            const item = this.cart[idx];

            if (val <= 0) {
                Swal.fire({
                    title: 'cantidad 0',
                    text: 'eliminar este medicamento?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'si, eliminar',
                    cancelButtonText: 'cancelar'
                }).then(r => {
                    if (r.isConfirmed) {
                        this.cart.splice(idx, 1);
                        this.renderCart();
                    } else {
                        item.cantidad = 1;
                        this.renderCart();
                    }
                });
                return;
            }

            if (item.stock != null && val > item.stock) {
                Swal.fire({
                    title: 'sin stock suficiente',
                    html: `<p><strong>${this.escapeHtml(item.nombre)}</strong></p>
                   ${item.lote ? '<p>lote: <strong>' + this.escapeHtml(item.lote) + '</strong></p>' : ''}
                   <p>stock disponible: <strong>${item.stock}</strong> unidades</p>
                   <p>cantidad ingresada: <strong>${val}</strong> unidades</p>`,
                    icon: 'warning',
                    confirmButtonText: 'entendido'
                });
                item.cantidad = item.stock > 0 ? item.stock : 1;
                this.renderCart();
                return;
            }

            // Corregido: usamos 'val' directamente (esta función espera cantidad total en unidades)
            item.cantidad = val;
            this.renderCart();
        }

        // metodo para clamp descuento (similar a clampMargen)
        clampDescuento(input) {
            let value = input.value;
            
            // Permitir vacío para que el usuario pueda borrar todo
            if (value === "") return;

            // Eliminar cualquier caracter que no sea número o punto
            value = value.replace(/[^0-9.]/g, "");

            // Asegurar solo un punto decimal
            const parts = value.split(".");
            if (parts.length > 2) {
                value = parts[0] + "." + parts.slice(1).join("").replace(/\./g, "");
            }

            // Limitar a 2 decimales
            if (parts.length > 1 && parts[1].length > 2) {
                value = parts[0] + "." + parts[1].substring(0, 2);
            }

            // Evitar que el 0 inicial interfiera con otros números, a menos que sea 0.
            if (value.length > 1 && value.startsWith("0") && value[1] !== ".") {
                value = value.substring(1);
            }

            // No superar el límite de 100
            if (parseFloat(value) > 100) {
                value = "100";
            }

            if (input.value !== value) {
                input.value = value;
            }
        }

        // metodo para validar descuento
        validarDescuento(input) {
            let valor = parseFloat(input.value);
            if (isNaN(valor) || valor < 0) {
                input.value = "0.00";
            } else {
                if (valor > 100) valor = 100;
                input.value = valor.toFixed(2);
            }
        }

        // metodo para establecer descuento por indice
        setDescuentoByIndex(idx, val) {
            if (idx < 0 || idx >= this.cart.length) return;

            const item = this.cart[idx];
            let descuento = parseFloat(val);
            if (isNaN(descuento) || descuento < 0) {
                descuento = 0;
            } else if (descuento > 100) {
                descuento = 100;
            }
            item.descuento = descuento;
            this.renderCart();
        }

        // metodo para actualizar totales
        updateTotals() {
            const subtotal = this.cart.reduce((s, i) => s + ((i.precio * i.cantidad) - (i.descuento || 0)), 0);
            const total = subtotal;
            this.subtotalHidden.value = subtotal.toFixed(2);
            this.totalHidden.value = total.toFixed(2);
            if (this.subtotalText) this.subtotalText.textContent = 'bs. ' + this.formatMoney(subtotal);
            if (this.totalText) this.totalText.textContent = 'bs. ' + this.formatMoney(total);
            const dinero = Number(this.inputDinero ? this.inputDinero.value : 0);
            const cambio = dinero - total;
            this.cambioHidden.value = (cambio > 0 ? cambio : 0).toFixed(2);
            if (this.cambioText) this.cambioText.textContent = (isNaN(cambio) ? '0.00' : this.formatMoney(Math.max(0, cambio)));
            if (this.dineroHidden) this.dineroHidden.value = dinero;
        }

        // metodo para agregar item al carrito
        addItem(m) {
            const idx = this.findItemIndex(m.med_id, m.lote_id);

            if (idx !== -1) {
                const ex = this.cart[idx];
                const nuevaCantidad = ex.cantidad + 1;

                if (m.stock != null && nuevaCantidad > m.stock) {
                    Swal.fire({
                        title: 'sin stock suficiente',
                        html: `<p><strong>${this.escapeHtml(m.nombre)}</strong></p>
                       ${m.lote ? '<p>lote: <strong>' + this.escapeHtml(m.lote) + '</strong></p>' : ''}
                       <p>stock disponible: <strong>${m.stock}</strong> unidades</p>
                       <p>ya tienes <strong>${ex.cantidad}</strong> en el carrito</p>`,
                        icon: 'warning',
                        confirmButtonText: 'entendido'
                    });
                    return;
                }

                ex.cantidad = nuevaCantidad;
            } else {
                if (m.stock != null && m.stock <= 0) {
                    Swal.fire({
                        title: 'sin stock',
                        html: `<p><strong>${this.escapeHtml(m.nombre)}</strong></p>
                       ${m.lote ? '<p>lote: <strong>' + this.escapeHtml(m.lote) + '</strong></p>' : ''}
                       <p>este lote no tiene stock disponible</p>`,
                        icon: 'error',
                        confirmButtonText: 'entendido'
                    });
                    return;
                }

                this.cart.push({
                    med_id: m.med_id,
                    lote_id: m.lote_id || null,
                    lote: m.lote || null,
                    nombre: m.nombre,
                    presentacion: m.presentacion,
                    proveedor: m.proveedor,
                    precio: parseFloat(m.precio) || 0,
                    cantidad: 0,
                    descuento: 0,
                    stock: m.stock != null ? Number(m.stock) : null,
                    unidades_por_caja: m.unidades_por_caja || 1
                });
            }
            this.renderCart();
        }

        // metodo para buscar medicamentos
        doSearch(term) {
            if (!term || term.trim().length < 1) {
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '';
                    this.resultsContainer.style.display = 'none';
                }
                return;
            }

            const cacheKey = JSON.stringify({
                term,
                presentacion: this.filtro_presentacion ? this.filtro_presentacion.dataset.selectedId : null,
                funcion: this.filtro_funcion ? this.filtro_funcion.dataset.selectedId : null,
                via: this.filtro_via ? this.filtro_via.dataset.selectedId : null,
                proveedor: this.filtro_proveedor ? this.filtro_proveedor.dataset.selectedId : null
            });

            if (this.medicamentosCache[cacheKey]) {
                this.renderResults(this.medicamentosCache[cacheKey]);
                return;
            }

            const body = new URLSearchParams();
            body.append('ventaAjax', 'buscar_agrupado');
            body.append('termino', term);
            if (this.filtro_presentacion && this.filtro_presentacion.dataset.selectedId) body.append('presentacion', this.filtro_presentacion.dataset.selectedId);
            if (this.filtro_funcion && this.filtro_funcion.dataset.selectedId) body.append('funcion', this.filtro_funcion.dataset.selectedId);
            if (this.filtro_via && this.filtro_via.dataset.selectedId) body.append('via', this.filtro_via.dataset.selectedId);
            if (this.filtro_proveedor && this.filtro_proveedor.dataset.selectedId) body.append('proveedor', this.filtro_proveedor.dataset.selectedId);

            fetch(this.URL_MED, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            }).then(r => r.json()).then(json => {
                this.medicamentosCache[cacheKey] = json || [];
                this.renderResults(json || []);
            }).catch(err => {
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '<div class="search-results-item no-results">error en la busqueda</div>';
                    this.resultsContainer.style.display = 'block';
                }
            });
        }

        // metodo para renderizar resultados de busqueda
        renderResults(items) {
            if (!this.resultsContainer) return;

            if (!items || items.length === 0) {
                this.resultsContainer.innerHTML = `
                    <div class="table-popup-wrap">
                        <div class="table-popup open">
                            <div class="tp-scroll">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="tp-empty" colspan="8">no se encontraron resultados</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                this.resultsContainer.style.display = 'block';
                return;
            }

const tableHtml = `
                <div class="table-popup-wrap">
                    <div class="table-popup open">
                        <div class="tp-scroll">
                            <table style="font-size:11px; width:100%;">
                                <thead>
                                    <tr>
                                        <th style="width:5%;">n° lote</th>
                                        <th style="width:35%;">descripcion</th>
                                        <th style="width:12%;">proveedor</th>
                                        <th>codigo barras</th>
                                        <th>c. unidad actual</th>
                                        <th>Precio unitario</th>
                                        <th>Precio caja</th>
                                    </tr>
                                </thead>
                                <tbody style="font-size:18px;">
                                    ${items.map((it, index) => {
                                        const nombre = this.escapeHtml(it.nombre || '');
                                        const lote = this.escapeHtml(it.lm_numero_lote || '');
                                        const presentacion = this.escapeHtml(it.presentacion || 'sin presentacion');
                                        const proveedor = this.escapeHtml(it.proveedor || 'sin proveedor');
                                        const precio = this.formatMoney(it.precio_venta || 0);
                                        const stock = Number(it.stock || 0);
                                        const blister = Number(it.lm_cant_blister || 1);
                                        const unidad = Number(it.lm_cant_unidad || 1);
                                        const unidadesPorCaja = blister * unidad;

                                        let diasVenc = '';
                                        let fechaVencimientoDisplay = '';
                                        if (it.fecha_vencimiento) {
                                            const hoy = new Date();
                                            const vence = new Date(it.fecha_vencimiento);
                                            const diff = Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24));

                                            const dia = String(vence.getDate()).padStart(2, '0');
                                            const mes = String(vence.getMonth() + 1).padStart(2, '0');
                                            const anio = vence.getFullYear();
                                            fechaVencimientoDisplay = `${dia}/${mes}/${anio}`;

                                            if (diff < 0) {
                                                diasVenc = '<span style="color: red; font-weight: bold;">vencido</span>';
                                            } else if (diff <= 30) {
                                                diasVenc = `<span style="color: orange; font-weight: bold;">${diff}d</span>`;
                                            } else if (diff <= 90) {
                                                diasVenc = `<span style="color: #ff9800;">${diff}d</span>`;
                                            }
                                        }

                                        const stockText = stock > 0 ?
                                            `<span style="color: #4caf50;">${stock}</span>` :
                                            '<span style="color: red;">sin stock</span>';

                                        const sinStock = stock <= 0 ? 'sin-stock' : '';

const descripcion = `${nombre} - ${presentacion}`;
                                        const precioVentaUnitario = this.formatMoney(it.precio_venta || 0);
                                        const precioVentaCaja = this.formatMoney((it.precio_venta || 0) * unidadesPorCaja);

                                         return `
                                             <tr class="tr-cart ${sinStock}" data-med-id="${it.med_id}" data-lote-id="${it.lm_id || ''}" data-nombre="${this.escapeHtml(it.nombre)}" data-presentacion="${this.escapeHtml(it.presentacion)}" data-proveedor="${this.escapeHtml(it.proveedor)}" data-precio="${it.precio_venta || 0}" data-stock="${stock}" data-unidades-caja="${unidadesPorCaja}" data-lote="${this.escapeHtml(it.lm_numero_lote || '')}">
<td style="font-size:11px;"><span style="color:#7C4DFF;font-weight:700;">${lote}</span></td>
                                                  <td style="font-size:15px;" class="tdp" style="font-size:13px;"><span style="color:#1565C0;font-weight:600;">${nombre}</span> <span style="color:#546E7A;">- ${presentacion}</span></td>
<td style="font-size:13px;"><span style="color:#00897B;font-weight:600;">${proveedor}</span></td>
                                                <td style="font-size:18px;"><span style="color:#455A64;font-family:monospace;">${this.escapeHtml(it.med_codigo_barras || 'n/a')}</span></td>
                                                <td style="font-size:18px;">${stockText}</td>
                                                <td style="font-size:18px;"><span style="color:#0097A7;font-weight:700;">bs. ${precioVentaUnitario}</span></td>
                                                <td style="font-size:18px;"><span style="color:#03e1ff;font-weight:700;">bs. ${precioVentaCaja}</span></td>
                                             </tr>
                                         `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            this.resultsContainer.innerHTML = tableHtml;

            // agregar eventos de click a las filas de la tabla
            this.resultsContainer.querySelectorAll('.table-popup tr.tr-cart').forEach(row => {
                row.addEventListener('click', () => {
                    const data = row.dataset;
                    this.addItem({
                        med_id: data.medId,
                        lote_id: data.loteId || null,
                        lote: data.lote || null,
                        nombre: data.nombre,
                        presentacion: data.presentacion,
                        proveedor: data.proveedor,
                        precio: parseFloat(data.precio || 0),
                        stock: Number(data.stock || 0),
                        unidades_por_caja: Number(data.unidadesCaja || 1)
                    });
                    this.resultsContainer.innerHTML = '';
                    this.resultsContainer.style.display = 'none';
                    if (this.medSearch) this.medSearch.value = '';
                });
            });

            this.resultsContainer.style.display = 'block';
        }

        // metodo para realizar busqueda
        performSearch() {
            const term = this.medSearch.value.trim();
            if (term.length === 0) {
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '';
                    this.resultsContainer.style.display = 'none';
                }
                return;
            }
            this.doSearch(term);
        }

        // metodo para cargar medicamentos mas vendidos
        loadMostSold() {
            const body = new URLSearchParams();
            body.append('ventaAjax', 'mas_vendidos');
            body.append('limit', '5');

            fetch(this.URL_MED, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            }).then(r => r.json()).then(json => {
                if (!this.tablaMasVendidos) return;

                this.tablaMasVendidos.innerHTML = '';
                (json || []).forEach((it, i) => {
                    const stock = Number(it.stock || 0);
                    const stockClass = stock <= 0 ? 'sin-stock' : '';
                    const stockText = stock > 0 ? `(stock: ${stock})` : '<span style="color: red;">(sin stock)</span>';

                    const tr = document.createElement('tr');
                    tr.className = stockClass;
                    tr.dataset.id = it.med_id;
                    tr.innerHTML = `
                    <td>${i + 1}</td>
                    <td>
                        <strong>${this.escapeHtml(it.nombre)}</strong><br>
                        <small style="color: #666;">lote: ${it.lote || 'n/a'} | ${it.proveedor || 'sin proveedor'}</small>
                    </td>
                    <td>bs. ${this.formatMoney(it.precio_venta)} ${stockText}</td>
                    <td>
                        <button type="button"
                           class="btn caja btn-add"
                           data-id="${it.med_id}"
                           data-nombre="${this.escapeHtml(it.nombre)}"
                           data-lote-id="${it.lote_id}"
                           data-lote="${it.lote}"
                           data-presentacion="${it.presentacion}"
                           data-proveedor="${it.proveedor}"
                           data-precio="${it.precio_venta}"
                           data-stock="${stock}"
                           data-unidades-por-caja="${Number(it.lm_cant_blister || 1) * Number(it.lm_cant_unidad || 1)}">
                            agregar
                        </button>
                    </td>
                    `;

                    tr.addEventListener('mouseenter', (e) => {
                        clearTimeout(this.tooltipTimeout);
                        this.tooltipTimeout = setTimeout(() => mostrarTooltip(e, it, tr), 3000);
                    });
                    tr.addEventListener('mousemove', (e) => {
                        clearTimeout(this.tooltipTimeout);
                        this.tooltipTimeout = setTimeout(() => mostrarTooltip(e, it, tr), 3000);
                    });
                    tr.addEventListener('mouseleave', () => {
                        clearTimeout(this.tooltipTimeout);
                        ocultarTooltip();
                    });

                    this.tablaMasVendidos.appendChild(tr);
                });
            });

            // adjuntar event listener a la tabla para botones dinamicos
            if (!this.tablaMasVendidos.dataset.hasListener) {
                this.tablaMasVendidos.addEventListener('click', e => {
                    if (e.target.classList.contains('btn-add')) {
                        e.preventDefault();
                        const el = e.target;
                        const stock = Number(el.dataset.stock || 0);

                        this.addItem({
                            med_id: el.dataset.id,
                            lote_id: el.dataset.loteId || null,
                            lote: el.dataset.lote || null,
                            nombre: el.dataset.nombre,
                            presentacion: el.dataset.presentacion || '',
                            proveedor: el.dataset.proveedor || '',
                            precio: parseFloat(el.dataset.precio || 0),
                            stock: stock,
                            unidades_por_caja: parseInt(el.dataset.unidadesPorCaja || 1)
                        });
                        ocultarTooltip();
                    }
                });
                this.tablaMasVendidos.dataset.hasListener = 'true';
            }
        }

        // metodo para inicializar
        init() {
            if (this.medSearch) {
                this.medSearch.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.performSearch();
                    }
                });

                this.medSearch.addEventListener('focus', function() {
                    if (this.value.trim().length > 0 && this.manager.resultsContainer && this.manager.resultsContainer.innerHTML) {
                        this.manager.resultsContainer.style.display = 'block';
                    }
                });
                this.medSearch.manager = this;
            }

            const btnBuscarMed = this.formVenta.querySelector('.btn_buscar_med');
            if (btnBuscarMed) {
                btnBuscarMed.addEventListener('click', () => this.performSearch());
            }

            [this.filtro_presentacion, this.filtro_funcion, this.filtro_via].forEach(input => {
                if (input) input.addEventListener('input', () => {
                    // Solo buscar medicamentos si el input de busqueda tiene valor
                    // y el dropdown no está siendo usado para seleccionar
                    if (this.medSearch && this.medSearch.value && !this.medSearch.dataset.isDropdown) this.doSearch(this.medSearch.value);
                });
            });
            
            if (this.filtro_proveedor) {
                this.filtro_proveedor.addEventListener('input', () => {
                    if (this.medSearch && this.medSearch.value && !this.medSearch.dataset.isDropdown) this.doSearch(this.medSearch.value);
                });
            }

            // limpiar cache cada 5 minutos
            setInterval(() => {
                for (const key in this.medicamentosCache) {
                    delete this.medicamentosCache[key];
                }
            }, 300000);

            if (this.inputDinero) this.inputDinero.addEventListener('input', () => this.updateTotals());

            this.formVenta.addEventListener('submit', e => {
                if (this.cart.length === 0) {
                    e.preventDefault();
                    Swal.fire('carrito vacio', 'agrega al menos un medicamento para realizar la venta.', 'warning');
                    return;
                }

                this.updateTotals();
            });

            this.loadMostSold();
            this.renderCart();

            // prevenir submit del formulario al presionar enter en inputs
            this.formVenta.addEventListener('keydown', e => {
                if (e.key === 'Enter' && e.target.tagName === 'INPUT' && e.target.type !== 'submit') {
                    e.preventDefault();
                }
            });

            document.addEventListener('click', e => {
                if (this.resultsContainer &&
                    !this.resultsContainer.contains(e.target) &&
                    e.target !== this.medSearch) {
                    this.resultsContainer.style.display = 'none';
                }
            });

            // exponer metodos globalmente
            window.VentaCaja = {
                addItem: (m) => this.addItem(m),
                cart: this.cart,
                renderCart: () => this.renderCart(),
                updateTotals: () => this.updateTotals()
            };
        }
    }

    // clase para manejar la cotizacion
    class CotizarManager {
        constructor() {
            // inicializa propiedades
            this.medSearchQuote = document.querySelector('.med_search_quote');
            this.btnBuscarQuote = document.querySelector('.btn_buscar_med_quote');
            this.filtroProveedorQuote = document.getElementById('filtro_proveedor_quote');
            this.resultsContainer = null;
            this.debounce = null;

            if (!this.medSearchQuote) return;

            // crear contenedor de resultados
            this.resultsContainer = document.createElement('div');
            this.resultsContainer.id = 'quote_search_results';
            this.resultsContainer.className = 'search-results-dropdown';
            this.resultsContainer.style.cssText = `
            display: none;
            position: fixed;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            color: var(--text-primary);
            box-shadow: 0 4px 6px var(--shadow-md);
        `;
            document.body.appendChild(this.resultsContainer);

            // posicionar debajo del input
            this.updatePosition();

            // inicializa eventos
            this.init();
        }

        // metodo para escapar html
        escapeHtml(s) {
            if (s == null) return '';
            return String(s).replace(/[&<>"'`]/g, function(m) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": "&#39;",
                    '`': '&#96;'
                })[m];
            });
        }

        // metodo para buscar
        doSearch(term) {
            if (!term || term.trim().length < 1) {
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '';
                    this.resultsContainer.style.display = 'none';
                }
                return;
            }

            const body = new URLSearchParams();
            body.append('ventaAjax', 'buscar');
            body.append('termino', term);
            body.append('proveedor', this.filtroProveedorQuote ? this.filtroProveedorQuote.dataset.selectedId : '');
            // otros filtros vacios para cotizacion

            fetch('<?php echo SERVER_URL ?>ajax/ventaAjax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            }).then(r => r.json()).then(json => {
                this.renderResults(json || []);
            }).catch(err => {
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '<div class="search-results-item no-results">error en la busqueda</div>';
                    this.resultsContainer.style.display = 'block';
                }
            });
        }

        // metodo para renderizar resultados
        renderResults(items) {
            if (!this.resultsContainer) return;

            if (!items || items.length === 0) {
                this.resultsContainer.innerHTML = '<div class="txctr tc">no se encontraron resultados</div>';
                this.updatePosition();
                this.resultsContainer.style.display = 'block';
                return;
            }

            const tableHtml = `
                <div class="tw">
                    <table>
                        <thead>
                            <tr style="background: var(--bg-secondary);">
                                <th style="width: 18%">n° lote</th>
                                <th>medicamento</th>
                                <th style="width: 18%">p. unitario</th>
                                <th style="width: 18%">p. caja</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map((it) => {
                                const nombre = this.escapeHtml(it.nombre || '');
                                const lote = this.escapeHtml(it.lm_numero_lote || '');
                                const presentacion = this.escapeHtml(it.presentacion || 'sin presentacion');
                                const precioUnitario = this.formatMoney(it.precio_venta || 0);
                                const unidadesPorCaja = (it.lm_cant_blister || 1) * (it.lm_cant_unidad || 1);
                                const precioCaja = this.formatMoney((it.precio_venta || 0) * unidadesPorCaja);

                                const descripcion = `${nombre} - ${presentacion}`;

                                return `
                                    <tr>
                                        <td>${lote}</td>
                                        <td>${descripcion}</td>
                                        <td>bs. ${precioUnitario}</td>
                                        <td>bs. ${precioCaja}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            `;

            this.resultsContainer.innerHTML = tableHtml;
            this.updatePosition();
            this.resultsContainer.style.display = 'block';
        }

        // metodo para formatear dinero
        formatMoney(n) {
            return Number(n || 0).toFixed(2);
        }

        // metodo para actualizar posicion
        updatePosition() {
            if (!this.medSearchQuote) return;
            const rect = this.medSearchQuote.getBoundingClientRect();
            this.resultsContainer.style.top = (rect.bottom + window.scrollY) + 'px';
            this.resultsContainer.style.left = (rect.left + window.scrollX) + 'px';
            this.resultsContainer.style.width = rect.width + 'px';
        }

        // metodo para inicializar
        init() {
            if (this.medSearchQuote) {
                this.medSearchQuote.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.performSearch();
                    }
                });

                this.medSearchQuote.addEventListener('focus', () => {
                    if (this.medSearchQuote.value.trim().length > 0 && this.resultsContainer && this.resultsContainer.innerHTML) {
                        this.resultsContainer.style.display = 'block';
                    }
                });
            }

            if (this.btnBuscarQuote) {
                this.btnBuscarQuote.addEventListener('click', () => this.performSearch());
            }

            if (this.filtroProveedorQuote) {
                this.filtroProveedorQuote.addEventListener('input', () => {
                    if (this.medSearchQuote && this.medSearchQuote.value && !this.medSearchQuote.dataset.isDropdown) this.doSearch(this.medSearchQuote.value);
                });
            }

            // cerrar resultados al hacer click fuera
            document.addEventListener('click', e => {
                if (this.resultsContainer &&
                    !this.resultsContainer.contains(e.target) &&
                    e.target !== this.medSearchQuote) {
                    this.resultsContainer.style.display = 'none';
                }
            });
        }

        // metodo para realizar busqueda
        performSearch() {
            const term = this.medSearchQuote.value.trim();
            if (term.length === 0) {
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = '';
                    this.resultsContainer.style.display = 'none';
                }
                return;
            }
            this.doSearch(term);
        }
}

    class ProviderSearchManager {
        constructor(inputId, resultsId, tabla = 'proveedores', campos = ['pr_id', 'pr_razon_social', 'pr_nit']) {
            this.input = document.getElementById(inputId);
            this.resultsContainer = document.getElementById(resultsId);
            this.tabla = tabla;
            this.campos = campos;
            this.debounce = null;
            this.init();
        }
        
        escapeHtml(text) {
            return String(text).replace(/[&<>"'`]/g, m => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[m]));
        }
        
        async search(term) {
            if (term.length < 2) { this.hide(); return; }
            try {
                const body = new URLSearchParams();
                body.append('ventaAjax', 'select_v2');
                body.append('tabla', this.tabla);
                body.append('campos', JSON.stringify(this.campos));
                body.append('termino', term);
                
                const response = await fetch('<?php echo SERVER_URL ?>ajax/ventaAjax.php', {
                    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: body.toString()
                });
                const providers = await response.json();
                this.renderResults(providers);
            } catch (err) { this.renderResults([]); }
        }
        
        renderResults(items) {
            if (!this.resultsContainer) return;
            if (!items.length) {
                this.resultsContainer.innerHTML = '<div class="search-results-item no-results">No se encontraron resultados</div>';
            } else {
                this.resultsContainer.innerHTML = items.map(item => {
                    const id = item[this.campos[0]];
                    const name = item[this.campos[1]] || item[this.campos[1].replace('_id', '_nombre')];
                    const extra = item[this.campos[2]] || '';
                    return `
                        <div class="search-results-item" data-id="${id}" data-name="${this.escapeHtml(name)}" style="cursor: pointer; padding: 8px; border-bottom: 1px solid var(--border-light, #eee);">
                            <div><strong>${this.escapeHtml(name)}</strong></div>
                            ${extra ? `<small style="color: var(--text-secondary, #666);">${this.escapeHtml(extra)}</small>` : ''}
                        </div>
                    `;
                }).join('');
                this.attachListeners();
            }
            this.applyStyles();
            this.show();
        }
        
        applyStyles() {
            if (!this.resultsContainer) return;
            this.resultsContainer.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                z-index: 1000;
                max-height: 300px;
                overflow-y: auto;
                background: var(--bg-primary, #fff);
                border: 1px solid var(--border-light, #ddd);
                border-top: none;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                border-radius: 0 0 4px 4px;
                margin-top: 0;
            `;
        }
        
        attachListeners() {
            this.resultsContainer.querySelectorAll('.search-results-item').forEach(item => {
                item.addEventListener('click', () => this.selectProvider(item));
            });
        }
        
        selectProvider(item) {
            this.input.value = item.dataset.name;
            this.input.dataset.selectedId = item.dataset.id;
            this.hide();
        }
        
        show() { this.resultsContainer.style.display = 'block'; }
        hide() { this.resultsContainer.style.display = 'none'; }
        
        init() {
            this.input.addEventListener('input', (e) => {
                clearTimeout(this.debounce);
                this.debounce = setTimeout(() => this.search(e.target.value.trim()), 300);
            });
            
            this.input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.hide();
                if (e.key === 'Enter') {
                    const first = this.resultsContainer?.querySelector('.search-results-item[data-id]');
                    if (first) this.selectProvider(first);
                }
            });
            
            document.addEventListener('click', (e) => {
                if (!this.input.contains(e.target) && !this.resultsContainer?.contains(e.target)) {
                    this.hide();
                }
            });
        }
    }
    
    const style = document.createElement('style');
    style.textContent = `
        .search-results-item:hover {
            background: var(--bg-secondary, #f5f5f5);
        }
        .search-results-item.no-results {
            color: var(--text-secondary, #666);
        }
    `;
    document.head.appendChild(style);

    // instanciar las clases cuando el dom este listo
    document.addEventListener('DOMContentLoaded', () => {
        new CajaManager();
        new CotizarManager();
        
        // Solo inicializar ProviderSearchManager si los elementos existen (evita error en páginas sin esos elementos)
        const providerSearchConfigs = [
            ['filtro_proveedor', 'provider_results_venta', 'proveedores', ['pr_id', 'pr_razon_social', 'pr_nit']],
            ['filtro_proveedor_quote', 'provider_results_quote', 'proveedores', ['pr_id', 'pr_razon_social', 'pr_nit']],
            ['filtro_presentacion', 'presentation_results', 'forma_farmaceutica', ['ff_id', 'ff_nombre']],
            ['filtro_funcion', 'function_results', 'uso_farmacologico', ['uf_id', 'uf_nombre']],
            ['filtro_via', 'via_results', 'via_de_administracion', ['vd_id', 'vd_nombre']]
        ];
        
        providerSearchConfigs.forEach(([inputId, resultsId, tabla, campos]) => {
            if (document.getElementById(inputId) && document.getElementById(resultsId)) {
                new ProviderSearchManager(inputId, resultsId, tabla, campos);
            }
        });
    });
</script>

<!-- script busqueda de cliente para caja -->
<script>
    // clase para manejar la busqueda de clientes
    class ClienteBusquedaManager {
        constructor() {
            // inicializa propiedades
            this.URL_CLI = "<?php echo SERVER_URL ?>ajax/ventaAjax.php";
            this.inputCliente = document.getElementById("buscar_cliente_venta");
            this.resultadoClientes = document.getElementById("resultado_clientes");

            this.clienteSeleccionado = null;
            this.debounceCliente = null;

            // verificar si el contenedor existe, si no, crearlo
            if (!this.resultadoClientes) {
                this.resultadoClientes = document.createElement('div');
                this.resultadoClientes.id = 'resultado_clientes';
                this.resultadoClientes.className = 'resultado-busqueda';
            }

            // encontrar el contenedor correcto
            this.ventasClienteContainer = this.inputCliente.closest('.ventas-cliente');

            if (this.ventasClienteContainer) {
                // asegurar position relative en el contenedor
                this.ventasClienteContainer.style.position = 'relative';

                // insertar el dropdown como hijo directo del contenedor
                if (!this.ventasClienteContainer.contains(this.resultadoClientes)) {
                    this.ventasClienteContainer.appendChild(this.resultadoClientes);
                }
            } else if (this.inputCliente.parentElement) {
                // fallback: usar el padre directo
                this.inputCliente.parentElement.style.position = 'relative';
                if (!this.inputCliente.parentElement.contains(this.resultadoClientes)) {
                    this.inputCliente.parentElement.appendChild(this.resultadoClientes);
                }
            }

            // aplicar estilos al contenedor
            this.resultadoClientes.style.cssText = `
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 9999;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 0 0 4px 4px;
            margin-top: 0;
        `;

            // obtener contenedor de cliente seleccionado
            this.clienteSeleccionadoContainer = document.getElementById('cliente_seleccionado_container');
            this.clienteNombreTexto = document.getElementById('cliente_nombre_texto');
            this.quitarClienteBtn = document.getElementById('quitar_cliente_btn');
            this.clienteIdHidden = document.getElementById('cliente_id_seleccionado');

            // inicializa eventos
            this.init();
        }

        // metodo para escapar html
        escapeHtml(text) {
            if (text == null) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        // metodo para buscar clientes
        async buscarClientes(termino) {
            const formData = new FormData();
            formData.append("ventaAjax", "buscar_cliente");
            formData.append("termino", termino);

            try {
                const response = await fetch(this.URL_CLI, {
                    method: "POST",
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`http error! status: ${response.status}`);
                }

                const text = await response.text();

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    data = [];
                }

                this.mostrarResultadosClientes(data);

            } catch (error) {
                this.resultadoClientes.innerHTML = '<div class="search-results-item no-results">error en la busqueda</div>';
                this.resultadoClientes.style.display = "block";
            }
        }

        // metodo para mostrar resultados de clientes
        mostrarResultadosClientes(clientes) {
            if (!clientes || clientes.length === 0) {
                this.resultadoClientes.innerHTML = '<div class="search-results-item no-results">no se encontraron clientes</div>';
                this.resultadoClientes.style.display = "block";
                return;
            }

            // generar html usando la misma estructura que medicamentos
            const html = clientes.map(cli => {
                const nombreCompleto = `${cli.cl_nombres || ''} ${cli.cl_apellido_paterno || ''} ${cli.cl_apellido_materno || ''}`.trim();
                const carnet = cli.cl_carnet || 'sin ci';
                const telefono = cli.cl_telefono ? ` · ${cli.cl_telefono}` : '';

                return `
                <div class="search-results-item cliente-item" 
                    data-id="${cli.cl_id}" 
                    data-nombre="${this.escapeHtml(nombreCompleto)}">
                    <div class="search-result-name">
                        <ion-icon name="person-circle-outline" style="vertical-align: middle; margin-right: 4px;"></ion-icon>
                        ${this.escapeHtml(nombreCompleto)}
                    </div>
                    <div class="search-result-details">ci: ${this.escapeHtml(carnet)}${this.escapeHtml(telefono)}</div>
                </div>`;
            }).join('');

            this.resultadoClientes.innerHTML = html;
            this.resultadoClientes.style.display = "block";

            // agregar event listeners a los items
            this.resultadoClientes.querySelectorAll('.cliente-item').forEach(item => {
                item.addEventListener('click', e => {
                    e.preventDefault();
                    this.seleccionarCliente(item);
                });
            });
        }

        // metodo para seleccionar cliente
        seleccionarCliente(item) {
            const id = item.dataset.id;
            const nombre = item.dataset.nombre;

            // guardar cliente seleccionado
            this.clienteSeleccionado = {
                id,
                nombre
            };

            // mostrar en la interfaz
            if (this.clienteNombreTexto) this.clienteNombreTexto.textContent = nombre;
            if (this.clienteIdHidden) this.clienteIdHidden.value = id;
            if (this.clienteSeleccionadoContainer) this.clienteSeleccionadoContainer.style.display = "block";

            // limpiar busqueda
            this.resultadoClientes.innerHTML = "";
            this.resultadoClientes.style.display = "none";
            if (this.inputCliente) this.inputCliente.value = "";
        }

        // metodo para quitar cliente seleccionado
        quitarCliente() {
            this.clienteSeleccionado = null;
            if (this.clienteIdHidden) this.clienteIdHidden.value = "";
            if (this.clienteSeleccionadoContainer) this.clienteSeleccionadoContainer.style.display = "none";
            if (this.inputCliente) {
                this.inputCliente.value = "";
                this.inputCliente.focus();
            }
        }

        // metodo para inicializar
        init() {
            if (this.inputCliente) {
                this.inputCliente.addEventListener("input", () => {
                    const termino = this.inputCliente.value.trim();
                    clearTimeout(this.debounceCliente);

                    if (termino.length < 1) {
                        this.resultadoClientes.innerHTML = "";
                        this.resultadoClientes.style.display = "none";
                        return;
                    }

                    // buscar despues de 250ms
                    this.debounceCliente = setTimeout(() => {
                        this.buscarClientes(termino);
                    }, 250);
                });

                this.inputCliente.addEventListener("focus", () => {
                    if (this.inputCliente.value.trim().length > 0 && this.resultadoClientes.innerHTML) {
                        this.resultadoClientes.style.display = "block";
                    }
                });

                // prevenir que el enter envie el formulario desde este input
                this.inputCliente.addEventListener("keydown", e => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        // si hay un resultado visible, seleccionar el primero
                        const primerResultado = this.resultadoClientes.querySelector('.cliente-item');
                        if (primerResultado && this.resultadoClientes.style.display === "block") {
                            this.seleccionarCliente(primerResultado);
                        }
                    }
                });
            }

            // quitar cliente seleccionado
            if (this.quitarClienteBtn) {
                this.quitarClienteBtn.addEventListener("click", e => {
                    e.preventDefault();
                    this.quitarCliente();
                });
            }

            // cerrar resultados al hacer click fuera
            document.addEventListener("click", e => {
                if (this.resultadoClientes &&
                    this.resultadoClientes.style.display === "block" &&
                    !this.inputCliente.contains(e.target) &&
                    !this.resultadoClientes.contains(e.target)) {
                    this.resultadoClientes.style.display = "none";
                }
            });

            // asegurar que el formulario envie el cliente_id
            const formVenta = document.querySelector('.form.FormularioAjax');
            if (formVenta) {
                formVenta.addEventListener('submit', () => {
                    if (this.clienteSeleccionado && this.clienteIdHidden) {
                        this.clienteIdHidden.value = this.clienteSeleccionado.id;
                    }
                });
            }

            // exponer funciones globalmente si es necesario
            window.ClienteBusqueda = {
                seleccionarCliente: (item) => this.seleccionarCliente(item),
                clienteSeleccionado: () => this.clienteSeleccionado
            };
        }
    }

    // instanciar la clase cuando el dom este listo
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById("buscar_cliente_venta")) {
            new ClienteBusquedaManager();
        }
    });
</script>
<!-- script para cerrar caja -->
<script>
    // clase para manejar el cierre de caja
    class CerrarCajaManager {
        constructor() {
            // inicializa propiedades
            this.btnCerrarCaja = document.getElementById('btn_cerrar_caja');
            this.URL_AJAX = "<?php echo SERVER_URL ?>ajax/ventaAjax.php";

            // inicializa eventos
            this.init();
        }

        // metodo para confirmar cierre de caja
        confirmarCierre() {
            // primera confirmacion
            Swal.fire({
                title: 'cerrar caja?',
                html: `
                <div style="text-align: left; padding: 10px;">
                    <p style="margin-bottom: 15px;"><ion-icon name="warning-outline" style="color: #ff9800; font-size: 24px; vertical-align: middle;"></ion-icon> <strong>esta accion cerrara tu caja actual</strong></p>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin: 8px 0;"><ion-icon name="checkmark-circle" style="color: #4caf50;"></ion-icon> se realizara un balance automatico</li>
                        <li style="margin: 8px 0;"><ion-icon name="checkmark-circle" style="color: #4caf50;"></ion-icon> el detalle sera visible para administradores</li>
                    </ul>
                </div>
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff9800',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'continuar',
                cancelButtonText: 'cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // segunda confirmacion
                    this.confirmarCierreSegunda();
                }
            });
        }

        // metodo para segunda confirmacion
        confirmarCierreSegunda() {
            Swal.fire({
                title: 'confirmar cierre de caja',
                html: `
                <p style="font-size: 16px; margin: 20px 0;">
                    <strong>estas completamente seguro?</strong>
                </p>
                <p style="color: #666; margin-bottom: 15px;">
                    esta accion es irreversible y cerrara tu sesion de ventas.
                </p>
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'si, cerrar caja',
                cancelButtonText: 'no, cancelar',
                reverseButtons: true
            }).then((result2) => {
                if (result2.isConfirmed) {
                    // proceder con el cierre
                    this.cerrarCajaAjax();
                }
            });
        }

        // metodo ajax para cerrar caja
        async cerrarCajaAjax() {
            // mostrar loading
            Swal.fire({
                title: 'cerrando caja...',
                html: 'por favor espera',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const formData = new FormData();
                formData.append('ventaAjax', 'cerrar-caja');

                const response = await fetch(this.URL_AJAX, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`http ${response.status}`);
                }

                const data = await response.json();

                Swal.close();

                // mostrar resultado
                if (data.Alerta === 'recargar') {
                    await Swal.fire({
                        icon: data.Tipo || 'success',
                        title: data.Titulo || 'exito',
                        html: data.texto || 'operacion exitosa',
                        confirmButtonText: 'entendido'
                    });

                    // recargar pagina
                    window.location.reload();
                } else {
                    Swal.fire({
                        icon: data.Tipo || 'info',
                        title: data.Titulo || 'atencion',
                        html: data.texto || 'operacion completada',
                        confirmButtonText: 'entendido'
                    });
                }

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'error de conexion',
                    text: 'no se pudo cerrar la caja: ' + error.message,
                    confirmButtonText: 'entendido'
                });
            }
        }

        // metodo para inicializar
        init() {
            if (this.btnCerrarCaja) {
                this.btnCerrarCaja.addEventListener('click', e => {
                    e.preventDefault();
                    this.confirmarCierre();
                });
            }
        }
    }

    // instanciar la clase cuando el dom este listo
    document.addEventListener('DOMContentLoaded', () => {
        new CerrarCajaManager();
    });
</script>

<!-- base 64s -->
<script>
    /* base 64 */
    window.abrirPDFDesdeBase64 = function(base64Data, nombreArchivo) {
        try {
            // Decodificar base64
            const byteCharacters = atob(base64Data);
            const byteNumbers = new Array(byteCharacters.length);

            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }

            const byteArray = new Uint8Array(byteNumbers);
            const blob = new Blob([byteArray], {
                type: 'application/pdf'
            });

            // Crear URL temporal
            const url = URL.createObjectURL(blob);

            // Abrir en nueva ventana
            const ventanaPDF = window.open(url, '_blank');

            // Limpiar URL cuando se cierre la ventana
            if (ventanaPDF) {
                ventanaPDF.onbeforeunload = function() {
                    URL.revokeObjectURL(url);
                };
            }

            return true;

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo abrir el PDF. Intenta nuevamente.'
            });
            return false;
        }
    };
</script>



<!-- funciones para inventario -->

<script>
    window.InventarioModals = (function() {
        'use strict';

        const API_URL = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php';

        // ==================== UTILIDADES ====================
        const utils = {
            async ajax(params) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(params)
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const data = await response.json();
                    return data;

                } catch (error) {
                    throw error;
                }
            },

            abrir(modalId) {
                App.showM(modalId);
            },

            cerrar(modalId) {
                App.closeM(modalId);
            },

            formatearFecha(fecha) {
                if (!fecha) return 'N/A';
                const d = new Date(fecha);
                const dia = String(d.getDate()).padStart(2, '0');
                const mes = String(d.getMonth() + 1).padStart(2, '0');
                const anio = d.getFullYear();
                return `${dia}/${mes}/${anio}`;
            },

            formatearNumero(num) {
                return parseInt(num || 0).toLocaleString('es-BO');
            },

            formatearMoneda(num) {
                return 'Bs ' + parseFloat(num || 0).toFixed(2);
            }
        };

        // ==================== MODAL DETALLE ====================
        const detalle = {
            async abrir(invId, medId, suId, medicamento) {
                console.log({
                    invId,
                    medId,
                    suId,
                    medicamento
                });

                document.getElementById('modalDetalleMedicamento').textContent = medicamento;
                document.getElementById('modalDetalleInvId').value = invId;
                document.getElementById('modalDetalleMedId').value = medId;
                document.getElementById('modalDetalleSuId').value = suId;

                utils.abrir('modalDetalleInventario');

                document.getElementById('tablaLotesDetalle').innerHTML =
                    '<tr><td colspan="5" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                try {
                    const data = await utils.ajax({
                        inventarioAjax: 'detalle',
                        inv_id: invId,
                        med_id: medId,
                        su_id: suId
                    });

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    document.getElementById('detalleLaboral').textContent = data.proveedor || 'N/A';
                    document.getElementById('detalleSucursal').textContent = data.sucursal || 'N/A';
                    document.getElementById('detalleCajas').textContent = utils.formatearNumero(data.cajas);
                    document.getElementById('detalleUnidades').textContent = utils.formatearNumero(data.unidades);
                    document.getElementById('detalleValorado').textContent = utils.formatearMoneda(data.valorado);
                    document.getElementById('detalleEstado').innerHTML = data.estado_html || 'N/A';

                    const tbody = document.getElementById('tablaLotesDetalle');
                    if (data.lotes && data.lotes.length > 0) {
                        tbody.innerHTML = data.lotes.map(lote => `
                        <tr>
                            <td>${lote.numero_lote}</td>
                            <td>${utils.formatearNumero(lote.unidades)}</td>
                            <td>${utils.formatearMoneda(lote.precio)}</td>
                            <td>${utils.formatearFecha(lote.vencimiento)}</td>
                            <td>${lote.estado}</td>
                        </tr>
                    `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin lotes</td></tr>';
                    }

                } catch (error) {
                    Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                }
            }
        };

        // ==================== MODAL TRANSFERIR ====================
        const transferir = {
            async abrir(invId, medId, suId, medicamento) {
                document.getElementById('modalTransferirMedicamento').textContent = medicamento;
                document.getElementById('modalTransferirInvId').value = invId;
                document.getElementById('modalTransferirMedId').value = medId;
                document.getElementById('modalTransferirSuOrigenId').value = suId;

                document.getElementById('transferirSucursalDestino').value = '';
                document.getElementById('transferirCantidad').value = '';
                document.getElementById('transferirMotivo').value = '';
                document.getElementById('transferirStockDisponible').textContent = '';

                utils.abrir('modalTransferirInventario');

                try {
                    const data = await utils.ajax({
                        inventarioAjax: 'lotes_transferibles',
                        med_id: medId,
                        su_id: suId
                    });

                    const selectLote = document.getElementById('transferirLote');
                    selectLote.innerHTML = '<option value="">Seleccione lote...</option>';

                    if (data.lotes && data.lotes.length > 0) {
                        data.lotes.forEach(lote => {
                            selectLote.innerHTML += `<option value="${lote.lm_id}" data-stock="${lote.stock}">${lote.numero_lote} (${utils.formatearNumero(lote.stock)} unid.)</option>`;
                        });
                    } else {
                        selectLote.innerHTML = '<option value="">Sin lotes disponibles</option>';
                    }

                } catch (error) {
                    Swal.fire('Error', 'No se pudieron cargar los lotes', 'error');
                }
            },

            procesar() {
                Swal.fire({
                    title: 'Funcionalidad en desarrollo',
                    text: 'La transferencia se implementará en la siguiente fase',
                    icon: 'info'
                });
            }
        };

        // ==================== MODAL HISTORIAL ====================
        const historial = {
            async abrir(medId, suId, medicamento) {
                document.getElementById('modalHistorialMedicamento').textContent = medicamento;
                document.getElementById('modalHistorialMedId').value = medId;
                document.getElementById('modalHistorialSuId').value = suId;

                utils.abrir('modalHistorialInventario');

                document.getElementById('tablaHistorialMovimientos').innerHTML =
                    '<tr><td colspan="7" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

                try {
                    const data = await utils.ajax({
                        inventarioAjax: 'historial',
                        med_id: medId,
                        su_id: suId
                    });

                    const tbody = document.getElementById('tablaHistorialMovimientos');

                    if (data.movimientos && data.movimientos.length > 0) {
                        tbody.innerHTML = data.movimientos.map(mov => {
                            const colorTipo = mov.tipo === 'entrada' ? '#e8f5e9' : '#ffebee';
                            const iconTipo = mov.tipo === 'entrada' ? 'arrow-down-circle-outline' : 'arrow-up-circle-outline';

                            const motivoLimpio = (mov.motivo || '-').replace(/\s*\(lm_id\s+\d+\)/g, '');
                            return `
                            <tr>
                                <td>${mov.fecha}</td>
                                <td>
                                    <span style="background:${colorTipo}; padding:4px 8px; border-radius:4px; display:inline-flex; align-items:center; gap:4px;">
                                        <ion-icon name="${iconTipo}"></ion-icon>
                                        ${mov.tipo.toUpperCase()}
                                    </span>
                                </td>
                                <td>${utils.formatearNumero(mov.cantidad)} ${mov.unidad}</td>
                                <td>${mov.lote || 'N/A'}</td>
                                <td>${mov.sucursal || 'N/A'}</td>
                                <td>${mov.usuario || 'Sistema'}</td>
                                <td>${motivoLimpio}</td>
                            </tr>
                        `;
                        }).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin movimientos</td></tr>';
                    }

                } catch (error) {
                    Swal.fire('Error', 'No se pudo cargar el historial', 'error');
                }
            }
        };

        // ==================== MODAL CONFIGURACION ====================
        const configuracion = {
            async abrir(invId, medId, suId, medicamento, minimoActual = 0, maximoActual = null) {
                console.log({
                    invId,
                    medId,
                    suId,
                    medicamento,
                    minimoActual,
                    maximoActual
                });

                document.getElementById('modalConfiguracionMedicamento').textContent = medicamento;
                document.getElementById('modalConfiguracionInvId').value = invId;
                document.getElementById('modalConfiguracionMedId').value = medId;
                document.getElementById('modalConfiguracionSuId').value = suId;
                document.getElementById('configuracionMinimo').value = minimoActual || 0;
                document.getElementById('configuracionMaximo').value = maximoActual || '';

                utils.abrir('modalConfiguracionInventario');
            },

            async guardar() {
                const invId = document.getElementById('modalConfiguracionInvId').value;
                const invMinimo = parseInt(document.getElementById('configuracionMinimo').value) || 0;
                const invMaximoInput = document.getElementById('configuracionMaximo').value;
                const invMaximo = invMaximoInput !== '' ? parseInt(invMaximoInput) : null;

                console.log({
                    invId,
                    invMinimo,
                    invMaximo
                });

                if (!invId) {
                    Swal.fire('Error', 'Inventario no válido', 'error');
                    return;
                }

                if (invMinimo < 0) {
                    Swal.fire('Validación', 'La cantidad mínima no puede ser negativa', 'warning');
                    return;
                }

                if (invMaximo !== null && invMaximo < invMinimo) {
                    Swal.fire('Validación', 'La cantidad máxima debe ser mayor o igual a la mínima', 'warning');
                    return;
                }

                try {
                    const data = await utils.ajax({
                        inventarioAjax: 'configurar',
                        inv_id: invId,
                        inv_minimo: invMinimo,
                        inv_maximo: invMaximo || ''
                    });

                    if (data.Tipo === 'success') {
                        Swal.fire('Éxito', data.texto, 'success');
                        utils.cerrar('modalConfiguracionInventario');

                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        Swal.fire('Error', data.texto, 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'No se pudo guardar la configuración', 'error');
                }
            }
        };

        // ==================== MODAL BALANCE ====================
        const balance = {
            async abrir(medId, suId, medicamento, sucursal) {
                document.getElementById('balanceMedId').value = medId;
                document.getElementById('balanceSuId').value = suId;
                document.getElementById('modalBalanceMedicamento').textContent = medicamento;
                document.getElementById('balanceNombreMedicamento').textContent = medicamento;
                document.getElementById('balanceSucursal').textContent = sucursal;

                await balance.obtenerDatosActuales(medId, suId);

                utils.abrir('modalBalanceInventario');
                balance.bindCalculationEvents();
            },

            async obtenerDatosActuales(medId, suId) {
                try {
                    const data = await utils.ajax({
                        inventarioAjax: 'obtener_datos_balance',
                        med_id: medId,
                        su_id: suId
                    });

                    if (data.success) {
                        document.getElementById('balanceCostoLista').value = data.costo_lista || '';
                        document.getElementById('balancePrecioCosto').value = data.precio_costo || '';
                        document.getElementById('balanceUnidadesCaja').value = data.unidades_caja || 1;
                        document.getElementById('balanceMargenUnitario').value = data.margen_u || '';
                        document.getElementById('balanceMargenCaja').value = data.margen_c || '';
                        document.getElementById('balanceLaboratorio').textContent = data.proveedor || 'Sin proveedor';

                        balance.calcularTodo();
                    }
                } catch (error) {
                    console.error('Error obteniendo datos balance:', error);
                }
            },

            calcularTodo() {
                const costoListaCaja = parseFloat(document.getElementById('balanceCostoLista')?.value) || 0;
                const unidadesCaja = parseInt(document.getElementById('balanceUnidadesCaja')?.value) || 1;
                const margenU = parseFloat(document.getElementById('balanceMargenUnitario')?.value) || 0;
                const margenC = parseFloat(document.getElementById('balanceMargenCaja')?.value) || 0;

                const costoUnitario = unidadesCaja > 0 ? costoListaCaja / unidadesCaja : 0;
                const precioVenta = costoUnitario * (1 + margenU / 100);
                const precioMinU = costoUnitario * (1 + margenU / 100);
                const precioMinC = costoListaCaja * (1 + margenC / 100);

                document.getElementById('balanceCostoUnitario').value = costoUnitario.toFixed(2);
                document.getElementById('balancePrecioVenta').value = precioVenta.toFixed(2);
                document.getElementById('balancePrecioMinUnitario').value = precioMinU.toFixed(2);
                document.getElementById('balancePrecioMinCaja').value = precioMinC.toFixed(2);
            },

            clampMargen(input) {
                let value = input.value;

                if (value === "") return;

                value = value.replace(/[^0-9.]/g, "");

                const parts = value.split(".");
                if (parts.length > 2) {
                    value = parts[0] + "." + parts.slice(1).join("").replace(/\./g, "");
                }

                if (parts.length > 1 && parts[1].length > 2) {
                    value = parts[0] + "." + parts[1].substring(0, 2);
                }

                if (value.length > 1 && value.startsWith("0") && value[1] !== ".") {
                    value = value.substring(1);
                }

                if (parseFloat(value) > 100) {
                    value = "100";
                }

                if (input.value !== value) {
                    input.value = value;
                }
            },

            validarMargen(input) {
                let valor = parseFloat(input.value);
                if (isNaN(valor) || valor < 0) {
                    input.value = "0.00";
                } else {
                    if (valor > 100) valor = 100;
                    input.value = valor.toFixed(2);
                }
            },

            bindCalculationEvents() {
                const margenU = document.getElementById('balanceMargenUnitario');
                const margenC = document.getElementById('balanceMargenCaja');

                if (margenU) {
                    margenU.addEventListener('input', (e) => {
                        balance.clampMargen(e.target);
                        balance.calcularTodo();
                    });
                    margenU.addEventListener('blur', (e) => balance.validarMargen(e.target));
                }
                if (margenC) {
                    margenC.addEventListener('input', (e) => {
                        balance.clampMargen(e.target);
                        balance.calcularTodo();
                    });
                    margenC.addEventListener('blur', (e) => balance.validarMargen(e.target));
                }
            },

            async guardar() {
                const medId = document.getElementById('balanceMedId').value;
                const suId = document.getElementById('balanceSuId').value;
                const costoLista = document.getElementById('balanceCostoLista').value;
                const precioCosto = document.getElementById('balancePrecioCosto').value;
                const unidadesCaja = document.getElementById('balanceUnidadesCaja').value;
                const margenU = document.getElementById('balanceMargenUnitario').value;
                const margenC = document.getElementById('balanceMargenCaja').value;
                const precioVenta = document.getElementById('balancePrecioVenta').value;
                const precioMinU = document.getElementById('balancePrecioMinUnitario').value;
                const precioMinC = document.getElementById('balancePrecioMinCaja').value;

                const formData = new FormData();
                formData.append('inventarioAjax', 'guardar_balance');
                formData.append('med_id', medId);
                formData.append('su_id', suId);
                formData.append('lm_costo_lista', costoLista);
                formData.append('lm_precio_costo', precioCosto);
                formData.append('lm_unidades_caja', unidadesCaja);
                formData.append('lm_margen_u', margenU);
                formData.append('lm_margen_c', margenC);
                formData.append('lm_precio_venta', precioVenta);
                formData.append('lm_precio_min_u', precioMinU);
                formData.append('lm_precio_min_c', precioMinC);

                try {
                    const data = await utils.ajax(formData);
                    if (data.success) {
                        Swal.fire('Éxito', 'Balance de precios guardado', 'success');
                        utils.cerrar('modalBalanceInventario');
                    } else {
                        Swal.fire('Error', data.error || 'Error al guardar', 'error');
                    }
                } catch (error) {
                    Swal.fire('Error', 'Error de conexión', 'error');
                }
            }
        };

        // ==================== LISTENER PARA ACTUALIZAR STOCK ==================== 
        document.addEventListener('DOMContentLoaded', function() {
            const selectLote = document.getElementById('transferirLote');
            if (selectLote) {
                selectLote.addEventListener('change', function() {
                    const option = this.options[this.selectedIndex];
                    const stock = option.getAttribute('data-stock');
                    const infoElement = document.getElementById('transferirStockDisponible');

                    if (stock && stock > 0) {
                        infoElement.textContent = `Stock disponible: ${utils.formatearNumero(stock)} unidades`;
                        infoElement.style.color = '#4caf50';
                    } else {
                        infoElement.textContent = '';
                    }
                });
            }
        });

        // ==================== API PÚBLICA ====================
        return {
            cerrar: utils.cerrar,
            verDetalle: detalle.abrir,
            abrirTransferencia: transferir.abrir,
            procesarTransferencia: transferir.procesar,
            verHistorial: historial.abrir,
            abrirConfiguracion: configuracion.abrir,
            guardarConfiguracion: configuracion.guardar,
            abrirBalance: balance.abrir,
            guardarBalance: balance.guardar
        };
    })(); //  Cierre correcto del IIFE

    // ==================== EXPORTAR EXCEL ====================
    document.addEventListener('DOMContentLoaded', function() {
        const btnExcel = document.getElementById('btnExportarExcel');

        if (btnExcel) {
            btnExcel.addEventListener('click', function() {
                const laboratorioSelect = document.querySelector('select[name="select1"]');
                const estadoSelect = document.querySelector('select[name="select2"]');
                const sucursalSelect = document.querySelector('select[name="select3"]');
                const formaSelect = document.querySelector('select[name="select4"]');
                const busquedaInput = document.querySelector('input[name="busqueda"]');

                const laboratorioId = laboratorioSelect ? laboratorioSelect.value : '';
                const estado = estadoSelect ? estadoSelect.value : '';
                const sucursalId = sucursalSelect ? sucursalSelect.value : '';
                const formaId = formaSelect ? formaSelect.value : '';
                const busqueda = busquedaInput ? busquedaInput.value : '';

                let url = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php?inventarioAjax=exportar_excel';

                if (laboratorioId) url += '&select1=' + encodeURIComponent(laboratorioId);
                if (estado) url += '&select2=' + encodeURIComponent(estado);
                if (sucursalId) url += '&select3=' + encodeURIComponent(sucursalId);
                if (formaId) url += '&select4=' + encodeURIComponent(formaId);
                if (busqueda) url += '&busqueda=' + encodeURIComponent(busqueda);

                console.log(' Descargando archivo:', url);

                window.open(url, '_blank');

                Swal.fire({
                    icon: 'success',
                    title: 'Descargando',
                    text: 'El archivo se está descargando...',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }
    });

    // ==================== CERRAR MODALES AL HACER CLIC FUERA ====================
    (function() {
        const modalIds = ['modalDetalleInventario', 'modalTransferirInventario', 'modalHistorialInventario', 'modalConfiguracionInventario'];

        document.addEventListener('click', function(e) {
            modalIds.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && modal.style && modal.style.display === 'flex' && e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    })();
</script>

<!-- vista tavla historial caja -->
<script>
    const CajaHistorial = (function() {
        'use strict';

        const API_URL = '<?php echo SERVER_URL; ?>ajax/cajaHistorialAjax.php';

        function init() {
            cargarResumen();
            cargarGrafico();
            configurarEventos();
        }

        function configurarEventos() {
            const btnExcel = document.getElementById('btnExportarExcelCajaHistorial');
            const btnPDF = document.getElementById('btnExportarPDFCajaHistorial');


            if (btnExcel) {
                btnExcel.addEventListener('click', exportarExcel);
            }

            if (btnPDF) {
                btnPDF.addEventListener('click', exportarPDF);
            }

            const filtros = document.querySelectorAll('.filtro-dinamico select, .filtro-dinamico input[type="date"]');
            filtros.forEach(filtro => {
                filtro.addEventListener('change', function() {
                    cargarResumen();
                    cargarGrafico();
                });
            });
        }

        async function cargarResumen() {
            const formData = obtenerFiltros();
            formData.append('cajaHistorialAjax', 'resumen');


            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                });

                const data = await response.json();

                if (data.error) {
                    return;
                }

                const resumenHTML = `
                <div class="container">
                    <div class="title">
                    <h4>
                        <ion-icon name="analytics-outline" ></ion-icon>
                        Resumen del Periodo Filtrado
                    </h4></div>
                    <div class="resumen-content">
                        <div class="resumen-bloque">
                            <div >Total Ingresos</div>
                            <div style="font-size: 24px; font-weight: bold; color: #4caf50;">
                                <ion-icon name="arrow-down-circle-outline" ></ion-icon>
                                Bs. ${formatearNumero(data.total_ingresos)}
                            </div>
                        </div>
                        <div class="resumen-bloque">
                            <div >Total Egresos</div>
                            <div style="font-size: 24px; font-weight: bold; color: #f44336;">
                                <ion-icon name="arrow-up-circle-outline" ></ion-icon>
                                Bs. ${formatearNumero(data.total_egresos)}
                            </div>
                        </div>
                        <div class="resumen-bloque">
                            <div >Balance</div>
                            <div style="font-size: 24px; font-weight: bold; color: ${data.balance >= 0 ? '#4caf50' : '#f44336'};">
                                <ion-icon name="${data.balance >= 0 ? 'trending-up-outline' : 'trending-down-outline'}" ></ion-icon>
                                Bs. ${formatearNumero(data.balance)}
                            </div>
                        </div>
                    </div>
                </div>
            `;

                const resumenContainer = document.getElementById('resumen-periodo');
                if (resumenContainer) {
                    resumenContainer.innerHTML = resumenHTML;
                }

            } catch (error) {}
        }

        async function cargarGrafico() {
            const formData = obtenerFiltros();
            formData.append('cajaHistorialAjax', 'grafico');


            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                });

                const datos = await response.json();

                if (datos.error) {
                    return;
                }

                const fechas = [...new Set(datos.map(d => d.fecha))];
                const ingresos = fechas.map(fecha => {
                    const registro = datos.find(d => d.fecha === fecha && (d.mc_tipo === 'ingreso' || d.mc_tipo === 'venta'));
                    return registro ? parseFloat(registro.total) : 0;
                });
                const egresos = fechas.map(fecha => {
                    const registro = datos.find(d => d.fecha === fecha && (d.mc_tipo === 'egreso' || d.mc_tipo === 'compra'));
                    return registro ? parseFloat(registro.total) : 0;
                });

                const fechasFormateadas = fechas.map(f => {
                    const [y, m, d] = f.split('-');
                    return `${d}/${m}`;
                });

                const myChart = echarts.init(document.getElementById('grafico-movimientos'));

                const option = {
                    title: {
                        text: 'Movimientos de Caja por Fecha',
                        left: 'center'
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        }
                    },
                    legend: {
                        data: ['Ingresos', 'Egresos'],
                        bottom: 0
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '10%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        data: fechasFormateadas
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                            name: 'Ingresos',
                            type: 'bar',
                            data: ingresos,
                            itemStyle: {
                                color: '#4caf50'
                            }
                        },
                        {
                            name: 'Egresos',
                            type: 'bar',
                            data: egresos,
                            itemStyle: {
                                color: '#f44336'
                            }
                        }
                    ]
                };

                myChart.setOption(option);

            } catch (error) {}
        }

        function obtenerFiltros() {
            const formData = new FormData();
            const form = document.querySelector('.filtro-dinamico');

            if (form) {
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');
                const select1 = form.querySelector('select[name="select1"]');
                const select2 = form.querySelector('select[name="select2"]');
                const select3 = form.querySelector('select[name="select3"]');
                const select4 = form.querySelector('select[name="select4"]');

                if (fechaDesde && fechaDesde.value) formData.append('fecha_desde', fechaDesde.value);
                if (fechaHasta && fechaHasta.value) formData.append('fecha_hasta', fechaHasta.value);
                if (select1 && select1.value) formData.append('select1', select1.value);
                if (select2 && select2.value) formData.append('select2', select2.value);
                if (select3 && select3.value) formData.append('select3', select3.value);
                if (select4 && select4.value) formData.append('select4', select4.value);
            }

            return formData;
        }

        function formatearNumero(num) {
            return parseFloat(num || 0).toFixed(2);
        }

        function verReferencia(tipo, id) {
            if (!tipo || !id) {
                Swal.fire('Sin referencia', 'Este movimiento no tiene referencia asociada', 'info');
                return;
            }

            const urls = {
                'venta': '<?php echo SERVER_URL; ?>ventaDetalle/' + id + '/',
                'compra': '<?php echo SERVER_URL; ?>compraDetalle/' + id + '/'
            };

            if (urls[tipo]) {
                window.location.href = urls[tipo];
            } else {
                Swal.fire('Referencia no disponible', 'No se puede abrir esta referencia', 'warning');
            }
        }

        function exportarMovimiento(mc_id) {
            if (!mc_id || mc_id <= 0) {
                Swal.fire('Error', 'ID de movimiento invalido', 'error');
                return;
            }

            const url = API_URL + '?cajaHistorialAjax=exportar_movimiento_pdf&mc_id=' + mc_id;

            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Generando Comprobante',
                text: 'El comprobante PDF se esta generando...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function exportarExcel() {
            const filtros = obtenerFiltros();
            const params = new URLSearchParams(filtros);
            const url = API_URL + '?cajaHistorialAjax=exportar_excel&' + params.toString();


            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Descargando',
                text: 'El archivo Excel se esta descargando...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        function exportarPDF() {
            const filtros = obtenerFiltros();
            const params = new URLSearchParams(filtros);
            const url = API_URL + '?cajaHistorialAjax=exportar_pdf_historial&' + params.toString();


            window.open(url, '_blank');

            Swal.fire({
                icon: 'success',
                title: 'Generando PDF',
                text: 'El archivo PDF se esta generando...',
                timer: 2000,
                showConfirmButton: false
            });
        }

        document.addEventListener('DOMContentLoaded', init);

        return {
            verReferencia,
            exportarMovimiento,
            exportarExcel,
            exportarPDF
        };
    })();
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const API_URL = '<?php echo SERVER_URL; ?>ajax/dashboardAjax.php';

        const chartVencimientos = document.getElementById('chartVencimientos');
        if (chartVencimientos) {
            const myChart = echarts.init(chartVencimientos);

            fetch(API_URL + '?dashboardAjax=obtener_vencimientos_ajax')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const opcion = {
                            tooltip: {
                                trigger: 'item'
                            },
                            legend: {
                                orient: 'vertical',
                                left: 'left'
                            },
                            series: [{
                                name: 'Vencimientos',
                                type: 'pie',
                                radius: '50%',
                                data: [{
                                        value: data.data.expirados || 0,
                                        name: 'Expirados',
                                        itemStyle: {
                                            color: '#d32f2f'
                                        }
                                    },
                                    {
                                        value: data.data.proximos || 0,
                                        name: 'Próximos 30 días',
                                        itemStyle: {
                                            color: '#ffa500'
                                        }
                                    },
                                    {
                                        value: data.data.disponibles || 0,
                                        name: 'Disponibles',
                                        itemStyle: {
                                            color: '#4caf50'
                                        }
                                    }
                                ]
                            }]
                        };
                        myChart.setOption(opcion);
                    }
                })
                .catch(error => console.error('Error al cargar vencimientos:', error));
        }

        const chartStockMinimo = document.getElementById('chartStockMinimo');
        if (chartStockMinimo) {
            const myChart = echarts.init(chartStockMinimo);

            fetch(API_URL + '?dashboardAjax=obtener_stock_minimo_ajax')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        const productos = data.data.map(item => item.med_nombre_quimico);
                        const stock = data.data.map(item => parseInt(item.inv_total_unidades));

                        const opcion = {
                            tooltip: {
                                trigger: 'axis'
                            },
                            xAxis: {
                                type: 'category',
                                data: productos,
                                axisLabel: {
                                    interval: 0,
                                    rotate: 45
                                }
                            },
                            yAxis: {
                                type: 'value'
                            },
                            series: [{
                                data: stock,
                                type: 'bar',
                                itemStyle: {
                                    color: '#ffa500'
                                }
                            }],
                            grid: {
                                bottom: 100
                            }
                        };
                        myChart.setOption(opcion);
                    }
                })
                .catch(error => console.error('Error al cargar stock mínimo:', error));
        }

        const chartProductosVendidos = document.getElementById('chartProductosVendidos');
        if (chartProductosVendidos) {
            const myChart = echarts.init(chartProductosVendidos);

            fetch(API_URL + '?dashboardAjax=obtener_productos_vendidos_ajax')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        const productos = data.data.map(item => item.med_nombre_quimico);
                        const cantidades = data.data.map(item => parseInt(item.cantidad_vendida));

                        const opcion = {
                            tooltip: {
                                trigger: 'axis'
                            },
                            xAxis: {
                                type: 'category',
                                data: productos,
                                axisLabel: {
                                    interval: 0,
                                    rotate: 45
                                }
                            },
                            yAxis: {
                                type: 'value'
                            },
                            series: [{
                                data: cantidades,
                                type: 'bar',
                                itemStyle: {
                                    color: '#2196f3'
                                }
                            }],
                            grid: {
                                bottom: 100
                            }
                        };
                        myChart.setOption(opcion);
                    }
                })
                .catch(error => console.error('Error al cargar productos vendidos:', error));
        }

        const chartVentasMensuales = document.getElementById('chartVentasMensuales');
        if (chartVentasMensuales) {
            const myChart = echarts.init(chartVentasMensuales);

            fetch(API_URL + '?dashboardAjax=obtener_ventas_mensuales_ajax')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        const meses = data.data.map(item => item.mes);
                        const totales = data.data.map(item => parseFloat(item.total_mes));

                        const opcion = {
                            tooltip: {
                                trigger: 'axis'
                            },
                            xAxis: {
                                type: 'category',
                                data: meses
                            },
                            yAxis: {
                                type: 'value'
                            },
                            series: [{
                                data: totales,
                                type: 'bar',
                                itemStyle: {
                                    color: '#4caf50'
                                }
                            }],
                            grid: {
                                bottom: 50
                            }
                        };
                        myChart.setOption(opcion);
                    }
                })
                .catch(error => console.error('Error al cargar ventas mensuales:', error));
        }
    });
</script>
<script src="<?php echo SERVER_URL; ?>views/script/ajax-tabla.js"></script>
<script src="<?php echo SERVER_URL; ?>views/script/notificaciones.js"></script>

<script src="<?php echo SERVER_URL; ?>views/script/script-base.js"></script>

    <script>
    // =====================================================
    // NUEVO SISTEMA DE DROPDOWNS - Vista Caja (sin afectar selects)
    // =====================================================
    class DropdownCajaManager {
        constructor() {
            this.dropdowns = new Map();
        }

        create(inputId, resultsId, tabla, campos) {
            const input = document.getElementById(inputId);
            const results = document.getElementById(resultsId);
            
            if (!input || !results) return null;

            const config = {
                input,
                results,
                tabla,
                campos,
                debounce: null,
                cache: {}
            };

            this.dropdowns.set(inputId, config);
            this.attachEvents(config);
            return config;
        }

        attachEvents(config) {
            const { input, results } = config;
            
            input.addEventListener('input', () => {
                clearTimeout(config.debounce);
                config.debounce = setTimeout(() => {
                    this.search(config, input.value.trim());
                }, 300);
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.hide(config);
                }
                if (e.key === 'Enter') {
                    const first = results.querySelector('.dd-item[data-id]');
                    if (first) {
                        e.preventDefault();
                        this.select(config, first.dataset.id, first.dataset.name);
                    }
                }
            });

            // Solo cerrar este dropdown específico, no afectar otros
            const closeHandler = (e) => {
                if (!input.contains(e.target) && !results.contains(e.target)) {
                    this.hide(config);
                }
            };
            document.addEventListener('click', closeHandler);
            config.closeHandler = closeHandler;
        }

        async search(config, term) {
            if (term.length < 2) {
                this.hide(config);
                return;
            }

            const cacheKey = term;
            if (config.cache[cacheKey]) {
                this.render(config, config.cache[cacheKey]);
                return;
            }

            try {
                const body = new URLSearchParams();
                body.append('ventaAjax', 'select_v2');
                body.append('tabla', config.tabla);
                body.append('campos', JSON.stringify(config.campos));
                body.append('termino', term);

                const response = await fetch('<?php echo SERVER_URL ?>ajax/ventaAjax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                });
                const data = await response.json();
                config.cache[cacheKey] = data;
                this.render(config, data);
            } catch (err) {
                this.render(config, []);
            }
        }

        render(config, items) {
            const { results } = config;
            
            if (!items || items.length === 0) {
                results.innerHTML = '<div class="dd-empty">Sin resultados</div>';
                this.show(config);
                return;
            }

            results.innerHTML = items.map(item => {
                const id = item[config.campos[0]];
                const name = item[config.campos[1]] || '';
                const extra = item[config.campos[2]] || '';
                
                return `
                    <div class="dd-item" data-id="${id}" data-name="${this.escapeHtml(name)}" style="cursor: pointer; padding: 8px; border-bottom: 1px solid #eee;">
                        <div><strong>${this.escapeHtml(name)}</strong></div>
                        ${extra ? `<small style="color: #666;">${this.escapeHtml(extra)}</small>` : ''}
                    </div>
                `;
            }).join('');

            this.attachSelectListener(config);
            this.show(config);
        }

        attachSelectListener(config) {
            config.results.querySelectorAll('.dd-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.select(config, item.dataset.id, item.dataset.name);
                });
            });
        }

        select(config, id, name) {
            config.input.value = name;
            config.input.dataset.selectedId = id;
            this.hide(config);
        }

        show(config) {
            config.results.style.display = 'block';
        }

        hide(config) {
            config.results.style.display = 'none';
        }

        escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/[&<>"']/g, m => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[m]));
        }

        clearCache(inputId) {
            const config = this.dropdowns.get(inputId);
            if (config) {
                config.cache = {};
            }
        }
    }

    // Clase específica para búsqueda de clientes en caja
    class ClienteDropdownCaja {
        constructor() {
            this.input = document.getElementById('buscar_cliente_venta');
            this.results = document.getElementById('resultado_clientes');
            this.debounce = null;
            
            if (!this.input) return;
            this.init();
        }

        init() {
            this.input.addEventListener('input', () => {
                clearTimeout(this.debounce);
                this.debounce = setTimeout(() => {
                    this.search(this.input.value.trim());
                }, 300);
            });

            this.input.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.hide();
                if (e.key === 'Enter') {
                    const first = this.results?.querySelector('.dd-cliente-item[data-id]');
                    if (first) {
                        e.preventDefault();
                        this.select(first);
                    }
                }
            });

            // Solo cerrar este dropdown específico
            const closeHandler = (e) => {
                if (this.results && !this.input.contains(e.target) && !this.results.contains(e.target)) {
                    this.hide();
                }
            };
            document.addEventListener('click', closeHandler);
            this.closeHandler = closeHandler;
        }

        async search(term) {
            if (term.length < 1) {
                this.hide();
                return;
            }

            try {
                const form = new FormData();
                form.append('ventaAjax', 'buscar_cliente');
                form.append('termino', term);

                const response = await fetch('<?php echo SERVER_URL ?>ajax/ventaAjax.php', {
                    method: 'POST',
                    body: form
                });
                const data = await response.json();
                this.render(data);
            } catch (err) {
                this.render([]);
            }
        }

        render(clientes) {
            if (!this.results) return;

            if (!clientes || clientes.length === 0) {
                this.results.innerHTML = '<div class="dd-empty">Sin clientes</div>';
                this.show();
                return;
            }

            this.results.innerHTML = clientes.map(cli => {
                const nombre = `${cli.cl_nombres || ''} ${cli.cl_apellido_paterno || ''} ${cli.cl_apellido_materno || ''}`.trim();
                return `
                    <div class="dd-cliente-item" data-id="${cli.cl_id}" data-name="${this.escapeHtml(nombre)}" 
                         style="padding: 8px; border-bottom: 1px solid #eee; cursor: pointer;">
                        <div><strong>${this.escapeHtml(nombre)}</strong></div>
                        <small style="color: #666;">CI: ${this.escapeHtml(cli.cl_carnet || 'N/A')}</small>
                    </div>
                `;
            }).join('');

            this.attachListeners();
            this.show();
        }

        attachListeners() {
            this.results.querySelectorAll('.dd-cliente-item').forEach(item => {
                item.addEventListener('click', () => this.select(item));
            });
        }

        select(item) {
            const id = item.dataset.id;
            const name = item.dataset.name;

            this.input.value = name;
            const hiddenId = document.getElementById('cliente_id_seleccionado');
            if (hiddenId) hiddenId.value = id;

            const container = document.getElementById('cliente_seleccionado_container');
            const nombreTexto = document.getElementById('cliente_nombre_texto');
            if (container) container.style.display = 'flex';
            if (nombreTexto) nombreTexto.textContent = name;

            this.hide();
        }

        hide() {
            if (this.results) this.results.style.display = 'none';
        }

        show() {
            if (this.results) this.results.style.display = 'block';
        }

        escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/[&<>"']/g, m => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[m]));
        }
    }

    // Inicialización cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar dropdowns de la vista caja
        const dropdownManager = new DropdownCajaManager();
        
        dropdownManager.create(
            'filtro_proveedor', 
            'provider_results_venta', 
            'proveedores', 
            ['pr_id', 'pr_razon_social', 'pr_nit']
        );
        
        dropdownManager.create(
            'filtro_presentacion', 
            'presentation_results', 
            'forma_farmaceutica', 
            ['ff_id', 'ff_nombre']
        );
        
        dropdownManager.create(
            'filtro_funcion', 
            'function_results', 
            'uso_farmacologico', 
            ['uf_id', 'uf_nombre']
        );
        
        dropdownManager.create(
            'filtro_via', 
            'via_results', 
            'via_de_administracion', 
            ['vd_id', 'vd_nombre']
        );

        // Dropdown de cliente
        new ClienteDropdownCaja();
    });
    </script>

    <script>
    // Navbar scroll behavior - Hide on scroll down, show on scroll up
    (function() {
        let lastScrollTop = 0;
        const topbar = document.getElementById('topbar');
        
        if (!topbar) return;

        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 60) {
                // Scroll hacia ABAJO - ocultar navbar
                topbar.style.transform = 'translateY(-100%)';
                topbar.style.transition = 'transform 0.25s ease-out';
            } else if (scrollTop < lastScrollTop) {
                // Scroll hacia ARRIBA - mostrar navbar EN CUALQUIER POSICION
                topbar.style.transform = 'translateY(0)';
                topbar.style.transition = 'transform 0.2s ease-in';
            }
            
            lastScrollTop = scrollTop;
        }, { passive: true });
    })();
    </script>

    <style>
    /* Estilos para nuevos dropdowns de caja */
    .dd-item {
        transition: background-color 0.2s;
    }
    .dd-item:hover {
        background-color: #f5f5f5;
    }
    .dd-empty {
        color: #666;
        font-style: italic;
    }
    .dd-cliente-item {
        transition: background-color 0.2s;
    }
    .dd-cliente-item:hover {
        background-color: #f5f5f5;
    }
    </style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnLimpiar = document.getElementById('btn_limpiar_busquedas_caja');
    if (!btnLimpiar) return;

    btnLimpiar.addEventListener('click', function() {
        // Limpiar inputs de búsqueda venta
        var idsVenta = ['filtro_proveedor', 'filtro_presentacion', 'filtro_funcion', 'filtro_via'];
        idsVenta.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        var medSearchVenta = document.querySelector('.med_search');
        if (medSearchVenta) medSearchVenta.value = '';

        // Limpiar inputs de cotización
        var medSearchQuote = document.querySelector('.med_search_quote');
        if (medSearchQuote) medSearchQuote.value = '';
        var proveedorQuote = document.getElementById('filtro_proveedor_quote');
        if (proveedorQuote) proveedorQuote.value = '';

        // Limpiar resultados desplegables
        var resultsVenta = document.getElementById('provider_results_venta');
        var resultsQuote = document.getElementById('provider_results_quote');
        var medResults = document.getElementById('med_search_results');
        var quoteResults = document.getElementById('quote_search_results');
        
        if (resultsVenta) { resultsVenta.innerHTML = ''; resultsVenta.style.display = 'none'; }
        if (resultsQuote) { resultsQuote.innerHTML = ''; resultsQuote.style.display = 'none'; }
        if (medResults) { medResults.innerHTML = ''; medResults.style.display = 'none'; }
        if (quoteResults) { quoteResults.innerHTML = ''; quoteResults.style.display = 'none'; }
    });
});
</script>