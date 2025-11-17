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