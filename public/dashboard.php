<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

$stats['pending_feedback'] = $role === 'manager'
    ? (function ($pdo, $uid) {
        $s = $pdo->prepare("
            SELECT COUNT(*)
            FROM interview_rounds
            WHERE manager_id = ?
              AND interview_status = 'assigned'
        ");
        $s->execute([$uid]);
        return (int) $s->fetchColumn();
    })($pdo, $user['id'])
    : (int) $pdo->query("
        SELECT COUNT(*)
        FROM interview_rounds
        WHERE interview_status IN ('assigned', 'under_review')
    ")->fetchColumn();

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

//  Add pending query

$pendingStmt = $pdo->prepare("
    SELECT
        c.id,
        c.application_no,
        c.full_name,
        c.position_applied,
        c.current_status,
        ir.round_name,
        ir.round_no,
        u.full_name AS manager_name
    FROM candidates c
    LEFT JOIN interview_rounds ir
        ON ir.candidate_id = c.id
       AND ir.id = (
            SELECT ir2.id
            FROM interview_rounds ir2
            WHERE ir2.candidate_id = c.id
            ORDER BY ir2.id DESC
            LIMIT 1
       )
    LEFT JOIN users u
        ON u.id = ir.manager_id
    WHERE c.current_status = 'sent_to_manager'
    ORDER BY u.full_name ASC, c.id DESC
");
$pendingStmt->execute();
$pendingFeedbacks = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);


$pendingCount = count($pendingFeedbacks);

//  Add pending query eND

$title = ucfirst($role) . ' Dashboard';
include __DIR__ . '/../app/views/layouts/header.php';
?>

<?php if ($role !== 'manager'): ?>
    <section class="stats">
        <div class="card">
            <h3>Total Candidates</h3>
            <p><?= (int) $stats['total_candidates'] ?></p>
        </div>
        <div class="card">
            <h3>Today Interviews</h3>
            <p><?= (int) $stats['today_interviews'] ?></p>
        </div>
        <div class="card selected">
            <h3>Selected</h3>
            <p><?= (int) $stats['selected'] ?></p>
        </div>
        <div class="card rejected">
            <h3>Rejected</h3>
            <p><?= (int) $stats['rejected'] ?></p>
        </div>
        <div class="card pending-card" onclick="openPendingModal()">
            <h3>Pending Feedback</h3>
            <p><?= (int) $pendingCount ?></p>
        </div>
    </section>
<?php endif; ?>

<?php if ($role === 'admin'): ?>
    <div class="section-title">
        <h2>Admin Controls</h2>
        <div class="actions">
            <a class="btn" href="users.php">Manage Users</a>
            <a class="btn btn-outline" href="apply.php">Candidate Form</a>
        </div>
    </div>
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
                        <td><span class="badge pending"><?= h($row['current_status']) ?></span></td>
                        <td><span class="badge round"><?= h($row['interview_status']) ?></span></td>
                        <td>
                            <a class="btn btn-outline" href="manager-review.php?round_id=<?= (int) $row['round_id'] ?>">Open</a>
                        </td>
                    <?php else: ?>
                        <?php
                        $latestFeedback = $row['latest_feedback'] ?: '-';
                        if ($latestFeedback === 'selected' || $latestFeedback === 'direct_selected' || $latestFeedback === 'manager_selected') {
                            $latestFeedback = 'select';
                        } elseif ($latestFeedback === 'rejected' || $latestFeedback === 'rejected_without_interview' || $latestFeedback === 'manager_rejected') {
                            $latestFeedback = 'reject';
                        }
                        ?>
                        <td><?= h($row['application_no']) ?></td>
                        <td><?= h($row['full_name']) ?></td>
                        <td><?= h($row['position_applied']) ?></td>
                        <td><span class="badge pending"><?= h($row['current_status']) ?></span></td>
                        <td><?= h($row['manager_name'] ?: '-') ?></td>
                        <td><?= h($latestFeedback) ?></td>
                        <td><?= h($row['latest_remark'] ?: '-') ?></td>
                        <td>
                            <a class="btn btn-outline" href="recruiter-candidate.php?id=<?= (int) $row['id'] ?>">Open</a>
                        </td>
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
                                <td><?= h($row['current_status'] ?? '') ?></td>
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