<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-calendar-plus-o"></i> Create Exam Event</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li><a href="<?php echo site_url('coe/coe_event'); ?>"><i class="fa fa-arrow-left"></i> Exam Events</a></li>
            <li class="active">Create</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">New Exam Event</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('coe/coe_event'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <form method="post" action="<?php echo site_url('coe/coe_event/save'); ?>" id="event-form">
                        <div class="box-body">

                            <!-- Session -->
                            <div class="form-group">
                                <label for="session_id">Academic Session <span class="text-danger">*</span></label>
                                <select name="session_id" id="session_id" class="form-control" required>
                                    <option value="">— Select Session —</option>
                                    <?php foreach ($session_list as $s): ?>
                                        <option value="<?php echo $s['id']; ?>"
                                            <?php echo ($s['id'] == ($this->input->get('session_id') ?: $current_session)) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['session']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="help-block">All batches in this event will default to this session.</p>
                            </div>

                            <!-- Event Name -->
                            <div class="form-group">
                                <label for="name">Event Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                       placeholder="e.g. Nov/Dec 2026 End Semester"
                                       value="<?php echo set_value('name'); ?>" required maxlength="250">
                                <p class="help-block">A descriptive name for this exam event (appears in all sub-modules).</p>
                            </div>

                            <div class="row">
                                <!-- Exam Category -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_category">Category <span class="text-danger">*</span></label>
                                        <select name="exam_category" id="exam_category" class="form-control" required>
                                            <option value="">— Select —</option>
                                            <option value="main"          <?php echo (set_value('exam_category') === 'main')          ? 'selected' : ''; ?>>Main / Regular</option>
                                            <option value="arrear"        <?php echo (set_value('exam_category') === 'arrear')        ? 'selected' : ''; ?>>Arrear</option>
                                            <option value="supplementary" <?php echo (set_value('exam_category') === 'supplementary') ? 'selected' : ''; ?>>Supplementary</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Exam Type (Mode) -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_type">Mode <span class="text-danger">*</span></label>
                                        <select name="exam_type" id="exam_type" class="form-control" required>
                                            <option value="">— Select —</option>
                                            <option value="theory"      <?php echo (set_value('exam_type') === 'theory')      ? 'selected' : ''; ?>>Theory</option>
                                            <option value="practical"   <?php echo (set_value('exam_type') === 'practical')   ? 'selected' : ''; ?>>Practical</option>
                                            <option value="project"     <?php echo (set_value('exam_type') === 'project')     ? 'selected' : ''; ?>>Project</option>
                                            <option value="viva"        <?php echo (set_value('exam_type') === 'viva')        ? 'selected' : ''; ?>>Viva</option>
                                            <option value="online"      <?php echo (set_value('exam_type') === 'online')      ? 'selected' : ''; ?>>Online</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Description (optional) -->
                            <div class="form-group">
                                <label for="description">Description <span class="text-muted">(optional)</span></label>
                                <textarea name="description" id="description" class="form-control" rows="2"
                                          placeholder="Any notes about this event…"><?php echo set_value('description'); ?></textarea>
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-arrow-right"></i> Create &amp; Add Batch Exams
                            </button>
                            <a href="<?php echo site_url('coe/coe_event'); ?>" class="btn btn-default">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
