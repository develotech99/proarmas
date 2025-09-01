@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

@section('content')
<!-- Meta tag para CSRF token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Pasar datos a JavaScript -->
<script id="usuarios-data" type="application/json">
@json($usuarios->items())
</script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="usuariosManager()">
    
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                Gestión de Usuarios
            </h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <button @click="openCreateModal()" 
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Usuario
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
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Usuarios</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $usuarios->total() }}</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Con Rol Asignado</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $usuarios->where('rol_id', '!=', null)->count() }}</dd>
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
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Registrados Hoy</dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $usuarios->where('created_at', '>=', today())->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Buscar por nombre</label>
                    <input type="text" 
                           x-model="searchTerm" 
                           @input="filterUsers()"
                           placeholder="Escribir nombre..."
                           class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por rol</label>
                    <select x-model="roleFilter" 
                            @change="filterUsers()"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        <option value="">Todos los roles</option>
                        <option value="ADMIN">Admin</option>
                        <option value="GERENTE">Gerente</option>
                        <option value="VENTAS">Ventas</option>
                        <option value="CONTABILIDAD">Contabilidad</option>
                        <option value="CLIENTE">Contabilidad</option>
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

    <!-- Users Table -->
    <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Usuario
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Email
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Rol
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Registrado
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($usuarios as $usuario)
                                <tr x-show="showUser({{ $usuario->id }})" x-transition>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-yellow-500 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-white">{{ strtoupper(substr($usuario->name, 0, 2)) }}</span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $usuario->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ $usuario->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($usuario->rol)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                @if($usuario->rol->nombre === 'admin') bg-red-100 text-red-800
                                                @elseif($usuario->rol->nombre === 'chef') bg-blue-100 text-blue-800
                                                @elseif($usuario->rol->nombre === 'gerente') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800 @endif"
                                                data-role="{{ $usuario->rol->nombre }}">
                                                {{ ucfirst($usuario->rol->nombre) }}
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800" data-role="sin-rol">
                                                Sin rol
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $usuario->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button @click="viewUser({{ $usuario->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <button @click="editUser({{ $usuario->id }})" 
                                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <button @click="deleteUser({{ $usuario->id }})" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                            </svg>
                                            <p class="mt-2">No hay usuarios registrados.</p>
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
    @if($usuarios->hasPages())
        <div class="mt-6">
            {{ $usuarios->links() }}
        </div>
    @endif

    <!-- Modal para Crear/Editar Usuario -->
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
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                <form @submit="handleFormSubmit($event)">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="isEditing ? 'Editar Usuario' : 'Crear Nuevo Usuario'"></h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <!-- Nombre -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre completo</label>
                            <input type="text" 
                                   x-model="formData.name"
                                   required
                                   @input="validateForm()"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo electrónico</label>
                            <input type="email" 
                                   x-model="formData.email"
                                   required
                                   @input="validateForm()"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Rol -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Rol</label>
                            <select x-model="formData.rol_id"
                                    @change="validateForm()"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                                <option value="">Seleccionar rol</option>
                                @if(isset($roles))
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }} - {{ $rol->descripcion }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Contraseña (solo para crear) -->
                        <div x-show="!isEditing">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                            <input type="password" 
                                   x-model="formData.password"
                                   :required="!isEditing"
                                   @input="validateForm()"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Confirmar Contraseña (solo para crear) -->
                        <div x-show="!isEditing">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar contraseña</label>
                            <input type="password" 
                                   x-model="formData.password_confirmation"
                                   @input="validateForm()"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
                        </div>

                        <!-- Campos de contraseña para edición (opcionales) -->
                        <div x-show="isEditing">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nueva contraseña (opcional)</label>
                            <input type="password" 
                                   x-model="formData.password"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm"
                                   placeholder="Dejar vacío para mantener la actual">
                        </div>

                        <div x-show="isEditing">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirmar nueva contraseña</label>
                            <input type="password" 
                                   x-model="formData.password_confirmation"
                                   class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm">
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

    <!-- Modal para Ver Usuario -->
    <div x-show="showViewModal" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeViewModal()"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detalles del Usuario</h3>
                </div>

                <div x-show="viewingUser" class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 rounded-full bg-yellow-500 flex items-center justify-center">
                            <span class="text-xl font-medium text-white" x-text="viewingUser ? viewingUser.name.substring(0, 2).toUpperCase() : ''"></span>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="viewingUser ? viewingUser.name : ''"></h4>
                            <p class="text-gray-600 dark:text-gray-400" x-text="viewingUser ? viewingUser.email : ''"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Rol</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="viewingUser && viewingUser.rol ? viewingUser.rol.nombre.charAt(0).toUpperCase() + viewingUser.rol.nombre.slice(1) : 'Sin rol'"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de registro</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100" x-text="viewingUser ? new Date(viewingUser.created_at).toLocaleDateString('es-ES') : ''"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            @click="closeViewModal()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.usuariosManager = () => ({
    // Estados
    showModal: false,
    showViewModal: false,
    showDebug: true,
    isEditing: false,
    editingUserId: null,
    viewingUser: null,
    searchTerm: '',
    roleFilter: '',
    isSubmitting: false,
    
    // Form data
    formData: {
        name: '',
        email: '',
        rol_id: '',
        password: '',
        password_confirmation: ''
    },
    
    // Los datos se pasarán desde la vista
    usuarios: [],

    init() {
        console.log('🚀 usuariosManager inicializado');
        this.loadUsuarios();
        console.log('📊 Total usuarios cargados:', this.usuarios.length);
    },

    loadUsuarios() {
        try {
            const usuariosData = document.getElementById('usuarios-data');
            if (usuariosData) {
                this.usuarios = JSON.parse(usuariosData.textContent);
                console.log('📊 Usuarios cargados desde script:', this.usuarios.length);
            }
        } catch (error) {
            console.error('Error cargando usuarios:', error);
            this.usuarios = [];
        }
    },

    getFormAction() {
        const baseUrl = window.location.origin;
        const action = this.isEditing 
            ? `${baseUrl}/usuarios/${this.editingUserId}` 
            : `${baseUrl}/usuarios`;
        console.log('🎯 Form action calculado:', action);
        return action;
    },

    isFormValid() {
        const nameValid = this.formData.name.trim().length > 0;
        const emailValid = this.formData.email.trim().length > 0 && this.formData.email.includes('@');
        
        let passwordValid = true;
        if (!this.isEditing) {
            passwordValid = this.formData.password.length >= 8 && 
                           this.formData.password === this.formData.password_confirmation;
        }

        const isValid = nameValid && emailValid && passwordValid;
        console.log('✅ Validación form:', { nameValid, emailValid, passwordValid, isValid });
        return isValid;
    },

    validateForm() {
        this.isFormValid();
    },

    openCreateModal() {
        console.log('➕ Abriendo modal para crear usuario');
        this.isEditing = false;
        this.editingUserId = null;
        this.resetFormData();
        this.showModal = true;
    },

    editUser(userId) {
        console.log('✏️ Editando usuario con ID:', userId);
        const user = this.usuarios.find(u => u.id === userId);
        if (user) {
            this.isEditing = true;
            this.editingUserId = userId;
            this.formData = {
                name: user.name,
                email: user.email,
                rol_id: user.rol_id || '',
                password: '',
                password_confirmation: ''
            };
            this.showModal = true;
        } else {
            console.error('❌ Usuario no encontrado:', userId);
            this.showSweetAlert('error', 'Error', 'Usuario no encontrado');
        }
    },

    async handleFormSubmit(event) {
        event.preventDefault();
        console.log('📤 Enviando formulario...');
        
        this.isSubmitting = true;
        
        if (!this.isFormValid()) {
            console.error('❌ Formulario inválido');
            this.showSweetAlert('error', 'Error de validación', 'Por favor complete todos los campos correctamente');
            this.isSubmitting = false;
            return false;
        }

        try {
            const formData = new FormData();
            formData.append('name', this.formData.name);
            formData.append('email', this.formData.email);
            formData.append('rol_id', this.formData.rol_id);
            
            if (!this.isEditing || this.formData.password) {
                formData.append('password', this.formData.password);
                formData.append('password_confirmation', this.formData.password_confirmation);
            }
            
            // Agregar CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('_token', csrfToken);
            }
            
            if (this.isEditing) {
                formData.append('_method', 'PUT');
            }

            const response = await fetch(this.getFormAction(), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (response.ok) {
                this.showSweetAlert('success', 'Éxito', this.isEditing ? 'Usuario actualizado correctamente' : 'Usuario creado correctamente');
                this.closeModal();
                // Recargar la página o actualizar la lista
                setTimeout(() => window.location.reload(), 1500);
            } else {
                const errorData = await response.json();
                this.showSweetAlert('error', 'Error', errorData.message || 'Error al procesar la solicitud');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showSweetAlert('error', 'Error', 'Error de conexión');
        } finally {
            this.isSubmitting = false;
        }
    },

    closeModal() {
        console.log('🔒 Cerrando modal');
        this.showModal = false;
        this.isSubmitting = false;
        this.resetFormData();
    },

    resetFormData() {
        this.formData = {
            name: '',
            email: '',
            rol_id: '',
            password: '',
            password_confirmation: ''
        };
    },

    showSweetAlert(type, title, text) {
        const config = {
            title: title,
            text: text,
            icon: type,
            customClass: {
                popup: 'dark:bg-gray-800 dark:text-gray-100',
                title: 'dark:text-gray-100',
                content: 'dark:text-gray-300'
            }
        };

        if (type === 'success') {
            config.confirmButtonColor = '#10b981';
            config.timer = 3000;
        } else if (type === 'error') {
            config.confirmButtonColor = '#dc2626';
        }

        Swal.fire(config);
    },

    viewUser(userId) {
        const user = this.usuarios.find(u => u.id === userId);
        if (user) {
            this.viewingUser = user;
            this.showViewModal = true;
        }
    },

    closeViewModal() {
        this.showViewModal = false;
        this.viewingUser = null;
    },

    deleteUser(userId) {
        const user = this.usuarios.find(u => u.id === userId);
        if (!user) return;

        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar al usuario "${user.name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submitDeleteForm(userId);
            }
        });
    },

    submitDeleteForm(userId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/usuarios/${userId}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    },

    showUser(userId) {
        const user = this.usuarios.find(u => u.id === userId);
        if (!user) return false;

        if (this.searchTerm && !user.name.toLowerCase().includes(this.searchTerm.toLowerCase())) {
            return false;
        }

        if (this.roleFilter) {
            if (this.roleFilter === 'sin-rol' && user.rol) return false;
            if (this.roleFilter !== 'sin-rol' && (!user.rol || user.rol.nombre !== this.roleFilter)) return false;
        }

        return true;
    },

    filterUsers() {
        // El filtrado se hace en showUser()
    },

    clearFilters() {
        this.searchTerm = '';
        this.roleFilter = '';
    }
});
</script>

@endsection