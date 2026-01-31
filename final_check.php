<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

// Get current session from settings
$result = $conn->query("SELECT id FROM academic_sessions ORDER BY id DESC LIMIT 1");
$session = $result->fetch_assoc();
$current_session = $session['id'];

echo "Using Session ID: " . $current_session . "\n\n";

echo "Gender Count by Session (matching dashboard query):\n";
echo "==================================================\n";

$sql = "SELECT IFNULL(students.gender, 'Not Specified') as gender, COUNT(DISTINCT students.id) as count
        FROM students
        JOIN student_session ON student_session.student_id = students.id
        WHERE student_session.session_id = " . $current_session . "
        AND students.is_active = 'yes'
        AND students.disable_at IS NULL
        GROUP BY IFNULL(students.gender, 'Not Specified')
        ORDER BY count DESC";

$result2 = $conn->query($sql);
$total = 0;

while($row = $result2->fetch_assoc()) {
    echo "Gender: '" . $row["gender"] . "' | Count: " . $row["count"] . "\n";
    $total += $row["count"];
}

echo "\n====================================\n";
echo "TOTAL: " . $total . "\n";

$conn->close();
?>
