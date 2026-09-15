<?php
require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_role(['admin', 'recruiter']);

$pdo = Database::connect();

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/
$tab = $_GET['tab'] ?? 'all';
$search = trim($_GET['search'] ?? '');

/*
|--------------------------------------------------------------------------
| Allowed feedback filters
|--------------------------------------------------------------------------
*/
$allowedTabs = [
    'all',
    'select',
    'reject',
    'next_round',
    'hold'
];

if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'all';
}

/*
|--------------------------------------------------------------------------
| Main Query
|
| Get ONLY the latest feedback for each candidate.
|--------------------------------------------------------------------------
*/
$sql = "
    SELECT
        c.id,
        c.application_no,
        c.full_name,
        c.position_applied,
        c.current_status,
        c.applied_at,
        (
            SELECT f.recommendation
            FROM interview_feedback f
            WHERE f.candidate_id = c.id
            ORDER BY f.id DESC
            LIMIT 1
        ) AS latest_feedback
    FROM candidates c
    WHERE 1=1
";

$params = [];

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/
if ($search !== '') {
    $sql .= "
        AND (
            c.application_no LIKE :search
            OR c.full_name LIKE :search
            OR c.position_applied LIKE :search
            OR c.current_status LIKE :search
        )
    ";

    $params[':search'] = '%' . $search . '%';
}

/*
|--------------------------------------------------------------------------
| Feedback Tab Filter
|
| IMPORTANT:
| Filter is based on LAST feedback/recommendation.
|--------------------------------------------------------------------------
*/
if ($tab !== 'all') {
    $sql .= "
        AND (
            SELECT f2.recommendation
            FROM interview_feedback f2
            WHERE f2.candidate_id = c.id
            ORDER BY f2.id DESC
            LIMIT 1
        ) = :feedback
    ";

    $params[':feedback'] = $tab;
}

$sql .= " ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Candidates';

include __DIR__ . '/../app/views/layouts/header.php';
?>

<style>
    .candidate-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .candidate-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 17px;
        border: 1px solid #efd8e9;
        border-radius: 12px;
        background: #fff;
        color: #9f287a;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        transition: .2s;
    }

    .candidate-tab:hover {
        background: #fff3fa;
        border-color: #d72b91;
    }

    .candidate-tab.active {
        background: #a9257c;
        color: #fff;
        border-color: #a9257c;
    }

    .tab-count {
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        background: #f6dfef;
        color: #9f287a;
    }

    .candidate-tab.active .tab-count {
        background: rgba(255,255,255,.2);
        color: #fff;
    }

    .candidate-search {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .candidate-search input {
        flex: 1;
        min-width: 200px;
        padding: 12px 15px;
        border: 1px solid #ead8e5;
        border-radius: 12px;
        font-size: 14px;
        outline: none;
    }

    .candidate-search input:focus {
        border-color: #bd2a88;
        box-shadow: 0 0 0 3px rgba(189,42,136,.08);
    }

    .candidate-search button {
        padding: 12px 22px;
        border: 0;
        border-radius: 12px;
        background: #a9257c;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    .candidate-search .clear-btn {
        display: inline-flex;
        align-items: center;
        padding: 0 18px;
        border: 1px solid #ead8e5;
        border-radius: 12px;
        color: #a9257c;
        text-decoration: none;
        background: #fff;
    }

    .feedback-badge {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .feedback-select {
        background: #e8f7ed;
        color: #18733a;
    }

    .feedback-reject {
        background: #fdeaea;
        color: #b32626;
    }

    .feedback-next {
        background: #e9f0ff;
        color: #2857a8;
    }

    .feedback-hold {
        background: #fff4df;
        color: #98600b;
    }

    .feedback-none {
        background: #f3f3f3;
        color: #777;
    }

    @media (max-width: 768px) {
        .candidate-search {
            flex-wrap: wrap;
        }

        .candidate-search input {
            width: 100%;
            flex-basis: 100%;
        }
    }
</style>

<div class="section-title">
    <h2>All Candidates</h2>

    <span class="pill">
        <?= count($rows) ?> records
    </span>
</div>


<!-- =========================================================
     SEARCH
========================================================= -->

<form method="GET" class="candidate-search">

    <input
        type="text"
        name="search"
        value="<?= h($search) ?>"
        placeholder="Search by App No, name, position, status..."
    >

    <input type="hidden" name="tab" value="<?= h($tab) ?>">

    <button type="submit">
        Search
    </button>

    <?php if ($search !== ''): ?>
        <a href="?tab=<?= urlencode($tab) ?>" class="clear-btn">
            Clear
        </a>
    <?php endif; ?>

</form>


<!-- =========================================================
     TABS
========================================================= -->

<div class="candidate-tabs">

    <a
        href="?tab=all<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
        class="candidate-tab <?= $tab === 'all' ? 'active' : '' ?>"
    >
        All
    </a>

    <a
        href="?tab=select<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
        class="candidate-tab <?= $tab === 'select' ? 'active' : '' ?>"
    >
        Selected
    </a>

    <a
        href="?tab=reject<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
        class="candidate-tab <?= $tab === 'reject' ? 'active' : '' ?>"
    >
        Rejected
    </a>

    <a
        href="?tab=next_round<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
        class="candidate-tab <?= $tab === 'next_round' ? 'active' : '' ?>"
    >
        Next Round
    </a>

    <a
        href="?tab=hold<?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
        class="candidate-tab <?= $tab === 'hold' ? 'active' : '' ?>"
    >
        Hold
    </a>

</div>


<!-- =========================================================
     CANDIDATES TABLE
========================================================= -->

<div class="card">

    <table>

        <thead>
            <tr>
                <th>App No</th>
                <th>Name</th>
                <th>Position</th>
                <th>Status</th>
                <th>Latest Feedback</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($rows)): ?>

            <tr>
                <td colspan="6" style="text-align:center; padding:40px;">
                    No candidates found.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($rows as $r): ?>

                <?php
                $feedback = $r['latest_feedback'];

                $feedbackClass = 'feedback-none';

                if ($feedback === 'select') {
                    $feedbackClass = 'feedback-select';
                } elseif ($feedback === 'reject') {
                    $feedbackClass = 'feedback-reject';
                } elseif ($feedback === 'next_round') {
                    $feedbackClass = 'feedback-next';
                } elseif ($feedback === 'hold') {
                    $feedbackClass = 'feedback-hold';
                }

                $feedbackText = $feedback ?: '-';

                if ($feedback === 'select') {
                    $feedbackText = 'Selected';
                } elseif ($feedback === 'reject') {
                    $feedbackText = 'Rejected';
                } elseif ($feedback === 'next_round') {
                    $feedbackText = 'Next Round';
                } elseif ($feedback === 'hold') {
                    $feedbackText = 'Hold';
                }
                ?>

                <tr>

                    <td>
                        <?= h($r['application_no']) ?>
                    </td>

                    <td>
                        <?= h($r['full_name']) ?>
                    </td>

                    <td>
                        <?= h($r['position_applied']) ?>
                    </td>

                    <td>
                        <?= h($r['current_status']) ?>
                    </td>

                    <td>
                        <span class="feedback-badge <?= $feedbackClass ?>">
                            <?= h($feedbackText) ?>
                        </span>
                    </td>

                    <td>
                        <a
                            class="btn btn-outline"
                            href="recruiter-candidate.php?id=<?= (int) $r['id'] ?>"
                        >
                            Open
                        </a>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>