<?php
// Verify today's attendance has been processed correctly
$sock = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
$conn = new mysqli('localhost', 'root', '', 'mcekknagar', 3306, $sock);

$today = date('Y-m-d');

echo "=== Today's Attendance Summary ($today) ===\n\n";

// Total punches
$result1 = $conn->query("SELECT COUNT(*) as total FROM staff_biometric_punches WHERE DATE(punch_time) = '$today'");
$row1 = $result1->fetch_assoc();
echo "Total Punches Recorded: " . $row1['total'] . "\n";

// Total active staff
$result2 = $conn->query("SELECT COUNT(*) as total FROM staff WHERE is_active = 1");
$row2 = $result2->fetch_assoc();
echo "Total Active Staff: " . $row2['total'] . "\n\n";

// Attendance summary
$result3 = $conn->query("SELECT 
  COUNT(*) as total_attendance,
  SUM(CASE WHEN staff_attendance_type_id = 1 THEN 1 ELSE 0 END) as present,
  SUM(CASE WHEN staff_attendance_type_id = 2 THEN 1 ELSE 0 END) as late,
  SUM(CASE WHEN staff_attendance_type_id = 3 THEN 1 ELSE 0 END) as absent,
  SUM(CASE WHEN staff_attendance_type_id = 4 THEN 1 ELSE 0 END) as half_day,
  SUM(CASE WHEN staff_attendance_type_id = 5 THEN 1 ELSE 0 END) as holiday,
  SUM(CASE WHEN staff_attendance_type_id = 6 THEN 1 ELSE 0 END) as shl,
  SUM(CASE WHEN staff_attendance_type_id = 7 THEN 1 ELSE 0 END) as shp,
  SUM(CASE WHEN staff_attendance_type_id = 8 THEN 1 ELSE 0 END) as first_half_absent,
  SUM(CASE WHEN staff_attendance_type_id = 9 THEN 1 ELSE 0 END) as second_half_absent
FROM staff_attendance WHERE date = '$today'");

$row3 = $result3->fetch_assoc();

echo "=== Attendance Distribution ($today) ===\n";
echo "Total Attendance Records: " . ($row3['total_attendance'] ?? 0) . "\n";
echo "  Present (1): " . ($row3['present'] ?? 0) . "\n";
echo "  Late (2): " . ($row3['late'] ?? 0) . "\n";
echo "  Absent (3): " . ($row3['absent'] ?? 0) . "\n";
echo "  Half Day (4): " . ($row3['half_day'] ?? 0) . "\n";
echo "  Holiday (5): " . ($row3['holiday'] ?? 0) . "\n";
echo "  SHL (6): " . ($row3['shl'] ?? 0) . "\n";
echo "  SHP (7): " . ($row3['shp'] ?? 0) . "\n";
echo "  1st Half Absent (8): " . ($row3['first_half_absent'] ?? 0) . "\n";
echo "  2nd Half Absent (9): " . ($row3['second_half_absent'] ?? 0) . "\n\n";

// Sample records with times
echo "=== Sample Attendance Records with Times ===\n";
$result4 = $conn->query("SELECT s.id, s.name, sa.staff_attendance_type_id, sa.in_time, sa.out_time, sa.remark 
                        FROM staff_attendance sa 
                        JOIN staff s ON s.id = sa.staff_id 
                        WHERE sa.date = '$today' AND sa.in_time IS NOT NULL 
                        ORDER BY sa.staff_id 
                        LIMIT 10");

$types = [1=>'Present', 2=>'Late', 3=>'Absent', 4=>'Half Day', 5=>'Holiday', 6=>'SHL', 7=>'SHP', 8=>'1stHalfAbs', 9=>'2ndHalfAbs'];

while ($row = $result4->fetch_assoc()) {
    $type_name = $types[$row['staff_attendance_type_id']] ?? 'Unknown';
    echo "Staff " . $row['id'] . " (" . substr($row['name'], 0, 20) . "): $type_name | In: " . ($row['in_time'] ?? 'NULL') . " Out: " . ($row['out_time'] ?? 'NULL') . "\n";
}

// Check for staff with no role
echo "\n=== Staff with No Role (using default Admin config) ===\n";
$result5 = $conn->query("SELECT s.id, s.name, COUNT(sr.role_id) as role_count 
                        FROM staff s 
                        LEFT JOIN staff_roles sr ON sr.staff_id = s.id 
                        WHERE s.is_active = 1 
                        GROUP BY s.id 
                        HAVING role_count = 0 
                        LIMIT 5");

echo "Records found: " . $result5->num_rows . "\n";
while ($row = $result5->fetch_assoc()) {
    echo "Staff " . $row['id'] . ": " . $row['name'] . "\n";
}

$conn->close();
?>
