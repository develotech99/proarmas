
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


CREATE TABLE pro_productos_fotos (
    foto_id INT AUTO_INCREMENT PRIMARY KEY,
    foto_producto_id INT NOT NULL,
    foto_url VARCHAR(255) NOT NULL,
    foto_principal BOOLEAN DEFAULT FALSE,
    foto_situacion INT DEFAULT 1, 
    FOREIGN KEY (producto_id) REFERENCES pro_productos(producto_id)
);


CREATE TABLE pro_series_productos (
    serie_id INT AUTO_INCREMENT PRIMARY KEY,
    serie_producto_id INT NOT NULL,
    serie_numero_serie VARCHAR(200) UNIQUE NOT NULL,
    serie_estado ENUM('disponible','reservado','vendido','baja') DEFAULT 'disponible',
    serie_fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    serie_situacion INT DEFAULT 1, 
    FOREIGN KEY (serie_producto_id) REFERENCES pro_productos(producto_id)
);

CREATE TABLE pro_lotes (
    lote_id INT AUTO_INCREMENT PRIMARY KEY,
    lote_codigo VARCHAR(100) UNIQUE NOT NULL, -- Ej: 'L2025-08-GLOCK-001'
    lote_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lote_descripcion VARCHAR(255) NULL, 
    lote_situacion INT DEFAULT 1
);


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
















------estas eliminar --------------------



--sergio
-- CREATE TABLE pro_clases_pistolas (
--     clase_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de clase de arma',
--     clase_descripcion VARCHAR(50) COMMENT 'pistola, carabina, etc.',
--     clase_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
-- );




-- ==========================================
-- 📦 1. Stock actual de productos SIN series
-- ==========================================
-- Se calcula la diferencia entre entradas y salidas solo para productos
-- que NO requieren control por número de serie (producto_requiere_serie = FALSE)

SELECT 
    p.producto_id,
    p.producto_nombre,
    COALESCE(SUM(CASE WHEN m.mov_tipo = 'entrada' THEN m.mov_cantidad ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN m.mov_tipo = 'salida' THEN m.mov_cantidad ELSE 0 END), 0) AS stock_actual
FROM pro_productos p
LEFT JOIN pro_movimientos m ON m.mov_producto_id = p.producto_id
WHERE p.producto_requiere_serie = FALSE
GROUP BY p.producto_id, p.producto_nombre;


-- ==========================================
-- Stock actual para productos CON series
-- ==========================================
-- Cuenta cuántas unidades con serie están disponibles

SELECT 
    p.producto_id,
    p.producto_nombre,
    COUNT(s.serie_id) AS stock_actual
FROM pro_productos p
JOIN pro_series_productos s ON s.serie_producto_id = p.producto_id
WHERE p.producto_requiere_serie = TRUE
  AND s.serie_estado = 'disponible'
GROUP BY p.producto_id, p.producto_nombre;


-- ==========================================
-- Listado de series disponibles por producto
-- ==========================================

SELECT 
    s.serie_id,
    s.serie_numero_serie,
    s.serie_estado,
    p.producto_nombre
FROM pro_series_productos s
JOIN pro_productos p ON p.producto_id = s.serie_producto_id
WHERE s.serie_estado = 'disponible';


-- ==========================================
-- Productos filtrados por categoría, subcategoría, marca y modelo
-- ==========================================

SELECT 
    p.producto_id,
    p.producto_nombre,
    c.categoria_nombre,
    sc.subcategoria_nombre,
    m.marca_descripcion,
    mo.modelo_descripcion
FROM pro_productos p
JOIN pro_categorias c ON c.categoria_id = p.producto_categoria_id
JOIN pro_subcategorias sc ON sc.subcategoria_id = p.producto_subcategoria_id
JOIN pro_marcas m ON m.marca_id = p.producto_marca_id
LEFT JOIN pro_modelo mo ON mo.modelo_id = p.producto_modelo_id;


-- ==========================================
-- Validación para ventas - productos con o sin serie
-- ==========================================
-- Ejemplo para verificar si puedes vender 10 unidades de un producto

-- Requiere serie:
SELECT COUNT(*) FROM pro_series_productos 
WHERE serie_producto_id = 1 AND serie_estado = 'disponible';

-- No requiere serie:
SELECT 
    COALESCE(SUM(CASE WHEN mov_tipo = 'entrada' THEN mov_cantidad ELSE 0 END), 0) -
    COALESCE(SUM(CASE WHEN mov_tipo = 'salida' THEN mov_cantidad ELSE 0 END), 0) AS stock_actual
FROM pro_movimientos
WHERE mov_producto_id = 1;


-- ==========================================
-- 📦 6. Stock agrupado por producto y marca
-- ==========================================

SELECT 
    p.producto_id,
    p.producto_nombre,
    m.marca_descripcion,
    p.producto_requiere_serie,
    CASE 
        WHEN p.producto_requiere_serie = TRUE THEN 
            (SELECT COUNT(*) FROM pro_series_productos s 
             WHERE s.serie_producto_id = p.producto_id AND s.serie_estado = 'disponible')
        ELSE 
            (SELECT 
                COALESCE(SUM(CASE WHEN mov_tipo = 'entrada' THEN mov_cantidad ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN mov_tipo = 'salida' THEN mov_cantidad ELSE 0 END), 0)
             FROM pro_movimientos m2 
             WHERE m2.mov_producto_id = p.producto_id)
    END AS stock_actual
FROM pro_productos p
JOIN pro_marcas m ON m.marca_id = p.producto_marca_id;


-- ==========================================
--Ejemplo de cómo se insertaría una venta de productos CON y SIN serie
-- ==========================================

-- Para productos que NO requieren serie
-- Solo insertas un movimiento de salida
INSERT INTO pro_movimientos (
    mov_producto_id,
    mov_tipo,
    mov_origen,
    mov_cantidad,
    mov_usuario_id,
    mov_observaciones
) VALUES (
    1, -- ID producto
    'salida',
    'venta',
    5,  -- cantidad vendida
    2,  -- usuario
    'Venta directa sin serie'
);

-- Para productos que SÍ requieren serie
-- Se debe actualizar el estado de cada serie vendida
UPDATE pro_series_productos
SET serie_estado = 'vendido'
WHERE serie_id IN (101, 102, 103); -- IDs seleccionadas

-- También se puede registrar el movimiento (opcional)
INSERT INTO pro_movimientos (
    mov_producto_id,
    mov_tipo,
    mov_origen,
    mov_cantidad,
    mov_usuario_id,
    mov_observaciones
) VALUES (
    2,
    'salida',
    'venta',
    3,
    2,
    'Venta por series'
);
