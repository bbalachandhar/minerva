<style>
@media print { .no-print, .no-print * { display: none !important; } }
.ac-card-img { width: 64px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #eee; }
.ac-card-placeholder { width: 64px; height: 44px; background: #f5f5f5; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #bbb; font-size: 20px; }
.upload-zone { border: 2px dashed #ddd; border-radius: 6px; padding: 16px; text-align: center; cursor: pointer; transition: border-color .2s, background .2s; position: relative; }
.upload-zone:hover { border-color: #90caf9; background: #f8fbff; }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-zone .upload-icon { font-size: 22px; color: #bbb; margin-bottom: 4px; }
.upload-zone .upload-text { font-size: 12px; color: #999; }
.upload-zone .upload-preview { max-height: 48px; border-radius: 4px; margin-top: 6px; }
.toggle-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; }
.toggle-item { display: flex; align-items: center; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f5f5f5; }
.toggle-item label { margin: 0; font-weight: 400; font-size: 13px; color: #555; cursor: pointer; }
.existing-file { display: inline-flex; align-items: center; gap: 6px; background: #f5f5f5; border-radius: 4px; padding: 4px 10px; margin-top: 6px; font-size: 12px; color: #555; }
.existing-file .remove-btn { color: #e53935; cursor: pointer; font-size: 14px; }
</style>

<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-id-card-o"></i> <?php echo $this->lang->line('design_admit_card'); ?></h1>
    </section>

    <section class="content">
        <!-- Admit Card List -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list"></i> <?php echo $this->lang->line('admit_card_list'); ?></h3>
                <div class="box-tools">
                    <a href="<?php echo site_url('admin/admitcard'); ?>" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> <?php echo $this->lang->line('add_admit_card'); ?></a>
                </div>
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
                                <th><?php echo $this->lang->line('heading'); ?></th>
                                <th><?php echo $this->lang->line('exam_name'); ?></th>
                                <th style="width:80px"><?php echo $this->lang->line('background_image'); ?></th>
                                <th style="width:80px"><?php echo $this->lang->line('active'); ?></th>
                                <th style="width:50px">Fields</th>
                                <th class="text-right noExport" style="width:120px"><?php echo $this->lang->line('action'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($admitcardList)) {
                            $count = 1;
                            foreach ($admitcardList as $certificate) {
                                $fields = [];
                                if ($certificate->is_name) $fields[] = 'Name';
                                if ($certificate->is_father_name) $fields[] = 'Father';
                                if ($certificate->is_mother_name) $fields[] = 'Mother';
                                if ($certificate->is_dob) $fields[] = 'DOB';
                                if ($certificate->is_admission_no) $fields[] = 'Adm No';
                                if ($certificate->is_roll_no) $fields[] = 'Roll';
                                if ($certificate->is_class) $fields[] = 'Class';
                                if ($certificate->is_section) $fields[] = 'Section';
                                if ($certificate->is_gender) $fields[] = 'Gender';
                                if ($certificate->is_photo) $fields[] = 'Photo';
                                if ($certificate->is_address) $fields[] = 'Address';
                                $is_editing = (isset($admitcard) && $certificate->id == $admitcard->id);
                        ?>
                            <tr <?php if ($is_editing) echo 'style="background:#e3f2fd;"'; ?>>
                                <td><?php echo $count; ?></td>
                                <td>
                                    <a style="cursor:pointer; font-weight:600; color:#1565c0;" class="view_data" id="<?php echo $certificate->id; ?>">
                                        <?php echo $certificate->template; ?>
                                    </a>
                                    <?php if ($is_editing) { ?><span class="label label-info" style="margin-left:6px;">Editing</span><?php } ?>
                                </td>
                                <td><small class="text-muted"><?php echo $certificate->heading ?: '&mdash;'; ?></small></td>
                                <td><?php echo $certificate->exam_name ?: '&mdash;'; ?></td>
                                <td class="text-center">
                                    <?php if ($certificate->background_img != '' && !is_null($certificate->background_img)) { ?>
                                        <img src="<?php echo $this->media_storage->getImageURL('uploads/admit_card/' . $certificate->background_img); ?>" class="ac-card-img">
                                    <?php } else { ?>
                                        <div class="ac-card-placeholder"><i class="fa fa-image"></i></div>
                                    <?php } ?>
                                </td>
                                <td class="text-center">
                                    <input onclick="save_active_status(this.value)" type="radio" name="active_admit_card" value="<?php echo $certificate->id; ?>" <?php echo $certificate->is_active == 1 ? 'checked' : ''; ?> style="transform:scale(1.3);">
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
                                    <?php if ($this->rbac->hasPrivilege('design_admit_card', 'can_edit')) { ?>
                                    <a href="<?php echo site_url('admin/admitcard/edit/' . $certificate->id); ?>" class="btn btn-<?php echo $is_editing ? 'primary' : 'default'; ?> btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('edit'); ?>">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <?php }
                                    if ($this->rbac->hasPrivilege('design_admit_card', 'can_delete')) { ?>
                                    <a href="<?php echo base_url('admin/admitcard/delete/' . $certificate->id); ?>" class="btn btn-danger btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');">
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

        <!-- Edit Admit Card Form -->
        <?php if ($this->rbac->hasPrivilege('design_admit_card', 'can_edit') && isset($admitcard)) { ?>
        <form enctype="multipart/form-data" action="<?php echo site_url('admin/admitcard/edit/' . $admitcard->id); ?>" id="editform" method="post" accept-charset="utf-8">
            <input type="hidden" name="id" value="<?php echo $admitcard->id; ?>">
            <div class="row">
                <!-- Card Details -->
                <div class="col-md-5">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-pencil"></i> Edit: <?php echo htmlspecialchars($admitcard->template); ?></h3>
                        </div>
                        <div class="box-body">
                            <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>

                            <div class="form-group">
                                <label><?php echo $this->lang->line('template'); ?> <small class="req">*</small></label>
                                <input name="template" type="text" class="form-control" value="<?php echo set_value('template', $admitcard->template); ?>">
                                <span class="text-danger"><?php echo form_error('template'); ?></span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('heading'); ?></label>
                                        <input name="heading" type="text" class="form-control" value="<?php echo set_value('heading', $admitcard->heading); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('title'); ?></label>
                                        <input name="title" type="text" class="form-control" value="<?php echo set_value('title', $admitcard->title); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('exam_name'); ?></label>
                                        <input name="exam_name" type="text" class="form-control" value="<?php echo set_value('exam_name', $admitcard->exam_name); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('exam_center'); ?></label>
                                        <input name="exam_center" type="text" class="form-control" value="<?php echo set_value('exam_center', $admitcard->exam_center); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('school_name'); ?></label>
                                <input name="school_name" type="text" class="form-control" value="<?php echo set_value('school_name', $admitcard->school_name); ?>">
                            </div>
                            <div class="form-group">
                                <label><?php echo $this->lang->line('footer_text'); ?></label>
                                <textarea name="content_footer" class="form-control" rows="3"><?php echo set_value('content_footer', htmlspecialchars_decode($admitcard->content_footer)); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="col-md-4">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-image"></i> Images & Logos</h3>
                        </div>
                        <div class="box-body">
                            <?php
                            $image_fields = [
                                ['name' => 'left_logo',  'label' => $this->lang->line('left_logo'),  'value' => $admitcard->left_logo,  'remove_name' => 'removeleft_logo'],
                                ['name' => 'right_logo', 'label' => $this->lang->line('right_logo'), 'value' => $admitcard->right_logo, 'remove_name' => 'removeright_logo'],
                                ['name' => 'sign',       'label' => $this->lang->line('sign'),       'value' => $admitcard->sign,       'remove_name' => 'removesign'],
                                ['name' => 'background_img', 'label' => $this->lang->line('background_image'), 'value' => $admitcard->background_img, 'remove_name' => 'removebackground_image'],
                            ];
                            foreach ($image_fields as $idx => $img) {
                                $zone_id = 'zone-' . $img['name'];
                                $has_existing = !empty($img['value']);
                            ?>
                            <div class="form-group" id="group-<?php echo $img['name']; ?>">
                                <label style="font-size:12px;"><?php echo $img['label']; ?></label>
                                <div class="upload-zone" id="<?php echo $zone_id; ?>">
                                    <?php if ($has_existing) { ?>
                                        <img src="<?php echo $this->media_storage->getImageURL('uploads/admit_card/' . $img['value']); ?>" class="upload-preview">
                                        <div class="upload-text"><?php echo $img['value']; ?></div>
                                    <?php } else { ?>
                                        <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                        <div class="upload-text">Drop or click</div>
                                    <?php } ?>
                                    <input type="file" name="<?php echo $img['name']; ?>" onchange="previewUpload(this, '<?php echo $zone_id; ?>')">
                                </div>
                                <?php if ($has_existing) { ?>
                                <div class="existing-file" id="existing-<?php echo $img['name']; ?>">
                                    <i class="fa fa-file-image-o"></i>
                                    <span><?php echo $img['value']; ?></span>
                                    <i class="fa fa-times-circle remove-btn" onclick="removeExistingFile('<?php echo $img['name']; ?>', '<?php echo $img['remove_name']; ?>')"></i>
                                </div>
                                <?php } ?>
                                <input type="hidden" name="old_<?php echo $img['name']; ?>" value="<?php echo $img['value']; ?>">
                                <span class="text-danger"><?php echo form_error($img['name']); ?></span>
                            </div>
                            <?php if ($idx < count($image_fields) - 1) { ?><hr style="margin:10px 0;"><?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Visible Fields -->
                <div class="col-md-3">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-check-square-o"></i> Visible Fields</h3>
                        </div>
                        <div class="box-body" style="padding: 10px 15px;">
                            <div class="toggle-grid">
                                <?php
                                $toggles = [
                                    'is_name'         => $this->lang->line('name'),
                                    'is_father_name'  => $this->lang->line('father_name'),
                                    'is_mother_name'  => $this->lang->line('mother_name'),
                                    'is_dob'          => $this->lang->line('date_of_birth'),
                                    'is_admission_no' => $this->lang->line('admission_no'),
                                    'is_roll_no'      => $this->lang->line('roll_number'),
                                    'is_class'        => $this->lang->line('class'),
                                    'is_section'      => $this->lang->line('section'),
                                    'is_gender'       => $this->lang->line('gender'),
                                    'is_photo'        => $this->lang->line('photo'),
                                    'is_address'      => $this->lang->line('address'),
                                ];
                                foreach ($toggles as $field => $label) { ?>
                                <div class="toggle-item">
                                    <label for="<?php echo $field; ?>"><?php echo $label; ?></label>
                                    <div class="material-switch switchcheck">
                                        <input id="<?php echo $field; ?>" name="<?php echo $field; ?>" type="checkbox" class="chk" value="1"
                                            <?php echo set_checkbox($field, '1', (set_value($field, $admitcard->$field) == 1) ? true : false); ?>>
                                        <label for="<?php echo $field; ?>" class="label-success"></label>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="box-footer text-right">
                            <a href="<?php echo site_url('admin/admitcard'); ?>" class="btn btn-default"><?php echo $this->lang->line('cancel'); ?></a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $this->lang->line('update'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php } ?>

    </section>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><i class="fa fa-eye"></i> <?php echo $this->lang->line('view_admit_card'); ?></h4>
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
            var icon = zone.querySelector('.upload-icon');
            if (icon) icon.style.display = 'none';
            zone.querySelector('.upload-text').textContent = input.files[0].name.substring(0, 20);
            var img = document.createElement('img');
            img.className = 'upload-preview';
            img.src = e.target.result;
            zone.insertBefore(img, zone.querySelector('.upload-text'));
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeExistingFile(fieldName, removeName) {
    if (!confirm('<?php echo $this->lang->line('delete_confirm'); ?>')) return;
    var el = document.getElementById('existing-' + fieldName);
    if (el) el.innerHTML = '<input type="hidden" name="' + removeName + '" value="1"><span class="text-muted" style="font-size:12px;"><i class="fa fa-check"></i> Will be removed on save</span>';
    var zone = document.getElementById('zone-' + fieldName);
    if (zone) {
        var preview = zone.querySelector('.upload-preview');
        if (preview) preview.remove();
        var icon = zone.querySelector('.upload-icon');
        if (icon) icon.style.display = '';
        else {
            var newIcon = document.createElement('div');
            newIcon.className = 'upload-icon';
            newIcon.innerHTML = '<i class="fa fa-cloud-upload"></i>';
            zone.insertBefore(newIcon, zone.firstChild);
        }
        zone.querySelector('.upload-text').textContent = 'Drop or click';
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
            url: "<?php echo base_url('admin/admitcard/view'); ?>",
            method: "post",
            data: {certificateid: certificateid},
            dataType: 'JSON',
            success: function (data) {
                $('#certificate_detail').html(data.page);
                $('#myModal').modal("show");
            }
        });
    });
});

function save_active_status(value) {
    $.ajax({
        url: "<?php echo base_url('admin/admitcard/save_active_status'); ?>",
        method: "post",
        data: {value: value},
        success: function () { successMsg("<?php echo $this->lang->line('record_updated_successfully'); ?>"); }
    });
}
</script>
