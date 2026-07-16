<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_role(['admin', 'recruiter', 'manager']);

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('Invalid candidate ID');
}

$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    exit('Candidate not found');
}

$managerStmt = $pdo->query("
    SELECT u.id, u.full_name
    FROM users u
    INNER JOIN user_roles ur ON ur.user_id = u.id
    INNER JOIN roles r ON r.id = ur.role_id
    WHERE r.name = 'manager'
    ORDER BY u.full_name ASC
");
$managers = $managerStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $managerId = (int) ($_POST['manager_id'] ?? 0);
    $scheduledAt = trim($_POST['scheduled_at'] ?? '');
    $scheduledAt = $scheduledAt !== '' ? date('Y-m-d H:i:s', strtotime($scheduledAt)) : null;

    $oldStatus = $candidate['current_status'] ?? 'submitted';
    $oldDecision = $candidate['final_decision'] ?? 'pending';

    $newStatus = $oldStatus;
    $newDecision = $oldDecision;
    $roundId = null;

    $actionBy = $_SESSION['user']['id'] ?? ($_SESSION['user_id'] ?? null);
    $actionRole = $_SESSION['user']['role'] ?? ($_SESSION['role'] ?? 'recruiter');

    if ($action === 'reject_without_interview') {
        $newStatus = 'rejected';
        $newDecision = 'rejected';
    } elseif ($action === 'direct_select') {
        $newStatus = 'selected';
        $newDecision = 'selected';
    } elseif ($action === 'send_to_manager') {
        if ($managerId <= 0) {
            exit('Please select manager');
        }

        $nextRoundNoStmt = $pdo->prepare("SELECT COALESCE(MAX(round_no), 0) + 1 FROM interview_rounds WHERE candidate_id = ?");
        $nextRoundNoStmt->execute([$id]);
        $roundNo = (int) $nextRoundNoStmt->fetchColumn();

        $roundName = 'Round ' . $roundNo;

        $insertRound = $pdo->prepare("
            INSERT INTO interview_rounds
            (candidate_id, round_no, round_name, recruiter_id, manager_id, scheduled_at, interview_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insertRound->execute([$id, $roundNo, $roundName, $actionBy, $managerId, $scheduledAt, 'assigned']);
        $roundId = (int) $pdo->lastInsertId();

        $newStatus = 'sent_to_manager';
        $newDecision = 'pending';
    } elseif ($action === 'final_select') {
        $newStatus = 'selected';
        $newDecision = 'selected';
    } elseif ($action === 'final_reject') {
        $newStatus = 'rejected';
        $newDecision = 'rejected';
    }

    if ($newStatus !== $oldStatus || $newDecision !== $oldDecision) {
        $pdo->prepare("
            UPDATE candidates
            SET current_status = ?, final_decision = ?
            WHERE id = ?
        ")->execute([$newStatus, $newDecision, $id]);

        $pdo->prepare("
            INSERT INTO candidate_status_logs
            (candidate_id, round_id, action_by, action_role, old_status, new_status, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ")->execute([
            $id,
            $roundId,
            $actionBy,
            $actionRole,
            $oldStatus,
            $newStatus,
            $note !== '' ? $note : null
        ]);
    }

    header('Location: recruiter-candidate.php?id=' . $id);
    exit;
}

$title = 'Candidate Action';
include __DIR__ . '/../app/views/layouts/header.php';
?>

<style>
body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f7f3f7;color:#2c2337}
.container{max-width:900px;margin:24px auto;padding:0 16px 40px}
.card{background:#fff;border:1px solid #eadfeb;border-radius:18px;padding:20px;box-shadow:0 10px 24px rgba(122,69,119,.06)}
h2{margin:0 0 18px;font-size:28px;color:#2f2640}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:16px}
.panel{border:1px solid #ecdfee;border-radius:16px;padding:16px;background:#fcfafc}
.panel h4{margin:0 0 14px;font-size:16px;color:#302640}
.field{margin-bottom:12px}
.field label{display:block;margin-bottom:6px;font-size:13px;font-weight:700;color:#43384f}
.field input,.field select,.field textarea{width:100%;border:1px solid #e3dbe5;background:#fff;border-radius:12px;padding:11px 12px;font-size:14px;color:#2e2437;outline:none}
.field textarea{min-height:74px;resize:vertical}
.btn{display:inline-block;border:none;border-radius:12px;padding:10px 14px;font-size:13px;font-weight:700;cursor:pointer;color:#fff}
.btn-pink{background:#cf3d84}
.btn-green{background:#2ea160}
.btn-orange{background:#d98314}
.btn-red{background:#d53f5d}
.meta{margin:6px 0;color:#5d526a}
.links{margin-top:16px;display:flex;gap:12px;flex-wrap:wrap}
.links a{color:#cf3d84;text-decoration:none;font-weight:700}
@media (max-width:760px){.grid{grid-template-columns:1fr}}
</style>

<div class="container">
    <div class="card">
        <h2>Candidate Action</h2>
        <p class="meta"><strong>Name:</strong> <?= h($candidate['full_name'] ?? '') ?></p>
        <p class="meta"><strong>Application No:</strong> <?= h($candidate['application_no'] ?? '') ?></p>
        <p class="meta"><strong>Status:</strong> <?= h($candidate['current_status'] ?? '') ?></p>
        <p class="meta"><strong>Final Decision:</strong> <?= h($candidate['final_decision'] ?? '') ?></p>

        <div class="grid">
            <div class="panel">
                <h4>Reject / Direct Select</h4>

                <form method="post" style="margin-bottom:16px;">
                    <input type="hidden" name="action" value="reject_without_interview">
                    <div class="field">
                        <label>Reject reason</label>
                        <textarea name="note" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-pink" type="submit">Reject Without Interview</button>
                </form>

                <form method="post">
                    <input type="hidden" name="action" value="direct_select">
                    <div class="field">
                        <label>Select note</label>
                        <textarea name="note" rows="2"></textarea>
                    </div>
                    <button class="btn btn-green" type="submit">Direct Select</button>
                </form>
            </div>

            <div class="panel">
                <h4>Next Round / Final Decision</h4>

                <form method="post" style="margin-bottom:16px;">
                    <input type="hidden" name="action" value="send_to_manager">
                    <div class="field">
                        <label>Select Manager</label>
                        <select name="manager_id" required>
                            <option value="">Choose manager</option>
                            <?php foreach ($managers as $m): ?>
                                <option value="<?= (int) $m['id'] ?>"><?= h($m['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label>Interview Date/Time</label>
                        <input type="datetime-local" name="scheduled_at">
                    </div>
                    <div class="field">
                        <label>Round note</label>
                        <textarea name="note" rows="3"></textarea>
                    </div>
                    <button class="btn btn-orange" type="submit">Next Round / Send to Manager</button>
                </form>

                <form method="post" style="margin-bottom:16px;">
                    <input type="hidden" name="action" value="final_select">
                    <div class="field">
                        <label>Selection note</label>
                        <textarea name="note" rows="2"></textarea>
                    </div>
                    <button class="btn btn-green" type="submit">Final Select</button>
                </form>

                <form method="post">
                    <input type="hidden" name="action" value="final_reject">
                    <div class="field">
                        <label>Final reject reason</label>
                        <textarea name="note" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-red" type="submit">Final Reject</button>
                </form>
            </div>
        </div>

        <div class="links">
            <a href="recruiter-candidate.php?id=<?= (int) $candidate['id'] ?>">Back to Candidate Detail</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>