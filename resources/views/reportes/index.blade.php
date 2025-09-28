@extends('layouts.app')

@section('title', 'Sistema de Reportes - Armería')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                Sistema de Reportes
            </h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Análisis integral de ventas, productos y comisiones
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
            <div class="flex items-center space-x-2 bg-white dark:bg-gray-800 px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Período:</label>
                <input type="date" id="fecha_inicio" 
                       class="border-0 focus:ring-0 text-sm dark:bg-gray-800 dark:text-gray-100">
                <span class="text-gray-500">—</span>
                <input type="date" id="fecha_fin" 
                       class="border-0 focus:ring-0 text-sm dark:bg-gray-800 dark:text-gray-100">
                <button onclick="reportesManager.aplicarFiltroFecha()"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i>
                    Aplicar
                </button>
            </div>
        </div>
    </div>

    <!-- Dashboard de KPIs -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Ventas -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shopping-cart text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Ventas</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="kpi-total-ventas">0</dd>
                            <dd class="text-sm text-gray-500 dark:text-gray-400" id="kpi-porcentaje-ventas">0% vs período anterior</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monto Total -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-green-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-dollar-sign text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Monto Total</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="kpi-monto-total">Q0.00</dd>
                            <dd class="text-sm text-gray-500 dark:text-gray-400" id="kpi-porcentaje-monto">0% vs período anterior</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos Vendidos -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-yellow-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-boxes text-yellow-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Productos Vendidos</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="kpi-productos-vendidos">0</dd>
                            <dd class="text-sm text-gray-500 dark:text-gray-400" id="kpi-promedio-productos">0 promedio por venta</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comisiones Pendientes -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border-l-4 border-red-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-percent text-red-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Comisiones Pendientes</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100" id="kpi-comisiones-pendientes">Q0.00</dd>
                            <dd class="text-sm text-gray-500 dark:text-gray-400" id="kpi-total-comisiones">Q0.00 total del período</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación de Reportes -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="reportesManager.cambiarTab('dashboard')" 
                        class="tab-button active border-blue-500 text-blue-600 dark:text-blue-400 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </button>
                <button onclick="reportesManager.cambiarTab('ventas')" 
                        class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Reporte de Ventas
                </button>
                <button onclick="reportesManager.cambiarTab('productos')" 
                        class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-boxes mr-2"></i>
                    Productos Más Vendidos
                </button>
                <button onclick="reportesManager.cambiarTab('comisiones')" 
                        class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-percent mr-2"></i>
                    Comisiones
                </button>
                <button onclick="reportesManager.cambiarTab('pagos')" 
                        class="tab-button border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-credit-card mr-2"></i>
                    Estado de Pagos
                </button>
            </nav>
        </div>
    </div>

    <!-- Contenido de los Tabs -->
    
    <!-- Tab Dashboard -->
    <div id="tab-dashboard" class="tab-content">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Gráfico de Ventas por Día -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ventas por Día</h3>
                    <div class="flex space-x-2">
                        <button onclick="reportesManager.cambiarTipoGrafico('ventas', 'line')" 
                                class="grafico-tipo-btn active p-1 rounded" data-tipo="line" title="Líneas">
                            <i class="fas fa-chart-line text-sm"></i>
                        </button>
                        <button onclick="reportesManager.cambiarTipoGrafico('ventas', 'bar')" 
                                class="grafico-tipo-btn p-1 rounded" data-tipo="bar" title="Barras">
                            <i class="fas fa-chart-bar text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="grafico-ventas-dias"></canvas>
                </div>
            </div>

            <!-- Gráfico de Productos Más Vendidos -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Top 10 Productos</h3>
                    <button onclick="reportesManager.verDetalleProductos()" 
                            class="text-blue-600 hover:text-blue-800 text-sm">
                        Ver todos
                    </button>
                </div>
                <div class="h-64">
                    <canvas id="grafico-productos-top"></canvas>
                </div>
            </div>

            <!-- Gráfico de Ventas por Vendedor -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ventas por Vendedor</h3>
                    <div class="flex space-x-2">
                        <button onclick="reportesManager.cambiarTipoGrafico('vendedor', 'doughnut')" 
                                class="grafico-tipo-btn active p-1 rounded" data-tipo="doughnut" title="Dona">
                            <i class="fas fa-chart-pie text-sm"></i>
                        </button>
                        <button onclick="reportesManager.cambiarTipoGrafico('vendedor', 'bar')" 
                                class="grafico-tipo-btn p-1 rounded" data-tipo="bar" title="Barras">
                            <i class="fas fa-chart-bar text-sm"></i>
                        </button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="grafico-vendedores"></canvas>
                </div>
            </div>

            <!-- Gráfico de Métodos de Pago -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Métodos de Pago</h3>
                </div>
                <div class="h-64">
                    <canvas id="grafico-metodos-pago"></canvas>
                </div>
            </div>

        </div>
    </div>

    <!-- Tab Reporte de Ventas -->
    <div id="tab-ventas" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Reporte Detallado de Ventas</h3>
                    <div class="flex space-x-3">
                        <button onclick="reportesManager.exportarReporte('ventas', 'excel')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i class="fas fa-file-excel mr-2"></i>
                            Excel
                        </button>
                        <button onclick="reportesManager.exportarReporte('ventas', 'pdf')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            <i class="fas fa-file-pdf mr-2"></i>
                            PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros para Reporte de Ventas -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendedor</label>
                        <select id="filtro-vendedor-ventas" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos los vendedores</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
                        <input type="text" id="filtro-cliente-ventas" placeholder="Buscar cliente..."
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                        <select id="filtro-estado-ventas"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos los estados</option>
                            <option value="1">Activas</option>
                            <option value="0">Anuladas</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="reportesManager.aplicarFiltrosVentas()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de Ventas -->
            <div class="overflow-x-auto">
                <table id="tabla-ventas" class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vendedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Productos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-ventas" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        <!-- Los datos se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div id="paginacion-ventas" class="flex items-center justify-between">
                    <!-- La paginación se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Productos Más Vendidos -->
    <div id="tab-productos" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Productos Más Vendidos</h3>
                    <div class="flex space-x-3">
                        <button onclick="reportesManager.exportarReporte('productos', 'excel')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i class="fas fa-file-excel mr-2"></i>
                            Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros para Productos -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría</label>
                        <select id="filtro-categoria-productos"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todas las categorías</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                        <select id="filtro-marca-productos"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todas las marcas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Límite</label>
                        <select id="filtro-limite-productos"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="25">Top 25</option>
                            <option value="50">Top 50</option>
                            <option value="100">Top 100</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="reportesManager.aplicarFiltrosProductos()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lista de Productos -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ranking</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marca</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad Vendida</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio Promedio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Ingresos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transacciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-productos" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        <!-- Los datos se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Comisiones -->
    <div id="tab-comisiones" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Reporte de Comisiones</h3>
                    <div class="flex space-x-3">
                        <button onclick="reportesManager.exportarReporte('comisiones', 'excel')"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i class="fas fa-file-excel mr-2"></i>
                            Excel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Resumen de Comisiones -->
            <div class="px-6 py-4 bg-blue-50 dark:bg-blue-900">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" id="resumen-total-comisiones">Q0.00</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Comisiones</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" id="resumen-pendientes-comisiones">Q0.00</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Pendientes</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="resumen-pagadas-comisiones">Q0.00</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Pagadas</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400" id="resumen-canceladas-comisiones">Q0.00</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Canceladas</div>
                    </div>
                </div>
            </div>

            <!-- Filtros para Comisiones -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendedor</label>
                        <select id="filtro-vendedor-comisiones"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos los vendedores</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                        <select id="filtro-estado-comisiones"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos los estados</option>
                            <option value="PENDIENTE">Pendientes</option>
                            <option value="PAGADO">Pagadas</option>
                            <option value="CANCELADO">Canceladas</option>
                        </select>
                    </div>
                    <div></div>
                    <div class="flex items-end">
                        <button onclick="reportesManager.aplicarFiltrosComisiones()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de Comisiones -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vendedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Venta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto Base</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Porcentaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Comisión</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-comisiones" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        <!-- Los datos se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab Estado de Pagos -->
    <div id="tab-pagos" class="tab-content hidden">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Estado de Pagos y Cuotas</h3>
            </div>

            <!-- Filtros para Pagos -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado de Pago</label>
                        <select id="filtro-estado-pagos"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos los estados</option>
                            <option value="PENDIENTE">Pendientes</option>
                            <option value="PARCIAL">Parciales</option>
                            <option value="COMPLETADO">Completados</option>
                            <option value="VENCIDO">Vencidos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Pago</label>
                        <select id="filtro-tipo-pagos"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos los tipos</option>
                            <option value="UNICO">Pago único</option>
                            <option value="CUOTAS">Cuotas</option>
                        </select>
                    </div>
                    <div></div>
                    <div class="flex items-end">
                        <button onclick="reportesManager.aplicarFiltrosPagos()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de Pagos -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha Inicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vendedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pagado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pendiente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-pagos" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        <!-- Los datos se cargarán dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@vite('resources/js/reportes/index.js')