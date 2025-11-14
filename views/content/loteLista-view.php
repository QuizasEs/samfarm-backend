<div class="container tabla-dinamica"
    data-ajax-table="true"
    data-ajax-url="ajax/loteAjax.php"
    data-ajax-param="loteAjax"
    data-ajax-registros="10">

    <form class="filtro-dinamico">
        <div class="search">

            <select class="select-filtro" name="select1">
                <option value="">Todos</option>
                <option value="en_espera">en_espera</option>
                <option value="activo">Activo</option>
                <option value="terminado">terminado</option>
                <option value="caducado">Caducado</option>
            </select>
            <select class="select-filtro" name="select2" id="">
                <option value="">Mes</option>
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiempre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>




            <input type="text" class="" name="busqueda" id="filtroBusqueda" placeholder="Buscar por nombre o lote...">
            <button type="button" class="btn-search"><ion-icon name="search"></ion-icon></button>

        </div>
    </form>

    <div class="tabla-contenedor"></div>
</div>


<!-- modal -->
<!-- Modal Activar Lote -->
<div id="modalActivarLote" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">📦<span> Activar lote</span></div>
            <a class="close" onclick="cerrarModal()">×</a>
        </div>
        <div class="modal-group">
            <h3>Activar lote</h3>

            <div class="row">
                <div id="detalleLote"></div>

            </div>
            <div class="row">
                <div class="modal-info">

                    <p class="info">
                        <strong>Atención:</strong> La activación del lote solo puede hacerse una vez.
                        Luego la edición será limitada.
                    </p>
                </div>
            </div>
            <div class="row">
                <button id="btnConfirmarActivacion" class="btn success">Activar</button>
                <button class="modal-close btn warning">Cancelar</button>
            </div>
        </div>
    </div>
</div>