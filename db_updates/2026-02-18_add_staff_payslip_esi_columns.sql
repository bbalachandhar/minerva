-- Add missing ESI columns to staff_payslip safely (idempotent)
-- Run against production database mcekknagar

SET @schema_name := DATABASE();

SET @has_esi_wage := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'staff_payslip'
      AND COLUMN_NAME = 'esi_wage'
);

SET @has_employee_esi := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'staff_payslip'
      AND COLUMN_NAME = 'employee_esi'
);

SET @has_employer_esi := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'staff_payslip'
      AND COLUMN_NAME = 'employer_esi'
);

SET @sql := IF(
    @has_esi_wage = 0,
    'ALTER TABLE staff_payslip ADD COLUMN esi_wage DECIMAL(10,2) DEFAULT 0.00 AFTER employer_eps',
    'SELECT "esi_wage already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_employee_esi = 0,
    'ALTER TABLE staff_payslip ADD COLUMN employee_esi DECIMAL(10,2) DEFAULT 0.00 AFTER esi_wage',
    'SELECT "employee_esi already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF(
    @has_employer_esi = 0,
    'ALTER TABLE staff_payslip ADD COLUMN employer_esi DECIMAL(10,2) DEFAULT 0.00 AFTER employee_esi',
    'SELECT "employer_esi already exists"'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
