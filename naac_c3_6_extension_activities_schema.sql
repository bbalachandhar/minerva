CREATE TABLE `naac_c3_6_extension_activities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `activity_name` VARCHAR(255) NOT NULL,
    `organizing_unit` VARCHAR(255),
    `number_of_students_participated` INT,
    `number_of_public_benefited` INT,
    `extension_activity_impact` TEXT,
    `document_link_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);