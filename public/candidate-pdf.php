<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function tableOrEmpty(array $rows, callable $renderer, int $colspan)
{
    if (!$rows) {
        return '<tr><td colspan="' . $colspan . '">No records found.</td></tr>';
    }

    $html = '';
    foreach ($rows as $row) {
        $html .= $renderer($row);
    }
    return $html;
}

$logoBase64 = '';
$candidatePhotoBase64 = '';

$logoPath = __DIR__ . '/assets/logo.png';
if (file_exists($logoPath)) {
    $logoExt = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
    $logoMime = $logoExt === 'jpg' || $logoExt === 'jpeg' ? 'jpeg' : 'png';
    $logoBase64 = 'data:image/' . $logoMime . ';base64,' . base64_encode(file_get_contents($logoPath));
}

if (!empty($candidate['photo_path'])) {
    $photoFileName = basename($candidate['photo_path']);
    $photoPath = __DIR__ . '/uploads/candidates/' . $photoFileName;

    if (file_exists($photoPath)) {
        $photoExt = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
        $photoMime = $photoExt === 'jpg' || $photoExt === 'jpeg' ? 'jpeg' : 'png';
        $candidatePhotoBase64 = 'data:image/' . $photoMime . ';base64,' . base64_encode(file_get_contents($photoPath));
    }
}
ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Candidate Summary PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.45;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 10px;
            color: #7d2562;
        }

        h2 {
            font-size: 15px;
            margin: 22px 0 8px;
            padding: 6px 10px;
            background: #f4e7f0;
            color: #6f2359;
            border: 1px solid #e5cfe0;
        }

        .meta {
            margin-bottom: 16px;
            font-size: 12px;
        }

        .meta strong {
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        th,
        td {
            border: 1px solid #cfcfcf;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #f7f7f7;
            font-weight: bold;
        }

        .two-col td:first-child,
        .two-col th:first-child {
            width: 28%;
            background: #fafafa;
            font-weight: bold;
        }

        .section {
            margin-bottom: 12px;
        }

        .small {
            font-size: 11px;
            color: #555;
        }

        .report-header {
            width: 100%;
            margin-bottom: 20px;
        }

        .report-header-table {
            width: 100%;
            border: 0;
        }

        .report-header-table td {
            border: 0;
            vertical-align: top;
            padding: 0;
        }

        .report-title-cell {
            width: 75%;
        }

        .report-logo-cell {
            width: 25%;
            text-align: right;
        }

        .report-logo {
            max-width: 130px;
            max-height: 100px;
        }

        .report-logo img {
            max-width: 130px;
            max-height: 100px;
        }

        /* new css header  */

        .report-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .report-header-table td {
            border: 0;
            vertical-align: top;
            padding: 0;
        }

        .header-photo-col {
            width: 28%;
            padding-right: 18px;
        }

        .header-detail-col {
            width: 72%;
        }

        .candidate-photo-box {
            width: 150px;
            height: 180px;
            border: 1px solid #d7c5d1;
            padding: 6px;
            text-align: center;
        }

        .candidate-photo-box img {
            width: 136px;
            height: 166px;
            object-fit: cover;
        }

        .no-photo {
            width: 136px;
            height: 166px;
            line-height: 166px;
            text-align: center;
            background: #f7f7f7;
            color: #777;
            font-size: 12px;
        }

        .right-top-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .right-top-table td {
            border: 0;
            vertical-align: top;
            padding: 0;
        }

        .report-title-cell {
            width: 72%;
            padding-right: 10px;
        }

        .report-logo-cell {
            width: 28%;
            text-align: right;
        }

        .report-logo {
            max-width: 110px;
            max-height: 80px;
        }

        .report-logo img {
            max-width: 110px;
            max-height: 80px;
        }

        .detail-lines {
            margin-top: 6px;
            font-size: 12px;
            line-height: 1.7;
        }

        .detail-lines strong {
            color: #000;
        }
    </style>
</head>

<body>

    <table class="report-header-table">
        <tr>
            <td class="header-photo-col">
                <div class="candidate-photo-box">
                    <?php if ($candidatePhotoBase64): ?>
                        <img src="<?= $candidatePhotoBase64 ?>" alt="Candidate Photo">
                    <?php else: ?>
                        <div class="no-photo">No Photo</div>
                    <?php endif; ?>
                </div>
            </td>

            <td class="header-detail-col">
                <table class="right-top-table">
                    <tr>
                        <td class="report-title-cell">
                            <h1>Candidate Summary Report</h1>
                        </td>
                        <td class="report-logo-cell">
                            <?php if ($logoBase64): ?>
                                <img class="report-logo" src="<?= $logoBase64 ?>" alt="Company Logo">
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <div class="detail-lines">
                    <strong>Application No:</strong> <?= e($candidate['application_no'] ?? '') ?><br>
                    <strong>Candidate Name:</strong> <?= e($candidate['full_name'] ?? '') ?><br>
                    <strong>Email:</strong> <?= e($candidate['email'] ?? '') ?><br>
                    <strong>Phone:</strong> <?= e($candidate['phone'] ?? '') ?><br>
                    <strong>Position Applied:</strong> <?= e($candidate['position_applied'] ?? '') ?><br>
                    <strong>Department:</strong> <?= e($candidate['department'] ?? '') ?><br>
                    <strong>Current Status:</strong> <?= e($candidate['current_status'] ?? 'submitted') ?><br>
                    <strong>Final Decision:</strong> <?= e($candidate['final_decision'] ?? 'pending') ?><br>
                    
                </div>
            </td>
        </tr>
    </table>

    <h2>User Info</h2>
    <table class="two-col">
        <tr>
            <td>Full Name</td>
            <td><?= e($candidate['full_name'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Email</td>
            <td><?= e($candidate['email'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Phone</td>
            <td><?= e($candidate['phone'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Alternate Phone</td>
            <td><?= e($candidate['alternate_phone'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Father / Husband Name</td>
            <td><?= e($candidate['father_husband_name'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Emergency No</td>
            <td><?= e($candidate['emergency_no'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Date of Birth</td>
            <td><?= e($candidate['dob'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Age</td>
            <td><?= e($candidate['age'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Gender</td>
            <td><?= e($candidate['gender'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Marital Status</td>
            <td><?= e($candidate['marital_status'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Highest Qualification</td>
            <td><?= e($candidate['highest_qualification'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Total Experience</td>
            <td><?= e($candidate['total_experience'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Current Company</td>
            <td><?= e($candidate['current_company'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Current Salary</td>
            <td><?= e($candidate['current_salary'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Expected Salary</td>
            <td><?= e($candidate['expected_salary'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Position Applied</td>
            <td><?= e($candidate['position_applied'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Department</td>
            <td><?= e($candidate['department'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Notice Period</td>
            <td><?= e($candidate['notice_period'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Notice Specify</td>
            <td><?= e($candidate['notice_period_specify'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Source Type</td>
            <td><?= e($candidate['source_type'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Source Reference Name</td>
            <td><?= e($candidate['source_reference_name'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Current Address</td>
            <td><?= nl2br(e($candidate['address'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>Permanent Address</td>
            <td><?= nl2br(e($candidate['permanent_address'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>City</td>
            <td><?= e($candidate['city'] ?? '') ?></td>
        </tr>
        <tr>
            <td>State</td>
            <td><?= e($candidate['state'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Pincode</td>
            <td><?= e($candidate['pincode'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Career Goals</td>
            <td><?= nl2br(e($candidate['career_goals'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>Interest in Field</td>
            <td><?= nl2br(e($candidate['interest_in_field'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>Scheduled Exam</td>
            <td><?= nl2br(e($candidate['scheduled_exam'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>Strengths</td>
            <td><?= nl2br(e($candidate['strengths'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>Weakness</td>
            <td><?= nl2br(e($candidate['weakness'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>EPF Registered</td>
            <td><?= e($candidate['epf_registered'] ?? '') ?></td>
        </tr>
        <tr>
            <td>UAN No</td>
            <td><?= e($candidate['uan_no'] ?? '') ?></td>
        </tr>
        <tr>
            <td>ESIC Registered</td>
            <td><?= e($candidate['esic_registered'] ?? '') ?></td>
        </tr>
        <tr>
            <td>IP No</td>
            <td><?= e($candidate['ip_no'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Aadhaar No</td>
            <td><?= e($candidate['aadhaar_no'] ?? '') ?></td>
        </tr>
        <tr>
            <td>PAN No</td>
            <td><?= e($candidate['pan_no'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Bank Account No</td>
            <td><?= e($candidate['bank_account_no'] ?? '') ?></td>
        </tr>
        <tr>
            <td>IFSC Code</td>
            <td><?= e($candidate['ifsc_code'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Weekly Working Days</td>
            <td><?= e($candidate['weekly_working_days'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Smoking</td>
            <td><?= e($candidate['smoking'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Self Vehicle</td>
            <td><?= e($candidate['self_vehicle'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Driving Licence</td>
            <td><?= e($candidate['driving_licence'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Medical Issue</td>
            <td><?= nl2br(e($candidate['medical_issue'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>Hobbies</td>
            <td><?= nl2br(e($candidate['hobbies'] ?? '')) ?></td>
        </tr>
        <tr>
            <td>Resume Path</td>
            <td><?= e($candidate['resume_path'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Photo Path</td>
            <td><?= e($candidate['photo_path'] ?? '') ?></td>
        </tr>
        <tr>
            <td>Applied At</td>
            <td><?= e($candidate['applied_at'] ?? '') ?></td>
        </tr>
    </table>

    <h2>Academic Details</h2>
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
            <?= tableOrEmpty($academics, function ($row) {
                return '<tr>
                    <td>' . e($row['level_name'] ?? '') . '</td>
                    <td>' . e($row['subject'] ?? '') . '</td>
                    <td>' . e($row['institute'] ?? '') . '</td>
                    <td>' . e($row['passing_year'] ?? '') . '</td>
                    <td>' . e($row['percentage'] ?? '') . '</td>
                </tr>';
            }, 5); ?>
        </tbody>
    </table>

    <h2>Experience</h2>
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
            <?= tableOrEmpty($experiences, function ($row) {
                return '<tr>
                    <td>' . e($row['company_name'] ?? '') . '</td>
                    <td>' . e($row['designation'] ?? '') . '</td>
                    <td>' . e($row['from_date'] ?? '') . '</td>
                    <td>' . e($row['to_date'] ?? '') . '</td>
                    <td>' . e($row['salary_ctc'] ?? '') . '</td>
                    <td>' . e($row['reason_for_leaving'] ?? '') . '</td>
                </tr>';
            }, 6); ?>
        </tbody>
    </table>

    <h2>References</h2>
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
            <?= tableOrEmpty($references, function ($row) {
                return '<tr>
                    <td>' . e($row['ref_name'] ?? '') . '</td>
                    <td>' . e($row['designation'] ?? '') . '</td>
                    <td>' . e($row['email'] ?? '') . '</td>
                    <td>' . e($row['mobile'] ?? '') . '</td>
                </tr>';
            }, 4); ?>
        </tbody>
    </table>

    <h2>Family Details</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Relation</th>
                <th>Occupation</th>
            </tr>
        </thead>
        <tbody>
            <?= tableOrEmpty($familyDetails, function ($row) {
                return '<tr>
                    <td>' . e($row['member_name'] ?? '') . '</td>
                    <td>' . e($row['relation_name'] ?? '') . '</td>
                    <td>' . e($row['occupation'] ?? '') . '</td>
                </tr>';
            }, 3); ?>
        </tbody>
    </table>

    <h2>Interview Rounds</h2>
    <table>
        <thead>
            <tr>
                <th>Round Name</th>
                <th>Scheduled At</th>
                <th>Status</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?= tableOrEmpty($rounds, function ($row) {
                return '<tr>
                    <td>' . e($row['round_name'] ?? '') . '</td>
                    <td>' . e($row['scheduled_at'] ?? '') . '</td>
                    <td>' . e($row['status'] ?? '') . '</td>
                    <td>' . nl2br(e($row['note'] ?? '')) . '</td>
                </tr>';
            }, 4); ?>
        </tbody>
    </table>

    <h2>Manager Feedback</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Recommendation</th>
                <th>Rating</th>
                <th>Remarks</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            <?= tableOrEmpty($feedbacks, function ($row) {
                return '<tr>
                    <td>' . e($row['title'] ?? '') . '</td>
                    <td>' . e($row['recommendation'] ?? '') . '</td>
                    <td>' . e($row['rating'] ?? '') . '</td>
                    <td>' . nl2br(e($row['remarks'] ?? '')) . '</td>
                    <td>' . e($row['created_at'] ?? '') . '</td>
                </tr>';
            }, 5); ?>
        </tbody>
    </table>

    <h2>Status History</h2>
    <table>
        <thead>
            <tr>
                <th>Old Status</th>
                <th>New Status</th>
                <th>Action Role</th>
                <th>Note</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?= tableOrEmpty($statusLogs, function ($row) {
                return '<tr>
                    <td>' . e($row['old_status'] ?? '') . '</td>
                    <td>' . e($row['new_status'] ?? '') . '</td>
                    <td>' . e($row['action_role'] ?? '') . '</td>
                    <td>' . nl2br(e($row['note'] ?? '')) . '</td>
                    <td>' . e($row['created_at'] ?? '') . '</td>
                </tr>';
            }, 5); ?>
        </tbody>
    </table>

    <p class="small" style="margin-top: 20px;">
        End of candidate summary report.
        <strong>Generated At:</strong> <?= date('Y-m-d H:i:s') ?>
    </p>
</body>

</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$fileName = 'candidate-summary-' . preg_replace('/[^A-Za-z0-9_\-]/', '-', ($candidate['application_no'] ?? (string) $id)) . '.pdf';

$dompdf->stream($fileName, ['Attachment' => false]);
exit;