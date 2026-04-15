<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content-header">
        <h1>
            <i class="fa fa-mortar-board"></i> <?php echo $this->lang->line('academics'); ?>     </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php
            if ($this->rbac->hasPrivilege('class', 'can_add') || $this->rbac->hasPrivilege('class', 'can_edit')) {
                ?>  
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('edit_class'); ?></h3>
                            <div class="box-tools pull-right">
                                <button type="submit" form="form1" class="btn btn-info btn-sm"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </div><!-- /.box-header -->
                        <form id="form1" action="<?php echo site_url('classes/edit/' . $id) ?>"  method="post" accept-charset="utf-8">
                            <div class="box-body">

                                <?php 
                                    if ($this->session->flashdata('msg')) {
                                        echo $this->session->flashdata('msg');
                                        $this->session->unset_userdata('msg');
                                    } 
                                ?>

                                <?php
                                if (isset($error_message)) {
                                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                                }
                                ?>

                                <?php echo $this->customlib->getCSRF(); ?>
                                <input type="hidden" name="id" value="<?php echo set_value('id', $vehroute[0]->id); ?>" >
                                <input type="hidden" name="pre_class_id" value="<?php echo $vehroute[0]->id; ?>" >
                                <?php
                                foreach ($vehroute[0]->vehicles as $v_key => $v_value) {
                                    ?>
                                    <input type="hidden" name="prev_sections[]" value="<?php echo $v_value->id; ?>">
                                    <?php
                                }
                                ?>

                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                    <input autofocus="" id="class" name="class" placeholder="" type="text" class="form-control"  value="<?php echo set_value('class', $class_data['class']); ?>" />
                                    <span class="text-danger"><?php echo form_error('class'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Class Type</label>
                                    <div>
                                        <label class="radio-inline">
                                            <input type="radio" name="class_type" value="academic" <?php echo (($class_data['class_type'] ?? 'academic') == 'academic') ? 'checked' : ''; ?>> Academic
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="class_type" value="applicant" <?php echo (($class_data['class_type'] ?? '') == 'applicant') ? 'checked' : ''; ?>> Applicant
                                        </label>
                                    </div>
                                    <small class="text-muted">"Applicant" classes are for scholarship exam question bank only — they won't appear in enrollment, fees, or reports.</small>
                                </div>
                                <div class="form-group">
                                    <label>Active</label>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="is_active" value="yes" <?php echo (($class_data['is_active'] ?? 'no') === 'yes') ? 'checked' : ''; ?>>
                                            Enable this class (uncheck to deactivate)
                                        </label>
                                    </div>
                                </div>
                                <?php if ($sch_setting->institution_type == 'college') { ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Department</label><small class="req"> *</small>
                                    <select id="department_id" name="department_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($department_list as $department) {
                                            ?>
                                            <option value="<?php echo $department['id'] ?>"<?php
                                            if (isset($class_data) && $class_data['department_id'] == $department['id']) {
                                                echo "selected=selected";
                                            } else if (set_value('department_id') == $department['id']) {
                                                echo "selected=selected";
                                            }
                                            ?>><?php echo $department['department_name'] ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('department_id'); ?></span>
                                </div>
                                <?php } ?>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('sections'); ?></label><small class="req"> *</small>
                                    <div class="checkbox" style="margin-bottom:4px">
                                        <label style="font-weight:bold">
                                            <input type="checkbox" id="edit-section-all"> Select All
                                        </label>
                                    </div>
                                    <hr style="margin:4px 0 6px">

                                    <?php
                                    foreach ($vehiclelist as $vehicle) {
                                        ?>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="sections[]" value="<?php echo $vehicle['id'] ?>" <?php echo set_checkbox('sections[]', $vehicle['id'], check_in_array($vehicle['id'], $vehroute[0]->vehicles)); ?> ><?php echo $vehicle['section'] ?>
                                            </label>
                                        </div>
                                        <?php
                                    }
                                    ?>

                                    <span class="text-danger"><?php echo form_error('sections[]'); ?></span>
                                </div>


                            </div><!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>

                </div><!--/.col (right) -->
                <!-- left column -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('class', 'can_add') || $this->rbac->hasPrivilege('class', 'can_edit')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>  ">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('class_list'); ?></h3>
                        <div class="box-tools pull-right">
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages overflow-visible">
                            <div class="download_label"><?php echo $this->lang->line('class_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>

                                        <th><?php echo $this->lang->line('class'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('sections'); ?>
                                        </th>
                                        <th>Active</th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($vehroutelist as $vehroute) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name">
                                                <?php echo $vehroute->class; ?>
                                                <?php if (($vehroute->class_type ?? '') === 'applicant'): ?>
                                                    <span class="label label-info" style="margin-left:4px">Applicant</span>
                                                <?php endif; ?>
                                            </td>


                                            <td>
                                                <?php
                                                $vehicles = $vehroute->vehicles;
                                                if (!empty($vehicles)) {


                                                    foreach ($vehicles as $key => $value) {


                                                        echo "<div>" . $value->section . "</div>";
                                                    }
                                                }
                                                ?>

                                            </td>
                                            <td>
                                                <?php if ($this->rbac->hasPrivilege('class', 'can_edit')): ?>
                                                    <input type="checkbox" class="class-active-chk"
                                                        data-id="<?php echo $vehroute->id; ?>"
                                                        <?php echo (($vehroute->is_active ?? 'no') === 'yes') ? 'checked' : ''; ?>>
                                                <?php else: ?>
                                                    <?php echo (($vehroute->is_active ?? 'no') === 'yes') ? 'Yes' : 'No'; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="mailbox-date pull-right">
                                                <?php
                                                if ($this->rbac->hasPrivilege('class', 'can_edit')) {
                                                    ?>  
                                                    <a href="<?php echo base_url(); ?>classes/edit/<?php echo $vehroute->id; ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                        <i class="fa fa-pencil"></i>
                                                    </a>
                                                    <?php
                                                }
                                                if ($this->rbac->hasPrivilege('class', 'can_delete')) {
                                                    ?>  
            <a href="<?php echo base_url(); ?>classes/delete/<?php echo $vehroute->id; ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('deleting_class'); ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </tbody>
                            </table><!-- /.table -->



                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->

        </div>
        <div class="row">
            <!-- left column -->

            <!-- right column -->
            <div class="col-md-12">

            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script>
// Select All sections (edit form)
$('#edit-section-all').on('change', function () {
    $('input[name="sections[]"]').prop('checked', this.checked);
});
$('input[name="sections[]"]').on('change', function () {
    var total   = $('input[name="sections[]"]').length;
    var checked = $('input[name="sections[]"]:checked').length;
    $('#edit-section-all').prop('checked', checked === total).prop('indeterminate', checked > 0 && checked < total);
});
// Pre-set indeterminate state on load
$(function () {
    var total   = $('input[name="sections[]"]').length;
    var checked = $('input[name="sections[]"]:checked').length;
    $('#edit-section-all').prop('checked', checked === total).prop('indeterminate', checked > 0 && checked < total);
});

$(document).on('change', '.class-active-chk', function () {
    var chk = $(this);
    var id  = chk.data('id');
    $.post('<?php echo base_url(); ?>classes/toggleActive/' + id, {
        '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
    }, function (data) {
        if (data.status === 1) {
            chk.prop('checked', data.is_active === 'yes');
        } else {
            chk.prop('checked', !chk.prop('checked')); // revert
        }
    }, 'json').fail(function () {
        chk.prop('checked', !chk.prop('checked'));
    });
});
</script>

<?php

function check_in_array($find, $array) {

    foreach ($array as $element) {
        if ($find == $element->id) {
            return TRUE;
        }
    }
    return FALSE;
}
?>