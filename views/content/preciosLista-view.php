<?php
if (isset($_SESSION['id_smp']) && $_SESSION['rol_smp'] == 1) {
?>

    <div class="container">
        <div class="title">
            <h2>
                <ion-icon name="pricetag-outline"></ion-icon> Balance de Precios de Venta
            </h2>
        </div>

        <form class="filtro-dinamico" id="formularioBusqueda">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" id="busquedaMedicamento" placeholder="Buscar medicamento por nombre, principio activo...">
                    <button type="button" class="btn" onclick="buscarMedicamentos()">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
                <a href="<?php echo SERVER_URL; ?>precio-balance" class="btn info">
                    <ion-icon name="document-text-outline"></ion-icon> Ver Informes
                </a>
            </div>
        </form>

        <div id="medicamentosContainer" style="margin-top: 20px;">
            <div class="alert alert-info" style="padding: 20px; text-align: center;">
                📦 Cargando medicamentos...
            </div>
        </div>
    </div>

    <!-- MODAL EDICIÓN DE PRECIOS -->
    <div class="modal" id="modalEditarPrecios" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
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

                <div id="lotesContainer" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                    <p style="text-align: center; color: #999;">Cargando lotes...</p>
                </div>

                <div class="row" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                    <h4 style="margin-bottom: 15px;">Aplicar Nuevo Precio a TODOS los Lotes</h4>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Nuevo Precio (Bs):</label>
                        <input type="number" id="precioNuevoTodos" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>

                <div class="row" style="margin-top: 15px;">
                    <button type="button" class="btn success" onclick="aplicarPrecioTodos()" style="width: 100%;">
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Aplicar a Todos los Lotes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDICIÓN INDIVIDUAL -->
    <div class="modal" id="modalEditarLote" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
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
                        <label>Número de Lote:</label>
                        <p id="detalleNumeroLote" style="font-weight: bold;">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Precio Actual (Bs):</label>
                        <p id="detallePrecioActual" style="color: #e74c3c; font-weight: bold;">-</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>Nuevo Precio (Bs):</label>
                        <input type="number" id="precioNuevoLote" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>

                <div class="row" style="margin-top: 15px;">
                    <button type="button" class="btn success" onclick="guardarPrecioLote()" style="width: 100%;">
                        <ion-icon name="checkmark-circle-outline"></ion-icon> Guardar Cambio
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let medicamentoActual = null;
        let loteActual = null;

        document.addEventListener('DOMContentLoaded', function() {
            buscarMedicamentos();
            
            const inputBusqueda = document.getElementById('busquedaMedicamento');
            inputBusqueda.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    buscarMedicamentos();
                }
            });
        });

        function buscarMedicamentos() {
            const busqueda = document.getElementById('busquedaMedicamento').value;
            const container = document.getElementById('medicamentosContainer');

            const formData = new FormData();
            formData.append('preciosAjax', 'obtener_medicamentos');
            formData.append('busqueda', busqueda);

            container.innerHTML = '<div class="alert alert-info" style="padding: 20px; text-align: center;">⏳ Cargando medicamentos...</div>';

            fetch('<?php echo SERVER_URL; ?>ajax/preciosAjax.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        container.innerHTML = '<div class="alert alert-warning" style="padding: 20px; text-align: center;">❌ No se encontraron medicamentos</div>';
                        return;
                    }

                    let html = '<div style="display: grid; gap: 15px;">';

                    data.forEach(med => {
                        const margenPromedio = med.total_lotes > 0 ? (((med.total_valorado || 0) / (med.total_lotes * med.precio_compra_promedio)) - 1) * 100 : 0;
                        const colorMargen = margenPromedio < 0 ? '#e74c3c' : '#27ae60';

                        html += `
                            <div class="card" style="padding: 15px; border-left: 4px solid #3498db;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 10px;">
                                    <div>
                                        <strong>${med.med_nombre_quimico}</strong><br>
                                        <small style="color: #999;">${med.la_nombre_comercial || 'N/A'}</small>
                                    </div>
                                    <div>
                                        <small style="color: #999;">Precio Compra Promedio</small><br>
                                        <strong>Bs ${parseFloat(med.precio_compra_promedio).toFixed(2)}</strong>
                                    </div>
                                    <div>
                                        <small style="color: #999;">Stock Activo</small><br>
                                        <strong>${parseInt(med.total_unidades_activas)} unidades</strong>
                                    </div>
                                    <div>
                                        <small style="color: #999;">Valorado</small><br>
                                        <strong style="color: ${colorMargen};">Bs ${parseFloat(med.total_valorado || 0).toFixed(2)}</strong>
                                    </div>
                                </div>
                                <div style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                                    <small style="color: #999;">📊 Lotes activos: ${med.lotes_activos} de ${med.total_lotes}</small>
                                    <button type="button" class="btn primary" style="float: right; padding: 5px 10px; font-size: 12px;" onclick="abrirModalPrecios(${med.med_id}, '${med.med_nombre_quimico}')">
                                        Editar Precios
                                    </button>
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<div class="alert alert-error" style="padding: 20px; text-align: center;">⚠️ Error al cargar medicamentos</div>';
                });
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

            container.innerHTML = '<p style="text-align: center; color: #999;">⏳ Cargando lotes...</p>';

            fetch('<?php echo SERVER_URL; ?>ajax/preciosAjax.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        container.innerHTML = '<p style="text-align: center; color: #999;">❌ No hay lotes disponibles</p>';
                        return;
                    }

                    let html = '<table class="tabla-dinamica-lista" style="width: 100%; font-size: 12px;">';
                    html += '<thead><tr><th>Lote</th><th>Stock</th><th>Precio Actual</th><th>Margen %</th><th>Acción</th></tr></thead><tbody>';

                    data.forEach(lote => {
                        const colorMargen = lote.margen_pct < 0 ? '#e74c3c' : '#27ae60';
                        html += `
                            <tr>
                                <td>${lote.lm_numero_lote}</td>
                                <td>${lote.lm_cant_actual_unidades} ud</td>
                                <td><strong>Bs ${parseFloat(lote.lm_precio_venta).toFixed(2)}</strong></td>
                                <td><span style="color: ${colorMargen};">${parseFloat(lote.margen_pct).toFixed(2)}%</span></td>
                                <td>
                                    <button type="button" class="btn sm primary" onclick="abrirModalLote(${lote.lm_id}, '${lote.lm_numero_lote}', ${lote.lm_precio_venta})">
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
                    container.innerHTML = '<p style="text-align: center; color: #e74c3c;">⚠️ Error al cargar lotes</p>';
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
                alert('❌ Por favor ingresa un precio válido mayor a 0');
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
                            title: '✅ Éxito',
                            text: data.mensaje,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            cerrarModalLote();
                            cargarLotesModal(loteActual.med_id);
                        });
                    } else {
                        Swal.fire({
                            title: '❌ Error',
                            text: data.mensaje,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        title: '⚠️ Error',
                        text: 'Error al actualizar el precio',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        function aplicarPrecioTodos() {
            const precioNuevo = parseFloat(document.getElementById('precioNuevoTodos').value);

            if (!precioNuevo || precioNuevo <= 0) {
                alert('❌ Por favor ingresa un precio válido mayor a 0');
                return;
            }

            Swal.fire({
                title: '⚠️ Confirmar',
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
                                    title: '✅ Éxito',
                                    text: data.mensaje,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    cerrarModalPrecios();
                                    buscarMedicamentos();
                                });
                            } else {
                                Swal.fire({
                                    title: '❌ Error',
                                    text: data.mensaje,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire({
                                title: '⚠️ Error',
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
    <div class="error" style="padding:30px;text-align:center;">
        <h3>⛔ Acceso Denegado</h3>
        <p>No tiene permisos para ver esta sección</p>
    </div>
<?php } ?>
