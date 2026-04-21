
<section class="content-header">
    <h1><i class="fa fa-pencil-square-o"></i> Online Exam</h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo base_url('public_admission/applicant_dashboard'); ?>"><i class="fa fa-home"></i> Dashboard</a></li>
        <li><a href="<?php echo site_url('public_admission/exam_list'); ?>">Online Exams</a></li>
        <li class="active"><?php echo htmlspecialchars($exam->exam); ?></li>
    </ol>
</section>

<style>
    /* Question map floats fixed on the right */
    .question-map-float {
        position: fixed;
        top: 60px;
        right: 0;
        width: 220px;
        z-index: 999;
        background: #fff;
        border: 1px solid #ddd;
        border-right: none;
        border-radius: 4px 0 0 4px;
        box-shadow: -3px 3px 10px rgba(0,0,0,0.15);
        transition: transform 0.3s ease;
    }
    .question-map-float.map-collapsed {
        transform: translateX(220px);
    }
    /* Push main content away from the floating panel */
    .content-wrapper {
        padding-right: 230px !important;
    }
    .question-map-header {
        background: #367fa9;
        color: #fff;
        padding: 8px 10px;
        border-radius: 4px 0 0 0;
        font-weight: bold;
        font-size: 13px;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .question-map-toggle-btn {
        position: fixed;
        top: 60px;
        right: 0;
        z-index: 1000;
        background: #367fa9;
        color: #fff;
        border: none;
        border-radius: 4px 0 0 4px;
        padding: 8px 6px;
        cursor: pointer;
        font-size: 13px;
        display: none; /* shown when map collapses */
        box-shadow: -2px 2px 6px rgba(0,0,0,0.2);
    }
    .map-buttons-grid {
        padding: 8px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .map-buttons-grid .question_switcher {
        width: 36px;
        height: 36px;
        padding: 0;
        font-size: 12px;
        font-weight: bold;
        border-radius: 4px;
    }
    button.question_switcher.btn-success {
        background-color: #5cb85c;
        border-color: #4cae4c;
        color: #fff;
    }
    /* Timer bar inside floating panel */
    .exam-timer-bar {
        padding: 6px 10px;
        background: #f4f4f4;
        border-bottom: 1px solid #ddd;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .exam-timer-bar.timer-warning { background: #fcf8e3; color: #8a6d3b; }
    .exam-timer-bar.timer-danger  { background: #f2dede; color: #a94442; }
    /* === EXAM MODE: Sidebar hidden so user cannot accidentally navigate away === */
    .main-sidebar  { display: none !important; }
    .sidebar-toggle { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
    /* Make logo non-clickable and show exam context text */
    .main-header .logo .logo-mini b { font-size: 11px; letter-spacing: 0; }
    .main-header .logo .logo-lg b   { font-size: 13px; font-weight: bold; }
    /* Hide the inline col-md-3 map rendered by the partial */
    #questiondata .col-md-3 { display: none !important; }
    /* Expand question col to full width */
    #questiondata .col-md-9 { width: 100% !important; }
</style>

<section class="content">
<div class="row">
    <div class="col-md-12">

            <?php
/* Determine effective publish status (same logic as user/onlineexam/view.php) */
$show_result = false;
$show_result_no_answers = false;
if ($online_exam_validate->is_attempted) {
    if ($exam->is_quiz && !empty($exam->show_result_immediately)) {
        $show_result = true;
    } elseif (!empty($exam->publish_result_no_answers)) {
        $show_result_no_answers = true;
    } elseif ($exam->publish_result) {
        $show_result = true;
    }
}
?>

            <?php if ($online_exam_validate->is_attempted && $show_result_no_answers): ?>
                <!-- ===== RESULT WITHOUT ANSWERS VIEW ===== -->
                <?php
                $exam_total_marks2  = 0;
                $exam_total_scored2 = 0;
                if (!empty($question_result)) {
                    foreach ($question_result as $qr2) {
                        $exam_total_marks2 += (float)$qr2->marks;
                        if ((float)$qr2->score_marks > 0) {
                            $exam_total_scored2 += (float)$qr2->score_marks;
                        } elseif (!empty($qr2->select_option) && $qr2->select_option == $qr2->correct) {
                            $exam_total_scored2 += (float)$qr2->marks;
                        }
                    }
                }
                $score_pct2 = $exam_total_marks2 > 0 ? round(($exam_total_scored2 / $exam_total_marks2) * 100, 1) : 0;
                $sch_logo   = isset($sch_setting->logo) ? $sch_setting->logo : '';
                $sch_nm     = $sch_name ?? 'Meenakshi College Of Engineering';
                ?>
                <style>
                .mset-result-card { max-width:700px; margin:0 auto; font-family:Arial,sans-serif; }
                .mset-header { text-align:center; border-bottom:3px solid #1a5c84; padding-bottom:14px; margin-bottom:20px; }
                .mset-header img { height:70px; display:block; margin:0 auto 8px; }
                .mset-header h2 { color:#1a5c84; font-size:20px; margin:0 0 4px; }
                .mset-header p  { color:#555; font-size:13px; margin:0; }
                .mset-congrats { background:linear-gradient(135deg,#1a5c84,#2980b9); color:#fff; border-radius:8px; padding:22px 20px; text-align:center; margin-bottom:24px; }
                .mset-congrats h3 { font-size:24px; margin:0 0 6px; }
                .mset-congrats p  { font-size:14px; margin:0; opacity:.9; }
                .mset-score-box { background:#f0f8ff; border:2px solid #1a5c84; border-radius:8px; padding:20px; text-align:center; margin-bottom:24px; }
                .mset-score-box .pct { font-size:52px; font-weight:bold; color:#1a5c84; line-height:1; }
                .mset-score-box .lbl { font-size:14px; color:#555; margin-top:6px; }
                .mset-score-box .raw { font-size:16px; color:#333; margin-top:8px; }
                .mset-info-box { background:#fff; border:1px solid #ddd; border-radius:6px; padding:16px 20px; margin-bottom:24px; font-size:14px; line-height:1.8; }
                .mset-info-box p { margin:0 0 8px; }
                .mset-info-box a { color:#1a5c84; font-weight:bold; }
                .mset-contact { background:#fff8e1; border-left:4px solid #f0ad4e; padding:12px 16px; border-radius:4px; font-size:13px; color:#555; }
                .mset-contact strong { color:#333; }
                </style>
                <div class="mset-result-card">
                    <div class="mset-header">
                        <?php if (!empty($sch_logo)): ?>
                        <img src="<?php echo base_url('uploads/logos/' . $sch_logo); ?>" alt="Logo">
                        <?php endif; ?>
                        <h2><?php echo htmlspecialchars($sch_nm); ?></h2>
                        <p><?php echo htmlspecialchars($exam->exam); ?> — Result</p>
                    </div>

                    <div class="mset-congrats">
                        <h3>&#127881; Congratulations!</h3>
                        <p>You have successfully completed the <?php echo htmlspecialchars($exam->exam); ?>.</p>
                    </div>

                    <div class="mset-score-box">
                        <div class="pct"><?php echo number_format($score_pct2, 1); ?>%</div>
                        <div class="lbl">Your Score Percentage</div>
                        <div class="raw"><?php echo $exam_total_scored2; ?> out of <?php echo $exam_total_marks2; ?> marks</div>
                    </div>

                    <div class="mset-info-box">
                        <p>The result of <strong><?php echo htmlspecialchars($exam->exam); ?></strong> conducted at <strong><?php echo htmlspecialchars($sch_nm); ?></strong> on 19th April has been published on 22nd April 2026.</p>
                        <p><strong>Candidate:</strong> <?php echo htmlspecialchars(($applicant['firstname'] ?? '') . ' ' . ($applicant['lastname'] ?? '')); ?></p>
                        <p><strong>Reference No:</strong> <?php echo htmlspecialchars($applicant['reference_no'] ?? ''); ?></p>
                    </div>

                    <div class="mset-contact">
                        <strong>Note:</strong> For details of the eligible scholarship amount, candidates may contact<br>
                        <strong>Admission Head @ 8925977077</strong>
                    </div>
                </div>
                <br>
            <?php elseif ($online_exam_validate->is_attempted && $show_result): ?>
                <!-- ===== RESULT VIEW (WITH ANSWERS) ===== -->
                <?php
                $total_questions = 0;
                $exam_total_marks = 0;
                $exam_total_scored = 0;
                $correct_ans = 0;
                $questionOpt = $this->customlib->getQuesOption();
                // Escape < chars that are NOT a recognised HTML tag (e.g. math 3<x<5, chemistry Al<Ga).
                // Only allow through tags that CKEditor actually produces; everything else (e.g. <x, <y)
                // gets escaped to &lt; so the browser never treats it as markup.
                $safe_html = function($text) {
                    $known = 'a|abbr|b|blockquote|br|caption|cite|code|col|colgroup|div|em|h[1-6]|hr|i|img|li|ol|p|pre|s|span|strong|sub|sup|table|tbody|td|tfoot|th|thead|tr|u|ul';
                    return preg_replace('/<(?!\/?(?:' . $known . ')\b)(?!!--)/i', '&lt;', $text);
                };
                if (!empty($question_result)) {
                    $total_questions = count($question_result);
                    foreach ($question_result as $qr) {
                        $exam_total_marks += (float)$qr->marks;
                        // If stored score_marks is non-zero use it, else calculate from correct answer
                        if ((float)$qr->score_marks > 0) {
                            $exam_total_scored += (float)$qr->score_marks;
                        } elseif (!empty($qr->select_option) && $qr->select_option == $qr->correct) {
                            $exam_total_scored += (float)$qr->marks;
                        }
                        if ($qr->question_type == 'singlechoice' || $qr->question_type == 'true_false') {
                            if ($qr->select_option == $qr->correct) $correct_ans++;
                        }
                    }
                }
                ?>
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-trophy"></i> Your Result — <?php echo htmlspecialchars($exam->exam); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row text-center" style="margin-bottom:20px;">
                            <div class="col-sm-3">
                                <div class="info-box bg-aqua">
                                    <span class="info-box-icon"><i class="fa fa-question-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Questions</span>
                                        <span class="info-box-number"><?php echo $total_questions; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="info-box bg-green">
                                    <span class="info-box-icon"><i class="fa fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Correct</span>
                                        <span class="info-box-number"><?php echo $correct_ans; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="info-box bg-yellow">
                                    <span class="info-box-icon"><i class="fa fa-star"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Your Score</span>
                                        <span class="info-box-number"><?php echo $exam_total_scored; ?> / <?php echo $exam_total_marks; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="info-box bg-navy">
                                    <span class="info-box-icon"><i class="fa fa-percent"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Score %</span>
                                        <span class="info-box-number"><?php echo $exam_total_marks > 0 ? number_format(($exam_total_scored / $exam_total_marks) * 100, 1) : 0; ?>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($question_result)): ?>
                        <h4 style="margin-bottom:15px;"><i class="fa fa-file-text-o"></i> Your Answer Sheet</h4>
                        <div class="legend-row" style="margin-bottom:12px; font-size:13px;">
                            <span style="color:#5cb85c;"><i class="fa fa-check-circle"></i> Correct</span> &nbsp;&nbsp;
                            <span style="color:#d9534f;"><i class="fa fa-times-circle"></i> Wrong</span> &nbsp;&nbsp;
                            <span style="color:#aaa;"><i class="fa fa-minus-circle"></i> Not Attempted</span>
                        </div>
                        <?php foreach ($question_result as $qi => $qr): ?>
                            <?php
                            $is_correct = ($qr->question_type == 'singlechoice' || $qr->question_type == 'true_false') && $qr->select_option == $qr->correct;
                            $not_attempted = empty($qr->select_option);
                            $border_color = $not_attempted ? '#aaa' : ($is_correct ? '#5cb85c' : '#d9534f');
                            $icon = $not_attempted ? '<i class="fa fa-minus-circle" style="color:#aaa"></i>' : ($is_correct ? '<i class="fa fa-check-circle" style="color:#5cb85c"></i>' : '<i class="fa fa-times-circle" style="color:#d9534f"></i>');
                            ?>
                            <div style="border-left:4px solid <?php echo $border_color; ?>; padding:10px 14px; margin-bottom:12px; background:#fafafa; border-radius:3px;">
                                <div style="margin-bottom:6px;">
                                    <strong>Q<?php echo $qi + 1; ?>.</strong> <?php echo $icon; ?>
                                    <span style="font-size:12px; color:#777; margin-left:8px;">(<?php echo $qr->marks; ?> mark<?php echo $qr->marks != 1 ? 's' : ''; ?>)</span>
                                    <div style="margin-top:4px;"><?php echo $safe_html($qr->question); ?></div>
                                </div>
                                <?php if ($qr->question_type == 'singlechoice' || $qr->question_type == 'multichoice'): ?>
                                    <div class="row" style="font-size:13px; margin-top:6px;">
                                        <?php foreach ($questionOpt as $opt_key => $opt_label): ?>
                                            <?php if (!empty($qr->$opt_key)): ?>
                                                <?php
                                                $isYourAnswer = ($qr->select_option == $opt_key);
                                                $isCorrect = ($qr->correct == $opt_key);
                                                $optStyle = '';
                                                $optIcon  = '';
                                                if ($isYourAnswer && $isCorrect) {
                                                    $optStyle = 'background:#dff0d8; border-radius:3px; padding:2px 6px;';
                                                    $optIcon  = ' <i class="fa fa-check" style="color:#3c763d"></i> <em style="color:#3c763d">(Your answer — Correct)</em>';
                                                } elseif ($isYourAnswer && !$isCorrect) {
                                                    $optStyle = 'background:#f2dede; border-radius:3px; padding:2px 6px;';
                                                    $optIcon  = ' <i class="fa fa-times" style="color:#a94442"></i> <em style="color:#a94442">(Your answer — Wrong)</em>';
                                                } elseif ($isCorrect) {
                                                    $optStyle = 'background:#dff0d8; border-radius:3px; padding:2px 6px;';
                                                    $optIcon  = ' <i class="fa fa-check" style="color:#3c763d"></i> <em style="color:#3c763d">(Correct Answer)</em>';
                                                }
                                                ?>
                                                <div class="col-sm-6" style="margin-bottom:4px;">
                                                    <span style="<?php echo $optStyle; ?>">
                                                        <strong><?php echo $opt_label; ?>.</strong> <?php echo $safe_html(html_entity_decode($qr->$opt_key)); ?><?php echo $optIcon; ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif ($qr->question_type == 'true_false'): ?>
                                    <div style="font-size:13px; margin-top:6px;">
                                        <?php foreach (['True' => 'True', 'False' => 'False'] as $tf_val => $tf_label): ?>
                                            <?php
                                            $isYA = ($qr->select_option == $tf_val);
                                            $isCr = ($qr->correct == $tf_val);
                                            $s = ''; $em = '';
                                            if ($isYA && $isCr)  { $s = 'background:#dff0d8; border-radius:3px; padding:2px 8px;'; $em = ' <em style="color:#3c763d">(Your answer — Correct)</em>'; }
                                            elseif ($isYA)        { $s = 'background:#f2dede; border-radius:3px; padding:2px 8px;'; $em = ' <em style="color:#a94442">(Your answer — Wrong)</em>'; }
                                            elseif ($isCr)        { $s = 'background:#dff0d8; border-radius:3px; padding:2px 8px;'; $em = ' <em style="color:#3c763d">(Correct Answer)</em>'; }
                                            ?>
                                            <span style="<?php echo $s; ?> margin-right:12px;"><?php echo $tf_label; ?><?php echo $em; ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="font-size:13px; margin-top:4px;">
                                        <strong>Your Answer:</strong> <?php echo !empty($qr->select_option) ? html_entity_decode($qr->select_option) : '<em style="color:#aaa">Not attempted</em>'; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($online_exam_validate->is_attempted && !$show_result): ?>
                <div class="alert alert-info">
                    <i class="fa fa-clock-o"></i>
                    <strong>Exam Submitted.</strong> Your result will be published by the institution. Please check back later.
                </div>

            <?php else: ?>
            <form id="exam_form" method="post" action="<?php echo site_url('public_admission/save_applicant_exam'); ?>" enctype="multipart/form-data">
                <?php echo $this->customlib->getCSRF(); ?>
                <div id="questiondata"></div>
                <div id="wait" style="display:none;">Please wait while loading questions...</div>
            </form>
            <?php endif; ?>
    </div><!-- /.col-md-12 -->
</div><!-- /.row -->
</section><!-- /.content -->

<script src="<?php echo base_url(); ?>backend/plugins/ckeditor/ckeditor.js"></script>
<script src="<?php echo base_url(); ?>backend/js/ckeditor_config.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/ckeditor/adapters/jquery.js"></script>
<script src="<?php echo base_url(); ?>backend/sweet-alert/sweetalert2.min.js"></script>

<script type="text/javascript">
(function($) {
    "use strict";

    var examid = '<?php echo $exam->id; ?>';
    var isAttempted = <?php echo (int)$online_exam_validate->is_attempted; ?>;
    // Server-calculated remaining seconds (exam_to - now)
    var examEndTimestamp = <?php echo strtotime($exam->exam_to); ?>;
    var timerInterval = null;

    $(document).ready(function() {
        // Exam mode: collapse sidebar so user cannot accidentally click menu links
        $('body').addClass('sidebar-collapse');

        // Replace logo text so user knows they are in exam mode
        $('.main-header .logo .logo-mini b').text('EXAM');
        $('.main-header .logo .logo-lg b').html('<i class="fa fa-pencil-square-o"></i> Exam In Progress');

        if (isAttempted === 1) {
            $('#questiondata').html('<div class="alert alert-info">This exam has already been submitted and cannot be attempted again.</div>');
            $('#submit_btn_wrapper').hide();
            return;
        }
        loadExam();
    });

    function loadExam() {
        $.ajax({
            type: 'POST',
            url: '<?php echo site_url('public_admission/getApplicantExamForm'); ?>',
            data: {'recordid': examid},
            dataType: 'json',
            beforeSend: function() {
                $('#wait').show();
            },
            success: function(response) {
                if (response.status == 0) {
                    $('#questiondata').html(response.page);
                    buildFloatingMap();
                } else {
                    $('#questiondata').html('<div class="alert alert-danger">' + (response.message || 'Unable to load exam.') + '</div>');
                    $('#submit_btn_wrapper').hide();
                }
            },
            complete: function() {
                $('#wait').hide();
            }
        });
    }

    function buildFloatingMap() {
        // Collect buttons from the partial-rendered map
        var $sourceButtons = $('#questiondata .question_switcher');
        if (!$sourceButtons.length) return;

        // Build floating panel
        var $panel = $('<div class="question-map-float" id="floatingMapPanel">' +
            '<div class="question-map-header" id="mapPanelHeader">' +
                '<span><i class="fa fa-th"></i> Question Map</span>' +
                '<i class="fa fa-chevron-right" id="mapCollapseIcon"></i>' +
            '</div>' +
            '<div class="exam-timer-bar" id="examTimerBar"><i class="fa fa-clock-o"></i> <span id="examTimerDisplay">--:--:--</span></div>' +
            '<div class="map-buttons-grid" id="floatingMapGrid"></div>' +
            '<div style="padding:8px;border-top:1px solid #ddd;">' +
                '<button type="button" id="floatingSubmitBtn" class="btn btn-primary btn-block btn-sm">' +
                    '<i class="fa fa-paper-plane"></i> Submit Exam' +
                '</button>' +
            '</div>' +
        '</div>');

        var $toggleBtn = $('<button class="question-map-toggle-btn" id="mapExpandBtn" title="Show Question Map">' +
            '<i class="fa fa-th"></i>' +
        '</button>');

        $('body').append($panel).append($toggleBtn);

        // Clone buttons into the floating grid
        $sourceButtons.each(function() {
            var $clone = $(this).clone(true);
            $clone.addClass('question_switcher');
            $('#floatingMapGrid').append($clone);
        });

        // Floating submit triggers the main form
        $(document).on('click', '#floatingSubmitBtn', function() {
            $('#exam_form').submit();
        });

        // Start countdown timer
        startTimer();

        // Collapse/expand toggle
        $('#mapPanelHeader').on('click', function() {
            $('#floatingMapPanel').addClass('map-collapsed');
            $('#mapCollapseIcon').removeClass('fa-chevron-right').addClass('fa-chevron-left');
            $('#mapExpandBtn').show();
            $('.content-wrapper').css('padding-right', '');
        });

        $('#mapExpandBtn').on('click', function() {
            $('#floatingMapPanel').removeClass('map-collapsed');
            $('#mapCollapseIcon').removeClass('fa-chevron-left').addClass('fa-chevron-right');
            $(this).hide();
            $('.content-wrapper').css('padding-right', '230px');
        });
    }

    // Mark answered — update both inline and floating maps
    $(document).on('change', 'input[type="radio"], input[type="checkbox"]', function() {
        var name = $(this).attr('name');
        var qno;
        if (name && name.indexOf('radio') === 0) {
            qno = name.replace('radio', '');
        } else if (name && name.indexOf('checkbox') === 0) {
            qno = name.replace('checkbox', '').replace('[]', '');
        }
        if (qno) {
            $('button.question_switcher[data-qustion_no="' + qno + '"]')
                .removeClass('btn-default').addClass('btn-success');
        }
    });

    // Scroll to question when map button clicked
    $(document).on('click', '.question_switcher', function() {
        var qno = $(this).data('qustion_no');
        var target = $('#question_' + qno);
        if (target.length) {
            $('html, body').animate({ scrollTop: target.offset().top - 80 }, 300);
        }
    });

    function startTimer() {
        function tick() {
            var now = Math.floor(Date.now() / 1000);
            var remaining = examEndTimestamp - now;

            if (remaining <= 0) {
                clearInterval(timerInterval);
                $('#examTimerDisplay').text('00:00:00');
                $('#examTimerBar').removeClass('timer-warning').addClass('timer-danger');
                // Auto-submit without confirm dialog
                doSubmitExam(true);
                return;
            }

            var h = Math.floor(remaining / 3600);
            var m = Math.floor((remaining % 3600) / 60);
            var s = remaining % 60;
            var display = (h < 10 ? '0' : '') + h + ':' +
                          (m < 10 ? '0' : '') + m + ':' +
                          (s < 10 ? '0' : '') + s;
            $('#examTimerDisplay').text(display);

            // Warning: under 10 minutes
            if (remaining <= 600 && remaining > 120) {
                $('#examTimerBar').addClass('timer-warning').removeClass('timer-danger');
            }
            // Danger: under 2 minutes
            if (remaining <= 120) {
                $('#examTimerBar').removeClass('timer-warning').addClass('timer-danger');
            }
        }

        tick(); // run immediately
        timerInterval = setInterval(tick, 1000);
    }

    // Warn before leaving page while exam is in progress
    var examSubmitting = false;
    var dashboardUrl   = '<?php echo site_url("public_admission/applicant_dashboard"); ?>';

    window.onbeforeunload = function() {
        if (!examSubmitting && !isAttempted) {
            return 'Your exam is in progress. Are you sure you want to leave this page?';
        }
    };

    function doSubmitExam(autoSubmit) {
        if (examSubmitting) return;
        if (!autoSubmit) {
            swal({
                title:             'Submit Exam?',
                html:              '<p>You are about to <strong>submit your exam</strong>.</p>' +
                                   '<ul style="text-align:left;margin-top:10px;padding-left:20px;line-height:1.9;">' +
                                   '<li>All your selected answers will be saved.</li>' +
                                   '<li>Unanswered questions will be left blank.</li>' +
                                   '<li><strong>You cannot change your answers after this.</strong></li>' +
                                   '<li>You will be taken to the Dashboard once submitted.</li>' +
                                   '</ul>',
                type:              'warning',
                showCancelButton:  true,
                confirmButtonText: 'Yes, Submit Now',
                cancelButtonText:  'No, Go Back',
                confirmButtonColor:'#d9534f',
                cancelButtonColor: '#367fa9',
                allowOutsideClick: false,
                allowEscapeKey:    false
            }, function(confirmed) {
                if (confirmed) {
                    _runSubmit();
                }
            });
            return;
        }
        _runSubmit();
    }

    function _runSubmit() {
        if (examSubmitting) return;
        examSubmitting = true;
        clearInterval(timerInterval);
        var formData = new FormData($('#exam_form')[0]);
        $.ajax({
            url:         '<?php echo site_url("public_admission/save_applicant_exam"); ?>',
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            dataType:    'json',
            success: function(res) {
                var html;
                if (res.publish_result == 1) {
                    html = '<p style="margin-bottom:8px;">Your exam has been submitted successfully.</p>' +
                           '<table class="table table-bordered table-condensed" style="text-align:left;margin-bottom:0;">' +
                           '<tr><td><strong>Total Questions</strong></td><td>'                   + res.total_questions + '</td></tr>' +
                           '<tr><td><strong>Answered</strong></td><td>'                         + res.answered        + '</td></tr>' +
                           '<tr><td><strong>Correct Answers</strong></td><td>'                  + res.correct         + '</td></tr>' +
                           '<tr><td><strong>Your Score</strong></td><td><strong>'               + res.obtained_marks  + ' / ' + res.total_marks + '</strong></td></tr>' +
                           '</table>';
                } else {
                    html = '<p>Your exam has been submitted successfully.</p>' +
                           '<p style="color:#777;margin-top:8px;">Results will be announced after evaluation.</p>';
                }
                window.onbeforeunload = null;
                swal({
                    title:             'Exam Submitted!',
                    html:              html,
                    type:              'success',
                    confirmButtonText: 'Go to Dashboard',
                    allowOutsideClick: false,
                    allowEscapeKey:    false,
                    confirmButtonColor:'#367fa9'
                }, function() {
                    window.location = dashboardUrl;
                });
            },
            error: function() {
                examSubmitting = false;
                swal({
                    title:             'Submission Error',
                    text:              'Could not submit your exam. Please check your internet connection and try again.',
                    type:              'error',
                    confirmButtonText: 'OK, Try Again',
                    confirmButtonColor:'#d9534f'
                });
            }
        });
    }

    $('#exam_form').on('submit', function(e) {
        e.preventDefault();
        doSubmitExam(false);
    });
})(jQuery);
</script>
