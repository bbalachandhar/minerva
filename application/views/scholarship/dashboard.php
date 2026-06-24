<style>
.sch-info-box { background:#fff; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.06); padding:20px; text-align:center; margin-bottom:16px; border-top:3px solid #4f46e5; }
.sch-info-box .sch-value { font-size:22px; font-weight:700; color:#2c3e50; }
.sch-info-box .sch-label { font-size:12px; color:#95a5a6; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
.sch-card { background:#fff; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.06); margin-bottom:20px; overflow:hidden; }
.sch-card-header { padding:16px 20px; border-bottom:1px solid #eee; font-size:15px; font-weight:700; color:#2c3e50; }
.sch-card-header i { color:#4f46e5; margin-right:8px; }
.sch-card-body { padding:20px; }
.sch-detail-row { display:flex; padding:8px 0; border-bottom:1px solid #f5f5f5; font-size:14px; }
.sch-detail-row:last-child { border-bottom:none; }
.sch-detail-label { width:160px; font-weight:600; color:#6c757d; flex-shrink:0; }
.sch-detail-value { color:#2c3e50; }
.sch-exam-card { background:#fff; border:1px solid #eef0f3; border-radius:10px; padding:18px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; transition:all .2s; }
.sch-exam-card:hover { border-color:#4f46e5; box-shadow:0 4px 12px rgba(79,70,229,.1); }
.sch-exam-name { font-size:15px; font-weight:600; color:#2c3e50; }
.sch-exam-date { font-size:12px; color:#95a5a6; margin-top:2px; }
.sch-exam-actions { display:flex; gap:8px; align-items:center; }
.sch-badge { padding:4px 12px; border-radius:20px; font-size:11px; font-weight:600; }
.sch-badge-upcoming { background:#fff3cd; color:#856404; }
.sch-badge-open { background:#d4edda; color:#155724; }
.sch-badge-completed { background:#e8ebf7; color:#4f46e5; }
.sch-badge-closed { background:#f5f5f5; color:#666; }
.btn-sch { background:#4f46e5; color:#fff; border:none; border-radius:6px; padding:7px 16px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; transition:all .2s; }
.btn-sch:hover { background:#4338ca; color:#fff; text-decoration:none; }
.btn-sch-outline { background:transparent; color:#4f46e5; border:1px solid #4f46e5; border-radius:6px; padding:6px 14px; font-size:12px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px; transition:all .2s; }
.btn-sch-outline:hover { background:#4f46e5; color:#fff; text-decoration:none; }
.sch-photo { width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #e8ebf7; }
.sch-empty { text-align:center; padding:40px; color:#adb5bd; }
.sch-empty i { font-size:40px; margin-bottom:10px; display:block; }
</style>

<section class="content-header">
    <h1><i class="fa fa-graduation-cap"></i> Scholarship Exam Portal</h1>
</section>

<section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <!-- Info Boxes -->
    <div class="row">
        <div class="col-md-4">
            <div class="sch-info-box">
                <div class="sch-value"><?php echo htmlspecialchars($applicant_info->reference_no); ?></div>
                <div class="sch-label">Reference Number</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="sch-info-box" style="border-top-color:#27ae60;">
                <div class="sch-value"><?php echo count($assigned_exams); ?></div>
                <div class="sch-label">Assigned Exams</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="sch-info-box" style="border-top-color:#f39c12;">
                <div class="sch-value"><?php echo htmlspecialchars($applicant_info->course_name); ?></div>
                <div class="sch-label">Preferred Course</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Registration Details -->
        <div class="col-md-5">
            <div class="sch-card">
                <div class="sch-card-header"><i class="fa fa-user"></i> Registration Details</div>
                <div class="sch-card-body">
                    <div style="text-align:center; margin-bottom:16px;">
                        <?php
                        if (!empty($applicant_info->image) && file_exists(FCPATH . $applicant_info->image)) {
                            $photo = base_url($applicant_info->image);
                        } elseif (strtolower($applicant_info->gender ?? '') === 'female') {
                            $photo = base_url('uploads/staff_images/default_female.jpg');
                        } else {
                            $photo = base_url('uploads/staff_images/default_male.jpg');
                        }
                        ?>
                        <img src="<?php echo $photo; ?>" class="sch-photo" alt="Photo">
                    </div>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Name</div>
                        <div class="sch-detail-value"><?php echo htmlspecialchars($applicant_info->firstname . ' ' . ($applicant_info->lastname ?? '')); ?></div>
                    </div>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Reference No</div>
                        <div class="sch-detail-value"><strong><?php echo htmlspecialchars($applicant_info->reference_no); ?></strong></div>
                    </div>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Gender</div>
                        <div class="sch-detail-value"><?php echo htmlspecialchars($applicant_info->gender ?? '-'); ?></div>
                    </div>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Mobile</div>
                        <div class="sch-detail-value"><?php echo htmlspecialchars($applicant_info->mobileno ?? '-'); ?></div>
                    </div>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Email</div>
                        <div class="sch-detail-value"><?php echo htmlspecialchars($applicant_info->email ?? '-'); ?></div>
                    </div>
                    <?php if (!empty($applicant_info->dob) && $applicant_info->dob != '0000-00-00'): ?>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Date of Birth</div>
                        <div class="sch-detail-value"><?php echo date('d-m-Y', strtotime($applicant_info->dob)); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Course</div>
                        <div class="sch-detail-value"><?php echo htmlspecialchars($applicant_info->course_name); ?></div>
                    </div>
                    <?php if (!empty($applicant_info->previous_school)): ?>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">School / College</div>
                        <div class="sch-detail-value"><?php echo htmlspecialchars($applicant_info->previous_school); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="sch-detail-row">
                        <div class="sch-detail-label">Registered On</div>
                        <div class="sch-detail-value"><?php echo date('d-m-Y', strtotime($applicant_info->created_at)); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exams -->
        <div class="col-md-7">
            <div class="sch-card">
                <div class="sch-card-header"><i class="fa fa-pencil-square-o"></i> My Exams</div>
                <div class="sch-card-body">
                    <?php if (empty($assigned_exams)): ?>
                    <div class="sch-empty">
                        <i class="fa fa-calendar-check-o"></i>
                        <p>No exams assigned yet. You will be notified when exams are scheduled.</p>
                    </div>
                    <?php else: ?>
                    <?php
                    $now = time();
                    foreach ($assigned_exams as $ex):
                        $from = strtotime($ex->exam_from);
                        $to   = strtotime($ex->exam_to);
                        if ($ex->is_attempted) {
                            $badge_class = 'sch-badge-completed';
                            $badge_text  = 'Completed';
                        } elseif ($now < $from) {
                            $badge_class = 'sch-badge-upcoming';
                            $badge_text  = 'Upcoming';
                        } elseif ($now >= $from && $now <= $to) {
                            $badge_class = 'sch-badge-open';
                            $badge_text  = 'Open Now';
                        } else {
                            $badge_class = 'sch-badge-closed';
                            $badge_text  = 'Closed';
                        }
                    ?>
                    <div class="sch-exam-card">
                        <div>
                            <div class="sch-exam-name"><?php echo htmlspecialchars($ex->exam); ?></div>
                            <div class="sch-exam-date">
                                <i class="fa fa-calendar"></i>
                                <?php echo date('d M Y, h:i A', $from); ?> &mdash; <?php echo date('h:i A', $to); ?>
                                &nbsp;&bull;&nbsp; <i class="fa fa-clock-o"></i> <?php echo $ex->duration; ?>
                            </div>
                        </div>
                        <div class="sch-exam-actions">
                            <span class="sch-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                            <?php if ($badge_text === 'Open Now' && !$ex->is_attempted): ?>
                            <a href="<?php echo base_url('scholarship_dashboard/exam_view/' . $ex->id); ?>" class="btn-sch"><i class="fa fa-play"></i> Take Exam</a>
                            <?php endif; ?>
                            <a href="<?php echo base_url('scholarship_dashboard/hall_ticket/' . $ex->id); ?>" class="btn-sch-outline" target="_blank"><i class="fa fa-id-card"></i> Hall Ticket</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
