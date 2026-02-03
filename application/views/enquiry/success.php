<div class="text-center">
    <h3>Thank You!</h3>
    <p><?php echo $this->session->flashdata('success_message'); ?></p>
    <a href="<?php echo isset($website_url) ? $website_url : base_url(); ?>" class="btn btn-primary">Back to Home</a>
</div>
