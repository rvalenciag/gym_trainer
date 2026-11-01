  CREATE DATABASE IF NOT EXISTS gym_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym_db;

CREATE TABLE USUARIO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NULL,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('INSTRUCTOR','CLIENTE') NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuario_correo UNIQUE (correo),
    INDEX idx_usuario_rol (rol)
) ENGINE=InnoDB;

CREATE TABLE INSTRUCTOR (
    id_usuario INT PRIMARY KEY,
    especialidad VARCHAR(150) NULL,
    CONSTRAINT fk_instructor_usuario FOREIGN KEY (id_usuario)
        REFERENCES USUARIO(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE CLIENTE (
    id_usuario INT PRIMARY KEY,
    observacion TEXT NULL,
    CONSTRAINT fk_cliente_usuario FOREIGN KEY (id_usuario)
        REFERENCES USUARIO(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE CATEGORIA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NULL,
    CONSTRAINT uq_categoria_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

CREATE TABLE EJERCICIO (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    tipo_multimedia ENUM('IMAGEN','VIDEO_URL','IFRAME_HTML') NOT NULL,
    url TEXT NULL,
    id_categoria INT NOT NULL,
    CONSTRAINT fk_ejercicio_categoria FOREIGN KEY (id_categoria)
        REFERENCES CATEGORIA(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_ejercicio_categoria (id_categoria),
    INDEX idx_ejercicio_tipo (tipo_multimedia),
    FULLTEXT INDEX ft_ejercicio_nombre_desc (nombre, descripcion)
) ENGINE=InnoDB;

CREATE TABLE RUTINA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_plan VARCHAR(120) NOT NULL,
    id_instructor INT NOT NULL,
    id_cliente INT NOT NULL,
    CONSTRAINT fk_rutina_instructor FOREIGN KEY (id_instructor)
        REFERENCES INSTRUCTOR(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_rutina_cliente FOREIGN KEY (id_cliente)
        REFERENCES CLIENTE(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_rutina_cliente (id_cliente),
    INDEX idx_rutina_instructor (id_instructor)
) ENGINE=InnoDB;

CREATE TABLE DETALLE_RUTINA (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_rutina INT NOT NULL,
    id_ejercicio INT NOT NULL,
    dia_semana TINYINT NOT NULL,
    series INT NOT NULL,
    repeticiones VARCHAR(20) NOT NULL,
    peso_sugerido DECIMAL(6,2) NULL,
    descanso INT NULL COMMENT 'segundos',
    CONSTRAINT fk_detalle_rutina FOREIGN KEY (id_rutina)
        REFERENCES RUTINA(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_ejercicio FOREIGN KEY (id_ejercicio)
        REFERENCES EJERCICIO(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_detalle_dia CHECK (dia_semana BETWEEN 1 AND 7),
    CONSTRAINT chk_detalle_series CHECK (series > 0),
    INDEX idx_detalle_rutina_dia (id_rutina, dia_semana),
    INDEX idx_detalle_ejercicio (id_ejercicio)
) ENGINE=InnoDB;
