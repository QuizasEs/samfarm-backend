<?php
if (isset($_SESSION['id_smp']) && ($_SESSION['rol_smp'] != 1 && $_SESSION['rol_smp'] != 2)) {
?>
    <div style="text-align: center; padding: 60px;">
        <h2><ion-icon name="lock-closed-outline"></ion-icon> Acceso Denegado</h2>
        <p>No tiene permisos para acceder a esta sección.</p>
    </div>
<?php
    exit();
}
require_once "./controllers/medicamentoController.php";
$ins_med = new medicamentoController();
$datos_select = $ins_med->datos_extras_controller();
$ultimo_lote = $ins_med->ultimo_lote_controller();
$ultima_compra = $ins_med->ultima_compra_controller();


?>
<div class="">
    <div class="ph">
        <div>
            <div class="ptit">Registrar Compra</div>
            <div class="psub">Complete los datos para registrar una nueva compra</div>
        </div>
    </div>

    <form class="FormularioAjax formCompra" id="formCompra" action="<?php echo SERVER_URL; ?>ajax/compraAjax.php" method="POST"
        data-form="save" autocomplete="off">

        <input type="hidden" name="compraAjax" value="save">
        <input type="hidden" name="lotes_json" id="lotes_json" value="[]">
        <input type="hidden" name="totales_json" id="totales_json" value="{}">
        <input type="hidden" id="ultimo_lote_valor" value="<?php echo $ultimo_lote ?? 0; ?>">
        <input type="hidden" id="ultima_campra_valor" value="<?php echo $ultima_compra ?? 0; ?>">

        <script>
            /**
             * Clase para gestionar la lógica de la Vista de Orden de Compra
             */
            class CompraOrdenManager {
                constructor() {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', () => this.init());
                    } else {
                        this.init();
                    }
                }

                init() {
                    this.cacheElements();
                    this.bindEvents();
                    this.initContadores();
                }

                cacheElements() {
                    this.form = document.getElementById('formCompra');
                    this.costoLista = document.getElementById('costo_lista');
                    this.margenUnitario = document.getElementById('margen_unitario');
                    this.margenCaja = document.getElementById('margen_caja');
                    this.cantidad = document.getElementById('cantidad');
                    this.cantidadUnidades = document.getElementById('cantidad_unidades');
                    this.precioVentaReg = document.getElementById('precio_venta_reg');
                    this.precioMinCaja = document.getElementById('precio_min_caja');
                    this.precioMinUnitario = document.getElementById('precio_min_unitario');
                }

                bindEvents() {
                    // Manejo del envío del formulario
                    if (this.form) {
                        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
                    }

                    // Cálculos automáticos (Evita el error de null al validar existencia)
                    if (this.costoLista) {
                        this.costoLista.addEventListener('input', () => {
                            this.calcularPrecioVenta();
                            this.calcularPrecioMinCaja();
                            this.calcularPrecioMinUnitario();
                        });
                    }

                    if (this.margenUnitario) {
                        this.margenUnitario.addEventListener('input', () => {
                            this.calcularPrecioVenta();
                            this.calcularPrecioMinUnitario();
                        });
                        this.margenUnitario.addEventListener('blur', (e) => this.validarMargen(e.target));
                    }

                    if (this.margenCaja) {
                        this.margenCaja.addEventListener('input', () => this.calcularPrecioMinCaja());
                        this.margenCaja.addEventListener('blur', (e) => this.validarMargen(e.target));
                    }

                    if (this.cantidad) {
                        this.cantidad.addEventListener('input', () => this.calcularPrecioMinCaja());
                    }

                    if (this.cantidadUnidades) {
                        this.cantidadUnidades.addEventListener('input', () => this.calcularPrecioMinCaja());
                    }
                }

                initContadores() {
                    // Sincronizar con el ModalManager si existe
                    const originalAbrirModal = window.abrirModal || function() {};
                    window.abrirModal = (id, nombre) => {
                        originalAbrirModal(id, nombre);
                        // Re-cacheamos elementos porque el modal puede haber inyectado nuevos o haberlos hecho visibles
                        this.cacheElements();
                    };
                }



                handleSubmit(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const lotes = typeof ModalManager !== 'undefined' ? ModalManager.obtenerLotes() : [];
                    const totales = typeof ModalManager !== 'undefined' ? ModalManager.obtenerTotales() : {};

                    this.setHiddenInput('lotes_json', JSON.stringify(lotes));
                    this.setHiddenInput('totales_json', JSON.stringify(totales));
                    
                    // Enviar el formulario manualmente despues de actualizar los campos
                    this.form.submit();
                }

                setHiddenInput(id, value) {
                    let input = document.getElementById(id);
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = id;
                        input.id = id;
                        this.form.appendChild(input);
                    }
                    input.value = value;
                }

                calcularPrecioVenta() {
                    const costo = parseFloat(this.costoLista?.value) || 0;
                    const margen = parseFloat(this.margenUnitario?.value) || 0;
                    const precioVenta = costo + (costo * margen / 100);
                    if (this.precioVentaReg) this.precioVentaReg.value = precioVenta.toFixed(2);
                }

                calcularPrecioMinCaja() {
                    const costo = parseFloat(this.costoLista?.value) || 0;
                    const margen = parseFloat(this.margenCaja?.value) || 0;
                    const unidadesPorCaja = parseInt(this.cantidadUnidades?.value) || 1;
                    const precioMinCaja = costo * unidadesPorCaja * (1 + margen / 100);
                    if (this.precioMinCaja) this.precioMinCaja.value = precioMinCaja.toFixed(2);
                }

                calcularPrecioMinUnitario() {
                    const costo = parseFloat(this.costoLista?.value) || 0;
                    const margen = parseFloat(this.margenUnitario?.value) || 0;
                    const precioMinUnitario = costo * (1 + margen / 100);
                    if (this.precioMinUnitario) this.precioMinUnitario.value = precioMinUnitario.toFixed(2);
                }

                validarMargen(input) {
                    let valor = parseFloat(input.value);
                    if (isNaN(valor)) {
                        input.value = '0.00';
                        return;
                    }
                    if (valor < 0) valor = 0;
                    if (valor > 100) valor = 100;
                    input.value = valor.toFixed(2);
                }
            }

            // Instanciar el manager de la vista
            const compraManager = new CompraOrdenManager();

            // Funciones globales para compatibilidad con atributos onchange/onclick del HTML existente
            function actualizarProveedor() { compraManager.actualizarProveedor(); }
            function calcularPrecioVenta() { compraManager.calcularPrecioVenta(); }
            function calcularPrecioMinCaja() { compraManager.calcularPrecioMinCaja(); }
            function calcularPrecioMinUnitario() { compraManager.calcularPrecioMinUnitario(); }
            function forzarLimiteMargen(input) { compraManager.validarMargen(input); }
            function validarMargen(input) { compraManager.validarMargen(input); }
        </script>

        <!-- DATOS ESENCIALES -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Datos de Compra</span>
            </div>
            <div class="cb">
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="numero_compra">Número de Compra*</label>
                        <input class="inp" type="text" name="Numero_compra_reg" id="numero_compra" readonly>
                    </div>
                </div>
            </div>
        </div>


        <!-- FILTRAR MEDICAMENTO -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Filtrar por Medicamento</span>
            </div>
             <div class="cb">
                 <div class="filtro-dinamico">
                     <div class="fr3">
                        <div class="fg">
                            <label class="fl" for="Proveedor_filtro">Proveedor</label>
                            <select class="sel select-filtro" name="Proveedor_filtro" id="Proveedor_filtro" onchange="actualizarProveedor()">
                                <option value="">Todos</option>
                                <?php foreach ($datos_select['proveedores'] as $pro) { ?>
                                    <option value="<?php echo $pro['pr_id']; ?>" data-nit="<?php echo $pro['pr_nit']; ?>"><?php echo $pro['pr_razon_social']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="Form_reg">Forma Farmacéutica</label>
                            <select class="sel select-filtro" name="Form_reg" id="Form_reg">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['forma_farmaceutica'] as $forma) { ?>
                                    <option value="<?php echo $forma['ff_id']; ?>"><?php echo $forma['ff_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="Via_reg">Vía de Administración</label>
                            <select class="sel select-filtro" name="Via_reg" id="Via_reg">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['via_administracion'] as $via) { ?>
                                    <option value="<?php echo $via['vd_id']; ?>"><?php echo $via['vd_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="fr1">
                        <div class="fg">
                            <label class="fl" for="Uso_reg">Uso Farmacológico</label>
                            <select class="sel select-filtro" name="Uso_reg" id="Uso_reg">
                                <option value="">Seleccionar</option>
                                <?php foreach ($datos_select['uso_farmacologico'] as $uso) { ?>
                                    <option value="<?php echo $uso['uf_id']; ?>"><?php echo $uso['uf_nombre']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="fg">
                            <label class="fl" for="buscarMedicamento">Buscar Medicamento</label>
                            <div class="inpg">
                                <input class="inp" type="text" name="termino" id="buscarMedicamento" placeholder="Buscar medicamento...">
                                <button type="button" class="btn btn-def btn-search">
                                    <ion-icon name="search-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                         </div>
                 </div>
             </div>
        </div>

        <!-- RESULTADO DE BÚSQUEDA -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Resultado Filtrado</span>
            </div>
            <div class="cb">
                <div class="tw">
                    <table>
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Producto</th>
                                <th>Presentación</th>
                                <th>Descripción</th>
                                <th>Código de Barras</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tablaMedicamentos">
                            <!-- Resultados de búsqueda -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- LISTA DE MEDICAMENTOS AGREGADOS -->
        <div class="card mb16">
            <div class="ch">
                <span class="ct">Lista de Medicamentos Agregados</span>
            </div>
            <div class="cb">
                <div id="items-compra" class="content"></div>
            </div>
        </div>

        <!-- TOTALES -->
        <div class="card">
            <div class="ch">
                <span class="ct">Totales</span>
            </div>
            <div class="cb">
                <div class="calc">
                    <div class="calc-group">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="calc-group">
                        <span>Impuestos:</span>
                        <span id="impuestos">$0.00</span>
                    </div>
                    <div class="calc-group total">
                        <span>TOTAL:</span>
                        <span id="total">$0.00</span>
                    </div>
                </div>
                <div class="calc-buttons">
                     <button type="submit" class="btn btn-suc">Agregar</button>
                    <a href="#" class="btn btn-war">Cancelar</a>
                </div>
            </div>
        </div>

    </form>

    <!-- MODAL LOTE -->
    <div class="mov" id="modalLote">
        <div class="modal mxl">
            <div class="mh">
                <div>
                    <div class="mt">Agregar Lote - <span id="modalMedicamentoNombre">Paracetamol</span></div>
                    <div class="ms">Complete los datos del lote</div>
                </div>
                <button class="mcl" onclick="cerrarModal()"><ion-icon name="close-outline"></ion-icon></button>
            </div>

            <input type="hidden" id="modalMedicamentoId" value="1">

            <div class="mb">
                <div class="stit">Información Básica</div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="numero_lote">Número de Lote</label>
                        <input class="inp" type="text" id="numero_lote" readonly>
                    </div>
                </div>

                <div class="stit">Cantidades</div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="cantidad">Número de Cajas que Entran</label>
                        <input class="inp" type="number" name="Cantidad_reg" id="cantidad" min="1">
                    </div>
                    <div class="fg">
                        <label class="fl" for="cantidad_unidades">Unidades por Caja</label>
                        <input class="inp" type="number" name="Cantidad_unidades_reg" id="cantidad_unidades" min="1" value="1" oninput="calcularPrecioMinCaja();">
                    </div>
                </div>

                <div class="stit">Fechas</div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="fecha_vencimiento">Vencimiento</label>
                        <input class="inp" type="date" name="Vencimiento_reg" id="fecha_vencimiento">
                    </div>
                </div>

                <div class="stit">Precios Principales</div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="precio_compra">Precio Costo</label>
                        <input class="inp" type="number" name="Precio_compra_reg" id="precio_compra" step="0.01" min="0.01">
                    </div>
                    <div class="fg">
                        <label class="fl" for="precio_venta_reg">Precio Venta</label>
                        <input class="inp" type="number" name="precio_venta_reg" id="precio_venta_reg" step="0.01" min="0.01" oninput="calcularMargenDePrecio();">
                    </div>
                </div>

                <div class="stit">Auditoría y Márgenes</div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="costo_lista">Costo Lista</label>
                        <input class="inp" type="number" id="costo_lista" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label class="fl" for="margen_unitario">Margen Unitario (%)</label>
                        <input class="inp" type="number" id="margen_unitario" step="0.01" min="0">
                    </div>
                </div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="margen_caja">Margen Caja (%)</label>
                        <input class="inp" type="number" id="margen_caja" step="0.01" min="0">
                    </div>
                    <div class="fg">
                        <label class="fl" for="precio_min_unitario">Precio Min. Unitario</label>
                        <input class="inp" type="number" id="precio_min_unitario" step="0.01" min="0">
                    </div>
                </div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl" for="precio_min_caja">Precio Min. Caja</label>
                        <input class="inp" type="number" id="precio_min_caja" step="0.01" min="0">
                    </div>
                </div>

                <div class="stit">Opciones</div>
                <div class="fr">
                    <div class="fg">
                        <label class="fl">Activar este Lote?</label>
                        <div class="swg">
                            <div></div>
                            <label class="sw">
                                <input type="checkbox" id="cb5">
                                    <span class="swt"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mf">
                <button class="btn btn-war" onclick="cerrarModal()">Cancelar</button>
                <button class="btn btn-suc" onclick="agregarLote()">Agregar</button>
            </div>
        </div>
    </div>
</div>