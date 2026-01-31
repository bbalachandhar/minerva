<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

$current_session = 21;

echo "Checking for students in student_session that don't match getTotalHeadCountBySession:\n";
echo "==================================================================================\n\n";

echo "Method A: COUNT(DISTINCT students.id) from student_session\n";
$sql_a = "SELECT COUNT(DISTINCT ss.student_id) as count
          FROM student_session ss
          JOIN students s ON s.id = ss.student_id
          WHERE ss.session_id = " . $current_session . "
          AND s.is_active = 'yes'
          AND s.disable_at IS NULL";
$result_a = $conn->query($sql_a);
$row_a = $result_a->fetch_assoc();
echo "Result: " . $row_a['count'] . "\n\n";

echo "Method B: getTotalHeadCountBySession (GROUP BY student_session.student_id)\n";
$sql_b = "SELECT count(*) as count FROM (
          SELECT count(*) as total FROM `student_session` 
          INNER JOIN students on students.id=student_session.student_id 
          INNER JOIN classes on classes.id=student_session.class_id 
          INNER JOIN sections on sections.id=student_session.section_id 
          WHERE student_session.session_id=" . $current_session . " 
          AND students.is_active = 'yes' 
          AND students.disable_at IS NULL 
          GROUP BY student_session.student_id
         ) as subquery";
$result_b = $conn->query($sql_b);
$row_b = $result_b->fetch_assoc();
echo "Result: " . $row_b['count'] . "\n\n";

echo "Method C: SELECT * from getTotalHeadCountBySession query\n";
$sql_c = "SELECT count(*) FROM (
          SELECT * FROM `student_session` 
          INNER JOIN students on students.id=student_session.student_id 
          INNER JOIN classes on classes.id=student_session.class_id 
          INNER JOIN sections on sections.id=student_session.section_id 
          WHERE student_session.session_id=" . $current_session . " 
          AND students.is_active = 'yes' 
          AND students.disable_at IS NULL 
          GROUP BY student_session.student_id
         ) as subquery";
$result_c = $conn->query($sql_c);
$row_c = $result_c->fetch_assoc();
echo "Result: " . $row_c['count'] . "\n\n";

echo "Method D: Using MIN() function to pick one count per group\n";
$sql_d = "SELECT SUM(one_count) as total FROM (
          SELECT MIN(count(*)) as one_count FROM `student_session` 
          INNER JOIN students on students.id=student_session.student_id 
          INNER JOIN classes on classes.id=student_session.class_id 
          INNER JOIN sections on sections.id=student_session.section_id 
          WHERE student_session.session_id=" . $current_session . " 
          AND students.is_active = 'yes' 
          AND students.disable_at IS NULL 
          GROUP BY student_session.student_id
         ) as subquery";
$result_d = $conn->query($sql_d);
$row_d = $result_d->fetch_assoc();
echo "Result: " . ($row_d['total'] ?? 'NULL') . "\n";

$conn->close();
?>
