@extends('layouts.app')

@section('title', 'Sistema de Inventario - Armería')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                Sistema de Inventario
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Gestión integral para armería - Productos, stock y movimientos
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <!-- Alertas -->
            <div class="relative">
                <button onclick="inventarioManager.toggleAlertas()" 
                        class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-bell mr-2"></i>
                    Alertas
                    <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center" id="alertas-badge">0</span>
                </button>
            </div>

            <button onclick="inventarioManager.openRegistroModal()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-plus mr-2"></i>
                Registrar Producto
            </button>
            <button onclick="inventarioManager.openIngresoModal()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                <i class="fas fa-box-open mr-2"></i>
                Ingreso a Inventario
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-boxes text-gray-400 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Productos</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="total-productos">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-warehouse text-green-400 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Stock Total</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="stock-total">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Stock Bajo</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="stock-bajo">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400 text-xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Agotados</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="stock-agotado">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar producto</label>
                    <input type="text" 
                           id="search-productos"
                           placeholder="Nombre, SKU o código de barra..."
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                    <select id="filter-categoria"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Todas las categorías</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado de stock</label>
                    <select id="filter-stock"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="disponible">Con stock</option>
                        <option value="bajo">Stock bajo</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="inventarioManager.clearFilters()" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Lista de Productos -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Productos</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        <span id="productos-count">0</span> productos
                    </span>
                </div>
                
                <div class="overflow-hidden">
                    <div class="max-h-96 overflow-y-auto">
                        
                            <!-- Estado vacío inicial -->
                            <div id="empty-state" class="text-center py-12">
                                <i class="fas fa-boxes text-gray-400 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No hay productos registrados</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Comienza registrando tu primer producto en el inventario</p>
                                <button onclick="inventarioManager.openRegistroModal()" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                                    <i class="fas fa-plus mr-2"></i>
                                    Registrar Primer Producto
                                </button>
                            </div>
                             <!-- AGREGA ESTA LÍNEA -->
        <div id="productos-list" class="space-y-3">
            <!-- Los productos se cargarán aquí dinámicamente -->
        </div>
                      
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="space-y-6">
            <!-- Alertas Recientes -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Alertas</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            <span id="alertas-count">0</span> alertas
                        </span>
                    </div>
                    
                    <div id="alertas-list" class="space-y-2">
                        <div class="text-center py-6">
                            <i class="fas fa-check-circle text-green-400 text-2xl mb-2"></i>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No hay alertas pendientes</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Acciones Rápidas</h3>
                    <div class="space-y-3">
                        <button onclick="inventarioManager.openEgresoModal()" 
                                class="w-full inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-orange-700 bg-orange-100 hover:bg-orange-200 dark:bg-orange-900 dark:text-orange-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Registrar Egreso
                        </button>
                        <button onclick="inventarioManager.verHistorial()" 
                                class="w-full inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fas fa-history mr-2"></i>
                            Ver Historial
                        </button>
                        <button onclick="inventarioManager.generarReporte()" 
                                class="w-full inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Generar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Producto -->
    <div id="registro-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="inventarioManager.closeModal('registro')"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                
                <form id="registro-form">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Registrar Nuevo Producto</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Información básica del producto</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Nombre del producto -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del producto *</label>
                            <input type="text" 
                                   id="producto_nombre"
                                   name="producto_nombre"
                                   required
                                   placeholder="Ej: Glock 19 Gen 5"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <div id="producto_nombre_error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- Categoría -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría *</label>
                            <select id="producto_categoria"
                                    name="producto_categoria_id"
                                    required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Seleccionar categoría</option>
                            </select>
                            <div id="producto_categoria_id_error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- Subcategoría -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subcategoría *</label>
                            <select id="producto_subcategoria"
                                    name="producto_subcategoria_id"
                                    required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Seleccionar subcategoría</option>
                            </select>
                            <div id="producto_subcategoria_id_error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- Marca -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marca *</label>
                            <select id="producto_marca"
                                    name="producto_marca_id"
                                    required
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Seleccionar marca</option>
                            </select>
                            <div id="producto_marca_id_error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>

                        <!-- Modelo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</label>
                            <select id="producto_modelo"
                                    name="producto_modelo_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Seleccionar modelo</option>
                            </select>
                        </div>

                        <!-- Calibre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Calibre</label>
                            <select id="producto_calibre"
                                    name="producto_calibre_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Seleccionar calibre</option>
                            </select>
                        </div>


                        <!-- País de fabricación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">País de fabricación</label>
                            <select id="producto_madein"
                                    name="producto_madein"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Seleccionar país</option>
                            </select>
                        </div>

                        <!-- Código de barra (ya existente) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código de barra</label>
                            <input type="text" 
                                id="producto_codigo_barra"
                                name="producto_codigo_barra"
                                placeholder="Opcional"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <!-- Stock mínimo y máximo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock mínimo <small class="text-gray-500">(para alertas)</small></label>
                            <input type="number" 
                                id="producto_stock_minimo"
                                name="producto_stock_minimo"
                                min="0"
                                value="0"
                                placeholder="Ej: 5"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <small class="text-xs text-gray-500 dark:text-gray-400">Se generará alerta cuando el stock esté por debajo de este número</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock máximo <small class="text-gray-500">(recomendado)</small></label>
                            <input type="number" 
                                id="producto_stock_maximo"
                                name="producto_stock_maximo"
                                min="0"
                                value="0"
                                placeholder="Ej: 50"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <small class="text-xs text-gray-500 dark:text-gray-400">Stock máximo recomendado para reportes</small>
                        </div>

                        <!-- Requiere serie -->
                        <div class="md:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="producto_requiere_serie"
                                       name="producto_requiere_serie"
                                       value="1"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="producto_requiere_serie" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                    Este producto requiere número de serie (armas)
                                </label>
                            </div>
                        </div>
                        
                        <!-- Sección de Fotos -->
                        <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" 
                                    id="agregar_fotos"
                                    name="agregar_fotos"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="agregar_fotos" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    ¿Desea agregar fotos a este producto?
                                </label>
                            </div>

                            <!-- Sección de upload de fotos (oculta por defecto) -->
                            <div id="seccion_fotos" class="hidden">
                                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6">
                                    <div id="foto_drop_zone" class="text-center cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors rounded-lg p-4">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                            <span class="font-medium">Arrastra fotos aquí</span> o haz clic para seleccionar
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-500">
                                            Máximo 5 fotos • JPG, PNG, WebP • Hasta 2MB cada una
                                        </p>
                                        <input type="file" 
                                            id="fotos_producto" 
                                            name="fotos[]" 
                                            multiple 
                                            accept="image/jpeg,image/jpg,image/png,image/webp"
                                            class="hidden">
                                    </div>

                                    <!-- Preview de fotos seleccionadas -->
                                    <div id="preview_fotos" class="mt-4 flex flex-wrap gap-3">
                                        <!-- Las previews se cargarán aquí dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                onclick="inventarioManager.closeModal('registro')" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" 
                                id="registro-submit-btn"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="registro-submit-text">Registrar Producto</span>
                            <span id="registro-loading" class="hidden flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


<!-- Modal de Gestión de Fotos (agregar después de los modales existentes) -->
<div id="fotos-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="inventarioManager.closeModal('fotos')"></div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Gestión de Fotos</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Administrar fotos del producto</p>
            </div>

            <!-- Fotos existentes -->
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Fotos actuales</h4>
                <div id="fotos_existentes" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4">
                    <!-- Las fotos se cargarán aquí dinámicamente -->
                </div>
            </div>

            <!-- Subir nuevas fotos -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Agregar nuevas fotos</h4>
                <div class="flex items-center space-x-4">
                    <input type="file" 
                           id="nuevas_fotos" 
                           multiple 
                           accept="image/jpeg,image/jpg,image/png,image/webp"
                           class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <button type="button" 
                            onclick="inventarioManager.subirNuevasFotos()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">
                        <i class="fas fa-upload mr-2"></i>
                        Subir
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Máximo 5 fotos total por producto
                </p>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button" 
                        onclick="inventarioManager.closeModal('fotos')" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
    <!-- Modal Ingreso a Inventario -->
    <div id="ingreso-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="inventarioManager.closeModal('ingreso')"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                
                <form id="ingreso-form">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ingreso a Inventario</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Agregar stock de productos existentes</p>
                    </div>

                    <div id="ingreso-step-1">
                        <!-- Selección de producto -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar producto *</label>
                            <input type="text" 
                                   id="buscar_producto"
                                   placeholder="Escribir nombre, SKU o código..."
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <div id="productos_encontrados" class="mt-2 max-h-48 overflow-y-auto hidden">
                                <!-- Resultados de búsqueda -->
                            </div>
                        </div>
                    </div>

                    <div id="ingreso-step-2" class="hidden">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100" id="producto_seleccionado_nombre">Producto seleccionado</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400" id="producto_seleccionado_info">Stock actual: 0</p>
                        </div>

                        <!-- Tipo de ingreso -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de movimiento *</label>
                                <select id="mov_tipo"
                                        name="mov_tipo"
                                        required
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                    <option value="ingreso">Ingreso (Compra)</option>
                                    <option value="ajuste_positivo">Ajuste Positivo</option>
                                    <option value="devolucion">Devolución</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Origen *</label>
                                <input type="text" 
                                       id="mov_origen"
                                       name="mov_origen"
                                       required
                                       placeholder="Ej: Proveedor ABC, Importación"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Cantidad o Series -->
                        <div id="cantidad_section" class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad *</label>
                            <input type="number" 
                                   id="mov_cantidad"
                                   name="mov_cantidad"
                                   min="1"
                                   required
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                        </div>

                        <div id="series_section" class="mt-4 hidden">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Números de serie *</label>
                            <textarea id="numeros_series" 
                                      name="numeros_series"
                                      rows="4"
                                      placeholder="Un número de serie por línea&#10;Ejemplo:&#10;GLK123456&#10;GLK123457&#10;GLK123458"
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
                        </div>

                        <!-- Observaciones -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                            <textarea id="mov_observaciones" 
                                      name="mov_observaciones"
                                      rows="2"
                                      placeholder="Detalles adicionales del ingreso..."
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                onclick="inventarioManager.closeModal('ingreso')" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" 
                                id="ingreso-submit-btn"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="ingreso-submit-text">Procesar Ingreso</span>
                            <span id="ingreso-loading" class="hidden flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@vite('resources/js/inventario/index.js')