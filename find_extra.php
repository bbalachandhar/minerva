<?php
$conn = new mysqli('localhost', 'root', '', 'mcekknagar');

$current_session = 21;

echo "Finding the 1 extra DISTINCT student:\n";
echo "=====================================\n\n";

// Get students NOT in the getTotalHeadCountBySession result
$sql = "SELECT s.id, s.firstname, s.lastname, s.gender, s.is_active
        FROM students s
        JOIN student_session ss ON ss.student_id = s.id
        WHERE ss.session_id = " . $current_session . "
        AND s.is_active = 'yes'
        AND s.disable_at IS NULL
        AND s.id NOT IN (
            SELECT student_session.student_id FROM student_session 
            INNER JOIN students ON students.id=student_session.student_id 
            INNER JOIN classes ON classes.id=student_session.class_id 
            INNER JOIN sections ON sections.id=student_session.section_id 
            WHERE student_session.session_id=" . $current_session . " 
            AND students.is_active = 'yes' 
            AND students.disable_at IS NULL 
            GROUP BY student_session.student_id
        )";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Found " . $result->num_rows . " student(s) not in getTotalHeadCountBySession:\n\n";
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . " | Name: " . $row["firstname"] . " " . $row["lastname"] . " | Gender: " . $row["gender"] . "\n";
        
        // Check their enrollment details
        $detail_sql = "SELECT ss.id, c.id as class_id, c.class, s.id as section_id, s.section 
                      FROM student_session ss
                      LEFT JOIN classes c ON c.id = ss.class_id
                      LEFT JOIN sections s ON s.id = ss.section_id
                      WHERE ss.student_id = " . $row["id"] . " AND ss.session_id = " . $current_session;
        $detail_result = $conn->query($detail_sql);
        echo "  Enrollments:\n";
        while($detail = $detail_result->fetch_assoc()) {
            echo "    class_id=" . ($detail["class_id"] ?? "NULL") . ", section_id=" . ($detail["section_id"] ?? "NULL") . "\n";
        }
    }
} else {
    echo "No students found (extra student is properly enrolled).\n";
}

$conn->close();
?>
