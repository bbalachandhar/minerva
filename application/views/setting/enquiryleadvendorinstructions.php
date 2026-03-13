<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <?php $this->load->view('setting/_settingmenu'); ?>
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title titlefix"><i class="fa fa-book"></i> Enquiry Lead API Instructions</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('schsettings/enquiryleadvendors'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Vendors
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <p class="text-muted" style="margin-bottom: 15px;">
                            Source file: <code>docs/lead_enquiry_api_vendor_integration.md</code>
                        </p>
                        <pre style="white-space: pre-wrap; word-break: break-word; background: #f7f7f7; border: 1px solid #e3e3e3; padding: 12px;"><?php echo html_escape((string) $doc_text); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
