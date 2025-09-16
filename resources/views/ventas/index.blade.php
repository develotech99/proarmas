
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ventas - Armería</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">
            <i class="fas fa-shopping-cart mr-2"></i>Sistema de Ventas - Armería
        </h1>

        <!-- Dashboard de Stock -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-chart-bar mr-2"></i>Stock Disponible
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="text-2xl font-bold text-blue-600" id="stockDisponible">--</div>
                    <div class="text-sm text-blue-500">Unidades Disponibles</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="text-2xl font-bold text-green-600" id="precioUnitario">$--</div>
                    <div class="text-sm text-green-500">Precio Unitario</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <div class="text-2xl font-bold text-yellow-600" id="productoSeleccionado">--</div>
                    <div class="text-sm text-yellow-500">Producto Seleccionado</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                    <div class="text-2xl font-bold text-purple-600" id="totalCarrito">$0.00</div>
                    <div class="text-sm text-purple-500">Total Carrito</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel de Filtros -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-filter mr-2"></i>Filtros de Producto
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Categoría -->
                        <div>
                            <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                            <select id="categoria" name="categoria"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seleccionar...</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->categoria_id }}">{{ $categoria->categoria_nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategoría -->
                        <div>
                            <label for="subcategoria" class="block text-sm font-medium text-gray-700 mb-2">Subcategoría</label>
                            <select id="subcategoria" name="subcategoria"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Marca -->
                        <div>
                            <label for="marca" class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                            <select id="marca" name="marca"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Modelo -->
                        <div>
                            <label for="modelo" class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                            <select id="modelo" name="modelo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Calibre -->
                        <div>
                            <label for="calibre" class="block text-sm font-medium text-gray-700 mb-2">Calibre</label>
                            <select id="calibre" name="calibre"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Producto Final -->
                        <div>
                            <label for="producto" class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                            <select id="producto"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Detalles de Venta -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-shopping-bag mr-2"></i>Detalles de Venta
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Fecha -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Venta</label>
                            <input type="date" id="fechaVenta"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
                            <input type="number" id="cantidad" min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <button id="agregarCarrito"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors disabled:bg-gray-400"
                        disabled>
                        <i class="fas fa-plus mr-2"></i>Agregar al Carrito
                    </button>
                </div>
            </div>

            <!-- Panel de Cliente y Pago -->
            <div class="lg:col-span-1">
                <!-- Cliente -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-user mr-2"></i>Cliente
                    </h2>

                    <div>
                        <label for="cliente" class="block text-sm font-medium text-gray-700 mb-2">Clientes</label>
                        <select id="cliente" name="cliente"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccionar...</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->user_id }}">{{ $cliente->user_primer_nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Método de Pago -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-credit-card mr-2"></i>Método de Pago
                    </h2>

                    <div class="space-y-2">
                        @foreach ($metodopago as $metodo)
                            <label class="flex items-center">
                                <input type="radio" name="metodoPago" value="{{ $metodo->metpago_id }}" class="mr-2">
                                <i class="fas fa-credit-card mr-2 text-blue-600"></i>{{ $metodo->metpago_descripcion }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Descuento y Total -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-calculator mr-2"></i>Resumen
                    </h2>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <label class="text-sm">Descuento (%):</label>
                            <input type="number" id="descuento" min="0" max="100" value="0"
                                class="w-20 px-2 py-1 border border-gray-300 rounded text-center">
                        </div>

                        <div class="flex justify-between font-bold text-lg border-t pt-2">
                            <span>Total a Pagar:</span>
                            <span id="totalFinal">$0.00</span>
                        </div>

                        <button id="procesarVenta"
                            class="w-full bg-green-600 text-white px-4 py-3 rounded-md hover:bg-green-700 transition-colors font-semibold disabled:bg-gray-400"
                            disabled>
                            <i class="fas fa-check mr-2"></i>Procesar Venta
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carrito de Compras -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-shopping-cart mr-2"></i>Carrito de Compras
            </h2>
            <div id="carritoItems" class="space-y-2">
                <p class="text-gray-500 text-center py-4">No hay productos en el carrito</p>
            </div>
        </div>
    </div>

    @vite('resources/js/ventas/index.js')