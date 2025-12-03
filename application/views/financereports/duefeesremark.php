<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 1126px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php //echo $this->lang->line('fees_collection'); ?> <small> <?php //echo $this->lang->line('filter_by_name1'); ?></small></h1>
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
                    <form action="<?php echo site_url('financereports/duefeesremark') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <?php if ($sch_setting->institution_type == 'college') {?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('department'); ?></label>
                                        <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
foreach ($department_list as $department) {
    ?>
                                                <option value="<?php echo $department['id'] ?>" <?php if (set_value('department_id', $department_id_selected) == $department['id']) {
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
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($classlist as $class) {
                                                ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id', $class_id_selected) == $class['id']) echo "selected=selected" ?>><?php echo $class['class'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('section'); ?></label>
                                        <select  id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>

                    <?php
if (!empty($student_remain_fees)) {
    ?>
                        <div class="box-header ptbnull"></div>
                        <div class="box-header with-border">
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student_due_fees'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('student_due_fees'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('class_section'); ?></th>
                                        <th><?php echo $this->lang->line('admission_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <?php if ($sch_setting->father_name) { ?>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
                                        <?php } ?>
                                        <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <th><?php echo $this->lang->line('category'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('fees_code'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('due_date'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text-right"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text-right"><?php echo $this->lang->line('deposit'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text-right"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        <th class="text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_amount          = 0;
                                    $total_deposite_amount = 0;
                                    $total_discount_amount = 0;
                                    $total_fine_amount     = 0;
                                    $total_balance_amount  = 0;

                                    foreach ($student_remain_fees as $student_key => $student) {
                                        foreach ($student['fees'] as $fee_key => $fee_value) {
                                            $total_amount          = $total_amount + $fee_value['amount'];
                                            $total_deposite_amount = $total_deposite_amount + $fee_value['amount_deposite'];
                                            $total_discount_amount = $total_discount_amount + $fee_value['amount_discount'];
                                            $total_fine_amount     = $total_fine_amount + $fee_value['amount_fine'];
                                            $total_balance_amount  = $total_balance_amount + $fee_value['amount'] - ($fee_value['amount_deposite'] + $fee_value['amount_discount']);
                                            ?>
                                            <tr>
                                                <td><?php echo $student['class'] . "(" . $student['section'] . ")"; ?></td>
                                                <td><?php echo $student['admission_no']; ?></td>
                                                <td><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></td>
                                                <?php if ($sch_setting->father_name) { ?>
                                                    <td><?php echo $student['father_name']; ?></td>
                                                <?php } ?>
                                                <td><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob'])); ?></td>
                                                <td><?php echo $student['gender']; ?></td>
                                                <td><?php echo $student['category']; ?></td>
                                                <td class="text-right"><?php echo $fee_value['fee_type']; ?></td>
                                                <td class="text-right"><?php echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($start_month)); ?></td>
                                                <td class="text-right">
                                                    <?php
                                                    $display_none = "ss-none";
                                                    if ($fee_value['amount'] - ($fee_value['amount_deposite'] + $fee_value['amount_discount']) == 0) {
                                                        ?>
                                                        <span class="label label-success"><?php echo $this->lang->line('paid'); ?></span>
                                                        <?php
                                                    } else if (!empty($fee_value['amount_deposite'])) {
                                                        ?><span class="label label-warning"><?php echo $this->lang->line('partial'); ?></span><?php
                                                    } else {
                                                        ?><span class="label label-danger"><?php echo $this->lang->line('unpaid'); ?></span><?php
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-right"><?php echo amountFormat($fee_value['amount']); ?></td>
                                                <td class="text-right"><?php echo amountFormat($fee_value['amount_deposite']); ?></td>
                                                <td class="text-right"><?php echo amountFormat($fee_value['amount_discount']); ?></td>
                                                <td class="text-right"><?php echo amountFormat($fee_value['amount_fine']); ?></td>
                                                <td class="text-right"><?php echo amountFormat($fee_value['amount'] - ($fee_value['amount_deposite'] + $fee_value['amount_discount'])); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <tr class="total-bg">
                                        <td colspan="<?php if ($sch_setting->father_name) {echo "10";} else {echo "9";} ?>"></td>
                                        <td class="text-right"><?php echo $this->lang->line('grand_total'); ?></td>
                                        <td class="text-right"><?php echo ($currency_symbol . amountFormat($total_amount)); ?></td>
                                        <td class="text-right"><?php echo ($currency_symbol . amountFormat($total_deposite_amount)); ?></td>
                                        <td class="text-right"><?php echo ($currency_symbol . amountFormat($total_discount_amount)); ?></td>
                                        <td class="text-right"><?php echo ($currency_symbol . amountFormat($total_fine_amount)); ?></td>
                                        <td class="text-right"><?php echo ($currency_symbol . amountFormat($total_balance_amount)); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php
} else {
    ?>
                            <div class="col-md-12">
                                <div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div>
                            </div>
                            <?php
}
?>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    function getSectionByClass(class_id, section_id, department_id) {
        if (class_id !== "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id, 'department_id': department_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        }
    }

    $(document).ready(function () {
        var department_id_selected = '<?php echo set_value('department_id', $department_id_selected); ?>';
        var class_id_selected = '<?php echo set_value('class_id', $class_id_selected); ?>';
        var section_id_selected = '<?php echo set_value('section_id', $section_id_selected); ?>';

        if (department_id_selected !== "") {
            getClassesByDepartment(department_id_selected, class_id_selected);
        }
        if (class_id_selected !== "") {
            getSectionByClass(class_id_selected, section_id_selected, department_id_selected);
        }

        $(document).on('change', '#department_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
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
                            $('#class_id').append("<option value=" + obj.id + ">" + obj.class + "</option>");
                        });
                    }
                });
            }
        });

        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var class_id = $(this).val();
            var department_id = $('#department_id').val();
            var base_url = '<?php echo base_url() ?>';
            if (class_id != "") {
                $.ajax({
                    type: "GET",
                    url: base_url + "sections/getByClass",
                    data: {'class_id': class_id, 'department_id': department_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            $('#section_id').append("<option value=" + obj.section_id + ">" + obj.section + "</option>");
                        });
                    }
                });
            }
        });
    });

    function getClassesByDepartment(department_id, class_id) {
        if (department_id !== "") {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
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

    // getSectionByClass function defined globally for use in other parts of the script
    function getSectionByClass(class_id, section_id, department_id) {
        if (class_id !== "") {
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var base_url = '<?php echo base_url() ?>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id, 'department_id': department_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var sel = (section_id == obj.section_id) ? "selected" : "";
                        $('#section_id').append("<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>");
                    });
                }
            });
        }
    }
</script>
<script>
    document.getElementById("print").style.display = "block";
    document.getElementById("btnExport").style.display = "block";

    function printDiv() {
        document.getElementById("print").style.display = "none";
        document.getElementById("btnExport").style.display = "none";
        var divElements = document.getElementById('transfee').innerHTML;
        var oldPage = document.body.innerHTML;
        document.body.innerHTML =
                "<html><head><title></title></head><body>" +
                divElements + "</body>";
        window.print();
        document.body.innerHTML = oldPage;

        location.reload(true);
    }
</script>
