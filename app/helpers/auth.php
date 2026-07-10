<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function is_logged_in(){ return !empty($_SESSION['user']); }
function current_user(){ return $_SESSION['user'] ?? null; }
function h($value){ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function require_login(){ if (!is_logged_in()) { header('Location: login.php'); exit; } }
function has_role($roles){ require_login(); return in_array(current_user()['role'] ?? '', (array)$roles, true); }
function require_role($roles){ if (!has_role($roles)) { http_response_code(403); exit('Access denied'); } }
function password_expired($user){ if (!$user || empty($user['password_changed_at'])) return true; return strtotime($user['password_changed_at']) < strtotime('-30 days'); }
