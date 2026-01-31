<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

echo "Students with 'Unknown' Gender:\n";
echo "===============================\n";
$sql1 = "SELECT id, firstname, lastname, gender FROM students WHERE gender = 'Unknown' ORDER BY id";
$result1 = $conn->query($sql1);

while($row = $result1->fetch_assoc()) {
    echo "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . "\n";
}

echo "\n\nStudents with 'Feemale' (Misspelled):\n";
echo "====================================\n";
$sql2 = "SELECT id, firstname, lastname, gender FROM students WHERE gender = 'Feemale' ORDER BY id";
$result2 = $conn->query($sql2);

while($row = $result2->fetch_assoc()) {
    echo "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . "\n";
}

echo "\n\nNote: Your 'Not Specified' count includes these 4 records:\n";
echo "- 3 with gender = 'Unknown'\n";
echo "- 1 with gender = 'Feemale' (typo)\n";

$conn->close();
?>
