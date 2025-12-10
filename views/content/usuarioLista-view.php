<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/userController.php";
    $ins_usuario = new userController();
    $datos_select = $ins_usuario->datos_extras_usuarios_controller();
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/usuariosAjax.php"
        data-ajax-param="usuariosAjax"
        data-ajax-registros="10">
        <div class="title">
            <h2>
                <ion-icon name="people-outline"></ion-icon> Gestión de Usuarios
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">

                <?php if ($_SESSION['rol_smp'] == 1) { ?>
                    <div class="form-fechas">
                        <small>Sucursales</small>
                        <select class="select-filtro" name="select1">
                            <option value="">Todas las sucursales</option>
                            <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="form-fechas">
                    <small>Roles</small>
                    <select class="select-filtro" name="select2">
                        <option value="">Todos los roles</option>
                        <option value="2">Gerente</option>
                        <option value="3">Vendedor</option>
                    </select>
                </div>

                <div class="form-fechas">
                    <small>Estado</small>
                    <select class="select-filtro" name="select3">
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>

                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por nombre, usuario, carnet...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>

            </div>

            <div class="filtro-dinamico-buttons">
                <button type="button" class="btn success" onclick="UsuariosModals.abrirModalNuevo()">
                    <ion-icon name="person-add-outline"></ion-icon> Nuevo
                </button>

            </div>
        </form>

        <div class="tabla-contenedor"></div>
    </div>

    <div class="modal" id="modalNuevoUsuario" style="display: none;">
        <div class="modal-content detalle">
            <div class="modal-header">
                <div class="modal-title"><ion-icon name="person-add-outline"></ion-icon> Nuevo Usuario</div>
                <a class="close" onclick="UsuariosModals.cerrarModalNuevo()"><ion-icon name="close-outline"></ion-icon></a>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/usuariosAjax.php" method="POST" data-form="save" autocomplete="off">
                <input type="hidden" name="usuariosAjax" value="nuevo">

                <div class="modal-group">
                    <div class="modal-title">
                        <h3><ion-icon name="person-outline"></ion-icon> Información Personal</h3>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Nombres</label>
                                <input type="text" name="Nombres_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                            </div>
                        </div>

                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Apellido Paterno</label>
                                <input type="text" name="ApellidoPaterno_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Apellido Materno</label>
                                <input type="text" name="ApellidoMaterno_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Carnet</label>
                                <input type="text" name="Carnet_reg" pattern="[0-9]{6,20}" maxlength="20" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Teléfono</label>
                                <input type="text" name="Telefono_reg" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>

                        <div class="col">
                            <div class="modal-bloque">
                                <label>Correo</label>
                                <input type="email" name="Correo_reg">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Dirección</label>
                                <input type="text" name="Direccion_reg">
                            </div>
                        </div>
                    </div>

                    <div class="modale-title">
                        <h3><ion-icon name="key-outline"></ion-icon> Credenciales de Acceso</h3>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Nombre de Usuario</label>
                                <input type="text" name="UsuarioName_reg" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$" maxlength="100" required>
                            </div>
                        </div>

                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Contraseña</label>
                                <input type="password" name="Password_reg" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Confirmar Contraseña</label>
                                <input type="password" name="PasswordConfirm_reg" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-title">
                        <h3><ion-icon name="briefcase-outline"></ion-icon> Asignación</h3>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Rol</label>
                                <select name="Rol_reg" class="select-filtro" required>
                                    <option value="">Seleccione rol...</option>
                                    <option value="2">Gerente</option>
                                    <option value="3">Vendedor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Sucursal</label>
                                <select name="Sucursal_reg" class="select-filtro" required>
                                    <option value="">Seleccione sucursal...</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="UsuariosModals.cerrarModalNuevo()">Cancelar</a>
                        <button type="submit" class="btn success">Registrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- editar usuario -->
    <div class="modal " id="modalEditarUsuario" style="display: none;">
        <div class="modal-content detalle">
            <div class="modal-header">
                <div class="modal-title"><ion-icon name="create-outline"></ion-icon> Editar Usuario</div>
                <a class="close" onclick="UsuariosModals.cerrarModalEditar()"><ion-icon name="close-outline"></ion-icon></a>
            </div>

            <form class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/usuariosAjax.php" method="POST" data-form="update" autocomplete="off">
                <input type="hidden" name="usuariosAjax" value="editar">
                <input type="hidden" name="us_id_editar" id="us_id_editar">

                <div class="modal-group">
                    <div class="modal-title">
                        <h3><ion-icon name="person-outline"></ion-icon> Información Personal</h3>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Nombres</label>
                                <input type="text" name="Nombres_edit" id="Nombres_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Apellido Paterno</label>
                                <input type="text" name="ApellidoPaterno_edit" id="ApellidoPaterno_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Apellido Materno</label>
                                <input type="text" name="ApellidoMaterno_edit" id="ApellidoMaterno_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Carnet</label>
                                <input type="text" name="Carnet_edit" id="Carnet_edit" pattern="[0-9]{6,20}" maxlength="20" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Teléfono</label>
                                <input type="text" name="Telefono_edit" id="Telefono_edit" pattern="[0-9]{6,20}" maxlength="20">
                            </div>
                        </div>

                        <div class="col">
                            <div class="modal-bloque">
                                <label>Correo</label>
                                <input type="email" name="Correo_edit" id="Correo_edit">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Dirección</label>
                                <input type="text" name="Direccion_edit" id="Direccion_edit">
                            </div>
                        </div>
                    </div>

                    <div class="modal-title">
                        <h3><ion-icon name="key-outline"></ion-icon> Credenciales de Acceso</h3>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Nombre de Usuario</label>
                                <input type="text" name="UsuarioName_edit" id="UsuarioName_edit" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$" maxlength="100" required>
                            </div>
                        </div>

                        <div class="col">
                            <div class="modal-bloque">
                                <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
                                <input type="password" name="Password_edit" id="Password_edit" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label>Confirmar Nueva Contraseña</label>
                                <input type="password" name="PasswordConfirm_edit" id="PasswordConfirm_edit" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                            </div>
                        </div>
                    </div>

                    <div class="modal-title">
                        <h3><ion-icon name="briefcase-outline"></ion-icon> Asignación</h3>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Rol</label>
                                <select name="Rol_edit" id="Rol_edit" class="select-filtro" required>
                                    <option value="">Seleccione rol...</option>
                                    <option value="2">Gerente</option>
                                    <option value="3">Vendedor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="modal-bloque">
                                <label class="required">Sucursal</label>
                                <select name="Sucursal_edit" id="Sucursal_edit" class="select-filtro" required>
                                    <option value="">Seleccione sucursal...</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-btn-content">
                        <a href="javascript:void(0)" class="btn warning" onclick="UsuariosModals.cerrarModalEditar()">Cancelar</a>
                        <button type="submit" class="btn success">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- detalle de usuario -->
    <div class="modal" id="modalDetalleUsuario" style="display: none;">
        <div class="modal-content detalle" style="max-width: 1200px;">
            <div class="modal-header">
                <div class="modal-title">
                    <ion-icon name="person-circle-outline"></ion-icon>
                    Detalle del Usuario - <span id="detalleUsuarioNombre">...</span>
                </div>
                <a class="close" onclick="UsuariosModals.cerrarModalDetalle()">
                    <ion-icon name="close-outline"></ion-icon>
                </a>
            </div>

            <input type="hidden" id="detalleUsuarioId">

            <div class="modal-group modal-estadisticas">
                <div class="modal-title">
                    <h3><ion-icon name="information-circle-outline"></ion-icon> Información Personal</h3>
                </div>

                <div class="row">

                    <div class="col">
                        <div class="modal-detalles-info">
                            <div class="detalle-info-bloque">
                                <label>Nombre Completo:</label>
                                <p id="detalleNombreCompletoUsuario">-</p>
                            </div>
                            <div class="detalle-info-bloque">

                                <label>Usuario:</label>
                                <p id="detalleUsername">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Carnet:</label>
                                <p id="detalleCarnetUsuario">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Teléfono:</label>
                                <p id="detalleTelefonoUsuario">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Correo:</label>
                                <p id="detalleCorreoUsuario">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Dirección:</label>
                                <p id="detalleDireccionUsuario">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Rol:</label>
                                <p id="detalleRolUsuario">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Sucursal:</label>
                                <p id="detalleSucursalUsuario">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Fecha de Registro:</label>
                                <p id="detalleFechaRegistroUsuario">-</p>
                            </div>

                            <div class="detalle-info-bloque">
                                <label>Estado:</label>
                                <p id="detalleEstadoUsuario">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <!-- grfico -->
                        <div class="row">
                            <h3><ion-icon name="stats-chart-outline"></ion-icon> Estadísticas de Actividad</h3>

                        </div>


                        <div class="row">
                            <div id="graficoVentasUsuario" style="width:100%;height:350px;background:#f9f9f9;border-radius:8px;"></div>
                        </div>
                        <!-- totales -->
                        <div class="modal-totales">
                            <div class="col">
                                <div style="background:#e3f2fd;padding:15px;border-radius:8px;text-align:center;">
                                    <label style="color:#1565c0;">Total Ventas:</label>
                                    <p style="font-size:24px;font-weight:bold;color:#0d47a1;margin:5px 0;" id="detalleTotalVentas">0</p>
                                </div>
                            </div>
                            <div class="col">
                                <div style="background:#e8f5e9;padding:15px;border-radius:8px;text-align:center;">
                                    <label style="color:#2e7d32;">Monto Total:</label>
                                    <p style="font-size:24px;font-weight:bold;color:#1b5e20;margin:5px 0;" id="detalleMontoTotalUsuario">Bs. 0.00</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-title">
                    <h3><ion-icon name="receipt-outline"></ion-icon> Últimas 10 Ventas</h3>
                </div>
                <!-- tabla -->
                <div class="row">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>N° Documento</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody id="tablaUltimasVentasUsuario">
                                <tr>
                                    <td colspan="6" style="text-align:center;">
                                        <ion-icon name="hourglass-outline"></ion-icon> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-btn-content">
                    <a href="javascript:void(0)" class="btn warning" onclick="UsuariosModals.cerrarModalDetalle()">
                        Cerrar
                    </a>
                    <a href="javascript:void(0)" class="btn danger" id="btnToggleEstadoDetalleUsuario">
                        <ion-icon name="power-outline"></ion-icon> Estado
                    </a>
                    <a href="javascript:void(0)" class="btn primary" onclick="UsuariosModals.editarDesdeDetalle()">
                        <ion-icon name="create-outline"></ion-icon> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const UsuariosModals = (function() {
            'use strict';

            const API_URL = '<?php echo SERVER_URL; ?>ajax/usuariosAjax.php';

            function abrirModalNuevo() {
                const modal = document.getElementById('modalNuevoUsuario');
                if (modal) {
                    modal.style.display = 'flex';
                }
            }

            function cerrarModalNuevo() {
                const modal = document.getElementById('modalNuevoUsuario');
                if (modal) {
                    modal.style.display = 'none';
                    const form = modal.querySelector('form');
                    if (form) form.reset();
                }
            }

            async function abrirModalEditar(us_id) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            usuariosAjax: 'datos_usuario',
                            us_id: us_id
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    document.getElementById('us_id_editar').value = data.us_id;
                    document.getElementById('Nombres_edit').value = data.us_nombres || '';
                    document.getElementById('ApellidoPaterno_edit').value = data.us_apellido_paterno || '';
                    document.getElementById('ApellidoMaterno_edit').value = data.us_apellido_materno || '';
                    document.getElementById('Carnet_edit').value = data.us_numero_carnet || '';
                    document.getElementById('Telefono_edit').value = data.us_telefono || '';
                    document.getElementById('Correo_edit').value = data.us_correo || '';
                    document.getElementById('Direccion_edit').value = data.us_direccion || '';
                    document.getElementById('UsuarioName_edit').value = data.us_username || '';
                    document.getElementById('Rol_edit').value = data.ro_id || '';
                    document.getElementById('Sucursal_edit').value = data.su_id || '';

                    document.getElementById('Password_edit').value = '';
                    document.getElementById('PasswordConfirm_edit').value = '';

                    const modal = document.getElementById('modalEditarUsuario');
                    if (modal) modal.style.display = 'flex';

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cargar los datos del usuario', 'error');
                }
            }

            function cerrarModalEditar() {
                const modal = document.getElementById('modalEditarUsuario');
                if (modal) {
                    modal.style.display = 'none';
                }
            }

            async function toggleEstado(us_id, estado) {
                const texto = estado == 1 ? 'desactivar' : 'activar';

                const result = await Swal.fire({
                    title: '¿Está seguro?',
                    text: '¿Desea ' + texto + ' este usuario?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ' + texto,
                    cancelButtonText: 'Cancelar'
                });

                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            usuariosAjax: 'toggle_estado',
                            us_id: us_id,
                            estado: estado
                        })
                    });

                    const data = await response.json();
                    Swal.close();

                    await Swal.fire({
                        title: data.Titulo || 'Resultado',
                        html: data.texto || '',
                        icon: data.Tipo || 'info'
                    });

                    if (data.Alerta === 'recargar' || data.Tipo === 'success') {
                        document.querySelector('.filtro-dinamico .btn-search')?.click();
                    }

                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
                }
            }

            async function verDetalle(us_id) {
                document.getElementById('detalleUsuarioId').value = us_id;

                const modal = document.getElementById('modalDetalleUsuario');
                if (modal) modal.style.display = 'flex';

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            usuariosAjax: 'detalle_completo',
                            us_id: us_id
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        Swal.fire('Error', data.error, 'error');
                        return;
                    }

                    const nombreCompleto = `${data.us_nombres || ''} ${data.us_apellido_paterno || ''} ${data.us_apellido_materno || ''}`.trim();
                    document.getElementById('detalleUsuarioNombre').textContent = nombreCompleto;
                    document.getElementById('detalleNombreCompletoUsuario').textContent = nombreCompleto;
                    document.getElementById('detalleUsername').textContent = data.us_username || '-';
                    document.getElementById('detalleCarnetUsuario').textContent = data.us_numero_carnet || '-';
                    document.getElementById('detalleTelefonoUsuario').textContent = data.us_telefono || '-';
                    document.getElementById('detalleCorreoUsuario').textContent = data.us_correo || '-';
                    document.getElementById('detalleDireccionUsuario').textContent = data.us_direccion || '-';
                    document.getElementById('detalleRolUsuario').textContent = data.rol_nombre || '-';
                    document.getElementById('detalleSucursalUsuario').textContent = data.sucursal_nombre || '-';
                    document.getElementById('detalleFechaRegistroUsuario').textContent = formatearFecha(data.us_creado_en);

                    const estadoHtml = data.us_estado == 1 ?
                        '<span class="estado-badge activo"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>' :
                        '<span class="estado-badge caducado"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';
                    document.getElementById('detalleEstadoUsuario').innerHTML = estadoHtml;

                    document.getElementById('detalleTotalVentas').textContent = data.total_ventas || 0;
                    document.getElementById('detalleMontoTotalUsuario').textContent = 'Bs. ' + formatearNumero(data.monto_total || 0);

                    const btnToggle = document.getElementById('btnToggleEstadoDetalleUsuario');
                    if (btnToggle) {
                        btnToggle.onclick = function() {
                            toggleEstado(us_id, data.us_estado);
                        };
                    }

                    // Cargar tabla de ventas
                    cargarUltimasVentasUsuario(us_id);

                    // NUEVO: Cargar gráfico de ventas mensuales
                    cargarGraficoVentasMensuales(us_id);

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cargar el detalle del usuario', 'error');
                }
            }
            async function cargarGraficoVentasMensuales(us_id) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            usuariosAjax: 'ventas_mensuales',
                            us_id: us_id
                        })
                    });

                    const data = await response.json();

                    if (data.error) {
                        console.error('Error cargando ventas mensuales:', data.error);
                        return;
                    }

                    // Inicializar gráfico ECharts
                    const chartDom = document.getElementById('graficoVentasUsuario');
                    const myChart = echarts.init(chartDom);

                    const meses = data.ventas_mensuales.map(item => item.mes);
                    const cantidades = data.ventas_mensuales.map(item => parseInt(item.cantidad));
                    const montos = data.ventas_mensuales.map(item => parseFloat(item.monto));

                    const option = {
                        title: {
                            text: 'Rendimiento de Ventas',
                            left: 'center',
                            textStyle: {
                                fontSize: 16,
                                fontWeight: 'bold',
                                color: '#333',
                            },
                            top: 10
                        },
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            }
                        },
                        legend: {
                            data: ['Cantidad', 'Monto Bs.'],
                            top: 40,
                            textStyle: {
                                fontSize: 12
                            }
                        },
                        grid: {
                            left: '15%',
                            right: '15%',
                            top: '25%',
                            bottom: '25%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            data: meses,
                            axisLabel: {
                                rotate: 0,
                                fontSize: 11,
                                interval: 0
                            }
                        },
                        yAxis: [{
                                type: 'value',
                                name: 'Cantidad',
                                position: 'left',
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            },
                            {
                                type: 'value',
                                name: 'Bs.',
                                position: 'right',
                                axisLabel: {
                                    formatter: '{value}'
                                }
                            }
                        ],
                        series: [{
                                name: 'Cantidad',
                                type: 'bar',
                                data: cantidades,
                                itemStyle: {
                                    color: '#1976D2'
                                },
                                label: {
                                    show: false
                                }
                            },
                            {
                                name: 'Monto Bs.',
                                type: 'line',
                                yAxisIndex: 1,
                                data: montos,
                                itemStyle: {
                                    color: '#4CAF50'
                                },
                                lineStyle: {
                                    width: 2
                                },
                                label: {
                                    show: false
                                }
                            }
                        ]
                    };

                    myChart.setOption(option);

                    // Responsive simple
                    window.addEventListener('resize', function() {
                        myChart.resize();
                    });

                } catch (error) {
                    console.error('Error en gráfico:', error);
                }
            }

            async function cargarUltimasVentasUsuario(us_id) {
                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            usuariosAjax: 'ultimas_ventas',
                            us_id: us_id
                        })
                    });

                    const data = await response.json();
                    const tbody = document.getElementById('tablaUltimasVentasUsuario');

                    if (data.ventas && data.ventas.length > 0) {
                        tbody.innerHTML = data.ventas.map(venta => {
                            const cliente = venta.cliente_nombre || 'Sin cliente';
                            return `
                        <tr>
                            <td><strong>${venta.ve_numero_documento}</strong></td>
                            <td>${formatearFecha(venta.ve_fecha_emision)}</td>
                            <td style="font-size:11px;">${cliente}</td>
                            <td style="text-align:center;">${venta.total_items || 0}</td>
                            <td style="text-align:right;"><strong style="color:#1976D2;">Bs. ${formatearNumero(venta.ve_total)}</strong></td>
                            <td style="font-size:11px;">${venta.ve_tipo_documento || 'nota de venta'}</td>
                        </tr>
                    `
                        }).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;"><ion-icon name="cart-outline"></ion-icon> Sin ventas registradas</td></tr>';
                    }

                } catch (error) {
                    console.error('Error:', error);
                }
            }

            function cerrarModalDetalle() {
                const modal = document.getElementById('modalDetalleUsuario');
                if (modal) modal.style.display = 'none';
            }

            function editarDesdeDetalle() {
                const us_id = document.getElementById('detalleUsuarioId').value;
                cerrarModalDetalle();
                abrirModalEditar(us_id);
            }

            function formatearFecha(fecha) {
                if (!fecha) return '-';
                const d = new Date(fecha);
                const dia = String(d.getDate()).padStart(2, '0');
                const mes = String(d.getMonth() + 1).padStart(2, '0');
                const anio = d.getFullYear();
                return `${dia}/${mes}/${anio}`;
            }

            function formatearNumero(num) {
                return parseFloat(num || 0).toFixed(2);
            }

            document.addEventListener('click', function(e) {
                const modals = ['modalNuevoUsuario', 'modalEditarUsuario', 'modalDetalleUsuario'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && modal.style.display === 'flex' && e.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            return {
                abrirModalNuevo,
                cerrarModalNuevo,
                abrirModalEditar,
                cerrarModalEditar,
                toggleEstado,
                verDetalle,
                cerrarModalDetalle,
                editarDesdeDetalle
            };
        })();
    </script>

<?php } else { ?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php } ?>