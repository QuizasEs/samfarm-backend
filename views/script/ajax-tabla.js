/* ajax_tablas.js — versión estable sin pushState
   Reutilizable para tablas con paginación y filtros.
   Requiere:
   - Contenedor .tabla-dinamica[data-ajax-table="true"]
   - data-ajax-url → ruta al archivo AJAX (por ejemplo "ajax/loteAjax.php")
   - data-ajax-param → parámetro para acción (por ejemplo "loteAjax")
   - data-ajax-registros → opcional (default 10)
   - Dentro del contenedor: formulario .filtro-dinamico con input[name="busqueda"] y selects opcionales select1, select2, select3
*/

(function () {
    // Detecta automáticamente el path base del proyecto (sin SERVER_URL)
    function getBaseURL() {
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? '/' + match[1] + '/' : '/';
    }

    const tablas = document.querySelectorAll('.tabla-dinamica[data-ajax-table="true"]');
    if (!tablas || tablas.length === 0) return;

    tablas.forEach(initTabla);

    function initTabla(container) {
        const ajaxUrl = container.dataset.ajaxUrl || 'ajax/loteAjax.php';
        const paramName = container.dataset.ajaxParam || 'loteAjax';
        const registrosDefault = parseInt(container.dataset.ajaxRegistros || 10);

        // Área donde se renderiza la tabla
        let destino = container.querySelector('.tabla-contenedor');
        if (!destino) {
            destino = document.createElement('div');
            destino.className = 'tabla-contenedor';
            container.appendChild(destino);
        }

        // Loader visual
        const loader = document.createElement('div');
        loader.className = 'ajax-loader';
        loader.style.display = 'none';
        loader.innerHTML = '<div class="loader-inner">Cargando...</div>';
        container.appendChild(loader);

        const form = container.querySelector('.filtro-dinamico');

        // Eventos de formulario (filtros)
        if (form) {
            // Búsqueda por Enter
            const busqInput = form.querySelector('input[name="busqueda"], #filtroBusqueda');
            if (busqInput) {
                busqInput.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        cargarPagina(1);
                    }
                });
            }

            // Cambio de selects
            const selects = form.querySelectorAll('select');
            selects.forEach(sel => {
                sel.addEventListener('change', () => cargarPagina(1));
            });

            // Click en botón buscar
            const btnBuscar = form.querySelector('button[type="button"], .btn-search');
            if (btnBuscar) {
                btnBuscar.addEventListener('click', () => cargarPagina(1));
            }
        }

        // Delegar clicks de paginación
        destino.addEventListener('click', e => {
            const a = e.target.closest('a.page-link');
            if (!a) return;
            const page = a.dataset.page || parsePageFromHref(a.getAttribute('href'));
            if (!page) return;
            e.preventDefault();
            cargarPagina(page);
        });

        // Cargar tabla inicial
        cargarPagina(1);

        async function cargarPagina(pagina) {
            loader.style.display = 'block';
            destino.style.opacity = '0.6';

            const base = getBaseURL();
            const fullUrl = window.location.origin + base + ajaxUrl.replace(/^\//, '');
            const formData = new URLSearchParams();

            formData.append(paramName, 'listar');
            formData.append('pagina', pagina);
            formData.append('registros', registrosDefault);

            if (form) {
                const busq = form.querySelector('input[name="busqueda"], #filtroBusqueda');
                if (busq) formData.append('busqueda', busq.value.trim());

                for (let i = 1; i <= 3; i++) {
                    const sel = form.querySelector('select[name="select' + i + '"], #select' + i);
                    if (sel) {
                        formData.append('select' + i, sel.value);
                        if (sel.dataset.type === 'fecha') {
                            formData.append('select' + i + '_type', 'fecha');
                        }
                    }
                }
            }

            try {
                const res = await fetch(fullUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: formData
                });

                const contentType = res.headers.get('Content-Type') || '';
                let html = '';
                if (contentType.includes('application/json')) {
                    const json = await res.json();
                    html = json.html || '';
                } else {
                    html = await res.text();
                }

                destino.innerHTML = html;

                // Actualizar data-page en links del paginador
                const links = destino.querySelectorAll('.custom-pagination a.page-link');
                links.forEach(a => {
                    const p = parsePageFromHref(a.getAttribute('href'));
                    if (p) a.dataset.page = p;
                });
            } catch (err) {
                console.error('Error AJAX:', err);
                destino.innerHTML = '<div class="error">Error al cargar datos.</div>';
            } finally {
                loader.style.display = 'none';
                destino.style.opacity = '';
            }
        }
    }

    function parsePageFromHref(href) {
        if (!href) return null;
        const m = href.match(/\/(\d+)\/?$/);
        return m ? m[1] : null;
    }
})();
