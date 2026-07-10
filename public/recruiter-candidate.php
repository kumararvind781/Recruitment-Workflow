<?php
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';
require_role(['admin','recruiter']);
$pdo = Database::connect();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, dr.file_path AS resume_path, dr.original_file_name AS resume_name, dp.file_path AS photo_path, dp.original_file_name AS photo_name FROM candidates c LEFT JOIN candidate_documents dr ON dr.candidate_id = c.id AND dr.document_type = 'resume' LEFT JOIN candidate_documents dp ON dp.candidate_id = c.id AND dp.document_type = 'photo' WHERE c.id = ? LIMIT 1");
$stmt->execute([$id]);
$candidate = $stmt->fetch();
if (!$candidate) exit('Candidate not found');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $note = trim($_POST['note'] ?? '');
    $oldStatus = $candidate['current_status'];
    $userId = current_user()['id'];
    if ($action === 'reject_without_interview') {
        if ($note === '') $note = 'Rejected without interview';
        $pdo->prepare("UPDATE candidates SET current_status='rejected_without_interview', final_decision='rejected' WHERE id=?")->execute([$id]);
        $pdo->prepare("INSERT INTO candidate_status_logs (candidate_id, round_id, action_by, action_role, old_status, new_status, note) VALUES (?, NULL, ?, ?, ?, 'rejected_without_interview', ?)")->execute([$id, $userId, current_user()['role'], $oldStatus, $note]);
    } elseif ($action === 'direct_select') {
        $pdo->prepare("UPDATE candidates SET current_status='direct_selected', final_decision='selected' WHERE id=?")->execute([$id]);
        $pdo->prepare("INSERT INTO candidate_status_logs (candidate_id, round_id, action_by, action_role, old_status, new_status, note) VALUES (?, NULL, ?, ?, ?, 'direct_selected', ?)")->execute([$id, $userId, current_user()['role'], $oldStatus, $note ?: 'Direct selected']);
    } elseif ($action === 'update_profile') {
        $pdo->prepare("UPDATE candidates SET full_name=?, email=?, phone=?, position_applied=?, address=?, total_experience=?, current_status='under_recruiter_review' WHERE id=?")
            ->execute([trim($_POST['full_name']), trim($_POST['email']), trim($_POST['phone']), trim($_POST['position_applied']), trim($_POST['address']), (float)$_POST['total_experience'], $id]);
        $pdo->prepare("INSERT INTO candidate_status_logs (candidate_id, round_id, action_by, action_role, old_status, new_status, note) VALUES (?, NULL, ?, ?, ?, 'under_recruiter_review', ?)")->execute([$id, $userId, current_user()['role'], $oldStatus, $note ?: 'Profile updated']);
    } elseif ($action === 'send_to_manager') {
        $managerId = (int)($_POST['manager_id'] ?? 0);
        $nextStmt = $pdo->prepare("SELECT COALESCE(MAX(round_no),0)+1 FROM interview_rounds WHERE candidate_id = ?");
        $nextStmt->execute([$id]);
        $roundNo = (int)$nextStmt->fetchColumn();
        $pdo->prepare("INSERT INTO interview_rounds (candidate_id, round_no, round_name, recruiter_id, manager_id, interview_status, scheduled_at) VALUES (?, ?, ?, ?, ?, 'assigned', ?)")
            ->execute([$id, $roundNo, 'Round ' . $roundNo, $userId, $managerId, $_POST['scheduled_at'] ?: null]);
        $roundId = $pdo->lastInsertId();
        $pdo->prepare("UPDATE candidates SET current_status='sent_to_manager' WHERE id=?")->execute([$id]);
        $pdo->prepare("INSERT INTO candidate_status_logs (candidate_id, round_id, action_by, action_role, old_status, new_status, note) VALUES (?, ?, ?, ?, ?, 'sent_to_manager', ?)")->execute([$id, $roundId, $userId, current_user()['role'], $oldStatus, $note ?: 'Sent to manager']);
    } elseif ($action === 'final_select') {
        $pdo->prepare("UPDATE candidates SET current_status='selected', final_decision='selected' WHERE id=?")->execute([$id]);
        $pdo->prepare("INSERT INTO candidate_status_logs (candidate_id, round_id, action_by, action_role, old_status, new_status, note) VALUES (?, NULL, ?, ?, ?, 'selected', ?)")->execute([$id, $userId, current_user()['role'], $oldStatus, $note ?: 'Selected']);
    } elseif ($action === 'final_reject') {
        if ($note === '') $note = 'Rejected after feedback';
        $pdo->prepare("UPDATE candidates SET current_status='rejected', final_decision='rejected' WHERE id=?")->execute([$id]);
        $pdo->prepare("INSERT INTO candidate_status_logs (candidate_id, round_id, action_by, action_role, old_status, new_status, note) VALUES (?, NULL, ?, ?, ?, 'rejected', ?)")->execute([$id, $userId, current_user()['role'], $oldStatus, $note]);
    }
    header('Location: recruiter-candidate.php?id=' . $id);
    exit;
}
$managers = $pdo->query("SELECT u.id, u.full_name FROM users u JOIN user_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE r.name='manager' AND u.status='active' ORDER BY u.full_name")->fetchAll();
$rounds = $pdo->prepare("SELECT ir.*, u.full_name AS manager_name FROM interview_rounds ir JOIN users u ON u.id = ir.manager_id WHERE ir.candidate_id = ? ORDER BY ir.round_no DESC");
$rounds->execute([$id]);
$roundList = $rounds->fetchAll();
$feedback = $pdo->prepare("SELECT f.*, u.full_name AS manager_name FROM interview_feedback f JOIN users u ON u.id = f.manager_id WHERE f.candidate_id = ? ORDER BY f.id DESC");
$feedback->execute([$id]);
$feedbackList = $feedback->fetchAll();
$logs = $pdo->prepare("SELECT * FROM candidate_status_logs WHERE candidate_id = ? ORDER BY id DESC");
$logs->execute([$id]);
$logList = $logs->fetchAll();
$title = 'Candidate Review';
include __DIR__ . '/../app/views/layouts/header.php';
?>
<div class="grid grid-2">
    <div class="card">
        <div class="section-title"><h2><?= h($candidate['full_name']) ?></h2><span class="badge pending"><?= h($candidate['current_status']) ?></span></div><?php if (!empty($candidate['photo_path'])): ?><img src="<?= h($candidate['photo_path']) ?>" alt="Candidate Photo" style="width:120px;height:140px;object-fit:cover;border-radius:18px;margin-bottom:16px;"><?php endif; ?><div class="mini-meta"><p><strong>Reference:</strong> <?= h($candidate['source_type']) ?><?= !empty($candidate['source_reference_name']) ? ' / ' . h($candidate['source_reference_name']) : '' ?></p>
        
      <?php if (!empty($candidate['resume_path'])): ?>
    <p>
        <a class="btn btn-outline"
           href="<?= h($candidate['resume_path']) ?>"
           target="_blank"
           rel="noopener noreferrer">
           Open Resume
        </a>
    </p>
<?php endif; ?></div>



        <form method="post">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group"><label>Full Name</label><input name="full_name" value="<?= h($candidate['full_name']) ?>"></div>
            <div class="form-group"><label>Email</label><input name="email" value="<?= h($candidate['email']) ?>"></div>
            <div class="form-group"><label>Phone</label><input name="phone" value="<?= h($candidate['phone']) ?>"></div>
            <div class="form-group"><label>Position Applied</label><input name="position_applied" value="<?= h($candidate['position_applied']) ?>"></div>
            <div class="form-group"><label>Experience</label><input name="total_experience" value="<?= h($candidate['total_experience']) ?>"></div>
            <div class="form-group"><label>Address</label><textarea name="address" rows="4"><?= h($candidate['address']) ?></textarea></div>
            <div class="form-group"><label>Edit Note</label><textarea name="note" rows="3"></textarea></div>
            <button class="btn" type="submit">Save Changes</button>
        </form>
    </div>
    <div class="card">
        <div class="section-title"><h2>Actions</h2><span class="pill">Select / Reject / Next round</span></div>
        <form method="post" style="margin-bottom:16px;">
            <input type="hidden" name="action" value="reject_without_interview">
            <div class="form-group"><label>Reject reason</label><textarea name="note" rows="3" required></textarea></div>
            <button class="btn btn-danger" type="submit">Reject Without Interview</button>
        </form>
        <form method="post" style="margin-bottom:16px;">
            <input type="hidden" name="action" value="direct_select">
            <div class="form-group"><label>Select note</label><textarea name="note" rows="2"></textarea></div>
            <button class="btn btn-success" type="submit">Direct Select</button>
        </form>
        <form method="post" style="margin-bottom:16px;">
            <input type="hidden" name="action" value="send_to_manager">
            <div class="form-group"><label>Select Manager</label><select name="manager_id" required><option value="">Choose manager</option><?php foreach ($managers as $m): ?><option value="<?= (int)$m['id'] ?>"><?= h($m['full_name']) ?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label>Interview Date/Time</label><input type="datetime-local" name="scheduled_at"></div>
            <div class="form-group"><label>Round note</label><textarea name="note" rows="3"></textarea></div>
            <button class="btn btn-warning" type="submit">Next Round / Send to Manager</button>
        </form>
        <form method="post" style="margin-bottom:16px;">
            <input type="hidden" name="action" value="final_select">
            <div class="form-group"><label>Selection note</label><textarea name="note" rows="2"></textarea></div>
            <button class="btn btn-success" type="submit">Final Select</button>
        </form>
        <form method="post">
            <input type="hidden" name="action" value="final_reject">
            <div class="form-group"><label>Final reject reason</label><textarea name="note" rows="3" required></textarea></div>
            <button class="btn btn-danger" type="submit">Final Reject</button>
        </form>
    </div>
</div>
<div class="grid grid-2" style="margin-top:20px;">
    <div class="card">
        <div class="section-title"><h2>Interview Rounds</h2><span class="badge round"><?= count($roundList) ?> rounds</span></div>
        <div class="timeline"><?php foreach ($roundList as $r): ?><div class="timeline-item"><strong><?= h($r['round_name']) ?></strong><br><span class="small muted">Manager: <?= h($r['manager_name']) ?> | Status: <?= h($r['interview_status']) ?> | Scheduled: <?= h($r['scheduled_at']) ?></span></div><?php endforeach; ?></div>
    </div>
    <div class="card">
        <div class="section-title"><h2>Manager Feedback</h2><span class="pill">Current feedback</span></div>
        <div class="timeline"><?php foreach ($feedbackList as $f): ?><div class="timeline-item"><strong><?= h($f['manager_name']) ?></strong><br><span class="small">Recommendation: <?= h($f['recommendation']) ?></span><p><?= nl2br(h($f['remark_text'])) ?></p></div><?php endforeach; ?></div>
    </div>
</div>
<div class="card" style="margin-top:20px;">
    <div class="section-title"><h2>Status History</h2><span class="pill">Current status and remarks</span></div>
    <div class="timeline"><?php foreach ($logList as $log): ?><div class="timeline-item"><strong><?= h($log['new_status']) ?></strong><br><span class="small muted"><?= h($log['action_role']) ?> | <?= h($log['created_at']) ?></span><p><?= nl2br(h($log['note'])) ?></p></div><?php endforeach; ?></div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
