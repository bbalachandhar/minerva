<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">   
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?>  </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?> - <?php echo $feediscountList['name']; ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('admin/feediscount/assign/' . $id) ?>" method="post" class="row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('fees_discount'); ?></label>
                                    <select autofocus="" id="feediscount_id" name="feediscount_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($allFeediscountList as $feediscount) {
                                            ?>
                                            <option value="<?php echo $feediscount['id'] ?>" <?php if (set_value('feediscount_id', $id) == $feediscount['id']) echo "selected=selected" ?>><?php echo $feediscount['name'] ?> (<?php echo $feediscount['code'] ?>)</option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('feediscount_id'); ?></span>
                                </div>
                            </div><!--./col-sm-3-->
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label>
                                    <select autofocus="" id="class_id" name="class_id[]" class="form-control" multiple="multiple" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($classlist as $class) {
                                            ?>
                                            <option value="<?php echo $class['id'] ?>" <?php echo in_array($class['id'], set_value('class_id', array())) ? "selected=selected" : "" ?>><?php echo $class['class'] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                </div>
                            </div><!--./col-sm-3-->  
                            <div class="col-sm-3">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('section'); ?></label>
                                    <select  id="section_id" name="section_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                </div>  
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('hostel'); ?></label>
                                    <select id="hostel_id" name="hostel_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($hostel_list as $hostel) { ?>
                                            <option value="<?php echo $hostel['id'] ?>" <?php echo set_select('hostel_id', $hostel['id']); ?>><?php echo $hostel['hostel_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('category'); ?></label>
                                    <select  id="category_id" name="category_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($categorylist as $category) {
                                            ?>
                                            <option value="<?php echo $category['id'] ?>" <?php if (set_value('category_id') == $category['id']) echo "selected=selected"; ?>><?php echo $category['category'] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>  
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('gender'); ?></label>
                                    <select class="form-control" name="gender">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($genderList as $key => $value) {
                                            ?>
                                            <option value="<?php echo $key; ?>" <?php if (set_value('gender') == $key) echo "selected"; ?>><?php echo $value; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>  
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">  
                                    <label><?php echo $this->lang->line('rte'); ?></label>
                                    <select  id="rte" name="rte" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($RTEstatusList as $k => $rte) {
                                            ?>
                                            <option value="<?php echo $k; ?>" <?php if (set_value('rte') == $k) echo "selected"; ?>><?php echo $rte; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>  
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>Transport Student</label>
                                    <select class="form-control" name="transport_student">
                                        <option value=""><?php echo $this->lang->line('all'); ?></option>
                                        <option value="yes" <?php if (isset($transport_student) && $transport_student == 'yes') echo "selected"; ?>>Yes</option>
                                        <option value="no" <?php if (isset($transport_student) && $transport_student == 'no') echo "selected"; ?>>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                     <label class="hidden-xs">&nbsp;</label>
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <form method="post" action="<?php echo site_url('admin/feediscount/studentdiscount') ?>" id="assign_form">

                        <?php
                        if (isset($resultlist)) {
                            ?>
                            <div class="box-header ptbnull"></div>  
                            <div class="">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><i class="fa fa-users"></i> <?php echo $this->lang->line('assign_fees_discount'); ?>
                                        </i> <?php echo form_error('student'); ?></h3>
                                    <div class="box-tools pull-right">
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="">
                                            <div class="col-md-4">
                                                <div class="table-responsive">
                                                    <h4>
                                                        <?php echo $this->lang->line('fees_discount'); ?>
                                                    </h4>
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                    <th><?php echo $this->lang->line('discount_code'); ?></th>
                                        <th class="text-right"><?php   echo  ($feediscountList['type'] == "fix") ? $this->lang->line('amount') : $this->lang->line('percentage'); ?></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="mailbox-name">
                                                        <input type="hidden" name="feediscount_id"value="<?php echo $feediscountList['id']; ?>">
                                                        <td>
                                                            <?php echo $feediscountList['code']; ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php 
                                                           
                                                           echo  ($feediscountList['type'] == "fix") ? $currency_symbol . amountFormat($feediscountList['amount']) : $feediscountList['percentage']."%";
                                                            
                                                             ?>
                                                        </td>
                                                        </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class=" table-responsive">
                                                    <table class="table table-striped">
                                                        <tbody>
                                                            <tr>
                                                                <th><input type="checkbox" id="select_all"/> <?php echo $this->lang->line('all'); ?></th>
                                                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                                                <th><?php echo $this->lang->line('student_name'); ?></th>
                                                                <th><?php echo $this->lang->line('class'); ?></th>
                                                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                                                <th><?php echo $this->lang->line('category'); ?></th>
                                                                  <th><?php echo $this->lang->line('gender'); ?></th>
                                                                <?php if ($feediscountList['amount'] == '0.00') { ?>
                                                                <th><?php echo $this->lang->line('custom_amount'); ?></th>
                                                                <?php } ?>
                                                            </tr>
                                                            <?php
                                                            if (empty($resultlist)) {
                                                                ?>
                                                                <tr>
                                                                    <td colspan="8" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                                                </tr>
                                                                <?php
                                                            } else {
                                                                $count = 1;
                                                                foreach ($resultlist as $student) {
                                                                    ?>
                                                                <input type="hidden" name="student_list[]" value="<?php echo $student['student_session_id'] ?>">
                                                                <tr>
                                                                    <td>
                                                                        <?php
                                                                        $sel = "";
                                                                        $custom_amount_value = "";
                                                                        if ($student['student_fees_discount_id'] != 0) {
                                                                            $sel = "checked='checked'";
                                                                            $custom_amount_value = $student['custom_amount'];
                                                                        }
                                                                        ?>
                                                                        <input class="checkbox" type="checkbox" name="student_session_id[]"  value="<?php echo $student['student_session_id']; ?>" <?php echo $sel; ?>/>
                                                                    </td>
                                                                    <td><?php echo $student['admission_no']; ?></td>
                                                                    <td><?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname); ?></td>
                                                                    <td><?php echo $student['class'] . "(" . $student['section'] . ")" ?></td>
                                                                    <td><?php echo $student['father_name']; ?></td>
                                                                    <td><?php echo $student['category']; ?></td>
                                                                    <td><?php echo $this->lang->line(strtolower($student['gender'])); ?></td>
                                                                    <?php if ($feediscountList['amount'] == '0.00') { ?>
                                                                    <td>
                                                                        <input type="number" step="0.01" class="form-control custom_amount_input" name="custom_amounts[<?php echo $student['student_session_id']; ?>]" id="custom_amount_<?php echo $student['student_session_id']; ?>" value="<?php echo set_value('custom_amounts[' . $student['student_session_id'] . ']', $custom_amount_value); ?>" <?php echo ($sel == "") ? 'disabled' : ''; ?>>
                                                                    </td>
                                                                    <?php } ?>
                                                                </tr>
                                                                <?php
                                                            }
                                                            $count++;
                                                        }
                                                        ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <?php
                                                if (!empty($resultlist)) {
                                                    ?>
                                                    <button type="submit" class="allot-fees pull-right btn btn-primary btn-sm " id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> <?php echo $this->lang->line('please_wait'); ?>"><?php echo $this->lang->line('save'); ?>
                                                    </button>
                                                <?php } ?>

                                                <br/>
                                                <br/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </form>
                </div>  
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    var allFeediscountList = <?php echo json_encode($allFeediscountList); ?>;

//select all checkboxes
    $("#select_all").change(function () {  //"select all" change 
        $(".checkbox").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
        $('.custom_amount_input').prop('disabled', !$(this).prop("checked"));
    });

//".checkbox" change 
    $('.checkbox').change(function () {
        var student_session_id = $(this).val();
        var custom_amount_input = $('#custom_amount_' + student_session_id);
        custom_amount_input.prop('disabled', !$(this).prop("checked"));

        //uncheck "select all", if one of the listed checkbox item is unchecked
        if (false == $(this).prop("checked")) { //if this item is unchecked
            $("#select_all").prop('checked', false); //change "select all" checked status to false
        }
        //check "select all" if all checkbox items are checked
        if ($('.checkbox:checked').length == $('.checkbox').length) {
            $("#select_all").prop('checked', true);
        }
    });

    function getSectionByClass(class_id, section_id) {
        if (class_id && class_id.length > 0) {
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
                    $('#section_id').append(div_data);
                }
            });
        }
    }

    $(document).ready(function () {
        $('#feediscount_id').select2();
        $('#class_id').select2({
            closeOnSelect: false
        });

        $(document).on('change', '#feediscount_id', function () {
            var selectedFeediscountId = $(this).val();
            var selectedFeediscount = allFeediscountList.find(item => item.id == selectedFeediscountId);

            if (selectedFeediscount) {
                // Update heading
                $('.box-title').html('<i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?> - ' + selectedFeediscount.name);

                // Update fee discount details table
                $('input[name="feediscount_id"]').val(selectedFeediscount.id);
                $('.mailbox-name td:eq(0)').text(selectedFeediscount.code);
                
                var amountDisplay = '';
                if (selectedFeediscount.type == "fix") {
                    amountDisplay = '<?php echo $currency_symbol; ?>' + parseFloat(selectedFeediscount.amount).toFixed(2);
                } else {
                    amountDisplay = selectedFeediscount.percentage + '% ';
                }
                $('.mailbox-name td:eq(1)').text(amountDisplay);
            }
        });

        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id') ?>';
        getSectionByClass(class_id, section_id);
        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            var section_id = '<?php echo set_value('section_id') ?>'; // Added this line
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
                    $('#section_id').append(div_data);
                }
            });
        });
    });
</script>

<div class="modal" id="confirmModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title"><?php echo $this->lang->line('confirmation') ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo $this->lang->line('are_you_sure_to_assign_fees_discount') ?></p>
            </div>
            <div class="modal-footer">
                <a href="#" data-dismiss="modal" aria-hidden="true" class="btn btn-danger btn secondary"><?php echo $this->lang->line('no') ?></a>
                <a href="#" id="delete-btn" class="btn btn-confirm confirm"><?php echo $this->lang->line('yes') ?></a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('submit', '#assign_form', function (e) {
        e.preventDefault();
        $('#confirmModal').modal({backdrop: 'static', keyboard: false});
    });

    $(document).on('click', '#delete-btn', function (e) {
        //===================
        var $this = $('.allot-fees');
        var confirm_modal = $('#confirmModal');
        $.ajax({
            type: "POST",
            dataType: 'Json',
            url: $("#assign_form").attr('action'),
            data: $("#assign_form").serialize(),
            beforeSend: function () {
                confirm_modal.addClass('modal_loading');
            },
            success: function (data)
            {
                if (data.status == "fail") {
                    var message = "";
                    $.each(data.error, function (index, value) {

                        message += value;
                    });
                    errorMsg(message);
                } else {
                    successMsg(data.message);
                }

                confirm_modal.modal('hide');
            },
            error: function (xhr) { // if error occured
                confirm_modal.removeClass('modal_loading');
            },
            complete: function () {
                confirm_modal.removeClass('modal_loading');
            },
        });
        //====================

    });
</script>