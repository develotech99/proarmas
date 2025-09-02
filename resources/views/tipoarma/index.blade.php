@extends('layouts.app')

@section('title', 'Gestión de Tipos de armas')

@section('content')
  <div class="mx-auto max-w-screen-2xl px-6 py-8">

    {{-- Flash messages --}}
    @if (session('success'))
      <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 shadow-sm">
        {{ session('success') }}
      </div>
    @endif

    {{-- Errores de validación --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-sm">
        <ul class="list-inside list-disc space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- Header + Filtros -->
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Gestión de Tipos de Arma</h1>
        <p class="text-sm text-gray-500">Cree y edite tipos de arma. Use los filtros para buscar rápidamente.</p>
      </div>

      <button id="btnNueva"
              class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 active:scale-[.99] transition">
        <span class="text-lg leading-none">＋</span> Nuevo Tipo
      </button>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
      <form method="GET" action="{{ route('tipoarma.search') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
        <div class="md:col-span-6">
          <label class="mb-1 block text-xs font-medium text-gray-600">Buscar por descripción</label>
          <input type="text" name="descripcion" value="{{ request('descripcion') }}"
                 placeholder="Ej. Pistola, Fusil, Escopeta…"
                 class="w-full rounded-xl border border-gray-300 px-3 py-2.5 outline-none ring-blue-400 transition focus:ring-2">
        </div>

        <div class="md:col-span-3">
          <label class="mb-1 block text-xs font-medium text-gray-600">Situación</label>
          <select name="situacion"
                  class="w-full rounded-xl border border-gray-300 px-3 py-2.5 outline-none ring-blue-400 transition focus:ring-2">
            <option value="">-- Situación --</option>
            <option value="1" {{ request('situacion') === '1' ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ request('situacion') === '0' ? 'selected' : '' }}>Inactivo</option>
          </select>
        </div>

        <div class="md:col-span-3 flex items-end gap-2">
          <button class="inline-flex w-full items-center justify-center rounded-xl bg-gray-900 px-4 py-2.5 text-white shadow hover:bg-black focus:outline-none focus:ring-2 focus:ring-gray-400 transition">
            Buscar
          </button>
          <a href="{{ route('tipoarma.index') }}"
             class="inline-flex w-full items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-300 transition">
            Limpiar
          </a>
        </div>
      </form>
    </div>

    <!-- Formulario -->
    <div id="formContainer" class="mb-6 hidden rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
      <div class="mb-4 flex items-center justify-between">
        <h2 id="formTitle" class="text-lg font-semibold">Nuevo Tipo</h2>
        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600">Formulario</span>
      </div>

      <form id="tipoForm" method="POST" action="{{ route('tipoarma.store') }}">
        @csrf
        <input type="hidden" id="tipoIdHidden">
        <div id="methodSpoof"></div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Descripción</label>
            <input type="text" id="descripcion" name="clase_descripcion" required
                   class="w-full rounded-xl border border-gray-300 px-3 py-2.5 outline-none ring-blue-400 transition focus:ring-2">
          </div>

          <div>
            <label class="mb-1 block text-xs font-medium text-gray-600">Situación</label>
            <select id="situacion" name="clase_situacion" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 outline-none ring-blue-400 transition focus:ring-2">
              <option value="">Seleccionar...</option>
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
        </div>

        <div class="mt-5 flex items-center gap-3">
          <button type="submit" id="btnSubmit"
                  class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-white shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 active:scale-[.99] transition">
            Guardar
          </button>
          <button type="button" id="btnCancelar"
                  class="inline-flex items-center gap-2 rounded-xl bg-gray-500 px-4 py-2.5 text-white shadow hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300 active:scale-[.99] transition">
            Cancelar
          </button>
        </div>
      </form>
    </div>

    <!-- Tabla -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
      <div class="max-h-[60vh] overflow-auto">
        <table class="min-w-full text-sm">
          <thead class="sticky top-0 z-10 bg-gray-100/90 backdrop-blur">
            <tr class="text-left text-gray-600">
              <th class="px-4 py-3 font-medium">#</th>
              <th class="px-4 py-3 font-medium">Descripción</th>
              <th class="px-4 py-3 font-medium">Situación</th>
              <th class="px-4 py-3 font-medium">Creado</th>
              <th class="px-4 py-3 text-center font-medium">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($tipoarma as $tipo)
              <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-700">
                  {{ $loop->iteration }}
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-gray-900">{{ $tipo->clase_descripcion }}</div>
                  <div class="text-[11px] text-gray-400">ID: {{ $tipo->clase_id ?? $tipo->id }}</div>
                </td>
                <td class="px-4 py-3">
                  @php $activa = (int)$tipo->clase_situacion === 1; @endphp
                  <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium
                               {{ $activa ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $activa ? 'bg-green-600' : 'bg-red-600' }}"></span>
                    {{ $activa ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-600">
                  {{ optional($tipo->created_at)->format('d/m/Y') }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center">
                    <button
                      class="inline-flex items-center rounded-xl px-3 py-1.5 text-blue-700 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"
                      data-action="editar"
                      data-id="{{ $tipo->clase_id ?? $tipo->id }}"
                      data-descripcion="{{ $tipo->clase_descripcion }}"
                      data-situacion="{{ (int)$tipo->clase_situacion }}">
                      Editar
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-4 py-10 text-center text-gray-500">No hay tipos registrados</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

@endsection
<script>
    window.tipoarmaRoutes = {
        store: "{{ route('tipoarma.store') }}",
        update: "{{ route('tipoarma.update', ':id') }}"
    };
</script>

@vite('resources/js/tipoarma/index.js')
