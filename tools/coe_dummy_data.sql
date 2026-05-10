-- =============================================================================
-- CoE Module — Dummy Data
-- Demonstrates: arrear filter (session / exam-event / active-only)
--
-- Anchors on existing data:
--   exam_group_class_batch_exams:
--     id=1  Nov/Dec 2025 - CSE   (eg_id=1, session=21, main, date_from=2025-11-15)
--     id=3  Apr/May 2026 - CSE   (eg_id=2, session=21, main, date_from=2026-04-10)  ← results already exist
--     id=4  Nov/Dec 2025 Arrear  (eg_id=3, session=21, arrear, date_from=2025-11-20)
--   students: id 2-6 (AAKASH K, AJAI V, AKILAN K, AKSHAYA N + student 6)
--   subjects: 4=CS3401 Algorithms, 5=CS3451 Intro to OS, 6=GE3451 Env Sciences
--             1=CS3452 TOC, 2=CS3491 AIML, 3=CS3492 DBMS (used by existing batch_3 data)
-- =============================================================================

-- ─────────────────────────────────────────────────────────────────────────────
-- 1. Enroll students in exam event 1 (Nov/Dec 2025 - CSE, I/II sem)
--    exam_group_class_batch_exam_students
-- ─────────────────────────────────────────────────────────────────────────────
INSERT IGNORE INTO exam_group_class_batch_exam_students
    (exam_group_class_batch_exam_id, student_id, student_session_id, roll_no, is_active)
VALUES
    (1, 2, 2, 101, 1),   -- AAKASH K
    (1, 3, 3, 102, 1),   -- AJAI V
    (1, 4, 4, 103, 1),   -- AKILAN K
    (1, 5, 5, 104, 1);   -- AKSHAYA N

-- ─────────────────────────────────────────────────────────────────────────────
-- 2. Results — Nov/Dec 2025 End-Semester (batch_exam_id=1)
--    Subjects: CS3401(4), CS3451(5), GE3451(6)  — each 4 credits
--    Pass criteria: EA ≥ 28  AND  Total ≥ 50
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO coe_student_results
    (student_id, exam_group_class_batch_exam_id, subject_id,
     internal_marks, external_marks, total_marks, max_internal, max_external, max_total,
     credits, grade, grade_points, result_status, is_arrear, moderation_applied, is_published, published_at)
VALUES

-- ── AAKASH K (student 2) ──
-- CS3401 Algorithms : IA=20, EA=25 (<28) → FAIL
(2, 1, 4,  20.00, 25.00, 45.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- CS3451 Intro to OS : IA=24, EA=55 → pass  A
(2, 1, 5,  24.00, 55.00, 79.00, 30.00, 70.00, 100.00, 4, 'A', 8.00, 'pass', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- GE3451 Env Sciences : IA=26, EA=60 → pass  A+
(2, 1, 6,  26.00, 60.00, 86.00, 30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00, 1, '2025-12-10 10:00:00'),

-- ── AJAI V (student 3) ──
-- CS3401 Algorithms : IA=15, EA=22 (<28) → FAIL
(3, 1, 4,  15.00, 22.00, 37.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- CS3451 Intro to OS : IA=16, EA=24 (<28) → FAIL
(3, 1, 5,  16.00, 24.00, 40.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- GE3451 Env Sciences : IA=22, EA=45 → pass  A
(3, 1, 6,  22.00, 45.00, 67.00, 30.00, 70.00, 100.00, 4, 'A', 8.00, 'pass', 0, 0.00, 1, '2025-12-10 10:00:00'),

-- ── AKILAN K (student 4) — all pass ──
-- CS3401 Algorithms : IA=28, EA=65 → pass  O
(4, 1, 4,  28.00, 65.00, 93.00, 30.00, 70.00, 100.00, 4, 'O', 10.00, 'pass', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- CS3451 Intro to OS : IA=26, EA=58 → pass  A+
(4, 1, 5,  26.00, 58.00, 84.00, 30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- GE3451 Env Sciences : IA=27, EA=62 → pass  A+
(4, 1, 6,  27.00, 62.00, 89.00, 30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00, 1, '2025-12-10 10:00:00'),

-- ── AKSHAYA N (student 5) — all fail ──
-- CS3401 Algorithms : IA=14, EA=20 (<28) → FAIL
(5, 1, 4,  14.00, 20.00, 34.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- CS3451 Intro to OS : IA=15, EA=22 (<28) → FAIL
(5, 1, 5,  15.00, 22.00, 37.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 0, 0.00, 1, '2025-12-10 10:00:00'),
-- GE3451 Env Sciences : IA=18, EA=28 but Total=46 (<50) → FAIL
(5, 1, 6,  18.00, 28.00, 46.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 0, 0.00, 1, '2025-12-10 10:00:00');

-- ─────────────────────────────────────────────────────────────────────────────
-- 3. SGPA — Nov/Dec 2025 End-Semester (batch_exam_id=1)
--    Grade scale: O=10, A+=9, A=8, B+=7, B=6, C=5, U=0
--    SGPA = Σ(grade_points × credits) / total_credits_registered
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO coe_sgpa_summary
    (student_id, exam_group_class_batch_exam_id, class_id,
     total_credits_earned, total_credits_registered,
     sgpa, cgpa, arrear_count, result_status,
     computed_at, is_published, published_at, published_by)
VALUES
-- AAKASH K: (0 + 8*4 + 9*4)/12 = 68/12 ≈ 5.67  1 arrear
(2, 1, NULL,  8.00, 12.00,  5.67, 5.67, 1, 'fail', '2025-12-10 10:00:00', 1, '2025-12-10 10:00:00', 1),
-- AJAI V:   (0 + 0 + 8*4)/12 = 32/12 ≈ 2.67  2 arrears
(3, 1, NULL,  4.00, 12.00,  2.67, 2.67, 2, 'fail', '2025-12-10 10:00:00', 1, '2025-12-10 10:00:00', 1),
-- AKILAN K: (10*4 + 9*4 + 9*4)/12 = 112/12 ≈ 9.33  0 arrears
(4, 1, NULL, 12.00, 12.00,  9.33, 9.33, 0, 'pass', '2025-12-10 10:00:00', 1, '2025-12-10 10:00:00', 1),
-- AKSHAYA N:(0 + 0 + 0)/12 = 0  3 arrears
(5, 1, NULL,  0.00, 12.00,  0.00, 0.00, 3, 'fail', '2025-12-10 10:00:00', 1, '2025-12-10 10:00:00', 1);

-- ─────────────────────────────────────────────────────────────────────────────
-- 4. Arrear Applications — Nov/Dec 2025 Arrear event (batch_exam_id=4, eg_id=3)
--    Only failed subjects from batch_exam_id=1 are eligible
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO coe_exam_applications
    (exam_group_id, exam_group_class_batch_exam_id, student_id, student_session_id,
     subject_id, is_arrear, cbcs_category, application_status, attendance_pct, applied_at, processed_at, processed_by)
VALUES
-- AAKASH K: CS3401 arrear
(3, 4, 2, 2, 4, 1, 'core', 'eligible', 76.50, '2025-11-10 09:00:00', '2025-11-10 09:00:00', 1),
-- AJAI V: CS3401 and CS3451 arrears
(3, 4, 3, 3, 4, 1, 'core', 'eligible', 81.00, '2025-11-10 09:00:00', '2025-11-10 09:00:00', 1),
(3, 4, 3, 3, 5, 1, 'core', 'eligible', 81.00, '2025-11-10 09:00:00', '2025-11-10 09:00:00', 1),
-- AKSHAYA N: CS3401, CS3451, GE3451 arrears
(3, 4, 5, 5, 4, 1, 'core', 'eligible', 72.00, '2025-11-10 09:00:00', '2025-11-10 09:00:00', 1),
(3, 4, 5, 5, 5, 1, 'core', 'eligible', 72.00, '2025-11-10 09:00:00', '2025-11-10 09:00:00', 1),
(3, 4, 5, 5, 6, 1, 'core', 'eligible', 72.00, '2025-11-10 09:00:00', '2025-11-10 09:00:00', 1);

-- ─────────────────────────────────────────────────────────────────────────────
-- 5. Arrear Exam Results — Nov/Dec 2025 Arrear (batch_exam_id=4, date_from=2025-11-20)
--    Scenario:
--      AAKASH K  → CS3401 CLEARED (pass)
--      AJAI V    → CS3401 still FAIL, CS3451 CLEARED (pass)
--      AKSHAYA N → CS3401 still FAIL, CS3451 CLEARED (pass), GE3451 still FAIL
-- ─────────────────────────────────────────────────────────────────────────────
INSERT INTO coe_student_results
    (student_id, exam_group_class_batch_exam_id, subject_id,
     internal_marks, external_marks, total_marks, max_internal, max_external, max_total,
     credits, grade, grade_points, result_status, is_arrear, moderation_applied, is_published, published_at)
VALUES

-- ── AAKASH K: CS3401 CLEARED ──
(2, 4, 4,  20.00, 38.00, 58.00, 30.00, 70.00, 100.00, 4, 'B', 6.00, 'pass', 1, 0.00, 1, '2025-12-05 10:00:00'),

-- ── AJAI V: CS3401 still fail, CS3451 cleared ──
(3, 4, 4,  15.00, 24.00, 39.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 1, 0.00, 1, '2025-12-05 10:00:00'),
(3, 4, 5,  16.00, 36.00, 52.00, 30.00, 70.00, 100.00, 4, 'B', 6.00, 'pass', 1, 0.00, 1, '2025-12-05 10:00:00'),

-- ── AKSHAYA N: CS3401 still fail, CS3451 cleared, GE3451 still fail ──
(5, 4, 4,  14.00, 22.00, 36.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 1, 0.00, 1, '2025-12-05 10:00:00'),
(5, 4, 5,  15.00, 38.00, 53.00, 30.00, 70.00, 100.00, 4, 'B', 6.00, 'pass', 1, 0.00, 1, '2025-12-05 10:00:00'),
(5, 4, 6,  18.00, 26.00, 44.00, 30.00, 70.00, 100.00, 4, 'U', 0.00, 'fail', 1, 0.00, 1, '2025-12-05 10:00:00');

-- =============================================================================
-- Summary of active arrears after all inserts:
--
--   AAKASH K (student 2):
--     CS3401 failed batch_1 (2025-11-15), PASSED batch_4 (2025-11-20) → CLEARED
--     → active_only = 0 active arrears
--
--   AJAI V (student 3):
--     CS3401 failed batch_1, failed batch_4 again → ACTIVE
--     CS3451 failed batch_1, PASSED batch_4       → CLEARED
--     → active_only = 1 active arrear (CS3401)
--
--   AKILAN K (student 4):
--     All pass → no arrears at all
--
--   AKSHAYA N (student 5):
--     CS3401 failed batch_1, failed batch_4        → ACTIVE
--     CS3451 failed batch_1, PASSED batch_4        → CLEARED
--     GE3451 failed batch_1, failed batch_4        → ACTIVE
--     (also CS3452 & CS3492 failed in batch_3 from existing data → ACTIVE)
--     → active_only = 4 active arrears
-- =============================================================================
