<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('salary_abstract_report'); ?></h1>
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
                    <form role="form" action="<?php echo site_url('financereports/salaryabstract') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-3" >
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_type'); ?></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                        <?php foreach ($searchlist as $key => $search) { ?>
                                            <option value="<?php echo $key ?>" <?php if ((isset($search_type)) && ($search_type == $key)) echo 'selected'; ?>><?php echo $search ?></option>
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
                                        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                                        foreach ($months as $month) {
                                            $selected = (isset($filter_month) && $filter_month == $month) ? 'selected' : '';
                                            echo '<option value="'.$month.'" '.$selected.'>'.$this->lang->line(strtolower($month)).'</option>';
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
                                            $sel = (isset($filter_year) && $filter_year == $y) ? 'selected' : '';
                                            echo '<option value="'.$y.'" '.$sel.'>'.$y.'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div id='date_result'></div>
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
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('salary_abstract_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <?php if (empty($abstractList)) { ?>
                                <div class="alert alert-info" style="margin:10px 0;">
                                    No payroll records found for selected criteria.
                                    <?php if (!empty($start_date) && !empty($end_date) && $start_date !== '1900-01-01') { ?>
                                        <br>Query range: <?php echo $start_date; ?> to <?php echo $end_date; ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div class="download_label"><?php echo $this->lang->line('salary_abstract_report').' '. $this->customlib->get_postmessage(); ?></div>
                            <?php
                            // compute summary values so they can be placed in datatable
                            $bank_credit = 0;
                            $total_epf = 0;
                            $total_esi = 0;
                            $total_prof_tax = 0; // unused
                            $total_income_tax = 0;
                            $total_gross = 0;
                            if(!empty($abstractList)){
                                foreach($abstractList as $r){
                                    $bank_credit += floatval($r['net_salary'] ?? 0);
                                    $total_epf += floatval($r['employee_epf'] ?? 0) + floatval($r['employer_pf'] ?? 0);
                                    $total_esi += floatval($r['employee_esi'] ?? 0) + floatval($r['employer_esi'] ?? 0);
                                    $total_income_tax += floatval($r['tax'] ?? 0);
                                    $gross = floatval($r['basic'] ?? 0) + floatval($r['total_allowance'] ?? 0);
                                    $total_gross += $gross;
                                }
                            }
                            ?>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr><th>Description</th><th>Amount</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>BANK CREDIT / Salary</td><td><?php echo amountFormat($bank_credit) ?: '0.00';?></td></tr>
                                    <tr><td>EPF EMPLOYER & EMPLOYEE CONTRIBUTION</td><td><?php echo amountFormat($total_epf) ?: '0.00';?></td></tr>
                                    <tr><td>ESIC EMPLOYER & EMPLOYEE CONTRIBUTION</td><td><?php echo amountFormat($total_esi) ?: '0.00';?></td></tr>
                                    <tr><td>Professional Tax</td><td></td></tr>
                                    <tr><td>Income Tax / TDS</td><td><?php echo amountFormat($total_income_tax) ?: '0.00';?></td></tr>
                                    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                                </tbody>
                                <tfoot>
                                    <?php
                                    // compute grand total (exclude gross salary to avoid double-counting)
                                    $grand_total = 0;
                                    $grand_total += floatval($bank_credit);
                                    $grand_total += floatval($total_epf);
                                    $grand_total += floatval($total_esi);
                                    $grand_total += floatval($total_income_tax);
                                    // note: do NOT add $total_gross here
                                    ?>
                                    <tr><td><strong>Gross Salary</strong></td><td><strong><?php echo amountFormat($grand_total) ?: '0.00';?></strong></td></tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div> <!-- end .row around filters and table -->
        <!-- signatory row -->
        <div class="row" style="margin-top:30px;">
            <div class="col-md-2 text-center">ACCOUNTS</div>
            <div class="col-md-2 text-center">ADMIN OFFICER</div>
            <div class="col-md-2 text-center">PRINCIPAL</div>
            <div class="col-md-2 text-center">EXECUTIVE DIRECTOR</div>
            <div class="col-md-2 text-center">MANAGING TRUSTEE</div>
        </div>
    </section>
</div>

<script>
<?php if (isset($search_type) && $search_type == 'period') { ?>
    $(document).ready(function(){ showdate('period'); });
<?php } ?>
</script>

<script type="text/javascript">
    $(document).ready(function () {
        var schoolName = "<?php echo addslashes($this->sch_setting_detail->name);?>";
        var schoolAddr = "<?php echo addslashes($this->sch_setting_detail->address);?>";
        var headerMsg = schoolName + "\n" + schoolAddr + "\n" + "Salary Abstract Report" + "\n";
        $('.example').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy','csv',
                {
                    extend:'excelHtml5', title:'', messageTop:headerMsg,
                    customize:function(xlsx){
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row c[r^="A1"]',sheet).attr('s','22');
                    },
                    exportOptions:{format:{body:function(d){return d.replace(/(<([^>]+)>)/ig,'');},footer:function(d){return d.replace(/(<([^>]+)>)/ig,'');}}},footer:true
                },
                {
                    extend:'pdfHtml5', title:'', customize:function(doc){
                        doc.content.splice(0,0,
                            {text:schoolName,style:'dtHeader'},
                            {text:schoolAddr,style:'dtSubHeader'},
                            {text:'Salary Abstract Report',style:'dtSubHeader'},
                            {text:'\n'}
                        );
                        doc.styles.dtHeader={fontSize:16,bold:true,alignment:'center'};
                        doc.styles.dtSubHeader={fontSize:11,alignment:'center'};
                    },footer:true
                },
                'print'
            ]
        });
    });
</script>