<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/**
 * Per-student exam paper view, rendered server-side and injected into the evaluation page.
 *
 * Expects:
 *   $grouped  — array keyed by onlineexam_student_id; each value has:
 *               ['student'] => first result row (student info)
 *               ['questions'] => array of result rows
 *   $sch_setting — school settings object
 */
?>
<?php if (empty($grouped)): ?>
    <div class="alert alert-warning">No results found for the selected students.</div>
<?php else: ?>
    <?php foreach ($grouped as $onlineexam_student_id => $data): ?>
        <?php
        $student    = $data['student'];
        $questions  = $data['questions'];
        $total_max  = 0;
        $total_got  = 0;
        foreach ($questions as $q) {
            $total_max += (float) $q->question_marks;
            $total_got += (float) $q->marks;
        }
        $name = $this->customlib->getFullName(
            $student->firstname,
            $student->middlename,
            $student->lastname,
            $sch_setting->middlename,
            $sch_setting->lastname
        );
        ?>
        <div class="box box-default student-paper-box" style="margin-bottom:30px;">
            <div class="box-header with-border" style="background:#f0f4ff;">
                <h4 class="box-title">
                    <i class="fa fa-user"></i>
                    <?php echo htmlspecialchars($name); ?>
                    <?php if (!empty($student->admission_no)): ?>
                        <small class="text-muted"> &mdash; <?php echo htmlspecialchars($student->admission_no); ?></small>
                    <?php endif; ?>
                    <?php if (!empty($student->class)): ?>
                        <span class="label label-default" style="margin-left:8px;">
                            <?php echo htmlspecialchars($student->class . ($student->section ? ' (' . $student->section . ')' : '')); ?>
                        </span>
                    <?php endif; ?>
                    <span class="pull-right label label-info">
                        Score: <?php echo $total_got; ?> / <?php echo $total_max; ?>
                    </span>
                </h4>
                <div class="box-tools pull-right" style="margin-top:-22px;">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <!-- Student info strip -->
                <div class="row" style="margin-bottom:12px; font-size:13px; color:#555;">
                    <?php if (!empty($student->mobileno)): ?>
                        <div class="col-sm-3"><i class="fa fa-phone"></i> <?php echo htmlspecialchars($student->mobileno); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($student->guardian_name)): ?>
                        <div class="col-sm-5"><i class="fa fa-users"></i> <?php echo htmlspecialchars($student->guardian_name); ?>
                            <?php if (!empty($student->guardian_phone)): ?>
                                (<?php echo htmlspecialchars($student->guardian_phone); ?>)
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php foreach ($questions as $qi => $result_item): ?>
                    <div class="row" style="border-top:1px solid #eee; padding-top:12px; margin-top:8px;">
                        <div class="col-lg-12">
                            <span class="label label-primary">Q<?php echo $qi + 1; ?></span>
                            <span class="ques_marks text text-danger" style="margin-left:6px;">
                                (<?php echo $this->lang->line('marks'); ?>: <?php echo $result_item->question_marks; ?>)
                            </span>
                            <div style="margin-top:6px;"><?php echo $result_item->question; ?></div>

                            <div style="margin-top:8px;">
                                <strong><?php echo $this->lang->line('answer'); ?>:</strong>
                                <span class="displayblock pb5"><?php echo html_entity_decode($result_item->select_option); ?></span>
                            </div>

                            <?php if (!empty($result_item->attachment_name)): ?>
                                <div>
                                    <strong><?php echo $this->lang->line('attachment'); ?>:</strong>
                                    <a href="<?php echo site_url('admin/onlineexam/downloadattachment/' . $result_item->attachment_upload_name); ?>"
                                       data-toggle="tooltip" title="<?php echo $this->lang->line('download'); ?>">
                                        <?php echo htmlspecialchars($result_item->attachment_name); ?>
                                        <i class="fa fa-download"></i>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <form class="mark_fill_form" method="POST" action="<?php echo site_url('admin/onlineexam/fillmarks'); ?>" style="margin-top:10px;">
                                <input type="hidden" name="onlineexam_student_result_id" value="<?php echo $result_item->id; ?>">
                                <input type="hidden" name="question_marks" value="<?php echo $result_item->question_marks; ?>">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('your_marks'); ?>:</label>
                                            <input type="text" name="fill_mark" class="form-control"
                                                   value="<?php echo htmlspecialchars((string)$result_item->marks); ?>"
                                                   placeholder="0 – <?php echo $result_item->question_marks; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('your_remark'); ?>:</label>
                                            <textarea id="remark_<?php echo $result_item->id; ?>"
                                                      name="remark"
                                                      class="form-control remark"><?php echo $result_item->remark; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-info btn-sm smallbtn28"
                                        data-loading-text="<i class='fa fa-spinner fa-spin'></i> <?php echo $this->lang->line('please_wait'); ?>">
                                    <?php echo $this->lang->line('save'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div><!-- /.box-body -->
        </div><!-- /.student-paper-box -->
    <?php endforeach; ?>
<?php endif; ?>
