<style>
.sub-card{border:1px solid #e3e8f0;border-radius:8px;padding:10px 14px;margin-bottom:6px;cursor:pointer;transition:background .15s,border-color .15s;display:flex;align-items:center;gap:10px;}
.sub-card:hover{background:#f5f9ff;border-color:#b8d4f0;}
.sub-card.selected{background:#e8f5e9;border-color:#81c784;}
.sub-card input[type=checkbox]{width:16px;height:16px;flex-shrink:0;cursor:pointer;}
.sub-card .sub-name{font-weight:600;font-size:13px;color:#333;flex:1;}
.sub-card .sub-code{font-size:11px;color:#888;font-family:monospace;}
.sub-card .sub-type-pill{font-size:10px;padding:2px 8px;border-radius:10px;font-weight:600;text-transform:uppercase;letter-spacing:.3px;flex-shrink:0;}
.type-theory{background:#d0e8ff;color:#004085;}
.type-practical{background:#d4edda;color:#155724;}
.type-integrated{background:#e2d9f3;color:#432874;}
.type-other{background:#f8d7da;color:#721c24;}
.class-hint-badge{font-size:10px;padding:2px 7px;border-radius:10px;background:#fff3cd;color:#856404;font-weight:600;flex-shrink:0;}
.type-section{margin-bottom:18px;}
.type-section-header{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#666;margin-bottom:8px;padding-bottom:5px;border-bottom:2px solid #f0f0f0;}
#subjectSearch{border-radius:20px;padding-left:32px;}
.search-wrap{position:relative;margin-bottom:16px;}
.search-wrap .fa-search{position:absolute;left:11px;top:9px;color:#aaa;font-size:13px;}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <i class="fa fa-book"></i>
            Assign Exam Subjects
            <?php if ($batch->coe_locked ?? false): ?>
                <span class="label label-danger" style="font-size:12px;vertical-align:middle;"><i class="fa fa-lock"></i> Locked</span>
            <?php endif; ?>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo site_url('coe/coe_dashboard'); ?>"><i class="fa fa-home"></i> CoE</a></li>
            <li><a href="<?php echo site_url('coe/coe_application'); ?>">Exam Applications</a></li>
            <li><a href="<?php echo site_url('coe/coe_application/view/' . $batch->id); ?>"><?php echo htmlspecialchars($batch->exam); ?></a></li>
            <li class="active">Assign Subjects</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('msg'); ?>

        <!-- Batch Info Card -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info" style="border-radius:8px;">
                    <div class="box-header with-border" style="padding:12px 18px;">
                        <h3 class="box-title" style="font-size:15px;">
                            <i class="fa fa-calendar"></i>
                            <?php echo htmlspecialchars($batch->exam); ?>
                            &mdash;
                            <span class="label label-<?php echo ['main'=>'primary','arrear'=>'danger','supplementary'=>'warning'][$batch->exam_category] ?? 'default'; ?>">
                                <?php echo ucfirst($batch->exam_category); ?>
                            </span>
                        </h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('coe/coe_application/view/' . $batch->id); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-arrow-left"></i> Back to Applications
                            </a>
                        </div>
                    </div>
                    <div class="box-body" style="padding:12px 18px;">
                        <div class="row" style="font-size:13px;">
                            <?php if ($batch->class ?? ''): ?>
                            <div class="col-sm-3"><strong>Class:</strong> <?php echo htmlspecialchars($batch->class); ?></div>
                            <?php endif; ?>
                            <div class="col-sm-3"><strong>From:</strong> <?php echo htmlspecialchars($batch->date_from ?? '—'); ?></div>
                            <div class="col-sm-3"><strong>To:</strong> <?php echo htmlspecialchars($batch->date_to ?? '—'); ?></div>
                            <div class="col-sm-3"><strong>Pass %:</strong> <?php echo htmlspecialchars($batch->passing_percentage ?? '—'); ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($batch->coe_locked ?? false): ?>
        <!-- LOCKED: read-only view -->
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-default" style="border-radius:8px;">
                    <div class="box-header with-border" style="padding:14px 18px;">
                        <h3 class="box-title" style="font-size:14px;"><i class="fa fa-lock"></i> Configured Subjects (<?php echo count($configured_ids); ?>)</h3>
                    </div>
                    <div class="box-body">
                        <?php if (empty($configured_ids)): ?>
                            <p class="text-muted text-center" style="padding:20px;"><i class="fa fa-info-circle"></i> No subjects assigned and batch is locked.</p>
                        <?php else: ?>
                            <table class="table table-bordered table-condensed" style="font-size:13px;">
                                <thead><tr><th>#</th><th>Subject</th><th>Code</th><th>Type</th></tr></thead>
                                <tbody>
                                <?php $i=1; foreach ($all_subjects as $sub): if (!in_array((int)$sub->id, $configured_ids)) continue; ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($sub->name); ?></td>
                                    <td><code><?php echo htmlspecialchars($sub->code ?? '—'); ?></code></td>
                                    <td><?php echo ucfirst($sub->type ?? '—'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>

        <!-- EDITABLE FORM -->
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary" style="border-radius:8px;">
                    <div class="box-header with-border" style="padding:14px 18px;background:#f8f9fa;">
                        <h3 class="box-title" style="font-size:14px;"><i class="fa fa-list-ul"></i> Select Subjects for this Batch Exam</h3>
                        <div class="box-tools pull-right" style="display:flex;gap:8px;align-items:center;">
                            <span id="selectedCount" style="font-size:12px;color:#555;font-weight:600;">
                                <?php echo count($configured_ids); ?> selected
                            </span>
                            <a href="#" id="selAll" class="btn btn-xs btn-default">Select All</a>
                            <a href="#" id="selNone" class="btn btn-xs btn-default">Clear All</a>
                            <?php if (!empty($class_subject_ids)): ?>
                            <a href="#" id="selClass" class="btn btn-xs btn-info" title="Select subjects linked to this class via Subject Groups">
                                <i class="fa fa-filter"></i> Select for This Class
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (empty($all_subjects)): ?>
                            <div class="text-center text-muted" style="padding:40px;">
                                <i class="fa fa-book" style="font-size:40px;display:block;margin-bottom:10px;color:#ddd;"></i>
                                No active subjects found in the system. Add subjects first.
                            </div>
                        <?php else: ?>
                        <!-- Search -->
                        <div class="search-wrap">
                            <i class="fa fa-search"></i>
                            <input type="text" id="subjectSearch" class="form-control input-sm" placeholder="Search subjects by name or code…">
                        </div>

                        <form method="post" action="<?php echo site_url('coe/coe_subject/assign/' . $batch->id); ?>" id="subjectsForm">

                        <?php
                        // Group by type
                        $grouped = [];
                        foreach ($all_subjects as $sub) {
                            $t = $sub->type ?: 'other';
                            $grouped[$t][] = $sub;
                        }
                        $type_order = ['theory', 'practical', 'integrated', 'other'];
                        uksort($grouped, function($a, $b) use ($type_order) {
                            $ia = array_search($a, $type_order); $ib = array_search($b, $type_order);
                            if ($ia === false) $ia = 99; if ($ib === false) $ib = 99;
                            return $ia - $ib;
                        });
                        ?>

                        <?php foreach ($grouped as $type => $subs): ?>
                        <div class="type-section">
                            <div class="type-section-header">
                                <?php echo ucfirst($type); ?> (<?php echo count($subs); ?>)
                            </div>
                            <?php foreach ($subs as $sub):
                                $checked  = in_array((int)$sub->id, $configured_ids);
                                $in_class = in_array((int)$sub->id, $class_subject_ids);
                                $type_cls = in_array($sub->type, ['theory','practical','integrated']) ? 'type-' . $sub->type : 'type-other';
                            ?>
                            <label class="sub-card <?php echo $checked ? 'selected' : ''; ?>"
                                   id="lbl_<?php echo $sub->id; ?>"
                                   data-name="<?php echo strtolower(htmlspecialchars($sub->name . ' ' . ($sub->code ?? ''))); ?>">
                                <input type="checkbox"
                                       name="subject_ids[]"
                                       value="<?php echo (int)$sub->id; ?>"
                                       <?php echo $checked ? 'checked' : ''; ?>
                                       data-class-subject="<?php echo $in_class ? '1' : '0'; ?>"
                                       onchange="onSubjectToggle(this)">
                                <span class="sub-name"><?php echo htmlspecialchars($sub->name); ?></span>
                                <?php if ($sub->code ?? ''): ?>
                                <span class="sub-code"><?php echo htmlspecialchars($sub->code); ?></span>
                                <?php endif; ?>
                                <span class="sub-type-pill <?php echo $type_cls; ?>"><?php echo ucfirst($sub->type ?: '—'); ?></span>
                                <?php if ($in_class): ?>
                                <span class="class-hint-badge" title="This subject is linked to this class via Subject Groups"><i class="fa fa-star"></i> Class</span>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>

                        <div style="margin-top:18px;border-top:1px solid #eee;padding-top:16px;">
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="fa fa-save"></i> Save Subject Assignment
                            </button>
                            <a href="<?php echo site_url('coe/coe_application/view/' . $batch->id); ?>" class="btn btn-default" style="margin-left:8px;">
                                Cancel
                            </a>
                            <span id="saveCount" style="font-size:12px;color:#888;margin-left:12px;"></span>
                        </div>

                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right panel: summary + tips -->
            <div class="col-md-4">
                <div class="box box-default" style="border-radius:8px;">
                    <div class="box-header with-border" style="padding:12px 16px;background:#f8f9fa;">
                        <h3 class="box-title" style="font-size:13px;"><i class="fa fa-info-circle text-info"></i> How It Works</h3>
                    </div>
                    <div class="box-body" style="font-size:12px;color:#555;line-height:1.7;">
                        <p><strong>Step 1:</strong> Select all subjects that will be examined in this batch exam.</p>
                        <p><strong>Step 2:</strong> Save the selection. Each selected subject creates an entry in the batch exam schedule.</p>
                        <p><strong>Step 3:</strong> Go back and run <strong>Generate Applications</strong> — students will be enrolled per subject.</p>
                        <p><strong>Step 4:</strong> Run <strong>Eligibility</strong> to check attendance and arrear rules per subject.</p>
                        <hr style="margin:10px 0;">
                        <p class="text-muted"><i class="fa fa-star" style="color:#f39c12;"></i> Subjects marked <strong>Class</strong> are already linked to this batch's class via Subject Groups.</p>
                        <?php if (!empty($configured_ids)): ?>
                        <div class="alert alert-success" style="padding:8px 12px;margin-top:8px;border-radius:6px;">
                            <strong><i class="fa fa-check-circle"></i> <?php echo count($configured_ids); ?> subject(s)</strong> currently assigned.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning" style="padding:8px 12px;margin-top:8px;border-radius:6px;">
                            <i class="fa fa-exclamation-triangle"></i> No subjects assigned yet. Applications cannot be generated without subjects.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($configured_ids)): ?>
                <div class="box box-default" style="border-radius:8px;margin-top:0;">
                    <div class="box-header with-border" style="padding:12px 16px;background:#f8f9fa;">
                        <h3 class="box-title" style="font-size:13px;"><i class="fa fa-check-circle text-success"></i> Currently Assigned (<?php echo count($configured_ids); ?>)</h3>
                    </div>
                    <div class="box-body" style="padding:10px 14px;">
                        <?php foreach ($all_subjects as $sub): if (!in_array((int)$sub->id, $configured_ids)) continue; ?>
                        <div style="font-size:12px;padding:4px 0;border-bottom:1px solid #f5f5f5;display:flex;align-items:center;gap:8px;">
                            <i class="fa fa-check-circle text-success" style="font-size:11px;"></i>
                            <span><?php echo htmlspecialchars($sub->name); ?></span>
                            <?php if ($sub->code ?? ''): ?><code style="font-size:10px;"><?php echo htmlspecialchars($sub->code); ?></code><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; /* not locked */ ?>

    </section>
</div>

<script>
(function(){
    var classSubjectIds = <?php echo json_encode(array_values($class_subject_ids)); ?>;

    function updateCount() {
        var n = document.querySelectorAll('#subjectsForm input[type=checkbox]:checked').length;
        var sc = document.getElementById('selectedCount');
        var sv = document.getElementById('saveCount');
        if (sc) sc.textContent = n + ' selected';
        if (sv) sv.textContent = n > 0 ? 'Will save ' + n + ' subject(s).' : 'No subjects selected — all will be deactivated.';
    }

    window.onSubjectToggle = function(cb) {
        var lbl = document.getElementById('lbl_' + cb.value);
        if (lbl) lbl.classList.toggle('selected', cb.checked);
        updateCount();
    };

    // Select All
    document.getElementById('selAll') && document.getElementById('selAll').addEventListener('click', function(e){
        e.preventDefault();
        document.querySelectorAll('#subjectsForm .sub-card:not([style*="display:none"]) input[type=checkbox]').forEach(function(cb){
            cb.checked = true;
            var lbl = document.getElementById('lbl_' + cb.value);
            if (lbl) lbl.classList.add('selected');
        });
        updateCount();
    });

    // Clear All
    document.getElementById('selNone') && document.getElementById('selNone').addEventListener('click', function(e){
        e.preventDefault();
        document.querySelectorAll('#subjectsForm input[type=checkbox]').forEach(function(cb){
            cb.checked = false;
            var lbl = document.getElementById('lbl_' + cb.value);
            if (lbl) lbl.classList.remove('selected');
        });
        updateCount();
    });

    // Select for This Class
    document.getElementById('selClass') && document.getElementById('selClass').addEventListener('click', function(e){
        e.preventDefault();
        document.querySelectorAll('#subjectsForm input[type=checkbox]').forEach(function(cb){
            var inClass = cb.getAttribute('data-class-subject') === '1';
            cb.checked = inClass;
            var lbl = document.getElementById('lbl_' + cb.value);
            if (lbl) lbl.classList.toggle('selected', inClass);
        });
        updateCount();
    });

    // Live search
    var searchInput = document.getElementById('subjectSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(){
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('#subjectsForm .sub-card').forEach(function(card){
                var txt = card.getAttribute('data-name') || '';
                card.style.display = (!q || txt.indexOf(q) !== -1) ? '' : 'none';
            });
            // hide empty type sections
            document.querySelectorAll('.type-section').forEach(function(sec){
                var visible = sec.querySelectorAll('.sub-card:not([style*="display:none"])').length;
                sec.style.display = visible ? '' : 'none';
            });
        });
    }

    // Form submit confirmation
    var form = document.getElementById('subjectsForm');
    if (form) {
        form.addEventListener('submit', function(e){
            var n = form.querySelectorAll('input[type=checkbox]:checked').length;
            if (n === 0) {
                if (!confirm('No subjects selected. This will deactivate all existing subject assignments for this batch. Continue?')) {
                    e.preventDefault();
                }
            }
        });
    }

    updateCount();
})();
</script>
