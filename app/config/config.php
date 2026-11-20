
<?php

/* Base URL */
$host = $_SERVER['HTTP_HOST'] ?? '';
$host = explode(':', $host)[0];

/* Local Access: localhost or Laptop LAN IP */
if (in_array($host, ['localhost', '127.0.0.1', '192.168.1.60'])) {

    define('BASE_URL', '/Recruitment-Workflow/');

    // Local Database
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'recruitment_workflow');
    define('DB_USER', 'root');
    define('DB_PASS', '');

} else {

    define('BASE_URL', '/');

    // Live Database
    define('DB_HOST', '68.178.227.144');
    define('DB_NAME', 'recruitment_workflow');
    define('DB_USER', 'recruitment_workflow');
    define('DB_PASS', 'recruitment_workflow');

}

define('APP_NAME', 'Recruitment Workflow System');

define('CHARSET', 'utf8mb4');