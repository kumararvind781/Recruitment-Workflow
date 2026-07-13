<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../app/helpers/auth.php';
require_once __DIR__ . '/../app/config/database.php';

require_role(['admin', 'recruiter', 'manager']);

$pdo = Database::connect();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid candidate ID');
}

$stmt = $pdo->prepare("SELECT id, full_name, resume_path FROM candidates WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$candidate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidate) {
    exit('Candidate not found');
}

if (empty($candidate['resume_path'])) {
    exit('Resume path empty');
}

$resumePath = $candidate['resume_path'];
$try1 = __DIR__ . '/' . ltrim($resumePath, '/');
$try2 = __DIR__ . '/../' . ltrim($resumePath, '/');

if (is_file($try1)) {
    $fullPath = realpath($try1);
} elseif (is_file($try2)) {
    $fullPath = realpath($try2);
} else {
    exit('File not found');
}

$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$fileName = basename($fullPath);

if ($ext === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
} elseif (in_array($ext, ['jpg', 'jpeg'])) {
    header('Content-Type: image/jpeg');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
} elseif ($ext === 'png') {
    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="' . $fileName . '"');
} else {
    exit('Preview not supported for this file type');
}

header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;