<style>
@media print { .no-print, .no-print * { display: none !important; } }
.ms-card-img { width: 64px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
.ms-card-placeholder { width: 64px; height: 44px; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #bbb; font-size: 20px; }
.upload-zone { border: 2px dashed #ddd; border-radius: 6px; padding: 16px; text-align: center; cursor: pointer; transition: border-color .2s, background .2s; position: relative; }
.upload-zone:hover { border-color: #90caf9; background: #f8fbff; }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-zone .upload-icon { font-size: 22px; color: #bbb; margin-bottom: 4px; }
.upload-zone .upload-text { font-size: 12px; color: #999; }
.upload-zone .upload-preview { max-height: 48px; border-radius: 4px; margin-top: 6px; }
.toggle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; }
.toggle-item { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f5f5f5; }
.toggle-item label { margin: 0; font-weight: 400; font-size: 13px; color: #555; cursor: pointer; }
.btn-add-card { border-radius: 6px; font-weight: 600; letter-spacing: .3px; }
</style>

<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> Design Marksheet</h1>
    </section>

    <section class="content">
        <!-- Marksheet List -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> <?php echo $this->lang->line('marksheet_list'); ?></h3>
                <?php if ($this->rbac->hasPrivilege('design_marksheet', 'can_add')) { ?>
                <div class="box-tools">
                    <button type="button" class="btn btn-primary btn-sm btn-add-card" onclick="$('#add-marksheet-section').slideToggle(300); $(this).find('i').toggleClass('fa-plus fa-minus');">
                        <i class="fa fa-plus"></i> <?php echo $this->lang->line('add_marksheet'); ?>
                    </button>
                </div>
                <?php } ?>
            </div>
            <div class="box-body">
                <?php if ($this->session->flashdata('msg')) {
                    echo $this->session->flashdata('msg');
                    $this->session->unset_userdata('msg');
                } ?>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover example">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th><?php echo $this->lang->line('template'); ?></th>
                                <th><?php echo $this->lang->line('exam_name'); ?></th>
                                <th style="width:80px"><?php echo $this->lang->line('background_image'); ?></th>
                                <th style="width:50px">Fields</th>
                                <th class="text-right noExport" style="width:120px"><?php echo $this->lang->line('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($certificateList)) {
                            $count = 1;
                            foreach ($certificateList as $certificate) {
                                $fields = [];
                                if ($certificate->is_name) $fields[] = 'Name';
                                if ($certificate->is_father_name) $fields[] = 'Father';
                                if ($certificate->is_mother_name) $fields[] = 'Mother';
                                if ($certificate->is_dob) $fields[] = 'DOB';
                                if ($certificate->is_admission_no) $fields[] = 'Adm No';
                                if ($certificate->is_roll_no) $fields[] = 'Roll';
                                if ($certificate->is_photo) $fields[] = 'Photo';
                                if ($certificate->is_class) $fields[] = 'Class';
                                if ($certificate->is_section) $fields[] = 'Section';
                                if ($certificate->is_division) $fields[] = 'Division';
                                if ($certificate->is_rank) $fields[] = 'Rank';
                                if ($certificate->is_teacher_remark) $fields[] = 'Remark';
                                if ($certificate->exam_session) $fields[] = 'Exam Session';
                        ?>
                            <tr>
                                <td><?php echo $count; ?></td>
                                <td>
                                    <a style="cursor:pointer; font-weight:600; color:#1565c0;" class="view_data" id="<?php echo $certificate->id; ?>">
                                        <?php echo $certificate->template; ?>
                                    </a>
                                </td>
                                <td><?php echo $certificate->exam_name ?: '&mdash;'; ?></td>
                                <td class="text-center">
                                    <?php if ($certificate->background_img != '' && !is_null($certificate->background_img)) { ?>
                                        <img src="<?php echo $this->media_storage->getImageURL('uploads/marksheet/' . $certificate->background_img); ?>" class="ms-card-img">
                                    <?php } else { ?>
                                        <div class="ms-card-placeholder"><i class="fa fa-image"></i></div>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size:11px;" title="<?php echo implode(', ', $fields); ?>">
                                        <?php echo count($fields); ?> fields
                                    </span>
                                </td>
                                <td class="text-right no-print white-space-nowrap">
                                    <a class="btn btn-default btn-xs view_data" id="<?php echo $certificate->id; ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('view'); ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if ($this->rbac->hasPrivilege('design_marksheet', 'can_edit')) { ?>
                                    <a href="<?php echo site_url('admin/marksheet/edit/' . $certificate->id); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <?php }
                                    if ($this->rbac->hasPrivilege('design_marksheet', 'can_delete')) { ?>
                                    <a href="<?php echo base_url('admin/marksheet/delete/' . $certificate->id); ?>" class="btn btn-danger btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php $count++; } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Marksheet Form -->
        <?php if ($this->rbac->hasPrivilege('design_marksheet', 'can_add')) { ?>
        <div id="add-marksheet-section" style="display:none;">
            <form id="form1" enctype="multipart/form-data" action="<?php echo site_url('admin/marksheet'); ?>" method="post" accept-charset="utf-8">
                <div class="row">
                    <!-- Card Details -->
                    <div class="col-md-5">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-info-circle"></i> Card Details</h3>
                            </div>
                            <div class="box-body">
                                <?php if (isset($error_message)) { echo "<div class='alert alert-danger'>" . $error_message . "</div>"; } ?>

                                <div class="form-group">
                                    <label><?php echo $this->lang->line('template'); ?> <small class="req">*</small></label>
                                    <input id="template" name="template" type="text" class="form-control" value="<?php echo set_value('template'); ?>">
                                    <span class="text-danger"><?php echo form_error('template'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('exam_name'); ?></label>
                                    <input name="exam_name" type="text" class="form-control" value="<?php echo set_value('exam_name'); ?>">
                                    <span class="text-danger"><?php echo form_error('exam_name'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('school_name'); ?></label>
                                    <input name="school_name" type="text" class="form-control" value="<?php echo set_value('school_name'); ?>">
                                    <span class="text-danger"><?php echo form_error('school_name'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('exam_center'); ?></label>
                                    <input name="exam_center" type="text" class="form-control" value="<?php echo set_value('exam_center'); ?>">
                                    <span class="text-danger"><?php echo form_error('exam_center'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('body_text'); ?></label>
                                    <textarea name="content" class="form-control" rows="3"><?php echo set_value('content'); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('content'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('footer_text'); ?></label>
                                    <textarea name="content_footer" class="form-control" rows="3"><?php echo set_value('content_footer'); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('content_footer'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('printing_date'); ?></label>
                                    <input name="date" type="text" class="form-control date" value="<?php echo set_value('date'); ?>">
                                    <span class="text-danger"><?php echo form_error('date'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Images & Logos -->
                    <div class="col-md-4">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-image"></i> Images & Logos</h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label style="font-size:12px;"><?php echo $this->lang->line('header_image'); ?></label>
                                            <div class="upload-zone" id="zone-header_image">
                                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                <div class="upload-text">Drop or click</div>
                                                <input type="file" name="header_image" onchange="previewUpload(this, 'zone-header_image')">
                                            </div>
                                            <span class="text-danger"><?php echo form_error('header_image'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label style="font-size:12px;"><?php echo $this->lang->line('left_logo'); ?></label>
                                            <div class="upload-zone" id="zone-left_logo">
                                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                <div class="upload-text">Drop or click</div>
                                                <input type="file" name="left_logo" onchange="previewUpload(this, 'zone-left_logo')">
                                            </div>
                                            <span class="text-danger"><?php echo form_error('left_logo'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label style="font-size:12px;"><?php echo $this->lang->line('right_logo'); ?></label>
                                            <div class="upload-zone" id="zone-right_logo">
                                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                <div class="upload-text">Drop or click</div>
                                                <input type="file" name="right_logo" onchange="previewUpload(this, 'zone-right_logo')">
                                            </div>
                                            <span class="text-danger"><?php echo form_error('right_logo'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label style="font-size:12px;"><?php echo $this->lang->line('background_image'); ?></label>
                                            <div class="upload-zone" id="zone-background_img">
                                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                <div class="upload-text">Drop or click</div>
                                                <input type="file" name="background_img" onchange="previewUpload(this, 'zone-background_img')">
                                            </div>
                                            <span class="text-danger"><?php echo form_error('background_img'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label style="font-size:12px;"><?php echo $this->lang->line('left_sign'); ?></label>
                                            <div class="upload-zone" id="zone-left_sign">
                                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                <div class="upload-text">Drop or click</div>
                                                <input type="file" name="left_sign" onchange="previewUpload(this, 'zone-left_sign')">
                                            </div>
                                            <span class="text-danger"><?php echo form_error('left_sign'); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label style="font-size:12px;"><?php echo $this->lang->line('middle_sign'); ?></label>
                                            <div class="upload-zone" id="zone-middle_sign">
                                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                <div class="upload-text">Drop or click</div>
                                                <input type="file" name="middle_sign" onchange="previewUpload(this, 'zone-middle_sign')">
                                            </div>
                                            <span class="text-danger"><?php echo form_error('middle_sign'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <label style="font-size:12px;"><?php echo $this->lang->line('right_sign'); ?></label>
                                            <div class="upload-zone" id="zone-right_sign">
                                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                                <div class="upload-text">Drop or click</div>
                                                <input type="file" name="right_sign" onchange="previewUpload(this, 'zone-right_sign')">
                                            </div>
                                            <span class="text-danger"><?php echo form_error('right_sign'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visible Fields -->
                    <div class="col-md-3">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><i class="fa fa-check-square-o"></i> Visible Fields</h3>
                            </div>
                            <div class="box-body" style="padding: 10px 15px;">
                                <div class="toggle-grid">
                                    <?php
                                    $toggles = [
                                        'is_name'           => $this->lang->line('name'),
                                        'is_father_name'    => $this->lang->line('father_name'),
                                        'is_mother_name'    => $this->lang->line('mother_name'),
                                        'is_dob'            => $this->lang->line('date_of_birth'),
                                        'is_admission_no'   => $this->lang->line('admission_no'),
                                        'is_roll_no'        => $this->lang->line('roll_number'),
                                        'is_class'          => $this->lang->line('class'),
                                        'is_section'        => $this->lang->line('section'),
                                        'is_division'       => $this->lang->line('division'),
                                        'is_rank'           => $this->lang->line('rank'),
                                        'is_photo'          => $this->lang->line('photo'),
                                        'exam_session'      => $this->lang->line('exam_session'),
                                        'is_teacher_remark' => $this->lang->line('remark'),
                                    ];
                                    foreach ($toggles as $field => $label) { ?>
                                    <div class="toggle-item">
                                        <label for="<?php echo $field; ?>"><?php echo $label; ?></label>
                                        <div class="material-switch switchcheck">
                                            <input id="<?php echo $field; ?>" name="<?php echo $field; ?>" type="checkbox" class="chk" value="1">
                                            <label for="<?php echo $field; ?>" class="label-success"></label>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="box-footer text-right">
                                <button type="button" class="btn btn-default" onclick="$('#add-marksheet-section').slideUp(300);"><?php echo $this->lang->line('cancel'); ?></button>
                                <button type="submit" id="submitbtn" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php } ?>

    </section>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-eye"></i> <?php echo $this->lang->line('view_marksheet'); ?></h4>
            </div>
            <div class="modal-body" id="certificate_detail"></div>
        </div>
    </div>
</div>

<script>
var base_url = '<?php echo base_url(); ?>';

function previewUpload(input, zoneId) {
    var zone = document.getElementById(zoneId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            var existing = zone.querySelector('.upload-preview');
            if (existing) existing.remove();
            zone.querySelector('.upload-icon').style.display = 'none';
            zone.querySelector('.upload-text').textContent = input.files[0].name.substring(0, 18);
            var img = document.createElement('img');
            img.className = 'upload-preview';
            img.src = e.target.result;
            zone.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function printDiv(elem) { Popup(jQuery(elem).html()); }

function Popup(data) {
    var frame1 = $('<iframe />');
    frame1[0].name = "frame1";
    frame1.css({"position": "absolute", "top": "-1000000px"});
    $("body").append(frame1);
    var frameDoc = frame1[0].contentWindow || frame1[0].contentDocument.document || frame1[0].contentDocument;
    frameDoc.document.open();
    frameDoc.document.write('<html><head><title></title>');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
    frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
    frameDoc.document.write('</head><body>' + data + '</body></html>');
    frameDoc.document.close();
    setTimeout(function () {
        window.frames["frame1"].focus();
        window.frames["frame1"].print();
        frame1.remove();
    }, 500);
}

$(document).ready(function () {
    $('.view_data').click(function () {
        var certificateid = $(this).attr("id");
        $.ajax({
            url: "<?php echo base_url('admin/marksheet/view'); ?>",
            method: "post",
            data: {certificateid: certificateid},
            dataType: 'JSON',
            success: function (data) {
                $('#certificate_detail').html(data.page);
                $('#myModal').modal("show");
            }
        });
    });

    $('#form1').submit(function () { $("#submitbtn").button('loading'); });

    <?php if (form_error('template')) { ?>
    $('#add-marksheet-section').show();
    <?php } ?>
});
</script>
