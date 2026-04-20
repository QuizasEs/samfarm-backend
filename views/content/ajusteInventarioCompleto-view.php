<?php
if (!in_array($_SESSION['rol_smp'], [1, 2])) {
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}

require_once './controllers/ajusteInventarioCompletoController.php';
$ins_ajuste = new ajusteInventarioCompletoController();
$datos_iniciales = $ins_ajuste->obtener_datos_iniciales_controlador();
?>

<div class="">
    <div class="ph">
        <div>
            <div class="ptit">Ajuste de Inventario Completo</div>
            <div class="psub">Este módulo le permite buscar un medicamento y ajustar completamente sus datos y los de sus lotes activos.</div>
        </div>
    </div>

    <!-- Formulario de Búsqueda -->
    <div class="card mb16">
        <div class="ch">
            <div class="ct"><ion-icon name="search-outline"></ion-icon> Búsqueda de Medicamento</div>
        </div>
        <div class="cb">
            <form class="filtro-dinamico">
                <div class="fr3">
                    <div class="fg">
                        <label class="fl">Buscar Medicamento</label>
                        <input class="inp" type="text" id="termino_busqueda" placeholder="Nombre, principio activo, código...">
                    </div>
                    <div class="fg">
                        <label class="fl">Sucursal</label>
                        <select class="sel" id="sucursal_busqueda">
                            <option value="">Seleccione Sucursal...</option>
                            <?php foreach ($datos_iniciales['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id']; ?>">
                                    <?php echo $sucursal['su_nombre']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="fg">
                        <label class="fl">&nbsp;</label>
                        <button type="button" class="btn btn-def btn-search">
                            <ion-icon name="search-outline"></ion-icon> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Area de Resultados de Búsqueda -->
    <div id="resultados-busqueda-container" style="display: none;">
        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Resultados de Búsqueda</div>
            </div>
            <div class="cb">
                <div class="tabla-contenedor"></div>
            </div>
        </div>
    </div>

    <!-- Area de Edición -->
    <div id="edicion-medicamento-container" style="display: none;">
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchButton = document.querySelector('.btn-search');
        const searchTermInput = document.getElementById('termino_busqueda');
        const sucursalSelect = document.getElementById('sucursal_busqueda');
        const resultsContainer = document.getElementById('resultados-busqueda-container');
        const edicionContainer = document.getElementById('edicion-medicamento-container');

        searchButton.addEventListener('click', function() {
            const termino = searchTermInput.value;
            const sucursal_id = sucursalSelect.value;

            if (!termino && !sucursal_id) {
                Swal.fire('Error', 'Debe ingresar un término de búsqueda o seleccionar una sucursal.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'buscar_medicamentos');
            formData.append('termino', termino);
            formData.append('sucursal_id', sucursal_id);

            fetch('<?php echo SERVER_URL; ?>ajax/ajusteInventarioCompletoAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                renderResults(data);
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Ocurrió un error al realizar la búsqueda.', 'error');
            });
        });

        function renderResults(medicamentos) {
            let html = '<table class="table"><thead><tr>';
            html += '<th>Medicamento</th>';
            html += '<th>Stock</th>';
            html += '<th>Ubicación</th>';
            html += '<th>Acción</th>';
            html += '</tr></thead><tbody>';

            if (Array.isArray(medicamentos) && medicamentos.length > 0) {
                medicamentos.forEach(med => {
                    html += `
                        <tr class="tr-click" onclick="seleccionarMedicamento(${med.med_id}, ${med.su_id})">
                            <td>
                                <div class="td-main"><strong>${med.med_nombre_quimico}</strong></div>
                                <div class="td-sub">${med.med_principio_activo} · ${med.la_nombre_comercial || 'N/A'}</div>
                            </td>
                            <td>
                                <div class="td-main"><strong style="color:#1976D2;">${med.inv_total_unidades}</strong> unidades</div>
                            </td>
                            <td>
                                <div class="td-main">${med.su_nombre}</div>
                            </td>
                            <td class="buttons">
                                <button class="btn btn-def" onclick="event.stopPropagation(); seleccionarMedicamento(${med.med_id}, ${med.su_id})">
                                    <ion-icon name="create-outline"></ion-icon> Editar
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="4" style="text-align:center;">No se encontraron resultados.</td></tr>';
            }

            html += '</tbody></table>';
            const tablaContenedor = resultsContainer.querySelector('.tabla-contenedor');
            tablaContenedor.innerHTML = html;
            resultsContainer.style.display = 'block';
            edicionContainer.style.display = 'none';
        }

        window.seleccionarMedicamento = function(medicamentoId, sucursalId) {
            console.log('Seleccionado Medicamento ID:', medicamentoId, 'Sucursal ID:', sucursalId);
            cargarDatosMedicamento(medicamentoId, sucursalId);
        }

        function cargarDatosMedicamento(medicamentoId, sucursalId) {
            console.log('Cargando datos del medicamento ID:', medicamentoId, 'Sucursal ID:', sucursalId);
            
            if (!medicamentoId || !sucursalId) {
                Swal.fire('Error', 'ID de medicamento o sucursal no válido.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'obtener_detalles');
            formData.append('medicamento_id', medicamentoId);
            formData.append('sucursal_id', sucursalId);

            fetch('<?php echo SERVER_URL; ?>ajax/ajusteInventarioCompletoAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Respuesta de obtener_detalles:', response);
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.error) {
                    Swal.fire('Error', data.error, 'error');
                    return;
                }
                renderFormularioEdicion(data.medicamento, data.lotes, medicamentoId, sucursalId);
            })
            .catch(error => {
                console.error('Error al cargar datos del medicamento:', error);
                Swal.fire('Error', 'Ocurrió un error al cargar los datos.', 'error');
            });
        }

        function renderFormularioEdicion(medicamento, lotes, medicamentoId, sucursalId) {
            console.log('Renderizando formulario de edición para medicamento:', medicamento, 'lotes:', lotes);
            
            fetch('<?php echo SERVER_URL; ?>ajax/ajusteInventarioCompletoAjax.php', {
                method: 'POST',
                body: new URLSearchParams({ accion: 'obtener_listas' })
            })
            .then(response => {
                console.log('Respuesta de obtener_listas:', response);
                return response.json();
            })
            .then(listas => {
                console.log('Listas recibidas:', listas);
                const proveedores = listas.proveedores || [];
                const formas = listas.formas || [];
                const usos = listas.usos || [];
                const vias = listas.vias || [];

                // Verificar que el medicamento tenga los datos necesarios
                console.log('Datos del medicamento para formulario:', medicamento);

                let html = `
                    <div class="card mb16">
                        <div class="ch">
                            <div class="ct"><ion-icon name="create-outline"></ion-icon> Editar Medicamento</div>
                            <div class="tbr">
                                <button class="btn btn-sec" onclick="volverBusqueda()">
                                    <ion-icon name="arrow-back-outline"></ion-icon> Volver a Búsqueda
                                </button>
                            </div>
                        </div>
                        <div class="cb">
                            <form id="form-editar-medicamento">
                                <input type="hidden" id="medicamento_id" value="${medicamento.med_id}">
                                <input type="hidden" id="sucursal_id_hidden" value="${sucursalId}">

                                <div class="fr">
                                    <div class="fg">
                                        <label class="fl req">Nombre Químico</label>
                                        <input class="inp" type="text" id="nombre" value="${medicamento.med_nombre_quimico || ''}" required>
                                    </div>
                                    <div class="fg">
                                        <label class="fl req">Principio Activo</label>
                                        <input class="inp" type="text" id="principio" value="${medicamento.med_principio_activo || ''}" required>
                                    </div>
                                </div>

                                <div class="fr">
                                    <div class="fg">
                                        <label class="fl">Código de Barras</label>
                                        <input class="inp" type="text" id="codigo" value="${medicamento.med_codigo_barras || ''}">
                                    </div>
                                    <div class="fg">
                                        <label class="fl">Proveedor</label>
                                        <select class="sel" id="proveedor_id">
                                            <option value="0">Sin Proveedor</option>
                                            ${proveedores.map(prov => `<option value="${prov.pr_id}" ${prov.pr_id == medicamento.pr_id ? 'selected' : ''}>${prov.pr_razon_social}</option>`).join('')}
                                        </select>
                                    </div>
                                </div>

                                <div class="fr">
                                    <div class="fg">
                                        <label class="fl">Forma Farmacéutica</label>
                                        <select class="sel" id="ff_id">
                                            <option value="0">Seleccione...</option>
                                            ${formas.map(f => `<option value="${f.ff_id}" ${f.ff_id == medicamento.ff_id ? 'selected' : ''}>${f.ff_nombre}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="fg">
                                        <label class="fl">Uso Farmacológico</label>
                                        <select class="sel" id="uf_id">
                                            <option value="0">Seleccione...</option>
                                            ${usos.map(u => `<option value="${u.uf_id}" ${u.uf_id == medicamento.uf_id ? 'selected' : ''}>${u.uf_nombre}</option>`).join('')}
                                        </select>
                                    </div>
                                </div>

                                <div class="fg">
                                    <label class="fl">Vía de Administración</label>
                                    <select class="sel" id="via_administracion">
                                        <option value="">Seleccione...</option>
                                        ${vias.map(v => `<option value="${v.vd_id}" ${v.vd_id == medicamento.vd_id ? 'selected' : ''}>${v.vd_nombre}</option>`).join('')}
                                    </select>
                                </div>

                                <div class="cf">
                                    <button type="button" class="btn btn-def" onclick="guardarMedicamento()">
                                        <ion-icon name="save-outline"></ion-icon> Actualizar Datos del Medicamento
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="ch">
                            <div class="ct"><ion-icon name="archive-outline"></ion-icon> Lotes Activos en esta Sucursal</div>
                        </div>
                        <div class="cb">
                            <div class="tabla-contenedor">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Número de Lote</th>
                                            <th>Cajas (inicial)</th>
                                            <th>Blisters/caja</th>
                                            <th>Unidades/blister</th>
                                            <th>Cajas Actuales</th>
                                            <th>Unidades Actuales</th>
                                            <th>P. Compra</th>
                                            <th>P. Venta</th>
                                            <th>Vencimiento</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                    if (lotes && lotes.length > 0) {
                        lotes.forEach(lote => {
                            html += `
                                <tr>
                                    <td><input type="text" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="numero_lote" value="${lote.lm_numero_lote}"></td>
                                    <td><input type="number" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="cant_caja" value="${lote.lm_cant_caja}" min="0"></td>
                                    <td><input type="number" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="cant_blister" value="${lote.lm_cant_blister}" min="0"></td>
                                    <td><input type="number" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="cant_unidad" value="${lote.lm_cant_unidad}" min="0"></td>
                                    <td><input type="number" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="cant_actual_cajas" value="${lote.lm_cant_actual_cajas}" min="0"></td>
                                    <td><input type="number" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="cant_actual_unidades" value="${lote.lm_cant_actual_unidades}" min="0"></td>
                                    <td><input type="number" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="precio_compra" value="${lote.lm_precio_compra}" min="0" step="0.01"></td>
                                    <td><input type="number" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="precio_venta" value="${lote.lm_precio_venta}" min="0" step="0.01"></td>
                                    <td><input type="date" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="fecha_vencimiento" value="${lote.lm_fecha_vencimiento}"></td>
                                    <td class="buttons">
                                        <button class="btn btn-def" onclick="guardarLote(${lote.lm_id})">
                                            <ion-icon name="checkmark-outline"></ion-icon>
                                        </button>
                                        <button class="btn btn-dan" onclick="eliminarLote(${lote.lm_id})">
                                            <ion-icon name="trash-outline"></ion-icon>
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html += '<tr><td colspan="10" style="text-align:center;">No hay lotes activos.</td></tr>';
                    }

                    html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

                console.log('HTML generado:', html);
                edicionContainer.innerHTML = html;
                resultsContainer.style.display = 'none';
                edicionContainer.style.display = 'block';
                console.log('Formulario renderizado y contenedores actualizados');
            })
            .catch(error => {
                console.error('Error al cargar listas:', error);
                Swal.fire('Error', 'Ocurrió un error al cargar las listas.', 'error');
            });
        }

        window.volverBusqueda = function() {
            resultsContainer.style.display = 'block';
            edicionContainer.style.display = 'none';
        }

        window.guardarMedicamento = function() {
            const formData = new FormData();
            formData.append('accion', 'actualizar_medicamento');
            formData.append('medicamento_id', document.getElementById('medicamento_id').value);
            formData.append('nombre', document.getElementById('nombre').value);
            formData.append('principio', document.getElementById('principio').value);
            formData.append('codigo', document.getElementById('codigo').value);
            formData.append('proveedor_id', document.getElementById('proveedor_id').value);
            formData.append('ff_id', document.getElementById('ff_id').value);
            formData.append('uf_id', document.getElementById('uf_id').value);
            formData.append('via_administracion', document.getElementById('via_administracion').value);

            fetch('<?php echo SERVER_URL; ?>ajax/ajusteInventarioCompletoAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.success, 'success');
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error al guardar medicamento:', error);
                Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
            });
        }

        window.guardarLote = function(lote_id) {
            const formData = new FormData();
            formData.append('accion', 'actualizar_lote');
            formData.append('lote_id', lote_id);
            formData.append('numero_lote', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="numero_lote"]`).value);
            formData.append('cant_caja', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="cant_caja"]`).value);
            formData.append('cant_blister', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="cant_blister"]`).value);
            formData.append('cant_unidad', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="cant_unidad"]`).value);
            formData.append('cant_actual_cajas', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="cant_actual_cajas"]`).value);
            formData.append('cant_actual_unidades', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="cant_actual_unidades"]`).value);
            formData.append('precio_compra', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="precio_compra"]`).value);
            formData.append('precio_venta', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="precio_venta"]`).value);
            formData.append('fecha_vencimiento', document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="fecha_vencimiento"]`).value);

            fetch('<?php echo SERVER_URL; ?>ajax/ajusteInventarioCompletoAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.success, 'success');
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => {
                console.error('Error al guardar lote:', error);
                Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
            });
        }

        window.eliminarLote = function(lote_id) {
            Swal.fire({
                title: '¿Eliminar lote?',
                text: "Se recalculará el inventario total.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('accion', 'eliminar_lote');
                    formData.append('lote_id', lote_id);

                    fetch('<?php echo SERVER_URL; ?>ajax/ajusteInventarioCompletoAjax.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Eliminado', data.success, 'success');
                            const medId = document.getElementById('medicamento_id').value;
                            const suId = document.getElementById('sucursal_id_hidden').value;
                            cargarDatosMedicamento(medId, suId);
                        } else {
                            Swal.fire('Error', data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error al eliminar lote:', error);
                        Swal.fire('Error', 'Ocurrió un error al procesar la solicitud.', 'error');
                    });
                }
            });
        }
    });
</script>
