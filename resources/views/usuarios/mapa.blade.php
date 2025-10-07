@extends('layouts.app')

{{-- Estilos fullscreen (con tu lógica original) --}}
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


    /* estilos de datatable */
    .dataTable-wrapper table button.bg-indigo-600 {
        background-color: #4f46e5 !important;
        color: #fff !important;
    }

    .dataTable-wrapper table button.bg-emerald-600 {
        background-color: #059669 !important;
        color: #fff !important;
    }

    .dataTable-wrapper table button.bg-rose-600 {
        background-color: #dc2626 !important;
        color: #fff !important;
    }

    .dataTable-wrapper table button:hover.bg-indigo-600 {
        background-color: #6366f1 !important;
    }

    .dataTable-wrapper table button:hover.bg-emerald-600 {
        background-color: #10b981 !important;
    }

    .dataTable-wrapper table button:hover.bg-rose-600 {
        background-color: #e11d48 !important;
    }

    .dataTable-wrapper table button:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, .25);
    }

    .swal-nueva-visita {
        z-index: 10002 !important;
    }

    .swal2-container {
        z-index: 10002 !important;
    }
</style>

@section('title', 'Ubicar cliente en el mapa')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header --}}
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1
                    class="text-3xl font-bold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 dark:from-slate-100 dark:to-slate-300 bg-clip-text text-transparent">
                    Ubicación de Clientes
                </h1>
                <p class="mt-2 text-slate-600 dark:text-slate-400">
                    Selecciona un cliente, ajusta su ubicación y guarda los cambios.
                </p>
            </div>
            <div class="flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                <span class="inline-flex items-center"><span
                        class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>Visitado/Comprado</span>
                <span class="inline-flex items-center"><span
                        class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>Visitado/No comprado</span>
                <span class="inline-flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>No
                    visitado</span>
            </div>
        </div>
    </div>

    <div class="grid gap-8 grid-cols-1 lg:grid-cols-3">

        {{-- Formulario --}}
        <div class="min-w-0">
            <form id="FormRegistroUbicaciones"
                class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg p-6">
                <input id="ubi_id" name="ubi_id" type="hidden">

                <div class="mb-5">
                    <label for="cliente_id"
                        class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Cliente</label>
                    <select id="cliente_id" name="cliente_id"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                        <option value="">Seleccione un cliente...</option>
                        @foreach ($usuarios as $op)
                        <option value="{{ $op->user_id }}">{{ $op->name }} |
                            {{ $op->user_empresa ?? 'Sin Empresa' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Dirección --}}
                <div class="mb-5">
                    <label for="direccion"
                        class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección /
                        referencia</label>
                    <textarea id="direccion" name="direccion" rows="3" placeholder="Ej. 6a ave 1-23 zona 1, frente al parque…"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 resize-none"></textarea>
                </div>

                {{-- Estado visita --}}
                <div class="mb-5">
                    <label for="visitado"
                        class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado de la
                        Visita</label>
                    <select id="visitado" name="visitado"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                        <option value="">Seleccione el estado...</option>
                        <option value="1">🟡 Visitado, No Comprado</option>
                        <option value="2">🟢 Visitado, Comprado</option>
                        <option value="3">🔴 No Visitado</option>
                    </select>
                </div>

                {{-- FECHA visita (tu JS la oculta si "No visitado") --}}
                <div id="grupo_fecha_visita" class="mb-5">
                    <label for="fecha_visita"
                        class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de la
                        Visita</label>
                    <input id="fecha_visita" name="fecha_visita" type="date"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                </div>


                <div class="mb-5">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <!-- Latitud -->
                        <div class="sm:col-span-5">
                            <label for="lat"
                                class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Latitud</label>
                            <input id="lat" name="lat" type="text" placeholder="14.6…"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                        </div>

                        <!-- Longitud -->
                        <div class="sm:col-span-5">
                            <label for="lng"
                                class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Longitud</label>
                            <input id="lng" name="lng" type="text" placeholder="-90.5…"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                        </div>

                        <!-- Botón pequeño -->
                        <div class="sm:col-span-2 flex justify-center">
                            <button id="btn_plotear" type="button"
                                class="h-8 w-8 flex items-center justify-center rounded-md bg-blue-600 text-white hover:bg-blue-500 transition"
                                title="Buscar coordenadas">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0
                                           1110.89 3.476l4.817 4.817a1 1 0
                                           01-1.414 1.414l-4.816-4.816A6 6
                                           0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>


                {{-- Info venta (tu JS la muestra si visitado=2) --}}
                <div id="venta_info"
                    class="hidden mb-5 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20">
                    <h3 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300 mb-3">Información de Venta
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="cantidad_vendida"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Cantidad
                                Vendida</label>
                            <input id="cantidad_vendida" name="cantidad_vendida" type="number" step="0.01"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="descripcion_venta"
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Descripción de
                                la Venta</label>
                            <textarea id="descripcion_venta" name="descripcion_venta" rows="3"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 resize-none"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Botones de formulario --}}
                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3">
                    <button id="btn_tomar_posicion" type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border-2 border-slate-300 dark:border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        {{-- crosshair --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6h6m6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="hidden sm:inline">Tomar posición</span>
                        <span class="sm:hidden">Posición</span>
                    </button>

                    <button id="btn_guardar" type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M17 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2zm-5 14a3 3 0 110-6 3 3 0 010 6z" />
                        </svg>
                        <span id="btn_guardar_texto">Guardar</span>
                    </button>

                </div>
            </form>
        </div>

        {{-- Mapa --}}
        <div class="lg:col-span-2 min-w-0">
            <div id="map_card"
                class="rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg overflow-hidden">

                {{-- Barra superior del mapa: UNA SOLA LÍNEA (responsiva) --}}
                <div class="p-4">
                    <div class="flex flex-col md:flex-row md:items-center md:gap-3 gap-2 w-full">

                        {{-- Título compacto --}}
                        <div class="flex items-center gap-2 shrink-0">
                            <div
                                class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Mapa Interactivo</h2>
                        </div>

                        {{-- Buscador (ocupa todo el ancho disponible) --}}
                        <div class="relative flex-1 min-w-[220px]">
                            <input id="busqueda_lugar" type="text"
                                placeholder="Ej. Zona 1, Catedral…  o  14.6349, -90.5069"
                                class="w-full pl-4 pr-12 rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 h-11">
                            <button id="btn_buscar_lugar" type="button"
                                class="absolute inset-y-0 right-0 px-4 text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                                title="Buscar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35M16 10.5a5.5 5.5 0 11-11 0 5.5 5.5 0 0111 0z" />
                                </svg>
                            </button>
                        </div>

                        {{-- Acciones a la derecha (mantienen 1 línea en md+) --}}
                        <div class="flex items-stretch gap-2 shrink-0">
                            <button id="btn_mi_ubicacion" type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 h-11 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M12 2a9.99 9.99 0 00-7.07 2.93A9.99 9.99 0 002 12c0 5.52 4.48 10 10 10s10-4.48 10-10-4.48-10-10-10zm0 5a2 2 0 110 4 2 2 0 010-4zm0 11a7.97 7.97 0 01-6-2.7c.03-1.98 4-3.07 6-3.07s5.97 1.09 6 3.07a7.97 7.97 0 01-6 2.7z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="hidden sm:inline">Mi ubicación</span>
                                <span class="sm:hidden">Ubicación</span>
                            </button>
                            <button id="btn_limpiar_mapa" type="button"
                                class="inline-flex items-center gap-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 h-11 text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a1 1 0 011 1v1h5m-6 0V4a1 1 0 00-1 1v1m0 0H4" />
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Contenedor mapa --}}
                <div class="relative">
                    <div id="map" class="w-full h-[56vh] sm:h-[60vh] lg:h-[66vh]"></div>

                    {{-- Botón fullscreen --}}
                    <button id="btn_fullscreen" type="button"
                        class="absolute top-4 right-4 z-[1000] inline-flex items-center justify-center rounded-xl border border-slate-300 dark:border-slate-600 bg-white/95 dark:bg-slate-900/95 backdrop-blur px-3 py-3 shadow-lg hover:shadow-xl transition"
                        title="Pantalla completa">
                        <svg id="icon_expand" class="h-5 w-5 block text-slate-700 dark:text-slate-200"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z" />
                        </svg>
                        <svg id="icon_compress" class="h-5 w-5 hidden text-slate-700 dark:text-slate-200"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z" />
                        </svg>
                    </button>
                </div>

                {{-- Barra inferior estado --}}
                <div
                    class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div id="status_mapa" class="flex items-center gap-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Haz clic en el mapa para seleccionar coordenadas.
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <span id="zoom_level">Zoom: —</span>
                        <span id="coords_display">Coordenadas: —</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="mt-10">
        <div class="rounded-3x border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg">
            <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Clientes Georreferenciados
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Gestiona las ubicaciones guardadas</p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                    <div class="relative flex-1">
                        <input id="filtro_cliente" type="text" placeholder="Filtrar por cliente…"
                            class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 pl-10 pr-3 py-2 text-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M16 10.5a5.5 5.5 0 11-11 0 5.5 5.5 0 0111 0z" />
                        </svg>
                    </div>
                    <button id="btn_exportar" type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3M5 20h14a2 2 0 002-2V7a2 2 0 00-2-2h-5.586a1 1 0 00-.707.293L9.293 9.707A1 1 0 009 10.414V18a2 2 0 002 2z" />
                        </svg>
                        Exportar
                    </button>
                    <button id="btn_ver_modal" type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1.5 12s3.75-7.5 10.5-7.5S22.5 12 22.5 12 18.75 19.5 12 19.5 1.5 12 1.5 12z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        Ver Detalles
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-700 dark:text-slate-200">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-900 dark:text-slate-100">
                        <tr>
                            <th class="text-left px-6 py-4 font-semibold">Cliente</th>
                            <th class="text-left px-6 py-4 font-semibold hidden md:table-cell">Empresa</th>
                            <th class="text-left px-6 py-4 font-semibold">Estado</th>
                            <th class="text-left px-6 py-4 font-semibold hidden lg:table-cell">Dirección</th>
                            <th class="text-left px-6 py-4 font-semibold hidden xl:table-cell">Coordenadas</th>
                            <th class="text-left px-6 py-4 font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla_clientes" class="divide-y divide-slate-100 dark:divide-slate-800">
                        {{-- filas dinámicas por JS --}}
                    </tbody>
                </table>
            </div>

            <div id="no-data-message" class="hidden p-12 text-center text-slate-500 dark:text-slate-400">
                No hay clientes georreferenciados
            </div>
        </div>
    </div>
</div>


<!-- Modal Detalles Cliente -->
<div id="cliente_modal" class="fixed inset-0 z-[10000] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="relative mx-auto mt-12 w-full max-w-4xl">
        <div class="rounded-2xl bg-white dark:bg-slate-900 shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 id="cm_nombre" class="text-lg font-semibold">Cliente</h3>
                    <p id="cm_sub" class="text-xs text-slate-500">Resumen y actividad</p>
                </div>
                <button id="cm_close" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800">✕</button>
            </div>

            <div class="p-6 grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="rounded-xl border p-4">
                    <div class="text-xs">Visitas</div>
                    <div id="cm_visitas" class="text-2xl font-bold">—</div>
                </div>
                <div class="rounded-xl border p-4">
                    <div class="text-xs">Compras</div>
                    <div id="cm_compras" class="text-2xl font-bold text-emerald-600">—</div>
                </div>
                <div class="rounded-xl border p-4">
                    <div class="text-xs">No compró</div>
                    <div id="cm_nocompra" class="text-2xl font-bold text-amber-600">—</div>
                </div>
                <div class="rounded-xl border p-4">
                    <div class="text-xs">No visitado</div>
                    <div id="cm_novisitado" class="text-2xl font-bold text-rose-600">—</div>
                </div>
                <div class="rounded-xl border p-4">
                    <div class="text-xs">Total vendido</div>
                    <div id="cm_total" class="text-2xl font-bold">—</div>
                </div>
            </div>

            <div class="px-6">
                <div class="flex gap-3 text-sm">
                    <button id="tab_visitas"
                        class="px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800">Ultima Visita</button>
                    <button id="tab_hist" class="px-3 py-2 rounded-lg">Historial</button>
                </div>
            </div>

            <div class="p-6">
                <div id="cm_loading" class="text-sm text-slate-500">Cargando…</div>

                <div id="cm_visitas_pane" class="hidden overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="text-left px-4 py-2">Fecha</th>
                                <th class="text-left px-4 py-2">Estado</th>
                                <th class="text-left px-4 py-2">Venta</th>
                                <th class="text-left px-4 py-2">Descripción</th>
                            </tr>
                        </thead>
                        <tbody id="cm_visitas_tb" class="divide-y"></tbody>
                    </table>
                </div>

                <div id="cm_hist_pane" class="hidden overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th class="text-left px-4 py-2">Fecha actualización</th>
                                <th class="text-left px-4 py-2">Estado (antes → nuevo)</th>
                                <th class="text-left px-4 py-2">Venta (antes → nueva)</th>
                                <th class="text-left px-4 py-2">Descripción</th>
                            </tr>
                        </thead>
                        <tbody id="cm_hist_tb" class="divide-y"></tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <button id="cm_add_visita" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm">Registrar
                    visita</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para registrar nueva visita -->
<div id="nueva_visita_modal" class="fixed inset-0 z-[10001] hidden">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <div class="relative mx-auto mt-20 w-full max-w-lg">
        <div class="rounded-2xl bg-white dark:bg-slate-900 shadow-2xl p-6">
            <h3 class="text-lg font-semibold mb-4">Registrar Nueva Visita</h3>

            <form id="form_nueva_visita">
                <input type="hidden" id="nv_user_id" name="user_id">

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Estado</label>
                    <select id="nv_estado" name="estado" class="w-full rounded-xl border-slate-300">
                        <option value="1">🟡 Visitado, No Comprado</option>
                        <option value="2">🟢 Visitado, Comprado</option>
                        <option value="3">🔴 No Visitado</option>
                    </select>
                </div>

                <div id="nv_grupo_fecha" class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Fecha</label>
                    <input type="date" id="nv_fecha" name="fecha" class="w-full rounded-xl border-slate-300">
                </div>

                <div id="nv_grupo_venta" class="hidden mb-4">
                    <label class="block text-sm font-semibold mb-1">Cantidad Vendida</label>
                    <input type="number" step="0.01" id="nv_venta" name="venta" class="w-full rounded-xl border-slate-300">

                    <label class="block text-sm font-semibold mb-1 mt-3">Descripción</label>
                    <textarea id="nv_descripcion" name="descripcion" rows="2" class="w-full rounded-xl border-slate-300"></textarea>
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" id="nv_cancelar" class="px-4 py-2 rounded-lg border">Cancelar</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-emerald-600 text-white">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>


@endsection

@vite(['resources/js/usuarios/mapa.js'])