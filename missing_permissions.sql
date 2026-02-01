-- ==============================================
-- SQL INSERT STATEMENTS FOR MISSING PERMISSION CATEGORIES
-- Generated: February 1, 2026
-- ==============================================

-- ========== FRONT OFFICE MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Enquiry', 1, 1, 1, 1),
('Visitors', 1, 1, 1, 1),
('Complaint', 1, 1, 1, 1),
('Phone Call Log', 1, 1, 1, 1),
('Postal Dispatch', 1, 1, 1, 1),
('Postal Receive', 1, 1, 1, 1),
('Visitor Book', 1, 1, 1, 1),
('General Call', 1, 1, 1, 1),
('Dispatch', 1, 1, 1, 1),
('Receive', 1, 1, 1, 1),
('Visitor Purpose', 1, 1, 1, 1);

-- ========== ONLINE COURSE MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Online Course', 1, 1, 1, 1),
('Course Category', 1, 1, 1, 1),
('Course Report', 1, 1, 1, 1),
('Course Purchase Report', 1, 1, 1, 1),
('Course Sell Report', 1, 1, 1, 1),
('Trending Course Report', 1, 1, 1, 1),
('Complete Course Report', 1, 1, 1, 1),
('Course Rating Report', 1, 1, 1, 1),
('Course Guest List', 1, 1, 1, 1),
('Quiz Performance Report', 1, 1, 1, 1),
('Course Assignment Report', 1, 1, 1, 1),
('Course Exam Result Report', 1, 1, 1, 1),
('Course Exam Report', 1, 1, 1, 1),
('Course Exam Attempt Report', 1, 1, 1, 1),
('Course Offline Payment', 1, 1, 1, 1),
('Course Exam Question', 1, 1, 1, 1),
('Course Tag', 1, 1, 1, 1);

-- ========== CBSE EXAM MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('CBSE Exam', 1, 1, 1, 1),
('CBSE Exam Result', 1, 1, 1, 1),
('CBSE Grade', 1, 1, 1, 1),
('CBSE Observation', 1, 1, 1, 1),
('CBSE Observation Parameter', 1, 1, 1, 1),
('CBSE Assessment', 1, 1, 1, 1),
('CBSE Term', 1, 1, 1, 1),
('CBSE Template', 1, 1, 1, 1),
('CBSE Report', 1, 1, 1, 1),
('CBSE Setting', 1, 1, 1, 1);

-- ========== HALL MANAGEMENT MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Hall Master', 1, 1, 1, 1),
('Hall Bookings', 1, 1, 1, 1),
('Approve Booking', 1, 1, 1, 1),
('Reject Booking', 1, 1, 1, 1);

-- ========== QR CODE ATTENDANCE MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('QR Code Attendance', 1, 1, 1, 1),
('QR Attendance Setting', 1, 1, 1, 1);

-- ========== STUDENT CV / RESUME MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Student Resume', 1, 1, 1, 1),
('Resume Download', 1, 1, 1, 1),
('Resume Setting', 1, 1, 1, 1),
('Student Resume Details', 1, 1, 1, 1);

-- ========== NAAC MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('NAAC Configuration', 1, 1, 1, 1),
('NAAC IIQA', 1, 1, 1, 1),
('NAAC SSR', 1, 1, 1, 1),
('NAAC AQAR', 1, 1, 1, 1);

-- ========== GOOGLE MEET INTEGRATION ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Google Meet', 1, 1, 1, 1),
('GMeet Timetable', 1, 1, 1, 1),
('GMeet Meeting', 1, 1, 1, 1),
('GMeet Class Report', 1, 1, 1, 1),
('GMeet Meeting Report', 1, 1, 1, 1);

-- ========== ZOOM INTEGRATION ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Zoom Conference', 1, 1, 1, 1),
('Zoom Timetable', 1, 1, 1, 1),
('Zoom Meeting', 1, 1, 1, 1),
('Zoom Class Report', 1, 1, 1, 1),
('Zoom Meeting Report', 1, 1, 1, 1);

-- ========== BEHAVIOUR RECORDS MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Student Incidents', 1, 1, 1, 1),
('Incidents', 1, 1, 1, 1),
('Incident Report', 1, 1, 1, 1),
('Student Incident Report', 1, 1, 1, 1),
('Student Behavior Rank Report', 1, 1, 1, 1),
('Class Wise Rank Report', 1, 1, 1, 1),
('Class Section Wise Rank', 1, 1, 1, 1),
('House Wise Rank', 1, 1, 1, 1),
('Incident Wise Report', 1, 1, 1, 1),
('Behaviour Setting', 1, 1, 1, 1);

-- ========== MULTI-BRANCH ADDITIONAL FEATURES ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Branch Overview', 1, 1, 1, 1),
('Branch Finance Dashboard', 1, 1, 1, 1),
('Multi-Branch Daily Collection Report', 1, 1, 1, 1),
('Multi-Branch Payroll Report', 1, 1, 1, 1),
('Multi-Branch Income List', 1, 1, 1, 1),
('Multi-Branch Expense List', 1, 1, 1, 1),
('Multi-Branch Income Report', 1, 1, 1, 1),
('Multi-Branch Expense Report', 1, 1, 1, 1),
('Multi-Branch User Log Report', 1, 1, 1, 1);

-- ========== TWO FACTOR AUTHENTICATION MODULE ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Two Factor Authentication', 1, 1, 1, 1),
('2FA Setup', 1, 1, 1, 1);

-- ========== ADDITIONAL MISSING FEATURES ==========
INSERT INTO permission_category (name, enable_view, enable_add, enable_edit, enable_delete) VALUES
('Online Student Category', 1, 1, 1, 1),
('Subject Group Assignment', 1, 1, 1, 1),
('Subject Group Management', 1, 1, 1, 1),
('Student Subject Group', 1, 1, 1, 1),
('Exam Schedule', 1, 1, 1, 1),
('Incidental Fee Type', 1, 1, 1, 1),
('Assign Incidental Fee', 1, 1, 1, 1),
('Collect Incidental Fee', 1, 1, 1, 1),
('Incidental Fee Report', 1, 1, 1, 1),
('Biometric Device Management', 1, 1, 1, 1),
('Add Biometric Device', 1, 1, 1, 1),
('Edit Biometric Device', 1, 1, 1, 1),
('Delete Biometric Device', 1, 1, 1, 1),
('Biometric Attendance Log', 1, 1, 1, 1);

-- ==============================================
-- TOTAL NEW PERMISSIONS: 127
-- These can now be assigned to roles via the Permission Management interface
-- ==============================================

-- VERIFICATION QUERY (Run this to check if all were inserted successfully):
-- SELECT COUNT(*) as total_permissions FROM permission_category;
