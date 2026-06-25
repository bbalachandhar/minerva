<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-bar-chart"></i> <?php echo $this->lang->line('marks_grade'); ?>
        </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('grade_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/grade'); ?>" class="btn btn-sm btn-default"><i class="fa fa-arrow-left"></i> <?php echo $this->lang->line('back'); ?></a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <?php echo $this->session->flashdata('msg'); $this->session->unset_userdata('msg'); ?>
                        <?php } ?>
                        <?php if (isset($error_message)) {
                            echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                        } ?>
                        <div class="table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('grade_list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo $this->lang->line('exam_type'); ?></th>
                                        <th><?php echo $this->lang->line('grade_name'); ?></th>
                                        <th><?php echo $this->lang->line('percent_from_upto'); ?></th>
                                        <th><?php echo $this->lang->line('grade_point'); ?></th>
                                        <th><?php echo $this->lang->line('description'); ?></th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    if (!empty($listgrade)) {
                                        foreach ($listgrade as $grade) {
                                            if (!empty($grade['exam_grade_values'])) {
                                                foreach ($grade['exam_grade_values'] as $grade_value) {
                                    ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo $grade['exm_type_value']; ?></td>
                                        <td><?php echo $grade_value->name; ?></td>
                                        <td><?php echo $grade_value->mark_from . " " . $this->lang->line('to') . " " . $grade_value->mark_upto; ?></td>
                                        <td><?php echo $grade_value->point; ?></td>
                                        <td><?php echo isset($grade_value->description) ? $grade_value->description : ''; ?></td>
                                        <td class="text-right white-space-nowrap">
                                            <?php if ($this->rbac->hasPrivilege('marks_grade', 'can_edit')) { ?>
                                            <a href="<?php echo base_url(); ?>admin/grade/edit/<?php echo $grade_value->id; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <?php } ?>
                                            <?php if ($this->rbac->hasPrivilege('marks_grade', 'can_delete')) { ?>
                                            <a href="<?php echo base_url(); ?>admin/grade/delete/<?php echo $grade_value->id; ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
                                                <i class="fa fa-remove"></i>
                                            </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php
                                                }
                                            }
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

<!-- Edit Grade Modal -->
<div class="modal fade" id="editGradeModal" tabindex="-1" role="dialog" aria-labelledby="editGradeModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editGradeModalLabel"><?php echo $this->lang->line('edit_marks_grade'); ?></h4>
            </div>
            <form id="form1" action="<?php echo site_url('admin/grade/edit/' . $id); ?>" method="post" accept-charset="utf-8">
                <div class="modal-body">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <input type="hidden" name="id" value="<?php echo set_value('id', $editgrade['id']); ?>">
                    <div class="form-group">
                        <label><?php echo $this->lang->line('exam_type'); ?><small class="req"> *</small></label>
                        <select name="exam_type" class="form-control">
                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                            <?php foreach ($examType as $examType_key => $examType_value) { ?>
                            <option value="<?php echo $examType_key; ?>" <?php echo set_select('exam_type', $examType_key, (set_value('exam_type', $editgrade['exam_type']) == $examType_key) ? true : false); ?>><?php echo $examType_value; ?></option>
                            <?php } ?>
                        </select>
                        <span class="text-danger"><?php echo form_error('exam_type'); ?></span>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('grade_name'); ?><small class="req"> *</small></label>
                        <input name="name" type="text" class="form-control" value="<?php echo set_value('name', $editgrade['name']); ?>" />
                        <span class="text-danger"><?php echo form_error('name'); ?></span>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('percent_upto'); ?><small class="req"> *</small></label>
                        <input name="mark_from" type="text" class="form-control" value="<?php echo set_value('mark_from', $editgrade['mark_from']); ?>" />
                        <span class="text-danger"><?php echo form_error('mark_from'); ?></span>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('percent_from'); ?><small class="req"> *</small></label>
                        <input name="mark_upto" type="text" class="form-control" value="<?php echo set_value('mark_upto', $editgrade['mark_upto']); ?>" />
                        <span class="text-danger"><?php echo form_error('mark_upto'); ?></span>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('grade_point'); ?><small class="req"> *</small></label>
                        <input name="grade_point" type="text" class="form-control" value="<?php echo set_value('grade_point', $editgrade['point']); ?>" />
                        <span class="text-danger"><?php echo form_error('grade_point'); ?></span>
                    </div>
                    <div class="form-group">
                        <label><?php echo $this->lang->line('description'); ?></label>
                        <textarea class="form-control" name="description" rows="3"><?php echo set_value('description', $editgrade['description']); ?></textarea>
                        <span class="text-danger"><?php echo form_error('description'); ?></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="<?php echo site_url('admin/grade'); ?>" class="btn btn-default"><?php echo $this->lang->line('cancel'); ?></a>
                    <button type="submit" class="btn btn-info"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#editGradeModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: true
    });
});
</script>
