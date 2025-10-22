<?php
if ($_SESSION['rol_smp'] != 1) {/* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
    echo $lc->forzar_cierre_sesion_controller();
    exit();
}
require_once "./controllers/userController.php";
/* instanciamos controlador usuariosS */
$ins_usuario = new userController();

$datos_rol = $ins_usuario->data_rol_list_controller("Multiple", 0);
$datos_sucursal = $ins_usuario->data_sucursal_list_controller("Multiple", 0);


?>
<div class="title">
    <h1>Registro de usuarios</h1>
</div>
<!-- formulario de registro de usuarios -->
<div class="registro-usaurios-container">
    <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/userAjax.php" method="POST" data-form="save" autocomplete="off">


        <!-- DATOS PERSONALES -->
        <div class="form-title">
            <h3>datos personales</h3>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">NOMBRES*</label>
                <input type="text" name="Nombres_reg" placeholder="ingresar nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">APELLIDO PATERNO*</label>
                <input type="text" name="ApellidoPaterno_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" placeholder="ingresar apellido paterno" required>
            </div>
            <div class="form-bloque">
                <label for="">APELLIDO MATERNO*</label>
                <input type="text" name="ApellidoMaterno_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" placeholder="ingesar apellido materno" required>
            </div>
        </div>

        <div class="form-group">


            <div class="form-bloque">
                <label for="">NUMERO DE CARNET*</label>
                <input type="text" name="Carnet_reg" pattern="[0-9]{6,20}" maxlength="20" placeholder="ingresar número de carnet" required>
            </div>
            <div class="form-bloque">
                <label for="">TELEFONO O CELULAR PERSONAL</label>
                <input type="text" name="Telefono_reg" pattern="[0-9]{6,20}" maxlength="12" placeholder="ingresar telefono o celular" required>
            </div>
            <div class="form-bloque">
                <label for="">DIRECCION DE CORREO ELECTRONICO</label>
                <input type="email" name="Correo_reg" maxlength="100" placeholder="ingresar correo">
            </div>
        </div>


        <div class="form-group">
            <div class="form-bloque">
                <label for="">DIRECCION DE VIVIENDA</label>
                <input type="text" name="Direccion_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ().,#\/]{3,200}" maxlength="200" placeholder="ingresar direccion" required>
            </div>
        </div>

        <!-- DATOS DE USAURIO  -->

        <div class="form-title">
            <h3>datos de usuario</h3>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">NOMBRE DE USUARIO*</label>
                <input type="text" name="UsuarioName_reg" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}" maxlength="100" placeholder="ingresar nombre de usuario" required>
            </div>
            <div class="form-bloque">
                <label for="">CONTRASEÑA*</label>
                <input type="password" name="Password_reg" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#\]{3,100}" maxlength="100" placeholder="ingresar contraseña" required>
            </div>
            <div class="form-bloque">
                <label for="">CONTRASEÑA CONFIRMACION*</label>
                <input type="password" name="PasswordConfirm_reg" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#\]{3,100}" maxlength="100" placeholder="confirmar contraseña" required>
            </div>
        </div>
        <div class="form-group">

            <div class="form-bloque">
                <label for="">ASIGNAR SUCURSAL</label>
                <select class="select-style" name="Sucursal_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach ($datos_sucursal as $sucursal) { ?>
                        <option value="<?php echo $sucursal['su_id'];?>"><?php echo $sucursal['su_nombre']?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">ASIGNAR ROL</label>
                <select class="select-style" name="Rol_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach ($datos_rol as $rol) { ?>
                        <option value="<?php echo $rol['ro_id']; ?>"><?php echo $rol['ro_nombre'] ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="form-buttons">
            <button class="btn-primary">Agregar</button>
        </div>
    </form>
</div>