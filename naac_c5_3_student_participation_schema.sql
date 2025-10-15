CREATE TABLE `naac_c5_3_student_participation` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `activity_name` VARCHAR(255) NOT NULL,
    `activity_type` VARCHAR(255),
    `number_of_students_participated` INT,
    `awards_medals_won` TEXT,
    `promotion_of_activities_description` TEXT,
    `document_link_activity_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);