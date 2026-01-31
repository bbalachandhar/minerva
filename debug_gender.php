<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

echo "All Gender Values (including NULL/empty):\n";
echo "=========================================\n";
$sql = "SELECT gender, COUNT(*) as count FROM students WHERE is_active = 'yes' AND disable_at IS NULL GROUP BY gender WITH ROLLUP";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    $gender = $row["gender"] === null ? "NULL" : "'" . $row["gender"] . "'";
    if ($row["count"] !== null) {
        echo "Gender: " . $gender . " | Count: " . $row["count"] . "\n";
    }
}

echo "\n\nChecking for Unknown and Feemale:\n";
echo "=================================\n";
$sql2 = "SELECT gender, COUNT(*) as count FROM students WHERE gender IN ('Unknown', 'Feemale', 'unknown', 'feemale') AND is_active = 'yes' AND disable_at IS NULL GROUP BY gender";
$result2 = $conn->query($sql2);

if ($result2->num_rows == 0) {
    echo "No students found with 'Unknown' or 'Feemale' gender\n";
} else {
    while($row = $result2->fetch_assoc()) {
        echo "Gender: '" . $row["gender"] . "' | Count: " . $row["count"] . "\n";
    }
}

$conn->close();
?>
