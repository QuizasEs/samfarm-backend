<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
    require_once "./controllers/userController.php";
    $ins_usuario = new userController();
    $datos_select = $ins_usuario->datos_extras_usuarios_controller();
?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/usuariosAjax.php"
        data-ajax-param="usuariosAjax"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="people-outline"></ion-icon> Gestión de Usuarios</div>
                <div class="psub">Administre y consulte la información detallada de sus usuarios</div>
            </div>
            <div class="tbr">
                <button type="button" class="btn btn-def" onclick="UsuariosModals.abrirModalNuevo()">
                    <ion-icon name="person-add-outline"></ion-icon> Nuevo Usuario
                </button>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr3">
                        <?php if ($_SESSION['rol_smp'] == 1) { ?>
                            <div class="fg">
                                <label class="fl">Sucursales</label>
                                <select class="sel select-filtro" name="select1">
                                    <option value="">Todas las sucursales</option>
                                    <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                        <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>

                        <div class="fg">
                            <label class="fl">Roles</label>
                            <select class="sel select-filtro" name="select2">
                                <option value="">Todos los roles</option>
                                <option value="2">Gerente</option>
                                <option value="3">Vendedor</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select3">
                                <option value="">Todos</option>
                                <option value="activo">Activos</option>
                                <option value="inactivo">Inactivos</option>
                            </select>
                        </div>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre, usuario, carnet...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="cb">
                <div class="tw">
                    <div class="tabla-contenedor"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mov" id="modalNuevoUsuario" style="display: none;">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="person-add-outline"></ion-icon> Nuevo Usuario</div>
                    <div class="ms">Registre un nuevo usuario en el sistema</div>
                </div>
                <button class="mcl" onclick="UsuariosModals.cerrarModalNuevo()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <form id="formNuevoUsuario" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/usuariosAjax.php" method="POST" data-form="save" autocomplete="off">
                    <input type="hidden" name="usuariosAjax" value="nuevo">

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="person-outline"></ion-icon> Información Personal</h3>
                    </div>

                    <div class="fr3">
                        <div class="fg">
                            <label class="fl required">Nombres</label>
                            <input class="inp" type="text" name="Nombres_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ1-9 ]{3,100}" maxlength="100" required>
                        </div>

                        <div class="fg">
                            <label class="fl required">Apellido Paterno</label>
                            <input class="inp" type="text" name="ApellidoPaterno_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                        </div>

                        <div class="fg">
                            <label class="fl required">Apellido Materno</label>
                            <input class="inp" type="text" name="ApellidoMaterno_reg" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                        </div>
                    </div>

                    <div class="fr3">
                        <div class="fg">
                            <label class="fl required">Carnet</label>
                            <input class="inp" type="text" name="Carnet_reg" pattern="[0-9]{6,20}" maxlength="20" required>
                        </div>

                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_reg" pattern="[0-9]{6,20}" maxlength="20">
                        </div>

                        <div class="fg">
                            <label class="fl">Correo</label>
                            <input class="inp" type="email" name="Correo_reg">
                        </div>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Dirección</label>
                            <input class="inp" type="text" name="Direccion_reg">
                        </div>
                    </div>

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="key-outline"></ion-icon> Credenciales de Acceso</h3>
                    </div>

                    <div class="fr3">
                        <div class="fg">
                            <label class="fl required">Nombre de Usuario</label>
                            <input class="inp" type="text" name="UsuarioName_reg" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$" maxlength="100" required>
                        </div>

                        <div class="fg">
                            <label class="fl required">Contraseña</label>
                            <input class="inp" type="password" name="Password_reg" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100" required>
                        </div>

                        <div class="fg">
                            <label class="fl required">Confirmar Contraseña</label>
                            <input class="inp" type="password" name="PasswordConfirm_reg" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100" required>
                        </div>
                    </div>

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="briefcase-outline"></ion-icon> Asignación</h3>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl required">Rol</label>
                            <select class="sel" name="Rol_reg" required>
                                <option value="">Seleccione rol...</option>
                                <option value="2">Gerente</option>
                                <option value="3">Vendedor</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl required">Sucursal</label>
                            <select class="sel" name="Sucursal_reg" required>
                                <option value="">Seleccione sucursal...</option>
                                <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                    <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="UsuariosModals.cerrarModalNuevo()">Cancelar</button>
                <button type="submit" form="formNuevoUsuario" class="btn btn-def">Registrar</button>
            </div>
        </div>
    </div>
    <!-- editar usuario -->
    <div class="mov" id="modalEditarUsuario" style="display: none;">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="create-outline"></ion-icon> Editar Usuario</div>
                    <div class="ms">Modifique la información del usuario</div>
                </div>
                <button class="mcl" onclick="UsuariosModals.cerrarModalEditar()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <form id="formEditarUsuario" class="FormularioAjax" action="<?php echo SERVER_URL; ?>ajax/usuariosAjax.php" method="POST" data-form="update" autocomplete="off">
                    <input type="hidden" name="usuariosAjax" value="editar">
                    <input type="hidden" name="us_id_editar" id="us_id_editar">

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="person-outline"></ion-icon> Información Personal</h3>
                    </div>

                    <div class="fr3">
                        <div class="fg">
                            <label class="fl required">Nombres</label>
                            <input class="inp" type="text" name="Nombres_edit" id="Nombres_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,100}" maxlength="100" required>
                        </div>

                        <div class="fg">
                            <label class="fl required">Apellido Paterno</label>
                            <input class="inp" type="text" name="ApellidoPaterno_edit" id="ApellidoPaterno_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                        </div>

                        <div class="fg">
                            <label class="fl required">Apellido Materno</label>
                            <input class="inp" type="text" name="ApellidoMaterno_edit" id="ApellidoMaterno_edit" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ]{3,100}" maxlength="100" required>
                        </div>
                    </div>

                    <div class="fr3">
                        <div class="fg">
                            <label class="fl required">Carnet</label>
                            <input class="inp" type="text" name="Carnet_edit" id="Carnet_edit" pattern="[0-9]{6,20}" maxlength="20" required>
                        </div>

                        <div class="fg">
                            <label class="fl">Teléfono</label>
                            <input class="inp" type="text" name="Telefono_edit" id="Telefono_edit" pattern="[0-9]{6,20}" maxlength="20">
                        </div>

                        <div class="fg">
                            <label class="fl">Correo</label>
                            <input class="inp" type="email" name="Correo_edit" id="Correo_edit">
                        </div>
                    </div>

                    <div class="fr1">
                        <div class="fg">
                            <label class="fl">Dirección</label>
                            <input class="inp" type="text" name="Direccion_edit" id="Direccion_edit">
                        </div>
                    </div>

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="key-outline"></ion-icon> Credenciales de Acceso</h3>
                    </div>

                    <div class="fr3">
                        <div class="fg">
                            <label class="fl required">Nombre de Usuario</label>
                            <input class="inp" type="text" name="UsuarioName_edit" id="UsuarioName_edit" pattern="^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_]{3,100}$" maxlength="100" required>
                        </div>

                        <div class="fg">
                            <label class="fl">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                            <input class="inp" type="password" name="Password_edit" id="Password_edit" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                        </div>

                        <div class="fg">
                            <label class="fl">Confirmar Nueva Contraseña</label>
                            <input class="inp" type="password" name="PasswordConfirm_edit" id="PasswordConfirm_edit" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ0-9@$!%*?&._#]{3,100}" maxlength="100">
                        </div>
                    </div>

                    <div class="tb">
                        <h3 class="th3"><ion-icon name="briefcase-outline"></ion-icon> Asignación</h3>
                    </div>

                    <div class="fr">
                        <div class="fg">
                            <label class="fl required">Rol</label>
                            <select class="sel" name="Rol_edit" id="Rol_edit" required>
                                <option value="">Seleccione rol...</option>
                                <option value="2">Gerente</option>
                                <option value="3">Vendedor</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl required">Sucursal</label>
                            <select class="sel" name="Sucursal_edit" id="Sucursal_edit" required>
                                <option value="">Seleccione sucursal...</option>
                                <?php foreach ($datos_select['sucursales'] as $sucursal) { ?>
                                    <option value="<?php echo $sucursal['su_id'] ?>"><?php echo $sucursal['su_nombre'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="UsuariosModals.cerrarModalEditar()">Cancelar</button>
                <button type="submit" form="formEditarUsuario" class="btn btn-def">Guardar</button>
            </div>
        </div>
    </div>
    <!-- detalle de usuario -->
    <div class="mov" id="modalDetalleUsuario" style="display: none;">
        <div class="modal" style="max-width: 1200px;">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="person-circle-outline"></ion-icon> Detalle del Usuario - <span id="detalleUsuarioNombre">...</span></div>
                    <div class="ms">Información completa y estadísticas del usuario</div>
                </div>
                <button class="mcl" onclick="UsuariosModals.cerrarModalDetalle()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <input type="hidden" id="detalleUsuarioId">

                <div class="tb">
                    <h3 class="th3"><ion-icon name="information-circle-outline"></ion-icon> Información Personal</h3>
                </div>

                <div class="fr2">
                    <div class="fg">
                        <label class="fl">Nombre Completo:</label>
                        <p class="tbs" id="detalleNombreCompletoUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Usuario:</label>
                        <p class="tbs" id="detalleUsername">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Carnet:</label>
                        <p class="tbs" id="detalleCarnetUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Teléfono:</label>
                        <p class="tbs" id="detalleTelefonoUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Correo:</label>
                        <p class="tbs" id="detalleCorreoUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Dirección:</label>
                        <p class="tbs" id="detalleDireccionUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Rol:</label>
                        <p class="tbs" id="detalleRolUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Sucursal:</label>
                        <p class="tbs" id="detalleSucursalUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Fecha de Registro:</label>
                        <p class="tbs" id="detalleFechaRegistroUsuario">-</p>
                    </div>

                    <div class="fg">
                        <label class="fl">Estado:</label>
                        <p class="tbs" id="detalleEstadoUsuario">-</p>
                    </div>
                </div>

                <div class="tb">
                    <h3 class="th3"><ion-icon name="stats-chart-outline"></ion-icon> Estadísticas de Actividad</h3>
                </div>

                <div class="fr2">
                    <div class="fg">
                        <div id="graficoVentasUsuario" style="width:100%;height:350px;background:#f9f9f9;border-radius:8px;"></div>
                    </div>

                    <div class="fg">
                        <div class="statc">
                            <div class="stat">
                                <label>Total Ventas:</label>
                                <p id="detalleTotalVentas">0</p>
                            </div>
                            <div class="stat">
                                <label>Monto Total:</label>
                                <p id="detalleMontoTotalUsuario">Bs. 0.00</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tb">
                    <h3 class="th3"><ion-icon name="receipt-outline"></ion-icon> Últimas 10 Ventas</h3>
                </div>

                <div class="tw">
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
            <div class="mf">
                <button class="btn btn-war" onclick="UsuariosModals.cerrarModalDetalle()">Cerrar</button>
                <button class="btn btn-danger" id="btnToggleEstadoDetalleUsuario"><ion-icon name="power-outline"></ion-icon> Estado</button>
                <button class="btn btn-def" onclick="UsuariosModals.editarDesdeDetalle()"><ion-icon name="create-outline"></ion-icon> Editar</button>
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
                    modal.classList.add('open');
                }
            }

            function cerrarModalNuevo() {
                const modal = document.getElementById('modalNuevoUsuario');
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
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
                    if (modal) {
                        modal.style.display = 'flex';
                        modal.classList.add('open');
                    }

                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'No se pudo cargar los datos del usuario', 'error');
                }
            }

            function cerrarModalEditar() {
                const modal = document.getElementById('modalEditarUsuario');
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
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
                if (modal) {
                    modal.style.display = 'flex';
                    modal.classList.add('open');
                }

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
                        '<span class="badge bgr"><ion-icon name="checkmark-circle-outline"></ion-icon> Activo</span>' :
                        '<span class="badge bgry"><ion-icon name="close-circle-outline"></ion-icon> Inactivo</span>';
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
                if (modal) {
                    modal.classList.remove('open');
                    setTimeout(() => modal.style.display = 'none', 300);
                }
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
<?php } ?>
