<h3>Admission Enquiry</h3>
<hr>
<?php
if (validation_errors()) {
    echo '<div class="alert alert-danger">' . validation_errors() . '</div>';
}
?>
<form action="<?php echo site_url('enquiry/index'); ?>" method="post">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="name">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="contact">Phone <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="contact" value="<?php echo set_value('contact'); ?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo set_value('email'); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" name="address"><?php echo set_value('address'); ?></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="class">Department/Class <span class="text-danger">*</span></label>
                <select name="class" class="form-control">
                    <option value="">Select Class</option>
                    <?php foreach ($class_list as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo set_select('class', $class['id']); ?>><?php echo $class['class']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="source">Source <span class="text-danger">*</span></label>
                <select name="source" class="form-control">
                    <option value="">Select Source</option>
                    <?php foreach ($sourcelist as $source): ?>
                        <option value="<?php echo $source['source']; ?>" <?php echo set_select('source', $source['source']); ?>><?php echo $source['source']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="reference">Reference</label>
                <select name="reference" class="form-control">
                    <option value="">Select Reference</option>
                    <?php foreach ($references as $ref): ?>
                        <option value="<?php echo $ref['reference']; ?>" <?php echo set_select('reference', $ref['reference']); ?>><?php echo $ref['reference']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" name="description"><?php echo set_value('description'); ?></textarea>
    </div>
     <div class="form-group">
        <label for="note">Note</label>
        <textarea class="form-control" name="note"><?php echo set_value('note'); ?></textarea>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Captcha <span class="text-danger">*</span></label>
                <div style="display: flex; align-items: center;">
                    <p id="captcha_image_container" style="margin-right: 10px; margin-bottom: 0;"><?php echo $captcha_image; ?></p>
                    <button type="button" id="refresh_captcha_button" class="btn btn-default btn-sm" style="margin-right: 10px;">Refresh</button>
                    <input type="text" name="captcha" class="form-control" autocomplete="off" style="width: 150px;">
                </div>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Submit Enquiry</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('#refresh_captcha_button').click(function(){
        $.ajax({
            url: '<?php echo site_url('enquiry/refresh_captcha'); ?>',
            type: 'GET',
            success: function(res){
                $('#captcha_image_container').html(res);
            }
        });
    });
});
</script>
