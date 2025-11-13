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
        <h1>Editar lote</h1>
    </div>

    <div class='container'>
        <form class='form FormularioAjax formCodigos'
            action='<?php echo SERVER_URL; ?>ajax/loteAjax.php'
            method='POST' data-form='update' autocomplete='off'>

            <input type='hidden' name='loteAjax' value='update'>
            <input type="hidden" name="id" value="<?php echo $pagina[1] ?>">

            <!-- DATOS VISIBLES DEL LOTE -->
            <div class='form-title'>
                <h3>Información del Lote</h3>
            </div>

            <div class="form-labels">
                <div class="form-label-row">
                    <span class="label-info">Número de lote</span>
                    <span><?php echo $lote['lm_numero_lote']; ?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Medicamento</span>
                    <span><?php echo $lote['med_nombre']; ?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Proveedor</span>
                    <span><?php echo $lote['proveedor_nombres']; ?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Forma farmacéutica</span>
                    <span><?php echo $lote['forma_farmaceutica'] ?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Via de Administracion </span>
                    <span><?php echo $lote['via_administracion'] ?> Paquetes</span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Uso farmacologico </span>
                    <span><?php echo $lote['uso_farmacologico'] ?> Paquetes</span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Fecha de engreso</span>
                    <span><?php echo $lote['lm_fecha_ingreso'] ?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Fecha de vencimiento</span>
                    <span><?php echo $lote['lm_fecha_vencimiento'] ?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Precio de compra</span>
                    <span><?php echo $lote['lm_precio_compra'] ?> Bs</span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Precio venta sugerido</span>
                    <span><?php echo $lote['lm_precio_venta'] ?> Bs</span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Estado del lote</span>
                    <span><?php echo $lote['lm_estado'] ?></span>
                </div>
                <div class="form-label-row">
                    <span class="label-info">Codigo de barras medicamento</span>
                    <span><?php echo isset($lote['med_codigo_barras'])? '' : 'Sin Codigo'; ?></span>
                </div>

            </div>


            <div class="form-info">
                <div class="danger-img">
                    <ion-icon name="warning-outline"></ion-icon>
                </div>
                <span class="info">Verifique apropiadamente la informacion que desea modificar de este lote, cualquier cambio puede influir de manera negativa al inventario. <br>

                </span>
            </div>

            <div class="form-title">
                <h4>Datos Editables</h4>
            </div>
            <div class="form-group">
                <div class="form-bloque">
                    <label for="">cantidad blister por caja</label>
                    <small>Solo en caso que sea necesario</small>
                    <input type="number" value="<?php echo $lote['lm_cant_blister'] ?>" name="Cantidad_blister_up" id="" placeholder="Por defecto 1">
                </div>
                <div class="form-bloque">
                    <label for="">Cantidad unidades por blister</label>
                    <small>Solo en caso que sea necesario</small>
                    <input type="number" value="<?php echo $lote['lm_cant_unidad'] ?>" class="Cantidad_unidades_up" placeholder="Por defecto 1">
                </div>
                <!-- solo para el administrador -->
                <?php if ($_SESSION['id_smp'] == 1 || $_SESSION['id_smp'] == 2) { ?>
                    <?php if ($_SESSION['id_smp'] == 1) { ?>
                        <div class="form-bloque">
                            <label for="">Precio de compra</label>
                            <small>Monto total de compra del lote en Bs</small>
                            <input type="number" value="<?php echo $lote['lm_precio_compra'] ?>" name="Precio_compra_up" id="" placeholder="Precio de lote" required>
                        </div>
                    <?php } ?>
                    <!-- solo para el administrador o por gerente -->
                    <div class="form-bloque">
                        <label for="">Precio venta por unidad</label>
                        <small>Monto por unidad a la venta en Bs</small>
                        <input type="number" value="<?php echo $lote['lm_precio_venta'] ?>" name="Precio_venta_up" id="" placeholder="Ej. pastilla a 1bs" required>
                    </div>
                    <!-- solo para admin o gerente -->
                    <div class="form-bloque">
                        <label for="">fecha de vencimiento</label>
                        <input type="date" value="<?php echo $lote['lm_fecha_vencimiento'] ?>" name="Fecha_vencimiento_up" id="" required>
                    </div>

                <?php } ?>

            </div>

            <!-- <div class='form-group'>
                <div class='form-bloque lista'>
                    <label for=''>Observaciones (opcional)</label>
                    <textarea name="Observacion_reg" placeholder="Observaciones sobre este lote..."></textarea>
                </div>
            </div> -->



            <div class='form-buttons'>
                <button class='btn-primary'>Activar Lote</button>
            </div>
        </form>
    </div>

    <!-- 🧩 MODAL SIMPLE PARA AGREGAR CÓDIGOS -->

<?php
} else { ?>
    <div class='title'>
        <h1>parece que occurio un error</h1>
    </div>
<?php
}
?>