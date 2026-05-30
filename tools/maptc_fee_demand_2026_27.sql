-- ============================================================
-- MAPTC Fee Demand Setup: Session 2026-27 (session_id = 22)
-- Date: 2026-05-30
-- Fee structure per institution image:
--   4 fee types: Tuition Fee (17500), Book Fees (1000),
--                Mise Fees-I (500), Mise Fees-II (varies by dept/year)
--   Mise Fees-II:
--     I year  - MECH/EEE = 5000  | CIVIL/CT/ECE/IT = 3000
--     II/III  - MECH/EEE = 4000  | CIVIL/CT/ECE/IT = 1000
-- Lateral entry: placed in II year classes by admin
-- ============================================================

-- Step 1: Add 3 new fee types
--   TUTFEE (id=1) already exists. Adding Book Fees, Mise Fees-I, Mise Fees-II.
INSERT INTO feetype
    (is_system, feecategory_id, `type`, code, is_active, description,
     session_id, student_session_id, nature, sub_merchant_id)
VALUES
    (0, NULL, 'Book Fees',    'BOOK_FEE',   'yes', 'Book fees',             NULL, NULL, '', NULL),
    (0, NULL, 'Mise Fees-I',  'MISE_FEE_1', 'yes', 'Miscellaneous fees I',  NULL, NULL, '', NULL),
    (0, NULL, 'Mise Fees-II', 'MISE_FEE_2', 'yes', 'Miscellaneous fees II', NULL, NULL, '', NULL);

-- Step 2: Add I year classes (new admissions 2026-27) + III IT (future use)
INSERT INTO classes (department_id, class, is_active, class_type)
VALUES
    (NULL, 'I CIVIL', 'yes', 'academic'),
    (NULL, 'I CT',    'yes', 'academic'),
    (NULL, 'I ECE',   'yes', 'academic'),
    (NULL, 'I EEE',   'yes', 'academic'),
    (NULL, 'I IT',    'yes', 'academic'),
    (NULL, 'I MECH',  'yes', 'academic'),
    (NULL, 'III IT',  'yes', 'academic');

-- ============================================================
-- NOTE: feemasters is the OLD schema. studentShow.php reads from
-- student_fees_master (new schema) via getStudentFees().
-- Steps 3-6 below write directly to the new schema tables so that
-- the student fee display works immediately after running this script.
-- ============================================================

-- Step 3: Create one fee_group per active academic class.
--   Name format: "<class> Fees <session_label>"  e.g. "I CIVIL Fees 2025-26"
--   Adjust the session label string if the year changes.
INSERT INTO fee_groups (name, nature, is_active)
SELECT CONCAT(c.class, ' Fees 2025-26'), '', 'yes'
FROM classes c
WHERE c.is_active = 'yes'
  AND c.class_type = 'academic'
  AND NOT EXISTS (
      SELECT 1 FROM fee_groups fg
      WHERE fg.name = CONCAT(c.class, ' Fees 2025-26')
  )
ORDER BY c.id;

-- Step 4: Link each fee_group to session 22 via fee_session_groups.
INSERT INTO fee_session_groups (fee_groups_id, session_id, is_active)
SELECT fg.id, 22, 'yes'
FROM fee_groups fg
WHERE fg.name LIKE '% Fees 2025-26'
  AND NOT EXISTS (
      SELECT 1 FROM fee_session_groups fsg
      WHERE fsg.fee_groups_id = fg.id AND fsg.session_id = 22
  );

-- Step 5: Create fee type entries (72 rows = 18 classes × 4 fee types).
--   One row per (fee_session_group, feetype) with the correct amount.
--   Mise Fees-II varies by class and year as per the signed fee structure.
INSERT INTO fee_groups_feetype (fee_session_group_id, feetype_id, amount, fine_type, is_active)
SELECT
    fsg.id AS fee_session_group_id,
    ft.id  AS feetype_id,
    CASE ft.code
        WHEN 'TUTFEE'     THEN 17500.00
        WHEN 'BOOK_FEE'   THEN 1000.00
        WHEN 'MISE_FEE_1' THEN 500.00
        WHEN 'MISE_FEE_2' THEN
            CASE
                -- I year: MECH and EEE pay more
                WHEN c.class IN ('I MECH', 'I EEE')                       THEN 5000.00
                -- I year: remaining departments
                WHEN c.class LIKE 'I %'                                    THEN 3000.00
                -- II and III year: MECH and EEE pay more
                WHEN c.class IN ('II MECH','II EEE','III MECH','III EEE') THEN 4000.00
                -- II and III year: remaining departments
                ELSE 1000.00
            END
    END AS amount,
    'none' AS fine_type,
    'yes'  AS is_active
FROM feetype ft
CROSS JOIN classes c
JOIN fee_groups fg  ON fg.name = CONCAT(c.class, ' Fees 2025-26')
JOIN fee_session_groups fsg ON fsg.fee_groups_id = fg.id AND fsg.session_id = 22
WHERE ft.code IN ('TUTFEE', 'BOOK_FEE', 'MISE_FEE_1', 'MISE_FEE_2')
  AND c.is_active = 'yes'
  AND c.class_type = 'academic'
  AND NOT EXISTS (
      SELECT 1 FROM fee_groups_feetype x
      WHERE x.fee_session_group_id = fsg.id AND x.feetype_id = ft.id
  )
ORDER BY c.id, ft.id;

-- Step 6: Assign all active students in session 22 to their class fee group.
--   One row per student in student_fees_master links the student to their
--   fee_session_group so getStudentFees() can find their fees.
INSERT INTO student_fees_master (student_session_id, fee_session_group_id)
SELECT ss.id AS student_session_id, fsg.id AS fee_session_group_id
FROM student_session ss
JOIN classes c   ON c.id  = ss.class_id
JOIN fee_groups fg  ON fg.name = CONCAT(c.class, ' Fees 2025-26')
JOIN fee_session_groups fsg ON fsg.fee_groups_id = fg.id AND fsg.session_id = 22
WHERE ss.session_id = 22
  AND ss.is_active  = 'yes'
  AND NOT EXISTS (
      SELECT 1 FROM student_fees_master x
      WHERE x.student_session_id = ss.id
        AND x.fee_session_group_id = fsg.id
  );

-- ============================================================
-- LEGACY (feemasters — old schema, kept for reference only).
-- The admin "Fee Master" UI reads from feemasters, but student
-- view pages use student_fees_master. Do NOT rely on feemasters
-- for fee display. Steps 3-6 above are the authoritative setup.
-- ============================================================
-- INSERT INTO feemasters (session_id, feetype_id, class_id, amount, is_active)
-- SELECT 22, ft.id, c.id, <amount>, 'yes'
-- FROM feetype ft CROSS JOIN classes c ...
-- (disabled — feemasters is legacy, do not write new fee data here)

-- ============================================================
-- Verification query (run separately to confirm amounts and counts):
-- ============================================================
-- SELECT fg.name AS fee_group,
--        MAX(CASE WHEN ft.code='TUTFEE'     THEN fgf.amount END) AS tuition,
--        MAX(CASE WHEN ft.code='BOOK_FEE'   THEN fgf.amount END) AS book,
--        MAX(CASE WHEN ft.code='MISE_FEE_1' THEN fgf.amount END) AS mise1,
--        MAX(CASE WHEN ft.code='MISE_FEE_2' THEN fgf.amount END) AS mise2,
--        SUM(fgf.amount) AS total
-- FROM fee_groups fg
-- JOIN fee_session_groups fsg ON fsg.fee_groups_id = fg.id AND fsg.session_id = 22
-- JOIN fee_groups_feetype fgf ON fgf.fee_session_group_id = fsg.id
-- JOIN feetype ft ON ft.id = fgf.feetype_id
-- GROUP BY fg.name ORDER BY fg.name;
--
-- SELECT COUNT(*) AS students_assigned FROM student_fees_master sfm
-- JOIN fee_session_groups fsg ON fsg.id = sfm.fee_session_group_id
-- WHERE fsg.session_id = 22;
