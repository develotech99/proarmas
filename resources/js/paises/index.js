window.paisesManager = () => ({
   
    // Estados
    showModal: false,
    showDebug: true,
    isEditing: false,
    editingPaisId: null,
    searchTerm: '',
    statusFilter: '',
    isSubmitting: false,
    
    // Form data
    formData: {
        pais_descripcion: '',
        pais_situacion: ''
    },
    
    // Los datos se pasarán desde la vista
    paises: [],
 
    init() {
        console.log('🚀 paisesManager inicializado');
        this.loadPaises();
        console.log('📊 Total países cargados:', this.paises.length);
    },
 
    loadPaises() {
        try {
            const paisesData = document.getElementById('paises-data');
            if (paisesData) {
                this.paises = JSON.parse(paisesData.textContent);
                console.log('📊 Países cargados desde script:', this.paises.length);
            }
        } catch (error) {
            console.error('Error cargando países:', error);
            this.paises = [];
        }
    },
 
    getFormAction() {
        const baseUrl = window.location.origin;
        const action = this.isEditing 
            ? `${baseUrl}/paises/${this.editingPaisId}` 
            : `${baseUrl}/paises`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },
 
    isFormValid() {
        const descripcionValid = this.formData.pais_descripcion.trim().length > 0 && 
                                this.formData.pais_descripcion.trim().length <= 50;
        const situacionValid = this.formData.pais_situacion !== '' && 
                              ['0', '1'].includes(this.formData.pais_situacion.toString());
 
        const isValid = descripcionValid && situacionValid;
        console.log('✅ Validación form:', { descripcionValid, situacionValid, isValid });
        return isValid;
    },
 
    validateForm() {
        this.isFormValid();
    },
 
    openCreateModal() {
        console.log('➕ Abriendo modal para crear país');
        this.isEditing = false;
        this.editingPaisId = null;
        this.resetFormData();
        this.showModal = true;
    },
 
    editPais(paisId) {
        console.log('✏️ Editando país con ID:', paisId);
        const pais = this.paises.find(p => p.pais_id === paisId);
        if (pais) {
            this.isEditing = true;
            this.editingPaisId = paisId;
            this.formData = {
                pais_descripcion: pais.pais_descripcion,
                pais_situacion: pais.pais_situacion.toString()
            };
            this.showModal = true;
        } else {
            console.error('❌ País no encontrado:', paisId);
            this.showSweetAlert('error', 'Error', 'País no encontrado');
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
            formData.append('pais_descripcion', this.formData.pais_descripcion.trim());
            formData.append('pais_situacion', this.formData.pais_situacion);
            
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
                    (this.isEditing ? 'País actualizado correctamente' : 'País creado correctamente'));
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
            pais_descripcion: '',
            pais_situacion: ''
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
 
    deletePais(paisId) {
        const pais = this.paises.find(p => p.pais_id === paisId);
        if (!pais) return;
 
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el país "${pais.pais_descripcion}"?`,
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
                this.submitDeleteForm(paisId);
            }
        });
    },
 
    submitDeleteForm(paisId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/paises/${paisId}`;
        
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
 
    showPais(paisId) {
        const pais = this.paises.find(p => p.pais_id === paisId);
        if (!pais) return false;
 
        // Filtro por término de búsqueda
        if (this.searchTerm && !pais.pais_descripcion.toLowerCase().includes(this.searchTerm.toLowerCase())) {
            return false;
        }
 
        // Filtro por estado
        if (this.statusFilter !== '' && pais.pais_situacion.toString() !== this.statusFilter) {
            return false;
        }
 
        return true;
    },
 
    filterPaises() {
        // El filtrado se hace en showPais()
    },
 
    clearFilters() {
        this.searchTerm = '';
        this.statusFilter = '';
    }
 });