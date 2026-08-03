<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionTimeout = 900; // 15 Minutes

$current = time();

if (isset($_SESSION['LAST_ACTIVITY'])) {

    $diff = $current - $_SESSION['LAST_ACTIVITY'];

    if ($diff >= $sessionTimeout) {

        session_unset();
        session_destroy();

        header("Location: login.php?timeout=1");
        exit;
    }
}

$_SESSION['LAST_ACTIVITY'] = $current;