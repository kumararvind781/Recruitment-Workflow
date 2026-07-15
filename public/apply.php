<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../app/config/database.php';

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$success = '';
$error = '';

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function post($key, $default = '')
{
    return trim($_POST[$key] ?? $default);
}

function uploadFile($fileKey, $targetDir, array $allowedExt)
{
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Upload failed for {$fileKey}.");
    }

    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
        throw new Exception("Unable to create upload directory.");
    }

    $originalName = $_FILES[$fileKey]['name'];
    $tmpName = $_FILES[$fileKey]['tmp_name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        throw new Exception("Invalid file type for {$fileKey}.");
    }

    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $newName = time() . '_' . $fileKey . '_' . $safeBase . '.' . $ext;
    $fullPath = rtrim($targetDir, '/') . '/' . $newName;

    if (!move_uploaded_file($tmpName, $fullPath)) {
        throw new Exception("Failed to move uploaded file for {$fileKey}.");
    }

    return str_replace(__DIR__ . '/', '', $fullPath);
}

function generateApplicationNo(PDO $pdo)
{
    $prefix = 'APP' . date('Ymd');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM candidates WHERE application_no LIKE ?");
    $stmt->execute([$prefix . '%']);
    $count = (int)$stmt->fetchColumn() + 1;
    return $prefix . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
}

$states = [
    'Andhra Pradesh' => ['Visakhapatnam', 'Vijayawada', 'Guntur', 'Tirupati', 'Nellore'],
    'Arunachal Pradesh' => ['Itanagar', 'Naharlagun'],
    'Assam' => ['Guwahati', 'Dibrugarh', 'Silchar', 'Jorhat'],
    'Bihar' => ['Patna', 'Gaya', 'Bhagalpur', 'Muzaffarpur'],
    'Chhattisgarh' => ['Raipur', 'Bilaspur', 'Durg'],
    'Goa' => ['Panaji', 'Margao', 'Vasco da Gama'],
    'Gujarat' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot'],
    'Haryana' => ['Gurugram', 'Faridabad', 'Panipat', 'Ambala'],
    'Himachal Pradesh' => ['Shimla', 'Manali', 'Dharamshala'],
    'Jharkhand' => ['Ranchi', 'Jamshedpur', 'Dhanbad'],
    'Karnataka' => ['Bengaluru', 'Mysuru', 'Mangaluru', 'Hubballi'],
    'Kerala' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur'],
    'Madhya Pradesh' => ['Bhopal', 'Indore', 'Gwalior', 'Jabalpur'],
    'Maharashtra' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik'],
    'Manipur' => ['Imphal'],
    'Meghalaya' => ['Shillong'],
    'Mizoram' => ['Aizawl'],
    'Nagaland' => ['Kohima', 'Dimapur'],
    'Odisha' => ['Bhubaneswar', 'Cuttack', 'Rourkela'],
    'Punjab' => ['Ludhiana', 'Amritsar', 'Jalandhar'],
    'Rajasthan' => ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Ajmer', 'Alwar', 'Bikaner', 'Bharatpur'],
    'Sikkim' => ['Gangtok'],
    'Tamil Nadu' => ['Chennai', 'Coimbatore', 'Madurai', 'Tiruchirappalli'],
    'Telangana' => ['Hyderabad', 'Warangal', 'Nizamabad'],
    'Tripura' => ['Agartala'],
    'Uttar Pradesh' => ['Lucknow', 'Kanpur', 'Varanasi', 'Agra'],
    'Uttarakhand' => ['Dehradun', 'Haridwar', 'Roorkee'],
    'West Bengal' => ['Kolkata', 'Howrah', 'Siliguri'],
    'Delhi' => ['New Delhi', 'Delhi'],
    'Jammu & Kashmir' => ['Srinagar', 'Jammu'],
    'Ladakh' => ['Leh', 'Kargil'],
    'Chandigarh' => ['Chandigarh'],
    'Puducherry' => ['Puducherry']
];

$boards = [
    'CBSE (Central)',
    'RBSE / BSER (Rajasthan)',
    'CISCE (Central)',
    'NIOS (Central)',
    'UPMSP (Uttar Pradesh)',
    'AHSEC (Assam)',
    'BIEAP (Andhra Pradesh)',
    'BSEB (Bihar)',
    'GSEB (Gujarat)',
    'HBSE / BSEH (Haryana)',
    'JAC (Jharkhand)',
    'MPBSE (Madhya Pradesh)',
    'MSBSHSE (Maharashtra)',
    'PSEB (Punjab)',
    'TNBSE / TNDGE (Tamil Nadu)',
    'TSBIE (Telangana)',
    'UBSE (Uttarakhand)',
    'WBBSE (West Bengal)'
];

$positionOptions = ['Process Associate', 'Operations', 'Accounts', 'Reservation'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fullName = post('full_name');
        $email = post('email');
        $phone = post('phone');
        $alternatePhone = post('alternate_phone');
        $fatherHusbandName = post('father_husband_name');
        $emergencyNo = post('emergency_no');
        $dob = post('dob');
        $age = post('age');
        $gender = post('gender');
        $maritalStatus = post('marital_status');
        $address = post('address');
        $permanentAddress = post('permanent_address');
        $city = post('city');
        $state = post('state');
        $pincode = post('pincode');
        $highestQualification = post('highest_qualification');
        $positionApplied = post('position_applied');
        $department = post('department');
        $totalExperience = post('total_experience');
        $currentCompany = post('current_company');
        $currentSalary = post('current_salary');
        $expectedSalary = post('expected_salary');
        $scheduledExam = post('scheduled_exam');
        $careerGoals = post('career_goals');
        $interestInField = post('interest_in_field');
        $noticePeriod = post('notice_period');
        $noticePeriodSpecify = post('notice_period_specify');
        $sourceType = post('source_type', 'walkin');
        $sourceReferenceName = post('source_reference_name');
        $strengths = post('strengths');
        $weakness = post('weakness');
        $aadhaarNo = post('aadhaar_no');
        $hobbies = post('hobbies');
        $computerKnowledge = post('computer_knowledge');
        $medicalIssue = post('medical_issue');

        if ($fullName === '' || $email === '' || $phone === '' || $positionApplied === '') {
            throw new Exception('Please fill all required fields.');
        }

        $resumePath = uploadFile('resume', __DIR__ . '/uploads/resumes', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);
        $photoPath = uploadFile('photo', __DIR__ . '/uploads/photos', ['jpg', 'jpeg', 'png']);

        $pdo->beginTransaction();

        $applicationNo = generateApplicationNo($pdo);

        $candidateSql = "
            INSERT INTO candidates (
                application_no, full_name, email, phone, alternate_phone,
                father_husband_name, emergency_no, gender, dob, age, marital_status,
                address, permanent_address, city, state, pincode,
                highest_qualification, total_experience, current_company, current_salary, expected_salary,
                position_applied, department, source_type, source_reference_name,
                scheduled_exam, career_goals, interest_in_field, notice_period, notice_period_specify,
                strengths, weakness, aadhaar_no, hobbies, computer_knowledge, medical_issue,
                photo_path, resume_path, current_status, final_decision, applied_at
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, 'submitted', 'pending', NOW()
            )
        ";

        $candidateStmt = $pdo->prepare($candidateSql);
        $candidateStmt->execute([
            $applicationNo,
            $fullName,
            $email,
            $phone,
            $alternatePhone ?: null,
            $fatherHusbandName ?: null,
            $emergencyNo ?: null,
            $gender ?: null,
            $dob ?: null,
            ($age !== '' ? (int)$age : null),
            $maritalStatus ?: null,
            $address ?: null,
            $permanentAddress ?: null,
            $city ?: null,
            $state ?: null,
            $pincode ?: null,
            $highestQualification ?: null,
            ($totalExperience !== '' ? $totalExperience : null),
            $currentCompany ?: null,
            ($currentSalary !== '' ? $currentSalary : null),
            ($expectedSalary !== '' ? $expectedSalary : null),
            $positionApplied,
            $department ?: null,
            in_array($sourceType, ['walkin', 'qr', 'link', 'reference'], true) ? $sourceType : 'walkin',
            $sourceReferenceName ?: null,
            $scheduledExam ?: null,
            $careerGoals ?: null,
            $interestInField ?: null,
            $noticePeriod ?: null,
            $noticePeriodSpecify ?: null,
            $strengths ?: null,
            $weakness ?: null,
            $aadhaarNo ?: null,
            $hobbies ?: null,
            $computerKnowledge ?: null,
            $medicalIssue ?: null,
            $photoPath,
            $resumePath
        ]);

        $candidateId = (int)$pdo->lastInsertId();

        if (!empty($_POST['academic_level']) && is_array($_POST['academic_level'])) {
            $acadStmt = $pdo->prepare("
                INSERT INTO candidate_academics
                (candidate_id, level_name, subject, institute, passing_year, percentage, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($_POST['academic_level'] as $i => $level) {
                $level = trim($_POST['academic_level'][$i] ?? '');
                $board = trim($_POST['academic_board'][$i] ?? '');
                $subject = trim($_POST['academic_subject'][$i] ?? '');
                $institute = trim($_POST['academic_institute'][$i] ?? '');
                $passingYear = trim($_POST['academic_passing_year'][$i] ?? '');
                $percentage = trim($_POST['academic_percentage'][$i] ?? '');

                if ($level === '' && $board === '' && $subject === '' && $institute === '' && $passingYear === '' && $percentage === '') {
                    continue;
                }

                $subjectValue = trim($board . ($subject !== '' ? ' - ' . $subject : ''));

                $acadStmt->execute([
                    $candidateId,
                    $level ?: null,
                    $subjectValue ?: null,
                    $institute ?: null,
                    $passingYear ?: null,
                    $percentage ?: null
                ]);
            }
        }

        if (!empty($_POST['exp_company_name']) && is_array($_POST['exp_company_name'])) {
            $expStmt = $pdo->prepare("
                INSERT INTO candidate_experiences
                (candidate_id, company_name, designation, from_date, to_date, salary_ctc, reason_for_leaving, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($_POST['exp_company_name'] as $i => $companyName) {
                $companyName = trim($_POST['exp_company_name'][$i] ?? '');
                $designation = trim($_POST['exp_designation'][$i] ?? '');
                $fromDate = trim($_POST['exp_from'][$i] ?? '');
                $toDate = trim($_POST['exp_to'][$i] ?? '');
                $salaryCtc = trim($_POST['exp_salary_ctc'][$i] ?? '');
                $reason = trim($_POST['exp_reason_leaving'][$i] ?? '');

                if ($companyName === '' && $designation === '' && $fromDate === '' && $toDate === '' && $salaryCtc === '' && $reason === '') {
                    continue;
                }

                $expStmt->execute([
                    $candidateId,
                    $companyName ?: null,
                    $designation ?: null,
                    $fromDate ?: null,
                    $toDate ?: null,
                    $salaryCtc ?: null,
                    $reason ?: null
                ]);
            }
        }

        if (!empty($_POST['reference_name']) && is_array($_POST['reference_name'])) {
            $refStmt = $pdo->prepare("
                INSERT INTO candidate_references
                (candidate_id, ref_name, designation, email, mobile, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            foreach ($_POST['reference_name'] as $i => $refName) {
                $refName = trim($_POST['reference_name'][$i] ?? '');
                $designation = trim($_POST['reference_designation'][$i] ?? '');
                $refEmail = trim($_POST['reference_email'][$i] ?? '');
                $refMobile = trim($_POST['reference_mobile'][$i] ?? '');

                if ($refName === '' && $designation === '' && $refEmail === '' && $refMobile === '') {
                    continue;
                }

                $refStmt->execute([
                    $candidateId,
                    $refName ?: null,
                    $designation ?: null,
                    $refEmail ?: null,
                    $refMobile ?: null
                ]);
            }
        }

        if (!empty($_POST['family_name']) && is_array($_POST['family_name'])) {
            $famStmt = $pdo->prepare("
                INSERT INTO candidate_family_details
                (candidate_id, member_name, relation_name, occupation, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            foreach ($_POST['family_name'] as $i => $memberName) {
                $memberName = trim($_POST['family_name'][$i] ?? '');
                $relationName = trim($_POST['family_relation'][$i] ?? '');
                $occupation = trim($_POST['family_occupation'][$i] ?? '');

                if ($memberName === '' && $relationName === '' && $occupation === '') {
                    continue;
                }

                $famStmt->execute([
                    $candidateId,
                    $memberName ?: null,
                    $relationName ?: null,
                    $occupation ?: null
                ]);
            }
        }

        if (!empty($_POST['language_name']) && is_array($_POST['language_name'])) {
            $langStmt = $pdo->prepare("
                INSERT INTO candidate_languages
                (candidate_id, language_name, can_read, can_write, can_speak, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            foreach ($_POST['language_name'] as $i => $languageName) {
                $languageName = trim($_POST['language_name'][$i] ?? '');
                $canRead = trim($_POST['language_read'][$i] ?? '');
                $canWrite = trim($_POST['language_write'][$i] ?? '');
                $canSpeak = trim($_POST['language_speak'][$i] ?? '');

                if ($languageName === '' && $canRead === '' && $canWrite === '' && $canSpeak === '') {
                    continue;
                }

                $langStmt->execute([
                    $candidateId,
                    $languageName ?: null,
                    $canRead ?: null,
                    $canWrite ?: null,
                    $canSpeak ?: null
                ]);
            }
        }

        $actionBy = $_SESSION['user']['id'] ?? null;
        $actionRole = $_SESSION['user']['role'] ?? 'candidate';

        $logStmt = $pdo->prepare("
            INSERT INTO candidate_status_logs
            (candidate_id, action_by, action_role, old_status, new_status, note, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        $logStmt->execute([
            $candidateId,
            $actionBy,
            $actionRole,
            null,
            'submitted',
            'Candidate applied from public form'
        ]);

        $pdo->commit();

        $success = 'Application submitted successfully. Your application number is ' . $applicationNo;
        $_POST = [];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Now | UNIRE Recruitment Workflow</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; background: #f7f3f7; color: #2c2337; }
        .page { max-width: 1180px; margin: 26px auto; padding: 0 18px 32px; }
        .card { background: #fff; border: 1px solid #eadfeb; border-radius: 22px; box-shadow: 0 12px 30px rgba(122,69,119,.08); }
        .intro { padding: 28px 24px; min-height: 180px; background: linear-gradient(180deg,#fff 0%,#fdf8fc 100%); margin-bottom: 22px; }
        .pill { display:inline-block; padding:8px 14px; border-radius:999px; background:#f5deed; color:#b93a8d; font-size:13px; font-weight:700; letter-spacing:.4px; margin-bottom:20px; }
        .intro h2 { margin:0 0 14px; font-size:24px; line-height:1.3; }
        .intro p { margin:0; font-size:16px; color:#5f546d; line-height:1.65; }
        .form-card { padding: 22px; }
        .message { padding:12px 14px; border-radius:12px; margin-bottom:16px; font-size:14px; }
        .success { background:#e0f5e7; color:#17633b; border:1px solid #b8e4c7; }
        .error { background:#fde7ea; color:#9a2137; border:1px solid #f5bcc7; }
        .section-title { margin:0 0 14px; font-size:18px; color:#2f2640; border-bottom:1px solid #f0e7f0; padding-bottom:8px; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px 18px; }
        .grid-1 { grid-column:1/-1; }
        .field label { display:block; margin-bottom:7px; font-size:14px; font-weight:700; color:#372d48; }
        .field input, .field select, .field textarea {
            width:100%; border:1px solid #e3dbe5; background:#fff; border-radius:14px;
            padding:12px 14px; font-size:14px; color:#2b2436; outline:none;
        }
        .field textarea { min-height:96px; resize:vertical; }
        .field input:focus, .field select:focus, .field textarea:focus {
            border-color:#c34a96; box-shadow:0 0 0 3px rgba(195,74,150,.12);
        }
        .table-block { margin-top:14px; overflow:auto; border:1px solid #eee3ee; border-radius:14px; }
        table { width:100%; border-collapse:collapse; min-width:760px; background:#fff; }
        th, td { border-bottom:1px solid #f1e8f1; padding:10px; text-align:left; vertical-align:top; }
        th { background:#fbf7fb; color:#554a66; font-size:13px; }
        td input, td select { width:100%; border:1px solid #e3dbe5; border-radius:10px; padding:10px 11px; font-size:13px; }
        .actions { margin-top:24px; display:flex; gap:12px; flex-wrap:wrap; }
        .btn { border:none; border-radius:14px; padding:13px 20px; font-size:15px; font-weight:700; cursor:pointer; }
        .btn-primary { background:linear-gradient(135deg,#c5368f,#a3297c); color:#fff; box-shadow:0 10px 22px rgba(181,47,129,.22); }
        .btn-secondary { background:#f6ebf3; color:#9f2f7b; }
        .btn-add { background:#fff; border:1px dashed #c34a96; color:#c34a96; padding:10px 14px; }
        .btn-remove { background:#fff3f6; border:1px solid #f0b8ca; color:#b1265d; padding:10px 14px; }
        .muted { color:#7a7087; font-size:13px; }
        .mt-24 { margin-top:24px; }
        .same-wrap { display:inline-flex; align-items:center; gap:10px; margin:8px 0 10px; color:#5f546d; font-size:14px; font-weight:600; }
        .same-wrap input[type="checkbox"] { width:18px; height:18px; margin:0; flex:0 0 auto; }
        .same-wrap span { line-height:1; }
        .upper-input { text-transform: uppercase; }
        .hidden { display:none !important; }

        @media (max-width:640px) {
            .grid { grid-template-columns:1fr; }
            .form-card { padding:16px; }
            .intro { min-height:auto; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="card intro">
        <span class="pill">UNIRE CAREERS</span>
        <h2>Candidate Application Form</h2>
        <p>Fill your details, upload resume and passport-size photo, and submit your application for <strong>Recruiter Review.</strong></p>
    </div>

    <div class="card form-card">
        <?php if ($success): ?>
            <div class="message success"><?= h($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="applyForm">
            <h3 class="section-title">Personal Information</h3>
            <div class="grid">
                <div class="field grid-1">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" value="<?= h($_POST['full_name'] ?? '') ?>" required>
                </div>

                <div class="field grid-1">
                    <label>Father / Husband Name</label>
                    <input type="text" name="father_husband_name" value="<?= h($_POST['father_husband_name'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label>Phone *</label>
                    <input type="text" name="phone" value="<?= h($_POST['phone'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label>Alternate Phone</label>
                    <input type="text" name="alternate_phone" value="<?= h($_POST['alternate_phone'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Aadhaar Number</label>
                    <input type="text" name="aadhaar_no" value="<?= h($_POST['aadhaar_no'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Emergency Contact No.</label>
                    <input type="text" name="emergency_no" value="<?= h($_POST['emergency_no'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" id="dob" value="<?= h($_POST['dob'] ?? '') ?>" required>
                </div>

                <div class="field">
                    <label>Age</label>
                    <input type="number" name="age" id="age" value="<?= h($_POST['age'] ?? '') ?>" readonly>
                </div>

                <div class="field">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">Select gender</option>
                        <option value="Male" <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="field">
                    <label>Marital Status</label>
                    <select name="marital_status">
                        <option value="">Select status</option>
                        <option value="Married" <?= (($_POST['marital_status'] ?? '') === 'Married') ? 'selected' : '' ?>>Married</option>
                        <option value="Single" <?= (($_POST['marital_status'] ?? '') === 'Single') ? 'selected' : '' ?>>Single</option>
                        <option value="Divorced" <?= (($_POST['marital_status'] ?? '') === 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                        <option value="Widow" <?= (($_POST['marital_status'] ?? '') === 'Widow') ? 'selected' : '' ?>>Widow</option>
                    </select>
                </div>

                <div class="field grid-1">
                    <label>Current Residential Address *</label>
                    <textarea name="address" id="address" required><?= h($_POST['address'] ?? '') ?></textarea>
                </div>

                <div class="field grid-1">
                    <label for="permanentAddress">Permanent Address</label>
                    <div class="same-wrap">
                        <input type="checkbox" id="sameAddress">
                        <span>Same as current address</span>
                    </div>
                    <textarea name="permanent_address" id="permanentAddress"><?= h($_POST['permanent_address'] ?? '') ?></textarea>
                </div>

                <div class="field">
                    <label>State</label>
                    <select name="state" id="state">
                        <option value="">Select state</option>
                        <?php foreach ($states as $st => $cities): ?>
                            <option value="<?= h($st) ?>" <?= (($_POST['state'] ?? '') === $st) ? 'selected' : '' ?>><?= h($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>City</label>
                    <select name="city" id="city" data-selected="<?= h($_POST['city'] ?? '') ?>">
                        <option value="">Select city</option>
                    </select>
                </div>

                <div class="field">
                    <label>Pincode</label>
                    <input type="text" name="pincode" value="<?= h($_POST['pincode'] ?? '') ?>">
                </div>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Job Details</h3>
            <div class="grid">
                <div class="field">
                    <label>Experience Type *</label>
                    <select name="experience_type" id="experience_type" required>
                        <option value="">Select</option>
                        <option value="Fresher" <?= (($_POST['experience_type'] ?? '') === 'Fresher') ? 'selected' : '' ?>>Fresher</option>
                        <option value="Experienced" <?= (($_POST['experience_type'] ?? '') === 'Experienced') ? 'selected' : '' ?>>Experienced</option>
                    </select>
                </div>

                <div class="field">
                    <label>Apply Position *</label>
                    <select name="position_applied" required>
                        <option value="">Select position</option>
                        <?php foreach ($positionOptions as $pos): ?>
                            <option value="<?= h($pos) ?>" <?= (($_POST['position_applied'] ?? '') === $pos) ? 'selected' : '' ?>><?= h($pos) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Department</label>
                    <input type="text" name="department" value="<?= h($_POST['department'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Highest Qualification</label>
                    <input type="text" name="highest_qualification" value="<?= h($_POST['highest_qualification'] ?? '') ?>">
                </div>
            </div>

            <div class="grid" id="experienceFields">
                <div class="field">
                    <label>Total Experience</label>
                    <input type="text" name="total_experience" value="<?= h($_POST['total_experience'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Current Company</label>
                    <input type="text" name="current_company" value="<?= h($_POST['current_company'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Current Salary</label>
                    <input type="text" name="current_salary" value="<?= h($_POST['current_salary'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Expected Salary</label>
                    <input type="text" name="expected_salary" value="<?= h($_POST['expected_salary'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Scheduled Exam in Coming Months</label>
                    <input type="text" name="scheduled_exam" value="<?= h($_POST['scheduled_exam'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Notice Period</label>
                    <input type="text" name="notice_period" value="<?= h($_POST['notice_period'] ?? '') ?>">
                </div>

                <div class="field">
                    <label>Notice Period Specify</label>
                    <input type="text" name="notice_period_specify" value="<?= h($_POST['notice_period_specify'] ?? '') ?>">
                </div>

                <div class="field grid-1">
                    <label>Interest In Field</label>
                    <textarea name="interest_in_field"><?= h($_POST['interest_in_field'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="field grid-1">
                <label>Career Goals</label>
                <textarea name="career_goals"><?= h($_POST['career_goals'] ?? '') ?></textarea>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Academic Details</h3>
            <div class="table-block">
                <table>
                    <thead>
                    <tr>
                        <th>Level</th>
                        <th>Board / University</th>
                        <th>Institute</th>
                        <th>Subject/Course</th>
                        <th>Passing Year</th>
                        <th>Percentage</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $levels = ['10TH', '12TH', 'GRADUATION', 'POST GRADUATION', 'DIPLOMA'];
                    foreach ($levels as $idx => $level):
                    ?>
                        <tr>
                            <td><input type="text" name="academic_level[]" value="<?= h($_POST['academic_level'][$idx] ?? $level) ?>" readonly></td>
                            <td>
                                <?php if ($level === '10TH' || $level === '12TH'): ?>
                                    <select name="academic_board[]">
                                        <option value="">Select board</option>
                                        <?php foreach ($boards as $board): ?>
                                            <option value="<?= h($board) ?>" <?= (($_POST['academic_board'][$idx] ?? '') === $board) ? 'selected' : '' ?>><?= h($board) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="text" name="academic_board[]" value="<?= h($_POST['academic_board'][$idx] ?? '') ?>">
                                <?php endif; ?>
                            </td>
                            <td><input type="text" name="academic_institute[]" value="<?= h($_POST['academic_institute'][$idx] ?? '') ?>"></td>
                            <td><input type="text" name="academic_subject[]" value="<?= h($_POST['academic_subject'][$idx] ?? '') ?>"></td>
                            <td><input type="text" name="academic_passing_year[]" value="<?= h($_POST['academic_passing_year'][$idx] ?? '') ?>"></td>
                            <td><input type="text" name="academic_percentage[]" value="<?= h($_POST['academic_percentage'][$idx] ?? '') ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Work Experience</h3>
            <div class="table-block">
                <table>
                    <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Designation</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Salary (CTC)</th>
                        <th>Reason for Leaving</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="experienceRows">
                    <?php
                    $expRows = max(1, count($_POST['exp_company_name'] ?? []));
                    for ($i = 0; $i < $expRows; $i++):
                    ?>
                        <tr>
                            <td><input type="text" name="exp_company_name[]" value="<?= h($_POST['exp_company_name'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="exp_designation[]" value="<?= h($_POST['exp_designation'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="exp_from[]" value="<?= h($_POST['exp_from'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="exp_to[]" value="<?= h($_POST['exp_to'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="exp_salary_ctc[]" value="<?= h($_POST['exp_salary_ctc'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="exp_reason_leaving[]" value="<?= h($_POST['exp_reason_leaving'][$i] ?? '') ?>"></td>
                            <td>
                                <?php if ($i === 0): ?>
                                    <button type="button" class="btn btn-add" id="addExperienceRow">+</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-remove remove-row">×</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Last Employer References</h3>
            <div class="table-block">
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
                    <?php for ($i = 0; $i < 2; $i++): ?>
                        <tr>
                            <td><input type="text" name="reference_name[]" value="<?= h($_POST['reference_name'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="reference_designation[]" value="<?= h($_POST['reference_designation'][$i] ?? '') ?>"></td>
                            <td><input type="email" name="reference_email[]" value="<?= h($_POST['reference_email'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="reference_mobile[]" value="<?= h($_POST['reference_mobile'][$i] ?? '') ?>"></td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Family Details</h3>
            <div class="table-block">
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Relation</th>
                        <th>Occupation</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="familyRows">
                    <?php
                    $familyRows = max(1, count($_POST['family_name'] ?? []));
                    for ($i = 0; $i < $familyRows; $i++):
                    ?>
                        <tr>
                            <td><input type="text" name="family_name[]" value="<?= h($_POST['family_name'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="family_relation[]" value="<?= h($_POST['family_relation'][$i] ?? '') ?>"></td>
                            <td><input type="text" name="family_occupation[]" value="<?= h($_POST['family_occupation'][$i] ?? '') ?>"></td>
                            <td>
                                <?php if ($i === 0): ?>
                                    <button type="button" class="btn btn-add" id="addFamilyRow">+</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-remove remove-row">×</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Additional Information</h3>
            <div class="grid">
                <div class="field">
                    <label>Source Type</label>
                    <select name="source_type">
                        <option value="walkin" <?= (($_POST['source_type'] ?? 'walkin') === 'walkin') ? 'selected' : '' ?>>Walk In</option>
                        <option value="reference" <?= (($_POST['source_type'] ?? '') === 'reference') ? 'selected' : '' ?>>Internal Reference</option>
                        <option value="link" <?= (($_POST['source_type'] ?? '') === 'link') ? 'selected' : '' ?>>Recruiter</option>
                        <option value="qr" <?= (($_POST['source_type'] ?? '') === 'qr') ? 'selected' : '' ?>>Rejoin</option>
                    </select>
                </div>

                <div class="field">
                    <label>Source Reference Name</label>
                    <input type="text" name="source_reference_name" value="<?= h($_POST['source_reference_name'] ?? '') ?>">
                </div>

                <div class="field grid-1">
                    <label>Strengths</label>
                    <textarea name="strengths"><?= h($_POST['strengths'] ?? '') ?></textarea>
                </div>

                <div class="field grid-1">
                    <label>Weakness</label>
                    <textarea name="weakness"><?= h($_POST['weakness'] ?? '') ?></textarea>
                </div>

                <div class="field grid-1">
                    <label>Hobbies</label>
                    <textarea name="hobbies"><?= h($_POST['hobbies'] ?? '') ?></textarea>
                </div>

                <div class="field grid-1">
                    <label>Computer Knowledge</label>
                    <textarea name="computer_knowledge"><?= h($_POST['computer_knowledge'] ?? '') ?></textarea>
                </div>

                <div class="field grid-1">
                    <label>Medical / Health Issue</label>
                    <textarea name="medical_issue"><?= h($_POST['medical_issue'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Language Proficiency</h3>
            <div class="table-block">
                <table>
                    <thead>
                    <tr>
                        <th>Language</th>
                        <th>Read</th>
                        <th>Write</th>
                        <th>Speak</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody id="languageRows">
                    <?php
                    $languageRows = max(1, count($_POST['language_name'] ?? []));
                    for ($i = 0; $i < $languageRows; $i++):
                    ?>
                        <tr>
                            <td><input type="text" name="language_name[]" value="<?= h($_POST['language_name'][$i] ?? '') ?>"></td>
                            <td>
                                <select name="language_read[]">
                                    <option value="">Select</option>
                                    <option value="Yes" <?= (($_POST['language_read'][$i] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
                                    <option value="No" <?= (($_POST['language_read'][$i] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
                                </select>
                            </td>
                            <td>
                                <select name="language_write[]">
                                    <option value="">Select</option>
                                    <option value="Yes" <?= (($_POST['language_write'][$i] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
                                    <option value="No" <?= (($_POST['language_write'][$i] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
                                </select>
                            </td>
                            <td>
                                <select name="language_speak[]">
                                    <option value="">Select</option>
                                    <option value="Yes" <?= (($_POST['language_speak'][$i] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
                                    <option value="No" <?= (($_POST['language_speak'][$i] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
                                </select>
                            </td>
                            <td>
                                <?php if ($i === 0): ?>
                                    <button type="button" class="btn btn-add" id="addLanguageRow">+</button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-remove remove-row">×</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-24"></div>
            <h3 class="section-title">Uploads</h3>
            <div class="grid">
                <div class="field">
                    <label>Resume *</label>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>

                <div class="field">
                    <label>Self Photo *</label>
                    <input type="file" name="photo" accept=".jpg,.jpeg,.png">
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Submit Application</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>

<script>
const statesData = <?= json_encode($states, JSON_UNESCAPED_UNICODE) ?>;

function populateCities() {
    const stateEl = document.getElementById('state');
    const cityEl = document.getElementById('city');
    const selectedCity = cityEl.getAttribute('data-selected') || '';
    const state = stateEl.value;
    const cities = statesData[state] || [];

    cityEl.innerHTML = '<option value="">Select city</option>';
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        if (city === selectedCity) option.selected = true;
        cityEl.appendChild(option);
    });
}

document.getElementById('state').addEventListener('change', function () {
    document.getElementById('city').setAttribute('data-selected', '');
    populateCities();
});
populateCities();

const dob = document.getElementById('dob');
const age = document.getElementById('age');
function calculateAge() {
    if (!dob.value) {
        age.value = '';
        return;
    }
    const birthDate = new Date(dob.value);
    const today = new Date();
    let years = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        years--;
    }
    age.value = years >= 0 ? years : '';
}
dob.addEventListener('change', calculateAge);
calculateAge();

const sameAddress = document.getElementById('sameAddress');
const address = document.getElementById('address');
const permanentAddress = document.getElementById('permanentAddress');

sameAddress.addEventListener('change', function () {
    if (this.checked) {
        permanentAddress.value = address.value;
        permanentAddress.style.display = 'none';
    } else {
        permanentAddress.style.display = 'block';
    }
});

address.addEventListener('input', function () {
    if (sameAddress.checked) {
        permanentAddress.value = address.value;
    }
});

const experienceType = document.getElementById('experience_type');
const experienceFields = document.getElementById('experienceFields');

function toggleExperienceFields() {
    if (experienceType.value === 'Fresher') {
        experienceFields.classList.add('hidden');
    } else {
        experienceFields.classList.remove('hidden');
    }
}
experienceType.addEventListener('change', toggleExperienceFields);
toggleExperienceFields();

function makeRemoveButton() {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-remove remove-row';
    btn.textContent = '×';
    return btn;
}

document.getElementById('addExperienceRow').addEventListener('click', function () {
    const tbody = document.getElementById('experienceRows');
    if (tbody.querySelectorAll('tr').length >= 10) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="exp_company_name[]"></td>
        <td><input type="text" name="exp_designation[]"></td>
        <td><input type="text" name="exp_from[]"></td>
        <td><input type="text" name="exp_to[]"></td>
        <td><input type="text" name="exp_salary_ctc[]"></td>
        <td><input type="text" name="exp_reason_leaving[]"></td>
        <td></td>
    `;
    tr.querySelector('td:last-child').appendChild(makeRemoveButton());
    tbody.appendChild(tr);
});

document.getElementById('addFamilyRow').addEventListener('click', function () {
    const tbody = document.getElementById('familyRows');
    if (tbody.querySelectorAll('tr').length >= 10) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="family_name[]"></td>
        <td><input type="text" name="family_relation[]"></td>
        <td><input type="text" name="family_occupation[]"></td>
        <td></td>
    `;
    tr.querySelector('td:last-child').appendChild(makeRemoveButton());
    tbody.appendChild(tr);
});

document.getElementById('addLanguageRow').addEventListener('click', function () {
    const tbody = document.getElementById('languageRows');
    if (tbody.querySelectorAll('tr').length >= 10) return;
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="language_name[]"></td>
        <td>
            <select name="language_read[]">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </td>
        <td>
            <select name="language_write[]">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </td>
        <td>
            <select name="language_speak[]">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </td>
        <td></td>
    `;
    tr.querySelector('td:last-child').appendChild(makeRemoveButton());
    tbody.appendChild(tr);
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
    }
});
</script>
</body>
</html>