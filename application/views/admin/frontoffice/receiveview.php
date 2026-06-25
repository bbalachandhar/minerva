<div class="content-wrapper" style="min-height: 348px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-ioxhost"></i> <?php echo $this->lang->line('front_office'); ?></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('postal_receive_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('postal_receive', 'can_add')) { ?>
                                <a class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addReceiveModal"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add'); ?></a>
                            <?php } ?>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                        <div class="download_label"><?php echo $this->lang->line('postal_receive_list'); ?></div>
                        <div class="mailbox-messages table-responsive overflow-visible-lg">
                            <table class="table table-hover table-striped table-bordered example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('from_title'); ?></th>
                                        <th><?php echo $this->lang->line('reference_no'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('to_title'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('date'); ?>
                                        </th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($ReceiveList)) {
                                        ?>
                                        <?php
                                    } else {
                                        foreach ($ReceiveList as $key => $value) {

                                            ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $value->from_title; ?></td>
                                                <td class="mailbox-name"><?php echo $value->reference_no; ?></td>
                                                <td class="mailbox-name"><?php echo $value->to_title; ?></td>
                                                <td class="mailbox-name"><?php if($value->date != '0000-00-00'){ echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->date)); } ?></td>
                                                <td class="mailbox-date pull-right white-space-nowrap">
                                                    <a onclick="getRecord(<?php echo $value->id; ?>)" class="btn btn-default btn-xs" data-target="#receviedetails" data-toggle="modal"  title="<?php echo $this->lang->line('view') ?>"><i class="fa fa-reorder"></i></a>
                                                    <?php if ($value->image != "") { ?><a href="<?php echo base_url(); ?>admin/receive/download/<?php echo $value->id; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('download'); ?>">
                                                            <i class="fa fa-download"></i>
                                                        </a>  <?php } ?>   <?php if ($this->rbac->hasPrivilege('postal_receive', 'can_edit')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/receive/editreceive/<?php echo $value->id; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('edit'); ?>">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } if ($this->rbac->hasPrivilege('postal_receive', 'can_delete')) { ?>
                                                            <a href="<?php echo base_url(); ?>admin/receive/delete/<?php echo $value->id; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');" data-original-title="<?php echo $this->lang->line('delete'); ?>">
                                                                <i class="fa fa-remove"></i>
                                                            </a>

                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col -->
        </div>
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<!-- Add Postal Receive Modal -->
<?php if ($this->rbac->hasPrivilege('postal_receive', 'can_add')) { ?>
<div id="addReceiveModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('add_postal_receive'); ?></h4>
            </div>
            <form id="form1" action="<?php echo site_url('admin/receive') ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="pwd"><?php echo $this->lang->line('from_title'); ?></label>   <small class="req"> *</small>
                        <input type="text" class="form-control" value="<?php echo set_value('from_title'); ?>" name="from_title">
                        <span class="text-danger"><?php echo form_error('from_title'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1"><?php echo $this->lang->line('reference_no'); ?></label>

                        <input type="text" class="form-control" value="<?php echo set_value('ref_no'); ?>" name="ref_no">
                        <span class="text-danger"><?php echo form_error('ref_no'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="pwd"><?php echo $this->lang->line('address'); ?></label>
                        <textarea class="form-control" id="description"  name="address" rows="3"><?php echo set_value('address'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo $this->lang->line('note'); ?></label>
                        <textarea class="form-control" id="description" name="note" name="note" rows="3"><?php echo set_value('note'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="pwd"><?php echo $this->lang->line('to_title'); ?></label>
                        <input type="text" class="form-control" value="<?php echo set_value('to_title'); ?>"  name="to_title">
                        <span class="text-danger"><?php echo form_error('to_title'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="pwd"><?php echo $this->lang->line('date'); ?></label>
                        <input id="date" name="date" placeholder="" type="text" class="form-control date"  value="<?php echo set_value('date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly" />
                        <span class="text-danger"><?php echo form_error('date'); ?></span>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputFile"><?php echo $this->lang->line('attach_document'); ?></label>
                        <div><input class="filestyle form-control" type='file' name='file'/>
                        </div>
                        <span class="text-danger"><?php echo form_error('file'); ?></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('cancel'); ?></button>
                    <button type="submit" id="submitbtn" class="btn btn-info"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<!-- View Receive Details Modal -->
<div id="receviedetails" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('details'); ?></h4>
            </div>
            <div class="modal-body" id="getdetails">

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function getRecord(id) {
        $.ajax({
            url: '<?php echo base_url(); ?>admin/dispatch/details/' + id + '/receive',
            success: function (result) {

                $('#getdetails').html(result);
            }
        });
    }
</script>
<script>
    $(function(){
        $('#form1').submit(function() {
            $("#submitbtn").button('loading');
        });

        <?php if (form_error('from_title') || form_error('to_title') || form_error('date') || form_error('file')) { ?>
            $('#addReceiveModal').modal('show');
        <?php } ?>
    })
</script>
