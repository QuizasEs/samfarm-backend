<?php
// Verificar permisos - Solo Admin y Gerente pueden acceder
if (!in_array($_SESSION['rol_smp'], [1, 2])) {
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}

require_once './controllers/ingresoMasivoController.php';
$ins_ingreso = new ingresoMasivoController();
$datos_iniciales = $ins_ingreso->obtener_datos_iniciales_controlador();
?>

<div class="title">
    <h1>Ingreso Masivo de Inventario</h1>
    <p>Permite cargar inventario desde un archivo Excel de manera rápida y sencilla.</p>
</div>

<div class="container">
    <!-- Instrucciones -->
    <div class="form-container" style="margin-bottom: 20px;">
        <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; border-left: 4px solid #2196F3;">
            <h3 style="margin-top: 0; color: #1565C0;">
                <ion-icon name="information-circle-outline"></ion-icon> Instrucciones
            </h3>
            <ul style="margin-bottom: 0; padding-left: 20px; color: #333;">
                <li>Seleccione la sucursal donde se realizará el ingreso</li>
                <li>El archivo Excel debe contener las siguientes columnas:</li>
                <ul>
                    <li><strong>Descripción</strong> - Nombre del medicamento (obligatorio)</li>
                    <li><strong>Proveedor</strong> - ID del proveedor (opcional)</li>
                    <li><strong>Total Unidades</strong> - Cantidad total de unidades (obligatorio)</li>
                    <li><strong>Unidades por caja</strong> - Cantidad de unidades por caja (opcional, por defecto 1)</li>
                    <li><strong>Costo Unitario</strong> - Precio de adquisición por unidad (obligatorio)</li>
                    <li><strong>Precio Unitario</strong> - Precio de venta al público por unidad (obligatorio)</li>
                    <li><strong>Vencimiento</strong> - Fecha de vencimiento (opcional, formato: YYYY-MM-DD)</li>
                    <li><strong>Número de Lote</strong> - Número de lote del producto (opcional)</li>
                </ul>
                <li>Los medicamentos que no existan se crearán automáticamente</li>
                <li>Las categorías (Uso, Forma, Vía) se asignarán como "N/A" (ID: 10)</li>
                <li>El principio activo se asignará como "N/A"</li>
                <li>Los lotes se crearán en estado <strong>ACTIVO</strong></li>
            </ul>
        </div>
    </div>

    <!-- Formulario de carga -->
    <div class="form-container">
        <h2>Cargar Archivo Excel</h2>
        
        <form id="form-ingreso-masivo" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-bloque">
                    <label for="sucursal_id">Sucursal <span class="obligatorio">*</span></label>
                    <select id="sucursal_id" name="sucursal_id" required>
                        <option value="">Seleccione Sucursal...</option>
                        <?php foreach ($datos_iniciales['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id']; ?>">
                                <?php echo htmlspecialchars($sucursal['su_nombre']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-bloque">
                    <label for="archivo_excel">Archivo Excel (.xlsx o .xls) <span class="obligatorio">*</span></label>
                    <input type="file" id="archivo_excel" name="archivo_excel" accept=".xlsx,.xls" required>
                    <small style="color: #666;">Tamaño máximo recomendado: 5MB</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-bloque">
                    <button type="submit" class="btn primary" id="btn-procesar">
                        <ion-icon name="cloud-upload-outline"></ion-icon> Procesar Ingreso Masivo
                    </button>
                    <button type="button" class="btn secondary" id="btn-limpiar" onclick="limpiarFormulario()">
                        <ion-icon name="refresh-outline"></ion-icon> Limpiar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Área de resultados -->
    <div id="resultados-container" style="display: none; margin-top: 20px;">
        <div class="form-container">
            <h2>Resultados del Procesamiento</h2>
            <div id="resultados-detalle"></div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading-overlay" style="display: none;">
        <div class="loading-content">
            <ion-icon name="sync-outline" class="spinning"></ion-icon>
            <p>Procesando archivo Excel...</p>
            <small>Por favor espere, esto puede tomar unos minutos</small>
        </div>
    </div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-content {
    background: white;
    padding: 40px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
}

.loading-content ion-icon {
    font-size: 50px;
    color: #2196F3;
    animation: spin 1s linear infinite;
}

.loading-content p {
    margin: 20px 0 10px;
    font-size: 18px;
    font-weight: bold;
}

.loading-content small {
    color: #666;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.spinning {
    animation: spin 1s linear infinite;
}

.resultado-exitoso {
    background: #e8f5e9;
    border-left: 4px solid #4CAF50;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 4px;
}

.resultado-error {
    background: #ffebee;
    border-left: 4px solid #f44336;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 4px;
}

.detalle-item {
    padding: 5px 0;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.detalle-item:last-child {
    border-bottom: none;
}

.btn.loading {
    opacity: 0.7;
    pointer-events: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-ingreso-masivo');
    const btnProcesar = document.getElementById('btn-procesar');
    const loadingOverlay = document.getElementById('loading-overlay');
    const resultadosContainer = document.getElementById('resultados-container');
    const resultadosDetalle = document.getElementById('resultados-detalle');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const sucursalId = document.getElementById('sucursal_id').value;
        const archivoInput = document.getElementById('archivo_excel');

        // Validaciones
        if (!sucursalId) {
            Swal.fire('Error', 'Debe seleccionar una sucursal.', 'error');
            return;
        }

        if (!archivoInput.files.length) {
            Swal.fire('Error', 'Debe seleccionar un archivo Excel.', 'error');
            return;
        }

        const archivo = archivoInput.files[0];
        const extension = archivo.name.split('.').pop().toLowerCase();
        
        if (!['xlsx', 'xls'].includes(extension)) {
            Swal.fire('Error', 'El archivo debe ser un Excel (.xlsx o .xls).', 'error');
            return;
        }

        // Mostrar loading
        btnProcesar.classList.add('loading');
        btnProcesar.innerHTML = '<ion-icon name="sync-outline" class="spinning"></ion-icon> Procesando...';
        
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }

        // Crear FormData
        const formData = new FormData();
        formData.append('accion', 'procesar_ingreso');
        formData.append('sucursal_id', sucursalId);
        formData.append('archivo_excel', archivo);

        // Enviar solicitud
        fetch('<?php echo SERVER_URL; ?>ajax/ingresoMasivoAjax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text.substring(0, 500));
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('La respuesta del servidor no es válida: ' + text.substring(0, 200));
            }
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            // Ocultar loading
            btnProcesar.classList.remove('loading');
            btnProcesar.innerHTML = '<ion-icon name="cloud-upload-outline"></ion-icon> Procesar Ingreso Masivo';
            
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }

            if (data.Tipo === 'error') {
                Swal.fire(data.Titulo, data.texto, 'error');
            } else {
                // Mostrar resultados
                let htmlResultados = '';
                
                if (data.resultados) {
                    htmlResultados += '<div class="resultado-exitoso">';
                    htmlResultados += '<strong>Procesamiento completado</strong><br>';
                    htmlResultados += '✓ Registros exitosos: ' + data.resultados.exitosos + '<br>';
                    htmlResultados += '✗ Registros con error: ' + data.resultados.errores;
                    htmlResultados += '</div>';

                    if (data.resultados.detalles && data.resultados.detalles.length > 0) {
                        htmlResultados += '<div style="max-height: 400px; overflow-y: auto; margin-top: 15px;">';
                        htmlResultados += '<h4>Detalle:</h4>';
                        
                        data.resultados.detalles.forEach(function(detalle) {
                            const mensaje = detalle.mensaje || '';
                            if (mensaje.startsWith('OK:')) {
                                htmlResultados += '<div class="detalle-item" style="color: green;">✓ ' + mensaje + '</div>';
                            } else if (mensaje.startsWith('Error:') || mensaje.includes('Error')) {
                                htmlResultados += '<div class="detalle-item" style="color: red;">✗ Fila ' + detalle.fila + ': ' + mensaje + '</div>';
                            } else {
                                htmlResultados += '<div class="detalle-item">⚠ Fila ' + detalle.fila + ': ' + mensaje + '</div>';
                            }
                        });
                        
                        htmlResultados += '</div>';
                    }
                }

                resultadosDetalle.innerHTML = htmlResultados;
                resultadosContainer.style.display = 'block';

                if (data.Tipo === 'success') {
                    Swal.fire(data.Titulo, data.texto, 'success');
                } else {
                    Swal.fire(data.Titulo, data.texto, 'warning');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Ocultar loading
            btnProcesar.classList.remove('loading');
            btnProcesar.innerHTML = '<ion-icon name="cloud-upload-outline"></ion-icon> Procesar Ingreso Masivo';
            
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }

            Swal.fire('Error', 'Ocurrió un error al procesar el archivo: ' + error.message, 'error');
        });
    });
});

function limpiarFormulario() {
    document.getElementById('form-ingreso-masivo').reset();
    document.getElementById('resultados-container').style.display = 'none';
    document.getElementById('resultados-detalle').innerHTML = '';
}
</script>

<?php
// Agregar el overlay de loading al HTML
echo '
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); z-index: 9999;">
    <div class="loading-content" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 10px; text-align: center; max-width: 400px;">
        <ion-icon name="sync-outline" style="font-size: 50px; color: #2196F3; animation: spin 1s linear infinite;"></ion-icon>
        <p style="margin: 20px 0 10px; font-size: 18px; font-weight: bold;">Procesando archivo Excel...</p>
        <small style="color: #666;">Por favor espere, esto puede tomar unos minutos</small>
    </div>
</div>
<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
';
?>
