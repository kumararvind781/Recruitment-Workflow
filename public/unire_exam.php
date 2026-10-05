<?php
session_start();

require_once __DIR__ . '/../app/config/database.php';

$pdo = Database::connect();

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect_exam()
{
    header('Location: unire_exam.php');
    exit;
}

function exam_error($message)
{
    $_SESSION['exam_error'] = $message;
}

function get_exam_error()
{
    $error = $_SESSION['exam_error'] ?? '';
    unset($_SESSION['exam_error']);
    return $error;
}

/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['exam_csrf'])) {
    $_SESSION['exam_csrf'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['exam_csrf'];


/*
|--------------------------------------------------------------------------
| Logout / Reset
|--------------------------------------------------------------------------
*/

if (isset($_GET['logout'])) {

    unset(
        $_SESSION['exam_access_id'],
        $_SESSION['exam_candidate_id'],
        $_SESSION['exam_attempt_id'],
        $_SESSION['exam_application_no']
    );

    redirect_exam();
}


/*
|--------------------------------------------------------------------------
| STEP 1
| Username + Password
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {

    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        exam_error('Invalid request. Please try again.');
        redirect_exam();
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        exam_error('Please enter username and password.');
        redirect_exam();
    }

    $stmt = $pdo->prepare("
        SELECT
            id,
            candidate_id,
            username,
            password_hash,
            is_active
        FROM exam_access
        WHERE username = :username
        LIMIT 1
    ");

    $stmt->execute([
        ':username' => $username
    ]);

    $access = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$access) {
        exam_error('Invalid username or password.');
        redirect_exam();
    }

    if ((int)$access['is_active'] !== 1) {
        exam_error('Your exam access has been disabled.');
        redirect_exam();
    }

    if (!password_verify($password, $access['password_hash'])) {
        exam_error('Invalid username or password.');
        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Login successful
    |--------------------------------------------------------------------------
    */

    $_SESSION['exam_access_id'] = (int)$access['id'];
    $_SESSION['exam_candidate_id'] = (int)$access['candidate_id'];

    unset(
        $_SESSION['exam_attempt_id'],
        $_SESSION['exam_application_no']
    );

    redirect_exam();
}


/*
|--------------------------------------------------------------------------
| Candidate ID from login
|--------------------------------------------------------------------------
*/

$examCandidateId = $_SESSION['exam_candidate_id'] ?? null;


/*
|--------------------------------------------------------------------------
| STEP 2
| Application ID Verification
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'verify_application'
) {

    if (!$examCandidateId) {
        exam_error('Please login first.');
        redirect_exam();
    }

    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        exam_error('Invalid request. Please try again.');
        redirect_exam();
    }

    $applicationNo = trim($_POST['application_no'] ?? '');

    if ($applicationNo === '') {
        exam_error('Please enter your Application ID.');
        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Verify application belongs to logged-in candidate
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            application_no,
            full_name,
            position_applied,
            current_status
        FROM candidates
        WHERE id = :candidate_id
          AND application_no = :application_no
        LIMIT 1
    ");

    $stmt->execute([
        ':candidate_id' => $examCandidateId,
        ':application_no' => $applicationNo
    ]);

    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$candidate) {
        exam_error('Application ID does not match your exam account.');
        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Only Process Associate
    |--------------------------------------------------------------------------
    */

    if (strcasecmp(trim($candidate['position_applied']), 'Process Associate') !== 0) {
        exam_error(
            'This assessment is only available for Process Associate candidates.'
        );

        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Check if already completed
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            status
        FROM exam_attempts
        WHERE candidate_id = :candidate_id
          AND application_no = :application_no
          AND exam_type = 'process_associate'
          AND status = 'completed'
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([
        ':candidate_id' => $examCandidateId,
        ':application_no' => $applicationNo
    ]);

    $completedExam = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($completedExam) {
        exam_error(
            'You have already completed this assessment. A second attempt is not allowed.'
        );

        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Check if existing started attempt exists
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT id
        FROM exam_attempts
        WHERE candidate_id = :candidate_id
          AND application_no = :application_no
          AND exam_type = 'process_associate'
          AND status = 'started'
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute([
        ':candidate_id' => $examCandidateId,
        ':application_no' => $applicationNo
    ]);

    $existingAttempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingAttempt) {

        $_SESSION['exam_attempt_id'] = (int)$existingAttempt['id'];
        $_SESSION['exam_application_no'] = $applicationNo;

        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Get 20 random questions from Process Associate pool
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT
            id,
            question,
            option_a,
            option_b,
            option_c,
            option_d,
            correct_answer,
            marks
        FROM exam_questions
        WHERE exam_type = 'process_associate'
          AND is_active = 1
        ORDER BY RAND()
        LIMIT 20
    ");

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($questions) < 20) {
        exam_error(
            'The Process Associate assessment is not ready yet. Please contact HR.'
        );

        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Create exam attempt
    |--------------------------------------------------------------------------
    */

    $pdo->beginTransaction();

    try {

        $totalMarks = 0;

        foreach ($questions as $q) {
            $totalMarks += (float)$q['marks'];
        }

        $stmt = $pdo->prepare("
            INSERT INTO exam_attempts
            (
                candidate_id,
                application_no,
                exam_type,
                total_questions,
                total_marks,
                status,
                started_at
            )
            VALUES
            (
                :candidate_id,
                :application_no,
                'process_associate',
                20,
                :total_marks,
                'started',
                NOW()
            )
        ");

        $stmt->execute([
            ':candidate_id' => $examCandidateId,
            ':application_no' => $applicationNo,
            ':total_marks' => $totalMarks
        ]);

        $attemptId = (int)$pdo->lastInsertId();

        /*
        |--------------------------------------------------------------------------
        | Save selected questions
        |--------------------------------------------------------------------------
        */

        $insertAnswer = $pdo->prepare("
            INSERT INTO exam_answers
            (
                attempt_id,
                question_id,
                question_order,
                candidate_answer,
                correct_answer,
                is_correct,
                marks_obtained
            )
            VALUES
            (
                :attempt_id,
                :question_id,
                :question_order,
                NULL,
                :correct_answer,
                0,
                0
            )
        ");

        $order = 1;

        foreach ($questions as $q) {

            $insertAnswer->execute([
                ':attempt_id' => $attemptId,
                ':question_id' => $q['id'],
                ':question_order' => $order,
                ':correct_answer' => $q['correct_answer']
            ]);

            $order++;
        }

        $pdo->commit();

        $_SESSION['exam_attempt_id'] = $attemptId;
        $_SESSION['exam_application_no'] = $applicationNo;

        redirect_exam();

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        exam_error(
            'Unable to start the assessment. Please try again.'
        );

        redirect_exam();
    }
}


/*
|--------------------------------------------------------------------------
| STEP 3
| Submit Exam
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['action'] ?? '') === 'submit_exam'
) {

    if (!$examCandidateId) {
        exam_error('Your session has expired. Please login again.');
        redirect_exam();
    }

    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        exam_error('Invalid request. Please try again.');
        redirect_exam();
    }

    $attemptId = (int)($_SESSION['exam_attempt_id'] ?? 0);

    if ($attemptId <= 0) {
        exam_error('Exam attempt not found.');
        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Verify attempt
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM exam_attempts
        WHERE id = :attempt_id
          AND candidate_id = :candidate_id
          AND status = 'started'
        LIMIT 1
    ");

    $stmt->execute([
        ':attempt_id' => $attemptId,
        ':candidate_id' => $examCandidateId
    ]);

    $attempt = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attempt) {
        exam_error('This exam has already been submitted or is invalid.');
        redirect_exam();
    }

    /*
    |--------------------------------------------------------------------------
    | Get saved questions
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            ea.id,
            ea.question_id,
            ea.question_order,
            ea.correct_answer,
            eq.marks
        FROM exam_answers ea
        INNER JOIN exam_questions eq
            ON eq.id = ea.question_id
        WHERE ea.attempt_id = :attempt_id
        ORDER BY ea.question_order ASC
    ");

    $stmt->execute([
        ':attempt_id' => $attemptId
    ]);

    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Calculate result
    |--------------------------------------------------------------------------
    */

    $attempted = 0;
    $correct = 0;
    $wrong = 0;
    $unattempted = 0;
    $obtainedMarks = 0;

    $pdo->beginTransaction();

    try {

        $updateAnswer = $pdo->prepare("
            UPDATE exam_answers
            SET
                candidate_answer = :candidate_answer,
                is_correct = :is_correct,
                marks_obtained = :marks_obtained,
                answered_at = NOW()
            WHERE id = :answer_id
        ");

        foreach ($answers as $answer) {

            $questionAnswer = $_POST['answer'][$answer['question_id']] ?? null;

            if (!in_array($questionAnswer, ['A', 'B', 'C', 'D'], true)) {

                $unattempted++;

                $updateAnswer->execute([
                    ':candidate_answer' => null,
                    ':is_correct' => 0,
                    ':marks_obtained' => 0,
                    ':answer_id' => $answer['id']
                ]);

                continue;
            }

            $attempted++;

            $isCorrect = ($questionAnswer === $answer['correct_answer']);

            if ($isCorrect) {

                $correct++;

                $marks = (float)$answer['marks'];
                $obtainedMarks += $marks;

            } else {

                $wrong++;

                $marks = 0;
            }

            $updateAnswer->execute([
                ':candidate_answer' => $questionAnswer,
                ':is_correct' => $isCorrect ? 1 : 0,
                ':marks_obtained' => $marks,
                ':answer_id' => $answer['id']
            ]);
        }

        $totalMarks = (float)$attempt['total_marks'];

        $percentage = $totalMarks > 0
            ? round(($obtainedMarks / $totalMarks) * 100, 2)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Update attempt
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE exam_attempts
            SET
                attempted_questions = :attempted,
                correct_answers = :correct,
                wrong_answers = :wrong,
                unattempted_questions = :unattempted,
                obtained_marks = :obtained_marks,
                percentage = :percentage,
                status = 'completed',
                submitted_at = NOW()
            WHERE id = :attempt_id
        ");

        $stmt->execute([
            ':attempted' => $attempted,
            ':correct' => $correct,
            ':wrong' => $wrong,
            ':unattempted' => $unattempted,
            ':obtained_marks' => $obtainedMarks,
            ':percentage' => $percentage,
            ':attempt_id' => $attemptId
        ]);

        $pdo->commit();

        /*
        |--------------------------------------------------------------------------
        | Store result in session
        |--------------------------------------------------------------------------
        */

      unset($_SESSION['exam_attempt_id']);
unset($_SESSION['exam_application_no']);

// Candidate ko result nahi dikhana
header('Location: https://www.unire.co.in/');
exit;

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log($e->getMessage());

        exam_error(
            'Unable to submit the assessment. Please try again.'
        );

        redirect_exam();
    }
}


/*
|--------------------------------------------------------------------------
| Current Candidate
|--------------------------------------------------------------------------
*/

$candidate = null;

if ($examCandidateId) {

    $stmt = $pdo->prepare("
        SELECT
            id,
            application_no,
            full_name,
            position_applied
        FROM candidates
        WHERE id = :candidate_id
        LIMIT 1
    ");

    $stmt->execute([
        ':candidate_id' => $examCandidateId
    ]);

    $candidate = $stmt->fetch(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Current Attempt
|--------------------------------------------------------------------------
*/

$attemptId = (int)($_SESSION['exam_attempt_id'] ?? 0);

$currentAttempt = null;

if ($attemptId > 0) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM exam_attempts
        WHERE id = :attempt_id
          AND candidate_id = :candidate_id
          AND status = 'started'
        LIMIT 1
    ");

    $stmt->execute([
        ':attempt_id' => $attemptId,
        ':candidate_id' => $examCandidateId
    ]);

    $currentAttempt = $stmt->fetch(PDO::FETCH_ASSOC);
}


/*
|--------------------------------------------------------------------------
| Questions for current attempt
|--------------------------------------------------------------------------
*/

$currentQuestions = [];

if ($currentAttempt) {

    $stmt = $pdo->prepare("
        SELECT
            ea.id AS answer_id,
            ea.question_id,
            ea.question_order,
            eq.question,
            eq.option_a,
            eq.option_b,
            eq.option_c,
            eq.option_d
        FROM exam_answers ea
        INNER JOIN exam_questions eq
            ON eq.id = ea.question_id
        WHERE ea.attempt_id = :attempt_id
        ORDER BY ea.question_order ASC
    ");

    $stmt->execute([
        ':attempt_id' => $attemptId
    ]);

    $currentQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$error = get_exam_error();
$result = $_SESSION['exam_result'] ?? null;

if ($result) {
    unset($_SESSION['exam_result']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Process Associate Assessment</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #faf7fb;
            color: #25213d;
        }

        .exam-wrapper {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }

        .exam-card {
            background: #fff;
            border: 1px solid #efddea;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(80, 30, 70, .07);
        }

        .exam-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .exam-header h1 {
            margin: 0 0 10px;
            font-size: 28px;
            color: #8f236e;
        }

        .exam-header p {
            margin: 0;
            color: #777;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            margin-bottom: 20px;
            outline: none;
        }

        input:focus {
            border-color: #a9257c;
        }

        .btn {
            display: inline-block;
            border: 0;
            border-radius: 10px;
            padding: 12px 22px;
            background: #a9257c;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn:hover {
            opacity: .92;
        }

        .btn-danger {
            background: #b32626;
        }

        .error {
            background: #fdeaea;
            color: #a32020;
            padding: 13px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .info {
            background: #f4eafa;
            color: #72205a;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .candidate-info {
            background: #faf4f9;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 25px;
        }

        .candidate-info strong {
            color: #8f236e;
        }

        .question {
            padding: 25px 0;
            border-bottom: 1px solid #eee;
        }

        .question:last-child {
            border-bottom: 0;
        }

        .question-title {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 18px;
        }

        .option {
            display: block;
            padding: 12px 15px;
            border: 1px solid #e6dce4;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            font-weight: normal;
        }

        .option:hover {
            background: #faf3f8;
            border-color: #c52b8e;
        }

        .option input {
            margin-right: 10px;
        }

        .submit-area {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .result-box {
            text-align: center;
        }

        .score {
            font-size: 42px;
            font-weight: 700;
            color: #a9257c;
            margin: 20px 0;
        }

        .result-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin: 25px 0;
        }

        .result-item {
            padding: 18px 10px;
            background: #faf5f9;
            border-radius: 12px;
        }

        .result-item strong {
            display: block;
            font-size: 22px;
            margin-bottom: 5px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .top-bar a {
            color: #a9257c;
            text-decoration: none;
            font-size: 14px;
        }

        @media(max-width: 650px) {

            .exam-card {
                padding: 22px;
            }

            .result-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .top-bar {
                align-items: flex-start;
            }
        }

    </style>

</head>

<body>

<div class="exam-wrapper">

    <div class="exam-card">

        <?php if ($error): ?>

            <div class="error">
                <?= h($error) ?>
            </div>

        <?php endif; ?>


        <!-- =====================================================
             RESULT
        ====================================================== -->

        <?php if ($result): ?>

            <div class="result-box">

                <div class="exam-header">

                    <h1>Assessment Submitted</h1>

                    <p>
                        Your Process Associate assessment has been submitted successfully.
                    </p>

                </div>

                <div class="score">
                    <?= h($result['percentage']) ?>%
                </div>

                <div class="result-grid">

                    <div class="result-item">
                        <strong><?= h($result['total_marks']) ?></strong>
                        Total Marks
                    </div>

                    <div class="result-item">
                        <strong><?= h($result['obtained_marks']) ?></strong>
                        Obtained
                    </div>

                    <div class="result-item">
                        <strong><?= h($result['correct']) ?></strong>
                        Correct
                    </div>

                    <div class="result-item">
                        <strong><?= h($result['wrong']) ?></strong>
                        Wrong
                    </div>

                </div>

                <p>
                    Attempted:
                    <strong><?= h($result['attempted']) ?></strong>
                    &nbsp; | &nbsp;
                    Unattempted:
                    <strong><?= h($result['unattempted']) ?></strong>
                </p>

                <p style="margin-top:30px;color:#777;">
                    Thank you for completing the assessment.
                </p>

            </div>


        <!-- =====================================================
             STEP 1 - LOGIN
        ====================================================== -->

        <?php elseif (!$examCandidateId): ?>

            <div class="exam-header">

                <h1>Process Associate Assessment</h1>

                <p>
                    Please login using the credentials provided by HR.
                </p>

            </div>

            <form method="POST">

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= h($csrf) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="login"
                >

                <label>
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    autocomplete="username"
                    required
                >

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="submit"
                    class="btn"
                >
                    Login
                </button>

            </form>


        <!-- =====================================================
             STEP 2 - APPLICATION ID
        ====================================================== -->

        <?php elseif (!$currentAttempt): ?>

            <div class="exam-header">

                <h1>Application Verification</h1>

                <p>
                    Enter your Application ID to continue.
                </p>

            </div>

            <div class="info">

                Example:

                <strong>APP202609030005</strong>

            </div>

            <form method="POST">

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= h($csrf) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="verify_application"
                >

                <label>
                    Application ID
                </label>

                <input
                    type="text"
                    name="application_no"
                    placeholder="Enter Application ID"
                    required
                >

                <button
                    type="submit"
                    class="btn"
                >
                    Continue
                </button>

            </form>


        <!-- =====================================================
             STEP 3 - EXAM
        ====================================================== -->

        <?php else: ?>

            <div class="top-bar">

                <div>
                    <strong>
                        Process Associate Assessment
                    </strong>
                </div>

                <a href="?logout=1">
                    Exit
                </a>

            </div>

            <?php if ($candidate): ?>

                <div class="candidate-info">

                    <div>
                        <strong>Candidate:</strong>
                        <?= h($candidate['full_name']) ?>
                    </div>

                    <div style="margin-top:6px;">
                        <strong>Application ID:</strong>
                        <?= h($candidate['application_no']) ?>
                    </div>

                    <div style="margin-top:6px;">
                        <strong>Position:</strong>
                        <?= h($candidate['position_applied']) ?>
                    </div>

                </div>

            <?php endif; ?>


            <div class="info">

                <strong>Instructions</strong>

                <ul style="margin-bottom:0;">

                    <li>
                        There are <?= count($currentQuestions) ?> questions.
                    </li>

                    <li>
                        Select one answer for each question.
                    </li>

                    <li>
                        Once submitted, the assessment cannot be attempted again.
                    </li>

                </ul>

            </div>


            <form
                method="POST"
                onsubmit="return confirm('Are you sure you want to submit the assessment? You cannot change your answers after submission.');"
            >

                <input
                    type="hidden"
                    name="csrf"
                    value="<?= h($csrf) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="submit_exam"
                >


                <?php foreach ($currentQuestions as $q): ?>

                    <div class="question">

                        <div class="question-title">

                            Q<?= (int)$q['question_order'] ?>.
                            <?= h($q['question']) ?>

                        </div>


                        <label class="option">

                            <input
                                type="radio"
                                name="answer[<?= (int)$q['question_id'] ?>]"
                                value="A"
                            >

                            A.
                            <?= h($q['option_a']) ?>

                        </label>


                        <label class="option">

                            <input
                                type="radio"
                                name="answer[<?= (int)$q['question_id'] ?>]"
                                value="B"
                            >

                            B.
                            <?= h($q['option_b']) ?>

                        </label>


                        <label class="option">

                            <input
                                type="radio"
                                name="answer[<?= (int)$q['question_id'] ?>]"
                                value="C"
                            >

                            C.
                            <?= h($q['option_c']) ?>

                        </label>


                        <label class="option">

                            <input
                                type="radio"
                                name="answer[<?= (int)$q['question_id'] ?>]"
                                value="D"
                            >

                            D.
                            <?= h($q['option_d']) ?>

                        </label>

                    </div>

                <?php endforeach; ?>


                <div class="submit-area">

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Submit Assessment
                    </button>

                </div>

            </form>

        <?php endif; ?>

    </div>

</div>

</body>
</html>