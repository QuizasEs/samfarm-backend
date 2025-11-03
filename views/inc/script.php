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
        /**
         * 🚀 SEARCH MANAGER - Script Corregido
         * - No muestra resultados cuando el input está vacío
         * - Filtrado correcto por selects
         * - Validación de búsquedas vacías
         */

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
                // Botón de búsqueda
                const searchBtn = document.querySelector('.btn-search');
                if (searchBtn) {
                    searchBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        buscar();
                    });
                }

                // Búsqueda por término - SOLO si hay texto
                const searchInput = document.getElementById('buscarMedicamento');
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        if (currentTimeout) clearTimeout(currentTimeout);

                        const termino = this.value.trim();
                        // Solo buscar si hay término O si ya se ha buscado antes
                        if (termino.length > 0 || hasSearched) {
                            currentTimeout = setTimeout(() => buscar(), 300);
                        }
                    });
                }

                // Cambios en selects - SIEMPRE buscar
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

                // Obtener valores actuales
                const termino = document.getElementById('buscarMedicamento')?.value.trim() || '';
                const forma = document.querySelector('select[name="Form_reg"]')?.value || '';
                const via = document.querySelector('select[name="Via_reg"]')?.value || '';
                const laboratorio = document.querySelector('select[name="Laboratorio_reg"]')?.value || '';
                const uso = document.querySelector('select[name="Uso_reg"]')?.value || '';

                // ✅ VALIDACIÓN: No buscar si no hay criterios
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

                    console.log('Enviando filtros:', filtros); // Para debug

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