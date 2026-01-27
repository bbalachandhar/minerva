<!DOCTYPE html>
<html>
<head>
    <title><?php echo $this->lang->line('billdesk_payment'); ?></title>
</head>
<body>
    <form action="<?php echo $form_action; ?>" method="POST" name="sdklaunch" id="sdklaunch">
        <input type="hidden" id="bdorderid" name="bdorderid" value="<?php echo $fields['bdorderid']; ?>">
        <input type="hidden" id="merchantid" name="merchantid" value="<?php echo $fields['merchantid']; ?>">
        <input type="hidden" id="rdata" name="rdata" value="<?php echo $fields['rdata']; ?>">
        <center><h3><?php echo $this->lang->line('please_wait_for_system_to_redirect'); ?></h3></center>
    </form>
    <script type="text/javascript">
        document.getElementById("sdklaunch").submit();
    </script>
</body>
</html>