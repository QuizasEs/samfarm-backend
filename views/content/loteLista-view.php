<div class="container tabla-dinamica"
    data-ajax-table="true"
    data-ajax-url="ajax/loteAjax.php"
    data-ajax-param="loteAjax"
    data-ajax-registros="10">

    <form class="filtro-dinamico">
        <select name="select1">
            <option value="">Todos</option>
            <option value="en_espera">en_espera</option>
            <option value="activo">Activo</option>
            <option value="terminado">terminado</option>
            <option value="caducado">Caducado</option>
        </select>
        <select name="select2" id="">
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
        


        <input type="text" name="busqueda" id="filtroBusqueda" placeholder="Buscar por nombre o lote...">
        <button type="button" class="btn-search"><ion-icon name="search"></ion-icon></button>
    </form>

    <div class="tabla-contenedor"></div>
</div>


<!-- modal -->
<!-- Modal Activar Lote -->
<div id="modalActivarLote" class="modal" style="display:none;">
    <div class="modal-overlay"></div>
    <div class="modal-window">
        <button class="modal-close">&times;</button>
        <h3>Activar lote</h3>
        <div class="modal-body">
            <p><strong>Atención:</strong> La activación del lote solo puede hacerse una vez.
                Luego la edición será limitada.</p>
            <div id="detalleLote"></div>
        </div>
        <div class="modal-footer">
            <button id="btnConfirmarActivacion" class="btn btn-success">Confirmar Activación</button>
            <button class="modal-close btn btn-secondary">Cancelar</button>
        </div>
    </div>
</div>

<style>
    .modal {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-window {
        position: relative;
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        z-index: 2;
    }

    .modal-close {
        position: absolute;
        top: 10px;
        right: 10px;
        border: none;
        background: none;
        font-size: 1.5rem;
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('click', async (e) => {
        // abrir modal al hacer click en botón activar
        const btn = e.target.closest('.btn-activar-lote');
        if (btn) {
            const id = btn.dataset.id;
            const nombre = btn.dataset.nombre;

            const modal = document.getElementById('modalActivarLote');
            modal.style.display = 'flex';
            document.getElementById('detalleLote').innerHTML = `
       <p><b>Lote:</b> #${id}</p>
       <p><b>Medicamento:</b> ${nombre}</p>
       <p>Confirma que deseas activar este lote. Esta acción no se puede deshacer.</p>
    `;
            document.getElementById('btnConfirmarActivacion').dataset.id = id;
        }

        // cerrar modal
        if (e.target.classList.contains('modal-close')) {
            e.target.closest('.modal').style.display = 'none';
        }
    });

    // confirmar activación
    document.addEventListener('click', (e) => {
        if (e.target.id === 'btnConfirmarActivacion') {
            const id = e.target.dataset.id;
            if (!id) return;
            Swal.fire({
                title: "¿Activar este lote?",
                text: "Solo se podrá activar una vez.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, activar"
            }).then((result) => {
                if (result.isConfirmed) {
                    activarLote(id);
                }
            });
        }
    });

    async function activarLote(id) {
        try {
            const res = await fetch('ajax/loteAjax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `loteAjax=active&id=${id}`
            });
            const data = await res.json();

            Swal.fire({
                title: data.Titulo || 'Resultado',
                html: data.texto || '',
                icon: data.Tipo || 'info'
            });

            // refrescar tabla
            document.querySelector('.filtro-dinamico .btn-search')?.click();

            document.getElementById('modalActivarLote').style.display = 'none';
        } catch (err) {
            Swal.fire("Error", "No se pudo procesar la solicitud", "error");
        }
    }
</script>