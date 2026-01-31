<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

$current_session = 21;

echo "Students with multiple enrollments in session 21:\n";
echo "================================================\n";

$sql = "SELECT s.id, CONCAT(s.firstname, ' ', s.lastname) as name, COUNT(*) as enrollment_count
        FROM students s
        JOIN student_session ss ON ss.student_id = s.id
        WHERE ss.session_id = " . $current_session . "
        AND s.is_active = 'yes'
        AND s.disable_at IS NULL
        GROUP BY s.id
        HAVING COUNT(*) > 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " student(s) with multiple enrollments:\n\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . " | Name: " . $row["name"] . " | Enrollments: " . $row["enrollment_count"] . "\n";
        
        // Get details of all enrollments for this student
        $detail_sql = "SELECT ss.id, c.class, s.section FROM student_session ss
                      JOIN classes c ON c.id = ss.class_id
                      JOIN sections s ON s.id = ss.section_id
                      WHERE ss.student_id = " . $row["id"] . " AND ss.session_id = " . $current_session;
        $detail_result = $conn->query($detail_sql);
        while($detail = $detail_result->fetch_assoc()) {
            echo "  -> Class: " . $detail["class"] . ", Section: " . $detail["section"] . "\n";
        }
    }
} else {
    echo "No students with multiple enrollments found.\n";
}

$conn->close();
?>
