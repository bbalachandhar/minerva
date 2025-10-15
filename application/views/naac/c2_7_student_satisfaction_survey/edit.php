<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c2_7_edit/' . $entry['id']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
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
                                                <label for="survey_methodology">Survey Methodology</label>
                                                <textarea class="form-control" id="survey_methodology" name="survey_methodology"><?php echo set_value('survey_methodology', $entry['survey_methodology']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_students_enrolled">Total Students Enrolled</label>
                                                <input type="number" class="form-control" id="total_students_enrolled" name="total_students_enrolled" value="<?php echo set_value('total_students_enrolled', $entry['total_students_enrolled']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="total_students_surveyed">Total Students Surveyed</label>
                                                <input type="number" class="form-control" id="total_students_surveyed" name="total_students_surveyed" value="<?php echo set_value('total_students_surveyed', $entry['total_students_surveyed']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sss_analysis_report">SSS Analysis Report</label>
                                                <textarea class="form-control" id="sss_analysis_report" name="sss_analysis_report"><?php echo set_value('sss_analysis_report', $entry['sss_analysis_report']); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="action_taken_on_sss">Action Taken on SSS</label>
                                                <textarea class="form-control" id="action_taken_on_sss" name="action_taken_on_sss"><?php echo set_value('action_taken_on_sss', $entry['action_taken_on_sss']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_survey_report">Document Link (Survey Report)</label>
                                                <input type="url" class="form-control" id="document_link_survey_report" name="document_link_survey_report" value="<?php echo set_value('document_link_survey_report', $entry['document_link_survey_report']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Update</button>
                            <a href="<?php echo base_url('naac/c2_7_student_satisfaction_survey'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>