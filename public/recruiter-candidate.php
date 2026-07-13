<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_role(['admin', 'recruiter', 'manager']);

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$title = 'Candidate Detail';
include __DIR__ . '/../app/views/layouts/header.php';

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

$academicStmt = $pdo->prepare("SELECT * FROM candidate_academics WHERE candidate_id = ? ORDER BY id ASC");
$academicStmt->execute([$id]);
$academics = $academicStmt->fetchAll(PDO::FETCH_ASSOC);

$experienceStmt = $pdo->prepare("SELECT * FROM candidate_experiences WHERE candidate_id = ? ORDER BY id ASC");
$experienceStmt->execute([$id]);
$experiences = $experienceStmt->fetchAll(PDO::FETCH_ASSOC);

$referenceStmt = $pdo->prepare("SELECT * FROM candidate_references WHERE candidate_id = ? ORDER BY id ASC");
$referenceStmt->execute([$id]);
$references = $referenceStmt->fetchAll(PDO::FETCH_ASSOC);

$familyStmt = $pdo->prepare("SELECT * FROM candidate_family_details WHERE candidate_id = ? ORDER BY id ASC");
$familyStmt->execute([$id]);
$familyDetails = $familyStmt->fetchAll(PDO::FETCH_ASSOC);

$statusStmt = $pdo->prepare("SELECT * FROM candidate_status_logs WHERE candidate_id = ? ORDER BY id DESC");
$statusStmt->execute([$id]);
$statusLogs = $statusStmt->fetchAll(PDO::FETCH_ASSOC);

$roundStmt = $pdo->prepare("SELECT * FROM interview_rounds WHERE candidate_id = ? ORDER BY id DESC");
$roundStmt->execute([$id]);
$rounds = $roundStmt->fetchAll(PDO::FETCH_ASSOC);

$feedbackStmt = $pdo->prepare("SELECT * FROM interview_feedback WHERE candidate_id = ? ORDER BY id DESC");
$feedbackStmt->execute([$id]);
$feedbacks = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

$managerStmt = $pdo->query("SELECT id, full_name FROM users ORDER BY full_name ASC");
$managers = $managerStmt->fetchAll(PDO::FETCH_ASSOC);
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

    .summary-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }

    .sum-card {
        background: #fff;
        border: 1px solid #eadfeb;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(122, 69, 119, .06);
    }

    .sum-card small {
        display: block;
        font-size: 12px;
        color: #8f819a;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .sum-card strong {
        display: block;
        font-size: 18px;
        color: #2e2439;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 10px;
        background: #f5dff0;
        color: #b23284;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .accordion-wrap {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .acc-item {
        background: #fff;
        border: 1px solid #eadfeb;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(122, 69, 119, .06);
    }

    .acc-item summary {
        list-style: none;
        cursor: pointer;
        padding: 18px 20px;
        font-size: 22px;
        font-weight: 700;
        color: #2f2640;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }

    .acc-item summary::-webkit-details-marker {
        display: none;
    }

    .acc-item summary::after {
        content: "+";
        font-size: 24px;
        font-weight: 700;
        color: #b43c8c;
    }

    .acc-item[open] summary::after {
        content: "−";
    }

    .acc-body {
        padding: 0 20px 20px;
        border-top: 1px solid #f1e7f0;
        background: #fff;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 18px;
    }

    .info-box {
        border: 1px solid #eadfeb;
        border-radius: 14px;
        padding: 12px 14px;
        background: #fcfafc;
    }

    .info-box.wide {
        grid-column: span 3;
    }

    .info-box label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #887a97;
        margin-bottom: 5px;
        text-transform: uppercase;
        letter-spacing: .35px;
    }

    .info-box div {
        font-size: 15px;
        color: #2d2437;
        line-height: 1.5;
        word-break: break-word;
        min-height: 22px;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-top: 18px;
    }

    .action-panel {
        border: 1px solid #ecdfee;
        border-radius: 16px;
        padding: 16px;
        background: #fcfafc;
    }

    .action-panel h4 {
        margin: 0 0 14px;
        font-size: 16px;
        color: #302640;
    }

    .field {
        margin-bottom: 12px;
    }

    .field label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
        color: #43384f;
    }

    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1px solid #e3dbe5;
        background: #fff;
        border-radius: 12px;
        padding: 11px 12px;
        font-size: 14px;
        color: #2e2437;
        outline: none;
    }

    .field textarea {
        min-height: 74px;
        resize: vertical;
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
    }

    .btn-pink {
        background: #cf3d84;
    }

    .btn-green {
        background: #2ea160;
    }

    .btn-orange {
        background: #d98314;
    }

    .btn-red {
        background: #d53f5d;
    }

    .table-wrap {
        margin-top: 18px;
        overflow: auto;
        border: 1px solid #eee3ee;
        border-radius: 14px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
        background: #fff;
    }

    th,
    td {
        border-bottom: 1px solid #f1e8f1;
        padding: 12px 10px;
        text-align: left;
        vertical-align: top;
        font-size: 14px;
    }

    th {
        background: #fbf7fb;
        color: #5f536f;
        font-size: 13px;
    }

    .empty {
        margin-top: 16px;
        padding: 14px;
        border: 1px dashed #e6d8e6;
        border-radius: 14px;
        background: #fcfafc;
        color: #83778f;
    }

    .stack-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 18px;
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

    @media (max-width: 1100px) {
        .summary-grid {
            grid-template-columns: 1fr 1fr;
        }

        .info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .info-box.wide {
            grid-column: span 2;
        }
    }

    @media (max-width: 760px) {

        .summary-grid,
        .action-grid,
        .info-grid {
            grid-template-columns: 1fr;
        }

        .info-box.wide {
            grid-column: span 1;
        }

        .acc-item summary {
            font-size: 18px;
            padding: 16px;
        }

        .acc-body {
            padding: 0 16px 16px;
        }
    }
</style>

<div class="container">
    <div class="summary-grid">
        <div class="sum-card">
            <small>Candidate</small>
            <strong><?= h($candidate['full_name'] ?? '') ?></strong>
            <div style="margin-top:8px;color:#72677f;font-size:14px;">
                <?= h($candidate['position_applied'] ?? '') ?> • <?= h($candidate['email'] ?? '') ?>
            </div>
        </div>
        <div class="sum-card">
            <small>Application No</small>
            <strong><?= h($candidate['application_no'] ?? '') ?></strong>
            <br>
            <div style="margin: 0 0 20px; display:flex; justify-content:flex-end;">
                <a class="btn btn-pink" href="candidate-pdf.php?id=<?= (int) $candidate['id'] ?>" target="_blank">
                    Preview Summary PDF
                </a>
        </div>
    </div>
    <div class="sum-card">
        <small>Status</small>
        <strong><span class="status-badge"><?= h($candidate['current_status'] ?? 'submitted') ?></span></strong>
    </div>
    <div class="sum-card">
        <small>Final Decision</small>
        <strong><?= h($candidate['final_decision'] ?? 'pending') ?></strong>


    </div>


</div>

<div class="accordion-wrap">
    <details class="acc-item" open name="candidate-detail">
        <summary>User Info</summary>
        <div class="acc-body">
            <div class="info-grid">
                <div class="info-box"><label>Full Name</label>
                    <div><?= h($candidate['full_name'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Email</label>
                    <div><?= h($candidate['email'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Phone</label>
                    <div><?= h($candidate['phone'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Alternate Phone</label>
                    <div><?= h($candidate['alternate_phone'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Father / Husband Name</label>
                    <div><?= h($candidate['father_husband_name'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Emergency No</label>
                    <div><?= h($candidate['emergency_no'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Date of Birth</label>
                    <div><?= h($candidate['dob'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Age</label>
                    <div><?= h($candidate['age'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Gender</label>
                    <div><?= h($candidate['gender'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Marital Status</label>
                    <div><?= h($candidate['marital_status'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Highest Qualification</label>
                    <div><?= h($candidate['highest_qualification'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Total Experience</label>
                    <div><?= h($candidate['total_experience'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Current Company</label>
                    <div><?= h($candidate['current_company'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Current Salary</label>
                    <div><?= h($candidate['current_salary'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Expected Salary</label>
                    <div><?= h($candidate['expected_salary'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Position Applied</label>
                    <div><?= h($candidate['position_applied'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Department</label>
                    <div><?= h($candidate['department'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Notice Period</label>
                    <div><?= h($candidate['notice_period'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Notice Specify</label>
                    <div><?= h($candidate['notice_period_specify'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Source Type</label>
                    <div><?= h($candidate['source_type'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Source Reference Name</label>
                    <div><?= h($candidate['source_reference_name'] ?? '') ?></div>
                </div>

                <div class="info-box wide"><label>Current Address</label>
                    <div><?= nl2br(h($candidate['address'] ?? '')) ?></div>
                </div>
                <div class="info-box wide"><label>Permanent Address</label>
                    <div><?= nl2br(h($candidate['permanent_address'] ?? '')) ?></div>
                </div>

                <div class="info-box"><label>City</label>
                    <div><?= h($candidate['city'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>State</label>
                    <div><?= h($candidate['state'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Pincode</label>
                    <div><?= h($candidate['pincode'] ?? '') ?></div>
                </div>

                <div class="info-box wide"><label>Career Goals</label>
                    <div><?= nl2br(h($candidate['career_goals'] ?? '')) ?></div>
                </div>
                <div class="info-box wide"><label>Interest in Field</label>
                    <div><?= nl2br(h($candidate['interest_in_field'] ?? '')) ?></div>
                </div>
                <div class="info-box wide"><label>Scheduled Exam</label>
                    <div><?= nl2br(h($candidate['scheduled_exam'] ?? '')) ?></div>
                </div>

                <div class="info-box wide"><label>Strengths</label>
                    <div><?= nl2br(h($candidate['strengths'] ?? '')) ?></div>
                </div>
                <div class="info-box wide"><label>Weakness</label>
                    <div><?= nl2br(h($candidate['weakness'] ?? '')) ?></div>
                </div>

                <div class="info-box"><label>EPF Registered</label>
                    <div><?= h($candidate['epf_registered'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>UAN No</label>
                    <div><?= h($candidate['uan_no'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>ESIC Registered</label>
                    <div><?= h($candidate['esic_registered'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>IP No</label>
                    <div><?= h($candidate['ip_no'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Aadhaar No</label>
                    <div><?= h($candidate['aadhaar_no'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>PAN No</label>
                    <div><?= h($candidate['pan_no'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Bank Account No</label>
                    <div><?= h($candidate['bank_account_no'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>IFSC Code</label>
                    <div><?= h($candidate['ifsc_code'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Weekly Working Days</label>
                    <div><?= h($candidate['weekly_working_days'] ?? '') ?></div>
                </div>

                <div class="info-box"><label>Smoking</label>
                    <div><?= h($candidate['smoking'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Self Vehicle</label>
                    <div><?= h($candidate['self_vehicle'] ?? '') ?></div>
                </div>
                <div class="info-box"><label>Driving Licence</label>
                    <div><?= h($candidate['driving_licence'] ?? '') ?></div>
                </div>

                <div class="info-box wide"><label>Medical Issue</label>
                    <div><?= nl2br(h($candidate['medical_issue'] ?? '')) ?></div>
                </div>
                <div class="info-box wide"><label>Hobbies</label>
                    <div><?= nl2br(h($candidate['hobbies'] ?? '')) ?></div>
                </div>

                <div class="info-box">
                    <label>Resume</label>
                    <div>
                        <?php if (!empty($candidate['resume_path'])): ?>
                            <a href="<?= h($candidate['resume_path']) ?>" target="_blank" rel="noopener noreferrer">Open
                                Resume</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-box">
                    <label>Photo</label>
                    <div>
                        <?php if (!empty($candidate['photo_path'])): ?>
                            <a href="<?= h($candidate['photo_path']) ?>" target="_blank" rel="noopener noreferrer">Open
                                Photo</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-box"><label>Applied At</label>
                    <div><?= h($candidate['applied_at'] ?? '') ?></div>
                </div>
            </div>
        </div>
    </details>



    <details class="acc-item" name="candidate-detail">
        <summary>Academic Details</summary>
        <div class="acc-body">
            <?php if ($academics): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Subject</th>
                                <th>Institute</th>
                                <th>Passing Year</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($academics as $row): ?>
                                <tr>
                                    <td><?= h($row['level_name'] ?? '') ?></td>
                                    <td><?= h($row['subject'] ?? '') ?></td>
                                    <td><?= h($row['institute'] ?? '') ?></td>
                                    <td><?= h($row['passing_year'] ?? '') ?></td>
                                    <td><?= h($row['percentage'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty">No academic details found.</div>
            <?php endif; ?>
        </div>
    </details>

    <details class="acc-item" name="candidate-detail">
        <summary>Experience</summary>
        <div class="acc-body">
            <?php if ($experiences): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Designation</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Salary (CTC)</th>
                                <th>Reason for Leaving</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($experiences as $row): ?>
                                <tr>
                                    <td><?= h($row['company_name'] ?? '') ?></td>
                                    <td><?= h($row['designation'] ?? '') ?></td>
                                    <td><?= h($row['from_date'] ?? '') ?></td>
                                    <td><?= h($row['to_date'] ?? '') ?></td>
                                    <td><?= h($row['salary_ctc'] ?? '') ?></td>
                                    <td><?= h($row['reason_for_leaving'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty">No experience records found.</div>
            <?php endif; ?>
        </div>
    </details>

    <details class="acc-item" name="candidate-detail">
        <summary>References</summary>
        <div class="acc-body">
            <?php if ($references): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Email</th>
                                <th>Mobile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($references as $row): ?>
                                <tr>
                                    <td><?= h($row['ref_name'] ?? '') ?></td>
                                    <td><?= h($row['designation'] ?? '') ?></td>
                                    <td><?= h($row['email'] ?? '') ?></td>
                                    <td><?= h($row['mobile'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty">No reference details found.</div>
            <?php endif; ?>
        </div>
    </details>

    <details class="acc-item" name="candidate-detail">
        <summary>Family Details</summary>
        <div class="acc-body">
            <?php if ($familyDetails): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Relation</th>
                                <th>Occupation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($familyDetails as $row): ?>
                                <tr>
                                    <td><?= h($row['member_name'] ?? '') ?></td>
                                    <td><?= h($row['relation_name'] ?? '') ?></td>
                                    <td><?= h($row['occupation'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty">No family details found.</div>
            <?php endif; ?>
        </div>
    </details>


    <details class="acc-item" open name="candidate-detail">
        <summary>Actions</summary>
        <div class="acc-body">
            <div class="action-grid">
                <div class="action-panel">
                    <h4>Reject / Direct Select</h4>

                    <form method="post" style="margin-bottom:16px;">
                        <input type="hidden" name="action" value="reject_without_interview">
                        <div class="field">
                            <label>Reject reason</label>
                            <textarea name="note" rows="3" required></textarea>
                        </div>
                        <button class="btn btn-pink" type="submit">Reject Without Interview</button>
                    </form>

                    <form method="post" style="margin-bottom:16px;">
                        <input type="hidden" name="action" value="direct_select">
                        <div class="field">
                            <label>Select note</label>
                            <textarea name="note" rows="2"></textarea>
                        </div>
                        <button class="btn btn-green" type="submit">Direct Select</button>
                    </form>
                </div>

                <div class="action-panel">
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
        </div>
    </details>

    <details class="acc-item" name="candidate-detail">
        <summary>Interview Rounds</summary>
        <div class="acc-body">
            <?php if ($rounds): ?>
                <div class="stack-list">
                    <?php foreach ($rounds as $row): ?>
                        <div class="stack-card">
                            <h5><?= h($row['round_name'] ?? 'Interview Round') ?></h5>
                            <p><strong>Date/Time:</strong> <?= h($row['scheduled_at'] ?? '') ?></p>
                            <p><strong>Status:</strong> <?= h($row['status'] ?? '') ?></p>
                            <p><strong>Note:</strong> <?= nl2br(h($row['note'] ?? '')) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">No interview rounds found.</div>
            <?php endif; ?>
        </div>
    </details>

    <details class="acc-item" name="candidate-detail">
        <summary>Manager Feedback</summary>
        <div class="acc-body">
            <?php if ($feedbacks): ?>
                <div class="stack-list">
                    <?php foreach ($feedbacks as $row): ?>
                        <div class="stack-card">
                            <h5><?= h($row['title'] ?? 'Feedback') ?></h5>
                            <p><strong>Recommendation:</strong> <?= h($row['recommendation'] ?? '') ?></p>
                            <p><strong>Rating:</strong> <?= h($row['rating'] ?? '') ?></p>
                            <p><strong>Remarks:</strong> <?= nl2br(h($row['remarks'] ?? '')) ?></p>
                            <p><strong>Submitted At:</strong> <?= h($row['created_at'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">No manager feedback found.</div>
            <?php endif; ?>
        </div>
    </details>

    <details class="acc-item" name="candidate-detail">
        <summary>Status History</summary>
        <div class="acc-body">
            <?php if ($statusLogs): ?>
                <div class="stack-list">
                    <?php foreach ($statusLogs as $row): ?>
                        <div class="stack-card">
                            <h5><?= h($row['new_status'] ?? 'status') ?></h5>
                            <p><strong>Date:</strong> <?= h($row['created_at'] ?? '') ?></p>
                            <p><strong>Action Role:</strong> <?= h($row['action_role'] ?? '') ?></p>
                            <p><strong>Note:</strong> <?= nl2br(h($row['note'] ?? '')) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">No status history found.</div>
            <?php endif; ?>
        </div>
    </details>

</div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>