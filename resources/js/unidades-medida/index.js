window.unidadesMedidaManager = () => ({
   
    // Estados
    showModal: false,
    showDebug: true,
    isEditing: false,
    editingUnidadId: null,
    searchTerm: '',
    tipoFilter: '',
    statusFilter: '',
    isSubmitting: false,
    
    // Form data
    formData: {
        unidad_nombre: '',
        unidad_abreviacion: '',
        unidad_tipo: '',
        unidad_situacion: ''
    },
    
    // Los datos se pasarán desde la vista
    unidadesMedida: [],
    tipos: {},
 
    init() {
        console.log('🚀 unidadesMedidaManager inicializado');
        this.loadUnidadesMedida();
        this.loadTipos();
        console.log('📊 Total unidades de medida cargadas:', this.unidadesMedida.length);
    },
 
    loadUnidadesMedida() {
        try {
            const unidadesMedidaData = document.getElementById('unidades-medida-data');
            if (unidadesMedidaData) {
                this.unidadesMedida = JSON.parse(unidadesMedidaData.textContent);
                console.log('📊 Unidades de medida cargadas desde script:', this.unidadesMedida.length);
            }
        } catch (error) {
            console.error('Error cargando unidades de medida:', error);
            this.unidadesMedida = [];
        }
    },
 
    loadTipos() {
        try {
            const tiposData = document.getElementById('tipos-data');
            if (tiposData) {
                this.tipos = JSON.parse(tiposData.textContent);
                console.log('📊 Tipos cargados:', this.tipos);
            }
        } catch (error) {
            console.error('Error cargando tipos:', error);
            this.tipos = {};
        }
    },
 
    getFormAction() {
        const baseUrl = window.location.origin;
        const action = this.isEditing 
            ? `${baseUrl}/unidades-medida/${this.editingUnidadId}` 
            : `${baseUrl}/unidades-medida`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },
 
    isFormValid() {
        const nombreValid = this.formData.unidad_nombre.trim().length > 0 && 
                           this.formData.unidad_nombre.trim().length <= 50;
        const abreviacionValid = this.formData.unidad_abreviacion.trim().length > 0 && 
                                this.formData.unidad_abreviacion.trim().length <= 10;
        const tipoValid = this.formData.unidad_tipo !== '' && 
                         ['longitud', 'peso', 'volumen', 'otro'].includes(this.formData.unidad_tipo);
        const situacionValid = this.formData.unidad_situacion !== '' && 
                              ['0', '1'].includes(this.formData.unidad_situacion.toString());
 
        const isValid = nombreValid && abreviacionValid && tipoValid && situacionValid;
        console.log('✅ Validación form:', { nombreValid, abreviacionValid, tipoValid, situacionValid, isValid });
        return isValid;
    },
 
    validateForm() {
        this.isFormValid();
    },
 
    openCreateModal() {
        console.log('➕ Abriendo modal para crear unidad de medida');
        this.isEditing = false;
        this.editingUnidadId = null;
        this.resetFormData();
        this.showModal = true;
    },
 
    editUnidad(unidadId) {
        console.log('✏️ Editando unidad de medida con ID:', unidadId);
        const unidad = this.unidadesMedida.find(u => u.unidad_id === unidadId);
        if (unidad) {
            this.isEditing = true;
            this.editingUnidadId = unidadId;
            this.formData = {
                unidad_nombre: unidad.unidad_nombre,
                unidad_abreviacion: unidad.unidad_abreviacion,
                unidad_tipo: unidad.unidad_tipo,
                unidad_situacion: unidad.unidad_situacion.toString()
            };
            this.showModal = true;
        } else {
            console.error('❌ Unidad de medida no encontrada:', unidadId);
            this.showSweetAlert('error', 'Error', 'Unidad de medida no encontrada');
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
            formData.append('unidad_nombre', this.formData.unidad_nombre.trim());
            formData.append('unidad_abreviacion', this.formData.unidad_abreviacion.trim());
            formData.append('unidad_tipo', this.formData.unidad_tipo);
            formData.append('unidad_situacion', this.formData.unidad_situacion);
            
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
                    (this.isEditing ? 'Unidad de medida actualizada correctamente' : 'Unidad de medida creada correctamente'));
                this.closeModal();
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
            unidad_nombre: '',
            unidad_abreviacion: '',
            unidad_tipo: '',
            unidad_situacion: ''
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
 
    deleteUnidad(unidadId) {
        const unidad = this.unidadesMedida.find(u => u.unidad_id === unidadId);
        if (!unidad) return;
 
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar la unidad de medida "${unidad.unidad_nombre}"?`,
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
                this.submitDeleteForm(unidadId);
            }
        });
    },
 
    submitDeleteForm(unidadId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/unidades-medida/${unidadId}`;
        
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
 
    showUnidad(unidadId) {
        const unidad = this.unidadesMedida.find(u => u.unidad_id === unidadId);
        if (!unidad) return false;
 
        // Filtro por término de búsqueda
        if (this.searchTerm && 
            !unidad.unidad_nombre.toLowerCase().includes(this.searchTerm.toLowerCase()) &&
            !unidad.unidad_abreviacion.toLowerCase().includes(this.searchTerm.toLowerCase())) {
            return false;
        }
 
        // Filtro por tipo
        if (this.tipoFilter !== '' && unidad.unidad_tipo !== this.tipoFilter) {
            return false;
        }
 
        // Filtro por estado
        if (this.statusFilter !== '' && unidad.unidad_situacion.toString() !== this.statusFilter) {
            return false;
        }
 
        return true;
    },
 
    filterUnidades() {
        // El filtrado se hace en showUnidad()
    },
 
    clearFilters() {
        this.searchTerm = '';
        this.tipoFilter = '';
        this.statusFilter = '';
    }
 });