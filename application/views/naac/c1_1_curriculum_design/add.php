<div class="content-wrapper" style="min-height: 408px;">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form action="<?php echo site_url('naac/c1_1_add'); ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
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
                                                <label for="course_title">Course Title</label><small class="req"> *</small>
                                                <input type="text" class="form-control" id="course_title" name="course_title" value="<?php echo set_value('course_title'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="po_pso_co_relevance">PO/PSO/CO Relevance</label>
                                                <textarea class="form-control" id="po_pso_co_relevance" name="po_pso_co_relevance"><?php echo set_value('po_pso_co_relevance'); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="curriculum_development_process">Curriculum Development Process</label>
                                                <textarea class="form-control" id="curriculum_development_process" name="curriculum_development_process"><?php echo set_value('curriculum_development_process'); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="curriculum_revision_date">Curriculum Revision Date</label>
                                                <input type="date" class="form-control" id="curriculum_revision_date" name="curriculum_revision_date" value="<?php echo set_value('curriculum_revision_date'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_syllabus">Document Link (Syllabus)</label>
                                                <input type="url" class="form-control" id="document_link_syllabus" name="document_link_syllabus" value="<?php echo set_value('document_link_syllabus'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_link_minutes">Document Link (Minutes)</label>
                                                <input type="url" class="form-control" id="document_link_minutes" name="document_link_minutes" value="<?php echo set_value('document_link_minutes'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Submit</button>
                            <a href="<?php echo base_url('naac/c1_1_curriculum_design'); ?>" class="btn btn-default pull-right mr-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>