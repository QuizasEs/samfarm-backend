<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/cajaAjax.php"
        data-ajax-param="cajaAjax"
        data-ajax-action="listar"
        data-ajax-registros="10">

        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="cash-outline"></ion-icon> Gestión de Cajas</div>
                <div class="psub">Administre y supervise las cajas del sistema</div>
            </div>
        </div>

        <div class="card mb16">
            <div class="ch">
                <div class="ct"><ion-icon name="filter-outline"></ion-icon> Filtros de Búsqueda</div>
            </div>
            <div class="cb">
                <form class="filtro-dinamico">
                    <div class="fr">
                        <div class="fg">
                            <label class="fl">Estado</label>
                            <select class="sel select-filtro" name="select1">
                                <option value="abierta">Abiertas</option>
                                <option value="cerrada">Cerradas</option>
                                <option value="">Todas</option>
                            </select>
                        </div>

                        <div class="fg">
                            <label class="fl">Búsqueda rápida</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="busqueda" placeholder="Buscar por nombre o usuario...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb16">
            <div class="cb">
                <div class="tw">
                    <div class="tabla-contenedor"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="bar-chart-outline"></ion-icon> Total de Ventas por Usuario</div>
            </div>
            <div class="cb">
                <div id="graficoVentasUsuario"></div>
            </div>
        </div>
    </div>

    <div class="mov" id="modalCerrarCaja" style="display: none;">
        <div class="modal">
            <div class="mh">
                <div>
                    <div class="mt"><ion-icon name="checkmark-circle-outline"></ion-icon> Cerrar Caja</div>
                    <div class="ms">Confirme el cierre de la caja con los datos finales</div>
                </div>
                <button class="mcl" onclick="CajaGestion.cerrarModalCerrar()"><ion-icon name="close-outline"></ion-icon></button>
            </div>
            <div class="mb">
                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Nombre de Caja:</label>
                        <p class="tbs" id="modalNombreCaja">-</p>
                    </div>
                </div>

                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Usuario Responsable:</label>
                        <p class="tbs" id="modalUsuarioCaja">-</p>
                    </div>
                </div>

                <div class="fr2">
                    <div class="fg">
                        <label class="fl">Saldo Inicial (Bs):</label>
                        <p class="tbs" id="modalSaldoInicial">-</p>
                    </div>
                    <div class="fg">
                        <label class="fl">Total Ingresos (Bs):</label>
                        <p class="tbs" id="modalTotalIngresos">-</p>
                    </div>
                </div>

                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Saldo Final Teórico (Bs):</label>
                        <p class="tbs" id="modalSaldoFinal">-</p>
                    </div>
                </div>

                <div class="fr1">
                    <div class="fg">
                        <label class="fl">Observaciones:</label>
                        <textarea id="modalObservacion" class="inp" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button class="btn btn-war" onclick="CajaGestion.cerrarModalCerrar()">Cancelar</button>
                <button class="btn btn-dan" onclick="CajaGestion.confirmarCierre()"><ion-icon name="checkmark-circle-outline"></ion-icon> Confirmar Cierre</button>
            </div>
        </div>
    </div>

    <style>
        #graficoVentasUsuario {
            width: 100%;
            height: 400px;
            min-width: 300px;
            max-width: 100%;
        }
    </style>

    <script src="<?php echo SERVER_URL; ?>views/script/cajaLista-view.js"></script>

<?php } else { ?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit"><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</div>
                <div class="psub">Solo administradores pueden acceder a esta sección</div>
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