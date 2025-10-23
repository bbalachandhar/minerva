<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-gears"></i> Manual Configuration</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Manual Configuration Selection</h3>
                    </div>
                    <?php if ($this->session->flashdata('msg')) { ?>
                        <?php echo $this->session->flashdata('msg'); ?>
                    <?php } ?>
                    <form action="<?php echo site_url('admin/naac/manual_config_actions') ?>" id="manual_form" class="form-horizontal" method="post">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="id" id="manual_config_id" value="<?php echo set_value('id', (isset($manual_configuration_record['id'])) ? $manual_configuration_record['id'] : ''); ?>">
                            <div class="row">
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="manual_id" class="col-sm-4 control-label">Manual ID <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="manual_id" placeholder="Manual ID" required="" value="<?php echo set_value('manual_id', (isset($manual_configuration_record['manual_id'])) ? $manual_configuration_record['manual_id'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="institution_category" class="col-sm-4 control-label">Institution Category <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="institution_category" required="">
                                                <option selected="" disabled="" value="">Select Category</option>
                                                <option value="1" <?php echo set_select('institution_category', '1', (isset($manual_configuration_record['institution_category']) && $manual_configuration_record['institution_category'] == '1') ? TRUE : FALSE); ?>>Affliated</option>
                                                <option value="2" <?php echo set_select('institution_category', '2', (isset($manual_configuration_record['institution_category']) && $manual_configuration_record['institution_category'] == '2') ? TRUE : FALSE); ?>>Autonomous</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="manual_description" class="col-sm-4 control-label">Manual Description <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="manual_description" placeholder="Manual Description" value="<?php echo set_value('manual_description', (isset($manual_configuration_record['manual_description'])) ? $manual_configuration_record['manual_description'] : ''); ?>" required="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="month" class="col-sm-4 control-label">Month <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="month" required="">
                                                <option selected="" disabled="" value="">Select Month</option>
                                                <option value="1" <?php echo set_select('month', '1', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '1') ? TRUE : FALSE); ?>>January</option>
                                                <option value="2" <?php echo set_select('month', '2', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '2') ? TRUE : FALSE); ?>>February</option>
                                                <option value="3" <?php echo set_select('month', '3', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '3') ? TRUE : FALSE); ?>>March</option>
                                                <option value="4" <?php echo set_select('month', '4', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '4') ? TRUE : FALSE); ?>>April</option>
                                                <option value="5" <?php echo set_select('month', '5', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '5') ? TRUE : FALSE); ?>>May</option>
                                                <option value="6" <?php echo set_select('month', '6', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '6') ? TRUE : FALSE); ?>>June</option>
                                                <option value="7" <?php echo set_select('month', '7', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '7') ? TRUE : FALSE); ?>>July</option>
                                                <option value="8" <?php echo set_select('month', '8', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '8') ? TRUE : FALSE); ?>>August</option>
                                                <option value="9" <?php echo set_select('month', '9', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '9') ? TRUE : FALSE); ?>>September</option>
                                                <option value="10" <?php echo set_select('month', '10', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '10') ? TRUE : FALSE); ?>>October</option>
                                                <option value="11" <?php echo set_select('month', '11', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '11') ? TRUE : FALSE); ?>>November</option>
                                                <option value="12" <?php echo set_select('month', '12', (isset($manual_configuration_record['month']) && $manual_configuration_record['month'] == '12') ? TRUE : FALSE); ?>>December</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="year" class="col-sm-4 control-label">Year <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="year" required="">
                                                <option selected="" disabled="" value="">Select Year</option>
                                                <?php
                                                foreach ($sessionlist as $session) {
                                                    echo "<option value='" . $session['id'] . "' " . set_select('year', $session['id'], (isset($manual_configuration_record['year']) && $manual_configuration_record['year'] == $session['id']) ? TRUE : FALSE) . ">" . $session['session'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="total_criteria" class="col-sm-4 control-label">Total Criteria <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" pattern="[0-9.]+" class="form-control" name="total_criteria" placeholder="Total Criteria" required="" value="<?php echo set_value('total_criteria', (isset($manual_configuration_record['total_criteria'])) ? $manual_configuration_record['total_criteria'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="total_key_indicators" class="col-sm-4 control-label">Total Key Indicators (KIs) <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" pattern="[0-9.]+" class="form-control" name="total_key_indicators" placeholder="Total Key Indicators (KIs)" required="" value="<?php echo set_value('total_key_indicators', (isset($manual_configuration_record['total_key_indicators'])) ? $manual_configuration_record['total_key_indicators'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="total_qualitative_metrics" class="col-sm-4 control-label">Total Qualitative Metrics <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" pattern="[0-9.]+" class="form-control" name="total_qualitative_metrics" placeholder="Total Qualitative Metrics" required="" value="<?php echo set_value('total_qualitative_metrics', (isset($manual_configuration_record['total_qualitative_metrics'])) ? $manual_configuration_record['total_qualitative_metrics'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="total_quantitative_metrics" class="col-sm-4 control-label">Total Quantitative Metrics <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" pattern="[0-9.]+" class="form-control" name="total_quantitative_metrics" placeholder="Total Quantitative Metrics" required="" value="<?php echo set_value('total_quantitative_metrics', (isset($manual_configuration_record['total_quantitative_metrics'])) ? $manual_configuration_record['total_quantitative_metrics'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="total_metrics" class="col-sm-4 control-label">Total Metrics <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" pattern="[0-9.]+" class="form-control" name="total_metrics" placeholder="Total Metrics" required="" value="<?php echo set_value('total_metrics', (isset($manual_configuration_record['total_metrics'])) ? $manual_configuration_record['total_metrics'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="total_weightage" class="col-sm-4 control-label">Total Weightage <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" pattern="[0-9.]+" class="form-control" name="total_weightage" placeholder="Total Weightage" required="" value="<?php echo set_value('total_weightage', (isset($manual_configuration_record['total_weightage'])) ? $manual_configuration_record['total_weightage'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="total_marks" class="col-sm-4 control-label">Total Marks <span class="text-danger">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" pattern="[0-9.]+" name="total_marks" class="form-control" placeholder="Total Marks" required="" value="<?php echo set_value('total_marks', (isset($manual_configuration_record['total_marks'])) ? $manual_configuration_record['total_marks'] : ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4" style="padding-right: 20px;">
                                    <div class="form-group">
                                        <label for="is_optional_metric" class="col-sm-4 control-label">Optical Metrics</label>
                                        <div class="col-sm-8">
                                            <label class="radio-inline">
                                                <input type="radio" name="is_optional_metric" value="yes" class="form-check-input" required="" <?php echo set_radio('is_optional_metric', 'yes', (isset($manual_configuration_record['is_optional_metric']) && $manual_configuration_record['is_optional_metric'] == 'yes') ? TRUE : FALSE); ?>> Yes
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="is_optional_metric" value="no" class="form-check-input" required="" <?php echo set_radio('is_optional_metric', 'no', (isset($manual_configuration_record['is_optional_metric']) && $manual_configuration_record['is_optional_metric'] == 'no') ? TRUE : FALSE); ?>> No
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" name="add_manual" value="add_manual" class="btn btn-primary" id="add_manual_btn"><i class="ri-save-line"></i> Add Manual</button>
                                <button type="button" id="reset_form" class="btn btn-default" onclick="window.location.href='<?php echo site_url('admin/naac/manual_configuration'); ?>'"><i class="ri-close-line"></i> Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Manual Configuration List</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="manual_configuration_table" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Manual ID</th>
                                        <th>Institution Category</th>
                                        <th>Manual Description</th>
                                        <th>Month</th>
                                        <th>Year</th>
                                        <th>Total Criteria</th>
                                        <th>Total Key Indicators (KIs)</th>
                                        <th>Total Qualitative Metrics</th>
                                        <th>Total Quantitative Metrics</th>
                                        <th>Total Metrics</th>
                                        <th>Total Weightage</th>
                                        <th>Total Marks</th>
                                        <th>Optical Metrics</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($manual_configuration_list as $manual_configuration) { ?>
                                        <tr data-id="<?php echo $manual_configuration['id']; ?>">
                                            <td><?php echo $manual_configuration['manual_id']; ?></td>
                                            <td><?php echo $manual_configuration['institution_category']; ?></td>
                                            <td><?php echo $manual_configuration['manual_description']; ?></td>
                                            <td><?php echo $manual_configuration['month']; ?></td>
                                            <td><?php echo $manual_configuration['year']; ?></td>
                                            <td><?php echo $manual_configuration['total_criteria']; ?></td>
                                            <td><?php echo $manual_configuration['total_key_indicators']; ?></td>
                                            <td><?php echo $manual_configuration['total_qualitative_metrics']; ?></td>
                                            <td><?php echo $manual_configuration['total_quantitative_metrics']; ?></td>
                                            <td><?php echo $manual_configuration['total_metrics']; ?></td>
                                            <td><?php echo $manual_configuration['total_weightage']; ?></td>
                                            <td><?php echo $manual_configuration['total_marks']; ?></td>
                                            <td><?php echo $manual_configuration['is_optional_metric']; ?></td>
                                            <td>
                                                <a href="<?php echo site_url('admin/naac/manual_configuration/' . $manual_configuration['id']); ?>" class="btn btn-default btn-xs edit_manual_config" data-toggle="tooltip" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="<?php echo site_url('admin/naac/delete_manual_configuration/' . $manual_configuration['id']); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this item?');">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
$(document).ready(function() {
    $('#manual_configuration_table').DataTable();

    // Populate form for editing if record data is available
    <?php if (isset($manual_configuration_record)) { ?>
        var record = <?php echo json_encode($manual_configuration_record); ?>;
        $('#manual_config_id').val(record.id);
        $('input[name="manual_id"]').val(record.manual_id);
        $('select[name="institution_category"]').val(record.institution_category);
        $('input[name="manual_description"]').val(record.manual_description);
        $('select[name="month"]').val(record.month);
        $('select[name="year"]').val(record.year);
        $('input[name="total_criteria"]').val(record.total_criteria);
        $('input[name="total_key_indicators"]').val(record.total_key_indicators);
        $('input[name="total_qualitative_metrics"]').val(record.total_qualitative_metrics);
        $('input[name="total_quantitative_metrics"]').val(record.total_quantitative_metrics);
        $('input[name="total_metrics"]').val(record.total_metrics);
        $('input[name="total_weightage"]').val(record.total_weightage);
        $('input[name="total_marks"]').val(record.total_marks);
        $('input[name="is_optional_metric"][value="' + record.is_optional_metric + '"]').prop('checked', true);
        $('#add_manual_btn').html('<i class="ri-save-line"></i> Save Changes');
    <?php } ?>

    // Handle row click for editing
    $('#manual_configuration_table tbody').on('click', 'tr', function () {
        var id = $(this).data('id');
        window.location.href = '<?php echo site_url('admin/naac/manual_configuration/'); ?>' + id;
    });
});
</script>