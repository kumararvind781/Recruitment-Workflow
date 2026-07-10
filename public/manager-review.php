<?php
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';
require_role('manager');
$pdo = Database::connect();
$user = current_user();
$roundId = (int)($_GET['round_id'] ?? 0);
$stmt = $pdo->prepare("SELECT ir.*, c.full_name, c.email, c.phone, c.address, c.position_applied, c.total_experience, c.current_status FROM interview_rounds ir JOIN candidates c ON c.id = ir.candidate_id WHERE ir.id = ? AND ir.manager_id = ? LIMIT 1");
$stmt->execute([$roundId, $user['id']]);
$data = $stmt->fetch();
if (!$data) exit('Interview round not found');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remark = trim($_POST['remark_text'] ?? '');
    $recommendation = $_POST['recommendation'] ?? 'hold';
    if ($remark !== '') {
        $pdo->prepare("INSERT INTO interview_feedback (round_id, candidate_id, manager_id, remark_text, recommendation) VALUES (?, ?, ?, ?, ?)")->execute([$roundId, $data['candidate_id'], $user['id'], $remark, $recommendation]);
        $pdo->prepare("UPDATE interview_rounds SET interview_status='returned_to_recruiter', feedback_submitted_at=NOW(), closed_at=NOW() WHERE id=?")->execute([$roundId]);
        $pdo->prepare("UPDATE candidates SET current_status='manager_feedback_received' WHERE id=?")->execute([$data['candidate_id']]);
        $pdo->prepare("INSERT INTO candidate_status_logs (candidate_id, round_id, action_by, action_role, old_status, new_status, note) VALUES (?, ?, ?, 'manager', 'sent_to_manager', 'manager_feedback_received', ?)")->execute([$data['candidate_id'], $roundId, $user['id'], 'Manager feedback submitted: ' . $recommendation]);
        header('Location: manager-dashboard.php');
        exit;
    }
}
$title = 'Manager Review';
include __DIR__ . '/../app/views/layouts/header.php';
?>
<div class="grid grid-2">
    <div class="card">
        <div class="section-title"><h2><?= h($data['full_name']) ?></h2><span class="badge round"><?= h($data['round_name']) ?></span></div>
        <p><strong>Position:</strong> <?= h($data['position_applied']) ?></p>
        <p><strong>Email:</strong> <?= h($data['email']) ?></p>
        <p><strong>Phone:</strong> <?= h($data['phone']) ?></p>
        <p><strong>Experience:</strong> <?= h($data['total_experience']) ?> years</p>
        <p><strong>Address:</strong> <?= h($data['address']) ?></p>
    </div>
    <div class="card">
        <div class="section-title"><h2>Add Remark</h2><span class="pill">Returns to recruiter</span></div>
        <form method="post">
            <div class="form-group"><label>Recommendation</label><select name="recommendation"><option value="hold">Hold</option><option value="next_round">Next Round</option><option value="select">Select</option><option value="reject">Reject</option></select></div>
            <div class="form-group"><label>Manager Remark</label><textarea name="remark_text" rows="8" required></textarea></div>
            <button class="btn" type="submit">Send Feedback to Recruiter</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
