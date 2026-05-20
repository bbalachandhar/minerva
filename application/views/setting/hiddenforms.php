<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><i class="fa fa-globe"></i> Hidden Form URLs</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Form Name</th>
                                        <th>Description</th>
                                        <th>URL</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Admission Enquiry</td>
                                        <td>A public form for prospective students or parents to submit admission enquiries.</td>
                                        <td><a href="<?php echo site_url('enquiry'); ?>" target="_blank"><?php echo site_url('enquiry'); ?></a></td>
                                        <td class="text-right">
                                            <button class="btn btn-default btn-xs copy-to-clipboard" data-clipboard-text="<?php echo site_url('enquiry'); ?>" data-toggle="tooltip" title="Copy to Clipboard">
                                                <i class="fa fa-clipboard"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Online Admission Form</td>
                                        <td>A public form for students to apply for admission online.</td>
                                        <td><a href="<?php echo site_url('publicadmissionform'); ?>" target="_blank"><?php echo site_url('publicadmissionform'); ?></a></td>
                                        <td class="text-right">
                                            <button class="btn btn-default btn-xs copy-to-clipboard" data-clipboard-text="<?php echo site_url('welcome/admission'); ?>" data-toggle="tooltip" title="Copy to Clipboard">
                                                <i class="fa fa-clipboard"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Complaint / Suggestion Form</td>
                                        <td>A public form for anyone (including non-registered users) to submit complaints or suggestions.</td>
                                        <td><a href="<?php echo site_url('complaint'); ?>" target="_blank"><?php echo site_url('complaint'); ?></a></td>
                                        <td class="text-right">
                                            <button class="btn btn-default btn-xs copy-to-clipboard" data-clipboard-text="<?php echo site_url('complaint'); ?>" data-toggle="tooltip" title="Copy to Clipboard">
                                                <i class="fa fa-clipboard"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var clipboard = new ClipboardJS('.copy-to-clipboard');

        clipboard.on('success', function(e) {
            var original_title = $(e.trigger).attr('data-original-title');
            $(e.trigger).attr('data-original-title', 'Copied!').tooltip('show');
            setTimeout(function(){
                $(e.trigger).tooltip('hide').attr('data-original-title', original_title);
            }, 1000);
            e.clearSelection();
        });

        clipboard.on('error', function(e) {
            // You can add error handling here if needed
        });

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
