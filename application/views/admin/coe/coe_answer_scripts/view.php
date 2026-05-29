<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-file-text-o"></i> Script Detail
            <small><?php echo htmlspecialchars($script->hall_ticket_no); ?> &mdash; <?php echo htmlspecialchars($script->subject_code . ' ' . $script->subject_name); ?></small>
        <button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li>
                <a href="<?php echo site_url('coe/coe_answer_scripts/listing/' . $script->exam_group_class_batch_exam_id); ?>">
                    <i class="fa fa-arrow-left"></i> Back to Listing
                </a>
            </li>
        </ol>
    </section>
    <section class="content">
        <?php $this->load->view('admin/coe/coe_answer_scripts/_script_detail', ['script' => $script]); ?>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'answer_scripts']); ?>
