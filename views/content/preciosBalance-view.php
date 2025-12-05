<?php
if (isset($_SESSION['id_smp']) && $_SESSION['rol_smp'] == 1) {
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/preciosAjax.php"
        data-ajax-param="preciosAjax"
        data-ajax-registros="10"
        data-ajax-action="listar_informes">
        
        <div class="title">
            <h2>
                <ion-icon name="document-text-outline"></ion-icon> Informes de Cambios de Precios
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" name="busqueda" placeholder="Buscar por medicamento...">
                    <button type="button" class="btn-search">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>

                <a href="<?php echo SERVER_URL; ?>preciosLista" class="btn default">
                    <ion-icon name="arrow-back-outline"></ion-icon> Volver a Balance
                </a>
            </div>
        </form>

        <div class="tabla-contenedor"></div>

    </div>

    <style>
        /* Estilos específicos de la vista de balance */
    </style>

<?php } else { ?>
    <div class="error" style="padding:30px;text-align:center;">
        <h3>Acceso Denegado</h3>
        <p>Solo administradores pueden ver esta sección</p>
    </div>
<?php } ?>
