<?php
if ($_SESSION['rol_smp'] != 1) {
    echo $lc->forzar_cierre_sesion_controller();
    exit();
}

require_once './controllers/loteController.php';

$ins_lote = new loteController();

$datos = $ins_lote->datos_lote_controller($pagina[1]);

$lote = $datos->fetch();
if ($datos->rowCount() == 1) {
    
?>


    <div class='title'>
        <h1>ACTIVAR LOTE</h1>
    </div>

    <div class='container'>
        <form class='form FormularioAjax formCodigos'
            action='<?php echo SERVER_URL; ?>ajax/loteAjax.php'
            method='POST' data-form='update' autocomplete='off'>

            <input type='hidden' name='loteAjax' value='active'>
            <input type="hidden" name="id" value="<?php echo $pagina[1] ?>">

            <!-- DATOS VISIBLES DEL LOTE -->
            <div class='form-title'>
                <h3>Información del Lote</h3>
            </div>

            <div class="form-labels">
                <div class="form-label-row">
                    <span class="label-info">Número de lote</span>
                    <span ><?php echo $lote['lm_numero_lote'];?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Medicamento</span>
                    <span ><?php echo $lote['med_nombre'];?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Proveedor</span>
                    <span ><?php echo $lote['proveedor_nombres'];?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Forma farmacéutica</span>
                    <span ><?php echo $lote['forma_farmaceutica']?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Cantidad total</span>
                    <span ><?php echo $lote['lm_cantidad_inicial']?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Fecha de vencimiento</span>
                    <span ><?php echo $lote['lm_fecha_ingreso']?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Fecha de vencimiento</span>
                    <span ><?php echo $lote['lm_fecha_vencimiento']?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Precio de compra</span>
                    <span ><?php echo $lote['lm_precio_compra']?> Bs</span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Precio venta sugerido</span>
                    <span ><?php echo $lote['lm_precio_venta']?> Bs</span>
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
                    <label for="">Cantidad Unidad de ventas</label>
                    <input type="number" name="Cantidad_real_reg" min="1" placeholder="Ingresar cantidad real" required>
                </div>
                <div class="form-bloque">
                    <label for="">Precio de venta por unidad</label>
                    <input type="number"  name="Precio_venta_reg" value="<?php echo $lote['lm_precio_venta']?>" id="precio-venta-input" step="0.01" placeholder="0.00">
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
            <div class="modal-bloque ">
                <button id="btn-guardar-codigo" class="btn success">Agregar</button>
                <button id="btn-cancelar-codigo" class="btn warning">Cancelar</button>
            </div>
        </div>
    </div>
<?php
} else { ?>
    <div class='title'>
        <h1>parece que occurio un error</h1>
    </div>
<?php
}
?>