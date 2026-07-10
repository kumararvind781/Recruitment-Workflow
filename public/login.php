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
            u.id,
            u.full_name,
            u.email,
            u.password_hash,
            u.password_changed_at,
            u.status,
            r.name AS role
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

$title = 'Login';
include __DIR__ . '/../app/views/layouts/header.php';
?>

<div class="login-shell">
    <div class="login-card card">
        <div class="section-title">
            <h2>Login</h2>
            <span class="pill">Admin / Recruiter / Manager</span>
        </div>

        <?php if ($error): ?>
            <div class="notice error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Username or Email</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn" type="submit">Login</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>