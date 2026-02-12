<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-gear"></i> EPF & TDS Configuration
            <small>India FY 2025-26</small>
        </h1>
    </section>
    
    <section class="content">
        <div class="row">
            <!-- EPF Settings -->
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-money"></i> Provident Fund (EPF) Settings
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr style="background: #f0f8ff;">
                                    <th style="font-weight: 600; color: #0056b3;">Parameter</th>
                                    <th style="font-weight: 600; color: #0056b3;">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Employee Contribution Rate</strong></td>
                                    <td style="color: #28a745; font-weight: 600;"><?php echo $epf['employee_contribution_rate']; ?>%</td>
                                </tr>
                                <tr>
                                    <td><strong>Employer Contribution (Total)</strong></td>
                                    <td style="color: #28a745; font-weight: 600;"><?php echo $epf['employer_contribution_rate']; ?>%</td>
                                </tr>
                                <tr style="background: #fff3cd;">
                                    <td><strong>&nbsp;&nbsp;├─ PF Contribution</strong></td>
                                    <td style="color: #ff6b00; font-weight: 600;"><?php echo $epf['employer_pf_rate']; ?>%</td>
                                </tr>
                                <tr style="background: #fff3cd;">
                                    <td><strong>&nbsp;&nbsp;└─ EPS Contribution</strong></td>
                                    <td style="color: #ff6b00; font-weight: 600;"><?php echo $epf['employer_eps_rate']; ?>%</td>
                                </tr>
                                <tr>
                                    <td><strong>EPF Wage Ceiling</strong></td>
                                    <td style="color: #0056b3; font-weight: 600;">₹<?php echo number_format($epf['epf_wage_ceiling']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>EPS Monthly Cap</strong></td>
                                    <td style="color: #0056b3; font-weight: 600;">₹<?php echo number_format($epf['eps_monthly_cap']); ?></td>
                                </tr>
                                <tr style="background: #e8f5e9;">
                                    <td><strong>DA Applicable in EPF</strong></td>
                                    <td style="color: #2e7d32; font-weight: 600;"><?php echo $epf['da_applicable'] ? 'YES' : 'NO'; ?></td>
                                </tr>
                                <tr style="background: #e8f5e9;">
                                    <td><strong>Basic Applicable in EPF</strong></td>
                                    <td style="color: #2e7d32; font-weight: 600;"><?php echo $epf['basic_applicable'] ? 'YES' : 'NO'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                            <h4 style="margin-top: 0; color: #1565c0;">EPF Wage Calculation:</h4>
                            <p style="margin: 8px 0; color: #555;">
                                <strong>EPF Wage</strong> = MIN(Basic + DA, ₹<?php echo number_format($epf['epf_wage_ceiling']); ?>)
                            </p>
                            <p style="margin: 8px 0; color: #555;">
                                <strong>Employee EPF</strong> = <?php echo $epf['employee_contribution_rate']; ?>% × EPF Wage
                            </p>
                            <p style="margin: 8px 0; color: #555;">
                                <strong>Employer PF</strong> = <?php echo $epf['employer_pf_rate']; ?>% × EPF Wage
                            </p>
                            <p style="margin: 8px 0; color: #555;">
                                <strong>Employer EPS</strong> = MIN(<?php echo $epf['employer_eps_rate']; ?>% × EPF Wage, ₹<?php echo number_format($epf['eps_monthly_cap']); ?> per month)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tax Settings -->
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-calculator"></i> New Tax Regime Settings (FY 2025-26)
                        </h3>
                    </div>
                    <div class="box-body">
                        <div style="margin-bottom: 15px; padding: 10px; background: #f0f8ff; border-radius: 4px;">
                            <strong>Current Tax Regime:</strong>
                            <span style="background: #0056b3; color: white; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
                                <?php echo strtoupper($tax_regime); ?>
                            </span>
                        </div>

                        <h4 style="color: #28a745; margin-top: 20px;">Tax Slabs:</h4>
                        <table class="table table-striped table-hover" style="font-size: 12px;">
                            <thead>
                                <tr style="background: #e8f5e9;">
                                    <th style="color: #2e7d32;">Income Range</th>
                                    <th style="color: #2e7d32; text-align: right;">Tax Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
$income_from = 0;
foreach ($new_tax_regime['slabs'] as $i => $slab) {
    $from = number_format($slab['from']);
    $to = ($slab['to'] == PHP_INT_MAX) ? 'Above' : number_format($slab['to']);
    $is_last = ($slab['to'] == PHP_INT_MAX);
    ?>
                                    <tr>
                                        <td>
                                            <?php if ($is_last) { ?>
                                                ₹<?php echo $from; ?> and above
                                            <?php } else { ?>
                                                ₹<?php echo $from; ?> - ₹<?php echo $to; ?>
                                            <?php } ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 600; color: #0056b3;">
                                            <?php echo $slab['rate']; ?>%
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <h4 style="color: #28a745; margin-top: 20px;">Deductions & Rebates:</h4>
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Standard Deduction</strong></td>
                                    <td style="text-align: right; color: #0056b3; font-weight: 600;">₹<?php echo number_format($new_tax_regime['standard_deduction']); ?></td>
                                </tr>
                                <tr style="background: #e8f5e9;">
                                    <td><strong>Section 87A Rebate (Income ≤ ₹<?php echo number_format($new_tax_regime['section_87a_rebate']['income_limit']); ?>)</strong></td>
                                    <td style="text-align: right; color: #2e7d32; font-weight: 600;">₹<?php echo number_format($new_tax_regime['section_87a_rebate']['rebate_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Health & Education Cess</strong></td>
                                    <td style="text-align: right; color: #d32f2f; font-weight: 600;">4%</td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="margin-top: 20px; padding: 15px; background: #f3e5f5; border-left: 4px solid #9c27b0; border-radius: 4px;">
                            <h4 style="margin-top: 0; color: #6a1b9a;">Tax Calculation Formula:</h4>
                            <p style="margin: 8px 0; font-size: 11px; color: #555;">
                                1. Taxable Income = Gross - Standard Deduction (₹<?php echo number_format($new_tax_regime['standard_deduction']); ?>)
                            </p>
                            <p style="margin: 8px 0; font-size: 11px; color: #555;">
                                2. Tax = Apply slab rates to taxable income
                            </p>
                            <p style="margin: 8px 0; font-size: 11px; color: #555;">
                                3. Rebate (87A) = Up to ₹<?php echo number_format($new_tax_regime['section_87a_rebate']['rebate_amount']); ?> if income ≤ ₹<?php echo number_format($new_tax_regime['section_87a_rebate']['income_limit']); ?>
                            </p>
                            <p style="margin: 8px 0; font-size: 11px; color: #555;">
                                4. Cess = 4% of (Tax - Rebate)
                            </p>
                            <p style="margin: 8px 0; font-size: 11px; color: #555;">
                                5. Total TDS = Tax - Rebate + Cess
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- YTD TDS Calculation Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-calendar"></i> Year-To-Date (YTD) TDS Calculation
                        </h3>
                        <p style="margin: 8px 0; color: #666; font-size: 12px;">
                            <strong>Method Used:</strong> Dynamic calculation based on actual income earned till current month
                        </p>
                    </div>
                    <div class="box-body">
                        <!-- WHY YTD -->
                        <div style="margin-bottom: 20px; padding: 15px; background: #fffbea; border-left: 4px solid #ffa500; border-radius: 4px;">
                            <h4 style="margin-top: 0; color: #ff6b00;">Why YTD Calculation?</h4>
                            <p style="margin: 8px 0; color: #555; font-size: 12px;">
                                <strong>Problem with Simple Method:</strong> If an employee gets a salary increment mid-year, the simple method assumes they earned that higher salary for all 12 months, resulting in <span style="background: #ffe6e6; padding: 2px 4px;">OVERCHARGING of TDS</span>.
                            </p>
                            <p style="margin: 8px 0; color: #555; font-size: 12px;">
                                <strong>YTD Solution:</strong> Uses actual income earned from January to current month, providing <span style="background: #e6ffe6; padding: 2px 4px;">ACCURATE TDS</span> based on real earnings.
                            </p>
                        </div>

                        <!-- REAL EXAMPLE -->
                        <h4 style="color: #1565c0; margin-top: 20px;">Real-World Example: Mid-Year Salary Increment</h4>
                        
                        <table class="table table-bordered" style="margin-top: 15px;">
                            <thead>
                                <tr style="background: #e8f4f8;">
                                    <th style="color: #0056b3; font-weight: 600; width: 40%;">Scenario Details</th>
                                    <th style="color: #0056b3; font-weight: 600; width: 30%;">Simple Method</th>
                                    <th style="color: #0056b3; font-weight: 600; width: 30%;">YTD Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Jan-Oct Salary</strong></td>
                                    <td>₹90,000/month</td>
                                    <td>₹90,000/month</td>
                                </tr>
                                <tr style="background: #e8f5e9;">
                                    <td><strong>Nov-Dec Salary</strong></td>
                                    <td>₹120,000/month</td>
                                    <td>₹120,000/month</td>
                                </tr>
                                <tr>
                                    <td><strong>YTD Income (Till Oct)</strong></td>
                                    <td>Not considered</td>
                                    <td>₹90,000 × 10 = <span style="color: #0056b3; font-weight: 600;">₹9,00,000</span></td>
                                </tr>
                                <tr style="background: #fff3cd;">
                                    <td><strong>November Calculation Base</strong></td>
                                    <td>₹120,000 × 12 = <span style="color: #d32f2f; font-weight: 600;">₹14,40,000</span> ❌ INFLATED</td>
                                    <td>₹9,00,000 + (₹120,000 × 2) = <span style="color: #28a745; font-weight: 600;">₹11,40,000</span> ✅ ACTUAL</td>
                                </tr>
                                <tr>
                                    <td><strong>Taxable Income (After ₹75K Deduction)</strong></td>
                                    <td><span style="color: #d32f2f; font-weight: 600;">₹13,65,000</span></td>
                                    <td><span style="color: #28a745; font-weight: 600;">₹10,65,000</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tax Calculated</strong></td>
                                    <td><span style="color: #d32f2f; font-weight: 600;">₹1,33,000</span></td>
                                    <td><span style="color: #28a745; font-weight: 600;">₹59,750</span></td>
                                </tr>
                                <tr style="background: #e8f5e9;">
                                    <td><strong>Section 87A Rebate (≤ ₹12L)</strong></td>
                                    <td>NOT Eligible (Income > ₹12L)</td>
                                    <td>✅ ELIGIBLE - Rebate ₹60,000</td>
                                </tr>
                                <tr>
                                    <td><strong>Final TDS</strong></td>
                                    <td><span style="color: #d32f2f; font-weight: 600;">₹11,083/month × 2 = ₹22,166</span> ❌ OVERCHARGED</td>
                                    <td><span style="color: #28a745; font-weight: 600;">₹0 (Fully Rebated)</span> ✅ CORRECT</td>
                                </tr>
                                <tr style="background: #ffebee;">
                                    <td><strong style="color: #d32f2f;">Difference (Overcharge)</strong></td>
                                    <td colspan="2" style="color: #d32f2f; font-weight: 600;">You would be overcharged by ₹22,166 with simple method!</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- HOW IT WORKS -->
                        <div style="margin-top: 20px; padding: 15px; background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px;">
                            <h4 style="margin-top: 0; color: #1565c0;">How YTD Calculation Works:</h4>
                            <ol style="margin: 0; padding-left: 20px; color: #555; font-size: 12px;">
                                <li style="margin: 8px 0;"><strong>Step 1:</strong> Fetch all payslips from January to previous month</li>
                                <li style="margin: 8px 0;"><strong>Step 2:</strong> Calculate total YTD gross income</li>
                                <li style="margin: 8px 0;"><strong>Step 3:</strong> Add projected income for remaining months (at current salary)</li>
                                <li style="margin: 8px 0;"><strong>Step 4:</strong> Calculate tax on projected annual income</li>
                                <li style="margin: 8px 0;"><strong>Step 5:</strong> Apply Section 87A rebate if eligible</li>
                                <li style="margin: 8px 0;"><strong>Step 6:</strong> Divide by 12 to get monthly TDS</li>
                            </ol>
                        </div>

                        <!-- BENEFITS -->
                        <div style="margin-top: 15px; padding: 15px; background: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 4px;">
                            <h4 style="margin-top: 0; color: #2e7d32;"><i class="fa fa-check-circle"></i> Benefits of YTD Method:</h4>
                            <ul style="margin: 0; padding-left: 20px; color: #555; font-size: 12px;">
                                <li style="margin: 8px 0;">✅ Handles mid-year salary increments correctly</li>
                                <li style="margin: 8px 0;">✅ Prevents overcharging of TDS</li>
                                <li style="margin: 8px 0;">✅ Automatically applies Section 87A rebate when eligible</li>
                                <li style="margin: 8px 0;">✅ Accurate for promotions and transfers with salary changes</li>
                                <li style="margin: 8px 0;">✅ Compliant with Indian tax regulation</li>
                                <li style="margin: 8px 0;">✅ No manual adjustments needed</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Calculation Examples -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-lightbulb-o"></i> Tax Calculation Examples (Annual)
                        </h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr style="background: #e3f2fd;">
                                    <th style="color: #1565c0; font-weight: 600;">Gross Income</th>
                                    <th style="color: #1565c0; font-weight: 600;">Taxable Income</th>
                                    <th style="color: #1565c0; font-weight: 600;">Tax</th>
                                    <th style="color: #1565c0; font-weight: 600;">Rebate (87A)</th>
                                    <th style="color: #1565c0; font-weight: 600;">Cess (4%)</th>
                                    <th style="color: #1565c0; font-weight: 600;">Total TDS</th>
                                    <th style="color: #1565c0; font-weight: 600;">Monthly TDS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
// Example calculations
$examples = array(
    600000,    // Below Section 87A limit
    900000,    // Below Section 87A limit
    1200000,   // At Section 87A limit
    1500000,   // Above Section 87A limit
    2000000    // High income
);

foreach ($examples as $annual_gross) {
    $taxable_income = max(0, $annual_gross - $new_tax_regime['standard_deduction']);
    
    // Calculate tax based on slabs
    $tax = 0;
    foreach ($new_tax_regime['slabs'] as $slab) { 
        if ($taxable_income <= $slab['from']) {
            break;
        }
        $taxable_in_slab = min($taxable_income, $slab['to']) - $slab['from'];
        $tax += $taxable_in_slab * ($slab['rate'] / 100);
    }
    
    // Apply Section 87A rebate
    $rebate = 0;
    if ($annual_gross <= $new_tax_regime['section_87a_rebate']['income_limit']) {
        $rebate = min($tax, $new_tax_regime['section_87a_rebate']['rebate_amount']);
    }
    
    $tax_after_rebate = $tax - $rebate;
    $cess = $tax_after_rebate * 0.04;
    $total_tds = $tax_after_rebate + $cess;
    $monthly_tds = $total_tds / 12;
    
    $rebate_applied = ($rebate > 0) ? 'YES' : 'NO';
    $rebate_style = ($rebate > 0) ? 'background: #e8f5e9; color: #2e7d32; font-weight: 600;' : '';
    ?>
                                <tr>
                                    <td style="color: #0056b3; font-weight: 600;">₹<?php echo number_format($annual_gross); ?></td>
                                    <td>₹<?php echo number_format($taxable_income); ?></td>
                                    <td>₹<?php echo number_format($tax, 2); ?></td>
                                    <td style="<?php echo $rebate_style; ?>">₹<?php echo number_format($rebate, 2); ?> (<?php echo $rebate_applied; ?>)</td>
                                    <td>₹<?php echo number_format($cess, 2); ?></td>
                                    <td style="color: #d32f2f; font-weight: 600;">₹<?php echo number_format($total_tds, 2); ?></td>
                                    <td style="color: #ff6b00; font-weight: 600;">₹<?php echo number_format($monthly_tds, 2); ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="row">
            <div class="col-md-12">
                <a href="<?php echo base_url('admin/payroll'); ?>" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> Back to Payroll
                </a>
            </div>
        </div>
    </section>
</div>

<style type="text/css">
    .box-primary > .box-header,
    .box-success > .box-header,
    .box-info > .box-header {
        background-color: #f8f9fa;
        border-top: 3px solid;
    }
    
    .box-primary > .box-header {
        border-top-color: #0056b3;
    }
    
    .box-success > .box-header {
        border-top-color: #28a745;
    }
    
    .box-info > .box-header {
        border-top-color: #17a2b8;
    }
</style>
