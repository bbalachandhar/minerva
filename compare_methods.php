<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

$current_session = 21;

echo "Method 1: getTotalHeadCountBySession() - with GROUP BY student_session.student_id\n";
echo "=================================================================================\n";

$sql1 = "SELECT count(*) as `total_student` FROM `student_session` 
         INNER JOIN students on students.id=student_session.student_id 
         INNER JOIN classes on classes.id=student_session.class_id 
         INNER JOIN sections on sections.id=student_session.section_id 
         WHERE student_session.session_id=" . $current_session . " 
         AND students.is_active = 'yes' 
         AND students.disable_at IS NULL 
         GROUP BY student_session.student_id";

$result1 = $conn->query($sql1);
$count1 = $result1->num_rows;
echo "Result rows (num_rows): " . $count1 . "\n";

echo "\n\nMethod 2: getTotalStudentBySession() - without GROUP BY\n";
echo "=========================================================\n";

$sql2 = "SELECT count(*) as `total_student` FROM `student_session` 
         INNER JOIN students on students.id=student_session.student_id 
         INNER JOIN classes on classes.id=student_session.class_id 
         INNER JOIN sections on sections.id=student_session.section_id 
         WHERE student_session.session_id=" . $current_session . " 
         AND students.is_active = 'yes' 
         AND students.disable_at IS NULL";

$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
echo "Result (total_student field): " . $row2['total_student'] . "\n";

echo "\n\nMethod 3: getStudentCountByGender() - DISTINCT students.id\n";
echo "===========================================================\n";

$sql3 = "SELECT IFNULL(students.gender, 'Not Specified') as gender, COUNT(DISTINCT students.id) as count
         FROM students
         JOIN student_session ON student_session.student_id = students.id
         WHERE student_session.session_id = " . $current_session . "
         AND students.is_active = 'yes'
         AND students.disable_at IS NULL
         GROUP BY IFNULL(students.gender, 'Not Specified')";

$result3 = $conn->query($sql3);
$total3 = 0;
while($row = $result3->fetch_assoc()) {
    $total3 += $row['count'];
}
echo "Sum of gender counts: " . $total3 . "\n";

$conn->close();
?>
