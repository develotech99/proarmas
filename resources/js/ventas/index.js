// BUSQUEDA CLIENTES Y REGISTRO NUEVO CLIENTE
const btnBuscarCliente = document.getElementById("btnBuscarCliente");
const tipoClienteSelect = document.getElementById("tipoCliente");
const selectorPremium = document.getElementById("selectorPremium");
const clientePremiumSelect = document.getElementById("clientePremium");

async function clientesParticulares() {
    const nit = document.getElementById("nitClientes").value.trim();
    const dpi = document.getElementById("dpiClientes").value.trim();
    const select = document.getElementById("clienteSelect");

    const params = new URLSearchParams();
    if (nit) params.append("nit", nit);
    if (dpi) params.append("dpi", dpi);

    const res = await fetch(`/api/ventas/buscar?${params.toString()}`);
    const data = await res.json();
    console.log(data);

    // Siempre limpiamos el select
    select.innerHTML = "";

    if (data.length > 0) {
        Swal.fire({
            title: "Cliente Encontrado",
            text: `Se ${data.length === 1 ? "encontró" : "encontraron"} ${
                data.length
            } cliente(s).`,
            icon: "success",
            confirmButtonText: "Aceptar",
        });

        // SOLO mostramos los resultados, sin "Seleccionar..."
        data.forEach((c) => {
            const nombre = [
                c.cliente_nombre1,
                c.cliente_nombre2,
                c.cliente_apellido1,
                c.cliente_apellido2,
            ]
                .filter(Boolean)
                .join(" ");

            select.innerHTML += `
                <option value="${c.cliente_id}">
                    ${nombre} — NIT: ${c.cliente_nit ?? "SN"}
                </option>`;
        });

        // Si hay un único resultado → se selecciona automáticamente
        if (data.length === 1) {
            select.value = data[0].cliente_id;
        }
    } else {
        Swal.fire({
            title: "Cliente No Encontrado",
            text: "No se encontró el cliente con los datos proporcionados.",
            icon: "error",
            confirmButtonText: "Aceptar",
        });
        select.innerHTML = '<option value="">Cliente no encontrado</option>';
    }
}

btnBuscarCliente.addEventListener("click", function () {
    clientesParticulares();
});

// Event listener para el tipo de cliente
tipoClienteSelect.addEventListener("change", function () {
    const tipoSeleccionado = this.value;
    if (tipoSeleccionado === "2") {
        selectorPremium.style.display = "block";
        limpiarFormulario();
    } else {
        selectorPremium.style.display = "none";
        clientePremiumSelect.value = "";

        if (tipoSeleccionado === "1") {
            limpiarFormulario();
        }
    }

    actualizarEstado(
        `Tipo de cliente seleccionado: ${
            tipoSeleccionado === "1"
                ? "Normal"
                : tipoSeleccionado === "2"
                ? "Premium"
                : "Ninguno"
        }`
    );
});

// Event listener para el cliente premium seleccionado
clientePremiumSelect.addEventListener("change", function () {
    const clienteSeleccionado = this.options[this.selectedIndex];

    if (clienteSeleccionado.value !== "") {
        // Llenar los campos del formulario con los datos del cliente premium
        llenarDatosCliente(clienteSeleccionado);
        actualizarEstado(
            `Cliente premium seleccionado: ${clienteSeleccionado.textContent}`
        );
    } else {
        // Limpiar formulario si no hay cliente seleccionado
        limpiarFormulario();
        actualizarEstado("Seleccione un cliente premium");
    }
});

// Función para llenar los datos del cliente
function llenarDatosCliente(option) {
    document.getElementById("idCliente").value = option.dataset.clienteid || "";
    document.getElementById("nc_nombre1").value = option.dataset.nombre1 || "";
    document.getElementById("nc_nombre2").value = option.dataset.nombre2 || "";
    document.getElementById("nc_apellido1").value =
        option.dataset.apellido1 || "";
    document.getElementById("nc_apellido2").value =
        option.dataset.apellido2 || "";
    document.getElementById("nc_dpi").value = option.dataset.dpi || "";
}

// Función para limpiar el formulario
function limpiarFormulario() {
    document.getElementById("idCliente").value = "";
    document.getElementById("nc_nombre1").value = "";
    document.getElementById("nc_nombre2").value = "";
    document.getElementById("nc_apellido1").value = "";
    document.getElementById("nc_apellido2").value = "";
    document.getElementById("nc_dpi").value = "";
    document.getElementById("nc_nit").value = "";
    document.getElementById("nc_telefono").value = "";
    document.getElementById("nc_correo").value = "";
    document.getElementById("nc_direccion").value = "";
}

// Función para actualizar el estado
function actualizarEstado(mensaje) {
    document.getElementById("nc_estado").textContent = mensaje;
}

// GUARDAR UN NUEVO CLIENTE: NORMAL O PREMIUM
const btnNuevo = document.getElementById("btnNuevoCliente");
const modal = document.getElementById("modalNuevoCliente");
const overlay = document.getElementById("modalOverlayNC");
const btnCerrar = document.getElementById("modalCerrarNC");
const btnCancel = document.getElementById("modalCancelarNC");
const btnGuardar = document.getElementById("modalGuardarCliente");
const btnLimpiar = document.getElementById("btnLimpiarBusqueda");
const estado = document.getElementById("nc_estado");
const selectClientes = document.getElementById("cliente_particular");

// Funciones para abrir y cerrar el modal
function abrirModal() {
    modal.classList.remove("hidden");
    limpiarFormulario();
    selectorPremium.style.display = "none";
    clientePremiumSelect.value = "";
    tipoClienteSelect.value = "";
}

function cerrarModal() {
    modal.classList.add("hidden");
}

btnNuevo.addEventListener("click", abrirModal);
overlay.addEventListener("click", cerrarModal);
btnCerrar.addEventListener("click", cerrarModal);
btnCancel.addEventListener("click", cerrarModal);

async function guardarCliente() {
    const form = document.getElementById("formNuevoCliente");
    const formData = new FormData(form);

    estado.textContent = "Guardando cliente...";

    try {
        const response = await fetch("/api/clientes/guardar", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
            body: formData,
        });

        if (!response.ok) throw new Error("Error HTTP " + response.status); // Error HTTP

        const nuevo = await response.json();
        console.log(nuevo);

        Swal.fire({
            title: "¡Éxito!",
            text: "Cliente guardado correctamente.",
            icon: "success",
            confirmButtonText: "Aceptar",
        });

        estado.textContent = "Cliente guardado correctamente ✅";

        setTimeout(() => {
            cerrarModal();
            estado.textContent = "";
        }, 1000);
    } catch (error) {
        console.error(error);

        Swal.fire({
            title: "¡Error!",
            text: "No se pudo guardar el cliente. Intenta de nuevo.",
            icon: "error",
            confirmButtonText: "Aceptar",
        });

        estado.textContent = "Error al guardar el cliente ❌";
    }
}

btnGuardar.addEventListener("click", guardarCliente);
btnLimpiar.addEventListener("click", clearInputs);

function clearInputs() {
    document.getElementById("dpiClientes").value = "";
    document.getElementById("nitClientes").value = "";
    document.getElementById("clienteSelect").innerHTML =
        '<option value="">Seleccionar...</option>'; // Limpiar el select
}

// FUNCIONES PARA FILTRAR LA BUSQUEDA DE PRODUCTOS
// Función para obtener las subcategorías de la categoría seleccionada
async function obtenerSubcategorias(categoriaId) {
    const subcategoriaSelect = document.getElementById("subcategoria");

    if (!subcategoriaSelect) return;

    try {
        const response = await fetch(
            `/api/ventas/subcategorias/${categoriaId}`
        );
        const data = await response.json();
        console.log(data);

        subcategoriaSelect.innerHTML =
            '<option value="">Seleccionar subcategoría...</option>';
        subcategoriaSelect.disabled = false;
        // Llenar el select con las subcategorías
        data.forEach((subcategoria) => {
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
        marcaSelect.innerHTML =
            '<option value="">Seleccionar marca...</option>';
        marcaSelect.disabled = false;
        // Llenar el select con las marcas
        data.forEach((marca) => {
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
        modeloSelect.innerHTML =
            '<option value="">Seleccionar modelo...</option>';
        modeloSelect.disabled = false;
        // Llenar el select con los modelos
        data.forEach((modelo) => {
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
        calibreSelect.innerHTML =
            '<option value="">Seleccionar calibre...</option>';
        calibreSelect.disabled = false;
        // Llenar el select con los calibres
        data.forEach((calibre) => {
            calibreSelect.innerHTML += `<option value="${calibre.calibre_id}">${calibre.calibre_nombre}</option>`;
        });
    } catch (error) {
        console.error("Error:", error);
        alert("Error al cargar los calibres");
    }
}

async function buscarProductos() {
    const categoria_id = document.getElementById("categoria").value;
    const subcategoria_id = document.getElementById("subcategoria").value;
    const marca_id = document.getElementById("marca").value;
    const modelo_id = document.getElementById("modelo").value;
    const calibre_id = document.getElementById("calibre").value;

    const params = new URLSearchParams();
    if (categoria_id) params.append("categoria_id", categoria_id);
    if (subcategoria_id) params.append("subcategoria_id", subcategoria_id);
    if (marca_id) params.append("marca_id", marca_id);
    if (modelo_id) params.append("modelo_id", modelo_id);
    if (calibre_id) params.append("calibre_id", calibre_id);

    try {
        const response = await fetch(
            `/api/ventas/buscar-productos?${params.toString()}`
        );
        const productos = await response.json();
        console.log(productos);

        mostrarProductos(productos);
    } catch (error) {
        console.error("Error:", error);
    }
}

let productosGlobales = [];
let resultadosBusquedaData = [];
function mostrarProductos(productosData) {
    productosGlobales = productosData;

    const grid = document.getElementById("gridProductos");
    const contador = document.getElementById("contadorResultados");

    // Actualizar contador
    contador.textContent = `Mostrando ${productosData.length} producto${
        productosData.length === 1 ? "" : "s"
    }`;

    if (productosData.length === 0) {
        grid.innerHTML = `
      <div class="col-span-full text-center py-12 text-gray-500">
        <i class="fas fa-search text-4xl mb-4 opacity-30"></i>
        <p>No se encontraron productos</p>
      </div>`;
        return;
    }

    grid.innerHTML = productosData
        .map((producto) => {
            const stock = Number(producto.stock_cantidad_total ?? 0);
            const necesitaStock = Number(producto.producto_requiere_stock ?? 1) === 1;
            
            // Manejo de imagen con fallback
            const imagenSrc = producto.foto_principal;
            const iniciales = producto.producto_nombre.substring(0, 2).toUpperCase();

            // Badge de stock por color - SOLO si el producto necesita stock
            let stockBadgeHtml = "";
            if (necesitaStock) {
                if (stock > 4) {
                    stockBadgeHtml = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Stock: ${stock}</span>`;
                } else if (stock > 0) {
                    stockBadgeHtml = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Stock: ${stock}</span>`;
                } else {
                    stockBadgeHtml = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Sin stock</span>`;
                }
            } else {
                // Badge para productos que NO necesitan stock (servicios, documentación, etc.)
                stockBadgeHtml = `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-infinity mr-1"></i>Disponible
                </span>`;
            }

            // Determinar si el botón debe estar deshabilitado
            const sinStock = necesitaStock && stock <= 0;
            const botonClase = sinStock 
                ? "w-full bg-gray-400 text-white py-2 px-4 rounded-lg cursor-not-allowed text-sm font-medium flex items-center justify-center opacity-60"
                : "w-full bg-gray-900 text-white py-2 px-4 rounded-lg hover:bg-gray-800 transition-colors duration-200 text-sm font-medium flex items-center justify-center";

            return `
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
          <div class="relative h-48 bg-gray-100">
            ${imagenSrc ? 
                `<img src="${imagenSrc}" 
                      alt="${producto.producto_nombre}"
                      class="w-full h-full object-cover"
                      onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                 <div class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-3xl" style="display:none;">
                    ${iniciales}
                 </div>` 
                :
                `<img src="images/standar.webp"
                      alt="${producto.producto_nombre}"
                      class="w-full h-full object-cover"
                      onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                 <div class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-3xl" style="display:none;">
                    ${iniciales}
                 </div>`
            }
            <!-- Badge de stock -->
            <div class="absolute top-2 right-2">
              ${stockBadgeHtml}
            </div>
          </div>
          
          <div class="p-4">
            <div class="mb-2">
              <div class="text-lg font-bold text-green-600">Q${parseFloat(
                  producto.precio_venta
              ).toFixed(2)}</div>
              ${
                  producto.precio_venta_empresa
                      ? `
                <div class="text-sm text-blue-600">Precio especial: Q${parseFloat(
                    producto.precio_venta_empresa
                ).toFixed(2)}</div>
              `
                      : ""
              }
            </div>
            
            <div class="text-xs text-blue-600 font-medium mb-1">
              ${producto.marca_descripcion}
            </div>
            
            <h3 class="font-semibold text-gray-900 text-sm mb-2 line-clamp-2">
              ${producto.producto_nombre}
            </h3>
            
            <div class="text-xs text-gray-600 mb-3">
              <div>${producto.modelo_descripcion || ""} ${
                producto.calibre_nombre ? "- " + producto.calibre_nombre : ""
            }</div>
            </div>
            
            <button type="button"
                    data-action="agregar"
                    data-id="${producto.producto_id}"
                    ${sinStock ? 'disabled' : ''}
                    class="${botonClase}">
              <i class="fas fa-shopping-cart mr-2"></i>
              ${sinStock ? 'Sin stock' : 'Agregar'}
            </button>
          </div>
        </div>`;
        })
        .join("");
}

// 🎯 FUNCIÓN AUXILIAR: Renderizar card individual (opcional)
function renderProductoCard(producto) {
    const stock = producto.stock_cantidad_disponible || 0;
    const minimo = producto.producto_stock_minimo || 0;
    
    let stockClass = 'bg-green-100 text-green-800';
    let stockText = 'En stock';
    let stockIcon = 'fa-check-circle';
    
    if (stock <= 0) {
        stockClass = 'bg-red-100 text-red-800';
        stockText = 'Agotado';
        stockIcon = 'fa-times-circle';
    } else if (stock <= minimo) {
        stockClass = 'bg-yellow-100 text-yellow-800';
        stockText = 'Stock bajo';
        stockIcon = 'fa-exclamation-triangle';
    }

    // Determinar imagen a mostrar
    const imagenSrc = producto.foto_principal;
    const iniciales = producto.producto_nombre.substring(0, 2).toUpperCase();

    return `
        <div class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 border-b border-gray-200 dark:border-gray-600">
            <!-- Foto o Avatar -->
            <div class="flex-shrink-0">
                ${imagenSrc ? 
                    `<img src="${imagenSrc}" 
                          alt="${producto.producto_nombre}"
                          class="w-20 h-20 rounded-full object-cover border-2 border-blue-200"
                          onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                     <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium text-sm" style="display:none;">
                        ${iniciales}
                     </div>` 
                    :
                    `<div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium text-sm">
                        ${iniciales}
                     </div>`
                }
            </div>
            
            <!-- Información del producto -->
            <div class="flex-grow">
                <h4 class="font-medium text-gray-900 dark:text-white">${producto.producto_nombre}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">${producto.marca_nombre || 'N/A'}</p>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${stockClass} mt-1">
                    <i class="fas ${stockIcon} mr-1"></i>${stockText}
                </span>
            </div>
            
            <!-- Stock -->
            <div class="text-right">
                <p class="text-lg font-bold text-gray-900 dark:text-white">${stock}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">unidades</p>
            </div>
        </div>
    `;
}

const grid = document.getElementById("gridProductos");

// EVENTO PARA AGREGAR AL CARRITO EL PRODUCTO SELECCIONADO
grid.addEventListener("click", (e) => {
    const btn = e.target.closest('[data-action="agregar"]');
    if (!btn) return;
    const id = btn.dataset.id;
    agregarAlCarrito(id);
});

// Event listeners para los cambios en los selects
document.getElementById("categoria").addEventListener("change", function () {
    const categoriaId = this.value;
    limpiarSelectsPosteriores(["subcategoria", "marca", "modelo", "calibre"]);
    if (categoriaId) {
        obtenerSubcategorias(categoriaId);
        buscarProductos();
    }
});

document.getElementById("subcategoria").addEventListener("change", function () {
    const subcategoriaId = this.value;
    limpiarSelectsPosteriores(["marca", "modelo", "calibre"]);
    if (subcategoriaId) {
        obtenerMarcas(subcategoriaId);
        buscarProductos();
    }
});

document.getElementById("marca").addEventListener("change", function () {
    const marcaId = this.value;
    limpiarSelectsPosteriores(["modelo", "calibre"]);
    if (marcaId) {
        obtenerModelos(marcaId);
        buscarProductos();
    }
});

document.getElementById("modelo").addEventListener("change", function () {
    const modeloId = this.value;
    limpiarSelectsPosteriores(["calibre"]);
    if (modeloId) {
        obtenerCalibres(modeloId);
        buscarProductos();
    }
});

document.getElementById("calibre").addEventListener("change", function () {
    obtenerProductos();
    buscarProductos();
});

// Función para limpiar los selects posteriores
function limpiarSelectsPosteriores(selects) {
    selects.forEach((id) => {
        const select = document.getElementById(id);
        select.innerHTML = '<option value="">Seleccionar...</option>';
        select.disabled = true;
    });
}

let timeoutBusqueda;

document
    .getElementById("busquedaProductos")
    .addEventListener("input", function (e) {
        const busqueda = e.target.value.trim();

        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(() => {
            buscarProductosTexto(busqueda);
        }, 200);
    });

async function buscarProductosTexto(busqueda) {
    const resultados = document.getElementById("resultadosBusqueda");

    if (busqueda.length < 2) {
        resultados.classList.add("hidden");
        return;
    }
    try {
        const response = await fetch(
            `/api/ventas/buscar-productos?busqueda=${encodeURIComponent(
                busqueda
            )}`
        );
        const productos = await response.json();

        mostrarResultados(productos);
    } catch (error) {
        console.error("Error:", error);
        resultados.innerHTML =
            '<div class="p-4 text-red-500">Error al buscar productos</div>';
        resultados.classList.remove("hidden");
    }
}

function mostrarResultados(productos) {
    resultadosBusquedaData = productos; // guarda la data reciente
    const resultados = document.getElementById("resultadosBusqueda");

    if (productos.length === 0) {
        resultados.innerHTML =
            '<div class="p-4 text-gray-500">No se encontraron productos</div>';
    } else {
        resultados.innerHTML = productos
            .map(
                (p) => `
      <div class="p-3 hover:bg-gray-50 cursor-pointer border-b last:border-b-0"
           data-action="select-product"
           data-id="${String(p.producto_id)}">
        <div class="font-semibold">${p.producto_nombre}</div>
        <div class="text-sm text-gray-600">${p.marca_descripcion} - ${
                    p.modelo_descripcion || ""
                }</div>
        <div class="text-sm text-green-600 font-medium">Q${parseFloat(
            p.precio_venta
        ).toFixed(2)}</div>
      </div>
    `
            )
            .join("");
    }
    resultados.classList.remove("hidden");
}

// listener único
(function initResultadosDelegation() {
    const resultados = document.getElementById("resultadosBusqueda");
    if (!resultados) return;

    resultados.addEventListener("click", (e) => {
        const item = e.target.closest('[data-action="select-product"]');
        if (!item) return;

        const id = item.dataset.id;
        seleccionarProducto2(id);
    });
})();

function seleccionarProducto2(producto_id) {
    const id = String(producto_id);
    let producto = (productosGlobales || []).find(
        (p) => String(p.producto_id) === id
    );
    if (!producto) {
        producto = (resultadosBusquedaData || []).find(
            (p) => String(p.producto_id) === id
        );
    }
    if (!producto) {
        mostrarNotificacion(
            "No se pudo cargar el producto seleccionado",
            "warning"
        );
        return;
    }

    mostrarProductos([producto]); // pinta solo ese
    document.getElementById("resultadosBusqueda")?.classList.add("hidden");
    document
        .getElementById("gridProductos")
        ?.scrollIntoView({ behavior: "smooth", block: "start" });
}

let carritoProductos = [];

// Establecer fecha actual
const ahora = new Date();
const fechaHoraLocal = new Date(ahora.getTime() - (ahora.getTimezoneOffset() * 60000))
    .toISOString()
    .slice(0, 16); // Formato: YYYY-MM-DDTHH:MM

document.getElementById("fechaVenta").value = fechaHoraLocal;

// Eventos para abrir/cerrar carrito
document
    .getElementById("btnAbrirCarrito")
    .addEventListener("click", abrirCarrito);
document
    .getElementById("btnCerrarCarrito")
    .addEventListener("click", cerrarCarrito);
document
    .getElementById("overlayCarrito")
    .addEventListener("click", cerrarCarrito);

// Evento para descuento
document
    .getElementById("descuentoModal")
    .addEventListener("input", calcularTotales);

function abrirCarrito() {
    const modal = document.getElementById("modalCarrito");
    const panel = document.getElementById("panelCarrito");

    modal.classList.remove("hidden");
    setTimeout(() => {
        panel.style.width = "45%";
    }, 10);
}

function cerrarCarrito() {
    const modal = document.getElementById("modalCarrito");
    const panel = document.getElementById("panelCarrito");

    panel.style.width = "0";
    setTimeout(() => {
        modal.classList.add("hidden");
    }, 300);
}

function agregarAlCarrito(producto_id) {
    const id = String(producto_id);
    const producto = productosGlobales.find(
        (p) => String(p.producto_id) === id
    );
    if (producto) agregarProductoAlCarrito(producto);
    else console.error("Producto no encontrado:", producto_id);
}

function agregarProductoAlCarrito(producto) {
    const id = String(producto.producto_id);
    const existente = carritoProductos.find(
        (p) => String(p.producto_id) === id
    );

    // Normaliza datos del producto entrante
    const stockProducto = Number(producto.stock_cantidad_total ?? 0);
    const necesitaStock = Number(producto.producto_requiere_stock ?? 1) === 1;
    const requiereSerie = Number(producto.producto_requiere_serie ?? 0) === 1;
    const seriesDisp = Array.isArray(producto.series_disponibles)
        ? producto.series_disponibles
        : [];

    // 🔹 Lotes del producto (tal cual vienen del backend)
    const lotesDelProducto = Array.isArray(producto.lotes)
        ? producto.lotes
        : [];
    const cantLotes = Number(
        producto.cantidad_lotes ?? lotesDelProducto.length ?? 0
    );
    const lotesSuma = Number(producto.lotes_cantidad_total ?? 0);

    // 🔹 PRECIOS - Extraer correctamente
    const precioVenta = Number(producto.precio_venta ?? 0);
    const precioVentaEmpresa = Number(producto.precio_venta_empresa ?? 0);

    if (existente) {
        // Solo validar stock si el producto lo necesita
        if (necesitaStock) {
            const stockItem = Number(
                existente.stock ??
                    existente.stock_cantidad_total ??
                    stockProducto ??
                    0
            );
            if (existente.cantidad >= stockItem) {
                mostrarNotificacion?.(
                    `Stock máximo disponible: ${stockItem}`,
                    "warning"
                );
                return;
            }
        }

        // (Opcional) si requiere serie, evitar superar # series disponibles
        if (requiereSerie) {
            const maxSeries = Array.isArray(existente.series_disponibles)
                ? existente.series_disponibles.length
                : seriesDisp.length;
            if (existente.cantidad + 1 > maxSeries) {
                mostrarNotificacion?.(
                    `Solo hay ${maxSeries} serie(s) disponibles para este producto.`,
                    "warning"
                );
                return;
            }
        }

        // 🔸 Si el ítem existente no tenía lotes y el producto sí, los acoplamos
        if (
            (!Array.isArray(existente.lotes) || existente.lotes.length === 0) &&
            lotesDelProducto.length > 0
        ) {
            existente.lotes = lotesDelProducto;
            existente.cantidad_lotes = cantLotes;
            existente.lotes_cantidad_total = lotesSuma;
            existente.lotesSeleccionados = existente.lotesSeleccionados ?? [];
        }

        existente.cantidad += 1;
    } else {
        // Solo validar stock si el producto lo necesita
        if (necesitaStock && stockProducto <= 0) {
            mostrarNotificacion?.(
                "Sin stock disponible para este producto.",
                "warning"
            );
            return;
        }

        // 🔹 Precio inicial siempre es el precio_venta (normal)
        let precioInicial = precioVenta;
        let precioActivo = 'normal';

        carritoProductos.push({
            // Identidad / visual
            producto_id: producto.producto_id,
            nombre: producto.producto_nombre,
            marca: producto.marca_descripcion,
            imagen: producto.foto_url
                ? `/storage/productos/${producto.foto_url}`
                : "/storage/productos/standar.webp",

            // 🔹 PRECIOS CORREGIDOS - mantener valores originales
            precio_venta: precioVenta,                    // Precio normal
            precio_venta_empresa: precioVentaEmpresa,     // Precio especial
            precio: precioInicial,                         // Precio que se está usando
            precio_activo: precioActivo,                   // 'normal' o 'empresa'

            // Cantidad y stock
            cantidad: 1,
            stock: stockProducto,
            stock_cantidad_total: stockProducto,
            producto_requiere_stock: producto.producto_requiere_stock ?? 1,

            // Series
            producto_requiere_serie: producto.producto_requiere_serie ?? 0,
            series_disponibles: seriesDisp,
            seriesSeleccionadas: [],

            // 🔹 LOTES
            lotes: lotesDelProducto,
            cantidad_lotes: cantLotes,
            lotes_cantidad_total: lotesSuma,
            lotesSeleccionados: [],
        });
    }

    actualizarVistaCarrito();
    actualizarContadorCarrito();
    mostrarNotificacion?.(
        `${producto.producto_nombre} agregado al carrito`,
        "success"
    );
}

function cambiarCantidad(producto_id, cambio) {
    const id = String(producto_id);
    const p = carritoProductos.find((x) => String(x.producto_id) === id);
    if (!p) return;

    const stock = Number(p.stock_cantidad_total ?? 0);
    const necesitaStock = Number(p.producto_requiere_stock ?? 1) === 1;
    const nueva = (p.cantidad || 0) + cambio;

    if (nueva <= 0) return eliminarProducto(id);
    
    // Solo validar stock si el producto lo necesita
    if (necesitaStock && nueva > stock) {
        return mostrarNotificacion?.(
            `Stock máximo disponible: ${stock}`,
            "warning"
        );
    }

    p.cantidad = nueva;

    // Si requiere serie y ahora la cantidad supera el número de series seleccionadas,
    // invitamos a completar las series (no bloquea, pero guía).
    const requiereSerie = (p.requiere_serie ?? p.producto_requiere_serie) == 1;
    if (requiereSerie) {
        p.seriesSeleccionadas = Array.isArray(p.seriesSeleccionadas)
            ? p.seriesSeleccionadas
            : [];
        if (p.seriesSeleccionadas.length > p.cantidad) {
            // Si bajaron la cantidad, recortamos
            p.seriesSeleccionadas = p.seriesSeleccionadas.slice(0, p.cantidad);
        } else if (p.seriesSeleccionadas.length < p.cantidad) {
            // Sugerimos completar
            mostrarNotificacion?.(
                `Selecciona ${
                    p.cantidad - p.seriesSeleccionadas.length
                } serie(s) para completar`,
                "info"
            );
        }
    }

    actualizarVistaCarrito();
}

function eliminarProducto(producto_id) {
    const id = String(producto_id);
    const p = carritoProductos.find((x) => String(x.producto_id) === id);

    carritoProductos = carritoProductos.filter(
        (x) => String(x.producto_id) !== id
    );

    if (p) mostrarNotificacion(`${p.nombre} eliminado del carrito`, "info");

    actualizarVistaCarrito();
    actualizarContadorCarrito?.();
}

function actualizarVistaCarrito() {
    const container = document.getElementById("productosCarrito");
    const carritoVacio = document.getElementById("carritoVacio");
    if (!container) return;

    if (!Array.isArray(carritoProductos)) carritoProductos = [];
    console.log(carritoProductos);

    // 🔧 Normalizar carrito en caliente
    carritoProductos = carritoProductos.map((p) => {
        if (!p || typeof p !== "object") return p;

        // nombre / marca
        p.nombre = p.nombre ?? p.producto_nombre ?? p.descripcion ?? "Producto";
        p.marca = p.marca ?? p.marca_descripcion ?? "";

        // imagen
        p.imagen =
            p.imagen ??
            (p.foto_url
                ? `/storage/productos/${p.foto_url}`
                : "images/standar.webp");

        // precios - NO sobrescribir si ya existen
        if (!p.precio_venta) p.precio_venta = Number(p.precio ?? 0);
        if (!p.precio_venta_empresa) p.precio_venta_empresa = Number(p.precio_venta_empresa ?? 0);
        
        // Convertir a números
        p.precio_venta = Number(p.precio_venta);
        p.precio_venta_empresa = Number(p.precio_venta_empresa);
        
        // Precio activo (el que se está usando)
        if (!p.precio_activo) {
            p.precio_activo = 'normal';
        }
        
        // Precio final según selección
        p.precio = p.precio_activo === 'empresa' ? p.precio_venta_empresa : p.precio_venta;

        // cantidad
        p.cantidad = Number(p.cantidad ?? 1);

        // stock
        p.stock_cantidad_total = Number(
            p.stock_cantidad_total ?? p.stockDisponible ?? p.stock ?? 0
        );

        // producto necesita stock (1=sí, 0=no)
        p.producto_requiere_stock = Number(p.producto_requiere_stock ?? 1);

        // requiere serie
        p.producto_requiere_serie = Number(
            p.producto_requiere_serie ?? p.requiere_serie ?? 0
        );

        // series disponibles
        if (!Array.isArray(p.series_disponibles)) {
            p.series_disponibles = Array.isArray(p.seriesDisponibles)
                ? p.seriesDisponibles
                : [];
        }

        // series seleccionadas
        if (!Array.isArray(p.seriesSeleccionadas)) {
            p.seriesSeleccionadas = [];
        }

        // LOTES
        if (!Array.isArray(p.lotes)) p.lotes = [];
        if (!Array.isArray(p.lotesSeleccionados)) p.lotesSeleccionados = [];

        return p;
    });

    if (carritoProductos.length === 0) {
        carritoVacio?.classList.remove("hidden");
        container.innerHTML = "";
        calcularTotales?.();
        return;
    }

    carritoVacio?.classList.add("hidden");

    container.innerHTML = carritoProductos
        .map((p) => {
            const stock = Number(p.stock_cantidad_total ?? 0);
            const necesitaStock = Number(p.producto_requiere_stock ?? 1) === 1;
            const requiereSerie = Number(p.producto_requiere_serie ?? 0) === 1;
            const seriesSel = Array.isArray(p.seriesSeleccionadas)
                ? p.seriesSeleccionadas
                : [];
            const seriesDisponibles = Array.isArray(p.series_disponibles)
                ? p.series_disponibles
                : [];
            const tieneSuficientesSeries =
                !requiereSerie || seriesSel.length === Number(p.cantidad || 0);

            // Badge de serie - SOLO si necesita stock
            const badgeSerie = (necesitaStock && requiereSerie)
                ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                       ${
                           tieneSuficientesSeries
                               ? "bg-emerald-100 text-emerald-700"
                               : "bg-amber-100 text-amber-700"
                       }">
                    <i class="fas fa-barcode mr-1"></i>
                    Serie: ${seriesSel.length}/${p.cantidad}
               </span>`
                : "";

            const hasLotes = Array.isArray(p.lotes) && p.lotes.length > 0;
            const asignadoLotes = Array.isArray(p.lotesSeleccionados)
                ? p.lotesSeleccionados.reduce(
                      (acc, it) => acc + Number(it.cantidad || 0),
                      0
                  )
                : 0;

            // Badge de lotes - SOLO si necesita stock
            const badgeLotes = (necesitaStock && hasLotes)
                ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
         ${
             asignadoLotes === Number(p.cantidad || 0)
                 ? "bg-emerald-100 text-emerald-700"
                 : "bg-amber-100 text-amber-700"
         }">
       <i class="fas fa-layer-group mr-1"></i>
       Lotes: ${asignadoLotes}/${p.cantidad}
     </span>`
                : "";

            // Selector de precio (solo si hay precio empresa)
            const tienePrecioEmpresa = p.precio_venta_empresa > 0 && p.precio_venta_empresa !== p.precio_venta;
            const selectorPrecio = tienePrecioEmpresa
                ? `
                <div class="mt-1.5 flex items-center gap-1.5 text-[10px]">
                    <span class="text-gray-500 font-medium">Precio:</span>
                    <button type="button"
                            data-action="cambiar-precio"
                            data-id="${String(p.producto_id)}"
                            data-tipo="normal"
                            class="px-2 py-1 rounded-md font-medium transition-all duration-200 border
                            ${p.precio_activo === 'normal' 
                                ? 'bg-blue-600 text-white shadow-sm border-blue-600' 
                                : 'bg-gray-200 text-gray-500 border-gray-300 hover:bg-gray-300'}">
                        Venta (Q${p.precio_venta.toFixed(2)})
                    </button>
                    <button type="button"
                            data-action="cambiar-precio"
                            data-id="${String(p.producto_id)}"
                            data-tipo="empresa"
                            class="px-2 py-1 rounded-md font-medium transition-all duration-200 border
                            ${p.precio_activo === 'empresa' 
                                ? 'bg-blue-600 text-white shadow-sm border-blue-600' 
                                : 'bg-gray-200 text-gray-500 border-gray-300 hover:bg-gray-300'}">
                        Especial (Q${p.precio_venta_empresa.toFixed(2)})
                    </button>
                </div>
                `
                : "";

            return `
        <div class="group bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 flex items-start gap-4">
          <!-- Imagen -->
          <div class="relative w-20 h-20 flex-shrink-0">
            <img src="${p.imagen}"
                 alt="${p.nombre}"
                 class="w-full h-full object-cover rounded-lg border border-gray-200 group-hover:scale-105 transition-transform duration-300">
            <div class="absolute -top-2 -right-2 bg-gradient-to-br from-blue-500 to-blue-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                ${p.cantidad}x
            </div>
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start mb-2">
              <div class="flex-1 min-w-0 mr-2">
                <h4 class="font-semibold text-sm text-gray-900 truncate">${p.nombre}</h4>
                ${p.marca ? `<p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                    <i class="fas fa-industry text-[9px]"></i>
                    ${p.marca}
                </p>` : ''}
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                  ${necesitaStock ? `
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 border border-gray-300">
                    <i class="fas fa-box mr-1"></i>
                    Stock: ${stock}
                  </span>
                  ` : ''}
                  ${badgeSerie}
                  ${badgeLotes}
                </div>
              </div>
              <button type="button"
                      data-action="eliminar"
                      data-id="${String(p.producto_id)}"
                      class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition-all duration-200">
                <i class="fas fa-trash-alt text-xs"></i>
              </button>
            </div>

            <!-- Controles de cantidad -->
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center gap-1 bg-gray-50 rounded-lg p-1 border border-gray-200">
                    <button type="button"
                            data-action="disminuir"
                            data-id="${String(p.producto_id)}"
                            class="bg-white hover:bg-gray-100 text-gray-700 w-7 h-7 rounded-md flex items-center justify-center text-sm shadow-sm border border-gray-200 transition-all duration-200 hover:scale-105 active:scale-95">
                      <i class="fas fa-minus text-xs"></i>
                    </button>

                    <span class="font-bold text-sm w-10 text-center text-gray-900">${p.cantidad}</span>

                    <button type="button"
                            data-action="aumentar"
                            data-id="${String(p.producto_id)}"
                            class="bg-blue-600 hover:bg-blue-700 text-white w-7 h-7 rounded-md flex items-center justify-center text-sm shadow-sm transition-all duration-200 hover:scale-105 active:scale-95">
                      <i class="fas fa-plus text-xs font-bold"></i>
                    </button>
                </div>

                ${
                    (necesitaStock && requiereSerie && seriesDisponibles.length > 0)
                        ? `
                  <button type="button"
                          data-action="series"
                          data-id="${String(p.producto_id)}"
                          class="px-3 py-1.5 rounded-lg text-xs font-medium border-2 border-blue-200 bg-white hover:bg-blue-50 hover:border-blue-300 text-blue-700 transition-all duration-200 flex items-center gap-1.5">
                    <i class="fas fa-barcode"></i>
                    Series
                  </button>
                `
                        : ``
                }

                ${
                    (necesitaStock && hasLotes)
                        ? `
                    <button type="button"
                            data-action="lotes"
                            data-id="${String(p.producto_id)}"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium border-2 border-purple-200 bg-white hover:bg-purple-50 hover:border-purple-300 text-purple-700 transition-all duration-200 flex items-center gap-1.5">
                        <i class="fas fa-layer-group"></i>
                        Lotes
                    </button>
                    `
                        : ``
                }
            </div>

            <!-- Selector de precio -->
            ${selectorPrecio}

            <!-- Precio total -->
            <div class="mt-2 flex items-center justify-between bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-lg p-2">
              <div class="text-xs text-gray-600">
                <span class="font-medium">Precio unitario:</span>
                <span class="ml-1 font-semibold text-emerald-700">Q${p.precio.toFixed(2)}</span>
                ${p.precio_activo === 'empresa' ? '<span class="ml-1 text-[10px] text-purple-600 font-medium">(Especial)</span>' : ''}
              </div>
              <div class="text-right">
                <div class="text-xs text-gray-500 font-medium">Total</div>
                <div class="font-bold text-emerald-600 text-lg">
                  Q${(p.precio * p.cantidad).toFixed(2)}
                </div>
              </div>
            </div>
          </div>
        </div>`;
        })
        .join("");

    // Event listener para cambio de precio
    container.querySelectorAll('[data-action="cambiar-precio"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.dataset.id;
            const tipo = e.currentTarget.dataset.tipo;
            
            const producto = carritoProductos.find(p => String(p.producto_id) === id);
            if (producto) {
                producto.precio_activo = tipo;
                producto.precio = tipo === 'empresa' ? producto.precio_venta_empresa : producto.precio_venta;
                actualizarVistaCarrito();
            }
        });
    });

    calcularTotales?.();
}

(function initCarritoDelegation() {
    const container = document.getElementById("productosCarrito");
    if (!container) return;

    container.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-action]");
        if (!btn) return;

        const action = btn.dataset.action;
        const id = btn.dataset.id;
        if (!id) return;

        switch (action) {
            case "eliminar":
                eliminarProducto(id);
                break;
            case "disminuir":
                cambiarCantidad(id, -1);
                break;
            case "aumentar":
                cambiarCantidad(id, 1);
                break;
            case "series":
                seleccionarSeries(id); // <<< NUEVO
                break;
            case "lotes":
                seleccionarLotes(id);
                break;
        }
    });
})();

function calcularTotales() {
    const subtotal = carritoProductos.reduce(
        (sum, p) => sum + p.precio * p.cantidad,
        0
    );
    const descuento =
        parseFloat(document.getElementById("descuentoModal").value) || 0;
    const descuentoMonto = subtotal * (descuento / 100);
    const total = subtotal - descuentoMonto;

    document.getElementById("subtotalModal").textContent = `Q${subtotal.toFixed(
        2
    )}`;
    document.getElementById("totalModal").textContent = `Q${total.toFixed(2)}`;
}

function actualizarContadorCarrito() {
    const contador = carritoProductos.reduce((sum, p) => sum + p.cantidad, 0);
    const badge = document.getElementById("contadorCarrito");

    badge.textContent = contador;

    // Animar el badge cuando se agrega algo
    if (contador > 0) {
        badge.classList.remove("hidden");
        badge.style.transform = "scale(1.2)";
        setTimeout(() => {
            badge.style.transform = "scale(1)";
        }, 200);
    } else {
        badge.classList.add("hidden");
    }
}

function mostrarNotificacion(mensaje, tipo = "success") {
    const notificacion = document.createElement("div");
    notificacion.className = `
        fixed top-4 left-1/2 
        -translate-x-1/2 -translate-y-5
        opacity-0
        px-6 py-3 rounded-lg shadow-lg z-50 
        transition-all duration-300 transform
        ${
            tipo === "success"
                ? "bg-green-500 text-white"
                : tipo === "warning"
                ? "bg-yellow-500 text-white"
                : tipo === "info"
                ? "bg-blue-500 text-white"
                : "bg-red-500 text-white"
        }
    `;

    notificacion.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${
                tipo === "success"
                    ? "check"
                    : tipo === "warning"
                    ? "exclamation-triangle"
                    : "info-circle"
            } mr-2"></i>
            ${mensaje}
        </div>
    `;

    document.body.appendChild(notificacion);

    // Mostrar con animación
    setTimeout(() => {
        notificacion.classList.remove("-translate-y-5", "opacity-0");
        notificacion.classList.add("translate-y-0", "opacity-100");
    }, 10);

    // Ocultar después de 3 segundos
    setTimeout(() => {
        notificacion.classList.remove("translate-y-0", "opacity-100");
        notificacion.classList.add("-translate-y-5", "opacity-0");
        setTimeout(() => {
            document.body.removeChild(notificacion);
        }, 300);
    }, 3000);
}

// Inicializar contador al cargar la página
document.addEventListener("DOMContentLoaded", function () {
    actualizarContadorCarrito();
    calcularTotales();
});

async function seleccionarSeries(producto_id) {
    const id = String(producto_id);
    const p = carritoProductos.find((x) => String(x.producto_id) === id);
    if (!p) return;

    if ((p.requiere_serie ?? p.producto_requiere_serie) != 1) {
        return mostrarNotificacion?.(
            "Este producto no requiere series.",
            "info"
        );
    }

    const cantidad = Number(p.cantidad || 0);
    const disponibles = Array.isArray(p.series_disponibles)
        ? p.series_disponibles
        : [];
    const yaSel = new Set(
        Array.isArray(p.seriesSeleccionadas) ? p.seriesSeleccionadas : []
    );

    // Armar HTML de checkboxes
    const opciones = disponibles
        .map((s) => {
            const serie = s.serie_numero_serie ?? s.numero ?? String(s);
            const checked = yaSel.has(serie) ? "checked" : "";
            return `
          <label class="flex items-center gap-2 text-sm py-1">
            <input type="checkbox" class="serie-opt" value="${serie}" ${checked}>
            <span class="font-mono">${serie}</span>
          </label>`;
        })
        .join("");

    const { value: ok } = await Swal.fire({
        title: `Selecciona ${cantidad} serie(s)`,
        html: `
          <div class="text-left max-h-64 overflow-auto px-1">
            ${
                opciones ||
                '<div class="text-sm text-gray-500">Sin series disponibles.</div>'
            }
          </div>
          <div class="mt-3 text-xs text-gray-600">
            Seleccionadas: <span id="selCount">${
                yaSel.size
            }</span> / ${cantidad}
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Guardar",
        cancelButtonText: "Cancelar",
        didOpen: () => {
            const checks =
                Swal.getHtmlContainer().querySelectorAll(".serie-opt");
            const selCount = Swal.getHtmlContainer().querySelector("#selCount");

            checks.forEach((chk) => {
                chk.addEventListener("change", () => {
                    const current = Array.from(checks).filter(
                        (c) => c.checked
                    ).length;
                    selCount.textContent = current;

                    // No permitir seleccionar más que la cantidad requerida (bloqueo soft)
                    if (current > cantidad) {
                        chk.checked = false;
                        selCount.textContent = Array.from(checks).filter(
                            (c) => c.checked
                        ).length;
                        mostrarNotificacion?.(
                            `Solo puedes seleccionar ${cantidad} serie(s).`,
                            "warning"
                        );
                    }
                });
            });
        },
        preConfirm: () => {
            const checks =
                Swal.getHtmlContainer().querySelectorAll(".serie-opt");
            const seleccionadas = Array.from(checks)
                .filter((c) => c.checked)
                .map((c) => c.value);

            if (seleccionadas.length !== cantidad) {
                Swal.showValidationMessage(
                    `Debes seleccionar exactamente ${cantidad} serie(s).`
                );
                return false;
            }
            return seleccionadas;
        },
    });

    if (ok) {
        p.seriesSeleccionadas = ok; // guardamos exactamente cantidad series
        actualizarVistaCarrito();
        mostrarNotificacion?.("Series actualizadas.", "success");
    }
}

async function seleccionarLotes(producto_id) {
    const id = String(producto_id);
    const p = carritoProductos.find((x) => String(x.producto_id) === id);
    if (!p) return;

    const cantidadNecesaria = Number(p.cantidad || 0);
    if (cantidadNecesaria < 1) {
        return mostrarNotificacion?.(
            "La cantidad del producto debe ser al menos 1.",
            "info"
        );
    }

    const lotes = Array.isArray(p.lotes) ? p.lotes : [];
    if (lotes.length === 0) {
        return mostrarNotificacion?.(
            "Este producto no tiene lotes disponibles.",
            "info"
        );
    }

    // Prefill con lo que ya seleccionó
    const preSel = new Map(
        (p.lotesSeleccionados || []).map((it) => [
            Number(it.lote_id),
            Number(it.cantidad),
        ])
    );

    // Construir HTML: cada lote con checkbox y input cantidad
    const opciones = lotes
        .map((l) => {
            const loteId = Number(l.lote_id ?? l.id);
            const loteCodigo = l.lote_codigo ?? `Lote ${loteId}`;
            const max = Number(l.lote_cantidad_total ?? 0);
            const pre = preSel.get(loteId) || 0;
            const checked = pre > 0 ? "checked" : "";
            const disabled = pre > 0 ? "" : "disabled";
            return `
      <div class="flex items-center justify-between gap-3 py-1">
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" class="lote-check" data-lote-id="${loteId}" data-max="${max}" ${checked}>
          <span class="font-mono">${loteCodigo}</span>
          <span class="text-xs text-gray-500">(disp: ${max})</span>
        </label>
        <input type="number" min="1" max="${max}" value="${pre || ""}" 
               class="lote-input w-20 px-2 py-1 border rounded text-sm text-right" 
               data-lote-id="${loteId}" ${disabled}>
      </div>`;
        })
        .join("");

    const { value: asignaciones } = await Swal.fire({
        title: `Seleccione ${cantidadNecesaria} unidad(es) de los lotes disponibles`,
        html: `
      <div class="text-left max-h-72 overflow-auto px-1">
        ${opciones}
      </div>
      <div class="mt-3 text-xs text-gray-600">
        Asignadas: <span id="lotSum">0</span> / ${cantidadNecesaria}
      </div>
    `,
        showCancelButton: true,
        confirmButtonText: "Guardar",
        cancelButtonText: "Cancelar",
        didOpen: () => {
            const container = Swal.getHtmlContainer();
            const checks = container.querySelectorAll(".lote-check");
            const inputs = container.querySelectorAll(".lote-input");
            const lotSum = container.querySelector("#lotSum");

            const getSum = () =>
                Array.from(inputs).reduce((acc, inp) => {
                    const v = Number(inp.value || 0);
                    return acc + (!inp.disabled && v > 0 ? v : 0);
                }, 0);

            const recalc = () => {
                lotSum.textContent = getSum();
            };

            checks.forEach((chk) => {
                const loteId = Number(chk.dataset.loteId);
                const inp = container.querySelector(
                    `.lote-input[data-lote-id="${loteId}"]`
                );
                chk.addEventListener("change", () => {
                    if (chk.checked) {
                        // Al marcar, intentamos poner 1 sin exceder el total ni el max del lote
                        const current = getSum();
                        const maxLote = Number(chk.dataset.max || 0);
                        if (current >= cantidadNecesaria) {
                            // No hay espacio para más unidades
                            chk.checked = false;
                            mostrarNotificacion?.(
                                `No puedes superar ${cantidadNecesaria} unidad(es) en total.`,
                                "warning"
                            );
                            return;
                        }
                        inp.disabled = false;
                        let val = Number(inp.value || 0);
                        if (val < 1) val = 1;
                        if (val > maxLote) val = maxLote;
                        // Cap al restante disponible
                        const restante = cantidadNecesaria - current;
                        if (val > restante) val = restante;
                        inp.value = val;
                    } else {
                        inp.value = "";
                        inp.disabled = true;
                    }
                    recalc();
                });
            });

            inputs.forEach((inp) => {
                inp.addEventListener("input", () => {
                    const max = Number(inp.getAttribute("max") || 0);
                    // suma de todos los demás inputs activos
                    const othersSum = Array.from(inputs).reduce((acc, el) => {
                        if (el === inp) return acc;
                        const v = Number(el.value || 0);
                        return acc + (!el.disabled && v > 0 ? v : 0);
                    }, 0);

                    let val = Number(inp.value || 0);
                    if (val < 1) val = 1;
                    if (val > max) val = max; // no superar cantidad del lote

                    const restante = cantidadNecesaria - othersSum;
                    if (val > restante) {
                        val = Math.max(restante, 0); // no exceder el total requerido
                    }

                    // si restante es 0, desmarcamos este lote
                    if (val <= 0) {
                        inp.value = "";
                        const loteId = inp.dataset.loteId;
                        const chk = container.querySelector(
                            `.lote-check[data-lote-id="${loteId}"]`
                        );
                        if (chk) chk.checked = false;
                        inp.disabled = true;
                    } else {
                        inp.value = val;
                    }
                    recalc();
                });
            });

            // Calcular suma inicial (prefill)
            recalc();
        },
        preConfirm: () => {
            const container = Swal.getHtmlContainer();
            const checks = container.querySelectorAll(".lote-check");
            const inputs = container.querySelectorAll(".lote-input");

            const asign = [];
            let sum = 0;

            checks.forEach((chk) => {
                const loteId = Number(chk.dataset.loteId);
                const max = Number(chk.dataset.max);
                const inp = container.querySelector(
                    `.lote-input[data-lote-id="${loteId}"]`
                );
                if (chk.checked) {
                    const cant = Number(inp.value || 0);
                    if (cant < 1) {
                        Swal.showValidationMessage(
                            "Hay lotes seleccionados sin cantidad válida."
                        );
                    }
                    if (cant > max) {
                        Swal.showValidationMessage(
                            `No puedes tomar más de ${max} del lote ${loteId}.`
                        );
                    }
                    asign.push({
                        lote_id: loteId,
                        cantidad: cant,
                        lote_codigo:
                            lotes.find((ll) => Number(ll.lote_id) === loteId)
                                ?.lote_codigo ?? "",
                    });
                    sum += cant;
                }
            });

            if (asign.length === 0) {
                Swal.showValidationMessage("Selecciona al menos un lote.");
                return false;
            }

            if (sum !== cantidadNecesaria) {
                Swal.showValidationMessage(
                    `Debes seleccionar exactamente ${cantidadNecesaria} unidad(es) de los lotes disponibles. Actualmente: ${sum}.`
                );
                return false;
            }

            return asign;
        },
    });

    if (asignaciones) {
        p.lotesSeleccionados = asignaciones; // [{lote_id, cantidad, lote_codigo}]
        actualizarVistaCarrito();
        mostrarNotificacion?.("Lotes asignados.", "success");
    }
}

// LOGICA PARA EL METODO DE PAGO

// Utils
const moneyToNumber = (s) => Number(String(s).replace(/[^\d.-]/g, "")) || 0;
const getTotalVenta = () =>
    moneyToNumber(document.getElementById("totalModal")?.textContent || 0);

// Reparto exacto a 2 decimales (distribuye el redondeo)
function repartirEnCuotas(total, n) {
    const cent = Math.round(total * 100);
    const base = Math.floor(cent / n);
    let resto = cent - base * n;
    const res = Array.from(
        { length: n },
        (_, i) => (base + (i < resto ? 1 : 0)) / 100
    );
    return res;
}

// Render de cuotas
function renderCuotas(montos) {
    const lista = document.getElementById("cuotasLista");
    lista.innerHTML = montos
        .map(
            (m, i) => `
    <div class="flex items-center justify-between gap-3">
      <div class="text-sm text-gray-700">Cuota ${i + 1}</div>
      <input type="number" step="0.01" min="0" value="${m.toFixed(2)}"
             class="cuota-input w-28 px-2 py-1 border rounded text-right text-sm" data-index="${i}">
    </div>
  `
        )
        .join("");

    // listeners de edición
    lista.querySelectorAll(".cuota-input").forEach((inp) => {
        inp.addEventListener("input", validarCuotas);
    });

    validarCuotas();
}


// Valida suma = total y habilita/deshabilita botón
function validarCuotas() {
    const errores = [];
    const total = getTotalVenta();

    // Tomar abono y limitarlo entre 0 y total, pero sin vaciar cuotas
    const abonoInp = document.getElementById("abonoInicial");
    let abono = Number(abonoInp?.value || 0);

    // Si el abono es mayor que el total, lo limitamos al total, pero no vaciamos las cuotas
    if (abono < 0) abono = 0;
    if (abono > total) abono = total;
    if (abonoInp) abonoInp.value = abono.toFixed(2);

    // Suma de cuotas
    const inputs = Array.from(
        document.querySelectorAll("#cuotasLista .cuota-input")
    );
    const sumCuotas = inputs.reduce(
        (acc, el) => acc + (Number(el.value) || 0),
        0
    );

    // Validación: abono + cuotas = total
    const sumTotal = +(sumCuotas + abono).toFixed(2);
    const diff = +(sumTotal - total).toFixed(2);

    // (opcional) mostrar saldo si tienes #saldoCuotas en el HTML
    const saldoEl = document.getElementById("saldoCuotas");
    if (saldoEl) saldoEl.textContent = `Q${(total - abono).toFixed(2)}`;

    const msg = document.getElementById("cuotasMensaje");

    if (Math.abs(diff) < 0.01) {
        msg.textContent = "OK: Abono + cuotas = total.";
        msg.className = "text-xs mt-1 text-green-600";
    } else {
        msg.textContent = `Abono + cuotas (Q${sumTotal.toFixed(
            2
        )}) debe ser igual al total (Q${total.toFixed(
            2
        )})`;
        msg.className = "text-xs mt-1 text-red-600";
        errores.push("La suma de las cuotas y el abono debe ser igual al total.");
    }

    return errores;  // Devolvemos el arreglo de errores
}

// Repartir según total y cantidad actual
function updateCuotasFromTotal() {
    const cont = document.getElementById("cuotasContainer");
    if (!cont || cont.classList.contains("hidden")) return; // solo si método 6

    const n = Math.max(
        2,
        Math.min(
            36,
            Number(document.getElementById("cuotasNumero")?.value || 2)
        )
    );
    const total = getTotalVenta();

    // Restar abono del total
    const abono = Math.min(
        total,
        Math.max(0, Number(document.getElementById("abonoInicial")?.value || 0))
    );
    const saldo = Math.max(0, total - abono);

    // (opcional) mostrar saldo si tienes #saldoCuotas en el HTML
    const saldoEl = document.getElementById("saldoCuotas");
    if (saldoEl) saldoEl.textContent = `Q${saldo.toFixed(2)}`;

    // Repartir SOLO el saldo
    renderCuotas(repartirEnCuotas(saldo, n));
}

document.getElementById("abonoInicial")?.addEventListener("input", () => {
    const total = getTotalVenta();
    const abonoInp = document.getElementById("abonoInicial");
    let v = Number(abonoInp.value || 0);
    if (v < 0) v = 0;
    if (v > total) v = total;
    abonoInp.value = v.toFixed(2);
    updateCuotasFromTotal();
});

// Evento para método de pago
document.querySelectorAll('input[name="metodoPago"]').forEach((radio) => {
    radio.addEventListener("change", function () {
        const val = String(this.value);
        const autorizacionContainer = document.getElementById(
            "autorizacionContainer"
        );
        const cuotasContainer = document.getElementById("cuotasContainer");
        const numeroAut = document.getElementById("numeroAutorizacion");

        if (["2", "3", "4", "5"].includes(val)) {
            // Mostrar autorización
            cuotasContainer.classList.add("hidden");
            autorizacionContainer.classList.remove("hidden");
            // Validación rápida: no permitir procesar si falta autorización
            numeroAut.removeEventListener("input", checkAuth);
            numeroAut.addEventListener("input", checkAuth);
            checkAuth();
        } else if (val === "6") {
            // Pagos: mostrar cuotas, ocultar autorización y repartir
            autorizacionContainer.classList.add("hidden");
            numeroAut.value = "";
            cuotasContainer.classList.remove("hidden");
            document.getElementById("abonoInicial").value = "";
            document.querySelectorAll(".cuota-input").forEach((input) => {
                input.value = ""; // Vaciar cada input de cuotas
            });
            // Inicializar cuotas según total
            updateCuotasFromTotal();
        } else {
            // Otro método
            autorizacionContainer.classList.add("hidden");
            numeroAut.value = "";
            document.getElementById("cuotasContainer").classList.add("hidden");
            // deja el estado del botón a cargo de calcularTotales()
        }

        calcularTotales?.(); // sigue tu flujo
    });
});

// Controles de cuotas
document
    .getElementById("cuotasRepartir")
    ?.addEventListener("click", updateCuotasFromTotal);
document
    .getElementById("cuotasNumero")
    ?.addEventListener("input", updateCuotasFromTotal);

// Si cambia descuento, vuelve a repartir (y validar)
document.getElementById("descuentoModal")?.addEventListener("input", () => {
    calcularTotales?.();
    // repartimos otra vez (igualitario)
    updateCuotasFromTotal();
});

// Observa cambios en el total (por cambios de carrito, descuentos, etc.)
const totalEl = document.getElementById("totalModal");
if (totalEl) {
    const obs = new MutationObserver(() => {
        updateCuotasFromTotal();
    });
    obs.observe(totalEl, {
        childList: true,
        characterData: true,
        subtree: true,
    });
}

document.querySelectorAll('input[name="metodoAbono"]').forEach((radio) => {
    radio.addEventListener("change", function () {
        const autorizacionContainer = document.getElementById(
            "autorizacionContainer"
        );

        if (this.value === "transferencia" || this.value === "cheque") {
            // Mostrar contenedor de autorización cuando se seleccione Transferencia
            autorizacionContainer.classList.remove("hidden");
        } else {
            // Ocultar contenedor de autorización cuando se seleccione Efectivo
            autorizacionContainer.classList.add("hidden");
        }
    });
});




























































// ============================================
// VALIDACIÓN COMPLETA PARA PROCESAR VENTA
// ============================================


const btnProcesarventa = document.getElementById("procesarVentaModal");

btnProcesarventa.addEventListener("click", validarVenta);

function validarVenta() {
    const errores = [];
    const avisos = [];

    // 1. VALIDAR CLIENTE
    const cliente = validarCliente();
    if (!cliente || !cliente.valido) {
        errores.push(cliente?.error || "Error validando cliente");
    }

    // 2. VALIDAR PRODUCTOS EN CARRITO
    const productos = validarProductosCarrito();
    if (!productos.valido) {
        errores.push(...productos.errores);
    }
    if (productos.avisos && productos.avisos.length > 0) {
        avisos.push(...productos.avisos);
    }

    // 3. VALIDAR MÉTODO DE PAGO
    const metodoPago = validarMetodoPago();
    if (!metodoPago.valido) {
        errores.push(...metodoPago.errores);
    }

    // 4. VALIDAR FECHAS Y DATOS GENERALES
    const datosGenerales = validarDatosGenerales();
    if (!datosGenerales.valido) {
        errores.push(...datosGenerales.errores);
    }


    // Mostrar errores si los hay
    if (errores.length > 0) {
        const erroresHtml = errores.map(error => 
            `<li class="text-sm text-red-700 mb-1">• ${error}</li>`
        ).join('');
        
        Swal.fire({
            title: 'Faltan datos por completar',
            html: `
                <div class="text-left">
                    <p class="text-sm text-gray-600 mb-3">Complete los siguientes datos para proceder:</p>
                    <ul class="max-h-60 overflow-auto">
                        ${erroresHtml}
                    </ul>
                </div>
            `,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }

    // Si todo está válido, procesar
    procesarVentaFinal();

    return {
        // valido: todoValido,
        errores,
        avisos,
    };
}

// ============================================
// VALIDACIONES ESPECÍFICAS
// ============================================
 
function validarProductosCarrito() {
    const errores = [];
    const avisos = [];

    // Verificar que hay productos
    if (!Array.isArray(carritoProductos) || carritoProductos.length === 0) {
        errores.push("El carrito está vacío. Agregue al menos un producto.");
        return { valido: false, errores, avisos };
    }

    // Validar cada producto
    carritoProductos.forEach((producto, index) => {
        const nombre = producto.nombre || `Producto ${index + 1}`;
        const cantidad = Number(producto.cantidad || 0);
        const stock = Number(producto.stock_cantidad_total || 0);
        const necesitaStock = Number(producto.producto_requiere_stock ?? 1) === 1;
        const requiereSerie = Number(producto.producto_requiere_serie || 0) === 1;
        const tieneLotes = Array.isArray(producto.lotes) && producto.lotes.length > 0;

        // Validar cantidad básica (siempre)
        if (cantidad <= 0) {
            errores.push(`${nombre}: La cantidad debe ser mayor a 0`);
        }

        // Solo validar stock, series y lotes si el producto necesita stock
        if (necesitaStock) {
            // Validar cantidad vs stock
            if (cantidad > stock) {
                errores.push(
                    `${nombre}: Cantidad solicitada (${cantidad}) supera el stock disponible (${stock})`
                );
            }

            // Validar series si las requiere
            if (requiereSerie) {
                const seriesSeleccionadas = Array.isArray(producto.seriesSeleccionadas)
                    ? producto.seriesSeleccionadas
                    : [];

                if (seriesSeleccionadas.length === 0) {
                    errores.push(
                        `${nombre}: Debe seleccionar las series (requiere ${cantidad} serie(s))`
                    );
                } else if (seriesSeleccionadas.length !== cantidad) {
                    errores.push(
                        `${nombre}: Debe seleccionar exactamente ${cantidad} serie(s). Actualmente: ${seriesSeleccionadas.length}`
                    );
                }
            }

            // Validar lotes si los tiene
            if (tieneLotes) {
                const lotesSeleccionados = Array.isArray(producto.lotesSeleccionados)
                    ? producto.lotesSeleccionados
                    : [];

                const totalAsignadoLotes = lotesSeleccionados.reduce(
                    (sum, lote) => sum + Number(lote.cantidad || 0),
                    0
                );

                if (totalAsignadoLotes === 0) {
                    errores.push(
                        `${nombre}: Debe asignar los productos a los lotes disponibles`
                    );
                } else if (totalAsignadoLotes !== cantidad) {
                    errores.push(
                        `${nombre}: La cantidad asignada en lotes (${totalAsignadoLotes}) debe coincidir con la cantidad del producto (${cantidad})`
                    );
                }

                // Verificar que no exceda la cantidad disponible en cada lote
                lotesSeleccionados.forEach((loteSeleccionado) => {
                    const loteOriginal = producto.lotes.find(
                        (l) => Number(l.lote_id) === Number(loteSeleccionado.lote_id)
                    );
                    if (loteOriginal) {
                        const maxLote = Number(loteOriginal.lote_cantidad_total || 0);
                        const cantidadAsignada = Number(loteSeleccionado.cantidad || 0);
                        if (cantidadAsignada > maxLote) {
                            errores.push(
                                `${nombre}: Cantidad asignada al lote ${loteOriginal.lote_codigo} (${cantidadAsignada}) supera lo disponible (${maxLote})`
                            );
                        }
                    }
                });
            }
        }

        // Validar precios (siempre, para todos los productos)
        if (!producto.precio || producto.precio <= 0) {
            errores.push(`${nombre}: Precio inválido`);
        }
    });

    return {
        valido: errores.length === 0,
        errores,
        avisos,
    };
}

function validarCliente() {
    const clienteSelect = document.getElementById("clienteSelect");
    const clienteId = clienteSelect?.value;

    if (!clienteId || clienteId === "") {
        return {
            valido: false,
            error: "Debe seleccionar un cliente",
        };
    }

    return {
        valido: true,
        clienteId: clienteId
    };
}

function validarMetodoPago() {
    const errores = [];
    const metodoPago = document.querySelector('input[name="metodoPago"]:checked')?.value;
    const numeroAutorizacion = document.getElementById("numeroAutorizacion")?.value.trim() || '';
    const selecBanco = document.getElementById("selectBanco")?.value.trim() || '';

    if (!metodoPago) {
        errores.push("Debe seleccionar un método de pago");
        return { valido: false, errores };
    }

    // Definir 'tipoMetodo' antes del switch
    const tipoMetodo = {
        2: "tarjeta de crédito",
        3: "tarjeta de débito",
        4: "transferencia",
        5: "cheque",
    };

    // Validaciones específicas por método de pago
    switch (metodoPago) {
        case "1": // Efectivo
            // No requiere validaciones adicionales
            break;

        case "2": // Tarjeta de crédito
        case "3": // Tarjeta de débito
        case "4": // Transferencia
        case "5": // Cheque
            if (!selecBanco || selecBanco === "") {
                errores.push(
                    `Debe seleccionar el tipo de banco para ${tipoMetodo[metodoPago]}`
                );
            }

            if (!numeroAutorizacion) {
                errores.push(
                    `Debe ingresar el número de autorización para ${tipoMetodo[metodoPago]}`
                );
            }
            break;

        case "6": // Pagos/Cuotas
            // Verificar si el abono es por transferencia
            const esTransferencia = document.querySelector(
                'input[name="metodoAbono"][value="transferencia"]:checked'
            );

            if (esTransferencia) {
                if (!selecBanco || selecBanco === "") {
                    errores.push(
                        `Debe seleccionar el tipo de banco para el abono por transferencia`
                    );
                }

                if (!numeroAutorizacion) {
                    errores.push(
                        `Debe ingresar el número de autorización de la transferencia`
                    );
                }
            }

            const erroresCuotas = validarCuotas(); 

            if (erroresCuotas.length > 0) {
                errores.push(...erroresCuotas);
            }

            break;

        default:
            errores.push("Método de pago no válido");
    }

    return {
        valido: errores.length === 0,
        errores,
    };
}


function validarDatosGenerales() {
    const errores = [];
    
    // Validar fecha
    const fecha = document.getElementById("fechaVenta")?.value;
    if (!fecha) {
        errores.push("Debe seleccionar una fecha para la venta");
    }

    // Validar que el total sea mayor a 0
    const total = getTotalVenta();
    if (total <= 0) {
        errores.push("El total de la venta debe ser mayor a 0");
    }

    return {
        valido: errores.length === 0,
        errores
    };
}







































// ============================================
// FUNCIÓN PARA PROCESAR LA VENTA FINAL
// ============================================

async function procesarVentaFinal() {
    try {
        // Mostrar loading
        Swal.fire({
            title: 'Procesando venta...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // 1. DATOS GENERALES DE LA VENTA
        const clienteId = document.getElementById("clienteSelect").value;
        const fechaVenta = document.getElementById("fechaVenta").value;
        const metodoPago = document.querySelector('input[name="metodoPago"]:checked').value;
        const descuento = parseFloat(document.getElementById("descuentoModal").value) || 0;
        
        // Calcular totales
        const subtotal = carritoProductos.reduce((sum, p) => sum + (p.precio * p.cantidad), 0);
        const descuentoMonto = subtotal * (descuento / 100);
        const total = subtotal - descuentoMonto;

        // 2. DATOS DE VENTA
        const datosVenta = {
            // Información general
            cliente_id: clienteId,
            fecha_venta: fechaVenta,
            subtotal: subtotal.toFixed(2),
            descuento_porcentaje: descuento,
            descuento_monto: descuentoMonto.toFixed(2),
            total: total.toFixed(2),
            
            // Método de pago
            metodo_pago: metodoPago,
            
            // Productos del carrito
            productos: carritoProductos.map(producto => ({
                producto_id: producto.producto_id,
                cantidad: producto.cantidad,
                precio_unitario: producto.precio,
                subtotal_producto: (producto.precio * producto.cantidad).toFixed(2),

                 // Indica si el producto requiere validación de stock
                producto_requiere_stock: producto.producto_requiere_stock,
                
                // Series si las requiere
                requiere_serie: producto.producto_requiere_serie || 0,
                series_seleccionadas: producto.seriesSeleccionadas || [],
                
                // Lotes si los tiene
                tiene_lotes: (Array.isArray(producto.lotes) && producto.lotes.length > 0),
                lotes_seleccionados: producto.lotesSeleccionados || []
            }))
        };

        // 3. DATOS ESPECÍFICOS SEGÚN MÉTODO DE PAGO
        switch(metodoPago) {
            case "1": // Efectivo
                datosVenta.pago = {
                    tipo: "efectivo",
                    monto: total.toFixed(2)
                };
                break;

            case "2": // Tarjeta de crédito
            case "3": // Tarjeta de débito  
            case "4": // Transferencia
            case "5": // Cheque
                const numeroAutorizacion = document.getElementById("numeroAutorizacion").value.trim();
                const bancoId = document.getElementById("selectBanco").value;
                
                datosVenta.pago = {
                    tipo: metodoPago === "2" ? "tarjeta_credito" : 
                          metodoPago === "3" ? "tarjeta_debito" :
                          metodoPago === "4" ? "transferencia" : "cheque",
                    monto: total.toFixed(2),
                    numero_autorizacion: numeroAutorizacion,
                    banco_id: bancoId
                };
                break;

            case "6": // Pagos/Cuotas
                const abonoInicial = parseFloat(document.getElementById("abonoInicial").value) || 0;
                const metodoAbono = document.querySelector('input[name="metodoAbono"]:checked')?.value || "efectivo";
                
                // Recopilar cuotas
                const cuotasInputs = document.querySelectorAll("#cuotasLista .cuota-input");
                const cuotas = Array.from(cuotasInputs).map((input, index) => ({
                    numero_cuota: index + 1,
                    monto: parseFloat(input.value) || 0,
                    fecha_vencimiento: null // Se calculará en el backend
                }));

                datosVenta.pago = {
                    tipo: "cuotas",
                    abono_inicial: abonoInicial.toFixed(2),
                    metodo_abono: metodoAbono,
                    total_cuotas: cuotas.reduce((sum, c) => sum + c.monto, 0).toFixed(2),
                    cantidad_cuotas: cuotas.length,
                    cuotas: cuotas
                };

                // Si el abono es por transferencia, agregar datos bancarios
                if (metodoAbono === "transferencia") {
                    const numeroAutorizacionAbono = document.getElementById("numeroAutorizacion").value.trim();
                    const bancoIdAbono = document.getElementById("selectBanco").value;
                    
                    datosVenta.pago.numero_autorizacion_abono = numeroAutorizacionAbono;
                    datosVenta.pago.banco_id_abono = bancoIdAbono;
                }
                          // Si el abono es por transferencia, agregar datos bancarios
                if (metodoAbono === "cheque") {
                    const numeroAutorizacionAbono = document.getElementById("numeroAutorizacion").value.trim();
                    const bancoIdAbono = document.getElementById("selectBanco").value;
                    
                    datosVenta.pago.numero_autorizacion_abono = numeroAutorizacionAbono;
                    datosVenta.pago.banco_id_abono = bancoIdAbono;
                }
                break;

        }

        console.log('Datos de venta a enviar:', datosVenta);

        // 4. ENVIAR AL CONTROLADOR
        const response = await fetch('/api/ventas/procesar-venta', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(datosVenta)
        });

        const resultado = await response.json();
        console.log('resultado proceso de venta: ',resultado);

        if (response.ok && resultado.success) {
            // Éxito
            await Swal.fire({
                title: '¡Venta procesada!',
                text: `Venta registrada exitosamente. Folio: ${resultado.folio || 'N/A'}`,
                icon: 'success',
                confirmButtonText: 'Continuar'
            });

            // Limpiar formulario
            limpiarFormularioVenta();
            
            // Opcional: imprimir ticket o redirigir
            if (resultado.venta_id) {
                const imprimirTicket = await Swal.fire({
                    title: '¿Desea imprimir el ticket?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, imprimir',
                    cancelButtonText: 'No, gracias'
                });

                if (imprimirTicket.isConfirmed) {
                    window.open(`/ventas/${resultado.venta_id}/ticket`, '_blank');
                }
            }

        } else {
            // Error del servidor
            throw new Error(resultado.message || 'Error procesando la venta');
        }

    } catch (error) {
        console.error('Error procesando venta:', error);
        
        await Swal.fire({
            title: 'Error',
            text: error.message || 'Ocurrió un error al procesar la venta',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    }
}

function limpiarFormularioVenta() {
    // Limpiar carrito
    carritoProductos = [];
    actualizarVistaCarrito();
    actualizarContadorCarrito();
    
    // Resetear cliente
    document.getElementById("clienteSelect").value = "";
    
    // Resetear método de pago
    document.querySelectorAll('input[name="metodoPago"]').forEach(radio => {
        radio.checked = false;
    });
    
    // Ocultar contenedores específicos
    document.getElementById("autorizacionContainer").classList.add("hidden");
    document.getElementById("cuotasContainer").classList.add("hidden");
    
    // Limpiar campos
    document.getElementById("numeroAutorizacion").value = "";
    document.getElementById("selectBanco").value = "";
    document.getElementById("abonoInicial").value = "";
    document.getElementById("descuentoModal").value = "";
    
    // Limpiar cuotas
    document.getElementById("cuotasLista").innerHTML = "";
    
    // Cerrar modal de carrito
    cerrarCarrito();
    
    // Resetear fecha a actual
    const ahora = new Date();
    const fechaHoraLocal = new Date(ahora.getTime() - (ahora.getTimezoneOffset() * 60000))
        .toISOString()
        .slice(0, 16);
    document.getElementById("fechaVenta").value = fechaHoraLocal;
    
    // Recalcular totales
    calcularTotales();
}




document.addEventListener('DOMContentLoaded', function () {
    const checkbox = document.getElementById('checkRequiereDocumentacion');
    const modal = document.getElementById('modalDocumentacion');
    const btnCerrarModal = document.getElementById('btnCerrarModalDocumentacion'); // si usas ID

    // Mostrar/ocultar modal al marcar el checkbox
    checkbox.addEventListener('change', function () {
        if (this.checked) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } else {
            cerrarModalDocumentacion(); // También cerrar si desmarcan
        }
    });

    // Si el botón de cerrar tiene ID o clase, usa esto:
    if (btnCerrarModal) {
        btnCerrarModal.addEventListener('click', cerrarModalDocumentacion);
    }

    // Opcional: cerrar al hacer clic fuera del contenido
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            cerrarModalDocumentacion();
        }
    });
});

// Función para cerrar el modal
function cerrarModalDocumentacion() {
    const modal = document.getElementById('modalDocumentacion');
    const checkbox = document.getElementById('checkRequiereDocumentacion');
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    checkbox.checked = false;
}

// Función para agregar documento
function agregarDocumentoVenta() {
    const tipo = document.getElementById("tipoDocumentoSelect").value;
    const numero = document.getElementById("numeroDocumentoInput").value;

    if (!tipo || !numero) {
        alert("Por favor completa todos los campos.");
        return;
    }

    // Aquí puedes enviar a un array, backend o agregar a una lista visual
    console.log("Documento agregado:", tipo, numero);

    cerrarModalDocumentacion();
}
