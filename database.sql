EXEC sp_msforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all"

DROP TABLE IF EXISTS [academic_records];
DROP TABLE IF EXISTS [class_schedules];
DROP TABLE IF EXISTS [course_sections];
DROP TABLE IF EXISTS [subject_prerequisites];
DROP TABLE IF EXISTS [teachers];
DROP TABLE IF EXISTS [subjects];
DROP TABLE IF EXISTS [users];

-- 1. Usuarios
CREATE TABLE [users] (
    [id] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [name] VARCHAR(255) NOT NULL,
    [email] VARCHAR(255) NOT NULL UNIQUE,
    [password] VARCHAR(255) NOT NULL,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL
);

-- 2. Materias
CREATE TABLE [subjects] (
    [id] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [name] VARCHAR(255) NOT NULL,
    [code] VARCHAR(255) NOT NULL UNIQUE,
    [semester] INT NOT NULL,
    [credits] INT NOT NULL DEFAULT 1,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL
);

-- 3. Maestros
CREATE TABLE [teachers] (
    [id] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [name] VARCHAR(255) NOT NULL,
    [email] VARCHAR(255) NULL,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL
);

-- 4. Prerrequisitos de Materias (Seriación)
CREATE TABLE [subject_prerequisites] (
    [id] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [subject_id] BIGINT NOT NULL,
    [prerequisite_id] BIGINT NOT NULL,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL,

    CONSTRAINT [subject_prerequisites_subject_id_foreign] 
        FOREIGN KEY ([subject_id]) REFERENCES [subjects] ([id]) ON DELETE CASCADE,
    CONSTRAINT [subject_prerequisites_prerequisite_id_foreign] 
        FOREIGN KEY ([prerequisite_id]) REFERENCES [subjects] ([id]) ON DELETE NO ACTION 
);

-- 5. Secciones/Grupos
CREATE TABLE [course_sections] (
    [id] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [subject_id] BIGINT NOT NULL,
    [teacher_id] BIGINT NOT NULL,
    [group_code] VARCHAR(255) NOT NULL,
    [capacity] INT NOT NULL DEFAULT 30,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL,

    CONSTRAINT [course_sections_subject_id_foreign] 
        FOREIGN KEY ([subject_id]) REFERENCES [subjects] ([id]) ON DELETE CASCADE,
    CONSTRAINT [course_sections_teacher_id_foreign] 
        FOREIGN KEY ([teacher_id]) REFERENCES [teachers] ([id]) ON DELETE CASCADE
);

-- 6. Horarios de Clases
CREATE TABLE [class_schedules] (
    [id] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [course_section_id] BIGINT NOT NULL,
    [day_of_week] VARCHAR(20) NOT NULL CHECK ([day_of_week] IN ('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')),
    [start_time] TIME NOT NULL,
    [end_time] TIME NOT NULL,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL,

    CONSTRAINT [class_schedules_course_section_id_foreign] 
        FOREIGN KEY ([course_section_id]) REFERENCES [course_sections] ([id]) ON DELETE CASCADE
);

-- 7. Historial Académico
CREATE TABLE [academic_records] (
    [id] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [user_id] BIGINT NOT NULL,
    [subject_id] BIGINT NOT NULL,
    [status] VARCHAR(20) NOT NULL DEFAULT 'enrolled' CHECK ([status] IN ('passed', 'failed', 'enrolled')),
    [grade] DECIMAL(5, 2) NULL,
    [created_at] DATETIME NULL,
    [updated_at] DATETIME NULL,

    CONSTRAINT [academic_records_user_id_foreign] 
        FOREIGN KEY ([user_id]) REFERENCES [users] ([id]) ON DELETE CASCADE,
    CONSTRAINT [academic_records_subject_id_foreign] 
        FOREIGN KEY ([subject_id]) REFERENCES [subjects] ([id]) ON DELETE CASCADE
);

-- Reactivar Checks
EXEC sp_msforeachtable "ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all"

print 'Tablas creadas. Iniciando Inserción...'


INSERT INTO [teachers] ([name], [email], [created_at], [updated_at]) VALUES
('Prof. Alejandro García', 'alejandro@uni.edu', GETDATE(), GETDATE()),
('Prof. Beatriz López', 'beatriz@uni.edu', GETDATE(), GETDATE()),
('Prof. Carlos Méndez', 'carlos@uni.edu', GETDATE(), GETDATE()),
('Prof. Diana Ruiz', 'diana@uni.edu', GETDATE(), GETDATE()),
('Prof. Eduardo Vargas', 'eduardo@uni.edu', GETDATE(), GETDATE()),
('Prof. Fernanda Castillo', 'fernanda@uni.edu', GETDATE(), GETDATE()),
('Prof. Gabriel Soto', 'gabriel@uni.edu', GETDATE(), GETDATE()),
('Prof. Hilda Miranda', 'hilda@uni.edu', GETDATE(), GETDATE()),
('Prof. Ignacio Silva', 'ignacio@uni.edu', GETDATE(), GETDATE()),
('Prof. Julia Romero', 'julia@uni.edu', GETDATE(), GETDATE()),
('Prof. Kevin Duran', 'kevin@uni.edu', GETDATE(), GETDATE()),
('Prof. Laura Paredes', 'laura@uni.edu', GETDATE(), GETDATE()),
('Prof. Manuel Ortiz', 'manuel@uni.edu', GETDATE(), GETDATE()),
('Prof. Natalia Cruz', 'natalia@uni.edu', GETDATE(), GETDATE()),
('Prof. Oscar Rivas', 'oscar@uni.edu', GETDATE(), GETDATE()),
('Prof. Patricia Vega', 'patricia@uni.edu', GETDATE(), GETDATE()),
('Prof. Quique Flores', 'quique@uni.edu', GETDATE(), GETDATE()),
('Prof. Rosa Campos', 'rosa@uni.edu', GETDATE(), GETDATE()),
('Prof. Sergio Navarro', 'sergio@uni.edu', GETDATE(), GETDATE()),
('Prof. Tania Rios', 'tania@uni.edu', GETDATE(), GETDATE());

-- Insertamos IDs explícitos con IDENTITY_INSERT ON
SET IDENTITY_INSERT [subjects] ON;

INSERT INTO [subjects] ([id], [name], [code], [semester], [credits], [created_at], [updated_at]) VALUES
(1, 'Programación I', 'PRO-101', 1, 6, GETDATE(), GETDATE()),
(2, 'Cálculo I', 'CAL-101', 1, 6, GETDATE(), GETDATE()),
(3, 'Álgebra I', 'ALG-101', 1, 5, GETDATE(), GETDATE()),
(4, 'Física I', 'FIS-101', 1, 5, GETDATE(), GETDATE()),
(5, 'Inglés I', 'ING-101', 1, 4, GETDATE(), GETDATE()),
(6, 'Base de Datos I', 'BDD-101', 1, 6, GETDATE(), GETDATE()),
(7, 'Redes I', 'RED-101', 1, 5, GETDATE(), GETDATE()),
(8, 'Ética I', 'ETI-101', 1, 3, GETDATE(), GETDATE()),
(9, 'Contabilidad I', 'CON-101', 1, 4, GETDATE(), GETDATE()),
(10, 'Sistemas I', 'SIS-101', 1, 5, GETDATE(), GETDATE()),
(11, 'Programación II', 'PRO-201', 2, 6, GETDATE(), GETDATE()),
(12, 'Cálculo II', 'CAL-201', 2, 6, GETDATE(), GETDATE()),
(13, 'Álgebra II', 'ALG-201', 2, 5, GETDATE(), GETDATE()),
(14, 'Física II', 'FIS-201', 2, 5, GETDATE(), GETDATE()),
(15, 'Inglés II', 'ING-201', 2, 4, GETDATE(), GETDATE()),
(16, 'Base de Datos II', 'BDD-201', 2, 6, GETDATE(), GETDATE()),
(17, 'Redes II', 'RED-201', 2, 5, GETDATE(), GETDATE()),
(18, 'Ética II', 'ETI-201', 2, 3, GETDATE(), GETDATE()),
(19, 'Contabilidad II', 'CON-201', 2, 4, GETDATE(), GETDATE()),
(20, 'Sistemas II', 'SIS-201', 2, 5, GETDATE(), GETDATE()),
(21, 'Programación III', 'PRO-301', 3, 6, GETDATE(), GETDATE()),
(22, 'Cálculo III', 'CAL-301', 3, 6, GETDATE(), GETDATE()),
(23, 'Álgebra III', 'ALG-301', 3, 5, GETDATE(), GETDATE()),
(24, 'Física III', 'FIS-301', 3, 5, GETDATE(), GETDATE()),
(25, 'Inglés III', 'ING-301', 3, 4, GETDATE(), GETDATE()),
(26, 'Base de Datos III', 'BDD-301', 3, 6, GETDATE(), GETDATE()),
(27, 'Redes III', 'RED-301', 3, 5, GETDATE(), GETDATE()),
(28, 'Ética III', 'ETI-301', 3, 3, GETDATE(), GETDATE()),
(29, 'Contabilidad III', 'CON-301', 3, 4, GETDATE(), GETDATE()),
(30, 'Sistemas III', 'SIS-301', 3, 5, GETDATE(), GETDATE());

SET IDENTITY_INSERT [subjects] OFF;

INSERT INTO [subject_prerequisites] ([subject_id], [prerequisite_id], [created_at], [updated_at]) 
SELECT id, id - 10, GETDATE(), GETDATE() FROM [subjects] WHERE semester = 2;

INSERT INTO [subject_prerequisites] ([subject_id], [prerequisite_id], [created_at], [updated_at]) 
SELECT id, id - 10, GETDATE(), GETDATE() FROM [subjects] WHERE semester = 3;

-- Generar Secciones
-- Asignamos maestro aleatorio usando CHECKSUM(NEWID())
INSERT INTO [course_sections] ([subject_id], [teacher_id], [group_code], [capacity], [created_at], [updated_at])
SELECT id, (ABS(CHECKSUM(NEWID())) % 20) + 1, 'A', 30, GETDATE(), GETDATE() FROM [subjects];

INSERT INTO [course_sections] ([subject_id], [teacher_id], [group_code], [capacity], [created_at], [updated_at])
SELECT id, (ABS(CHECKSUM(NEWID())) % 20) + 1, 'B', 30, GETDATE(), GETDATE() FROM [subjects];

-- HORARIOS VARIDADOS
-- Lógica: Usamos el ID de la sección para calcular la hora de inicio: 7, 9, 11, 13, 15
-- (id % 5) da un valor de 0 a 4. Multiplicamos por 2 para steps de 2 horas (0, 2, 4, 8). Sumamos 7 (7, 9, 11, 15).
-- Día 1
INSERT INTO [class_schedules] ([course_section_id], [day_of_week], [start_time], [end_time], [created_at], [updated_at])
SELECT 
    id, 
    CASE WHEN group_code = 'A' THEN 'Monday' ELSE 'Tuesday' END,
    DATEADD(HOUR, (id % 5) * 2 + 7, CAST('00:00' AS TIME)),
    DATEADD(HOUR, (id % 5) * 2 + 9, CAST('00:00' AS TIME)),
    GETDATE(), GETDATE() 
FROM [course_sections];

-- Día 2 (Mismo horario, diferente día)
INSERT INTO [class_schedules] ([course_section_id], [day_of_week], [start_time], [end_time], [created_at], [updated_at])
SELECT 
    id, 
    CASE WHEN group_code = 'A' THEN 'Wednesday' ELSE 'Thursday' END,
    DATEADD(HOUR, (id % 5) * 2 + 7, CAST('00:00' AS TIME)),
    DATEADD(HOUR, (id % 5) * 2 + 9, CAST('00:00' AS TIME)),
    GETDATE(), GETDATE() 
FROM [course_sections];

-- USUARIOS
SET IDENTITY_INSERT [users] ON;

INSERT INTO [users] ([id], [name], [email], [password], [created_at], [updated_at]) VALUES
(1, 'Juan Nuevo (Sem 1)', 'juan@test.com', '$2y$10$HASH', GETDATE(), GETDATE()),
(2, 'Pedro Regular (Sem 2)', 'pedro@test.com', '$2y$10$HASH', GETDATE(), GETDATE()),
(3, 'Beto Irregular (Reprobado)', 'beto@test.com', '$2y$10$HASH', GETDATE(), GETDATE());

SET IDENTITY_INSERT [users] OFF;

-- Generar 20 alumnos más
DECLARE @i INT = 4;
WHILE @i <= 24
BEGIN
    INSERT INTO [users] ([name], [email], [password], [created_at], [updated_at])
    VALUES ('Alumno Random ' + CAST(@i AS VARCHAR), 'alumno' + CAST(@i AS VARCHAR) + '@test.com', 'password_hash', GETDATE(), GETDATE());
    SET @i = @i + 1;
END

-- HISTORIAL ACADEMICO

-- 1. Pedro: Excelencia (Todo pasado 90-100)
INSERT INTO [academic_records] ([user_id], [subject_id], [status], [grade], [created_at], [updated_at])
SELECT 2, id, 'passed', (ABS(CHECKSUM(NEWID())) % 11) + 90, GETDATE(), GETDATE() FROM [subjects] WHERE semester = 1;

-- 2. Beto: Reprobó 2 materias al azar del sem 1
INSERT INTO [academic_records] ([user_id], [subject_id], [status], [grade], [created_at], [updated_at])
SELECT 3, id, 
    CASE WHEN (ABS(CHECKSUM(NEWID())) % 100) < 30 THEN 'failed' ELSE 'passed' END, -- 30% chance fail
    0, -- update later
    GETDATE(), GETDATE() 
FROM [subjects] WHERE semester = 1;

-- 3. Alumnos 4-10: Pasaron Semestre 1 con notas variadas (60-100)
INSERT INTO [academic_records] ([user_id], [subject_id], [status], [grade], [created_at], [updated_at])
SELECT u.id, s.id, 'passed', (ABS(CHECKSUM(NEWID())) % 41) + 60, GETDATE(), GETDATE()
FROM [users] u
JOIN [subjects] s ON s.semester = 1
WHERE u.id BETWEEN 4 AND 10;

-- 4. Alumnos 11-20: Pasaron Semestre 1 y 2 (Algunos irregulares en el 2)
INSERT INTO [academic_records] ([user_id], [subject_id], [status], [grade], [created_at], [updated_at])
SELECT u.id, s.id, 
    CASE 
        WHEN s.semester = 1 THEN 'passed' 
        WHEN (ABS(CHECKSUM(NEWID())) % 100) < 80 THEN 'passed' -- 80% pass rate en sem 2
        ELSE 'failed' 
    END, 
    0, -- placeholder
    GETDATE(), GETDATE()
FROM [users] u
JOIN [subjects] s ON s.semester <= 2
WHERE u.id BETWEEN 11 AND 20;

-- Actualizar calificaciones finales basadas en el status generado arriba
UPDATE [academic_records]
SET grade = (ABS(CHECKSUM(NEWID())) % 41) + 60 -- 60 a 100
WHERE status = 'passed' AND grade = 0;

UPDATE [academic_records]
SET grade = (ABS(CHECKSUM(NEWID())) % 50) + 10 -- 10 a 59
WHERE status = 'failed' AND grade = 0;

PRINT '========================================='
PRINT 'VERIFICACIÓN DE TABLAS'
PRINT '========================================='

SELECT 'Users' as TableName, COUNT(*) as Total FROM [users]
UNION ALL
SELECT 'Subjects', COUNT(*) FROM [subjects]
UNION ALL
SELECT 'Teachers', COUNT(*) FROM [teachers]
UNION ALL
SELECT 'Records', COUNT(*) FROM [academic_records];

PRINT '========================================='
PRINT 'MUESTRA DE HISTORIAL (Alumno ID 15)'
PRINT '========================================='

SELECT 
    u.name AS Alumno,
    s.name AS Materia,
    s.semester AS Semestre,
    ar.status AS Estado,
    ar.grade AS Calificacion
FROM academic_records ar
JOIN users u ON ar.user_id = u.id
JOIN subjects s ON ar.subject_id = s.id
WHERE u.id = 15
ORDER BY s.semester, s.name;

PRINT '========================================='
PRINT 'MUESTRA DE HORARIOS (Grupo A - Programación I)'
PRINT '========================================='

SELECT TOP 5
    s.name AS Materia,
    cs.group_code AS Grupo,
    sch.day_of_week AS Dia,
    sch.start_time AS Inicio,
    sch.end_time AS Fin
FROM class_schedules sch
JOIN course_sections cs ON sch.course_section_id = cs.id
JOIN subjects s ON cs.subject_id = s.id
WHERE cs.group_code = 'A'
ORDER BY sch.start_time;