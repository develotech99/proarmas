// Función para obtener las subcategorías de la categoría seleccionada
async function obtenerSubcategorias(categoriaId) {
    const subcategoriaSelect = document.getElementById("subcategoria");

    if (!subcategoriaSelect) return;

    try {
        const response = await fetch(`/api/ventas/subcategorias/${categoriaId}`);
          
        const data = await response.json();

        console.log(data);

        // Limpiar y habilitar el select de subcategorías
        subcategoriaSelect.innerHTML = '<option value="">Seleccionar subcategoría...</option>';
        subcategoriaSelect.disabled = false;

        // Llenar el select con las subcategorías
        data.forEach(subcategoria => {
            subcategoriaSelect.innerHTML += `<option value="${subcategoria.subcategoria_id}">${subcategoria.subcategoria_nombre}</option>`;
        });
    } catch (error) {
        console.error("Error:", error);
        alert("Error al cargar las subcategorías");
    }
}

// Función para obtener las marcas de una subcategoría seleccionada
async function obtenerMarcas(subcategoriaId) {
    const marcaSelect = document.getElementById("marca");

    if (!marcaSelect) return;

    try {
        const response = await fetch(`/api/ventas/marcas/${subcategoriaId}`);
        const data = await response.json();

        // Limpiar y habilitar el select de marcas
        marcaSelect.innerHTML = '<option value="">Seleccionar marca...</option>';
        marcaSelect.disabled = false;

        // Llenar el select con las marcas
        data.forEach(marca => {
            marcaSelect.innerHTML += `<option value="${marca.marca_id}">${marca.marca_descripcion}</option>`;
        });
    } catch (error) {
        console.error("Error:", error);
        alert("Error al cargar las marcas");
    }
}

// Función para obtener los modelos de una marca seleccionada
async function obtenerModelos(marcaId) {
    const modeloSelect = document.getElementById("modelo");

    if (!modeloSelect) return;

    try {
        const response = await fetch(`/api/ventas/modelos/${marcaId}`);
        const data = await response.json();

        // Limpiar y habilitar el select de modelos
        modeloSelect.innerHTML = '<option value="">Seleccionar modelo...</option>';
        modeloSelect.disabled = false;

        // Llenar el select con los modelos
        data.forEach(modelo => {
            modeloSelect.innerHTML += `<option value="${modelo.modelo_id}">${modelo.modelo_descripcion}</option>`;
        });
    } catch (error) {
        console.error("Error:", error);
        alert("Error al cargar los modelos");
    }
}

// Función para obtener los calibres de un modelo seleccionado
async function obtenerCalibres(modeloId) {
    const calibreSelect = document.getElementById("calibre");

    if (!calibreSelect) return;

    try {
        const response = await fetch(`/api/ventas/calibres/${modeloId}`);
        const data = await response.json();

        // Limpiar y habilitar el select de calibres
        calibreSelect.innerHTML = '<option value="">Seleccionar calibre...</option>';
        calibreSelect.disabled = false;

        // Llenar el select con los calibres
        data.forEach(calibre => {
            calibreSelect.innerHTML += `<option value="${calibre.calibre_id}">${calibre.calibre_nombre}</option>`;
        });
    } catch (error) {
        console.error("Error:", error);
        alert("Error al cargar los calibres");
    }
}

// Función para obtener los productos con todos los filtros aplicados
async function obtenerProductos() {
    const categoriaId = document.getElementById("categoria").value;
    const subcategoriaId = document.getElementById("subcategoria").value;
    const marcaId = document.getElementById("marca").value;
    const modeloId = document.getElementById("modelo").value;
    const calibreId = document.getElementById("calibre").value;

    if (!categoriaId || !subcategoriaId || !marcaId || !modeloId || !calibreId) {
        limpiarDashboard();
        return;
    }

    const productoSelect = document.getElementById("producto");
    productoSelect.innerHTML = '<option value="">Cargando productos...</option>';
    productoSelect.disabled = true;
    limpiarDashboard();

    try {
        const response = await fetch(`/api/ventas/productos?categoria_id=${categoriaId}&subcategoria_id=${subcategoriaId}&marca_id=${marcaId}&modelo_id=${modeloId}&calibre_id=${calibreId}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const productos = await response.json()
        console.log(productos);
        
        productoSelect.innerHTML = '<option value="">Seleccionar producto...</option>';
        
        if (productos.length === 0) {
            productoSelect.innerHTML = '<option value="">No hay productos disponibles</option>';
            productoSelect.disabled = true;
            return;
        }

        // Llenar el select con información concatenada
        productos.forEach(producto => {
            const optionText = `${producto.producto_nombre} - ${producto.producto_completo} - $${formatearPrecio(producto.precio_venta)} (Stock: ${producto.producto_stock})`;
            
            productoSelect.innerHTML += `<option value="${producto.producto_id}" 
                data-precio="${producto.precio_venta}"
                data-stock="${producto.producto_stock}"
                data-nombre="${producto.producto_nombre}"
                data-completo="${producto.producto_completo}">
                ${optionText}
            </option>`;
        });
        
        productoSelect.disabled = false;

    } catch (error) {
        console.error("Error:", error);
        productoSelect.innerHTML = '<option value="">Error al cargar productos</option>';
        alert("Error al cargar los productos");
    }
}

// Event listener para cuando cambia el producto seleccionado
document.getElementById("producto").addEventListener("change", function() {
    const select = this;
    const selectedOption = select.options[select.selectedIndex];
    
    if (select.value === "") {
        limpiarDashboard();
        return;
    }
    
    // Actualizar dashboard con datos del producto seleccionado
    const stock = parseInt(selectedOption.dataset.stock);
    const precio = parseFloat(selectedOption.dataset.precio);
    const productoCompleto = selectedOption.dataset.completo;
    
    // Actualizar stock disponible
    document.getElementById("stockDisponible").textContent = stock;
    document.getElementById("stockDisponible").className = 
        stock > 10 ? "text-2xl font-bold text-green-600" :
        stock > 5 ? "text-2xl font-bold text-yellow-600" :
        stock > 0 ? "text-2xl font-bold text-orange-600" :
        "text-2xl font-bold text-red-600";

    // Actualizar precio unitario
    document.getElementById("precioUnitario").textContent = `$${formatearPrecio(precio)}`;

    // Actualizar producto seleccionado
    document.getElementById("productoSeleccionado").textContent = productoCompleto;
    
    // Habilitar/deshabilitar controles
    const cantidadInput = document.getElementById("cantidad");
    const agregarBtn = document.getElementById("agregarCarrito");
    
    cantidadInput.disabled = false;
    cantidadInput.max = stock;
    cantidadInput.value = 1;
    
    if (stock <= 0) {
        agregarBtn.disabled = true;
        agregarBtn.textContent = "Sin Stock";
        agregarBtn.className = "w-full bg-red-500 text-white px-4 py-2 rounded-md cursor-not-allowed";
    } else {
        agregarBtn.disabled = false;
        agregarBtn.textContent = "Agregar al Carrito";
        agregarBtn.className = "w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors";
    }
});

// Función para limpiar dashboard
function limpiarDashboard() {
    document.getElementById("stockDisponible").textContent = "--";
    document.getElementById("stockDisponible").className = "text-2xl font-bold text-blue-600";
    document.getElementById("precioUnitario").textContent = "$--";
    document.getElementById("productoSeleccionado").textContent = "--";
    
    document.getElementById("cantidad").disabled = true;
    document.getElementById("cantidad").value = "";
    document.getElementById("agregarCarrito").disabled = true;
}

// Función para formatear precios
function formatearPrecio(precio) {
    return parseFloat(precio).toLocaleString('es-GT', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Validación de cantidad
document.getElementById("cantidad").addEventListener("input", function() {
    const cantidad = parseInt(this.value);
    const stockDisponible = parseInt(document.getElementById("stockDisponible").textContent);
    const agregarBtn = document.getElementById("agregarCarrito");
    
    if (cantidad <= 0 || cantidad > stockDisponible || isNaN(cantidad)) {
        agregarBtn.disabled = true;
        this.style.borderColor = "#ef4444";
    } else {
        agregarBtn.disabled = false;
        this.style.borderColor = "#10b981";
    }
});

// Event listeners para los cambios en los selects
document.getElementById("categoria").addEventListener("change", function () {
    const categoriaId = this.value;
    limpiarSelectsPosteriores(["subcategoria", "marca", "modelo", "calibre", "producto"]);
    if (categoriaId) {
        obtenerSubcategorias(categoriaId);
    }
});

document.getElementById("subcategoria").addEventListener("change", function () {
    const subcategoriaId = this.value;
    limpiarSelectsPosteriores(["marca", "modelo", "calibre", "producto"]);
    if (subcategoriaId) {
        obtenerMarcas(subcategoriaId);
    }
});

document.getElementById("marca").addEventListener("change", function () {
    const marcaId = this.value;
    limpiarSelectsPosteriores(["modelo", "calibre", "producto"]);
    if (marcaId) {
        obtenerModelos(marcaId);
    }
});

document.getElementById("modelo").addEventListener("change", function () {
    const modeloId = this.value;
    limpiarSelectsPosteriores(["calibre", "producto"]);
    if (modeloId) {
        obtenerCalibres(modeloId);
    }
});

document.getElementById("calibre").addEventListener("change", function () {
    obtenerProductos();
});

// Función para limpiar los selects posteriores
function limpiarSelectsPosteriores(selects) {
    selects.forEach((id) => {
        const select = document.getElementById(id);
        select.innerHTML = '<option value="">Seleccionar...</option>';
        select.disabled = true;
    });
}


