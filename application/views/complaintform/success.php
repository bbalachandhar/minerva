<div class="text-center" style="padding: 20px 0;">
    <i class="fa fa-check-circle text-success" style="font-size:60px;"></i>
    <h3 style="margin-top:15px;">Thank You!</h3>
    <p class="text-muted">Your complaint / suggestion has been submitted successfully. Our team will review it shortly.</p>
    <?php if (!empty($ticket_no)): ?>
        <div class="alert alert-info" style="display:inline-block; padding: 10px 30px;">
            <strong>Your Reference Number:</strong> <span style="font-size:1.2em;"><?php echo htmlspecialchars($ticket_no); ?></span>
        </div>
        <p class="text-muted"><small>Please save this reference number for follow-up.</small></p>
    <?php endif; ?>
    <a href="<?php echo isset($website_url) ? $website_url : base_url(); ?>" class="btn btn-default">
        <i class="fa fa-home"></i> Back to Home
    </a>
    <a href="<?php echo site_url('complaint'); ?>" class="btn btn-primary" style="margin-left:10px;">
        <i class="fa fa-plus"></i> Submit Another
    </a>
</div>
