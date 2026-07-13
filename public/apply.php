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
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
  $count = (int) $stmt->fetchColumn() + 1;
  return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
}

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
    $epfRegistered = post('epf_registered');
    $uanNo = post('uan_no');
    $esicRegistered = post('esic_registered');
    $ipNo = post('ip_no');
    $aadhaarNo = post('aadhaar_no');
    $panNo = post('pan_no');
    $bankAccountNo = post('bank_account_no');
    $ifscCode = post('ifsc_code');
    $hobbies = post('hobbies');
    $weeklyWorkingDays = post('weekly_working_days');
    $medicalIssue = post('medical_issue');
    $smoking = post('smoking');
    $selfVehicle = post('self_vehicle');
    $drivingLicence = post('driving_licence');

    if ($fullName === '' || $email === '' || $phone === '' || $positionApplied === '') {
      throw new Exception('Please fill all required fields.');
    }

    $resumePath = uploadFile(
      'resume',
      __DIR__ . '/uploads/resumes',
      ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']
    );

    $photoPath = uploadFile(
      'photo',
      __DIR__ . '/uploads/photos',
      ['jpg', 'jpeg', 'png']
    );

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
                strengths, weakness, epf_registered, uan_no, esic_registered, ip_no,
                aadhaar_no, pan_no, bank_account_no, ifsc_code, hobbies,
                weekly_working_days, medical_issue, smoking, self_vehicle, driving_licence,
                photo_path, resume_path, current_status, final_decision, applied_at
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, 'submitted', 'pending', NOW()
            )
        ";

    $candidateStmt = $pdo->prepare($candidateSql);
    $candidateStmt->execute([
      $applicationNo,
      $fullName,
      $email,
      $phone,
      $alternatePhone,
      $fatherHusbandName,
      $emergencyNo,
      $gender ?: null,
      $dob ?: null,
      ($age !== '' ? (int) $age : null),
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
      $sourceType ?: 'walkin',
      $sourceReferenceName ?: null,
      $scheduledExam ?: null,
      $careerGoals ?: null,
      $interestInField ?: null,
      $noticePeriod ?: null,
      $noticePeriodSpecify ?: null,
      $strengths ?: null,
      $weakness ?: null,
      $epfRegistered ?: null,
      $uanNo ?: null,
      $esicRegistered ?: null,
      $ipNo ?: null,
      $aadhaarNo ?: null,
      $panNo ?: null,
      $bankAccountNo ?: null,
      $ifscCode ?: null,
      $hobbies ?: null,
      $weeklyWorkingDays ?: null,
      $medicalIssue ?: null,
      $smoking ?: null,
      $selfVehicle ?: null,
      $drivingLicence ?: null,
      $photoPath,
      $resumePath
    ]);

    $candidateId = (int) $pdo->lastInsertId();

    if (!empty($_POST['academic_level']) && is_array($_POST['academic_level'])) {
      $acadStmt = $pdo->prepare("
                INSERT INTO candidate_academics
                (candidate_id, level_name, subject, institute, passing_year, percentage, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

      foreach ($_POST['academic_level'] as $i => $level) {
        $level = trim($level);
        $subject = trim($_POST['academic_subject'][$i] ?? '');
        $institute = trim($_POST['academic_institute'][$i] ?? '');
        $passingYear = trim($_POST['academic_passing_year'][$i] ?? '');
        $percentage = trim($_POST['academic_percentage'][$i] ?? '');

        if ($level === '' && $subject === '' && $institute === '' && $passingYear === '' && $percentage === '') {
          continue;
        }

        $acadStmt->execute([
          $candidateId,
          $level ?: null,
          $subject ?: null,
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
        $companyName = trim($companyName);
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
        $refName = trim($refName);
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
        $memberName = trim($memberName);
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
    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      background: #f7f3f7;
      color: #2c2337;
    }

    .topbar {
      background: #fff;
      border-bottom: 1px solid #e8dfe8;
      padding: 18px 24px;
    }

    .topbar-inner {
      max-width: 1180px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .brand-logo {
      font-weight: 800;
      font-size: 22px;
      color: #c02f8a;
      letter-spacing: .5px;
    }

    .brand-text h1 {
      margin: 0;
      font-size: 20px;
      color: #2f2640;
    }

    .brand-text p {
      margin: 3px 0 0;
      color: #7b7089;
      font-size: 14px;
    }

    .login-btn {
      text-decoration: none;
      border: 1px solid #ead8e7;
      color: #b43c8c;
      padding: 10px 18px;
      border-radius: 16px;
      background: #fff;
      font-weight: 700;
    }

    .page {
      max-width: 1180px;
      margin: 26px auto;
      padding: 0 18px 32px;
      display: flex;
      flex-direction: column;
      gap: 22px;
    }

    .card {
      background: #fff;
      border: 1px solid #eadfeb;
      border-radius: 22px;
      box-shadow: 0 12px 30px rgba(122, 69, 119, .08);
    }

    .intro {
      padding: 28px 24px;
      min-height: 240px;
      background: linear-gradient(180deg, #fff 0%, #fdf8fc 100%);
    }

    .pill {
      display: inline-block;
      padding: 8px 14px;
      border-radius: 999px;
      background: #f5deed;
      color: #b93a8d;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .4px;
      margin-bottom: 20px;
    }

    .intro h2 {
      margin: 0 0 14px;
      font-size: 24px;
      line-height: 1.3;
    }

    .intro p {
      margin: 0;
      font-size: 16px;
      color: #5f546d;
      line-height: 1.65;
    }

    .form-card {
      padding: 22px;
    }

    .message {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 16px;
      font-size: 14px;
    }

    .success {
      background: #e0f5e7;
      color: #17633b;
      border: 1px solid #b8e4c7
    }

    .error {
      background: #fde7ea;
      color: #9a2137;
      border: 1px solid #f5bcc7
    }

    .section-title {
      margin: 0 0 14px;
      font-size: 18px;
      color: #2f2640;
      border-bottom: 1px solid #f0e7f0;
      padding-bottom: 8px;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px 18px;
    }

    .grid-1 {
      grid-column: 1 / -1;
    }

    .field label {
      display: block;
      margin-bottom: 7px;
      font-size: 14px;
      font-weight: 700;
      color: #372d48;
    }

    .field input,
    .field select,
    .field textarea {
      width: 100%;
      border: 1px solid #e3dbe5;
      background: #fff;
      border-radius: 14px;
      padding: 12px 14px;
      font-size: 14px;
      color: #2b2436;
      outline: none;
    }

    .field textarea {
      min-height: 96px;
      resize: vertical;
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      border-color: #c34a96;
      box-shadow: 0 0 0 3px rgba(195, 74, 150, .12);
    }

    .table-block {
      margin-top: 14px;
      overflow: auto;
      border: 1px solid #eee3ee;
      border-radius: 14px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 760px;
      background: #fff;
    }

    th,
    td {
      border-bottom: 1px solid #f1e8f1;
      padding: 10px;
      text-align: left;
      vertical-align: top;
    }

    th {
      background: #fbf7fb;
      color: #554a66;
      font-size: 13px;
    }

    td input {
      width: 100%;
      border: 1px solid #e3dbe5;
      border-radius: 10px;
      padding: 10px 11px;
      font-size: 13px;
    }

    .actions {
      margin-top: 24px;
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .btn {
      border: none;
      border-radius: 14px;
      padding: 13px 20px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-primary {
      background: linear-gradient(135deg, #c5368f, #a3297c);
      color: #fff;
      box-shadow: 0 10px 22px rgba(181, 47, 129, .22);
    }

    .btn-secondary {
      background: #f6ebf3;
      color: #9f2f7b;
    }

    .muted {
      color: #7a7087;
      font-size: 13px;
    }

    .mt-24 {
      margin-top: 24px
    }

    @media (max-width: 980px) {
      .page {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 640px) {
      .grid {
        grid-template-columns: 1fr;
      }

      .form-card {
        padding: 16px;
      }

      .intro {
        min-height: auto;
      }
    }

    .intro,
.form-card{
    width:100%;
}
  </style>
</head>

<body>
  <div class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <div class="brand-logo">UNIRE</div>
        <div class="brand-text">
          <h1>UNIRE Recruitment Workflow</h1>
          <p>Travel back-office hiring panel</p>
        </div>
      </div>
      <a href="login.php" class="login-btn">Login</a>
    </div>
  </div>

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

      <form method="POST" enctype="multipart/form-data">
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
            <label>Emergency No</label>
            <input type="text" name="emergency_no" value="<?= h($_POST['emergency_no'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= h($_POST['dob'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Age</label>
            <input type="number" name="age" value="<?= h($_POST['age'] ?? '') ?>">
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
              <option value="Married" <?= (($_POST['marital_status'] ?? '') === 'Married') ? 'selected' : '' ?>>Married
              </option>
              <option value="Single" <?= (($_POST['marital_status'] ?? '') === 'Single') ? 'selected' : '' ?>>Single
              </option>
              <option value="Divorced" <?= (($_POST['marital_status'] ?? '') === 'Divorced') ? 'selected' : '' ?>>Divorced
              </option>
              <option value="Widow" <?= (($_POST['marital_status'] ?? '') === 'Widow') ? 'selected' : '' ?>>Widow</option>
            </select>
          </div>

          <div class="field grid-1">
            <label>Current Residential Address</label>
            <textarea name="address"><?= h($_POST['address'] ?? '') ?></textarea>
          </div>

          <div class="field grid-1">
            <label>Permanent Address</label>
            <textarea name="permanent_address"><?= h($_POST['permanent_address'] ?? '') ?></textarea>
          </div>

          <div class="field">
            <label>City</label>
            <input type="text" name="city" value="<?= h($_POST['city'] ?? '') ?>">
          </div>

          <div class="field">
            <label>State</label>
            <input type="text" name="state" value="<?= h($_POST['state'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Pincode</label>
            <input type="text" name="pincode" value="<?= h($_POST['pincode'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Highest Qualification</label>
            <input type="text" name="highest_qualification" value="<?= h($_POST['highest_qualification'] ?? '') ?>">
          </div>
        </div>

        <div class="mt-24"></div>
        <h3 class="section-title">Job Details</h3>
        <div class="grid">
          <div class="field">
            <label>Apply Position *</label>
            <input type="text" name="position_applied" value="<?= h($_POST['position_applied'] ?? '') ?>" required>
          </div>

          <div class="field">
            <label>Department</label>
            <input type="text" name="department" value="<?= h($_POST['department'] ?? '') ?>">
          </div>

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
            <input type="text" name="notice_period" value="<?= h($_POST['notice_period'] ?? '') ?>"
              placeholder="Immediate / 15 days / 30 days">
          </div>

          <div class="field grid-1">
            <label>Specify Notice Period (if any)</label>
            <input type="text" name="notice_period_specify" value="<?= h($_POST['notice_period_specify'] ?? '') ?>">
          </div>

          <div class="field grid-1">
            <label>Career Goals</label>
            <textarea name="career_goals"><?= h($_POST['career_goals'] ?? '') ?></textarea>
          </div>

          <div class="field grid-1">
            <label>Interest in Field</label>
            <textarea name="interest_in_field"><?= h($_POST['interest_in_field'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="mt-24"></div>
        <h3 class="section-title">Academic Details</h3>
        <div class="table-block">
          <table>
            <thead>
              <tr>
                <th>Level</th>
                <th>Subject</th>
                <th>School / College / Institute</th>
                <th>Passing Year</th>
                <th>Percentage</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $levels = ['10th', '12th', 'Graduation', 'Post Graduation', 'Diploma'];
              foreach ($levels as $idx => $level):
                ?>
                <tr>
                  <td>
                    <input type="text" name="academic_level[]" value="<?= h($_POST['academic_level'][$idx] ?? $level) ?>">
                  </td>
                  <td>
                    <input type="text" name="academic_subject[]" value="<?= h($_POST['academic_subject'][$idx] ?? '') ?>">
                  </td>
                  <td>
                    <input type="text" name="academic_institute[]"
                      value="<?= h($_POST['academic_institute'][$idx] ?? '') ?>">
                  </td>
                  <td>
                    <input type="text" name="academic_passing_year[]"
                      value="<?= h($_POST['academic_passing_year'][$idx] ?? '') ?>">
                  </td>
                  <td>
                    <input type="text" name="academic_percentage[]"
                      value="<?= h($_POST['academic_percentage'][$idx] ?? '') ?>">
                  </td>
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
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < 4; $i++): ?>
                <tr>
                  <td><input type="text" name="exp_company_name[]" value="<?= h($_POST['exp_company_name'][$i] ?? '') ?>">
                  </td>
                  <td><input type="text" name="exp_designation[]" value="<?= h($_POST['exp_designation'][$i] ?? '') ?>">
                  </td>
                  <td><input type="text" name="exp_from[]" value="<?= h($_POST['exp_from'][$i] ?? '') ?>"></td>
                  <td><input type="text" name="exp_to[]" value="<?= h($_POST['exp_to'][$i] ?? '') ?>"></td>
                  <td><input type="text" name="exp_salary_ctc[]" value="<?= h($_POST['exp_salary_ctc'][$i] ?? '') ?>">
                  </td>
                  <td><input type="text" name="exp_reason_leaving[]"
                      value="<?= h($_POST['exp_reason_leaving'][$i] ?? '') ?>"></td>
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
              <?php for ($i = 0; $i < 3; $i++): ?>
                <tr>
                  <td><input type="text" name="reference_name[]" value="<?= h($_POST['reference_name'][$i] ?? '') ?>">
                  </td>
                  <td><input type="text" name="reference_designation[]"
                      value="<?= h($_POST['reference_designation'][$i] ?? '') ?>"></td>
                  <td><input type="email" name="reference_email[]" value="<?= h($_POST['reference_email'][$i] ?? '') ?>">
                  </td>
                  <td><input type="text" name="reference_mobile[]" value="<?= h($_POST['reference_mobile'][$i] ?? '') ?>">
                  </td>
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
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < 4; $i++): ?>
                <tr>
                  <td><input type="text" name="family_name[]" value="<?= h($_POST['family_name'][$i] ?? '') ?>"></td>
                  <td><input type="text" name="family_relation[]" value="<?= h($_POST['family_relation'][$i] ?? '') ?>">
                  </td>
                  <td><input type="text" name="family_occupation[]"
                      value="<?= h($_POST['family_occupation'][$i] ?? '') ?>"></td>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>

        <div class="mt-24"></div>
        <h3 class="section-title">Other Information</h3>
        <div class="grid">
          <div class="field">
            <label>Source Type</label>
            <select name="source_type">
              <option value="walkin" <?= (($_POST['source_type'] ?? 'walkin') === 'walkin') ? 'selected' : '' ?>>Walk In
              </option>
              <option value="qr" <?= (($_POST['source_type'] ?? '') === 'qr') ? 'selected' : '' ?>>QR</option>
              <option value="link" <?= (($_POST['source_type'] ?? '') === 'link') ? 'selected' : '' ?>>Link</option>
              <option value="reference" <?= (($_POST['source_type'] ?? '') === 'reference') ? 'selected' : '' ?>>Reference
              </option>
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

          <div class="field">
            <label>EPF Registered</label>
            <select name="epf_registered">
              <option value="">Select</option>
              <option value="Yes" <?= (($_POST['epf_registered'] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
              <option value="No" <?= (($_POST['epf_registered'] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
            </select>
          </div>

          <div class="field">
            <label>UAN No</label>
            <input type="text" name="uan_no" value="<?= h($_POST['uan_no'] ?? '') ?>">
          </div>

          <div class="field">
            <label>ESIC Registered</label>
            <select name="esic_registered">
              <option value="">Select</option>
              <option value="Yes" <?= (($_POST['esic_registered'] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
              <option value="No" <?= (($_POST['esic_registered'] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
            </select>
          </div>

          <div class="field">
            <label>IP No</label>
            <input type="text" name="ip_no" value="<?= h($_POST['ip_no'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Aadhaar Number</label>
            <input type="text" name="aadhaar_no" value="<?= h($_POST['aadhaar_no'] ?? '') ?>">
          </div>

          <div class="field">
            <label>PAN</label>
            <input type="text" name="pan_no" value="<?= h($_POST['pan_no'] ?? '') ?>">
          </div>

          <div class="field">
            <label>Bank Account No</label>
            <input type="text" name="bank_account_no" value="<?= h($_POST['bank_account_no'] ?? '') ?>">
          </div>

          <div class="field">
            <label>IFSC</label>
            <input type="text" name="ifsc_code" value="<?= h($_POST['ifsc_code'] ?? '') ?>">
          </div>

          <div class="field grid-1">
            <label>Hobbies</label>
            <textarea name="hobbies"><?= h($_POST['hobbies'] ?? '') ?></textarea>
          </div>

          <div class="field">
            <label>Weekly Working Days</label>
            <select name="weekly_working_days">
              <option value="">Select</option>
              <option value="5" <?= (($_POST['weekly_working_days'] ?? '') === '5') ? 'selected' : '' ?>>5 Days</option>
              <option value="6" <?= (($_POST['weekly_working_days'] ?? '') === '6') ? 'selected' : '' ?>>6 Days</option>
            </select>
          </div>

          <div class="field">
            <label>Smoking</label>
            <select name="smoking">
              <option value="">Select</option>
              <option value="Yes" <?= (($_POST['smoking'] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
              <option value="No" <?= (($_POST['smoking'] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
            </select>
          </div>

          <div class="field">
            <label>Self Vehicle</label>
            <select name="self_vehicle">
              <option value="">Select</option>
              <option value="Yes" <?= (($_POST['self_vehicle'] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
              <option value="No" <?= (($_POST['self_vehicle'] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
            </select>
          </div>

          <div class="field">
            <label>Driving Licence</label>
            <select name="driving_licence">
              <option value="">Select</option>
              <option value="Yes" <?= (($_POST['driving_licence'] ?? '') === 'Yes') ? 'selected' : '' ?>>Yes</option>
              <option value="No" <?= (($_POST['driving_licence'] ?? '') === 'No') ? 'selected' : '' ?>>No</option>
            </select>
          </div>

          <div class="field grid-1">
            <label>Medical / Health Issue</label>
            <textarea name="medical_issue"><?= h($_POST['medical_issue'] ?? '') ?></textarea>
          </div>

          <div class="field">
            <label>Resume</label>
            <input type="file" name="resume" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <div class="muted">Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG</div>
          </div>

          <div class="field">
            <label>Passport-size Photo</label>
            <input type="file" name="photo" accept=".jpg,.jpeg,.png">
            <div class="muted">Allowed: JPG, JPEG, PNG</div>
          </div>
        </div>

        <div class="actions">
          <button type="submit" class="btn btn-primary">Submit Application</button>
          <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
      </form>
    </div>
  </div>
</body>

</html>