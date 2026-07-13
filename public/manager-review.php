<?php
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';
require_role('manager');
$pdo = Database::connect();
$user = current_user();
$roundId = (int) ($_GET['round_id'] ?? 0);
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
$data = $stmt->fetch();

$expStmt = $pdo->prepare("
    SELECT company_name, designation, from_date, to_date, salary_ctc, reason_for_leaving
    FROM candidate_experiences
    WHERE candidate_id = ?
    ORDER BY id ASC
");
$expStmt->execute([$data['candidate_id']]);
$experiences = $expStmt->fetchAll(PDO::FETCH_ASSOC);


if (!$data)
    exit('Interview round not found');
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

<style>

    .grid.grid-2{
    display:grid;
    grid-template-columns:1.35fr 0.95fr;
    gap:20px;
    align-items:start;
}
</style>
<div class="grid grid-2">
    <div class="card">
        <div class="section-title">
            <h2>Candidate Info</h2>
            <span class="badge round"><?= h($data['round_name']) ?></span>
        </div>

        <div class="table-wrap">
            <table class="info-table">
                <tbody>
                    <tr>
                        <th>Full Name</th>
                        <td><?= h($data['full_name']) ?></td>
                        <th>Application No</th>
                        <td><?= h($data['application_no'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Position Applied</th>
                        <td><?= h($data['position_applied']) ?></td>
                        <th>Total Experience</th>
                        <td><?= h($data['total_experience']) ?> years</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= h($data['email']) ?></td>
                        <th>Phone</th>
                        <td><?= h($data['phone']) ?></td>
                    </tr>
                    <!-- <tr>
                        <th>Alternate Phone</th>
                        <td><?= h($data['alternate_phone'] ?? '') ?></td>
                        <th>Gender</th>
                        <td><?= h($data['gender'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Date of Birth</th>
                        <td><?= h($data['dob'] ?? '') ?></td>
                        <th>Current Salary</th>
                        <td><?= h($data['current_salary'] ?? '') ?></td>
                    </tr> -->
                    <tr>
                        <th>Expected Salary</th>
                        <td><?= h($data['expected_salary'] ?? '') ?></td>
                        <th>Current Company</th>
                        <td><?= h($data['current_company'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td colspan="3"><?= h($data['address'] ?? '') ?></td>
                    </tr>
                    <tr>
                        <th>Resume</th>
                        <td colspan="3">
                            <?php if (!empty($data['resume_path'])): ?>
                                &nbsp; &nbsp; &nbsp; &nbsp; <a href="<?= h($data['resume_path']) ?>" target="_blank" rel="noopener noreferrer"> <stong>Open
                                    Resume </stong></a>
                                &nbsp;  &nbsp;
                                <!-- <a href="download-resume.php?file=<?= urlencode($data['resume_path']) ?>">Download
                                    Resume</a> -->
                            <?php else: ?>
                                <span>No resume uploaded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (!empty($experiences)): ?>
            <div style="margin-top:20px;">
                <h3 style="margin:0 0 12px;font-size:22px;color:#2f2640;">Candidate Experience Details</h3>
                <div class="table-wrap">
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Designation</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Salary (CTC)</th>
                                <th>Reason for Leaving</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($experiences as $exp): ?>
                                <tr>
                                    <td><?= h($exp['company_name'] ?? '') ?></td>
                                    <td><?= h($exp['designation'] ?? '') ?></td>
                                    <td><?= h($exp['from_date'] ?? '') ?></td>
                                    <td><?= h($exp['to_date'] ?? '') ?></td>
                                    <td><?= h($exp['salary_ctc'] ?? '') ?></td>
                                    <td><?= h($exp['reason_for_leaving'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="section-title">
            <h2>Add Remark</h2><span class="pill">Returns to recruiter</span>
        </div>
        <form method="post">
            <div class="form-group"><label>Recommendation</label><select name="recommendation">
                    <option value="hold">Hold</option>
                    <option value="next_round">Next Round</option>
                    <option value="select">Select</option>
                    <option value="reject">Reject</option>
                </select></div>
            <div class="form-group"><label>Manager Remark</label><textarea name="remark_text" rows="8"
                    required></textarea></div>
            <button class="btn" type="submit">Send Feedback to Recruiter</button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>