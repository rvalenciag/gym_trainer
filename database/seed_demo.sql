-- GymTrainer · datos de demostración (solo desarrollo)
-- Ejecutar DESPUÉS de database/schema.sql y database/seed.sql.
-- Requiere que RUTINA utilice el modelo semanal (sin fechas de vigencia).
-- Reutiliza el único instructor registrado en el seed original.
-- Usuarios de prueba: contraseña GymDemo2026! (hash bcrypt, no texto plano).
-- No altera ni crea instructores. Se puede volver a ejecutar sin duplicar los datos de ejemplo.

USE gym_db;
SET NAMES utf8mb4;

SET @instructor_id = (
    SELECT INSTRUCTOR.id_usuario
    FROM INSTRUCTOR
    INNER JOIN USUARIO ON USUARIO.id = INSTRUCTOR.id_usuario
    WHERE USUARIO.correo = 'entrenador@gym.local'
      AND USUARIO.rol = 'INSTRUCTOR'
    LIMIT 1
);

START TRANSACTION;

-- 1. Usuarios clientes
INSERT INTO USUARIO (nombre, correo, telefono, contrasena, rol)
SELECT datos.nombre, datos.correo, datos.telefono, datos.contrasena, 'CLIENTE'
FROM (
    SELECT 'Carlos Mendoza' AS nombre, 'carlos.mendoza@gym.test' AS correo, '72000001' AS telefono, '$2y$12$8JE5z.NO/cIwcePyYTf6F.OcPhm7O/W9Iv5tVNBc81dm/x1kU27Yu' AS contrasena
    UNION ALL
    SELECT 'Ana Ruiz', 'ana.ruiz@gym.test', '72000002', '$2y$12$Cv8wH3vdYIfgh9ziNVUuhO66bnkx9NH3qldYQ0Mmw9eX4FbvwYWYC'
    UNION ALL
    SELECT 'Luis Torres', 'luis.torres@gym.test', '72000003', '$2y$12$nsnD5.w2g7UffaEIHRsdj.MGOCB03S4ijR5bx5ChsqlXucjm16Dt2'
    UNION ALL
    SELECT 'Sofía Vargas', 'sofia.vargas@gym.test', '72000004', '$2y$12$V545N2gShrR7yCYG.lLe0OP/1PjvethdIU.GZqMDHrQgmdSOEF0dy'
    UNION ALL
    SELECT 'Diego Flores', 'diego.flores@gym.test', '72000005', '$2y$12$L32Rx177HSibZp42fRdsNeE.zWCTfsZJL7iUbtZMxlumz3lOpH/om'
    UNION ALL
    SELECT 'Valeria Castro', 'valeria.castro@gym.test', '72000006', '$2y$12$qK7bMzzUVRVJyiZV34KwzenjzoXKsFCBqpmKOlzmQxk2kfmamzoia'
) AS datos
LEFT JOIN USUARIO ON USUARIO.correo = datos.correo
WHERE USUARIO.id IS NULL;

-- 2. Perfiles de cliente
INSERT INTO CLIENTE (id_usuario, observacion)
SELECT USUARIO.id, 'Sin observaciones registradas.'
FROM USUARIO
LEFT JOIN CLIENTE ON CLIENTE.id_usuario = USUARIO.id
WHERE USUARIO.rol = 'CLIENTE'
  AND USUARIO.correo IN (
      'carlos.mendoza@gym.test',
      'ana.ruiz@gym.test',
      'luis.torres@gym.test',
      'sofia.vargas@gym.test',
      'diego.flores@gym.test',
      'valeria.castro@gym.test'
  )
  AND CLIENTE.id_usuario IS NULL;

-- 3. Categorías musculares
INSERT INTO CATEGORIA (nombre, descripcion)
SELECT datos.nombre, datos.descripcion
FROM (
    SELECT 'Pecho' AS nombre, 'Ejercicios para la musculatura pectoral.' AS descripcion
    UNION ALL
    SELECT 'Espalda', 'Trabajo de dorsales y musculatura de la espalda.'
    UNION ALL
    SELECT 'Piernas', 'Ejercicios generales para las extremidades inferiores.'
    UNION ALL
    SELECT 'Hombros', 'Ejercicios de deltoides y estabilidad del hombro.'
    UNION ALL
    SELECT 'Brazos', 'Trabajo de bíceps, tríceps y antebrazos.'
    UNION ALL
    SELECT 'Abdomen', 'Ejercicios para la musculatura del abdomen y core.'
    UNION ALL
    SELECT 'Cardio', 'Actividad cardiovascular de distintas intensidades.'
    UNION ALL
    SELECT 'Glúteos', 'Ejercicios orientados a la musculatura glútea.'
    UNION ALL
    SELECT 'Movilidad', 'Ejercicios de amplitud de movimiento y movilidad articular.'
) AS datos
LEFT JOIN CATEGORIA ON CATEGORIA.nombre = datos.nombre
WHERE CATEGORIA.id IS NULL;

-- 4. Ejercicios del catálogo
INSERT INTO EJERCICIO (nombre, descripcion, tipo_multimedia, url, id_categoria)
SELECT datos.nombre, datos.descripcion, datos.tipo_multimedia, datos.url, CATEGORIA.id
FROM (
    SELECT 'Press de banca' AS nombre, 'Acostado en banco, baja la barra de forma controlada y empuja hasta extender los codos.' AS descripcion, 'VIDEO_URL' AS tipo_multimedia, 'https://www.youtube.com/watch?v=rT7DgCr-3pg' AS url, 'Pecho' AS categoria
    UNION ALL
    SELECT 'Aperturas con mancuernas', 'Con los codos ligeramente flexionados, abre y cierra los brazos de forma controlada.', 'IMAGEN', NULL, 'Pecho'
    UNION ALL
    SELECT 'Flexiones de pecho', 'Mantén el cuerpo alineado y desciende el pecho sin hundir la cadera.', 'IMAGEN', NULL, 'Pecho'
    UNION ALL
    SELECT 'Press inclinado con mancuernas', 'En banco inclinado, empuja las mancuernas hacia arriba de manera controlada.', 'IMAGEN', NULL, 'Pecho'
    UNION ALL
    SELECT 'Jalón al pecho', 'Lleva la barra hacia la zona superior del pecho evitando balancear el torso.', 'IMAGEN', NULL, 'Espalda'
    UNION ALL
    SELECT 'Remo con barra', 'Inclina ligeramente el torso y lleva la barra hacia el abdomen.', 'IMAGEN', NULL, 'Espalda'
    UNION ALL
    SELECT 'Dominadas', 'Desde la barra, eleva el cuerpo manteniendo control escapular.', 'IMAGEN', NULL, 'Espalda'
    UNION ALL
    SELECT 'Remo con mancuerna', 'Apoya una mano en banco y acerca la mancuerna hacia la cadera.', 'IMAGEN', NULL, 'Espalda'
    UNION ALL
    SELECT 'Sentadilla con barra', 'Mantén la espalda neutra y flexiona cadera y rodillas controladamente.', 'IMAGEN', NULL, 'Piernas'
    UNION ALL
    SELECT 'Prensa de piernas', 'Empuja la plataforma sin bloquear violentamente las rodillas.', 'IMAGEN', NULL, 'Piernas'
    UNION ALL
    SELECT 'Extensión de cuádriceps', 'Extiende las rodillas de forma controlada en la máquina.', 'IMAGEN', NULL, 'Piernas'
    UNION ALL
    SELECT 'Curl femoral', 'Flexiona las rodillas contra la resistencia de la máquina.', 'IMAGEN', NULL, 'Piernas'
    UNION ALL
    SELECT 'Press militar con mancuernas', 'Desde los hombros, eleva las mancuernas y evita arquear la espalda.', 'IFRAME_HTML', '<iframe src="https://www.youtube.com/embed/qEwKCR5JCog" title="Press militar con mancuernas" loading="lazy" allowfullscreen></iframe>', 'Hombros'
    UNION ALL
    SELECT 'Elevaciones laterales', 'Eleva los brazos hacia los lados sin impulsarte con la espalda.', 'IMAGEN', NULL, 'Hombros'
    UNION ALL
    SELECT 'Pájaros con mancuernas', 'Con el torso inclinado, abre los brazos trabajando deltoides posteriores.', 'IMAGEN', NULL, 'Hombros'
    UNION ALL
    SELECT 'Curl de bíceps', 'Flexiona los codos manteniendo los brazos cerca del torso.', 'IMAGEN', NULL, 'Brazos'
    UNION ALL
    SELECT 'Curl martillo', 'Flexiona el codo con agarre neutro y movimiento controlado.', 'IMAGEN', NULL, 'Brazos'
    UNION ALL
    SELECT 'Extensión de tríceps en polea', 'Extiende los codos manteniendo los brazos pegados al cuerpo.', 'IMAGEN', NULL, 'Brazos'
    UNION ALL
    SELECT 'Plancha abdominal', 'Sostén una línea recta entre hombros y talones activando el abdomen.', 'IMAGEN', NULL, 'Abdomen'
    UNION ALL
    SELECT 'Crunch abdominal', 'Eleva ligeramente el tronco sin tirar del cuello.', 'IMAGEN', NULL, 'Abdomen'
    UNION ALL
    SELECT 'Elevaciones de piernas', 'Eleva las piernas manteniendo la pelvis estable.', 'IMAGEN', NULL, 'Abdomen'
    UNION ALL
    SELECT 'Caminata en cinta', 'Camina a ritmo cómodo con postura erguida.', 'IMAGEN', NULL, 'Cardio'
    UNION ALL
    SELECT 'Bicicleta estática', 'Pedalea con resistencia moderada y postura controlada.', 'IMAGEN', NULL, 'Cardio'
    UNION ALL
    SELECT 'Saltos de tijera', 'Alterna apertura y cierre de brazos y piernas a ritmo constante.', 'IMAGEN', NULL, 'Cardio'
    UNION ALL
    SELECT 'Hip thrust', 'Eleva la cadera y contrae glúteos manteniendo el tronco estable.', 'IMAGEN', NULL, 'Glúteos'
    UNION ALL
    SELECT 'Puente de glúteos', 'Eleva la pelvis desde el suelo sin hiperextender la espalda.', 'IMAGEN', NULL, 'Glúteos'
    UNION ALL
    SELECT 'Estiramiento de cadera', 'Moviliza suavemente la cadera sin llegar al dolor.', 'IMAGEN', NULL, 'Movilidad'
    UNION ALL
    SELECT 'Movilidad de hombros', 'Realiza movimientos controlados dentro de un rango cómodo.', 'IMAGEN', NULL, 'Movilidad'
) AS datos
INNER JOIN CATEGORIA ON CATEGORIA.nombre = datos.categoria
LEFT JOIN EJERCICIO ON EJERCICIO.nombre = datos.nombre
WHERE EJERCICIO.id IS NULL;

-- 5. Rutinas semanales (asignadas al instructor del seed)
INSERT INTO RUTINA (nombre_plan, id_instructor, id_cliente)
SELECT datos.nombre_plan, INSTRUCTOR.id_usuario, CLIENTE.id_usuario
FROM (
    SELECT 'Fuerza general' AS nombre_plan, 'carlos.mendoza@gym.test' AS correo_cliente
    UNION ALL
    SELECT 'Hipertrofia superior', 'carlos.mendoza@gym.test'
    UNION ALL
    SELECT 'Acondicionamiento inicial', 'ana.ruiz@gym.test'
    UNION ALL
    SELECT 'Piernas y glúteos', 'ana.ruiz@gym.test'
    UNION ALL
    SELECT 'Resistencia muscular', 'luis.torres@gym.test'
    UNION ALL
    SELECT 'Fuerza inicial', 'luis.torres@gym.test'
    UNION ALL
    SELECT 'Hipertrofia general', 'sofia.vargas@gym.test'
    UNION ALL
    SELECT 'Movilidad y bienestar', 'diego.flores@gym.test'
    UNION ALL
    SELECT 'Full body semanal', 'valeria.castro@gym.test'
    UNION ALL
    SELECT 'Cardio y core', 'valeria.castro@gym.test'
) AS datos
INNER JOIN USUARIO ON USUARIO.correo = datos.correo_cliente AND USUARIO.rol = 'CLIENTE'
INNER JOIN CLIENTE ON CLIENTE.id_usuario = USUARIO.id
INNER JOIN INSTRUCTOR ON INSTRUCTOR.id_usuario = @instructor_id
LEFT JOIN RUTINA ON RUTINA.id_cliente = CLIENTE.id_usuario
  AND RUTINA.id_instructor = INSTRUCTOR.id_usuario
  AND RUTINA.nombre_plan = datos.nombre_plan
WHERE RUTINA.id IS NULL;

-- 6. Ejercicios asociados a cada día (1 = lunes, 7 = domingo)
INSERT INTO DETALLE_RUTINA
    (id_rutina, id_ejercicio, dia_semana, series, repeticiones, peso_sugerido, descanso)
SELECT RUTINA.id, EJERCICIO.id, datos.dia_semana, datos.series,
       datos.repeticiones, datos.peso_sugerido, datos.descanso
FROM (
    SELECT 'carlos.mendoza@gym.test' AS correo_cliente, 'Fuerza general' AS nombre_plan, 'Press de banca' AS ejercicio, 1 AS dia_semana, 4 AS series, '8' AS repeticiones, 50 AS peso_sugerido, 90 AS descanso
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Fuerza general', 'Remo con barra', 1, 4, '10', 40, 90
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Fuerza general', 'Plancha abdominal', 1, 3, '30 segundos', NULL, 45
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Fuerza general', 'Sentadilla con barra', 3, 4, '8', 55, 120
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Fuerza general', 'Curl femoral', 3, 3, '12', 25, 60
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Fuerza general', 'Press militar con mancuernas', 5, 3, '10', 14, 75
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Fuerza general', 'Jalón al pecho', 5, 3, '12', 35, 60
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Press inclinado con mancuernas', 1, 3, '12', 18, 75
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Aperturas con mancuernas', 1, 3, '12', 10, 60
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Extensión de tríceps en polea', 1, 3, '12', 20, 60
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Dominadas', 2, 3, '6', NULL, 90
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Remo con mancuerna', 2, 3, '12', 18, 75
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Curl martillo', 2, 3, '12', 10, 60
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Press de banca', 4, 4, '10', 45, 90
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Elevaciones laterales', 4, 3, '15', 6, 60
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Jalón al pecho', 5, 4, '12', 30, 75
    UNION ALL
    SELECT 'carlos.mendoza@gym.test', 'Hipertrofia superior', 'Curl de bíceps', 5, 3, '12', 10, 60
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Acondicionamiento inicial', 'Caminata en cinta', 1, 1, '20 minutos', NULL, 0
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Acondicionamiento inicial', 'Sentadilla con barra', 1, 3, '12', 15, 60
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Acondicionamiento inicial', 'Bicicleta estática', 3, 1, '25 minutos', NULL, 0
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Acondicionamiento inicial', 'Plancha abdominal', 3, 3, '20 segundos', NULL, 45
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Acondicionamiento inicial', 'Flexiones de pecho', 5, 3, '8', NULL, 60
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Acondicionamiento inicial', 'Puente de glúteos', 5, 3, '15', NULL, 45
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Sentadilla con barra', 1, 4, '10', 25, 90
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Hip thrust', 1, 4, '12', 30, 90
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Estiramiento de cadera', 2, 3, '30 segundos', NULL, 30
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Prensa de piernas', 4, 4, '12', 70, 90
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Curl femoral', 4, 3, '12', 20, 60
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Extensión de cuádriceps', 4, 3, '12', 15, 60
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Puente de glúteos', 6, 3, '15', NULL, 45
    UNION ALL
    SELECT 'ana.ruiz@gym.test', 'Piernas y glúteos', 'Caminata en cinta', 6, 1, '15 minutos', NULL, 0
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Flexiones de pecho', 1, 3, '15', NULL, 45
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Curl de bíceps', 1, 3, '15', 8, 45
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Caminata en cinta', 2, 1, '30 minutos', NULL, 0
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Plancha abdominal', 2, 3, '40 segundos', NULL, 45
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Sentadilla con barra', 4, 3, '15', 25, 60
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Jalón al pecho', 4, 3, '15', 30, 60
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Bicicleta estática', 6, 1, '30 minutos', NULL, 0
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Resistencia muscular', 'Crunch abdominal', 6, 3, '20', NULL, 45
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Fuerza inicial', 'Press de banca', 1, 3, '8', 30, 90
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Fuerza inicial', 'Remo con mancuerna', 1, 3, '10', 12, 75
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Fuerza inicial', 'Sentadilla con barra', 3, 3, '8', 30, 90
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Fuerza inicial', 'Plancha abdominal', 3, 3, '30 segundos', NULL, 45
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Fuerza inicial', 'Press militar con mancuernas', 5, 3, '10', 10, 75
    UNION ALL
    SELECT 'luis.torres@gym.test', 'Fuerza inicial', 'Curl femoral', 5, 3, '12', 20, 60
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Press de banca', 1, 3, '10', 25, 75
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Aperturas con mancuernas', 1, 3, '12', 6, 60
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Jalón al pecho', 2, 4, '10', 25, 75
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Curl de bíceps', 2, 3, '12', 7, 60
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Sentadilla con barra', 4, 4, '10', 25, 90
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Hip thrust', 4, 3, '12', 25, 75
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Press militar con mancuernas', 5, 3, '10', 8, 75
    UNION ALL
    SELECT 'sofia.vargas@gym.test', 'Hipertrofia general', 'Elevaciones laterales', 5, 3, '12', 4, 60
    UNION ALL
    SELECT 'diego.flores@gym.test', 'Movilidad y bienestar', 'Movilidad de hombros', 1, 3, '12', NULL, 30
    UNION ALL
    SELECT 'diego.flores@gym.test', 'Movilidad y bienestar', 'Estiramiento de cadera', 1, 3, '30 segundos', NULL, 30
    UNION ALL
    SELECT 'diego.flores@gym.test', 'Movilidad y bienestar', 'Caminata en cinta', 3, 1, '25 minutos', NULL, 0
    UNION ALL
    SELECT 'diego.flores@gym.test', 'Movilidad y bienestar', 'Plancha abdominal', 3, 3, '20 segundos', NULL, 40
    UNION ALL
    SELECT 'diego.flores@gym.test', 'Movilidad y bienestar', 'Bicicleta estática', 5, 1, '20 minutos', NULL, 0
    UNION ALL
    SELECT 'diego.flores@gym.test', 'Movilidad y bienestar', 'Puente de glúteos', 5, 3, '12', NULL, 45
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Sentadilla con barra', 1, 3, '12', 20, 75
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Press de banca', 1, 3, '10', 20, 75
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Jalón al pecho', 1, 3, '12', 25, 60
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Hip thrust', 3, 3, '12', 20, 75
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Press militar con mancuernas', 3, 3, '10', 7, 60
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Plancha abdominal', 3, 3, '30 segundos', NULL, 40
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Prensa de piernas', 5, 3, '12', 55, 75
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Remo con mancuerna', 5, 3, '12', 10, 60
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Full body semanal', 'Curl de bíceps', 5, 3, '12', 6, 60
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Cardio y core', 'Caminata en cinta', 2, 1, '30 minutos', NULL, 0
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Cardio y core', 'Plancha abdominal', 2, 3, '30 segundos', NULL, 40
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Cardio y core', 'Bicicleta estática', 4, 1, '25 minutos', NULL, 0
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Cardio y core', 'Crunch abdominal', 4, 3, '15', NULL, 40
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Cardio y core', 'Saltos de tijera', 6, 3, '30 segundos', NULL, 45
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Cardio y core', 'Elevaciones de piernas', 6, 3, '12', NULL, 45
    UNION ALL
    SELECT 'valeria.castro@gym.test', 'Cardio y core', 'Estiramiento de cadera', 7, 2, '30 segundos', NULL, 30
) AS datos
INNER JOIN USUARIO ON USUARIO.correo = datos.correo_cliente AND USUARIO.rol = 'CLIENTE'
INNER JOIN CLIENTE ON CLIENTE.id_usuario = USUARIO.id
INNER JOIN RUTINA ON RUTINA.id_cliente = CLIENTE.id_usuario
  AND RUTINA.id_instructor = @instructor_id
  AND RUTINA.nombre_plan = datos.nombre_plan
INNER JOIN EJERCICIO ON EJERCICIO.nombre = datos.ejercicio
LEFT JOIN DETALLE_RUTINA ON DETALLE_RUTINA.id_rutina = RUTINA.id
  AND DETALLE_RUTINA.id_ejercicio = EJERCICIO.id
  AND DETALLE_RUTINA.dia_semana = datos.dia_semana
WHERE DETALLE_RUTINA.id IS NULL;

COMMIT;
