<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-warning"></i> UFM Incident #<?php echo $incident->id; ?><button type="button" class="coe-info-btn" data-toggle="modal" data-target="#coeHelpModal"><i class="fa fa-info-circle"></i></button></h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_ufm/listing/' . $incident->batch_exam_id); ?>"><i class="fa fa-arrow-left"></i> Back to Incidents</a></li>
        </ol>
    </section>
    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <?php $this->load->view('admin/coe/coe_ufm/_incident_detail', ['incident' => $incident]); ?>
    </section>
</div>

<?php $this->load->view('admin/coe/_help_modal', ['help_key' => 'ufm']); ?>
