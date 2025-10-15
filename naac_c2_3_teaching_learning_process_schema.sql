CREATE TABLE `naac_c2_3_teaching_learning_process` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `course_code` VARCHAR(50) NOT NULL,
    `teacher_name` VARCHAR(255) NOT NULL,
    `teaching_methodologies_used` TEXT,
    `ict_tools_used` TEXT,
    `percentage_teachers_using_ict` DECIMAL(5,2),
    `document_link_teaching_plan` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);