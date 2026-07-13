<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_role(['admin', 'recruiter', 'manager']);

$pdo = Database::connect();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid candidate ID');
}

$stmt = $pdo->prepare("
    SELECT c.*
    FROM candidates c
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    exit('Candidate not found');
}

$feedbackRows = [];
try {
    $feedbackStmt = $pdo->prepare("
        SELECT f.*, u.full_name AS manager_name
        FROM interview_feedback f
        LEFT JOIN users u ON u.id = f.manager_id
        WHERE f.candidate_id = ?
        ORDER BY f.id DESC
    ");
    $feedbackStmt->execute([$id]);
    $feedbackRows = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $feedbackRows = [];
}

$roundRows = [];
try {
    $roundStmt = $pdo->prepare("
        SELECT ir.*, u.full_name AS manager_name
        FROM interview_rounds ir
        LEFT JOIN users u ON u.id = ir.manager_id
        WHERE ir.candidate_id = ?
        ORDER BY ir.id ASC
    ");
    $roundStmt->execute([$id]);
    $roundRows = $roundStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $roundRows = [];
}

$timelineRows = [];
try {
    $timelineStmt = $pdo->prepare("
        SELECT sl.*, u.full_name AS action_user
        FROM status_logs sl
        LEFT JOIN users u ON u.id = sl.action_by
        WHERE sl.candidate_id = ?
        ORDER BY sl.id ASC
    ");
    $timelineStmt->execute([$id]);
    $timelineRows = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $timelineRows = [];
}

$generatedBy = $_SESSION['user']['name'] ?? 'System User';
$generatedDate = date('d-m-Y h:i A');

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/* dynamic base url for localhost + live */
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'];
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$scriptDir = rtrim($scriptDir, '/');

$projectBase = $scheme . '://' . $host;

/* detect localhost project folder */
if (stripos($host, 'localhost') !== false || stripos($host, '127.0.0.1') !== false) {
    $publicPos = stripos($scriptDir, '/public');
    if ($publicPos !== false) {
        $projectRoot = substr($scriptDir, 0, $publicPos + 7);
    } else {
        $projectRoot = $scriptDir;
    }
    $baseUrl = $projectBase . $projectRoot . '/';
} else {
    $baseUrl = $projectBase . '/';
}

$photoUrl = '';
if (!empty($candidate['photo_path'])) {
    $photoPath = ltrim($candidate['photo_path'], '/');
    $photoUrl = $baseUrl . $photoPath;
    $photoUrl = str_replace('/public/uploads/', '/uploads/', $photoUrl);
    $photoUrl = preg_replace('#(?<!:)/{2,}#', '/', $photoUrl);
    $photoUrl = str_replace(':/', '://', $photoUrl);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Detail PDF</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            color:#222;
            margin:0;
            background:#f5f5f5;
        }
        .page{
            width:900px;
            margin:20px auto;
            background:#fff;
            padding:30px;
            border:1px solid #ddd;
        }
        .header{
            text-align:center;
            border-bottom:2px solid #444;
            padding-bottom:12px;
            margin-bottom:20px;
        }
        .header h1{
            margin:0;
            font-size:24px;
            letter-spacing:0.5px;
        }
        .header p{
            margin:6px 0 0;
            font-size:15px;
        }
        .section{
            margin-bottom:24px;
        }
        .section h2{
            font-size:18px;
            margin:0 0 10px;
            padding-bottom:6px;
            border-bottom:1px solid #ccc;
        }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:10px;
        }
        table th, table td{
            border:1px solid #ccc;
            padding:8px;
            font-size:14px;
            vertical-align:top;
            text-align:left;
        }
        .footer{
            margin-top:30px;
            padding-top:12px;
            border-top:2px solid #444;
            font-size:14px;
        }
        .print-btn{
            width:900px;
            margin:20px auto 0;
            text-align:right;
        }
        .print-btn button{
            background:#b8328a;
            color:#fff;
            border:none;
            padding:10px 16px;
            border-radius:6px;
            cursor:pointer;
            font-size:14px;
        }
        .photo-box{
            width:140px;
            height:160px;
            object-fit:cover;
            border:1px solid #ccc;
            display:block;
            margin:0 auto;
        }
        .photo-empty{
            width:140px;
            height:160px;
            border:1px solid #ccc;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto;
            font-size:14px;
            color:#666;
        }
        .small-text{
            font-size:12px;
            color:#666;
            word-break:break-all;
        }
        @media print{
            .print-btn{ display:none; }
            body{ background:#fff; }
            .page{
                margin:0 auto;
                border:none;
                width:100%;
            }
        }
    </style>
</head>
<body>

<div class="print-btn">
    <button onclick="window.print()">Print / Save PDF</button>
</div>

<div class="page">
    <div class="header">
        <h1>UNIRE BUSINESS SOLUTIONS</h1>
        <p>Recruitment Workflow Report</p>
    </div>

    <div class="section">
        <h2>Candidate Information</h2>

        <table>
            <tr>
                <td style="width:180px; text-align:center; vertical-align:top;">
                    <?php if (!empty($photoUrl)): ?>
                        <img src="<?= e($photoUrl) ?>" alt="Candidate Photo" class="photo-box">
                    <?php else: ?>
                        <div class="photo-empty">No Photo</div>
                    <?php endif; ?>
                </td>

                <td>
                    <table>
                        <tr>
                            <th style="width:180px;">Application No</th>
                            <td><?= e($candidate['application_no'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Full Name</th>
                            <td><?= e($candidate['full_name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= e($candidate['email'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?= e($candidate['phone'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Position Applied</th>
                            <td><?= e($candidate['position_applied'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Current Status</th>
                            <td><?= e($candidate['current_status'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Applied At</th>
                            <td><?= e($candidate['applied_at'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>Resume File</th>
                            <td><?= !empty($candidate['resume_path']) ? e(basename($candidate['resume_path'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <th>Photo URL</th>
                            <td class="small-text"><?= e($photoUrl ?: '-') ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Recruiter Information</h2>
        <table>
            <tr>
                <th style="width:180px;">Source Type</th>
                <td><?= e($candidate['source_type'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>Reference Name</th>
                <td><?= e($candidate['source_reference_name'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>Final Decision</th>
                <td><?= e($candidate['final_decision'] ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Manager Feedback</h2>
        <?php if ($feedbackRows): ?>
            <table>
                <tr>
                    <th>Manager</th>
                    <th>Recommendation</th>
                    <th>Remark</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($feedbackRows as $row): ?>
                    <tr>
                        <td><?= e($row['manager_name'] ?? '-') ?></td>
                        <td><?= e($row['recommendation'] ?? '-') ?></td>
                        <td><?= e($row['remark_text'] ?? '-') ?></td>
                        <td><?= e($row['created_at'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No manager feedback available.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Interview Round History</h2>
        <?php if ($roundRows): ?>
            <table>
                <tr>
                    <th>Round</th>
                    <th>Manager</th>
                    <th>Status</th>
                    <th>Scheduled At</th>
                </tr>
                <?php foreach ($roundRows as $row): ?>
                    <tr>
                        <td><?= e($row['round_name'] ?? '-') ?></td>
                        <td><?= e($row['manager_name'] ?? '-') ?></td>
                        <td><?= e($row['interview_status'] ?? '-') ?></td>
                        <td><?= e($row['scheduled_at'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No interview rounds available.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Status Timeline</h2>
        <?php if ($timelineRows): ?>
            <table>
                <tr>
                    <th>Old Status</th>
                    <th>New Status</th>
                    <th>Action By</th>
                    <th>Note</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($timelineRows as $row): ?>
                    <tr>
                        <td><?= e($row['old_status'] ?? '-') ?></td>
                        <td><?= e($row['new_status'] ?? '-') ?></td>
                        <td><?= e($row['action_user'] ?? '-') ?></td>
                        <td><?= e($row['note'] ?? '-') ?></td>
                        <td><?= e($row['created_at'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No status timeline available.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Final Decision</h2>
        <table>
            <tr>
                <th style="width:180px;">Decision</th>
                <td><?= e($candidate['final_decision'] ?? '-') ?></td>
            </tr>
            <tr>
                <th>Current Status</th>
                <td><?= e($candidate['current_status'] ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p><strong>Generated By:</strong> <?= e($generatedBy) ?></p>
        <p><strong>Generated Date:</strong> <?= e($generatedDate) ?></p>
    </div>
</div>

</body>
</html>