<?php
if ($lc->encryption($_SESSION['id_smp']) != $pagina[1]) {

    if ($_SESSION['rol_smp'] != 1) {/* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
        echo $lc->forzar_cierre_sesion_controller();
        exit();
    }
}
require_once "./controllers/proveedorController.php";
$ins_pro = new proveedorController();
$datos = $ins_pro->datos_proveedor_controller($pagina[1]);


?>
<div class="title">
    <h1>Actualizar proveedor</h1>
</div>
<!-- formulario de registro provedor -->
<div class="registro-usaurios-container">

    <!--  -->
    <?php
    /* preguntamos si existe registro a editar */
    if ($datos->rowCount() > 0) {
        $campo = $datos->fetch();
    }
    ?>
    <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/proveedorAjax.php" method="POST" data-form="update" autocomplete="off">

        <input type="hidden" name="ProveedorAjax" value="update">
        <input type="hidden" name="id" value="<?php echo $pagina[1]?>">
        <!-- DATOS escenciales -->
        <div class="form-title">
            <h3>datos personales</h3>
        </div>

        <div class="form-group">
            <div class="form-bloque">
                <label for="">nombres*</label>
                <input type="text" name="Nombre_up" value="<?php echo $campo['pr_nombres'] ?>" placeholder="Nombres" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">apellido paterno*</label>
                <input type="text" name="Apellido_paterno_up" value="<?php echo $campo['pr_apellido_paterno'] ?>" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Apellido paterno" required>
            </div>
            <div class="form-bloque">
                <label for="">apellido materno*</label>
                <input type="text" name="Apellido_materno_up" value="<?php echo $campo['pr_apellido_materno'] ?>" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Apellido materno" required>
            </div>
            <div class="form-bloque">
                <label for="">telefono*</label>
                <input type="text" name="Telefono_up" value="<?php echo $campo['pr_telefono'] ?>" pattern="[0-9.]{1,100}" placeholder="Telefono o celular" maxlength="100" required>
            </div>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">CAMBIAR ESTADO</label>
                <select class="select-style" name="Estado_up">
                    <option value="1" <?php if ($campo['pr_estado'] == 1) { ?> selected="" <?php } ?>>HABILITAR</option>
                    <option value="0" <?php if ($campo['pr_estado'] == 0) { ?> selected="" <?php } ?>>DESABILITAR</option>

                </select>
            </div>
        </div>


        <div class="form-buttons">
            <button class="btn-primary">Agregar</button>
        </div>






    </form>
</div>