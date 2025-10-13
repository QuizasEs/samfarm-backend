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
                <input type="text" name="Nombres_reg" placeholder="ingresar nombre" required>
            </div>
            <div class="form-bloque">
                <label for="">APELLIDO PATERNO*</label>
                <input type="text" name="ApellidoPaterno_reg" placeholder="ingresar apellido paterno" required>
            </div>
            <div class="form-bloque">
                <label for="">APELLIDO MATERNO*</label>
                <input type="text" name="ApellidoMaterno_reg" placeholder="ingesar apellido materno" required>
            </div>
        </div>

        <div class="form-group">


            <div class="form-bloque">
                <label for="">NUMERO DE CARNET*</label>
                <input type="text" name="Carnet_reg" placeholder="ingresar carnet" required>
            </div>
            <div class="form-bloque">
                <label for="">TELEFONO O CELULAR PERSONAL</label>
                <input type="text" name="Telefono_reg" placeholder="ingresar telefono o celular" required>
            </div>
            <div class="form-bloque">
                <label for="">DIRECCION DE CORREO ELECTRONICO*</label>
                <input type="text" name="Correo_reg" placeholder="ingresar correo" required>
            </div>
        </div>


        <div class="form-group">
            <div class="form-bloque">
                <label for="">DIRECCION DE VIVIENDA</label>
                <input type="text" name="Direccion_reg" placeholder="ingresar direccion" required>
            </div>
        </div>

        <!-- DATOS DE USAURIO  -->

        <div class="form-title">
            <h3>datos de usuario</h3>
        </div>
        <div class="form-group">
            <div class="form-bloque">
                <label for="">NOMBRE DE USUARIO*</label>
                <input type="text" name="UsuarioName_reg" placeholder="ingresar nombre de usuario" required>
            </div>
            <div class="form-bloque">
                <label for="">CONTRASEÑA*</label>
                <input type="text" name="Password_reg" placeholder="ingresar contraseña" required>
            </div>
            <div class="form-bloque">
                <label for="">CONTRASEÑA CONFIRMACION*</label>
                <input type="text" name="PasswordConfirm_reg" placeholder="ingresar" required>
            </div>
        </div>
        <div class="form-group">

            <div class="form-bloque">
                <label for="">ASIGNAR SUCURSAL</label>
                <select class="select-style" name="Sucursal_reg">
                    <option value="En espera">en espera</option>
                    <option value="En espera">en espera</option>
                    <option value="En espera">en espera</option>
                    <option value="En espera">en espera</option>
                </select>
            </div>
            <div class="form-bloque">
                <label for="">ASIGNAR ROL</label>
                <select class="select-style" name="Rol_reg">
                    <option value="usuario">usuario</option>
                    <option value="gerente">gerente</option>
                    <option value="administrador">administrador</option>
                </select>
            </div>
        </div>
        <div class="form-buttons">
            <button class="btn-primary">Agregar</button>
        </div>
    </form>
</div>