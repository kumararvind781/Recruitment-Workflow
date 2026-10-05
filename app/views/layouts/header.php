<?php
require_once __DIR__ . '/../../helpers/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$title = $title ?? 'UNIRE Recruitment Workflow';

// Session timeout: 60 minutes
$sessionTimeout = 3600;

// Calculate session expiry
$lastActivity = $_SESSION['LAST_ACTIVITY'] ?? time();
$sessionExpires = $lastActivity + $sessionTimeout;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= h($title) ?></title>

  <link rel="stylesheet" href="assets/css/style.css">

  <style>
    /* ================================
       SESSION COUNTDOWN
    ================================= */

    #sessionCountdown {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 999999;

      background: #dc3545;
      color: #fff;

      text-align: center;

      padding: 8px 15px;

      font-family: Arial, sans-serif;
      font-size: 14px;
      font-weight: 600;

      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    #countdownTime {
      font-weight: 700;
      margin-left: 5px;
    }

    /* Add space when countdown is visible */
    body.session-warning {
      padding-top: 38px;
    }

    /* Stay logged in button */
    #stayLoggedIn {
      margin-left: 10px;
      padding: 4px 10px;

      border: 1px solid #fff;
      border-radius: 4px;

      background: #fff;
      color: #dc3545;

      font-size: 13px;
      font-weight: 600;

      cursor: pointer;
    }

    #stayLoggedIn:hover {
      opacity: 0.9;
    }
  </style>

</head>

<body>

  <?php if ($user): ?>

    <!-- SESSION COUNTDOWN -->
    <div id="sessionCountdown">

      Session expires in

      <span id="countdownTime">
        05:00
      </span>

      <button type="button" id="stayLoggedIn">
        Stay Logged In
      </button>

    </div>

  <?php endif; ?>


  <header class="topbar">

    <div class="brand-wrap">

      <div class="brand-logo">

        <img
          src="assets/Unire-Business-Solutions-Pvt-Ltd.png"
          alt="Unire Business Solutions Pvt Ltd Logo"
        >

      </div>

      <div class="brand-text">

        <h1>Unire Business Solutions Pvt Ltd</h1>

        <p class="muted">
          Travel back-office hiring panel
        </p>

      </div>

    </div>


    <nav class="actions">

      <?php if ($user): ?>

        <a class="btn btn-outline" href="dashboard.php">
          Dashboard
        </a>


        <?php if (($user['role'] ?? '') === 'admin'): ?>

          <a class="btn btn-outline" href="users.php">
            Users
          </a>

          <a class="btn btn-outline" href="reports.php">
            Reports
          </a>

        <?php endif; ?>


        <?php if (($user['role'] ?? '') !== 'manager'): ?>

          <a class="btn btn-outline" href="recruiter-candidates.php">
            Candidates
          </a>

        <?php endif; ?>


        <span class="pill">
          <?= h($user['name']) ?>
          (<?= h($user['role']) ?>)
        </span>


        <a class="btn btn-outline" href="logout.php">
          Logout
        </a>


      <?php else: ?>

        <a class="btn btn-outline" href="login.php">
          Login
        </a>

      <?php endif; ?>

    </nav>

  </header>


  <main class="container">


<?php if ($user): ?>

<script>

(function () {

    // PHP session expiry timestamp
    const sessionExpires = <?= (int)$sessionExpires ?>;

    const countdownBox = document.getElementById('sessionCountdown');
    const countdownTime = document.getElementById('countdownTime');
    const stayLoggedIn = document.getElementById('stayLoggedIn');

    if (!countdownBox || !countdownTime) {
        return;
    }


    function updateCountdown() {

        const now = Math.floor(Date.now() / 1000);

        let remaining = sessionExpires - now;


        // Session expired
        if (remaining <= 0) {

            countdownTime.textContent = '00:00';

            window.location.href = 'login.php?timeout=1';

            return;
        }


        // Show warning only in last 5 minutes
        if (remaining <= 300) {

            countdownBox.style.display = 'block';

            document.body.classList.add('session-warning');


            const minutes = Math.floor(remaining / 60);

            const seconds = remaining % 60;


            countdownTime.textContent =
                String(minutes).padStart(2, '0') +
                ':' +
                String(seconds).padStart(2, '0');

        } else {

            countdownBox.style.display = 'none';

            document.body.classList.remove('session-warning');
        }

    }


    // Run immediately
    updateCountdown();


    // Update every second
    setInterval(updateCountdown, 1000);


    /*
     * STAY LOGGED IN
     *
     * This makes a request to the current page.
     * Because auth.php runs on every page request,
     * LAST_ACTIVITY will be updated.
     */
    if (stayLoggedIn) {

        stayLoggedIn.addEventListener('click', function () {

            fetch(window.location.href, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store'
            })
            .then(function () {

                // Reload page so PHP creates a fresh expiry time
                window.location.reload();

            })
            .catch(function () {

                window.location.reload();

            });

        });

    }

})();

</script>

<?php endif; ?>