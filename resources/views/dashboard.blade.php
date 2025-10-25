@extends('layouts.app')

@section('content')
<header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 mb-6 py-4 px-4 sm:px-6 lg:px-8 shadow-sm rounded-md">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-slate-100 leading-tight">
            Dashboard - Sistema de Inventario
        </h2>
        <div class="flex items-center space-x-4">
            <button id="btn-actualizar-dashboard" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-sync-alt"></i>
                <span>Actualizar</span>
            </button>
            <div class="text-sm text-slate-600 dark:text-slate-300">
                Bienvenido, {{ Auth::user()->name }}
            </div>
        </div>
    </div>
</header>

<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Armas -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Total Armas</p>
                    <p class="text-3xl font-bold text-slate-900" data-stat="total-armas">0</p>
                    <p class="text-xs text-slate-500 mt-1">En inventario</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Ventas del Mes -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Ventas del Mes</p>
                    <p class="text-3xl font-bold text-slate-900" data-stat="ventas-mes">0</p>
                    <p class="text-xs text-slate-500 mt-1">Transacciones</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-2.4 0L5 7h14m-4 6v6a1 1 0 01-1 1H6a1 1 0 01-1-1v-6m6 0V9a1 1 0 011-1h2a1 1 0 011 1v4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Clientes Registrados -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Clientes</p>
                    <p class="text-3xl font-bold text-slate-900" data-stat="total-clientes">0</p>
                    <p class="text-xs text-slate-500 mt-1">Registrados</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Licencias Activas -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-600">Licencias</p>
                    <p class="text-3xl font-bold text-slate-900" data-stat="licencias-activas">0</p>
                    <p class="text-xs text-slate-500 mt-1">Activas</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800">Ventas Recientes</h3>
                <a href="/reportes" class="text-sm text-blue-600 hover:text-blue-800">
                    Ver todas →
                </a>
            </div>
            <div class="p-6" id="ventas-recientes-container">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="text-slate-500 mt-4">Cargando ventas...</p>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800">Alertas de Stock</h3>
                <a href="/inventario" class="text-sm text-blue-600 hover:text-blue-800">
                    Ver inventario →
                </a>
            </div>
            <div class="p-6" id="alertas-stock-container">
                <div class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-600 mx-auto"></div>
                    <p class="text-slate-500 mt-4">Cargando alertas...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Acciones Rápidas</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Nueva Venta -->
            <button data-accion="nueva-venta" 
                    class="flex items-center space-x-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-green-300 transition-all text-left group">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Nueva Venta</p>
                    <p class="text-sm text-slate-500">Registrar venta</p>
                </div>
            </button>

            <!-- Agregar Arma -->
            <button data-accion="agregar-arma" 
                    class="flex items-center space-x-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-blue-300 transition-all text-left group">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Agregar Arma</p>
                    <p class="text-sm text-slate-500">Nuevo inventario</p>
                </div>
            </button>

            <!-- Nuevo Cliente -->
            <button data-accion="nuevo-cliente" 
                    class="flex items-center space-x-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-purple-300 transition-all text-left group">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Nuevo Cliente</p>
                    <p class="text-sm text-slate-500">Registrar cliente</p>
                </div>
            </button>

            <!-- Generar Reporte -->
            <button data-accion="generar-reporte" 
                    class="flex items-center space-x-3 p-4 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-orange-300 transition-all text-left group">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="font-medium text-slate-800">Generar Reporte</p>
                    <p class="text-sm text-slate-500">Reportes legales</p>
                </div>
            </button>
        </div>
    </div>
</div>


@endsection

@vite('resources/js/dashboard.js')
