
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-building"></i> <?php echo $this->lang->line('hall_management'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('edit_hall'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('admin/hall/edit/' . $id) ?>" id="hallform" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="id" value="<?php echo $hall->id; ?>">
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('hall_name'); ?></label><small class="req"> *</small>
                                <input autofocus="" id="name" name="name" placeholder="" type="text" class="form-control" value="<?php echo set_value('name', $hall->name); ?>" />
                                <span class="text-danger"><?php echo form_error('name'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('capacity'); ?></label><small class="req"> *</small>
                                <input id="capacity" name="capacity" placeholder="" type="number" class="form-control" value="<?php echo set_value('capacity', $hall->capacity); ?>" />
                                <span class="text-danger"><?php echo form_error('capacity'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('location'); ?></label><small class="req"> *</small>
                                <input id="location" name="location" placeholder="" type="text" class="form-control" value="<?php echo set_value('location', $hall->location); ?>" />
                                <span class="text-danger"><?php echo form_error('location'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                <textarea id="description" name="description" placeholder="" class="form-control"><?php echo set_value('description', $hall->description); ?></textarea>
                                <span class="text-danger"><?php echo form_error('description'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('available_equipment'); ?></label>
                                <input id="available_equipment" name="available_equipment" placeholder="" type="text" class="form-control" value="<?php echo set_value('available_equipment', $hall->available_equipment); ?>" />
                                <span class="text-danger"><?php echo form_error('available_equipment'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('hourly_rate'); ?></label>
                                <input id="hourly_rate" name="hourly_rate" placeholder="" type="number" step="0.01" class="form-control" value="<?php echo set_value('hourly_rate', $hall->hourly_rate); ?>" />
                                <span class="text-danger"><?php echo form_error('hourly_rate'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('min_booking_duration'); ?></label>
                                <input id="min_booking_duration" name="min_booking_duration" placeholder="" type="number" class="form-control" value="<?php echo set_value('min_booking_duration', $hall->min_booking_duration); ?>" />
                                <span class="text-danger"><?php echo form_error('min_booking_duration'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('max_booking_duration'); ?></label>
                                <input id="max_booking_duration" name="max_booking_duration" placeholder="" type="number" class="form-control" value="<?php echo set_value('max_booking_duration', $hall->max_booking_duration); ?>" />
                                <span class="text-danger"><?php echo form_error('max_booking_duration'); ?></span>
                            </div>
                            <div class="bootstrap-timepicker">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('opening_time'); ?></label><small class="req"> *</small>
                                    <div class="input-group">
                                        <input id="opening_time" name="opening_time" placeholder="" type="text" class="form-control time" value="<?php echo set_value('opening_time', $hall->opening_time); ?>" />
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('opening_time'); ?></span>
                                </div>
                            </div>
                            <div class="bootstrap-timepicker">
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('closing_time'); ?></label><small class="req"> *</small>
                                    <div class="input-group">
                                        <input id="closing_time" name="closing_time" placeholder="" type="text" class="form-control time" value="<?php echo set_value('closing_time', $hall->closing_time); ?>" />
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                    </div>
                                    <span class="text-danger"><?php echo form_error('closing_time'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputFile"><?php echo $this->lang->line('hall_image'); ?></label>
                                <input type="file" id="image" name="image" class="dropify" data-height="100" data-default-file="<?php echo !empty($hall->image) ? base_url($hall->image) : ''; ?>">
                                <span class="text-danger"><?php echo form_error('image'); ?></span>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?php echo $this->lang->line('is_active'); ?></label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="is_active" value="1" <?php echo set_checkbox('is_active', '1', (bool)$hall->is_active); ?>> <?php echo $this->lang->line('yes'); ?>
                                </label>
                            </div>
                            <!-- Image upload will be handled later -->
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('hall_list'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('hall_name'); ?></th>
                                        <th><?php echo $this->lang->line('capacity'); ?></th>
                                        <th><?php echo $this->lang->line('location'); ?></th>
                                        <th><?php echo $this->lang->line('image'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text-right no-print"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($hallList)) {
                                        ?>
                                        <tr>
                                            <td colspan="6" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($hallList as $hall_item) { // Renamed $hall to $hall_item to avoid conflict with $hall in edit form
                                            ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $hall_item->name ?></td>
                                                <td class="mailbox-name"><?php echo $hall_item->capacity ?></td>
                                                <td class="mailbox-name"><?php echo $hall_item->location ?></td>
                                                <td class="mailbox-name">
                                                    <?php if (!empty($hall_item->image)) { ?>
                                                        <img src="<?php echo base_url($hall_item->image); ?>" class="img-thumbnail" style="width: 50px !important; height: 50px !important;">
                                                    <?php } else { ?>
                                                        <img src="<?php echo base_url('uploads/no_image.png'); ?>" class="img-thumbnail" style="width: 50px !important; height: 50px !important;">
                                                    <?php } ?>
                                                </td>
                                                <td class="mailbox-name"><?php echo ($hall_item->is_active) ? $this->lang->line('active') : $this->lang->line('inactive'); ?></td>
                                                <td class="mailbox-date pull-right no-print">
                                                    <a href="<?php echo base_url(); ?>admin/hall/edit/<?php echo $hall_item->id ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo base_url(); ?>admin/hall/delete/<?php echo $hall_item->id ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.time').timepicker({
            showInputs: false,
            icons: {
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down'
            }
        });

        // Initialize Dropify
        $('.dropify').dropify();
    });
</script>
