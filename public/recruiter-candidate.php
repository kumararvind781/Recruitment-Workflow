<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_role(['admin', 'recruiter', 'manager']);

if (!function_exists('h')) {
    function h($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    exit('Invalid candidate ID');
}

$isEditMode = isset($_GET['edit']) && $_GET['edit'] == '1';

$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    exit('Candidate not found');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'candidate_update') {
    $pdo->beginTransaction();

    try {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $alternatePhone = trim($_POST['alternate_phone'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $dob = trim($_POST['dob'] ?? '') ?: null;
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $highestQualification = trim($_POST['highest_qualification'] ?? '');
        $totalExperience = trim($_POST['total_experience'] ?? '') !== '' ? $_POST['total_experience'] : 0;
        $currentCompany = trim($_POST['current_company'] ?? '');
        $currentSalary = trim($_POST['current_salary'] ?? '') !== '' ? $_POST['current_salary'] : null;
        $expectedSalary = trim($_POST['expected_salary'] ?? '') !== '' ? $_POST['expected_salary'] : null;
        $positionApplied = trim($_POST['position_applied'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $sourceType = trim($_POST['source_type'] ?? '');
        $sourceReferenceName = trim($_POST['source_reference_name'] ?? '');
        $fatherHusbandName = trim($_POST['father_husband_name'] ?? '');
        $emergencyNo = trim($_POST['emergency_no'] ?? '');
        $age = trim($_POST['age'] ?? '') !== '' ? (int) $_POST['age'] : null;
        $maritalStatus = trim($_POST['marital_status'] ?? '');
        $permanentAddress = trim($_POST['permanent_address'] ?? '');
        $scheduledExam = trim($_POST['scheduled_exam'] ?? '');
        $careerGoals = trim($_POST['career_goals'] ?? '');
        $interestInField = trim($_POST['interest_in_field'] ?? '');
        $noticePeriod = trim($_POST['notice_period'] ?? '');
        $noticePeriodSpecify = trim($_POST['notice_period_specify'] ?? '');
        $strengths = trim($_POST['strengths'] ?? '');
        $weakness = trim($_POST['weakness'] ?? '');
        $epfRegistered = trim($_POST['epf_registered'] ?? '');
        $uanNo = trim($_POST['uan_no'] ?? '');
        $esicRegistered = trim($_POST['esic_registered'] ?? '');
        $ipNo = trim($_POST['ip_no'] ?? '');
        $aadhaarNo = trim($_POST['aadhaar_no'] ?? '');
        $panNo = trim($_POST['pan_no'] ?? '');
        $bankAccountNo = trim($_POST['bank_account_no'] ?? '');
        $ifscCode = trim($_POST['ifsc_code'] ?? '');
        $hobbies = trim($_POST['hobbies'] ?? '');
        $weeklyWorkingDays = trim($_POST['weekly_working_days'] ?? '');
        $medicalIssue = trim($_POST['medical_issue'] ?? '');
        $smoking = trim($_POST['smoking'] ?? '');
        $selfVehicle = trim($_POST['self_vehicle'] ?? '');
        $drivingLicence = trim($_POST['driving_licence'] ?? '');

        $updateCandidate = $pdo->prepare("
            UPDATE candidates
            SET
                full_name = ?,
                email = ?,
                phone = ?,
                alternate_phone = ?,
                gender = ?,
                dob = ?,
                address = ?,
                city = ?,
                state = ?,
                pincode = ?,
                highest_qualification = ?,
                total_experience = ?,
                current_company = ?,
                current_salary = ?,
                expected_salary = ?,
                position_applied = ?,
                department = ?,
                source_type = ?,
                source_reference_name = ?,
                father_husband_name = ?,
                emergency_no = ?,
                age = ?,
                marital_status = ?,
                permanent_address = ?,
                scheduled_exam = ?,
                career_goals = ?,
                interest_in_field = ?,
                notice_period = ?,
                notice_period_specify = ?,
                strengths = ?,
                weakness = ?,
                epf_registered = ?,
                uan_no = ?,
                esic_registered = ?,
                ip_no = ?,
                aadhaar_no = ?,
                pan_no = ?,
                bank_account_no = ?,
                ifsc_code = ?,
                hobbies = ?,
                weekly_working_days = ?,
                medical_issue = ?,
                smoking = ?,
                self_vehicle = ?,
                driving_licence = ?
            WHERE id = ?
        ");

        $updateCandidate->execute([
            $fullName,
            $email,
            $phone,
            $alternatePhone,
            $gender,
            $dob,
            $address,
            $city,
            $state,
            $pincode,
            $highestQualification,
            $totalExperience,
            $currentCompany,
            $currentSalary,
            $expectedSalary,
            $positionApplied,
            $department,
            $sourceType,
            $sourceReferenceName,
            $fatherHusbandName,
            $emergencyNo,
            $age,
            $maritalStatus,
            $permanentAddress,
            $scheduledExam,
            $careerGoals,
            $interestInField,
            $noticePeriod,
            $noticePeriodSpecify,
            $strengths,
            $weakness,
            $epfRegistered,
            $uanNo,
            $esicRegistered,
            $ipNo,
            $aadhaarNo,
            $panNo,
            $bankAccountNo,
            $ifscCode,
            $hobbies,
            $weeklyWorkingDays,
            $medicalIssue,
            $smoking,
            $selfVehicle,
            $drivingLicence,
            $id
        ]);

        if (!empty($_POST['academics']['id'])) {
            foreach ($_POST['academics']['id'] as $i => $rowId) {
                $rowId = (int) $rowId;
                if ($rowId <= 0) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    UPDATE candidate_academics
                    SET level_name = ?, subject = ?, institute = ?, passing_year = ?, percentage = ?
                    WHERE id = ? AND candidate_id = ?
                ");
                $stmt->execute([
                    trim($_POST['academics']['level_name'][$i] ?? ''),
                    trim($_POST['academics']['subject'][$i] ?? ''),
                    trim($_POST['academics']['institute'][$i] ?? ''),
                    trim($_POST['academics']['passing_year'][$i] ?? ''),
                    trim($_POST['academics']['percentage'][$i] ?? ''),
                    $rowId,
                    $id
                ]);
            }
        }

        if (!empty($_POST['experiences']['id'])) {
            foreach ($_POST['experiences']['id'] as $i => $rowId) {
                $rowId = (int) $rowId;
                if ($rowId <= 0) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    UPDATE candidate_experiences
                    SET company_name = ?, designation = ?, from_date = ?, to_date = ?, salary_ctc = ?, reason_for_leaving = ?
                    WHERE id = ? AND candidate_id = ?
                ");
                $stmt->execute([
                    trim($_POST['experiences']['company_name'][$i] ?? ''),
                    trim($_POST['experiences']['designation'][$i] ?? ''),
                    trim($_POST['experiences']['from_date'][$i] ?? ''),
                    trim($_POST['experiences']['to_date'][$i] ?? ''),
                    trim($_POST['experiences']['salary_ctc'][$i] ?? ''),
                    trim($_POST['experiences']['reason_for_leaving'][$i] ?? ''),
                    $rowId,
                    $id
                ]);
            }
        }

        if (!empty($_POST['references']['id'])) {
            foreach ($_POST['references']['id'] as $i => $rowId) {
                $rowId = (int) $rowId;
                if ($rowId <= 0) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    UPDATE candidate_references
                    SET ref_name = ?, designation = ?, email = ?, mobile = ?
                    WHERE id = ? AND candidate_id = ?
                ");
                $stmt->execute([
                    trim($_POST['references']['ref_name'][$i] ?? ''),
                    trim($_POST['references']['designation'][$i] ?? ''),
                    trim($_POST['references']['email'][$i] ?? ''),
                    trim($_POST['references']['mobile'][$i] ?? ''),
                    $rowId,
                    $id
                ]);
            }
        }

        if (!empty($_POST['family']['id'])) {
            foreach ($_POST['family']['id'] as $i => $rowId) {
                $rowId = (int) $rowId;
                if ($rowId <= 0) {
                    continue;
                }

                $stmt = $pdo->prepare("
                    UPDATE candidate_family_details
                    SET member_name = ?, relation_name = ?, occupation = ?
                    WHERE id = ? AND candidate_id = ?
                ");
                $stmt->execute([
                    trim($_POST['family']['member_name'][$i] ?? ''),
                    trim($_POST['family']['relation_name'][$i] ?? ''),
                    trim($_POST['family']['occupation'][$i] ?? ''),
                    $rowId,
                    $id
                ]);
            }
        }

        $pdo->commit();
        header('Location: recruiter-candidate.php?id=' . $id);
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        exit('Error saving candidate: ' . $e->getMessage());
    }
}

$stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

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

$feedbackStmt = $pdo->prepare("
    SELECT
        f.id,
        f.round_id,
        f.candidate_id,
        f.manager_id,
        f.remark_text,
        f.recommendation,
        f.created_at,
        ir.round_no,
        ir.round_name,
        u.full_name AS manager_name
    FROM interview_feedback f
    JOIN interview_rounds ir ON ir.id = f.round_id
    LEFT JOIN users u ON u.id = f.manager_id
    WHERE f.candidate_id = ?
    ORDER BY ir.round_no DESC, f.id DESC
");
$feedbackStmt->execute([$id]);
$feedbacks = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Candidate Detail';

$latestManagerStmt = $pdo->prepare("
    SELECT u.full_name
    FROM interview_rounds ir
    LEFT JOIN users u ON u.id = ir.manager_id
    WHERE ir.candidate_id = ?
    ORDER BY ir.id DESC
    LIMIT 1
");
$latestManagerStmt->execute([$id]);
$latestManagerName = $latestManagerStmt->fetchColumn();

include __DIR__ . '/../app/views/layouts/header.php';

function field_value($isEditMode, $name, $value, $type = 'text', $options = [])
{
    if (!$isEditMode) {
        return $type === 'textarea'
            ? nl2br(h((string) $value))
            : h((string) $value);
    }

    if ($type === 'textarea') {
        return '<textarea name="' . h($name) . '">' . h((string) $value) . '</textarea>';
    }

    if ($type === 'select') {
        $html = '<select name="' . h($name) . '">';
        $html .= '<option value="">Select</option>';
        foreach ($options as $option) {
            $selected = ((string) $value === (string) $option) ? ' selected' : '';
            $html .= '<option value="' . h($option) . '"' . $selected . '>' . h($option) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    return '<input type="' . h($type) . '" name="' . h($name) . '" value="' . h((string) $value) . '">';
}
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
    .accordion-wrap { display: flex; flex-direction: column; gap: 16px; }
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
    .acc-item summary::-webkit-details-marker { display: none; }
    .acc-item summary::after {
        content: "+";
        font-size: 24px;
        font-weight: 700;
        color: #b43c8c;
    }
    .acc-item[open] summary::after { content: "−"; }
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
    .info-box.wide { grid-column: span 3; }
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
    .info-box input,
    .info-box select,
    .info-box textarea,
    .table-wrap input,
    .table-wrap select,
    .table-wrap textarea {
        width: 100%;
        border: 1px solid #e3dbe5;
        background: #fff;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 14px;
        color: #2e2437;
        outline: none;
    }
    .info-box textarea,
    .table-wrap textarea {
        min-height: 90px;
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
        background-color: #CF3D84;
        text-decoration: none;
    }
    .btn-pink { background: #cf3d84; }
    .btn-green { background: #2ea160; }
    .btn-orange { background: #d98314; }
    .btn-red { background: #d53f5d; }
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
    th, td {
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
    .feedback-card {
        border: 1px solid #eadfeb;
        border-radius: 14px;
        padding: 14px;
        background: #fcfafc;
    }
    @media (max-width: 1100px) {
        .summary-grid { grid-template-columns: 1fr 1fr; }
        .info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .info-box.wide { grid-column: span 2; }
    }
    @media (max-width: 760px) {
        .summary-grid, .info-grid { grid-template-columns: 1fr; }
        .info-box.wide { grid-column: span 1; }
        .acc-item summary {
            font-size: 18px;
            padding: 16px;
        }
        .acc-body { padding: 0 16px 16px; }
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
            <div style="margin-top:16px;display:flex;justify-content:flex-end;">
                <a class="btn btn-pink" href="candidate-pdf.php?id=<?= (int) $candidate['id'] ?>" target="_blank"
                    rel="noopener noreferrer">
                    Preview Summary PDF
                </a>
            </div>
        </div>

        <div class="sum-card">
            <small>Status</small>
            <?php $status = $candidate['current_status'] ?? 'submitted'; ?>
            <span class="status-badge"><?= h($status) ?></span>
        </div>

        <div class="sum-card">
            <small>Final Decision</small>
            <strong><?= h($candidate['final_decision'] ?? 'pending') ?></strong>

            <div style="margin-top:16px;display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
                <?php if ($isEditMode): ?>
                    <button class="btn btn-green" type="submit" form="candidateEditForm">Save Changes</button>
                    <a class="btn btn-red" href="recruiter-candidate.php?id=<?= (int) $candidate['id'] ?>">Cancel</a>
                <?php else: ?>
                    <a class="btn btn-pink" href="recruiter-candidate.php?id=<?= (int) $candidate['id'] ?>&edit=1">Edit Candidate</a>
                <?php endif; ?>

                <?php if (($candidate['final_decision'] ?? 'pending') === 'pending' && !$isEditMode): ?>
                    <a class="btn btn-orange" href="recruiter-candidate-action.php?id=<?= (int) $candidate['id'] ?>">Take Action</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($isEditMode): ?>
        <form method="post" id="candidateEditForm">
            <input type="hidden" name="form_type" value="candidate_update">
        <?php endif; ?>

        <div class="accordion-wrap">
            <details class="acc-item" open>
                <summary>User Info</summary>
                <div class="acc-body">
                    <div class="info-grid">
                        <div class="info-box"><label>Full Name</label>
                            <div><?= field_value($isEditMode, 'full_name', $candidate['full_name'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Email</label>
                            <div><?= field_value($isEditMode, 'email', $candidate['email'] ?? '', 'email') ?></div>
                        </div>
                        <div class="info-box"><label>Phone</label>
                            <div><?= field_value($isEditMode, 'phone', $candidate['phone'] ?? '') ?></div>
                        </div>

                        <div class="info-box"><label>Alternate Phone</label>
                            <div><?= field_value($isEditMode, 'alternate_phone', $candidate['alternate_phone'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Father / Husband Name</label>
                            <div><?= field_value($isEditMode, 'father_husband_name', $candidate['father_husband_name'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Emergency No</label>
                            <div><?= field_value($isEditMode, 'emergency_no', $candidate['emergency_no'] ?? '') ?></div>
                        </div>

                        <div class="info-box"><label>Date of Birth</label>
                            <div><?= field_value($isEditMode, 'dob', $candidate['dob'] ?? '', 'date') ?></div>
                        </div>
                        <div class="info-box"><label>Age</label>
                            <div><?= field_value($isEditMode, 'age', $candidate['age'] ?? '', 'number') ?></div>
                        </div>
                        <div class="info-box"><label>Gender</label>
                            <div><?= field_value($isEditMode, 'gender', $candidate['gender'] ?? '', 'select', ['Male', 'Female', 'Other']) ?></div>
                        </div>

                        <div class="info-box"><label>Marital Status</label>
                            <div><?= field_value($isEditMode, 'marital_status', $candidate['marital_status'] ?? '', 'select', ['Single', 'Married', 'Other']) ?></div>
                        </div>
                        <div class="info-box"><label>Highest Qualification</label>
                            <div><?= field_value($isEditMode, 'highest_qualification', $candidate['highest_qualification'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Total Experience</label>
                            <div><?= field_value($isEditMode, 'total_experience', $candidate['total_experience'] ?? '', 'number') ?></div>
                        </div>

                        <div class="info-box"><label>Current Company</label>
                            <div><?= field_value($isEditMode, 'current_company', $candidate['current_company'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Current Salary</label>
                            <div><?= field_value($isEditMode, 'current_salary', $candidate['current_salary'] ?? '', 'number') ?></div>
                        </div>
                        <div class="info-box"><label>Expected Salary</label>
                            <div><?= field_value($isEditMode, 'expected_salary', $candidate['expected_salary'] ?? '', 'number') ?></div>
                        </div>

                        <div class="info-box"><label>Position Applied</label>
                            <div><?= field_value($isEditMode, 'position_applied', $candidate['position_applied'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Department</label>
                            <div><?= field_value($isEditMode, 'department', $candidate['department'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Notice Period</label>
                            <div><?= field_value($isEditMode, 'notice_period', $candidate['notice_period'] ?? '') ?></div>
                        </div>

                        <div class="info-box"><label>Notice Specify</label>
                            <div><?= field_value($isEditMode, 'notice_period_specify', $candidate['notice_period_specify'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Source Type</label>
                            <div><?= field_value($isEditMode, 'source_type', $candidate['source_type'] ?? '', 'select', ['walkin', 'qr', 'link', 'reference']) ?></div>
                        </div>
                        <div class="info-box"><label>Source Reference Name</label>
                            <div><?= field_value($isEditMode, 'source_reference_name', $candidate['source_reference_name'] ?? '') ?></div>
                        </div>

                        <div class="info-box wide"><label>Current Address</label>
                            <div><?= field_value($isEditMode, 'address', $candidate['address'] ?? '', 'textarea') ?></div>
                        </div>
                        <div class="info-box wide"><label>Permanent Address</label>
                            <div><?= field_value($isEditMode, 'permanent_address', $candidate['permanent_address'] ?? '', 'textarea') ?></div>
                        </div>

                        <div class="info-box"><label>City</label>
                            <div><?= field_value($isEditMode, 'city', $candidate['city'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>State</label>
                            <div><?= field_value($isEditMode, 'state', $candidate['state'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Pincode</label>
                            <div><?= field_value($isEditMode, 'pincode', $candidate['pincode'] ?? '') ?></div>
                        </div>

                        <div class="info-box wide"><label>Career Goals</label>
                            <div><?= field_value($isEditMode, 'career_goals', $candidate['career_goals'] ?? '', 'textarea') ?></div>
                        </div>
                        <div class="info-box wide"><label>Interest in Field</label>
                            <div><?= field_value($isEditMode, 'interest_in_field', $candidate['interest_in_field'] ?? '', 'textarea') ?></div>
                        </div>
                        <div class="info-box wide"><label>Scheduled Exam</label>
                            <div><?= field_value($isEditMode, 'scheduled_exam', $candidate['scheduled_exam'] ?? '', 'textarea') ?></div>
                        </div>

                        <div class="info-box wide"><label>Strengths</label>
                            <div><?= field_value($isEditMode, 'strengths', $candidate['strengths'] ?? '', 'textarea') ?></div>
                        </div>
                        <div class="info-box wide"><label>Weakness</label>
                            <div><?= field_value($isEditMode, 'weakness', $candidate['weakness'] ?? '', 'textarea') ?></div>
                        </div>

                        <div class="info-box"><label>EPF Registered</label>
                            <div><?= field_value($isEditMode, 'epf_registered', $candidate['epf_registered'] ?? '', 'select', ['Yes', 'No']) ?></div>
                        </div>
                        <div class="info-box"><label>UAN No</label>
                            <div><?= field_value($isEditMode, 'uan_no', $candidate['uan_no'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>ESIC Registered</label>
                            <div><?= field_value($isEditMode, 'esic_registered', $candidate['esic_registered'] ?? '', 'select', ['Yes', 'No']) ?></div>
                        </div>

                        <div class="info-box"><label>IP No</label>
                            <div><?= field_value($isEditMode, 'ip_no', $candidate['ip_no'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Aadhaar No</label>
                            <div><?= field_value($isEditMode, 'aadhaar_no', $candidate['aadhaar_no'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>PAN No</label>
                            <div><?= field_value($isEditMode, 'pan_no', $candidate['pan_no'] ?? '') ?></div>
                        </div>

                        <div class="info-box"><label>Bank Account No</label>
                            <div><?= field_value($isEditMode, 'bank_account_no', $candidate['bank_account_no'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>IFSC Code</label>
                            <div><?= field_value($isEditMode, 'ifsc_code', $candidate['ifsc_code'] ?? '') ?></div>
                        </div>
                        <div class="info-box"><label>Weekly Working Days</label>
                            <div><?= field_value($isEditMode, 'weekly_working_days', $candidate['weekly_working_days'] ?? '') ?></div>
                        </div>

                        <div class="info-box"><label>Smoking</label>
                            <div><?= field_value($isEditMode, 'smoking', $candidate['smoking'] ?? '', 'select', ['Yes', 'No']) ?></div>
                        </div>
                        <div class="info-box"><label>Self Vehicle</label>
                            <div><?= field_value($isEditMode, 'self_vehicle', $candidate['self_vehicle'] ?? '', 'select', ['Yes', 'No']) ?></div>
                        </div>
                        <div class="info-box"><label>Driving Licence</label>
                            <div><?= field_value($isEditMode, 'driving_licence', $candidate['driving_licence'] ?? '', 'select', ['Yes', 'No']) ?></div>
                        </div>

                        <div class="info-box wide"><label>Medical Issue</label>
                            <div><?= field_value($isEditMode, 'medical_issue', $candidate['medical_issue'] ?? '', 'textarea') ?></div>
                        </div>
                        <div class="info-box wide"><label>Hobbies</label>
                            <div><?= field_value($isEditMode, 'hobbies', $candidate['hobbies'] ?? '', 'textarea') ?></div>
                        </div>

                        <div class="info-box">
                            <label>Resume</label>
                            <div>
                                <?php if (!empty($candidate['resume_path'])): ?>
                                    <a href="<?= h($candidate['resume_path']) ?>" target="_blank" rel="noopener noreferrer">Open Resume</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-box">
                            <label>Photo</label>
                            <div>
                                <?php if (!empty($candidate['photo_path'])): ?>
                                    <a href="<?= h($candidate['photo_path']) ?>" target="_blank" rel="noopener noreferrer">Open Photo</a>
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

            <details class="acc-item">
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
                                            <?php if ($isEditMode): ?>
                                                <input type="hidden" name="academics[id][]" value="<?= (int) $row['id'] ?>">
                                                <td><input type="text" name="academics[level_name][]" value="<?= h($row['level_name'] ?? '') ?>"></td>
                                                <td><input type="text" name="academics[subject][]" value="<?= h($row['subject'] ?? '') ?>"></td>
                                                <td><input type="text" name="academics[institute][]" value="<?= h($row['institute'] ?? '') ?>"></td>
                                                <td><input type="text" name="academics[passing_year][]" value="<?= h($row['passing_year'] ?? '') ?>"></td>
                                                <td><input type="text" name="academics[percentage][]" value="<?= h($row['percentage'] ?? '') ?>"></td>
                                            <?php else: ?>
                                                <td><?= h($row['level_name'] ?? '') ?></td>
                                                <td><?= h($row['subject'] ?? '') ?></td>
                                                <td><?= h($row['institute'] ?? '') ?></td>
                                                <td><?= h($row['passing_year'] ?? '') ?></td>
                                                <td><?= h($row['percentage'] ?? '') ?></td>
                                            <?php endif; ?>
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

            <details class="acc-item">
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
                                            <?php if ($isEditMode): ?>
                                                <input type="hidden" name="experiences[id][]" value="<?= (int) $row['id'] ?>">
                                                <td><input type="text" name="experiences[company_name][]" value="<?= h($row['company_name'] ?? '') ?>"></td>
                                                <td><input type="text" name="experiences[designation][]" value="<?= h($row['designation'] ?? '') ?>"></td>
                                                <td><input type="text" name="experiences[from_date][]" value="<?= h($row['from_date'] ?? '') ?>"></td>
                                                <td><input type="text" name="experiences[to_date][]" value="<?= h($row['to_date'] ?? '') ?>"></td>
                                                <td><input type="text" name="experiences[salary_ctc][]" value="<?= h($row['salary_ctc'] ?? '') ?>"></td>
                                                <td><input type="text" name="experiences[reason_for_leaving][]" value="<?= h($row['reason_for_leaving'] ?? '') ?>"></td>
                                            <?php else: ?>
                                                <td><?= h($row['company_name'] ?? '') ?></td>
                                                <td><?= h($row['designation'] ?? '') ?></td>
                                                <td><?= h($row['from_date'] ?? '') ?></td>
                                                <td><?= h($row['to_date'] ?? '') ?></td>
                                                <td><?= h($row['salary_ctc'] ?? '') ?></td>
                                                <td><?= h($row['reason_for_leaving'] ?? '') ?></td>
                                            <?php endif; ?>
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

            <details class="acc-item">
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
                                            <?php if ($isEditMode): ?>
                                                <input type="hidden" name="references[id][]" value="<?= (int) $row['id'] ?>">
                                                <td><input type="text" name="references[ref_name][]" value="<?= h($row['ref_name'] ?? '') ?>"></td>
                                                <td><input type="text" name="references[designation][]" value="<?= h($row['designation'] ?? '') ?>"></td>
                                                <td><input type="email" name="references[email][]" value="<?= h($row['email'] ?? '') ?>"></td>
                                                <td><input type="text" name="references[mobile][]" value="<?= h($row['mobile'] ?? '') ?>"></td>
                                            <?php else: ?>
                                                <td><?= h($row['ref_name'] ?? '') ?></td>
                                                <td><?= h($row['designation'] ?? '') ?></td>
                                                <td><?= h($row['email'] ?? '') ?></td>
                                                <td><?= h($row['mobile'] ?? '') ?></td>
                                            <?php endif; ?>
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

            <details class="acc-item">
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
                                            <?php if ($isEditMode): ?>
                                                <input type="hidden" name="family[id][]" value="<?= (int) $row['id'] ?>">
                                                <td><input type="text" name="family[member_name][]" value="<?= h($row['member_name'] ?? '') ?>"></td>
                                                <td><input type="text" name="family[relation_name][]" value="<?= h($row['relation_name'] ?? '') ?>"></td>
                                                <td><input type="text" name="family[occupation][]" value="<?= h($row['occupation'] ?? '') ?>"></td>
                                            <?php else: ?>
                                                <td><?= h($row['member_name'] ?? '') ?></td>
                                                <td><?= h($row['relation_name'] ?? '') ?></td>
                                                <td><?= h($row['occupation'] ?? '') ?></td>
                                            <?php endif; ?>
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

            <details class="acc-item">
                <summary>Interview Rounds</summary>
                <div class="acc-body">
                    <?php if ($rounds): ?>
                        <div class="stack-list">
                            <?php foreach ($rounds as $row): ?>
                                <div class="stack-card">
                                    <h5><?= h($row['round_name'] ?? 'Interview Round') ?></h5>
                                    <p><strong>Date/Time:</strong> <?= h($row['scheduled_at'] ?? '') ?></p>
                                    <p><strong>Status:</strong> <?= h($row['interview_status'] ?? '') ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty">No interview rounds found.</div>
                    <?php endif; ?>
                </div>
            </details>

            <details class="acc-item">
                <summary>Manager Feedback</summary>
                <div class="acc-body">
                    <?php if ($feedbacks): ?>
                        <div class="stack-list">
                            <?php foreach ($feedbacks as $fb): ?>
                                <div class="feedback-card">
                                    <h5><?= h($fb['round_name'] ?? ('Round ' . (int) $fb['round_no'])) ?></h5>
                                    <p><strong>Manager:</strong> <?= h($fb['manager_name'] ?? '-') ?></p>
                                    <p><strong>Recommendation:</strong> <?= h($fb['recommendation'] ?? '') ?></p>
                                    <p><strong>Remarks:</strong> <?= nl2br(h($fb['remark_text'] ?? '')) ?></p>
                                    <p><strong>Submitted At:</strong> <?= h($fb['created_at'] ?? '') ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty">No manager feedback found.</div>
                    <?php endif; ?>
                </div>
            </details>

            <details class="acc-item">
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

        <?php if ($isEditMode): ?>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>