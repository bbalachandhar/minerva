<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
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
                    <form action="<?php echo site_url('financereports/categorywisebalancefeesreport') ?>"  method="post" accept-charset="utf-8">
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
                                                <option value="<?php echo $department['id'] ?>" <?php if (set_value('department_id', $department_id_selected) == $department['id']) echo "selected"; ?>><?php echo $department['department_name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger" id="error_department_id"></span>
                                    </div>
                                </div>
                                <?php }?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <select id="class_id" name="class_id" class="form-control" >
                                            <option value="all"><?php echo $this->lang->line('all_classes'); ?></option>
                                            <?php foreach ($classlist as $class) { ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id', $class_id_selected) == $class['id']) echo "selected"; ?>><?php echo $class['class'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control" >
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

                    <?php if (isset($category_summary)) { ?>
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-users"></i> Category Wise Balance Fees Report</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="report_table">
                            <?php if (!isset($fee_type_columns)) $fee_type_columns = []; ?>
                            <thead>
                                <tr>
                                    <th>Student Category</th>
                                    <th>Student Count</th>
                                    <?php foreach ($fee_type_columns as $ft_id => $ft_name): ?>
                                    <th class="text-right"><?php echo htmlspecialchars($ft_name); ?> Demand</th>
                                    <th class="text-right"><?php echo htmlspecialchars($ft_name); ?> Paid</th>
                                    <?php endforeach; ?>
                                    <th class="text-right">Govt FG Discounts</th>
                                    <th class="text-right">Govt 7.5 Discounts</th>
                                    <th class="text-right">Total Management Discounts</th>
                                    <th class="text-right">Total Paid</th>
                                    <th class="text-right">Pending Fee</th>
                                    <th class="text-right">Transport Fee Demand</th>
                                    <th class="text-right">Transport Fee Paid</th>
                                    <th class="text-right">Transport Fee Balance</th>
                                    <th class="text-right">7.5 Transport Subsidy</th>
                                    <th class="text-right">7.5 Hostel Subsidy</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($category_summary)) { ?>
                                    <tr><td colspan="<?php echo 12 + (count($fee_type_columns) * 2); ?>" class="text-center">No Record Found</td></tr>
                                <?php } else {
                                    $total_students = 0;
                                    // Initialize other totals...
                                    foreach ($category_summary as $category) {
                                        // Accumulate totals...
                                        ?>
                                        <tr>
                                            <td><?php echo $category->category_name; ?></td>
                                            <td><a href="#" class="student-list" data-category-id="<?php echo $category->category_id; ?>"><?php echo $category->number_of_students; ?></a></td>
                                            <?php foreach ($fee_type_columns as $ft_id => $ft_name):
                                                $ft_d = isset($category->fee_types[$ft_id]) ? $category->fee_types[$ft_id]['demand'] : 0;
                                                $ft_p = isset($category->fee_types[$ft_id]) ? $category->fee_types[$ft_id]['paid']   : 0;
                                            ?>
                                            <td class="text-right"><?php echo amountFormat($ft_d); ?></td>
                                            <td class="text-right"><?php echo amountFormat($ft_p); ?></td>
                                            <?php endforeach; ?>
                                            <td class="text-right"><?php echo amountFormat($category->govt_fg_discounts); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->govt_7_5_discounts); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->total_management_discounts); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->total_paid); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->pending_fee); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->transport_fee_demand); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->transport_fee_paid); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->transport_fee_balance); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->transport_subsidy_7_5); ?></td>
                                            <td class="text-right"><?php echo amountFormat($category->hostel_subsidy_7_5); ?></td>
                                        </tr>
                                    <?php }
                                } ?>
                            </tbody>
                             <tfoot>
                                    <tr class="total-bg">
                                        <th>Grand Total</th>
                                        <th><?php echo array_sum(array_column($category_summary, 'number_of_students')); ?></th>
                                        <?php foreach ($fee_type_columns as $ft_id => $ft_name):
                                            $gt_d = $gt_p = 0;
                                            foreach ($category_summary as $cat) {
                                                $gt_d += isset($cat->fee_types[$ft_id]) ? $cat->fee_types[$ft_id]['demand'] : 0;
                                                $gt_p += isset($cat->fee_types[$ft_id]) ? $cat->fee_types[$ft_id]['paid']   : 0;
                                            }
                                        ?>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat($gt_d); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat($gt_p); ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'govt_fg_discounts'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'govt_7_5_discounts'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'total_management_discounts'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'total_paid'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'pending_fee'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'transport_fee_demand'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'transport_fee_paid'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'transport_fee_balance'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'transport_subsidy_7_5'))); ?></th>
                                        <th class="text-right"><?php echo $currency_symbol . amountFormat(array_sum(array_column($category_summary, 'hostel_subsidy_7_5'))); ?></th>
                                    </tr>
                                </tfoot>
                        </table>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Student List Modal -->
<div class="modal fade" id="studentModal" tabindex="-1" role="dialog" aria-labelledby="studentModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="studentModalLabel">Students List</h4>
            </div>
            <div class="modal-body">
                <table class="table table-striped" id="student_table">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th class="text-right">Demand</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody id="student_list_body">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url(); ?>backend/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/datatables/js/buttons.colVis.min.js"></script>
<script>
$(document).ready(function() {
    var department_id_selected = '<?php echo set_value('department_id', $department_id_selected); ?>';
    var class_id_selected = '<?php echo set_value('class_id', $class_id_selected); ?>';
    var section_id_selected = '<?php echo set_value('section_id', $section_id_selected); ?>';

    // Initialize dropdowns on page load
    if (department_id_selected !== "") {
        getClassesByDepartment(department_id_selected, class_id_selected);
    }
    if (class_id_selected !== "") {
        getSectionByClass(class_id_selected, section_id_selected, department_id_selected);
    }

    $(document).on('change', '#department_id', function (e) {
        $('#class_id').html('<option value="all"><?php echo $this->lang->line('all_classes'); ?></option>');
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Clear section
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

    $(document).on('change', '#class_id', function (e) {
        $('#section_id').html("");
        var class_id = $(this).val();
        var department_id = $('#department_id').val();
        var base_url = '<?php echo base_url() ?>';
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        $.ajax({
            type: "GET",
            url: base_url + "sections/getByClass",
            data: {'class_id': class_id, 'department_id': department_id},
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj) {
                    div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                });
                $('#section_id').append(div_data);
            }
        });
    });

    $('.student-list').on('click', function(e) {
        e.preventDefault();
        var category_id = $(this).data('category-id');
        var class_id = $('#class_id').val();
        var department_id = $('#department_id').val();
        var section_id = $('#section_id').val();

        $('#studentModal').modal('show');
        $('#student_list_body').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
        
        $.ajax({
            url: '<?php echo site_url("financereports/get_students_by_category") ?>',
            type: 'POST',
            data: {
                category_id: category_id,
                class_id: class_id,
                department_id: department_id,
                section_id: section_id
            },
            dataType: 'json',
            success: function(response) {
                var table_body = '';
                if(response.length > 0) {
                    $.each(response, function(index, student) {
                        table_body += '<tr>';
                        table_body += '<td>' + student.admission_no + '</td>';
                        table_body += '<td>' + student.name + '</td>';
                        table_body += '<td>' + student.class + '</td>';
                        table_body += '<td>' + student.section + '</td>';
                        table_body += '<td class="text-right">' + parseFloat(student.demand).toFixed(2) + '</td>';
                        table_body += '<td class="text-right">' + parseFloat(student.paid).toFixed(2) + '</td>';
                        table_body += '<td class="text-right">' + parseFloat(student.balance).toFixed(2) + '</td>';
                        table_body += '</tr>';
                    });
                } else {
                    table_body = '<tr><td colspan="7" class="text-center">No students found for this category.</td></tr>';
                }
                $('#student_list_body').html(table_body);
            },
            error: function() {
                $('#student_list_body').html('<tr><td colspan="7" class="text-center">Error fetching student data.</td></tr>');
            }
        });
    });
    
    $('#report_table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});

function getClassesByDepartment(department_id, class_id) {
    if (department_id !== "") {
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

function getSectionByClass(class_id, section_id, department_id) {
    if (class_id != "") {
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>'); // Add "Select" option explicitly
        var base_url = '<?php echo base_url() ?>';
        var div_data = ''; // Initialize div_data here
        $.ajax({
            type: "GET",
            url: base_url + "sections/getByClass",
            data: {'class_id': class_id, 'department_id': department_id},
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj) {
                    var sel = (section_id == obj.section_id) ? "selected" : "";
                    div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                });
                $('#section_id').append(div_data);
            }
        });
    }
}
</script>