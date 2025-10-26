// resources/js/clientes/clientes.js

/**
 * Gestor de Clientes - JavaScript puro (Laravel 10+)
 * Requiere: SweetAlert2 (Swal) disponible globalmente
 */

class ClientesManager {
    constructor() {
        // Estado
        this.clientes = [];
        this.isEditing = false;
        this.editingClienteId = null;

        // Filtros
        this.filtros = {
            searchTerm: '',
            tipoFilter: '',
            statusFilter: '' // opcional (si agregas el select en Blade)
        };

        // CSRF
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // Debouncers
        this._debounceTimers = {};

        // Init
        this.init();
    }

    // ==========================
    // Utils
    // ==========================
    debounce(key, fn, delay = 250) {
        clearTimeout(this._debounceTimers[key]);
        this._debounceTimers[key] = setTimeout(fn, delay);
    }

    showAlert(type, title, text) {
        const config = {
            title,
            html: text ?? '',
            icon: type,
            confirmButtonText: 'Entendido'
        };
        if (type === 'success') {
            config.confirmButtonColor = '#10b981';
            config.timer = 3000;
        } else if (type === 'error') {
            config.confirmButtonColor = '#dc2626';
        } else if (type === 'warning') {
            config.confirmButtonColor = '#f59e0b';
        }
        Swal.fire(config);
    }

    // ==========================
    // Init
    // ==========================
    init() {
        console.log('🚀 ClientesManager inicializado');

        this.loadClientes();
        this.setupEventListeners();
        this.renderClientes();
    }

    loadClientes() {
        try {
            const script = document.getElementById('clientes-data');
            this.clientes = script ? JSON.parse(script.textContent) : [];
            console.log('📊 Clientes cargados:', this.clientes.length);
        } catch (e) {
            console.error('❌ Error cargando clientes:', e);
            this.clientes = [];
        }
    }

    setupEventListeners() {
        // Filtro de búsqueda (con debounce)
        const search = document.getElementById('search-clientes');
        if (search) {
            search.addEventListener('input', (e) => {
                const val = e.target.value;
                this.debounce('search', () => {
                    this.filtros.searchTerm = val;
                    this.aplicarFiltros();
                }, 250);
            });
        }

        // Filtro de tipo
        const tipo = document.getElementById('tipo-filter');
        if (tipo) {
            tipo.addEventListener('change', (e) => {
                this.filtros.tipoFilter = e.target.value;
                this.aplicarFiltros();
            });
        }

        // Filtro de estado (si lo llegas a agregar al Blade)
        const status = document.getElementById('status-filter');
        if (status) {
            status.addEventListener('change', (e) => {
                this.filtros.statusFilter = e.target.value;
                this.aplicarFiltros();
            });
        }

        // Botón "Limpiar"
        const btnLimpiar = document.querySelector('[onclick="clientesManager.clearFilters()"]');
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', () => this.clearFilters());
        }

        // Tipo de cliente en el formulario
        const tipoCliente = document.getElementById('tipoCliente');
        if (tipoCliente) {
            tipoCliente.addEventListener('change', (e) => this.toggleCamposSegunTipo(e.target.value));
        }

        // Selector Premium
        const premium = document.getElementById('clientePremium');
        if (premium) {
            premium.addEventListener('change', (e) => {
                const opt = e.target.options[e.target.selectedIndex];
                if (opt && opt.value) this.llenarDatosClientePremium(opt);
            });
        }

        // Form submit
        const form = document.getElementById('formNuevoCliente');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmit();
            });
        }

        // Botón Guardar (type="button" en tu Blade)
        const btnGuardar = document.getElementById('modalGuardarCliente');
        if (btnGuardar) {
            btnGuardar.addEventListener('click', () => this.handleFormSubmit());
        }

        // Cerrar modal con botones/overlay/ESC
        document.getElementById('modalCerrarNC')?.addEventListener('click', () => this.closeModal());
        document.getElementById('modalCancelarNC')?.addEventListener('click', () => this.closeModal());
        document.getElementById('modalOverlayNC')?.addEventListener('click', () => this.closeModal());
        document.addEventListener('keydown', (e) => (e.key === 'Escape') && this.closeModal());
    }

    // ==========================
    // Filtros
    // ==========================
    aplicarFiltros() {
        this.renderClientes();
    }

    clearFilters() {
        this.filtros = { searchTerm: '', tipoFilter: '', statusFilter: '' };
        const search = document.getElementById('search-clientes');
        const tipo = document.getElementById('tipo-filter');
        const status = document.getElementById('status-filter');
        if (search) search.value = '';
        if (tipo) tipo.value = '';
        if (status) status.value = '';
        this.aplicarFiltros();
    }

    getClientesFiltrados() {
        const term = (this.filtros.searchTerm || '').toLowerCase();

        return this.clientes.filter(c => {
            const matchSearch =
                !term ||
                (c.nombre_completo && c.nombre_completo.toLowerCase().includes(term)) ||
                (c.cliente_dpi && String(c.cliente_dpi).includes(term)) ||
                (c.cliente_telefono && String(c.cliente_telefono).includes(term)) ||
                (c.cliente_id && String(c.cliente_id).includes(term));

            const matchTipo = !this.filtros.tipoFilter || String(c.cliente_tipo) === this.filtros.tipoFilter;
            const matchStatus = this.filtros.statusFilter === '' ||
                String(c.cliente_situacion) === this.filtros.statusFilter;

            return matchSearch && matchTipo && matchStatus;
        });
    }

    // ==========================
    // Render
    // ==========================
    renderClientes() {
        const tbody = document.getElementById('clientes-tbody');
        if (!tbody) return;

        const data = this.getClientesFiltrados();

        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-lg font-medium text-gray-500">No hay clientes que mostrar</p>
                            <p class="text-sm text-gray-400 mt-1">Intenta ajustar los filtros o agrega un nuevo cliente</p>
                        </div>
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = data.map(c => {
            const tipoTxt = c.cliente_tipo == 1 ? 'Normal' : (c.cliente_tipo == 2 ? 'Premium' : (c.cliente_tipo == 3 ? 'Empresa' : 'Otro'));
            const colorTipo = c.cliente_tipo == 1 ? 'bg-blue-100 text-blue-800'
                            : c.cliente_tipo == 2 ? 'bg-purple-100 text-purple-800'
                            : c.cliente_tipo == 3 ? 'bg-green-100 text-green-800'
                            : 'bg-gray-100 text-gray-800';

            // Fondo del avatar
            const avatarClass = colorTipo.replace('100', '500').replace('text-', 'text-').includes('text-')
                ? 'bg-blue-500' : 'bg-gray-500';
            const avatarBG =
                c.cliente_tipo == 1 ? 'bg-blue-500'
              : c.cliente_tipo == 2 ? 'bg-purple-500'
              : c.cliente_tipo == 3 ? 'bg-green-500'
              : 'bg-gray-500';

            const iniciales = `${(c.cliente_nombre1 || '').substring(0,1).toUpperCase()}${(c.cliente_apellido1 || '').substring(0,1).toUpperCase()}`;

            return `
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-900">#${c.cliente_id}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full ${avatarBG} flex items-center justify-center shadow-md">
                                    <span class="text-sm font-bold text-white">${iniciales}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-semibold text-gray-900">${c.nombre_completo}</div>
                                ${c.cliente_nom_empresa ? `
                                    <div class="text-xs text-gray-500 flex items-center mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"></path>
                                        </svg>
                                        ${c.cliente_nom_empresa}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${c.cliente_dpi || '---'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                            </svg>
                            ${c.cliente_telefono || '---'}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorTipo}">${tipoTxt}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            c.cliente_situacion == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">
                            <svg class="w-2 h-2 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            ${c.cliente_situacion == 1 ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <button onclick="clientesManager.editCliente(${c.cliente_id})"
                                    class="inline-flex items-center p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors duration-150"
                                    title="Editar cliente">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="clientesManager.deleteCliente(${c.cliente_id})"
                                    class="inline-flex items-center p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                    title="Eliminar cliente">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7-2h8a1 1 0 011 1v1H6V6a1 1 0 011-1z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    // ==========================
    // Modal
    // ==========================
    openCreateModal() {
        this.isEditing = false;
        this.editingClienteId = null;

        const titulo = document.querySelector('#modalNuevoCliente h3');
        if (titulo) titulo.innerHTML = '<i class="fas fa-user-plus mr-2 text-emerald-200"></i>Registrar nuevo cliente';

        const btn = document.getElementById('modalGuardarCliente');
        if (btn) btn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar';

        this.resetForm();
        this.showModal();
    }

    editCliente(clienteId) {
        const c = this.clientes.find(x => Number(x.cliente_id) === Number(clienteId));
        if (!c) return this.showAlert('error', 'Error', 'Cliente no encontrado');

        this.isEditing = true;
        this.editingClienteId = clienteId;

        const titulo = document.querySelector('#modalNuevoCliente h3');
        if (titulo) titulo.innerHTML = '<i class="fas fa-user-edit mr-2 text-blue-200"></i>Editar cliente';

        const btn = document.getElementById('modalGuardarCliente');
        if (btn) btn.innerHTML = '<i class="fas fa-save mr-2"></i>Actualizar';

        // Tipo
        const tipo = document.getElementById('tipoCliente');
        if (tipo) {
            tipo.value = c.cliente_tipo;
            this.toggleCamposSegunTipo(String(c.cliente_tipo));
        }

        // Si es premium, setear el select
        if (c.cliente_tipo == 2 && c.cliente_user_id) {
            const premium = document.getElementById('clientePremium');
            if (premium) premium.value = c.cliente_user_id;
        }

        // Básicos
        document.getElementById('idCliente').value = c.cliente_user_id || '';
        document.getElementById('nc_nombre1').value = c.cliente_nombre1 || '';
        document.getElementById('nc_nombre2').value = c.cliente_nombre2 || '';
        document.getElementById('nc_apellido1').value = c.cliente_apellido1 || '';
        document.getElementById('nc_apellido2').value = c.cliente_apellido2 || '';
        document.getElementById('nc_dpi').value = c.cliente_dpi || '';
        document.getElementById('nc_nit').value = c.cliente_nit || '';
        document.getElementById('nc_telefono').value = c.cliente_telefono || '';
        document.getElementById('nc_correo').value = c.cliente_correo || '';
        document.getElementById('nc_direccion').value = c.cliente_direccion || '';

        // Empresa
        if (c.cliente_tipo == 3) {
            document.getElementById('nombreEmpresa').value = c.cliente_nom_empresa || '';
            document.getElementById('nc_nombre_vendedor').value = c.cliente_nom_vendedor || '';
            document.getElementById('nc_telefono_vendedor').value = c.cliente_cel_vendedor || '';
            document.getElementById('nc_ubicacion').value = c.cliente_ubicacion || '';
        }

        this.showModal();
    }

    showModal() {
        const modal = document.getElementById('modalNuevoCliente');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal() {
        const modal = document.getElementById('modalNuevoCliente');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        this.resetForm();
    }

    resetForm() {
        const form = document.getElementById('formNuevoCliente');
        if (form) form.reset();

        this.limpiarCamposFormulario();
        this.ocultarInputsEmpresa();

        const selectorPremium = document.getElementById('selectorPremium');
        if (selectorPremium) selectorPremium.style.display = 'none';

        const premium = document.getElementById('clientePremium');
        if (premium) premium.value = '';

        const tipo = document.getElementById('tipoCliente');
        if (tipo) tipo.value = '';
    }

    // ==========================
    // Form helpers
    // ==========================
    toggleCamposSegunTipo(tipo) {
        const selectorPremium = document.getElementById('selectorPremium');

        // Ocultar todo
        if (selectorPremium) selectorPremium.style.display = 'none';
        this.ocultarInputsEmpresa();
        this.limpiarCamposFormulario();

        // Mostrar según tipo
        if (tipo === '2') { // Premium
            if (selectorPremium) selectorPremium.style.display = 'block';
        } else if (tipo === '3') { // Empresa
            this.mostrarInputsEmpresa();
        }
    }

    llenarDatosClientePremium(opt) {
        document.getElementById('idCliente').value = opt.dataset.clienteid || '';
        document.getElementById('nc_nombre1').value = opt.dataset.nombre1 || '';
        document.getElementById('nc_nombre2').value = opt.dataset.nombre2 || '';
        document.getElementById('nc_apellido1').value = opt.dataset.apellido1 || '';
        document.getElementById('nc_apellido2').value = opt.dataset.apellido2 || '';
        document.getElementById('nc_dpi').value = opt.dataset.dpi || '';
    }

    limpiarCamposFormulario() {
        const ids = [
            'idCliente','nc_nombre1','nc_nombre2','nc_apellido1','nc_apellido2','nc_dpi','nc_nit',
            'nc_telefono','nc_correo','nc_direccion','nombreEmpresa','nc_telefono_vendedor',
            'nc_nombre_vendedor','nc_ubicacion'
        ];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }

    ocultarInputsEmpresa() {
        const ids = ['nombreEmpresa','nc_telefono_vendedor','nc_nombre_vendedor','nc_ubicacion'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add('hidden');
                el.disabled = true;
            }
        });
        document.getElementById('contenedorempresa')?.classList.add('hidden');
        document.getElementById('titulopropietario')?.classList.add('hidden');
    }

    mostrarInputsEmpresa() {
        const ids = ['nombreEmpresa','nc_telefono_vendedor','nc_nombre_vendedor','nc_ubicacion'];
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('hidden');
                el.disabled = false;
            }
        });
        document.getElementById('contenedorempresa')?.classList.remove('hidden');
        document.getElementById('titulopropietario')?.classList.remove('hidden');
    }

    // ==========================
    // CRUD
    // ==========================
    async handleFormSubmit() {
        const tipo = document.getElementById('tipoCliente')?.value || '';
        const nombre1 = (document.getElementById('nc_nombre1')?.value || '').trim();
        const apellido1 = (document.getElementById('nc_apellido1')?.value || '').trim();

        if (!tipo) return this.showAlert('warning', 'Atención', 'Debe seleccionar un tipo de cliente');
        if (!nombre1 || !apellido1) return this.showAlert('warning', 'Atención', 'El primer nombre y apellido son obligatorios');

        const btn = document.getElementById('modalGuardarCliente');
        const original = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
        }

        try {
            const form = document.getElementById('formNuevoCliente');
            const formData = new FormData(form);

            // Agregar token/headers
            if (this.csrfToken) formData.append('_token', this.csrfToken);
            // No enviar 'clientePremium' al backend
            formData.delete('clientePremium');

            let url = '/clientes';
            let method = 'POST';

            if (this.isEditing) {
                url = '/clientes/actualizar';
                formData.append('cliente_id', this.editingClienteId);
                formData.append('_method', 'PUT');
            }

            const resp = await fetch(url, {
                method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await resp.json().catch(() => ({}));

            if (resp.ok && data.success) {
                this.showAlert('success', 'Éxito', data.mensaje || 'Operación realizada correctamente');
                this.closeModal();
                // Recargamos para sincronizar con la paginación/servidor
                setTimeout(() => window.location.reload(), 1200);
            } else {
                if (data?.errors) {
                    let html = '<ul class="list-disc list-inside text-left">';
                    Object.values(data.errors).forEach(arr => { html += `<li>${arr[0]}</li>`; });
                    html += '</ul>';
                    Swal.fire({ title: 'Errores de validación', html, icon: 'error' });
                } else {
                    this.showAlert('error', 'Error', data?.mensaje || 'Error al procesar la solicitud');
                }
            }
        } catch (e) {
            console.error(e);
            this.showAlert('error', 'Error', 'Error de conexión con el servidor');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = original;
            }
        }
    }

    deleteCliente(clienteId) {
        const c = this.clientes.find(x => Number(x.cliente_id) === Number(clienteId));
        if (!c) return this.showAlert('error', 'Error', 'Cliente no encontrado');

        Swal.fire({
            title: '¿Estás seguro?',
            html: `¿Deseas eliminar al cliente <strong>"${c.nombre_completo}"</strong>?<br><small class="text-gray-500">Esta acción no se puede deshacer</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) this.submitDeleteForm(clienteId);
        });
    }

    submitDeleteForm(clienteId) {
        // Enviar como form clásico para aprovechar tu ruta DELETE + redirect con flash
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/clientes/eliminar';

        // CSRF
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = this.csrfToken;
        form.appendChild(csrf);

        // _method DELETE
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        // ID
        const id = document.createElement('input');
        id.type = 'hidden';
        id.name = 'cliente_id';
        id.value = clienteId;
        form.appendChild(id);

        document.body.appendChild(form);
        form.submit();
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    window.clientesManager = new ClientesManager();
});
