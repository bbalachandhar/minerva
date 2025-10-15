CREATE TABLE `naac_c1_2_academic_flexibility` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `program_name` VARCHAR(255) NOT NULL,
    `elective_courses_offered` INT,
    `interdisciplinary_courses_offered` INT,
    `credit_transfer_details` TEXT,
    `experiential_learning_details` TEXT,
    `students_undertaking_internships` INT,
    `document_link_electives` VARCHAR(255),
    `document_link_internship_policy` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);