function obtenerAjaxConfig(container) {
    return {
        url: container.dataset.ajaxUrl || "ajax/loteAjax.php",
        param: container.dataset.ajaxParam || "loteAjax",
        registros: parseInt(container.dataset.ajaxRegistros || 10),
        action: container.dataset.ajaxAction || "listar"
    };
}
(function () {
    // Obtiene la URL base del servidor desde el data attribute
    function getBaseURL() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        // Fallback al método anterior si no está disponible
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    const tablas = document.querySelectorAll('.tabla-dinamica[data-ajax-table="true"]');
    if (!tablas || tablas.length === 0) {
        return;
    }

    tablas.forEach(initTabla);

    function initTabla(container) {
        const ajaxCfg = obtenerAjaxConfig(container);
        const ajaxUrl = ajaxCfg.url;
        const paramName = ajaxCfg.param;
        const registrosDefault = ajaxCfg.registros;
        const ajaxAction = ajaxCfg.action;




        // Área donde se renderiza la tabla
        let destino = container.querySelector(".tabla-contenedor");
        if (!destino) {
            destino = document.createElement("div");
            destino.className = "tabla-contenedor";
            container.appendChild(destino);
        }

        // Loader visual
        const loader = document.createElement("div");
        loader.className = "ajax-loader";
        loader.style.display = "none";
        loader.innerHTML = '<div class="loader-inner">Cargando...</div>';
        container.appendChild(loader);

        const form = container.querySelector(".filtro-dinamico");

        // Eventos de formulario (filtros)
        if (form) {

            const fechaInputs = form.querySelectorAll('input[name="fecha_desde"], input[name="fecha_hasta"]');
            if (fechaInputs.length > 0) {
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');

                // Función de validación
                function validarFechas() {
                    if (!fechaDesde || !fechaHasta) return true;

                    const desde = fechaDesde.value;
                    const hasta = fechaHasta.value;

                    // Si ambas están vacías, no validar
                    if (!desde && !hasta) {
                        fechaDesde.setCustomValidity('');
                        fechaHasta.setCustomValidity('');
                        fechaDesde.style.borderColor = '';
                        fechaHasta.style.borderColor = '';
                        return true;
                    }

                    // Si solo una está llena, es válido
                    if (!desde || !hasta) {
                        fechaDesde.setCustomValidity('');
                        fechaHasta.setCustomValidity('');
                        fechaDesde.style.borderColor = '';
                        fechaHasta.style.borderColor = '';
                        return true;
                    }

                    // Validar que desde <= hasta
                    const tsDesde = new Date(desde).getTime();
                    const tsHasta = new Date(hasta).getTime();

                    if (tsDesde > tsHasta) {
                        // ❌ Rango inválido
                        fechaDesde.style.borderColor = '#ff9800';
                        fechaHasta.style.borderColor = '#ff9800';
                        fechaDesde.setCustomValidity('La fecha inicial debe ser menor o igual a la final');

                        // Mostrar tooltip
                        mostrarTooltip(fechaDesde, 'La fecha "Desde" debe ser anterior o igual a "Hasta"');
                        return false;
                    } else {
                        // ✅ Rango válido
                        fechaDesde.setCustomValidity('');
                        fechaHasta.setCustomValidity('');
                        fechaDesde.style.borderColor = '#4CAF50';
                        fechaHasta.style.borderColor = '#4CAF50';

                        // Quitar estilos después de 2 segundos
                        setTimeout(() => {
                            fechaDesde.style.borderColor = '';
                            fechaHasta.style.borderColor = '';
                        }, 2000);

                        return true;
                    }
                }

                // Función para mostrar tooltip
                function mostrarTooltip(elemento, mensaje) {
                    // Remover tooltip existente
                    const tooltipExistente = document.querySelector('.tooltip-fecha-error');
                    if (tooltipExistente) tooltipExistente.remove();

                    const tooltip = document.createElement('div');
                    tooltip.className = 'tooltip-fecha-error';
                    tooltip.textContent = mensaje;
                    tooltip.style.cssText = `
                        position: absolute;
                        background: #ff9800;
                        color: white;
                        padding: 8px 12px;
                        border-radius: 4px;
                        font-size: 12px;
                        font-weight: bold;
                        white-space: nowrap;
                        z-index: 10000;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                        animation: fadeInTooltip 0.3s ease;
                    `;

                    document.body.appendChild(tooltip);

                    const rect = elemento.getBoundingClientRect();
                    tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
                    tooltip.style.left = rect.left + 'px';

                    // Auto-remover después de 3 segundos
                    setTimeout(() => tooltip.remove(), 3000);
                }

                // Eventos de cambio
                fechaInputs.forEach(input => {
                    input.addEventListener('change', () => {
                        if (validarFechas()) {
                            // Solo buscar si las fechas son válidas
                            cargarPagina(1);
                        }
                    });

                    // Validar también al escribir (input event)
                    input.addEventListener('input', () => {
                        validarFechas();
                    });
                });

                // Agregar estilos de animación
                if (!document.querySelector('#tooltip-animation-styles')) {
                    const style = document.createElement('style');
                    style.id = 'tooltip-animation-styles';
                    style.textContent = `
                        @keyframes fadeInTooltip {
                            from {
                                opacity: 0;
                                transform: translateY(-5px);
                            }
                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }
            }

            // Búsqueda por Enter
            const busqInput = form.querySelector('input[name="busqueda"]');
            if (busqInput) {
                busqInput.addEventListener("keydown", (e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        cargarPagina(1);
                    }
                });
            }

            // Cambio de selects
            const selects = form.querySelectorAll("select");
            if (selects.length > 0) {
                selects.forEach((sel, idx) => {
                    sel.addEventListener("change", () => {
                        cargarPagina(1);
                    });
                });
            }

            // Click en botón buscar
            const btnBuscar = form.querySelector('.btn-search');
            if (btnBuscar) {
                btnBuscar.addEventListener("click", (e) => {
                    e.preventDefault();
                    cargarPagina(1);
                });
            }
        }

        // Delegar clicks de paginación
        destino.addEventListener("click", (e) => {
            const a = e.target.closest("a.page-link");
            if (!a) return;
            const page = a.dataset.page || parsePageFromHref(a.getAttribute("href"));
            if (!page) return;
            e.preventDefault();
            cargarPagina(page);
        });

        // Cargar tabla inicial
        cargarPagina(1);

        async function cargarPagina(pagina) {
            loader.style.display = "block";
            destino.style.opacity = "0.6";

            const base = getBaseURL();
            const fullUrl = base + ajaxUrl.replace(/^\//, "");
            const formData = new URLSearchParams();

            formData.append(paramName, ajaxAction);
            formData.append("pagina", pagina);
            formData.append("registros", registrosDefault);

            if (form) {
                // 🔍 Búsqueda por término
                const busq = form.querySelector('input[name="busqueda"]');
                if (busq) {
                    const valor = busq.value ? busq.value.trim() : '';
                    if (valor) {
                        formData.append("busqueda", valor);
                    }
                }

                // 📅 Filtros de fecha
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');

                if (fechaDesde && fechaDesde.value) {
                    formData.append("fecha_desde", fechaDesde.value);
                }
                if (fechaHasta && fechaHasta.value) {
                    formData.append("fecha_hasta", fechaHasta.value);
                }

                // 🎛️ Selects genéricos (hasta 5)
                for (let i = 1; i <= 5; i++) {
                    const sel = form.querySelector(`select[name="select${i}"]`);
                    if (sel && sel.value) {
                        formData.append(`select${i}`, sel.value);
                    }
                }
            }

            try {
                const res = await fetch(fullUrl, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: formData,
                });

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }

                const contentType = res.headers.get("Content-Type") || "";
                let html = "";

                if (contentType.includes("application/json")) {
                    const json = await res.json();
                    html = json.html || "";
                } else {
                    html = await res.text();
                }

                if (!html || html.trim().length === 0) {
                    destino.innerHTML = '<div class="error">No se recibieron datos del servidor</div>';
                } else {
                    destino.innerHTML = html;

                    // Actualizar data-page en links
                    const links = destino.querySelectorAll(".custom-pagination a.page-link");
                    links.forEach((a) => {
                        const p = parsePageFromHref(a.getAttribute("href"));
                        if (p) a.dataset.page = p;
                    });
                }
            } catch (err) {
                destino.innerHTML = `<div class="error">Error al cargar datos: ${err.message}</div>`;
            } finally {
                loader.style.display = "none";
                destino.style.opacity = "";
            }
        }
    }

    function parsePageFromHref(href) {
        if (!href) return null;
        const m = href.match(/\/(\d+)\/?$/);
        return m ? m[1] : null;
    }
})();