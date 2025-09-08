
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
CREATE TABLE pro_paises (
    pais_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de país',
    pais_descripcion VARCHAR(50) COMMENT 'Descripción del país',
    pais_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
);

--sergio
CREATE TABLE pro_clases_pistolas (
    clase_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de clase de arma',
    clase_descripcion VARCHAR(50) COMMENT 'pistola, carabina, etc.',
    clase_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
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
    modelo_situacion INT DEFAULT 1 COMMENT '1 = activo, 0 = inactivo'
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
    arma_estado ENUM('disponible','vendida','reservada','baja') DEFAULT 'disponible',
    FOREIGN KEY (arma_modelo_id) REFERENCES pro_inventario_modelos(modelo_id)
);

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
 
 CREATE TABLE pro_modelo_fotos (
    foto_id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID de la foto',
    modelo_id INT NOT NULL COMMENT 'Modelo al que pertenece',
    foto_url VARCHAR(255) NOT NULL COMMENT 'Ruta de la imagen',
    FOREIGN KEY (modelo_id) REFERENCES pro_inventario_modelos(modelo_id)
); 


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

-- ============================================
-- MÓDULO DE VENTAS - ESTRUCTURA SIMPLIFICADA
-- ============================================

--VENDEDORES
CREATE TABLE pro_vendedores (
    vendedor_id INT AUTO_INCREMENT PRIMARY KEY,
    vendedor_user_id INT NOT NULL COMMENT 'FK al usuario del sistema',
    vendedor_codigo VARCHAR(20) UNIQUE NOT NULL,
    vendedor_nombres VARCHAR(100) NOT NULL,
    vendedor_apellidos VARCHAR(100) NOT NULL,
    vendedor_comision_porcentaje DECIMAL(5,2) DEFAULT 0.00,
    vendedor_telefono VARCHAR(20),
    vendedor_email VARCHAR(100),
    vendedor_situacion INT DEFAULT 1,
    vendedor_fecha_ingreso DATE DEFAULT CURRENT_DATE,
    
    FOREIGN KEY (vendedor_user_id) REFERENCES users(user_id)
);

--CLIENTES MEJORADOS 
CREATE TABLE pro_clientes_ventas (
    cliente_id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_tipo ENUM('empresa','persona') NOT NULL,
    cliente_codigo VARCHAR(20) UNIQUE,
    cliente_nombre VARCHAR(200) NOT NULL,
    cliente_nombre_comercial VARCHAR(200),
    cliente_razon_social VARCHAR(200),
    cliente_nit VARCHAR(15),
    cliente_dpi VARCHAR(20),
    cliente_telefono VARCHAR(20),
    cliente_email VARCHAR(100),
    cliente_direccion TEXT,
    cliente_ubicacion VARCHAR(100),
    cliente_situacion INT DEFAULT 1,
    cliente_fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--VENTAS PRINCIPALES
CREATE TABLE pro_ventas_principales (
    pro_venta_id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- CÓDIGO ÚNICO PARA FACTURACIÓN (IMPORTANTE)
    pro_venta_codigo VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código único para facturas',
    
    pro_venta_tipo ENUM('cotizacion','venta') DEFAULT 'cotizacion',
    pro_venta_fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Cliente (registrado o temporal)
    cliente_id INT COMMENT 'FK cliente registrado',
    cliente_nombre_temporal VARCHAR(200) COMMENT 'Si no está registrado',
    cliente_nit_temporal VARCHAR(15) COMMENT 'NIT para facturar',
    cliente_telefono_temporal VARCHAR(20),
    cliente_direccion_temporal TEXT,
    
    -- Vendedor responsable
    vendedor_id INT NOT NULL,
    
    -- Estados
    venta_estado ENUM('borrador','cotizado','confirmado','entregado','cancelado') DEFAULT 'borrador',
    venta_estado_pago ENUM('pendiente','parcial','completado') DEFAULT 'pendiente',
    
    -- Totales
    venta_subtotal DECIMAL(12,2) DEFAULT 0.00,
    venta_descuento_global DECIMAL(12,2) DEFAULT 0.00,
    venta_impuestos DECIMAL(12,2) DEFAULT 0.00,
    venta_total DECIMAL(12,2) DEFAULT 0.00,
    venta_total_pagado DECIMAL(12,2) DEFAULT 0.00,
    venta_saldo_pendiente DECIMAL(12,2) DEFAULT 0.00,
    
    -- Fechas
    venta_fecha_entrega DATE,
    venta_fecha_confirmacion DATETIME,
    venta_fecha_completado DATETIME,
    
    -- Control
    venta_observaciones TEXT,
    venta_motivo_cancelacion VARCHAR(255),
    venta_situacion INT DEFAULT 1,
    venta_usuario_creacion INT NOT NULL,
    venta_usuario_modificacion INT,
    venta_fecha_modificacion TIMESTAMP,
    
    FOREIGN KEY (cliente_id) REFERENCES pro_clientes_ventas(cliente_id),
    FOREIGN KEY (vendedor_id) REFERENCES pro_vendedores(vendedor_id),
    FOREIGN KEY (venta_usuario_creacion) REFERENCES users(user_id),
    FOREIGN KEY (venta_usuario_modificacion) REFERENCES users(user_id)
);

--DETALLE DE VENTAS (PRODUCTOS)
CREATE TABLE pro_detalle_ventas (
    pro_det_detalle_id INT AUTO_INCREMENT PRIMARY KEY,
    pro_venta_id INT NOT NULL,
    
    -- Referencia a inventario
    pro_det_producto_id INT NOT NULL COMMENT 'FK al producto',
    pro_det_serie_id INT COMMENT 'FK serie específica si aplica',
    numero_serie VARCHAR(200) COMMENT 'Número de serie',
    
    -- Info del producto al momento de venta
    pro_det_producto_nombre VARCHAR(100) NOT NULL,
    pro_det_producto_marca VARCHAR(50),
    pro_det_producto_modelo VARCHAR(50),
    pro_det_producto_calibre VARCHAR(20),
    
    -- Cantidades y precios
    pro_det_cantidad INT NOT NULL DEFAULT 1,
    pro_det_precio_unitario DECIMAL(12,2) NOT NULL,
    pro_det_descuento_porcentaje DECIMAL(5,2) DEFAULT 0.00,
    pro_det_descuento_monto DECIMAL(12,2) DEFAULT 0.00,
    pro_det_subtotal DECIMAL(12,2) NOT NULL,
    
    -- Estado
    pro_det_detalle_estado ENUM('pendiente','reservado','entregado','cancelado') DEFAULT 'pendiente',
    pro_det_fecha_entrega DATETIME,
    pro_det_observaciones VARCHAR(255),
    
    FOREIGN KEY (venta_id) REFERENCES pro_ventas_principales(venta_id),
    FOREIGN KEY (producto_id) REFERENCES pro_productos(producto_id),
    FOREIGN KEY (serie_id) REFERENCES pro_series_productos(serie_id)
);

--PAGOS DE VENTAS
CREATE TABLE pro_pagos_ventas (
    pago_id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    pago_numero INT NOT NULL,
    pago_fecha DATE NOT NULL,
    pago_monto DECIMAL(12,2) NOT NULL,
    pago_metodo_id INT NOT NULL,
    pago_referencia VARCHAR(100),
    pago_comprobante_ruta VARCHAR(255),
    pago_estado ENUM('registrado','verificado','rechazado') DEFAULT 'registrado',
    pago_fecha_verificacion DATETIME,
    pago_usuario_verificacion INT,
    pago_observaciones TEXT,
    
    FOREIGN KEY (venta_id) REFERENCES pro_ventas_principales(venta_id),
    FOREIGN KEY (pago_metodo_id) REFERENCES pro_metodos_pago(metpago_id),
    FOREIGN KEY (pago_usuario_verificacion) REFERENCES users(user_id)
);

--COMISIONES VENDEDORES
CREATE TABLE pro_comisiones_vendedores (
    comision_id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    vendedor_id INT NOT NULL,
    comision_base_calculo DECIMAL(12,2) NOT NULL,
    comision_porcentaje DECIMAL(5,2) NOT NULL,
    comision_monto DECIMAL(12,2) NOT NULL,
    comision_estado ENUM('calculada','pagada','anulada') DEFAULT 'calculada',
    comision_fecha_calculo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comision_fecha_pago DATE,
    observaciones VARCHAR(255),
    
    FOREIGN KEY (venta_id) REFERENCES pro_ventas_principales(venta_id),
    FOREIGN KEY (vendedor_id) REFERENCES pro_vendedores(vendedor_id)
);

--RESERVAS DE INVENTARIO
CREATE TABLE pro_reservas_inventario (
    reserva_id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT NOT NULL,
    detalle_id INT NOT NULL,
    producto_id INT NOT NULL,
    serie_id INT,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    estado ENUM('activa','confirmada','expirada','cancelada') DEFAULT 'activa',
    
    FOREIGN KEY (venta_id) REFERENCES pro_ventas_principales(venta_id),
    FOREIGN KEY (detalle_id) REFERENCES pro_detalle_ventas(detalle_id),
    FOREIGN KEY (producto_id) REFERENCES pro_productos(producto_id),
    FOREIGN KEY (serie_id) REFERENCES pro_series_productos(serie_id)
);

-- ========================
-- TRIGGERS BÁSICOS
-- ========================

-- Generar código único de venta
DELIMITER $$
CREATE TRIGGER tr_generar_codigo_venta
BEFORE INSERT ON pro_ventas_principales
FOR EACH ROW
BEGIN
    IF NEW.venta_codigo IS NULL OR NEW.venta_codigo = '' THEN
        SET NEW.venta_codigo = CONCAT(
            'VEN',
            DATE_FORMAT(NOW(), '%Y%m%d'),
            '-',
            LPAD(NEW.venta_id, 6, '0')
        );
    END IF;
END$$

-- Calcular totales automáticamente
CREATE TRIGGER tr_calcular_totales
AFTER INSERT ON pro_detalle_ventas
FOR EACH ROW
BEGIN
    UPDATE pro_ventas_principales SET
        venta_subtotal = (
            SELECT IFNULL(SUM(subtotal), 0) 
            FROM pro_detalle_ventas 
            WHERE venta_id = NEW.venta_id
        )
    WHERE venta_id = NEW.venta_id;
    
    UPDATE pro_ventas_principales SET
        venta_total = venta_subtotal - venta_descuento_global + venta_impuestos
    WHERE venta_id = NEW.venta_id;
END$$

-- Calcular comisiones
CREATE TRIGGER tr_calcular_comision
AFTER UPDATE ON pro_ventas_principales
FOR EACH ROW
BEGIN
    IF OLD.venta_estado != 'confirmado' AND NEW.venta_estado = 'confirmado' THEN
        INSERT INTO pro_comisiones_vendedores (
            venta_id,
            vendedor_id,
            comision_base_calculo,
            comision_porcentaje,
            comision_monto
        )
        SELECT 
            NEW.venta_id,
            NEW.vendedor_id,
            NEW.venta_total,
            v.vendedor_comision_porcentaje,
            (NEW.venta_total * v.vendedor_comision_porcentaje / 100)
        FROM pro_vendedores v
        WHERE v.vendedor_id = NEW.vendedor_id;
    END IF;
END$$

-- Actualizar inventario al entregar
CREATE TRIGGER tr_actualizar_inventario
AFTER UPDATE ON pro_detalle_ventas
FOR EACH ROW
BEGIN
    IF OLD.detalle_estado != 'entregado' AND NEW.detalle_estado = 'entregado' THEN
        -- Si tiene serie específica
        IF NEW.serie_id IS NOT NULL THEN
            UPDATE pro_series_productos 
            SET serie_estado = 'vendido'
            WHERE serie_id = NEW.serie_id;
        END IF;
        
        -- Crear movimiento de inventario
        INSERT INTO pro_movimientos (
            mov_producto_id,
            mov_tipo,
            mov_origen,
            mov_cantidad,
            mov_usuario_id,
            mov_observaciones
        ) VALUES (
            NEW.producto_id,
            'egreso',
            'venta',
            NEW.cantidad,
            NEW.venta_id,
            CONCAT('Venta entregada - Código: ', (SELECT venta_codigo FROM pro_ventas_principales WHERE venta_id = NEW.venta_id))
        );
    END IF;
END$$

DELIMITER ;

-- ========================
-- COMENTARIOS
-- ========================

/*
ESTRUCTURA SIMPLIFICADA SIMILAR A TUS COMPAÑEROS:

1. Nombres de tablas claros y descriptivos
2. Campos con prefijos consistentes
3. Estructura similar a las tablas existentes
4. Foreign keys simples
5. Triggers básicos para automatización

PUNTOS CLAVE:
- venta_codigo: Código único para facturas (VEN20250908-000001)
- Integración con inventario mediante producto_id y serie_id
- Comisiones automáticas para vendedores
- Control de pagos múltiples
- Estados de venta manejables

Las tablas están listas para usar con Laravel o cualquier ORM.
*/