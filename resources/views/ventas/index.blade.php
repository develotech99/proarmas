@extends('layouts.app')

@section('title', 'Gestión de Ventas')

@section('content')


<!DOCTYPE html>
<html lang="es">



</head>

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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                            <select id="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Seleccionar...</option>
                                <option value="armas">Armas</option>
                                <option value="municion">Munición</option>
                                <option value="accesorios">Accesorios</option>


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

                            <label class="block text-sm font-medium text-gray-700 mb-2">Subcategoría</label>
                            <select id="subcategoria" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                       <label for="subcategoria" class="block text-sm font-medium text-gray-700 mb-2">Subcategoría</label>
                            <select id="subcategoria" name="subcategoria"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                 <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Marca -->
                        <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                            <select id="marca" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>

                            <label for="marca" class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                            <select id="marca" name="marca"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>

                                <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Modelo -->
                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                            <select id="modelo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>

                            <label for="modelo" class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                            <select id="modelo" name="modelo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>

                                <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Calibre -->
                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-2">Calibre</label>
                            <select id="calibre" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>

                            <label for="calibre" class="block text-sm font-medium text-gray-700 mb-2">Calibre</label>
                            <select id="calibre" name="calibre"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" disabled>
                  <option value="">Seleccionar...</option>
                            </select>
                        </div>

                        <!-- Producto Final -->
                        <div>
 
                            <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                            <select id="producto" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>

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
 
                            <input type="date" id="fechaVenta" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                            <input type="date" id="fechaVenta"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
 
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
 
                            <input type="number" id="cantidad" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <button id="agregarCarrito" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors disabled:bg-gray-400" disabled>

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

                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Cliente</label>
                        <select id="cliente" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccionar cliente...</option>
                            <option value="1">Juan Pérez - 12345678</option>
                            <option value="2">María González - 87654321</option>
                            <option value="nuevo">+ Nuevo Cliente</option>
                        </select>
                    </div>

                    <!-- Formulario Nuevo Cliente -->
                    <div id="nuevoClienteForm" class="hidden space-y-3">
                        <input type="text" id="nombreCliente" placeholder="Nombre completo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="text" id="cedulaCliente" placeholder="Cédula" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="tel" id="telefonoCliente" placeholder="Teléfono" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="email" id="emailCliente" placeholder="Email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button id="guardarCliente" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Guardar Cliente
                        </button>


                    <div>
                        <label for="cliente" class="block text-sm font-medium text-gray-700 mb-2">Clientes</label>
                        <select id="cliente" name="cliente"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Seleccionar...</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->cliente_id }}">{{ $cliente->nombre }}</option>
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
                        <label class="flex items-center">
                            <input type="radio" name="metodoPago" value="efectivo" class="mr-2">
                            <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>Efectivo
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="metodoPago" value="tarjeta_credito" class="mr-2">
                            <i class="fas fa-credit-card mr-2 text-blue-600"></i>Tarjeta de Crédito
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="metodoPago" value="tarjeta_debito" class="mr-2">
                            <i class="fas fa-credit-card mr-2 text-purple-600"></i>Tarjeta de Débito
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="metodoPago" value="cheque" class="mr-2">
                            <i class="fas fa-money-check mr-2 text-yellow-600"></i>Cheque
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="metodoPago" value="visacuotas" class="mr-2">
                            <i class="fas fa-calendar-alt mr-2 text-red-600"></i>Visacuotas
                        </label>

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
                            <input type="number" id="descuento" min="0" max="100" value="0" class="w-20 px-2 py-1 border border-gray-300 rounded text-center">
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

                        
                        <button id="procesarVenta" class="w-full bg-green-600 text-white px-4 py-3 rounded-md hover:bg-green-700 transition-colors font-semibold disabled:bg-gray-400" disabled>


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

    <script>
        // Datos de ejemplo (en Laravel vendrían de la base de datos)
        const datosEjemplo = {
            subcategorias: {
                'armas': ['Pistolas', 'Rifles', 'Escopetas'],
                'municion': ['Pistola', 'Rifle', 'Escopeta'],
                'accesorios': ['Miras', 'Cargadores', 'Fundas']
            },
            marcas: {
                'Pistolas': ['CZ', 'Glock', 'Smith & Wesson', 'Beretta'],
                'Rifles': ['Remington', 'Winchester', 'Savage'],
                'Escopetas': ['Benelli', 'Beretta', 'Remington']
            },
            modelos: {
                'CZ': ['CZ 75', 'CZ P-10C', 'CZ Shadow 2'],
                'Glock': ['Glock 17', 'Glock 19', 'Glock 43'],
                'Smith & Wesson': ['M&P 9', 'M&P Shield', 'Model 686']
            },
            calibres: {
                'CZ 75': ['9mm', '.40 S&W'],
                'CZ P-10C': ['9mm'],
                'CZ Shadow 2': ['9mm'],
                'Glock 17': ['9mm'],
                'Glock 19': ['9mm'],
                'Glock 43': ['9mm']
            },
            productos: {
                'CZ 75-9mm': { id: 1, stock: 5, precio: 1500.00, nombre: 'CZ 75 9mm' },
                'CZ P-10C-9mm': { id: 2, stock: 3, precio: 1200.00, nombre: 'CZ P-10C 9mm' },
                'Glock 17-9mm': { id: 3, stock: 8, precio: 1800.00, nombre: 'Glock 17 9mm' }
            }
        };

        let carrito = [];
        let productoActual = null;

        // Inicializar fecha actual
        document.getElementById('fechaVenta').valueAsDate = new Date();

        // Event listeners para filtros en cascada
        document.getElementById('categoria').addEventListener('change', function() {
            const categoria = this.value;
            const subcategoriaSelect = document.getElementById('subcategoria');
            
            limpiarSelect(subcategoriaSelect);
            limpiarSelectsPosteriores(['marca', 'modelo', 'calibre', 'producto']);
            
            if (categoria && datosEjemplo.subcategorias[categoria]) {
                subcategoriaSelect.disabled = false;
                datosEjemplo.subcategorias[categoria].forEach(sub => {
                    subcategoriaSelect.innerHTML += `<option value="${sub}">${sub}</option>`;
                });
            } else {
                subcategoriaSelect.disabled = true;
            }
            actualizarDashboard();
        });

        document.getElementById('subcategoria').addEventListener('change', function() {
            const subcategoria = this.value;
            const marcaSelect = document.getElementById('marca');
            
            limpiarSelect(marcaSelect);
            limpiarSelectsPosteriores(['modelo', 'calibre', 'producto']);
            
            if (subcategoria && datosEjemplo.marcas[subcategoria]) {
                marcaSelect.disabled = false;
                datosEjemplo.marcas[subcategoria].forEach(marca => {
                    marcaSelect.innerHTML += `<option value="${marca}">${marca}</option>`;
                });
            } else {
                marcaSelect.disabled = true;
            }
            actualizarDashboard();
        });

        document.getElementById('marca').addEventListener('change', function() {
            const marca = this.value;
            const modeloSelect = document.getElementById('modelo');
            
            limpiarSelect(modeloSelect);
            limpiarSelectsPosteriores(['calibre', 'producto']);
            
            if (marca && datosEjemplo.modelos[marca]) {
                modeloSelect.disabled = false;
                datosEjemplo.modelos[marca].forEach(modelo => {
                    modeloSelect.innerHTML += `<option value="${modelo}">${modelo}</option>`;
                });
            } else {
                modeloSelect.disabled = true;
            }
            actualizarDashboard();
        });

        document.getElementById('modelo').addEventListener('change', function() {
            const modelo = this.value;
            const calibreSelect = document.getElementById('calibre');
            
            limpiarSelect(calibreSelect);
            limpiarSelectsPosteriores(['producto']);
            
            if (modelo && datosEjemplo.calibres[modelo]) {
                calibreSelect.disabled = false;
                datosEjemplo.calibres[modelo].forEach(calibre => {
                    calibreSelect.innerHTML += `<option value="${calibre}">${calibre}</option>`;
                });
            } else {
                calibreSelect.disabled = true;
            }
            actualizarDashboard();
        });

        document.getElementById('calibre').addEventListener('change', function() {
            const modelo = document.getElementById('modelo').value;
            const calibre = this.value;
            const productoSelect = document.getElementById('producto');
            
            limpiarSelect(productoSelect);
            
            if (modelo && calibre) {
                const claveProducto = `${modelo}-${calibre}`;
                if (datosEjemplo.productos[claveProducto]) {
                    productoSelect.disabled = false;
                    const producto = datosEjemplo.productos[claveProducto];
                    productoSelect.innerHTML += `<option value="${claveProducto}">${producto.nombre}</option>`;
                }
            } else {
                productoSelect.disabled = true;
            }
            actualizarDashboard();
        });

        document.getElementById('producto').addEventListener('change', function() {
            const claveProducto = this.value;
            if (claveProducto && datosEjemplo.productos[claveProducto]) {
                productoActual = datosEjemplo.productos[claveProducto];
                document.getElementById('agregarCarrito').disabled = false;
            } else {
                productoActual = null;
                document.getElementById('agregarCarrito').disabled = true;
            }
            actualizarDashboard();
        });

        // Cliente nuevo
        document.getElementById('cliente').addEventListener('change', function() {
            const nuevoClienteForm = document.getElementById('nuevoClienteForm');
            if (this.value === 'nuevo') {
                nuevoClienteForm.classList.remove('hidden');
            } else {
                nuevoClienteForm.classList.add('hidden');
            }
        });

        // Agregar al carrito
        document.getElementById('agregarCarrito').addEventListener('click', function() {
            const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
            
            if (!productoActual || cantidad <= 0) {
                alert('Por favor seleccione un producto y una cantidad válida');
                return;
            }
            
            if (cantidad > productoActual.stock) {
                alert(`No hay suficiente stock. Disponible: ${productoActual.stock}`);
                return;
            }

            const itemCarrito = {
                id: productoActual.id,
                nombre: productoActual.nombre,
                precio: productoActual.precio,
                cantidad: cantidad,
                subtotal: productoActual.precio * cantidad
            };

            carrito.push(itemCarrito);
            actualizarCarrito();
            limpiarFormulario();
        });

        // Calcular descuento
        document.getElementById('descuento').addEventListener('input', calcularTotal);

        function limpiarSelect(select) {
            select.innerHTML = '<option value="">Seleccionar...</option>';
        }

        function limpiarSelectsPosteriores(selects) {
            selects.forEach(id => {
                const select = document.getElementById(id);
                limpiarSelect(select);
                select.disabled = true;
            });
        }

        function actualizarDashboard() {
            const claveProducto = document.getElementById('producto').value;
            
            if (claveProducto && datosEjemplo.productos[claveProducto]) {
                const producto = datosEjemplo.productos[claveProducto];
                document.getElementById('stockDisponible').textContent = producto.stock;
                document.getElementById('precioUnitario').textContent = `$${producto.precio.toFixed(2)}`;
                document.getElementById('productoSeleccionado').textContent = producto.nombre;
            } else {
                document.getElementById('stockDisponible').textContent = '--';
                document.getElementById('precioUnitario').textContent = '$--';
                document.getElementById('productoSeleccionado').textContent = '--';
            }
        }

        function actualizarCarrito() {
            const carritoItems = document.getElementById('carritoItems');
            
            if (carrito.length === 0) {
                carritoItems.innerHTML = '<p class="text-gray-500 text-center py-4">No hay productos en el carrito</p>';
            } else {
                carritoItems.innerHTML = carrito.map((item, index) => `
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <span class="font-medium">${item.nombre}</span>
                            <span class="text-sm text-gray-500 ml-2">x${item.cantidad} @ $${item.precio.toFixed(2)}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="font-semibold">$${item.subtotal.toFixed(2)}</span>
                            <button onclick="eliminarDelCarrito(${index})" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            }
            
            calcularTotal();
        }

        function eliminarDelCarrito(index) {
            carrito.splice(index, 1);
            actualizarCarrito();
        }

        function calcularTotal() {
            const subtotal = carrito.reduce((sum, item) => sum + item.subtotal, 0);
            const descuento = parseFloat(document.getElementById('descuento').value) || 0;
            const montoDescuento = subtotal * (descuento / 100);
            const total = subtotal - montoDescuento;

            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('totalFinal').textContent = `$${total.toFixed(2)}`;
            document.getElementById('totalCarrito').textContent = `$${total.toFixed(2)}`;

            // Habilitar botón de procesar si hay items y cliente seleccionado
            const cliente = document.getElementById('cliente').value;
            const metodoPago = document.querySelector('input[name="metodoPago"]:checked');
            document.getElementById('procesarVenta').disabled = !(carrito.length > 0 && cliente && metodoPago);
        }

        function limpiarFormulario() {
            document.getElementById('cantidad').value = '';
            // Reset filtros
            ['categoria', 'subcategoria', 'marca', 'modelo', 'calibre', 'producto'].forEach(id => {
                document.getElementById(id).selectedIndex = 0;
            });
            limpiarSelectsPosteriores(['subcategoria', 'marca', 'modelo', 'calibre', 'producto']);
            productoActual = null;
            document.getElementById('agregarCarrito').disabled = true;
            actualizarDashboard();
        }

        // Event listeners para habilitar botón de procesar venta
        document.getElementById('cliente').addEventListener('change', calcularTotal);
        document.querySelectorAll('input[name="metodoPago"]').forEach(radio => {
            radio.addEventListener('change', calcularTotal);
        });

        // Procesar venta
        document.getElementById('procesarVenta').addEventListener('click', function() {
            const cliente = document.getElementById('cliente').value;
            const metodoPago = document.querySelector('input[name="metodoPago"]:checked')?.value;
            const fecha = document.getElementById('fechaVenta').value;
            
            if (!cliente || !metodoPago || carrito.length === 0) {
                alert('Por favor complete todos los campos requeridos');
                return;
            }

            // Aquí enviarías los datos al servidor Laravel
            const ventaData = {
                cliente_id: cliente,
                fecha: fecha,
                metodo_pago: metodoPago,
                descuento: parseFloat(document.getElementById('descuento').value) || 0,
                items: carrito
            };

            console.log('Datos de venta:', ventaData);
            alert('¡Venta procesada exitosamente!');
            
            // Limpiar formulario
            carrito = [];
            actualizarCarrito();
            limpiarFormulario();
            document.getElementById('cliente').selectedIndex = 0;
            document.getElementById('descuento').value = 0;
            document.querySelectorAll('input[name="metodoPago"]').forEach(radio => radio.checked = false);
        });

        // Guardar nuevo cliente
        document.getElementById('guardarCliente').addEventListener('click', function() {
            const nombre = document.getElementById('nombreCliente').value;
            const cedula = document.getElementById('cedulaCliente').value;
            
            if (!nombre || !cedula) {
                alert('Nombre y cédula son requeridos');
                return;
            }

            // Aquí guardarías el cliente en la base de datos
            console.log('Nuevo cliente:', {
                nombre: nombre,
                cedula: cedula,
                telefono: document.getElementById('telefonoCliente').value,
                email: document.getElementById('emailCliente').value
            });

            alert('Cliente guardado exitosamente');
            
            // Agregar al select y seleccionar
            const clienteSelect = document.getElementById('cliente');
            const newOption = document.createElement('option');
            newOption.value = 'nuevo_cliente';
            newOption.textContent = `${nombre} - ${cedula}`;
            clienteSelect.insertBefore(newOption, clienteSelect.lastElementChild);
            clienteSelect.value = 'nuevo_cliente';
            
            // Ocultar formulario
            document.getElementById('nuevoClienteForm').classList.add('hidden');
        });
    </script>
</body>
</html>
@endsection

    @vite('resources/js/ventas/index.js')
