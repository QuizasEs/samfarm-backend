<?php
/* preguntamos si el que intenta acceder a este vista es para cambiar su propia informacion */
if ($lc->encryption($_SESSION['id_smp']) != $pagina[1]) {

    /* sino preguntamos si tiene lo permisos necesarios para cambiar otros usuario */
    if ($_SESSION['rol_smp'] != 1) {/* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
        echo $lc->forzar_cierre_sesion_controller();
        exit();
    }
}
?>
<div class="title">
    <h1>Actualiza usuario</h1>
</div>
<!-- formulario de registro de usuarios -->
<div class="registro-usuarios-container">
    <?php
    require_once "./controllers/userController.php";
    /* instanciamos controlador usuariosS */
    $ins_usuario = new userController();
    $datos_usuario = $ins_usuario->data_user_controller("Unico", $pagina[1]);


    /* preguntamoss si hay un registro retornado de datos usuario */
    if ($datos_usuario->rowCount() == 1) {
        /* scargamos los campos a editar a la varieble campos */

        $rol = $ins_usuario->data_rol_list_controller("Multiple", 0);
        $sucursal = $ins_usuario->data_sucursal_list_controller("Multiple", 0);
        $campos = $datos_usuario->fetch();

    ?><!-- encaso que haya una registro muestra el contenido  -->

        <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/userAjax.php" method="POST" data-form="update" autocomplete="off">
            <!-- id oculta y encriptada -->
            <input type="hidden" name="usuario_id_up" value="<?php echo $pagina[1]; ?>">
            <!-- DATOS PERSONALES -->
            <div class="form-title">
                <h3>datos de usuario</h3>
            </div>
            <div class="form-group">
                <div class="form-bloque">
                    <label for="">NOMBRES*</label>
                    <input type="text" name="Nombres_up" value="<?php echo $campos['us_nombres'] ?>" placeholder="ingresar nombre" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                </div>
                <div class="form-bloque">
                    <label for="">APELLIDO PATERNO*</label>
                    <input type="text" name="ApellidoPaterno_up" value="<?php echo $campos['us_apellido_paterno'] ?>" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" placeholder="ingresar apellido paterno" required>
                </div>
                <div class="form-bloque">
                    <label for="">APELLIDO MATERNO*</label>
                    <input type="text" name="ApellidoMaterno_up" value="<?php echo $campos['us_apellido_materno'] ?>" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" placeholder="ingesar apellido materno" required>
                </div>
            </div>

            <div class="form-group">


                <div class="form-bloque">
                    <label for="">NUMERO DE CARNET*</label>
                    <input type="text" name="Carnet_up" value="<?php echo $campos['us_numero_carnet'] ?>" pattern="[0-9]{6,20}" maxlength="20" placeholder="ingresar número de carnet" required>
                </div>
                <div class="form-bloque">
                    <label for="">TELEFONO O CELULAR PERSONAL</label>
                    <input type="text" name="Telefono_up" value="<?php echo $campos['us_telefono'] ?>" pattern="[0-9]{6,20}" maxlength="12" placeholder="ingresar telefono o celular" required>
                </div>
                <div class="form-bloque">
                    <label for="">DIRECCION DE CORREO ELECTRONICO</label>
                    <input type="email" name="Correo_up" value="<?php echo $campos['us_correo'] ?>" maxlength="100" placeholder="ingresar correo">
                </div>
            </div>


            <div class="form-group">
                <div class="form-bloque">
                    <label for="">DIRECCION DE VIVIENDA</label>
                    <input type="text" name="Direccion_up" value="<?php echo $campos['us_direccion'] ?>" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ().,#\/]{3,200}" maxlength="200" placeholder="ingresar direccion" required>
                </div>
                <div class="form-bloque">
                    <label for="">NOMBRE DE USUARIO*</label>
                    <input type="text" name="UsuarioName_up" value="<?php echo $campos['us_username'] ?>" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}" maxlength="100" placeholder="ingresar nombre de usuario" required>
                </div>
            </div>

            <!-- DATOS DE USAURIO  -->
            <?php if ($_SESSION['rol_smp'] == 1) { ?>
                <div class="form-title">
                    <h3>cambiar contraseña</h3>
                </div>
                <div class="form-group">

                    <div class="form-bloque">
                        <label for="">CONTRASEÑA*</label>
                        <input type="password" name="Password_up" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100" placeholder="ingresar contraseña" >
                    </div>
                    <div class="form-bloque">
                        <label for="">CONTRASEÑA CONFIRMACION*</label>
                        <input type="password" name="PasswordConfirm_up" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100" placeholder="confirmar contraseña" >
                    </div>
                </div>
            <?php } ?>
            <div class="form-group">

                <div class="form-bloque">
                    <label for="">ASIGNAR SUCURSAL</label>
                    <select class="select-style" name="Sucursal_up">
                        <?php foreach ($sucursal as $lista_sucursal) { ?>
                            <option value="<?php echo  $lista_sucursal['su_id']?>" <?php if ($campos['su_id'] == $lista_sucursal['su_id']) { ?> selected="" <?php } ?>><?php echo $lista_sucursal['su_nombre'] ?></option>

                        <?php } ?>
                        <?php ?>
                    </select>
                </div>
                <div class="form-bloque">
                    <label for="">ASIGNAR ROL</label>
                    <select class="select-style" name="Rol_up">
                        <?php foreach ($rol as $lista_rol) { ?>
                            <option value="<?php echo $lista_rol['ro_id'] ?>" <?php if ($campos['ro_id'] == $lista_rol['ro_id']) { ?> selected="" <?php } ?>><?php echo $lista_rol['ro_nombre'] ?></option>
                        <?php } ?>
                    </select>

                </div>
                <?php if ($_SESSION['rol_smp'] == 1 && $lc->encryption($_SESSION['rol_smp']) != $pagina[1]) { ?>

                    <div class="form-bloque">
                        <label for="">CAMBIAR ESTADO</label>
                        <select class="select-style" name="Estado_up">
                            <option value="1" <?php if ($campos['us_estado'] == 1) { ?> selected="" <?php } ?>>HABILITAR</option>
                            <option value="2" <?php if ($campos['us_estado'] == 0) { ?> selected="" <?php } ?>>DESABILITAR</option>

                        </select>
                    </div>
                <?php } ?>

            </div>



            <h3>INGRESE SUS CREDENCIALES PARA CONFIRMAR CAMBIOS</h3>
            <div class="form-group">


                <div class="form-bloque">
                    <label for="">usuario</label>
                    <input type="text" name="Usuario_confirm" value="" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}" maxlength="20" placeholder="ingresar usuario" required>
                </div>
                <div class="form-bloque">
                    <label for="">contraseña</label>
                    <input type="text" name="Password_confirm" value="" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#\]{3,100}" maxlength="12" placeholder="ingresar contraseña" required>
                </div>

            </div>
            <!-- preguntamos si quiere actualizar su propia cuenta o la de otro -->
            <?php if ($lc->encryption($_SESSION['rol_smp']) != $pagina[1]) { ?>
                <input type="hidden" name="Tipo_up" value="Inpropio">
            <?php } else { ?>
                <input type="hidden" name="Tipo_up" value="Propio">

            <?php } ?>


            <div class=" form-buttons">
                <button class="btn-primary">Agregar</button>
            </div>
        </form>
    <?php } else { ?><!-- en caso no muestra mensage de error -->
        <div class="error-content">
            <h2>No podimos mostrar la informacion solicitada debido a un error, intentelo nuevamente mas tarde!</h2>
        </div>
    <?php } ?>
</div>