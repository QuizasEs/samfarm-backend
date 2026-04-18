<?php
if (!isset($_SESSION['id_smp'])) {
?>
   <div style="text-align: center; padding: 60px;">
       <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
       <p>Debe iniciar sesión para acceder a esta sección.</p>
   </div>
<?php
   exit();
}

$id_solicitado = mainModel::decryption($pagina[1]);

if ($_SESSION['rol_smp'] != 1) {
?>
   <div style="text-align: center; padding: 60px;">
       <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
       <p>Solo los administradores pueden editar perfiles.</p>
   </div>
<?php
   exit();
}
require_once "./controllers/userController.php";
$ins_usuario = new userController();

$datos_sucursales = $ins_usuario->datos_extras_usuarios_controller();

$datos = $ins_usuario->datos_usuario_controller($pagina[1]);
$datos_decoded = json_decode($datos, true);

if (!isset($datos_decoded['error']) && $datos_decoded) {
   $campos = $datos_decoded;
?>
   <div class="pg">
       <div class="ph">
           <div>
               <div class="ptit">Editar Perfil</div>
               <div class="psub">Modifique la información del usuario seleccionado</div>
           </div>
       </div>

       <div class="card">
           <div class="cb">
               <form class="FormularioAjax" id="editar_perfil_form" action="<?php echo SERVER_URL; ?>ajax/usuariosAjax.php" method="POST" data-form="update" autocomplete="off">

                   <input type="hidden" name="usuariosAjax" value="editar_perfil">
                   <input type="hidden" name="us_id_perfil" value="<?php echo $pagina[1]?>">

                   <div class="stit">Información Personal</div>

                   <div class="fr">
                       <div class="fg">
                           <label class="fl">Nombres<span class="req">*</span></label>
                           <input class="inp" type="text" name="Nombres_perfil" value="<?php echo htmlspecialchars($campos['us_nombres']); ?>" placeholder="Nombres" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                       </div>
                       <div class="fg">
                           <label class="fl">Apellido Paterno<span class="req">*</span></label>
                           <input class="inp" type="text" name="ApellidoPaterno_perfil" value="<?php echo htmlspecialchars($campos['us_apellido_paterno']); ?>" placeholder="Apellido paterno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                       </div>
                       <div class="fg">
                           <label class="fl">Apellido Materno<span class="req">*</span></label>
                           <input class="inp" type="text" name="ApellidoMaterno_perfil" value="<?php echo htmlspecialchars($campos['us_apellido_materno']); ?>" placeholder="Apellido materno" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                       </div>
                   </div>

                   <div class="fr">
                       <div class="fg">
                           <label class="fl">Carnet<span class="req">*</span></label>
                           <input class="inp" type="text" name="Carnet_perfil" value="<?php echo htmlspecialchars($campos['us_numero_carnet']); ?>" placeholder="Número de carnet" pattern="[0-9]{6,20}" maxlength="20" required>
                       </div>
                       <div class="fg">
                           <label class="fl">Teléfono</label>
                           <input class="inp" type="text" name="Telefono_perfil" value="<?php echo htmlspecialchars($campos['us_telefono'] ?? ''); ?>" placeholder="Teléfono" pattern="[0-9]{6,20}" maxlength="20">
                       </div>
                       <div class="fg">
                           <label class="fl">Correo</label>
                           <input class="inp" type="email" name="Correo_perfil" value="<?php echo htmlspecialchars($campos['us_correo'] ?? ''); ?>" placeholder="Correo electrónico">
                       </div>
                   </div>

                   <div class="fg">
                       <label class="fl">Dirección</label>
                       <input class="inp" type="text" name="Direccion_perfil" value="<?php echo htmlspecialchars($campos['us_direccion'] ?? ''); ?>" placeholder="Dirección" maxlength="255">
                   </div>

                   <?php if ($_SESSION['rol_smp'] == 1) { ?>
                       <div class="fg">
                           <label class="fl">Sucursal<span class="req">*</span></label>
                           <select class="sel" name="Sucursal_perfil" required>
                               <option value="" disabled>Seleccione una sucursal</option>
                               <?php foreach ($datos_sucursales['sucursales'] as $sucursal) { ?>
                                   <option value="<?php echo $sucursal['su_id']; ?>" <?php echo ($sucursal['su_id'] == $campos['su_id']) ? 'selected' : ''; ?>>
                                       <?php echo htmlspecialchars($sucursal['su_nombre']); ?>
                                   </option>
                               <?php } ?>
                           </select>
                       </div>
                   <?php } ?>

                   <div class="stit">Credenciales de Acceso</div>

                   <div class="fg">
                       <label class="fl">Nombre de Usuario<span class="req">*</span></label>
                       <input class="inp" type="text" name="UsuarioName_perfil" value="<?php echo htmlspecialchars($campos['us_username']); ?>" placeholder="Nombre de usuario" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$" maxlength="100" required>
                   </div>

                   <div class="fr">
                       <div class="fg">
                           <label class="fl">Nueva Contraseña (Dejar en blanco para no cambiar)</label>
                           <input class="inp" type="password" name="Password_perfil" placeholder="Nueva contraseña (opcional)" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                       </div>
                       <div class="fg">
                           <label class="fl">Confirmar Contraseña</label>
                           <input class="inp" type="password" name="PasswordConfirm_perfil" placeholder="Confirmar contraseña (opcional)" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                       </div>
                   </div>

               </form>
           </div>
           <div class="cf">
               <a href="<?php echo SERVER_URL; ?>dashboard/" class="btn btn-gho">Cancelar</a>
               <button class="btn btn-def" form="editar_perfil_form">Actualizar Perfil</button>
           </div>
       </div>
   <?php } else { ?>
       <div class="empty">
           <ion-icon class="empi" name="alert-circle-outline"></ion-icon>
           <div class="empt">Error al cargar datos</div>
           <div class="empx">No pudimos mostrar la información solicitada. Inténtelo nuevamente más tarde.</div>
       </div>
   <?php } ?>
   </div>
