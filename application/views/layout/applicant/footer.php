    </div><!-- /.content-wrapper -->

    <footer class="main-footer">
        &copy; <?php echo date('Y'); ?>
        <?php
        $setting_result = $this->setting_model->getSetting();
        echo isset($setting_result->name) ? htmlspecialchars($setting_result->name) : (isset($sch_name) ? htmlspecialchars($sch_name) : 'School Management System');
        ?>
        &nbsp;|&nbsp; Applicant Portal
    </footer>
    <div class="control-sidebar-bg"></div>
</div><!-- ./wrapper -->

<script src="<?php echo base_url(); ?>backend/bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>backend/sweet-alert/sweetalert2.min.js"></script>
<link href="<?php echo base_url(); ?>backend/toast-alert/toastr.css" rel="stylesheet"/>
<script src="<?php echo base_url(); ?>backend/toast-alert/toastr.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo base_url(); ?>backend/plugins/fastclick/fastclick.min.js"></script>
<script src="<?php echo base_url(); ?>backend/dist/js/app.min.js"></script>
</body>
</html>
