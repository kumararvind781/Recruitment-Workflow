<?php
require_once __DIR__ . '/../../helpers/auth.php';
if (session_status() === PHP_SESSION_NONE)
  session_start();
$user = $_SESSION['user'] ?? null;
$title = $title ?? 'UNIRE Recruitment Workflow';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <header class="topbar">
    <div class="brand-wrap">
      <div class="brand-logo">
        <img src="assets/Unire-Business-Solutions-Pvt-Ltd.png" alt="Unire Business Solutions Pvt Ltd Logo">
      </div>
      <div class="brand-text">
        <h1>Unire Business Solutions Pvt Ltd</h1>
        <p class="muted">Travel back-office hiring panel</p>
      </div>
    </div>

    <nav class="actions">
      <?php if ($user): ?>
        <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
        <?php if (($user['role'] ?? '') === 'admin'): ?>
          <a class="btn btn-outline" href="users.php">Users</a>
          <a class="btn btn-outline" href="reports.php">Reports</a>
        <?php endif; ?>
        <?php if (($user['role'] ?? '') !== 'manager'): ?>
          <a class="btn btn-outline" href="recruiter-candidates.php">Candidates</a>
        <?php endif; ?>
        <span class="pill"><?= h($user['name']) ?> (<?= h($user['role']) ?>)</span>
        <a class="btn btn-outline" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-outline" href="login.php">Login</a>
      <?php endif; ?>
    </nav>
  </header>
  <main class="container">