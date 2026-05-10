-- =============================================================
-- Phase 3 Demo Data — batch_exam_id=3 (Apr/May 2026 - CSE)
-- Students 1-6, Hall Tickets 8-13, Subjects 1-3
-- =============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- 1. SUBJECT CONFIG
-- credits=4, max_internal=30, max_external=70, pass_internal=12, pass_external=28, pass_total=50
INSERT INTO coe_subject_config
  (exam_group_class_batch_exam_id, subject_id, credits, max_internal, max_external, pass_internal, pass_external, pass_total, scheme)
VALUES
  (3, 1, 4, 30.00, 70.00, 12.00, 28.00, 50.00, 'NEP2020'),
  (3, 2, 4, 30.00, 70.00, 12.00, 28.00, 50.00, 'NEP2020'),
  (3, 3, 4, 30.00, 70.00, 12.00, 28.00, 50.00, 'NEP2020');

-- 2. ANSWER SCRIPTS
-- Subject 1 (CS3452 ToC) — IDs will be 1-6
-- Subject 2 (CS3491 AIML) — IDs will be 7-12
-- Subject 3 (CS3492 DBMS) — IDs will be 13-18
-- hall_ticket_ids: 8-13 → student_ids: 1-6
INSERT INTO coe_answer_scripts
  (exam_group_class_batch_exam_id, coe_hall_ticket_id, subject_id, exam_date, session_slot, barcode_token, scan_status, page_count, uploaded_by, uploaded_at)
VALUES
  -- Subject 1 (ToC) — exam date 2026-04-20 FN
  (3, 8,  1, '2026-04-20', 'FN', 'BAR-3-1-001', 'uploaded', 24, 1, '2026-04-21 09:00:00'),
  (3, 9,  1, '2026-04-20', 'FN', 'BAR-3-1-002', 'uploaded', 24, 1, '2026-04-21 09:00:00'),
  (3, 10, 1, '2026-04-20', 'FN', 'BAR-3-1-003', 'uploaded', 24, 1, '2026-04-21 09:00:00'),
  (3, 11, 1, '2026-04-20', 'FN', 'BAR-3-1-004', 'uploaded', 24, 1, '2026-04-21 09:00:00'),
  (3, 12, 1, '2026-04-20', 'FN', 'BAR-3-1-005', 'uploaded', 24, 1, '2026-04-21 09:00:00'),
  (3, 13, 1, '2026-04-20', 'FN', 'BAR-3-1-006', 'uploaded', 24, 1, '2026-04-21 09:00:00'),
  -- Subject 2 (AIML) — exam date 2026-04-22 AN
  (3, 8,  2, '2026-04-22', 'AN', 'BAR-3-2-001', 'uploaded', 24, 1, '2026-04-23 09:00:00'),
  (3, 9,  2, '2026-04-22', 'AN', 'BAR-3-2-002', 'uploaded', 24, 1, '2026-04-23 09:00:00'),
  (3, 10, 2, '2026-04-22', 'AN', 'BAR-3-2-003', 'uploaded', 24, 1, '2026-04-23 09:00:00'),
  (3, 11, 2, '2026-04-22', 'AN', 'BAR-3-2-004', 'uploaded', 24, 1, '2026-04-23 09:00:00'),
  (3, 12, 2, '2026-04-22', 'AN', 'BAR-3-2-005', 'uploaded', 24, 1, '2026-04-23 09:00:00'),
  (3, 13, 2, '2026-04-22', 'AN', 'BAR-3-2-006', 'uploaded', 24, 1, '2026-04-23 09:00:00'),
  -- Subject 3 (DBMS) — exam date 2026-04-24 FN
  (3, 8,  3, '2026-04-24', 'FN', 'BAR-3-3-001', 'uploaded', 24, 1, '2026-04-25 09:00:00'),
  (3, 9,  3, '2026-04-24', 'FN', 'BAR-3-3-002', 'uploaded', 24, 1, '2026-04-25 09:00:00'),
  (3, 10, 3, '2026-04-24', 'FN', 'BAR-3-3-003', 'uploaded', 24, 1, '2026-04-25 09:00:00'),
  (3, 11, 3, '2026-04-24', 'FN', 'BAR-3-3-004', 'uploaded', 24, 1, '2026-04-25 09:00:00'),
  (3, 12, 3, '2026-04-24', 'FN', 'BAR-3-3-005', 'uploaded', 24, 1, '2026-04-25 09:00:00'),
  (3, 13, 3, '2026-04-24', 'FN', 'BAR-3-3-006', 'uploaded', 24, 1, '2026-04-25 09:00:00');

-- 3. OSM SCRIPTS (for Subject 1 only — answer_script_ids 1-6)
-- Staff: 217 = S.KARUPPASWAMY, 218 = ARUN B
INSERT INTO coe_osm_scripts
  (answer_script_id, assigned_evaluator, stage, total_marks, status, assigned_at, submitted_at, locked_by, locked_at)
VALUES
  -- Script 1 (student 1, BAR-3-1-001): locked, 65 marks
  (1, 217, 1, 65.00, 'locked',  '2026-04-22 10:00:00', '2026-04-23 14:00:00', 1, '2026-04-24 09:00:00'),
  -- Script 2 (student 2, BAR-3-1-002): done, 58 marks
  (2, 217, 1, 58.00, 'done',    '2026-04-22 10:00:00', '2026-04-23 16:00:00', NULL, NULL),
  -- Script 3 (student 3, BAR-3-1-003): marking in progress
  (3, 218, 1, NULL,  'marking', '2026-04-22 10:30:00', NULL, NULL, NULL),
  -- Script 4 (student 4, BAR-3-1-004): assigned
  (4, 218, 1, NULL,  'assigned','2026-04-22 10:30:00', NULL, NULL, NULL),
  -- Script 5 (student 5, BAR-3-1-005): pending
  (5, NULL, 1, NULL, 'pending', NULL, NULL, NULL, NULL),
  -- Script 6 (student 6, BAR-3-1-006): locked, 60 marks
  (6, 217, 1, 60.00, 'locked',  '2026-04-22 10:00:00', '2026-04-23 15:00:00', 1, '2026-04-24 09:00:00');

-- 4. OSM MARKS (for scripts 1, 2, 6 — locked/done)
-- Part A: Q1-10, max 2 each; Part B: Q11-15 either 'a' or 'b', max 16 each
-- Script 1 (id=1): target total=65 → Part A=17, Part B=48
INSERT INTO coe_osm_marks (osm_script_id, question_no, sub_question, max_marks, marks_awarded, awarded_by, awarded_at) VALUES
  (1,  1, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1,  2, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1,  3, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1,  4, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1,  5, NULL,  2.00, 1.00, 217, '2026-04-23 10:00:00'),
  (1,  6, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1,  7, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1,  8, NULL,  2.00, 0.00, 217, '2026-04-23 10:00:00'),
  (1,  9, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1, 10, NULL,  2.00, 2.00, 217, '2026-04-23 10:00:00'),
  (1, 11, 'a', 16.00, 10.00, 217, '2026-04-23 10:30:00'),
  (1, 12, 'a', 16.00, 12.00, 217, '2026-04-23 10:30:00'),
  (1, 13, 'b', 16.00, 14.00, 217, '2026-04-23 10:30:00'),
  (1, 14, 'b', 16.00,  8.00, 217, '2026-04-23 10:30:00'),
  (1, 15, 'a', 16.00,  4.00, 217, '2026-04-23 10:30:00');
-- Script 2 (id=2): target total=58 → Part A=14, Part B=44
INSERT INTO coe_osm_marks (osm_script_id, question_no, sub_question, max_marks, marks_awarded, awarded_by, awarded_at) VALUES
  (2,  1, NULL,  2.00, 2.00, 217, '2026-04-23 11:00:00'),
  (2,  2, NULL,  2.00, 1.00, 217, '2026-04-23 11:00:00'),
  (2,  3, NULL,  2.00, 2.00, 217, '2026-04-23 11:00:00'),
  (2,  4, NULL,  2.00, 2.00, 217, '2026-04-23 11:00:00'),
  (2,  5, NULL,  2.00, 0.00, 217, '2026-04-23 11:00:00'),
  (2,  6, NULL,  2.00, 2.00, 217, '2026-04-23 11:00:00'),
  (2,  7, NULL,  2.00, 2.00, 217, '2026-04-23 11:00:00'),
  (2,  8, NULL,  2.00, 1.00, 217, '2026-04-23 11:00:00'),
  (2,  9, NULL,  2.00, 1.00, 217, '2026-04-23 11:00:00'),
  (2, 10, NULL,  2.00, 1.00, 217, '2026-04-23 11:00:00'),
  (2, 11, 'b', 16.00, 12.00, 217, '2026-04-23 11:30:00'),
  (2, 12, 'a', 16.00,  8.00, 217, '2026-04-23 11:30:00'),
  (2, 13, 'b', 16.00, 10.00, 217, '2026-04-23 11:30:00'),
  (2, 14, 'a', 16.00, 10.00, 217, '2026-04-23 11:30:00'),
  (2, 15, 'b', 16.00,  4.00, 217, '2026-04-23 11:30:00');
-- Script 6 (id=6): target total=60 → Part A=16, Part B=44
INSERT INTO coe_osm_marks (osm_script_id, question_no, sub_question, max_marks, marks_awarded, awarded_by, awarded_at) VALUES
  (6,  1, NULL,  2.00, 2.00, 217, '2026-04-23 13:00:00'),
  (6,  2, NULL,  2.00, 2.00, 217, '2026-04-23 13:00:00'),
  (6,  3, NULL,  2.00, 2.00, 217, '2026-04-23 13:00:00'),
  (6,  4, NULL,  2.00, 1.00, 217, '2026-04-23 13:00:00'),
  (6,  5, NULL,  2.00, 2.00, 217, '2026-04-23 13:00:00'),
  (6,  6, NULL,  2.00, 2.00, 217, '2026-04-23 13:00:00'),
  (6,  7, NULL,  2.00, 1.00, 217, '2026-04-23 13:00:00'),
  (6,  8, NULL,  2.00, 2.00, 217, '2026-04-23 13:00:00'),
  (6,  9, NULL,  2.00, 1.00, 217, '2026-04-23 13:00:00'),
  (6, 10, NULL,  2.00, 1.00, 217, '2026-04-23 13:00:00'),
  (6, 11, 'a', 16.00, 10.00, 217, '2026-04-23 13:30:00'),
  (6, 12, 'b', 16.00, 12.00, 217, '2026-04-23 13:30:00'),
  (6, 13, 'a', 16.00, 12.00, 217, '2026-04-23 13:30:00'),
  (6, 14, 'b', 16.00,  8.00, 217, '2026-04-23 13:30:00'),
  (6, 15, 'a', 16.00,  2.00, 217, '2026-04-23 13:30:00');

-- 5. STUDENT RESULTS
-- Grades: O>=91(10), A+>=81(9), A>=71(8), B+>=61(7), B>=51(6), C=50(5), U<50(0)
-- Fail if: external < 28 OR total < 50
-- Subj 1 (CS3452 ToC), Subj 2 (CS3491 AIML), Subj 3 (CS3492 DBMS)
INSERT INTO coe_student_results
  (student_id, exam_group_class_batch_exam_id, subject_id, internal_marks, external_marks, total_marks,
   max_internal, max_external, max_total, credits, grade, grade_points, result_status, is_arrear, moderation_applied)
VALUES
  -- Student 1: A+(90), O(99), O(94) — all pass
  (1, 3, 1, 25.00, 65.00, 90.00,  30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00),
  (1, 3, 2, 27.00, 72.00, 99.00,  30.00, 70.00, 100.00, 4, 'O',  10.00,'pass', 0, 0.00),
  (1, 3, 3, 26.00, 68.00, 94.00,  30.00, 70.00, 100.00, 4, 'O',  10.00,'pass', 0, 0.00),
  -- Student 2: A(80), A+(87), A+(83) — all pass
  (2, 3, 1, 22.00, 58.00, 80.00,  30.00, 70.00, 100.00, 4, 'A',  8.00, 'pass', 0, 0.00),
  (2, 3, 2, 24.00, 63.00, 87.00,  30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00),
  (2, 3, 3, 23.00, 60.00, 83.00,  30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00),
  -- Student 3: A(73), A(76), A(71) — all pass
  (3, 3, 1, 21.00, 52.00, 73.00,  30.00, 70.00, 100.00, 4, 'A',  8.00, 'pass', 0, 0.00),
  (3, 3, 2, 21.00, 55.00, 76.00,  30.00, 70.00, 100.00, 4, 'A',  8.00, 'pass', 0, 0.00),
  (3, 3, 3, 18.00, 53.00, 71.00,  30.00, 70.00, 100.00, 4, 'A',  8.00, 'pass', 0, 0.00),
  -- Student 4: B(58), B+(68), B(51) — all pass
  (4, 3, 1, 18.00, 40.00, 58.00,  30.00, 70.00, 100.00, 4, 'B',  6.00, 'pass', 0, 0.00),
  (4, 3, 2, 20.00, 48.00, 68.00,  30.00, 70.00, 100.00, 4, 'B+', 7.00, 'pass', 0, 0.00),
  (4, 3, 3, 15.00, 36.00, 51.00,  30.00, 70.00, 100.00, 4, 'B',  6.00, 'pass', 0, 0.00),
  -- Student 5: U(45 ext=30<28? 30>=28 so external ok, but total=45<50 → U), A(77), U(39, ext=25<28 → fail)
  -- Subject1: Int=15, Ext=30, Total=45 (<50) → U
  -- Subject2: Int=22, Ext=55, Total=77 → A (pass)
  -- Subject3: Int=14, Ext=25, Total=39 (ext=25<28 AND total<50) → U
  (5, 3, 1, 15.00, 30.00, 45.00,  30.00, 70.00, 100.00, 4, 'U',  0.00, 'fail', 1, 0.00),
  (5, 3, 2, 22.00, 55.00, 77.00,  30.00, 70.00, 100.00, 4, 'A',  8.00, 'pass', 0, 0.00),
  (5, 3, 3, 14.00, 25.00, 39.00,  30.00, 70.00, 100.00, 4, 'U',  0.00, 'fail', 1, 0.00),
  -- Student 6: A+(84), O(96), A+(87) — all pass
  (6, 3, 1, 24.00, 60.00, 84.00,  30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00),
  (6, 3, 2, 26.00, 70.00, 96.00,  30.00, 70.00, 100.00, 4, 'O',  10.00,'pass', 0, 0.00),
  (6, 3, 3, 25.00, 62.00, 87.00,  30.00, 70.00, 100.00, 4, 'A+', 9.00, 'pass', 0, 0.00);

-- 6. SGPA SUMMARY
-- SGPA = Σ(GP × credits) / Σ(credits), total_credits_registered=12 (3 subj × 4 credits)
-- Student 1: (9+10+10)×4/12 = 116/12 = 9.67
-- Student 2: (8+9+9)×4/12 = 104/12 = 8.67
-- Student 3: (8+8+8)×4/12 = 96/12 = 8.00
-- Student 4: (6+7+6)×4/12 = 76/12 = 6.33
-- Student 5: (0+8+0)×4/12 = 32/12 = 2.67, earned=4 (only subj2), arrear_count=2
-- Student 6: (9+10+9)×4/12 = 112/12 = 9.33
INSERT INTO coe_sgpa_summary
  (student_id, exam_group_class_batch_exam_id, total_credits_earned, total_credits_registered, sgpa, cgpa, arrear_count, result_status, computed_at)
VALUES
  (1, 3, 12.00, 12.00, 9.67, 9.67, 0, 'pass', '2026-05-10 10:00:00'),
  (2, 3, 12.00, 12.00, 8.67, 8.67, 0, 'pass', '2026-05-10 10:00:00'),
  (3, 3, 12.00, 12.00, 8.00, 8.00, 0, 'pass', '2026-05-10 10:00:00'),
  (4, 3, 12.00, 12.00, 6.33, 6.33, 0, 'pass', '2026-05-10 10:00:00'),
  (5, 3,  4.00, 12.00, 2.67, 2.67, 2, 'fail', '2026-05-10 10:00:00'),
  (6, 3, 12.00, 12.00, 9.33, 9.33, 0, 'pass', '2026-05-10 10:00:00');

-- 7. MODERATION RULE
-- Grace: +3 flat on external marks for Subject 3 (DBMS), not yet applied
INSERT INTO coe_moderation_rules
  (exam_group_class_batch_exam_id, subject_id, rule_type, value_type, value, max_cap, applies_to, is_applied, reason)
VALUES
  (3, 3, 'grace', 'flat', 3.00, 70.00, 'external', 0, 'Grace marks for DBMS paper difficulty');

-- 8. REVALUATION REQUEST
-- Student 5 failed Subject 1 (ToC), wants revaluation, paid ₹500
INSERT INTO coe_revaluation_requests
  (student_id, exam_group_class_batch_exam_id, subject_id, original_marks, request_date,
   payment_status, payment_ref, payment_amount, payment_date, stage, status, created_by)
VALUES
  (5, 3, 1, 45.00, '2026-05-01', 'paid', 'PAY/2026/0501/001', 500.00, '2026-05-02', 1, 'assigned', 1);

SET foreign_key_checks = 1;
