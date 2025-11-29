<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?> </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('studentfee/bulk_upload_fees') ?>" class="btn btn-info btn-sm"><i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload_entries'); ?></a>
                        </div>
                    </div>
                    <div class="box-body">
                        <form  action="<?php echo site_url('studentfee/search') ?>" method="post" class="class_search_form">
                                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="row">
                            <div class="col-md-6 col-sm-6">
                                <div class="row">
                                        <?php if ($sch_setting->institution_type == 'college') { ?>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="department_id"><?php echo $this->lang->line('department'); ?></label>
                                                <select id="department_id" name="department_id" class="form-control">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php
                                                    foreach ($department_list as $department) {
                                                        ?>
                                                        <option value="<?php echo $department['id'] ?>"<?php
                                                        if (set_value('department_id') == $department['id']) {
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
                                        <?php } ?>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('class'); ?></label><small class="req">  *</small>
                                                <select autofocus="" id="class_id" name="class_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php
                                    foreach ($classlist as $class) {
                                        ?>
                                          <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id') == $class['id']) {
                                            echo "selected=selected";
                                        }
                                        ?>><?php echo $class['class'] ?></option>
                                                                                            <?php
                                    }
                                    ?>
                                                                                    </select>
                                                 <span class="text-danger" id="error_class_id"></span>
                                            </div>

                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('section'); ?></label>
                                                <select  id="section_id" name="section_id" class="form-control" >
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                </select>
                                                <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group">

                                              

                                             <button type="submit" class="btn btn-primary btn-sm pull-right" name="class_search" data-loading-text="Please wait.." value="class_search"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>

                                            </div>
                                        </div>
                                    
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6">
                                <div class="row">
                                   
                                        <div class="col-sm-12">
                                            <div class="form-group">
                                                <label><?php echo $this->lang->line('search_by_keyword'); ?></label>
            <input type="text" name="search_text" id="search_text" class="form-control" value="<?php echo set_value('search_text'); ?>" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                                                 <span class="text-danger" id="error_search_text"></span>
                                            </div>
                                        </div>

                                        <div class="col-sm-12">
                                            <div class="form-group">
                                               <button type="submit" class="btn btn-primary btn-sm pull-right" name="keyword_search" data-loading-text="Please wait.." value="keyword_search"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                                            </div>
                                        </div>
                                  
                                </div>
                            </div>
                        </div>
                    </form>
                    </div>


                        <div class="">
                            <div class="box-header ptbnull"></div>
                            <div class="box-header ptbnull">
                                <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('list'); ?>
                                    <?php echo form_error('student'); ?></h3>
                                <div class="box-tools pull-right"></div>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    
                              
                                <table class="table table-striped table-bordered table-hover student-list" data-export-title="<?php echo $this->lang->line('student')." ".$this->lang->line('list'); ?>">
                                    <thead>

                                        <tr>
                                            <th><?php echo $this->lang->line('class'); ?></th>
                                            <th><?php echo $this->lang->line('section'); ?></th>

                                            <th><?php echo $this->lang->line('admission_no'); ?></th>

                                            <th><?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('name'); ?></th>
                                            <?php if ($sch_setting->father_name) {?>
                                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                            <?php }?>
                                            <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                            <th><?php echo $this->lang->line('mobile_no'); ?></th>
                                            <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>

                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                                  </div>
                            </div><!--./box-body-->
                        </div>
                    </div>

            </div>

        </div>

    </section>
</div>

<script>
$(document).ready(function() {
     emptyDatatable('student-list','fees_data');

});
</script>
<script type="text/javascript">
    $(document).ready(function () {
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id', 0) ?>';
        var department_id = '<?php echo set_value('department_id', 0) ?>';

        getSectionByClass(class_id, section_id);
        
        if('<?php echo $sch_setting->institution_type; ?>' == 'college' && department_id){
            getClassesByDepartment(department_id, class_id);
        }

        <?php if ($sch_setting->institution_type == 'college') { ?>
        $(document).on('change', '#department_id', function (e) {
            getClassesByDepartment($(this).val(),'');
            $('#section_id').html("");
            $('#section_id').append('<option value=""><?php echo $this->lang->line('select'); ?></option>');
        });
        <?php } ?>

        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            getSectionByClass(class_id, 0);
        });

        function getClassesByDepartment(department_id, class_id = null) {
            if (department_id != "") {
                $('#class_id').html("");
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
                    }
                });
            } else {
                $('#class_id').html("");
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
    });

    $(document).ready(function(){ 
      $("form.class_search_form button[type=submit]").click(function() {
        $("button[type=submit]", $(this).parents("form")).removeAttr("clicked");
        $(this).attr("clicked", "true");
    });


$(document).on('submit','.class_search_form',function(e){
    e.preventDefault(); // avoid to execute the actual submit of the form.
        var $this = $("button[type=submit][clicked=true]");
    var form = $(this);
    var url = '<?php echo site_url('studentfee/search') ?>';
    var form_data = form.serializeArray();
    form_data.push({name: 'search_type', value: $this.attr('value')});
    $.ajax({
           url: url,
           type: "POST",
           dataType:'JSON',
           data: form_data, // serializes the form's elements.
              beforeSend: function () {
                $('[id^=error]').html("");
                $this.button('loading');
                resetFields($this.attr('name'));
               },
 success: function(response) { // your success handler

                if(!response.status){
                    $.each(response.error, function(key, value) {
                    $('#error_' + key).html(value);
                    });
                }else{        

        if ($.fn.DataTable.isDataTable('.student-list')) { // if exist datatable it will destrory first
         $('.student-list').DataTable().destroy();
       }
        table= $('.student-list').DataTable({
        
       dom: 'Bfrtip',
          buttons: [
            {
                extend:    'copy',
                text:      '<i class="fa fa-files-o"></i>',
                titleAttr: 'Copy',
                 className: "btn-copy",
                title: $('.student-list').data("exportTitle"),
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'excel',
                text:      '<i class="fa fa-file-excel-o"></i>',
                titleAttr: 'Excel',
                     className: "btn-excel",
                title: $('.student-list').data("exportTitle"),
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'csv',
                text:      '<i class="fa fa-file-text-o"></i>',
                titleAttr: 'CSV',
                className: "btn-csv",
                title: $('.student-list').data("exportTitle"),
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  }
            },
            {
                extend:    'pdf',
                text:      '<i class="fa fa-file-pdf-o"></i>',
                titleAttr: 'PDF',
                className: "btn-pdf",
                title: $('.student-list').data("exportTitle"),
                  exportOptions: {
                    columns: ["thead th:not(.noExport)"]
                  },

            },
            {
                extend:    'print',
                text:      '<i class="fa fa-print"></i>',
                titleAttr: 'Print',
                className: "btn-print",
                title: $('.student-list').data("exportTitle"),
                customize: function ( win ) {

                    $(win.document.body).find('th').addClass('display').css('text-align', 'center');
                    $(win.document.body).find('table').addClass('display').css('font-size', '14px');     
                    $(win.document.body).find('h1').css('text-align', 'center');
                },
                exportOptions: {
                    columns: ["thead th:not(.noExport)"]

                  }

            }
        ],
      
        "columnDefs": [ {
        "targets": -1,
        "orderable": false
        } ],


           "language": {
            processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span> '},
        "pageLength": 100,
        "processing": true,
        "serverSide": true,
        "ajax":{
        "url": "<?php echo site_url('studentfee/ajaxSearch') ?>",
        "dataSrc": 'data',
        "type": "POST",
        'data': response.params,

     },"drawCallback": function(settings) {

    $('.detail_view_tab').html("").html(settings.json.student_detail_view);
}

    });
            //=======================
                }
              },
             error: function() { // your error handler
                 $this.button('reset');
             },
             complete: function() {
             $this.button('reset');
             }
         });

});

    });
    function resetFields(search_type){
        if(search_type == "keyword_search"){
            $('#class_id').prop('selectedIndex',0);
            $('#section_id').find('option').not(':first').remove();
        }else if (search_type == "class_search") {
            
             $('#search_text').val("");
        }
    }
</script>
