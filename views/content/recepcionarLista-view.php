<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] == 1 || $_SESSION['rol_smp'] == 2)) {
?>

    <div class="">
        <div class="ph">
            <div>
                <div class="ptit">
                    <ion-icon name="cloud-download-outline"></ion-icon> Recepcionar Transferencias
                </div>
                <div class="psub">Administre las transferencias pendientes de recepción en su sucursal</div>
            </div>
        </div>


            <div class="cb">

            </div>
        </div>

        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Transferencias Pendientes</div>
            </div>
            <div class="cb">
                <div id="resultado-busqueda-recepcionar">
                    <p class="txctr tmut" style="padding: 20px;">
                        <ion-icon name="search-outline" style="font-size: 48px;"></ion-icon><br>
                        Use el buscador para encontrar transferencias pendientes
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="mov" id="modal-detalles-transfer-recepcionar">
        <div class="modal mlg">
            <div class="mh">
                <div>
                    <div class="mt">
                        <ion-icon name="document-text-outline"></ion-icon>
                        Detalles de Transferencia
                    </div>
                    <div class="ms">Revise los detalles antes de aceptar o rechazar</div>
                </div>
                <button class="mcl" onclick="RecepcionManager.cerrarModalDetalles()">
                    <ion-icon name="close-outline"></ion-icon>
                </button>
            </div>

            <div class="mb">
                <div class="fr mb16">
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="document-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Número Transferencia</div><div class="th5" id="modal-numero-transfer-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="radio-button-on-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Estado</div><div class="th5" id="modal-estado-transfer-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="storefront-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Sucursal Origen</div><div class="th5" id="modal-sucursal-origen-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="location-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Sucursal Destino</div><div class="th5" id="modal-sucursal-destino-recepcionar"></div></div></div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="cb">
                            <div class="litem"><ion-icon name="person-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Usuario Emisor</div><div class="th5" id="modal-usuario-emisor-recepcionar"></div></div></div>
                            <div class="litem"><ion-icon name="calendar-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Fecha Envío</div><div class="th5" id="modal-fecha-envio-recepcionar"></div></div></div>
                            <div class="litem" style="border:none"><ion-icon name="chatbox-outline" style="font-size:18px;color:var(--accent-primary)"></ion-icon><div class="f1"><div class="tc">Observaciones</div><div class="th5" id="modal-observaciones-recepcionar" style="font-style: italic;"></div></div></div>
                        </div>
                    </div>
                </div>

                <div class="stit">Items en Transferencia</div>
                <div class="card mb16">
                    <div class="cb">
                        <div id="modal-detalles-items-recepcionar" style="max-height: 400px; overflow-y: auto;"></div>
                    </div>
                </div>

                <div class="stit">Resumen</div>
                <div class="grid4 mb16">
                    <div class="statc">
                        <div class="siw bl"><ion-icon name="cube-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-items-recepcionar">0</div>
                            <div class="sl">Total Items</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw gr"><ion-icon name="archive-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-cajas-recepcionar">0</div>
                            <div class="sl">Total Cajas</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw ww"><ion-icon name="medical-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-unidades-recepcionar">0</div>
                            <div class="sl">Total Unidades</div>
                        </div>
                    </div>
                    <div class="statc">
                        <div class="siw rd"><ion-icon name="cash-outline"></ion-icon></div>
                        <div>
                            <div class="sv" id="modal-total-valorado-recepcionar">Bs. 0.00</div>
                            <div class="sl">Valor Total</div>
                        </div>
                    </div>
                </div>

                <div id="modal-rechazo-container" style="display: none;">
                    <div class="alert alwar mb16">
                        <ion-icon name="warning-outline"></ion-icon>
                        <div>
                            <div class="altt">Modo Rechazo</div>
                            <div class="altx">Ingrese el motivo del rechazo.</div>
                        </div>
                    </div>
                    <div class="fg mb16">
                        <label class="fl req">Motivo de Rechazo</label>
                        <textarea class="ta" id="modal-motivo-rechazo" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="RecepcionManager.cerrarModalDetalles()">Cancelar</button>
                <button class="btn btn-dan" id="btn-rechazar-transfer-modal" onclick="RecepcionManager.mostrarRechazo()">
                    <ion-icon name="close-circle-outline"></ion-icon> Rechazar
                </button>
                <button class="btn btn-def" id="btn-aceptar-transfer-modal" onclick="RecepcionManager.aceptarTransferencia()">
                    <ion-icon name="checkmark-circle-outline"></ion-icon> Aceptar
                </button>
            </div>
        </div>
    </div>

    <script>
        window.SU_ACTUAL_RECEPCION = <?php echo (int)($_SESSION['sucursal_smp'] ?? 0); ?>;
        window.ROL_USUARIO_RECEPCION = <?php echo (int)($_SESSION['rol_smp'] ?? 0); ?>;
    </script>
    <script src="<?php echo SERVER_URL; ?>views/script/recepcionarLista-view.js"></script>

<?php
} else {
?>
    <div class="pg">
        <div class="ph">
            <div>
                <div class="ptit">Acceso Denegado</div>
                <div class="psub">No tiene permisos para acceder a esta sección</div>
            </div>
        </div>
        <div class="card">
            <div class="cb txctr" style="padding:60px">
                <ion-icon name="lock-closed-outline" style="font-size:48px;color:var(--text-faint);margin-bottom:16px"></ion-icon>
                <div class="th3 mb8">Acceso Denegado</div>
                <div class="tbs tmut">No tiene permisos para acceder a esta sección.</div>
            </div>
        </div>
    </div>
<?php
}
