<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c2_3_add'); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
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
                                                <label for="course_code">Course Code</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="course_code" name="course_code" value="<?php echo set_value('course_code'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="teacher_name">Teacher Name</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="teacher_name" name="teacher_name" value="<?php echo set_value('teacher_name'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="teaching_methodologies_used">Teaching Methodologies Used</label>
                                                <textarea class="form-control" id="teaching_methodologies_used" name="teaching_methodologies_used"><?php echo set_value('teaching_methodologies_used'); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="ict_tools_used">ICT Tools Used</label>
                                                <textarea class="form-control" id="ict_tools_used" name="ict_tools_used"><?php echo set_value('ict_tools_used'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="percentage_teachers_using_ict">Percentage of Teachers Using ICT</label>
                                                <input type="number" step="0.01" class="form-control" id="percentage_teachers_using_ict" name="percentage_teachers_using_ict" value="<?php echo set_value('percentage_teachers_using_ict'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_teaching_plan">Document Link (Teaching Plan)</label>
                                                <input type="url" class="form-control" id="document_link_teaching_plan" name="document_link_teaching_plan" value="<?php echo set_value('document_link_teaching_plan'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Submit</button>
                            <a href="<?php echo base_url('naac/c2_3_teaching_learning_process'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>