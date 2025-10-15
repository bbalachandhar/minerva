CREATE TABLE `naac_c2_6_student_performance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `course_code` VARCHAR(50) NOT NULL,
    `student_id` VARCHAR(50) NOT NULL,
    `grade_percentage` DECIMAL(5,2),
    `po_co_attainment_description` TEXT,
    `document_link_results` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);