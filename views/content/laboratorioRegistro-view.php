<?php
if ($_SESSION['rol_smp'] != 1) {
    /* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}
/* establecmos coneccion con controlador medicamentos para datos extras de select */
require_once './controllers/medicamentoController.php';
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();

?>
<div class='title'>
    <h1>Registrar Laboratorio</h1>
</div>
<!-- formulario de registro provedor -->
<div class='registro-usaurios-container'>
    <form class='form-registro-usuario FormularioAjax' action='<?php echo SERVER_URL; ?>ajax/laboratorioAjax.php' method='POST' data-form='save' autocomplete='off'>

        <input type='hidden' name='LaboratorioAjax' value='save'>
        <!-- DATOS escenciales -->
        <div class='form-title'>
            <h3>datos de laboratorio</h3>
        </div>

        <div class='form-group'>
            <div class='form-bloque'>
                <label for=''>Nombre Comercial*</label>
                <input type='text' name='Nombre_reg' placeholder='Nombres' pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength='100' required>
            </div>
            <div class='form-bloque'>
                <label for=''>Proveedor</label>
                <select class='select-style' name='proveedor_reg'>
                    <option value=''>Seleccionar</option>
                    <?php
                    foreach ($datos_select['proveedores'] as $pro) {
                    ?>
                        <option value="<?php echo $pro['pr_id'] ?>"><?php echo $pro['pr_nombres'] ?></option>

                    <?php
                    }
                    ?>

                </select>
            </div>

        </div>
        <div class='form-group'>

            <div class='form-bloque'>

                <label class='file-label'>
                    <span class='file-cta'>
                        <span class='file-label-text'>
                            Seleccionar Logo JPG, JPEG, PNG. ( MAX 5MB )*
                        </span>
                    </span>
                    <span class='file-name'></span>
                    <input class='file-input' id='imgLoad' type='file' name='logo_reg' accept='.jpg, .png, .jpeg'>

                    <!-- Vista previa de la imagen -->
                    <img class='view-img' src='<?php echo SERVER_URL; ?>views/image/default.jpg' id='img-pic' alt='Vista previa'>
                </label>

            </div>

        </div>

        <div class='form-buttons'>
            <button class='btn-primary'>Agregar</button>
        </div>

    </form>
</div>