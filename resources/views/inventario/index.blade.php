@extends('layouts.app')

@section('title', 'Control de Inventario')

@section('content')
<style>
        /* Estilos para las transiciones de pestañas */
        .tab-pane {
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .tab-pane.active {
            display: block;
            opacity: 1;
        }
        
        /* Estilos para los botones de pestañas */
        .tab-button {
            position: relative;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }
        
        .tab-button.active {
            border-bottom-color: #3b82f6;
            color: #3b82f6;
        }
        
        .tab-button:not(.active) {
            color: #6b7280;
        }
        
        .tab-button:not(.active):hover {
            color: #374151;
            border-bottom-color: #d1d5db;
        }
    </style>


<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header Principal -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600"></div>
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-4 mb-6 lg:mb-0">
                    <div class="flex-shrink-0 bg-white bg-opacity-20 backdrop-blur-sm rounded-xl p-4">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="text-white">
                        <h1 class="text-3xl font-bold tracking-tight">Control de Inventario</h1>
                        <p class="text-blue-100 text-lg">Sistema Integral de Armería</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard de Métricas -->
    <div class="px-4 py-6 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Cards de métricas -->
            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Productos Totales</p>
                        <span class="text-3xl font-bold text-gray-900 dark:text-white" id="total-productos">126</span>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-blue-100 dark:bg-blue-900 rounded-xl p-3">
                            <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Series Disponibles</p>
                        <span class="text-3xl font-bold text-gray-900 dark:text-white" id="total-series">87</span>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-green-100 dark:bg-green-900 rounded-xl p-3">
                            <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Movimientos Hoy</p>
                        <span class="text-3xl font-bold text-gray-900 dark:text-white" id="movimientos-hoy">23</span>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-purple-100 dark:bg-purple-900 rounded-xl p-3">
                            <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7h12m0 0l-4-4m4 4l-4 4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Egresos del Mes</p>
                        <span class="text-3xl font-bold text-gray-900 dark:text-white" id="egresos-mes">15</span>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-red-100 dark:bg-red-900 rounded-xl p-3">
                            <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Alertas de Stock</p>
                        <span class="text-3xl font-bold text-gray-900 dark:text-white" id="stock-bajo">3</span>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-amber-100 dark:bg-amber-900 rounded-xl p-3">
                            <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 9v2m0 4h.01"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Principal con Pestañas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Header de Pestañas CORREGIDO -->
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-50 to-gray-100 dark:from-gray-800 dark:to-gray-900"></div>
                <div class="relative border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <!-- Pestaña 1: Stock Actual -->
                        <button 
                            class="tab-button active whitespace-nowrap py-4 px-1 font-semibold text-sm transition-all duration-200 flex items-center space-x-2"
                            data-tab="stock-actual">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>Stock Actual</span>
                        </button>
                        
                        <!-- Pestaña 2: Ingresar Producto -->
                        <button 
                            class="tab-button whitespace-nowrap py-4 px-1 font-semibold text-sm transition-all duration-200 flex items-center space-x-2"
                            data-tab="ingresar-producto">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Ingresar Producto</span>
                        </button>
                        
                        <!-- Pestaña 3: Egresos -->
                        <button 
                            class="tab-button whitespace-nowrap py-4 px-1 font-semibold text-sm transition-all duration-200 flex items-center space-x-2"
                            data-tab="egresos">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                            </svg>
                            <span>Egresos</span>
                        </button>
                        
                        <!-- Pestaña 4: Movimientos -->
                        <button 
                            class="tab-button whitespace-nowrap py-4 px-1 font-semibold text-sm transition-all duration-200 flex items-center space-x-2"
                            data-tab="movimientos">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7h12m0 0l-4-4m4 4l-4 4"/>
                            </svg>
                            <span>Movimientos</span>
                        </button>
                        
                        <!-- Pestaña 5: Gráficas -->
                        <button 
                            class="tab-button whitespace-nowrap py-4 px-1 font-semibold text-sm transition-all duration-200 flex items-center space-x-2"
                            data-tab="graficas">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"/>
                            </svg>
                            <span>Gráficas</span>
                        </button>
                        
                        <!-- Pestaña 6: Historial -->
                        <button 
                            class="tab-button whitespace-nowrap py-4 px-1 font-semibold text-sm transition-all duration-200 flex items-center space-x-2"
                            data-tab="historial">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Historial</span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Contenido de las Pestañas -->
            <div class="tab-content p-6" id="inventarioTabContent">
                <!-- Aquí van las demás secciones de la vista -->



                <!-- TAB 1: Stock Actual -->
<div class="tab-pane fade show active" id="stock-actual" role="tabpanel" aria-labelledby="stock-tab">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="h-6 w-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Inventario Actual
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Vista completa de productos en stock</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 mt-4 lg:mt-0">
            <button onclick="inventario.exportarStock('excel')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar Excel
            </button>
            <button onclick="inventario.exportarStock('pdf')" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar PDF
            </button>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-blue-200 dark:border-gray-600 p-6 mb-6">
        <div class="flex items-center mb-4">
            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros de Búsqueda</h3>
        </div>
        
        <form id="form-filtros-stock">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="filtro-categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoría</label>
                    <select id="filtro-categoria" name="categoria" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->categoria_id }}">{{ $categoria->categoria_nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filtro-marca" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Marca</label>
                    <select id="filtro-marca" name="marca" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todas las marcas</option>
                        @foreach($marcas as $marca)
                            <option value="{{ $marca->marca_id }}">{{ $marca->marca_descripcion }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filtro-stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado de Stock</label>
                    <select id="filtro-stock" name="estado_stock" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos los estados</option>
                        <option value="normal">Stock Normal</option>
                        <option value="bajo">Stock Bajo</option>
                        <option value="agotado">Sin Stock</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="button" id="btn-aplicar-filtros" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Filtrar
                    </button>
                    <button type="button" id="btn-limpiar-filtros" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Cards de Resumen Rápido -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Normal</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="count-stock-normal">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stock Bajo</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="count-stock-bajo">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sin Stock</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="count-stock-agotado">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Con Series</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="count-con-series">--</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Stock -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Productos en Inventario</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Total: </span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white" id="total-productos-tabla">0</span>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table id="tabla-stock" class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-1">
                                <span>Código</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center space-x-1">
                                <span>Producto</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marca</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modelo</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <div class="flex items-center justify-center space-x-1">
                                <span>Stock</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Series</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Los datos se cargarán dinámicamente -->
                    <tr id="loading-row">
                        <td colspan="9" class="px-6 py-8 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                <span class="text-gray-500 dark:text-gray-400">Cargando productos...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación personalizada -->
        <div class="bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Mostrar</span>
                    <select id="tabla-stock-length" class="border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 text-sm dark:bg-gray-700 dark:text-white">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-700 dark:text-gray-300">registros</span>
                </div>
                
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300" id="tabla-stock-info">
                        Mostrando 0 a 0 de 0 registros
                    </span>
                </div>
                
                <div class="flex items-center space-x-1" id="tabla-stock-pagination">
                    <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50" disabled>
                        Anterior
                    </button>
                    <button class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md">1</button>
                    <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50" disabled>
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="mt-6 bg-gradient-to-r from-gray-50 to-blue-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Acciones Rápidas
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Nueva Venta -->
            <button onclick="window.location.href='/ventas/nueva'" class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-white dark:hover:bg-gray-700 transition-colors text-left group">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-800 transition-colors">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Nueva Venta</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Registrar venta</p>
                </div>
            </button>

            <!-- Agregar Producto -->
            <button onclick="$('#ingreso-tab').tab('show')" class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-white dark:hover:bg-gray-700 transition-colors text-left group">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition-colors">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Nuevo Producto</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Agregar inventario</p>
                </div>
            </button>

            <!-- Registrar Egreso -->
            <button onclick="$('#egresos-tab').tab('show')" class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-white dark:hover:bg-gray-700 transition-colors text-left group">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center group-hover:bg-red-200 dark:group-hover:bg-red-800 transition-colors">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Registrar Egreso</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Traslados y bajas</p>
                </div>
            </button>

            <!-- Ver Reportes -->
            <button onclick="$('#graficas-tab').tab('show')" class="flex items-center space-x-3 p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-white dark:hover:bg-gray-700 transition-colors text-left group">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition-colors">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Ver Reportes</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Análisis y gráficas</p>
                </div>
            </button>
        </div>
    </div>
</div>









<!-- TAB 2: Ingresar Producto -->
<div class="tab-pane fade" id="ingresar-producto" role="tabpanel" aria-labelledby="ingreso-tab">
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl mb-4">
            <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Nuevo Producto</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Registra un nuevo producto en el inventario con información completa</p>
    </div>

    <form id="form-ingresar-producto" enctype="multipart/form-data" class="space-y-8">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- COLUMNA 1: Información del Producto -->
            <div class="space-y-6">
                <!-- Información Básica -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-blue-200 dark:border-gray-600 overflow-hidden">
                    <div class="bg-blue-600 dark:bg-blue-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Información del Producto
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Nombre del Producto -->
                        <div>
                            <label for="producto_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nombre del Producto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="producto_nombre" 
                                   name="producto_nombre" 
                                   placeholder="Ej: Glock 17 Gen 5" 
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <div class="invalid-feedback hidden"></div>
                        </div>

                        <!-- Código de Barras -->
                        <div>
                            <label for="producto_codigo_barra" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Código de Barras</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <input type="text" 
                                       id="producto_codigo_barra" 
                                       name="producto_codigo_barra" 
                                       placeholder="Escanear o ingresar manualmente"
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Opcional - Facilita la identificación rápida</p>
                        </div>

                        <!-- Categoría y Subcategoría -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="producto_categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <select id="producto_categoria_id" name="producto_categoria_id" required
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->categoria_id }}">{{ $categoria->categoria_nombre }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback hidden"></div>
                            </div>
                            <div>
                                <label for="producto_subcategoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Subcategoría <span class="text-red-500">*</span>
                                </label>
                                <select id="producto_subcategoria_id" name="producto_subcategoria_id" required
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar categoría primero...</option>
                                </select>
                                <div class="invalid-feedback hidden"></div>
                            </div>
                        </div>

                        <!-- Marca y Modelo -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="producto_marca_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Marca <span class="text-red-500">*</span>
                                </label>
                                <select id="producto_marca_id" name="producto_marca_id" required
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($marcas as $marca)
                                        <option value="{{ $marca->marca_id }}">{{ $marca->marca_descripcion }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback hidden"></div>
                            </div>
                            <div>
                                <label for="producto_modelo_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Modelo</label>
                                <select id="producto_modelo_id" name="producto_modelo_id"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($modelos as $modelo)
                                        <option value="{{ $modelo->modelo_id }}">{{ $modelo->modelo_descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Calibre -->
                        <div>
                            <label for="producto_calibre_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Calibre</label>
                            <select id="producto_calibre_id" name="producto_calibre_id"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($calibres as $calibre)
                                    <option value="{{ $calibre->calibre_id }}">{{ $calibre->calibre_nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Información de Precios -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-green-200 dark:border-gray-600 overflow-hidden">
                    <div class="bg-green-600 dark:bg-green-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Precios del Producto
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm text-blue-800 dark:text-blue-200">Los precios son opcionales pero recomendados para un control completo</p>
                            </div>
                        </div>

                        <!-- Precio de Costo y Venta -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="precio_costo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio de Costo</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" 
                                           id="precio_costo" 
                                           name="precio_costo" 
                                           step="0.01" 
                                           min="0"
                                           placeholder="0.00"
                                           class="w-full pl-8 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Precio de compra del producto</p>
                            </div>
                            <div>
                                <label for="precio_venta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio de Venta</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" 
                                           id="precio_venta" 
                                           name="precio_venta" 
                                           step="0.01" 
                                           min="0"
                                           placeholder="0.00"
                                           class="w-full pl-8 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Precio al público</p>
                            </div>
                        </div>

                        <!-- Precio Especial y Margen -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="precio_especial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio Especial</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">$</span>
                                    </div>
                                    <input type="number" 
                                           id="precio_especial" 
                                           name="precio_especial" 
                                           step="0.01" 
                                           min="0"
                                           placeholder="0.00"
                                           class="w-full pl-8 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Precio promocional (opcional)</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Margen de Ganancia</label>
                                <div class="relative">
                                    <input type="text" 
                                           id="margen_calculado" 
                                           readonly 
                                           placeholder="Se calcula automáticamente"
                                           class="w-full pr-8 pl-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Calculado automáticamente</p>
                            </div>
                        </div>

                        <!-- Gestión de Promociones -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white">Promoción Especial</h4>
                                <button type="button" id="btn-toggle-promocion" class="text-sm bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1 rounded-md transition-colors">
                                    Agregar Promoción
                                </button>
                            </div>
                            
                            <div id="panel-promocion" class="hidden space-y-4">
                                <div class="bg-purple-50 dark:bg-purple-900 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="promo_nombre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre de la Promoción</label>
                                            <input type="text" 
                                                   id="promo_nombre" 
                                                   name="promo_nombre" 
                                                   placeholder="Ej: Black Friday 2025"
                                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                        <div>
                                            <label for="promo_tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Descuento</label>
                                            <select id="promo_tipo" name="promo_tipo" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">Seleccionar...</option>
                                                <option value="porcentaje">Porcentaje (%)</option>
                                                <option value="fijo">Monto Fijo ($)</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                                        <div>
                                            <label for="promo_valor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Valor del Descuento</label>
                                            <input type="number" 
                                                   id="promo_valor" 
                                                   name="promo_valor" 
                                                   step="0.01" 
                                                   min="0"
                                                   placeholder="25.00"
                                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                        <div>
                                            <label for="promo_fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Inicio</label>
                                            <input type="date" 
                                                   id="promo_fecha_inicio" 
                                                   name="promo_fecha_inicio" 
                                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                        <div>
                                            <label for="promo_fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Fin</label>
                                            <input type="date" 
                                                   id="promo_fecha_fin" 
                                                   name="promo_fecha_fin" 
                                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label for="promo_justificacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Justificación de la Promoción</label>
                                        <textarea id="promo_justificacion" 
                                                  name="promo_justificacion" 
                                                  rows="2" 
                                                  placeholder="Motivo o descripción de la promoción..."
                                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Justificación del Precio -->
                        <div>
                            <label for="precio_justificacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Justificación del Precio</label>
                            <textarea id="precio_justificacion" 
                                      name="precio_justificacion" 
                                      rows="3" 
                                      placeholder="Motivo del precio especial o comentarios sobre el precio..."
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA 2: Configuración e Inventario -->
            <div class="space-y-6">
                <!-- Configuración del Producto -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-purple-200 dark:border-gray-600 overflow-hidden">
                    <div class="bg-purple-600 dark:bg-purple-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Configuración del Producto
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Control de Series -->
                        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="producto_requiere_serie" 
                                       name="producto_requiere_serie" 
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <div class="ml-3">
                                    <label for="producto_requiere_serie" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Requiere Número de Serie
                                    </label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Para productos con control individual</p>
                                </div>
                            </div>
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>

                        <!-- Producto Importado -->
                        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="producto_es_importado" 
                                       name="producto_es_importado" 
                                       class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <div class="ml-3">
                                    <label for="producto_es_importado" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Producto Importado
                                    </label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Requiere licencia de importación</p>
                                </div>
                            </div>
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <!-- Licencia de Importación (oculto por defecto) -->
                        <div id="grupo-licencia" class="hidden">
                            <label for="producto_id_licencia" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Licencia de Importación <span class="text-red-500">*</span>
                            </label>
                            <select id="producto_id_licencia" name="producto_id_licencia"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar licencia...</option>
                                @if(isset($licencias))
                                    @foreach($licencias as $licencia)
                                        <option value="{{ $licencia->lipaimp_id }}" data-poliza="{{ $licencia->lipaimp_poliza }}" data-vencimiento="{{ $licencia->lipaimp_fecha_vencimiento }}">
                                            Licencia #{{ $licencia->lipaimp_poliza }} - {{ $licencia->lipaimp_descripcion }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div id="info-licencia" class="mt-2 text-sm text-gray-500 dark:text-gray-400 hidden">
                                <p><strong>Vencimiento:</strong> <span id="fecha-vencimiento">--</span></p>
                                <p><strong>Estado:</strong> <span id="estado-licencia" class="font-medium">--</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gestión de Inventario -->
                <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-amber-200 dark:border-gray-600 overflow-hidden">
                    <div class="bg-amber-600 dark:bg-amber-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Inventario Inicial
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Información del Movimiento -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="mov_origen" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Origen del Producto <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="mov_origen" 
                                       name="mov_origen" 
                                       placeholder="Ej: Compra, Importación, Traslado" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div id="grupo-cantidad">
                                <label for="cantidad_inicial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cantidad Inicial <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       id="cantidad_inicial" 
                                       name="cantidad_inicial" 
                                       min="1" 
                                       placeholder="1"
                                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>

                        <!-- Código de Lote (SIEMPRE VISIBLE AHORA) -->
                        <div id="grupo-lote">
                            <label for="lote_codigo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Código de Lote
                                <span class="text-sm text-gray-500 dark:text-gray-400">(Opcional para rastreo)</span>
                            </label>
                            <input type="text" 
                                   id="lote_codigo" 
                                   name="lote_codigo" 
                                   placeholder="Ej: L2025-01-GLOCK-001"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Útil para rastrear origen de importación o compra. Formato sugerido: L[AÑO]-[MES]-[MARCA]-[SECUENCIAL]
                            </p>
                        </div>

                        <!-- Gestión de Series (oculto por defecto) -->
                        <div id="grupo-series" class="hidden">
                            <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-4">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                        <strong>Importante:</strong> Los productos con serie pueden tener lote de origen para rastreo de importación
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Números de Serie <span class="text-red-500">*</span>
                                </label>
                                <button type="button" 
                                        id="btn-add-serie" 
                                        class="btn-add-serie inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Agregar Serie
                                </button>
                            </div>
                            <div id="container-series" class="space-y-2">
                                <div class="flex space-x-2 serie-input-group">
                                    <input type="text" 
                                           name="series[]" 
                                           placeholder="Número de serie único" 
                                           class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white serie-input">
                                    <button type="button" 
                                            class="px-3 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 btn-remove-serie">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Cada número de serie debe ser único en el sistema. El lote ayuda a rastrear el origen.
                            </p>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label for="mov_observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observaciones</label>
                            <textarea id="mov_observaciones" 
                                      name="mov_observaciones" 
                                      rows="3" 
                                      placeholder="Comentarios adicionales sobre el ingreso del producto..."
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Fotos del Producto -->
                <div class="bg-gradient-to-r from-pink-50 to-rose-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-pink-200 dark:border-gray-600 overflow-hidden">
                    <div class="bg-pink-600 dark:bg-pink-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Fotos del Producto
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-gray-400 dark:hover:border-gray-500 transition-colors duration-200">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <div class="mt-4">
                                    <label for="fotos" class="cursor-pointer">
                                        <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                                            Subir fotos del producto
                                        </span>
                                        <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                                            PNG, JPG, GIF hasta 2MB cada una
                                        </span>
                                    </label>
                                    <input id="fotos" 
                                           name="fotos[]" 
                                           type="file" 
                                           multiple 
                                           accept="image/*" 
                                           class="sr-only">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Preview de fotos -->
                        <div id="preview-fotos" class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <button type="button" 
                    id="btn-limpiar-form" 
                    class="w-full sm:w-auto px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Limpiar Formulario
            </button>
            
            <div class="flex space-x-3">
                <button type="button" 
                        id="btn-vista-previa" 
                        class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Vista Previa
                </button>
                
                <button type="submit" 
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105">
                    <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ingresar Producto
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Vista Previa del Producto -->
<div id="modal-vista-previa" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Vista Previa del Producto
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 close-modal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div id="contenido-vista-previa" class="mt-3">
                    <!-- El contenido se carga dinámicamente -->
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript específico para el formulario de ingreso
<script>
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
</script> -->













<!-- TAB 3: Egresos -->
<div class="tab-pane fade" id="egresos" role="tabpanel" aria-labelledby="egresos-tab">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Egresos de Inventario
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Gestiona salidas por traslados, bajas y otros egresos (no ventas)</p>
        </div>
        
        <div class="mt-4 lg:mt-0">
            <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Las ventas se manejan en el módulo de ventas</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario de Egreso -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="bg-gradient-to-r from-red-600 to-pink-600 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">Registrar Egreso</h3>
                </div>
                
                <form id="form-registrar-egreso" class="p-6 space-y-6">
                    @csrf
                    
                    <!-- Selección de Producto -->
                    <div>
                        <label for="egreso_producto_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Producto <span class="text-red-500">*</span>
                        </label>
                        <select id="egreso_producto_id" name="producto_id" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Buscar y seleccionar producto...</option>
                        </select>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Solo productos con stock disponible</p>
                    </div>

                    <!-- Tipo de Egreso -->
                    <div>
                        <label for="egreso_tipo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tipo de Egreso <span class="text-red-500">*</span>
                        </label>
                        <select id="egreso_tipo" name="mov_tipo" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Seleccionar tipo...</option>
                            <option value="egreso">Egreso - Traslado/Entrega</option>
                            <option value="baja">Baja - Producto dañado/perdido</option>
                            <option value="devolucion">Devolución - Regreso a proveedor</option>
                            <option value="prestamo">Préstamo - Uso temporal</option>
                        </select>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                    <span>Egreso: Traslado a otra ubicación</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                    <span>Baja: Producto perdido/dañado</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                                    <span>Devolución: Regreso al proveedor</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-purple-500 rounded-full mr-2"></div>
                                    <span>Préstamo: Uso temporal</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Destino/Motivo y Cantidad -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="egreso_destino" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Destino/Motivo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="egreso_destino" 
                                   name="mov_origen" 
                                   placeholder="Ej: Sucursal Norte, Producto dañado"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div id="panel-cantidad-egreso" class="hidden">
                            <label for="egreso_cantidad" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cantidad <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="egreso_cantidad" 
                                   name="mov_cantidad" 
                                   min="1"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Stock disponible: <span id="stock-disponible-egreso" class="font-medium">--</span>
                            </p>
                        </div>
                    </div>

                    <!-- Responsable y Fecha Esperada de Retorno (para préstamos) -->
                    <div id="panel-prestamo" class="hidden">
                        <div class="bg-purple-50 dark:bg-purple-900 border border-purple-200 dark:border-purple-700 rounded-lg p-4">
                            <h4 class="text-md font-medium text-purple-900 dark:text-purple-100 mb-3">Información del Préstamo</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="prestamo_responsable" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Responsable <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="prestamo_responsable" 
                                           name="prestamo_responsable" 
                                           placeholder="Nombre del responsable"
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label for="prestamo_fecha_retorno" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Fecha Esperada de Retorno
                                    </label>
                                    <input type="date" 
                                           id="prestamo_fecha_retorno" 
                                           name="prestamo_fecha_retorno" 
                                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label for="egreso_observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Observaciones <span class="text-red-500">*</span>
                        </label>
                        <textarea id="egreso_observaciones" 
                                  name="mov_observaciones" 
                                  rows="4" 
                                  placeholder="Detalles adicionales del egreso..."
                                  required
                                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white"></textarea>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Incluye detalles como motivo, destino exacto, condiciones especiales, etc.
                        </p>
                    </div>

                    <!-- Panel de Series para Egreso (oculto por defecto) -->
                    <div id="panel-series-egreso" class="hidden">
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">Seleccionar Series para Egreso</h4>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" 
                                           id="check-all-series-egreso" 
                                           class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    <label for="check-all-series-egreso" class="text-sm text-gray-700 dark:text-gray-300">Seleccionar todas</label>
                                </div>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table id="tabla-series-egreso" class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sel.</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Número de Serie</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha Ingreso</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Lote</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span>Series seleccionadas: <span id="contador-series-egreso" class="font-medium">0</span></span>
                                <button type="button" id="btn-buscar-serie" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    Buscar por número de serie
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmación de Egreso -->
                    <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Confirmación Requerida</h4>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                    Este egreso reducirá el stock disponible. Asegúrate de que toda la información sea correcta antes de proceder.
                                </p>
                                <div class="mt-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="confirmar-egreso" required class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-yellow-800 dark:text-yellow-200">
                                            Confirmo que la información es correcta y autorizo este egreso
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" 
                                id="btn-limpiar-egreso" 
                                class="w-full sm:w-auto px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Limpiar
                        </button>
                        
                        <button type="submit" 
                                class="w-full sm:w-auto px-8 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105">
                            <svg class="h-5 w-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Registrar Egreso
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel de Información del Producto -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Información del Producto Seleccionado -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Información del Producto
                </h3>
                
                <div id="info-producto-egreso" class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <svg class="mx-auto h-12 w-12 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p>Seleccione un producto para ver su información</p>
                </div>
            </div>

            <!-- Egresos Recientes -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Egresos Recientes
                </h3>
                
                <div id="lista-egresos-recientes" class="space-y-3">
                    <!-- Se carga dinámicamente -->
                    <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                        <p class="text-sm">No hay egresos recientes</p>
                    </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button onclick="$('#historial-tab').tab('show')" class="w-full text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium">
                        Ver historial completo →
                    </button>
                </div>
            </div>

            <!-- Guía de Egresos -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tipos de Egreso</h3>
                
                <div class="space-y-3">
                    <div class="flex items-start space-x-3">
                        <div class="w-3 h-3 bg-blue-500 rounded-full mt-1"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Egreso</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Traslado a otra ubicación o entrega autorizada</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-3 h-3 bg-red-500 rounded-full mt-1"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Baja</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Producto dañado, perdido o fuera de uso</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full mt-1"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Devolución</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Regreso al proveedor por defectos</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-3 h-3 bg-purple-500 rounded-full mt-1"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Préstamo</p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">Uso temporal con fecha de retorno</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        <strong>Nota:</strong> Todos los egresos quedan registrados en el historial para auditoría.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Búsqueda de Series -->
<div id="modal-buscar-serie" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Buscar Serie Específica
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 close-modal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="mt-3">
                    <label for="buscar-numero-serie" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Número de Serie
                    </label>
                    <input type="text" 
                           id="buscar-numero-serie" 
                           placeholder="Ingrese el número de serie..."
                           class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        Se buscará y seleccionará automáticamente la serie si está disponible
                    </p>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="btn-buscar-serie-confirmar" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Buscar
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript específico para el TAB de Egresos
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar campos según tipo de egreso
    document.getElementById('egreso_tipo').addEventListener('change', function() {
        const tipo = this.value;
        const panelPrestamo = document.getElementById('panel-prestamo');
        const responsableField = document.getElementById('prestamo_responsable');
        
        if (tipo === 'prestamo') {
            panelPrestamo.classList.remove('hidden');
            responsableField.setAttribute('required', 'required');
        } else {
            panelPrestamo.classList.add('hidden');
            responsableField.removeAttribute('required');
        }
    });

    // Búsqueda de series
    document.getElementById('btn-buscar-serie').addEventListener('click', function() {
        document.getElementById('modal-buscar-serie').classList.remove('hidden');
    });

    document.getElementById('btn-buscar-serie-confirmar').addEventListener('click', function() {
        const numeroSerie = document.getElementById('buscar-numero-serie').value.trim();
        if (numeroSerie) {
            buscarYSeleccionarSerie(numeroSerie);
            document.getElementById('modal-buscar-serie').classList.add('hidden');
        }
    });

    function buscarYSeleccionarSerie(numeroSerie) {
        // Buscar en la tabla de series
        const tabla = document.getElementById('tabla-series-egreso');
        const filas = tabla.querySelectorAll('tbody tr');
        
        filas<!-- TAB 3: Egresos -->
<!-- <div class="tab-pane fade" id="egresos" role="tabpanel" aria-labelledby="egresos-tab">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex
 -->


<!-- TAB 4: Historial de Movimientos -->
<div class="tab-pane fade" id="movimientos" role="tabpanel" aria-labelledby="historial-tab">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="h-6 w-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historial de Movimientos
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Auditoría completa de todas las transacciones de inventario</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 mt-4 lg:mt-0">
            <button onclick="inventario.exportarMovimientos('excel')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar Excel
            </button>
            
            <button onclick="inventario.exportarMovimientos('pdf')" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Generar PDF
            </button>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-purple-200 dark:border-gray-600 p-6 mb-6">
        <div class="flex items-center mb-4">
            <svg class="h-5 w-5 text-purple-600 dark:text-purple-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros de Búsqueda</h3>
        </div>
        
        <form id="form-filtros-historial">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="filtro-fecha-desde" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Desde</label>
                    <input type="date" id="filtro-fecha-desde" name="fecha_desde" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="filtro-fecha-hasta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Hasta</label>
                    <input type="date" id="filtro-fecha-hasta" name="fecha_hasta" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="filtro-tipo-movimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Movimiento</label>
                    <select id="filtro-tipo-movimiento" name="tipo_movimiento" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos los tipos</option>
                        <option value="ingreso">Ingresos</option>
                        <option value="egreso">Egresos</option>
                        <option value="venta">Ventas</option>
                        <option value="baja">Bajas</option>
                        <option value="devolucion">Devoluciones</option>
                        <option value="prestamo">Préstamos</option>
                        <option value="ajuste">Ajustes</option>
                    </select>
                </div>

                <div>
                    <label for="filtro-producto-historial" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Producto</label>
                    <select id="filtro-producto-historial" name="producto_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos los productos</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center mt-6">
                <div class="flex space-x-2">
                    <button type="button" id="btn-filtro-hoy" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Hoy
                    </button>
                    <button type="button" id="btn-filtro-semana" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Esta Semana
                    </button>
                    <button type="button" id="btn-filtro-mes" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Este Mes
                    </button>
                </div>

                <div class="flex space-x-2">
                    <button type="button" id="btn-limpiar-filtros-historial" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors duration-200">
                        Limpiar
                    </button>
                    
                    <button type="button" id="btn-aplicar-filtros-historial" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors duration-200">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Estadísticas de Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Movimientos</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-movimientos-filtro">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-ingresos-filtro">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Egresos</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-egresos-filtro">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-2.4 0L5 7h14m-4 6v6a1 1 0 01-1 1H6a1 1 0 01-1-1v-6m6 0V9a1 1 0 011-1h2a1 1 0 011 1v4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ventas</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-ventas-filtro">--</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Historial -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Historial Completo de Movimientos</h3>
                <div class="flex items-center space-x-3">
                    <input type="text" id="busqueda-rapida-historial" placeholder="Buscar en tabla..." class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1 dark:bg-gray-700 dark:text-white w-48">
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table id="tabla-historial-movimientos" class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Origen/Destino</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Observaciones</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="tbody-historial-movimientos">
                    <!-- Los datos se cargarán dinámicamente -->
                    <tr id="loading-historial">
                        <td colspan="8" class="px-6 py-8 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                                <span class="text-gray-500 dark:text-gray-400">Cargando historial de movimientos...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300" id="info-paginacion-historial">
                        Mostrando 0 a 0 de 0 movimientos
                    </span>
                </div>
                
                <div class="flex items-center space-x-1" id="paginacion-historial">
                    <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50" disabled>
                        ‹ Anterior
                    </button>
                    <button class="px-3 py-1 text-sm bg-purple-600 text-white rounded-md">1</button>
                    <button class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50" disabled>
                        Siguiente ›
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Análisis -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfica de Timeline -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Timeline de Movimientos</h3>
                <select id="timeline-period" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 dark:bg-gray-700 dark:text-white">
                    <option value="7">Últimos 7 días</option>
                    <option value="30" selected>Últimos 30 días</option>
                    <option value="90">Últimos 3 meses</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="grafica-timeline-movimientos" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Resumen por Tipo -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Resumen por Tipo</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">Período filtrado</div>
            </div>
            <div class="h-64">
                <canvas id="grafica-tipos-movimiento" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
            <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Actividad Reciente (Últimas 24h)
        </h3>
        
        <div id="actividad-reciente" class="space-y-3 max-h-80 overflow-y-auto">
            <!-- Se carga dinámicamente -->
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                <svg class="mx-auto h-12 w-12 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>No hay actividad reciente</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalles del Movimiento -->
<div id="modal-detalle-movimiento" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="titulo-modal-detalle">
                        Detalles del Movimiento
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 close-modal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div id="contenido-detalle-movimiento">
                    <!-- El contenido se carga dinámicamente -->
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- <script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtros rápidos de fecha
    const hoy = new Date();
    const formatoFecha = (fecha) => fecha.toISOString().split('T')[0];

    document.getElementById('btn-filtro-hoy').addEventListener('click', function() {
        document.getElementById('filtro-fecha-desde').value = formatoFecha(hoy);
        document.getElementById('filtro-fecha-hasta').value = formatoFecha(hoy);
    });

    document.getElementById('btn-filtro-semana').addEventListener('click', function() {
        const inicioSemana = new Date(hoy);
        inicioSemana.setDate(hoy.getDate() - 7);
        document.getElementById('filtro-fecha-desde').value = formatoFecha(inicioSemana);
        document.getElementById('filtro-fecha-hasta').value = formatoFecha(hoy);
    });

    document.getElementById('btn-filtro-mes').addEventListener('click', function() {
        const inicioMes = new Date(hoy);
        inicioMes.setDate(hoy.getDate() - 30);
        document.getElementById('filtro-fecha-desde').value = formatoFecha(inicioMes);
        document.getElementById('filtro-fecha-hasta').value = formatoFecha(hoy);
    });

    // Búsqueda rápida en la tabla
    document.getElementById('busqueda-rapida-historial').addEventListener('input', function() {
        const termino = this.value.toLowerCase();
        const filas = document.querySelectorAll('#tbody-historial-movimientos tr:not(#loading-historial)');
        
        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            if (texto.includes(termino)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    });

    // Cerrar modales
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
            document.querySelectorAll('.fixed.inset-0').forEach(modal => {
                modal.classList.add('hidden');
            });
        }
        
        if (e.target.classList.contains('bg-gray-500') && e.target.classList.contains('bg-opacity-75')) {
            const modal = e.target.closest('.fixed.inset-0');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    });
});

// Funciones del namespace inventario
if (typeof inventario === 'undefined') {
    window.inventario = {};
}

inventario.exportarMovimientos = function(formato) {
    console.log(`Exportando movimientos en formato ${formato}`);
};
</script> -->


 

 <!-- TAB 5: Gráficas y Reportes -->
<div class="tab-pane fade" id="graficas" role="tabpanel" aria-labelledby="graficas-tab">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="h-6 w-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Reportes y Gráficas
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Análisis visual del inventario y tendencias</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 mt-4 lg:mt-0">
            <button id="btn-actualizar-graficas" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualizar Datos
            </button>
            
            <button class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar Reporte
            </button>
        </div>
    </div>

    <!-- Filtros de Período -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-indigo-200 dark:border-gray-600 p-6 mb-6">
        <div class="flex items-center mb-4">
            <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros de Período</h3>
        </div>
        
        <form id="form-filtros-graficas">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="periodo-grafica" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Período</label>
                    <select id="periodo-grafica" name="periodo" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="7">Últimos 7 días</option>
                        <option value="30" selected>Últimos 30 días</option>
                        <option value="90">Últimos 3 meses</option>
                        <option value="365">Último año</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>

                <div id="fecha-desde-container" class="hidden">
                    <label for="fecha-desde-grafica" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Desde</label>
                    <input type="date" id="fecha-desde-grafica" name="fecha_desde" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div id="fecha-hasta-container" class="hidden">
                    <label for="fecha-hasta-grafica" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Hasta</label>
                    <input type="date" id="fecha-hasta-grafica" name="fecha_hasta" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div class="flex items-end">
                    <button type="button" id="btn-aplicar-filtros-graficas" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Grid de Gráficas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Gráfica de Movimientos Detallada -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Movimientos de Inventario</h3>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-1">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Ingresos</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Egresos</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Ventas</span>
                    </div>
                </div>
            </div>
            <div class="h-80">
                <canvas id="grafica-movimientos-detallada" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Gráfica de Stock por Categoría -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Distribución por Categoría</h3>
                <select id="tipo-grafica-stock" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 dark:bg-gray-700 dark:text-white">
                    <option value="doughnut">Dona</option>
                    <option value="bar">Barras</option>
                </select>
            </div>
            <div class="h-80">
                <canvas id="grafica-stock-categoria-detallada" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Gráfica de Tendencias -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tendencia de Stock</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">Evolución del inventario</div>
            </div>
            <div class="h-80">
                <canvas id="grafica-tendencias" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Gráfica de Top Productos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top 10 Productos</h3>
                <select id="criterio-top-productos" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 dark:bg-gray-700 dark:text-white">
                    <option value="stock">Por Stock</option>
                    <option value="movimientos">Por Movimientos</option>
                    <option value="ventas">Por Ventas</option>
                </select>
            </div>
            <div class="h-80">
                <canvas id="grafica-top-productos" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráficas Adicionales -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Gráfica de Estado de Stock -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Estado del Stock</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">Distribución actual</div>
            </div>
            <div class="h-64">
                <canvas id="grafica-estado-stock" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Gráfica de Productos por Marca -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Productos por Marca</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">Top marcas</div>
            </div>
            <div class="h-64">
                <canvas id="grafica-marcas" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Gráfica de Valor del Inventario -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Valor del Inventario</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">Por categoría</div>
            </div>
            <div class="h-64">
                <canvas id="grafica-valor-inventario" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Estadísticas Resumidas -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400" id="stat-total-ingresos">0</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Ingresos</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Período seleccionado</div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="text-3xl font-bold text-red-600 dark:text-red-400" id="stat-total-egresos">0</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Total Egresos</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Período seleccionado</div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400" id="stat-productos-activos">0</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Productos Activos</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">En inventario</div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400" id="stat-valor-inventario">$0</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Valor del Inventario</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Precio de costo</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="text-3xl font-bold text-amber-600 dark:text-amber-400" id="stat-rotacion-promedio">0</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Rotación Promedio</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Días promedio</div>
        </div>
    </div>

    <!-- Tabla de Análisis Detallado -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Análisis Detallado por Producto</h3>
                <div class="flex items-center space-x-2">
                    <button class="text-sm bg-indigo-100 hover:bg-indigo-200 text-indigo-700 px-3 py-1 rounded-md transition-colors">
                        Exportar Análisis
                    </button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table id="tabla-analisis-productos" class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stock Actual</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingresos</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Egresos</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ventas</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Stock</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rotación</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Los datos se cargarán dinámicamente -->
                    <tr id="loading-analisis">
                        <td colspan="8" class="px-6 py-8 text-center">
                            <div class="flex items-center justify-center space-x-3">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                                <span class="text-gray-500 dark:text-gray-400">Cargando análisis...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alertas y Recomendaciones -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Alertas de Stock -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="h-5 w-5 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Alertas de Stock
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400" id="total-alertas">0 alertas</span>
            </div>
            
            <div id="lista-alertas-stock" class="space-y-3">
                <!-- Se cargan dinámicamente -->
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <svg class="mx-auto h-12 w-12 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>No hay alertas de stock críticas</p>
                </div>
            </div>
        </div>

        <!-- Recomendaciones -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                    <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Recomendaciones
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">Basado en análisis</span>
            </div>
            
            <div id="lista-recomendaciones" class="space-y-3">
                <!-- Se cargan dinámicamente -->
                <div class="bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 dark:text-blue-200">
                                Configurar alertas automáticas para stock mínimo
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 dark:bg-green-900 border-l-4 border-green-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 dark:text-green-200">
                                Inventario en buen estado general
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<!-- TAB 6: Historial -->
<div class="tab-pane fade" id="historial" role="tabpanel" aria-labelledby="historial-tab">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                <svg class="h-6 w-6 text-amber-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Historial Completo de Inventario
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Registro detallado de todas las transacciones y cambios en el inventario</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 mt-4 lg:mt-0">
            <button onclick="inventario.respaldarHistorial()" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Respaldar Historial
            </button>
            
            <button onclick="inventario.generarReporteCompleto()" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Reporte Completo
            </button>
            
            <button onclick="inventario.exportarHistorial('excel')" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar Excel
            </button>
        </div>
    </div>

    <!-- Panel de Filtros Avanzados -->
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-800 dark:to-gray-700 rounded-xl border border-amber-200 dark:border-gray-600 p-6 mb-6">
        <div class="flex items-center mb-4">
            <svg class="h-5 w-5 text-amber-600 dark:text-amber-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros Avanzados de Búsqueda</h3>
        </div>
        
        <form id="form-filtros-historial-completo">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Rango de Fechas -->
                <div>
                    <label for="historial-fecha-desde" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Desde</label>
                    <input type="date" id="historial-fecha-desde" name="fecha_desde" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="historial-fecha-hasta" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha Hasta</label>
                    <input type="date" id="historial-fecha-hasta" name="fecha_hasta" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Tipo de Movimiento -->
                <div>
                    <label for="historial-tipo-movimiento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo de Movimiento</label>
                    <select id="historial-tipo-movimiento" name="tipo_movimiento" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="ingreso">Ingresos</option>
                        <option value="egreso">Egresos</option>
                        <option value="venta">Ventas</option>
                        <option value="baja">Bajas</option>
                        <option value="devolucion">Devoluciones</option>
                        <option value="ajuste">Ajustes</option>
                        <option value="prestamo">Préstamos</option>
                        <option value="importacion">Importaciones</option>
                    </select>
                </div>

                <!-- Usuario -->
                <div>
                    <label for="historial-usuario" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuario</label>
                    <select id="historial-usuario" name="usuario_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos los usuarios</option>
                        <!-- Se llenan dinámicamente -->
                    </select>
                </div>

                <!-- Producto -->
                <div>
                    <label for="historial-producto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Producto</label>
                    <select id="historial-producto" name="producto_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todos los productos</option>
                        <!-- Se llenan dinámicamente -->
                    </select>
                </div>
            </div>

            <!-- Filtros adicionales en segunda fila -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <!-- Categoría -->
                <div>
                    <label for="historial-categoria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoría</label>
                    <select id="historial-categoria" name="categoria_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Todas las categorías</option>
                        <!-- Se llenan dinámicamente -->
                    </select>
                </div>

                <!-- Serie/Lote -->
                <div>
                    <label for="historial-serie-lote" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Serie/Lote</label>
                    <input type="text" id="historial-serie-lote" name="serie_lote" placeholder="Buscar por serie o lote..." class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Rango de Cantidad -->
                <div>
                    <label for="historial-cantidad-min" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad Mín.</label>
                    <input type="number" id="historial-cantidad-min" name="cantidad_min" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label for="historial-cantidad-max" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cantidad Máx.</label>
                    <input type="number" id="historial-cantidad-max" name="cantidad_max" min="0" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Botones de acción y filtros rápidos -->
            <div class="flex flex-col lg:flex-row justify-between items-center mt-6 space-y-4 lg:space-y-0">
                <!-- Filtros rápidos -->
                <div class="flex flex-wrap gap-2">
                    <button type="button" id="btn-filtro-hoy-historial" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Hoy
                    </button>
                    <button type="button" id="btn-filtro-ayer-historial" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Ayer
                    </button>
                    <button type="button" id="btn-filtro-semana-historial" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Esta Semana
                    </button>
                    <button type="button" id="btn-filtro-mes-historial" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Este Mes
                    </button>
                    <button type="button" id="btn-filtro-trimestre-historial" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Trimestre
                    </button>
                    <button type="button" id="btn-filtro-año-historial" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm rounded-md transition-colors">
                        Este Año
                    </button>
                </div>

                <!-- Botones de control -->
                <div class="flex space-x-2">
                    <button type="button" id="btn-limpiar-filtros-historial-completo" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Limpiar
                    </button>
                    
                    <button type="button" id="btn-aplicar-filtros-historial-completo" class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors duration-200">
                        <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen de Resultados -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Registros</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-registros-historial">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ingresos</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-ingresos-historial">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Egresos</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-egresos-historial">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-2.4 0L5 7h14m-4 6v6a1 1 0 01-1 1H6a1 1 0 01-1-1v-6m6 0V9a1 1 0 011-1h2a1 1 0 011 1v4"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Ventas</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-ventas-historial">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bajas</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="total-bajas-historial">--</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Usuarios Activos</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" id="usuarios-activos-historial">--</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla Principal del Historial -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 lg:mb-0">Historial Detallado de Movimientos</h3>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="text" id="busqueda-global-historial" placeholder="Buscar en toda la tabla..." class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 dark:bg-gray-700 dark:text-white w-full sm:w-64">
                    
                    <div class="flex items-center space-x-2">
                        <label for="historial-page-size" class="text-sm text-gray-600 dark:text-gray-400">Mostrar:</label>
                        <select id="historial-page-size" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 dark:bg-gray-700 dark:text-white">
                            <option value="25">25</option>
                            <option value="50" selected>50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                            <option value="500">500</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table id="tabla-historial-completo" class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex items-center space-x-1">
                                <span>ID</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex items-center space-x-1">
                                <span>Fecha & Hora</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Origen/Destino</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Lote/Serie</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Observaciones</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="tbody-historial-completo">
                    <!-- Los datos se cargarán dinámicamente -->
                    <tr id="loading-historial-completo">
                        <td colspan="11" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600"></div>
                                <span class="text-gray-500 dark:text-gray-400">Cargando historial completo...</span>
                                <span class="text-sm text-gray-400 dark:text-gray-500">Esto puede tomar unos momentos</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación Avanzada -->
        <div class="bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-3 lg:space-y-0">
                <!-- Información de paginación -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700 dark:text-gray-300" id="info-paginacion-historial-completo">
                        Mostrando 0 a 0 de 0 registros
                    </span>
                    
                    <div class="flex items-center space-x-2">
                        <label for="ir-a-pagina" class="text-sm text-gray-600 dark:text-gray-400">Ir a página:</label>
                        <input type="number" id="ir-a-pagina" min="1" class="w-16 text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 dark:bg-gray-700 dark:text-white text-center">
                        <button type="button" id="btn-ir-pagina" class="text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 px-2 py-1 rounded-md">Ir</button>
                    </div>
                </div>
                
                <!-- Controles de paginación -->
                <div class="flex items-center space-x-1" id="controles-paginacion-historial">
                    <button id="btn-primera-pagina" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        ⟨⟨ Primera
                    </button>
                    <button id="btn-pagina-anterior" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        ⟨ Anterior
                    </button>
                    
                    <div id="numeros-pagina" class="flex items-center space-x-1">
                        <!-- Se generan dinámicamente -->
                    </div>
                    
                    <button id="btn-pagina-siguiente" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Siguiente ⟩
                    </button>
                    <button id="btn-ultima-pagina" class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Última ⟩⟩
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Visual del Historial -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="h-5 w-5 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Timeline de Actividad
            </h3>
            
            <div class="flex items-center space-x-2">
                <select id="timeline-periodo" class="text-sm border border-gray-300 dark:border-gray-600 rounded-md px-2 py-1 dark:bg-gray-700 dark:text-white">
                    <option value="24h">Últimas 24 horas</option>
                    <option value="7d" selected>Últimos 7 días</option>
                    <option value="30d">Últimos 30 días</option>
                    <option value="90d">Últimos 3 meses</option>
                </select>
                
                <button id="btn-actualizar-timeline" class="text-sm bg-amber-100 hover:bg-amber-200 text-amber-700 px-3 py-1 rounded-md transition-colors">
                    Actualizar
                </button>
            </div>
        </div>
        
        <div class="h-80">
            <canvas id="timeline-historial-canvas" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Análisis y Estadísticas Avanzadas -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribución por Horas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actividad por Horas del Día</h3>
            <div class="h-64">
                <canvas id="grafica-horas-actividad" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Top Usuarios Más Activos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Usuarios Más Activos</h3>
            <div class="space-y-3" id="lista-usuarios-activos">
                <!-- Se llena dinámicamente -->
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <svg class="mx-auto h-12 w-12 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <p>Cargando datos de usuarios...</p>
                </div>
            </div>
        </div>

        <!-- Productos Más Movidos -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Productos Más Movidos</h3>
            <div class="h-64">
                <canvas id="grafica-productos-movidos" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Patrones de Movimiento -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Patrones de Movimiento</h3>
            <div class="space-y-4">
                <!-- Promedio diario -->
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Promedio de movimientos diarios:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white" id="promedio-movimientos-dia">--</span>
                </div>
                
                <!-- Día más activo -->
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Día más activo:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white" id="dia-mas-activo">--</span>
                </div>
                
                <!-- Hora pico -->
                <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Hora pico de actividad:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white" id="hora-pico">--</span>
                </div>
                
                <!-- Tipo más común -->
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Tipo de movimiento más común:</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white" id="tipo-mas-comun">--</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Auditoria y Compliance -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Auditoría y Cumplimiento
            </h3>
            
            <div class="flex space-x-2">
                <button onclick="inventario.generarReporteAuditoria()" class="text-sm bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded-md transition-colors">
                    Generar Reporte de Auditoría
                </button>
                
                <button onclick="inventario.verificarIntegridad()" class="text-sm bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-1 rounded-md transition-colors">
                    Verificar Integridad
                </button>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Estado de Auditoría -->
            <div class="space-y-3">
                <h4 class="text-md font-medium text-gray-900 dark:text-white">Estado de Auditoría</h4>
                
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Registros verificados:</span>
                        <span class="text-sm font-medium text-green-600" id="registros-verificados">100%</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Inconsistencias detectadas:</span>
                        <span class="text-sm font-medium text-red-600" id="inconsistencias-detectadas">0</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Última verificación:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white" id="ultima-verificacion">--</span>
                    </div>
                </div>
            </div>
            
            <!-- Cumplimiento Legal -->
            <div class="space-y-3">
                <h4 class="text-md font-medium text-gray-900 dark:text-white">Cumplimiento Legal</h4>
                
                <div class="space-y-2">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Trazabilidad completa</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Registros íntegros</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Backup actualizado</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Revisión pendiente</span>
                    </div>
                </div>
            </div>
            
            <!-- Acciones Críticas -->
            <div class="space-y-3">
                <h4 class="text-md font-medium text-gray-900 dark:text-white">Acciones Críticas</h4>
                
                <div class="space-y-2">
                    <button class="w-full text-left text-sm bg-blue-50 hover:bg-blue-100 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 p-2 rounded-md transition-colors">
                        📊 Exportar para DIGECAM
                    </button>
                    
                    <button class="w-full text-left text-sm bg-green-50 hover:bg-green-100 dark:bg-green-900 dark:hover:bg-green-800 text-green-700 dark:text-green-200 p-2 rounded-md transition-colors">
                        🔒 Crear Backup Seguro
                    </button>
                    
                    <button class="w-full text-left text-sm bg-purple-50 hover:bg-purple-100 dark:bg-purple-900 dark:hover:bg-purple-800 text-purple-700 dark:text-purple-200 p-2 rounded-md transition-colors">
                        📋 Reporte de Cumplimiento
                    </button>
                    
                    <button class="w-full text-left text-sm bg-orange-50 hover:bg-orange-100 dark:bg-orange-900 dark:hover:bg-orange-800 text-orange-700 dark:text-orange-200 p-2 rounded-md transition-colors">
                        🔍 Auditoría Detallada
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalles Completos del Movimiento -->
<div id="modal-detalle-movimiento-completo" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="titulo-modal-detalle-completo">
                        Detalles Completos del Movimiento #--
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 close-modal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div id="contenido-detalle-movimiento-completo" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- El contenido se carga dinámicamente -->
                    <div class="col-span-2 text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-amber-600 mx-auto mb-4"></div>
                        <span class="text-gray-500 dark:text-gray-400">Cargando detalles del movimiento...</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Imprimir Comprobante
                </button>
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- <script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el TAB de Historial
    if (typeof inventario === 'undefined') {
        window.inventario = {};
    }

    // Funciones específicas del historial
    inventario.respaldarHistorial = function() {
        console.log('Respaldando historial...');
        // Implementar lógica de respaldo
    };

    inventario.generarReporteCompleto = function() {
        console.log('Generando reporte completo...');
        // Implementar generación de reporte
    };

    inventario.exportarHistorial = function(formato) {
        console.log(`Exportando historial en formato ${formato}`);
        // Implementar exportación
    };

    inventario.generarReporteAuditoria = function() {
        console.log('Generando reporte de auditoría...');
        // Implementar reporte de auditoría
    };

    inventario.verificarIntegridad = function() {
        console.log('Verificando integridad de datos...');
        // Implementar verificación de integridad
    };

    // Filtros rápidos de fecha para historial
    const hoy = new Date();
    const formatoFecha = (fecha) => fecha.toISOString().split('T')[0];

    document.getElementById('btn-filtro-hoy-historial')?.addEventListener('click', function() {
        document.getElementById('historial-fecha-desde').value = formatoFecha(hoy);
        document.getElementById('historial-fecha-hasta').value = formatoFecha(hoy);
    });

    document.getElementById('btn-filtro-ayer-historial')?.addEventListener('click', function() {
        const ayer = new Date(hoy);
        ayer.setDate(hoy.getDate() - 1);
        document.getElementById('historial-fecha-desde').value = formatoFecha(ayer);
        document.getElementById('historial-fecha-hasta').value = formatoFecha(ayer);
    });

    document.getElementById('btn-filtro-semana-historial')?.addEventListener('click', function() {
        const inicioSemana = new Date(hoy);
        inicioSemana.setDate(hoy.getDate() - 7);
        document.getElementById('historial-fecha-desde').value = formatoFecha(inicioSemana);
        document.getElementById('historial-fecha-hasta').value = formatoFecha(hoy);
    });

    document.getElementById('btn-filtro-mes-historial')?.addEventListener('click', function() {
        const inicioMes = new Date(hoy);
        inicioMes.setDate(hoy.getDate() - 30);
        document.getElementById('historial-fecha-desde').value = formatoFecha(inicioMes);
        document.getElementById('historial-fecha-hasta').value = formatoFecha(hoy);
    });

    document.getElementById('btn-filtro-trimestre-historial')?.addEventListener('click', function() {
        const inicioTrimestre = new Date(hoy);
        inicioTrimestre.setDate(hoy.getDate() - 90);
        document.getElementById('historial-fecha-desde').value = formatoFecha(inicioTrimestre);
        document.getElementById('historial-fecha-hasta').value = formatoFecha(hoy);
    });

    document.getElementById('btn-filtro-año-historial')?.addEventListener('click', function() {
        const inicioAño = new Date(hoy.getFullYear(), 0, 1);
        document.getElementById('historial-fecha-desde').value = formatoFecha(inicioAño);
        document.getElementById('historial-fecha-hasta').value = formatoFecha(hoy);
    });

    // Cerrar modales
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('close-modal') || e.target.closest('.close-modal')) {
            document.querySelectorAll('.fixed.inset-0').forEach(modal => {
                modal.classList.add('hidden');
            });
        }
        
        if (e.target.classList.contains('bg-gray-500') && e.target.classList.contains('bg-opacity-75')) {
            const modal = e.target.closest('.fixed.inset-0');
            if (modal) {
                modal.classList.add('hidden');
            }
        }
    });
});
</script> -->



<!-- -------------AQUI FINALIZA LAS TABS  -->

                
            </div>
        </div>
    </div>
</div>



@endsection


@vite('resources/js/inventario/index.js')