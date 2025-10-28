<?php
if ($_SESSION['rol_smp'] != 1) {/* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
    echo $lc->forzar_cierre_sesion_controller();
    exit();
}
require_once "./controllers/MedicamentoController.php";
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();

?>
<div class="title">
    <h1>Registro de usuarios</h1>
</div>
<!-- formulario de registro de medicamentos -->
<div class="registro-usaurios-container">
    <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/medicamentoAjax.php" method="POST" data-form="save" autocomplete="off">

        <input type="hidden" name="MedicamentoAjax" value="save">
        <!-- DATOS esenciales -->
        <div class="form-title">
            <h3>datos personales</h3>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">NOMBRE COMERCIAL*</label>
                <input type="text" name="Nombre_reg" placeholder="Nombre comercial" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">PRINCIPIO ACTIVO*</label>
                <input type="text" name="Principio_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Ingrediente principal" required>
            </div>
            <div class="form-bloque">
                <label for="">ACCION FARMACOLOGICA*</label>
                <input type="text" name="Accion_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Accion esperada" required>
            </div>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">DESCRIPCION</label>
                <input type="text" name="Descripcion_reg" placeholder="Si serequire" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">PRESENTACION*</label>
                <input type="text" name="Presentacion_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Metrica" required>
            </div>

        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">PRECIO UNITARIO*</label>
                <input type="text" name="Precio_unitario_reg" pattern="[0-9.]{1,10}" placeholder="Numero" maxlength="10" required>
            </div>
            <div class="form-bloque">
                <label for="">PRECIO CAJA*</label>
                <input type="text" name="Precio_caja_reg" pattern="[0-9.]{1,10}" maxlength="10" placeholder="Numero" required>
            </div>

        </div>

        <div class="form-group">
            <div class="form-bloque">
                <label for="">USO FARMACOLOGICO*</label>
                <select class="select-style" name="Uso_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['uso_farmacologico'] as $uso){?>
                    <option value="<?php echo $uso['uf_id']?>"><?php echo $uso['uf_nombre']?></option>
                    <?php }?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">FORMA FARMACEUTICA*</label>
                <select class="select-style" name="Forma_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['forma_farmaceutica'] as $forma) {?>
                    <option value="<?php echo $forma['ff_id']?>"><?php echo $forma['ff_nombre']?></option>
                    <?php }?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">VIA DE ADMINISTRACION*</label>
                <select class="select-style" name="Via_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['via_administracion'] as $via) {?>
                    <option value="<?php echo $via['vd_id']?>"><?php echo $via['vd_nombre']?></option>
                    <?php }?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">LABORATORIO*</label>
                <select class="select-style" name="Laboratorio_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['laboratorios'] as $laboratorios) {?>
                    <option value="<?php echo $laboratorios['la_id']?>"><?php echo $laboratorios['la_nombre_comercial']?></option>
                    <?php }?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">SUCURSAL*</label>
                <select class="select-style" name="Sucursal_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                    <option value="<?php echo $sucursal['su_id']?>"><?php echo $sucursal['su_nombre']?></option>
                    <?php }?>
                    <?php ?>
                    <?php ?>

                </select>
            </div>

        </div>
        <div class="form-buttons">
            <button class="btn-primary">Agregar</button>
        </div>






    </form>
</div>