CREATE TABLE `admission_classis` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `department` VARCHAR(255) NOT NULL,
    `class_name` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `admission_flags` VARCHAR(255) NULL
);