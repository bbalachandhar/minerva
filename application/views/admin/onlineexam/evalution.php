<script src="<?php echo base_url(); ?>backend/plugins/ckeditor/ckeditor.js"></script>
<script src="<?php echo base_url(); ?>backend/js/ckeditor_config.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/ckeditor/adapters/jquery.js"></script>

<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('class'); ?></label>
                                    <select id="eval_class_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php foreach ($classlist as $class): ?>
                                            <option value="<?php echo $class['id']; ?>"><?php echo $class['class']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('section'); ?></label>
                                    <select id="eval_section_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('student'); ?> <small class="text-muted">(Ctrl+click to select multiple)</small></label>
                                    <select id="eval_student_ids" class="form-control" multiple style="height:120px;">
                                        <option value="" disabled><?php echo $this->lang->line('select'); ?> class &amp; section first</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button id="load_papers_btn" type="button" class="btn btn-primary btn-sm pull-right">
                                    <i class="fa fa-file-text-o"></i> Load Papers
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="wave-box-wrapper relative">
                    <div class="quesoverlay" style="display:none;">
                        <div class="cv-spinner">
                            <span class="spinner"></span>
                        </div>
                    </div>
                    <div id="papers_container"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
var base_url = '<?php echo base_url(); ?>';
var onlineexam_id = <?php echo (int)$onlineexam->id; ?>;

/* Destroy all existing CKEditor instances safely */
function destroyEditors() {
    for (var name in CKEDITOR.instances) {
        if (CKEDITOR.instances.hasOwnProperty(name)) {
            try { CKEDITOR.instances[name].destroy(true); } catch(e) {}
        }
    }
}

/* Load sections when class changes */
$(document).on('change', '#eval_class_id', function () {
    var class_id = $(this).val();
    $('#eval_section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
    $('#eval_student_ids').html('<option value="" disabled>Select class &amp; section first</option>');
    if (!class_id) return;

    $.ajax({
        type: 'GET',
        url: base_url + 'sections/getByClass',
        data: { class_id: class_id },
        dataType: 'json',
        beforeSend: function () { $('#eval_section_id').addClass('dropdownloading'); },
        success: function (data) {
            $.each(data, function (i, obj) {
                $('#eval_section_id').append('<option value="' + obj.section_id + '">' + obj.section + '</option>');
            });
        },
        complete: function () { $('#eval_section_id').removeClass('dropdownloading'); }
    });
});

/* Load students when section changes */
$(document).on('change', '#eval_section_id', function () {
    var class_id   = $('#eval_class_id').val();
    var section_id = $(this).val();
    $('#eval_student_ids').html('<option value="" disabled>Loading…</option>');
    if (!class_id) return;

    $.ajax({
        type: 'GET',
        url: base_url + 'admin/onlineexam/getStudentsForEval',
        data: { class_id: class_id, section_id: section_id, onlineexam_id: onlineexam_id },
        dataType: 'json',
        success: function (data) {
            var html = '';
            if (data && data.length > 0) {
                $.each(data, function (i, s) {
                    var name = s.firstname + (s.middlename ? ' ' + s.middlename : '') + ' ' + s.lastname;
                    name = name.trim() + ' (' + s.admission_no + ')';
                    html += '<option value="' + s.onlineexam_student_id + '">' + name + '</option>';
                });
            } else {
                html = '<option value="" disabled>No students found for this exam</option>';
            }
            $('#eval_student_ids').html(html);
        },
        error: function () {
            $('#eval_student_ids').html('<option value="" disabled>Error loading students</option>');
        }
    });
});

/* Load exam papers for selected students */
$(document).on('click', '#load_papers_btn', function () {
    var selected = $('#eval_student_ids').val();
    if (!selected || selected.length === 0) {
        errorMsg('Please select at least one student.');
        return;
    }

    destroyEditors();
    $('#papers_container').html('');
    $('.quesoverlay').show();

    var post_data = {
        onlineexam_id: onlineexam_id,
        student_ids: selected
    };

    $.ajax({
        type: 'POST',
        url: base_url + 'admin/onlineexam/getStudentPapers',
        data: post_data,
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#papers_container').html(response.html);
                CKEDITOR.env.isCompatible = true;
                $('[class*="remark"]').ckeditor({
                    toolbar: 'Evalution',
                    allowedContent: true,
                    extraPlugins: 'ckeditor_wiris,wordcount,notification',
                    enterMode: CKEDITOR.ENTER_BR,
                    shiftEnterMode: CKEDITOR.ENTER_P,
                    customConfig: base_url + 'backend/js/ckeditor_config.js'
                });
                $('body,html').animate({ scrollTop: 0 }, 400);
            } else {
                errorMsg(response.message || '<?php echo $this->lang->line('error_occurred_please_try_again'); ?>');
            }
        },
        error: function () {
            errorMsg('<?php echo $this->lang->line('error_occurred_please_try_again'); ?>');
        },
        complete: function () {
            $('.quesoverlay').hide();
        }
    });
});

/* Save marks via fillmarks endpoint */
$(document).on('submit', '.mark_fill_form', function (e) {
    e.preventDefault();
    var form          = $(this);
    var submit_button = form.find(':submit');
    var formdata      = form.serializeArray();

    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        data: formdata,
        dataType: 'JSON',
        beforeSend: function () { submit_button.button('loading'); },
        success: function (response) {
            if (!response.status) {
                var message = '';
                $.each(response.error, function (index, value) { message += value; });
                errorMsg(message);
            } else {
                successMsg(response.message);
            }
        },
        error: function () {
            alert('<?php echo $this->lang->line('error_occurred_please_try_again'); ?>');
        },
        complete: function () { submit_button.button('reset'); }
    });
});
</script>

