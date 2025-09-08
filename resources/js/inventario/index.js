/**
 * Sistema de Inventario - Armería
 * JavaScript corregido para trabajar con datos reales del backend
 */

class InventarioSistema {
    constructor() {
        this.currentTab = 'stock-actual';
        this.modals = new Map();
        this.isLoading = false;
        this.init();
    }

    init() {
        console.log('Inicializando Sistema de Inventario...');
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initializeSystem());
        } else {
            this.initializeSystem();
        }
    }

    async initializeSystem() {
        try {
            this.setupTabs();
            this.setupModals();
            this.setupForms();
            this.setupTooltips();
            this.bindGlobalEvents();
            
            // Cargar datos iniciales del backend
            await this.loadInitialData();
            
            console.log('Sistema de inventario inicializado correctamente');
        } catch (error) {
            console.error('Error al inicializar el sistema:', error);
            this.showError('Error al inicializar el sistema de inventario');
        }
    }

    /**
     * SISTEMA DE PESTAÑAS - SIN CAMBIOS
     */
/**
 * SISTEMA DE PESTAÑAS CORREGIDO
 */
setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');

    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = button.getAttribute('data-tab');
            if (targetTab) {
                this.switchTab(targetTab, button);
            }
        });
    });

    // Inicializar primera pestaña activa
    const firstActiveTab = document.querySelector('.tab-button.active');
    if (firstActiveTab) {
        const target = firstActiveTab.getAttribute('data-tab');
        if (target) {
            this.switchTab(target, firstActiveTab);
        }
    }
}

switchTab(targetId, activeButton) {
    // Limpiar todas las pestañas
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.remove('text-blue-600', 'border-blue-500');
        btn.classList.add('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');
    });

    // Activar pestaña seleccionada
    activeButton.classList.add('active');
    activeButton.classList.add('text-blue-600', 'border-blue-500');
    activeButton.classList.remove('text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'border-transparent');

    // Ocultar todos los paneles
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('show', 'active');
        pane.classList.add('fade');
    });

    // Mostrar panel objetivo
    const targetPane = document.getElementById(targetId);
    if (targetPane) {
        targetPane.classList.add('show', 'active');
        targetPane.classList.remove('fade');
        this.currentTab = targetId;
        this.onTabChange(targetId);
    }
}

async onTabChange(tabId) {
    console.log(`Cambiando a pestaña: ${tabId}`);
    
    try {
        switch (tabId) {
            case 'stock-actual':
                await this.loadStockData();
                break;
            case 'ingresar-producto':
                await this.initializeProductForm();
                break;
            case 'egresos':
                await this.initializeEgresosForm();
                break;
            case 'historial-movimientos':
                await this.loadHistorialData();
                break;
            case 'graficas-reportes':
                await this.loadGraficasData();
                break;
            case 'historial':
                await this.loadHistorialCompleto();
                break;
        }
    } catch (error) {
        console.error(`Error al cargar datos para ${tabId}:`, error);
    }
}

   /**
 * Método público para cambiar pestañas desde HTML
 */
switchToTab(tabId) {
    const button = document.querySelector(`[data-tab="${tabId}"]`);
    if (button) {
        this.switchTab(tabId, button);
    } else {
        console.warn(`No se encontró botón para la pestaña: ${tabId}`);
    }
}

    /**
     * CARGA DE DATOS REALES DEL BACKEND
     */
    async loadInitialData() {
        console.log('Cargando datos iniciales...');
        
        try {
            // Cargar métricas del dashboard
            await this.updateDashboardMetrics();
            
            // Si estamos en la pestaña de stock, cargar datos
            if (this.currentTab === 'stock-actual') {
                await this.loadStockData();
            }
            
        } catch (error) {
            console.error('Error al cargar datos iniciales:', error);
            // Mostrar datos por defecto en caso de error
            this.showDefaultDashboard();
        }
    }

    async updateDashboardMetrics() {
        try {
            const response = await fetch('/inventario/resumen-dashboard', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.updateElement('total-productos', result.data.total_productos || 0);
                this.updateElement('total-series', result.data.total_series || 0);
                this.updateElement('movimientos-hoy', result.data.movimientos_hoy || 0);
                this.updateElement('egresos-mes', result.data.egresos_mes || 0);
                this.updateElement('stock-bajo', result.data.stock_bajo || 0);
            } else {
                throw new Error(result.message || 'Error en la respuesta del servidor');
            }
            
        } catch (error) {
            console.error('Error al actualizar métricas del dashboard:', error);
            this.showDefaultDashboard();
        }
    }

    showDefaultDashboard() {
        // Mostrar valores por defecto cuando no se pueden cargar datos
        this.updateElement('total-productos', '0');
        this.updateElement('total-series', '0');
        this.updateElement('movimientos-hoy', '0');
        this.updateElement('egresos-mes', '0');
        this.updateElement('stock-bajo', '0');
    }

    async loadStockData() {
        console.log('Cargando datos de stock desde el backend...');
        
        // Mostrar loader
        this.showStockLoader();
        
        try {
            const response = await fetch('/inventario/productos-stock', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.data && Array.isArray(result.data)) {
                this.renderStockTable(result.data);
                this.updateStockCounters(result.data);
                this.hideStockLoader();
            } else {
                throw new Error('Formato de datos incorrecto');
            }
            
        } catch (error) {
            console.error('Error al cargar stock:', error);
            this.hideStockLoader();
            this.showEmptyStockTable();
            this.showError('Error al cargar datos de stock. Verifique la conexión con el servidor.');
        }
    }

    showStockLoader() {
        const tbody = document.querySelector('#tabla-stock tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr id="loading-row">
                    <td colspan="9" class="px-6 py-8 text-center">
                        <div class="flex items-center justify-center space-x-3">
                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <span class="text-gray-500 dark:text-gray-400">Cargando productos...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    hideStockLoader() {
        const loadingRow = document.getElementById('loading-row');
        if (loadingRow) {
            loadingRow.remove();
        }
    }

    showEmptyStockTable() {
        const tbody = document.querySelector('#tabla-stock tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-8 text-center">
                        <div class="text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p>No hay productos en el inventario</p>
                            <p class="text-sm mt-2">Comience agregando productos desde la pestaña "Ingresar Producto"</p>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    renderStockTable(productos) {
        const tbody = document.querySelector('#tabla-stock tbody');
        if (!tbody) {
            console.error('No se encontró el tbody de la tabla de stock');
            return;
        }

        // Limpiar tabla
        tbody.innerHTML = '';

        if (!productos || productos.length === 0) {
            this.showEmptyStockTable();
            return;
        }

        productos.forEach(producto => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700';
            
            const estadoStock = this.getEstadoStock(producto.stock_actual || 0);
            
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    ${producto.codigo_barra || 'Sin código'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">${producto.nombre || 'Sin nombre'}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${producto.categoria || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${producto.marca || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${producto.modelo || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-lg font-bold ${estadoStock.clase}">${producto.stock_actual || 0}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    ${producto.requiere_serie ? 
                        `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ${producto.series_disponibles || 0}
                        </span>` : 
                        '<span class="text-gray-400">N/A</span>'
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${estadoStock.badgeClass}">
                        ${estadoStock.texto}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center space-x-2">
                        <button onclick="inventario.verDetalleProducto(${producto.producto_id || producto.id})" 
                                class="text-blue-600 hover:text-blue-900 text-sm" title="Ver detalles">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        <button onclick="inventario.verHistorialProducto(${producto.producto_id || producto.id})" 
                                class="text-green-600 hover:text-green-900 text-sm" title="Ver historial">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        <button onclick="inventario.seleccionarProductoMovimiento(${producto.producto_id || producto.id})" 
                                class="text-orange-600 hover:text-orange-900 text-sm" title="Registrar movimiento">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </button>
                    </div>
                </td>
            `;
            
            tbody.appendChild(row);
        });

        // Actualizar información de paginación
        this.updateElement('total-productos-tabla', productos.length);
    }

    getEstadoStock(cantidad) {
        const stock = parseInt(cantidad) || 0;
        
        if (stock === 0) {
            return {
                clase: 'text-red-600',
                badgeClass: 'bg-red-100 text-red-800',
                texto: 'Agotado'
            };
        } else if (stock <= 5) {
            return {
                clase: 'text-yellow-600',
                badgeClass: 'bg-yellow-100 text-yellow-800',
                texto: 'Bajo'
            };
        } else {
            return {
                clase: 'text-green-600',
                badgeClass: 'bg-green-100 text-green-800',
                texto: 'Normal'
            };
        }
    }

    updateStockCounters(productos) {
        if (!productos || !Array.isArray(productos)) {
            productos = [];
        }

        const stockNormal = productos.filter(p => (p.stock_actual || 0) > 5).length;
        const stockBajo = productos.filter(p => (p.stock_actual || 0) > 0 && (p.stock_actual || 0) <= 5).length;
        const stockAgotado = productos.filter(p => (p.stock_actual || 0) === 0).length;
        const conSeries = productos.filter(p => p.requiere_serie).length;

        this.updateElement('count-stock-normal', stockNormal);
        this.updateElement('count-stock-bajo', stockBajo);
        this.updateElement('count-stock-agotado', stockAgotado);
        this.updateElement('count-con-series', conSeries);
    }

    async initializeProductForm() {
        console.log('Inicializando formulario de producto...');
        
        try {
            // Aquí deberías cargar las opciones para los selects desde el backend
            // Por ejemplo, categorías, marcas, modelos, etc.
            
            const form = document.getElementById('form-ingresar-producto');
            if (form) {
                this.limpiarFormulario(form);
            }

            // Ejemplo de cómo cargar opciones reales
            // await this.cargarOpcionesFormulario();
            
        } catch (error) {
            console.error('Error al inicializar formulario de producto:', error);
        }
    }

    async initializeEgresosForm() {
        console.log('Inicializando formulario de egresos...');
        
        try {
            await this.setupProductSelectForEgresos();
            this.setupEgresosEvents();
        } catch (error) {
            console.error('Error al inicializar formulario de egresos:', error);
        }
    }

    async setupProductSelectForEgresos() {
        const select = document.getElementById('egreso_producto_id');
        if (!select) return;
        
        try {
            const response = await fetch('/inventario/productos-stock', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            select.innerHTML = '<option value="">Seleccionar producto...</option>';
            
            if (result.data && Array.isArray(result.data)) {
                result.data.filter(p => (p.stock_actual || 0) > 0).forEach(producto => {
                    select.innerHTML += `<option value="${producto.producto_id || producto.id}">${producto.nombre} (Stock: ${producto.stock_actual || 0})</option>`;
                });
            }
            
        } catch (error) {
            console.error('Error al cargar productos para egresos:', error);
            select.innerHTML = '<option value="">Error al cargar productos</option>';
        }
    }

    async loadHistorialData() {
        console.log('Cargando historial de movimientos...');
        
        try {
            const response = await fetch('/inventario/movimientos', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success && result.data) {
                this.renderHistorialTable(result.data);
            } else {
                this.showEmptyHistorialTable();
            }
            
        } catch (error) {
            console.error('Error al cargar historial:', error);
            this.showEmptyHistorialTable();
            this.showError('Error al cargar historial de movimientos');
        }
    }

    showEmptyHistorialTable() {
        const tbody = document.querySelector('#tbody-historial-movimientos');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>No hay movimientos registrados</p>
                    </td>
                </tr>
            `;
        }
    }

    async loadGraficasData() {
        console.log('Cargando datos para gráficas...');
        // Implementar cuando tengas los endpoints de gráficas
    }

    /**
     * UTILIDADES
     */
    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.style.opacity = '0.5';
            setTimeout(() => {
                element.textContent = value;
                element.style.opacity = '1';
            }, 200);
        }
    }

    /**
     * SETUP DE MODALES Y TOOLTIPS - CORREGIDOS
     */
    setupModals() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
                this.closeAllModals();
            }
            
            if (e.target.classList.contains('bg-gray-500') && e.target.classList.contains('bg-opacity-75')) {
                const modal = e.target.closest('.fixed.inset-0');
                if (modal) {
                    this.closeModal(modal);
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    setupTooltips() {
        // Tooltip corregido - verificar que sea un elemento antes de usar hasAttribute
        document.addEventListener('mouseenter', (e) => {
            if (e.target && e.target.nodeType === Node.ELEMENT_NODE && e.target.hasAttribute && e.target.hasAttribute('data-tooltip')) {
                this.showTooltip(e.target, e.target.getAttribute('data-tooltip'));
            }
        });

        document.addEventListener('mouseleave', (e) => {
            if (e.target && e.target.nodeType === Node.ELEMENT_NODE && e.target.hasAttribute && e.target.hasAttribute('data-tooltip')) {
                this.hideTooltip();
            }
        });
    }

    showTooltip(element, text) {
        const tooltip = document.createElement('div');
        tooltip.id = 'custom-tooltip';
        tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg';
        tooltip.textContent = text;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    }

    hideTooltip() {
        const tooltip = document.getElementById('custom-tooltip');
        if (tooltip) tooltip.remove();
    }

    setupForms() {
        // Configuración básica de formularios - expandir según necesites
        const formIngreso = document.getElementById('form-ingresar-producto');
        if (formIngreso) {
            formIngreso.addEventListener('submit', (e) => this.handleProductoSubmit(e));
        }

        const formEgreso = document.getElementById('form-registrar-egreso');
        if (formEgreso) {
            formEgreso.addEventListener('submit', (e) => this.handleEgresoSubmit(e));
        }
    }

    async handleProductoSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            this.showLoading('Registrando producto...');
            
            const response = await fetch('/inventario/ingresar-producto', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Producto registrado exitosamente');
                this.limpiarFormulario(form);
                // Recargar datos de stock si estamos en esa pestaña
                if (this.currentTab === 'stock-actual') {
                    await this.loadStockData();
                }
            } else {
                this.showValidationErrors(result.errors || { general: ['Error al registrar producto'] });
            }
            
        } catch (error) {
            console.error('Error al registrar producto:', error);
            this.showError('Error de conexión al procesar la solicitud');
        } finally {
            this.hideLoading();
        }
    }

    async handleEgresoSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            this.showLoading('Registrando egreso...');
            
            const response = await fetch('/inventario/registrar-egreso', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess('Egreso registrado exitosamente');
                this.limpiarFormulario(form);
                // Recargar datos si es necesario
                if (this.currentTab === 'stock-actual') {
                    await this.loadStockData();
                }
            } else {
                this.showValidationErrors(result.errors || { general: ['Error al registrar egreso'] });
            }
            
        } catch (error) {
            console.error('Error al registrar egreso:', error);
            this.showError('Error de conexión al procesar la solicitud');
        } finally {
            this.hideLoading();
        }
    }

    limpiarFormulario(form) {
        form.reset();
        
        form.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });
    }

    setupEgresosEvents() {
        // Implementar eventos específicos de egresos
    }

    bindGlobalEvents() {
        // Eventos globales básicos
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.altKey && e.keyCode === 73) {
                e.preventDefault();
                const ingresoTab = document.querySelector('[data-bs-target="#ingresar-producto"]');
                if (ingresoTab) ingresoTab.click();
            }
            
            if (e.ctrlKey && e.altKey && e.keyCode === 83) {
                e.preventDefault();
                const stockTab = document.querySelector('[data-bs-target="#stock-actual"]');
                if (stockTab) stockTab.click();
            }
        });
    }

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            this.modals.set(modalId, modal);
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.classList.add('hidden');
            this.modals.delete(modal.id);
        }
    }

    closeAllModals() {
        document.querySelectorAll('.fixed.inset-0:not(.hidden)').forEach(modal => {
            this.closeModal(modal);
        });
    }

    showLoading(message = 'Procesando...') {
        const loadingModal = document.createElement('div');
        loadingModal.id = 'loading-modal';
        loadingModal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
        loadingModal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700 dark:text-gray-300">${message}</span>
            </div>
        `;
        document.body.appendChild(loadingModal);
    }

    hideLoading() {
        const loadingModal = document.getElementById('loading-modal');
        if (loadingModal) {
            loadingModal.remove();
        }
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showValidationErrors(errors) {
        let errorMessage = 'Se encontraron errores:\n\n';
        
        for (const field in errors) {
            if (errors.hasOwnProperty(field)) {
                errorMessage += `• ${Array.isArray(errors[field]) ? errors[field].join('\n• ') : errors[field]}\n`;
                
                // Marcar campo con error
                const input = document.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('border-red-500');
                }
            }
        }
        
        this.showError(errorMessage);
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm`;
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-500 text-white'
        };
        
        toast.className += ` ${colors[type] || colors.info}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Auto-remove después de 3 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Funciones para acciones desde botones
    async verDetalleProducto(productoId) {
        try {
            this.showLoading('Cargando detalles...');
            
            const response = await fetch(`/inventario/producto/${productoId}/detalle`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.mostrarModalDetalleProducto(result.data);
            } else {
                this.showError('Error al cargar detalles del producto');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión');
        } finally {
            this.hideLoading();
        }
    }

    async verHistorialProducto(productoId) {
        try {
            this.showLoading('Cargando historial...');
            
            const response = await fetch(`/inventario/producto/${productoId}/movimientos`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.mostrarModalHistorialProducto(result.data);
            } else {
                this.showError('Error al cargar historial del producto');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión');
        } finally {
            this.hideLoading();
        }
    }

    seleccionarProductoMovimiento(id) {
        console.log(`Seleccionar producto ${id} para movimiento`);
        const egresosTab = document.querySelector('[data-bs-target="#egresos"]');
        if (egresosTab) {
            egresosTab.click();
            // Preseleccionar el producto en el formulario de egresos
            setTimeout(() => {
                const select = document.getElementById('egreso_producto_id');
                if (select) {
                    select.value = id;
                    select.dispatchEvent(new Event('change'));
                }
            }, 500);
        }
    }

    mostrarModalDetalleProducto(producto) {
        const modal = document.getElementById('modal-detalle-producto');
        const contenido = document.getElementById('contenido-detalle-producto');
        
        if (!modal || !contenido) {
            console.error('Modal de detalle no encontrado');
            return;
        }
        
        let fotosHtml = '';
        if (producto.fotos && producto.fotos.length > 0) {
            fotosHtml = producto.fotos.map(foto => 
                `<img src="${foto.foto_url}" class="w-20 h-20 object-cover rounded-lg" alt="Foto del producto">`
            ).join('');
        }
        
        contenido.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">${producto.producto_nombre || 'Sin nombre'}</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Código de barras:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${producto.codigo_barra || 'Sin código'}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Stock actual:</dt>
                            <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${producto.stock_actual || 0}</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Categoría:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${producto.categoria || '-'}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Marca:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${producto.marca || '-'}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Requiere serie:</dt>
                            <dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${producto.requiere_serie ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">${producto.requiere_serie ? 'Sí' : 'No'}</span></dd>
                        </div>
                        ${producto.requiere_serie ? `
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Series disponibles:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${producto.series_disponibles || 0}</dd>
                        </div>
                        ` : ''}
                    </dl>
                </div>
                <div>
                    <h6 class="text-md font-medium text-gray-900 dark:text-white mb-3">Fotos del producto:</h6>
                    <div class="flex flex-wrap gap-2">
                        ${fotosHtml || '<p class="text-gray-500 dark:text-gray-400">Sin fotos disponibles</p>'}
                    </div>
                </div>
            </div>
        `;
        
        this.showModal('modal-detalle-producto');
    }

    mostrarModalHistorialProducto(movimientos) {
        const modal = document.getElementById('modal-historial-producto');
        const tbody = document.querySelector('#tabla-historial-producto tbody');
        
        if (!modal || !tbody) {
            console.error('Modal de historial no encontrado');
            return;
        }
        
        tbody.innerHTML = '';
        
        if (!movimientos || movimientos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        No hay movimientos registrados para este producto
                    </td>
                </tr>
            `;
        } else {
            movimientos.forEach(mov => {
                const row = document.createElement('tr');
                const tipoBadge = this.getTipoBadge(mov.tipo);
                
                row.innerHTML = `
                    <td class="px-4 py-2 text-sm">${mov.fecha}</td>
                    <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${tipoBadge}">${mov.tipo}</span></td>
                    <td class="px-4 py-2 text-sm text-center">${mov.cantidad}</td>
                    <td class="px-4 py-2 text-sm">${mov.origen}</td>
                    <td class="px-4 py-2 text-sm">${mov.usuario}</td>
                    <td class="px-4 py-2 text-sm">${mov.observaciones || '-'}</td>
                `;
                
                tbody.appendChild(row);
            });
        }
        
        this.showModal('modal-historial-producto');
    }

    renderHistorialTable(movimientos) {
        const tbody = document.querySelector('#tbody-historial-movimientos');
        if (!tbody) return;

        // Remover loading
        const loadingRow = document.getElementById('loading-historial');
        if (loadingRow) loadingRow.remove();

        tbody.innerHTML = '';

        if (!movimientos || movimientos.length === 0) {
            this.showEmptyHistorialTable();
            return;
        }

        movimientos.forEach(mov => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 dark:hover:bg-gray-700';
            
            const tipoBadge = this.getTipoBadge(mov.tipo);
            
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    ${mov.fecha || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${tipoBadge}">
                        ${mov.tipo || '-'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    ${mov.producto_nombre || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-900 dark:text-white">
                    ${mov.cantidad || 0}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${mov.origen || '-'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    ${mov.usuario || '-'}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                    ${mov.observaciones || '-'}
                </td>
            `;
            
            tbody.appendChild(row);
        });
    }

    getTipoBadge(tipo) {
        const badges = {
            'Ingreso': 'bg-green-100 text-green-800',
            'ingreso': 'bg-green-100 text-green-800',
            'Egreso': 'bg-blue-100 text-blue-800',
            'egreso': 'bg-blue-100 text-blue-800',
            'Baja': 'bg-red-100 text-red-800',
            'baja': 'bg-red-100 text-red-800',
            'Venta': 'bg-purple-100 text-purple-800',
            'venta': 'bg-purple-100 text-purple-800',
            'Devolucion': 'bg-yellow-100 text-yellow-800',
            'devolucion': 'bg-yellow-100 text-yellow-800',
            'Prestamo': 'bg-indigo-100 text-indigo-800',
            'prestamo': 'bg-indigo-100 text-indigo-800'
        };
        return badges[tipo] || 'bg-gray-100 text-gray-800';
    }

    // Funciones de exportación
    async exportarStock(formato) {
        console.log(`Exportando stock en formato ${formato}`);
        
        try {
            this.showLoading('Generando archivo...');
            
            const response = await fetch(`/inventario/exportar-stock?formato=${formato}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `inventario_stock.${formato}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                this.showSuccess(`Stock exportado en formato ${formato.toUpperCase()}`);
            } else {
                throw new Error('Error en la exportación');
            }
            
        } catch (error) {
            console.error('Error al exportar:', error);
            this.showError('Error al exportar los datos');
        } finally {
            this.hideLoading();
        }
    }

    async exportarMovimientos(formato) {
        console.log(`Exportando movimientos en formato ${formato}`);
        
        try {
            this.showLoading('Generando archivo...');
            
            const response = await fetch(`/inventario/exportar-movimientos?formato=${formato}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `movimientos_inventario.${formato}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                this.showSuccess(`Movimientos exportados en formato ${formato.toUpperCase()}`);
            } else {
                throw new Error('Error en la exportación');
            }
            
        } catch (error) {
            console.error('Error al exportar:', error);
            this.showError('Error al exportar los datos');
        } finally {
            this.hideLoading();
        }
    }
}

// productos para ingreso de produtos aqui empieza 


document.addEventListener('DOMContentLoaded', function() {
    // Cálculo automático del margen
    function calcularMargen() {
        const costo = parseFloat(document.getElementById('precio_costo').value) || 0;
        const venta = parseFloat(document.getElementById('precio_venta').value) || 0;
        
        if (costo > 0 && venta > 0) {
            const margen = ((venta - costo) / costo) * 100;
            document.getElementById('margen_calculado').value = margen.toFixed(2) + '%';
        } else {
            document.getElementById('margen_calculado').value = '';
        }
    }

    // Eventos para cálculo de margen
    document.getElementById('precio_costo').addEventListener('input', calcularMargen);
    document.getElementById('precio_venta').addEventListener('input', calcularMargen);

    // Toggle promoción
    document.getElementById('btn-toggle-promocion').addEventListener('click', function() {
        const panel = document.getElementById('panel-promocion');
        const btn = this;
        
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            btn.textContent = 'Ocultar Promoción';
            btn.classList.remove('bg-purple-100', 'hover:bg-purple-200', 'text-purple-700');
            btn.classList.add('bg-red-100', 'hover:bg-red-200', 'text-red-700');
        } else {
            panel.classList.add('hidden');
            btn.textContent = 'Agregar Promoción';
            btn.classList.remove('bg-red-100', 'hover:bg-red-200', 'text-red-700');
            btn.classList.add('bg-purple-100', 'hover:bg-purple-200', 'text-purple-700');
        }
    });

    // Mostrar información de licencia seleccionada
    document.getElementById('producto_id_licencia').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const infoLicencia = document.getElementById('info-licencia');
        
        if (selectedOption.value) {
            const vencimiento = selectedOption.dataset.vencimiento;
            const fechaVenc = new Date(vencimiento);
            const hoy = new Date();
            
            document.getElementById('fecha-vencimiento').textContent = fechaVenc.toLocaleDateString();
            
            const estadoSpan = document.getElementById('estado-licencia');
            if (fechaVenc > hoy) {
                estadoSpan.textContent = 'Vigente';
                estadoSpan.className = 'font-medium text-green-600';
            } else {
                estadoSpan.textContent = 'Vencida';
                estadoSpan.className = 'font-medium text-red-600';
            }
            
            infoLicencia.classList.remove('hidden');
        } else {
            infoLicencia.classList.add('hidden');
        }
    });

    // Vista previa del producto
    document.getElementById('btn-vista-previa').addEventListener('click', function() {
        const formData = new FormData(document.getElementById('form-ingresar-producto'));
        mostrarVistaPrevia(formData);
    });

    function mostrarVistaPrevia(formData) {
        const contenido = document.getElementById('contenido-vista-previa');
        
        // Obtener valores del formulario
        const nombre = formData.get('producto_nombre') || 'Sin nombre';
        const codigo = formData.get('producto_codigo_barra') || 'Sin código';
        const requiereSerie = formData.get('producto_requiere_serie') === 'on';
        const esImportado = formData.get('producto_es_importado') === 'on';
        const precioVenta = formData.get('precio_venta') || '0';
        const precioCosto = formData.get('precio_costo') || '0';
        const precioEspecial = formData.get('precio_especial') || '';
        
        const html = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información General</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Nombre:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${nombre}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Código:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${codigo}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Requiere Serie:</dt>
                            <dd class="text-sm">
                                <span class="px-2 py-1 text-xs rounded-full ${requiereSerie ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                    ${requiereSerie ? 'Sí' : 'No'}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Importado:</dt>
                            <dd class="text-sm">
                                <span class="px-2 py-1 text-xs rounded-full ${esImportado ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                                    ${esImportado ? 'Sí' : 'No'}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Precios</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Precio de Costo:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${parseFloat(precioCosto).toFixed(2)}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Precio de Venta:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${parseFloat(precioVenta).toFixed(2)}</dd>
                        </div>
                        ${precioEspecial ? `
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Precio Especial:</dt>
                            <dd class="text-sm text-green-600 font-medium">${parseFloat(precioEspecial).toFixed(2)}</dd>
                        </div>
                        ` : ''}
                    </dl>
                </div>
            </div>
        `;
        
        contenido.innerHTML = html;
        document.getElementById('modal-vista-previa').classList.remove('hidden');
    }

    // Establecer fecha mínima para promociones (hoy)
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('promo_fecha_inicio').min = hoy;
    document.getElementById('promo_fecha_fin').min = hoy;

    // Validar que fecha fin sea posterior a fecha inicio
    document.getElementById('promo_fecha_inicio').addEventListener('change', function() {
        document.getElementById('promo_fecha_fin').min = this.value;
    });
});

// Cerrar modales
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
        document.querySelectorAll('.fixed.inset-0').forEach(modal => {
            modal.classList.add('hidden');
        });
    }
    
    // Cerrar modal al hacer clic fuera
    if (e.target.classList.contains('bg-gray-500') && e.target.classList.contains('bg-opacity-75')) {
        const modal = e.target.closest('.fixed.inset-0');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
});


// aqui finaliza para ingreso de productos 

/**
 * INICIALIZACIÓN GLOBAL
 */
let inventarioSistema;

// Inicializar cuando esté listo
document.addEventListener('DOMContentLoaded', function() {
    inventarioSistema = new InventarioSistema();
    
    // Exponer funciones globalmente para compatibilidad con HTML
    window.inventario = inventarioSistema;
    
    // Funciones globales para eventos onclick
    
    window.verDetalleProducto = (id) => inventarioSistema.verDetalleProducto(id);
    window.verHistorialProducto = (id) => inventarioSistema.verHistorialProducto(id);
    window.seleccionarProductoMovimiento = (id) => inventarioSistema.seleccionarProductoMovimiento(id);
    
    console.log('✅ Sistema de inventario cargado y listo para usar');
});

// Manejo de errores globales
window.addEventListener('error', function(e) {
    console.error('Error JavaScript en sistema de inventario:', e.error);
});

console.log('📦 Sistema de Inventario - Armería v2.0 cargado completamente');