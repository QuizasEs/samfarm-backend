<?php
if ($_SESSION['rol_smp'] != 1) {
    echo $lc->forzar_cierre_sesion_controller();
    exit();
}
require_once "./controllers/sucursalController.php";
$ins_sucursal = new sucursalController();

$datos = $ins_sucursal->datos_config_empresa_controller();
$datos_decoded = json_decode($datos, true);

if (!isset($datos_decoded['error']) && $datos_decoded) {
    $campos = $datos_decoded;
?>
    <div class="title">
        <h1>Información de la Empresa</h1>
    </div>

    <div class="container">
        <form class="form FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/sucursalAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">

            <input type="hidden" name="sucursalAjax" value="actualizar_config">

            <div class="form-title">
                <h3>Datos de la Empresa</h3>
            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">NOMBRE EMPRESA*</label>
                    <input type="text" name="NombreEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_nombre'] ?? ''); ?>" placeholder="Nombre de la empresa" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
                </div>
                <div class="form-bloque">
                    <label for="">NIT / RUC*</label>
                    <input type="text" name="NITEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_nit'] ?? ''); ?>" placeholder="NIT o RUC" pattern="[a-zA-Z0-9\-]{3,50}" maxlength="50" required>
                </div>
            </div>

            <div class="form-group">
                <div class="form-bloque">
                    <label for="">DIRECCIÓN</label>
                    <input type="text" name="DireccionEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_direccion'] ?? ''); ?>" placeholder="Dirección de la empresa" maxlength="255">
                </div>
                <div class="form-bloque">
                    <label for="">TELÉFONO</label>
                    <input type="text" name="TelefonoEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_telefono'] ?? ''); ?>" placeholder="Teléfono de contacto" pattern="[0-9\-\+\s]{6,20}" maxlength="20">
                </div>
            </div>

            <div class="form-title">
                <h3>Logo de la Empresa</h3>
            </div>

            <div class="form-group">
                <div class="form-bloque" style="display: flex; flex-direction: column; gap: 15px;">
                    <label for="">LOGO (JPEG, PNG - Máximo 5MB)</label>
                    <div style="display: flex; gap: 20px; align-items: flex-start;">
                        <div>
                            <p style="font-size: 0.9em; color: #666; margin-bottom: 10px;">Logo actual:</p>
                            <img src="<?php echo htmlspecialchars($campos['ce_logo']); ?>" alt="Logo actual" onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'" style="width: 150px; height: 150px; border-radius: 4px; object-fit: contain; border: 1px solid #ddd; padding: 5px;">
                        </div>
                        <div style="flex: 1;">
                            <p style="font-size: 0.9em; color: #666; margin-bottom: 10px;">Nuevo logo:</p>
                            <input type="file" id="imgLoad" name="LogoEmpresa_edit" accept="image/jpeg,image/png" />
                            <img id="img-pic" style="display: none; width: 150px; height: 150px; border-radius: 4px; object-fit: contain; border: 1px solid #ddd; padding: 5px; margin-top: 10px;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-buttons">
                <a href="<?php echo SERVER_URL; ?>sucursalLista/" class="btn warning">Cancelar</a>
                <button class="btn success">Actualizar Información</button>
            </div>

        </form>
    <?php } else { ?>
        <div class="error-content">
            <h2>No pudimos mostrar la información solicitada debido a un error. Inténtelo nuevamente más tarde.</h2>
        </div>
    <?php } ?>
    </div>
