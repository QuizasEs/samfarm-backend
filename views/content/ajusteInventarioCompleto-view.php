<?php
if (!in_array($_SESSION['rol_smp'], [1, 2])) {
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}

require_once './controllers/ajusteInventarioCompletoController.php';
$ins_ajuste = new ajusteInventarioCompletoController();

$rol_usuario = (int)$_SESSION['rol_smp'];

/* La eleccion de sucursal es la misma que en Transferencias: viaja como segmento
   de la ruta (ajusteInventarioCompleto/5/) y se valida contra las sucursales a
   las que el usuario tiene permiso. El administrador ajusta en cualquiera; los
   demas usuarios solo en la suya.

   A diferencia de Transferencias, el POST ya no manda la sucursal: el controlador
   la deduce de la sesion. El segmento de la URL es solo la eleccion de pantalla,
   no una autorizacion. */
$sucursales = $ins_ajuste->sucursales_permitidas_controlador();

$segmentos = isset($_GET['views']) ? explode('/', (string)$_GET['views']) : array();
$sucursal_elegida = isset($segmentos[1]) && $segmentos[1] !== '' ? (int)$segmentos[1] : 0;

$sucursal_nombre = '';
$sucursal_permitida = 0;
if ($sucursal_elegida > 0) {
    foreach ($sucursales as $s) {
        if ((int)$s['su_id'] === $sucursal_elegida) {
            $sucursal_permitida = (int)$s['su_id'];
            $sucursal_nombre = $s['su_nombre'];
        }
    }
}
?>

<div id="ajuste-container" data-su-actual="<?php echo $sucursal_permitida; ?>">

<?php if ($sucursal_permitida === 0) { ?>

    <div class="ph">
        <div>
            <div class="ptit"><ion-icon name="build-outline"></ion-icon> Ajuste de Inventario Completo</div>
            <div class="psub">Elija la sucursal cuyo inventario desea ajustar</div>
        </div>
    </div>

    <div class="card mb16">
        <div class="ch">
            <div class="ct"><ion-icon name="business-outline"></ion-icon> Sucursal</div>
        </div>
        <div class="cb">
            <?php if (empty($sucursales)) { ?>
                <p class="txctr tmut" style="padding: 20px;">
                    <ion-icon name="alert-circle-outline" style="font-size: 48px;"></ion-icon><br>
                    No tiene ninguna sucursal activa asignada para ajustar el inventario
                </p>
            <?php } else { ?>
                <div class="grid3">
                    <?php foreach ($sucursales as $s) { ?>
                        <a class="card" style="text-decoration:none;color:inherit;display:block;"
                           href="<?php echo SERVER_URL; ?>ajusteInventarioCompleto/<?php echo (int)$s['su_id']; ?>/">
                            <div class="cb" style="display:flex;align-items:center;gap:14px;">
                                <div class="siw bl" style="width:48px;height:48px;">
                                    <ion-icon name="business-outline" style="font-size:24px"></ion-icon>
                                </div>
                                <div style="flex:1;">
                                    <div class="th4"><?php echo htmlspecialchars($s['su_nombre']); ?></div>
                                    <div class="tbs tmut">Ajustar el inventario de esta sucursal</div>
                                </div>
                                <ion-icon name="chevron-forward" style="font-size:22px;color:var(--text-faint)"></ion-icon>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>

<?php } else { ?>

    <div class="ph">
        <div>
            <div class="ptit"><ion-icon name="build-outline"></ion-icon> Ajuste de Inventario Completo</div>
            <div class="psub">Sucursal: <strong><?php echo htmlspecialchars($sucursal_nombre); ?></strong> &mdash; busque un medicamento para corregir sus datos y los de sus lotes activos.</div>
        </div>
        <a class="btn btn-def" href="<?php echo SERVER_URL; ?>ajusteInventarioCompleto/">
            <ion-icon name="arrow-back-outline"></ion-icon> Cambiar sucursal
        </a>
    </div>

    <!-- Formulario de Búsqueda -->
    <div class="card mb16">
        <div class="ch">
            <div class="ct"><ion-icon name="search-outline"></ion-icon> Búsqueda de Medicamento</div>
        </div>
        <div class="cb">
            <form class="filtro-dinamico" onsubmit="return false;">
                <div class="fr2">
                    <div class="fg">
                        <label class="fl">Buscar Medicamento</label>
                        <input class="inp" type="text" id="termino_busqueda"
                               placeholder="Nombre, principio activo, código...">
                    </div>
                    <div class="fg">
                        <label class="fl">&nbsp;</label>
                        <button type="button" class="btn btn-def btn-search">
                            <ion-icon name="search-outline"></ion-icon> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Area de Resultados de Búsqueda -->
    <div id="resultados-busqueda-container" style="display: none;">
        <div class="card">
            <div class="ch">
                <div class="ct"><ion-icon name="list-outline"></ion-icon> Resultados de Búsqueda</div>
            </div>
            <div class="cb">
                <div class="tabla-contenedor table-container"></div>
            </div>
        </div>
    </div>

    <!-- Area de Edición -->
    <div id="edicion-medicamento-container" style="display: none;">
    </div>

<?php } ?>

</div>

<script>
    /* Sustituto de Swal.

       Swal viene de un CDN (views/inc/head.php) y en una red sin salida a
       internet el script no carga: `Swal` no existe y la PRIMERA llamada a
       Swal.fire() revienta con "Swal is not defined". En este modulo eso no era
       cosmético, porque el boton de eliminar lote esta detrás de una confirmacion
       de Swal: sin el, el clic no hacia nada y el usuario no recibia ningun aviso
       ni de exito ni de error.

       Por eso este sustituto SI pregunta de verdad, con botones de Si y No, y no
       autoconfirma. Devuelve la misma promesa que SweetAlert y la misma forma de
       resultado ({isConfirmed}), asi que el resto del archivo no cambia. */
    const Swal = window.Swal || (function () {
        'use strict';

        let overlay = null;

        const cerrar = () => {
            if (overlay) {
                overlay.remove();
                overlay = null;
            }
        };

        const btn = (etiqueta, valor, color) =>
            '<button type="button" data-sw="' + valor + '" style="padding:8px 16px;border-radius:6px;'
            + 'border:1px solid ' + color + ';background:' + color + ';color:#fff;cursor:pointer;'
            + 'font-size:13px;font-family:inherit;">' + etiqueta + '</button>';

        function mostrar(html) {
            return new Promise((resolve) => {
                cerrar();

                overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;display:flex;'
                    + 'align-items:center;justify-content:center;background:rgba(0,0,0,.45)';

                const caja = document.createElement('div');
                caja.style.cssText = 'background:#fff;border-radius:8px;padding:22px 26px;max-width:430px;'
                    + 'font-family:inherit;box-shadow:0 10px 40px rgba(0,0,0,.3)';
                caja.innerHTML = html;

                let resuelto = false;
                const terminar = (confirmado) => {
                    if (resuelto) return;
                    resuelto = true;
                    document.removeEventListener('keydown', alTecla);
                    overlay.remove();
                    overlay = null;
                    resolve({ isConfirmed: !!confirmado, isDenied: false });
                };
                const alTecla = (ev) => {
                    if (ev.key === 'Escape') terminar(false);
                };

                document.addEventListener('keydown', alTecla);
                overlay.addEventListener('click', (ev) => {
                    if (ev.target === overlay) terminar(false);
                });
                caja.querySelectorAll('button[data-sw]').forEach((b) => {
                    b.addEventListener('click', () => terminar(b.dataset.sw === 'si'));
                });

                overlay.appendChild(caja);
                document.body.appendChild(overlay);

                const primero = caja.querySelector('button[data-sw="si"]');
                if (primero) primero.focus();
            });
        }

        return {
            /* Acepta las dos formas que usa este archivo:
               fire(titulo, texto, icono) y fire({title, text, icon}). */
            fire(a, b, c) {
                const esObjeto = a !== null && typeof a === 'object';
                const titulo = esObjeto ? (a.title || '') : (a || '');
                const cuerpo = esObjeto ? (a.text || '') : (b || '');
                const icono = esObjeto ? (a.icon || '') : (c || '');

                /* SweetAlert distingue 'text' (escapado) de 'html' (confia en el
                   que llama). Este modulo usa 'html' para la lista de cambios del
                   medicamento: sin esto el sustituto mostraria el titulo solo y el
                   usuario confirmaria a ciegas. Se inserta sin escapar porque lo
                   arma este mismo archivo, con escaparHtml aplicado a cada valor. */
                const cuerpoHtml = esObjeto ? (a.html || '') : '';

                /* Con objeto, el boton de cancelar solo aparece si el que llama lo
                   pide. En la forma de texto (los avisos simples) se deja como
                   estaba: siempre se puede cerrar. */
                const conCancelar = esObjeto ? a.showCancelButton !== false : true;
                const textoConfirmar = esObjeto && a.confirmButtonText
                    ? a.confirmButtonText
                    : (icono === 'warning' ? 'Sí, continuar' : 'Aceptar');
                const textoCancelar = esObjeto && a.cancelButtonText ? a.cancelButtonText : 'Cancelar';

                const color = icono === 'success' ? '#1a7f37'
                    : icono === 'warning' ? '#b26a00'
                        : icono === 'error' ? '#c62828' : '#333';

                const cuerpoVisible = cuerpoHtml
                    ? '<div style="font-size:13px;color:#555;margin-top:8px;text-align:left">' + cuerpoHtml + '</div>'
                    : cuerpo
                        ? '<div style="font-size:13px;color:#555;margin-top:8px">' + escaparHtml(cuerpo) + '</div>'
                        : '';

                const html =
                    '<div style="font-size:15px;font-weight:600;color:' + color + '">'
                    + escaparHtml(titulo) + '</div>'
                    + cuerpoVisible
                    + '<div style="display:flex;gap:10px;margin-top:18px;justify-content:flex-end">'
                    + (conCancelar ? btn(textoCancelar, 'no', '#9aa0a6') : '')
                    + btn(textoConfirmar, 'si', '#1976D2')
                    + '</div>';

                return mostrar(html);
            },
            close: cerrar,
            showLoading() { }
        };
    })();

    function escaparHtml(valor) {
        if (valor === null || valor === undefined) return '';
        return String(valor).replace(/[&<>"']/g, function (c) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c];
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const API_URL = '<?php echo SERVER_URL; ?>ajax/ajusteInventarioCompletoAjax.php';

        /* Lo que el usuario no debe tocar se muestra como texto, con la misma
           tipografia que el resto de la tabla pero sin caja de input: asi se
           distingue de un solo vistazo que campo se puede cambiar. */
        const estiloLectura = document.createElement('style');
        estiloLectura.textContent = `
            .tabla-lotes .solo-lectura {
                color: var(--text-muted, #6b7280);
                padding-left: 10px;
                padding-right: 10px;
            }
            .tabla-lotes input.cantidad {
                max-width: 110px;
                text-align: right;
            }
        `;
        document.head.appendChild(estiloLectura);

        const contenedor = document.getElementById('ajuste-container');
        const searchTermInput = document.getElementById('termino_busqueda');
        const resultsContainer = document.getElementById('resultados-busqueda-container');
        const edicionContainer = document.getElementById('edicion-medicamento-container');

        /* En la pantalla de eleccion de sucursal no hay nada que ajustar todavia:
           no hay formulario, y referenciar searchTermInput daria null. */
        if (!searchTermInput || !resultsContainer || !edicionContainer) {
            return;
        }

        /* La sucursal viene del data- del contenedor, que el PHP lleno con la
           sucursal validada de la URL. El servidor la deduce otra vez de la
           sesion, asi que este valor es solo para saber que pantalla dibujar. */
        const SUCURSAL_ID = Number(contenedor.dataset.suActual) || 0;

        /* Campos editables del medicamento. Esta lista es la unica fuente: el
           envio, la comparacion con lo que ya estaba y el aviso de confirmacion
           se arman a partir de aca, asi que agregar un campo al formulario es
           agregar una linea aca y nada mas. */
        const CAMPOS_MEDICAMENTO = [
            { id: 'nombre', etiqueta: 'Nombre químico' },
            { id: 'principio', etiqueta: 'Principio activo' },
            { id: 'codigo', etiqueta: 'Código de barras' },
            { id: 'proveedor_id', etiqueta: 'Proveedor', esSelect: true },
            { id: 'ff_id', etiqueta: 'Forma farmacéutica', esSelect: true },
            { id: 'uf_id', etiqueta: 'Uso farmacológico', esSelect: true },
            { id: 'via_administracion', etiqueta: 'Vía de administración', esSelect: true }
        ];

        /* Estado del formulario al abrirse. Se compara contra el valor que se
           dibujo para poder preguntar por el detalle del cambio y no confirmar
           a ciegas. */
        let originalMedicamento = null;

        /* Lee los campos del formulario. Devuelve el valor que se manda al
           servidor y, aparte, el texto que se le muestra al usuario: en un select
           el valor es un id y al usuario le sirve el nombre. */
        function leerCamposMedicamento() {
            const valores = {};
            const textos = {};

            CAMPOS_MEDICAMENTO.forEach((campo) => {
                const el = document.getElementById(campo.id);
                if (!el) {
                    return;
                }

                const opcion = campo.esSelect ? el.selectedOptions[0] : null;
                const texto = (opcion ? opcion.textContent : el.value).trim();

                valores[campo.id] = el.value.trim();
                textos[campo.id] = texto === '' ? '(vacío)' : texto;
            });

            return { valores, textos };
        }

        /* Un solo punto de salida hacia el servidor: si la respuesta no es JSON
           util se avisa con el mensaje real en vez de dejar la pantalla en
           blanco. */
        async function pedir(datos) {
            const respuesta = await fetch(API_URL, { method: 'POST', body: new URLSearchParams(datos) });

            if (!respuesta.ok) {
                throw new Error('El servidor respondió ' + respuesta.status);
            }

            try {
                return await respuesta.json();
            } catch (e) {
                throw new Error('La respuesta del servidor no se pudo leer.');
            }
        }

        const searchButton = document.querySelector('.btn-search');

        searchButton.addEventListener('click', function () {
            buscarMedicamentos(searchTermInput.value.trim());
        });

        searchTermInput.addEventListener('keydown', function (ev) {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                buscarMedicamentos(searchTermInput.value.trim());
            }
        });

        async function buscarMedicamentos(termino) {
            if (!termino) {
                Swal.fire('Error', 'Escriba el nombre, el principio activo o el código del medicamento.', 'error');
                return;
            }

            try {
                const datos = await pedir({
                    accion: 'buscar_medicamentos',
                    termino: termino
                });

                if (datos.error) {
                    Swal.fire('Error', datos.error, 'error');
                    return;
                }

                renderResults(Array.isArray(datos) ? datos : []);
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }

        function renderResults(medicamentos) {
            let html = '<table class="table"><thead><tr>';
            html += '<th>Medicamento</th>';
            html += '<th>Stock</th>';
            html += '<th>Ubicación</th>';
            html += '<th>Acción</th>';
            html += '</tr></thead><tbody>';

            if (medicamentos.length > 0) {
                medicamentos.forEach(med => {
                    const sinInventario = !med.su_id;

                    /* El identificador va en data-attributes y no en un onclick.
                       Ponerlo en el onclick obligaba a pasar el valor por
                       JSON.stringify, que escribe comillas dobles, y el atributo
                       tambien va entre comillas dobles: el HTML se partia a la mitad
                       y el navegador reportaba "Unexpected end of input" al hacer
                       clic, sin llegar a abrir el formulario. Con data- no hay que
                       escapar nada porque no se escribe codigo. */
                    html += `
                        <tr class="tr-click" data-med-id="${med.med_id}">
                            <td>
                                <div class="td-main"><strong>${escaparHtml(med.med_nombre_quimico)}</strong></div>
                                <div class="td-sub">${escaparHtml(med.med_principio_activo)}</div>
                            </td>
                            <td>
                                <div class="td-main"><strong style="color:#1976D2;">${escaparHtml(med.inv_total_unidades)}</strong> unidades</div>
                                ${sinInventario ? '<div class="td-sub">sin stock en esta sucursal</div>' : ''}
                            </td>
                            <td>
                                <div class="td-main">${escaparHtml(med.su_nombre)}</div>
                            </td>
                            <td class="buttons">
                                <button type="button" class="btn btn-def">
                                    <ion-icon name="create-outline"></ion-icon> Editar
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="4" style="text-align:center;">No se encontraron resultados.</td></tr>';
            }

            if (medicamentos.length === 50) {
                html += '<tr><td colspan="4" style="text-align:center;color:#888;">'
                    + 'Se muestran los primeros 50. Acorte el término para ver el resto.'
                    + '</td></tr>';
            }

            html += '</tbody></table>';

            resultsContainer.querySelector('.tabla-contenedor').innerHTML = html;
            resultsContainer.style.display = 'block';
            edicionContainer.style.display = 'none';
        }

        /* Un solo delegado para toda la tabla de resultados. Como el boton Editar esta
           DENTRO de la fila y el clic en el boton tambien cae en la fila, no hace
           falta stopPropagation: el delegado mira primero si hay un boton y, si no,
           toma la fila. */
        resultsContainer.addEventListener('click', function (ev) {
            const boton = ev.target.closest('button[data-accion]');
            const fila = ev.target.closest('tr[data-med-id]');

            if (boton) {
                const accion = boton.dataset.accion;
                const loteId = boton.dataset.loteId;

                if (accion === 'guardar-lote') {
                    guardarLote(Number(loteId), boton);
                } else if (accion === 'terminar-lote') {
                    terminarLote(Number(loteId));
                } else if (accion === 'volver') {
                    resultsContainer.style.display = 'block';
                    edicionContainer.style.display = 'none';
                }
                return;
            }

            if (fila) {
                cargarDatosMedicamento(Number(fila.dataset.medId));
            }
        });

        async function cargarDatosMedicamento(medicamentoId) {
            if (!medicamentoId) {
                Swal.fire('Error', 'Medicamento no válido.', 'error');
                return;
            }

            try {
                const datos = await pedir({
                    accion: 'obtener_detalles',
                    medicamento_id: medicamentoId
                });

                if (datos.error) {
                    Swal.fire('Error', datos.error, 'error');
                    return;
                }

                renderFormularioEdicion(datos.medicamento, datos.lotes || [], medicamentoId);
            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }

        function renderFormularioEdicion(medicamento, lotes, medicamentoId) {
            pedir({ accion: 'obtener_listas' }).then((listas) => {
                const proveedores = listas.proveedores || [];
                const formas = listas.formas || [];
                const usos = listas.usos || [];
                const vias = listas.vias || [];
                const sinInventario = !SUCURSAL_ID;

                const opcion = (lista, campoId, campoNombre, seleccionado, vacio) => {
                    const head = `<option value="${vacio.valor}"${String(seleccionado) === String(vacio.valor) ? ' selected' : ''}>${escaparHtml(vacio.texto)}</option>`;
                    return head + lista.map(item =>
                        `<option value="${item[campoId]}"${String(item[campoId]) === String(seleccionado) ? ' selected' : ''}>${escaparHtml(item[campoNombre])}</option>`
                    ).join('');
                };

            let html = `
                <div class="card mb16">
                    <div class="ch">
                        <div class="ct"><ion-icon name="create-outline"></ion-icon> Editar Medicamento</div>
                        <div class="tbr">
                            <button type="button" class="btn btn-sec" data-accion="volver">
                                <ion-icon name="arrow-back-outline"></ion-icon> Volver a Búsqueda
                            </button>
                        </div>
                    </div>
                    <div class="cb">
                        <form id="form-editar-medicamento" onsubmit="return false;">
                            <input type="hidden" id="medicamento_id" value="${medicamento.med_id}">
                            <input type="hidden" id="sucursal_id_hidden" value="${SUCURSAL_ID}">

                            <div class="fr">
                                <div class="fg">
                                    <label class="fl req">Nombre Químico</label>
                                    <input class="inp" type="text" id="nombre" value="${escaparHtml(medicamento.med_nombre_quimico)}" required>
                                </div>
                                <div class="fg">
                                    <label class="fl req">Principio Activo</label>
                                    <input class="inp" type="text" id="principio" value="${escaparHtml(medicamento.med_principio_activo)}" required>
                                </div>
                            </div>

                            <div class="fr">
                                <div class="fg">
                                    <label class="fl">Código de Barras</label>
                                    <input class="inp" type="text" id="codigo" value="${escaparHtml(medicamento.med_codigo_barras)}">
                                </div>
                                <div class="fg">
                                    <label class="fl">Proveedor</label>
                                    <select class="sel" id="proveedor_id">
                                        ${opcion(proveedores, 'pr_id', 'pr_razon_social', medicamento.pr_id, { valor: 0, texto: 'Sin Proveedor' })}
                                    </select>
                                </div>
                            </div>

                            <div class="fr">
                                <div class="fg">
                                    <label class="fl">Forma Farmacéutica</label>
                                    <select class="sel" id="ff_id">
                                        ${opcion(formas, 'ff_id', 'ff_nombre', medicamento.ff_id, { valor: 0, texto: 'Seleccione...' })}
                                    </select>
                                </div>
                                <div class="fg">
                                    <label class="fl">Uso Farmacológico</label>
                                    <select class="sel" id="uf_id">
                                        ${opcion(usos, 'uf_id', 'uf_nombre', medicamento.uf_id, { valor: 0, texto: 'Seleccione...' })}
                                    </select>
                                </div>
                            </div>

                            <div class="fg">
                                <label class="fl">Vía de Administración</label>
                                <select class="sel" id="via_administracion">
                                    ${opcion(vias, 'vd_id', 'vd_nombre', medicamento.vd_id, { valor: 0, texto: 'Seleccione...' })}
                                </select>
                            </div>

                            <div class="cf">
                                <button type="button" class="btn btn-def" data-accion="guardar-medicamento">
                                    <ion-icon name="save-outline"></ion-icon> Actualizar Datos del Medicamento
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="ch">
                        <div class="ct"><ion-icon name="archive-outline"></ion-icon> Lotes Activos</div>
                    </div>
                    <div class="cb">
                        <div class="tabla-contenedor table-container">
                            <table class="table tabla-lotes">
                                <thead>
                                    <tr>
                                        <th>Número de Lote</th>
                                        <th>Cajas (inicial)</th>
                                        <th>Unidades por caja</th>
                                        <th>Cajas Actuales</th>
                                        <th>Unidades Actuales</th>
                                        <th>Vencimiento</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            if (lotes.length > 0) {
                lotes.forEach(lote => {
                    /* Solo tres campos son editables: las dos cantidades actuales
                       y el vencimiento. El numero de lote y los factores de
                       conversion se muestran como texto porque el usuario no debe
                       poder cambiarlos desde aqui: definien que significa una caja
                       para ese medicamento, y editarlos descoloca la valoracion
                       del balance sin que nadie lo note.

                       Los tres campos de solo lectura viajan como atributos de la
                       fila porque el guardado los sigue mandando al servidor: no
                       se editan aqui, pero el UPDATE los reescribe y no pueden
                       llegar vacios. */
                    html += `
                        <tr data-lote-id="${lote.lm_id}"
                            data-lote-nombre="${escaparHtml(lote.lm_numero_lote)}"
                            data-lote-caja="${lote.lm_cant_caja}"
                            data-lote-unidad="${lote.lm_cant_unidad}">
                            <td class="solo-lectura">${escaparHtml(lote.lm_numero_lote)}</td>
                            <td class="solo-lectura">${lote.lm_cant_caja}</td>
                            <td class="solo-lectura">${lote.lm_cant_unidad}</td>
                            <td><input type="number" class="inp cantidad lote-input" data-lote-id="${lote.lm_id}" data-field="cant_actual_cajas" value="${lote.lm_cant_actual_cajas}" min="0"></td>
                            <td><input type="number" class="inp cantidad lote-input" data-lote-id="${lote.lm_id}" data-field="cant_actual_unidades" value="${lote.lm_cant_actual_unidades}" min="0"></td>
                            <td><input type="date" class="inp lote-input" data-lote-id="${lote.lm_id}" data-field="fecha_vencimiento" value="${escaparHtml(lote.lm_fecha_vencimiento)}"></td>
                            <td class="buttons">
                                <button type="button" class="btn btn-def" data-accion="guardar-lote" data-lote-id="${lote.lm_id}" title="Guardar cambios del lote">
                                    <ion-icon name="checkmark-outline"></ion-icon>
                                </button>
                                <button type="button" class="btn btn-dan" data-accion="terminar-lote" data-lote-id="${lote.lm_id}" title="Dar el lote por terminado">
                                    <ion-icon name="archive-outline"></ion-icon>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html += '<tr><td colspan="7" style="text-align:center;">'
                    + (sinInventario
                        ? 'Este medicamento todavía no tiene inventario en ninguna sucursal, así que no hay lotes que ajustar. '
                          + 'Los datos del medicamento sí se pueden corregir.'
                        : 'No hay lotes activos en esta sucursal.')
                    + '</td></tr>';
            }

            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            edicionContainer.innerHTML = html;
            /* Foto de los valores con los que se dibujo el formulario. Es lo que
               permite despues preguntar solo por lo que el usuario cambio de
               verdad, en vez de confirmar cada clic. */
            originalMedicamento = leerCamposMedicamento();
            resultsContainer.style.display = 'none';
            edicionContainer.style.display = 'block';
        }).catch((error) => {
            Swal.fire('Error', error.message, 'error');
        });
    }

    /* Un solo delegado tambien para el formulario de edicion: los botones de
       Volver, Actualizar medicamento, guardar lote y eliminar lote. */
    edicionContainer.addEventListener('click', function (ev) {
        const boton = ev.target.closest('button[data-accion]');
        if (!boton) return;

        switch (boton.dataset.accion) {
            case 'volver':
                resultsContainer.style.display = 'block';
                edicionContainer.style.display = 'none';
                break;
            case 'guardar-medicamento':
                guardarMedicamento(boton);
                break;
            case 'guardar-lote':
                guardarLote(Number(boton.dataset.loteId), boton);
                break;
            case 'terminar-lote':
                terminarLote(Number(boton.dataset.loteId));
                break;
        }
    });

    async function guardarMedicamento(boton) {
        const actual = leerCamposMedicamento();

        const cambios = originalMedicamento
            ? CAMPOS_MEDICAMENTO
                .filter((campo) => actual.valores[campo.id] !== originalMedicamento.valores[campo.id])
                .map((campo) => ({
                    etiqueta: campo.etiqueta,
                    antes: originalMedicamento.textos[campo.id],
                    despues: actual.textos[campo.id]
                }))
            : [];

        /* Sin foto previa no hay forma de saber que cambio, asi que no se puede
           decir "no hay cambios": se pregunta a secas. Decirlo seria peor, porque
           dejaria un formulario que no guarda sin avisar de nada. */
        if (originalMedicamento && cambios.length === 0) {
            Swal.fire('Sin cambios', 'No modificó ningún dato del medicamento.', 'info');
            return;
        }

        const cuerpo = cambios.length > 0
            ? '<ul style="margin:0;padding-left:18px">'
                + cambios.map((cambio) =>
                    '<li style="margin:3px 0;text-align:left">'
                    + `<b>${escaparHtml(cambio.etiqueta)}</b>: `
                    + `${escaparHtml(cambio.antes)} &rarr; ${escaparHtml(cambio.despues)}</li>`
                ).join('')
                + '</ul>'
            : '<div style="font-size:13px;color:#555">Se van a actualizar los datos del medicamento.</div>';

        const confirmacion = await Swal.fire({
            title: '¿Guardar los cambios?',
            html: cuerpo,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmacion.isConfirmed) {
            return;
        }

        if (boton) boton.disabled = true;

        try {
            const datos = await pedir({
                accion: 'actualizar_medicamento',
                medicamento_id: document.getElementById('medicamento_id').value,
                ...actual.valores
            });

            Swal.fire(datos.success ? 'Éxito' : 'Error', datos.success || datos.error, datos.success ? 'success' : 'error');

            /* Si se guardo, el formulario pasa a ser el estado vigente: si el
               usuario lo vuelve a tocar, la proxima confirmacion lista solo lo
               que cambio desde ahora y no repite lo anterior. */
            if (datos.success) {
                originalMedicamento = actual;
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        } finally {
            if (boton) boton.disabled = false;
        }
    }

    async function guardarLote(lote_id, boton) {
        const valor = (campo) => {
            const input = document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="${campo}"]`);
            return input ? input.value : '';
        };

        const filaLote = document.querySelector(`tr[data-lote-id="${lote_id}"]`);

        if (boton) boton.disabled = true;

        try {
            const datos = await pedir({
                accion: 'actualizar_lote',
                lote_id: lote_id,
                numero_lote: filaLote ? filaLote.dataset.loteNombre : '',
                cant_caja: filaLote ? filaLote.dataset.loteCaja : 0,
                cant_unidad: filaLote ? filaLote.dataset.loteUnidad : 0,
                cant_actual_cajas: valor('cant_actual_cajas'),
                cant_actual_unidades: valor('cant_actual_unidades'),
                fecha_vencimiento: valor('fecha_vencimiento')
            });

            Swal.fire(datos.success ? 'Éxito' : 'Error', datos.success || datos.error, datos.success ? 'success' : 'error');
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        } finally {
            if (boton) boton.disabled = false;
        }
    }

        async function terminarLote(lote_id) {
        /* El numero de lote se lee de los datos de la fila y no se pasa por el
           boton: un numero de lote con comilla simple que viaja dentro de un
           atributo se decodifica dos veces (HTML y despues JavaScript) y rompe el
           script. */
        const fila = document.querySelector(`tr[data-lote-id="${lote_id}"]`);
        const numeroLote = fila && fila.dataset.loteNombre ? fila.dataset.loteNombre : ('lote ' + lote_id);

        const unidades = document.querySelector(`.lote-input[data-lote-id="${lote_id}"][data-field="cant_actual_unidades"]`);
        const quedan = unidades && unidades.value ? unidades.value : 0;

        const confirmacion = await Swal.fire({
            title: '¿Dar el lote por terminado?',
            html: `El lote <b>${escaparHtml(numeroLote)}</b> dejará de contar en el inventario`
                + (parseInt(quedan, 10) > 0
                    ? ` y sus <b>${escaparHtml(quedan)}</b> unidades saldrán del stock.`
                    : '.')
                + '<br><br>No se borra: queda como <b>terminado</b> con su historial, '
                + 'y las ventas ya registradas no se modifican.'
                + '<br><small>Si la baja es por caducidad, rotura o pérdida, usa el módulo de Mermas: pide el motivo y lo deja asentado.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, terminar el lote',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmacion.isConfirmed) return;

        try {
            const datos = await pedir({
                accion: 'terminar_lote',
                lote_id: lote_id
            });

            if (!datos.success) {
                Swal.fire('Error', datos.error, 'error');
                return;
            }

            Swal.fire('Terminado', datos.success, 'success');

            /* Se recarga el formulario para que desaparezca la fila del lote. */
            cargarDatosMedicamento(Number(document.getElementById('medicamento_id').value));
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }
});
</script>