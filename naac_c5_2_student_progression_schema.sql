CREATE TABLE `naac_c5_2_student_progression` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `total_outgoing_students` INT,
    `students_placed` INT,
    `students_to_higher_education` INT,
    `students_qualified_competitive_exams` INT,
    `progression_facilitation_description` TEXT,
    `document_link_placement_report` VARCHAR(255),
    `document_link_higher_education_data` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);