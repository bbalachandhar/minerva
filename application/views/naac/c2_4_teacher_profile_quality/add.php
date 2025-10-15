<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c2_4_add'); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
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
                                                <label for="teacher_name">Teacher Name</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="teacher_name" name="teacher_name" value="<?php echo set_value('teacher_name'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="highest_qualification">Highest Qualification</label>
                                                <input type="text" class="form-control" id="highest_qualification" name="highest_qualification" value="<?php echo set_value('highest_qualification'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="years_of_experience">Years of Experience</label>
                                                <input type="number" class="form-control" id="years_of_experience" name="years_of_experience" value="<?php echo set_value('years_of_experience'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="phd_status">PhD Status</label>
                                                <select class="form-control" id="phd_status" name="phd_status">
                                                    <option value="">Select</option>
                                                    <option value="Yes" <?php echo set_select('phd_status', 'Yes'); ?>>Yes</option>
                                                    <option value="No" <?php echo set_select('phd_status', 'No'); ?>>No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="professional_development_activities">Professional Development Activities</label>
                                                <textarea class="form-control" id="professional_development_activities" name="professional_development_activities"><?php echo set_value('professional_development_activities'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_cv">Document Link (CV)</label>
                                                <input type="url" class="form-control" id="document_link_cv" name="document_link_cv" value="<?php echo set_value('document_link_cv'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Submit</button>
                            <a href="<?php echo base_url('naac/c2_4_teacher_profile_quality'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>