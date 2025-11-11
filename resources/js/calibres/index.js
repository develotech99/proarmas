window.calibresManager = () => ({
   
    // Estados
    showModal: false,
    showDebug: true,
    isEditing: false,
    editingCalibreId: null,
    searchTerm: '',
    unidadFilter: '',
    equivalenciaFilter: '',
    statusFilter: '',
    isSubmitting: false,
    
    // Form data
    formData: {
        calibre_nombre: '',
        calibre_unidad_id: '',
        calibre_equivalente_mm: '',
        calibre_situacion: ''
    },
    
    // Los datos se pasarán desde la vista
    calibres: [],
    unidadesMedida: [],
 
    init() {
        console.log('🚀 calibresManager inicializado');
        this.loadCalibres();
        this.loadUnidadesMedida();
        console.log('📊 Total calibres cargados:', this.calibres.length);
        console.log('📊 Total unidades de medida cargadas:', this.unidadesMedida.length);
    },
 
    loadCalibres() {
        try {
            const calibresData = document.getElementById('calibres-data');
            if (calibresData) {
                this.calibres = JSON.parse(calibresData.textContent);
                console.log('📊 Calibres cargados desde script:', this.calibres.length);
            }
        } catch (error) {
            console.error('Error cargando calibres:', error);
            this.calibres = [];
        }
    },
 
    loadUnidadesMedida() {
        try {
            const unidadesMedidaData = document.getElementById('unidades-medida-data');
            if (unidadesMedidaData) {
                this.unidadesMedida = JSON.parse(unidadesMedidaData.textContent);
                console.log('📊 Unidades de medida cargadas:', this.unidadesMedida.length);
            }
        } catch (error) {
            console.error('Error cargando unidades de medida:', error);
            this.unidadesMedida = [];
        }
    },
 
    getFormAction() {
        const baseUrl = window.location.origin;
        const action = this.isEditing 
            ? `${baseUrl}/calibres/${this.editingCalibreId}` 
            : `${baseUrl}/calibres`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },
 
    isFormValid() {
        const nombreValid = this.formData.calibre_nombre.trim().length > 0 && 
                           this.formData.calibre_nombre.trim().length <= 20;
        const unidadValid = this.formData.calibre_unidad_id !== '' && 
                           this.formData.calibre_unidad_id > 0;
        const equivalenteValid = this.formData.calibre_equivalente_mm === '' || 
                                (this.formData.calibre_equivalente_mm >= 0 && this.formData.calibre_equivalente_mm <= 9999.99);
        const situacionValid = this.formData.calibre_situacion !== '' && 
                              ['0', '1'].includes(this.formData.calibre_situacion.toString());
 
        const isValid = nombreValid && unidadValid && equivalenteValid && situacionValid;
        console.log('✅ Validación form:', { nombreValid, unidadValid, equivalenteValid, situacionValid, isValid });
        return isValid;
    },
 
    validateForm() {
        this.isFormValid();
    },
 
    openCreateModal() {
        console.log('➕ Abriendo modal para crear calibre');
        this.isEditing = false;
        this.editingCalibreId = null;
        this.resetFormData();
        this.showModal = true;
    },
 
    editCalibre(calibreId) {
        console.log('✏️ Editando calibre con ID:', calibreId);
        const calibre = this.calibres.find(c => c.calibre_id === calibreId);
        if (calibre) {
            this.isEditing = true;
            this.editingCalibreId = calibreId;
            this.formData = {
                calibre_nombre: calibre.calibre_nombre,
                calibre_unidad_id: calibre.calibre_unidad_id.toString(),
                calibre_equivalente_mm: calibre.calibre_equivalente_mm || '',
                calibre_situacion: calibre.calibre_situacion.toString()
            };
            this.showModal = true;
        } else {
            console.error('❌ Calibre no encontrado:', calibreId);
            this.showSweetAlert('error', 'Error', 'Calibre no encontrado');
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
            formData.append('calibre_nombre', this.formData.calibre_nombre.trim());
            formData.append('calibre_unidad_id', this.formData.calibre_unidad_id);
            if (this.formData.calibre_equivalente_mm) {
                formData.append('calibre_equivalente_mm', this.formData.calibre_equivalente_mm);
            }
            formData.append('calibre_situacion', this.formData.calibre_situacion);
            
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
                    (this.isEditing ? 'Calibre actualizado correctamente' : 'Calibre creado correctamente'));
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
            calibre_nombre: '',
            calibre_unidad_id: '',
            calibre_equivalente_mm: '',
            calibre_situacion: ''
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
 
    deleteCalibre(calibreId) {
        const calibre = this.calibres.find(c => c.calibre_id === calibreId);
        if (!calibre) return;
 
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el calibre "${calibre.calibre_nombre}"?`,
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
                this.submitDeleteForm(calibreId);
            }
        });
    },
 
    submitDeleteForm(calibreId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/calibres/${calibreId}`;
        
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
 
    showCalibre(calibreId) {
        const calibre = this.calibres.find(c => c.calibre_id === calibreId);
        if (!calibre) return false;
 
        // Filtro por término de búsqueda
        if (this.searchTerm && !calibre.calibre_nombre.toLowerCase().includes(this.searchTerm.toLowerCase())) {
            return false;
        }
 
        // Filtro por unidad
        if (this.unidadFilter !== '' && calibre.calibre_unidad_id.toString() !== this.unidadFilter) {
            return false;
        }
 
        // Filtro por equivalencia
        if (this.equivalenciaFilter === 'con' && !calibre.calibre_equivalente_mm) {
            return false;
        }
        if (this.equivalenciaFilter === 'sin' && calibre.calibre_equivalente_mm) {
            return false;
        }
 
        // Filtro por estado
        if (this.statusFilter !== '' && calibre.calibre_situacion.toString() !== this.statusFilter) {
            return false;
        }
 
        return true;
    },
 
    filterCalibres() {
        // El filtrado se hace en showCalibre()
    },
 
    clearFilters() {
        this.searchTerm = '';
        this.unidadFilter = '';
        this.equivalenciaFilter = '';
        this.statusFilter = '';
    }
 });