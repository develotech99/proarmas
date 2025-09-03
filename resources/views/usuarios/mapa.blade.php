@extends('layouts.app')

@section('title', 'Ubicar cliente en el mapa')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">
            Ubicación de Clientes
        </h1>
        <p class="mt-1 text-slate-600 dark:text-slate-400">
            Selecciona un cliente, ajusta su ubicación y guarda los cambios.
        </p>
    </div>


    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-1">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow p-5">
                <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">
                    Datos del cliente
                </h2>

                <div class="space-y-1 mb-4">
                    <label for="cliente_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Cliente
                    </label>
                    <select id="cliente_id" name="cliente_id"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccione…</option>

                        @foreach ($usuarios as $opciones )
                        <option value="{{ $opciones->id }}">
                            {{ $opciones->name }}
                        </option>
                        @endforeach

                    </select>
                </div>

                <div class="space-y-1 mb-4">
                    <label for="tipo_cliente" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Marca mas comprada
                    </label>
                    <select id="tipo_cliente" name="tipo_cliente"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccione…</option>
                        <option value="red">CZ</option>
                        <option value="blue">GLOCK</option>
                        <option value="orange">JERICHO</option>
                    </select>

                    <p id="cliente_estado" class="text-xs text-slate-500">Seleccione la marca mas comprada por el Cliente</p>

                </div>

                <div class="space-y-1 mb-4">
                    <label for="busqueda_lugar" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Buscar lugar
                    </label>
                    <div class="relative">
                        <input id="busqueda_lugar" type="text" placeholder="Ej. Zona 1, Catedral, Tikal…"
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 pr-10 focus:ring-2 focus:ring-indigo-500">
                        <button id="btn_buscar_lugar" type="button"
                            class="absolute inset-y-0 right-0 px-3 text-slate-500 hover:text-slate-700 dark:hover:text-slate-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <div>
                        <label for="lat" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Latitud</label>
                        <input id="lat" name="lat" type="text" placeholder="14.6…"
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="lng" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Longitud</label>
                        <input id="lng" name="lng" type="text" placeholder="-90.5…"
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="space-y-1 mb-5">
                    <label for="direccion" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                        Dirección / referencia
                    </label>
                    <textarea id="direccion" name="direccion" rows="3" placeholder="Ej. 6a ave 1-23 zona 1, frente al parque…"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button id="btn_tomar_posicion" type="button"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-700 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                        Tomar posición del mapa
                    </button>
                    <button id="btn_plotear" type="button"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">
                        Buscar Coordenadas
                    </button>
                    <button id="btn_guardar" type="button"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        Guardar
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow overflow-hidden">
                <div class="p-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Mapa</h2>
                    <div class="flex items-center gap-2">
                        <button id="btn_mi_ubicacion" type="button"
                            class="rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-1.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                            Mi ubicación
                        </button>
                        <button id="btn_limpiar_mapa" type="button"
                            class="rounded-lg border border-slate-300 dark:border-slate-700 px-3 py-1.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                            Limpiar
                        </button>
                    </div>
                </div>
                <div class="h-[520px] w-full" id="map"></div>
                <!-- Barra inferior de estado -->
                <div class="px-4 py-2 text-xs text-slate-500 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <div id="status_mapa">Haz clic en el mapa para seleccionar coordenadas.</div>
                    <div>
                        <span id="zoom_level">Zoom: —</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla/Lista de puntos guardados -->
    <div class="mt-8">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow">
            <div class="p-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Clientes georreferenciados</h3>
                <div class="flex items-center gap-2">
                    <input id="filtro_cliente" type="text" placeholder="Filtrar…"
                        class="rounded-lg border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500">
                    <button id="btn_exportar" type="button"
                        class="rounded-lg bg-slate-100 dark:bg-slate-800 px-3 py-1.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700">
                        Exportar
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-700 dark:text-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100">
                        <tr>
                            <th class="text-left px-4 py-2">Cliente</th>
                            <th class="text-left px-4 py-2">Dirección / referencia</th>
                            <th class="text-left px-4 py-2">Lat</th>
                            <th class="text-left px-4 py-2">Lng</th>
                            <th class="text-left px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_clientes" class="divide-y divide-slate-100 dark:divide-slate-800">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@vite('resources/js/usuarios/mapa.js')