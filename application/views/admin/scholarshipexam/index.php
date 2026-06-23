<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="content-wrapper">
<section class="content-header">
    <h1><i class="fa fa-graduation-cap" style="color:#4f46e5;"></i> Scholarship Exams</h1>
</section>
<section class="content">
    <?php if ($this->session->flashdata('msg')) echo $this->session->flashdata('msg'); ?>

    <div style="margin-bottom:14px;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
            <div>
                <span style="font-size:13px; color:#64748b;">Exams marked as "Scholarship Exam" in Online Exam module appear here.</span>
            </div>
            <div style="display:flex; gap:8px;">
                <a href="<?php echo site_url('admin/onlineexam'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create Exam in Online Exam Module</a>
                <button class="btn btn-default btn-sm" onclick="copyRegLink()"><i class="fa fa-link"></i> Copy Registration Link</button>
            </div>
        </div>
    </div>

    <?php if (empty($exams)): ?>
    <div class="box box-primary">
        <div class="box-body" style="text-align:center; padding:50px;">
            <i class="fa fa-graduation-cap" style="font-size:48px; color:#94a3b8;"></i>
            <h3 style="color:#64748b; margin-top:16px;">No Scholarship Exams Yet</h3>
            <p style="color:#94a3b8;">Go to Online Exam module → Create an exam → Check "Scholarship Exam" checkbox → Select applicable courses.</p>
            <a href="<?php echo site_url('admin/onlineexam'); ?>" class="btn btn-primary" style="margin-top:12px;"><i class="fa fa-plus"></i> Go to Online Exam Module</a>
        </div>
    </div>
    <?php else: ?>

    <div class="row" style="display:flex; flex-wrap:wrap; margin:0 -8px;">
        <?php foreach ($exams as $exam):
            $course_names = [];
            if (!empty($exam->scholarship_courses)) {
                $ids = array_map('intval', explode(',', $exam->scholarship_courses));
                foreach ($ids as $cid) {
                    if (isset($course_map[$cid])) $course_names[] = $course_map[$cid];
                }
            }
            $is_active = $exam->is_active;
            $now = time();
            $from = strtotime($exam->exam_from);
            $to = strtotime($exam->exam_to);
            if ($now < $from) $exam_status = 'Upcoming';
            elseif ($now >= $from && $now <= $to) $exam_status = 'Live';
            else $exam_status = 'Ended';
        ?>
        <div class="col-md-6 col-lg-4" style="padding:8px; display:flex;">
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; width:100%; display:flex; flex-direction:column; overflow:hidden;">
                <div style="padding:20px 20px 12px;">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <h4 style="font-size:16px; font-weight:700; color:#1e293b; margin:0;"><?php echo htmlspecialchars($exam->exam); ?></h4>
                        <?php if ($exam_status == 'Live'): ?>
                            <span style="background:#d1fae5; color:#059669; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px;">LIVE</span>
                        <?php elseif ($exam_status == 'Upcoming'): ?>
                            <span style="background:#e0e7ff; color:#4f46e5; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px;">UPCOMING</span>
                        <?php else: ?>
                            <span style="background:#f1f5f9; color:#64748b; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px;">ENDED</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!$is_active): ?>
                        <span style="color:#ef4444; font-size:11px; font-weight:600;"><i class="fa fa-eye-slash"></i> Not Published</span>
                    <?php endif; ?>
                </div>

                <div style="padding:0 20px 16px; flex:1; font-size:13px; color:#475569;">
                    <div style="margin-bottom:6px;"><i class="fa fa-calendar" style="width:16px; color:#94a3b8;"></i> <?php echo date('d M Y, h:i A', strtotime($exam->exam_from)); ?> — <?php echo date('d M Y, h:i A', strtotime($exam->exam_to)); ?></div>
                    <div style="margin-bottom:6px;"><i class="fa fa-clock-o" style="width:16px; color:#94a3b8;"></i> Duration: <?php echo $exam->duration; ?></div>
                    <div style="margin-bottom:8px;"><i class="fa fa-users" style="width:16px; color:#94a3b8;"></i> <strong><?php echo $exam->candidate_count; ?></strong> registered candidate(s)</div>

                    <?php if (!empty($course_names)): ?>
                    <div style="margin-top:8px;">
                        <?php foreach ($course_names as $cn): ?>
                            <span style="display:inline-block; background:#f1f5f9; color:#475569; font-size:10px; padding:2px 8px; border-radius:10px; margin:2px 2px 2px 0;"><?php echo htmlspecialchars($cn); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="border-top:1px solid #f1f5f9; padding:12px 20px; display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="<?php echo site_url('admin/scholarshipexam/candidates/' . $exam->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-users"></i> Candidates</a>
                    <a href="<?php echo site_url('admin/onlineexam#exam_' . $exam->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-pencil"></i> Edit Exam</a>
                    <a href="<?php echo site_url('admin/onlineexam/assign/' . $exam->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-list"></i> Questions</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</section>
</div>

<script>
function copyRegLink() {
    var link = '<?php echo site_url("scholarship_register"); ?>';
    if (navigator.clipboard) {
        navigator.clipboard.writeText(link).then(function() {
            swal({ title: 'Link Copied!', html: '<code>' + link + '</code><br><br>Share this link with candidates for scholarship exam registration.', type: 'success' });
        });
    } else {
        prompt('Copy this registration link:', link);
    }
}
</script>
