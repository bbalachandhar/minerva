<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

echo "Current Gender Distribution:\n";
echo "===========================\n";
$sql = "SELECT gender, COUNT(*) as count FROM students WHERE is_active = 'yes' AND disable_at IS NULL GROUP BY gender ORDER BY count DESC";
$result = $conn->query($sql);

$total = 0;
while($row = $result->fetch_assoc()) {
    $gender = $row["gender"] === null || $row["gender"] === '' ? "NULL/EMPTY" : $row["gender"];
    echo "Gender: " . $gender . " | Count: " . $row["count"] . "\n";
    $total += $row["count"];
}

echo "\n=========================\n";
echo "TOTAL: " . $total . "\n";

$conn->close();
?>
