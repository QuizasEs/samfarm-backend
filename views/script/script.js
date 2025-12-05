// ===================================================
// 🌓 MODO OSCURO
// ===================================================
(function () {
    const toggle = document.querySelector('#darkModeToggleInput');
    const body = document.body;

    if (toggle && body) {
        // Inicializar estado
        loadDarkMode();

        // Detectar cambios
        toggle.addEventListener('change', () => {
            body.classList.toggle('dark');
            storeDarkMode(body.classList.contains('dark'));
        });
    }

    function loadDarkMode() {
        const darkmode = localStorage.getItem('dark') === 'true';
        body.classList.toggle('dark', darkmode);
        if (toggle) toggle.checked = darkmode;
    }

    function storeDarkMode(value) {
        localStorage.setItem('dark', value);
    }
})();

// ===================================================
// 📂 DESPLIEGUE DE SUBMENÚS EN SIDEBAR
// ===================================================
(function () {
    const menuItems = document.querySelectorAll(".sidebar .menu-item");

    if (menuItems.length > 0) {
        menuItems.forEach(item => {
            item.addEventListener("click", () => {
                const parent = item.closest(".link");
                if (!parent) return;

                // Cerrar los demás enlaces abiertos
                document.querySelectorAll(".sidebar .link.open").forEach(link => {
                    if (link !== parent) link.classList.remove("open");
                });

                // Alternar el actual
                parent.classList.toggle("open");
            });
        });
    }
})();

// ===================================================
// 🍔 BOTÓN HAMBURGUESA (mostrar/ocultar sidebar)
// ===================================================
(function () {
    const hamburguesa = document.querySelector('.hamburguesa');
    const sidebar = document.querySelector('.sidebar');

    if (hamburguesa && sidebar) {
        hamburguesa.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');

            // Detectar si está colapsado y aplicar inline styles solo si existen
            if (!sidebar.classList.contains('collapsed')) {
                if (sidebar.style) {
                    sidebar.style.transform = 'translateX(0)';
                    sidebar.style.width = '250px';
                    sidebar.style.padding = '10px 2px';
                }
            } else {
                if (sidebar.style) {
                    sidebar.style.transform = 'translateX(-300px)';
                    sidebar.style.width = '0';
                    sidebar.style.padding = '0';
                    sidebar.style.border = 'none';
                }
            }
        });
    }
})();


// ===================================================
// 📊 GRÁFICO DE INGRESOS Y EGRESOS (ECharts)
// ===================================================
(function () {
    const graphyc = document.getElementById('graphyc');

    if (graphyc && typeof echarts !== 'undefined') {
        const myChart = echarts.init(graphyc);

        const option = {
            title: { text: 'INGRESOS EGRESOS' },
            tooltip: {},
            legend: { data: ['egresos', 'ingresos'] },
            xAxis: {
                data: ['LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO']
            },
            yAxis: {},
            series: [
                {
                    name: 'egresos',
                    type: 'bar',
                    data: [5, 25, 36, 10, 10, 34, 1]
                },
                {
                    name: 'ingresos',
                    type: 'bar',
                    data: [1, 20, 56, 10, 13, 20, 1]
                }
            ]
        };

        myChart.setOption(option);
    }
})();
