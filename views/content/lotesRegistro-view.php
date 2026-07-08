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
    <h1>Registrar Nuevo Lote</h1>
</div>
<!-- formulario de registro lotess -->
<div class="registro-usaurios-container">
    <form class="form-registro-usuario FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/loteAjax.php" method="POST" data-form="save" autocomplete="off">

        <input type="hidden" name="loteAjax" value="save">
        <!-- DATOS escenciales -->
        <div class="form-title">
            <h3>datos personales</h3>
        </div>
        <div class="form_group">
            <div class="lista-header">
                <div class="filtro">
                    <input type="text" name="" id=""><button><ion-icon name="search"></ion-icon></button>
                </div>
            </div>
            <div class="table-container">

                <table class="table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>cofarm</td>
                            <td>10bs</td>
                            <td>500 unidades</td>
                            <td><span class="estate"> Disponible</span></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">Fecha de vencimiento*</label>
                <input type="date" name="Fecha_reg" placeholder="Nombre comercial" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑüÜ\s.,#°ºª()\-\/+']{3,100}" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">Cantidad ingresada*</label>
                <input type="text" name="Cantidad_reg" pattern="[0-9.]{1,100}" maxlength="100" placeholder="Ingrediente principal" required>
            </div>
            <div class="form-bloque">
                <label for="">precio compra*</label>
                <input type="text" name="Precio_compra_reg" pattern="[0-9.]{1,100}" maxlength="100" placeholder="Accion esperada" required>
            </div>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">precio de venta*</label>
                <input type="text" name="precio_venta_reg" pattern="[0-9.]{1,100}" placeholder="Si serequire" maxlength="100" required>
            </div>
            <div class="form-bloque">
                <label for="">PROVEEDOR*</label>
                <select class="select-style" name="proveedor_reg">
                    <option value="">SELECCIONAR</option>
                    <option value=""></option>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">SUCURSAL*</label>
                <select class="select-style" name="Sucursal_reg">
                    <option value="">SELECCIONAR</option>
                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                    <?php } ?>


                </select>
            </div>


        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">Costo total*</label>
                <input type="text" name="total_reg" pattern="[0-9.]{1,100}" placeholder="Si serequire" maxlength="100" required>
            </div>



        </div>

        <div class="form-buttons">
            <button class="btn-primary">Agregar</button>
        </div>






    </form>
</div>