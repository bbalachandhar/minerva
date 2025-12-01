<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$month_list= $this->customlib->getMonthDropdown($start_month);
?> 
<div class="content-wrapper">
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
                                        <label><?php echo $this->lang->line('department'); ?></label>
                                        <select autofocus="" id="department_id" name="department_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($department_list as $department) { ?>
                                                <option value="<?php echo $department['id'] ?>" <?php if (isset($department_id_selected) && $department_id_selected == $department['id']) echo "selected"; ?>><?php echo $department['department_name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger" id="error_department_id"></span>
                                    </div>
                                </div>
                                <?php }?>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                        <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($classlist as $class) { ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (isset($class_id_selected) && $class_id_selected == $class['id']) echo "selected" ?>><?php echo $class['class'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                        <select  id="section_id" name="section_id" class="form-control" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($section_list as $value) {
                                                ?>
                                                <option  <?php
                                                if ($value['section_id'] == $section_id) {
                                                    echo "selected";
                                                }
                                                ?> value="<?php echo $value['section_id']; ?>"><?php echo $value['section']; ?></option>
                                                    <?php
                                                }
                                                ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="resp">                                
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search') ?></button>   </div>
                    </form>
                    <div class="row">
                        <?php
                        if (isset($student_remain_fees)) {
                            ?>
                            <div class="" id="transfee">
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('balance_fees_report_with_remark'); ?> </h3>
                                </div>                              
                                <div class="box-body">
                                    <?php
                                    if (!empty($student_remain_fees)) {
                                        ?>
                                        
                                    <button type="button" class="btn btn-primary btn-sm pull-right print" id="load" data-class-id="<?php echo $class_id;?>"  data-section-id="<?php echo $section_id;?>" data-loading-text="<i class='fa fa-spinner fa-spin '></i> Please wait"><i class="fa fa-print"></i> <?php echo $this->lang->line('print') ?> </button>
                    <div class="clearfix"></div>

      <div class="table-responsive">
                                                   <table class="table table-striped table-bordered table-hover ">
                                    <thead>
                                        <tr>

                                          
                                            <th><?php echo $this->lang->line('student_name')."<br/>". "(".$this->lang->line('admission_no').")"; ?></th>
                                            <th><?php echo $this->lang->line('class'); ?></th>                                
                                            <th width="30%"><?php echo $this->lang->line('fees'); ?></th>                     
                                            <th class="text text-right"><?php echo $this->lang->line('amount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th class="text text-right"><?php echo $this->lang->line('paid'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>         

                                            <th class="text text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            <th ><?php echo $this->lang->line('guardian_phone'); ?></th>
                                          <th class="text text-right"><?php echo $this->lang->line('remark'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($student_remain_fees)) {
                                            ?>

                                            <?php 
                                        } else {
                                            $count = 1;
                                            foreach ($student_remain_fees as $student) {
                                                
                    $amount=0;
                    $amount_deposite=0;
                    $amount_discount=0;
                    $amount_fine=0;

                                                if(!empty($student['fees'])){
                                                           foreach ($student['fees'] as $fee_key => $fee_value) {
                                                          
                                                             $amount+=$fee_value['amount'];
                                                             $amount_deposite+=$fee_value['amount_deposite'];
                                                             $amount_discount+=$fee_value['amount_discount'];
                                                             $amount_fine+=$fee_value['amount_fine'];
                                                            }                                                        
                                                        }                                              
                                                ?>
                                                <tr>
                                                    <td><?php echo $this->customlib->getFullName($student['firstname'],$student['middlename'],$student['lastname'],$sch_setting->middlename,$sch_setting->lastname) ."<br/>"."(".$student['admission_no'].")";?></td>                                         
                                                    <td><?php echo $student['class']."-".$student['section']; ?></td>                             
                                                    <td>
                                                        <?php   
                                                        if(!empty($student['fees'])){


                                                        echo implode(', <br/>', array_map(
                                                         function ($v) {
                                                           
                                                           return ($v['is_system']) ? $this->lang->line($v['fee_group']) . ' (' . $this->lang->line($v['fee_type']) . ')' :$v['fee_group'] . ' (' . $v['fee_type'] . ' : ' . $v['fee_code'] . ')';
                                                                      },
                                                             $student['fees']));
                                                        }                                                       
                                                       
                                                    ?>
                                                    </td>
                                                    <td class="text text-right"><?php echo amountFormat($amount); ?></td>
                                                    <td class="text text-right"><?php echo amountFormat($amount_deposite+$amount_discount); ?></td>                                                    
                                                    <td class="text text-right"><?php
                                            echo amountFormat(($amount - ($amount_deposite + $amount_discount)));
                                                ?></td> 
                                                  <td ><?php
                                            echo $student['guardian_phone'];
                                                ?></td>
                                                  <td class="text text-right">
                                                      <div style="height: 100px; overflow:hidden;">
   
  </div>
                                                  </td>
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
                                     
                                    } else {
                                        ?>
                                        <div class="alert alert-info">
                                           <?php echo $this->lang->line('no_record_found') ; ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>                            
                            </div>                 
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var department_id = $('#department_id').val();
        var class_id = '<?php echo isset($class_id_selected) ? $class_id_selected : 0; ?>';
        var section_id = '<?php echo isset($section_id) ? $section_id : 0; ?>';

        if(department_id !== ""){
            getClassesByDepartment(department_id, class_id);
        }

        if(class_id !== 0){
            getSectionByClass(class_id, section_id);
        }

        $(document).on('change', '#department_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            var department_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            if (department_id != "") {
                $.ajax({
                    type: "POST",
                    url: base_url + "financereports/get_classes_by_department",
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
            getSectionByClass(class_id, 0);
        });

        function getClassesByDepartment(department_id, class_id) {
            if (department_id != "") {
                $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                var base_url = '<?php echo base_url() ?>';
                $.ajax({
                    type: "POST",
                    url: base_url + "financereports/get_classes_by_department",
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

    function getSectionByClass(class_id, section_id) {
        if (class_id != "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            var department_id = $('#department_id').val();
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id, 'department_id': department_id},
                dataType: "json",
                beforeSend: function () {
                    $('#section_id').addClass('dropdownloading');
                },
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
                },
                complete: function () {
                    $('#section_id').removeClass('dropdownloading');
                }
            });
        }
    }
</script>