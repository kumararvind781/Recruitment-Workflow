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
$roundId = (int) ($_GET['round_id'] ?? 0);

if ($roundId <= 0) {
    exit('Invalid round ID');
}

$stmt = $pdo->prepare("
    SELECT
        ir.*,
        c.id AS candidate_id,
        c.full_name,
        c.application_no,
        c.email,
        c.phone,
        c.address,
        c.position_applied,
        c.total_experience,
        c.current_status,
        c.expected_salary,
        c.current_company,
        c.resume_path
    FROM interview_rounds ir
    JOIN candidates c ON c.id = ir.candidate_id
    WHERE ir.id = ? AND ir.manager_id = ?
    LIMIT 1
");
$stmt->execute([$roundId, $user['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    exit('Interview round not found');
}

// Latest exam attempt for this candidate (shown to the assigned manager).
$examStmt = $pdo->prepare("
    SELECT
        id,
        total_questions,
        attempted_questions,
        correct_answers,
        wrong_answers,
        unattempted_questions,
        total_marks,
        obtained_marks,
        percentage,
        status,
        started_at,
        submitted_at,
        duration_seconds,
        submission_reason,
        violation_count
    FROM exam_attempts
    WHERE candidate_id = ?
    ORDER BY id DESC
    LIMIT 1
");
$examStmt->execute([$data['candidate_id']]);
$examAttempt = $examStmt->fetch(PDO::FETCH_ASSOC) ?: null;

$prevFeedbacks = [];
if ((int) ($data['round_no'] ?? 1) > 1) {
    $prevFeedbackStmt = $pdo->prepare("
    SELECT
        ir.round_no,
        ir.round_name,
        f.recommendation,
        f.remark_text,
        f.created_at,
        u.full_name AS manager_name
    FROM interview_feedback f
    JOIN interview_rounds ir ON ir.id = f.round_id
    LEFT JOIN users u ON u.id = f.manager_id
    WHERE f.candidate_id = ?
      AND ir.round_no < ?
    ORDER BY ir.round_no DESC
");
    $prevFeedbackStmt->execute([$data['candidate_id'], (int) $data['round_no']]);
    $prevFeedbacks = $prevFeedbackStmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remark = trim($_POST['remark_text'] ?? '');
    $recommendation = $_POST['recommendation'] ?? 'hold';

    if ($remark !== '') {
        $pdo->prepare("
            INSERT INTO interview_feedback
                (round_id, candidate_id, manager_id, remark_text, recommendation)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$roundId, $data['candidate_id'], $user['id'], $remark, $recommendation]);

        $pdo->prepare("
            UPDATE interview_rounds
            SET interview_status = 'returned_to_recruiter',
                feedback_submitted_at = NOW(),
                closed_at = NOW()
            WHERE id = ?
        ")->execute([$roundId]);

        $pdo->prepare("
            UPDATE candidates
            SET current_status = 'manager_feedback_received'
            WHERE id = ?
        ")->execute([$data['candidate_id']]);

        $pdo->prepare("
            INSERT INTO candidate_status_logs
                (candidate_id, round_id, action_by, action_role, old_status, new_status, note)
            VALUES (?, ?, ?, 'manager', 'sent_to_manager', 'manager_feedback_received', ?)
        ")->execute([
                    $data['candidate_id'],
                    $roundId,
                    $user['id'],
                    'Manager feedback submitted: ' . $recommendation
                ]);

        header('Location: manager-dashboard.php');
        exit;
    }
}

$title = 'Manager Review';
include __DIR__ . '/../app/views/layouts/header.php';
?>

<style>
    * {
        box-sizing: border-box;
    }

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

    .layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 18px;
        align-items: start;
    }

    .card {
        background: #fff;
        border: 1px solid #eadfeb;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(122, 69, 119, .06);
    }

    .title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
    }

    .title-row h2 {
        margin: 0;
        font-size: 32px;
        color: #2f2640;
    }

    .pill {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 999px;
        background: #f5dff0;
        color: #b23284;
        font-weight: 700;
        font-size: 13px;
    }

    table.info {
        width: 100%;
        border-collapse: collapse;
        overflow: hidden;
        border-radius: 14px;
    }

    table.info td {
        border: 1px solid #eee3ee;
        padding: 12px 14px;
        vertical-align: top;
        font-size: 14px;
    }

    table.info td.label {
        background: #fbf7fb;
        font-weight: 700;
        color: #5f536f;
        text-transform: uppercase;
        width: 22%;
    }

    .btn {
        display: inline-block;
        border: none;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        color: #fff;
        background: #cf3d84;
        text-decoration: none;
    }

    .btn-outline {
        background: #fff;
        color: #b23284;
        border: 1px solid #eadfeb;
    }

    .btn-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .section-title {
        margin: 18px 0 10px;
        font-size: 24px;
        color: #2f2640;
    }

    .stack-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .stack-card {
        border: 1px solid #eadfeb;
        border-radius: 14px;
        padding: 14px;
        background: #fcfafc;
    }

    .stack-card h5 {
        margin: 0 0 6px;
        font-size: 15px;
        color: #2f2640;
    }

    .stack-card p {
        margin: 4px 0;
        font-size: 14px;
        color: #5d526a;
        line-height: 1.5;
    }

    .empty {
        padding: 14px;
        border: 1px dashed #e6d8e6;
        border-radius: 14px;
        background: #fcfafc;
        color: #83778f;
    }

    textarea,
    select {
        width: 100%;
        border: 1px solid #e3dbe5;
        background: #fff;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 14px;
        color: #2e2437;
        outline: none;
    }

    textarea {
        min-height: 160px;
        resize: vertical;
    }

    .form-label {
        display: block;
        font-weight: 700;
        margin: 12px 0 8px;
        color: #2f2640;
    }

    @media (max-width: 900px) {
        .layout {
            grid-template-columns: 1fr;
        }

        .title-row h2 {
            font-size: 26px;
        }
    }
</style>

<div class="container">
    <div class="layout">
        <div class="card">
            <div class="title-row">
                <h2>Candidate Info</h2>
                <span class="pill">Round <?= (int) ($data['round_no'] ?? 1) ?></span>
            </div>

            <table class="info">
                <tr>
                    <td class="label">Full Name</td>
                    <td><?= h($data['full_name']) ?></td>
                    <td class="label">Application No</td>
                    <td><?= h($data['application_no'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label">Position Applied</td>
                    <td><?= h($data['position_applied']) ?></td>
                    <td class="label">Total Experience</td>
                    <td><?= h($data['total_experience']) ?> years</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td><?= h($data['email']) ?></td>
                    <td class="label">Phone</td>
                    <td><?= h($data['phone']) ?></td>
                </tr>
                <tr>
                    <td class="label">Expected Salary</td>
                    <td><?= h($data['expected_salary'] ?? '') ?></td>
                    <td class="label">Current Company</td>
                    <td><?= h($data['current_company'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label">Address</td>
                    <td colspan="3"><?= h($data['address'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label">Resume</td>
                    <td colspan="3">
                        <?php if (!empty($data['resume_path'])): ?>
                            <a class="btn" href="../<?= h($data['resume_path']) ?>" target="_blank"
                                rel="noopener noreferrer">
                                Open Resume
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>

                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                        <a class="btn btn-pink" href="candidate-pdf.php?id=<?= (int) $data['candidate_id'] ?>"
                            target="_blank" rel="noopener noreferrer">
                            Candidate Form
                        </a>

                        
                    </td>
                </tr>
            </table>

            <?php if (strcasecmp(trim($data['position_applied'] ?? ''), 'Process Associate') === 0): ?>
            <div class="section-title">Candidate Exam Performance</div>
            <?php if (!$examAttempt): ?>
                <div class="empty">No exam attempt found for this candidate.</div>
            <?php else: ?>
                <?php
                $examDuration = isset($examAttempt['duration_seconds']) && $examAttempt['duration_seconds'] !== null
                    ? gmdate('H:i:s', (int) $examAttempt['duration_seconds'])
                    : 'Not recorded';

                $examReason = $examAttempt['submission_reason'] ?? '';
                if ($examReason === 'tab_switch_limit') {
                    $examReasonLabel = 'Automatic Submission (2nd Tab Switch)';
                } elseif ($examReason === 'manual') {
                    $examReasonLabel = 'Manual Submission';
                } else {
                    $examReasonLabel = 'Not recorded';
                }
                ?>
                <div class="stack-card">
                    <p><strong>Exam Status:</strong> <?= h(ucfirst($examAttempt['status'] ?? '')) ?></p>
                    <p><strong>Obtained Marks:</strong>
                        <?= h($examAttempt['obtained_marks'] ?? '0') ?> /
                        <?= h($examAttempt['total_marks'] ?? '0') ?>
                    </p>
                    <p><strong>Percentage:</strong> <?= h($examAttempt['percentage'] ?? '0') ?>%</p>
                    <p><strong>Total Questions:</strong> <?= (int) ($examAttempt['total_questions'] ?? 0) ?></p>
                    <p><strong>Attempted:</strong> <?= (int) ($examAttempt['attempted_questions'] ?? 0) ?></p>
                    <p><strong>Correct Answers:</strong> <?= (int) ($examAttempt['correct_answers'] ?? 0) ?></p>
                    <p><strong>Wrong Answers:</strong> <?= (int) ($examAttempt['wrong_answers'] ?? 0) ?></p>
                    <p><strong>Unattempted:</strong> <?= (int) ($examAttempt['unattempted_questions'] ?? 0) ?></p>
                    <p><strong>Time Taken:</strong> <?= h($examDuration) ?></p>
                    <p><strong>Tab Switch Violations:</strong> <?= (int) ($examAttempt['violation_count'] ?? 0) ?> / 2</p>
                    <p><strong>Submission Reason:</strong> <?= h($examReasonLabel) ?></p>
                    <p><strong>Started At:</strong> <?= h($examAttempt['started_at'] ?? '-') ?></p>
                    <p><strong>Submitted At:</strong> <?= h($examAttempt['submitted_at'] ?? '-') ?></p>

                    <?php if (($examAttempt['status'] ?? '') === 'completed'): ?>
                        <div class="btn-row">
                            <a class="btn" href="unire_exam_paper.php?attempt_id=<?= (int) $examAttempt['id'] ?>"
                               target="_blank" rel="noopener noreferrer">
                                Open Full Exam Paper
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php endif; // Process Associate exam section ?>

            <?php if (!empty($prevFeedbacks)): ?>
                <div class="section-title">Previous Round Feedback</div>
                <div class="stack-list">
                   
                    <?php foreach ($prevFeedbacks as $fb): ?>
                        <div class="stack-card">
                            <h5><?= h($fb['round_name'] ?? ('Round ' . (int) ($fb['round_no'] ?? 0))) ?></h5>
                            <p><strong>Manager:</strong> <?= h($fb['manager_name'] ?? '') ?></p>
                            <p><strong>Recommendation:</strong> <?= h($fb['recommendation'] ?? '') ?></p>
                            <p><strong>Remark:</strong> <?= nl2br(h($fb['remark_text'] ?? '')) ?></p>
                            <p><strong>Date:</strong> <?= h($fb['created_at'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="title-row">
                <h2>Add Remark</h2>
                <span class="pill">Returns to recruiter</span>
            </div>

            <form method="post">
    <?= csrf_field(); ?>
                <label class="form-label">Recommendation</label>
                <select name="recommendation">
                    <option value="hold">Hold</option>
                    <option value="next_round">Next Round</option>
                    <option value="select">Select</option>
                    <option value="reject">Reject</option>
                </select>

                <label class="form-label">Manager Remark</label>
                <textarea name="remark_text" required></textarea>

                <div class="btn-row">
                    <button class="btn" type="submit">Send Feedback to Recruiter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>