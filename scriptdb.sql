-- Crear la base de datos
CREATE DATABASE agencia_viajes;
\c agencia_viajes;

-- Crear tabla CLIENTE
CREATE TABLE CLIENTE (
    id_cliente SERIAL PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(9) NOT NULL UNIQUE CHECK (dni ~ '^[0-9]{8}[A-Z]$'),
    correo_electronico VARCHAR(40) NOT NULL UNIQUE
);

-- Crear tabla TELEFONO
CREATE TABLE TELEFONO (
    id_cliente INT NOT NULL,
    telefono VARCHAR(9) NOT NULL CHECK (telefono ~ '^[0-9]{9}$'),
    PRIMARY KEY (id_cliente, telefono),
    FOREIGN KEY (id_cliente) REFERENCES CLIENTE(id_cliente) ON UPDATE CASCADE ON DELETE NO ACTION
);

-- Crear tabla VIAJE
CREATE TABLE VIAJE (
    id_viaje SERIAL PRIMARY KEY,
    origen VARCHAR(20) NOT NULL,
    destino VARCHAR(20) NOT NULL,
    fecha_salida TIMESTAMP NOT NULL,
    fecha_llegada TIMESTAMP NOT NULL,
    precio NUMERIC(10, 2) NOT NULL,
    max_plazas INT NOT NULL
);

-- Crear tabla RESERVA
CREATE TABLE RESERVA (
    id_reserva SERIAL PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_viaje INT NOT NULL,
    fecha_reserva TIMESTAMP NOT NULL,
    cantidad_pagada NUMERIC(10, 2) NOT NULL,
    num_plazas_reservadas INT NOT NULL,
    cancelada BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (id_cliente) REFERENCES CLIENTE(id_cliente) ON UPDATE CASCADE ON DELETE NO ACTION,
    FOREIGN KEY (id_viaje) REFERENCES VIAJE(id_viaje) ON UPDATE CASCADE ON DELETE NO ACTION
);

-- Crear tabla CANCELACION
CREATE TABLE CANCELACION (
    id_cancelacion SERIAL PRIMARY KEY,
    fecha_cancelacion DATE NOT NULL,
    penalizacion NUMERIC(10, 2),
    id_reserva INT NOT NULL,
    FOREIGN KEY (id_reserva) REFERENCES RESERVA(id_reserva) ON UPDATE CASCADE ON DELETE NO ACTION
);

-- Crear función para actualizar la columna cancelada
CREATE OR REPLACE FUNCTION marcar_reserva_cancelada()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE RESERVA
    SET cancelada = TRUE
    WHERE id_reserva = NEW.id_reserva;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Crear trigger para actualizar reservas al insertar cancelaciones
CREATE TRIGGER trg_marcar_reserva_cancelada
AFTER INSERT ON CANCELACION
FOR EACH ROW
EXECUTE FUNCTION marcar_reserva_cancelada();
