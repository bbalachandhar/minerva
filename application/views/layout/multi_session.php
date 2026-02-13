<!-- Current Session Modal -->
<div id="currentSessionModal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <form action="<?php echo site_url('admin/admin/updateSession') ?>" method="POST" id="form_session_change">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><?php echo $this->lang->line('current_session'); ?></h4>
                </div>
                <div class="modal-body sessionSwitchbody">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class='fa fa-spinner fa-spin' style='display:none;'></i>
                        <?php echo $this->lang->line('save'); ?>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script type="text/javascript">
     $('#currentSessionModal').on('show.bs.modal', function (event) {
      
        var $modalDiv = $(event.delegateTarget);
        $('.sessionSwitchbody').html("");
        $.ajax({
            type: "POST",
            url: baseurl + "admin/admin/getSession",
            dataType: 'text',
            data: {},
            beforeSend: function () {
                $modalDiv.addClass('modal_loading');
            },
            success: function (data) {
                $('.sessionSwitchbody').html(data);
            },
            error: function (xhr) {
                $modalDiv.removeClass('modal_loading');
                alert('Error loading session data');
            },
            complete: function () {
                $modalDiv.removeClass('modal_loading');
            },
        });
    });

    $("#form_session_change").on('submit', (function (e) {
        e.preventDefault();

         var form = $(this);
        var $this = $(this).find("button[type=submit]");
        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            dataType: 'json',
          
            beforeSend: function () {
                $this.prop('disabled', true);
                $this.find('i').show();
            },
            success: function (res)
            {
                    if (res.status == 1) {
                        successMsg(res.message);
                        $('#currentSessionModal').modal('hide');
                        window.location.href = baseurl + "admin/admin/dashboard";

                    } else {
                        errorMsg(res.message);
                    }
            },
            error: function (xhr) {
                alert("Error occurred. Please try again");
            },
            complete: function () {
                $this.prop('disabled', false);
                $this.find('i').hide();
            }

        });
    }));
</script>
