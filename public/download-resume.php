<?php
require_once __DIR__ . '/../app/helpers/auth.php';

$file = $_GET['file'] ?? '';
if ($file === '') {
    exit('File not found');
}

$basePath = realpath(__DIR__ . '/../');
$fullPath = realpath($basePath . '/' . ltrim($file, '/\\'));

if (!$fullPath || !file_exists($fullPath)) {
    exit('File not found');
}

if (strpos($fullPath, $basePath) !== 0) {
    exit('Invalid file path');
}

$filename = basename($fullPath);
$mime = mime_content_type($fullPath);

header('Content-Description: File Transfer');
header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($fullPath));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

readfile($fullPath);
exit;