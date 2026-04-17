<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit">Balance de Precios de Venta</div>
                <div class="psub">Gestión y actualización de precios de medicamentos por lote</div>
            </div>
            <div class="tbr">
                <a href="<?php echo SERVER_URL; ?>preciosBalance/" class="btn btn-sec">
                    <ion-icon name="document-text-outline"></ion-icon> Ver Informes
                </a>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico" id="formularioBusqueda">
                    <div class="fr3">
                        <div class="fg">
                            <label class="fl">Sucursales</label>
                            <select class="sel" id="filtroSucursal">
                                <option value="">Todas las sucursales</option>
                                <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                    <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" id="busquedaMedicamento" placeholder="Buscar medicamento por nombre, principio activo...">
                                <button type="button" class="btn btn-def btn-search" onclick="buscarMedicamentos(1)">
                                    <ion-icon name="search"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div id="medicamentosContainer" class="medicamentos-container">
                    <div class="alert alert-info alert-center">
                        Cargando medicamentos...
                    </div>
                </div>

                <div id="paginacionContainer" class="pag"></div>
            </div>
        </div>
    </div>

    <!-- MODAL EDICIÓN DE PRECIOS -->
    <div class="mov" id="modalEditarPrecios" style="display: none;">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Actualizar Precios</div>
                    <div class="ms"><span id="modalMedicamento">...</span></div>
                </div>
                <button class="mcl" onclick="cerrarModalPrecios()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="stit">Lotes del Medicamento</div>
                <div class="tw">
                    <div id="lotesContainer" class="lotes-container">
                        <p class="text-center text-muted">Cargando lotes...</p>
                    </div>
                </div>

                <div class="stit">Aplicar Nuevo Precio a TODOS los Lotes</div>
                <div class="fg">
                    <label class="fl">Nuevo Precio (Bs)</label>
                    <input class="inp" type="number" id="precioNuevoTodos" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="cerrarModalPrecios()">
                    <ion-icon name="close-outline"></ion-icon> Cancelar
                </button>
                <button type="button" class="btn btn-dan" onclick="aplicarPrecioTodos()">
                    <ion-icon name="checkmark-circle-outline"></ion-icon> Aplicar a Todos
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL EDICIÓN INDIVIDUAL -->
    <div class="mov" id="modalEditarLote" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt">Editar Precio de Lote</div>
                    <div class="ms">Modificar precio individual</div>
                </div>
                <button class="mcl" onclick="cerrarModalLote()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="fg">
                    <label class="fl">Número de Lote</label>
                    <p id="detalleNumeroLote" class="text-bold">-</p>
                </div>

                <div class="fg">
                    <label class="fl">Precio Actual (Bs)</label>
                    <p id="detallePrecioActual" class="text-error text-bold">-</p>
                </div>

                <div class="fg">
                    <label class="fl">Nuevo Precio (Bs)</label>
                    <input class="inp" type="number" id="precioNuevoLote" step="0.01" min="0" placeholder="0.00">
                </div>
            </div>

            <div class="mf">
                <button type="button" class="btn btn-war" onclick="cerrarModalLote()">
                    <ion-icon name="close-outline"></ion-icon> Cancelar
                </button>
                <button type="button" class="btn btn-dan" onclick="guardarPrecioLote()">
                    <ion-icon name="checkmark-circle-outline"></ion-icon> Guardar
                </button>
            </div>
        </div>
    </div>

    <script>
        let medicamentoActual = null;
        let loteActual = null;
        let paginaActual = 1;
        const registrosPorPagina = 10;
        let totalRegistros = 0;
        let totalPaginas = 0;

        document.addEventListener('DOMContentLoaded', function() {
            buscarMedicamentos(1);

            const inputBusqueda = document.getElementById('busquedaMedicamento');
            inputBusqueda.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    buscarMedicamentos(1);
                }
            });

            const filtroSucursal = document.getElementById('filtroSucursal');
            filtroSucursal.addEventListener('change', function() {
                buscarMedicamentos(1);
            });
        });

        function buscarMedicamentos(pagina = 1) {
            const busqueda = document.getElementById('busquedaMedicamento').value;
            const sucursal = document.getElementById('filtroSucursal').value;
            const container = document.getElementById('medicamentosContainer');

            const formData = new FormData();
            formData.append('preciosAjax', 'obtener_medicamentos_paginado');
            formData.append('busqueda', busqueda);
            formData.append('su_id', sucursal);
            formData.append('pagina', pagina);
            formData.append('registros', registrosPorPagina);

            container.innerHTML = '<div class="alert alert-info alert-center">Cargando medicamentos...</div>';

            fetch('<?php echo SERVER_URL; ?>ajax/preciosAjax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (!data || data.medicamentos.length === 0) {
                        container.innerHTML = '<div class="title">No se encontraron medicamentos</div>';
                        document.getElementById('paginacionContainer').innerHTML = '';
                        return;
                    }

                    totalRegistros = data.total;
                    totalPaginas = data.total_paginas;
                    paginaActual = pagina;

                    let html = '<div class="medicamentos-grid disabled">';

                    data.medicamentos.forEach(med => {
                        const margenPromedio = med.total_lotes > 0 ? (((med.total_valorado || 0) / (med.total_lotes * med.precio_compra_promedio)) - 1) * 100 : 0;
                        const colorMargen = margenPromedio < 0 ? '#e74c3c' : '#27ae60';

                        html += `
                            <div class="price-card">
                                <div class="price-card-header">
                                    <div class="price-card-title">
                                        <strong>${med.med_nombre_quimico}</strong>
                                        <small class="price-card-subtitle">${med.proveedor || 'Sin proveedor'}</small>
                                    </div>
                                </div>
                                
                                <div class="price-card-body">
                                    <div class="price-card-row">
                                        <span class="price-card-label">Precio Compra Promedio</span>
                                        <span class="price-card-value">Bs ${parseFloat(med.precio_compra_promedio).toFixed(2)}</span>
                                    </div>

                                    <div class="price-card-row">
                                        <span class="price-card-label">Stock Activo</span>
                                        <span class="price-card-value">${parseInt(med.total_unidades_activas)} unidades</span>
                                    </div>

                                    <div class="price-card-row">
                                        <span class="price-card-label">Valorado</span>
                                        <span class="price-card-value price-card-highlight" style="color: ${colorMargen};">Bs ${parseFloat(med.total_valorado || 0).toFixed(2)}</span>
                                    </div>

                                    <div class="price-card-row">
                                        <span class="price-card-label">Precio Venta Unitario</span>
                                        <span class="price-card-value">Bs ${parseFloat(med.precio_venta_unitario_promedio || 0).toFixed(2)}</span>
                                    </div>

                                    <div class="price-card-row">
                                        <span class="price-card-label">Precio de Venta Caja</span>
                                        <span class="price-card-value">Bs ${parseFloat(med.precio_venta_caja_promedio || 0).toFixed(2)}</span>
                                    </div>
                                </div>
                                
                                <div class="price-card-footer">
                                    <small class="price-card-info">Lotes activos: ${med.lotes_activos} de ${med.total_lotes}</small>
                                    <button type="button" class="btn danger" onclick="abrirModalPrecios(${med.med_id}, '${med.med_nombre_quimico.replace(/'/g, "\\'")}')">
                                        Editar Precios
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    container.innerHTML = html;
                    generarPaginacion();
                })
                .catch(err => {
                    container.innerHTML = '<div class="alert alert-error alert-center">Error al cargar medicamentos</div>';
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudieron cargar los medicamentos',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        function generarPaginacion() {
            const paginacionContainer = document.getElementById('paginacionContainer');

            if (totalPaginas <= 1) {
                paginacionContainer.innerHTML = '';
                return;
            }

            let html = '<div class="pginf">Página ' + paginaActual + ' de ' + totalPaginas + '</div><div style="display:flex;gap:4px">';

            // Previous
            if (paginaActual > 1) {
                html += `<div class="pb" onclick="buscarMedicamentos(${paginaActual - 1})"><ion-icon name="chevron-back" style="font-size:11px"></ion-icon></div>`;
            } else {
                html += '<div class="pb dis"><ion-icon name="chevron-back" style="font-size:11px"></ion-icon></div>';
            }

            // Pages
            if (totalPaginas <= 5) {
                for (let i = 1; i <= totalPaginas; i++) {
                    if (paginaActual === i) {
                        html += `<div class="pb ac">${i}</div>`;
                    } else {
                        html += `<div class="pb" onclick="buscarMedicamentos(${i})">${i}</div>`;
                    }
                }
            } else {
                // Show 1,2,3,...,total
                for (let i = 1; i <= 3; i++) {
                    if (paginaActual === i) {
                        html += `<div class="pb ac">${i}</div>`;
                    } else {
                        html += `<div class="pb" onclick="buscarMedicamentos(${i})">${i}</div>`;
                    }
                }
                if (totalPaginas > 3) {
                    html += '<span style="padding:0 2px;color:var(--text-faint);align-self:center">…</span>';
                    if (paginaActual === totalPaginas) {
                        html += `<div class="pb ac">${totalPaginas}</div>`;
                    } else {
                        html += `<div class="pb" onclick="buscarMedicamentos(${totalPaginas})">${totalPaginas}</div>`;
                    }
                }
            }

            // Next
            if (paginaActual < totalPaginas) {
                html += `<div class="pb" onclick="buscarMedicamentos(${paginaActual + 1})"><ion-icon name="chevron-forward" style="font-size:11px"></ion-icon></div>`;
            } else {
                html += '<div class="pb dis"><ion-icon name="chevron-forward" style="font-size:11px"></ion-icon></div>';
            }

            html += '</div>';
            paginacionContainer.innerHTML = html;
        }

        function abrirModalPrecios(med_id, med_nombre) {
            medicamentoActual = med_id;
            document.getElementById('modalMedicamento').textContent = med_nombre;
            document.getElementById('precioNuevoTodos').value = '';
            const modal = document.getElementById('modalEditarPrecios');
            modal.style.display = 'flex';
            modal.classList.add('open');

            cargarLotesModal(med_id);
        }

        function cargarLotesModal(med_id) {
            const container = document.getElementById('lotesContainer');
            const formData = new FormData();
            formData.append('preciosAjax', 'obtener_lotes');
            formData.append('med_id', med_id);

            container.innerHTML = '<p class="text-center text-muted">Cargando lotes...</p>';

            fetch('<?php echo SERVER_URL; ?>ajax/preciosAjax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        container.innerHTML = '<p class="text-center text-muted">No hay lotes disponibles</p>';
                        return;
                    }

                    let html = '<table class="table-detail">';
                    html += '<thead><tr><th>Lote</th><th>Detalles</th><th>Precio</th><th>Acción</th></tr></thead><tbody>';

                    data.forEach(lote => {
                        html += `
                            <tr>
                                <td>
                                    <div class="td-main">${lote.lm_numero_lote}</div>
                                    <div class="td-sub"><ion-icon name="business-outline"></ion-icon>${lote.sucursal_nombre || 'N/A'}</div>
                                </td>
                                <td>
                                    <div class="td-main">${lote.proveedor || 'Sin proveedor'}</div>
                                    <div class="td-meta"><ion-icon name="cube-outline"></ion-icon>${lote.lm_cant_actual_unidades} unidades</div>
                                </td>
                                <td>
                                    <div class="td-main"><strong>Bs ${parseFloat(lote.lm_precio_venta).toFixed(2)}</strong></div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-dan btn-sm" onclick="abrirModalLote(${lote.lm_id}, '${lote.lm_numero_lote}', ${lote.lm_precio_venta})">
                                        <ion-icon name="pencil-outline"></ion-icon>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    html += '</tbody></table>';
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<p class="text-center text-error">Error al cargar lotes</p>';
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudieron cargar los lotes',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        function abrirModalLote(lm_id, numero_lote, precio_actual) {
            loteActual = {
                lm_id: lm_id,
                med_id: medicamentoActual,
                precio_anterior: precio_actual
            };

            document.getElementById('detalleNumeroLote').textContent = numero_lote;
            document.getElementById('detallePrecioActual').textContent = `Bs ${parseFloat(precio_actual).toFixed(2)}`;
            document.getElementById('precioNuevoLote').value = '';
            const modal = document.getElementById('modalEditarLote');
            modal.style.display = 'flex';
            modal.classList.add('open');
        }

        function guardarPrecioLote() {
            const precioNuevo = parseFloat(document.getElementById('precioNuevoLote').value);

            if (!precioNuevo || precioNuevo <= 0) {
                Swal.fire({
                    title: 'Validación',
                    text: 'Por favor ingresa un precio válido mayor a 0',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const formData = new FormData();
            formData.append('preciosAjax', 'actualizar_lote');
            formData.append('lm_id', loteActual.lm_id);
            formData.append('med_id', loteActual.med_id);
            formData.append('precio_nuevo', precioNuevo);

            fetch('<?php echo SERVER_URL; ?>ajax/preciosAjax.php', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Éxito',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            cerrarModalLote();
                            cargarLotesModal(loteActual.med_id);
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.mensaje,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al actualizar el precio',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        function aplicarPrecioTodos() {
            const precioNuevo = parseFloat(document.getElementById('precioNuevoTodos').value);

            if (!precioNuevo || precioNuevo <= 0) {
                Swal.fire({
                    title: 'Validación',
                    text: 'Por favor ingresa un precio válido mayor a 0',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }

            Swal.fire({
                title: 'Confirmar',
                text: `¿Deseas aplicar el precio de Bs ${precioNuevo.toFixed(2)} a TODOS los lotes de este medicamento?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, aplicar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('preciosAjax', 'actualizar_todos');
                    formData.append('med_id', medicamentoActual);
                    formData.append('precio_nuevo', precioNuevo);

                    fetch('<?php echo SERVER_URL; ?>ajax/preciosAjax.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Éxito',
                                    text: data.mensaje,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    cerrarModalPrecios();
                                    buscarMedicamentos(paginaActual);
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.mensaje,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(err => {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al actualizar los precios',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        });
                }
            });
        }

        function cerrarModalPrecios() {
            const modal = document.getElementById('modalEditarPrecios');
            modal.classList.remove('open');
            modal.style.display = 'none';
        }

        function cerrarModalLote() {
            const modal = document.getElementById('modalEditarLote');
            modal.classList.remove('open');
            modal.style.display = 'none';
        }
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
