<?php
if ($_SESSION['rol_smp'] != 1) {
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}
require_once "./controllers/userController.php";
$ins_usuario = new userController();

$datos = $ins_usuario->datos_usuario_controller($pagina[1]);
$datos_decoded = json_decode($datos, true);

if (!isset($datos_decoded['error']) && $datos_decoded) {
    $campos = $datos_decoded;
?>
    <div class="title">
        <h1>Editar Perfil</h1>
    </div>

    <div class="container">
        <form class="form FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/usuariosAjax.php" method="POST" data-form="update" autocomplete="off">

            <input type="hidden" name="usuariosAjax" value="editar_perfil">
            <input type="hidden" name="us_id_perfil" value="<?php echo $pagina[1]?>">

            <div class="form-title">
                <h3>Información Personal</h3>
            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">NOMBRES*</label>
                    <input type="text" name="Nombres_perfil" value="<?php echo htmlspecialchars($campos['us_nombres']); ?>" placeholder="Nombres" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                </div>
                <div class="form-bloque">
                    <label for="">APELLIDO PATERNO*</label>
                    <input type="text" name="ApellidoPaterno_perfil" value="<?php echo htmlspecialchars($campos['us_apellido_paterno']); ?>" placeholder="Apellido paterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                </div>
                <div class="form-bloque">
                    <label for="">APELLIDO MATERNO*</label>
                    <input type="text" name="ApellidoMaterno_perfil" value="<?php echo htmlspecialchars($campos['us_apellido_materno']); ?>" placeholder="Apellido materno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                </div>
            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">CARNET*</label>
                    <input type="text" name="Carnet_perfil" value="<?php echo htmlspecialchars($campos['us_numero_carnet']); ?>" placeholder="Número de carnet" pattern="[0-9]{6,20}" maxlength="20" required>
                </div>
                <div class="form-bloque">
                    <label for="">TELÉFONO</label>
                    <input type="text" name="Telefono_perfil" value="<?php echo htmlspecialchars($campos['us_telefono'] ?? ''); ?>" placeholder="Teléfono" pattern="[0-9]{6,20}" maxlength="20">
                </div>
                <div class="form-bloque">
                    <label for="">CORREO</label>
                    <input type="email" name="Correo_perfil" value="<?php echo htmlspecialchars($campos['us_correo'] ?? ''); ?>" placeholder="Correo electrónico">
                </div>
            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">DIRECCIÓN</label>
                    <input type="text" name="Direccion_perfil" value="<?php echo htmlspecialchars($campos['us_direccion'] ?? ''); ?>" placeholder="Dirección" maxlength="255">
                </div>
            </div>

            <div class="form-title">
                <h3>Credenciales de Acceso</h3>
            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">NOMBRE DE USUARIO*</label>
                    <input type="text" name="UsuarioName_perfil" value="<?php echo htmlspecialchars($campos['us_username']); ?>" placeholder="Nombre de usuario" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$" maxlength="100" required>
                </div>
            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">NUEVA CONTRASEÑA (Dejar en blanco para no cambiar)</label>
                    <input type="password" name="Password_perfil" placeholder="Nueva contraseña (opcional)" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                </div>
                <div class="form-bloque">
                    <label for="">CONFIRMAR CONTRASEÑA</label>
                    <input type="password" name="PasswordConfirm_perfil" placeholder="Confirmar contraseña (opcional)" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                </div>
            </div>

            <div class="form-buttons">
                <a href="<?php echo SERVER_URL; ?>usuarios/" class="btn warning">Cancelar</a>
                <button class="btn success">Actualizar Perfil</button>
            </div>

        </form>
    <?php } else { ?>
        <div class="error-content">
            <h2>No pudimos mostrar la información solicitada debido a un error. Inténtelo nuevamente más tarde.</h2>
        </div>
    <?php } ?>
    </div>
