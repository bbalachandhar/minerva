<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th><input type="checkbox" id="select_all_students" /></th>
                <th><?php echo $this->lang->line('admission_no'); ?></th>
                <th><?php echo $this->lang->line('student_name'); ?></th>
                <th><?php echo $this->lang->line('class'); ?></th>
                <th class="text-right"><?php echo $this->lang->line('advance_balance'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="5" class="text-center">No students found with an advance balance.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><input type="checkbox" class="student_checkbox" name="student_session_ids[]" value="<?php echo $student['student_session_id']; ?>" /></td>
                        <td><?php echo $student['admission_no']; ?></td>
                        <td><?php echo $this->customlib->getFullName($student['firstname'], $student['middlename'], $student['lastname'], $sch_setting->middlename, $sch_setting->lastname); ?></td>
                        <td><?php echo $student['class'] . ' (' . $student['section'] . ')'; ?></td>
                        <td class="text-right"><?php echo amountFormat($student['advance']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="box-footer">
    <button type="button" class="btn btn-primary pull-right" id="apply_advance_btn">Apply Advance to Selected</button>
</div>
