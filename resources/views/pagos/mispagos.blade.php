@extends('layouts.app')

@section('title', 'Control de Pagos')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* REEMPLAZA SOLO TU SECCIÓN <style> CON ESTO */

        /* Animaciones suaves */
        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        @keyframes pulse-gentle {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        /* Mejores fondos y efectos de vidrio */
        .modal-backdrop {
            backdrop-filter: blur(12px);
            background: rgba(0, 0, 0, 0.7);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }

        /* Efectos hover mejorados */
        .hover-scale {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-scale:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        /* Botones más atractivos */
        .btn-enhanced {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-enhanced:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Cuotas más amigables */
        .cuota-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
        }

        .cuota-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            border-color: #e5e7eb;
        }

        .cuota-card.selected {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(99, 102, 241, 0.05));
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.2), 0 8px 25px rgba(59, 130, 246, 0.15);
        }

        /* Indicadores de pasos más elegantes */
        .step-indicator {
            position: relative;
            transition: all 0.3s ease;
        }

        .step-indicator::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -20px;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #e5e7eb, #d1d5db);
            transform: translateY(-50%);
            transition: all 0.5s ease;
        }

        .step-indicator.active::after {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.3);
        }

        .step-indicator:last-child::after {
            display: none;
        }

        /* Zona de upload más atractiva */
        .upload-zone {
            border: 3px dashed #d1d5db;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(248, 250, 252, 0.9));
        }

        .upload-zone:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(99, 102, 241, 0.03));
            transform: scale(1.02);
        }

        .upload-zone.dragover {
            border-color: #1d4ed8;
            background: linear-gradient(135deg, rgba(29, 78, 216, 0.1), rgba(67, 56, 202, 0.05));
            transform: scale(1.05);
        }

        /* Resultados OCR más presentables */
        .ocr-result {
            animation: slideInUp 0.5s ease-out;
        }

        /* Microinteracciones */
        .microinteraction {
            transition: all 0.2s ease;
        }

        .microinteraction:hover {
            transform: scale(1.05);
        }

        .microinteraction:active {
            transform: scale(0.95);
        }

        /* Mejores gradientes para botones */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        /* Animaciones de entrada */
        .animate-slideInUp {
            animation: slideInUp 0.6s ease-out;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-pulse-gentle {
            animation: pulse-gentle 2s ease-in-out infinite;
        }

        /* Responsive mejorado */
        @media (max-width: 768px) {
            .hover-scale:hover {
                transform: none;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            }

            .modal-backdrop .bg-white {
                margin: 10px;
                max-height: 95vh;
            }
        }
    </style>

    <!-- Encabezado -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Control de Pagos</h2>
        <p class="text-gray-600">Aquí puedes ver todas tus facturas, su estado y los pagos asociados.</p>
    </div>

    <!-- Estadísticas de Facturas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white shadow rounded-lg p-4 flex items-center justify-between">
            <div>
                <p class="text-gray-500">Total de Facturas</p>
                <p class="text-2xl font-semibold text-gray-800" id="totalFacturas">0</p>
            </div>
            <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center justify-between">
            <div>
                <p class="text-gray-500">Facturas Pendientes</p>
                <p class="text-2xl font-semibold text-gray-800" id="facturasPendientes">0</p>
            </div>
            <div class="w-12 h-12 flex items-center justify-center bg-yellow-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center justify-between">
            <div>
                <p class="text-gray-500">Pagos Completados</p>
                <p class="text-2xl font-semibold text-gray-800" id="pagosCompletados">0</p>
            </div>
            <div class="w-12 h-12 flex items-center justify-center bg-green-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-4 flex items-center justify-between">
            <div>
                <p class="text-gray-500">Pagos Parciales</p>
                <p class="text-2xl font-semibold text-gray-800" id="pagosParciales">0</p>
            </div>
            <div class="w-12 h-12 flex items-center justify-center bg-orange-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m0-4h.01M12 12v.01" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Tabla de Facturas -->
    <div class="overflow-x-auto bg-gray-50 rounded-lg shadow">
        <table class="min-w-full table-auto divide-y divide-gray-200" id="tablaFacturas">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-700">Factura #</th>
                    <th class="px-4 py-2 text-left text-gray-700">Cliente</th>
                    <th class="px-4 py-2 text-left text-gray-700">Monto Total</th>
                    <th class="px-4 py-2 text-left text-gray-700">Pagos Realizados</th>
                    <th class="px-4 py-2 text-left text-gray-700">Monto Pendiente</th>
                    <th class="px-4 py-2 text-left text-gray-700">Estado</th>
                    <th class="px-4 py-2 text-left text-gray-700">Acciones</th>
                </tr>
            </thead>
            <tbody id="facturasBody">
                <!-- Filas generadas desde JS -->
            </tbody>
        </table>
    </div>

    <!-- Modal de Pago Mejorado -->
    <div id="modalPago" class="fixed inset-0 z-50 hidden items-center justify-center modal-backdrop">
        <div class="bg-white rounded-2xl shadow-2xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden">
            <!-- Header del Modal -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-white/20 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Procesar Pago</h3>
                            <p class="text-blue-100 text-sm">Selecciona las cuotas y adjunta tu comprobante</p>
                        </div>
                    </div>
                    <button id="btnCancelarPago" class="text-white/80 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Indicador de Pasos -->
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex items-center justify-center space-x-8">
                    <div class="step-indicator active flex items-center space-x-2">
                        <div
                            class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                            1</div>
                        <span class="text-sm font-medium text-blue-600">Seleccionar Cuotas</span>
                    </div>
                    <div class="step-indicator flex items-center space-x-2">
                        <div
                            class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                            2</div>
                        <span class="text-sm font-medium text-gray-500">Subir Comprobante</span>
                    </div>
                    <div class="step-indicator flex items-center space-x-2">
                        <div
                            class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-semibold">
                            3</div>
                        <span class="text-sm font-medium text-gray-500">Confirmar</span>
                    </div>
                </div>
            </div>

            <div class="p-6 max-h-[65vh] overflow-y-auto">
                <!-- Paso 1: Selección de Cuotas -->
                <div id="step1" class="space-y-6">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Selecciona las cuotas a pagar</h4>
                        <p class="text-gray-600">Puedes seleccionar una o múltiples cuotas pendientes</p>
                    </div>

                    <div id="cuotasList" class="space-y-3 max-h-64 overflow-y-auto">
                        <!-- Las cuotas se generan aquí dinámicamente -->
                    </div>

                    <div class="bg-blue-50 p-4 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-blue-800">Total a Pagar:</span>
                            <span id="totalSeleccionado" class="text-2xl font-bold text-blue-600">Q 0.00</span>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button id="btnContinuarPaso2"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            Continuar con Comprobante
                        </button>
                    </div>
                </div>

                <!-- Paso 2: Subir Comprobante -->
                <div id="step2" class="hidden space-y-6">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Adjunta tu comprobante de pago</h4>
                        <p class="text-gray-600">Sube una imagen clara del comprobante o ingresa los datos manualmente</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Banco destino <span class="text-red-500">*</span>
                            </label>
                            <select id="bancoSelectTop"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">— Selecciona el banco —</option>
                                <option value="1">Banrural</option>
                                <option value="2">Banco Industrial</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Este banco se usará para validar el comprobante.</p>
                        </div>
                    </div>

                    <!-- Zona de Upload -->
                    <div class="upload-zone p-8 rounded-xl text-center cursor-pointer" id="uploadZone">
                        <input id="inputComprobante" type="file" accept="image/*" class="hidden">
                        <div id="uploadContent">
                            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                </path>
                            </svg>
                            <p class="text-xl font-medium text-gray-700 mb-2">Arrastra tu imagen aquí</p>
                            <p class="text-gray-500 mb-4">o haz clic para seleccionar</p>
                            <button type="button"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                Seleccionar Archivo
                            </button>
                        </div>
                        <div id="previewContent" class="hidden">
                            <img id="imagePreview" class="max-h-48 mx-auto rounded-lg shadow-md mb-4">
                            <p class="text-green-600 font-medium">✓ Imagen cargada correctamente</p>
                        </div>
                    </div>

                    <!-- Opciones de Procesamiento -->
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex flex-wrap gap-4 justify-center">
                            <button id="btnOcr"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                                    </path>
                                </svg>
                                Extraer Datos con IA
                            </button>
                            <button id="btnEditarManual"
                                class="bg-violet-600 hover:bg-violet-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Ingresar Manualmente
                            </button>
                        </div>

                        <!-- Mensaje debajo del botón "Ingresar Manualmente" -->
                        <div class="text-center text-sm text-gray-600 max-w-xs">
                            <p>Si ya no cuenta con su voucher, puede ingresar los datos manualmente.</p>
                        </div>
                    </div>

                    <!-- Resultado OCR -->
                    <div id="ocrPreview" class="hidden ocr-result">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="bg-green-500 p-2 rounded-lg mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h5 class="text-lg font-semibold text-green-800">Datos Extraídos Automáticamente</h5>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="ocrRows">
                                <!-- Datos OCR se insertan aquí -->
                            </div>
                        </div>
                    </div>

                    <!-- Formulario Manual -->
                    <div id="formWrap" class="hidden">
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
                            <div class="flex items-center mb-4">
                                <div class="bg-violet-500 p-2 rounded-lg mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </div>
                                <h5 class="text-lg font-semibold text-gray-800">Ingresar Datos Manualmente</h5>
                            </div>
                            <form id="datosPagoForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha y Hora</label>
                                    <input name="fecha" type="datetime-local"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="25/07/2025 16:44:32">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto (Q)</label>
                                    <input name="monto" type="number" step="0.01"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Referencia / Número</label>
                                    <input name="referencia"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Número de referencia">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Concepto</label>
                                    <input name="concepto"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Descripción del pago">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button id="btnVolverPaso1"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                            ← Volver a Cuotas
                        </button>
                        <button id="btnContinuarPaso3"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            Continuar a Confirmación →
                        </button>
                    </div>
                </div>

                <!-- Paso 3: Confirmación -->
                <div id="step3" class="hidden space-y-6">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">Confirma tu pago</h4>
                        <p class="text-gray-600">Revisa los datos antes de enviar</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Resumen de Cuotas -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                            <h5 class="text-lg font-semibold text-blue-800 mb-4">Cuotas Seleccionadas</h5>
                            <div id="resumenCuotas" class="space-y-2">
                                <!-- Se llena dinámicamente -->
                            </div>
                            <div class="border-t border-blue-200 mt-4 pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-blue-800">Total:</span>
                                    <span id="totalFinal" class="text-xl font-bold text-blue-600">Q 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de Datos -->
                        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                            <h5 class="text-lg font-semibold text-green-800 mb-4">Datos del Comprobante</h5>
                            <div id="resumenDatos" class="space-y-2 text-sm">
                                <!-- Se llena dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button id="btnVolverPaso2"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                            ← Volver a Comprobante
                        </button>
                        <button id="btnEnviarPago"
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-bold transition-colors text-lg">
                            <svg class="w-6 h-6 inline-block mr-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Confirmar Pago
                        </button>
                    </div>
                </div>
            </div>

            <!-- Para compatibilidad con el JS existente -->
            <button id="btnSubirPago" type="button" class="hidden"></button>
        </div>
    </div>
@endsection
@vite('resources/js/pagos/mispagos.js')
