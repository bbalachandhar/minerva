<?php

$config['staffattendance'] = array(
    'present' => 1,
    'late' => 2,
    'absent' => 3,
    'half_day' => 6,
    'holiday' => 5,
    'first_half_permission' => 7,
    'second_half_permission' => 9,
    'first_half_absent' => 10,
    'second_half_absent' => 11
);

$config['contracttype'] = array(
    'Visiting Faculty' => 'Visiting Faculty',
    'Part Time Faculty' => 'Part Time Faculty',
    'Full Time Faculty' => 'Full Time Faculty',
);

$config['status'] = array(
    'approve' => lang('approved'),
    'disapprove' => lang('disapproved'),
    'pending' => lang('pending'),
    'recommended' => lang('recommended'),
);

$config['marital_status'] = array(
    'Single' => lang('single'),
    'Married' => lang('married'),
    'Widowed' => lang('widowed'),
    'Separated' => lang('separated'),
    'Not Specified' => lang('not_specified'),
);

$config['payroll_status'] = array(
    'generated' => lang('generated'),
    'paid' => lang('paid'),
    'unpaid' => lang('unpaid'),
    'not_generate' => lang('not_generated'),
    'no_attendance' => 'No Attendance',
);
$config['lop_rules'] = array(
    'half_day_weight' => 0.5,
    'late_to_half_day' => 0,
    'permission_to_half_day' => 0,
);
$config['payment_mode'] = array(
    'cash' => lang('cash'),
    'cheque' => lang('cheque'),
    'online' => lang('transfer_to_bank_account'),
);
$config['enquiry_status'] = array(
    'active' => lang('active'),
    'passive' => lang('passive'),
    'dead' => lang('dead'),
    'application_done' => lang('application_done'),
    'lost' => lang('lost'),
);
$config['search_type'] = array(
    'today' => lang('today'),
    'this_week' => lang('this_week'),
    'last_week' => lang('last_week'),
    'this_month' => lang('this_month'),
    'last_month' => lang('last_month'),
    'last_3_month' => lang('last_3_month'),
    'last_6_month' => lang('last_6_month'),
    'last_12_month' => lang('last_12_month'),
    'this_year' => lang('this_year'),
    'last_year' => lang('last_year'),
    'period' => lang('period'),
);