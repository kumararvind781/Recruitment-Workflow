<?php
declare(strict_types=1);

// Prevent browser/proxy caching of OTP and password-reset screens.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

// Allow the reset page to open only from the login page.
// Direct URL entry, bookmarks, and refresh/back navigation return to login.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $refererHost = $referer !== '' ? (parse_url($referer, PHP_URL_HOST) ?: '') : '';
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    $refererPath = $referer !== '' ? (parse_url($referer, PHP_URL_PATH) ?: '') : '';

    $cameFromLogin = (
        $refererHost !== '' &&
        strcasecmp($refererHost, $currentHost) === 0 &&
        preg_match('~/public/login\\.php$~i', $refererPath)
    );

    if (!$cameFromLogin) {
        header('Location: login.php', true, 302);
        exit;
    }
}

/* Standalone Recruitment Workflow Password Reset for all active users.
   Save as public/reset_password.php.
   Does not include or modify app/helpers/session.php or csrf.php. */

require_once __DIR__ . '/../app/config/database.php';

$mailConfig = require __DIR__ . '/../app/config/mail_config.php';
$recoveryEmail = 'arvind.sharma@unire.co.in';
$error = '';
$success = '';
$step = 'request';
$resetId = 0;
$pdo = null;

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function sendResetOtp(string $otp, array $cfg, string $to): bool
{
    foreach (['host','port','encryption','username','password','from_email','from_name'] as $key) {
        if (!isset($cfg[$key]) || trim((string)$cfg[$key]) === '') {
            error_log('Password reset: missing mail setting ' . $key);
            return false;
        }
    }
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        error_log('Password reset: PHPMailer autoload missing: ' . $autoload);
        return false;
    }
    require_once $autoload;

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string)$cfg['host'];
        $mail->SMTPAuth = true;
        $mail->Username = (string)$cfg['username'];
        $mail->Password = (string)$cfg['password'];
        $mail->Port = (int)$cfg['port'];
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0;
        $enc = strtolower(trim((string)$cfg['encryption']));
        if (in_array($enc, ['ssl','smtps'], true)) {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif (in_array($enc, ['tls','starttls'], true)) {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            throw new RuntimeException('SMTP encryption must be ssl or tls.');
        }
        $mail->setFrom((string)$cfg['from_email'], (string)$cfg['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = 'Recruitment Workflow - Password Reset OTP';
        $mail->Body = '<h2>Recruitment Workflow Password Reset</h2><p>Your verification OTP is:</p><h1 style="letter-spacing:6px;color:#8F246C">'
            . h($otp) . '</h1><p>This code expires in 5 minutes and can be used once.</p>';
        $mail->AltBody = "Your password reset OTP is {$otp}. It expires in 5 minutes.";
        return $mail->send();
    } catch (Throwable $e) {
        error_log('Password reset mail error: ' . $e->getMessage());
        return false;
    }
}

try {
    $pdo = Database::connect();
    if (!$pdo instanceof PDO) throw new RuntimeException('Database connection did not return PDO.');

    // Separate table; existing users/login/security tables are not structurally changed.
    $pdo->exec("CREATE TABLE IF NOT EXISTS recruitment_password_reset_otps (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        otp_hash VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
        verified_at DATETIME NULL,
        used_at DATETIME NULL,
        INDEX idx_reset_user_created (user_id, created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'send_otp') {
            $identifier = trim((string)($_POST['identifier'] ?? ''));
            if ($identifier === '' || strlen($identifier) > 190) {
                $error = 'Enter your username or registered email address.';
            } else {
                $q = $pdo->prepare("SELECT u.id FROM users u
                    WHERE u.status='active'
                      AND (LOWER(u.full_name)=LOWER(?) OR LOWER(u.email)=LOWER(?))
                    ORDER BY u.id ASC LIMIT 1");
                $q->execute([$identifier, $identifier]);
                $targetUserId = (int)$q->fetchColumn();

                if (!$targetUserId) {
                    $error = 'No active account matched that username or email address.';
                } else {
                    $recent = $pdo->prepare("SELECT id FROM recruitment_password_reset_otps
                        WHERE user_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 60 SECOND)
                        ORDER BY id DESC LIMIT 1");
                    $recent->execute([$targetUserId]);
                if ($recent->fetchColumn()) {
                    $error = 'Please wait 60 seconds before requesting another OTP.';
                } else {
                    $otp = (string)random_int(100000, 999999);
                    if (sendResetOtp($otp, $mailConfig, $recoveryEmail)) {
                        $ins = $pdo->prepare("INSERT INTO recruitment_password_reset_otps
                            (user_id,otp_hash,created_at,expires_at)
                            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
                        $ins->execute([$targetUserId, password_hash($otp, PASSWORD_DEFAULT)]);
                        $resetId = (int)$pdo->lastInsertId();
                        $step = 'verify';
                        $success = 'OTP sent to the configured recovery email address.';
                    } else {
                        $error = 'OTP email could not be sent. Check mail_config.php and the PHP error log.';
                    }
                    }
                }
            }
        } elseif ($action === 'verify_otp') {
            $resetId = (int)($_POST['reset_id'] ?? 0);
            $otp = trim((string)($_POST['otp'] ?? ''));
            $q = $pdo->prepare("SELECT * FROM recruitment_password_reset_otps WHERE id=? LIMIT 1");
            $q->execute([$resetId]);
            $row = $q->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['used_at'] !== null || strtotime((string)$row['expires_at']) < time()) {
                $error = 'OTP expired or reset request not found. Please request a new OTP.';
            } elseif ((int)$row['attempts'] >= 5) {
                $error = 'Maximum OTP attempts exceeded. Please request a new OTP.';
            } elseif (!preg_match('/^[0-9]{6}$/', $otp)) {
                $error = 'Enter the six-digit OTP.';
                $step = 'verify';
            } else {
                $pdo->prepare("UPDATE recruitment_password_reset_otps SET attempts=attempts+1 WHERE id=?")->execute([$resetId]);
                if (password_verify($otp, (string)$row['otp_hash'])) {
                    $pdo->prepare("UPDATE recruitment_password_reset_otps SET verified_at=NOW() WHERE id=?")->execute([$resetId]);
                    $step = 'password';
                    $success = 'OTP verified. Enter your new password.';
                } else {
                    $error = 'Incorrect OTP. Please try again.';
                    $step = 'verify';
                }
            }
        } elseif ($action === 'update_password') {
            $resetId = (int)($_POST['reset_id'] ?? 0);
            $new = (string)($_POST['new_password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');
            $q = $pdo->prepare("SELECT t.user_id FROM recruitment_password_reset_otps t
                INNER JOIN users u ON u.id=t.user_id
                WHERE t.id=? AND t.verified_at IS NOT NULL
                  AND t.verified_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                  AND t.used_at IS NULL AND u.status='active' LIMIT 1");
            $q->execute([$resetId]);
            $targetUserId = (int)$q->fetchColumn();

            if (!$targetUserId) {
                $error = 'OTP verification expired. Please start again.';
            } elseif (strlen($new) < 12) {
                $error = 'Password must contain at least 12 characters.';
                $step = 'password';
            } elseif ($new !== $confirm) {
                $error = 'Passwords do not match.';
                $step = 'password';
            } else {
                $pdo->beginTransaction();
                $u = $pdo->prepare("UPDATE users SET password_hash=?, password_changed_at=NOW()
                    WHERE id=? AND status='active'");
                $u->execute([password_hash($new, PASSWORD_DEFAULT), $targetUserId]);
                if ($u->rowCount() < 1) throw new RuntimeException('User password was not updated.');
                $pdo->prepare("UPDATE recruitment_password_reset_otps SET used_at=NOW()
                    WHERE user_id=? AND used_at IS NULL")->execute([$targetUserId]);
                $pdo->commit();
                $step = 'done';
                $success = 'Password reset successfully. Redirecting to login page...';
            }
        }
    }
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Recruitment Workflow password reset error: ' . $e->getMessage());
    $error = 'A system error occurred. Please check the PHP error log.';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Recruitment Workflow - Password Reset</title>
<style>
*{box-sizing:border-box}body{margin:0;padding:24px;background:#eef6f1;font-family:Arial,sans-serif;min-height:100vh;display:flex;justify-content:center;align-items:flex-start}
.card{width:480px;max-width:100%;margin-top:35px;background:#fff;padding:32px;border-radius:14px;box-shadow:0 8px 28px #00000012}
h2{text-align:center;color:#8F246C;margin-top:0}p{line-height:1.5;color:#444}label{display:block;font-weight:bold;margin:18px 0 7px}
input{width:100%;padding:13px;border:1px solid #ccc;border-radius:8px;font-size:16px}
button{width:100%;margin-top:22px;padding:14px;border:0;border-radius:8px;background:#8F246C;color:#fff;font-weight:bold;font-size:16px;cursor:pointer}
.notice{padding:12px;border-radius:8px;margin:12px 0}.error{background:#ffe8e8;color:#9b1111}.success{background:#e7f7ec;color:#176b36}
.small{text-align:center;font-size:13px;color:#666}
</style>
</head>
<body><main class="card">
<h2>Reset Your Password</h2>
<?php if ($error !== ''): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>
<?php if ($success !== ''): ?><div class="notice success"><?= h($success) ?></div><?php endif; ?>

<?php if ($step === 'verify'): ?>
<p>Enter the six-digit OTP sent to <?= h($recoveryEmail) ?>.</p>
<form method="post">
<input type="hidden" name="action" value="verify_otp">
<input type="hidden" name="reset_id" value="<?= (int)$resetId ?>">
<label for="otp">OTP</label><input id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
<button type="submit">Verify OTP</button>
</form>
<?php elseif ($step === 'password'): ?>
<form method="post">
<input type="hidden" name="action" value="update_password">
<input type="hidden" name="reset_id" value="<?= (int)$resetId ?>">
<label for="new_password">New password</label><input id="new_password" name="new_password" type="password" minlength="12" autocomplete="new-password" required>
<label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="12" autocomplete="new-password" required>
<button type="submit">Update Password</button>
</form>
<?php elseif ($step === 'done'): ?>
<p class="small"><?= h($success) ?></p>
<script>
(function () {
    // Replace the reset form's history entry so Back does not reopen OTP screens.
    const loginUrl = 'login.php?password_reset=success';
    window.location.replace(loginUrl);
})();
</script>
<p class="small"><a href="login.php?password_reset=success">Continue to Login</a></p>
<?php else: ?>
<p>Enter your active account username or registered email address. A verification code will be sent to the central recovery mailbox for approval.</p>
<form method="post">
<input type="hidden" name="action" value="send_otp">
<label for="identifier">Username or registered email</label>
<input id="identifier" name="identifier" type="text" maxlength="190" autocomplete="username" required>
<button type="submit">Send OTP</button>
</form>
<p class="small">OTP is delivered to the configured central recovery email, not to the user's personal email.</p>
<?php endif; ?>
<?php if ($step !== 'done'): ?>
<p class="small" style="margin-top:18px"><a href="login.php" style="color:#8F246C">Back to Login</a></p>
<?php endif; ?>
</main></body></html>
