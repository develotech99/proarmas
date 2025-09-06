<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Inventario - Armería CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .animate-fadeInUp { animation: fadeInUp 0.6s ease-out; }
        .animate-pulse { animation: pulse 2s infinite; }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeInUp 0.5s ease-out; }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .glass-effect {
            backdrop-filter: blur(16px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 1rem 0;
        }
        
        .modal {
            backdrop-filter: blur(8px);
        }
        
        .btn-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .form-input-modern {
            @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200;
        }
        
        .status-badge {
            @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-medium;
        }
        
        .status-success { @apply bg-green-100 text-green-800; }
        .status-warning { @apply bg-yellow-100 text-yellow-800; }
        .status-danger { @apply bg-red-100 text-red-800; }
        
        /* Scrollbar personalizado */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header Principal -->
    <header class="gradient-bg text-white shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-6 lg:space-y-0">
                <div class="flex-1">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="glass-effect rounded-xl p-3">
                            <i class="fas fa-shield-alt text-3xl text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-4xl lg:text-5xl font-bold tracking-tight">
                                Control de Inventario
                            </h1>
                            <p class="text-xl opacity-90 mt-2">Sistema CRM de Armería</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-4 mt-6">
                        <div class="glass-effect rounded-lg px-4 py-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                <span class="text-white font-medium">Sistema Activo</span>
                            </div>
                        </div>
                        <div class="glass-effect rounded-lg px-4 py-2">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-lock text-blue-300"></i>
                                <span class="text-white font-medium">Seguro & Confiable</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Breadcrumb -->
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="glass-effect rounded-lg px-6 py-3 flex items-center space-x-4">
                        <li>
                            <a href="#" class="text-white opacity-75 hover:opacity-100 transition-opacity duration-200 flex items-center">
                                <i class="fas fa-home mr-2"></i>
                                <span class="font-medium">Dashboard</span>
                            </a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-white opacity-50 mx-2"></i>
                            <span class="text-white font-semibold">Inventario</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </header>

    <!-- Dashboard de Métricas -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <!-- Card 1: Productos en Stock -->
            <div class="group relative bg-white rounded-2xl card-shadow overflow-hidden transform hover:scale-105 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative z-10 p-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="bg-blue-100 rounded-xl p-3">
                                <i class="fas fa-cubes text-blue-600 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Productos</p>
                                <p class="text-3xl font-bold text-gray-900">247</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-green-600 text-sm font-semibold">+12%</p>
                            <p class="text-gray-500 text-xs">vs mes anterior</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 78%"></div>
                        </div>
                        <span class="ml-3 text-gray-600 text-sm font-medium">78% activo</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Series Disponibles -->
            <div class="group relative bg-white rounded-2xl card-shadow overflow-hidden transform hover:scale-105 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-500 to-emerald-700 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative z-10 p-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="bg-emerald-100 rounded-xl p-3">
                                <i class="fas fa-barcode text-emerald-600 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Series</p>
                                <p class="text-3xl font-bold text-gray-900">1,842</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-green-600 text-sm font-semibold">+8%</p>
                            <p class="text-gray-500 text-xs">vs mes anterior</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: 92%"></div>
                        </div>
                        <span class="ml-3 text-gray-600 text-sm font-medium">92% tracked</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Movimientos Hoy -->
            <div class="group relative bg-white rounded-2xl card-shadow overflow-hidden transform hover:scale-105 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative z-10 p-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="bg-amber-100 rounded-xl p-3">
                                <i class="fas fa-exchange-alt text-amber-600 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Movimientos</p>
                                <p class="text-3xl font-bold text-gray-900">34</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-green-600 text-sm font-semibold">+15%</p>
                            <p class="text-gray-500 text-xs">vs ayer</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: 65%"></div>
                        </div>
                        <span class="ml-3 text-gray-600 text-sm font-medium">Hoy</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Alertas de Stock -->
            <div class="group relative bg-white rounded-2xl card-shadow overflow-hidden transform hover:scale-105 transition-all duration-300">
                <div class="absolute inset-0 bg-gradient-to-br from-red-500 to-pink-600 opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                <div class="relative z-10 p-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="bg-red-100 rounded-xl p-3">
                                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm font-medium uppercase tracking-wider">Alertas</p>
                                <p class="text-3xl font-bold text-gray-900">7</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-red-600 text-sm font-semibold">Crítico</p>
                            <p class="text-gray-500 text-xs">Stock bajo</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-red-500 h-2 rounded-full" style="width: 15%"></div>
                        </div>
                        <span class="ml-3 text-gray-600 text-sm font-medium">Requiere atención</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Panel Principal CRM -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="bg-white rounded-3xl card-shadow overflow-hidden">
            <!-- Navigation Tabs -->
            <div class="gradient-bg">
                <nav class="flex" role="tablist">
                    <button class="tab-button active flex-1 relative overflow-hidden py-6 px-8 text-center font-semibold transition-all duration-300 focus:outline-none text-white" 
                            data-target="stock-actual">
                        <div class="relative z-10 flex items-center justify-center space-x-3">
                            <div class="glass-effect rounded-lg p-2">
                                <i class="fas fa-warehouse text-lg"></i>
                            </div>
                            <span class="hidden lg:block text-lg">Stock Actual</span>
                            <span class="lg:hidden text-sm">Stock</span>
                        </div>
                    </button>
                    
                    <button class="tab-button flex-1 relative overflow-hidden py-6 px-8 text-center font-semibold transition-all duration-300 focus:outline-none text-white opacity-75 hover:opacity-100" 
                            data-target="ingresar-producto">
                        <div class="relative z-10 flex items-center justify-center space-x-3">
                            <div class="glass-effect rounded-lg p-2">
                                <i class="fas fa-plus-circle text-lg"></i>
                            </div>
                            <span class="hidden lg:block text-lg">Ingresar</span>
                            <span class="lg:hidden text-sm">Nuevo</span>
                        </div>
                    </button>
                    
                    <button class="tab-button flex-1 relative overflow-hidden py-6 px-8 text-center font-semibold transition-all duration-300 focus:outline-none text-white opacity-75 hover:opacity-100" 
                            data-target="movimientos">
                        <div class="relative z-10 flex items-center justify-center space-x-3">
                            <div class="glass-effect rounded-lg p-2">
                                <i class="fas fa-exchange-alt text-lg"></i>
                            </div>
                            <span class="hidden lg:block text-lg">Movimientos</span>
                            <span class="lg:hidden text-sm">Mov.</span>
                        </div>
                    </button>
                    
                    <button class="tab-button flex-1 relative overflow-hidden py-6 px-8 text-center font-semibold transition-all duration-300 focus:outline-none text-white opacity-75 hover:opacity-100" 
                            data-target="historial">
                        <div class="relative z-10 flex items-center justify-center space-x-3">
                            <div class="glass-effect rounded-lg p-2">
                                <i class="fas fa-history text-lg"></i>
                            </div>
                            <span class="hidden lg:block text-lg">Historial</span>
                            <span class="lg:hidden text-sm">Log</span>
                        </div>
                    </button>
                </nav>
            </div>
            
            <!-- Tab Content -->
            <div class="p-8 lg:p-12">
                <!-- TAB 1: Stock Actual -->
                <div class="tab-content active animate-fadeInUp" id="stock-actual">
                    <!-- Filtros -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8 mb-8 border border-blue-100 card-shadow">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="bg-blue-500 rounded-xl p-3 shadow-lg">
                                    <i class="fas fa-filter text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">Filtros de Búsqueda</h3>
                                    <p class="text-gray-600">Personaliza tu vista del inventario</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-6">
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Categoría</label>
                                <select class="form-input-modern" id="filtro-categoria">
                                    <option value="">Todas las categorías</option>
                                    <option value="1">Pistolas</option>
                                    <option value="2">Rifles</option>
                                    <option value="3">Municiones</option>
                                    <option value="4">Accesorios</option>
                                </select>
                            </div>
                            
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Marca</label>
                                <select class="form-input-modern" id="filtro-marca">
                                    <option value="">Todas las marcas</option>
                                    <option value="1">Glock</option>
                                    <option value="2">Smith & Wesson</option>
                                    <option value="3">Sig Sauer</option>
                                    <option value="4">Beretta</option>
                                </select>
                            </div>
                            
                            <div class="flex flex-col justify-end space-y-3">
                                <button type="button" class="btn-modern" id="btn-aplicar-filtros">
                                    <i class="fas fa-search mr-2"></i>
                                    <span class="hidden sm:inline">Filtrar</span>
                                </button>
                                <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all duration-300" id="btn-limpiar-filtros">
                                    <i class="fas fa-times mr-2"></i>
                                    <span class="hidden sm:inline">Limpiar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de Stock -->
                    <div class="bg-white rounded-2xl card-shadow overflow-hidden border border-gray-100">
                        <div class="bg-gray-50 px-8 py-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Inventario Actual</h3>
                                    <p class="text-gray-600 mt-1">Vista completa de productos en stock</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                        <i class="fas fa-file-excel"></i>
                                        <span class="hidden sm:inline">Excel</span>
                                    </button>
                                    <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                        <i class="fas fa-file-pdf"></i>
                                        <span class="hidden sm:inline">PDF</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table id="tabla-stock" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-barcode text-gray-400"></i>
                                                <span>Código</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-box text-gray-400"></i>
                                                <span>Producto</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-tags text-gray-400"></i>
                                                <span>Categoría</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-copyright text-gray-400"></i>
                                                <span>Marca</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-cubes text-gray-400"></i>
                                                <span>Stock</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-traffic-light text-gray-400"></i>
                                                <span>Estado</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center justify-end space-x-2">
                                                <i class="fas fa-tools text-gray-400"></i>
                                                <span>Acciones</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#001</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-gun text-gray-600"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Glock 17 Gen 5</div>
                                                    <div class="text-sm text-gray-500">Pistola 9mm</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Pistolas</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Glock</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">15</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge status-success">Normal</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="text-green-600 hover:text-green-900 p-2 rounded-lg hover:bg-green-50 transition-colors">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="text-purple-600 hover:text-purple-900 p-2 rounded-lg hover:bg-purple-50 transition-colors">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Ingresar Producto -->
                <div class="tab-content animate-fadeInUp" id="ingresar-producto">
                    <div class="text-center mb-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl shadow-lg mb-4">
                            <i class="fas fa-plus text-2xl text-white"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Nuevo Producto</h2>
                        <p class="text-gray-600 max-w-md mx-auto">Registra un nuevo producto en el inventario</p>
                    </div>

                    <form id="form-producto" class="space-y-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Información Básica -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 overflow-hidden card-shadow">
                                <div class="gradient-bg px-8 py-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="glass-effect rounded-xl p-3">
                                            <i class="fas fa-info-circle text-2xl text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-white">Información del Producto</h3>
                                            <p class="text-white opacity-75">Datos básicos y clasificación</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-8 space-y-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">
                                            Nombre del Producto *
                                        </label>
                                        <input type="text" class="form-input-modern" placeholder="Ej: Glock 17 Gen 5" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Código de Barras</label>
                                        <input type="text" class="form-input-modern" placeholder="Escanear o ingresar manualmente">
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-3">Categoría *</label>
                                            <select class="form-input-modern" required>
                                                <option value="">Seleccionar categoría...</option>
                                                <option value="1">Pistolas</option>
                                                <option value="2">Rifles</option>
                                                <option value="3">Escopetas</option>
                                                <option value="4">Municiones</option>
                                                <option value="5">Accesorios</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-3">Subcategoría</label>
                                            <select class="form-input-modern">
                                                <option value="">Seleccionar subcategoría...</option>
                                                <option value="1">Pistola de servicio</option>
                                                <option value="2">Pistola compacta</option>
                                                <option value="3">Revólver</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-3">Marca *</label>
                                            <select class="form-input-modern" required>
                                                <option value="">Seleccionar marca...</option>
                                                <option value="1">Glock</option>
                                                <option value="2">Smith & Wesson</option>
                                                <option value="3">Sig Sauer</option>
                                                <option value="4">Beretta</option>
                                                <option value="5">Heckler & Koch</option>
                                            </select>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-3">Modelo</label>
                                            <select class="form-input-modern">
                                                <option value="">Seleccionar modelo...</option>
                                                <option value="1">Glock 17</option>
                                                <option value="2">Glock 19</option>
                                                <option value="3">Glock 22</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Calibre</label>
                                        <select class="form-input-modern">
                                            <option value="">Seleccionar calibre...</option>
                                            <option value="1">9mm</option>
                                            <option value="2">.40 S&W</option>
                                            <option value="3">.45 ACP</option>
                                            <option value="4">.380 ACP</option>
                                            <option value="5">.357 Magnum</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configuración y Stock -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl border border-emerald-200 overflow-hidden card-shadow">
                                <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-8 py-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="glass-effect rounded-xl p-3">
                                            <i class="fas fa-cogs text-2xl text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-white">Configuración</h3>
                                            <p class="text-white opacity-75">Gestión de inventario</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-8 space-y-6">
                                    <!-- Switches de configuración -->
                                    <div class="grid grid-cols-1 gap-6">
                                        <div class="bg-white rounded-xl p-6 border border-gray-200 card-shadow">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="bg-blue-100 rounded-lg p-3">
                                                        <i class="fas fa-list-ol text-blue-600"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-gray-900">Requiere Número de Serie</h4>
                                                        <p class="text-sm text-gray-600">Seguimiento individual por unidad</p>
                                                    </div>
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" class="sr-only peer toggle-series">
                                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-white rounded-xl p-6 border border-gray-200 card-shadow">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="bg-green-100 rounded-lg p-3">
                                                        <i class="fas fa-globe text-green-600"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-bold text-gray-900">Producto Importado</h4>
                                                        <p class="text-sm text-gray-600">Requiere licencia especial</p>
                                                    </div>
                                                </div>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" class="sr-only peer toggle-importado">
                                                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600"></div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Licencia de importación (condicional) -->
                                    <div class="hidden bg-white rounded-xl p-6 border border-gray-200 card-shadow" id="grupo-licencia">
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Licencia de Importación</label>
                                        <select class="form-input-modern">
                                            <option value="">Seleccionar licencia...</option>
                                            <option value="1">LIC-2024-001 - Pistolas Deportivas</option>
                                            <option value="2">LIC-2024-002 - Rifles de Precisión</option>
                                            <option value="3">LIC-2024-003 - Accesorios Tácticos</option>
                                        </select>
                                    </div>
                                    
                                    <!-- Gestión de stock por cantidad -->
                                    <div id="grupo-cantidad" class="bg-white rounded-xl p-6 border border-gray-200 card-shadow">
                                        <div class="flex items-center space-x-3 mb-4">
                                            <div class="bg-purple-100 rounded-lg p-3">
                                                <i class="fas fa-cubes text-purple-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-900">Stock por Cantidad</h4>
                                                <p class="text-sm text-gray-600">Gestión numérica del inventario</p>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-3">Cantidad Inicial *</label>
                                                <input type="number" class="form-input-modern text-center text-2xl font-bold" min="1" placeholder="0" required>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-3">Código de Lote *</label>
                                                <input type="text" class="form-input-modern" value="LOTE-20241205-143022" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Gestión de series (condicional) -->
                                    <div id="grupo-series" class="hidden bg-white rounded-xl p-6 border border-gray-200 card-shadow">
                                        <div class="flex items-center justify-between mb-6">
                                            <div class="flex items-center space-x-3">
                                                <div class="bg-indigo-100 rounded-lg p-3">
                                                    <i class="fas fa-list-ol text-indigo-600"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-900">Números de Serie</h4>
                                                    <p class="text-sm text-gray-600">Registro individual de cada unidad</p>
                                                </div>
                                            </div>
                                            <span class="text-red-500 text-sm font-medium">* Requerido</span>
                                        </div>
                                        
                                        <div id="container-series" class="space-y-3">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-1">
                                                    <input type="text" class="form-input-modern" name="series[]" placeholder="Número de serie único" required>
                                                </div>
                                                <button type="button" class="bg-green-500 hover:bg-green-600 text-white p-3 rounded-lg transition-colors btn-add-serie">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                                            <div class="flex items-start space-x-3">
                                                <i class="fas fa-info-circle text-indigo-500 mt-0.5"></i>
                                                <div class="text-sm text-indigo-700">
                                                    <p class="font-medium">Consejos para números de serie:</p>
                                                    <ul class="mt-2 space-y-1 list-disc list-inside">
                                                        <li>Cada número debe ser único</li>
                                                        <li>Agregue tantos como productos desee ingresar</li>
                                                        <li>Use el botón + para agregar más campos</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Información del movimiento -->
                                    <div class="bg-white rounded-xl p-6 border border-gray-200 card-shadow">
                                        <div class="flex items-center space-x-3 mb-4">
                                            <div class="bg-green-100 rounded-lg p-3">
                                                <i class="fas fa-truck text-green-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-900">Información del Ingreso</h4>
                                                <p class="text-sm text-gray-600">Datos del proveedor</p>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-3">Origen/Proveedor *</label>
                                                <input type="text" class="form-input-modern" placeholder="Nombre del proveedor o origen" required>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-bold text-gray-700 mb-3">Observaciones</label>
                                                <textarea class="form-input-modern resize-none" rows="3" placeholder="Información adicional sobre el ingreso..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sección de fotos -->
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-200 overflow-hidden card-shadow">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-8 py-6">
                                <div class="flex items-center space-x-4">
                                    <div class="glass-effect rounded-xl p-3">
                                        <i class="fas fa-camera text-2xl text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">Fotos del Producto</h3>
                                        <p class="text-white opacity-75">Documentación visual</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-8">
                                <div class="border-2 border-dashed border-indigo-300 rounded-2xl p-8 text-center hover:border-indigo-400 transition-colors cursor-pointer" id="upload-area">
                                    <div class="space-y-4">
                                        <div class="mx-auto w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-cloud-upload-alt text-2xl text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <label for="fotos" class="cursor-pointer">
                                                <span class="text-lg font-semibold text-gray-900">Subir fotos</span>
                                                <p class="text-gray-600 mt-1">Arrastra archivos aquí o haz clic para seleccionar</p>
                                            </label>
                                            <input type="file" class="hidden" id="fotos" name="fotos[]" multiple accept="image/*">
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <p>Máximo 5MB por imagen • JPG, PNG, GIF</p>
                                            <p>La primera imagen será la foto principal</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Preview de fotos -->
                                <div id="preview-fotos" class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4 hidden"></div>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6 pt-8">
                            <button type="submit" class="btn-modern min-w-[200px]">
                                <i class="fas fa-save mr-3"></i>
                                <span>Ingresar Producto</span>
                            </button>
                            
                            <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-12 py-3 rounded-xl font-semibold transition-all duration-300 min-w-[200px]" id="btn-limpiar-form">
                                <i class="fas fa-broom mr-3"></i>
                                <span>Limpiar Formulario</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: Movimientos -->
                <div class="tab-content animate-fadeInUp" id="movimientos">
                    <div class="text-center mb-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl shadow-lg mb-4">
                            <i class="fas fa-exchange-alt text-2xl text-white"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Registrar Movimiento</h2>
                        <p class="text-gray-600 max-w-md mx-auto">Gestiona egresos y bajas de inventario</p>
                    </div>

                    <form id="form-movimiento" class="space-y-8">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Datos del Movimiento -->
                            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl border border-purple-200 overflow-hidden card-shadow">
                                <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-8 py-6">
                                    <div class="flex items-center space-x-4">
                                        <div class="glass-effect rounded-xl p-3">
                                            <i class="fas fa-exchange-alt text-2xl text-white"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-white">Datos del Movimiento</h3>
                                            <p class="text-white opacity-75">Información del egreso o baja</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-8 space-y-6">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Producto *</label>
                                        <select class="form-input-modern" id="mov-producto" required>
                                            <option value="">Buscar producto...</option>
                                            <option value="1">Glock 17 Gen 5 - Stock: 15</option>
                                            <option value="2">Smith & Wesson M&P9 - Stock: 3</option>
                                            <option value="3">Sig Sauer P320 - Stock: 8</option>
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Tipo de Movimiento *</label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="mov_tipo" value="egreso" class="sr-only peer" required>
                                                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200 hover:border-gray-300">
                                                    <div class="flex items-center space-x-4">
                                                        <div class="bg-blue-100 rounded-lg p-3">
                                                            <i class="fas fa-arrow-right text-blue-600 text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-bold text-gray-900">Egreso</h4>
                                                            <p class="text-sm text-gray-600">Venta o entrega</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                            
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="mov_tipo" value="baja" class="sr-only peer" required>
                                                <div class="bg-white border-2 border-gray-200 rounded-xl p-6 peer-checked:border-red-500 peer-checked:bg-red-50 transition-all duration-200 hover:border-gray-300">
                                                    <div class="flex items-center space-x-4">
                                                        <div class="bg-red-100 rounded-lg p-3">
                                                            <i class="fas fa-times text-red-600 text-xl"></i>
                                                        </div>
                                                        <div>
                                                            <h4 class="font-bold text-gray-900">Baja</h4>
                                                            <p class="text-sm text-gray-600">Destrucción o pérdida</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Destino/Motivo *</label>
                                        <input type="text" class="form-input-modern" placeholder="Cliente, destino o motivo del movimiento" required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-3">Observaciones</label>
                                        <textarea class="form-input-modern resize-none" rows="3" placeholder="Información adicional sobre el movimiento..."></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del Producto Seleccionado -->
                            <div class="space-y-6">
                                <!-- Panel de información -->
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 overflow-hidden card-shadow">
                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-8 py-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="glass-effect rounded-xl p-3">
                                                <i class="fas fa-info-circle text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-white">Información del Producto</h3>
                                                <p class="text-white opacity-75">Detalles y stock disponible</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="p-8">
                                        <div id="info-producto" class="hidden">
                                            <div class="flex items-center space-x-4 mb-6">
                                                <div class="w-16 h-16 bg-gray-200 rounded-xl flex items-center justify-center">
                                                    <i class="fas fa-gun text-gray-600 text-2xl"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="text-xl font-bold text-gray-900" id="producto-nombre">Glock 17 Gen 5</h4>
                                                    <p class="text-gray-600" id="producto-categoria">Pistolas > Glock</p>
                                                    <div class="mt-2">
                                                        <span class="status-badge status-success">En Stock</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                    <div class="text-sm font-medium text-gray-600">Stock Disponible</div>
                                                    <div class="text-2xl font-bold text-green-600" id="stock-disponible">15</div>
                                                </div>
                                                <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                    <div class="text-sm font-medium text-gray-600">Código</div>
                                                    <div class="text-lg font-semibold text-gray-900" id="producto-codigo">#001</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="placeholder-producto" class="text-center py-12">
                                            <div class="mx-auto w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <i class="fas fa-box-open text-3xl text-gray-400"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Selecciona un producto</h3>
                                            <p class="text-gray-600">Elige un producto para ver su información</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Panel para cantidad -->
                                <div id="panel-cantidad" class="hidden bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-200 overflow-hidden card-shadow">
                                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-8 py-6">
                                        <div class="flex items-center space-x-4">
                                            <div class="glass-effect rounded-xl p-3">
                                                <i class="fas fa-sort-numeric-up text-2xl text-white"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-white">Cantidad a Mover</h3>
                                                <p class="text-white opacity-75">Especifica cuántas unidades</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="p-8">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-3">Cantidad *</label>
                                            <input type="number" class="form-input-modern text-center text-2xl font-bold" min="1" max="15" placeholder="0" required>
                                            <div class="mt-3 p-4 bg-amber-100 rounded-lg border border-amber-200">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm font-medium text-amber-800">Stock disponible:</span>
                                                    <span class="text-lg font-bold text-amber-900">15</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6 pt-8">
                            <button type="submit" class="btn-modern min-w-[200px]">
                                <i class="fas fa-check mr-3"></i>
                                <span>Registrar Movimiento</span>
                            </button>
                            
                            <button type="button" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-12 py-3 rounded-xl font-semibold transition-all duration-300 min-w-[200px]" id="btn-limpiar-movimiento">
                                <i class="fas fa-broom mr-3"></i>
                                <span>Limpiar</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 4: Historial -->
                <div class="tab-content animate-fadeInUp" id="historial">
                    <div class="text-center mb-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg mb-4">
                            <i class="fas fa-history text-2xl text-white"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">Historial de Movimientos</h2>
                        <p class="text-gray-600 max-w-md mx-auto">Registro completo de transacciones</p>
                    </div>
                    
                    <!-- Filtros del historial -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8 mb-8 border border-indigo-100 card-shadow">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-4">
                                <div class="bg-indigo-500 rounded-xl p-3 shadow-lg">
                                    <i class="fas fa-filter text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">Filtros de Historial</h3>
                                    <p class="text-gray-600">Busca movimientos específicos</p>
                                </div>
                            </div>
                            <div class="hidden lg:flex items-center space-x-4">
                                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                        <span>Ingreso</span>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                                        <span>Egreso</span>
                                    </div>
                                </div>
                                <div class="bg-white rounded-lg px-4 py-2 shadow-sm">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                        <span>Baja</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-6">
                            <div class="xl:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Producto</label>
                                <select class="form-input-modern">
                                    <option value="">Todos los productos</option>
                                    <option value="1">Glock 17 Gen 5</option>
                                    <option value="2">Smith & Wesson M&P9</option>
                                    <option value="3">Sig Sauer P320</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Desde</label>
                                <input type="date" class="form-input-modern" value="2024-11-01">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Hasta</label>
                                <input type="date" class="form-input-modern" value="2024-12-05">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Tipo</label>
                                <select class="form-input-modern">
                                    <option value="">Todos</option>
                                    <option value="ingreso">Ingreso</option>
                                    <option value="egreso">Egreso</option>
                                    <option value="baja">Baja</option>
                                </select>
                            </div>
                            
                            <div class="flex flex-col justify-end">
                                <button type="button" class="btn-modern">
                                    <i class="fas fa-search mr-2"></i>
                                    <span class="hidden sm:inline">Filtrar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de historial -->
                    <div class="bg-white rounded-2xl card-shadow overflow-hidden border border-gray-100">
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-6 border-b border-gray-200">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Registro de Movimientos</h3>
                                    <p class="text-gray-600 mt-1">Historial completo de transacciones</p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="bg-white rounded-lg px-4 py-2 shadow-sm border">
                                        <div class="flex items-center space-x-2 text-sm">
                                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                            <span class="text-gray-700 font-medium">En tiempo real</span>
                                        </div>
                                    </div>
                                    <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2">
                                        <i class="fas fa-file-excel"></i>
                                        <span class="hidden sm:inline">Exportar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table id="tabla-historial" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-clock text-gray-400"></i>
                                                <span>Fecha</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-box text-gray-400"></i>
                                                <span>Producto</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-exchange-alt text-gray-400"></i>
                                                <span>Tipo</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-map-marker-alt text-gray-400"></i>
                                                <span>Origen/Destino</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-sort-numeric-up text-gray-400"></i>
                                                <span>Cantidad</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas fa-user text-gray-400"></i>
                                                <span>Usuario</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-12-05 14:30</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-gun text-gray-600 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Glock 17 Gen 5</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge bg-green-100 text-green-800">Ingreso</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Proveedor ABC</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">5</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Admin</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-12-04 16:45</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-gun text-gray-600 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Smith & Wesson M&P9</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge bg-blue-100 text-blue-800">Egreso</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Cliente XYZ</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Vendedor1</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-12-03 09:15</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-gun text-gray-600 text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">Sig Sauer P320</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="status-badge bg-red-100 text-red-800">Baja</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Defecto de fábrica</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">1</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Admin</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modales -->
    <!-- Modal de Detalles del Producto -->
    <div id="modal-detalle" class="fixed inset-0 z-50 hidden overflow-y-auto modal">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl px-4 pt-5 pb-4 text-left overflow-hidden card-shadow transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-info-circle text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-bold text-gray-900">
                            Detalles del Producto
                        </h3>
                        <div class="mt-6" id="contenido-detalle">
                            <!-- Contenido dinámico -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nombre</label>
                                        <p class="text-lg font-semibold text-gray-900">Glock 17 Gen 5</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Categoría</label>
                                        <p class="text-gray-900">Pistolas</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Marca</label>
                                        <p class="text-gray-900">Glock</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stock Actual</label>
                                        <p class="text-2xl font-bold text-green-600">15 unidades</p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Código</label>
                                        <p class="text-gray-900">#001</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Calibre</label>
                                        <p class="text-gray-900">9mm</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Estado</label>
                                        <span class="status-badge status-success">Normal</span>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Última Actualización</label>
                                        <p class="text-gray-900">2024-12-05 14:30</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" class="btn-modern sm:ml-3 sm:w-auto" onclick="cerrarModal('modal-detalle')">
                        <i class="fas fa-times mr-2"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast/Notificaciones -->
    <div id="toast-container" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

  