CREATE TABLE `naac_c7_2_best_practices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `best_practice_title_1` VARCHAR(255),
    `best_practice_description_1` TEXT,
    `best_practice_title_2` VARCHAR(255),
    `best_practice_description_2` TEXT,
    `document_link_best_practice_1` VARCHAR(255),
    `document_link_best_practice_2` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);