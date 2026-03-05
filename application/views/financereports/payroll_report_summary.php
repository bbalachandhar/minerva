<?php
// Version: 2026-02-18-FINAL - Grand total in tfoot for Excel
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style type="text/css">
    /*REQUIRED*/
    .carousel-row {
        margin-bottom: 10px;
    }
    .slide-row {
        padding: 0;
        background-color: #ffffff;
        min-height: 150px;
        border: 1px solid #e7e7e7;
        overflow: hidden;
        height: auto;
        position: relative;
    }
    .slide-carousel {
        width: 20%;
        float: left;
        display: inline-block;
    }
    .slide-carousel .carousel-indicators {
        margin-bottom: 0;
        bottom: 0;
        background: rgba(0, 0, 0, .5);
    }
    .slide-carousel .carousel-indicators li {
        border-radius: 0;
        width: 20px;
        height: 6px;
    }
    .slide-carousel .carousel-indicators .active {
        margin: 1px;
    }
    .slide-content {
        position: absolute;
        top: 0;
        left: 20%;
        display: block;
        float: left;
        width: 80%;
        max-height: 76%;
        padding: 1.5% 2% 2% 2%;
        overflow-y: auto;
    }
    .slide-content h4 {
        margin-bottom: 3px;
        margin-top: 0;
    }
    .slide-footer {
        position: absolute;
        bottom: 0;
        left: 20%;
        width: 78%;
        height: 20%;
        margin: 1%;
    }
    /* Scrollbars */
    .slide-content::-webkit-scrollbar {
        width: 5px;
    }
    .slide-content::-webkit-scrollbar-thumb:vertical {
        margin: 5px;
        background-color: #999;
        -webkit-border-radius: 5px;
    }
    .slide-content::-webkit-scrollbar-button:start:decrement,
    .slide-content::-webkit-scrollbar-button:end:increment {
        height: 5px;
        display: block;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('financereports/_finance'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>

                    <form role="form" action="<?php echo site_url('financereports/payrollreportsummary') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-3" >
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_type'); ?></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">

                                        <?php foreach ($searchlist as $key => $search) {
                                            ?>
                                            <option value="<?php echo $key ?>" <?php
                                            if ((isset($search_type)) && ($search_type == $key)) {

                                                echo "selected";
                                            }
                                            ?>><?php echo $search ?></option>
                                                <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-2" >
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('month'); ?></label>
                                    <select class="form-control" name="filter_month">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php 
                                        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
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
                            <div id='date_result'>

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
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> Payroll Report Summary</h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php
                                echo 'Payroll Report Summary '.
                                $this->customlib->get_postmessage();
                            
                                ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('name'); ?></th>
                                        <th><?php echo $this->lang->line('month_year'); ?></th>
                                        <th>Category</th>
                                        <th><?php echo $this->lang->line('payslip'); ?> #</th>
                                        <th class="text text-right"><?php echo $this->lang->line('basic_salary'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('earning'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('gross_salary'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">No. Of Days</th>
                                        <th class="text text-right">AWD</th>
                                        <th class="text text-right">Paid Days</th>
                                        <th class="text text-right">LOP Days</th>
                                        <th class="text text-right">LOP <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">EPF Wages (Gross - LOP) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">EPF (Employee) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">ESI Wages (Gross - LOP, if Gross ≤ 21,000) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">ESI (Employee) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right">Tax/TDS <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('deduction'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text text-right"><?php echo $this->lang->line('net_salary'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $basic = 0;
                                    $gross = 0;
                                    $net = 0;
                                    $earnings = 0;
                                    $deduction = 0;
                                    $tax = 0;
                                    $total_lop = 0;
                                    $total_epf_wages = 0;
                                    $total_employee_epf = 0;
                                    $total_esi_wages = 0;
                                    $total_esi = 0;
                                    $total_no_of_days = 0;
                                    $total_awd = 0;
                                    $total_paid_days = 0;
                                    $total_lop_days = 0;

                                    // compute later as we iterate rows

                                    if (empty($payrollList)) {
                                        ?>

                                        <?php
                                    } else {
                                        $count = 1;
										$grossTotal = 0;
										$netTotal = 0;
                                        foreach ($payrollList as $key => $value) {
                                            $basic += $value["basic"];
                                            $gross += $value["basic"] + $value["total_allowance"];  // Gross = Basic + Allowances (NO deductions)
                                            $earnings += $value["total_allowance"];
                                            // include leave deduction in the overall deduction total
                                            $deduction += $value["total_deduction"]
                                                 + (!empty($value["leave_deduction"]) ? $value["leave_deduction"] : 0)
                                                 + (!empty($value["employee_epf"]) ? $value["employee_epf"] : 0)
                                                 + (!empty($value["esi_deduction"]) ? $value["esi_deduction"] : 0)
                                                 + (!empty($value["tax"]) ? $value["tax"] : 0);
                                            if ($value["tax"] != '') {
                                                $taxdata = $value["tax"];
                                            } else {
                                                $taxdata = 0;
                                            }
                                            $tax += $taxdata;
                                            // Add net salary from database to total
                                            $netTotal += $value["net_salary"];
                                            // Add LOP total
                                            $total_lop += !empty($value["leave_deduction"]) ? $value["leave_deduction"] : 0;
                                            $total_epf_wages += !empty($value["epf_wage"]) ? $value["epf_wage"] : 0;
                                            // Add EPF and ESI totals
                                            $total_employee_epf += !empty($value["employee_epf"]) ? $value["employee_epf"] : 0;
                                            $total_esi_wages += !empty($value["esi_wage"]) ? $value["esi_wage"] : 0;
                                            $total_esi += !empty($value["esi_deduction"]) ? $value["esi_deduction"] : 0;

                                            $days_in_month = 0;
                                            if (!empty($value['month']) && !empty($value['year'])) {
                                                $month_num = date('n', strtotime($value['month'] . ' 1'));
                                                $year_num = (int)$value['year'];
                                                if ($month_num > 0 && $year_num > 0) {
                                                    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_num, $year_num);
                                                }
                                            }

                                            $lop_days = 0;
                                            if (isset($value['net_lop_days']) && $value['net_lop_days'] !== '') {
                                                $lop_days = (float)$value['net_lop_days'];
                                            } elseif (isset($value['actual_lop_days']) && $value['actual_lop_days'] !== '') {
                                                $lop_days = (float)$value['actual_lop_days'];
                                            }

                                            $awd_days = $days_in_month - $lop_days;
                                            if ($awd_days < 0) {
                                                $awd_days = 0;
                                            }

                                            $paid_days = (float) $awd_days;
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

                                            $total_no_of_days += $days_in_month;
                                            $total_awd += $awd_days;
                                            $total_paid_days += $paid_days;
                                            $total_lop_days += $lop_days;

                                            $total = 0;
                                            $grd_total = 0;
                                            ?>
                                            <tr>
                                                <td style="text-transform: capitalize;">
                                                    <span data-toggle="popover" class="detail_popover" data-original-title="" title=""><a href="<?php echo base_url() ?>admin/staff/profile/<?php echo $value['staff_id']; ?>"><?php echo $value['name'] . " " . $value['surname']." (".$value['employee_id'].")"; ?></a></span>
                                                   
                                                </td>
                                                <td>
        <?php echo $this->lang->line(strtolower($value['month'])) . " - " . $value['year']; ?>
                                                </td>
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
                                                <td>

                                                    <span data-toggle="popover" class="detail_popover" data-original-title="" title=""><a href="#"><?php echo $value['id']; ?></a></span>
                                                    <div class="fee_detail_popover" style="display: none"><?php echo $this->lang->line('mode'); ?>: <?php
                                                        if (array_key_exists($value["payment_mode"], $payment_mode)) {
                                                            echo $payment_mode[$value["payment_mode"]];
                                                        }
                                                        ?></div>

                                                </td>
                                                <td class="text text-right">
        <?php if($value['basic'] > 0){ echo amountFormat($value['basic']); } ?>
                                                </td>

                                                <td class="text text-right">
                                                    <?php if($value['total_allowance'] > 0){ echo amountFormat($value['total_allowance']); } ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php
                                                    $gross = $value['basic'] + $value['total_allowance'];
                                                    if($gross > 0){
                                                        echo amountFormat($gross);
                                                        $grossTotal += $gross;
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text text-right"><?php echo $days_in_month > 0 ? rtrim(rtrim(number_format($days_in_month, 2, '.', ''), '0'), '.') : '-'; ?></td>
                                                <td class="text text-right"><?php echo $awd_days > 0 ? rtrim(rtrim(number_format($awd_days, 2, '.', ''), '0'), '.') : '0'; ?></td>
                                                <td class="text text-right"><?php echo $paid_days > 0 ? rtrim(rtrim(number_format($paid_days, 2, '.', ''), '0'), '.') : '0'; ?></td>
                                                <td class="text text-right"><?php echo $lop_days > 0 ? rtrim(rtrim(number_format($lop_days, 2, '.', ''), '0'), '.') : '0'; ?></td>
                                                <td class="text text-right">
                                                    <?php echo (!empty($value['leave_deduction']) && $value['leave_deduction'] > 0) ? amountFormat($value['leave_deduction']) : '-'; ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php echo (!empty($value['epf_wage']) && $value['epf_wage'] > 0) ? amountFormat($value['epf_wage']) : '-'; ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php echo (!empty($value['employee_epf']) && $value['employee_epf'] > 0) ? amountFormat($value['employee_epf']) : '-'; ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php echo (!empty($value['esi_wage']) && $value['esi_wage'] > 0) ? amountFormat($value['esi_wage']) : '-'; ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php echo (!empty($value['esi_deduction']) && $value['esi_deduction'] > 0) ? amountFormat($value['esi_deduction']) : '-'; ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php
                                                    if ($value['tax']) {
                                                        $total_tax = amountFormat($value['tax']);
                                                    } else {
                                                        $total_tax = 0;
                                                    }

                                                    echo $total_tax;
                                                    ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php
                                                            // show total including leave deduction and statutory charges
                                                    $total_deduction = $value['total_deduction']
                                                        + (!empty($value['leave_deduction']) ? $value['leave_deduction'] : 0)
                                                        + (!empty($value['employee_epf']) ? $value['employee_epf'] : 0)
                                                        + (!empty($value['esi_deduction']) ? $value['esi_deduction'] : 0)
                                                        + (!empty($value['tax']) ? $value['tax'] : 0);
                                                    if($total_deduction > 0){ echo amountFormat($total_deduction); }
                                                    ?>
                                                </td>
                                                <td class="text text-right">
                                                    <?php
                                                    // Use net_salary from database (paybill)
                                                    $net_amount = $value['net_salary'];
                                                    
                                                    if($net_amount > 0){
                                                        echo amountFormat($net_amount);
                                                    }else{
                                                        echo 0;
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                            $count++;
                                        }
                                        ?>
                                </tbody>
                                <tfoot>
									<tr class="box box-solid total-bg">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="text-right"><?php echo $this->lang->line('grand_total'); ?> </td>
                                        <td class="text text-right"><?php if($basic > 0){ echo $currency_symbol . amountFormat($basic); } ?></td>
                                        <td class="text text-right"><?php if($earnings > 0){ echo $currency_symbol . amountFormat($earnings); } ?></td>
                                        <td class="text text-right"><?php if($grossTotal > 0){ echo $currency_symbol . amountFormat($grossTotal); } ?></td>
                                        <td class="text text-right"><?php if($total_no_of_days > 0){ echo rtrim(rtrim(number_format($total_no_of_days, 2, '.', ''), '0'), '.'); } ?></td>
                                        <td class="text text-right"><?php if($total_awd > 0){ echo rtrim(rtrim(number_format($total_awd, 2, '.', ''), '0'), '.'); } ?></td>
                                        <td class="text text-right"><?php if($total_paid_days > 0){ echo rtrim(rtrim(number_format($total_paid_days, 2, '.', ''), '0'), '.'); }else{ echo '0'; } ?></td>
                                        <td class="text text-right"><?php if($total_lop_days > 0){ echo rtrim(rtrim(number_format($total_lop_days, 2, '.', ''), '0'), '.'); } ?></td>
                                        <td class="text text-right"><?php if($total_lop > 0){ echo $currency_symbol . amountFormat($total_lop); } ?></td>
                                        <td class="text text-right"><?php if($total_epf_wages > 0){ echo $currency_symbol . amountFormat($total_epf_wages); } ?></td>
                                        <td class="text text-right"><?php if($total_employee_epf > 0){ echo $currency_symbol . amountFormat($total_employee_epf); } ?></td>
                                        <td class="text text-right"><?php if($total_esi_wages > 0){ echo $currency_symbol . amountFormat($total_esi_wages); } ?></td>
                                        <td class="text text-right"><?php if($total_esi > 0){ echo $currency_symbol . amountFormat($total_esi); } ?></td>
                                        <td class="text text-right"><?php if($tax > 0){ echo $currency_symbol . amountFormat($tax); } ?></td>
                                        <td class="text text-right"><?php if($deduction > 0){ echo $currency_symbol . amountFormat($deduction); } ?></td>
                                        <td class="text text-right"><?php if($netTotal > 0){ echo $currency_symbol . amountFormat($netTotal); }  ?></td>
                                    </tr> 
                                    <?php } ?>
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
if ($search_type == 'period') {
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
        var headerMsg = schoolName + "\n" + schoolAddr + "\n" + "Payroll Report Summary" + "\n";
        $('.example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv',
                {
                    extend: 'excelHtml5',
                    title: '',
                    messageTop: headerMsg,
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        // Remove all cell styling to ensure uniform formatting
                        $('row c', sheet).attr('s', '');
                    },
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
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
                    customize: function(doc) {
                        doc.content.splice(0,0,
                            { text: schoolName, style: 'dtHeader' },
                            { text: schoolAddr, style: 'dtSubHeader' },
                            { text: 'Payroll Report Summary', style: 'dtSubHeader' },
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