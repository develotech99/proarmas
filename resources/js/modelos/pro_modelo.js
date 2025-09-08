/**
 * Gestor de Modelos con Marcas
 * JavaScript puro - Laravel 12
 */
class ModelosManager {
    constructor() {
        this.modelos = [];
        this.marcas = [];
        this.isEditing = false;
        this.editingModeloId = null;
        this.filtros = {
            searchTerm: '',
            marcaFilter: '',
            statusFilter: ''
        };
        
        this.init();
    }

    /**
     * Inicializar el gestor
     */
    init() {
        console.log('🚀 ModelosManager inicializado');
        this.loadModelos();
        this.setupEventListeners();
        this.loadMarcasForSelect();
        this.loadMarcasForFilter();
        this.renderModelos();
    }

    /**
     * Cargar modelos desde el script tag
     */
    loadModelos() {
        try {
            const modelosData = document.getElementById('modelos-data');
            if (modelosData) {
                this.modelos = JSON.parse(modelosData.textContent);
                console.log('📊 Modelos cargados:', this.modelos.length);
            }
        } catch (error) {
            console.error('❌ Error cargando modelos:', error);
            this.modelos = [];
        }
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Filtros
        document.getElementById('search-modelos').addEventListener('input', (e) => {
            this.filtros.searchTerm = e.target.value;
            this.aplicarFiltros();
        });

        document.getElementById('marca-filter').addEventListener('change', (e) => {
            this.filtros.marcaFilter = e.target.value;
            this.aplicarFiltros();
        });

        document.getElementById('status-filter').addEventListener('change', (e) => {
            this.filtros.statusFilter = e.target.value;
            this.aplicarFiltros();
        });

        // Formulario
        document.getElementById('modelo-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleFormSubmit();
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }

    /**
     * Cargar marcas para el select del modal
     */
    async loadMarcasForSelect() {
        try {
            const response = await fetch('/modelos/marcas-activas');
            if (response.ok) {
                const data = await response.json();
                const select = document.getElementById('modelo_marca_id');
                
                // Limpiar options existentes (excepto el placeholder)
                select.innerHTML = '<option value="">Seleccionar marca</option>';
                
                // Agregar las marcas activas
                data.data.forEach(marca => {
                    const option = document.createElement('option');
                    option.value = marca.marca_id;
                    option.textContent = marca.marca_descripcion;
                    select.appendChild(option);
                });

                this.marcas = data.data;
            }
        } catch (error) {
            console.error('Error cargando marcas para select:', error);
        }
    }

    /**
     * Cargar marcas para el filtro
     */
    async loadMarcasForFilter() {
        try {
            const response = await fetch('/modelos/marcas-activas');
            if (response.ok) {
                const data = await response.json();
                const select = document.getElementById('marca-filter');
                
                // Limpiar options existentes (excepto el placeholder)
                select.innerHTML = '<option value="">Todas las marcas</option>';
                
                // Agregar las marcas activas
                data.data.forEach(marca => {
                    const option = document.createElement('option');
                    option.value = marca.marca_id;
                    option.textContent = marca.marca_descripcion;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error cargando marcas para filtro:', error);
        }
    }

    /**
     * Aplicar filtros
     */
    aplicarFiltros() {
        this.renderModelos();
    }

    /**
     * Filtrar modelos
     */
    getModelosFiltrados() {
        return this.modelos.filter(modelo => {
            const matchSearch = !this.filtros.searchTerm || 
                modelo.modelo_descripcion.toLowerCase().includes(this.filtros.searchTerm.toLowerCase()) ||
                modelo.modelo_id.toString().includes(this.filtros.searchTerm);
            
            const matchMarca = !this.filtros.marcaFilter || 
                modelo.modelo_marca_id.toString() === this.filtros.marcaFilter;

            const matchStatus = this.filtros.statusFilter === '' || 
                modelo.modelo_situacion.toString() === this.filtros.statusFilter;

            return matchSearch && matchMarca && matchStatus;
        });
    }

    /**
     * Renderizar modelos en la tabla
     */
    renderModelos() {
        const tbody = document.getElementById('modelos-tbody');
        const modelosFiltrados = this.getModelosFiltrados();

        if (modelosFiltrados.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                            </svg>
                            <p class="mt-2">No hay modelos que mostrar</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = modelosFiltrados.map(modelo => `
            <tr data-modelo-id="${modelo.modelo_id}">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                    ${modelo.modelo_id}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8">
                            <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
                                <span class="text-xs font-medium text-white">
                                    ${modelo.modelo_descripcion.substring(0, 2).toUpperCase()}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                ${modelo.modelo_descripcion}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        ${modelo.marca_nombre || 'Sin marca'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        modelo.modelo_situacion == 1 
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                    }">
                        ${modelo.modelo_situacion == 1 ? 'Activo' : 'Inactivo'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${modelo.created_at ? new Date(modelo.created_at).toLocaleDateString('es-ES') : '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center space-x-2">
                        <button onclick="modelosManager.editModelo(${modelo.modelo_id})" 
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 p-1"
                                title="Editar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button onclick="modelosManager.deleteModelo(${modelo.modelo_id})" 
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 p-1"
                                title="Eliminar">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    /**
     * Limpiar filtros
     */
    clearFilters() {
        this.filtros = {
            searchTerm: '',
            marcaFilter: '',
            statusFilter: ''
        };

        document.getElementById('search-modelos').value = '';
        document.getElementById('marca-filter').value = '';
        document.getElementById('status-filter').value = '';

        this.aplicarFiltros();
    }

    /**
     * Abrir modal para crear modelo
     */
    openCreateModal() {
        this.isEditing = false;
        this.editingModeloId = null;
        
        document.getElementById('modelo-modal-title').textContent = 'Crear Nuevo Modelo';
        document.getElementById('modelo-submit-text').textContent = 'Crear';
        
        this.resetForm();
        this.loadMarcasForSelect();
        this.showModal();
    }

    /**
     * Editar modelo
     */
    editModelo(modeloId) {
        const modelo = this.modelos.find(m => m.modelo_id === modeloId);
        
        if (!modelo) {
            this.showAlert('error', 'Error', 'Modelo no encontrado');
            return;
        }

        this.isEditing = true;
        this.editingModeloId = modeloId;
        
        document.getElementById('modelo-modal-title').textContent = 'Editar Modelo';
        document.getElementById('modelo-submit-text').textContent = 'Actualizar';
        
        // Cargar marcas y luego llenar el formulario
        this.loadMarcasForSelect().then(() => {
            document.getElementById('modelo_marca_id').value = modelo.modelo_marca_id;
            document.getElementById('modelo_descripcion').value = modelo.modelo_descripcion;
        });
        
        this.showModal();
    }

    /**
     * Manejar envío del formulario
     */
    async handleFormSubmit() {
        // Validar formulario
        if (!this.validateForm()) {
            return;
        }

        this.setLoading(true);

        try {
            const formData = new FormData();
            formData.append('modelo_descripcion', document.getElementById('modelo_descripcion').value.trim());
            formData.append('modelo_marca_id', document.getElementById('modelo_marca_id').value);
            
            // Agregar CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            let url, method;
            
            if (this.isEditing) {
                url = '/modelos/actualizar';
                method = 'POST';
                formData.append('modelo_id', this.editingModeloId);
                formData.append('_method', 'PUT');
            } else {
                url = '/modelos';
                method = 'POST';
            }

            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showAlert('success', 'Éxito', data.mensaje);
                this.closeModal();
                
                // Recargar la página después de un breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                if (data.errors) {
                    this.showValidationErrors(data.errors);
                } else {
                    this.showAlert('error', 'Error', data.mensaje || 'Error al procesar la solicitud');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Eliminar modelo
     */
    deleteModelo(modeloId) {
        const modelo = this.modelos.find(m => m.modelo_id === modeloId);
        
        if (!modelo) {
            this.showAlert('error', 'Error', 'Modelo no encontrado');
            return;
        }

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el modelo "${modelo.modelo_descripcion}" de la marca "${modelo.marca_nombre}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'dark:bg-gray-800 dark:text-gray-100',
                title: 'dark:text-gray-100',
                content: 'dark:text-gray-300'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.submitDeleteForm(modeloId);
            }
        });
    }

    /**
     * Enviar formulario de eliminación
     */
    submitDeleteForm(modeloId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/modelos/eliminar';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const idField = document.createElement('input');
        idField.type = 'hidden';
        idField.name = 'modelo_id';
        idField.value = modeloId;
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(idField);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }

    /**
     * Validar formulario
     */
    validateForm() {
        const descripcion = document.getElementById('modelo_descripcion').value.trim();
        const marcaId = document.getElementById('modelo_marca_id').value;
        
        this.clearErrors();
        
        let isValid = true;
        
        if (!marcaId) {
            this.showFieldError('modelo_marca_id', 'La marca es obligatoria');
            isValid = false;
        }
        
        if (!descripcion) {
            this.showFieldError('modelo_descripcion', 'La descripción del modelo es obligatoria');
            isValid = false;
        } else if (descripcion.length > 50) {
            this.showFieldError('modelo_descripcion', 'La descripción no puede exceder 50 caracteres');
            isValid = false;
        }
        
        return isValid;
    }

    /**
     * Mostrar modal
     */
    showModal() {
        const modal = document.getElementById('modelo-modal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Cerrar modal
     */
    closeModal() {
        const modal = document.getElementById('modelo-modal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        this.setLoading(false);
        this.clearErrors();
        this.resetForm();
    }

    /**
     * Resetear formulario
     */
    resetForm() {
        document.getElementById('modelo-form').reset();
        this.clearErrors();
    }

    /**
     * Establecer estado de carga
     */
    setLoading(loading) {
        const submitBtn = document.getElementById('modelo-submit-btn');
        const submitText = document.getElementById('modelo-submit-text');
        const loadingSpan = document.getElementById('modelo-loading');
        
        submitBtn.disabled = loading;
        
        if (loading) {
            submitText.classList.add('hidden');
            loadingSpan.classList.remove('hidden');
        } else {
            submitText.classList.remove('hidden');
            loadingSpan.classList.add('hidden');
        }
    }

    /**
     * Limpiar errores
     */
    clearErrors() {
        const form = document.getElementById('modelo-form');
        const errorElements = form.querySelectorAll('.text-red-600');
        
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });
    }

    /**
     * Mostrar error de campo
     */
    showFieldError(fieldName, message) {
        const errorElement = document.getElementById(`${fieldName}_error`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }

    /**
     * Mostrar errores de validación
     */
    showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`${field}_error`);
            if (errorElement && errors[field].length > 0) {
                errorElement.textContent = errors[field][0];
                errorElement.classList.remove('hidden');
            }
        });
    }

    /**
     * Mostrar alerta
     */
    showAlert(type, title, text) {
        const config = {
            title: title,
            text: text,
            icon: type,
            customClass: {
                popup: 'dark:bg-gray-800 dark:text-gray-100',
                title: 'dark:text-gray-100',
                content: 'dark:text-gray-300'
            }
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
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.modelosManager = new ModelosManager();
});