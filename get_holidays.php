<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$year = 2026;
$month = 1;

$db = new mysqli('127.0.0.1', 'root', '', 'mcekknagar');

if ($db->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    exit();
}

// Get official holidays from the database
$officialHolidays = [];
$holidayDetails = [];
$query = "SELECT description, from_date, to_date FROM annual_calendar WHERE (YEAR(from_date) = $year AND MONTH(from_date) = $month) OR (YEAR(to_date) = $year AND MONTH(to_date) = $month)";
if ($result = $db->query($query)) {
    echo "Holiday details from the database for January 2026:\n";
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row['from_date']);
        $end = new DateTime($row['to_date']);
        
        echo "  - Found event: '" . $row['description'] . "' from " . $start->format('Y-m-d') . " to " . $end->format('Y-m-d') . "\n";
        
        // Correctly iterate through the date range
        $current = clone $start;
        while ($current <= $end) {
            if ($current->format('m') == $month && $current->format('Y') == $year) {
                $dayOfWeek = $current->format('l');
                echo "    - Counting: " . $current->format('Y-m-d') . " (" . $dayOfWeek . ")\n";
                $officialHolidays[] = $current->format('Y-m-d');
            }
            $current->modify('+1 day');
        }
    }
    $result->free();
}
$officialHolidays = array_unique($officialHolidays);

// Get Sundays
$sundays = [];
$start_date_obj = new DateTime("$year-$month-01");
$end_of_month_obj = new DateTime($start_date_obj->format('Y-m-t'));

$current = clone $start_date_obj;
while ($current <= $end_of_month_obj) {
    if ($current->format('w') == 0) { // 0 for Sunday
        $sundays[] = $current->format('Y-m-d');
    }
    $current->modify('+1 day');
}
$sundays = array_unique($sundays);

// Separate the counts
$holidaysThatAreNotSundays = array_diff($officialHolidays, $sundays);
$sundaysThatAreNotHolidays = array_diff($sundays, $officialHolidays);

echo "\n--- Calculation --- \n";
echo "Total official holiday days found in database: " . count($officialHolidays) . "\n";
echo "Total Sundays in the month: " . count($sundays) . "\n";
echo "Official holidays that are also Sundays: " . count(array_intersect($officialHolidays, $sundays)) . "\n";
echo "Holidays that are not Sundays (other leaves): " . count($holidaysThatAreNotSundays) . "\n";
echo "Sundays that are not holidays: " . count($sundaysThatAreNotHolidays) . "\n";
echo "Total distinct leave days: " . count(array_unique(array_merge($sundays, $officialHolidays))) . "\n";

echo "\nFinal Answer:\n";
echo "Here are the 'other leave' titles:\n";
foreach ($holidayDetails as $holiday) {
    echo " - " . $holiday['description'] . "\n";
}

$db->close();
?>
