<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c1_3_edit/' . $entry['id']); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
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
                                                <label for="program_name">Program Name</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="program_name" name="program_name" value="<?php echo set_value('program_name', $entry['program_name']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="cross_cutting_issues_integrated">Cross-Cutting Issues Integrated</label>
                                                <textarea class="form-control" id="cross_cutting_issues_integrated" name="cross_cutting_issues_integrated"><?php echo set_value('cross_cutting_issues_integrated', $entry['cross_cutting_issues_integrated']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="value_added_courses_offered">Value-Added Courses Offered</label>
                                                <input type="number" class="form-control" id="value_added_courses_offered" name="value_added_courses_offered" value="<?php echo set_value('value_added_courses_offered', $entry['value_added_courses_offered']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="students_enrolled_value_added">Students Enrolled in Value-Added Courses</label>
                                                <input type="number" class="form-control" id="students_enrolled_value_added" name="students_enrolled_value_added" value="<?php echo set_value('students_enrolled_value_added', $entry['students_enrolled_value_added']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="project_field_work_details">Project/Field Work Details</label>
                                                <textarea class="form-control" id="project_field_work_details" name="project_field_work_details"><?php echo set_value('project_field_work_details', $entry['project_field_work_details']); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_value_added_syllabus">Document Link (Value-Added Syllabus)</label>
                                                <input type="url" class="form-control" id="document_link_value_added_syllabus" name="document_link_value_added_syllabus" value="<?php echo set_value('document_link_value_added_syllabus', $entry['document_link_value_added_syllabus']); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_project_reports">Document Link (Project Reports)</label>
                                                <input type="url" class="form-control" id="document_link_project_reports" name="document_link_project_reports" value="<?php echo set_value('document_link_project_reports', $entry['document_link_project_reports']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Update</button>
                            <a href="<?php echo base_url('naac/c1_3_curriculum_enrichment'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>