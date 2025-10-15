CREATE TABLE `naac_c4_2_library_resources` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `number_of_books` INT,
    `number_of_e_journals` INT,
    `integrated_library_management_system` TEXT,
    `library_e_resources_description` TEXT,
    `library_usage_details` TEXT,
    `document_link_library_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);