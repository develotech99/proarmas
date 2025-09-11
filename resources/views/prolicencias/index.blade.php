@extends('layouts.app')

@section('title', 'Gestión de Licencias de Importación')

@section('content')
<!-- Meta CSRF -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Datos para Alpine/JS -->
<script id="licencias-data" type="application/json">
@json($licencias->items())
</script>
<script id="empresas-data" type="application/json">[]</script>
<script id="modelos-data" type="application/json">[]</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
      x-data="licenciasManager()"
  x-init="
    formData.lipaimp_id = formData.lipaimp_id || '';
  ">

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

    <!-- Alerts flash -->
    @if (session('success'))
        <div class="rounded-md bg-green-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
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
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Alertas dinámicas -->
    <div class="fixed top-4 right-4 z-50">
        <template x-for="alert in alerts" :key="alert.id">
            <div x-show="alert"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-2 rounded-md p-4 shadow-lg max-w-sm"
                 :class="{
                    'bg-green-50 text-green-800 border border-green-200': alert.type === 'success',
                    'bg-red-50 text-red-800 border border-red-200': alert.type === 'error'
                 }">
                <div class="flex justify-between items-center">
                    <span x-text="alert.message" class="text-sm font-medium"></span>
                    <button @click="removeAlert(alert.id)" class="ml-2 text-current opacity-70 hover:opacity-100">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Stats -->
    @php
        $totalLic   = $licencias->total();
        $pendientes = $licencias->getCollection()->where('lipaimp_situacion', 1)->count();
        $autorizadas= $licencias->getCollection()->where('lipaimp_situacion', 2)->count();
        $rechazadas = $licencias->getCollection()->where('lipaimp_situacion', 3)->count();
        $enTransito = $licencias->getCollection()->where('lipaimp_situacion', 4)->count();
        $recibidas  = $licencias->getCollection()->where('lipaimp_situacion', 5)->count();
    @endphp
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg"><div class="p-5"><div class="flex items-center"><div class="flex-shrink-0"><svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div><div class="ml-5 w-0 flex-1"><dl><dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total</dt><dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $totalLic }}</dd></dl></div></div></div></div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg"><div class="p-5"><div class="flex items-center"><div class="flex-shrink-0"><svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg></div><div class="ml-5 w-0 flex-1"><dl><dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Pendientes</dt><dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $pendientes }}</dd></dl></div></div></div></div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg"><div class="p-5"><div class="flex items-center"><div class="flex-shrink-0"><svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div class="ml-5 w-0 flex-1"><dl><dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Autorizadas</dt><dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $autorizadas }}</dd></dl></div></div></div></div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg"><div class="p-5"><div class="flex items-center"><div class="flex-shrink-0"><svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></div><div class="ml-5 w-0 flex-1"><dl><dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">En Tránsito</dt><dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $enTransito }}</dd></dl></div></div></div></div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg"><div class="p-5"><div class="flex items-center"><div class="flex-shrink-0"><svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg></div><div class="ml-5 w-0 flex-1"><dl><dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Recibidas</dt><dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $recibidas }}</dd></dl></div></div></div></div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar</label>
                    <input type="text"
                           x-model="searchTerm"
                           @input="filterLicencias()"
                           placeholder="Descripción u observaciones..."
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
                    <select x-model="statusFilter"
                            @change="filterLicencias()"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <option value="">Todos</option>
                        <option value="1">Pendiente</option>
                        <option value="2">Autorizado</option>
                        <option value="3">Rechazado</option>
                        <option value="4">En Tránsito</option>
                        <option value="5">Recibido</option>
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

    <!-- Tabla -->
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No. Licencia</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subcat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marca</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Calibre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Modelo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Largo de cañón</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($licencias as $licencia)
                            @php
                                $armas = collect($licencia->armas ?? []);
                                $totalCant = $armas->sum('arma_cantidad');

                                // <- NOMBRES CORRECTOS
                                $subcats  = $armas->map(fn($a) => data_get($a, 'subcategoria.subcategoria_nombre'))
                                                  ->filter()->unique()->values();

                                // Puedes mostrar nombre completo del calibre (incl. unidad) si tu accessor existe
                                 $calibres = $armas->map(fn($a) => data_get($a, 'arma_calibre'))
                    ->filter()->unique()->values();

                                // Estas dos dependerán de que tengas relaciones definidas; si no, quedarán en "—"
                                $marcas = $armas->map(fn($a) => data_get($a, 'modelo.marca.marca_descripcion'))
                ->filter()->unique()->values();

                                $modelos  = $armas->pluck('modelo.modelo_descripcion')->filter()->unique()->values();

                                // Largo de cañón desde modelo.largo_canon o un campo propio si lo tienes
                                $largos   = $armas->map(fn($a) => data_get($a, 'modelo.largo_canon') ?? data_get($a, 'arma_largo_canon'))
                                                  ->filter()->unique()->values();

                                $joinOrVarios = function ($col) {
                                    if ($col->isEmpty()) return '—';
                                    if ($col->count() === 1) return $col->first();
                                    return $col->take(2)->implode(', ').' … (Varios)';
                                };
                                

                             
                            @endphp

                            <tr x-show="showLicencia({{ $licencia->lipaimp_id }})" x-transition>
                                <!-- No. Licencia -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <div class="font-medium">{{ $licencia->lipaimp_id }}</div>
                                    @if($licencia->lipaimp_descripcion)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $licencia->lipaimp_descripcion }}</div>
                                    @endif
                                </td>

                                <!-- Cantidad total -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $totalCant }}
                                    </span>
                                </td>

                                <!-- Subcat -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $joinOrVarios($subcats) }}
                                </td>

                                <!-- Marca -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $joinOrVarios($marcas) }}
                                </td>

                                <!-- Calibre -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $joinOrVarios($calibres) }}
                                </td>

                                <!-- Modelo -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $joinOrVarios($modelos) }}
                                </td>

                                <!-- Largo de cañón -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $joinOrVarios($largos) }}
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button @click="editLicencia({{ $licencia->lipaimp_id }})"
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteLicencia({{ $licencia->lipaimp_id }})"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <p class="mt-2">No hay licencias registradas.</p>
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

    <!-- Paginación -->
    @if($licencias->hasPages())
        <div class="mt-6">
            {{ $licencias->links() }}
        </div>
    @endif

    <!-- Modal Crear/Editar -->
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
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

            <div x-show="showModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full sm:p-6">

        <form @submit="handleFormSubmit($event)">
  <div class="mb-4">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100"
        x-text="isEditing ? 'Editar Licencia' : 'Crear Nueva Licencia'"></h3>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <!-- ID -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Licencia (ID) *</label>
      <input type="number"
             x-model="formData.lipaimp_id"
             :disabled="isEditing"
             required
             @input="validateForm()"
             placeholder="Ej: 20250001"
             class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
      <p class="text-xs text-gray-500 mt-1" x-show="isEditing">El ID no se puede modificar.</p>
    </div>

    <!-- Póliza -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Póliza</label>
      <input type="number"
             x-model="formData.lipaimp_poliza"
             @input="validateForm()"
             placeholder="Ej: 123456"
             class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
    </div>

    <!-- Descripción -->
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
      <input type="text"
             x-model="formData.lipaimp_descripcion"
             maxlength="255"
             @input="validateForm()"
             placeholder="Descripción de la licencia"
             class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
    </div>

    <!-- Fechas -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Emisión</label>
      <input type="date"
             x-model="formData.lipaimp_fecha_emision"
             @input="validateForm()"
             class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Vencimiento</label>
      <input type="date"
             x-model="formData.lipaimp_fecha_vencimiento"
             @input="validateForm()"
             class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
    </div>

    <!-- Estado -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado *</label>
      <select x-model="formData.lipaimp_situacion"
              required
              @change="validateForm()"
              class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
        <option value="">Seleccionar estado</option>
        <option value="1">Pendiente</option>
        <option value="2">Autorizado</option>
        <option value="3">Rechazado</option>
        <option value="4">En Tránsito</option>
        <option value="5">Recibido</option>
      </select>
    </div>

    <!-- Observaciones -->
    <div class="md:col-span-2">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
      <textarea x-model="formData.lipaimp_observaciones"
                rows="3"
                @input="validateForm()"
                placeholder="Observaciones adicionales..."
                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm"></textarea>
    </div>

  <!-- ================== DETALLE DE ARMAS (repetidor) ================== -->
<div class="md:col-span-2 mt-2">
  <div class="flex items-center justify-between">
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Armas de esta licencia</label>
    <button type="button"
            @click="addArma()"
            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
      + Agregar arma
    </button>
  </div>

  <template x-if="!formData.armas.length">
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No has agregado armas aún.</p>
  </template>

  <div class="mt-3 space-y-4">
    <template x-for="(a, idx) in formData.armas" :key="a._rowKey">
      <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900">
        <div class="flex justify-between items-start mb-3">
          <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Arma #<span x-text="idx+1"></span></h4>
          <button type="button"
                  @click="removeArma(idx)"
                  class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm">
            Quitar
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
          <!-- Subcategoría -->
          <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Subcategoría *</label>
            <select x-model="a.arma_sub_cat" required
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
              <option value="">Seleccione…</option>
              @foreach($subcategorias as $sc)
                <option value="{{ $sc->subcategoria_id }}">{{ $sc->subcategoria_nombre }}</option>
              @endforeach
            </select>
          </div>

          <!-- Modelo -->
          <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Modelo *</label>
            <select x-model="a.arma_modelo" required
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
              <option value="">Seleccione…</option>
  @foreach($modelosSelect as $m)
    <option value="{{ $m->modelo_id }}">{{ $m->modelo_descripcion }}</option>
  @endforeach

            </select>
          </div>

          <!-- Empresa -->
          <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Empresa *</label>
            <select x-model="a.arma_empresa" required
                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
              <option value="">Seleccione…</option>
              @foreach($empresas as $e)
                <option value="{{ $e->empresaimp_id }}">{{ $e->empresaimp_descripcion }}</option>
              @endforeach
            </select>
          </div>

          <!-- Largo de cañón -->
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Largo de cañón *</label>
            <input type="number" step="0.01" min="0.01" x-model="a.arma_largo_canon" required
                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
          </div>

          <!-- Cantidad -->
          <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">Cantidad *</label>
            <input type="number" min="1" x-model.number="a.arma_cantidad" required
                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
          </div>
        </div>
      </div>
    </template>
  </div>
</div>
<!-- ================== /DETALLE DE ARMAS ================== -->

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
          <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
          </path>
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

<script>
  window.PROLICENCIAS_BASE = @json(rtrim(route('prolicencias.store'), '/'));
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@vite('resources/js/prolicencias/index.js')
