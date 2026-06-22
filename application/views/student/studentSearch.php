<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper mn-search-page">
    <section class="content-header"></section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <?php if ($this->session->flashdata('msg')) { ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="fa fa-check-circle"></i> <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                </div>
                <?php } ?>

                <!-- Search Criteria Card -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('student/searchvalidation') ?>" method="post" class="class_search_form">
                            <?php echo $this->customlib->getCSRF(); ?>

                            <!-- Row 1: Filter Search -->
                            <div class="row">
                                <?php if ($sch_setting->institution_type == 'college') { ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="department_id"><?php echo $this->lang->line('department'); ?></label>
                                        <select id="department_id" name="department_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($department_list as $department) { ?>
                                            <option value="<?php echo $department['id'] ?>"<?php
                                                if (isset($_GET['department_id']) && $_GET['department_id'] == $department['id']) {
                                                    echo "selected=selected";
                                                } else if (set_value('department_id') == $department['id']) {
                                                    echo "selected=selected";
                                                }
                                            ?>><?php echo $department['department_name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger" id="error_department_id"></span>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label> <small class="req"> *</small>
                                        <select autofocus="" id="class_id" name="class_id[]" class="form-control" multiple="multiple">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            $count = 0;
                                            foreach ($classlist as $class) {
                                            ?>
                                            <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id') == $class['id']) { echo "selected=selected"; } ?>><?php echo $class['class'] ?></option>
                                            <?php
                                                $count++;
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger" id="error_class_id"></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-block checkbox-toggle"><i class="fa fa-filter"></i> Filter Search</button>
                                    </div>
                                </div>
                            </div>

                            <hr style="margin: 5px 0 15px;">

                            <!-- Row 2: Keyword Search -->
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('search_by_keyword'); ?></label>
                                        <input type="text" name="search_text" id="search_text" class="form-control" value="<?php echo set_value('search_text'); ?>" placeholder="Search By Student Name, Roll Number, Enroll Number, National Id, Local Id Etc.">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit" name="search" value="search_full" class="btn btn-default btn-block checkbox-toggle"><i class="fa fa-search"></i> Keyword Search</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div><!-- /.box-body -->
                </div><!-- /.box box-primary -->

                <!-- Results Card -->
                <div class="box box-primary">
                    <div class="nav-tabs-custom" style="margin-bottom: 0; box-shadow: none; border: none;">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_1" data-toggle="tab"><i class="fa fa-list"></i> <?php echo $this->lang->line('list_view'); ?></a></li>
                            <li><a href="#tab_2" data-toggle="tab"><i class="fa fa-th-large"></i> <?php echo $this->lang->line('details_view'); ?></a></li>
                        </ul>
                        <div class="tab-content">
                            <!-- List View Tab -->
                            <div class="tab-pane active table-responsive" id="tab_1">
                                <table class="table table-striped table-bordered table-hover student-list" data-export-title="<?php echo $this->lang->line('student_list'); ?>">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th><?php echo $this->lang->line('student_name'); ?></th>
                                            <th><?php echo $this->lang->line('roll_no'); ?></th>
                                            <th><?php echo $this->lang->line('class'); ?></th>
                                            <?php if ($sch_setting->father_name) { ?>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
                                            <?php } ?>
                                            <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                            <th><?php echo $this->lang->line('gender'); ?></th>
                                            <?php if ($sch_setting->category) { ?>
                                            <?php if ($sch_setting->category) { ?>
                                            <th><?php echo $this->lang->line('category'); ?></th>
                                            <?php }
                                            } if ($sch_setting->mobile_no) { ?>
                                            <th><?php echo $this->lang->line('mobile_number'); ?></th>
                                            <?php }
                                            if (!empty($fields)) {
                                                foreach ($fields as $fields_key => $fields_value) { ?>
                                            <th><?php echo $fields_value->name; ?></th>
                                            <?php }
                                            } ?>
                                            <th class="text-right noExport white-space-nowrap"><?php echo $this->lang->line('action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Details View Tab -->
                            <div class="tab-pane detail_view_tab" id="tab_2">
                                <?php if (empty($resultlist)) { ?>
                                <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $this->lang->line('no_record_found'); ?></div>
                                <?php } else {
                                    $count = 1;
                                ?>
                                <div class="row">
                                    <?php foreach ($resultlist as $student) {
                                        if (empty($student["image"])) {
                                            if ($student['gender'] == 'Female') {
                                                $image = "uploads/student_images/default_female.jpg";
                                            } else {
                                                $image = "uploads/student_images/default_male.jpg";
                                            }
                                        } else {
                                            $image = $student['image'];
                                        }
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="student-card">
                                            <div class="student-card-body">
                                                <div class="row">
                                                    <div class="col-xs-4 text-center">
                                                        <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>">
                                                            <?php if ($sch_setting->student_photo) { ?>
                                                            <img class="student-card-img img-responsive img-thumbnail" alt="<?php echo $student["firstname"] . " " . $student["lastname"] ?>" src="<?php echo $this->media_storage->getImageURL($image); ?>">
                                                            <?php } ?>
                                                        </a>
                                                    </div>
                                                    <div class="col-xs-8">
                                                        <h4 style="margin-top: 0;">
                                                            <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>">
                                                                <?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?>
                                                            </a>
                                                        </h4>
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('class'); ?>:</strong> <?php echo $student['class'] . "(" . $student['section'] . ")" ?></p>
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('admission_no'); ?>:</strong> <?php echo $student['admission_no'] ?></p>
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('date_of_birth'); ?>:</strong>
                                                            <?php if ($student["dob"] != null && $student["dob"] != '0000-00-00') { echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob'])); } ?>
                                                        </p>
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('gender'); ?>:</strong> <?php echo $this->lang->line(strtolower($student['gender'])) ?></p>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-top: 8px;">
                                                    <div class="col-xs-6">
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('local_identification_no'); ?>:</strong> <?php echo $student['samagra_id'] ?></p>
                                                        <?php if ($sch_setting->guardian_name) { ?>
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('guardian_name'); ?>:</strong> <?php echo $student['guardian_name'] ?></p>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <?php if ($sch_setting->guardian_name) { ?>
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('guardian_phone'); ?>:</strong> <i class="fa fa-phone-square"></i> <?php echo $student['guardian_phone'] ?></p>
                                                        <?php } ?>
                                                        <p style="margin-bottom: 3px;"><strong><?php echo $this->lang->line('current_address'); ?>:</strong> <?php echo $student['current_address'] ?> <?php echo $student['city'] ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="student-card-actions">
                                                <a href="<?php echo base_url(); ?>student/view/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                                    <i class="fa fa-reorder"></i>
                                                </a>
                                                <?php if ($this->rbac->hasPrivilege('student', 'can_edit')) { ?>
                                                <a href="<?php echo base_url(); ?>student/edit/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <?php } ?>
                                                <?php if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_add')) { ?>
                                                <a href="<?php echo base_url(); ?>studentfee/addfee/<?php echo $student['id'] ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('add_fees'); ?>">
                                                    <?php echo $currency_symbol; ?>
                                                </a>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                        $count++;
                                    }
                                    ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div><!-- /.tab-content -->
                    </div><!-- /.nav-tabs-custom -->
                </div><!-- /.box box-primary -->

            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
$(document).ready(function () {
    // Initialize select2
    $('#class_id').select2({
        placeholder: "Select",
    });

    var class_id = $('#class_id').val();
    var section_id = '<?php echo set_value('section_id') ?>';
    var department_id = '<?php echo set_value('department_id') ?>';

    // Initial population
    getSectionByClass(class_id, section_id);
    if('<?php echo $sch_setting->institution_type; ?>' == 'college' && department_id){
        getClassesByDepartment(department_id, class_id);
    }

    // Event Listeners
    <?php if ($sch_setting->institution_type == 'college') { ?>
    $(document).on('change', '#department_id', function (e) {
        getClassesByDepartment($(this).val());
        $('#section_id').html("").select2({data: null});
        $('#section_id').append('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        $('#section_id').select2();
    });
    <?php } ?>

    $(document).on('change', '#class_id', function (e) {
        getSectionByClass($(this).val(),'');
    });

    // Functions
    function getClassesByDepartment(department_id, class_id = null) {
        if (department_id != "") {
            $('#class_id').html("").select2({data: null});
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: "<?php echo site_url('classes/getClassesByDepartment'); ?>",
                data: {'department_id': department_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (class_id == obj.id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.id + " " + sel + ">" + obj.class + "</option>";
                    });
                    $('#class_id').append(div_data);
                    $('#class_id').select2();
                },
                error: function (xhr, status, error) {
                    console.error("AJAX error:", error);
                }
            });
        } else {
            $('#class_id').html("").select2({data: null});
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            <?php
            foreach ($classlist as $class) {
                ?>
                var sel = "";
                if (class_id == '<?php echo $class['id'] ?>') {
                    sel = "selected";
                }
                div_data += "<option value='<?php echo $class['id'] ?>' " + sel + "><?php echo addslashes($class['class']) ?></option>";
                <?php
            }
            ?>
            $('#class_id').append(div_data);
            $('#class_id').select2();
        }
    }

    function getSectionByClass(class_id, section_id) {
        if (class_id != "" && class_id != null) {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            if (!Array.isArray(class_id)) {
                class_id = [class_id];
            }
            class_id.forEach(function(class_id) {
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
        }
    }

    // Form submission logic
    $("form.class_search_form button[type=submit]").click(function() {
        $("button[type=submit]", $(this).parents("form")).removeAttr("clicked");
        $(this).attr("clicked", "true");
    });

    var last_search_type = '';

    $(document).on('submit','.class_search_form',function(e){
       e.preventDefault();
        var $this = $("button[type=submit][clicked=true]");
        last_search_type = $this.attr('value');
        var form = $(this);
        var url = form.attr('action');
        var form_data = form.serializeArray();
        form_data.push({name: 'srch_type', value: $this.attr('value')});
        $.ajax({
               url: url,
               type: "POST",
               dataType:'JSON',
               data: form_data,
                  beforeSend: function () {
                    $('[id^=error]').html("");
                    $this.button('loading');
                    resetFields($this.attr('value'));
                   },
                  success: function(response) {
                    if(!response.status){
                        $.each(response.error, function(key, value) {
                        $('#error_' + key).html(value);
                        });
                    }else{
                        if ($.fn.DataTable.isDataTable('.student-list')) {
                             $('.student-list').DataTable().destroy();
                        }
                        var table = $('.student-list').DataTable({
                           dom: 'Bfrtip',
                              buttons: [
                                { extend: 'copy', text: '<i class="fa fa-files-o"></i>', titleAttr: 'Copy', className: "btn-copy", title: $('.student-list').data("exportTitle"), exportOptions: { columns: ["thead th:not(.noExport)"] } },
                                { extend: 'excel', text: '<i class="fa fa-file-excel-o"></i>', titleAttr: 'Excel', className: "btn-excel", title: $('.student-list').data("exportTitle"), exportOptions: { columns: ["thead th:not(.noExport)"] } },
                                { extend: 'csv', text: '<i class="fa fa-file-text-o"></i>', titleAttr: 'CSV', className: "btn-csv", title: $('.student-list').data("exportTitle"), exportOptions: { columns: ["thead th:not(.noExport)"] } },
                                { extend: 'pdf', text: '<i class="fa fa-file-pdf-o"></i>', titleAttr: 'PDF', className: "btn-pdf", title: $('.student-list').data("exportTitle"), exportOptions: { columns: ["thead th:not(.noExport)"] } },
                                { extend: 'print', text: '<i class="fa fa-print"></i>', titleAttr: 'Print', className: "btn-print", title: $('.student-list').data("exportTitle"), customize: function ( win ) { $(win.document.body).find('th').addClass('display').css('text-align', 'center'); $(win.document.body).find('table').addClass('display').css('font-size', '14px'); $(win.document.body).find('h1').css('text-align', 'center'); }, exportOptions: { columns: ["thead th:not(.noExport)"] } }
                            ],
                            'initComplete': function() {
                                var $button = $('<a class="btn btn-default dt-button buttons-csv buttons-html5 btn-csv" tabindex="0" aria-controls="DataTables_Table_0" href="#" title="CSV"><span><i class="fa fa-file-text-o"></i> Export All to CSV</span></a>');
                                $('.dt-buttons').append($button);

                                $button.on('click', function() {
                                    var form = $('form.class_search_form');
                                    var class_id = $('#class_id').val();
                                    var section_id = $('#section_id').val();
                                    var search_text = $('#search_text').val();
                                    var export_url = '<?php echo site_url("student/exportall") ?>' + '?search_type=' + last_search_type + '&class_id=' + class_id.join(',') + '&section_id=' + section_id + '&search_text=' + search_text;
                                    window.open(export_url, '_blank');
                                });
                            },
                            "columnDefs": [ { "targets": -1, "orderable": false } ],
                            "language": { processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span> '},
                            "pageLength": 100,
                            "processing": true,
                            "serverSide": true,
                            "ajax":{
                                "url": "<?php echo site_url('student/dtstudentlist')?>",
                                "dataSrc": 'data',
                                "type": "POST",
                                'data': response.params,
                             },
                             "drawCallback": function(settings) {
                                $('.detail_view_tab').html("").html(settings.json.student_detail_view);
                            }
                        });
                    }
                  },
                 error: function() {
                     $this.button('reset');
                 },
                 complete: function() {
                     $this.button('reset');
                 }
             });
    });

    function resetFields(search_type){
        if(search_type == "search_full"){
            $('#class_id').prop('selectedIndex',0);
            $('#section_id').find('option').not(':first').remove();
        }else if (search_type == "search_filter") {
             $('#search_text').val("");
        }
    }

    $(document).on('click', '.print_student_details', function() {
        let $button_ = $(this);
        var student_id = $(this).attr('data-student_id');
        var admission_no = $(this).attr('data-admission_no');
        var student_name = $(this).attr('data-student_name');
        $.ajax({
            type: 'POST',
            url: "<?php echo site_url('student/printStudentDetails')?>",
            data: {'student_id':student_id},
            beforeSend: function() {
                $button_.button('loading');
            },
            xhr: function() {
                var xhr = new XMLHttpRequest();
                xhr.responseType = 'blob';
                return xhr;
            },
            success: function(data, jqXHR, response) {
                var blob = new Blob([data], {type: 'application/pdf'});
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = student_name + '_' + admission_no + '.pdf';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                $button_.button('reset');
            },
            error: function(xhr, status, error) {
                console.error("Error occurred:", status, error);
                $button_.button('reset');
            },
            complete: function() {
                $button_.button('reset');
            }
        });
    });

    emptyDatatable('student-list','data');
});
</script>
