<?php
if ($_SESSION['rol_smp'] != 1) {
?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb">
                <div style="text-align: center; padding: 60px;">
                    <ion-icon name="lock-closed-outline" style="font-size: 64px; color: #ccc;"></ion-icon>
                </div>
            </div>
        </div>
    </div>
<?php
    exit();
}
require_once "./controllers/sucursalController.php";
$ins_sucursal = new sucursalController();

$datos = $ins_sucursal->datos_config_empresa_controller();
$datos_decoded = json_decode($datos, true);

if (!isset($datos_decoded['error']) && $datos_decoded) {
    $campos = $datos_decoded;
?>
    <div class="">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="business-outline"></ion-icon> Información de la Empresa</div>
                <div class="psub">Configure los datos principales de su empresa</div>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="information-circle-outline"></ion-icon> Datos de la Empresa</div>
            </div>
            <div class="cb">
                <form id="formEmpresa" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/sucursalAjax.php" method="POST" data-form="update" autocomplete="off" enctype="multipart/form-data">
                    <input type="hidden" name="sucursalAjax" value="actualizar_config">

                    <div class="fr2">
                        <div class="fg">
                            <label class="fl required">Nombre Empresa</label>
                            <input class="inp" type="text" name="NombreEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_nombre'] ?? ''); ?>" placeholder="Nombre de la empresa" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
                        </div>
                        <div class="fg">
                            <label class="fl required">NIT / RUC</label>
                            <input class="inp" type="text" name="NITEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_nit'] ?? ''); ?>" placeholder="NIT o RUC" pattern="[a-zA-Z0-9\-]{3,50}" maxlength="50" required>
                        </div>
                    </div>

                    <div class="fr2">
                        <div class="fg">
                            <label class="fl">Dirección</label>
                            <input class="inp" type="text" name="DireccionEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_direccion'] ?? ''); ?>" placeholder="Dirección de la empresa" maxlength="255">
                        </div>
                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="TelefonoEmpresa_edit" value="<?php echo htmlspecialchars($campos['ce_telefono'] ?? ''); ?>" placeholder="Teléfono de contacto" pattern="[0-9\-\+\s]{6,20}" maxlength="20">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="image-outline"></ion-icon> Logo de la Empresa</div>
            </div>
            <div class="cb">
                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Logo (JPEG, PNG - Máximo 5MB)</label>
                        <div style="display: flex; gap: 20px; align-items: flex-start;">
                            <div>
                                <p class="tbs" style="margin-bottom: 10px;">Logo actual:</p>
                                <img src="<?php echo htmlspecialchars($campos['ce_logo']); ?>" alt="Logo actual" onerror="this.src='<?php echo SERVER_URL; ?>views/assets/img/predeterminado.png'" style="width: 150px; height: 150px; border-radius: 4px; object-fit: contain; border: 1px solid #ddd; padding: 5px;">
                            </div>
                            <div style="flex: 1;">
                                <p class="tbs" style="margin-bottom: 10px;">Nuevo logo:</p>
                                <input class="inp" type="file" id="imgLoad" name="LogoEmpresa_edit" accept="image/jpeg,image/png" form="formEmpresa" />
                                <img id="img-pic" style="display: none; width: 150px; height: 150px; border-radius: 4px; object-fit: contain; border: 1px solid #ddd; padding: 5px; margin-top: 10px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div class="fr1" style="justify-content: flex-end;">
                    <a href="<?php echo SERVER_URL; ?>sucursalLista/" class="btn btn-war">Cancelar</a>
                    <button type="submit" form="formEmpresa" class="btn btn-def">Actualizar Información</button>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="pg">
            <div class="ph">
                <div>
                    <div class="ptit"><ion-icon name="alert-circle-outline"></ion-icon> Error</div>
                    <div class="psub">No se pudo cargar la información</div>
                </div>
            </div>
            <div class="card">
                <div class="cb">
                    <div style="text-align: center; padding: 60px;">
                        <ion-icon name="alert-circle-outline" style="font-size: 64px; color: #f44336;"></ion-icon>
                        <p class="tbs" style="margin-top: 20px;">No pudimos mostrar la información solicitada debido a un error. Inténtelo nuevamente más tarde.</p>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
