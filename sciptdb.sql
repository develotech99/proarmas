--marin
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_primer_nombre VARCHAR(100),
    user_segundo_nombre VARCHAR(100),
    user_primer_apellido VARCHAR(100),
    user_segundo_apellido VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_dpi_dni VARCHAR(20),
    user_rol INT,
    user_fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_fecha_contraseña DATETIME NULL,
    user_foto VARCHAR(250),
    user_token VARCHAR(250),
    user_fecha_verificacion DATETIME NULL,
    user_situacion TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (user_rol) REFERENCES roles(id)
);

-- ========================
-- ENTIDADES FUERTES
-- ========================
--marin
CREATE TABLE pro_metodos_pago (
    metpago_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID método de pago',
    metpago_descripcion VARCHAR(50) NOT NULL COMMENT 'efectivo, transferencia, etc.',
    metpago_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
);

--marin


--marin 
CREATE TABLE pro_paises (
    pais_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de país',
    pais_descripcion VARCHAR(50) COMMENT 'Descripción del país',
    pais_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
);
--MARIN
CREATE TABLE pro_categorias (
    categoria_id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_nombre VARCHAR(100) NOT NULL,  ----ejemplo armas, accessorios, municiones, etc.. 
    categoria_situacion INT DEFAULT 1
);
--MARIN

CREATE TABLE pro_subcategorias (
    subcategoria_id INT AUTO_INCREMENT PRIMARY KEY,
    subcategoria_nombre VARCHAR(100) NOT NULL, --ejemplo pistola, fusil, chaleco, mira, etc.... 
    subcategoria_idcategoria INT NOT NULL,
    subcategoria_situacion INT DEFAULT 1,
    FOREIGN KEY (subcategoria_idcategoria) REFERENCES pro_categorias(categoria_id)
);


CREATE TABLE pro_marcas (
    marca_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de marca',
    marca_descripcion VARCHAR(50) COMMENT 'system defense, glock, brigade',
    marca_situacion INT DEFAULT 1 COMMENT '1 = activa, 0 = inactiva'
);

--carlos
CREATE TABLE pro_modelo (
    modelo_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de modelo',
    modelo_descripcion VARCHAR(50) COMMENT 'c9, bm-f-9, sd15',
    modelo_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo', 
    modelo_marca_id INT NULL,
    FOREIGN KEY (modelo_marca_id) REFERENCES pro_marcas(marca_id)
);

--MARIN 
-- =========================================
-- 🟦 TABLA: Unidades de Medida
-- =========================================
CREATE TABLE pro_unidades_medida (
    unidad_id SERIAL PRIMARY KEY,
    unidad_nombre VARCHAR(50) NOT NULL,
    -- Ej: 'milímetro', 'pulgada'
    unidad_abreviacion VARCHAR(10) NOT NULL,
    -- Ej: 'mm', 'in'
    unidad_tipo VARCHAR(20) DEFAULT 'longitud',
    unidad_situacion INT DEFAULT 1 -- 1 = activo, 0 = inactivo
);

--MARIN
-- =========================================
-- 🟦 TABLA: Calibres
-- =========================================
CREATE TABLE pro_calibres (
    calibre_id SERIAL PRIMARY KEY,
    calibre_nombre VARCHAR(20) NOT NULL,
    -- Ej: '9', '.45'
    calibre_unidad_id INT NOT NULL,
    calibre_equivalente_mm DECIMAL(6, 2) NULL,
    calibre_situacion INT DEFAULT 1,
    FOREIGN KEY (calibre_unidad_id) REFERENCES pro_unidades_medida(unidad_id)
);







-- =========================================
--  SISTEMA DE INVENTARIO PARA ARMERÍA 
-- =========================================
-- Autor: Marín
-- Fecha: Septiembre 2025
-- Versión: 2.0 - Corregida y Optimizada 

-- ========================
-- TABLAS PRINCIPALES DE INVENTARIO
-- ========================

-- Tabla principal de productos
CREATE TABLE pro_productos (
    producto_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID único del producto',
    producto_nombre VARCHAR(100) NOT NULL COMMENT 'Nombre comercial del producto',
    producto_descripcion TEXT COMMENT 'Descripción detallada del producto',
    pro_codigo_sku VARCHAR(100) UNIQUE NOT NULL COMMENT 'SKU único autogenerado',
    producto_codigo_barra VARCHAR(100) UNIQUE COMMENT 'Código de barra si aplica (puede ser nulo)',
    producto_categoria_id INT NOT NULL COMMENT 'Categoría general (armas, accesorios, etc)',
    producto_subcategoria_id INT NOT NULL COMMENT 'Subcategoría (pistolas, fusiles, etc)',
    producto_marca_id INT NOT NULL COMMENT 'Marca del producto',
    producto_modelo_id INT COMMENT 'Modelo, puede ser nulo si no aplica',
    producto_calibre_id INT COMMENT 'Calibre, puede ser nulo si no aplica',
    producto_madein INT COMMENT 'País de fabricación',
    producto_requiere_serie BOOLEAN DEFAULT FALSE COMMENT 'Indica si requiere número de serie',
    producto_es_importado BOOLEAN DEFAULT FALSE COMMENT 'TRUE si el producto es de importación',
    producto_id_licencia INT NULL COMMENT 'FK a licencia de importación, si aplica',
    producto_stock_minimo INT DEFAULT 0 COMMENT 'Alerta de stock mínimo',
    producto_stock_maximo INT DEFAULT 0 COMMENT 'Stock máximo recomendado',
    producto_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de actualización',

    -- Índices para rendimiento
    INDEX idx_producto_categoria (producto_categoria_id),
    INDEX idx_producto_marca (producto_marca_id),
    INDEX idx_producto_situacion (producto_situacion),
    INDEX idx_producto_codigo (producto_codigo_barra),

    -- Foreign Keys
    FOREIGN KEY (producto_categoria_id) REFERENCES pro_categorias(categoria_id),
    FOREIGN KEY (producto_subcategoria_id) REFERENCES pro_subcategorias(subcategoria_id),
    FOREIGN KEY (producto_marca_id) REFERENCES pro_marcas(marca_id),
    FOREIGN KEY (producto_modelo_id) REFERENCES pro_modelo(modelo_id),
    FOREIGN KEY (producto_calibre_id) REFERENCES pro_calibres(calibre_id), 
    FOREIGN KEY (producto_id_licencia) REFERENCES pro_licencias_para_importacion(lipaimp_id),
    FOREIGN KEY (producto_madein) REFERENCES pro_paises(pais_id),

    -- Validaciones
    CONSTRAINT chk_stock_minimo CHECK (producto_stock_minimo >= 0),
    CONSTRAINT chk_stock_maximo CHECK (producto_stock_maximo >= 0)
) COMMENT='Productos disponibles para venta o control de inventario';

-- ========================
-- FOTOS DE PRODUCTOS
-- ========================

CREATE TABLE pro_productos_fotos (
    foto_id INT AUTO_INCREMENT PRIMARY KEY,
    foto_producto_id INT NOT NULL COMMENT 'FK al producto',
    foto_url VARCHAR(255) NOT NULL COMMENT 'URL o ruta de la imagen',
    foto_alt_text VARCHAR(255) COMMENT 'Texto alternativo para SEO/accesibilidad',
    foto_principal BOOLEAN DEFAULT FALSE COMMENT 'TRUE si es la imagen destacada',
    foto_orden INT DEFAULT 0 COMMENT 'Orden de visualización',
    foto_situacion INT DEFAULT 1 COMMENT '1 = activa, 0 = inactiva',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de subida',

    -- Índices
    INDEX idx_foto_producto (foto_producto_id),
    INDEX idx_foto_principal (foto_principal),
    INDEX idx_foto_orden (foto_orden),

    FOREIGN KEY (foto_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE
) COMMENT='Fotos asociadas a los productos';

-- ========================
-- SERIES INDIVIDUALES
-- ========================

CREATE TABLE pro_series_productos (
    serie_id INT AUTO_INCREMENT PRIMARY KEY,
    serie_producto_id INT NOT NULL COMMENT 'FK al producto',
    serie_numero_serie VARCHAR(200) UNIQUE NOT NULL COMMENT 'Número de serie único',
    serie_estado VARCHAR(25) DEFAULT 'disponible' COMMENT 'disponible, reservado, vendido, baja',
    serie_fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha en que fue ingresado al sistema',
    serie_observaciones VARCHAR(255) COMMENT 'Observaciones específicas de esta serie',
    serie_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = eliminado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices para rendimiento
    INDEX idx_serie_producto (serie_producto_id),
    INDEX idx_serie_estado (serie_estado),
    INDEX idx_serie_numero (serie_numero_serie),

    FOREIGN KEY (serie_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE
) COMMENT='Series individuales de productos que requieren número de serie';

-- ========================
-- LOTES DE PRODUCTOS
-- ========================

CREATE TABLE pro_lotes (
    lote_id INT AUTO_INCREMENT PRIMARY KEY,
    lote_codigo VARCHAR(100) UNIQUE NOT NULL COMMENT 'Código único del lote, ej: L2025-08-GLOCK-001',
    lote_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación o ingreso del lote',
    lote_descripcion VARCHAR(255) NULL COMMENT 'Descripción breve opcional del lote',
    lote_usuario_id INT COMMENT 'Usuario que creó el lote',
    lote_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = cerrado o eliminado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_lote_codigo (lote_codigo),
    INDEX idx_lote_fecha (lote_fecha),
    INDEX idx_lote_usuario (lote_usuario_id),

    FOREIGN KEY (lote_usuario_id) REFERENCES users(user_id)
) COMMENT='Lotes de productos, útil para productos sin serie o importaciones';



-- Agregar las columnas faltantes a la tabla pro_lotes existente
ALTER TABLE pro_lotes 
ADD COLUMN lote_producto_id INT NOT NULL COMMENT 'FK al producto específico' AFTER lote_codigo,
ADD COLUMN lote_cantidad_total INT DEFAULT 0 COMMENT 'Cantidad total en este lote' AFTER lote_descripcion,
ADD COLUMN lote_cantidad_disponible INT DEFAULT 0 COMMENT 'Cantidad disponible en este lote' AFTER lote_cantidad_total;

-- Corregir el tipo de dato de lote_producto_id para que coincida con producto_id
ALTER TABLE pro_lotes 
MODIFY COLUMN lote_producto_id bigint unsigned NOT NULL COMMENT 'FK al producto específico';


-- Agregar índices para las nuevas columnas
ALTER TABLE pro_lotes 
ADD INDEX idx_lote_producto (lote_producto_id),
ADD INDEX idx_lote_cantidad_total (lote_cantidad_total),
ADD INDEX idx_lote_cantidad_disponible (lote_cantidad_disponible);

-- Agregar la foreign key al producto
ALTER TABLE pro_lotes 
ADD CONSTRAINT fk_lote_producto 
FOREIGN KEY (lote_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE;

-- Agregar constraints de validación
ALTER TABLE pro_lotes 
ADD CONSTRAINT chk_lote_cantidad_total_positiva CHECK (lote_cantidad_total >= 0),
ADD CONSTRAINT chk_lote_cantidad_disponible_positiva CHECK (lote_cantidad_disponible >= 0),
ADD CONSTRAINT chk_cantidad_disponible_menor_igual_total CHECK (lote_cantidad_disponible <= lote_cantidad_total);




-- ========================
-- TABLA DE ASIGNACIÓN LICENCIA-PRODUCTO
-- ========================
-- Conecta productos del inventario con licencias específicas

CREATE TABLE pro_licencia_asignacion_producto (
    asignacion_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asignacion_producto_id  BIGINT UNSIGNED NOT NULL COMMENT 'FK al producto del inventario',
    asignacion_licencia_id  BIGINT UNSIGNED NOT NULL COMMENT 'FK a la licencia de importación',
    asignacion_cantidad INT NOT NULL COMMENT 'Cantidad de este producto en esta licencia',
    asignacion_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Foreign Keys
    FOREIGN KEY (asignacion_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE,
    FOREIGN KEY (asignacion_licencia_id) REFERENCES pro_licencias_para_importacion(lipaimp_id) ON DELETE CASCADE,
    
    -- Validaciones
    CONSTRAINT chk_asignacion_cantidad_positiva CHECK (asignacion_cantidad > 0),
    
    -- ESTO ES LO QUE TE FALTABA:
    UNIQUE KEY unique_producto_licencia (asignacion_producto_id, asignacion_licencia_id)
) COMMENT='Asignación de productos específicos a licencias de importación';
-- ========================
-- PRECIOS DE PRODUCTOS
-- ========================

CREATE TABLE pro_precios (
    precio_id INT AUTO_INCREMENT PRIMARY KEY,
    precio_producto_id INT NOT NULL COMMENT 'FK al producto',
    precio_costo DECIMAL(10,2) NOT NULL COMMENT 'Precio de compra del producto',
    precio_venta DECIMAL(10,2) NOT NULL COMMENT 'Precio regular de venta',
    precio_margen DECIMAL(5,2) DEFAULT NULL COMMENT 'Margen de ganancia estimado (%)',
    precio_especial DECIMAL(10,2) DEFAULT NULL COMMENT 'Precio especial, si se aplica',
    precio_moneda VARCHAR(3) DEFAULT 'GTQ' COMMENT 'Código de moneda ISO',
    precio_justificacion VARCHAR(255) DEFAULT NULL COMMENT 'Motivo del precio especial (descuento, promoción, etc)',
    precio_fecha_asignacion DATE NOT NULL DEFAULT (CURRENT_DATE) COMMENT 'Fecha en que se asignó este precio',
    precio_usuario_id INT COMMENT 'Usuario que asignó el precio',
    precio_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = histórico o inactivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices para consultas rápidas
    INDEX idx_precio_producto_fecha (precio_producto_id, precio_fecha_asignacion),
    INDEX idx_precio_situacion (precio_situacion),
    INDEX idx_precio_usuario (precio_usuario_id),

    -- Foreign Keys
    FOREIGN KEY (precio_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE,
    FOREIGN KEY (precio_usuario_id) REFERENCES users(user_id),

    -- Validaciones
    CONSTRAINT chk_precio_costo_positivo CHECK (precio_costo > 0),
    CONSTRAINT chk_precio_venta_positivo CHECK (precio_venta > 0),
    CONSTRAINT chk_precio_especial_positivo CHECK (precio_especial IS NULL OR precio_especial >= 0)
) COMMENT='Precios por producto incluyendo costo, venta, y especiales';

-- ========================
-- PROMOCIONES TEMPORALES
-- ========================

CREATE TABLE pro_promociones (
    promo_id INT AUTO_INCREMENT PRIMARY KEY,
    promo_producto_id INT NOT NULL COMMENT 'FK al producto promocionado',
    promo_nombre VARCHAR(100) NOT NULL COMMENT 'Nombre de la promoción, ej: Black Friday',
    promo_tipo VARCHAR(20) NOT NULL DEFAULT 'porcentaje' COMMENT 'porcentaje o fijo',
    promo_valor DECIMAL(10,2) NOT NULL COMMENT 'Valor del descuento, ej: 25.00 = 25% si es porcentaje',
    promo_precio_original DECIMAL(10,2) COMMENT 'Precio antes del descuento (solo para mostrar)',
    promo_precio_descuento DECIMAL(10,2) COMMENT 'Precio final con descuento',
    promo_fecha_inicio DATE NOT NULL COMMENT 'Inicio de la promoción',
    promo_fecha_fin DATE NOT NULL COMMENT 'Fin de la promoción',
    promo_justificacion VARCHAR(255) COMMENT 'Motivo de la promoción',
    promo_usuario_id INT COMMENT 'Usuario que creó la promoción',
    promo_situacion INT DEFAULT 1 COMMENT '1 = activa, 0 = expirada o desactivada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_promo_producto (promo_producto_id),
    INDEX idx_promo_fechas (promo_fecha_inicio, promo_fecha_fin),
    INDEX idx_promo_situacion (promo_situacion),

    -- Foreign Keys
    FOREIGN KEY (promo_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE,
    FOREIGN KEY (promo_usuario_id) REFERENCES users(user_id),

    -- Validaciones
    CONSTRAINT chk_promo_fechas CHECK (promo_fecha_fin >= promo_fecha_inicio),
    CONSTRAINT chk_promo_valor_positivo CHECK (promo_valor > 0)
) COMMENT='Promociones temporales activadas sobre productos';

-- ========================
-- MOVIMIENTOS DE INVENTARIO
-- ========================

CREATE TABLE pro_movimientos (
    mov_id INT AUTO_INCREMENT PRIMARY KEY,
    mov_producto_id INT NOT NULL COMMENT 'FK al producto involucrado',
    mov_tipo VARCHAR(50) NOT NULL COMMENT 'ingreso, egreso, ajuste_positivo, ajuste_negativo, venta, devolucion, merma, transferencia',
    mov_origen VARCHAR(100) COMMENT 'Fuente del movimiento: compra, importación, venta, etc.',
    mov_destino VARCHAR(100) COMMENT 'Destino del movimiento si aplica',
    mov_cantidad INT NOT NULL COMMENT 'Cantidad afectada por el movimiento',
    mov_precio_unitario DECIMAL(10,2) COMMENT 'Precio unitario en el momento del movimiento',
    mov_valor_total DECIMAL(10,2) COMMENT 'Valor total del movimiento',
    mov_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha del movimiento',
    mov_usuario_id INT NOT NULL COMMENT 'Usuario que realizó el movimiento',
    mov_lote_id INT COMMENT 'FK al lote si aplica',
    mov_serie_id INT COMMENT 'FK a la serie específica si aplica',
    mov_documento_referencia VARCHAR(100) COMMENT 'Número de factura, orden, etc.',
    mov_observaciones VARCHAR(250) COMMENT 'Detalles u observaciones del movimiento',
    mov_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = anulado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices para consultas rápidas
    INDEX idx_mov_producto_fecha (mov_producto_id, mov_fecha),
    INDEX idx_mov_tipo_fecha (mov_tipo, mov_fecha),
    INDEX idx_mov_usuario_fecha (mov_usuario_id, mov_fecha),
    INDEX idx_mov_lote (mov_lote_id),
    INDEX idx_mov_serie (mov_serie_id),
    INDEX idx_mov_situacion (mov_situacion),

    -- Foreign Keys
    FOREIGN KEY (mov_producto_id) REFERENCES pro_productos(producto_id),
    FOREIGN KEY (mov_usuario_id) REFERENCES users(user_id),
    FOREIGN KEY (mov_lote_id) REFERENCES pro_lotes(lote_id),
    FOREIGN KEY (mov_serie_id) REFERENCES pro_series_productos(serie_id),

    -- Validaciones
    CONSTRAINT chk_mov_cantidad_positiva CHECK (mov_cantidad > 0),
    CONSTRAINT chk_mov_precio_positivo CHECK (mov_precio_unitario IS NULL OR mov_precio_unitario >= 0)
) COMMENT='Historial completo de movimientos de inventario';

-- ========================
-- TABLA DE STOCK ACTUAL
-- ========================

CREATE TABLE pro_stock_actual (
    stock_id INT AUTO_INCREMENT PRIMARY KEY,
    stock_producto_id INT NOT NULL,
    stock_cantidad_total INT DEFAULT 0 COMMENT 'Stock total del producto',
    stock_cantidad_disponible INT DEFAULT 0 COMMENT 'Stock disponible para venta',
    stock_cantidad_reservada INT DEFAULT 0 COMMENT 'Stock reservado/apartado',
    stock_valor_total DECIMAL(12,2) DEFAULT 0 COMMENT 'Valor total del inventario',
    stock_ultimo_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_stock_producto (stock_producto_id),
    INDEX idx_stock_disponible (stock_cantidad_disponible),

    FOREIGN KEY (stock_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE,
    UNIQUE KEY unique_producto_stock (stock_producto_id),

    -- Validaciones
    CONSTRAINT chk_stock_total_positivo CHECK (stock_cantidad_total >= 0),
    CONSTRAINT chk_stock_disponible_positivo CHECK (stock_cantidad_disponible >= 0),
    CONSTRAINT chk_stock_reservado_positivo CHECK (stock_cantidad_reservada >= 0)
) COMMENT='Stock actual por producto para consultas rápidas';

-- ========================
-- SISTEMA DE ALERTAS Y NOTIFICACIONES
-- ========================
-- Combina tabla separada de roles + campo "para todos"

-- Tabla principal de alertas
CREATE TABLE pro_alertas (
    alerta_id INT AUTO_INCREMENT PRIMARY KEY,
    alerta_tipo VARCHAR(50) NOT NULL COMMENT 'stock_bajo, stock_agotado, etc.',
    alerta_titulo VARCHAR(100) NOT NULL COMMENT 'Título de la alerta',
    alerta_mensaje TEXT NOT NULL COMMENT 'Mensaje detallado',
    alerta_prioridad VARCHAR(20) DEFAULT 'media' COMMENT 'baja, media, alta, critica',
    
    -- Solo lo esencial
    alerta_producto_id INT NULL COMMENT 'Producto relacionado si aplica',
    alerta_usuario_id INT NULL COMMENT 'Usuario específico si aplica',
    
    -- NUEVO: Campo para todos los roles
    alerta_para_todos BOOLEAN DEFAULT FALSE COMMENT 'TRUE = todos los roles pueden verla, FALSE = solo roles específicos',
    
    -- Control simple
    alerta_vista BOOLEAN DEFAULT FALSE COMMENT 'Si ya fue vista',
    alerta_resuelta BOOLEAN DEFAULT FALSE COMMENT 'Si fue resuelta',
    alerta_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo se generó',
    
    -- Email simple
    email_enviado BOOLEAN DEFAULT FALSE COMMENT 'Si se envió email',
    
    -- Índices básicos
    INDEX idx_alerta_tipo (alerta_tipo),
    INDEX idx_alerta_vista (alerta_vista),
    INDEX idx_alerta_producto (alerta_producto_id),
    INDEX idx_alerta_para_todos (alerta_para_todos),
    
    -- Foreign Keys básicas
    FOREIGN KEY (alerta_producto_id) REFERENCES pro_productos(producto_id) ON DELETE CASCADE,
    FOREIGN KEY (alerta_usuario_id) REFERENCES users(user_id) ON DELETE SET NULL
    
) COMMENT='Sistema de alertas con roles específicos o para todos';

-- Tabla de relación: alertas específicas por roles
CREATE TABLE pro_alertas_roles (
    alerta_rol_id INT AUTO_INCREMENT PRIMARY KEY,
    alerta_id INT NOT NULL,
    rol_id INT NOT NULL,
    
    FOREIGN KEY (alerta_id) REFERENCES pro_alertas(alerta_id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_alerta_rol (alerta_id, rol_id)
) COMMENT='Roles específicos que pueden ver cada alerta';

-- ========================
-- COMENTARIOS FINALES INVENTARIO
-- ========================



-- VALORES RECOMENDADOS:

-- serie_estado: 'disponible', 'reservado', 'vendido', 'baja'
-- mov_tipo: 'ingreso', 'egreso', 'ajuste_positivo', 'ajuste_negativo', 'venta', 'devolucion', 'merma', 'transferencia'
-- promo_tipo: 'porcentaje', 'fijo'
-- alerta_tipo: 'stock_bajo', 'stock_agotado', 'precio_vencido', 'serie_duplicada'
-- alerta_prioridad: 'baja', 'media', 'alta', 'critica'
-- alerta_estado: 'pendiente', 'vista', 'resuelta', 'ignorada'
-- */


-- ========================
-- EMPRESAS E IMPORTACIONES
-- ========================
--carlos
CREATE TABLE pro_empresas_de_importacion (
    empresaimp_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID empresa importadora',
    empresaimp_pais INT NOT NULL COMMENT 'ID del país asociado',
    empresaimp_descripcion VARCHAR(50) COMMENT 'tipo: empresa matriz o logística',
    empresaimp_situacion INT DEFAULT 1 COMMENT '1 = activa, 0 = inactiva',
    FOREIGN KEY (empresaimp_pais) REFERENCES pro_paises(pais_id)
);

--MARIN-- =========================================
-- 🟦 TABLA: Licencias de Importación
-- =========================================
CREATE TABLE pro_licencias_para_importacion (
    lipaimp_id SERIAL PRIMARY KEY,
    lipaimp_poliza INT,
    lipaimp_descripcion VARCHAR(100),
    lipaimp_empresa INT NOT NULL,
    lipaimp_fecha_vencimiento DATE,
    lipaimp_situacion INT DEFAULT 1,
    ----- 1 pendiente  2 autorizado 3 rechazado
    FOREIGN KEY (lipaimp_empresa) REFERENCES pro_empresas_de_importacion(empresaimp_id)
);

--MARIN
-- =========================================
-- 🟦 TABLA: Armas Licenciadas (relacionadas a la licencia)
-- =========================================
CREATE TABLE pro_armas_licenciadas (
    arma_id SERIAL PRIMARY KEY,
    arma_licencia_id INT NOT NULL,
    arma_clase_id INT NOT NULL,
    arma_marca_id INT NOT NULL,
    arma_modelo_id INT NOT NULL,
    arma_calibre_id INT NOT NULL,
    arma_cantidad INT DEFAULT 1,
    arma_situacion INT DEFAULT 1,
    FOREIGN KEY (arma_licencia_id) REFERENCES pro_licencias_para_importacion(lipaimp_id),
    FOREIGN KEY (arma_clase_id) REFERENCES pro_clases_pistolas(clase_id), ----aqui tiene que referenciar a categorias y subcategorias
    FOREIGN KEY (arma_marca_id) REFERENCES pro_marcas(marca_id),
    FOREIGN KEY (arma_modelo_id) REFERENCES pro_modelo(modelo_id),
    FOREIGN KEY (arma_calibre_id) REFERENCES pro_calibres(calibre_id)
);

-- ========================
-- PAGOS DE LICENCIAS
-- ========================
CREATE TABLE pro_pagos_licencias (
    pago_id INT AUTO_INCREMENT PRIMARY KEY,
    pago_licencia_id INT NOT NULL,
    pago_empresa_id INT NOT NULL,
    pago_fecha DATE NOT NULL,
    pago_monto DECIMAL(10, 2) NOT NULL,
    pago_metodo INT NOT NULL,
    pago_verificado VARCHAR(50) DEFAULT 'no aprobada',
    pago_concepto VARCHAR(250),
    CONSTRAINT chk_pago_verificado CHECK (pago_verificado IN ('aprobada', 'no aprobada')),
    FOREIGN KEY (pago_licencia_id) REFERENCES pro_licencias_para_importacion(lipaimp_id),
    FOREIGN KEY (pago_empresa_id) REFERENCES pro_empresas_de_importacion(empresaimp_id),
    FOREIGN KEY (pago_metodo) REFERENCES pro_metodos_pago(metpago_id)
);

CREATE TABLE pro_comprobantes_pago (
    comprob_id INT AUTO_INCREMENT PRIMARY KEY,
    comprob_ruta VARCHAR(50),
    comprob_pagos_licencia INT,
    comprob_situacion INT DEFAULT 1,
    FOREIGN KEY (comprob_pagos_licencia) REFERENCES pro_pagos_licencias(pago_id)
);

CREATE TABLE pro_documentacion_lic_import (
    doclicimport_id INT AUTO_INCREMENT PRIMARY KEY,
    doclicimport_ruta VARCHAR(50) NOT NULL,
    doclicimport_num_lic INT,
    doclicimport_situacion INT DEFAULT 1,
    FOREIGN KEY (doclicimport_num_lic) REFERENCES pro_licencias_para_importacion(lipaimp_id)
);

-----MARIN ----
-----
--hugo ----pendiente solo manejar situaciones en licencia  ----  ----- 1 pendiente  2 autorizado 3 rechazado
-- CREATE TABLE pro_digecam (
--     digecam_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID digecam',
--     digecam_licencia_import INT NOT NULL COMMENT 'Licencia asociada',
--     digecam_autorizacion VARCHAR(50) DEFAULT 'no aprobada' COMMENT 'Estado autorización',
--     digecam_situacion INT DEFAULT 1 COMMENT '1 = activa, 0 = inactiva',
--     CONSTRAINT chk_digecam_autorizacion CHECK (digecam_autorizacion IN ('aprobada','no aprobada')),
--     FOREIGN KEY (digecam_licencia_import) REFERENCES pro_licencias_para_importacion(lipaimp_id)
-- );
-- ========================
-- INVENTARIO 
-- ========================

-- Catálogo de lotes/modelos
CREATE TABLE pro_inventario_modelos (
    modelo_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID del modelo/lote',
    modelo_licencia INT NOT NULL COMMENT 'Licencia de importación asociada',
    modelo_poliza INT NOT NULL COMMENT 'No. de póliza/factura de compra',
    modelo_fecha_ingreso DATE NOT NULL COMMENT 'Fecha de ingreso del lote',
    modelo_clase INT NOT NULL,
    modelo_marca INT NOT NULL,
    modelo_modelo INT NOT NULL,
    modelo_calibre VARCHAR(50),
    modelo_cantidad INT NOT NULL DEFAULT 0 COMMENT 'Cantidad total en este lote',
    modelo_disponible INT NOT NULL DEFAULT 0 COMMENT 'Stock disponible',
    modelo_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo',
    FOREIGN KEY (modelo_licencia) REFERENCES pro_licencias_para_importacion(lipaimp_id),
    FOREIGN KEY (modelo_clase) REFERENCES pro_clases_pistolas(clase_id),
    FOREIGN KEY (modelo_marca) REFERENCES pro_marcas(marca_id),
    FOREIGN KEY (modelo_modelo) REFERENCES pro_modelo(modelo_id)
);

-- Armas individuales con serie
CREATE TABLE pro_inventario_armas (
    arma_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID correlativo',
    arma_modelo_id INT NOT NULL COMMENT 'Referencia al lote o modelo',
    arma_numero_serie VARCHAR(200) UNIQUE COMMENT 'Número de serie de la pistola',
    arma_estado ENUM('disponible', 'vendida', 'reservada', 'baja') DEFAULT 'disponible',
    FOREIGN KEY (arma_modelo_id) REFERENCES pro_inventario_modelos(modelo_id)
);
=======

-- ========================
-- CLIENTES Y VENTAS
-- ========================
CREATE TABLE pro_clientes (
    cliente_id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('empresa', 'persona') NOT NULL,
    nombre_empresa VARCHAR(200),
    nombre VARCHAR(200) NOT NULL COMMENT 'NOMBRE DEL DUENO DE LA EMPRESA O PERSONA INDIVIDUAL',
    razon_social VARCHAR(200),
    -- solo para empresas
    ubicacion VARCHAR(100),
    situacion INT DEFAULT 1
);

-- Ventas solo referencian cliente_id
-- jovenes hice este cambio en la db   
CREATE TABLE pro_ventas (
    venta_id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NULL,
    nombre_persona VARCHAR(200),
    -- solo se llena si no hay cliente_id
    factura VARCHAR(200),
    fecha DATE NOT NULL,
    autorizacion INT NOT NULL,
    situacion INT DEFAULT 1,
    observaciones VARCHAR(200),
    FOREIGN KEY (cliente_id) REFERENCES pro_clientes(cliente_id)
);


 
 


CREATE TABLE pro_detalle_venta (
    detalle_id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    modelo_id INT COMMENT 'Si la venta es por lote/cantidad',
    arma_id INT COMMENT 'Si la venta es por arma única',
    cantidad INT DEFAULT 1,
    precio_unitario DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES pro_ventas(venta_id),
    FOREIGN KEY (modelo_id) REFERENCES pro_inventario_modelos(modelo_id),
    FOREIGN KEY (arma_id) REFERENCES pro_inventario_armas(arma_id)
);

-- ========================
-- PAGOS DE VENTAS
-- ========================
CREATE TABLE pro_pagos (
    pago_id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    venta_tipo ENUM('empresa', 'persona') NOT NULL,
    pago_fecha DATE NOT NULL,
    pago_monto DECIMAL(12, 2) NOT NULL,
    pago_metodo INT NOT NULL,
    pago_num_cuota INT DEFAULT 1,
    FOREIGN KEY (pago_metodo) REFERENCES pro_metodos_pago(metpago_id)
);

CREATE TABLE pro_comprobantes_pago_ventas (
    comprobventas_id INT AUTO_INCREMENT PRIMARY KEY,
    comprobventas_ruta VARCHAR(255) NOT NULL,
    comprobventas_pago_id INT NOT NULL,
    comprobventas_situacion TINYINT DEFAULT 1,
    FOREIGN KEY (comprobventas_pago_id) REFERENCES pro_pagos(pago_id)
);

-- TABLAS DE UBICACIONES  Y VISITAS DE USUARIOS 
CREATE TABLE users_ubicaciones (
    ubi_id INT AUTO_INCREMENT PRIMARY KEY,
    ubi_user INT NOT NULL,
    ubi_latitud DECIMAL(9, 6) NOT NULL,
    ubi_longitud DECIMAL(9, 6) NOT NULL,
    ubi_descripcion VARCHAR(255),
    FOREIGN KEY (ubi_user) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE users_visitas (
    visita_id INT AUTO_INCREMENT PRIMARY KEY,
    visita_user INT NOT NULL,
    visita_fecha DATETIME NULL,
    visita_estado INT NOT NULL,      -- 1: Visitado no comprado, 2: Visitado comprado, 3: No visitado
    visita_venta DECIMAL(10, 2) DEFAULT 0,
    visita_descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (visita_user) REFERENCES users(user_id)
);

CREATE TABLE users_historial_visitas (
    hist_id INT AUTO_INCREMENT PRIMARY KEY,
    hist_visita_id INT NOT NULL,
    hist_fecha_actualizacion DATETIME NOT NULL,
    hist_estado_anterior INT,
    hist_estado_nuevo INT,
    hist_total_venta_anterior DECIMAL(10, 2),
    hist_total_venta_nuevo DECIMAL(10, 2),
    hist_descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hist_visita_id) REFERENCES users_visitas(visita_id)
);










-- ========================
-- TRIGGERS PARA GENERAR ALERTAS AUTOMÁTICAS
-- ========================

DELIMITER //

-- Trigger para alertas de stock bajo
CREATE TRIGGER tr_alerta_stock_bajo
AFTER UPDATE ON pro_stock_actual
FOR EACH ROW
BEGIN
    DECLARE producto_nombre VARCHAR(100);
    DECLARE stock_minimo INT;
    DECLARE sku_producto VARCHAR(100);
    
    IF NEW.stock_cantidad_disponible != OLD.stock_cantidad_disponible THEN
        
        SELECT p.producto_nombre, p.producto_stock_minimo, p.pro_codigo_sku
        INTO producto_nombre, stock_minimo, sku_producto
        FROM pro_productos p 
        WHERE p.producto_id = NEW.stock_producto_id;
        
        -- Alerta de stock bajo
        IF NEW.stock_cantidad_disponible <= stock_minimo AND stock_minimo > 0 THEN
            
            IF NOT EXISTS (
                SELECT 1 FROM sys_alertas_notificaciones 
                WHERE alerta_producto_id = NEW.stock_producto_id 
                AND alerta_tipo = 'stock_bajo' 
                AND alerta_estado IN ('pendiente', 'vista')
            ) THEN
                
                INSERT INTO sys_alertas_notificaciones (
                    alerta_tipo,
                    alerta_titulo,
                    alerta_mensaje,
                    alerta_prioridad,
                    alerta_producto_id,
                    alerta_datos
                ) VALUES (
                    'stock_bajo',
                    CONCAT('Stock bajo: ', producto_nombre),
                    CONCAT('El producto "', producto_nombre, '" (SKU: ', sku_producto, ') tiene stock bajo. Stock actual: ', 
                           NEW.stock_cantidad_disponible, ', Stock mínimo: ', stock_minimo),
                    CASE 
                        WHEN NEW.stock_cantidad_disponible = 0 THEN 'critica'
                        WHEN NEW.stock_cantidad_disponible <= (stock_minimo * 0.5) THEN 'alta'
                        ELSE 'media'
                    END,
                    NEW.stock_producto_id,
                    JSON_OBJECT(
                        'stock_actual', NEW.stock_cantidad_disponible,
                        'stock_minimo', stock_minimo,
                        'producto_nombre', producto_nombre,
                        'sku', sku_producto
                    )
                );
                
            END IF;
        END IF;
        
        -- Alerta de stock agotado
        IF NEW.stock_cantidad_disponible = 0 AND OLD.stock_cantidad_disponible > 0 THEN
            
            INSERT INTO sys_alertas_notificaciones (
                alerta_tipo,
                alerta_titulo,
                alerta_mensaje,
                alerta_prioridad,
                alerta_producto_id,
                alerta_datos
            ) VALUES (
                'stock_agotado',
                CONCAT('¡AGOTADO! ', producto_nombre),
                CONCAT('El producto "', producto_nombre, '" (SKU: ', sku_producto, ') se ha AGOTADO completamente.'),
                'critica',
                NEW.stock_producto_id,
                JSON_OBJECT(
                    'producto_nombre', producto_nombre,
                    'sku', sku_producto,
                    'stock_anterior', OLD.stock_cantidad_disponible
                )
            );
            
        END IF;
    END IF;
END //

DELIMITER ;

-- ========================
-- VISTAS ÚTILES
-- ========================

-- Vista de alertas pendientes con información completa
CREATE VIEW v_alertas_pendientes AS
SELECT 
    a.alerta_id,
    a.alerta_tipo,
    a.alerta_titulo,
    a.alerta_mensaje,
    a.alerta_prioridad,
    a.alerta_estado,
    a.alerta_fecha_generacion,
    
    -- Información del producto si aplica
    p.producto_nombre,
    p.pro_codigo_sku,
    
    -- Información del usuario si aplica
    CONCAT(u.user_primer_nombre, ' ', u.user_primer_apellido) as usuario_nombre,
    
    -- Stock actual si es alerta de stock
    sa.stock_cantidad_disponible,
    
    -- Datos adicionales
    a.alerta_datos,
    
    -- Control de emails
    a.alerta_enviar_email,
    a.alerta_email_enviado,
    
    -- Tiempo transcurrido
    TIMESTAMPDIFF(MINUTE, a.alerta_fecha_generacion, NOW()) as minutos_transcurridos

FROM sys_alertas_notificaciones a
LEFT JOIN pro_productos p ON a.alerta_producto_id = p.producto_id
LEFT JOIN users u ON a.alerta_usuario_id = u.user_id
LEFT JOIN pro_stock_actual sa ON a.alerta_producto_id = sa.stock_producto_id
WHERE a.alerta_estado IN ('pendiente', 'vista')
ORDER BY 
    FIELD(a.alerta_prioridad, 'critica', 'alta', 'media', 'baja'),
    a.alerta_fecha_generacion DESC;