<?php
    require_once __DIR__ . '/../../controllers/sucursalController.php';
    $ins_sucursal = new sucursalController();
    $config_json = $ins_sucursal->datos_config_empresa_controller();
    $config = json_decode($config_json, true);
    $nombre_empresa = $config['ce_nombre'] ?? '';
    if (empty($nombre_empresa)) {
        $nombre_empresa = 'SamFarm';
    }
?>
<div class="sf-login-wrapper">
    <div class="sf-login-spacer"></div>
    <div class="sf-login-side">
        <div class="sf-login-card">
            <header class="sf-login-header">
                <h1 class="sf-login-title"><?php echo htmlspecialchars($nombre_empresa); ?></h1>
                <span class="sf-login-subtitle">Acceso al sistema</span>
            </header>
            
            <form action="" method="POST" autocomplete="off">
                <div class="sf-login-group">
                    <label class="sf-login-label">Nombre de Usuario</label>
                    <div class="sf-login-input-wrapper">
                        <input class="inp" type="text" name="Usuario_log" class="sf-login-input" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}" maxlength="100" placeholder="Tu usuario" required>
                    </div>
                </div>
                
                <div class="sf-login-group">
                    <label class="sf-login-label">Contraseña</label>
                    <div class="sf-login-input-wrapper">
                        <input class="inp" type="password" name="Password_log" class="sf-login-input" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#\]{3,100}" maxlength="100" placeholder="Tu contraseña" required>
                    </div>
                </div>
                
                <button type="submit" class="sf-login-button">Entrar</button>
            </form>
        </div>
    </div>
</div>

<?php 
    if(isset($_POST['Usuario_log']) && isset($_POST['Password_log'])){
        require_once "./controllers/loginController.php";
        $ins_login = new loginController();
        echo $ins_login->iniciar_sesion_controller();
    }
?>
