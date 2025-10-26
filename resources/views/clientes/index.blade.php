@extends('layouts.app')

@section('title', 'Gestión de Clientes')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Pasar datos iniciales -->
<script id="clientes-data" type="application/json">
    @json($clientesData->values())
</script>

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header con gradiente -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Gestión de Clientes
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Administra y organiza la información de tus clientes
                    </p>
                </div>
                <button onclick="clientesManager.openCreateModal()" 
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Cliente
                </button>
            </div>
        </div>

        <!-- Alertas -->
        @if (session('success'))
            <div class="mb-6 rounded-xl bg-green-50 border border-green-200 p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="inline-flex text-green-400 hover:text-green-600">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- KPIs Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Clientes -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-200 hover:scale-105 hover:shadow-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Clientes</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clientes Normales -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-200 hover:scale-105 hover:shadow-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Normales</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-gray-900">{{ $stats['normales'] }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clientes Premium -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-200 hover:scale-105 hover:shadow-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Premium</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-gray-900">{{ $stats['premium'] }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empresas -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden transform transition-all duration-200 hover:scale-105 hover:shadow-xl">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Empresas</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-gray-900">{{ $stats['empresas'] }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-md mb-6">
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar cliente</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text"
                                   id="search-clientes"
                                   placeholder="Nombre, DPI, teléfono, ID..."
                                   class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cliente</label>
                        <select id="tipo-filter"
                                class="block w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Todos</option>
                            <option value="1">Normal</option>
                            <option value="2">Premium</option>
                            <option value="3">Empresa</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="clientesManager.clearFilters()"
                                class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">DPI</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Teléfono</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="clientes-tbody">
                        <!-- Los datos se cargarán con JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        @if($clientes->hasPages())
            <div class="mt-6">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Nuevo/Editar Cliente -->
<div id="modalNuevoCliente" class="fixed inset-0 z-50 hidden">
    <!-- Overlay -->
    <div id="modalOverlayNC" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Contenido -->
    <div class="relative mx-auto mt-8 mb-8 w-11/12 sm:w-[42rem] bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-purple-600 p-5 border-b flex items-center justify-between rounded-t-2xl z-10">
            <h3 class="text-xl font-bold text-white flex items-center">
                <i class="fas fa-user-plus mr-3"></i>
                Registrar nuevo cliente
            </h3>
            <button id="modalCerrarNC" class="p-2 rounded-lg hover:bg-white/20 transition-colors duration-200">
                <i class="fas fa-times text-2xl text-white"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-8 space-y-6">
            <form id="formNuevoCliente">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    <!-- Tipo de Cliente y Selector Premium -->
                    <div class="sm:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="tipoCliente" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user-tag mr-1 text-blue-500"></i>
                                    Tipo de Cliente *
                                </label>
                                <select id="tipoCliente" name="cliente_tipo" required
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    <option value="">Seleccionar...</option>
                                    <option value="1">Cliente Normal</option>
                                    <option value="2">Cliente Premium</option>
                                    <option value="3">Cliente Empresa</option>
                                </select>
                            </div>

                            <!-- Cliente Premium -->
                            <div id="selectorPremium" style="display: none;">
                                <label for="clientePremium" class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-star mr-1 text-purple-500"></i>
                                    Usuario Premium
                                </label>
                                <select id="clientePremium" name="clientePremium"
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200">
                                    <option value="">Seleccionar usuario...</option>
                                    @foreach ($usuariosPremium as $usuario)
                                        <option value="{{ $usuario->user_id }}" 
                                                data-clienteid="{{ $usuario->user_id }}"
                                                data-nombre1="{{ $usuario->user_primer_nombre }}"
                                                data-nombre2="{{ $usuario->user_segundo_nombre ?? '' }}"
                                                data-apellido1="{{ $usuario->user_primer_apellido }}"
                                                data-apellido2="{{ $usuario->user_segundo_apellido ?? '' }}"
                                                data-dpi="{{ $usuario->user_dpi_dni }}">
                                            {{ $usuario->user_primer_nombre }} {{ $usuario->user_segundo_nombre }}
                                            {{ $usuario->user_primer_apellido }} {{ $usuario->user_segundo_apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ID Cliente (oculto) -->
                    <input id="idCliente" name="cliente_user_id" type="hidden">

                    <!-- Contenedor Empresa -->
                    <div id="contenedorempresa" class="sm:col-span-2 w-full hidden">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-building mr-1 text-green-500"></i>
                            Nombre de la Empresa
                        </label>
                        <input id="nombreEmpresa" name="cliente_nom_empresa" type="text"
                            placeholder="Nombre de la empresa" disabled
                            class="hidden w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">

                        <!-- Título Propietario -->
                        <p id="titulopropietario" class="hidden mt-4 text-base font-bold text-gray-800 border-b-2 border-green-500 pb-2">
                            <i class="fas fa-user-tie mr-2 text-green-600"></i>
                            Datos del Propietario
                        </p>
                    </div>

                    <!-- Nombres -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Primer Nombre *
                        </label>
                        <input id="nc_nombre1" name="cliente_nombre1" type="text" placeholder="Primer nombre"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Segundo Nombre
                        </label>
                        <input id="nc_nombre2" name="cliente_nombre2" type="text" placeholder="Segundo nombre"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <!-- Apellidos -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Primer Apellido *
                        </label>
                        <input id="nc_apellido1" name="cliente_apellido1" type="text" placeholder="Primer apellido"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Segundo Apellido
                        </label>
                        <input id="nc_apellido2" name="cliente_apellido2" type="text" placeholder="Segundo apellido"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <!-- DPI y NIT -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-1 text-gray-500"></i>
                            DPI
                        </label>
                        <input id="nc_dpi" name="cliente_dpi" type="text" placeholder="1234567890101"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-invoice mr-1 text-gray-500"></i>
                            NIT
                        </label>
                        <input id="nc_nit" name="cliente_nit" type="text" placeholder="12345678"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <!-- Teléfono y Correo -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1 text-gray-500"></i>
                            Teléfono
                        </label>
                        <input id="nc_telefono" name="cliente_telefono" type="tel" placeholder="12345678"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1 text-gray-500"></i>
                            Correo Electrónico
                        </label>
                        <input id="nc_correo" name="cliente_correo" type="email" placeholder="correo@ejemplo.com"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <!-- Nombre y Teléfono Vendedor (Empresa) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-tie mr-1 text-gray-500"></i>
                            Nombre del Vendedor
                        </label>
                        <input id="nc_nombre_vendedor" name="cliente_nom_vendedor" type="text"
                            placeholder="Nombre vendedor" disabled
                            class="hidden w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-mobile-alt mr-1 text-gray-500"></i>
                            Teléfono Vendedor
                        </label>
                        <input id="nc_telefono_vendedor" name="cliente_cel_vendedor" disabled type="tel" 
                            placeholder="Teléfono vendedor"
                            class="hidden w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>

                    <!-- Dirección -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-gray-500"></i>
                            Dirección
                        </label>
                        <textarea id="nc_direccion" name="cliente_direccion" rows="2"
                            placeholder="Dirección completa del cliente"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none"></textarea>
                    </div>

                    <!-- Ubicación Empresa -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marked-alt mr-1 text-gray-500"></i>
                            Ubicación Empresa
                        </label>
                        <input id="nc_ubicacion" name="cliente_ubicacion" type="text" 
                            placeholder="Ubicación de la empresa" disabled
                            class="hidden w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                    </div>
                </div>

                <!-- Estado mensajes -->
                <div id="nc_estado" class="mt-4 text-xs text-gray-500"></div>
            </form>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-gray-50 px-8 py-5 border-t flex items-center justify-end gap-3 rounded-b-2xl">
            <button id="modalCancelarNC" type="button"
                class="inline-flex items-center px-6 py-3 border-2 border-gray-300 text-sm font-semibold rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <i class="fas fa-times mr-2"></i>
                Cancelar
            </button>
            <button id="modalGuardarCliente" type="button"
                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-semibold rounded-xl shadow-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <i class="fas fa-save mr-2"></i>
                Guardar
            </button>
        </div>
    </div>
</div>

@endsection

@vite('resources/js/clientes/clientes.js')