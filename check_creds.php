<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';
$conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);
$res = $conn->query("SELECT * FROM payment_settings WHERE payment_type = 'billdesk'");
print_r($res->fetch_assoc());
$conn->close();