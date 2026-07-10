<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';
require_login();
$pdo = Database::connect();
$user = current_user();
$role = $user['role'];
$whereRounds = '';
$whereCandidates = '';
$paramsRounds = [];
$paramsCandidates = [];
if ($role === 'manager') {
    $whereRounds = ' WHERE ir.manager_id = ?';
    $paramsRounds[] = $user['id'];
}
$stats = [];
$stats['total_candidates'] = (int)$pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$stats['today_interviews'] = $role === 'manager'
    ? (function($pdo,$uid){ $s=$pdo->prepare("SELECT COUNT(*) FROM interview_rounds WHERE manager_id=? AND DATE(scheduled_at)=CURDATE()"); $s->execute([$uid]); return (int)$s->fetchColumn(); })($pdo,$user['id'])
    : (int)$pdo->query("SELECT COUNT(*) FROM interview_rounds WHERE DATE(scheduled_at)=CURDATE()")->fetchColumn();
$stats['selected'] = (int)$pdo->query("SELECT COUNT(*) FROM candidates WHERE current_status IN ('selected','direct_selected') OR final_decision='selected'")->fetchColumn();
$stats['rejected'] = (int)$pdo->query("SELECT COUNT(*) FROM candidates WHERE current_status IN ('rejected','rejected_without_interview') OR final_decision='rejected'")->fetchColumn();
$stats['pending_feedback'] = $role === 'manager'
    ? (function($pdo,$uid){ $s=$pdo->prepare("SELECT COUNT(*) FROM interview_rounds WHERE manager_id=? AND interview_status='assigned'"); $s->execute([$uid]); return (int)$s->fetchColumn(); })($pdo,$user['id'])
    : (int)$pdo->query("SELECT COUNT(*) FROM interview_rounds WHERE interview_status IN ('assigned','under_review')")->fetchColumn();

if ($role === 'manager') {
    $stmt = $pdo->prepare("SELECT ir.id AS round_id, c.id, c.application_no, c.full_name, c.position_applied, c.current_status, ir.round_name, ir.interview_status, ir.scheduled_at
        FROM interview_rounds ir
        JOIN candidates c ON c.id = ir.candidate_id
        WHERE ir.manager_id = ?
        ORDER BY ir.id DESC");
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll();
} else {
    $rows = $pdo->query("SELECT c.id, c.application_no, c.full_name, c.position_applied, c.current_status, c.final_decision, c.applied_at,
        (SELECT recommendation FROM interview_feedback f WHERE f.candidate_id = c.id ORDER BY f.id DESC LIMIT 1) AS latest_feedback,
        (SELECT remark_text FROM interview_feedback f WHERE f.candidate_id = c.id ORDER BY f.id DESC LIMIT 1) AS latest_remark
        FROM candidates c ORDER BY c.id DESC LIMIT 50")->fetchAll();
}
$title = ucfirst($role) . ' Dashboard';
include __DIR__ . '/../app/views/layouts/header.php';
?>
<section class="stats grid grid-4">
    <div class="card"><h3>Total Candidates</h3><p><?= $stats['total_candidates'] ?></p></div>
    <div class="card"><h3>Today Interviews</h3><p><?= $stats['today_interviews'] ?></p></div>
    <div class="card"><h3>Selected</h3><p><?= $stats['selected'] ?></p></div>
    <div class="card"><h3>Rejected</h3><p><?= $stats['rejected'] ?></p></div>
    <div class="card"><h3>Pending Feedback</h3><p><?= $stats['pending_feedback'] ?></p></div>
</section>
<?php if ($role === 'admin'): ?>
<div class="section-title"><h2>Admin Controls</h2><div class="actions"><a class="btn" href="users.php">Manage Users</a><a class="btn btn-outline" href="apply.php">Candidate Form</a></div></div>
<?php endif; ?>
<div class="section-title"><h2><?= $role === 'manager' ? 'Assigned Interviews' : 'Interview Status Overview' ?></h2><span class="pill"><?= h($role) ?> view</span></div>
<div class="card">
    <table>
        <thead>
        <?php if ($role === 'manager'): ?>
            <tr><th>Round</th><th>App No</th><th>Name</th><th>Position</th><th>Current Status</th><th>Interview Status</th><th>Action</th></tr>
        <?php else: ?>
            <tr><th>App No</th><th>Name</th><th>Position</th><th>Current Status</th><th>Latest Feedback</th><th>Remark</th><th>Action</th></tr>
        <?php endif; ?>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <?php if ($role === 'manager'): ?>
                    <td><?= h($row['round_name']) ?></td>
                    <td><?= h($row['application_no']) ?></td>
                    <td><?= h($row['full_name']) ?></td>
                    <td><?= h($row['position_applied']) ?></td>
                    <td><span class="badge pending"><?= h($row['current_status']) ?></span></td>
                    <td><span class="badge round"><?= h($row['interview_status']) ?></span></td>
                    <td><a class="btn btn-outline" href="manager-review.php?round_id=<?= (int)$row['round_id'] ?>">Open</a></td>
                <?php else: ?>
                    <td><?= h($row['application_no']) ?></td>
                    <td><?= h($row['full_name']) ?></td>
                    <td><?= h($row['position_applied']) ?></td>
                    <td><span class="badge pending"><?= h($row['current_status']) ?></span></td>
                    <td><?= h($row['latest_feedback'] ?: '-') ?></td>
                    <td><?= h($row['latest_remark'] ?: '-') ?></td>
                    <td><a class="btn btn-outline" href="recruiter-candidate.php?id=<?= (int)$row['id'] ?>">Open</a></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>
