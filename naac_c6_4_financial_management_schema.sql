CREATE TABLE `naac_c6_4_financial_management` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `academic_year` VARCHAR(9) NOT NULL,
    `internal_audits_regularity` TEXT,
    `external_audits_regularity` TEXT,
    `funds_grants_received_lakhs` DECIMAL(10,2),
    `resource_mobilization_strategies` TEXT,
    `document_link_audit_reports` VARCHAR(255),
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);