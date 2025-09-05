
--marin
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


----marin

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


--SERGIO
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
    unidad_nombre VARCHAR(50) NOT NULL,       -- Ej: 'milímetro', 'pulgada'
    unidad_abreviacion VARCHAR(10) NOT NULL,  -- Ej: 'mm', 'in'
    unidad_tipo VARCHAR(20) DEFAULT 'longitud',
    unidad_situacion INT DEFAULT 1            -- 1 = activo, 0 = inactivo
);
--MARIN
-- =========================================
-- 🟦 TABLA: Calibres
-- =========================================
CREATE TABLE pro_calibres (
    calibre_id SERIAL PRIMARY KEY,
    calibre_nombre VARCHAR(20) NOT NULL,         -- Ej: '9', '.45'
    calibre_unidad_id INT NOT NULL,
    calibre_equivalente_mm DECIMAL(6,2) NULL,
    calibre_situacion INT DEFAULT 1,

    FOREIGN KEY (calibre_unidad_id) REFERENCES pro_unidades_medida(unidad_id)
);







-------------INVENTARIO -------------
------------////////////////////////------

--MARIN
CREATE TABLE pro_productos (
    producto_id INT AUTO_INCREMENT PRIMARY KEY,
    producto_nombre VARCHAR(100) NOT NULL,
    producto_codigo_barra VARCHAR(100) UNIQUE, -- si aplica
    producto_categoria_id INT NOT NULL,
    producto_subcategoria_id INT NOT NULL,
    producto_marca_id INT NOT NULL,
    producto_modelo_id INT,         -- NULL si no aplica
    producto_calibre_id INT,        -- NULL si no aplica
    producto_requiere_serie BOOLEAN DEFAULT FALSE,
    producto_es_importado BOOLEAN DEFAULT FALSE, -- true = importación, false = compra local
    producto_id_licencia INT NULL, -- Si viene de importación, se guarda aquí el ID de la licencia
    producto_situacion INT DEFAULT 1,  -- 1 = activo, 0 = inactivo
    FOREIGN KEY (producto_categoria_id) REFERENCES pro_categorias(categoria_id),
    FOREIGN KEY (producto_subcategoria_id) REFERENCES pro_subcategorias(subcategoria_id),
    FOREIGN KEY (producto_marca_id) REFERENCES pro_marcas(marca_id),
    FOREIGN KEY (producto_modelo_id) REFERENCES pro_modelo(modelo_id),
    FOREIGN KEY (producto_calibre_id) REFERENCES pro_calibres(calibre_id), 
    FOREIGN KEY (producto_id_licencia) REFERENCES pro_licencias_para_importacion(lipaimp_id)
);

--MARIN
CREATE TABLE pro_productos_fotos (
    foto_id INT AUTO_INCREMENT PRIMARY KEY,
    foto_producto_id INT NOT NULL,
    foto_url VARCHAR(255) NOT NULL,
    foto_principal BOOLEAN DEFAULT FALSE,
    foto_situacion INT DEFAULT 1, 
    FOREIGN KEY (producto_id) REFERENCES pro_productos(producto_id)
);

--MARIN
CREATE TABLE pro_series_productos (
    serie_id INT AUTO_INCREMENT PRIMARY KEY,
    serie_producto_id INT NOT NULL,
    serie_numero_serie VARCHAR(200) UNIQUE NOT NULL,
    serie_estado ENUM('disponible','reservado','vendido','baja') DEFAULT 'disponible',
    serie_fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    serie_situacion INT DEFAULT 1, 
    FOREIGN KEY (serie_producto_id) REFERENCES pro_productos(producto_id)
);
--MARIN
CREATE TABLE pro_lotes (
    lote_id INT AUTO_INCREMENT PRIMARY KEY,
    lote_codigo VARCHAR(100) UNIQUE NOT NULL, -- Ej: 'L2025-08-GLOCK-001'
    lote_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lote_descripcion VARCHAR(255) NULL, 
    lote_situacion INT DEFAULT 1
);

--MARIN
CREATE TABLE pro_movimientos (
    mov_id INT AUTO_INCREMENT PRIMARY KEY,
    mov_producto_id INT NOT NULL,
    mov_tipo NOT NULL,  --ingreso -- 
    mov_origen VARCHAR(100),     -- importación, ajuste, venta, compra local, etc.
    mov_cantidad INT NOT NULL,
    mov_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mov_usuario_id INT NOT NULL,
    mov_lote_id INT, -- NULL si no aplica
    mov_observaciones VARCHAR(250),
    mov_situacion INT DEFAULT 1, 
    FOREIGN KEY (mov_producto_id) REFERENCES pro_productos(producto_id),
    FOREIGN KEY (mov_usuario_id) REFERENCES users(id), 
    FOREIGN KEY (mov_lote_id) REFERENCES pro_lotes(lote_id)
);






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
    lipaimp_situacion INT DEFAULT 1,    ----- 1 pendiente  2 autorizado 3 rechazado

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
    FOREIGN KEY (arma_clase_id) REFERENCES pro_clases_pistolas(clase_id),
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
    pago_monto DECIMAL(10,2) NOT NULL,
    pago_metodo INT NOT NULL,
    pago_verificado VARCHAR(50) DEFAULT 'no aprobada',
    pago_concepto VARCHAR(250),
    CONSTRAINT chk_pago_verificado CHECK (pago_verificado IN ('aprobada','no aprobada')),
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


-- ========================
-- CLIENTES Y VENTAS
-- ========================

CREATE TABLE pro_clientes (
    cliente_id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('empresa','persona') NOT NULL,
    nombre_empresa VARCHAR(200) ,
    nombre VARCHAR(200)  NOT NULL COMMENT 'NOMBRE DEL DUENO DE LA EMPRESA O PERSONA INDIVIDUAL',
    razon_social VARCHAR(200), -- solo para empresas
    ubicacion VARCHAR(100),
    situacion INT DEFAULT 1
);

-- Ventas solo referencian cliente_id
-- jovenes hice este cambio en la db   

CREATE TABLE pro_ventas (
    venta_id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NULL,
    nombre_persona VARCHAR(200),  -- solo se llena si no hay cliente_id
    factura VARCHAR(200),
    fecha DATE NOT NULL,
    autorizacion INT NOT NULL,
    situacion INT DEFAULT 1,
    observaciones VARCHAR(200),
    FOREIGN KEY (cliente_id) REFERENCES pro_clientes(cliente_id)
);   
--  le agregue un campo    tambien agregue esta tabla para poder guardar las fotos de las armas   
 
 


CREATE TABLE pro_detalle_venta (
    detalle_id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    modelo_id INT COMMENT 'Si la venta es por lote/cantidad',
    arma_id INT COMMENT 'Si la venta es por arma única',
    cantidad INT DEFAULT 1,
    precio_unitario DECIMAL(12,2) NOT NULL,
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
    venta_tipo ENUM('empresa','persona') NOT NULL,
    pago_fecha DATE NOT NULL,
    pago_monto DECIMAL(12,2) NOT NULL,
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

