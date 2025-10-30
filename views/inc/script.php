    
    <script> // funcionamiento del modo oscuro
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
    <script>//funcionamiento del despligue de los sub links
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
    <script> //funcionamiento del boton hamburguesa
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

    <script type="text/javascript"> //crafica de ingresos y egresos 
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
            series: [
                {
                    name: 'egresos',
                    type: 'bar',
                    data: [5, 25, 36, 10, 10, 34, 1]
                },{
                    name: 'ingresos',
                    type: 'bar',
                    data: [1, 20, 56, 10, 13, 20, 1]
                }
            ]
        };

        // Display the chart using the configuration items and data just specified.
        myChart.setOption(option);
    </script>
    <script src="<?php echo SERVER_URL; ?>views/script/alertas.js"></script>
    <script>// Para visualizar imágenes en inputs
        
        const imgPic = document.getElementById('img-pic');
        const inputFile = document.getElementById('imgLoad');

        // Validar que los elementos existan antes de agregar eventos
        if (imgPic && inputFile) {
            inputFile.onchange = function () {
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