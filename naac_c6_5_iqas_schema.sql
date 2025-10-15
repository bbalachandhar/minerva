CREATE TABLE `naac_c6_5_iqas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `iqac_initiatives_description` TEXT,
    `quality_assurance_initiatives` TEXT,
    `document_link_iqac_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);