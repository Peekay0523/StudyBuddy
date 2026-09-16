<?php
/**
 * Student's tutor requests section (pending + rejected) for the study group page.
 */
$pendingRequests = [];
$rejectedRequests = [];
foreach ($myTutorRequests ?? [] as $request) {
    if (($request['status'] ?? '') === 'pending') {
        $pendingRequests[] = $request;
    } elseif (($request['status'] ?? '') === 'rejected') {
        $rejectedRequests[] = $request;
    }
}
?>
<section class="sg-section">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;">
        <div class="sg-section-head" style="margin:0;">
            <span class="sg-section-icon alt-rose"><i class="fas fa-paper-plane"></i></span>
            <div class="sg-section-text">
                <h2>My Tutor Requests</h2>
                <p>Track requests you have sent to tutors.</p>
            </div>
        </div>
    </div>

    <?php if (empty($myTutorRequests)): ?>
        <div class="sg-empty">
            <div class="sg-empty-icon" style="color:#f59e0b;background:rgba(245,158,11,0.12);"><i class="fas fa-paper-plane"></i></div>
            <h3>No tutor requests yet</h3>
            <p>Browse available tutors and send a request to start learning.</p>
        </div>
    <?php else: ?>
        <?php if (!empty($pendingRequests)): ?>
            <h3 style="font-size:16px;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-clock" style="color:#f59e0b;"></i> Pending
            </h3>
            <div class="sg-groups-grid" style="margin-bottom:28px;">
                <?php foreach ($pendingRequests as $req):
                    $tutorName = $req['tutor_name'] ?: ($req['tutor_username'] ?? 'Tutor');
                    $nameParts = preg_split('/\s+/', trim($tutorName));
                    $initials = '';
                    foreach ($nameParts as $part) {
                        if ($part !== '') $initials .= strtoupper(mb_substr($part, 0, 1));
                        if (mb_strlen($initials) >= 2) break;
                    }
                    if ($initials === '') $initials = 'T';
                    $subjects = array_filter(array_map('trim', explode(',', $req['subjects'] ?? '')));
                ?>
                    <div class="feature-card sg-tutor-card" style="border-left:4px solid #f59e0b;">
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#f97316);color:white;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;flex-shrink:0;">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <div style="min-width:0;">
                                <h3 style="font-size:16px;color:#1e293b;margin:0;word-break:break-word;"><?php echo htmlspecialchars($tutorName); ?></h3>
                                <div style="font-size:12px;color:#94a3b8;">Sent <?php echo htmlspecialchars(date('M j, Y', strtotime($req['requested_at']))); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($subjects)): ?>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                <?php foreach ($subjects as $subject): ?><span style="background:#fff7ed;color:#c2410c;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;"><?php echo htmlspecialchars($subject); ?></span><?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div style="display:flex;flex-wrap:wrap;gap:8px;font-size:12px;color:#64748b;">
                            <?php if (!empty($req['grade'])): ?><span><i class="fas fa-layer-group" style="color:#667eea;"></i> <?php echo htmlspecialchars($req['grade']); ?></span><?php endif; ?>
                            <?php if (!empty($req['teaching_mode'])): ?><span><i class="fas fa-<?php echo $req['teaching_mode'] === 'online' ? 'laptop' : 'chalkboard-teacher'; ?>" style="color:#667eea;"></i> <?php echo ucfirst(htmlspecialchars($req['teaching_mode'])); ?></span><?php endif; ?>
                            <?php if (!empty($req['schedule'])): ?><span><i class="fas fa-calendar-alt" style="color:#667eea;"></i> <?php echo htmlspecialchars($req['schedule']); ?></span><?php endif; ?>
                            <?php if (!empty($req['estimated_price'])): ?><span style="color:#059669;font-weight:600;"><i class="fas fa-tag"></i> Est. <?php echo htmlspecialchars($req['estimated_price']); ?></span><?php endif; ?>
                        </div>

                        <span class="td-badge pending" style="align-self:flex-start;"><i class="fas fa-clock"></i> Pending</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($rejectedRequests)): ?>
            <h3 style="font-size:16px;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-times-circle" style="color:#ef4444;"></i> Declined
            </h3>
            <div class="sg-groups-grid">
                <?php foreach ($rejectedRequests as $req):
                    $tutorName = $req['tutor_name'] ?: ($req['tutor_username'] ?? 'Tutor');
                    $nameParts = preg_split('/\s+/', trim($tutorName));
                    $initials = '';
                    foreach ($nameParts as $part) {
                        if ($part !== '') $initials .= strtoupper(mb_substr($part, 0, 1));
                        if (mb_strlen($initials) >= 2) break;
                    }
                    if ($initials === '') $initials = 'T';
                    $subjects = array_filter(array_map('trim', explode(',', $req['subjects'] ?? '')));
                ?>
                    <div class="feature-card sg-tutor-card" style="border-left:4px solid #ef4444;">
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#ec4899);color:white;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;flex-shrink:0;">
                                <?php echo htmlspecialchars($initials); ?>
                            </div>
                            <div style="min-width:0;">
                                <h3 style="font-size:16px;color:#1e293b;margin:0;word-break:break-word;"><?php echo htmlspecialchars($tutorName); ?></h3>
                                <div style="font-size:12px;color:#94a3b8;">Sent <?php echo htmlspecialchars(date('M j, Y', strtotime($req['requested_at']))); ?></div>
                            </div>
                        </div>

                        <?php if (!empty($subjects)): ?>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                <?php foreach ($subjects as $subject): ?><span style="background:#fef2f2;color:#b91c1c;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;"><?php echo htmlspecialchars($subject); ?></span><?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div style="display:flex;flex-wrap:wrap;gap:8px;font-size:12px;color:#64748b;">
                            <?php if (!empty($req['grade'])): ?><span><i class="fas fa-layer-group" style="color:#667eea;"></i> <?php echo htmlspecialchars($req['grade']); ?></span><?php endif; ?>
                            <?php if (!empty($req['teaching_mode'])): ?><span><i class="fas fa-<?php echo $req['teaching_mode'] === 'online' ? 'laptop' : 'chalkboard-teacher'; ?>" style="color:#667eea;"></i> <?php echo ucfirst(htmlspecialchars($req['teaching_mode'])); ?></span><?php endif; ?>
                            <?php if (!empty($req['schedule'])): ?><span><i class="fas fa-calendar-alt" style="color:#667eea;"></i> <?php echo htmlspecialchars($req['schedule']); ?></span><?php endif; ?>
                        </div>

                        <span class="td-badge rejected" style="align-self:flex-start;"><i class="fas fa-times-circle"></i> Declined</span>

                        <?php if (!empty($req['rejection_reason'])): ?>
                            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px;margin-top:4px;">
                                <div style="font-size:12px;font-weight:600;color:#991b1b;margin-bottom:4px;"><i class="fas fa-comment-alt" style="margin-right:6px;"></i>Reason for declining</div>
                                <p style="margin:0;color:#7f1d1d;font-size:13px;line-height:1.5;"><?php echo nl2br(htmlspecialchars($req['rejection_reason'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
