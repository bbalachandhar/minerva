<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i> Library</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Vendor</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form id="form1" action="<?php echo site_url('admin/libraryvendor') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php
                                    echo $this->session->flashdata('msg');
                                    $this->session->unset_userdata('msg');
                                ?>
                            <?php } ?>
                            <?php
                            if (isset($error_message)) {
                                echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                            }
                            ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Vendor Name</label><small class="req"> *</small>
                                <input autofocus=""  id="vendor_name" name="vendor_name" placeholder="" type="text" class="form-control"  value="<?php
                                if(isset($edit_vendor)){
                                    echo $edit_vendor['vendor_name'];
                                }else{
                                    echo set_value('vendor_name');
                                }?>" />
                                <span class="text-danger"><?php echo form_error('vendor_name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Description</label>
                                <textarea class="form-control" id="description" name="description" placeholder="" rows="3" placeholder="Enter ..."><?php
                                if(isset($edit_vendor)){
                                    echo $edit_vendor['description'];
                                }else{
                                    echo set_value('description');
                                }?></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
                            <?php
                            if(isset($edit_vendor)){
                                ?><input type="hidden" name="id" value="<?php echo $edit_vendor['id']; ?>"><?php
                            }?>
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Save</button>
                        </div>
                    </form>
                </div>

            </div><!--/.col (right) -->
            <!-- left column -->
            <div class="col-md-8">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix">Vendor List</h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('library_vendor', 'can_add')) {
                                ?>
                                <a class="btn btn-sm btn-primary" href="<?php echo base_url(); ?>admin/libraryvendor/import" autocomplete="off"><i class="fa fa-plus"></i> Import Vendor</a> 
                            <?php }
                            ?>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label">Vendor List</div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th>Vendor Name</th>
                                        <th>Description</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($listvendor)) {
                                        ?>

                                        <?php
                                    } else {
                                        $count = 1;
                                        foreach ($listvendor as $vendor) {
                                            ?>
                                            <tr>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $vendor['vendor_name'] ?></a>
                                                </td>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $vendor['description'] ?></a>
                                                </td>
                                                <td class="mailbox-date pull-right">
                                                    <?php if ($this->rbac->hasPrivilege('library_vendor', 'can_edit')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/libraryvendor/index/<?php echo $vendor['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="Edit">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($this->rbac->hasPrivilege('library_vendor', 'can_delete')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/libraryvendor/delete/<?php echo $vendor['id'] ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this item?');">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        $count++;
                                    }
                                    ?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
        </div>
        <div class="row">
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
    $(document).ready(function () {
        $("#btnreset").click(function () {
            /* Single line Reset function executes on click of Reset Button */
            $("#form1")[0].reset();
        });
    });
</script>
