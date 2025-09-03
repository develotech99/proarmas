window.metodosPagoManager = () => ({
   
    // Estados
    showModal: false,
    showDebug: true,
    isEditing: false,
    editingMetodoId: null,
    searchTerm: '',
    statusFilter: '',
    isSubmitting: false,
    
    // Form data
    formData: {
        metpago_descripcion: '',
        metpago_situacion: ''
    },
    
    // Los datos se pasarán desde la vista
    metodosPago: [],
 
    init() {
        console.log('🚀 metodosPagoManager inicializado');
        this.loadMetodosPago();
        console.log('📊 Total métodos de pago cargados:', this.metodosPago.length);
    },
 
    loadMetodosPago() {
        try {
            const metodosPagoData = document.getElementById('metodos-pago-data');
            if (metodosPagoData) {
                this.metodosPago = JSON.parse(metodosPagoData.textContent);
                console.log('📊 Métodos de pago cargados desde script:', this.metodosPago.length);
            }
        } catch (error) {
            console.error('Error cargando métodos de pago:', error);
            this.metodosPago = [];
        }
    },
 
    getFormAction() {
        const baseUrl = window.location.origin;
        const action = this.isEditing 
            ? `${baseUrl}/metodos-pago/${this.editingMetodoId}` 
            : `${baseUrl}/metodos-pago`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },
 
    isFormValid() {
        const descripcionValid = this.formData.metpago_descripcion.trim().length > 0 && 
                                this.formData.metpago_descripcion.trim().length <= 50;
        const situacionValid = this.formData.metpago_situacion !== '' && 
                              ['0', '1'].includes(this.formData.metpago_situacion.toString());
 
        const isValid = descripcionValid && situacionValid;
        console.log('✅ Validación form:', { descripcionValid, situacionValid, isValid });
        return isValid;
    },
 
    validateForm() {
        this.isFormValid();
    },
 
    openCreateModal() {
        console.log('➕ Abriendo modal para crear método de pago');
        this.isEditing = false;
        this.editingMetodoId = null;
        this.resetFormData();
        this.showModal = true;
    },
 
    editMetodo(metodoId) {
        console.log('✏️ Editando método de pago con ID:', metodoId);
        const metodo = this.metodosPago.find(m => m.metpago_id === metodoId);
        if (metodo) {
            this.isEditing = true;
            this.editingMetodoId = metodoId;
            this.formData = {
                metpago_descripcion: metodo.metpago_descripcion,
                metpago_situacion: metodo.metpago_situacion.toString()
            };
            this.showModal = true;
        } else {
            console.error('❌ Método de pago no encontrado:', metodoId);
            this.showSweetAlert('error', 'Error', 'Método de pago no encontrado');
        }
    },
 
    async handleFormSubmit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario...');
        
        this.isSubmitting = true;
        
        if (!this.isFormValid()) {
            console.error('❌ Formulario inválido');
            this.showSweetAlert('error', 'Error de validación', 'Por favor complete todos los campos correctamente');
            this.isSubmitting = false;
            return false;
        }
 
        try {
            const formData = new FormData();
            formData.append('metpago_descripcion', this.formData.metpago_descripcion.trim());
            formData.append('metpago_situacion', this.formData.metpago_situacion);
            
            // Agregar CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            if (this.isEditing) {
                formData.append('_method', 'PUT');
            }
 
            const response = await fetch(this.getFormAction(), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
 
            if (response.ok) {
                const responseData = await response.json();
                this.showSweetAlert('success', 'Éxito', responseData.message || 
                    (this.isEditing ? 'Método de pago actualizado correctamente' : 'Método de pago creado correctamente'));
                this.closeModal();
                // Recargar la página o actualizar la lista
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorData = await response.json();
                this.showSweetAlert('error', 'Error', errorData.message || 'Error al procesar la solicitud');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showSweetAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.isSubmitting = false;
        }
    },
 
    closeModal() {
        console.log('🔒 Cerrando modal');
        this.showModal = false;
        this.isSubmitting = false;
        this.resetFormData();
    },
 
    resetFormData() {
        this.formData = {
            metpago_descripcion: '',
            metpago_situacion: ''
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
 
    deleteMetodo(metodoId) {
        const metodo = this.metodosPago.find(m => m.metpago_id === metodoId);
        if (!metodo) return;
 
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el método de pago "${metodo.metpago_descripcion}"?`,
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
                this.submitDeleteForm(metodoId);
            }
        });
    },
 
    submitDeleteForm(metodoId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/metodos-pago/${metodoId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    },
 
    showMetodo(metodoId) {
        const metodo = this.metodosPago.find(m => m.metpago_id === metodoId);
        if (!metodo) return false;
 
        // Filtro por término de búsqueda
        if (this.searchTerm && !metodo.metpago_descripcion.toLowerCase().includes(this.searchTerm.toLowerCase())) {
            return false;
        }
 
        // Filtro por estado
        if (this.statusFilter !== '' && metodo.metpago_situacion.toString() !== this.statusFilter) {
            return false;
        }
 
        return true;
    },
 
    filterMetodos() {
        // El filtrado se hace en showMetodo()
    },
 
    clearFilters() {
        this.searchTerm = '';
        this.statusFilter = '';
    }
 });