<?php
// Tax and EPF Configuration for India
// FY 2025-26

$config['tax_regime'] = 'new'; // 'new' or 'old'

// EPF Configuration
$config['epf'] = array(
    'enabled' => true,
    'employee_contribution_rate' => 12,      // 12% of EPF Wage
    'employer_contribution_rate' => 12,      // 12% of EPF Wage
    'employer_eps_rate' => 8.33,             // 8.33% to EPS (from 12%)
    'employer_pf_rate' => 3.67,              // 3.67% to PF (from 12%)
    'epf_wage_ceiling' => 15000,             // Wage ceiling for EPF calculation
    'eps_monthly_cap' => 1250,               // EPS monthly cap
    'da_applicable' => true,                 // Whether DA is included in EPF wage
    'basic_applicable' => true,              // Whether Basic is included in EPF wage
);

// ESI Configuration
$config['esi'] = array(
    'enabled' => true,
    'employee_contribution_rate' => 0.75,    // 0.75% of Employee's Gross Wages
    'employer_contribution_rate' => 3.25,    // 3.25% of Employee's Gross Wages
    'wage_ceiling' => 21000,                 // Monthly wage ceiling for ESI applicability
    'minimum_wage' => 0,                     // Minimum wage for ESI (typically no minimum)
);

// New Tax Regime FY 2025-26
$config['new_tax_regime'] = array(
    'standard_deduction' => 75000,           // Standard deduction for new regime
    'slabs' => array(
        array('from' => 0, 'to' => 300000, 'rate' => 0),
        array('from' => 300001, 'to' => 700000, 'rate' => 5),
        array('from' => 700001, 'to' => 1000000, 'rate' => 10),
        array('from' => 1000001, 'to' => 1200000, 'rate' => 15),
        array('from' => 1200001, 'to' => 1500000, 'rate' => 20),
        array('from' => 1500001, 'to' => PHP_INT_MAX, 'rate' => 30),
    ),
    'section_87a_rebate' => array(
        'income_limit' => 1200000,           // Income limit for rebate
        'rebate_amount' => 60000,            // Maximum rebate amount
    ),
);

// Old Tax Regime FY 2025-26 (for reference)
$config['old_tax_regime'] = array(
    'standard_deduction' => 50000,           // Standard deduction for old regime
    'slabs' => array(
        array('from' => 0, 'to' => 250000, 'rate' => 0),
        array('from' => 250001, 'to' => 500000, 'rate' => 5),
        array('from' => 500001, 'to' => 1000000, 'rate' => 20),
        array('from' => 1000001, 'to' => 1200000, 'rate' => 30),
        array('from' => 1200001, 'to' => 1500000, 'rate' => 35),
        array('from' => 1500001, 'to' => PHP_INT_MAX, 'rate' => 37),
    ),
);

// Surcharge Configuration (optional, can be added later)
$config['surcharge'] = array(
    'enabled' => false,
    'applicable_from_income' => 5000000, // Surcharge applicable from 50 lakhs
);

// Health and Education Cess
$config['cess'] = array(
    'enabled' => true,
    'rate' => 4,                             // 4% Health and Education Cess
);
