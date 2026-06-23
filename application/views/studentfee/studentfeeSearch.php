<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<style>
.fc-page .fc-panel { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:20px; overflow:visible; }
.fc-panel-header { background:linear-gradient(135deg,#5b73e8 0%,#7c5ce7 100%); color:#fff; padding:14px 20px; border-radius:10px 10px 0 0; display:flex; align-items:center; justify-content:space-between; }
.fc-panel-header h3 { margin:0; font-size:16px; font-weight:600; }
.fc-panel-header h3 i { margin-right:8px; }
.fc-panel-body { padding:20px; }
.fc-label { font-weight:600; font-size:13px; color:#495057; margin-bottom:6px; display:block; }
.fc-label .req { color:#e74c3c; }
.fc-select, .fc-input {
    width:100%; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px;
    padding:9px 14px; font-size:14px; color:#333; transition:border-color .2s;
    height:40px; box-sizing:border-box;
    -webkit-appearance:none; appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10z'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 12px center; padding-right:32px;
}
.fc-input { background-image:none; padding-right:14px; }
.fc-select:focus, .fc-input:focus { border-color:#5b73e8; outline:none; background:#fff; }
.fc-divider { display:flex; align-items:center; gap:12px; margin:0 -20px; padding:0 20px; }
.fc-divider-line { flex:1; height:1px; background:#e9ecef; }
.fc-divider-text { font-size:12px; font-weight:600; color:#adb5bd; text-transform:uppercase; letter-spacing:1px; }
.btn-fc-search {
    background:linear-gradient(135deg,#5b73e8,#7c5ce7); color:#fff; border:none;
    border-radius:8px; padding:9px 24px; font-size:14px; font-weight:600; cursor:pointer;
    transition:all .2s; display:inline-flex; align-items:center; gap:6px; height:40px;
}
.btn-fc-search:hover { opacity:.9; color:#fff; transform:translateY(-1px); }
.btn-fc-search:disabled { opacity:.6; }
.btn-fc-upload {
    background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.3);
    border-radius:8px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer;
    transition:all .2s; display:inline-flex; align-items:center; gap:6px; text-decoration:none;
}
.btn-fc-upload:hover { background:rgba(255,255,255,.25); color:#fff; text-decoration:none; }

/* Results table */
.fc-results { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,.06); overflow:hidden; }
.fc-results-header { padding:16px 20px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between; }
.fc-results-header h4 { margin:0; font-size:15px; font-weight:600; color:#2c3e50; }

.student-list thead th {
    background:#f8f9fb !important; padding:11px 14px !important;
    font-size:11px !important; font-weight:700 !important; text-transform:uppercase !important;
    letter-spacing:.4px !important; color:#8492a6 !important; border-bottom:2px solid #eef0f3 !important;
}
.student-list tbody td {
    padding:12px 14px !important; font-size:13px !important; color:#333 !important;
    border-bottom:1px solid #f0f0f0 !important; vertical-align:middle !important;
}
.student-list tbody tr:hover { background:#f8f9ff !important; }

.btn-collect {
    background:#27ae60; color:#fff; border:none; border-radius:6px;
    padding:7px 16px; font-size:12px; font-weight:600; cursor:pointer;
    transition:all .2s; display:inline-flex; align-items:center; gap:5px; text-decoration:none;
}
.btn-collect:hover { background:#219653; color:#fff; text-decoration:none; transform:translateY(-1px); }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> <?php echo $this->lang->line('fees_collection'); ?></h1>
    </section>

    <section class="content">
        <div class="fc-page">

            <!-- Search Panel -->
            <div class="fc-panel">
                <div class="fc-panel-header">
                    <h3><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    <a href="<?php echo site_url('studentfee/bulk_upload_fees'); ?>" class="btn-fc-upload">
                        <i class="fa fa-upload"></i> <?php echo $this->lang->line('bulk_upload_entries'); ?>
                    </a>
                </div>
                <div class="fc-panel-body">
                    <form action="<?php echo site_url('studentfee/search'); ?>" method="post" class="class_search_form">
                        <?php echo $this->customlib->getCSRF(); ?>

                        <!-- Row 1: Class filters + Search button -->
                        <div class="row">
                            <?php if ($sch_setting->institution_type == 'college'): ?>
                            <div class="col-md-3">
                                <label class="fc-label"><?php echo $this->lang->line('department'); ?></label>
                                <select id="department_id" name="department_id" class="fc-select">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($department_list as $department): ?>
                                    <option value="<?php echo $department['id']; ?>" <?php if (set_value('department_id') == $department['id']) echo "selected"; ?>><?php echo $department['department_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>
                            <div class="col-md-<?php echo ($sch_setting->institution_type == 'college') ? '3' : '4'; ?>">
                                <label class="fc-label"><?php echo $this->lang->line('class'); ?> <span class="req">*</span></label>
                                <select id="class_id" name="class_id" class="fc-select">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    <?php foreach ($classlist as $class): ?>
                                    <option value="<?php echo $class['id']; ?>" <?php if (set_value('class_id') == $class['id']) echo "selected"; ?>><?php echo $class['class']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="text-danger" id="error_class_id" style="font-size:12px;"></span>
                            </div>
                            <div class="col-md-<?php echo ($sch_setting->institution_type == 'college') ? '3' : '3'; ?>">
                                <label class="fc-label"><?php echo $this->lang->line('section'); ?></label>
                                <select id="section_id" name="section_id" class="fc-select">
                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-<?php echo ($sch_setting->institution_type == 'college') ? '3' : '2'; ?>">
                                <label class="fc-label">&nbsp;</label>
                                <button type="submit" class="btn-fc-search" style="width:100%;" name="class_search" value="class_search" data-loading-text="<?php echo $this->lang->line('please_wait'); ?>">
                                    <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div style="margin:16px 0;">
                            <div class="fc-divider">
                                <div class="fc-divider-line"></div>
                                <span class="fc-divider-text">or search by keyword</span>
                                <div class="fc-divider-line"></div>
                            </div>
                        </div>

                        <!-- Row 2: Keyword search -->
                        <div class="row">
                            <div class="col-md-<?php echo ($sch_setting->institution_type == 'college') ? '9' : '10'; ?>">
                                <label class="fc-label"><?php echo $this->lang->line('search_by_keyword'); ?></label>
                                <input type="text" name="search_text" id="search_text" class="fc-input" value="<?php echo set_value('search_text'); ?>" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                                <span class="text-danger" id="error_search_text" style="font-size:12px;"></span>
                            </div>
                            <div class="col-md-<?php echo ($sch_setting->institution_type == 'college') ? '3' : '2'; ?>">
                                <label class="fc-label">&nbsp;</label>
                                <button type="submit" class="btn-fc-search" style="width:100%;background:linear-gradient(135deg,#3498db,#2980b9);" name="keyword_search" value="keyword_search" data-loading-text="<?php echo $this->lang->line('please_wait'); ?>">
                                    <i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="fc-results">
                <div class="fc-results-header">
                    <h4><i class="fa fa-list-alt" style="margin-right:6px;color:#5b73e8;"></i> <?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('list'); ?></h4>
                </div>
                <div style="padding:0 12px 12px;">
                    <table class="table table-hover student-list" data-export-title="<?php echo $this->lang->line('student') . " " . $this->lang->line('list'); ?>">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('class'); ?></th>
                                <th><?php echo $this->lang->line('section'); ?></th>
                                <th><?php echo $this->lang->line('admission_no'); ?></th>
                                <th><?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('name'); ?></th>
                                <?php if ($sch_setting->father_name): ?>
                                <th><?php echo $this->lang->line('father_name'); ?></th>
                                <?php endif; ?>
                                <th><?php echo $this->lang->line('date_of_birth'); ?></th>
                                <th><?php echo $this->lang->line('mobile_no'); ?></th>
                                <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    emptyDatatable('student-list', 'fees_data');

    var class_id = $('#class_id').val();
    var section_id = '<?php echo set_value('section_id', 0); ?>';
    var department_id = '<?php echo set_value('department_id', 0); ?>';

    getSectionByClass(class_id, section_id);

    <?php if ($sch_setting->institution_type == 'college'): ?>
    if (department_id) getClassesByDepartment(department_id, class_id);
    $(document).on('change', '#department_id', function() {
        getClassesByDepartment($(this).val(), '');
        $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
    });
    <?php endif; ?>

    $(document).on('change', '#class_id', function() {
        $('#section_id').html('');
        getSectionByClass($(this).val(), 0);
    });

    function getClassesByDepartment(department_id, class_id) {
        if (department_id != '') {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $.ajax({
                type: 'GET', url: '<?php echo site_url('classes/getClassesByDepartment'); ?>',
                data: { department_id: department_id }, dataType: 'json',
                success: function(data) {
                    $.each(data, function(i, obj) {
                        var sel = (class_id == obj.id) ? 'selected' : '';
                        $('#class_id').append('<option value="' + obj.id + '" ' + sel + '>' + obj.class + '</option>');
                    });
                }
            });
        } else {
            $('#class_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            <?php foreach ($classlist as $class): ?>
            $('#class_id').append('<option value="<?php echo $class['id']; ?>"><?php echo addslashes($class['class']); ?></option>');
            <?php endforeach; ?>
        }
    }

    function getSectionByClass(class_id, section_id) {
        if (class_id != '' && class_id != null) {
            $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
            $.ajax({
                type: 'GET', url: '<?php echo base_url(); ?>sections/getByClass',
                data: { class_id: class_id }, dataType: 'json',
                beforeSend: function() { $('#section_id').addClass('dropdownloading'); },
                success: function(data) {
                    $.each(data, function(i, obj) {
                        var sel = (section_id == obj.section_id) ? 'selected' : '';
                        $('#section_id').append('<option value="' + obj.section_id + '" ' + sel + '>' + obj.section + '</option>');
                    });
                },
                complete: function() { $('#section_id').removeClass('dropdownloading'); }
            });
        }
    }

    // Form submission
    $('form.class_search_form button[type=submit]').click(function() {
        $('button[type=submit]', $(this).parents('form')).removeAttr('clicked');
        $(this).attr('clicked', 'true');
    });

    $(document).on('submit', '.class_search_form', function(e) {
        e.preventDefault();
        var $btn = $('button[type=submit][clicked=true]');
        var form_data = $(this).serializeArray();
        form_data.push({ name: 'search_type', value: $btn.attr('value') });

        $.ajax({
            url: '<?php echo site_url('studentfee/search'); ?>',
            type: 'POST', dataType: 'JSON', data: form_data,
            beforeSend: function() {
                $('[id^=error]').html('');
                $btn.button('loading');
                resetFields($btn.attr('name'));
            },
            success: function(response) {
                if (!response.status) {
                    $.each(response.error, function(key, value) { $('#error_' + key).html(value); });
                } else {
                    if ($.fn.DataTable.isDataTable('.student-list')) $('.student-list').DataTable().destroy();

                    $('.student-list').DataTable({
                        dom: 'Bfrtip',
                        buttons: [
                            { extend:'copy', text:'<i class="fa fa-files-o"></i>', titleAttr:'Copy', className:'btn-copy', title:$('.student-list').data('exportTitle'), exportOptions:{columns:['thead th:not(.noExport)']} },
                            { extend:'excel', text:'<i class="fa fa-file-excel-o"></i>', titleAttr:'Excel', className:'btn-excel', title:$('.student-list').data('exportTitle'), exportOptions:{columns:['thead th:not(.noExport)']} },
                            { extend:'csv', text:'<i class="fa fa-file-text-o"></i>', titleAttr:'CSV', className:'btn-csv', title:$('.student-list').data('exportTitle'), exportOptions:{columns:['thead th:not(.noExport)']} },
                            { extend:'pdf', text:'<i class="fa fa-file-pdf-o"></i>', titleAttr:'PDF', className:'btn-pdf', title:$('.student-list').data('exportTitle'), exportOptions:{columns:['thead th:not(.noExport)']} },
                            { extend:'print', text:'<i class="fa fa-print"></i>', titleAttr:'Print', className:'btn-print', title:$('.student-list').data('exportTitle'), customize:function(win){ $(win.document.body).find('th').css('text-align','center'); $(win.document.body).find('table').css('font-size','14px'); $(win.document.body).find('h1').css('text-align','center'); }, exportOptions:{columns:['thead th:not(.noExport)']} }
                        ],
                        columnDefs: [{ targets: -1, orderable: false }],
                        language: { processing: '<i class="fa fa-spinner fa-spin fa-1x fa-fw"></i><span class="sr-only">Loading...</span>' },
                        pageLength: 100,
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: '<?php echo site_url('studentfee/ajaxSearch'); ?>',
                            dataSrc: 'data', type: 'POST', data: response.params
                        },
                        drawCallback: function(settings) {
                            $('.detail_view_tab').html('').html(settings.json.student_detail_view);
                        }
                    });
                }
            },
            error: function() { $btn.button('reset'); },
            complete: function() { $btn.button('reset'); }
        });
    });

    function resetFields(search_type) {
        if (search_type == 'keyword_search') {
            $('#class_id').prop('selectedIndex', 0);
            $('#section_id').find('option').not(':first').remove();
        } else if (search_type == 'class_search') {
            $('#search_text').val('');
        }
    }
});
</script>
