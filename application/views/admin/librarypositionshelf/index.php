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
                        <h3 class="box-title">Add Position Shelf</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <form id="form1" action="<?php echo site_url('admin/librarypositionshelf') ?>"  id="employeeform" name="employeeform" method="post" accept-charset="utf-8">
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
                                <label for="exampleInputEmail1">Position Rack</label><small class="req"> *</small>
                                <select autofocus="" id="rack_id" name="rack_id" class="form-control" >
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php
                                    foreach ($listpositionrack as $rack) {
                                        ?>
                                        <option value="<?php echo $rack['id'] ?>"<?php if (set_value('rack_id') == $rack['id']) echo "selected=selected" ?>><?php echo $rack['rack_name'] ?></option>
                                        <?php
                                        $count++;
                                    }
                                    ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('rack_id'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Position Shelf Name</label><small class="req"> *</small>
                                <input id="shelf_name" name="shelf_name" placeholder="" type="text" class="form-control"  value="<?php echo set_value('shelf_name'); ?>" />
                                <span class="text-danger"><?php echo form_error('shelf_name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Description</label>
                                <textarea class="form-control" id="description" name="description" placeholder="" rows="3" placeholder="Enter ..."><?php echo set_value('description'); ?></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
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
                        <h3 class="box-title titlefix">Position Shelf List</h3>
                        <div class="box-tools pull-right">
                            <?php if ($this->rbac->hasPrivilege('library_position_shelf', 'can_add')) {
                                ?>
                                <a class="btn btn-sm btn-primary" href="<?php echo base_url(); ?>admin/librarypositionshelf/import" autocomplete="off"><i class="fa fa-plus"></i> Import Position Shelf</a> 
                            <?php }
                            ?>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label">Position Shelf List</div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th>Position Shelf Name</th>
                                        <th>Position Rack Name</th>
                                        <th>Description</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($listpositionshelf)) {
                                        ?>

                                        <?php
                                    } else {
                                        $count = 1;
                                        foreach ($listpositionshelf as $positionshelf) {
                                            ?>
                                            <tr>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $positionshelf['shelf_name'] ?></a>
                                                </td>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $positionshelf['rack_name'] ?></a>
                                                </td>
                                                <td class="mailbox-name">
                                                    <a href="#" data-toggle="popover" class="detail_popover"><?php echo $positionshelf['description'] ?></a>
                                                </td>
                                                <td class="mailbox-date pull-right">
                                                    <?php if ($this->rbac->hasPrivilege('library_position_shelf', 'can_edit')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/librarypositionshelf/edit/<?php echo $positionshelf['id'] ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="Edit">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <?php if ($this->rbac->hasPrivilege('library_position_shelf', 'can_delete')) { ?>
                                                        <a href="<?php echo base_url(); ?>admin/librarypositionshelf/delete/<?php echo $positionshelf['id'] ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this item?');">
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
