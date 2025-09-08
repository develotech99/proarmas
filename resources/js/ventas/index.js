// Función para obtener las subcategorías de la categoría seleccionada
async function obtenerSubcategorias(categoriaId) {
    const subcategoriaSelect = document.getElementById("subcategoria");

    if (!subcategoriaSelect) return;

    try {
        const response = await fetch(`/api/ventas/subcategorias/${categoriaId}`);
        const data = await response.json();

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

    if (!categoriaId || !subcategoriaId || !marcaId || !modeloId || !calibreId) return;

    const productoSelect = document.getElementById("producto");
    productoSelect.innerHTML = '<option value="">Seleccionar producto...</option>';
    productoSelect.disabled = true;

    try {
        const response = await fetch(`/api/ventas/productos?categoria_id=${categoriaId}&subcategoria_id=${subcategoriaId}&marca_id=${marcaId}&modelo_id=${modeloId}&calibre_id=${calibreId}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        productoSelect.disabled = false;
        result.forEach(producto => {
            productoSelect.innerHTML += `<option value="${producto.producto_id}">${producto.producto_nombre} - $${producto.precio_venta}</option>`;
        });
    } catch (error) {
        console.error("Error:", error);
        alert("Error al cargar los productos");
    }
}

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
