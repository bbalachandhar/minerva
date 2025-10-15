<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c1_2_add'); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
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
                                                <input type="text" class="form-control" id="academic_year" name="academic_year" value="<?php echo set_value('academic_year'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="program_name">Program Name</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="program_name" name="program_name" value="<?php echo set_value('program_name'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="elective_courses_offered">Elective Courses Offered</label>
                                                <input type="number" class="form-control" id="elective_courses_offered" name="elective_courses_offered" value="<?php echo set_value('elective_courses_offered'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="interdisciplinary_courses_offered">Interdisciplinary Courses Offered</label>
                                                <input type="number" class="form-control" id="interdisciplinary_courses_offered" name="interdisciplinary_courses_offered" value="<?php echo set_value('interdisciplinary_courses_offered'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="credit_transfer_details">Credit Transfer Details</label>
                                                <textarea class="form-control" id="credit_transfer_details" name="credit_transfer_details"><?php echo set_value('credit_transfer_details'); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="experiential_learning_details">Experiential Learning Details</label>
                                                <textarea class="form-control" id="experiential_learning_details" name="experiential_learning_details"><?php echo set_value('experiential_learning_details'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="students_undertaking_internships">Students Undertaking Internships</label>
                                                <input type="number" class="form-control" id="students_undertaking_internships" name="students_undertaking_internships" value="<?php echo set_value('students_undertaking_internships'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_electives">Document Link (Electives)</label>
                                                <input type="url" class="form-control" id="document_link_electives" name="document_link_electives" value="<?php echo set_value('document_link_electives'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_internship_policy">Document Link (Internship Policy)</label>
                                                <input type="url" class="form-control" id="document_link_internship_policy" name="document_link_internship_policy" value="<?php echo set_value('document_link_internship_policy'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Submit</button>
                            <a href="<?php echo base_url('naac/c1_2_academic_flexibility'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>