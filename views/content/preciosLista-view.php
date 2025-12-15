<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/medicamentoController.php";
    $ins_med = new medicamentoController();
    $datos_select = $ins_med->datos_extras_controller();
?>

    <div class="container">
        <div class="title">
            <h2>
                <ion-icon name="pricetag-outline"></ion-icon> Balance de Precios de Venta
            </h2>
        </div>

        <form class="filtro-dinamico" id="formularioBusqueda">
            <div class="filtro-dinamico-search">
                <div class="form-fechas">
                    <small>Sucursales</small>
                    <select class="select-filtro" id="filtroSucursal">
                        <option value="">Todas las sucursales</option>
                        <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="search">
                    <input type="text" id="busquedaMedicamento" placeholder="Buscar medicamento por nombre, principio activo...">
                    <button type="button" class="btn-search" onclick="buscarMedicamentos(1)">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>

                <a href="<?php echo SERVER_URL; ?>preciosBalance/" class="btn default">
                    <ion-icon name="document-text-outline"></ion-icon> Ver Informes
                </a>
            </div>
        </form>

        <div id="medicamentosContainer" class="medicamentos-container">
            <div class="alert alert-info alert-center">
                Cargando medicamentos...
            </div>
        </div>

        <div id="paginacionContainer" class="pagination-container"></div>
    </div>

    <!-- MODAL EDICIÓN DE PRECIOS -->
    <div class="modal" id="modalEditarPrecios">
        <div class="modal-content detalle">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="create-outline"></ion-icon>
                    Actualizar Precios - <span id="modalMedicamento">...</span>
                </div>
                <a class="close" onclick="cerrarModalPrecios()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <div class="modal-group">
                <div class="row">
                    <h4>Lotes del Medicamento</h4>
                </div>

                <div class="table-container">
                    <div id="lotesContainer" class="lotes-container">
                        <p class="text-center text-muted">Cargando lotes...</p>
                    </div>
                </div>

                <div class="row row-separator">
                    <h4 class="section-title">Aplicar Nuevo Precio a TODOS los Lotes</h4>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="modal-bloque">
                            <label>Nuevo Precio (Bs):</label>
                            <input type="number" id="precioNuevoTodos" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="modal-btn-content">
                    <button type="button" class="btn warning btn-full" onclick="cerrarModalPrecios()">
                        <ion-icon name="close-outline"></ion-icon> Cancelar
                    </button>
                    <button type="button" style=" width: fit-content;" class="btn danger btn-full" onclick="aplicarPrecioTodos()">
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Aplicar a Todos los Lotes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDICIÓN INDIVIDUAL -->
    <div class="modal" id="modalEditarLote">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="create-outline"></ion-icon>
                    Editar Precio de Lote
                </div>
                <a class="close" onclick="cerrarModalLote()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <div class="modal-group">
                <div class="row">
                    <div class="col">
                        <div class="modal-bloque">
                            <label>Número de Lote:</label>
                            <p id="detalleNumeroLote" class="text-bold">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="modal-bloque">
                            <label>Precio Actual (Bs):</label>
                            <p id="detallePrecioActual" class="text-error text-bold">-</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="modal-bloque">
                            <label>Nuevo Precio (Bs):</label>
                            <input type="number" id="precioNuevoLote" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="modal-btn-content">
                    <button type="button" class="btn warning btn-full" onclick="cerrarModalLote()">
                        <ion-icon name="close-outline"></ion-icon> Cancelar
                    </button>
                    <button type="button" class="btn danger btn-full" onclick="guardarPrecioLote()">
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Guardar
                    </button>
                </div>
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

                    let html = '<div class="medicamentos-grid">';

                    data.medicamentos.forEach(med => {
                        const margenPromedio = med.total_lotes > 0 ? (((med.total_valorado || 0) / (med.total_lotes * med.precio_compra_promedio)) - 1) * 100 : 0;
                        const colorMargen = margenPromedio < 0 ? '#e74c3c' : '#27ae60';

                        html += `
                            <div class="price-card">
                                <div class="price-card-header">
                                    <div class="price-card-title">
                                        <strong>${med.med_nombre_quimico}</strong>
                                        <small class="price-card-subtitle">${med.la_nombre_comercial || 'N/A'}</small>
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

            let html = '<nav><ul class="custom-pagination">';

            if (paginaActual > 1) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="buscarMedicamentos(${paginaActual - 1}); return false;">Previous</a></li>`;
            } else {
                html += '<li class="page-item disabled"><a class="page-link">Previous</a></li>';
            }

            for (let i = paginaActual; i <= Math.min(paginaActual + 4, totalPaginas); i++) {
                if (paginaActual === i) {
                    html += `<li class="page-item active"><a class="page-link">${i}</a></li>`;
                } else {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="buscarMedicamentos(${i}); return false;">${i}</a></li>`;
                }
            }

            if (paginaActual < totalPaginas) {
                html += `<li class="page-item"><a class="page-link" href="#" onclick="buscarMedicamentos(${paginaActual + 1}); return false;">Next</a></li>`;
            } else {
                html += '<li class="page-item disabled"><a class="page-link">Next</a></li>';
            }

            html += '</ul></nav>';
            paginacionContainer.innerHTML = html;
        }

        function abrirModalPrecios(med_id, med_nombre) {
            medicamentoActual = med_id;
            document.getElementById('modalMedicamento').textContent = med_nombre;
            document.getElementById('precioNuevoTodos').value = '';
            document.getElementById('modalEditarPrecios').style.display = 'flex';

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

                    let html = '<table class="table">';
                    html += '<thead><tr><th>Lote</th><th>Sucursal</th><th>Nombre Comercial</th><th>Stock</th><th>Precio Actual</th><th>Acción</th></tr></thead><tbody>';

                    data.forEach(lote => {
                        html += `
                            <tr>
                                <td>${lote.lm_numero_lote}</td>
                                <td><span style="background:#E3F2FD;padding:4px 8px;border-radius:4px;font-weight:600;color:#1565C0;">${lote.sucursal_nombre || 'N/A'}</span></td>
                                <td>${lote.med_nombre_comercial || 'N/A'}</td>
                                <td>${lote.lm_cant_actual_unidades} ud</td>
                                <td><strong>Bs ${parseFloat(lote.lm_precio_venta).toFixed(2)}</strong></td>
                                <td>
                                    <button type="button" class="btn sm danger" onclick="abrirModalLote(${lote.lm_id}, '${lote.lm_numero_lote}', ${lote.lm_precio_venta})">
                                        Editar
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
            document.getElementById('modalEditarLote').style.display = 'flex';
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
            document.getElementById('modalEditarPrecios').style.display = 'none';
        }

        function cerrarModalLote() {
            document.getElementById('modalEditarLote').style.display = 'none';
        }
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
