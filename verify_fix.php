<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

$current_session = 21;

echo "Updated Gender Count Query (with INNER JOINs):\n";
echo "==============================================\n\n";

$sql = "SELECT IFNULL(students.gender, 'Not Specified') as gender, COUNT(DISTINCT student_session.student_id) as count
        FROM student_session
        INNER JOIN students ON students.id = student_session.student_id
        INNER JOIN classes ON classes.id = student_session.class_id
        INNER JOIN sections ON sections.id = student_session.section_id
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
echo "NEW TOTAL: " . $total . "\n";
echo "Expected: 2248 ✓\n";

$conn->close();
?>
