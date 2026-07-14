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

    if ($noticePeriod !== '' && !ctype_digit((string) $noticePeriod)) {
      throw new Exception('Notice period must be a number.');
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
      $noticePeriod !== '' ? $noticePeriod : null,
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
                (candidate_id, level_name, board_name, subject, institute, passing_year, percentage, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

      foreach ($_POST['academic_level'] as $i => $level) {
        $level = trim($level);
        $board = trim($_POST['academic_board'][$i] ?? '');
        $subject = trim($_POST['academic_subject'][$i] ?? '');
        $institute = trim($_POST['academic_institute'][$i] ?? '');
        $passingYear = trim($_POST['academic_passing_year'][$i] ?? '');
        $percentage = trim($_POST['academic_percentage'][$i] ?? '');

        if ($level === '' && $board === '' && $subject === '' && $institute === '' && $passingYear === '' && $percentage === '') {
          continue;
        }

        $acadStmt->execute([
          $candidateId,
          $level ?: null,
          $board ?: null,
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
  'Rajasthan' => ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Ajmer', 'Alwar', 'Bikaner', 'Bharatpur', 'Bhilwara', 'Sikar', 'Pali', 'Sri Ganganagar', 'Hanumangarh', 'Churu', 'Jhunjhunu', 'Nagaur', 'Barmer', 'Jaisalmer', 'Sawai Madhopur', 'Tonk', 'Dausa', 'Dholpur', 'Karauli', 'Bundi', 'Baran', 'Jhalawar', 'Chittorgarh', 'Pratapgarh', 'Rajsamand', 'Dungarpur', 'Banswara', 'Sirohi', 'Jalore', 'Didwana', 'Kuchaman City', 'Kishangarh', 'Mount Abu', 'Phalodi', 'Sujangarh', 'Anupgarh', 'Kekri'],
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
  'Puducherry' => ['Puducherry'],
  'Andaman and Nicobar Islands' => ['Port Blair'],
  'Dadra and Nagar Haveli and Daman and Diu' => ['Daman', 'Silvassa']
];

$boards = [
  'CBSE (Central)',
  'RBSE / BSER (Rajasthan)',
  'CISCE (Central)',
  'NIOS (Central)',
  'UPMSP (Uttar Pradesh)',
  'AHSEC (Assam)',
  'BIEAP (Andhra Pradesh)',
  'BSEAP (Andhra Pradesh)',
  'BSEB (Bihar)',
  'CGBSE (Chhattisgarh)',
  'GBSHSE (Goa)',
  'GSEB (Gujarat)',
  'HBSE / BSEH (Haryana)',
  'HPBOSE (Himachal Pradesh)',
  'JAKBOST / JKBOSE (Jammu & Kashmir)',
  'JAC (Jharkhand)',
  'KSEEB / KSEAB (Karnataka)',
  'DHSE (Kerala)',
  'KBPE (Kerala)',
  'MPBSE (Madhya Pradesh)',
  'MSBSHSE (Maharashtra)',
  'BOSEM (Manipur)',
  'COHSEM (Manipur)',
  'MBOSE (Meghalaya)',
  'MBSE (Mizoram)',
  'NBSE (Nagaland)',
  'CHSE (Odisha)',
  'BSE (Odisha)',
  'PSEB (Punjab)',
  'SBSE (Sikkim)',
  'TNBSE / TNDGE (Tamil Nadu)',
  'TSBIE (Telangana)',
  'BSET (Telangana)',
  'TBSE (Tripura)',
  'UBSE (Uttarakhand)',
  'WBBSE (West Bengal)',
  'WBCHSE (West Bengal)'
];

$positionOptions = ['Process Associate', 'Operations', 'Accounts', 'Reservation'];

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
      color: #2c2337
    }

    .topbar {
      background: #fff;
      border-bottom: 1px solid #e8dfe8;
      padding: 18px 24px
    }

    .topbar-inner {
      max-width: 1180px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 14px
    }

    .brand-logo {
      font-weight: 800;
      font-size: 22px;
      color: #c02f8a;
      letter-spacing: .5px
    }

    .brand-text h1 {
      margin: 0;
      font-size: 20px;
      color: #2f2640
    }

    .brand-text p {
      margin: 3px 0 0;
      color: #7b7089;
      font-size: 14px
    }

    .login-btn {
      text-decoration: none;
      border: 1px solid #ead8e7;
      color: #b43c8c;
      padding: 10px 18px;
      border-radius: 16px;
      background: #fff;
      font-weight: 700
    }

    .page {
      max-width: 1180px;
      margin: 26px auto;
      padding: 0 18px 32px;
      display: flex;
      flex-direction: column;
      gap: 22px
    }

    .card {
      background: #fff;
      border: 1px solid #eadfeb;
      border-radius: 22px;
      box-shadow: 0 12px 30px rgba(122, 69, 119, .08)
    }

    .intro {
      padding: 28px 24px;
      min-height: 240px;
      background: linear-gradient(180deg, #fff 0%, #fdf8fc 100%)
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
      margin-bottom: 20px
    }

    .intro h2 {
      margin: 0 0 14px;
      font-size: 24px;
      line-height: 1.3
    }

    .intro p {
      margin: 0;
      font-size: 16px;
      color: #5f546d;
      line-height: 1.65
    }

    .form-card {
      padding: 22px
    }

    .message {
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 16px;
      font-size: 14px
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
      padding-bottom: 8px
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px 18px
    }

    .grid-1 {
      grid-column: 1/-1
    }

    .field label {
      display: block;
      margin-bottom: 7px;
      font-size: 14px;
      font-weight: 700;
      color: #372d48
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
      outline: none
    }

    .field textarea {
      min-height: 96px;
      resize: vertical
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
      border-color: #c34a96;
      box-shadow: 0 0 0 3px rgba(195, 74, 150, .12)
    }

    .table-block {
      margin-top: 14px;
      overflow: auto;
      border: 1px solid #eee3ee;
      border-radius: 14px
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 760px;
      background: #fff
    }

    th,
    td {
      border-bottom: 1px solid #f1e8f1;
      padding: 10px;
      text-align: left;
      vertical-align: top
    }

    th {
      background: #fbf7fb;
      color: #554a66;
      font-size: 13px
    }

    td input,
    td select {
      width: 100%;
      border: 1px solid #e3dbe5;
      border-radius: 10px;
      padding: 10px 11px;
      font-size: 13px
    }

    .actions {
      margin-top: 24px;
      display: flex;
      gap: 12px;
      flex-wrap: wrap
    }

    .btn {
      border: none;
      border-radius: 14px;
      padding: 13px 20px;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer
    }

    .btn-primary {
      background: linear-gradient(135deg, #c5368f, #a3297c);
      color: #fff;
      box-shadow: 0 10px 22px rgba(181, 47, 129, .22)
    }

    .btn-secondary {
      background: #f6ebf3;
      color: #9f2f7b
    }

    .btn-add {
      background: #fff;
      border: 1px dashed #c34a96;
      color: #c34a96
    }

    .muted {
      color: #7a7087;
      font-size: 13px
    }

    .mt-24 {
      margin-top: 24px
    }

    .same-wrap {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      margin: 8px 0 10px;
      color: #5f546d;
      font-size: 14px;
      font-weight: 600;
    }

    .same-wrap input[type="checkbox"] {
      width: 18px;
      height: 18px;
      margin: 0;
      flex: 0 0 auto;
    }

    .same-wrap span {
      line-height: 1;
    }

    @media (max-width:640px) {
      .grid {
        grid-template-columns: 1fr
      }

      .form-card {
        padding: 16px
      }

      .intro {
        min-height: auto
      }
    }

    .intro,
    .form-card {
      width: 100%
    }

    #experienceFields {
      margin-top: 18px;
    }

    #experienceFields .field {
      margin-top: 6px;
    }

    /* .required {
    color: red;
    font-weight: bold; */
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
      <p>Fill your details, upload resume and passport-size photo, and submit your application for <strong>Recruiter
          Review.</strong></p>
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
            <label>Father / Husband Name *</label>
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
            <label>Alternate Phone *</label>
            <input type="text" name="alternate_phone" value="<?= h($_POST['alternate_phone'] ?? '') ?>" required>
          </div>

          <div class="field">
            <label>Aadhaar Number *</label>
            <input type="text" name="aadhaar_no" value="<?= h($_POST['aadhaar_no'] ?? '') ?>" required>
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
            <label>Current Residential Address *</label>
            <textarea name="address" id="address" required><?= h($_POST['address'] ?? '') ?></textarea>
          </div>

          <div class="field grid-1">
            <label for="permanentAddress">Permanent Address</label>

            <div class="same-wrap">
              <input type="checkbox" id="sameAddress">
              <span>Same as current address</span>
            </div>

            <textarea name="permanent_address"
              id="permanentAddress"><?= h($_POST['permanent_address'] ?? '') ?></textarea>
          </div>

          <div class="field">
            <label>State</label>
            <select name="state" id="state">
              <option value="">Select state</option>
              <?php foreach ($states as $st => $cities): ?>
                <option value="<?= h($st) ?>" <?= (($_POST['state'] ?? '') === $st) ? 'selected' : '' ?>><?= h($st) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>City</label>
            <select name="city" id="city">
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
              <option value="Fresher" <?= (($_POST['experience_type'] ?? '') === 'Fresher') ? 'selected' : '' ?>>Fresher
              </option>
              <option value="Experienced" <?= (($_POST['experience_type'] ?? '') === 'Experienced') ? 'selected' : '' ?>>
                Experienced</option>
            </select>
          </div>

          <div class="field">
            <label>Apply Position *</label>
            <select name="position_applied" required>
              <option value="">Select position</option>
              <?php foreach ($positionOptions as $pos): ?>
                <option value="<?= h($pos) ?>" <?= (($_POST['position_applied'] ?? '') === $pos) ? 'selected' : '' ?>>
                  <?= h($pos) ?>
                </option>
              <?php endforeach; ?>
            </select>
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
            <input type="number" name="notice_period" min="0" step="1" value="<?= h($_POST['notice_period'] ?? '') ?>"
              placeholder="Days">
          </div>
        </div>

        <div class="field grid-1">
          <label>Career Goals</label>
          <textarea name="career_goals"><?= h($_POST['career_goals'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="mt-24"></div>
    <h3 class="section-title">Academic Details</h3>
    <div class="table-block">
      <table>
        <thead>
          <tr>
            <th>Level</th>
            <th>Board</th>
            <th>Subject/Course</th>
            <!-- <th>School / College / Institute</th> -->
            <th>Passing Year</th>
            <th>Percentage</th>
          </tr>
        </thead>
        <tbody id="academicRows">
          <?php
          $levels = ['10th', '12th', 'Graduation', 'Post Graduation', 'Diploma'];
          foreach ($levels as $idx => $level):
            ?>
            <tr>
              <td><input type="text" name="academic_level[]" value="<?= h($_POST['academic_level'][$idx] ?? $level) ?>">
              </td>
              <td>
                <select name="academic_board[]">
                  <option value="">Select board</option>
                  <?php foreach ($boards as $board): ?>
                    <option value="<?= h($board) ?>" <?= (($_POST['academic_board'][$idx] ?? '') === $board) ? 'selected' : '' ?>><?= h($board) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="text" name="academic_subject[]" value="<?= h($_POST['academic_subject'][$idx] ?? '') ?>">
              </td>
              <!-- <td><input type="text" name="academic_institute[]"
                  value="<?= h($_POST['academic_institute'][$idx] ?? '') ?>"></td> -->
              <td><input type="text" name="academic_passing_year[]"
                  value="<?= h($_POST['academic_passing_year'][$idx] ?? '') ?>"></td>
              <td><input type="text" name="academic_percentage[]"
                  value="<?= h($_POST['academic_percentage'][$idx] ?? '') ?>"></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-24"></div>
    <h3 class="section-title">Work Experience <small>(Current to Previous)</small></h3>
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
              <td><input type="text" name="exp_reason_leaving[]" value="<?= h($_POST['exp_reason_leaving'][$i] ?? '') ?>">
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
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="familyRows">
          <tr>
            <td><input type="text" name="family_name[]" value="<?= h($_POST['family_name'][0] ?? '') ?>"></td>
            <td><input type="text" name="family_relation[]" value="<?= h($_POST['family_relation'][0] ?? '') ?>">
            </td>
            <td><input type="text" name="family_occupation[]" value="<?= h($_POST['family_occupation'][0] ?? '') ?>">
            </td>
            <td><button type="button" class="btn btn-add" id="addFamilyRow">+</button></td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-24"></div>
    <h3 class="section-title">Additional Information</h3>
    <div class="grid">
      <div class="field">
        <label>Source Type</label>
        <select name="source_type">
          <option value="walkin" <?= (($_POST['source_type'] ?? 'walkin') === 'walkin') ? 'selected' : '' ?>>Walk In
          </option>
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

      <div class="field grid-1">
        <label>Hobbies</label>
        <textarea name="hobbies"><?= h($_POST['hobbies'] ?? '') ?></textarea>
      </div>

      <div class="field grid-1">
        <label>Medical / Health Issue</label>
        <textarea name="medical_issue"><?= h($_POST['medical_issue'] ?? '') ?></textarea>
      </div>

      <div class="field">
        <label>Resume <span class="required">*</span></label>
        <input type="file" name="resume" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
        <div class="muted">Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG</div>
      </div>

      <div class="field">
        <label>Passport-size Photo <span class="required">*</span></label>
        <input type="file" name="photo" accept=".jpg,.jpeg,.png" required>
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

  <script>
    const states = <?= json_encode($states, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const preState = <?= json_encode($_POST['state'] ?? '') ?>;
    const preCity = <?= json_encode($_POST['city'] ?? '') ?>;

    function calcAge(dob) {
      if (!dob) return '';
      const birth = new Date(dob);
      if (isNaN(birth.getTime())) return '';
      const now = new Date();
      let age = now.getFullYear() - birth.getFullYear();
      const m = now.getMonth() - birth.getMonth();
      if (m < 0 || (m === 0 && now.getDate() < birth.getDate())) age--;
      return age >= 0 ? age : '';
    }

    function fillCities(stateName, selectedCity = '') {
      const city = document.getElementById('city');
      city.innerHTML = '<option value="">Select city</option>';
      (states[stateName] || []).forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        if (name === selectedCity) opt.selected = true;
        city.appendChild(opt);
      });
    }

    document.getElementById('dob').addEventListener('change', function () {
      document.getElementById('age').value = calcAge(this.value);
    });

    document.getElementById('state').addEventListener('change', function () {
      fillCities(this.value, '');
    });

    document.getElementById('sameAddress').addEventListener('change', function () {
      const pa = document.getElementById('permanentAddress');
      const ca = document.getElementById('address');
      if (this.checked) {
        pa.value = ca.value;
        pa.style.display = 'none';
      } else {
        pa.style.display = 'block';
      }
    });

    document.getElementById('address').addEventListener('input', function () {
      const same = document.getElementById('sameAddress');
      if (same.checked) {
        document.getElementById('permanentAddress').value = this.value;
      }
    });

    document.getElementById('addFamilyRow').addEventListener('click', function () {
      const tbody = document.getElementById('familyRows');
      const currentRows = tbody.querySelectorAll('tr').length;
      if (currentRows >= 5) return;

      const row = document.createElement('tr');
      row.innerHTML = `
    <td><input type="text" name="family_name[]"></td>
    <td><input type="text" name="family_relation[]"></td>
    <td><input type="text" name="family_occupation[]"></td>
    <td><button type="button" class="btn btn-add remove-family">-</button></td>
  `;
      tbody.appendChild(row);
    });

    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('remove-family')) {
        e.target.closest('tr').remove();
      }
    });

    if (preState) fillCities(preState, preCity);

    const sameAddress = document.getElementById('sameAddress');
    const pa = document.getElementById('permanentAddress');
    if (pa.value && document.getElementById('address').value && pa.value === document.getElementById('address').value) {
      sameAddress.checked = true;
      pa.style.display = 'none';
    }

    document.getElementById('dob').dispatchEvent(new Event('change'));


    function toggleExperienceFields() {
      const expType = document.getElementById('experience_type').value;
      const expFields = document.getElementById('experienceFields');
      const inputs = expFields.querySelectorAll('input, select, textarea');

      if (expType === 'Fresher') {
        expFields.style.display = 'none';
        inputs.forEach(input => {
          input.dataset.oldRequired = input.required ? '1' : '0';
          input.required = false;
          if (input.name !== 'scheduled_exam') {
            input.value = '';
          }
        });
      } else {
        expFields.style.display = 'grid';
        inputs.forEach(input => {
          if (input.dataset.oldRequired === '1') {
            input.required = true;
          }
        });
      }
    }

    document.getElementById('experience_type').addEventListener('change', toggleExperienceFields);
    document.addEventListener('DOMContentLoaded', toggleExperienceFields);


  </script>
</body>

</html>