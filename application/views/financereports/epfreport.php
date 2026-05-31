<?php
// simple epf report view similar to payroll
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> EPF Report</h1>
    </section>
    <section class="content">
        <?php $this->load->view('financereports/_finance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>

                    <form role="form" action="<?php echo site_url('financereports/epfreport') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select class="form-control" name="filter_category[]" multiple="multiple" size="4">
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($filter_category) && is_array($filter_category) && in_array($cat['id'], $filter_category)) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-2" >
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('month'); ?></label>
                                    <select class="form-control" name="filter_month">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php 
                                        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                                        foreach ($months as $month) {
                                            $selected = (isset($filter_month) && $filter_month == $month) ? 'selected' : '';
                                            echo '<option value="' . $month . '" ' . $selected . '>' . $this->lang->line(strtolower($month)) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-2" >
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('year'); ?></label>
                                    <select class="form-control" name="filter_year">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php 
                                        $current_year = date('Y');
                                        for ($y = $current_year; $y >= $current_year - 10; $y--) {
                                            $selected = (isset($filter_year) && $filter_year == $y) ? 'selected' : '';
                                            echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="">
                        <div class="box-header ptbnull"></div>
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('epf_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('epf_report').' '. $this->customlib->get_postmessage(); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th>DOJ</th>
                                        <th><?php echo $this->lang->line('employee_id'); ?></th>
                                        <th>UAN</th>
                                        <th>Category</th>
                                        <th class="text text-right"><?php echo $this->lang->line('gross_salary'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th>Total Calendar days</th>
                                        <th>Payable Days</th>
                                        <th>Paid Days</th>
                                        <th><?php echo $this->lang->line('net_lop'); ?></th>
                                        <th class="text text-right"><?php echo $this->lang->line('lop_amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">EPF Wages (Gross - LOP) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">EPF (Employee) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('net_salary'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">PF Contribution 3.67% <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">EPS Contribution 8.33% <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">EDLI (Insurance) 0.5% <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">Admin Charges 0.5% <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">Employer EPF (13%) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_net_lop = 0;
                                    $total_calendar_days = 0;
                                    $total_payable_days = 0;
                                    $total_paid_days = 0;
                                    $total_lop_amt = 0;
                                    $gross_total = 0;
                                    $net_total   = 0;
                                    $epf_wages_total = 0;
                                    $emp_epf_total = 0;
                                    $employer_pf_total = 0;
                                    $employer_eps_total = 0;
                                    $employer_edli_total = 0;
                                    $employer_admin_total = 0;
                                    $empr_epf_total = 0;
                                    if (!empty($epfList)) {
                                        foreach ($epfList as $value) {
                                            // Skip if employee EPF is 0 or empty
                                            $emp_epf = !empty($value['employee_epf']) ? $value['employee_epf'] : 0;
                                            if ($emp_epf <= 0) {
                                                continue;
                                            }
                                            
                                            // use values added by controller
                                            $netlop = $value['net_lop_days'] ?? '';
                                            $lop_amt = $value['lop_amount'] ?? 0;
                                            $total_net_lop += is_numeric($netlop) ? $netlop : 0;
                                            $total_lop_amt += is_numeric($lop_amt) ? $lop_amt : 0;

                                            $days_in_month = 0;
                                            if (!empty($value['month']) && !empty($value['year'])) {
                                                $month_num = date('n', strtotime($value['month'] . ' 1'));
                                                $year_num = (int)$value['year'];
                                                if ($month_num > 0 && $year_num > 0) {
                                                    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_num, $year_num);
                                                }
                                            }
                                            $total_calendar_days += $days_in_month;
                                            $payable_days = $days_in_month - (is_numeric($netlop) ? (float)$netlop : 0);
                                            if ($payable_days < 0) {
                                                $payable_days = 0;
                                            }
                                            $total_payable_days += $payable_days;

                                            $paid_days = (float)$payable_days;
                                            if (!empty($value['date_of_joining']) && $days_in_month > 0 && !empty($value['month']) && !empty($value['year'])) {
                                                $doj_ts = strtotime($value['date_of_joining']);
                                                if ($doj_ts !== false) {
                                                    $month_start = sprintf('%04d-%02d-01', (int)$year_num, (int)$month_num);
                                                    $month_end = date('Y-m-t', strtotime($month_start));
                                                    $doj_date = date('Y-m-d', $doj_ts);

                                                    if ($doj_date > $month_end) {
                                                        $paid_days = 0;
                                                    } elseif ($doj_date > $month_start) {
                                                        $eligible_start = new DateTime($doj_date);
                                                        $eligible_end = new DateTime($month_end);
                                                        $eligible_days = (int)$eligible_start->diff($eligible_end)->days + 1;
                                                        $paid_days = min((float)$paid_days, (float)$eligible_days);
                                                    }
                                                }
                                            }
                                            if ($paid_days < 0) {
                                                $paid_days = 0;
                                            }
                                            $total_paid_days += $paid_days;

                                            $gross = $value['basic'] + $value['total_allowance'];
                                            $gross_total += $gross;
                                            $net_total += $value['net_salary'];
                                            $epf_wages_total += !empty($value['epf_wage']) ? $value['epf_wage'] : 0;
                                            $emp_epf_total += !empty($value['employee_epf']) ? $value['employee_epf'] : 0;
                                            $employer_pf = !empty($value['employer_pf']) ? $value['employer_pf'] : 0;
                                            $employer_eps = !empty($value['employer_eps']) ? $value['employer_eps'] : 0;
                                            $employer_edli = !empty($value['employer_edli']) ? $value['employer_edli'] : 0;
                                            $employer_admin = !empty($value['employer_admin']) ? $value['employer_admin'] : 0;
                                            $employer_pf_total += $employer_pf;
                                            $employer_eps_total += $employer_eps;
                                            $employer_edli_total += $employer_edli;
                                            $employer_admin_total += $employer_admin;
                                            $empr_epf_total += $employer_pf + $employer_eps + $employer_edli + $employer_admin;
                                            ?>
                                            <tr>
                                                <td><?php echo $value['name'] . ' ' . $value['surname']; ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($value['date_of_joining'])) {
                                                        echo date($this->customlib->getSchoolDateFormat(), strtotime($value['date_of_joining']));
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $value['employee_id']; ?></td>
                                                <td><?php echo !empty($value['uan_no']) ? $value['uan_no'] : '-'; ?></td>
                                                <td>
                                                    <?php if (!empty($value['staff_type'])): ?>
                                                        <span style="border-left: 3px solid <?php echo $value['staff_type_color'] ?? '#ccc'; ?>; padding-left: 6px; display: inline-block;">
                                                            <i class="fa <?php echo $value['staff_type_icon'] ?? 'fa-folder'; ?>" style="color: <?php echo $value['staff_type_color'] ?? '#ccc'; ?>; margin-right: 3px;"></i>
                                                            <?php echo $value['staff_type']; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span style="color: #999; font-style: italic;">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text text-right"><?php if ($gross > 0) { echo amountFormat($gross); } ?></td>
                                                <td><?php echo $days_in_month; ?></td>
                                                <td><?php echo rtrim(rtrim(number_format($payable_days, 2, '.', ''), '0'), '.'); ?></td>
                                                <td><?php echo rtrim(rtrim(number_format($paid_days, 2, '.', ''), '0'), '.'); ?></td>
                                                <td><?php echo $netlop; ?></td>
                                                <td class="text text-right"><?php if($lop_amt>0){ echo amountFormat($lop_amt);} ?></td>
                                                <td class="text text-right"><?php echo (!empty($value['epf_wage']) ? amountFormat($value['epf_wage']) : '-'); ?></td>
                                                <td class="text text-right"><?php echo (!empty($value['employee_epf']) ? amountFormat($value['employee_epf']) : '-'); ?></td>
                                                <td class="text text-right"><?php if ($value['net_salary'] > 0) { echo amountFormat($value['net_salary']); } ?></td>
                                                <td class="text text-right"><?php echo ($employer_pf > 0 ? amountFormat($employer_pf) : '-'); ?></td>
                                                <td class="text text-right"><?php echo ($employer_eps > 0 ? amountFormat($employer_eps) : '-'); ?></td>
                                                <td class="text text-right"><?php echo ($employer_edli > 0 ? amountFormat($employer_edli) : '-'); ?></td>
                                                <td class="text text-right"><?php echo ($employer_admin > 0 ? amountFormat($employer_admin) : '-'); ?></td>
                                                <td class="text text-right"><?php 
                                                    $total_empr = $employer_pf + $employer_eps + $employer_edli + $employer_admin;
                                                    echo ($total_empr > 0 ? amountFormat($total_empr) : '-'); 
                                                ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="box box-solid total-bg">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right"><?php echo $this->lang->line('grand_total'); ?></td>
                                        <td></td>
                                        <td class="text text-right"><?php if($gross_total > 0){ echo $currency_symbol . amountFormat($gross_total); } ?></td>
                                        <td class="text text-right"><?php if(isset($total_calendar_days) && $total_calendar_days > 0){ echo $total_calendar_days; } ?></td>
                                        <td class="text text-right"><?php if(isset($total_payable_days) && $total_payable_days > 0){ echo rtrim(rtrim(number_format($total_payable_days, 2, '.', ''), '0'), '.'); } ?></td>
                                        <td class="text text-right"><?php if(isset($total_paid_days) && $total_paid_days > 0){ echo rtrim(rtrim(number_format($total_paid_days, 2, '.', ''), '0'), '.'); }else{ echo '0'; } ?></td>
                                        <td class="text text-right"><?php if(isset($total_net_lop) && $total_net_lop > 0){ echo $total_net_lop; } ?></td>
                                        <td class="text text-right"><?php if(isset($total_lop_amt) && $total_lop_amt > 0){ echo $currency_symbol . amountFormat($total_lop_amt); } ?></td>
                                        <td class="text text-right"><?php if($epf_wages_total > 0){ echo $currency_symbol . amountFormat($epf_wages_total); } ?></td>
                                        <td class="text text-right"><?php if($emp_epf_total > 0){ echo $currency_symbol . amountFormat($emp_epf_total); } ?></td>
                                        <td class="text text-right"><?php if($net_total > 0){ echo $currency_symbol . amountFormat($net_total); } ?></td>
                                        <td class="text text-right"><?php if($employer_pf_total > 0){ echo $currency_symbol . amountFormat($employer_pf_total); } ?></td>
                                        <td class="text text-right"><?php if($employer_eps_total > 0){ echo $currency_symbol . amountFormat($employer_eps_total); } ?></td>
                                        <td class="text text-right"><?php if($employer_edli_total > 0){ echo $currency_symbol . amountFormat($employer_edli_total); } ?></td>
                                        <td class="text text-right"><?php if($employer_admin_total > 0){ echo $currency_symbol . amountFormat($employer_admin_total); } ?></td>
                                        <td class="text text-right"><?php if($empr_epf_total > 0){ echo $currency_symbol . amountFormat($empr_epf_total); } ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>   
</div>  
</section>
</div>

<script>
<?php
if (isset($search_type) && $search_type == 'period') {
    ?>

        $(document).ready(function () {
            showdate('period');
        });

    <?php
}
?>

</script>
<script type="text/javascript">
    $(document).ready(function () {
        var schoolName = "<?php echo addslashes($this->sch_setting_detail->name);?>";
        var schoolAddr = "<?php echo addslashes($this->sch_setting_detail->address);?>";
        var filterMonth = "<?php echo addslashes(!empty($filter_month) ? $filter_month : ''); ?>";
        var filterYear  = "<?php echo (int)(!empty($filter_year) ? $filter_year : date('Y')); ?>";
        var reportBase  = 'EPF Report';
        var reportName  = (filterMonth && filterYear) ? reportBase + '_' + filterMonth + '_' + filterYear : reportBase;
        var headerMsg = schoolName + "\n" + schoolAddr + "\n" + reportBase + "\n";
        $('.example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', filename: reportName },
                { extend: 'csv', filename: reportName },
                {
                    extend: 'excelHtml5',
                    title: '',
                    filename: reportName,
                    messageTop: headerMsg,
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        // Remove all cell styling to ensure uniform formatting
                        $('row c', sheet).attr('s', '');
                    },
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                // Strip all HTML tags and styling
                                return data.replace( /(<([^>]+)>)/ig, '' ).trim();
                            },
                            footer: function ( data, row, column, node ) {
                                return data.replace( /(<([^>]+)>)/ig, '' ).trim();
                            }
                        },
                        stripHtml: true,
                        stripNewlines: true
                    },
                    footer: true
                },
                {
                    extend: 'pdfHtml5',
                    title: '',
                    filename: reportName,
                    customize: function(doc) {
                        doc.content.splice(0,0,
                            { text: schoolName, style: 'dtHeader' },
                            { text: schoolAddr, style: 'dtSubHeader' },
                            { text: reportName, style: 'dtSubHeader' },
                            { text: '\n' }
                        );
                        doc.styles.dtHeader = { fontSize: 16, bold: true, alignment: 'center' };
                        doc.styles.dtSubHeader = { fontSize: 11, alignment: 'center' };
                    },
                    footer: true
                },
                'print'
            ]
        });
    });
</script>
