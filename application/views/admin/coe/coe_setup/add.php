<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-university"></i> <?php echo $this->lang->line('coe_add_regulation'); ?><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_setup'); ?>"><?php echo $this->lang->line('coe_exam_regulations'); ?></a></li>
            <li class="active"><?php echo $this->lang->line('add'); ?></li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('coe_add_regulation'); ?></h3>
                    </div>
                    <form method="post" action="<?php echo site_url('coe/coe_setup/save'); ?>" id="regulation_form">
                        <div class="box-body">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('session'); ?> <span class="text-danger">*</span></label>
                                        <select name="session_id" class="form-control" required>
                                            <?php foreach ($session_list as $s): ?>
                                                <option value="<?php echo $s["id"]; ?>" <?php echo ($s["id"] == $current_session) ? 'selected' : ''; ?>>
                                                    <?php echo $s["session"]; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?> <span class="text-danger">*</span></label>
                                        <select name="class_id" class="form-control" required>
                                            <option value="">— Select —</option>
                                            <?php foreach ($class_list as $c): ?>
                                                <option value="<?php echo $c["id"]; ?>"><?php echo htmlspecialchars($c["class"]); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('department'); ?></label>
                                        <select name="department_id" class="form-control">
                                            <option value="">— All Departments —</option>
                                            <?php foreach ($department_list as $d): ?>
                                                <option value="<?php echo $d["id"]; ?>"><?php echo htmlspecialchars($d["department_name"]); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('coe_regulation_type'); ?> <span class="text-danger">*</span></label>
                                        <select name="regulation_type" class="form-control" id="regulation_type" required>
                                            <option value="affiliated"><?php echo $this->lang->line('coe_affiliated'); ?></option>
                                            <option value="autonomous"><?php echo $this->lang->line('coe_autonomous'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="affiliated_row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Affiliated University</label>
                                        <input type="text" name="affiliated_university" class="form-control" value="Anna University" placeholder="e.g. Anna University">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('coe_min_attendance_pct'); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="min_attendance_pct" class="form-control" value="75" min="0" max="100" step="0.01" required>
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('coe_internal_marks_pct'); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="internal_marks_pct" class="form-control" value="25" min="0" max="100" step="0.01" id="internal_pct" required>
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('coe_external_marks_pct'); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="external_marks_pct" class="form-control" value="75" min="0" max="100" step="0.01" id="external_pct" required>
                                            <span class="input-group-addon">%</span>
                                        </div>
                                        <small class="text-muted">Internal + External must = 100%</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('coe_pass_marks_pct'); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="pass_marks_pct" class="form-control" value="50" min="0" max="100" step="0.01" required>
                                            <span class="input-group-addon">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('coe_grading_scheme'); ?> <span class="text-danger">*</span></label>
                                        <select name="grading_scheme" class="form-control" required>
                                            <option value="ten_point">10-Point (CBCS/Anna Univ)</option>
                                            <option value="seven_point">7-Point</option>
                                            <option value="percentage">Percentage Only</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h4>Options</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="has_credit_system" value="1">
                                            <?php echo $this->lang->line('coe_has_credit_system'); ?> (CBCS/NEP 2020)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="arrear_allowed" value="1" checked>
                                            <?php echo $this->lang->line('coe_arrear_allowed'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="supplementary_allowed" value="1">
                                            <?php echo $this->lang->line('coe_supplementary_allowed'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="check_fee_dues" value="1" checked>
                                            <?php echo $this->lang->line('coe_check_fee_dues'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary"><?php echo $this->lang->line('save'); ?></button>
                            <a href="<?php echo site_url('coe/coe_setup'); ?>" class="btn btn-default"><?php echo $this->lang->line('cancel'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$('#regulation_type').on('change', function() {
    $('#affiliated_row').toggle($(this).val() === 'affiliated');
});

$('#internal_pct, #external_pct').on('input', function() {
    var int_val = parseFloat($('#internal_pct').val()) || 0;
    var ext_val = parseFloat($('#external_pct').val()) || 0;
    var sum = Math.round((int_val + ext_val) * 100) / 100;
    if (Math.abs(sum - 100) > 0.01) {
        $('#external_pct').closest('.form-group').addClass('has-error');
    } else {
        $('#external_pct').closest('.form-group').removeClass('has-error');
    }
});
</script>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'exam_regulations']); ?>
