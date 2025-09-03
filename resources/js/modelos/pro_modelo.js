window.modelosManager = () => ({
    // Estados
    showModal: false,
    isEditing: false,
    editingModeloId: null,
    searchTerm: '',
    isSubmitting: false,
    
    formData: {
        modelo_descripcion: ''
    },
    
    modelos: [],
    
    init() {
        this.loadModelos();
    },
    
    loadModelos() {
        try {
            const modelosData = document.getElementById('modelos-data');
            if (modelosData) {
                this.modelos = JSON.parse(modelosData.textContent);
            }
        } catch (error) {
            console.error('Error cargando modelos:', error);
            this.modelos = [];
        }
    },
    
    // RUTAS EXPLÍCITAS - DIRECTAS
    getCreateRoute() {
        return '/modelos';  // Ruta para CREAR
    },
    
    getUpdateRoute() {
        return '/modelos/actualizar';  // Ruta para ACTUALIZAR
    },
    
    getDeleteRoute() {
        return '/modelos/eliminar';  // Ruta para ELIMINAR
    },
    
    isFormValid() {
        const isValid = this.formData.modelo_descripcion.trim().length > 0 && 
                      this.formData.modelo_descripcion.trim().length <= 50;
        return isValid;
    },
    
    validateForm() {
        this.isFormValid();
    },
    
    openCreateModal() {
        this.isEditing = false;
        this.editingModeloId = null;
        this.resetFormData();
        this.showModal = true;
    },
    
    editModelo(modeloId) {
        const modelo = this.modelos.find(m => m.modelo_id === modeloId);
        
        if (modelo) {
            this.isEditing = true;
            this.editingModeloId = modeloId;
            this.formData = {
                modelo_descripcion: modelo.modelo_descripcion
            };
            this.showModal = true;
        } else {
            this.showSweetAlert('error', 'Error', 'Modelo no encontrado');
        }
    },
    
    async handleFormSubmit(event) {
        event.preventDefault();
        this.isSubmitting = true;
        
        if (!this.isFormValid()) {
            this.showSweetAlert('error', 'Error de validación', 'La descripción debe tener entre 1 y 50 caracteres');
            this.isSubmitting = false;
            return false;
        }
        
        try {
            const formData = new FormData();
            formData.append('modelo_descripcion', this.formData.modelo_descripcion.trim());
            
            // Agregar CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            let url;
            let method;
            
            if (this.isEditing) {
                url = this.getUpdateRoute();
                method = 'POST';
                formData.append('modelo_id', this.editingModeloId);
                formData.append('_method', 'PUT');
            } else {
                url = this.getCreateRoute();
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
            
            const responseData = await response.json();
            const {success, mensaje} = responseData;
            
            if (success === true) {
                this.showSweetAlert('success', 'Éxito', mensaje || 
                    (this.isEditing ? 'Modelo actualizado correctamente' : 'Modelo creado correctamente'));
                this.closeModal();
                
                // Recargar la página después de éxito
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showSweetAlert('error', 'Error', mensaje || 'Error al procesar la solicitud');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showSweetAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.isSubmitting = false;
        }
    },
    
    closeModal() {
        this.showModal = false;
        this.isSubmitting = false;
        this.resetFormData();
    },
    
    resetFormData() {
        this.formData = {
            modelo_descripcion: ''
        };
    },
    
    showSweetAlert(type, title, text) {
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
        }
        
        Swal.fire(config);
    },
    
    deleteModelo(modeloId) {
        const modelo = this.modelos.find(m => m.modelo_id === modeloId);
        if (!modelo) return;
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el modelo "${modelo.modelo_descripcion}"?`,
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
    },
    
    submitDeleteForm(modeloId) {
        // RUTA EXPLÍCITA PARA ELIMINAR
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = this.getDeleteRoute();
        
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
    },
    
    showModelo(modeloId) {
        const modelo = this.modelos.find(m => m.modelo_id === modeloId);
        if (!modelo) return false;
        
        // Filtro por término de búsqueda
        if (this.searchTerm) {
            const searchLower = this.searchTerm.toLowerCase();
            const matchesSearch = 
                modelo.modelo_descripcion.toLowerCase().includes(searchLower) ||
                modelo.modelo_id.toString().includes(this.searchTerm);
            
            if (!matchesSearch) {
                return false;
            }
        }
        
        return true;
    },
    
    filterModelos() {
        // El filtrado se hace automáticamente en showModelo()
    },
    
    clearFilters() {
        this.searchTerm = '';
    }
});