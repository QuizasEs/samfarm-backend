<?php
if ($_SESSION['rol_smp'] != 1) {
    /* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
    echo $lc->forzar_cierre_sesion_controller();
    exit();
}
/* establecmos coneccion con controlador medicamentos para datos extras de select */
require_once './controllers/medicamentoController.php';
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();

require_once "./controllers/proveedorController.php";
$ins_lab = new proveedorController;
$datos = $ins_lab->data_laboratorio_controller($pagina[1]);



?>
<div class='title'>
    <h1>Registrar Laboratorio</h1>
</div>
<?php
if ($datos->rowCount() == 1) {
    $campos = $datos->fetch();

?>
    <!-- formulario de registro provedor -->
    <div class='registro-usaurios-container'>
        <form class='form-registro-usuario FormularioAjax' action='<?php echo SERVER_URL; ?>ajax/laboratorioAjax.php' method='POST' data-form='update' autocomplete='off'>

            <input type='hidden' name='LaboratorioAjax' value='update'>
            <input type="hidden" name="id" value="<?php echo $pagina[1] ?>">
            <!-- DATOS escenciales -->
            <div class='form-title'>
                <h3>datos de laboratorio</h3>
            </div>

            <div class='form-group'>
                <div class='form-bloque'>
                    <label for=''>Nombre Comercial*</label>
                    <input type='text' name='Nombre_up' value="<?php echo $campos['la_nombre_comercial'] ?>" placeholder='Nombres' pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength='100' required>
                </div>
                <div class='form-bloque'>
                    <label for=''>Proveedor *</label>
                    <select class='select-style' name='Proveedor_up'>
                        <?php
                        foreach ($datos_select['proveedores'] as $pro) {
                        ?>
                            <option value="<?php echo $pro['pr_id'] ?>" <?php if ($pro['pr_id'] == $campos['la_id']) {
                                                                            echo 'selected';
                                                                        } ?>><?php echo $pro['pr_nombres'] ?></option>

                        <?php
                        }
                        ?>

                    </select>
                </div>
                <div class="form-bloque">
                    <label for="">CAMBIAR ESTADO</label>
                    <select class="select-style" name="Estado_up">
                        <option value="1" <?php if ($campos['la_estado'] == 1) { ?> selected="" <?php } ?>>HABILITAR</option>
                        <option value="0" <?php if ($campos['la_estado'] == 0) { ?> selected="" <?php } ?>>DESABILITAR</option>

                    </select>
                </div>

            </div>
            <div class="form-group">

                <div class="form-bloque">

                    <label class="file-label">
                        <span class="file-cta">
                            <span class="file-label-text">
                                Seleccionar Logo JPG, JPEG, PNG. ( MAX 5MB )*
                            </span>
                        </span>
                        <span class="file-name"></span>
                        <input class="file-input" id="imgLoad" type="file" name="Logo_up" accept=".jpg, .png, .jpeg">

                        <!-- Vista previa de la imagen -->
                        <img class="view-img" src="<?php echo SERVER_URL; ?>views/assets/img/<?php echo $campos['la_logo']; ?>" id="img-pic" alt="Vista previa">
                    </label>

                </div>

            </div>

            <div class='form-buttons'>
                <button class='btn-primary'>Agregar</button>
            </div>

        </form>
    </div>
<?php } ?>