-- =============================================================================
-- Merit Scholarship / Exam Marks Migration
-- Run once on MCE production database (mcekknagar)
-- =============================================================================

-- 1. Add exam score columns to online_admissions
--    (safe to re-run: uses INFORMATION_SCHEMA guard via procedure)
DROP PROCEDURE IF EXISTS _merit_add_col_oa;
DELIMITER //
CREATE PROCEDURE _merit_add_col_oa()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'online_admissions' AND COLUMN_NAME = 'mat_exam_score'
    ) THEN
        ALTER TABLE online_admissions
            ADD COLUMN mat_exam_score      DECIMAL(6,2) DEFAULT NULL
                COMMENT 'Raw score in MCE merit exam (out of 100)',
            ADD COLUMN mat_exam_percentage DECIMAL(5,2) DEFAULT NULL
                COMMENT 'Percentage in MCE merit exam (= score since total is 100)';
    END IF;
END //
DELIMITER ;
CALL _merit_add_col_oa();
DROP PROCEDURE IF EXISTS _merit_add_col_oa;

-- 2. Add percentage range + fee discount link to scholarship_types
DROP PROCEDURE IF EXISTS _merit_add_col_st;
DELIMITER //
CREATE PROCEDURE _merit_add_col_st()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'scholarship_types' AND COLUMN_NAME = 'min_percentage'
    ) THEN
        ALTER TABLE scholarship_types
            ADD COLUMN min_percentage   DECIMAL(5,2) DEFAULT NULL
                COMMENT 'Minimum percentage for this tier (inclusive)',
            ADD COLUMN max_percentage   DECIMAL(5,2) DEFAULT NULL
                COMMENT 'Maximum percentage for this tier (inclusive)',
            ADD COLUMN fees_discount_id INT          DEFAULT NULL
                COMMENT 'Linked fees_discounts.id for auto-apply at onboarding';
    END IF;
END //
DELIMITER ;
CALL _merit_add_col_st();
DROP PROCEDURE IF EXISTS _merit_add_col_st;

-- 3. Update MAT-SET Cat 1–5 (IDs 16–20) with new amounts and percentage ranges
UPDATE scholarship_types
    SET name            = 'MAT-SET (Merit Exam) - Category 1 (90-100%)',
        amount          = 50000.00,
        min_percentage  = 90.00,
        max_percentage  = 100.00
    WHERE id = 16;

UPDATE scholarship_types
    SET name            = 'MAT-SET (Merit Exam) - Category 2 (75-89%)',
        amount          = 30000.00,
        min_percentage  = 75.00,
        max_percentage  = 89.99
    WHERE id = 17;

UPDATE scholarship_types
    SET name            = 'MAT-SET (Merit Exam) - Category 3 (60-74%)',
        amount          = 20000.00,
        min_percentage  = 60.00,
        max_percentage  = 74.99
    WHERE id = 18;

UPDATE scholarship_types
    SET name            = 'MAT-SET (Merit Exam) - Category 4 (50-59%)',
        amount          = 10000.00,
        min_percentage  = 50.00,
        max_percentage  = 59.99
    WHERE id = 19;

UPDATE scholarship_types
    SET name            = 'MAT-SET (Merit Exam) - Category 5 (Standard, 0-49%)',
        amount          = 5000.00,
        min_percentage  = 0.00,
        max_percentage  = 49.99
    WHERE id = 20;

-- 4. Create fees_discounts entries for each merit tier (idempotent)
INSERT INTO fees_discounts (name, code, type, amount, is_active, description)
    SELECT 'Merit Scholarship Cat 1 (Rs 50,000)', 'MAT_CAT1', 'fixed', 50000.00, 'yes',
           'Auto-created for MAT-SET merit exam Category 1'
    WHERE NOT EXISTS (SELECT 1 FROM fees_discounts WHERE code = 'MAT_CAT1');

INSERT INTO fees_discounts (name, code, type, amount, is_active, description)
    SELECT 'Merit Scholarship Cat 2 (Rs 30,000)', 'MAT_CAT2', 'fixed', 30000.00, 'yes',
           'Auto-created for MAT-SET merit exam Category 2'
    WHERE NOT EXISTS (SELECT 1 FROM fees_discounts WHERE code = 'MAT_CAT2');

INSERT INTO fees_discounts (name, code, type, amount, is_active, description)
    SELECT 'Merit Scholarship Cat 3 (Rs 20,000)', 'MAT_CAT3', 'fixed', 20000.00, 'yes',
           'Auto-created for MAT-SET merit exam Category 3'
    WHERE NOT EXISTS (SELECT 1 FROM fees_discounts WHERE code = 'MAT_CAT3');

INSERT INTO fees_discounts (name, code, type, amount, is_active, description)
    SELECT 'Merit Scholarship Cat 4 (Rs 10,000)', 'MAT_CAT4', 'fixed', 10000.00, 'yes',
           'Auto-created for MAT-SET merit exam Category 4'
    WHERE NOT EXISTS (SELECT 1 FROM fees_discounts WHERE code = 'MAT_CAT4');

INSERT INTO fees_discounts (name, code, type, amount, is_active, description)
    SELECT 'Merit Scholarship Cat 5 Standard (Rs 5,000)', 'MAT_CAT5', 'fixed', 5000.00, 'yes',
           'Auto-created for MAT-SET merit exam Category 5 (standard)'
    WHERE NOT EXISTS (SELECT 1 FROM fees_discounts WHERE code = 'MAT_CAT5');

-- 5. Link scholarship_types.fees_discount_id to their fees_discounts rows
UPDATE scholarship_types st
    JOIN fees_discounts fd ON fd.code = 'MAT_CAT1'
    SET st.fees_discount_id = fd.id
    WHERE st.id = 16;

UPDATE scholarship_types st
    JOIN fees_discounts fd ON fd.code = 'MAT_CAT2'
    SET st.fees_discount_id = fd.id
    WHERE st.id = 17;

UPDATE scholarship_types st
    JOIN fees_discounts fd ON fd.code = 'MAT_CAT3'
    SET st.fees_discount_id = fd.id
    WHERE st.id = 18;

UPDATE scholarship_types st
    JOIN fees_discounts fd ON fd.code = 'MAT_CAT4'
    SET st.fees_discount_id = fd.id
    WHERE st.id = 19;

UPDATE scholarship_types st
    JOIN fees_discounts fd ON fd.code = 'MAT_CAT5'
    SET st.fees_discount_id = fd.id
    WHERE st.id = 20;

-- 6. Verify results
SELECT id, name, amount, min_percentage, max_percentage, fees_discount_id
    FROM scholarship_types
    WHERE id IN (16,17,18,19,20)
    ORDER BY id;

SELECT id, name, code, amount FROM fees_discounts WHERE code LIKE 'MAT_%' ORDER BY id;
