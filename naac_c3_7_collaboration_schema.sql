CREATE TABLE `naac_c3_7_collaboration` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `partner_organization` VARCHAR(255) NOT NULL,
    `type_of_collaboration` VARCHAR(255),
    `purpose_of_collaboration` TEXT,
    `document_link_mou` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);