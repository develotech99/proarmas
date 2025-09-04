
--marin
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
----marin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT DEFAULT NULL,  -- ya no UNSIGNED
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL
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
CREATE TABLE pro_paises (
    pais_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de país',
    pais_descripcion VARCHAR(50) COMMENT 'Descripción del país',
    pais_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
);

--marin 

CREATE TABLE pro_categorias (
    categoria_id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_nombre VARCHAR(100) NOT NULL,  ----ejemplo armas, accessorios, municiones, etc.. 
    categoria_situacion INT DEFAULT 1
);


CREATE TABLE pro_subcategorias (
    subcategoria_id INT AUTO_INCREMENT PRIMARY KEY,
    subcategoria_nombre VARCHAR(100) NOT NULL, --ejemplo pistola, fusil, chaleco, mira, etc.... 
    subcategoria_idcategoria INT NOT NULL,
    subcategoria_situacion INT DEFAULT 1,
    FOREIGN KEY (subcategoria_idcategoria) REFERENCES pro_categorias(categoria_id)
);



--sergio
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
modelo_marca_id INT NOT NULL,
FOREIGN KEY (modelo_marca_id) REFERENCES pro_marcas(marca_id)

);

----aquí hay una sugerencia agregar a pro_modelo



Glock => Glock 17, Glock 19
CZ => CZ P-10, CZ 75
--MARIN 
-- =========================================
-- TABLA: Unidades de Medida
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
--  TABLA: Calibres
-- =========================================
CREATE TABLE pro_calibres (
    calibre_id SERIAL PRIMARY KEY,
    calibre_nombre VARCHAR(20) NOT NULL,         -- Ej: '9', '.45'
    calibre_unidad_id INT NOT NULL,
    calibre_equivalente_mm DECIMAL(6,2) NULL,
    calibre_situacion INT DEFAULT 1,

    FOREIGN KEY (calibre_unidad_id) REFERENCES pro_unidades_medida(unidad_id)
);



CREATE TABLE pro_productos (
    producto_id INT AUTO_INCREMENT PRIMARY KEY,
    producto_nombre VARCHAR(100) NOT NULL,
    producto_categoria_id INT NOT NULL,
    producto_subcategoria_id INT NOT NULL,
    producto_marca_id INT NOT NULL,
    producto_modelo_id INT,         -- NULL si no aplica
    producto_calibre_id INT,        -- NULL si no aplica
    producto_es_arma BOOLEAN DEFAULT FALSE,
    producto_requiere_serie BOOLEAN DEFAULT FALSE,
    producto_situacion INT DEFAULT 1,  -- 1 = activo, 0 = inactivo

    FOREIGN KEY (producto_categoria_id) REFERENCES pro_categorias(categoria_id),
    FOREIGN KEY (producto_subcategoria_id) REFERENCES pro_subcategorias(subcategoria_id),
    FOREIGN KEY (producto_marca_id) REFERENCES pro_marcas(marca_id),
    FOREIGN KEY (producto_modelo_id) REFERENCES pro_modelo(modelo_id),
    FOREIGN KEY (producto_calibre_id) REFERENCES pro_calibres(calibre_id)
);


CREATE TABLE pro_movimientos (
    mov_id INT AUTO_INCREMENT PRIMARY KEY,
    mov_producto_id INT NOT NULL,
    mov_modelo_id INT, -- si aplica (por lote/lote-arma)
    mov_tipo ENUM('entrada','salida') NOT NULL,
    mov_origen VARCHAR(100),     -- por ejemplo: 'importación', 'ajuste', 'venta'
    mov_cantidad INT NOT NULL,
    mov_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mov_usuario_id INT,
    mov_observaciones VARCHAR(250),

    FOREIGN KEY (mov_producto_id) REFERENCES pro_productos(producto_id),
    FOREIGN KEY (mov_modelo_id) REFERENCES pro_inventario_modelos(modelo_id),
    FOREIGN KEY (mov_usuario_id) REFERENCES users(id)
);



















------estas eliminar --------------------



--sergio
-- CREATE TABLE pro_clases_pistolas (
--     clase_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de clase de arma',
--     clase_descripcion VARCHAR(50) COMMENT 'pistola, carabina, etc.',
--     clase_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
-- );