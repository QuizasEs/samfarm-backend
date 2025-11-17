<?php
require_once "./controllers/MedicamentoController.php";
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();
?>
<div class="container">
    <form class="form FormularioAjax" action="<?php echo SERVER_URL ?>ajax/ventaAjax.php" autocomplet="off" method="POST" autocomplete="off" data-form="save">
        <input type="hidden" name="ventaAjax" value="save">

        <input type="hidden" id="venta_items_json" name="venta_items_json">
        <input type="hidden" id="subtotal_venta" name="subtotal_venta">
        <input type="hidden" id="total_venta" name="total_venta">
        <input type="hidden" id="cambio_venta" name="cambio_venta">
        <input type="hidden" id="dinero_recibido_venta" name="dinero_recibido_venta">

        <div class="caja-content">

            <div class="saldo-content">
                <div class="ventas-resumen">

                    <div class="ventas-resumen-bloque">
                        <span>Dinero recibido</span>
                        <input type="number" id="input_dinero_recibido" placeholder="Dinero cancelado" required>
                    </div>

                    <div class="resumen-totales">
                        <span>cambio</span>
                        <span id="cambio_texto">0</span>
                    </div>

                    <div class="resumen-totales">
                        <span>subtotal</span>
                        <span id="subtotal_texto">0</span>
                    </div>

                    <div class="resumen-totales">
                        <span>total</span>
                        <span id="total_texto">0</span>
                    </div>

                </div>

                <div class="ventas-finalizar">
                    <div class="ventas-cliente">
                        <input type="text" id="buscar_cliente_venta" placeholder="Buscar Cliente">

                        <a href="javascript:void(0)" title="Nuevo" onclick="ModalCliente.abrirModal()">
                            <ion-icon name="person-add-outline"></ion-icon>
                        </a>

                    </div>

                    <div class="ventas-metodo">
                        <select class="select-filtro" name="metodo_pago_venta" id="metodo_pago_venta">
                            <option value="">Metodo</option>
                            <option value="efectivo">efectivo</option>
                            <option value="QR">QR</option>
                            <option value="targeta">targeta</option>
                        </select>

                        <select class="select-filtro" name="documento_venta" id="documento_venta">
                            <option value="">Documento</option>
                            <option value="nota de venta">nota de venta</option>
                            <option value="factura">factura</option>
                        </select>
                    </div>



                </div>
            </div>

            <div class="table-container caja-lista">
                <table class="table caja-lista">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Medicamento</th>
                            <th>presentacion</th>
                            <th>cantidad</th>
                            <th>precio</th>
                            <th>subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_items_venta"></tbody>
                </table>
            </div>

            <div class="ventas-finalizar-buttons">
                <a href="javascript:void(0)" class="btn warning" id="cancelar_venta_btn">cancelar</a>
                <button class="btn success" id="btn_realizar_venta">vender</button>
            </div>

            <div class="filtro-caja">

                <div class="caja-texto">
                    <h3>filtros de busqueda</h3>
                </div>

                <div class="caja-filtro-search" style="position: relative;">
                    <input type="text" id="med_search" placeholder="Que medicamento busca?">
                    <!-- El div se crea dinámicamente aquí -->
                </div>

                <div class="caja-texto">
                    <h3>Filtros de busqueda</h3>
                </div>

                <div class="caja-filtro-content">
                    <select class="select-filtro" id="filtro_linea">
                        <option value="">Linea</option>
                        <?php foreach ($datos_select['laboratorios'] as $lab) { ?>
                            <option value="<?php echo $lab['la_id'] ?>"><?php echo $lab['la_nombre_comercial'] ?></option>
                        <?php } ?>
                    </select>

                    <select class="select-filtro" id="filtro_presentacion">
                        <option value="">presentacion</option>
                        <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                            <option value="<?php echo $forma['ff_id'] ?>"><?php echo $forma['ff_nombre'] ?></option>
                        <?php } ?>
                    </select>

                    <select class="select-filtro" id="filtro_funcion">
                        <option value="">Funcion</option>
                        <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                            <option value="<?php echo $uso['uf_id'] ?>"><?php echo $uso['uf_nombre'] ?></option>
                        <?php } ?>
                    </select>
                    <select class="select-filtro" id="filtro_via">
                        <option value="">Via</option>
                        <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                            <option value="<?php echo $via['vd_id'] ?>"><?php echo $via['vd_nombre'] ?></option>
                        <?php } ?>
                    </select>

                </div>

                <div class="caja-texto">
                    <h3>Mas Vendidos</h3>
                </div>

                <div class="table-container">
                    <table class="table caja">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Medicamento</th>
                                <th>Precio</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_mas_vendidos"></tbody>
                    </table>
                </div>

            </div>
        </div>
    </form>

    <form class="FormularioAjax" action="<?php echo SERVER_URL ?>ajax/clienteAjax.php" autocomplet="off" method="POST" autocomplete="off" data-form="save">
        <input type="hidden" name="clienteAjax" value="save">

        <div class="modal" id="modalCliente" style="display: none;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title"><ion-icon name="person-add-outline"></ion-icon> Nuevo cliente</div>
                    <a class="close" onclick="ModalCliente.cerrarModalCliente()">×</a>
                </div>

                <div class="modal-group">
                    <div class="row">
                        <label class="required">Nombres</label>
                        <input type="text" name="Nombres_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Apellido Paterno</label>
                                <input type="text" name="Paterno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Apellido Materno</label>
                                <input type="text" name="Materno_cl" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Telefono</label>
                                <input type="number" name="Telefono_cl" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Correo</label>
                                <input type="email" name="Correo_cl">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Direccion</label>
                                <input type="text" name="Direccion_cl">
                            </div>
                        </div>

                        <div class="col">
                            <div class="modal-bloque">
                                <label>Carnet</label>
                                <input type="number" name="Carnet_cl" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>
                    </div>

                    <div class="btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="ModalCliente.cerrarModalCliente()">Cancelar</a>
                        <button class="btn success">Agregar</button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<script>
    /*
  ModalCliente robusto y compatible.
  - Expone: ModalCliente.abrirModal(), ModalCliente.cerrarModal()
           ModalCliente.abrirModalCliente(), ModalCliente.cerrarModalCliente()
  - También crea globales: abrirModal(), cerrarModal() (compatibilidad con onclick antiguos)
  - Maneja carga antes o después del DOM.
*/
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


<script>
    (function() {
        const URL_MED = "<?php echo SERVER_URL ?>ajax/ventaAjax.php";
        const TAX = 0.13;
        let cart = [];
        let debounce = null;

        function $(s) {
            return document.querySelector(s)
        }

        function $all(s) {
            return Array.from(document.querySelectorAll(s))
        }

        function ensureHidden(name, id) {
            let el = document.querySelector('input[name="' + name + '"]');
            if (!el) {
                el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                if (id) el.id = id;
                document.querySelector('.form.FormularioAjax').appendChild(el)
            }
            return el;
        }

        const itemsHidden = ensureHidden('venta_items_json', 'venta_items_json');
        const subtotalHidden = ensureHidden('subtotal_venta', 'subtotal_venta');
        const totalHidden = ensureHidden('total_venta', 'total_venta');
        const cambioHidden = ensureHidden('cambio_venta', 'cambio_venta');
        const dineroHidden = ensureHidden('dinero_recibido_venta', 'dinero_recibido_venta');

        const medSearch = document.getElementById('med_search') || document.querySelector('.caja-filtro-search input');
        const filtro_linea = document.getElementById('filtro_linea');
        const filtro_presentacion = document.getElementById('filtro_presentacion');
        const filtro_funcion = document.getElementById('filtro_funcion');
        const filtro_via = document.getElementById('filtro_via');

        let resultsContainer = document.getElementById('med_search_results');
        if (!resultsContainer && medSearch) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'med_search_results';
            resultsContainer.className = 'search-results-dropdown';

            // Estilos críticos para posicionamiento
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

            // Asegurar que el padre tenga position relative
            const parent = medSearch.parentElement;
            parent.style.position = 'relative';
            parent.appendChild(resultsContainer);
        }

        const tablaBody = document.getElementById('tabla_items_venta');
        const tablaMasVendidos = document.getElementById('tabla_mas_vendidos');
        const inputDinero = document.getElementById('input_dinero_recibido');
        const subtotalText = document.getElementById('subtotal_texto');
        const totalText = document.getElementById('total_texto');
        const cambioText = document.getElementById('cambio_texto');

        function formatMoney(n) {
            return Number(n || 0).toFixed(2)
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
                })[m]
            })
        }

        function renderCart() {
            console.log('Renderizando carrito, items:', cart.length); // DEBUG

            if (!tablaBody) {
                console.error('tablaBody no encontrado');
                return;
            }

            tablaBody.innerHTML = '';

            if (cart.length === 0) {
                tablaBody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#666;padding:12px">No hay medicamentos en la lista</td></tr>';
            } else {
                cart.forEach((it, i) => {
                    const tr = document.createElement('tr');
                    tr.dataset.med = it.med_id;
                    tr.innerHTML =
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + escapeHtml(it.nombre) + '</td>' +
                        '<td>' + escapeHtml(it.presentacion || '') + '</td>' +
                        '<td><div class="table-cantidad">' +
                        '<button type="button" class="qty-dec" data-med="' + it.med_id + '">' +
                        '<ion-icon name="remove-outline"></ion-icon>' +
                        '</button>' +
                        '<input type="number" class="qty-input" data-med="' + it.med_id + '" value="' + it.cantidad + '" min="1">' +
                        '<button type="button" class="qty-inc" data-med="' + it.med_id + '">' +
                        '<ion-icon name="add-outline"></ion-icon>' +
                        '</button>' +
                        '</div></td>' +
                        '<td>' + formatMoney(it.precio) + '</td>' +
                        '<td>' + formatMoney(it.precio * it.cantidad) + '</td>';

                    tablaBody.appendChild(tr); // ⚠️ ESTA LÍNEA FALTABA
                });
            }

            itemsHidden.value = JSON.stringify(cart.map(i => ({
                med_id: i.med_id,
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
                    changeQty(this.dataset.med, 1)
                }
            })
            $all('.qty-dec').forEach(b => {
                b.onclick = function() {
                    changeQty(this.dataset.med, -1)
                }
            })
            $all('.qty-input').forEach(i => {
                i.onchange = function() {
                    setQty(this.dataset.med, parseInt(this.value) || 0)
                }
            })
        }

        function changeQty(id, delta) {
            const idx = cart.findIndex(c => String(c.med_id) === String(id));
            if (idx === -1) return;
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
                        renderCart()
                    } else {
                        renderCart()
                    }
                });
                return;
            }
            if (item.stock != null && nuevo > item.stock) {
                Swal.fire('Sin stock', 'No hay stock suficiente', 'warning');
                return
            }
            item.cantidad = nuevo;
            renderCart();
        }

        function setQty(id, val) {
            const idx = cart.findIndex(c => String(c.med_id) === String(id));
            if (idx === -1) return;
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
                        renderCart()
                    } else {
                        item.cantidad = 1;
                        renderCart()
                    }
                });
                return;
            }
            if (item.stock != null && val > item.stock) {
                Swal.fire('Sin stock', 'No hay suficiente stock', 'warning');
                renderCart();
                return
            }
            item.cantidad = val;
            renderCart();
        }

        function updateTotals() {
            const subtotal = cart.reduce((s, i) => s + (i.precio * i.cantidad), 0);
            const impuestos = subtotal * TAX;
            const total = subtotal + impuestos;
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
            console.log('🔵 addItem llamado con:', m); // DEBUG

            const idx = cart.findIndex(c => String(c.med_id) === String(m.med_id));
            if (idx !== -1) {
                const ex = cart[idx];
                if (m.stock != null && ex.cantidad + 1 > m.stock) {
                    Swal.fire('Sin stock', 'No hay stock suficiente', 'warning');
                    return
                }
                ex.cantidad += 1;
                console.log('✅ Cantidad actualizada:', ex); // DEBUG
            } else {
                cart.push({
                    med_id: m.med_id,
                    nombre: m.nombre,
                    presentacion: m.presentacion || '',
                    linea: m.linea || '',
                    precio: parseFloat(m.precio) || 0,
                    cantidad: 1,
                    stock: m.stock != null ? Number(m.stock) : null
                });
                console.log('✅ Item agregado al carrito'); // DEBUG
            }
            console.log('🛒 Carrito completo:', cart); // DEBUG
            renderCart();
        }

        function doSearch(term) {
            if (!term || term.trim().length < 1) {
                if (resultsContainer) resultsContainer.innerHTML = '';
                return
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
                renderResults(json || []);
            }).catch(err => {
                console.error(err)
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
                const presentacion = escapeHtml(it.presentacion || 'Sin presentación');
                const via = escapeHtml(it.linea || 'Sin vía');
                const precio = formatMoney(it.precio_venta || 0);

                return `<div class="search-results-item" 
            data-id="${it.med_id}" 
            data-nombre="${nombre}" 
            data-presentacion="${presentacion}" 
            data-linea="${via}"
            data-precio="${it.precio_venta || 0}"
            data-stock="${it.stock || 0}">
            <div class="search-result-name">${nombre}</div>
            <div class="search-result-details">${via} · ${presentacion} · Bs. ${precio}</div>
        </div>`;
            }).join('');

            resultsContainer.style.display = 'block';

            resultsContainer.querySelectorAll('.search-results-item:not(.no-results)').forEach(el => {
                el.addEventListener('click', () => {
                    addItem({
                        med_id: el.dataset.id,
                        nombre: el.dataset.nombre,
                        presentacion: el.dataset.presentacion,
                        linea: el.dataset.linea,
                        precio: parseFloat(el.dataset.precio || 0),
                        stock: Number(el.dataset.stock || 0)
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
                if (medSearch && medSearch.value) doSearch(medSearch.value)
            })
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
                tablaMasVendidos.innerHTML = (json || []).map((it, i) =>
                    '<tr data-id="' + it.med_id + '"><td>' + (i + 1) + '</td><td>' + escapeHtml(it.nombre) + '</td><td>Bs. ' + formatMoney(it.precio_venta) + '</td><td><a href="#" class="btn caja btn-add" data-id="' + it.med_id + '" data-nombre="' + escapeHtml(it.nombre) + '" data-precio="' + it.precio_venta + '">agregar</a></td></tr>'
                ).join('');
                tablaMasVendidos.querySelectorAll('.btn-add').forEach(b => b.addEventListener('click', e => {
                    e.preventDefault();
                    const el = e.currentTarget;
                    addItem({
                        med_id: el.dataset.id,
                        nombre: el.dataset.nombre,
                        presentacion: '',
                        linea: '',
                        precio: parseFloat(el.dataset.precio || 0),
                        stock: null
                    });
                }));
            }).catch(err => console.error(err));
        }

        if (inputDinero) inputDinero.addEventListener('input', updateTotals);

        document.querySelector('.form.FormularioAjax').addEventListener('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                Swal.fire('Carrito vacío', 'Agrega al menos un medicamento para realizar la venta.', 'warning');
                return
            }
            itemsHidden.value = JSON.stringify(cart.map(i => ({
                med_id: i.med_id,
                cantidad: i.cantidad,
                precio: Number(i.precio),
                subtotal: Number((i.precio * i.cantidad).toFixed(2))
            })));
            updateTotals();
        });

        loadMostSold();
        renderCart();

        // Cerrar dropdown al hacer click fuera
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