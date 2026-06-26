<div class="success-card">
    <i class="bi bi-check-circle-fill success-icon"></i>
    <h3>Thank You!</h3>
    <p>Your complaint / suggestion has been submitted successfully.<br>Our team will review it shortly.</p>
    <?php if (!empty($ticket_no)): ?>
        <div style="margin-bottom:8px; font-size:12px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:600;">Your Reference Number</div>
        <div class="ticket-badge"><?php echo htmlspecialchars($ticket_no); ?></div>
        <p style="font-size:12px;">Please save this reference number for follow-up.</p>
    <?php endif; ?>
    <div style="margin-top:8px;">
        <a href="<?php echo isset($website_url) ? $website_url : base_url(); ?>" class="btn-link-styled btn-link-default">
            <i class="bi bi-house-door"></i> Back to Home
        </a>
        <a href="<?php echo site_url('complaint'); ?>" class="btn-link-styled btn-link-primary">
            <i class="bi bi-plus-circle"></i> Submit Another
        </a>
    </div>
</div>
