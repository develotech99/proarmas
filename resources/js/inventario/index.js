/**
 * Módulo de Control de Inventario - Armería
 * JavaScript puro sin frameworks (compatible con AdminLTE 4)
 * Requiere: jQuery, DataTables, Select2, SweetAlert2
 */

class InventarioManager {
    constructor() {
        this.tablaStock = null;
        this.tablaHistorial = null;
        this.tablaHistorialProducto = null;
        this.productosSelect2Data = [];
        
        this.init();
    }

    /**
     * Inicializa el módulo completo
     */
    init() {
        this.initializeComponents();
        this.bindEvents();
        this.loadInitialData();
        
        console.log('InventarioManager inicializado correctamente');
    }

    /**
     * Inicializa componentes como DataTables, Select2, etc.
     */
    initializeComponents() {
        // Inicializar Select2
        this.initSelect2();
        
        // Inicializar DataTables
        this.initDataTables();
        
        // Configurar formularios
        this.setupForms();
        
        // Configurar tooltips
        $('[data-toggle="tooltip"]').tooltip();
    }

    /**
     * Configura todos los Select2 del módulo
     */
    initSelect2() {
        // Select2 básicos
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Seleccionar...',
            allowClear: true
        });

        // Select2 para búsqueda de productos en movimientos
        $('#mov_producto_id').select2({
            theme: 'bootstrap4',
            placeholder: 'Buscar producto...',
            allowClear: true,
            ajax: {
                url: '/inventario/productos-stock',
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    search: params.term,
                    page: params.page || 1
                }),
                processResults: (data) => {
                    const results = data.data.map(item => ({
                        id: item.producto_id,
                        text: `${item.nombre} (Stock: ${item.stock_actual})`,
                        data: item
                    }));
                    
                    return {
                        results: results,
                        pagination: { more: false }
                    };
                },
                cache: true
            }
        });

        // Select2 para filtros en historial
        $('#filtro-producto-historial').select2({
            theme: 'bootstrap4',
            placeholder: 'Buscar producto...',
            allowClear: true
        });
    }

    /**
     * Inicializa las DataTables del módulo
     */
    initDataTables() {
        // Tabla de Stock Actual
        this.tablaStock = $('#tabla-stock').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '/inventario/productos-stock',
                type: 'GET',
                data: (d) => {
                    d.categoria = $('#filtro-categoria').val();
                    d.marca = $('#filtro-marca').val();
                }
            },
            columns: [
                { data: 'codigo_barra', defaultContent: 'Sin código' },
                { data: 'nombre' },
                { data: 'categoria' },
                { data: 'marca' },
                { data: 'modelo', defaultContent: '-' },
                { 
                    data: 'stock_actual',
                    render: (data, type, row) => {
                        let clase = 'stock-normal';
                        if (data === 0) clase = 'stock-agotado';
                        else if (data <= 5) clase = 'stock-bajo';
                        
                        return `<span class="${clase}">${data}</span>`;
                    }
                },
                { 
                    data: 'series_disponibles',
                    render: (data, type, row) => {
                        if (!row.requiere_serie) return '<span class="text-muted">N/A</span>';
                        return `<span class="badge badge-info">${data}</span>`;
                    }
                },
                {
                    data: 'stock_actual',
                    render: (data, type, row) => {
                        if (data > 5) return '<span class="badge badge-success">Normal</span>';
                        if (data > 0) return '<span class="badge badge-warning">Bajo</span>';
                        return '<span class="badge badge-danger">Agotado</span>';
                    }
                },
                {
                    data: 'producto_id',
                    render: (data, type, row) => {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="inventario.verDetalleProducto(${data})"
                                        data-toggle="tooltip" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary" 
                                        onclick="inventario.verHistorialProducto(${data})"
                                        data-toggle="tooltip" title="Ver historial">
                                    <i class="fas fa-history"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" 
                                        onclick="inventario.seleccionarProductoMovimiento(${data})"
                                        data-toggle="tooltip" title="Registrar movimiento">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm'
                }
            ]
        });

        // Tabla de Historial General
        this.tablaHistorial = $('#tabla-historial').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '/inventario/movimientos',
                type: 'GET',
                data: (d) => {
                    d.producto_id = $('#filtro-producto-historial').val();
                    d.fecha_desde = $('#fecha-desde').val();
                    d.fecha_hasta = $('#fecha-hasta').val();
                    d.tipo = $('#filtro-tipo-movimiento').val();
                }
            },
            columns: [
                { data: 'fecha' },
                { data: 'producto_nombre' },
                { 
                    data: 'tipo',
                    render: (data) => {
                        const badges = {
                            'Ingreso': 'badge-success',
                            'Egreso': 'badge-primary',
                            'Baja': 'badge-danger',
                            'Importación': 'badge-info'
                        };
                        return `<span class="badge ${badges[data] || 'badge-secondary'}">${data}</span>`;
                    }
                },
                { data: 'origen' },
                { data: 'cantidad' },
                { data: 'usuario' },
                { data: 'lote', defaultContent: '-' },
                { data: 'observaciones', defaultContent: '-' }
            ],
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            pageLength: 25,
            responsive: true
        });

        // Tabla de Historial por Producto (Modal)
        this.tablaHistorialProducto = $('#tabla-historial-producto').DataTable({
            processing: true,
            serverSide: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            pageLength: 10,
            order: [[0, 'desc']]
        });
    }

    /**
     * Configura los formularios y sus validaciones
     */
    setupForms() {
        // Configurar preview de fotos
        $('#fotos').on('change', this.previewFotos);
        
        // Configurar campos dinámicos del formulario de ingreso
        $('#producto_requiere_serie').on('change', this.toggleSeriesCantidad);
        $('#producto_es_importado').on('change', this.toggleLicenciaImportacion);
        
        // Configurar dependencias de categoría -> subcategoría
        $('#producto_categoria_id').on('change', this.cargarSubcategorias);
        
        // Configurar gestión dinámica de series
        this.setupSeriesManager();
    }

    /**
     * Configura el gestor dinámico de números de serie
     */
    setupSeriesManager() {
        // Agregar nueva serie
        $(document).on('click', '.btn-add-serie', function() {
            const container = $('#container-series');
            const newInput = `
                <div class="input-group mb-2 serie-input-group">
                    <input type="text" class="form-control serie-input" name="series[]" placeholder="Número de serie" required>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger btn-remove-serie">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            `;
            container.append(newInput);
        });

        // Remover serie
        $(document).on('click', '.btn-remove-serie', function() {
            $(this).closest('.serie-input-group').remove();
        });

        // Validar series únicas en tiempo real
        $(document).on('blur', '.serie-input', this.validarSerieUnica);
    }

    /**
     * Vincula todos los eventos del módulo
     */
    bindEvents() {
        // Eventos de formularios
        $('#form-ingresar-producto').on('submit', this.procesarIngresoProducto.bind(this));
        $('#form-registrar-movimiento').on('submit', this.procesarRegistroMovimiento.bind(this));
        
        // Eventos de filtros
        $('#btn-aplicar-filtros').on('click', () => this.aplicarFiltrosStock());
        $('#btn-limpiar-filtros').on('click', () => this.limpiarFiltrosStock());
        $('#btn-filtrar-historial').on('click', () => this.filtrarHistorial());
        
        // Eventos de botones de limpieza
        $('#btn-limpiar-form').on('click', () => this.limpiarFormularioIngreso());
        $('#btn-limpiar-movimiento').on('click', () => this.limpiarFormularioMovimiento());
        
        // Evento cambio de producto en movimientos
        $('#mov_producto_id').on('select2:select', this.cargarInfoProductoMovimiento.bind(this));
        
        // Eventos de selección de series
        $('#check-all-series').on('change', this.toggleTodasSeries);
        $(document).on('change', '.serie-checkbox', this.actualizarContadorSeries);
        
        // Eventos de pestañas
        $('a[data-toggle="pill"]').on('shown.bs.tab', this.onTabChange.bind(this));
    }

    /**
     * Carga datos iniciales del módulo
     */
    loadInitialData() {
        this.actualizarResumenDashboard();
        this.cargarProductosParaFiltros();
    }

    /**
     * Procesa el formulario de ingreso de producto
     */
    async procesarIngresoProducto(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Validaciones adicionales
        if (!this.validarFormularioIngreso(formData)) {
            return false;
        }
        
        try {
            this.showLoading('Ingresando producto...');
            
            const response = await fetch('/inventario/producto/ingresar', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Producto Ingresado!',
                    text: result.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                
                this.limpiarFormularioIngreso();
                this.tablaStock.ajax.reload();
                this.actualizarResumenDashboard();
                
                // Cambiar a pestaña de stock
                $('#stock-tab').tab('show');
                
            } else {
                this.showValidationErrors(result.errors);
            }
            
        } catch (error) {
            console.error('Error al ingresar producto:', error);
            this.showError('Error de conexión al procesar la solicitud');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Procesa el formulario de registro de movimiento
     */
    async procesarRegistroMovimiento(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        // Agregar series seleccionadas si aplica
        const productId = $('#mov_producto_id').val();
        const productoData = $('#mov_producto_id').select2('data')[0]?.data;
        
        if (productoData?.requiere_serie) {
            const seriesSeleccionadas = [];
            $('.serie-checkbox:checked').each(function() {
                seriesSeleccionadas.push($(this).val());
            });
            
            if (seriesSeleccionadas.length === 0) {
                this.showError('Debe seleccionar al menos una serie');
                return false;
            }
            
            formData.append('series_seleccionadas', JSON.stringify(seriesSeleccionadas));
            formData.append('requiere_serie', 'true');
        } else {
            formData.append('requiere_serie', 'false');
        }
        
        try {
            this.showLoading('Registrando movimiento...');
            
            const response = await fetch('/inventario/movimiento/registrar', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Movimiento Registrado!',
                    text: result.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                
                this.limpiarFormularioMovimiento();
                this.tablaStock.ajax.reload();
                this.actualizarResumenDashboard();
                
            } else {
                this.showValidationErrors(result.errors);
            }
            
        } catch (error) {
            console.error('Error al registrar movimiento:', error);
            this.showError('Error de conexión al procesar la solicitud');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Carga información del producto seleccionado para movimiento
     */
    async cargarInfoProductoMovimiento(e) {
        const productoData = e.params.data.data;
        
        if (!productoData) return;
        
        try {
            const response = await fetch(`/inventario/producto/${productoData.producto_id}/detalle`);
            const result = await response.json();
            
            if (result.success) {
                this.mostrarInfoProductoMovimiento(result.data);
                
                if (result.data.requiere_serie) {
                    await this.cargarSeriesDisponibles(productoData.producto_id);
                }
            }
            
        } catch (error) {
            console.error('Error al cargar info del producto:', error);
        }
    }

    /**
     * Muestra la información del producto en el panel de movimiento
     */
    mostrarInfoProductoMovimiento(producto) {
        const html = `
            <div class="row">
                <div class="col-md-12">
                    <h5>${producto.producto.producto_nombre}</h5>
                    <p class="mb-1"><strong>Stock actual:</strong> 
                        <span class="badge badge-${producto.stock_actual > 0 ? 'success' : 'danger'}">${producto.stock_actual}</span>
                    </p>
                    <p class="mb-1"><strong>Requiere serie:</strong> 
                        <span class="badge badge-${producto.requiere_serie ? 'info' : 'secondary'}">
                            ${producto.requiere_serie ? 'Sí' : 'No'}
                        </span>
                    </p>
                    ${producto.requiere_serie ? `
                        <p class="mb-1"><strong>Series disponibles:</strong> ${producto.series_disponibles}</p>
                    ` : ''}
                </div>
            </div>
        `;
        
        $('#info-producto-seleccionado').html(html);
        
        // Mostrar panel correspondiente
        if (producto.requiere_serie) {
            $('#panel-cantidad-movimiento').hide();
            $('#panel-series-movimiento').show();
        } else {
            $('#panel-series-movimiento').hide();
            $('#panel-cantidad-movimiento').show();
            $('#stock-disponible').text(producto.stock_actual);
            $('#mov_cantidad_input').attr('max', producto.stock_actual);
        }
    }

    /**
     * Carga las series disponibles de un producto
     */
    async cargarSeriesDisponibles(productoId) {
        try {
            const response = await fetch(`/inventario/producto/${productoId}/series`);
            const result = await response.json();
            
            if (result.success) {
                const tbody = $('#tabla-series-disponibles tbody');
                tbody.empty();
                
                result.data.forEach(serie => {
                    const row = `
                        <tr>
                            <td>
                                <input type="checkbox" class="serie-checkbox" value="${serie.serie_id}">
                            </td>
                            <td>${serie.serie_numero_serie}</td>
                            <td>${new Date(serie.serie_fecha_ingreso).toLocaleDateString()}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                
                this.actualizarContadorSeries();
            }
            
        } catch (error) {
            console.error('Error al cargar series:', error);
        }
    }

    /**
     * Ver detalles de un producto específico
     */
    async verDetalleProducto(productoId) {
        try {
            this.showLoading('Cargando detalles...');
            
            const response = await fetch(`/inventario/producto/${productoId}/detalle`);
            const result = await response.json();
            
            if (result.success) {
                this.mostrarModalDetalleProducto(result.data);
            } else {
                this.showError('Error al cargar detalles del producto');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Ver historial de movimientos de un producto
     */
    async verHistorialProducto(productoId) {
        try {
            this.showLoading('Cargando historial...');
            
            const response = await fetch(`/inventario/producto/${productoId}/movimientos`);
            const result = await response.json();
            
            if (result.success) {
                this.mostrarModalHistorialProducto(result.data);
            } else {
                this.showError('Error al cargar historial del producto');
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión');
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Muestra el modal con detalles del producto
     */
    mostrarModalDetalleProducto(producto) {
        let fotosHtml = '';
        if (producto.fotos && producto.fotos.length > 0) {
            fotosHtml = producto.fotos.map(foto => 
                `<img src="${foto.foto_url}" class="img-thumbnail m-1" style="max-width: 100px;">`
            ).join('');
        }
        
        const html = `
            <div class="row">
                <div class="col-md-8">
                    <h4>${producto.producto.producto_nombre}</h4>
                    <p><strong>Código de barras:</strong> ${producto.producto.producto_codigo_barra || 'Sin código'}</p>
                    <p><strong>Stock actual:</strong> <span class="badge badge-primary">${producto.stock_actual}</span></p>
                    <p><strong>Requiere serie:</strong> 
                        <span class="badge badge-${producto.requiere_serie ? 'success' : 'secondary'}">
                            ${producto.requiere_serie ? 'Sí' : 'No'}
                        </span>
                    </p>
                    ${producto.requiere_serie ? `
                        <p><strong>Series disponibles:</strong> ${producto.series_disponibles}</p>
                        <p><strong>Total de series:</strong> ${producto.series_total}</p>
                    ` : ''}
                    <p><strong>Producto importado:</strong> 
                        <span class="badge badge-${producto.producto.producto_es_importado ? 'warning' : 'secondary'}">
                            ${producto.producto.producto_es_importado ? 'Sí' : 'No'}
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6>Fotos del producto:</h6>
                    <div class="fotos-container">
                        ${fotosHtml || '<p class="text-muted">Sin fotos disponibles</p>'}
                    </div>
                </div>
            </div>
        `;
        
        $('#contenido-detalle-producto').html(html);
        $('#modal-detalle-producto').modal('show');
    }

    /**
     * Muestra el modal con historial del producto
     */
    mostrarModalHistorialProducto(movimientos) {
        this.tablaHistorialProducto.clear();
        this.tablaHistorialProducto.rows.add(movimientos);
        this.tablaHistorialProducto.draw();
        
        $('#modal-historial-producto').modal('show');
    }

    /**
     * Selecciona un producto para registrar movimiento
     */
    seleccionarProductoMovimiento(productoId) {
        // Cambiar a la pestaña de movimientos
        $('#movimientos-tab').tab('show');
        
        // Buscar el producto en la tabla y seleccionarlo
        const filaProducto = this.tablaStock.row((idx, data) => data.producto_id === productoId).data();
        
        if (filaProducto) {
            // Crear opción para Select2 y seleccionarla
            const option = new Option(
                `${filaProducto.nombre} (Stock: ${filaProducto.stock_actual})`,
                productoId,
                true,
                true
            );
            
            $('#mov_producto_id').append(option).trigger('change');
        }
    }

    /**
     * Funciones utilitarias y helpers
     */

    // Toggle entre campos de serie y cantidad
    toggleSeriesCantidad() {
        const requiereSerie = $('#producto_requiere_serie').is(':checked');
        
        if (requiereSerie) {
            $('#grupo-cantidad').hide();
            $('#grupo-series').show();
            $('#cantidad_inicial, #lote_codigo').removeAttr('required');
            $('.serie-input').attr('required', true);
        } else {
            $('#grupo-series').hide();
            $('#grupo-cantidad').show();
            $('#cantidad_inicial, #lote_codigo').attr('required', true);
            $('.serie-input').removeAttr('required');
        }
    }

    // Toggle campo de licencia de importación
    toggleLicenciaImportacion() {
        const esImportado = $('#producto_es_importado').is(':checked');
        
        if (esImportado) {
            $('#grupo-licencia').show();
        } else {
            $('#grupo-licencia').hide();
            $('#producto_id_licencia').val('').trigger('change');
        }
    }

    // Preview de fotos seleccionadas
    previewFotos() {
        const files = this.files;
        const preview = $('#preview-fotos');
        preview.empty();
        
        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const col = `
                        <div class="col-md-2 mb-2">
                            <img src="${e.target.result}" class="img-thumbnail">
                            <small class="d-block text-center mt-1">
                                ${index === 0 ? '<span class="badge badge-primary">Principal</span>' : 'Secundaria'}
                            </small>
                        </div>
                    `;
                    preview.append(col);
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Validar que el número de serie sea único
    async validarSerieUnica() {
        const numeroSerie = $(this).val().trim();
        
        if (!numeroSerie) return;
        
        // Aquí implementarías una validación AJAX para verificar unicidad
        // Por simplicidad, solo verifico duplicados en el formulario actual
        const series = $('.serie-input').map(function() { return $(this).val().trim(); }).get();
        const duplicados = series.filter(serie => serie === numeroSerie).length;
        
        if (duplicados > 1) {
            $(this).addClass('is-invalid');
            $(this).after('<div class="invalid-feedback">Este número de serie ya está en la lista</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    }

    // Cargar subcategorías basadas en categoría seleccionada
    async cargarSubcategorias() {
        const categoriaId = $(this).val();
        const subcategoriaSelect = $('#producto_subcategoria_id');
        
        subcategoriaSelect.empty().append('<option value="">Cargando...</option>');
        
        if (!categoriaId) {
            subcategoriaSelect.empty().append('<option value="">Seleccionar categoría primero...</option>');
            return;
        }
        
        try {
            // Aquí harías la llamada AJAX para cargar subcategorías
            // Por ahora, simulamos algunas opciones
            const subcategorias = [
                { id: 1, nombre: 'Subcategoría 1' },
                { id: 2, nombre: 'Subcategoría 2' },
                { id: 3, nombre: 'Subcategoría 3' }
            ];
            
            subcategoriaSelect.empty().append('<option value="">Seleccionar...</option>');
            subcategorias.forEach(sub => {
                subcategoriaSelect.append(`<option value="${sub.id}">${sub.nombre}</option>`);
            });
            
        } catch (error) {
            console.error('Error al cargar subcategorías:', error);
            subcategoriaSelect.empty().append('<option value="">Error al cargar</option>');
        }
    }

    // Aplicar filtros a la tabla de stock
    aplicarFiltrosStock() {
        this.tablaStock.ajax.reload();
    }

    // Limpiar filtros de stock
    limpiarFiltrosStock() {
        $('#filtro-categoria, #filtro-marca').val('').trigger('change');
        this.tablaStock.ajax.reload();
    }

    // Filtrar historial
    filtrarHistorial() {
        this.tablaHistorial.ajax.reload();
    }

    // Limpiar formulario de ingreso
    limpiarFormularioIngreso() {
        $('#form-ingresar-producto')[0].reset();
        $('.select2').val('').trigger('change');
        $('#preview-fotos').empty();
        $('#grupo-cantidad').show();
        $('#grupo-series, #grupo-licencia').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    // Limpiar formulario de movimiento
    limpiarFormularioMovimiento() {
        $('#form-registrar-movimiento')[0].reset();
        $('#mov_producto_id').val('').trigger('change');
        $('#info-producto-seleccionado').html('<p class="text-muted text-center">Seleccione un producto para ver su información</p>');
        $('#panel-cantidad-movimiento, #panel-series-movimiento').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    // Toggle todas las series
    toggleTodasSeries() {
        const checked = $(this).is(':checked');
        $('.serie-checkbox').prop('checked', checked);
        inventario.actualizarContadorSeries();
    }

    // Actualizar contador de series seleccionadas
    actualizarContadorSeries() {
        const total = $('.serie-checkbox').length;
        const seleccionadas = $('.serie-checkbox:checked').length;
        
        // Actualizar estado del checkbox "Todos"
        $('#check-all-series').prop('indeterminate', seleccionadas > 0 && seleccionadas < total);
        $('#check-all-series').prop('checked', seleccionadas === total);
    }

    // Validar formulario de ingreso
    validarFormularioIngreso(formData) {
        const requiereSerie = formData.get('producto_requiere_serie') === 'on';
        
        if (requiereSerie) {
            const series = formData.getAll('series[]').filter(s => s.trim() !== '');
            if (series.length === 0) {
                this.showError('Debe ingresar al menos un número de serie');
                return false;
            }
            
            // Verificar series únicas
            const seriesUnicas = [...new Set(series)];
            if (seriesUnicas.length !== series.length) {
                this.showError('No puede haber números de serie duplicados');
                return false;
            }
        } else {
            const cantidad = parseInt(formData.get('cantidad_inicial'));
            if (!cantidad || cantidad < 1) {
                this.showError('La cantidad inicial debe ser mayor a 0');
                return false;
            }
        }
        
        return true;
    }

    // Cargar productos para filtros
    async cargarProductosParaFiltros() {
        try {
            const response = await fetch('/inventario/productos-stock');
            const result = await response.json();
            
            if (result.data) {
                const select = $('#filtro-producto-historial');
                select.empty().append('<option value="">Todos los productos</option>');
                
                result.data.forEach(producto => {
                    select.append(`<option value="${producto.producto_id}">${producto.nombre}</option>`);
                });
            }
            
        } catch (error) {
            console.error('Error al cargar productos para filtros:', error);
        }
    }

    // Actualizar resumen del dashboard
    async actualizarResumenDashboard() {
        try {
            const response = await fetch('/inventario/resumen-dashboard');
            const result = await response.json();
            
            if (result.success) {
                $('#total-productos').text(result.data.total_productos);
                $('#total-series').text(result.data.total_series);
                $('#movimientos-hoy').text(result.data.movimientos_hoy);
                $('#stock-bajo').text(result.data.stock_bajo);
            }
            
        } catch (error) {
            console.error('Error al actualizar resumen:', error);
            // Valores por defecto si falla la carga
            $('#total-productos').text('--');
            $('#total-series').text('--');
            $('#movimientos-hoy').text('--');
            $('#stock-bajo').text('--');
        }
    }

    // Manejar cambio de pestañas
    onTabChange(e) {
        const tabId = $(e.target).attr('href');
        
        switch (tabId) {
            case '#stock-actual':
                if (this.tablaStock) {
                    this.tablaStock.columns.adjust().responsive.recalc();
                }
                break;
                
            case '#historial-movimientos':
                if (this.tablaHistorial) {
                    this.tablaHistorial.ajax.reload();
                    this.tablaHistorial.columns.adjust().responsive.recalc();
                }
                break;
        }
    }

    /**
     * Funciones utilitarias para UI
     */

    showLoading(message = 'Procesando...') {
        Swal.fire({
            title: message,
            allowEscapeKey: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    hideLoading() {
        Swal.close();
    }

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#d33'
        });
    }

    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    showValidationErrors(errors) {
        let errorMessage = 'Se encontraron los siguientes errores:\n\n';
        
        for (const field in errors) {
            if (errors.hasOwnProperty(field)) {
                errorMessage += `• ${errors[field].join('\n• ')}\n`;
                
                // Marcar campos con error
                const input = $(`[name="${field}"]`);
                input.addClass('is-invalid');
                
                // Agregar mensaje de error si no existe
                if (!input.siblings('.invalid-feedback').length) {
                    input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                }
            }
        }
        
        this.showError(errorMessage);
    }

    /**
     * Funciones de confirmación para acciones críticas
     */

    async confirmarEliminacion(callback, mensaje = '¿Está seguro de eliminar este elemento?') {
        const result = await Swal.fire({
            title: '¿Está seguro?',
            text: mensaje,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
        
        return result.isConfirmed;
    }

    async confirmarAccion(callback, titulo = '¿Confirmar acción?', mensaje = 'Esta acción no se puede deshacer') {
        const result = await Swal.fire({
            title: titulo,
            text: mensaje,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
        
        return result.isConfirmed;
    }

    /**
     * Funciones de utilidad para formateo
     */

    formatearFecha(fecha) {
        if (!fecha) return '-';
        return new Date(fecha).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    formatearNumero(numero) {
        if (numero === null || numero === undefined) return '-';
        return new Intl.NumberFormat('es-ES').format(numero);
    }

    formatearMoneda(cantidad) {
        if (cantidad === null || cantidad === undefined) return '-';
        return new Intl.NumberFormat('es-ES', {
            style: 'currency',
            currency: 'USD' // Ajustar según la moneda de tu país
        }).format(cantidad);
    }

    /**
     * Funciones para manejo de archivos e imágenes
     */

    validarArchivo(file, tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'], tamañoMax = 2048000) {
        if (!tiposPermitidos.includes(file.type)) {
            this.showError(`Tipo de archivo no permitido. Tipos válidos: ${tiposPermitidos.join(', ')}`);
            return false;
        }
        
        if (file.size > tamañoMax) {
            this.showError(`El archivo es demasiado grande. Tamaño máximo: ${tamañoMax / 1024 / 1024}MB`);
            return false;
        }
        
        return true;
    }

    /**
     * Funciones para exportación de datos
     */

    exportarStock(formato = 'excel') {
        const datos = this.tablaStock.data().toArray();
        
        if (datos.length === 0) {
            this.showError('No hay datos para exportar');
            return;
        }
        
        switch (formato) {
            case 'excel':
                this.exportarExcel(datos, 'stock_inventario');
                break;
            case 'pdf':
                this.exportarPDF(datos, 'Stock de Inventario');
                break;
            case 'csv':
                this.exportarCSV(datos, 'stock_inventario');
                break;
        }
    }

    exportarExcel(datos, nombreArchivo) {
        // Implementación de exportación a Excel usando librerías como SheetJS
        console.log('Exportando a Excel:', datos);
        this.showSuccess('Función de exportación a Excel pendiente de implementación');
    }

    exportarPDF(datos, titulo) {
        // Implementación de exportación a PDF usando librerías como jsPDF
        console.log('Exportando a PDF:', datos);
        this.showSuccess('Función de exportación a PDF pendiente de implementación');
    }

    exportarCSV(datos, nombreArchivo) {
        const csv = this.convertirArrayToCSV(datos);
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `${nombreArchivo}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    convertirArrayToCSV(datos) {
        if (datos.length === 0) return '';
        
        const headers = Object.keys(datos[0]);
        const csvContent = [
            headers.join(','),
            ...datos.map(row => headers.map(header => `"${row[header] || ''}"`).join(','))
        ].join('\n');
        
        return csvContent;
    }

    /**
     * Funciones de debug y desarrollo
     */

    debug(mensaje, datos = null) {
        if (typeof console !== 'undefined' && console.log) {
            console.log(`[InventarioManager] ${mensaje}`, datos);
        }
    }

    getEstadoActual() {
        return {
            tablaStock: this.tablaStock ? this.tablaStock.data().count() : 0,
            tablaHistorial: this.tablaHistorial ? this.tablaHistorial.data().count() : 0,
            productosSelect2: this.productosSelect2Data.length,
            timestamp: new Date().toISOString()
        };
    }
}

/**
 * Funciones globales para compatibilidad con eventos onclick en HTML
 */

// Variable global para el manager de inventario
let inventario;

// Inicializar cuando el documento esté listo
$(document).ready(function() {
    inventario = new InventarioManager();
    
    // Exponer funciones globalmente para uso en botones
    window.inventario = inventario;
    
    // Funciones globales para eventos onclick
    window.verDetalleProducto = (id) => inventario.verDetalleProducto(id);
    window.verHistorialProducto = (id) => inventario.verHistorialProducto(id);
    window.seleccionarProductoMovimiento = (id) => inventario.seleccionarProductoMovimiento(id);
    
    console.log('Sistema de inventario inicializado correctamente');
});

/**
 * Configuración de eventos globales para el módulo
 */

// Limpiar eventos cuando se abandona la página
$(window).on('beforeunload', function() {
    if (inventario && inventario.tablaStock) {
        inventario.tablaStock.destroy();
    }
    if (inventario && inventario.tablaHistorial) {
        inventario.tablaHistorial.destroy();
    }
});

// Manejar errores de JavaScript globales
window.addEventListener('error', function(e) {
    console.error('Error JavaScript en módulo de inventario:', e.error);
    
    // Solo mostrar errores críticos al usuario
    if (e.error && e.error.message && e.error.message.includes('inventario')) {
        Swal.fire({
            icon: 'error',
            title: 'Error del Sistema',
            text: 'Ha ocurrido un error inesperado. Por favor, recargue la página.',
            confirmButtonText: 'Recargar',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.reload();
            }
        });
    }
});

/**
 * Configuraciones adicionales para mejorar la experiencia del usuario
 */

// Prevenir envío doble de formularios
$(document).on('submit', 'form', function() {
    const submitButton = $(this).find('button[type="submit"]');
    submitButton.prop('disabled', true);
    
    setTimeout(() => {
        submitButton.prop('disabled', false);
    }, 3000);
});

// Autoguardado de borradores (opcional)
let borradorTimeout;
$(document).on('input', '#form-ingresar-producto input, #form-ingresar-producto select, #form-ingresar-producto textarea', function() {
    clearTimeout(borradorTimeout);
    borradorTimeout = setTimeout(() => {
        // Aquí podrías implementar autoguardado en localStorage
        // localStorage.setItem('borrador_producto', JSON.stringify(formData));
    }, 2000);
});

// Shortcuts de teclado útiles
$(document).on('keydown', function(e) {
    // Ctrl + Alt + I = Ir a pestaña de ingreso
    if (e.ctrlKey && e.altKey && e.keyCode === 73) {
        e.preventDefault();
        $('#ingreso-tab').tab('show');
    }
    
    // Ctrl + Alt + S = Ir a pestaña de stock
    if (e.ctrlKey && e.altKey && e.keyCode === 83) {
        e.preventDefault();
        $('#stock-tab').tab('show');
    }
    
    // Ctrl + Alt + M = Ir a pestaña de movimientos
    if (e.ctrlKey && e.altKey && e.keyCode === 77) {
        e.preventDefault();
        $('#movimientos-tab').tab('show');
    }
    
    // ESC = Cerrar modales
    if (e.keyCode === 27) {
        $('.modal').modal('hide');
    }
});

// Tooltips informativos dinámicos
$(document).on('mouseenter', '[data-info]', function() {
    const info = $(this).data('info');
    $(this).attr('title', info).tooltip('show');
});

/**
 * Validaciones adicionales en tiempo real
 */

// Validar códigos de barras en tiempo real
$(document).on('blur', '#producto_codigo_barra', function() {
    const codigo = $(this).val().trim();
    if (codigo && codigo.length < 8) {
        $(this).addClass('is-invalid');
        if (!$(this).siblings('.invalid-feedback').length) {
            $(this).after('<div class="invalid-feedback">El código de barras debe tener al menos 8 caracteres</div>');
        }
    } else {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').remove();
    }
});

// Validar cantidades numéricas
$(document).on('input', 'input[type="number"]', function() {
    const valor = parseInt($(this).val());
    const min = parseInt($(this).attr('min')) || 0;
    const max = parseInt($(this).attr('max')) || Infinity;
    
    if (valor < min || valor > max) {
        $(this).addClass('is-invalid');
    } else {
        $(this).removeClass('is-invalid');
    }
});

console.log('Módulo de inventario cargado completamente');