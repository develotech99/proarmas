window.empresasImportacionManager = () => ({
   
    // Estados
    showModal: false,
    showDebug: true,
    isEditing: false,
    editingEmpresaId: null,
    searchTerm: '',
    paisFilter: '',
    statusFilter: '',
    isSubmitting: false,
    
    // Form data
    formData: {
        empresaimp_descripcion: '',
        empresaimp_pais: '',
        empresaimp_situacion: ''
    },
    
    // Los datos se pasarán desde la vista
    empresas: [],
    paises: [],
    filteredEmpresas: [],
 
    init() {
        console.log('🚀 empresasImportacionManager inicializado');
        this.loadData();
        this.filterEmpresas();
        console.log('📊 Total empresas cargadas:', this.empresas.length);
        console.log('🌍 Total países cargados:', this.paises.length);
    },
 
    loadData() {
        try {
            const empresasData = document.getElementById('empresas-importacion-data');
            const paisesData = document.getElementById('paises-data');
            
            if (empresasData) {
                this.empresas = JSON.parse(empresasData.textContent);
                console.log('📊 Empresas cargadas desde script:', this.empresas.length);
            }
            
            if (paisesData) {
                this.paises = JSON.parse(paisesData.textContent);
                console.log('🌍 Países cargados desde script:', this.paises.length);
            }
        } catch (error) {
            console.error('Error cargando datos:', error);
            this.empresas = [];
            this.paises = [];
        }
    },
 
    getFormAction() {
        const baseUrl = window.location.origin;
        const action = this.isEditing 
            ? `${baseUrl}/empresas-importacion/${this.editingEmpresaId}` 
            : `${baseUrl}/empresas-importacion`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },
 
    isFormValid() {
        const descripcionValid = this.formData.empresaimp_descripcion.trim().length > 0 && 
                                this.formData.empresaimp_descripcion.trim().length <= 50;
        const paisValid = this.formData.empresaimp_pais !== '';
        const situacionValid = this.formData.empresaimp_situacion !== '' && 
                              ['0', '1'].includes(this.formData.empresaimp_situacion.toString());
 
        const isValid = descripcionValid && paisValid && situacionValid;
        console.log('✅ Validación form:', { descripcionValid, paisValid, situacionValid, isValid });
        return isValid;
    },
 
    validateForm() {
        this.isFormValid();
    },
 
    openCreateModal() {
        console.log('➕ Abriendo modal para crear empresa');
        this.isEditing = false;
        this.editingEmpresaId = null;
        this.resetFormData();
        this.showModal = true;
    },
 
    editEmpresa(empresaId) {
        console.log('✏️ Editando empresa con ID:', empresaId);
        const empresa = this.empresas.find(e => e.empresaimp_id === empresaId);
        if (empresa) {
            this.isEditing = true;
            this.editingEmpresaId = empresaId;
            this.formData = {
                empresaimp_descripcion: empresa.empresaimp_descripcion,
                empresaimp_pais: empresa.empresaimp_pais.toString(),
                empresaimp_situacion: empresa.empresaimp_situacion.toString()
            };
            this.showModal = true;
        } else {
            console.error('❌ Empresa no encontrada:', empresaId);
            this.showSweetAlert('error', 'Error', 'Empresa no encontrada');
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
            formData.append('empresaimp_descripcion', this.formData.empresaimp_descripcion.trim());
            formData.append('empresaimp_pais', this.formData.empresaimp_pais);
            formData.append('empresaimp_situacion', this.formData.empresaimp_situacion);
            
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
                    (this.isEditing ? 'Empresa actualizada correctamente' : 'Empresa creada correctamente'));
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
            empresaimp_descripcion: '',
            empresaimp_pais: '',
            empresaimp_situacion: ''
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
 
    deleteEmpresa(empresaId) {
        const empresa = this.empresas.find(e => e.empresaimp_id === empresaId);
        if (!empresa) return;
 
        Swal.fire({
            title: '¿Confirmar eliminación?',
            html: `¿Deseas eliminar la empresa <br><strong>"${empresa.empresaimp_descripcion}"</strong>?<br><br><small class="text-gray-500">Esta acción no se puede deshacer</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            allowOutsideClick: false,
            allowEscapeKey: true,
            customClass: {
                popup: 'dark:bg-gray-800 dark:text-gray-100',
                title: 'dark:text-gray-100',
                content: 'dark:text-gray-300',
                confirmButton: 'swal2-confirm swal2-styled',
                cancelButton: 'swal2-cancel swal2-styled'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading inmediatamente
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espere',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                this.submitDeleteForm(empresaId);
            }
        });
    },
 
    submitDeleteForm(empresaId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/empresas-importacion/${empresaId}`;
        
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
 
    filterEmpresas() {
        this.filteredEmpresas = this.empresas.filter(empresa => {
            // Filtro por término de búsqueda
            const matchesSearch = !this.searchTerm || 
                empresa.empresaimp_descripcion.toLowerCase().includes(this.searchTerm.toLowerCase());
            
            // Filtro por país
            const matchesPais = !this.paisFilter || 
                empresa.empresaimp_pais.toString() === this.paisFilter;
            
            // Filtro por estado
            const matchesStatus = this.statusFilter === '' || 
                empresa.empresaimp_situacion.toString() === this.statusFilter;
            
            return matchesSearch && matchesPais && matchesStatus;
        });
        
        console.log('🔍 Filtrado aplicado:', {
            total: this.empresas.length,
            filtradas: this.filteredEmpresas.length,
            searchTerm: this.searchTerm,
            paisFilter: this.paisFilter,
            statusFilter: this.statusFilter
        });
    },
 
    clearFilters() {
        this.searchTerm = '';
        this.paisFilter = '';
        this.statusFilter = '';
        this.filterEmpresas();
    },
    
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } catch (error) {
            return 'N/A';
        }
    }
 });