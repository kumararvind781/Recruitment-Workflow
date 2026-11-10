<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/auth.php';

$error = '';
$pdo = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT
            u.id, u.full_name, u.email, u.password_hash,
            u.password_changed_at, u.status, r.name AS role
        FROM users u
        LEFT JOIN user_roles ur ON ur.user_id = u.id
        LEFT JOIN roles r ON r.id = ur.role_id
        WHERE (u.full_name = ? OR u.email = ?)
          AND u.status = 'active'
        LIMIT 1
    ");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $storedHash = trim($user['password_hash'] ?? '');
        $validPassword = false;

        if ($storedHash !== '') {
            if (strpos($storedHash, '$2y$') === 0 || strpos($storedHash, '$argon2') === 0) {
                $validPassword = password_verify($password, $storedHash);

                if ($validPassword && password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password_hash = ?, password_changed_at = NOW() WHERE id = ?");
                    $update->execute([$newHash, $user['id']]);
                    $user['password_changed_at'] = date('Y-m-d H:i:s');
                }
            } elseif (strlen($storedHash) === 32) {
                $validPassword = (md5($password) === $storedHash);

                if ($validPassword) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE users SET password_hash = ?, password_changed_at = NOW() WHERE id = ?");
                    $update->execute([$newHash, $user['id']]);
                    $user['password_changed_at'] = date('Y-m-d H:i:s');
                }
            }
        }

        if ($validPassword) {
            session_regenerate_id(true);
            $_SESSION['LAST_ACTIVITY'] = time();
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user',
                'password_changed_at' => $user['password_changed_at']
            ];

            if (password_expired($user)) {
                header('Location: users.php?force_password=1');
                exit;
            }

            header('Location: dashboard.php');
            exit;
        }
    }

    $error = 'Invalid username/email or password';
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Unire Recruitment Workflow</title>
    <style>
        :root {
            --unire: #8F246C;
            --unire-dark: #68194f;
            --ink: #251b35;
            --muted: #777184
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: var(--ink);
            overflow: hidden;
            background: #fbf7fb
        }

        .login-screen {
            width: 100%;
            height: 100vh;
            min-height: 620px;
            position: relative;
            overflow: hidden;
            padding: 145px clamp(100px, 9vw, 170px) 42px;
            background: radial-gradient(ellipse at 100% 0%, rgba(225, 174, 220, .30), transparent 28%), radial-gradient(ellipse at 0% 100%, rgba(231, 190, 225, .22), transparent 30%), linear-gradient(120deg, #fff 0%, #fcf8fc 58%, #f9eff7 100%)
        }

        .login-screen:before,
        .login-screen:after {
            content: "";
            position: absolute;
            pointer-events: none;
            border-radius: 50%
        }

        .login-screen:before {
            width: 560px;
            height: 260px;
            right: -210px;
            top: -170px;
            background: rgba(227, 185, 224, .22);
            transform: rotate(25deg)
        }

        .login-screen:after {
            width: 620px;
            height: 230px;
            left: -230px;
            bottom: -170px;
            background: rgba(227, 185, 224, .20);
            transform: rotate(15deg)
        }

        .login-brand {
            position: absolute;
            top: 28px;
            left: clamp(28px, 3.8vw, 66px);
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 16px
        }

        .brand-word {
            font-size: 38px;
            letter-spacing: -2px;
            font-weight: 300;
            color: #b72d91;
            line-height: .9
        }

        .brand-word small {
            display: block;
            font-size: 7px;
            letter-spacing: 0;
            text-align: center;
            color: #e67a21;
            font-weight: 700;
            margin-top: 5px
        }

        .brand-divider {
            height: 42px;
            width: 1px;
            background: #c9b9c9
        }

        .brand-company {
            font-size: 19px;
            font-weight: 800;
            color: #29213a
        }

        .brand-sub {
            font-size: 14px;
            color: #777087;
            margin-top: 5px
        }

        .login-layout {
            position: relative;
            z-index: 1;
            width: 100%;
            height: 100%;
            max-width: 1600px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, .88fr) minmax(440px, 1.08fr);
            gap: clamp(18px, 2vw, 34px);
            align-items: center
        }

        .hero-copy {
            min-width: 0;
            align-self: center;
            padding-bottom: 35px
        }

        .hero-copy h1 {
            font-size: clamp(37px, 3.35vw, 58px);
            line-height: 1.12;
            letter-spacing: -1.8px;
            margin: 0 0 22px;
            color: #281d39;
            font-weight: 800
        }

        .hero-copy h1 .highlight {
            display: inline-block;
            background: var(--unire);
            color: #fff;
            border-radius: 13px;
            padding: 0 11px 5px
        }

        .hero-copy p {
            font-size: clamp(15px, 1.15vw, 18px);
            line-height: 1.65;
            color: var(--muted);
            max-width: 510px;
            margin: 0
        }

        .hero-visual {
            height: min(53vh, 440px);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0
        }

        .hero-visual img {
            display: block;
            width: 100%;
            height: 100%;
            max-width: 100%;
            object-fit: contain;
            object-position: center;
            filter: drop-shadow(0 12px 24px rgba(94, 38, 91, .08))
        }

        .login-card {
            width: 100%;
            max-width: 610px;
            justify-self: end;
            background: rgba(255, 255, 255, .96);
            border: 1px solid #f0ddec;
            border-radius: 24px;
            padding: clamp(27px, 2.7vw, 42px);
            box-shadow: 0 18px 55px rgba(100, 35, 83, .10)
        }

        .card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 30px
        }

        .card-heading h2 {
            font-size: 36px;
            margin: 0;
            color: #70406d;
            letter-spacing: -1px
        }

        .role-pill {
            white-space: nowrap;
            border-radius: 30px;
            background: #f8e4f2;
            color: var(--unire);
            font-size: 12px;
            font-weight: 700;
            padding: 10px 15px
        }

        .field {
            margin-bottom: 22px
        }

        .field label {
            display: block;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #30253d
        }

        .field input {
            width: 100%;
            height: 58px;
            border: 1px solid #e6d2e4;
            border-radius: 14px;
            padding: 0 18px;
            font-size: 16px;
            color: #30253d;
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s
        }

        .field input:focus {
            border-color: var(--unire);
            box-shadow: 0 0 0 4px rgba(143, 36, 108, .10)
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1.15fr;
            gap: 12px;
            margin-top: 30px
        }

        .actions button,
        .actions a {
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform .2s, background .2s
        }

        .actions button {
            border: 1px solid var(--unire);
            background: var(--unire);
            color: #fff
        }

        .actions button:hover {
            background: var(--unire-dark);
            transform: translateY(-1px)
        }

        .actions a {
            border: 1.5px solid var(--unire);
            background: #fff;
            color: var(--unire)
        }

        .actions a:hover {
            background: #fbf0f8;
            transform: translateY(-1px)
        }

        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin: 0 0 18px;
            background: #fff0f0;
            color: #a32636;
            font-size: 14px
        }

        .alert.info {
            background: #f8eaf4;
            color: var(--unire-dark)
        }

        .card-note {
            text-align: center;
            margin: 22px 0 0;
            color: #938a9b;
            font-size: 12px
        }

        .benefits {
            grid-column: 1/3;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            max-width: 560px;
            margin-top: -12px
        }

        .benefit {
            flex: 1;
            text-align: center;
            padding: 0 14px
        }

        .benefit+.benefit {
            border-left: 1px solid #e8d9e7
        }

        .benefit-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #f8e5f3;
            color: var(--unire);
            display: grid;
            place-items: center;
            font-size: 23px;
            margin: 0 auto 8px
        }

        .benefit b {
            font-size: 14px;
            display: block;
            color: #30253d
        }

        .benefit small {
            font-size: 12px;
            color: #777087
        }

        .left-panel {
            grid-column: 1/3;
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(250px, .92fr);
            grid-template-rows: 1fr auto;
            column-gap: 10px;
            row-gap: 8px;
            align-self: stretch;
            min-height: 0
        }

        .left-panel .hero-copy {
            grid-column: 1;
            grid-row: 1;
            align-self: center;
            padding: 0 0 0 4px
        }

        .left-panel .hero-visual {
            grid-column: 2;
            grid-row: 1;
            align-self: center
        }

        .left-panel .benefits {
            grid-column: 1/3;
            grid-row: 2;
            margin: 0 0 0 5px
        }

        .login-card {
            grid-column: 3;
            grid-row: 1;
            align-self: center
        }

        @media(max-width:1100px) {
            body {
                overflow: auto
            }

            .login-screen {
                height: auto;
                min-height: 100vh;
                overflow: visible;
                padding: 120px 22px 35px
            }

            .login-layout {
                height: auto;
                max-width: 650px;
                display: flex;
                flex-direction: column;
                gap: 22px
            }

            .left-panel {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto auto
            }

            .left-panel .hero-copy {
                grid-column: 1;
                grid-row: 1
            }

            .left-panel .hero-visual {
                grid-column: 2;
                grid-row: 1;
                height: 260px;
                min-height: 0
            }

            .left-panel .benefits {
                grid-column: 1/3;
                grid-row: 2;
                margin: 8px auto
            }

            .login-card {
                max-width: 100%;
                align-self: center
            }
        }

        @media(max-width:560px) {
            .login-screen {
                padding: 105px 14px 25px
            }

            .login-brand {
                left: 15px;
                top: 18px;
                gap: 10px
            }

            .brand-word {
                font-size: 29px
            }

            .brand-company {
                font-size: 13px
            }

            .brand-sub {
                font-size: 11px
            }

            .brand-divider {
                height: 34px
            }

            .left-panel {
                display: flex;
                flex-direction: column;
                gap: 6px
            }

            .left-panel .hero-copy {
                text-align: center;
                padding: 0
            }

            .hero-copy h1 {
                font-size: 35px;
                letter-spacing: -1px;
                margin-bottom: 12px
            }

            .hero-copy p {
                font-size: 14px
            }

            .left-panel .hero-visual {
                width: 100%;
                height: 210px;
                min-height: 0
            }

            .left-panel .benefits {
                width: 100%;
                margin: 5px auto 10px
            }

            .benefit {
                padding: 0 5px
            }

            .benefit b {
                font-size: 12px
            }

            .benefit small {
                font-size: 11px
            }

            .benefit-icon {
                width: 40px;
                height: 40px
            }

            .login-card {
                padding: 24px 18px;
                border-radius: 18px
            }

            .card-heading {
                align-items: flex-start;
                flex-direction: column;
                margin-bottom: 23px
            }

            .card-heading h2 {
                font-size: 30px
            }

            .field input {
                height: 52px
            }

            .actions {
                grid-template-columns: 1fr
            }
        }
    </style>
</head>

<body>
    <header class="login-brand">
        <div class="brand-word">UNIRE<small>BUSINESS PROCESS SERVICES</small></div>
        <div class="brand-divider"></div>
        <div>
            <div class="brand-company">Unire Business Solutions Pvt Ltd</div>
            <div class="brand-sub">Travel back-office hiring panel</div>
        </div>
    </header>

    <main class="login-screen">
        <div class="login-layout">
            <div class="left-panel">
                <section class="hero-copy">
                    <h1>Build Your Career,<br>One <span class="highlight">Opportunity</span><br>at a Time</h1>
                    <p>Explore openings, showcase your skills, and be part of a growing global travel BPO team.</p>
                </section>

                <div class="hero-visual">
                    <img src="assets/unire-login-hero.png" alt="Recruitment team working together">
                </div>

                <div class="benefits">
                    <div class="benefit">
                        <div class="benefit-icon">♧</div><b>Explore</b><small>Opportunities</small>
                    </div>
                    <div class="benefit">
                        <div class="benefit-icon">↗</div><b>Grow</b><small>Your Skills</small>
                    </div>
                    <div class="benefit">
                        <div class="benefit-icon">♙</div><b>Work with</b><small>A Global Team</small>
                    </div>
                </div>
            </div>

            <section class="login-card">
                <div class="card-heading">
                    <h2>Login</h2>
                    <span class="role-pill">Admin / Recruiter / Manager</span>
                </div>

                <?php if (isset($_GET['timeout'])): ?>
                    <div class="alert info">Your session expired. Please login again.</div>
                <?php endif; ?>

                <?php if (isset($_GET['password_reset']) && $_GET['password_reset'] === 'success'): ?>
                    <div class="alert info">Password reset successfully. Please log in with your new password.</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert"><?= h($error) ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="on">
                    <?= csrf_field(); ?>
                    <div class="field">
                        <label for="username">Username or Email</label>
                        <input id="username" type="text" name="username" placeholder="Enter username or email"
                            autocomplete="username" required>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" placeholder="Enter password"
                            autocomplete="current-password" required>
                    </div>
                    <div class="actions">
                        <button type="submit">Login</button>
                        <a href="<?= BASE_URL ?>public/password_reset.php">Reset Password</a>
                    </div>
                </form>
                <p class="card-note">Secure access for authorised recruitment team members.</p>
            </section>
        </div>
    </main>
</body>

</html>