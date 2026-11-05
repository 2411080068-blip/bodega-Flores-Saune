CREATE DATABASE bodega;
USE bodega;

CREATE TABLE producto (
    id              INT           NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(100)  NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    stock           INT           NOT NULL DEFAULT 0,
    CONSTRAINT pk_producto PRIMARY KEY (id),
    CONSTRAINT chk_precio_positivo CHECK (precio_unitario > 0),
    CONSTRAINT chk_stock_no_negativo CHECK (stock >= 0)
) ENGINE=InnoDB;

CREATE TABLE venta (
    id        INT           NOT NULL AUTO_INCREMENT,
    subtotal  DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0,
    total     DECIMAL(10,2) NOT NULL,
    fecha     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_venta PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE detalle_venta (
    id              INT           NOT NULL AUTO_INCREMENT,
    venta_id        INT           NOT NULL,
    producto_id     INT           NOT NULL,
    cantidad        INT           NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL,
    CONSTRAINT pk_detalle PRIMARY KEY (id),
    CONSTRAINT fk_detalle_venta
        FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE RESTRICT,
    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (producto_id) REFERENCES producto(id) ON DELETE RESTRICT,
    CONSTRAINT chk_cantidad_positiva CHECK (cantidad > 0)
) ENGINE=InnoDB;

INSERT INTO producto (nombre, precio_unitario, stock) VALUES
    ('Arroz Costeño 1 kg', 4.50, 25),
    ('Aceite Primor 900 ml', 10.90, 15),
    ('Leche evaporada Gloria 400 g', 4.20, 0),
    ('Azúcar rubia 1 kg', 4.50, 40),
    ('Atún Florida 170 g', 6.90, 18),
    ('Gaseosa Inca Kola 1.5 L', 8.50, 30);
