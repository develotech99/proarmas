@extends('layouts.app')

@section('title', 'Gestión de Licencias de Importación')

@section('content')
<!-- Meta tag para CSRF token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Pasar datos a JavaScript -->
<script id="licencias-data" type="application/json">
@json($licencias->items())
</script>

<script id="empresas-data" type="application/json">
@json($empresas)
</script>

<script id="clases-data" type="application/json">
@json($clases)
</script>

<script id="marcas-data" type="application/json">
@json($marcas)
</script>

<script id="modelos-data" type="application/json">
@json($modelos)
</script>

<script id="calibres-data" type="application/json">
@json($calibres)
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="licenciasManager()">
    
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                Gestión de Licencias de Importación
            </h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button @click="openCreateModal()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Licencia
            </button>
        </div>
    </div>

    <!-- Alerts -->
    @if (session('success'))
        <div class="rounded-md bg-green-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-md bg-red-50 p-4 mb-6">
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

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Licencias</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $licencias->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Activas</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $licencias->where('lipaimp_situacion', 1)->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Próximas a Vencer</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $licencias->where('lipaimp_fecha_vencimiento', '<=', now()->addDays(30))->where('lipaimp_situacion', 1)->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Inactivas</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $licencias->where('lipaimp_situacion', 0)->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar descripción</label>
                    <input type="text" 
                           x-model="searchTerm" 
                           @input="filterLicencias()"
                           placeholder="Descripción de licencia..."
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Empresa</label>
                    <select x-model="empresaFilter" 
                            @change="filterLicencias()"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <option value="">Todas las empresas</option>
                        @foreach($empresas as $empresa)
                            <option value="{{ $empresa->empresaimp_id }}">{{ $empresa->empresaimp_nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clase</label>
                    <select x-model="claseFilter" 
                            @change="filterLicencias()"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <option value="">Todas las clases</option>
                        @foreach($clases as $clase)
                            <option value="{{ $clase->clase_id }}">{{ $clase->clase_nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                    <select x-model="statusFilter" 
                            @change="filterLicencias()"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <option value="">Todos los estados</option>
                        <option value="1">Activas</option>
                        <option value="0">Inactivas</option>
                        <option value="vencidas">Vencidas</option>
                        <option value="por_vencer">Por vencer (30 días)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
                    <select x-model="yearFilter" 
                            @change="filterLicencias()"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <option value="">Todos los años</option>
                        @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="clearFilters()" 
                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Póliza
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Empresa
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Especificaciones
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Vencimiento
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($licencias as $licencia)
                                <tr x-show="showLicencia({{ $licencia->lipaimp_id }})" x-transition>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $licencia->lipaimp_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $licencia->lipaimp_poliza ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $licencia->lipaimp_descripcion ?? 'Sin descripción' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($licencia->empresa)
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $licencia->empresa->empresaimp_nombre }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Sin empresa</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs space-y-1">
                                            @if($licencia->clase)
                                                <div class="flex items-center">
                                                    <span class="text-gray-500 w-12">Clase:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $licencia->clase->clase_nombre }}</span>
                                                </div>
                                            @endif
                                            @if($licencia->marca)
                                                <div class="flex items-center">
                                                    <span class="text-gray-500 w-12">Marca:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $licencia->marca->marca_nombre }}</span>
                                                </div>
                                            @endif
                                            @if($licencia->modelo)
                                                <div class="flex items-center">
                                                    <span class="text-gray-500 w-12">Modelo:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $licencia->modelo->modelo_nombre }}</span>
                                                </div>
                                            @endif
                                            @if($licencia->calibre)
                                                <div class="flex items-center">
                                                    <span class="text-gray-500 w-12">Calibre:</span>
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $licencia->calibre->calibre_nombre }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        @if($licencia->lipaimp_fecha_vencimiento)
                                            @php
                                                $vencimiento = \Carbon\Carbon::parse($licencia->lipaimp_fecha_vencimiento);
                                                $diasParaVencer = $vencimiento->diffInDays(now(), false);
                                            @endphp
                                            <div class="flex flex-col">
                                                <span class="font-mono">{{ $vencimiento->format('d/m/Y') }}</span>
                                                @if($diasParaVencer > 0)
                                                    <span class="text-xs text-red-600">Vencida hace {{ abs($diasParaVencer) }} días</span>
                                                @elseif($diasParaVencer > -30)
                                                    <span class="text-xs text-yellow-600">Vence en {{ abs($diasParaVencer) }} días</span>
                                                @else
                                                    <span class="text-xs text-green-600">Vigente</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Sin fecha</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $vencida = $licencia->lipaimp_fecha_vencimiento && 
                                                      \Carbon\Carbon::parse($licencia->lipaimp_fecha_vencimiento)->isPast();
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($licencia->lipaimp_situacion == 0)
                                                bg-red-100 text-red-800
                                            @elseif($vencida)
                                                bg-red-100 text-red-800
                                            @else
                                                bg-green-100 text-green-800
                                            @endif"
                                              data-status="{{ $licencia->lipaimp_situacion }}">
                                            @if($licencia->lipaimp_situacion == 0)
                                                Inactiva
                                            @elseif($vencida)
                                                Vencida
                                            @else
                                                Activa
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button @click="editLicencia({{ $licencia->lipaimp_id }})" 
                                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                    title="Editar">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteLicencia({{ $licencia->lipaimp_id }})" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                    title="Eliminar">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="mt-2">No hay licencias de importación registradas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($licencias->hasPages())
        <div class="mt-6">
            {{ $licencias->links() }}
        </div>
    @endif

    <!-- Modal para Crear/Editar -->
    <div x-show="showModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                
                <form @submit="handleFormSubmit($event)">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="isEditing ? 'Editar Licencia de Importación' : 'Crear Nueva Licencia de Importación'"></h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Número de Póliza -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de póliza/factura</label>
                            <input type="number" 
                                   x-model="formData.lipaimp_poliza"
                                   @input="validateForm()"
                                   placeholder="Número de póliza o factura"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                            <input type="text" 
                                   x-model="formData.lipaimp_descripcion"
                                   maxlength="100"
                                   @input="validateForm()"
                                   placeholder="Descripción identificativa"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Empresa -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Empresa *</label>
                            <select x-model="formData.lipaimp_empresa"
                                    required
                                    @change="validateForm()"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="">Seleccionar empresa</option>
                                <template x-for="empresa in empresas" :key="empresa.empresaimp_id">
                                    <option :value="empresa.empresaimp_id" x-text="empresa.empresaimp_nombre"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Fecha de Vencimiento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de vencimiento</label>
                            <input type="date" 
                                   x-model="formData.lipaimp_fecha_vencimiento"
                                   @input="validateForm()"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Clase de Arma -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clase de arma</label>
                            <select x-model="formData.lipaimp_clase"
                                    @change="validateForm()"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="">Seleccionar clase</option>
                                <template x-for="clase in clases" :key="clase.clase_id">
                                    <option :value="clase.clase_id" x-text="clase.clase_nombre"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Marca -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Marca</label>
                            <select x-model="formData.lipaimp_marca"
                                    @change="validateForm()"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="">Seleccionar marca</option>
                                <template x-for="marca in marcas" :key="marca.marca_id">
                                    <option :value="marca.marca_id" x-text="marca.marca_nombre"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Modelo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Modelo</label>
                            <select x-model="formData.lipaimp_modelo"
                                    @change="validateForm()"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="">Seleccionar modelo</option>
                                <template x-for="modelo in modelos" :key="modelo.modelo_id">
                                    <option :value="modelo.modelo_id" x-text="modelo.modelo_nombre"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Calibre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Calibre</label>
                            <select x-model="formData.lipaimp_calibre"
                                    @change="validateForm()"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="">Seleccionar calibre</option>
                                <template x-for="calibre in calibres" :key="calibre.calibre_id">
                                    <option :value="calibre.calibre_id" x-text="calibre.calibre_nombre"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
                            <select x-model="formData.lipaimp_situacion"
                                    required
                                    @change="validateForm()"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="">Seleccionar estado</option>
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" 
                                @click="closeModal()" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="isSubmitting || !isFormValid()"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting" x-text="isEditing ? 'Actualizar' : 'Crear'"></span>
                            <span x-show="isSubmitting" class="flex items-center">
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

@vite('resources/js/licencias-importacion/index.js')