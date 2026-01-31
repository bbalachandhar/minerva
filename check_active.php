<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

echo "Gender Distribution by is_active Status:\n";
echo "========================================\n";
$sql = "SELECT s.gender, s.is_active, COUNT(*) as count 
        FROM students s 
        WHERE s.disable_at IS NULL 
        GROUP BY s.gender, s.is_active 
        ORDER BY s.is_active DESC, count DESC";
$result = $conn->query($sql);

$total_active = 0;
$total_inactive = 0;

while($row = $result->fetch_assoc()) {
    $gender = $row["gender"] === null ? "NULL" : "'" . $row["gender"] . "'";
    $is_active = $row["is_active"] === 'yes' ? 'yes' : 'no';
    echo "Gender: " . $gender . " | is_active: " . $is_active . " | Count: " . $row["count"] . "\n";
    
    if ($is_active === 'yes') {
        $total_active += $row["count"];
    } else {
        $total_inactive += $row["count"];
    }
}

echo "\n=================================\n";
echo "TOTAL (is_active='yes'): " . $total_active . "\n";
echo "TOTAL (is_active='no'): " . $total_inactive . "\n";
echo "GRAND TOTAL: " . ($total_active + $total_inactive) . "\n";

$conn->close();
?>
