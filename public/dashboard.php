<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_login();

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user = current_user();
$role = $user['role'];

$stats = [];

$stats['total_candidates'] = (int) $pdo->query("
    SELECT COUNT(*)
    FROM candidates
")->fetchColumn();

$stats['today_interviews'] = $role === 'manager'
    ? (function ($pdo, $uid) {
        $s = $pdo->prepare("
            SELECT COUNT(*)
            FROM interview_rounds
            WHERE manager_id = ?
              AND DATE(scheduled_at) = CURDATE()
        ");
        $s->execute([$uid]);
        return (int) $s->fetchColumn();
    })($pdo, $user['id'])
    : (int) $pdo->query("
        SELECT COUNT(*)
        FROM interview_rounds
        WHERE DATE(scheduled_at) = CURDATE()
    ")->fetchColumn();

$stats['selected'] = (int) $pdo->query("
    SELECT COUNT(*)
    FROM candidates
    WHERE current_status IN ('selected', 'direct_selected', 'manager_selected')
       OR final_decision = 'selected'
")->fetchColumn();

$stats['rejected'] = (int) $pdo->query("
    SELECT COUNT(*)
    FROM candidates
    WHERE current_status IN ('rejected', 'rejected_without_interview', 'manager_rejected')
       OR final_decision = 'rejected'
")->fetchColumn();

$pendingSql = "
    SELECT
        c.id,
        c.application_no,
        c.full_name,
        c.position_applied,
        c.current_status,
        ir.round_name,
        ir.round_no,
        ir.interview_status,
        u.full_name AS manager_name
    FROM interview_rounds ir
    JOIN candidates c ON c.id = ir.candidate_id
    LEFT JOIN users u ON u.id = ir.manager_id
";

if ($role === 'manager') {
    $pendingSql .= "
        WHERE ir.manager_id = ?
          AND ir.interview_status = 'assigned'
    ";
    $pendingStmt = $pdo->prepare($pendingSql . " ORDER BY ir.id DESC");
    $pendingStmt->execute([$user['id']]);
} else {
    $pendingSql .= "
        WHERE ir.interview_status IN ('assigned', 'under_review')
    ";
    $pendingStmt = $pdo->prepare($pendingSql . " ORDER BY ir.id DESC");
    $pendingStmt->execute();
}

$pendingFeedbacks = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
$pendingCount = count($pendingFeedbacks);

if ($role === 'manager') {
    $stmt = $pdo->prepare("
        SELECT
            ir.id AS round_id,
            c.id,
            c.application_no,
            c.full_name,
            c.position_applied,
            c.current_status,
            ir.round_name,
            ir.interview_status,
            ir.scheduled_at,
            u.full_name AS manager_name
        FROM interview_rounds ir
        JOIN candidates c ON c.id = ir.candidate_id
        LEFT JOIN users u ON u.id = ir.manager_id
        WHERE ir.manager_id = ?
        ORDER BY ir.id DESC
    ");
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $rows = $pdo->query("
        SELECT
            c.id,
            c.application_no,
            c.full_name,
            c.position_applied,
            c.current_status,
            c.final_decision,
            c.applied_at,
            (
                SELECT f.recommendation
                FROM interview_feedback f
                WHERE f.candidate_id = c.id
                ORDER BY f.id DESC
                LIMIT 1
            ) AS latest_feedback,
            (
                SELECT f.remark_text
                FROM interview_feedback f
                WHERE f.candidate_id = c.id
                ORDER BY f.id DESC
                LIMIT 1
            ) AS latest_remark,
            (
                SELECT u.full_name
                FROM interview_feedback f
                LEFT JOIN users u ON u.id = f.manager_id
                WHERE f.candidate_id = c.id
                ORDER BY f.id DESC
                LIMIT 1
            ) AS manager_name
        FROM candidates c
        ORDER BY c.id DESC
        LIMIT 50
    ")->fetchAll(PDO::FETCH_ASSOC);
}

$title = ucfirst($role) . ' Dashboard';
include __DIR__ . '/../app/views/layouts/header.php';
?>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f7f3f7;
    color: #2c2337;
}

.container {
    max-width: 1220px;
    margin: 24px auto;
    padding: 0 16px 40px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 18px;
}

.card {
    background: #fff;
    border: 1px solid #eadfeb;
    border-radius: 18px;
    padding: 18px;
    box-shadow: 0 10px 24px rgba(122, 69, 119, .06);
}

.card h3 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #5f536f;
    text-align: center;
}

.card p {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #b23284;
    text-align: center;
}

.selected p { color: #2e8b57; }
.rejected p { color: #c03b5f; }

.pending-card {
    cursor: pointer;
    transition: transform .15s ease;
}

.pending-card:hover {
    transform: translateY(-2px);
}

.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin: 0 0 14px;
}

.section-title h2 {
    margin: 0;
    font-size: 24px;
    color: #2f2640;
}

.pill {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 999px;
    background: #f5dff0;
    color: #b23284;
    font-size: 12px;
    font-weight: 700;
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
}

th, td {
    border-bottom: 1px solid #f1e8f1;
    padding: 12px 10px;
    text-align: left;
    font-size: 13px;
    vertical-align: top;
}

th {
    background: #fbf7fb;
    color: #5f536f;
    font-size: 12px;
    text-transform: uppercase;
}

.badge {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    background: #f5dff0;
    color: #b23284;
}

.btn {
    display: inline-block;
    border: 1px solid #eadfeb;
    border-radius: 12px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    color: #b23284;
    background: #fff;
}

.btn:hover { background: #fdf7fb; }
.btn-outline { background: #fff; }

.modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: rgba(0, 0, 0, .45);
    padding: 24px;
}

.modal.open {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    width: min(1100px, 100%);
    max-height: 85vh;
    overflow: auto;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, .25);
    padding: 18px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
    color: #2d2340;
}

.modal-close {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    background: #f1e3ef;
    color: #a23c7f;
    font-size: 26px;
    line-height: 1;
}

.pending-table {
    width: 100%;
    border-collapse: collapse;
}

.pending-table th,
.pending-table td {
    border-bottom: 1px solid #eee3ee;
    padding: 12px 10px;
    text-align: left;
    font-size: 13px;
}

.pending-table th {
    background: #fbf7fb;
    color: #5f536f;
    text-transform: uppercase;
    font-size: 12px;
}

.empty {
    padding: 14px;
    border: 1px dashed #e6d8e6;
    border-radius: 14px;
    background: #fcfafc;
    color: #83778f;
}

@media (max-width: 1200px) {
    .stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 760px) {
    .stats { grid-template-columns: 1fr; }
}
</style>

<div class="container">
    <?php if ($role !== 'manager'): ?>
    <section class="stats">
        <div class="card"><h3>Total Candidates</h3><p><?= (int) $stats['total_candidates'] ?></p></div>
        <div class="card"><h3>Today Interviews</h3><p><?= (int) $stats['today_interviews'] ?></p></div>
        <div class="card selected"><h3>Selected</h3><p><?= (int) $stats['selected'] ?></p></div>
        <div class="card rejected"><h3>Rejected</h3><p><?= (int) $stats['rejected'] ?></p></div>
        <div class="card pending-card" onclick="openPendingModal()">
            <h3>Pending Feedback</h3>
            <p><?= (int) $pendingCount ?></p>
        </div>
    </section>
    <?php endif; ?>

    <div class="section-title">
        <h2>Assigned Interviews</h2>
        <span class="pill"><?= h($role) ?> view</span>
    </div>

    <div class="card">
        <table>
            <thead>
                <?php if ($role === 'manager'): ?>
                <tr>
                    <th>Round</th>
                    <th>App No</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Current Status</th>
                    <th>Interview Status</th>
                    <th>Action</th>
                </tr>
                <?php else: ?>
                <tr>
                    <th>App No</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Current Status</th>
                    <th>Manager</th>
                    <th>Latest Feedback</th>
                    <th>Remark</th>
                    <th>Action</th>
                </tr>
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
                        <td><span class="badge"><?= h($row['current_status']) ?></span></td>
                        <td><span class="badge"><?= h($row['interview_status']) ?></span></td>
                        <td><a class="btn" href="manager-review.php?round_id=<?= (int) $row['round_id'] ?>">Open</a></td>
                    <?php else: ?>
                        <?php
                        $latestFeedback = $row['latest_feedback'] ?: '-';
                        if (in_array($latestFeedback, ['selected', 'direct_selected', 'manager_selected'], true)) {
                            $latestFeedback = 'select';
                        } elseif (in_array($latestFeedback, ['rejected', 'rejected_without_interview', 'manager_rejected'], true)) {
                            $latestFeedback = 'reject';
                        }
                        ?>
                        <td><?= h($row['application_no']) ?></td>
                        <td><?= h($row['full_name']) ?></td>
                        <td><?= h($row['position_applied']) ?></td>
                        <td><span class="badge"><?= h($row['current_status']) ?></span></td>
                        <td><?= h($row['manager_name'] ?: '-') ?></td>
                        <td><?= h($latestFeedback) ?></td>
                        <td><?= h($row['latest_remark'] ?: '-') ?></td>
                        <td><a class="btn" href="recruiter-candidate.php?id=<?= (int) $row['id'] ?>">Open</a></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="pendingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pending Feedback</h3>
                <button class="modal-close" type="button" onclick="closePendingModal()">&times;</button>
            </div>

            <div class="modal-body">
                <?php if ($pendingFeedbacks): ?>
                <table class="pending-table">
                    <thead>
                        <tr>
                            <th>App No</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Manager</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingFeedbacks as $row): ?>
                        <tr>
                            <td><?= h($row['application_no'] ?? '') ?></td>
                            <td><?= h($row['full_name'] ?? '') ?></td>
                            <td><?= h($row['position_applied'] ?? '') ?></td>
                            <td><?= h($row['interview_status'] ?? '') ?></td>
                            <td><?= h($row['manager_name'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty">No pending feedback found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function openPendingModal() {
    document.getElementById('pendingModal').classList.add('open');
}

function closePendingModal() {
    document.getElementById('pendingModal').classList.remove('open');
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closePendingModal();
});

document.getElementById('pendingModal').addEventListener('click', function (e) {
    if (e.target === this) closePendingModal();
});
</script>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>