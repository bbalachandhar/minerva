
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i> <?php echo $this->lang->line('hall_bookings'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('hall_booking_request'); ?></h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/hall/book') ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> <?php echo $this->lang->line('book_hall'); ?></a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <?php echo $this->session->flashdata('msg') ?>
                        <?php } ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover booking-table">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('hall_name'); ?></th>
                                        <th><?php echo $this->lang->line('booked_by'); ?></th>
                                        <th><?php echo $this->lang->line('purpose'); ?></th>
                                        <th><?php echo $this->lang->line('start_time'); ?></th>
                                        <th><?php echo $this->lang->line('end_time'); ?></th>
                                        <th><?php echo $this->lang->line('status'); ?></th>
                                        <th class="text-right no-print"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($bookingList)) {
                                        ?>
                                        <tr>
                                            <td colspan="7" class="text-danger text-center"><?php echo $this->lang->line('no_record_found'); ?></td>
                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($bookingList as $booking) {
                                            ?>
                                            <tr>
                                                <td class="mailbox-name"><?php echo $booking->hall_name ?></td>
                                                <td class="mailbox-name"><?php echo $booking->booked_by_staff_name . ' (' . $booking->employee_id . ')' ?></td>
                                                <td class="mailbox-name"><?php echo $booking->purpose ?></td>
                                                <td class="mailbox-name"><?php echo $this->customlib->dateTimeformat($booking->start_time) ?></td>
                                                <td class="mailbox-name"><?php echo $this->customlib->dateTimeformat($booking->end_time) ?></td>
                                                <td class="mailbox-name">
                                                    <?php
                                                    if ($booking->status == 'pending') {
                                                        echo '<span class="label label-warning">' . $this->lang->line('pending') . '</span>';
                                                    } elseif ($booking->status == 'approved') {
                                                        echo '<span class="label label-success">' . $this->lang->line('approved') . '</span>';
                                                    } elseif ($booking->status == 'rejected') {
                                                        echo '<span class="label label-danger">' . $this->lang->line('rejected') . '</span>';
                                                    } elseif ($booking->status == 'cancelled') {
                                                        echo '<span class="label label-info">' . $this->lang->line('cancelled') . '</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="mailbox-date pull-right no-print">
                                                    <?php if ($booking->status == 'pending') { ?>
                                                        <?php if ($this->rbac->hasPrivilege('hall_booking_approval', 'can_edit')) { ?>
                                                            <a href="#" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('approve'); ?>" onclick="confirm_approve(<?php echo $booking->id; ?>)">
                                                                <i class="fa fa-check"></i>
                                                            </a>
                                                            <a href="#" class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php echo $this->lang->line('reject'); ?>" onclick="confirm_reject(<?php echo $booking->id; ?>)">
                                                                <i class="fa fa-times"></i>
                                                            </a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <a href="<?php echo base_url(); ?>admin/hall/delete_booking/<?php echo $booking->id ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Approve/Reject Modal -->
<div class="modal fade" id="approveRejectModal" tabindex="-1" role="dialog" aria-labelledby="approveRejectModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="approveRejectModalLabel"><?php echo $this->lang->line('booking_action'); ?></h4>
            </div>
            <form action="" method="POST" id="approveRejectForm">
                <div class="modal-body">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <input type="hidden" name="booking_id" id="modal_booking_id">
                    <div class="form-group">
                        <label for="remarks"><?php echo $this->lang->line('remarks'); ?></label>
                        <textarea name="remarks" id="remarks" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->lang->line('close'); ?></button>
                    <button type="submit" class="btn btn-primary" id="modalActionButton"><?php echo $this->lang->line('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function confirm_approve(booking_id) {
        $('#modal_booking_id').val(booking_id);
        $('#approveRejectModalLabel').text('<?php echo $this->lang->line('approve_booking'); ?>');
        $('#modalActionButton').text('<?php echo $this->lang->line('approve'); ?>').removeClass('btn-danger').addClass('btn-success');
        $('#approveRejectForm').attr('action', '<?php echo site_url('admin/hall/approve_booking/') ?>' + booking_id);
        $('#approveRejectModal').modal('show');
    }

    function confirm_reject(booking_id) {
        $('#modal_booking_id').val(booking_id);
        $('#approveRejectModalLabel').text('<?php echo $this->lang->line('reject_booking'); ?>');
        $('#modalActionButton').text('<?php echo $this->lang->line('reject'); ?>').removeClass('btn-success').addClass('btn-danger');
        $('#approveRejectForm').attr('action', '<?php echo site_url('admin/hall/reject_booking/') ?>' + booking_id);
        $('#approveRejectModal').modal('show');
    }

    $(document).ready(function() {
        $('.booking-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]
        });
    });
</script>