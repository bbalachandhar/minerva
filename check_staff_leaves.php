<?php
// Temporary debug file to check staff leave setup
// Replace STAFF_ID with the actual staff ID

define('BASEPATH', TRUE);
require_once 'application/config/database.php';

$conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$staff_id = 471; // CHANGE THIS to the staff ID you're checking

echo "<h2>Checking Leave Setup for Staff ID: $staff_id</h2>";

// Check staff_leave_details
echo "<h3>1. Staff Leave Details (Allotted Leaves)</h3>";
$query = "SELECT sld.*, lt.type, lt.is_lop 
          FROM staff_leave_details sld 
          JOIN leave_types lt ON lt.id = sld.leave_type_id 
          WHERE sld.staff_id = $staff_id";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Leave Type</th><th>Allotted</th><th>Is LOP?</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td>" . $row['alloted_leave'] . "</td>";
        echo "<td>" . ($row['is_lop'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No leave allotments found! Staff needs leave allocation.</p>";
}

// Check monthly balance records for January 2026
echo "<h3>2. Monthly Leave Balance (January 2026)</h3>";
$query = "SELECT smlb.*, lt.type 
          FROM staff_monthly_leave_balance smlb 
          JOIN leave_types lt ON lt.id = smlb.leave_type_id 
          WHERE smlb.staff_id = $staff_id 
          AND smlb.month = 1 
          AND smlb.year = 2026";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Leave Type</th><th>Opening</th><th>Earned</th><th>Used (LOP Adj)</th><th>Used (Leave App)</th><th>Other Ded</th><th>Closing</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td>" . $row['opening_balance'] . "</td>";
        echo "<td>" . $row['earned_in_month'] . "</td>";
        echo "<td>" . $row['used_for_lop_adjustment'] . "</td>";
        echo "<td>" . $row['used_for_leave_application'] . "</td>";
        echo "<td>" . $row['other_deductions'] . "</td>";
        echo "<td>" . $row['closing_balance'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>No monthly balance records found for January 2026. Will be auto-created during payroll processing.</p>";
}

// Check setting
echo "<h3>3. System Setting</h3>";
$query = "SELECT auto_adjust_lop_with_leaves FROM sch_settings LIMIT 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$setting = $row['auto_adjust_lop_with_leaves'] ?? 0;
echo "<p>Auto Adjust LOP with Leaves: <strong>" . ($setting == 1 ? 'ENABLED' : 'DISABLED') . "</strong></p>";

if ($setting != 1) {
    echo "<p style='color: red;'>⚠️ Setting is DISABLED! Enable it in Admin → Settings → Attendance Type</p>";
}

$conn->close();
?>
