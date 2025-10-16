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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($classlist as $class) { ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id') == $class['id']) echo "selected=selected" ?>><?php echo $class['class'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
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
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>

                    <?php if (isset($student_due_fee)) { ?>
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
                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                    <th class="text-right">Last Yr(CF)</th>
                                    <?php foreach ($discount_list as $discount) { ?>
                                        <th class="text-right"><?php echo $discount['name']; ?></th>
                                    <?php } ?>
                                    <th class="text-right"><?php echo $this->lang->line('total_fees'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('paid_fees'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('total') . " " . $this->lang->line('discount'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('fine'); ?></th>
                                    <th class="text-right"><?php echo $this->lang->line('balance'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total_fees = 0; $total_paid = 0; $total_fine = 0; $total_balance = 0; $total_discount = 0;
                                $total_last_yr_cf = 0;
                                $discount_totals = array_fill_keys(array_column($discount_list, 'id'), 0);

                                if (!empty($student_due_fee)) {
                                    foreach ($student_due_fee as $student) {
                                        $total_fees += $student->totalfee;
                                        $total_paid += $student->deposit;
                                        $total_fine += $student->fine;
                                        $total_balance += $student->balance;
                                        $total_last_yr_cf += $student->last_yr_cf;
                                        $total_discount += $student->discount;
                                        ?>
                                        <tr>
                                            <td><?php echo $student->name; ?></td>
                                            <td><?php echo $student->class . " (" . $student->section . ")"; ?></td>
                                            <td><?php echo $student->admission_no; ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->last_yr_cf); ?></td>
                                            <?php 
                                            foreach ($discount_list as $discount) {
                                                $discount_amount = 0;
                                                if (!empty($student->applied_discounts)) {
                                                    foreach ($student->applied_discounts as $student_discount) {
                                                        if ($student_discount['fees_discount_id'] == $discount['id']) {
                                                            $discount_amount = $student_discount['amount'];
                                                            $discount_totals[$discount['id']] += $discount_amount;
                                                            break;
                                                        }
                                                    }
                                                }
                                                echo "<td class='text-right'>" . amountFormat($discount_amount) . "</td>";
                                            }
                                            ?>
                                            <td class="text-right"><?php echo amountFormat($student->totalfee); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->deposit); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->discount); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->fine); ?></td>
                                            <td class="text-right"><?php echo amountFormat($student->balance); ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr><td colspan="<?php echo 8 + count($discount_list); ?>" class="text-center">No Record Found</td></tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                             <tfoot>
                                <tr class="box box-solid total-bg">
                                    <td></td>
                                    <td></td>
                                    <td class="text-right"><?php echo $this->lang->line('grand_total'); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_last_yr_cf); ?></td>
                                    <?php foreach ($discount_totals as $total) { ?>
                                        <td class="text-right"><?php echo $currency_symbol . amountFormat($total); ?></td>
                                    <?php } ?>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_fees); ?></td>
                                    <td class="text-right"><?php echo $currency_symbol . amountFormat($total_paid); ?></td>
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
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', 0) ?>';
        getSectionByClass(class_id, section_id);
        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').html(div_data);
                }
            });
        });
    });
    function getSectionByClass(class_id, section_id) {
        if (class_id != "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
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
                'copy', 'csv', 'excel', 'pdf', 'print'
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