<div class="container is-fluid">
    <h1 class="title">Actualizar Precios Masivamente</h1>
    <h2 class="subtitle">Desde archivo CSV</h2>
</div>

<div class="container is-fluid">
    <div class="notification is-info is-light">
        <strong>Instrucciones:</strong><br>
        1. El archivo a cargar debe ser en formato <strong>CSV</strong>.<br>
        2. El archivo debe tener dos columnas: la primera para el <strong>nombre del medicamento</strong> y la segunda para el <strong>nuevo precio de venta unitario</strong>.<br>
        3. La primera fila del archivo (cabecera) será ignorada.<br>
        4. El separador de columnas puede ser la coma (,) o el punto y coma (;).<br>
        5. Se generarán archivos de registro para los medicamentos no encontrados o con múltiples coincidencias.
    </div>

    <form action="" method="POST" class="box" enctype="multipart/form-data" id="uploadForm">
        <label>Seleccionar archivo CSV</label><br>
        <div class="file has-name is-fullwidth">
            <label class="file-label">
                <input class="file-input" type="file" name="archivo_excel" accept=".csv" id="fileInput">
                <span class="file-cta">
                    <span class="file-icon">
                        <i class="fas fa-upload"></i>
                    </span>
                    <span class="file-label">
                        Elegir archivo…
                    </span>
                </span>
                <span class="file-name" id="fileName">
                    No se ha seleccionado ningún archivo
                </span>
            </label>
        </div>
        
        <!-- Área de arrastrar y soltar -->
        <div id="dropArea" class="drop-area">
            <p>Arrastra y suelta tu archivo CSV aquí</p>
            <p class="small-text">o haz clic para seleccionar</p>
        </div>
        
        <br>
        <p class="has-text-centered">
            <button type="submit" class="button is-success is-rounded">Procesar Archivo</button>
        </p>
    </form>

    <?php
        // Incluir el script de procesamiento
        if (isset($_FILES['archivo_excel'])) {
            require_once "./scripts_precios/actualizar_precios.php";
        }
    ?>
</div>

<style>
.drop-area {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
    cursor: pointer;
    margin-bottom: 20px;
}

.drop-area:hover {
    border-color: #00d1b2;
    background-color: #f0fff8;
}

.drop-area.active {
    border-color: #00d1b2;
    background-color: #e8fff5;
    transform: scale(1.02);
}

.drop-area p {
    margin: 0 0 10px 0;
    font-weight: bold;
    color: #333;
}

.small-text {
    font-size: 0.9em;
    color: #666;
}

.file-input {
    display: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const dropArea = document.getElementById('dropArea');
    const uploadForm = document.getElementById('uploadForm');

    // Manejar cambio de archivo
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            fileName.textContent = fileInput.files[0].name;
            dropArea.style.borderColor = '#00d1b2';
            dropArea.style.backgroundColor = '#e8fff5';
        }
    });

    // Manejar arrastrar y soltar
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.classList.add('active');
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.classList.remove('active');
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.classList.remove('active');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            // Simular la selección del archivo
            const fileList = new DataTransfer();
            fileList.items.add(files[0]);
            fileInput.files = fileList.files;
            
            // Actualizar el nombre del archivo
            fileName.textContent = files[0].name;
            dropArea.style.borderColor = '#00d1b2';
            dropArea.style.backgroundColor = '#e8fff5';
        }
    });

    // Hacer clic en el área de arrastrar y soltar abre el selector de archivos
    dropArea.addEventListener('click', () => {
        fileInput.click();
    });

    // Validar que se haya seleccionado un archivo antes de enviar
    uploadForm.addEventListener('submit', (e) => {
        if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('Por favor, selecciona un archivo CSV para procesar.');
        }
    });
});
</script>
