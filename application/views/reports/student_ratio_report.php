<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style type="text/css">
    /*REQUIRED*/
    .carousel-row {
        margin-bottom: 10px;
    }
    .slide-row {
        padding: 0;
        background-color: #ffffff;
        min-height: 150px;
        border: 1px solid #e7e7e7;
        overflow: hidden;
        height: auto;
        position: relative;
    }
    .slide-carousel {
        width: 20%;
        float: left;
        display: inline-block;
    }
    .slide-carousel .carousel-indicators {
        margin-bottom: 0;
        bottom: 0;
        background: rgba(0, 0, 0, .5);
    }
    .slide-carousel .carousel-indicators li {
        border-radius: 0;
        width: 20px;
        height: 6px;
    }
    .slide-carousel .carousel-indicators .active {
        margin: 1px;
    }
    .slide-content {
        position: absolute;
        top: 0;
        left: 20%;
        display: block;
        float: left;
        width: 80%;
        max-height: 76%;
        padding: 1.5% 2% 2% 2%;
        overflow-y: auto;
    }
    .slide-content h4 {
        margin-bottom: 3px;
        margin-top: 0;
    }
    .slide-footer {
        position: absolute;
        bottom: 0;
        left: 20%;
        width: 78%;
        height: 20%;
        margin: 1%;
    }
    /* Scrollbars */
    .slide-content::-webkit-scrollbar {
        width: 5px;
    }
    .slide-content::-webkit-scrollbar-thumb:vertical {
        margin: 5px;
        background-color: #999;
        -webkit-border-radius: 5px;
    }
    .slide-content::-webkit-scrollbar-button:start:decrement,
    .slide-content::-webkit-scrollbar-button:end:increment {
        height: 5px;
        display: block;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-bus"></i> <?php //echo $this->lang->line('transport'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <?php $this->load->view('reports/_studentinformation'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form role="form" action="<?php echo site_url('report/boys_girls_ratio') ?>" method="post" class="" id="class_search_form"  >
                        <div class="box-body row">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <?php if ($sch_setting->institution_type == 'college') {?>
                            <div class="col-sm-6 col-md-3">
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
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                    <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($classlist as $class) {
                                            ?>
                                            <option value="<?php echo $class['id'] ?>" <?php
                                            if (set_value('class_id') == $class['id']) {
                                                echo "selected =selected";
                                            }
                                            ?>><?php echo $class['class'] ?></option>
                                                    <?php
                                                    $count++;
                                                }
                                                ?>
                                    </select>
                                    <span class="text-danger" id="error_class_id"><?php echo form_error('class_id'); ?></span>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('section'); ?></label><small class="req"> *</small>
                                    <select  id="section_id" name="section_id" class="form-control" >
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger" id="error_section_id"><?php echo form_error('section_id'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php if (isset($result)) {?>
                    <div class="">
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-money"></i> <?php echo $this->lang->line('student_gender_ratio_report'); ?></h3>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"> <?php echo $this->lang->line('student_gender_ratio_report'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('class_section'); ?></th>
                                        <th><?php echo $this->lang->line('total_boys'); ?></th>
                                        <th><?php echo $this->lang->line('total_girls'); ?></th>
                                        <th><?php echo $this->lang->line('total_students'); ?></th>
                                        <th><?php echo $this->lang->line('boys_girls_ratio'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $total_boys = $total_girls = $total_students = 0;
                                    if(!empty($result)){
                                    foreach ($result as $key => $value) {
                                        ?>
                                        <tr>
                                            <td><?php echo $value['class'] . " (" . $value['section'] . ")"; ?></td>
                                            <td><?php echo $value['male']; ?></td>
                                            <td><?php echo $value['female']; ?></td>
                                            <td><?php echo $value['total_student']; ?></td>
                                            <td><?php echo $value['boys_girls_ratio'] ?></td>
                                        </tr>
                                        <?php
                                        $total_boys += $value['male'];
                                        $total_girls += $value['female'];
                                        $total_students += $value['total_student'];
                                    }
                                    }
                                    ?>                                     
                                </tbody>
                                <?php if(!empty($result)){ ?>
                                    <tr><td> </td><td><b><?php echo $total_boys; ?></b></td><td><b><?php echo $total_girls; ?></b></td><td><b><?php echo $total_students; ?></b></td><td><b><?php echo $all_boys_girls_ratio; ?></b></td></tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                    <?php }?>
                </div>
            </div>
        </div>   
    </section>
</div>
<script>
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
        var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
        var url = "";
        var department_id = $('#department_id').val(); // Get selected department_id
        $.ajax({
            type: "GET",
            url: baseurl + "sections/getByClass",
            data: {'class_id': class_id, 'department_id': department_id}, // Pass department_id
            dataType: "json",
            success: function (data) {
                $.each(data, function (i, obj)
                {
                    div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                });
                $('#section_id').append(div_data);
            }
        });
    });
</script>
