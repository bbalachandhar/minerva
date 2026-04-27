<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$prLabels = ['low'=>'success','medium'=>'warning','high'=>'danger','critical'=>'danger'];
$stLabels = ['open'=>'danger','in_progress'=>'warning','resolved'=>'success','closed'=>'default'];
$pr = $complaint_data['priority'] ?? 'medium';
$st = $complaint_data['status']   ?? 'open';
?>
<table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <th><?php echo $this->lang->line('ticket_no'); ?></th>
            <td><strong><?php echo htmlspecialchars($complaint_data['ticket_no'] ?: '#'.$complaint_data['id']); ?></strong></td>
            <th><?php echo $this->lang->line('complaint_type'); ?></th>
            <td><?php echo htmlspecialchars($complaint_data['complaint_type']); ?></td>
        </tr>
        <tr>
            <th><?php echo $this->lang->line('priority'); ?></th>
            <td><span class="label label-<?php echo $prLabels[$pr] ?? 'default'; ?>"><?php echo ucfirst($pr); ?></span></td>
            <th><?php echo $this->lang->line('status'); ?></th>
            <td><span class="label label-<?php echo $stLabels[$st] ?? 'default'; ?>"><?php echo ucwords(str_replace('_',' ',$st)); ?></span></td>
        </tr>
        <tr>
            <th><?php echo $this->lang->line('source'); ?></th>
            <td><?php echo htmlspecialchars($complaint_data['source']); ?></td>
            <th><?php echo $this->lang->line('submitted_by'); ?></th>
            <td><?php echo !empty($complaint_data['submitted_by']) ? ucfirst($complaint_data['submitted_by']) : $this->lang->line('walk_in'); ?></td>
        </tr>
        <tr>
            <th><?php echo $this->lang->line('name'); ?></th>
            <td><?php echo htmlspecialchars($complaint_data['name']); ?> <?php if (!empty($complaint_data['email'])) echo '(' . htmlspecialchars($complaint_data['email']) . ')'; ?></td>
            <th><?php echo $this->lang->line('phone'); ?></th>
            <td><?php echo htmlspecialchars($complaint_data['contact']); ?></td>
        </tr>
        <?php if (!empty($complaint_data['admission_no']) || !empty($complaint_data['class_name'])): ?>
        <tr>
            <th><?php echo $this->lang->line('admission_no'); ?></th>
            <td><?php echo htmlspecialchars($complaint_data['admission_no'] ?? ''); ?></td>
            <th><?php echo $this->lang->line('class'); ?> / <?php echo $this->lang->line('section'); ?></th>
            <td><?php echo htmlspecialchars($complaint_data['class_name'] ?? ''); ?> <?php echo !empty($complaint_data['section_name']) ? '- ' . htmlspecialchars($complaint_data['section_name']) : ''; ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($complaint_data['parent_name'])): ?>
        <tr>
            <th><?php echo $this->lang->line('guardian_name'); ?></th>
            <td colspan="3"><?php echo htmlspecialchars($complaint_data['parent_name']); ?></td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($complaint_data['employee_id'])): ?>
        <tr>
            <th><?php echo $this->lang->line('employee_id'); ?></th>
            <td colspan="3"><?php echo htmlspecialchars($complaint_data['employee_id']); ?></td>
        </tr>
        <?php endif; ?>
        <tr>
            <th><?php echo $this->lang->line('date'); ?></th>
            <td><?php if ($complaint_data['date'] != '0000-00-00') echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($complaint_data['date'])); ?></td>
            <th><?php echo $this->lang->line('assigned'); ?></th>
            <td><?php echo htmlspecialchars($complaint_data['assigned']); ?></td>
        </tr>
        <tr>
            <th><?php echo $this->lang->line('description'); ?></th>
            <td colspan="3"><?php echo nl2br(htmlspecialchars($complaint_data['description'])); ?></td>
        </tr>
        <tr>
            <th><?php echo $this->lang->line('action_taken'); ?></th>
            <td colspan="3"><?php echo nl2br(htmlspecialchars($complaint_data['action_taken'])); ?></td>
        </tr>
        <tr>
            <th><?php echo $this->lang->line('note'); ?></th>
            <td colspan="3"><?php echo nl2br(htmlspecialchars($complaint_data['note'])); ?></td>
        </tr>
        <?php if (!empty($complaint_data['admin_response'])): ?>
        <tr>
            <th><?php echo $this->lang->line('admin_response'); ?></th>
            <td colspan="3" class="bg-success">
                <?php echo nl2br(htmlspecialchars($complaint_data['admin_response'])); ?>
                <?php if (!empty($complaint_data['responded_at'])): ?>
                    <br><small class="text-muted"><?php echo $this->lang->line('responded_by'); ?>: <?php echo htmlspecialchars($complaint_data['responded_by_name'] ?? ''); ?> &mdash; <?php echo date($this->customlib->getSchoolDateFormat(), strtotime($complaint_data['responded_at'])); ?></small>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
        <?php if (!empty($complaint_data['image'])): ?>
        <tr>
            <th><?php echo $this->lang->line('attachment'); ?></th>
            <td colspan="3"><a href="<?php echo base_url(); ?>admin/complaint/download/<?php echo $complaint_data['id']; ?>" class="btn btn-xs btn-default"><i class="fa fa-download"></i> <?php echo $this->lang->line('download'); ?></a></td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($this->rbac->hasPrivilege('complaint', 'can_edit')): ?>
<hr>
<h4><?php echo $this->lang->line('respond_to_complaint'); ?></h4>
<form id="respond-form-<?php echo $complaint_data['id']; ?>" class="respond-form">
    <input type="hidden" name="complaint_id" value="<?php echo $complaint_data['id']; ?>">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label><?php echo $this->lang->line('status'); ?></label>
                <select name="status" class="form-control">
                    <?php foreach (['open','in_progress','resolved','closed'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php if ($st === $s) echo 'selected'; ?>><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label><?php echo $this->lang->line('priority'); ?></label>
                <select name="priority" class="form-control">
                    <?php foreach (['low','medium','high','critical'] as $p): ?>
                        <option value="<?php echo $p; ?>" <?php if ($pr === $p) echo 'selected'; ?>><?php echo ucfirst($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label><?php echo $this->lang->line('assigned'); ?></label>
                <select name="assigned" id="modal-assigned-<?php echo $complaint_data['id']; ?>" class="form-control assigned-select2">
                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                    <?php foreach ($staff_list as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['name']); ?>" <?php if ($complaint_data['assigned'] === $s['name']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($s['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label><?php echo $this->lang->line('action_taken'); ?></label>
        <input type="text" name="action_taken" class="form-control" value="<?php echo htmlspecialchars($complaint_data['action_taken']); ?>">
    </div>
    <div class="form-group">
        <label><?php echo $this->lang->line('admin_response'); ?> <small class="text-muted">(<?php echo $this->lang->line('visible_to_reporter'); ?>)</small></label>
        <textarea name="admin_response" class="form-control" rows="3"><?php echo htmlspecialchars($complaint_data['admin_response']); ?></textarea>
    </div>
    <button type="button" class="btn btn-primary" onclick="respondComplaint(<?php echo $complaint_data['id']; ?>)">
        <i class="fa fa-reply"></i> <?php echo $this->lang->line('save_response'); ?>
    </button>
</form>
<?php endif; ?>

<script>
$(function() {
    $('.assigned-select2').select2({
        placeholder: '<?php echo $this->lang->line('select'); ?>',
        allowClear: true,
        dropdownParent: $('#complaintdetails')
    });
});
function respondComplaint(id) {
    var form = $('#respond-form-' + id);
    $.post('<?php echo base_url(); ?>admin/complaint/respond/' + id, form.serialize(), function(res) {
        if (res.status === 'success') {
            $('#complaintdetails').modal('hide');
            location.reload();
        } else {
            alert(res.message || 'Error saving response.');
        }
    }, 'json');
}
</script>