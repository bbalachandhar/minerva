<?php
// Partial view — loaded via AJAX (both inside edit_application and studentList modal)
// Variables: $follow_up_list, $date_fmt, $online_admission_id, $can_delete
$can_delete = isset($can_delete) ? $can_delete : false;
?>
<?php if (empty($follow_up_list)): ?>
    <p class="text-muted" style="padding:8px 0;"><i class="fa fa-info-circle"></i> No follow-up notes yet.</p>
<?php else: ?>
<table class="table table-condensed table-bordered" style="margin-bottom:0;">
    <thead>
        <tr>
            <th style="width:12%">Date</th>
            <th>Note</th>
            <th style="width:15%">Next Contact</th>
            <th style="width:18%">Added By</th>
            <?php if ($can_delete): ?><th style="width:5%" class="text-center">Del</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($follow_up_list as $row): ?>
        <tr>
            <td class="white-space-nowrap"><?php echo date($date_fmt, strtotime($row['created_at'])); ?></td>
            <td><?php echo nl2br(htmlspecialchars($row['note'])); ?></td>
            <td class="white-space-nowrap">
                <?php if (!empty($row['next_contact_date']) && $row['next_contact_date'] !== '0000-00-00'): ?>
                    <span class="label label-info"><?php echo date($date_fmt, strtotime($row['next_contact_date'])); ?></span>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['staff_name'] ?? '—'); ?></td>
            <?php if ($can_delete): ?>
            <td class="text-center">
                <a onclick="deleteFollowup(<?php echo (int)$row['id']; ?>, <?php echo (int)$online_admission_id; ?>)"
                   class="btn btn-danger btn-xs" title="Delete"
                   style="cursor:pointer;"><i class="fa fa-trash-o"></i></a>
            </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
