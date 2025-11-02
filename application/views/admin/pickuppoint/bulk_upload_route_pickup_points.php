<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Bulk Upload Route Pickup Points</h3>
    </div>
    <form action="<?php echo site_url('admin/pickuppoint/bulk_upload_route_pickup_points') ?>" method="post" enctype="multipart/form-data">
        <div class="box-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="exampleInputFile">Select CSV File</label>
                <input type="file" id="exampleInputFile" name="file">
                <p class="help-block">Please upload a CSV file with 'Route Title', 'Pickup Point Name', 'Distance', 'Pickup Time', 'Fees' columns. The first row should be the header.</p>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary">Upload</button>
        </div>
    </form>
</div>