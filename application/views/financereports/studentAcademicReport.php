<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
 
<div class="content-wrapper">
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
                    <form action="<?php echo site_url('financereports/studentacademicreport') ?>"  method="post" accept-charset="utf-8">
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('search_type'); ?></label>
                                        <select  id="search_type" name="search_type" class="form-control" >
                                            <?php 
                                            foreach ($payment_type as $payment_key => $payment_value) {
                                            ?>
                                             <option value="<?php echo $payment_key; ?>" <?php echo set_select('search_type', $payment_key, set_value('search_type')); ?>><?php echo $payment_value; ?></option>
                                            <?php
                                            }
                                             ?>                                        
                                       </select>
                                        <span class="text-danger"><?php echo form_error('search_type'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">

                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>   </div>
                    </form>


                    <div class="row">

                        <?php
                        if (isset($student_due_fee)) {
                            ?>

                            <div class="" id="transfee">
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('balance_fees_report'); ?></h3>
                                </div>                              
                                <div class="box-body table-responsive">
                                    <div class="download_label"><?php
                            echo $this->lang->line('balance_fees_report') . "<br>";
                            $this->customlib->get_postmessage();
                            ?></div> 
                                    <table class="table table-striped table-bordered table-hover example" id="headerTable">
                                        <thead>
                                            <tr>
                                                
                                                <th class="text text-left"><?php echo $this->lang->line('student_name'); ?></th>
                                                <th class="text text-left"><?php echo $this->lang->line('class'); ?></th>

                                                <th class="text text-left"><?php echo $this->lang->line('mobile_no'); ?></th>
                                                <th class="text text-left"><?php echo $this->lang->line('admission_no'); ?></th>
                                                <?php if ($sch_setting->roll_no) { ?>
                                                    <th class="text text-left"><?php echo $this->lang->line('roll_number'); ?></th>
                                                <?php } if ($sch_setting->father_name) { ?>
                                                    <th class="text text-left"><?php echo $this->lang->line('father_name'); ?></th>
                                                <?php } ?>
                                                <th class="text-right" width="9%"><?php echo $this->lang->line('total_fees'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                <th class="text-right" width="8%"><?php echo $this->lang->line('paid_fees'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>

                                                <th class="text text-right" width="8%"><?php echo $this->lang->line('discount'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                                <th class="text text-right"><?php echo $this->lang->line('fine'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>

                                                <th class="text-right" width="8%"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                            </tr>
                                        </thead>  
                                        <tbody> 
                                            <?php
                                            if (!empty($student_due_fee)) {
                                                        $totalfeelabel = 0;
                                                        $depositfeelabel = 0;
                                                        $discountlabel = 0;
                                                        $finelabel = 0;
                                                        $balancelabel = 0;                                       
                                                foreach ($student_due_fee as $students) {                                                   
                                                     
                                                                                               
                                                            $totalfeelabel += $students->totalfee;
                                                            $depositfeelabel += $students->deposit;
                                                            $discountlabel += $students->discount;
                                                            $finelabel += $students->fine;
                                                            $balancelabel += $students->balance;
                                                                    ?>                                            
                                                      <tr>
                                                            <td><?php echo $students->name;?></td>
                                                            <td><?php echo $students->class." (".$students->section.")";?></td>
                                                            <td><?php echo $students->mobileno;?></td>
                                                            <td><?php echo $students->admission_no;?></td>
                                                            <?php if ($sch_setting->roll_no) { ?>
                                                            <td><?php echo $students->roll_no;?></td>
                                                            <?php } if ($sch_setting->father_name) { ?>
                                                            <td><?php echo $students->father_name;?></td>
                                                            <?php } ?>

                                                            <td class="text-right"><?php echo amountFormat($students->totalfee);?></td>

                                                            <td class="text-right"><?php echo amountFormat($students->deposit);?></td>

                                                            <td class="text-right"><?php echo amountFormat($students->discount);?></td>

                                                            <td class="text-right"><?php echo amountFormat($students->fine);?></td>

                                                            <td class="text-right"><?php echo amountFormat($students->balance);?></td>
                                                            </tr>
                                                                <?php
                                                        
                                                              ?>
                                                              
                                                        <?php                                
                                                          } ?>
                                                          <tr class="box box-solid total-bg">
                                                                
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>

                                                                <?php if ($sch_setting->roll_no) { ?>
                                                                    <td></td>
                                                                    <?php }

                                                                     if ($sch_setting->father_name) {
                                                                      ?>
                                                                                                                    <td></td>
                                                                    <?php 
                                                                     } 
                                                                ?>
                                                                <td class="text-right"><?php echo $this->lang->line('grand_total'); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($totalfeelabel);  ?></td>
                                                                <td class="text-right"><?php echo amountFormat($depositfeelabel); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($discountlabel); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($finelabel); ?></td>
                                                                <td class="text-right"><?php echo amountFormat($balancelabel); ?></td>
                                                            </tr>
                                                            <?php } ?>
                                            </tbody> 
                                        </table>
                                    </div>                            
                                </div>  
 <?php
                        
                    }else{ 
?>
                            <div class="col-md-12" ><div class="col-md-12" ><div class="alert alert-info"><?php echo $this->lang->line('no_record_found'); ?></div></div></div>
                            
                    <?php } ?>                                
                            </div>

                           


                </div>
            </div>
    </section>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        var department_id = '<?php echo set_value('department_id', $department_id_selected) ?>';
        var class_id = '<?php echo set_value('class_id', $class_id_selected) ?>';
        var section_id = '<?php echo set_value('section_id', $section_id_selected) ?>';

        if(department_id !== ""){
            getClassesByDepartment(department_id, class_id);
        }
        
        getSectionByClass(class_id, section_id);

        $(document).on('change', '#department_id', function (e) {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
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
                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').html(div_data);
                }
            });
        });
    });

    function getClassesByDepartment(department_id, class_id) {
        if (department_id != "") {
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
    $('.example').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ]
    });
});
</script>
