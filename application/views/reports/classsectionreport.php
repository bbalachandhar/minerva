<div class="content-wrapper">
    <!-- Main content -->
    <section class="content" >
        <?php $this->load->view('reports/_studentinformation'); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box removeboxmius">
                    <div class="box-header ptbnull"></div>
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('report/classsectionreport') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <?php if ($sch_setting->institution_type == 'college') {?>
                                <div class="col-md-12">
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
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary btn-sm pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>   </div>
                    </form>
                    <div class="">
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('class_section_report'); ?> </h3>
                        </div>
                        <div class="box-body table-responsive">
                            <?php 

if(!empty($class_section_list)){
?>
<div class="download_label"><?php echo $this->lang->line('class_section_report'); ?></div>
 <table class="table table-striped table-bordered table-hover example">
        <thead>
            <tr>
                <th><?php echo $this->lang->line('s_no'); ?></th>
                <th class="text text-center"><?php echo $this->lang->line('class'); ?></th>
                <th class="text text-center"><?php echo $this->lang->line('students'); ?></th>
                <th class="text text-right noExport"><?php echo $this->lang->line('action'); ?></th>
            </tr>
        </thead>
    <tbody>
        <?php 
    $count=1;
 foreach ($class_section_list as $class_section_key => $class_section_value) {
?>
<tr>
        <td><?php echo $count; ?></td>
        <td class="text text-center"><?php echo $class_section_value->class . " (" . $class_section_value->section . ")" ?></td>
        <td class="text text-center"><?php echo $class_section_value->student_count; ?></td>
        <td class="text text-right">    
          
   <button type="button" class="btn btn-default btn-xs studentlist" id="load" data-toggle="tooltip"  data-clssection-id="<?php echo $class_section_value->id; ?>" title="<?php echo $this->lang->line('view_students'); ?>" data-loading-text="<i class='fa fa-spinner fa-spin'></i>"><i class="fa fa-eye"></i></button></td>
</tr>
<?php
      $count+=1;  
    }

         ?>
    </tbody>
</table>
<?php   
}else{
    ?>
                                        <div class="alert alert-info">
											<?php echo $this->lang->line('no_record_found'); ?>
                                        </div>
                                        <?php
}
                                 ?>
                        </div>
                    </div>
                </div><!--./box box-primary-->
            </div><!-- ./col-md-12 -->  
        </div>
</section>
</div>
       

<div id="studentModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-xl">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('student_list'); ?></h4>
            </div>

            <div class="modal-body">
            </div>
        </div>
    </div>
</div>
      
<script type="text/javascript">

$(document).ready(function(){
  $('#studentModal').modal({backdrop:'static', keyboard:false, show: false});
});

      $(document).on('click', '.studentlist', function () {
        var $this = $(this);
        var date=$this.data('date');    
     
        $.ajax({
            type: 'POST',
            url: baseurl + "student/getStudentByClassSection",
            data: {'cls_section_id':$this.data('clssectionId')},
            dataType: 'JSON',
            beforeSend: function () {
                $this.button('loading');
            },
            success: function (data) {
                $('#studentModal .modal-body').html(data.page);
                $('#studentModal').modal('show');
                $this.button('reset');
            },
            error: function (xhr) { // if error occured
                alert("<?php echo $this->lang->line('error_occurred_please_try_again'); ?>");
                $this.button('reset');
            },
            complete: function () {
                $this.button('reset');
            }
        });
    });
</script>