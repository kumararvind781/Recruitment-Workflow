<?php
/*
|--------------------------------------------------------------------------
| FILE: exam_paper_management.php
| PURPOSE: Admin/recruiter management of the Process Associate question bank.
| CHANGES: File header comment added; existing upload, preview, activation,
|          question grouping, form names and page element IDs are retained.
|--------------------------------------------------------------------------
*/


declare(strict_types=1);



/*

|--------------------------------------------------------------------------

| UNIRE - Process Associate Exam Paper Management

|--------------------------------------------------------------------------

| Upload one Excel workbook with two worksheets:

|   1) Fixed Questions

|   2) Random Questions

|

| Supported format: .xlsx

| Required columns:

| question, option_a, option_b, option_c, option_d, correct_answer, marks

|

| Existing question rows are deactivated (not deleted) so completed exam

| reports that reference old question IDs continue to work.

|--------------------------------------------------------------------------

*/



session_start();



require_once __DIR__ . '/../app/helpers/auth.php';

require_once __DIR__ . '/../app/config/database.php';



require_role(['admin', 'recruiter', 'manager']);



if (!function_exists('h')) {

    function h($value): string

    {

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

    }

}



$pdo = Database::connect();

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



const EXAM_TYPE = 'process_associate';

const PAPER_TOTAL = 20;

const MAX_UPLOAD_BYTES = 5242880; // 5 MB



if (empty($_SESSION['exam_paper_csrf'])) {

    $_SESSION['exam_paper_csrf'] = bin2hex(random_bytes(32));

}

$csrf = $_SESSION['exam_paper_csrf'];



$success = '';

$error = '';

$preview = null;



/* -------------------------------------------------------------------------

   Ensure the existing table can distinguish fixed and random questions.

   This is the only schema addition required by this page.

------------------------------------------------------------------------- */

function ensureQuestionGroupColumn(PDO $pdo): void

{

    $stmt = $pdo->query("SHOW COLUMNS FROM exam_questions LIKE 'question_group'");

    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {

        $pdo->exec("

            ALTER TABLE exam_questions

            ADD COLUMN question_group ENUM('fixed','random')

            NOT NULL DEFAULT 'random'

            AFTER exam_type

        ");

    }

}

ensureQuestionGroupColumn($pdo);



/* -------------------------------------------------------------------------

   Spreadsheet helpers

------------------------------------------------------------------------- */

function normalizeHeader(string $value): string

{

    $value = strtolower(trim($value));

    $value = preg_replace('/[^a-z0-9]+/', '_', $value);

    return trim((string) $value, '_');

}



function columnLettersToIndex(string $letters): int

{

    $number = 0;

    foreach (str_split(strtoupper($letters)) as $char) {

        $number = ($number * 26) + (ord($char) - 64);

    }

    return $number - 1;

}



function readCsvRows(string $path): array

{

    $handle = fopen($path, 'rb');

    if (!$handle) {

        throw new RuntimeException('Unable to read the uploaded CSV file.');

    }



    $rows = [];

    while (($row = fgetcsv($handle)) !== false) {

        if (count($row) === 1 && trim((string) $row[0]) === '') {

            continue;

        }

        // Remove UTF-8 BOM from the first cell/header.

        if (isset($row[0])) {

            $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]);

        }

        $rows[] = $row;

    }

    fclose($handle);

    return $rows;

}



function readXlsxRows(string $path, int $sheetNumber = 1): array

{

    if (!class_exists('ZipArchive')) {

        throw new RuntimeException('PHP ZipArchive extension is required to read .xlsx files. Please upload CSV or enable ZipArchive.');

    }

    if (!function_exists('simplexml_load_string')) {

        throw new RuntimeException('PHP SimpleXML extension is required to read .xlsx files. Please upload CSV or enable SimpleXML.');

    }



    $zip = new ZipArchive();

    if ($zip->open($path) !== true) {

        throw new RuntimeException('The uploaded Excel file is not a valid .xlsx file.');

    }



    try {

        $sharedStrings = [];

        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');

        if ($sharedXml !== false) {

            $sx = simplexml_load_string($sharedXml);

            if ($sx !== false) {

                $ns = $sx->getNamespaces(true);

                $mainNs = $ns[''] ?? 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

                foreach ($sx->children($mainNs)->si as $si) {

                    $parts = [];

                    foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) {

                        $parts[] = (string) $t;

                    }

                    $sharedStrings[] = implode('', $parts);

                }

            }

        }



        // The first worksheet is used; standard Excel exports store it here.

        $sheetXml = $zip->getFromName('xl/worksheets/sheet' . $sheetNumber . '.xml');

        if ($sheetXml === false) {

            throw new RuntimeException('The Excel file does not contain worksheet ' . $sheetNumber . '. Please use the supplied sample workbook.');

        }



        $sheet = simplexml_load_string($sheetXml);

        if ($sheet === false) {

            throw new RuntimeException('Unable to read the Excel worksheet.');

        }



        $rows = [];

        foreach ($sheet->xpath('//*[local-name()="sheetData"]/*[local-name()="row"]') ?: [] as $rowNode) {

            $row = [];

            foreach ($rowNode->xpath('./*[local-name()="c"]') ?: [] as $cell) {

                $attrs = $cell->attributes();

                $reference = (string) ($attrs['r'] ?? '');

                if (!preg_match('/^([A-Z]+)/i', $reference, $matches)) {

                    continue;

                }

                $index = columnLettersToIndex($matches[1]);

                $type = (string) ($attrs['t'] ?? '');

                $value = '';



                if ($type === 'inlineStr') {

                    $texts = [];

                    foreach ($cell->xpath('.//*[local-name()="t"]') ?: [] as $t) {

                        $texts[] = (string) $t;

                    }

                    $value = implode('', $texts);

                } else {

                    $v = $cell->xpath('./*[local-name()="v"]');

                    $value = isset($v[0]) ? (string) $v[0] : '';

                    if ($type === 's' && $value !== '') {

                        $value = $sharedStrings[(int) $value] ?? '';

                    }

                }

                $row[$index] = $value;

            }



            if ($row) {

                $max = max(array_keys($row));

                $normalized = [];

                for ($i = 0; $i <= $max; $i++) {

                    $normalized[] = $row[$i] ?? '';

                }

                $rows[] = $normalized;

            }

        }

        return $rows;

    } finally {

        $zip->close();

    }

}



function parseQuestionRows(array $rows, string $label): array

{

    if (!$rows)

        return [];

    $headers = array_map(static fn($v) => normalizeHeader((string) $v), array_map('strval', $rows[0]));

    $aliases = [

        'question' => ['question', 'questions'],

        'option_a' => ['option_a', 'optiona', 'a'],

        'option_b' => ['option_b', 'optionb', 'b'],

        'option_c' => ['option_c', 'optionc', 'c'],

        'option_d' => ['option_d', 'optiond', 'd'],

        'correct_answer' => ['correct_answer', 'answer', 'correct_option']

    ];

    $map = [];

    foreach ($aliases as $field => $names) {

        $found = false;

        foreach ($headers as $index => $header) {

            if (in_array($header, $names, true)) {

                $map[$field] = $index;

                $found = true;

                break;

            }

        }

        if (!$found)

            throw new RuntimeException('Missing column "' . $field . '" in worksheet ' . $label . '.');

    }

    $questions = [];

    foreach (array_slice($rows, 1) as $rowIndex => $row) {

        $values = [];

        foreach ($map as $field => $index)

            $values[$field] = trim((string) ($row[$index] ?? ''));

        if (implode('', $values) === '')

            continue;

        $line = $rowIndex + 2;

        foreach (['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'] as $required) {

            if ($values[$required] === '')

                throw new RuntimeException('Blank "' . $required . '" at row ' . $line . ' in worksheet ' . $label . '.');

        }

        $answer = strtoupper($values['correct_answer']);

        if (!in_array($answer, ['A', 'B', 'C', 'D'], true))

            throw new RuntimeException('Correct answer must be A, B, C or D at row ' . $line . ' in worksheet ' . $label . '.');

        $questions[] = [

            'question' => $values['question'],

            'option_a' => $values['option_a'],

            'option_b' => $values['option_b'],

            'option_c' => $values['option_c'],

            'option_d' => $values['option_d'],

            'correct_answer' => $answer,

            'marks' => '1.00'

        ];

    }

    return $questions;

}



function readQuestionWorkbook(array $file): array

{

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK)

        throw new RuntimeException('Please select the sample Excel workbook.');

    if (($file['size'] ?? 0) <= 0 || $file['size'] > MAX_UPLOAD_BYTES)

        throw new RuntimeException('Excel file must be smaller than 5 MB.');

    if (strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION)) !== 'xlsx')

        throw new RuntimeException('Please upload the supplied .xlsx workbook.');

    $path = (string) $file['tmp_name'];

    $fixedRows = readXlsxRows($path, 1);

    $randomRows = readXlsxRows($path, 2);

    return [

        'fixed' => parseQuestionRows($fixedRows, 'Fixed Questions'),

        'random' => parseQuestionRows($randomRows, 'Random Questions')

    ];

}



function hasUploadedFile(array $files, string $key): bool

{

    return isset($files[$key])

        && (int) ($files[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

}



function getActiveQuestionsByGroup(PDO $pdo, string $group): array

{

    $stmt = $pdo->prepare("

        SELECT *

        FROM exam_questions

        WHERE exam_type = 'process_associate'

          AND question_group = :question_group

          AND is_active = 1

        ORDER BY id ASC

    ");



    $stmt->execute([

        ':question_group' => $group

    ]);



    return $stmt->fetchAll(PDO::FETCH_ASSOC);

}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {

            throw new RuntimeException('Your session expired. Refresh the page and try again.');

        }



        $action = (string) ($_POST['action'] ?? '');



        if ($action === 'preview') {

            unset($_SESSION['exam_paper_pending']);



            if (!hasUploadedFile($_FILES, 'paper_file')) {

                throw new RuntimeException('Please select the Excel workbook to upload.');

            }

            $workbookQuestions = readQuestionWorkbook($_FILES['paper_file']);

            // A blank worksheet means keep that existing active question group unchanged.

            $fixed = count($workbookQuestions['fixed'])

                ? $workbookQuestions['fixed'] : getActiveQuestionsByGroup($pdo, 'fixed');

            $random = count($workbookQuestions['random'])

                ? $workbookQuestions['random'] : getActiveQuestionsByGroup($pdo, 'random');



            $fixedCount = count($fixed);

            $randomPoolCount = count($random);

            $availableTotal = $fixedCount + $randomPoolCount;

            $randomNeeded = PAPER_TOTAL - $fixedCount;



            if ($fixedCount > PAPER_TOTAL) {

                throw new RuntimeException('Fixed file can contain a maximum of 20 questions. Please reduce fixed questions.');

            }



            if ($availableTotal < PAPER_TOTAL || $randomPoolCount < $randomNeeded) {

                $missing = max(0, PAPER_TOTAL - $availableTotal);

                throw new RuntimeException(

                    'Please upload more questions. Current total: ' . $availableTotal .

                    '. Minimum required: 20. Additional questions needed: ' . $missing . '.'

                );

            }



            $_SESSION['exam_paper_pending'] = [

                'fixed' => $fixed,

                'random' => $random,

                'created_at' => time()

            ];

            $preview = $_SESSION['exam_paper_pending'];

            $success = 'File validation successful. Review the paper and activate it.';

        } elseif ($action === 'activate') {

            $pending = $_SESSION['exam_paper_pending'] ?? null;

            if (!$pending || !isset($pending['fixed'], $pending['random'])) {

                throw new RuntimeException('Please upload and preview at least one file before activation.');

            }

            if (time() - (int) ($pending['created_at'] ?? 0) > 1800) {

                unset($_SESSION['exam_paper_pending']);

                throw new RuntimeException('Preview expired. Please upload the file(s) again.');

            }



            $fixed = $pending['fixed'];

            $random = $pending['random'];

            $fixedCount = count($fixed);

            $randomPoolCount = count($random);

            $randomNeeded = PAPER_TOTAL - $fixedCount;



            if ($fixedCount > PAPER_TOTAL || $randomPoolCount < $randomNeeded) {

                throw new RuntimeException('Total question bank is below 20. Please upload more questions.');

            }



            $pdo->beginTransaction();



            // Old rows stay in the table for historical attempt/report references.

            $deactivate = $pdo->prepare("UPDATE exam_questions SET is_active = 0 WHERE exam_type = ?");

            $deactivate->execute([EXAM_TYPE]);



            $insert = $pdo->prepare("

                INSERT INTO exam_questions

                (exam_type, question_group, question, option_a, option_b, option_c, option_d, correct_answer, marks, is_active)

                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1.00, 1)

            ");



            foreach ($fixed as $q) {

                $insert->execute([

                    EXAM_TYPE,

                    'fixed',

                    $q['question'],

                    $q['option_a'],

                    $q['option_b'],

                    $q['option_c'],

                    $q['option_d'],

                    $q['correct_answer']

                ]);

            }



            foreach ($random as $q) {

                $insert->execute([

                    EXAM_TYPE,

                    'random',

                    $q['question'],

                    $q['option_a'],

                    $q['option_b'],

                    $q['option_c'],

                    $q['option_d'],

                    $q['correct_answer']

                ]);

            }



            $pdo->commit();

            unset($_SESSION['exam_paper_pending']);

            $success = 'New question workbook activated. Any blank worksheet kept its existing question group. Previous questions remain available for old exam reports.';

        } elseif ($action === 'cancel_preview') {

            unset($_SESSION['exam_paper_pending']);

            $success = 'Pending preview cleared. Current active paper was not changed.';

        }

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {

            $pdo->rollBack();

        }

        $error = $e->getMessage();

    }

}



/* -------------------------------------------------------------------------

   Current active paper

------------------------------------------------------------------------- */

$stmt = $pdo->prepare("

    SELECT id, question_group, question, option_a, option_b, option_c, option_d,

           correct_answer, marks, created_at

    FROM exam_questions

    WHERE exam_type = ?

      AND is_active = 1

    ORDER BY FIELD(question_group, 'fixed', 'random'), id ASC

");

$stmt->execute([EXAM_TYPE]);

$currentQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);



$currentFixed = array_values(array_filter(

    $currentQuestions,

    static fn($q) => $q['question_group'] === 'fixed'

));

$currentRandom = array_values(array_filter(

    $currentQuestions,

    static fn($q) => $q['question_group'] === 'random'

));

$currentFixedCount = count($currentFixed);

$currentRandomPoolCount = count($currentRandom);

$currentRandomNeeded = max(0, PAPER_TOTAL - $currentFixedCount);

$currentReady = $currentFixedCount <= PAPER_TOTAL

    && count($currentQuestions) >= PAPER_TOTAL

    && $currentRandomPoolCount >= $currentRandomNeeded;



if (!$preview && isset($_SESSION['exam_paper_pending'])) {

    $preview = $_SESSION['exam_paper_pending'];

}

?>

<?php

$pageTitle = 'Exam Paper Management';

include __DIR__ . '/../app/views/layouts/header.php';

?>



<style>

    :root {

        --pink: #b52b80;

        --pink2: #cf3d84;

        --ink: #282039;

        --muted: #716a7b;

        --line: #eadfeb;

        --bg: #f8f5fa;

        --green: #16834a;

        --red: #bd2638

    }



    * {

        box-sizing: border-box

    }



    body {

        margin: 0;

        background: var(--bg);

        font-family: Arial, sans-serif;

        color: var(--ink)

    }



    .brand {

        font-size: 20px;

        font-weight: 700;

        color: #30263c

    }



    .brand small {

        display: block;

        font-size: 13px;

        font-weight: 400;

        color: var(--muted);

        margin-top: 4px

    }



    .container {

        max-width: 1200px;

        margin: 28px auto;

        padding: 0 18px

    }



    .page-head {

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 15px;

        flex-wrap: wrap;

        margin-bottom: 20px

    }



    h1 {

        font-size: 27px;

        margin: 0;

        color: #92236b

    }



    h2 {

        font-size: 19px;

        margin: 0 0 16px

    }



    .muted {

        color: var(--muted);

        font-size: 13px;

        line-height: 1.5

    }



    .cards {

        display: grid;

        grid-template-columns: repeat(3, 1fr);

        gap: 14px;

        margin-bottom: 22px

    }



    .card,

    .panel {

        background: #fff;

        border: 1px solid var(--line);

        border-radius: 14px;

        padding: 20px;

        box-shadow: 0 7px 24px rgba(50, 25, 55, .04)

    }



    .stat strong {

        display: block;

        font-size: 30px;

        color: var(--pink);

        margin-top: 8px

    }



    .stat span {

        font-size: 13px;

        color: var(--muted)

    }



    .panel {

        margin-bottom: 20px

    }



    .notice {

        padding: 13px 15px;

        border-radius: 9px;

        margin: 0 0 18px;

        line-height: 1.45

    }



    .success {

        background: #eaf7ef;

        color: #17683d;

        border: 1px solid #c8ead4

    }



    .error {

        background: #fff0f0;

        color: #a31e2c;

        border: 1px solid #f1caca

    }



    .upload-grid {

        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 16px

    }



    .upload-box {

        border: 1px dashed #d7b5d0;

        border-radius: 12px;

        padding: 18px;

        background: #fdfafd

    }



    .upload-box label {

        display: block;

        font-weight: 700;

        margin-bottom: 9px

    }



    .upload-box input {

        width: 100%;

        padding: 10px;

        background: #fff;

        border: 1px solid var(--line);

        border-radius: 8px

    }



    .btn-secondary {

        display: inline-block;

        text-decoration: none;

        background: #fff;

        color: var(--pink);

        border: 1px solid var(--pink);

        padding: 10px 15px;

        border-radius: 8px;

        font-weight: 700

    }



    .btn {

        border: 0;

        border-radius: 8px;

        padding: 12px 18px;

        background: var(--pink);

        color: #fff;

        font-weight: 700;

        cursor: pointer;

        text-decoration: none;

        display: inline-block

    }



    .btn.secondary {

        background: #fff;

        color: var(--pink);

        border: 1px solid var(--pink)

    }



    .btn.danger {

        background: #7b7180

    }



    .actions {

        display: flex;

        gap: 10px;

        flex-wrap: wrap;

        margin-top: 17px

    }



    .table-wrap {

        overflow-x: auto

    }



    table {

        width: 100%;

        border-collapse: collapse;

        min-width: 760px

    }



    th,

    td {

        padding: 11px 10px;

        border-bottom: 1px solid #eee;

        text-align: left;

        vertical-align: top;

        font-size: 13px

    }



    th {

        background: #fbf7fb;

        color: #6b5268

    }



    .pill {

        display: inline-block;

        padding: 4px 9px;

        border-radius: 30px;

        font-size: 11px;

        font-weight: 700;

        background: #f2e7f1;

        color: #81256d

    }



    .pill.fixed {

        background: #e9f6ed;

        color: #17683d

    }



    .pill.random {

        background: #edf1ff;

        color: #354b9a

    }



    .question {

        border: 1px solid #eee;

        border-radius: 10px;

        padding: 15px;

        margin: 12px 0;

        background: #fff

    }



    .question-title {

        font-weight: 700;

        margin-bottom: 9px;

        line-height: 1.5

    }



    .options {

        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 7px;

        color: #51495a;

        font-size: 13px

    }



    .answer {

        margin-top: 10px;

        color: var(--green);

        font-weight: 700;

        font-size: 13px

    }



    .preview-actions {

        display: flex;

        gap: 10px;

        flex-wrap: wrap;

        margin-top: 18px

    }



    @media(max-width:700px) {



        .cards,

        .upload-grid {

            grid-template-columns: 1fr

        }



        .container {

            margin: 18px auto

        }



        .topbar {

            padding: 14px

        }



        .options {

            grid-template-columns: 1fr

        }



        h1 {

            font-size: 22px

        }

    }



    @media print {



        .topbar,

        .no-print,

        .upload-panel {

            display: none !important

        }



        body {

            background: #fff

        }



        .container {

            max-width: none;

            margin: 0;

            padding: 0

        }



        .panel,

        .card {

            box-shadow: none;

            border: 0

        }



        .question {

            break-inside: avoid

        }

    }

</style>



<main class="container">



    <div class="page-head">

        <div>

            <h1>Exam Paper Management</h1>

            <div class="muted">Process Associate assessment · Total paper size: <?= PAPER_TOTAL ?> questions</div>

        </div>

        <button class="btn secondary no-print" type="button" onclick="window.print()">Print Current Paper</button>

    </div>



    <?php if ($success !== ''): ?>

        <div class="notice success"><?= h($success) ?></div><?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="notice error"><?= h($error) ?></div><?php endif; ?>



    <section class="cards" style="grid-template-columns:1fr">

        <div class="card stat">

            <span>Questions in each candidate paper</span>

            <strong><?= PAPER_TOTAL ?></strong>

            <span>20 questions · 1 mark each · Total 20 marks</span>

        </div>

    </section>



    <?php if (!$currentReady && $currentQuestions): ?>

        <div class="notice error">Current active question bank has fewer than 20 usable questions. Please upload more

            questions.</div>

    <?php elseif (!$currentQuestions): ?>

        <div class="notice error">No active paper is available yet. Upload either or both files. The combined question bank

            must contain at least 20 questions.</div>

    <?php else: ?>

        <div class="notice success">Current paper is ready: <?= $currentFixedCount ?> fixed + <?= $currentRandomNeeded ?>

            randomly selected from a pool of <?= $currentRandomPoolCount ?> = 20 questions. Each question carries 1 mark.

        </div>

    <?php endif; ?>



    <section class="panel upload-panel no-print">

        <h2>Upload New Paper</h2>

        <p class="muted">Upload one Excel workbook containing two worksheets: <strong>Fixed Questions</strong> and

            <strong>Random Questions</strong>. Fill only the group you want to update; leave the other worksheet blank

            to retain its current active questions. Every candidate paper contains exactly 20 questions.

        </p>

        <div class="actions" style="margin:12px 0 18px">

            <a class="btn btn-secondary" href="uploads/Sample_exam/UNIRE_Exam_Questions_Sample.xlsx" download>Download

                Sample Excel File</a>

        </div>

        <form method="post" enctype="multipart/form-data">

            <?= csrf_field() ?>

            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

            <input type="hidden" name="action" value="preview">

            <div class="upload-box">

                <label for="paper_file">Select Excel Workbook (.xlsx)</label>

                <input type="file" id="paper_file" name="paper_file" accept=".xlsx" required>

                <p class="muted">Keep the worksheet names and column headings unchanged. Correct answer must be A, B, C

                    or D.</p>

            </div>

            <div class="actions"><button class="btn" type="submit">Upload Excel &amp; Preview</button></div>

        </form>

        <p class="muted" style="margin-top:14px"><strong>Required headers in both worksheets:</strong> question,

            option_a, option_b, option_c, option_d, correct_answer. Every question is automatically assigned 1 mark.</p>

    </section>



    <?php if ($preview): ?>

        <?php

        $previewFixed = $preview['fixed'] ?? [];

        $previewRandom = $preview['random'] ?? [];

        $previewNeed = PAPER_TOTAL - count($previewFixed);

        ?>

        <section class="panel no-print">

            <h2>Preview New Paper</h2>

            <div class="notice success">

                Validated: <?= count($previewFixed) ?> fixed questions + <?= $previewNeed ?> random questions selected from

                a pool of <?= count($previewRandom) ?> = <?= PAPER_TOTAL ?> questions per candidate · 1 mark each · 20 total

                marks.

            </div>

            <h3>Fixed Questions (all included)</h3>

            <?php foreach ($previewFixed as $i => $q): ?>

                <div class="question">

                    <div class="question-title">Fixed <?= $i + 1 ?>. <?= h($q['question']) ?></div>

                    <div class="options">

                        <div>A. <?= h($q['option_a']) ?></div>

                        <div>B. <?= h($q['option_b']) ?></div>

                        <div>C. <?= h($q['option_c']) ?></div>

                        <div>D. <?= h($q['option_d']) ?></div>

                    </div>

                    <div class="answer">Correct answer: <?= h($q['correct_answer']) ?> · Marks: 1</div>

                </div>

            <?php endforeach; ?>

            <h3>Random Question Bank (candidate receives <?= $previewNeed ?>)</h3>

            <?php foreach ($previewRandom as $i => $q): ?>

                <div class="question">

                    <div class="question-title">Pool <?= $i + 1 ?>. <?= h($q['question']) ?></div>

                    <div class="options">

                        <div>A. <?= h($q['option_a']) ?></div>

                        <div>B. <?= h($q['option_b']) ?></div>

                        <div>C. <?= h($q['option_c']) ?></div>

                        <div>D. <?= h($q['option_d']) ?></div>

                    </div>

                    <div class="answer">Correct answer: <?= h($q['correct_answer']) ?> · Marks: 1</div>

                </div>

            <?php endforeach; ?>

            <div class="preview-actions">

                <form method="post">

                    <?= csrf_field() ?>

                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

                    <input type="hidden" name="action" value="activate">

                    <button class="btn" type="submit"

                        onclick="return confirm('Activate this paper? The current active questions will be replaced for future exam attempts. Existing exam history will be retained.')">Confirm

                        & Activate New Paper</button>

                </form>

                <form method="post">

                    <?= csrf_field() ?>

                    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

                    <input type="hidden" name="action" value="cancel_preview">

                    <button class="btn danger" type="submit">Cancel Preview</button>

                </form>

            </div>

        </section>

    <?php endif; ?>



    <section class="panel">

        <h2>Current Active Paper – All Questions & Answers</h2>

        <?php if (!$currentQuestions): ?>

            <p class="muted">No active questions found.</p>

        <?php else: ?>

            <?php foreach (['fixed' => $currentFixed, 'random' => $currentRandom] as $group => $items): ?>

                <h3><?= $group === 'fixed' ? 'Fixed Questions' : 'Random Question Bank' ?> (<?= count($items) ?>)</h3>

                <?php foreach ($items as $i => $q): ?>

                    <div class="question">

                        <div class="question-title">

                            <span class="pill <?= h($group) ?>"><?= strtoupper($group) ?></span>

                            <?= $group === 'fixed' ? 'Fixed ' . ($i + 1) : 'Pool ' . ($i + 1) ?>.

                            <?= h($q['question']) ?>

                        </div>

                        <div class="options">

                            <div>A. <?= h($q['option_a']) ?></div>

                            <div>B. <?= h($q['option_b']) ?></div>

                            <div>C. <?= h($q['option_c']) ?></div>

                            <div>D. <?= h($q['option_d']) ?></div>

                        </div>

                        <div class="answer">Correct answer: <?= h($q['correct_answer']) ?> · Marks: 1</div>

                    </div>

                <?php endforeach; ?>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</main>
