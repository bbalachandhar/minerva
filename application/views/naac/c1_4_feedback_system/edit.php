<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c1_4_edit/' . $entry['id']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="bozero">
                                <h4 class="pagetitleh-whitebg"><?php echo $page_title; ?></h4>
                                <div class="around10">
                                    <?php if ($this->session->flashdata('msg')): ?>
                                        <div class="alert alert-success">
                                            <?php echo $this->session->flashdata('msg'); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php echo validation_errors(); ?>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="academic_year">Academic Year</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year', $entry['academic_year']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="stakeholder_type">Stakeholder Type</label><small class="req"> *</small>
                                                <select class="form-control" id="stakeholder_type" name="stakeholder_type">
                                                    <option value="">Select Type</option>
                                                    <option value="Student" <?php echo set_select('stakeholder_type', 'Student', ($entry['stakeholder_type'] == 'Student')); ?>>Student</option>
                                                    <option value="Faculty" <?php echo set_select('stakeholder_type', 'Faculty', ($entry['stakeholder_type'] == 'Faculty')); ?>>Faculty</option>
                                                    <option value="Employer" <?php echo set_select('stakeholder_type', 'Employer', ($entry['stakeholder_type'] == 'Employer')); ?>>Employer</option>
                                                    <option value="Alumni" <?php echo set_select('stakeholder_type', 'Alumni', ($entry['stakeholder_type'] == 'Alumni')); ?>>Alumni</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="feedback_mechanism">Feedback Mechanism</label>
                                                <textarea class="form-control" id="feedback_mechanism" name="feedback_mechanism"><?php echo set_value('feedback_mechanism', $entry['feedback_mechanism']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="feedback_analysis_report">Feedback Analysis Report</label>
                                                <textarea class="form-control" id="feedback_analysis_report" name="feedback_analysis_report"><?php echo set_value('feedback_analysis_report', $entry['feedback_analysis_report']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="action_taken_report">Action Taken Report</label>
                                                <textarea class="form-control" id="action_taken_report" name="action_taken_report"><?php echo set_value('action_taken_report', $entry['action_taken_report']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_feedback_forms">Document Link (Feedback Forms)</label>
                                                <input type="url" class="form-control" id="document_link_feedback_forms" name="document_link_feedback_forms" value="<?php echo set_value('document_link_feedback_forms', $entry['document_link_feedback_forms']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_analysis_report">Document Link (Analysis Report)</label>
                                                <input type="url" class="form-control" id="document_link_analysis_report" name="document_link_analysis_report" value="<?php echo set_value('document_link_analysis_report', $entry['document_link_analysis_report']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Update</button>
                            <a href="<?php echo base_url('naac/c1_4_feedback_system'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>