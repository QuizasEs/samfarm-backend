<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/mermaController.php";
    $mermaController = new mermaController();
    
    $rol_usuario = $_SESSION['rol_smp'];
    $sucursal_usuario = $_SESSION['sucursal_smp'] ?? 1;
    
    $lotes_caducidad = $mermaController->obtener_lotes_caducidad_controller();
    $total_lotes = count($lotes_caducidad);
?>

    <div class="container">
        <div class="title">
            <h2>
                <ion-icon name="alert-circle-outline"></ion-icon> Crear Merma - Lotes con Riesgo de Caducidad
            </h2>
            <p>Total de lotes detectados: <strong><?php echo $total_lotes; ?></strong></p>
        </div>

        <?php if ($total_lotes > 0) { ?>
            <div class="tabla-contenedor">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Lote</th>
                            <th>Sucursal</th>
                            <th>Vencimiento</th>
                            <th>Días</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lotes_caducidad as $lote) { ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($lote['med_nombre_quimico'] ?? ''); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($lote['lm_numero_lote'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($lote['su_nombre'] ?? ''); ?></td>
                                <td>
                                    <?php
                                    $fecha_venc = strtotime($lote['lm_fecha_vencimiento']);
                                    $hoy = time();
                                    $diferencia_dias = ceil(($fecha_venc - $hoy) / (60 * 60 * 24));
                                    echo htmlspecialchars($lote['lm_fecha_vencimiento']);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($diferencia_dias <= 0) {
                                        echo '<span style="color: #d32f2f;"><strong>CADUCADO</strong></span>';
                                    } elseif ($diferencia_dias <= 10) {
                                        echo '<span style="color: #ff9800;"><strong>' . $diferencia_dias . ' días</strong></span>';
                                    } else {
                                        echo $diferencia_dias . ' días';
                                    }
                                    ?>
                                </td>
                                <td><?php echo number_format($lote['lm_cant_actual_unidades'], 0); ?> unidades</td>
                                <td>
                                    <?php 
                                    if ($diferencia_dias <= 0) {
                                        echo '<span class="badge-danger">Caducado</span>';
                                    } else {
                                        echo '<span class="badge-warning">Próximo a Vencer</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button type="button" class="btn success btn-sm" 
                                        onclick="abrirFormularioMerma(<?php echo $lote['lm_id']; ?>, <?php echo $lote['med_id']; ?>, '<?php echo htmlspecialchars($lote['med_nombre_quimico']); ?>', <?php echo $lote['lm_cant_actual_unidades']; ?>)">
                                        <ion-icon name="create-outline"></ion-icon> Registrar Merma
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="modal" id="modalMerma" style="display: none;">
                <div class="modal-content" style="width: 500px;">
                    <div class="modal-header">
                        <div class="modal-title">
                            <ion-icon name="alert-circle-outline"></ion-icon>
                            Registrar Merma
                        </div>
                        <a class="close" onclick="cerrarModalMerma()">
                            <ion-icon name="close-outline"></ion-icon>
                        </a>
                    </div>

                    <form id="formMerma" class="form">
                        <div class="modal-group">
                            <label>Medicamento:</label>
                            <input type="text" id="medicamentoNombre" readonly style="background-color: #f5f5f5;">
                            <input type="hidden" id="lm_id" name="lm_id">
                            <input type="hidden" id="med_id" name="med_id">

                            <label style="margin-top: 15px;">Cantidad a Registrar como Merma:</label>
                            <input type="number" id="me_cantidad" name="me_cantidad" min="1" required 
                                placeholder="Cantidad de unidades">
                            <small id="cantidadMax" style="color: #666;"></small>

                            <label style="margin-top: 15px;">Motivo de la Merma:</label>
                            <textarea id="me_motivo" name="me_motivo" required 
                                placeholder="Ej: Producto caducado, Vencimiento próximo, Daño físico, etc."
                                style="width: 100%; height: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-family: Arial, sans-serif;"></textarea>
                        </div>

                        <div class="modal-btn-content">
                            <a href="javascript:void(0)" class="btn default" onclick="cerrarModalMerma()">
                                Cancelar
                            </a>
                            <button type="button" class="btn success" onclick="guardarMerma()">
                                <ion-icon name="checkmark-circle-outline"></ion-icon> Registrar Merma
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        <?php } else { ?>
            <div class="alert alert-info" style="padding: 40px; text-align: center;">
                <ion-icon name="checkmark-circle-outline" style="font-size: 48px; margin-bottom: 10px;"></ion-icon>
                <h3>Sin lotes con riesgo de caducidad</h3>
                <p>No hay productos próximos a vencer o caducados en este momento.</p>
            </div>
        <?php } ?>
    </div>

    <script>
        let mermaIdActual = null;
        let cantidadMaxActual = 0;

        function abrirFormularioMerma(lm_id, med_id, medicamento, cantidad_max) {
            mermaIdActual = lm_id;
            cantidadMaxActual = cantidad_max;
            
            document.getElementById('lm_id').value = lm_id;
            document.getElementById('med_id').value = med_id;
            document.getElementById('medicamentoNombre').value = medicamento;
            document.getElementById('me_cantidad').value = '';
            document.getElementById('me_motivo').value = '';
            document.getElementById('cantidadMax').textContent = 'Máximo: ' + cantidad_max + ' unidades';
            
            document.getElementById('modalMerma').style.display = 'flex';
        }

        function cerrarModalMerma() {
            document.getElementById('modalMerma').style.display = 'none';
            mermaIdActual = null;
        }

        function guardarMerma() {
            const lm_id = document.getElementById('lm_id').value;
            const me_cantidad = document.getElementById('me_cantidad').value;
            const me_motivo = document.getElementById('me_motivo').value;

            if (!lm_id || !me_cantidad || !me_motivo) {
                Swal.fire('Error', 'Todos los campos son obligatorios', 'error');
                return;
            }

            if (parseInt(me_cantidad) > cantidadMaxActual) {
                Swal.fire('Error', 'La cantidad no puede exceder ' + cantidadMaxActual + ' unidades', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('lm_id', lm_id);
            formData.append('me_cantidad', me_cantidad);
            formData.append('me_motivo', me_motivo);
            formData.append('mermaControllerAjax', 'crear');

            fetch('<?php echo SERVER_URL; ?>ajax/mermaAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.Alerta === 'redireccionar') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Merma Registrada',
                        text: 'La merma ha sido registrada correctamente',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = data.URL;
                    });
                } else if (data.Alerta === 'simple') {
                    Swal.fire(data.Titulo, data.Texto, data.Tipo);
                }
                cerrarModalMerma();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Ocurrió un error al procesar la merma', 'error');
                cerrarModalMerma();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalMerma');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === this) {
                        cerrarModalMerma();
                    }
                });
            }
        });
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>
