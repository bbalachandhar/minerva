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
                    <form action="<?php echo site_url('customfinancereports/custombalancefeesreport') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <?php if ($sch_setting->institution_type == 'college') {?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('department'); ?></label>
                                        <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($department_list as $department) {
    ?>
                                                <option value="<?php echo $department['id'] ?>" <?php if (set_value('department_id') == $department['id']) {
        echo "selected=selected";
    }
    ?>><?php echo $department['department_name'] ?></option>
                                                <?php
}
?>
                                        </select>
                                        <span class="text-danger" id="error_department_id"></span>
                                    </div>
                                </div>
                                <?php }?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <select autofocus="" id="class_id" name="class_id[]" class="form-control select2" multiple="multiple">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($classlist as $class) { ?>
                                                <option value="<?php echo $class['id'] ?>" <?php echo set_select('class_id[]', $class['id']); ?>><?php echo $class['class'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <script>
                                    $(document).ready(function() {
                                        $('#class_id').select2();
                                    });
                                </script>
                                <style>
                                .select2-container--default .select2-selection--multiple {
                                    max-height: 100px; /* Adjust as needed */
                                    overflow-y: auto;
                                }
                                </style>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select  id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Discount Type</label>
                                        <select id="discount_type_filter" name="discount_type_filter" class="form-control">
                                            <option value="">All Discounts</option>
                                            <?php foreach ($discount_list as $discount) { ?>
                                                <option value="<?php echo $discount['id']; ?>" <?php echo set_select('discount_type_filter', $discount['id'], set_value('discount_type_filter')); ?>><?php echo $discount['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('discount_type_filter'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>

                    <?php
                    if (!isset($fee_type_columns)) $fee_type_columns = [];
                    if (isset($student_due_fee)) { ?>
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-users"></i> Custom Balance Fees Report</h3>
                    </div>
                    <div class="box-body">
                        <div class="dataTables_scrollBody" style="position: relative; overflow: auto; width: 100%; max-height: 300px;">
                            <table class="table table-striped table-hover headerTable" id="headerTable">
                            <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('student_name'); ?></th>
                                    <th><?php echo $this->lang->line('class'); ?></th>
                                    <th><?php echo $this->lang->line('category'); ?></th>
                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                    <th class="text-right">CF-Demand</th>
                                    <th class="text-right">CF-Paid</th>
                                    <th class="text-right">CF-Balance</th>
                                                                         <?php foreach ($discount_list as $discount) { ?>
                                                                            <th class="text-right"><?php echo $discount['name']; ?></th>
                                                                        <?php } ?>                                    <?php foreach ($fee_type_columns as $ft_id => $ft_name): ?>
                                    <th class="text-right"><?php echo htmlspecialchars($ft_name); ?> (Demand)</th>
                                    <th class="text-right"><?php echo htmlspecialchars($ft_name); ?> (Paid)</th>
                                    <th class="text-right"><?php echo htmlspecialchars($ft_name); ?> (Balance)</th>
                                    <?php endforeach; ?>
                                    <th class="text-right">Transport Fees (Demand)</th>
                                    <th class="text-right">Transport Fees (Paid)</th>
                                    <th class="text-right">Transport Fees (Balance)</th>
                                    <th class="text-right"><?php echo $this->lang->line('total_fees'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('paid_fees'); ?></th>
                                    <th class="text-right">Advance Payments</th>
                                    <th class="text-right"><?php echo $this->lang->line('total') . " " . $this->lang->line('discount'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('fine'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('balance'); ?></th>
                                    <th class="text-right">Net Balance(Balance-CF-Balance)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_fees = 0; $total_paid = 0; $total_fine = 0; $total_balance = 0; $total_discount = 0; $total_advance_paid = 0;
                                $total_last_yr_cf = 0; $total_cf_paid = 0; $total_cf_balance = 0;
                                $total_transport_demand = 0; $total_transport_paid = 0; $total_transport_balance = 0;
                                $total_net_balance = 0;
                                $ft_totals = []; // [feetype_id => ['demand'=>0,'paid'=>0]]
                                $discount_totals = array_fill_keys(array_column($discount_list, 'id'), 0);

                                if (!empty($student_due_fee)) {
                                    foreach ($student_due_fee as $student) {
                                        $total_fees += $student->totalfee;
                                        $total_paid += $student->deposit;
                                        $total_fine += $student->fine;
                                        $total_balance += $student->balance;
                                        $total_last_yr_cf += $student->last_yr_cf;
                                        $total_cf_paid += $student->cf_paid;
                                        $total_cf_balance += $student->cf_balance;
                                        $total_advance_paid += $student->advance_paid;
                                        $total_transport_demand += $student->transport_demand;
                                        $total_transport_paid  += $student->transport_paid;
                                        $total_transport_balance += $student->transport_balance;
                                        $total_discount += $student->discount;
                                        $total_net_balance += $student->net_balance;
                                        foreach ($student->fee_types as $tid => $ft) {
                                            if (!isset($ft_totals[$tid])) $ft_totals[$tid] = ['demand' => 0, 'paid' => 0];
                                            $ft_totals[$tid]['demand'] += $ft['demand'];
                                            $ft_totals[$tid]['paid']   += $ft['paid'];
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $student->name; ?></td>
                                            <td><?php echo $student->class . " (" . $student->section . ")"; ?></td>
                                            <td><?php echo $student->category; ?></td>
                                            <td><?php echo $student->admission_no; ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->last_yr_cf); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->cf_paid); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->cf_balance); ?></td>
                                                                                         <?php 
                                                                                        foreach ($discount_list as $discount) {
                                                                                            $discount_amount = 0;
                                                                                            if (!empty($student->applied_discounts)) {
                                                                                                foreach ($student->applied_discounts as $student_discount) {
                                                                                                    if ($student_discount['fees_discount_id'] == $discount['id']) {
                                                                                                        if (isset($student_discount['custom_amount']) && $student_discount['custom_amount'] != null) {
                                                                                                            $discount_amount = $student_discount['custom_amount'];
                                                                                                        } else {
                                                                                                            $discount_amount = $student_discount['amount'];
                                                                                                        }
                                                                                                        $discount_totals[$discount['id']] += $discount_amount;
                                                                                                        break;
                                                                                                    }
                                                                                                }
                                                                                            }
                                                                                            echo "<td class='text-right'>" . amountFormat($discount_amount) . "</td>";
                                                                                        }
                                                                                        ?>
                                            <?php foreach ($fee_type_columns as $ft_id => $ft_name):
                                                $ft_d = isset($student->fee_types[$ft_id]) ? $student->fee_types[$ft_id]['demand'] : 0;
                                                $ft_p = isset($student->fee_types[$ft_id]) ? $student->fee_types[$ft_id]['paid']   : 0;
                                            ?>
                                            <td class="text-right"><?php echo amountFormat($ft_d); ?></td>
                                            <td class="text-right"><?php echo amountFormat($ft_p); ?></td>
                                            <td class="text-right"><?php echo amountFormat($ft_d - $ft_p); ?></td>
                                            <?php endforeach; ?>
                                            <td class="text-right"><?php echo amountFormat($student->transport_demand); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->transport_paid); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->transport_balance); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->totalfee); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->deposit); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->advance_paid + $student->advance_discount); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->discount); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->fine); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->balance); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->balance - $student->cf_balance); ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr><td colspan="<?php echo 16 + (count($fee_type_columns) * 3) + count($discount_list); ?>" class="text-center">No Record Found</td></tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                             <tfoot>
                                <tr class="box box-solid total-bg">
                                    <th colspan="4" class="text-right"><?php echo $this->lang->line('grand_total'); ?></th>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_last_yr_cf); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_cf_paid); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_cf_balance); ?></td>
                                    <?php foreach ($discount_totals as $total) { ?>
                                        <td class="text-right"><?php echo $currency_symbol . amountFormat($total); ?></td>
                                    <?php } ?>
                                    <?php foreach ($fee_type_columns as $ft_id => $ft_name):
                                        $td = isset($ft_totals[$ft_id]) ? $ft_totals[$ft_id]['demand'] : 0;
                                        $tp = isset($ft_totals[$ft_id]) ? $ft_totals[$ft_id]['paid']   : 0;
                                    ?>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($td); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($tp); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($td - $tp); ?></td>
                                    <?php endforeach; ?>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_transport_demand); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_transport_paid); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_transport_balance); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_fees); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_paid); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_advance_paid); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_discount); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_fine); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_balance); ?></td>
                                    <td class="text-right">
                                        <?php 
                                        $total_net_balance_cf = 0;
                                        if (!empty($student_due_fee)) {
                                            foreach ($student_due_fee as $student) {
                                                $total_net_balance_cf += ($student->balance - $student->cf_balance);
                                            }
                                        }
                                        echo $currency_symbol . amountFormat($total_net_balance_cf); 
                                        ?>
                                    </td>
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
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', 0) ?>';
        var department_id = $('#department_id').val(); // Get current department_id on ready
        getSectionByClass(class_id, section_id, department_id); // Pass department_id

        $(document).on('change', '#department_id', function (e) {
            $('#class_id').html(''); // Clear all options
            $('#class_id').select2('val', ''); // Clear selected classes in multiselect
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Clear section dropdown

            var department_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            
            $.ajax({
                type: "POST",
                url: base_url + "customfinancereports/get_classes_by_department",
                data: {'department_id': department_id},
                dataType: "json",
                success: function (data) {
                    $('#class_id').append('<option value=""><?php echo $this->lang->line('select_all'); ?></option>'); // Add "Select All" option
                    $.each(data, function (i, obj) {
                        $('#class_id').append("<option value=" + obj.id + ">" + obj.class + "</option>");
                    });
                    $('#class_id').select2(); // Re-initialize select2 after updating options

                    // After populating classes, ensure sections are also updated
                    var class_id_initial = $('#class_id').val();
                    var section_id_initial = '<?php echo set_value('section_id', 0) ?>';
                    getSectionByClass(class_id_initial, section_id_initial);
                }
            });
        });

        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            var department_id = $('#department_id').val(); // Get selected department_id
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id, 'department_id': department_id}, // Pass department_id
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').html(div_data);
                }
            });
        });
    });
    function getSectionByClass(class_id, section_id, department_id) { // Added department_id
        if (class_id != "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            // var department_id = $('#department_id').val(); // Removed, now passed as param
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id, 'department_id': department_id}, // Pass department_id
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').html(div_data);
                }
            });
        }
    }
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
                    footer: true,
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                // Strip HTML tags from data for excel
                                return data.replace( /(<([^>]+)>)/ig, '' );
                            },
                            footer: function ( data, row, column, node ) {
                                // Strip HTML tags from data for excel
                                return data.replace( /(<([^>]+)>)/ig, '' );
                            }
                        }
                    }
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