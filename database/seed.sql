USE gym_db;

-- Instructor inicial:
-- correo: entrenador@gym.local
-- contraseña: la usada para generar el hash en PHP
INSERT INTO USUARIO (nombre, correo, telefono, contrasena, rol)
VALUES (
    'Entrenador principal',
    'entrenador@gym.local',
    NULL,
    '$2y$12$GSS6Hx0lNTAb6PX3AUdR5.8sHovU/xqYcHAS2ldPpdx5OTIOCubEq', -- Instructor123!
    'INSTRUCTOR'
)
ON DUPLICATE KEY UPDATE
    id = LAST_INSERT_ID(id),
    nombre = VALUES(nombre),
    rol = VALUES(rol);

SET @id_instructor = LAST_INSERT_ID();

INSERT INTO INSTRUCTOR (id_usuario, especialidad)
VALUES (@id_instructor, 'Entrenamiento general')
ON DUPLICATE KEY UPDATE
    especialidad = VALUES(especialidad);
