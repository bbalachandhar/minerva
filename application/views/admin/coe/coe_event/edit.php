<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-pencil-square-o"></i> Edit Exam Event</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li><a href="<?php echo site_url('coe/coe_event'); ?>"><i class="fa fa-arrow-left"></i> Exam Events</a></li>
            <li class="active">Edit</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit: <?php echo htmlspecialchars($event->name); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('coe/coe_event'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <form method="post" action="<?php echo site_url('coe/coe_event/update/' . $event->id); ?>">
                        <div class="box-body">

                            <!-- Event Name -->
                            <div class="form-group">
                                <label for="name">Event Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="<?php echo htmlspecialchars($event->name); ?>" required maxlength="250">
                            </div>

                            <div class="row">
                                <!-- Exam Category -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_category">Category <span class="text-danger">*</span></label>
                                        <select name="exam_category" id="exam_category" class="form-control" required>
                                            <option value="main"          <?php echo ($event->exam_category === 'main')          ? 'selected' : ''; ?>>Main / Regular</option>
                                            <option value="arrear"        <?php echo ($event->exam_category === 'arrear')        ? 'selected' : ''; ?>>Arrear</option>
                                            <option value="supplementary" <?php echo ($event->exam_category === 'supplementary') ? 'selected' : ''; ?>>Supplementary</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Exam Mode -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_type">Mode <span class="text-danger">*</span></label>
                                        <select name="exam_type" id="exam_type" class="form-control" required>
                                            <option value="theory"    <?php echo ($event->exam_type === 'theory')    ? 'selected' : ''; ?>>Theory</option>
                                            <option value="practical" <?php echo ($event->exam_type === 'practical') ? 'selected' : ''; ?>>Practical</option>
                                            <option value="project"   <?php echo ($event->exam_type === 'project')   ? 'selected' : ''; ?>>Project</option>
                                            <option value="viva"      <?php echo ($event->exam_type === 'viva')      ? 'selected' : ''; ?>>Viva</option>
                                            <option value="online"    <?php echo ($event->exam_type === 'online')    ? 'selected' : ''; ?>>Online</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Description <span class="text-muted">(optional)</span></label>
                                <textarea name="description" id="description" class="form-control" rows="2"><?php echo htmlspecialchars($event->description ?? ''); ?></textarea>
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-save"></i> Save Changes
                            </button>
                            <a href="<?php echo site_url('coe/coe_event/manage/' . $event->id); ?>" class="btn btn-default">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
