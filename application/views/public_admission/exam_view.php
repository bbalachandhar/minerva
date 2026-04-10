<script src="<?php echo base_url(); ?>backend/plugins/ckeditor/ckeditor.js"></script>
<script src="<?php echo base_url(); ?>backend/js/ckeditor_config.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/ckeditor/adapters/jquery.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">

<div class="container" style="margin-top:20px; margin-bottom:30px;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <strong><?php echo $exam->exam; ?></strong>
            <a href="<?php echo site_url('public_admission/exam_list'); ?>" class="btn btn-default btn-xs pull-right">Back To Exams</a>
        </div>
        <div class="panel-body">
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
                <div class="text-right" id="submit_btn_wrapper" style="display:none; margin-top:15px;">
                    <button type="submit" class="btn btn-primary">Submit Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
(function($) {
    "use strict";

    var examid = '<?php echo $exam->id; ?>';
    var isAttempted = <?php echo (int)$online_exam_validate->is_attempted; ?>;

    $(document).ready(function() {
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
                    $('#submit_btn_wrapper').show();
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

    $('#exam_form').on('submit', function() {
        return confirm('<?php echo $this->lang->line('are_you_sure'); ?>');
    });
})(jQuery);
</script>
