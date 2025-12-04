<?php
if (isset($_SESSION['id_smp']) && $_SESSION['rol_smp'] == 1) {
?>

    <div class="container tabla-dinamica"
        data-ajax-table="true"
        data-ajax-url="ajax/preciosAjax.php"
        data-ajax-param="preciosAjax"
        data-ajax-registros="10"
        data-ajax-consulta="listar_informes">
        
        <div class="title">
            <h2>
                <ion-icon name="document-text-outline"></ion-icon> Informes de Cambios de Precios
            </h2>
        </div>

        <form class="filtro-dinamico">
            <div class="filtro-dinamico-search">
                <div class="search">
                    <input type="text" id="filtroMedicamento" placeholder="Buscar por medicamento...">
                    <button type="button" class="btn" onclick="aplicarFiltros()">
                        <ion-icon name="search"></ion-icon>
                    </button>
                </div>
                
                <select id="filtroTipoCambio" onchange="aplicarFiltros()">
                    <option value="">Tipo de cambio - Todos</option>
                    <option value="lote_individual">Lote Individual</option>
                    <option value="todos_lotes">Todos los Lotes</option>
                </select>

                <a href="<?php echo SERVER_URL; ?>precio-lista" class="btn info">
                    <ion-icon name="arrow-back-outline"></ion-icon> Volver a Balance
                </a>
            </div>
        </form>

        <div class="tabla-contenedor"></div>

    </div>

    <style>
        .filtro-dinamico-search {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filtro-dinamico-search select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .tabla-dinamica-lista {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .tabla-dinamica-lista thead {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            color: white;
        }

        .tabla-dinamica-lista th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border: none;
        }

        .tabla-dinamica-lista tbody tr {
            border-bottom: 1px solid #eee;
            transition: background 0.2s ease;
        }

        .tabla-dinamica-lista tbody tr:hover {
            background: #f8f9fa;
        }

        .tabla-dinamica-lista td {
            padding: 12px;
            text-align: left;
        }

        .paginador {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-paginador {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .btn-paginador:hover {
            background: #f0f0f0;
            border-color: #999;
        }

        .btn-paginador.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .badge-individual {
            background: #3498db;
        }

        .badge-todos {
            background: #27ae60;
        }
    </style>

    <script>
        function aplicarFiltros() {
            const tipoCambio = document.getElementById('filtroTipoCambio').value;
            const medicamento = document.getElementById('filtroMedicamento').value;
            
            // Reiniciar a página 1 cuando se aplican filtros
            if (window.tabla_dinamica_actual) {
                window.tabla_dinamica_actual.dataset.ajaxPagina = '1';
                cargarTablaDinamica(window.tabla_dinamica_actual);
            }
        }
    </script>

<?php } else { ?>
    <div class="error" style="padding:30px;text-align:center;">
        <h3>Acceso Denegado</h3>
        <p>Solo administradores pueden ver esta sección</p>
    </div>
<?php } ?>
