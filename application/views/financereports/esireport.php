<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> ESI Report</h1>
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

                    <form role="form" action="<?php echo site_url('financereports/esireport') ?>" method="post" class="">
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-6 col-md-3" >
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('search_type'); ?></label>
                                    <select class="form-control" name="search_type" onchange="showdate(this.value)">
                                        <?php foreach ($searchlist as $key => $search) { ?>
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
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('esi_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('esi_report').' '. $this->customlib->get_postmessage(); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                    <thead>
                        <tr>
                            <th><?php echo $this->lang->line('name'); ?></th>
                            <th><?php echo $this->lang->line('employee_id'); ?></th>
                            <th><?php echo $this->lang->line('working_days'); ?></th>
                            <th><?php echo $this->lang->line('lop_days'); ?></th>
                            <th><?php echo $this->lang->line('net_lop'); ?></th>
                            <th class="text text-right"><?php echo $this->lang->line('lop_amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                            <th class="text text-right">ESI (Employee) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                            <th class="text text-right">ESI (Employer) <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_working = 0;
                        $total_lop_days = 0;
                        $total_net_lop = 0;
                        $total_lop_amt = 0;
                        $emp_esi_total = 0;
                        $empr_esi_total = 0;
                        if (!empty($esilist)) {
                            foreach ($esilist as $value) {
                                $working = $value['working_days'] ?? '';
                                $lop_days = $value['lop_days'] ?? '';
                                $netlop = $value['net_lop_days'] ?? '';
                                $lop_amt = $value['lop_amount'] ?? 0;
                                $total_working += is_numeric($working) ? $working : 0;
                                $total_lop_days += is_numeric($lop_days) ? $lop_days : 0;
                                $total_net_lop += is_numeric($netlop) ? $netlop : 0;
                                $total_lop_amt += is_numeric($lop_amt) ? $lop_amt : 0;
                                $emp_esi_total += !empty($value['employee_esi']) ? $value['employee_esi'] : 0;
                                $empr_esi_total += !empty($value['employer_esi']) ? $value['employer_esi'] : 0;
                                $gross = $value['basic'] + $value['total_allowance'];
                                ?>
                                <tr>
                                    <td><?php echo $value['name'] . ' ' . $value['surname']; ?></td>
                                    <td><?php echo $value['employee_id']; ?></td>
                                    <td><?php echo $working; ?></td>
                                    <td><?php echo $lop_days; ?></td>
                                    <td><?php echo $netlop; ?></td>
                                    <td class="text text-right"><?php if($lop_amt>0){ echo amountFormat($lop_amt);} ?></td>
                                    <td class="text text-right"><?php echo (!empty($value['employee_esi']) ? amountFormat($value['employee_esi']) : '-'); ?></td>
                                    <td class="text text-right"><?php echo (!empty($value['employer_esi']) ? amountFormat($value['employer_esi']) : '-'); ?></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="box box-solid total-bg">
                            <td></td>
                            <td class="text-right"><?php echo $this->lang->line('grand_total'); ?></td>
                            <td class="text text-right"><?php if($total_working > 0){ echo $total_working; } ?></td>
                            <td class="text text-right"><?php if(isset($total_lop_days) && $total_lop_days > 0){ echo $total_lop_days; } ?></td>
                            <td class="text text-right"><?php if(isset($total_net_lop) && $total_net_lop > 0){ echo $total_net_lop; } ?></td>
                            <td class="text text-right"><?php if(isset($total_lop_amt) && $total_lop_amt > 0){ echo $currency_symbol . amountFormat($total_lop_amt); } ?></td>
                            <td class="text text-right"><?php if($emp_esi_total > 0){ echo $currency_symbol . amountFormat($emp_esi_total); } ?></td>
                            <td class="text text-right"><?php if($empr_esi_total > 0){ echo $currency_symbol . amountFormat($empr_esi_total); } ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var schoolName = "<?php echo addslashes($this->sch_setting_detail->name);?>";
        var schoolAddr = "<?php echo addslashes($this->sch_setting_detail->address);?>";
        var headerMsg = schoolName + "\n" + schoolAddr + "\n" + "ESI Report" + "\n";
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
                        $('row c[r^="A1"]', sheet).attr('s', '22');
                    },
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                return data.replace( /(<([^>]+)>)/ig, '' );
                            },
                            footer: function ( data, row, column, node ) {
                                return data.replace( /(<([^>]+)>)/ig, '' );
                            }
                        }
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
                            { text: 'ESI Report', style: 'dtSubHeader' },
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