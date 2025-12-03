<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/datatables/extensions/FixedHeader/css/dataTables.fixedHeader.css">
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?></h1>
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
                    <form action="<?php echo site_url('financereports/balancesummaryreport') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <?php if ($sch_setting->institution_type == 'college') {?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('department'); ?></label>
                                        <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($department_list as $department) { ?>
                                                <option value="<?php echo $department['id'] ?>" <?php if ($department_id_selected == $department['id']) echo "selected"; ?>><?php echo $department['department_name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger" id="error_department_id"></span>
                                    </div>
                                </div>
                                <?php }?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                            <option value="all"><?php echo $this->lang->line('all_classes'); ?></option>
                                            <?php foreach ($classlist as $class) { ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if ($class_id_selected == $class['id']) echo "selected"; ?>><?php echo $class['class'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('search_type'); ?></label>
                                        <select  id="search_type" name="search_type" class="form-control" >
                                            <?php foreach ($payment_type as $payment_key => $payment_value) { ?>
                                             <option value="<?php echo $payment_key; ?>" <?php echo set_select('search_type', $payment_key, set_value('search_type')); ?>><?php echo $payment_value; ?></option>
                                            <?php } ?>                                        
                                       </select>
                                        <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>

                    <?php if (isset($student_due_fee)) { ?>
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-users"></i> Balance Summary Report</h3>
                    </div>
                    <div class="box-body">
                        <div class="dataTables_scrollBody" style="position: relative; overflow: auto; width: 100%; max-height: 300px;">
                            <table class="table table-striped table-hover headerTable" id="headerTable">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('class_name'); ?></th>
                                    <th class="text-right">CF-Demand</th>
                                    <th class="text-right">CF-Paid</th>
                                    <th class="text-right">CF-Balance</th>
                                    <?php foreach ($discount_list as $discount) { ?>
                                        <th class="text-right"><?php echo $discount['name']; ?></th>
                                    <?php } ?>
                                    <th class="text-right">Tuition Fee (Demand)</th>
                                    <th class="text-right">Tuition Fee (Balance)</th>
                                    <th class="text-right">Other Fees (Demand)</th>
                                    <th class="text-right">Other Fees (Balance)</th>
                                    <th class="text-right">Hostel Fees (Demand)</th>
                                    <th class="text-right">Hostel Fees (Balance)</th>
                                    <th class="text-right">Transport Fees (Demand)</th>
                                    <th class="text-right">Transport Fees (Balance)</th>

                                    <th class="text-right"><?php echo $this->lang->line('total_fees'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('paid_fees'); ?></th>
                                    <th class="text-right">Advance Payments</th>
                                    <th class="text-right"><?php echo $this->lang->line('total') . " " . $this->lang->line('discount'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('fine'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('balance'); ?></th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_fees = 0; $total_paid = 0; $total_fine = 0; $total_balance = 0; $total_discount = 0; $total_advance_paid = 0;
                                $total_last_yr_cf = 0; $total_cf_paid = 0; $total_cf_balance = 0;
                                $total_tuition_demand = 0; $total_tuition_paid = 0; $total_tuition_balance = 0;
                                $total_other_demand = 0; $total_other_paid = 0; $total_other_balance = 0;
                                $total_hostel_demand = 0; $total_hostel_paid = 0; $total_hostel_balance = 0;
                                $total_transport_demand = 0; $total_transport_paid = 0; $total_transport_balance = 0;
                                $total_actual_balance = 0; // Added // Commented out by Gemini
                                $discount_totals = array_fill_keys(array_column($discount_list, 'id'), 0);

                                if (!empty($student_due_fee)) {
                                    foreach ($student_due_fee as $student) {
                                        $total_fees += $student->totalfee;
                                        $total_paid += $student->deposit;
                                        $total_fine += $student->fine;
                                        $total_balance += $student->balance;
                                        $total_last_yr_cf += $student->last_yr_cf;
                                        $total_cf_paid += $student->cf_paid; // Accumulate CF-Paid
                                        $total_cf_balance += $student->cf_balance; // Accumulate CF-Balance
                                        $total_advance_paid += $student->advance_paid; /* Accumulate advance paid */
                                        $total_tuition_demand += $student->tuition_demand;
                                        $total_tuition_paid += $student->tuition_paid;
                                        $total_tuition_balance += $student->tuition_balance;
                                        $total_other_demand += $student->other_demand;
                                        $total_other_paid += $student->other_paid;
                                        $total_other_balance += $student->other_balance;
                                        $total_hostel_demand += $student->hostel_demand;
                                        $total_hostel_paid += $student->hostel_paid;
                                        $total_hostel_balance += $student->hostel_balance;
                                        $total_transport_demand += $student->transport_demand;
                                        $total_transport_paid += $student->transport_paid;
                                        $total_transport_balance += $student->transport_balance;
                                        // $total_actual_balance += $student->actual_balance;     // Added
                                        $total_discount += $student->discount;
                                        ?>
                                        <tr>
                                            <td><?php echo $student->class_name; ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->last_yr_cf); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->cf_paid); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->cf_balance); ?></td>
                                            <?php 
                                            foreach ($discount_list as $discount) {
                                                $prop_name = 'discount_' . $discount['id'];
                                                $discount_amount_student = $student->$prop_name;
                                                echo "<td class='text-right'>" . amountFormat($discount_amount_student) . "</td>";
                                            }
                                            ?>
                                            <td class="text-right"><?php echo amountFormat($student->tuition_demand); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->tuition_balance); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->other_demand); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->other_balance); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->hostel_demand); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->hostel_balance); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->transport_demand); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->transport_balance); ?></td>

                                            <td class="text-right"><?php echo amountFormat($student->totalfee); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->deposit); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->advance_paid); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->discount); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->fine); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->balance); ?></td>

                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr><td colspan="<?php echo 9 + count($discount_list); ?>" class="text-center">No Record Found</td></tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                             <tfoot>
                                <tr class="box box-solid total-bg">
                                    <th><?php echo $this->lang->line('grand_total'); ?></th>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_last_yr_cf); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_cf_paid); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_cf_balance); ?></td>
                                    <?php foreach ($discount_list as $discount) {
                                        $total_for_discount = $discount_totals_footer[$discount['id']];
                                        ?>
                                        <td class="text-right"><?php echo $currency_symbol . amountFormat($total_for_discount); ?></td>
                                    <?php } ?>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_tuition_demand); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_tuition_balance); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_other_demand); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_other_balance); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_hostel_demand); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_hostel_balance); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_transport_demand); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_transport_balance); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_fees); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_paid); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_advance_paid); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_discount); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_fine); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_balance); ?></td>

                                </tr>
                            </tfoot>
                        </table>
                        </div>
                    </div>
                    <?php } ?>
                </div> <!-- /.box -->
            </div> <!-- /.col-md-12 -->
        </div> <!-- /.row -->
    </section> <!-- /.content -->
</div> <!-- /.content-wrapper -->
<script src="<?php echo base_url(); ?>backend/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/datatables/extensions/FixedHeader/js/dataTables.fixedHeader.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.colVis.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/vfs_fonts.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var department_id = $('#department_id').val();
        var class_id = '<?php echo $class_id_selected; ?>';
        
        if(department_id !== ""){
            getClassesByDepartment(department_id, class_id);
        }

        $(document).on('change', '#department_id', function (e) {
            $('#class_id').html('<option value="all"><?php echo $this->lang->line('all_classes'); ?></option>');
            var department_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            if (department_id != "") {
                $.ajax({
                    type: "POST",
                    url: base_url + "report/getClassesByDepartment",
                    data: {'department_id': department_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var sel = "";
                            $('#class_id').append("<option value=" + obj.id + " " + sel + ">" + obj.class + "</option>");
                        });
                    }
                });
            }
        });

        function getClassesByDepartment(department_id, class_id) {
            if (department_id != "") {
                $('#class_id').html('<option value="all"><?php echo $this->lang->line('all_classes'); ?></option>');
                var base_url = '<?php echo base_url() ?>';
                $.ajax({
                    type: "POST",
                    url: base_url + "report/getClassesByDepartment",
                    data: {'department_id': department_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var sel = (class_id == obj.id) ? "selected" : "";
                            $('#class_id').append("<option value=" + obj.id + " " + sel + ">" + obj.class + "</option>");
                        });
                    }
                });
            }
        }
    });
</script>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#headerTable')) {
            $('#headerTable').DataTable().destroy();
        }
        $('#headerTable').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv',
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                // Strip HTML tags from data
                                return data.replace( /(<([^>]+)>)/ig, '' );
                            },
                            footer: function ( data, row, column, node ) {
                                // Strip HTML tags from data
                                return data.replace( /(<([^>]+)>)/ig, '' );
                            }
                        }
                    },
                    footer: true
                },
                'pdf', 'print'
            ]
        });
    });
</script>

<style>
    #headerTable thead {
        position: sticky;
        top: 0;
        z-index: 10; /* Ensure header is above scrolling content */
        background-color: #f4f4f4; /* Or your table header background color */
    }

    #headerTable tfoot {
        position: sticky;
        bottom: 0;
        z-index: 10; /* Ensure footer is above scrolling content */
        background-color: #f4f4f4; /* Or your table footer background color */
    }
</style>