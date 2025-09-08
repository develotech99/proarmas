function licenciasManager() {
    return {
        // Estados del componente
        showModal: false,
        isEditing: false,
        isSubmitting: false,
        currentLicencia: null,
        
        // Datos principales
        licenciasData: [],
        filteredLicencias: [],
        empresas: [],
        clases: [],
        marcas: [],
        modelos: [],
        calibres: [],
        
        // Filtros
        searchTerm: '',
        empresaFilter: '',
        claseFilter: '',
        statusFilter: '',
        yearFilter: '',
        
        // Datos del formulario
        formData: {
            lipaimp_poliza: '',
            lipaimp_descripcion: '',
            lipaimp_empresa: '',
            lipaimp_clase: '',
            lipaimp_marca: '',
            lipaimp_modelo: '',
            lipaimp_calibre: '',
            lipaimp_fecha_vencimiento: '',
            lipaimp_situacion: '1'
        },

        // Inicialización
        init() {
            this.loadData();
            this.filteredLicencias = [...this.licenciasData];
        },

        // Cargar datos desde scripts JSON
        loadData() {
            try {
                // Cargar licencias
                const licenciasScript = document.getElementById('licencias-data');
                if (licenciasScript) {
                    this.licenciasData = JSON.parse(licenciasScript.textContent);
                }

                // Cargar empresas
                const empresasScript = document.getElementById('empresas-data');
                if (empresasScript) {
                    this.empresas = JSON.parse(empresasScript.textContent);
                }

                // Cargar clases
                const clasesScript = document.getElementById('clases-data');
                if (clasesScript) {
                    this.clases = JSON.parse(clasesScript.textContent);
                }

                // Cargar marcas
                const marcasScript = document.getElementById('marcas-data');
                if (marcasScript) {
                    this.marcas = JSON.parse(marcasScript.textContent);
                }

                // Cargar modelos
                const modelosScript = document.getElementById('modelos-data');
                if (modelosScript) {
                    this.modelos = JSON.parse(modelosScript.textContent);
                }

                // Cargar calibres
                const calibresScript = document.getElementById('calibres-data');
                if (calibresScript) {
                    this.calibres = JSON.parse(calibresScript.textContent);
                }

            } catch (error) {
                console.error('Error cargando datos:', error);
            }
        },

        // Gestión del modal
        openCreateModal() {
            this.resetForm();
            this.isEditing = false;
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.resetForm();
        },

        // Gestión del formulario
        resetForm() {
            this.formData = {
                lipaimp_poliza: '',
                lipaimp_descripcion: '',
                lipaimp_empresa: '',
                lipaimp_clase: '',
                lipaimp_marca: '',
                lipaimp_modelo: '',
                lipaimp_calibre: '',
                lipaimp_fecha_vencimiento: '',
                lipaimp_situacion: '1'
            };
            this.currentLicencia = null;
            this.isSubmitting = false;
        },

        validateForm() {
            // Validación básica
            return this.formData.lipaimp_empresa && this.formData.lipaimp_situacion !== '';
        },

        isFormValid() {
            return this.validateForm();
        },

        // Envío del formulario
        async handleFormSubmit(event) {
            event.preventDefault();
            
            if (!this.isFormValid()) {
                this.showNotification('Por favor complete los campos requeridos', 'error');
                return;
            }

            this.isSubmitting = true;

            try {
                const url = this.isEditing 
                    ? `/licencias-importacion/${this.currentLicencia.lipaimp_id}`
                    : '/licencias-importacion';
                
                const method = this.isEditing ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.formData)
                });

                const result = await response.json();

                if (response.ok) {
                    this.showNotification(
                        this.isEditing ? 'Licencia actualizada exitosamente' : 'Licencia creada exitosamente',
                        'success'
                    );
                    this.closeModal();
                    window.location.reload(); // Recargar para mostrar cambios
                } else {
                    throw new Error(result.message || 'Error al procesar la solicitud');
                }

            } catch (error) {
                console.error('Error:', error);
                this.showNotification(error.message || 'Error al procesar la solicitud', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        // Editar licencia
        editLicencia(licenciaId) {
            const licencia = this.licenciasData.find(l => l.lipaimp_id === licenciaId);
            if (licencia) {
                this.currentLicencia = licencia;
                this.formData = {
                    lipaimp_poliza: licencia.lipaimp_poliza || '',
                    lipaimp_descripcion: licencia.lipaimp_descripcion || '',
                    lipaimp_empresa: licencia.lipaimp_empresa || '',
                    lipaimp_clase: licencia.lipaimp_clase || '',
                    lipaimp_marca: licencia.lipaimp_marca || '',
                    lipaimp_modelo: licencia.lipaimp_modelo || '',
                    lipaimp_calibre: licencia.lipaimp_calibre || '',
                    lipaimp_fecha_vencimiento: licencia.lipaimp_fecha_vencimiento || '',
                    lipaimp_situacion: licencia.lipaimp_situacion.toString()
                };
                this.isEditing = true;
                this.showModal = true;
            }
        },

        // Eliminar licencia
        async deleteLicencia(licenciaId) {
            if (!confirm('¿Está seguro de que desea eliminar esta licencia de importación?')) {
                return;
            }

            try {
                const response = await fetch(`/licencias-importacion/${licenciaId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    this.showNotification('Licencia eliminada exitosamente', 'success');
                    window.location.reload();
                } else {
                    throw new Error(result.message || 'Error al eliminar la licencia');
                }

            } catch (error) {
                console.error('Error:', error);
                this.showNotification(error.message || 'Error al eliminar la licencia', 'error');
            }
        },

        // Sistema de filtrado
        filterLicencias() {
            let filtered = [...this.licenciasData];

            // Filtro por término de búsqueda (descripción)
            if (this.searchTerm) {
                const term = this.searchTerm.toLowerCase();
                filtered = filtered.filter(licencia => 
                    (licencia.lipaimp_descripcion || '').toLowerCase().includes(term) ||
                    (licencia.lipaimp_poliza || '').toString().includes(term)
                );
            }

            // Filtro por empresa
            if (this.empresaFilter) {
                filtered = filtered.filter(licencia => 
                    licencia.lipaimp_empresa == this.empresaFilter
                );
            }

            // Filtro por clase
            if (this.claseFilter) {
                filtered = filtered.filter(licencia => 
                    licencia.lipaimp_clase == this.claseFilter
                );
            }

            // Filtro por estado
            if (this.statusFilter) {
                if (this.statusFilter === 'vencidas') {
                    const today = new Date();
                    filtered = filtered.filter(licencia => {
                        if (!licencia.lipaimp_fecha_vencimiento) return false;
                        const vencimiento = new Date(licencia.lipaimp_fecha_vencimiento);
                        return vencimiento < today && licencia.lipaimp_situacion == 1;
                    });
                } else if (this.statusFilter === 'por_vencer') {
                    const today = new Date();
                    const futureDate = new Date();
                    futureDate.setDate(today.getDate() + 30);
                    
                    filtered = filtered.filter(licencia => {
                        if (!licencia.lipaimp_fecha_vencimiento) return false;
                        const vencimiento = new Date(licencia.lipaimp_fecha_vencimiento);
                        return vencimiento >= today && vencimiento <= futureDate && licencia.lipaimp_situacion == 1;
                    });
                } else {
                    filtered = filtered.filter(licencia => 
                        licencia.lipaimp_situacion == this.statusFilter
                    );
                }
            }

            // Filtro por año
            if (this.yearFilter) {
                filtered = filtered.filter(licencia => {
                    if (!licencia.created_at && !licencia.lipaimp_fecha_vencimiento) return false;
                    const fecha = licencia.created_at || licencia.lipaimp_fecha_vencimiento;
                    const year = new Date(fecha).getFullYear();
                    return year == this.yearFilter;
                });
            }

            this.filteredLicencias = filtered;
        },

        // Limpiar filtros
        clearFilters() {
            this.searchTerm = '';
            this.empresaFilter = '';
            this.claseFilter = '';
            this.statusFilter = '';
            this.yearFilter = '';
            this.filteredLicencias = [...this.licenciasData];
        },

        // Verificar si mostrar licencia (para x-show en el template)
        showLicencia(licenciaId) {
            return this.filteredLicencias.some(l => l.lipaimp_id === licenciaId);
        },

        // Sistema de notificaciones
        showNotification(message, type = 'info') {
            // Crear elemento de notificación
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transition-all duration-300 transform translate-x-full`;
            
            // Estilos según el tipo
            if (type === 'success') {
                notification.classList.add('bg-green-50', 'text-green-800', 'border', 'border-green-200');
            } else if (type === 'error') {
                notification.classList.add('bg-red-50', 'text-red-800', 'border', 'border-red-200');
            } else {
                notification.classList.add('bg-blue-50', 'text-blue-800', 'border', 'border-blue-200');
            }

            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${type === 'success' ? 
                            '<svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>' :
                          type === 'error' ?
                            '<svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>' :
                            '<svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
                        }
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button class="inline-flex text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(notification);

            // Animar entrada
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Remover automáticamente después de 5 segundos
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    };
}