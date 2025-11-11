window.empresasManager = () => ({
    
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
        empresaimp_pais: '',
        empresaimp_descripcion: '',
        empresaimp_situacion: ''
    },
    
    // Datos
    empresas: [],
    paises: [],
    filteredEmpresas: [],
    
    // Alertas
    alerts: [],
    
    init() {
        console.log('🚀 empresasManager inicializado');
        this.loadEmpresas();
        this.loadPaises();
        console.log('📊 Total empresas cargadas:', this.empresas.length);
        console.log('📊 Total países cargados:', this.paises.length);
    },
    
    loadEmpresas() {
        try {
            const empresasData = document.getElementById('empresas-data');
            if (empresasData) {
                this.empresas = JSON.parse(empresasData.textContent);
                this.filteredEmpresas = [...this.empresas];
                console.log('📊 Empresas cargadas desde script:', this.empresas.length);
            }
        } catch (error) {
            console.error('Error cargando empresas:', error);
            this.empresas = [];
            this.filteredEmpresas = [];
        }
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
            ? `${baseUrl}/proempresas/${this.editingEmpresaId}` 
            : `${baseUrl}/proempresas`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },
    
    isFormValid() {
        const paisValid = this.formData.empresaimp_pais !== '';
        const descripcionValid = this.formData.empresaimp_descripcion.trim().length <= 50;
        const situacionValid = this.formData.empresaimp_situacion !== '' && 
                              ['0', '1'].includes(this.formData.empresaimp_situacion.toString());
        
        const isValid = paisValid && descripcionValid && situacionValid;
        console.log('✅ Validación form:', { paisValid, descripcionValid, situacionValid, isValid });
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
                empresaimp_pais: empresa.empresaimp_pais.toString(),
                empresaimp_descripcion: empresa.empresaimp_descripcion || '',
                empresaimp_situacion: empresa.empresaimp_situacion.toString()
            };
            this.showModal = true;
        } else {
            console.error('❌ Empresa no encontrada:', empresaId);
            this.showAlert('Empresa no encontrada', 'error');
        }
    },
    
    async handleFormSubmit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario...');
        
        this.isSubmitting = true;
        
        if (!this.isFormValid()) {
            console.error('❌ Formulario inválido');
            this.showAlert('Por favor complete todos los campos correctamente', 'error');
            this.isSubmitting = false;
            return false;
        }
        
        try {
            const formData = new FormData();
            formData.append('empresaimp_pais', this.formData.empresaimp_pais);
            formData.append('empresaimp_descripcion', this.formData.empresaimp_descripcion.trim());
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
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                this.showAlert(data.message || 
                    (this.isEditing ? 'Empresa actualizada correctamente' : 'Empresa creada correctamente'), 'success');
                this.closeModal();
                // Recargar la página después de un breve delay
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorMessage = data.message || data.errors ? 'Por favor corrige los errores en el formulario' : 'Error al procesar la solicitud';
                this.showAlert(errorMessage, 'error');
                
                // Mostrar errores de validación específicos
                if (data.errors) {
                    console.error('Errores de validación:', data.errors);
                }
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error de conexión al procesar la solicitud', 'error');
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
            empresaimp_pais: '',
            empresaimp_descripcion: '',
            empresaimp_situacion: ''
        };
    },
    
    // Sistema de Alertas con SweetAlert
    showAlert(message, type = 'success') {
        const config = {
            title: type === 'success' ? 'Éxito' : 'Error',
            text: message,
            icon: type,
            confirmButtonColor: type === 'success' ? '#10b981' : '#dc2626',
            customClass: {
                popup: 'dark:bg-gray-800 dark:text-gray-100',
                title: 'dark:text-gray-100',
                content: 'dark:text-gray-300'
            }
        };
        
        Swal.fire(config);
    },
    
    deleteEmpresa(empresaId) {
        const empresa = this.empresas.find(e => e.empresaimp_id === empresaId);
        if (!empresa) return;
        
        if (!confirm(`¿Estás seguro de que deseas eliminar la empresa "${empresa.empresaimp_descripcion || 'Sin descripción'}"?`)) {
            return;
        }
        
        this.submitDeleteForm(empresaId);
    },
    
    async submitDeleteForm(empresaId) {
        try {
            const response = await fetch(`/proempresas/${empresaId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                this.showAlert(data.message || 'Empresa eliminada correctamente', 'success');
                // Recargar la página después de un breve delay
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorMessage = data.message || 'Error al eliminar la empresa';
                this.showAlert(errorMessage, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('Error de conexión al eliminar la empresa', 'error');
        }
    },
    
    filterEmpresas() {
        this.filteredEmpresas = this.empresas.filter(empresa => {
            const matchesSearch = !this.searchTerm || 
                (empresa.empresaimp_descripcion && 
                 empresa.empresaimp_descripcion.toLowerCase().includes(this.searchTerm.toLowerCase()));
            
            const matchesPais = !this.paisFilter || 
                empresa.empresaimp_pais == this.paisFilter;
            
            const matchesStatus = this.statusFilter === '' || 
                empresa.empresaimp_situacion == this.statusFilter;
            
            return matchesSearch && matchesPais && matchesStatus;
        });
        
        console.log('🔍 Empresas filtradas:', this.filteredEmpresas.length);
    },
    
    clearFilters() {
        this.searchTerm = '';
        this.paisFilter = '';
        this.statusFilter = '';
        this.filterEmpresas();
    },
    
    showEmpresa(empresaId) {
        return this.filteredEmpresas.some(e => e.empresaimp_id === empresaId);
    },
    
    // Utilidades
    getPaisNombre(paisId) {
        const pais = this.paises.find(p => p.pais_id == paisId);
        return pais ? pais.pais_nombre : 'Desconocido';
    },
    
    getEmpresaInitials(descripcion) {
        if (!descripcion) return 'EM';
        return descripcion.substring(0, 2).toUpperCase();
    }
});
