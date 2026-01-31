<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

echo "Looking for session table:\n";
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_row()) {
    if (strpos(strtolower($row[0]), 'session') !== false) {
        echo "Found: " . $row[0] . "\n";
    }
}

echo "\n\nFirst few records from student_session:\n";
$result2 = $conn->query("SELECT DISTINCT session_id FROM student_session LIMIT 5");
while($row = $result2->fetch_assoc()) {
    echo "Session ID: " . $row['session_id'] . "\n";
}

$conn->close();
?>
