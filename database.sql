-- 1. Usuarios
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);

-- 2. Materias
CREATE TABLE `subjects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL UNIQUE,
    `semester` INT NOT NULL,
    `credits` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);

-- 3. Maestros
CREATE TABLE `teachers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
);

-- 4. Prerrequisitos de Materias (Seriación)
CREATE TABLE `subject_prerequisites` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `subject_id` BIGINT UNSIGNED NOT NULL,
    `prerequisite_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    
    CONSTRAINT `subject_prerequisites_subject_id_foreign` 
        FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `subject_prerequisites_prerequisite_id_foreign` 
        FOREIGN KEY (`prerequisite_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
);

-- 5. Secciones/Grupos
CREATE TABLE `course_sections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `subject_id` BIGINT UNSIGNED NOT NULL,
    `teacher_id` BIGINT UNSIGNED NOT NULL,
    `group_code` VARCHAR(255) NOT NULL,
    `capacity` INT NOT NULL DEFAULT 30,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,

    CONSTRAINT `course_sections_subject_id_foreign` 
        FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `course_sections_teacher_id_foreign` 
        FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE
);

-- 6. Horarios de Clases
CREATE TABLE `class_schedules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `course_section_id` BIGINT UNSIGNED NOT NULL,
    `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,

    CONSTRAINT `class_schedules_course_section_id_foreign` 
        FOREIGN KEY (`course_section_id`) REFERENCES `course_sections` (`id`) ON DELETE CASCADE
);

-- 7. Historial Académico
CREATE TABLE `academic_records` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `subject_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('passed', 'failed', 'enrolled') NOT NULL DEFAULT 'enrolled',
    `grade` DECIMAL(5, 2) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,

    CONSTRAINT `academic_records_user_id_foreign` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `academic_records_subject_id_foreign` 
        FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
);