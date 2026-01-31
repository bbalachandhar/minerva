<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for distinct gender values
$sql = "SELECT DISTINCT gender, COUNT(*) as count FROM students GROUP BY gender ORDER BY count DESC";
$result = $conn->query($sql);

echo "Gender Distribution:\n";
echo "====================\n";
while($row = $result->fetch_assoc()) {
    $gender = $row["gender"] === null ? "NULL" : "'" . $row["gender"] . "'";
    echo "Gender: " . $gender . " | Count: " . $row["count"] . "\n";
}

echo "\n\nStudents with NULL or Empty Gender:\n";
echo "====================================\n";

// Now get the actual students with NULL/empty gender
$sql2 = "SELECT id, firstname, lastname, gender FROM students WHERE gender IS NULL OR gender = '' ORDER BY id";
$result2 = $conn->query($sql2);

if ($result2->num_rows > 0) {
    echo "Found " . $result2->num_rows . " students:\n\n";
    while($row = $result2->fetch_assoc()) {
        echo "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . " | Gender: " . ($row["gender"] === null ? "NULL" : "EMPTY") . "\n";
    }
} else {
    echo "No students found with NULL or empty gender.";
}

$conn->close();
?>
