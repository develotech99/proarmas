--marin
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    visita_estado INT NOT NULL,
    -- 1: Visitado no comprado, 2: Visitado comprado, 3: No visitado
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
DELIMITER / / -- Trigger para alertas de stock bajo
CREATE TRIGGER tr_alerta_stock_bajo
AFTER
UPDATE
    ON pro_stock_actual FOR EACH ROW BEGIN DECLARE producto_nombre VARCHAR(100);

DECLARE stock_minimo INT;

DECLARE sku_producto VARCHAR(100);

IF NEW.stock_cantidad_disponible != OLD.stock_cantidad_disponible THEN
SELECT
    p.producto_nombre,
    p.producto_stock_minimo,
    p.pro_codigo_sku INTO producto_nombre,
    stock_minimo,
    sku_producto
FROM
    pro_productos p
WHERE
    p.producto_id = NEW.stock_producto_id;

-- Alerta de stock bajo
IF NEW.stock_cantidad_disponible <= stock_minimo
AND stock_minimo > 0 THEN IF NOT EXISTS (
    SELECT
        1
    FROM
        sys_alertas_notificaciones
    WHERE
        alerta_producto_id = NEW.stock_producto_id
        AND alerta_tipo = 'stock_bajo'
        AND alerta_estado IN ('pendiente', 'vista')
) THEN
INSERT INTO
    sys_alertas_notificaciones (
        alerta_tipo,
        alerta_titulo,
        alerta_mensaje,
        alerta_prioridad,
        alerta_producto_id,
        alerta_datos
    )
VALUES
    (
        'stock_bajo',
        CONCAT('Stock bajo: ', producto_nombre),
        CONCAT(
            'El producto "',
            producto_nombre,
            '" (SKU: ',
            sku_producto,
            ') tiene stock bajo. Stock actual: ',
            NEW.stock_cantidad_disponible,
            ', Stock mínimo: ',
            stock_minimo
        ),
        CASE
            WHEN NEW.stock_cantidad_disponible = 0 THEN 'critica'
            WHEN NEW.stock_cantidad_disponible <= (stock_minimo * 0.5) THEN 'alta'
            ELSE 'media'
        END,
        NEW.stock_producto_id,
        JSON_OBJECT(
            'stock_actual',
            NEW.stock_cantidad_disponible,
            'stock_minimo',
            stock_minimo,
            'producto_nombre',
            producto_nombre,
            'sku',
            sku_producto
        )
    );

END IF;

END IF;

-- Alerta de stock agotado
IF NEW.stock_cantidad_disponible = 0
AND OLD.stock_cantidad_disponible > 0 THEN
INSERT INTO
    sys_alertas_notificaciones (
        alerta_tipo,
        alerta_titulo,
        alerta_mensaje,
        alerta_prioridad,
        alerta_producto_id,
        alerta_datos
    )
VALUES
    (
        'stock_agotado',
        CONCAT('¡AGOTADO! ', producto_nombre),
        CONCAT(
            'El producto "',
            producto_nombre,
            '" (SKU: ',
            sku_producto,
            ') se ha AGOTADO completamente.'
        ),
        'critica',
        NEW.stock_producto_id,
        JSON_OBJECT(
            'producto_nombre',
            producto_nombre,
            'sku',
            sku_producto,
            'stock_anterior',
            OLD.stock_cantidad_disponible
        )
    );

END IF;

END IF;

END / / DELIMITER;

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
    CONCAT(
        u.user_primer_nombre,
        ' ',
        u.user_primer_apellido
    ) as usuario_nombre,
    -- Stock actual si es alerta de stock
    sa.stock_cantidad_disponible,
    -- Datos adicionales
    a.alerta_datos,
    -- Control de emails
    a.alerta_enviar_email,
    a.alerta_email_enviado,
    -- Tiempo transcurrido
    TIMESTAMPDIFF(MINUTE, a.alerta_fecha_generacion, NOW()) as minutos_transcurridos
FROM
    sys_alertas_notificaciones a
    LEFT JOIN pro_productos p ON a.alerta_producto_id = p.producto_id
    LEFT JOIN users u ON a.alerta_usuario_id = u.user_id
    LEFT JOIN pro_stock_actual sa ON a.alerta_producto_id = sa.stock_producto_id
WHERE
    a.alerta_estado IN ('pendiente', 'vista')
ORDER BY
    FIELD(
        a.alerta_prioridad,
        'critica',
        'alta',
        'media',
        'baja'
    ),
    a.alerta_fecha_generacion DESC;

--////////////////***************************************/////////////////////////////--
--/////// TABLAS DE CARLOS VÁSQUEZ PRO_PAGOS_SUBIDOS /////////////////////////////////--
--//**********************************************************************************--
CREATE TABLE pro_pagos_subidos (
    ps_id INT AUTO_INCREMENT PRIMARY KEY,
    ps_venta_id INT NOT NULL,
    ps_cliente_user_id BIGINT UNSIGNED NULL,
    ps_fecha_comprobante DATETIME NULL,
    ps_monto_comprobante DECIMAL(10, 2) NOT NULL,
    ps_monto_total_cuotas_front DECIMAL(10, 2) NULL,
    ps_banco_id BIGINT UNSIGNED NULL,
    ps_banco_nombre VARCHAR(64) NULL,
    ps_referencia VARCHAR(64) NULL,
    ps_concepto VARCHAR(255) NULL,
    ps_cuotas_json JSON NULL,
    ps_imagen_path VARCHAR(255) NULL,
    ps_estado ENUM('PENDIENTE', 'APROBADO', 'RECHAZADO') DEFAULT 'PENDIENTE',
    ps_monto_aprobado DECIMAL(10, 2) NULL,
    ps_cuotas_aprobadas_json JSON NULL,
    ps_revisado_por BIGINT UNSIGNED NULL,
    ps_revisado_en DATETIME NULL,
    ps_notas_revision VARCHAR(300) NULL,
    ps_dedupe_key VARCHAR(64) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ps_venta FOREIGN KEY (ps_venta_id) REFERENCES pro_ventas(ven_id) ON DELETE CASCADE,
    CONSTRAINT fk_ps_cliente FOREIGN KEY (ps_cliente_user_id) REFERENCES users(user_id) ON DELETE
    SET
        NULL,
        CONSTRAINT fk_ps_revisor FOREIGN KEY (ps_revisado_por) REFERENCES users(user_id) ON DELETE
    SET
        NULL,
        UNIQUE KEY uq_ps_dedupe_key (ps_dedupe_key),
        KEY idx_ps_estado_created (ps_estado, created_at),
        KEY idx_ps_venta_estado (ps_venta_id, ps_estado),
        KEY idx_ps_ref_banco (ps_referencia, ps_banco_id),
        KEY idx_ps_cliente (ps_cliente_user_id)
);

-- Tabla de saldos actuales de caja (simple y clara)
CREATE TABLE caja_saldos (
    caja_saldo_id           INT AUTO_INCREMENT PRIMARY KEY,
    caja_saldo_metodo_pago  BIGINT UNSIGNED NOT NULL,         -- FK a pro_metodos_pago (EFECTIVO, TARJETA, TRANSFERENCIA, etc.)
    caja_saldo_moneda       VARCHAR(3) NOT NULL DEFAULT 'GTQ', -- Si solo usas GTQ, queda así
    caja_saldo_monto_actual DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    caja_saldo_actualizado  DATETIME NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_caja_saldo (caja_saldo_metodo_pago, caja_saldo_moneda),
    CONSTRAINT fk_caja_saldos_metodo
        FOREIGN KEY (caja_saldo_metodo_pago) REFERENCES pro_metodos_pago(metpago_id)
) COMMENT='Saldos actuales de caja por método de pago y moneda';


CREATE TABLE pro_estados_cuenta (
    ec_id INT AUTO_INCREMENT PRIMARY KEY,
    ec_banco_id BIGINT UNSIGNED NULL COMMENT 'FK al banco de origen (si aplica)',
    ec_archivo VARCHAR(255) NOT NULL COMMENT 'Ruta del archivo subido en storage',
    ec_headers JSON NULL COMMENT 'Lista de encabezados detectados en el archivo',
    ec_rows LONGTEXT NULL COMMENT 'Contenido de filas normalizado (JSON)',
    ec_fecha_ini DATE NULL COMMENT 'Fecha inicio del período (opcional)',
    ec_fecha_fin DATE NULL COMMENT 'Fecha fin del período (opcional)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT='Registros de estados de cuenta cargados por el admin para conciliación';