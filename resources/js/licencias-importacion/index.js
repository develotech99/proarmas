window.licenciasImportacionManager = () => ({
   
    // Estados para modales
    showLicenciaModal: false,
    showViewModal: false,
    showArmaModal: false,
    
    // Estados de edición
    isEditingLicencia: false,
    isEditingArma: false,
    editingLicenciaId: null,
    editingArmaId: null,
    viewingLicencia: null,
    
    // Estados de filtros
    searchTerm: '',
    empresaFilter: '',
    statusFilter: '',
    
    // Estados de envío
    isSubmittingLicencia: false,
    isSubmittingArma: false,
    
    // Form data para licencias
    licenciaFormData: {
        lipaimp_poliza: '',
        lipaimp_descripcion: '',
        lipaimp_empresa: '',
        lipaimp_fecha_vencimiento: '',
        lipaimp_situacion: ''
    },
    
    // Form data para armas
    armaFormData: {
        arma_licencia_id: '',
        arma_clase_id: '',
        arma_marca_id: '',
        arma_modelo_id: '',
        arma_calibre_id: '',
        arma_cantidad: 1,
        arma_situacion: ''
    },
    
    // Datos cargados desde el servidor
    licencias: [],
    empresas: [],
    clases: [],
    marcas: [],
    modelos: [],
    calibres: [],
 
    init() {
        console.log('🚀 licenciasImportacionManager inicializado');
        this.loadData();
        console.log('📊 Datos cargados:', {
            licencias: this.licencias.length,
            empresas: this.empresas.length,
            clases: this.clases.length,
            marcas: this.marcas.length,
            modelos: this.modelos.length,
            calibres: this.calibres.length
        });
    },
 
    loadData() {
        try {
            // Cargar licencias
            const licenciasData = document.getElementById('licencias-data');
            if (licenciasData) {
                this.licencias = JSON.parse(licenciasData.textContent);
            }
 
            // Cargar empresas
            const empresasData = document.getElementById('empresas-data');
            if (empresasData) {
                this.empresas = JSON.parse(empresasData.textContent);
            }
 
            // Cargar clases
            const clasesData = document.getElementById('clases-data');
            if (clasesData) {
                this.clases = JSON.parse(clasesData.textContent);
            }
 
            // Cargar marcas
            const marcasData = document.getElementById('marcas-data');
            if (marcasData) {
                this.marcas = JSON.parse(marcasData.textContent);
            }
 
            // Cargar modelos
            const modelosData = document.getElementById('modelos-data');
            if (modelosData) {
                this.modelos = JSON.parse(modelosData.textContent);
            }
 
            // Cargar calibres
            const calibresData = document.getElementById('calibres-data');
            if (calibresData) {
                this.calibres = JSON.parse(calibresData.textContent);
            }
        } catch (error) {
            console.error('Error cargando datos:', error);
        }
    },
 
    // ==========================================
    // MÉTODOS PARA LICENCIAS
    // ==========================================
 
    openCreateLicenciaModal() {
        console.log('➕ Abriendo modal para crear licencia');
        this.isEditingLicencia = false;
        this.editingLicenciaId = null;
        this.resetLicenciaFormData();
        this.showLicenciaModal = true;
    },
 
    editLicencia(licenciaId) {
        console.log('✏️ Editando licencia con ID:', licenciaId);
        const licencia = this.licencias.find(l => l.lipaimp_id === licenciaId);
        if (licencia) {
            this.isEditingLicencia = true;
            this.editingLicenciaId = licenciaId;
            this.licenciaFormData = {
                lipaimp_poliza: licencia.lipaimp_poliza || '',
                lipaimp_descripcion: licencia.lipaimp_descripcion,
                lipaimp_empresa: licencia.lipaimp_empresa.toString(),
                lipaimp_fecha_vencimiento: licencia.lipaimp_fecha_vencimiento || '',
                lipaimp_situacion: licencia.lipaimp_situacion.toString()
            };
            this.showLicenciaModal = true;
        }
    },
 
    async viewLicencia(licenciaId) {
        console.log('👁️ Viendo licencia con ID:', licenciaId);
        try {
            const response = await fetch(`/licencias-importacion/${licenciaId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
 
            if (response.ok) {
                const data = await response.json();
                this.viewingLicencia = data.licencia || data;
                this.showViewModal = true;
            } else {
                this.showSweetAlert('error', 'Error', 'No se pudo cargar la información de la licencia');
            }
        } catch (error) {
            console.error('Error al cargar licencia:', error);
            this.showSweetAlert('error', 'Error', 'Error de conexión');
        }
    },
 
    async handleLicenciaFormSubmit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de licencia...');
        
        this.isSubmittingLicencia = true;
        
        if (!this.isLicenciaFormValid()) {
            console.error('❌ Formulario de licencia inválido');
            this.showSweetAlert('error', 'Error de validación', 'Por favor complete todos los campos correctamente');
            this.isSubmittingLicencia = false;
            return false;
        }
 
        try {
            const formData = new FormData();
            formData.append('lipaimp_poliza', this.licenciaFormData.lipaimp_poliza || '');
            formData.append('lipaimp_descripcion', this.licenciaFormData.lipaimp_descripcion.trim());
            formData.append('lipaimp_empresa', this.licenciaFormData.lipaimp_empresa);
            formData.append('lipaimp_fecha_vencimiento', this.licenciaFormData.lipaimp_fecha_vencimiento || '');
            formData.append('lipaimp_situacion', this.licenciaFormData.lipaimp_situacion);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            if (this.isEditingLicencia) {
                formData.append('_method', 'PUT');
            }
 
            const url = this.isEditingLicencia 
                ? `/licencias-importacion/${this.editingLicenciaId}` 
                : '/licencias-importacion';
 
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
 
            if (response.ok) {
                const responseData = await response.json();
                this.showSweetAlert('success', 'Éxito', responseData.message || 
                    (this.isEditingLicencia ? 'Licencia actualizada correctamente' : 'Licencia creada correctamente'));
                this.closeLicenciaModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorData = await response.json();
                this.showSweetAlert('error', 'Error', errorData.message || 'Error al procesar la solicitud');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showSweetAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.isSubmittingLicencia = false;
        }
    },
 
    deleteLicencia(licenciaId) {
        const licencia = this.licencias.find(l => l.lipaimp_id === licenciaId);
        if (!licencia) return;
 
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar la licencia "${licencia.lipaimp_descripcion}"?`,
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
                this.submitDeleteLicenciaForm(licenciaId);
            }
        });
    },
 
    submitDeleteLicenciaForm(licenciaId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/licencias-importacion/${licenciaId}`;
        
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
 
    closeLicenciaModal() {
        this.showLicenciaModal = false;
        this.isSubmittingLicencia = false;
        this.resetLicenciaFormData();
    },
 
    resetLicenciaFormData() {
        this.licenciaFormData = {
            lipaimp_poliza: '',
            lipaimp_descripcion: '',
            lipaimp_empresa: '',
            lipaimp_fecha_vencimiento: '',
            lipaimp_situacion: ''
        };
    },
 
    isLicenciaFormValid() {
        const descripcionValid = this.licenciaFormData.lipaimp_descripcion.trim().length > 0;
        const empresaValid = this.licenciaFormData.lipaimp_empresa !== '' && this.licenciaFormData.lipaimp_empresa > 0;
        const situacionValid = ['0', '1'].includes(this.licenciaFormData.lipaimp_situacion.toString());
 
        return descripcionValid && empresaValid && situacionValid;
    },
 
    validateLicenciaForm() {
        this.isLicenciaFormValid();
    },
 
    // ==========================================
    // MÉTODOS PARA ARMAS
    // ==========================================
 
    openAddArmaModal() {
        console.log('➕ Abriendo modal para agregar arma');
        this.isEditingArma = false;
        this.editingArmaId = null;
        this.resetArmaFormData();
        this.armaFormData.arma_licencia_id = this.viewingLicencia.lipaimp_id;
        this.showArmaModal = true;
    },
 
    editArma(arma) {
        console.log('✏️ Editando arma:', arma);
        this.isEditingArma = true;
        this.editingArmaId = arma.arma_id;
        this.armaFormData = {
            arma_licencia_id: arma.arma_licencia_id,
            arma_clase_id: arma.arma_clase_id.toString(),
            arma_marca_id: arma.arma_marca_id.toString(),
            arma_modelo_id: arma.arma_modelo_id.toString(),
            arma_calibre_id: arma.arma_calibre_id.toString(),
            arma_cantidad: arma.arma_cantidad,
            arma_situacion: arma.arma_situacion.toString()
        };
        this.showArmaModal = true;
    },
 
    async handleArmaFormSubmit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario de arma...');
        
        this.isSubmittingArma = true;
        
        if (!this.isArmaFormValid()) {
            console.error('❌ Formulario de arma inválido');
            this.showSweetAlert('error', 'Error de validación', 'Por favor complete todos los campos correctamente');
            this.isSubmittingArma = false;
            return false;
        }
 
        try {
            const formData = new FormData();
            Object.keys(this.armaFormData).forEach(key => {
                formData.append(key, this.armaFormData[key]);
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            if (this.isEditingArma) {
                formData.append('_method', 'PUT');
            }
 
            const url = this.isEditingArma 
                ? `/licencias-importacion/armas/${this.editingArmaId}` 
                : '/licencias-importacion/armas';
 
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
 
            if (response.ok) {
                const responseData = await response.json();
                this.showSweetAlert('success', 'Éxito', responseData.message || 
                    (this.isEditingArma ? 'Arma actualizada correctamente' : 'Arma agregada correctamente'));
                this.closeArmaModal();
                // Recargar la vista de la licencia
                setTimeout(() => this.viewLicencia(this.viewingLicencia.lipaimp_id), 1000);
            } else {
                const errorData = await response.json();
                this.showSweetAlert('error', 'Error', errorData.message || 'Error al procesar la solicitud');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showSweetAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.isSubmittingArma = false;
        }
    },

    async deleteArma(armaId) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar esta arma de la licencia?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/licencias-importacion/armas/${armaId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        this.showSweetAlert('success', 'Éxito', 'Arma eliminada correctamente');
                        // Recargar la vista de la licencia
                        setTimeout(() => this.viewLicencia(this.viewingLicencia.lipaimp_id), 1000);
                    } else {
                        this.showSweetAlert('error', 'Error', 'Error al eliminar el arma');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showSweetAlert('error', 'Error', 'Error de conexión');
                }
            }
        });
    },
 
    closeArmaModal() {
        this.showArmaModal = false;
        this.isSubmittingArma = false;
        this.resetArmaFormData();
    },
 
    resetArmaFormData() {
        this.armaFormData = {
            arma_licencia_id: '',
            arma_clase_id: '',
            arma_marca_id: '',
            arma_modelo_id: '',
            arma_calibre_id: '',
            arma_cantidad: 1,
            arma_situacion: ''
        };
    },
 
    isArmaFormValid() {
        const claseValid = this.armaFormData.arma_clase_id !== '' && this.armaFormData.arma_clase_id > 0;
        const marcaValid = this.armaFormData.arma_marca_id !== '' && this.armaFormData.arma_marca_id > 0;
        const modeloValid = this.armaFormData.arma_modelo_id !== '' && this.armaFormData.arma_modelo_id > 0;
        const calibreValid = this.armaFormData.arma_calibre_id !== '' && this.armaFormData.arma_calibre_id > 0;
        const cantidadValid = this.armaFormData.arma_cantidad >= 1;
        const situacionValid = ['0', '1'].includes(this.armaFormData.arma_situacion.toString());

        return claseValid && marcaValid && modeloValid && calibreValid && cantidadValid && situacionValid;
    },
 
    validateArmaForm() {
        this.isArmaFormValid();
    },

    // ==========================================
    // MÉTODOS DE FILTROS Y UTILIDADES
    // ==========================================

    closeViewModal() {
        this.showViewModal = false;
        this.viewingLicencia = null;
    },

    showLicencia(licenciaId) {
        const licencia = this.licencias.find(l => l.lipaimp_id === licenciaId);
        if (!licencia) return false;

        let showLicencia = true;

        // Filtro de búsqueda
        if (this.searchTerm.trim() !== '') {
            const searchLower = this.searchTerm.toLowerCase();
            const descripcion = licencia.lipaimp_descripcion.toLowerCase();
            const poliza = (licencia.lipaimp_poliza || '').toString().toLowerCase();
            
            showLicencia = showLicencia && 
                (descripcion.includes(searchLower) || poliza.includes(searchLower));
        }

        // Filtro de empresa
        if (this.empresaFilter !== '') {
            showLicencia = showLicencia && 
                licencia.lipaimp_empresa.toString() === this.empresaFilter;
        }

        // Filtro de estado
        if (this.statusFilter !== '') {
            if (this.statusFilter === 'vencidas') {
                // Lógica para licencias vencidas
                const fechaVencimiento = licencia.lipaimp_fecha_vencimiento;
                const estaVencida = fechaVencimiento && new Date(fechaVencimiento) < new Date();
                showLicencia = showLicencia && estaVencida && licencia.lipaimp_situacion == 1;
            } else {
                showLicencia = showLicencia && 
                    licencia.lipaimp_situacion.toString() === this.statusFilter;
            }
        }

        return showLicencia;
    },

    filterLicencias() {
        // Los filtros se aplican automáticamente a través del método showLicencia
        // Este método puede ser llamado cuando sea necesario forzar un re-render
        console.log('Aplicando filtros:', {
            searchTerm: this.searchTerm,
            empresaFilter: this.empresaFilter,
            statusFilter: this.statusFilter
        });
    },

    clearFilters() {
        this.searchTerm = '';
        this.empresaFilter = '';
        this.statusFilter = '';
        console.log('Filtros limpiados');
    },

    // Método para mostrar SweetAlert
    showSweetAlert(icon, title, text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                customClass: {
                    popup: 'dark:bg-gray-800 dark:text-gray-100',
                    title: 'dark:text-gray-100',
                    content: 'dark:text-gray-300'
                }
            });
        } else {
            // Fallback si SweetAlert no está disponible
            alert(`${title}: ${text}`);
        }
    }
});