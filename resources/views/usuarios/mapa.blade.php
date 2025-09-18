@extends('layouts.app')

<style>
    #map_card:fullscreen,
    #map_card:-webkit-full-screen,
    #map_card:-moz-full-screen,
    #map_card:-ms-fullscreen,
    #map_card.in-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        max-width: none !important;
        max-height: none !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        border-radius: 0 !important;
        background: #0f172a !important;
        display: flex !important;
        flex-direction: column !important;
        z-index: 999999 !important;
        overflow: hidden !important;
    }

    #map_card:fullscreen>.p-4,
    #map_card:-webkit-full-screen>.p-4,
    #map_card.in-fullscreen>.p-4,
    #map_card:fullscreen>.px-4,
    #map_card:-webkit-full-screen>.px-4,
    #map_card.in-fullscreen>.px-4 {
        display: none !important;
    }

    #map_card:fullscreen>.relative,
    #map_card:-webkit-full-screen>.relative,
    #map_card.in-fullscreen>.relative {
        position: relative !important;
        flex: 1 1 auto !important;
        width: 100% !important;
        height: 100% !important;
        min-height: 100% !important;
    }

    #map_card:fullscreen #map,
    #map_card:-webkit-full-screen #map,
    #map_card:-moz-full-screen #map,
    #map_card:-ms-fullscreen #map,
    #map_card.in-fullscreen #map {
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        min-height: 100% !important;
        border-radius: 0 !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    #map {
        height: 520px;
    }

    #map_card:fullscreen #map,
    #map_card.in-fullscreen #map {
        height: 100% !important;
    }

    body:has(#map_card:fullscreen),
    body:has(#map_card:-webkit-full-screen),
    body:has(#map_card:-moz-full-screen),
    body:has(#map_card:-ms-fullscreen),
    body:has(#map_card.in-fullscreen) {
        overflow: hidden !important;
    }

    body.fullscreen-active {
        overflow: hidden !important;
    }

    #mobile-sidebar {
        z-index: 10;
    }

    #map_card:fullscreen #mobile-sidebar,
    #map_card:-webkit-full-screen #mobile-sidebar,
    #map_card:-moz-full-screen #mobile-sidebar,
    #map_card:-ms-fullscreen #mobile-sidebar,
    #map_card.in-fullscreen #mobile-sidebar {
        display: none !important;
    }
</style>
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

            <!-- Formulario de Datos del Cliente y Visita -->
            <div class="lg:col-span-1">
                <form id="FormRegistroUbicaciones">
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow p-5">
                        <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100 mb-4">
                            Datos del Cliente
                        </h2>

                           <input id="ubi_id" name="ubi_id" type="number">
        
                        <div class="space-y-1 mb-4">
                            <label for="cliente_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Cliente
                            </label>
                            <select id="cliente_id" name="cliente_id"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="">Seleccione...</option>

                                @foreach ($usuarios as $opciones)
                                    <option value="{{ $opciones->id }}">
                                        {{ $opciones->name }} | {{ $opciones->user_empresa ?? 'Sin Empresa' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1 mb-4">
                            <label for="direccion" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Dirección / referencia
                            </label>
                            <textarea id="direccion" name="direccion" rows="3" placeholder="Ej. 6a ave 1-23 zona 1, frente al parque…"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <!-- Estado de la Visita -->
                        <div class="space-y-1 mb-4">
                            <label for="visitado" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Estado de la Visita
                            </label>
                            <select id="visitado" name="visitado"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                                <option value="">Seleccione...</option>
                                <option value="1">Visitado, No Comprado</option>
                                <option value="2">Visitado, Comprado</option>
                                <option value="3">No Visitado</option>
                            </select>
                        </div>

                        <div class="space-y-1 mb-4">
                            <label for="fecha_visita" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Fecha de la Visita
                            </label>
                            <input id="fecha_visita" name="fecha_visita" type="date"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="space-y-1 mb-4">
                            <label for="busqueda_lugar"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300">
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
                                <label for="lat"
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300">Latitud</label>
                                <input id="lat" name="lat" type="text" placeholder="14.6…"
                                    class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="lng"
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300">Longitud</label>
                                <input id="lng" name="lng" type="text" placeholder="-90.5…"
                                    class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div id="venta_info" class="space-y-1 mb-4 hidden">
                            <label for="cantidad_vendida"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Cantidad Vendida
                            </label>
                            <input id="cantidad_vendida" name="cantidad_vendida" type="number"
                                placeholder="Cantidad vendida"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-indigo-500">

                            <label for="descripcion_venta"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Descripción de la Venta
                            </label>
                            <textarea id="descripcion_venta" name="descripcion_venta" rows="3" placeholder="Detalles de la venta"
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
                            <button id="btn_guardar" type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Mapa -->
            <div class="lg:col-span-2">
                <div id="map_card"
                    class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow overflow-hidden relative">
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

                    <!-- Contenedor del mapa con posición relativa para el botón flotante -->
                    <div class="relative">
                        <div class="h-[520px] w-full" id="map"></div>

                        <!-- BOTÓN FLOTANTE DENTRO DEL CONTENEDOR DEL MAPA -->
                        <button id="btn_fullscreen" type="button"
                            class="absolute top-3 right-3 z-[1000] inline-flex items-center justify-center rounded-lg border border-slate-300 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm px-2.5 py-2 shadow-lg hover:bg-white dark:hover:bg-slate-800 hover:shadow-xl transition-all duration-200"
                            title="Pantalla completa">
                            <!-- icono expandir -->
                            <svg id="icon_expand" xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 block text-slate-700 dark:text-slate-200" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" />
                            </svg>
                            <!-- icono contraer (oculto hasta fullscreen) -->
                            <svg id="icon_compress" xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 hidden text-slate-700 dark:text-slate-200" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Barra inferior de estado -->
                    <div
                        class="px-4 py-2 text-xs text-slate-500 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <div id="status_mapa">Haz clic en el mapa para seleccionar coordenadas.</div>
                        <div><span id="zoom_level">Zoom: —</span></div>
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
