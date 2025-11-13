<div class="container tabla-dinamica" 
     data-ajax-table="true"
     data-ajax-url="ajax/loteAjax.php"
     data-ajax-param="loteAjax"
     data-ajax-registros="10">

    <form class="filtro-dinamico">
        <select name="select1">
            <option value="">Todos</option>
            <option value="activo">Activo</option>
            <option value="caducado">Caducado</option>
        </select>

        <input type="text" name="busqueda" id="filtroBusqueda" placeholder="Buscar por nombre o lote...">
        <button type="button" class="btn-search"><ion-icon name="search"></ion-icon></button>
    </form>

    <div class="tabla-contenedor"></div>
</div>
