
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
            <div class="row" style="margin-bottom:10px;">
                <div class="col-sm-6"><strong>Applicant:</strong> <?php echo $this->customlib->getFullName($applicant['firstname'], $applicant['middlename'], $applicant['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></div>
                <div class="col-sm-6 text-right"><strong>Reference No:</strong> <?php echo $applicant['reference_no']; ?></div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-sm-12">
                    <div class="well well-sm" style="margin-bottom:0;">
                        <strong>Exam Window:</strong> <?php echo $this->customlib->dateyyyymmddToDateTimeformat($exam->exam_from, false); ?> to <?php echo $this->customlib->dateyyyymmddToDateTimeformat($exam->exam_to, false); ?> |
                        <strong>Duration:</strong> <?php echo $exam->duration; ?> |
                        <strong>Attempts:</strong> <?php echo $exam->attempt; ?>
                    </div>
                </div>
            </div>

            <?php if ($online_exam_validate->is_attempted) { ?>
                <div class="alert alert-success">You have already submitted this exam.</div>
            <?php } ?>

            <form id="exam_form" method="post" action="<?php echo site_url('public_admission/save_applicant_exam'); ?>" enctype="multipart/form-data">
                <?php echo $this->customlib->getCSRF(); ?>
                <div id="questiondata"></div>
                <div id="wait" style="display:none;">Please wait while loading questions...</div>
            </form>
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
