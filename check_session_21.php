<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

$current_session = 21;

echo "Gender Distribution for Session ID: " . $current_session . "\n";
echo "====================================\n\n";

$sql = "SELECT IFNULL(students.gender, 'Not Specified') as gender, COUNT(DISTINCT students.id) as count
        FROM students
        JOIN student_session ON student_session.student_id = students.id
        WHERE student_session.session_id = " . $current_session . "
        AND students.is_active = 'yes'
        AND students.disable_at IS NULL
        GROUP BY IFNULL(students.gender, 'Not Specified')
        ORDER BY count DESC";

$result = $conn->query($sql);
$total = 0;

while($row = $result->fetch_assoc()) {
    echo "Gender: '" . $row["gender"] . "' | Count: " . $row["count"] . "\n";
    $total += $row["count"];
}

echo "\n====================================\n";
echo "DASHBOARD TOTAL: " . $total . "\n";

// Also check raw count from student_session
echo "\n\nRaw student_session count (DISTINCT students):\n";
$sql2 = "SELECT COUNT(DISTINCT student_session.student_id) as count
         FROM student_session
         JOIN students ON students.id = student_session.student_id
         WHERE student_session.session_id = " . $current_session . "
         AND students.is_active = 'yes'
         AND students.disable_at IS NULL";

$result2 = $conn->query($sql2);
$row2 = $result2->fetch_assoc();
echo "Total: " . $row2['count'] . "\n";

$conn->close();
?>
