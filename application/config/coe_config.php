<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
|--------------------------------------------------------------------------
| CoE (Controller of Examinations) Module Configuration
|--------------------------------------------------------------------------
*/

// AES-256-CBC key for QR code hash generation and QPD encryption
// IMPORTANT: Change this before any production deployment
$config['coe_qr_secret_key']    = 'CoE_Minerva_QR_Secret_2025_MCE!'; // 32 chars
$config['coe_qr_iv']            = 'CoEIV2025MCE!!!!';                 // 16 chars

// Hall ticket number prefix and zero-padding
$config['coe_ht_prefix']        = 'HT';
$config['coe_ht_padding']       = 6;  // HT000001

// Attendance type IDs (from attendence_type table)
$config['coe_present_type_ids'] = [1, 3, 6]; // Present, Late, Half Day — count as attended

// CBCS grading grade points (10-point scale, Anna University pattern)
$config['coe_grade_points'] = [
    'O'  => 10,  // 91-100
    'A+' => 9,   // 81-90
    'A'  => 8,   // 71-80
    'B+' => 7,   // 61-70
    'B'  => 6,   // 50-60  (pass mark)
    'U'  => 0,   // < 50   (fail)
];

$config['coe_grade_ranges'] = [
    // grade => [min, max]
    'O'  => [91, 100],
    'A+' => [81,  90],
    'A'  => [71,  80],
    'B+' => [61,  70],
    'B'  => [50,  60],
    'U'  => [0,   49],
];
