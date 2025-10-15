CREATE TABLE `naac_c6_2_strategy_deployment` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `strategic_plan_description` TEXT,
    `e_governance_implementation_areas` TEXT,
    `document_link_strategic_plan` VARCHAR(255),
    `document_link_e_governance_report` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);