<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-file-text-o"></i> Question Paper Distribution
            <small><?php echo htmlspecialchars($event->exam_group_name); ?> — <?php echo htmlspecialchars($event->exam); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_qpd'); ?>"><i class="fa fa-arrow-left"></i> Back to Events</a></li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>
        <div class="row">

            <!-- Upload Panel -->
            <?php if ($this->rbac->hasPrivilege('coe_qpd', 'can_add')): ?>
            <div class="col-md-12">
                <div class="box box-success collapsed-box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-upload"></i> Upload New Question Paper</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <form method="post" action="<?php echo site_url('coe/coe_qpd/upload/' . $batch_exam_id); ?>" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Subject <span class="text-red">*</span></label>
                                        <select name="subject_id" class="form-control" required>
                                            <option value="">— Select Subject —</option>
                                            <?php foreach ($subjects as $sub): ?>
                                                <option value="<?php echo $sub->id; ?>"><?php echo htmlspecialchars($sub->code . ' – ' . $sub->name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Question Paper File <span class="text-red">*</span></label>
                                        <input type="file" name="paper_file" class="form-control" accept=".pdf,.doc,.docx" required>
                                        <p class="help-block">PDF, DOC, DOCX — max 20 MB</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Unlock At (Time Lock) <span class="text-red">*</span></label>
                                        <input type="datetime-local" name="unlock_at" class="form-control" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                                        <p class="help-block">Paper sealed until this date/time</p>
                                    </div>
                                </div>
                                <div class="col-md-2" style="margin-top:25px;">
                                    <button type="submit" class="btn btn-success btn-block"
                                        onclick="return confirm('Upload and encrypt this question paper?')">
                                        <i class="fa fa-lock"></i> Encrypt &amp; Upload
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Papers List -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Uploaded Papers</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Original Filename</th>
                                    <th>Uploaded By</th>
                                    <th>Unlock At</th>
                                    <th>Status</th>
                                    <th>Downloads</th>
                                    <th><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($papers)): ?>
                                    <tr><td colspan="8" class="text-center"><?php echo $this->lang->line('no_record_found'); ?></td></tr>
                                <?php else: ?>
                                    <?php foreach ($papers as $i => $p): ?>
                                    <?php
                                        $is_locked    = strtotime($p->unlock_at) > strtotime($now);
                                        $is_distributed = (bool) $p->is_distributed;
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><strong><?php echo htmlspecialchars($p->subject_code); ?></strong> <?php echo htmlspecialchars($p->subject_name); ?></td>
                                        <td><?php echo htmlspecialchars($p->original_filename); ?></td>
                                        <td><?php echo htmlspecialchars($p->uploaded_by_name ?? '—'); ?></td>
                                        <td>
                                            <?php echo date('d M Y h:i A', strtotime($p->unlock_at)); ?>
                                            <?php if ($is_locked): ?>
                                                <span class="label label-warning"><i class="fa fa-lock"></i> Locked</span>
                                            <?php else: ?>
                                                <span class="label label-success"><i class="fa fa-unlock"></i> Unlocked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($is_distributed): ?>
                                                <span class="label label-info">Distributed<br><?php echo date('d M Y h:i A', strtotime($p->distributed_at)); ?></span>
                                            <?php elseif (!$is_locked): ?>
                                                <span class="label label-default">Not yet distributed</span>
                                            <?php else: ?>
                                                <span class="label label-default">Sealed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo (int) $p->download_count; ?></td>
                                        <td>
                                            <?php if (!$is_locked): ?>
                                                <a href="<?php echo site_url('coe/coe_qpd/download/' . $p->id); ?>" class="btn btn-xs btn-primary" title="Download (decrypted)">
                                                    <i class="fa fa-download"></i> Download
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-xs btn-default" disabled title="Locked until <?php echo date('d M Y h:i A', strtotime($p->unlock_at)); ?>">
                                                    <i class="fa fa-lock"></i> Locked
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($this->rbac->hasPrivilege('coe_qpd', 'can_delete') && !$is_distributed): ?>
                                                <a href="<?php echo site_url('coe/coe_qpd/delete/' . $p->id); ?>" class="btn btn-xs btn-danger"
                                                   onclick="return confirm('Delete this paper? This cannot be undone.')">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
