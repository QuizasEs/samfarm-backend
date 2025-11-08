<?php
if ($_SESSION['rol_smp'] != 1) {
    echo $lc->forzar_cierre_sesion_controller();
    exit();
}

require_once './controllers/medicamentoController.php';
?>

<div class='title'>
    <h1>ACTIVAR LOTE</h1>
</div>

<div class='container'>
    <form class='form FormularioAjax'
        action='<?php echo SERVER_URL; ?>ajax/loteAjax.php'
        method='POST' data-form='update' autocomplete='off'>

        <input type='hidden' name='LoteAjax' value='activar'>
        <input type="hidden" name="id" value="<?php echo $pagina[1] ?>">

        <!-- DATOS VISIBLES DEL LOTE -->
        <div class='form-title'>
            <h3>Información del Lote</h3>
        </div>

        <div class="form-labels">
            <div class="form-label-row">
                <span class="label-info">Número de lote</span>
                <span id="num-lote-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Medicamento</span>
                <span id="medicamento-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Proveedor</span>
                <span id="proveedor-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Presentación</span>
                <span id="presentacion-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Forma farmacéutica</span>
                <span id="forma-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Cantidad total</span>
                <span id="cantidad-total-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Fecha de vencimiento</span>
                <span id="fecha-venc-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Precio de compra</span>
                <span id="precio-compra-info"></span>
            </div>
            <div class="form-label-row">
                <span class="label-info">Precio venta sugerido</span>
                <span id="precio-sugerido-info"></span>
            </div>
        </div>


        <div class="form-info">
            <div class="danger-img">
                <ion-icon name="warning-outline"></ion-icon>
            </div>
            <span class="info">Registre los códigos QR o de barras de cada unidad. Si el producto no los tiene, puede dejar la lista vacía.</span>
        </div>

        <div class="form-title">
            <h4>Datos adicionales para activar</h4>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">Cantidad real recibida</label>
                <input type="number" name="Cantidad_real_reg" min="1" placeholder="Ingresar cantidad real" required>
            </div>
            <div class="form-bloque">
                <label for="">Precio de venta por unidad</label>
                <input type="number" name="precio_venta_unidad" id="precio-venta-input" step="0.01" placeholder="0.00">
            </div>
        </div>

        <div class='form-group'>
            <div class='form-bloque lista'>
                <label for=''>Observaciones (opcional)</label>
                <textarea name="Observacion_reg" placeholder="Observaciones sobre este lote..."></textarea>
            </div>
        </div>

        <div class="form-group">
            <div class="form-bloque lista">
                <label for="">Códigos QR / Barras (opcional)</label>
                <div class="form-lista" id="lista-codigos">
                    <!-- aqui se visualiza la lista de codigos -->
                </div>
                <button type="button" class="btn default" id="btn-agregar-codigo">Agregar código</button>
            </div>
        </div>

        <div class='form-buttons'>
            <button class='btn-primary'>Activar Lote</button>
        </div>
    </form>
</div>

<!-- 🧩 MODAL SIMPLE PARA AGREGAR CÓDIGOS -->
<div id="modal-codigo" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Agregar Código QR / Barra</h3>
        </div>
        <label for="" class="modal-title">Ingrese codigo</label>
        <div class="modal-bloque">

            <input type="text" id="input-codigo" placeholder="Ingrese o escanee el código aquí">
        </div>
        <div class="modal-row buttons">
            <button id="btn-guardar-codigo" class="btn default">Agregar</button>
            <button id="btn-cancelar-codigo" class="btn danger">Cancelar</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const listaCodigos = document.getElementById("lista-codigos");
        const btnAgregar = document.getElementById("btn-agregar-codigo");
        const form = document.querySelector(".FormularioAjax");

        // Modal
        const modal = document.getElementById("modal-codigo");
        const inputCodigo = document.getElementById("input-codigo");
        const btnGuardar = document.getElementById("btn-guardar-codigo");
        const btnCancelar = document.getElementById("btn-cancelar-codigo");

        let codigos = [];

        // Seguridad: si faltan elementos, no continuar
        if (!modal || !inputCodigo || !btnGuardar || !btnCancelar || !form) {
            console.warn("Script modal-codigo: faltan elementos requeridos en el DOM.");
            return;
        }

        // Abrir modal
        if (btnAgregar) {
            btnAgregar.addEventListener("click", (e) => {
                e.preventDefault();
                inputCodigo.value = "";
                modal.style.display = "flex";
                inputCodigo.focus();
            });
        }

        // Cerrar modal (botón cancelar)
        btnCancelar.addEventListener("click", () => {
            modal.style.display = "none";
        });

        // Cerrar modal al hacer click fuera del contenido
        modal.addEventListener("click", (e) => {
            // Asume que el overlay es el mismo elemento modal: si el target === modal, cerramos
            if (e.target === modal) {
                modal.style.display = "none";
            }
        });

        // Cerrar modal con Escape
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape" || e.key === "Esc") {
                if (modal.style.display === "flex") {
                    modal.style.display = "none";
                }
            }
        });

        // Guardar código
        btnGuardar.addEventListener("click", () => {
            const nuevoCodigo = inputCodigo.value.trim();
            if (nuevoCodigo === "") return alert("Debe ingresar un código válido.");

            if (!codigos.includes(nuevoCodigo)) {
                codigos.push(nuevoCodigo);
                renderCodigos();
                modal.style.display = "none";
            } else {
                alert("Este código ya fue agregado.");
            }
        });

        // Renderizar lista de códigos
        function renderCodigos() {
            if (!listaCodigos) return;
            listaCodigos.innerHTML = "";
            codigos.forEach((codigo, index) => {
                const item = document.createElement("div");
                item.className = "codigo-item";
                item.innerHTML = `
                <div class="lista_codigo">
                    <span>${codigo}</span>
                    <button type="button" class="btn eliminar warning" data-index="${index}"><ion-icon name="trash-outline"></ion-icon>Eliminar</button>
                </div>
                `;
                listaCodigos.appendChild(item);
            });

            // Eliminar código (con confirmación opcional)
            const botones = listaCodigos.querySelectorAll(".btn.eliminar");
            botones.forEach(btn => {
                btn.addEventListener("click", () => {
                    if (confirm("¿Eliminar este código?")) {
                        const idx = parseInt(btn.dataset.index, 10);
                        if (!isNaN(idx)) {
                            codigos.splice(idx, 1);
                            renderCodigos();
                        }
                    }
                });
            });
        }

        // Antes de enviar formulario → agregar inputs ocultos
        form.addEventListener("submit", (e) => {
            // No prevengo el submit: solo inyecto inputs antes de enviar
            const inputsPrevios = document.querySelectorAll("input[name^='codigo_qr_']");
            inputsPrevios.forEach(input => input.remove());

            codigos.forEach((codigo, i) => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = `codigo_qr_${i + 1}`;
                input.value = codigo;
                form.appendChild(input);
            });
            // el formulario sigue su envío normal (AJAX o submit)
        });
    });
</script>