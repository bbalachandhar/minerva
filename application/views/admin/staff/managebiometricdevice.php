<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-gears"></i> Biometric Device Management</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add Biometric Device</h3>
                    </div>
                    <form action="<?php echo site_url('admin/staff/' . ((isset($device_record['id']) && $device_record['id'] != '') ? 'edit_biometric_device/' . $device_record['id'] : 'add_biometric_device')) ?>" id="device_form" class="form-horizontal" method="post">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="id" value="<?php echo set_value('id', (isset($device_record['id'])) ? $device_record['id'] : ''); ?>">
                            <div class="form-group">
                                <label for="device_name" class="col-sm-4 control-label">Device Name <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="device_name" placeholder="Device Name" required="" value="<?php echo set_value('device_name', (isset($device_record['device_name'])) ? $device_record['device_name'] : ''); ?>">
                                    <span class="text-danger"><?php echo form_error('device_name'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="brand" class="col-sm-4 control-label">Brand <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="brand" required="">
                                        <option value="">Select Brand</option>
                                        <?php foreach ($device_brands as $brand) { ?>
                                            <option value="<?php echo $brand; ?>" <?php echo set_select('brand', $brand, (isset($device_record['brand']) && $device_record['brand'] == $brand) ? TRUE : FALSE); ?>><?php echo $brand; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('brand'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="serial_number" class="col-sm-4 control-label">Serial Number <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="serial_number" placeholder="Serial Number" required="" value="<?php echo set_value('serial_number', (isset($device_record['serial_number'])) ? $device_record['serial_number'] : ''); ?>">
                                    <span class="text-danger"><?php echo form_error('serial_number'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="api_endpoint" class="col-sm-4 control-label">API Endpoint <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="url" class="form-control" name="api_endpoint" placeholder="API Endpoint" required="" value="<?php echo set_value('api_endpoint', (isset($device_record['api_endpoint'])) ? $device_record['api_endpoint'] : ''); ?>">
                                    <span class="text-danger"><?php echo form_error('api_endpoint'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="username" class="col-sm-4 control-label">Username <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" name="username" placeholder="Username" required="" value="<?php echo set_value('username', (isset($device_record['username'])) ? $device_record['username'] : ''); ?>">
                                    <span class="text-danger"><?php echo form_error('username'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password" class="col-sm-4 control-label">Password <span class="text-danger">*</span></label>
                                <div class="col-sm-8">
                                    <input type="password" class="form-control" name="password" placeholder="Password" required="" value="<?php echo set_value('password', (isset($device_record['password'])) ? $device_record['password'] : ''); ?>">
                                    <span class="text-danger"><?php echo form_error('password'); ?></span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="is_active" class="col-sm-4 control-label">Active</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" name="is_active" value="1" <?php echo set_checkbox('is_active', '1', (isset($device_record['is_active']) && $device_record['is_active'] == '1') ? TRUE : FALSE); ?>>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right">Save</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Biometric Device List</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="biometric_device_table" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Brand</th>
                                        <th>Serial Number</th>
                                        <th>API Endpoint</th>
                                        <th>Username</th>
                                        <th>Active</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($device_list as $device) { ?>
                                        <tr>
                                        <td><?php echo $device['brand']; ?></td>
                                            <td><?php echo $device['serial_number']; ?></td>
                                            <td><?php echo $device['api_endpoint']; ?></td>
                                            <td><?php echo $device['username']; ?></td>
                                            <td><?php echo ($device['is_active'] == 1) ? 'Yes' : 'No'; ?></td>
                                            <td>
                                                <a href="<?php echo site_url('admin/staff/edit_biometric_device/' . $device['id']); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="Edit">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="<?php echo site_url('admin/staff/delete_biometric_device/' . $device['id']); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="Delete" onclick="return confirm('Are you sure you want to delete this item?');">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                                <?php if ($device['is_active'] == 0) { ?>
                                                    <a href="<?php echo site_url('admin/staff/activate_biometric_device/' . $device['id']); ?>" class="btn btn-default btn-xs" data-toggle="tooltip" title="Activate" onclick="return confirm('Are you sure you want to activate this device? This will deactivate any other active device.');">
                                                        <i class="fa fa-check-circle"></i>
                                                    </a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
$(document).ready(function() {
    $('#biometric_device_table').DataTable();
});
</script>