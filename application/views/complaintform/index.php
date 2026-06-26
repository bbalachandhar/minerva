<?php if (validation_errors()): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo validation_errors(); ?></div>
<?php endif; ?>

<form action="<?php echo site_url('complaint'); ?>" method="post">
    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

    <div class="section-card">
        <div class="section-title"><i class="bi bi-megaphone-fill"></i> Complaint / Suggestion Form</div>
        <p style="font-size:13px; color:var(--text-muted); margin:-12px 0 20px;">Use this form to submit a complaint or suggestion. You do not need an account.</p>
        <div class="field-grid">
            <div>
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?php echo set_value('name'); ?>" placeholder="Your full name" required>
            </div>
            <div>
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="complaint_type" class="form-select" required>
                    <option value="">-- Select Type --</option>
                    <?php foreach ($complaint_types as $ct): ?>
                        <option value="<?php echo htmlspecialchars($ct['complaint_type']); ?>"
                            <?php echo set_select('complaint_type', $ct['complaint_type']); ?>>
                            <?php echo htmlspecialchars($ct['complaint_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Phone <span style="font-size:10px; text-transform:none; font-weight:400; color:var(--text-muted);">(optional)</span></label>
                <input type="tel" class="form-control" name="contact" id="contact" value="<?php echo set_value('contact'); ?>" maxlength="15" placeholder="e.g. 9876543210">
            </div>
            <div>
                <label class="form-label">Email <span style="font-size:10px; text-transform:none; font-weight:400; color:var(--text-muted);">(optional)</span></label>
                <input type="email" class="form-control" name="email" value="<?php echo set_value('email'); ?>" placeholder="your@email.com">
            </div>
            <div class="full-width">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" name="description" rows="5" placeholder="Please describe your complaint or suggestion in detail..." required><?php echo set_value('description'); ?></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-submit">
        <i class="bi bi-send-fill me-2"></i> Submit
    </button>
</form>

<script>
document.getElementById('contact').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9+\- ]/g, '').substring(0, 15);
});
</script>
