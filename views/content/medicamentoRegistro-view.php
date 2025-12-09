<?php
if ($_SESSION['rol_smp'] != 1) {/* preguntamos que si el que intenta entrar a esta vista tien un privilegio distinto de admin que sierre su sesio */
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}
require_once "./controllers/MedicamentoController.php";
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();

?>
<div class="title">
    <h2><ion-icon name="layers-outline"></ion-icon> Registro de medicamentos</h2>
</div>
<!-- formulario de registro de medicamentos -->
<div class="container">
    <form class="form FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/medicamentoAjax.php" method="POST" data-form="save" autocomplete="off">

        <input type="hidden" name="MedicamentoAjax" value="save">
        <!-- DATOS esenciales -->
        <div class="form-title">
            <h3>datos</h3>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">NOMBRE COMERCIAL*</label>
                <input type="text" name="Nombre_reg" placeholder="Nombre comercial" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">PRINCIPIO ACTIVO*</label>
                <input type="text" name="Principio_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Ingrediente principal" required>
            </div>
            <div class="form-bloque">
                <label for="">ACCION FARMACOLOGICA*</label>
                <input type="text" name="Accion_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Accion esperada" required>
            </div>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">DESCRIPCION</label>
                <input type="text" name="Descripcion_reg" placeholder="Si serequire" maxlength="200">
            </div>
            <div class="form-bloque">
                <label for="">PRESENTACION*</label>
                <input type="text" name="Presentacion_reg" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" placeholder="Metrica" required>
            </div>

        </div>

        <div class="form-group">
            <div class="form-bloque">
                <label for="">USO FARMACOLOGICO*</label>
                <select class="select-style" name="Uso_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['uso_farmacologico'] as $uso){?>
                    <option value="<?php echo $uso['uf_id']?>"><?php echo $uso['uf_nombre']?></option>
                    <?php }?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">FORMA FARMACEUTICA*</label>
                <select class="select-style" name="Forma_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['forma_farmaceutica'] as $forma) {?>
                    <option value="<?php echo $forma['ff_id']?>"><?php echo $forma['ff_nombre']?></option>
                    <?php }?>
                </select>
            </div>
        
            <div class="form-bloque">
                <label for="">VIA DE ADMINISTRACION*</label>
                <select class="select-style" name="Via_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['via_administracion'] as $via) {?>
                    <option value="<?php echo $via['vd_id']?>"><?php echo $via['vd_nombre']?></option>
                    <?php }?>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">LABORATORIO*</label>
                <select class="select-style" name="Laboratorio_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach($datos_select['laboratorios'] as $laboratorios) {?>
                    <option value="<?php echo $laboratorios['la_id']?>"><?php echo $laboratorios['la_nombre_comercial']?></option>
                    <?php }?>
                </select>
            </div>

        </div>
        <div class="form-buttons">
            <a href="<?php echo SERVER_URL;?>medicamentoLista?>" class="btn warning">cancelar</a>
            <button class="btn success">Agregar</button>
        </div>


    </form>
</div>