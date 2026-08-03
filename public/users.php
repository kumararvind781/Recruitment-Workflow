<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = Database::connect();
$passwordExpiryDays = 30;
$message = '';
$errors = [];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function is_post_request(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function csrf_valid_request(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function h2($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$currentUser = current_user();
$currentUserId = (int)($currentUser['id'] ?? 0);
$mode = $_GET['mode'] ?? 'manage';

if (!$currentUser) {
    header('Location: login.php');
    exit;
}

$stmtMe = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmtMe->execute([$currentUserId]);
$me = $stmtMe->fetch(PDO::FETCH_ASSOC);

if (!$me || (int)($me['is_deleted'] ?? 0) === 1 || ($me['status'] ?? '') !== 'active') {
    session_destroy();
    header('Location: login.php');
    exit;
}

$mustResetPassword = false;
if ((int)($me['must_change_password_on_first_login'] ?? 0) === 1 || (int)($me['force_password_change'] ?? 0) === 1) {
    $mustResetPassword = true;
} elseif (!empty($me['password_changed_at'])) {
    $changedAt = new DateTime($me['password_changed_at']);
    $today = new DateTime();
    $daysSinceChange = (int)$changedAt->diff($today)->format('%a');

    if ($daysSinceChange >= $passwordExpiryDays) {
        $mustResetPassword = true;
        $pdo->prepare("UPDATE users SET force_password_change = 1 WHERE id = ?")->execute([$currentUserId]);
        $me['force_password_change'] = 1;
    }
}

if ($mustResetPassword && $mode !== 'change_password') {
    header('Location: users.php?mode=change_password');
    exit;
}

if ($mode === 'change_password') {
    if (is_post_request()) {
        if (!csrf_valid_request()) {
            $errors[] = 'Invalid request token.';
        } else {
            $newPassword = trim($_POST['new_password'] ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if ($newPassword === '') {
                $errors[] = 'New password is required.';
            }
            if (strlen($newPassword) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            }
            if ($newPassword !== $confirmPassword) {
                $errors[] = 'Confirm password does not match.';
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("\n                    UPDATE users\n                    SET password_hash = ?,\n                        password_changed_at = NOW(),\n                        force_password_change = 0,\n                        must_change_password_on_first_login = 0,\n                        updated_at = NOW(),\n                        last_login_at = COALESCE(last_login_at, NOW())\n                    WHERE id = ?\n                ");
                $stmt->execute([
                    password_hash($newPassword, PASSWORD_DEFAULT),
                    $currentUserId
                ]);

                header('Location: users.php?password_changed=1');
                exit;
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Change Password</title>
        <style>
            body{font-family:Arial,sans-serif;background:#f4f6fb;margin:0;padding:40px;}
            .wrap{max-width:460px;margin:0 auto;}
            .card{background:#fff;border-radius:14px;padding:28px;box-shadow:0 12px 40px rgba(0,0,0,.08);}
            h2{margin-top:0;margin-bottom:10px;}
            .form-group{margin-bottom:16px;}
            label{display:block;margin-bottom:6px;font-weight:600;}
            input{width:100%;padding:12px;border:1px solid #d0d7e2;border-radius:8px;box-sizing:border-box;}
            .btn{width:100%;padding:12px;border:0;background:#CF3D84;color:#fff;border-radius:8px;cursor:pointer;}
            .msg{padding:12px;border-radius:8px;margin-bottom:14px;}
            .error{background:#fdeaea;color:#b42318;}
            .note{color:#666;font-size:14px;line-height:1.5;}
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="card">
                <h2>Change Password</h2>
                <p class="note">First login or password expiry policy requires a new password every <?= (int)$passwordExpiryDays ?> days.</p>

                <?php if (!empty($errors)): ?>
                    <div class="msg error">
                        <?php foreach ($errors as $error): ?>
                            <div><?= h2($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post">
    <?= csrf_field(); ?>
                    <input type="hidden" name="csrf_token" value="<?= h2($_SESSION['csrf_token']) ?>">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button class="btn" type="submit">Update Password</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

require_role('admin');

if (isset($_GET['password_changed']) && $_GET['password_changed'] == '1') {
    $message = 'Password updated successfully.';
}

$roles = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

if (is_post_request()) {
    if (!csrf_valid_request()) {
        $errors[] = 'Invalid request token. Please refresh page and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create_user') {
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $role_id = (int)($_POST['role_id'] ?? 0);

            if ($full_name === '') {
                $errors[] = 'Full name is required.';
            }
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required.';
            }
            if ($password === '') {
                $errors[] = 'Password is required.';
            }
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            }
            if ($role_id <= 0) {
                $errors[] = 'Please select role.';
            }

            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $checkEmail->execute([$email]);
            if ($checkEmail->fetch()) {
                $errors[] = 'Email already exists.';
            }

            if (empty($errors)) {
                try {
                    $pdo->beginTransaction();

                    $stmt = $pdo->prepare("\n                        INSERT INTO users\n                        (full_name, email, phone, password_hash, status, password_changed_at, force_password_change, must_change_password_on_first_login, is_deleted, deleted_at, created_at, updated_at, last_login_at)\n                        VALUES\n                        (?, ?, ?, ?, 'active', NOW(), 1, 1, 0, NULL, NOW(), NOW(), NULL)\n                    ");
                    $stmt->execute([
                        $full_name,
                        $email,
                        $phone !== '' ? $phone : null,
                        password_hash($password, PASSWORD_DEFAULT)
                    ]);

                    $userId = (int)$pdo->lastInsertId();
                    $stmtRole = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                    $stmtRole->execute([$userId, $role_id]);

                    $pdo->commit();
                    $message = 'User created successfully. First login password reset is enabled.';
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $errors[] = 'Failed to create user.';
                }
            }
        }

        elseif ($action === 'reset_password') {
            $id = (int)($_POST['id'] ?? 0);
            $newPassword = trim($_POST['new_password'] ?? '');

            if ($id <= 0) {
                $errors[] = 'Invalid user selected.';
            }
            if ($newPassword === '') {
                $errors[] = 'New password is required.';
            }
            if (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            }

            if (empty($errors)) {
                $stmt = $pdo->prepare("\n                    UPDATE users\n                    SET password_hash = ?,\n                        password_changed_at = NOW(),\n                        force_password_change = 0,\n                        must_change_password_on_first_login = 0,\n                        updated_at = NOW()\n                    WHERE id = ? AND is_deleted = 0\n                ");
                $stmt->execute([
                    password_hash($newPassword, PASSWORD_DEFAULT),
                    $id
                ]);
                $message = 'Password reset successfully.';
            }
        }

        elseif ($action === 'change_role') {
            $id = (int)($_POST['id'] ?? 0);
            $role_id = (int)($_POST['new_role_id'] ?? 0);

            if ($id <= 0 || $role_id <= 0) {
                $errors[] = 'Invalid user or role selected.';
            } else {
                $checkRole = $pdo->prepare("SELECT id FROM roles WHERE id = ? LIMIT 1");
                $checkRole->execute([$role_id]);

                if (!$checkRole->fetch()) {
                    $errors[] = 'Selected role not found.';
                } else {
                    $checkUserRole = $pdo->prepare("SELECT user_id FROM user_roles WHERE user_id = ? LIMIT 1");
                    $checkUserRole->execute([$id]);

                    if ($checkUserRole->fetch()) {
                        $stmt = $pdo->prepare("UPDATE user_roles SET role_id = ? WHERE user_id = ?");
                        $stmt->execute([$role_id, $id]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                        $stmt->execute([$id, $role_id]);
                    }

                    $message = 'User role updated successfully.';
                }
            }
        }

        elseif ($action === 'inactive_user') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === $currentUserId) {
                $errors[] = 'You cannot inactive your own account.';
            } elseif ($id > 0) {
                $pdo->prepare("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = ? AND is_deleted = 0")
                    ->execute([$id]);
                $message = 'User marked inactive.';
            }
        }

        elseif ($action === 'activate_user') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ? AND is_deleted = 0")
                    ->execute([$id]);
                $message = 'User activated successfully.';
            }
        }

        elseif ($action === 'delete_user') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id === $currentUserId) {
                $errors[] = 'You cannot delete your own account.';
            } elseif ($id > 0) {
                $pdo->prepare("UPDATE users SET is_deleted = 1, deleted_at = NOW(), updated_at = NOW() WHERE id = ?")
                    ->execute([$id]);
                $message = 'User moved to deleted list.';
            }
        }

        elseif ($action === 'restore_user') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("UPDATE users SET is_deleted = 0, deleted_at = NULL, status = 'active', updated_at = NOW() WHERE id = ?")
                    ->execute([$id]);
                $message = 'User restored successfully.';
            }
        }
    }
}

$filter = $_GET['status'] ?? 'active';
$where = 'WHERE u.is_deleted = 0 AND u.status = :status';
$params = ['status' => 'active'];

if ($filter === 'inactive') {
    $where = 'WHERE u.is_deleted = 0 AND u.status = :status';
    $params = ['status' => 'inactive'];
} elseif ($filter === 'deleted') {
    $where = 'WHERE u.is_deleted = 1';
    $params = [];
} elseif ($filter === 'all') {
    $where = '';
    $params = [];
}

$sql = "
    SELECT
        u.*, 
        r.id AS current_role_id,
        r.name AS role_name,
        CASE
            WHEN u.must_change_password_on_first_login = 1 THEN 'First login reset required'
            WHEN u.force_password_change = 1 THEN 'Reset required'
            WHEN u.password_changed_at IS NULL THEN 'Not set'
            WHEN TIMESTAMPDIFF(DAY, u.password_changed_at, NOW()) >= :expiry_days THEN 'Expired'
            ELSE CONCAT(TIMESTAMPDIFF(DAY, u.password_changed_at, NOW()), ' days ago')
        END AS password_status
    FROM users u
    LEFT JOIN user_roles ur ON ur.user_id = u.id
    LEFT JOIN roles r ON r.id = ur.role_id
    $where
    ORDER BY u.full_name ASC
";

$stmtUsers = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmtUsers->bindValue(':' . $key, $value);
}
$stmtUsers->bindValue(':expiry_days', $passwordExpiryDays, PDO::PARAM_INT);
$stmtUsers->execute();
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

$title = 'Users';
include __DIR__ . '/../app/views/layouts/header.php';
?>

<style>
.filter-bar{display:flex;gap:14px;margin-bottom:20px;flex-wrap:wrap;}
.filter-tab{display:inline-flex;align-items:center;justify-content:center;min-width:180px;padding:14px 28px;border-radius:18px;font-size:16px;font-weight:700;text-decoration:none;border:1px solid #e4cfe0;background:#ffffff;color:#b13a84;transition:all .2s ease;}
.filter-tab:hover{background:#fff5fb;color:#b13a84;}
.filter-tab.active{background:#c63d86 !important;color:#ffffff !important;border-color:#c63d86 !important;box-shadow:0 6px 18px rgba(198, 61, 134, 0.18);}
.user-grid{display:grid;grid-template-columns:1fr 1.5fr;gap:20px;}
@media (max-width: 992px){.user-grid{grid-template-columns:1fr;}}
.user-access-list{max-height:520px;overflow-y:auto;}
.user-access-item{display:flex;align-items:flex-start;gap:15px;padding:14px 0;border-bottom:1px solid #ececec;}
.user-avatar{width:48px;height:48px;border-radius:50%;background:#CF3D84;color:#fff;display:flex;justify-content:center;align-items:center;font-size:18px;font-weight:bold;flex-shrink:0;}
.user-details{flex:1;}
.user-details strong{display:block;font-size:15px;}
.user-position,.user-role,.user-meta{color:#777;font-size:13px;margin-top:3px;}
.user-role{color:#7b3ea6;font-weight:600;}
.status-active,.status-inactive,.status-deleted,.pwd-ok,.pwd-expired,.pwd-force{display:inline-block;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-active{background:#e8f7ee;color:#198754;}
.status-inactive{background:#fff3cd;color:#856404;}
.status-deleted{background:#fdeaea;color:#dc3545;}
.pwd-ok{background:#eef3ff;color:#3457d5;}
.pwd-expired{background:#fff0f0;color:#b42318;}
.pwd-force{background:#f3e8ff;color:#7b3ea6;}
.notice-box{padding:12px 14px;border-radius:8px;margin-bottom:16px;}
.notice-success{background:#e8f7ee;color:#198754;}
.notice-error{background:#fdeaea;color:#b42318;}
.table-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.table-actions input[type="password"],
.table-actions select{min-width:140px;}
.small-note{font-size:12px;color:#777;margin-top:6px;}
.role-wrap{display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
</style>

<?php if ($message): ?>
    <div class="notice-box notice-success"><?= h2($message) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="notice-box notice-error">
        <?php foreach ($errors as $error): ?>
            <div><?= h2($error) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="filter-bar">
    <a href="?status=active" class="filter-tab <?= $filter === 'active' ? 'active' : '' ?>">Active Users</a>
    <a href="?status=inactive" class="filter-tab <?= $filter === 'inactive' ? 'active' : '' ?>">Inactive Users</a>
    <a href="?status=deleted" class="filter-tab <?= $filter === 'deleted' ? 'active' : '' ?>">Deleted Users</a>
    <a href="?status=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">All Users</a>
</div>

<div class="user-grid">
    <div class="card">
        <div class="section-title">
            <h2>Add User</h2>
            <span class="pill">Single page user management</span>
        </div>

        <form method="post">
    <?= csrf_field(); ?>
            <input type="hidden" name="csrf_token" value="<?= h2($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="action" value="create_user">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label>Temporary Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role_id" required>
                        <option value="">Select Role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int)$role['id'] ?>"><?= h2($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="small-note">New user will reset password on first login. Password auto reset policy is <?= (int)$passwordExpiryDays ?> days.</div>
            <br>
            <button class="btn" type="submit">Create User</button>
        </form>
    </div>

    <div class="card">
        <div class="section-title">
            <h2>User Access</h2>
            <span class="pill"><?= count($users) ?> Users</span>
        </div>

        <div class="user-access-list">
            <?php if (empty($users)): ?>
                <p>No users found.</p>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <?php
                        $statusClass = ((int)$u['is_deleted'] === 1) ? 'deleted' : strtolower($u['status']);
                        $pwdClass = 'pwd-ok';
                        if ((int)($u['must_change_password_on_first_login'] ?? 0) === 1 || (int)($u['force_password_change'] ?? 0) === 1) {
                            $pwdClass = 'pwd-force';
                        } elseif (($u['password_status'] ?? '') === 'Expired') {
                            $pwdClass = 'pwd-expired';
                        }
                    ?>
                    <div class="user-access-item">
                        <div class="user-avatar"><?= strtoupper(substr($u['full_name'], 0, 1)) ?></div>
                        <div class="user-details">
                            <strong><?= h2($u['full_name']) ?></strong>
                            <div class="user-position"><?= h2($u['email']) ?></div>
                            <div class="user-role">Role : <?= h2($u['role_name'] ?? 'No Role') ?></div>
                            <div class="user-meta">Password : <span class="<?= h2($pwdClass) ?>"><?= h2($u['password_status']) ?></span></div>
                        </div>
                        <span class="status-<?= h2($statusClass) ?>"><?= ucfirst(h2($statusClass)) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<br>

<div class="card">
    <div class="section-title">
        <h2>Manage Users</h2>
        <span class="pill"><?= count($users) ?> Users</span>
    </div>

    <table>
        <thead>
            <tr>
                <th width="16%">Name</th>
                <th width="12%">Role</th>
                <th width="18%">Email</th>
                <th width="10%">Status</th>
                <th width="16%">Password Changed</th>
                <th width="12%">Password Status</th>
                <th width="26%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <?php $isCurrentUser = (int)$u['id'] === $currentUserId; ?>
                    <tr>
                        <td>
                            <strong><?= h2($u['full_name']) ?></strong>
                            <?php if (!empty($u['phone'])): ?>
                                <br><small><?= h2($u['phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= h2($u['role_name'] ?? 'No Role') ?></td>
                        <td><?= h2($u['email']) ?></td>
                        <td>
                            <?php if ((int)$u['is_deleted'] === 1): ?>
                                <span class="status-deleted">Deleted</span>
                            <?php else: ?>
                                <span class="status-<?= h2(strtolower($u['status'])) ?>"><?= ucfirst(h2($u['status'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= h2($u['password_changed_at'] ?: '-') ?></td>
                        <td><?= h2($u['password_status']) ?></td>
                        <td>
                            <form method="post" class="table-actions">
                                <input type="hidden" name="csrf_token" value="<?= h2($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">

                                <?php if ($isCurrentUser): ?>
                                    <span style="color:#888;">Current User</span>
                                <?php elseif ((int)$u['is_deleted'] === 1): ?>
                                    <button class="btn btn-green" name="action" value="restore_user" type="submit">Restore</button>
                                <?php else: ?>
                                    <div class="role-wrap">
                                        <select name="new_role_id">
                                            <option value="">Change Role</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= (int)$role['id'] ?>" <?= ((int)$role['id'] === (int)($u['current_role_id'] ?? 0)) ? 'selected' : '' ?>>
                                                    <?= h2($role['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-outline" name="action" value="change_role" type="submit">Update Role</button>
                                    </div>

                                    <?php if ($u['status'] === 'active'): ?>
                                        <input type="password" name="new_password" placeholder="New Password">
                                        <button class="btn btn-outline" name="action" value="reset_password" type="submit">Reset</button>
                                        <button class="btn btn-warning" name="action" value="inactive_user" type="submit" onclick="return confirm('Mark user inactive?')">Inactive</button>
                                        <button class="btn btn-danger" name="action" value="delete_user" type="submit" onclick="return confirm('Move user to deleted list?')">Delete</button>
                                    <?php elseif ($u['status'] === 'inactive'): ?>
                                        <button class="btn btn-green" name="action" value="activate_user" type="submit">Activate</button>
                                        <button class="btn btn-danger" name="action" value="delete_user" type="submit" onclick="return confirm('Move user to deleted list?')">Delete</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../app/views/layouts/footer.php'; ?>