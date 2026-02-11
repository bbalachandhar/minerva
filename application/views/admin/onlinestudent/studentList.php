<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('student_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                      <div class="table-responsive">
                        <div class="mailbox-messages">
                             <?php if ($this->session->flashdata('msg')) {
    echo $this->session->flashdata('msg');
    $this->session->unset_userdata('msg');}?>
                            <table class="table table-striped table-bordered table-hover student-list" data-export-title="<?php echo $this->lang->line('student_list'); ?>">
                                <thead>
                                    <tr>
                                        <th style="width:5%"><?php echo $this->lang->line('reference_no'); ?></th>
                                        <th><?php echo $this->lang->line('student_name'); ?></th>
                                        <th class="white-space-nowrap"><?php echo $this->lang->line('class'); ?></th>
                                         <?php if ($sch_setting->father_name) {?>
                                            <th><?php echo $this->lang->line('father_name'); ?></th>
                                        <?php }?>
                                        <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                        <th><?php echo $this->lang->line('gender'); ?></th>
                                        <th><?php echo $this->lang->line('category'); ?></th>
                                          <?php if ($sch_setting->mobile_no) {?>
                                        <th style="width:10%"><?php echo $this->lang->line('student_mobile_number'); ?></th>
                                       <?php }?>
                                        <th><?php echo $this->lang->line('form_status'); ?></th>
                                        <?php if ($sch_setting->online_admission_payment == 'yes') {?>
                                            <th><?php echo $this->lang->line('payment_status'); ?></th>
                                            <?php }?>
                                        <th><?php echo $this->lang->line('enrolled'); ?></th>
                                        <th><?php echo $this->lang->line('created_at'); ?></th>
                                        <th><?php echo $this->lang->line('updated_by'); ?></th>
                                        <th><?php echo $this->lang->line('updated_at'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                       </div><!--./table-responsive-->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel">
    <div class="modal-dialog" role="document">
        <form id="add_payment_form" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addPaymentModalLabel"><?php echo $this->lang->line('add_payment'); ?></h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="online_admission_id" id="online_admission_id" value="">
                    <div class="form-group">
                        <label for="reference_no"><?php echo $this->lang->line('reference_no'); ?></label>
                        <input type="text" class="form-control" id="reference_no" name="reference_no" readonly>
                    </div>
                    <div class="form-group">
                        <label for="transaction_id"><?php echo $this->lang->line('transaction_id'); ?> <span style="color: red;">*</span></label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
                    </div>
                    <div class="form-group">
                        <label for="note"><?php echo $this->lang->line('note'); ?> <span style="color: red;">*</span></label>
                        <textarea class="form-control" id="note" name="note" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    ( function ( $ ) {
    'use strict';
    $(document).ready(function () {
        initDatatable('student-list','admin/onlinestudent/getstudentlist',[],[],100);
    });
} ( jQuery ) )
</script>

<script>
    function checkpaymentstatus(id){
       $.ajax({
            url: '<?php echo base_url(); ?>admin/onlinestudent/checkpaymentstatus',
            type: "POST",
            data: {id:id},
            success: function (data) {

               if(data!=""){
                    if(confirm(data)){
                      window.location.href="<?php echo base_url() . 'admin/onlinestudent/edit/' ?>"+id ;
                    }else{
                         return false ;
                    }
                }else{
                     window.location.href="<?php echo base_url() . 'admin/onlinestudent/edit/' ?>"+id ;
                }
            }
        });
    }

    function addpayment(id, reference_no){
        $('#online_admission_id').val(id);
        $('#reference_no').val(reference_no);
        $('#addPaymentModal').modal('show');
    }

    $(document).ready(function () {
        $('#add_payment_form').on('submit', function (e) {
            e.preventDefault();
            var $this = $(this);
            $.ajax({
                url: '<?php echo site_url("admin/onlinestudent/addpayment") ?>',
                type: "POST",
                data: $this.serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.status == "fail") {
                        var message = "";
                        $.each(data.error, function (index, value) {
                            message += value;
                        });
                        errorMsg(message);
                    } else {
                        successMsg(data.message);
                        $('#addPaymentModal').modal('hide');
                        $('.student-list').DataTable().ajax.reload();
                    }
                }
            });
        });
    });
</script>