<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tax and EPF Calculation Library
 * Handles TDS calculation under new tax regime (FY 2025-26)
 * and EPF contributions for Indian payroll
 */
class Tax_epf_calculator
{
    private $CI;
    private $config;
    
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->config->load('tax_epf');
        $this->config = $this->CI->config->item('epf');
    }

    /**
     * Calculate EPF Wage (EPF salary)
     * EPF Wage = MIN(Basic + DA, 15000)
     * 
     * @param float $basic Basic salary
     * @param float $da Dearness allowance
     * @return float EPF wage
     */
    public function calculate_epf_wage($basic, $da = 0)
    {
        $epf_wage_ceiling = $this->config['epf_wage_ceiling'];
        $epf_wage = $basic;
        
        if ($this->config['da_applicable']) {
            $epf_wage += $da;
        }
        
        // Cap at wage ceiling
        if ($epf_wage > $epf_wage_ceiling) {
            $epf_wage = $epf_wage_ceiling;
        }
        
        return $epf_wage;
    }

    /**
     * Calculate Employee EPF Contribution
     * 12% of EPF Wage
     * 
     * @param float $epf_wage EPF wage
     * @return float Employee EPF contribution
     */
    public function calculate_employee_epf($epf_wage)
    {
        if (!$this->config['enabled']) {
            return 0;
        }
        
        $rate = $this->config['employee_contribution_rate'] / 100;
        return round($epf_wage * $rate, 2);
    }

    /**
     * Calculate Employer PF Contribution
     * 3.67% of EPF Wage (goes to PF account)
     * 
     * @param float $epf_wage EPF wage
     * @return float Employer PF contribution
     */
    public function calculate_employer_pf($epf_wage)
    {
        if (!$this->config['enabled']) {
            return 0;
        }
        
        $rate = $this->config['employer_pf_rate'] / 100;
        return round($epf_wage * $rate, 2);
    }

    /**
     * Calculate Employer EPS Contribution
     * 8.33% of EPF Wage, capped at ₹1,250/month
     * 
     * @param float $epf_wage EPF wage
     * @return float Employer EPS contribution
     */
    public function calculate_employer_eps($epf_wage)
    {
        if (!$this->config['enabled']) {
            return 0;
        }
        
        $rate = $this->config['employer_eps_rate'] / 100;
        $eps_amount = $epf_wage * $rate;
        $eps_cap = $this->config['eps_monthly_cap'];
        
        // Cap at maximum limit
        if ($eps_amount > $eps_cap) {
            $eps_amount = $eps_cap;
        }
        
        return round($eps_amount, 2);
    }

    /**
     * Calculate Employer EPF total contribution
     * Employer PF + Employer EPS
     * 
     * @param float $epf_wage EPF wage
     * @return float Employer EPF total contribution
     */
    public function calculate_employer_epf_total($epf_wage)
    {
        return $this->calculate_employer_pf($epf_wage) + $this->calculate_employer_eps($epf_wage);
    }

    /**
     * Calculate ESI Wage (ESI salary)
     * This is the total wages on which ESI is calculated
     * ESI Wage = MIN(Total Wages, 21000)
     * 
     * @param float $basic Basic salary
     * @param float $da Dearness allowance
     * @param float $other_allowances Other allowances (HRA, SA, etc.)
     * @param float $increment Increment amount (if any)
     * @return float ESI wage (capped at ceiling)
     */
    public function calculate_esi_wage($basic, $da = 0, $other_allowances = 0, $increment = 0)
    {
        // ESI wage ceiling per quarter (₹21,000 as per current ESI rules)
        $esi_wage_ceiling = 21000;
        
        // Total wages = Basic + DA + Other Allowances + Increment
        $esi_wage = $basic + $da + $other_allowances + $increment;
        
        // Cap at ESI wage ceiling
        if ($esi_wage > $esi_wage_ceiling) {
            $esi_wage = $esi_wage_ceiling;
        }
        
        return $esi_wage;
    }

    /**
     * Calculate Employee ESI Contribution
     * 0.75% of ESI wage (capped at ceiling)
     * Note: Employer also contributes 3.25% (not calculated here as per requirement)
     * 
     * @param float $esi_wage ESI wage
     * @return float Employee ESI contribution
     */
    public function calculate_employee_esi($esi_wage)
    {
        $esi_rate = 0.75; // Employee contribution rate
        return round($esi_wage * ($esi_rate / 100), 2);
    }

    /**
     * Calculate TDS under New Tax Regime FY 2025-26
     * Includes Section 87A rebate and health cess
     * 
     * @param float $annual_income Annual gross income
     * @param float $standard_deduction Standard deduction
     * @return array Array with 'tax' and 'breakdown' details
     */
    public function calculate_new_regime_tds($annual_income, $standard_deduction = null)
    {
        $new_regime = $this->CI->config->item('new_tax_regime');
        
        if ($standard_deduction === null) {
            $standard_deduction = $new_regime['standard_deduction'];
        }
        
        // Calculate taxable income
        $taxable_income = max(0, $annual_income - $standard_deduction);
        
        // Calculate tax based on slabs
        $tax = $this->_calculate_slab_tax($taxable_income, $new_regime['slabs']);
        
        // Apply Section 87A Rebate FIRST (before cess)
        $rebate = 0;
        $rebate_config = $new_regime['section_87a_rebate'];
        if ($annual_income <= $rebate_config['income_limit']) {
            // Full rebate if tax is less than rebate amount
            $rebate = min($tax, $rebate_config['rebate_amount']);
            $tax = max(0, $tax - $rebate);
        }
        
        // Apply health and education cess (4%) AFTER rebate
        $cess = 0;
        $cess_config = $this->CI->config->item('cess');
        if ($cess_config['enabled']) {
            $cess_rate = $cess_config['rate'] / 100;
            $cess = $tax * $cess_rate;
            $tax += $cess;
        }
        
        // Apply surcharge if applicable
        $surcharge = 0;
        $surcharge_config = $this->CI->config->item('surcharge');
        if ($surcharge_config['enabled'] && $annual_income > $surcharge_config['applicable_from_income']) {
            // Surcharge calculation (can be added later)
            $surcharge = 0;
        }
        
        return array(
            'tax' => round($tax, 2),
            'taxable_income' => $taxable_income,
            'standard_deduction' => $standard_deduction,
            'rebate' => $rebate,
            'cess' => $cess,
            'surcharge' => $surcharge,
        );
    }

    /**
     * Calculate monthly TDS (1/12 of annual TDS)
     * 
     * @param float $monthly_gross Monthly gross salary
     * @param int $months_in_year Months considered for annual calculation (default 12)
     * @return float Monthly TDS
     */
    public function calculate_monthly_tds($monthly_gross, $months_in_year = 12)
    {
        // Calculate annual income (simplified - assumes constant salary)
        $annual_income = $monthly_gross * $months_in_year;
        
        // Calculate annual TDS
        $tds_result = $this->calculate_new_regime_tds($annual_income);
        
        // Return monthly TDS
        return round($tds_result['tax'] / $months_in_year, 2);
    }

    /**
     * Calculate TDS using Year-To-Date (YTD) approach
     * Properly handles mid-year salary increments/decrements
     * 
     * @param float $ytd_income Year-to-date income (actual income earned till current month)
     * @param float $current_month_gross Current month's gross salary
     * @param int $current_month Month number (1-12)
     * @param int $total_months Total months in the year (default 12)
     * @return array ['annual_tax' => tax, 'monthly_tds' => monthly_tds, 'tds_method' => 'YTD']
     */
    public function calculate_tds_ytd($ytd_income, $current_month_gross, $current_month = 11, $total_months = 12)
    {
        // Project remaining months with current salary
        $remaining_months = $total_months - $current_month + 1;
        $projected_annual_income = $ytd_income + ($current_month_gross * $remaining_months);
        
        // Calculate tax on projected annual income
        $tds_result = $this->calculate_new_regime_tds($projected_annual_income);
        $annual_tax = $tds_result['tax'];
        
        // Calculate remaining tax to be deducted
        // (This would be more accurate if we knew tax paid in previous months)
        $monthly_tds = round($annual_tax / $total_months, 2);
        
        return array(
            'annual_tax' => $annual_tax,
            'monthly_tds' => $monthly_tds,
            'tds_method' => 'YTD (Projected)',
            'ytd_income' => $ytd_income,
            'projected_annual' => $projected_annual_income,
            'months_remaining' => $remaining_months,
        );
    }

    /**
     * Calculate TDS for current financial year with actual YTD income
     * This is the most accurate method for mid-year increments
     * 
     * @param float $ytd_income Actual year-to-date income
     * @param int $total_months Total months in year (usually 12)
     * @return array ['total_tax' => tax, 'monthly_average' => avg_tds, 'tds_method' => 'YTD (Actual)']
     */
    public function calculate_tds_ytd_actual($ytd_income, $total_months = 12)
    {
        // Calculate tax on actual YTD income
        // Year is not yet complete, so this is provisional
        $tds_result = $this->calculate_new_regime_tds($ytd_income);
        $ytd_tax = $tds_result['tax'];
        
        // Calculate average monthly TDS for months completed
        $months_completed = ceil($ytd_income / 30000); // Rough estimate - should be passed as parameter
        $monthly_average = ($months_completed > 0) ? round($ytd_tax / $months_completed, 2) : 0;
        
        return array(
            'ytd_tax' => $ytd_tax,
            'monthly_average' => $monthly_average,
            'tds_method' => 'YTD (Actual)',
            'ytd_income' => $ytd_income,
        );
    }

    /**
     * Helper function to calculate tax based on slabs
     * 
     * @param float $taxable_income Taxable income
     * @param array $slabs Tax slabs array
     * @return float Tax amount
     */
    private function _calculate_slab_tax($taxable_income, $slabs)
    {
        $tax = 0;
        
        foreach ($slabs as $slab) {
            if ($taxable_income <= $slab['from']) {
                break;
            }
            
            $taxable_in_slab = min($taxable_income, $slab['to']) - $slab['from'];
            $tax += $taxable_in_slab * ($slab['rate'] / 100);
        }
        
        return $tax;
    }

    /**
     * Get EPF configuration
     * 
     * @return array EPF configuration
     */
    public function get_epf_config()
    {
        return $this->config;
    }

    /**
     * Get tax regime configuration
     * 
     * @return array Tax regime configuration
     */
    public function get_tax_regime_config()
    {
        return $this->CI->config->item('new_tax_regime');
    }

    /**
     * Full payroll calculation for a staff member
     * Returns all deductions and contributions
     * 
     * @param array $staff_data Staff salary data (basic, da, allowances)
     * @return array Full payroll breakdown
     */
    public function calculate_full_payroll($staff_data)
    {
        $basic = isset($staff_data['basic']) ? $staff_data['basic'] : 0;
        $da = isset($staff_data['da']) ? $staff_data['da'] : 0;
        $total_allowance = isset($staff_data['total_allowance']) ? $staff_data['total_allowance'] : 0;
        $total_deduction = isset($staff_data['total_deduction']) ? $staff_data['total_deduction'] : 0;
        $lop_deduction = isset($staff_data['lop_deduction']) ? $staff_data['lop_deduction'] : 0;
        
        // Calculate EPF
        $epf_wage = $this->calculate_epf_wage($basic, $da);
        $employee_epf = $this->calculate_employee_epf($epf_wage);
        $employer_pf = $this->calculate_employer_pf($epf_wage);
        $employer_eps = $this->calculate_employer_eps($epf_wage);
        
        // Calculate TDS
        $gross_salary = $basic + $total_allowance;
        $monthly_tds = $this->calculate_monthly_tds($gross_salary);
        
        return array(
            'epf_wage' => $epf_wage,
            'employee_epf' => $employee_epf,
            'employer_pf' => $employer_pf,
            'employer_eps' => $employer_eps,
            'employer_epf_total' => $employer_pf + $employer_eps,
            'tds' => round($monthly_tds, 2),
            'total_deductions' => $employee_epf + $monthly_tds + $total_deduction + $lop_deduction,
        );
    }
}
