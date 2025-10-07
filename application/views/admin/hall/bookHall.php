
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-plus-square"></i> <?php echo $this->lang->line('book_hall'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('book_new_hall'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('admin/hall/book') ?>" id="bookhallform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('hall'); ?></label><small class="req"> *</small>
                                <select autofocus="" id="hall_id" name="hall_id" class="form-control">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php
                                    foreach ($hallList as $hall) {
                                        ?>
                                        <option value="<?php echo $hall->id ?>" <?php if (set_value('hall_id') == $hall->id) echo "selected=selected" ?>><?php echo $hall->name . ' (' . $hall->location . ')' ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <span class="text-danger"><?php echo form_error('hall_id'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('purpose'); ?></label><small class="req"> *</small>
                                <input id="purpose" name="purpose" placeholder="" type="text" class="form-control" value="<?php echo set_value('purpose'); ?>" />
                                <span class="text-danger"><?php echo form_error('purpose'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('start_time'); ?></label><small class="req"> *</small>
                                <input id="start_time" name="start_time" placeholder="" type="text" class="form-control datetime" value="<?php echo set_value('start_time'); ?>" />
                                <span class="text-danger"><?php echo form_error('start_time'); ?></span>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1"><?php echo $this->lang->line('end_time'); ?></label><small class="req"> *</small>
                                <input id="end_time" name="end_time" placeholder="" type="text" class="form-control datetime" value="<?php echo set_value('end_time'); ?>" />
                                <span class="text-danger"><?php echo form_error('end_time'); ?></span>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('book_now'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.datetime').datetimepicker({
            format: 'YYYY-MM-DD HH:mm', // Adjust format as per your system's date/time picker
            // You might need to load specific datetimepicker assets if not already loaded
        });
    });
</script>