<script>
    // funcionamiento del modo oscuro
    const toggle = document.querySelector('#darkModeToggleInput'); // el input real
    const body = document.querySelector('body');

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
</script>
<script>
    //funcionamiento del despligue de los sub links
    document.querySelectorAll(".sidebar .menu-item").forEach(item => {
        item.addEventListener("click", () => {
            const parent = item.closest(".link");

            // Cerrar todos los demás
            document.querySelectorAll(".sidebar .link").forEach(link => {
                if (link !== parent) link.classList.remove("open");
            });

            // Alternar el actual
            parent.classList.toggle("open");
        });
    });
</script>
<script>
    //funcionamiento del boton hamburguesa
    const hamburguesa = document.querySelector('.hamburguesa');
    const sidebar = document.querySelector('.sidebar');

    hamburguesa.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');

        // Forzar el estado visible en móviles cuando se quita 'collapsed'
        if (!sidebar.classList.contains('collapsed')) {
            sidebar.style.transform = 'translateX(0)';
            sidebar.style.width = '250px';
            sidebar.style.padding = '10px 2px';
        } else {
            sidebar.style.transform = '';
            sidebar.style.width = '';
            sidebar.style.padding = '';
            sidebar.style.border = '';
        }
    });
</script>

<script type="text/javascript">
    //crafica de ingresos y egresos 
    // Initialize the echarts instance based on the prepared dom
    var myChart = echarts.init(document.getElementById('graphyc'));

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
    } else {
        console.warn('Elementos de carga de imagen no encontrados en esta página');
    }
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
                resultados.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Use los filtros o busque por nombre</td></tr>';
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


            const filterSelects = document.querySelectorAll('select[name="Form_reg"], select[name="Via_reg"], select[name="Laboratorio_reg"], select[name="Uso_reg"]');
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


            if (!termino && !forma && !via && !laboratorio && !uso) {
                if (hasSearched) {
                    resultados.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Ingrese algún criterio de búsqueda</td></tr>';
                }
                return;
            }

            hasSearched = true;
            resultados.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Buscando...</td></tr>';

            try {
                const filtros = {
                    compraAjax: 'buscar_medicamentos',
                    termino: termino,
                    forma: forma,
                    via: via,
                    laboratorio: laboratorio,
                    uso: uso
                };

                console.log('Enviando filtros:', filtros);

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
                console.error('Error:', error);
                resultados.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> Error en la búsqueda</td></tr>';
            }
        }

        function renderResultados(data) {
            const resultados = document.getElementById('tablaMedicamentos');
            if (!resultados) return;

            if (!data || data.length === 0) {
                resultados.innerHTML = '<tr><td colspan="5" style="text-align:center;"><ion-icon name="pencil-outline"></ion-icon> No se encontraron resultados</td></tr>';
                return;
            }

            resultados.innerHTML = data.map((item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${escapeHtml(item.nombre || 'N/A')}</td>
            <td>${formatCurrency(item.precio || 0)}</td>
            <td>${item.stock || 'N/A'}</td>
            <td>
                <button type="button" class="btn primary" 
                        data-id="${item.med_id}"
                        data-nombre="${escapeHtml(item.nombre)}"
                        onclick="handleSelectItem(this)">
                    Seleccionar
                </button>
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
            return `$${num.toFixed(2)}`;
        }

        return {
            init
        };
    })();

    function handleSelectItem(button) {
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');
        console.log('Seleccionado:', {
            id,
            nombre
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        SearchManager.init();
    });
</script>

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
                console.warn("⚠️ No se encontró número de lote anterior. Se inicia desde MED-0000.");
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
                "fecha_vencimiento",
                "precio_compra",
                "precio_venta_reg",
                "cantidad_blister",
                "cantidad_unidades"
            ].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = "";
            });

            const check = document.getElementById("cb5");
            if (check) check.checked = false;
        }

        /** 🔓 Abre el modal de lote */
        function abrirModal(id, nombre) {
            if (!modal) return;
            modal.style.display = "flex";
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
            if (modal) modal.style.display = "none";
        }

        /** ✅ Valida datos antes de agregar lote */
        function validarCampos() {
            const numero = document.getElementById("numero_lote").value.trim();
            const cantidad = parseInt(document.getElementById("cantidad").value);
            const vencimiento = document.getElementById("fecha_vencimiento").value;
            const precioCompra = parseFloat(document.getElementById("precio_compra").value);
            const precioVenta = parseFloat(document.getElementById("precio_venta_reg").value);
            const cantidadBlister = parseInt(document.getElementById("cantidad_blister").value) || 0;
            const cantidadUnidades = parseInt(document.getElementById("cantidad_unidades").value) || 0;
            const activar = document.getElementById("cb5").checked ? 1 : 0;

            if (!numero) {
                alert("No se pudo generar el número de lote.");
                return false;
            }
            if (!cantidad || cantidad <= 0) {
                alert("La cantidad debe ser mayor a 0.");
                return false;
            }
            if (!vencimiento) {
                alert("Debe ingresar una fecha de vencimiento.");
                return false;
            }

            const fechaVenc = new Date(vencimiento);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            if (fechaVenc < hoy) {
                alert("La fecha de vencimiento no puede ser anterior a hoy.");
                return false;
            }

            if (!precioCompra || precioCompra <= 0) {
                alert("Precio de compra inválido.");
                return false;
            }
            if (!precioVenta || precioVenta <= 0) {
                alert("Precio de venta inválido.");
                return false;
            }

            return {
                numero,
                cantidad,
                cantidad_blister: cantidadBlister,
                cantidad_unidades: cantidadUnidades,
                vencimiento,
                precioCompra,
                precioVenta,
                activar_lote: activar
            };
        }

        /** ➕ Agrega un nuevo lote */
        function agregarLote() {
            const datos = validarCampos();
            if (!datos) return;

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
        }

        /** 🧾 Renderiza todos los lotes */
        function renderizarLista() {
            if (listaLotes.length === 0) {
                contenedorLista.innerHTML = "<p style='text-align:center; padding: 20px; color: #666;'>No hay lotes agregados aún.</p>";
                return;
            }

            contenedorLista.innerHTML = listaLotes.map((lote, i) => `
    <div class="item-lote" style="padding: 15px; margin-bottom: 10px; background: #f8f9fa; border-left: 4px solid #007bff; border-radius: 4px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="flex: 1;">
                <strong style="font-size: 16px; color: #333;">${i + 1}. ${lote.nombre}</strong>
                <span style="margin-left: 10px; font-size: 13px; font-weight: bold; color:${lote.activar_lote ? '#28a745' : '#dc3545'};">
                    [${lote.activar_lote ? 'Activo' : 'Inactivo'}]
                </span>
                <br>
                <div style="margin-top: 8px; font-size: 14px; color: #666;">
                    <span><ion-icon name="clipboard-outline"></ion-icon> <strong>Lote:</strong> ${lote.numero}</span>
                    <span style="margin-left: 15px;"><ion-icon name="cube-outline"></ion-icon> <strong>Cant:</strong> ${lote.cantidad}</span>
                    <span style="margin-left: 15px;"><ion-icon name="calendar-outline"></ion-icon> <strong>Vence:</strong> ${formatearFecha(lote.vencimiento)}</span>
                </div>
                <div style="margin-top: 5px; font-size: 14px; color: #666;">
                    <span><ion-icon name="cash-outline"></ion-icon> <strong>Compra:</strong> Bs. ${lote.precioCompra.toFixed(2)}</span>
                    <span style="margin-left: 15px;"><ion-icon name="pricetag-outline"></ion-icon> <strong>Venta:</strong> Bs. ${lote.precioVenta.toFixed(2)}</span>
                    <span style="margin-left: 15px;"><ion-icon name="card-outline"></ion-icon> <strong>Subtotal:</strong> Bs. ${(lote.cantidad * lote.precioCompra).toFixed(2)}</span>
                </div>
            </div>
            <div>
                <a href="javascript:void(0)" class="btn warning btn-sm" onclick="ModalManager.eliminarLote(${i})">
                    <ion-icon name="trash-outline"></ion-icon> Eliminar
                </a>
            </div>
        </div>
    </div>
`).join("");
        }

        function formatearFecha(fecha) {
            const [y, m, d] = fecha.split("-");
            return `${d}/${m}/${y}`;
        }

        /** 💰 Actualiza subtotales */
        function actualizarTotales() {
            const subtotal = listaLotes.reduce((t, l) => t + (l.cantidad * l.precioCompra), 0);
            const impuestos = subtotal * 0.13;
            const total = subtotal + impuestos;

            document.getElementById("subtotal").textContent = `Bs. ${subtotal.toFixed(2)}`;
            document.getElementById("impuestos").textContent = `Bs. ${impuestos.toFixed(2)}`;
            document.getElementById("total").textContent = `Bs. ${total.toFixed(2)}`;
        }

        /** ❌ Eliminar lote */
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
                const impuestos = subtotal * 0.13;
                return {
                    subtotal: subtotal.toFixed(2),
                    impuestos: impuestos.toFixed(2),
                    total: (subtotal + impuestos).toFixed(2),
                    cantidadLotes: listaLotes.length
                };
            }
        };
    })();

    /** 🌐 Vinculación externa */
    function handleSelectItem(button) {
        ModalManager.abrirModal(
            button.getAttribute("data-id"),
            button.getAttribute("data-nombre")
        );
    }

    function cerrarModal() {
        ModalManager.cerrarModal();
    }

    function agregarLote() {
        ModalManager.agregarLote();
    }

    document.addEventListener("DOMContentLoaded", () => {
        ModalManager.cerrarModal();
        const modal = document.getElementById("modalLote");
        if (modal) {
            modal.addEventListener("click", e => {
                if (e.target === modal) ModalManager.cerrarModal();
            });
        }
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

        function calcularSubtotal() {
            const lotes = ModalManager.obtenerLotes();
            return lotes.reduce((acc, lote) => acc + (lote.cantidad * lote.precioCompra), 0);
        }

        function calcularImpuestos(subtotal) {
            const valor = parseFloat(impuestosInput.value) || 0;
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
            document.getElementById('subtotal_total').value = subtotal.toFixed(2);
            document.getElementById('impuestos_total').value = impuestos.toFixed(2);
            document.getElementById('total_general').value = total.toFixed(2);
        }

        // Escuchar cambios en impuestos
        if (impuestosInput) {
            impuestosInput.addEventListener("input", actualizarTotales);
        }

        // Conectar con ModalManager
        function conectarConModal() {
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
                    alert('⚠️ Debe agregar al menos un lote antes de guardar la compra');
                    return false;
                }

                // Crear campo oculto con los lotes en JSON
                let inputLotes = document.getElementById('lotes_json');
                if (!inputLotes) {
                    inputLotes = document.createElement('input');
                    inputLotes.type = 'hidden';
                    inputLotes.name = 'lotes_json';
                    inputLotes.id = 'lotes_json';
                    this.appendChild(inputLotes);
                }
                inputLotes.value = JSON.stringify(lotes);

                // Crear campo con totales
                const totales = ModalManager.obtenerTotales();
                let inputTotales = document.getElementById('totales_json');
                if (!inputTotales) {
                    inputTotales = document.createElement('input');
                    inputTotales.type = 'hidden';
                    inputTotales.name = 'totales_json';
                    inputTotales.id = 'totales_json';
                    this.appendChild(inputTotales);
                }
                inputTotales.value = JSON.stringify(totales);

                console.log('📤 Enviando formulario con:', {
                    lotes: lotes,
                    totales: totales
                });

                // Tu clase FormularioAjax manejará el envío
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
            console.error('❌ No se encontró el campo #numero_compra en el DOM.');
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
            if (!ultimaCompraValor || ultimaCompraValor === '0') {
                console.warn('⚠️ No se detectó número de compra anterior. Se inicia desde COMP-' + añoActual + '-0001.');
            } else {
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
            console.log('✅ Número de compra generado:', nuevoCodigo);
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

            const modal = document.getElementById('modalActivarLote');
            modal.style.display = 'flex';

            document.getElementById('detalleLote').innerHTML = `
            <p><b>Lote:</b> #${id}</p>
            <p><b>Medicamento:</b> ${nombre}</p>
            <p>Confirma que deseas activar este lote. Esta acción no se puede deshacer.</p>
        `;

            document.getElementById('btnConfirmarActivacion').dataset.id = id;
        }

        // CERRAR MODAL CON BOTÓN (Cancelar o X)
        if (e.target.classList.contains('modal-close') || e.target.classList.contains('close')) {
            e.target.closest('.modal').style.display = 'none';
        }
    });

    // CONFIRMAR EN EL MODAL
    document.addEventListener('click', (e) => {
        if (e.target.id === 'btnConfirmarActivacion') {
            const id = e.target.dataset.id;
            if (!id) return;

            Swal.fire({
                title: "¿Activar este lote?",
                text: "Solo se puede activar una vez.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
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
            text: 'Activando lote',
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
                icon: data.Tipo || 'info'
            });

            if (data.Alerta === 'recargar' || data.Tipo === 'success') {
                document.getElementById('modalActivarLote').style.display = 'none';
                document.querySelector('.filtro-dinamico .btn-search')?.click();
            }

        } catch (err) {
            console.error(err);
            Swal.fire("Error", "No se pudo procesar la solicitud: " + err.message, "error");
        }
    }

    // CERRAR MODAL AL HACER CLICK EN EL FONDO
    document.addEventListener("click", (e) => {
        const modal = document.getElementById("modalActivarLote");

        if (modal.style.display !== "flex") return;

        // Si se hace clic fuera del contenido → cerrar
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });
</script>


<!-- script que maneja el modal de agregar nuevo cliente -->
<script>
    (function() {
        // Estado interno
        let modal = null;
        let initialized = false;

        function initIfNeeded() {
            if (initialized) return;
            modal = document.getElementById('modalCliente');
            // Si todavía no existe, esperar al DOMContentLoaded
            if (!modal) {
                document.addEventListener('DOMContentLoaded', () => {
                    modal = document.getElementById('modalCliente');
                    setupModal();
                }, {
                    once: true
                });
                initialized = true; // marcaremos inicializado para no agregar más listeners aquí
                return;
            }
            setupModal();
        }

        function setupModal() {
            if (!modal) return; // nada que hacer si no existe
            // cerrar al click fuera
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    api.cerrarModal();
                }
            });

            // Si tienes un botón con clase .close dentro, lo conectamos si existe
            const closeBtns = modal.querySelectorAll('.close, [data-close="modalCliente"]');
            closeBtns.forEach(btn => btn.addEventListener('click', (e) => {
                e.preventDefault();
                api.cerrarModal();
            }));

            initialized = true;
        }

        // Funciones públicas que aseguran init
        function abrirModal() {
            initIfNeeded();
            if (!modal) {
                console.warn('ModalCliente: #modalCliente no encontrado al intentar abrir.');
                return;
            }
            modal.style.display = 'flex';
        }

        function cerrarModal() {
            initIfNeeded();
            if (!modal) {
                // intento fallback: cerrar cualquier modal visible
                const visible = Array.from(document.querySelectorAll('.modal')).find(m => window.getComputedStyle(m).display !== 'none');
                if (visible) visible.style.display = 'none';
                return;
            }
            modal.style.display = 'none';
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
                } catch (e) {
                    console.error(e);
                }
            };
        }
        if (typeof window.cerrarModal !== 'function') {
            window.cerrarModal = () => {
                try {
                    window.ModalCliente.cerrarModal();
                } catch (e) {
                    console.error(e);
                }
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

<!-- script que manea ja busqueda de medicamentos lista de compras y envio por post -->
<script>
    // Script corregido para el control de stock en ventas
    // Script completo de ventas con búsqueda por lote y tiempo real
    (function() {
        const formVenta = document.getElementById('form-venta-caja');
        if (!formVenta) {
            console.log('⚠️ Formulario de venta no encontrado, script no se ejecuta');
            return;
        }

        console.log('✅ Script de ventas inicializado');

        const URL_MED = "<?php echo SERVER_URL ?>ajax/ventaAjax.php";
        let cart = [];
        let debounce = null;

        function $(s) {
            return formVenta.querySelector(s);
        }

        function $all(s) {
            return Array.from(formVenta.querySelectorAll(s));
        }

        function ensureHidden(name, id) {
            let el = formVenta.querySelector('input[name="' + name + '"]');
            if (!el) {
                el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                if (id) el.id = id;
                formVenta.appendChild(el);
            }
            return el;
        }

        const itemsHidden = ensureHidden('venta_items_json', 'venta_items_json');
        const subtotalHidden = ensureHidden('subtotal_venta', 'subtotal_venta');
        const totalHidden = ensureHidden('total_venta', 'total_venta');
        const cambioHidden = ensureHidden('cambio_venta', 'cambio_venta');
        const dineroHidden = ensureHidden('dinero_recibido_venta', 'dinero_recibido_venta');

        const medSearch = $('#med_search') || $('.caja-filtro-search input');
        const filtro_linea = $('#filtro_linea');
        const filtro_presentacion = $('#filtro_presentacion');
        const filtro_funcion = $('#filtro_funcion');
        const filtro_via = $('#filtro_via');

        let resultsContainer = $('#med_search_results');
        if (!resultsContainer && medSearch) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'med_search_results';
            resultsContainer.className = 'search-results-dropdown';

            resultsContainer.style.cssText = `
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

            const parent = medSearch.parentElement;
            parent.style.position = 'relative';
            parent.appendChild(resultsContainer);
        }

        const tablaBody = $('#tabla_items_venta');
        const tablaMasVendidos = $('#tabla_mas_vendidos');
        const inputDinero = $('#input_dinero_recibido');
        const subtotalText = $('#subtotal_texto');
        const totalText = $('#total_texto');
        const cambioText = $('#cambio_texto');

        function formatMoney(n) {
            return Number(n || 0).toFixed(2);
        }

        function escapeHtml(s) {
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

        function renderCart() {
            if (!tablaBody) return;

            tablaBody.innerHTML = '';

            if (cart.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#666;padding:12px">No hay medicamentos en la lista</td></tr>';
            } else {
                cart.forEach((it, i) => {
                    const tr = document.createElement('tr');
                    tr.dataset.med = it.med_id;
                    tr.dataset.lote = it.lote_id || '';

                    // Mostrar nombre con información del lote si existe
                    let nombreDisplay = escapeHtml(it.nombre);
                    if (it.lote) {
                        nombreDisplay += '<br><small style="color: #666;">' +
                            '<ion-icon name="barcode-outline"></ion-icon> ' +
                            escapeHtml(it.lote) +
                            (it.linea ? ' | ' + escapeHtml(it.linea) : '') +
                            '</small>';
                    }

                    tr.innerHTML =
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + nombreDisplay + '</td>' +
                        '<td>' + escapeHtml(it.presentacion || '') + '</td>' +
                        '<td><div class="table-cantidad">' +
                        '<button type="button" class="qty-dec" data-index="' + i + '">' +
                        '<ion-icon name="remove-outline"></ion-icon>' +
                        '</button>' +
                        '<input type="number" class="qty-input" data-index="' + i + '" value="' + it.cantidad + '" min="1">' +
                        '<button type="button" class="qty-inc" data-index="' + i + '">' +
                        '<ion-icon name="add-outline"></ion-icon>' +
                        '</button>' +
                        '</div></td>' +
                        '<td>' + formatMoney(it.precio) + '</td>' +
                        '<td>' + formatMoney(it.precio * it.cantidad) + '</td>';

                    tablaBody.appendChild(tr);
                });
            }

            itemsHidden.value = JSON.stringify(cart.map(i => ({
                med_id: i.med_id,
                lote_id: i.lote_id || null,
                cantidad: i.cantidad,
                precio: Number(i.precio),
                subtotal: Number((i.precio * i.cantidad).toFixed(2))
            })));

            updateTotals();
            attachQtyEvents();
        }

        function attachQtyEvents() {
            $all('.qty-inc').forEach(b => {
                b.onclick = function() {
                    const idx = parseInt(this.dataset.index);
                    changeQtyByIndex(idx, 1);
                };
            });
            $all('.qty-dec').forEach(b => {
                b.onclick = function() {
                    const idx = parseInt(this.dataset.index);
                    changeQtyByIndex(idx, -1);
                };
            });
            $all('.qty-input').forEach(i => {
                i.onchange = function() {
                    const idx = parseInt(this.dataset.index);
                    setQtyByIndex(idx, parseInt(this.value) || 0);
                };
            });
        }

        function changeQtyByIndex(idx, delta) {
            if (idx < 0 || idx >= cart.length) return;

            const item = cart[idx];
            const nuevo = item.cantidad + delta;

            if (nuevo <= 0) {
                Swal.fire({
                    title: '¿Eliminar medicamento?',
                    text: 'La cantidad llegaría a 0. ¿Deseas eliminarlo?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(r => {
                    if (r.isConfirmed) {
                        cart.splice(idx, 1);
                        renderCart();
                    } else {
                        renderCart();
                    }
                });
                return;
            }

            if (item.stock != null && nuevo > item.stock) {
                Swal.fire({
                    title: 'Sin stock suficiente',
                    html: `<p><strong>${escapeHtml(item.nombre)}</strong></p>
                       ${item.lote ? '<p>Lote: <strong>' + escapeHtml(item.lote) + '</strong></p>' : ''}
                       <p>Stock disponible: <strong>${item.stock}</strong> unidades</p>
                       <p>Intentando agregar: <strong>${nuevo}</strong> unidades</p>`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            item.cantidad = nuevo;
            renderCart();
        }

        function setQtyByIndex(idx, val) {
            if (idx < 0 || idx >= cart.length) return;

            const item = cart[idx];

            if (val <= 0) {
                Swal.fire({
                    title: 'Cantidad 0',
                    text: '¿Eliminar este medicamento?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then(r => {
                    if (r.isConfirmed) {
                        cart.splice(idx, 1);
                        renderCart();
                    } else {
                        item.cantidad = 1;
                        renderCart();
                    }
                });
                return;
            }

            if (item.stock != null && val > item.stock) {
                Swal.fire({
                    title: 'Sin stock suficiente',
                    html: `<p><strong>${escapeHtml(item.nombre)}</strong></p>
                       ${item.lote ? '<p>Lote: <strong>' + escapeHtml(item.lote) + '</strong></p>' : ''}
                       <p>Stock disponible: <strong>${item.stock}</strong> unidades</p>
                       <p>Cantidad ingresada: <strong>${val}</strong> unidades</p>`,
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                item.cantidad = item.stock > 0 ? item.stock : 1;
                renderCart();
                return;
            }

            item.cantidad = val;
            renderCart();
        }

        function updateTotals() {
            const subtotal = cart.reduce((s, i) => s + (i.precio * i.cantidad), 0);
            const total = subtotal;
            subtotalHidden.value = subtotal.toFixed(2);
            totalHidden.value = total.toFixed(2);
            if (subtotalText) subtotalText.textContent = 'Bs. ' + formatMoney(subtotal);
            if (totalText) totalText.textContent = 'Bs. ' + formatMoney(total);
            const dinero = Number(inputDinero ? inputDinero.value : 0);
            const cambio = dinero - total;
            cambioHidden.value = (cambio > 0 ? cambio : 0).toFixed(2);
            if (cambioText) cambioText.textContent = (isNaN(cambio) ? '0.00' : formatMoney(Math.max(0, cambio)));
            if (dineroHidden) dineroHidden.value = dinero;
        }

        function addItem(m) {
            // Buscar si existe el mismo medicamento Y lote
            const idx = cart.findIndex(c =>
                String(c.med_id) === String(m.med_id) &&
                (m.lote_id ? String(c.lote_id) === String(m.lote_id) : !c.lote_id)
            );

            if (idx !== -1) {
                const ex = cart[idx];
                const nuevaCantidad = ex.cantidad + 1;

                if (m.stock != null && nuevaCantidad > m.stock) {
                    Swal.fire({
                        title: 'Sin stock suficiente',
                        html: `<p><strong>${escapeHtml(m.nombre)}</strong></p>
                           ${m.lote ? '<p>Lote: <strong>' + escapeHtml(m.lote) + '</strong></p>' : ''}
                           <p>Stock disponible: <strong>${m.stock}</strong> unidades</p>
                           <p>Ya tienes <strong>${ex.cantidad}</strong> en el carrito</p>`,
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                ex.cantidad = nuevaCantidad;
            } else {
                if (m.stock != null && m.stock <= 0) {
                    Swal.fire({
                        title: 'Sin stock',
                        html: `<p><strong>${escapeHtml(m.nombre)}</strong></p>
                           ${m.lote ? '<p>Lote: <strong>' + escapeHtml(m.lote) + '</strong></p>' : ''}
                           <p>Este ${m.lote ? 'lote' : 'medicamento'} no tiene stock disponible</p>`,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }

                cart.push({
                    med_id: m.med_id,
                    lote_id: m.lote_id || null,
                    lote: m.lote || null,
                    nombre: m.nombre,
                    presentacion: m.presentacion || '',
                    linea: m.linea || '',
                    precio: parseFloat(m.precio) || 0,
                    cantidad: 1,
                    stock: m.stock != null ? Number(m.stock) : null
                });
            }
            renderCart();
        }

        function doSearch(term) {
            if (!term || term.trim().length < 1) {
                if (resultsContainer) {
                    resultsContainer.innerHTML = '';
                    resultsContainer.style.display = 'none';
                }
                return;
            }

            const body = new URLSearchParams();
            body.append('ventaAjax', 'buscar');
            body.append('termino', term);
            if (filtro_linea && filtro_linea.value) body.append('linea', filtro_linea.value);
            if (filtro_presentacion && filtro_presentacion.value) body.append('presentacion', filtro_presentacion.value);
            if (filtro_funcion && filtro_funcion.value) body.append('funcion', filtro_funcion.value);
            if (filtro_via && filtro_via.value) body.append('via', filtro_via.value);

            fetch(URL_MED, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            }).then(r => r.json()).then(json => {
                console.log('Resultados recibidos:', json);
                renderResults(json || []);
            }).catch(err => {
                console.error('Error en búsqueda:', err);
                if (resultsContainer) {
                    resultsContainer.innerHTML = '<div class="search-results-item no-results">Error en la búsqueda</div>';
                    resultsContainer.style.display = 'block';
                }
            });
        }

        function renderResults(items) {
            if (!resultsContainer) return;

            if (!items || items.length === 0) {
                resultsContainer.innerHTML = '<div class="search-results-item no-results">No se encontraron resultados</div>';
                resultsContainer.style.display = 'block';
                return;
            }

            resultsContainer.innerHTML = items.map(it => {
                const nombre = escapeHtml(it.nombre || '');
                const lote = escapeHtml(it.lm_numero_lote || '');
                const presentacion = escapeHtml(it.presentacion || 'Sin presentación');
                const linea = escapeHtml(it.linea || 'Sin laboratorio');
                const precio = formatMoney(it.precio_venta || 0);
                const stock = Number(it.stock || 0);

                // Calcular días hasta vencimiento
                let diasVenc = '';
                if (it.fecha_vencimiento) {
                    const hoy = new Date();
                    const vence = new Date(it.fecha_vencimiento);
                    const diff = Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24));

                    if (diff < 0) {
                        diasVenc = '<span style="color: red; font-weight: bold;">⚠ VENCIDO</span>';
                    } else if (diff <= 30) {
                        diasVenc = `<span style="color: orange; font-weight: bold;">⚠ ${diff}d</span>`;
                    } else if (diff <= 90) {
                        diasVenc = `<span style="color: #ff9800;">${diff}d</span>`;
                    }
                }

                const stockText = stock > 0 ?
                    `<span style="color: #4caf50;">Stock: ${stock}</span>` :
                    '<span style="color: red;">Sin stock</span>';

                const sinStock = stock <= 0 ? 'sin-stock' : '';

                return `<div class="search-results-item ${sinStock}" 
                data-id="${it.med_id}" 
                data-lote-id="${it.lm_id || ''}"
                data-lote="${lote}"
                data-nombre="${nombre}" 
                data-presentacion="${presentacion}" 
                data-linea="${linea}"
                data-precio="${it.precio_venta || 0}"
                data-stock="${stock}">
                
                <div class="search-result-name">
                    <strong>${nombre}</strong>
                    <span style="font-size: 0.85em; color: #666; margin-left: 8px;">(${linea})</span>
                </div>
                
                <div class="search-result-details" style="font-size: 0.9em; color: #555;">
                    <span><ion-icon name="barcode-outline"></ion-icon> ${lote}</span>
                    <span style="margin: 0 6px;">•</span>
                    <span>${presentacion}</span>
                    <span style="margin: 0 6px;">•</span>
                    <span><ion-icon name="pricetag-outline"></ion-icon> Bs. ${precio}</span>
                    <span style="margin: 0 6px;">•</span>
                    ${stockText}
                    ${diasVenc ? '<span style="margin: 0 6px;">•</span>' + diasVenc : ''}
                </div>
            </div>`;
            }).join('');

            resultsContainer.style.display = 'block';

            resultsContainer.querySelectorAll('.search-results-item:not(.no-results)').forEach(el => {
                el.addEventListener('click', () => {
                    const stock = Number(el.dataset.stock || 0);

                    addItem({
                        med_id: el.dataset.id,
                        lote_id: el.dataset.loteId || null,
                        lote: el.dataset.lote || null,
                        nombre: el.dataset.nombre,
                        presentacion: el.dataset.presentacion,
                        linea: el.dataset.linea,
                        precio: parseFloat(el.dataset.precio || 0),
                        stock: stock
                    });

                    resultsContainer.innerHTML = '';
                    resultsContainer.style.display = 'none';
                    if (medSearch) medSearch.value = '';
                });
            });
        }

        if (medSearch) {
            medSearch.addEventListener('input', e => {
                const term = e.target.value.trim();
                clearTimeout(debounce);

                if (term.length === 0) {
                    if (resultsContainer) {
                        resultsContainer.innerHTML = '';
                        resultsContainer.style.display = 'none';
                    }
                    return;
                }

                // Búsqueda en tiempo real después de 250ms
                debounce = setTimeout(() => doSearch(term), 250);
            });

            medSearch.addEventListener('focus', function() {
                if (this.value.trim().length > 0 && resultsContainer && resultsContainer.innerHTML) {
                    resultsContainer.style.display = 'block';
                }
            });
        }

        [filtro_linea, filtro_presentacion, filtro_funcion, filtro_via].forEach(sel => {
            if (sel) sel.addEventListener('change', () => {
                if (medSearch && medSearch.value) doSearch(medSearch.value);
            });
        });

        function loadMostSold() {
            const body = new URLSearchParams();
            body.append('ventaAjax', 'mas_vendidos');
            body.append('limit', '5');

            fetch(URL_MED, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            }).then(r => r.json()).then(json => {
                if (!tablaMasVendidos) return;

                tablaMasVendidos.innerHTML = (json || []).map((it, i) => {
                    const stock = Number(it.stock || 0);
                    const stockClass = stock <= 0 ? 'sin-stock' : '';
                    const stockText = stock > 0 ? `(Stock: ${stock})` : '<span style="color: red;">(Sin stock)</span>';

                    return `<tr data-id="${it.med_id}" class="${stockClass}">
                    <td>${i + 1}</td>
                    <td>${escapeHtml(it.nombre)}</td>
                    <td>Bs. ${formatMoney(it.precio_venta)} ${stockText}</td>
                    <td>
                        <a href="#" 
                           class="btn caja btn-add" 
                           data-id="${it.med_id}" 
                           data-nombre="${escapeHtml(it.nombre)}" 
                           data-precio="${it.precio_venta}"
                           data-stock="${stock}">
                            agregar
                        </a>
                    </td>
                </tr>`;
                }).join('');

                tablaMasVendidos.querySelectorAll('.btn-add').forEach(b => {
                    b.addEventListener('click', e => {
                        e.preventDefault();
                        const el = e.currentTarget;
                        const stock = Number(el.dataset.stock || 0);

                        addItem({
                            med_id: el.dataset.id,
                            lote_id: null,
                            lote: null,
                            nombre: el.dataset.nombre,
                            presentacion: '',
                            linea: '',
                            precio: parseFloat(el.dataset.precio || 0),
                            stock: stock
                        });
                    });
                });
            }).catch(err => console.error(err));
        }

        if (inputDinero) inputDinero.addEventListener('input', updateTotals);

        formVenta.addEventListener('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                Swal.fire('Carrito vacío', 'Agrega al menos un medicamento para realizar la venta.', 'warning');
                return;
            }

            updateTotals();
        });

        loadMostSold();
        renderCart();

        document.addEventListener('click', function(e) {
            if (resultsContainer &&
                !resultsContainer.contains(e.target) &&
                e.target !== medSearch) {
                resultsContainer.style.display = 'none';
            }
        });

        window.VentaCaja = {
            addItem,
            cart,
            renderCart,
            updateTotals
        };
    })();
</script>

<!-- script busqueda de cliente para caja -->
<script>
    // Script mejorado para búsqueda de clientes
    (function() {
        const URL_CLI = "<?php echo SERVER_URL ?>ajax/ventaAjax.php";
        const inputCliente = document.getElementById("buscar_cliente_venta");
        let resultadoClientes = document.getElementById("resultado_clientes");

        let clienteSeleccionado = null;
        let debounceCliente = null;

        // Verificar si el contenedor existe, si no, crearlo
        if (!resultadoClientes) {
            resultadoClientes = document.createElement('div');
            resultadoClientes.id = 'resultado_clientes';
            resultadoClientes.className = 'resultado-busqueda';
        }

        // CRÍTICO: Encontrar el contenedor correcto (.ventas-cliente)
        const ventasClienteContainer = inputCliente.closest('.ventas-cliente');

        if (ventasClienteContainer) {
            // Asegurar position relative en el contenedor
            ventasClienteContainer.style.position = 'relative';

            // Insertar el dropdown como hijo directo del contenedor
            if (!ventasClienteContainer.contains(resultadoClientes)) {
                ventasClienteContainer.appendChild(resultadoClientes);
            }
        } else if (inputCliente.parentElement) {
            // Fallback: usar el padre directo
            inputCliente.parentElement.style.position = 'relative';
            if (!inputCliente.parentElement.contains(resultadoClientes)) {
                inputCliente.parentElement.appendChild(resultadoClientes);
            }
        }

        // Aplicar estilos críticos al contenedor
        resultadoClientes.style.cssText = `
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

        console.log("📍 Contenedor de resultados posicionado en:", ventasClienteContainer || inputCliente.parentElement);

        // Obtener contenedor de cliente seleccionado
        const clienteSeleccionadoContainer = document.getElementById('cliente_seleccionado_container');
        const clienteNombreTexto = document.getElementById('cliente_nombre_texto');
        const quitarClienteBtn = document.getElementById('quitar_cliente_btn');
        const clienteIdHidden = document.getElementById('cliente_id_seleccionado');

        // Event listener para búsqueda en tiempo real (sin botón)
        if (inputCliente) {
            inputCliente.addEventListener("input", function() {
                const termino = this.value.trim();
                clearTimeout(debounceCliente);

                console.log("⌨️ Escribiendo:", termino);

                if (termino.length < 1) {
                    resultadoClientes.innerHTML = "";
                    resultadoClientes.style.display = "none";
                    return;
                }

                // Buscar después de 250ms (búsqueda en tiempo real)
                debounceCliente = setTimeout(() => {
                    console.log("🚀 Iniciando búsqueda automática");
                    buscarClientes(termino);
                }, 250);
            });

            inputCliente.addEventListener("focus", function() {
                if (this.value.trim().length > 0 && resultadoClientes.innerHTML) {
                    resultadoClientes.style.display = "block";
                }
            });

            // Prevenir que el Enter envíe el formulario desde este input
            inputCliente.addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    // Si hay un resultado visible, seleccionar el primero
                    const primerResultado = resultadoClientes.querySelector('.cliente-item');
                    if (primerResultado && resultadoClientes.style.display === "block") {
                        seleccionarCliente(primerResultado);
                    }
                }
            });
        }

        async function buscarClientes(termino) {
            console.log("🔍 Buscando clientes:", termino);

            const formData = new FormData();
            formData.append("ventaAjax", "buscar_cliente");
            formData.append("termino", termino);

            try {
                const response = await fetch(URL_CLI, {
                    method: "POST",
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const text = await response.text();
                console.log("📥 Respuesta:", text);

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("❌ Error parseando JSON:", e);
                    data = [];
                }

                console.log("✅ Datos parseados:", data);
                mostrarResultadosClientes(data);

            } catch (error) {
                console.error("❌ Error buscando clientes:", error);
                resultadoClientes.innerHTML = '<div class="search-results-item no-results">Error en la búsqueda</div>';
                resultadoClientes.style.display = "block";
            }
        }

        function mostrarResultadosClientes(clientes) {
            console.log("📋 Mostrando resultados:", clientes);

            if (!clientes || clientes.length === 0) {
                resultadoClientes.innerHTML = '<div class="search-results-item no-results">No se encontraron clientes</div>';
                resultadoClientes.style.display = "block";
                return;
            }

            // Generar HTML usando la misma estructura que medicamentos
            const html = clientes.map(cli => {
                const nombreCompleto = `${cli.cl_nombres || ''} ${cli.cl_apellido_paterno || ''} ${cli.cl_apellido_materno || ''}`.trim();
                const carnet = cli.cl_carnet || 'Sin CI';
                const telefono = cli.cl_telefono ? ` · ${cli.cl_telefono}` : '';

                return `
                <div class="search-results-item cliente-item" 
                    data-id="${cli.cl_id}" 
                    data-nombre="${escapeHtml(nombreCompleto)}">
                    <div class="search-result-name">
                        <ion-icon name="person-circle-outline" style="vertical-align: middle; margin-right: 4px;"></ion-icon>
                        ${escapeHtml(nombreCompleto)}
                    </div>
                    <div class="search-result-details">CI: ${escapeHtml(carnet)}${escapeHtml(telefono)}</div>
                </div>`;
            }).join('');

            resultadoClientes.innerHTML = html;
            resultadoClientes.style.display = "block";

            console.log("✅ HTML insertado, display:", resultadoClientes.style.display);

            // Agregar event listeners a los items
            resultadoClientes.querySelectorAll('.cliente-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    seleccionarCliente(this);
                });
            });
        }

        function seleccionarCliente(item) {
            const id = item.dataset.id;
            const nombre = item.dataset.nombre;

            console.log("👤 Cliente seleccionado:", {
                id,
                nombre
            });

            // Guardar cliente seleccionado
            clienteSeleccionado = {
                id,
                nombre
            };

            // Mostrar en la interfaz
            if (clienteNombreTexto) clienteNombreTexto.textContent = nombre;
            if (clienteIdHidden) clienteIdHidden.value = id;
            if (clienteSeleccionadoContainer) clienteSeleccionadoContainer.style.display = "block";

            // Limpiar búsqueda
            resultadoClientes.innerHTML = "";
            resultadoClientes.style.display = "none";
            if (inputCliente) inputCliente.value = "";
        }

        // Quitar cliente seleccionado
        if (quitarClienteBtn) {
            quitarClienteBtn.addEventListener("click", function(e) {
                e.preventDefault();
                clienteSeleccionado = null;
                if (clienteIdHidden) clienteIdHidden.value = "";
                if (clienteSeleccionadoContainer) clienteSeleccionadoContainer.style.display = "none";
                if (inputCliente) {
                    inputCliente.value = "";
                    inputCliente.focus();
                }
            });
        }

        // Cerrar resultados al hacer click fuera
        document.addEventListener("click", function(e) {
            if (resultadoClientes &&
                resultadoClientes.style.display === "block" &&
                !inputCliente.contains(e.target) &&
                !resultadoClientes.contains(e.target)) {
                resultadoClientes.style.display = "none";
            }
        });

        // Asegurar que el formulario envíe el cliente_id
        const formVenta = document.querySelector('.form.FormularioAjax');
        if (formVenta) {
            formVenta.addEventListener('submit', function(e) {
                if (clienteSeleccionado && clienteIdHidden) {
                    clienteIdHidden.value = clienteSeleccionado.id;
                }
            });
        }

        // Función helper para escapar HTML
        function escapeHtml(text) {
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

        // Exponer funciones globalmente si es necesario
        window.ClienteBusqueda = {
            seleccionarCliente,
            clienteSeleccionado: () => clienteSeleccionado
        };

        console.log("✅ Script de búsqueda de clientes inicializado");
    })();
</script>
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

            console.log('PDF abierto exitosamente');
            return true;

        } catch (error) {
            console.error('Error abriendo PDF:', error);
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
    const InventarioModals = (function() {
        'use strict';

        const API_URL = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php';

        // ==================== UTILIDADES ====================
        const utils = {
            async ajax(params) {
                try {
                    console.log('📡 Enviando petición:', params);

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
                    console.log('✅ Respuesta recibida:', data);
                    return data;

                } catch (error) {
                    console.error('❌ Error AJAX:', error);
                    throw error;
                }
            },

            abrir(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'flex';
                    console.log(`✅ Modal abierto: ${modalId}`);
                } else {
                    console.error(`❌ Modal no encontrado: ${modalId}`);
                }
            },

            cerrar(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                    console.log(`✅ Modal cerrado: ${modalId}`);
                }
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
                console.log('📋 Abriendo detalle:', {
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

                // Mostrar loading
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

                    document.getElementById('detalleLaboral').textContent = data.laboratorio || 'N/A';
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
                    console.error('❌ Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
                }
            }
        };

        // ==================== MODAL TRANSFERIR ====================
        const transferir = {
            async abrir(invId, medId, suId, medicamento) {
                console.log('🔄 Abriendo transferencia:', {
                    invId,
                    medId,
                    suId,
                    medicamento
                });

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
                    console.error('❌ Error:', error);
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
                console.log('📜 Abriendo historial:', {
                    medId,
                    suId,
                    medicamento
                });

                document.getElementById('modalHistorialMedicamento').textContent = medicamento;
                document.getElementById('modalHistorialMedId').value = medId;
                document.getElementById('modalHistorialSuId').value = suId;

                utils.abrir('modalHistorialInventario');

                document.getElementById('tablaHistorialMovimientos').innerHTML =
                    '<tr><td colspan="6" style="text-align:center;"><ion-icon name="hourglass-outline"></ion-icon> Cargando...</td></tr>';

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
                                <td>${mov.usuario || 'Sistema'}</td>
                                <td>${mov.motivo || '-'}</td>
                            </tr>
                        `;
                        }).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="information-circle-outline"></ion-icon> Sin movimientos</td></tr>';
                    }

                } catch (error) {
                    console.error('❌ Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el historial', 'error');
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
            verHistorial: historial.abrir
        };
    })();

    /* exportar excel */
    document.addEventListener('DOMContentLoaded', function() {
        const btnExcel = document.getElementById('btnExportarExcel');

        if (btnExcel) {
            btnExcel.addEventListener('click', function() {
                const sucursalSelect = document.querySelector('select[name="select3"]');
                const sucursalId = sucursalSelect ? sucursalSelect.value : '';

                const url = '<?php echo SERVER_URL; ?>ajax/inventarioAjax.php?inventarioAjax=exportar_excel' +
                    (sucursalId ? '&su_id=' + sucursalId : '');

                console.log('📥 Descargando archivo:', url);

                // Abrir en nueva ventana para forzar descarga
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


    (function() {
        const modalIds = ['modalDetalleInventario', 'modalTransferirInventario', 'modalHistorialInventario'];

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
            console.log('CajaHistorial inicializado');
            console.log('API_URL:', API_URL);
            cargarResumen();
            cargarGrafico();
            configurarEventos();
        }

        function configurarEventos() {
            const btnExcel = document.getElementById('btnExportarExcelCajaHistorial');
            const btnPDF = document.getElementById('btnExportarPDFCajaHistorial');

            console.log('Botón Excel encontrado:', !!btnExcel);
            console.log('Botón PDF encontrado:', !!btnPDF);

            if (btnExcel) {
                btnExcel.addEventListener('click', exportarExcel);
            }

            if (btnPDF) {
                btnPDF.addEventListener('click', exportarPDF);
            }

            const filtros = document.querySelectorAll('.filtro-dinamico select, .filtro-dinamico input[type="date"]');
            filtros.forEach(filtro => {
                filtro.addEventListener('change', function() {
                    console.log('Filtro cambiado:', this.name, this.value);
                    cargarResumen();
                    cargarGrafico();
                });
            });
        }

        async function cargarResumen() {
            const formData = obtenerFiltros();
            formData.append('cajaHistorialAjax', 'resumen');

            console.log('Cargando resumen con filtros:', Object.fromEntries(formData));

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                });

                console.log('Respuesta resumen status:', response.status);
                const data = await response.json();
                console.log('Datos resumen:', data);

                if (data.error) {
                    console.error('Error en resumen:', data.error);
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

            } catch (error) {
                console.error('Error cargando resumen:', error);
            }
        }

        async function cargarGrafico() {
            const formData = obtenerFiltros();
            formData.append('cajaHistorialAjax', 'grafico');

            console.log('Cargando gráfico con filtros:', Object.fromEntries(formData));

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(formData)
                });

                console.log('Respuesta gráfico status:', response.status);
                const datos = await response.json();
                console.log('Datos gráfico:', datos);

                if (datos.error) {
                    console.error('Error en gráfico:', datos.error);
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

            } catch (error) {
                console.error('Error cargando gráfico:', error);
            }
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
            console.log('Exportando movimiento:', url);

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

            console.log('Exportando Excel:', url);

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

            console.log('Exportando PDF:', url);

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