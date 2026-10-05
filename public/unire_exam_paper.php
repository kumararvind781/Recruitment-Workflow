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
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$pdo = Database::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$attemptId = (int)($_GET['attempt_id'] ?? 0);

if ($attemptId <= 0) {
    exit('Invalid exam attempt.');
}


/*
|--------------------------------------------------------------------------
| Get Attempt + Candidate
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        ea.*,
        c.application_no,
        c.full_name,
        c.email,
        c.position_applied
    FROM exam_attempts ea
    INNER JOIN candidates c
        ON c.id = ea.candidate_id
    WHERE ea.id = ?
    LIMIT 1
");

$stmt->execute([$attemptId]);

$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    exit('Exam attempt not found.');
}


/*
|--------------------------------------------------------------------------
| Only Process Associate
|--------------------------------------------------------------------------
*/

if (
    strcasecmp(
        trim($attempt['position_applied']),
        'Process Associate'
    ) !== 0
) {
    exit('This assessment is only available for Process Associate.');
}


/*
|--------------------------------------------------------------------------
| Get Questions + Candidate Answers
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        q.id,
        q.question,
        q.option_a,
        q.option_b,
        q.option_c,
        q.option_d,
        q.correct_answer,
        q.marks,

        aa.candidate_answer,
        aa.is_correct,
        aa.marks_obtained

    FROM exam_answers aa

    INNER JOIN exam_questions q
        ON q.id = aa.question_id

    WHERE aa.attempt_id = ?

    ORDER BY aa.id ASC
");

$stmt->execute([$attemptId]);

$answers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$answers) {
    exit('No exam answers found.');
}

$totalQuestions = count($answers);

$attempted = 0;
$correct = 0;
$wrong = 0;
$unattempted = 0;
$totalMarks = 0;
$obtainedMarks = 0;

foreach ($answers as $row) {

    $totalMarks += (float)$row['marks'];

    if (
        $row['candidate_answer'] === null ||
        $row['candidate_answer'] === ''
    ) {
        $unattempted++;
    } else {

        $attempted++;

        if ((int)$row['is_correct'] === 1) {
            $correct++;
        } else {
            $wrong++;
        }
    }

    $obtainedMarks += (float)$row['marks_obtained'];
}

$percentage = $totalMarks > 0
    ? round(($obtainedMarks / $totalMarks) * 100, 2)
    : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>
        Process Associate Assessment -
        <?= h($attempt['application_no']) ?>
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f8f5fa;
            font-family: Arial, sans-serif;
            color: #241b35;
        }

        .page {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #cf3d84;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .header h1 {
            margin: 0;
            color: #9f246a;
            font-size: 28px;
        }

        .header h2 {
            margin: 8px 0 0;
            font-size: 20px;
        }

        .candidate-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 25px;
        }

        .info-box {
            border: 1px solid #eadfeb;
            padding: 12px;
            border-radius: 8px;
            background: #fcfafc;
        }

        .info-box strong {
            display: block;
            margin-bottom: 4px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }

        .summary-box {
            text-align: center;
            background: #faf4f8;
            border-radius: 10px;
            padding: 15px;
        }

        .summary-box .number {
            font-size: 24px;
            font-weight: bold;
            color: #a42372;
        }

        .summary-box .label {
            margin-top: 5px;
            font-size: 13px;
        }

        .question {
            border: 1px solid #e7dce7;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }

        .question-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .options {
            margin-bottom: 15px;
        }

        .option {
            padding: 7px 0;
        }

        .answer-box {
            border-top: 1px solid #eee;
            padding-top: 12px;
        }

        .correct {
            font-weight: bold;
        }

        .wrong {
            font-weight: bold;
        }

        .unattempted {
            font-weight: bold;
        }

        .print-btn {
            display: inline-block;
            background: #cf3d84;
            color: white;
            padding: 11px 18px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }

        @media print {

            body {
                background: #fff;
                padding: 0;
            }

            .page {
                max-width: none;
                padding: 20px;
            }

            .print-btn {
                display: none;
            }

            .question {
                page-break-inside: avoid;
            }

        }

        @media (max-width: 700px) {

            body {
                padding: 10px;
            }

            .page {
                padding: 20px;
            }

            .candidate-info,
            .summary {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="page">

    <button
        type="button"
        class="print-btn"
        onclick="window.print()"
    >
        Print / Save PDF
    </button>


    <div class="header">

        <h1>
            UNIRE BUSINESS SOLUTIONS PVT LTD
        </h1>

        <h2>
            Process Associate Assessment
        </h2>

    </div>


    <div class="candidate-info">

        <div class="info-box">
            <strong>Candidate Name</strong>
            <?= h($attempt['full_name']) ?>
        </div>

        <div class="info-box">
            <strong>Application No</strong>
            <?= h($attempt['application_no']) ?>
        </div>

        <div class="info-box">
            <strong>Email</strong>
            <?= h($attempt['email']) ?>
        </div>

        <div class="info-box">
            <strong>Exam Status</strong>
            <?= h(ucfirst($attempt['status'])) ?>
        </div>

    </div>


    <div class="summary">

        <div class="summary-box">
            <div class="number">
                <?= $totalQuestions ?>
            </div>
            <div class="label">
                Total Questions
            </div>
        </div>

        <div class="summary-box">
            <div class="number">
                <?= $correct ?>
            </div>
            <div class="label">
                Correct
            </div>
        </div>

        <div class="summary-box">
            <div class="number">
                <?= $wrong ?>
            </div>
            <div class="label">
                Wrong
            </div>
        </div>

        <div class="summary-box">
            <div class="number">
                <?= $unattempted ?>
            </div>
            <div class="label">
                Unattempted
            </div>
        </div>

    </div>


    <div class="summary">

        <div class="summary-box">
            <div class="number">
                <?= $attempted ?>
            </div>
            <div class="label">
                Attempted
            </div>
        </div>

        <div class="summary-box">
            <div class="number">
                <?= h(number_format($obtainedMarks, 2)) ?>
            </div>
            <div class="label">
                Obtained Marks
            </div>
        </div>

        <div class="summary-box">
            <div class="number">
                <?= h(number_format($totalMarks, 2)) ?>
            </div>
            <div class="label">
                Total Marks
            </div>
        </div>

        <div class="summary-box">
            <div class="number">
                <?= h(number_format($percentage, 2)) ?>%
            </div>
            <div class="label">
                Percentage
            </div>
        </div>

    </div>


    <h2>Full Question Paper</h2>


    <?php foreach ($answers as $index => $row): ?>

        <?php

        $candidateAnswer =
            trim((string)($row['candidate_answer'] ?? ''));

        $correctAnswer =
            trim((string)$row['correct_answer']);

        if ($candidateAnswer === '') {
            $resultClass = 'unattempted';
            $resultText = 'Unattempted';
        } elseif ((int)$row['is_correct'] === 1) {
            $resultClass = 'correct';
            $resultText = 'Correct';
        } else {
            $resultClass = 'wrong';
            $resultText = 'Wrong';
        }

        ?>

        <div class="question">

            <div class="question-title">

                Q<?= $index + 1 ?>.
                <?= h($row['question']) ?>

            </div>


            <div class="options">

                <div class="option">
                    <strong>A.</strong>
                    <?= h($row['option_a']) ?>
                </div>

                <div class="option">
                    <strong>B.</strong>
                    <?= h($row['option_b']) ?>
                </div>

                <div class="option">
                    <strong>C.</strong>
                    <?= h($row['option_c']) ?>
                </div>

                <div class="option">
                    <strong>D.</strong>
                    <?= h($row['option_d']) ?>
                </div>

            </div>


            <div class="answer-box">

                <div>
                    <strong>Candidate Answer:</strong>

                    <?= $candidateAnswer !== ''
                        ? h($candidateAnswer)
                        : 'Not Answered'
                    ?>
                </div>


                <div>
                    <strong>Correct Answer:</strong>
                    <?= h($correctAnswer) ?>
                </div>


                <div class="<?= h($resultClass) ?>">

                    Result:
                    <?= h($resultText) ?>

                </div>


                <div>

                    <strong>Marks:</strong>

                    <?= h($row['marks_obtained']) ?>

                    /

                    <?= h($row['marks']) ?>

                </div>

            </div>

        </div>

    <?php endforeach; ?>


</div>

</body>
</html>