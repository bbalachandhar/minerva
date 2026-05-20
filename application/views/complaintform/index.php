<h3><i class="fa fa-bullhorn"></i> Complaint / Suggestion Form</h3>
<p class="text-muted">Use this form to submit a complaint or suggestion. You do not need an account to submit.</p>
<hr>
<?php if (validation_errors()): ?>
    <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
<?php endif; ?>

<form action="<?php echo site_url('complaint'); ?>" method="post">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>" placeholder="Your full name" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Complaint / Suggestion Type <span class="text-danger">*</span></label>
                <select name="complaint_type" class="form-control" required>
                    <option value="">— Select Type —</option>
                    <?php foreach ($complaint_types as $ct): ?>
                        <option value="<?php echo htmlspecialchars($ct['complaint_type']); ?>"
                            <?php echo set_select('complaint_type', $ct['complaint_type']); ?>>
                            <?php echo htmlspecialchars($ct['complaint_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Phone <small class="text-muted">(optional)</small></label>
                <input type="tel" class="form-control" name="contact" id="contact" value="<?php echo set_value('contact'); ?>" maxlength="15" placeholder="e.g. 9876543210">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Email <small class="text-muted">(optional)</small></label>
                <input type="email" class="form-control" name="email" value="<?php echo set_value('email'); ?>" placeholder="your@email.com">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Description <span class="text-danger">*</span></label>
        <textarea class="form-control" name="description" rows="5" placeholder="Please describe your complaint or suggestion in detail…" required><?php echo set_value('description'); ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary btn-block">
        <i class="fa fa-paper-plane-o"></i> Submit
    </button>
</form>

<script>
    document.getElementById('contact').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9+\- ]/g, '').substring(0, 15);
    });
</script>
