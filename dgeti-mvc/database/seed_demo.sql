-- ============================================================
-- database/seed_demo.sql
-- Datos de prueba para sistema_justificantes
-- Ejecutar DESPUÉS del script principal de creación de tablas
-- ============================================================

USE sistema_justificantes;

-- ── 1. Grupos ──────────────────────────────────────────────
INSERT IGNORE INTO grupo (id_grupo, nombre_grupo, grado, turno) VALUES
(1, '3-A', '3°', 'matutino'),
(2, '3-B', '3°', 'matutino'),
(3, '4-C', '4°', 'vespertino');

-- ── 2. Usuarios (contraseña: "password" con bcrypt) ────────
-- Hash generado con password_hash('password', PASSWORD_BCRYPT)
SET @hash = '$2y$10$l6dBYxKdQK1Ug4gt/GMRgeosx6qtQnCo24OTB2jRJeZSxVBssa5zO';

-- Jefa de servicios (equivale a "admin" en el sistema anterior)
INSERT IGNORE INTO usuario (id_usuario, nombre, apellido, correo, contrasena, id_rol) VALUES
(1, 'Administrador', 'DGETI',          'admin@cetis.edu.mx',        @hash, (SELECT id_rol FROM rol WHERE nombre_rol = 'jefa_servicios')),
(2, 'María',         'Sánchez Ruiz',   'docente@cetis.edu.mx',      @hash, (SELECT id_rol FROM rol WHERE nombre_rol = 'docente')),
(3, 'Juan',          'García López',   'alumno@cetis.edu.mx',       @hash, (SELECT id_rol FROM rol WHERE nombre_rol = 'alumno')),
(4, 'Laura',         'Reyes Díaz',     'orientadora@cetis.edu.mx',  @hash, (SELECT id_rol FROM rol WHERE nombre_rol = 'orientadora')),
(5, 'Carlos',        'Mendoza Torres', 'tutor@cetis.edu.mx',        @hash, (SELECT id_rol FROM rol WHERE nombre_rol = 'tutor_institucional')),
(6, 'Ana',           'López Martínez', 'alumno2@cetis.edu.mx',      @hash, (SELECT id_rol FROM rol WHERE nombre_rol = 'alumno'));

-- ── 3. Docentes ────────────────────────────────────────────
INSERT IGNORE INTO docente (id_docente, id_usuario, especialidad) VALUES
(1, 2, 'Informática'),
(2, 5, 'Matemáticas');

-- ── 4. Actualizar tutor del grupo 1 ────────────────────────
UPDATE grupo SET id_tutor_institucional = 2 WHERE id_grupo = 1;

-- ── 5. Alumnos ─────────────────────────────────────────────
INSERT IGNORE INTO alumno (id_alumno, id_usuario, matricula, nombre_tutor, correo_tutor, telefono_tutor, id_grupo, es_jefe_grupo) VALUES
(1, 3, 'CETIS-0001', 'Roberto García Pérez', 'tutor.garcia@gmail.com', '4421234567', 1, 1),
(2, 6, 'CETIS-0002', 'Sofía Martínez Ruiz',  'sofia.mtz@gmail.com',    '4429876543', 1, 0);

-- ── 6. Materias ────────────────────────────────────────────
INSERT IGNORE INTO materia (id_materia, nombre, id_docente, id_grupo) VALUES
(1, 'Programación',     1, 1),
(2, 'Bases de Datos',   1, 1),
(3, 'Matemáticas',      2, 1);

-- ── 7. Asistencias de ejemplo ──────────────────────────────
INSERT IGNORE INTO asistencia (id_alumno, id_materia, id_docente, fecha, estatus) VALUES
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY),  'ausente'),
(1, 2, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY),  'ausente'),
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY),  'presente'),
(2, 1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY),  'ausente');

-- ── 8. Justificantes de ejemplo ────────────────────────────
INSERT IGNORE INTO justificante (id_alumno, id_asistencia, tipo_motivo, descripcion_motivo, estado, fecha_resolucion, id_orientadora) VALUES
(1, 1, 'Salud',    'Consulta médica',        'Aprobado',  DATE_SUB(CURDATE(), INTERVAL 4 DAY), 4),
(1, 2, 'Personal', 'Asunto familiar urgente',     'Pendiente', NULL,                                 NULL),
(2, 4, 'Salud',    'Malestar general',             'Generado',  NULL,                                 NULL);

-- ── Fin del seed ────────────────────────────────────────────
