CREATE TABLE `naac_c2_1_student_enrollment` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `total_sanctioned_seats` INT,
    `total_students_admitted` INT,
    `students_from_other_states` INT,
    `students_from_other_countries` INT,
    `reserved_category_seats_filled` INT,
    `admission_process_description` TEXT,
    `document_link_admission_policy` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);