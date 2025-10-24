<?php
if ($_SESSION['rol_smp'] != 1) {/* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
    echo $lc->forzar_cierre_sesion_controller();
    exit();
}
require_once "./controllers/medicamentoController.php";
$ins_med = new medicamentoController();
/* ide encriptada del medicamento  */
/* recabando datos para los selects */
$datos = $ins_med->datos_medicamento_controller($pagina[1]);


/* preguntamoss si hay un registro retornado de datos usuario */
if ($datos->rowCount() == 1) {
    /* scargamos los campos a editar a la varieble campos */
    $datos_select = $ins_med->datos_extras_controller(1);

    $campos = $datos->fetch();

?>
    <div class="title">
        <h1>Registro de usuarios</h1>
    </div>
    <!-- formulario de registro de usuarios -->
    <div class="registro-usaurios-container">
        <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/medicamentoAjax.php" method="POST" data-form="update" autocomplete="off">

            <input type="hidden" name="MedicamentoAjax" value="update">
            <input type="hidden" name="id" value="<?php echo $pagina[1]?>">
            <!-- DATOS PERSONALES -->
            <div class="form-title">
                <h3>datos personales</h3>
            </div>
            <div class="form-group">
                <div class="form-bloque">
                    <label for="">NOMBRE COMERCIAL*</label>
                    <input type="text" name="Nombre_up" value="<?php echo $campos['med_nombre_quimico']; ?>" placeholder="Nombre comercial" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
                </div>
                <div class="form-bloque">
                    <label for="">PRINCIPIO ACTIVO*</label>
                    <input type="text" name="Principio_up" value="<?php echo $campos['med_principio_activo'] ?>" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Ingrediente principal" required>
                </div>
                <div class="form-bloque">
                    <label for="">ACCION FARMACOLOGICA*</label>
                    <input type="text" name="Accion_up" value="<?php echo $campos['med_accion_farmacologica'] ?>" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Accion esperada" required>
                </div>
            </div>
            <div class="form-group">
                <div class="form-bloque">
                    <label for="">DESCRIPCION</label>
                    <input type="text" name="Descripcion_up" value="<?php echo $campos['med_descripcion'] ?>" placeholder="Si serequire" maxlength="100" required>
                </div>
                <div class="form-bloque">
                    <label for="">PRESENTACION*</label>
                    <input type="text" name="Presentacion_up" value="<?php echo $campos['med_presentacion'] ?>" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Metrica" required>
                </div>

            </div>
            <div class="form-group">
                <div class="form-bloque">
                    <label for="">PRECIO UNITARIO EN BS*</label>
                    <input type="text" name="Precio_unitario_up" value="<?php echo $campos['med_precio_unitario'] ?>" pattern="[0-9.]{1,10}" placeholder="Numero" maxlength="10" required>
                </div>
                <div class="form-bloque">
                    <label for="">PRECIO CAJA EN BS*</label>
                    <input type="text" name="Precio_caja_up" value="<?php echo $campos['med_precio_caja'] ?>" pattern="[0-9.]{1,10}" maxlength="10" placeholder="Numero" required>
                </div>

            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">USO FARMACOLOGICO*</label>
                    <select class="select-style" name="Uso_up">
                        <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                            <option value="<?php echo $uso['uf_id'] ?>" <?php if ($campos['uf_id'] == $uso['uf_id']) { ?> selected="" <?php } ?>><?php echo $uso['uf_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-bloque">
                    <label for="">FORMA FARMACEUTICA*</label>
                    <select class="select-style" name="Forma_up">
                        <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                            <option value="<?php echo $forma['ff_id'] ?>" <?php if ($campos['ff_id'] == $forma['ff_id']) { ?> selected="" <?php } ?>><?php echo $forma['ff_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="form-bloque">
                    <label for="">VIA DE ADMINISTRACION*</label>
                    <select class="select-style" name="Via_up">
                        <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                            <option value="<?php echo $via['vd_id'] ?>" <?php if ($campos['vd_id'] == $via['vd_id']) { ?> selected="" <?php } ?>><?php echo $via['vd_nombre'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-bloque">
                    <label for="">LABORATORIO*</label>
                    <select class="select-style" name="Laboratorio_up">
                        <?php foreach ($datos_select['laboratorios'] as $laboratorios) { ?>
                            <option value="<?php echo $laboratorios['la_id'] ?>" <?php if ($campos['la_id'] == $laboratorios['la_id']) { ?> selected="" <?php } ?>><?php echo $laboratorios['la_nombre_comercial'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-bloque">
                    <label for="">SUCURSAL*</label>
                    <select class="select-style" name="Sucursal_up">
                        <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                            <option value="<?php echo $sucursal['su_id'] ?>" <?php if ($campos['su_id'] == $sucursal['su_id']) { ?> selected="" <?php } ?>><?php echo $sucursal['su_nombre'] ?></option>
                        <?php } ?>
                        <?php ?>
                        <?php ?>

                    </select>
                </div>

            </div>
            <div class="form-buttons">
                <button class="btn-primary">Agregar</button>
            </div>






        </form>
    <?php } else { ?><!-- en caso no muestra mensage de error -->
        <div class="error-content">
            <h2>No podimos mostrar la informacion solicitada debido a un error, intentelo nuevamente mas tarde!</h2>
        </div>
    <?php } ?>
    </div>