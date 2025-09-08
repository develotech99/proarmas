@extends('layouts.app')

@section('title', 'Gestión de Licencias de Importación')

@section('content')

<!-- Meta tag para CSRF token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script id="licencias-data" type="application/json">
@json($licencias->items())
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Table -->
    <div class="overflow-hidden rounded-lg shadow sm:rounded-lg">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Situación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($licencias as $licencia)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $licencia->lipaimp_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $licencia->lipaimp_descripcion }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $licencia->empresa->empresaimp_descripcion }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $licencia->lipaimp_situacion == 1 ? 'Pendiente' : ($licencia->lipaimp_situacion == 2 ? 'Autorizado' : 'Rechazado') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                            <button @click="openEditModal({{ $licencia->lipaimp_id }})" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                Editar
                            </button>
                            <form action="{{ route('prolicencias.destroy', $licencia->lipaimp_id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $licencias->links() }}
    </div>

    <!-- Modal para Crear/Editar Licencia -->
    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                <form @submit="handleFormSubmit($event)">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="isEditing ? 'Editar Licencia' : 'Crear Nueva Licencia'"></h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <!-- Descripción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                            <input type="text" x-model="formData.lipaimp_descripcion" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Empresa -->
                        <select x-model="formData.lipaimp_empresa" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                            <option value="">Seleccionar Empresa</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->empresaimp_id }}">{{ $empresa->empresaimp_descripcion }}</option>
                            @endforeach
                        </select>

                        <!-- Fecha de vencimiento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Vencimiento</label>
                            <input type="date" x-model="formData.lipaimp_fecha_vencimiento" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Situación -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Situación</label>
                            <select x-model="formData.lipaimp_situacion" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="1">Pendiente</option>
                                <option value="2">Autorizado</option>
                                <option value="3">Rechazado</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed">
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let licencias = @json($licencias);
        let showModal = false;
        let isEditing = false;
        let formData = { lipaimp_descripcion: '', lipaimp_empresa: '', lipaimp_fecha_vencimiento: '', lipaimp_situacion: 1 };

        function openCreateModal() {
            isEditing = false;
            formData = { lipaimp_descripcion: '', lipaimp_empresa: '', lipaimp_fecha_vencimiento: '', lipaimp_situacion: 1 };
            showModal = true;
        }

        function openEditModal(id) {
            let licencia = licencias.find(l => l.lipaimp_id == id);
            formData = { lipaimp_descripcion: licencia.lipaimp_descripcion, lipaimp_empresa: licencia.lipaimp_empresa, lipaimp_fecha_vencimiento: licencia.lipaimp_fecha_vencimiento, lipaimp_situacion: licencia.lipaimp_situacion };
            isEditing = true;
            showModal = true;
        }

        function closeModal() {
            showModal = false;
        }

        function handleFormSubmit(event) {
            event.preventDefault();

            // Aquí implementas la lógica para enviar el formulario (puede ser un fetch o form.submit)
            console.log(formData);
            closeModal();
        }

        window.licenciasManager = () => ({
            showModal,
            openCreateModal,
            openEditModal,
            closeModal,
            handleFormSubmit,
            formData,
            isEditing
        });
    });
</script>

@endsection
