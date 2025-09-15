/**
 * Gestor del Sistema de Inventario para Armería
 * JavaScript puro - Laravel 12
 */
class InventarioManager {
    constructor() {
        this.productos = [];
        this.categorias = [];
        this.marcas = [];
        this.modelos = [];
        this.calibres = [];
        this.licenciaSeleccionada = null;
        this.lotePreview = '';
        this.currentProductoId = null;
        this.stats = {
            totalProductos: 0,
            stockTotal: 0,
            stockBajo: 0,
            stockAgotado: 0
        };
        this.filtros = {
            search: '',
            categoria: '',
            stock: ''
        };
        this.productoSeleccionado = null;
        
        this.init();
    }

    /**
     * Inicializar el gestor
     */
    init() {
        console.log('🚀 InventarioManager inicializado');
        this.setupEventListeners();
        this.setupFotosHandling(); 
        this.setupPreciosHandling();
        this.loadInitialData();
    }

    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Filtros
        document.getElementById('search-productos').addEventListener('input', (e) => {
            this.filtros.search = e.target.value;
            this.aplicarFiltros();
        });

        document.getElementById('filter-categoria').addEventListener('change', (e) => {
            this.filtros.categoria = e.target.value;
            this.aplicarFiltros();
        });

        document.getElementById('filter-stock').addEventListener('change', (e) => {
            this.filtros.stock = e.target.value;
            this.aplicarFiltros();
        });

        // Formularios
        document.getElementById('registro-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegistroSubmit();
        });

        document.getElementById('ingreso-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleIngresoSubmit();
        });

        // Búsqueda de productos para ingreso
        document.getElementById('buscar_producto').addEventListener('input', (e) => {
            this.buscarProductos(e.target.value);
        });

        // Cambios en categoría para cargar subcategorías
        document.getElementById('producto_categoria').addEventListener('change', (e) => {
            this.loadSubcategorias(e.target.value);
        });

        // Cambios en marca para cargar modelos
        document.getElementById('producto_marca').addEventListener('change', (e) => {
            this.loadModelos(e.target.value);
        });

        // Cerrar modales con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal('registro');
                this.closeModal('ingreso');
            }
        });

            
        // Búsqueda de licencias
        const buscarLicenciaInput = document.getElementById('buscar_licencia');
        if (buscarLicenciaInput) {
            buscarLicenciaInput.addEventListener('input', (e) => {
                this.buscarLicencias(e.target.value);
            });
        }

        // Radio buttons para generar lote  
        document.querySelectorAll('input[name="generar_lote"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.toggleLoteInput(e.target.value);
            });
        });

        // Contador de series
        const numerosSeriesInput = document.getElementById('numeros_series');
        if (numerosSeriesInput) {
            numerosSeriesInput.addEventListener('input', (e) => {
                this.contarSeries(e.target.value);
            });
        }

        // Checkbox para agregar precios
        const checkboxPrecios = document.getElementById('agregar_precios');
        if (checkboxPrecios) {
            checkboxPrecios.addEventListener('change', (e) => {
                const seccionPrecios = document.getElementById('seccion_precios');
                if (seccionPrecios) {
                    if (e.target.checked) {
                        seccionPrecios.classList.remove('hidden');
                    } else {
                        seccionPrecios.classList.add('hidden');
                    }
                }
            });
        }

    }

    /**
     * Cargar datos iniciales
     */
    async loadInitialData() {
        try {
            await Promise.all([
                this.loadProductos(),
                this.loadCategorias(),
                this.loadMarcas(),
                this.loadPaises(), 
                this.loadCalibres(),
                this.loadStats(),
                this.loadAlertas()
            ]);
        } catch (error) {
            console.error('Error cargando datos iniciales:', error);
            this.showAlert('error', 'Error', 'No se pudieron cargar los datos iniciales');
        }
    }

    /**
     * Cargar productos
     */
/**
 * Cargar productos
 */
async loadProductos() {
    try {
        const response = await fetch('/inventario/productos-stock');
        if (response.ok) {
            const data = await response.json();
            this.productos = data.data || [];
            // Solo renderizar si realmente hay datos o si la carga fue exitosa
            this.renderProductos();
        } else {
            // Si hay error, mantener productos como array vacío
            this.productos = [];
            this.renderProductos();
        }
    } catch (error) {
        console.error('Error cargando productos:', error);
        // En caso de error, mantener como vacío
        this.productos = [];
        this.renderProductos();
    }
}

    /**
     * Cargar categorías
     */
    async loadCategorias() {
        try {
            const response = await fetch('/categorias/activas');
            if (response.ok) {
                const data = await response.json();
                this.categorias = data.data || [];
                this.populateSelect('producto_categoria', this.categorias, 'categoria_id', 'categoria_nombre');
                this.populateSelect('filter-categoria', this.categorias, 'categoria_id', 'categoria_nombre');
            }
        } catch (error) {
            console.error('Error cargando categorías:', error);
        }
    }

    /**
     * Cargar marcas
     */
    async loadMarcas() {
        try {
            const response = await fetch('/marcas/activas');
            if (response.ok) {
                const data = await response.json();
                this.marcas = data.data || [];
                this.populateSelect('producto_marca', this.marcas, 'marca_id', 'marca_descripcion');
            }
        } catch (error) {
            console.error('Error cargando marcas:', error);
        }
    }

    /**
     * Cargar calibres
     */
    async loadCalibres() {
        try {
            const response = await fetch('/calibres/activos');
            if (response.ok) {
                const data = await response.json();
                this.calibres = data.data || [];
                this.populateSelect('producto_calibre', this.calibres, 'calibre_id', 'calibre_nombre');
            }
        } catch (error) {
            console.error('Error cargando calibres:', error);
        }
    }

    /**
     * Cargar subcategorías por categoría
     */
    async loadSubcategorias(categoriaId) {
        const select = document.getElementById('producto_subcategoria');
        select.innerHTML = '<option value="">Seleccionar subcategoría</option>';
        
        if (!categoriaId) return;

        try {
            const response = await fetch(`/categorias/${categoriaId}/subcategorias`);
            if (response.ok) {
                const data = await response.json();
                this.populateSelect('producto_subcategoria', data.data || [], 'subcategoria_id', 'subcategoria_nombre');
            }
        } catch (error) {
            console.error('Error cargando subcategorías:', error);
        }
    }

    /**
     * Cargar modelos por marca
     */
    async loadModelos(marcaId) {
        const select = document.getElementById('producto_modelo');
        select.innerHTML = '<option value="">Seleccionar modelo</option>';
        
        if (!marcaId) return;

        try {
            const response = await fetch(`/marcas/${marcaId}/modelos`);
            if (response.ok) {
                const data = await response.json();
                this.populateSelect('producto_modelo', data.data || [], 'modelo_id', 'modelo_descripcion');
            }
        } catch (error) {
            console.error('Error cargando modelos:', error);
        }
    }

    /**
     * Cargar estadísticas
     */
    async loadStats() {
        try {
            const response = await fetch('/inventario/estadisticas');
            if (response.ok) {
                const data = await response.json();
                this.stats = data.data || this.stats;
                this.updateStats();
            }
        } catch (error) {
            console.error('Error cargando estadísticas:', error);
        }
    }

    /**
     * Cargar alertas
     */
    async loadAlertas() {
        try {
            const response = await fetch('/inventario/alertas-stock');
            if (response.ok) {
                const data = await response.json();
                this.renderAlertas(data.data || []);
            }
        } catch (error) {
            console.error('Error cargando alertas:', error);
        }
    }

        /**
     * Configurar manejo de fotos en formulario de registro
     */
    setupFotosHandling() {
        // Checkbox para mostrar/ocultar sección de fotos
        const checkboxFotos = document.getElementById('agregar_fotos');
        const seccionFotos = document.getElementById('seccion_fotos');
        
        if (checkboxFotos && seccionFotos) {
            checkboxFotos.addEventListener('change', (e) => {
                if (e.target.checked) {
                    seccionFotos.classList.remove('hidden');
                } else {
                    seccionFotos.classList.add('hidden');
                    this.limpiarPreviewFotos();
                }
            });
        }

        // Manejo de archivo seleccionado
        const inputFotos = document.getElementById('fotos_producto');
        if (inputFotos) {
            inputFotos.addEventListener('change', (e) => {
                this.handleFotosSeleccionadas(e.target.files);
            });
        }

        // Drag & Drop
        const dropZone = document.getElementById('foto_drop_zone');
        if (dropZone) {
            this.setupDragAndDrop(dropZone, inputFotos);
        }
    }

    /**
     * Manejar fotos seleccionadas
     */
    handleFotosSeleccionadas(archivos) {
        if (archivos.length > 5) {
            this.showAlert('warning', 'Límite excedido', 'Máximo 5 fotos por producto');
            return;
        }

        // Validar tipos de archivo
        const tiposValidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        const archivosInvalidos = Array.from(archivos).filter(archivo => 
            !tiposValidos.includes(archivo.type) || archivo.size > 2048000
        );

        if (archivosInvalidos.length > 0) {
            this.showAlert('error', 'Archivos inválidos', 'Solo se permiten JPG, PNG, WebP hasta 2MB');
            return;
        }

        this.previewFotos(archivos);
    }

    /**
     * Mostrar preview de fotos
     */
    previewFotos(archivos) {
        const container = document.getElementById('preview_fotos');
        if (!container) return;

        container.innerHTML = '';

        Array.from(archivos).forEach((archivo, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'relative group';
                previewDiv.innerHTML = `
                    <div class="relative">
                        <img src="${e.target.result}" 
                            alt="Preview ${index + 1}"
                            class="w-20 h-20 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-600">
                        <button type="button" 
                                onclick="inventarioManager.eliminarPreview(this)"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                            ×
                        </button>
                        ${index === 0 ? '<span class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 bg-blue-500 text-white text-xs px-1 rounded">Principal</span>' : ''}
                    </div>
                `;
                container.appendChild(previewDiv);
            };
            reader.readAsDataURL(archivo);
        });
    }

    /**
     * Configurar drag and drop
     */
    setupDragAndDrop(dropZone, inputFile) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            inputFile.files = files;
            this.handleFotosSeleccionadas(files);
        });

        dropZone.addEventListener('click', () => {
            inputFile.click();
        });
    }

    /**
     * Eliminar preview individual
     */
    eliminarPreview(boton) {
        const previewDiv = boton.closest('.relative.group');
        previewDiv.remove();
        
        // Actualizar el input file (esto es más complejo en vanilla JS)
        // Por ahora solo removemos visualmente
    }

    /**
     * Limpiar preview de fotos
     */
    limpiarPreviewFotos() {
        const container = document.getElementById('preview_fotos');
        if (container) {
            container.innerHTML = '';
        }
        
        const inputFotos = document.getElementById('fotos_producto');
        if (inputFotos) {
            inputFotos.value = '';
        }
    }

    /**
     * Abrir modal de gestión de fotos
     */
    async openFotosModal(productoId) {
        this.currentProductoId = productoId;
        
        try {
            // Cargar fotos existentes
            const response = await fetch(`/inventario/productos/${productoId}/fotos`);
            if (response.ok) {
                const data = await response.json();
                this.renderFotosExistentes(data.data || []);
                this.showModal('fotos');
            }
        } catch (error) {
            console.error('Error cargando fotos:', error);
            this.showAlert('error', 'Error', 'No se pudieron cargar las fotos');
        }
    }

    /**
     * Renderizar fotos existentes en modal - Corregido para tu modelo
     */
    renderFotosExistentes(fotos) {
        const container = document.getElementById('fotos_existentes');
        if (!container) return;

        if (fotos.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay fotos para este producto</p>
                </div>
            `;
            return;
        }

        container.innerHTML = fotos.map(foto => `
            <div class="relative group">
                <img src="${foto.foto_url}" 
                    alt="Foto producto"
                    class="w-24 h-24 object-cover rounded-lg border-2 ${foto.foto_principal ? 'border-blue-500' : 'border-gray-200 dark:border-gray-600'}">
                
                ${foto.foto_principal ? 
                    '<span class="absolute -top-2 left-1/2 transform -translate-x-1/2 bg-blue-500 text-white text-xs px-2 py-1 rounded">Principal</span>' : 
                    `<button onclick="inventarioManager.establecerPrincipal(${foto.foto_id})"
                            class="absolute -top-2 left-1/2 transform -translate-x-1/2 bg-gray-500 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity hover:bg-blue-500">
                        Hacer Principal
                    </button>`
                }
                
                <button onclick="inventarioManager.eliminarFotoProducto(${foto.foto_id})"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity">
                    ×
                </button>
            </div>
        `).join('');
    }

    /**
     * Subir nuevas fotos
     */
    async subirNuevasFotos() {
        const inputFile = document.getElementById('nuevas_fotos');
        if (!inputFile.files || inputFile.files.length === 0) {
            this.showAlert('warning', 'Sin archivos', 'Selecciona al menos una foto');
            return;
        }

        const formData = new FormData();
        Array.from(inputFile.files).forEach(archivo => {
            formData.append('fotos[]', archivo);
        });

        this.setLoading('fotos', true);

        try {
            const response = await fetch(`/inventario/productos/${this.currentProductoId}/fotos`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showAlert('success', 'Éxito', data.message);
                // Recargar fotos
                this.openFotosModal(this.currentProductoId);
                // Limpiar input
                inputFile.value = '';
            } else {
                this.showAlert('error', 'Error', data.message || 'Error al subir fotos');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.setLoading('fotos', false);
        }
    }

    /**
     * Eliminar foto del producto
     */
    async eliminarFotoProducto(fotoId) {
        const confirmacion = await Swal.fire({
            title: '¿Eliminar foto?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmacion.isConfirmed) return;

        try {
            const response = await fetch(`/inventario/fotos/${fotoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showAlert('success', 'Éxito', data.message);
                // Recargar fotos
                this.openFotosModal(this.currentProductoId);
            } else {
                this.showAlert('error', 'Error', data.message || 'Error al eliminar foto');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error', 'Error de conexión');
        }
    }

    /**
     * Establecer foto como principal
     */
    async establecerPrincipal(fotoId) {
        try {
            const response = await fetch(`/inventario/fotos/${fotoId}/principal`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showAlert('success', 'Éxito', data.message);
                // Recargar fotos
                this.openFotosModal(this.currentProductoId);
            } else {
                this.showAlert('error', 'Error', data.message || 'Error al actualizar foto principal');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error', 'Error de conexión');
        }
    }

    /**
     * Poblar select con opciones
     */
    populateSelect(selectId, options, valueField, textField) {
        const select = document.getElementById(selectId);
        const placeholder = select.querySelector('option[value=""]');
        
        // Limpiar opciones excepto el placeholder
        select.innerHTML = '';
        if (placeholder) {
            select.appendChild(placeholder);
        }
        
        options.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option[valueField];
            optionElement.textContent = option[textField];
            select.appendChild(optionElement);
        });
    }

    /**
     * Aplicar filtros a productos
     */
    aplicarFiltros() {
        this.renderProductos();
    }

    /**
     * Limpiar filtros
     */
    clearFilters() {
        this.filtros = { search: '', categoria: '', stock: '' };
        
        document.getElementById('search-productos').value = '';
        document.getElementById('filter-categoria').value = '';
        document.getElementById('filter-stock').value = '';
        
        this.renderProductos();
    }

    /**
     * Filtrar productos
     */
    getProductosFiltrados() {
        return this.productos.filter(producto => {
            const matchSearch = !this.filtros.search || 
                producto.producto_nombre.toLowerCase().includes(this.filtros.search.toLowerCase()) ||
                (producto.pro_codigo_sku && producto.pro_codigo_sku.toLowerCase().includes(this.filtros.search.toLowerCase())) ||
                (producto.producto_codigo_barra && producto.producto_codigo_barra.toLowerCase().includes(this.filtros.search.toLowerCase()));
            
            const matchCategoria = !this.filtros.categoria || 
                producto.producto_categoria_id.toString() === this.filtros.categoria;

            let matchStock = true;
            if (this.filtros.stock) {
                const stock = producto.stock_cantidad_disponible || 0;
                const minimo = producto.producto_stock_minimo || 0;
                
                switch (this.filtros.stock) {
                    case 'disponible':
                        matchStock = stock > 0;
                        break;
                    case 'bajo':
                        matchStock = stock <= minimo && stock > 0;
                        break;
                    case 'agotado':
                        matchStock = stock <= 0;
                        break;
                }
            }

            return matchSearch && matchCategoria && matchStock;
        });
    }

    async loadPaises() {
        try {
            const response = await fetch('/paises/activos');
            if (response.ok) {
                const data = await response.json();
                this.populateSelect('producto_madein', data.data || [], 'pais_id', 'pais_descripcion');
            }
        } catch (error) {
            console.error('Error cargando países:', error);
        }
    }


    // ================================
// 4. NUEVO MÉTODO: setupPreciosHandling()
// ================================
setupPreciosHandling() {
    // Cálculo automático de margen
    ['precio_costo', 'precio_venta'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', () => this.calcularMargen());
        }
    });
}

// ================================
// 5. NUEVOS MÉTODOS PARA LICENCIAS
// ================================

/**
 * Buscar licencias disponibles
 */
async buscarLicencias(query) {
    const container = document.getElementById('licencias_encontradas');
    
    if (!query || query.length < 2) {
        if (container) {
            container.classList.add('hidden');
        }
        return;
    }

    try {
        const response = await fetch(`/licencias/buscar?q=${encodeURIComponent(query)}`);
        if (response.ok) {
            const data = await response.json();
            this.renderResultadosLicencias(data.data || []);
        }
    } catch (error) {
        console.error('Error buscando licencias:', error);
    }
}

/**
 * Renderizar resultados de búsqueda de licencias
 */
renderResultadosLicencias(licencias) {
    const container = document.getElementById('licencias_encontradas');
    if (!container) return;
    
    if (licencias.length === 0) {
        container.innerHTML = `
            <div class="p-3 text-center text-gray-500 dark:text-gray-400">
                No se encontraron licencias
            </div>
        `;
        container.classList.remove('hidden');
        return;
    }

    container.innerHTML = licencias.map(licencia => `
        <div onclick="inventarioManager.seleccionarLicencia(${licencia.lipaimp_id})" 
             class="p-3 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-200 dark:border-gray-600 last:border-b-0">
            <div class="font-medium text-gray-900 dark:text-gray-100">
                Póliza: ${licencia.lipaimp_poliza}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                ${licencia.lipaimp_descripcion}
            </div>
            <div class="text-xs text-gray-400 dark:text-gray-500">
                Vence: ${new Date(licencia.lipaimp_fecha_vencimiento).toLocaleDateString()}
            </div>
        </div>
    `).join('');
    
    container.classList.remove('hidden');
}

/**
 * Seleccionar licencia específica
 */
async seleccionarLicencia(licenciaId) {
    try {
        const response = await fetch(`/licencias/${licenciaId}`);
        if (response.ok) {
            const data = await response.json();
            this.licenciaSeleccionada = data.data;
            
            // Actualizar interfaz
            const container = document.getElementById('licencia_seleccionada');
            const inputHidden = document.getElementById('licencia_id');
            const searchInput = document.getElementById('buscar_licencia');
            
            if (container) {
                container.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                Póliza: ${this.licenciaSeleccionada.lipaimp_poliza}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                ${this.licenciaSeleccionada.lipaimp_descripcion}
                            </div>
                        </div>
                        <button onclick="inventarioManager.limpiarLicenciaSeleccionada()" 
                                class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }
            
            if (inputHidden) {
                inputHidden.value = licenciaId;
            }
            
            if (searchInput) {
                searchInput.value = this.licenciaSeleccionada.lipaimp_poliza;
            }
            
            // Ocultar resultados
            document.getElementById('licencias_encontradas').classList.add('hidden');
        }
    } catch (error) {
        console.error('Error obteniendo licencia:', error);
    }
}

/**
 * Limpiar licencia seleccionada
 */
limpiarLicenciaSeleccionada() {
    this.licenciaSeleccionada = null;
    
    const container = document.getElementById('licencia_seleccionada');
    const inputHidden = document.getElementById('licencia_id');
    const searchInput = document.getElementById('buscar_licencia');
    
    if (container) {
        container.innerHTML = `
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Ninguna licencia seleccionada
            </div>
        `;
    }
    
    if (inputHidden) {
        inputHidden.value = '';
    }
    
    if (searchInput) {
        searchInput.value = '';
    }
}

// ================================
// 6. NUEVOS MÉTODOS PARA LOTES
// ================================

/**
 * Toggle entre lote automático y manual
 */
toggleLoteInput(tipo) {
    const loteManualInput = document.getElementById('lote_manual_input');
    const loteAutomaticoPreview = document.getElementById('lote_automatico_preview');
    
    if (tipo === 'manual') {
        if (loteManualInput) loteManualInput.classList.remove('hidden');
        if (loteAutomaticoPreview) loteAutomaticoPreview.classList.add('hidden');
    } else {
        if (loteManualInput) loteManualInput.classList.add('hidden');
        if (loteAutomaticoPreview) loteAutomaticoPreview.classList.remove('hidden');
        this.generarPreviewLote();
    }
}

/**
 * Generar preview del lote automático
 */
generarPreviewLote() {
    if (!this.productoSeleccionado) return;
    
    const fecha = new Date();
    const año = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    
    // Obtener código de marca del producto seleccionado
    let marcaCode = 'AUTO';
    if (this.productoSeleccionado.marca_nombre) {
        marcaCode = this.productoSeleccionado.marca_nombre.substring(0, 3).toUpperCase();
    }
    
    this.lotePreview = `L${año}-${mes}-${marcaCode}-001`;
    
    const previewElement = document.getElementById('lote_preview');
    if (previewElement) {
        previewElement.textContent = this.lotePreview;
    }
    
    // Actualizar descripción del preview
    const descripcionElement = document.getElementById('lote_automatico_preview');
    if (descripcionElement) {
        descripcionElement.innerHTML = `
            El sistema generará: <span id="lote_preview" class="font-mono text-green-600">${this.lotePreview}</span>
            <small class="block text-xs text-gray-400 mt-1">Basado en: Año-Mes-Marca-Secuencial</small>
        `;
    }
}

// ================================
// 7. NUEVOS MÉTODOS PARA SERIES
// ================================

/**
 * Contar series ingresadas
 */
contarSeries(texto) {
    const series = texto.split('\n').filter(line => line.trim() !== '');
    const countElement = document.getElementById('series_count');
    
    if (countElement) {
        countElement.textContent = series.length;
        
        // Cambiar color según cantidad
        if (series.length > 0) {
            countElement.className = 'font-semibold text-green-600';
        } else {
            countElement.className = 'font-semibold text-gray-400';
        }
    }
}

// ================================
// 8. NUEVOS MÉTODOS PARA PRECIOS
// ================================

/**
 * Calcular margen automáticamente
 */
calcularMargen() {
    const costInput = document.getElementById('precio_costo');
    const ventaInput = document.getElementById('precio_venta');
    const margenElement = document.getElementById('margen_calculado');
    const gananciaElement = document.getElementById('ganancia_calculada');
    
    if (!costInput || !ventaInput || !margenElement || !gananciaElement) return;
    
    const costo = parseFloat(costInput.value) || 0;
    const venta = parseFloat(ventaInput.value) || 0;
    
    if (costo > 0 && venta > 0) {
        const ganancia = venta - costo;
        const margen = ((ganancia / costo) * 100);
        
        margenElement.textContent = `${margen.toFixed(1)}%`;
        gananciaElement.textContent = `Q${ganancia.toFixed(2)}`;
        
        // Colorear según el margen
        if (margen < 10) {
            margenElement.className = 'text-red-600 font-bold';
        } else if (margen < 25) {
            margenElement.className = 'text-yellow-600 font-bold';
        } else {
            margenElement.className = 'text-green-600 font-bold';
        }
    } else {
        margenElement.textContent = '0%';
        gananciaElement.textContent = 'Q0.00';
        margenElement.className = 'text-gray-400 font-bold';
    }
}

// ================================
// 9. MODIFICAR seleccionarProducto() EXISTENTE
// ================================
async seleccionarProducto(productoId) {
    try {
        const response = await fetch(`/inventario/productos/${productoId}`);
        if (response.ok) {
            const data = await response.json();
            this.productoSeleccionado = data.data;
            
            // Ocultar step 1 y mostrar step 2
            document.getElementById('ingreso-step-1').classList.add('hidden');
            document.getElementById('ingreso-step-2').classList.remove('hidden');
            
            // Actualizar información del producto
            document.getElementById('producto_seleccionado_nombre').textContent = this.productoSeleccionado.producto_nombre;
            document.getElementById('producto_seleccionado_info').textContent = 
                `Stock actual: ${this.productoSeleccionado.stock_cantidad_disponible || 0} • SKU: ${this.productoSeleccionado.pro_codigo_sku}`;
            
            // Gestión de secciones según tipo de producto
            const licenciaSection = document.getElementById('licencia_section');
            const loteSection = document.getElementById('lote_section');
            const cantidadSection = document.getElementById('cantidad_section');
            const seriesSection = document.getElementById('series_section');
            
            // CAMBIO PRINCIPAL: Mostrar sección de licencia si requiere licencia
            if (this.productoSeleccionado.requiere_licencia && licenciaSection) {
                licenciaSection.classList.remove('hidden');
            } else if (licenciaSection) {
                licenciaSection.classList.add('hidden');
            }
            
            // Mostrar sección correcta según si requiere serie
            if (this.productoSeleccionado.producto_requiere_serie) {
                if (cantidadSection) cantidadSection.classList.add('hidden');
                if (seriesSection) seriesSection.classList.remove('hidden');
                if (loteSection) loteSection.classList.add('hidden'); // Series no usan lotes
            } else {
                if (cantidadSection) cantidadSection.classList.remove('hidden');
                if (seriesSection) seriesSection.classList.add('hidden');
                if (loteSection) loteSection.classList.remove('hidden');
                this.generarPreviewLote(); // Generar preview para lote automático
            }
            
            // Ocultar resultados de búsqueda
            document.getElementById('productos_encontrados').classList.add('hidden');
        }
    } catch (error) {
        console.error('Error obteniendo detalle del producto:', error);
    }
}
/**
 * Eliminar producto (con validaciones completas)
 */
async eliminarProducto(productoId) {
    // Obtener información del producto primero
    const producto = this.productos.find(p => p.producto_id === productoId);
    if (!producto) {
        this.showAlert('error', 'Error', 'Producto no encontrado');
        return;
    }

    // Advertencia adicional si tiene stock
    const tieneStock = (producto.stock_cantidad_total || 0) > 0;
    const tieneSeries = producto.producto_requiere_serie;

    let mensajeAdvertencia = 'Esta acción eliminará el producto permanentemente.';
    if (tieneStock) {
        mensajeAdvertencia = `⚠️ ATENCIÓN: Este producto tiene ${producto.stock_cantidad_total} unidades en stock.`;
    }
    if (tieneSeries) {
        mensajeAdvertencia += ' También se verificarán las series registradas.';
    }

    const confirmacion = await Swal.fire({
        title: '¿Eliminar producto?',
        html: `
            <div class="text-left">
                <p class="font-medium text-gray-900 mb-2">${producto.producto_nombre}</p>
                <p class="text-sm text-gray-600 mb-3">SKU: ${producto.pro_codigo_sku}</p>
                <p class="text-sm text-orange-600">${mensajeAdvertencia}</p>
                <p class="text-xs text-gray-500 mt-2">No se puede deshacer esta acción.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        width: '500px'
    });

    if (!confirmacion.isConfirmed) return;

    // Mostrar loading
    Swal.fire({
        title: 'Eliminando producto...',
        html: 'Verificando restricciones y eliminando datos asociados',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const response = await fetch(`/inventario/productos/${productoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            Swal.fire({
                title: 'Producto eliminado',
                text: data.message,
                icon: 'success',
                timer: 3000,
                timerProgressBar: true
            });
            
            // Recargar datos
            await Promise.all([
                this.loadProductos(),
                this.loadStats(),
                this.loadAlertas()
            ]);
        } else {
            Swal.fire({
                title: 'No se puede eliminar',
                text: data.message,
                icon: 'error',
                confirmButtonColor: '#dc2626'
            });
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor',
            icon: 'error',
            confirmButtonColor: '#dc2626'
        });
    }
}
/**
 * Renderizar productos
 */renderProductos() {
    const container = document.getElementById('productos-list');
    const emptyState = document.getElementById('empty-state');
    const productosFiltrados = this.getProductosFiltrados();

    // Si no hay productos filtrados
    if (productosFiltrados.length === 0) {
        // Si literalmente no hay productos cargados (array vacío o null)
        if (!this.productos || this.productos.length === 0) {
            emptyState.style.display = 'block';
            container.innerHTML = '';
        } else {
            // Hay productos pero los filtros no muestran ninguno
            emptyState.style.display = 'none';
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-search text-gray-400 text-3xl mb-2"></i>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No se encontraron productos con los filtros aplicados</p>
                    <button onclick="inventarioManager.clearFilters()" 
                            class="mt-3 inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i class="fas fa-eraser mr-2"></i>
                        Limpiar Filtros
                    </button>
                </div>
            `;
        }
        document.getElementById('productos-count').textContent = '0';
        return;
    }

    // Hay productos para mostrar
    emptyState.style.display = 'none';
    container.innerHTML = productosFiltrados.map(producto => this.renderProductoCard(producto)).join('');
    document.getElementById('productos-count').textContent = productosFiltrados.length;
}
    /**
     * Renderizar card de producto
     */
  // Método renderProductoCard actualizado con foto principal y todas las acciones


renderProductoCard(producto) {
    const stock = producto.stock_cantidad_disponible || 0;
    const minimo = producto.producto_stock_minimo || 0;
    
    let stockClass = 'bg-green-100 text-green-800';
    let stockText = 'En stock';
    let stockIcon = 'fa-check-circle';
    
    if (stock <= 0) {
        stockClass = 'bg-red-100 text-red-800';
        stockText = 'Agotado';
        stockIcon = 'fa-times-circle';
    } else if (stock <= minimo) {
        stockClass = 'bg-yellow-100 text-yellow-800';
        stockText = 'Stock bajo';
        stockIcon = 'fa-exclamation-triangle';
    }

    // Determinar imagen a mostrar
    const imagenSrc = producto.foto_principal;
    const iniciales = producto.producto_nombre.substring(0, 2).toUpperCase();

    return `
        <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 border-b border-gray-200 dark:border-gray-600">
            <!-- Foto o Avatar -->
            <div class="flex-shrink-0">
                ${imagenSrc ? 
                    `<img src="${imagenSrc}" 
                          alt="${producto.producto_nombre}"
                          class="w-20 h-20 rounded-full object-cover border-2 border-blue-200"
                          onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                     <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium text-sm" style="display:none;">
                        ${iniciales}
                     </div>` :
                    `<div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium text-sm">
                        ${iniciales}
                     </div>`
                }
            </div>

            <!-- Información del producto -->
            <div class="flex-1 min-w-0">
                <div class="font-medium text-gray-900 dark:text-gray-100">
                    ${producto.producto_nombre}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    SKU: ${producto.pro_codigo_sku} • ${producto.categoria_nombre || 'Sin categoría'} • ${producto.marca_nombre || 'Sin marca'}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Stock: ${stock} unidades${producto.producto_requiere_serie ? ' • Control por serie' : ''}
                </div>
            </div>

            <!-- Estado y Acciones -->
            <div class="flex items-center space-x-2">
                <!-- Estado del stock -->
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${stockClass}">
                    <i class="fas ${stockIcon} mr-1"></i>
                    ${stockText}
                </span>

                <!-- Botones de acciones -->
                <div class="flex space-x-1 ml-2">
                    <button onclick="inventarioManager.verDetalleProducto(${producto.producto_id})" 
                            class="p-1 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900 rounded"
                            title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="inventarioManager.editarProducto(${producto.producto_id})" 
                            class="p-1 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-100 dark:hover:bg-green-900 rounded"
                            title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="inventarioManager.openFotosModal(${producto.producto_id})" 
                            class="p-1 text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900 rounded"
                            title="Gestionar fotos">
                        <i class="fas fa-camera"></i>
                    </button>
                    <button onclick="inventarioManager.eliminarProducto(${producto.producto_id})" 
                            class="p-1 text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-100 dark:hover:bg-red-900 rounded"
                            title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                    <button onclick="inventarioManager.ingresoRapido(${producto.producto_id})" 
                            class="p-1 text-orange-600 hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-900 rounded"
                            title="Ingreso rápido">
                        <i class="fas fa-plus-circle"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}
    /**
     * Actualizar estadísticas en el dashboard
     */
    updateStats() {
        document.getElementById('total-productos').textContent = this.stats.totalProductos;
        document.getElementById('stock-total').textContent = this.stats.stockTotal;
        document.getElementById('stock-bajo').textContent = this.stats.stockBajo;
        document.getElementById('stock-agotado').textContent = this.stats.stockAgotado;
    }

    /**
     * Renderizar alertas
     */
    renderAlertas(alertas) {
        const container = document.getElementById('alertas-list');
        const badge = document.getElementById('alertas-badge');
        const count = document.getElementById('alertas-count');
        
        badge.textContent = alertas.length;
        count.textContent = alertas.length;
        
        if (alertas.length === 0) {
            container.innerHTML = `
                <div class="text-center py-6">
                    <i class="fas fa-check-circle text-green-400 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay alertas pendientes</p>
                </div>
            `;
            return;
        }

        container.innerHTML = alertas.slice(0, 5).map(alerta => `
            <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">
                        ${alerta.producto_nombre}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Stock: ${alerta.stock_actual}
                    </p>
                </div>
            </div>
        `).join('');
    }

    /**
     * Buscar productos para el modal de ingreso
     */
    async buscarProductos(query) {
        const container = document.getElementById('productos_encontrados');
        
        if (!query || query.length < 2) {
            container.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`/inventario/buscar-productos?q=${encodeURIComponent(query)}`);
            if (response.ok) {
                const data = await response.json();
                this.renderResultadosBusqueda(data.data || []);
            }
        } catch (error) {
            console.error('Error buscando productos:', error);
        }
    }

    /**
     * Renderizar resultados de búsqueda
     */
    renderResultadosBusqueda(productos) {
        const container = document.getElementById('productos_encontrados');
        
        if (productos.length === 0) {
            container.innerHTML = `
                <div class="p-3 text-center text-gray-500 dark:text-gray-400">
                    No se encontraron productos
                </div>
            `;
            container.classList.remove('hidden');
            return;
        }

        container.innerHTML = productos.map(producto => `
            <div onclick="inventarioManager.seleccionarProducto(${producto.producto_id})" 
                 class="p-3 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer border-b border-gray-200 dark:border-gray-600">
                <div class="font-medium text-gray-900 dark:text-gray-100">${producto.producto_nombre}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    SKU: ${producto.pro_codigo_sku} • Stock actual: ${producto.stock_cantidad_disponible || 0}
                </div>
            </div>
        `).join('');
        
        container.classList.remove('hidden');
    }

    /**
     * Seleccionar producto para ingreso
     */
    async seleccionarProducto(productoId) {
        try {
            const response = await fetch(`/inventario/productos/${productoId}`);
            if (response.ok) {
                const data = await response.json();
                this.productoSeleccionado = data.data;
                
                // Ocultar step 1 y mostrar step 2
                document.getElementById('ingreso-step-1').classList.add('hidden');
                document.getElementById('ingreso-step-2').classList.remove('hidden');
                
                // Actualizar información del producto
                document.getElementById('producto_seleccionado_nombre').textContent = this.productoSeleccionado.producto_nombre;
                document.getElementById('producto_seleccionado_info').textContent = 
                    `Stock actual: ${this.productoSeleccionado.stock_cantidad_disponible || 0} • SKU: ${this.productoSeleccionado.pro_codigo_sku}`;
                
                // Mostrar sección correcta según si requiere serie
                if (this.productoSeleccionado.producto_requiere_serie) {
                    document.getElementById('cantidad_section').classList.add('hidden');
                    document.getElementById('series_section').classList.remove('hidden');
                } else {
                    document.getElementById('cantidad_section').classList.remove('hidden');
                    document.getElementById('series_section').classList.add('hidden');
                }
                
                // Ocultar resultados de búsqueda
                document.getElementById('productos_encontrados').classList.add('hidden');
            }
        } catch (error) {
            console.error('Error obteniendo detalle del producto:', error);
        }
    }

    /**
     * Abrir modal de registro de producto
     */
    openRegistroModal() {
        this.resetRegistroForm();
        this.showModal('registro');
    }

    /**
     * Abrir modal de ingreso a inventario
     */
    openIngresoModal() {
        this.resetIngresoForm();
        this.showModal('ingreso');
    }

    /**
     * Abrir modal de egreso
     */
    openEgresoModal() {
        this.showAlert('info', 'Próximamente', 'Función de egreso en desarrollo');
    }

    /**
     * Ver historial de movimientos
     */
    verHistorial() {
        this.showAlert('info', 'Próximamente', 'Historial de movimientos en desarrollo');
    }

    /**
     * Generar reporte
     */
    generarReporte() {
        this.showAlert('info', 'Próximamente', 'Generación de reportes en desarrollo');
    }

    /**
     * Ingreso rápido a producto existente
     */
    ingresoRapido(productoId) {
        // Pre-seleccionar el producto y abrir modal de ingreso
        this.openIngresoModal();
        setTimeout(() => {
            this.seleccionarProducto(productoId);
        }, 100);
    }

    /**
     * Ver detalle de producto
     */
    verDetalleProducto(productoId) {
        this.showAlert('info', 'Próximamente', 'Vista de detalle en desarrollo');
    }

    /**
     * Editar producto
     */
    editarProducto(productoId) {
        this.showAlert('info', 'Próximamente', 'Edición de productos en desarrollo');
    }

    /**
     * Toggle panel de alertas
     */
    toggleAlertas() {
        const container = document.getElementById('alerts-container');
        container.classList.toggle('hidden');
    }

    /**
     * Manejar envío del formulario de registro
     */
    async handleRegistroSubmit() {
        const form = document.getElementById('registro-form');
        const formData = new FormData(form);
        
        if (!this.validateRegistroForm()) {
            return;
        }

        this.setLoading('registro', true);

        try {
            const response = await fetch('/inventario/productos', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showAlert('success', 'Éxito', data.message);
                this.closeModal('registro');
                this.loadProductos();
                this.loadStats();
            } else {
                if (data.errors) {
                    this.showValidationErrors('registro', data.errors);
                } else {
                    this.showAlert('error', 'Error', data.message || 'Error al procesar la solicitud');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.setLoading('registro', false);
        }
    }

    /**
     * Manejar envío del formulario de ingreso
     */
    async handleIngresoSubmit() {
        if (!this.productoSeleccionado) {
            this.showAlert('error', 'Error', 'Debe seleccionar un producto');
            return;
        }
    
        const form = document.getElementById('ingreso-form');
        const formData = new FormData(form);
        formData.append('producto_id', this.productoSeleccionado.producto_id);
        
        if (!this.validateIngresoForm()) {
            return;
        }
    
        this.setLoading('ingreso', true);
    
        try {
            const response = await fetch('/inventario/ingresar', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
    
            const data = await response.json();
    
            if (response.ok && data.success) {
                // Mostrar mensaje de éxito más detallado
                let mensaje = data.message;
                if (data.data) {
                    if (data.data.lote_codigo) {
                        mensaje += `\nLote: ${data.data.lote_codigo}`;
                    }
                    if (data.data.licencia_asignada) {
                        mensaje += `\nLicencia: ${data.data.licencia_asignada}`;
                    }
                }
                
                this.showAlert('success', 'Éxito', mensaje);
                this.closeModal('ingreso');
                
                // Recargar datos
                await Promise.all([
                    this.loadProductos(),
                    this.loadStats(),
                    this.loadAlertas()
                ]);
            } else {
                if (data.errors) {
                    this.showValidationErrors('ingreso', data.errors);
                } else {
                    this.showAlert('error', 'Error', data.message || 'Error al procesar la solicitud');
                }
            }
        } catch (error) {
            console.error('Error:', error);
            this.showAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.setLoading('ingreso', false);
        }
    }


    
// ================================
//  Verificar si producto requiere licencia (OPCIONAL)
// ================================
async verificarRequiereLicencia(productoId) {
    try {
        const response = await fetch(`/inventario/productos/${productoId}/requiere-licencia`);
        if (response.ok) {
            const data = await response.json();
            return data.requiere_licencia;
        }
        return false;
    } catch (error) {
        console.error('Error verificando licencia:', error);
        return false;
    }
}

    /**
     * Validar formulario de registro
     */
    validateRegistroForm() {
        const nombre = document.getElementById('producto_nombre').value.trim();
        const categoria = document.getElementById('producto_categoria').value;
        const subcategoria = document.getElementById('producto_subcategoria').value;
        const marca = document.getElementById('producto_marca').value;
        
        this.clearErrors('registro');
        
        let isValid = true;
        
        if (!nombre) {
            this.showFieldError('producto_nombre', 'El nombre del producto es obligatorio');
            isValid = false;
        }
        
        if (!categoria) {
            this.showFieldError('producto_categoria_id', 'La categoría es obligatoria');
            isValid = false;
        }
        
        if (!subcategoria) {
            this.showFieldError('producto_subcategoria_id', 'La subcategoría es obligatoria');
            isValid = false;
        }
        
        if (!marca) {
            this.showFieldError('producto_marca_id', 'La marca es obligatoria');
            isValid = false;
        }
        
        return isValid;
    }

    /**
     * Validar formulario de ingreso
     */
    validateIngresoForm() {
        const tipo = document.getElementById('mov_tipo').value;
        const origen = document.getElementById('mov_origen').value.trim();
        
        this.clearErrors('ingreso');
        
        let isValid = true;
        
        if (!tipo) {
            this.showFieldError('mov_tipo', 'El tipo de movimiento es obligatorio');
            isValid = false;
        }
        
        if (!origen) {
            this.showFieldError('mov_origen', 'El origen es obligatorio');
            isValid = false;
        }
        
        // CAMBIO PRINCIPAL: Validación de licencia para productos que requieren licencia
        if (this.productoSeleccionado.requiere_licencia) {
            const licenciaId = document.getElementById('licencia_id').value;
            if (!licenciaId) {
                this.showFieldError('licencia_id', 'La licencia es obligatoria para productos con asignaciones de licencias');
                isValid = false;
            }
        }
        
        // Validación de lote para productos sin serie
        if (!this.productoSeleccionado.producto_requiere_serie) {
            const tipoLote = document.querySelector('input[name="generar_lote"]:checked')?.value;
            if (tipoLote === 'manual') {
                const numeroLote = document.getElementById('numero_lote').value.trim();
                if (!numeroLote) {
                    this.showFieldError('numero_lote', 'El número de lote es obligatorio');
                    isValid = false;
                }
            }
        }
        
        // Validación existente de cantidad/series
        if (this.productoSeleccionado.producto_requiere_serie) {
            const series = document.getElementById('numeros_series').value.trim();
            if (!series) {
                this.showFieldError('numeros_series', 'Los números de serie son obligatorios');
                isValid = false;
            }
        } else {
            const cantidad = document.getElementById('mov_cantidad').value;
            if (!cantidad || cantidad <= 0) {
                this.showFieldError('mov_cantidad', 'La cantidad debe ser mayor a 0');
                isValid = false;
            }
        }
        
        // Validación de precios si están habilitados
        const agregaPrecios = document.getElementById('agregar_precios')?.checked;
        if (agregaPrecios) {
            const precioCosto = document.getElementById('precio_costo').value;
            const precioVenta = document.getElementById('precio_venta').value;
            
            if (!precioCosto || parseFloat(precioCosto) <= 0) {
                this.showFieldError('precio_costo', 'El precio de costo es obligatorio');
                isValid = false;
            }
            
            if (!precioVenta || parseFloat(precioVenta) <= 0) {
                this.showFieldError('precio_venta', 'El precio de venta es obligatorio');
                isValid = false;
            }
    
            // Validar que precio de venta sea mayor al costo
            if (parseFloat(precioVenta) <= parseFloat(precioCosto)) {
                this.showFieldError('precio_venta', 'El precio de venta debe ser mayor al costo');
                isValid = false;
            }
        }
        
        return isValid;
    }



    /**
     * Mostrar modal
     */
    showModal(type) {
        const modal = document.getElementById(`${type}-modal`);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Cerrar modal
     */
    closeModal(type) {
        const modal = document.getElementById(`${type}-modal`);
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        
        this.setLoading(type, false);
        this.clearErrors(type);
        
        if (type === 'registro') {
            this.resetRegistroForm();
        } else if (type === 'ingreso') {
            this.resetIngresoForm();
        }
    }

    /**
     * Resetear formulario de registro
     */
    resetRegistroForm() {
        document.getElementById('registro-form').reset();
        this.clearErrors('registro');
        
        // Resetear selects a su estado inicial
        document.getElementById('producto_subcategoria').innerHTML = '<option value="">Seleccionar subcategoría</option>';
        document.getElementById('producto_modelo').innerHTML = '<option value="">Seleccionar modelo</option>';
    }

    /**
     * Resetear formulario de ingreso
     */
    resetIngresoForm() {
        document.getElementById('ingreso-form').reset();
        this.clearErrors('ingreso');
        
        // Resetear estado del modal
        document.getElementById('ingreso-step-1').classList.remove('hidden');
        document.getElementById('ingreso-step-2').classList.add('hidden');
        document.getElementById('productos_encontrados').classList.add('hidden');
        
        // Resetear campos de licencias
        const licenciasEncontradas = document.getElementById('licencias_encontradas');
        if (licenciasEncontradas) {
            licenciasEncontradas.classList.add('hidden');
        }
        
        // Resetear licencia seleccionada
        this.limpiarLicenciaSeleccionada();
        
        // Resetear sección de precios
        const seccionPrecios = document.getElementById('seccion_precios');
        const checkboxPrecios = document.getElementById('agregar_precios');
        if (seccionPrecios) {
            seccionPrecios.classList.add('hidden');
        }
        if (checkboxPrecios) {
            checkboxPrecios.checked = false;
        }
        
        // Resetear contador de series
        const seriesCount = document.getElementById('series_count');
        if (seriesCount) {
            seriesCount.textContent = '0';
            seriesCount.className = 'font-semibold text-gray-400';
        }
        
        // Resetear cálculo de margen
        const margenCalculado = document.getElementById('margen_calculado');
        const gananciaCalculada = document.getElementById('ganancia_calculada');
        if (margenCalculado) {
            margenCalculado.textContent = '0%';
            margenCalculado.className = 'text-gray-400 font-bold';
        }
        if (gananciaCalculada) {
            gananciaCalculada.textContent = 'Q0.00';
        }
        
        // Resetear radio buttons de lote
        const radioAutomatico = document.querySelector('input[name="generar_lote"][value="automatico"]');
        if (radioAutomatico) {
            radioAutomatico.checked = true;
            this.toggleLoteInput('automatico');
        }
        
        // Resetear estado
        this.productoSeleccionado = null;
        this.licenciaSeleccionada = null;
        this.lotePreview = '';
    }


    
// ================================
//  Validación adicional para formulario con licencias
// ================================
validateLicenciaSeleccionada() {
    if (!this.productoSeleccionado?.requiere_licencia) {
        return true; // No requiere validación
    }
    
    const licenciaId = document.getElementById('licencia_id')?.value;
    
    if (!licenciaId) {
        this.showAlert('error', 'Error', 'Debe seleccionar una licencia para este producto');
        return false;
    }
    
    // Verificar que la licencia no esté vencida
    if (this.licenciaSeleccionada?.lipaimp_fecha_vencimiento) {
        const fechaVencimiento = new Date(this.licenciaSeleccionada.lipaimp_fecha_vencimiento);
        const hoy = new Date();
        
        if (fechaVencimiento < hoy) {
            this.showAlert('error', 'Error', 'La licencia seleccionada está vencida');
            return false;
        }
    }
    
    return true;
}
    /**
     * Establecer estado de carga
     */
    setLoading(type, loading) {
        const submitBtn = document.getElementById(`${type}-submit-btn`);
        const submitText = document.getElementById(`${type}-submit-text`);
        const loadingSpan = document.getElementById(`${type}-loading`);
        
        if (!submitBtn || !submitText || !loadingSpan) return;
        
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
    clearErrors(type) {
        const form = document.getElementById(`${type}-form`);
        if (!form) return;
        
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
     * Mostrar errores de validación del servidor
     */
    showValidationErrors(type, errors) {
        Object.keys(errors).forEach(field => {
            if (errors[field] && errors[field].length > 0) {
                this.showFieldError(field, errors[field][0]);
            }
        });
    }

    /**
     * Mostrar alerta con SweetAlert2
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
            config.timerProgressBar = true;
        } else if (type === 'error') {
            config.confirmButtonColor = '#dc2626';
        } else if (type === 'warning') {
            config.confirmButtonColor = '#f59e0b';
        } else if (type === 'info') {
            config.confirmButtonColor = '#3b82f6';
        }

        Swal.fire(config);
    }

    /**
     * Mostrar notificación toast
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white transition-all duration-300 transform translate-x-full`;
        
        const bgColors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        
        toast.classList.add(bgColors[type] || bgColors.info);
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Animar entrada
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Remover después de 3 segundos
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    }

    /**
     * Formatear números
     */
    formatNumber(num) {
        return new Intl.NumberFormat('es-GT').format(num);
    }

    /**
     * Formatear moneda
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('es-GT', {
            style: 'currency',
            currency: 'GTQ'
        }).format(amount);
    }

    /**
     * Debounce para búsquedas
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar dependencias necesarias
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 no está cargado');
        return;
    }
    
    // Inicializar el gestor de inventario
    window.inventarioManager = new InventarioManager();
    
    console.log('Sistema de inventario inicializado correctamente');
});