<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mcekknagar";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT s.id, s.firstname, s.lastname, s.gender, s.is_active 
        FROM students s 
        WHERE (s.gender IS NULL OR s.gender = '') 
        AND s.is_active = 'yes' 
        AND s.disable_at IS NULL";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " students with no gender specified:\n\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . " | Gender: " . ($row["gender"] ?? "NULL") . "\n";
    }
} else {
    echo "No students found with missing gender.";
}

$conn->close();
?>
