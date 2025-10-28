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
    <h1>Registrar proveedor</h1>
</div>
<!-- formulario de registro provedor -->
<div class="registro-usaurios-container">
    <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/proveedorAjax.php" method="POST" data-form="save" autocomplete="off">

        <input type="hidden" name="ProveedorAjax" value="save">
        <!-- DATOS escenciales -->
        <div class="form-title">
            <h3>datos personales</h3>
        </div>

        <div class="form-group">
            <div class="form-bloque">
                <label for="">nombres*</label>
                <input type="text" name="Nombre_reg" placeholder="Nombres" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">apellido paterno*</label>
                <input type="text" name="Apellido_paterno_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Apellido paterno" required>
            </div>
            <div class="form-bloque">
                <label for="">apellido materno*</label>
                <input type="text" name="Apellido_materno_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Apellido materno" required>
            </div>
            <div class="form-bloque">
                <label for="">telefono*</label>
                <input type="text" name="Telefono_reg" pattern="[0-9.]{1,100}" placeholder="Telefono o celular" maxlength="100" required>
            </div>
        </div>


        <div class="form-buttons">
            <button class="btn-primary">Agregar</button>
        </div>






    </form>
</div>