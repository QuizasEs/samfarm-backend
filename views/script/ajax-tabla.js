function obtenerAjaxConfig(container) {
    return {
        url: container.dataset.ajaxUrl || "ajax/loteAjax.php",
        param: container.dataset.ajaxParam || "loteAjax",
        registros: parseInt(container.dataset.ajaxRegistros || 10),
        action: container.dataset.ajaxAction || "listar"
    };
}
(function () {
    // Detecta automáticamente el path base del proyecto
    function getBaseURL() {
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    const tablas = document.querySelectorAll('.tabla-dinamica[data-ajax-table="true"]');
    if (!tablas || tablas.length === 0) {
        console.warn('⚠️ No se encontraron tablas dinámicas');
        return;
    }

    console.log('✅ Inicializando', tablas.length, 'tabla(s) dinámica(s)');
    tablas.forEach(initTabla);

    function initTabla(container) {
        const ajaxCfg = obtenerAjaxConfig(container);
        const ajaxUrl = ajaxCfg.url;
        const paramName = ajaxCfg.param;
        const registrosDefault = ajaxCfg.registros;
        const ajaxAction = ajaxCfg.action;


        console.log('🔧 Configurando tabla:', { ajaxUrl, paramName, registrosDefault, ajaxAction });

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
            console.log('✅ Formulario encontrado, configurando eventos');

            // 📅 Eventos para filtros de fecha
            // 📅 Eventos para filtros de fecha CON VALIDACIÓN
            const fechaInputs = form.querySelectorAll('input[name="fecha_desde"], input[name="fecha_hasta"]');
            if (fechaInputs.length > 0) {
                console.log('📅 Inputs de fecha encontrados:', fechaInputs.length);

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
                        console.warn('⚠️ Fecha desde es mayor que fecha hasta');
                        fechaDesde.style.borderColor = '#ff9800';
                        fechaHasta.style.borderColor = '#ff9800';
                        fechaDesde.setCustomValidity('La fecha inicial debe ser menor o igual a la final');

                        // Mostrar tooltip
                        mostrarTooltip(fechaDesde, 'La fecha "Desde" debe ser anterior o igual a "Hasta"');
                        return false;
                    } else {
                        // ✅ Rango válido
                        console.log('✅ Rango de fechas válido:', desde, 'a', hasta);
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
                        console.log('📅 Cambio en fecha:', input.name, input.value);

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
                console.log('🔍 Input de búsqueda encontrado');
                busqInput.addEventListener("keydown", (e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        console.log('🔍 Enter presionado, buscando...');
                        cargarPagina(1);
                    }
                });
            } else {
                console.warn('⚠️ No se encontró input[name="busqueda"]');
            }

            // Cambio de selects
            const selects = form.querySelectorAll("select");
            if (selects.length > 0) {
                console.log('🎛️ Selects encontrados:', selects.length);
                selects.forEach((sel, idx) => {
                    sel.addEventListener("change", () => {
                        console.log('🎛️ Cambio en select', idx + 1, ':', sel.value);
                        cargarPagina(1);
                    });
                });
            }

            // Click en botón buscar
            const btnBuscar = form.querySelector('.btn-search');
            if (btnBuscar) {
                console.log('🔘 Botón buscar encontrado');
                btnBuscar.addEventListener("click", (e) => {
                    e.preventDefault();
                    console.log('🔘 Click en buscar');
                    cargarPagina(1);
                });
            } else {
                console.warn('⚠️ No se encontró .btn-search');
            }
        } else {
            console.warn('⚠️ No se encontró .filtro-dinamico');
        }

        // Delegar clicks de paginación
        destino.addEventListener("click", (e) => {
            const a = e.target.closest("a.page-link");
            if (!a) return;
            const page = a.dataset.page || parsePageFromHref(a.getAttribute("href"));
            if (!page) return;
            e.preventDefault();
            console.log('📄 Navegando a página:', page);
            cargarPagina(page);
        });

        // Cargar tabla inicial
        console.log('🚀 Cargando página inicial');
        cargarPagina(1);

        async function cargarPagina(pagina) {
            console.log('📡 Cargando página:', pagina);
            loader.style.display = "block";
            destino.style.opacity = "0.6";

            const base = getBaseURL();
            const fullUrl = window.location.origin + base + ajaxUrl.replace(/^\//, "");
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
                        console.log('🔍 Búsqueda:', valor);
                        formData.append("busqueda", valor);
                    }
                }

                // 📅 Filtros de fecha
                const fechaDesde = form.querySelector('input[name="fecha_desde"]');
                const fechaHasta = form.querySelector('input[name="fecha_hasta"]');

                if (fechaDesde && fechaDesde.value) {
                    console.log('📅 Fecha desde:', fechaDesde.value);
                    formData.append("fecha_desde", fechaDesde.value);
                }
                if (fechaHasta && fechaHasta.value) {
                    console.log('📅 Fecha hasta:', fechaHasta.value);
                    formData.append("fecha_hasta", fechaHasta.value);
                }

                // 🎛️ Selects genéricos (hasta 5)
                for (let i = 1; i <= 5; i++) {
                    const sel = form.querySelector(`select[name="select${i}"]`);
                    if (sel && sel.value) {
                        console.log(`🎛️ Select${i}:`, sel.value);
                        formData.append(`select${i}`, sel.value);
                    }
                }
            }

            const formDataObj = Object.fromEntries(formData);
            console.log('📤 Enviando datos:', formDataObj);
            console.log('📤 Valor de parámetro [' + paramName + ']:', formDataObj[paramName]);

            try {
                const res = await fetch(fullUrl, {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: formData,
                });

                console.log('📥 Respuesta recibida:', res.status, res.statusText);

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }

                const contentType = res.headers.get("Content-Type") || "";
                let html = "";

                if (contentType.includes("application/json")) {
                    const json = await res.json();
                    console.log('📋 JSON recibido:', json);
                    html = json.html || "";
                } else {
                    html = await res.text();
                    console.log('📄 HTML recibido (primeros 200 chars):', html.substring(0, 200));
                }

                if (!html || html.trim().length === 0) {
                    console.error('❌ Respuesta vacía del servidor');
                    destino.innerHTML = '<div class="error">No se recibieron datos del servidor</div>';
                } else {
                    destino.innerHTML = html;
                    console.log('✅ Tabla actualizada correctamente');

                    // Actualizar data-page en links
                    const links = destino.querySelectorAll(".custom-pagination a.page-link");
                    links.forEach((a) => {
                        const p = parsePageFromHref(a.getAttribute("href"));
                        if (p) a.dataset.page = p;
                    });
                }
            } catch (err) {
                console.error("❌ Error AJAX:", err);
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