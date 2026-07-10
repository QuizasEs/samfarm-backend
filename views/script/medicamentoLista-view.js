const MedicamentosModals = (function() {
    'use strict';

    function getBaseURL() {
        const serverUrl = document.documentElement.dataset.serverUrl;
        if (serverUrl) {
            return serverUrl.replace('ajax/notificacionesAjax.php', '');
        }
        const path = window.location.pathname;
        const match = path.match(/^\/([^\/]+)\//);
        return match ? "/" + match[1] + "/" : "/";
    }

    const API_URL = getBaseURL() + 'ajax/medicamentoAjax.php';

    function abrirModalNuevo() {
        const modal = document.getElementById('modalNuevoMedicamento');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
            document.getElementById('formNuevoMedicamento').reset();
        }
    }

    function cerrarModalNuevo() {
        const modal = document.getElementById('modalNuevoMedicamento');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    }

    function setMedicamentoDropdownEdit(name, id) {
        const sel = document.querySelector('#formEditarMedicamento select[name="' + name + '"]');
        if (!sel) return;
        sel.value = id || '';
        const input = document.getElementById('dd_' + name);
        if (input) {
            const opt = sel.options[sel.selectedIndex];
            input.value = opt ? opt.text : '';
        }
    }

    async function abrirModalEditar(med_id) {
        const modal = document.getElementById('modalEditarMedicamento');
        if (!modal) return;

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    MedicamentoAjax: 'datos_medicamento',
                    med_id: med_id
                })
            });

            const data = await response.json();

            if (data.error) {
                Swal.fire('Error', data.error, 'error');
                return;
            }

            // Fill form
            document.querySelector('#formEditarMedicamento input[name="med_id"]').value = data.med_id;
            document.querySelector('#formEditarMedicamento input[name="Nombre_up"]').value = data.med_nombre_quimico || '';
            document.querySelector('#formEditarMedicamento input[name="Principio_up"]').value = data.med_principio_activo || '';
            document.querySelector('#formEditarMedicamento input[name="Accion_up"]').value = data.med_accion_farmacologica || '';
            document.querySelector('#formEditarMedicamento input[name="Descripcion_up"]').value = data.med_descripcion || '';
            document.querySelector('#formEditarMedicamento input[name="Presentacion_up"]').value = data.med_presentacion || '';
            document.querySelector('#formEditarMedicamento input[name="CodigoBarras_up"]').value = data.med_codigo_barras || '';
            setMedicamentoDropdownEdit('Uso_up', data.uf_id);
            setMedicamentoDropdownEdit('Forma_up', data.ff_id);
            setMedicamentoDropdownEdit('Via_up', data.vd_id);
            setMedicamentoDropdownEdit('Proveedor_up', data.pr_id);

            modal.style.display = 'flex';
            modal.classList.add('open');
        } catch (error) {
            Swal.fire('Error', 'No se pudo cargar los datos', 'error');
        }
    }

    function cerrarModalEditar() {
        const modal = document.getElementById('modalEditarMedicamento');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    }

    async function guardarNuevo() {
        const formData = new FormData(document.getElementById('formNuevoMedicamento'));
        formData.append('MedicamentoAjax', 'save');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.Alerta === 'recargar') {
                cerrarModalNuevo();
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                });
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo guardar', 'error');
        }
    }

    async function guardarEditar() {
        const formData = new FormData(document.getElementById('formEditarMedicamento'));
        formData.append('MedicamentoAjax', 'update');

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.Alerta === 'recargar') {
                cerrarModalEditar();
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: data.Tipo,
                    title: data.Titulo,
                    text: data.texto
                });
            }
        } catch (error) {
            Swal.fire('Error', 'No se pudo actualizar', 'error');
        }
    }

    async function eliminarMedicamento(med_id) {
        const result = await Swal.fire({
            title: '¿Eliminar medicamento?',
            text: "Esto eliminará el medicamento. No se puede revertir.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar'
        });
        
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('MedicamentoAjax', 'eliminar');
            formData.append('med_id', med_id);
            
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.Alerta === 'recargar') {
                    Swal.fire({
                        icon: data.Tipo,
                        title: data.Titulo,
                        text: data.texto
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: data.Tipo,
                        title: data.Titulo,
                        text: data.texto
                    });
                }
            } catch (error) {
                Swal.fire('Error', 'Ocurrió un error de conexión', 'error');
            }
        }
    }

    // Función global para cerrar modales (compatibilidad)
    window.closeM = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    };

    // ===================== Dropdown reutilizable (estilo módulo caja) =====================
    function MedicamentoDropdown(inputId, resultsId, tabla, campos, selectId) {
        this.input = document.getElementById(inputId);
        this.resultsContainer = document.getElementById(resultsId);
        this.select = document.getElementById(selectId);
        this.tabla = tabla;
        this.campos = campos;
        this.debounce = null;
        if (this.input && this.resultsContainer) this.init();
    }

    MedicamentoDropdown.prototype.escapeHtml = function(text) {
        return String(text).replace(/[&<>"'`]/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[m]));
    };

    MedicamentoDropdown.prototype.search = async function(term) {
        if (term.length < 2) { this.hide(); return; }
        try {
            const body = new URLSearchParams();
            body.append('MedicamentoAjax', 'select_v2');
            body.append('tabla', this.tabla);
            body.append('campos', JSON.stringify(this.campos));
            body.append('termino', term);
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            });
            const items = await response.json();
            this.renderResults(items);
        } catch (err) {
            this.renderResults([]);
        }
    };

    MedicamentoDropdown.prototype.renderResults = function(items) {
        if (!this.resultsContainer) return;
        if (!items.length) {
            this.resultsContainer.innerHTML = '<div class="search-results-item no-results">No se encontraron resultados</div>';
        } else {
            this.resultsContainer.innerHTML = items.map(item => {
                const id = item[this.campos[0]];
                const name = item[this.campos[1]] || '';
                const extra = item[this.campos[2]] || '';
                return `
                    <div class="search-results-item" data-id="${id}" data-name="${this.escapeHtml(name)}" style="cursor: pointer; padding: 8px; border-bottom: 1px solid #eee;">
                        <div><strong>${this.escapeHtml(name)}</strong></div>
                        ${extra ? `<small style="color: #666;">${this.escapeHtml(extra)}</small>` : ''}
                    </div>`;
            }).join('');
            this.attachListeners();
        }
        this.applyStyles();
        this.show();
    };

    MedicamentoDropdown.prototype.applyStyles = function() {
        if (!this.resultsContainer) return;
        this.resultsContainer.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            background: var(--bg-primary, #fff);
            border: 1px solid var(--border-light, #ddd);
            border-top: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 0 0 4px 4px;
            margin-top: 0;
        `;
    };

    MedicamentoDropdown.prototype.attachListeners = function() {
        this.resultsContainer.querySelectorAll('.search-results-item').forEach(item => {
            item.addEventListener('click', () => this.selectProvider(item));
        });
    };

    MedicamentoDropdown.prototype.selectProvider = function(item) {
        this.input.value = item.dataset.name;
        this.input.dataset.selectedId = item.dataset.id;
        if (this.select) {
            this.select.value = item.dataset.id;
            this.select.dispatchEvent(new Event('change'));
        }
        this.hide();
    };

    MedicamentoDropdown.prototype.show = function() { this.resultsContainer.style.display = 'block'; };
    MedicamentoDropdown.prototype.hide = function() { this.resultsContainer.style.display = 'none'; };

    MedicamentoDropdown.prototype.init = function() {
        this.input.addEventListener('input', (e) => {
            this.input.dataset.selectedId = '';
            if (this.select) this.select.value = '';
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => this.search(e.target.value.trim()), 300);
        });
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.hide();
        });
        document.addEventListener('click', (e) => {
            if (!this.resultsContainer.contains(e.target) && e.target !== this.input) this.hide();
        });
    };

    // Inicializar dropdown del filtro de proveedores (vista lista)
    new MedicamentoDropdown(
        'dd_proveedor_filtro',
        'dd_proveedor_filtro_res',
        'proveedores',
        ['pr_id', 'pr_razon_social', 'pr_nit'],
        'select1'
    );

    // Inicializar dropdowns del modal Nuevo Medicamento
    new MedicamentoDropdown('dd_Uso_reg', 'dd_Uso_reg_res', 'uso_farmacologico', ['uf_id', 'uf_nombre'], 'sel_Uso_reg');
    new MedicamentoDropdown('dd_Forma_reg', 'dd_Forma_reg_res', 'forma_farmaceutica', ['ff_id', 'ff_nombre'], 'sel_Forma_reg');
    new MedicamentoDropdown('dd_Via_reg', 'dd_Via_reg_res', 'via_de_administracion', ['vd_id', 'vd_nombre'], 'sel_Via_reg');
    new MedicamentoDropdown('dd_Proveedor_reg', 'dd_Proveedor_reg_res', 'proveedores', ['pr_id', 'pr_razon_social', 'pr_nit'], 'sel_Proveedor_reg');

    // Inicializar dropdowns del modal Editar Medicamento
    new MedicamentoDropdown('dd_Uso_up', 'dd_Uso_up_res', 'uso_farmacologico', ['uf_id', 'uf_nombre'], 'sel_Uso_up');
    new MedicamentoDropdown('dd_Forma_up', 'dd_Forma_up_res', 'forma_farmaceutica', ['ff_id', 'ff_nombre'], 'sel_Forma_up');
    new MedicamentoDropdown('dd_Via_up', 'dd_Via_up_res', 'via_de_administracion', ['vd_id', 'vd_nombre'], 'sel_Via_up');
    new MedicamentoDropdown('dd_Proveedor_up', 'dd_Proveedor_up_res', 'proveedores', ['pr_id', 'pr_razon_social', 'pr_nit'], 'sel_Proveedor_up');

    return {
        abrirModalNuevo,
        cerrarModalNuevo,
        abrirModalEditar,
        cerrarModalEditar,
        guardarNuevo,
        guardarEditar,
        eliminarMedicamento
    };
})();
